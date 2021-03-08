<?php
/**
 * @filesource Kotchasan/Http/UploadedFile.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan\Http;

use Kotchasan\Image;
use Kotchasan\Language;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class สำหรับจัดการไฟล์อัปโหลด.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class UploadedFile implements UploadedFileInterface
{
    /**
     * ข้อผิดพลาดการอัปโหลด UPLOAD_ERR_XXX.
     *
     * @var int
     */
    private $error;
    /**
     * นามสกุลของไฟล์อัปโหลด.
     *
     * @var string
     */
    private $ext;
    /**
     * ใช้สำหรับบอกว่ามีการย้ายไฟล์ไปแล้ว.
     *
     * @var bool
     */
    private $isMoved = false;
    /**
     * MIME Type.
     *
     * @var string
     */
    private $mime;
    /**
     * ชื่อไฟล์ที่อัปโหลด.
     *
     * @var string
     */
    private $name;
    /**
     *  Indicates if the upload is from a SAPI environment.
     *
     * @var bool
     */
    private $sapi = false;
    /**
     * ขนาดไฟล์อัปโหลด.
     *
     * @var int
     */
    private $size;
    /**
     * file stream.
     *
     * @var Stream
     */
    private $stream;
    /**
     * ไฟล์อัปโหลด รวมพาธ.
     *
     * @var string
     */
    private $tmp_name;

    /**
     * ไฟล์อัปโหลด.
     *
     * @param string $path         ไฟล์อัปโหลด รวมพาธ
     * @param string $originalName ชื่อไฟล์ที่อัปโหลด
     * @param string $mimeType     MIME Type
     * @param int    $size         ขนาดไฟล์อัปโหลด
     * @param int    $error        ข้อผิดพลาดการอัปโหลด UPLOAD_ERR_XXX
     * @param bool   $sapi         indicates if the upload is in a SAPI environment
     */
    public function __construct($path, $originalName, $mimeType = null, $size = null, $error = null, $sapi = true)
    {
        $this->tmp_name = $path;
        $this->name = $originalName;
        $this->mime = $mimeType;
        $this->size = $size;
        $this->error = $error;
        $this->sapi = $sapi;
    }

    /**
     * สำเนาไฟล์อัปโหลดไปยังที่อยู่ใหม่
     * คืนค่า true ถ้าอัปโหลดเรียบร้อย.
     *
     * @param string $targetPath ที่อยู่ปลายทางที่ต้องการย้าย
     *
     * @throws \RuntimeException         ข้อผิดพลาดการอัปโหลด
     * @throws \InvalidArgumentException ไดเรคทอรี่ไม่สามารถเขียนได้
     *
     * @return bool
     */
    public function copyTo($targetPath)
    {
        if ($this->isMoved) {
            throw new \RuntimeException(sprintf(Language::get('Uploaded file %1s has already been moved'), $this->name));
        }
        if (!is_writable(dirname($targetPath))) {
            throw new \InvalidArgumentException(Language::get('Target directory is not writable'));
        }
        if (!copy($this->tmp_name, $targetPath)) {
            throw new \RuntimeException(sprintf(Language::get('Error copying file %1s to %2s'), $this->name, $targetPath));
        }

        return true;
    }

    /**
     * ฟังก์ชั่น ตัดรูปภาพ ตามขนาดที่กำหนด และย้ายไปยังปลายทาง
     * รูปภาพปลายทางจะมีขนาดเท่าที่กำหนด หากรูปภาพต้นฉบับมีขนาดหรืออัตราส่วนไม่พอดีกับขนาดของภาพปลายทาง
     * รูปภาพจะถูกตัดขอบออกหรือจะถูกขยาย เพื่อให้พอดีกับรูปภาพปลายทางที่ต้องการ
     * ผลลัพท์จะได้ไฟล์รูปภาพ jpg เท่านั้น
     * สำเร็จคืนค่า true ไม่สำเร็จคืนค่าข้อความผิดพลาด.
     *
     * @param array  $exts       นามสกุลของไฟล์รูปภาพที่ยอมรับ เช่น [jpg, gif, png]
     * @param string $targetPath path และชื่อไฟล์ของไฟล์รูปภาพปลายทาง
     * @param int    $width      ความกว้างของรูปภาพที่ต้องการ
     * @param int    $height     ความสูงของรูปภาพที่ต้องการ
     * @param string $watermark  (optional) ข้อความลายน้ำ
     *
     * @throws \InvalidArgumentException ข้อผิดพลาดหากที่อยู่ปลายทางไม่สามารถเขียนได้
     * @throws \RuntimeException         ข้อผิดพลาดไม่สามารถสร้างรูปภาพได้
     *
     * @return bool|string
     */
    public function cropImage($exts, $targetPath, $width, $height, $watermark = '')
    {
        $this->check($exts, dirname($targetPath));
        $ret = Image::crop($this->tmp_name, $targetPath, $width, $height, $watermark);
        if ($ret === false) {
            throw new \RuntimeException(Language::get('Unable to create image'));
        }

        return true;
    }

    /**
     * อ่านชื่อไฟล์จากไฟล์ที่อัปโหลดและตัดตัวอักษที่ไม่สามารถใช้เป็นชื่อไฟล์ได้ออก
     * ยอมรับ ภาษาอังกฤษ ตัวเลข ( ) _ - และ .(จุด) เท่านั้น
     * นอกเหนือจากนั้นจะถูกแทนที่ด้วย $replace ติดกันไม่เกิน 1 ตัวอักษร.
     *
     * @param string $replace ตัวอักษรที่จะแทนที่อักขระไที่ไม่ต้องการ ถ้าไม่ระบุจะใช้ _ (ขีดล่าง)
     *
     * @return string
     */
    public function getCleanFilename($replace = '_')
    {
        return preg_replace('/[^a-zA-Z0-9_\-\.\(\)]{1,}/', $replace, $this->name);
    }

    /**
     * คืนค่านามสกุลของไฟล์อัปโหลด ตัวพิมพ์เล็ก เช่น jpg.
     *
     * @return string
     */
    public function getClientFileExt()
    {
        if ($this->ext == null) {
            $exts = explode('.', $this->name);
            $this->ext = strtolower(end($exts));
        }

        return $this->ext;
    }

    /**
     * อ่านชื่อไฟล์ (ต้นฉบับ) ของไฟล์ที่อัปโหลด.
     *
     * @return string|null
     */
    public function getClientFilename()
    {
        return $this->name;
    }

    /**
     * อ่าน MIME Type ของไฟล์.
     *
     * @return string|null
     */
    public function getClientMediaType()
    {
        return $this->mime;
    }

    /**
     * อ่านข้อผิดพลาดของไฟล์อัปโหลด
     * คืนค่า UPLOAD_ERR_XXX.
     *
     * @return int
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * อ่านข้อผิดพลาดของไฟล์อัปโหลด เป็นข้อความ
     *
     * @staticvar array $errors
     *
     * @return string
     */
    public function getErrorMessage()
    {
        switch ($this->error) {
            case UPLOAD_ERR_INI_SIZE:
                return sprintf('The file "%s" exceeds your upload_max_filesize ini directive (limit is %s).', $this->getClientFilename(), self::getUploadSize());
                break;
            case UPLOAD_ERR_FORM_SIZE:
                return sprintf('The file "%s" exceeds the upload limit defined in your form.', $this->getClientFilename());
                break;
            case UPLOAD_ERR_PARTIAL:
                return sprintf('The file "%s" was only partially uploaded.', $this->getClientFilename());
                break;
            case UPLOAD_ERR_CANT_WRITE:
                return sprintf('The file "%s" could not be written on disk.', $this->getClientFilename());
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'File could not be uploaded: missing temporary directory.';
                break;
            case UPLOAD_ERR_EXTENSION:
                return 'File upload was stopped by a PHP extension.';
                break;
            case UPLOAD_ERR_OK:
            case UPLOAD_ERR_NO_FILE:
                return null;
                break;
            default:
                return sprintf('The file "%s" was not uploaded due to an unknown error.', $this->getClientFilename());
                break;
        }
    }

    /**
     * อ่านขนาดของไฟล์อัปโหลด.
     *
     * @return int|null
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * ส่งออกไฟล์อัปโหลดเป็น Stream.
     *
     * @throws \RuntimeException ถ้าไม่พบไฟล์
     *
     * @return StreamInterface
     */
    public function getStream()
    {
        if (!is_file($this->tmp_name)) {
            throw new \RuntimeException(sprintf(Language::get('Uploaded file %1s has already been moved'), $this->name));
        }
        if ($this->stream === null) {
            $this->stream = new Stream($this->tmp_name);
        }

        return $this->stream;
    }

    /**
     * อ่านไฟล์รวม path จากตัวแปร tmp_name.
     *
     * @return string|null
     */
    public function getTempFileName()
    {
        return $this->tmp_name;
    }

    /**
     * อ่านการตั้งค่าขนาดของไฟลอัปโหลด.
     *
     * @param bool $return_byte false (default) คืนค่าเป็นข้อความเช่น 2M, true คืนค่าเป็นตัวเลข (byte)
     *
     * @return string|int
     */
    public static function getUploadSize($return_byte = false)
    {
        $val = trim(ini_get('upload_max_filesize'));
        if (is_numeric($val)) {
            return $val;
        } elseif ($return_byte && preg_match('/^([0-9]+)([gmk])$/', strtolower($val), $match)) {
            $units = array('k' => 1024, 'm' => 1048576, 'g' => 1073741824);
            $val = (int) $match[1] * $units[$match[2]];
        }

        return $val;
    }

    /**
     * ตรวจสอบว่ามีข้อผิดพลาดการอัปโหลดหรือไม่
     * คืนค่า false ถ้าไม่มีไฟล์อัปโหลดหรืออัปโหลดสำเร็จ, คืนค่า true ถ้ามีข้อผิดพลาด.
     *
     * @return bool
     */
    public function hasError()
    {
        if ($this->error === null || $this->error === UPLOAD_ERR_OK || $this->error === UPLOAD_ERR_NO_FILE) {
            return false;
        }

        return true;
    }

    /**
     * ตรวจสอบไฟล์อัปโหลด
     * คืนค่า true ถ้ามีไฟล์อัปโหลด.
     *
     * @return bool
     */
    public function hasUploadFile()
    {
        return $this->error == UPLOAD_ERR_OK;
    }

    /**
     * ย้ายไฟล์อัปโหลดไปยังที่อยู่ใหม่
     * คืนค่า true ถ้าอัปโหลดเรียบร้อย.
     *
     * @param string $targetPath ที่อยู่ปลายทางที่ต้องการย้าย
     *
     * @throws \InvalidArgumentException ข้อผิดพลาดหากที่อยู่ปลายทางไม่สามารถเขียนได้
     * @throws \RuntimeException         ข้อผิดพลาดการอัปโหลด
     *
     * @return bool
     */
    public function moveTo($targetPath)
    {
        if ($this->isMoved) {
            throw new \RuntimeException(sprintf(Language::get('Uploaded file %1s has already been moved'), $this->name));
        }
        if (!is_writable(dirname($targetPath))) {
            throw new \InvalidArgumentException(Language::get('Target directory is not writable'));
        }
        if (strpos($targetPath, '://') > 0) {
            if (!copy($this->tmp_name, $targetPath)) {
                throw new \RuntimeException(sprintf(Language::get('Error moving uploaded file %1s to %2s'), $this->name, $targetPath));
            }
            if (!unlink($this->tmp_name)) {
                throw new \RuntimeException(sprintf(Language::get('Error removing uploaded file %1s'), $this->name));
            }
        } elseif ($this->sapi) {
            if (!move_uploaded_file($this->tmp_name, $targetPath)) {
                throw new \RuntimeException(sprintf(Language::get('Error moving uploaded file %1s to %2s'), $this->name, $targetPath));
            }
        } elseif (copy($this->tmp_name, $targetPath)) {
            unlink($this->tmp_name);
        } else {
            throw new \RuntimeException(sprintf(Language::get('Error moving uploaded file %1s to %2s'), $this->name, $targetPath));
        }
        $this->isMoved = true;

        return true;
    }

    /**
     * ปรับขนาดของรูปภาพอัปโหลด โดยรักษาอัตราส่วนของภาพตามความกว้างที่ต้องการ
     * หากรูปภาพมีขนาดเล็กกว่าที่กำหนด จะเป็นการ copy file
     * หากรูปภาพมีความสูง หรือความกว้างมากกว่า $width
     * จะถูกปรับขนาดให้มีขนาดไม่เกิน $width (ทั้งความสูงและความกว้าง)
     * และเปลี่ยนชนิดของภาพเป็น jpg
     * คืนค่าแอเรย์ [name, width, height, mime] ของรูปภาพปลายทาง หรือ false ถ้าไม่สามารถดำเนินการได้.
     *
     * @param array  $exts      นามสกุลของไฟล์รูปภาพที่ยอมรับ เช่น [jpg, gif, png]
     * @param string $target    path ของไฟล์รูปภาพปลายทาง
     * @param string $name      ชื่อไฟล์ของรูปภาพปลายทาง
     * @param int    $width     ขนาดสูงสุดของรูปภาพที่ต้องการ
     * @param string $watermark (optional) ข้อความลายน้ำ
     *
     * @return array|bool
     */
    public function resizeImage($exts, $target, $name, $width, $watermark = '')
    {
        $this->check($exts, $target);
        $ret = Image::resize($this->tmp_name, $target, $name, $width, $watermark);
        if ($ret === false) {
            throw new \RuntimeException(Language::get('Unable to create image'));
        } else {
            return $ret;
        }
    }

    /**
     * ตรวจสอบนามสกุลของไฟล์อัปโหลด
     * คืนค่า true ถ้านามสกุลของไฟล์อัปโหลดอยู่ใน $exts.
     *
     * @param array $exts รายการนามสกุลของไฟล์อัปโหลดที่ยอมรับ เช่น [jpg, gif, png]
     *
     * @return bool
     */
    public function validFileExt($exts)
    {
        return in_array($this->getClientFileExt(), $exts);
    }

    /**
     * ฟังชั่นตรวจสอบไฟล์อัปโหลด
     * คืนค่า true ถ้าสามารถอัปโหลดได้.
     *
     * @param array  $exts      นามสกุลของไฟล์รูปภาพที่ยอมรับ เช่น [jpg, gif, png]
     * @param string $targetDir ไดเรคทอรี่ปลายทาง
     *
     * @throws \RuntimeException         ถ้าชนิดของไฟล์อัปโหลดไม่ถูกต้อง
     * @throws \InvalidArgumentException ถ้าไดเร็คทอรี่ไม่สามารถเขียนได้
     *
     * @return bool
     */
    private function check($exts, $targetDir)
    {
        if (!$this->validFileExt($exts)) {
            throw new \RuntimeException(Language::get('The type of file is invalid'));
        }
        if (!is_writable($targetDir)) {
            throw new \InvalidArgumentException(Language::get('Target directory is not writable'));
        }

        return true;
    }
}
