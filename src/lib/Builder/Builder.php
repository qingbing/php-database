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
    protected $query = [];
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
        } else {
            $prefix = '';
        }
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
        if (false === strpos($name, '.')) {
            return $this->quoteSimpleTableName($name);
        }
        $parts = explode('.', $name);
        foreach ($parts as $i => $part) {
            $parts[$i] = $this->quoteSimpleTableName($part);
        }
        return implode('.', $parts);
    }

    /**
     * 返回构造SQL语句的选项
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
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
            $this->query['table'] = $table;
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
        unset($this->query['columns']);
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
        if (!isset($this->query['columns'])) {
            $this->query['columns'] = [];
        }
        $this->query['columns'][$field] = $value;
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
     * @param array $params
     * @return $this
     */
    public function setWhere($where, $params = [])
    {
        unset($this->query['where']);
        return $this->addWhere($where, $params);
    }

    /**
     * 添加 SELECT-where 内容
     * @param string $where
     * @param array $params
     * @param string $operator
     * @return $this
     */
    public function addWhere($where, $params = [], $operator = 'AND')
    {
        if ($where instanceof Criteria) {
            return $this->addCriteria($where);
        }
        if (null === $where) {
            return $this;
        }
        if (isset($this->query['where']) && !empty($this->query['where'])) {
            $operator = strtoupper($operator);
            $where = "({$this->query['where']}) {$operator} ({$where})";
        }
        $this->query['where'] = $where;
        $this->addParams($params);
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
        $params = [];
        foreach ($attributes as $k => $v) {
            if ($v instanceof Expression) {
                $t[] = $k . '=' . $v->expression;
                continue;
            }
            $bk = $this->getBindKey();
            $params[$bk] = $v;
            $t[] = $k . '=' . $bk;
        }
        return $this->addWhere(implode(' AND ', $t), $params, $operator);
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
        $condition = $column
            . ($isLike ? " LIKE " : " NOT LIKE ")
            . $bk;
        return $this->addWhere($condition, [
            $bk => $keyword,
        ], $operator);
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
        return $this->addWhere($condition, [], $operator);
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
        $params = [];
        if ($startVar instanceof Expression) {
            $startKey = $startVar->expression;
        } else {
            $startKey = $this->getBindKey();
            $params[$startKey] = $startVar;
        }
        if ($endVar instanceof Expression) {
            $endKey = $endVar->expression;
        } else {
            $endKey = $this->getBindKey();
            $params[$endKey] = $endVar;
        }

        $condition = $column . " BETWEEN $startKey AND $endKey";
        return $this->addWhere($condition, $params, $operator);
    }

    /**
     * 添加 Criteria-Where
     * @param Criteria $criteria | null
     * @param string $operator
     * @return $this
     */
    public function addCriteria($criteria = null, $operator = 'AND')
    {
        if ($criteria instanceof Criteria) {
            $query = $criteria->getQuery();
            // 添加 WHERE 条件
            isset($query['where']) && $this->addWhere($query['where'], $criteria->getParams(), $operator);
        }
        return $this;
    }
}

/**
 * Trait BuilderFind
 * @package Db\Builder
 */
trait BuilderFind
{
    /**
     * 设置 SELECT-distinct 子句
     * @param bool $isDistinct
     * @return $this
     */
    public function setDistinct($isDistinct)
    {
        if (true === $isDistinct) {
            $this->query['distinct'] = true;
        } else {
            $this->query['distinct'] = false;
        }
        return $this;
    }

    /**
     * 设置 SELECT-select 子句
     * @param mixed $select
     * @return $this
     */
    public function setSelect($select)
    {
        unset($this->query['select']);
        return $this->addSelect($select);
    }

    /**
     * 添加 SELECT-select 内容
     * @param mixed $select
     * @return $this
     */
    public function addSelect($select)
    {
        if (null === $select) {
            return $this;
        }
        if (is_array($select)) {
            $t = [];
            foreach ($select as $field => $alias) {
                $field = $this->quoteColumnName($field);
                $alias = $this->quoteColumnName($alias);
                array_push($t, "{$field} AS {$alias}");
            }
            $select = implode(',', $t);
        }
        if (isset($this->query['select']) && !empty($this->query['select'])) {
            $select = "{$this->query['select']},{$select}";
        }
        $this->query['select'] = $select;
        return $this;
    }

    /**
     * 设置 SQL 语句主表别名
     * @param string $alias
     * @return $this
     */
    public function setAlias($alias = null)
    {
        if (!empty($alias)) {
            $this->query['alias'] = $alias;
        } else {
            unset($this->query['alias']);
        }
        return $this;
    }

    /**
     * 设置 SELECT-join 子句
     * @param string $join
     * @return $this
     */
    public function setJoin($join)
    {
        unset($this->query['join']);
        return $this->addJoin($join);
    }

    /**
     * 添加 SELECT-join 内容
     * @param string $join
     * @return $this
     */
    public function addJoin($join)
    {
        if (null === $join)
            return $this;
        if (!isset($this->query['join'])) {
            $this->query['join'] = [];
        }
        $this->query['join'][] = $join;
        return $this;
    }

    /**
     * 设置 SELECT-group 子句
     * @param string $group
     * @return $this
     */
    public function setGroup($group)
    {
        if (!empty($group)) {
            $this->query['group'] = $group;
        } else {
            unset($this->query['group']);
        }
        return $this;
    }

    /**
     * 设置 SELECT-having 子句
     * @param string $having
     * @return $this
     */
    public function setHaving($having)
    {
        if (!empty($having)) {
            $this->query['having'] = $having;
        } else {
            unset($this->query['having']);
        }
        return $this;
    }

    /**
     * 设置 SELECT-union 子句
     * @param string $union
     * @return $this
     */
    public function setUnion($union)
    {
        unset($this->query['union']);
        return $this->addUnion($union);
    }

    /**
     * 添加 SELECT-union 内容
     * @param string $union
     * @return $this
     */
    public function addUnion($union)
    {
        if (null === $union)
            return $this;
        if (!isset($this->query['union'])) {
            $this->query['union'] = [];
        }
        $this->query['union'][] = $union;
        return $this;
    }

    /**
     * 设置 SELECT-order 子句
     * @param string $order
     * @return $this
     */
    public function setOrder($order)
    {
        if (!empty($order)) {
            $this->query['order'] = $order;
        } else {
            unset($this->query['order']);
        }
        return $this;
    }

    /**
     * 设置 SELECT-limit 子句
     * @param int $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        if ($limit >= 0) {
            $this->query['limit'] = $limit;
        } else {
            unset($this->query['limit']);
        }
        return $this;
    }

    /**
     * 设置 SELECT-offset 子句
     * @param int $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        if ($offset > 0) {
            $this->query['offset'] = $offset;
        } else {
            unset($this->query['offset']);
        }
        return $this;
    }

    /**
     * 添加SQL标准
     * @param Criteria $criteria
     * @param string $operator
     * @return $this
     */
    public function addCriteria($criteria, $operator = 'AND')
    {
        if (!$criteria instanceof Criteria) {
            return $this;
        }
        $query = $criteria->getQuery();
        // distinct
        isset($query['distinct']) && $this->setDistinct($query['distinct']);
        // select
        isset($query['select']) && !empty($query['select']) && $this->addSelect($query['select']);
        // table
        isset($query['table']) && !empty($query['table']) && $this->setTable($query['table']);
        // alias
        isset($query['alias']) && !empty($query['alias']) && $this->setAlias($query['alias']);
        // join
        if (isset($query['join']) && !empty($query['join'])) {
            foreach ($query['join'] as $join) {
                $this->addJoin($join);
            }
        }
        // where
        // 添加 WHERE 条件
        isset($query['where']) && $this->addWhere($query['where'], [], $operator);
        // group
        isset($query['group']) && !empty($query['group']) && $this->setGroup($query['group']);
        // having
        isset($query['having']) && !empty($query['having']) && $this->setHaving($query['having']);
        // union
        if (isset($query['union']) && !empty($query['union'])) {
            foreach ($query['union'] as $union) {
                $this->addUnion($union);
            }
        }
        // order
        isset($query['order']) && !empty($query['order']) && $this->setOrder($query['order']);
        // limit
        isset($query['limit']) && $this->setLimit($query['limit']);
        // offset
        isset($query['offset']) && $this->setOffset($query['offset']);
        // bind params
        $this->addParams($criteria->getParams());

        return $this;
    }
}