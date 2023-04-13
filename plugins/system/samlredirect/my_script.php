<?php


defined("\137\x4a\105\130\105\103") or die("\122\145\x73\164\x72\x69\143\164\x65\x64\40\141\x63\143\x65\163\x73");
class plgSystemSamlredirectInstallerScript
{
    public function install($sG)
    {
        $VM = JFactory::getDbo();
        $Ql = $VM->getQuery(true);
        $Ql->update("\43\137\137\145\x78\164\x65\156\x73\x69\157\156\163");
        $Ql->set($VM->quoteName("\x65\x6e\141\142\154\x65\x64") . "\40\x3d\40\61");
        $Ql->where($VM->quoteName("\145\154\145\155\145\x6e\x74") . "\40\75\x20" . $VM->quote("\x6d\x69\x6e\x69\157\162\x61\x6e\147\145\x73\x61\x6d\154"));
        $Ql->where($VM->quoteName("\164\171\160\145") . "\40\75\40" . $VM->quote("\x70\x6c\x75\147\151\156"));
        $VM->setQuery($Ql);
        $VM->execute();
        $Lw = $VM->getQuery(true);
        $Lw->update("\43\137\x5f\145\x78\x74\x65\156\163\x69\x6f\x6e\x73");
        $Lw->set($VM->quoteName("\x65\156\141\142\154\x65\144") . "\40\x3d\x20\x31");
        $Lw->where($VM->quoteName("\x65\x6c\145\155\x65\x6e\x74") . "\x20\75\40" . $VM->quote("\x73\x61\155\x6c\x6c\157\x67\157\x75\x74"));
        $Lw->where($VM->quoteName("\164\x79\160\x65") . "\x20\75\x20" . $VM->quote("\160\x6c\165\x67\151\156"));
        $VM->setQuery($Lw);
        $VM->execute();
        $fK = $VM->getQuery(true);
        $fK->update("\43\137\137\x65\x78\x74\145\x6e\x73\151\x6f\156\x73");
        $fK->set($VM->quoteName("\x65\x6e\141\142\154\x65\144") . "\x20\x3d\40\x31");
        $fK->where($VM->quoteName("\145\x6c\x65\x6d\x65\156\x74") . "\40\75\40" . $VM->quote("\x73\141\x6d\154\x72\145\x64\151\x72\x65\x63\x74"));
        $fK->where($VM->quoteName("\164\171\160\x65") . "\x20\75\x20" . $VM->quote("\160\x6c\x75\x67\x69\x6e"));
        $VM->setQuery($fK);
        $VM->execute();
    }
    public function uninstall($sG)
    {
    }
    public function update($sG)
    {
    }
    public function preflight($Ps, $sG)
    {
    }
    function postflight($Ps, $sG)
    {
    }
}
