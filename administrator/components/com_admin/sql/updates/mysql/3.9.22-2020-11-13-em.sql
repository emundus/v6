/*SET GLOBAL log_bin_trust_function_creators = 1;

DROP PROCEDURE IF EXISTS addFieldIfNotExists;
DROP FUNCTION IF EXISTS isFieldExisting;

CREATE FUNCTION isFieldExisting (table_name_IN VARCHAR(100), field_name_IN VARCHAR(100))
RETURNS INT
RETURN (
    SELECT COUNT(COLUMN_NAME)
    FROM INFORMATION_SCHEMA.columns
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = table_name_IN
    AND COLUMN_NAME = field_name_IN
);
CREATE PROCEDURE addFieldIfNotExists (
    IN table_name_IN VARCHAR(100)
    , IN field_name_IN VARCHAR(100)
    , IN field_definition_IN VARCHAR(100)
)
BEGIN
    SET @isFieldThere = isFieldExisting(table_name_IN, field_name_IN);
    IF (@isFieldThere = 0) THEN
        SET @ddl = CONCAT('ALTER TABLE ', table_name_IN);
        SET @ddl = CONCAT(@ddl, ' ', 'ADD COLUMN') ;
        SET @ddl = CONCAT(@ddl, ' ', field_name_IN);
        SET @ddl = CONCAT(@ddl, ' ', field_definition_IN);
        PREPARE stmt FROM @ddl;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END;

CALL addFieldIfNotExists ('jos_emundus_hikashop', 'status', 'INT(2) NULL DEFAULT NULL AFTER `order_id`');*/

ALTER TABLE `jos_emundus_hikashop` ADD COLUMN `status` INT(2) NULL DEFAULT NULL AFTER `order_id`;

ALTER TABLE `jos_emundus_hikashop` ADD INDEX(`status`);

ALTER TABLE `jos_emundus_hikashop` ADD CONSTRAINT jos_emundus_hikashop_ibfk_3 FOREIGN KEY if not exists (`status`) REFERENCES `jos_emundus_setup_status`(`step`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `jos_emundus_hikashop` DROP INDEX `fnum`, ADD INDEX `fnum` (`fnum`) USING BTREE;

ALTER TABLE `jos_emundus_hikashop` ADD UNIQUE (`fnum`, `status`);

ALTER TABLE `jos_emundus_setup_campaigns` ADD CONSTRAINT jos_emundus_setup_campaigns_ibfk_2 FOREIGN KEY if not exists (`profile_id`) REFERENCES `jos_emundus_setup_profiles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
