<?php
/**
 * @filesource Kotchasan/Login.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

use Kotchasan\Http\Request;

/**
 * คลาสสำหรับตรวจสอบการ Login.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Login extends \Kotchasan\KBase
{
    /**
     * ตัวแปรบอกว่ามาจากการ submit
     * true มาจากการ submit
     * false default.
     *
     * @var bool
     */
    public static $from_submit = false;
    /**
     * ชื่อ Input ที่ต้องการให้ active
     * login_username หรือ login_password.
     *
     * @var string
     */
    public static $login_input;
    /**
     * ข้อความจาก Login Class.
     *
     * @var string
     */
    public static $login_message;
    /**
     * ตัวแปรเก็บข้อมูลที่ส่งมา
     * เช่น username, password.
     *
     * @var array
     */
    public static $login_params = array();

    /**
     * ฟังก์ชั่นตรวจสอบการ login
     * เข้าระบบสำเร็จคืนค่าแอเรย์ข้อมูลสมาชิก, ไม่สำเร็จ คืนค่าข้อความผิดพลาด
     *
     * @param array $params ข้อมูลการ login ที่ส่งมา $params = array('username' => '', 'password' => '');
     *
     * @return string|array
     */
    public function checkLogin($params)
    {
        $field_name = reset(self::$cfg->login_fields);
        if ($params['username'] !== self::$cfg->get($field_name)) {
            self::$login_input = $field_name;
            return Language::get('not a registered user');
        } elseif ($params['password'] !== self::$cfg->get('password')) {
            self::$login_input = 'password';
            return Language::get('password incorrect');
        } else {
            return array(
                'id' => 1,
                $field_name => $params['username'],
                'password' => $params['password'],
                'status' => 1,
            );
        }
        return 'not a registered user';
    }

    /**
     * ตรวจสอบการ login เมื่อมีการเรียกใช้ class new Login
     * action=logout ออกจากระบบ
     * มาจากการ submit ตรวจสอบการ login
     * ถ้าไม่มีทั้งสองส่วนด้านบน จะตรวจสอบการ login จาก session.
     *
     * @return \static
     */
    public static function create()
    {
        // create class
        $login = new static();
        // ชื่อฟิลด์สำหรับการรับค่าเป็นรายการแรกของ login_fields
        $field_name = reset(self::$cfg->login_fields);
        try {
            // อ่านข้อมูลจากฟอร์ม login ฟิลด์ login_username
            self::$login_params['username'] = self::$request->post('login_username')->username();
            if (empty(self::$login_params['username'])) {
                if (isset($_SESSION['login']) && isset($_SESSION['login'][$field_name])) {
                    // session
                    self::$login_params['username'] = Text::username($_SESSION['login'][$field_name]);
                    if (isset($_SESSION['login']['token'])) {
                        self::$login_params['token'] = $_SESSION['login']['token'];
                    } elseif (isset($_SESSION['login']['password'])) {
                        self::$login_params['password'] = $_SESSION['login']['password'];
                    }
                } else {
                    self::$login_params['username'] = null;
                }
                self::$from_submit = self::$request->post('login_username')->exists();
            } elseif (self::$request->post('login_password')->exists()) {
                self::$login_params['password'] = self::$request->post('login_password')->password();
                self::$from_submit = true;
            }
            $action = self::$request->request('action')->toString();
            // ตรวจสอบการ login
            if ($action === 'logout' && !self::$from_submit) {
                // logout ลบ session และ cookie
                unset($_SESSION['login']);
                self::$login_message = Language::get('Logout successful');
            } elseif ($action === 'forgot') {
                // ขอรหัสผ่านใหม่
                $login = $login->forgot(self::$request);
            } else {
                // ตรวจสอบค่าที่ส่งมา
                if (empty(self::$login_params['username'])) {
                    if (self::$from_submit) {
                        self::$login_message = 'Please fill in';
                        self::$login_input = 'login_username';
                    }
                } elseif (empty(self::$login_params['password']) && self::$from_submit) {
                    self::$login_message = 'Please fill in';
                    self::$login_input = 'login_password';
                } elseif (!self::$from_submit || (self::$from_submit && self::$request->isReferer())) {
                    // ตรวจสอบการ login กับฐานข้อมูล
                    $login_result = $login->checkLogin(self::$login_params);
                    if (is_array($login_result)) {
                        // save login session
                        $_SESSION['login'] = $login_result;
                    } else {
                        if (is_string($login_result)) {
                            // ข้อความผิดพลาด
                            self::$login_input = self::$login_input == 'password' ? 'login_password' : 'login_username';
                            self::$login_message = $login_result;
                        }
                        // logout ลบ session และ cookie
                        unset($_SESSION['login']);
                    }
                }
            }
        } catch (InputItemException $e) {
            self::$login_message = $e->getMessage();
        }

        return $login;
    }

    /**
     * ฟังก์ชั่นส่งอีเมลลืมรหัสผ่าน.
     */
    public function forgot(Request $request)
    {
        return $this;
    }

    /**
     * ฟังก์ชั่นตรวจสอบสถานะแอดมิน
     * คืนค่าข้อมูลสมาชิก (แอเรย์) ถ้าเป็นผู้ดูแลระบบและเข้าระบบแล้ว ไม่ใช่คืนค่า null.
     *
     * @return array|null
     */
    public static function isAdmin()
    {
        $login = self::isMember();

        return isset($login['status']) && $login['status'] == 1 ? $login : null;
    }

    /**
     * ฟังก์ชั่นตรวจสอบการเข้าระบบ
     * คืนค่าข้อมูลสมาชิก (แอเรย์) ถ้าเป็นสมาชิกและเข้าระบบแล้ว ไม่ใช่คืนค่า null.
     *
     * @return array|null
     */
    public static function isMember()
    {
        return empty($_SESSION['login']) ? null : $_SESSION['login'];
    }

    /**
     * ตรวจสอบสถานะสมาชิก
     * แอดมินสูงสุด (status=1) ทำได้ทุกอย่าง
     * คืนค่าข้อมูลสมาชิก (แอเรย์) ถ้าไม่สามารถทำรายการได้คืนค่า null.
     *
     * @param array        $login
     * @param array|int $statuses
     *
     * @return array|null
     */
    public static function checkStatus($login, $statuses)
    {
        if (!empty($login)) {
            if ($login['status'] == 1) {
                // แอดมิน
                return $login;
            } elseif (is_array($statuses)) {
                if (in_array($login['status'], $statuses)) {
                    return $login;
                }
            } elseif ($login['status'] == $statuses) {
                return $login;
            }
        }
        // ไม่มีสิทธิ

        return null;
    }
}
