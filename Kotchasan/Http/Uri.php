<?php
/**
 * @filesource Kotchasan/Http/Uri.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan\Http;

/**
 * Class สำหรับจัดการ Uri (PSR-7)
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Uri extends \Kotchasan\KBase implements \Psr\Http\Message\UriInterface
{
    /**
     * Uri fragment หลัง  #
     *
     * @var string
     */
    protected $fragment = '';
    /**
     * Uri host
     *
     * @var string
     */
    protected $host = '';
    /**
     * Uri path
     *
     * @var string
     */
    protected $path = '';
    /**
     * Uri port
     *
     * @var int
     */
    protected $port;
    /**
     * Uri query string หลัง ?
     *
     * @var string
     */
    protected $query = '';
    /**
     * Uri scheme
     *
     * @var string
     */
    protected $scheme = '';
    /**
     * Uri user info
     *
     * @var string
     */
    protected $userInfo = '';

    /**
     * Create a new Uri
     *
     * @param string $uri
     *
     * @throws \InvalidArgumentException ถ้า Uri ไม่ถูกต้อง
     */
    public function __construct($scheme, $host, $path = '/', $query = '', $port = null, $user = '', $pass = '', $fragment = '')
    {
        $this->scheme = $this->filterScheme($scheme);
        $this->host = $host;
        $this->path = $path;
        $this->query = $this->filterQueryFragment($query);
        $this->port = $this->filterPort($this->scheme, $this->host, $port) ? $port : null;
        $this->userInfo = $user.($pass === '' ? '' : ':'.$pass);
        $this->fragment = $this->filterQueryFragment($fragment);
    }

    /**
     * magic function ส่งออกคลาสเป็น String
     *
     * @return string
     */
    public function __toString()
    {
        return self::createUriString(
            $this->scheme,
            $this->getAuthority(),
            $this->path,
            $this->query,
            $this->fragment
        );
    }

    /**
     * ฟังก์ชั่นสร้าง URL สำหรับส่งต่อ Query string จากหน้าหนึ่งไปยังอีกหน้าหนึ่ง
     * เพื่อให้สามารถสร้าง URL ที่สามารถส่งกลับไปยังหน้าเดิมได้โดย ฟังก์ชั่น back()
     * ลบรายการที่ เป็น null ออก
     *
     * @param array $query_string
     *
     * @return string
     */
    public function createBackUri($query_string)
    {
        $query_str = array();
        foreach ($this->parseQueryParams($this->query) as $key => $value) {
            $key = ltrim($key, '_');
            if (key_exists($key, $query_string) && $query_string[$key] === null) {
                continue;
            } elseif (preg_match('/((^[0-9]+$)|(.*?(username|password|token|time).*?))/', $key)) {
                continue;
            }
            if ($value !== null) {
                $query_str['_'.$key] = $value;
            }
        }
        foreach ($query_string as $key => $value) {
            if ($value !== null) {
                $query_str[$key] = $value;
            }
        }
        return $this->withQuery($this->paramsToQuery($query_str, true));
    }

    /**
     * สร้าง Uri จากตัวแปร $_SERVER
     *
     * @throws \InvalidArgumentException ถ้า Uri ไม่ถูกต้อง
     *
     * @return \static
     */
    public static function createFromGlobals()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://';
        } elseif ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) {
            $scheme = 'https://';
        } else {
            $scheme = 'http://';
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = trim(current(explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'])));
        } elseif (empty($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['SERVER_NAME'];
        } else {
            $host = $_SERVER['HTTP_HOST'];
        }
        $pos = strpos($host, ':');
        if ($pos !== false) {
            $port = (int) substr($host, $pos + 1);
            $host = strstr($host, ':', true);
        } else {
            $port = isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 80;
        }
        $path = empty($_SERVER['REQUEST_URI']) ? '/' : parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
        $user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
        $pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
        return new static($scheme, $host, $path, $query, $port, $user, $pass);
    }

    /**
     * สร้างคลาสจากลิงค์
     *
     * @param string $uri
     *
     * @throws \InvalidArgumentException ถ้า $uri ไม่ถูกต้อง
     *
     * @return \static
     */
    public static function createFromUri($uri)
    {
        $parts = parse_url($uri);
        if (false === $parts) {
            throw new \InvalidArgumentException('Invalid Uri');
        } else {
            $scheme = isset($parts['scheme']) ? $parts['scheme'] : '';
            $host = isset($parts['host']) ? $parts['host'] : '';
            $port = isset($parts['port']) ? $parts['port'] : null;
            $user = isset($parts['user']) ? $parts['user'] : '';
            $pass = isset($parts['pass']) ? $parts['pass'] : '';
            $path = isset($parts['path']) ? $parts['path'] : '';
            $query = isset($parts['query']) ? $parts['query'] : '';
            $fragment = isset($parts['fragment']) ? $parts['fragment'] : '';
            return new static($scheme, $host, $path, $query, $port, $user, $pass, $fragment);
        }
    }

    /**
     * ตืนค่า authority ของ Uri [user-info@]host[:port]
     *
     * @return string
     */
    public function getAuthority()
    {
        return ($this->userInfo ? $this->userInfo.'@' : '').$this->host.($this->port !== null ? ':'.$this->port : '');
    }

    /**
     * แปลง GET เป็น query string สำหรับการส่งกลับไปหน้าเดิม ที่มาจากการโพสต์ด้วยฟอร์ม
     *
     * @param string $url          URL ที่ต้องการส่งกลับ เช่น index.php
     * @param array  $query_string (option) query string ที่ต้องการส่งกลับไปด้วย array('key' => 'value', ...)
     *
     * @return string URL+query string
     */
    public function getBack($url, $query_string = array())
    {
        return $this->createBack($url, $_GET, $query_string);
    }

    /**
     * คืนค่า fragment (ข้อมูลหลัง # ใน Uri) ของ Uri
     *
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * คืนค่า Hostname ของ Uri เช่น domain.tld
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * คืนค่า path ของ Uri เช่น /kotchasan
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * คืนค่าหมายเลข Port ของ Uri
     * ไม่ระบุหรือเป็น default port (80,433) คืนค่า null
     *
     * @return null|int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * คืนค่า query string (ข้อมูลหลัง ? ใน Uri) ของ Uri
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * คืนค่า scheme ของ Uri ไม่รวม :// เช่น http, https.
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * คืนค่าข้อมูล user ของ Uri user[:password]
     *
     * @return string
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * ฟังก์ชั่นแสดงผลตัวแบ่งหน้า
     *
     * @param int $totalpage จำนวนหน้าทั้งหมด
     * @param int $page      หน้าปัจจุบัน
     * @param int $maxlink   (optional) จำนวนตัวเลือกแบ่งหน้าสูงสุด ค่าปกติ 9
     *
     * @return string
     */
    public function pagination($totalpage, $page, $maxlink = 9)
    {
        if ($totalpage > $maxlink) {
            $start = $page - floor($maxlink / 2);
            if ($start < 1) {
                $start = 1;
            } elseif ($start + $maxlink > $totalpage) {
                $start = $totalpage - $maxlink + 1;
            }
        } else {
            $start = 1;
        }
        $url = '<a href="'.$this->withParams(array('page' => ':page'), true).'" title="{LNG_go to page} :page">:page</a>';
        $splitpage = ($start > 2) ? str_replace(':page', 1, $url) : '';
        for ($i = $start; $i <= $totalpage && $maxlink > 0; ++$i) {
            $splitpage .= ($i == $page) ? '<strong title="{LNG_Showing page} '.$i.'">'.$i.'</strong>' : str_replace(':page', $i, $url);
            --$maxlink;
        }
        $splitpage .= ($i < $totalpage) ? str_replace(':page', $totalpage, $url) : '';
        return empty($splitpage) ? '<strong>1</strong>' : $splitpage;
    }

    /**
     * ฟังก์ชั่นแปลง Queryparams เป็น Querystring
     *
     * @param array $params
     * @param bool  $encode false เชื่อม Querystring ด้วย &, true เชื่อม Querystring ด้วย &amp;
     *
     * @return string
     */
    public function paramsToQuery($params, $encode)
    {
        $query_str = array();
        foreach ($params as $key => $value) {
            if (preg_match('/^[a-zA-Z0-9_\-\[\]]+$/', $key)) {
                if ($value === null) {
                    $query_str[$key] = $key;
                } else {
                    $query_str[$key] = $key.'='.$this->filterQueryFragment($value);
                }
            }
        }
        return implode($encode ? '&amp;' : '&', $query_str);
    }

    /**
     * ฟังก์ชั่น แยก Querystring ออกเป็น array
     *
     * @param string $query
     *
     * @return array
     */
    public function parseQueryParams($query)
    {
        $result = array();
        if (!empty($query)) {
            foreach (explode('&', str_replace('&amp;', '&', $query)) as $item) {
                if (preg_match('/^([a-zA-Z0-9_\-\[\]]+)(=(.*))?$/', $item, $match)) {
                    if (isset($match[3])) {
                        if (!(preg_match('/^[0-9]+$/', $match[1]) && $match[3] === '')) {
                            $result[$match[1]] = $match[3];
                        }
                    } else {
                        $result[$match[1]] = null;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * แปลง POST เป็น query string สำหรับการส่งกลับไปหน้าเดิม ที่มาจากการโพสต์ด้วยฟอร์ม
     * คืนค่า URL+query string
     *
     * @param string $url          URL ที่ต้องการส่งกลับ เช่น index.php
     * @param array  $query_string (option) query string ที่ต้องการส่งกลับไปด้วย array('key' => 'value', ...)
     *
     * @return string
     */
    public function postBack($url, $query_string = array())
    {
        return $this->createBack($url, $_POST, $query_string);
    }

    /**
     * กำหนดค่า fragment
     * คืนค่า Object ใหม่
     *
     * @param string $fragment
     *
     * @throws \InvalidArgumentException ถ้า fragment ไม่ถูกต้อง
     *
     * @return \static
     */
    public function withFragment($fragment)
    {
        if (!is_string($fragment) && !method_exists($fragment, '__toString')) {
            throw new \InvalidArgumentException('Uri fragment must be a string');
        }
        $fragment = ltrim((string) $fragment, '#');
        $clone = clone $this;
        $clone->fragment = $this->filterQueryFragment($fragment);
        return $clone;
    }

    /**
     * กำหนดชื่อ host
     * คืนค่า Object ใหม่
     *
     * @param string $host ชื่อ host
     *
     * @return \static
     */
    public function withHost($host)
    {
        $clone = clone $this;
        $clone->host = $host;
        return $clone;
    }

    /**
     * ฟังก์ชั่นแทนที่ Query params ลงใน URL
     *
     * @param array $params
     * @param bool  $encode false (default) เชื่อม Querystring ด้วย &, true เชื่อม Querystring ด้วย &amp;
     *
     * @return \static
     */
    public function withParams($params, $encode = false)
    {
        $query_str = array();
        foreach ($this->parseQueryParams($this->query) as $key => $value) {
            $query_str[$key] = $value;
        }
        foreach ($params as $key => $value) {
            $query_str[$key] = $value;
        }
        return $this->withQuery($this->paramsToQuery($query_str, $encode));
    }

    /**
     * ฟังก์ลบ Query params ออกจาก URL.
     *
     * @param string|array $names  ชื่อของ attributes ที่ต้องการลบ
     * @param bool         $encode false (default) เชื่อม Querystring ด้วย &, true เชื่อม Querystring ด้วย &amp;
     *
     * @return \static
     */
    public function withoutParams($names, $encode = false)
    {
        $attributes = $this->parseQueryParams($this->query);
        if (is_array($names)) {
            foreach ($names as $name) {
                unset($attributes[$name]);
            }
        } else {
            unset($attributes[$names]);
        }
        return $this->withQuery($this->paramsToQuery($attributes, $encode));
    }

    /**
     * กำหนดชื่อ path
     * path ต้องเริ่มต้นด้วย / เช่น /kotchasan
     * หรือเป็นค่าว่าง ถ้าเป็นรากของโดเมน
     * คืนค่า Object ใหม่
     *
     * @param string $path ชื่อ path
     *
     * @return \static
     */
    public function withPath($path)
    {
        $clone = clone $this;
        $clone->path = $this->filterPath($path);
        return $clone;
    }

    /**
     * กำหนดค่า port
     * คืนค่า Object ใหม่
     *
     * @param null|int $port หมายเลข port 1- 65535 หรือ null
     *
     * @throws \InvalidArgumentException ถ้า port ไม่ถูกต้อง
     *
     * @return \static
     */
    public function withPort($port)
    {
        $clone = clone $this;
        $clone->port = $this->filterPort($this->scheme, $this->host, $port);
        return $clone;
    }

    /**
     * กำหนดค่า query string
     * คืนค่า Object ใหม่
     *
     * @param string $query
     *
     * @throws \InvalidArgumentException ถ้า query string ไม่ถูกต้อง
     *
     * @return \static
     */
    public function withQuery($query)
    {
        if (!is_string($query) && !method_exists($query, '__toString')) {
            throw new \InvalidArgumentException('Uri query must be a string');
        }
        $query = ltrim((string) $query, '?');
        $clone = clone $this;
        $clone->query = $this->filterQueryFragment($query);
        return $clone;
    }

    /**
     * กำหนดค่า scheme ของ Uri
     * คืนค่า Object ใหม่
     *
     * @param string $scheme http หรือ https หรือค่าว่าง
     *
     * @throws \InvalidArgumentException ถ้าไม่ใช่ ค่าว่าง http หรือ https
     *
     * @return \static
     */
    public function withScheme($scheme)
    {
        $clone = clone $this;
        $clone->scheme = $this->filterScheme($scheme);
        return $clone;
    }

    /**
     * กำหนดข้อมูล user ของ Uri
     * คืนค่า Object ใหม่
     *
     * @param string $user
     * @param string $password
     *
     * @return \static
     */
    public function withUserInfo($user, $password = null)
    {
        $clone = clone $this;
        $clone->userInfo = $user.($password ? ':'.$password : '');
        return $clone;
    }

    /**
     * แปลง POST เป็น query string สำหรับการส่งกลับไปหน้าเดิม ที่มาจากการโพสต์ด้วยฟอร์ม
     * คืนค่า URL+query string
     *
     * @param string $url          URL ที่ต้องการส่งกลับ เช่น index.php
     * @param array  $source       query string จาก $_POST หรือ $_GET
     * @param array  $query_string query string ที่ต้องการส่งกลับไปด้วย array('key' => 'value', ...)
     *
     * @return string
     */
    private function createBack($url, $source, $query_string)
    {
        foreach ($source as $key => $value) {
            if ($value !== '' && !preg_match('/.*?(username|password|token|time).*?/', $key) && preg_match('/^_{1,}(.*)$/', $key, $match)) {
                if (!isset($query_string[$match[1]])) {
                    $query_string[$match[1]] = $value;
                }
            }
        }
        if (isset($query_string['time'])) {
            $query_string['time'] = time();
        }
        $query_str = array();
        foreach ($query_string as $key => $value) {
            if ($value !== null) {
                $query_str[$key] = $value;
            }
        }
        return $url.(strpos($url, '?') === false ? '?' : '&').$this->paramsToQuery($query_str, false);
    }

    /**
     * สร้าง Uri
     * เช่น http://domain.tld/
     *
     * @param string $scheme
     * @param string $authority
     * @param string $path
     * @param string $query
     * @param string $fragment
     *
     * @return string
     */
    private static function createUriString($scheme, $authority, $path, $query, $fragment)
    {
        $uri = '';
        if (!empty($scheme)) {
            $uri .= $scheme.'://';
        }
        if (!empty($authority)) {
            $uri .= $authority;
        }
        if ($path != null) {
            if ($uri && substr($path, 0, 1) !== '/') {
                $uri .= '/';
            }
            $uri .= $path;
        }
        if ($query != '') {
            $uri .= '?'.$query;
        }
        if ($fragment != '') {
            $uri .= '#'.$fragment;
        }
        return $uri;
    }

    /**
     * ตรวจสอบ path
     *
     * @param  $path
     *
     * @return string
     */
    private function filterPath($path)
    {
        return preg_replace_callback('/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/', function ($match) {
            return rawurlencode($match[0]);
        }, $path);
    }

    /**
     * ตรวจสอบ port
     *
     * @param string $scheme
     * @param string $host
     * @param int    $port
     *
     * @throws \InvalidArgumentException ถ้า port ไม่ถูกต้อง
     *
     * @return int|null
     */
    private function filterPort($scheme, $host, $port)
    {
        if (null !== $port) {
            $port = (int) $port;
            if (1 > $port || 0xffff < $port) {
                throw new \InvalidArgumentException('Port number must be between 1 and 65535');
            }
        }
        return $this->isNonStandardPort($scheme, $host, $port) ? $port : null;
    }

    /**
     * ตรวจสอบ query และ fragment
     *
     * @param  $str
     *
     * @return string
     */
    private function filterQueryFragment($str)
    {
        return preg_replace_callback('/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/', function ($match) {
            return rawurlencode($match[0]);
        }, $str);
    }

    /**
     * ตรวจสอบ scheme
     *
     * @param string $scheme
     *
     * @throws \InvalidArgumentException ถ้าไม่ใช่ ค่าว่าง http หรือ https
     *
     * @return string
     */
    private function filterScheme($scheme)
    {
        $schemes = array('' => '', 'http' => 'http', 'https' => 'https');
        $scheme = rtrim(strtolower($scheme), ':/');
        if (isset($schemes[$scheme])) {
            return $scheme;
        } else {
            throw new \InvalidArgumentException('Uri scheme must be http, https or empty string');
        }
    }

    /**
     * ตรวจสอบว่าเป็น port มาตรฐานหรือไม่
     * เช่น http เป็น 80 หรือ https เป็น 433
     *
     * @param string $scheme
     * @param string $host
     * @param int    $port
     *
     * @return bool
     */
    private function isNonStandardPort($scheme, $host, $port)
    {
        if (!$scheme && $port) {
            return true;
        }
        if (!$host || !$port) {
            return false;
        }
        return ($scheme != 'http' && $scheme != 'https') || ($port != 80 && $port != 443);
    }
}
