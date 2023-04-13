<?php


defined("\137\x4a\x45\x58\x45\103") or die("\122\145\163\x74\x72\x69\143\x74\145\144\x20\x61\143\x63\145\x73\163");
jimport("\152\x6f\157\155\154\141\56\160\154\x75\147\151\x6e\x2e\160\x6c\165\x67\x69\156");
class plgSystemSamlredirect extends JPlugin
{
    function onAfterRender()
    {
        $ht = JFactory::getApplication();
        $xF = $ht->getBody();
        $Og = JURI::root();
        $nL = JFactory::getApplication()->input->get->getArray();
        $Tb = UtilitiesSAML::getExpiryDate();
        $l8 = isset($Tb["\154\151\143\145\x6e\163\x65\105\170\160\151\162\171"]) ? date("\106\40\x6a\54\40\x59\54\40\147\72\x69\x20\141", strtotime($Tb["\154\151\143\145\x6e\x73\145\x45\x78\160\151\x72\x79"])) : '';
        $F9 = UtilitiesSAML::checkIsLicenseExpired();
        $OG = $F9["\114\151\x63\x65\x6e\x73\145\105\x78\160\151\x72\145\144"] == 1 ? UtilitiesSAML::showLicenseExpiryMessage($F9) : 0;
        $aU = $F9["\114\x69\143\x65\x6e\x73\145\x45\170\x70\x69\162\171"] == 1 ? UtilitiesSAML::showLicenseExpiryMessage($F9) : 0;
        $hQ = UtilitiesSAML::getJoomlaCmsVersion();
        $hQ = substr($hQ, 0, 3);
        $nL = JFactory::getApplication()->input->get->getArray();
        if (!($OG || $aU)) {
            goto NZ;
        }
        if (!(stristr($xF, "\143\x6f\x6e\164\x65\156\x74") && !isset($nL["\x6f\160\164\151\x6f\156"]))) {
            goto rR;
        }
        $hE = UtilitiesSAML::renewalMessage($F9, $l8, "\x68\157\x6d\x65");
        $GA = "\74\x64\151\166\40\x63\x6c\141\x73\x73\75\42\x63\157\x6e\x74\141\x69\156\145\162\x2d\x66\154\x75\x69\144\x20\x63\x6f\156\164\141\x69\x6e\145\162\x2d\x6d\x61\151\156\x22\x3e" . $hE;
        $xF = str_replace("\74\x64\x69\166\x20\x63\x6c\141\163\x73\x3d\42\143\157\x6e\x74\141\x69\156\x65\162\x2d\146\154\x75\x69\144\x20\x63\x6f\156\x74\x61\x69\x6e\x65\x72\55\155\x61\151\156\42\76", $GA, $xF);
        $ht->setBody($xF);
        rR:
        if (!(stristr($xF, "\x74\x6f\x6f\x6c\142\141\162") && stristr($xF, "\152\x6f\x6f\x6d\154\x61\55\164\157\x6f\154\142\x61\x72\x2d\x62\165\164\x74\x6f\156"))) {
            goto lZ;
        }
        $hE = UtilitiesSAML::renewalMessage($F9, $l8, "\160\154\x75\147\x69\156");
        $qy = "\40\x3c\x6a\157\x6f\x6d\154\x61\x2d\164\x6f\x6f\154\142\141\x72\x2d\142\x75\x74\164\157\x6e\40\143\x6c\x61\163\x73\x3d\42\x6d\163\x2d\x61\x75\x74\x6f\x22\x3e" . $hE;
        $xF = str_replace("\74\152\x6f\x6f\155\154\141\55\x74\x6f\x6f\154\142\141\x72\x2d\142\x75\x74\164\157\x6e\40\x63\154\x61\x73\163\75\42\x6d\x73\55\141\165\x74\x6f\x22\x3e", $qy, $xF);
        $ht->setBody($xF);
        lZ:
        NZ:
    }
    public function onAfterInitialise()
    {
        jimport("\155\151\156\x69\157\x72\x61\156\147\145\x73\141\x6d\154\x70\154\x75\x67\151\156\56\x75\164\151\154\151\164\x79\56\125\x74\151\154\151\164\151\x65\163\123\101\x4d\114");
        require_once JPATH_ROOT . DIRECTORY_SEPARATOR . "\141\x64\155\x69\x6e\x69\x73\164\x72\x61\164\x6f\162" . DIRECTORY_SEPARATOR . "\143\157\x6d\x70\x6f\156\x65\156\164\163" . DIRECTORY_SEPARATOR . "\x63\157\x6d\x5f\155\x69\x6e\x69\157\162\141\156\x67\145\x5f\163\x61\x6d\154" . DIRECTORY_SEPARATOR . "\x68\145\154\160\145\x72\163" . DIRECTORY_SEPARATOR . "\x6d\x6f\55\163\141\155\154\55\x75\x74\151\x6c\151\x74\x79\56\x70\x68\x70";
        $post = JFactory::getApplication()->input->post->getArray();
        $nL = JFactory::getApplication()->input->get->getArray();
        $Lb = JFactory::getApplication()->input->request->getArray();
        $xJ = UtilitiesSAML::getSAMLConfiguration();
        $F9 = UtilitiesSAML::checkIsLicenseExpired();
        $QY = UtilitiesSAML::getCustomerDetails();
        if (!($F9["\x4c\x69\143\145\x6e\163\145\x54\162\151\x61\154\105\170\160\x69\162\171"] && !empty($QY["\163\155\x6c\x5f\154\x6b"]))) {
            goto Ek;
        }
        UtilitiesSAML::fetchTLicense();
        Ek:
        $Cd = isset($post["\155\x6f\x5f\162\x65\155\x6f\166\145\x5f\166\141\x6c\x75\145\x5f\x64\x6f\155\141\x69\156"]) ? $post["\x6d\x6f\x5f\162\145\155\x6f\166\x65\x5f\166\141\154\165\145\137\x64\x6f\155\x61\x69\156"] : '';
        if (empty($QY["\x73\155\154\x5f\154\153"])) {
            goto n_;
        }
        $Sb = $QY["\x74\x72\151\163\164\x73"];
        $Sb = Mo_saml_Local_Util::decrypt_value($Sb);
        if (!($Sb == true)) {
            goto jU;
        }
        $F9 = UtilitiesSAML::gt_lk_trl();
        $Sw = 0;
        UtilitiesSAML::auto_fetch_details();
        if (!$F9["\x4c\151\x63\145\156\x73\145\x45\x78\160\151\x72\x65\144"]) {
            goto JO;
        }
        UtilitiesSAML::rmvextnsns();
        $AA = UtilitiesSAML::get_message_and_cause($F9["\114\x69\x63\x65\156\x73\x65\105\x78\160\x69\162\x65\144"], $Sw);
        UtilitiesSAML::show_error_messages($AA["\155\x73\147"], $AA["\x63\141\165\x73\145"]);
        JO:
        jU:
        n_:
        if (!(!empty($Cd) || '' != $Cd)) {
            goto EN;
        }
        if (!($Cd == "\x6d\157\x5f\162\145\155\157\166\145\137\x64\157\155\x61\x69\x6e\137\155\x61\160")) {
            goto AN;
        }
        $y5 = isset($post["\162\x65\x6d\157\166\x65\137\166\141\x6c\x75\145\137\144\x6f\x6d\x61\151\x6e\x5f\x6d\141\x70\x70"]) ? $post["\162\145\155\157\166\145\137\166\x61\154\165\145\137\x64\157\155\141\151\156\x5f\x6d\x61\x70\x70"] : '';
        UtilitiesSAML::_remove_domain_mapp($y5);
        AN:
        EN:
        $ns = '';
        if (!(isset($nL["\x69\x64\x70"]) && !empty($nL["\x69\144\160"]))) {
            goto nT;
        }
        $ns = $nL["\x69\144\x70"];
        nT:
        if (isset($nL["\x6d\x6f\162\145\161\165\145\x73\x74"]) && $nL["\155\157\162\145\161\x75\x65\x73\x74"] == "\x73\x73\157") {
            goto gs;
        }
        if (isset($nL["\x6d\x6f\x72\x65\x71\x75\145\163\164"]) && $nL["\x6d\157\162\145\x71\x75\x65\x73\164"] == "\x61\143\x73") {
            goto ef;
        }
        if (isset($nL["\155\157\162\x65\x71\x75\145\x73\164"]) && $nL["\x6d\x6f\162\145\161\165\145\163\164"] == "\x6d\145\x74\x61\144\x61\164\x61") {
            goto WW;
        }
        if (isset($nL["\155\x6f\162\145\x71\x75\x65\163\164"]) && $nL["\155\157\162\x65\x71\165\145\163\164"] == "\x64\157\167\156\x6c\157\x61\x64\x5f\x6d\x65\164\141\144\x61\x74\x61") {
            goto GB;
        }
        if (isset($nL["\155\157\x72\145\161\x75\145\x73\x74"]) && $nL["\155\157\x72\x65\161\x75\145\x73\x74"] == "\x64\x6f\x77\156\154\x6f\x61\x64\137\x63\145\162\164") {
            goto e3;
        }
        if (!(array_key_exists("\123\101\115\x4c\122\145\x73\x70\157\156\163\x65", $Lb) && !empty($Lb["\x53\101\x4d\x4c\122\x65\x73\160\157\x6e\163\x65"]))) {
            goto sw;
        }
        $v2 = $Lb["\x53\x41\x4d\x4c\x52\145\163\x70\x6f\x6e\163\145"];
        $v2 = base64_decode($v2);
        if (!(array_key_exists("\x53\x41\x4d\x4c\x52\145\x73\160\157\x6e\x73\x65", $nL) && !empty($nL["\123\101\x4d\114\122\145\x73\x70\157\156\163\145"]))) {
            goto jz;
        }
        $v2 = gzinflate($v2);
        jz:
        $this->getSamlLogoutResponse($v2);
        sw:
        goto UT;
        e3:
        $this->downloadCert($xJ);
        UT:
        goto iE;
        GB:
        $this->generateMetadata($xJ, $cB = true);
        iE:
        goto tF;
        WW:
        $this->generateMetadata($xJ);
        tF:
        goto fy;
        ef:
        $this->getSamlResponse();
        fy:
        goto TM;
        gs:
        $nv = UtilitiesSAML::getSAMLConfiguration($ns);
        $this->sendSamlRequest($QY, $nv[0]);
        TM:
        if (!(isset($_COOKIE["\x6d\x6f\163\141\x6d\x6c\162\x65\x64\x69\x72\145\143\164"]) && $_COOKIE["\155\157\163\141\155\x6c\x72\x65\144\x69\162\x65\x63\164"] != "\x2d\61")) {
            goto oY;
        }
        $this->adminDashboardLogin($xJ);
        oY:
        $iS = isset($QY["\145\x6e\x61\x62\x6c\x65\x5f\155\x61\156\141\x67\145\x72\137\154\157\147\151\x6e"]) ? $QY["\145\156\141\x62\x6c\145\137\155\x61\x6e\x61\x67\x65\x72\x5f\154\x6f\147\151\156"] : 0;
        $c2 = isset($QY["\x65\156\141\x62\x6c\x65\137\141\144\155\x69\156\137\x72\145\x64\x69\x72\x65\143\164"]) ? $QY["\145\x6e\141\142\x6c\145\137\x61\x64\155\x69\156\137\162\145\144\x69\x72\x65\x63\164"] : 0;
        $yy = isset($QY["\x65\x6e\x61\142\154\x65\x5f\x72\145\144\151\162\x65\x63\164"]) ? $QY["\145\x6e\141\x62\x6c\x65\x5f\x72\145\x64\x69\x72\x65\143\164"] : 0;
        $Mh = isset($QY["\145\156\141\142\154\x65\137\141\x64\x6d\151\156\137\x63\150\151\154\144\137\x6c\x6f\147\151\156"]) ? $QY["\x65\156\x61\x62\154\145\137\x61\144\x6d\151\156\x5f\x63\x68\x69\x6c\144\137\x6c\157\x67\x69\156"] : 0;
        $l6 = isset($QY["\145\156\141\x62\154\x65\x5f\x6d\141\x6e\141\147\145\162\x5f\143\150\151\154\x64\137\x6c\157\x67\151\156"]) ? $QY["\145\156\x61\142\154\x65\137\x6d\x61\x6e\x61\147\145\162\x5f\143\x68\x69\x6c\x64\137\154\x6f\147\x69\x6e"] : 0;
        if (!($iS || $c2 || $yy || $Mh || $l6)) {
            goto rB;
        }
        self::autoRedirectIoIDP();
        rB:
    }
    function isLoginRportAddonEnable()
    {
        $n4 = false;
        if (!file_exists(JPATH_PLUGINS . "\57\x75\x73\x65\162\x2f\x6d\x69\x6e\151\157\162\x61\x6e\147\145\x6c\x6f\x67\151\x6e\162\145\x70\157\162\x74\57\155\151\156\x69\x6f\x72\141\x6e\x67\x65\x6c\x6f\147\151\x6e\162\x65\160\157\162\164\56\x70\x68\160")) {
            goto tb;
        }
        require_once JPATH_PLUGINS . "\x2f\x75\x73\145\x72\57\x6d\151\156\151\157\162\141\x6e\x67\x65\x6c\x6f\x67\x69\156\x72\145\x70\x6f\162\x74\x2f\155\151\156\151\157\x72\x61\156\147\145\154\157\147\151\x6e\162\145\160\157\x72\164\x2e\160\x68\x70";
        $n4 = plgUserMiniorangeloginreport::loginreport_addon();
        tb:
        return $n4;
    }
    function autoRedirectIoIDP()
    {
        $QY = UtilitiesSAML::getCustomerDetails();
        $user = JFactory::getUser();
        $ht = JFactory::getApplication("\x73\151\164\145");
        $hQ = UtilitiesSAML::getJoomlaCmsVersion();
        $hQ = substr($hQ, 0, 3);
        $nL = JFactory::getApplication()->input->get->getArray();
        if (!($hQ < 4.0)) {
            goto NG;
        }
        $ht->initialise();
        NG:
        $Lb = JFactory::getApplication()->input->request->getArray();
        $sY = 0;
        $wA = JPATH_ROOT . DIRECTORY_SEPARATOR . "\160\154\165\147\151\156\x73" . DIRECTORY_SEPARATOR . "\x73\x79\163\x74\x65\x6d" . DIRECTORY_SEPARATOR . "\155\151\x6e\x69\x6f\162\141\156\x67\145\160\141\x67\x65\162\145\163\164\162\x69\x63\x74\151\157\156" . DIRECTORY_SEPARATOR . "\x6d\x69\156\x69\x6f\162\141\x6e\x67\145\x70\141\x67\145\x72\145\x73\x74\x72\x69\143\164\151\157\156\x2e\x70\150\x70";
        if (!file_exists($wA)) {
            goto aT;
        }
        include_once JPATH_ROOT . DIRECTORY_SEPARATOR . "\160\154\x75\x67\x69\156\x73" . DIRECTORY_SEPARATOR . "\163\x79\163\x74\x65\x6d" . DIRECTORY_SEPARATOR . "\x6d\x69\x6e\x69\x6f\x72\141\156\147\x65\160\141\147\x65\x72\145\x73\x74\162\151\143\164\x69\x6f\156" . DIRECTORY_SEPARATOR . "\155\x69\x6e\151\x6f\x72\141\156\x67\145\x70\141\x67\145\x72\x65\x73\164\x72\151\143\x74\151\x6f\x6e\x2e\x70\x68\160";
        $f3 = plgSystemMiniorangepagerestriction::getCustomerDetails();
        $sY = $f3["\x65\156\141\142\154\x65\x5f\160\x61\x67\x65\x5f\162\x65\x73\x74\162\151\143\164\x69\157\x6e"];
        aT:
        if ($user->id == 0 && !isset($Lb["\155\x6f\162\145\161\165\145\163\164"]) && !isset($_COOKIE["\x62\x61\143\153\144\x6f\157\162\x5f\165\162\154\137\x73\145\164"]) && $sY == 0) {
            goto eU;
        }
        if ($user->id == 0 && !isset($_COOKIE["\162\x65\x71\165\x65\x73\x74\x5f\x75\x72\x69\137\x72\x65\154\141\x79\137\163\164\141\164\x65"]) && $sY == 1) {
            goto pW;
        }
        if (isset($_COOKIE["\162\x65\161\165\x65\163\x74\x5f\x75\x72\x69\x5f\x72\x65\154\141\x79\137\163\164\x61\164\x65"])) {
            goto is;
        }
        if (!isset($_COOKIE["\142\x61\143\x6b\x64\157\157\x72\x5f\x75\162\154\x5f\x73\145\x74"])) {
            goto Lt;
        }
        unset($_COOKIE["\x62\x61\x63\x6b\x64\157\157\x72\x5f\x75\x72\154\137\163\145\164"]);
        Lt:
        goto vY;
        is:
        unset($_COOKIE["\162\x65\x71\x75\145\163\x74\x5f\x75\162\151\x5f\x72\x65\154\141\x79\x5f\x73\164\141\x74\x65"]);
        vY:
        goto N2;
        pW:
        plgSystemMiniorangepagerestriction::page_resitriction_flow();
        N2:
        goto U8;
        eU:
        $q_ = !empty($_SERVER["\x48\x54\124\120\123"]) && $_SERVER["\110\124\x54\x50\123"] !== "\157\x66\x66" || $_SERVER["\x53\105\x52\x56\105\x52\x5f\120\x4f\x52\x54"] == 443 ? "\x68\x74\164\x70\x73\x3a\57\57" : "\150\x74\x74\160\72\57\57";
        $hg = $q_ . "{$_SERVER["\x48\124\124\x50\137\110\x4f\123\124"]}{$_SERVER["\x52\105\121\x55\105\x53\x54\137\125\x52\111"]}";
        $XH = $QY["\x69\144\160\137\x6c\151\156\x6b\137\160\x61\x67\x65"];
        $uA = $QY["\145\x6e\141\x62\x6c\145\x5f\141\x64\155\x69\x6e\137\162\145\x64\151\162\x65\x63\164"];
        $Qm = $QY["\145\x6e\141\142\x6c\145\137\x72\145\x64\151\x72\145\x63\x74"];
        $nT = $QY["\x65\x6e\141\x62\x6c\145\x5f\155\x61\156\x61\147\145\162\137\154\x6f\147\x69\x6e"];
        $Mh = $QY["\x65\156\x61\142\154\145\137\141\x64\155\x69\x6e\x5f\143\x68\x69\x6c\144\137\x6c\x6f\x67\151\x6e"];
        $l6 = $QY["\x65\x6e\x61\x62\x6c\145\137\155\x61\x6e\141\x67\x65\x72\137\x63\150\x69\154\x64\x5f\154\157\147\x69\156"];
        $T1 = $QY["\155\x6f\137\x61\144\155\151\156\x5f\x69\144\x70\137\154\151\163\164\137\154\x69\156\153\x5f\160\x61\147\145"];
        $f4 = "\x72\145\x71\x75\x65\163\x74\137\165\x72\151\x5f\162\x65\x6c\141\x79\x5f\163\164\x61\164\145";
        $j7 = "\x69\156\151\164\137\x72\145\161\x75\145\163\x74\x5f\165\x72\x69\137\162\145\154\x61\x79\x5f\x73\164\x61\164\145";
        $A2 = $hg;
        setcookie($f4, $A2, time() + 5, "\57");
        setcookie($j7, $A2, time() + 5, "\57");
        if (isset($_COOKIE["\155\157\x73\141\155\x6c\141\165\164\150\x61\144\x6d\151\x6e"]) && $_COOKIE["\155\157\x73\141\155\154\141\x75\164\x68\x61\x64\x6d\x69\156"] != "\55\x31") {
            goto qT;
        }
        if ($hg != $XH && !strpos($hg, "\141\x64\x6d\x69\x6e\x69\x73\164\162\x61\164\x6f\162") && $Qm == 1) {
            goto tJ;
        }
        if (!($hg != $XH && ($uA == 1 || $nT == 1 || $Mh == 1 || $l6 == 1) && strpos($hg, "\141\144\x6d\x69\x6e\x69\x73\164\x72\141\164\x6f\162"))) {
            goto S9;
        }
        if (isset($nL["\x6d\157\160\x61\163\x73\141\144\155\x69\x6e\x73\x73\x6f"]) && $nL["\x6d\157\160\x61\x73\163\141\x64\x6d\151\x6e\163\163\x6f"] == "\x74\162\165\x65") {
            goto hz;
        }
        header("\114\x6f\x63\x61\164\151\x6f\x6e\x3a\40" . $T1);
        exit;
        goto il;
        hz:
        setcookie("\x62\x61\x63\x6b\144\157\157\162\x5f\165\x72\x6c\137\x73\x65\x74", $A2, time() + 50, "\57");
        header("\114\x6f\143\x61\x74\151\157\156\x3a\x20" . $hg);
        exit;
        il:
        S9:
        goto vC;
        tJ:
        header("\114\157\143\141\x74\151\x6f\x6e\x3a\x20" . $XH);
        exit;
        vC:
        goto lf;
        qT:
        unset($_COOKIE["\162\145\x71\165\x65\163\164\x5f\x75\x72\x69\x5f\x72\x65\x6c\141\x79\137\163\x74\141\x74\x65"]);
        lf:
        U8:
    }
    function getSamlLogoutResponse($v2)
    {
        $Lb = JFactory::getApplication()->input->request->getArray();
        $o2 = new DOMDocument();
        $v2 = str_replace("\46", "\46\141\155\160\73", $v2);
        $o2->loadXML($v2);
        $lG = $o2->firstChild;
        if (!($lG->localName == "\x4c\157\147\x6f\x75\164\x52\145\x73\160\x6f\x6e\x73\x65")) {
            goto PY;
        }
        $ht = JFactory::getApplication("\163\151\x74\x65");
        $Og = JURI::root();
        if (!isset($Lb["\x52\x65\154\x61\171\123\164\141\x74\145"])) {
            goto Zs;
        }
        $Og = $Lb["\x52\x65\x6c\141\171\x53\x74\x61\164\145"];
        Zs:
        $Zz = strpos($Og, "\77");
        if (!($Zz !== false)) {
            goto zi;
        }
        $Og = substr($Og, 0, $Zz);
        zi:
        $ht->redirect($Og);
        PY:
    }
    function sendSamlRequest($f1, $nv)
    {
        $Mn = '';
        $Ox = '';
        if (!isset($f1["\163\160\137\142\x61\x73\145\x5f\165\x72\x6c"])) {
            goto kE;
        }
        $Mn = $f1["\163\x70\x5f\x62\x61\x73\x65\137\165\x72\154"];
        $Ox = $f1["\163\x70\x5f\145\x6e\164\151\164\171\137\151\x64"];
        kE:
        $ei = JURI::root();
        if (!empty($Mn)) {
            goto iV;
        }
        $Mn = $ei;
        iV:
        if (!empty($Ox)) {
            goto Bn;
        }
        $Ox = $ei . "\160\154\x75\x67\151\156\x73\57\141\165\164\150\x65\x6e\164\151\x63\141\x74\151\157\156\57\155\151\x6e\151\x6f\162\x61\156\x67\145\x73\141\x6d\x6c";
        Bn:
        if (defined("\x5f\112\x44\x45\106\x49\x4e\x45\x53")) {
            goto Xc;
        }
        require_once JPATH_BASE . "\x2f\x69\156\x63\x6c\165\144\145\x73\57\x64\145\146\151\x6e\145\x73\56\160\x68\x70";
        Xc:
        require_once JPATH_BASE . "\57\151\156\x63\x6c\x75\144\145\163\57\x66\x72\141\155\x65\167\x6f\162\153\x2e\160\x68\160";
        $ht = JFactory::getApplication("\163\x69\x74\145");
        $hQ = UtilitiesSAML::getJoomlaCmsVersion();
        $hQ = substr($hQ, 0, 3);
        if (!($hQ < 4.0)) {
            goto Sk;
        }
        $ht->initialise();
        Sk:
        $q6 = $Mn;
        $user = JFactory::getUser();
        $ks = $Mn . "\77\x6d\157\x72\x65\161\x75\145\x73\164\x3d\x61\143\163";
        $xu = $nv["\x73\x69\156\147\154\145\x5f\163\x69\147\156\157\x6e\137\x73\145\162\x76\151\x63\x65\137\x75\x72\x6c"];
        $pk = $nv["\142\151\156\x64\151\x6e\x67"];
        $Wv = $nv["\x6d\x6f\x5f\x73\141\155\154\x5f\163\x65\x6c\x65\x63\164\x5f\x73\151\x67\156\x5f\x61\x6c\147\x6f"];
        $Gh = $nv["\x73\141\155\154\137\162\145\x71\165\x65\163\164\137\x73\151\147\156"];
        $wu = $nv["\156\x61\155\145\x5f\x69\x64\x5f\146\157\162\x6d\141\164"];
        $Go = isset($nv["\x41\x75\164\150\x6e\x43\157\156\x74\x65\170\x74\103\154\141\163\163\x52\x65\x66"]) ? $nv["\x41\165\x74\150\156\x43\157\x6e\164\x65\170\164\103\x6c\x61\x73\x73\122\145\146"] : "\x50\x61\163\x73\167\157\162\144\x50\162\x6f\164\145\143\164\x64\x54\162\141\x6e\x73\x70\157\x72\164";
        $Lb = JFactory::getApplication()->input->request->getArray();
        $Vo = $this->getRelayState($Mn, $Lb);
        $IA = UtilitiesSAML::createAuthnRequest($ks, $Ox, $xu, $wu, $Go, "\146\x61\154\x73\x65", $pk);
        $this->sendSamlRequestByBindingType($IA, $pk, $Vo, $xu, $Wv, $Gh);
    }
    function sendSamlRequestByBindingType($IA, $pk, $Vo, $xu, $Wv, $Gh)
    {
        $Ru = UtilitiesSAML::getSAMLConfiguration();
        if (empty($pk) || $pk == "\x48\124\124\120\55\x52\x65\144\151\162\145\143\164") {
            goto Z8;
        }
        $vJ = UtilitiesSAML::get_public_private_certificate($Ru, "\160\162\151\x76\141\x74\x65\137\143\x65\162\164\x69\146\151\x63\x61\164\x65");
        $ef = UtilitiesSAML::get_public_private_certificate($Ru, "\x70\165\142\x6c\x69\x63\137\143\145\162\164\x69\146\151\143\x61\x74\145");
        if ($vJ == null || $vJ == '') {
            goto c1;
        }
        $I1 = JPATH_BASE . DIRECTORY_SEPARATOR . "\x70\154\x75\147\x69\156\x73" . DIRECTORY_SEPARATOR . "\141\165\x74\150\145\x6e\x74\x69\x63\141\164\151\157\x6e" . DIRECTORY_SEPARATOR . "\155\x69\x6e\x69\x6f\x72\141\156\147\145\x73\141\155\154" . DIRECTORY_SEPARATOR . "\163\141\155\154\x32" . DIRECTORY_SEPARATOR . "\143\145\162\164" . DIRECTORY_SEPARATOR . "\103\x75\x73\164\157\155\120\x72\x69\166\x61\164\x65\x43\x65\162\164\x69\146\151\143\x61\164\x65\56\x6b\x65\171";
        goto kB;
        c1:
        $I1 = JPATH_BASE . DIRECTORY_SEPARATOR . "\x70\x6c\x75\x67\151\x6e\x73" . DIRECTORY_SEPARATOR . "\x61\x75\x74\x68\145\x6e\x74\x69\x63\141\164\151\x6f\156" . DIRECTORY_SEPARATOR . "\155\151\156\151\x6f\162\141\156\x67\145\163\141\x6d\154" . DIRECTORY_SEPARATOR . "\163\141\x6d\154\62" . DIRECTORY_SEPARATOR . "\143\x65\162\164" . DIRECTORY_SEPARATOR . "\163\160\x2d\153\x65\x79\x2e\x6b\x65\171";
        kB:
        if ($ef == null || $ef == '') {
            goto ES;
        }
        $pZ = JPATH_BASE . DIRECTORY_SEPARATOR . "\x70\154\x75\147\151\x6e\163" . DIRECTORY_SEPARATOR . "\141\165\x74\x68\x65\156\164\x69\143\x61\164\x69\x6f\x6e" . DIRECTORY_SEPARATOR . "\155\151\156\151\157\x72\x61\x6e\147\x65\163\x61\155\x6c" . DIRECTORY_SEPARATOR . "\163\x61\x6d\154\x32" . DIRECTORY_SEPARATOR . "\x63\145\x72\164" . DIRECTORY_SEPARATOR . "\x43\x75\x73\164\157\x6d\120\165\x62\154\x69\x63\103\x65\x72\164\151\146\151\143\x61\164\145\x2e\x63\162\164";
        goto aG;
        ES:
        $pZ = JPATH_BASE . DIRECTORY_SEPARATOR . "\160\x6c\165\147\151\156\163" . DIRECTORY_SEPARATOR . "\x61\x75\164\x68\145\x6e\x74\x69\x63\x61\164\x69\157\x6e" . DIRECTORY_SEPARATOR . "\x6d\x69\156\151\x6f\162\x61\156\x67\145\x73\x61\155\154" . DIRECTORY_SEPARATOR . "\x73\141\x6d\154\62" . DIRECTORY_SEPARATOR . "\143\x65\162\x74" . DIRECTORY_SEPARATOR . "\x73\160\55\143\x65\x72\x74\x69\146\151\143\x61\x74\145\56\143\162\164";
        aG:
        $Zo = UtilitiesSAML::signXML($IA, $pZ, $I1, $Wv, "\x4e\x61\155\x65\x49\104\x50\157\x6c\151\143\171");
        UtilitiesSAML::postSAMLRequest($xu, $Zo, $Vo);
        goto Y9;
        Z8:
        $zt = $xu;
        if (strpos($xu, "\x3f") !== false) {
            goto zL;
        }
        $zt .= "\x3f";
        goto dL;
        zL:
        $zt .= "\46";
        dL:
        if (!($Gh !== "\163\151\x67\156\145\x64")) {
            goto Sw;
        }
        $zt .= "\x53\x41\x4d\114\122\x65\x71\165\145\x73\164\x3d" . $IA . "\x26\x52\145\x6c\141\171\123\x74\141\164\x65\x3d" . urlencode($Vo);
        header("\x4c\157\143\141\x74\x69\157\x6e\x3a\x20" . $zt);
        exit;
        Sw:
        if ($Wv == "\122\123\x41\x5f\123\x48\101\x32\x35\66") {
            goto R1;
        }
        if ($Wv == "\122\x53\101\137\123\x48\x41\x33\70\x34") {
            goto yy;
        }
        if ($Wv == "\122\123\x41\137\123\x48\x41\x35\x31\62") {
            goto Qt;
        }
        $IA = "\x53\x41\x4d\x4c\122\145\161\x75\x65\x73\x74\x3d" . $IA . "\46\x52\145\x6c\x61\171\x53\164\141\164\x65\x3d" . urlencode($Vo) . "\x26\x53\x69\147\101\x6c\x67\75" . urlencode(XMLSecurityKeySAML::RSA_SHA1);
        goto Cy;
        R1:
        $IA = "\x53\101\x4d\x4c\122\145\161\x75\x65\x73\x74\75" . $IA . "\46\122\x65\x6c\x61\x79\123\x74\x61\x74\145\75" . urlencode($Vo) . "\x26\123\x69\x67\101\x6c\147\75" . urlencode(XMLSecurityKeySAML::RSA_SHA256);
        goto Cy;
        yy:
        $IA = "\x53\101\115\114\x52\x65\x71\165\145\x73\x74\75" . $IA . "\x26\x52\x65\x6c\x61\x79\x53\164\141\164\x65\x3d" . urlencode($Vo) . "\46\123\151\147\101\x6c\x67\x3d" . urlencode(XMLSecurityKeySAML::RSA_SHA384);
        goto Cy;
        Qt:
        $IA = "\x53\101\115\114\122\x65\161\x75\x65\163\x74\75" . $IA . "\46\x52\145\x6c\141\171\x53\x74\141\x74\x65\x3d" . urlencode($Vo) . "\46\123\151\147\x41\x6c\x67\75" . urlencode(XMLSecurityKeySAML::RSA_SHA512);
        Cy:
        $OC = array("\164\171\160\x65" => "\x70\162\x69\x76\141\x74\x65");
        if ($Wv == "\x52\x53\101\137\x53\110\101\62\x35\x36") {
            goto r7;
        }
        if ($Wv == "\x52\x53\x41\137\123\110\101\x33\70\x34") {
            goto Xi;
        }
        if ($Wv == "\122\123\x41\137\123\110\x41\x35\x31\x32") {
            goto A9;
        }
        $KY = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA1, $OC);
        goto cM;
        r7:
        $KY = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA256, $OC);
        goto cM;
        Xi:
        $KY = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA384, $OC);
        goto cM;
        A9:
        $KY = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA512, $OC);
        cM:
        $vJ = UtilitiesSAML::get_public_private_certificate($Ru, "\x70\162\x69\166\x61\x74\x65\x5f\x63\145\162\164\151\146\x69\143\x61\x74\x65");
        if ($vJ == null || $vJ == '' || empty($vJ)) {
            goto Af;
        }
        $e2 = JPATH_BASE . DIRECTORY_SEPARATOR . "\x70\154\165\x67\x69\x6e\x73" . DIRECTORY_SEPARATOR . "\x61\165\164\x68\x65\156\x74\x69\x63\141\x74\x69\x6f\156" . DIRECTORY_SEPARATOR . "\x6d\151\156\x69\157\162\x61\x6e\x67\x65\x73\x61\155\154" . DIRECTORY_SEPARATOR . "\x73\141\x6d\154\x32" . DIRECTORY_SEPARATOR . "\143\x65\x72\x74" . DIRECTORY_SEPARATOR . "\x43\165\163\164\157\x6d\x50\162\151\166\141\x74\145\x43\145\162\x74\x69\x66\x69\143\x61\164\x65\x2e\153\x65\x79";
        goto lg;
        Af:
        $e2 = JPATH_BASE . DIRECTORY_SEPARATOR . "\160\x6c\165\147\151\x6e\163" . DIRECTORY_SEPARATOR . "\141\165\x74\x68\145\x6e\x74\151\x63\141\x74\x69\x6f\156" . DIRECTORY_SEPARATOR . "\x6d\151\x6e\x69\x6f\162\x61\x6e\147\145\163\141\155\x6c" . DIRECTORY_SEPARATOR . "\x73\141\x6d\154\x32" . DIRECTORY_SEPARATOR . "\143\x65\x72\x74" . DIRECTORY_SEPARATOR . "\x73\x70\55\x6b\145\x79\56\x6b\x65\171";
        lg:
        $KY->loadKey($e2, TRUE);
        $je = new XMLSecurityDSigSAML();
        $XE = $KY->signData($IA);
        $XE = base64_encode($XE);
        $zt .= $IA . "\46\123\151\x67\x6e\141\x74\x75\x72\145\75" . urlencode($XE);
        header("\x4c\157\x63\x61\164\151\x6f\156\72\x20" . $zt);
        exit;
        Y9:
    }
    function getRelayState($Mn, $Lb)
    {
        $nL = JFactory::getApplication()->input->get->getArray();
        $ns = '';
        if (!(isset($nL["\151\144\160"]) && !empty($nL["\151\144\x70"]))) {
            goto Yd;
        }
        $ns = $nL["\x69\144\x70"];
        Yd:
        $nv = UtilitiesSAML::getSAMLConfiguration($ns)[0];
        $base_url = $_SERVER["\x48\124\124\120\x5f\110\117\x53\x54"];
        $Vo = $Mn;
        if (isset($Lb["\x71"])) {
            goto vF;
        }
        if (isset($nv["\x64\145\146\141\165\x6c\x74\x5f\162\145\x6c\141\x79\x5f\163\164\141\x74\145"]) && $nv["\x64\145\146\141\165\x6c\x74\137\162\145\154\x61\171\x5f\163\x74\x61\164\x65"] != '') {
            goto gT;
        }
        $bH = isset($_SERVER["\x48\124\x54\x50\137\x52\105\x46\105\122\x45\122"]) ? $_SERVER["\110\124\124\120\137\122\105\106\x45\x52\x45\x52"] : '';
        if (isset($Lb["\x52\x65\154\141\171\x53\x74\141\164\x65"])) {
            goto rX;
        }
        if (isset($Lb["\155\157\137\163\x73\157\x5f\157\x72\x69\147\x69\x6e"]) && trim($Lb["\x6d\157\137\163\163\x6f\x5f\157\x72\x69\147\151\x6e"]) != '') {
            goto gQ;
        }
        if (isset($_COOKIE["\162\145\161\x75\x65\163\164\x5f\165\x72\x69\x5f\x72\x65\154\141\x79\x5f\x73\164\x61\x74\x65"])) {
            goto Qi;
        }
        if (!($bH != '')) {
            goto VU;
        }
        $Vo = $bH;
        VU:
        goto sb;
        Qi:
        $Vo = $_COOKIE["\162\145\161\165\145\163\164\x5f\x75\162\151\137\x72\145\154\141\x79\x5f\x73\x74\141\x74\145"];
        sb:
        goto jI;
        gQ:
        $q_ = !empty($_SERVER["\110\124\124\x50\x53"]) && $_SERVER["\110\x54\124\120\x53"] !== "\157\146\146" || $_SERVER["\x53\x45\x52\126\x45\x52\x5f\x50\x4f\122\124"] == 443 ? "\150\x74\x74\x70\163\72\57\57" : "\x68\164\164\160\x3a\57\57";
        $Vo = $q_ . $base_url . $Lb["\x6d\157\137\163\163\157\137\157\162\151\147\151\x6e"];
        jI:
        goto J_;
        rX:
        $Vo = $Lb["\x52\x65\x6c\x61\x79\123\x74\x61\x74\145"];
        J_:
        goto zu;
        gT:
        $Vo = $nv["\144\145\146\141\x75\x6c\x74\137\162\145\154\141\171\137\163\x74\x61\x74\x65"];
        zu:
        goto Rw;
        vF:
        if (!($Lb["\161"] == "\164\145\163\x74\x5f\143\x6f\x6e\x66\151\x67")) {
            goto sD;
        }
        $Vo = "\x74\145\x73\164\126\x61\x6c\151\144\141\x74\x65";
        sD:
        Rw:
        return $Vo;
    }
    function getSamlResponse()
    {
        $Vs = UtilitiesSAML::getCustomerincmk_lk("\151\x6e\x5f\143\155\x70");
        $Pf = UtilitiesSAML::getCustomerincmk_lk("\x73\x6d\x6c\x5f\154\x6b");
        foreach ($Pf as $KY) {
            $J_ = $KY;
            ms:
        }
        t8:
        $Hl = JURI::root() . $J_;
        $FR = UtilitiesSAML::getCustomerDetails();
        require_once JPATH_BASE . DIRECTORY_SEPARATOR . "\x61\144\x6d\x69\x6e\151\x73\x74\x72\x61\164\157\162" . DIRECTORY_SEPARATOR . "\x63\157\x6d\x70\x6f\156\x65\x6e\164\163" . DIRECTORY_SEPARATOR . "\143\x6f\155\137\x6d\151\156\151\157\162\141\x6e\147\x65\137\x73\141\x6d\x6c" . DIRECTORY_SEPARATOR . "\x68\x65\154\160\x65\x72\163" . DIRECTORY_SEPARATOR . "\155\157\55\x73\x61\x6d\x6c\x2d\x75\164\151\154\151\164\x79\x2e\160\x68\160";
        $Hl = Mo_saml_Local_Util::encrypt($Hl);
        $Sb = $FR["\164\162\x69\x73\164\x73"];
        foreach ($Vs as $KY) {
            if (!($Hl === $KY) && $KY != null && $KY != '') {
                goto NC;
            }
            if (!($KY == null || $KY == '')) {
                goto x_;
            }
            echo "\x3c\x64\x69\x76\x20\163\164\171\x6c\145\x3d\42\x66\157\x6e\x74\x2d\146\x61\155\x69\154\171\72\103\141\x6c\151\x62\x72\151\73\160\141\144\144\x69\x6e\x67\x3a\60\x20\63\x25\73\x22\x3e";
            echo "\74\x64\151\166\40\163\164\x79\154\x65\75\42\x63\157\154\x6f\x72\72\40\x23\x61\71\64\x34\x34\x32\x3b\142\141\x63\x6b\x67\162\157\165\x6e\144\x2d\143\157\154\x6f\162\x3a\x20\x23\x66\62\144\x65\144\145\x3b\160\x61\144\144\x69\x6e\x67\72\40\61\65\x70\170\x3b\155\141\162\147\151\156\x2d\142\x6f\x74\164\157\x6d\72\40\62\60\160\x78\x3b\x74\x65\170\164\55\x61\x6c\x69\x67\x6e\x3a\x63\x65\156\164\145\162\73\x62\157\x72\x64\x65\162\72\61\160\x78\x20\x73\157\x6c\x69\x64\x20\43\105\66\102\63\102\62\x3b\x66\x6f\x6e\164\x2d\x73\151\x7a\145\x3a\x31\70\160\x74\x3b\42\x3e\40\105\122\122\x4f\122\x3c\x2f\144\x69\x76\76\15\xa\x20\40\40\x20\x20\40\40\x20\x20\x20\x20\x20\x20\40\x20\x20\x20\x20\x20\x20\x20\40\40\x20\74\x64\x69\x76\x20\x73\164\x79\154\145\x3d\42\x63\x6f\154\157\x72\x3a\40\43\141\71\x34\x34\64\x32\73\x66\x6f\156\x74\55\x73\151\x7a\x65\x3a\x31\64\160\164\x3b\40\x6d\x61\x72\147\x69\156\55\142\157\x74\x74\157\x6d\x3a\62\x30\160\x78\73\x22\76\x3c\160\x3e\x3c\x73\164\x72\x6f\156\x67\76\x45\162\162\x6f\162\x3a\40\74\x2f\163\x74\162\157\156\147\x3e\131\157\165\x20\x61\162\145\40\156\157\x74\40\x6c\x6f\x67\147\145\x64\x20\151\156\x3c\x2f\x70\x3e\15\xa\40\40\x20\40\40\x20\40\x20\40\x20\x20\40\x20\40\x20\40\x20\40\x20\x20\x20\x20\x20\40\40\x20\x20\x20\x3c\160\x3e\120\x6c\145\141\x73\x65\x20\141\x63\164\151\166\x61\164\145\40\x79\x6f\x75\162\x20\x6c\151\x63\145\x6e\x73\145\x20\153\x65\x79\40\146\151\x72\x73\164\x20\x74\x6f\40\141\x63\164\x69\x76\141\164\x65\x20\x73\151\x6e\x67\x6c\145\x20\x73\x69\x67\x6e\x20\157\156\x2e\74\x2f\160\76\xd\xa\40\x20\x20\40\40\x20\40\40\x20\x20\40\40\x20\x20\x20\40\x20\x20\x20\x20\40\x20\x20\40\x20\x20\x20\40\74\x70\x3e\x3c\x73\164\x72\x6f\156\147\76\120\x6f\x73\x73\x69\142\154\x65\x20\x43\x61\x75\163\x65\72\40\x3c\x2f\x73\x74\162\x6f\156\x67\x3e\x4d\x61\153\x65\x20\163\x75\x72\145\x20\x79\157\x75\40\150\141\166\x65\40\x61\x63\164\x69\x76\141\x74\145\40\x79\157\x75\x72\40\x6c\151\143\145\156\163\x65\40\153\145\171\40\x69\x6e\x20\164\157\x20\x70\x6c\x75\147\x69\156\x3c\x2f\160\76\15\12\40\40\40\40\x20\40\40\x20\40\40\x20\x20\x20\x20\x20\40\x20\x20\x20\x20\40\40\40\x20\74\x2f\x64\x69\x76\76\xd\12\x20\40\x20\40\40\40\40\40\x20\40\x20\x20\40\40\40\40\x20\x20\x20\x20\74\144\x69\166\x20\163\x74\171\154\145\75\x22\x6d\x61\162\x67\x69\x6e\72\x33\45\73\x64\151\x73\x70\x6c\141\171\x3a\142\154\157\x63\x6b\x3b\x74\145\x78\164\x2d\141\154\x69\x67\156\x3a\143\x65\156\x74\x65\162\73\42\76";
            $Og = JURI::root();
            echo "\74\x64\x69\166\x20\163\x74\171\x6c\x65\75\x22\155\141\x72\x67\x69\156\x3a\63\x25\x3b\x64\151\163\160\154\141\x79\72\142\x6c\x6f\143\153\73\164\145\x78\164\55\x61\154\x69\x67\x6e\72\143\145\156\164\x65\x72\x3b\42\76\x3c\141\x20\x68\x72\x65\146\75\x22";
            echo $Og;
            echo "\x20\x22\x3e\74\x69\156\x70\x75\164\40\x73\x74\171\x6c\x65\75\x22\160\x61\x64\x64\x69\x6e\147\72\61\x25\73\x77\x69\x64\164\x68\x3a\x31\60\x30\x70\170\73\142\x61\x63\x6b\x67\x72\157\165\x6e\x64\72\x20\x23\x30\60\71\61\103\104\x20\156\157\x6e\145\x20\x72\145\x70\145\141\164\40\x73\143\x72\x6f\154\x6c\40\x30\45\x20\x30\45\73\x63\x75\x72\x73\157\x72\x3a\40\x70\157\x69\x6e\164\x65\162\x3b\146\x6f\156\164\x2d\163\151\x7a\145\72\61\x35\x70\170\73\142\x6f\x72\x64\145\x72\55\x77\x69\x64\164\150\x3a\x20\61\160\x78\73\142\157\x72\144\x65\x72\55\x73\x74\171\x6c\x65\72\40\163\157\154\151\144\73\x62\157\162\x64\145\162\55\x72\x61\144\151\165\163\x3a\40\63\160\170\x3b\x77\x68\151\x74\145\55\163\160\141\x63\x65\x3a\x20\x6e\157\167\x72\x61\x70\73\x62\157\x78\x2d\163\151\x7a\151\156\x67\x3a\x20\142\157\x72\x64\x65\162\x2d\142\x6f\x78\x3b\142\157\x72\x64\145\162\55\x63\157\x6c\157\x72\72\40\x23\60\60\67\x33\101\x41\x3b\x62\x6f\x78\x2d\x73\x68\x61\144\x6f\x77\72\40\60\160\x78\40\61\x70\x78\40\x30\x70\x78\x20\162\147\x62\141\x28\61\x32\60\54\x20\62\x30\x30\54\x20\62\63\x30\54\40\60\x2e\x36\x29\40\151\x6e\x73\x65\164\73\143\157\154\157\x72\x3a\x20\x23\106\x46\106\x3b\42\x74\171\160\145\x3d\x22\142\x75\x74\x74\157\156\42\40\166\x61\154\165\145\75\42\x44\157\156\145\x22\76\74\x2f\141\x3e\74\57\x64\151\x76\x3e";
            exit;
            x_:
            goto jM;
            NC:
            echo "\x3c\144\151\x76\x20\163\164\171\154\x65\75\42\146\157\156\x74\x2d\x66\x61\155\151\154\x79\x3a\x43\x61\154\x69\x62\162\x69\x3b\160\141\x64\x64\x69\156\x67\x3a\x30\x20\63\x25\x3b\x22\76";
            echo "\74\x64\x69\166\x20\163\x74\171\154\145\x3d\x22\143\x6f\154\x6f\162\x3a\40\x23\141\x39\x34\x34\64\62\x3b\142\141\x63\x6b\x67\x72\x6f\x75\x6e\144\55\x63\157\x6c\157\x72\72\40\43\x66\62\x64\x65\144\x65\x3b\160\x61\x64\x64\151\x6e\x67\72\40\61\65\x70\170\x3b\x6d\x61\162\147\x69\156\55\142\157\164\x74\x6f\155\x3a\40\x32\x30\160\x78\x3b\164\145\170\164\x2d\x61\x6c\151\x67\156\x3a\143\145\156\164\145\162\73\x62\x6f\x72\x64\145\x72\x3a\61\160\x78\x20\x73\x6f\x6c\151\144\x20\x23\x45\66\x42\x33\102\62\73\146\157\x6e\x74\x2d\163\x69\172\x65\x3a\61\x38\x70\x74\73\x22\76\x20\105\122\122\x4f\122\x3c\x2f\144\x69\166\x3e\xd\xa\x20\x20\x20\40\40\40\x20\40\40\40\40\40\40\x20\40\40\40\x20\x20\x20\x20\x20\40\40\x3c\x64\151\166\40\x73\x74\x79\x6c\145\75\42\x63\x6f\154\157\162\72\40\43\141\71\64\64\x34\x32\73\x66\157\156\x74\x2d\x73\x69\x7a\x65\72\x31\x34\x70\164\x3b\40\x6d\x61\162\x67\151\156\55\x62\157\x74\164\157\155\x3a\62\x30\160\170\73\42\76\x3c\x70\76\74\163\164\x72\157\156\x67\76\x45\x72\x72\x6f\162\72\40\74\57\x73\x74\x72\x6f\x6e\147\x3e\104\x75\160\154\151\x63\141\x74\145\40\x4c\x69\143\145\156\x63\x65\x20\x4b\145\171\x20\151\x73\40\x45\156\x63\157\x75\x6e\164\145\x72\x65\144\56\74\x2f\160\x3e\xd\xa\40\x20\40\40\x20\x20\40\x20\40\40\40\x20\x20\40\x20\40\x20\40\40\x20\40\x20\x20\x20\x20\40\x20\x20\74\x70\76\120\x6c\x65\141\163\145\x20\x63\x6f\156\164\x61\x63\164\x20\x79\157\165\162\40\141\144\155\x69\x6e\x69\x73\164\162\141\x74\x6f\x72\40\x61\x6e\x64\x20\x72\x65\160\157\x72\x74\x20\x74\150\x65\x20\146\157\154\154\157\167\x69\x6e\147\x20\145\162\162\157\162\x3a\74\x2f\160\x3e\15\xa\x20\x20\x20\x20\x20\x20\40\40\40\x20\x20\40\40\x20\40\40\x20\x20\40\40\x20\40\x20\40\x20\40\40\x20\74\x70\x3e\x3c\x73\164\x72\157\x6e\x67\76\x50\x6f\163\163\x69\x62\x6c\x65\x20\x43\141\165\163\x65\72\x20\74\57\163\164\162\x6f\156\x67\x3e\x20\x4c\x69\x63\145\156\x73\x65\x20\153\x65\171\x20\x66\157\162\x20\164\x68\151\x73\40\151\156\163\164\x61\x6e\x63\145\40\x69\x73\x20\x69\x6e\x63\157\162\162\x65\x63\164\56\40\x4d\x61\x6b\x65\40\163\x75\162\x65\x20\x79\157\x75\x20\x68\141\166\145\40\156\x6f\x74\40\x74\141\x6d\x70\x65\162\x65\x64\x20\167\x69\x74\150\40\x69\x74\x20\141\x74\40\141\x6c\154\56\40\x50\x6c\145\x61\x73\x65\40\x65\156\164\145\x72\x20\x61\x20\166\x61\x6c\x69\144\x20\x6c\151\143\x65\x6e\x73\x65\40\153\145\x79\56\74\x2f\160\x3e\15\xa\x20\x20\40\x20\x20\40\x20\x20\x20\40\40\40\x20\x20\40\x20\x20\x20\x20\40\40\x20\x20\x20\74\57\144\x69\x76\76\xd\xa\x20\40\x20\x20\40\x20\40\40\40\x20\40\40\40\x20\40\x20\x20\x20\x20\40\74\144\x69\x76\x20\x73\164\171\x6c\x65\75\42\x6d\x61\162\147\x69\156\x3a\x33\45\x3b\x64\151\x73\x70\x6c\x61\171\72\142\154\x6f\x63\153\73\x74\145\170\x74\55\x61\154\x69\x67\156\72\143\145\x6e\164\x65\162\73\x22\x3e";
            $Og = JURI::root();
            echo "\x3c\144\151\x76\x20\x73\x74\171\154\145\75\42\x6d\x61\162\x67\151\x6e\x3a\63\45\73\144\x69\x73\160\154\141\x79\x3a\x62\x6c\157\x63\x6b\73\x74\x65\x78\164\x2d\x61\154\151\x67\156\x3a\x63\145\x6e\164\x65\162\x3b\42\76\74\x61\x20\x68\x72\145\x66\x3d\42";
            echo $Og;
            echo "\x20\x22\76\x3c\x69\156\x70\165\164\40\x73\x74\171\x6c\x65\x3d\x22\160\x61\x64\x64\151\156\x67\72\x31\x25\73\167\x69\144\164\x68\72\61\x30\60\160\x78\x3b\142\141\x63\x6b\x67\x72\x6f\165\156\144\x3a\x20\x23\60\60\71\x31\x43\x44\x20\x6e\x6f\x6e\x65\40\162\145\x70\145\x61\164\40\163\143\x72\157\154\x6c\x20\x30\x25\x20\x30\45\73\x63\165\x72\x73\157\x72\72\40\x70\157\151\156\x74\x65\162\73\146\157\156\x74\x2d\163\x69\172\145\x3a\61\65\x70\170\x3b\142\157\x72\144\x65\162\x2d\x77\151\144\x74\150\72\40\61\x70\170\73\x62\157\162\144\145\162\55\x73\x74\171\x6c\x65\72\40\x73\x6f\154\151\144\73\x62\x6f\x72\144\145\x72\x2d\x72\141\144\x69\x75\163\72\x20\63\160\170\x3b\x77\150\x69\x74\145\x2d\163\160\141\143\x65\72\40\156\x6f\x77\162\141\x70\x3b\142\157\170\x2d\163\151\x7a\151\156\147\x3a\x20\142\x6f\162\x64\x65\x72\x2d\142\157\x78\x3b\x62\157\x72\x64\145\x72\55\143\157\x6c\x6f\x72\72\x20\43\60\60\x37\63\101\x41\x3b\142\157\x78\x2d\x73\150\141\144\x6f\x77\72\40\x30\160\170\x20\x31\160\170\x20\x30\x70\x78\x20\x72\x67\142\x61\x28\x31\x32\x30\x2c\x20\62\x30\60\x2c\40\x32\x33\x30\54\x20\x30\56\66\x29\x20\x69\x6e\163\x65\164\73\143\157\154\x6f\x72\x3a\x20\x23\x46\x46\x46\x3b\42\164\x79\160\145\75\x22\142\165\x74\x74\157\x6e\42\40\166\141\x6c\165\x65\x3d\42\x44\x6f\x6e\x65\x22\x3e\x3c\x2f\x61\x3e\74\x2f\x64\x69\166\76";
            exit;
            jM:
            sr:
        }
        C1:
        $F9 = UtilitiesSAML::checkIsLicenseExpired();
        if (!($F9["\x4c\151\143\x65\x6e\x73\x65\105\x78\x70\151\x72\145\x64"] || $F9["\114\x69\x63\x65\156\x73\x65\105\170\x70\151\162\x79"])) {
            goto VS;
        }
        UtilitiesSAML::_cuc();
        VS:
        if (defined("\x5f\x4a\104\105\x46\x49\x4e\x45\123")) {
            goto fW;
        }
        require_once JPATH_BASE . "\x2f\151\x6e\143\x6c\x75\144\145\163\x2f\x64\x65\x66\151\156\x65\163\x2e\160\150\160";
        fW:
        $Sb = Mo_saml_Local_Util::decrypt_value($Sb);
        require_once JPATH_BASE . "\x2f\x69\156\143\x6c\165\x64\x65\x73\57\x66\162\141\155\x65\x77\x6f\x72\153\x2e\160\150\x70";
        $EB = JPATH_BASE . DIRECTORY_SEPARATOR . "\160\x6c\x75\147\151\x6e\163" . DIRECTORY_SEPARATOR . "\141\165\x74\150\x65\156\x74\151\143\141\164\151\x6f\x6e" . DIRECTORY_SEPARATOR . "\x6d\x69\x6e\151\x6f\x72\x61\x6e\x67\145\163\x61\x6d\154";
        include_once $EB . DIRECTORY_SEPARATOR . "\x73\x61\x6d\154\62" . DIRECTORY_SEPARATOR . "\x52\x65\x73\160\157\156\x73\145\56\x70\150\160";
        jimport("\155\x69\x6e\151\x6f\x72\141\x6e\x67\145\x73\141\155\154\x70\x6c\165\147\151\x6e\56\165\164\x69\x6c\151\x74\171\x2e\x65\x6e\143\x72\171\x70\164\151\157\156");
        jimport("\x6a\x6f\x6f\x6d\154\x61\56\141\160\160\154\151\x63\x61\x74\151\157\x6e\56\x61\160\x70\x6c\x69\x63\141\x74\151\x6f\156");
        jimport("\152\157\157\x6d\x6c\x61\x2e\150\x74\155\x6c\56\x70\141\x72\x61\155\145\x74\x65\162");
        $nL = JFactory::getApplication()->input->get->getArray();
        $ns = '';
        if (!(isset($nL["\x69\x64\160"]) && !empty($nL["\151\144\160"]))) {
            goto z5;
        }
        $ns = $nL["\x69\144\160"];
        z5:
        if (!($Sb == true)) {
            goto Pb;
        }
        $F9 = UtilitiesSAML::gt_lk_trl();
        $Sw = 0;
        if (!$F9["\x4c\151\143\x65\x6e\163\x65\105\170\160\151\162\145\144"]) {
            goto F3;
        }
        UtilitiesSAML::rmvextnsns();
        $AA = UtilitiesSAML::get_message_and_cause($F9["\114\151\x63\x65\x6e\x73\145\x45\x78\160\x69\x72\145\144"], $Sw);
        UtilitiesSAML::show_error_messages($AA["\x6d\x73\147"], $AA["\x63\x61\165\x73\x65"]);
        F3:
        Pb:
        $nv = UtilitiesSAML::getSAMLConfiguration($ns)[0];
        $k4 = isset($nv["\144\145\x66\141\x75\154\x74\x5f\x72\x65\154\141\171\137\163\x74\141\164\145"]) ? $nv["\x64\x65\x66\x61\x75\x6c\164\x5f\162\x65\154\141\171\x5f\x73\x74\x61\x74\x65"] : '';
        $ht = JFactory::getApplication("\163\x69\164\145");
        $hQ = UtilitiesSAML::getJoomlaCmsVersion();
        $hQ = substr($hQ, 0, 3);
        if (!($hQ < 4.0)) {
            goto pv;
        }
        $ht->initialise();
        pv:
        $post = JFactory::getApplication()->input->post->getArray();
        if (!($Sb == true)) {
            goto NS;
        }
        $F9 = UtilitiesSAML::gt_lk_trl();
        $Sw = 0;
        if (!($F9["\x4e\157\x6f\146\125\x73\145\x72\163"] <= $FR["\x75\x73\162\x6c\x6d\x74"])) {
            goto nu;
        }
        UtilitiesSAML::rmvextnsns();
        $Sw = 1;
        $AA = UtilitiesSAML::get_message_and_cause($F9["\114\151\x63\x65\x6e\x73\145\105\170\160\x69\162\145\x64"], $Sw);
        UtilitiesSAML::show_error_messages($AA["\x6d\x73\x67"], $AA["\x63\x61\x75\x73\x65"]);
        nu:
        NS:
        if (array_key_exists("\123\x41\x4d\x4c\122\x65\163\160\x6f\x6e\x73\145", $post)) {
            goto I1;
        }
        throw new Exception("\x4d\x69\x73\163\151\x6e\147\40\123\101\115\114\x52\145\x71\x75\x65\163\x74\40\x6f\x72\40\123\101\x4d\114\x52\145\x73\x70\x6f\x6e\163\145\x20\160\141\162\141\155\145\x74\x65\x72\x2e");
        goto Ei;
        I1:
        $this->validateSamlResponse($post, $ht, $k4);
        Ei:
    }
    function validateSamlResponse($post, $ht, $k4)
    {
        $v2 = $post["\x53\x41\x4d\114\122\145\163\160\x6f\156\163\145"];
        $Lb = JFactory::getApplication()->input->request->getArray();
        $FR = UtilitiesSAML::getCustomerDetails();
        $j7 = '';
        if (empty($k4)) {
            goto DU;
        }
        $j7 = $k4;
        DU:
        if (!array_key_exists("\x52\145\x6c\x61\171\123\164\141\164\145", $Lb)) {
            goto qe;
        }
        $j7 = $Lb["\x52\145\154\141\x79\x53\x74\x61\164\x65"];
        qe:
        $v2 = base64_decode($v2);
        $o2 = new DOMDocument();
        $o2->loadXML($v2);
        $lG = $o2->firstChild;
        $v2 = new SAML2_Response($lG);
        $Sb = $FR["\164\162\x69\x73\x74\x73"];
        $Gk = current($v2->getAssertions())->getIssuer();
        $Ru = UtilitiesSAML::getSAMLConfiguration($Gk);
        $Ou = isset($Ru[0]) ? $Ru[0] : '';
        $Cr = Mo_saml_Local_Util::decrypt_value($Sb);
        if (!(!empty($Ou) || $Ou != '')) {
            goto V_;
        }
        UtilitiesSAML::auto_update_metadata($Ou);
        V_:
        if (!empty($Ru)) {
            goto nH;
        }
        echo "\40\40\40\x20\x20\40\40\x20\40\x20\x20\40\74\x64\151\166\x20\x73\x74\x79\154\145\x3d\42\x66\157\x6e\164\x2d\x66\141\155\151\x6c\171\72\x43\141\154\x69\142\x72\151\73\160\141\x64\x64\x69\x6e\147\x3a\x30\40\63\45\73\42\x3e\xd\xa\40\40\x20\x20\x20\40\40\40\40\x20\40\x20\x3c\x64\x69\166\x20\x73\x74\x79\154\145\75\42\143\157\154\x6f\162\x3a\x20\43\x61\71\x34\64\x34\x32\73\142\x61\x63\153\x67\x72\x6f\165\x6e\144\55\143\x6f\x6c\x6f\162\72\x20\x23\146\62\144\145\144\x65\x3b\x70\141\144\x64\151\156\x67\x3a\x20\61\65\x70\170\x3b\x6d\x61\x72\147\x69\156\x2d\x62\157\x74\164\157\155\72\x20\62\60\160\x78\73\x74\x65\170\164\x2d\x61\154\151\x67\156\72\143\x65\156\164\145\162\x3b\x62\x6f\162\144\145\162\x3a\x31\160\x78\x20\163\x6f\154\x69\144\x20\x23\x45\66\102\63\x42\x32\73\x66\x6f\156\x74\x2d\x73\151\172\145\72\x31\x38\160\x74\x3b\42\x3e\40\105\122\x52\117\122\x3c\57\144\x69\166\x3e\15\12\40\40\40\40\x20\40\x20\40\40\40\x20\40\x3c\144\x69\166\40\x73\164\x79\x6c\x65\75\x22\143\x6f\x6c\157\162\x3a\x20\x23\141\71\64\x34\x34\62\x3b\146\x6f\156\164\55\x73\x69\x7a\145\72\x31\x34\160\x74\x3b\x20\155\141\x72\x67\x69\156\x2d\142\x6f\x74\x74\x6f\155\x3a\x32\x30\160\x78\73\42\x3e\74\x70\x3e\x3c\x73\x74\162\157\x6e\x67\x3e\x45\x72\162\157\162\x3a\x20\x3c\x2f\x73\x74\162\x6f\156\147\x3e\x49\163\x73\x75\145\x72\x20\x63\141\x6e\156\157\164\40\x62\x65\40\166\x65\162\x69\146\x69\x65\x64\56\74\x2f\x70\76\15\xa\40\x20\x20\x20\x20\x20\x20\40\40\x20\x20\x20\40\40\x20\x20\x3c\160\x3e\120\154\x65\141\163\x65\x20\143\x6f\156\x74\141\x63\164\40\171\157\x75\x72\40\x61\x64\155\x69\156\x69\x73\164\x72\x61\164\x6f\162\x20\x61\x6e\x64\40\x72\x65\160\157\162\x74\x20\x74\150\145\40\x66\157\154\154\x6f\x77\151\x6e\x67\40\145\x72\x72\157\162\x3a\x3c\57\160\76\xd\xa\40\x20\x20\40\40\x20\40\x20\x20\40\x20\x20\x20\x20\x20\40\x3c\x70\x3e\x3c\x73\164\x72\157\x6e\x67\x3e\120\x6f\x73\163\151\142\154\x65\40\103\141\165\163\x65\72\40\x3c\x2f\x73\164\162\x6f\156\147\x3e\124\150\145\x20\x76\141\154\165\145\x20\157\x66\40\74\163\x74\x72\157\x6e\147\76\111\x64\x50\x20\105\x6e\164\x69\x74\x79\40\x49\x44\40\157\162\x20\x49\x73\x73\165\x65\x72\40\x6f\162\40\x41\x75\x64\x69\x65\156\143\145\x20\x55\122\111\x3c\x2f\163\164\162\157\156\147\x3e\x20\x69\x6e\40\x4a\x6f\157\x6d\154\141\x20\123\101\x4d\x4c\x20\x53\120\x20\160\x6c\165\147\x69\156\40\x61\x6e\x64\40\x74\150\145\40\143\x6f\156\146\x69\147\x75\162\x65\x64\x20\x5c\47\105\156\x74\151\x74\171\40\111\104\134\47\40\x69\156\40\171\x6f\165\x72\40\111\x44\120\x20\151\163\40\x69\156\143\x6f\162\x72\145\143\164\x2e\74\x2f\x70\x3e\15\xa\40\40\x20\40\x20\x20\x20\40\40\x20\40\40\x3c\x2f\x64\x69\166\x3e\15\12\x20\40\x20\40\x20\40\x20\x20\x20\x20\x20\x20\74\151\156\x70\x75\x74\40\x73\164\x79\x6c\145\75\42\160\141\144\144\x69\156\147\x3a\61\x25\x3b\x77\151\144\164\x68\x3a\x31\60\60\x70\x78\73\142\141\143\153\147\x72\x6f\165\x6e\x64\72\40\x23\x30\60\x39\61\x43\x44\40\x6e\157\156\x65\40\x72\x65\x70\145\141\164\40\163\x63\162\x6f\154\x6c\40\x30\45\40\x30\45\x3b\143\165\x72\163\x6f\162\72\40\160\157\151\x6e\164\145\162\73\146\157\156\164\55\163\151\x7a\145\72\x31\x35\160\170\x3b\142\157\x72\144\145\162\x2d\x77\151\x64\x74\150\72\40\x31\160\170\x3b\142\x6f\x72\x64\145\x72\x2d\163\164\x79\154\x65\72\40\x73\x6f\x6c\x69\x64\x3b\x62\x6f\162\x64\145\162\x2d\162\141\144\x69\x75\163\72\x20\63\x70\170\x3b\x77\150\151\164\x65\55\163\160\x61\143\x65\72\40\156\157\x77\x72\x61\160\x3b\142\157\x78\55\163\151\172\151\156\x67\72\40\142\x6f\x72\144\x65\162\55\142\x6f\170\73\142\157\162\x64\x65\x72\x2d\x63\157\x6c\157\x72\72\40\x23\x30\x30\x37\63\101\101\73\x62\157\170\55\163\x68\141\144\157\167\72\40\60\160\170\x20\x31\160\x78\x20\60\x70\x78\40\x72\147\x62\141\50\61\62\x30\x2c\40\x32\60\x30\54\40\62\63\60\54\x20\60\x2e\x36\x29\40\x69\156\x73\x65\x74\x3b\143\x6f\x6c\x6f\x72\x3a\x20\x23\106\106\106\x3b\x64\151\x73\160\x6c\141\x79\72\142\x6c\x6f\143\153\73\x6d\x61\x72\147\x69\x6e\55\x6c\x65\146\x74\x3a\x61\x75\164\x6f\73\x6d\141\162\x67\151\156\x2d\x72\151\x67\x68\x74\x3a\141\165\164\157\42\x20\164\x79\160\145\x3d\x22\x62\165\164\x74\157\x6e\42\x20\x76\x61\154\x75\145\75\x22\104\157\x6e\145\42\40\x6f\156\x43\154\151\x63\153\x3d\42\163\145\x6c\x66\x2e\143\154\157\163\145\x28\x29\73\42\x3e\15\12\x20\40\x20\x20\40\40\x20\x20\40\40\x20\40";
        exit;
        goto gG;
        nH:
        if (!($Cr == true)) {
            goto Qm;
        }
        $F9 = UtilitiesSAML::gt_lk_trl();
        $Sw = 0;
        if (!($F9["\116\157\x6f\146\125\163\145\162\x73"] <= $FR["\165\x73\162\x6c\155\x74"])) {
            goto bp;
        }
        UtilitiesSAML::rmvextnsns();
        $Sw = 1;
        $AA = UtilitiesSAML::get_message_and_cause($F9["\114\151\x63\145\x6e\163\145\105\x78\x70\x69\162\145\144"], $Sw);
        UtilitiesSAML::show_error_messages($AA["\x6d\x73\x67"], $AA["\x63\x61\165\x73\145"]);
        bp:
        Qm:
        $Ru = $Ru[0];
        $f1 = UtilitiesSAML::getCustomerDetails();
        $Mn = '';
        $Ox = '';
        if (!isset($f1["\x73\x70\137\142\141\163\x65\x5f\165\x72\x6c"])) {
            goto RA;
        }
        $Mn = $f1["\163\x70\137\x62\141\x73\x65\137\x75\162\x6c"];
        $Ox = $f1["\163\x70\x5f\145\156\164\151\x74\171\x5f\151\144"];
        RA:
        $ei = JURI::root();
        if (!($Cr == true)) {
            goto zC;
        }
        $F9 = UtilitiesSAML::gt_lk_trl();
        $Sw = 0;
        if (!$F9["\114\x69\143\145\156\x73\x65\x45\x78\x70\151\x72\x65\144"]) {
            goto Re;
        }
        UtilitiesSAML::rmvextnsns();
        $AA = UtilitiesSAML::get_message_and_cause($F9["\x4c\151\143\x65\x6e\163\x65\105\170\160\x69\x72\145\x64"], $Sw);
        UtilitiesSAML::show_error_messages($AA["\x6d\163\x67"], $AA["\x63\x61\165\x73\145"]);
        Re:
        zC:
        if (!empty($Mn)) {
            goto cv;
        }
        $Mn = $ei;
        cv:
        if (!empty($Ox)) {
            goto zE;
        }
        $Ox = $ei . "\160\154\x75\x67\x69\156\163\57\x61\165\x74\x68\145\156\164\151\x63\141\164\151\157\x6e\57\155\151\x6e\151\157\162\141\x6e\147\x65\x73\141\155\x6c";
        zE:
        $ks = $Mn . "\77\x6d\157\x72\145\161\x75\x65\163\164\x3d\141\143\x73";
        $Pk = $Ru["\143\145\162\x74\x69\146\x69\x63\141\164\145"];
        $vs = explode("\73", $Pk);
        $ZP = array();
        $UG = 0;
        foreach ($vs as $Iv) {
            $Iv = UtilitiesSAML::sanitize_certificate($Iv);
            $Iv = XMLSecurityKeySAML::getRawThumbprint($Iv);
            $Iv = preg_replace("\57\134\x73\x2b\x2f", '', $Iv);
            $ZP[$UG] = iconv("\x55\124\x46\55\70", "\x43\x50\x31\x32\x35\x32\x2f\x2f\x49\107\116\x4f\122\105", $Iv);
            $UG++;
            FS:
        }
        FZ:
        $wj = $v2->getSignatureData();
        $r9 = current($v2->getAssertions())->getSignatureData();
        if (empty($wj)) {
            goto Ux;
        }
        $Z2 = UtilitiesSAML::processResponse($ks, $ZP, $wj, $v2, $Pk, $j7);
        if (!($Z2 === FALSE)) {
            goto Q5;
        }
        UtilitiesSAML::showErrorMessage("\111\x6e\166\x61\154\x69\x64\40\x73\151\x67\x6e\141\164\165\162\145\40\x69\x6e\40\x74\x68\x65\x20\123\101\115\x4c\x20\x52\x65\x73\x70\x6f\156\163\145\x2e\40\105\x69\x74\150\x65\162\40\123\x41\115\x4c\40\122\145\163\160\x6f\156\163\x65\x20\x69\163\x20\x6e\x6f\164\40\x73\151\x67\x6e\145\x64\x20\x6f\162\x20\163\x69\x67\156\x65\x64\x20\167\151\x74\x68\x20\167\x72\157\156\x67\x20\153\x65\x79\x20\142\x79\40\x49\104\120\56", "\112\x4f\x31\60\64");
        exit;
        Q5:
        Ux:
        if (empty($r9)) {
            goto RZ;
        }
        $Z2 = UtilitiesSAML::processResponse($ks, $ZP, $r9, $v2, $Pk, $j7);
        if (!($Z2 === FALSE)) {
            goto cb;
        }
        UtilitiesSAML::showErrorMessage("\x49\156\x76\x61\x6c\151\144\x20\x73\x69\x67\156\141\164\x75\x72\x65\x20\151\156\x20\x74\x68\145\x20\x53\x41\x4d\x4c\x20\101\x73\x73\145\x72\164\x69\x6f\156\x2e\x20\105\x69\x74\x68\x65\x72\x20\123\101\115\114\x20\101\x73\x73\x65\162\164\151\x6f\156\x20\151\x73\x20\x6e\157\164\x20\x73\x69\147\156\x65\x64\40\157\x72\40\163\151\x67\x6e\145\144\40\x77\151\164\150\40\x77\162\x6f\156\x67\40\x6b\x65\x79\x20\142\x79\x20\x49\x44\x50\56", "\x4a\117\61\x30\x33");
        exit;
        cb:
        RZ:
        if (!(empty($r9) && empty($wj))) {
            goto Dh;
        }
        UtilitiesSAML::showErrorMessage("\x4e\x6f\40\x73\151\x67\156\141\x74\x75\162\145\40\x69\156\x20\x53\101\x4d\114\40\x52\x65\x73\160\157\x6e\x73\x65\40\157\162\x20\101\163\163\145\x72\164\x69\157\156\x2e", "\x4a\117\61\x30\x32");
        exit;
        Dh:
        $r6 = $Ru["\x69\x64\160\137\145\156\x74\151\x74\171\137\x69\x64"];
        UtilitiesSAML::validateIssuerAndAudience($v2, $Ox, $r6);
        $L3 = current(current($v2->getAssertions())->getNameId());
        $x2 = current($v2->getAssertions())->getAttributes();
        $x2["\x41\x53\123\x45\122\124\x49\x4f\x4e\137\x4e\101\115\105\x5f\x49\x44"] = current(current($v2->getAssertions())->getNameId());
        if (!($j7 == "\x74\145\163\164\126\141\154\x69\144\141\164\145")) {
            goto Qq;
        }
        UtilitiesSAML::mo_saml_show_test_result($L3, $x2, $Mn);
        Qq:
        $XP = current($v2->getAssertions())->getSessionIndex();
        $x2["\101\x53\x53\105\122\124\x49\117\116\137\123\105\123\x53\111\x4f\x4e\137\111\116\x44\x45\130"] = $XP;
        $dZ = $L3;
        $hO = '';
        $S0 = isset($Ru["\x66\151\x72\163\164\x5f\156\x61\x6d\x65"]) ? $Ru["\146\x69\162\x73\x74\137\156\x61\x6d\145"] : '';
        $Zv = isset($Ru["\154\141\163\x74\x5f\x6e\x61\155\x65"]) ? $Ru["\154\141\163\x74\x5f\x6e\x61\x6d\145"] : '';
        $r1 = isset($Ru["\x6e\141\x6d\x65"]) ? trim($Ru["\156\141\x6d\145"]) : '';
        $tq = isset($Ru["\165\x73\145\x72\156\141\x6d\x65"]) ? $Ru["\165\163\x65\162\x6e\141\x6d\145"] : '';
        $qq = isset($Ru["\145\x6d\x61\151\154"]) ? $Ru["\x65\x6d\141\x69\x6c"] : '';
        $Xw = UtilitiesSAML::getRoleMapping($Ru);
        $AW = isset($Ru["\x64\151\x73\141\142\x6c\145\x5f\x75\160\144\141\164\145\137\145\x78\x69\x73\x74\x69\x6e\x67\137\143\x75\163\164\x6f\155\145\x72\x5f\141\x74\164\x72\x69\142\x75\x74\145\163"]) ? $Ru["\x64\x69\163\141\142\x6c\145\x5f\x75\160\x64\x61\x74\x65\137\145\170\x69\x73\x74\x69\156\x67\x5f\143\x75\x73\x74\x6f\155\145\x72\137\141\164\x74\162\x69\x62\x75\164\145\x73"] : 0;
        $hI = isset($Xw["\x67\x72\x70"]) ? $Xw["\x67\162\x70"] : '';
        if (!(!empty($tq) && isset($x2[$tq]) && !empty($x2[$tq]))) {
            goto ui;
        }
        $L3 = $x2[$tq];
        if (!is_array($L3)) {
            goto cu;
        }
        $L3 = $L3[0];
        cu:
        ui:
        if (!(!empty($qq) && isset($x2[$qq]) && !empty($x2[$qq]))) {
            goto AJ;
        }
        $dZ = $x2[$qq];
        if (!is_array($dZ)) {
            goto Mg;
        }
        $dZ = $dZ[0];
        Mg:
        AJ:
        if (!(!empty($S0) && isset($x2[$S0][0]) && !empty($x2[$S0][0]))) {
            goto Uh;
        }
        $eG = $x2[$S0];
        if (!is_array($eG)) {
            goto vl;
        }
        $eG = $eG[0];
        vl:
        Uh:
        if (!(!empty($Zv) && isset($x2[$Zv][0]) && !empty($x2[$Zv][0]))) {
            goto YK;
        }
        $Dk = $x2[$Zv];
        if (!is_array($Dk)) {
            goto EE;
        }
        $Dk = $Dk[0];
        EE:
        YK:
        if (!(isset($eG) && !empty($eG))) {
            goto SN;
        }
        $hO = $eG . "\x20";
        SN:
        if (!(isset($Dk) && !empty($Dk))) {
            goto VT;
        }
        $hO = $hO . $Dk;
        VT:
        if (!(!empty($r1) && isset($x2[$r1][0]) && !empty($x2[$r1][0]))) {
            goto cR;
        }
        $hO = $x2[$r1];
        if (!is_array($hO)) {
            goto ut;
        }
        $hO = $hO[0];
        ut:
        cR:
        if (!empty($hI) && isset($x2[$hI]) && !empty($x2[$hI])) {
            goto Uj;
        }
        $Ax = array();
        goto xd;
        Uj:
        $Ax = $x2[$hI];
        xd:
        if (isset($FR["\145\x6e\141\142\154\x65\x5f\x65\x6d\141\x69\154"]) && $FR["\145\156\x61\142\x6c\145\137\x65\x6d\141\x69\x6c"] == 0) {
            goto b9;
        }
        $J2 = "\x65\x6d\x61\151\x6c";
        goto Z9;
        b9:
        $J2 = "\165\x73\145\162\156\141\x6d\145";
        Z9:
        $lc = UtilitiesSAML::get_user_from_joomla($J2, $L3, $dZ);
        $q6 = isset($j7) ? $j7 : $Mn;
        $hO = isset($hO) && !empty($hO) ? $hO : $L3;
        if ($Cr && (isset($F9["\114\x69\143\145\x6e\x73\145\x45\170\x70\151\x72\145\x64"]) && $F9["\x4c\x69\143\145\x6e\x73\x65\x45\170\x70\x69\x72\145\144"] == "\x54\x72\x75\145") || isset($Sw) && $Sw == 1) {
            goto E9;
        }
        if ($lc) {
            goto bb;
        }
        $this->RegisterCurrentUser($x2, $q6, $hO, $L3, $dZ, $J2, $ht, $Ax, $Ru);
        goto XW;
        bb:
        $this->loginCurrentUser($lc, $x2, $q6, $hO, $L3, $ht, $Ax, $Ru, $AW);
        XW:
        goto ul;
        E9:
        $AA = UtilitiesSAML::get_message_and_cause($F9["\114\151\x63\145\156\163\145\x45\170\160\151\x72\145\144"], $Sw);
        UtilitiesSAML::show_error_messages($AA["\x6d\x73\147"], $AA["\x63\x61\x75\x73\145"]);
        ul:
        gG:
    }
    function loginCurrentUser($lc, $x2, $q6, $hO, $L3, $ht, $Ax, $nv, $AW)
    {
        $user = JUser::getInstance($lc->id);
        $Xw = UtilitiesSAML::getRoleMapping($nv);
        $QY = UtilitiesSAML::getCustomerDetails();
        $OE = isset($QY["\x69\147\156\x6f\162\145\137\x73\x70\x65\x63\151\x61\x6c\137\143\x68\141\162\x61\x63\x74\145\x72\163"]) ? $QY["\x69\x67\156\x6f\162\145\x5f\163\160\145\143\151\x61\x6c\137\143\x68\141\162\141\x63\164\x65\x72\x73"] : 0;
        $pW = isset($QY["\145\x6e\141\142\154\145\x5f\155\141\x6e\141\x67\145\162\137\154\157\147\x69\x6e"]) ? $QY["\145\156\141\142\x6c\145\137\x6d\x61\156\x61\147\145\162\x5f\x6c\x6f\147\x69\156"] : 0;
        $Mh = isset($QY["\x65\x6e\141\x62\x6c\x65\137\x61\x64\155\x69\x6e\x5f\x63\x68\151\x6c\144\137\x6c\157\147\x69\156"]) ? $QY["\x65\x6e\141\142\x6c\x65\x5f\x61\144\x6d\x69\156\x5f\143\150\151\x6c\x64\x5f\154\x6f\x67\151\156"] : 0;
        $l6 = isset($QY["\145\156\141\142\x6c\145\x5f\155\141\156\141\147\145\x72\137\143\150\151\154\x64\137\x6c\157\147\x69\x6e"]) ? $QY["\x65\x6e\141\x62\154\x65\137\155\x61\x6e\141\x67\x65\162\137\143\x68\151\x6c\x64\137\x6c\x6f\147\x69\156"] : 0;
        $Sb = $QY["\164\162\x69\163\x74\163"];
        if (!(!$OE && preg_match("\133\x27\x5d", $user->email))) {
            goto KS;
        }
        $ht = JFactory::getApplication();
        $ht->enqueueMessage("\131\157\x75\40\x61\162\145\40\156\157\x74\40\141\x6c\x6c\x6f\x77\x65\x64\x20\164\157\x20\154\157\x67\x69\156\40\x69\156\x74\x6f\x20\164\150\x65\x20\x73\151\164\145\x20\167\151\164\150\40\163\x70\x65\143\151\141\154\x20\143\x68\x61\162\x61\143\164\x65\162\x20\151\x6e\x20\145\155\x61\x69\x6c\x20\x61\144\x64\162\x65\163\163\x2e\x20\120\x6c\145\141\163\145\40\143\157\156\164\x61\x63\164\x20\171\x6f\x75\162\40\101\144\155\151\156\x69\163\x74\162\x61\x74\157\x72\x2e", "\145\x72\162\157\162");
        $ht->redirect(JURI::root());
        KS:
        $HV = isset($Xw["\x75\x70\144\141\x74\145\137\x65\170\151\163\164\x69\156\x67\x5f\x75\x73\145\x72\x73\x5f\162\157\x6c\145\x5f\x77\151\164\x68\157\165\164\x5f\162\x65\155\157\166\x69\x6e\x67\137\143\165\x72\x72\x65\156\164"]) ? $Xw["\165\x70\144\x61\164\145\x5f\x65\x78\x69\163\x74\x69\x6e\147\x5f\165\163\145\162\163\137\162\157\154\145\x5f\167\x69\x74\150\157\x75\164\x5f\x72\x65\x6d\157\166\x69\x6e\147\x5f\143\165\x72\162\x65\x6e\x74"] : 0;
        $nV = isset($Xw["\x64\151\x73\x61\142\154\x65\137\145\x78\x69\163\164\151\156\x67\x5f\x75\163\145\x72\163\x5f\162\157\154\x65\137\x75\x70\x64\141\164\x65"]) ? $Xw["\x64\x69\x73\141\x62\154\145\x5f\145\170\x69\x73\x74\x69\x6e\147\137\x75\163\x65\x72\x73\137\x72\157\x6c\145\137\165\x70\x64\141\x74\x65"] : 0;
        if ($nV) {
            goto eG;
        }
        $dh = 2;
        if (!isset($Xw["\155\141\160\160\151\156\147\x5f\166\x61\x6c\165\145\137\x64\x65\x66\141\165\154\164"])) {
            goto aX;
        }
        $dh = $Xw["\155\141\x70\x70\x69\x6e\147\x5f\x76\141\154\x75\145\x5f\x64\x65\x66\x61\x75\x6c\164"];
        aX:
        $Zs = array();
        if (!isset($Xw["\162\157\x6c\x65\x5f\x6d\x61\x70\160\x69\156\x67\x5f\x6b\x65\171\x5f\166\x61\x6c\165\x65"])) {
            goto Cw;
        }
        $Zs = json_decode($Xw["\x72\x6f\x6c\145\x5f\x6d\141\x70\160\151\x6e\x67\x5f\153\x65\x79\x5f\x76\141\154\x75\x65"]);
        Cw:
        $vp = 0;
        if (!isset($Xw["\x65\x6e\x61\x62\x6c\x65\137\163\x61\155\x6c\x5f\162\157\154\x65\137\x6d\x61\160\160\x69\x6e\x67"])) {
            goto V7;
        }
        $vp = json_decode($Xw["\x65\156\141\x62\154\x65\x5f\163\141\x6d\154\x5f\162\x6f\x6c\145\137\155\141\160\x70\x69\x6e\x67"]);
        V7:
        jimport("\152\x6f\157\155\154\141\56\165\163\x65\162\56\150\x65\154\160\x65\x72");
        if (!($vp == 1)) {
            goto Vr;
        }
        $kd = UtilitiesSAML::get_mapped_groups($Zs, $Ax);
        $this->addOrRemoveUserFromGroup($kd, $dh, $user, $HV);
        Vr:
        eG:
        $Tv = Mo_saml_Local_Util::decrypt_value($Sb);
        if ($AW) {
            goto eT;
        }
        UtilitiesSAML::updateCurrentUserName($user->id, $hO, $L3);
        $this->updateUserProfileAttributes($user->id, $x2, isset($nv["\x75\163\145\162\137\x70\x72\x6f\x66\x69\154\x65\137\x61\x74\x74\162\x69\x62\165\x74\145\163"]) ? $nv["\165\x73\145\x72\137\x70\x72\x6f\146\x69\x6c\145\x5f\141\164\164\x72\x69\x62\x75\x74\145\x73"] : '');
        $q8 = UtilitiesSAML::getPluginConfigurations($nv["\151\144"]);
        $q8 = current($q8);
        $ed = isset($q8["\165\163\145\162\x5f\x66\151\145\154\144\x5f\141\164\164\162\151\x62\165\x74\145\x73"]) ? $q8["\x75\163\145\x72\137\x66\x69\x65\154\x64\137\x61\x74\164\162\151\x62\x75\164\145\163"] : '';
        $this->updateUserFieldAttributes($user->id, $x2, $ed);
        $GG = isset($q8["\165\163\x65\x72\x5f\x63\157\156\x74\x61\x63\x74\x5f\141\164\x74\x72\x69\142\x75\164\145\163"]) ? $q8["\165\x73\145\x72\x5f\x63\157\156\x74\x61\143\164\137\x61\164\x74\x72\x69\x62\x75\164\x65\x73"] : '';
        $this->updateUserContactAttributes($user->id, $x2, $GG);
        $ep = UtilitiesSAML::check_table_present("\143\x77\165\163\x65\162\x73");
        if (!$ep) {
            goto ew;
        }
        $KP = isset($q8["\165\x73\145\162\x5f\x63\167\x5f\141\164\x74\x72\151\x62\165\164\145\x73"]) ? $q8["\165\163\x65\162\137\x63\x77\x5f\141\x74\164\162\x69\x62\x75\164\x65\x73"] : '';
        $Ag = $this->updateUserCWAttributes($user->id, $x2, $KP);
        if (!$Ag) {
            goto I6;
        }
        $ht->checkSession();
        $ht->redirect(urldecode($q6));
        I6:
        ew:
        eT:
        if (!($Tv == true)) {
            goto in;
        }
        $F9 = UtilitiesSAML::gt_lk_trl();
        $Sw = 0;
        if (!$F9["\x4c\151\143\x65\156\163\145\x45\x78\x70\151\162\145\144"]) {
            goto Z1;
        }
        UtilitiesSAML::rmvextnsns();
        $AA = UtilitiesSAML::get_message_and_cause($F9["\114\x69\x63\x65\x6e\163\x65\105\170\x70\x69\162\x65\x64"], $Sw);
        UtilitiesSAML::show_error_messages($AA["\155\163\147"], $AA["\143\x61\x75\163\145"]);
        Z1:
        in:
        $Va = JPATH_BASE . DIRECTORY_SEPARATOR . "\x6c\x69\142\162\141\162\151\145\163" . DIRECTORY_SEPARATOR . "\155\x69\156\x69\x6f\162\x61\156\x67\x65\147\165\x72\x75\154\151\142" . DIRECTORY_SEPARATOR . "\x75\164\151\x6c\151\x74\x79" . DIRECTORY_SEPARATOR . "\x55\164\151\x6c\151\164\151\x65\163\107\x75\162\x75\56\160\x68\160";
        if (!file_exists($Va)) {
            goto Ln;
        }
        require_once $Va;
        $pf = UtilitiesGuru::loadGuruCourseName($kd);
        if (empty($pf)) {
            goto OB;
        }
        foreach ($pf as $oU) {
            UtilitiesGuru::addUserToCourse($oU, $user);
            TC:
        }
        c8:
        OB:
        Ln:
        $BE = JPATH_BASE . DIRECTORY_SEPARATOR . "\154\151\142\162\141\x72\x69\145\163" . DIRECTORY_SEPARATOR . "\x6d\151\x6e\151\x6f\x72\141\x6e\147\145\x63\157\155\155\165\x6e\151\x74\171\x62\165\x69\154\x64\145\x72" . DIRECTORY_SEPARATOR . "\x75\x74\x69\x6c\x69\x74\x79" . DIRECTORY_SEPARATOR . "\103\142\x55\164\151\154\x69\164\151\x65\163\56\160\150\x70";
        if (!file_exists($BE)) {
            goto Qw;
        }
        jimport("\x6d\151\156\x69\x6f\x72\x61\156\x67\x65\143\157\155\155\x75\156\x69\164\171\142\x75\x69\x6c\x64\145\x72\x2e\x75\164\x69\154\151\x74\x79\x2e\x43\x62\x55\x74\151\x6c\x69\164\151\x65\163");
        $vb = CbUtilities::checkAndUpdateCBAttributes($user->id, $x2);
        if (!($vb == "\x46\x41\x49\x4c\x45\x44\137\x43\102")) {
            goto k_;
        }
        $hE = "\x49\x74\40\x61\160\160\x65\141\162\163\x20\164\150\141\x74\x20\164\x68\145\x20\143\157\155\160\x72\157\x66\x69\154\145\x72\40\164\141\x62\154\145\40\151\163\40\x6d\x69\x73\x73\x69\156\x67\x20\x66\162\157\x6d\x20\171\x6f\165\162\x20\144\x61\164\x61\x62\x61\x73\145\x2e\x20\x54\x68\145\40\x61\x74\x74\x72\151\x62\x75\x74\145\163\x20\143\157\x75\x6c\144\40\x6e\157\x74\40\x62\x65\40\155\141\160\x70\x65\144\56";
        $ht = JFactory::getApplication("\163\x69\164\145");
        $ht->enqueueMessage($hE, "\167\x61\x72\156\x69\156\147");
        k_:
        Qw:
        $KK = JFactory::getSession();
        if (strpos($q6, "\141\144\x6d\x69\156\x69\163\x74\162\141\x74\x6f\162")) {
            goto T4;
        }
        $KK->set("\x75\x73\x65\162", $user);
        $KK->set("\115\x4f\x5f\123\x41\x4d\x4c\x5f\116\x41\x4d\105\x49\x44", $x2["\x41\123\123\105\122\124\x49\117\x4e\137\116\x41\115\x45\137\111\x44"]);
        $KK->set("\x4d\117\137\x53\x41\x4d\x4c\x5f\x53\105\x53\123\111\117\116\x5f\111\x4e\104\x45\x58", $x2["\101\x53\123\x45\122\124\111\x4f\x4e\x5f\123\x45\123\x53\x49\x4f\116\x5f\x49\116\104\105\x58"]);
        $KK->set("\115\117\x5f\123\101\x4d\x4c\137\114\x4f\107\107\105\104\x5f\111\x4e\137\x57\x49\x54\x48\x5f\x49\104\120", TRUE);
        $KK->set("\x4d\117\137\123\x41\x4d\x4c\x5f\111\104\120\137\125\x53\x45\x44", $nv["\151\144\x70\x5f\145\x6e\x74\x69\164\x79\137\x69\x64"]);
        T4:
        if (!($Tv == true)) {
            goto sY;
        }
        $F9 = UtilitiesSAML::gt_lk_trl();
        $Sw = 0;
        if (!($F9["\116\157\x6f\x66\x55\163\145\162\163"] <= $QY["\x75\163\162\x6c\x6d\164"])) {
            goto W2;
        }
        UtilitiesSAML::rmvextnsns();
        $Sw = 1;
        $AA = UtilitiesSAML::get_message_and_cause($F9["\x4c\151\x63\145\x6e\163\x65\x45\170\160\x69\162\145\x64"], $Sw);
        UtilitiesSAML::show_error_messages($AA["\155\163\x67"], $AA["\143\141\x75\163\x65"]);
        W2:
        sY:
        $ht->checkSession();
        $yH = $KK->getId();
        UtilitiesSAML::updateUsernameToSessionId($user->username, $yH);
        $YE = $this->isRoleBasedRedirectPluginEnabled();
        if (!$YE) {
            goto vn;
        }
        $xY = UtilitiesSAML::getRoleBasedRedirectionConfiguration();
        $Fx = isset($xY["\145\x6e\141\x62\154\145\x5f\162\x6f\x6c\145\137\142\141\x73\x65\x64\137\x72\x65\144\x69\x72\145\x63\x74\151\157\x6e"]) ? $xY["\145\x6e\x61\x62\x6c\145\137\162\x6f\154\145\x5f\x62\x61\163\x65\x64\x5f\x72\x65\x64\151\162\x65\143\x74\151\x6f\156"] : 0;
        if (!$Fx) {
            goto ti;
        }
        $mc = UtilitiesSAML::getUserGroupID($user->groups);
        $Bn = isset($xY["\x72\x6f\x6c\x65\x5f\142\x61\x73\x65\144\137\x72\x65\x64\151\x72\145\143\164\x5f\153\145\171\x5f\166\141\154\165\145"]) ? $xY["\162\157\154\x65\x5f\142\x61\x73\145\144\137\162\x65\144\151\x72\x65\x63\x74\137\x6b\x65\171\x5f\166\141\154\165\x65"] : '';
        $Bn = json_decode($Bn);
        $w6 = UtilitiesSAML::get_role_based_redirect_values($Bn, $mc);
        if (empty($w6)) {
            goto oe;
        }
        $q6 = $w6;
        oe:
        ti:
        vn:
        $ir = UtilitiesSAML::IsUserSuperUser($user);
        $jt = UtilitiesSAML::IsUserSuperUserChild($user);
        $Zm = UtilitiesSAML::IsUserManager($user);
        $uU = UtilitiesSAML::IsUserManagerChild($user);
        $q6 = Mo_saml_Local_Util::check_special_character_in_url($q6);
        $SR = isset($_COOKIE["\151\x6e\x69\x74\x5f\x72\145\161\x75\145\163\164\137\x75\x72\x69\137\x72\x65\x6c\141\171\137\163\164\141\x74\x65"]) ? $_COOKIE["\x69\156\x69\x74\x5f\162\145\x71\x75\145\x73\x74\x5f\x75\x72\151\x5f\162\145\154\x61\x79\x5f\163\164\141\x74\x65"] : '';
        $n4 = UtilitiesSAML::isLoginReportAddonEnable();
        if (!$n4) {
            goto jQ;
        }
        $pB = "\x53\123\117\x20\x55\x73\145\x72";
        commonUtilities::createLogs($user->username, $pB);
        jQ:
        if ($ir || $Zm && $pW || $jt && $Mh || $uU && $l6) {
            goto nM;
        }
        if ($pW || $Mh || $l6) {
            goto i5;
        }
        $ht->redirect($q6);
        goto Sv;
        i5:
        if (strpos($q6, "\141\144\155\x69\x6e\151\x73\x74\x72\x61\x74\x6f\162") || strpos($SR, "\141\x64\x6d\151\156\151\163\x74\x72\141\164\157\162")) {
            goto KQ;
        }
        $ht->redirect($q6);
        goto se;
        KQ:
        if (!(!$ir || !$Zm || !$jt || !$uU)) {
            goto AO;
        }
        UtilitiesSAML::show_error_messages("\131\157\165\40\x61\x72\145\x20\x6e\x6f\x74\x20\141\154\154\x6f\x77\145\x64\40\x74\x6f\x20\x6c\x6f\147\x69\156\40\151\156\x74\x6f\40\142\141\143\x6b\x65\x6e\x64", "\x59\x6f\165\x20\x64\157\x6e\164\40\x68\x61\166\145\40\x61\143\143\145\x73\x73\40\x74\157\40\x62\x61\143\153\x65\x6e\x64");
        exit;
        AO:
        se:
        Sv:
        goto jt;
        nM:
        if (strpos($q6, "\x61\x64\155\151\x6e\x69\163\x74\x72\x61\164\x6f\x72") || strpos($SR, "\141\144\155\x69\156\151\163\x74\162\141\x74\157\x72")) {
            goto hx;
        }
        $ht->redirect($q6);
        goto HJ;
        hx:
        $KK->set("\x4d\x4f\137\x53\x41\x4d\x4c\x5f\x49\104\120\137\x55\123\105\104", $nv["\x69\x64\x70\x5f\x65\156\164\151\x74\x79\x5f\151\144"]);
        $KK->set("\115\117\137\x53\101\115\114\x5f\116\101\115\105\x49\x44", $x2["\x41\x53\x53\x45\122\124\x49\117\x4e\137\x4e\x41\x4d\x45\137\111\x44"]);
        $KK->set("\115\x4f\x5f\123\x41\115\114\137\123\x45\x53\123\111\117\116\x5f\111\116\104\x45\x58", $x2["\x41\x53\123\105\x52\124\x49\x4f\x4e\x5f\123\x45\123\x53\111\x4f\116\x5f\x49\116\104\x45\130"]);
        $this->loginIntoAdminDashboardIfEnabled($q6, $user, $KK, $ht, false);
        HJ:
        jt:
    }
    function isRoleBasedRedirectPluginEnabled()
    {
        $wB = UtilitiesSAML::isRolebasedRedirectionPluginInstalled();
        return isset($wB["\145\x6e\141\x62\x6c\x65\x64"]) ? $wB["\145\156\141\142\154\145\x64"] : 0;
    }
    function RegisterCurrentUser($x2, $q6, $hO, $L3, $dZ, $J2, $ht, $Ax, $nv)
    {
        $BE = JPATH_BASE . DIRECTORY_SEPARATOR . "\154\x69\x62\162\141\x72\151\x65\163" . DIRECTORY_SEPARATOR . "\x6d\x69\x6e\x69\x6f\x72\x61\156\147\x65\143\x6f\155\x6d\165\156\151\164\171\x62\x75\x69\x6c\144\145\x72" . DIRECTORY_SEPARATOR . "\165\x74\x69\154\151\164\171" . DIRECTORY_SEPARATOR . "\x43\x62\125\164\x69\154\151\164\x69\x65\163\56\160\150\x70";
        $KI = 0;
        if (!file_exists($BE)) {
            goto o6;
        }
        jimport("\x6d\x69\156\x69\157\162\x61\x6e\147\145\143\157\155\x6d\165\156\151\x74\171\142\x75\x69\154\144\145\162\x2e\165\x74\x69\x6c\151\164\x79\x2e\x43\x62\125\164\151\x6c\x69\164\x69\x65\x73");
        $KI = CbUtilities::checkAndMapCBAttributes();
        o6:
        $FR = UtilitiesSAML::getCustomerDetails();
        $Xw = UtilitiesSAML::getRoleMapping($nv);
        $pW = $FR["\x65\156\x61\x62\154\x65\137\155\x61\156\x61\x67\145\x72\x5f\154\x6f\x67\x69\x6e"];
        $Mh = $FR["\x65\156\141\142\154\x65\x5f\141\x64\155\151\156\137\x63\150\x69\x6c\x64\137\154\157\x67\x69\156"];
        $l6 = $FR["\x65\156\x61\x62\x6c\x65\137\155\x61\156\x61\147\145\162\137\143\x68\x69\154\144\x5f\154\x6f\147\x69\x6e"];
        $Io = $FR["\145\x6e\x61\142\x6c\x65\137\144\157\x5f\x6e\x6f\x74\x5f\141\x75\x74\x6f\137\143\162\x65\141\x74\145\137\165\163\x65\x72\163"];
        $Cr = $FR["\164\x72\x69\x73\x74\x73"];
        $dh = 2;
        if (!isset($Xw["\x6d\141\160\x70\151\156\x67\137\x76\x61\154\x75\x65\137\144\145\146\x61\165\x6c\164"])) {
            goto ku;
        }
        $dh = $Xw["\x6d\141\x70\x70\151\156\147\x5f\166\141\x6c\165\145\x5f\x64\145\x66\x61\165\x6c\164"];
        ku:
        $Zs = array();
        if (!isset($Xw["\162\157\x6c\x65\137\155\141\x70\160\x69\156\147\137\x6b\x65\x79\137\166\141\154\165\x65"])) {
            goto qI;
        }
        $Zs = json_decode($Xw["\162\157\x6c\x65\x5f\x6d\141\160\x70\151\156\147\137\153\145\x79\x5f\166\x61\x6c\165\x65"]);
        qI:
        $vp = 0;
        if (!isset($Xw["\145\156\x61\142\x6c\x65\137\x73\x61\x6d\x6c\137\162\x6f\x6c\x65\x5f\155\x61\x70\160\x69\x6e\x67"])) {
            goto cf;
        }
        $vp = json_decode($Xw["\145\156\x61\x62\154\145\x5f\x73\x61\155\x6c\x5f\x72\x6f\154\x65\137\155\x61\x70\160\x69\x6e\147"]);
        cf:
        $Cr = Mo_saml_Local_Util::decrypt_value($Cr);
        $I6["\x6e\141\x6d\145"] = isset($hO) && !empty($hO) ? $hO : $L3;
        $I6["\x75\163\145\x72\x6e\x61\x6d\x65"] = $L3;
        $I6["\x65\x6d\141\151\x6c"] = $I6["\x65\x6d\141\151\x6c\x31"] = $I6["\145\155\141\x69\x6c\62"] = JStringPunycode::emailToPunycode($dZ);
        $I6["\x70\141\x73\163\167\x6f\162\144"] = $I6["\160\x61\x73\x73\167\157\x72\144\61"] = $I6["\160\141\163\163\x77\x6f\162\x64\x32"] = JUserHelper::genRandomPassword();
        $Pw = 0;
        if (!($Cr == true)) {
            goto Nb;
        }
        $F9 = UtilitiesSAML::gt_lk_trl();
        $Sw = 0;
        if (!$F9["\x4c\x69\x63\145\156\163\145\x45\x78\x70\x69\162\x65\144"]) {
            goto XY;
        }
        UtilitiesSAML::rmvextnsns();
        $AA = UtilitiesSAML::get_message_and_cause($F9["\x4c\x69\x63\145\156\x73\x65\105\x78\x70\151\162\145\144"], $Sw);
        UtilitiesSAML::show_error_messages($AA["\x6d\x73\147"], $AA["\x63\141\165\x73\x65"]);
        XY:
        Nb:
        if (!($vp == 1)) {
            goto Py;
        }
        $kd = UtilitiesSAML::get_mapped_groups($Zs, $Ax);
        if (empty($kd)) {
            goto Zx;
        }
        foreach ($kd as $wN) {
            $I6["\x67\162\157\x75\160\x73"][] = $wN;
            Ho:
        }
        FC:
        goto J0;
        Zx:
        if (isset($Xw["\x64\157\137\x6e\x6f\164\x5f\141\165\164\157\137\x63\162\x65\x61\x74\x65\137\x75\163\145\162\x73"]) && $Xw["\144\x6f\x5f\x6e\x6f\x74\137\x61\165\x74\157\x5f\143\162\x65\141\x74\x65\137\x75\163\x65\162\163"]) {
            goto mV;
        }
        $I6["\147\x72\157\165\x70\163"][] = $dh;
        goto OV;
        mV:
        $Pw = 1;
        OV:
        J0:
        Py:
        if (!($Pw || $Io)) {
            goto WQ;
        }
        $ei = JURI::root();
        echo "\x20\40\40\40\40\x20\x20\x20\40\40\x20\x20\x3c\x64\151\x76\x20\x73\x74\x79\x6c\x65\75\x22\146\157\156\164\x2d\x66\141\155\151\x6c\171\x3a\103\141\154\x69\x62\x72\x69\x3b\160\141\144\144\x69\156\147\72\60\40\x33\x25\x3b\x22\76\xd\12\x20\x20\40\x20\x20\40\x20\40\40\x20\x20\40\74\144\151\166\40\x73\164\171\154\x65\75\x22\x63\157\x6c\157\162\72\x20\x23\141\71\x34\64\64\x32\73\142\141\x63\153\147\162\x6f\x75\x6e\144\x2d\x63\x6f\154\157\x72\72\x20\x23\146\62\x64\x65\144\x65\x3b\160\141\x64\144\x69\x6e\147\72\x20\x31\x35\x70\170\73\155\x61\162\x67\x69\x6e\x2d\142\x6f\x74\x74\157\155\72\40\x32\x30\160\170\73\164\x65\170\164\55\x61\154\151\147\156\x3a\x63\x65\x6e\x74\145\162\73\142\x6f\162\144\x65\x72\x3a\x31\160\x78\x20\163\x6f\154\151\144\40\43\x45\x36\x42\63\x42\x32\73\x66\x6f\x6e\x74\x2d\x73\151\172\145\72\61\x38\x70\164\73\x22\x3e\40\x45\122\122\x4f\x52\x3c\x2f\x64\151\166\x3e\xd\xa\40\x20\x20\x20\x20\x20\x20\40\x20\40\40\40\x3c\x64\151\x76\40\x73\164\x79\154\145\75\x22\x63\x6f\x6c\157\162\x3a\x20\x23\141\71\64\64\x34\62\x3b\x66\x6f\x6e\x74\55\163\x69\172\145\72\x31\64\160\164\x3b\40\x6d\x61\162\x67\x69\x6e\55\142\157\164\x74\157\155\x3a\x32\x30\x70\170\x3b\x22\76\74\x70\x3e\74\x73\164\162\157\x6e\x67\x3e\x45\162\162\x6f\162\72\x20\74\x2f\x73\x74\162\157\x6e\147\x3e\x55\163\145\162\40\151\x73\x20\162\145\x73\164\162\x69\x63\x74\145\144\40\x74\157\40\154\x6f\x67\151\156\56\74\57\x70\x3e\xd\xa\x20\40\40\40\x20\x20\x20\x20\x20\40\x20\40\x20\40\40\x20\74\x70\x3e\x50\154\x65\x61\163\145\40\x63\157\156\x74\x61\143\164\x20\x79\157\x75\162\x20\141\x64\x6d\x69\x6e\x69\x73\164\x72\141\x74\157\162\40\141\x6e\144\x20\162\x65\x70\x6f\162\x74\x20\164\x68\x65\x20\146\157\x6c\154\157\x77\151\x6e\147\x20\x65\162\162\x6f\162\x3a\x3c\x2f\160\76\15\xa\x20\x20\40\x20\40\x20\x20\x20\40\x20\40\x20\x20\40\40\x20\x3c\x70\76\x3c\x73\x74\x72\157\156\147\76\x50\x6f\x73\163\x69\x62\x6c\x65\x20\103\141\165\x73\x65\x3a\x20\x3c\57\163\x74\162\157\156\147\x3e\x20\116\157\156\x20\145\170\151\163\164\x69\x6e\147\40\x75\x73\x65\x72\163\40\141\x72\x65\40\156\x6f\164\x20\x61\154\x6c\x6f\167\x65\144\40\164\157\x20\154\x6f\147\151\x6e\56\74\x2f\160\76\15\xa\40\40\x20\x20\x20\x20\40\x20\x20\x20\x20\40\74\57\144\x69\x76\76\xd\12\40\x20\x20\x20\x20\40\x20\x20\x20\x20\40\40\x3c\144\x69\x76\x20\163\x74\x79\x6c\x65\75\x22\155\141\162\147\151\156\x3a\63\45\x3b\x64\x69\163\160\x6c\x61\171\72\142\154\157\143\x6b\x3b\164\x65\170\164\55\x61\x6c\x69\147\x6e\72\143\145\156\164\145\x72\73\x22\x3e\xd\12\xd\xa\40\40\40\40\40\40\x20\x20\x20\40\40\40\x3c\x64\151\166\40\x73\x74\171\154\x65\75\42\x6d\x61\162\x67\x69\x6e\x3a\x33\45\73\144\x69\163\x70\x6c\x61\x79\72\x62\x6c\x6f\143\153\73\164\x65\170\x74\55\x61\154\151\147\x6e\72\143\145\156\x74\x65\x72\73\x22\x3e\x3c\x61\x20\150\x72\145\146\75\x22\x20";
        echo $ei;
        echo "\x20\x22\x3e\15\xa\40\x20\40\x20\40\x20\40\x20\x20\x20\x20\40\x20\x20\40\x20\x20\x20\x20\x20\x3c\151\156\160\165\x74\40\163\x74\x79\154\145\75\x22\x70\141\144\x64\x69\x6e\x67\x3a\61\x25\73\x77\151\x64\x74\150\72\61\x30\x30\160\170\x3b\142\141\143\153\x67\162\157\x75\156\x64\x3a\x20\x23\60\60\71\x31\x43\x44\x20\156\157\156\x65\x20\162\145\x70\145\x61\x74\40\163\x63\162\x6f\154\x6c\40\x30\x25\x20\x30\45\73\x63\165\162\163\157\162\72\40\x70\x6f\x69\156\164\145\162\x3b\146\157\x6e\x74\x2d\163\151\172\x65\x3a\x31\x35\160\170\73\x62\157\x72\144\x65\162\55\167\x69\x64\164\150\x3a\x20\x31\160\170\73\x62\157\162\x64\x65\162\55\163\164\171\x6c\x65\x3a\x20\x73\x6f\154\151\144\73\142\x6f\x72\x64\145\x72\x2d\x72\x61\x64\151\165\163\x3a\x20\63\160\x78\73\x77\150\x69\x74\x65\x2d\x73\160\x61\143\x65\72\40\156\157\167\162\x61\160\73\142\157\170\55\x73\x69\x7a\151\156\x67\72\40\142\x6f\162\144\x65\162\x2d\x62\x6f\170\x3b\142\157\162\144\145\162\x2d\143\157\154\x6f\x72\x3a\40\x23\x30\60\67\63\101\x41\73\142\x6f\x78\55\163\x68\141\x64\x6f\x77\x3a\40\x30\160\x78\40\x31\x70\170\40\60\x70\x78\40\x72\147\142\141\x28\x31\62\x30\x2c\x20\x32\60\x30\54\x20\x32\63\60\x2c\x20\x30\x2e\66\x29\40\x69\156\163\145\164\73\x63\157\x6c\157\162\x3a\x20\43\106\x46\x46\73\x22\x20\x74\171\160\145\75\42\142\165\164\164\x6f\156\42\40\x76\x61\154\165\145\75\x22\x44\x6f\156\145\42\x20\157\x6e\103\154\x69\x63\153\x3d\42\x73\x65\x6c\x66\x2e\143\x6c\x6f\163\145\x28\51\73\42\x3e\74\57\141\x3e\x3c\x2f\x64\x69\x76\x3e\xd\12\x20\40\x20\x20\40\x20\40\x20\x20\40\x20\40";
        exit;
        WQ:
        jimport("\152\x6f\157\x6d\154\x61\x2e\x61\160\x70\x6c\151\143\x61\164\151\x6f\x6e\56\x63\x6f\155\160\x6f\156\x65\156\x74\x2e\x6d\x6f\144\145\154");
        if (defined("\x4a\120\101\124\110\137\x43\117\115\120\x4f\116\105\x4e\124")) {
            goto Ce;
        }
        define("\112\120\101\x54\x48\x5f\103\x4f\115\x50\117\116\105\x4e\x54", JPATH_BASE . "\x2f\x63\157\155\x70\157\156\x65\156\x74\x73\x2f");
        Ce:
        $user = new JUser();
        if ($user->bind($I6)) {
            goto Ny;
        }
        throw new Exception("\x43\157\x75\x6c\144\x20\x6e\x6f\164\40\142\x69\156\x64\x20\x64\141\164\x61\56\x20\x45\162\x72\157\x72\x3a\40" . $user->getError());
        Ny:
        if (!($Cr == true)) {
            goto el;
        }
        $F9 = UtilitiesSAML::gt_lk_trl();
        $Sw = 0;
        if (!($F9["\x4e\157\157\x66\125\163\145\162\163"] <= $FR["\x75\x73\162\154\x6d\x74"])) {
            goto xs;
        }
        UtilitiesSAML::rmvextnsns();
        $Sw = 1;
        UtilitiesSAML::get_message_and_cause($F9["\114\x69\143\145\x6e\163\x65\105\170\x70\x69\162\x65\x64"], $Sw);
        UtilitiesSAML::show_error_messages($AA["\155\x73\x67"], $AA["\x63\141\x75\163\145"]);
        xs:
        el:
        $Ru = UtilitiesSAML::getCustomerDetails();
        $qT = isset($Ru["\x69\147\156\157\x72\145\x5f\163\160\145\x63\151\x61\154\137\143\x68\141\x72\141\x63\x74\x65\x72\x73"]) ? $Ru["\x69\x67\156\x6f\x72\x65\x5f\163\160\x65\x63\151\x61\154\x5f\x63\x68\x61\162\141\143\x74\x65\x72\x73"] : 0;
        if ($qT) {
            goto YC;
        }
        if (!$user->save()) {
            goto pU;
        }
        if (!($Cr && $user->save())) {
            goto qZ;
        }
        UtilitiesSAML::addUser($FR["\x75\163\162\x6c\x6d\x74"]);
        qZ:
        goto mu;
        pU:
        UtilitiesSAML::showErrorMessage("\103\x6f\165\154\x64\40\156\157\x74\x20\x73\x61\x76\145\40\x75\163\145\x72\x2e\40\x42\x65\143\x61\165\x73\x65\40\x75\163\145\162\40\167\151\164\x68\x20\x73\x61\155\x65\40\165\x73\x65\162\156\x61\x6d\x65\40\x6f\162\x20\145\x6d\141\151\154\x20\141\x6c\x72\x65\x61\144\171\40\160\x72\145\163\145\156\164\x20\151\156\x20\x4a\x6f\x6f\x6d\154\x61", "\112\x4f\61\60\x31");
        mu:
        goto qC;
        YC:
        if (preg_match("\x5b\x27\135", $I6["\x65\x6d\x61\151\x6c"])) {
            goto r_;
        }
        if (!$user->save()) {
            goto D8;
        }
        if (!($Cr == true)) {
            goto gw;
        }
        UtilitiesSAML::addUser($FR["\165\163\162\154\155\164"]);
        gw:
        goto BL;
        D8:
        UtilitiesSAML::showErrorMessage("\103\157\165\x6c\144\x20\x6e\x6f\164\40\x73\141\x76\145\40\165\x73\x65\162\56\x20\102\x65\143\141\165\x73\145\x20\x75\x73\x65\162\x20\x77\x69\x74\x68\x20\x73\x61\x6d\x65\40\165\x73\145\x72\156\x61\x6d\x65\x20\x6f\162\40\145\155\x61\151\154\40\141\x6c\162\x65\141\144\171\40\x70\162\x65\163\x65\156\x74\x20\151\156\x20\112\x6f\x6f\x6d\154\x61", "\112\x4f\x31\60\x31");
        BL:
        goto D3;
        r_:
        UtilitiesSAML::saveUserInDB($I6, $J2);
        $lc = UtilitiesSAML::get_user_from_joomla($J2, $I6["\x75\163\145\162\156\141\155\145"], $I6["\145\x6d\x61\x69\154"]);
        if (empty($lc)) {
            goto yd;
        }
        UtilitiesSAML::updateUserGroup($lc->id, $I6["\x67\162\157\165\x70\x73"][0]);
        yd:
        if (!($Cr == true)) {
            goto fB;
        }
        UtilitiesSAML::addUser($FR["\x75\x73\162\154\x6d\x74"]);
        fB:
        D3:
        qC:
        $Va = JPATH_BASE . DIRECTORY_SEPARATOR . "\x6c\151\142\162\141\x72\x69\x65\x73" . DIRECTORY_SEPARATOR . "\155\151\156\151\x6f\162\x61\156\x67\145\147\165\x72\x75\154\x69\x62" . DIRECTORY_SEPARATOR . "\x75\164\x69\x6c\151\164\x79" . DIRECTORY_SEPARATOR . "\x55\164\151\x6c\151\x74\x69\x65\x73\107\x75\x72\165\x2e\160\150\160";
        if (!file_exists($Va)) {
            goto kq;
        }
        require_once $Va;
        $pf = UtilitiesGuru::loadGuruCourseName($kd);
        if (empty($pf)) {
            goto SJ;
        }
        foreach ($pf as $oU) {
            UtilitiesGuru::addUserToCourse($oU, $user);
            p2:
        }
        oo:
        SJ:
        kq:
        UtilitiesSAML::updateActivationStatusForUser($L3);
        $lc = UtilitiesSAML::get_user_from_joomla($J2, $L3, $dZ);
        if (!$lc) {
            goto Dv;
        }
        $user = JUser::getInstance($lc->id);
        $this->updateUserProfileAttributes($user->id, $x2, isset($nv["\x75\x73\145\162\137\x70\162\157\x66\151\x6c\x65\x5f\x61\164\164\x72\151\x62\165\164\145\x73"]) ? $nv["\x75\163\145\x72\x5f\x70\162\157\x66\151\x6c\x65\x5f\141\x74\164\162\151\x62\x75\x74\x65\x73"] : '');
        if (!$KI) {
            goto hv;
        }
        CbUtilities::mapAttributes($user, $x2);
        hv:
        $q8 = UtilitiesSAML::getPluginConfigurations($nv["\151\x64"]);
        $q8 = current($q8);
        $ed = isset($q8["\x75\163\145\162\137\146\x69\145\x6c\144\137\141\164\x74\162\x69\x62\x75\164\x65\163"]) ? $q8["\165\x73\x65\162\137\x66\x69\145\x6c\144\137\141\x74\164\x72\151\x62\165\x74\x65\163"] : '';
        $this->updateUserFieldAttributes($user->id, $x2, $ed);
        $KK = JFactory::getSession();
        if (strpos($q6, "\x61\144\x6d\151\156\x69\163\164\162\x61\x74\157\162")) {
            goto x8;
        }
        $KK->set("\x75\x73\145\162", $user);
        $KK->set("\115\117\137\x53\x41\115\114\137\116\101\x4d\105\111\x44", $x2["\101\123\123\x45\122\124\111\x4f\x4e\137\x4e\101\115\x45\137\x49\x44"]);
        $KK->set("\115\117\x5f\123\101\x4d\x4c\137\123\105\123\123\111\117\x4e\x5f\x49\116\x44\105\x58", $x2["\101\x53\x53\105\122\x54\111\x4f\116\x5f\123\x45\x53\x53\x49\x4f\x4e\x5f\111\116\104\x45\x58"]);
        $KK->set("\x4d\117\137\x53\101\x4d\x4c\137\x4c\117\x47\x47\105\104\137\111\116\x5f\x57\x49\124\110\137\x49\104\120", TRUE);
        $KK->set("\115\x4f\x5f\123\x41\115\x4c\x5f\x49\104\120\x5f\x55\x53\x45\x44", $nv["\151\x64\160\x5f\x65\x6e\x74\151\x74\171\x5f\151\x64"]);
        x8:
        $ht->checkSession();
        $yH = $KK->getId();
        UtilitiesSAML::updateUsernameToSessionId($user->username, $yH);
        $ir = UtilitiesSAML::IsUserSuperUser($user);
        $jt = UtilitiesSAML::IsUserSuperUserChild($user);
        $Zm = UtilitiesSAML::IsUserManager($user);
        $uU = UtilitiesSAML::IsUserManagerChild($user);
        $q6 = Mo_saml_Local_Util::check_special_character_in_url($q6);
        $YE = $this->isRoleBasedRedirectPluginEnabled();
        if (!$YE) {
            goto gY;
        }
        $xY = UtilitiesSAML::getRoleBasedRedirectionConfiguration();
        $Fx = isset($xY["\145\x6e\141\x62\x6c\145\137\162\x6f\x6c\x65\137\x62\141\163\x65\x64\137\x72\x65\x64\151\162\145\x63\164\151\157\x6e"]) ? $xY["\145\x6e\141\142\x6c\145\x5f\162\157\x6c\145\x5f\142\x61\x73\x65\x64\137\x72\145\144\151\x72\145\143\x74\151\x6f\156"] : 0;
        if (!$Fx) {
            goto Gi;
        }
        $mc = UtilitiesSAML::getUserGroupID($user->groups);
        $Bn = isset($xY["\x72\x6f\154\145\x5f\142\141\x73\145\144\x5f\162\145\x64\x69\x72\x65\x63\164\x5f\153\x65\x79\x5f\166\x61\154\165\145"]) ? $xY["\x72\x6f\154\145\x5f\x62\141\x73\x65\x64\x5f\162\x65\x64\x69\x72\145\x63\164\x5f\153\145\x79\137\x76\x61\x6c\x75\145"] : '';
        $Bn = json_decode($Bn);
        $w6 = UtilitiesSAML::get_role_based_redirect_values($Bn, $mc);
        if (empty($w6)) {
            goto UI;
        }
        $q6 = $w6;
        UI:
        Gi:
        gY:
        $n4 = UtilitiesSAML::isLoginReportAddonEnable();
        if (!$n4) {
            goto JU;
        }
        $pB = "\123\123\x4f\40\125\x73\x65\162";
        commonUtilities::createLogs($user->username, $pB);
        JU:
        $GG = isset($q8["\165\x73\145\x72\137\x63\x6f\x6e\164\141\x63\164\137\x61\164\x74\162\x69\142\165\x74\x65\163"]) ? $q8["\x75\163\x65\x72\137\143\x6f\x6e\x74\141\143\164\x5f\141\x74\164\162\151\x62\165\x74\x65\163"] : '';
        $this->updateUserContactAttributes($user->id, $x2, $GG);
        $ep = UtilitiesSAML::check_table_present("\x63\x77\165\163\145\x72\x73");
        if (!$ep) {
            goto xq;
        }
        $KP = isset($q8["\165\x73\x65\162\x5f\x63\x77\x5f\141\164\x74\x72\151\x62\x75\164\145\x73"]) ? $q8["\x75\x73\145\162\137\143\x77\x5f\x61\164\x74\162\151\x62\x75\164\x65\x73"] : '';
        $Ag = $this->updateUserCWAttributes($user->id, $x2, $KP);
        if (!$Ag) {
            goto qd;
        }
        $ht->checkSession();
        $ht->redirect(urldecode($q6));
        qd:
        xq:
        if ($ir || $Zm && $pW || $jt && $Mh || $uU && $l6) {
            goto Dl;
        }
        if ($pW || $Mh || $l6) {
            goto Ia;
        }
        $ht->redirect($q6);
        goto OQ;
        Ia:
        if (strpos($q6, "\141\144\x6d\x69\x6e\x69\163\164\x72\141\x74\157\162") || strpos($SR, "\141\x64\155\151\x6e\151\163\x74\162\141\164\x6f\162")) {
            goto n5;
        }
        $ht->redirect($q6);
        goto S7;
        n5:
        if (!(!$ir || !$Zm || !$jt || !$uU)) {
            goto IM;
        }
        UtilitiesSAML::show_error_messages("\x59\157\165\40\141\x72\x65\40\x6e\157\x74\x20\x61\154\154\x6f\x77\145\144\40\x74\x6f\x20\x6c\x6f\147\151\156\x20\151\x6e\164\x6f\40\142\141\x63\x6b\x65\156\x64", "\131\157\165\40\x64\157\x6e\164\x20\150\141\166\x65\x20\x61\x63\143\145\163\x73\x20\164\x6f\40\142\x61\x63\x6b\145\156\144");
        exit;
        IM:
        S7:
        OQ:
        goto SW;
        Dl:
        if (strpos($q6, "\x61\144\155\151\x6e\151\163\x74\162\x61\x74\157\162")) {
            goto IS;
        }
        $ht->redirect($q6);
        goto Q0;
        IS:
        $q6 = JURI::root() . "\141\144\155\x69\156\151\163\x74\x72\141\x74\157\162\57\151\156\x64\x65\170\56\x70\150\x70";
        $KK->set("\115\117\137\123\x41\x4d\x4c\137\111\x44\x50\137\x55\123\x45\104", $nv["\151\x64\x70\x5f\x65\156\164\151\164\x79\137\151\x64"]);
        $KK->set("\115\x4f\137\123\x41\115\114\137\116\101\x4d\x45\x49\104", $x2["\101\123\123\x45\x52\124\x49\117\116\137\x4e\101\115\105\x5f\111\x44"]);
        $KK->set("\x4d\x4f\137\x53\x41\115\x4c\137\123\x45\x53\123\x49\117\116\137\111\x4e\104\x45\x58", $x2["\x41\x53\x53\105\x52\124\111\117\116\137\x53\x45\123\123\111\117\x4e\x5f\x49\116\x44\x45\x58"]);
        $this->loginIntoAdminDashboardIfEnabled($q6, $user, $KK, $ht, false);
        Q0:
        SW:
        Dv:
    }
    function updateUserFieldAttributes($QQ, $x2, $R3)
    {
        $eA = UtilitiesSAML::getUserProfileData($x2, $R3);
        $Gu = UtilitiesSAML::getUserFieldDataFromTable($QQ);
        if (!empty($Gu)) {
            goto L3;
        }
        foreach ($eA as $Kj) {
            $KY = $Kj["\160\x72\x6f\146\151\154\145\137\153\145\171"];
            $KY = UtilitiesSAML::getIdFromFields($KY);
            $OA = $Kj["\160\x72\157\x66\x69\154\x65\x5f\166\x61\x6c\x75\145"];
            $eF = new stdClass();
            $eF->field_id = $KY->id;
            $eF->item_id = $QQ;
            $eF->value = $OA;
            JFactory::getDbo()->insertObject("\43\137\137\x66\x69\x65\154\x64\163\137\x76\141\x6c\x75\145\x73", $eF);
            Pi:
        }
        d1:
        goto be;
        L3:
        foreach ($eA as $Kj) {
            $KY = $Kj["\160\162\x6f\146\x69\154\x65\x5f\153\x65\x79"];
            $KY = UtilitiesSAML::getIdFromFields($KY);
            $OA = $Kj["\x70\x72\x6f\x66\151\154\x65\137\166\141\154\x75\x65"];
            $VM = JFactory::getDbo();
            $Ql = $VM->getQuery(true);
            $wL = $VM->quoteName("\x76\x61\x6c\165\x65") . "\x20\x3d\x20" . $VM->quote($OA);
            $uL = array($VM->quoteName("\x66\x69\x65\154\x64\137\151\144") . "\40\75\x20" . $VM->quote($KY->id), $VM->quoteName("\x69\x74\145\x6d\137\151\144") . "\40\75\40" . $VM->quote($QQ));
            $Ql->update($VM->quoteName("\x23\137\137\x66\x69\x65\x6c\x64\x73\x5f\x76\x61\x6c\x75\x65\163"))->set($wL)->where($uL);
            $VM->setQuery($Ql);
            $VM->execute();
            tu:
        }
        ql:
        be:
    }
    function updateUserContactAttributes($QQ, $x2, $sv)
    {
        $yD = UtilitiesSAML::getUserProfileData($x2, $sv);
        $Tq = UtilitiesSAML::checkIfContactExist($QQ);
        $DH = UtilitiesSAML::checkExtensionEnabled("\x63\x6f\x6e\164\x61\x63\x74\x63\162\x65\x61\x74\x6f\162");
        if (!$DH["\x65\156\x61\x62\x6c\145\144"]) {
            goto Bm;
        }
        if (!empty($Tq)) {
            goto u8;
        }
        $VM = JFactory::getDbo();
        $Ql = $VM->getQuery(true);
        $user = JFactory::getUser($QQ);
        $D3 = array("\156\141\x6d\145", "\141\x6c\151\x61\163", "\145\155\141\x69\x6c\137\164\x6f", "\x75\x73\145\x72\x5f\x69\x64", "\160\x61\162\141\155\x73", "\154\141\156\147\165\141\147\x65", "\x63\x72\145\x61\164\145\144", "\155\x6f\x64\x69\x66\x69\x65\144", "\x6d\x65\164\141\144\141\x74\x61", "\x6d\x65\x74\141\x64\145\163\143");
        $ZR = array($VM->quote($user->email), $VM->quote($user->email), $VM->quote($user->email), $QQ, $VM->quote(''), $VM->quote("\x2a"), $VM->quote(date("\131\55\x6d\x2d\x64\x20\x68\x3a\x6d\x3a\163", time())), $VM->quote(date("\x59\55\x6d\55\144\40\x68\x3a\155\x3a\x73", time())), $VM->quote(''), $VM->quote(''));
        foreach ($yD as $tA) {
            array_push($D3, $tA["\160\162\x6f\146\x69\x6c\x65\137\153\x65\171"]);
            array_push($ZR, $VM->quote($tA["\160\x72\157\146\x69\x6c\x65\137\x76\141\x6c\x75\145"]));
            a4:
        }
        g6:
        $Ql->insert($VM->quoteName("\x23\137\x5f\143\157\x6e\x74\141\x63\x74\x5f\x64\x65\x74\x61\x69\154\x73"))->columns($VM->quoteName($D3))->values(implode("\54", $ZR));
        $VM->setQuery($Ql);
        $VM->execute();
        goto I7;
        u8:
        foreach ($yD as $tA) {
            $VM = JFactory::getDbo();
            $Ql = $VM->getQuery(true);
            $wL = $VM->quoteName($tA["\160\162\157\146\x69\154\x65\137\153\145\171"]) . "\40\75\x20" . $VM->quote($tA["\x70\x72\157\146\151\154\x65\x5f\x76\x61\x6c\165\x65"]);
            $Ql->update($VM->quoteName("\43\x5f\x5f\143\157\x6e\164\x61\143\x74\137\144\x65\x74\141\x69\154\x73"))->set($wL)->where($VM->quoteName("\x69\x64") . "\75" . $VM->quote($Tq));
            $VM->setQuery($Ql);
            $VM->execute();
            kP:
        }
        KB:
        I7:
        Bm:
    }
    function updateUserProfileAttributes($ZS, $r3, $Ik)
    {
        $an = UtilitiesSAML::getUserProfileData($r3, $Ik);
        $WJ = UtilitiesSAML::getUserProfileDataFromTable($ZS);
        if (!(isset($an) && !empty($an))) {
            goto L4;
        }
        $fV = UtilitiesSAML::selectMaxOrdering($ZS);
        $VM = JFactory::getDbo();
        foreach ($an as $eq) {
            $KY = "\x70\x72\x6f\146\151\154\145\56" . strtolower($eq["\160\x72\x6f\146\151\154\x65\137\x6b\145\x79"]);
            $OA = $eq["\160\162\x6f\146\151\x6c\145\137\x76\141\x6c\165\x65"];
            if (in_array($KY, $WJ)) {
                goto YM;
            }
            $Ql = $VM->getQuery(true);
            $qv = array("\165\x73\145\x72\x5f\151\x64", "\x70\162\x6f\x66\151\154\x65\137\153\145\x79", "\160\162\157\x66\151\x6c\x65\x5f\166\x61\154\x75\145", "\x6f\x72\x64\145\162\x69\156\147");
            $ZR = array($ZS, $VM->quote($KY), $VM->quote($OA), ++$fV);
            $Ql->insert($VM->quoteName("\43\x5f\x5f\x75\x73\145\162\x5f\160\162\157\x66\151\x6c\145\163"))->columns($VM->quoteName($qv))->values(implode("\x2c", $ZR));
            $VM->setQuery($Ql);
            $VM->execute();
            goto sI;
            YM:
            $Ql = $VM->getQuery(true);
            $Kj = array($VM->quoteName("\160\x72\157\146\151\154\145\x5f\166\x61\x6c\x75\145") . "\40\75\x20" . $VM->quote($OA));
            $WX = array($VM->quoteName("\165\x73\x65\x72\137\151\144") . "\x20\x3d\40" . $VM->quote($ZS), $VM->quoteName("\160\x72\x6f\146\x69\x6c\x65\x5f\153\x65\x79") . "\x20\x3d\40" . $VM->quote($KY));
            $Ql->update($VM->quoteName("\x23\x5f\x5f\165\x73\145\x72\137\160\162\157\x66\151\154\145\x73"))->set($Kj)->where($WX);
            $VM->setQuery($Ql);
            $VM->execute();
            sI:
            MA:
        }
        E4:
        L4:
    }
    function updateUserCWAttributes($QQ, $x2, $KP)
    {
        $qw = UtilitiesSAML::getUserProfileData($x2, $KP);
        $Yw = "\x23\137\137\x63\167\x75\x73\x65\x72\x73";
        $fy = self::ifuseridexists($QQ, $Yw);
        if (!empty($fy)) {
            goto LM;
        }
        $bV = array();
        $nb = array();
        $ym = JFactory::getDBO();
        $zU = $ym->getTableColumns("\x23\x5f\137\x63\x77\x75\x73\x65\x72\163");
        $WG = array();
        foreach ($zU as $KY => $OA) {
            $WG[] = $KY;
            OT:
        }
        nm:
        $Jk = array();
        foreach ($qw as $KY) {
            $Jk[] = $KY["\x70\162\x6f\146\x69\x6c\145\x5f\x6b\x65\x79"];
            Fa:
        }
        Tp:
        $Fe = array_diff($WG, $Jk);
        $VM = JFactory::getDbo();
        $Ql = $VM->getQuery(true);
        foreach ($Fe as $KY) {
            if ($KY == "\151\x64") {
                goto Ij;
            }
            if ($KY == "\x62\x6c\157\x63\x6b") {
                goto z1;
            }
            $bV[] = $KY;
            $nb[] = $VM->quote("\40");
            goto bu;
            z1:
            $bV[] = "\142\x6c\157\x63\153";
            $nb[] = 0;
            bu:
            goto Im;
            Ij:
            $bV[] = "\x69\144";
            $nb[] = $QQ;
            Im:
            H2:
        }
        oP:
        foreach ($qw as $Kj) {
            $bV[] = $Kj["\x70\x72\x6f\146\x69\x6c\145\x5f\x6b\145\171"];
            $nb[] = $VM->quote($Kj["\x70\x72\x6f\x66\x69\154\145\137\x76\141\154\x75\145"]);
            if (!($Kj["\x70\x72\x6f\146\151\x6c\x65\x5f\x6b\x65\x79"] == "\125\163\145\162\x2e\141\x63\x74\151\x76\141\x74\145\144")) {
                goto jA;
            }
            if (!($Kj["\x70\162\x6f\146\151\154\145\137\x76\141\154\165\x65"] == "\x75\163\x65\x72\x2e\x64\x69\x73\141\142\x6c\x65\x64")) {
                goto Fu;
            }
            $lW = JFactory::getUser($QQ);
            $lW->set("\x62\154\x6f\143\153", 1);
            $lW->save();
            return "\142\x6c\157\143\153\x65\144";
            Fu:
            jA:
            PB:
        }
        xW:
        $Ql->insert($VM->quoteName("\x23\137\x5f\x63\x77\165\x73\x65\x72\x73"))->columns($VM->quoteName($bV))->values(implode("\54", $nb));
        $VM->setQuery($Ql);
        $VM->execute();
        goto ai;
        LM:
        foreach ($qw as $TK) {
            if (!($TK["\160\x72\x6f\x66\x69\x6c\145\137\x6b\x65\171"] == "\x70\x68\157\x74\157" && empty($TK["\x70\x72\157\146\x69\x6c\145\x5f\166\x61\x6c\165\145"]))) {
                goto eO;
            }
            goto ik;
            eO:
            $VM = JFactory::getDbo();
            $Ql = $VM->getQuery(true);
            $wL = $VM->quoteName($TK["\160\x72\157\x66\151\x6c\x65\x5f\153\145\x79"]) . "\x20\x3d\x20" . $VM->quote($TK["\160\x72\x6f\x66\x69\x6c\x65\x5f\166\141\154\165\x65"]);
            $Ql->update($VM->quoteName("\x23\137\x5f\x63\x77\x75\163\x65\x72\x73"))->set($wL)->where($VM->quoteName("\x69\x64") . "\x3d" . $VM->quote($fy[0]));
            $VM->setQuery($Ql);
            $VM->execute();
            ik:
        }
        Lb:
        ai:
    }
    function addOrRemoveUserFromGroup($kd, $dh, $user, $HV)
    {
        $uM = UtilitiesSAML::getCustomerDetails();
        if (empty($kd)) {
            goto Cb;
        }
        $XC = 1;
        foreach ($kd as $wN) {
            UtilitiesSAML::updateUserGroup($user->id, $wN);
            if (!$XC) {
                goto wG;
            }
            if ($HV) {
                goto Vo;
            }
            foreach ($user->groups as $iF) {
                if (!($iF != $wN)) {
                    goto iM;
                }
                if ($uM["\x69\147\156\x6f\x72\145\x5f\x73\x70\145\x63\x69\141\154\x5f\x63\x68\x61\162\x61\143\x74\x65\162\x73"] != 1) {
                    goto bU;
                }
                UtilitiesSAML::removeUserGroups($user->id, $iF);
                goto Tw;
                bU:
                JUserHelper::removeUserFromGroup($user->id, $iF);
                Tw:
                iM:
                iO:
            }
            ge:
            $XC = 0;
            Vo:
            wG:
            nN:
        }
        Uf:
        goto eH;
        Cb:
        UtilitiesSAML::updateUserGroup($user->id, $dh);
        if ($HV) {
            goto ho;
        }
        foreach ($user->groups as $iF) {
            if (!($iF != $dh)) {
                goto fi;
            }
            JUserHelper::removeUserFromGroup($user->id, $iF);
            fi:
            Nx:
        }
        j_:
        ho:
        eH:
    }
    function loginIntoAdminDashboardIfEnabled($q6, $user, $KK, $ht, $rr)
    {
        $QY = UtilitiesSAML::getCustomerDetails();
        $uA = isset($QY["\x65\156\141\142\154\x65\x5f\141\x64\155\x69\156\x5f\x72\145\144\151\162\145\143\x74"]) ? $QY["\x65\156\141\x62\154\145\137\141\x64\155\x69\x6e\x5f\x72\x65\x64\x69\162\145\143\164"] : 0;
        $iS = isset($QY["\145\156\141\142\154\145\137\155\x61\x6e\141\x67\145\162\137\154\x6f\x67\151\x6e"]) ? $QY["\145\x6e\141\142\154\145\x5f\x6d\141\156\x61\147\x65\162\x5f\x6c\x6f\x67\x69\x6e"] : 0;
        $Mh = isset($QY["\x65\x6e\141\142\x6c\x65\137\x61\x64\155\151\x6e\137\x63\150\x69\x6c\x64\x5f\x6c\157\x67\151\x6e"]) ? $QY["\145\x6e\141\x62\154\145\x5f\x61\144\155\151\156\137\143\x68\151\154\x64\x5f\x6c\157\147\151\x6e"] : 0;
        $l6 = isset($QY["\145\156\141\x62\154\x65\x5f\155\141\156\141\x67\x65\x72\137\143\150\151\154\x64\137\154\157\x67\x69\156"]) ? $QY["\x65\156\x61\x62\154\x65\x5f\x6d\x61\x6e\x61\x67\x65\x72\x5f\143\x68\151\x6c\144\137\154\x6f\x67\x69\x6e"] : 0;
        $ir = UtilitiesSAML::IsUserSuperUser($user);
        $Zm = UtilitiesSAML::IsUserManager($user);
        $jt = UtilitiesSAML::IsUserSuperUserChild($user);
        $uU = UtilitiesSAML::IsUserManagerChild($user);
        if ($uA && $ir || $iS && $Zm || $Mh && $jt || $l6 && $uU) {
            goto dh;
        }
        $ht->redirect(urldecode($q6));
        goto lG;
        dh:
        $xi = time();
        $rb = $QY["\x61\x70\x69\x5f\153\145\x79"];
        $eW = $xi . "\x3a" . $rb . "\72" . $user->username;
        $Cy = $QY["\x63\x75\163\164\157\155\x65\x72\137\164\157\x6b\145\156"];
        $eW = AESEncryption::encrypt_data($eW, $Cy);
        $fp = $xi + 30;
        $Nw = setcookie("\x6d\x6f\x73\x61\x6d\154\x72\145\144\151\162\145\143\164", $eW, $fp, "\57");
        $Od = $KK->get("\x4d\x4f\137\123\101\115\114\137\x4e\x41\x4d\x45\x49\x44");
        $XP = $KK->get("\115\117\x5f\123\x41\115\x4c\137\x53\x45\123\123\x49\117\x4e\137\x49\116\x44\x45\130");
        $eW = $XP . "\x3a" . $Od . "\72" . urlencode($KK->get("\x4d\117\137\x53\101\115\x4c\x5f\x49\x44\x50\x5f\125\123\105\x44"));
        $eW = AESEncryption::encrypt_data($eW, $Cy);
        $Nw = setcookie("\137\x6d\157\163\145\x73\163\x69\x6f\x6e", $eW, $fp, "\x2f");
        $q6 = Mo_saml_Local_Util::check_special_character_in_url($q6);
        if (!empty($q6)) {
            goto tU;
        }
        $de = JURI::base();
        $kp = $de . "\x61\144\155\151\156\151\x73\164\162\141\164\x6f\162";
        goto x5;
        tU:
        $kp = $q6;
        x5:
        $ht->redirect($kp);
        lG:
    }
    function downloadCert($Ru)
    {
        $ef = UtilitiesSAML::get_public_private_certificate($Ru, "\160\x75\x62\154\151\x63\x5f\x63\x65\162\x74\x69\x66\151\x63\x61\164\x65");
        if ($ef == null || $ef == '' || empty($ef)) {
            goto Em;
        }
        $Iv = JPATH_BASE . DIRECTORY_SEPARATOR . "\x70\154\165\147\x69\156\163" . DIRECTORY_SEPARATOR . "\x61\165\x74\150\x65\x6e\164\x69\143\141\164\x69\157\x6e" . DIRECTORY_SEPARATOR . "\x6d\x69\156\x69\x6f\162\141\x6e\x67\145\163\x61\x6d\x6c" . DIRECTORY_SEPARATOR . "\x73\x61\x6d\154\62" . DIRECTORY_SEPARATOR . "\x63\x65\x72\x74" . DIRECTORY_SEPARATOR . "\x43\165\163\164\x6f\x6d\x50\x75\x62\x6c\151\143\103\145\x72\164\x69\x66\151\x63\x61\164\x65\x2e\x63\162\x74";
        goto gg;
        Em:
        $Iv = JPATH_BASE . DIRECTORY_SEPARATOR . "\x70\154\x75\147\151\x6e\163" . DIRECTORY_SEPARATOR . "\141\165\x74\x68\145\156\x74\151\143\141\164\x69\x6f\156" . DIRECTORY_SEPARATOR . "\x6d\151\x6e\x69\x6f\x72\x61\156\x67\x65\163\x61\x6d\x6c" . DIRECTORY_SEPARATOR . "\163\x61\155\x6c\x32" . DIRECTORY_SEPARATOR . "\x63\145\x72\x74" . DIRECTORY_SEPARATOR . "\163\x70\x2d\143\145\162\x74\x69\146\x69\x63\x61\164\x65\56\x63\162\164";
        gg:
        header("\x43\x6f\x6e\164\x65\156\x74\x2d\x54\171\160\x65\x3a\40\141\x70\x70\154\151\143\x61\164\151\157\x6e\57\x6f\x63\x74\145\164\55\163\164\x72\145\141\x6d");
        header("\103\157\x6e\164\145\156\164\55\124\x72\141\x6e\163\x66\x65\162\x2d\105\156\143\x6f\x64\151\x6e\147\x3a\x20\x75\164\x66\55\70");
        header("\103\157\156\164\145\156\164\55\144\151\x73\160\x6f\x73\x69\x74\151\157\156\x3a\40\141\x74\164\141\x63\150\155\x65\156\164\73\x20\x66\x69\154\145\x6e\141\155\x65\x3d\42" . basename($Iv) . "\42");
        flush();
        ob_clean();
        readfile($Iv);
        die;
    }
    function generateMetadata($Ru, $cB = false)
    {
        $lc = Mo_saml_Local_Util::getCustomerDetails();
        $g8 = $lc["\x6f\162\x67\x61\x6e\x69\172\141\164\x69\x6f\x6e\x5f\x6e\x61\155\x65"];
        $Tz = $lc["\157\x72\x67\x61\x6e\151\x7a\x61\x74\x69\157\x6e\137\x64\151\x73\x70\154\x61\171\x5f\x6e\141\155\145"];
        $DT = $lc["\x6f\x72\147\x61\156\151\172\141\x74\x69\157\x6e\137\165\x72\154"];
        $Tr = $lc["\164\x65\143\150\137\x70\145\162\137\x6e\x61\x6d\x65"];
        $QW = $lc["\x74\145\x63\150\137\145\x6d\x61\x69\x6c\x5f\x61\144\x64"];
        $Ob = $lc["\x73\165\x70\160\157\162\164\x5f\160\x65\x72\137\156\x61\x6d\x65"];
        $Yq = $lc["\x73\165\x70\x70\157\162\164\x5f\x65\155\x61\x69\x6c\x5f\141\144\144"];
        $Mn = '';
        $Ox = '';
        $ei = JURI::root();
        if (isset($lc["\163\160\137\x62\x61\x73\x65\x5f\x75\x72\x6c"])) {
            goto J1;
        }
        $Mn = $ei;
        $Ox = $ei . "\160\x6c\165\147\x69\x6e\x73\57\x61\165\x74\150\x65\156\164\x69\143\x61\164\x69\157\156\x2f\155\x69\x6e\151\157\x72\141\x6e\x67\x65\163\x61\155\x6c";
        goto uR;
        J1:
        $Mn = $lc["\163\160\137\142\x61\163\x65\x5f\x75\162\154"];
        $Ox = $lc["\x73\160\137\x65\156\164\x69\x74\171\137\x69\x64"];
        uR:
        $EO = $Mn . "\77\x6d\x6f\162\145\161\165\x65\163\164\x3d\141\x63\163";
        $lm = $Mn . "\x69\x6e\x64\145\170\x2e\160\150\x70\77\x6f\x70\164\x69\x6f\x6e\x3d\143\x6f\x6d\x5f\165\163\x65\x72\163\46\141\x6d\x70\73\x74\141\163\153\75\154\x6f\x67\157\165\164";
        $ef = UtilitiesSAML::get_public_private_certificate($Ru, "\x70\x75\142\x6c\x69\x63\x5f\143\145\x72\x74\151\146\x69\143\141\164\x65");
        if ($ef == null || $ef == '' || empty($ef)) {
            goto Ax;
        }
        $Iv = JPATH_BASE . DIRECTORY_SEPARATOR . "\x70\154\x75\147\x69\x6e\163" . DIRECTORY_SEPARATOR . "\x61\165\x74\x68\x65\156\x74\x69\x63\141\x74\x69\157\156" . DIRECTORY_SEPARATOR . "\155\x69\x6e\151\157\162\141\156\147\145\x73\x61\x6d\x6c" . DIRECTORY_SEPARATOR . "\x73\141\155\154\x32" . DIRECTORY_SEPARATOR . "\143\x65\162\x74" . DIRECTORY_SEPARATOR . "\103\x75\163\164\x6f\x6d\120\165\142\x6c\151\x63\x43\x65\x72\x74\x69\146\151\x63\x61\x74\x65\56\x63\162\x74";
        goto pb;
        Ax:
        $Iv = JPATH_BASE . DIRECTORY_SEPARATOR . "\160\x6c\x75\147\x69\x6e\163" . DIRECTORY_SEPARATOR . "\141\165\164\x68\x65\x6e\x74\151\x63\x61\x74\151\157\x6e" . DIRECTORY_SEPARATOR . "\155\151\x6e\x69\157\x72\141\156\147\145\163\141\x6d\x6c" . DIRECTORY_SEPARATOR . "\x73\141\x6d\154\62" . DIRECTORY_SEPARATOR . "\x63\145\x72\164" . DIRECTORY_SEPARATOR . "\x73\x70\x2d\x63\x65\x72\164\x69\146\x69\143\x61\x74\x65\x2e\143\162\164";
        pb:
        $Iv = file_get_contents($Iv);
        $Iv = UtilitiesSAML::desanitize_certificate($Iv);
        if ($cB) {
            goto i9;
        }
        header("\103\x6f\x6e\164\145\156\x74\x2d\x54\x79\160\145\72\x20\x74\x65\x78\164\x2f\x78\x6d\154");
        goto UU;
        i9:
        header("\x43\x6f\156\164\x65\156\x74\55\104\x69\163\160\157\163\x69\x74\x69\x6f\156\x3a\40\x61\x74\x74\141\143\150\x6d\145\156\x74\x3b\40\x66\151\154\x65\156\x61\155\x65\x20\75\x20\x22\x4d\145\x74\x61\x64\x61\164\x61\x2e\x78\x6d\154\x22");
        UU:
        echo "\x3c\77\170\x6d\x6c\40\x76\x65\x72\x73\x69\x6f\156\x3d\x22\x31\56\60\42\77\76\15\xa\11\11\x3c\155\144\x3a\105\156\x74\151\164\171\104\145\x73\x63\x72\x69\x70\x74\x6f\x72\40\x78\155\x6c\x6e\163\x3a\155\x64\x3d\42\x75\162\156\x3a\x6f\141\x73\151\x73\x3a\x6e\x61\155\145\163\72\x74\x63\72\123\101\x4d\x4c\x3a\x32\56\x30\72\155\145\x74\141\144\141\x74\141\x22\x20\166\141\154\151\x64\x55\x6e\164\x69\154\x3d\x22\62\x30\x32\x34\55\60\66\55\62\70\x54\x32\63\x3a\65\71\x3a\65\x39\x5a\42\x20\x63\x61\143\x68\x65\x44\165\162\141\164\x69\157\156\75\x22\x50\x54\x31\x34\64\x36\x38\60\70\x37\x39\62\x53\42\x20\x65\x6e\164\151\x74\171\x49\104\75\x22" . $Ox . "\x22\x3e\15\12\11\11\40\40\74\x6d\144\x3a\x53\x50\123\x53\117\104\145\x73\143\162\151\160\164\x6f\162\x20\x41\x75\164\x68\x6e\x52\145\x71\x75\145\163\164\163\123\151\147\156\x65\144\75\42\164\162\x75\x65\42\40\127\141\x6e\164\101\x73\x73\145\x72\x74\x69\157\156\x73\x53\x69\x67\156\x65\144\75\x22\x74\x72\165\145\42\40\160\162\x6f\164\157\x63\x6f\x6c\x53\165\160\x70\157\x72\x74\105\x6e\x75\155\145\x72\141\x74\x69\x6f\x6e\75\42\x75\x72\x6e\72\157\141\163\151\x73\72\156\x61\x6d\145\163\72\164\x63\x3a\123\x41\115\114\x3a\x32\x2e\60\x3a\160\x72\157\164\x6f\143\x6f\154\x22\x3e\15\xa\11\11\11\74\155\x64\x3a\x4b\145\x79\x44\x65\x73\143\x72\x69\x70\164\x6f\162\40\x75\163\145\x3d\42\163\151\147\156\x69\156\x67\42\x3e\15\xa\11\x9\x9\40\x20\74\144\163\72\x4b\x65\x79\111\156\146\157\x20\170\155\x6c\156\x73\x3a\144\163\75\x22\x68\164\164\160\x3a\x2f\x2f\x77\167\x77\56\167\x33\x2e\157\162\x67\57\62\60\60\x30\57\60\x39\x2f\170\155\x6c\x64\x73\x69\147\43\x22\76\15\xa\11\11\x9\11\x3c\x64\163\x3a\130\x35\60\x39\104\x61\164\141\76\15\12\x9\x9\x9\11\x20\40\74\x64\163\72\130\x35\60\71\x43\145\162\164\151\x66\151\143\141\x74\x65\76" . $Iv . "\x3c\x2f\x64\163\x3a\130\65\x30\x39\x43\x65\162\164\151\x66\151\143\x61\x74\x65\76\xd\xa\11\11\x9\x9\x3c\57\144\x73\72\130\65\x30\71\x44\x61\x74\x61\x3e\15\12\x9\x9\11\x20\40\x3c\x2f\144\163\72\113\145\x79\111\x6e\x66\157\76\15\12\11\x9\11\x3c\57\155\144\x3a\x4b\x65\x79\104\x65\163\x63\x72\x69\x70\164\x6f\x72\76\15\xa\11\11\11\x3c\155\144\x3a\113\145\x79\104\x65\163\x63\162\151\160\x74\x6f\x72\x20\x75\x73\x65\75\x22\145\156\143\x72\x79\x70\164\151\x6f\x6e\x22\x3e\xd\xa\11\11\11\x20\40\x3c\x64\163\x3a\113\x65\171\111\156\x66\157\40\x78\x6d\x6c\x6e\x73\x3a\x64\163\75\42\150\164\164\x70\x3a\57\57\x77\167\x77\56\167\x33\x2e\157\x72\x67\57\x32\60\60\60\x2f\x30\x39\57\170\x6d\x6c\x64\163\x69\147\x23\42\x3e\xd\12\x9\x9\11\x9\x3c\x64\163\72\x58\65\60\x39\104\x61\x74\141\x3e\15\12\x9\x9\11\11\40\x20\74\x64\163\72\x58\65\x30\x39\103\145\162\x74\x69\146\x69\143\141\164\x65\x3e" . $Iv . "\74\57\144\163\72\130\x35\x30\x39\x43\145\162\x74\151\x66\x69\x63\x61\164\x65\76\15\12\x9\11\11\x9\x3c\57\x64\163\x3a\x58\x35\x30\x39\x44\141\164\x61\x3e\xd\12\11\11\11\x20\x20\x3c\57\144\x73\72\x4b\145\x79\111\x6e\x66\157\x3e\xd\xa\x9\11\x9\x3c\57\x6d\x64\x3a\113\145\x79\x44\x65\x73\x63\162\x69\160\164\x6f\162\76\xd\xa\11\x9\11\x3c\x6d\144\x3a\x53\151\156\x67\154\145\114\157\147\157\x75\x74\x53\145\162\166\151\143\x65\40\102\151\156\144\x69\156\x67\75\x22\165\162\x6e\72\x6f\x61\x73\151\x73\x3a\x6e\x61\x6d\x65\163\x3a\x74\143\72\x53\x41\115\114\x3a\x32\56\60\72\x62\x69\156\144\x69\x6e\147\x73\72\x48\x54\124\120\55\120\x4f\x53\x54\42\40\x4c\x6f\143\141\x74\x69\x6f\x6e\75\42" . $lm . "\42\x2f\x3e\xd\12\11\x9\x9\x3c\x6d\x64\72\x53\x69\x6e\147\154\x65\x4c\157\147\157\165\164\123\145\x72\166\x69\x63\145\40\102\151\156\144\x69\156\x67\75\42\165\162\x6e\72\x6f\141\163\151\163\72\x6e\x61\x6d\x65\x73\72\164\x63\x3a\123\x41\x4d\114\72\x32\56\60\x3a\x62\x69\156\x64\x69\156\147\x73\72\x48\x54\x54\x50\x2d\x52\145\x64\x69\x72\x65\x63\164\42\40\x4c\157\143\x61\164\x69\157\x6e\75\42" . $lm . "\42\57\76\xd\xa\x9\11\x9\x3c\x6d\144\72\x4e\x61\x6d\x65\x49\x44\106\x6f\162\x6d\x61\164\x3e\x75\x72\156\x3a\x6f\x61\163\151\163\72\156\x61\x6d\145\x73\72\164\143\x3a\123\101\115\x4c\72\61\56\x31\x3a\x6e\141\x6d\145\151\x64\x2d\x66\157\162\x6d\x61\x74\x3a\145\155\x61\151\x6c\101\144\144\x72\145\x73\163\74\x2f\x6d\144\x3a\116\x61\x6d\x65\x49\104\x46\157\162\x6d\141\164\x3e\15\xa\11\x9\x9\x3c\155\144\x3a\116\141\155\145\x49\x44\x46\157\162\x6d\141\x74\76\165\162\156\x3a\157\141\x73\x69\x73\72\x6e\x61\x6d\145\163\x3a\x74\x63\x3a\123\x41\x4d\114\72\x31\56\x31\x3a\156\x61\x6d\x65\151\144\x2d\146\157\162\155\141\164\x3a\x75\156\163\160\x65\x63\x69\x66\151\x65\x64\74\57\155\144\x3a\x4e\141\x6d\145\x49\104\x46\x6f\162\x6d\x61\x74\x3e\xd\12\11\x9\11\74\x6d\144\72\x4e\141\155\145\111\x44\106\157\162\155\x61\164\76\165\x72\156\x3a\x6f\x61\x73\x69\x73\72\156\x61\x6d\x65\163\72\x74\143\72\x53\101\115\114\x3a\x32\56\60\x3a\156\x61\x6d\x65\151\x64\55\x66\x6f\x72\x6d\141\164\x3a\160\145\x72\x73\x69\x73\164\145\156\164\74\x2f\155\x64\72\x4e\141\x6d\145\x49\104\106\x6f\162\x6d\141\164\x3e\xd\12\11\11\11\74\155\144\x3a\116\x61\155\x65\x49\x44\x46\x6f\x72\155\141\x74\x3e\165\162\x6e\72\x6f\141\x73\x69\163\x3a\156\x61\x6d\145\x73\x3a\164\143\x3a\123\x41\115\x4c\72\62\56\60\x3a\x6e\141\155\x65\x69\144\x2d\x66\x6f\x72\x6d\x61\164\72\164\162\x61\x6e\163\151\x65\156\x74\74\57\x6d\x64\x3a\x4e\141\x6d\145\x49\104\x46\x6f\x72\x6d\x61\164\76\15\xa\x9\x9\x9\74\x6d\x64\x3a\x41\x73\x73\x65\x72\164\151\157\x6e\x43\157\156\163\165\155\145\x72\123\x65\162\166\151\143\x65\40\102\151\x6e\144\x69\x6e\x67\x3d\42\x75\x72\156\72\x6f\141\x73\151\163\x3a\x6e\x61\x6d\145\x73\72\x74\x63\x3a\x53\101\115\114\x3a\62\x2e\60\x3a\142\x69\x6e\144\151\x6e\147\x73\x3a\x48\124\124\120\55\x50\117\x53\124\x22\40\114\157\x63\141\x74\151\157\156\75\42" . $EO . "\x22\40\151\156\144\x65\x78\75\x22\x31\x22\x2f\x3e\xd\xa\x9\11\40\40\74\x2f\x6d\144\72\123\120\x53\123\x4f\104\145\x73\x63\x72\x69\160\x74\x6f\162\76\xd\12\11\x9\40\x20\74\155\x64\x3a\x4f\x72\147\x61\x6e\151\x7a\x61\164\151\x6f\x6e\76\15\12\x20\40\x20\x20\40\40\x20\x20\x20\x20\x20\40\74\x6d\x64\x3a\x4f\x72\147\x61\156\x69\x7a\x61\x74\x69\x6f\x6e\x4e\x61\x6d\x65\x20\170\x6d\x6c\72\154\x61\x6e\x67\75\x22\145\x6e\55\125\123\42\76" . $g8 . "\74\57\155\x64\72\117\x72\x67\x61\x6e\151\x7a\141\164\151\x6f\156\116\x61\x6d\145\x3e\xd\xa\40\40\40\x20\x20\x20\40\x20\x20\40\40\40\74\155\144\x3a\x4f\162\x67\141\x6e\x69\x7a\141\x74\151\157\156\x44\x69\x73\160\154\141\171\116\x61\x6d\x65\x20\x78\x6d\x6c\x3a\x6c\x61\156\147\x3d\42\x65\156\x2d\x55\123\x22\76" . $Tz . "\74\57\155\144\x3a\x4f\162\x67\x61\156\x69\172\x61\164\151\157\x6e\x44\151\163\x70\x6c\x61\x79\116\x61\155\145\76\15\xa\40\x20\40\x20\40\40\x20\40\x20\x20\x20\x20\x3c\x6d\x64\72\x4f\162\x67\141\x6e\x69\172\141\164\x69\157\156\125\122\x4c\40\x78\x6d\154\72\154\141\x6e\147\x3d\42\x65\156\x2d\125\x53\x22\x3e" . $DT . "\74\57\x6d\x64\x3a\x4f\x72\147\141\156\x69\x7a\x61\x74\151\x6f\x6e\x55\122\x4c\x3e\15\xa\x20\40\x20\40\x20\x20\40\40\40\x20\74\57\x6d\144\72\x4f\x72\147\141\156\x69\172\141\164\x69\x6f\156\76\xd\xa\40\x20\x20\x20\x20\x20\40\40\40\x20\74\x6d\x64\x3a\103\157\156\x74\x61\143\x74\120\145\162\x73\x6f\x6e\x20\x63\157\156\x74\x61\x63\x74\124\x79\160\x65\x3d\42\x74\145\143\x68\156\x69\143\141\154\42\x3e\xd\xa\40\40\40\x20\40\40\40\x20\x20\40\40\40\74\155\x64\x3a\x47\151\166\145\x6e\116\141\x6d\x65\76" . $Tr . "\x3c\x2f\x6d\144\72\107\151\166\145\x6e\x4e\x61\155\x65\76\15\12\40\x20\40\x20\40\40\40\x20\40\40\x20\x20\74\155\144\72\105\155\141\x69\x6c\x41\144\144\162\x65\163\163\x3e" . $QW . "\x3c\x2f\155\144\72\105\155\x61\x69\x6c\x41\144\144\162\145\x73\x73\x3e\xd\12\40\40\x20\x20\40\40\40\40\40\x20\x3c\x2f\x6d\144\x3a\x43\x6f\156\164\x61\x63\164\120\x65\162\x73\x6f\x6e\76\xd\xa\x20\40\40\40\40\40\40\40\40\x20\74\155\144\72\103\x6f\x6e\164\141\143\x74\120\x65\x72\x73\x6f\156\x20\143\x6f\156\164\x61\143\x74\124\171\x70\145\75\x22\x73\165\x70\x70\x6f\x72\164\x22\76\15\xa\x20\40\40\40\40\x20\40\40\40\x20\40\x20\74\155\x64\72\x47\x69\166\145\156\x4e\141\155\145\76" . $Ob . "\74\x2f\155\144\x3a\x47\x69\166\x65\156\x4e\141\x6d\145\76\xd\xa\x20\40\x20\x20\40\40\x20\40\40\40\40\x20\x3c\x6d\144\72\x45\155\x61\x69\x6c\x41\x64\x64\162\x65\163\x73\x3e" . $Yq . "\x3c\x2f\155\x64\x3a\105\x6d\x61\151\154\101\x64\144\x72\145\163\163\76\15\12\40\x20\40\x20\x20\x20\40\40\40\x20\x3c\x2f\x6d\144\72\103\x6f\x6e\164\141\143\164\x50\145\x72\x73\157\x6e\76\xd\12\x20\x20\x20\x20\x20\40\x20\40\x3c\57\155\x64\x3a\105\156\164\x69\164\171\x44\x65\x73\x63\162\x69\160\164\x6f\x72\76";
        exit;
    }
    function adminDashboardLogin($Ru)
    {
        $QY = UtilitiesSAML::getCustomerDetails();
        $uA = isset($QY["\145\156\x61\142\154\145\137\x61\144\155\x69\156\137\x72\145\144\x69\x72\x65\x63\x74"]) ? $QY["\145\156\x61\x62\154\x65\x5f\141\x64\x6d\151\x6e\x5f\162\x65\x64\151\x72\x65\143\x74"] : 0;
        $wm = isset($QY["\145\x6e\141\142\x6c\145\137\x6d\x61\156\x61\147\145\162\x5f\154\x6f\147\x69\156"]) ? $QY["\x65\x6e\141\x62\x6c\145\x5f\155\x61\x6e\141\x67\145\162\137\x6c\x6f\147\x69\x6e"] : 0;
        $Mh = isset($QY["\x65\x6e\141\142\154\145\137\x61\144\x6d\x69\x6e\x5f\143\x68\x69\x6c\144\x5f\x6c\x6f\147\151\156"]) ? $QY["\x65\156\x61\142\x6c\145\x5f\141\144\155\151\156\x5f\x63\150\x69\154\144\x5f\154\x6f\147\151\x6e"] : 0;
        $l6 = isset($QY["\145\156\141\x62\x6c\145\137\155\x61\156\x61\147\x65\x72\137\143\150\x69\154\144\137\154\x6f\147\x69\x6e"]) ? $QY["\x65\x6e\141\142\x6c\x65\137\x6d\x61\x6e\141\x67\145\x72\x5f\x63\150\x69\x6c\144\x5f\x6c\x6f\x67\x69\156"] : 0;
        if (!($uA || $wm || $Mh || $l6)) {
            goto OP;
        }
        $Mn = '';
        $Ox = '';
        if (!isset($Ru["\x73\x70\x5f\x62\x61\163\145\137\165\x72\x6c"])) {
            goto uk;
        }
        $Mn = $Ru["\x73\x70\137\142\141\163\x65\137\x75\x72\x6c"];
        $Ox = $Ru["\x73\x70\137\145\156\164\x69\x74\171\137\x69\144"];
        uk:
        $ei = JURI::root();
        if (!empty($Mn)) {
            goto Tn;
        }
        $Mn = $ei;
        Tn:
        if (!empty($Ox)) {
            goto rh;
        }
        $Ox = $ei . "\x70\x6c\165\x67\151\156\x73\57\x61\165\x74\x68\145\156\164\151\x63\x61\x74\x69\157\x6e\57\155\151\156\x69\x6f\162\x61\156\x67\145\163\x61\155\x6c";
        rh:
        jimport("\155\x69\156\x69\157\x72\x61\x6e\x67\145\x73\x61\155\154\160\x6c\x75\147\151\156\x2e\x75\x74\x69\x6c\151\164\171\56\x65\x6e\143\162\171\160\164\151\157\x6e");
        $eW = $_COOKIE["\x6d\x6f\x73\x61\x6d\x6c\x72\x65\x64\x69\x72\x65\143\164"];
        $ji = $QY["\x63\x75\x73\x74\157\155\145\x72\x5f\x74\x6f\x6b\145\x6e"];
        $BK = $QY["\141\160\151\x5f\153\145\x79"];
        $eW = AESEncryption::decrypt_data($eW, $ji);
        $gz = array();
        $gz = explode("\72", $eW);
        $ga = $gz[0];
        $sO = $gz[1];
        $L3 = $gz[2];
        $xi = time();
        if (!($sO == $BK && $xi - $ga < 30)) {
            goto d7;
        }
        setcookie("\x6d\157\x73\x61\155\154\x72\x65\144\x69\x72\145\x63\x74", "\x2d\x31", time() - 100, "\57");
        unset($_COOKIE["\x6d\157\163\141\155\154\162\145\x64\x69\x72\x65\143\x74"]);
        $xi = time();
        $eW = $xi . "\x3a" . $BK;
        $eW = AESEncryption::encrypt_data($eW, $ji);
        $fp = $xi + 30;
        $Nw = setcookie("\x6d\x6f\x73\x61\x6d\154\141\165\164\150\141\x64\155\x69\x6e", $eW, $fp, "\x2f");
        $kp = $Mn . "\x61\144\155\151\x6e\151\163\x74\162\x61\164\x6f\x72\x2f\151\156\144\x65\170\x2e\x70\150\160";
        $eW = $_COOKIE["\x5f\x6d\157\163\145\163\163\x69\x6f\156"];
        unset($_COOKIE["\x5f\x6d\x6f\x73\x65\163\x73\x69\157\x6e"]);
        $eW = AESEncryption::decrypt_data($eW, $ji);
        $cb = array();
        $cb = explode("\72", $eW);
        $HF = $cb[0];
        $cf = $cb[1];
        $J9 = urldecode($cb[2]);
        $KK = JFactory::getSession();
        $KK->set("\x4d\117\137\x53\101\115\x4c\137\116\101\115\105\x49\x44", $cf);
        $KK->set("\x4d\117\x5f\x53\101\x4d\114\x5f\114\117\107\107\x45\104\137\x49\116\137\127\x49\x54\x48\137\111\x44\120", TRUE);
        $KK->set("\x4d\x4f\x5f\x53\101\x4d\114\137\111\x44\x50\x5f\125\123\105\x44", $J9);
        $KK->set("\x75\x73\145\x72\156\141\155\x65", $L3);
        echo "\74\146\x6f\x72\155\40\151\x64\75\x22\x6d\x6f\163\141\x6d\x6c\x5f\x61\x64\x6d\x69\156\154\x6f\x67\x69\156\146\x6f\x72\155\x22\40\141\x63\164\151\157\156\x3d\42" . $kp . "\x22\x20\x6d\145\164\x68\x6f\x64\x3d\42\x70\x6f\x73\x74\42\x3e\xd\xa\11\x9\x9\11\x9\74\151\156\x70\x75\164\x20\x74\x79\x70\145\x3d\42\150\x69\144\144\145\x6e\x22\x20\156\141\x6d\x65\75\42\x75\x73\x65\162\x6e\141\x6d\145\x22\40\166\x61\x6c\165\145\x3d\x22" . $L3 . "\x22\40\57\76\15\12\x9\11\x9\11\x9\x3c\151\156\x70\x75\164\x20\x74\x79\x70\x65\75\x22\x68\x69\144\x64\x65\156\42\40\156\x61\155\145\x3d\42\160\141\x73\x73\167\x64\x22\40\166\141\x6c\165\145\x3d\42\x70\x61\x73\163\167\x64\x22\40\57\x3e\xd\xa\x9\x9\x9\11\11\x3c\151\x6e\160\165\164\x20\164\171\x70\145\x3d\42\150\x69\x64\144\145\x6e\x22\x20\156\x61\155\145\75\x22\157\160\x74\151\157\156\42\40\x76\x61\154\165\145\75\x22\x63\157\x6d\137\x6c\157\147\x69\156\x22\76\15\12\11\x9\11\11\11\74\x69\x6e\160\x75\164\x20\x74\x79\x70\145\x3d\x22\150\x69\144\x64\x65\x6e\x22\40\156\141\x6d\145\x3d\42\x74\141\163\153\42\x20\166\141\154\x75\145\75\x22\x6c\157\x67\151\156\x22\x3e\xd\12\x9\11\x9\x9\x9\74\x69\156\160\x75\164\x20\x74\171\160\x65\75\x22\x68\151\x64\x64\145\x6e\42\40\156\x61\155\145\75\42\x72\145\164\x75\x72\156\42\40\x76\141\x6c\165\145\75\42\141\x57\65\153\x5a\x58\147\x75\x63\107\150\167\x22\x3e" . JHtml::_("\146\157\162\155\x2e\164\x6f\153\x65\x6e") . "\x3c\57\146\x6f\x72\x6d\x3e\xd\xa\x9\x9\11\x9\74\x73\x63\162\151\x70\x74\x3e\xd\xa\x9\11\11\x9\x9\163\145\164\x54\151\155\x65\x6f\x75\x74\x28\146\x75\156\x63\164\x69\157\x6e\x28\x29\173\15\xa\x9\x9\11\11\x9\11\x64\x6f\x63\x75\x6d\x65\x6e\x74\56\147\145\x74\x45\x6c\145\x6d\145\156\164\x42\x79\x49\x64\x28\x22\155\x6f\x73\x61\155\154\x5f\141\144\155\x69\156\x6c\x6f\147\x69\x6e\x66\157\x72\155\x22\x29\x2e\163\x75\x62\x6d\151\x74\50\x29\x3b\15\12\11\11\x9\x9\11\175\54\x20\61\x30\x30\x29\x3b\x9\15\12\11\11\x9\11\x3c\x2f\x73\143\162\151\160\x74\76";
        exit;
        d7:
        OP:
    }
}
