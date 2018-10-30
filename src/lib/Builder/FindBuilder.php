<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-10-29
 * Version      :   1.0
 */

namespace Db\Builder;


class FindBuilder extends SqlBuilder
{
    /**
     * 查询影响的结果集行数，对于 select ，rowCount() 返回有可能为部分结果，这里用统计更好
     * @param array $params
     * @return int
     * @throws DbException
     */
    public function queryCount($params = [])
    {
        $rs = $this->setSelect('COUNT(*) AS total')
            ->setLimit(-1)
            ->prepare($params)
            ->fetch(\PDO::FETCH_ASSOC);
        return $rs['total'];
    }

    /**
     * 查询第一条结果集
     * @param array $params
     * @return array
     * @throws DbException
     */
    public function queryRow($params = [])
    {
        return $this->setLimit(1)
            ->prepare($params)
            ->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * 查询所有结果集
     * @param array $params
     * @return array
     * @throws DbException
     */
    public function queryAll($params = [])
    {
        return $this->prepare($params)
            ->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * 创建 Sql-Command 语句
     * @return string
     */
    protected function buildSql()
    {
        // TODO: Implement buildSql() method.
    }
}


class xxx
{

    /**
     * 设置 SELECT-distinct 子句
     * @param bool $isDistinct
     * @return $this
     */
    public function setDistinct($isDistinct)
    {
        if (true === $isDistinct) {
            $this->_query['distinct'] = true;
        } else {
            $this->_query['distinct'] = false;
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
        unset($this->_query['select']);
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
                $field = $this->getDriver()->quoteColumnName($field);
                $alias = $this->getDriver()->quoteColumnName($alias);
                array_push($t, "{$field} AS {$alias}");
            }
            $select = implode(',', $t);
        }
        if (isset($this->_query['select']) && !empty($this->_query['select'])) {
            $select = "{$this->_query['select']},{$select}";
        }
        $this->_query['select'] = $select;
        return $this;
    }

    /**
     * 设置 SELECT-from 子句
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

    /**
     * 设置 SQL 语句主表别名
     * @param string $alias
     * @return $this
     */
    public function setAlias($alias = null)
    {
        if (!empty($alias)) {
            $this->_query['alias'] = $alias;
        } else {
            unset($this->_query['alias']);
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
        unset($this->_query['join']);
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
        if (!isset($this->_query['join'])) {
            $this->_query['join'] = [];
        }
        $this->_query['join'][] = $join;
        return $this;
    }

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
     * 设置 SELECT-group 子句
     * @param string $group
     * @return $this
     */
    public function setGroup($group)
    {
        if (!empty($group)) {
            $this->_query['group'] = $group;
        } else {
            unset($this->_query['group']);
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
            $this->_query['having'] = $having;
        } else {
            unset($this->_query['having']);
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
        unset($this->_query['union']);
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
        if (!isset($this->_query['union'])) {
            $this->_query['union'] = [];
        }
        $this->_query['union'][] = $union;
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
            $this->_query['order'] = $order;
        } else {
            unset($this->_query['order']);
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
            $this->_query['limit'] = $limit;
        } else {
            unset($this->_query['limit']);
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
            $this->_query['offset'] = $offset;
        } else {
            unset($this->_query['offset']);
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
        isset($query['where']) && $this->addWhere($query['where'], $operator);
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

    /**
     * 准备要执行的SQL语句
     * @param array $params
     * @return \PDOStatement
     * @throws DbException
     */
    protected function prepare($params = [])
    {
        $this->setText($this->buildSql());
        return parent::prepare($params);
    }

    /**
     * 根据 query 选项创建SQL
     * @return string
     * @throws DbException
     */
    public function buildSql()
    {
        $query = $this->getQuery();
        // DISTINCT
        if (isset($query['distinct']) && !empty($query['distinct'])) {
            $sql = 'SELECT DISTINCT';
        } else {
            $sql = 'SELECT';
        }
        // SELECT
        if (isset($query['select']) && !empty($query['select'])) {
            $sql .= " {$query['select']}";
        } else {
            $sql .= ' *';
        }
        // FROM
        if (!isset($query['table']) || empty($query['table'])) {
            throw new DbException('The DB query must contain the "table" portion.');
        }
        $sql .= " FROM " . $this->getDriver()->quoteTableName($query['table']);
        // ALIAS
        if (isset($query['alias']) && !empty($query['alias'])) {
            $sql .= " {$query['alias']}";
        } else {
            $sql .= " t";
        }
        // JOIN
        if (isset($query['join']) && !empty($query['join']))
            $sql .= ' ' . (is_array($query['join']) ? implode(" ", $query['join']) : $query['join']);
        // WHERE
        if (isset($query['where']) && !empty($query['where']))
            $sql .= " WHERE {$query['where']}";
        // GROUP
        if (isset($query['group']) && !empty($query['group']))
            $sql .= " GROUP BY {$query['group']}";
        // HAVING
        if (isset($query['having']) && !empty($query['having']))
            $sql .= " HAVING {$query['having']}";
        // UNION
        if (isset($query['union']) && !empty($query['union']))
            $sql .= " UNION (" . (is_array($query['union']) ? implode(") UNION (", $query['union']) : $query['union']) . ')';
        // ORDER
        if (isset($query['order']) && !empty($query['order']))
            $sql .= " ORDER BY {$query['order']}";
        if (isset($query['limit']) && $query['limit'] >= 0) {
            $sql .= " LIMIT {$query['limit']}";
            if (isset($query['offset']) && $query['offset'] > 0) {
                $sql .= ", {$query['offset']}";
            }
        } elseif (isset($query['offset']) && $query['offset'] > 0) {
            throw new DbException(Unit::replace('"offset" must be set in pairs with "limit"'));
        }
        return $sql;
    }

    /**
     * 查询影响的结果集行数，对于 select ，rowCount() 返回有可能为部分结果，这里用统计更好
     * @param array $params
     * @return int
     * @throws DbException
     */
    public function queryCount($params = [])
    {
        $rs = $this->setSelect('COUNT(*) AS total')
            ->setLimit(-1)
            ->prepare($params)
            ->fetch(\PDO::FETCH_ASSOC);
        return $rs['total'];
    }

    /**
     * 查询第一条结果集
     * @param array $params
     * @return array
     * @throws DbException
     */
    public function queryRow($params = [])
    {
        return $this->setLimit(1)
            ->prepare($params)
            ->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * 查询所有结果集
     * @param array $params
     * @return array
     * @throws DbException
     */
    public function queryAll($params = [])
    {
        return $this->prepare($params)
            ->fetchAll(\PDO::FETCH_ASSOC);
    }
}