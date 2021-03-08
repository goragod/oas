<?php
/**
 * @filesource  Kotchasan/Cache/ApcCache.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan\Cache;

use Kotchasan\Cache\CacheItem as Item;
use Psr\Cache\CacheItemInterface;

/**
 * APC cache driver.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class ApcCache extends Cache
{
    /**
     * Class constructor
     *
     * @throws Exception ถ้า Server ไม่รองรับ APC
     */
    public function __construct()
    {
        if (!extension_loaded('apc') || !is_callable('apc_fetch')) {
            throw new \Exception('APC not supported.');
        }
    }

    /**
     * เคลียร์แคช
     * คืนค่า true ถ้าลบเรียบร้อย, หรือ false ถ้าไม่สำเร็จ.
     *
     * @return bool
     */
    public function clear()
    {
        return \apc_clear_cache('user');
    }

    /**
     * ลบแคชหลายๆรายการ
     * คืนค่า true ถ้าสำเร็จ, false ถ้าไม่สำเร็จ.
     *
     * @param array $keys
     *
     * @return bool
     */
    public function deleteItems(array $keys)
    {
        if ($this->cache_dir) {
            foreach ($keys as $key) {
                \apc_delete($key);
            }
        }

        return true;
    }

    /**
     * อ่านแคชหลายรายการ.
     *
     * @param array $keys
     *
     * @return array
     */
    public function getItems(array $keys = array())
    {
        $resuts = array();
        $success = false;
        $values = \apc_fetch($keys, $success);
        if ($success && is_array($values)) {
            foreach ($values as $key => $value) {
                $item = new Item($key);
                $resuts[$key] = $item->set($value);
            }
        }

        return $resuts;
    }

    /**
     * ตรวจสอบแคช
     * คืนค่า true ถ้ามี.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasItem($key)
    {
        return \apc_exists($key);
    }

    /**
     * บันทึกแคช
     * สำเร็จคืนค่า true ไม่สำเร็จคืนค่า false.
     *
     * @param CacheItemInterface $item
     *
     * @throws CacheException
     *
     * @return bool
     */
    public function save(CacheItemInterface $item)
    {
        return \apc_store($item->getKey(), $item->get(), self::$cfg->get('cache_expire', 5));
    }
}
