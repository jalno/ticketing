--
--
--
ALTER TABLE `ticketing_tickets` ADD `operator_id` INT NULL DEFAULT NULL AFTER `client`, ADD INDEX (`operator_id`); 
ALTER TABLE `ticketing_tickets` ADD FOREIGN KEY (`operator_id`) REFERENCES `ticketing_departments`(`id`) ON DELETE SET NULL ON UPDATE RESTRICT; 

--
-- Commit: 1bfd7e8d0e1967b46267d9f411906ea4d049ee00
-- Date:   Fri Jan 17 17:24:54 2020 +0330
--
ALTER TABLE `ticketing_departments` ADD `status` TINYINT NOT NULL AFTER `users`; 


--
-- Commit: 4f3874d43cc2028f47d9474a923043966672c8be
-- Date: Wed, 01 Apr 2020 01:20:30 +0430
--
INSERT INTO `options` (`name`, `value`, `autoload`) VALUES ('packages.ticketing.close.respitetime', '86400', '1');


--
--	Commit: 4bca3fd25db3f20f6d3cb3833bdbd807d00c5d32
--	Author: Hossein Hosni <hosni.hossein@gmail.com>
--	Date:	Wed Apr 15 13:31:42 2020 +0430
--	Fix #65 - Compatibility to UserPanel new permissions style
--
UPDATE `userpanel_usertypes_permissions` SET name = REPLACE(`name`,'ticketing_department','ticketing_settings_departments') WHERE `name` LIKE 'ticketing_department%';
UPDATE `userpanel_usertypes_permissions` SET name = REPLACE(`name`,'ticketing_files_download','ticketing_files-download') WHERE `name` LIKE 'ticketing_files_download';


--
--	Commit: adb36d319f8d713ec71077001e923f9d95675eea
--	Author: Hossein Hosni <hosni.hossein@gmail.com>
--	Date:   Sun Jun 14 15:31:39 2020 +0430
--	#70 Make Departments Paramable
--
CREATE TABLE `ticketing_departments_params` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`department` int(11) NOT NULL,
	`name` varchar(255) NOT NULL,
	`value` text NOT NULL,
	PRIMARY KEY (`id`),
	KEY `department` (`department`),
	CONSTRAINT `ticketing_departments_params_ibfk_1` FOREIGN KEY (`department`) REFERENCES `ticketing_departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Commit: -
--
ALTER TABLE `ticketing_tickets_msgs` CHANGE `text` `text` longtext COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `user`;
