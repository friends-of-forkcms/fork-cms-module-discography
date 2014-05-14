DROP TABLE IF EXISTS `discography_albums`, `discography_categories`, `discography_albums_tracks`;

CREATE  TABLE IF NOT EXISTS `discography_albums` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`category_id` INT(11) NULL,
	`meta_id` INT(11) NULL,
	`title` VARCHAR(255) NULL,
	`release_date` DATE NULL,
	`image` VARCHAR(255) NULL,
	`record_company` VARCHAR(255) NULL,
	`link_itunes` VARCHAR(255) NULL,
	`link_spotify` VARCHAR(255) NULL,
	`created_on` datetime NOT NULL,
	`edited_on` datetime NOT NULL,
	`hidden` enum('Y','N') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `discography_categories` (
	`id` int(11) NOT NULL auto_increment,
	`meta_id` int(11) NOT NULL,
	`language` varchar(5) NOT NULL,
	`title` varchar(255) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE  TABLE IF NOT EXISTS `discography_albums_tracks` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`album_id` int(11) NOT NULL,
	`title` varchar(255) NOT NULL,
	`duration` TIME NULL,
	`link_youtube` VARCHAR(255) NULL,
	`sequence` int(11) NOT NULL,
	`created_on` datetime NOT NULL,
	`edited_on` datetime NOT NULL,
	`hidden` enum('Y','N') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
