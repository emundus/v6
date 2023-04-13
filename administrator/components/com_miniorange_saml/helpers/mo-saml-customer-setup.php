<?php


class Mo_saml_Local_Customer
{
    public $email;
    public $phone;
    public $customerKey;
    public $transactionId;
    private $defaultCustomerKey = "\x31\x36\x35\x35\x35";
    private $defaultApiKey = "\x66\x46\144\x32\130\143\166\124\107\x44\x65\155\x5a\x76\x62\x77\x31\142\x63\125\145\163\x4e\x4a\x57\105\161\x4b\x62\142\125\x71";
    function get_customer_key()
    {
        if (Mo_saml_Local_Util::is_curl_installed()) {
            goto l3;
        }
        return json_encode(array("\x61\160\151\113\x65\171" => "\103\x55\x52\114\137\105\122\x52\x4f\x52", "\x74\157\x6b\x65\x6e" => "\74\141\x20\x68\x72\x65\146\75\42\x68\x74\x74\160\x3a\x2f\57\x70\150\x70\x2e\156\145\164\x2f\x6d\x61\156\x75\x61\x6c\x2f\145\156\x2f\x63\x75\x72\154\x2e\x69\x6e\163\164\x61\x6c\x6c\141\x74\x69\157\x6e\56\x70\x68\x70\42\76\x50\110\x50\x20\143\x55\122\114\x20\145\x78\x74\x65\x6e\163\151\x6f\156\x3c\57\x61\76\x20\x69\x73\x20\156\x6f\164\x20\x69\156\x73\164\141\154\154\x65\144\x20\x6f\x72\x20\144\x69\163\141\142\154\x65\x64\x2e"));
        l3:
        $v0 = Mo_saml_Local_Util::getHostname();
        $CN = $v0 . "\x2f\155\x6f\x61\163\57\162\x65\x73\x74\57\143\165\163\164\x6f\x6d\145\x72\57\x6b\145\x79";
        $dO = curl_init($CN);
        $tF = Mo_saml_Local_Util::getCustomerDetails();
        $pb = $tF["\160\x61\x73\x73\x77\157\162\x64"];
        if (empty($pb)) {
            goto Fw;
        }
        $pb = base64_decode($pb);
        Fw:
        $LD = array("\x65\x6d\x61\151\154" => $tF["\x65\x6d\x61\151\154"], "\160\x61\x73\x73\167\x6f\162\x64" => $pb);
        $Wn = json_encode($LD);
        curl_setopt($dO, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($dO, CURLOPT_ENCODING, '');
        curl_setopt($dO, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($dO, CURLOPT_AUTOREFERER, true);
        curl_setopt($dO, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($dO, CURLOPT_MAXREDIRS, 10);
        curl_setopt($dO, CURLOPT_HTTPHEADER, array("\103\x6f\x6e\164\x65\x6e\164\55\x54\x79\160\145\x3a\x20\x61\x70\160\x6c\x69\143\x61\x74\x69\157\156\x2f\152\163\157\x6e", "\x63\x68\x61\x72\x73\x65\164\x3a\40\x55\x54\x46\40\x2d\x20\70", "\101\165\x74\x68\157\x72\x69\x7a\x61\164\x69\x6f\156\x3a\40\102\141\x73\151\x63"));
        curl_setopt($dO, CURLOPT_POST, true);
        curl_setopt($dO, CURLOPT_POSTFIELDS, $Wn);
        $dK = curl_exec($dO);
        if (!curl_errno($dO)) {
            goto Rx;
        }
        echo "\122\145\161\x75\x65\163\164\40\x45\x72\162\x6f\162\x3a" . curl_error($dO);
        exit;
        Rx:
        curl_close($dO);
        return $dK;
    }
    function submit_contact_us($iF, $v9, $zO)
    {
        if (Mo_saml_Local_Util::is_curl_installed()) {
            goto eB;
        }
        return json_encode(array("\x73\164\141\x74\165\x73" => "\103\x55\122\114\137\105\x52\x52\x4f\x52", "\x73\164\x61\x74\165\x73\x4d\145\x73\163\x61\147\145" => "\x3c\141\40\x68\x72\x65\x66\x3d\x22\150\x74\x74\160\x3a\57\57\x70\150\x70\x2e\x6e\145\x74\57\x6d\141\156\x75\141\x6c\57\x65\x6e\x2f\x63\x75\x72\154\x2e\x69\156\x73\164\141\x6c\x6c\141\x74\151\157\x6e\56\160\x68\x70\42\x3e\120\x48\120\40\143\125\x52\x4c\x20\x65\x78\x74\145\x6e\x73\x69\157\x6e\74\57\x61\76\x20\x69\163\40\156\x6f\x74\x20\x69\156\163\164\141\154\154\x65\144\40\x6f\x72\40\x64\151\x73\141\142\154\x65\144\x2e"));
        eB:
        $v0 = Mo_saml_Local_Util::getHostname();
        $CN = $v0 . "\x2f\x6d\x6f\141\x73\57\x72\145\x73\x74\57\x63\x75\x73\x74\x6f\x6d\x65\162\x2f\x63\x6f\156\164\x61\x63\x74\55\x75\x73";
        $dO = curl_init($CN);
        $current_user = JFactory::getUser();
        $tf = phpversion();
        $Xy = new JVersion();
        $yO = $Xy->getShortVersion();
        $Bw = UtilitiesSAML::GetPluginVersion();
        $zO = "\x5b\112\157\x6f\x6d\154\141\x20" . $yO . "\x20\x53\101\115\114\x20\x53\x50\x20\x45\x6e\164\x65\x72\x70\162\x69\x73\x65\40\120\154\x75\147\151\x6e\40\x7c\x20" . $Bw . "\40\174\x20\x50\x48\120\40" . $tf . "\135\40\x3a\x20" . $zO;
        $LD = array("\146\x69\162\163\x74\116\x61\x6d\145" => $current_user->username, "\143\x6f\x6d\160\141\x6e\171" => $_SERVER["\123\x45\x52\x56\x45\x52\137\116\x41\x4d\x45"], "\145\155\141\x69\x6c" => $iF, "\143\x63\105\155\141\151\154" => "\x6a\157\x6f\155\x6c\141\163\165\160\x70\x6f\x72\164\x40\x78\145\x63\165\x72\x69\146\x79\56\x63\157\x6d", "\160\x68\x6f\x6e\x65" => $v9, "\161\165\x65\x72\171" => $zO);
        $Wn = json_encode($LD);
        curl_setopt($dO, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($dO, CURLOPT_ENCODING, '');
        curl_setopt($dO, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($dO, CURLOPT_AUTOREFERER, true);
        curl_setopt($dO, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($dO, CURLOPT_MAXREDIRS, 10);
        curl_setopt($dO, CURLOPT_HTTPHEADER, array("\103\157\x6e\164\145\x6e\164\55\x54\x79\160\x65\72\x20\141\x70\x70\x6c\x69\x63\141\164\x69\157\x6e\x2f\152\x73\157\x6e", "\x63\150\x61\162\163\145\164\x3a\40\125\124\x46\x2d\70", "\x41\165\164\150\157\162\151\x7a\x61\x74\151\x6f\x6e\x3a\40\x42\x61\163\x69\x63"));
        curl_setopt($dO, CURLOPT_POST, true);
        curl_setopt($dO, CURLOPT_POSTFIELDS, $Wn);
        $dK = curl_exec($dO);
        if (!curl_errno($dO)) {
            goto W5;
        }
        echo "\x52\145\x71\165\x65\x73\x74\x20\105\162\x72\157\162\x3a" . curl_error($dO);
        return false;
        W5:
        curl_close($dO);
        return true;
    }
    function check($jV)
    {
        $tF = Mo_saml_Local_Util::getCustomerDetails();
        $v0 = Mo_saml_Local_Util::getHostname();
        $CN = $v0 . "\x2f\x6d\157\x61\x73\57\141\x70\151\57\x62\141\143\x6b\x75\x70\x63\157\144\x65\57\x76\x65\162\x69\x66\x79";
        $dO = curl_init($CN);
        $Bn = $tF["\x63\165\x73\164\157\155\x65\162\137\153\x65\x79"];
        $YK = $tF["\141\160\x69\137\x6b\145\x79"];
        $bf = round(microtime(true) * 1000);
        $op = $Bn . number_format($bf, 0, '', '') . $YK;
        $lf = hash("\x73\150\x61\65\x31\x32", $op);
        $h8 = "\103\x75\163\164\157\x6d\x65\162\55\x4b\x65\x79\x3a\x20" . $Bn;
        $Jw = "\124\151\155\x65\163\164\x61\155\160\72\40" . number_format($bf, 0, '', '');
        $jt = "\x41\165\x74\x68\x6f\162\151\x7a\x61\x74\x69\157\x6e\72\x20" . $lf;
        $YT = JURI::root();
        $LD = array("\143\157\144\145" => $jV, "\x63\165\163\x74\x6f\x6d\x65\162\x4b\145\171" => $Bn, "\x61\144\144\x69\164\151\x6f\156\141\154\106\x69\145\x6c\144\x73" => array("\146\x69\x65\x6c\144\61" => $YT));
        $Wn = json_encode($LD);
        curl_setopt($dO, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($dO, CURLOPT_ENCODING, '');
        curl_setopt($dO, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($dO, CURLOPT_AUTOREFERER, true);
        curl_setopt($dO, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($dO, CURLOPT_MAXREDIRS, 10);
        curl_setopt($dO, CURLOPT_HTTPHEADER, array("\x43\157\156\164\x65\x6e\164\x2d\x54\171\160\x65\72\x20\x61\160\160\x6c\151\x63\141\x74\151\x6f\x6e\x2f\152\x73\x6f\156", $h8, $Jw, $jt));
        curl_setopt($dO, CURLOPT_POST, true);
        curl_setopt($dO, CURLOPT_POSTFIELDS, $Wn);
        curl_setopt($dO, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($dO, CURLOPT_TIMEOUT, 20);
        $dK = curl_exec($dO);
        if (!curl_errno($dO)) {
            goto t3;
        }
        echo "\122\145\x71\165\x65\x73\164\x20\105\162\162\x6f\x72\x3a" . curl_error($dO);
        exit;
        t3:
        curl_close($dO);
        $dK = json_decode($dK, true);
        return $dK;
    }
    function check_customer($RN)
    {
        if (Mo_saml_Local_Util::is_curl_installed()) {
            goto Y6;
        }
        return json_encode(array("\x73\x74\x61\x74\x75\163" => "\x43\x55\122\x4c\x5f\x45\122\122\117\x52", "\163\164\141\164\165\x73\x4d\145\163\x73\141\x67\145" => "\x3c\141\x20\x68\x72\x65\146\x3d\42\150\x74\x74\160\x3a\57\x2f\160\150\x70\56\x6e\x65\x74\57\155\x61\156\165\x61\x6c\x2f\x65\x6e\x2f\143\165\x72\x6c\56\151\156\163\164\141\x6c\x6c\141\x74\151\157\156\x2e\x70\x68\x70\x22\x3e\120\110\120\x20\143\x55\122\114\40\x65\170\x74\x65\156\163\x69\x6f\x6e\x3c\57\141\x3e\40\151\x73\x20\156\x6f\164\x20\x69\156\163\164\x61\x6c\x6c\x65\144\x20\157\x72\40\x64\151\x73\x61\142\154\x65\x64\x2e"));
        Y6:
        $v0 = Mo_saml_Local_Util::getHostname();
        $CN = $v0 . "\x2f\x6d\157\x61\163\x2f\162\x65\163\x74\57\143\165\x73\164\157\155\145\x72\57\x63\x68\x65\143\153\55\x69\x66\x2d\145\x78\151\163\x74\x73";
        $dO = curl_init($CN);
        $LD = array("\145\x6d\141\x69\154" => $RN);
        $Wn = json_encode($LD);
        curl_setopt($dO, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($dO, CURLOPT_ENCODING, '');
        curl_setopt($dO, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($dO, CURLOPT_AUTOREFERER, true);
        curl_setopt($dO, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($dO, CURLOPT_MAXREDIRS, 10);
        curl_setopt($dO, CURLOPT_HTTPHEADER, array("\x43\x6f\x6e\164\145\x6e\164\55\124\x79\x70\145\72\40\141\x70\160\x6c\x69\143\141\164\x69\x6f\x6e\x2f\x6a\x73\x6f\x6e", "\143\150\x61\x72\163\x65\164\x3a\x20\125\124\106\x20\x2d\x20\x38", "\101\165\x74\x68\x6f\x72\x69\x7a\x61\164\x69\x6f\156\x3a\x20\102\141\163\x69\143"));
        curl_setopt($dO, CURLOPT_POST, true);
        curl_setopt($dO, CURLOPT_POSTFIELDS, $Wn);
        $dK = curl_exec($dO);
        if (!curl_errno($dO)) {
            goto tH;
        }
        echo "\x52\x65\161\x75\145\x73\164\40\x45\x72\x72\x6f\x72\x3a" . curl_error($dO);
        exit;
        tH:
        curl_close($dO);
        return $dK;
    }
    function update_status()
    {
        $v0 = Mo_saml_Local_Util::getHostname();
        $CN = $v0 . "\x2f\155\x6f\141\x73\57\x61\x70\x69\57\x62\x61\x63\153\165\160\x63\x6f\144\x65\x2f\165\x70\x64\x61\x74\x65\163\164\141\x74\x75\x73";
        $dO = curl_init($CN);
        $tF = Mo_saml_Local_Util::getCustomerDetails();
        $Bn = $tF["\x63\165\163\164\x6f\155\145\162\x5f\x6b\x65\x79"];
        $YK = $tF["\x61\160\x69\x5f\153\x65\x79"];
        $As = $tF["\x73\155\x6c\137\x6c\153"];
        $bf = round(microtime(true) * 1000);
        $op = $Bn . number_format($bf, 0, '', '') . $YK;
        $lf = hash("\163\150\x61\65\61\62", $op);
        $h8 = "\x43\165\163\164\157\155\x65\x72\x2d\x4b\145\171\72\x20" . $Bn;
        $Jw = "\124\151\155\145\x73\x74\x61\x6d\x70\72\x20" . number_format($bf, 0, '', '');
        $jt = "\x41\x75\164\x68\157\162\x69\x7a\x61\164\151\x6f\x6e\x3a\x20" . $lf;
        $ao = $tF["\x63\165\x73\x74\x6f\x6d\x65\162\137\164\x6f\153\x65\156"];
        $jV = Mo_saml_Local_Util::decrypt($As);
        $LD = array("\x63\157\144\145" => $jV, "\x63\x75\163\164\157\155\x65\162\113\x65\x79" => $Bn);
        $Wn = json_encode($LD);
        curl_setopt($dO, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($dO, CURLOPT_ENCODING, '');
        curl_setopt($dO, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($dO, CURLOPT_AUTOREFERER, true);
        curl_setopt($dO, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($dO, CURLOPT_MAXREDIRS, 10);
        curl_setopt($dO, CURLOPT_HTTPHEADER, array("\x43\157\156\164\145\156\x74\55\x54\x79\x70\x65\x3a\x20\x61\x70\x70\154\x69\x63\x61\164\151\x6f\156\x2f\x6a\163\x6f\156", $h8, $Jw, $jt));
        curl_setopt($dO, CURLOPT_POST, true);
        curl_setopt($dO, CURLOPT_POSTFIELDS, $Wn);
        curl_setopt($dO, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($dO, CURLOPT_TIMEOUT, 20);
        $dK = curl_exec($dO);
        if (!curl_errno($dO)) {
            goto rY;
        }
        echo "\122\145\x71\165\145\x73\x74\40\x45\x72\162\x6f\x72\x3a" . curl_error($dO);
        exit;
        rY:
        curl_close($dO);
        return $dK;
    }
    public static function ccl($Bn, $YK)
    {
        $v0 = Mo_saml_Local_Util::getHostname();
        $CN = $v0 . "\x2f\155\157\x61\163\57\162\x65\163\164\x2f\143\x75\x73\164\x6f\155\x65\162\x2f\x6c\151\x63\x65\156\x73\145";
        $dO = curl_init($CN);
        $bf = round(microtime(true) * 1000);
        $op = $Bn . number_format($bf, 0, '', '') . $YK;
        $lf = hash("\163\x68\141\65\61\62", $op);
        $h8 = "\x43\165\163\x74\157\x6d\145\x72\55\113\145\171\72\x20" . $Bn;
        $Jw = "\124\151\x6d\x65\x73\x74\141\x6d\160\x3a\x20" . number_format($bf, 0, '', '');
        $jt = "\101\165\164\x68\157\162\151\x7a\x61\164\x69\157\156\72\40" . $lf;
        $LD = array("\x63\x75\163\164\x6f\155\145\x72\x49\x64" => $Bn, "\141\x70\160\x6c\x69\x63\x61\x74\151\x6f\156\116\x61\155\x65" => UtilitiesSAML::getLicensePlanName());
        $Wn = json_encode($LD);
        curl_setopt($dO, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($dO, CURLOPT_ENCODING, '');
        curl_setopt($dO, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($dO, CURLOPT_AUTOREFERER, true);
        curl_setopt($dO, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($dO, CURLOPT_MAXREDIRS, 10);
        curl_setopt($dO, CURLOPT_HTTPHEADER, array("\x43\x6f\x6e\x74\x65\156\164\x2d\124\171\x70\x65\x3a\x20\x61\x70\160\154\151\x63\x61\x74\x69\157\156\x2f\152\163\x6f\x6e", $h8, $Jw, $jt));
        curl_setopt($dO, CURLOPT_POST, true);
        curl_setopt($dO, CURLOPT_POSTFIELDS, $Wn);
        curl_setopt($dO, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($dO, CURLOPT_TIMEOUT, 20);
        $dK = curl_exec($dO);
        if (!curl_errno($dO)) {
            goto QI;
        }
        echo "\122\145\161\165\145\x73\164\x20\105\x72\162\x6f\x72\72" . curl_error($dO);
        exit;
        QI:
        curl_close($dO);
        return $dK;
    }
    function submit_feedback_form($Gr)
    {
        $v0 = Mo_saml_Local_Util::getHostname();
        $CN = $v0 . "\57\x6d\x6f\x61\163\x2f\141\160\151\x2f\x6e\157\164\x69\x66\171\x2f\163\145\156\x64";
        $dO = curl_init($CN);
        $tF = Mo_saml_Local_Util::getCustomerDetails();
        $Bn = $tF["\x63\165\163\x74\157\155\145\x72\x5f\153\x65\171"];
        $YK = $tF["\141\x70\x69\137\153\145\171"];
        $As = $tF["\x73\155\x6c\x5f\154\x6b"];
        $bf = round(microtime(true) * 1000);
        $op = $Bn . number_format($bf, 0, '', '') . $YK;
        $lf = hash("\163\x68\141\65\61\62", $op);
        $h8 = "\x43\165\x73\x74\157\x6d\145\162\55\113\x65\x79\x3a\40" . $Bn;
        $Jw = "\x54\x69\x6d\145\x73\x74\141\x6d\160\72\x20" . number_format($bf, 0, '', '');
        $jt = "\101\165\x74\150\x6f\x72\x69\x7a\141\164\151\157\x6e\72\x20" . $lf;
        $jh = $tF["\x65\155\x61\x69\154"];
        $C4 = $tF["\141\144\x6d\151\156\x5f\x70\x68\157\x6e\x65"];
        $QJ = $tF["\165\163\162\x6c\155\x74"];
        $hz = strtotime($tF["\x6c\151\x63\145\x6e\x73\145\x45\170\160\151\x72\x79"]);
        $Bl = strtotime($tF["\163\165\x70\160\x6f\x72\164\x45\170\x70\x69\162\x79"]);
        $hz = $hz === FALSE || $hz <= -62169987208 ? "\x2d" : date("\x46\40\152\54\x20\131\x2c\x20\147\x3a\x69\x20\x61", $hz);
        $Bl = $Bl === FALSE || $Bl <= -62169987208 ? "\55" : date("\106\40\x6a\54\x20\131\54\40\147\x3a\151\40\x61", $Bl);
        $TH = "\x61\x72\141\164\151\x2e\143\x68\141\x75\144\x68\x61\162\x69\x40\170\x65\x63\x75\x72\x69\x66\171\56\x63\157\155";
        $mh = "\163\x6f\x6d\163\x68\x65\x6b\x68\141\162\100\170\x65\143\165\162\x69\x66\171\x2e\143\157\x6d";
        $MF = "\115\x69\x6e\x69\117\162\x61\156\x67\145\40\x4a\157\157\x6d\154\x61\x20\x53\x41\x4d\114\x20\x53\x50\40\105\156\164\x65\162\x70\x72\x69\163\x65\40\x54\162\x69\x61\x6c\40\105\x6e\144";
        $dK = "\74\144\151\166\x20\76\x48\145\154\x6c\x6f\54\40\x3c\x62\162\x3e\74\x62\x72\76\74\163\x74\x72\x6f\x6e\147\x3e\x43\157\155\x70\x61\156\171\40\72\74\141\40\x68\x72\x65\x66\75\42" . $_SERVER["\x53\x45\122\126\x45\x52\137\x4e\101\115\105"] . "\42\40\x74\x61\x72\147\x65\x74\x3d\42\137\x62\x6c\141\156\153\42\x20\x3e\74\57\163\164\162\157\x6e\x67\x3e" . $_SERVER["\123\105\x52\x56\105\x52\137\x4e\101\115\x45"] . "\x3c\57\x61\x3e\x3c\x62\x72\x3e\x3c\x62\162\x3e\74\163\x74\162\x6f\156\x67\x3e\120\150\157\156\x65\x20\x4e\x75\x6d\142\x65\x72\40\x3a\x3c\x2f\163\164\162\x6f\156\147\x3e" . $C4 . "\x3c\142\162\x3e\74\142\162\76\74\x73\164\x72\x6f\x6e\x67\x3e\101\144\x6d\151\x6e\x20\x45\x6d\141\x69\154\40\x3a\x3c\x61\x20\150\162\x65\x66\x3d\x22\x6d\141\x69\x6c\164\157\x3a" . $jh . "\42\40\164\x61\x72\x67\145\164\75\42\137\x62\x6c\141\156\153\42\x3e" . $jh . "\74\57\x61\x3e\74\x2f\x73\164\162\x6f\156\x67\x3e\74\142\162\76\74\x62\x72\76\74\163\164\162\x6f\156\x67\76\x41\165\164\157\40\x63\x72\x65\141\x74\145\x64\x20\x55\x73\145\162\x73\72\x3c\x2f\x73\x74\x72\157\x6e\147\x3e" . $QJ . "\x20\74\142\162\x3e\74\142\x72\76\x3c\x73\x74\162\157\x6e\147\76\x20\114\x69\x63\145\x6e\163\x65\40\x45\170\x70\x69\162\171\72\x3c\57\x73\164\x72\157\156\x67\x3e\x20" . $hz . "\74\x62\x72\x3e\x3c\x62\x72\x3e\74\163\164\162\x6f\156\147\x3e\40\x53\165\x70\x70\157\x72\x74\40\105\170\x70\x69\x72\171\x3a\x3c\57\163\x74\x72\x6f\156\147\x3e\40" . $Bl . "\x3c\x2f\x64\x69\166\x3e";
        if (!$Gr) {
            goto T5;
        }
        $MF = "\x4d\151\x6e\x69\x4f\162\141\x6e\x67\145\40\112\x6f\157\155\x6c\x61\40\123\101\115\x4c\x20\x53\120\x20\105\x6e\x74\x65\162\160\x72\151\163\145\40\124\x72\151\x61\x6c\x20\x54\162\x61\143\x6b\151\156\x67";
        $dK = "\x3c\144\x69\166\40\76\x48\x65\154\154\x6f\54\40\74\142\x72\x3e\74\142\162\x3e\74\163\164\162\157\156\147\76\x43\x6f\x6d\160\141\x6e\x79\40\72\74\x61\40\150\x72\x65\146\75\x22" . $_SERVER["\123\x45\122\126\x45\122\x5f\116\x41\x4d\105"] . "\42\40\x74\x61\162\147\x65\164\75\x22\137\x62\x6c\x61\x6e\153\x22\x20\x3e\x3c\x2f\x73\164\x72\x6f\156\x67\x3e" . $_SERVER["\x53\105\122\x56\105\x52\x5f\x4e\x41\x4d\105"] . "\x3c\x2f\141\x3e\74\142\x72\76\x3c\x62\162\x3e\x3c\x73\x74\x72\x6f\x6e\147\x3e\x50\150\157\156\145\x20\116\x75\155\142\145\162\40\x3a\74\x2f\163\164\162\x6f\156\147\x3e" . $C4 . "\74\x62\x72\x3e\x3c\x62\162\x3e\74\x73\164\x72\x6f\x6e\x67\x3e\101\144\x6d\x69\x6e\x20\x45\x6d\141\151\154\40\x3a\74\141\x20\150\162\145\146\x3d\42\155\x61\x69\x6c\x74\157\x3a" . $jh . "\42\40\x74\141\x72\x67\x65\x74\x3d\x22\x5f\x62\x6c\x61\156\153\42\76" . $jh . "\x3c\57\x61\76\x3c\x2f\x73\164\x72\157\x6e\x67\x3e\x3c\x62\x72\x3e\x3c\142\162\x3e\x3c\x73\x74\x72\x6f\x6e\147\76\x41\x75\164\x6f\x20\143\x72\x65\141\x74\145\144\x20\x55\163\x65\x72\x73\72\x3c\57\163\x74\x72\157\x6e\x67\x3e" . $QJ . "\x20\x3c\142\x72\76\x3c\142\x72\x3e\x3c\x73\164\x72\x6f\156\147\x3e\x20\x4c\x69\x63\x65\x6e\x73\145\40\x45\x78\160\151\162\171\72\x3c\57\163\x74\x72\x6f\x6e\x67\76\40" . $hz . "\74\142\162\x3e\x3c\x62\x72\76\x3c\163\x74\x72\157\x6e\x67\76\x20\x53\165\160\160\157\162\x74\x20\x45\x78\160\x69\x72\171\x3a\x3c\x2f\x73\x74\x72\x6f\156\147\x3e\x20" . $Bl . "\x3c\142\x72\76\74\142\x72\76\74\163\x74\162\157\156\147\76\40\103\x75\162\162\145\x6e\164\40\104\141\x74\145\x3a\74\57\163\164\162\157\156\147\76\40" . date("\144\x2d\x6d\55\171\x20\150\x3a\151\72\x73") . "\74\57\x64\151\166\76";
        T5:
        $LD = array("\143\x75\163\164\157\x6d\x65\162\113\145\171" => $Bn, "\x73\x65\156\144\x45\155\x61\x69\154" => true, "\x65\155\141\151\x6c" => array("\143\165\x73\164\157\155\145\x72\113\x65\171" => $Bn, "\146\162\157\155\105\x6d\141\151\154" => $jh, "\142\x63\143\105\x6d\141\x69\154" => $mh, "\146\162\157\x6d\116\141\155\145" => "\x6d\x69\x6e\x69\x4f\162\x61\x6e\147\145", "\x74\157\x45\155\141\x69\154" => $TH, "\x74\157\x4e\x61\x6d\x65" => $mh, "\163\165\x62\x6a\x65\x63\164" => $MF, "\143\157\156\x74\145\156\164" => $dK));
        $Wn = json_encode($LD);
        curl_setopt($dO, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($dO, CURLOPT_ENCODING, '');
        curl_setopt($dO, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($dO, CURLOPT_AUTOREFERER, true);
        curl_setopt($dO, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($dO, CURLOPT_MAXREDIRS, 10);
        curl_setopt($dO, CURLOPT_HTTPHEADER, array("\103\x6f\156\x74\x65\x6e\164\55\124\171\160\145\x3a\x20\141\160\160\x6c\x69\143\141\x74\151\x6f\x6e\57\x6a\163\157\156", $h8, $Jw, $jt));
        curl_setopt($dO, CURLOPT_POST, true);
        curl_setopt($dO, CURLOPT_POSTFIELDS, $Wn);
        $dK = curl_exec($dO);
        if (!curl_errno($dO)) {
            goto jO;
        }
        echo "\122\x65\x71\165\x65\x73\164\40\x45\162\162\x6f\x72\72" . curl_error($dO);
        exit;
        jO:
        curl_close($dO);
        return $dK;
    }
    public static function send_email_alert($MF, $WE)
    {
        $v0 = Mo_saml_Local_Util::getHostname();
        $CN = $v0 . "\x2f\x6d\x6f\141\163\57\x61\x70\151\57\x6e\157\164\x69\x66\171\x2f\x73\x65\x6e\144";
        $dO = curl_init($CN);
        $tF = Mo_saml_Local_Util::getCustomerDetails();
        $Bn = $tF["\x63\x75\163\x74\157\x6d\x65\162\x5f\153\145\171"];
        $YK = $tF["\x61\x70\x69\137\x6b\145\x79"];
        $As = $tF["\163\155\154\137\x6c\x6b"];
        $bf = round(microtime(true) * 1000);
        $op = $Bn . number_format($bf, 0, '', '') . $YK;
        $lf = hash("\163\150\141\x35\61\62", $op);
        $h8 = "\x43\165\x73\x74\x6f\x6d\x65\x72\x2d\113\x65\171\x3a\40" . $Bn;
        $Jw = "\124\151\x6d\x65\x73\164\x61\155\160\x3a\x20" . number_format($bf, 0, '', '');
        $jt = "\x41\x75\164\x68\x6f\162\151\172\141\164\x69\x6f\x6e\72\40" . $lf;
        $qO = $tF["\145\155\x61\x69\x6c"];
        $kc = "\x6a\x6f\x6f\155\154\x61\163\x75\160\x70\x6f\162\164\100\170\x65\x63\x75\x72\151\146\171\56\143\x6f\x6d";
        $LD = array("\x63\x75\163\164\157\155\x65\162\x4b\145\x79" => $Bn, "\163\145\156\x64\105\155\141\151\154" => true, "\145\x6d\x61\x69\154" => array("\143\165\x73\x74\157\155\145\x72\x4b\145\171" => $Bn, "\146\x72\157\155\105\155\141\x69\x6c" => $kc, "\146\162\157\x6d\x4e\141\155\x65" => "\155\x69\156\151\x4f\x72\x61\156\147\x65", "\x74\x6f\x45\155\x61\151\154" => $qO, "\164\157\x4e\x61\155\145" => $qO, "\x62\143\x63\x45\x6d\141\151\154" => $kc, "\x73\165\x62\152\x65\x63\164" => $MF, "\143\157\x6e\x74\145\x6e\164" => $WE));
        $Wn = json_encode($LD);
        curl_setopt($dO, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($dO, CURLOPT_ENCODING, '');
        curl_setopt($dO, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($dO, CURLOPT_AUTOREFERER, true);
        curl_setopt($dO, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($dO, CURLOPT_MAXREDIRS, 10);
        curl_setopt($dO, CURLOPT_HTTPHEADER, array("\103\157\156\164\145\156\x74\55\x54\171\x70\x65\x3a\x20\x61\160\x70\x6c\151\143\141\x74\x69\x6f\156\57\152\163\157\156", $h8, $Jw, $jt));
        curl_setopt($dO, CURLOPT_POST, true);
        curl_setopt($dO, CURLOPT_POSTFIELDS, $Wn);
        $dK = json_decode(curl_exec($dO));
        if (!($dK->status == "\x53\x55\103\103\x45\123\123")) {
            goto CM;
        }
        UtilitiesSAML::_update_lid("\x6d\151\x6e\151\157\x72\x61\156\x67\x65\137\x6c\145\x78\160\137\156\x6f\164\151\146\x69\143\141\x74\151\x6f\x6e\x5f\163\x65\x6e\x74");
        CM:
        if (!curl_errno($dO)) {
            goto ZS;
        }
        return json_encode(array("\x73\164\141\164\x75\163" => "\x45\x52\122\x4f\122", "\163\x74\x61\164\x75\x73\x4d\x65\x73\163\141\147\x65" => curl_error($dO)));
        ZS:
        curl_close($dO);
        return json_encode(array("\x73\164\141\x74\x75\x73" => "\123\x55\x43\103\105\x53\123", "\163\x74\x61\164\165\x73\x4d\145\x73\x73\x61\147\145" => "\x53\125\103\103\105\x53\123"));
    }
}
