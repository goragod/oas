<?php
/**
 * @filesource modules/index/models/register.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Register;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=register
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * บันทึกข้อมูล (register.php)
     *
     * @param Request $request
     */
    public function submit(Request $request)
    {
        $ret = array();
        // session, token
        if ($request->initSession() && $request->isSafe()) {
            // แอดมิน
            $isAdmin = Login::isAdmin();
            // บุคคลทั่วไป สมัครสมาชิกได้ และ ไม่ใช่โหมดตัวอย่าง หรือ แอดมิน
            if ((!empty(self::$cfg->user_register) && self::$cfg->demo_mode == false) || $isAdmin) {
                try {
                    // รับค่าจากการ POST
                    $save = array(
                        'username' => $request->post('register_username')->username(),
                        'name' => $request->post('register_name')->topic(),
                        'status' => $isAdmin ? $request->post('register_status')->toInt() : 0,
                        'active' => 1,
                    );
                    $permission = $isAdmin ? $request->post('register_permission', array())->topic() : array();
                    // table
                    $table_user = $this->getTableName('user');
                    // Database
                    $db = $this->db();
                    // ข้อมูลการเข้าระบบ
                    $login_fields = Language::get('LOGIN_FIELDS');
                    foreach (self::$cfg->login_fields as $k) {
                        if (isset($save[$k])) {
                            if (empty($save[$k])) {
                                $ret['ret_register_'.$k] = 'Please fill in';
                            } else {
                                // ตรวจสอบข้อมูลซ้ำ
                                $search = $db->first($table_user, array($k, $save[$k]));
                                if ($search) {
                                    $ret['ret_register_'.$k] = Language::replace('This :name already exist', array(':name' => $login_fields[$k]));
                                }
                            }
                        }
                    }
                    // password
                    $password = $request->post('register_password')->password();
                    $repassword = $request->post('register_repassword')->password();
                    if (mb_strlen($password) < 4) {
                        // รหัสผ่านต้องไม่น้อยกว่า 4 ตัวอักษร
                        $ret['ret_register_password'] = 'this';
                    } elseif ($repassword != $password) {
                        // กรอกรหัสผ่านสองช่องให้ตรงกัน
                        $ret['ret_register_repassword'] = 'this';
                    } else {
                        $save['password'] = $password;
                    }
                    // name
                    if (empty($save['name'])) {
                        $ret['ret_register_name'] = 'Please fill in';
                    }
                    if (empty($ret)) {
                        // ลงทะเบียนสมาชิกใหม่
                        $save = self::execute($this, $save, $permission);
                        if ($isAdmin) {
                            // คืนค่า
                            $ret['alert'] = Language::get('Saved successfully');
                            // ไปหน้าสมาชิก
                            $ret['location'] = 'index.php?module=member';
                        } elseif (!empty(self::$cfg->welcome_email)) {
                            // ส่งอีเมล แจ้งลงทะเบียนสมาชิกใหม่
                            $subject = '['.self::$cfg->web_title.'] '.Language::get('Welcome new members');
                            $msg = "{LNG_Your registration information}<br>\n<br>\n";
                            $msg .= '{LNG_Username} : '.$save['username']."<br>\n";
                            $msg .= '{LNG_Password} : '.$password."<br>\n";
                            $msg .= '{LNG_Name} : '.$save['name'];
                            $msg = Language::trans($msg);
                            $err = \Kotchasan\Email::send($save['username'], self::$cfg->noreply_email, $subject, $msg);
                            if ($err->error()) {
                                // คืนค่า error
                                $ret['alert'] = strip_tags($err->getErrorMessage());
                            } else {
                                // คืนค่า
                                $ret['alert'] = Language::replace('Register successfully, We have sent complete registration information to :email', array(':email' => $save['username']));
                            }
                        } else {
                            // คืนค่า
                            $ret['alert'] = Language::get('Register successfully Please log in');
                        }
                        // ไปหน้าเข้าระบบ
                        $ret['location'] = $isAdmin ? 'index.php?module=member' : 'index.php?action=login';
                        // เคลียร์
                        $request->removeToken();
                    }
                } catch (\Kotchasan\InputItemException $e) {
                    $ret['alert'] = $e->getMessage();
                }
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่าเป็น JSON
        echo json_encode($ret);
    }

    /**
     * ลงทะเบียนสมาชิกใหม่
     * คืนค่าแอเรย์ของข้อมูลสมาชิกใหม่
     *
     * @param Model $model
     * @param array $save       ข้อมูลสมาชิก
     * @param array $permission
     *
     * @return array
     */
    public static function execute($model, $save, $permission = null)
    {
        if (!isset($save['username'])) {
            $save['username'] = '';
        }
        if (!isset($save['password'])) {
            $save['password'] = '';
        } else {
            $save['salt'] = \Kotchasan\Password::uniqid();
            $save['password'] = sha1(self::$cfg->password_key.$save['password'].$save['salt']);
        }
        $save['create_date'] = date('Y-m-d H:i:s');
        $save['session_id'] = session_id();
        if ($permission === null) {
            // permission ถ้าเป็น null สามารถทำได้ทั้งหมด
            $permission = array_keys(\Gcms\Controller::getPermissions());
        } else {
            // สมาชิกทั่วไปใช้ค่าเริ่มต้นของโมดูล
            $permission = \Gcms\Controller::initModule($permission, 'newRegister', $save);
        }
        $save['permission'] = empty($permission) ? '' : ','.implode(',', $permission).',';
        // บันทึกลงฐานข้อมูล
        $save['id'] = $model->db()->insert($model->getTableName('user'), $save);
        // คืนค่าแอเรย์ของข้อมูลสมาชิกใหม่
        $save['permission'] = array();
        foreach ($permission as $key => $value) {
            $save['permission'][] = $value;
        }
        return $save;
    }
}
