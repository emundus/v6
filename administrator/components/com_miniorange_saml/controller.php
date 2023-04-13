<?php


defined("\x5f\112\x45\x58\x45\103") or die;
class Miniorange_samlController extends JControllerLegacy
{
    public function display($Y0 = false, $df = false)
    {
        $mq = JFactory::getApplication()->input->getCmd("\x76\x69\145\167", "\x6d\x79\x61\x63\x63\157\165\x6e\x74\163");
        JFactory::getApplication()->input->set("\166\x69\x65\x77", $mq);
        parent::display($Y0, $df);
        return $this;
    }
}
