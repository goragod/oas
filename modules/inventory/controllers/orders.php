<?php
/**
 * @filesource modules/inventory/controllers/orders.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Inventory\Orders;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=inventory-orders
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * รายการ Orders
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // ค่าที่ส่งมา
        $owner = (object) array(
            'order_status' => Language::get('ORDER_STATUS'),
            'year' => $request->request('year', date('Y'))->toInt(),
            'month' => $request->request('month', date('m'))->toInt(),
            'day' => $request->request('day', date('d'))->toInt(),
            'status' => $request->request('status')->filter('A-Z'),
        );
        // สมาชิก
        $login = Login::isMember();
        if (in_array($owner->status, self::$cfg->buy_status) && Login::checkPermission($login, 'can_manage_inventory')) {
            // จัดซื้อ
            $this->menu = 'purchase';
            $sub_title = '{LNG_Purchase}';
            $title = '{LNG_Order report} ';
        } elseif (Login::checkPermission($login, 'can_inventory_order')) {
            // พนักงานขาย
            $owner->status = isset($owner->order_status[$owner->status]) ? $owner->status : 'OUT';
            $this->menu = 'sales';
            $sub_title = '{LNG_Sales}';
            $title = '{LNG_Sales report} ';
        } else {
            // ไม่สามารถทำรายการได้
            $owner->status = null;
        }
        if (isset($owner->order_status[$owner->status])) {
            $title .= $owner->order_status[$owner->status];
            if ($owner->day > 0) {
                $title .= ' {LNG_date}  '.$owner->day;
            }
            if ($owner->month > 0) {
                $title .= ' {LNG_month}  '.Language::find('MONTH_LONG', null, $owner->month);
            }
            if ($owner->year > 0) {
                $title .= ' {LNG_year} '.($owner->year + Language::get('YEAR_OFFSET'));
            }
            $this->title = Language::trans($title);
            // แสดงผล
            $section = Html::create('section', array(
                'class' => 'content_bg',
            ));
            // breadcrumbs
            $breadcrumbs = $section->add('div', array(
                'class' => 'breadcrumbs',
            ));
            $ul = $breadcrumbs->add('ul');
            $ul->appendChild('<li><a href="index.php" class="icon-home">{LNG_Home}</a></li>');
            $ul->appendChild('<li><span>'.$sub_title.'</span></li>');
            $ul->appendChild('<li><span>{LNG_Report}</span></li>');
            $section->add('header', array(
                'innerHTML' => '<h2 class="icon-report">'.$this->title.'</h2>',
            ));
            // แสดงตาราง
            $section->appendChild(createClass('Inventory\Orders\View')->render($request, $owner));
            // คืนค่า HTML
            return $section->render();
        }
        // 404
        return \Index\Error\Controller::execute($this, $request->getUri());
    }
}
