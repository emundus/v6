<?php


defined("\x5f\112\x45\130\x45\103") or die;
jimport("\x6a\x6f\x6f\155\154\x61\56\x61\160\160\154\x69\143\x61\164\x69\157\x6e\56\x63\157\155\x70\x6f\x6e\145\x6e\164\x2e\x63\157\x6e\x74\x72\157\154\x6c\x65\x72");
class Miniorange_samlController extends JControllerLegacy
{
    public function display($Y0 = false, $df = false)
    {
        $mq = JFactory::getApplication()->input->getCmd("\x76\151\145\167", "\x6d\x79\x61\x63\143\x6f\165\156\164\x73");
        JFactory::getApplication()->input->set("\166\151\145\167", $mq);
        parent::display($Y0, $df);
        return $this;
    }
}
