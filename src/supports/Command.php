<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-12-12
 * Version      :   1.0
 */

namespace DbSupports;


use Abstracts\Base;
use Components\Db;
use Helper\Timer;

class Command extends Base
{
    /* @var Db */
    private $_db;
    /* @var \PDOStatement */
    private $_statement;
    /* @var string SQL语句 */
    private $_text;

    private static $_sqlCount = 0;

    /**
     * 构造函数
     * Command constructor.
     * @param Db $db
     */
    public function __construct(Db $db)
    {
        $this->_db = $db;
    }

    /**
     * 返回当前使用驱动器
     * @return Db
     */
    public function getDb()
    {
        return $this->_db;
    }

    /**
     * 取消原有的查询结果
     */
    public function cancel()
    {
        $this->_statement = null;
    }

    /**
     * 指定需要执行的 SQL 语句
     * @param string $value
     * @return $this
     */
    public function setText($value)
    {
        if (null !== ($tablePrefix = $this->getDb()->tablePrefix) && '' !== $value) {
            $this->_text = preg_replace('/{{(.*?)}}/', $tablePrefix . '\1', $value);
        } else {
            $this->_text = $value;
        }
        $this->cancel();
        return $this;
    }

    /**
     * 返回需要执行的 SQL 语句
     * @return string
     */
    public function getText()
    {
        return $this->_text;
    }

    /**
     * 准备要执行的SQL语句
     * @param array $params
     * @return \PDOStatement
     * @throws \Exception
     */
    protected function prepare($params = [])
    {
        if (null === $this->_statement) {
            $sqlCount = 'Db-Query:' . self::$_sqlCount++;
            try {
                $text = $this->getText();
                Timer::begin($sqlCount); // 记录DB开始时间
                $this->_statement = $this->getDb()
                    ->getPdoInstance()
                    ->prepare($text);
                if (empty($params)) {
                    $this->_statement->execute();
                } else {
                    $this->_statement->execute($params);
                }
                // 文件日志记录
                $this->getDb()->pushLog($sqlCount, [
                    'Sql' => $text,
                    'Params' => $params,
                ]);
            } catch (\Exception $e) {
                // 文件日志记录
                $this->getDb()->pushErrorLog($sqlCount, [
                    'Sql' => $this->getText(),
                    'Params' => $params,
                    'Error' => $e->getMessage(),
                    'Code' => $e->getMessage(),
                ]);
            }
        }
        return $this->_statement;
    }

    /**
     * 返回最后一条 insert 语句的自增ID
     * @return string
     */
    public function getLastInsertId()
    {
        return $this->getDb()
            ->getPdoInstance()
            ->lastInsertId();
    }

    /**
     * 执行所查询的SQL并返回影响的行数，主要针对 insert、update、delete返回正确条数
     * @param array $params
     * @return int
     * @throws \Exception
     */
    public function execute($params = [])
    {
        return $this->prepare($params)
            ->rowCount();
    }

    /**
     * 查询影响的结果集行数，对于 select ，rowCount() 返回有可能为部分结果
     * @param array $params
     * @return int
     * @throws \Exception
     */
    public function count($params = [])
    {
        return $this->prepare($params)
            ->rowCount();
    }

    /**
     * 查询第一条结果集
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function queryRow($params = [])
    {
        return $this->prepare($params)
            ->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * 查询所有结果集
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function queryAll($params = [])
    {
        return $this->prepare($params)
            ->fetchAll(\PDO::FETCH_ASSOC);
    }
}