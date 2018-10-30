<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-10-29
 * Version      :   1.0
 */

namespace Db\Builder;

abstract class SqlBuilder extends Builder
{
    private $_db; // \Db

    /**
     * Builder constructor.
     * @param \Db $db
     */
    public function __construct(\Db $db)
    {
        $this->_db = $db;
    }

    /**
     * 数据库连接
     * @return \Db
     */
    public function getDb()
    {
        return $this->_db;
    }

    /**
     * 创建 Sql-Command 语句
     * @return string
     */
    abstract protected function buildSql();
}