<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-05-19
 * Version      :   1.0
 */
require("../vendor/autoload.php");

define('ENV', "dev");

$className = \TestCore\TestCommand::getInstance()->getParam('c', null);

try {
    if (null !== $className) {
        $class = "\Test\\{$className}";
    } else {
        $class = "\TestCore\\Helper";
    }
    /* @var $class \TestCore\Tester */
    $class::getInstance()->run();
} catch (Exception $e) {
    var_dump($e);
}