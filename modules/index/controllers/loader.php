<?php
/**
 * @filesource modules/index/controllers/loader.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Loader;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Template;

/**
 * Controller สำหรับโหลดหน้าเว็บด้วย GLoader
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * มาจากการเรียกด้วย GLoader
     * ให้ผลลัพท์เป็น JSON String
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        // session, referer
        if ($request->initSession() && $request->isReferer()) {
            // ตัวแปรป้องกันการเรียกหน้าเพจโดยตรง
            define('MAIN_INIT', 'loader');
            // ตรวจสอบการ login
            Login::create();
            // สมาชิก
            $login = Login::isMember();
            // กำหนด skin ให้กับ template
            Template::init(self::$cfg->skin);
            // View
            self::$view = new \Gcms\View();
            // โหลดเมนู
            self::$menus = \Index\Menu\Controller::init($login);
            // โมดูลจาก URL ถ้าไม่มีใช้ default (home)
            $className = \Index\Main\Controller::parseModule($request, self::$menus->home());
            if ($className === null || !$login) {
                // ถ้าไม่พบหน้าที่เรียก หรือไม่ได้เข้าระบบ แสดงหน้า 404
                include APP_PATH.'modules/index/controllers/error.php';
                $className = 'Index\Error\Controller';
            }
            // create Controller
            $controller = new $className();
            $controller->detail = $controller->render($request);
            // เนื้อหา
            self::$view->setContents(array(
                '/{CONTENT}/' => $controller->detail(),
            ));
            // คืนค่า JSON
            echo json_encode(array(
                'detail' => self::$view->renderHTML(Template::load('', '', 'loader')),
                'menu' => $controller->menu(),
                'title' => $controller->title(),
                'to' => $request->post('to', 'scroll-to')->filter('a-z0-9_\-'),
            ));
        }
    }
}
