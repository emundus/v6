<?php


defined("\x5f\x4a\x45\130\x45\x43") or die;
jimport("\152\157\157\155\x6c\141\x2e\141\160\x70\x6c\x69\143\x61\x74\151\x6f\156\56\143\157\x6d\x70\x6f\x6e\x65\x6e\x74\56\x63\157\156\164\162\157\154\154\x65\x72");
JLoader::registerPrefix("\115\x69\x6e\x69\x6f\162\x61\x6e\147\x65\x5f\x73\x61\155\x6c", JPATH_COMPONENT);
JLoader::register("\115\151\x6e\151\157\x72\141\156\x67\x65\x5f\x73\x61\155\154\x43\157\x6e\x74\162\157\154\154\x65\x72", JPATH_COMPONENT . "\57\x63\x6f\x6e\164\162\x6f\154\x6c\x65\162\56\160\150\160");
$Io = JControllerLegacy::getInstance("\115\151\156\151\157\x72\141\156\x67\x65\137\x73\141\155\x6c");
$Io->execute(JFactory::getApplication()->input->get("\164\141\x73\x6b"));
$Io->redirect();
