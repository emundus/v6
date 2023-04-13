<?php


defined("\x5f\112\105\x58\105\103") or die;
class Miniorange_samlHelpersMiniorange_saml
{
    public static function getModel($F6)
    {
        $A8 = null;
        if (!file_exists(JPATH_SITE . "\x2f\x63\x6f\x6d\160\157\156\x65\156\164\x73\x2f\143\x6f\155\137\155\x69\156\151\157\x72\141\156\x67\x65\x5f\163\x61\155\x6c\57\155\157\144\x65\154\163\x2f" . strtolower($F6) . "\56\x70\150\160")) {
            goto v7;
        }
        require_once JPATH_SITE . "\x2f\x63\x6f\x6d\160\x6f\x6e\x65\x6e\164\163\57\143\x6f\x6d\137\155\151\x6e\151\x6f\x72\x61\156\x67\145\x5f\163\141\155\x6c\x2f\x6d\x6f\x64\145\154\163\x2f" . strtolower($F6) . "\x2e\160\150\160";
        $A8 = JModelLegacy::getInstance($F6, "\115\151\156\x69\x6f\162\x61\156\147\x65\137\163\141\155\154\115\157\x64\x65\x6c");
        v7:
        return $A8;
    }
}
