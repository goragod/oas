<?php
/**
 * @filesource modules/index/views/member.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Member;

use Kotchasan\DataTable;
use Kotchasan\Date;
use Kotchasan\Http\Request;

/**
 * module=member
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ตารางรายชื่อสมาชิก
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // ค่าที่ส่งมา
        $params = array(
            'status' => $request->request('status', -1)->toInt(),
        );
        // สถานะสมาชิก
        $member_status = array(-1 => '{LNG_all items}');
        foreach (self::$cfg->member_status as $key => $value) {
            $member_status[$key] = '{LNG_'.$value.'}';
        }
        // URL สำหรับส่งให้ตาราง
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        // ตาราง
        $table = new DataTable(array(
            /* Uri */
            'uri' => $uri,
            /* Model */
            'model' => \Index\Member\Model::toDataTable($params),
            /* รายการต่อหน้า */
            'perPage' => $request->cookie('member_perPage', 30)->toInt(),
            /* เรียงลำดับ */
            'sort' => $request->cookie('member_sort', 'id desc')->toString(),
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => array($this, 'onRow'),
            /* คอลัมน์ที่ไม่ต้องแสดงผล */
            'hideColumns' => array('id', 'visited'),
            /* คอลัมน์ที่สามารถค้นหาได้ */
            'searchColumns' => array('name', 'username', 'phone'),
            /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
            'action' => 'index.php/index/model/member/action',
            'actionCallback' => 'dataTableActionCallback',
            'actions' => array(
                array(
                    'id' => 'action',
                    'class' => 'ok',
                    'text' => '{LNG_With selected}',
                    'options' => array(
                        'sendpassword' => '{LNG_Get new password}',
                        'active_1' => '{LNG_Current staff}',
                        'active_0' => '{LNG_Past employees}',
                        'delete' => '{LNG_Delete}',
                    ),
                ),
            ),
            /* ตัวเลือกด้านบนของตาราง ใช้จำกัดผลลัพท์การ query */
            'filters' => array(
                array(
                    'name' => 'status',
                    'text' => '{LNG_Member status}',
                    'options' => $member_status,
                    'value' => $params['status'],
                ),
            ),
            /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
            'headers' => array(
                'username' => array(
                    'text' => '{LNG_Email}/{LNG_Username}',
                ),
                'name' => array(
                    'text' => '{LNG_Name}',
                    'sort' => 'name',
                ),
                'active' => array(
                    'text' => '',
                    'colspan' => 2,
                ),
                'phone' => array(
                    'text' => '{LNG_Phone}',
                ),
                'status' => array(
                    'text' => '{LNG_Member status}',
                    'class' => 'center',
                ),
                'create_date' => array(
                    'text' => '{LNG_Created}',
                    'class' => 'center',
                ),
                'lastvisited' => array(
                    'text' => '{LNG_Last login} ({LNG_times})',
                    'class' => 'center',
                    'sort' => 'lastvisited',
                ),
            ),
            /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
            'cols' => array(
                'active' => array(
                    'class' => 'center',
                ),
                'social' => array(
                    'class' => 'center',
                ),
                'status' => array(
                    'class' => 'center',
                ),
                'create_date' => array(
                    'class' => 'center',
                ),
                'lastvisited' => array(
                    'class' => 'center',
                ),
            ),
            /* ปุ่มแสดงในแต่ละแถว */
            'buttons' => array(
                array(
                    'class' => 'icon-edit button green',
                    'href' => $uri->createBackUri(array('module' => 'editprofile', 'id' => ':id')),
                    'text' => '{LNG_Edit}',
                ),
            ),
            /* ปุ่มเพิม */
            'addNew' => array(
                'class' => 'float_button icon-register',
                'href' => $uri->createBackUri(array('module' => 'register', 'id' => 0)),
                'title' => '{LNG_Register}',
            ),
        ));
        // save cookie
        setcookie('member_perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
        setcookie('member_sort', $table->sort, time() + 2592000, '/', HOST, HTTPS, true);
        // คืนค่า HTML
        return $table->render();
    }

    /**
     * จัดรูปแบบการแสดงผลในแต่ละแถว
     *
     * @param array  $item ข้อมูลแถว
     * @param int    $o    ID ของข้อมูล
     * @param object $prop กำหนด properties ของ TR
     *
     * @return array คืนค่า $item กลับไป
     */
    public function onRow($item, $o, $prop)
    {
        $item['create_date'] = Date::format($item['create_date'], 'd M Y');
        if ($item['active'] == 1) {
            $item['active'] = '<span class="icon-valid access" title="{LNG_Current staff}"></span>';
            $item['lastvisited'] = empty($item['lastvisited']) ? '-' : Date::format($item['lastvisited'], 'd M Y H:i').' ('.number_format($item['visited']).')';
        } else {
            $item['active'] = '<span class="icon-valid disabled" title="{LNG_Past employees}"></span>';
            $item['lastvisited'] = '-';
        }
        if ($item['social'] == 1) {
            $item['social'] = '<span class="icon-facebook notext"></span>';
        } elseif ($item['social'] == 2) {
            $item['social'] = '<span class="icon-google notext"></span>';
        } else {
            $item['social'] = '';
        }
        $item['status'] = isset(self::$cfg->member_status[$item['status']]) ? '<span class=status'.$item['status'].'>{LNG_'.self::$cfg->member_status[$item['status']].'}</span>' : '';
        $item['phone'] = self::showPhone($item['phone']);
        return $item;
    }
}
