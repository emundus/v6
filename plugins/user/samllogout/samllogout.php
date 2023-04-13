<?php


defined("\137\x4a\x45\130\105\103") or die("\122\x65\163\x74\162\151\143\x74\x65\144\x20\141\x63\143\145\163\163");
include_once "\114\x6f\x67\157\x75\164\x52\x65\x71\165\145\x73\164\x2e\160\x68\160";
jimport("\152\157\157\155\x6c\x61\56\160\x6c\x75\x67\151\156\x2e\160\x6c\x75\x67\x69\x6e");
jimport("\155\151\x6e\151\x6f\x72\x61\156\147\145\163\x61\x6d\154\160\154\x75\147\x69\156\56\x75\164\x69\x6c\151\x74\x79\x2e\x55\164\x69\154\151\x74\x69\x65\x73\123\x41\115\114");
jimport("\155\x69\156\x69\x6f\162\141\156\x67\x65\163\141\x6d\x6c\x70\154\x75\147\x69\156\56\165\164\151\x6c\151\x74\171\56\170\x6d\154\x73\145\143\x6c\x69\142\163\123\101\x4d\x4c");
require_once JPATH_BASE . "\57\151\x6e\x63\x6c\x75\144\145\163\57\x64\x65\146\151\x6e\x65\x73\x2e\x70\150\x70";
require_once JPATH_BASE . "\x2f\151\156\143\154\165\x64\145\x73\x2f\x66\162\x61\x6d\x65\x77\157\x72\x6b\56\x70\150\160";
$Ha = JFactory::getApplication()->input->request->getArray();
$AO = JFactory::getApplication()->input->get->getArray();
if (array_key_exists("\123\101\115\x4c\122\x65\x71\x75\x65\x73\x74", $Ha) && !empty($Ha["\x53\101\x4d\x4c\x52\145\x71\165\x65\x73\164"])) {
    goto f2;
}
if (!(array_key_exists("\x53\x41\115\x4c\x52\145\163\x70\157\156\163\145", $Ha) && !empty($Ha["\x53\x41\115\114\122\145\163\x70\157\x6e\x73\x65"]))) {
    goto M7;
}
$vl = $Ha["\x53\101\115\x4c\x52\145\x73\160\157\x6e\x73\145"];
$vl = base64_decode($vl);
$xT = isset($Ha["\122\x65\x6c\x61\171\x53\164\141\164\x65"]) ? $Ha["\x52\145\154\141\171\x53\x74\x61\x74\x65"] : '';
if (!empty($xT)) {
    goto t2;
}
$xT = JRoute::_("\x2e\56\57\56\x2e\x2f\56\x2e\x2f\151\x6e\x64\145\170\x2e\x70\150\160\x3f\x6f\x70\x74\x69\157\x6e\75\x63\157\x6d\137\x75\x73\145\x72\x73\x26\x76\151\x65\x77\x3d\154\x6f\x67\x69\156");
t2:
if (!(array_key_exists("\123\101\x4d\x4c\x52\145\163\x70\x6f\x6e\x73\x65", $AO) && !empty($AO["\123\101\115\x4c\122\145\163\x70\x6f\156\x73\145"]))) {
    goto Vn;
}
$vl = gzinflate($vl);
Vn:
$cA = new DOMDocument();
$cA->loadXML($vl);
$nk = $cA->firstChild;
if (!($nk->localName == "\114\x6f\x67\x6f\x75\x74\x52\145\163\x70\157\x6e\163\145")) {
    goto uJ;
}
$s5 = JFactory::getApplication("\163\151\164\x65");
$s5->redirect($xT);
uJ:
M7:
goto Pp;
f2:
$s5 = JFactory::getApplication("\163\x69\x74\x65");
$Il = UtilitiesSAML::getJoomlaCmsVersion();
$Il = substr($Il, 0, 3);
if (!($Il < 4.0)) {
    goto kU;
}
$s5->initialise();
kU:
$Kg = $Ha["\x53\x41\x4d\114\122\145\x71\165\145\x73\164"];
$xT = JURI::base();
if (!array_key_exists("\122\x65\154\141\x79\123\164\x61\x74\x65", $Ha)) {
    goto rR;
}
$xT = $Ha["\122\145\154\x61\171\x53\x74\x61\164\145"];
rR:
$Kg = base64_decode($Kg);
if (!(array_key_exists("\x53\101\x4d\114\122\145\x71\x75\145\x73\164", $AO) && !empty($AO["\123\x41\115\x4c\x52\145\x71\165\145\x73\164"]))) {
    goto mX;
}
$Kg = gzinflate($Kg);
mX:
$cA = new DOMDocument();
$cA->loadXML($Kg);
$xC = $cA->firstChild;
if (!($xC->localName == "\114\157\147\x6f\x75\164\122\145\x71\165\145\x73\x74")) {
    goto KX;
}
$xP = new SAML2_LogoutRequest($xC);
$Te = JFactory::getSession();
$Te->set("\x4d\x4f\137\123\101\x4d\114\x5f\x4c\x4f\107\117\125\x54\x5f\122\105\121\125\105\123\x54", $Kg);
$Te->set("\x4d\x4f\137\x53\x41\x4d\x4c\x5f\122\x45\x4c\x41\x59\x5f\x53\x54\x41\x54\x45", $xT);
$user = JFactory::getUser();
$tr = $user->id;
$s5->logout($tr, array());
KX:
Pp:
class plgUserSamllogout extends JPlugin
{
    private $sessionIndex;
    private $nameId;
    private $logout_request;
    private $relayState;
    private $idpEntityId;
    public function onUserLogout($user, $Me = array())
    {
        $Te = JFactory::getSession();
        $QL = $Te->get("\115\x4f\137\123\x41\x4d\114\137\114\117\107\x47\x45\x44\x5f\x49\116\x5f\x57\111\124\x48\137\x49\x44\120");
        if (!($QL === TRUE)) {
            goto z5;
        }
        $this->nameId = $Te->get("\115\x4f\x5f\x53\101\115\114\x5f\x4e\x41\115\105\111\x44");
        $this->sessionIndex = $Te->get("\x4d\117\137\123\x41\x4d\x4c\137\x53\105\123\123\111\x4f\x4e\137\x49\116\x44\105\130");
        $this->logout_request = $Te->get("\115\117\137\123\101\115\x4c\x5f\x4c\x4f\107\x4f\125\124\137\x52\x45\121\x55\x45\x53\x54");
        $this->relayState = $Te->get("\115\x4f\137\123\x41\115\x4c\x5f\122\105\114\101\x59\137\123\124\x41\124\x45");
        $this->idpEntityId = $Te->get("\115\x4f\137\x53\x41\x4d\114\137\x49\x44\x50\x5f\x55\123\x45\104");
        z5:
        return true;
    }
    public function onUserAfterLogout($user, $Me = array())
    {
        $bI = UtilitiesSAML::isLoginReportAddonEnable();
        if (!$bI) {
            goto f9;
        }
        $kH = "\x53\123\x4f\x20\x55\x73\x65\x72\x20\x4c\157\147\x6f\x75\164";
        commonUtilities::afterLogout($user, $kH);
        f9:
        $Te = JFactory::getSession();
        if (empty($this->idpEntityId)) {
            goto F2;
        }
        $GJ = JFactory::getDbo();
        $Z5 = UtilitiesSAML::getSAMLConfiguration($this->idpEntityId);
        $Z5 = $Z5[0];
        $Xu = UtilitiesSAML::getCustomerDetails();
        $m1 = '';
        $kl = '';
        if (!isset($Xu["\163\x70\x5f\142\141\x73\145\137\x75\x72\154"])) {
            goto lb;
        }
        $m1 = $Xu["\x73\160\137\142\x61\x73\145\x5f\x75\x72\154"];
        $kl = $Xu["\163\160\137\x65\156\164\151\164\171\137\151\x64"];
        lb:
        $py = JURI::root();
        if (!empty($m1)) {
            goto Yg;
        }
        $m1 = $py;
        Yg:
        if (!empty($kl)) {
            goto Z0;
        }
        $kl = $py . "\160\154\x75\147\151\156\x73\x2f\x61\x75\x74\150\145\156\164\x69\143\x61\x74\151\x6f\x6e\x2f\155\151\156\x69\157\x72\x61\x6e\147\145\163\x61\x6d\154";
        Z0:
        if (!(!isset($this->nameId) || empty($this->nameId))) {
            goto wf;
        }
        header("\114\x6f\143\x61\x74\151\157\156\x3a\x20" . $m1);
        exit;
        wf:
        $NL = $Xu["\x65\156\x61\x62\x6c\x65\x5f\x61\144\155\x69\x6e\x5f\x72\145\x64\x69\x72\145\143\164"];
        $D1 = $Xu["\145\156\141\142\x6c\145\137\x6d\x61\156\x61\x67\x65\x72\x5f\154\x6f\x67\x69\x6e"];
        $bS = $Xu["\145\x6e\141\142\x6c\x65\137\x61\144\x6d\151\x6e\x5f\x63\x68\151\x6c\144\x5f\x6c\157\147\x69\156"];
        $iF = $Xu["\145\156\x61\142\x6c\145\137\155\x61\156\141\x67\x65\162\137\x63\x68\151\154\x64\x5f\154\157\147\151\x6e"];
        if (!($NL || $D1 || $bS || $iF)) {
            goto jZ;
        }
        $bv = $user["\x75\163\145\x72\156\x61\x6d\x65"];
        $X6 = JUserHelper::getUserId($bv);
        $user = JFactory::getUser($X6);
        $nC = $user->email;
        $Dn = $GJ->getQuery(true);
        $Uv = array($GJ->quoteName("\x75\163\x65\x72\x6e\141\x6d\x65") . "\x20\x3d\40" . $GJ->quote($bv) . "\x4f\122" . $GJ->quoteName("\x75\x73\x65\x72\x6e\141\155\x65") . "\40\75\40" . $GJ->quote($nC));
        $Dn->delete($GJ->quoteName("\43\137\x5f\x73\x65\163\163\151\157\x6e"));
        $Dn->where($Uv);
        $GJ->setQuery($Dn);
        $f8 = $GJ->execute();
        jZ:
        $q_ = JPluginHelper::getPlugin("\141\165\x74\150\x65\156\x74\151\x63\x61\x74\151\x6f\x6e", "\x6d\x69\156\x69\x6f\162\x61\156\147\145\163\x61\155\154");
        $xX = $Z5["\163\x69\x6e\x67\x6c\x65\137\154\x6f\147\157\165\x74\137\x75\162\154"];
        $HN = $Z5["\x6d\151\156\x69\157\162\141\x6e\x67\x65\137\x73\141\155\154\137\x69\x64\x70\137\163\154\x6f\137\142\x69\x6e\144\151\x6e\x67"];
        $xT = JURI::base();
        $Ha = JFactory::getApplication()->input->request->getArray();
        if (!array_key_exists("\x52\145\x6c\x61\x79\123\164\141\x74\x65", $Ha)) {
            goto Iv;
        }
        $xT = $Ha["\122\x65\x6c\x61\171\123\x74\x61\x74\145"];
        Iv:
        if (!empty($xX)) {
            goto WX;
        }
        return;
        WX:
        if (is_null($this->logout_request)) {
            goto jr;
        }
        self::createLogoutResponseAndRedirect($Z5, $Xu, $xX, $this->logout_request, $this->relayState, $HN);
        exit;
        jr:
        $xa = UtilitiesSAML::createLogoutRequest($this->nameId, $kl, $xX, $HN, $this->sessionIndex);
        $F8 = !empty($_SERVER["\110\x54\124\x50\123"]) && $_SERVER["\110\124\124\x50\x53"] !== "\x6f\146\x66" || $_SERVER["\x53\105\x52\126\x45\x52\137\x50\x4f\x52\124"] == 443 ? "\150\164\x74\x70\163\72\x2f\x2f" : "\150\164\x74\x70\72\x2f\57";
        $xT = $F8 . "{$_SERVER["\x48\124\x54\120\137\110\x4f\123\x54"]}{$_SERVER["\122\x45\x51\125\x45\123\124\137\x55\x52\111"]}";
        $bg = $Z5["\155\157\137\163\x61\x6d\154\137\x73\145\154\145\x63\x74\x5f\163\x69\x67\156\x5f\141\154\147\157"];
        if (empty($HN) || $HN == "\x48\124\x54\120\55\122\x65\144\151\x72\145\143\x74") {
            goto tw;
        }
        if (!empty($xT)) {
            goto Qv;
        }
        $this->relayState;
        Qv:
        $GY = UtilitiesSAML::getSAMLConfiguration();
        $rj = UtilitiesSAML::get_public_private_certificate($GY, "\160\x72\151\166\x61\x74\145\137\x63\x65\162\164\151\146\x69\143\x61\164\x65");
        $hD = UtilitiesSAML::get_public_private_certificate($GY, "\x70\x75\x62\154\151\143\137\143\x65\x72\164\151\146\151\x63\x61\164\x65");
        if (($rj == null || $rj == '') && ($hD == null || $hD == '')) {
            goto vC;
        }
        $zF = UtilitiesSAML::getCustom_CertificatePath("\103\x75\x73\164\x6f\155\120\x72\x69\x76\x61\164\x65\103\x65\162\164\151\x66\x69\x63\x61\x74\x65\x2e\x6b\145\x79");
        $yw = UtilitiesSAML::getCustom_CertificatePath("\103\x75\163\164\157\155\x50\x75\x62\154\x69\143\103\145\162\x74\151\146\151\x63\141\164\145\x2e\153\145\171");
        goto Al;
        vC:
        $zF = dirname(__FILE__) . DIRECTORY_SEPARATOR . "\143\x65\162\x74" . DIRECTORY_SEPARATOR . "\x73\160\55\x6b\x65\x79\56\x6b\x65\171";
        $yw = dirname(__FILE__) . DIRECTORY_SEPARATOR . "\143\x65\x72\x74" . DIRECTORY_SEPARATOR . "\x73\160\x2d\x63\x65\x72\x74\151\x66\151\143\141\164\145\56\143\x72\164";
        Al:
        $vC = UtilitiesSAML::signXML($xa, $yw, $zF, $bg, "\116\141\155\x65\111\x44");
        UtilitiesSAML::postSAMLRequest($xX, $vC, $xT);
        goto ec;
        tw:
        if ($bg == "\x52\x53\101\x5f\123\110\x41\62\x35\66") {
            goto Vz;
        }
        if ($bg == "\x52\x53\x41\x5f\x53\110\x41\x33\x38\x34") {
            goto Cc;
        }
        if ($bg == "\122\123\x41\x5f\x53\x48\101\x35\x31\x32") {
            goto YC;
        }
        $xa = "\x53\101\x4d\x4c\122\145\161\x75\145\x73\164\x3d" . $xa . "\46\122\145\154\141\171\x53\164\x61\164\x65\75" . $xT . "\x26\x53\x69\147\101\x6c\x67\75" . urlencode(XMLSecurityKeySAML::RSA_SHA1);
        goto wq;
        Vz:
        $xa = "\123\x41\x4d\114\122\x65\x71\x75\145\x73\x74\x3d" . $xa . "\x26\x52\145\154\141\171\x53\x74\x61\x74\x65\x3d" . $xT . "\46\123\x69\147\101\x6c\147\75" . urlencode(XMLSecurityKeySAML::RSA_SHA256);
        goto wq;
        Cc:
        $xa = "\x53\x41\x4d\x4c\122\x65\161\x75\145\163\164\75" . $xa . "\x26\x52\x65\x6c\x61\171\123\164\141\164\145\x3d" . $xT . "\46\x53\x69\x67\101\154\x67\x3d" . urlencode(XMLSecurityKeySAML::RSA_SHA384);
        goto wq;
        YC:
        $xa = "\x53\x41\115\x4c\x52\x65\x71\x75\145\163\x74\x3d" . $xa . "\46\x52\145\154\141\x79\123\x74\x61\164\145\x3d" . $xT . "\46\123\x69\147\101\x6c\x67\x3d" . urlencode(XMLSecurityKeySAML::RSA_SHA512);
        wq:
        $t3 = array("\164\171\160\145" => "\160\x72\x69\x76\x61\x74\x65");
        if ($bg == "\122\123\101\137\x53\x48\x41\x32\x35\x36") {
            goto XB;
        }
        if ($bg == "\122\x53\101\137\x53\110\x41\x33\x38\x34") {
            goto ta;
        }
        if ($bg == "\122\x53\x41\x5f\x53\x48\101\x35\x31\62") {
            goto AP;
        }
        $ep = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA1, $t3);
        goto ZB;
        XB:
        $ep = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA256, $t3);
        goto ZB;
        ta:
        $ep = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA384, $t3);
        goto ZB;
        AP:
        $ep = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA512, $t3);
        ZB:
        $GY = UtilitiesSAML::getSAMLConfiguration();
        $rj = UtilitiesSAML::get_public_private_certificate($GY, "\x70\162\151\166\x61\164\x65\137\x63\145\x72\164\151\146\x69\143\x61\164\x65");
        if ($rj == null || $rj == '') {
            goto YF;
        }
        $na = UtilitiesSAML::getCustom_CertificatePath("\x43\x75\x73\x74\157\x6d\120\162\x69\166\141\x74\145\103\145\162\x74\x69\146\151\x63\x61\164\145\56\153\145\171");
        goto TG;
        YF:
        $na = dirname(__FILE__) . DIRECTORY_SEPARATOR . "\143\145\162\x74" . DIRECTORY_SEPARATOR . "\163\x70\55\x6b\145\x79\x2e\x6b\x65\171";
        TG:
        $ep->loadKey($na, TRUE);
        $l9 = new XMLSecurityDSigSAML();
        $x3 = $ep->signData($xa);
        $x3 = base64_encode($x3);
        $dV = $xX;
        if (strpos($xX, "\x3f") !== false) {
            goto ut;
        }
        $dV .= "\77";
        goto xd;
        ut:
        $dV .= "\46";
        xd:
        $dV .= $xa . "\46\x53\151\147\x6e\141\x74\x75\x72\145\x3d" . urlencode($x3);
        header("\114\x6f\x63\x61\164\151\x6f\156\72" . $dV);
        exit;
        ec:
        F2:
    }
    function createLogoutResponseAndRedirect($Z5, $Xu, $l1, $rq, $Vt, $HN)
    {
        $m1 = '';
        $kl = '';
        if (!isset($Xu["\163\x70\137\x62\x61\x73\145\x5f\165\162\x6c"])) {
            goto dK;
        }
        $m1 = $Xu["\163\x70\137\142\x61\x73\x65\x5f\165\162\154"];
        $kl = $Xu["\163\x70\x5f\145\156\164\151\x74\x79\137\151\x64"];
        dK:
        $py = JURI::root();
        if (!empty($m1)) {
            goto ww;
        }
        $m1 = $py;
        ww:
        if (!empty($kl)) {
            goto Pk;
        }
        $kl = $py . "\160\x6c\165\x67\151\156\x73\57\141\x75\x74\150\x65\156\164\151\x63\x61\x74\x69\157\156\x2f\x6d\x69\156\x69\157\162\141\156\147\x65\163\141\x6d\x6c";
        Pk:
        $cA = new DOMDocument();
        $cA->loadXML($rq);
        $rq = $cA->firstChild;
        if (!($rq->localName == "\114\x6f\147\157\165\x74\x52\x65\161\165\x65\163\x74")) {
            goto fU;
        }
        $xP = new SAML2_LogoutRequest($rq);
        $yV = $l1;
        $MT = UtilitiesSAML::createLogoutResponse($xP->getId(), $kl, $yV, $HN);
        if (empty($HN) || $HN == "\x48\x54\x54\x50\x2d\x52\x65\x64\x69\162\145\x63\x74") {
            goto LH;
        }
        $GY = UtilitiesSAML::getSAMLConfiguration();
        $rj = UtilitiesSAML::get_public_private_certificate($GY, "\x70\x72\151\x76\141\164\x65\x5f\x63\x65\162\164\151\146\x69\143\141\164\x65");
        $hD = UtilitiesSAML::get_public_private_certificate($GY, "\160\162\x69\166\141\x74\x65\x5f\143\x65\162\x74\151\x66\x69\143\x61\x74\x65");
        if (($rj == null || $rj == '') && ($hD == null || $hD == '')) {
            goto d4;
        }
        $zF = UtilitiesSAML::getCustom_CertificatePath("\103\x75\x73\x74\157\x6d\120\162\x69\x76\141\164\x65\103\145\x72\x74\x69\x66\x69\143\141\164\x65\56\x6b\x65\171");
        $yw = UtilitiesSAML::getCustom_CertificatePath("\103\x75\163\x74\157\155\x50\165\142\x6c\x69\143\x43\145\x72\164\151\146\151\x63\x61\x74\x65\56\x6b\145\x79");
        goto sf;
        d4:
        $zF = dirname(__FILE__) . DIRECTORY_SEPARATOR . "\x63\x65\162\164" . DIRECTORY_SEPARATOR . "\163\160\x2d\x6b\145\x79\56\x6b\x65\x79";
        $yw = dirname(__FILE__) . DIRECTORY_SEPARATOR . "\x63\145\162\164" . DIRECTORY_SEPARATOR . "\163\x70\55\143\145\162\x74\x69\146\x69\143\x61\x74\x65\x2e\x63\162\x74";
        sf:
        $bg = isset($Z5["\x6d\x6f\x5f\163\141\155\x6c\137\163\145\154\x65\x63\164\x5f\x73\151\147\x6e\137\x61\x6c\x67\x6f"]) ? $Z5["\x6d\x6f\137\x73\141\155\x6c\137\163\145\x6c\x65\x63\x74\137\163\151\x67\x6e\137\x61\x6c\147\x6f"] : '';
        if (!(empty($bg) || '' == $bg)) {
            goto aM;
        }
        $bg = "\x52\123\101\137\x53\110\x41\62\x35\x36";
        aM:
        $vC = UtilitiesSAML::signXML($MT, $yw, $zF, $bg, "\x53\x74\x61\164\165\x73");
        UtilitiesSAML::postSAMLResponse($l1, $vC, $Vt);
        goto vW;
        LH:
        $dV = $l1;
        if (strpos($l1, "\77") !== false) {
            goto n1;
        }
        $dV .= "\77";
        goto zV;
        n1:
        $dV .= "\x26";
        zV:
        $dV .= "\123\101\115\x4c\122\x65\163\x70\157\156\x73\145\x3d" . $MT . "\46\x52\145\154\x61\171\123\164\141\x74\x65\75" . urlencode($Vt);
        header("\114\157\x63\141\x74\151\x6f\x6e\72\x20" . $dV);
        exit;
        vW:
        fU:
    }
}
