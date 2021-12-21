UPDATE `jos_menu` SET `title` = "Formulaires du déposant"  WHERE `jos_menu`.`title`like "Formulaire" AND `jos_menu`.`note` like "1|r";
UPDATE `jos_menu` SET `title` = "Commentaires internes"  WHERE `jos_menu`.`title`like "Commentaires" AND `jos_menu`.`note` like "10|c";
UPDATE `jos_menu` SET `title` = "Messagerie instantanée"  WHERE `jos_menu`.`title`like "Messages" AND `jos_menu`.`note` like "36|c";
