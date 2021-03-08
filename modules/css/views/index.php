<?php
/**
 * @filesource modules/css/views/index.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Css\Index;

/**
 * Generate CSS file.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\KBase
{
    /**
     * สร้างไฟล์ CSS.
     */
    public function index()
    {
        // โหลด css หลัก
        $data = file_get_contents(ROOT_PATH.'skin/fonts.css');
        $data = preg_replace('/url\(([\'"])?fonts\//isu', 'url(\\1'.WEB_URL.'skin/fonts/', $data);
        $data .= file_get_contents(ROOT_PATH.'skin/gcss.css');
        // css ของ template
        $data2 = file_get_contents(ROOT_PATH.self::$cfg->skin.'/style.css');
        $data2 = preg_replace('/url\(([\'"])?(img|fonts)\//isu', 'url(\\1'.WEB_URL.self::$cfg->skin.'/\\2/', $data2);
        // โหลดโมดูลที่ติดตั้งแล้ว
        $modules = \Gcms\Modules::create();
        // ไดเร็คทอรี่โมดูล
        $dir = $modules->getDir();
        // css ของโมดูล
        foreach ($modules->get() as $module) {
            if (is_file($dir.$module.'/style.css')) {
                $data2 .= preg_replace('/url\(img\//isu', 'url('.WEB_URL.'modules/'.$module.'/img/', file_get_contents($dir.$module.'/style.css'));

            }
        }
        $data2 .= 'header.header,body.loginpage,.language-menu li>a:hover,.topmenu>ul ul>li.hover>a,.topmenu>ul ul>li:hover>a,.gdpanel a:hover{background-color:'.self::$cfg->bg_color.'}';
        $data2 .= 'header.header .td,.topmenu>ul>li,.language-menu li>a:hover,.topmenu>ul ul>li.hover>a,.topmenu>ul ul>li:hover>a,.gdpanel a:hover{color:'.self::$cfg->color.'}';
        $data2 .= '.tab_menus>li.select,.tab_menus>li:hover{background-color:'.self::$cfg->bg_color.';color:'.self::$cfg->color.'}';
        $data2 .= '.tab_menus ul>li:hover{background-color:'.self::$cfg->bg_color.';color:'.self::$cfg->color.'}';
        foreach (self::$cfg->color_status as $key => $value) {
            $data2 .= '.status'.$key.'{color:'.$value.'}';
        }
        // compress css
        $data = self::compress($data.$data2);
        // Response
        $response = new \Kotchasan\Http\Response();
        $response->withHeaders(array(
            'Content-type' => 'text/css; charset=utf-8',
            'Cache-Control' => 'max-age=31557600',
        ))
            ->withContent($data)
            ->send();
    }

    /**
     * @param $css
     */
    public static function compress($css)
    {
        return preg_replace(array('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '/[\s]{0,}([:;,>\{\}])[\s]{0,}/', '/[\r\n\t]/s', '/[\s]{2,}/s', '/;}/'), array('', '\\1', '', ' ', '}'), $css);
    }
}
