<?php


defined("\x5f\x4a\x45\130\105\x43") or die;
require_once JPATH_COMPONENT . "\x2f\150\145\154\x70\x65\x72\x73\57\x6d\x6f\55\163\x61\x6d\154\55\x75\x74\x69\154\x69\164\x79\x2e\160\x68\160";
require_once JPATH_COMPONENT . "\x2f\x68\145\x6c\160\145\162\x73\x2f\155\x6f\55\x73\x61\155\x6c\55\143\x75\163\x74\157\x6d\145\162\55\x73\145\x74\x75\x70\56\x70\x68\x70";
require_once JPATH_COMPONENT . "\x2f\150\x65\x6c\x70\145\162\163\57\155\157\137\x73\141\x6d\154\137\x73\x75\160\160\157\162\x74\56\x70\150\x70";
require_once JPATH_COMPONENT . "\x2f\x68\145\154\x70\145\x72\x73\x2f\x6d\151\156\x69\x6f\162\x61\x6e\x67\145\x5f\163\x61\155\154\x2e\x70\x68\160";
if (JFactory::getUser()->authorise("\143\157\162\x65\56\x6d\x61\156\141\x67\145", "\143\157\x6d\x5f\x6d\x69\x6e\151\157\162\141\x6e\x67\x65\137\x73\x61\x6d\x6c")) {
    goto pj;
}
throw new Exception(JText::_("\112\x45\122\x52\x4f\122\x5f\101\114\105\122\x54\116\x4f\101\x55\124\x48\117\x52"));
pj:
jimport("\x6a\157\157\x6d\x6c\x61\x2e\141\160\x70\154\151\x63\141\164\151\x6f\156\56\x63\157\x6d\160\x6f\156\x65\156\x74\56\x63\x6f\156\x74\x72\x6f\x6c\x6c\x65\162");
JLoader::registerPrefix("\115\x69\x6e\151\157\x72\141\156\147\145\x5f\x73\x61\x6d\154", JPATH_COMPONENT_ADMINISTRATOR);
$Io = JControllerLegacy::getInstance("\x4d\151\x6e\x69\157\x72\141\x6e\147\x65\x5f\163\141\155\154");
if (!empty(JFactory::getApplication()->input->get("\x74\x61\163\x6b"))) {
    goto EX;
}
$Io->execute('');
goto mi;
EX:
$Io->execute(JFactory::getApplication()->input->get("\164\x61\163\x6b"));
mi:
$Io->redirect();
