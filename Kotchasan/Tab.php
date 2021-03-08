<?php
/**
 * @filesource Kotchasan/Tab.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

/**
 * Tab.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Tab
{
    /**
     * @var array
     */
    private $datas;
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $select;
    /**
     * @var array
     */
    private $urls;

    /**
     * Construct.
     *
     * @param string $id    ID ของ Tab ห้ามซ้ำกับอันอื่น
     * @param string $url   URL ของหน้านี้ ใช้เป็นค่าเริ่มต้นของเมนู
     * @param array  $items รายการเริ่มต้น
     */
    public function __construct($id, $url, $items = array())
    {
        $this->id = $id;
        $this->urls = explode('?', $url);
        if (count($this->urls) == 1) {
            $this->urls[1] = '';
        } else {
            $this->urls[1] = str_replace(array('&', '&amp;amp;'), '&amp;', $this->urls[1]);
        }
        $this->datas = empty($items) ? array() : $items;
    }

    /**
     * เพิ่มรายการ Tab.
     *
     * @param string $id     ID ของแท็บ ใช้สำหรับเลือกแท็บ
     * @param string $title  ข้อความในเมนูแท็บ
     * @param string $url    URL เมื่อคลิกแท็บ ถ้าไม่กำหนดจะใช้ URL ตอนสร้างแท็บ
     * @param string $target ค่าเริ่มต้น null คือไม่มี target
     */
    public function add($id, $title, $url = '', $target = null)
    {
        $this->datas[] = array(
            'title' => $title,
            'url' => $url,
            'id' => $id,
            'target' => $target,
        );
    }

    /**
     * คืนค่าชื่อแท็บที่ถูกเลือก
     *
     * @return string
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * สร้างโค้ด HTML.
     *
     * @param string $select ID ของ Tab ที่เลือก ถ้าเป็นค่าว่างจะเลือกรายการแรกสุด
     *
     * @return string
     */
    public function render($select = '')
    {
        $html = '<div class="inline"><div class="writetab"><ul id="'.$this->id.'">';
        foreach ($this->datas as $i => $item) {
            $prop = array();
            if (empty($item['url'])) {
                if (isset($item['id'])) {
                    if ($this->urls[1] == '') {
                        $prop[] = 'href="'.$this->urls[0].'?tab='.$item['id'].'"';
                    } else {
                        $prop[] = 'href="'.$this->urls[0].'?'.$this->urls[1].'&amp;tab='.$item['id'].'"';
                    }
                    $prop[] = 'id="tab_'.$item['id'].'"';
                }
            } else {
                $prop[] = 'href="'.$item['url'].'"';
            }
            if (!empty($item['target'])) {
                $prop[] = 'target="'.$item['target'].'"';
            }
            if ($select == $item['id'] || ($i == 0 && $select == '')) {
                $prop[] = 'class="select"';
                $this->select = $item['id'];
            }
            $html .= '<li><a '.implode(' ', $prop).'>'.$item['title'].'</a></li>';
        }

        return $html.'</ul></div></div>';
    }
}
