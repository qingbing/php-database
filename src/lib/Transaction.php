<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 2018/10/30
 * Time: 下午3:12
 */

namespace Db;


use Helper\Base;

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
            throw new Exception('"Transaction"尚属未激活状态，不能执行"commit"和"rollback"操作', 100800601);
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
            throw new Exception('"Transaction"尚属未激活状态，不能执行"commit"和"rollback"操作', 100800602);
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