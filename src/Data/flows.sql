CREATE TABLE IF NOT EXISTS `flow_steps` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_name` varchar(50) DEFAULT NULL,
  `flow_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `real_title` varchar(255) DEFAULT NULL,
  `content` text,
  `real_content` text,
  `data` text COMMENT '业务相关数据 json',
  `step` varchar(255) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `created_user` varchar(128) DEFAULT NULL COMMENT '创建者',
  `created_role` varchar(128) DEFAULT NULL COMMENT '创建者角色',
  `alert_times` int(11) DEFAULT '0' COMMENT '提醒次数',
  `next_processing_time` timestamp NULL DEFAULT NULL COMMENT '下次执行时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='流程步骤表';

CREATE TABLE IF NOT EXISTS  `flows` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_name` varchar(50) NOT NULL DEFAULT 'zyd',
  `current_step` varchar(255) DEFAULT NULL,
  `current_status` varchar(255) DEFAULT NULL COMMENT '当前任务的状态',
  `accepted_users` varchar(255) DEFAULT NULL COMMENT '接受任务的人',
  `accepted_roles` varchar(255) DEFAULT NULL COMMENT '当前接收的角色',
  `created_user` varchar(128) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='流程主表';

CREATE TABLE `config_delays` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `start_step` varchar(255) DEFAULT NULL COMMENT '开始步骤',
  `start_at` varchar(50) DEFAULT NULL COMMENT '开始步骤位置，0接收,1执行完成',
  `end_step` varchar(255) DEFAULT NULL COMMENT '结束步骤',
  `end_at` varchar(50) DEFAULT NULL COMMENT '结束步骤位置，0接收,1执行完成',
  `delay_hours` int(11) DEFAULT NULL COMMENT '超时时间',
  `back_role` varchar(255) DEFAULT NULL COMMENT '超时原因录入角色',
  `cc_roles` varchar(255) DEFAULT NULL COMMENT '抄送角色，多个以逗号分隔',
  `more_cc_roles` varchar(255) DEFAULT NULL COMMENT '多次提醒抄送角色',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;