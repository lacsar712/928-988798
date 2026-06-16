-- Database init script for GovCore CMS
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

USE govcore;

-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO `admin` VALUES ('1', 'admin', 'admin888'); -- Password is plaintext for easier CTF demo, or use MD5: 7fef6171469e80d32c0559f88b377245

-- ----------------------------
-- Table structure for news
-- ----------------------------
DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `publish_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of news
-- ----------------------------
INSERT INTO `news` (`title`, `content`) VALUES 
('XX市人民政府关于印发“十四五”数字政府建设规划的通知', '为深入贯彻落实国家和省关于加强数字政府建设的部署要求，加快推动我市数字政府改革建设，并在全市范围内进行推广实施...'),
('我市召开网络安全和信息化工作会议', '近日，我市召开网络安全和信息化工作会议，深入学习贯彻习近平总书记关于网络强国的重要思想，传达学习全国、全省网络安全和信息化工作会议精神...'),
('关于开展2024年度政务公开评估工作的公告', '为进一步提升全市政务公开工作水平，根据《中华人民共和国政府信息公开条例》规定，决定在全市范围内开展2024年度政务公开第三方评估工作...'),
('市大数据中心成功举办第三届数字城市论坛', '由市大数据中心主办的第三届数字城市论坛在市会议中心隆重举行。来自全国各地的专家学者、企业代表齐聚一堂，共话数字城市建设新未来...'),
('关于防范电信网络诈骗的紧急预警', '近期，我市电信网络诈骗案件高发，不法分子冒充公检法、领导、熟人等实施诈骗。市反诈中心提醒广大市民，务必提高警惕，不轻信、不转账...');

-- ----------------------------
-- Table structure for sys_logs
-- ----------------------------
DROP TABLE IF EXISTS `sys_logs`;
CREATE TABLE `sys_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(50) DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `payload` text,
  `log_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for faq_categories
-- ----------------------------
DROP TABLE IF EXISTS `faq_categories`;
CREATE TABLE `faq_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of faq_categories
-- ----------------------------
INSERT INTO `faq_categories` (`id`, `parent_id`, `name`, `icon`, `sort_order`, `status`) VALUES
(1, 0, '户口', 'bi-people-fill', 1, 1),
(2, 0, '医保', 'bi-heart-pulse-fill', 2, 1),
(3, 0, '社保', 'bi-shield-check', 3, 1),
(4, 0, '教育', 'bi-mortarboard-fill', 4, 1),
(5, 0, '出行', 'bi-car-front-fill', 5, 1);

-- ----------------------------
-- Table structure for faq_items
-- ----------------------------
DROP TABLE IF EXISTS `faq_items`;
CREATE TABLE `faq_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `question` varchar(500) NOT NULL,
  `answer` mediumtext NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `is_top` tinyint(1) NOT NULL DEFAULT '0',
  `view_count` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_is_top` (`is_top`),
  FULLTEXT KEY `ft_qa` (`question`,`answer`) WITH PARSER ngram
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of faq_items
-- ----------------------------
INSERT INTO `faq_items` (`category_id`, `question`, `answer`, `sort_order`, `is_top`, `view_count`, `status`) VALUES
(1, '新生儿如何办理户口登记？', '<p>新生儿出生后一个月内，由婴儿监护人或户主向婴儿父亲或母亲常住户口所在地公安派出所申报出生登记。</p><p><strong>所需材料：</strong></p><ul><li>出生医学证明原件及复印件</li><li>父母双方居民户口簿、居民身份证</li><li>结婚证原件及复印件</li></ul><p>办理时限：材料齐全当场办结。</p>', 1, 1, 1256, 1),
(1, '购房落户需要什么条件？', '<p>凡在本市市区购买成套商品房（含二手房），面积达60平方米以上，可申请本人、配偶及未成年子女常住户口。</p><p><strong>所需材料：</strong></p><ul><li>房屋产权证或按揭购房合同</li><li>户口簿、身份证</li><li>亲属关系证明</li></ul>', 2, 0, 892, 1),
(1, '户口迁移如何办理？', '<p>跨区县户口迁移，需先到迁入地派出所申请准迁证，再回原户籍地办理迁移证，最后到迁入地落户。</p><p>本市内户口迁移实行"一站式"服务，直接到迁入地派出所办理即可。</p>', 3, 0, 678, 1),
(2, '城镇居民医保如何参保缴费？', '<p>本市户籍未参加职工医保的居民，可持身份证、户口簿到户籍所在地社区服务中心办理参保登记。</p><p><strong>缴费标准：</strong>成年人每人每年380元，未成年人每人每年250元。</p><p>缴费时间：每年9月1日至12月31日缴纳下年度医保费。</p>', 1, 1, 2341, 1),
(2, '医保异地就医如何备案？', '<p>参保人跨省异地就医前，需办理异地就医备案手续。</p><p><strong>办理方式：</strong></p><ul><li>线上：通过"国家医保服务平台"APP自助办理</li><li>线下：持身份证到参保地医保经办机构办理</li></ul><p>备案后可在异地定点医院直接结算。</p>', 2, 0, 1567, 1),
(2, '医保门诊可以报销吗？', '<p>职工医保参保人在定点医疗机构发生的门诊费用，可使用个人账户支付，也可按规定享受门诊共济保障。</p><p>居民医保参保人在基层医疗机构就诊，门诊报销比例为50%，年度限额300元。</p>', 3, 0, 1123, 1),
(3, '养老保险缴费比例是多少？', '<p><strong>职工养老保险：</strong></p><ul><li>单位缴费比例：16%</li><li>个人缴费比例：8%</li></ul><p><strong>灵活就业人员：</strong>缴费比例为20%，其中8%计入个人账户。</p>', 1, 1, 1890, 1),
(3, '失业保险金如何领取？', '<p>失业前用人单位和本人已缴纳失业保险费满一年，非因本人意愿中断就业的，可申领失业保险金。</p><p>失业保险金标准为本市最低工资的80%，领取期限最长24个月。</p>', 2, 0, 987, 1),
(3, '社保断缴有什么影响？', '<p><strong>养老保险：</strong>累计缴费满15年即可，断缴不影响累计年限。</p><p><strong>医疗保险：</strong>断缴次月起停止享受医保待遇，连续缴费年限需重新计算。</p><p><strong>生育保险：</strong>需连续缴纳满1年才能享受生育津贴。</p>', 3, 0, 1456, 1),
(4, '义务教育阶段入学如何报名？', '<p>适龄儿童少年按照"划片招生、就近入学"原则，在规定时间内通过网上报名系统登记。</p><p><strong>报名材料：</strong>户口簿、房产证或租房合同、儿童预防接种证。</p><p>分配结果由教育局统一公示。</p>', 1, 1, 2134, 1),
(4, '高中招生录取政策是什么？', '<p>高中招生实行"分数优先、遵循志愿"的录取原则。</p><p>总分=中考文化考试成绩+体育成绩+政策性加分。</p><p>省级示范高中指标到校生比例不低于50%。</p>', 2, 0, 1678, 1),
(4, '学生资助政策有哪些？', '<p>我市已建立覆盖学前教育至高等教育的学生资助体系：</p><ul><li>学前教育：贫困家庭幼儿保教费减免</li><li>义务教育："两免一补"</li><li>高中教育：国家助学金、免学费</li><li>高等教育：生源地信用助学贷款</li></ul>', 3, 0, 876, 1),
(5, '机动车驾驶证如何申领？', '<p>初次申领驾驶证，需向车辆管理所提出申请。</p><p><strong>申请条件：</strong></p><ul><li>C1驾驶证：年满18周岁，身体条件合格</li><li>通过科目一（理论）、科目二（场地）、科目三（道路）、科目四（安全文明）考试</li></ul>', 1, 1, 1987, 1),
(5, '机动车年检如何办理？', '<p>小型非营运载客汽车6年内免检，每2年申领检验标志。</p><p>超过6年不满10年的，每2年上线检验1次；超过10年的，每年检验1次。</p><p>可通过"交管12123"APP预约办理。</p>', 2, 0, 1345, 1),
(5, '外地车限行规定是什么？', '<p>工作日早高峰7:00-9:00、晚高峰17:00-19:00，外地号牌小型客车禁止在绕城高速公路以内区域通行。</p><p>确需临时进入限行区域的，可提前在"交管12123"APP申请"入城通行证"。</p>', 3, 0, 2234, 1);

-- ----------------------------
-- Table structure for announcements
-- ----------------------------
DROP TABLE IF EXISTS `announcements`;
CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(500) NOT NULL,
  `content` text,
  `position` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1:顶部跑马灯 2:中部飘窗 3:底部条',
  `bg_color` varchar(50) DEFAULT '#ff6b6b',
  `text_color` varchar(50) DEFAULT '#ffffff',
  `link_url` varchar(500) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `can_close` tinyint(1) NOT NULL DEFAULT '1',
  `click_count` int(11) NOT NULL DEFAULT '0',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_position` (`position`),
  KEY `idx_status` (`status`),
  KEY `idx_time` (`start_time`,`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of announcements
-- ----------------------------
INSERT INTO `announcements` (`title`, `content`, `position`, `bg_color`, `text_color`, `link_url`, `start_time`, `end_time`, `can_close`, `sort_order`, `status`) VALUES
('系统维护通知', '本系统将于2024年12月31日22:00-次日02:00进行系统升级维护，期间可能短暂无法访问，请提前做好相关工作安排。', 1, '#dc3545', '#ffffff', NULL, '2024-01-01 00:00:00', '2025-12-31 23:59:59', 1, 1, 1),
('重要公告', '2024年度政务公开评估工作已启动，请各部门按照要求及时报送相关材料。点击查看详情。', 2, '#004d99', '#ffffff', 'faq.php', '2024-01-01 00:00:00', '2025-12-31 23:59:59', 1, 2, 1),
('温馨提示', '冬季天干物燥，请注意用火用电安全，做好防火措施。', 3, '#28a745', '#ffffff', NULL, '2024-01-01 00:00:00', '2025-12-31 23:59:59', 0, 3, 1);

-- ----------------------------
-- Table structure for emergency_departments
-- ----------------------------
DROP TABLE IF EXISTS `emergency_departments`;
CREATE TABLE `emergency_departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '部门名称',
  `icon` varchar(50) DEFAULT NULL COMMENT '图标',
  `sort_order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1启用 0禁用',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of emergency_departments
-- ----------------------------
INSERT INTO `emergency_departments` (`name`, `icon`, `sort_order`, `status`) VALUES
('应急办', 'bi-shield-exclamation', 1, 1),
('消防救援', 'bi-fire', 2, 1),
('医疗急救', 'bi-heart-pulse', 3, 1),
('公安', 'bi-person-badge', 4, 1),
('电力抢修', 'bi-lightning-charge', 5, 1),
('燃气抢修', 'bi-flame', 6, 1),
('供水抢修', 'bi-droplet', 7, 1),
('交通救援', 'bi-truck', 8, 1);

-- ----------------------------
-- Table structure for emergency_contacts
-- ----------------------------
DROP TABLE IF EXISTS `emergency_contacts`;
CREATE TABLE `emergency_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) NOT NULL COMMENT '部门ID',
  `name` varchar(50) NOT NULL COMMENT '姓名',
  `position` varchar(100) DEFAULT NULL COMMENT '职务',
  `emergency_phone` varchar(50) DEFAULT NULL COMMENT '24h应急电话',
  `office_phone` varchar(50) DEFAULT NULL COMMENT '办公电话',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `duty_time` varchar(100) DEFAULT NULL COMMENT '值班时间',
  `is_24h` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否24h应急',
  `sort_order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1启用 0禁用',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_department_id` (`department_id`),
  KEY `idx_is_24h` (`is_24h`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of emergency_contacts
-- ----------------------------
INSERT INTO `emergency_contacts` (`department_id`, `name`, `position`, `emergency_phone`, `office_phone`, `email`, `duty_time`, `is_24h`, `sort_order`, `status`) VALUES
(1, '张伟', '应急办主任', '13800138001', '010-88881001', 'zhangwei@gov.cn', '24小时值班', 1, 1, 1),
(1, '李娜', '应急办副主任', '13800138002', '010-88881002', 'lina@gov.cn', '工作日 9:00-17:00', 0, 2, 1),
(1, '王强', '应急协调员', '13800138003', '010-88881003', 'wangqiang@gov.cn', '24小时轮班', 1, 3, 1),
(2, '陈刚', '消防支队支队长', '13900139001', '010-88882001', 'chengang@fire.gov.cn', '24小时值班', 1, 1, 1),
(2, '刘芳', '消防中队中队长', '13900139002', '010-88882002', 'liufang@fire.gov.cn', '24小时轮班', 1, 2, 1),
(2, '赵磊', '消防员', '13900139003', '010-88882003', 'zhaolei@fire.gov.cn', '24小时轮班', 1, 3, 1),
(3, '孙医生', '急诊科主任', '13700137001', '010-88883001', 'sun@hospital.gov.cn', '24小时值班', 1, 1, 1),
(3, '周护士', '护士长', '13700137002', '010-88883002', 'zhou@hospital.gov.cn', '24小时轮班', 1, 2, 1),
(3, '吴医生', '急诊医生', '13700137003', '010-88883003', 'wu@hospital.gov.cn', '工作日 8:00-17:00', 0, 3, 1),
(4, '马警官', '派出所所长', '13600136001', '010-88884001', 'ma@police.gov.cn', '24小时值班', 1, 1, 1),
(4, '黄警官', '治安民警', '13600136002', '010-88884002', 'huang@police.gov.cn', '24小时轮班', 1, 2, 1),
(5, '韩工程师', '电力抢修队队长', '13500135001', '010-88885001', 'han@power.gov.cn', '24小时值班', 1, 1, 1),
(5, '徐师傅', '电力抢修员', '13500135002', '010-88885002', 'xu@power.gov.cn', '24小时轮班', 1, 2, 1),
(6, '冯工程师', '燃气抢修队队长', '13400134001', '010-88886001', 'feng@gas.gov.cn', '24小时值班', 1, 1, 1),
(6, '郑师傅', '燃气抢修员', '13400134002', '010-88886002', 'zheng@gas.gov.cn', '24小时轮班', 1, 2, 1),
(7, '何工程师', '供水抢修队队长', '13300133001', '010-88887001', 'he@water.gov.cn', '24小时值班', 1, 1, 1),
(7, '吕师傅', '供水抢修员', '13300133002', '010-88887002', 'lv@water.gov.cn', '24小时轮班', 1, 2, 1),
(8, '孔队长', '交通救援队队长', '13200132001', '010-88888001', 'kong@traffic.gov.cn', '24小时值班', 1, 1, 1),
(8, '曹师傅', '交通救援员', '13200132002', '010-88888002', 'cao@traffic.gov.cn', '24小时轮班', 1, 2, 1);

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

-- ----------------------------
-- Records of assessment_activities
-- ----------------------------
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

-- ----------------------------
-- Records of assessment_departments
-- ----------------------------
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

-- ----------------------------
-- Records of assessment_indicators
-- ----------------------------
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

-- ----------------------------
-- Records of townships
-- ----------------------------
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

-- ----------------------------
-- Records of service_tags
-- ----------------------------
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

-- ----------------------------
-- Records of service_points
-- ----------------------------
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
('北城社区便民服务站', 9, '北城街道居委会', '010-88880901', '工作日 9:00-17:00', 35.00, 20.00, 3.50, 1, 1),
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

-- ----------------------------
-- Records of service_point_tags
-- ----------------------------
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

-- ----------------------------
-- Records of emergency_plans
-- ----------------------------
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

-- ----------------------------
-- Records of emergency_plan_revisions
-- ----------------------------
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

-- ----------------------------
-- Records of purchases
-- ----------------------------
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

