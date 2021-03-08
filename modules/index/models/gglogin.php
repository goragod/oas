<?php
/**
 * @filesource modules/index/models/gglogin.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Gglogin;

use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Validator;

/**
 * Google Login
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * รับข้อมูลที่ส่งมาจากการเข้าระบบด้วยบัญชี Google
     *
     * @param Request $request
     */
    public function chklogin(Request $request)
    {
        // session, token
        if ($request->initSession() && $request->isSafe()) {
            $ret = array();
            try {
                // ข้อมูลที่ส่งมา
                $username = $request->post('email')->url();
                if (!Validator::email($username)) {
                    // อีเมลไม่ถูกต้อง
                    $ret['alert'] = Language::get('Unable to complete the transaction');
                } else {
                    // สุ่มรหัสผ่านใหม่
                    $password = \Kotchasan\Password::uniqid(6);
                    // db
                    $db = $this->db();
                    // table
                    $user_table = $this->getTableName('user');
                    // ตรวจสอบสมาชิกกับ db
                    $search = $db->createQuery()
                        ->from('user')
                        ->where(array('username', $username))
                        ->toArray()
                        ->first();
                    if ($search === false) {
                        // ยังไม่เคยลงทะเบียน, ลงทะเบียนใหม่
                        if (self::$cfg->demo_mode) {
                            $permissions = array_keys(\Gcms\Controller::getPermissions());
                            unset($permissions['can_config']);
                        } else {
                            $permissions = array();
                        }
                        $save = \Index\Register\Model::execute($this, array(
                            'username' => $username,
                            'password' => $password,
                            'name' => $request->post('name')->topic(),
                            // Google
                            'social' => 2,
                            'visited' => 1,
                            'lastvisited' => time(),
                            // โหมดตัวอย่างเป็นแอดมิน, ไม่ใช่เป็นสมาชิกทั่วไป
                            'status' => self::$cfg->demo_mode ? 1 : 0,
                            'token' => \Kotchasan\Password::uniqid(40),
                            'active' => 1,
                        ), $permissions);
                    } elseif ($search['social'] == 2) {
                        if ($search['active'] == 1) {
                            // google เคยเยี่ยมชมแล้ว อัปเดตการเยี่ยมชม
                            $save = $search;
                            ++$save['visited'];
                            $save['lastvisited'] = time();
                            $save['ip'] = $request->getClientIp();
                            $save['salt'] = \Kotchasan\Password::uniqid();
                            $save['token'] = \Kotchasan\Password::uniqid(40);
                            // อัปเดต
                            $db->update($user_table, $search['id'], $save);
                            $save['permission'] = explode(',', trim($save['permission'], " \t\n\r\0\x0B,"));
                        } else {
                            // ไม่ใช่สมาชิกปัจจุบัน ไม่สามารถเข้าระบบได้
                            $ret['alert'] = Language::get('Unable to complete the transaction');
                            $ret['isMember'] = 0;
                        }
                    } else {
                        // ไม่สามารถ login ได้ เนื่องจากมี email อยู่ก่อนแล้ว
                        $save = false;
                        $ret['alert'] = Language::replace('This :name already exist', array(':name' => Language::get('Username')));
                        $ret['isMember'] = 0;
                    }
                    if (is_array($save)) {
                        // login
                        unset($save['password']);
                        $_SESSION['login'] = $save;
                        // คืนค่า
                        $ret['isMember'] = 1;
                        $ret['alert'] = Language::replace('Welcome %s, login complete', array('%s' => $save['name']));
                        // เคลียร์
                        $request->removeToken();
                    }
                }
            } catch (\Kotchasan\InputItemException $e) {
                $ret['alert'] = $e->getMessage();
            }
            // คืนค่าเป็น json
            echo json_encode($ret);
        }
    }
}
