<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-10-29
 * Version      :   1.0
 */

namespace Db\Builder;


use Db\Exception;

class FindBuilder extends SqlBuilder
{
    use BuilderTable;
    use BuilderFind, BuilderWhere {
        BuilderFind::addCriteria insteadof BuilderWhere;
    }

    /**
     * 查询影响的结果集行数，对于 select ，rowCount() 返回有可能为部分结果，这里用统计更好
     * @param array $params
     * @return int
     * @throws \Exception
     */
    public function count($params = [])
    {
        $this->setSelect('COUNT(*) AS ' . $this->quoteColumnName('total'))
            ->setLimit(-1);
        $record = $this->getDb()
            ->createCommand()
            ->setText($this->buildSql())
            ->queryRow(array_merge($this->getParams(), $params));
        return $record['total'];
    }

    /**
     * 查询第一条结果集
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function queryRow($params = [])
    {
        $this->setLimit(1);
        return $this->getDb()
            ->createCommand()
            ->setText($this->buildSql())
            ->queryRow(array_merge($this->getParams(), $params));
    }

    /**
     * 查询所有结果集
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function queryAll($params = [])
    {
        return $this->getDb()
            ->createCommand()
            ->setText($this->buildSql())
            ->queryAll(array_merge($this->getParams(), $params));
    }

    /**
     * 根据 query 选项创建SQL
     * @return string
     * @throws Exception
     */
    protected function buildSql()
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
            throw new Exception('Find查询必须带有"table"参数', 100800501);
        }
        $sql .= " FROM " . $this->quoteTableName($query['table']);
        // ALIAS
        $sql .= " AS " . $this->quoteColumnName((isset($query['alias']) && !empty($query['alias'])) ? $query['alias'] : 't');
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
            throw new Exception('"offset"必须和"limit"配对出现', 100800502);
        }
        return $sql;
    }
}