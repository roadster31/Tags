-- Delete empty tags
DELETE FROM  `tags` WHERE  `tag`='';

-- The source column is now a varchar(64)
ALTER TABLE `tags` CHANGE `source` `source` VARCHAR(64);
