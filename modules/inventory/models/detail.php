<?php
/**
 * @filesource modules/inventory/models/detail.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Inventory\Detail;

use Gcms\Login;
use Kotchasan\File;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=inventory-write&id=xx&tab=detail
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * อ่านข้อมูลสินค้าที่ $id
     *
     * @param int $id
     *
     * @return object
     */
    public static function get($id)
    {
        $result = array(
            'image' => '',
            'detail' => '',
            'description' => '',
        );
        $query = static::createQuery()
            ->select('name', 'value')
            ->from('inventory_meta')
            ->where(array(
                array('inventory_id', $id),
                array('name', array_keys($result)),
            ));
        foreach ($query->execute() as $item) {
            $result[$item->name] = $item->value;
        }
        return (object) $result;
    }

    /**
     * บันทึกข้อมูล (detail.php)
     *
     * @param Request $request
     */
    public function submit(Request $request)
    {
        $ret = array();
        // session, token, can_manage_inventory, ไม่ใช่สมาชิกตัวอย่าง
        if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
            if (Login::checkPermission($login, 'can_manage_inventory') && Login::notDemoMode($login)) {
                try {
                    // อ่านข้อมูลที่เลือก
                    $index = \Inventory\Write\Model::get($request->post('write_id')->toInt());
                    if ($index) {
                        // ไดเร็คทอรี่เก็บไฟล์
                        $dir = ROOT_PATH.DATA_FOLDER.'inventory/';
                        // อัปโหลดไฟล์
                        foreach ($request->getUploadedFiles() as $item => $file) {
                            /* @var $file \Kotchasan\Http\UploadedFile */
                            if ($item == 'write_image') {
                                if ($file->hasUploadFile()) {
                                    if (!File::makeDirectory($dir)) {
                                        // ไดเรคทอรี่ไม่สามารถสร้างได้
                                        $ret['ret_'.$item] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'inventory/');
                                    } else {
                                        try {
                                            $file->resizeImage(array('jpg', 'jpeg', 'png'), $dir, $index->id.'.jpg', self::$cfg->inventory_w);
                                        } catch (\Exception $exc) {
                                            // ไม่สามารถอัปโหลดได้
                                            $ret['ret_'.$item] = Language::get($exc->getMessage());
                                        }
                                    }
                                } elseif ($file->hasError()) {
                                    // ข้อผิดพลาดการอัปโหลด
                                    $ret['ret_'.$item] = Language::get($file->getErrorMessage());
                                }
                            }
                        }
                        if (empty($ret)) {
                            // แก้ไข
                            $table = $this->getTableName('inventory_meta');
                            // Database
                            $db = $this->db();
                            // ลบรายการเก่า
                            $db->delete($table, array(
                                array('inventory_id', $index->id),
                                array('name', array('detail', 'description')),
                            ), 0);
                            // บันทึก meta (ถ้ามี)
                            $description = $request->post('write_description')->topic();
                            if ($description != '') {
                                $db->insert($table, array(
                                    'inventory_id' => $index->id,
                                    'name' => 'description',
                                    'value' => $description,
                                ));
                            }
                            $detail = $request->post('write_detail')->textarea();
                            if ($detail != '') {
                                $db->insert($table, array(
                                    'inventory_id' => $index->id,
                                    'name' => 'detail',
                                    'value' => $detail,
                                ));
                            }
                            // คืนค่า
                            $ret['alert'] = Language::get('Saved successfully');
                            $ret['location'] = 'reload';
                            // เคลียร์
                            $request->removeToken();
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
