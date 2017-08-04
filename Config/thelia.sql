
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- tags
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `tags`;

CREATE TABLE `tags`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `tag` VARCHAR(255),
    `source` VARCHAR(64),
    `source_id` INTEGER,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
