<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-12-12
 * Version      :   1.0
 */

namespace DbSupports\Builder\Traits;


use DbSupports\Builder\Criteria;
use DbSupports\Expression;

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
                $t[] = $this->quoteColumnName($k) . '=' . $v->expression;
                continue;
            }
            $bk = $this->getBindKey();
            $params[$bk] = $v;
            $t[] = $this->quoteColumnName($k) . '=' . $bk;
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
        if ('' === trim($keyword) || NULL === $keyword) {
            return $this;
        }
        if ($escape) {
            $keyword = '%' . strtr($keyword, ['%' => '\%', '_' => '\_', '\\' => '\\\\']) . '%';
        }
        $bk = $this->getBindKey();
        $condition = $this->quoteColumnName($column)
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
                $condition = $this->quoteColumnName($column) . ($isIn ? ' IS NULL' : ' IS NOT NULL');
            } else {
                if ($value instanceof Expression) {
                    $condition = $this->quoteColumnName($column) . ($isIn ? '=' : '!=') . $value->expression;
                } else {
                    $bk = $this->getBindKey();
                    $this->addParam($bk, $value);
                    $condition = $this->quoteColumnName($column) . ($isIn ? '=' : '!=') . $bk;
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
            $condition = $this->quoteColumnName($column) . ($isIn ? ' IN ' : ' NOT IN ') . '(' . implode(', ', $params) . ')';
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

        $condition = $this->quoteColumnName($column) . " BETWEEN $startKey AND $endKey";
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