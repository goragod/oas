<?php
/**
 * @filesource Kotchasan/Http/Request.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan\Http;

/**
 * คลาสสำหรับจัดการตัวแปรต่างๆจาก Server
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Request extends AbstractRequest implements \Psr\Http\Message\RequestInterface
{
    /**
     * @var array
     */
    private $attributes = array();
    /**
     * $_COOKIE
     *
     * @var array
     */
    private $cookieParams;
    /**
     * $_POST
     *
     * @var array
     */
    private $parsedBody;
    /**
     * $_GET
     *
     * @var array
     */
    private $queryParams;
    /**
     * $_SERVER
     *
     * @var array
     */
    private $serverParams;
    /**
     * @var Kotchasan\Files
     */
    private $uploadedFiles;

    /**
     * อ่านค่าจากตัวแปร COOKIE
     * คืนค่า InputItem หรือ Collection ของ InputItem.
     *
     * @param string $name    ชื่อตัวแปร
     * @param mixed  $default ค่าเริ่มต้นหากไม่พบตัวแปร
     *
     * @return \Kotchasan\InputItem|\Kotchasan\Inputs
     */
    public function cookie($name, $default = '')
    {
        return $this->createInputItem($this->getCookieParams(), $name, $default, 'COOKIE');
    }

    /**
     * ฟังก์ชั่นสร้าง token
     *
     * @return string
     */
    public function createToken()
    {
        $token = \Kotchasan\Password::uniqid(32);
        $_SESSION[$token] = array(
            'times' => 0,
            'expired' => time() + TOKEN_AGE,
        );
        return $token;
    }

    /**
     * อ่านค่าจากตัวแปร GET
     * คืนค่า InputItem หรือ Collection ของ InputItem
     *
     * @param string $name    ชื่อตัวแปร
     * @param mixed  $default ค่าเริ่มต้นหากไม่พบตัวแปร
     *
     * @return \Kotchasan\InputItem|\Kotchasan\Inputs
     */
    public function get($name, $default = null)
    {
        return $this->createInputItem($this->getQueryParams(), $name, $default, 'GET');
    }

    /**
     * คืนค่ารายการภาษาที่รองรับ จาก HTTP header
     *
     * @return array
     */
    public function getAcceptableLanguages()
    {
        $acceptLanguages = empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? array() : explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $matches = array();
        if (!empty($acceptLanguages)) {
            foreach ($acceptLanguages as $item) {
                $item = array_map('trim', explode(';', $item));
                if (isset($item[1])) {
                    $q = str_replace('q=', '', $item[1]);
                } else {
                    if ($item[0] == '*/*') {
                        $q = 0.01;
                    } elseif (substr($item[0], -1) == '*') {
                        $q = 0.02;
                    } else {
                        $q = 1000 - count($matches);
                    }
                }
                $matches[(string) $q] = $item[0];
            }
            krsort($matches, SORT_NUMERIC);
            $matches = array_values($matches);
        }
        return $matches;
    }

    /**
     * อ่านค่า attributes ที่ต้องการ
     *
     * @param string $name    ชื่อของ attributes
     * @param mixed  $default คืนค่า $default ถ้าไม่พบ
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    /**
     * คืนค่า attributes ทั้งหมด
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * ฟังก์ชั่น อ่าน ip ของ client
     * คืนค่า IP ที่อ่านได้
     *
     * @return string|null
     */
    public function getClientIp()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $IParray = array_filter(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
            return $IParray[0];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return null;
    }

    /**
     * คืนค่าจากตัวแปร $_COOKIE
     *
     * @return array
     */
    public function getCookieParams()
    {
        if ($this->cookieParams === null) {
            $this->cookieParams = self::filterRequestKey($_COOKIE);
        }
        return $this->cookieParams;
    }

    /**
     * คืนค่าจากตัวแปร $_POST
     *
     * @return null|array|object
     */
    public function getParsedBody()
    {
        if ($this->parsedBody === null) {
            $this->parsedBody = self::filterRequestKey($_POST);
        }
        return $this->parsedBody;
    }

    /**
     * คืนค่าจากตัวแปร $_GET
     *
     * @return null|array|object
     */
    public function getQueryParams()
    {
        if ($this->queryParams === null) {
            $this->queryParams = self::filterRequestKey($_GET);
        }
        return $this->queryParams;
    }

    /**
     * คืนค่าจากตัวแปร $_SERVER
     *
     * @return array
     */
    public function getServerParams()
    {
        if ($this->serverParams === null) {
            $this->serverParams = self::filterRequestKey($_SERVER);
        }
        return $this->serverParams;
    }

    /**
     * คืนค่าไฟล์อัปโหลด FILES
     *
     * @return \Kotchasan\Files
     */
    public function getUploadedFiles()
    {
        if ($this->uploadedFiles === null) {
            $this->uploadedFiles = new \Kotchasan\Files();
            if (isset($_FILES)) {
                foreach ($_FILES as $name => $file) {
                    if (is_array($file['name'])) {
                        foreach ($file['name'] as $key => $value) {
                            $this->uploadedFiles->add($name.'['.$key.']', $file['tmp_name'][$key], $value, $file['type'][$key], $file['size'][$key], $file['error'][$key]);
                        }
                    } else {
                        $this->uploadedFiles->add($name, $file['tmp_name'], $file['name'], $file['type'], $file['size'], $file['error']);
                    }
                }
            }
        }
        return $this->uploadedFiles;
    }

    /**
     * อ่านค่าจากตัวแปร GLOBALS เช่น $_POST $_GET $_SESSION $_COOKIE ตามที่ระบุใน $keys ตามลำดับ
     * เช่น array('POST', 'GET') หมายถึงอ่านจาก $_POST ก่อน ถ้าไม่พบจะอ่านจาก $_GET
     * และถ้าไม่พบอีกจะคืนค่า $default
     *
     * @param array  $keys    ชื่อตัวแปรที่ต้องการอ่าน ตัวพิมพ์ใหญ่ เช่น array('POST', 'GET')
     * @param string $name    ชื่อตัวแปร
     * @param mixed  $default ค่าเริ่มต้นหากไม่พบตัวแปร
     *
     * @return \Kotchasan\InputItem|\Kotchasan\Inputs
     */
    public function globals($keys, $name, $default = null)
    {
        foreach ($keys as $key) {
            if ($key == 'POST') {
                $datas = $this->getParsedBody();
            } elseif ($key == 'GET') {
                $datas = $this->getQueryParams();
            } elseif ($key == 'SESSION') {
                $datas = $_SESSION;
            } elseif ($key == 'COOKIE') {
                $datas = $this->getCookieParams();
            }
            if (isset($datas[$name])) {
                return is_array($datas[$name]) ? new \Kotchasan\Inputs($datas[$name], $key) : new \Kotchasan\InputItem($datas[$name], $key);
            }
        }
        return is_array($default) ? new \Kotchasan\Inputs($default) : new \Kotchasan\InputItem($default);
    }

    /**
     * ฟังก์ชั่นเริ่มต้นใช้งาน session
     *
     * @return bool
     */
    public function initSession()
    {
        $sessid = $this->get('sess')->toString();
        if (!empty($sessid) && preg_match('/[a-zA-Z0-9]{20,}/', $sessid)) {
            session_id($sessid);
        }
        if (defined('USE_SESSION_DATABASE') && USE_SESSION_DATABASE === true) {
            $sess = new \Kotchasan\Session();
            session_set_save_handler(
                array($sess, '_open'),
                array($sess, '_close'),
                array($sess, '_read'),
                array($sess, '_write'),
                array($sess, '_destroy'),
                array($sess, '_gc')
            );
            register_shutdown_function('session_write_close');
        }
        session_start();
        if (!ob_get_status()) {
            if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
                // เปิดใช้งานการบีบอัดหน้าเว็บไซต์
                ob_start('ob_gzhandler');
            } else {
                ob_start();
            }
        }
        return true;
    }

    /**
     * ตรวจสอบว่าเรียกมาโดย Ajax หรือไม่
     * คืนค่า true ถ้าเรียกมาจาก Ajax (XMLHttpRequest).
     *
     * @return bool
     */
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * ฟังก์ชั่น ตรวจสอบ referer
     * คืนค่า true ถ้า referer มาจากเว็บไซต์นี้
     *
     * @return bool
     */
    public function isReferer()
    {
        $host = empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if (preg_match("/$host/ui", $referer)) {
            return true;
        } elseif (preg_match('/^(http(s)?:\/\/)(.*)(\/.*){0,}$/U', WEB_URL, $match)) {
            return preg_match("/$match[3]/ui", $referer);
        }
        return false;
    }

    /**
     * ฟังก์ชั่น ตรวจสอบ token ที่มาจากฟอร์ม และ ตรวจสอบ Referer ด้วย
     * รับค่าที่มาจาก $_POST เท่านั้น
     * ฟังก์ชั่นนี้ต้องเรียกต่อจาก initSession() เสมอ
     * อายุของ token กำหนดที่ TOKEN_LIMIT
     * คืนค่า true ถ้า token ถูกต้องและไม่หมดอายุ
     *
     * @return bool
     */
    public function isSafe()
    {
        $token = $this->post('token')->toString();
        if ($token !== null) {
            if (isset($_SESSION[$token]) && $_SESSION[$token]['times'] < TOKEN_LIMIT && $_SESSION[$token]['expired'] > time() && $this->isReferer()) {
                ++$_SESSION[$token]['times'];
                return true;
            } else {
                unset($_SESSION[$token]);
            }
        }
        return false;
    }

    /**
     * อ่านค่าจากตัวแปร $_POST
     * ถ้าไม่พบเลยคืนค่า $default
     * คืนค่า InputItem หรือ แอเรย์ของ InputItem
     *
     * @param string $name    ชื่อตัวแปร
     * @param mixed  $default ค่าเริ่มต้นหากไม่พบตัวแปร
     *
     * @return \Kotchasan\InputItem|\Kotchasan\Inputs
     */
    public function post($name, $default = null)
    {
        return $this->createInputItem($this->getParsedBody(), $name, $default, 'POST');
    }

    /**
     * ลบ token
     */
    public function removeToken()
    {
        $token = $this->globals(array('POST', 'GET'), 'token', null)->toString();
        if ($token !== null) {
            unset($_SESSION[$token]);
        }
    }

    /**
     * อ่านค่าจากตัวแปร $_POST $_GET $_COOKIE(options) ตามลำดับ
     * คืนค่ารายการแรกที่พบ ถ้าไม่พบเลยคืนค่า $default
     * คืนค่า InputItem หรือ แอเรย์ของ InputItem
     *
     * @param string $name    ชื่อตัวแปร
     * @param mixed  $default ค่าเริ่มต้นหากไม่พบตัวแปร
     * @param mixed  $cookie  false (default) ไม่อ่านจาก cookie, true อ่านจาก cookie ด้วย
     *
     * @return \Kotchasan\InputItem|\Kotchasan\Inputs
     */
    public function request($name, $default = null, $cookie = false)
    {
        $from = array('POST', 'GET');
        if ($cookie) {
            $from[] = 'COOKIE';
        }
        return $this->globals($from, $name, $default);
    }

    /**
     * อ่านค่าจากตัวแปร $_SERVER
     * ถ้าไม่พบเลยคืนค่า $default
     *
     * @param string $name    ชื่อตัวแปร
     * @param mixed  $default ค่าเริ่มต้นหากไม่พบตัวแปร
     *
     * @return mixed
     */
    public function server($name, $default = null)
    {
        return isset($_SERVER[$name]) ? $_SERVER[$name] : $default;
    }

    /**
     * อ่านค่าจากตัวแปร $_SESSION
     * ถ้าไม่พบเลยคืนค่า $default
     * คืนค่า InputItem หรือ Collection ของ InputItem
     *
     * @param string $name    ชื่อตัวแปร
     * @param mixed  $default ค่าเริ่มต้นหากไม่พบตัวแปร
     *
     * @return \Kotchasan\InputItem|\Kotchasan\Inputs
     */
    public function session($name, $default = null)
    {
        return $this->createInputItem($_SESSION, $name, $default, 'SESSION');
    }

    /**
     * กำหนดค่าตัวแปร $_SESSION
     *
     * @param string $name  ชื่อตัวแปร
     * @param mixed  $value ค่าของตัวแปร
     *
     * @return \static
     */
    public function setSession($name, $value)
    {
        $_SESSION[$name] = $value;
        return $this;
    }

    /**
     * กำหนดค่า attributes
     *
     * @param string $name  ชื่อของ attributes
     * @param mixed  $value ค่าของ attribute
     *
     * @return \static
     */
    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    /**
     * กำหนดค่า cookieParams
     *
     * @param array $cookies
     *
     * @return \static
     */
    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;
        $clone->cookieParams = $cookies;
        return $clone;
    }

    /**
     * กำหนดค่า parsedBody
     *
     * @param mixed $data
     *
     * @return \static
     */
    public function withParsedBody($data)
    {
        $clone = clone $this;
        $clone->parsedBody = $data;
        return $clone;
    }

    /**
     * กำหนดค่า queryParams
     *
     * @param array $query
     *
     * @return \static
     */
    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->queryParams = $query;
        return $clone;
    }

    /**
     * กำหนดค่า uploadedFiles
     *
     * @param array $uploadedFiles
     *
     * @return \static
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;
        return $clone;
    }

    /**
     * ลบ attributes
     *
     * @param string|array $names ชื่อของ attributes ที่ต้องการลบ
     *
     * @return \static
     */
    public function withoutAttribute($names)
    {
        $clone = clone $this;
        if (is_array($names)) {
            foreach ($names as $name) {
                unset($clone->attributes[$name]);
            }
        } else {
            unset($clone->attributes[$names]);
        }
        return $clone;
    }

    /**
     * อ่านค่าจาก $source
     * คืนค่า InputItem หรือ Collection ของ InputItem
     *
     * @param array       $source  ตัวแปร GET POST
     * @param string      $name    ชื่อตัวแปร
     * @param mixed       $default ค่าเริ่มต้นหากไม่พบตัวแปร
     * @param string|null $type    ประเภท Input เช่น GET POST SESSION COOKIE หรือ null ถ้าไม่ได้มาจากรายการข้างต้น
     *
     * @return \Kotchasan\InputItem|\Kotchasan\Inputs
     */
    private function createInputItem($source, $name, $default, $type)
    {
        if (isset($source[$name])) {
            return is_array($source[$name]) ? new \Kotchasan\Inputs($source[$name], $type) : new \Kotchasan\InputItem($source[$name], $type);
        } elseif (preg_match('/(.*)\[(.*)\]/', $name, $match) && isset($source[$match[1]][$match[2]])) {
            return new \Kotchasan\InputItem($source[$match[1]][$match[2]], $type);
        }
        return is_array($default) ? new \Kotchasan\Inputs($default) : new \Kotchasan\InputItem($default);
    }

    /**
     * key ของ Input ต่างๆเป็น ตัวเลข ภาษาอังกฤษ และ [ ] _ - เท่านั้น
     *
     * @param array $source
     *
     * @return array
     */
    public static function filterRequestKey($source)
    {
        $result = array();
        foreach ($source as $key => $values) {
            if (preg_match('/^[a-zA-Z0-9\[\]_\-]+/', $key)) {
                if (is_array($values)) {
                    $result[$key] = self::filterRequestKey($values);
                } elseif ($values !== null) {
                    $result[$key] = $values;
                }
            }
        }
        return $result;
    }
}
