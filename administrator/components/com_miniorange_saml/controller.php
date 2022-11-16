<?php


defined("\137\112\x45\130\x45\x43") or die;
class Miniorange_samlController extends JControllerLegacy
{
    public function display($bg = false, $Ph = false)
    {
        $Ul = JFactory::getApplication()->input->getCmd("\166\151\x65\167", "\155\x79\x61\143\x63\x6f\x75\x6e\x74\163");
        JFactory::getApplication()->input->set("\166\151\x65\167", $Ul);
        parent::display($bg, $Ph);
        return $this;
    }
}
