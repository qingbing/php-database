<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-10-27
 * Version      :   1.0
 */

use Db\Builder\DeleteBuilder;
use Db\Builder\FindBuilder;
use Db\Builder\InsertBuilder;
use Db\Builder\UpdateBuilder;
use Db\Command;
use Db\Transaction;
use Helper\Timer;

defined('PHP_DEBUG') or define('PHP_DEBUG', false);

/**
 * Class Db
 */
class Db extends \Helper\Base
{
    private static $_instances = [];

    /**
     * 获取Db连接实例
     * @param string $type
     * @return $this
     * @throws Exception
     */
    public static function getInstance($type = 'master')
    {
        if (!isset(self::$_instances[$type])) {
            $config = Config::getInstance('database', 'master')->params('database.mysql');
            return self::$_instances[$type] = new self($config);
        }
        return self::$_instances[$type];
    }

    public $dsn; // 数据库链接串 eg：'mysql:dbname=mydatabase;host=127.0.0.1;charset=utf8;'
    public $username = ''; // 数据库连接用户
    public $password = ''; // 数据库连接密码
    public $autoConnect = true; // 数据库是否自动连接
    public $pdoClass = '\PDO'; // 链接数据库使用的类名，默认：\PDO
    public $logFile = false; // 是否记录在文件log上
    /**
     * 数据库连接使用编码
     * 该属性只在 MySql 和 PostgreSQL 中有效
     * PHP 5.3.6+, 'mysql:dbname=mydatabase;host=127.0.0.1;charset=utf8;'
     * @var string
     */
    public $charset = 'utf8';
    /**
     * 数据表前缀
     * 当查询语句中使用 "{{tableName}}" 时，将使用该前缀作为数据表前缀
     * @var string
     */
    public $tablePrefix = 'cf_';
    /**
     * 启用或禁用预处理语句的模拟(数据查询参数是否在本地进行转义，该属性在 PHP 5.1.3+ 有效)
     * PDO 默认为 true（在本地进行），设为 false 时转义将在 db 中进行
     * @var bool
     */
    public $emulatePrepare;

    /**
     * @var \PDO PDO链接数据库
     */
    private $_pdo;
    private $_active = false; // 连接是否被激活
    private $_attributes = []; // PDO连接属性
    /**
     * DB 当前事务处理
     * @var Transaction
     */
    private $_transaction;

    /**
     * Db constructor.
     * @param array $config
     * @throws Exception
     */
    public function __construct(array $config = [])
    {
        $this->configure($config);
        $this->init();
    }

    /**
     * 构造函数之后的初始化
     * @throws Exception
     */
    public function init()
    {
        if ($this->autoConnect) {
            $this->setActive(true);
        }
    }

    /**
     * 开启或关闭 db 链接
     * @param bool $value
     * @throws Exception
     */
    public function setActive($value)
    {
        if ($value != $this->_active) {
            if ($value) {
                $this->open();
            } else {
                $this->close();
            }
        }
    }

    /**
     * SQL 日志推送
     * @param $message
     * @param array $context
     * @throws Exception
     */
    public function pushLog($message, array $context = [])
    {
        if ($this->logFile) {
            Log::getInstance("SQL")->pushInfo($message, $context);
        }
    }

    /**
     * SQL 错误日志推送
     * @param $message
     * @param array $context
     * @throws Exception
     */
    public function pushErrorLog($message, array $context = [])
    {
        if ($this->logFile) {
            Log::getInstance("ERROR-SQL")->pushInfo($message, $context);
        }
    }

    /**
     * 获取 db-connect 是否被连接开启的标志
     * @return bool
     */
    public function getActive()
    {
        return $this->_active;
    }

    /**
     * 如果当前 db-connect 没有实例化，则开启
     * @throws Exception
     */
    protected function open()
    {
        if (null === $this->_pdo) {
            if (empty($this->dsn)) {
                throw new Exception('数据库连接串"dsn"不能为空', 100800101);
            }
            try {
                Timer::begin('db-connect');

                $this->_pdo = $this->createPdoInstance();
                $this->initConnection($this->_pdo);
                $this->_active = true;

                $this->pushLog('connect-db', [
                    'lastTime' => Timer::end('db-connect'),
                ]);
            } catch (\PDOException $e) {
                if (PHP_DEBUG) {
                    $err_msg = '数据库连接失败: (' . $e->getCode() . ')' . $e->getMessage();
                } else {
                    $err_msg = '数据库连接失败.(' . $e->getCode() . ')';
                }
                throw new Exception($err_msg, 100800102, $e->errorInfo);
            }
        }
    }

    /**
     * 关闭、置空当前的 db-connect
     */
    protected function close()
    {
        $this->_pdo = null;
        $this->_active = false;
    }

    /**
     * 创建 PDO 对象实例
     * @return \PDO
     * @throws Exception
     */
    protected function createPdoInstance()
    {
        $pdoClass = $this->pdoClass;
        if (!class_exists($pdoClass)) {
            throw new Exception(str_cover('PDO连接库"{className}"不存在', [
                '{className}' => $pdoClass
            ]), 100800104);
        }
        // 创建 PDO 实例
        @$instance = new $pdoClass($this->dsn, $this->username, $this->password);
        if (!$instance) {
            throw new Exception('PDO连接数据库失败.', 100800103);
        }
        return $instance;
    }

    /**
     * 返回 PDO 对象实例
     * @return \PDO|null
     */
    public function getPdoInstance()
    {
        return $this->_pdo;
    }

    /**
     * db 连接预处理
     * @param \PDO $pdo
     */
    protected function initConnection($pdo)
    {
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        if (null !== $this->emulatePrepare && constant('\PDO::ATTR_EMULATE_PREPARES')) {
            $this->setAttribute(\PDO::ATTR_EMULATE_PREPARES, $this->emulatePrepare);
        }

        if (null !== $this->charset) {
            $pdo->exec('SET NAMES ' . $pdo->quote($this->charset));
        }
    }

    /**
     * 获取 db-connect 的特殊属性
     * @param int $name
     * @return mixed
     * @throws Exception
     */
    public function getAttribute($name)
    {
        $this->setActive(true);
        return $this->_pdo->getAttribute($name);
    }

    /**
     * 设置 db-connect 的特殊属性
     * @param int $name
     * @param mixed $value
     */
    public function setAttribute($name, $value)
    {
        if ($this->_pdo instanceof \PDO) {
            $this->_pdo->setAttribute($name, $value);
        } else {
            $this->_attributes[$name] = $value;
        }
    }

    /**
     * 获取数据库的服务信息
     * @return string
     * @throws Exception
     */
    public function getServerInfo()
    {
        return $this->getAttribute(\PDO::ATTR_SERVER_INFO);
    }

    /**
     * 获取数据库的版本信息
     * @return string
     * @throws Exception
     */
    public function getServerVersion()
    {
        return $this->getAttribute(\PDO::ATTR_SERVER_VERSION);
    }

    /**
     * 获取 db-driver 的版本信息
     * @return string
     * @throws Exception
     */
    public function getClientVersion()
    {
        return $this->getAttribute(\PDO::ATTR_CLIENT_VERSION);
    }

    /**
     * 获取当前事务
     * @return Transaction
     */
    public function getCurrentTransaction()
    {
        if (null !== $this->_transaction && $this->_transaction->getActive()) {
            return $this->_transaction;
        }
        return null;
    }

    /**
     * 开始 db 事务处理
     * @return Transaction
     * @throws Exception
     */
    public function beginTransaction()
    {
        $this->setActive(true);
        $this->_pdo->beginTransaction();
        return $this->_transaction = new Transaction($this);
    }

    /**
     * Sql 命令执行器
     * @return Command
     */
    public function createCommand()
    {
        return new Command($this);
    }

    /**
     * 通过SQL语句插入数据库，返回成功插入的数据条数
     * @param string $sql
     * @param array $params
     * @return int
     * @throws Exception
     */
    public function insertBySql($sql, $params = [])
    {
        return $this->createCommand()
            ->setText($sql)
            ->execute($params);
    }

    /**
     * 返回一个 db-insert 的builder
     * @return InsertBuilder
     */
    public function getInsertBuilder()
    {
        return new InsertBuilder($this);
    }

    /**
     * 通过数组插入数据库，返回成功插入的数据条数
     * @param string $table
     * @param array $columns
     * @return int
     * @throws Exception
     */
    public function insert($table, array $columns)
    {
        if (empty($columns)) {
            return 0;
        }
        return $this->getInsertBuilder()
            ->setTable($table)
            ->setColumns($columns)
            ->execute();
    }

    /**
     * 通过多数组插入数据库，返回成功插入的数据条数
     * @param string $table
     * @param array $multiData
     * @return int
     * @throws Exception
     */
    public function insertData($table, array $multiData)
    {
        if (empty($multiData)) {
            return 0;
        }

        return $this->getInsertBuilder()
            ->setTable($table)
            ->setMultiFields(array_keys(array_values($multiData)[0]))
            ->setMultiData($multiData)
            ->execute();
    }

    /**
     * 返回最后一条 insert 语句的自增ID
     * @return string
     */
    public function getLastInsertId()
    {
        return $this->getPdoInstance()
            ->lastInsertId();
    }

    /**
     * 通过 sql 更新数据库，返回成功更新的数据条数
     * @param string $sql
     * @param $params
     * @return int
     * @throws Exception
     */
    public function updateBySql($sql, $params = [])
    {
        return $this->createCommand()
            ->setText($sql)
            ->execute($params);
    }

    /**
     * 返回一个 db-update 的builder
     * @return UpdateBuilder
     */
    public function getUpdateBuilder()
    {
        return new UpdateBuilder($this);
    }

    /**
     * 通过数组更新数据库，返回成功更新的数据条数
     * @param string $table
     * @param array $columns
     * @param string $where
     * @param array $params
     * @return int
     * @throws Exception
     */
    public function update($table, array $columns, $where = '', $params = [])
    {
        if (empty($columns)) {
            return 0;
        }
        return $this->getUpdateBuilder()
            ->setTable($table)
            ->setColumns($columns)
            ->setWhere($where, $params)
            ->execute();
    }

    /**
     * 通过 sql 删除数据库，返回成功删除的数据条数
     * @param string $sql
     * @param array $params
     * @return int
     * @throws Exception
     */
    public function deleteBySql($sql, $params = [])
    {
        return $this->createCommand()
            ->setText($sql)
            ->execute($params);
    }

    /**
     * 返回一个 db-delete 的builder
     * @return DeleteBuilder
     */
    public function getDeleteBuilder()
    {
        return new DeleteBuilder($this);
    }

    /**
     * 通过 builder 删除数据库，返回成功删除的数据条数
     * @param string $table
     * @param string $where
     * @param array $params
     * @return int
     * @throws Exception
     */
    public function delete($table, $where = '', $params = [])
    {
        return $this->getDeleteBuilder()
            ->setTable($table)
            ->setWhere($where, $params)
            ->execute();
    }

    /**
     * 通过 sql 查询符合条件的记录数
     * @param string $sql
     * @param array $params
     * @return int
     * @throws Exception
     */
    public function countBySql($sql, $params = [])
    {
        return $this->createCommand()
            ->setText($sql)
            ->count($params);
    }

    /**
     * 通过 sql 查询符合条件的第一条记录
     * @param string $sql
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function findBySql($sql, $params = [])
    {
        return $this->createCommand()
            ->setText($sql)
            ->queryRow($params);
    }

    /**
     * 通过 sql 查询符合条件的第一条记录
     * @param string $sql
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function findAllBySql($sql, $params = [])
    {
        return $this->createCommand()
            ->setText($sql)
            ->queryAll($params);
    }

    /**
     * 返回一个 db-find 的builder
     * @return FindBuilder
     */
    public function getFindBuilder()
    {
        return new FindBuilder($this);
    }

    /**
     * 查询影响的结果集行数，对于 select ，rowCount() 返回有可能为部分结果，这里用统计更好
     * @param \Db\Builder\Criteria $criteria
     * @return int
     * @throws Exception
     */
    public function count($criteria)
    {
        return $this->getFindBuilder()
            ->addCriteria($criteria)
            ->count();
    }

    /**
     * 查询第一条结果集
     * @param \Db\Builder\Criteria $criteria
     * @return array
     * @throws Exception
     */
    public function find($criteria)
    {
        return $this->getFindBuilder()
            ->addCriteria($criteria)
            ->queryRow();
    }

    /**
     * 查询所有结果集
     * @param \Db\Builder\Criteria $criteria
     * @return array
     * @throws Exception
     */
    public function findAll($criteria)
    {
        return $this->getFindBuilder()
            ->addCriteria($criteria)
            ->queryAll();
    }

    /**
     * @param \Db\Builder\Criteria|array|string $criteria
     * @param array $params
     * @return \Db\Pagination\Pagination
     * @throws \Helper\Exception
     */
    public function pagination($criteria, $params = [])
    {
        return new Db\Pagination\Pagination($criteria, $params, $this);
    }
}