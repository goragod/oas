<?php
/**
 * @filesource Kotchasan/Files.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

use Kotchasan\Http\UploadedFile;

/**
 * รายการ File รูปแบบ Array.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Files implements \Iterator
{
    /**
     * แอเรย์เก็บรายการ UploadedFile.
     *
     * @var array
     */
    private $datas = array();

    /**
     * เพื่ม File ลงในคอลเล็คชั่น.
     *
     * @param string $name         ชื่อของ Input
     * @param string $path         ไฟล์อัปโหลด รวมพาธ
     * @param string $originalName ชื่อไฟล์ที่อัปโหลด
     * @param string $mimeType     MIME Type
     * @param int    $size         ขนาดไฟล์อัปโหลด
     * @param int    $error        ข้อผิดพลาดการอัปโหลด UPLOAD_ERR_XXX
     */
    public function add($name, $path, $originalName, $mimeType = null, $size = null, $error = null)
    {
        $this->datas[$name] = new UploadedFile($path, $originalName, $mimeType, $size, $error);
    }

    /**
     * คืนค่า UploadedFile รายการปัจจุบัน.
     *
     * @return \Kotchasan\Http\UploadedFile
     */
    public function current()
    {
        $var = current($this->datas);

        return $var;
    }

    /**
     * อ่าน File ที่ต้องการ.
     *
     * @param string|int $key รายการที่ต้องการ
     *
     * @return \Kotchasan\Http\UploadedFile
     */
    public function get($key)
    {
        return $this->datas[$key];
    }

    /**
     * คืนค่าคีย์หรือลำดับของ UploadedFile ในลิสต์รายการ.
     *
     * @return string
     */
    public function key()
    {
        $var = key($this->datas);

        return $var;
    }

    /**
     * คืนค่า UploadedFile รายการถัดไป.
     *
     * @return \Kotchasan\Http\UploadedFile
     */
    public function next()
    {
        $var = next($this->datas);

        return $var;
    }

    /**
     * inherited from Iterator.
     */
    public function rewind()
    {
        reset($this->datas);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        $key = key($this->datas);

        return $key !== null && $key !== false;
    }
}
