<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-05-19
 * Version      :   1.0
 */
require("../vendor/autoload.php");

define('ENV', "dev");

try {
    if ($_GET['c']) {
        $className = $_GET['c'];
        $class = "\Test\\{$className}";
    } else {
        $class = "\TestCore\\Helper";
    }
    /* @var $class \TestCore\Tester */
    $class::getInstance()->run();
} catch (Exception $e) {
    var_dump($e);
}