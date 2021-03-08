<?php
/**
 * @filesource Kotchasan/Http/AbstractMessage.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * HTTP messages base class (PSR-7)
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
abstract class AbstractMessage implements MessageInterface
{
    /**
     * @var array
     */
    protected $headers = array();
    /**
     * @var string
     */
    protected $protocol = '1.1';
    /**
     * @var StreamInterface
     */
    protected $stream;

    /**
     * init Class
     *
     * @param bool $with_header true คืนค่า HTTP Header ด้วย, false (default) ไม่รวม HTTP Header
     */
    public function __construct($with_header = false)
    {
        if ($with_header) {
            $this->headers = $this->getRequestHeaders();
        }
    }

    /**
     * อ่าน stream.
     *
     * @return StreamInterface
     */
    public function getBody()
    {
        return $this->stream;
    }

    /**
     * อ่าน header ที่ต้องการ ผลลัพท์เป็น array
     * คืนค่าแอเรย์ของ header ถ้าไม่พบคืนค่าแอเรย์ว่าง
     *
     * @param string $name
     *
     * @return string[]
     */
    public function getHeader($name)
    {
        return isset($this->headers[$name]) ? $this->headers[$name] : array();
    }

    /**
     * อ่าน header ที่ต้องการ ผลลัพท์เป็น string
     * คืนค่ารายการ header ทั้งหมดที่พบเชื่อมต่อด้วย ลูกน้ำ (,) หรือคืนค่าข้อความว่าง หากไม่พบ
     *
     * @param string $name
     *
     * @return string
     */
    public function getHeaderLine($name)
    {
        $values = $this->getHeader($name);
        return empty($values) ? '' : implode(',', $values);
    }

    /**
     * คืนค่า header ทั้งหมด ผลลัพท์เป็น array.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * คืนค่าเวอร์ชั่นของโปรโตคอล
     * เช่น 1.1, 1.0
     *
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    /**
     * ตรวจสอบว่ามี header หรือไม่
     * คืนค่า true ถ้ามี.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasHeader($name)
    {
        return isset($this->headers[$name]);
    }

    /**
     * เพิ่ม header ใหม่
     *
     * @param string          $name  ชื่อของ Header
     * @param string|string[] $value ค่าของ Header เป็น string หรือ แอเรย์ของ string
     *
     * @throws \InvalidArgumentException ถ้าชื่อ header ไม่ถูกต้อง
     *
     * @return \static
     */
    public function withAddedHeader($name, $value)
    {
        $this->filterHeader($name);
        $clone = clone $this;
        if (is_array($value)) {
            foreach ($value as $item) {
                $clone->headers[$name][] = $item;
            }
        } else {
            $clone->headers[$name][] = $value;
        }
        return $clone;
    }

    /**
     * กำหนด stream
     *
     * @param streamInterface $body
     *
     * @return \static
     */
    public function withBody(StreamInterface $body)
    {
        $clone = clone $this;
        $clone->stream = $body;
        return $clone;
    }

    /**
     * กำหนด header แทนที่รายการเดิม
     *
     * @param string          $name  ชื่อของ Header
     * @param string|string[] $value ค่าของ Header เป็น string หรือ แอเรย์ของ string
     *
     * @throws \InvalidArgumentException for invalid header names or values
     *
     * @return \static
     */
    public function withHeader($name, $value)
    {
        $this->filterHeader($name);
        $clone = clone $this;
        $clone->headers[$name] = is_array($value) ? $value : (array) $value;
        return $clone;
    }

    /**
     * กำหนด header พร้อมกันหลายรายการ แทนที่รายการเดิม
     *
     * @param array $headers array($key => $value, $key => $value...)
     *
     * @throws \InvalidArgumentException for invalid header names or values
     *
     * @return \static
     */
    public function withHeaders($headers)
    {
        $clone = clone $this;
        foreach ($headers as $name => $value) {
            $this->filterHeader($name);
            $clone->headers[$name] = is_array($value) ? $value : (array) $value;
        }
        return $clone;
    }

    /**
     * กำหนดเวอร์ชั่นของโปรโตคอล.
     *
     * @param string $version เช่น 1.1, 1.0
     *
     * @return \static
     */
    public function withProtocolVersion($version)
    {
        $clone = clone $this;
        $clone->protocol = $version;
        return $clone;
    }

    /**
     * ลบ header.
     *
     * @param string $name ชื่อ header ที่ต้องการลบ
     *
     * @return \static
     */
    public function withoutHeader($name)
    {
        $clone = clone $this;
        unset($clone->headers[$name]);
        return $clone;
    }

    /**
     * ตรวจสอบความถูกต้องของ header
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException ถ้า header ไม่ถูกต้อง
     */
    protected function filterHeader($name)
    {
        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $name)) {
            throw new \InvalidArgumentException('Invalid header name');
        }
    }

    /**
     * ฟังก์ชั่นคืนค่า HTTP Header
     *
     * @return array
     */
    protected function getRequestHeaders()
    {
        $headers = array();
        if (function_exists("apache_request_headers")) {
            foreach (apache_request_headers() as $key => $value) {
                if (preg_match('/^[a-zA-Z0-9\-]+$/', $key)) {
                    $headers[$key] = array($value);
                }
            }
        } else {
            foreach ($_SERVER as $key => $value) {
                if (preg_match('/^HTTP_([a-zA-Z0-9_]+)$/', $key, $match)) {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace(array('_', '-'), ' ', $match[1]))))] = array($value);
                }
            }
        }
        return $headers;
    }
}
