DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES ('DoctrineMigrations\\Version20240618135603'', ''2024-06-18 15:56:31'', ''10');


DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user` (`id`, `name`, `email`, `username`, `address`, `role`) VALUES ('1'', ''John Doe'', ''john.doe@example.com'', ''johndoe'', ''123 Main St'', ''USER');
INSERT INTO `user` (`id`, `name`, `email`, `username`, `address`, `role`) VALUES ('2'', ''Jane Smith'', ''jane.smith@example.com'', ''janesmith'', ''456 Elm St'', ''ADMIN');
INSERT INTO `user` (`id`, `name`, `email`, `username`, `address`, `role`) VALUES ('3'', ''Michael Johnson'', ''michael.j@example.com'', ''mjohnson'', ''789 Pine St'', ''USER');
INSERT INTO `user` (`id`, `name`, `email`, `username`, `address`, `role`) VALUES ('4'', ''Emily Davis'', ''emily.d@example.com'', ''emilydavis'', ''101 Oak St'', ''ADMIN');
INSERT INTO `user` (`id`, `name`, `email`, `username`, `address`, `role`) VALUES ('5'', ''David Brown'', ''david.b@example.com'', ''davidbrown'', ''202 Maple St'', ''USER');
INSERT INTO `user` (`id`, `name`, `email`, `username`, `address`, `role`) VALUES ('6'', ''Sarah Wilson'', ''sarah.w@example.com'', ''sarahwilson'', ''303 Birch St'', ''USER');
INSERT INTO `user` (`id`, `name`, `email`, `username`, `address`, `role`) VALUES ('7'', ''Daniel Lee'', ''daniel.l@example.com'', ''daniellee'', ''404 Cedar St'', ''ADMIN');
INSERT INTO `user` (`id`, `name`, `email`, `username`, `address`, `role`) VALUES ('8'', ''Jessica Martinez'', ''jessica.m@example.com'', ''jessicam'', ''505 Walnut St'', ''USER');
INSERT INTO `user` (`id`, `name`, `email`, `username`, `address`, `role`) VALUES ('9'', ''Paul Garcia'', ''paul.g@example.com'', ''paulgarcia'', ''606 Ash St'', ''USER');
INSERT INTO `user` (`id`, `name`, `email`, `username`, `address`, `role`) VALUES ('10'', ''Laura Clark'', ''laura.c@example.com'', ''lauraclark'', ''707 Cherry St'', ''ADMIN');


