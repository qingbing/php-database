<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 2018/10/29
 * Time: 下午3:33
 */

namespace Db\Builder;

use Helper\Base;

/**
 * SQL 构建器
 * Class Builder
 * @package Db\Builder
 */
abstract class Builder extends Base
{
    /**
     * build sql 需要的选项
     * @var array
     */
    protected $_query = [];
    private $_params = []; // SQL绑定参数

    private $_db; // \Db

    static private $_paramCount = 0; // 参数绑定个数

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
     * 引号包裹字段名称
     * @param string $name
     * @return string
     */
    public function quoteSimpleColumnName($name)
    {
        return '`' . $name . '`';
    }

    /**
     * 引号包裹字段名称，带表名
     * @param string $name
     * @return string
     */
    public function quoteColumnName($name)
    {
        if (false !== ($pos = strrpos($name, '.'))) {
            $prefix = $this->quoteTableName(substr($name, 0, $pos)) . '.';
            $name = substr($name, $pos + 1);
        } else
            $prefix = '';
        return $prefix . ($name === '*' ? $name : $this->quoteSimpleColumnName($name));
    }

    /**
     * 引号包裹表名称
     * @param string $name
     * @return string
     */
    public function quoteSimpleTableName($name)
    {
        return '`' . $name . '`';
    }

    /**
     * 引号包裹表名称，带库名
     * @param string $name
     * @return string
     */
    public function quoteTableName($name)
    {
        if (false === strpos($name, '.'))
            return $this->quoteSimpleTableName($name);
        $parts = explode('.', $name);
        foreach ($parts as $i => $part)
            $parts[$i] = $this->quoteSimpleTableName($part);
        return implode('.', $parts);
    }

    /**
     * 返回构造SQL语句的选项
     * @return array
     */
    protected function getQuery()
    {
        return $this->_query;
    }

    /**
     * 获取绑定值
     * @return string
     */
    protected function getBindKey()
    {
        return ':pf_' . self::$_paramCount++;
    }

    /**
     * 获取绑定的SQL参数
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * 绑定SQL参数
     * @param mixed $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->_params = $params;
        return $this;
    }

    /**
     * 添加绑定参数
     * @param string $bk
     * @param string $bv
     * @return $this
     */
    public function addParam($bk, $bv)
    {
        $this->_params[$bk] = $bv;
        return $this;
    }

    /**
     * 添加绑定参数
     * @param array $params
     * @return $this
     */
    public function addParams(array $params)
    {
        foreach ($params as $k => $v) {
            $this->addParam($k, $v);
        }
        return $this;
    }

    /**
     * 创建 Sql-Command 语句
     * @return string
     */
    abstract protected function buildSql();
}

/**
 * Trait BuilderTable
 * @package Db\Builder
 */
trait BuilderTable
{
    /**
     * 设置操作表
     * @param string $table
     * @return $this
     */
    public function setTable($table)
    {
        if (!empty($table)) {
            $this->_query['table'] = $table;
        }
        return $this;
    }
}

/**
 * Trait BuilderColumns
 * @package Db\Builder
 */
trait BuilderColumns
{
    /**
     * 单表插入设置记录数据
     * @param array $data
     * @return $this
     */
    public function setColumns($data)
    {
        unset($this->_query['columns']);
        foreach ($data as $field => $value) {
            $this->addColumn($field, $value);
        }
        return $this;
    }

    /**
     * 单表插入添加数据
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    public function addColumn($field, $value)
    {
        if (!isset($this->_query['columns'])) {
            $this->_query['columns'] = [];
        }
        $this->_query['columns'][$field] = $value;
        return $this;
    }
}

/**
 * Trait BuilderWhere
 * @package Db\Builder
 */
trait BuilderWhere
{
    /**
     * 设置 SELECT-where 子句
     * @param string $where
     * @return $this
     */
    public function setWhere($where)
    {
        unset($this->_query['where']);
        return $this->addWhere($where);
    }

    /**
     * 添加 SELECT-where 内容
     * @param string $where
     * @param string $operator
     * @return $this
     */
    public function addWhere($where, $operator = 'AND')
    {
        if ($where instanceof Criteria) {
            return $this->addCriteria($where);
        }
        if (null === $where) {
            return $this;
        }
        if (isset($this->_query['where']) && !empty($this->_query['where'])) {
            $operator = strtoupper($operator);
            $where = "({$this->_query['where']}) {$operator} ({$where})";
        }
        $this->_query['where'] = $where;
        return $this;
    }

    /**
     * 添加SQL标准
     * @param Criteria $criteria
     * @param string $operator
     * @return $this
     * todo
     */
    public function addCriteria(Criteria $criteria, $operator = 'AND')
    {
        $query = $criteria->getQuery();
        // 绑定操作表
        isset($query['table']) && !empty($query['table']) && $this->setTable($query['table']);
        // 添加更新字段
        if (isset($query['columns']) && !empty($query['columns'])) {
            foreach ($query['columns'] as $field => $value) {
                $this->addColumn($field, $value);
            }
        }
        // 添加 WHERE 条件
        isset($query['where']) && $this->addWhere($query['where'], $operator);
        $this->addParams($criteria->getParams());
        return $this;
    }
}