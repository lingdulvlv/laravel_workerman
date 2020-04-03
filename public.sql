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

 Date: 03/04/2020 16:04:48
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
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `passwd` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `img` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `company_id` int(4) NULL DEFAULT 0 COMMENT '公司人员（客服）：0为个人',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES (1, '唉吆喂', '123456', 'https://avatar.csdnimg.cn/C/A/0/3_assasin0308_1577329682.jpg', 0);
INSERT INTO `user` VALUES (2, '似水流年', '123456', 'https://avatar.csdnimg.cn/8/6/3/3_insist211314.jpg', 0);
INSERT INTO `user` VALUES (3, '上善若水', '123456', 'https://avatar.csdnimg.cn/3/8/8/2_qq_38257857.jpg', 0);

SET FOREIGN_KEY_CHECKS = 1;
