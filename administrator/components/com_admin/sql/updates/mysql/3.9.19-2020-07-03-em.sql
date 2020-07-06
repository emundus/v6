UPDATE `jos_assets`
SET `lft` = `lft` + (
	SELECT MAX(`t1`.`rgt`)+1-`t2`.`rgt`
	FROM `jos_assets` AS `t1` JOIN `jos_assets` as `t2`
	WHERE `t1`.`name` LIKE 'com_modules.module.%' AND `t2`.`name` = 'com_modules')
WHERE `lft` + (
	SELECT MAX(`t1`.`rgt`)+1-`t2`.`rgt`
	FROM `jos_assets` AS `t1` JOIN `jos_assets` as `t2`
	WHERE `t1`.`name` LIKE 'com_modules.module.%' AND `t2`.`name` = 'com_modules')
>= (
	SELECT MAX(`rgt`)
	FROM `jos_assets`
	WHERE `name` LIKE 'com_modules%')
AND `name` NOT LIKE 'com_modules%';


UPDATE `jos_assets`
SET `rgt` = `rgt` + (
	SELECT MAX(`t1`.`rgt`)+1-`t2`.`rgt`
	FROM `jos_assets` AS `t1` JOIN `jos_assets` as `t2`
	WHERE `t1`.`name` LIKE 'com_modules.module.%' AND `t2`.`name` = 'com_modules')
WHERE `rgt` + (
	SELECT MAX(`t1`.`rgt`)+1-`t2`.`rgt`
	FROM `jos_assets` AS `t1` JOIN `jos_assets` as `t2`
	WHERE `t1`.`name` LIKE 'com_modules.module.%' AND `t2`.`name` = 'com_modules')
>= (
	SELECT `rgt`
	FROM `jos_assets`
	WHERE `name` = 'com_modules')
AND `name` NOT LIKE 'com_modules.module%';