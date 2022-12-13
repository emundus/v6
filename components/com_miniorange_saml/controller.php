<?php


defined("\x5f\112\105\x58\105\x43") or die;
jimport("\152\157\x6f\155\154\x61\56\141\160\x70\x6c\x69\143\x61\164\151\157\x6e\x2e\143\x6f\155\x70\x6f\x6e\145\156\x74\56\143\157\156\164\x72\x6f\154\x6c\x65\162");
class Miniorange_samlController extends JControllerLegacy
{
    public function display($bg = false, $Ph = false)
    {
        $Ul = JFactory::getApplication()->input->getCmd("\166\x69\145\x77", "\155\171\x61\143\x63\x6f\x75\x6e\x74\x73");
        JFactory::getApplication()->input->set("\x76\x69\145\167", $Ul);
        parent::display($bg, $Ph);
        return $this;
    }
}
