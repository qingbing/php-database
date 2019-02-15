<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-10-27
 * Version      :   1.0
 */

namespace Test;

use Components\Db;
use DbSupports\Builder\Criteria;
use TestCore\Tester;

class TestDb extends Tester
{
    /**
     * 执行函数
     * @return mixed|void
     * @throws \Exception
     */
    public function run()
    {
        /**
         * 获取db实例
         */
        $db = Db::getInstance([
            'c-file' => 'database',
            'c-group' => 'master',
        ]);

        /**
         * 基础信息获取
         */
        // 获取数据库的服务信息
        $serverInfo = $db->getServerInfo();
        var_dump($serverInfo);
        // 获取数据库的版本信息
        $serverVersion = $db->getServerVersion();
        var_dump($serverVersion);
        // 获取驱动的版本信息
        $clientVersion = $db->getClientVersion();
        var_dump($clientVersion);

        /**
         * sql语句操作
         */
        // sql 插入语句范例
        $sql = "INSERT INTO `{{stu}}` (`name`,`sex`) VALUES (:name,:sex)";
        $res = $db->insertBySql($sql, [
            ':name' => 'name_12',
            ':sex' => 'sex_12',
        ]);
        var_dump($res);
        // sql 更新语句范例
        $sql = "UPDATE `{{stu}}` SET `sex`=:sex WHERE id>=:bid AND id<=:eid";
        $res = $db->updateBySql($sql, [
            ':sex' => 12345678,
            ':bid' => 2,
            ':eid' => 4,
        ]);
        var_dump($res);
        // sql 删除语句范例
        $sql = "DELETE FROM `{{stu}}` WHERE id>=:bid AND id<=:eid";
        $res = $db->deleteBySql($sql, [
            ':bid' => 2,
            ':eid' => 4,
        ]);
        var_dump($res);
        // Sql 查询符合条件的记录数
        $sql = "SELECT * FROM `{{stu}}`";
        $count = $db->countBySql($sql);
        var_dump($count);

        $sql = "SELECT * FROM `{{stu}}`";
        $row = $db->findBySql($sql);
        var_dump($row);

        $sql = "SELECT * FROM `{{stu}}`";
        $records = $db->findAllBySql($sql);
        var_dump($records);


        /**
         * 简化操作
         */
        // 单记录数组插入操作
        $res = $db->insert('{{stu}}', [
            'name' => 'name_1',
            'sex' => 'sex_1',
        ]);
        var_dump($res);
        // 多记录数组插入操作
        $res = $db->insertData('{{stu}}', [
            ['name' => 'name_2', 'sex' => 'sex_2',],
            ['name' => 'name_3', 'sex' => 'sex_3',],
        ]);
        var_dump($res);
        // 获取插入的最后一次的insertID
        $lastId = $db->getLastInsertId();
        var_dump($lastId);

        // 更新数组操作
        $res = $db->update('{{stu}}', ['sex' => '1234511'], 'id>=:bid AND id<=:eid', [
            ':bid' => 2,
            ':eid' => 4,
        ]);
        var_dump($res);
        // 删除 build 操作
        $res = $db->delete('{{stu}}', 'id>=:bid AND id<=:eid', [
            ':bid' => 2,
            ':eid' => 4,
        ]);
        var_dump($res);
        // 初始化 条件
        $criteria = new Criteria();
        $criteria->setTable('{{stu}}')
            ->addWhere('id>:startId', [
                ':startId' => 2
            ]);
        // 查询符合条件的记录数
        $count = $db->count($criteria);
        var_dump($count);
        // 查询符合条件的首条记录
        $row = $db->find($criteria);
        var_dump($row);
        // 查询符合条件的全部记录
        $res = $db->findAll($criteria);
        var_dump($res);
        var_dump('===== over ====');
    }
}