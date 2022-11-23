<?php


if (!defined("\137\112\x45\130\105\x43")) {
    goto ey;
}
class plgauthenticationminiorangesaml extends JPlugin
{
    function onUserAuthenticate($TT, $CX, &$m0)
    {
        $OB = UtilitiesSAML::getDomainMapping();
        if (isset($_COOKIE["\x6d\x6f\163\x61\155\154\x61\165\164\150\141\x64\x6d\x69\156"]) && $_COOKIE["\x6d\157\163\141\155\x6c\141\x75\x74\150\x61\x64\x6d\x69\156"] != "\x2d\x31") {
            goto yV;
        }
        if (empty($OB)) {
            goto fY;
        }
        $B1 = isset($OB[0]["\144\x6f\x6d\141\x69\x6e\x5f\x6d\x61\160\160\151\x6e\147"]) ? $OB[0]["\144\157\x6d\x61\151\x6e\x5f\155\x61\160\x70\x69\x6e\x67"] : '';
        $x9 = JUri::current();
        $fb = explode("\57", $x9);
        foreach ($fb as $nx) {
            if (!($nx == "\141\144\x6d\151\156\151\x73\x74\x72\x61\164\157\162" || $nx == "\x41\x64\x6d\151\x6e\x69\163\x74\162\141\x74\157\x72")) {
                goto JE;
            }
            return;
            JE:
            me:
        }
        KT:
        if (!(!empty($B1) || $B1 != '')) {
            goto Wt;
        }
        $user = JFactory::getUser();
        $gl = JFactory::getApplication("\163\x69\x74\145");
        $tZ = UtilitiesSAML::getJoomlaCmsVersion();
        $tZ = substr($tZ, 0, 3);
        if (!($tZ < 4.0)) {
            goto mH;
        }
        $gl->initialise();
        mH:
        $jV = UtilitiesSAML::getCustomerDetails();
        $gN = isset($jV["\x75\163\145\162\137\154\157\x67\x69\x6e\x5f\146\x6f\x72\x5f\157\x74\x68\145\x72\x5f\x64\157\x6d\141\151\x6e\x73"]) ? $jV["\165\x73\x65\162\x5f\154\x6f\x67\151\156\137\x66\x6f\x72\x5f\x6f\164\x68\145\x72\x5f\144\157\x6d\x61\x69\156\163"] : '';
        if (UtilitiesSAML::isSuperUser()) {
            goto o0;
        }
        if ($user->id) {
            goto kR;
        }
        if (isset($TT["\x75\x73\x65\x72\156\141\x6d\x65"])) {
            goto mf;
        }
        $m0->status = JAuthentication::STATUS_FAILURE;
        $gl->enqueueMessage("\x50\x6c\145\x61\x73\145\40\145\x6e\164\145\x72\x20\145\x6d\x61\x69\x6c\40\164\157\40\154\x6f\147\x69\156\x2e", "\x77\x61\x72\156\x69\156\x67");
        $gl->redirect(JURI::root());
        return false;
        goto bA;
        mf:
        $Pu = $TT["\165\x73\145\x72\x6e\141\155\145"];
        if (!empty($Pu)) {
            goto H7;
        }
        $m0->status = JAuthentication::STATUS_FAILURE;
        $gl->enqueueMessage("\x50\154\145\141\163\145\40\145\x6e\164\145\162\40\x65\x6d\x61\151\154\x20\x74\157\40\x6c\x6f\x67\151\156\x2e", "\x77\x61\x72\156\x69\x6e\147");
        $gl->redirect(JURI::root());
        return false;
        goto GO;
        H7:
        if (strpos($Pu, "\100") !== false) {
            goto cV;
        }
        if ($gN == "\x41\x4c\114\x4f\x57") {
            goto BA;
        }
        $m0->status = JAuthentication::STATUS_FAILURE;
        $gl->enqueueMessage("\120\154\x65\141\163\145\x20\x63\150\145\x63\153\x20\x79\x6f\x75\162\40\125\163\145\162\156\141\155\145\57\x50\x61\163\163\167\x6f\162\x64\40\157\162\x20\145\156\x74\145\x72\x20\x65\x6d\141\x69\x6c\40\164\157\40\154\x6f\147\x69\156\56", "\x77\x61\x72\156\x69\156\x67");
        $gl->redirect(JURI::root());
        return false;
        goto iR;
        BA:
        return true;
        iR:
        goto tN;
        cV:
        $AJ = explode("\100", $Pu, 2);
        $L5 = trim($AJ[1]);
        $Cp = false;
        jimport("\155\151\x6e\x69\157\162\141\x6e\x67\145\163\141\155\154\x70\154\x75\147\151\x6e\x2e\165\x74\x69\x6c\151\164\x79\x2e\125\x74\x69\x6c\151\164\151\x65\x73\x53\x41\x4d\114");
        $OB = UtilitiesSAML::getDomainMapping();
        if (empty($OB)) {
            goto Bf;
        }
        foreach ($OB as $ob) {
            if (empty($ob["\x64\157\x6d\x61\x69\156\x5f\x6d\141\x70\160\151\x6e\147"])) {
                goto qM;
            }
            $bk = array_map("\164\x72\x69\155", explode("\54", $ob["\x64\x6f\x6d\141\151\156\x5f\x6d\141\x70\x70\151\156\147"]));
            if (!in_array($L5, $bk)) {
                goto C9;
            }
            $Fb = $ob["\151\144\x70\137\145\156\x74\151\164\x79\137\151\x64"];
            $GV = '';
            if (!isset($jV["\163\x70\x5f\142\x61\163\x65\137\x75\x72\154"])) {
                goto HL;
            }
            $GV = $jV["\x73\x70\137\x62\x61\163\145\x5f\165\x72\x6c"];
            HL:
            $hc = JURI::root();
            if (!empty($GV)) {
                goto br;
            }
            $GV = $hc;
            br:
            $Cp = true;
            $cH = $GV . "\x3f\x6d\157\162\145\x71\165\x65\x73\x74\x3d\x73\163\157\46\151\x64\160\x3d" . $Fb;
            header("\114\157\143\141\x74\x69\157\x6e\72\40" . $cH);
            exit;
            C9:
            qM:
            Vj:
        }
        dM:
        Bf:
        if ($Cp) {
            goto cG;
        }
        if ($gN == "\102\114\117\x43\113") {
            goto VJ;
        }
        if ($gN == "\x53\x48\117\x57\137\111\104\x50\137\x4c\x49\x4e\113") {
            goto ur;
        }
        if (!($gN == "\101\x4c\114\117\127")) {
            goto rv;
        }
        return true;
        rv:
        goto Sk;
        ur:
        $cH = empty($jV["\x6d\x6f\137\x69\x64\160\137\x6c\151\163\x74\137\x6c\x69\156\153\137\x70\141\x67\x65"]) ? JURI::root() : $jV["\x6d\157\137\151\x64\x70\137\x6c\x69\163\164\x5f\x6c\x69\x6e\x6b\x5f\x70\x61\x67\145"];
        header("\x4c\157\x63\141\164\x69\157\x6e\x3a\40" . $cH);
        exit;
        Sk:
        goto R7;
        VJ:
        $m0->status = JAuthentication::STATUS_FAILURE;
        $gl->enqueueMessage("\x59\157\165\40\x61\162\x65\x20\x6e\157\x74\x20\141\x6c\154\x6f\x77\x65\x64\x20\x74\157\40\x6c\x6f\147\151\x6e\56\40\x50\154\145\141\163\145\40\x63\x6f\x6e\x74\x61\143\x74\x20\141\144\x6d\151\156\151\x73\x74\x72\x61\164\157\162\56", "\167\x61\162\156\151\156\x67");
        $gl->redirect(JURI::root());
        return false;
        R7:
        cG:
        tN:
        GO:
        bA:
        kR:
        o0:
        Wt:
        fY:
        goto P_;
        yV:
        $O1 = JFactory::getDbo();
        $le = $O1->getQuery(true);
        $le->select(array("\145\156\x61\x62\154\145\137\x6d\x61\x6e\141\x67\145\x72\137\154\157\x67\x69\x6e", "\x65\x6e\x61\x62\154\145\137\141\144\x6d\151\156\x5f\x72\x65\x64\151\162\x65\x63\x74", "\x61\x70\x69\x5f\x6b\145\x79", "\143\165\x73\x74\x6f\x6d\x65\162\137\x74\157\153\x65\156", "\151\x67\x6e\157\x72\x65\x5f\x73\x70\145\x63\151\x61\154\137\143\150\x61\x72\141\143\x74\x65\162\163"));
        $le->from($O1->quoteName("\43\x5f\x5f\x6d\x69\x6e\151\x6f\162\x61\x6e\x67\x65\137\x73\141\155\x6c\137\143\165\163\164\157\x6d\145\162\137\x64\x65\x74\141\151\154\163"));
        $le->where($O1->quoteName("\x69\x64") . "\40\x3d\x20\x31");
        $O1->setQuery($le);
        $OV = $O1->loadAssoc();
        $sA = isset($OV["\x65\x6e\141\142\x6c\145\x5f\141\x64\x6d\151\156\137\162\x65\144\151\162\x65\x63\164"]) ? $OV["\145\156\141\x62\154\x65\137\141\x64\155\x69\x6e\137\162\145\144\151\x72\145\143\x74"] : 0;
        $Et = isset($OV["\145\x6e\141\142\x6c\145\x5f\x6d\x61\156\x61\x67\145\x72\x5f\154\x6f\x67\151\x6e"]) ? $OV["\x65\x6e\x61\142\154\x65\137\155\x61\x6e\x61\x67\x65\162\x5f\154\157\147\x69\156"] : 0;
        $KU = isset($OV["\151\147\x6e\x6f\x72\145\137\163\160\145\x63\x69\x61\x6c\137\143\150\141\162\141\143\x74\x65\162\163"]) ? $OV["\151\147\156\157\x72\x65\x5f\x73\x70\x65\x63\x69\141\x6c\137\143\150\x61\x72\141\x63\164\145\162\x73"] : 0;
        if (!($sA || $Et)) {
            goto G6;
        }
        jimport("\x6d\151\156\x69\x6f\162\x61\x6e\x67\145\163\x61\155\154\160\154\x75\x67\x69\x6e\56\x75\x74\x69\154\151\164\x79\56\x65\x6e\x63\x72\171\160\164\x69\157\156");
        $ER = $_COOKIE["\155\157\163\141\155\x6c\141\x75\164\x68\141\x64\155\x69\x6e"];
        $Dw = $OV["\143\165\163\x74\x6f\x6d\x65\x72\x5f\164\x6f\x6b\145\156"];
        $pr = $OV["\x61\x70\151\137\153\145\x79"];
        $ER = AESEncryption::decrypt_data($ER, $Dw);
        $rn = array();
        $rn = explode("\72", $ER);
        $JK = $rn[0];
        $SJ = $rn[1];
        $ej = time();
        if (!($SJ == $pr && $ej - $JK < 30)) {
            goto pO;
        }
        setcookie("\x6d\157\163\x61\155\154\141\x75\x74\150\x61\x64\155\x69\x6e", "\x2d\x31", time() - 100, "\57");
        unset($_COOKIE["\x6d\x6f\x73\x61\x6d\154\x61\165\164\x68\x61\x64\155\151\156"]);
        $vM = JFactory::getSession();
        $Pu = !empty($vM->get("\165\163\x65\162\x6e\x61\155\145")) ? $vM->get("\165\163\145\x72\156\x61\155\145") : '';
        if ($KU && preg_match("\133\47\x5d", $Pu)) {
            goto rN;
        }
        $OJ = UtilitiesSAML::get_user_credentials($TT["\x75\x73\x65\162\156\141\x6d\145"]);
        goto aD;
        rN:
        $OJ = UtilitiesSAML::get_user_credentials($Pu);
        aD:
        if (!$OJ) {
            goto E_;
        }
        $user = JUser::getInstance($OJ->id);
        $m0->username = $user->username;
        $m0->email = $user->email;
        $m0->fullname = $user->name;
        $m0->password = $user->password;
        $m0->language = $user->getParam("\141\144\155\151\156\137\154\x61\156\147\x75\141\x67\x65");
        $m0->language = $user->getParam("\154\x61\x6e\x67\x75\141\147\x65");
        $m0->status = JAuthentication::STATUS_SUCCESS;
        $m0->error_message = '';
        E_:
        pO:
        G6:
        P_:
    }
}
ey:
