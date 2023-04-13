<?php


defined("\x5f\112\105\x58\x45\103") or die;
class Miniorange_samlHelpersMiniorange_saml
{
    public static function addSubmenu($mz = '')
    {
        JHtmlSidebar::addEntry(JText::_("\x43\117\115\x5f\x4d\111\x4e\111\x4f\x52\x41\x4e\107\x45\x5f\x53\x41\115\x4c\x5f\x54\x49\x54\x4c\x45\137\115\131\101\103\x43\117\x55\x4e\x54\x53"), "\x69\156\144\x65\x78\x2e\160\x68\160\x3f\x6f\x70\164\151\x6f\x6e\x3d\143\157\x6d\137\155\x69\x6e\x69\x6f\x72\x61\x6e\147\x65\x5f\x73\141\155\x6c\x26\x76\x69\x65\167\x3d\x6d\x79\x61\143\x63\x6f\165\156\x74\163", $mz == "\155\x79\x61\x63\143\x6f\x75\x6e\x74\x73");
    }
    public static function getActions()
    {
        $user = JFactory::getUser();
        $mb = new JObject();
        $CO = "\x63\157\x6d\x5f\155\x69\156\151\x6f\x72\141\156\147\x65\137\163\141\155\154";
        $c2 = array("\143\x6f\x72\145\x2e\x61\x64\x6d\x69\156", "\143\x6f\162\x65\x2e\x6d\141\156\x61\x67\145", "\x63\x6f\x72\145\56\143\162\x65\141\x74\x65", "\x63\157\162\x65\56\x65\144\x69\164", "\x63\157\162\x65\x2e\145\144\x69\x74\56\x6f\167\x6e", "\143\157\x72\145\56\x65\x64\x69\164\x2e\x73\164\x61\164\145", "\143\x6f\162\145\56\144\145\x6c\145\164\145");
        foreach ($c2 as $tZ) {
            $mb->set($tZ, $user->authorise($tZ, $CO));
            II:
        }
        ib:
        return $mb;
    }
}
