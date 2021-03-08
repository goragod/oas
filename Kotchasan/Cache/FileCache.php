<?php
/**
 * @filesource Kotchasan/Cache/FileCache.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan\Cache;

use Kotchasan\Cache\CacheItem as Item;
use Kotchasan\File;
use Psr\Cache\CacheItemInterface;

/**
 * Filesystem cache driver.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class FileCache extends Cache
{
    /**
     * ไดเร็คทอรี่แคช.
     *
     * @var string /root/to/dir/cache/
     */
    protected $cache_dir = null;
    /**
     * อายุของแคช (วินาที) 0 หมายถึงไม่มีการแคช.
     *
     * @var int
     */
    protected $cache_expire = 0;

    /**
     * class constructor.
     *
     * @throws Exception ถ้าไม่สามารถสร้างแคชได้
     */
    public function __construct()
    {
        $this->cache_expire = self::$cfg->get('cache_expire', 0);
        if (!empty($this->cache_expire)) {
            //  cache directory
            $this->cache_dir = ROOT_PATH.'datas/cache/';
            if (!File::makeDirectory($this->cache_dir)) {
                throw new \Exception('Folder '.str_replace(ROOT_PATH, '', $this->cache_dir).' cannot be created.');
            }
            // clear old cache every day
            $d = is_file($this->cache_dir.'index.php') ? (int) file_get_contents($this->cache_dir.'index.php') : 0;
            if ($d != (int) date('d')) {
                $this->clear();
                $f = @fopen($this->cache_dir.'index.php', 'wb');
                if ($f === false) {
                    throw new \Exception('File '.str_replace(ROOT_PATH, '', $this->cache_dir).'index.php cannot be written.');
                } else {
                    fwrite($f, date('d-m-Y H:i:s'));
                    fclose($f);
                }
            }
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
        $error = array();
        if ($this->cache_dir && !empty($this->cache_expire)) {
            $this->clearCache($this->cache_dir, $error);
        }

        return empty($error) ? true : false;
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
                @unlink($this->fetchStreamUri($key));
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
        foreach ($keys as $key) {
            $file = $this->fetchStreamUri($key);
            if ($this->isExpired($file)) {
                $item = new Item($key);
                $resuts[$key] = $item->set(unserialize(preg_replace('/^<\?php\sexit\?>/', '', file_get_contents($file), 1)));
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
        return $this->isExpired($this->fetchStreamUri($key));
    }

    /**
     * บันทึกแคช
     * สำเร็จคืนค่า true ไม่สำเร็จคืนค่า false.
     *
     * @param CacheItemInterface $item
     *
     * @throws Exception ถ้าไม่สามารถสร้างแคชได้
     *
     * @return bool
     */
    public function save(CacheItemInterface $item)
    {
        if ($this->cache_dir && !empty($this->cache_expire)) {
            $f = @fopen($this->fetchStreamUri($item->getKey()), 'wb');
            if (!$f) {
                throw new \Exception('resource cache file cannot be created.');
            } else {
                fwrite($f, '<?php exit?>'.serialize($item->get()));
                fclose($f);

                return true;
            }
        }

        return false;
    }

    /**
     * ลบไฟล์ทั้งหมดในไดเร็คทอรี่ (cache).
     *
     * @param string $dir
     * @param array  $error เก็บรายชื่อไฟล์ที่ไม่สามารถลบได้
     */
    private function clearCache($dir, &$error)
    {
        $f = @opendir($dir);
        if ($f) {
            while (false !== ($text = readdir($f))) {
                if ($text != '.' && $text != '..' && $text != 'index.php') {
                    if (is_dir($dir.$text)) {
                        $this->clearCache($dir.$text.'/', $error);
                    } elseif (is_file($dir.$text)) {
                        if (@unlink($dir.$text) === false) {
                            $error[] = $dir.$text;
                        }
                    }
                }
            }
            closedir($f);
        }
    }

    /**
     * อ่านค่า full path ของไฟล์แคช.
     *
     * @param string $key
     *
     * @return string
     */
    private function fetchStreamUri($key)
    {
        return $this->cache_dir.md5($key).'.php';
    }

    /**
     * ตรวจสอบวันหมดอายุของไฟล์แคช
     * คืนค่า true ถ้าแคชสามารถใช้งานได้.
     *
     * @param string $file
     *
     * @return bool
     */
    private function isExpired($file)
    {
        if ($this->cache_dir && !empty($this->cache_expire)) {
            return file_exists($file) && time() - filemtime($file) < $this->cache_expire;
        }

        return false;
    }
}
