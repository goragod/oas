<?php
/**
 * @filesource Kotchasan/Session.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

class Session extends \Kotchasan\Model
{
    /**
     * @var Object
     */
    private $database;
    /**
     * @var string
     */
    private $table;

    /**
     * @return bool
     */
    public function _open()
    {
        $model = new static;
        $this->database = $model->db();
        $this->table = $model->getTableName('sessions');
        return true;
    }

    /**
     * @return bool
     */
    public function _close()
    {
        return true;
    }

    /**
     * @param string $sess_id
     *
     * @return string
     */
    public function _read($sess_id)
    {
        $search = $this->database->first($this->table, array('sess_id', $sess_id), true);
        if ($search) {
            return $search->data;
        } else {
            return '';
        }
    }

    /**
     * @param string $sess_id
     * @param string $data
     *
     * @return bool
     */
    public function _write($sess_id, $data)
    {
        $search = $this->database->first($this->table, array('sess_id', $sess_id), true);
        if ($search) {
            $this->database->update($this->table, array('sess_id', $sess_id), array(
                'access' => time(),
                'data' => $data,
            ));
        } else {
            $this->database->insert($this->table, array(
                'sess_id' => $sess_id,
                'access' => time(),
                'data' => $data,
                'create_date' => date('Y-m-d H:i:s'),
            ));
        }
        return true;
    }

    /**
     * @param string $sess_id
     *
     * @return bool
     */
    public function _destroy($sess_id)
    {
        $this->database->delete($this->table, array('sess_id', $sess_id));
        return true;
    }

    /**
     * @param $max
     *
     * @return bool
     */
    public function _gc($max)
    {
        $old = time() - $max;
        $this->database->delete($this->table, array('access', '<', $old));
        return true;
    }
}
