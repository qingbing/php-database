<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-12-12
 * Version      :   1.0
 */

namespace DbSupports\Builder;


use DbSupports\Builder\Abstracts\SqlBuilder;
use DbSupports\Builder\Traits\BuilderTable;
use DbSupports\Builder\Traits\BuilderWhere;
use DbSupports\Exception;

class DeleteBuilder extends SqlBuilder
{
    use BuilderTable;
    use BuilderWhere;

    /**
     * 执行SQL删除，返回成功删除的数据条数
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
     * 创建单表操作的 delete 语句
     * @return string
     * @throws Exception
     */
    protected function buildSql()
    {
        $query = $this->getQuery();
        if (
            !isset($query['table']) || empty($query['table'])
            || !isset($query['where']) || empty($query['where'])
        ) {
            throw new Exception(str_cover('"{type}"查询参数不完整', [
                '{type}' => 'delete'
            ]), 101300401);
        }
        return 'DELETE FROM ' . $this->quoteTableName($query['table']) . ' WHERE ' . $query['where'];
    }
}