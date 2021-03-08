<?php
/**
 * @filesource Kotchasan/Database/Driver.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan\Database;

use Kotchasan\ArrayTool;
use Kotchasan\Cache\CacheItem as Item;
use Kotchasan\Database\DbCache as Cache;
use Kotchasan\Log\Logger;
use Kotchasan\Text;

/**
 * Kotchasan Database driver Class (base class).
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
abstract class Driver extends Query
{
    /**
     * cache class.
     *
     * @var Cache
     */
    protected $cache;
    /**
     * Cacheitem.
     *
     * @var Item
     */
    protected $cache_item;
    /**
     * database connection.
     *
     * @var resource
     */
    protected $connection = null;
    /**
     * database error message.
     *
     * @var string
     */
    protected $error_message = '';
    /**
     * นับจำนวนการ query.
     *
     * @var int
     */
    protected static $query_count = 0;
    /**
     * เก็บ Object ที่เป็นผลลัพท์จากการ query.
     *
     * @var resource|object
     */
    protected $result_id;
    /**
     * ตัวแปรเก็บ query สำหรับการ execute.
     *
     * @var array
     */
    protected $sqls;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->cache = Cache::create();
    }

    /**
     * อ่านสถานะของแคช
     * 0 ไม่ใช้แคช
     * 1 โหลดและบันทึกแคชอัตโนมัติ
     * 2 โหลดข้อมูลจากแคชได้ แต่ไม่บันทึกแคชอัตโนมัติ.
     *
     * @return int
     */
    public function cacheGetAction()
    {
        return $this->cache->getAction();
    }

    /**
     * เปิดการใช้งานแคช
     * จะมีการตรวจสอบจากแคชก่อนการสอบถามข้อมูล.
     *
     * @param bool $auto_save (options) true (default) บันทึกผลลัพท์อัตโนมัติ, false ต้องบันทึกแคชเอง
     *
     * @return \static
     */
    public function cacheOn($auto_save = true)
    {
        $this->cache->cacheOn($auto_save);

        return $this;
    }

    /**
     * ฟังก์ชั่นบันทึก Cache
     * สำเร็จคืนค่า true ไม่สำเร็จคืนค่า false.
     *
     * @param array $datas ข้อมูลที่จะบันทึก
     *
     * @return bool
     */
    public function cacheSave($datas)
    {
        if ($this->cache_item instanceof Item) {
            return $this->cache->save($this->cache_item, $datas);
        }
        return false;
    }

    /**
     * close database.
     */
    public function close()
    {
        $this->connection = null;
    }

    /**
     * ฟังก์ชั่นอ่านค่า resource ID ของการเชื่อมต่อปัจจุบัน.
     *
     * @return resource
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * ฟังก์ชั่นสร้าง query builder.
     *
     * @return \Kotchasan\Database\QueryBuilder
     */
    public function createQuery()
    {
        return new QueryBuilder($this);
    }

    /**
     * ฟังก์ชั่นประมวลผลคำสั่ง SQL สำหรับสอบถามข้อมูล คืนค่าผลลัพท์เป็นแอเรย์ของข้อมูลที่ตรงตามเงื่อนไข
     * คืนค่าผลการทำงานเป็น record ของข้อมูลทั้งหมดที่ตรงตามเงื่อนไข ไม่พบคืนค่าแอเรย์ว่าง.
     *
     *
     * @param string $sql     query string
     * @param bool   $toArray default false คืนค่าผลลัทเป็น Object, true คืนค่าเป็น Array
     * @param array  $values  ถ้าระบุตัวแปรนี้จะเป็นการบังคับใช้คำสั่ง prepare แทน query
     *
     * @return array
     */
    public function customQuery($sql, $toArray = false, $values = array())
    {
        $result = $this->doCustomQuery($sql, $values);
        if ($result && !$toArray) {
            foreach ($result as $i => $item) {
                $result[$i] = (object) $item;
            }
        }
        return $result;
    }

    /**
     * ฟังก์ชั่นตรวจสอบว่ามี database หรือไม่
     * คืนค่า true หากมีฐานข้อมูลนี้อยู่ ไม่พบคืนค่า false.
     *
     * @param string $database ชื่อฐานข้อมูล
     *
     * @return bool
     */
    public function databaseExists($database)
    {
        $search = $this->doCustomQuery("SELECT 1 FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME='$database'");

        return $search && count($search) == 1 ? true : false;
    }

    /**
     * ฟังก์ชั่นลบ record
     * สำเร็จคืนค่าจำนวนแถวที่มีผล ไม่สำเร็จคืนค่า false.
     *
     * @param string $table_name ชื่อตาราง
     * @param mixed  $condition  query WHERE
     * @param int    $limit      จำนวนรายการที่ต้องการลบ 1 (default) รายการแรกที่เจอ, หมายถึงลบทุกรายการ
     * @param string $operator   AND (default) หรือ OR
     *
     * @return int|bool
     */
    public function delete($table_name, $condition, $limit = 1, $operator = 'AND')
    {
        $condition = $this->buildWhere($condition, $operator);
        if (is_array($condition)) {
            $values = $condition[1];
            $condition = $condition[0];
        } else {
            $values = array();
        }
        $sql = 'DELETE FROM '.$table_name.' WHERE '.$condition;
        if (is_int($limit) && $limit > 0) {
            $sql .= ' LIMIT '.$limit;
        }

        return $this->doQuery($sql, $values);
    }

    /**
     * ฟังก์ชั่นลบข้อมูลทั้งหมดในตาราง
     * คืนค่า true ถ้าสำเร็จ.
     *
     * @param string $table_name table name
     *
     * @return bool
     */
    public function emptyTable($table_name)
    {
        return $this->query("TRUNCATE TABLE $table_name") === false ? false : true;
    }

    /**
     * ฟังก์ชั่นประมวลผลคำสั่ง SQL จาก query builder.
     *
     * @param array $sqls
     * @param array $values ถ้าระบุตัวแปรนี้จะเป็นการบังคับใช้คำสั่ง prepare แทน query
     * @param bool $debugger แสดงผล Query
     *
     * @return mixed
     */
    public function execQuery($sqls, $values = array(), $debugger = false)
    {
        $sql = $this->makeQuery($sqls);
        if (isset($sqls['values'])) {
            $values = ArrayTool::replace($sqls['values'], $values);
        }
        if ($debugger) {
            echo '<pre>';
            echo $sql;
            if (!empty($values)) {
                echo var_export($values, true);
            }
            echo '</pre>';
        }
        if ($sqls['function'] == 'customQuery') {
            $result = $this->customQuery($sql, true, $values);
        } else {
            $result = $this->query($sql, $values);
        }
        return $result;
    }

    /**
     * จำนวนฟิลด์ทั้งหมดในผลลัพท์จากการ query.
     *
     * @return int
     */
    abstract public function fieldCount();

    /**
     * ตรวจสอบคอลัมน์ของตารางว่ามีหรือไม่
     * คืนค่า true ถ้ามี คืนค่า false ถ้าไม่มี.
     *
     * @param string $table_name  ชื่อตาราง
     * @param string $column_name ชื่อคอลัมน์
     *
     * @return bool
     */
    public function fieldExists($table_name, $column_name)
    {
        $result = $this->customQuery("SHOW COLUMNS FROM `$table_name` LIKE '$column_name'");
        return empty($result) ? false : true;
    }

    /**
     * ฟังก์ชั่น query ข้อมูล คืนค่าข้อมูลทุกรายการที่ตรงตามเงื่อนไข
     * คืนค่า แอเรย์ของ object ไม่พบคืนค่าแอรย์ว่าง.
     *
     * @param string $table_name ชื่อตาราง
     * @param mixed  $condition  query WHERE
     * @param array  $sort       เรียงลำดับ
     *
     * @return array
     */
    public function find($table_name, $condition, $sort = array())
    {
        $result = array();
        foreach ($this->select($table_name, $condition, $sort) as $item) {
            $result[] = (object) $item;
        }
        return $result;
    }

    /**
     * ฟังก์ชั่น query ข้อมูล คืนค่าข้อมูลรายการเดียว
     * คืนค่า object ของข้อมูล ไม่พบคืนค่า false.
     *
     * @param string $table_name ชื่อตาราง
     * @param mixed  $condition  query WHERE
     *
     * @return object|bool
     */
    public function first($table_name, $condition)
    {
        $result = $this->select($table_name, $condition, array(), 1);
        return count($result) == 1 ? (object) $result[0] : false;
    }

    /**
     * คืนค่าข้อความผิดพลาดของฐานข้อมูล.
     *
     * @return string
     */
    public function getError()
    {
        return $this->error_message;
    }

    /**
     * รายชื่อฟิลด์ทั้งหมดจากผลัพท์จองการ query.
     *
     * @return array
     */
    abstract public function getFields();

    /**
     * ฟังก์ชั่นอ่าน ID ล่าสุดของตาราง สำหรับตารางที่มีการกำหนด Auto_increment ไว้
     * คืนค่า id ล่าสุดของตาราง.
     *
     * @param string $table_name ชื่อตาราง
     *
     * @return int
     */
    public function getNextId($table_name)
    {
        $result = $this->doCustomQuery("SHOW TABLE STATUS LIKE '$table_name'");
        if (!$result) {
            throw new \InvalidArgumentException("Table `{$table_name}` not found");
        } else {
            return (int) $result[0]['Auto_increment'];
        }
    }

    /**
     * ตรวจสอบว่ามี $index ในตารางหรือไม่
     * คืนค่า true ถ้ามี คืนค่า false ถ้าไม่มี.
     *
     * @param string $database_name
     * @param string $table_name
     * @param string $index
     *
     * @return bool
     */
    public function indexExists($database_name, $table_name, $index)
    {
        $result = $this->customQuery("SELECT * FROM information_schema.statistics WHERE table_schema='$database_name' AND table_name = '$table_name' AND column_name = '$index'");

        return empty($result) ? false : true;
    }

    /**
     * ฟังก์ชั่นเพิ่มข้อมูลใหม่ลงในตาราง
     * สำเร็จ คืนค่า id ที่เพิ่ม ผิดพลาด คืนค่า false.
     *
     * @param string $table_name ชื่อตาราง
     * @param array  $save       ข้อมูลที่ต้องการบันทึก
     *
     * @return int|bool
     */
    abstract public function insert($table_name, $save);

    /**
     * ฟังก์ชั่นเพิ่มข้อมูลใหม่ลงในตาราง
     * ถ้ามีข้อมูลเดิมอยู่แล้วจะเป็นการอัปเดต
     * (ข้อมูลเดิมตาม KEY ที่เป็น UNIQUE)
     * insert คืนค่า id ที่เพิ่ม
     * update คืนค่า 0
     * ผิดพลาด คืนค่า null.
     *
     * @param string       $table_name ชื่อตาราง
     * @param array|object $save       ข้อมูลที่ต้องการบันทึก รูปแบบ array('key1'=>'value1', 'key2'=>'value2', ...)
     *
     * @return int|null
     */
    abstract public function insertOrUpdate($table_name, $save);

    /**
     * ฟังก์ชั่นสร้างคำสั่ง sql query
     * คืนค่า sql command.
     *
     * @param array $sqls คำสั่ง sql จาก query builder
     *
     * @return string
     */
    abstract public function makeQuery($sqls);

    /**
     * ปรับปรุงตาราง
     * คืนค่า true ถ้าสำเร็จ.
     *
     * @param string $table_name table name
     *
     * @return bool
     */
    public function optimizeTable($table_name)
    {
        return $this->query("OPTIMIZE TABLE $table_name") === false ? false : true;
    }

    /**
     * ฟังก์ชั่นประมวลผลคำสั่ง SQL ที่ไม่ต้องการผลลัพท์ เช่น CREATE INSERT UPDATE
     * สำเร็จคืนค่า true ไม่สำเร็จคืนค่า false.
     *
     * @param string $sql
     * @param array  $values ถ้าระบุตัวแปรนี้จะเป็นการบังคับใช้คำสั่ง prepare แทน query
     *
     * @return bool
     */
    public function query($sql, $values = array())
    {
        return $this->doQuery($sql, $values);
    }

    /**
     * ฟังก์ชั่นอ่านจำนวน query ทั้งหมดที่ทำงาน.
     *
     * @return int
     */
    public static function queryCount()
    {
        return self::$query_count;
    }

    /**
     * ซ่อมแซมตาราง
     * คืนค่า true ถ้าสำเร็จ.
     *
     * @param string $table_name table name
     *
     * @return bool
     */
    public function repairTable($table_name)
    {
        return $this->query("REPAIR TABLE $table_name") === false ? false : true;
    }

    /**
     * เรียกดูข้อมูล
     * คืนค่าผลลัพท์ในรูป array ถ้าไม่สำเร็จ คืนค่าแอเรย์ว่าง.
     *
     * @param string $table_name ชื่อตาราง
     * @param mixed  $condition  query WHERE
     * @param array  $sort       เรียงลำดับ
     * @param int    $limit      จำนวนข้อมูลที่ต้องการ
     *
     * @return array
     */
    abstract public function select($table_name, $condition, $sort = array(), $limit = 0);

    /**
     * เลือกฐานข้อมูล
     * คืนค่า false หากไม่สำเร็จ.
     *
     * @param string $database
     *
     * @return bool
     */
    abstract public function selectDB($database);

    /**
     * ฟังก์ชั่นตรวจสอบว่ามีตาราง หรือไม่
     * คืนค่า true หากมีตารางนี้อยู่ ไม่พบคืนค่า false.
     *
     * @param string $table_name ชื่อตาราง
     *
     * @return bool
     */
    public function tableExists($table_name)
    {
        $result = $this->doCustomQuery("SHOW TABLES LIKE '$table_name'");
        return empty($result) ? false : true;
    }

    /**
     * ฟังก์ชั่นแก้ไขข้อมูล
     * สำเร็จ คืนค่า true, ผิดพลาด คืนค่า false.
     *
     * @param string $table_name ชื่อตาราง
     * @param mixed  $condition  query WHERE
     * @param array  $save       ข้อมูลที่ต้องการบันทึก รูปแบบ array('key1'=>'value1', 'key2'=>'value2', ...)
     *
     * @return bool
     */
    abstract public function update($table_name, $condition, $save);

    /**
     * อัปเดตข้อมูลทุก record
     * สำเร็จ คืนค่า true, ผิดพลาด คืนค่า false.
     *
     * @param string $table_name table name
     * @param array  $save       ข้อมูลที่ต้องการบันทึก array('key1'=>'value1', 'key2'=>'value2', ...)
     *
     * @return bool
     */
    public function updateAll($table_name, $save)
    {
        return $this->update($table_name, array(1, 1), $save);
    }

    /**
     * ฟังก์ชั่นบันทึกการ query sql.
     *
     * @param string $type
     * @param string $sql
     * @param array  $values (options)
     */
    protected function log($type, $sql, $values = array())
    {
        if (DB_LOG == true) {
            $datas = array('<b>'.$type.' :</b> '.Text::replace($sql, $values));
            foreach (debug_backtrace() as $a => $item) {
                if (isset($item['file']) && isset($item['line'])) {
                    if ($item['function'] == 'all' || $item['function'] == 'first' || $item['function'] == 'count' || $item['function'] == 'save' || $item['function'] == 'find' || $item['function'] == 'execute') {
                        $datas[] = '<br>['.$a.'] <b>'.$item['function'].'</b> in <b>'.$item['file'].'</b> line <b>'.$item['line'].'</b>';
                        break;
                    }
                }
            }
            // บันทึก log
            Logger::create()->info(implode('', $datas));
        }
    }
}
