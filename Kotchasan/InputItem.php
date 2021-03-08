<?php
/**
 * @filesource Kotchasan/InputItem.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

/**
 * Input Object
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class InputItem
{
    /**
     * ตัวแปรบอกประเภท Input เช่น GET POST SESSION COOKIE
     *
     * @var string|null
     */
    protected $type;
    /**
     * ตัวแปรเก็บค่าของ Object
     *
     * @var mixed
     */
    protected $value;

    /**
     * Class Constructer
     *
     * @param mixed       $value null (default)
     * @param string|null $type  ประเภท Input เช่น GET POST SESSION COOKIE หรือ null ถ้าไม่ได้มาจากรายการข้างต้น
     */
    public function __construct($value = null, $type = null)
    {
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * คืนค่าตามข้อมูลที่ส่งมา
     *
     * @return mixed
     */
    public function all()
    {
        return $this->value;
    }

    /**
     * คืนค่า ค่าสี String หรือ แอเรย์ของ String
     *
     * @assert create('#000')->color() [==] '#000'
     * @assert create('red')->color() [==] 'red'
     *
     * @return string|array
     */
    public function color()
    {
        return $this->filter('\#a-zA-Z0-9');
    }

    /**
     * สร้าง Object
     *
     * @param mixed       $value
     * @param string|null $type  ประเภท Input เช่น GET POST SESSION COOKIE หรือ null ถ้าไม่ได้มาจากรายการข้างต้น
     *
     * @return \static
     */
    public static function create($value, $type = null)
    {
        return new static($value, $type);
    }

    /**
     * วันที่และเวลา
     * คืนค่า null ถ้าข้อมูลวันที่ว่างเปล่าหรือมีรูปแบบไม่ถูกต้อง
     *
     * @assert create('2016-01-01 20:20:20')->date() [==] '2016-01-01 20:20:20'
     * @assert create('2016-01-01   20:20:20')->date() [==] '2016-01-01   20:20:20'
     * @assert create('2016-01-01   20:20:20')->date(true) [==] '2016-01-01 20:20:20'
     * @assert create('20:20:20')->date() [==] '20:20:20'
     * @assert create('20:20')->date() [==] '20:20'
     * @assert create('20:20')->date(true) [==] '20:20:00'
     * @assert create('2016-01-01')->date() [==] '2016-01-01'
     * @assert create('')->date() [==] null
     * @assert create(null)->date() [==] null
     *
     * @param bool $strict true ตรวจสอบความถูกต้องของวันที่ด้วย, false (default) ไม่ต้องตรวจสอบ
     *
     * @return string|array|null
     */
    public function date($strict = false)
    {
        $ret = $this->filter('\d\s\-:');
        if ($strict) {
            if (preg_match('/^([0-9]{4,4}\-[0-9]{1,2}\-[0-9]{1,2})?[\s]{0,}([0-9]{1,2}:[0-9]{1,2})?(:[0-9]{1,2})?$/', $ret, $match)) {
                $ret = empty($match[1]) ? '' : $match[1];
                if (!empty($match[2])) {
                    $ret .= ($ret == '' ? '' : ' ').(empty($match[2]) ? '' : $match[2].(empty($match[3]) ? ':00' : $match[3]));
                }
            } else {
                $ret = null;
            }
        }
        return empty($ret) ? null : $ret;
    }

    /**
     * เวลา
     * คืนค่า null ถ้าข้อมูลเวลาว่างเปล่า หรือเท่าบ --:--.
     *
     * @assert create('20:20:20')->time() [==] '20:20:20'
     * @assert create('--:--')->time() [==] null
     * @assert create('')->time() [==] null
     * @assert create('20:20:20')->time() [==] '20:20:20'
     * @assert create('20:20')->time() [==] '20:20'
     * @assert create('20:20')->time(true) [==] '20:20:00'
     *
     * @param bool $strict true ตรวจสอบความถูกต้องของวันที่ด้วย, false (default) ไม่ต้องตรวจสอบ
     *
     * @return string|array|null
     */
    public function time($strict = false)
    {
        if (!empty($this->value) && preg_match('/^([0-9]{1,2}:[0-9]{1,2})?(:[0-9]{1,2})?$/', $this->value, $match)) {
            if (empty($match[2])) {
                $match[2] = $strict ? ':00' : '';
            }
            return $match[1].$match[2];
        }
        return null;
    }

    /**
     * ลบ tag, BBCode ออก ให้เหลือแต่ข้อความล้วน
     * ลบช่องว่างไม่เกิน 1 ช่อง ไม่ขึ้นบรรทัดใหม่
     * และลบช่องว่างหัวท้าย
     * ใช้เป็น description
     *
     *
     * @assert create("ท.ด(ส     )อ\"บ'\r\n\t<?php echo '555'?>")->description() [==] 'ท.ด(ส )อ บ'
     * @assert create('ทดสอบ<style>body {color: red}</style>')->description() [==] 'ทดสอบ'
     * @assert create('ทดสอบ<b>ตัวหนา</b>')->description() [==] 'ทดสอบตัวหนา'
     * @assert create('ทดสอบ{LNG_Language name}')->description() [==] 'ทดสอบ'
     * @assert create('ทดสอบ[code]ตัวหนา[/code]')->description() [==] 'ทดสอบ'
     * @assert create('ทดสอบ[b]ตัวหนา[/b]')->description() [==] 'ทดสอบตัวหนา'
     * @assert create('2 > 1 < 3 > 2{WIDGET_XXX}')->description() [==] '2 > 1 < 3 > 2'
     * @assert create('ทดสอบ<!--WIDGET_XXX-->')->description() [==] 'ทดสอบ'
     * @assert create('ท&amp;ด&quot;\&nbsp;/__ส-อ+บ')->description() [==] 'ท ด \ /__ส-อ+บ'
     * @assert create('ภาคภูมิ')->description(2) [==] 'ภา'
     * @assert create('U1.username ทดสอบภาษาไทย')->description(5) [throws] \Kotchasan\InputItemException
     *
     * @param int $len ความยาวของ description หมายถึงคืนค่าทั้งหมด
     *
     * @return string|array
     */
    public function description($len = 0)
    {
        $patt = array(
            /* style */
            '@<(script|style)[^>]*?>.*?</\\1>@isu' => '',
            /* tag */
            '@<[a-z\/\!\?][^>]{0,}>@isu' => '',
            /* keywords */
            '/{(WIDGET|LNG)_[\w\s\.\-\'\(\),%\/:&\#;]+}/su' => '',
            /* BBCode (code) */
            '/(\[code(.+)?\]|\[\/code\]|\[ex(.+)?\])/ui' => '',
            /* BBCode ทั่วไป [b],[i] */
            '/\[([a-z]+)([\s=].*)?\](.*?)\[\/\\1\]/ui' => '\\3',
            /* ตัวอักษรที่ไม่ต้องการ */
            '/(&rdquo;|&quot;|&nbsp;|&amp;|[\r\n\s\t\"\']){1,}/isu' => ' ',
        );
        $text = trim(preg_replace(array_keys($patt), array_values($patt), $this->value));
        return $this->checkValue($this->cut($text, $len));
    }

    /**
     * ลบ PHP tag และแปลง \ { } สำหรับใช้รับข้อมูลจาก editor
     * เช่นเนื้อหาของบทความ
     *
     * @assert create('{ทด\/สอบ<?php echo "555"?>}')->detail() [==] '&#x007B;ทด&#92;/สอบ&#x007D;'
     * @assert create('<?=555?>U1.username')->detail() [throws] \Kotchasan\InputItemException
     *
     * @return string|array
     */
    public function detail()
    {
        return $this->checkValue(preg_replace(
            array('#<\?(.*?)\?>#is', '#\{#', '#\}#', '#\\\#'),
            array('', '&#x007B;', '&#x007D;', '&#92;'),
            $this->value
        ));
    }

    /**
     * ตรวจสอบว่ามีตัวแปรส่งมาหรือไม่
     * คืนค่า true ถ้ามีตัวแปรส่งมา
     *
     * @return bool
     */
    public function exists()
    {
        return $this->type !== null;
    }

    /**
     * ฟังก์ชั่นแทนที่อักขระที่ไม่ต้องการ
     *
     * @assert create('admin,1234')->filter('0-9a-zA-Z,') [==] 'admin,1234'
     * @assert create('adminกข,12ฟ34')->filter('0-9a-zA-Z,') [==] 'admin,1234'
     * @assert create('U1.username')->filter('a-zA-Z0-9\.') [throws] \Kotchasan\InputItemException
     *
     * @param string $format  Regular Expression อักขระที่ยอมรับ เช่น \d\s\-:
     * @param string $replace ข้อความแทนที่
     *
     * @return string|array
     */
    public function filter($format, $replace = '')
    {
        return $this->checkValue(trim(preg_replace('/[^'.$format.']/', $replace, $this->value)));
    }

    /**
     * ตรวจสอบว่ามาจาก $_COOKIE หรือไม่
     * คืนค่า true ถ้ามาจาก $_COOKIE.
     *
     * @return bool
     */
    public function isCookie()
    {
        return $this->type === 'COOKIE';
    }

    /**
     * ตรวจสอบว่ามาจาก $_GET หรือไม่
     * คืนค่า true ถ้ามาจาก $_GET
     *
     * @return bool
     */
    public function isGet()
    {
        return $this->type === 'GET';
    }

    /**
     * ตรวจสอบว่ามาจาก $_POST หรือไม่
     * คืนค่า true ถ้ามาจาก $_POST
     *
     * @return bool
     */
    public function isPost()
    {
        return $this->type === 'POST';
    }

    /**
     * ตรวจสอบว่ามาจาก $_SESSION หรือไม่
     * คืนค่า true ถ้ามาจาก $_SESSION
     *
     * @return bool
     */
    public function isSession()
    {
        return $this->type === 'SESSION';
    }

    /**
     * ลบ tags และ ลบช่องว่างไม่เกิน 1 ช่อง ไม่ขึ้นบรรทัดใหม่
     * และลบช่องว่างหัวท้าย
     * ใช้เป็น tags หรือ keywords
     *
     * @assert create("<b>ทด</b>   \r\nสอบ")->keywords() [==] 'ทด สอบ'
     * @assert create('<b>U1.username</b> ทดสอบ')->keywords(5) [throws] \Kotchasan\InputItemException
     *
     * @param int $len ความยาวของ keywords หมายถึงคืนค่าทั้งหมด
     *
     * @return string|array
     */
    public function keywords($len = 0)
    {
        $text = trim(preg_replace('/[\r\n\s\t\"\'<>]{1,}/isu', ' ', strip_tags($this->value)));
        return $this->checkValue($this->cut($text, $len));
    }

    /**
     * ตัวเลข หรือ แอเรย์ของ ตัวเลข
     *
     * @assert create(12345)->number() [==] '12345'
     * @assert create(0.12345)->number() [==] '012345'
     * @assert create('ทด0123สอ4บ5')->number() [==] '012345'
     *
     * @return string|array
     */
    public function number()
    {
        return $this->filter('\d');
    }

    /**
     * รับค่าสำหรับ password อักขระทุกตัวไม่มีช่องว่าง
     *
     * @return string|array
     */
    public function password()
    {
        return $this->checkValue(Text::password($this->value));
    }

    /**
     * ฟังก์ชั่นรับข้อความ ยอมรับอักขระทั้งหมด
     * และแปลง ' เป็น &#39;
     * และลบช่องว่างหัวท้าย
     *
     * @assert create("ทด'สอบ")->quote() [==] "ทด&#39;สอบ"
     * @assert create(' U1.username ')->quote() [throws] \Kotchasan\InputItemException
     *
     * @return string|array
     */
    public function quote()
    {
        return $this->checkValue(str_replace("'", '&#39;', trim($this->value)));
    }

    /**
     * ฟังก์ชั่น แปลง & " ' < > \ เป็น HTML entities
     * และลบช่องว่างหัวท้าย
     * ใช้แปลงค่าที่รับจาก input ที่ไม่ยอมรับ tag
     *
     * @assert create(" ทด\/สอบ<?php echo '555'?> ")->text() [==] 'ทด&#92;/สอบ&lt;?php echo &#039;555&#039;?&gt;'
     * @assert create('U1.username')->text() [throws] \Kotchasan\InputItemException
     *
     * @return string|array
     */
    public function text()
    {
        return $this->checkValue(trim(Text::htmlspecialchars($this->value)));
    }

    /**
     * แปลง < > \ { } เป็น HTML entities และแปลง \n เป็น <br>
     * และลบช่องว่างหัวท้าย
     * ใช้รับข้อมูลที่มาจาก textarea
     *
     * @assert create("ทด\/สอบ\n<?php echo '$555'?>")->textarea() [==] "ทด&#92;/สอบ\n&lt;?php echo '&#36;555'?&gt;"
     * @assert create('U1.username')->textarea() [throws] \Kotchasan\InputItemException
     *
     * @return string|array
     */
    public function textarea()
    {
        return $this->checkValue(trim(preg_replace(
            array('/</s', '/>/s', '/\\\/s', '/\{/', '/\}/', '/\$/'),
            array('&lt;', '&gt;', '&#92;', '&#x007B;', '&#x007D;', '&#36;'),
            $this->value
        )));
    }

    /**
     * คืนค่าเป็น boolean หรือ แอเรย์ของ boolean
     *
     * @assert create(true)->toBoolean() [==] 1
     * @assert create(false)->toBoolean() [==] 0
     * @assert create(1)->toBoolean() [==] 1
     * @assert create(0)->toBoolean() [==] 0
     * @assert create(null)->toBoolean() [==] 0
     *
     * @return bool|array
     */
    public function toBoolean()
    {
        return empty($this->value) ? 0 : 1;
    }

    /**
     * คืนค่าเป็น double
     *
     * @assert create(0.454)->toDouble() [==] 0.454
     * @assert create(0.545)->toDouble() [==] 0.545
     * @assert create('15,362.454')->toDouble() [==] 15362.454
     *
     * @return float|array
     */
    public function toDouble()
    {
        return (float) str_replace(',', '', $this->value);
    }

    /**
     * คืนค่าเป็น float หรือ แอเรย์ของ float
     *
     * @assert create(0.454)->toFloat() [==] 0.454
     * @assert create(0.545)->toFloat() [==] 0.545
     *
     * @return float|array
     */
    public function toFloat()
    {
        return (float) $this->value;
    }

    /**
     * คืนค่าเป็น integer หรือ แอเรย์ของ integer
     *
     * @assert create(0.454)->toInt() [==] 0
     * @assert create(2.945)->toInt() [==] 2
     *
     * @return int|array
     */
    public function toInt()
    {
        return (int) $this->value;
    }

    /**
     * คืนค่าเป็น Object หรือ แอเรย์ของ Object
     *
     * @assert create('test')->toObject() [==] (object)'test'
     *
     * @return object|array
     */
    public function toObject()
    {
        return (object) $this->value;
    }

    /**
     * คืนค่าเป็น string หรือ แอเรย์ของ string หรือ null
     * ***ฟังก์ชั่นนี้ใช้สำหรับรับค่าเป็น string โดยไม่ผ่านการตรวจสอบใดๆทั้งสิ้น
     * ควรใช้อย่างระมัดระวัง***
     *
     * @assert create('ทดสอบ')->toString() [==] 'ทดสอบ'
     * @assert create('1')->toString() [==] '1'
     * @assert create(1)->toString() [==] '1'
     * @assert create(null)->toString() [==] null
     *
     * @return string|array|null
     */
    public function toString()
    {
        return $this->value === null ? null : (string) $this->value;
    }

    /**
     * คืนค่าเป็น array เท่านั้น
     * ถ้าไม่ใช่แอเรย์จะ error
     *
     * @assert create(array('one', 'two'))->toArray() [==] array('one', 'two')
     *
     * @return array
     */
    public function toArray()
    {
        return $this->value;
    }

    /**
     * แปลง tag และ ลบช่องว่างไม่เกิน 1 ช่อง ไม่ขึ้นบรรทัดใหม่
     * เช่นหัวข้อของบทความ
     *
     * @param bool $double_encode true (default) แปลง รหัส HTML เช่น &amp; เป็น &amp;amp;, false ไม่แปลง
     *
     * @assert create(' ทด\/สอบ'."\r\n\t".'<?php echo \'555\'?> ')->topic() [==] 'ทด&#92;/สอบ &lt;?php echo &#039;555&#039;?&gt;'
     * @assert create('U1.username')->topic() [throws] \Kotchasan\InputItemException
     *
     * @return string|array
     */
    public function topic($double_encode = true)
    {
        return $this->checkValue(Text::topic($this->value, $double_encode));
    }

    /**
     * แปลง tag ไม่แปลง &amp;
     * และลบช่องว่างหัวท้าย
     * สำหรับ URL หรือ email
     *
     * @assert create(" http://www.kotchasan.com?a=1&b=2&amp;c=3 ")->url() [==] 'http://www.kotchasan.com?a=1&amp;b=2&amp;c=3'
     * @assert create("javascript:alert('xxx')")->url() [==] 'alertxxx'
     * @assert create("http://www.xxx.com/javascript/")->url() [==] 'http://www.xxx.com/javascript/'
     * @assert create('U1.username')->url() [throws] \Kotchasan\InputItemException
     *
     * @return string|array
     */
    public function url()
    {
        return $this->checkValue(Text::url($this->value));
    }

    /**
     * รับค่าอีเมลและหมายเลขโทรศัพท์เท่านั้น
     *
     * @assert create(' admin@demo.com')->username() [==] 'admin@demo.com'
     * @assert create('012 3465')->username() [==] '0123465'
     * @assert create('U1.username')->username() [throws] \Kotchasan\InputItemException
     *
     * @return string|array
     */
    public function username()
    {
        return $this->checkValue(Text::username($this->value));
    }

    /**
     * ตัดสตริงค์
     *
     * @param string $str
     * @param int    $len ความยาวที่ต้องการ
     *
     * @return string
     */
    private function cut($str, $len)
    {
        if (!empty($len) && !empty($str)) {
            $str = mb_substr($str, 0, (int) $len);
        }
        return $str;
    }

    /**
     * ตรวจสอบว่าเป็นชื่อคอลัมน์หรือไม่
     * เช่น U.user_id U1.user_id
     *
     * @param string $value
     *
     * @return string
     *
     * @throws \Kotchasan\InputItemException ถ้า $value เป็นชื่อคอลัมน์
     */
    private function checkValue($value)
    {
        if (preg_match('/^[A-Z]{1,1}[0-9]{0,1}\.[a-z0-9_]+$/', $value)) {
            throw new \Kotchasan\InputItemException('Cannot use "'.$value.'"');
        }
        return $value;
    }
}
