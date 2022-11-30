<?php


defined("\x5f\112\x45\130\105\103") or die("\122\x65\x73\164\162\151\143\x74\x65\144\x20\141\x63\143\x65\163\163");
class plgSystemSamlredirectInstallerScript
{
    public function install($VW)
    {
        $F7 = JFactory::getDbo();
        $bm = $F7->getQuery(true);
        $bm->update("\43\137\137\145\170\164\145\156\x73\x69\x6f\x6e\x73");
        $bm->set($F7->quoteName("\x65\156\x61\142\x6c\x65\x64") . "\x20\75\x20\x31");
        $bm->where($F7->quoteName("\x65\154\145\155\x65\x6e\164") . "\x20\x3d\40" . $F7->quote("\x6d\151\156\x69\157\162\x61\x6e\x67\145\163\x61\x6d\x6c"));
        $bm->where($F7->quoteName("\164\x79\x70\x65") . "\x20\x3d\x20" . $F7->quote("\x70\154\x75\x67\x69\x6e"));
        $F7->setQuery($bm);
        $F7->execute();
        $S8 = $F7->getQuery(true);
        $S8->update("\x23\x5f\137\145\170\164\145\156\x73\x69\157\x6e\163");
        $S8->set($F7->quoteName("\145\156\141\x62\154\x65\x64") . "\x20\75\40\x31");
        $S8->where($F7->quoteName("\145\154\x65\155\145\156\x74") . "\40\x3d\x20" . $F7->quote("\x73\x61\155\154\154\157\147\x6f\x75\164"));
        $S8->where($F7->quoteName("\x74\x79\160\x65") . "\40\75\40" . $F7->quote("\160\x6c\165\x67\x69\x6e"));
        $F7->setQuery($S8);
        $F7->execute();
        $vW = $F7->getQuery(true);
        $vW->update("\43\x5f\x5f\145\170\x74\x65\156\x73\x69\157\x6e\163");
        $vW->set($F7->quoteName("\x65\156\141\x62\x6c\x65\144") . "\x20\75\40\x31");
        $vW->where($F7->quoteName("\x65\x6c\145\155\x65\156\x74") . "\40\x3d\x20" . $F7->quote("\x73\x61\155\x6c\x72\x65\144\x69\x72\x65\143\x74"));
        $vW->where($F7->quoteName("\164\x79\160\x65") . "\x20\x3d\x20" . $F7->quote("\x70\154\165\x67\151\x6e"));
        $F7->setQuery($vW);
        $F7->execute();
    }
    public function uninstall($VW)
    {
    }
    public function update($VW)
    {
    }
    public function preflight($Zu, $VW)
    {
    }
    function postflight($Zu, $VW)
    {
    }
}
