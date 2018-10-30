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
    use BuilderColumns;
    use BuilderTable;
    use BuilderFind, BuilderWhere {
        BuilderFind::addCriteria insteadof BuilderWhere;
    }

    /**
     * 构造函数
     * @param array $query
     */
    public function __construct($query = [])
    {
        if ($query instanceof Criteria) {
            $this->query = $query->getQuery();
            $this->addParams($query->getParams());
        } else if (is_array($query)) {
            $this->query = $query;
        }
    }
}