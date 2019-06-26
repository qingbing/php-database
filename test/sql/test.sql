

CREATE TABLE `test_stu` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(50) NOT NULL COMMENT '姓名',
  `sex` varchar(50) NOT NULL COMMENT '性别',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='学生表';



CREATE TABLE `test_stu_course` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `stu_id` BIGINT(20) UNSIGNED NOT NULL COMMENT '学生ID',
  `name` varchar(50) NOT NULL COMMENT '课程名称',
  PRIMARY KEY (`id`),
  KEY `stu_id`(`stu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='学生课程表';

