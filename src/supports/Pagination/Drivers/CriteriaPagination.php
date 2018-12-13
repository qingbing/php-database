<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-12-12
 * Version      :   1.0
 */

namespace DbSupports\Pagination\Drivers;


use Components\Db;
use DbSupports\Builder\Criteria;
use DbSupports\Pagination\Abstracts\Pagination;

class CriteriaPagination extends Pagination
{
    /**
     * 构造函数
     * @param Criteria $sqlment
     * @param array $params
     * @param mixed $db
     */
    public function __construct($sqlment, $params = [], Db $db)
    {
        $sqlment->addParams($params);
        parent::__construct($sqlment, [], $db);
    }

    /**
     * 获取显示数据内容
     * @param int $pageSize
     * @param int $page
     * @return array
     * @throws \Exception
     */
    public function getData($pageSize, $page)
    {
        $criteria = clone($this->sqlment);
        /* @var Criteria $criteria */
        $criteria->setLimit(($page - 1) * $pageSize);
        $criteria->setOffset($pageSize);
        return $this->db->findAll($criteria);
    }

    /**
     * 返回符合条件的总条数
     * @return int
     * @throws \Exception
     */
    public function getTotalCount()
    {
        return $this->db
            ->count($this->sqlment);
    }
}