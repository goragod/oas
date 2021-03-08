<?php
/**
 * @filesource Kotchasan/Inputs.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

/**
 * รายการ input รูปแบบ Array
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Inputs implements \Iterator
{
    /**
     * ตัวแปรเก็บ properties ของคลาส
     *
     * @var array
     */
    private $datas = array();

    /**
     * magic method คืนค่าข้อมูลสำหรับ input ชนิด array
     *
     * @param string $name
     * @param array  $arguments
     *
     * @throws \InvalidArgumentException ถ้าไม่มี method ที่ต้องการ
     *
     * @return array
     */
    public function __call($name, $arguments)
    {
        if (method_exists('Kotchasan\InputItem', $name)) {
            $result = array();
            foreach ($this->datas as $key => $item) {
                $result[$key] = $this->collectInputs($item, $name, $arguments);
            }
            return $result;
        } else {
            throw new \InvalidArgumentException('Method '.$name.' not found');
        }
    }

    /**
     * Class Constructer
     *
     * @param array       $items รายการ input
     * @param string|null $type  ประเภท Input เช่น GET POST SESSION COOKIE หรือ null ถ้าไม่ได้มาจากรายการข้างต้น
     */
    public function __construct(array $items = array(), $type = null)
    {
        foreach ($items as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $this->datas[$k][$key] = InputItem::create($v, $type);
                }
            } else {
                $this->datas[$key] = InputItem::create($value, $type);
            }
        }
    }

    /**
     * เตรียมผลลัพท์สำหรับ input แบบ array
     *
     * @param Object $item
     * @param string $name
     * @param array  $arguments
     *
     * @return array|object
     */
    private function collectInputs($item, $name, $arguments)
    {
        if (is_array($item)) {
            $array = array();
            foreach ($item as $k => $v) {
                $array[$k] = $this->collectInputs($v, $name, $arguments);
            }
            return $array;
        }
        if (isset($arguments[1])) {
            return $item->$name($arguments[0], $arguments[1]);
        } elseif (isset($arguments[0])) {
            return $item->$name($arguments[0]);
        } else {
            return $item->$name($arguments);
        }
    }

    /**
     * คืนค่า InputItem รายการปัจจุบัน
     *
     * @return \Kotchasan\InputItem
     */
    public function current()
    {
        $var = current($this->datas);

        return $var;
    }

    /**
     * อ่าน Input ที่ต้องการ
     *
     * @param string|int $key รายการที่ต้องการ
     *
     * @return \Kotchasan\InputItem
     */
    public function get($key)
    {
        return $this->datas[$key];
    }

    /**
     * คืนค่าคีย์หรือลำดับของ InputItem ในลิสต์รายการ
     *
     * @return string
     */
    public function key()
    {
        $var = key($this->datas);
        return $var;
    }

    /**
     * คืนค่า InputItem รายการถัดไป
     *
     * @return \Kotchasan\InputItem
     */
    public function next()
    {
        $var = next($this->datas);
        return $var;
    }

    /**
     * inherited from Iterator
     */
    public function rewind()
    {
        reset($this->datas);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        $key = key($this->datas);
        return $key !== null && $key !== false;
    }
}
