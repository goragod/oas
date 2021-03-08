<?php
/**
 * @filesource Kotchasan/View.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

/**
 * View base class.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\KBase
{
    /**
     * ตัวแปรเก็บเนื่อหาของเว็บไซต์ ที่จะแทนที่หลังจาก render แล้ว.
     *
     * @var array
     */
    protected $after_contents = array();
    /**
     * ตัวแปรเก็บเนื่อหาของเว็บไซต์.
     *
     * @var array
     */
    protected $contents = array();
    /**
     * รายการ header.
     *
     * @var array
     */
    protected $headers = array();
    /**
     * meta tag.
     *
     * @var array
     */
    protected $metas = array();
    /**
     * คำสั่ง Javascript ที่จะแทรกไว้ใน head.
     *
     * @var array
     */
    protected $script = array();

    /**
     * ใส่ไฟล์ CSS ลงใน header.
     *
     * @param string $url
     */
    public function addCSS($url)
    {
        $this->metas[$url] = '<link rel=stylesheet href="'.$url.'">';
    }

    /**
     * ใส่ไฟล์ Javascript ลงใน header.
     *
     * @param string $url
     */
    public function addJavascript($url)
    {
        $this->metas[$url] = '<script src="'.$url.'"></script>';
    }

    /**
     * เพิ่มคำสั่ง Javascript ใส่ลงใน head ก่อนปิด head.
     *
     * @param string $script
     */
    public function addScript($script)
    {
        $this->script[] = $script;
    }

    /**
     * ฟังก์ชั่น แทนที่ query string ด้วยข้อมูลจาก GET สำหรับส่งต่อไปยัง URL ถัดไป.
     * array ส่งมาจาก preg_replace
     * string กำหนดเอง
     * คืนค่า query string ใหม่ ลบ id=0.
     *
     * @assert (array(2 => 'module=retmodule&id=0')) [==] "http://localhost/?module=retmodule&amp;page=1&amp;sort=id"  [[$_SERVER['QUERY_STRING'] = '_module=test&1234&_page=1&_sort=id&action=login&id=1']]
     * @assert ('module=retmodule&5678') [==] "http://localhost/?module=retmodule&amp;page=1&amp;sort=id&amp;id=1&amp;5678"
     *
     * @param array|string $f รับค่าจากตัวแปร $f มาสร้าง query string
     *
     * @return string
     */
    public static function back($f)
    {
        $uri = self::$request->getUri();
        $query_url = array();
        foreach (explode('&', $uri->getQuery()) as $item) {
            if (preg_match('/^(_)?(.*)=([^$]{1,})$/', $item, $match)) {
                if ($match[2] == 'action' && ($match[3] == 'login' || $match[3] == 'logout')) {
                    // ไม่ใช้รายการ action=login, action=logout
                } else {
                    $query_url[$match[2]] = $match[3];
                }
            }
        }
        if (is_array($f)) {
            $f = isset($f[2]) ? $f[2] : null;
        }
        if (!empty($f)) {
            foreach (explode('&', $f) as $item) {
                if (preg_match('/^([a-zA-Z0-9_\-]+)(=(.*))?$/', $item, $match)) {
                    if (!isset($match[3])) {
                        // ไม่มี value
                        $query_url[$match[1]] = null;
                    } elseif ($match[3] === '0') {
                        // ไม่ใช้รายการที่หลังเครื่องหมาย = เท่ากับ 0
                        unset($query_url[$match[1]]);
                    } else {
                        $query_url[$match[1]] = $match[3];
                    }
                }
            }
        }

        return (string) $uri->withQuery($uri->paramsToQuery($query_url, true));
    }

    /**
     * ส่งออกเนื้อหา และ header ตามที่กำหนด.
     *
     * @param string $content เนื้อหา
     */
    public function output($content)
    {
        // send header
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
        // output content
        echo $content;
    }

    /**
     * ส่งออกเป็น HTML.
     *
     * @param string|null $template HTML Template ถ้าไม่กำหนด (null) จะใช้ index.html
     */
    public function renderHTML($template = null)
    {
        // default for template
        $this->contents['/{WEBTITLE}/'] = self::$cfg->web_title;
        $this->contents['/{WEBDESCRIPTION}/'] = self::$cfg->web_description;
        $this->contents['/{WEBURL}/'] = WEB_URL;
        $this->contents['/{SKIN}/'] = Template::get();
        foreach ($this->after_contents as $key => $value) {
            $this->contents[$key] = $value;
        }
        $head = '';
        if (!empty($this->metas)) {
            $head .= implode("\n", $this->metas);
        }
        if (!empty($this->script)) {
            $head .= "<script>\n".implode("\n", $this->script)."\n</script>";
        }
        if ($head != '') {
            $this->contents['/(<head.*)(<\/head>)/isu'] = '$1'.$head.'$2';
        }
        // แทนที่ลงใน Template
        if ($template === null) {
            // ถ้าไม่ได้กำหนดมาใช้ index.html
            $template = Template::load('', '', 'index');
        }

        return Template::pregReplace(array_keys($this->contents), array_values($this->contents), $template);
    }

    /**
     * ใส่เนื้อหาลงใน $contens.
     *
     * @param array $array ชื่อที่ปรากฏใน template รูปแบบ array(key1 => val1, key2 => val2)
     */
    public function setContents($array)
    {
        foreach ($array as $key => $value) {
            $this->contents[$key] = $value;
        }
    }

    /**
     * ใส่เนื้อหาลงใน $contens หลัง render แล้ว.
     *
     * @param array $array ชื่อที่ปรากฏใน template รูปแบบ array(key1 => val1, key2 => val2)
     */
    public function setContentsAfter($array)
    {
        foreach ($array as $key => $value) {
            $this->after_contents[$key] = $value;
        }
    }

    /**
     * กำหนด header ให้กับเอกสาร.
     *
     * @param array $array
     */
    public function setHeaders($array)
    {
        foreach ($array as $key => $value) {
            $this->headers[$key] = $value;
        }
    }

    /**
     * ใส่ Tag ลงใน Head ของ HTML.
     *
     * @param array $array
     */
    public function setMetas($array)
    {
        foreach ($array as $key => $value) {
            $this->metas[$key] = $value;
        }
    }

    /**
     * อ่านค่าจาก Array
     * คืนค่า $source[$key]
     * ถ้าไม่มี คืนค่า $default
     *
     * @param array $array
     * @param mixed $key
     * @param mixed $default
     *
     * @return mixed
     */
    public static function array_value($array, $key, $default = '')
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * อ่านค่าจาก Object
     * คืนค่า $source->{$key}
     * ถ้าไม่มี คืนค่า $default
     *
     * @param array|object $source
     * @param string|int $key
     * @param string|int $default
     *
     * @return mixed
     */
    public static function object_value($source, $key, $default = '')
    {
        return isset($source->{$key}) ? $source->{$key} : $default;
    }
}
