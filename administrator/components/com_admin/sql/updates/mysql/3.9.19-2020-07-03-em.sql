UPDATE jos_assets a,
    (
        SELECT MAX(`t1`.`rgt`)+1-`t2`.`rgt` AS diff_rgt
         FROM `jos_assets` AS `t1` JOIN `jos_assets` as `t2`
         WHERE `t1`.`name` LIKE 'com_modules.module.%' AND `t2`.`name` = 'com_modules'
    ) AS a2,
    (
        SELECT MAX(`rgt`) AS mrgt
         FROM `jos_assets`
         WHERE `name` LIKE 'com_modules%'
    ) AS a3

SET a.`lft` = a.`lft` + a2.diff_rgt
WHERE  a.`lft` + a2.diff_rgt >= a3.mrgt
AND a.`name` NOT LIKE 'com_modules%';


UPDATE `jos_assets` AS a,
    (
        SELECT MAX(`t1`.`rgt`)+1-`t2`.`rgt` AS diff_rgt
        FROM `jos_assets` AS `t1` JOIN `jos_assets` as `t2`
        WHERE `t1`.`name` LIKE 'com_modules.module.%' AND `t2`.`name` = 'com_modules'
    ) AS a2,
    (
        SELECT `rgt` AS mrgt
        FROM `jos_assets`
        WHERE `name` = 'com_modules'
    ) AS a3

SET `rgt` = `rgt` + a2.diff_rgt
WHERE `rgt` + a2.diff_rgt >= a3.mrgt
AND `name` NOT LIKE 'com_modules.module%';


