<?php
/**
 * @filesource modules/inventory/models/number.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Inventory\Number;

/**
 * คลาสสำหรับจัดการ Running Number.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * คืนค่าข้อมูล running number
     * ตรวจสอบข้อมูลซ้ำด้วย
     *
     * @param int    $id         ID สำหรับตรวจสอบข้อมูลซ้ำ
     * @param string $name       ชื่อฟิลด์ที่ต้องการ
     * @param string $table_name ชื่อตาราง สำหรับตรวจสอบข้อมูลซ้ำ
     * @param string $field      ชื่อฟิลด์ สำหรับตรวจสอบข้อมูลซ้ำ
     *
     * @return string
     */
    public static function get($id, $name, $table_name, $field)
    {
        if (isset(self::$cfg->$name)) {
            // Model
            $model = new static();
            // Database
            $db = $model->db();
            // Table
            $table_number = $model->getTableName('number');
            // ตรวจสอบรายการที่เลือก
            $number = $db->first($table_number, array('type', $name));
            if ($number) {
                $next_id = 1 + (int) $number->auto_increment;
            } else {
                $next_id = 1;
            }
            // ตรวจสอบ order_no ซ้ำ
            while (true) {
                $result = \Kotchasan\Number::printf(self::$cfg->$name, $next_id);
                $search = $db->first($table_name, array(
                    array($field, $result),
                ));
                if (!$search || ($id > 0 && $search->id == $id)) {
                    break;
                } else {
                    ++$next_id;
                }
            }
            // อัปเดต running number
            if ($number) {
                $db->update($table_number, array('type', $name), array('auto_increment' => $next_id));
            } else {
                $db->insert($table_number, array(
                    'type' => $name,
                    'auto_increment' => $next_id,
                    'last_update' => date('Y-m-d'),
                ));
            }
            // คืนค่า
            return $result;
        } else {
            // error
            throw new \Kotchasan\InputItemException('Not configured $cfg->'.$name);
        }
    }
}
