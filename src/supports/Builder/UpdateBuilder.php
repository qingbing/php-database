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
use DbSupports\Builder\Traits\BuilderWhere;
use DbSupports\Exception;
use DbSupports\Expression;

class UpdateBuilder extends SqlBuilder
{
    use BuilderTable;
    use BuilderColumns;
    use BuilderWhere;

    /**
     * 执行SQL更新，返回影响的数据条数
     * @param array $params
     * @return int
     * @throws \Exception
     */
    public function execute($params = [])
    {
        return $this->getDb()
            ->createCommand()
            ->setText($this->buildSql())
            ->execute(array_merge($this->getParams(), $params));
    }

    /**
     * 创建单表操作的 update 语句
     * @return string
     * @throws Exception
     */
    protected function buildSql()
    {
        $query = $this->getQuery();
        if (
            !isset($query['table']) || empty($query['table'])
            || !isset($query['columns']) || empty($query['columns'])
            || !isset($query['where']) || empty($query['where'])
        ) {
            throw new Exception(str_cover('"{type}"查询参数不完整', [
                '{type}' => 'update'
            ]), 101300201);
        }
        $setV = $VS = [];
        foreach ($query['columns'] as $k => $v) {
            if ($v instanceof Expression) {
                $setV[] = $this->quoteColumnName($k) . '=' . $v->expression;
                continue;
            }
            $VS[] = $bk = $this->getBindKey();
            $this->addParam($bk, $v);
            $setV[] = $this->quoteColumnName($k) . '=' . $bk;
        }
        $sql = 'UPDATE ' . $this->quoteTableName($query['table']) . ' SET ' . implode(',', $setV)
            . ' WHERE ' . $query['where'];

        return $sql;
    }
}