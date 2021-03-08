<?php
/**
 * @filesource modules/index/controllers/welcome.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Welcome;

use Kotchasan\Http\Request;

/**
 * Controller สำหรับการเข้าระบบ
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * forgot, login register
     *
     * @param Request $request
     *
     * @return string
     */
    public function execute(Request $request)
    {
        // action ที่เลือก
        $action = $request->get('action')->toString();
        // ตรวจสอบ method ที่กำหนดไว้เท่านั้น
        if ($action == 'register' && !empty(self::$cfg->user_register)) {
            $action = 'register';
        } elseif ($action == 'forgot' && !empty(self::$cfg->user_forgot)) {
            $action = 'forgot';
        } else {
            $action = 'login';
        }
        // ประมวลผลหน้าที่เรียก
        $page = \Index\Welcome\View::$action($request);
        // ไตเติลจากและเนื้อหาจาก View
        $this->title = $page->title;
        $this->detail = $page->detail;
        return $this;
    }
}
