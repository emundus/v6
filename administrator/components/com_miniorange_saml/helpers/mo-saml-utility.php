<?php


class Mo_saml_Local_Util
{
    public static function is_customer_registered()
    {
        $mb = UtilitiesSAML::getCustomerDetails();
        $RN = $mb["\145\x6d\x61\151\x6c"];
        $Bn = $mb["\x63\165\x73\164\x6f\x6d\145\x72\137\153\145\x79"];
        if (!$RN || !$Bn || !is_numeric(trim($Bn))) {
            goto Cg;
        }
        return 1;
        goto oy;
        Cg:
        return 0;
        oy:
    }
    public static function check_empty_or_null($Gt)
    {
        if (!(!isset($Gt) || empty($Gt))) {
            goto WE;
        }
        return true;
        WE:
        return false;
    }
    public static function is_curl_installed()
    {
        if (in_array("\x63\165\x72\154", get_loaded_extensions())) {
            goto Z6;
        }
        return 0;
        goto DC;
        Z6:
        return 1;
        DC:
    }
    public static function is_extension_installed($F6)
    {
        if (in_array($F6, get_loaded_extensions())) {
            goto bc;
        }
        return false;
        goto jN;
        bc:
        return true;
        jN:
    }
    public static function encrypt($Yu)
    {
        $Yu = stripcslashes($Yu);
        $ao = self::get_customer_token();
        return base64_encode(openssl_encrypt($Yu, "\x61\x65\x73\55\61\62\70\x2d\x65\143\142", $ao, OPENSSL_RAW_DATA));
    }
    public static function decrypt($Gt)
    {
        $ao = self::get_customer_token();
        $GX = rtrim(openssl_decrypt(base64_decode($Gt), "\x61\145\x73\55\61\x32\70\x2d\x65\x63\x62", $ao, OPENSSL_RAW_DATA), "\0");
        return trim($GX, "\0\x2e\56\32");
    }
    public static function getHostname()
    {
        return "\x68\x74\164\x70\x73\x3a\x2f\57\154\x6f\147\x69\x6e\x2e\x78\145\x63\165\x72\x69\146\x79\x2e\x63\157\x6d";
    }
    public static function encrypt_value($Yu)
    {
        $ao = self::get_customer_token();
        return base64_encode(openssl_encrypt($Yu, "\141\145\163\x2d\x31\x32\x38\x2d\145\143\142", $ao, OPENSSL_RAW_DATA));
    }
    public static function decrypt_value($Gt)
    {
        $ao = self::get_customer_token();
        return openssl_decrypt(base64_decode($Gt), "\x61\145\x73\x2d\61\62\x38\55\x65\143\x62", $ao, OPENSSL_RAW_DATA);
    }
    public static function get_customer_token()
    {
        $i1 = JFactory::getDbo();
        $zO = $i1->getQuery(true);
        $zO->select("\143\x75\163\x74\157\x6d\x65\x72\137\164\x6f\153\x65\156");
        $zO->from($i1->quoteName("\x23\x5f\x5f\155\151\x6e\151\x6f\x72\141\156\x67\x65\x5f\163\x61\x6d\154\x5f\x63\165\163\164\157\x6d\x65\x72\x5f\144\145\164\141\151\x6c\163"));
        $zO->where($i1->quoteName("\151\144") . "\x20\75\x20\x31");
        $i1->setQuery($zO);
        return $i1->loadResult();
    }
    public static function getCustomerDetails()
    {
        $i1 = JFactory::getDbo();
        $zO = $i1->getQuery(true);
        $zO->select("\52");
        $zO->from($i1->quoteName("\x23\x5f\137\155\151\x6e\x69\x6f\x72\x61\x6e\147\145\x5f\163\x61\155\154\137\143\165\163\x74\x6f\x6d\145\162\x5f\144\x65\x74\x61\x69\154\163"));
        $zO->where($i1->quoteName("\151\144") . "\40\75\x20\61");
        $i1->setQuery($zO);
        $tF = $i1->loadAssoc();
        return $tF;
    }
    public static function getSAMLCount()
    {
        $i1 = JFactory::getDbo();
        $zO = $i1->getQuery(true);
        $zO->select("\x43\x4f\x55\116\x54\x28\52\51");
        $zO->from($i1->quoteName("\x23\137\x5f\x6d\151\x6e\x69\x6f\x72\x61\x6e\147\145\137\163\x61\155\x6c\x5f\x63\157\156\x66\x69\x67"));
        $i1->setQuery($zO);
        $DS = $i1->loadResult();
        return $DS;
    }
    public static function check($TR)
    {
        if (empty($TR)) {
            goto LX;
        }
        return Mo_saml_Local_Util::decrypt($TR);
        goto JK;
        LX:
        return '';
        JK:
    }
    public static function sanitize_certificate($Q4)
    {
        $Q4 = preg_replace("\x2f\133\15\12\x5d\x2b\57", '', $Q4);
        $Q4 = str_replace("\55", '', $Q4);
        $Q4 = str_replace("\102\105\x47\111\116\x20\103\105\122\124\x49\106\x49\x43\101\124\x45", '', $Q4);
        $Q4 = str_replace("\x45\116\104\40\x43\105\122\124\x49\106\111\x43\x41\x54\105", '', $Q4);
        $Q4 = str_replace("\40", '', $Q4);
        $Q4 = chunk_split($Q4, 64, "\xd\12");
        $Q4 = "\x2d\55\55\55\x2d\102\x45\107\111\116\40\x43\105\x52\x54\x49\x46\111\103\101\x54\x45\55\x2d\55\x2d\55\xd\12" . $Q4 . "\55\x2d\x2d\x2d\55\x45\x4e\x44\x20\x43\105\122\x54\x49\x46\111\x43\x41\124\x45\x2d\55\55\55\x2d";
        return $Q4;
    }
    public static function get_last_idp_id()
    {
        $i1 = JFactory::getDbo();
        $zO = $i1->getQuery(true);
        $zO->select("\115\141\170\x28\x69\x64\x29");
        $zO->from($i1->quoteName("\43\x5f\x5f\x6d\151\x6e\151\x6f\x72\x61\x6e\x67\x65\x5f\163\141\155\154\x5f\x63\157\156\146\x69\x67"));
        $i1->setQuery($zO);
        return $i1->loadResult();
    }
    public static function showDashboardNotification()
    {
        $dK = UtilitiesSAML::getExpiryDate();
        $rO = isset($dK["\154\151\143\x65\156\x73\145\105\170\x70\x69\x72\171"]) ? date("\x46\x20\x6a\54\x20\131\54\40\147\x3a\x69\x20\x61", strtotime($dK["\x6c\x69\143\145\156\163\x65\x45\x78\x70\x69\162\171"])) : '';
        $zD = UtilitiesSAML::checkIsLicenseExpired();
        $yO = UtilitiesSAML::getJoomlaCmsVersion();
        $yO = substr($yO, 0, 3);
        if (!($yO < 4.0)) {
            goto K9;
        }
        $X4 = UtilitiesSAML::renewalMessage($zD, $rO, "\152\157\x6f\x6d\154\141\137\63");
        echo $X4;
        K9:
    }
    public static function check_special_character_in_url($CN)
    {
        $ZH = preg_match("\x2f\x5b\47\x5e\302\xa3\x24\x25\46\x2a\x28\51\x7d\173\x40\43\176\x3f\76\x3c\x3e\x2c\x7c\x3d\137\x2b\302\254\x2d\135\x2f", $CN);
        if ($ZH) {
            goto AD;
        }
        return $CN;
        goto n1;
        AD:
        return urldecode($CN);
        n1:
    }
}
