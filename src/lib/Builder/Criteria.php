<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 2018/10/29
 * Time: 下午5:59
 */

namespace Db\Builder;


class Criteria extends Builder
{
    use BuilderTable;
    use BuilderWhere;

    /**
     * 构造函数
     * @param array $query
     */
    public function __construct(array $query = [])
    {
        $this->_query = $query;
    }
}

class xxx
{

    /**
     * 单表插入设置记录数据
     * @param array $data
     * @return $this
     */
    public function setData($data)
    {
        unset($this->_query['data']);
        foreach ($data as $field => $value) {
            $this->addData($field, $value);
        }
        return $this;
    }

    /**
     * 单表插入添加数据
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    public function addData($field, $value)
    {
        if (!isset($this->_query['data'])) {
            $this->_query['data'] = [];
        }
        $this->_query['data'][$field] = $value;
        return $this;
    }

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
            $select = implode(',', $select);
        }
        if (isset($this->_query['select']) && !empty($this->_query['select'])) {
            $select = "{$this->_query['select']},{$select}";
        }
        $this->_query['select'] = $select;
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
    public function setLimit($limit = null)
    {
        if (null === $limit || $limit < 0) {
            unset($this->_query['limit']);
        } else {
            $this->_query['limit'] = $limit;
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


}