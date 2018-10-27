<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-10-27
 * Version      :   1.0
 */

namespace TestCore;

use Helper\SingleTon;

/**
 * Class Tester
 * @package TestCore
 */
abstract class Tester extends SingleTon
{
    /**
     * 执行函数
     */
    abstract public function run();
}