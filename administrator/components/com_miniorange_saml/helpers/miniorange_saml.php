<?php


defined("\x5f\112\105\x58\x45\x43") or die;
class Miniorange_samlHelpersMiniorange_saml
{
    public static function addSubmenu($NY = '')
    {
        JHtmlSidebar::addEntry(JText::_("\103\117\x4d\137\x4d\x49\x4e\x49\x4f\x52\x41\116\107\105\137\123\101\x4d\x4c\137\124\111\x54\x4c\105\137\115\x59\101\x43\103\x4f\x55\116\x54\123"), "\151\156\x64\145\170\x2e\x70\150\x70\x3f\x6f\160\x74\x69\157\x6e\x3d\x63\x6f\155\x5f\x6d\x69\x6e\151\x6f\162\141\156\147\145\x5f\x73\141\155\x6c\x26\166\x69\x65\167\x3d\155\x79\x61\x63\143\x6f\165\x6e\x74\x73", $NY == "\155\x79\141\x63\143\x6f\x75\x6e\x74\x73");
    }
    public static function getActions()
    {
        $user = JFactory::getUser();
        $bZ = new JObject();
        $A_ = "\143\157\155\137\155\x69\156\151\x6f\162\141\x6e\147\x65\137\x73\x61\155\x6c";
        $Rc = array("\x63\157\x72\x65\56\x61\144\155\x69\x6e", "\x63\x6f\x72\145\x2e\155\141\x6e\x61\x67\x65", "\x63\157\x72\145\56\143\162\x65\141\164\x65", "\143\157\162\145\x2e\145\144\151\x74", "\143\x6f\162\145\x2e\145\x64\x69\164\56\157\167\156", "\x63\157\162\145\56\145\144\151\x74\56\x73\164\x61\x74\x65", "\x63\157\162\145\x2e\144\145\154\145\x74\145");
        foreach ($Rc as $Cq) {
            $bZ->set($Cq, $user->authorise($Cq, $A_));
            Eb:
        }
        vQ:
        return $bZ;
    }
}
