CREATE TABLE IF NOT EXISTS `adminlogin`.`users` (
 `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing user_id of each user, unique index',
 `user_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'user''s name, unique',
 `user_password_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'user''s password in salted and hashed format',
 `user_email` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'user''s email, unique',
 `user_password_reset_hash` char(40) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'user''s password reset code',
 `user_password_reset_timestamp` bigint(20) DEFAULT NULL COMMENT 'timestamp of the password reset request',
 `user_failed_logins` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'user''s failed login attemps',
 `user_last_failed_login` int(10) DEFAULT NULL COMMENT 'unix timestamp of last failed login attempt',
 `user_registration_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `user_rememberme_token` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'user''s remember-me cookie token',
 `user_registration_ip` varchar(39) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0.0.0.0',
 `user_password_change` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 or 1 boolean holding random password change status',
 `admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 or 1 boolean determining user''s admin status',
 PRIMARY KEY (`user_id`),
 UNIQUE KEY `user_name` (`user_name`),
 UNIQUE KEY `user_email` (`user_email`)
);


-- Dummy account with the following information
--    User Name: admin
--    Password: password
INSERT INTO `adminlogin`.`users` VALUES (
 '',
 'admin',
 '$2y$10$LkliNBfbVsHpEaby3QapJOcXIiLgtKeZpwFXLFVAmYMnJhcfhp.Z2',
 'admin@example.com',
 NULL,
 NULL,
 0,
 NULL,
 '2014-01-01 01:01:01',
 NULL,
 '0.0.0.0',
 1,
 1
);
