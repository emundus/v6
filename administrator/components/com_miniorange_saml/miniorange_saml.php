<?php


defined("\137\112\105\130\105\x43") or die;
require_once JPATH_COMPONENT . "\x2f\x68\x65\x6c\x70\145\x72\163\57\x6d\x6f\x2d\x73\x61\x6d\154\x2d\x75\x74\x69\x6c\x69\164\x79\56\160\x68\x70";
require_once JPATH_COMPONENT . "\x2f\x68\145\154\x70\145\162\163\x2f\155\x6f\x2d\163\141\155\154\x2d\x63\x75\163\164\x6f\x6d\145\162\55\x73\145\164\165\160\x2e\160\150\160";
require_once JPATH_COMPONENT . "\x2f\x68\145\x6c\160\x65\162\163\57\155\x6f\137\x73\x61\x6d\154\x5f\x73\x75\x70\160\157\x72\164\x2e\160\x68\160";
require_once JPATH_COMPONENT . "\57\150\145\154\x70\x65\162\x73\x2f\155\151\x6e\151\x6f\x72\x61\x6e\x67\145\137\163\141\155\154\56\x70\x68\x70";
if (JFactory::getUser()->authorise("\x63\x6f\x72\x65\x2e\155\x61\x6e\141\x67\x65", "\143\157\155\x5f\155\x69\x6e\151\157\162\x61\156\x67\x65\137\163\x61\x6d\154")) {
    goto h4;
}
throw new Exception(JText::_("\112\x45\122\122\117\x52\137\x41\x4c\x45\122\x54\x4e\117\101\x55\x54\110\117\122"));
h4:
jimport("\x6a\x6f\157\155\x6c\x61\56\141\x70\160\154\x69\x63\141\164\151\x6f\156\x2e\143\157\155\x70\x6f\156\145\x6e\x74\x2e\143\x6f\x6e\164\x72\x6f\154\154\x65\162");
JLoader::registerPrefix("\x4d\x69\156\151\x6f\162\141\156\147\145\137\x73\141\155\x6c", JPATH_COMPONENT_ADMINISTRATOR);
$bW = JControllerLegacy::getInstance("\x4d\x69\x6e\151\x6f\x72\141\156\x67\x65\x5f\163\x61\155\x6c");
if (!empty(JFactory::getApplication()->input->get("\x74\x61\x73\x6b"))) {
    goto DQ;
}
$bW->execute('');
goto kY;
DQ:
$bW->execute(JFactory::getApplication()->input->get("\164\x61\163\x6b"));
kY:
$bW->redirect();
