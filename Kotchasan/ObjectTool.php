<?php
/**
 * @filesource Kotchasan/ObjectTool.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

/**
 * Object tools
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class ObjectTool
{
    /**
     * คืนค่ารายการที่มีคอลัมน์ตามที่กำหนด.
     *
     * @assert (array((object)array('id' => 1, 'name' => 'one'), (object)array('id' => 2, 'name' => 'two'), (object)array('id' => 3, 'name' => 'three')), 'name') [==] (object)array(0 => 'one', 1 => 'two', 2 => 'three')
     * @assert (array((object)array('id' => 1, 'name' => 'one'), (object)array('id' => 2, 'name' => 'two'), (object)array('id' => 3, 'name' => 'three')), 'name', 'id') [==] (object)array(1 => 'one', 2 => 'two', 3 => 'three')
     *
     * @param array  $array
     * @param string $column_key ชื่อคอลัมน์ที่ต้องการ
     * @param mixed  $index_key  null คืนค่า index ของ $array, string คืนค่า index จากคอลัมน์ที่กำหนด
     *
     * @return array
     */
    public static function columns($array, $column_key, $index_key = null)
    {
        $result = array();
        if ($index_key == null) {
            foreach ($array as $i => $item) {
                if (isset($item->$column_key)) {
                    $result[$i] = $item->$column_key;
                }
            }
        } else {
            foreach ($array as $i => $item) {
                if (isset($item->$column_key) && isset($item->$index_key)) {
                    $result[$item->$index_key] = $item->$column_key;
                }
            }
        }

        return (object) $result;
    }

    /**
     * ฟังก์ชั่นรวม object แทนที่คีย์เดิม
     *
     * @assert ((object)array('one' => 1), array('two' => 2)) [==] (object)array('one' => 1, 'two' => 2)
     * @assert ((object)array('one' => 1), (object)array('two' => 2)) [==] (object)array('one' => 1, 'two' => 2)
     *
     * @param object       $a
     * @param array|object $b
     *
     * @return object
     */
    public static function replace($a, $b)
    {
        foreach ($b as $key => $value) {
            $a->$key = $value;
        }

        return $a;
    }

    /**
     * ค้นหา object จาก property
     * คืนค่าทุกรายการที่พบ รักษา index ตาม array ของ object ต้นฉบับ และ คืนค่าแอเรย์ว่างถ้าไม่พบ.
     *
     * @assert (array((object)array('id' => 1, 'name' => 'one'), (object)array('id' => 2, 'name' => 'two'), (object)array('id' => 3, 'name' => 'one')), 'name', 'one') [==] array(0 => (object)array('id' => 1, 'name' => 'one'), 2 => (object)array('id' => 3, 'name' => 'one'))
     * @assert (array((object)array('id' => 1, 'name' => 'one'), (object)array('id' => 2, 'name' => 'two'), (object)array('id' => 3, 'name' => 'one')), 'id', 'one') [==] array()
     *
     * @param array $input  ข้อมูลแอเรย์ ของ object
     * @param mixed $key    property ที่ต้องการค้นหา
     * @param mixed $search ข้อความค้นหา
     *
     * @return array
     */
    public static function search($input, $key, $search)
    {
        $result = array();
        foreach ($input as $i => $values) {
            if (isset($values->$key) && $values->$key == $search) {
                $result[$i] = $values;
            }
        }

        return $result;
    }
}
