<?php
/**
 * @filesource modules/inventory/controllers/initmenu.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Inventory\Initmenu;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * Init Menu
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\KBase
{
    /**
     * ฟังก์ชั่นเริ่มต้นการทำงานของโมดูลที่ติดตั้ง
     * และจัดการเมนูของโมดูล
     *
     * @param Request                $request
     * @param \Index\Menu\Controller $menu
     * @param array                  $login
     */
    public static function execute(Request $request, $menu, $login)
    {
        // สามารถตั้งค่าระบบได้
        if (Login::checkPermission($login, 'can_config')) {
            $menu->add('settings', '{LNG_Accounting settings}', 'index.php?module=inventory-settings', null, 'inventory');
            $menu->add('settings', '{LNG_Pictures for a receipt}', 'index.php?module=inventory-image', null, 'image');
        }
        // สามารถดูรายชื่อลูกค้าได้
        if (Login::checkPermission($login, array('can_inventory_order', 'can_manage_inventory'))) {
            $menu->addTopLvlMenu('customer', '{LNG_Customer}/{LNG_Supplier}', 'index.php?module=inventory-customers', null, 'member');
        }
        // สามารถบริหารคลังสินค้าได้
        if (Login::checkPermission($login, 'can_manage_inventory')) {
            foreach (Language::get('INVENTORY_CATEGORIES', array()) as $type => $text) {
                $menu->add('settings', $text, 'index.php?module=inventory-categories&amp;type='.$type, null, 'category'.$type);
            }
            foreach (Language::get('INVENTORY_METAS', array()) as $type => $text) {
                $menu->add('settings', $text, 'index.php?module=inventory-categories&amp;type='.$type, null, 'category'.$type);
            }
            $menu->add('settings', '{LNG_Inventory}', 'index.php?module=inventory-setup', null, 'setup');
        }
        // สามารถขายได้
        if (Login::checkPermission($login, 'can_inventory_order')) {
            $menu->addTopLvlMenu('products', '{LNG_Inventory}', 'index.php?module=inventory-products', null, 'customer');
            $submenus = array();
            foreach (Language::get('ORDER_STATUS') as $k => $v) {
                if (in_array($k, self::$cfg->buy_status)) {
                    $submenus['buy'][$k] = array(
                        'text' => $v,
                        'url' => 'index.php?module=inventory-orders&amp;status='.$k,
                    );
                } else {
                    $submenus['sell'][$k] = array(
                        'text' => $v,
                        'url' => 'index.php?module=inventory-orders&amp;status='.$k,
                    );
                }
            }
            $menu->addTopLvlMenu('purchase', '{LNG_Purchase}', null, $submenus['buy'], 'products');
            $menu->addTopLvlMenu('sales', '{LNG_Sales}', null, $submenus['sell'], 'purchase');
        }
    }
}
