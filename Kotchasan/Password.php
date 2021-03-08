<?php
/**
 * @filesource Kotchasan/Password.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

/**
 * Password Class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Password
{
    /**
     * ฟังก์ชั่น ถอดรหัสข้อความ
     * คืนค่าข้อความที่ถอดรหัสแล้ว
     *
     * @assert (Password::encode("ทดสอบภาษาไทย", 12345678), 12345678) [==] "ทดสอบภาษาไทย"
     * @assert (Password::encode(1234, 12345678), 12345678) [==] 1234
     *
     * @param string $string ข้อความที่เข้ารหัสจาก encode()
     * @param string $password คีย์สำหรับการเข้ารหัส
     *
     * @return string
     */
    public static function decode($string, $password)
    {
        list($data, $iv) = explode('::', base64_decode($string), 2);
        return openssl_decrypt($data, 'aes-256-cbc', $password, 0, $iv);
    }

    /**
     * ฟังก์ชั่น เข้ารหัสข้อความ
     * คืนค่าข้อความที่เข้ารหัสแล้ว
     *
     * @param string $string ข้อความที่ต้องการเข้ารหัส
     * @param string $password คีย์สำหรับการเข้ารหัส
     *
     * @return string
     */
    public static function encode($string, $password)
    {
        $iv = self::uniqid(16);
        $encrypted = openssl_encrypt($string, 'aes-256-cbc', $password, 0, $iv);
        return base64_encode($encrypted.'::'.$iv);
    }

    /**
     * สร้าง Sign สำหรับส่งให้ API
     *
     * @param array $params
     * @param string $secret
     *
     * @return string
     */
    public static function generateSign($params, $secret)
    {
        // เรียงลำดับตามคีย์
        ksort($params);
        // นำข้อมูลมาต่อกัน
        $data = '';
        foreach ($params as $k => $v) {
            $data .= $k.$v;
        }
        // คืนค่าข้อความเข้ารหัส
        return strtoupper(hash_hmac('sha256', $data, $secret));
    }

    /**
     * สร้าง password แบบสุ่ม
     *
     * @param int $length ความยาวของ password ที่ต้องการ
     *
     * @return string
     */
    public static function uniqid($length = 13)
    {
        if (function_exists('random_bytes')) {
            $token = random_bytes(ceil($length / 2));
        } else {
            $token = openssl_random_pseudo_bytes(ceil($length / 2));
        }
        return substr(bin2hex($token), 0, $length);

    }
}
