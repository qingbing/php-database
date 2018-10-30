<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 2018/10/29
 * Time: 上午10:26
 */

namespace TestCore;


use Helper\Exception;
use Helper\SingleTon;

class TestCommand extends SingleTon
{
    private $_scriptFile;
    private $_params = [];

    /**
     * 单例模式初始化函数，在构造函数后执行，子类可以覆盖
     * @throws Exception
     */
    protected function init()
    {
        if (!isset($_SERVER['argc']) || 0 === $_SERVER['argc']) {
            throw new Exception("该程序是cli模式，只能在命令行模式下用脚步执行");
        }
        $this->_scriptFile = $_SERVER['SCRIPT_NAME'];
        for ($i = 1; $i < $_SERVER['argc']; $i++) {
            $arg = $_SERVER['argv'][$i];
            // 用 "--" 开头作为参数标志
            if (0 !== strpos($arg, '--')) {
                continue;
            }
            // 参数名和参数用 "=" 隔开
            $pos = strpos($arg, '=');
            if (false === $pos) { // 没有分隔符
                continue;
            }
            $name = trim(substr($arg, 2, $pos - 2));
            $value = trim(substr($arg, $pos + 1));
            $this->_params[$name] = $value;
        }
    }

    /**
     * 获取命令行的传递参数
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * 获取命令行执行的某个参数
     * @param string $name
     * @param mixed $defaultVal
     * @return string|null
     */
    public function getParam($name = null, $defaultVal = null)
    {
        return isset($this->_params[$name]) ? $this->_params[$name] : $defaultVal;
    }
}