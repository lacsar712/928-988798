-- Migration: 002_assessment_and_openday
-- Description: 新增绩效评议与公众开放日模块
-- Author: GovCore Team

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

USE govcore;

-- ----------------------------
-- Table structure for assessment_activities
-- ----------------------------
DROP TABLE IF EXISTS `assessment_activities`;
CREATE TABLE `assessment_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '活动标题',
  `start_time` datetime NOT NULL COMMENT '开始时间',
  `end_time` datetime NOT NULL COMMENT '结束时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1启用 0禁用',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_time` (`start_time`,`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `assessment_activities` (`title`, `start_time`, `end_time`, `status`) VALUES
('2024年度政府部门绩效评议', '2024-01-01 00:00:00', '2026-12-31 23:59:59', 1),
('2023年度政府服务满意度调查', '2023-01-01 00:00:00', '2023-12-31 23:59:59', 1);

-- ----------------------------
-- Table structure for assessment_departments
-- ----------------------------
DROP TABLE IF EXISTS `assessment_departments`;
CREATE TABLE `assessment_departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) NOT NULL COMMENT '活动ID',
  `name` varchar(100) NOT NULL COMMENT '部门名称',
  `sort_order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_activity_id` (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `assessment_departments` (`activity_id`, `name`, `sort_order`) VALUES
(1, '市发展改革委', 1),
(1, '市教育局', 2),
(1, '市公安局', 3),
(1, '市民政局', 4),
(1, '市财政局', 5),
(1, '市人力资源社会保障局', 6),
(1, '市自然资源和规划局', 7),
(1, '市住房城乡建设局', 8);

-- ----------------------------
-- Table structure for assessment_indicators
-- ----------------------------
DROP TABLE IF EXISTS `assessment_indicators`;
CREATE TABLE `assessment_indicators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) NOT NULL COMMENT '活动ID',
  `name` varchar(100) NOT NULL COMMENT '指标名称',
  `sort_order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_activity_id` (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `assessment_indicators` (`activity_id`, `name`, `sort_order`) VALUES
(1, '服务态度', 1),
(1, '办事效率', 2),
(1, '廉洁奉公', 3),
(1, '信息公开', 4),
(1, '依法行政', 5);

-- ----------------------------
-- Table structure for assessment_votes
-- ----------------------------
DROP TABLE IF EXISTS `assessment_votes`;
CREATE TABLE `assessment_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) NOT NULL COMMENT '活动ID',
  `ip_address` varchar(50) NOT NULL COMMENT 'IP地址',
  `cookie_token` varchar(100) DEFAULT NULL COMMENT 'Cookie标识',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_activity_ip` (`activity_id`,`ip_address`),
  KEY `idx_activity_cookie` (`activity_id`,`cookie_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for assessment_vote_details
-- ----------------------------
DROP TABLE IF EXISTS `assessment_vote_details`;
CREATE TABLE `assessment_vote_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vote_id` int(11) NOT NULL COMMENT '投票ID',
  `department_id` int(11) NOT NULL COMMENT '部门ID',
  `indicator_id` int(11) NOT NULL COMMENT '指标ID',
  `score` tinyint(1) NOT NULL COMMENT '评分 1-5',
  PRIMARY KEY (`id`),
  KEY `idx_vote_id` (`vote_id`),
  KEY `idx_dept_indicator` (`department_id`,`indicator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for openday_activities
-- ----------------------------
DROP TABLE IF EXISTS `openday_activities`;
CREATE TABLE `openday_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `theme` varchar(255) NOT NULL COMMENT '活动主题',
  `event_time` datetime NOT NULL COMMENT '活动时间',
  `location` varchar(255) NOT NULL COMMENT '活动地点',
  `quota` int(11) NOT NULL DEFAULT '0' COMMENT '名额限额',
  `manager` varchar(100) NOT NULL COMMENT '负责人',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1启用 0禁用',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_event_time` (`event_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for openday_reservations
-- ----------------------------
DROP TABLE IF EXISTS `openday_reservations`;
CREATE TABLE `openday_reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) NOT NULL COMMENT '活动ID',
  `booking_code` varchar(20) NOT NULL COMMENT '预约编号',
  `name` varchar(50) NOT NULL COMMENT '姓名',
  `id_card` varchar(18) NOT NULL COMMENT '身份证号',
  `phone` varchar(11) NOT NULL COMMENT '手机号',
  `companions` int(11) NOT NULL DEFAULT '0' COMMENT '同行人数',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1有效 0已取消',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_booking_code` (`booking_code`),
  KEY `idx_activity_id` (`activity_id`),
  KEY `idx_phone` (`phone`),
  KEY `idx_phone_code` (`phone`, `booking_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- ROLLBACK BEGIN
-- 以下为回滚操作，migrate.php 工具会自动解析并执行此段
-- ============================================================
-- DROP TABLE IF EXISTS `openday_reservations`;
-- DROP TABLE IF EXISTS `openday_activities`;
-- DROP TABLE IF EXISTS `assessment_vote_details`;
-- DROP TABLE IF EXISTS `assessment_votes`;
-- DROP TABLE IF EXISTS `assessment_indicators`;
-- DROP TABLE IF EXISTS `assessment_departments`;
-- DROP TABLE IF EXISTS `assessment_activities`;
-- ============================================================
-- ROLLBACK END
-- ============================================================
