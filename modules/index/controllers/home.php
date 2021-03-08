<?php
/**
 * @filesource modules/index/controllers/home.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Home;

use Gcms\Login;
use Kotchasan\Collection;
use Kotchasan\Html;
use Kotchasan\Http\Request;

/**
 * module=home
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * Dashboard
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // ไตเติล
        $this->title = self::$cfg->web_title.' - '.self::$cfg->web_description;
        // เมนู
        $this->menu = 'home';
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
        $section->add('header', array(
            'innerHTML' => '<h2 class="icon-dashboard">{LNG_Dashboard}</h2>',
        ));
        // Login
        $login = Login::isMember();
        // card
        $card = new Collection();
        $menu = new Collection();
        $block = new Collection();
        // โหลด Component หน้า Home
        $dir = ROOT_PATH.'modules/';
        $f = @opendir($dir);
        if ($f) {
            while (false !== ($text = readdir($f))) {
                if ($text != '.' && $text != '..' && $text != 'index' && $text != 'css' && $text != 'js' && is_dir($dir.$text)) {
                    if (is_file($dir.$text.'/controllers/home.php')) {
                        require_once $dir.$text.'/controllers/home.php';
                        $className = '\\'.ucfirst($text).'\Home\Controller';
                        if (method_exists($className, 'addCard')) {
                            $className::addCard($request, $card, $login);
                        }
                        if (method_exists($className, 'addMenu')) {
                            $className::addMenu($request, $menu, $login);
                        }
                        if (method_exists($className, 'addBlock')) {
                            $className::addBlock($request, $block, $login);
                        }
                    }
                }
            }
            closedir($f);
        }
        // แสดงจำนวนสมาชิกทั้งหมด
        if ($card->count() < 4 && Login::checkPermission($login, 'can_config')) {
            self::renderCard($card, 'icon-users', '{LNG_Users}', number_format(\Index\Member\Model::getCount()), '{LNG_Member list}', 'index.php?module=member');
        }
        // dashboard
        $dashboard = $section->add('div', array(
            'class' => 'dashboard clear',
        ));
        // render card
        foreach ($card as $item) {
            $dashboard->add('div', array(
                'class' => 'card',
                'innerHTML' => $item,
            ));
        }
        // render quick menu
        if ($menu->count() > 0) {
            $dashboard = $section->add('div', array(
                'class' => 'dashboard clear',
            ));
            $dashboard->add('h3', array(
                'innerHTML' => '<span class=icon-menus>{LNG_Quick Menu}</span>',
            ));
            $n = 0;
            foreach ($menu as $k => $item) {
                if ($n == 0 || $n % 4 == 0) {
                    $ggrid = $dashboard->add('div', array(
                        'class' => 'ggrid row',
                    ));
                }
                $ggrid->add('section', array(
                    'class' => 'qmenu block3 float-left',
                    'innerHTML' => $item,
                ));
                ++$n;
            }
        }
        // render block
        if ($block->count() > 0) {
            foreach ($block as $k => $item) {
                $section->add('div', array(
                    'class' => 'dashboard',
                    'innerHTML' => $item,
                ));
            }
        }
        return $section->render();
    }

    /**
     * ฟังก์ชั่นสร้าง card ในหน้า Home
     *
     * @param Collection $card
     * @param string     $icon
     * @param string     $title
     * @param string     $value
     * @param string     $link
     * @param string     $url
     */
    public static function renderCard($card, $icon, $title, $value, $link, $url = null)
    {
        $content = '<a class="card-item"'.($url === null ? '' : ' href="'.$url.'"').'>';
        $content .= '<span class="card-subitem '.$icon.' icon"></span>';
        $content .= '<span class="card-subitem">';
        $content .= '<span class="cuttext title" title="'.strip_tags($title).'">'.$title.'</span>';
        $content .= '<b class="cuttext">'.$value.'</b>';
        $content .= '<span class="cuttext title" title="'.strip_tags($link).'">'.$link.'</span>';
        $content .= '</span>';
        $content .= '</a>';
        $card->set(\Kotchasan\Password::uniqid(), $content);
    }

    /**
     * ฟังก์ชั่นสร้าง เมนูด่วน ในหน้า Home.
     *
     * @param Collection $menu
     * @param string     $icon
     * @param string     $title
     * @param string     $url
     */
    public static function renderQuickMenu($menu, $icon, $title, $url)
    {
        $menu->set($title, '<a class="cuttext" href="'.$url.'"><span class="'.$icon.'">'.$title.'</span></a>');
    }
}
