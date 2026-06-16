-- Migration: 003_service_and_misc
-- Description: 新增服务网点地图、应急预案、采购公告、无障碍偏好模块
-- Author: GovCore Team

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

USE govcore;

-- ----------------------------
-- Table structure for townships
-- ----------------------------
DROP TABLE IF EXISTS `townships`;
CREATE TABLE `townships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '乡镇/街道名称',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1:乡镇 2:街道',
  `sort_order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1启用 0禁用',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sort_order` (`sort_order`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `townships` (`name`, `type`, `sort_order`, `status`) VALUES
('城关镇', 1, 1, 1),
('河东镇', 1, 2, 1),
('河西镇', 1, 3, 1),
('南山镇', 1, 4, 1),
('北坝镇', 1, 5, 1),
('东城街道', 2, 6, 1),
('西城街道', 2, 7, 1),
('南城街道', 2, 8, 1),
('北城街道', 2, 9, 1);

-- ----------------------------
-- Table structure for service_tags
-- ----------------------------
DROP TABLE IF EXISTS `service_tags`;
CREATE TABLE `service_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '标签名称',
  `color` varchar(20) DEFAULT '#004d99' COMMENT '标签颜色',
  `icon` varchar(50) DEFAULT NULL COMMENT '图标',
  `sort_order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1启用 0禁用',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `service_tags` (`name`, `color`, `icon`, `sort_order`, `status`) VALUES
('医保', '#dc3545', 'bi-heart-pulse', 1, 1),
('户籍', '#004d99', 'bi-people', 2, 1),
('民政', '#28a745', 'bi-house', 3, 1),
('社保', '#fd7e14', 'bi-shield-check', 4, 1),
('教育', '#6f42c1', 'bi-mortarboard', 5, 1),
('不动产', '#17a2b8', 'bi-building', 6, 1),
('税务', '#6c757d', 'bi-calculator', 7, 1),
('计生', '#e83e8c', 'bi-gender-ambiguous', 8, 1);

-- ----------------------------
-- Table structure for service_points
-- ----------------------------
DROP TABLE IF EXISTS `service_points`;
CREATE TABLE `service_points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL COMMENT '村社/服务点名称',
  `township_id` int(11) NOT NULL COMMENT '所属乡镇/街道ID',
  `address` varchar(500) DEFAULT NULL COMMENT '地址',
  `phone` varchar(50) DEFAULT NULL COMMENT '联系电话',
  `open_time` varchar(200) DEFAULT NULL COMMENT '开放时间',
  `coord_x` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '地图X坐标 0-100',
  `coord_y` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '地图Y坐标 0-100',
  `distance` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '距离（公里）mock字段',
  `sort_order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1启用 0禁用',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_township_id` (`township_id`),
  KEY `idx_status` (`status`),
  KEY `idx_coord` (`coord_x`, `coord_y`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `service_points` (`name`, `township_id`, `address`, `phone`, `open_time`, `coord_x`, `coord_y`, `distance`, `sort_order`, `status`) VALUES
('东关村便民服务站', 1, '城关镇东关村村委会1楼', '010-88880101', '工作日 9:00-17:00', 25.00, 30.00, 1.20, 1, 1),
('西关村便民服务站', 1, '城关镇西关村村委会2楼', '010-88880102', '工作日 8:30-17:30', 15.00, 35.00, 2.50, 2, 1),
('南街村便民服务站', 1, '城关镇南街村活动中心', '010-88880103', '工作日 9:00-17:00 周六 9:00-12:00', 20.00, 45.00, 1.80, 3, 1),
('河东村便民服务站', 2, '河东镇河东村村委会', '010-88880201', '工作日 9:00-17:00', 55.00, 25.00, 4.50, 1, 1),
('河西村便民服务站', 3, '河西镇河西村便民大厅', '010-88880301', '工作日 9:00-17:00', 10.00, 50.00, 5.20, 1, 1),
('南山村便民服务站', 4, '南山镇南山村村委会', '010-88880401', '工作日 8:30-16:30', 50.00, 70.00, 8.30, 1, 1),
('北坝村便民服务站', 5, '北坝镇北坝村活动中心', '010-88880501', '工作日 9:00-17:00', 45.00, 15.00, 6.70, 1, 1),
('东城社区便民服务中心', 6, '东城街道办事处1楼', '010-88880601', '工作日 9:00-18:00 周六 9:00-12:00', 65.00, 40.00, 3.10, 1, 1),
('西城社区便民服务中心', 7, '西城街道政务大厅', '010-88880701', '工作日 9:00-18:00', 30.00, 55.00, 2.80, 1, 1),
('南城社区便民服务站', 8, '南城街道社区服务中心', '010-88880801', '工作日 9:00-17:30', 60.00, 65.00, 4.20, 1, 1),
('北城社区便民服务站', 9, '北城街道居委会', '010-88880801', '工作日 9:00-17:00', 35.00, 20.00, 3.50, 1, 1),
('太平村便民服务站', 2, '河东镇太平村村委会', '010-88880202', '工作日 9:00-17:00', 70.00, 30.00, 5.80, 2, 1),
('幸福村便民服务站', 3, '河西镇幸福村活动中心', '010-88880302', '工作日 9:00-16:30', 15.00, 65.00, 6.50, 2, 1),
('光明社区便民服务站', 6, '东城街道光明社区', '010-88880602', '工作日 9:00-17:30', 75.00, 45.00, 3.80, 2, 1),
('和平社区便民服务站', 7, '西城街道和平社区', '010-88880702', '工作日 9:00-17:00', 25.00, 60.00, 4.00, 2, 1);

-- ----------------------------
-- Table structure for service_point_tags
-- ----------------------------
DROP TABLE IF EXISTS `service_point_tags`;
CREATE TABLE `service_point_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_point_id` int(11) NOT NULL COMMENT '服务点ID',
  `tag_id` int(11) NOT NULL COMMENT '标签ID',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_point_tag` (`service_point_id`, `tag_id`),
  KEY `idx_service_point_id` (`service_point_id`),
  KEY `idx_tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `service_point_tags` (`service_point_id`, `tag_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4),
(2, 1), (2, 2), (2, 5),
(3, 2), (3, 3), (3, 8),
(4, 1), (4, 4), (4, 6),
(5, 2), (5, 3), (5, 7),
(6, 1), (6, 3), (6, 4),
(7, 2), (7, 5), (7, 8),
(8, 1), (8, 2), (8, 3), (8, 4), (8, 5), (8, 6),
(9, 1), (9, 2), (9, 3), (9, 4), (9, 7),
(10, 1), (10, 3), (10, 8),
(11, 2), (11, 4), (11, 5),
(12, 1), (12, 3), (12, 6),
(13, 2), (13, 4), (13, 7),
(14, 1), (14, 2), (14, 5), (14, 8),
(15, 3), (15, 4), (15, 6);

-- ----------------------------
-- Table structure for emergency_plans
-- ----------------------------
DROP TABLE IF EXISTS `emergency_plans`;
CREATE TABLE `emergency_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_code` varchar(50) NOT NULL COMMENT '预案编号',
  `name` varchar(255) NOT NULL COMMENT '预案名称',
  `category` varchar(20) NOT NULL COMMENT '类别：自然灾害/事故灾难/公共卫生/社会安全',
  `classification` varchar(10) NOT NULL DEFAULT '公开' COMMENT '密级：公开/内部',
  `version` varchar(20) NOT NULL DEFAULT '1.0' COMMENT '版本号',
  `reviser` varchar(100) NOT NULL COMMENT '修订人',
  `publish_date` date DEFAULT NULL COMMENT '发布日期',
  `pdf_file` varchar(500) DEFAULT NULL COMMENT 'PDF附件路径',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1启用 0禁用',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_plan_code` (`plan_code`),
  KEY `idx_category` (`category`),
  KEY `idx_classification` (`classification`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `emergency_plans` (`plan_code`, `name`, `category`, `classification`, `version`, `reviser`, `publish_date`, `pdf_file`, `status`) VALUES
('YA-2024-001', 'XX市防汛抗旱应急预案', '自然灾害', '公开', '3.0', '市应急管理局', '2024-03-15', NULL, 1),
('YA-2024-002', 'XX市地震应急预案', '自然灾害', '公开', '2.1', '市应急管理局', '2024-05-20', NULL, 1),
('YA-2024-003', 'XX市突发地质灾害应急预案', '自然灾害', '内部', '1.2', '市自然资源局', '2024-06-10', NULL, 1),
('YA-2024-004', 'XX市危险化学品事故应急预案', '事故灾难', '公开', '4.0', '市应急管理局', '2024-01-10', NULL, 1),
('YA-2024-005', 'XX市重大交通事故应急预案', '事故灾难', '内部', '2.0', '市公安局', '2024-04-22', NULL, 1),
('YA-2024-006', 'XX市突发公共卫生事件应急预案', '公共卫生', '公开', '5.0', '市卫健委', '2024-02-28', NULL, 1),
('YA-2024-007', 'XX市食品安全事故应急预案', '公共卫生', '公开', '3.1', '市市场监管局', '2024-07-05', NULL, 1),
('YA-2024-008', 'XX市群体性事件应急预案', '社会安全', '内部', '2.0', '市公安局', '2024-03-01', NULL, 1),
('YA-2024-009', 'XX市恐怖袭击事件应急预案', '社会安全', '内部', '1.5', '市公安局', '2024-04-15', NULL, 1),
('YA-2024-010', 'XX市暴雨洪涝灾害应急预案', '自然灾害', '公开', '2.0', '市水利局', '2024-08-01', NULL, 1);

-- ----------------------------
-- Table structure for emergency_plan_revisions
-- ----------------------------
DROP TABLE IF EXISTS `emergency_plan_revisions`;
CREATE TABLE `emergency_plan_revisions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL COMMENT '预案ID',
  `version` varchar(20) NOT NULL COMMENT '版本号',
  `reviser` varchar(100) NOT NULL COMMENT '修订人',
  `change_summary` text COMMENT '变更说明',
  `pdf_file` varchar(500) DEFAULT NULL COMMENT '该版本PDF附件',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '修订时间',
  PRIMARY KEY (`id`),
  KEY `idx_plan_id` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `emergency_plan_revisions` (`plan_id`, `version`, `reviser`, `change_summary`) VALUES
(1, '1.0', '市水利局', '初始版本发布'),
(1, '2.0', '市应急管理局', '根据新防洪标准修订响应等级划分'),
(1, '3.0', '市应急管理局', '增加城市内涝专项处置流程'),
(2, '1.0', '市地震局', '初始版本发布'),
(2, '2.0', '市应急管理局', '更新震后评估标准和恢复重建方案'),
(2, '2.1', '市应急管理局', '补充次生灾害防范措施'),
(4, '1.0', '市安监局', '初始版本发布'),
(4, '2.0', '市应急管理局', '调整应急响应分级标准'),
(4, '3.0', '市应急管理局', '增加危化品运输事故处置方案'),
(4, '4.0', '市应急管理局', '全面修订，衔接省级新预案体系'),
(6, '1.0', '市卫健委', '初始版本发布'),
(6, '2.0', '市卫健委', '增加传染病防控专项预案'),
(6, '3.0', '市卫健委', '修订应急响应启动条件'),
(6, '4.0', '市卫健委', '根据新冠疫情防控经验全面修订'),
(6, '5.0', '市卫健委', '优化多部门协同联动机制');

-- ----------------------------
-- Table structure for purchases
-- ----------------------------
DROP TABLE IF EXISTS `purchases`;
CREATE TABLE `purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_name` varchar(500) NOT NULL COMMENT '项目名称',
  `procurement_unit` varchar(200) NOT NULL COMMENT '采购单位',
  `budget_amount` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '预算金额',
  `status` varchar(20) NOT NULL DEFAULT '招标中' COMMENT '状态：招标中/已截止/已成交/流标',
  `deadline` datetime DEFAULT NULL COMMENT '报名截止时间',
  `content` text COMMENT '公告内容',
  `winner` varchar(200) DEFAULT NULL COMMENT '中标人',
  `winning_amount` decimal(15,2) DEFAULT NULL COMMENT '中标金额',
  `attachment` varchar(500) DEFAULT NULL COMMENT '附件路径',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_budget` (`budget_amount`),
  KEY `idx_deadline` (`deadline`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `purchases` (`project_name`, `procurement_unit`, `budget_amount`, `status`, `deadline`, `content`, `winner`, `winning_amount`, `attachment`) VALUES
('XX市智慧政务平台升级改造项目', '市大数据中心', 5800000.00, '招标中', '2026-07-31 17:00:00', '<p>XX市大数据中心现就智慧政务平台升级改造项目进行公开招标，欢迎符合条件的供应商参加投标。</p><p><strong>项目概况：</strong></p><ul><li>建设内容：政务服务平台升级、数据共享交换平台建设、统一身份认证系统改造</li><li>实施周期：合同签订后12个月内完成</li><li>质量要求：符合国家及行业相关标准</li></ul><p><strong>供应商资格要求：</strong></p><ul><li>具有独立法人资格</li><li>具备计算机信息系统集成二级及以上资质</li><li>近三年内有不少于2个同类项目业绩</li></ul>', NULL, NULL, NULL),
('XX市应急指挥中心视频会议系统采购', '市应急管理局', 1200000.00, '已截止', '2026-06-15 17:00:00', '<p>市应急管理局拟采购视频会议系统一套，用于市、区两级应急指挥中心远程会商。</p><p><strong>技术要求：</strong></p><ul><li>支持4K超高清视频会议</li><li>支持不少于50方同时在线</li><li>兼容现有指挥大厅大屏系统</li><li>支持移动端接入</li></ul>', NULL, NULL, NULL),
('XX市政务服务中心办公家具采购', '市政务服务中心', 350000.00, '已成交', '2026-05-20 17:00:00', '<p>市政务服务中心新址办公家具采购项目，包括办公桌椅、会议桌、文件柜等。</p><p><strong>采购清单：</strong></p><ul><li>办公桌椅：200套</li><li>会议桌：10张</li><li>文件柜：100个</li><li>接待沙发：20套</li></ul>', 'XX家具制造有限公司', 298000.00, NULL),
('XX市生态环境监测设备采购', '市生态环境局', 3200000.00, '流标', '2026-04-30 17:00:00', '<p>市生态环境局水质及空气质量自动监测站设备采购项目，因有效投标不足三家，本项目流标。</p><p><strong>流标原因：</strong>有效投标供应商不足法定三家，根据《政府采购法》相关规定，本项目予以流标，将择期重新组织招标。</p>', NULL, NULL, NULL),
('XX市公共资源交易系统维护服务', '市公共资源交易中心', 800000.00, '招标中', '2026-08-15 17:00:00', '<p>市公共资源交易中心现就公共资源交易系统年度维护服务进行公开招标。</p><p><strong>服务内容：</strong></p><ul><li>系统日常运维及故障排除</li><li>系统安全巡检及漏洞修复</li><li>功能优化及小版本升级</li><li>7×24小时技术支持热线</li></ul>', NULL, NULL, NULL);

-- ----------------------------
-- Table structure for accessibility_preferences
-- ----------------------------
DROP TABLE IF EXISTS `accessibility_preferences`;
CREATE TABLE `accessibility_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(100) NOT NULL COMMENT 'Session ID，用于未登录用户',
  `admin_user` varchar(255) DEFAULT NULL COMMENT '管理员用户名，用于已登录用户跨浏览器同步',
  `font_size` tinyint(1) NOT NULL DEFAULT 100 COMMENT '字体缩放比例 80-150',
  `high_contrast` tinyint(1) NOT NULL DEFAULT 0 COMMENT '高对比度模式 0关1开',
  `eye_care` tinyint(1) NOT NULL DEFAULT 0 COMMENT '护眼模式 0关1开',
  `tts_mode` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'TTS朗读模式 0关1开',
  `focus_highlight` tinyint(1) NOT NULL DEFAULT 0 COMMENT '键盘焦点高亮 0关1开',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_admin_user` (`admin_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- ROLLBACK BEGIN
-- 以下为回滚操作，migrate.php 工具会自动解析并执行此段
-- ============================================================
-- DROP TABLE IF EXISTS `accessibility_preferences`;
-- DROP TABLE IF EXISTS `purchases`;
-- DROP TABLE IF EXISTS `emergency_plan_revisions`;
-- DROP TABLE IF EXISTS `emergency_plans`;
-- DROP TABLE IF EXISTS `service_point_tags`;
-- DROP TABLE IF EXISTS `service_points`;
-- DROP TABLE IF EXISTS `service_tags`;
-- DROP TABLE IF EXISTS `townships`;
-- ============================================================
-- ROLLBACK END
-- ============================================================
