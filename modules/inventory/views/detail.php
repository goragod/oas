<?php
/**
 * @filesource modules/inventory/views/detail.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Inventory\Detail;

use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=inventory-write&tab=detail
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ฟอร์มเพิ่ม/แก้ไข พัสดุ
     *
     * @param Request $request
     * @param object $product
     *
     * @return string
     */
    public function render(Request $request, $product)
    {
        // อ่าน detail จาก DB
        $detail = \Inventory\Detail\Model::get($product->id);
        // form
        $form = Html::create('form', array(
            'id' => 'product',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/inventory/model/detail/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true,
        ));
        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Other details} '.$product->topic,
        ));
        // description
        $fieldset->add('text', array(
            'id' => 'write_description',
            'itemClass' => 'item',
            'labelClass' => 'g-input icon-edit',
            'label' => '{LNG_Description}',
            'value' => $detail->description,
        ));
        // detail
        $fieldset->add('textarea', array(
            'id' => 'write_detail',
            'itemClass' => 'item',
            'labelClass' => 'g-input icon-file',
            'label' => '{LNG_Detail}',
            'rows' => 5,
            'value' => $detail->detail,
        ));
        if (is_file(ROOT_PATH.DATA_FOLDER.'inventory/'.$product->id.'.jpg')) {
            $image = WEB_URL.DATA_FOLDER.'inventory/'.$product->id.'.jpg';
            $placeholder = $image;
        } elseif (!empty($detail->image)) {
            $image = $detail->image;
            $placeholder = $image;
        } else {
            $image = WEB_URL.'skin/img/blank.gif';
            $placeholder = '';
        }
        // image
        $fieldset->add('file', array(
            'id' => 'write_image',
            'itemClass' => 'item',
            'labelClass' => 'g-input icon-image',
            'label' => '{LNG_Image}',
            'comment' => Language::replace('Browse image uploaded, type :type', array(':type' => 'jpg, jpeg, png')).' ({LNG_resized automatically})',
            'accept' => array('jpg', 'jpeg', 'png'),
            'dataPreview' => 'logoImage',
            'previewSrc' => $image,
            'placeholder' => $placeholder,
        ));
        $fieldset = $form->add('fieldset', array(
            'class' => 'submit',
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button save large icon-save',
            'value' => '{LNG_Save}',
        ));
        // id
        $fieldset->add('hidden', array(
            'id' => 'write_id',
            'value' => $product->id,
        ));
        // คืนค่า HTML
        return $form->render();
    }
}
