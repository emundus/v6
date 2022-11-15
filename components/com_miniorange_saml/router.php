<?php


defined("\137\x4a\105\x58\105\103") or die;
JLoader::registerPrefix("\x4d\x69\156\x69\x6f\162\141\x6e\x67\x65\x5f\163\141\x6d\154", JPATH_SITE . "\57\143\157\155\x70\x6f\156\x65\x6e\164\163\x2f\143\157\155\x5f\x6d\x69\x6e\x69\157\x72\141\156\x67\145\137\x73\x61\155\x6c\x2f");
class Miniorange_samlRouter extends JComponentRouterBase
{
    public function build(&$qH)
    {
        $q0 = array();
        $Ul = null;
        if (!isset($qH["\x74\141\163\153"])) {
            goto kq;
        }
        $O5 = explode("\x2e", $qH["\x74\141\163\x6b"]);
        $q0[] = implode("\57", $O5);
        $Ul = $O5[0];
        unset($qH["\x74\x61\163\153"]);
        kq:
        if (!isset($qH["\166\x69\145\x77"])) {
            goto mt;
        }
        $q0[] = $qH["\166\x69\145\x77"];
        $Ul = $qH["\166\151\145\167"];
        unset($qH["\x76\x69\x65\167"]);
        mt:
        if (!isset($qH["\151\x64"])) {
            goto Zp;
        }
        if ($Ul !== null) {
            goto GQ;
        }
        $q0[] = $qH["\x69\x64"];
        goto V9;
        GQ:
        $q0[] = $qH["\151\144"];
        V9:
        unset($qH["\x69\144"]);
        Zp:
        return $q0;
    }
    public function parse(&$q0)
    {
        $aw = array();
        $aw["\166\151\x65\x77"] = array_shift($q0);
        $BF = Miniorange_samlHelpersMiniorange_saml::getModel($aw["\166\151\x65\167"]);
        Qa:
        if (empty($q0)) {
            goto wK;
        }
        $P1 = array_pop($q0);
        if (is_numeric($P1)) {
            goto Vo;
        }
        $aw["\164\141\x73\x6b"] = $aw["\166\x69\x65\167"] . "\x2e" . $P1;
        goto yq;
        Vo:
        $aw["\x69\x64"] = $P1;
        yq:
        goto Qa;
        wK:
        return $aw;
    }
}
