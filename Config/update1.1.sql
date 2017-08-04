-- Add timestamp fields to table
ALTER TABLE `tags` ADD `created_at` DATETIME NOT NULL ;
ALTER TABLE `tags` ADD `updated_at` DATETIME NOT NULL ;

-- Delete empty tags
DELETE FROM  `tags` WHERE  `tag`='';

-- The source column is now a varchar(64)
ALTER TABLE `tags` CHANGE `source` `source` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
