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

SET FOREIGN_KEY_CHECKS = 1;

