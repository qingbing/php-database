<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 2018/10/29
 * Time: 下午4:15
 */

namespace Db\Builder;


use Db\Exception;
use Db\Expression;

/**
 * Insert-Sql Builder
 * Class InsertBuilder
 * @package Db\Builder
 */
class InsertBuilder extends Builder
{
    use BuilderTable;
    use BuilderColumns;

    /**
     * 设置批量插入的字段名
     * @param array $fields
     * @return $this
     */
    public function setMultiFields(array $fields = [])
    {
        if (empty($fields)) {
            unset($this->_query['multi-fields']);
        } else {
            $this->_query['multi-fields'] = $fields;
        }
        return $this;
    }

    /**
     * 设置批量插入的数据，需要和 multi-field 对应
     * @param array $data
     * @return $this
     */
    public function setMultiData(array $data = [])
    {
        if (empty($data)) {
            unset($this->_query['multi-data']);
        } else {
            $this->_query['multi-data'] = $data;
        }
        return $this;
    }

    /**
     * 添加批量插入的数据，需要和 multi-field 对应
     * @param array $data
     * @return $this
     */
    public function addMultiData(array $data = [])
    {
        if (!isset($this->_query['multi-data'])) {
            $this->_query['multi-data'] = [];
        }
        $this->_query['multi-data'][] = $data;
        return $this;
    }

    /**
     * 执行SQL插入
     * @param array $params
     * @return int
     * @throws \Exception
     */
    public function execute($params = [])
    {
        if (isset($this->_query['columns'])) {
            $sql = $this->buildSql();
        } else {
            $sql = $this->buildMultiSql();
        }

        return $this->getDb()
            ->createCommand()
            ->setText($sql)
            ->execute(array_merge($this->getParams(), $params));
    }

    /**
     * 创建单表操作的 insert 语句（单条）
     * @return string
     * @throws Exception
     */
    protected function buildSql()
    {
        $query = $this->getQuery();
        if (
            !isset($query['table']) || empty($query['table'])
            || !isset($query['columns']) || empty($query['columns'])
        ) {
            throw new Exception(str_cover('Query-{type} parameters are incomplete.', [
                '{type}' => 'insert'
            ]));
        }
        $KS = $VS = [];
        foreach ($query['columns'] as $k => $v) {
            if ($v instanceof Expression) {
                $KS[] = $this->quoteColumnName($k);
                $VS[] = $v->expression;
                continue;
            }
            $KS[] = $this->quoteColumnName($k);
            $VS[] = $bk = $this->getBindKey();
            $this->addParam($bk, $v);
        }
        $sql = 'INSERT INTO '
            . $this->quoteTableName($query['table'])
            . ' (' . implode(',', $KS) . ') VALUES (' . implode(',', $VS) . ')';
        return $sql;
    }

    /**
     * 创建单表操作的 insert 语句(批量)
     * @return string
     * @throws Exception
     */
    protected function buildMultiSql()
    {
        $query = $this->getQuery();
        if (
            !isset($query['table']) || empty($query['table'])
            || !isset($query['multi-fields']) || empty($query['multi-fields'])
            || !isset($query['multi-data']) || empty($query['multi-data'])
        ) {
            throw new Exception(str_cover('Query-{type} parameters are incomplete.', [
                '{type}' => 'multi-insert'
            ]));
        }
        $KS = [];
        foreach ($query['multi-fields'] as $k) {
            $KS[] = $this->quoteColumnName($k);
        }
        $_VS = [];
        foreach ($query['multi-data'] as $data) {
            $bks = [];
            foreach ($data as $v) {
                if ($v instanceof Expression) {
                    $bks[] = $v->expression;
                    continue;
                }
                $bks[] = $bk = $this->getBindKey();
                $this->addParam($bk, $v);
            }
            $_VS[] = '(' . implode(',', $bks) . ')';
        }
        $sql = 'INSERT INTO '
            . $this->quoteTableName($query['table'])
            . ' (' . implode(',', $KS) . ') VALUES ' . implode(',', $_VS);
        return $sql;
    }
}