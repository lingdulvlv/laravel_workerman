/*
 Navicat Premium Data Transfer

 Source Server         : lvlv
 Source Server Type    : MySQL
 Source Server Version : 50719
 Source Host           : 127.0.0.1:33060
 Source Schema         : public

 Target Server Type    : MySQL
 Target Server Version : 50719
 File Encoding         : 65001

 Date: 13/04/2020 10:31:10
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for company
-- ----------------------------
DROP TABLE IF EXISTS `company`;
CREATE TABLE `company`  (
  `company_id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`company_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of company
-- ----------------------------
INSERT INTO `company` VALUES (1, '公司1');
INSERT INTO `company` VALUES (2, '公司2');
INSERT INTO `company` VALUES (3, '公司3');
INSERT INTO `company` VALUES (4, '公司4');
INSERT INTO `company` VALUES (5, '公司5');

-- ----------------------------
-- Table structure for record
-- ----------------------------
DROP TABLE IF EXISTS `record`;
CREATE TABLE `record`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `content` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `to_uid` int(11) NULL DEFAULT NULL,
  `company_id` int(11) NOT NULL,
  `create_time` int(20) NULL DEFAULT NULL,
  `read` int(2) NOT NULL DEFAULT 1 COMMENT '0：未读  1：已读  2：登录',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `passwd` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `company_id` int(4) NULL DEFAULT 0 COMMENT '公司人员（客服）：0为个人',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES (1, '唉吆喂', 'e10adc3949ba59abbe56e057f20f883e', 0);
INSERT INTO `user` VALUES (2, '似水流年', 'e10adc3949ba59abbe56e057f20f883e', 0);
INSERT INTO `user` VALUES (3, '上善若水', 'e10adc3949ba59abbe56e057f20f883e', 0);
INSERT INTO `user` VALUES (4, 'G1客服1', 'e10adc3949ba59abbe56e057f20f883e', 1);
INSERT INTO `user` VALUES (5, 'G1客服2', 'e10adc3949ba59abbe56e057f20f883e', 1);
INSERT INTO `user` VALUES (6, 'G2客服1', 'e10adc3949ba59abbe56e057f20f883e', 2);
INSERT INTO `user` VALUES (7, 'G3客服1', 'e10adc3949ba59abbe56e057f20f883e', 3);
INSERT INTO `user` VALUES (8, 'G3客服2', 'e10adc3949ba59abbe56e057f20f883e', 3);
INSERT INTO `user` VALUES (9, 'G3客服3', 'e10adc3949ba59abbe56e057f20f883e', 3);
INSERT INTO `user` VALUES (10, 'G4客服1', 'e10adc3949ba59abbe56e057f20f883e', 4);
INSERT INTO `user` VALUES (11, 'G4客服2', 'e10adc3949ba59abbe56e057f20f883e', 4);

SET FOREIGN_KEY_CHECKS = 1;
