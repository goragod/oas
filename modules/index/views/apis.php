<?php
/**
 * @filesource modules/index/views/apis.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Apis;

use Kotchasan\Html;

/**
 * module=apis
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ฟอร์มตั้งค่า api
     *
     * @param object $config
     *
     * @return string
     */
    public function render($config)
    {
        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/index/model/apis/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true,
        ));
        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_API settings}',
        ));
        // api_url
        $fieldset->add('text', array(
            'id' => 'api_url',
            'labelClass' => 'g-input icon-world',
            'itemClass' => 'item',
            'label' => '{LNG_Api Url}',
            'value' => isset($config->api_url) ? $config->api_url : WEB_URL.'api.php',
        ));
        // api_token
        $fieldset->add('text', array(
            'id' => 'api_token',
            'labelClass' => 'g-input icon-password',
            'itemClass' => 'item',
            'label' => '{LNG_Token}',
            'value' => isset($config->api_token) ? $config->api_token : \Kotchasan\Password::uniqid(40),
        ));
        // api_secret
        $fieldset->add('text', array(
            'id' => 'api_secret',
            'labelClass' => 'g-input icon-password',
            'itemClass' => 'item',
            'label' => '{LNG_Secret}',
            'value' => isset($config->api_secret) ? $config->api_secret : \Kotchasan\Password::uniqid(),
        ));
        // api_ips
        $fieldset->add('textarea', array(
            'id' => 'api_ips',
            'labelClass' => 'g-input icon-ip',
            'itemClass' => 'item',
            'label' => '{LNG_White list}',
            'placeholder' => '{LNG_0.0.0.0 mean all IP addresses}',
            'comment' => '{LNG_List of IPs that allow connection 1 line per 1 IP}',
            'rows' => 5,
            'value' => !empty($config->api_ips) && is_array($config->api_ips) ? implode("\n", $config->api_ips) : '',
        ));
        $fieldset = $form->add('fieldset', array(
            'class' => 'submit',
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button save large icon-save',
            'value' => '{LNG_Save}',
        ));
        // คืนค่า HTML
        return $form->render();
    }
}
