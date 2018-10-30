<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 2018/10/29
 * Time: 下午3:33
 */

namespace Db\Builder;

use Db\Expression;
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

    static private $_paramCount = 0; // 参数绑定个数

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
    public function getQuery()
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
     * 设置 where 子句
     * @param array $attributes
     * @param string $operator
     * @return $this
     */
    public function addWhereByAttributes($attributes, $operator = 'AND')
    {
        $t = [];
        foreach ($attributes as $k => $v) {
            if ($v instanceof Expression) {
                $t[] = $k . '=' . $v->expression;
                continue;
            }
            $bk = $this->getBindKey();
            $this->addParam($bk, $v);
            $t[] = $k . '=' . $bk;
        }
        return $this->addWhere(implode(' AND ', $t), $operator);
    }

    /**
     * 添加 Where Like 条件
     * @param string $column
     * @param string $keyword
     * @param bool|true $escape
     * @param string $operator
     * @param bool|true $isLike
     * @return $this
     */
    public function addWhereLike($column, $keyword, $escape = true, $operator = 'AND', $isLike = true)
    {
        if (empty($keyword)) {
            return $this;
        }
        if ($escape) {
            $keyword = '%' . strtr($keyword, ['%' => '\%', '_' => '\_', '\\' => '\\\\']) . '%';
        }
        $bk = $this->getBindKey();
        $this->addParam($bk, $keyword);
        $condition = $column
            . ($isLike ? " LIKE " : " NOT LIKE ")
            . $bk;
        return $this->addWhere($condition, $operator);
    }

    /**
     * 添加 Where Not Like 条件
     * @param string $column
     * @param string $keyword
     * @param bool|true $escape
     * @param string $operator
     * @return $this
     */
    public function addWhereNotLike($column, $keyword, $escape = true, $operator = 'AND')
    {
        return $this->addWhereLike($column, $keyword, $escape, $operator, false);
    }

    /**
     * 添加 Where In 条件
     * @param string $column
     * @param array $values
     * @param string $operator
     * @param bool $isIn
     * @return $this
     */
    public function addWhereIn($column, $values, $operator = 'AND', $isIn = true)
    {
        if (($n = count($values)) < 1) {
            $condition = $isIn ? '0=1' : '1=1'; // 0=1 is used because in MSSQL value alone can't be used in WHERE
        } else if (1 === $n) {
            $value = reset($values);
            if (null === $value) {
                $condition = $column . ($isIn ? ' IS NULL' : ' IS NOT NULL');
            } else {
                if ($value instanceof Expression) {
                    $condition = $column . ($isIn ? '=' : '!=') . $value->expression;
                } else {
                    $bk = $this->getBindKey();
                    $this->addParam($bk, $value);
                    $condition = $column . ($isIn ? '=' : '!=') . $bk;
                }
            }
        } else {
            $params = [];
            foreach ($values as $value) {
                if ($value instanceof Expression) {
                    $params[] = $value->expression;
                    continue;
                }
                $params[] = $bk = $this->getBindKey();
                $this->addParam($bk, $value);
            }
            $condition = $column . ($isIn ? ' IN ' : ' NOT IN ') . '(' . implode(', ', $params) . ')';
        }
        return $this->addWhere($condition, $operator);
    }

    /**
     * 添加 Where In 条件
     * @param string $column
     * @param array $values
     * @param string $operator
     * @return $this
     */
    public function addWhereNotIn($column, $values, $operator = 'AND')
    {
        return $this->addWhereIn($column, $values, $operator, false);
    }

    /**
     * 添加 Where Between 条件
     * @param string $column
     * @param mixed $startVar
     * @param mixed $endVar
     * @param string $operator
     * @return $this
     */
    public function addWhereBetween($column, $startVar, $endVar, $operator = 'AND')
    {
        if ('' === $startVar || '' === $endVar) {
            return $this;
        }
        if ($startVar instanceof Expression) {
            $startKey = $startVar->expression;
        } else {
            $startKey = $this->getBindKey();
            $this->addParam($startKey, $startVar);
        }
        if ($endVar instanceof Expression) {
            $endKey = $endVar->expression;
        } else {
            $endKey = $this->getBindKey();
            $this->addParam($endKey, $endVar);
        }

        $condition = $column . " BETWEEN $startKey AND $endKey";
        return $this->addWhere($condition, $operator);
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