<?php


class Mo_saml_Local_Customer
{
    public $email;
    public $phone;
    public $customerKey;
    public $transactionId;
    private $defaultCustomerKey = "\61\66\x35\x35\x35";
    private $defaultApiKey = "\x66\106\144\62\x58\143\x76\124\107\104\x65\155\x5a\x76\x62\167\x31\142\143\x55\145\163\x4e\x4a\x57\x45\x71\113\x62\x62\x55\161";
    function get_customer_key()
    {
        if (Mo_saml_Local_Util::is_curl_installed()) {
            goto eI;
        }
        return json_encode(array("\141\x70\x69\x4b\145\x79" => "\x43\x55\122\114\x5f\105\x52\x52\x4f\x52", "\164\x6f\x6b\x65\156" => "\74\141\40\150\x72\x65\146\x3d\42\150\x74\164\160\x3a\x2f\57\160\150\x70\x2e\156\145\x74\x2f\x6d\x61\156\x75\x61\154\57\x65\x6e\x2f\143\x75\162\x6c\x2e\x69\156\x73\x74\x61\154\x6c\x61\164\x69\x6f\156\x2e\160\x68\x70\x22\76\x50\110\120\x20\x63\x55\122\114\x20\145\x78\164\145\156\163\x69\157\156\x3c\x2f\x61\76\40\151\163\x20\156\x6f\x74\40\x69\x6e\163\x74\141\x6c\154\145\144\x20\157\x72\x20\144\x69\163\x61\142\154\x65\144\x2e"));
        eI:
        $Vs = Mo_saml_Local_Util::getHostname();
        $Ny = $Vs . "\x2f\x6d\157\x61\163\x2f\x72\x65\163\x74\57\143\165\x73\x74\157\x6d\x65\x72\x2f\x6b\145\x79";
        $IE = curl_init($Ny);
        $EK = Mo_saml_Local_Util::getCustomerDetails();
        $YU = $EK["\x70\141\x73\163\167\157\162\x64"];
        if (empty($YU)) {
            goto b9;
        }
        $YU = base64_decode($YU);
        b9:
        $qv = array("\145\155\x61\x69\x6c" => $EK["\x65\155\141\x69\154"], "\x70\x61\163\x73\167\x6f\162\144" => $YU);
        $JM = json_encode($qv);
        curl_setopt($IE, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($IE, CURLOPT_ENCODING, '');
        curl_setopt($IE, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($IE, CURLOPT_AUTOREFERER, true);
        curl_setopt($IE, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($IE, CURLOPT_MAXREDIRS, 10);
        curl_setopt($IE, CURLOPT_HTTPHEADER, array("\x43\157\x6e\x74\x65\156\164\55\124\x79\160\145\x3a\x20\x61\x70\160\154\151\x63\x61\x74\x69\x6f\156\x2f\x6a\x73\157\x6e", "\x63\150\x61\162\163\145\x74\x3a\40\x55\124\106\40\55\40\x38", "\x41\x75\164\x68\x6f\162\151\x7a\x61\164\151\x6f\156\72\x20\x42\x61\163\151\143"));
        curl_setopt($IE, CURLOPT_POST, true);
        curl_setopt($IE, CURLOPT_POSTFIELDS, $JM);
        $vO = curl_exec($IE);
        if (!curl_errno($IE)) {
            goto VV;
        }
        echo "\x52\x65\161\x75\x65\163\x74\x20\x45\x72\162\x6f\x72\72" . curl_error($IE);
        exit;
        VV:
        curl_close($IE);
        return $vO;
    }
    function submit_contact_us($AZ, $S0, $qH)
    {
        if (Mo_saml_Local_Util::is_curl_installed()) {
            goto pQ;
        }
        return json_encode(array("\x73\164\141\x74\x75\x73" => "\103\x55\122\114\x5f\105\122\122\x4f\122", "\x73\164\x61\x74\x75\x73\115\145\x73\163\x61\x67\145" => "\x3c\x61\x20\x68\x72\145\x66\75\x22\x68\164\164\160\72\57\x2f\160\150\160\x2e\156\x65\164\x2f\x6d\141\156\x75\141\x6c\57\x65\156\x2f\143\x75\x72\154\56\x69\156\x73\x74\141\x6c\154\141\x74\x69\x6f\156\56\x70\x68\x70\42\x3e\120\110\x50\x20\x63\x55\x52\x4c\40\x65\170\x74\145\x6e\163\x69\x6f\156\x3c\57\141\x3e\40\x69\x73\40\x6e\x6f\164\40\151\156\x73\x74\x61\154\154\x65\144\40\x6f\x72\40\x64\151\x73\141\142\x6c\x65\x64\x2e"));
        pQ:
        $Vs = Mo_saml_Local_Util::getHostname();
        $Ny = $Vs . "\57\x6d\x6f\141\163\57\x72\145\x73\x74\x2f\x63\x75\x73\164\157\x6d\145\162\x2f\x63\x6f\x6e\x74\x61\143\x74\55\165\x73";
        $IE = curl_init($Ny);
        $current_user = JFactory::getUser();
        $s6 = phpversion();
        $k1 = new JVersion();
        $BN = $k1->getShortVersion();
        $Jp = UtilitiesSAML::GetPluginVersion();
        $qH = "\133\112\157\157\155\x6c\141\x20" . $BN . "\x20\x53\101\x4d\114\x20\123\x50\40\x45\156\x74\x65\162\160\162\151\163\x65\40\x50\154\x75\147\x69\x6e\x20\x7c\x20" . $Jp . "\x20\174\40\x50\x48\120\40" . $s6 . "\x5d\40\72\40" . $qH;
        $qv = array("\x66\x69\162\163\x74\x4e\x61\x6d\145" => $current_user->username, "\x63\x6f\x6d\160\x61\156\171" => $_SERVER["\x53\x45\122\x56\105\x52\x5f\116\101\x4d\x45"], "\145\x6d\x61\x69\154" => $AZ, "\x63\143\105\x6d\141\x69\154" => "\x6a\157\x6f\x6d\154\x61\163\165\160\x70\157\x72\x74\100\170\x65\x63\165\x72\151\146\x79\x2e\x63\x6f\155", "\x70\x68\157\x6e\145" => $S0, "\161\x75\x65\x72\x79" => $qH);
        $JM = json_encode($qv);
        curl_setopt($IE, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($IE, CURLOPT_ENCODING, '');
        curl_setopt($IE, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($IE, CURLOPT_AUTOREFERER, true);
        curl_setopt($IE, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($IE, CURLOPT_MAXREDIRS, 10);
        curl_setopt($IE, CURLOPT_HTTPHEADER, array("\x43\157\156\164\x65\156\x74\x2d\124\x79\x70\x65\72\x20\x61\x70\160\154\151\143\x61\164\x69\157\156\57\x6a\163\x6f\x6e", "\x63\x68\x61\162\163\x65\x74\72\40\x55\124\106\x2d\x38", "\x41\x75\164\x68\x6f\x72\151\172\x61\x74\151\x6f\156\72\x20\x42\141\163\x69\x63"));
        curl_setopt($IE, CURLOPT_POST, true);
        curl_setopt($IE, CURLOPT_POSTFIELDS, $JM);
        $vO = curl_exec($IE);
        if (!curl_errno($IE)) {
            goto Wq;
        }
        echo "\122\145\x71\x75\x65\163\x74\x20\105\x72\x72\157\162\x3a" . curl_error($IE);
        return false;
        Wq:
        curl_close($IE);
        return true;
    }
    function check($G1)
    {
        $EK = Mo_saml_Local_Util::getCustomerDetails();
        $Vs = Mo_saml_Local_Util::getHostname();
        $Ny = $Vs . "\x2f\x6d\157\141\163\57\141\x70\151\57\142\141\143\153\165\160\143\x6f\144\x65\57\x76\x65\162\151\x66\x79";
        $IE = curl_init($Ny);
        $b1 = $EK["\x63\165\163\x74\157\155\x65\x72\x5f\x6b\145\x79"];
        $Wk = $EK["\141\x70\x69\x5f\153\145\171"];
        $zS = round(microtime(true) * 1000);
        $XU = $b1 . number_format($zS, 0, '', '') . $Wk;
        $Vf = hash("\x73\150\141\x35\61\x32", $XU);
        $ii = "\103\165\x73\x74\x6f\x6d\x65\162\55\x4b\145\171\x3a\40" . $b1;
        $dl = "\124\151\x6d\x65\163\x74\x61\x6d\x70\72\40" . number_format($zS, 0, '', '');
        $sH = "\x41\165\x74\x68\x6f\x72\151\x7a\141\x74\151\157\x6e\72\x20" . $Vf;
        $o4 = JURI::root();
        $qv = array("\x63\157\x64\x65" => $G1, "\x63\x75\x73\x74\x6f\x6d\x65\x72\x4b\145\x79" => $b1, "\141\144\144\151\x74\x69\157\156\141\x6c\106\151\145\154\x64\x73" => array("\x66\151\x65\x6c\144\x31" => $o4));
        $JM = json_encode($qv);
        curl_setopt($IE, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($IE, CURLOPT_ENCODING, '');
        curl_setopt($IE, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($IE, CURLOPT_AUTOREFERER, true);
        curl_setopt($IE, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($IE, CURLOPT_MAXREDIRS, 10);
        curl_setopt($IE, CURLOPT_HTTPHEADER, array("\103\157\x6e\x74\x65\156\164\x2d\x54\x79\160\x65\72\40\x61\160\x70\154\x69\143\141\x74\x69\157\x6e\x2f\x6a\x73\157\156", $ii, $dl, $sH));
        curl_setopt($IE, CURLOPT_POST, true);
        curl_setopt($IE, CURLOPT_POSTFIELDS, $JM);
        curl_setopt($IE, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($IE, CURLOPT_TIMEOUT, 20);
        $vO = curl_exec($IE);
        if (!curl_errno($IE)) {
            goto U3;
        }
        echo "\x52\x65\x71\x75\x65\x73\x74\40\x45\162\x72\x6f\x72\72" . curl_error($IE);
        exit;
        U3:
        curl_close($IE);
        $vO = json_decode($vO, true);
        return $vO;
    }
    function check_customer($MM)
    {
        if (Mo_saml_Local_Util::is_curl_installed()) {
            goto HY;
        }
        return json_encode(array("\163\164\x61\164\x75\163" => "\103\x55\122\x4c\137\105\x52\122\117\x52", "\x73\x74\141\x74\x75\x73\x4d\x65\x73\x73\141\147\145" => "\74\x61\x20\150\162\145\146\x3d\x22\x68\x74\x74\160\x3a\x2f\x2f\x70\150\x70\56\x6e\x65\164\x2f\x6d\141\x6e\165\x61\x6c\57\145\x6e\57\x63\x75\162\154\56\x69\156\163\x74\x61\154\x6c\141\x74\x69\157\x6e\x2e\160\x68\160\42\x3e\x50\x48\x50\40\x63\x55\122\114\x20\145\170\164\x65\156\x73\151\x6f\156\74\57\x61\76\40\151\163\x20\x6e\157\164\x20\x69\x6e\x73\x74\141\x6c\x6c\145\x64\x20\157\162\x20\144\x69\163\x61\x62\154\145\144\56"));
        HY:
        $Vs = Mo_saml_Local_Util::getHostname();
        $Ny = $Vs . "\57\x6d\x6f\x61\163\57\x72\x65\x73\x74\57\143\x75\x73\x74\157\155\x65\162\57\143\150\x65\x63\153\x2d\x69\146\55\x65\170\151\x73\164\x73";
        $IE = curl_init($Ny);
        $qv = array("\x65\155\x61\151\x6c" => $MM);
        $JM = json_encode($qv);
        curl_setopt($IE, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($IE, CURLOPT_ENCODING, '');
        curl_setopt($IE, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($IE, CURLOPT_AUTOREFERER, true);
        curl_setopt($IE, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($IE, CURLOPT_MAXREDIRS, 10);
        curl_setopt($IE, CURLOPT_HTTPHEADER, array("\x43\157\156\x74\x65\156\x74\x2d\124\x79\x70\x65\x3a\x20\x61\x70\160\154\151\143\x61\x74\151\157\156\x2f\152\x73\157\x6e", "\143\x68\141\x72\x73\145\164\x3a\x20\125\124\106\x20\x2d\x20\70", "\x41\x75\x74\x68\157\x72\151\172\x61\164\x69\x6f\x6e\x3a\x20\102\x61\163\151\143"));
        curl_setopt($IE, CURLOPT_POST, true);
        curl_setopt($IE, CURLOPT_POSTFIELDS, $JM);
        $vO = curl_exec($IE);
        if (!curl_errno($IE)) {
            goto UK;
        }
        echo "\122\145\x71\x75\x65\x73\164\40\x45\x72\162\x6f\162\x3a" . curl_error($IE);
        exit;
        UK:
        curl_close($IE);
        return $vO;
    }
    function update_status()
    {
        $Vs = Mo_saml_Local_Util::getHostname();
        $Ny = $Vs . "\57\x6d\157\x61\163\57\141\x70\151\x2f\x62\141\143\153\x75\160\x63\157\x64\145\57\x75\160\x64\141\x74\145\163\x74\x61\164\165\163";
        $IE = curl_init($Ny);
        $EK = Mo_saml_Local_Util::getCustomerDetails();
        $b1 = $EK["\x63\x75\x73\164\157\155\x65\x72\x5f\153\x65\x79"];
        $Wk = $EK["\x61\160\x69\137\153\x65\x79"];
        $wq = $EK["\163\155\x6c\x5f\x6c\153"];
        $zS = round(microtime(true) * 1000);
        $XU = $b1 . number_format($zS, 0, '', '') . $Wk;
        $Vf = hash("\163\150\x61\x35\x31\x32", $XU);
        $ii = "\x43\x75\x73\x74\157\x6d\x65\162\x2d\113\145\x79\x3a\40" . $b1;
        $dl = "\124\x69\155\145\x73\x74\x61\155\x70\x3a\x20" . number_format($zS, 0, '', '');
        $sH = "\101\165\164\x68\157\x72\151\172\x61\x74\151\x6f\x6e\72\40" . $Vf;
        $Tl = $EK["\x63\x75\x73\x74\x6f\155\145\162\137\164\x6f\x6b\x65\x6e"];
        $G1 = Mo_saml_Local_Util::decrypt($wq);
        $qv = array("\x63\157\144\145" => $G1, "\x63\x75\163\x74\157\155\x65\x72\113\x65\x79" => $b1);
        $JM = json_encode($qv);
        curl_setopt($IE, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($IE, CURLOPT_ENCODING, '');
        curl_setopt($IE, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($IE, CURLOPT_AUTOREFERER, true);
        curl_setopt($IE, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($IE, CURLOPT_MAXREDIRS, 10);
        curl_setopt($IE, CURLOPT_HTTPHEADER, array("\x43\x6f\x6e\164\145\x6e\164\x2d\x54\x79\160\x65\x3a\40\141\x70\x70\x6c\x69\143\141\164\x69\157\156\x2f\152\163\157\x6e", $ii, $dl, $sH));
        curl_setopt($IE, CURLOPT_POST, true);
        curl_setopt($IE, CURLOPT_POSTFIELDS, $JM);
        curl_setopt($IE, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($IE, CURLOPT_TIMEOUT, 20);
        $vO = curl_exec($IE);
        if (!curl_errno($IE)) {
            goto H0;
        }
        echo "\122\145\161\x75\145\163\x74\40\x45\162\162\157\x72\72" . curl_error($IE);
        exit;
        H0:
        curl_close($IE);
        return $vO;
    }
    public static function ccl($b1, $Wk)
    {
        $Vs = Mo_saml_Local_Util::getHostname();
        $Ny = $Vs . "\x2f\x6d\x6f\x61\x73\57\162\145\x73\x74\57\x63\165\x73\x74\x6f\155\x65\162\x2f\154\151\x63\145\156\163\x65";
        $IE = curl_init($Ny);
        $zS = round(microtime(true) * 1000);
        $XU = $b1 . number_format($zS, 0, '', '') . $Wk;
        $Vf = hash("\163\x68\141\x35\61\62", $XU);
        $ii = "\x43\x75\163\164\x6f\x6d\145\x72\55\113\145\x79\72\x20" . $b1;
        $dl = "\x54\151\155\x65\163\x74\x61\155\x70\x3a\x20" . number_format($zS, 0, '', '');
        $sH = "\x41\x75\x74\x68\157\x72\151\x7a\141\164\x69\x6f\156\72\40" . $Vf;
        $qv = array("\143\165\163\164\157\x6d\145\162\111\x64" => $b1, "\x61\160\160\154\151\143\x61\x74\151\x6f\x6e\x4e\141\x6d\145" => UtilitiesSAML::getLicensePlanName());
        $JM = json_encode($qv);
        curl_setopt($IE, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($IE, CURLOPT_ENCODING, '');
        curl_setopt($IE, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($IE, CURLOPT_AUTOREFERER, true);
        curl_setopt($IE, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($IE, CURLOPT_MAXREDIRS, 10);
        curl_setopt($IE, CURLOPT_HTTPHEADER, array("\103\x6f\156\164\x65\156\164\x2d\x54\x79\x70\x65\x3a\x20\x61\160\x70\x6c\151\143\x61\164\x69\x6f\x6e\x2f\x6a\x73\157\x6e", $ii, $dl, $sH));
        curl_setopt($IE, CURLOPT_POST, true);
        curl_setopt($IE, CURLOPT_POSTFIELDS, $JM);
        curl_setopt($IE, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($IE, CURLOPT_TIMEOUT, 20);
        $vO = curl_exec($IE);
        if (!curl_errno($IE)) {
            goto Ij;
        }
        echo "\122\145\x71\x75\145\x73\x74\x20\x45\162\162\x6f\162\x3a" . curl_error($IE);
        exit;
        Ij:
        curl_close($IE);
        return $vO;
    }
}
