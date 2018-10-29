

CREATE TABLE `cf_stu` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(50) NOT NULL COMMENT '姓名',
  `sex` varchar(50) NOT NULL COMMENT '性别',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='学生表';



CREATE TABLE `cf_stu_course` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `stu_id` int(11) NOT NULL COMMENT '学生ID',
  `name` varchar(50) NOT NULL COMMENT '课程名称',
  PRIMARY KEY (`id`),
  KEY `stu_id`(`stu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='学生课程表';

