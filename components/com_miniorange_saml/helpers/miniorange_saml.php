<?php


defined("\137\112\105\x58\105\103") or die;
class Miniorange_samlHelpersMiniorange_saml
{
    public static function getModel($UC)
    {
        $BF = null;
        if (!file_exists(JPATH_SITE . "\x2f\x63\157\155\160\x6f\x6e\145\156\164\163\x2f\143\157\155\137\155\151\156\x69\157\x72\x61\x6e\147\145\x5f\163\141\155\x6c\57\155\x6f\144\145\x6c\163\57" . strtolower($UC) . "\x2e\160\150\160")) {
            goto Y0;
        }
        require_once JPATH_SITE . "\x2f\143\x6f\x6d\x70\157\156\x65\x6e\164\x73\57\x63\x6f\155\x5f\x6d\x69\156\151\157\x72\141\x6e\x67\145\x5f\163\141\155\x6c\x2f\x6d\157\x64\x65\154\x73\x2f" . strtolower($UC) . "\56\x70\150\160";
        $BF = JModelLegacy::getInstance($UC, "\x4d\151\x6e\x69\x6f\x72\141\156\147\x65\137\163\x61\155\x6c\x4d\157\x64\145\x6c");
        Y0:
        return $BF;
    }
}
