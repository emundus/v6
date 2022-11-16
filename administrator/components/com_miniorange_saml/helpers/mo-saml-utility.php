<?php


class Mo_saml_Local_Util
{
    public static function is_customer_registered()
    {
        $bZ = UtilitiesSAML::getCustomerDetails();
        $MM = $bZ["\145\x6d\x61\151\154"];
        $b1 = $bZ["\x63\165\163\164\x6f\155\145\x72\x5f\x6b\x65\x79"];
        if (!$MM || !$b1 || !is_numeric(trim($b1))) {
            goto Ac;
        }
        return 1;
        goto f0;
        Ac:
        return 0;
        f0:
    }
    public static function check_empty_or_null($Uf)
    {
        if (!(!isset($Uf) || empty($Uf))) {
            goto H9;
        }
        return true;
        H9:
        return false;
    }
    public static function is_curl_installed()
    {
        if (in_array("\143\x75\162\x6c", get_loaded_extensions())) {
            goto Nl;
        }
        return 0;
        goto Rj;
        Nl:
        return 1;
        Rj:
    }
    public static function is_extension_installed($UC)
    {
        if (in_array($UC, get_loaded_extensions())) {
            goto bs;
        }
        return false;
        goto TN;
        bs:
        return true;
        TN:
    }
    public static function encrypt($ao)
    {
        $ao = stripcslashes($ao);
        $Tl = self::get_customer_token();
        return base64_encode(openssl_encrypt($ao, "\x61\x65\163\55\x31\62\70\x2d\x65\x63\142", $Tl, OPENSSL_RAW_DATA));
    }
    public static function decrypt($Uf)
    {
        $Tl = self::get_customer_token();
        $zT = rtrim(openssl_decrypt(base64_decode($Uf), "\x61\x65\163\x2d\x31\62\x38\55\145\x63\x62", $Tl, OPENSSL_RAW_DATA), "\0");
        return trim($zT, "\x0\56\x2e\32");
    }
    public static function getHostname()
    {
        return "\150\x74\164\160\163\72\57\57\154\x6f\x67\151\156\x2e\170\145\x63\165\x72\x69\x66\171\56\143\157\x6d";
    }
    public static function encrypt_value($ao)
    {
        $Tl = self::get_customer_token();
        return base64_encode(openssl_encrypt($ao, "\141\145\x73\55\x31\62\70\55\145\143\x62", $Tl, OPENSSL_RAW_DATA));
    }
    public static function decrypt_value($Uf)
    {
        $Tl = self::get_customer_token();
        return openssl_decrypt(base64_decode($Uf), "\141\x65\x73\55\x31\62\x38\55\145\x63\x62", $Tl, OPENSSL_RAW_DATA);
    }
    public static function get_customer_token()
    {
        $dZ = JFactory::getDbo();
        $qH = $dZ->getQuery(true);
        $qH->select("\143\165\163\164\x6f\155\145\x72\137\x74\157\x6b\x65\x6e");
        $qH->from($dZ->quoteName("\43\x5f\x5f\x6d\151\156\151\x6f\x72\141\156\147\145\x5f\163\x61\155\x6c\137\143\165\163\x74\157\155\145\x72\137\144\x65\x74\x61\151\154\x73"));
        $qH->where($dZ->quoteName("\151\x64") . "\40\x3d\40\61");
        $dZ->setQuery($qH);
        return $dZ->loadResult();
    }
    public static function getCustomerDetails()
    {
        $dZ = JFactory::getDbo();
        $qH = $dZ->getQuery(true);
        $qH->select("\52");
        $qH->from($dZ->quoteName("\x23\x5f\x5f\x6d\151\x6e\x69\x6f\162\x61\156\x67\145\x5f\163\141\155\154\137\143\165\163\164\157\155\x65\x72\x5f\x64\145\x74\141\x69\154\x73"));
        $qH->where($dZ->quoteName("\x69\144") . "\x20\x3d\x20\61");
        $dZ->setQuery($qH);
        $EK = $dZ->loadAssoc();
        return $EK;
    }
    public static function getSAMLCount()
    {
        $dZ = JFactory::getDbo();
        $qH = $dZ->getQuery(true);
        $qH->select("\103\117\125\x4e\x54\50\52\51");
        $qH->from($dZ->quoteName("\43\x5f\x5f\x6d\151\156\151\157\162\141\156\x67\x65\x5f\x73\x61\x6d\154\x5f\x63\x6f\x6e\x66\x69\x67"));
        $dZ->setQuery($qH);
        $n6 = $dZ->loadResult();
        return $n6;
    }
    public static function check($HG)
    {
        if (empty($HG)) {
            goto Os;
        }
        return Mo_saml_Local_Util::decrypt($HG);
        goto a3;
        Os:
        return '';
        a3:
    }
    public static function sanitize_certificate($t8)
    {
        $t8 = preg_replace("\57\x5b\15\xa\135\x2b\x2f", '', $t8);
        $t8 = str_replace("\55", '', $t8);
        $t8 = str_replace("\x42\x45\x47\111\x4e\40\x43\x45\x52\124\x49\x46\x49\x43\101\x54\x45", '', $t8);
        $t8 = str_replace("\x45\x4e\x44\x20\x43\x45\122\x54\111\106\x49\103\101\124\105", '', $t8);
        $t8 = str_replace("\x20", '', $t8);
        $t8 = chunk_split($t8, 64, "\15\12");
        $t8 = "\x2d\55\55\x2d\x2d\x42\105\107\x49\116\40\x43\105\x52\124\x49\x46\111\x43\101\124\105\x2d\55\x2d\x2d\55\15\12" . $t8 . "\55\55\55\x2d\x2d\105\116\104\x20\x43\x45\122\124\x49\x46\111\x43\x41\x54\105\55\55\55\x2d\55";
        return $t8;
    }
    public static function get_last_idp_id()
    {
        $dZ = JFactory::getDbo();
        $qH = $dZ->getQuery(true);
        $qH->select("\115\x61\170\50\151\x64\x29");
        $qH->from($dZ->quoteName("\43\137\137\155\x69\156\x69\157\162\141\x6e\x67\x65\137\163\x61\x6d\x6c\x5f\x63\x6f\x6e\x66\151\x67"));
        $dZ->setQuery($qH);
        return $dZ->loadResult();
    }
    public static function showDashboardNotification()
    {
        $vO = UtilitiesSAML::getExpiryDate();
        $qb = isset($vO["\x6c\x69\x63\145\156\163\145\105\170\160\151\162\171"]) ? date("\106\40\x6a\54\40\x59\x2c\40\x67\x3a\x69\x20\141", strtotime($vO["\154\151\143\145\x6e\x73\x65\105\170\160\x69\x72\171"])) : '';
        $dr = UtilitiesSAML::checkIsLicenseExpired();
        $BN = UtilitiesSAML::getJoomlaCmsVersion();
        $BN = substr($BN, 0, 3);
        if (!($BN < 4.0)) {
            goto q5;
        }
        $SV = UtilitiesSAML::renewalMessage($dr, $qb, "\152\x6f\157\x6d\x6c\141\137\x33");
        echo $SV;
        q5:
    }
    public static function check_special_character_in_url($Ny)
    {
        $xY = preg_match("\x2f\133\47\x5e\xc2\243\44\x25\x26\x2a\x28\x29\x7d\x7b\x40\43\176\77\x3e\x3c\x3e\54\x7c\x3d\137\53\xc2\254\x2d\x5d\x2f", $Ny);
        if ($xY) {
            goto ll;
        }
        return $Ny;
        goto SX;
        ll:
        return urldecode($Ny);
        SX:
    }
}
