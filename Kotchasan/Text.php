<?php
/**
 * @filesource Kotchasan/Text.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

/**
 * String functions
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Text
{
    /**
     * ฟังก์ชั่น ตัดสตริงค์ตามความยาวที่กำหนด
     * หากข้อความที่นำมาตัดยาวกว่าที่กำหนด จะตัดข้อความที่เกินออก และเติม .. ข้างท้าย
     *
     * @assert ('สวัสดี ประเทศไทย', 8) [==] 'สวัสดี..'
     * @assert ('123456789', 8) [==] '123456..'
     *
     * @param string $source ข้อความ
     * @param int    $len    ความยาวของข้อความที่ต้องการ (จำนวนตัวอักษรรวมจุด)
     *
     * @return string
     */
    public static function cut($source, $len)
    {
        if (!empty($len)) {
            $len = (int) $len;
            $source = (mb_strlen($source) <= $len || $len < 3) ? $source : mb_substr($source, 0, $len - 2).'..';
        }

        return $source;
    }

    /**
     * ฟังก์ชั่น แปลงขนาดของไฟล์จาก byte เป็น kb mb
     * คืนค่าขนาดของไฟล์เป็น KB MB
     *
     * @assert (256) [==] '256 Bytes'
     * @assert (1024) [==] '1 KB'
     * @assert (1024 * 1024) [==] '1 MB'
     * @assert (1024 * 1024 * 1024) [==] '1 GB'
     * @assert (1024 * 1024 * 1024 * 1024) [==] '1 TB'
     *
     * @param int $bytes     ขนาดของไฟล์ เป็น byte
     * @param int $precision จำนวนหลักหลังจุดทศนิยม (default 2)
     *
     * @return string
     */
    public static function formatFileSize($bytes, $precision = 2)
    {
        $units = array('Bytes', 'KB', 'MB', 'GB', 'TB');
        if ($bytes <= 0) {
            return '0 Byte';
        } else {
            $bytes = max($bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $bytes /= pow(1024, $pow);

            return round($bytes, $precision).' '.$units[$pow];
        }
    }

    /**
     * ฟังก์ชั่น HTML highlighter
     * แปลง BBCode
     * แปลงข้อความ http เป็นลิงค์
     * คืนค่าข้อความ
     *
     * @param string $detail ข้อความ
     *
     * @return string
     */
    public static function highlighter($detail)
    {
        $detail = preg_replace_callback('/\[([uo]l)\](.*)\[\/\\1\]/is', function ($match) {
            return '<'.$match[1].'><li>'.preg_replace('/<br(\s\/)?>/is', '</li><li>', $match[2]).'</li></'.$match[1].'>';
        }, $detail);
        $patt[] = '/\[(i|dfn|b|strong|u|em|ins|del|sub|sup|small|big)\](.*)\[\/\\1\]/is';
        $replace[] = '<\\1>\\2</\\1>';
        $patt[] = '/\[color=([#a-z0-9]+)\]/i';
        $replace[] = '<span style="color:\\1">';
        $patt[] = '/\[size=([0-9]+)(px|pt|em|\%)\]/i';
        $replace[] = '<span style="font-size:\\1\\2">';
        $patt[] = '/\[\/(color|size)\]/i';
        $replace[] = '</span>';
        $patt[] = '/\[url\](.*)\[\/url\]/i';
        $replace[] = '<a href="\\1" target="_blank">\\1</a>';
        $patt[] = '/\[url=(ftp|https?):\/\/(.*)\](.*)\[\/url\]/i';
        $replace[] = '<a href="\\1://\\2" target="_blank">\\3</a>';
        $patt[] = '/\[url=(\/)?(.*)\](.*)\[\/url\]/i';
        $replace[] = '<a href="'.WEB_URL.'\\2" target="_blank">\\3</a>';
        $patt[] = '/([^["]]|\r|\n|\s|\t|^)((ftp|https?):\/\/([a-z0-9\.\-_]+)\/([^\s<>\"\']{1,})([^\s<>\"\']{20,20}))/i';
        $replace[] = '\\1<a href="\\2" target="_blank">\\3://\\4/...\\6</a>';
        $patt[] = '/([^["]]|\r|\n|\s|\t|^)((ftp|https?):\/\/([^\s<>\"\']+))/i';
        $replace[] = '\\1<a href="\\2" target="_blank">\\2</a>';
        $patt[] = '/(<a[^>]+>)(https?:\/\/[^\%<]+)([\%][^\.\&<]+)([^<]{5,})(<\/a>)/i';
        $replace[] = '\\1\\2...\\4\\5';
        $patt[] = '/\[youtube\]([a-z0-9-_]+)\[\/youtube\]/i';
        $replace[] = '<div class="youtube"><iframe src="//www.youtube.com/embed/\\1?wmode=transparent"></iframe></div>';

        return preg_replace($patt, $replace, $detail);
    }

    /**
     * แปลง & " ' < > \ { } $ เป็น HTML entities ใช้แทน htmlspecialchars() ของ PHP
     *
     * @param string $text
     * @param bool $double_encode true (default) แปลง รหัส HTML เช่น &amp; เป็น &amp;amp;, false ไม่แปลง
     *
     * @return string
     */
    public static function htmlspecialchars($text, $double_encode = true)
    {
        $str = preg_replace(array('/&/', '/"/', "/'/", '/</', '/>/', '/\\\/', '/\{/', '/\}/', '/\$/'), array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;', '&#92;', '&#x007B;', '&#x007D;', '&#36;'), $text);
        if (!$double_encode) {
            $str = preg_replace('/&(amp;([#a-z0-9]+));/i', '&\\2;', $str);
        }

        return $str;
    }

    /**
     * ฟังก์ชั่น ลบช่องว่าง และ ตัวอักษรขึ้นบรรทัดใหม่ ที่ติดกันเกินกว่า 1 ตัว
     * คืนค่าข้อความที่ไม่มีตัวอักษรขึ้นบรรทัดใหม่
     *
     * @assert (" \tทดสอบ\r\nภาษาไทย") [==] 'ทดสอบ ภาษาไทย'
     *
     * @param string $text ข้อความ
     * @param int    $len  จำนวนตัวอักษรสูงสุดที่ต้องการ, (default) คืนค่าทั้งหมด
     *
     * @return string
     */
    public static function oneLine($text, $len = 0)
    {
        return self::cut(trim(preg_replace('/[\r\n\t\s]+/', ' ', $text)), $len);
    }

    /**
     * รับค่าสำหรับ password อักขระทุกตัวไม่มีช่องว่าง
     *
     * @assert (" 0\n12   34\r\r6\t5ทดสอบ@#$&{}!?+_-=") [==] '0123465ทดสอบ@#$&{}!?+_-='
     *
     * @param string $text
     *
     * @return string
     */
    public static function password($text)
    {
        return preg_replace('/[^\w\@\#\$\&\{\}\!\?\+_\-=ก-ฮ]+/', '', $text);
    }

    /**
     * ลบตัวอักษรที่ไม่สามารถพิมพ์ได้ออก
     * ตั้งแต่ chr(128)-chr(255) หรือ \x80-\xFF ขึ้นไปจะถูกลบออก
     *
     * @assert (chr(0).chr(127).chr(128).chr(255)) [==] chr(0).chr(127)
     *
     * @param string $text
     *
     * @return string
     */
    public static function removeNonCharacters($text)
    {
        return preg_replace('/((?:[\x00-\x7F]|[\xC0-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF]{2}|[\xF0-\xF7][\x80-\xBF]{3}){1,100})|./x', '\\1', $text);
    }

    /**
     * ฟังก์ชั่นคืนค่าข้อความซ้ำๆตามจำนวนที่กำหนด
     *
     * @assert ('0', 10) [==] '0000000000'
     *
     * @param string $text  ข้อความหรือตัวอักษรที่ต้องการทำซ้ำ
     * @param int    $count จำนวนที่ต้องการ
     *
     * @return string
     */
    public static function repeat($text, $count)
    {
        $result = '';
        for ($i = 0; $i < $count; ++$i) {
            $result .= $text;
        }

        return $result;
    }

    /**
     * แทนที่ข้อความด้วยข้อมูลจากแอเรย์ รองรับข้อมูลรูปแบบแอเรย์ย่อยๆ
     *
     * @assert ("SELECT * FROM table WHERE id=:id AND lang IN (:lang, '')", array(':id' => 1, array(':lang' => 'th'))) [==] "SELECT * FROM table WHERE id=1 AND lang IN (th, '')"
     *
     * @param string $source  ข้อความต้นฉบับ
     * @param array  $replace ข้อความที่จะนำมาแทนที่ รูปแบบ array($key1 => $value1, $key2 => $value2) ข้อความใน $source ที่ตรงกับ $key จะถูกแทนที่ด้วย $value
     *
     * @return string
     */
    public static function replace($source, $replace)
    {
        if (!empty($replace)) {
            $keys = array();
            $values = array();
            ArrayTool::extract($replace, $keys, $values);
            $source = str_replace($keys, $values, $source);
        }

        return $source;
    }

    /**
     * ฟังก์ชั่น เข้ารหัส อักขระพิเศษ และ {} ก่อนจะส่งให้กับ textarea หรือ editor ตอนแก้ไข
     * & " ' < > { } ไม่แปลง รหัส HTML เช่น &amp; &#38;.
     *
     * @assert ('&"'."'<>{}&amp;&#38;") [==] "&amp;&quot;&#039;&lt;&gt;&#x007B;&#x007D;&amp;&#38;"
     *
     * @param string $text ข้อความ
     *
     * @return string
     */
    public static function toEditor($text)
    {
        return preg_replace(array('/&/', '/"/', "/'/", '/</', '/>/', '/{/', '/}/', '/&(amp;([\#a-z0-9]+));/'), array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;', '&#x007B;', '&#x007D;', '&\\2;'), $text);
    }

    /**
     * แปลง tag และ ลบช่องว่างไม่เกิน 1 ช่อง ไม่ขึ้นบรรทัดใหม่
     * เช่นหัวข้อของบทความ
     *
     * @assert (' ทด\/สอบ$'."\r\n\t".'<?php echo \'555\'?> ') [==] 'ทด&#92;/สอบ&#36; &lt;?php echo &#039;555&#039;?&gt;'
     * @assert ('&nbsp;') [==] '&amp;nbsp;'
     * @assert ('&nbsp;', false) [==] '&nbsp;'
     *
     * @param string $text
     * @param bool $double_encode true (default) แปลง รหัส HTML เช่น &amp; เป็น &amp;amp;, false ไม่แปลง
     *
     * @return string
     */
    public static function topic($text, $double_encode = true)
    {
        return trim(preg_replace('/[\r\n\s\t]+/', ' ', self::htmlspecialchars($text, $double_encode)));
    }

    /**
     * แปลง htmlspecialchars กลับเป็นอักขระปกติ
     *
     * @assert (\Kotchasan\Text::htmlspecialchars('&"\'<>\\{}$')) [==] '&"\'<>\\{}$'
     *
     * @param string $text
     *
     * @return string
     */
    public static function unhtmlspecialchars($text)
    {
        return str_replace(array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;', '&#92;', '&#x007B;', '&#x007D;', '&#36;'), array('&', '"', "'", '<', '>', '\\', '{', '}', '$'), $text);
    }

    /**
     * แปลง tag ไม่แปลง &amp;
     * และลบช่องว่างหัวท้าย
     * สำหรับ URL หรือ email
     *
     * @assert (" http://www.kotchasan.com?a=1&b=2&amp;c=3 ") [==] 'http://www.kotchasan.com?a=1&amp;b=2&amp;c=3'
     * @assert ("javascript:alert('xxx')") [==] 'alertxxx'
     * @assert ("http://www.xxx.com/javascript/") [==] 'http://www.xxx.com/javascript/'
     *
     * @return string
     */
    public static function url($text)
    {
        $text = preg_replace('/(^javascript:|[\(\)\'\"]+)/', '', trim($text));

        return self::htmlspecialchars($text, false);
    }

    /**
     * ฟังก์ชั่นรับค่าสำหรับใช้เป็น username
     * รองรับอีเมล ตัวเลข (หมายเลขโทรศัพท์) @ - _ . เท่านั้น.
     *
     * @assert (' ad_min@demo.com') [==] 'ad_min@demo.com'
     * @assert ('012 3465') [==] '0123465'
     *
     * @param string $text
     *
     * @return string
     */
    public static function username($text)
    {
        return preg_replace('/[^a-zA-Z0-9@\.\-_]+/', '', $text);
    }
}
