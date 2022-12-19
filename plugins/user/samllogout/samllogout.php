<?php


defined("\x5f\x4a\105\130\x45\103") or die("\x52\x65\x73\164\x72\x69\143\164\145\x64\x20\x61\x63\x63\x65\163\163");
include_once "\114\x6f\x67\157\x75\x74\122\x65\x71\165\x65\x73\164\56\x70\x68\160";
jimport("\x6a\157\157\x6d\x6c\141\x2e\160\x6c\x75\x67\151\156\x2e\x70\154\x75\x67\x69\156");
jimport("\x6d\x69\156\x69\x6f\x72\141\156\147\145\x73\x61\x6d\154\x70\x6c\x75\147\x69\x6e\x2e\165\x74\151\x6c\151\164\171\x2e\x55\164\151\x6c\x69\x74\151\145\163\x53\x41\115\114");
jimport("\155\x69\156\151\x6f\x72\x61\x6e\147\x65\163\x61\155\154\x70\154\x75\147\x69\x6e\56\x75\164\x69\x6c\x69\x74\171\56\x78\x6d\154\x73\x65\x63\x6c\151\x62\x73\x53\x41\x4d\114");
require_once JPATH_BASE . "\57\151\156\143\x6c\165\144\x65\163\57\144\145\x66\x69\x6e\x65\163\56\x70\x68\160";
require_once JPATH_BASE . "\x2f\x69\x6e\143\x6c\165\x64\145\x73\x2f\146\x72\141\155\x65\167\x6f\x72\x6b\56\x70\150\x70";
$sB = JFactory::getApplication()->input->request->getArray();
$JW = JFactory::getApplication()->input->get->getArray();
if (array_key_exists("\x53\101\115\114\x52\x65\161\x75\x65\163\x74", $sB) && !empty($sB["\123\101\x4d\114\122\x65\161\165\x65\163\164"])) {
    goto St;
}
if (!(array_key_exists("\123\101\x4d\114\122\x65\163\x70\157\156\x73\145", $sB) && !empty($sB["\123\101\x4d\x4c\122\145\163\160\157\x6e\x73\145"]))) {
    goto Xj;
}
$W6 = $sB["\x53\x41\115\x4c\x52\145\x73\160\157\x6e\163\145"];
$W6 = base64_decode($W6);
$kZ = isset($sB["\122\x65\154\141\171\123\x74\x61\164\145"]) ? $sB["\122\x65\x6c\141\x79\x53\x74\x61\x74\145"] : '';
if (!empty($kZ)) {
    goto t1;
}
$kZ = JRoute::_("\56\56\x2f\x2e\x2e\x2f\x2e\x2e\x2f\x69\156\144\145\170\56\x70\150\x70\x3f\157\160\x74\151\157\x6e\75\x63\157\x6d\x5f\165\163\145\x72\x73\x26\166\151\145\167\75\x6c\157\x67\x69\x6e");
t1:
if (!(array_key_exists("\123\x41\115\114\122\145\x73\160\157\x6e\163\145", $JW) && !empty($JW["\x53\101\x4d\114\x52\x65\163\x70\157\x6e\163\x65"]))) {
    goto rp;
}
$W6 = gzinflate($W6);
rp:
$cg = new DOMDocument();
$cg->loadXML($W6);
$MC = $cg->firstChild;
if (!($MC->localName == "\x4c\157\x67\x6f\x75\x74\122\145\163\160\x6f\156\163\x65")) {
    goto PF;
}
$Sf = JFactory::getApplication("\163\x69\x74\145");
$Sf->redirect($kZ);
PF:
Xj:
goto z2;
St:
$Sf = JFactory::getApplication("\163\x69\164\x65");
$gc = UtilitiesSAML::getJoomlaCmsVersion();
$gc = substr($gc, 0, 3);
if (!($gc < 4.0)) {
    goto xe;
}
$Sf->initialise();
xe:
$V8 = $sB["\123\101\115\x4c\122\x65\x71\x75\145\163\164"];
$kZ = JURI::base();
if (!array_key_exists("\122\x65\154\x61\171\123\164\141\164\145", $sB)) {
    goto ze;
}
$kZ = $sB["\x52\x65\154\x61\x79\x53\164\141\164\x65"];
ze:
$V8 = base64_decode($V8);
if (!(array_key_exists("\123\101\115\114\x52\x65\x71\165\145\163\164", $JW) && !empty($JW["\x53\x41\x4d\x4c\x52\145\161\x75\x65\x73\164"]))) {
    goto JI;
}
$V8 = gzinflate($V8);
JI:
$cg = new DOMDocument();
$cg->loadXML($V8);
$S8 = $cg->firstChild;
if (!($S8->localName == "\x4c\x6f\147\157\165\x74\122\145\x71\165\145\x73\164")) {
    goto tF;
}
$UY = new SAML2_LogoutRequest($S8);
$gb = JFactory::getSession();
$gb->set("\x4d\x4f\137\123\x41\115\114\137\x4c\117\107\117\125\124\137\x52\x45\x51\x55\105\123\124", $V8);
$gb->set("\115\117\x5f\123\101\x4d\114\x5f\x52\x45\114\x41\x59\x5f\123\124\x41\x54\x45", $kZ);
$user = JFactory::getUser();
$tv = $user->id;
$Sf->logout($tv, array());
tF:
z2:
class plgUserSamllogout extends JPlugin
{
    private $sessionIndex;
    private $nameId;
    private $logout_request;
    private $relayState;
    private $idpEntityId;
    public function onUserLogout($user, $IE = array())
    {
        $gb = JFactory::getSession();
        $u9 = $gb->get("\x4d\x4f\x5f\123\x41\115\114\x5f\x4c\x4f\107\x47\x45\x44\x5f\111\116\x5f\127\111\124\110\x5f\x49\x44\120");
        if (!($u9 === TRUE)) {
            goto aq;
        }
        $this->nameId = $gb->get("\x4d\117\137\x53\x41\x4d\x4c\x5f\x4e\x41\x4d\105\x49\x44");
        $this->sessionIndex = $gb->get("\115\117\137\x53\x41\115\114\x5f\123\x45\123\x53\x49\117\x4e\137\111\x4e\x44\x45\x58");
        $this->logout_request = $gb->get("\115\117\137\x53\x41\115\x4c\137\x4c\x4f\107\117\x55\124\137\x52\105\x51\x55\x45\123\124");
        $this->relayState = $gb->get("\115\117\137\123\101\115\x4c\137\122\105\114\101\x59\x5f\123\124\101\x54\x45");
        $this->idpEntityId = $gb->get("\115\x4f\x5f\123\101\x4d\x4c\x5f\x49\x44\120\137\125\123\x45\x44");
        aq:
        return true;
    }
    public function onUserAfterLogout($user, $IE = array())
    {
        if (empty($this->idpEntityId)) {
            goto Qu;
        }
        $Ht = JFactory::getDbo();
        $NF = UtilitiesSAML::getSAMLConfiguration($this->idpEntityId);
        $NF = $NF[0];
        $zT = UtilitiesSAML::getCustomerDetails();
        $Fs = '';
        $l0 = '';
        if (!isset($zT["\163\160\137\142\x61\163\x65\137\165\x72\154"])) {
            goto mQ;
        }
        $Fs = $zT["\x73\x70\x5f\142\x61\x73\145\137\165\x72\x6c"];
        $l0 = $zT["\163\x70\137\x65\156\x74\151\164\171\137\151\x64"];
        mQ:
        $T5 = JURI::root();
        if (!empty($Fs)) {
            goto Id;
        }
        $Fs = $T5;
        Id:
        if (!empty($l0)) {
            goto Dz;
        }
        $l0 = $T5 . "\x70\x6c\165\147\x69\x6e\x73\x2f\x61\x75\164\x68\x65\156\x74\x69\x63\141\164\x69\x6f\156\57\155\x69\156\x69\157\x72\x61\156\x67\145\163\141\x6d\154";
        Dz:
        if (!(!isset($this->nameId) || empty($this->nameId))) {
            goto WM;
        }
        header("\x4c\157\x63\x61\164\x69\157\x6e\x3a\x20" . $Fs);
        exit;
        WM:
        $Bx = $zT["\145\x6e\x61\142\x6c\x65\137\x61\x64\155\151\156\x5f\x6c\157\x67\x69\x6e"];
        if (!$Bx) {
            goto OL;
        }
        $Am = $user["\x75\x73\145\162\x6e\x61\155\x65"];
        $Pp = JUserHelper::getUserId($Am);
        $user = JFactory::getUser($Pp);
        $os = $user->email;
        $sE = $Ht->getQuery(true);
        $pY = array($Ht->quoteName("\x75\163\x65\162\x6e\x61\x6d\145") . "\40\75\x20" . $Ht->quote($Am) . "\x4f\122" . $Ht->quoteName("\x75\x73\145\162\156\x61\x6d\145") . "\40\x3d\x20" . $Ht->quote($os));
        $sE->delete($Ht->quoteName("\x23\x5f\x5f\x73\145\163\x73\151\157\x6e"));
        $sE->where($pY);
        $Ht->setQuery($sE);
        $LJ = $Ht->execute();
        OL:
        $iO = JPluginHelper::getPlugin("\x61\165\x74\x68\145\x6e\x74\151\x63\141\x74\x69\x6f\x6e", "\155\x69\156\151\157\162\x61\156\147\145\163\141\155\154");
        $SS = $NF["\x73\x69\x6e\x67\x6c\145\x5f\x6c\157\x67\157\x75\x74\x5f\x75\162\154"];
        $G8 = $NF["\x6d\151\156\151\157\162\141\156\x67\x65\x5f\163\x61\155\154\x5f\151\x64\x70\137\x73\154\157\x5f\x62\151\156\144\x69\156\x67"];
        $kZ = JURI::base();
        $sB = JFactory::getApplication()->input->request->getArray();
        if (!array_key_exists("\122\x65\154\141\171\x53\164\x61\x74\x65", $sB)) {
            goto Xz;
        }
        $kZ = $sB["\122\x65\x6c\141\171\x53\164\x61\164\x65"];
        Xz:
        if (!empty($SS)) {
            goto Tr;
        }
        return;
        Tr:
        if (is_null($this->logout_request)) {
            goto X2;
        }
        self::createLogoutResponseAndRedirect($NF, $zT, $SS, $this->logout_request, $this->relayState, $G8);
        exit;
        X2:
        $iT = UtilitiesSAML::createLogoutRequest($this->nameId, $l0, $SS, $G8, $this->sessionIndex);
        $D1 = !empty($_SERVER["\110\124\124\120\x53"]) && $_SERVER["\110\x54\124\x50\123"] !== "\157\146\x66" || $_SERVER["\123\x45\122\x56\x45\122\137\x50\117\x52\x54"] == 443 ? "\150\164\x74\160\163\x3a\57\x2f" : "\x68\164\164\x70\x3a\57\57";
        $kZ = $D1 . "{$_SERVER["\x48\x54\x54\120\x5f\x48\117\x53\124"]}{$_SERVER["\x52\105\x51\x55\105\123\124\137\x55\122\111"]}";
        $Xw = $NF["\x6d\157\x5f\x73\x61\x6d\x6c\137\163\x65\154\145\143\x74\x5f\x73\151\x67\x6e\137\x61\x6c\x67\157"];
        if (empty($G8) || $G8 == "\110\x54\124\120\55\x52\145\144\151\x72\145\143\x74") {
            goto sT;
        }
        if (!empty($kZ)) {
            goto Ag;
        }
        $this->relayState;
        Ag:
        $br = UtilitiesSAML::getSAMLConfiguration();
        $Ne = UtilitiesSAML::get_public_private_certificate($br, "\160\162\151\166\141\164\x65\137\143\x65\162\x74\x69\x66\x69\143\141\164\x65");
        $O3 = UtilitiesSAML::get_public_private_certificate($br, "\x70\165\x62\x6c\151\x63\x5f\143\x65\x72\164\151\x66\151\x63\x61\164\x65");
        if (($Ne == null || $Ne == '') && ($O3 == null || $O3 == '')) {
            goto So;
        }
        $dM = UtilitiesSAML::getCustom_CertificatePath("\x43\x75\163\x74\157\x6d\120\162\x69\166\141\164\x65\x43\x65\x72\x74\151\146\x69\143\141\164\145\56\153\x65\171");
        $X1 = UtilitiesSAML::getCustom_CertificatePath("\103\165\163\x74\x6f\155\x50\165\x62\x6c\151\x63\103\x65\x72\164\151\146\x69\143\x61\x74\x65\x2e\x6b\x65\171");
        goto lU;
        So:
        $dM = dirname(__FILE__) . DIRECTORY_SEPARATOR . "\143\x65\x72\164" . DIRECTORY_SEPARATOR . "\163\160\55\153\145\171\56\153\x65\171";
        $X1 = dirname(__FILE__) . DIRECTORY_SEPARATOR . "\143\x65\x72\164" . DIRECTORY_SEPARATOR . "\x73\x70\55\143\x65\x72\x74\x69\146\151\143\141\164\145\x2e\x63\x72\x74";
        lU:
        $GA = UtilitiesSAML::signXML($iT, $X1, $dM, $Xw, "\x4e\141\155\145\111\x44");
        UtilitiesSAML::postSAMLRequest($SS, $GA, $kZ);
        goto aD;
        sT:
        if ($Xw == "\x52\x53\101\x5f\123\x48\101\x32\x35\66") {
            goto kG;
        }
        if ($Xw == "\122\123\101\x5f\x53\110\x41\63\70\x34") {
            goto Kd;
        }
        if ($Xw == "\x52\x53\101\137\123\110\x41\65\61\62") {
            goto dz;
        }
        $iT = "\123\101\x4d\x4c\x52\145\161\165\145\163\x74\75" . $iT . "\x26\x52\145\x6c\x61\171\123\164\141\x74\x65\x3d" . $kZ . "\46\123\x69\x67\101\154\x67\75" . urlencode(XMLSecurityKeySAML::RSA_SHA1);
        goto lI;
        kG:
        $iT = "\123\101\x4d\x4c\122\x65\x71\165\x65\163\x74\75" . $iT . "\x26\122\x65\154\141\171\123\164\x61\164\145\x3d" . $kZ . "\46\x53\x69\x67\101\x6c\147\75" . urlencode(XMLSecurityKeySAML::RSA_SHA256);
        goto lI;
        Kd:
        $iT = "\x53\101\x4d\114\x52\145\161\165\x65\163\164\75" . $iT . "\46\x52\145\x6c\141\171\x53\164\141\x74\x65\x3d" . $kZ . "\x26\123\x69\x67\x41\x6c\147\x3d" . urlencode(XMLSecurityKeySAML::RSA_SHA384);
        goto lI;
        dz:
        $iT = "\123\101\x4d\114\x52\145\161\165\x65\163\x74\75" . $iT . "\46\x52\x65\x6c\141\171\123\x74\x61\164\145\x3d" . $kZ . "\46\x53\x69\147\101\154\147\75" . urlencode(XMLSecurityKeySAML::RSA_SHA512);
        lI:
        $U0 = array("\x74\171\160\145" => "\x70\162\x69\x76\x61\164\x65");
        if ($Xw == "\x52\x53\x41\137\x53\110\x41\x32\x35\x36") {
            goto E5;
        }
        if ($Xw == "\x52\123\x41\137\123\x48\x41\x33\x38\x34") {
            goto Vq;
        }
        if ($Xw == "\x52\x53\x41\x5f\x53\x48\101\x35\61\62") {
            goto MJ;
        }
        $Gw = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA1, $U0);
        goto OJ;
        E5:
        $Gw = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA256, $U0);
        goto OJ;
        Vq:
        $Gw = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA384, $U0);
        goto OJ;
        MJ:
        $Gw = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA512, $U0);
        OJ:
        $br = UtilitiesSAML::getSAMLConfiguration();
        $Ne = UtilitiesSAML::get_public_private_certificate($br, "\x70\x72\151\166\141\x74\145\137\143\x65\x72\164\151\146\151\x63\141\x74\x65");
        if ($Ne == null || $Ne == '') {
            goto gG;
        }
        $Vy = UtilitiesSAML::getCustom_CertificatePath("\103\165\163\x74\x6f\155\120\x72\151\166\141\164\x65\x43\x65\162\164\x69\x66\x69\x63\x61\164\x65\x2e\x6b\x65\171");
        goto he;
        gG:
        $Vy = dirname(__FILE__) . DIRECTORY_SEPARATOR . "\143\145\x72\164" . DIRECTORY_SEPARATOR . "\x73\x70\x2d\153\145\171\56\x6b\x65\x79";
        he:
        $Gw->loadKey($Vy, TRUE);
        $aJ = new XMLSecurityDSigSAML();
        $V9 = $Gw->signData($iT);
        $V9 = base64_encode($V9);
        $y1 = $SS;
        if (strpos($SS, "\77") !== false) {
            goto hP;
        }
        $y1 .= "\77";
        goto fw;
        hP:
        $y1 .= "\x26";
        fw:
        $y1 .= $iT . "\46\x53\151\x67\156\x61\164\x75\162\145\x3d" . urlencode($V9);
        header("\114\157\x63\141\164\151\x6f\156\72" . $y1);
        exit;
        aD:
        Qu:
    }
    function createLogoutResponseAndRedirect($NF, $zT, $sM, $mD, $fe, $G8)
    {
        $Fs = '';
        $l0 = '';
        if (!isset($zT["\x73\x70\x5f\x62\141\x73\x65\x5f\x75\162\x6c"])) {
            goto SB;
        }
        $Fs = $zT["\x73\x70\x5f\x62\x61\x73\145\137\165\x72\154"];
        $l0 = $zT["\x73\160\x5f\x65\x6e\x74\x69\164\171\137\x69\144"];
        SB:
        $T5 = JURI::root();
        if (!empty($Fs)) {
            goto to;
        }
        $Fs = $T5;
        to:
        if (!empty($l0)) {
            goto bv;
        }
        $l0 = $T5 . "\160\154\165\x67\x69\x6e\163\x2f\141\165\x74\150\145\x6e\x74\151\x63\x61\x74\x69\157\156\x2f\x6d\151\x6e\x69\x6f\x72\141\x6e\147\x65\x73\x61\155\x6c";
        bv:
        $cg = new DOMDocument();
        $cg->loadXML($mD);
        $mD = $cg->firstChild;
        if (!($mD->localName == "\114\x6f\147\x6f\x75\164\x52\145\161\165\145\x73\164")) {
            goto Ss;
        }
        $UY = new SAML2_LogoutRequest($mD);
        $rD = $sM;
        $TE = UtilitiesSAML::createLogoutResponse($UY->getId(), $l0, $rD, $G8);
        if (empty($G8) || $G8 == "\x48\x54\x54\120\55\122\x65\144\x69\x72\145\x63\x74") {
            goto AQ;
        }
        $br = UtilitiesSAML::getSAMLConfiguration();
        $Ne = UtilitiesSAML::get_public_private_certificate($br, "\160\x72\x69\x76\141\x74\145\x5f\143\145\x72\x74\x69\x66\151\x63\141\x74\x65");
        $O3 = UtilitiesSAML::get_public_private_certificate($br, "\x70\x72\151\x76\x61\164\145\x5f\143\145\x72\164\151\x66\151\143\x61\164\x65");
        if (($Ne == null || $Ne == '') && ($O3 == null || $O3 == '')) {
            goto AG;
        }
        $dM = UtilitiesSAML::getCustom_CertificatePath("\103\165\x73\164\157\x6d\x50\x72\151\166\x61\164\145\x43\x65\162\x74\x69\x66\x69\x63\x61\x74\x65\56\x6b\x65\171");
        $X1 = UtilitiesSAML::getCustom_CertificatePath("\x43\x75\163\164\x6f\x6d\120\x75\x62\154\x69\143\x43\x65\x72\x74\151\x66\x69\143\141\164\145\56\153\145\x79");
        goto rw;
        AG:
        $dM = dirname(__FILE__) . DIRECTORY_SEPARATOR . "\143\145\162\x74" . DIRECTORY_SEPARATOR . "\x73\x70\55\x6b\145\x79\56\153\x65\x79";
        $X1 = dirname(__FILE__) . DIRECTORY_SEPARATOR . "\143\x65\162\x74" . DIRECTORY_SEPARATOR . "\x73\160\55\143\x65\162\164\151\146\x69\143\141\x74\x65\x2e\143\x72\164";
        rw:
        $Xw = isset($NF["\x6d\x6f\x5f\x73\141\x6d\x6c\137\163\x65\154\145\143\x74\137\163\151\147\x6e\137\141\154\x67\157"]) ? $NF["\155\157\x5f\163\141\x6d\154\137\163\x65\x6c\x65\143\x74\137\163\151\147\156\137\141\154\147\x6f"] : '';
        if (!(empty($Xw) || '' == $Xw)) {
            goto t3;
        }
        $Xw = "\122\x53\x41\x5f\123\x48\x41\x32\65\x36";
        t3:
        $GA = UtilitiesSAML::signXML($TE, $X1, $dM, $Xw, "\123\164\141\164\165\x73");
        UtilitiesSAML::postSAMLResponse($sM, $GA, $fe);
        goto mV;
        AQ:
        $y1 = $sM;
        if (strpos($sM, "\77") !== false) {
            goto Un;
        }
        $y1 .= "\x3f";
        goto Qr;
        Un:
        $y1 .= "\x26";
        Qr:
        $y1 .= "\x53\x41\x4d\x4c\x52\145\163\160\x6f\x6e\163\145\75" . $TE . "\46\122\x65\154\141\x79\x53\164\x61\164\145\75" . urlencode($fe);
        header("\x4c\x6f\x63\x61\164\x69\x6f\x6e\x3a\x20" . $y1);
        exit;
        mV:
        Ss:
    }
}
