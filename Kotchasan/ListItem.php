<?php
/**
 * @filesource Kotchasan/ListItem.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

/**
 * คลาสสำหรับจัดการแอเรย์.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class ListItem
{
    /**
     * ข้อมูล.
     *
     * @var array
     */
    public $datas;
    /**
     * ที่อยู่ไฟล์ที่โหลดมา.
     *
     * @var string
     */
    private $source;

    /**
     * นำเข้าข้อมูลครั้งละหลายรายการ.
     *
     * @param array $array ข้อมูลที่ต้องการนำเข้า
     */
    public function assign($array)
    {
        if (isset($this->datas)) {
            $this->datas = array_merge($this->datas, $array);
        } else {
            $this->datas = $array;
        }
    }

    /**
     * ลบข้อมูลทั้งหมด.
     */
    public function clear()
    {
        unset($this->datas);
    }

    /**
     * อ่านจำนวนสมาชิกทั้งหมด.
     *
     * @return int จำนวนสมาชิกทั้งหมด
     */
    public function count()
    {
        return count($this->datas);
    }

    /**
     * ลบรายการที่กำหนด
     * คืนค่า true ถ้าสำเร็จ, false ถ้าไม่พบ.
     *
     * @param string $key ของรายการที่ต้องการจะลบ
     *
     * @return bool
     */
    public function delete($key)
    {
        if (array_key_exists($key, $this->datas)) {
            unset($this->datas[$key]);

            return true;
        }

        return false;
    }

    /**
     * คืนค่าแอเรย์รายการแรก
     *
     * @return mixed
     */
    public function firstItem()
    {
        return reset($this->datas);
    }

    /**
     * อ่านข้อมูลที่ $key
     * คืนค่ารายการที่ $key ถ้าไม่พบคืนค่า null.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return array_key_exists($key, $this->datas) ? $this->datas[$key] : null;
    }

    /**
     * ค้นหาข้อมูลในแอเรย์
     * คืนค่า key ของรายการที่พบ ถ้าไม่พบคืนค่า false.
     *
     * @param mixed $value รายการค้นหา
     *
     * @return mixed
     */
    public function indexOf($value)
    {
        return array_search($value, $this->datas);
    }

    /**
     * กำหนดค่าเริ่มต้นของ Class.
     *
     * @param array $config
     */
    public function init($config)
    {
        $this->datas = $config;
    }

    /**
     * เพิ่มรายการใหม่ต่อจากรายการที่ $key.
     *
     * @param mixed $key
     * @param mixed $item รายการใหม่
     */
    public function insert($key, $item)
    {
        if (is_int($key) && $key == count($this->datas)) {
            $this->datas[] = $item;
        } else {
            $temp = $this->datas;
            $this->datas = array();
            foreach ($temp as $k => $value) {
                $this->datas[$k] = $value;
                if ($k == $key) {
                    $this->datas[$key] = $item;
                }
            }
        }
    }

    /**
     * เพิ่มรายการใหม่ก่อนรายการที่ $key.
     *
     * @param mixed $key
     * @param mixed $item รายการใหม่
     */
    public function insertBefore($key, $item)
    {
        $temp = $this->datas;
        $this->datas = array();
        foreach ($temp as $k => $value) {
            if ($k == $key) {
                $this->datas[$key] = $item;
            }
            $this->datas[$k] = $value;
        }
    }

    /**
     * อ่านจำนวนรายการทั้งหมด.
     *
     * @return array
     */
    public function items()
    {
        return $this->datas;
    }

    /**
     * อ่านรายชื่อ keys
     * คืนค่า แอเรย์ของรายการ key ทั้งหมด.
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->datas);
    }

    /**
     * คืนค่าแอเรย์รายการสุดท้าย.
     *
     * @return mixed
     */
    public function lastItem()
    {
        return end($this->datas);
    }

    /**
     * โหลดแอเรย์จากไฟล์.
     *
     * @param string $file ชื่อไฟล์ที่ต้องการโหลดรวม path
     *
     * @return \static
     */
    public function loadFromFile($file)
    {
        if (is_file($file)) {
            $config = include $file;
            $this->source = $file;
            $this->assign($config);
        }

        return $this;
    }

    /**
     * บันทึกเป็นไฟล์
     * คืนค่า true ถ้าสำเร็จ.
     *
     * @return bool
     */
    public function saveToFile()
    {
        if (!isset($this->source) || empty($this->datas)) {
            return false;
        } else {
            $datas = array();
            foreach ($this->datas as $key => $value) {
                if (is_array($value)) {
                    $datas[] = (is_int($key) ? $key : "'".strtolower($key)."'")." => array(\n".$this->arrayToString(1, $value)."\n\t)";
                } else {
                    $datas[] = (is_int($key) ? $key : "'".strtolower($key)."'").' => '.(is_int($value) ? $value : "'".addslashes($value)."'");
                }
            }
            $file = str_replace(ROOT_PATH, '', $this->source);
            $f = @fopen(ROOT_PATH.$file, 'w');
            if ($f === false) {
                return false;
            } else {
                fwrite($f, "<?php\n/* $file */\nreturn array (\n\t".implode(",\n\t", $datas)."\n);");
                fclose($f);

                return true;
            }
        }
    }

    /**
     * เพิ่มรายการใหม่ที่ลำดับสุดท้าย ถ้ามี $key อยู่แล้วจะแทนที่รายการเดิม
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        $this->datas[$key] = $value;
    }

    /**
     * คืนค่า แอเรย์ของข้อมูลทั้งหมด.
     *
     * @return array
     */
    public function values()
    {
        return array_values($this->datas);
    }

    /**
     * array to string.
     *
     * @param int   $indent
     * @param array $array
     *
     * @return string
     */
    private function arrayToString($indent, $array)
    {
        $t = str_repeat("\t", $indent + 1);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $datas[] = (is_int($key) ? $key : "'$key'")." => array(\n".$this->arrayToString($indent + 1, $value)."\n$t)";
            } else {
                $datas[] = (is_int($key) ? $key : "'$key'").' => '.(is_int($value) ? $value : "'".addslashes($value)."'");
            }
        }

        return $t.implode(",\n$t", $datas);
    }
}
