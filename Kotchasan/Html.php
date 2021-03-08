<?php
/**
 * @filesource Kotchasan/Html.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

/**
 * html
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Html extends \Kotchasan\KBase
{
    /**
     * attrribute ของ tag
     *
     * @var array
     */
    public $attributes;
    /**
     * ตัวแปรเก็บ form object
     *
     * @var \static
     */
    public static $form;
    /**
     * Javascript
     *
     * @var array
     */
    protected $javascript;
    /**
     * แอเรย์ของข้อมูลภายใน tag.
     *
     * @var array
     */
    protected $rows;
    /**
     * ชื่อ tag
     *
     * @var string
     */
    protected $tag;

    /**
     * class Constructor
     */
    public function __construct($tag, $attributes = array())
    {
        $this->tag = strtolower($tag);
        $this->attributes = $attributes;
        $this->rows = array();
        $this->javascript = array();
    }

    /**
     * แทรก tag ลงใน element เหมือนการใช้งาน innerHTML.
     *
     * @param string $tag
     * @param array  $attributes
     *
     * @return \static
     */
    public function add($tag, $attributes = array())
    {
        $tag = strtolower($tag);
        if ($tag == 'groups' || $tag == 'groups-table') {
            $obj = $this->addGroups($tag, $attributes);
        } elseif ($tag == 'inputgroups') {
            $obj = $this->addInputGroups($attributes);
        } elseif ($tag == 'radiogroups' || $tag == 'checkboxgroups') {
            $obj = $this->addRadioOrCheckbox($tag, $attributes);
        } elseif ($tag == 'menubutton') {
            $obj = $this->addMenuButton($attributes);
        } elseif ($tag == 'ckeditor') {
            $obj = $this->addCKEditor($tag, $attributes);
        } elseif ($tag == 'row') {
            $obj = new static('div', array(
                'class' => 'row',
            ));
            $this->rows[] = $obj;
        } elseif ($tag == 'rowgroup') {
            $obj = new static('div', array(
                'class' => 'rowgroup',
            ));
            $this->rows[] = $obj;
        } else {
            $obj = self::create($tag, $attributes);
            $this->rows[] = $obj;
        }
        return $obj;
    }

    /**
     * แทรก HTML ลงใน element ที่ตำแหน่งท้ายสุด.
     *
     * @param string $html
     */
    public function appendChild($html)
    {
        $this->rows[] = $html;
    }

    /**
     * creat new Element.
     *
     * @param string $tag
     * @param array  $attributes
     *
     * @return \static
     */
    public static function create($tag, $attributes = array())
    {
        if (method_exists(__CLASS__, $tag)) {
            $obj = self::$tag($attributes);
        } elseif (method_exists('Kotchasan\Form', $tag)) {
            $obj = \Kotchasan\Form::$tag($attributes);
        } else {
            $obj = new static($tag, $attributes);
        }

        return $obj;
    }

    /**
     * create Fieldset element.
     *
     * @param array $attributes
     *
     * @return \static
     */
    public static function fieldset($attributes = array())
    {
        $prop = array();
        $span = array();
        foreach ($attributes as $key => $value) {
            if ($key == 'title') {
                $span['innerHTML'] = $value;
            } elseif ($key == 'titleClass') {
                $span['class'] = $value;
            } else {
                $prop[$key] = $value;
            }
        }
        $obj = new static('fieldset', $prop);
        if (isset($span['innerHTML'])) {
            $legend = $obj->add('legend');
            $legend->add('span', $span);
        }

        return $obj;
    }

    /**
     * create Form element.
     *
     * @param array $attributes
     *
     * @return \static
     */
    public static function form($attributes = array())
    {
        $ajax = false;
        $prop = array('method' => 'post');
        $gform = true;
        $token = false;
        foreach ($attributes as $key => $value) {
            if ($key === 'ajax' || $key === 'action' || $key === 'onsubmit' || $key === 'onbeforesubmit' ||
                $key === 'elements' || $key === 'script' || $key === 'gform' || $key === 'token') {
                $$key = $value;
            } else {
                $prop[$key] = $value;
            }
        }
        if (isset($prop['id']) && $gform) {
            $script = 'new GForm("'.$prop['id'].'"';
            if (isset($action)) {
                if ($ajax) {
                    $script .= ', "'.$action.'"';
                    if (isset($onbeforesubmit)) {
                        $script .= ',null ,false , function(){return '.$onbeforesubmit.'}';
                    }
                } else {
                    $prop['action'] = $action;
                }
            }
            $script .= ')';
            if (isset($onsubmit)) {
                $script .= '.onsubmit('.$onsubmit.')';
            }
            $script .= ';';
            $form_inputs = Form::get2Input();
        } else {
            if (isset($action)) {
                $prop['action'] = $action;
            }
            if (isset($onsubmit)) {
                $prop['onsubmit'] = $onsubmit.'()';
            }
            if (isset($onbeforesubmit)) {
                $prop['onbeforesubmit'] = $onbeforesubmit.'()';
            }
        }
        self::$form = new static('form', $prop);
        self::$form->ajax = $ajax;
        self::$form->gform = $gform;
        if (!empty($form_inputs)) {
            self::$form->rows = $form_inputs;
        }
        if ($token) {
            self::$form->rows[] = '<input type="hidden" name="token" id="token" value="'.self::$request->createToken().'">';
        }
        if (isset($script)) {
            self::$form->javascript[] = $script;
        }

        return self::$form;
    }

    /**
     * สร้าง element และแทรก HTML ลงใน tag ให้ผลลัพท์เป็น string เลย.
     *
     * @param string $html
     *
     * @return string
     */
    public function innerHtml($html)
    {
        return '<'.$this->tag.$this->renderAttributes().'>'.$html.'</'.$this->tag.'>';
    }

    /**
     * สร้างโค้ด HTML.
     *
     * @return string
     */
    public function render()
    {
        $result = '<'.$this->tag.$this->renderAttributes().'>'.(isset($this->attributes['innerHTML']) ? $this->attributes['innerHTML'] : '');
        foreach ($this->rows as $row) {
            if (is_string($row)) {
                $result .= $row;
            } else {
                $result .= $row->render();
                if (!empty($row->javascript)) {
                    foreach ($row->javascript as $script) {
                        self::$form->javascript[] = $script;
                    }
                }
            }
        }
        $result .= '</'.$this->tag.'>';
        if ($this->tag == 'form' && !empty(self::$form->javascript)) {
            $result .= "\n".preg_replace('/^[\s\t]+/m', '', "<script>\n".implode("\n", self::$form->javascript)."\n</script>");
            self::$form = null;
        } elseif (!empty($this->javascript)) {
            $result .= "\n".preg_replace('/^[\s\t]+/m', '', "<script>\n".implode("\n", $this->javascript)."\n</script>");
        }

        return $result;
    }

    /**
     * กำหนด Javascript.
     *
     * @param string $script
     */
    public function script($script)
    {
        if (isset(self::$form)) {
            self::$form->javascript[] = $script;
        } else {
            $this->javascript[] = $script;
        }
    }

    /**
     * สร้าง Attributes ของ tag
     *
     * @return string
     */
    protected function renderAttributes()
    {
        $attr = array();
        foreach ($this->attributes as $key => $value) {
            if ($key != 'innerHTML') {
                if (is_int($key)) {
                    $attr[] = $value;
                } else {
                    $attr[] = $key.'="'.$value.'"';
                }
            }
        }

        return count($attr) == 0 ? '' : ' '.implode(' ', $attr);
    }

    /**
     * @param  $tag
     * @param  $attributes
     *
     * @return mixed
     */
    private function addCKEditor($tag, $attributes)
    {
        if (isset($attributes[$tag])) {
            $tag = $attributes[$tag];
            unset($attributes[$tag]);
        } else {
            $tag = 'textarea';
        }
        if (class_exists('Kotchasan\CKEditor')) {
            $obj = new \Kotchasan\CKEditor($tag, $attributes);
        } else {
            $obj = self::create($tag, $attributes);
        }
        $this->rows[] = $obj;

        return $obj;
    }

    /**
     * @param  $tag
     * @param  $attributes
     *
     * @return \static
     */
    private function addGroups($tag, $attributes)
    {
        $prop = array('class' => isset($attributes['class']) ? $attributes['class'] : 'item');
        if (isset($attributes['id'])) {
            $prop['id'] = $attributes['id'];
        }
        if (isset($attributes['label'])) {
            if (isset($attributes['for'])) {
                $item = new static('div', $prop);
                $item->add('label', array(
                    'innerHTML' => $attributes['label'],
                    'for' => $attributes['for'],
                ));
            } else {
                $prop['title'] = strip_tags($attributes['label']);
                $item = self::fieldset($prop);
            }
        } else {
            $item = new static('div', $prop);
        }
        $this->rows[] = $item;
        $obj = $item->add('div', array('class' => 'input-'.$tag));
        $rows = array();
        $comment = array();
        if (empty($attributes['id'])) {
            $id = '';
            $name = '';
        } else {
            $id = ' id='.$attributes['id'];
            $name = ' name='.$attributes['id'].'[]';
            $comment['id'] = 'result_'.$attributes['id'];
        }
        foreach ($attributes as $key => $value) {
            if ($key == 'checkbox' || $key == 'radio') {
                foreach ($value as $v => $text) {
                    $chk = isset($attributes['value']) && in_array($v, $attributes['value']) ? ' checked' : '';
                    $rows[] = '<label>'.$text.'&nbsp;<input type='.$key.$id.$name.$chk.' value="'.$v.'"></label>';
                    $id = '';
                }
            }
        }
        if (!empty($rows)) {
            $obj->appendChild(implode('&nbsp; ', $rows));
        }
        if (isset($attributes['comment'])) {
            if (isset($attributes['commentId'])) {
                $comment['id'] = $attributes['commentId'];
            }
            $comment['class'] = 'comment';
            $comment['innerHTML'] = $value;
            $item->add('div', $comment);
        }

        return $obj;
    }

    /**
     * @param  $attributes
     *
     * @return \static
     */
    private function addInputGroups($attributes)
    {
        if (!empty($attributes['disabled'])) {
            $attributes['disabled'] = 'disabled';
        } else {
            unset($attributes['disabled']);
        }
        if (!empty($attributes['readonly'])) {
            $attributes['readonly'] = 'readonly';
        } else {
            unset($attributes['readonly']);
        }
        $prop = array('class' => empty($attributes['itemClass']) ? 'item' : $attributes['itemClass']);
        if (isset($attributes['itemId'])) {
            $prop['id'] = $attributes['itemId'];
        }
        $obj = new static('div', $prop);
        $this->rows[] = $obj;
        if (isset($attributes['id'])) {
            $id = $attributes['id'];
        } else {
            $id = \Kotchasan\Password::uniqid();
        }
        $c = array('inputgroups');
        if (isset($attributes['labelClass'])) {
            $c[] = $attributes['labelClass'];
        }
        if (isset($attributes['label'])) {
            $obj->add('label', array(
                'innerHTML' => $attributes['label'],
                'for' => $id,
            ));
        }
        $li = '';
        if (isset($attributes['value']) && is_array($attributes['value'])) {
            if (isset($attributes['options'])) {
                foreach ($attributes['value'] as $value) {
                    if (isset($attributes['options'][$value])) {
                        $li .= '<li><span>'.$attributes['options'][$value].'</span><button type="button">x</button><input type="hidden" name="'.$id.'[]" value="'.$value.'"></li>';
                    }
                }
            } else {
                foreach ($attributes['value'] as $value) {
                    $li .= '<li><span>'.$value.'</span><button type="button">x</button><input type="hidden" name="'.$id.'[]" value="'.$value.'"></li>';
                }
            }
        }
        foreach ($attributes as $key => $value) {
            if ($key == 'validator') {
                $js = array();
                $js[] = '"'.$id.'"';
                $js[] = '"'.$value[0].'"';
                $js[] = $value[1];
                if (isset($value[2])) {
                    $js[] = '"'.$value[2].'"';
                    $js[] = empty($value[3]) || $value[3] === null ? 'null' : '"'.$value[3].'"';
                    $js[] = '"'.self::$form->attributes['id'].'"';
                }
                self::$form->javascript[] = 'new GValidator('.implode(', ', $js).');';
            } elseif ($key == 'options') {
                $options = $value;
                $datalist = $id.'_'.\Kotchasan\Password::uniqid();
                $prop['list'] = 'list="'.$datalist.'"';
            } elseif ($key == 'comment') {
                $comment = $value;
            } elseif (!in_array($key, array('id', 'type', 'itemId', 'itemClass', 'labelClass', 'label', 'value'))) {
                $prop[$key] = $key.'="'.$value.'"';
            }
        }
        $prop['id'] = 'id="'.$id.'"';
        $prop['type'] = 'type="text"';
        $prop['class'] = 'class="inputgroup"';
        $li .= '<li><input '.implode(' ', $prop).'>';
        if (isset($options) && is_array($options)) {
            $li .= '<datalist id="'.$datalist.'">';
            foreach ($options as $k => $v) {
                $li .= '<option value="'.$k.'">'.$v.'</option>';
            }
            $li .= '</datalist>';
        }
        $li .= '</li>';
        $obj->add('ul', array(
            'class' => implode(' ', $c),
            'innerHTML' => $li,
        ));
        if (isset($comment)) {
            $obj->add('div', array(
                'id' => 'result_'.$id,
                'class' => 'comment',
                'innerHTML' => $comment,
            ));
        }

        return $obj;
    }

    /**
     * @param  $attributes
     *
     * @return \static
     */
    private function addMenuButton($attributes)
    {
        $prop = array('class' => empty($attributes['itemClass']) ? 'item' : $attributes['itemClass']);
        if (isset($attributes['itemId'])) {
            $prop['id'] = $attributes['itemId'];
        }
        $obj = new static('div', $prop);
        $this->rows[] = $obj;
        if (isset($attributes['label'])) {
            $obj->add('label', array(
                'innerHTML' => $attributes['label'],
            ));
        }
        $div = $obj->add('div', array(
            'class' => 'g-input',
        ));
        $li = '<ul>';
        if (isset($attributes['submenus']) && is_array($attributes['submenus'])) {
            foreach ($attributes['submenus'] as $item) {
                $prop = array();
                $text = '';
                foreach ($item as $key => $value) {
                    if ($key == 'text') {
                        $text = $value;
                    } else {
                        $prop[$key] = $key.'="'.$value.'"';
                    }
                }
                $li .= '<li><a '.implode(' ', $prop).'>'.$text.'</a></li>';
            }
        }
        $li .= '</ul>';
        $prop = array(
            'class' => isset($attributes['class']) ? $attributes['class'].' menubutton' : 'menubutton',
            'tabindex' => 0,
        );
        if (isset($attributes['text'])) {
            $prop['innerHTML'] = $attributes['text'].$li;
        } else {
            $prop['innerHTML'] = $li;
        }
        $div->add('div', $prop);
        return $obj;
    }

    /**
     * @param  $tag
     * @param  $attributes
     *
     * @return \static
     */
    private function addRadioOrCheckbox($tag, $attributes)
    {
        $prop = array('class' => empty($attributes['itemClass']) ? 'item' : $attributes['itemClass']);
        if (!empty($attributes['itemId'])) {
            $prop['id'] = $attributes['itemId'];
        }
        $obj = new static('div', $prop);
        $this->rows[] = $obj;
        if (isset($attributes['name'])) {
            $name = $attributes['name'];
        } elseif (isset($attributes['id'])) {
            $name = $tag == 'checkboxgroups' ? $attributes['id'].'[]' : $attributes['id'];
        } else {
            $name = false;
        }
        $c = array($tag);
        if (isset($attributes['labelClass'])) {
            $c[] = $attributes['labelClass'];
        }
        if (isset($attributes['label']) && isset($attributes['id'])) {
            $obj->add('label', array(
                'innerHTML' => $attributes['label'],
                'for' => $attributes['id'],
            ));
        }
        if (isset($attributes['button']) && $attributes['button'] === true) {
            $c[] = 'groupsbutton';
        }
        $prop = array(
            'class' => implode(' ', $c),
        );
        if (isset($attributes['id'])) {
            $prop['id'] = $attributes['id'];
        }
        $div = $obj->add('div', $prop);
        if (!empty($attributes['multiline'])) {
            $c = array('multiline');
            if (!empty($attributes['scroll'])) {
                $c[] = 'hscroll';
            }
            $div = $div->add('div', array(
                'class' => implode(' ', $c),
            ));
        }
        if (!empty($attributes['options']) && is_array($attributes['options'])) {
            foreach ($attributes['options'] as $v => $label) {
                $item = array(
                    'label' => $label,
                    'value' => $v,
                );
                if (isset($attributes['value'])) {
                    if (is_array($attributes['value']) && in_array($v, $attributes['value'])) {
                        $item['checked'] = $v;
                    } elseif ($v == $attributes['value']) {
                        $item['checked'] = $v;
                    }
                }
                if ($name) {
                    $item['name'] = $name;
                }
                if (isset($attributes['id'])) {
                    if (isset($attributes['button']) && $attributes['button'] === true) {
                        $item['button'] = $attributes['button'];
                        $item['class'] = (empty($attributes['class']) ? '' : $attributes['class'].' ').str_replace('groups', 'button', $tag);
                    } elseif (isset($attributes['class'])) {
                        $item['class'] = $attributes['class'];
                    }
                }
                if (isset($attributes['comment'])) {
                    $item['title'] = strip_tags($attributes['comment']);
                }
                if (!empty($attributes['disabled'])) {
                    $item['disabled'] = true;
                }
                $div->add($tag == 'radiogroups' ? 'radio' : 'checkbox', $item);
            }
        }
        if (isset($attributes['id']) && !empty($attributes['comment'])) {
            $obj->add('div', array(
                'id' => 'result_'.$attributes['id'],
                'class' => 'comment',
                'innerHTML' => $attributes['comment'],
            ));
        }
        return $obj;
    }
}
