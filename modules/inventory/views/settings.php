<?php
/**
 * @filesource modules/inventory/views/settings.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Inventory\Settings;

use Kotchasan\Html;
use Kotchasan\Language;

/**
 * module=inventory-settings
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ตั้งค่าโมดูล
     *
     * @return string
     */
    public function render()
    {
        // form
        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/inventory/model/settings/submit',
            'onsubmit' => 'doFormSubmit',
            'token' => true,
            'ajax' => true,
        ));
        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Details of} {LNG_Accounting}',
        ));
        // authorized
        $fieldset->add('text', array(
            'id' => 'company_authorized',
            'labelClass' => 'g-input icon-user',
            'itemClass' => 'item',
            'label' => '{LNG_Authorized}',
            'comment' => '{LNG_Authorized signatory receipt}',
            'placeholder' => '{LNG_Name}',
            'maxlength' => 150,
            'value' => isset(self::$cfg->authorized) ? self::$cfg->authorized : '',
        ));
        // email
        $fieldset->add('text', array(
            'id' => 'company_email',
            'labelClass' => 'g-input icon-email',
            'itemClass' => 'item',
            'label' => '{LNG_Email}',
            'comment' => '{LNG_The contact email Used to send documents by email}',
            'maxlength' => 50,
            'value' => isset(self::$cfg->email) ? self::$cfg->email : '',
        ));
        // product_no
        $fieldset->add('text', array(
            'id' => 'product_no',
            'labelClass' => 'g-input icon-number',
            'itemClass' => 'item',
            'label' => '{LNG_Product code}',
            'comment' => '{LNG_number format such as %04d (%04d means the number on 4 digits, up to 11 digits)}',
            'placeholder' => 'P%04d',
            'value' => isset(self::$cfg->product_no) ? self::$cfg->product_no : 'P%04d',
        ));
        foreach (Language::get('ORDER_STATUS') as $s => $label) {
            // Order Number
            $fieldset->add('text', array(
                'id' => $s.'_NO',
                'labelClass' => 'g-input icon-number',
                'itemClass' => 'item',
                'label' => $label,
                'comment' => '{LNG_number format such as %04d (%04d means the number on 4 digits, up to 11 digits)}',
                'placeholder' => $s.'%Y%M%D-%04d',
                'value' => isset(self::$cfg->{$s.'_NO'}) ? self::$cfg->{$s.'_NO'} : $s.'%Y%M%D-%04d',
            ));
        }
        // currency_unit
        $fieldset->add('select', array(
            'id' => 'currency_unit',
            'labelClass' => 'g-input icon-currency',
            'itemClass' => 'item',
            'label' => '{LNG_Currency unit}',
            'comment' => '{LNG_Currency for goods and services}',
            'options' => Language::get('CURRENCY_UNITS'),
            'value' => isset(self::$cfg->currency_unit) ? self::$cfg->currency_unit : 'THB',
        ));
        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Finance}',
        ));
        $groups = $fieldset->add('groups');
        // bank
        $groups->add('select', array(
            'id' => 'bank',
            'itemClass' => 'width33',
            'labelClass' => 'g-input icon-office',
            'label' => '{LNG_Bank}',
            'options' => array('' => '{LNG_Please select}') + Language::get('BANKS'),
            'value' => isset(self::$cfg->bank) ? self::$cfg->bank : '',
        ));
        // bank_name
        $groups->add('text', array(
            'id' => 'bank_name',
            'itemClass' => 'width33',
            'label' => '{LNG_Account name}',
            'labelClass' => 'g-input icon-customer',
            'maxlength' => 100,
            'value' => isset(self::$cfg->bank_name) ? self::$cfg->bank_name : '',
        ));
        // bank_no
        $groups->add('text', array(
            'id' => 'bank_no',
            'itemClass' => 'width33',
            'label' => '{LNG_Account number}',
            'labelClass' => 'g-input icon-number',
            'maxlength' => 20,
            'value' => isset(self::$cfg->bank_no) ? self::$cfg->bank_no : '',
        ));
        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Size of} {LNG_Image}',
        ));
        // inventory_w
        $fieldset->add('text', array(
            'id' => 'inventory_w',
            'labelClass' => 'g-input icon-width',
            'itemClass' => 'item',
            'label' => '{LNG_Width}',
            'comment' => '{LNG_Image size is in pixels}',
            'value' => isset(self::$cfg->inventory_w) ? self::$cfg->inventory_w : 500,
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
