<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-10-28
 * Version      :   1.0
 */

namespace Db;

use Abstracts\Base;

class TableSchema extends Base
{
    /* @var string db-table-name（不带 引用） */
    public $name;
    /* @var string db-table-name（带引用，有前缀） */
    public $rawName;
    /* @var string|array 数据表的主键，如果为复合主键，将返回一个数组 */
    public $primaryKey;
    /* @var string 主键的序列名 */
    public $sequenceName;
    /* @var array 表列的元数据 */
    public $columns = [];
    /* @var string database 的 schema 名称 */
    public $schemaName;

    /**
     * 获取列元素
     * @param string $name
     * @return ColumnSchema metadata of the named column
     */
    public function getColumn($name)
    {
        return isset($this->columns[$name]) ? $this->columns[$name] : null;
    }

    /**
     * 返回所有列名
     * @return array
     */
    public function getColumnNames()
    {
        return array_keys($this->columns);
    }
}