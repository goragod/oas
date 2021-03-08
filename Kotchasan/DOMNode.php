<?php
/**
 * @filesource Kotchasan/DOMNode.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

/**
 * คลาสสำหรับ Dom Node.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class DOMNode
{
    /**
     * รายการคุณสมบัติของโหนด.
     *
     * @var array
     */
    public $attributes = array();
    /**
     * รายการของโหนดที่อยู่ภายใน
     * <parentNode><childNode></childNode><childNode></childNode></parentNode>.
     *
     * @var array
     */
    public $childNodes;
    /**
     * ลำดับของโหนด ชั้นนอกสุดคือ 0.
     *
     * @var int
     */
    public $level;
    /**
     * โหนดถัดไป (ลำดับเดียวกัน) ถ้าเป็นโหนดสุดท้ายจะเป็น null
     * <node></node><nextSibling></nextSibling>.
     *
     * @var DOMNode
     */
    public $nextSibling;
    /**
     * @var mixed
     */
    public $nodeName;
    /**
     * ข้อความภายในโหนด ถ้าเป็น tag ค่านี้จะเป็น null
     * <node>nodeValue</node>.
     *
     * @var string|null
     */
    public $nodeValue;
    /**
     * โหนดแม่
     * <parentNode><childNode></childNode></parentNode>.
     *
     * @var DOMNode
     */
    public $parentNode;
    /**
     * โหนดก่อนหน้า (ลำดับเดียวกัน) ถ้าเป็นโหนดแรกจะเป็น null
     * <previousSibling></previousSibling><node></node>.
     *
     * @var DOMNode
     */
    public $previousSibling;

    /**
     * class constructor.
     *
     * @param string       $nodeName   ชื่อ tag ถ้าไม่มีชื่อ tag หมายถึงข้อความเปล่าๆ
     * @param DOMNode|null $parentNode โหนดแม่ ถ้าเป็นโหนดแรกคือ null
     * @param array        $attributes คุณสมบัติของโหนก (properties)
     * @param string|null  $nodeValue  ข้อความภายในโหนด ถ้าเป็น tag ค่านี้จะเป็น null
     */
    public function __construct($nodeName, $parentNode, $attributes, $nodeValue = null)
    {
        $this->nodeName = strtoupper($nodeName);
        $this->parentNode = $parentNode;
        $this->nodeValue = $nodeValue;
        foreach ($attributes as $key => $value) {
            $this->attributes[strtoupper($key)] = $value;
        }
        $this->childNodes = array();
    }

    /**
     * ตรวจสอบว่ามีโหนดลูกหรือไม่
     * คืนค่า true ถ้ามีโหนดลูก, false ถ้าไม่มี.
     *
     * @return bool
     */
    public function hasChildNodes()
    {
        return !empty($this->childNodes);
    }

    /**
     * ตรวจสอบว่ามีคลาสอยู่หรือไม่
     * คืนค่า true ถ้ามี.
     *
     * @param string $className ชื่อคลาสที่ต้องการตรวจสอบ
     *
     * @return bool
     */
    public function hasClass($className)
    {
        if (!empty($this->attributes['CLASS'])) {
            $className = strtoupper($className);
            foreach (explode(' ', strtoupper($this->attributes['CLASS'])) as $item) {
                if ($item == $className) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * ตรวจสอบว่าเป็น element แบบ Inline หรือไม่
     * คืนค่า true ถ้าเป็น Inline Elements หรือ false ถ้าเป็น Block-level Elements.
     *
     * @return bool
     */
    public function isInlineElement()
    {
        switch ($this->nodeName) {
            case 'B':
            case 'BIG':
            case 'I':
            case 'SMALL':
            case 'TT':
            case 'ABBR':
            case 'ACRONYM':
            case 'CITE':
            case 'CODE':
            case 'DFN':
            case 'EM':
            case 'STRONG':
            case 'SAMP':
            case 'TIME':
            case 'VAR':
            case 'A':
            case 'BDO':
            case 'BR':
            case 'IMG':
            case 'MAP':
            case 'OBJECT':
            case 'Q':
            case 'SCRIPT':
            case 'SPAN':
            case 'SUB':
            case 'BUTTON':
            case 'INPUT':
            case 'LABEL':
            case 'SELECT':
            case 'TEXTAREA':
                return true;
        }

        return false;
    }

    /**
     * คืนค่า ข้อความทั้งหมดภายในโหนด.
     *
     * @return string
     */
    public function nodeText()
    {
        $txt = '';
        foreach ($this->childNodes as $node) {
            if ($node->hasChildNodes()) {
                $txt .= $this->nodeText();
            } else {
                switch ($node->nodeName) {
                    case 'BR':
                        $txt .= "\n";
                        break;
                    case '':
                        $txt .= $node->nodeValue;
                        break;
                }
            }
        }

        return $this->unentities($txt);
    }

    /**
     * แปลงรหัส HTML เป็นข้อความ เช่น &lt; เป็น <.
     *
     * @param string $html
     *
     * @return string
     */
    public function unentities($html)
    {
        return str_replace(array('&nbsp;', '&amp;', '&lt;', '&gt;', '&#39;', '&quot;'), array(' ', '&', '<', '>', "'", '"'), $html);
    }
}
