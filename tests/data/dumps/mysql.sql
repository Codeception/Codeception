CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `enabled` boolean DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;




insert  into `groups`(`id`,`name`,`enabled`,`created_at`) values (1,'coders',1,'2012-02-01 21:17:50');

insert  into `groups`(`id`,`name`,`enabled`,`created_at`) values (2,'jazzman',0,'2012-02-01 21:18:40');




CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `role` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_permissions` (`group_id`),
  KEY `FK_users` (`user_id`),
  CONSTRAINT `FK_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_permissions` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;





insert  into `permissions`(`id`,`user_id`,`group_id`,`role`) values (1,1,1,'member');

insert  into `permissions`(`id`,`user_id`,`group_id`,`role`) values (2,2,1,'member');

insert  into `permissions`(`id`,`user_id`,`group_id`,`role`) values (5,3,2,'member');

insert  into `permissions`(`id`,`user_id`,`group_id`,`role`) values (7,4,2,'admin');




CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;


insert  into `users`(`id`,`name`,`email`,`created_at`) values (1,'davert','davert@mail.ua','2012-02-01 21:17:04');

insert  into `users`(`id`,`name`,`email`,`created_at`) values (2,'nick','nick@mail.ua','2012-02-01 21:17:15');

insert  into `users`(`id`,`name`,`email`,`created_at`) values (3,'miles','miles@davis.com','2012-02-01 21:17:25');

insert  into `users`(`id`,`name`,`email`,`created_at`) values (4,'bird','charlie@parker.com','2012-02-01 21:17:39');




CREATE TABLE `order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;



