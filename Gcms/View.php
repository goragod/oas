<?php
/**
 * @filesource Gcms/View.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Gcms;

use Kotchasan\Language;

/**
 * View base class สำหรับ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{
    /**
     * ฟังก์ชั่น แทนที่ query string ด้วยข้อมูลจาก GET และ POST สำหรับส่งต่อไปยัง URL ถัดไป
     * โดยการรับค่าจาก preg_replace
     * คืนค่า URL
     *
     * @param array $f รับค่าจากตัวแปรที่ส่งมาจาก preg_replace มาสร้าง query string
     *
     * @return string
     */
    public static function back($f)
    {
        $query_url = array();
        foreach (self::$request->getQueryParams() as $key => $value) {
            if ($value != '' && !preg_match('/^(module|.*?username|.*?password)$/', $key) && (is_string($value) || is_int($value))) {
                $key = ltrim($key, '_');
                $query_url[$key] = $value;
            }
        }
        foreach (self::$request->getParsedBody() as $key => $value) {
            if ($value != '' && !preg_match('/^(module|.*?username|.*?password)$/', $key) && (is_string($value) || is_int($value))) {
                $key = ltrim($key, '_');
                $query_url[$key] = $value;
            }
        }
        if (isset($f[2])) {
            foreach (explode('&', $f[2]) as $item) {
                if (preg_match('/^([a-zA-Z0-9_\-]+)=([^$]{1,})$/', $item, $match)) {
                    if ($match[2] === '0') {
                        unset($query_url[$match[1]]);
                    } else {
                        $query_url[$match[1]] = $match[2];
                    }
                }
            }
        }
        return WEB_URL.'index.php?'.http_build_query($query_url, '', '&amp;');
    }

    /**
     * ouput เป็น HTML
     *
     * @param string|null $template HTML Template ถ้าไม่กำหนด (null) จะใช้ index.html
     *
     * @return string
     */
    public function renderHTML($template = null)
    {
        // เนื้อหา
        parent::setContents(array(
            // url สำหรับกลับไปหน้าก่อนหน้า
            '/{BACKURL(\?([a-zA-Z0-9=&\-_@\.]+))?}/e' => '\Gcms\View::back',
            /* ภาษา */
            '/{LNG_([^}]+)}/e' => '\Kotchasan\Language::parse(array(1=>"$1"))',
            /* ภาษา ที่ใช้งานอยู่ */
            '/{LANGUAGE}/' => Language::name(),
        ));
        return parent::renderHTML($template);
    }

    /**
     * คืนค่าลิงค์รูปแบบโทรศัพท์
     *
     * @param string $phone_number
     *
     * @return string
     */
    public static function showPhone($phone_number)
    {
        $result = array();
        foreach (explode(',', $phone_number) as $phone) {
            $result[] = '<a href="tel:'.$phone.'">'.$phone.'</a>';
        }
        return empty($result) ? '' : implode(', ', $result);
    }
}
