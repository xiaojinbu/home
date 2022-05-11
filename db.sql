/*
Navicat MySQL Data Transfer

Source Server         : 本地数据库
Source Server Version : 50726
Source Host           : localhost:3306
Source Database       : test

Target Server Type    : MYSQL
Target Server Version : 50726
File Encoding         : 65001

Date: 2022-05-11 12:53:16
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for auth_assignment
-- ----------------------------
DROP TABLE IF EXISTS `auth_assignment`;
CREATE TABLE `auth_assignment` (
  `item_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`item_name`,`user_id`),
  CONSTRAINT `auth_assignment_ibfk_1` FOREIGN KEY (`item_name`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='角色授权';

-- ----------------------------
-- Records of auth_assignment
-- ----------------------------
INSERT INTO `auth_assignment` VALUES ('企业管理员', '150', '1646732726');

-- ----------------------------
-- Table structure for auth_item
-- ----------------------------
DROP TABLE IF EXISTS `auth_item`;
CREATE TABLE `auth_item` (
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `rule_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data` text COLLATE utf8_unicode_ci,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`),
  KEY `rule_name` (`rule_name`),
  CONSTRAINT `auth_item_ibfk_1` FOREIGN KEY (`rule_name`) REFERENCES `auth_rule` (`name`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='权限项';

-- ----------------------------
-- Records of auth_item
-- ----------------------------
INSERT INTO `auth_item` VALUES ('企业管理员', '1', '企业管理员', null, null, '1516329321', '1547019438');

-- ----------------------------
-- Table structure for auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `auth_rule`;
CREATE TABLE `auth_rule` (
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='规则';

-- ----------------------------
-- Records of auth_rule
-- ----------------------------

-- ----------------------------
-- Table structure for log
-- ----------------------------
DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL COMMENT '管理员id',
  `user_name` varchar(80) NOT NULL DEFAULT '' COMMENT '管理员名',
  `route` varchar(100) NOT NULL DEFAULT '' COMMENT '操作的路由',
  `name` varchar(150) NOT NULL DEFAULT '' COMMENT '记录详情',
  `method` varchar(10) NOT NULL DEFAULT '' COMMENT '操作方法',
  `get_data` text NOT NULL COMMENT 'get数据',
  `post_data` text NOT NULL COMMENT '改变的数据',
  `ip` varchar(50) NOT NULL COMMENT '操作IP地址',
  `agent` text NOT NULL,
  `md5` varchar(32) NOT NULL DEFAULT '',
  `created_at` varchar(50) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1442485 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of log
-- ----------------------------
INSERT INTO `log` VALUES ('1442481', '150', '马永春', '/system/*', '空', 'GET', '[]', '', '::1', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.45 Safari/537.36', '86d6d1c8603e7af920dddc4b3565df81', '1652241482.559');
INSERT INTO `log` VALUES ('1442482', '150', '马永春', '/admin/user/logout', '退出', 'POST', '[]', '{\"_csrf-backend\":\"kabGK2MA29J6atybXte9UlHQTgkKKbaVH0j_1gu1KSnDx65aJHOquUoptbYmkfoEB6YETDtz__ptH5ThYfIYQA==\"}', '::1', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.45 Safari/537.36', '928a91807a5d83c671d6c07609c51e3d', '1652241484.652');
INSERT INTO `log` VALUES ('1442483', '150', '马永春', '/admin/user/login', '登录', 'POST', '[]', '{\"_csrf-backend\":\"aHv69b_L_aV0yRBv54EDhd1PhlanIHXq9uowQHimFCI6GpKE-LiMzkSKeUKfx0TTiznME5Z6PIWEvVt3EuElSw==\",\"Login\":{\"phone\":\"15319629546\",\"password\":\"123456\"},\"login-button\":\"\"}', '::1', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.45 Safari/537.36', 'f742ca25c50a8a2e60eeba4fe3190a17', '1652241491.173');
INSERT INTO `log` VALUES ('1442484', '150', '马永春', '/system/*', '空', 'GET', '[]', '', '::1', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.45 Safari/537.36', '86d6d1c8603e7af920dddc4b3565df81', '1652241491.821');

-- ----------------------------
-- Table structure for session
-- ----------------------------
DROP TABLE IF EXISTS `session`;
CREATE TABLE `session` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) unsigned DEFAULT NULL COMMENT '管理员id',
  `ip` varchar(128) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '访客ip',
  `is_trusted` tinyint(1) DEFAULT '1' COMMENT '是否受信',
  `expire` int(11) DEFAULT NULL,
  `data` blob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of session
-- ----------------------------
INSERT INTO `session` VALUES ('2om938osgf38e1bgld8g8qb5hn', null, '', '1', '1652248366', 0x5F5F666C6173687C613A303A7B7D6C616E677C733A353A227A682D434E223B5F5F73616C65735F766F6C756D655F69647C693A3135303B);
INSERT INTO `session` VALUES ('7363k2q57ei6f74n1vm8ud3h86', null, '', '1', '1652244868', 0x5F5F666C6173687C613A303A7B7D6C616E677C733A353A227A682D434E223B5F5F73616C65735F766F6C756D655F69647C693A3135303B);
INSERT INTO `session` VALUES ('g0n2s30n7gpiouus6j98in8ck2', null, '', '1', '1652238621', 0x5F5F666C6173687C613A303A7B7D6C616E677C733A353A227A682D434E223B);
INSERT INTO `session` VALUES ('k4eqif6j6vp26tdrta1js3g44n', null, '', '1', '1652238613', 0x5F5F666C6173687C613A303A7B7D6C616E677C733A353A227A682D434E223B5F5F73616C65735F766F6C756D655F69647C693A3135303B);
INSERT INTO `session` VALUES ('rp4e6tt3bu2pdalinhhnf1cuen', null, '', '1', '1652245602', 0x5F5F666C6173687C613A303A7B7D6C616E677C733A353A227A682D434E223B5F5F73616C65735F766F6C756D655F69647C693A3135303B);
INSERT INTO `session` VALUES ('tbf1hl2bllp9sfvos2kg9d874l', null, '', '1', '1652243783', 0x5F5F666C6173687C613A303A7B7D6C616E677C733A353A227A682D434E223B5F5F73616C65735F766F6C756D655F69647C693A3135303B);

-- ----------------------------
-- Table structure for supplier
-- ----------------------------
DROP TABLE IF EXISTS `supplier`;
CREATE TABLE `supplier` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `code` char(3) CHARACTER SET ascii DEFAULT NULL,
  `t_status` enum('ok','hold') CHARACTER SET ascii NOT NULL DEFAULT 'ok',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of supplier
-- ----------------------------
INSERT INTO `supplier` VALUES ('3', 'aaa', 'ccc', 'ok');
INSERT INTO `supplier` VALUES ('4', '', '', 'hold');
INSERT INTO `supplier` VALUES ('6', 'fdf', 'fdf', 'ok');
INSERT INTO `supplier` VALUES ('7', '5435', '543', 'hold');
INSERT INTO `supplier` VALUES ('8', '5435', '654', 'ok');
INSERT INTO `supplier` VALUES ('9', '87687', '987', 'hold');
INSERT INTO `supplier` VALUES ('10', '321321', '321', 'ok');
INSERT INTO `supplier` VALUES ('11', '4324', '432', 'ok');
INSERT INTO `supplier` VALUES ('13', '5435', '523', 'ok');
INSERT INTO `supplier` VALUES ('14', '111111111111', '222', 'hold');

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_number` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '员工工号',
  `username` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `auth_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password_reset_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '手机号码',
  `status` smallint(6) NOT NULL DEFAULT '1',
  `manager_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '对应前台经理账号',
  `user_group_id` int(5) unsigned NOT NULL COMMENT '所属组id',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `company_id` int(11) NOT NULL DEFAULT '1' COMMENT '所属公司的ID',
  `multi_company` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '多公司配置1,2,3',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=157 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='员工';

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES ('150', '', '马永春', '', '$2y$13$RQnds2xiOgMnRboxGWMGMuxWcl/LjqK5xf14wIAJekaWQmmYY4X.a', null, '15319629546@qq.com', '15319629546', '1', '0', '16', '1628506445', '1646732726', '1', '1');

-- ----------------------------
-- Table structure for user_group
-- ----------------------------
DROP TABLE IF EXISTS `user_group`;
CREATE TABLE `user_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL DEFAULT '',
  `desc` text NOT NULL COMMENT '简介',
  `parent_id` int(11) unsigned NOT NULL,
  `order` int(5) unsigned NOT NULL DEFAULT '10',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `company_id` int(11) NOT NULL DEFAULT '1' COMMENT '所属公司的ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COMMENT='员工组表';

-- ----------------------------
-- Records of user_group
-- ----------------------------
INSERT INTO `user_group` VALUES ('16', '管理员', '管理员', '15', '2', '1547193233', '1547193233', '3');
