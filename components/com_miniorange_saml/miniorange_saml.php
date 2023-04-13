<?php


defined("\137\112\x45\130\x45\x43") or die;
jimport("\x6a\157\157\x6d\154\141\x2e\x61\160\x70\154\x69\143\x61\x74\151\x6f\156\56\143\x6f\155\x70\157\156\x65\x6e\164\x2e\143\157\x6e\x74\162\x6f\x6c\154\145\162");
JLoader::registerPrefix("\115\151\x6e\x69\x6f\162\141\x6e\x67\x65\137\163\141\x6d\x6c", JPATH_COMPONENT);
JLoader::register("\115\151\156\x69\157\x72\141\x6e\147\145\x5f\x73\141\x6d\154\x43\x6f\x6e\x74\x72\157\154\154\145\162", JPATH_COMPONENT . "\x2f\x63\157\156\x74\x72\x6f\154\154\145\162\x2e\x70\150\160");
$bW = JControllerLegacy::getInstance("\115\151\x6e\151\x6f\162\x61\156\x67\145\x5f\163\141\155\x6c");
$bW->execute(JFactory::getApplication()->input->get("\164\141\x73\153"));
$bW->redirect();
