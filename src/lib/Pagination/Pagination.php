<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 2018/10/30
 * Time: 下午4:44
 */

namespace Db\Pagination;

use Db\Builder\Criteria;
use Helper\Base;
use Helper\Exception;

/**
 * Class Pagination
 * @package Db\Pagination
 */
class Pagination extends Base
{
    private $_pageVar = 'page';
    /**
     * @var DbPagination
     */
    private $_dbInstance; // 数据查询实例

    /**
     * 构造函数
     * @param string|Criteria| $sqlment
     * @param array $params
     * @param \Db $db
     * @throws Exception
     */
    public function __construct($sqlment, $params = [], $db)
    {
        // Set the sql type and sqlment.
        if ($sqlment instanceof Criteria) {
            $this->_dbInstance = new CriteriaPagination ($sqlment, $params, $db);
        } elseif (is_array($sqlment)) {
            $this->_dbInstance = new CriteriaPagination (new Criteria($sqlment), $params, $db);
        } elseif (is_string($sqlment)) {
            $this->_dbInstance = new SqlPagination ($sqlment, $params, $db);
        } else {
            throw new Exception('pagination 构建参数"sqlment"无效', 101300701);
        }
    }

    /**
     * 获取分页标记
     * @return string
     */
    public function getPageVar()
    {
        return $this->_pageVar;
    }

    /**
     * 设置分页标记
     * @param string $pageVar
     * @return $this
     */
    public function setPageVar($pageVar)
    {
        $this->_pageVar = $pageVar;
        return $this;
    }

    /**
     * 获取分页查询结果
     * @param int $pageSize
     * @param null $pageNo
     * @return array
     * @throws \Exception
     */
    public function getData($pageSize = 10, $pageNo = null)
    {
        if (empty($pageNo) && isset($_GET[$this->getPageVar()])) {
            $pageNo = intval($_GET[$this->getPageVar()]);
        }
        $pageNo = empty($pageNo) ? 1 : $pageNo;
        return [
            'pageSize' => $pageSize,
            'pageNo' => $pageNo,
            'totalCount' => $this->_dbInstance->getTotalCount(),
            'result' => $this->_dbInstance->getData($pageSize, $pageNo),
        ];
    }
}

/**
 * Class DbPagination
 * @package Db\Pagination
 */
abstract class DbPagination
{
    /**
     * @var \Db
     */
    public $db;
    public $sqlment;
    public $params;

    /**
     * 构造函数
     * @param mixed $sqlment
     * @param array $params
     * @param mixed $db
     */
    public function __construct($sqlment, $params = [], \Db $db)
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

/**
 * Class CriteriaPagination
 * @package Db\Pagination
 */
class CriteriaPagination extends DbPagination
{

    /**
     * 构造函数
     * @param Criteria $sqlment
     * @param array $params
     * @param mixed $db
     */
    public function __construct($sqlment, $params = [], \Db $db)
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

/**
 * Class SqlPagination
 * @package Db\Pagination
 */
class SqlPagination extends DbPagination
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