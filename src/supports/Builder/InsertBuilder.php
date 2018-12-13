<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-12-12
 * Version      :   1.0
 */

namespace DbSupports\Builder;


use DbSupports\Builder\Abstracts\SqlBuilder;
use DbSupports\Builder\Traits\BuilderColumns;
use DbSupports\Builder\Traits\BuilderTable;
use DbSupports\Exception;
use DbSupports\Expression;

class InsertBuilder extends SqlBuilder
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
            unset($this->query['multi-fields']);
        } else {
            $this->query['multi-fields'] = $fields;
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
            unset($this->query['multi-data']);
        } else {
            $this->query['multi-data'] = $data;
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
        if (!isset($this->query['multi-data'])) {
            $this->query['multi-data'] = [];
        }
        $this->query['multi-data'][] = $data;
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
        if (isset($this->query['columns'])) {
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
            throw new Exception(str_cover('"{type}"查询参数不完整', [
                '{type}' => 'insert'
            ]), 101300301);
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
            throw new Exception(str_cover('"{type}"查询参数不完整', [
                '{type}' => 'multi-insert'
            ]), 101300302);
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