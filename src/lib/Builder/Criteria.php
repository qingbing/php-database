<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-10-28
 * Version      :   1.0
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