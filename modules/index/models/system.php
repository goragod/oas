<?php
/**
 * @filesource modules/index/models/system.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\System;

use Gcms\Config;
use Gcms\Login;
use Kotchasan\File;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=system
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{
    /**
     * บันทึกการตั้งค่าเว็บไซต์ (system.php)
     *
     * @param Request $request
     */
    public function submit(Request $request)
    {
        $ret = array();
        // session, token, member, can_config, ไม่ใช่สมาชิกตัวอย่าง
        if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
            if (Login::checkPermission($login, 'can_config') && Login::notDemoMode($login)) {
                try {
                    // โหลด config
                    $config = Config::load(ROOT_PATH.'settings/config.php');
                    foreach (array('web_title', 'web_description') as $key) {
                        $value = $request->post($key)->quote();
                        if (empty($value)) {
                            $ret['ret_'.$key] = 'Please fill in';
                        } else {
                            $config->$key = $value;
                        }
                    }
                    foreach (array('user_forgot', 'user_register', 'welcome_email', 'member_only', 'demo_mode') as $key) {
                        $config->$key = $request->post($key)->toBoolean();
                    }
                    foreach (array('login_fields') as $key) {
                        $value = $request->post($key, array())->filter('a-z0-9_');
                        if (empty($value)) {
                            $ret['ret_'.$key] = Language::get('Please select at least one item');
                        } else {
                            $config->$key = $value;
                        }
                    }
                    $config->timezone = $request->post('timezone')->text();
                    $config->facebook_appId = $request->post('facebook_appId')->text();
                    $config->google_client_id = $request->post('google_client_id')->text();
                    $config->bg_color = $request->post('bg_color')->filter('#ABCDEF0-9');
                    $config->color = $request->post('color')->filter('#ABCDEF0-9');
                    $config->line_api_key = $request->post('line_api_key')->topic();
                    if (empty($ret)) {
                        // อัปโหลดไฟล์
                        $dir = ROOT_PATH.DATA_FOLDER.'images/';
                        foreach ($request->getUploadedFiles() as $item => $file) {
                            if (preg_match('/^file_(logo|bg_image)$/', $item, $match)) {
                                /* @var $file \Kotchasan\Http\UploadedFile */
                                if (!File::makeDirectory($dir)) {
                                    // ไดเรคทอรี่ไม่สามารถสร้างได้
                                    $ret['ret_file_'.$item] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'images/');
                                } elseif ($request->post('delete_'.$match[1])->toBoolean() == 1) {
                                    // ลบ
                                    if (is_file($dir.$match[1].'.png')) {
                                        unlink($dir.$match[1].'.png');
                                    }
                                } elseif ($file->hasUploadFile()) {
                                    if (!$file->validFileExt(array('jpg', 'jpeg', 'png'))) {
                                        // ชนิดของไฟล์ไม่รองรับ
                                        $ret['ret_file_'.$match[1]] = Language::get('The type of file is invalid');
                                    } else {
                                        try {
                                            $file->moveTo($dir.$match[1].'.png');
                                        } catch (\Exception $exc) {
                                            // ไม่สามารถอัปโหลดได้
                                            $ret['ret_file_'.$match[1]] = Language::get($exc->getMessage());
                                        }
                                    }
                                } elseif ($file->hasError()) {
                                    // ข้อผิดพลาดการอัปโหลด
                                    $ret['ret_file_'.$match[1]] = Language::get($file->getErrorMessage());
                                }
                            }
                        }
                    }
                    if (empty($ret)) {
                        // save config
                        if (Config::save($config, ROOT_PATH.'settings/config.php')) {
                            $ret['alert'] = Language::get('Saved successfully');
                            $ret['location'] = 'reload';
                            // เคลียร์
                            $request->removeToken();
                        } else {
                            // ไม่สามารถบันทึก config ได้
                            $ret['alert'] = sprintf(Language::get('File %s cannot be created or is read-only.'), 'settings/config.php');
                        }
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
}
