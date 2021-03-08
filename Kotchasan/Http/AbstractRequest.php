<?php
/**
 * @filesource Kotchasan/Http/AbstractRequest.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan\Http;

/**
 * Class สำหรับจัดการ URL.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class AbstractRequest extends AbstractMessage implements \Psr\Http\Message\RequestInterface
{
    /**
     * @var string
     */
    protected $method = null;
    /**
     * @var string
     */
    protected $requestTarget;
    /**
     * @var \Kotchasan\Http\Uri
     */
    protected $uri;

    /**
     * สร้างคลาสจากลิงค์ และ รวมค่าที่มาจาก $_GET ด้วย
     *
     * @param string $uri     ค่าเริ่มต้นคือ index.php
     * @param array  $exclude รายการแอเรย์ของ $_GET ที่ไม่ต้องการให้รวมอยู่ใน URL
     *
     * @return Uri
     */
    public static function createUriWithGet($uri = 'index.php', $exclude = array())
    {
        $query = array();
        self::map($query, $_GET, $exclude);
        if (!empty($query)) {
            $uri .= (strpos($uri, '?') === false ? '?' : '&').http_build_query($query);
        }
        return Uri::createFromUri($uri);
    }

    /**
     * สร้างคลาสจากลิงค์ และ รวมค่าที่มาจาก $_GET และ $_POST ด้วย
     *
     * @param string $uri     ค่าเริ่มต้นคือ index.php
     * @param array  $exclude รายการแอเรย์ของ $_GET และ $_POST ที่ไม่ต้องการให้รวมอยู่ใน URL
     *
     * @return Uri
     */
    public function createUriWithGlobals($uri = 'index.php', $exclude = array())
    {
        $query_str = array();
        self::map($query_str, $_GET, $exclude);
        self::map($query_str, $_POST, $exclude);
        if (!empty($query_str)) {
            $uri .= (strpos($uri, '?') === false ? '?' : '&').http_build_query($query_str);
        }
        return Uri::createFromUri($uri);
    }

    /**
     * สร้างคลาสจากลิงค์ และ รวมค่าที่มาจาก $_POST ด้วย
     *
     * @param string $uri     ค่าเริ่มต้นคือ index.php
     * @param array  $exclude รายการแอเรย์ของ $_POST ที่ไม่ต้องการให้รวมอยู่ใน URL
     *
     * @return Uri
     */
    public static function createUriWithPost($uri = 'index.php', $exclude = array())
    {
        $query = array();
        self::map($query, $_POST, $exclude);
        if (!empty($query)) {
            $uri .= (strpos($uri, '?') === false ? '?' : '&').http_build_query($query);
        }
        return Uri::createFromUri($uri);
    }

    /**
     * อ่านค่า HTTP method
     * returns the request method.
     *
     * @return string
     */
    public function getMethod()
    {
        if ($this->method === null) {
            $this->method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
            if ($this->method === 'POST' && isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                $this->method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
            }
        }
        return $this->method;
    }

    /**
     * อ่านค่า request target
     *
     * @return string
     */
    public function getRequestTarget()
    {
        if ($this->requestTarget === null) {
            $this->requestTarget = $this->uri;
        }
        return $this->requestTarget;
    }

    /**
     * อ่าน Uri
     *
     * @return \Kotchasan\Http\Uri
     */
    public function getUri()
    {
        if ($this->uri === null) {
            $this->uri = Uri::createFromGlobals();
        }
        return $this->uri;
    }

    /**
     * รวมแอเรย์ $_GET $_POST เป็นข้อมูลเดียวกัน
     *
     * @param array $result  ตัวแปรเก็บผลลัพท์ สำหรับนำไปใช้งานต่อ
     * @param array $array   ตัวแปรที่ต้องการรวม เช่น $_GET $_POST
     * @param array $exclude รายการคีย์ของแอเรย์ ที่ไม่ต้องการให้รวมอยู่ในผลลัพท์
     */
    public static function map(&$result, $array, $exclude = array())
    {
        foreach ($array as $key => $value) {
            if (!in_array($key, $exclude)) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $result[$key.'['.$k.']'] = $v;
                    }
                } else {
                    $result[$key] = $value;
                }
            }
        }
    }

    /**
     * กำหนดค่า HTTP method
     *
     * @param string $method
     *
     * @return \static
     */
    public function withMethod($method)
    {
        $clone = clone $this;
        $clone->method = $method;
        return $clone;
    }

    /**
     * กำหนดค่า request target
     *
     * @param mixed $requestTarget
     *
     * @return \static
     */
    public function withRequestTarget($requestTarget)
    {
        $clone = clone $this;
        $clone->requestTarget = $requestTarget;
        return $clone;
    }

    /**
     * กำหนดค่า Uri
     *
     * @param \Kotchasan\Http\UriInterface $uri
     * @param bool                         $preserveHost
     *
     * @return \static
     */
    public function withUri(\Psr\Http\Message\UriInterface $uri, $preserveHost = false)
    {
        $clone = clone $this;
        $clone->uri = $uri;
        if (!$preserveHost) {
            if ($uri->getHost() !== '') {
                $clone->headers['Host'] = $uri->getHost();
            }
        } else {
            if ($this->uri->getHost() !== '' && (!$this->hasHeader('Host') || $this->getHeader('Host') === null)) {
                $clone->headers['Host'] = $uri->getHost();
            }
        }
        return $clone;
    }
}
