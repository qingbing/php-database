<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-10-27
 * Version      :   1.0
 */

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
            $config = Config::suffix(Config::getInstance('database', 'master')->getAll());
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
     * @var \PDO PDO链接数据库
     */
    private $_pdo;
    private $_active = false; // 连接是否被激活

    /**
     * Db constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->configure($config);
        $this->init();
    }

    /**
     * 构造函数之后的初始化
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
     * 获取 db-connect 是否被连接开启的标志
     * @return bool
     */
    public function getActive()
    {
        return $this->_active;
    }

    /**
     * 如果当前 db-connect 没有实例化，则开启
     * @throws DbException
     */
    protected function open()
    {
        if (null === $this->_pdo) {
            if (empty($this->dsn)) {
                throw new DbException('\pf\db\Connection.connectionString cannot be empty.');
            }
            try {
                Logger::beginTimer('db-connect');
                $this->_pdo = $this->createPdoInstance();
                $this->initConnection($this->_pdo);
                Logger::add(Logger::TYPE_TIMER, 'db-connect', 'Connect DB'); // 应用结束，记录日志
                $this->_active = true;
            } catch (\PDOException $e) {
                if (PF_DEBUG) {
                    $err_msg = 'db-connection failed to open the DB connection: ' . $e->getMessage();
                } else {
                    $err_msg = '\pf\db\Connection failed to open the DB connection.';
                }
                throw new DbException($err_msg, (int)$e->getCode(), $e->errorInfo);
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
}