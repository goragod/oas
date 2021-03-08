<?php
/**
 * @filesource modules/index/controllers/page.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Page;

use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * page=xxx
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * หน้าเว็บไซต์เปล่าๆ
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // หน้าที่เลือก
        $src = $request->request('src')->filter('a-z');
        $pages = Language::get('PAGES');
        if (array_key_exists($src, $pages)) {
            if (file_exists(ROOT_PATH.DATA_FOLDER.'pages/'.$src.'_'.LANGUAGE.'.html')) {
                // ภาษาที่เลือก
                $content = file_get_contents(ROOT_PATH.DATA_FOLDER.'pages/'.$src.'_'.LANGUAGE.'.html');
                // ข้อความ title bar
                $this->title = $pages[$src];
                // เลือกเมนู
                $this->menu = $src;
                // แสดงผล
                $section = Html::create('section', array(
                    'class' => 'content_bg',
                ));
                // breadcrumbs
                $breadcrumbs = $section->add('div', array(
                    'class' => 'breadcrumbs',
                ));
                $ul = $breadcrumbs->add('ul');
                $ul->appendChild('<li><span class="icon-home">{LNG_Home}</span></li>');
                $ul->appendChild('<li><span>'.$this->title.'</span></li>');
                $section->add('header', array(
                    'innerHTML' => '<h2 class="icon-index">'.$this->title.'</h2>',
                ));
                $section->appendChild('<div class=dashboard>'.$content.'</div>');
                // คืนค่า HTML
                return $section->render();
            }
        }
        // 404
        return \Index\Error\Controller::execute($this);
    }
}
