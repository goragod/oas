<?php
/**
 * @filesource  Kotchasan/Cache/Cache.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan\Cache;

use Kotchasan\Cache\CacheItem as Item;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Kotchasan Caching Class (base class).
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
abstract class Cache extends \Kotchasan\KBase implements CacheItemPoolInterface
{
    /**
     * รายการแคชรอบันทึก
     *
     * @var array
     */
    protected $deferred = array();

    /**
     * บันทึกรายการแคชในคิว
     * คืนค่า true ถ้าสำเร็จ, ถ้ามีบางรายการไม่สำเร็จคืนค่า false.
     *
     * @return bool
     */
    public function commit()
    {
        $cuccess = true;
        foreach ($this->deferred as $item) {
            if (!$this->save($item)) {
                $cuccess = false;
            }
        }

        return $cuccess;
    }

    /**
     * ลบแคช
     * คืนค่า true ถ้าสำเร็จ, false ถ้าไม่สำเร็จ.
     *
     * @param string $key
     *
     * @return bool
     */
    public function deleteItem($key)
    {
        return $this->deleteItems(array($key));
    }

    /**
     * อ่านแคช.
     *
     * @param string $key
     *
     * @return CacheItemInterface
     */
    public function getItem($key)
    {
        $items = $this->getItems(array($key));

        return isset($items[$key]) ? $items[$key] : new Item($key);
    }

    /**
     * กำหนดรายการแคชสำหรับบันทึกในภายหลัง
     * คืนค่า false ถ้าไม่มีรายการในคิว.
     *
     * @param CacheItemInterface $item
     *
     * @return bool
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        $this->deferred[$item->getKey()] = $item;

        return true;
    }
}
