<?php
/**
 * @filesource modules/inventory/controllers/customers.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Inventory\Customers;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=inventory-customers
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * ตารางรายชื่อ ลูกค้า.
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // ข้อความ title bar
        $this->title = Language::get('Customer list');
        // เลือกเมนู
        $this->menu = 'customer';
        // สามารถขายได้
        if (Login::checkPermission(Login::isMember(), array('can_inventory_order', 'can_manage_inventory'))) {
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
            $ul->appendChild('<li><span>{LNG_Customer list} {LNG_Supplier}</span></li>');
            $section->add('header', array(
                'innerHTML' => '<h2 class="icon-customer">'.$this->title.'</h2>',
            ));
            // แสดงตาราง
            $section->appendChild(createClass('Inventory\Customers\View')->render($request));
            // คืนค่า HTML
            return $section->render();
        }
        // 404
        return \Index\Error\Controller::execute($this, $request->getUri());
    }
}
