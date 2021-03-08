<?php
/**
 * @filesource Kotchasan/Database/Schema.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan\Database;

/**
 * Database schema.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Schema
{
    /**
     * Database object.
     *
     * @var Driver
     */
    private $db;
    /**
     * รายการ Schema ที่โหลดแล้ว.
     *
     * @var array
     */
    private $tables = array();

    /**
     * Create Schema Class.
     *
     * @param Driver $db
     *
     * @return \static
     */
    public static function create(Driver $db)
    {
        $obj = new static();
        $obj->db = $db;

        return $obj;
    }

    /**
     * อ่านรายชื่อฟิลด์ของตาราง
     * คืนค่ารายชื่อฟิลด์ทั้งหมดในตาราง.
     *
     * @return array
     */
    public function fields($table)
    {
        if (empty($table)) {
            throw new \InvalidArgumentException('table name empty in fields');
        } else {
            $this->init($table);

            return array_keys($this->tables[$table]);
        }
    }

    /**
     * อ่านข้อมูล Schema จากตาราง.
     *
     * @param string $table
     */
    private function init($table)
    {
        if (empty($this->tables[$table])) {
            $sql = "SHOW FULL COLUMNS FROM $table";
            $columns = $this->db->cacheOn()->customQuery($sql, true);
            if (empty($columns)) {
                throw new \InvalidArgumentException($this->db->getError());
            } else {
                $datas = array();
                foreach ($columns as $column) {
                    $datas[$column['Field']] = $column;
                }
                $this->tables[$table] = $datas;
            }
        }
    }
}
