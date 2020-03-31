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
