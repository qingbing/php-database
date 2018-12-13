<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-12-12
 * Version      :   1.0
 */

namespace DbSupports\Builder\Abstracts;


use Abstracts\Base;

abstract class BaseBuilder extends Base
{
    /**
     * build sql 需要的选项
     * @var array
     */
    protected $query = [];
    private $_params = []; // SQL绑定参数

    static private $_paramCount = 0; // 参数绑定个数

    /**
     * 引号包裹字段名称
     * @param string $name
     * @return string
     */
    public function quoteSimpleColumnName($name)
    {
        return '`' . $name . '`';
    }

    /**
     * 引号包裹字段名称，带表名
     * @param string $name
     * @return string
     */
    public function quoteColumnName($name)
    {
        if (false !== ($pos = strrpos($name, '.'))) {
            $prefix = $this->quoteTableName(substr($name, 0, $pos)) . '.';
            $name = substr($name, $pos + 1);
        } else {
            $prefix = '';
        }
        return $prefix . ($name === '*' ? $name : $this->quoteSimpleColumnName($name));
    }

    /**
     * 引号包裹表名称
     * @param string $name
     * @return string
     */
    public function quoteSimpleTableName($name)
    {
        return '`' . $name . '`';
    }

    /**
     * 引号包裹表名称，带库名
     * @param string $name
     * @return string
     */
    public function quoteTableName($name)
    {
        if (false === strpos($name, '.')) {
            return $this->quoteSimpleTableName($name);
        }
        $parts = explode('.', $name);
        foreach ($parts as $i => $part) {
            $parts[$i] = $this->quoteSimpleTableName($part);
        }
        return implode('.', $parts);
    }

    /**
     * 返回构造SQL语句的选项
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * 获取绑定值
     * @return string
     */
    protected function getBindKey()
    {
        return ':pf_' . self::$_paramCount++;
    }

    /**
     * 获取绑定的SQL参数
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * 绑定SQL参数
     * @param mixed $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->_params = $params;
        return $this;
    }

    /**
     * 添加绑定参数
     * @param string $bk
     * @param string $bv
     * @return $this
     */
    public function addParam($bk, $bv)
    {
        $this->_params[$bk] = $bv;
        return $this;
    }

    /**
     * 添加绑定参数
     * @param array $params
     * @return $this
     */
    public function addParams(array $params)
    {
        foreach ($params as $k => $v) {
            $this->addParam($k, $v);
        }
        return $this;
    }
}