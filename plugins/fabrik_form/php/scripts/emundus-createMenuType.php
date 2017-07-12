<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1.5: createMenuType.php 89 2017-01-31 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2016 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Création d'un nouveau type de menu propre dédié au nouveau profil utilisateur
 */

$app            = JFactory::getApplication();
$db             = JFactory::getDBO();

$menutype = $fabrikFormData['menutype'][0];
$published = $fabrikFormData['published'];
$label = $fabrikFormData['label_raw'];
$description = $fabrikFormData['description_raw'];
$id = $fabrikFormData['id_raw'];

if ($menutype == -1) {
  if ($published == 1) {
    $menutype = 'menu-profile'.$id;
    $title = 'Formulaire '.$label;
  } else {
    $menutype = 'menu-'.$id;
    $title = 'Menu '.$label;
  }

  $query = 'INSERT INTO `#__menu_types` (`menutype`, `title`, `description`) 
              VALUES ('.$db->quote($menutype).', '.$db->quote($title).', '.$db->quote($description).')';
  try {
      $db->setQuery($query);
      $db->execute();
  } catch (Exception $e) {
      // catch any database errors.
  }

  $query = 'UPDATE `#__emundus_setup_profiles` SET menutype = '.$db->quote($menutype).' WHERE id='.$id;
  try {
      $db->setQuery($query);
      $db->execute();
  } catch (Exception $e) {
      // catch any database errors.
  }
}

?>