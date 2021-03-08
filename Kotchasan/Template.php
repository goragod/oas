<?php
/**
 * @filesource Kotchasan/Template.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

/**
 * Template engine.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Template
{
    /**
     * จำนวนคอลัมน์ สำหรับการแสดงผลด้วย Grid.
     *
     * @var int
     */
    protected $cols = 0;
    /**
     * แอเรย์ของข้อมูล.
     *
     * @var array
     */
    protected $items;
    /**
     * ตัวแปรสำหรับการขึ้นแถวใหม่ (Grid).
     *
     * @var int
     */
    protected $num;
    /**
     * ชื่อ template ที่กำลังใช้งานอยู่ รวมโฟลเดอร์ที่เก็บ template ด้วย
     * นับแต่ DOCUMENT_ROOT เช่น skin/default/.
     *
     * @var string
     */
    protected static $src;
    /**
     * ข้อมูล template.
     *
     * @var string
     */
    private $skin;

    /**
     * ฟังก์ชั่นกำหนดค่าตัวแปรของ template
     * ฟังก์ชั่นนี้จะแทนที่ตัวแปรที่ส่งทั้งหมดลงใน template ทันที.
     *
     * @param array $array ชื่อที่ปรากฏใน template รูปแบบ array(key1=>val1,key2=>val2)
     *
     * @return \static
     */
    public function add($array)
    {
        if ($this->cols > 0 && $this->num == 0) {
            $this->items[] = "</div>\n<div class=row>";
            $this->num = $this->cols;
        }
        $this->items[] = self::pregReplace(array_keys($array), array_values($array), $this->skin);
        --$this->num;

        return $this;
    }

    /**
     * โหลด template
     * ครั้งแรกจะตรวจสอบไฟล์จาก module ถ้าไม่พบ จะใช้ไฟล์จาก owner.
     *
     * @assert ('', '', 'FileNotFound')->isEmpty() [==] true
     *
     * @param string $owner  ชื่อโมดูลที่ติดตั้ง
     * @param string $module ชื่อโมดูล
     * @param string $name   ชื่อ template ไม่ต้องระบุนามสกุลของไฟล์
     *
     * @return \static
     */
    public static function create($owner, $module, $name)
    {
        return self::createFromHTML(self::load($owner, $module, $name));
    }

    /**
     * โหลด template จากไฟล์.
     *
     * @assert ('FileNotFound') [throws] InvalidArgumentException
     *
     * @param string $filename
     *
     * @throws \InvalidArgumentException ถ้าไม่พบไฟล์
     *
     * @return \static
     */
    public static function createFromFile($filename)
    {
        if (is_file($filename)) {
            return self::createFromHTML(file_get_contents($filename));
        } else {
            throw new \InvalidArgumentException('Template file not found');
        }
    }

    /**
     * สร้าง template จาก HTML.
     *
     * @param string $html
     *
     * @return \static
     */
    public static function createFromHTML($html)
    {
        $obj = new static();
        $obj->skin = $html;
        $obj->items = array();
        $obj->num = -1;

        return $obj;
    }

    /**
     * คืนค่าไดเร็คทอรี่ของ template ตั้งแต่ DOCUMENT_ROOT เช่น skin/default/.
     *
     * @return string
     */
    public static function get()
    {
        return self::$src;
    }

    /**
     * ฟังก์ชั่นตรวจสอบว่ามีการ add ข้อมูลมาหรือเปล่า
     * คืนค่า true ถ้ามีการเรียกใช้คำสั่ง add มาก่อนหน้า, หรือ false ถ้าไม่ใช่.
     *
     * @return bool
     */
    public function hasItem()
    {
        return empty($this->items) ? false : true;
    }

    /**
     * กำหนด template ที่ต้องการ.
     *
     * @param string $skin ไดเร็คทอรี่ของ template ตั้งแต่ DOCUMENT_ROOT ไม่ต้องมี / ปิดท้าย เช่น skin/default
     */
    public static function init($skin)
    {
        self::$src = $skin == '' ? '' : $skin.'/';
    }

    /**
     * ฟังก์ชั่นใส่ HTML ลงใน template ตรงๆ
     * ใช้สำหรับแทรก HTML ลงระหว่างแต่ละรายการ.
     *
     * @param string $html โค้ด HTML
     *
     * @return \static
     */
    public function insertHTML($html)
    {
        $this->items[] = $html;

        return $this;
    }

    /**
     * ตรวจสอบว่ามีไฟล์ Template ถูกเลือกหรือไม่
     * คืนค่า true ถ้าไม่พบไฟล์ Template หรือ Template ว่างเปล่า, อื่นๆคืนค่า False.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->skin == '';
    }

    /**
     * โหลด template
     * ครั้งแรกจะตรวจสอบไฟล์จาก $module ถ้าไม่พบ จะใช้ไฟล์จาก $owner
     * ถ้าไม่พบคืนค่าว่าง.
     *
     * @param string $owner  ชื่อโมดูลที่ติดตั้ง
     * @param string $module ชื่อโมดูลที่ลงทะเบียน
     * @param string $name   ชื่อ template ไม่ต้องระบุนามสกุลของไฟล์
     *
     * @return string
     */
    public static function load($owner, $module, $name)
    {
        $src = APP_PATH.self::$src;
        if ($module != '' && is_file($src.$module.'/'.$name.'.html')) {
            return file_get_contents($src.$module.'/'.$name.'.html');
        } elseif ($owner != '' && is_file($src.$owner.'/'.$name.'.html')) {
            return file_get_contents($src.$owner.'/'.$name.'.html');
        } elseif (is_file($src.$name.'.html')) {
            return file_get_contents($src.$name.'.html');
        }

        return '';
    }

    /**
     * ฟังก์ชั่น preg_replace รองรับถึง PHP7.
     *
     * @assert ('/{TITLE}/', 'Title', '<b>{TITLE}</b>') [==] '<b>Title</b>'
     * @assert ('/{LNG_([\w\s\.\-\'\(\),%\/:&\#;]+)}/e', '\Kotchasan\Language::parse(array(1=>"$1"))', '<b>{LNG_Language test}</b>') [==] '<b>Language test</b>'
     *
     * @param array  $patt    คีย์ใน template
     * @param array  $replace ข้อความที่จะถูกแทนที่ลงในคีย์
     * @param string $skin    template
     *
     * @return string
     */
    public static function pregReplace($patt, $replace, $skin)
    {
        if (!is_array($patt)) {
            $patt = array($patt);
        }
        if (!is_array($replace)) {
            $replace = array($replace);
        }
        foreach ($patt as $i => $item) {
            if (preg_match('/(.*\/(.*?))[e](.*?)$/', $item, $patt) && preg_match('/^([\\\\a-z0-9]+)::([a-z0-9_\\\\]+).*/i', $replace[$i], $func)) {
                $skin = preg_replace_callback($patt[1].$patt[3], array($func[1], $func[2]), $skin);
            } else {
                $skin = preg_replace($item, $replace[$i], $skin);
            }
        }

        return $skin;
    }

    /**
     * คืนค่า HTML ถ้าไม่พบ template คืนค่าว่าง.
     *
     * @return string
     */
    public function render()
    {
        if ($this->cols === 0) {
            // template
            return empty($this->items) ? $this->skin : implode("\n", $this->items);
        } elseif (!empty($this->items)) {
            // grid
            return "<div class=row>\n".implode("\n", $this->items)."\n</div>";
        }

        return '';
    }
}
