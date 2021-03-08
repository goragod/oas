<?php
/**
 * @filesource Kotchasan/Form.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

/**
 * Form class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Form extends \Kotchasan\KBase
{
    /**
     * ตับแปรบอกว่ามีการใช้ form แบบ Ajax หรือไม่
     * ถ้าใช้งานต้องมีการเรียกใช้ GAjax ด้วย
     *
     * @var bool
     */
    public $ajax;
    /**
     * ตัวแปรบอกว่ามีการใช้งานฟอร์มร่วมกับ GForm หรือไม่
     * ถ้าใช้งานต้องมีการเรียกใช้ GAjax ด้วย.
     *
     * @var bool
     */
    public $gform;
    /**
     * Javascript
     *
     * @var string
     */
    public $javascript;
    /**
     * tag attributes
     *
     * @var array
     */
    private $attributes;
    /**
     * ชื่อ tag
     *
     * @var string
     */
    private $tag;

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function button($attributes = array())
    {
        $obj = new static();
        if (isset($attributes['tag']) && $attributes['tag'] == 'input') {
            $obj->tag = 'input';
        } else {
            $obj->tag = 'button';
        }
        unset($attributes['tag']);
        $attributes['type'] = 'button';
        $obj->attributes = $attributes;
        return $obj;
    }

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function checkbox($attributes = array())
    {
        $obj = new static();
        $obj->tag = 'input';
        $attributes['type'] = 'checkbox';
        $obj->attributes = $attributes;
        return $obj;
    }

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function color($attributes = array())
    {
        $obj = new static();
        $obj->tag = 'input';
        $attributes['type'] = 'text';
        $attributes['class'] = 'color';
        $obj->attributes = $attributes;
        return $obj;
    }

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function currency($attributes = array())
    {
        $obj = new static();
        $obj->tag = 'input';
        $attributes['type'] = 'text';
        $attributes['class'] = 'currency';
        $obj->attributes = $attributes;
        return $obj;
    }

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function date($attributes = array())
    {
        $obj = new static();
        $obj->tag = 'input';
        $attributes['type'] = 'date';
        $obj->attributes = $attributes;
        return $obj;
    }

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function email($attributes = array())
    {
        $obj = new static();
        $obj->tag = 'input';
        $attributes['type'] = 'email';
        $obj->attributes = $attributes;

        return $obj;
    }

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function file($attributes = array())
    {
        $obj = new static();
        $obj->tag = 'input';
        $attributes['type'] = 'file';
        $attributes['class'] = 'g-file';
        $obj->attributes = $attributes;
        return $obj;
    }

    /**
     * ฟังก์ชั่นสร้าง input ชนิด hidden สำหรับใช้ในฟอร์ม
     * ใช้ประโยชน์ในการสร้าง URL เพื่อส่งกลับไปยังหน้าเดิมหลังจาก submit แล้ว.
     *
     * @return array
     */
    public static function get2Input()
    {
        $hiddens = array();
        foreach (self::$request->getQueryParams() as $key => $value) {
            if ($value != '' && !preg_match('/.*?(username|password|token|time).*?/', $key) && preg_match('/^[_]+([^0-9]+)$/', $key, $match)) {
                $hiddens[$match[1]] = '<input type="hidden" name="_'.$match[1].'" value="'.htmlspecialchars($value).'">';
            }
        }
        foreach (self::$request->getParsedBody() as $key => $value) {
            if ($value != '' && !preg_match('/.*?(username|password|token|time).*?/', $key) && preg_match('/^[_]+([^0-9]+)$/', $key, $match)) {
                $hiddens[$match[1]] = '<input type="hidden" name="_'.$match[1].'" value="'.htmlspecialchars($value).'">';
            }
        }
        return $hiddens;
    }

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function hidden($attributes = array())
    {
        $obj = new static();
        $obj->tag = 'input';
        $attributes['type'] = 'hidden';
        $obj->attributes = $attributes;
        return $obj;
    }

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function number($attributes = array())
    {
        $obj = new static();
        $obj->tag = 'input';
        $attributes['type'] = 'number';
        $obj->attributes = $attributes;
        return $obj;
    }

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function integer($attributes = array())
    {
        $obj = new static();
        $obj->tag = 'input';
        $attributes['type'] = 'integer';
        $obj->attributes = $attributes;
        return $obj;
    }

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function password($attributes = array())
    {
        $obj = new static();
        $obj->tag = 'input';
        $attributes['type'] = 'password';
        $obj->attributes = $attributes;
        return $obj;
    }

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function radio($attributes = array())
    {
        $obj = new static();
        $obj->tag = 'input';
        $attributes['type'] = 'radio';
        $obj->attributes = $attributes;
        return $obj;
    }

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function range($attributes = array())
    {
        $obj = new static();
        $obj->tag = 'input';
        $attributes['type'] = 'range';
        $obj->attributes = $attributes;
        return $obj;
    }

    /**
     * ฟังก์ชั่นสร้าง Form Element.
     * id, name, type property ต่างๆของinput
     * label : ข้อความแสดงใน label ของ input
     * labelClass : class ของ label
     * comment : ถ้ากำหนดจะแสดงคำอธิบายของ input
     * ถ้าไม่กำหนดทั้ง label และ labelClass จะเป็นการสร้าง input อย่างเดียว
     * array('name1' => 'value1', 'name2' => 'value2', ....)
     *
     * @param string $tag
     * @param array  $param   property ของ Input
     * @param string $options ตัวเลือก options ของ select
     *
     * @return string
     */
    public function render()
    {
        $prop = array();
        $event = array();
        foreach ($this->attributes as $k => $v) {
            switch ($k) {
                case 'itemClass':
                case 'itemId':
                case 'labelClass':
                case 'label':
                case 'comment':
                case 'unit':
                case 'value':
                case 'dataPreview':
                case 'previewSrc':
                case 'accept':
                case 'options':
                case 'optgroup':
                case 'multiple':
                case 'validator':
                case 'result':
                case 'checked':
                case 'datalist':
                case 'button':
                    $$k = $v;
                    break;
                case 'title':
                    $prop['title'] = 'title="'.strip_tags($v).'"';
                    break;
                default:
                    if ($k == 'id') {
                        $id = $v;
                    } elseif (is_int($k)) {
                        $prop[$v] = $v;
                    } elseif ($v === true) {
                        $prop[$k] = $k;
                    } elseif ($v === false) {
                    } elseif (preg_match('/^on([a-z]+)/', $k, $match)) {
                        $event[$match[1]] = $v;
                    } elseif (!is_array($v)) {
                        $prop[$k] = $k.'="'.$v.'"';
                        $$k = $v;
                    }
                    break;
            }
        }
        if (isset($id)) {
            if (empty($name)) {
                $name = $id;
                $prop['name'] = 'name="'.$name.'"';
            }
            $id = trim(preg_replace('/[\[\]]+/', '_', $id), '_');
            $prop['id'] = 'id="'.$id.'"';
        }
        if (isset(Html::$form)) {
            if (isset($id) && Html::$form->gform) {
                if (isset($validator)) {
                    $js = array();
                    $js[] = '"'.$id.'"';
                    $js[] = '"'.$validator[0].'"';
                    $js[] = $validator[1];
                    if (isset($validator[2])) {
                        $js[] = '"'.$validator[2].'"';
                        $js[] = empty($validator[3]) || $validator[3] === null ? 'null' : '"'.$validator[3].'"';
                        $js[] = '"'.Html::$form->attributes['id'].'"';
                    }
                    $this->javascript[] = 'new GValidator('.implode(', ', $js).');';
                    unset($validator);
                }
                foreach ($event as $on => $func) {
                    $this->javascript[] = '$G("'.$id.'").addEvent("'.$on.'", '.$func.');';
                }
            } elseif (!Html::$form->gform) {
                foreach ($event as $on => $func) {
                    $prop['on'.$on] = 'on'.$on.'="'.$func.'()"';
                }
            }
        }
        if ($this->tag == 'select') {
            unset($prop['type']);
            if (isset($multiple)) {
                $value = isset($value) ? $value : array();
            } else {
                $value = isset($value) ? $value : null;
            }
            if (isset($options) && is_array($options)) {
                $datas = array();
                foreach ($options as $k => $v) {
                    if (is_array($value)) {
                        $sel = in_array($k, $value) ? ' selected' : '';
                    } else {
                        $sel = $value == $k ? ' selected' : '';
                    }
                    if (is_int($k)) {
                        $datas[] = '<option value='.$k.$sel.'>'.$v.'</option>';
                    } else {
                        $datas[] = '<option value="'.$k.'"'.$sel.'>'.$v.'</option>';
                    }
                }
                $value = implode('', $datas);
            } elseif (isset($optgroup) && is_array($optgroup)) {
                $datas = array();
                foreach ($optgroup as $group_label => $options) {
                    $datas[] = '<optgroup label="'.$group_label.'">';
                    foreach ($options as $k => $v) {
                        if (is_array($value)) {
                            $sel = in_array($k, $value) ? ' selected' : '';
                        } else {
                            $sel = $value == $k ? ' selected' : '';
                        }
                        $datas[] = '<option value="'.$k.'"'.$sel.'>'.$v.'</option>';
                    }
                    $datas[] = '</optgroup>';
                }
                $value = implode('', $datas);
            }
        } elseif (isset($value)) {
            if ($this->tag === 'textarea') {
                $value = str_replace(array('{', '}', '&amp;'), array('&#x007B;', '&#x007D;', '&'), htmlspecialchars($value));
            } elseif ($this->tag != 'button') {
                if (is_int($value)) {
                    $prop['value'] = 'value='.$value;
                } else {
                    $prop['value'] = 'value="'.str_replace('&amp;', '&', htmlspecialchars($value)).'"';
                }
            }
        }
        if (empty($prop['title'])) {
            if (!empty($comment)) {
                $prop['title'] = 'title="'.strip_tags($comment).'"';
            } elseif (!empty($label)) {
                $prop['title'] = 'title="'.strip_tags($label).'"';
            }
        }
        if (isset($dataPreview)) {
            $prop['data-preview'] = 'data-preview="'.$dataPreview.'"';
        }
        if (isset($result)) {
            $prop['data-result'] = 'data-result="result_'.$result.'"';
        }
        if (isset($accept) && is_array($accept)) {
            $prop['accept'] = 'accept="'.Mime::getAccept($accept).'"';
        }
        if (isset($multiple)) {
            $prop['multiple'] = 'multiple';
        }
        if (isset($checked) && isset($value) && $checked == $value) {
            $prop['checked'] = 'checked';
        }
        if (isset($datalist) && is_array($datalist)) {
            if (empty($prop['list'])) {
                $list = $id.'-datalist';
            } else {
                $list = $prop['list'];
            }
            $prop['list'] = 'list="'.$list.'"';
            $prop['autocomplete'] = 'autocomplete="off"';
        }
        $prop = implode(' ', $prop);
        if ($this->tag == 'input') {
            $element = '<'.$this->tag.' '.$prop.'>';
        } elseif (isset($value)) {
            $element = '<'.$this->tag.' '.$prop.'>'.$value.'</'.$this->tag.'>';
        } else {
            $element = '<'.$this->tag.' '.$prop.'></'.$this->tag.'>';
        }
        if (isset($datalist) && is_array($datalist)) {
            $element .= '<datalist id="'.$list.'">';
            foreach ($datalist as $k => $v) {
                if (is_int($k)) {
                    $element .= '<option value='.$k.'>'.$v.'</option>';
                } else {
                    $element .= '<option value="'.$k.'">'.$v.'</option>';
                }
            }
            $element .= '</datalist>';
        }
        if (empty($itemClass)) {
            $input = empty($comment) ? '' : '<div class="item"'.(empty($itemId) ? '' : ' id="'.$itemId.'"').'>';
            $input = empty($unit) ? '' : '<div class="wlabel">';
            if (empty($labelClass) && empty($label)) {
                $input .= $element;
            } elseif (isset($type) && ($type === 'checkbox' || $type === 'radio')) {
                if (!empty($button)) {
                    $label = '<span>'.$label.'</span>';
                }
                $input .= '<label'.(empty($labelClass) ? '' : ' class="'.$labelClass.'"').'>'.$element.$label.'</label>';
            } else {
                $input .= '<label'.(empty($labelClass) ? '' : ' class="'.$labelClass.'"').'>'.(empty($label) ? '' : $label.'&nbsp;').$element.'</label>';
            }
            if (!empty($unit)) {
                $input .= '<span class="label">'.$unit.'</span></div>';
            }
            if (!empty($comment)) {
                $input .= '<div class="comment"'.(empty($id) ? '' : ' id="result_'.$id.'"').'>'.$comment.'</div>';
            }
        } else {
            if (!empty($unit)) {
                $itemClass .= ' wlabel';
            }
            $input = '<div class="'.$itemClass.'"'.(empty($itemId) ? '' : ' id="'.$itemId.'"').'>';
            if (isset($type) && $type === 'checkbox') {
                $input .= '<label'.(empty($labelClass) ? '' : ' class="'.$labelClass.'"').'>'.$element.'&nbsp;'.(isset($label) ? $label : '').'</label>';
            } else {
                if (isset($dataPreview)) {
                    $input .= '<div class="file-preview" id="'.$dataPreview.'">';
                    if (isset($previewSrc)) {
                        if (preg_match_all('/\.([a-z0-9]+)(\?|$)/i', $previewSrc, $match)) {
                            $ext = strtoupper($match[1][0]);
                            if (in_array($ext, array('JPG', 'JPEG', 'GIF', 'PNG', 'BMP', 'WEBP', 'TIFF', 'ICO'))) {
                                $input .= '<a href="'.$previewSrc.'" target="preview" class="file-thumb" style="background-image:url('.$previewSrc.')"></a>';
                            } else {
                                $input .= '<a href="'.$previewSrc.'" target="preview" class="file-thumb">'.$ext.'</a>';
                            }
                        }
                    }
                    $input .= '</div>';
                }
                if (isset($label) && isset($id)) {
                    $input .= '<label for="'.$id.'">'.$label.'</label>';
                }
                $input .= '<span'.(empty($labelClass) ? '' : ' class="'.$labelClass.'"').'>'.$element.'</span>';
                if (!empty($unit)) {
                    $input .= '<span class=label>'.$unit.'</span>';
                }
            }
            if (!empty($comment)) {
                $input .= '<div class="comment"'.(empty($id) ? '' : ' id="result_'.$id.'"').'>'.$comment.'</div>';
            }
            $input .= '</div>';
        }
        return $input;
    }

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function reset($attributes = array())
    {
        $obj = new static();
        if (isset($attributes['tag']) && $attributes['tag'] == 'input') {
            $obj->tag = 'input';
        } else {
            $obj->tag = 'button';
        }
        unset($attributes['tag']);
        $attributes['type'] = 'reset';
        if (isset($attributes['name']) && $attributes['name'] == 'reset') {
            unset($attributes['name']);
        }
        if (isset($attributes['id']) && $attributes['id'] == 'reset') {
            unset($attributes['id']);
        }
        $obj->attributes = $attributes;
        return $obj;
    }

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function select($attributes = array())
    {
        $obj = new static();
        $obj->tag = 'select';
        $obj->attributes = $attributes;

        return $obj;
    }

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function submit($attributes = array())
    {
        $obj = new static();
        if (isset($attributes['tag']) && $attributes['tag'] == 'input') {
            $obj->tag = 'input';
        } else {
            $obj->tag = 'button';
        }
        unset($attributes['tag']);
        $attributes['type'] = 'submit';
        if (isset($attributes['name']) && $attributes['name'] == 'submit') {
            unset($attributes['name']);
        }
        if (isset($attributes['id']) && $attributes['id'] == 'submit') {
            unset($attributes['id']);
        }
        $obj->attributes = $attributes;
        return $obj;
    }

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function tel($attributes = array())
    {
        $obj = new static();
        $obj->tag = 'input';
        $attributes['type'] = 'tel';
        $obj->attributes = $attributes;
        return $obj;
    }

    /**
     * สร้าง input, select, textarea สำหรับใส่ลงในฟอร์ม
     * id, name, type property ต่างๆของinput
     * options สำหรับ select เท่านั้น เช่น array('value1'=> 'name1', 'value2'=>'name2', ...)
     * label ข้อความแสดงใน label ของ input
     * labelClass class ของ label
     * comment ถ้ากำหนดจะแสดงคำอธิบายของ input
     * ถ้าไม่กำหนดทั้ง label และ labelClass จะเป็นการสร้าง input อย่างเดียว
     *
     * @param array $attributes property ของ Input
     */
    public static function text($attributes = array())
    {
        $obj = new static();
        $obj->tag = 'input';
        $attributes['type'] = 'text';
        $obj->attributes = $attributes;
        return $obj;
    }

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function textarea($attributes = array())
    {
        $obj = new static();
        $obj->tag = 'textarea';
        $obj->attributes = $attributes;
        return $obj;
    }

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function time($attributes = array())
    {
        $obj = new static();
        $obj->tag = 'input';
        $attributes['type'] = 'time';
        $obj->attributes = $attributes;
        return $obj;
    }

    /**
     * @param array $attributes
     *
     * @return \static
     */
    public static function url($attributes = array())
    {
        $obj = new static();
        $obj->tag = 'input';
        $attributes['type'] = 'url';
        $obj->attributes = $attributes;
        return $obj;
    }
}
