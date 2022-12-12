<?php


defined("\137\112\105\130\105\x43") or die("\x52\x65\163\x74\x72\151\x63\164\x65\144\x20\x61\x63\143\145\163\163");
jimport("\x6a\x6f\x6f\x6d\x6c\141\x2e\160\x6c\165\147\151\x6e\56\x70\x6c\165\x67\151\x6e");
class plgSystemSamlredirect extends JPlugin
{
    function onAfterRender()
    {
        $Ny = JFactory::getApplication();
        $JU = $Ny->getBody();
        $VD = JURI::root();
        $JE = JFactory::getApplication()->input->get->getArray();
        $ZK = UtilitiesSAML::getExpiryDate();
        $yh = isset($ZK["\x6c\x69\143\145\x6e\x73\145\105\170\x70\151\x72\x79"]) ? date("\x46\40\152\x2c\x20\131\54\40\x67\72\151\40\x61", strtotime($ZK["\154\151\143\x65\x6e\x73\145\105\x78\160\x69\x72\x79"])) : '';
        $ME = UtilitiesSAML::checkIsLicenseExpired();
        $KJ = $ME["\x4c\151\143\145\156\x73\x65\105\170\x70\151\x72\x65\x64"] == 1 ? UtilitiesSAML::showLicenseExpiryMessage($ME) : 0;
        $Qm = $ME["\114\151\x63\x65\x6e\163\145\x45\170\x70\x69\x72\x79"] == 1 ? UtilitiesSAML::showLicenseExpiryMessage($ME) : 0;
        $n3 = UtilitiesSAML::getJoomlaCmsVersion();
        $n3 = substr($n3, 0, 3);
        $JE = JFactory::getApplication()->input->get->getArray();
        if (!($KJ || $Qm)) {
            goto ba;
        }
        if (!(stristr($JU, "\143\x6f\156\x74\x65\x6e\164") && !isset($JE["\x6f\160\x74\151\x6f\156"]))) {
            goto xM;
        }
        $eV = UtilitiesSAML::renewalMessage($ME, $yh, "\150\157\x6d\145");
        $PA = "\x3c\x64\x69\x76\40\143\x6c\141\x73\163\75\x22\143\x6f\x6e\164\141\x69\156\x65\x72\x2d\146\154\165\151\x64\40\143\x6f\156\164\x61\151\156\145\x72\x2d\155\x61\x69\156\x22\76" . $eV;
        $JU = str_replace("\74\x64\151\x76\40\x63\154\141\x73\163\75\x22\x63\157\156\164\141\151\156\145\162\55\146\154\x75\151\x64\x20\143\157\x6e\x74\141\x69\156\145\162\x2d\155\141\151\156\x22\x3e", $PA, $JU);
        $Ny->setBody($JU);
        xM:
        if (!(stristr($JU, "\x74\x6f\x6f\x6c\x62\x61\x72") && stristr($JU, "\152\x6f\157\155\154\x61\x2d\x74\157\157\154\142\141\x72\x2d\x62\165\164\x74\157\156"))) {
            goto C7;
        }
        $eV = UtilitiesSAML::renewalMessage($ME, $yh, "\x70\x6c\x75\147\151\x6e");
        $J_ = "\40\x3c\152\157\157\x6d\154\x61\55\x74\x6f\x6f\154\142\x61\x72\x2d\142\165\164\164\157\156\x20\x63\x6c\x61\x73\163\75\42\x6d\163\55\141\x75\x74\x6f\x22\76" . $eV;
        $JU = str_replace("\74\x6a\x6f\157\155\154\141\x2d\x74\x6f\157\x6c\142\141\162\55\142\x75\164\x74\157\156\x20\143\154\141\163\163\x3d\x22\x6d\163\x2d\x61\x75\164\x6f\x22\x3e", $J_, $JU);
        $Ny->setBody($JU);
        C7:
        ba:
    }
    public function onAfterInitialise()
    {
        jimport("\155\x69\156\151\157\162\141\156\147\x65\x73\x61\155\154\160\154\x75\147\151\x6e\56\165\x74\x69\154\151\x74\171\56\125\164\x69\x6c\x69\x74\151\145\x73\x53\x41\115\x4c");
        require_once JPATH_ROOT . DIRECTORY_SEPARATOR . "\141\x64\155\x69\156\x69\163\x74\x72\x61\164\157\162" . DIRECTORY_SEPARATOR . "\x63\x6f\155\x70\157\156\x65\156\164\163" . DIRECTORY_SEPARATOR . "\143\157\x6d\x5f\x6d\151\x6e\151\157\162\x61\x6e\147\x65\137\x73\141\155\x6c" . DIRECTORY_SEPARATOR . "\150\x65\154\x70\145\x72\x73" . DIRECTORY_SEPARATOR . "\x6d\157\x2d\163\141\155\154\55\165\164\x69\x6c\151\164\171\x2e\160\150\160";
        $post = JFactory::getApplication()->input->post->getArray();
        $JE = JFactory::getApplication()->input->get->getArray();
        $rj = JFactory::getApplication()->input->request->getArray();
        $RT = UtilitiesSAML::getSAMLConfiguration();
        $ME = UtilitiesSAML::checkIsLicenseExpired();
        $fv = UtilitiesSAML::getCustomerDetails();
        if (!$ME["\x4c\151\143\x65\156\163\145\x45\x78\160\151\x72\x79"]) {
            goto FT;
        }
        UtilitiesSAML::showLicenseExpiryMessage($ME);
        FT:
        if (!($ME["\x4c\x69\143\145\156\x73\x65\x54\x72\x69\x61\154\x45\x78\160\x69\162\171"] && !empty($fv["\x73\x6d\154\137\154\153"]))) {
            goto oc;
        }
        UtilitiesSAML::fetchTLicense();
        oc:
        $zN = isset($post["\x6d\157\x5f\x72\145\155\157\x76\x65\137\166\x61\x6c\x75\x65\x5f\144\157\x6d\141\151\x6e"]) ? $post["\155\157\137\162\145\155\x6f\166\145\x5f\166\141\x6c\x75\x65\137\144\x6f\155\141\151\156"] : '';
        if (empty($fv["\163\x6d\154\137\154\x6b"])) {
            goto KX;
        }
        $lo = $fv["\164\x72\x69\163\x74\163"];
        $lo = Mo_saml_Local_Util::decrypt_value($lo);
        if (!($lo == true)) {
            goto zS;
        }
        $ME = UtilitiesSAML::gt_lk_trl();
        $Qa = 0;
        if (!$ME["\x4c\x69\x63\x65\156\x73\x65\x45\x78\x70\151\x72\x65\x64"]) {
            goto Qh;
        }
        UtilitiesSAML::rmvextnsns();
        $jA = UtilitiesSAML::get_message_and_cause($ME["\114\151\x63\x65\156\163\x65\105\170\x70\151\162\x65\144"], $Qa);
        UtilitiesSAML::show_error_messages($jA["\x6d\163\147"], $jA["\x63\x61\165\163\145"]);
        Qh:
        zS:
        KX:
        if (!(!empty($zN) || '' != $zN)) {
            goto kW;
        }
        if (!($zN == "\155\157\x5f\162\145\155\x6f\166\145\x5f\144\x6f\155\141\x69\156\137\155\141\160")) {
            goto A8;
        }
        $RM = isset($post["\162\x65\155\157\166\x65\x5f\x76\141\x6c\x75\x65\137\144\x6f\x6d\141\x69\x6e\x5f\155\141\160\x70"]) ? $post["\x72\145\x6d\157\166\145\x5f\x76\x61\x6c\x75\145\x5f\144\x6f\x6d\x61\x69\156\137\x6d\141\x70\x70"] : '';
        UtilitiesSAML::_remove_domain_mapp($RM);
        A8:
        kW:
        $nP = '';
        if (!(isset($JE["\x69\x64\x70"]) && !empty($JE["\151\144\x70"]))) {
            goto ar;
        }
        $nP = $JE["\x69\144\160"];
        ar:
        if (isset($JE["\155\157\x72\x65\161\165\x65\x73\x74"]) && $JE["\x6d\157\162\x65\161\165\145\x73\164"] == "\163\163\x6f") {
            goto cV;
        }
        if (isset($JE["\155\157\162\145\161\165\145\163\x74"]) && $JE["\x6d\x6f\162\145\161\x75\145\x73\x74"] == "\x61\x63\x73") {
            goto PD;
        }
        if (isset($JE["\x6d\157\162\x65\x71\x75\145\163\x74"]) && $JE["\x6d\x6f\162\145\161\165\145\x73\x74"] == "\x6d\x65\x74\141\144\141\164\x61") {
            goto Jx;
        }
        if (isset($JE["\x6d\x6f\x72\x65\161\165\145\163\x74"]) && $JE["\155\157\x72\x65\x71\165\x65\x73\164"] == "\x64\157\x77\156\154\x6f\141\x64\137\x6d\145\x74\x61\x64\x61\164\141") {
            goto WY;
        }
        if (!(array_key_exists("\x53\x41\x4d\x4c\122\x65\163\160\157\x6e\x73\x65", $rj) && !empty($rj["\123\101\x4d\114\122\x65\x73\160\157\156\x73\145"]))) {
            goto K5;
        }
        $id = $rj["\x53\101\115\114\x52\145\x73\x70\157\x6e\x73\x65"];
        $id = base64_decode($id);
        if (!(array_key_exists("\x53\x41\x4d\114\122\145\x73\160\157\x6e\163\145", $JE) && !empty($JE["\x53\101\115\114\x52\145\163\x70\x6f\x6e\x73\145"]))) {
            goto Mb;
        }
        $id = gzinflate($id);
        Mb:
        $this->getSamlLogoutResponse($id);
        K5:
        goto rK;
        WY:
        $this->generateMetadata($RT, $H3 = true);
        rK:
        goto zu;
        Jx:
        $this->generateMetadata($RT);
        zu:
        goto cO;
        PD:
        $this->getSamlResponse();
        cO:
        goto tn;
        cV:
        $gd = UtilitiesSAML::getSAMLConfiguration($nP);
        $this->sendSamlRequest($fv, $gd[0]);
        tn:
        if (!(isset($_COOKIE["\x6d\x6f\163\141\155\154\x72\145\144\x69\x72\x65\143\164"]) && $_COOKIE["\155\x6f\x73\141\x6d\x6c\x72\x65\144\151\162\x65\143\164"] != "\55\x31")) {
            goto EH;
        }
        $this->adminDashboardLogin($RT);
        EH:
        $Uh = isset($fv["\145\x6e\141\x62\154\145\x5f\x6d\x61\156\141\x67\145\x72\137\x6c\157\147\151\x6e"]) ? $fv["\x65\156\141\x62\x6c\145\137\x6d\x61\x6e\141\147\145\x72\x5f\x6c\x6f\147\x69\156"] : 0;
        $Rd = isset($fv["\x65\156\x61\x62\x6c\145\x5f\141\x64\155\151\156\x5f\x72\x65\144\x69\162\x65\143\164"]) ? $fv["\145\156\141\142\x6c\x65\137\141\x64\155\151\156\137\x72\x65\144\151\162\x65\143\x74"] : 0;
        $pd = isset($fv["\x65\x6e\141\x62\154\145\x5f\x72\145\x64\151\162\x65\x63\x74"]) ? $fv["\x65\x6e\141\142\x6c\x65\x5f\162\x65\144\151\x72\x65\x63\x74"] : 0;
        if (!($Uh || $Rd || $pd)) {
            goto MH;
        }
        self::autoRedirectIoIDP();
        MH:
    }
    function isLoginRportAddonEnable()
    {
        $wk = false;
        if (!file_exists(JPATH_PLUGINS . "\x2f\x75\x73\145\162\57\155\x69\156\x69\157\162\141\156\147\x65\x6c\157\x67\x69\156\x72\145\x70\x6f\162\164\x2f\x6d\x69\x6e\151\x6f\162\141\x6e\x67\x65\x6c\157\147\x69\156\162\145\x70\x6f\162\x74\56\x70\150\160")) {
            goto Ah;
        }
        require_once JPATH_PLUGINS . "\x2f\x75\163\145\x72\57\155\x69\x6e\151\x6f\x72\x61\x6e\x67\x65\x6c\x6f\x67\151\x6e\162\x65\x70\157\x72\x74\57\155\151\x6e\x69\157\x72\x61\x6e\147\x65\154\x6f\147\x69\x6e\162\145\x70\157\x72\164\56\x70\x68\x70";
        $wk = plgUserMiniorangeloginreport::loginreport_addon();
        Ah:
        return $wk;
    }
    function autoRedirectIoIDP()
    {
        $fv = UtilitiesSAML::getCustomerDetails();
        $user = JFactory::getUser();
        $Ny = JFactory::getApplication("\163\151\164\145");
        $n3 = UtilitiesSAML::getJoomlaCmsVersion();
        $n3 = substr($n3, 0, 3);
        $JE = JFactory::getApplication()->input->get->getArray();
        if (!($n3 < 4.0)) {
            goto KO;
        }
        $Ny->initialise();
        KO:
        if ($user->id == 0 && !isset($_COOKIE["\162\x65\161\165\x65\x73\164\x5f\165\x72\x69\x5f\162\x65\154\x61\171\137\x73\x74\x61\x74\145"])) {
            goto iT;
        }
        if (!isset($_COOKIE["\x72\145\x71\x75\x65\x73\x74\137\165\x72\151\137\162\x65\154\141\x79\x5f\163\164\x61\x74\x65"])) {
            goto iD;
        }
        unset($_COOKIE["\x72\x65\x71\x75\x65\163\x74\x5f\165\x72\x69\137\162\x65\x6c\141\x79\x5f\x73\164\x61\x74\x65"]);
        iD:
        goto qE;
        iT:
        $R5 = !empty($_SERVER["\x48\124\x54\120\x53"]) && $_SERVER["\x48\124\x54\120\123"] !== "\x6f\146\x66" || $_SERVER["\x53\x45\x52\126\x45\x52\137\120\x4f\x52\124"] == 443 ? "\x68\x74\x74\x70\x73\x3a\57\x2f" : "\150\164\164\160\x3a\57\57";
        $zQ = $R5 . "{$_SERVER["\110\124\x54\x50\137\x48\x4f\123\x54"]}{$_SERVER["\x52\105\121\125\105\x53\124\x5f\x55\x52\x49"]}";
        $Kw = $fv["\151\x64\160\x5f\x6c\x69\156\x6b\x5f\x70\141\x67\x65"];
        $mS = $fv["\145\x6e\x61\142\154\x65\137\x61\144\x6d\x69\156\x5f\162\145\144\x69\162\x65\x63\164"];
        $Fg = $fv["\145\x6e\141\x62\x6c\145\137\162\x65\144\x69\162\x65\143\x74"];
        $A6 = $fv["\145\x6e\x61\x62\x6c\145\137\x6d\141\x6e\x61\x67\x65\162\137\154\157\x67\x69\156"];
        $Qt = $fv["\x6d\x6f\137\141\144\x6d\151\x6e\137\x69\144\160\x5f\154\x69\x73\x74\x5f\x6c\151\x6e\x6b\137\160\x61\x67\145"];
        $mO = "\162\145\x71\165\145\163\164\137\x75\162\151\x5f\162\145\x6c\141\171\x5f\163\x74\141\x74\x65";
        $Cz = $zQ;
        setcookie($mO, $Cz, time() + 30, "\x2f");
        if ($zQ != $Kw && !strpos($zQ, "\x61\144\155\151\x6e\151\163\164\162\141\x74\157\162") && $Fg == 1) {
            goto x9;
        }
        if (!($zQ != $Kw && ($mS == 1 || $A6 == 1) && strpos($zQ, "\x61\x64\x6d\151\x6e\x69\163\x74\162\x61\164\157\x72"))) {
            goto Bb;
        }
        if (isset($JE["\x6d\x6f\x70\x61\163\163\141\144\155\151\156\163\x73\157"]) && $JE["\x6d\157\x70\x61\163\163\x61\x64\155\x69\156\x73\x73\x6f"] == "\164\x72\x75\145") {
            goto Rd;
        }
        header("\114\x6f\143\141\x74\x69\157\x6e\72\40" . $Qt);
        exit;
        goto kg;
        Rd:
        header("\114\x6f\143\x61\164\151\157\156\72\x20" . $zQ);
        exit;
        kg:
        Bb:
        goto J7;
        x9:
        header("\x4c\x6f\x63\141\x74\151\157\x6e\72\40" . $Kw);
        exit;
        J7:
        qE:
    }
    function getSamlLogoutResponse($id)
    {
        $rj = JFactory::getApplication()->input->request->getArray();
        $qm = new DOMDocument();
        $id = str_replace("\x26", "\46\141\155\160\73", $id);
        $qm->loadXML($id);
        $mf = $qm->firstChild;
        if (!($mf->localName == "\x4c\157\x67\x6f\x75\x74\x52\145\163\160\x6f\156\163\x65")) {
            goto gI;
        }
        $Ny = JFactory::getApplication("\163\x69\x74\x65");
        $VD = JURI::root();
        if (!isset($rj["\x52\145\154\x61\x79\123\x74\141\x74\145"])) {
            goto Yo;
        }
        $VD = $rj["\122\145\154\141\x79\123\x74\x61\164\x65"];
        Yo:
        $f7 = strpos($VD, "\x3f");
        if (!($f7 !== false)) {
            goto M1;
        }
        $VD = substr($VD, 0, $f7);
        M1:
        $Ny->redirect($VD);
        gI:
    }
    function sendSamlRequest($Ye, $gd)
    {
        $Ol = '';
        $FM = '';
        if (!isset($Ye["\163\x70\x5f\x62\x61\x73\x65\137\165\x72\154"])) {
            goto Dh;
        }
        $Ol = $Ye["\163\160\x5f\x62\141\x73\x65\137\165\162\154"];
        $FM = $Ye["\163\x70\x5f\x65\156\164\151\164\171\137\151\x64"];
        Dh:
        $gc = JURI::root();
        if (!empty($Ol)) {
            goto Z4;
        }
        $Ol = $gc;
        Z4:
        if (!empty($FM)) {
            goto oI;
        }
        $FM = $gc . "\x70\x6c\165\x67\151\x6e\163\x2f\x61\x75\x74\x68\x65\156\x74\x69\x63\141\x74\151\x6f\156\57\155\151\x6e\151\157\162\x61\156\147\x65\163\141\x6d\154";
        oI:
        if (defined("\137\x4a\x44\105\106\111\116\105\123")) {
            goto Gw;
        }
        require_once JPATH_BASE . "\x2f\x69\156\143\x6c\x75\144\x65\x73\57\x64\x65\146\151\156\145\x73\x2e\x70\150\x70";
        Gw:
        require_once JPATH_BASE . "\x2f\151\156\143\154\165\144\x65\x73\57\146\x72\141\x6d\145\167\157\x72\153\56\x70\150\160";
        $Ny = JFactory::getApplication("\x73\x69\164\x65");
        $n3 = UtilitiesSAML::getJoomlaCmsVersion();
        $n3 = substr($n3, 0, 3);
        if (!($n3 < 4.0)) {
            goto rS;
        }
        $Ny->initialise();
        rS:
        $lM = $Ol;
        $user = JFactory::getUser();
        $AG = $Ol . "\x3f\x6d\x6f\x72\x65\x71\165\145\163\164\75\x61\143\163";
        $yB = $gd["\x73\x69\156\147\154\x65\137\x73\x69\x67\156\157\156\137\163\145\x72\x76\x69\x63\145\x5f\x75\162\x6c"];
        $w8 = $gd["\x62\x69\156\144\x69\x6e\x67"];
        $vB = $gd["\155\x6f\x5f\x73\141\155\x6c\137\163\x65\x6c\x65\143\x74\137\x73\x69\x67\x6e\x5f\x61\154\147\157"];
        $cq = $gd["\163\x61\x6d\x6c\137\x72\x65\161\165\145\x73\x74\137\x73\151\x67\156"];
        $if = $gd["\156\141\155\x65\x5f\x69\x64\137\146\157\162\155\141\164"];
        $rj = JFactory::getApplication()->input->request->getArray();
        $qT = $this->getRelayState($Ol, $rj);
        $AH = UtilitiesSAML::createAuthnRequest($AG, $FM, $yB, $if, "\x66\141\x6c\x73\x65", $w8);
        $this->sendSamlRequestByBindingType($AH, $w8, $qT, $yB, $vB, $cq);
    }
    function sendSamlRequestByBindingType($AH, $w8, $qT, $yB, $vB, $cq)
    {
        $t7 = UtilitiesSAML::getSAMLConfiguration();
        if (empty($w8) || $w8 == "\110\x54\x54\x50\55\x52\x65\x64\151\162\x65\x63\164") {
            goto Hi;
        }
        $vt = UtilitiesSAML::get_public_private_certificate($t7, "\160\x72\x69\166\141\164\145\x5f\x63\x65\x72\x74\x69\x66\x69\x63\141\164\145");
        $Yn = UtilitiesSAML::get_public_private_certificate($t7, "\x70\x75\142\x6c\x69\143\137\143\145\162\164\x69\146\x69\143\141\164\145");
        if ($vt == null || $vt == '') {
            goto kO;
        }
        $hT = JPATH_BASE . DIRECTORY_SEPARATOR . "\x70\154\165\x67\151\x6e\163" . DIRECTORY_SEPARATOR . "\x61\165\164\x68\x65\156\164\151\x63\x61\164\151\157\x6e" . DIRECTORY_SEPARATOR . "\155\151\156\151\x6f\162\x61\156\x67\x65\x73\141\x6d\154" . DIRECTORY_SEPARATOR . "\163\x61\155\x6c\62" . DIRECTORY_SEPARATOR . "\143\x65\162\164" . DIRECTORY_SEPARATOR . "\x43\x75\163\164\157\x6d\120\x72\151\x76\x61\x74\x65\103\145\x72\164\x69\x66\151\143\141\x74\x65\x2e\x6b\x65\x79";
        goto vx;
        kO:
        $hT = JPATH_BASE . DIRECTORY_SEPARATOR . "\160\154\165\147\151\x6e\x73" . DIRECTORY_SEPARATOR . "\x61\165\164\150\x65\x6e\x74\151\143\x61\x74\x69\157\x6e" . DIRECTORY_SEPARATOR . "\155\x69\x6e\x69\157\x72\x61\156\147\x65\x73\141\155\x6c" . DIRECTORY_SEPARATOR . "\x73\141\x6d\154\x32" . DIRECTORY_SEPARATOR . "\x63\x65\162\164" . DIRECTORY_SEPARATOR . "\x73\160\x2d\153\x65\171\x2e\153\145\171";
        vx:
        if ($Yn == null || $Yn == '') {
            goto qQ;
        }
        $O0 = JPATH_BASE . DIRECTORY_SEPARATOR . "\160\x6c\x75\x67\151\x6e\163" . DIRECTORY_SEPARATOR . "\141\x75\164\x68\145\x6e\164\x69\143\141\x74\151\x6f\156" . DIRECTORY_SEPARATOR . "\155\151\156\151\x6f\x72\x61\156\x67\x65\x73\x61\155\x6c" . DIRECTORY_SEPARATOR . "\163\x61\x6d\154\62" . DIRECTORY_SEPARATOR . "\143\145\162\x74" . DIRECTORY_SEPARATOR . "\103\165\x73\x74\x6f\x6d\x50\165\142\x6c\x69\143\x43\x65\162\164\151\x66\151\143\x61\x74\145\x2e\143\162\164";
        goto Fv;
        qQ:
        $O0 = JPATH_BASE . DIRECTORY_SEPARATOR . "\x70\154\165\147\151\156\163" . DIRECTORY_SEPARATOR . "\x61\x75\x74\150\145\156\164\x69\x63\x61\164\x69\157\x6e" . DIRECTORY_SEPARATOR . "\x6d\x69\x6e\151\157\162\141\156\x67\145\x73\x61\x6d\x6c" . DIRECTORY_SEPARATOR . "\x73\x61\x6d\x6c\62" . DIRECTORY_SEPARATOR . "\x63\145\162\164" . DIRECTORY_SEPARATOR . "\163\160\55\143\x65\162\x74\151\x66\151\x63\141\x74\x65\x2e\143\x72\164";
        Fv:
        $wt = UtilitiesSAML::signXML($AH, $O0, $hT, $vB, "\116\141\155\x65\x49\104\x50\x6f\x6c\151\143\x79");
        UtilitiesSAML::postSAMLRequest($yB, $wt, $qT);
        goto ZP;
        Hi:
        $wC = $yB;
        if (strpos($yB, "\77") !== false) {
            goto b3;
        }
        $wC .= "\x3f";
        goto b2;
        b3:
        $wC .= "\x26";
        b2:
        if (!($cq !== "\163\x69\147\156\x65\144")) {
            goto mb;
        }
        $wC .= "\x53\x41\115\x4c\122\145\x71\x75\145\x73\164\75" . $AH . "\x26\x52\x65\154\141\171\123\164\141\164\145\75" . urlencode($qT);
        header("\114\157\x63\x61\164\x69\157\156\x3a\40" . $wC);
        exit;
        mb:
        if ($vB == "\x52\x53\101\x5f\x53\110\101\x32\65\66") {
            goto wh;
        }
        if ($vB == "\122\x53\x41\x5f\x53\110\101\x33\70\x34") {
            goto Z_;
        }
        if ($vB == "\122\x53\x41\x5f\x53\110\101\x35\x31\62") {
            goto Sh;
        }
        $AH = "\123\x41\x4d\x4c\122\x65\161\x75\x65\x73\164\75" . $AH . "\x26\122\145\154\x61\x79\123\x74\x61\164\x65\x3d" . urlencode($qT) . "\x26\123\151\147\101\154\x67\75" . urlencode(XMLSecurityKeySAML::RSA_SHA1);
        goto Q3;
        wh:
        $AH = "\x53\x41\115\114\122\x65\161\x75\145\163\x74\75" . $AH . "\x26\x52\x65\154\x61\171\123\164\x61\164\145\75" . urlencode($qT) . "\x26\x53\151\147\101\x6c\147\75" . urlencode(XMLSecurityKeySAML::RSA_SHA256);
        goto Q3;
        Z_:
        $AH = "\x53\x41\x4d\114\x52\x65\x71\165\145\x73\x74\x3d" . $AH . "\x26\122\x65\154\x61\x79\x53\x74\x61\164\145\x3d" . urlencode($qT) . "\x26\123\151\147\x41\154\147\x3d" . urlencode(XMLSecurityKeySAML::RSA_SHA384);
        goto Q3;
        Sh:
        $AH = "\123\101\115\114\122\x65\x71\x75\x65\163\164\x3d" . $AH . "\x26\x52\145\x6c\141\171\123\164\141\164\145\x3d" . urlencode($qT) . "\x26\x53\x69\147\x41\x6c\x67\75" . urlencode(XMLSecurityKeySAML::RSA_SHA512);
        Q3:
        $kJ = array("\164\x79\x70\x65" => "\x70\x72\x69\x76\x61\x74\x65");
        if ($vB == "\x52\x53\101\x5f\123\110\x41\x32\x35\66") {
            goto jk;
        }
        if ($vB == "\122\123\101\x5f\x53\x48\x41\63\70\64") {
            goto xw;
        }
        if ($vB == "\x52\x53\101\x5f\x53\110\x41\x35\x31\x32") {
            goto rn;
        }
        $l9 = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA1, $kJ);
        goto Jk;
        jk:
        $l9 = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA256, $kJ);
        goto Jk;
        xw:
        $l9 = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA384, $kJ);
        goto Jk;
        rn:
        $l9 = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA512, $kJ);
        Jk:
        $vt = UtilitiesSAML::get_public_private_certificate($t7, "\x70\162\151\166\141\164\145\137\x63\145\x72\x74\x69\146\151\143\x61\x74\x65");
        if ($vt == null || $vt == '' || empty($vt)) {
            goto Ax;
        }
        $N2 = JPATH_BASE . DIRECTORY_SEPARATOR . "\x70\154\165\x67\151\x6e\x73" . DIRECTORY_SEPARATOR . "\141\165\x74\x68\145\156\164\x69\143\x61\164\x69\157\x6e" . DIRECTORY_SEPARATOR . "\155\151\x6e\151\157\162\141\x6e\147\x65\x73\141\x6d\x6c" . DIRECTORY_SEPARATOR . "\x73\x61\155\154\x32" . DIRECTORY_SEPARATOR . "\143\x65\162\164" . DIRECTORY_SEPARATOR . "\x43\165\x73\164\157\155\120\162\151\166\x61\164\145\103\x65\x72\164\x69\146\x69\143\x61\x74\145\x2e\153\x65\x79";
        goto DG;
        Ax:
        $N2 = JPATH_BASE . DIRECTORY_SEPARATOR . "\x70\x6c\x75\x67\x69\156\163" . DIRECTORY_SEPARATOR . "\x61\x75\164\150\145\156\x74\x69\143\141\x74\151\x6f\x6e" . DIRECTORY_SEPARATOR . "\x6d\x69\x6e\151\x6f\x72\x61\156\147\x65\x73\x61\155\154" . DIRECTORY_SEPARATOR . "\163\141\155\154\62" . DIRECTORY_SEPARATOR . "\x63\x65\162\x74" . DIRECTORY_SEPARATOR . "\x73\x70\55\x6b\x65\x79\x2e\153\145\x79";
        DG:
        $l9->loadKey($N2, TRUE);
        $RD = new XMLSecurityDSigSAML();
        $sY = $l9->signData($AH);
        $sY = base64_encode($sY);
        $wC .= $AH . "\x26\123\x69\x67\x6e\x61\164\165\x72\x65\x3d" . urlencode($sY);
        header("\114\157\x63\x61\x74\151\157\x6e\x3a\x20" . $wC);
        exit;
        ZP:
    }
    function getRelayState($Ol, $rj)
    {
        $JE = JFactory::getApplication()->input->get->getArray();
        $nP = '';
        if (!(isset($JE["\x69\144\160"]) && !empty($JE["\x69\x64\160"]))) {
            goto Wi;
        }
        $nP = $JE["\151\x64\x70"];
        Wi:
        $gd = UtilitiesSAML::getSAMLConfiguration($nP)[0];
        $base_url = $_SERVER["\x48\124\x54\120\137\x48\x4f\123\124"];
        $qT = $Ol;
        if (isset($rj["\161"])) {
            goto rA;
        }
        if (isset($gd["\144\145\x66\x61\x75\154\x74\x5f\x72\145\154\x61\x79\137\163\x74\141\x74\x65"]) && $gd["\x64\145\146\141\x75\x6c\164\137\x72\145\154\x61\x79\137\163\164\x61\164\x65"] != '') {
            goto vZ;
        }
        $Xh = isset($_SERVER["\x48\x54\x54\x50\137\122\105\106\105\122\x45\122"]) ? $_SERVER["\110\124\x54\120\137\x52\x45\106\x45\122\105\x52"] : '';
        if (isset($rj["\x52\145\x6c\x61\x79\x53\164\x61\164\x65"])) {
            goto qr;
        }
        if (isset($rj["\155\x6f\x5f\x73\x73\x6f\x5f\157\x72\x69\147\x69\156"]) && trim($rj["\x6d\157\137\x73\x73\x6f\x5f\x6f\x72\151\x67\x69\x6e"]) != '') {
            goto qe;
        }
        if (isset($_COOKIE["\162\145\x71\x75\145\163\164\x5f\x75\x72\151\x5f\x72\145\x6c\141\171\x5f\x73\164\x61\x74\145"])) {
            goto o3;
        }
        if (!($Xh != '')) {
            goto gu;
        }
        $qT = $Xh;
        gu:
        goto Xd;
        o3:
        $qT = $_COOKIE["\162\145\161\x75\145\163\164\x5f\x75\x72\151\137\162\x65\x6c\141\171\x5f\163\x74\141\x74\145"];
        Xd:
        goto BY;
        qe:
        $R5 = !empty($_SERVER["\110\124\x54\x50\x53"]) && $_SERVER["\x48\x54\x54\120\x53"] !== "\157\x66\x66" || $_SERVER["\x53\x45\122\x56\105\122\x5f\120\x4f\122\124"] == 443 ? "\150\x74\164\x70\163\x3a\57\57" : "\150\x74\x74\160\72\x2f\x2f";
        $qT = $R5 . $base_url . $rj["\x6d\x6f\137\x73\163\157\x5f\157\x72\151\147\151\x6e"];
        BY:
        goto h2;
        qr:
        $qT = $rj["\x52\145\x6c\141\171\x53\164\x61\164\145"];
        h2:
        goto cb;
        vZ:
        $qT = $gd["\144\x65\x66\x61\165\x6c\x74\x5f\162\x65\154\141\x79\x5f\163\164\x61\164\x65"];
        cb:
        goto ut;
        rA:
        if (!($rj["\161"] == "\x74\145\163\x74\x5f\x63\157\156\146\x69\x67")) {
            goto a2;
        }
        $qT = "\x74\x65\x73\164\126\x61\x6c\x69\x64\x61\x74\x65";
        a2:
        ut:
        return $qT;
    }
    function getSamlResponse()
    {
        $Ta = UtilitiesSAML::getCustomerincmk_lk("\x69\156\137\x63\x6d\160");
        $ez = UtilitiesSAML::getCustomerincmk_lk("\163\x6d\x6c\137\154\x6b");
        foreach ($ez as $l9) {
            $PM = $l9;
            sJ:
        }
        O1:
        $aK = JURI::root() . $PM;
        $qh = UtilitiesSAML::getCustomerDetails();
        require_once JPATH_BASE . DIRECTORY_SEPARATOR . "\141\x64\155\x69\156\x69\x73\164\x72\x61\164\157\162" . DIRECTORY_SEPARATOR . "\143\x6f\155\x70\x6f\x6e\145\156\164\163" . DIRECTORY_SEPARATOR . "\143\157\x6d\137\x6d\x69\x6e\151\157\x72\x61\156\147\145\137\163\x61\155\154" . DIRECTORY_SEPARATOR . "\150\x65\x6c\160\145\x72\163" . DIRECTORY_SEPARATOR . "\155\x6f\55\x73\x61\x6d\x6c\x2d\165\x74\x69\x6c\151\164\171\x2e\x70\x68\x70";
        $aK = Mo_saml_Local_Util::encrypt($aK);
        $lo = $qh["\164\x72\x69\x73\164\x73"];
        foreach ($Ta as $l9) {
            if (!($aK === $l9) && $l9 != null && $l9 != '') {
                goto qd;
            }
            if (!($l9 == null || $l9 == '')) {
                goto qo;
            }
            echo "\x3c\144\151\x76\x20\x73\164\171\154\145\75\42\146\x6f\x6e\x74\x2d\x66\x61\x6d\x69\154\171\x3a\x43\141\x6c\x69\x62\x72\151\73\160\141\x64\144\151\156\147\72\x30\x20\x33\x25\x3b\x22\76";
            echo "\74\x64\151\166\40\163\164\x79\154\145\75\x22\x63\157\x6c\x6f\x72\72\40\43\141\71\64\64\64\62\73\x62\x61\x63\x6b\147\x72\157\165\x6e\x64\55\x63\157\x6c\x6f\x72\x3a\40\x23\x66\x32\x64\x65\x64\145\73\160\x61\x64\x64\151\156\147\x3a\40\x31\x35\160\170\73\x6d\x61\x72\147\x69\x6e\x2d\x62\x6f\164\x74\x6f\x6d\72\40\62\x30\x70\x78\73\x74\145\170\x74\55\141\154\151\x67\x6e\x3a\143\x65\x6e\164\145\x72\73\142\x6f\x72\144\x65\162\72\61\x70\170\40\x73\x6f\154\x69\144\x20\43\x45\x36\102\63\102\62\x3b\x66\157\156\164\55\163\x69\172\x65\72\x31\70\x70\x74\73\42\x3e\x20\105\x52\x52\x4f\122\x3c\x2f\144\151\x76\x3e\12\x20\40\x20\x20\x20\x20\40\x20\x20\40\x20\40\x20\40\x20\x20\40\x20\x20\x20\x20\40\x20\x20\74\144\x69\x76\40\x73\x74\171\154\x65\75\x22\143\x6f\x6c\x6f\162\72\40\43\x61\x39\64\x34\64\x32\x3b\146\x6f\x6e\x74\x2d\163\x69\x7a\145\72\x31\x34\x70\164\x3b\x20\155\x61\162\x67\151\x6e\55\142\157\x74\164\157\155\72\62\60\x70\x78\x3b\x22\76\x3c\160\x3e\x3c\x73\164\x72\157\156\x67\x3e\x45\162\162\x6f\162\72\40\x3c\57\x73\x74\x72\157\x6e\x67\76\131\x6f\x75\40\141\162\x65\x20\156\157\x74\x20\154\x6f\x67\x67\x65\144\x20\x69\156\74\x2f\x70\76\12\40\40\40\40\40\x20\x20\x20\40\40\x20\x20\x20\40\40\40\x20\40\40\40\x20\40\x20\40\x20\x20\40\40\74\160\76\x50\154\145\x61\x73\145\x20\141\x63\x74\x69\x76\141\164\145\x20\x79\x6f\x75\162\x20\x6c\151\x63\145\x6e\x73\x65\x20\153\x65\171\x20\146\151\162\163\x74\40\164\157\40\141\143\x74\151\x76\141\164\145\x20\163\151\156\147\154\x65\x20\163\151\147\x6e\x20\157\156\x2e\74\x2f\x70\x3e\12\x20\40\x20\x20\x20\x20\x20\40\40\x20\x20\x20\x20\40\x20\x20\x20\40\x20\40\x20\x20\x20\40\40\x20\40\x20\x3c\160\x3e\x3c\x73\164\x72\157\156\147\76\120\x6f\163\x73\151\142\x6c\x65\x20\103\x61\x75\163\x65\x3a\40\74\x2f\x73\164\162\x6f\x6e\x67\x3e\115\x61\153\145\x20\163\165\x72\x65\40\x79\157\x75\x20\150\x61\166\145\x20\x61\143\164\x69\x76\x61\x74\x65\x20\171\157\165\162\x20\154\x69\x63\x65\156\x73\x65\x20\x6b\145\171\40\x69\156\40\x74\157\x20\160\154\165\147\x69\156\74\x2f\x70\x3e\xa\x20\x20\x20\x20\x20\x20\x20\x20\x20\40\x20\x20\x20\40\x20\40\40\40\40\40\x20\40\40\x20\x3c\x2f\x64\x69\166\76\xa\40\x20\x20\x20\40\40\x20\40\x20\40\40\40\x20\x20\x20\x20\x20\x20\40\x20\x3c\144\x69\166\x20\163\x74\171\154\x65\x3d\42\155\141\x72\x67\151\156\x3a\63\x25\73\x64\x69\163\160\154\x61\171\x3a\x62\x6c\x6f\x63\153\73\x74\145\170\x74\x2d\x61\154\151\x67\156\x3a\x63\145\x6e\164\x65\162\73\42\76";
            $VD = JURI::root();
            echo "\x3c\144\x69\x76\40\163\164\171\x6c\145\75\x22\155\x61\x72\x67\x69\x6e\72\x33\x25\x3b\x64\x69\x73\x70\x6c\x61\171\x3a\x62\154\157\x63\x6b\x3b\164\x65\x78\164\55\141\154\151\x67\x6e\x3a\x63\x65\156\164\x65\x72\x3b\42\76\x3c\141\x20\x68\162\145\x66\x3d\x22";
            echo $VD;
            echo "\40\x22\x3e\x3c\151\x6e\160\x75\164\x20\163\164\x79\x6c\145\x3d\x22\x70\x61\144\144\151\156\x67\x3a\x31\x25\x3b\167\151\x64\x74\x68\x3a\x31\x30\x30\160\170\73\142\x61\x63\x6b\147\162\157\x75\x6e\144\72\x20\x23\60\60\71\61\x43\104\x20\156\157\156\145\40\x72\145\x70\145\x61\x74\40\x73\143\x72\x6f\x6c\x6c\40\x30\x25\x20\60\45\73\143\x75\162\163\157\162\x3a\x20\x70\x6f\151\x6e\164\145\x72\x3b\146\157\x6e\164\x2d\163\151\x7a\145\x3a\x31\65\x70\x78\x3b\x62\157\162\144\145\162\55\x77\x69\144\164\150\72\40\61\160\170\73\142\157\162\x64\x65\x72\55\163\x74\171\x6c\145\x3a\x20\163\157\x6c\x69\144\x3b\142\x6f\x72\x64\145\x72\x2d\162\x61\x64\x69\165\x73\72\x20\x33\x70\x78\73\167\150\x69\164\x65\55\x73\x70\x61\x63\145\x3a\x20\x6e\x6f\x77\x72\141\x70\x3b\x62\x6f\170\x2d\163\x69\x7a\x69\x6e\147\72\40\x62\x6f\162\144\145\x72\55\x62\157\170\73\142\x6f\162\x64\x65\x72\x2d\143\x6f\154\157\x72\72\40\x23\60\60\67\63\101\x41\73\142\x6f\170\55\163\x68\141\x64\x6f\167\x3a\x20\x30\160\x78\x20\61\x70\x78\40\60\x70\170\40\162\147\142\x61\x28\61\x32\x30\x2c\x20\x32\x30\x30\54\40\x32\x33\60\x2c\40\x30\56\66\x29\40\151\156\x73\x65\x74\x3b\x63\157\154\x6f\162\72\x20\x23\x46\106\106\73\x22\164\171\x70\145\75\x22\142\x75\x74\x74\157\156\42\40\166\141\x6c\165\145\x3d\x22\104\x6f\156\x65\x22\76\x3c\57\141\x3e\x3c\57\144\x69\166\76";
            exit;
            qo:
            goto RU;
            qd:
            echo "\x3c\x64\151\x76\40\x73\164\171\154\x65\x3d\x22\146\x6f\156\x74\55\x66\141\155\x69\154\171\72\103\x61\154\151\142\x72\x69\x3b\x70\141\x64\x64\151\x6e\147\72\x30\40\x33\45\73\42\x3e";
            echo "\74\x64\x69\x76\40\163\x74\x79\x6c\145\x3d\42\143\x6f\x6c\x6f\162\72\x20\x23\141\71\x34\64\x34\62\73\142\141\143\x6b\x67\162\x6f\165\156\144\x2d\x63\x6f\x6c\157\162\72\40\x23\x66\62\x64\x65\x64\145\73\160\x61\x64\144\x69\x6e\x67\72\40\x31\65\x70\x78\73\155\141\162\x67\x69\156\x2d\142\157\164\x74\x6f\155\72\40\x32\x30\160\x78\x3b\164\x65\170\164\55\x61\x6c\151\147\156\x3a\x63\x65\156\164\145\x72\x3b\x62\157\162\144\145\x72\x3a\x31\160\x78\x20\x73\157\154\151\x64\x20\43\105\66\102\63\102\x32\x3b\146\x6f\x6e\164\x2d\x73\151\x7a\145\72\x31\x38\160\x74\73\42\x3e\x20\x45\122\122\x4f\122\74\x2f\x64\151\x76\76\12\x20\x20\40\40\x20\40\x20\x20\x20\40\x20\x20\40\x20\40\x20\40\40\40\40\x20\x20\x20\40\x3c\x64\151\x76\40\x73\164\171\x6c\x65\x3d\x22\x63\x6f\154\x6f\x72\72\x20\43\141\71\64\x34\x34\x32\x3b\x66\157\x6e\x74\55\x73\x69\x7a\145\x3a\61\64\x70\164\73\x20\x6d\x61\x72\147\151\156\55\x62\x6f\164\164\157\x6d\x3a\x32\x30\x70\x78\73\42\76\74\160\76\x3c\x73\x74\162\157\x6e\147\x3e\105\162\x72\x6f\x72\72\x20\74\57\x73\164\x72\x6f\156\x67\x3e\104\x75\160\x6c\151\143\141\x74\145\x20\114\151\x63\145\x6e\x63\x65\x20\113\x65\x79\x20\x69\x73\x20\x45\x6e\x63\157\165\156\164\x65\x72\145\144\x2e\74\57\160\x3e\12\40\40\x20\x20\40\40\40\40\x20\40\x20\x20\x20\x20\x20\40\x20\40\40\x20\40\x20\40\40\x20\x20\40\x20\74\x70\76\x50\x6c\145\x61\163\x65\x20\143\157\x6e\164\x61\x63\164\x20\x79\157\x75\x72\x20\x61\x64\155\x69\x6e\x69\x73\164\162\x61\164\x6f\x72\x20\x61\156\x64\x20\162\x65\160\157\162\164\x20\164\x68\x65\40\146\157\154\x6c\x6f\167\151\156\x67\40\x65\x72\162\x6f\162\x3a\x3c\57\160\76\12\x20\40\40\40\x20\x20\x20\x20\40\40\40\x20\x20\40\40\40\x20\x20\40\40\x20\40\40\40\x20\40\x20\40\x3c\160\76\x3c\x73\164\x72\157\156\x67\x3e\120\x6f\x73\163\151\x62\154\145\x20\103\141\165\x73\x65\x3a\x20\74\x2f\163\164\x72\x6f\x6e\147\x3e\x20\114\151\x63\x65\x6e\163\145\40\x6b\145\x79\x20\x66\x6f\x72\x20\164\x68\151\163\40\151\156\163\x74\x61\x6e\x63\145\x20\x69\163\x20\151\156\x63\157\x72\162\x65\143\164\x2e\40\x4d\141\153\x65\x20\163\165\162\x65\x20\171\x6f\165\40\x68\141\x76\x65\40\156\157\x74\x20\164\141\155\160\x65\x72\145\x64\x20\167\151\164\x68\40\151\164\x20\x61\x74\x20\x61\x6c\x6c\x2e\x20\120\x6c\145\141\163\x65\x20\145\156\x74\x65\162\x20\x61\40\166\x61\154\151\x64\x20\x6c\x69\x63\x65\156\163\145\x20\x6b\145\171\x2e\74\57\160\76\xa\x20\x20\40\40\x20\x20\x20\x20\x20\x20\x20\40\40\40\40\40\x20\x20\x20\x20\40\x20\x20\x20\x3c\57\x64\151\166\76\xa\x20\40\x20\x20\40\x20\x20\40\x20\40\x20\40\x20\40\x20\40\x20\x20\x20\40\74\144\151\166\40\x73\x74\x79\x6c\x65\75\x22\x6d\141\162\147\151\x6e\72\63\45\73\x64\x69\163\x70\x6c\x61\x79\72\142\x6c\x6f\143\153\73\164\x65\170\x74\55\x61\x6c\151\147\156\72\x63\x65\x6e\x74\x65\x72\73\42\76";
            $VD = JURI::root();
            echo "\74\x64\x69\166\40\163\x74\171\154\145\75\42\x6d\141\x72\x67\x69\x6e\x3a\63\x25\x3b\144\151\x73\160\x6c\141\x79\x3a\142\154\157\x63\x6b\x3b\x74\145\x78\164\x2d\141\x6c\151\147\x6e\72\x63\x65\x6e\x74\x65\x72\73\x22\76\74\x61\40\150\162\145\x66\x3d\42";
            echo $VD;
            echo "\40\x22\x3e\74\151\156\x70\165\x74\40\x73\x74\171\x6c\x65\75\42\160\x61\x64\x64\x69\156\147\72\x31\45\x3b\167\151\144\x74\150\72\61\60\60\160\x78\73\x62\141\143\x6b\x67\162\157\x75\156\144\x3a\40\x23\x30\60\x39\x31\x43\x44\40\x6e\x6f\x6e\145\x20\x72\x65\160\145\141\x74\x20\x73\143\x72\157\x6c\154\x20\60\x25\40\60\x25\x3b\x63\x75\x72\x73\157\x72\72\40\x70\157\x69\156\x74\x65\x72\73\x66\157\156\x74\55\x73\x69\172\x65\72\61\x35\x70\x78\73\x62\x6f\162\x64\x65\162\x2d\x77\151\144\164\150\72\x20\x31\160\x78\x3b\x62\x6f\x72\x64\145\162\x2d\163\x74\171\x6c\x65\72\40\163\x6f\x6c\x69\144\x3b\142\x6f\162\x64\x65\x72\55\x72\x61\144\151\x75\163\72\40\63\160\170\73\x77\150\151\x74\145\x2d\163\160\141\143\145\72\x20\156\x6f\x77\x72\141\x70\x3b\x62\157\x78\x2d\x73\x69\172\151\156\x67\72\x20\142\157\162\x64\145\x72\55\142\x6f\x78\73\x62\157\162\x64\145\x72\55\143\157\154\157\162\72\40\43\60\x30\67\63\101\101\x3b\142\x6f\x78\x2d\x73\150\141\x64\x6f\x77\72\x20\x30\x70\x78\40\61\160\170\40\x30\160\170\40\x72\147\x62\141\50\61\x32\60\54\x20\62\60\60\x2c\40\62\x33\x30\x2c\40\x30\x2e\66\x29\40\151\x6e\x73\x65\164\73\143\157\154\x6f\x72\72\x20\x23\x46\106\x46\x3b\42\164\x79\x70\x65\x3d\42\142\165\164\x74\x6f\x6e\42\x20\x76\141\154\x75\145\x3d\x22\104\x6f\x6e\x65\42\x3e\x3c\x2f\141\x3e\74\x2f\144\151\166\x3e";
            exit;
            RU:
            MS:
        }
        AH:
        if (defined("\x5f\112\x44\x45\106\x49\x4e\105\123")) {
            goto Dm;
        }
        require_once JPATH_BASE . "\57\x69\x6e\143\x6c\165\x64\145\x73\x2f\144\x65\x66\151\x6e\x65\x73\x2e\x70\x68\160";
        Dm:
        $lo = Mo_saml_Local_Util::decrypt_value($lo);
        require_once JPATH_BASE . "\57\x69\x6e\x63\x6c\165\x64\x65\163\x2f\x66\x72\x61\155\145\x77\157\x72\153\x2e\160\150\x70";
        $xF = JPATH_BASE . DIRECTORY_SEPARATOR . "\x70\154\165\x67\x69\156\163" . DIRECTORY_SEPARATOR . "\x61\165\x74\x68\x65\156\x74\151\143\141\x74\151\157\156" . DIRECTORY_SEPARATOR . "\155\151\x6e\x69\x6f\162\141\x6e\147\x65\x73\x61\155\154";
        include_once $xF . DIRECTORY_SEPARATOR . "\x73\x61\155\154\x32" . DIRECTORY_SEPARATOR . "\122\x65\x73\x70\157\156\163\145\x2e\x70\x68\x70";
        jimport("\155\151\x6e\x69\157\x72\x61\156\147\145\163\x61\155\x6c\160\154\x75\x67\x69\x6e\56\165\x74\x69\x6c\151\164\171\x2e\145\x6e\x63\x72\171\x70\x74\x69\x6f\156");
        jimport("\152\x6f\157\x6d\154\x61\56\x61\160\160\x6c\151\x63\141\x74\x69\157\156\56\x61\x70\160\x6c\151\143\x61\x74\x69\157\x6e");
        jimport("\x6a\x6f\x6f\155\154\x61\56\x68\164\155\154\x2e\160\141\162\141\155\x65\x74\145\x72");
        $JE = JFactory::getApplication()->input->get->getArray();
        $nP = '';
        if (!(isset($JE["\x69\x64\160"]) && !empty($JE["\x69\x64\x70"]))) {
            goto Bs;
        }
        $nP = $JE["\151\x64\x70"];
        Bs:
        if (!($lo == true)) {
            goto xm;
        }
        $ME = UtilitiesSAML::gt_lk_trl();
        $Qa = 0;
        if (!$ME["\114\151\x63\x65\156\x73\145\x45\170\x70\151\x72\145\144"]) {
            goto Yr;
        }
        UtilitiesSAML::rmvextnsns();
        $jA = UtilitiesSAML::get_message_and_cause($ME["\x4c\151\143\x65\156\x73\145\105\x78\160\151\162\145\144"], $Qa);
        UtilitiesSAML::show_error_messages($jA["\x6d\x73\x67"], $jA["\143\x61\165\x73\x65"]);
        Yr:
        xm:
        $gd = UtilitiesSAML::getSAMLConfiguration($nP)[0];
        $bA = isset($gd["\x64\145\146\141\x75\x6c\164\137\162\145\154\141\x79\137\x73\164\141\x74\x65"]) ? $gd["\x64\x65\x66\x61\x75\154\164\137\x72\145\154\x61\x79\x5f\163\x74\x61\x74\145"] : '';
        $Ny = JFactory::getApplication("\x73\151\x74\145");
        $n3 = UtilitiesSAML::getJoomlaCmsVersion();
        $n3 = substr($n3, 0, 3);
        if (!($n3 < 4.0)) {
            goto ko;
        }
        $Ny->initialise();
        ko:
        $post = JFactory::getApplication()->input->post->getArray();
        if (!($lo == true)) {
            goto pV;
        }
        $ME = UtilitiesSAML::gt_lk_trl();
        $Qa = 0;
        if (!($ME["\116\x6f\157\x66\125\x73\145\x72\163"] <= $qh["\165\163\x72\x6c\x6d\x74"])) {
            goto nQ;
        }
        UtilitiesSAML::rmvextnsns();
        $Qa = 1;
        $jA = UtilitiesSAML::get_message_and_cause($ME["\x4c\151\x63\145\x6e\163\x65\x45\170\x70\151\162\x65\144"], $Qa);
        UtilitiesSAML::show_error_messages($jA["\155\163\x67"], $jA["\x63\141\x75\x73\145"]);
        nQ:
        pV:
        if (array_key_exists("\x53\101\x4d\x4c\x52\x65\x73\160\x6f\x6e\x73\145", $post)) {
            goto w3;
        }
        throw new Exception("\x4d\151\163\163\151\156\x67\x20\123\101\x4d\114\x52\145\x71\x75\145\x73\164\x20\157\162\x20\123\x41\x4d\x4c\x52\145\x73\x70\x6f\x6e\163\x65\40\x70\141\x72\x61\155\x65\164\145\x72\x2e");
        goto Wa;
        w3:
        $this->validateSamlResponse($post, $Ny, $bA);
        Wa:
    }
    function validateSamlResponse($post, $Ny, $bA)
    {
        $id = $post["\x53\x41\x4d\114\x52\x65\x73\160\157\x6e\163\x65"];
        $rj = JFactory::getApplication()->input->request->getArray();
        $qh = UtilitiesSAML::getCustomerDetails();
        if (empty($bA)) {
            goto hu;
        }
        $tH = $bA;
        hu:
        if (!array_key_exists("\122\x65\x6c\x61\171\123\x74\x61\164\x65", $rj)) {
            goto K7;
        }
        $tH = $rj["\x52\x65\154\x61\x79\123\164\141\164\x65"];
        K7:
        $id = base64_decode($id);
        $qm = new DOMDocument();
        $qm->loadXML($id);
        $mf = $qm->firstChild;
        $id = new SAML2_Response($mf);
        $lo = $qh["\x74\x72\151\163\164\x73"];
        $M4 = current($id->getAssertions())->getIssuer();
        $t7 = UtilitiesSAML::getSAMLConfiguration($M4);
        $pT = isset($t7[0]) ? $t7[0] : '';
        $uo = Mo_saml_Local_Util::decrypt_value($lo);
        if (!(!empty($pT) || $pT != '')) {
            goto wW;
        }
        UtilitiesSAML::auto_update_metadata($pT);
        wW:
        if (!empty($t7)) {
            goto nz;
        }
        echo "\40\x20\x20\40\x20\x20\40\40\x20\40\40\40\74\144\151\166\40\x73\x74\171\154\x65\x3d\42\146\157\156\x74\x2d\146\141\x6d\151\x6c\171\x3a\x43\141\x6c\x69\142\x72\x69\x3b\x70\x61\144\x64\151\156\147\x3a\x30\x20\63\x25\x3b\42\76\xa\x20\x20\x20\x20\x20\40\x20\40\x20\x20\x20\40\x3c\x64\x69\166\x20\x73\164\x79\x6c\x65\75\x22\x63\x6f\x6c\x6f\x72\x3a\x20\x23\x61\x39\x34\x34\64\x32\x3b\x62\x61\x63\153\x67\x72\157\x75\x6e\x64\55\143\x6f\x6c\157\162\72\x20\43\x66\x32\144\x65\144\x65\x3b\x70\x61\144\144\151\156\x67\x3a\x20\x31\x35\x70\x78\x3b\155\x61\x72\147\x69\156\x2d\x62\x6f\x74\164\157\x6d\x3a\40\x32\60\160\170\73\164\x65\170\164\55\141\154\151\x67\x6e\x3a\x63\x65\x6e\164\x65\x72\x3b\142\157\162\x64\x65\x72\x3a\61\160\170\x20\163\157\x6c\151\x64\x20\43\x45\x36\x42\63\x42\62\x3b\146\157\x6e\x74\55\x73\151\x7a\x65\x3a\x31\70\160\x74\x3b\42\76\x20\105\122\122\x4f\122\x3c\57\144\x69\166\x3e\12\x20\x20\x20\40\x20\x20\x20\40\40\40\x20\x20\x20\40\40\40\x3c\144\151\x76\x20\163\x74\x79\154\x65\75\42\143\157\154\157\162\x3a\40\43\x61\x39\x34\64\64\62\x3b\146\157\x6e\164\55\x73\x69\172\145\72\x31\x34\x70\164\x3b\40\155\141\162\x67\151\156\x2d\142\157\164\164\x6f\x6d\x3a\x32\x30\x70\x78\73\x22\76\74\x70\x3e\x3c\x73\164\x72\x6f\156\x67\x3e\105\162\x72\x6f\162\72\40\74\x2f\x73\x74\162\157\x6e\x67\76\x49\x73\163\165\145\x72\x20\143\x61\x6e\x6e\157\164\x20\142\x65\40\166\145\162\151\x66\x69\x65\x64\x2e\74\x2f\160\76\xa\40\40\40\40\40\40\x20\x20\x20\x20\40\x20\x20\40\x20\40\x3c\x70\x3e\x50\154\x65\141\x73\x65\40\143\157\156\164\x61\x63\x74\40\x79\157\x75\x72\40\x61\x64\155\x69\156\151\x73\164\x72\x61\x74\157\162\x20\141\x6e\144\40\162\x65\x70\x6f\x72\164\x20\x74\150\145\40\146\157\154\x6c\x6f\167\x69\x6e\x67\x20\x65\162\162\157\162\72\x3c\x2f\x70\76\xa\x20\40\x20\40\40\40\x20\x20\x20\x20\40\x20\40\40\40\x20\74\x70\76\74\x73\x74\162\157\x6e\147\x3e\120\157\163\x73\x69\x62\x6c\145\x20\x43\x61\165\163\x65\x3a\x20\x3c\57\x73\164\x72\157\156\x67\76\124\x68\145\40\x76\141\154\165\x65\x20\157\x66\40\74\163\x74\162\157\156\147\76\x49\x64\120\40\x45\x6e\x74\151\x74\171\x20\x49\x44\40\x6f\x72\x20\111\x73\x73\165\x65\x72\x20\157\162\40\x41\165\144\151\x65\x6e\143\145\40\125\x52\111\74\x2f\163\164\162\x6f\156\147\x3e\40\151\x6e\40\112\157\157\x6d\154\x61\x20\123\x41\x4d\114\40\x53\x50\x20\160\154\165\147\151\x6e\x20\141\x6e\144\40\x74\150\145\x20\x63\x6f\x6e\146\151\147\165\162\145\x64\40\x5c\x27\x45\156\x74\x69\x74\x79\40\111\104\134\47\x20\151\156\x20\x79\x6f\165\162\40\x49\104\x50\40\x69\163\40\151\x6e\143\157\x72\162\x65\143\x74\56\x3c\x2f\160\x3e\xa\40\40\40\x20\40\x20\x20\40\40\x20\40\40\x20\40\40\40\74\57\x64\x69\166\76\12\x20\x20\x20\40\40\40\x20\x20\40\x20\40\40\x20\x20\x20\x20\x3c\x69\x6e\160\165\x74\40\x73\x74\x79\x6c\145\75\x22\x70\141\144\144\x69\156\x67\72\x31\45\x3b\x77\151\144\x74\x68\72\61\60\x30\160\x78\x3b\142\141\x63\153\x67\x72\157\165\156\144\72\x20\43\x30\60\x39\x31\x43\x44\x20\156\157\x6e\x65\40\x72\x65\160\145\x61\x74\x20\163\143\x72\157\154\154\x20\x30\x25\40\x30\x25\73\x63\165\162\x73\x6f\x72\72\x20\x70\x6f\151\156\164\x65\x72\73\146\x6f\156\164\x2d\x73\151\x7a\x65\72\x31\65\x70\x78\73\x62\x6f\x72\x64\145\x72\55\x77\x69\x64\x74\x68\x3a\x20\61\160\x78\x3b\x62\157\162\144\x65\162\55\x73\164\171\x6c\145\72\x20\163\x6f\154\151\x64\x3b\142\157\162\x64\x65\x72\x2d\x72\x61\144\151\165\163\x3a\x20\x33\x70\170\73\167\x68\151\164\x65\x2d\163\160\141\x63\x65\x3a\40\156\x6f\167\162\x61\160\73\142\157\170\x2d\x73\151\172\151\x6e\x67\72\x20\x62\157\162\x64\x65\162\55\142\x6f\x78\x3b\142\x6f\x72\x64\145\x72\x2d\143\x6f\154\157\162\x3a\40\x23\60\60\x37\63\x41\101\73\x62\157\x78\x2d\x73\150\141\x64\157\x77\72\x20\60\x70\170\x20\x31\160\x78\x20\x30\x70\x78\x20\162\147\x62\141\50\x31\62\60\x2c\40\62\x30\60\x2c\40\x32\x33\60\54\40\60\x2e\x36\x29\40\151\x6e\163\145\164\x3b\143\157\x6c\157\x72\x3a\x20\43\x46\106\106\x3b\144\x69\x73\160\x6c\x61\x79\x3a\x62\154\157\x63\153\x3b\155\141\x72\147\x69\x6e\x2d\154\145\x66\164\x3a\x61\x75\164\157\x3b\155\141\162\147\x69\x6e\x2d\162\x69\147\x68\x74\x3a\141\x75\x74\x6f\42\x20\164\x79\x70\145\75\42\x62\x75\x74\164\157\156\42\x20\x76\141\x6c\165\x65\x3d\42\104\x6f\x6e\x65\x22\x20\157\x6e\x43\x6c\151\x63\x6b\x3d\x22\163\x65\154\x66\x2e\x63\154\157\163\x65\50\51\x3b\42\x3e\xa\40\40\40\x20\40\x20\40\x20\40\40\x20\40\40\x20\40";
        exit;
        goto YD;
        nz:
        if (!($uo == true)) {
            goto iq;
        }
        $ME = UtilitiesSAML::gt_lk_trl();
        $Qa = 0;
        if (!($ME["\116\157\x6f\146\x55\163\145\x72\x73"] <= $qh["\x75\x73\x72\x6c\155\164"])) {
            goto yn;
        }
        UtilitiesSAML::rmvextnsns();
        $Qa = 1;
        $jA = UtilitiesSAML::get_message_and_cause($ME["\x4c\151\x63\x65\156\163\x65\x45\170\x70\151\162\x65\144"], $Qa);
        UtilitiesSAML::show_error_messages($jA["\155\163\x67"], $jA["\143\x61\x75\x73\145"]);
        yn:
        iq:
        $t7 = $t7[0];
        $Ye = UtilitiesSAML::getCustomerDetails();
        $Ol = '';
        $FM = '';
        if (!isset($Ye["\x73\160\137\142\x61\163\145\x5f\x75\162\x6c"])) {
            goto ug;
        }
        $Ol = $Ye["\x73\x70\137\142\x61\163\145\x5f\165\162\154"];
        $FM = $Ye["\x73\x70\137\x65\156\164\151\x74\x79\137\151\x64"];
        ug:
        $gc = JURI::root();
        if (!($uo == true)) {
            goto v5;
        }
        $ME = UtilitiesSAML::gt_lk_trl();
        $Qa = 0;
        if (!$ME["\114\151\x63\145\x6e\x73\145\105\x78\160\x69\x72\x65\144"]) {
            goto Xr;
        }
        UtilitiesSAML::rmvextnsns();
        $jA = UtilitiesSAML::get_message_and_cause($ME["\114\x69\143\x65\x6e\x73\x65\x45\x78\160\x69\162\145\144"], $Qa);
        UtilitiesSAML::show_error_messages($jA["\155\163\147"], $jA["\x63\x61\165\x73\145"]);
        Xr:
        v5:
        if (!empty($Ol)) {
            goto ra;
        }
        $Ol = $gc;
        ra:
        if (!empty($FM)) {
            goto Il;
        }
        $FM = $gc . "\x70\x6c\x75\147\151\156\x73\57\141\x75\164\x68\145\x6e\x74\151\x63\x61\164\x69\x6f\x6e\57\155\151\x6e\x69\x6f\x72\141\x6e\147\145\163\141\x6d\154";
        Il:
        $AG = $Ol . "\x3f\155\x6f\162\x65\161\165\x65\163\x74\x3d\141\143\163";
        $tx = $t7["\143\145\162\164\x69\x66\151\x63\x61\x74\145"];
        $jQ = explode("\73", $tx);
        $hw = array();
        $bz = 0;
        foreach ($jQ as $mc) {
            $mc = UtilitiesSAML::sanitize_certificate($mc);
            $mc = XMLSecurityKeySAML::getRawThumbprint($mc);
            $mc = preg_replace("\57\134\163\x2b\x2f", '', $mc);
            $hw[$bz] = iconv("\x55\x54\x46\55\70", "\103\120\x31\x32\x35\62\x2f\57\x49\107\x4e\117\122\x45", $mc);
            $bz++;
            Pb:
        }
        au:
        $EP = $id->getSignatureData();
        $e3 = current($id->getAssertions())->getSignatureData();
        if (empty($EP)) {
            goto Ty;
        }
        $QS = UtilitiesSAML::processResponse($AG, $hw, $EP, $id, $tx, $tH);
        if (!($QS === FALSE)) {
            goto me;
        }
        echo "\111\x6e\166\x61\154\x69\144\x20\x73\x69\x67\x6e\x61\x74\165\162\x65\x20\151\156\x20\164\x68\145\x20\123\101\115\x4c\40\x52\145\x73\160\157\156\x73\145\x2e\40\x45\x69\x74\150\145\162\x20\x53\x41\115\114\40\122\145\x73\160\157\x6e\x73\x65\40\x69\163\x20\x6e\157\164\40\163\151\147\156\x65\144\x20\x6f\x72\x20\x73\x69\147\156\145\144\40\167\151\x74\150\40\x77\162\157\x6e\147\40\x6b\x65\x79\40\142\x79\x20\111\x44\x50\x2e";
        exit;
        me:
        Ty:
        if (empty($e3)) {
            goto eO;
        }
        $QS = UtilitiesSAML::processResponse($AG, $hw, $e3, $id, $tx, $tH);
        if (!($QS === FALSE)) {
            goto Uq;
        }
        echo "\111\156\x76\141\x6c\151\x64\x20\x73\151\x67\156\x61\164\165\x72\145\40\x69\156\x20\164\150\x65\x20\x53\x41\x4d\x4c\40\101\x73\163\x65\162\164\151\157\156\x2e\40\x45\x69\164\x68\x65\x72\40\x53\101\x4d\x4c\x20\101\163\x73\x65\162\x74\x69\157\156\40\x69\163\40\x6e\x6f\164\40\163\x69\x67\156\145\144\x20\x6f\x72\40\163\x69\147\156\x65\x64\40\x77\151\164\x68\x20\x77\x72\x6f\156\x67\x20\153\x65\x79\x20\142\x79\x20\x49\x44\120\56";
        exit;
        Uq:
        eO:
        if (!(empty($e3) && empty($EP))) {
            goto pm;
        }
        echo "\116\157\x20\x73\151\147\156\x61\x74\165\x72\145\40\151\156\x20\x53\x41\115\114\x20\122\x65\x73\160\157\156\163\x65\40\x6f\162\40\x41\x73\163\x65\x72\x74\151\157\156\x2e";
        exit;
        pm:
        $KH = $t7["\151\144\160\137\145\156\164\x69\x74\x79\137\x69\x64"];
        UtilitiesSAML::validateIssuerAndAudience($id, $FM, $KH);
        $Df = current(current($id->getAssertions())->getNameId());
        $O2 = current($id->getAssertions())->getAttributes();
        $O2["\x41\123\x53\105\x52\124\111\117\x4e\x5f\x4e\x41\115\x45\137\111\104"] = current(current($id->getAssertions())->getNameId());
        if (!($tH == "\x74\x65\x73\164\x56\141\154\151\144\x61\x74\145")) {
            goto Jq;
        }
        UtilitiesSAML::mo_saml_show_test_result($Df, $O2, $Ol);
        Jq:
        $Gv = current($id->getAssertions())->getSessionIndex();
        $O2["\x41\123\x53\x45\122\x54\111\x4f\116\137\123\105\x53\x53\x49\117\x4e\x5f\111\116\104\x45\x58"] = $Gv;
        $gn = $Df;
        $Hu = '';
        $lQ = isset($t7["\146\151\x72\x73\x74\137\x6e\x61\x6d\x65"]) ? $t7["\146\x69\162\x73\x74\x5f\x6e\141\155\145"] : '';
        $m3 = isset($t7["\x6c\141\x73\x74\x5f\x6e\x61\155\x65"]) ? $t7["\x6c\x61\x73\164\x5f\156\x61\x6d\145"] : '';
        $Eg = isset($t7["\x6e\x61\155\x65"]) ? trim($t7["\x6e\x61\x6d\x65"]) : '';
        $hL = isset($t7["\x75\x73\x65\162\x6e\x61\x6d\145"]) ? $t7["\x75\x73\x65\x72\156\x61\155\x65"] : '';
        $xf = isset($t7["\145\155\141\151\154"]) ? $t7["\145\155\141\151\154"] : '';
        $SD = UtilitiesSAML::getRoleMapping($t7);
        $Dq = isset($t7["\144\151\x73\141\142\154\x65\137\x75\x70\144\x61\164\x65\137\145\170\151\163\x74\x69\156\147\x5f\143\165\163\164\x6f\155\145\162\x5f\x61\x74\164\162\151\x62\x75\x74\x65\163"]) ? $t7["\x64\x69\163\141\x62\154\x65\x5f\x75\160\144\x61\x74\x65\x5f\145\x78\x69\x73\x74\x69\x6e\x67\x5f\143\x75\163\x74\x6f\x6d\145\x72\137\x61\x74\x74\x72\151\x62\165\164\x65\x73"] : 0;
        $sQ = isset($SD["\147\x72\160"]) ? $SD["\x67\x72\x70"] : '';
        if (!(!empty($hL) && isset($O2[$hL]) && !empty($O2[$hL]))) {
            goto xe;
        }
        $Df = $O2[$hL];
        if (!is_array($Df)) {
            goto uQ;
        }
        $Df = $Df[0];
        uQ:
        xe:
        if (!(!empty($xf) && isset($O2[$xf]) && !empty($O2[$xf]))) {
            goto SD;
        }
        $gn = $O2[$xf];
        if (!is_array($gn)) {
            goto II;
        }
        $gn = $gn[0];
        II:
        SD:
        if (!(!empty($lQ) && isset($O2[$lQ][0]) && !empty($O2[$lQ][0]))) {
            goto Eq;
        }
        $dJ = $O2[$lQ];
        if (!is_array($dJ)) {
            goto Yg;
        }
        $dJ = $dJ[0];
        Yg:
        Eq:
        if (!(!empty($m3) && isset($O2[$m3][0]) && !empty($O2[$m3][0]))) {
            goto tH;
        }
        $oQ = $O2[$m3];
        if (!is_array($oQ)) {
            goto Je;
        }
        $oQ = $oQ[0];
        Je:
        tH:
        if (!(!empty($Eg) && isset($O2[$Eg][0]) && !empty($O2[$Eg][0]))) {
            goto Ex;
        }
        $Hu = $O2[$Eg];
        if (!is_array($Hu)) {
            goto ss;
        }
        $Hu = $Hu[0];
        ss:
        Ex:
        if (!(isset($dJ) && !empty($dJ))) {
            goto qY;
        }
        $Hu = $dJ . "\x20";
        qY:
        if (!(isset($oQ) && !empty($oQ))) {
            goto Ip;
        }
        $Hu = $Hu . $oQ;
        Ip:
        if (!empty($sQ) && isset($O2[$sQ]) && !empty($O2[$sQ])) {
            goto lA;
        }
        $ZV = array();
        goto QN;
        lA:
        $ZV = $O2[$sQ];
        QN:
        $FD = "\x65\155\141\151\x6c";
        $lx = UtilitiesSAML::get_user_from_joomla($FD, $Df, $gn);
        $lM = isset($tH) ? $tH : $Ol;
        $Hu = isset($Hu) && !empty($Hu) ? $Hu : $Df;
        if ($uo && (isset($ME["\114\x69\x63\145\156\163\145\105\x78\x70\151\x72\x65\144"]) && $ME["\114\151\x63\145\156\163\145\105\170\160\x69\162\x65\144"] == "\x54\x72\165\x65") || isset($Qa) && $Qa == 1) {
            goto m2;
        }
        if ($lx) {
            goto lj;
        }
        $this->RegisterCurrentUser($O2, $lM, $Hu, $Df, $gn, $FD, $Ny, $ZV, $t7);
        goto yN;
        lj:
        $this->loginCurrentUser($lx, $O2, $lM, $Hu, $Ny, $ZV, $t7, $Dq);
        yN:
        goto sD;
        m2:
        $jA = UtilitiesSAML::get_message_and_cause($ME["\114\x69\x63\145\156\163\x65\x45\170\160\x69\162\145\x64"], $Qa);
        UtilitiesSAML::show_error_messages($jA["\x6d\163\147"], $jA["\143\141\165\163\x65"]);
        sD:
        YD:
    }
    function loginCurrentUser($lx, $O2, $lM, $Hu, $Ny, $ZV, $gd, $Dq)
    {
        $user = JUser::getInstance($lx->id);
        $SD = UtilitiesSAML::getRoleMapping($gd);
        $fv = UtilitiesSAML::getCustomerDetails();
        $Mu = isset($fv["\151\147\156\157\x72\x65\x5f\x73\x70\145\x63\151\x61\x6c\x5f\x63\150\141\x72\x61\x63\164\x65\162\x73"]) ? $fv["\x69\x67\x6e\x6f\x72\145\x5f\x73\160\x65\143\151\141\154\x5f\x63\150\x61\x72\141\x63\x74\145\x72\163"] : 0;
        $tk = isset($fv["\x65\156\x61\142\x6c\145\x5f\x6d\x61\x6e\x61\x67\x65\162\x5f\x6c\x6f\x67\x69\x6e"]) ? $fv["\x65\x6e\141\x62\154\145\137\x6d\141\x6e\141\147\x65\162\x5f\x6c\157\x67\151\156"] : 0;
        $lo = $fv["\164\162\151\x73\164\163"];
        if (!(!$Mu && preg_match("\133\47\135", $user->email))) {
            goto M0;
        }
        $Ny = JFactory::getApplication();
        $Ny->enqueueMessage("\131\x6f\165\x20\141\x72\145\x20\x6e\x6f\x74\x20\141\x6c\154\x6f\167\145\x64\x20\164\x6f\40\x6c\157\x67\x69\x6e\x20\151\x6e\164\157\40\164\x68\145\40\163\x69\164\x65\40\167\x69\164\x68\40\163\x70\145\143\151\141\154\40\143\150\141\x72\141\x63\164\145\162\40\x69\156\40\x65\155\141\151\x6c\40\x61\144\x64\162\x65\x73\163\x2e\40\120\x6c\x65\141\x73\145\x20\143\x6f\156\x74\141\x63\x74\x20\171\x6f\165\x72\40\x41\x64\155\151\156\151\x73\164\x72\x61\x74\157\x72\56", "\145\162\x72\x6f\162");
        $Ny->redirect(JURI::root());
        M0:
        $eW = isset($SD["\x75\160\x64\x61\164\x65\137\145\x78\151\163\x74\151\156\147\137\x75\163\x65\x72\x73\137\162\x6f\x6c\145\x5f\167\151\x74\x68\157\165\164\x5f\162\145\x6d\x6f\166\151\x6e\x67\137\x63\165\x72\162\x65\156\164"]) ? $SD["\165\x70\144\141\x74\145\137\145\x78\151\x73\164\151\x6e\147\137\x75\x73\145\162\163\137\162\x6f\x6c\145\137\167\x69\x74\x68\x6f\x75\x74\x5f\162\x65\x6d\x6f\166\x69\156\x67\137\143\165\162\162\145\x6e\164"] : 0;
        $aZ = isset($SD["\144\151\x73\141\x62\154\145\137\145\170\151\163\164\x69\156\x67\x5f\165\163\x65\162\x73\137\162\157\x6c\x65\137\165\160\144\x61\164\x65"]) ? $SD["\144\x69\163\x61\142\154\x65\137\x65\x78\151\x73\x74\151\x6e\x67\x5f\165\163\145\162\x73\137\x72\x6f\154\145\137\x75\x70\x64\x61\164\x65"] : 0;
        if ($aZ) {
            goto cX;
        }
        $mb = 2;
        if (!isset($SD["\155\141\160\160\x69\x6e\x67\137\x76\x61\154\165\145\137\144\145\x66\x61\x75\x6c\164"])) {
            goto Jw;
        }
        $mb = $SD["\x6d\141\x70\160\151\x6e\147\x5f\166\141\154\165\145\x5f\x64\x65\x66\x61\x75\x6c\164"];
        Jw:
        $P8 = array();
        if (!isset($SD["\x72\x6f\x6c\145\137\155\141\160\160\151\156\x67\x5f\x6b\145\x79\x5f\166\141\154\x75\x65"])) {
            goto RA;
        }
        $P8 = json_decode($SD["\162\x6f\x6c\x65\137\x6d\x61\160\160\151\156\147\137\153\145\x79\x5f\166\141\x6c\x75\145"]);
        RA:
        $H5 = 0;
        if (!isset($SD["\145\x6e\141\x62\154\145\137\x73\x61\x6d\x6c\x5f\x72\x6f\154\x65\x5f\x6d\141\x70\160\x69\x6e\147"])) {
            goto GH;
        }
        $H5 = json_decode($SD["\145\156\141\142\154\145\x5f\x73\141\155\154\x5f\162\157\154\x65\x5f\x6d\x61\x70\160\x69\156\x67"]);
        GH:
        jimport("\x6a\x6f\157\155\x6c\x61\56\x75\163\x65\162\x2e\x68\x65\154\x70\145\162");
        if (!($H5 == 1)) {
            goto zJ;
        }
        $OX = UtilitiesSAML::get_mapped_groups($P8, $ZV);
        $this->addOrRemoveUserFromGroup($OX, $mb, $user, $eW);
        zJ:
        cX:
        $W6 = Mo_saml_Local_Util::decrypt_value($lo);
        if ($Dq) {
            goto Eh;
        }
        UtilitiesSAML::updateCurrentUserName($user->id, $Hu);
        $this->updateUserProfileAttributes($user->id, $O2, isset($gd["\165\x73\145\162\x5f\x70\162\157\x66\x69\154\x65\137\x61\x74\164\x72\151\x62\x75\x74\x65\163"]) ? $gd["\165\163\145\x72\x5f\x70\162\x6f\x66\151\x6c\145\x5f\x61\164\x74\x72\151\142\165\x74\145\163"] : '');
        Eh:
        if (!($W6 == true)) {
            goto pR;
        }
        $ME = UtilitiesSAML::gt_lk_trl();
        $Qa = 0;
        if (!$ME["\114\x69\x63\145\156\x73\x65\x45\170\160\x69\162\x65\144"]) {
            goto RF;
        }
        UtilitiesSAML::rmvextnsns();
        $jA = UtilitiesSAML::get_message_and_cause($ME["\x4c\x69\143\145\x6e\163\145\x45\x78\160\151\x72\x65\144"], $Qa);
        UtilitiesSAML::show_error_messages($jA["\x6d\163\147"], $jA["\143\141\165\x73\145"]);
        RF:
        pR:
        $GA = UtilitiesSAML::getPluginConfigurations($gd["\x69\x64"]);
        $GA = current($GA);
        $GA = isset($GA["\x75\163\x65\x72\x5f\146\151\145\x6c\x64\x5f\x61\x74\164\x72\151\x62\x75\164\x65\x73"]) ? $GA["\x75\x73\x65\x72\x5f\x66\151\x65\x6c\x64\137\141\x74\x74\x72\151\142\x75\x74\145\x73"] : '';
        $this->updateUserFieldAttributes($user->id, $O2, $GA);
        $mv = JPATH_BASE . DIRECTORY_SEPARATOR . "\154\151\x62\162\x61\162\x69\x65\163" . DIRECTORY_SEPARATOR . "\x6d\151\156\151\x6f\162\141\x6e\147\145\x63\x6f\x6d\x6d\x75\156\x69\x74\x79\142\165\151\x6c\144\145\x72" . DIRECTORY_SEPARATOR . "\x75\x74\151\154\151\x74\x79" . DIRECTORY_SEPARATOR . "\x43\x62\x55\164\x69\x6c\x69\x74\x69\x65\x73\x2e\160\x68\x70";
        if (!file_exists($mv)) {
            goto nn;
        }
        jimport("\155\x69\x6e\x69\x6f\x72\x61\x6e\x67\x65\x63\157\x6d\155\165\x6e\151\164\171\142\x75\151\x6c\x64\145\162\x2e\165\164\151\154\x69\x74\171\56\x43\x62\125\164\x69\154\x69\164\x69\145\163");
        $vN = CbUtilities::checkAndUpdateCBAttributes($user->id, $O2);
        if (!($vN == "\x46\x41\111\114\x45\x44\x5f\x43\102")) {
            goto hJ;
        }
        $eV = "\111\x74\x20\x61\160\x70\x65\141\x72\x73\x20\x74\x68\141\x74\40\x74\150\145\40\x63\x6f\155\160\162\157\146\151\x6c\145\162\40\164\x61\142\154\145\x20\x69\163\40\x6d\x69\x73\x73\151\156\x67\x20\146\x72\157\x6d\40\171\157\165\x72\x20\x64\x61\x74\x61\x62\141\x73\145\x2e\x20\124\150\x65\x20\141\164\164\162\x69\x62\165\x74\145\163\x20\x63\x6f\165\x6c\x64\x20\156\x6f\164\x20\142\145\x20\155\141\x70\x70\x65\144\x2e";
        $Ny = JFactory::getApplication("\163\151\x74\x65");
        $Ny->enqueueMessage($eV, "\x77\x61\x72\156\151\156\x67");
        hJ:
        nn:
        $jr = JFactory::getSession();
        if (strpos($lM, "\x61\144\155\151\x6e\151\163\x74\162\x61\x74\x6f\162")) {
            goto aF;
        }
        $jr->set("\165\x73\145\x72", $user);
        $jr->set("\x4d\x4f\x5f\x53\101\x4d\114\x5f\x4e\x41\115\x45\x49\104", $O2["\101\123\123\x45\122\x54\x49\117\116\137\x4e\x41\115\x45\137\x49\x44"]);
        $jr->set("\x4d\117\137\123\101\x4d\x4c\x5f\123\x45\123\123\111\x4f\x4e\137\111\116\x44\105\130", $O2["\x41\x53\x53\x45\x52\124\x49\117\116\x5f\123\105\x53\123\111\x4f\116\x5f\x49\x4e\x44\x45\x58"]);
        $jr->set("\x4d\x4f\137\x53\x41\x4d\x4c\137\x4c\117\107\107\x45\104\x5f\x49\x4e\137\127\x49\124\110\x5f\x49\104\x50", TRUE);
        $jr->set("\115\x4f\137\123\101\x4d\114\137\x49\104\x50\x5f\x55\x53\105\104", $gd["\151\x64\160\x5f\145\156\x74\151\x74\x79\137\x69\x64"]);
        aF:
        if (!($W6 == true)) {
            goto Tg;
        }
        $ME = UtilitiesSAML::gt_lk_trl();
        $Qa = 0;
        if (!($ME["\x4e\x6f\x6f\x66\125\163\x65\162\163"] <= $fv["\165\x73\162\154\155\x74"])) {
            goto r3;
        }
        UtilitiesSAML::rmvextnsns();
        $Qa = 1;
        $jA = UtilitiesSAML::get_message_and_cause($ME["\114\151\x63\x65\156\x73\x65\x45\170\x70\151\x72\x65\x64"], $Qa);
        UtilitiesSAML::show_error_messages($jA["\155\x73\x67"], $jA["\143\x61\x75\163\145"]);
        r3:
        Tg:
        $Ny->checkSession();
        $cw = $jr->getId();
        UtilitiesSAML::updateUsernameToSessionId($user->username, $cw);
        $wk = self::isLoginRportAddonEnable();
        $Qx = isset($SD["\x65\156\141\142\x6c\x65\x5f\x72\x6f\x6c\145\x5f\142\141\163\x65\144\x5f\x72\145\144\x69\x72\145\143\x74\x69\x6f\156"]) ? $SD["\145\156\x61\x62\x6c\x65\x5f\162\x6f\x6c\x65\x5f\142\x61\x73\145\x64\137\162\x65\144\x69\x72\x65\143\164\151\x6f\156"] : 0;
        if (!$Qx) {
            goto mz;
        }
        $DY = UtilitiesSAML::getUserGroupID($user->groups);
        $xH = isset($SD["\162\x6f\x6c\145\x5f\142\141\x73\145\x64\137\162\x65\x64\x69\x72\x65\143\x74\x5f\153\x65\171\137\x76\x61\154\x75\145"]) ? $SD["\x72\x6f\x6c\x65\x5f\142\141\163\145\x64\x5f\162\x65\x64\151\x72\145\x63\164\137\x6b\x65\x79\137\x76\141\154\x75\x65"] : '';
        $xH = json_decode($xH);
        $G7 = UtilitiesSAML::get_role_based_redirect_values($xH, $DY);
        if (empty($G7)) {
            goto aY;
        }
        $lM = $G7;
        aY:
        mz:
        $pP = UtilitiesSAML::IsUserSuperUser($user);
        $Ge = UtilitiesSAML::IsUserManager($user);
        $lM = Mo_saml_Local_Util::check_special_character_in_url($lM);
        if ($pP || $Ge && $tk) {
            goto YJ;
        }
        if (!$wk) {
            goto kv;
        }
        $EU = "\x45\x6e\x64\x20\125\163\145\x72\x20\114\157\147\151\x6e\x20\x50\x61\x67\145";
        plgUserMiniorangeloginreport::createLogs($user->username, $EU);
        kv:
        $Ny->redirect($lM);
        goto Hr;
        YJ:
        if (strpos($lM, "\141\x64\x6d\x69\x6e\151\163\164\x72\x61\x74\157\162")) {
            goto Xj;
        }
        if (!$wk) {
            goto eo;
        }
        $EU = "\105\x6e\x64\40\125\x73\x65\162\40\114\x6f\x67\x69\156\x20\x50\x61\x67\x65";
        plgUserMiniorangeloginreport::createLogs($user->username, $EU);
        eo:
        $Ny->redirect($lM);
        goto cc;
        Xj:
        $lM = JURI::root() . "\141\144\x6d\151\156\151\x73\164\x72\x61\x74\x6f\x72\57\x69\156\144\x65\x78\56\x70\150\160";
        $this->loginIntoAdminDashboardIfEnabled($lM, $user, $jr, $Ny, false);
        cc:
        Hr:
    }
    function RegisterCurrentUser($O2, $lM, $Hu, $Df, $gn, $FD, $Ny, $ZV, $gd)
    {
        $mv = JPATH_BASE . DIRECTORY_SEPARATOR . "\x6c\151\142\x72\141\162\151\x65\x73" . DIRECTORY_SEPARATOR . "\155\151\x6e\151\x6f\x72\141\156\147\x65\143\x6f\x6d\x6d\165\x6e\x69\164\x79\x62\x75\151\x6c\144\145\162" . DIRECTORY_SEPARATOR . "\x75\164\x69\x6c\x69\x74\171" . DIRECTORY_SEPARATOR . "\103\142\x55\164\x69\154\151\x74\151\145\163\56\x70\x68\160";
        $oF = 0;
        if (!file_exists($mv)) {
            goto J4;
        }
        jimport("\155\x69\x6e\x69\x6f\162\141\x6e\147\145\143\157\155\x6d\165\x6e\x69\164\171\142\x75\151\154\144\145\x72\x2e\x75\164\x69\154\x69\164\x79\x2e\103\142\x55\x74\x69\154\x69\164\151\145\163");
        $oF = CbUtilities::checkAndMapCBAttributes();
        J4:
        $qh = UtilitiesSAML::getCustomerDetails();
        $SD = UtilitiesSAML::getRoleMapping($gd);
        $tk = $qh["\x65\x6e\x61\142\154\145\137\155\141\156\141\147\145\x72\x5f\x6c\x6f\x67\x69\156"];
        $uo = $qh["\x74\162\x69\x73\164\x73"];
        $mb = 2;
        if (!isset($SD["\155\141\x70\160\151\x6e\x67\137\x76\141\x6c\165\145\x5f\x64\145\x66\x61\x75\x6c\164"])) {
            goto EX;
        }
        $mb = $SD["\155\141\160\x70\151\x6e\147\137\x76\141\154\x75\x65\137\x64\x65\146\141\x75\154\x74"];
        EX:
        $P8 = array();
        if (!isset($SD["\162\x6f\154\145\x5f\155\x61\x70\x70\x69\156\147\x5f\x6b\x65\x79\137\x76\x61\x6c\165\x65"])) {
            goto Ao;
        }
        $P8 = json_decode($SD["\x72\157\x6c\145\x5f\155\x61\160\x70\x69\156\147\137\x6b\145\x79\137\166\141\x6c\165\x65"]);
        Ao:
        $H5 = 0;
        if (!isset($SD["\145\x6e\141\x62\x6c\145\137\163\x61\x6d\x6c\137\x72\157\x6c\145\137\155\x61\160\160\x69\156\147"])) {
            goto TD;
        }
        $H5 = json_decode($SD["\x65\x6e\x61\142\154\145\137\163\x61\155\x6c\137\x72\x6f\x6c\145\x5f\x6d\x61\x70\160\x69\x6e\147"]);
        TD:
        $uo = Mo_saml_Local_Util::decrypt_value($uo);
        $zE["\x6e\x61\155\145"] = isset($Hu) && !empty($Hu) ? $Hu : $Df;
        $zE["\x75\x73\145\x72\156\x61\x6d\145"] = $Df;
        $zE["\x65\x6d\x61\x69\154"] = $zE["\145\x6d\x61\x69\154\61"] = $zE["\x65\155\x61\151\x6c\x32"] = JStringPunycode::emailToPunycode($gn);
        $zE["\160\x61\x73\163\167\x6f\x72\x64"] = $zE["\160\x61\x73\x73\167\x6f\162\144\61"] = $zE["\160\141\163\163\x77\x6f\x72\x64\62"] = JUserHelper::genRandomPassword();
        $lb = 0;
        if (!($uo == true)) {
            goto SR;
        }
        $ME = UtilitiesSAML::gt_lk_trl();
        $Qa = 0;
        if (!$ME["\x4c\151\x63\145\x6e\x73\145\105\170\160\151\162\145\x64"]) {
            goto vn;
        }
        UtilitiesSAML::rmvextnsns();
        $jA = UtilitiesSAML::get_message_and_cause($ME["\114\x69\x63\145\156\x73\145\x45\x78\160\151\x72\145\x64"], $Qa);
        UtilitiesSAML::show_error_messages($jA["\155\x73\x67"], $jA["\x63\141\165\163\145"]);
        vn:
        SR:
        if (!($H5 == 1)) {
            goto rf;
        }
        $OX = UtilitiesSAML::get_mapped_groups($P8, $ZV);
        if (empty($OX)) {
            goto lS;
        }
        foreach ($OX as $tu) {
            $zE["\147\162\157\x75\x70\x73"][] = $tu;
            M3:
        }
        U9:
        goto AY;
        lS:
        if (isset($SD["\x64\x6f\x5f\x6e\157\x74\137\x61\165\164\157\137\143\162\x65\x61\164\x65\x5f\165\x73\x65\162\x73"]) && $SD["\x64\x6f\x5f\156\157\164\137\141\x75\164\157\x5f\143\x72\x65\141\164\145\x5f\165\163\x65\x72\163"]) {
            goto Sg;
        }
        $zE["\147\162\157\165\x70\163"][] = $mb;
        goto Ro;
        Sg:
        $lb = 1;
        Ro:
        AY:
        rf:
        if (!$lb) {
            goto by;
        }
        $gc = JURI::root();
        echo "\40\40\40\40\x20\x20\x20\x20\40\40\x20\40\74\144\151\166\40\163\x74\171\154\145\x3d\x22\146\x6f\156\164\x2d\146\141\155\151\154\x79\x3a\x43\x61\x6c\151\x62\162\151\73\160\x61\144\144\151\156\147\72\60\x20\63\x25\73\42\x3e\xa\40\40\40\40\40\40\x20\x20\x20\x20\40\x20\x20\x20\x20\40\x3c\x64\151\166\40\x73\x74\x79\154\145\75\x22\x63\157\154\157\x72\72\40\43\141\x39\64\64\64\x32\x3b\142\x61\143\153\147\x72\x6f\x75\x6e\x64\x2d\x63\157\154\x6f\x72\72\40\43\x66\x32\x64\x65\x64\145\73\160\141\144\x64\x69\x6e\x67\x3a\40\x31\x35\x70\170\x3b\155\x61\x72\147\151\156\x2d\142\157\164\x74\157\x6d\72\40\62\60\160\170\x3b\164\145\x78\x74\x2d\x61\154\151\x67\x6e\72\x63\145\x6e\x74\x65\x72\x3b\142\x6f\x72\x64\145\162\x3a\x31\160\170\x20\x73\157\154\151\144\x20\43\105\x36\102\63\102\x32\73\146\157\156\164\x2d\163\151\x7a\145\x3a\61\x38\160\164\x3b\x22\x3e\x20\x45\x52\122\x4f\122\x3c\57\x64\151\x76\x3e\xa\40\40\x20\x20\x20\x20\x20\40\40\x20\x20\x20\x20\x20\x20\40\40\40\40\x20\74\x64\x69\166\40\x73\x74\171\154\145\75\x22\143\157\x6c\x6f\x72\72\x20\43\141\x39\64\64\x34\62\73\x66\x6f\156\164\55\x73\x69\x7a\145\72\61\x34\x70\164\73\x20\x6d\x61\162\147\x69\156\55\142\x6f\x74\164\157\x6d\x3a\62\60\x70\x78\x3b\x22\x3e\74\160\76\74\163\164\x72\x6f\x6e\147\x3e\x45\162\162\157\162\72\x20\x3c\x2f\163\164\x72\x6f\x6e\x67\76\x55\163\x65\x72\40\x69\163\40\x72\145\x73\164\162\151\x63\164\162\x65\x64\40\164\157\40\154\x6f\147\151\x6e\56\74\57\160\76\12\40\40\40\40\x20\40\40\x20\40\40\x20\40\40\x20\x20\40\40\40\x20\x20\x20\x20\x20\x20\74\160\76\120\154\x65\141\163\x65\40\x63\157\x6e\164\x61\x63\x74\40\171\157\x75\x72\x20\141\144\155\151\156\x69\x73\x74\x72\141\x74\x6f\162\x20\141\x6e\x64\x20\162\x65\160\157\x72\164\40\x74\150\145\40\x66\x6f\x6c\154\x6f\x77\151\156\147\40\145\162\162\157\x72\x3a\x3c\x2f\160\x3e\xa\40\40\40\40\x20\x20\x20\x20\x20\40\40\x20\x20\x20\40\40\x20\40\x20\x20\x20\x20\40\40\74\x70\x3e\74\163\164\162\157\156\147\76\120\157\x73\x73\151\142\154\x65\x20\x43\141\x75\x73\145\72\40\74\57\163\x74\162\x6f\x6e\147\x3e\x20\x4e\157\x6e\40\145\170\151\x73\x74\x69\x6e\147\40\x75\163\145\x72\x73\x20\x61\x72\145\40\156\157\x74\x20\x61\x6c\x6c\157\167\x65\x64\x20\x74\x6f\x20\x6c\x6f\147\151\156\56\x3c\x2f\x70\76\12\40\x20\x20\40\40\x20\40\40\x20\x20\40\x20\x20\x20\x20\x20\40\x20\40\x20\74\x2f\x64\x69\166\76\xa\x20\40\40\40\40\40\40\40\40\x20\40\x20\x20\x20\40\x20\x3c\x64\x69\x76\40\x73\x74\171\x6c\x65\75\x22\x6d\141\162\147\x69\x6e\x3a\63\45\73\x64\151\x73\160\x6c\x61\171\x3a\142\x6c\157\143\x6b\73\x74\x65\170\164\55\141\154\x69\x67\x6e\x3a\x63\x65\156\164\145\162\73\42\76\12\x20\x20\x20\40\x20\x20\x20\x20\x20\x20\x20\40\xa\40\x20\40\x20\40\40\40\40\40\40\40\x20\74\144\x69\166\x20\x73\164\x79\x6c\x65\75\x22\x6d\141\162\x67\151\156\72\63\45\x3b\x64\151\x73\x70\x6c\141\x79\x3a\x62\154\x6f\x63\153\73\164\145\x78\x74\55\141\x6c\x69\147\156\x3a\x63\145\x6e\164\145\162\73\x22\x3e\x3c\x61\x20\x68\162\x65\146\75\42\40";
        echo $gc;
        echo "\x20\42\76\12\x20\40\40\x20\40\40\40\40\x20\x20\40\40\x20\40\x20\x20\x3c\151\156\x70\165\164\x20\x73\x74\x79\154\145\x3d\x22\x70\141\x64\144\x69\156\147\x3a\x31\45\73\x77\x69\144\164\150\x3a\x31\60\x30\160\x78\73\142\141\143\x6b\147\x72\157\x75\x6e\x64\x3a\x20\x23\x30\x30\x39\61\x43\x44\x20\156\157\156\145\40\162\145\x70\145\141\x74\x20\163\143\x72\157\154\x6c\x20\x30\x25\x20\x30\45\x3b\143\165\162\x73\157\162\x3a\40\160\x6f\151\156\164\x65\x72\x3b\x66\x6f\x6e\164\55\x73\x69\x7a\145\x3a\61\65\x70\170\x3b\x62\x6f\162\x64\145\162\x2d\167\x69\x64\164\x68\72\x20\61\x70\x78\73\142\x6f\162\144\145\162\x2d\163\x74\171\154\145\72\40\163\157\154\151\144\x3b\142\x6f\x72\144\145\x72\55\x72\141\x64\151\x75\163\x3a\40\x33\160\x78\x3b\x77\x68\x69\x74\x65\55\x73\160\x61\143\145\72\40\x6e\157\x77\x72\141\160\x3b\142\157\x78\55\163\x69\172\x69\156\x67\x3a\x20\142\x6f\162\144\x65\x72\x2d\142\157\x78\73\x62\157\x72\144\145\x72\x2d\143\x6f\x6c\157\162\72\x20\43\60\60\x37\63\101\101\x3b\142\x6f\170\55\x73\x68\141\144\x6f\167\72\x20\x30\160\x78\x20\x31\x70\x78\x20\60\x70\x78\40\162\x67\x62\141\x28\61\x32\60\x2c\40\x32\x30\60\54\x20\62\63\60\54\x20\60\56\x36\51\x20\x69\156\x73\145\x74\x3b\x63\x6f\154\x6f\x72\72\40\43\x46\x46\106\73\x22\x20\164\x79\160\145\x3d\x22\142\165\164\x74\157\x6e\x22\40\x76\141\154\x75\145\x3d\x22\104\x6f\x6e\x65\42\x20\x6f\156\x43\154\x69\143\x6b\75\x22\x73\145\x6c\x66\x2e\143\154\x6f\x73\x65\x28\x29\x3b\x22\x3e\x3c\57\141\x3e\74\57\x64\151\x76\76\12\40\x20\40\40\40\40\40\x20\x20\x20\40\40";
        exit;
        by:
        jimport("\152\157\x6f\155\154\x61\56\x61\160\160\154\x69\x63\141\164\x69\157\x6e\x2e\143\157\155\x70\x6f\x6e\x65\x6e\x74\x2e\155\x6f\x64\x65\x6c");
        if (defined("\112\120\101\x54\110\137\103\117\x4d\x50\x4f\116\x45\x4e\x54")) {
            goto DP;
        }
        define("\112\x50\x41\x54\x48\137\103\x4f\x4d\120\x4f\116\x45\116\x54", JPATH_BASE . "\57\143\x6f\155\160\x6f\156\x65\x6e\x74\x73\57");
        DP:
        $user = new JUser();
        if ($user->bind($zE)) {
            goto Mw;
        }
        throw new Exception("\103\157\x75\x6c\144\40\156\157\164\40\142\151\x6e\x64\x20\x64\x61\x74\x61\x2e\x20\x45\x72\x72\x6f\162\72\40" . $user->getError());
        Mw:
        if (!($uo == true)) {
            goto fu;
        }
        $ME = UtilitiesSAML::gt_lk_trl();
        $Qa = 0;
        if (!($ME["\x4e\157\x6f\146\125\163\x65\x72\x73"] <= $qh["\165\163\x72\x6c\155\x74"])) {
            goto oH;
        }
        UtilitiesSAML::rmvextnsns();
        $Qa = 1;
        UtilitiesSAML::get_message_and_cause($ME["\x4c\151\x63\145\156\163\x65\105\170\160\x69\162\x65\144"], $Qa);
        UtilitiesSAML::show_error_messages($jA["\155\163\x67"], $jA["\143\141\x75\163\x65"]);
        oH:
        fu:
        $t7 = UtilitiesSAML::getCustomerDetails();
        $Gn = isset($t7["\x69\x67\x6e\157\162\x65\x5f\163\x70\145\x63\151\141\x6c\137\143\x68\x61\x72\141\x63\164\145\x72\163"]) ? $t7["\x69\147\x6e\157\162\x65\137\x73\160\x65\143\x69\x61\x6c\137\143\150\141\162\x61\143\164\x65\x72\163"] : 0;
        if ($Gn) {
            goto qN;
        }
        if (!$user->save()) {
            goto sc;
        }
        if (!($uo && $user->save())) {
            goto Vd;
        }
        UtilitiesSAML::addUser($qh["\165\x73\162\x6c\x6d\164"]);
        Vd:
        goto cK;
        sc:
        UtilitiesSAML::showErrorMessage();
        cK:
        goto e8;
        qN:
        if (preg_match("\x5b\47\x5d", $zE["\x65\155\141\x69\x6c"])) {
            goto Fh;
        }
        if (!$user->save()) {
            goto Fu;
        }
        if (!($uo == true)) {
            goto we;
        }
        UtilitiesSAML::addUser($qh["\165\163\x72\x6c\155\164"]);
        we:
        goto Nm;
        Fu:
        UtilitiesSAML::showErrorMessage();
        Nm:
        goto pn;
        Fh:
        UtilitiesSAML::saveUserInDB($zE, $FD);
        $lx = UtilitiesSAML::get_user_from_joomla($FD, $zE["\165\x73\145\162\156\141\155\x65"], $zE["\145\x6d\x61\x69\x6c"]);
        if (empty($lx)) {
            goto b0;
        }
        UtilitiesSAML::updateUserGroup($lx->id, $zE["\x67\162\157\x75\x70\x73"][0]);
        b0:
        if (!($uo == true)) {
            goto e2;
        }
        UtilitiesSAML::addUser($qh["\x75\163\162\154\155\164"]);
        e2:
        pn:
        e8:
        UtilitiesSAML::updateActivationStatusForUser($Df);
        $lx = UtilitiesSAML::get_user_from_joomla($FD, $Df, $gn);
        if (!$lx) {
            goto V6;
        }
        $user = JUser::getInstance($lx->id);
        $this->updateUserProfileAttributes($user->id, $O2, isset($gd["\165\x73\145\x72\137\160\x72\157\146\x69\154\145\x5f\x61\164\164\x72\151\142\165\x74\x65\x73"]) ? $gd["\x75\163\145\x72\137\160\x72\157\x66\151\x6c\x65\137\141\164\164\162\x69\142\165\x74\x65\163"] : '');
        if (!$oF) {
            goto Ek;
        }
        CbUtilities::mapAttributes($user, $O2);
        Ek:
        $GA = UtilitiesSAML::getPluginConfigurations($gd["\x69\144"]);
        $GA = current($GA);
        $GA = isset($GA["\x75\x73\x65\162\137\146\151\145\154\144\x5f\x61\x74\x74\162\x69\142\165\164\x65\163"]) ? $GA["\165\163\145\x72\x5f\146\x69\145\x6c\144\x5f\141\x74\x74\162\x69\x62\x75\164\x65\163"] : '';
        $this->updateUserFieldAttributes($user->id, $O2, $GA);
        $jr = JFactory::getSession();
        if (strpos($lM, "\x61\144\155\151\x6e\x69\x73\x74\162\x61\x74\157\x72")) {
            goto FU;
        }
        $jr->set("\x75\x73\145\x72", $user);
        $jr->set("\x4d\117\137\x53\101\115\x4c\137\x4e\x41\115\x45\111\x44", $O2["\101\x53\x53\x45\122\x54\111\x4f\x4e\x5f\116\x41\115\x45\137\111\104"]);
        $jr->set("\x4d\117\137\123\x41\x4d\114\x5f\x53\105\123\x53\x49\x4f\x4e\137\111\x4e\104\x45\x58", $O2["\x41\123\123\x45\122\x54\x49\117\x4e\x5f\x53\105\123\x53\x49\117\116\x5f\x49\x4e\x44\105\130"]);
        $jr->set("\115\117\137\x53\x41\x4d\x4c\x5f\x4c\117\x47\x47\105\x44\137\x49\x4e\137\127\x49\x54\x48\x5f\111\104\120", TRUE);
        $jr->set("\x4d\117\137\123\x41\x4d\114\x5f\x49\x44\120\x5f\125\x53\x45\x44", $gd["\151\x64\x70\x5f\145\x6e\x74\151\164\171\x5f\x69\144"]);
        FU:
        $Ny->checkSession();
        $cw = $jr->getId();
        UtilitiesSAML::updateUsernameToSessionId($user->username, $cw);
        $pP = UtilitiesSAML::IsUserSuperUser($user);
        $Ge = UtilitiesSAML::IsUserManager($user);
        $lM = Mo_saml_Local_Util::check_special_character_in_url($lM);
        $wk = self::isLoginRportAddonEnable();
        $Qx = isset($SD["\145\x6e\x61\142\x6c\x65\137\162\x6f\x6c\145\137\142\141\x73\x65\144\137\162\145\144\151\162\145\x63\164\151\x6f\156"]) ? $SD["\x65\156\141\142\154\x65\x5f\x72\x6f\154\x65\x5f\x62\x61\x73\145\x64\137\162\145\x64\x69\x72\145\x63\x74\x69\x6f\156"] : 0;
        if (!$Qx) {
            goto Tu;
        }
        $DY = UtilitiesSAML::getUserGroupID($user->groups);
        $xH = isset($SD["\x72\157\x6c\145\137\142\141\x73\145\x64\x5f\162\145\144\151\x72\145\x63\x74\137\x6b\145\x79\x5f\x76\141\154\165\145"]) ? $SD["\x72\157\154\145\137\142\x61\163\145\144\x5f\162\145\x64\151\162\145\x63\x74\137\153\145\x79\x5f\166\x61\x6c\165\145"] : '';
        $xH = json_decode($xH);
        $G7 = UtilitiesSAML::get_role_based_redirect_values($xH, $DY);
        if (empty($G7)) {
            goto XP;
        }
        $lM = $G7;
        XP:
        Tu:
        if ($pP || $Ge && $tk) {
            goto ow;
        }
        if (!$wk) {
            goto x7;
        }
        $EU = "\105\x6e\144\x20\125\163\x65\x72\40\x4c\157\x67\x69\156\x20\x50\141\147\x65";
        plgUserMiniorangeloginreport::createLogs($user->username, $EU);
        x7:
        $Ny->redirect($lM);
        goto Nd;
        ow:
        if (strpos($lM, "\x61\x64\155\x69\156\x69\163\164\x72\141\164\x6f\x72")) {
            goto x4;
        }
        if (!$wk) {
            goto bH;
        }
        $EU = "\105\x6e\144\x20\125\163\x65\162\x20\114\157\147\x69\156\x20\120\x61\x67\145";
        plgUserMiniorangeloginreport::createLogs($user->username, $EU);
        bH:
        $Ny->redirect($lM);
        goto T9;
        x4:
        $lM = JURI::root() . "\141\144\155\x69\156\x69\163\x74\x72\x61\164\157\x72\57\x69\x6e\144\145\170\56\160\150\160";
        $this->loginIntoAdminDashboardIfEnabled($lM, $user, $jr, $Ny, false);
        T9:
        Nd:
        V6:
    }
    function updateUserFieldAttributes($zF, $O2, $Xc)
    {
        $pS = UtilitiesSAML::getUserProfileData($O2, $Xc);
        UtilitiesSAML::removeIfExistsUserId($zF);
        foreach ($pS as $Za) {
            $l9 = $Za["\160\x72\x6f\x66\151\x6c\145\137\153\x65\x79"];
            $l9 = UtilitiesSAML::getIdFromFields($l9);
            $Dn = $Za["\x70\162\x6f\x66\151\154\145\x5f\x76\x61\154\165\x65"];
            $hV = new stdClass();
            $hV->field_id = $l9->id;
            $hV->item_id = $zF;
            $hV->value = $Dn;
            JFactory::getDbo()->insertObject("\43\x5f\x5f\146\151\145\x6c\144\x73\137\x76\141\x6c\165\x65\163", $hV);
            NL:
        }
        gX:
    }
    function updateUserProfileAttributes($i1, $CB, $Q2)
    {
        $ZZ = UtilitiesSAML::getUserProfileData($CB, $Q2);
        $tV = UtilitiesSAML::getUserProfileDataFromTable($i1);
        if (!(isset($ZZ) && !empty($ZZ))) {
            goto Ht;
        }
        $cB = UtilitiesSAML::selectMaxOrdering($i1);
        $F7 = JFactory::getDbo();
        foreach ($ZZ as $vn) {
            $l9 = "\160\x72\x6f\x66\151\x6c\145\x2e" . strtolower($vn["\x70\162\x6f\x66\151\x6c\145\x5f\x6b\145\171"]);
            $Dn = $vn["\x70\162\157\146\x69\154\145\x5f\166\x61\154\x75\145"];
            if (in_array($l9, $tV)) {
                goto nV;
            }
            $bm = $F7->getQuery(true);
            $Bi = array("\x75\163\145\162\x5f\151\144", "\x70\162\x6f\146\x69\154\x65\137\x6b\145\x79", "\160\x72\x6f\146\151\x6c\145\x5f\166\141\x6c\x75\145", "\157\x72\x64\145\x72\151\x6e\x67");
            $S7 = array($i1, $F7->quote($l9), $F7->quote($Dn), ++$cB);
            $bm->insert($F7->quoteName("\43\x5f\x5f\165\163\145\162\137\x70\x72\x6f\x66\x69\154\x65\x73"))->columns($F7->quoteName($Bi))->values(implode("\54", $S7));
            $F7->setQuery($bm);
            $F7->execute();
            goto BN;
            nV:
            $bm = $F7->getQuery(true);
            $Za = array($F7->quoteName("\160\162\157\x66\151\154\x65\x5f\x76\141\154\165\x65") . "\40\75\x20" . $F7->quote($Dn));
            $DB = array($F7->quoteName("\x75\x73\145\162\137\x69\144") . "\x20\x3d\x20" . $F7->quote($i1), $F7->quoteName("\x70\162\157\x66\151\x6c\145\x5f\x6b\145\171") . "\x20\x3d\x20" . $F7->quote($l9));
            $bm->update($F7->quoteName("\x23\x5f\x5f\x75\163\x65\x72\x5f\x70\162\157\146\x69\154\145\163"))->set($Za)->where($DB);
            $F7->setQuery($bm);
            $F7->execute();
            BN:
            U1:
        }
        d0:
        Ht:
    }
    function addOrRemoveUserFromGroup($OX, $mb, $user, $eW)
    {
        if (empty($OX)) {
            goto XD;
        }
        $b5 = 1;
        foreach ($OX as $tu) {
            self::addUserGroup($user, $tu);
            if (!$b5) {
                goto EE;
            }
            if ($eW) {
                goto R4;
            }
            foreach ($user->groups as $Nr) {
                if (!($Nr != $tu)) {
                    goto hX;
                }
                JUserHelper::removeUserFromGroup($user->id, $Nr);
                hX:
                Ln:
            }
            iB:
            $b5 = 0;
            R4:
            EE:
            X4:
        }
        j5:
        goto QL;
        XD:
        self::addUserGroup($user, $mb);
        if ($eW) {
            goto u5;
        }
        foreach ($user->groups as $Nr) {
            if (!($Nr != $mb)) {
                goto ik;
            }
            JUserHelper::removeUserFromGroup($user->id, $Nr);
            ik:
            B2:
        }
        JT:
        u5:
        QL:
    }
    function addUserGroup($user, $mb)
    {
        $xp = UtilitiesSAML::getCustomerDetails();
        if ($xp["\151\147\x6e\x6f\x72\x65\137\163\x70\145\x63\151\x61\154\x5f\143\x68\x61\162\141\x63\x74\x65\x72\x73"] != 1) {
            goto IA;
        }
        UtilitiesSAML::updateUserGroup($user->id, $mb);
        goto Y1;
        IA:
        JUserHelper::addUserToGroup($user->id, $mb);
        Y1:
    }
    function loginIntoAdminDashboardIfEnabled($lM, $user, $jr, $Ny, $r5)
    {
        $fv = UtilitiesSAML::getCustomerDetails();
        $mS = isset($fv["\145\x6e\141\x62\154\x65\x5f\141\x64\155\151\156\137\x72\x65\x64\151\x72\x65\143\164"]) ? $fv["\145\156\x61\x62\154\x65\x5f\x61\x64\155\x69\156\x5f\x72\x65\144\151\x72\145\143\164"] : 0;
        $Uh = isset($fv["\145\156\141\x62\x6c\x65\x5f\x6d\x61\x6e\141\147\145\162\x5f\154\x6f\147\x69\156"]) ? $fv["\145\x6e\141\142\x6c\145\x5f\155\x61\x6e\141\x67\145\162\137\x6c\x6f\x67\151\156"] : 0;
        $pP = UtilitiesSAML::IsUserSuperUser($user);
        $Ge = UtilitiesSAML::IsUserManager($user);
        if ($mS && $pP || $Uh && $Ge) {
            goto Jd;
        }
        $Ny->redirect(urldecode($lM));
        goto gP;
        Jd:
        $NS = time();
        $kx = $fv["\141\160\x69\137\153\x65\171"];
        $eS = $NS . "\x3a" . $kx . "\72" . $user->username;
        $FR = $fv["\x63\165\x73\x74\x6f\155\145\x72\x5f\x74\157\x6b\x65\x6e"];
        $eS = AESEncryption::encrypt_data($eS, $FR);
        $mp = $NS + 30;
        $lB = setcookie("\x6d\x6f\163\x61\x6d\154\162\x65\x64\151\x72\x65\x63\x74", $eS, $mp, "\x2f");
        $Xo = $jr->get("\115\x4f\x5f\x53\x41\x4d\x4c\x5f\116\x41\x4d\x45\111\104");
        $Gv = $jr->set("\115\117\137\x53\x41\115\x4c\137\x53\105\123\x53\111\117\116\x5f\x49\x4e\104\x45\130");
        $eS = $Gv . "\x3a" . $Xo . "\x3a" . urlencode($jr->get("\115\117\137\123\x41\115\x4c\137\111\104\x50\137\x55\123\x45\104"));
        $eS = AESEncryption::encrypt_data($eS, $FR);
        $lB = setcookie("\x5f\x6d\x6f\x73\x65\x73\x73\x69\157\x6e", $eS, $mp, "\57");
        $UB = JURI::base();
        $yG = $UB . "\x61\x64\x6d\151\x6e\x69\163\x74\162\141\164\157\x72";
        $Ny->redirect($yG);
        gP:
    }
    function generateMetadata($t7, $H3 = false)
    {
        $lx = Mo_saml_Local_Util::getCustomerDetails();
        $E0 = $lx["\x6f\x72\x67\141\156\x69\172\141\164\x69\x6f\x6e\137\x6e\141\155\x65"];
        $BG = $lx["\x6f\162\x67\x61\x6e\x69\x7a\x61\x74\x69\157\x6e\x5f\144\x69\163\x70\x6c\x61\171\x5f\156\141\x6d\x65"];
        $UC = $lx["\x6f\x72\147\x61\156\x69\x7a\x61\164\x69\x6f\x6e\137\165\x72\154"];
        $yA = $lx["\x74\x65\x63\x68\x5f\160\145\x72\x5f\156\141\155\145"];
        $kt = $lx["\x74\145\x63\x68\137\145\x6d\x61\151\154\x5f\141\x64\144"];
        $vo = $lx["\163\165\x70\x70\x6f\162\x74\x5f\x70\145\162\137\x6e\x61\x6d\145"];
        $aA = $lx["\163\165\x70\160\157\x72\x74\137\145\x6d\141\151\154\x5f\141\144\144"];
        $Ol = '';
        $FM = '';
        $gc = JURI::root();
        if (isset($lx["\163\x70\x5f\142\x61\163\145\137\165\x72\x6c"])) {
            goto Ms;
        }
        $Ol = $gc;
        $FM = $gc . "\x70\154\165\x67\x69\156\x73\x2f\x61\165\164\x68\145\x6e\164\151\x63\x61\x74\x69\x6f\x6e\x2f\x6d\151\x6e\x69\157\x72\x61\x6e\147\x65\163\x61\x6d\x6c";
        goto wd;
        Ms:
        $Ol = $lx["\163\160\137\x62\x61\x73\x65\137\x75\x72\154"];
        $FM = $lx["\163\x70\x5f\x65\x6e\x74\x69\x74\171\x5f\x69\144"];
        wd:
        $Pu = $Ol . "\77\155\157\162\x65\161\165\145\163\x74\x3d\141\x63\x73";
        $uh = $Ol . "\151\x6e\144\x65\170\56\x70\150\160\77\157\160\164\151\157\x6e\75\143\157\155\x5f\165\x73\145\x72\x73\46\x61\x6d\160\73\x74\141\x73\153\x3d\154\157\147\157\x75\x74";
        $Yn = UtilitiesSAML::get_public_private_certificate($t7, "\x70\x75\x62\154\151\x63\x5f\x63\145\x72\x74\151\x66\x69\x63\x61\x74\145");
        if ($Yn == null || $Yn == '' || empty($Yn)) {
            goto UU;
        }
        $mc = JPATH_BASE . DIRECTORY_SEPARATOR . "\x70\x6c\165\147\151\156\x73" . DIRECTORY_SEPARATOR . "\x61\x75\x74\x68\145\156\x74\x69\143\141\164\151\x6f\156" . DIRECTORY_SEPARATOR . "\155\x69\156\151\x6f\162\x61\156\x67\145\163\x61\x6d\154" . DIRECTORY_SEPARATOR . "\x73\x61\x6d\x6c\x32" . DIRECTORY_SEPARATOR . "\143\145\162\x74" . DIRECTORY_SEPARATOR . "\x43\165\x73\x74\157\x6d\x50\x75\x62\x6c\x69\x63\x43\x65\x72\x74\151\x66\x69\x63\x61\164\x65\56\143\162\x74";
        goto g8;
        UU:
        $mc = JPATH_BASE . DIRECTORY_SEPARATOR . "\160\x6c\x75\147\x69\156\x73" . DIRECTORY_SEPARATOR . "\x61\x75\x74\x68\x65\x6e\164\x69\143\141\x74\x69\157\x6e" . DIRECTORY_SEPARATOR . "\155\x69\156\x69\x6f\162\141\156\x67\145\x73\x61\x6d\x6c" . DIRECTORY_SEPARATOR . "\x73\141\x6d\x6c\x32" . DIRECTORY_SEPARATOR . "\143\x65\162\164" . DIRECTORY_SEPARATOR . "\x73\x70\55\x63\145\x72\x74\151\x66\151\143\x61\164\145\56\x63\x72\x74";
        g8:
        $mc = file_get_contents($mc);
        $mc = UtilitiesSAML::desanitize_certificate($mc);
        if ($H3) {
            goto JF;
        }
        header("\x43\157\156\164\x65\156\x74\x2d\x54\171\160\x65\72\x20\164\145\x78\164\57\x78\x6d\154");
        goto is;
        JF:
        header("\103\x6f\x6e\x74\145\x6e\x74\55\104\151\163\160\157\x73\x69\164\x69\x6f\x6e\x3a\x20\141\164\164\x61\x63\x68\155\145\156\x74\73\x20\146\151\x6c\x65\x6e\x61\x6d\x65\x20\75\40\x22\x4d\x65\164\141\144\141\x74\141\x2e\170\155\x6c\42");
        is:
        echo "\74\77\170\x6d\154\x20\x76\145\162\163\x69\x6f\x6e\75\42\61\56\60\x22\77\x3e\12\11\x9\74\x6d\144\72\x45\156\164\x69\x74\171\x44\145\x73\143\162\x69\160\164\157\x72\40\x78\155\154\x6e\163\72\155\144\75\42\165\x72\x6e\x3a\x6f\x61\163\x69\x73\72\156\141\155\145\x73\72\x74\143\x3a\123\101\115\x4c\72\62\56\60\x3a\155\x65\164\141\144\141\x74\141\42\40\166\141\x6c\151\x64\x55\156\x74\x69\x6c\75\x22\x32\60\62\64\55\x30\x36\x2d\x32\x38\124\62\63\x3a\x35\x39\72\65\x39\x5a\x22\x20\143\x61\x63\x68\145\104\165\162\141\164\151\157\x6e\75\x22\x50\x54\x31\x34\64\x36\x38\x30\x38\x37\x39\62\x53\42\x20\x65\156\x74\151\164\x79\x49\x44\75\42" . $FM . "\x22\76\12\x9\x9\40\x20\74\155\144\x3a\x53\120\x53\x53\117\x44\x65\x73\x63\162\x69\160\x74\x6f\x72\x20\101\x75\x74\x68\x6e\x52\x65\x71\165\145\163\x74\163\x53\151\x67\x6e\145\144\x3d\42\x74\162\165\x65\42\40\127\141\156\x74\101\x73\x73\145\162\164\151\x6f\156\x73\123\x69\x67\156\x65\144\x3d\42\x74\x72\165\x65\42\40\160\162\x6f\164\x6f\x63\157\154\123\165\160\160\x6f\x72\164\105\156\x75\x6d\x65\x72\141\164\x69\x6f\156\x3d\42\x75\162\x6e\72\x6f\x61\x73\x69\163\72\156\141\x6d\x65\x73\72\164\143\x3a\x53\x41\115\x4c\72\62\56\x30\x3a\x70\x72\157\164\157\x63\x6f\x6c\x22\x3e\12\x9\x9\11\74\155\x64\x3a\113\145\x79\104\x65\163\143\x72\x69\160\164\157\x72\40\165\163\x65\x3d\42\163\x69\x67\x6e\x69\156\147\42\76\xa\x9\x9\x9\40\x20\x3c\x64\163\x3a\113\x65\x79\x49\156\x66\157\40\x78\x6d\x6c\156\x73\72\x64\x73\75\x22\x68\x74\164\x70\72\57\57\167\167\167\56\x77\x33\x2e\x6f\162\147\x2f\x32\60\x30\60\x2f\60\x39\57\x78\x6d\x6c\x64\163\151\x67\x23\x22\76\xa\11\11\x9\x9\74\x64\163\72\x58\x35\x30\x39\x44\141\164\x61\76\xa\11\11\x9\x9\x20\x20\74\144\x73\x3a\x58\65\x30\71\x43\x65\x72\164\151\146\x69\x63\141\164\145\76" . $mc . "\74\x2f\x64\x73\72\x58\65\60\71\103\x65\x72\x74\x69\x66\x69\x63\141\x74\x65\x3e\xa\11\11\11\x9\74\x2f\144\x73\72\130\x35\60\71\x44\141\164\141\76\xa\11\11\11\40\40\x3c\57\144\163\72\x4b\x65\171\111\156\x66\157\76\xa\11\11\11\74\57\155\144\72\113\145\x79\104\145\x73\143\x72\151\160\164\157\162\76\xa\11\x9\x9\x3c\155\144\72\113\x65\x79\104\145\163\143\x72\151\160\164\157\x72\x20\165\163\x65\x3d\42\x65\156\143\162\x79\x70\164\x69\x6f\x6e\x22\76\12\x9\x9\11\x20\x20\x3c\144\163\x3a\113\x65\x79\111\x6e\146\157\40\170\155\x6c\x6e\163\x3a\144\163\x3d\42\150\x74\164\160\x3a\57\x2f\167\167\x77\56\x77\x33\56\157\162\147\x2f\x32\60\x30\x30\57\60\71\x2f\170\155\x6c\x64\x73\x69\x67\43\42\76\xa\11\11\x9\x9\74\x64\163\x3a\x58\x35\x30\x39\104\x61\164\141\76\xa\x9\11\x9\11\x20\x20\x3c\x64\163\x3a\130\65\x30\71\x43\145\162\x74\151\146\x69\143\x61\x74\145\x3e" . $mc . "\x3c\x2f\x64\163\72\x58\x35\x30\71\x43\x65\162\164\151\x66\151\x63\141\164\x65\x3e\xa\11\11\11\11\74\57\144\163\x3a\x58\65\x30\71\x44\x61\164\141\76\12\x9\x9\x9\x20\x20\74\x2f\144\x73\x3a\x4b\x65\171\x49\x6e\146\x6f\76\xa\x9\x9\x9\x3c\57\155\144\72\x4b\145\171\104\x65\x73\143\162\x69\x70\164\x6f\x72\76\12\11\11\x9\x3c\155\x64\x3a\x53\x69\x6e\147\154\145\114\x6f\x67\157\x75\x74\123\x65\162\166\151\x63\145\40\102\x69\x6e\x64\151\x6e\147\x3d\42\165\x72\156\72\x6f\141\x73\151\x73\x3a\156\x61\x6d\145\x73\x3a\164\x63\72\x53\x41\x4d\114\72\x32\56\x30\x3a\142\x69\x6e\x64\x69\x6e\147\163\x3a\110\124\x54\120\x2d\120\x4f\x53\x54\42\x20\x4c\x6f\x63\141\164\x69\x6f\x6e\x3d\x22" . $uh . "\42\x2f\x3e\12\x9\x9\11\x3c\155\144\72\x53\151\156\x67\x6c\x65\114\157\x67\x6f\x75\x74\x53\145\x72\166\151\143\x65\x20\x42\151\156\x64\x69\156\147\75\42\x75\x72\156\x3a\157\x61\163\151\x73\x3a\x6e\x61\155\145\x73\72\x74\143\72\x53\x41\115\114\x3a\x32\x2e\60\72\142\151\156\144\x69\x6e\x67\163\x3a\x48\124\x54\120\55\122\145\144\x69\162\x65\x63\x74\x22\x20\114\157\x63\x61\164\x69\157\x6e\75\x22" . $uh . "\42\57\x3e\12\11\11\11\74\155\x64\72\116\141\x6d\145\111\x44\x46\x6f\162\x6d\x61\x74\76\x75\x72\x6e\x3a\x6f\141\x73\x69\x73\72\x6e\141\x6d\145\163\x3a\x74\143\72\123\101\115\x4c\72\61\56\x31\72\x6e\141\x6d\145\151\x64\x2d\x66\x6f\162\155\x61\x74\x3a\x65\x6d\141\x69\x6c\x41\144\x64\x72\x65\x73\x73\x3c\x2f\x6d\x64\x3a\116\x61\155\145\111\104\x46\157\162\155\141\164\x3e\12\x9\11\11\74\x6d\x64\x3a\x4e\141\155\145\x49\104\106\157\162\x6d\141\x74\76\x75\x72\156\x3a\157\x61\163\x69\163\x3a\x6e\x61\155\145\x73\x3a\x74\143\x3a\123\x41\115\x4c\x3a\x31\x2e\x31\72\x6e\141\155\145\x69\x64\55\x66\x6f\x72\x6d\141\164\x3a\165\156\163\160\145\143\151\146\x69\145\x64\74\x2f\155\144\72\116\141\x6d\x65\x49\x44\x46\157\162\x6d\141\164\x3e\xa\11\11\11\x3c\155\144\72\116\141\x6d\145\x49\104\x46\157\x72\x6d\x61\164\x3e\x75\162\156\x3a\157\141\x73\x69\x73\x3a\156\x61\x6d\145\x73\x3a\164\x63\72\123\x41\x4d\114\72\62\56\60\72\156\141\x6d\x65\x69\144\55\146\157\162\155\141\x74\72\160\145\162\163\x69\x73\x74\145\156\x74\x3c\57\155\144\72\x4e\141\x6d\145\x49\104\106\x6f\x72\x6d\141\x74\x3e\12\11\11\11\x3c\x6d\144\72\x4e\x61\155\145\111\104\x46\x6f\162\x6d\141\x74\x3e\165\x72\156\72\x6f\141\x73\x69\x73\72\x6e\141\155\x65\163\x3a\164\x63\x3a\123\101\115\114\72\x32\x2e\x30\72\x6e\141\155\145\x69\144\x2d\146\157\162\155\x61\164\72\164\162\x61\156\163\x69\x65\x6e\x74\74\57\x6d\x64\x3a\x4e\x61\x6d\145\x49\104\106\x6f\162\155\141\164\x3e\xa\x9\x9\11\74\x6d\x64\72\x41\163\163\145\x72\164\x69\x6f\156\103\157\156\x73\165\x6d\145\162\123\x65\162\x76\151\x63\x65\x20\x42\x69\156\x64\x69\156\147\x3d\42\165\x72\x6e\x3a\157\x61\163\x69\163\x3a\x6e\141\x6d\x65\163\x3a\164\x63\x3a\x53\101\x4d\114\72\x32\x2e\x30\72\x62\151\156\x64\151\x6e\147\x73\72\110\124\x54\x50\x2d\120\117\123\x54\x22\40\114\157\x63\141\164\151\157\156\x3d\x22" . $Pu . "\42\40\x69\x6e\144\x65\x78\x3d\x22\x31\x22\x2f\76\xa\x9\11\x20\x20\x3c\57\x6d\144\x3a\x53\120\x53\123\117\x44\x65\163\x63\162\x69\x70\164\157\162\76\12\x9\11\x20\x20\x3c\x6d\144\72\117\x72\x67\141\x6e\151\172\x61\164\x69\x6f\x6e\x3e\xa\40\x20\x20\40\40\x20\40\x20\40\40\x20\40\x3c\x6d\x64\x3a\117\162\x67\x61\156\x69\x7a\141\164\151\x6f\x6e\x4e\x61\155\145\x20\x78\155\x6c\x3a\154\141\156\x67\75\x22\x65\x6e\x2d\x55\x53\42\76" . $E0 . "\74\x2f\155\x64\72\117\162\147\x61\156\x69\172\x61\164\x69\157\x6e\x4e\141\x6d\x65\76\xa\x20\x20\x20\40\40\40\x20\x20\x20\40\40\40\74\x6d\x64\x3a\x4f\162\x67\141\x6e\151\172\x61\x74\x69\157\x6e\x44\x69\163\x70\x6c\x61\x79\116\141\155\145\40\x78\x6d\154\x3a\154\x61\x6e\147\x3d\42\x65\156\x2d\x55\123\x22\x3e" . $BG . "\x3c\57\155\144\x3a\117\162\x67\x61\x6e\151\x7a\141\164\x69\157\x6e\104\x69\x73\160\x6c\141\x79\116\x61\x6d\x65\x3e\12\x20\x20\x20\40\40\x20\40\40\40\40\40\x20\74\x6d\144\x3a\x4f\162\147\141\x6e\x69\x7a\141\164\151\157\156\x55\122\114\x20\x78\x6d\x6c\x3a\154\141\156\147\x3d\42\145\156\55\125\123\x22\x3e" . $UC . "\74\57\x6d\144\72\x4f\x72\x67\141\x6e\151\172\x61\x74\151\157\156\125\x52\114\x3e\12\40\x20\x20\x20\x20\40\x20\x20\x20\40\x3c\57\155\x64\x3a\117\x72\147\141\x6e\151\172\x61\x74\x69\157\156\76\12\40\x20\40\40\40\40\40\40\x20\40\74\155\x64\x3a\x43\157\156\164\141\x63\x74\120\x65\162\163\157\156\x20\x63\157\x6e\164\141\143\164\124\171\160\x65\75\42\164\x65\x63\150\x6e\x69\x63\141\154\42\x3e\xa\40\x20\40\x20\40\x20\40\40\x20\40\x20\x20\74\x6d\144\72\107\x69\166\145\x6e\x4e\x61\x6d\x65\76" . $yA . "\74\x2f\155\x64\72\107\151\166\145\156\x4e\141\155\145\76\12\x20\x20\40\40\40\40\40\x20\x20\x20\x20\x20\74\x6d\x64\72\x45\155\141\x69\x6c\x41\x64\x64\162\145\x73\163\x3e" . $kt . "\74\57\x6d\x64\72\105\155\141\x69\154\x41\144\x64\x72\145\163\x73\x3e\12\40\x20\40\x20\x20\40\x20\x20\40\x20\74\57\155\x64\x3a\x43\157\156\x74\x61\143\x74\x50\x65\x72\x73\x6f\156\76\xa\40\40\40\x20\x20\40\x20\x20\40\40\74\155\x64\x3a\103\x6f\156\x74\x61\x63\x74\x50\145\162\x73\157\156\x20\143\x6f\156\164\141\143\x74\124\x79\x70\145\75\42\x73\165\160\x70\157\162\x74\x22\76\12\x20\x20\40\40\x20\40\x20\40\x20\x20\x20\x20\x3c\155\144\72\x47\x69\x76\x65\x6e\x4e\x61\x6d\145\76" . $vo . "\74\x2f\x6d\144\x3a\107\151\x76\145\x6e\x4e\x61\155\x65\76\xa\x20\40\x20\x20\40\40\x20\x20\40\40\x20\40\74\x6d\x64\72\x45\x6d\x61\x69\x6c\x41\x64\144\162\x65\163\x73\x3e" . $aA . "\74\x2f\x6d\144\x3a\105\x6d\141\x69\x6c\101\144\x64\162\145\x73\x73\x3e\xa\40\40\40\x20\40\40\40\x20\40\x20\x3c\x2f\x6d\x64\x3a\x43\157\156\x74\x61\x63\x74\120\x65\162\x73\x6f\156\76\12\x20\40\40\x20\40\40\x20\x20\74\x2f\155\144\72\105\x6e\164\151\x74\x79\104\145\x73\x63\162\151\x70\x74\x6f\162\x3e";
        exit;
    }
    function adminDashboardLogin($t7)
    {
        $fv = UtilitiesSAML::getCustomerDetails();
        $mS = isset($fv["\x65\x6e\x61\142\x6c\145\137\x61\x64\155\151\x6e\x5f\162\145\x64\x69\162\145\x63\x74"]) ? $fv["\145\x6e\x61\x62\x6c\145\137\x61\144\x6d\x69\x6e\x5f\162\x65\x64\151\x72\145\143\x74"] : 0;
        $Fa = isset($fv["\x65\156\x61\142\x6c\145\x5f\x6d\x61\156\x61\147\145\162\137\154\157\x67\151\x6e"]) ? $fv["\145\156\141\142\x6c\x65\137\x6d\x61\156\141\147\145\x72\x5f\x6c\x6f\x67\151\156"] : 0;
        if (!($mS || $Fa)) {
            goto cu;
        }
        $Ol = '';
        $FM = '';
        if (!isset($t7["\x73\160\x5f\142\141\163\x65\x5f\x75\162\x6c"])) {
            goto rU;
        }
        $Ol = $t7["\x73\160\x5f\142\x61\x73\145\137\x75\x72\154"];
        $FM = $t7["\x73\160\x5f\x65\156\164\151\x74\171\137\x69\x64"];
        rU:
        $gc = JURI::root();
        if (!empty($Ol)) {
            goto Xi;
        }
        $Ol = $gc;
        Xi:
        if (!empty($FM)) {
            goto l9;
        }
        $FM = $gc . "\160\x6c\165\x67\x69\x6e\163\x2f\x61\165\x74\x68\x65\156\x74\x69\143\x61\x74\x69\157\x6e\57\155\151\156\151\x6f\x72\141\156\147\145\163\x61\155\154";
        l9:
        jimport("\x6d\x69\156\151\x6f\162\141\x6e\x67\x65\163\x61\155\154\160\154\x75\x67\151\156\x2e\x75\x74\151\154\151\x74\x79\x2e\x65\156\x63\162\x79\x70\164\151\157\x6e");
        $eS = $_COOKIE["\x6d\157\163\x61\155\154\x72\145\x64\151\x72\145\143\x74"];
        $cO = $fv["\143\165\x73\164\x6f\x6d\x65\162\137\x74\157\x6b\x65\x6e"];
        $GH = $fv["\141\160\151\x5f\153\145\x79"];
        $eS = AESEncryption::decrypt_data($eS, $cO);
        $Cd = array();
        $Cd = explode("\x3a", $eS);
        $a8 = $Cd[0];
        $dj = $Cd[1];
        $Df = $Cd[2];
        $NS = time();
        if (!($dj == $GH && $NS - $a8 < 30)) {
            goto H2;
        }
        setcookie("\x6d\x6f\163\141\155\154\162\x65\x64\x69\x72\145\143\x74", "\55\61", time() - 100, "\57");
        unset($_COOKIE["\x6d\x6f\x73\141\155\154\x72\x65\144\151\x72\145\x63\164"]);
        $NS = time();
        $eS = $NS . "\72" . $GH;
        $eS = AESEncryption::encrypt_data($eS, $cO);
        $mp = $NS + 30;
        $lB = setcookie("\x6d\157\163\141\155\154\141\x75\164\x68\x61\x64\x6d\151\x6e", $eS, $mp, "\x2f");
        $yG = $Ol . "\x61\x64\x6d\151\x6e\151\x73\164\162\141\x74\157\x72\x2f\x69\x6e\x64\145\170\56\160\x68\x70";
        $eS = $_COOKIE["\137\x6d\x6f\163\x65\x73\x73\151\x6f\156"];
        setcookie("\x5f\155\157\163\145\163\163\151\x6f\156", "\55\61", time() - 100, "\x2f");
        unset($_COOKIE["\137\x6d\x6f\163\x65\163\163\x69\157\156"]);
        $eS = AESEncryption::decrypt_data($eS, $cO);
        $AF = array();
        $AF = explode("\72", $eS);
        $M7 = $AF[0];
        $lH = $AF[1];
        $w3 = urldecode($AF[2]);
        $jr = JFactory::getSession();
        $jr->set("\x4d\117\x5f\123\x41\115\114\137\x4e\101\x4d\x45\x49\x44", $lH);
        $jr->set("\x4d\x4f\x5f\x53\x41\x4d\x4c\x5f\x53\105\123\123\x49\117\116\x5f\111\116\x44\x45\130", $M7);
        $jr->set("\115\x4f\x5f\x53\x41\x4d\x4c\x5f\114\117\107\x47\105\x44\137\111\x4e\137\127\x49\124\110\x5f\x49\104\x50", TRUE);
        $jr->set("\115\x4f\137\x53\x41\x4d\114\137\x49\x44\x50\x5f\x55\123\x45\104", $w3);
        $jr->set("\x75\x73\145\x72\156\141\x6d\145", $Df);
        echo "\74\x66\157\162\155\40\x69\144\75\42\x6d\x6f\163\x61\155\154\137\x61\144\x6d\151\156\x6c\x6f\147\x69\156\x66\157\162\155\42\x20\x61\143\x74\x69\x6f\156\x3d\x22" . $yG . "\42\x20\155\145\164\150\157\x64\75\x22\160\157\163\164\42\x3e\12\x9\11\11\11\11\x3c\151\156\x70\165\x74\x20\164\x79\x70\x65\75\x22\x68\151\144\x64\145\x6e\42\x20\x6e\x61\155\x65\75\42\x75\x73\145\x72\x6e\141\155\x65\42\x20\x76\x61\154\165\145\x3d\x22" . $Df . "\42\40\57\76\xa\x9\x9\11\11\11\x3c\x69\x6e\x70\165\164\40\x74\x79\x70\x65\75\42\x68\151\144\x64\x65\x6e\x22\40\156\141\x6d\145\x3d\42\x70\141\163\x73\x77\144\x22\x20\x76\x61\x6c\165\145\75\42\x70\141\x73\163\167\144\x22\40\x2f\76\xa\11\x9\x9\x9\x9\x3c\x69\x6e\160\165\x74\x20\x74\171\x70\145\75\x22\x68\x69\144\x64\x65\156\x22\40\156\141\155\145\x3d\42\157\160\x74\151\x6f\156\x22\x20\x76\141\x6c\x75\x65\x3d\x22\x63\157\155\137\154\157\x67\151\156\42\76\xa\11\x9\x9\11\11\74\151\156\160\x75\164\40\x74\171\x70\145\x3d\x22\x68\x69\144\x64\x65\x6e\x22\x20\x6e\x61\x6d\x65\x3d\42\164\141\163\x6b\x22\x20\x76\141\154\165\x65\75\x22\154\157\147\151\156\42\76\xa\x9\x9\x9\11\11\74\x69\x6e\x70\x75\x74\x20\164\171\x70\145\x3d\x22\x68\151\144\x64\x65\x6e\42\40\156\141\155\x65\x3d\x22\x72\145\x74\165\162\x6e\x22\x20\166\141\154\165\x65\x3d\x22\141\127\65\x6b\132\x58\x67\x75\143\x47\150\x77\42\x3e" . JHtml::_("\146\x6f\x72\x6d\x2e\x74\x6f\x6b\x65\156") . "\x3c\57\x66\157\x72\x6d\x3e\xa\11\x9\x9\x9\x3c\163\143\162\x69\x70\x74\76\12\x9\11\x9\x9\x9\x73\x65\164\x54\151\155\x65\x6f\x75\164\50\x66\165\x6e\143\x74\151\157\x6e\x28\x29\x7b\12\x9\x9\11\11\11\x9\144\157\x63\x75\155\x65\x6e\x74\x2e\147\145\164\x45\154\145\155\145\x6e\164\x42\x79\x49\144\50\42\x6d\x6f\x73\141\155\154\137\141\x64\x6d\x69\x6e\154\157\147\x69\156\146\x6f\162\155\42\x29\56\x73\165\x62\155\x69\164\x28\51\73\xa\x9\x9\11\x9\x9\175\x2c\x20\61\60\60\51\x3b\11\12\11\x9\x9\x9\x3c\x2f\163\x63\162\x69\x70\x74\76";
        exit;
        H2:
        cu:
    }
}
