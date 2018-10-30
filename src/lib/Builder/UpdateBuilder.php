<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 2018/10/29
 * Time: 下午5:42
 */

namespace Db\Builder;

use Db\Exception;
use Db\Expression;

/**
 * Update-Sql Builder
 * Class UpdateBuilder
 * @package Db\Builder
 */
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
            ]), 100800201);
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