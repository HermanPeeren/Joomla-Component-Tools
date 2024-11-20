CREATE TABLE IF NOT EXISTS `#__eventschedule_eventschedules` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`state` TINYINT(1)  NULL  DEFAULT 1,
`ordering` INT(11)  NULL  DEFAULT 0,
`checked_out` INT(11)  UNSIGNED,
`checked_out_time` DATETIME NULL  DEFAULT NULL ,
`path` VARCHAR(255)  NULL  DEFAULT "",
`level` INT(10)  NULL  DEFAULT 0,
`rgt` INT(11)  NULL  DEFAULT 0,
`lft` INT(11)  NULL  DEFAULT 0,
`schedule_name` VARCHAR(255)  NOT NULL  DEFAULT " ",
`alias` VARCHAR(255) COLLATE utf8_bin NULL ,
`access` INT(11)  NULL  DEFAULT 0,
`title` VARCHAR(255)  NOT NULL  DEFAULT "0",
`parent_id` INT(11)  NULL  DEFAULT 0,
PRIMARY KEY (`id`)
,KEY `idx_state` (`state`)
,KEY `idx_checked_out` (`checked_out`)
,KEY `idx_access` (`access`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__eventschedule_containers` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`state` TINYINT(1)  NULL  DEFAULT 1,
`ordering` INT(11)  NULL  DEFAULT 0,
`checked_out` INT(11)  UNSIGNED,
`checked_out_time` DATETIME NULL  DEFAULT NULL ,
`container_name` VARCHAR(255)  NOT NULL ,
`alias` VARCHAR(255)  NULL  DEFAULT "",
`schedule` INT(10)  NOT NULL  DEFAULT 0,
PRIMARY KEY (`id`)
,KEY `idx_state` (`state`)
,KEY `idx_checked_out` (`checked_out`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `#__eventschedule_containers_schedule` ON `#__eventschedule_containers`(`schedule`);

CREATE TABLE IF NOT EXISTS `#__eventschedule_sections` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`ordering` INT(11)  NULL  DEFAULT 0,
`checked_out` INT(11)  UNSIGNED,
`checked_out_time` DATETIME NULL  DEFAULT NULL ,
`section_name` VARCHAR(255)  NOT NULL ,
`state` TINYINT(1)  NULL  DEFAULT 1,
`container_ids` TEXT NULL ,
`schedule` INT(10)  NOT NULL  DEFAULT 0,
PRIMARY KEY (`id`)
,KEY `idx_checked_out` (`checked_out`)
,KEY `idx_state` (`state`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `#__eventschedule_sections_schedule` ON `#__eventschedule_sections`(`schedule`);

CREATE TABLE IF NOT EXISTS `#__eventschedule_actors` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`state` TINYINT(1)  NULL  DEFAULT 1,
`checked_out` INT(11)  UNSIGNED,
`checked_out_time` DATETIME NULL  DEFAULT NULL ,
`actor_name` VARCHAR(255)  NOT NULL ,
`actor_image` TEXT NULL ,
`biography` TEXT NULL ,
`website` VARCHAR(255)  NULL  DEFAULT "",
`events` TEXT NULL ,
`schedule` INT(10)  NOT NULL  DEFAULT 0,
PRIMARY KEY (`id`)
,KEY `idx_state` (`state`)
,KEY `idx_checked_out` (`checked_out`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `#__eventschedule_actors_schedule` ON `#__eventschedule_actors`(`schedule`);

CREATE TABLE IF NOT EXISTS `#__eventschedule_events` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`checked_out` INT(11)  UNSIGNED,
`checked_out_time` DATETIME NULL  DEFAULT NULL ,
`created_by` INT(11)  NULL  DEFAULT 0,
`modified_by` INT(11)  NULL  DEFAULT 0,
`event_name` VARCHAR(255)  NOT NULL ,
`short_description` TEXT NULL ,
`long_description` TEXT NULL ,
`duration` DOUBLE NULL ,
`event_type` INT(10)  NULL  DEFAULT 0,
`locators` TEXT NULL ,
`actors` TEXT NULL ,
`state` TINYINT(1)  NULL  DEFAULT 1,
`schedule` INT(10)  NOT NULL  DEFAULT 0,
PRIMARY KEY (`id`)
,KEY `idx_checked_out` (`checked_out`)
,KEY `idx_created_by` (`created_by`)
,KEY `idx_modified_by` (`modified_by`)
,KEY `idx_state` (`state`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `#__eventschedule_events_event_type` ON `#__eventschedule_events`(`event_type`);

CREATE INDEX `#__eventschedule_events_schedule` ON `#__eventschedule_events`(`schedule`);

CREATE TABLE IF NOT EXISTS `#__eventschedule_event_types` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`state` TINYINT(1)  NULL  DEFAULT 1,
`ordering` INT(11)  NULL  DEFAULT 0,
`checked_out` INT(11)  UNSIGNED,
`checked_out_time` DATETIME NULL  DEFAULT NULL ,
`created_by` INT(11)  NULL  DEFAULT 0,
`modified_by` INT(11)  NULL  DEFAULT 0,
`event_type_name` VARCHAR(255)  NOT NULL ,
`css_class` VARCHAR(255)  NULL  DEFAULT "",
`backgroundcolor` VARCHAR(22)  NULL  DEFAULT "",
`schedule` INT(10)  NOT NULL  DEFAULT 0,
PRIMARY KEY (`id`)
,KEY `idx_state` (`state`)
,KEY `idx_checked_out` (`checked_out`)
,KEY `idx_created_by` (`created_by`)
,KEY `idx_modified_by` (`modified_by`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `#__eventschedule_event_types_schedule` ON `#__eventschedule_event_types`(`schedule`);


INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `content_history_options`)
SELECT * FROM ( SELECT 'Actor','com_eventschedule.actor','{"special":{"dbtable":"#__eventschedule_actors","key":"id","type":"ActorTable","prefix":"Joomla\\\\Component\\\\Eventschedule\\\\Administrator\\\\Table\\\\"}}', CASE 
                                    WHEN 'rules' is null THEN ''
                                    ELSE ''
                                    END as rules, CASE 
                                    WHEN 'field_mappings' is null THEN ''
                                    ELSE ''
                                    END as field_mappings, '{"formFile":"administrator\/components\/com_eventschedule\/forms\/actor.xml", "hideFields":["checked_out","checked_out_time","params","language" ,"biography"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"group_id","targetTable":"#__usergroups","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"schedule","targetTable":"#__eventschedule_eventschedules","targetColumn":"id","displayColumn":"title"}]}') AS tmp
WHERE NOT EXISTS (
	SELECT type_alias FROM `#__content_types` WHERE (`type_alias` = 'com_eventschedule.actor')
) LIMIT 1;
