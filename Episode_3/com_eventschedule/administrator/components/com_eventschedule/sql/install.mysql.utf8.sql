CREATE TABLE IF NOT EXISTS `#__eventschedule_containers` (
    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    `container_name` varchar(255),
    `ordering` int(11),
    PRIMARY KEY (`id`)
)  ENGINE=InnoDB DEFAULT COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__eventschedule_sections` (
    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    `section_name` varchar(255),
    `ordering` int(11),
    PRIMARY KEY (`id`)
)  ENGINE=InnoDB DEFAULT COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__eventschedule_actors` (
    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    `actor_name` varchar(255),
    `actor_image` varchar(255),
    `biography` text,
    PRIMARY KEY (`id`)
)  ENGINE=InnoDB DEFAULT COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__eventschedule_events` (
    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_name` varchar(255),
    `short_description` text,
    `long_description` text,
    `duration` int NOT NULL DEFAULT 0,
    `locators` text,
    `event_type_id` bigint UNSIGNED,
    PRIMARY KEY (`id`)
)  ENGINE=InnoDB DEFAULT COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__eventschedule_event_types` (
    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_type_name` varchar(255),
    `css_class` varchar(255),
    `background_color` varchar(6),
    PRIMARY KEY (`id`)
)  ENGINE=InnoDB DEFAULT COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__eventschedule_container_section` (
    `container_id` bigint(20) UNSIGNED,
    `section_id` bigint(20) UNSIGNED,
    PRIMARY KEY (`container_id`, `section_id`)
)  ENGINE=InnoDB DEFAULT COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__eventschedule_actor_event` (
    `actor_id` bigint(20) UNSIGNED,
    `event_id` bigint(20) UNSIGNED,
    PRIMARY KEY (`actor_id`, `event_id`)
)  ENGINE=InnoDB DEFAULT COLLATE utf8mb4_unicode_ci;