<?php
/**
 * @filesource modules/index/views/system.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\System;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Language;

/**
 * module=system
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ฟอร์มตั้งค่า system.
     *
     * @param object $config
     * @param array $login
     *
     * @return string
     */
    public function render($config, $login)
    {
        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/index/model/system/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true,
        ));
        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_General}',
        ));
        // web_title
        $fieldset->add('text', array(
            'id' => 'web_title',
            'labelClass' => 'g-input icon-home',
            'itemClass' => 'item',
            'label' => '{LNG_Website title}',
            'comment' => '{LNG_Site Name}',
            'maxlength' => 255,
            'value' => isset($config->web_title) ? $config->web_title : self::$cfg->web_title,
        ));
        // web_description
        $fieldset->add('text', array(
            'id' => 'web_description',
            'labelClass' => 'g-input icon-home',
            'itemClass' => 'item',
            'label' => '{LNG_Description}',
            'comment' => '{LNG_Short description about your website}',
            'maxlength' => 255,
            'value' => isset($config->web_description) ? $config->web_description : self::$cfg->web_description,
        ));
        // timezone
        $datas = array();
        foreach (\DateTimeZone::listIdentifiers() as $item) {
            $datas[$item] = $item;
        }
        $fieldset->add('text', array(
            'id' => 'timezone',
            'labelClass' => 'g-input icon-clock',
            'itemClass' => 'item',
            'label' => '{LNG_Time zone}&nbsp;({LNG_Server time}&nbsp;<em id=server_time>'.date('H:i:s').'</em>&nbsp;{LNG_Local time}&nbsp;<em id=local_time></em>)',
            'comment' => '{LNG_Settings the timing of the server to match the local time}',
            'datalist' => $datas,
            'value' => isset($config->timezone) ? $config->timezone : self::$cfg->timezone,
        ));
        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_User}',
        ));
        // member_only
        $fieldset->add('select', array(
            'id' => 'member_only',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'item',
            'label' => '{LNG_Login per one account}',
            'comment' => '{LNG_Limit access to only one account per member. Members who have logged in before will be forced to log out.}',
            'options' => Language::get('BOOLEANS'),
            'value' => isset($config->member_only) ? $config->member_only : self::$cfg->member_only,
        ));
        // user_forgot
        $fieldset->add('select', array(
            'id' => 'user_forgot',
            'labelClass' => 'g-input icon-password',
            'itemClass' => 'item',
            'label' => '{LNG_Forgot}',
            'options' => Language::get('BOOLEANS'),
            'value' => isset($config->user_forgot) ? $config->user_forgot : 1,
        ));
        // user_register
        $fieldset->add('select', array(
            'id' => 'user_register',
            'labelClass' => 'g-input icon-register',
            'itemClass' => 'item',
            'label' => '{LNG_Register}',
            'options' => Language::get('BOOLEANS'),
            'value' => isset($config->user_register) ? $config->user_register : 1,
        ));
        // login_fields
        $fieldset->add('checkboxgroups', array(
            'id' => 'login_fields',
            'label' => '{LNG_Login by}',
            'comment' => '{LNG_Settings the conditions for member login}',
            'labelClass' => 'g-input icon-signin',
            'options' => Language::get('LOGIN_FIELDS'),
            'value' => isset($config->login_fields) ? $config->login_fields : self::$cfg->login_fields,
        ));
        // welcome_email
        $fieldset->add('select', array(
            'id' => 'welcome_email',
            'labelClass' => 'g-input icon-email',
            'itemClass' => 'item',
            'label' => '{LNG_Send a welcome email to new members}',
            'options' => Language::get('BOOLEANS'),
            'value' => isset($config->welcome_email) ? $config->welcome_email : 0,
        ));
        $notDemoMode = Login::notDemoMode($login);
        // google_client_id
        $fieldset->add('text', array(
            'id' => 'google_client_id',
            'labelClass' => 'g-input icon-google',
            'itemClass' => 'item',
            'label' => '{LNG_Google client ID} <a class=icon-help href="https://gcms.in.th/index.php?module=howto&id=374" target=_blank></a>',
            'comment' => '<em>xxxxxxxxxx</em>.apps.googleusercontent.com',
            'value' => $notDemoMode && isset($config->google_client_id) ? $config->google_client_id : '',
        ));
        // facebook_appId
        $fieldset->add('text', array(
            'id' => 'facebook_appId',
            'labelClass' => 'g-input icon-facebook',
            'itemClass' => 'item',
            'label' => '{LNG_Facebook App ID} <a class=icon-help href="https://gcms.in.th/index.php?module=howto&id=350" target="_blank"></a>',
            'value' => $notDemoMode && isset($config->facebook_appId) ? $config->facebook_appId : '',
        ));
        // demo_mode
        $fieldset->add('select', array(
            'id' => 'demo_mode',
            'labelClass' => 'g-input icon-design',
            'itemClass' => 'item',
            'label' => '{LNG_Demo Mode}',
            'comment' => '{LNG_When enabled Social accounts can be logged in as an administrator. (Some abilities will not be available)}',
            'options' => Language::get('BOOLEANS'),
            'value' => $notDemoMode && isset($config->demo_mode) ? $config->demo_mode : false,
        ));
        $fieldset = $form->add('fieldset', array(
            'titleClass' => 'icon-line',
            'title' => '{LNG_LINE Notify}',
        ));
        $groups = $fieldset->add('groups', array(
            'comment' => '{LNG_Send notification messages When making a transaction}',
        ));
        // line_api_key
        $groups->add('text', array(
            'id' => 'line_api_key',
            'labelClass' => 'g-input icon-password',
            'itemClass' => 'width90',
            'label' => '{LNG_Access token} <a href="https://gcms.in.th/index.php?module=howto&id=367" class=icon-help></a>',
            'value' => isset(self::$cfg->line_api_key) ? self::$cfg->line_api_key : '',
        ));
        $groups->add('button', array(
            'id' => 'line_test',
            'itemClass' => 'width10',
            'labelClass' => 'g-input',
            'class' => 'magenta button wide center icon-chat',
            'label' => '&nbsp;',
            'value' => 'Test',
        ));
        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Style}',
        ));
        // bg_color
        $fieldset->add('color', array(
            'id' => 'bg_color',
            'labelClass' => 'g-input icon-color',
            'itemClass' => 'item',
            'label' => '{LNG_Background color}',
            'value' => self::$cfg->bg_color,
        ));
        // color
        $fieldset->add('color', array(
            'id' => 'color',
            'labelClass' => 'g-input icon-color',
            'itemClass' => 'item',
            'label' => '{LNG_Color}',
            'value' => self::$cfg->color,
        ));
        // bg_image
        if (is_file(ROOT_PATH.DATA_FOLDER.'images/bg_image.png')) {
            $img = WEB_URL.DATA_FOLDER.'images/bg_image.png?'.time();
        } else {
            $img = WEB_URL.'skin/img/blank.gif';
        }
        // bg_image
        $fieldset->add('file', array(
            'id' => 'file_bg_image',
            'labelClass' => 'g-input icon-image',
            'itemClass' => 'item',
            'label' => '{LNG_Background image}',
            'comment' => Language::replace('Upload :type files no larger than :size', array(':type' => 'jpg, jpeg, png', ':size' => \Kotchasan\Http\UploadedFile::getUploadSize())),
            'accept' => array('jpg', 'jpeg', 'png'),
            'dataPreview' => 'bgImage',
            'previewSrc' => $img,
        ));
        // delete_bg_image
        $fieldset->add('checkbox', array(
            'id' => 'delete_bg_image',
            'itemClass' => 'subitem',
            'label' => '{LNG_Remove} {LNG_Background image}',
            'value' => 1,
        ));
        // logo
        if (is_file(ROOT_PATH.DATA_FOLDER.'images/logo.png')) {
            $img = WEB_URL.DATA_FOLDER.'images/logo.png?'.time();
        } else {
            $img = WEB_URL.'skin/img/blank.gif';
        }
        // logo
        $fieldset->add('file', array(
            'id' => 'file_logo',
            'labelClass' => 'g-input icon-image',
            'itemClass' => 'item',
            'label' => '{LNG_Logo}',
            'comment' => Language::replace('Browse image uploaded, type :type size :width*:height pixel', array(':type' => 'jpg, jpeg, png', ':width' => 144, ':height' => 51)),
            'accept' => array('jpg', 'jpeg', 'png'),
            'dataPreview' => 'logoImage',
            'previewSrc' => $img,
        ));
        // delete_logo
        $fieldset->add('checkbox', array(
            'id' => 'delete_logo',
            'itemClass' => 'subitem',
            'label' => '{LNG_Remove} {LNG_Logo}',
            'value' => 1,
        ));
        $fieldset = $form->add('fieldset', array(
            'class' => 'submit',
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button save large icon-save',
            'value' => '{LNG_Save}',
        ));
        $form->script('initSystem();');
        // คืนค่า HTML
        return $form->render();
    }
}
