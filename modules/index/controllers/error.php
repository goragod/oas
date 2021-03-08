<?php
/**
 * @filesource modules/index/controllers/error.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Error;

use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Template;

/**
 * Error Controller ถ้าไม่สามารถทำรายการได้
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * init Class
     */
    public function __construct()
    {
        // ค่าเริ่มต้นของ Controller
        $this->title = static::getMessage();
        $this->menu = 'home';
        $this->status = 404;
    }

    /**
     * แสดงข้อผิดพลาด (เช่น 404 page not found)
     * สำหรับการเรียกโดย GLoader.
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        $template = Template::create('', '', '404');
        $template->add(array(
            '/{TOPIC}/' => $this->title,
            '/{DETAIL}/' => $this->title,
        ));
        // คืนค่า HTML
        return $template->render();
    }

    /**
     * แสดงข้อผิดพลาด (เช่น 404 page not found)
     *
     * @param string $menu
     * @param Kotchasan\Http\Uri $uri
     *
     * @return \Gcms\Controller
     */
    public static function execute(\Gcms\Controller $controller, $uri = null)
    {
        if ($uri === null || \Gcms\Login::isMember()) {
            $template = Template::create($controller->menu, '', '404');
            $message = static::getMessage();
            $template->add(array(
                '/{TOPIC}/' => $message,
                '/{DETAIL}/' => $message,
            ));
            $controller->title = strip_tags($message);
            $controller->menu = $controller->menu;
            $controller->status = 404;
            // คืนค่า HTML
            return $template->render();
        } else {
            if (defined('MAIN_INIT') && MAIN_INIT == 'indexhtml') {
                // URL ปกติ
                $ret = (string) $uri;
            } else {
                // มาจาก loader
                $ret = 'reload';
            }
            // ฟอร์ม login
            $index = \Index\Welcome\View::login(self::$request->withQueryParams(array('ret' => $ret)));
            // ข้อความ title bar
            $controller->title = $index->title;
            // คืนค่า HTML
            return $index->detail;
        }
    }

    /**
     * คืนค่าข้อความ error
     *
     * @param string $message
     *
     * @return string
     */
    private static function getMessage($message = '')
    {
        return Language::get($message == '' ? 'Sorry, cannot find a page called Please check the URL or try the call again.' : $message);
    }
}
