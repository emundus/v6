<?php


if (!defined("\x5f\x4a\105\130\105\103")) {
    goto VP;
}
class plgauthenticationminiorangesaml extends JPlugin
{
    function onUserAuthenticate($Ny, $Ef, &$c4)
    {
        $ny = UtilitiesSAML::getDomainMapping();
        if (isset($_COOKIE["\x6d\157\163\141\x6d\x6c\x61\165\x74\150\x61\x64\x6d\x69\156"]) && $_COOKIE["\155\x6f\163\141\x6d\154\x61\165\164\150\141\144\155\x69\x6e"] != "\x2d\61") {
            goto bT;
        }
        if (empty($ny)) {
            goto tQ;
        }
        $Gd = isset($ny[0]["\x64\x6f\155\x61\x69\156\137\155\141\160\x70\151\x6e\x67"]) ? $ny[0]["\x64\157\x6d\141\x69\x6e\x5f\x6d\x61\x70\160\151\156\147"] : '';
        $tZ = JUri::current();
        $cW = explode("\x2f", $tZ);
        foreach ($cW as $nT) {
            if (!($nT == "\141\144\155\x69\156\x69\163\x74\x72\x61\164\157\162" || $nT == "\101\144\155\151\x6e\151\163\164\x72\x61\x74\x6f\x72")) {
                goto fK;
            }
            return;
            fK:
            G0:
        }
        qp:
        if (!(!empty($Gd) || $Gd != '')) {
            goto wO;
        }
        $user = JFactory::getUser();
        $r2 = JFactory::getApplication("\x73\151\164\145");
        $Hs = UtilitiesSAML::getJoomlaCmsVersion();
        $Hs = substr($Hs, 0, 3);
        if (!($Hs < 4.0)) {
            goto aX;
        }
        $r2->initialise();
        aX:
        $No = UtilitiesSAML::getCustomerDetails();
        $ZP = isset($No["\x75\x73\145\162\x5f\154\157\x67\x69\x6e\137\x66\x6f\162\x5f\x6f\x74\150\x65\162\x5f\144\157\x6d\141\x69\156\x73"]) ? $No["\x75\163\x65\x72\137\154\x6f\147\x69\x6e\x5f\146\157\162\x5f\157\x74\x68\145\x72\x5f\x64\x6f\155\x61\151\x6e\163"] : '';
        if (UtilitiesSAML::isSuperUser()) {
            goto vi;
        }
        if ($user->id) {
            goto bX;
        }
        if (isset($Ny["\x75\x73\x65\162\x6e\141\x6d\145"])) {
            goto Hh;
        }
        $c4->status = JAuthentication::STATUS_FAILURE;
        $r2->enqueueMessage("\x50\x6c\145\141\x73\145\40\x65\156\x74\145\x72\40\x65\x6d\x61\151\x6c\40\164\x6f\40\x6c\157\x67\151\x6e\x2e", "\167\141\162\x6e\x69\156\x67");
        $r2->redirect(JURI::root());
        return false;
        goto OP;
        Hh:
        $aN = $Ny["\x75\x73\x65\x72\156\x61\155\145"];
        if (!empty($aN)) {
            goto Eq;
        }
        $c4->status = JAuthentication::STATUS_FAILURE;
        $r2->enqueueMessage("\x50\154\145\x61\x73\145\x20\145\156\164\x65\x72\x20\x65\x6d\x61\x69\x6c\40\164\x6f\40\154\x6f\x67\151\156\x2e", "\x77\141\162\x6e\151\x6e\x67");
        $r2->redirect(JURI::root());
        return false;
        goto Xu;
        Eq:
        if (strpos($aN, "\x40") !== false) {
            goto HJ;
        }
        if ($ZP == "\101\114\x4c\x4f\x57") {
            goto ED;
        }
        $c4->status = JAuthentication::STATUS_FAILURE;
        $r2->enqueueMessage("\x50\154\x65\141\163\145\x20\x63\x68\145\x63\x6b\40\171\x6f\165\x72\40\x55\163\145\x72\156\x61\x6d\145\57\x50\141\x73\x73\x77\157\x72\x64\x20\157\162\40\x65\156\x74\x65\x72\x20\x65\x6d\141\151\x6c\40\164\x6f\40\154\x6f\147\x69\x6e\56", "\x77\141\x72\x6e\151\156\x67");
        $r2->redirect(JURI::root());
        return false;
        goto vw;
        ED:
        return true;
        vw:
        goto C9;
        HJ:
        $hc = explode("\x40", $aN, 2);
        $jL = trim($hc[1]);
        $Z3 = false;
        jimport("\x6d\x69\156\151\x6f\x72\141\156\x67\x65\x73\x61\x6d\x6c\160\x6c\165\147\151\156\x2e\165\164\x69\x6c\151\164\x79\56\125\164\x69\154\x69\164\x69\145\163\123\x41\x4d\x4c");
        $ny = UtilitiesSAML::getDomainMapping();
        if (empty($ny)) {
            goto fF;
        }
        foreach ($ny as $l0) {
            if (empty($l0["\x64\x6f\155\x61\151\x6e\x5f\155\141\160\160\x69\x6e\x67"])) {
                goto FR;
            }
            $Et = array_map("\x74\x72\x69\x6d", explode("\54", $l0["\144\157\x6d\x61\x69\x6e\x5f\155\x61\160\x70\x69\x6e\x67"]));
            if (!in_array($jL, $Et)) {
                goto xo;
            }
            $T2 = $l0["\151\x64\160\137\145\156\x74\x69\164\x79\137\151\144"];
            $M5 = '';
            if (!isset($No["\163\x70\137\142\141\163\145\137\x75\x72\x6c"])) {
                goto qe;
            }
            $M5 = $No["\163\160\x5f\x62\x61\163\x65\137\x75\162\x6c"];
            qe:
            $wA = JURI::root();
            if (!empty($M5)) {
                goto Y8;
            }
            $M5 = $wA;
            Y8:
            $Z3 = true;
            $BY = $M5 . "\77\x6d\x6f\x72\x65\x71\x75\x65\163\164\x3d\x73\163\157\46\151\x64\x70\x3d" . $T2;
            header("\114\x6f\143\x61\164\x69\x6f\x6e\x3a\40" . $BY);
            exit;
            xo:
            FR:
            L9:
        }
        J3:
        fF:
        if ($Z3) {
            goto cj;
        }
        if ($ZP == "\102\x4c\117\x43\113") {
            goto xW;
        }
        if ($ZP == "\x53\110\x4f\x57\137\111\104\120\137\114\x49\116\113") {
            goto nT;
        }
        if (!($ZP == "\101\x4c\114\117\x57")) {
            goto lK;
        }
        return true;
        lK:
        goto yl;
        nT:
        $BY = empty($No["\155\157\137\151\144\160\x5f\x6c\x69\x73\x74\x5f\154\x69\x6e\153\x5f\160\141\x67\145"]) ? JURI::root() : $No["\x6d\157\x5f\x69\144\160\x5f\x6c\x69\x73\164\137\154\151\x6e\x6b\137\x70\x61\x67\x65"];
        header("\x4c\157\143\141\x74\151\x6f\x6e\72\x20" . $BY);
        exit;
        yl:
        goto ih;
        xW:
        $c4->status = JAuthentication::STATUS_FAILURE;
        $r2->enqueueMessage("\131\157\165\40\141\162\x65\40\x6e\x6f\164\x20\141\154\x6c\157\x77\145\x64\x20\x74\x6f\40\x6c\x6f\x67\x69\x6e\x2e\x20\120\154\x65\x61\x73\x65\40\x63\157\156\164\141\143\164\x20\x61\144\155\151\x6e\151\163\x74\162\x61\x74\x6f\162\56", "\x77\141\162\x6e\x69\156\147");
        $r2->redirect(JURI::root());
        return false;
        ih:
        cj:
        C9:
        Xu:
        OP:
        bX:
        vi:
        wO:
        tQ:
        goto fJ;
        bT:
        $XN = UtilitiesSAML::getCustomerDetails();
        $kQ = isset($XN["\x65\156\x61\x62\x6c\x65\137\x61\x64\155\x69\x6e\137\x72\145\x64\151\x72\145\143\164"]) ? $XN["\145\x6e\141\x62\x6c\x65\x5f\x61\x64\155\x69\156\x5f\162\x65\144\151\162\145\143\164"] : 0;
        $Qb = isset($XN["\x65\x6e\141\142\154\145\137\155\x61\156\141\147\x65\162\x5f\x6c\157\x67\151\x6e"]) ? $XN["\x65\x6e\x61\x62\154\145\x5f\155\x61\x6e\x61\x67\x65\162\x5f\x6c\x6f\x67\151\x6e"] : 0;
        $dV = isset($XN["\145\156\x61\142\x6c\145\x5f\x61\144\155\151\156\137\x63\x68\151\x6c\x64\137\154\157\x67\151\156"]) ? $XN["\x65\x6e\141\142\154\x65\x5f\x61\144\x6d\151\156\x5f\x63\150\x69\154\x64\137\x6c\x6f\147\151\x6e"] : 0;
        $vr = isset($XN["\x65\156\141\142\x6c\145\137\155\141\156\x61\x67\x65\162\x5f\143\x68\151\x6c\144\x5f\154\157\x67\x69\156"]) ? $XN["\x65\x6e\x61\x62\154\145\137\x6d\141\156\141\x67\x65\x72\x5f\x63\x68\151\154\x64\x5f\x6c\157\x67\151\156"] : 0;
        $Bw = isset($XN["\151\147\x6e\x6f\x72\145\x5f\x73\x70\x65\x63\151\141\154\137\x63\150\141\x72\141\x63\164\x65\x72\x73"]) ? $XN["\151\x67\x6e\x6f\162\x65\x5f\x73\160\x65\x63\151\141\x6c\137\x63\150\141\162\x61\143\x74\x65\x72\x73"] : 0;
        if (!($kQ || $Qb || $dV || $vr)) {
            goto eH;
        }
        jimport("\155\151\x6e\x69\x6f\162\x61\x6e\x67\145\x73\141\155\154\160\154\x75\147\151\x6e\x2e\165\164\x69\x6c\x69\164\171\56\x65\156\143\x72\171\160\x74\151\157\156");
        $mP = $_COOKIE["\155\x6f\x73\141\155\154\x61\x75\x74\x68\x61\144\x6d\151\156"];
        $yH = $XN["\x63\165\x73\x74\x6f\155\145\162\x5f\164\x6f\153\145\x6e"];
        $Le = $XN["\141\160\151\137\x6b\145\x79"];
        $mP = AESEncryption::decrypt_data($mP, $yH);
        $nf = array();
        $nf = explode("\72", $mP);
        $EP = $nf[0];
        $LU = $nf[1];
        $Wx = time();
        if (!($LU == $Le && $Wx - $EP < 30)) {
            goto XE;
        }
        setcookie("\155\x6f\163\141\155\154\x61\165\164\x68\141\144\x6d\151\156", "\x2d\61", time() - 100, "\x2f");
        unset($_COOKIE["\x6d\x6f\x73\x61\155\154\x61\x75\164\150\x61\x64\155\151\156"]);
        $Mt = JFactory::getSession();
        $aN = !empty($Mt->get("\x75\163\x65\x72\156\141\x6d\145")) ? $Mt->get("\165\x73\x65\x72\x6e\x61\155\x65") : '';
        if ($Bw && preg_match("\133\47\135", $aN)) {
            goto ij;
        }
        $TD = UtilitiesSAML::get_user_credentials($Ny["\x75\163\x65\x72\x6e\141\x6d\x65"]);
        goto QU;
        ij:
        $TD = UtilitiesSAML::get_user_credentials($aN);
        QU:
        if (!$TD) {
            goto tS;
        }
        $user = JUser::getInstance($TD->id);
        $c4->username = $user->username;
        $c4->email = $user->email;
        $c4->fullname = $user->name;
        $c4->password = $user->password;
        $c4->language = $user->getParam("\x61\x64\x6d\x69\x6e\x5f\x6c\x61\156\x67\x75\x61\x67\x65");
        $c4->language = $user->getParam("\154\141\156\147\165\x61\x67\x65");
        $c4->status = JAuthentication::STATUS_SUCCESS;
        $c4->error_message = '';
        tS:
        XE:
        eH:
        fJ:
    }
}
VP:
