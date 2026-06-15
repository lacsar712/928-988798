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

SET FOREIGN_KEY_CHECKS = 1;
