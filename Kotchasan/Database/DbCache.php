<?php
/**
 * @filesource Kotchasan/Database/DbCache.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan\Database;

use Kotchasan\Cache\CacheItem as Item;
use Kotchasan\Cache\FileCache as Cache;
use Kotchasan\Text;

/**
 * Database Cache Class.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class DbCache
{
    /**
     * กำหนดการโหลดข้อมูลจากแคชอัตโนมัติ
     * 0 ไม่ใช้แคช
     * 1 โหลดและบันทึกแคชอัตโนมัติ
     * 2 โหลดข้อมูลจากแคชได้ แต่ไม่บันทึกแคชอัตโนมัติ.
     *
     * @var int
     */
    private $action = 0;
    /**
     * คลาส Cache.
     *
     * @var Cache
     */
    private $db_cache;
    /**
     * @var Singleton สำหรับเรียกใช้ class นี้เพียงครั้งเดียวเท่านั้น
     */
    private static $instance = null;

    /**
     * เปิดการใช้งานแคช
     * จะมีการตรวจสอบจากแคชก่อนการสอบถามข้อมูล.
     *
     * @param bool $auto_save (options) true (default) บันทึกผลลัพท์อัตโนมัติ, false ต้องบันทึกแคชเอง
     */
    public function cacheOn($auto_save = true)
    {
        $this->action = $auto_save ? 1 : 2;
    }

    /**
     * เคลียร์แคช
     * คืนค่า true ถ้าลบเรียบร้อย, หรือ array ของรายการที่ไม่สำเร็จ.
     *
     * @return bool
     */
    public function clear()
    {
        $this->db_cache->clear();
    }

    /**
     * Create Class (Singleton).
     *
     * @return \static
     */
    public static function create()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * อ่านข้อมูลจากแคช
     * คืนค่าข้อมูลหรือ false ถ้าไม่มีแคช.
     *
     * @param Item $item
     *
     * @return mixed
     */
    public function get(Item $item)
    {
        return $item->isHit() ? $item->get() : false;
    }

    /**
     * อ่านสถานะของแคช
     * 0 ไม่ใช้แคช
     * 1 โหลดและบันทึกแคชอัตโนมัติ
     * 2 โหลดข้อมูลจากแคชได้ แต่ไม่บันทึกแคชอัตโนมัติ.
     *
     * @return int
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * กำหนดคีย์ของแคชจาก query.
     *
     * @param string $sql
     * @param array  $values
     *
     * @return Item
     */
    public function init($sql, $values)
    {
        return $this->db_cache->getItem(Text::replace($sql, $values));
    }

    /**
     * บันทึก cache เมื่อบันทึกแล้วจะปิดการใช้งาน cache อัตโนมัติ
     * จะใช้คำสั่งนี้เมื่อมีการเรียกใช้แคชด้วยคำสั่ง cacheOn(false) เท่านั้น
     * query ครั้งต่อไปถ้าจะใช้ cache ต้อง เปิดการใช้งาน cache ก่อนทุกครั้ง
     * สำเร็จคืนค่า true ไม่สำเร็จคืนค่า false.
     *
     * @param Item  $item
     * @param array $datas ข้อมูลที่จะบันทึก
     *
     * @return bool
     */
    public function save(Item $item, $datas)
    {
        $this->action = 0;
        $item->set($datas);

        return $this->db_cache->save($item);
    }

    /**
     * กำหนดสถานะของแคช
     * 0 ไม่ใช้แคช
     * 1 โหลดและบันทึกแคชอัตโนมัติ
     * 2 โหลดข้อมูลจากแคชได้ แต่ไม่บันทึกแคชอัตโนมัติ.
     *
     * @param type $value
     *
     * @return \static
     */
    public function setAction($value)
    {
        $this->action = $value;

        return $this;
    }

    /**
     * ตรวจสอบว่าข้อมูลมาจาก cache หรือไม่.
     *
     * @param Item $item
     *
     * @return bool
     */
    public function usedCache(Item $item)
    {
        return $item->isHit();
    }

    /**
     * Class constructor.
     */
    private function __construct()
    {
        $this->db_cache = new Cache();
    }
}
