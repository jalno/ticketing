CREATE TABLE `ticketing_departments` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
	`users` text COLLATE utf8mb4_general_ci,
	`status` tinyint(4) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `ticketing_departments_params` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`department` int(11) NOT NULL,
	`name` varchar(255) NOT NULL,
	`value` text NOT NULL,
	PRIMARY KEY (`id`),
	KEY `department` (`department`),
	CONSTRAINT `ticketing_departments_params_ibfk_1` FOREIGN KEY (`department`) REFERENCES `ticketing_departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ticketing_departments_worktimes` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`department` int(11) NOT NULL,
	`day` int(11) NOT NULL,
	`time_start` int(11) NOT NULL,
	`time_end` int(11) NOT NULL,
	`message` text,
	PRIMARY KEY (`id`),
	UNIQUE KEY `department_2` (`department`,`day`),
	KEY `department` (`department`),
	CONSTRAINT `ticketing_departments_worktimes_ibfk_1` FOREIGN KEY (`department`) REFERENCES `ticketing_departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ticketing_tickets` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`operator_id` int(11) DEFAULT NULL,
	`create_at` int(11) NOT NULL,
	`reply_at` int(11) NOT NULL,
	`title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
	`priority` int(11) NOT NULL,
	`department` int(11) NOT NULL,
	`client` int(11) NOT NULL,
	`status` int(11) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `client` (`client`),
	KEY `department` (`department`),
	KEY `operator_id` (`operator_id`),
	CONSTRAINT `ticketing_tickets_ibfk_1` FOREIGN KEY (`client`) REFERENCES `userpanel_users` (`id`) ON DELETE CASCADE,
	CONSTRAINT `ticketing_tickets_ibfk_2` FOREIGN KEY (`department`) REFERENCES `ticketing_departments` (`id`) ON DELETE CASCADE,
	CONSTRAINT `ticketing_tickets_ibfk_3` FOREIGN KEY (`operator_id`) REFERENCES `userpanel_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `ticketing_tickets_params` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`ticket` int(11) NOT NULL,
	`name` varchar(100) NOT NULL,
	`value` varchar(100) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `ticket` (`ticket`),
	CONSTRAINT `ticketing_tickets_params_ibfk_1` FOREIGN KEY (`ticket`) REFERENCES `ticketing_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ticketing_tickets_msgs` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`ticket` int(11) NOT NULL,
	`date` int(11) NOT NULL,
	`user` int(11) NOT NULL,
	`text` longtext COLLATE utf8mb4_general_ci NOT NULL,
	`format` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
	`status` int(11) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `ticket` (`ticket`),
	KEY `user` (`user`),
	CONSTRAINT `ticketing_tickets_msgs_ibfk_1` FOREIGN KEY (`user`) REFERENCES `userpanel_users` (`id`) ON DELETE CASCADE,
	CONSTRAINT `ticketing_tickets_msgs_ibfk_2` FOREIGN KEY (`ticket`) REFERENCES `ticketing_tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `ticketing_files` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`message` int(11) NOT NULL,
	`name` varchar(255) NOT NULL,
	`size` int(11) NOT NULL,
	`path` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `message` (`message`),
	CONSTRAINT `ticketing_files_ibfk_1` FOREIGN KEY (`message`) REFERENCES `ticketing_tickets_msgs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ticketing_templates` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `title` varchar(255) NOT NULL,
 `subject` varchar(255) DEFAULT NULL,
 `department_id` int(11) DEFAULT NULL,
 `content` text NOT NULL,
 `message_type` enum('1','2') CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
 `message_format` enum('html','markdown') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'html',
 `status` enum('1','2') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `title` (`title`),
 KEY `department_id` (`department_id`),
 CONSTRAINT `ticketing_templates_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `ticketing_departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ticketing_labels` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `title` varchar(100) NOT NULL,
 `description` varchar(255) DEFAULT NULL,
 `color` varchar(7) NOT NULL,
 `status` enum('1','2') NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ticketing_tickets_labels` (
 `ticket_id` int(11) NOT NULL,
 `label_id` int(11) NOT NULL,
 PRIMARY KEY (`ticket_id`,`label_id`),
 KEY `label_id` (`label_id`),
 CONSTRAINT `ticketing_tickets_labels_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `ticketing_tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT `ticketing_tickets_labels_ibfk_2` FOREIGN KEY (`label_id`) REFERENCES `ticketing_labels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO `options` (`name`, `value`, `autoload`) VALUES ('packages.ticketing.close.respitetime', '86400', '1');
