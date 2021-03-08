<?php
/**
 * @filesource Kotchasan/Csv.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

/**
 * CSV function.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Csv
{
    /**
     * @var mixed
     */
    private $charset;
    /**
     * @var mixed
     */
    private $columns;
    /**
     * @var mixed
     */
    private $datas;
    /**
     * @var mixed
     */
    private $keys;

    /**
     * ฟังก์ชั่นนำเข้าข้อมูล CSV
     * คืนค่าข้อมูลที่อ่านได้เป็นแอเรย์
     *
     * @param string $csv     ชื่อไฟล์รวมพาธ
     * @param array  $columns ข้อมูลคอลัมน์ array('column1' => 'data type', 'column2' => 'data type', ....)
     * @param array  $keys    ชื่อคอลัมน์สำหรับตรวจข้อมูลซ้ำ null(default) หมายถึงไม่ตรวจสอบ
     * @param string $charset รหัสภาษาของไฟล์ ค่าเริ่มต้นคือ Windows-874 (ภาษาไทย)
     *
     * @return array
     */
    public static function import($csv, $columns, $keys = null, $charset = 'Windows-874')
    {
        $obj = new static();
        $obj->columns = $columns;
        $obj->datas = array();
        $obj->charset = strtoupper($charset);
        $obj->keys = $keys;
        $obj->read($csv, array($obj, 'importDatas'), array_keys($columns));
        return $obj->datas;
    }

    /**
     * อ่านไฟล์ CSV ทีละแถวส่งไปยังฟังก์ชั่น $onRow
     * แถวแรกของข้อมูลคือ Header ต้องระบุเสมอ
     *
     * @param string   $file  ชื่อไฟล์รวมพาธ
     * @param function $onRow ฟังก์ชั่นรับค่าแต่ละแถว function($data){}
     * @param array $headers แอเรย์เก็บชื่อคอลัมน์สำหรับการทดสอบความถูกต้อง ถ้าไม่ระบุจะไม่ตรวจสอบ
     *
     * @throws Exception ถ้า Header ของไฟล์ CSV ไม่ถูกต้อง
     */
    public static function read($file, $onRow, $headers = array())
    {
        $columns = array();
        $f = @fopen($file, 'r');
        if ($f) {
            while (($data = fgetcsv($f)) !== false) {
                if (empty($columns)) {
                    if (is_array($headers)) {
                        if (count($headers) != count($data)) {
                            throw new \Exception('Invalid CSV Header');
                        } else {
                            // remove BOM
                            $data[0] = self::removeBomUtf8($data[0]);
                            // ตรวจสอบ Header
                            foreach ($headers as $k) {
                                if (!in_array($k, $data)) {
                                    throw new \Exception('Invalid CSV Header');
                                }
                            }
                        }
                    }
                    $columns = $data;
                } else {
                    $items = array();
                    foreach ($data as $k => $v) {
                        if (isset($columns[$k])) {
                            $items[$columns[$k]] = $v;
                        }
                    }
                    call_user_func($onRow, $items);
                }
            }
            fclose($f);
        }
    }

    /**
     * สร้างไฟล์ CSV สำหรับดาวน์โหลด
     * คืนค่า true
     *
     * @param string $file    ชื่อไฟล์ ไม่ต้องมีนามสกุล
     * @param array  $header  ส่วนหัวของข้อมูล
     * @param array  $datas   ข้อมูล
     * @param string $charset ภาษาของ CSV ค่าเริ่มต้นคือ Windows-874 (ภาษาไทย)
     *
     * @return bool
     */
    public static function send($file, $header, $datas, $charset = 'Windows-874')
    {
        // header
        header('Content-Type: text/csv;charset="'.$charset.'"');
        header('Content-Disposition: attachment;filename="'.$file.'.csv"');
        // create stream
        $f = fopen('php://output', 'w');
        // charset
        $charset = strtoupper($charset);
        // csv header
        if (!empty($header)) {
            fputcsv($f, self::convert($header, $charset));
        }
        // content
        foreach ($datas as $item) {
            fputcsv($f, self::convert($item, $charset));
        }
        // close
        fclose($f);
        // คืนค่า สำเร็จ
        return true;
    }

    /**
     * remove BOM
     *
     * @param $s
     *
     * @return string
     */
    private static function removeBomUtf8($s)
    {
        if (substr($s, 0, 3) == chr(hexdec('EF')).chr(hexdec('BB')).chr(hexdec('BF'))) {
            return substr($s, 3);
        } else {
            return $s;
        }
    }

    /**
     * แปลงข้อมูลเป็นภาษาที่เลือก
     *
     * @param array  $datas
     * @param string $charset
     *
     * @return array
     */
    private static function convert($datas, $charset)
    {
        if ($charset != 'UTF-8') {
            foreach ($datas as $k => $v) {
                if ($v != '') {
                    $datas[$k] = iconv('UTF-8', $charset.'//IGNORE', $v);
                }
            }
        }
        return $datas;
    }

    /**
     * ฟังก์ชั่นรับค่าจากการอ่าน CSV
     *
     * @param array $data
     */
    private function importDatas($data)
    {
        $save = array();
        foreach ($this->columns as $key => $type) {
            $save[$key] = null;
            if (isset($data[$key])) {
                if (is_array($type)) {
                    $save[$key] = call_user_func($type, $data[$key]);
                } elseif ($type == 'int') {
                    $save[$key] = (int) $data[$key];
                } elseif ($type == 'double') {
                    $save[$key] = (float) $data[$key];
                } elseif ($type == 'float') {
                    $save[$key] = (float) $data[$key];
                } elseif ($type == 'number') {
                    $save[$key] = preg_replace('/[^0-9]+/', '', $data[$key]);
                } elseif ($type == 'en') {
                    $save[$key] = preg_replace('/[^a-zA-Z0-9]+/', '', $data[$key]);
                } elseif ($type == 'date') {
                    if (preg_match('/^([0-9]{4,4})[\-\/]([0-9]{1,2})[\-\/]([0-9]{1,2})$/', $data[$key], $match)) {
                        $save[$key] = "$match[1]-$match[2]-$match[3]";
                    } elseif (preg_match('/^([0-9]{1,2})[\-\/]([0-9]{1,2})[\-\/]([0-9]{4,4})$/', $data[$key], $match)) {
                        $save[$key] = "$match[3]-$match[2]-$match[1]";
                    }
                } elseif ($type == 'datetime') {
                    if (preg_match('/^([0-9]{4,4})[\-\/]([0-9]{2,2})[\-\/]([0-9]{2,2})\s([0-9]{2,2}):([0-9]{2,2}):([0-9]{2,2})$/', $data[$key])) {
                        $save[$key] = $data[$key];
                    } elseif (preg_match('/^([0-9]{2,2})[\-\/]([0-9]{2,2})[\-\/]([0-9]{4,4})\s(([0-9]{2,2}):([0-9]{2,2}):([0-9]{2,2}))$/', $data[$key], $match)) {
                        $save[$key] = "$match[4]-$match[3]-$match[2] $match[1]";
                    }
                } elseif ($type == 'time') {
                    if (preg_match('/^([0-9]{2,2}):([0-9]{2,2}):([0-9]{2,2})$/', $data[$key])) {
                        $save[$key] = $data[$key];
                    }
                } elseif ($this->charset == 'UTF-8') {
                    $save[$key] = \Kotchasan\Text::topic($data[$key]);
                } else {
                    $save[$key] = iconv($this->charset, 'UTF-8', \Kotchasan\Text::topic($data[$key]));
                }
            }
        }
        if (empty($this->keys)) {
            $this->datas[] = $save;
        } else {
            $keys = '';
            foreach ($this->keys as $item) {
                if ($save[$item] !== null && $save[$item] !== '') {
                    $keys .= $save[$item];
                } else {
                    $save = null;
                    continue;
                }
            }
            if (!empty($save) && !isset($this->datas[$keys])) {
                $this->datas[$keys] = $save;
            }
        }
    }
}
