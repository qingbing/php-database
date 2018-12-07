<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-10-28
 * Version      :   1.0
 */

namespace Db;

use Abstracts\Base;

class Transaction extends Base
{
    private $_db = null;
    private $_active;

    /**
     * 构造函数
     * @param \Db $db
     */
    public function __construct(\Db $db)
    {
        $this->_db = $db;
        $this->_active = true;
    }

    /**
     * 提交事务
     * @throws Exception
     */
    public function commit()
    {
        if ($this->_active && $this->_db->getActive()) {
            $this->_db->getPdoInstance()->commit();
            $this->_active = false;
        } else {
            throw new Exception('"Transaction"尚属未激活状态，不能执行"commit"和"rollback"操作', 101300601);
        }
    }

    /**
     * 事务回滚
     * @throws Exception
     */
    public function rollback()
    {
        if ($this->_active && $this->_db->getActive()) {
            $this->_db->getPdoInstance()->rollBack();
            $this->_active = false;
        } else {
            throw new Exception('"Transaction"尚属未激活状态，不能执行"commit"和"rollback"操作', 101300602);
        }
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->_active;
    }

    /**
     * @param bool $value
     */
    protected function setActive($value)
    {
        $this->_active = $value;
    }
}