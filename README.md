# php-database
## 描述
Database 相关操作。可以单独使用。

## 注意事项
 - database的参数配置参考 qingbing/php-config 组件
 - DB类为数据库连接器，并提供基础的db查询、
 - Model类为数据表映射类


## Db 使用方法
### 1. 获取db实例
```
$db = \Db::getInstance();
```
### 2. 基础信息获取
```
// 获取数据库的服务信息
$serverInfo = $db->getServerInfo();
var_dump($serverInfo);
// 获取数据库的版本信息
$serverVersion = $db->getServerVersion();
var_dump($serverVersion);
// 获取驱动的版本信息
$clientVersion = $db->getClientVersion();
var_dump($clientVersion);
```

### 3. sql语句操作
```
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
```

### 4. 简化操作
```
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
```

### 5. builder 链式操作
```
// 插入
$res = $db->getInsertBuilder()
    ->setTable('{{stu}}')
    ->setColumns([
        'name' => 'name-builder-single',
        'sex' => 'builder',
    ])
    ->execute();
var_dump($res);

// 批量插入
$res = $db->getInsertBuilder()
    ->setTable('{{stu}}')
    ->setMultiFields(['name', 'sex'])
    ->setMultiData([
        [
            'name' => 'name-builder-multi_1',
            'sex' => 'builder',
        ],
        [
            'name' => 'name-builder-multi_2',
            'sex' => 'builder',
        ],
    ])
    ->execute();
var_dump($res);

$lastId = $db->getLastInsertId();

// 更新
$res = $db->getUpdateBuilder()
    ->setTable('{{stu}}')
    ->setColumns([
        'sex' => 'update'
    ])
    ->addWhere('id=:id', [':id' => $lastId])
    ->execute();
var_dump($res);

// 删除
$res = $db->getDeleteBuilder()
    ->setTable('{{stu}}')
    ->addWhere('id=:id', [':id' => 2])
    ->execute();
var_dump($res);

// 查询
$res = $db->getFindBuilder()
    ->setTable('{{stu}}')
    ->setSelect('id, name')
    ->addWhere('id=:id', [':id' => $lastId])
    ->queryAll();
var_dump($res);
```

### 6. Criteria 操作
```
$criteria1 = (new Criteria())
    ->setSelect('c.name as courseName')
    ->addJoin("LEFT JOIN {{stu_course}} c ON(t.id=c.stu_id)");

$criteria = (new Criteria())
    ->setTable('{{stu}}')
    ->setAlias('t')
    ->setSelect('t.id,t.name, t.sex')
    ->addWhere('t.id>:startId', [
        ':startId' => 2
    ])
    ->addWhereBetween('t.id', 3, 7)
    ->addCriteria($criteria1);

$res = $db->findAll($criteria);
var_dump($res);
```

### 7. Transaction 操作
```
$transaction = $db->beginTransaction();

// 更新
$res = $db->getUpdateBuilder()
    ->setTable('{{stu}}')
    ->setColumns([
        'sex' => 'update111'
    ])
    ->addWhere('id=:id', [':id' => 12])
    ->execute();
var_dump($res);

//        $transaction->commit();
$transaction->rollback();
```

### 8. Pagination 操作
```
$criteria = (new Criteria())
    ->setTable('{{stu}}')
    ->addWhere('t.id>:startId', [
        ':startId' => 2
    ]);

$res = $db->pagination($criteria)
    ->getData(2, 3);
var_dump($res);


var_dump(1111);
$sql = "SELECT * FROM `{{stu}}` t WHERE t.id>:startId";
$res = $db->pagination($sql, [
    ':startId' => 2
])
    ->getData(2);
var_dump($res);
```

## ====== 异常代码集合 ======


异常代码格式：1008 - XXX - XX （组件编号 - 文件编号 - 代码内异常）
```
 - 100800101 : 数据库连接串"dsn"不能为空
 - 100800102 : 数据库连接失败
 - 100800103 : PDO连接数据库失败
 - 100800104 : PDO连接库"{className}"不存在
 - 100800201 : "{type}"查询参数不完整
 - 100800301 : "{type}"查询参数不完整
 - 100800302 : "{type}"查询参数不完整
 - 100800401 : "{type}"查询参数不完整
 - 100800501 : Find查询必须带有"table"参数
 - 100800502 : "offset"必须和"limit"配对出现
 - 100800601 : "Transaction"尚属未激活状态，不能执行"commit"和"rollback"操作
 - 100800602 : "Transaction"尚属未激活状态，不能执行"commit"和"rollback"操作
 - 100800701 : pagination 构建参数"sqlment"无效
```
