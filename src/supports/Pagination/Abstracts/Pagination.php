<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-12-12
 * Version      :   1.0
 */

namespace DbSupports\Pagination\Abstracts;


use Components\Db;

abstract class Pagination
{
    /* @var Db */
    public $db;
    public $sqlment;
    public $params;

    /**
     * 构造函数
     * @param mixed $sqlment
     * @param array $params
     * @param mixed $db
     */
    public function __construct($sqlment, $params = [], Db $db)
    {
        $this->db = $db;
        $this->sqlment = $sqlment;
        $this->params = $params;
    }

    /**
     * 获取显示数据内容
     * @param int $pageSize
     * @param int $page
     * @return array
     */
    abstract public function getData($pageSize, $page);

    /**
     * 返回符合条件的总条数
     * @return int
     */
    abstract public function getTotalCount();
}