<?php


defined("\137\x4a\105\130\x45\x43") or die;
JLoader::registerPrefix("\x4d\151\x6e\151\157\162\141\x6e\147\x65\x5f\x73\141\x6d\x6c", JPATH_SITE . "\57\x63\x6f\155\160\x6f\x6e\x65\x6e\164\163\57\x63\157\155\x5f\x6d\151\156\x69\157\x72\x61\156\x67\145\137\x73\x61\155\x6c\57");
class Miniorange_samlRouter extends JComponentRouterBase
{
    public function build(&$zO)
    {
        $t8 = array();
        $mq = null;
        if (!isset($zO["\164\141\163\153"])) {
            goto fg;
        }
        $wF = explode("\x2e", $zO["\164\141\163\x6b"]);
        $t8[] = implode("\57", $wF);
        $mq = $wF[0];
        unset($zO["\x74\x61\x73\153"]);
        fg:
        if (!isset($zO["\166\x69\x65\167"])) {
            goto G4;
        }
        $t8[] = $zO["\166\151\x65\167"];
        $mq = $zO["\x76\x69\145\x77"];
        unset($zO["\x76\151\x65\167"]);
        G4:
        if (!isset($zO["\x69\144"])) {
            goto nn;
        }
        if ($mq !== null) {
            goto za;
        }
        $t8[] = $zO["\151\x64"];
        goto OZ;
        za:
        $t8[] = $zO["\x69\x64"];
        OZ:
        unset($zO["\151\144"]);
        nn:
        return $t8;
    }
    public function parse(&$t8)
    {
        $Z5 = array();
        $Z5["\166\151\x65\x77"] = array_shift($t8);
        $A8 = Miniorange_samlHelpersMiniorange_saml::getModel($Z5["\x76\x69\145\167"]);
        Qu:
        if (empty($t8)) {
            goto b3;
        }
        $dE = array_pop($t8);
        if (is_numeric($dE)) {
            goto yE;
        }
        $Z5["\x74\x61\163\153"] = $Z5["\x76\151\145\167"] . "\x2e" . $dE;
        goto wp;
        yE:
        $Z5["\151\x64"] = $dE;
        wp:
        goto Qu;
        b3:
        return $Z5;
    }
}
