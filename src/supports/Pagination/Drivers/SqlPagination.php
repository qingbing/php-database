<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-12-12
 * Version      :   1.0
 */

namespace DbSupports\Pagination\Drivers;


use DbSupports\Pagination\Abstracts\Pagination;

class SqlPagination extends Pagination
{
    /**
     * 获取显示数据内容
     * @param int $pageSize
     * @param int $page
     * @return array
     * @throws \Exception
     */
    public function getData($pageSize, $page)
    {
        $limit = ($page - 1) * $pageSize;
        $offset = $pageSize;
        $sql = "{$this->sqlment} LIMIT {$limit}, {$offset}";

        return $this->db->findAllBySql($sql, $this->params);
    }

    /**
     * 返回符合条件的总条数
     * @return int
     * @throws \Exception
     */
    public function getTotalCount()
    {
        return $this->db->countBySql($this->sqlment, $this->params);
    }
}