<?php
/**
 * @filesource Kotchasan/Validator.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

/**
 * คลาสสำหรับตรวจสอบความถูกต้องของตัวแปรต่างๆ
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Validator extends \Kotchasan\KBase
{
    /**
     * ตรวจสอบความถูกของอีเมล
     * คืนค่า true ถ้ารูปแบบอีเมลถูกต้อง
     *
     * @assert ('admin@localhost.com') [==] true
     * @assert ('admin@localhost') [==] true
     * @assert ('ทดสอบ@localhost') [==] false
     *
     * @param string $email
     *
     * @return bool
     */
    public static function email($email)
    {
        if (function_exists('idn_to_ascii') && preg_match('/(.*)@(.*)/', $email, $match)) {
            // โดเมนภาษาไทย
            $email = $match[1].'@'.idn_to_ascii($match[2], 0, INTL_IDNA_VARIANT_UTS46);
        }
        if (preg_match('/^[a-zA-Z0-9\._\-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/sD', $email)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * ฟังก์ชั่นตรวจสอบไฟล์อัปโหลดว่าเป็นรูปภาพหรือไม่
     * คืนค่าแอเรย์ [width, height, mime] ของรูปภาพ หรือ  false ถ้าไม่ใช่รูปภาพ.
     *
     * @param array $excepts     ชนิดของไฟล์ที่ยอมรับเช่น array('jpg', 'gif', 'png')
     * @param array $file_upload รับค่ามาจาก $_FILES
     *
     * @return array|bool
     */
    public static function isImage($excepts, $file_upload)
    {
        // ext
        $imageinfo = explode('.', $file_upload['name']);
        $imageinfo = array('ext' => strtolower(end($imageinfo)));
        if (in_array($imageinfo['ext'], $excepts)) {
            // Exif
            $info = getimagesize($file_upload['tmp_name']);
            if ($info[0] == 0 || $info[1] == 0 || !Mime::check($excepts, $info['mime'])) {
                return false;
            } else {
                $imageinfo['width'] = $info[0];
                $imageinfo['height'] = $info[1];
                $imageinfo['mime'] = $info['mime'];

                return $imageinfo;
            }
        } else {
            return false;
        }
    }
}
