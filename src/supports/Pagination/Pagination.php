<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-12-12
 * Version      :   1.0
 */

namespace DbSupports\Pagination;


use Abstracts\Base;
use DbSupports\Builder\Criteria;
use DbSupports\Pagination\Drivers\CriteriaPagination;
use DbSupports\Pagination\Drivers\SqlPagination;
use Helper\Exception;

class Pagination extends Base
{
    private $_pageVar = 'page';
    /**
     * @var \Components\Db
     */
    private $_dbInstance; // 数据查询实例

    /**
     * 构造函数
     * @param string|Criteria| $sqlment
     * @param array $params
     * @param \Components\Db $db
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
     * @param null|int $pageNo
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