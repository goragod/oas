<?php
/**
 * @filesource Kotchasan/DataTable.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

use Kotchasan\Http\Uri;

/**
 * คลาสสำหรับจัดการแสดงผลข้อมูลจาก Model ในรูปแบบตาราง
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class DataTable extends \Kotchasan\KBase
{
    /**
     * id ของตาราง
     *
     * @var string
     */
    private $id;
    /**
     * ชื่อ Model ที่ต้องการเรียกข้อมูล
     *
     * @var \Kotchasan\Database\QueryBuilder
     */
    private $model;
    /**
     * ข้อมูลทั้งหมดของตารางรูปแบบแอเรย์
     * หากไม่ได้ใช้ตารางเชื่อมต่อกับ Model ให้กำหนดข้อมูลทั้งหมดที่นี่.
     *
     * @var array
     */
    private $datas;
    /**
     * database cache.
     *
     * @var bool
     */
    private $cache = false;
    /**
     * รายชื่อฟิลด์ที่จะ query.
     *
     * @var array
     */
    private $fields = array();
    /**
     * คอลัมน์ของ checkbox
     * -1 ไม่แสดง checkbox.
     *
     * @var int
     */
    private $checkCol = -1;
    /**
     * กำหนดการแสดงผล checkbox
     * ถ้าเป็น true จะซ่อน checkbox เสมอ.
     *
     * @var bool
     */
    public $hideCheckbox = false;
    /**
     * แสดงตารางกว้าง 100%.
     *
     * @var bool
     */
    private $fullWidth = true;
    /**
     * แสดงเส้นกรอบ.
     *
     * @var bool
     */
    private $border = false;
    /**
     * แสดงปุ่ม สำหรับเพิ่มและลบแถว.
     *
     * @var bool
     */
    private $pmButton = false;
    /**
     * แสดงตารางแบบ responsive.
     *
     * @var bool
     */
    private $responsive = false;
    /**
     * แสดงส่วนหัวของตาราง.
     *
     * @var bool
     */
    private $showCaption = true;
    /**
     * URL สำหรับรับค่าจาก action ต่างๆ เช่นการลบ
     * เช่น index/[controller|model]/className/method.php
     *
     * @var string
     */
    private $action;
    /**
     * ถ้ากำหนดรายการนี้จะแสดง checkbox และ ปุ่ม action
     * array('delete' => Language::get('Delete'), 'published' => Language::get('Published'))
     * หมายถึงแสดง select สำหรับ ลบ และ เผยแพร่.
     *
     * @var array
     */
    public $actions = array();
    /**
     * ชื่อฟังก์ชั่น Javascript เรียกหลังจากทำการส่งค่าจาก action ไปประมวลผลแล้ว
     * เช่น doFormSubmit.
     *
     * @var string
     */
    private $actionCallback;
    /**
     * ชื่อฟังก์ชั่น Javascript เรียกหลังจากคลิก action
     * เช่น confirmAction(text, action, id).
     *
     * @var string
     */
    private $actionConfirm;
    /**
     * method สำหรับจัดการข้อมูลแต่ละแถวก่อนการแสดงผล
     * function($item, $index, $prop)
     * $item array ข้อมูล
     * $row int ลำดับที่ของข้อมูล (key)
     * $prop array property ของ tr เช่น $prop[0]['id'] = xxx.
     *
     * @var array array($this, methodName)
     */
    private $onRow;
    /**
     * ชื่อฟังก์ชั่น Javascript เรียกก่อนที่จะมีการลบแถว (pmButton)
     * ถ้าฟังก์ชั่นคืนค่า true มา ถึงจะมีการลบแถว
     * function(tr){return true;}.
     *
     * @var string
     */
    private $onBeforeDelete;
    /**
     * ชื่อฟังก์ชั่น Javascript เรียกเมื่อมีการลบแถวแล้ว (pmButton)
     * function(){}.
     *
     * @var string
     */
    private $onDelete;
    /**
     * ชื่อฟังก์ชั่น Javascript เรียกเมื่อมีการเพิ่มแถวใหม่ (pmButton)
     * ฟังก์ชั่นนี้จะมีการเรียกใช้ก่อนเรียกใช้ $onInitRow
     * function(tr).
     *
     * @var string
     */
    private $onAddRow;
    /**
     * ชื่อฟังก์ชั่น Javascript เรียกเพื่อจัดการแถวใหม่
     * function(tr, row).
     *
     * @var string
     */
    private $onInitRow;
    /**
     * ลิสต์คำสั่ง Query หลัก สำหรับคัดเลือกข้อมูล
     * array('id', 1) WHERE `id` = 1 AND ...
     * array('id', array(1, 2)) WHERE `id` IN (1, 2) AND ...
     * array('id', '!=' , 1) WHERE `id` != 1 AND ...
     *
     * @var array
     */
    public $defaultFilters = array();
    /**
     * ฟิลเตอร์ข้อมูลแสดงผล
     * ถ้ากำหนดรายการนี้จะแสดงตัวเลือกการ filter ด้านบนตาราง.
     *
     * @var array
     */
    public $filters = array();
    /**
     * รายชื่อคอลัมน์ที่ไม่ต้องแสดงผล.
     *
     * @var array
     */
    public $hideColumns = array();
    /**
     * รายชื่อคอลัมน์ทั้งหมด.
     *
     * @var array
     */
    public $columns = array();
    /**
     * รายชื่อส่วนหัวของตอลัมน์.
     *
     * @var array
     */
    public $headers = array();
    /**
     * รายชื่อฟิลด์ที่สามารถค้นหาได้
     * ถ้ากำหนดรายการนี้จะแสดงกล่องค้นหา.
     *
     * @var array
     */
    public $searchColumns = array();
    /**
     * กำหนดวิธีการค้นหาจากช่อง search
     * true (default) ค้นหาจาก $searchColumns โดยอัตโนมัติ
     * false กำหนดการค้นหาด้วยตัวเอง.
     *
     * @var bool
     */
    public $autoSearch = true;
    /**
     * การแสดงฟอร์มค้นหา
     * auto (default) แสดงฟอร์มค้นหา ถ้ามี $searchColumns ระบุมา
     * true แสดงฟอร์มค้นหาเสมอ
     * false ไม่ต้องแสดงฟอร์มค้นหา
     *
     * @var bool
     */
    public $searchForm = 'auto';
    /**
     * ข้อความค้นหา
     *
     * @var string
     */
    public $search = '';
    /**
     * จำนวนรายการต่อหน้า
     * ถ้ากำหนดรายการนี้จะแสดงรายการแบ่งหน้า และตัวเลือกแสดงรายการต่อหน้า.
     *
     * @var int|null
     */
    public $perPage = null;
    /**
     * หน้าที่กำลังแสดงผล
     *
     * @var int
     */
    public $page = 1;
    /**
     * ชื่อคอลัมน์ที่ใช้เรียงลำดับ
     * ค่าเริ่มต้น null สำหรับการรับค่าอัตโนมัติ.
     *
     * @var string|null
     */
    public $sort = null;
    /**
     * ชื่อคอลัมน์ที่ใช้เรียงลำดับเริ่มต้น
     * ถ้ามีการกำหนดค่าจะเรียงลำดับตามรายการนี้ก่อนเสมอ
     *
     * @var string|null
     */
    public $defaultSort = null;
    /**
     * ข้อมูลการเรียงลำดับที่กำลังใช้งานอยู่.
     *
     * @var array
     */
    protected $sorts = array();
    /**
     * ปุ่มที่จะใส่ไว้ด้านหลังของแต่ละแถว.
     *
     * @var array
     */
    public $buttons = array();
    /**
     * method สำหรับเตรียมการแสดงผล button
     * ถ้าคืนค่า false กลับมาจะไม่มีการสรางปุ่ม
     * function($btn, $attributes, $items)
     * $btn string id ของ button
     * $attributes array property ของปุ่ม
     * $items array ข้อมูลในแถว.
     *
     * @var array array($this, methodName)
     */
    private $onCreateButton;
    /**
     * method เรียกเมื่อต้องการสร้าง header
     * คืนค่า tag tr ที่อยู่ภายใน header
     * function().
     *
     * @var array array($this, methodName)
     */
    private $onCreateHeader;
    /**
     * method เรียกเมื่อต้องการสร้าง footer
     * คืนค่า tag tr ที่อยู่ภายใน footer
     * function().
     *
     * @var array array($this, methodName)
     */
    private $onCreateFooter;
    /**
     * กำหนดคอลัมน์ หากยอมให้สามารถจัดลำดับตารางด้วยการลากได้.
     *
     * @var int
     */
    private $dragColumn = -1;
    /**
     * ชื่อคีย์หลักของข้อมูล
     * สำหรับอ่าน id ของ แถว.
     *
     * @var string
     */
    private $primaryKey = 'id';
    /**
     * Javascript.
     *
     * @var array
     */
    private $javascript = array();
    /**
     * เปิดใช้งาน Javascript ของตาราง
     * true เปิดใช้งาน GTable
     * false ปิดใช้งาน GTable แต่ยังแทรก Javascript อื่นๆได้.
     *
     * @var bool
     */
    public $enableJavascript = true;
    /**
     * Uri ปัจจุบันของหน้าเว็บ.
     *
     * @var Uri
     */
    private $uri;
    /**
     * ตัวเลือกจำนวนการแสดงผล.
     *
     * @var array
     */
    public $entriesList = array(10, 20, 30, 40, 50, 100);
    /**
     * แสดง Query ออกทางจอภาพ.
     *
     * @var bool
     */
    private $debug = false;
    /**
     * แสดง Explain ของ Query.
     *
     * @var bool
     */
    private $explain = false;

    /**
     * Initial Class.
     *
     * @param array $param
     */
    public function __construct($param)
    {
        $this->id = 'datatable';
        foreach ($param as $key => $value) {
            $this->{$key} = $value;
        }
        if (empty($this->uri)) {
            $this->uri = self::$request->getUri();
        } elseif (is_string($this->uri)) {
            $this->uri = Uri::createFromUri($this->uri);
        }
        // รายการต่อหน้า มาจากการเลือกภายในตาราง
        if ($this->perPage !== null) {
            $count = self::$request->globals(array('POST', 'GET'), 'count', $this->perPage)->toInt();
            if (in_array($count, $this->entriesList)) {
                $this->perPage = $count;
                $this->uri = $this->uri->withParams(array('count' => $count));
            }
        }
        // header ของตาราง มาจาก model หรือมาจากข้อมูล หรือ มาจากการกำหนดเอง
        if (isset($this->model)) {
            // แปลงฐานข้อมูลเป็น Model
            $model = new \Kotchasan\Model();
            $model = $model->db()->createQuery()->select();
            // อ่านข้อมูลรายการแรกเพื่อใช้ชื่อฟิลด์เป็นหัวตาราง
            if (is_string($this->model)) {
                // model เป็น Recordset, create Recordset
                $rs = new \Kotchasan\Orm\Recordset($this->model);
                // แปลง Recordset เป็น QueryBuilder
                $this->model = $model->from(array($rs->toQueryBuilder(), 'Z9'));
            } else {
                $this->model = $model->from(array($this->model, 'Z9'));
            }
            // อ่านข้อมูลรายการแรก
            if ($this->explain) {
                $first = $this->model->copy()->explain()->first();
            } else {
                $first = $this->model->copy()->first($this->fields);
            }
            // อ่านคอลัมน์ของตาราง
            if ($first) {
                foreach ($first as $k => $v) {
                    $this->columns[$k] = array('text' => $k);
                }
            } elseif (!empty($this->fields)) {
                foreach ($this->fields as $field) {
                    if (is_array($field)) {
                        $this->columns[$field[1]] = array('text' => $field[1]);
                    } elseif (is_string($field) && preg_match('/(.*?[`\s]+)?([a-z0-9_]+)`?$/i', $field, $match)) {
                        $this->columns[$match[2]] = array('text' => $match[2]);
                    }
                }
            }
        } elseif (isset($this->datas)) {
            // อ่านคอลัมน์จากข้อมูลรายการแรก
            $this->columns = array();
            if (!empty($this->datas)) {
                foreach (reset($this->datas) as $key => $value) {
                    $this->columns[$key] = array('text' => $key);
                }
            }
        }
        // สำหรับตรวจสอบว่าสามารถเรียงลำดับได้หรือไม่ ตาม header ที่ส่งมา
        $autoSort = false;
        // จัดการ header, ตรวจสอบกับค่ากำหนดมา เรียงลำดับ header ตาม columns
        if (!empty($this->columns)) {
            $headers = array();
            foreach ($this->columns as $field => $attributes) {
                if (!in_array($field, $this->hideColumns)) {
                    if (isset($this->headers[$field])) {
                        $headers[$field] = $this->headers[$field];
                        if (!isset($headers[$field]['text'])) {
                            $headers[$field]['text'] = $field;
                        }
                        if (isset($headers[$field]['sort'])) {
                            $autoSort = true;
                        }
                    } else {
                        $headers[$field]['text'] = $field;
                    }
                }
            }
            $this->headers = $headers;
        }
        // รับค่าการเรียงลำดับถ้ามีการกำหนด sort ไว้
        if ($autoSort) {
            $this->sort = self::$request->globals(array('POST', 'GET'), 'sort', $this->sort)->topic();
        }
        if (!empty($this->sort)) {
            $this->uri = $this->uri->withParams(array('sort' => $this->sort));
        }
        // search
        $this->search = self::$request->globals(array('POST', 'GET'), 'search')->text();
    }

    /**
     * กำหนด Javascript
     *
     * @param string $script
     */
    public function script($script)
    {
        $this->javascript[] = $script;
    }

    /**
     * สร้างตาราง และเริ่มต้นทำงานตาราง
     * คืนค่าเป็นโค้ด HTML ของ DataTable
     *
     * @return string
     */
    public function render()
    {
        if (!empty($this->actions) && $this->checkCol == -1) {
            $this->checkCol = 1;
        }
        $url_query = array();
        $hidden_fields = array();
        $query_string = array();
        parse_str($this->uri->getQuery(), $query_string);
        self::$request->map($url_query, $query_string);
        foreach ($url_query as $key => $value) {
            // Input สำหรับการเรียกไปยังหน้าถัดไป
            if (!preg_match('/.*?([0-9]+|username|password|token|time|search|count|page|action).*?/', $key)) {
                $hidden_fields[$key] = '<input type="hidden" name="'.$key.'" value="'.htmlspecialchars($value).'">';
            }
        }
        if (isset($this->model)) {
            // รายการ Query หลัก (AND)
            $qs = array();
            foreach ($this->defaultFilters as $array) {
                $qs[] = $array;
            }
        }
        // create HTML
        $content = array('<div class="datatable" id="'.$this->id.'">');
        // form
        $form = array();
        if ($this->perPage !== null) {
            $entries = Language::get('entries');
            $options = array();
            foreach ($this->entriesList as $c) {
                if ($c == 0) {
                    $options[0] = Language::get('all items');
                } else {
                    $options[$c] = $c.' '.$entries;
                }
            }
            $form[] = $this->addFilter(array(
                'name' => 'count',
                'text' => Language::get('Show'),
                'value' => $this->perPage,
                'options' => $options,
            ));
        }
        // รายการ Query กำหนดโดย User (AND)
        foreach ($this->filters as $key => $items) {
            $form[] = $this->addFilter($items);
            if (isset($items['name'])) {
                unset($hidden_fields[$items['name']]);
            }
            if (!isset($items['default'])) {
                $items['default'] = '';
            }
            // ไม่ Query รายการ default
            if (!empty($items['options']) && isset($items['value']) && $items['value'] !== $items['default'] && in_array($items['value'], array_keys($items['options']), true)) {
                if (isset($items['onFilter'])) {
                    $q = call_user_func($items['onFilter'], $key, $items['value']);
                    if ($q) {
                        $qs[] = $q;
                    }
                } elseif (is_string($key)) {
                    $qs[] = array($key, $items['value']);
                }
            }
        }
        if ($this->model) {
            if (!empty($qs)) {
                $this->model->andWhere($qs);
            }
        }
        // ปุ่ม Go
        if (!empty($form)) {
            $form[] = '<fieldset class=go>';
            $form[] = '<button type=submit class="button go">'.Language::get('Go').'</button>';
            $form[] = implode('', $hidden_fields);
            $form[] = '</fieldset>';
        }
        // search
        if ($this->searchForm === true || ($this->searchForm === 'auto' && !empty($this->searchColumns))) {
            if (!empty($this->search)) {
                if ($this->autoSearch) {
                    if (isset($this->model)) {
                        $sh = array();
                        foreach ($this->searchColumns as $key) {
                            $sh[] = array($key, 'LIKE', '%'.$this->search.'%');
                        }
                        $this->model->andWhere($sh, 'OR');
                    } elseif (isset($this->datas)) {
                        // filter ข้อมูลจาก array
                        $this->datas = ArrayTool::filter($this->datas, $this->search);
                    }
                }
                $this->uri = $this->uri->withParams(array('search' => $this->search));
            }
            $form[] = '</div>';
            $form[] = '<div class="table_search">';
            $form[] = '<fieldset class=search>';
            $form[] = '<label accesskey=f><input type=text name=search value="'.$this->search.'" placeholder="'.Language::get('Search').'"></label>';
            $form[] = '<button type=submit>&#xe607;</button>';
            $form[] = '<button type=submit class=clear_search>&#x78;</button>';
            $form[] = '</fieldset>';
        }
        if (!$this->explain && !empty($form)) {
            $content[] = '<form class="table_nav clear" method="get" action="'.$this->uri.'"><div>'.implode('', $form).'</div></form>';
        }
        if (isset($this->model)) {
            if ($this->explain) {
                // explain mode
                $count = 0;
            } else {
                // field select
                $this->model->select($this->fields);
                // จำนวนข้อมูลทั้งหมด (Query Builder)
                $model = new \Kotchasan\Model();
                $query = $model->db()->createQuery()
                    ->selectCount()
                    ->from(array($this->model, 'Z'));
                if ($this->cache) {
                    $query->cacheOn();
                }
                $result = $query->toArray()->execute();
                $count = empty($result) ? 0 : $result[0]['count'];
            }
        } elseif (!empty($this->datas)) {
            // จำนวนข้อมูลทั้งหมดจาก array
            $count = count($this->datas);
        } else {
            // ไม่มีข้อมูล
            $count = 0;
        }
        // การแบ่งหน้า
        if ($this->perPage > 0) {
            // หน้าที่เลือก
            $this->page = max(1, self::$request->globals(array('POST', 'GET'), 'page', 1)->toInt());
            // ตรวจสอบหน้าที่เลือกสูงสุด
            $totalpage = round($count / $this->perPage);
            $totalpage += ($totalpage * $this->perPage < $count) ? 1 : 0;
            $this->page = max(1, $this->page > $totalpage ? $totalpage : $this->page);
            $start = $this->perPage * ($this->page - 1);
            // คำนวณรายการที่แสดง
            $s = $start < 0 ? 0 : $start + 1;
            $e = min($count, $s + $this->perPage - 1);
        } else {
            $start = 0;
            $totalpage = 1;
            $this->page = 1;
            $s = 1;
            $e = $count;
            $this->perPage = 0;
        }
        // table caption
        if ($this->showCaption) {
            if (empty($this->search)) {
                $caption = Language::get('All :count entries, displayed :start to :end, page :page of :total pages');
            } else {
                $caption = Language::get('Search <strong>:search</strong> found :count entries, displayed :start to :end, page :page of :total pages');
            }
            $caption = str_replace(array(':search', ':count', ':start', ':end', ':page', ':total'), array($this->search, number_format($count), number_format($s), number_format($e), number_format($this->page), number_format($totalpage)), $caption);
        }
        // เรียงลำดับ
        $orders = array();
        if (!empty($this->defaultSort)) {
            foreach (explode(',', $this->defaultSort) as $sort) {
                if (preg_match('/^([a-z0-9_\-]+)([\s]+(desc|asc))?$/i', trim($sort), $match)) {
                    $sortType = isset($match[3]) && strtolower($match[3]) == 'desc' ? 'desc' : 'asc';
                    $orders[] = $match[1].' '.$sortType;
                    $this->sorts[$match[1]] = $sortType;
                }
            }
        }
        if (!empty($this->sort)) {
            $sorts = array();
            foreach (explode(',', $this->sort) as $sort) {
                if (preg_match('/^([a-z0-9_\-]+)([\s]+(desc|asc))?$/i', trim($sort), $match)) {
                    if (isset($this->headers[$match[1]]['sort'])) {
                        $sort = $this->headers[$match[1]]['sort'];
                    } elseif (isset($this->columns[$match[1]])) {
                        $sort = $match[1];
                    } elseif ($this->model && isset($this->columns[$match[1]])) {
                        $sort = $match[1];
                    } else {
                        $sort = null;
                    }
                    if ($sort) {
                        $sortType = isset($match[3]) && strtolower($match[3]) == 'desc' ? 'desc' : 'asc';
                        $this->sorts[$sort] = $sortType;
                        $sorts[] = $sort.' '.$sortType;
                    }
                }
            }
            $this->sort = implode(',', $sorts);
            $orders = array_merge($orders, $sorts);
        }

        if (isset($this->model)) {
            if (!empty($orders)) {
                $this->model->order($orders);
            }
        } elseif (!empty($this->sorts)) {
            reset($this->sorts);
            $sort = key($this->sorts);
            $this->datas = ArrayTool::sort($this->datas, $sort, $this->sorts[$sort]);
        }
        if (isset($this->model)) {
            if ($this->explain) {
                $this->model->explain();
            }
            if ($this->debug === true) {
                // debug Query
                $this->debug = $this->model->toArray()->limit($this->perPage, $start)->text();
            }
            // query ข้อมูล
            $this->datas = $this->model->toArray()->limit($this->perPage, $start)->execute();
            // รายการสุดท้าย
            $end = $this->perPage + 1;
            // รายการแรก
            $start = -1;
        } elseif (isset($this->datas)) {
            if ($this->debug === true) {
                // debug ข้อมูลแอเรย์
                $this->debug = var_export($this->datas, true);
            }
            // รายการสุดท้าย
            $end = $start + $this->perPage - 1;
            // รายการแรก
            $start = $start - 2;
        } else {
            $end = 0;
        }
        // property ของ ตาราง
        $prop = array();
        $c = array();
        if (isset($this->class)) {
            $c[] = $this->class;
        }
        if ($this->border) {
            $c[] = 'border';
        }
        if ($this->responsive) {
            $c[] = 'responsive-v';
        }
        if ($this->fullWidth) {
            $c[] = 'fullwidth';
        }
        if (count($c) > 0) {
            $prop[] = ' class="'.implode(' ', $c).'"';
        }
        // table
        $content[] = '<div class="tablebody"><table'.implode('', $prop).'>';
        if ($this->showCaption) {
            $content[] = '<caption>'.$caption.'</caption>';
        }
        $colCount = 0;
        // table header
        $thead = null;
        if (!$this->explain && isset($this->onCreateHeader)) {
            $thead = call_user_func($this->onCreateHeader);
        } elseif (!empty($this->headers)) {
            $row = array();
            $i = 0;
            $colspan = 0;
            foreach ($this->headers as $key => $attributes) {
                if ($colspan === 0) {
                    if (!$this->explain) {
                        if (!$this->hideCheckbox && $i == $this->checkCol) {
                            $row[] = '<th class="check-column"><a class="checkall icon-uncheck"></a></th>';
                            ++$colCount;
                        }
                        if ($i == $this->dragColumn) {
                            $row[] = '<th></th>';
                            ++$colCount;
                        }
                    }
                    if (isset($attributes['colspan'])) {
                        $colspan = $attributes['colspan'] - 1;
                    }
                    $row[] = $this->th($i, $key, $attributes);
                    ++$i;
                } else {
                    --$colspan;
                }
                ++$colCount;
            }
            if (!$this->explain && !$this->hideCheckbox && $colCount == $this->checkCol) {
                $row[] = '<th class="check-column"><a class="checkall icon-uncheck"></a></th>';
                ++$colCount;
            }
            if ($colspan === 0) {
                if (!empty($this->buttons)) {
                    $row[] = $this->th($i, '', array('text' => ''));
                    ++$colCount;
                    ++$i;
                }
            } else {
                --$colspan;
            }
            if ($colspan === 0) {
                if ($this->pmButton) {
                    $row[] = $this->th($i, '', array('text' => ''));
                    ++$colCount;
                }
            } else {
                --$colspan;
            }
            $thead = '<tr>'.implode('', $row).'</tr>';
        }
        if (!empty($thead) && is_string($thead)) {
            $content[] = '<thead>'.$thead.'</thead>';
        }
        // tbody
        if (!empty($this->datas)) {
            $content[] = '<tbody>'.$this->tbody($start, $end).'</tbody>';
        }
        if (!$this->explain) {
            // tfoot
            $tfoot = null;
            if (isset($this->onCreateFooter)) {
                $tfoot = call_user_func($this->onCreateFooter);
            } elseif (!$this->hideCheckbox && $this->checkCol > -1) {
                $tfoot = '<tr>';
                $tfoot .= '<td colspan="'.$this->checkCol.'"></td>';
                $tfoot .= '<td class="check-column"><a class="checkall icon-uncheck"></a></td>';
                $tfoot .= '<td colspan="'.($colCount - $this->checkCol - 1).'"></td>';
                $tfoot .= '</tr>';
            }
            if (!empty($tfoot) && is_string($tfoot)) {
                $content[] = '<tfoot>'.$tfoot.'</tfoot>';
            }
        }
        $content[] = '</table></div>';
        $table_nav = array();
        if (!empty($this->actions) && is_array($this->actions)) {
            foreach ($this->actions as $item) {
                $table_nav[] = $this->addAction($item);
            }
        }
        if (!empty($this->addNew) && is_array($this->addNew)) {
            $prop = array();
            foreach ($this->addNew as $k => $v) {
                if ($k != 'text') {
                    $prop[$k] = $k.'="'.$v.'"';
                }
            }
            $text = isset($this->addNew['text']) ? $this->addNew['text'] : '';
            if (preg_match('/^((.*)\s+)?(icon-[a-z0-9\-_]+)(\s+(.*))?$/', $this->addNew['class'], $match)) {
                $prop['class'] = 'class="'.trim($match[2].' '.(isset($match[5]) ? $match[5] : '')).'"';
                if (isset($prop['href'])) {
                    $table_nav[] = '<a '.implode(' ', $prop).'><span class="'.$match[3].'">'.$text.'</span></a>';
                } else {
                    $table_nav[] = '<button '.implode(' ', $prop).' type="button"><span class="'.$match[3].'">'.$text.'</span></button>';
                }
            } elseif (isset($prop['href'])) {
                $table_nav[] = '<a '.implode(' ', $prop).'>'.$text.'</a>';
            } else {
                $table_nav[] = '<button '.implode(' ', $prop).' type="button">'.$text.'</button>';
            }
        }
        if (!$this->explain) {
            if (!empty($table_nav)) {
                $content[] = '<div class="table_nav clear action">'.implode('', $table_nav).'</div>';
            }
            // แบ่งหน้า
            if ($this->perPage > 0) {
                $content[] = '<div class="splitpage">'.$this->uri->pagination($totalpage, $this->page).'</div>';
            }
        }
        $content[] = '</div>';
        if ($this->enableJavascript && !$this->explain) {
            $script = array(
                'page' => $this->page,
                'search' => $this->search,
                'sort' => $this->sort,
                'action' => $this->action,
                'actionCallback' => $this->actionCallback,
                'actionConfirm' => $this->actionConfirm,
                'onBeforeDelete' => $this->onBeforeDelete,
                'onDelete' => $this->onDelete,
                'onInitRow' => $this->onInitRow,
                'onAddRow' => $this->onAddRow,
                'pmButton' => $this->pmButton,
                'dragColumn' => $this->dragColumn,
                'debug' => is_string($this->debug) ? $this->debug : '',
            );
            $this->javascript[] = 'var table = new GTable("'.$this->id.'", '.json_encode($script).');';
        }
        if (!empty($this->javascript)) {
            $content[] = "<script>\n".implode("\n", $this->javascript)."\n</script>";
        }
        return implode("\n", $content);
    }

    /**
     * render tbody
     *
     * @param int $start ข้อมูลเริ่มต้น (เริ่มที่ 1)
     *
     * @return string
     */
    private function tbody($start, $end)
    {
        $row = array();
        $n = 0;
        foreach ($this->datas as $o => $items) {
            if ($this->perPage <= 0 || ($n > $start && $n < $end)) {
                $src_items = $items;
                // id ของข้อมูล
                $id = isset($items[$this->primaryKey]) ? $items[$this->primaryKey] : $o;
                $prop = (object) array(
                    'id' => $this->id.'_'.$id,
                );
                $buttons = array();
                if (!$this->explain) {
                    if (!empty($this->buttons)) {
                        foreach ($this->buttons as $btn => $attributes) {
                            if (isset($this->onCreateButton)) {
                                // event ปุ่มหลัก
                                $attributes = call_user_func($this->onCreateButton, $btn, $attributes, $items);
                            }
                            if ($attributes && $attributes !== false) {
                                $buttons[] = $this->button($btn, $attributes, $items);
                            }
                        }
                    }
                    if (isset($this->onRow)) {
                        $items = call_user_func($this->onRow, $items, $o, $prop);
                    }
                    if (isset($this->dragColumn)) {
                        $prop->class = (empty($prop->class) ? 'sort' : $prop->class.' sort');
                    }
                }
                // แถว
                $p = array();
                foreach ($prop as $k => $v) {
                    $p[] = $k.'="'.$v.'"';
                }
                $row[] = '<tr '.implode(' ', $p).'>';
                // แสดงผลข้อมูล
                $i = 0;
                foreach ($this->headers as $field => $attributes) {
                    if (!empty($field) && !in_array($field, $this->hideColumns)) {
                        if (!$this->explain) {
                            if (!$this->hideCheckbox && $i == $this->checkCol) {
                                $row[] = '<td headers="r'.$id.'" class="check-column"><a id="check_'.$id.'" class="icon-uncheck"></a></td>';
                            }
                            if ($i == $this->dragColumn) {
                                $row[] = '<td class=center><a id="move_'.$id.'" title="'.Language::get('Drag and drop to reorder').'" class="icon-move"></a></td>';
                            }
                        }
                        $properties = isset($this->cols[$field]) ? $this->cols[$field] : array();
                        $text = isset($items[$field]) ? $items[$field] : '';
                        $th = isset($attributes['text']) ? $attributes['text'] : $field;
                        $row[] = $this->td($id, $i, $properties, $text, $th);
                        ++$i;
                    }
                }
                if (!$this->explain) {
                    if (!$this->hideCheckbox && $i == $this->checkCol) {
                        $row[] = '<td headers="r'.$id.'" class="check-column"><a id="check_'.$id.'" class="icon-uncheck"></a></td>';
                        ++$i;
                    }
                    if (!empty($this->buttons)) {
                        if (!empty($buttons)) {
                            $patt = array();
                            $replace = array();
                            $keys = array_keys($src_items);
                            rsort($keys);
                            foreach ($keys as $k) {
                                if (!is_array($src_items[$k])) {
                                    $patt[] = ":$k";
                                    $replace[] = $src_items[$k];
                                }
                            }
                            $prop = array('class' => 'buttons');
                            if (isset($this->cols['buttons']) && isset($this->cols['buttons']['class'])) {
                                $prop = array('class' => $this->cols['buttons']['class'].' buttons');
                            }
                            $row[] = str_replace($patt, $replace, $this->td($id, $i, $prop, implode('', $buttons), ''));
                        } else {
                            $row[] = $this->td($id, $i, array(), '', '');
                        }
                    }
                    if ($this->pmButton) {
                        $row[] = '<td class="icons"><div><a class="icon-plus" title="'.Language::get('Add New').'"></a><a class="icon-minus" title="'.Language::get('Remove').'"></a></div></td>';
                    }
                }
                $row[] = '</tr>';
            }
            ++$n;
        }
        return implode("\n", $row);
    }

    /**
     * render th.
     *
     * @param int    $i          ลำดับคอลัมน์
     * @param string $column     ชื่อคอลัมน์
     * @param array  $properties properties ของ th
     *
     * @return string
     */
    private function th($i, $column, $properties)
    {
        $c = array();
        $c['id'] = 'id="c'.$i.'"';
        if (!empty($properties['sort'])) {
            $sort = isset($this->sorts[$properties['sort']]) ? $this->sorts[$properties['sort']] : 'none';
            $properties['class'] = 'sort_'.$sort.' col_'.$column.(empty($properties['class']) ? '' : ' '.$properties['class']);
        }
        foreach ($properties as $key => $value) {
            if ($key !== 'sort' && $key !== 'text') {
                $c[$key] = $key.'="'.$value.'"';
            }
        }
        return '<th '.implode(' ', $c).'>'.(isset($properties['text']) ? $properties['text'] : $column).'</th>';
    }

    /**
     * render td.
     *
     * @param int    $id         id ของ แถว
     * @param int    $i          ลำดับคอลัมน์
     * @param array  $properties ชื่อคอลัมน์
     * @param string $text       ข้อความใน td
     *
     * @return string
     */
    private function td($id, $i, $properties, $text, $th)
    {
        $c = array('data-text' => 'data-text="'.$th.'"');
        foreach ($properties as $key => $value) {
            $c[$key] = $key.'="'.$value.'"';
        }
        $c = implode(' ', $c);
        if ($i == 0) {
            $c .= ' id="r'.$id.'" headers="c'.$i.'"';
            return '<th '.$c.'>'.$text.'</th>';
        } else {
            $c .= ' headers="c'.$i.' r'.$id.'"';
            return '<td '.$c.'>'.$text.'</td>';
        }
    }

    /**
     * render button.
     *
     * @param string $btn ชื่อ button
     * @param array $properties properties ของ button
     * @param array $items ข้อมูลในแถว
     *
     * @return string
     */
    private function button($btn, $properties, $items)
    {
        if (isset($properties['submenus'])) {
            $innerHTML = '';
            $li = '';
            $attributes = array();
            foreach ($properties as $name => $item) {
                if ($name == 'submenus') {
                    foreach ($item as $btn => $menu) {
                        $prop = array();
                        if (isset($this->onCreateButton)) {
                            // event ปุ่ม submenus
                            $menu = call_user_func($this->onCreateButton, $btn, $menu, $items);
                        }
                        if ($menu && $menu !== false) {
                            $text = '';
                            foreach ($menu as $key => $value) {
                                if ($key == 'text') {
                                    $text = $value;
                                } else {
                                    $prop[$key] = $key.'="'.$value.'"';
                                }
                            }
                            $li .= '<li><a '.implode(' ', $prop).'>'.$text.'</a></li>';
                        }
                    }
                } elseif ($name == 'text') {
                    $innerHTML = $item;
                } elseif ($name == 'class') {
                    $attributes['class'] = 'class="'.$item.' menubutton"';
                } else {
                    $attributes[$name] = $name.'="'.$item.'"';
                }
            }
            if (!isset($attributes['class'])) {
                $attributes['class'] = 'class="menubutton"';
            }
            if (!isset($attributes['tabindex'])) {
                $attributes['tabindex'] = 'tabindex="0"';
            }

            return '<div '.implode(' ', $attributes).'>'.$innerHTML.'<ul>'.$li.'</ul></div>';
        } else {
            $prop = array();
            foreach ($properties as $key => $value) {
                if ($key === 'id') {
                    $prop[$key] = $key.'="'.$btn.'_'.$value.'"';
                } elseif ($key !== 'text') {
                    $prop[$key] = $key.'="'.$value.'"';
                }
            }
            if (!empty($properties['class']) && preg_match('/(.*)\s?(icon\-[a-z0-9\-_]+)($|\s(.*))/', $properties['class'], $match)) {
                $class = array();
                foreach (array(1, 4) as $i) {
                    if (!empty($match[$i])) {
                        $class[] = $match[$i];
                    }
                }
                if (empty($properties['text'])) {
                    $class[] = 'notext';
                    $prop['class'] = 'class="'.implode(' ', $class).'"';
                    return '<a '.implode(' ', $prop).'><span class="'.$match[2].'"></span></a>';
                } else {
                    $prop['class'] = 'class="'.implode(' ', $class).'"';
                    if (!isset($prop['title'])) {
                        $prop['title'] = 'title="'.strip_tags($properties['text']).'"';
                    }

                    return '<a '.implode(' ', $prop).'><span class="'.$match[2].' button_w_text"><span class=mobile>'.$properties['text'].'</span></span></a>';
                }
            } else {
                return '<a'.(empty($prop) ? '' : ' '.implode(' ', $prop)).'></a>';
            }
        }
    }

    /**
     * สร้าง select หรือ button ด้านล่างตาราง (actions).
     *
     * @param array $item
     *
     * @return string
     */
    private function addAction($item)
    {
        if (isset($item['class']) && preg_match('/^((.*)\s+)?(icon-[a-z0-9\-_]+)(\s+(.*))?$/', $item['class'], $match)) {
            $match[2] = trim($match[2].' '.(isset($match[5]) ? $match[5] : ''));
        }
        if (isset($item['options'])) {
            // select
            $rows = array();
            foreach ($item['options'] as $key => $text) {
                $rows[] = '<option value="'.$key.'">'.$text.'</option>';
            }

            return '<fieldset><select id="'.$item['id'].'">'.implode('', $rows).'</select><label for="'.$item['id'].'" class="button '.$item['class'].' action"><span>'.$item['text'].'</span></label></fieldset>';
        } elseif (isset($item['type'])) {
            $prop = array(
                'type="'.$item['type'].'"',
            );
            $prop2 = array(
                'button' => 'class="button action"',
            );
            foreach ($item as $key => $value) {
                if ($key == 'id') {
                    $prop[] = 'id="'.$value.'"';
                    $prop2[] = 'for="'.$value.'"';
                } elseif ($key == 'class') {
                    $prop2['button'] = 'class="button '.$value.' action"';
                } elseif (!in_array($key, array('type', 'text'))) {
                    $prop[] = $key.'="'.$value.'"';
                }
            }
            return '<fieldset><input '.implode(' ', $prop).'><label '.implode(' ', $prop2).'><span>'.$item['text'].'</span></label></fieldset>';
        } else {
            // link, button
            $prop = array();
            $text = isset($item['text']) ? $item['text'] : '';
            if ($text != '' && !isset($item['title'])) {
                $item['title'] = strip_tags($text);
            }
            if (isset($item['class'])) {
                if (empty($match[3])) {
                    $prop[] = 'class="'.$item['class'].'"';
                } else {
                    $text = '<span class="'.$match[3].'">'.$text.'</span>';
                    $prop[] = 'class="'.$match[2].'"';
                }
            }
            foreach ($item as $k => $v) {
                if ($k != 'class' && $k != 'text') {
                    $prop[] = $k.'="'.$v.'"';
                }
            }
            if (isset($item['href'])) {
                // link
                return '<a '.implode(' ', $prop).'>'.$text.'</a>';
            } else {
                // button
                return '<button '.implode(' ', $prop).' type="button">'.$text.'</button>';
            }
        }
    }

    /**
     * สร้าง select ด้านบนตาราง (filters)
     *
     * @param array $item
     *
     * @return string
     */
    private function addFilter($item)
    {
        if (isset($item['datalist'])) {
            $item['type'] = 'text';
        }
        if (isset($item['type'])) {
            $prop = array();
            $datalist = '';
            foreach ($item as $key => $value) {
                if ($key == 'datalist') {
                    foreach ($value as $k => $v) {
                        $datalist .= '<option value="'.$k.'">'.$v.'</option>';
                    }
                } elseif ($key != 'text' && $key != 'default' && !is_array($value)) {
                    $prop[$key] = $key.'="'.$value.'"';
                }
            }
            if ($datalist != '' && isset($item['name'])) {
                if (!isset($item['id'])) {
                    $item['id'] = $item['name'];
                    $prop['id'] = 'id="'.$item['name'].'"';
                }
                $prop['autocomplete'] = 'autocomplete="off"';
                $this->javascript[] = 'new Datalist("'.$item['id'].'");';
                $prop['list'] = 'list="'.$item['name'].'-datalist"';
                $datalist = '<datalist id="'.$item['name'].'-datalist">'.$datalist.'</datalist>';
            } else {
                $datalist = '';
            }
            $row = '<fieldset><label>'.(isset($item['text']) ? $item['text'] : '').' <input '.implode(' ', $prop).'>'.$datalist.'</label></fieldset>';
        } elseif (isset($item['items'])) {
            $button = '';
            foreach ($item['items'] as $link) {
                foreach ($link as $key => $value) {
                    if ($key != 'text') {
                        $prop[$key] = $key.'="'.$value.'"';
                    }
                }
                if (isset($item['name'])) {
                    $prop['name'] = 'name="'.$item['name'].'"';
                }
                if (isset($item['href'])) {
                    $button .= '<a '.implode(' ', $prop).'>'.(isset($link['text']) ? $link['text'] : '').'</a>';
                } else {
                    $button .= '<button '.implode(' ', $prop).'>'.(isset($link['text']) ? $link['text'] : '').'</button>';
                }
            }
            $row = '<fieldset class="buttons"><label>'.(isset($item['text']) ? $item['text'] : '').'</label>'.$button.'</fieldset>';
        } elseif (isset($item['href'])) {
            foreach ($item as $key => $value) {
                if ($key != 'text') {
                    $prop[$key] = $key.'="'.$value.'"';
                }
            }
            $row = '<a '.implode(' ', $prop).'>'.(isset($item['text']) ? $item['text'] : '').'</a>';
        } else {
            $prop = array();
            foreach ($item as $key => $value) {
                if ($key != 'options' && $key != 'value' && $key != 'text' && $key != 'default' && !is_array($value)) {
                    $prop[$key] = $key.'="'.$value.'"';
                }
            }
            $row = '<fieldset><label>'.(isset($item['text']) ? $item['text'] : '').' <select '.implode(' ', $prop).'>';
            if (!empty($item['options'])) {
                foreach ($item['options'] as $key => $text) {
                    $sel = isset($item['value']) && (string) $key == $item['value'] ? ' selected' : '';
                    $row .= '<option value="'.$key.'"'.$sel.'>'.$text.'</option>';
                }
            }
            $row .= '</select></label></fieldset>';
        }
        return $row;
    }
}
