<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-12-07
 * Version      :   1.0
 */
return [
    'master' => [
        'dsn' => 'mysql:host=127.0.0.1;dbname=pf_test;charset=utf8;',
        'username' => 'root',
        'password' => '123456',
        'autoConnect' => true,
        'pdoClass' => '\PDO',
        'logFile' => true,
        'tablePrefix' => 'pf_',
        'emulatePrepare' => '',
    ]
];