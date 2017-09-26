<?php
defined('_JEXEC') or die; 

$lang = JFactory::getLanguage();
$locallang = $lang->getTag(); 
if ($locallang == "fr-FR")
    setlocale(LC_TIME, 'fr', 'fr_FR', 'french', 'fra', 'fra_FRA', 'fr_FR.ISO_8859-1', 'fra_FRA.ISO_8859-1', 'fr_FR.utf8', 'fr_FR.utf-8', 'fra_FRA.utf8', 'fra_FRA.utf-8');
else
    setlocale (LC_ALL, 'en_GB');

?>




