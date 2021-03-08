<?php
/**
 * @filesource Gcms/Config.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Gcms;

/**
 * Config Class สำหรับ GCMS.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Config extends \Kotchasan\Config
{
    /**
     * กำหนดอายุของแคช (วินาที)
     * 0 หมายถึงไม่มีการใช้งานแคช.
     *
     * @var int
     */
    public $cache_expire = 5;
    /**
     * สีของสมาชิกตามสถานะ.
     *
     * @var array
     */
    public $color_status = array(
        0 => '#259B24',
        1 => '#FF0000',
        2 => '#FF6600',
        3 => '#3366FF',
        4 => '#902AFF',
        5 => '#660000',
        6 => '#336600',
    );
    /**
     * ถ้ากำหนดเป็น true บัญชี Facebook จะเป็นบัญชีตัวอย่าง
     * ได้รับสถานะแอดมิน (สมาชิกใหม่) แต่อ่านได้อย่างเดียว.
     *
     * @var bool
     */
    public $demo_mode = false;
    /**
     * App ID สำหรับการเข้าระบบด้วย Facebook https://gcms.in.th/howto/การขอ_app_id_จาก_facebook.html.
     *
     * @var string
     */
    public $facebook_appId = '';
    /**
     * Client ID สำหรับการเข้าระบบโดย Google.
     *
     * @var string
     */
    public $google_client_id = '';
    /**
     * รายชื่อฟิลด์จากตารางสมาชิก สำหรับตรวจสอบการ login.
     *
     * @var array
     */
    public $login_fields = array('username');
    /**
     * สถานะสมาชิก
     * 0 สมาชิกทั่วไป
     * 1 ผู้ดูแลระบบ.
     *
     * @var array
     */
    public $member_status = array(
        0 => 'สมาชิก',
        1 => 'ผู้ดูแลระบบ',
    );
    /*
     * คีย์สำหรับการเข้ารหัส ควรแก้ไขให้เป็นรหัสของตัวเอง
     * ตัวเลขหรือภาษาอังกฤษเท่านั้น ไม่น้อยกว่า 10 ตัว
     *
     * @var string
     */
    /**
     * @var string
     */
    public $password_key = '1234567890';
    /**
     * ไดเร็คทอรี่ template ที่ใช้งานอยู่ ตั้งแต่ DOCUMENT_ROOT
     * ไม่ต้องมี / ทั้งเริ่มต้นและปิดท้าย
     * เช่น skin/default.
     *
     * @var string
     */
    public $skin = 'skin/default';
    /**
     * ไอคอนเริ่มต้นของไซต์ (โลโก)
     *
     * @var string
     */
    public $default_icon = 'icon-office';
    /**
     * สีหลักของเว็บไซต์
     *
     * @var string
     */
    public $bg_color = '#3498DB';
    /**
     * สีของเมนูบนสุด+footer
     *
     * @var string
     */
    public $color = '#FFFFFF';
    /**
     * สามารถขอรหัสผ่านในหน้าเข้าระบบได้.
     *
     * @var bool
     */
    public $user_forgot = true;
    /**
     * บุคคลทั่วไป สามารถสมัครสมาชิกได้.
     *
     * @var bool
     */
    public $user_register = true;
    /**
     * ส่งอีเมลต้อนรับ เมื่อบุคคลทั่วไปสมัครสมาชิก
     *
     * @var bool
     */
    public $welcome_email = true;
    /**
     * การเข้าระบบต่อ 1 user
     * ค่าเริ่มต้น true (แนะนำ) สามารถเข้าระบบได้เพียงคนเดียวต่อ 1 user คนที่อยู่ในระบบก่อนหน้าจะถูกบังคับให้ออกจากระบบ.
     *
     * @var bool
     */
    public $member_only = true;
    /**
     * VAT.
     *
     * @var int
     */
    public $vat = 7;

    /**
     * สถานะซื้อ
     *
     * @var array
     */
    public $buy_status = array('PO', 'RET');

    /**
     * รายการรับเข้า Stock
     *
     * @var array
     */
    public $in_stock_status = array('IN', 'RET');
    /**
     * สถานะขาย
     *
     * @var array
     */
    public $sell_status = array('OUT', 'QUO');

    /**
     * รายการตัด Stock
     *
     * @var array
     */
    public $out_stock_status = array('OUT');
}
