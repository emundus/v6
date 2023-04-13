<?php


include "\170\155\x6c\x73\145\143\154\x69\142\x73\123\x41\x4d\114\x2e\160\x68\x70";
use Joomla\CMS\User\User;
require_once JPATH_SITE . DIRECTORY_SEPARATOR . "\141\144\x6d\151\x6e\x69\x73\x74\162\141\164\x6f\162" . DIRECTORY_SEPARATOR . "\x63\157\x6d\160\x6f\156\x65\x6e\164\x73" . DIRECTORY_SEPARATOR . "\143\157\x6d\x5f\x6d\x69\x6e\x69\157\162\x61\156\x67\x65\x5f\163\x61\155\x6c" . DIRECTORY_SEPARATOR . "\x68\x65\154\x70\x65\x72\x73" . DIRECTORY_SEPARATOR . "\x6d\157\x2d\x73\141\155\154\x2d\x63\x75\163\x74\157\x6d\x65\x72\x2d\x73\145\164\x75\x70\56\160\x68\x70";
class UtilitiesSAML
{
    public static function getRoleBasedRedirectionConfiguration()
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select("\52");
        $rw->from($U4->quoteName("\43\137\x5f\155\151\x6e\151\157\162\141\x6e\x67\145\x5f\162\x6f\154\x65\x62\141\x73\x65\x64\162\145\x64\151\x72\145\143\164\x69\x6f\x6e\137\x73\145\x74\x74\151\156\x67\163"));
        $U4->setQuery($rw);
        return $U4->loadAssoc();
    }
    public static function isRolebasedRedirectionPluginInstalled()
    {
        $My = array("\155\x69\156\151\x6f\x72\141\x6e\x67\145\x72\157\154\145\x62\141\163\x65\144\x72\x65\x64\151\x72\145\x63\164\151\x6f\156", "\x6d\151\156\151\157\x72\x61\156\x67\x65\x61\165\x74\150\162\157\x6c\145\142\141\163\145\144\x72\x65\144\151\162\145\x63\x74\x69\x6f\156");
        foreach ($My as $BI) {
            $U4 = JFactory::getDbo();
            $rw = $U4->getQuery(true);
            $rw->select("\145\156\x61\x62\154\145\144");
            $rw->from("\43\x5f\x5f\x65\170\164\145\156\x73\151\x6f\156\163");
            $rw->where($U4->quoteName("\145\154\145\155\x65\156\164") . "\40\x3d\40" . $U4->quote($BI));
            $rw->where($U4->quoteName("\x74\x79\x70\x65") . "\x20\75\40" . $U4->quote("\x70\154\165\147\151\x6e"));
            $U4->setQuery($rw);
            return $U4->loadAssoc();
            i6:
        }
        xX:
    }
    public static function saveUserInDB($post, $A_)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $BR = date("\x59\55\155\x2d\144\x20\x48\x3a\151\x3a\x73");
        $x2 = array("\156\141\x6d\145", "\x75\x73\145\162\156\141\x6d\145", "\145\155\141\x69\x6c", "\160\141\163\x73\167\x6f\162\x64", "\162\145\x67\151\163\x74\145\x72\104\141\x74\x65", "\x70\x61\x72\141\155\x73");
        $ZW = array($U4->quote($post["\x6e\x61\x6d\x65"]), $U4->quote($post["\x75\x73\145\162\156\x61\x6d\145"]), $U4->quote($post["\145\x6d\141\151\x6c"]), $U4->quote($post["\160\x61\163\163\167\157\162\x64"]), $U4->quote($BR), $U4->quote(''));
        $rw->insert($U4->quoteName("\x23\x5f\137\165\163\145\x72\163"))->columns($U4->quoteName($x2))->values(implode("\x2c", $ZW));
        $U4->setQuery($rw);
        $U4->execute();
    }
    public static function updateUserGroup($YH, $rK)
    {
        $YH = (int) $YH;
        $rK = (int) $rK;
        $user = JFactory::getUser($YH);
        if (\in_array($rK, $user->groups)) {
            goto m8;
        }
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $x2 = array("\165\163\x65\162\137\151\144", "\x67\x72\x6f\x75\x70\137\151\x64");
        $ZW = array($U4->quote($YH), $U4->quote($rK));
        $rw->insert($U4->quoteName("\x23\x5f\x5f\165\163\x65\x72\137\165\x73\145\162\147\x72\157\165\160\x5f\155\141\x70"))->columns($U4->quoteName($x2))->values(implode("\x2c", $ZW));
        $U4->setQuery($rw);
        $U4->execute();
        m8:
        $Wo = JFactory::getUser($YH);
        $Wo->groups = $user->groups;
        if (!JFactory::getSession()->getId()) {
            goto Jv;
        }
        $Wo = JFactory::getUser();
        if (!($Wo->id == $YH)) {
            goto Qz;
        }
        $Wo->groups = $user->groups;
        Qz:
        Jv:
        return true;
    }
    public static function removeUserGroups($i1, $V8)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $fi = array($U4->quoteName("\165\163\x65\162\137\151\144") . "\x20\75\40" . $U4->quote($i1), $U4->quoteName("\147\162\x6f\165\x70\x5f\x69\x64") . "\40\75\40" . $U4->quote($V8));
        $rw->delete($U4->quoteName("\x23\137\137\x75\x73\145\162\137\x75\163\x65\162\147\x72\157\x75\160\137\x6d\141\160"));
        $rw->where($fi);
        $U4->setQuery($rw);
        $U4->execute();
    }
    public static function addUser($av)
    {
        $Y3 = "\43\137\137\x6d\151\156\x69\x6f\x72\x61\156\147\145\137\x73\x61\x6d\154\137\x63\165\163\164\157\155\145\x72\137\x64\x65\x74\x61\151\x6c\x73";
        $RX = array("\165\x73\x72\x6c\155\164" => $av + 1);
        self::__genDBUpdate($Y3, $RX);
    }
    public static function showErrorMessage($gs, $E1)
    {
        echo "\40\40\x20\x20\x20\40\40\40\74\x64\151\x76\40\163\164\x79\x6c\x65\75\x22\146\157\x6e\164\x2d\x66\141\155\151\154\171\72\x43\x61\154\x69\142\162\151\x3b\160\x61\144\144\x69\x6e\x67\72\x30\40\x33\45\x3b\42\x3e\xd\xa\x20\x20\40\x20\40\x20\40\x20\40\x20\x20\x20\x3c\x64\x69\x76\40\x73\x74\171\154\x65\75\x22\x63\157\154\x6f\162\x3a\x20\x23\x61\x39\x34\x34\64\62\x3b\142\x61\x63\153\x67\x72\x6f\165\156\144\55\143\x6f\x6c\157\162\x3a\40\x23\146\x32\144\145\144\145\x3b\160\x61\x64\144\151\156\x67\72\40\x31\x35\160\170\73\x6d\x61\162\x67\x69\156\x2d\x62\x6f\164\x74\157\x6d\x3a\x20\62\x30\160\x78\73\x74\145\x78\164\x2d\141\x6c\x69\147\x6e\72\x63\x65\156\164\x65\162\x3b\x62\157\162\144\145\x72\72\x31\x70\170\40\163\157\154\151\x64\x20\x23\105\66\102\63\x42\62\x3b\x66\x6f\x6e\x74\x2d\163\151\x7a\x65\72\61\70\x70\x74\x3b\42\76\40\x45\122\x52\x4f\122\74\57\144\x69\x76\76\15\xa\x20\x20\x20\40\40\x20\40\40\40\x20\x20\40\x3c\x64\x69\166\40\163\x74\171\154\x65\x3d\x22\x63\157\x6c\157\x72\x3a\x20\43\x61\x39\x34\x34\x34\62\x3b\x66\157\x6e\x74\55\163\x69\x7a\x65\72\61\64\160\x74\x3b\x20\155\141\162\147\151\x6e\x2d\x62\x6f\164\x74\157\x6d\x3a\x32\x30\160\x78\73\42\x3e\15\12\x20\x20\x20\x20\40\x20\x20\40\40\x20\40\40\40\x20\40\40\x3c\160\x3e\x3c\163\164\162\157\x6e\147\76\x45\162\162\157\162\72\40\74\x2f\x73\164\162\x6f\x6e\x67\76\40\120\x6c\x65\141\163\145\40\x63\157\156\x74\141\143\x74\40\x74\157\40\171\x6f\165\162\40\101\144\x6d\156\151\163\164\162\x61\164\x6f\x72\56\74\x2f\x70\x3e\15\xa\40\40\40\x20\x20\x20\x20\x20\x20\40\40\40\40\40\x20\40\74\x70\x3e\74\163\164\x72\x6f\x6e\x67\76\120\157\163\163\151\x62\154\145\x20\103\141\165\163\x65\72\40\74\57\x73\164\162\x6f\x6e\x67\x3e";
        echo $gs;
        echo "\74\x2f\x70\76\xd\xa\40\40\40\x20\40\40\40\x20\x20\40\x20\x20\x20\x20\40\x20\74\160\76\74\163\x74\x72\157\156\x67\x3e\x52\145\146\145\162\x65\x6e\x63\145\x20\116\x6f\72\x20\74\57\x73\x74\162\157\156\x67\76";
        echo $E1;
        echo "\x20\40\40\40\x20\x20\x20\x20\40\x20\x20\x20\x3c\57\x64\151\x76\x3e\15\12\x20\40\40\x20\x20\x20\x20\x20\x20\x20\x20\x20\x3c\146\157\162\x6d\x20\x61\143\164\151\x6f\x6e\x3d\x22";
        echo JURI::root();
        echo "\x22\x3e\15\12\x20\x20\x20\x20\x20\40\40\40\x20\x20\x20\40\40\40\x20\x20\74\x64\x69\x76\x20\x73\164\x79\154\145\75\42\x6d\141\x72\x67\151\x6e\72\x33\45\x3b\x64\151\x73\x70\154\x61\171\72\x62\x6c\157\x63\153\x3b\164\x65\x78\164\55\141\154\151\x67\156\72\143\145\x6e\x74\x65\x72\73\42\76\15\12\40\40\40\40\40\x20\40\40\40\x20\40\x20\x20\x20\40\40\x20\x20\40\40\x3c\151\156\x70\165\164\x20\x73\164\x79\x6c\145\75\42\160\x61\x64\144\x69\x6e\147\72\61\x25\x3b\167\151\x64\x74\x68\72\x31\60\x30\x70\x78\73\x62\141\143\153\147\x72\x6f\x75\156\144\72\40\43\x30\x30\71\61\x43\104\40\x6e\157\156\145\40\162\x65\160\145\x61\x74\x20\163\x63\x72\157\154\154\x20\x30\45\x20\60\x25\x3b\143\165\x72\x73\x6f\x72\72\40\x70\157\151\156\x74\145\x72\x3b\146\157\x6e\164\x2d\x73\x69\172\x65\72\61\65\160\170\x3b\142\x6f\x72\x64\145\x72\55\167\151\x64\x74\x68\72\x20\61\x70\x78\73\x62\x6f\162\144\x65\162\x2d\163\x74\171\x6c\145\x3a\x20\x73\157\x6c\x69\144\73\142\x6f\x72\x64\145\162\55\x72\141\144\x69\x75\163\72\40\x33\x70\170\73\x77\150\151\x74\x65\x2d\x73\x70\x61\143\x65\x3a\x20\156\157\x77\x72\x61\160\x3b\142\x6f\170\55\163\x69\172\x69\156\147\72\x20\142\157\162\144\x65\162\x2d\x62\x6f\170\x3b\x62\x6f\x72\144\x65\x72\55\x63\x6f\x6c\x6f\162\72\40\43\60\x30\67\x33\101\101\73\142\x6f\170\55\163\150\141\x64\x6f\167\72\x20\60\160\x78\x20\x31\160\x78\40\x30\x70\170\x20\x72\x67\142\x61\x28\61\62\60\54\40\x32\x30\60\54\40\x32\63\x30\x2c\40\60\x2e\66\x29\x20\151\x6e\x73\x65\x74\x3b\x63\x6f\154\157\x72\x3a\40\43\x46\106\x46\73\42\x20\164\171\160\145\75\42\x73\165\142\155\x69\x74\42\40\x76\x61\154\165\x65\x3d\x22\x44\x6f\x6e\145\42\40\x6f\x6e\103\154\x69\143\x6b\75\x22\x73\145\154\x66\x2e\143\154\157\x73\x65\x28\51\73\x22\x3e\xd\12\x20\x20\40\40\x20\x20\x20\40\x20\40\40\x20\x20\x20\40\x20\74\57\144\x69\x76\76\15\xa\x20\x20\40\40\40\40\x20\40\40\x20\x20\40\x3c\x2f\x66\157\x72\x6d\76\15\12\x20\x20\x20\x20\x20\40\x20\40\74\x2f\x64\151\166\x3e\15\xa\40\x20\40\40\x20\40\x20\40";
        exit;
    }
    public static function getUserGroupID($V8)
    {
        foreach ($V8 as $Wi) {
            $Rp = $Wi;
            VX:
        }
        DD:
        return $Rp;
    }
    public static function loadUserGroups()
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select("\52");
        $rw->from($U4->quoteName("\43\137\137\165\163\x65\162\147\162\157\x75\160\163"));
        $U4->setQuery($rw);
        return $U4->loadRowList();
    }
    public static function CheckUserGroup($Rp)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select("\164\x69\164\154\145");
        $rw->from($U4->quoteName("\x23\137\x5f\165\163\145\x72\147\x72\157\x75\160\163"));
        $rw->where($U4->quoteName("\151\x64") . "\x20\x3d\x20" . $U4->quote($Rp));
        $U4->setQuery($rw);
        return $U4->loadResult();
    }
    public static function getLoginUserGroupId($sP)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select("\x67\162\157\x75\160\137\x69\144");
        $rw->from($U4->quoteName("\x23\137\137\165\x73\x65\162\x5f\165\163\145\162\147\x72\x6f\x75\x70\137\155\x61\160"));
        $rw->where($U4->quoteName("\x75\x73\145\x72\137\x69\x64") . "\x20\75\x20" . $U4->quote($sP));
        $U4->setQuery($rw);
        return $U4->loadColumn();
    }
    public static function isSuperUser()
    {
        $user = JFactory::getUser();
        return $user->get("\x69\x73\x52\x6f\157\x74");
    }
    public static function getJoomlaCmsVersion()
    {
        $sF = new JVersion();
        return $sF->getShortVersion();
    }
    public static function auto_fetch_details()
    {
        $Qp = 60 * 60 * 24;
        $H8 = self::getCustomerDetails();
        $uG = time();
        if (!($uG >= $H8["\141\x75\x74\x6f\137\163\x65\156\144\x5f\145\x6d\141\x69\x6c\137\x74\151\155\145"] || $H8["\141\165\x74\157\x5f\x73\145\156\144\x5f\x65\x6d\141\x69\154\x5f\x74\151\x6d\x65"] == 0)) {
            goto xc;
        }
        $uG = time() + $Qp;
        $Y3 = "\x23\x5f\137\x6d\151\x6e\151\157\x72\x61\x6e\147\x65\137\163\x61\x6d\154\x5f\x63\x75\163\x74\x6f\x6d\x65\162\137\x64\x65\164\141\151\154\163";
        $RX = array("\141\165\x74\x6f\137\163\x65\x6e\x64\137\x65\x6d\141\x69\154\x5f\x74\x69\155\x65" => $uG);
        self::__genDBUpdate($Y3, $RX);
        $g2 = new Mo_saml_Local_Customer();
        $xZ = json_decode($g2->submit_feedback_form(1), true);
        xc:
    }
    public static function renewalMessage($hK, $gC, $M9)
    {
        $XX = "\x20";
        $mk = "\40";
        if (!($M9 == "\160\x6c\165\147\x69\x6e")) {
            goto dg;
        }
        $XX = "\167\x69\144\x74\x68\72\70\x30\x25\x3b\146\154\157\141\164\72\154\145\146\164\x3b\155\x61\x72\x67\x69\x6e\x2d\x6c\x65\x66\164\x3a\x31\60\45";
        dg:
        if (!($hK["\114\151\143\x65\x6e\163\x65\105\x78\x70\x69\162\x65\x64"] == 1)) {
            goto Ih;
        }
        $mk = "\74\x64\x69\x76\40\143\x6c\x61\x73\163\75\42\142\x61\x63\x6b\x67\162\157\165\156\x64\x5f\x63\x6f\x6c\157\x72\x5f\165\x70\144\x61\x74\145\x5f\x6d\x65\x73\163\141\147\145\40\155\163\55\141\x75\164\x6f\x22\40\163\x74\x79\154\145\75\x22" . $XX . "\144\151\x73\160\x6c\141\171\72\142\154\157\143\x6b\x3b\x63\157\154\157\162\x3a\x72\145\144\x3b\x62\141\143\153\147\x72\157\x75\x6e\x64\55\143\157\x6c\x6f\x72\72\x72\147\142\141\x28\62\x35\x31\x2c\x20\62\63\62\54\40\60\x2c\x20\x30\x2e\61\x35\51\73\40\142\157\162\x64\145\x72\72\x73\157\154\151\144\40\x31\160\x78\40\x72\147\142\x61\x28\x32\65\65\54\40\60\54\x20\71\x2c\x20\60\x2e\x33\x36\x29\x3b\160\x61\144\144\151\156\x67\x3a\x20\61\x30\160\170\40\73\x6d\x61\x72\147\x69\156\x3a\40\x31\60\160\170\x20\73\x22\x3e\15\12\40\x20\x20\40\x20\x20\40\40\40\40\40\40\131\157\x75\162\x20\155\x69\156\x69\117\x72\x61\x6e\147\x65\x20\x4a\x6f\x6f\x6d\x6c\x61\40\x53\x41\x4d\x4c\x20\x53\x50\40\160\x6c\165\147\151\x6e\x20\154\x69\143\x65\x6e\x73\145\40\x68\x61\x73\x20\145\170\160\151\162\145\144\40\157\x6e\40\74\x73\164\162\x6f\156\x67\x3e\40" . $gC . "\x3c\x2f\x73\164\162\x6f\156\147\x3e\56\40\124\x68\151\x73\40\150\141\163\x20\x70\x72\x65\166\145\156\164\x65\x64\x20\171\x6f\165\x20\x66\162\157\155\40\x72\x65\143\x65\x69\x76\x69\x6e\147\40\x61\156\171\x20\160\x6c\x75\147\x69\156\x20\165\x70\144\141\164\145\163\40\x63\x6f\x6e\x74\x61\x69\x6e\151\x6e\x67\x20\163\x65\x63\x75\162\151\x74\171\x20\160\x61\x74\143\x68\145\x73\x2c\40\142\x75\147\x20\x66\x69\x78\145\x73\x2c\x20\x6e\145\x77\x20\146\x65\x61\164\165\x72\x65\x73\x2c\40\141\156\x64\40\145\x76\145\156\40\x63\157\x6d\x70\141\x74\x69\x62\x69\x6c\151\164\171\x20\x63\x68\141\156\x67\x65\163\x2e\40\106\x6f\x72\40\x66\x75\x72\164\x68\145\162\40\x69\x6e\x71\165\151\x72\171\x20\160\x6c\x65\141\163\145\x20\x63\157\156\x74\x61\x63\x74\x20\x3c\141\40\x73\164\x79\x6c\x65\75\42\143\x6f\154\157\162\x3a\x72\x65\144\x3b\42\x20\x68\162\145\146\x3d\42\x6d\x61\x69\154\164\157\72\152\x6f\157\155\x6c\141\x73\x75\160\x70\157\x72\x74\x40\170\x65\x63\151\162\151\146\171\x2e\x63\x6f\155\x22\76\x3c\x73\164\x72\157\156\147\76\152\157\x6f\x6d\x6c\141\x73\165\160\x70\157\x72\x74\100\170\x65\143\x75\162\x69\x66\x79\56\143\157\x6d\x3c\x2f\x73\164\x72\x6f\156\147\x3e\x3c\x2f\141\76\xd\12\x20\40\40\x20\40\40\x20\40\x20\40\40\40\x3c\x2f\x64\151\166\x3e";
        Ih:
        if (!($hK["\114\x69\143\x65\156\x73\145\105\x78\160\151\162\171"] == 1)) {
            goto q1;
        }
        $mk = "\74\144\x69\166\40\x63\x6c\x61\163\163\75\x22\x62\x61\x63\x6b\x67\x72\157\165\x6e\144\x5f\143\157\x6c\157\162\137\x75\160\x64\x61\164\x65\137\155\145\163\x73\141\x67\x65\40\x6d\163\55\x61\x75\x74\x6f\42\x20\x73\164\171\154\145\x3d\42" . $XX . "\x64\151\x73\x70\154\141\x79\x3a\x62\x6c\157\143\x6b\x3b\x63\x6f\154\x6f\162\72\x72\145\x64\x20\73\x20\x62\x61\143\153\x67\x72\x6f\165\156\144\55\143\x6f\154\x6f\162\72\162\x67\142\141\50\x32\65\x31\54\x20\62\x33\62\54\40\x30\54\x20\x30\x2e\61\65\51\x3b\40\142\157\162\x64\x65\162\x3a\x73\157\154\x69\x64\40\61\160\170\40\x72\x67\142\x61\x28\x32\65\65\x2c\40\60\54\40\x39\54\x20\x30\56\x33\66\x29\73\x70\x61\144\x64\151\x6e\147\x3a\40\x31\60\160\x78\40\x3b\155\x61\162\x67\x69\156\72\x20\61\x30\160\x78\73\x22\x3e\15\12\40\40\40\40\40\40\x20\40\40\40\40\40\131\157\x75\162\40\155\x69\156\151\117\x72\141\156\x67\145\40\112\157\x6f\x6d\x6c\141\x20\x53\101\x4d\114\40\x53\120\40\x70\154\x75\x67\x69\x6e\40\154\x69\143\145\x6e\x73\x65\x20\x77\151\x6c\x6c\x20\x65\170\x70\x69\x72\x65\x20\x6f\156\74\163\x74\162\x6f\156\x67\76\40" . $gC . "\x3c\57\163\x74\162\x6f\156\147\x3e\56\x20\124\x68\151\x73\40\150\141\x73\x20\x70\162\145\x76\x65\x6e\x74\145\x64\40\171\x6f\x75\40\x66\162\x6f\155\40\162\x65\143\145\x69\166\151\156\147\40\141\x6e\x79\40\160\x6c\165\x67\x69\156\40\x75\160\x64\141\x74\145\163\40\x63\157\x6e\x74\x61\151\x6e\x69\x6e\x67\x20\163\145\143\x75\x72\151\164\171\x20\x70\x61\x74\x63\150\145\163\x2c\x20\x62\165\x67\40\x66\151\x78\145\x73\x2c\40\156\145\x77\x20\x66\x65\x61\x74\165\x72\x65\x73\x2c\x20\141\x6e\x64\40\x65\x76\x65\x6e\x20\x63\x6f\x6d\x70\x61\164\151\x62\x69\x6c\151\x74\171\x20\143\150\141\x6e\147\145\163\x2e\x20\106\157\162\x20\146\165\x72\x74\x68\145\162\x20\x69\x6e\161\x75\x69\162\171\x20\x70\154\x65\141\x73\145\40\x63\x6f\x6e\x74\141\143\x74\x20\74\x61\40\163\x74\171\x6c\145\75\42\143\157\154\x6f\162\72\162\x65\144\73\x22\40\x68\162\x65\146\x3d\x22\x6d\141\x69\x6c\x74\157\x3a\152\157\x6f\x6d\x6c\x61\163\x75\160\x70\x6f\x72\x74\100\170\145\x63\151\x72\x69\x66\x79\56\x63\157\x6d\42\76\74\x73\164\162\x6f\156\147\76\152\x6f\157\155\x6c\141\x73\x75\160\x70\157\x72\164\100\170\x65\143\165\162\151\x66\x79\x2e\x63\x6f\155\x3c\x2f\x73\x74\x72\x6f\x6e\147\76\x3c\57\141\x3e\15\12\40\40\40\40\x20\40\x20\40\x20\40\40\40\x3c\57\144\151\166\76";
        q1:
        return $mk;
    }
    public static function checkIsLicenseExpired()
    {
        $xZ = self::getExpiryDate();
        $C0 = isset($xZ["\154\151\143\x65\x6e\163\x65\105\x78\160\151\x72\171"]) ? $xZ["\x6c\x69\143\x65\156\x73\x65\x45\x78\x70\151\x72\x79"] : "\60\x30\x30\x30\55\x30\60\55\x30\60\x20\60\x30\x3a\60\x30\72\x30\60";
        $Tz = intval((strtotime($C0) - time()) / (60 * 60 * 24));
        $hK = array();
        $hK["\114\151\x63\x65\x6e\x73\x65\x45\170\160\151\x72\171"] = $Tz >= 0 && $Tz < 31 ? TRUE : FALSE;
        $hK["\114\x69\143\x65\x6e\x73\145\x45\x78\160\151\x72\x65\144"] = $Tz > -30 && $Tz < 0 ? TRUE : FALSE;
        $hK["\x4c\x69\x63\145\156\x73\x65\x54\x72\151\141\x6c\105\x78\160\x69\x72\171"] = $Tz > 0 && $Tz < 2 ? TRUE : FALSE;
        return $hK;
    }
    public static function getExpiryDate()
    {
        $U4 = JFactory::getDbo();
        $U4->setQuery($U4->getQuery(true)->select("\52")->from("\43\137\x5f\x6d\x69\156\151\x6f\x72\x61\156\x67\x65\x5f\163\141\155\154\137\x63\x75\x73\164\157\155\145\x72\137\x64\x65\x74\x61\151\154\x73"));
        return $U4->loadAssoc();
    }
    public static function showLicenseExpiryMessage($hK)
    {
        $V0 = self::getExpiryDate();
        $DE = strtotime($V0["\154\x69\143\145\156\x73\x65\x45\x78\x70\151\x72\x79"]);
        $DE = $DE === FALSE || $DE <= -62169987208 ? "\x2d" : date("\x46\40\x6a\54\x20\131\x2c\40\147\72\x69\x20\141", $DE);
        $xZ = JFactory::getApplication()->input->get->getArray();
        $AP = isset($_SERVER["\122\105\121\x55\105\123\124\137\x55\122\111"]) ? $_SERVER["\122\105\121\125\x45\123\124\x5f\x55\122\111"] : '';
        $qG = substr($AP, "\55\x32\63");
        $OR = isset($xZ["\157\160\164\x69\157\x6e"]) ? $xZ["\x6f\x70\164\151\x6f\156"] : '';
        $QW = Joomla\CMS\Factory::getApplication();
        if (!($QW->getName() == "\141\144\155\151\x6e\x69\163\x74\x72\x61\164\157\x72")) {
            goto xN;
        }
        $user = JFactory::getUser();
        $Ky = self::IsUserSuperUser($user);
        xN:
        $d7 = self::getUpgradeURL(self::getLicenseType());
        if (!(($OR === "\143\157\155\137\x6d\151\156\x69\157\x72\x61\x6e\147\x65\x5f\163\x61\x6d\154" || $qG == "\x61\144\x6d\x69\x6e\x69\x73\x74\x72\x61\164\157\x72\57\x69\156\x64\x65\170\x2e\x70\x68\x70") && $Ky)) {
            goto fy;
        }
        if ($hK["\x4c\151\x63\145\156\163\x65\x45\170\x70\x69\x72\x65\x64"] || $hK["\114\151\143\145\x6e\x73\x65\105\x78\x70\151\162\x79"]) {
            goto pm;
        }
        return 0;
        goto HV;
        pm:
        return 1;
        HV:
        fy:
    }
    public static function getLicenseType()
    {
        return "\x4a\x4f\117\115\114\x41\137\123\101\115\114\x5f\123\120\x5f\x45\x4e\124\105\x52\120\122\x49\123\x45\x5f\120\114\x55\107\x49\x4e";
    }
    public static function getUpgradeURL($sI)
    {
        $KG = self::getUserEmail();
        return "\150\x74\x74\x70\163\72\57\57\154\x6f\147\x69\156\56\x78\x65\143\x75\162\151\x66\x79\56\x63\157\155\x2f\x6d\157\141\x73\x2f\x6c\x6f\147\151\156\77\165\x73\145\162\156\x61\x6d\145\75" . $KG . "\46\x72\x65\144\151\162\145\143\164\x55\x72\x6c\75\150\x74\x74\x70\x73\x3a\57\x2f\x6c\157\147\151\x6e\56\170\145\x63\165\x72\x69\146\171\x2e\143\157\x6d\x2f\155\x6f\141\163\57\x61\x64\x6d\x69\x6e\x2f\x63\x75\163\x74\x6f\x6d\x65\x72\57\154\x69\x63\145\x6e\163\145\x72\x65\156\145\167\141\154\163\x3f\162\145\x6e\x65\167\x61\x6c\x72\145\x71\x75\x65\163\164\75" . $sI;
    }
    public static function getUserEmail()
    {
        $hN = self::loadCustomerDetails("\x23\137\x5f\x6d\151\x6e\151\x6f\162\141\x6e\147\145\137\x73\141\x6d\154\x5f\143\x75\163\164\157\155\x65\162\x5f\144\x65\164\x61\151\x6c\x73");
        return $rj = isset($hN["\x65\x6d\x61\151\x6c"]) ? $hN["\x65\155\141\151\x6c"] : '';
    }
    public static function loadCustomerDetails($ML)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select("\52");
        $rw->from($U4->quoteName($ML));
        $rw->where($U4->quoteName("\x69\x64") . "\40\x3d\40\x31");
        $U4->setQuery($rw);
        $V5 = $U4->loadAssoc();
        return $V5;
    }
    public static function getGroupNames($cE)
    {
        $Uj = array();
        foreach ($cE as $Rp) {
            array_push($Uj, self::CheckUserGroup($Rp));
            MX:
        }
        v_:
        return $Uj;
    }
    public static function IsUserSuperUser($user)
    {
        $cE = UtilitiesSAML::getLoginUserGroupId($user->id);
        $Uj = UtilitiesSAML::getGroupNames($cE);
        if (!(in_array("\67", $cE) || in_array("\70", $cE) || in_array("\x61\x64\155\151\x6e\151\163\164\162\141\x74\157\x72", $Uj) || in_array("\x41\144\x6d\x69\156\151\x73\164\x72\141\x74\157\x72", $Uj) || in_array("\x53\x75\160\x65\162\x20\125\x73\145\x72\163", $Uj))) {
            goto wx;
        }
        return true;
        wx:
        return false;
    }
    public static function IsUserManager($user)
    {
        $cE = UtilitiesSAML::getLoginUserGroupId($user->id);
        $Uj = UtilitiesSAML::getGroupNames($cE);
        if (!(in_array("\x36", $cE) || in_array("\115\141\x6e\141\147\145\162", $Uj) || in_array("\155\x61\156\141\x67\x65\162", $Uj))) {
            goto xM;
        }
        return true;
        xM:
        return false;
    }
    public static function IsUserSuperUserChild($user)
    {
        $Rp = UtilitiesSAML::getLoginUserGroupId($user->id);
        $Uj = UtilitiesSAML::getGroupNames($Rp);
        foreach ($Rp as $f9) {
            $kF = empty(UtilitiesSAML::CheckUserParentGroup($f9)) ? '' : UtilitiesSAML::CheckUserParentGroup($f9);
            $D1 = UtilitiesSAML::getGroupNames($f9);
            if (!($kF == "\67" || $kF == "\70" || in_array("\141\x64\155\151\x6e\x69\x73\164\x72\x61\x74\157\x72", $D1) || in_array("\101\x64\155\151\156\x69\163\x74\x72\141\164\x6f\162", $D1) || in_array("\123\x75\160\145\x72\40\x55\163\145\162\163", $D1))) {
                goto kf;
            }
            return true;
            kf:
            jS:
        }
        h9:
        return false;
    }
    public static function IsUserManagerChild($user)
    {
        $Rp = UtilitiesSAML::getLoginUserGroupId($user->id);
        foreach ($Rp as $f9) {
            $kF = empty(UtilitiesSAML::CheckUserParentGroup($f9)) ? '' : UtilitiesSAML::CheckUserParentGroup($f9);
            $D1 = UtilitiesSAML::getGroupNames($f9);
            if (!($kF == "\x36" || in_array("\x4d\x61\x6e\141\x67\x65\x72", $D1) || in_array("\155\x61\156\x61\147\x65\162", $D1))) {
                goto O4;
            }
            return true;
            O4:
            tO:
        }
        Me:
        return false;
    }
    public static function loadDBValues($Xj)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select("\x2a");
        $rw->from($U4->quoteName("\43\x5f\x5f\155\x69\x6e\x69\x6f\162\141\x6e\x67\145\x5f\x73\141\x6d\154\x5f\143\157\x6e\146\151\x67"));
        $rw->where($U4->quoteName("\x69\x64\x70\137\x6e\x61\x6d\145") . "\40\x3d\x20" . $U4->quote($Xj));
        $U4->setQuery($rw);
        return $U4->loadAssoc();
    }
    public static function CheckUserParentGroup($Rp)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select("\160\141\x72\x65\156\164\137\x69\x64");
        $rw->from($U4->quoteName("\x23\137\137\x75\x73\x65\162\x67\x72\x6f\165\x70\x73"));
        $rw->where($U4->quoteName("\x69\144") . "\x20\75\40" . $U4->quote($Rp));
        $U4->setQuery($rw);
        $K1 = $U4->loadResult();
        return $K1;
    }
    public static function getLicensePlanName()
    {
        return "\x6a\157\157\155\x6c\x61\x5f\x73\141\155\154\137\163\163\157\137\145\x6e\x74\x65\162\x70\162\151\163\x65\x5f\x70\154\x61\156";
    }
    public static function generateID()
    {
        return "\x5f" . self::stringToHex(self::generateRandomBytes(21));
    }
    public static function stringToHex($JH)
    {
        $uq = '';
        $Me = 0;
        sL:
        if (!($Me < strlen($JH))) {
            goto jo;
        }
        $uq .= sprintf("\45\60\62\170", ord($JH[$Me]));
        gR:
        $Me++;
        goto sL;
        jo:
        return $uq;
    }
    public static function generateRandomBytes($cM, $SQ = TRUE)
    {
        return openssl_random_pseudo_bytes($cM);
    }
    public static function _custom_redirect($mk, $Oo)
    {
        $QW = JFactory::getApplication();
        $QW->enqueueMessage($mk, $Oo);
        $QW->redirect(JRoute::_("\x69\x6e\144\145\x78\56\160\150\x70\x3f\x6f\x70\164\x69\157\x6e\75\143\157\x6d\x5f\155\x69\x6e\151\157\162\x61\x6e\x67\x65\x5f\x73\x61\155\x6c\46\x74\141\142\x3d\144\157\155\x61\x69\x6e\137\x6d\x61\160\160\151\156\147"));
    }
    public static function getUserProfileData($fC, $SL)
    {
        $Qb = array();
        if (!(isset($SL) && !empty($SL))) {
            goto ho;
        }
        $SL = json_decode($SL, true);
        foreach ($SL as $js) {
            $BI = $js["\141\x74\164\162\137\x6e\141\155\x65"];
            $n2 = $js["\x61\x74\x74\x72\x5f\166\141\x6c\x75\x65"];
            if (!(isset($fC[$n2]) && !empty($fC[$n2]))) {
                goto YN;
            }
            $aK = array();
            $aK["\160\162\x6f\146\151\x6c\x65\x5f\x6b\x65\171"] = $BI;
            $IA = $fC[$n2];
            if (!is_array($IA)) {
                goto CB;
            }
            $IA = $IA[0];
            CB:
            if (!(isset($IA) && !empty($IA))) {
                goto I_;
            }
            $aK["\x70\162\x6f\x66\151\154\x65\137\x76\141\154\165\x65"] = trim($IA);
            array_push($Qb, $aK);
            I_:
            YN:
            WP:
        }
        Gd:
        ho:
        return $Qb;
    }
    public static function checkIfContactExist($MT)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select("\151\x64");
        $rw->from("\x23\x5f\x5f\143\x6f\x6e\x74\141\x63\164\137\x64\145\x74\x61\x69\x6c\163");
        $rw->where($U4->quoteName("\165\163\145\162\x5f\151\x64") . "\x20\75\40" . $U4->quote($MT));
        $U4->setQuery($rw);
        return $U4->loadResult();
    }
    public static function removeIfExistsUserId($MT)
    {
        $hN = self::getUserFieldDataFromTable($MT);
        if (!$hN) {
            goto Xu;
        }
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $fi = array($U4->quoteName("\151\x74\x65\155\137\x69\144") . "\40\x3d\40" . $U4->quote($MT));
        $rw->delete($U4->quoteName("\x23\137\x5f\x66\x69\145\154\144\x73\137\166\x61\154\x75\145\163"));
        $rw->where($fi);
        $U4->setQuery($rw);
        $U4->execute();
        Xu:
    }
    public static function getUserFieldDataFromTable($MT)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select("\x66\x69\x65\154\x64\x5f\151\x64");
        $rw->from("\43\137\137\x66\151\145\154\144\163\x5f\166\141\x6c\165\x65\163");
        $rw->where($U4->quoteName("\151\x74\145\x6d\x5f\151\x64") . "\x20\x3d\40" . $U4->quote($MT));
        $U4->setQuery($rw);
        return $U4->loadColumn();
    }
    public static function gttrlval()
    {
        $H8 = UtilitiesSAML::getCustomerDetails();
        $Oo = $H8["\x73\164\x61\x74\165\x73"];
        if (!(Mo_Saml_Local_Util::is_customer_registered() && Mo_Saml_Local_Util::check($Oo) == "\164\162\x75\145")) {
            goto ai;
        }
        $g2 = new Mo_saml_Local_Customer();
        $hh = $H8["\143\165\163\x74\x6f\x6d\145\162\x5f\x6b\x65\171"];
        $Ts = $H8["\x61\x70\x69\137\x6b\145\x79"];
        $xZ = json_decode($g2->ccl($hh, $Ts), true);
        if (!($xZ != "\156\x75\154\154")) {
            goto mh;
        }
        self::saveTvalue($xZ["\x74\162\x69\x61\x6c"]);
        mh:
        ai:
    }
    public static function getUserProfileDataFromTable($MT)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select("\x70\162\157\x66\151\x6c\145\x5f\153\145\171");
        $rw->from("\x23\137\137\165\163\x65\162\x5f\160\162\x6f\146\x69\x6c\145\163");
        $rw->where($U4->quoteName("\x75\x73\x65\162\x5f\x69\144") . "\40\75\40" . $U4->quote($MT));
        $U4->setQuery($rw);
        return $U4->loadColumn();
    }
    public static function getIdFromFields($Os)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select("\x69\x64");
        $rw->from("\43\x5f\137\146\x69\x65\154\x64\163");
        $rw->where($U4->quoteName("\156\x61\155\145") . "\x20\x3d\x20" . $U4->quote($Os));
        $U4->setQuery($rw);
        return $U4->loadObject();
    }
    public static function selectMaxOrdering($MT)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select("\x4d\101\x58\50\x6f\162\x64\145\x72\151\x6e\x67\x29");
        $rw->from($U4->quoteName("\x23\x5f\137\x75\163\145\x72\x5f\x70\x72\157\x66\151\x6c\x65\x73"));
        $rw->where($U4->quoteName("\x75\163\145\162\x5f\151\144") . "\x20\x3d\40" . $U4->quote($MT));
        $U4->setQuery($rw);
        $ip = $U4->loadResult();
        return isset($ip) && !empty($ip) ? $ip : "\x30";
    }
    public static function saveTvalue($n2)
    {
        $n2 = Mo_saml_Local_Util::encrypt($n2);
        $Y3 = "\43\137\137\x6d\151\156\151\x6f\162\x61\156\x67\145\137\163\x61\155\x6c\137\x63\165\163\164\157\x6d\145\162\137\144\145\x74\141\151\x6c\163";
        $RX = array("\164\x72\x69\x73\164\x73" => $n2);
        self::__genDBUpdate($Y3, $RX);
    }
    public static function _remove_domain_mapp($oz)
    {
        if (empty($oz) || '' == $oz) {
            goto PY;
        }
        self::__remove_domain_mapping_value($oz);
        $mk = "\x44\x6f\x6d\141\151\156\40\x68\141\163\40\142\x65\145\x6e\x20\x72\145\155\x6f\x76\x65\x64\x20\x73\165\143\143\145\x73\x73\146\165\x6c\x6c\171\x2e";
        $Oo = "\163\x75\x63\143\x65\163\x73";
        self::_custom_redirect($mk, $Oo);
        goto ln;
        PY:
        $mk = "\105\162\x72\x6f\162\40\160\162\x6f\143\x65\163\163\x69\x6e\x67\40\171\x6f\x75\162\x20\x72\x65\x71\165\x65\x73\164\56\x20\x50\x6c\145\141\x73\x65\x20\x74\162\x79\x20\141\x67\x61\151\x6e\x2e";
        $Oo = "\x65\x72\x72\x6f\162";
        self::_custom_redirect($mk, $Oo);
        ln:
    }
    public static function rmvlk()
    {
        $QW = JFactory::getApplication();
        $g2 = new Mo_saml_Local_Customer();
        $es = json_decode($g2->update_status(), true);
        $g2->submit_feedback_form(0);
        if (strcasecmp($es["\163\x74\x61\x74\165\x73"], "\123\125\103\x43\105\x53\x53") == 0) {
            goto B3;
        }
        $eo = "\105\x72\x72\x6f\x72\40\162\x65\155\157\166\151\156\147\40\x79\157\x75\162\40\x6c\x69\143\145\x6e\163\145\x20\153\145\171\56\40\120\x6c\x65\141\x73\145\40\164\x72\171\40\141\x67\141\x69\156\x20\x6f\x72\40\x63\x6f\156\x74\x61\143\x74\x20\x75\x73\x20\141\x74\40\74\x61\40\150\x72\145\146\x3d\x22\155\x61\151\x6c\x74\157\x3a\152\x6f\x6f\x6d\154\141\163\x75\x70\160\157\x72\x74\100\x78\x65\143\x75\162\151\146\171\56\x63\157\x6d\42\76\152\157\x6f\x6d\x6c\141\x73\x75\x70\x70\x6f\x72\x74\100\x78\x65\143\x75\162\151\146\x79\56\x63\x6f\155\x20\x3c\x2f\141\76";
        $QW->enqueueMessage($eo, "\x65\x72\162\157\162");
        goto cg;
        B3:
        $Y3 = "\43\137\x5f\x6d\x69\x6e\151\x6f\x72\x61\x6e\x67\x65\x5f\x73\141\x6d\154\x5f\x63\x75\x73\164\x6f\155\145\x72\137\x64\145\164\141\151\154\x73";
        $RX = array("\145\x6d\x61\x69\x6c" => '', "\x70\x61\163\163\167\x6f\x72\144" => '', "\141\x64\x6d\151\156\x5f\160\150\x6f\156\x65" => '', "\x63\x75\163\164\157\155\x65\162\137\x6b\145\x79" => '', "\143\165\163\x74\x6f\x6d\x65\x72\x5f\x74\157\153\145\x6e" => '', "\x61\160\x69\137\x6b\145\171" => '', "\x6c\157\x67\x69\x6e\x5f\163\x74\x61\x74\165\x73" => 1, "\x73\x74\141\164\165\163" => '', "\x73\155\x6c\x5f\154\x6b" => '', "\x73\x74\141\x74\x75\163" => '', "\156\x65\x77\x5f\162\x65\x67\x69\x73\164\x72\141\164\x69\x6f\156" => 0, "\x65\x6d\x61\x69\x6c\x5f\x63\x6f\x75\156\x74" => 0, "\151\156\x5f\143\x6d\160" => '', "\164\162\x69\163\x74\x73" => false, "\165\x73\x72\x6c\155\164" => 0, "\154\151\x63\145\x6e\163\x65\105\170\160\x69\x72\171" => "\x30\x30\x30\x30\55\x30\60\x2d\60\60\40\x30\x30\x3a\60\60\72\x30\x30", "\x73\165\160\x70\157\x72\x74\x45\170\x70\151\162\x79" => "\60\x30\60\60\x2d\x30\x30\x2d\x30\x30\40\60\x30\72\60\x30\x3a\x30\60", "\156\x6f\123\x50" => 0, "\x61\165\x74\x6f\x5f\x73\145\156\x64\137\145\155\141\151\x6c\x5f\164\x69\155\x65" => '');
        self::__genDBUpdate($Y3, $RX);
        $QW->enqueueMessage("\x59\x6f\165\x72\x20\x61\x63\143\157\x75\x6e\x74\40\x68\141\163\x20\x62\x65\145\156\x20\x72\145\155\157\166\x65\x64\40\163\x75\143\x63\145\163\163\146\165\x6c\x6c\171\x2e", "\163\x75\143\143\145\x73\x73");
        cg:
    }
    public static function __remove_domain_mapping_value($oz)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->clear("\x2a");
        $Yj = array($U4->quoteName("\144\157\x6d\141\151\156\137\155\x61\x70\x70\151\x6e\x67") . "\40\75\x20" . $U4->quote(''));
        $fi = array($U4->quoteName("\151\144\x70\x5f\x6e\141\155\145") . "\x20\x3d\40" . $U4->quote($oz));
        $rw->update($U4->quoteName("\43\x5f\137\155\x69\x6e\x69\x6f\162\x61\x6e\x67\145\137\x73\141\155\154\137\x63\x6f\156\x66\151\x67"))->set($Yj)->where($fi);
        $U4->setQuery($rw);
        $U4->execute();
    }
    public static function fetchTLicense()
    {
        $H8 = self::getCustomerDetails();
        $hh = $H8["\x63\x75\x73\x74\157\x6d\145\x72\137\x6b\x65\171"];
        $Ts = $H8["\x61\x70\x69\x5f\153\145\x79"];
        $g2 = new Mo_saml_Local_Customer();
        $xZ = json_decode($g2->ccl($hh, $Ts), true);
        $ri = $xZ["\x6c\x69\143\145\156\x73\145\105\x78\160\151\x72\x79"];
        $dg = $xZ["\163\x75\x70\x70\x6f\x72\x74\x45\x78\160\151\x72\171"];
        $n2 = Mo_saml_Local_Util::encrypt($xZ["\164\x72\x69\141\x6c"]);
        $ux = $xZ["\156\x6f\117\146\x53\x50"];
        $M_ = self::getExpiryDate();
        $DE = strtotime($M_["\x6c\151\143\x65\156\163\145\105\x78\x70\151\x72\x79"]);
        $yB = strtotime($M_["\163\165\160\x70\157\162\164\105\170\160\x69\x72\x79"]);
        $fa = $M_["\164\x72\151\x73\x74\163"];
        $FR = $M_["\x6e\x6f\x53\x50"];
        $XK = isset($H8["\155\x69\156\x69\x6f\162\141\x6e\x67\x65\x5f\154\x65\x78\160\x5f\x6e\x6f\x74\151\x66\x69\143\141\164\151\157\156\x5f\163\x65\156\x74"]) ? $H8["\155\x69\x6e\x69\x6f\162\141\156\147\145\137\x6c\x65\170\x70\137\156\157\164\x69\146\151\x63\141\x74\x69\x6f\x6e\x5f\x73\145\156\164"] : 0;
        if (!$XK) {
            goto Q3;
        }
        $Y3 = "\x23\x5f\x5f\x6d\x69\x6e\151\157\162\x61\x6e\x67\145\137\163\x61\x6d\154\x5f\x63\165\163\164\157\155\x65\x72\x5f\x64\145\164\x61\151\154\x73";
        $RX = array("\x6d\151\156\151\157\162\141\x6e\x67\x65\x5f\146\x69\146\x74\145\145\156\x5f\x64\x61\171\x73\137\142\145\146\157\x72\x65\x5f\154\145\170\160" => 0, "\155\151\156\151\157\162\x61\x6e\147\x65\137\146\x69\x76\145\x5f\x64\x61\171\x73\137\142\145\146\157\162\x65\x5f\x6c\145\x78\160" => 0, "\x6d\151\156\x69\157\162\x61\x6e\147\145\137\x61\x66\164\145\x72\137\154\x65\170\x70" => 0, "\x6d\151\156\151\157\x72\x61\156\147\145\x5f\141\x66\x74\x65\x72\137\146\151\x76\145\x5f\x64\x61\171\163\137\154\x65\x78\160" => 0, "\155\x69\x6e\x69\157\x72\x61\156\x67\145\137\154\145\170\160\137\x6e\x6f\164\x69\x66\151\x63\141\164\x69\x6f\156\137\163\x65\x6e\x74" => 0);
        self::__genDBUpdate($Y3, $RX);
        Q3:
        if (!(strtotime($ri) > $DE)) {
            goto Ff;
        }
        $Y3 = "\43\x5f\137\155\151\x6e\151\x6f\162\x61\156\147\x65\137\x73\x61\155\x6c\x5f\x63\x75\163\164\x6f\155\x65\162\137\x64\x65\x74\141\151\x6c\163";
        $RX = array("\154\x69\x63\x65\x6e\163\x65\x45\x78\160\x69\162\x79" => $ri);
        self::__genDBUpdate($Y3, $RX);
        Ff:
        if (!(strtotime($dg) > $yB)) {
            goto TN;
        }
        $Y3 = "\x23\x5f\x5f\155\151\x6e\151\x6f\x72\141\x6e\147\145\x5f\163\x61\x6d\154\x5f\x63\x75\x73\x74\157\155\x65\x72\x5f\144\145\x74\141\151\154\163";
        $RX = array("\x73\165\x70\160\x6f\162\x74\x45\170\x70\151\162\171" => $dg);
        self::__genDBUpdate($Y3, $RX);
        TN:
        if (!($n2 != $fa)) {
            goto AC;
        }
        $Y3 = "\x23\137\x5f\x6d\151\156\151\157\162\x61\x6e\147\145\x5f\x73\141\155\154\137\x63\x75\163\x74\x6f\x6d\145\162\137\x64\x65\164\141\x69\x6c\163";
        $RX = array("\x74\x72\151\x73\x74\x73" => $fa);
        self::__genDBUpdate($Y3, $RX);
        AC:
        if (!($ux != $FR)) {
            goto YY;
        }
        $Y3 = "\x23\x5f\137\x6d\x69\x6e\151\157\x72\x61\x6e\x67\x65\x5f\163\x61\155\x6c\137\x63\x75\x73\164\157\x6d\x65\162\x5f\x64\x65\164\141\151\x6c\x73";
        $RX = array("\156\x6f\x53\x50" => $ux);
        UtilitiesSAML::__genDBUpdate($Y3, $RX);
        YY:
    }
    public static function createAuthnRequest($vO, $J2, $pl, $nH, $R5, $W2 = "\146\x61\x6c\x73\145", $ie = "\110\x54\x54\x50\55\122\145\x64\151\x72\x65\143\164")
    {
        self::createAndUpdateUpgardeUrl();
        $Le = "\x3c\x3f\170\x6d\x6c\x20\x76\145\162\x73\151\x6f\x6e\75\x22\61\x2e\60\x22\40\145\x6e\x63\x6f\144\151\x6e\147\x3d\42\x55\x54\106\55\70\42\x3f\76" . "\x3c\x73\x61\x6d\x6c\x70\x3a\101\x75\164\150\x6e\x52\x65\x71\x75\145\163\x74\40\170\155\x6c\x6e\163\x3a\x73\141\155\x6c\160\75\42\x75\x72\x6e\x3a\157\141\163\151\x73\x3a\156\x61\155\145\x73\72\x74\143\72\123\101\x4d\114\x3a\x32\x2e\x30\x3a\x70\x72\157\164\157\143\157\154\42\40\170\x6d\x6c\156\x73\x3a\163\x61\x6d\x6c\75\x22\x75\162\x6e\72\x6f\141\x73\x69\163\72\156\x61\155\x65\163\72\x74\143\x3a\x53\x41\x4d\114\72\x32\56\x30\72\141\x73\x73\145\162\x74\x69\157\x6e\x22\x20\x49\x44\x3d\42" . self::generateID() . "\x22\x20\x56\x65\x72\x73\x69\157\156\x3d\x22\x32\56\60\x22\x20\111\x73\x73\x75\x65\111\156\163\x74\x61\x6e\x74\x3d\42" . self::generateTimestamp() . "\42";
        if (!($W2 == "\164\162\x75\145")) {
            goto tn;
        }
        $Le .= "\x20\106\157\x72\143\145\x41\x75\164\x68\156\75\42\164\162\165\x65\x22";
        tn:
        $Le .= "\40\x50\162\x6f\x74\x6f\x63\x6f\154\102\x69\x6e\x64\151\x6e\x67\75\x22\165\x72\156\x3a\157\x61\163\151\163\72\156\x61\x6d\145\163\72\x74\x63\72\123\101\x4d\x4c\x3a\x32\56\60\72\x62\151\x6e\144\x69\x6e\147\x73\72\x48\x54\x54\x50\x2d\x50\117\123\124\x22\40\101\163\x73\x65\162\x74\151\x6f\156\x43\x6f\x6e\x73\165\155\145\x72\123\145\162\166\151\143\145\x55\122\114\75\x22" . $vO . "\x22\x20\x44\145\x73\164\151\x6e\141\164\x69\x6f\156\x3d\42" . $pl . "\42\x3e\xd\12\40\40\40\40\40\40\40\40\40\40\x20\40\x20\x20\x20\40\x20\40\40\40\40\x20\x20\40\x20\x20\x20\40\x3c\163\x61\155\x6c\x3a\x49\163\163\x75\145\162\x3e" . $J2 . "\x3c\x2f\163\141\x6d\x6c\x3a\111\163\163\x75\145\162\x3e\x3c\x73\x61\155\154\160\x3a\x4e\141\155\x65\111\104\x50\157\154\x69\143\171\40\x41\154\x6c\x6f\167\x43\162\x65\141\164\x65\75\x22\164\162\x75\x65\x22\x20\106\x6f\x72\155\141\164\x3d\x22" . $nH . "\x22\x2f\x3e\xd\12\40\40\x20\x20\40\x20\40\x20\x20\40\x20\40\x20\40\40\x20\40\40\40\x20\40\x20\x20\40\x20\x20\40\x20\74\163\141\x6d\x6c\x70\x3a\x52\x65\x71\165\x65\163\164\x65\144\x41\165\164\x68\x6e\x43\157\156\164\145\x78\164\x20\103\157\x6d\x70\x61\162\x69\163\x6f\156\x3d\42\145\170\141\x63\x74\42\x3e\15\12\40\x20\40\40\x20\40\x20\x20\40\40\x20\40\40\x20\x20\40\40\x20\40\40\40\x20\40\x20\x20\40\x20\x20\x20\40\x20\40\x3c\163\x61\x6d\x6c\72\101\x75\x74\150\x6e\x43\x6f\156\164\x65\170\x74\103\154\141\x73\x73\x52\145\146\76\165\x72\x6e\72\x6f\141\x73\151\163\72\x6e\141\155\x65\x73\x3a\x74\143\x3a\x53\101\115\x4c\72\62\x2e\60\72\141\143\72\143\x6c\141\163\163\x65\163\x3a" . $R5 . "\x3c\x2f\163\141\x6d\154\72\x41\165\164\x68\156\103\x6f\x6e\x74\x65\170\x74\103\154\x61\163\163\x52\145\146\76\15\xa\x20\x20\x20\40\40\x20\40\x20\x20\x20\x20\x20\x20\40\x20\40\x20\x20\40\x20\x20\x20\40\x20\x20\x20\x20\x20\74\57\163\x61\x6d\154\160\x3a\x52\145\x71\x75\x65\x73\164\x65\x64\101\x75\164\x68\156\103\157\x6e\164\x65\170\164\x3e\15\12\x20\x20\40\x20\40\x20\40\x20\40\x20\x20\40\40\40\40\x20\x20\x20\x20\x20\x20\40\40\40\x20\x20\x20\40\74\x2f\x73\141\x6d\154\160\x3a\x41\165\x74\150\156\x52\x65\x71\x75\x65\163\x74\76";
        if (!(empty($ie) || $ie == "\110\124\124\120\55\122\x65\144\151\162\x65\143\x74")) {
            goto SB;
        }
        $T0 = gzdeflate($Le);
        $Xn = base64_encode($T0);
        $nC = urlencode($Xn);
        $Le = $nC;
        SB:
        return $Le;
    }
    public static function gt_lk_trl()
    {
        $H8 = self::getCustomerDetails();
        $hh = $H8["\x63\165\x73\164\157\x6d\x65\162\137\153\x65\x79"];
        $Ts = $H8["\141\160\x69\x5f\153\x65\171"];
        $g2 = new Mo_saml_Local_Customer();
        $xZ = json_decode($g2->ccl($hh, $Ts), true);
        $fq = self::getExpiryDate();
        if ($xZ != "\x6e\165\154\154") {
            goto LY;
        }
        $gC = $xZ["\x6c\151\143\x65\x6e\x73\145\x45\170\160\151\x72\171"];
        $xX = 10;
        goto O8;
        LY:
        $gC = isset($xZ["\x6c\x69\143\x65\156\x73\x65\x45\x78\160\x69\x72\x79"]) ? $xZ["\154\151\143\x65\x6e\x73\145\x45\170\160\x69\162\171"] : $fq["\154\x69\143\x65\x6e\x73\x65\x45\170\160\151\x72\x79"];
        $xX = isset($xZ["\x6e\157\117\x66\x55\x73\x65\162\163"]) ? $xZ["\156\157\x4f\x66\x55\163\x65\162\x73"] : 10;
        O8:
        $Tz = intval((strtotime($gC) - time()) / (60 * 60 * 24));
        $hK = array();
        $hK["\114\x69\x63\x65\156\x73\145\105\x78\x70\x69\x72\145\144"] = 0 > $Tz ? TRUE : FALSE;
        $hK["\x4e\157\x6f\x66\x55\163\x65\162\x73"] = $xX;
        return $hK;
    }
    public static function createLogoutRequest($wL, $J2, $pl, $nh = "\x48\124\124\x50\x2d\122\145\x64\x69\162\x65\143\x74", $JX = '')
    {
        $Le = "\x3c\x3f\x78\155\154\x20\x76\145\162\163\x69\157\156\x3d\x22\61\x2e\x30\x22\x20\x65\x6e\x63\x6f\x64\151\156\147\x3d\42\x55\124\106\x2d\70\42\x3f\x3e" . "\x3c\x73\141\x6d\154\160\x3a\114\x6f\x67\x6f\x75\x74\122\x65\161\x75\x65\x73\164\x20\x78\155\154\x6e\x73\72\x73\x61\x6d\x6c\160\x3d\42\165\162\x6e\x3a\157\141\163\x69\x73\x3a\156\141\x6d\145\163\72\x74\143\72\x53\101\x4d\x4c\72\x32\x2e\x30\72\160\162\157\x74\x6f\x63\x6f\154\x22\40\170\155\x6c\156\x73\72\163\141\x6d\x6c\x3d\x22\165\x72\x6e\72\x6f\141\163\x69\x73\x3a\x6e\141\x6d\145\x73\72\164\x63\72\123\101\115\x4c\72\x32\x2e\60\x3a\141\x73\x73\x65\x72\164\x69\x6f\x6e\x22\x20\x49\104\75\x22" . self::generateID() . "\x22\40\x49\163\163\x75\145\x49\156\163\x74\x61\156\164\75\42" . self::generateTimestamp() . "\42\x20\126\x65\162\163\x69\x6f\156\75\42\62\56\60\x22\x20\104\145\x73\164\151\x6e\141\164\x69\x6f\156\75\42" . $pl . "\42\x3e\15\12\11\x9\11\11\11\11\x3c\163\141\x6d\154\72\x49\163\x73\x75\x65\x72\x20\170\x6d\154\156\163\72\x73\x61\155\x6c\x3d\42\x75\x72\156\72\x6f\141\x73\151\163\x3a\x6e\x61\155\145\x73\72\x74\143\72\x53\x41\x4d\114\x3a\x32\56\60\x3a\141\x73\163\x65\162\x74\151\x6f\x6e\x22\x3e" . $J2 . "\74\57\x73\x61\x6d\x6c\72\111\x73\163\165\145\162\x3e\xd\xa\11\x9\11\11\11\x9\x3c\163\x61\x6d\x6c\72\116\141\x6d\145\x49\x44\40\x78\x6d\154\156\x73\x3a\x73\x61\x6d\154\75\x22\165\162\x6e\72\x6f\x61\163\151\163\x3a\156\141\x6d\145\x73\72\x74\x63\72\x53\101\115\x4c\x3a\x32\x2e\60\72\141\163\x73\145\x72\x74\151\157\156\42\76" . $wL . "\x3c\x2f\163\x61\155\154\72\x4e\x61\155\145\111\104\76";
        if (empty($JX)) {
            goto sP;
        }
        $Le .= "\74\163\x61\155\154\160\x3a\123\x65\x73\x73\151\157\156\111\156\x64\x65\170\76" . $JX . "\x3c\x2f\x73\x61\155\x6c\160\72\123\x65\163\x73\151\x6f\x6e\x49\156\x64\x65\170\x3e";
        sP:
        $Le .= "\74\57\163\141\155\x6c\x70\x3a\114\157\x67\157\x75\164\122\145\x71\165\x65\163\x74\76";
        if (!(empty($nh) || $nh == "\110\124\x54\x50\x2d\x52\145\144\151\x72\145\143\164")) {
            goto bI;
        }
        $T0 = gzdeflate($Le);
        $Xn = base64_encode($T0);
        $nC = urlencode($Xn);
        $Le = $nC;
        bI:
        return $Le;
    }
    public static function rmvextnsns()
    {
        self::rmvlk();
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $Yj = array($U4->quoteName("\x65\x6e\x61\x62\x6c\145\144") . "\40\x3d\x20" . $U4->quote(0));
        $fi = array($U4->quoteName("\x65\154\145\155\x65\156\164") . "\40\75\40" . $U4->quote("\x70\x6b\x67\x5f\155\151\x6e\151\x6f\162\141\156\147\145\x73\x61\x6d\154\x73\x73\x6f") . "\117\122" . $U4->quoteName("\x65\x6c\145\155\x65\x6e\164") . "\x20\x3d\x20" . $U4->quote("\x73\141\x6d\x6c\x72\x65\x64\x69\x72\145\x63\x74") . "\x4f\x52" . $U4->quoteName("\x65\154\145\x6d\x65\x6e\164") . "\x20\75\40" . $U4->quote("\155\x69\156\x69\157\x72\141\156\x67\x65\x73\141\155\154") . "\117\122" . $U4->quoteName("\145\x6c\x65\x6d\145\156\x74") . "\x20\x3d\x20" . $U4->quote("\155\x69\156\x69\x6f\162\141\x6e\147\x65\x73\x61\x6d\x6c\x70\x6c\165\147\151\156") . "\x4f\x52" . $U4->quoteName("\145\x6c\x65\155\145\156\x74") . "\40\x3d\x20" . $U4->quote("\143\x6f\155\x5f\155\151\x6e\x69\157\x72\x61\156\x67\x65\x5f\163\141\x6d\154") . "\x4f\x52" . $U4->quoteName("\145\x6c\x65\155\145\156\164") . "\40\75\40" . $U4->quote("\x73\141\x6d\154\154\157\147\157\165\164"));
        $rw->update($U4->quoteName("\x23\x5f\137\145\170\x74\x65\156\x73\x69\157\156\163"))->set($Yj)->where($fi);
        $U4->setQuery($rw);
        $hN = $U4->execute();
        $QW = JFactory::getApplication();
        $QW->enqueueMessage("\x59\x6f\x75\x72\x20\124\x72\151\141\x6c\x20\x70\x65\162\x69\x6f\144\x20\150\x61\x73\x20\x65\x78\160\151\162\145\x64", "\145\x72\x72\x6f\x72");
        $QW->redirect(JRoute::_("\x69\x6e\144\x65\170\56\x70\150\x70"));
    }
    public static function createLogoutResponse($O3, $J2, $pl, $nh = "\x48\x54\x54\x50\x2d\x52\145\x64\x69\x72\145\x63\164")
    {
        $Le = "\x3c\x3f\x78\155\154\x20\166\145\x72\163\x69\157\x6e\x3d\42\x31\x2e\60\42\40\x65\156\143\x6f\144\151\156\x67\x3d\x22\125\124\106\55\x38\x22\x3f\x3e" . "\74\x73\x61\155\154\160\x3a\114\x6f\147\x6f\x75\164\x52\x65\163\160\157\x6e\163\145\x20\x78\x6d\x6c\x6e\x73\72\163\x61\155\x6c\x70\x3d\42\165\x72\156\x3a\157\x61\x73\151\x73\x3a\156\141\155\x65\163\x3a\x74\x63\72\x53\101\115\114\72\x32\x2e\x30\72\160\162\157\164\x6f\143\x6f\154\x22\40\x78\x6d\154\156\x73\72\x73\141\x6d\154\x3d\42\165\162\x6e\x3a\157\141\x73\x69\163\72\x6e\x61\155\145\x73\x3a\164\143\x3a\123\x41\115\x4c\72\62\x2e\60\72\x61\x73\163\x65\162\x74\x69\157\x6e\x22\40" . "\x49\104\75\x22" . self::generateID() . "\x22\40" . "\x56\145\x72\163\151\157\156\x3d\42\x32\x2e\x30\x22\40\111\163\163\x75\x65\111\156\163\164\141\x6e\x74\75\42" . self::generateTimestamp() . "\42\x20" . "\x44\145\163\164\151\x6e\141\x74\151\x6f\x6e\x3d\x22" . $pl . "\42\x20" . "\111\156\x52\145\163\x70\157\156\163\x65\x54\x6f\x3d\42" . $O3 . "\x22\x3e" . "\x3c\163\x61\155\x6c\x3a\x49\x73\163\165\x65\162\40\170\155\154\x6e\x73\72\x73\141\x6d\x6c\x3d\42\x75\162\x6e\72\157\x61\163\151\163\x3a\x6e\141\x6d\145\x73\72\x74\x63\x3a\x53\x41\115\114\x3a\62\x2e\x30\72\141\163\163\145\x72\x74\151\x6f\156\42\x3e" . $J2 . "\x3c\57\x73\141\155\154\x3a\x49\x73\163\165\145\162\x3e" . "\74\x73\141\x6d\x6c\x70\x3a\123\164\x61\164\165\163\x3e\x3c\163\x61\x6d\x6c\x70\72\123\164\x61\x74\165\163\x43\157\144\x65\x20\126\x61\154\165\x65\x3d\42\165\x72\x6e\x3a\157\141\x73\x69\x73\72\156\x61\x6d\x65\163\x3a\164\143\x3a\x53\101\115\114\72\62\x2e\60\72\163\164\141\164\x75\163\72\123\165\143\143\145\163\x73\x22\57\76\74\x2f\x73\x61\x6d\x6c\x70\72\123\164\x61\x74\x75\163\76\74\x2f\163\141\x6d\x6c\160\x3a\114\157\147\157\165\x74\x52\145\163\160\157\x6e\x73\145\76";
        if (!(empty($nh) || $nh == "\110\x54\124\120\x2d\122\145\x64\x69\x72\145\143\164")) {
            goto sV;
        }
        $T0 = gzdeflate($Le);
        $Xn = base64_encode($T0);
        $nC = urlencode($Xn);
        $Le = $nC;
        sV:
        return $Le;
    }
    public static function generateTimestamp($Fr = NULL)
    {
        if (!($Fr === NULL)) {
            goto m1;
        }
        $Fr = time();
        m1:
        return gmdate("\131\x2d\155\x2d\x64\134\x54\110\72\151\x3a\x73\x5c\x5a", $Fr);
    }
    public static function xpQuery(DOMNode $El, $rw)
    {
        static $US = NULL;
        if ($El instanceof DOMDocument) {
            goto AZ;
        }
        $Zw = $El->ownerDocument;
        goto cQ;
        AZ:
        $Zw = $El;
        cQ:
        if (!($US === NULL || !$US->document->isSameNode($Zw))) {
            goto jF;
        }
        $US = new DOMXPath($Zw);
        $US->registerNamespace("\163\x6f\141\x70\x2d\145\156\x76", "\x68\164\164\160\x3a\57\57\163\x63\x68\x65\155\141\x73\56\x78\x6d\154\x73\x6f\141\x70\56\157\x72\x67\x2f\x73\x6f\141\160\57\x65\x6e\x76\x65\154\157\160\x65\x2f");
        $US->registerNamespace("\x73\x61\x6d\x6c\137\x70\162\157\x74\157\143\157\154", "\165\162\156\x3a\x6f\141\x73\151\163\72\156\x61\x6d\x65\163\x3a\x74\x63\x3a\x53\101\x4d\114\72\x32\56\60\72\x70\162\x6f\x74\x6f\x63\157\x6c");
        $US->registerNamespace("\163\x61\x6d\x6c\137\x61\163\x73\145\x72\164\x69\157\156", "\x75\162\x6e\x3a\157\141\x73\x69\x73\72\156\141\x6d\x65\x73\72\164\x63\72\x53\101\x4d\114\x3a\62\x2e\60\72\141\x73\x73\145\162\x74\x69\157\x6e");
        $US->registerNamespace("\163\x61\155\x6c\x5f\155\145\x74\x61\x64\x61\164\141", "\165\x72\x6e\72\157\141\163\x69\163\72\156\141\155\145\163\x3a\164\x63\72\x53\101\115\x4c\x3a\x32\x2e\60\x3a\155\x65\x74\141\x64\x61\x74\x61");
        $US->registerNamespace("\x64\163", "\150\164\164\160\72\x2f\57\x77\167\167\x2e\167\63\x2e\x6f\162\x67\x2f\62\60\60\x30\57\60\x39\57\x78\x6d\x6c\144\x73\x69\x67\43");
        $US->registerNamespace("\x78\145\x6e\143", "\x68\164\x74\160\x3a\x2f\57\x77\167\x77\56\167\x33\x2e\x6f\162\x67\57\x32\60\60\x31\x2f\60\64\57\x78\155\x6c\145\156\x63\x23");
        jF:
        $S5 = $US->query($rw, $El);
        $uq = array();
        $Me = 0;
        qR:
        if (!($Me < $S5->length)) {
            goto RI;
        }
        $uq[$Me] = $S5->item($Me);
        QH:
        $Me++;
        goto qR;
        RI:
        return $uq;
    }
    public static function parseNameId(DOMElement $Gf)
    {
        $uq = array("\126\x61\x6c\165\x65" => trim($Gf->textContent));
        foreach (array("\116\141\155\x65\x51\165\x61\x6c\151\x66\151\x65\162", "\123\120\x4e\141\x6d\x65\121\x75\141\x6c\151\146\x69\x65\162", "\106\157\x72\155\141\x74") as $um) {
            if (!$Gf->hasAttribute($um)) {
                goto p8;
            }
            $uq[$um] = $Gf->getAttribute($um);
            p8:
            sZ:
        }
        fb:
        return $uq;
    }
    public static function get_message_and_cause($hK, $pX)
    {
        $Gd = array();
        if ($hK && $pX) {
            goto jW;
        }
        if ($hK) {
            goto g9;
        }
        if (!$pX) {
            goto gc;
        }
        $Gd["\155\163\x67"] = "\120\154\x65\x61\163\145\x20\143\157\156\x74\141\x63\x74\x20\x79\157\x75\162\x20\141\x64\155\151\156\151\163\164\x72\141\x74\x6f\162\x2e";
        $Gd["\143\141\165\x73\145"] = "\125\x73\x65\162\x20\154\x69\155\x69\164\40\x65\x78\143\145\x65\x64\145\x64\x2e";
        gc:
        goto wB;
        g9:
        $Gd["\x6d\x73\x67"] = "\120\x6c\x65\x61\163\145\x20\143\x6f\x6e\164\141\x63\164\40\171\x6f\x75\162\x20\141\x64\x6d\x69\156\151\x73\x74\x72\x61\x74\157\x72\56";
        $Gd["\143\x61\165\x73\x65"] = "\x4c\151\x63\145\156\163\145\40\x65\170\x70\x69\162\171\40\x64\x61\x74\145\x20\145\170\x63\x65\x65\x64\145\144\56";
        wB:
        goto Sn;
        jW:
        $Gd["\x6d\x73\x67"] = "\120\x6c\x65\x61\x73\x65\40\x63\157\156\164\141\143\164\40\171\x6f\x75\x72\x20\x61\144\x6d\151\156\x69\x73\x74\162\x61\164\x6f\162\56";
        $Gd["\x63\141\165\x73\145"] = "\x4c\151\x63\145\x6e\x73\145\x20\x65\x78\160\151\162\x79\x20\144\141\164\145\40\141\156\144\x20\165\x73\145\162\40\x6c\x69\x6d\x69\x74\x20\x65\170\143\145\145\144\x65\144\56";
        Sn:
        return $Gd;
    }
    public static function xsDateTimeToTimestamp($uG)
    {
        $K9 = array();
        $Ac = "\57\136\x28\134\144\x5c\144\134\144\x5c\x64\51\x2d\50\134\x64\134\x64\x29\55\x28\x5c\144\x5c\144\x29\124\x28\134\144\x5c\x64\51\72\x28\134\x64\x5c\144\x29\x3a\x28\134\144\x5c\144\51\50\x3f\72\134\x2e\134\144\53\51\77\x5a\44\57\104";
        if (!(preg_match($Ac, $uG, $K9) == 0)) {
            goto u_;
        }
        throw new Exception("\111\x6e\x76\141\x6c\151\144\x20\123\101\x4d\x4c\x32\x20\164\151\x6d\x65\x73\x74\141\x6d\160\x20\x70\141\x73\163\x65\x64\40\164\157\40\170\x73\x44\x61\x74\145\124\x69\155\x65\x54\x6f\124\151\x6d\x65\x73\164\x61\x6d\x70\x3a\40" . $uG);
        u_:
        $ph = intval($K9[1]);
        $O6 = intval($K9[2]);
        $G4 = intval($K9[3]);
        $SK = intval($K9[4]);
        $mA = intval($K9[5]);
        $hv = intval($K9[6]);
        $A3 = gmmktime($SK, $mA, $hv, $O6, $G4, $ph);
        return $A3;
    }
    public static function extractStrings(DOMElement $zn, $Ap, $Jv)
    {
        $uq = array();
        $El = $zn->firstChild;
        vH:
        if (!($El !== NULL)) {
            goto o3;
        }
        if (!($El->namespaceURI !== $Ap || $El->localName !== $Jv)) {
            goto iX;
        }
        goto uk;
        iX:
        $uq[] = trim($El->textContent);
        uk:
        $El = $El->nextSibling;
        goto vH;
        o3:
        return $uq;
    }
    public static function validateElement(DOMElement $nd)
    {
        $ml = new XMLSecurityDSigSAML();
        $ml->idKeys[] = "\x49\x44";
        $Tt = self::xpQuery($nd, "\x2e\x2f\144\163\x3a\123\151\147\x6e\x61\164\165\x72\x65");
        if (count($Tt) === 0) {
            goto AI;
        }
        if (count($Tt) > 1) {
            goto fB;
        }
        goto T5;
        AI:
        return FALSE;
        goto T5;
        fB:
        echo "\x58\115\114\123\x65\x63\72\x20\x6d\157\x72\145\40\x74\x68\141\156\x20\x6f\156\145\40\x73\151\147\x6e\x61\164\x75\162\145\x20\145\x6c\x65\x6d\145\x6e\164\40\151\156\40\x72\x6f\x6f\x74\56";
        exit;
        T5:
        $Tt = $Tt[0];
        $ml->sigNode = $Tt;
        $ml->canonicalizeSignedInfo();
        if ($ml->validateReference()) {
            goto pw;
        }
        echo "\x58\x4d\x4c\163\145\143\72\40\x64\x69\147\x65\163\x74\x20\x76\141\154\x69\144\x61\x74\151\x6f\156\40\146\x61\151\154\x65\144";
        exit;
        pw:
        $No = FALSE;
        foreach ($ml->getValidatedNodes() as $uS) {
            if ($uS->isSameNode($nd)) {
                goto EC;
            }
            if ($nd->parentNode instanceof DOMDocument && $uS->isSameNode($nd->ownerDocument)) {
                goto U5;
            }
            goto lv;
            EC:
            $No = TRUE;
            goto qH;
            goto lv;
            U5:
            $No = TRUE;
            goto qH;
            lv:
            UI:
        }
        qH:
        if ($No) {
            goto nG;
        }
        echo "\x58\115\x4c\x53\x65\143\72\x20\x54\x68\x65\40\162\157\x6f\x74\40\x65\154\145\x6d\145\156\164\40\151\x73\x20\x6e\157\164\x20\163\x69\147\x6e\145\x64\x2e";
        exit;
        nG:
        $Gz = array();
        foreach (self::xpQuery($Tt, "\x2e\x2f\144\163\x3a\113\145\171\x49\156\146\x6f\57\x64\x73\x3a\130\65\60\71\x44\x61\x74\x61\x2f\x64\163\x3a\130\x35\60\x39\103\145\x72\x74\151\146\x69\x63\x61\x74\145") as $kO) {
            $dL = trim($kO->textContent);
            $dL = str_replace(array("\xd", "\12", "\x9", "\40"), '', $dL);
            $Gz[] = $dL;
            Jp:
        }
        SY:
        $uq = array("\123\x69\x67\x6e\x61\164\165\162\x65" => $ml, "\x43\x65\162\x74\151\x66\151\x63\x61\164\x65\163" => $Gz);
        return $uq;
    }
    public static function show_error_messages($mk, $Ml)
    {
        echo "\x20\40\x20\40\x20\40\x20\x20\x20\74\x64\151\x76\x20\x73\x74\171\154\145\75\42\x66\x6f\156\164\x2d\x66\x61\155\151\154\x79\72\103\141\x6c\x69\142\x72\x69\x3b\x70\141\x64\144\x69\156\x67\x3a\60\40\63\x25\73\42\x3e\xd\12\x20\40\40\40\40\40\x20\40\x20\x20\40\40\74\x64\151\x76\x20\x73\164\171\x6c\145\x3d\x22\143\x6f\x6c\157\162\72\x20\x23\141\x39\64\x34\64\62\73\x62\141\x63\153\147\x72\157\165\x6e\144\x2d\x63\x6f\x6c\x6f\x72\72\x20\43\x66\x32\x64\x65\x64\145\x3b\160\141\x64\x64\151\x6e\x67\72\40\61\x35\x70\170\x3b\x6d\x61\162\x67\151\x6e\x2d\142\157\164\164\x6f\155\x3a\x20\x32\x30\160\x78\73\x74\145\x78\164\x2d\141\x6c\x69\147\x6e\x3a\x63\145\x6e\x74\x65\x72\73\x62\x6f\x72\144\x65\162\x3a\61\160\170\40\x73\157\154\151\x64\40\x23\105\x36\102\x33\102\62\73\x66\x6f\156\x74\55\163\151\172\145\72\61\x38\160\x74\73\42\76\40\x45\x52\122\117\122\x3c\57\144\151\166\x3e\xd\xa\40\x20\40\40\x20\40\x20\x20\40\x20\40\40\74\x64\x69\166\x20\163\164\x79\x6c\x65\75\x22\x63\157\154\x6f\162\72\40\43\x61\x39\64\x34\64\62\73\x66\157\x6e\164\x2d\x73\151\x7a\145\72\61\x34\160\x74\73\x20\x6d\x61\162\147\x69\156\55\x62\157\164\x74\x6f\x6d\72\62\60\160\x78\73\x22\x3e\15\12\x20\x20\40\40\40\40\x20\40\40\40\x20\x20\x20\40\x20\40\74\x70\76\x3c\163\x74\x72\157\x6e\x67\x3e\105\x72\162\157\162\x3a\40\x3c\57\163\164\162\157\x6e\147\x3e";
        echo $mk;
        echo "\74\57\160\76\15\xa\x20\x20\40\40\40\x20\40\x20\40\40\40\40\x20\x20\x20\40\74\160\76\74\x73\164\162\157\x6e\147\x3e\x50\x6f\x73\163\x69\x62\154\145\x20\103\x61\165\x73\145\72\x20\x3c\57\163\x74\x72\157\156\x67\76";
        echo $Ml;
        echo "\74\x2f\x70\76\15\12\x20\40\40\40\40\x20\x20\40\40\x20\x20\x20\74\57\144\x69\166\76\15\12\x20\40\40\40\40\40\40\40\x20\40\x20\x20\x3c\x66\157\x72\155\40\141\x63\x74\151\x6f\156\75\42";
        echo JURI::root();
        echo "\x22\76\15\xa\x20\x20\x20\x20\40\40\40\x20\x20\40\x20\40\x20\x20\x20\40\x3c\x64\x69\x76\x20\163\x74\171\154\x65\x3d\42\x6d\141\162\x67\x69\156\72\63\45\x3b\144\x69\163\x70\154\141\171\72\142\x6c\157\143\153\x3b\x74\145\170\164\55\x61\x6c\x69\x67\x6e\72\143\x65\156\x74\x65\x72\x3b\x22\x3e\15\12\40\40\40\x20\40\40\40\40\40\40\x20\40\x20\40\40\40\x20\40\x20\x20\x3c\151\x6e\160\x75\164\40\163\164\171\x6c\145\x3d\x22\160\x61\144\x64\151\156\x67\x3a\61\x25\x3b\x77\x69\144\x74\x68\72\x31\x30\60\160\x78\x3b\142\x61\x63\x6b\147\162\x6f\x75\x6e\x64\72\x20\x23\x30\x30\71\x31\x43\x44\x20\156\x6f\x6e\145\40\x72\145\160\145\x61\x74\40\163\143\x72\157\154\x6c\x20\60\x25\x20\60\x25\73\143\x75\x72\x73\x6f\162\72\40\x70\157\x69\x6e\164\145\162\x3b\x66\157\x6e\x74\x2d\163\151\172\145\x3a\x31\65\160\x78\73\x62\x6f\x72\144\145\x72\x2d\167\151\144\164\150\x3a\x20\61\x70\170\x3b\x62\157\162\x64\x65\x72\55\x73\x74\x79\x6c\x65\72\x20\163\157\x6c\x69\x64\73\x62\x6f\x72\x64\x65\162\55\162\141\x64\151\x75\163\x3a\40\63\160\170\73\167\x68\151\164\x65\55\163\x70\x61\x63\145\72\40\156\x6f\x77\162\x61\160\x3b\142\x6f\170\x2d\163\151\x7a\151\156\147\x3a\40\x62\x6f\x72\144\x65\x72\55\x62\x6f\170\x3b\142\157\162\144\x65\x72\55\x63\x6f\154\157\x72\72\x20\x23\x30\60\x37\63\x41\101\73\142\157\170\55\x73\150\x61\x64\x6f\167\x3a\x20\60\160\170\40\61\x70\x78\x20\x30\160\170\x20\x72\x67\142\x61\x28\x31\x32\60\54\40\62\x30\x30\x2c\x20\62\63\60\x2c\40\60\56\x36\x29\x20\x69\156\163\x65\x74\x3b\x63\x6f\x6c\157\162\x3a\40\x23\106\x46\106\x3b\42\x20\164\x79\160\x65\x3d\x22\163\165\x62\155\x69\164\x22\40\x76\141\154\165\145\75\42\x44\x6f\x6e\145\42\40\157\156\103\x6c\x69\143\x6b\75\x22\163\145\x6c\x66\x2e\x63\154\x6f\163\145\x28\x29\73\42\x3e\xd\xa\x20\x20\40\40\x20\x20\40\x20\40\x20\40\x20\x20\40\x20\x20\x3c\57\144\x69\166\76\xd\xa\40\x20\40\40\40\x20\x20\x20\40\x20\x20\40\74\57\x66\x6f\x72\x6d\76\15\xa\40\x20\x20\40\40\x20\x20\x20\x3c\57\144\151\x76\x3e\xd\xa\40\40\x20\40\40\40\x20\40";
        exit;
    }
    public static function validateSignature(array $YC, XMLSecurityKeySAML $BI)
    {
        $ml = $YC["\x53\x69\x67\x6e\x61\x74\165\x72\x65"];
        $lG = self::xpQuery($ml->sigNode, "\56\57\x64\163\x3a\123\x69\147\156\145\144\x49\x6e\146\157\x2f\144\x73\x3a\123\x69\147\x6e\141\x74\x75\162\145\115\145\x74\x68\157\144");
        if (!empty($lG)) {
            goto v8;
        }
        throw new Exception("\115\151\x73\163\x69\x6e\x67\x20\x53\x69\147\x6e\141\x74\165\x72\145\115\x65\164\150\x6f\x64\x20\145\154\145\x6d\145\x6e\164\56");
        v8:
        $lG = $lG[0];
        if ($lG->hasAttribute("\x41\154\x67\157\x72\x69\x74\x68\155")) {
            goto kC;
        }
        throw new Exception("\115\x69\163\x73\151\x6e\147\40\101\x6c\147\x6f\162\x69\x74\150\x6d\55\x61\x74\x74\x72\151\x62\x75\164\x65\x20\157\x6e\40\x53\151\x67\156\141\164\165\x72\145\x4d\x65\164\x68\157\144\40\x65\154\x65\155\145\156\x74\x2e");
        kC:
        $iU = $lG->getAttribute("\101\x6c\147\x6f\162\x69\164\x68\x6d");
        if (!($BI->type === XMLSecurityKeySAML::RSA_SHA1 && $iU !== $BI->type)) {
            goto sw;
        }
        $BI = self::castKey($BI, $iU);
        sw:
        if ($ml->verify($BI)) {
            goto iB;
        }
        throw new Exception("\x55\x6e\x61\142\154\x65\x20\x74\157\x20\166\x61\154\151\x64\141\x74\x65\40\123\x69\x67\x6e\141\x74\165\x72\x65");
        iB:
    }
    public static function castKey(XMLSecurityKeySAML $BI, $tw, $gv = "\160\x75\x62\x6c\151\x63")
    {
        if (!($BI->type === $tw)) {
            goto fv;
        }
        return $BI;
        fv:
        $NO = openssl_pkey_get_details($BI->key);
        if (!($NO === FALSE)) {
            goto qc;
        }
        throw new Exception("\125\x6e\141\x62\x6c\x65\40\x74\x6f\x20\147\x65\164\x20\153\145\x79\40\144\x65\164\141\151\154\x73\40\146\162\157\x6d\40\x58\x4d\x4c\123\145\143\x75\162\x69\x74\171\x4b\x65\171\x53\x41\x4d\x4c\56");
        qc:
        if (isset($NO["\x6b\145\171"])) {
            goto wL;
        }
        throw new Exception("\115\151\x73\163\151\156\147\x20\153\x65\x79\x20\151\x6e\40\160\165\x62\x6c\151\143\40\153\145\x79\40\x64\145\x74\141\x69\154\163\x2e");
        wL:
        $Wn = new XMLSecurityKeySAML($tw, array("\x74\x79\160\145" => $gv));
        $Wn->loadKey($NO["\153\x65\171"]);
        return $Wn;
    }
    public static function processResponse($Xc, $ts, $RD, SAML2_Response $es, $WX, $zQ)
    {
        $pj = $es->getDestination();
        if (!($pj !== NULL && $pj !== $Xc)) {
            goto Qd;
        }
        echo "\104\145\163\x74\151\156\x61\164\151\x6f\156\x20\x69\156\x20\x72\x65\x73\160\x6f\x6e\163\145\40\144\x6f\x65\163\156\x27\x74\40\155\x61\164\x63\x68\x20\164\x68\x65\40\143\165\162\x72\x65\x6e\164\40\125\122\x4c\x2e\x20\104\145\x73\164\x69\156\x61\164\x69\x6f\156\40\151\163\x20\x22" . $pj . "\x22\54\40\x63\x75\162\x72\x65\156\x74\x20\125\x52\x4c\x20\x69\163\x20\42" . $Xc . "\x22\x2e";
        exit;
        Qd:
        try {
            $Hi = self::checkSign($ts, $RD, $WX, $zQ);
        } catch (Exception $Um) {
        }
        return $Hi;
    }
    public static function checkSign($ts, $RD, $WX, $zQ)
    {
        $Gz = $RD["\x43\145\x72\164\151\x66\151\143\141\164\145\163"];
        if (count($Gz) === 0) {
            goto J3;
        }
        $vb = self::findCertificate($ts, $Gz, $zQ);
        goto l7;
        J3:
        $rG = $WX;
        $rG = explode("\73", $rG);
        $vb = $rG[0];
        l7:
        $sT = NULL;
        $BI = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA1, array("\164\x79\160\145" => "\x70\x75\x62\x6c\151\143"));
        $BI->loadKey($vb);
        try {
            self::validateSignature($RD, $BI);
            return TRUE;
        } catch (Exception $Um) {
            echo "\126\x61\x6c\151\144\x61\x74\x69\x6f\156\40\167\151\x74\150\40\x6b\x65\171\40\146\x61\x69\154\x65\x64\x20\167\151\x74\x68\x20\145\170\x63\x65\x70\x74\151\x6f\x6e\72\40" . $Um->getMessage();
            $sT = $Um;
        }
        if ($sT !== NULL) {
            goto O0;
        }
        return FALSE;
        goto up;
        O0:
        throw $sT;
        up:
    }
    public static function validateIssuerAndAudience($vP, $Kp, $ao)
    {
        $J2 = current($vP->getAssertions())->getIssuer();
        $gn = current(current($vP->getAssertions())->getValidAudiences());
        if (strcmp($ao, $J2) === 0) {
            goto KC;
        }
        echo "\111\163\x73\165\145\162\40\x63\141\156\x6e\157\164\40\x62\145\x20\x76\145\x72\x69\x66\x69\x65\x64\56";
        exit;
        goto zJ;
        KC:
        if (strcmp($gn, $Kp) === 0) {
            goto K5;
        }
        $z9 = "\111\x6e\x76\141\x6c\x69\144\40\x61\x75\x64\151\x65\x6e\x63\145\40\x55\122\x49\x2e";
        $Ml = "\x45\x78\x70\145\x63\x74\x65\x64\40" . $Kp . "\x2c\40\146\157\x75\x6e\144\x20" . $gn;
        self::show_error_messages($z9, $Ml);
        goto dh;
        K5:
        return TRUE;
        dh:
        zJ:
    }
    private static function findCertificate($G6, $Gz, $zQ)
    {
        $DG = $Gz[0];
        foreach ($Gz as $rG) {
            $hs = strtolower(sha1(base64_decode($rG)));
            if (!in_array($hs, $G6, TRUE)) {
                goto nx;
            }
            $CA = "\x2d\55\55\x2d\55\x42\x45\x47\x49\116\40\103\x45\x52\x54\111\x46\111\x43\101\x54\105\x2d\55\x2d\x2d\55\xa" . chunk_split($rG, 64) . "\x2d\55\55\x2d\x2d\105\116\104\x20\103\105\122\124\111\106\x49\x43\x41\124\x45\x2d\55\55\x2d\55\xa";
            return $CA;
            nx:
            gN:
        }
        ub:
        $DG = self::sanitize_certificate($DG);
        $bH = Jfactory::getApplication()->input->request->getArray();
        if (array_key_exists("\122\145\x6c\x61\171\123\164\141\x74\x65", $bH) && $bH["\122\x65\x6c\141\171\123\164\x61\164\145"] == "\164\x65\x73\164\126\141\154\x69\144\x61\164\145") {
            goto da;
        }
        echo "\x20\x3c\144\x69\x76\x20\163\164\171\x6c\145\x3d\x22\x63\x6f\154\157\x72\72\40\43\141\71\64\64\x34\x32\x3b\146\x6f\x6e\x74\55\x73\151\172\x65\72\x31\64\x70\164\x3b\x20\x6d\141\x72\147\x69\x6e\55\142\x6f\164\x74\x6f\x6d\x3a\x32\x30\160\170\x3b\42\x3e\x3c\x70\x3e\x3c\142\x3e\x45\x72\x72\x6f\162\72\x20\74\57\x62\76\x57\x65\40\143\x6f\165\x6c\144\40\x6e\x6f\x74\40\163\x69\147\x6e\x20\x79\x6f\x75\40\151\x6e\56\x20\120\154\x65\141\163\145\x20\143\157\156\164\141\143\x74\x20\171\157\165\162\40\x41\x64\155\151\156\151\163\x74\162\141\x74\157\x72\x2e\x3c\57\160\76\x3c\x2f\144\x69\x76\76";
        goto sF;
        da:
        echo "\x3c\144\151\x76\x20\163\164\x79\154\x65\75\42\146\157\156\x74\x2d\146\x61\x6d\x69\154\x79\x3a\103\x61\x6c\151\142\x72\x69\73\x70\141\x64\144\x69\x6e\x67\72\x30\40\x33\45\x3b\x22\76\xd\12\40\x20\40\40\x20\x20\x20\40\x20\x20\40\40\x20\x20\40\40\x3c\x64\x69\166\x20\163\x74\171\154\x65\75\42\x63\157\154\157\x72\72\40\x23\x61\71\64\64\x34\x32\x3b\x62\x61\x63\x6b\147\162\157\165\156\144\x2d\x63\157\154\157\162\72\40\43\x66\x32\144\x65\144\145\73\x70\x61\x64\144\151\x6e\147\x3a\40\x31\x35\160\x78\x3b\155\x61\x72\147\151\156\55\x62\157\164\x74\x6f\155\72\x20\x32\x30\160\x78\73\x74\145\x78\164\55\x61\x6c\151\147\x6e\72\143\145\156\164\x65\162\73\x62\x6f\x72\x64\145\162\x3a\x31\160\x78\x20\163\x6f\x6c\151\144\x20\x23\105\66\102\x33\x42\x32\73\146\x6f\156\x74\55\163\151\x7a\x65\x3a\x31\70\160\164\73\42\76\x20\x45\x52\x52\117\x52\x3c\57\144\151\166\x3e\15\xa\x9\11\x20\40\x20\40\40\x20\40\x20\40\x20\40\x20\74\x64\x69\x76\40\x73\164\171\154\145\75\42\143\x6f\x6c\157\x72\x3a\x20\43\141\x39\64\x34\x34\62\73\x66\157\x6e\164\x2d\163\151\172\145\x3a\61\x34\160\x74\73\x20\x6d\141\162\x67\151\x6e\x2d\x62\x6f\164\x74\x6f\155\72\62\x30\160\x78\x3b\x22\x3e\x3c\160\76\x3c\163\164\x72\157\156\x67\76\x45\162\162\x6f\162\x3a\x20\x3c\57\x73\x74\162\157\x6e\147\76\125\156\141\x62\154\145\x20\164\x6f\x20\x66\151\x6e\x64\x20\141\x20\x63\x65\x72\x74\151\146\x69\x63\141\x74\x65\x20\155\141\164\x63\x68\151\156\147\40\x74\150\x65\x20\143\157\156\x66\151\x67\x75\x72\x65\144\40\x66\151\156\x67\x65\162\x70\162\151\x6e\164\56\74\57\x70\x3e\15\12\11\x9\x20\40\x20\40\40\40\40\x20\x20\x20\x20\x20\x9\11\74\160\76\x3c\163\x74\162\157\156\147\x3e\x50\157\163\x73\x69\x62\154\145\40\103\141\x75\163\x65\72\x20\74\57\x73\x74\162\157\156\x67\76\x43\x6f\x6e\164\145\x6e\x74\x20\x6f\146\40\x27\x58\x2e\65\x30\x39\x20\x43\x65\x72\164\151\146\x69\143\x61\x74\x65\47\40\x66\151\x65\154\x64\x20\x69\x6e\40\x53\x65\162\x76\x69\x63\x65\40\x50\x72\x6f\x76\x69\144\145\162\x20\123\x65\x74\x74\151\x6e\x67\163\40\151\163\40\151\x6e\x63\x6f\162\162\x65\x63\164\x3c\x2f\x70\x3e\xd\xa\x9\11\x9\11\x20\40\40\40\x20\40\40\40\40\x20\x20\40\74\x70\x3e\x3c\x62\76\x45\x78\x70\x65\143\164\x65\x64\40\166\141\154\165\145\x3a\74\x2f\142\76" . $DG . "\x3c\57\x70\x3e";
        echo str_repeat("\x26\156\142\x73\160\x3b", 15);
        echo "\74\x2f\144\x69\166\x3e\xd\xa\x20\40\40\40\40\40\40\40\x20\40\x3c\144\x69\x76\x20\x73\x74\x79\154\145\75\42\155\141\x72\x67\x69\x6e\72\x33\x25\73\x64\x69\163\160\154\x61\171\72\x62\x6c\x6f\143\153\73\164\x65\170\x74\55\x61\x6c\151\147\156\x3a\x63\x65\x6e\164\x65\x72\x3b\42\x3e\15\12\11\x9\x9\11\x20\40\x20\40\74\146\157\162\155\40\141\x63\164\x69\157\156\x3d\x22\x69\156\144\145\170\56\160\x68\x70\x22\76\xd\xa\11\x9\11\11\x20\40\40\40\74\x64\151\166\40\x73\x74\171\x6c\x65\75\42\x6d\141\x72\147\151\156\x3a\x33\x25\73\x64\151\x73\x70\154\141\171\x3a\x62\x6c\157\x63\x6b\x3b\x74\145\x78\x74\55\x61\154\x69\147\156\x3a\143\145\x6e\x74\145\162\73\42\76\74\x69\x6e\160\x75\x74\40\x73\164\171\x6c\145\75\42\x70\x61\x64\x64\151\x6e\x67\72\x31\x25\x3b\167\x69\x64\x74\x68\72\x31\x30\60\160\170\73\x62\x61\x63\153\147\x72\x6f\165\156\x64\x3a\40\43\60\x30\71\61\x43\x44\x20\156\157\156\145\40\162\145\x70\145\x61\x74\x20\x73\143\162\157\x6c\154\x20\60\45\40\60\x25\x3b\x63\x75\x72\x73\x6f\x72\72\x20\160\x6f\x69\x6e\164\x65\x72\x3b\x66\157\156\164\x2d\163\151\172\145\x3a\61\x35\x70\170\73\x62\x6f\162\x64\x65\x72\55\167\151\144\x74\x68\72\x20\x31\x70\x78\x3b\142\157\162\x64\x65\162\55\163\164\x79\154\x65\72\x20\163\x6f\x6c\151\x64\73\x62\157\162\x64\x65\x72\x2d\x72\x61\x64\151\x75\x73\72\x20\x33\160\170\73\167\x68\x69\164\x65\x2d\x73\x70\x61\x63\145\x3a\40\x6e\x6f\167\x72\x61\x70\73\142\157\x78\55\163\x69\172\x69\x6e\147\x3a\x20\142\x6f\x72\x64\x65\x72\55\x62\x6f\x78\73\142\157\162\144\x65\162\x2d\x63\157\x6c\157\162\72\x20\43\60\x30\67\63\x41\x41\73\x62\x6f\x78\55\x73\150\x61\x64\157\x77\72\x20\x30\x70\x78\40\61\x70\170\40\60\160\x78\40\x72\147\x62\x61\50\61\x32\60\x2c\40\x32\60\x30\54\40\x32\63\x30\x2c\x20\x30\x2e\66\x29\40\x69\x6e\x73\145\x74\73\143\157\154\157\x72\x3a\x20\43\x46\x46\x46\73\42\164\171\160\145\x3d\x22\x62\165\164\164\x6f\x6e\42\x20\x76\x61\154\165\x65\x3d\x22\104\x6f\x6e\145\42\40\157\156\x43\x6c\151\x63\x6b\75\42\x73\x65\x6c\146\56\143\154\x6f\x73\145\50\51\x3b\x22\x3e\x3c\57\x64\151\166\76";
        sF:
        exit;
    }
    private static function doDecryptElement(DOMElement $Jz, XMLSecurityKeySAML $A9, array &$pd)
    {
        $hL = new XMLSecEncSAML();
        $hL->setNode($Jz);
        $hL->type = $Jz->getAttribute("\124\171\x70\145");
        $h4 = $hL->locateKey($Jz);
        if ($h4) {
            goto MI;
        }
        throw new Exception("\x43\x6f\165\x6c\x64\x20\156\x6f\x74\40\154\157\143\141\164\x65\x20\153\145\x79\x20\141\x6c\147\157\162\151\x74\x68\155\40\x69\x6e\x20\x65\x6e\x63\x72\171\160\164\x65\144\x20\144\141\x74\x61\x2e");
        MI:
        $gE = $hL->locateKeyInfo($h4);
        if ($gE) {
            goto fZ;
        }
        throw new Exception("\x43\157\165\x6c\x64\40\156\157\x74\40\x6c\x6f\143\x61\164\145\x20\x3c\x64\163\x69\x67\72\x4b\x65\x79\x49\x6e\x66\x6f\76\40\x66\x6f\x72\40\x74\x68\x65\x20\145\x6e\x63\162\171\160\164\x65\144\40\153\x65\171\x2e");
        fZ:
        $S3 = $A9->getAlgorith();
        if ($gE->isEncrypted) {
            goto mP;
        }
        $KW = $h4->getAlgorith();
        if (!($S3 !== $KW)) {
            goto VR;
        }
        throw new Exception("\101\x6c\x67\157\x72\151\x74\150\155\40\x6d\x69\163\155\x61\x74\x63\x68\40\142\x65\x74\x77\x65\x65\x6e\x20\x69\156\160\x75\164\x20\153\145\x79\40\x61\x6e\144\x20\x6b\145\171\x20\x69\x6e\40\155\x65\x73\x73\141\x67\x65\x2e\40" . "\x4b\145\171\40\167\141\163\72\40" . var_export($S3, TRUE) . "\x3b\40\155\145\x73\x73\x61\x67\145\x20\x77\141\x73\x3a\40" . var_export($KW, TRUE));
        VR:
        $h4 = $A9;
        goto YZ;
        mP:
        $e_ = $gE->getAlgorith();
        if (!in_array($e_, $pd, TRUE)) {
            goto Wk;
        }
        throw new Exception("\101\x6c\x67\x6f\x72\x69\164\x68\155\40\x64\x69\x73\141\x62\x6c\145\144\x3a\x20" . var_export($e_, TRUE));
        Wk:
        if (!($e_ === XMLSecurityKeySAML::RSA_OAEP_MGF1P && $S3 === XMLSecurityKeySAML::RSA_1_5)) {
            goto nX;
        }
        $S3 = XMLSecurityKeySAML::RSA_OAEP_MGF1P;
        nX:
        if (!($S3 !== $e_)) {
            goto Dd;
        }
        throw new Exception("\x41\x6c\147\157\162\x69\x74\150\155\40\x6d\x69\163\155\x61\x74\143\150\40\x62\145\164\x77\145\x65\156\40\x69\x6e\x70\x75\x74\40\x6b\x65\x79\40\x61\156\x64\x20\x6b\x65\x79\40\x75\163\x65\x64\x20\x74\x6f\x20\x65\x6e\143\x72\171\160\x74\40" . "\40\164\150\x65\x20\x73\x79\x6d\155\x65\x74\162\151\x63\40\153\x65\171\40\x66\157\162\40\164\x68\145\40\155\x65\x73\x73\x61\x67\x65\x2e\40\113\x65\x79\x20\x77\x61\163\72\x20" . var_export($S3, TRUE) . "\73\x20\x6d\x65\x73\x73\141\x67\x65\40\167\141\x73\72\x20" . var_export($e_, TRUE));
        Dd:
        $wU = $gE->encryptedCtx;
        $gE->key = $A9->key;
        $wJ = $h4->getSymmetricKeySize();
        if (!($wJ === NULL)) {
            goto a9;
        }
        throw new Exception("\x55\x6e\153\x6e\157\x77\156\x20\153\145\x79\40\x73\x69\x7a\x65\x20\x66\157\x72\x20\145\156\x63\x72\171\160\x74\151\x6f\x6e\40\x61\x6c\147\x6f\x72\x69\x74\x68\x6d\x3a\40" . var_export($h4->type, TRUE));
        a9:
        try {
            $BI = $wU->decryptKey($gE);
            if (!(strlen($BI) != $wJ)) {
                goto m2;
            }
            throw new Exception("\x55\156\145\x78\x70\145\143\x74\x65\x64\40\x6b\145\171\x20\163\x69\172\145\x20\x28" . strlen($BI) * 8 . "\x62\151\164\163\51\x20\x66\157\x72\x20\x65\156\143\x72\171\160\164\x69\157\156\40\x61\154\147\x6f\162\151\x74\150\155\x3a\40" . var_export($h4->type, TRUE));
            m2:
        } catch (Exception $Um) {
            $j4 = $wU->getCipherValue();
            $p9 = openssl_pkey_get_details($gE->key);
            $p9 = sha1(serialize($p9), TRUE);
            $BI = sha1($j4 . $p9, TRUE);
            if (strlen($BI) > $wJ) {
                goto lF;
            }
            if (strlen($BI) < $wJ) {
                goto SJ;
            }
            goto pV;
            lF:
            $BI = substr($BI, 0, $wJ);
            goto pV;
            SJ:
            $BI = str_pad($BI, $wJ);
            pV:
        }
        $h4->loadkey($BI);
        YZ:
        $tw = $h4->getAlgorith();
        if (!in_array($tw, $pd, TRUE)) {
            goto N8;
        }
        throw new Exception("\101\154\x67\x6f\x72\x69\164\x68\x6d\x20\x64\x69\x73\141\142\x6c\x65\x64\x3a\40" . var_export($tw, TRUE));
        N8:
        $N5 = $hL->decryptNode($h4, FALSE);
        $Gf = "\x3c\x72\x6f\x6f\x74\x20\170\155\x6c\x6e\163\72\163\x61\x6d\154\75\42\165\162\x6e\72\x6f\141\163\151\x73\x3a\156\141\155\145\x73\x3a\164\143\x3a\x53\x41\115\114\x3a\x32\56\60\72\x61\163\163\x65\x72\164\151\157\156\42\40" . "\170\x6d\x6c\156\163\x3a\170\x73\151\75\x22\x68\164\164\160\72\57\57\167\167\167\x2e\x77\63\x2e\x6f\x72\147\57\x32\60\60\x31\57\130\x4d\114\x53\143\150\145\155\141\55\x69\156\x73\x74\x61\x6e\143\x65\42\x3e" . $N5 . "\x3c\57\x72\x6f\157\x74\x3e";
        $TP = new DOMDocument();
        if (@$TP->loadXML($Gf)) {
            goto C3;
        }
        throw new Exception("\x46\x61\x69\154\x65\x64\x20\x74\x6f\40\160\141\162\x73\x65\40\x64\x65\143\x72\171\160\164\x65\x64\x20\130\115\x4c\56\40\x4d\x61\x79\142\145\x20\164\150\145\x20\x77\x72\157\156\x67\40\163\150\x61\x72\145\144\x6b\145\171\40\x77\141\x73\x20\165\x73\145\x64\77");
        C3:
        $gt = $TP->firstChild->firstChild;
        if (!($gt === NULL)) {
            goto sh;
        }
        throw new Exception("\115\x69\163\163\x69\x6e\x67\40\x65\x6e\x63\162\x79\x70\x74\145\144\x20\145\154\145\155\145\156\x74\x2e");
        sh:
        if ($gt instanceof DOMElement) {
            goto XP;
        }
        throw new Exception("\x44\145\x63\x72\171\x70\x74\x65\144\x20\145\154\145\155\145\156\x74\x20\167\x61\163\x20\x6e\x6f\164\40\141\x63\x74\165\141\154\x6c\171\40\x61\40\104\x4f\115\x45\x6c\x65\x6d\145\x6e\x74\56");
        XP:
        return $gt;
    }
    public static function decryptElement(DOMElement $Jz, XMLSecurityKeySAML $A9, array $pd = array())
    {
        try {
            return self::doDecryptElement($Jz, $A9, $pd);
        } catch (Exception $Um) {
            $vt = UtilitiesSAML::getSAMLConfiguration();
            $r6 = self::get_public_private_certificate($vt, "\160\165\142\154\x69\x63\x5f\x63\145\x72\164\x69\x66\151\143\141\x74\x65");
            $SH = JPATH_BASE . DIRECTORY_SEPARATOR . "\x70\x6c\165\x67\x69\x6e\163" . DIRECTORY_SEPARATOR . "\141\165\x74\x68\145\156\x74\x69\143\x61\164\x69\x6f\156" . DIRECTORY_SEPARATOR . "\x6d\151\156\151\157\162\x61\x6e\147\145\163\x61\x6d\x6c" . DIRECTORY_SEPARATOR . "\x73\x61\x6d\154\x32" . DIRECTORY_SEPARATOR . "\x63\x65\162\x74" . DIRECTORY_SEPARATOR . "\163\x70\55\x63\145\162\x74\151\146\x69\x63\x61\x74\x65\56\x63\162\x74";
            if (!empty($r6)) {
                goto Hj;
            }
            $DG = file_get_contents($SH);
            $mk = "\74\163\x74\162\x6f\156\x67\x3e\120\157\x73\163\151\x62\x6c\145\x20\x43\141\165\x73\x65\x3a\x20\74\57\163\164\x72\x6f\x6e\x67\76\x49\146\x20\x79\157\x75\x20\150\141\166\145\x20\162\145\x6d\157\x76\145\144\x20\143\165\163\164\157\155\x20\x63\x65\162\x74\x69\x66\x69\143\141\164\145\x20\164\150\145\156\x20\160\x6c\x65\141\163\x65\x20\x75\x70\x64\141\x74\145\40\x74\x68\151\x73\x20\x64\x65\x66\x61\165\154\x74\x20\160\165\142\154\151\x63\40\x63\x65\162\164\x69\146\x69\x63\x61\x74\145\x20\151\x6e\40\171\157\x75\x72\x20\x49\x44\x50\x20\163\151\x64\x65\56";
            goto r4;
            Hj:
            $DG = $r6;
            $mk = "\74\163\164\162\157\x6e\147\x3e\120\x6f\163\163\x69\142\154\x65\40\x43\141\165\163\x65\x3a\x20\74\x2f\x73\164\162\x6f\156\147\x3e\x49\x66\x20\x79\x6f\165\x20\150\141\166\145\40\x75\x70\154\157\141\x64\x65\144\40\143\165\x73\x74\157\x6d\40\143\x65\x72\164\151\x66\x69\143\x61\x74\145\40\x74\x68\x65\156\40\160\x6c\145\141\163\x65\x20\165\x70\x64\x61\164\x65\40\x74\150\x69\163\x20\x6e\145\x77\40\143\165\x73\x74\x6f\x6d\x20\x70\165\x62\x6c\151\x63\40\143\145\162\x74\x69\x66\151\x63\x61\x74\x65\x20\x69\x6e\40\171\157\165\162\x20\111\104\x50\x20\163\x69\x64\145\x2e";
            r4:
            echo "\x3c\144\151\x76\40\x73\164\171\154\145\75\x22\146\157\x6e\164\x2d\146\x61\155\x69\154\x79\72\x43\141\x6c\x69\x62\162\151\73\160\141\144\144\x69\x6e\147\72\60\40\x33\45\x3b\x22\x3e";
            echo "\x3c\144\x69\166\x20\x73\164\171\154\x65\75\x22\143\x6f\154\157\x72\x3a\40\43\141\71\64\64\64\62\73\x62\x61\143\153\x67\x72\157\165\x6e\144\x2d\x63\x6f\x6c\x6f\162\x3a\x20\43\146\62\144\x65\x64\145\x3b\160\x61\144\x64\151\156\147\x3a\x20\x31\65\x70\170\x3b\155\x61\162\147\x69\156\55\142\157\164\164\157\155\72\40\x32\60\x70\170\x3b\164\x65\x78\164\55\x61\x6c\151\x67\x6e\x3a\143\145\x6e\164\x65\162\x3b\142\x6f\162\x64\x65\x72\x3a\61\x70\170\40\163\157\x6c\x69\144\40\43\105\x36\x42\63\102\x32\73\146\x6f\156\x74\55\163\151\172\x65\72\x31\70\x70\164\x3b\42\76\40\105\122\x52\x4f\122\x3c\x2f\144\151\166\76\15\12\x20\x20\40\x20\x20\x20\40\x20\x20\40\40\40\40\40\x20\40\40\40\x20\x20\x3c\x64\151\166\40\163\164\171\x6c\145\x3d\x22\143\157\x6c\x6f\162\x3a\40\43\141\71\64\64\64\62\x3b\x66\x6f\156\164\55\x73\x69\172\x65\x3a\x31\x34\x70\x74\x3b\40\x6d\141\162\x67\151\156\55\x62\x6f\x74\164\x6f\155\x3a\62\x30\x70\170\73\42\x3e\x3c\x70\76\74\x73\164\162\x6f\x6e\x67\76\x45\162\162\x6f\x72\72\x20\x3c\57\163\x74\x72\157\156\147\76\x55\156\x61\x62\154\x65\40\164\157\40\146\151\156\144\40\x61\x20\x63\x65\162\x74\151\x66\x69\x63\141\x74\x65\40\x6d\x61\x74\143\x68\x69\x6e\147\x20\x74\x68\x65\40\143\x6f\x6e\x66\x69\147\165\x72\x65\x64\x20\146\x69\156\x67\145\162\x70\x72\x69\x6e\x74\56\74\57\160\x3e\xd\xa\40\40\x20\40\x20\40\x20\40\40\x20\x20\x20\40\40\x20\40\40\40\x20\x20\40\x20\x20\40\74\x70\76" . $mk . "\74\57\x70\76\15\12\x9\11\x9\x20\x20\x20\40\40\x20\40\x20\40\x20\40\40\x20\x20\40\40\40\40\x3c\x70\76\74\x62\76\x45\170\x70\145\x63\164\x65\144\40\166\141\x6c\x75\x65\x3a\x20\x3c\x2f\142\76" . $DG . "\x3c\57\x70\x3e";
            echo str_repeat("\x26\x6e\x62\163\x70\x3b", 15);
            echo "\x3c\57\144\151\x76\76\xd\12\40\x20\40\x20\40\40\40\40\40\40\40\x20\x20\40\40\40\x20\40\40\x20\x20\x20\x20\40\74\144\x69\166\40\163\164\171\x6c\145\x3d\42\155\141\x72\147\151\x6e\x3a\x33\45\x3b\144\x69\163\160\154\x61\x79\x3a\x62\x6c\x6f\143\153\x3b\x74\x65\x78\x74\x2d\x61\154\151\147\156\x3a\143\x65\x6e\164\145\x72\x3b\42\76\xd\xa\40\x20\40\40\40\40\x20\40\x20\40\40\40\x20\40\40\x20\x20\x20\40\x20\x20\x20\40\40\x3c\x66\x6f\162\155\x20\141\x63\x74\x69\x6f\156\75\42\x69\156\x64\x65\x78\56\x70\150\160\x22\76\15\12\40\40\40\40\x20\x20\40\40\40\x20\40\40\40\x20\40\x20\x20\40\x20\x20\x20\40\x20\x20\x20\x20\40\x20\74\144\x69\x76\x20\163\164\x79\154\x65\75\x22\155\141\162\x67\x69\156\x3a\x33\x25\x3b\x64\x69\163\160\154\141\x79\x3a\x62\x6c\x6f\x63\153\73\x74\x65\x78\x74\x2d\141\x6c\151\147\x6e\72\x63\145\156\x74\x65\x72\x3b\42\76\74\x69\x6e\x70\165\x74\x20\163\x74\x79\154\145\75\x22\160\141\x64\x64\151\x6e\x67\x3a\61\x25\x3b\167\x69\x64\164\x68\72\x31\x30\x30\160\x78\73\142\x61\x63\x6b\x67\162\157\165\156\x64\72\40\43\x30\x30\x39\61\x43\104\40\156\x6f\x6e\x65\x20\162\x65\x70\x65\x61\x74\x20\x73\143\x72\157\x6c\154\40\60\45\x20\x30\45\73\143\x75\x72\x73\x6f\162\x3a\40\x70\157\x69\x6e\164\x65\162\73\x66\x6f\x6e\164\x2d\x73\x69\x7a\x65\x3a\x31\65\x70\170\73\142\157\162\x64\x65\x72\x2d\167\x69\x64\164\x68\72\x20\x31\x70\x78\x3b\x62\157\x72\x64\145\x72\55\x73\x74\171\154\x65\x3a\40\163\x6f\154\151\x64\x3b\x62\157\x72\144\145\162\x2d\x72\x61\144\151\165\x73\72\x20\x33\x70\170\x3b\167\150\x69\164\x65\x2d\163\x70\141\143\x65\x3a\x20\x6e\157\167\x72\141\x70\73\x62\157\170\x2d\163\151\172\151\x6e\147\72\x20\142\157\162\144\x65\162\x2d\x62\157\x78\x3b\x62\157\x72\x64\x65\162\x2d\143\157\x6c\x6f\x72\x3a\x20\x23\x30\60\x37\x33\101\101\73\142\x6f\170\x2d\x73\x68\x61\144\157\x77\72\40\60\x70\x78\x20\61\x70\x78\x20\60\160\x78\x20\162\x67\x62\x61\x28\x31\62\60\x2c\x20\x32\x30\60\x2c\x20\62\x33\60\54\40\x30\56\x36\51\40\x69\156\x73\145\164\73\143\157\x6c\157\162\x3a\40\43\106\x46\x46\73\42\x74\x79\x70\x65\75\x22\x62\165\164\164\x6f\156\x22\40\x76\141\x6c\165\145\x3d\42\104\x6f\x6e\x65\x22\40\157\156\103\x6c\151\143\153\75\x22\163\145\154\146\56\x63\154\157\163\145\50\51\x3b\x22\76\x3c\57\144\x69\x76\x3e";
            exit;
        }
    }
    public static function get_mapped_groups($vx, $Mj)
    {
        $Wi = array();
        foreach ($vx as $b6 => $op) {
            if (!(!empty($b6) && in_array(trim($b6), $Mj))) {
                goto j9;
            }
            $Wi[] = $op;
            j9:
            xJ:
        }
        ud:
        return array_unique($Wi);
    }
    public static function get_role_based_redirect_values($vx, $Mj)
    {
        $Wi = array();
        foreach ($vx as $b6 => $op) {
            if (empty($b6)) {
                goto wI;
            }
            if (!($b6 == $Mj)) {
                goto sX;
            }
            $Wi = $op;
            sX:
            wI:
            Di:
        }
        Xg:
        return $Wi;
    }
    public static function get_user_from_joomla($A_, $s7, $rj)
    {
        $U4 = JFactory::getDBO();
        switch ($A_) {
            case "\x75\163\145\x72\x6e\x61\x6d\145":
                $rw = $U4->getQuery(true)->select("\151\144")->from("\x23\137\137\165\163\x65\x72\163")->where("\165\x73\145\162\x6e\x61\x6d\145\x3d" . $U4->quote($s7));
                goto a5;
            case "\x65\155\x61\151\x6c":
                $rw = $U4->getQuery(true)->select("\x69\x64")->from("\x23\x5f\137\165\x73\x65\x72\x73")->where("\145\155\141\151\x6c\x3d" . $U4->quote($rj));
                goto a5;
        }
        RZ:
        a5:
        $U4->setQuery($rw);
        $hN = $U4->loadObject();
        return $hN;
    }
    public static function get_user_credentials($s7)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true)->select("\151\x64\x2c\40\160\x61\163\163\167\x6f\162\144")->from("\x23\x5f\137\x75\163\145\x72\163")->where("\165\163\x65\162\156\141\x6d\x65\75" . $U4->quote($s7));
        $U4->setQuery($rw);
        return $U4->loadObject();
    }
    public static function getEncryptionAlgorithm($kS)
    {
        switch ($kS) {
            case "\x68\x74\x74\x70\72\57\x2f\167\167\167\56\167\63\56\157\x72\x67\57\62\60\60\x31\x2f\60\64\x2f\170\155\154\145\156\x63\43\164\162\x69\x70\154\145\144\x65\163\55\143\x62\143":
                return XMLSecurityKeySAML::TRIPLEDES_CBC;
                goto d9;
            case "\150\x74\164\160\72\57\57\x77\x77\167\x2e\x77\63\x2e\x6f\162\x67\57\62\60\x30\61\57\x30\64\x2f\x78\155\x6c\x65\x6e\143\x23\x61\x65\163\x31\x32\x38\x2d\x63\x62\143":
                return XMLSecurityKeySAML::AES128_CBC;
            case "\150\x74\164\x70\72\x2f\x2f\167\167\167\x2e\167\63\56\157\162\147\57\62\60\x30\61\x2f\x30\64\x2f\x78\155\x6c\145\x6e\x63\x23\141\145\x73\61\71\x32\55\143\142\143":
                return XMLSecurityKeySAML::AES192_CBC;
                goto d9;
            case "\150\x74\x74\160\x3a\x2f\57\x77\x77\x77\56\x77\x33\56\x6f\162\147\57\62\60\x30\x31\x2f\60\x34\x2f\170\x6d\154\145\x6e\143\43\x61\x65\x73\62\65\66\55\143\x62\x63":
                return XMLSecurityKeySAML::AES256_CBC;
                goto d9;
            case "\x68\164\164\160\x3a\x2f\57\x77\167\167\x2e\x77\x33\56\157\x72\x67\57\62\x30\60\61\x2f\60\x34\x2f\x78\155\x6c\x65\156\x63\43\x72\163\x61\x2d\61\x5f\x35":
                return XMLSecurityKeySAML::RSA_1_5;
                goto d9;
            case "\150\164\x74\x70\72\x2f\57\x77\x77\x77\x2e\167\63\x2e\157\162\147\57\62\x30\60\x31\x2f\60\64\57\x78\155\x6c\x65\x6e\x63\x23\x72\x73\x61\55\157\141\145\160\x2d\155\x67\146\61\160":
                return XMLSecurityKeySAML::RSA_OAEP_MGF1P;
                goto d9;
            case "\x68\164\x74\160\72\x2f\57\x77\167\x77\56\167\x33\56\x6f\162\147\57\62\60\60\x30\x2f\60\71\57\x78\x6d\x6c\x64\163\151\x67\43\x64\163\141\55\163\x68\141\x31":
                return XMLSecurityKeySAML::DSA_SHA1;
                goto d9;
            case "\x68\164\x74\x70\x3a\x2f\57\x77\x77\x77\x2e\167\63\56\157\x72\147\x2f\x32\60\x30\60\57\60\x39\x2f\170\x6d\154\144\163\x69\x67\x23\x72\163\141\55\x73\150\141\x31":
                return XMLSecurityKeySAML::RSA_SHA1;
                goto d9;
            case "\150\164\164\x70\72\x2f\57\x77\x77\167\x2e\x77\63\56\x6f\x72\x67\x2f\x32\x30\60\x31\57\x30\x34\x2f\x78\x6d\x6c\144\163\x69\x67\x2d\x6d\x6f\162\x65\x23\x72\163\x61\55\x73\150\x61\62\x35\66":
                return XMLSecurityKeySAML::RSA_SHA256;
                goto d9;
            case "\150\164\164\160\x3a\x2f\x2f\x77\x77\167\x2e\167\x33\x2e\x6f\x72\147\x2f\62\60\x30\61\x2f\x30\x34\57\x78\155\x6c\x64\x73\151\x67\55\155\x6f\x72\145\43\x72\163\141\55\x73\150\141\x33\x38\x34":
                return XMLSecurityKeySAML::RSA_SHA384;
                goto d9;
            case "\150\x74\x74\x70\x3a\57\57\x77\x77\167\56\167\x33\56\x6f\x72\x67\57\62\x30\x30\61\x2f\x30\64\57\x78\155\x6c\144\163\151\147\55\155\157\162\x65\43\x72\163\141\55\x73\x68\141\65\x31\x32":
                return XMLSecurityKeySAML::RSA_SHA512;
                goto d9;
            default:
                throw new Exception("\x49\x6e\x76\x61\x6c\151\x64\x20\105\x6e\x63\x72\x79\x70\x74\151\157\156\x20\x4d\145\x74\150\157\x64\x3a\40" . $kS);
                goto d9;
        }
        IR:
        d9:
    }
    public static function sanitize_certificate($Rf)
    {
        $Rf = preg_replace("\57\133\15\xa\x5d\53\57", '', $Rf);
        $Rf = str_replace("\55", '', $Rf);
        $Rf = str_replace("\102\x45\x47\x49\116\x20\x43\x45\x52\x54\111\x46\x49\103\101\124\105", '', $Rf);
        $Rf = str_replace("\105\x4e\104\x20\x43\105\x52\x54\111\x46\111\103\101\x54\x45", '', $Rf);
        $Rf = str_replace("\x20", '', $Rf);
        $Rf = chunk_split($Rf, 64, "\xd\xa");
        $Rf = "\x2d\x2d\55\55\55\x42\105\x47\x49\x4e\40\103\x45\122\124\x49\106\x49\103\x41\x54\105\55\55\x2d\55\55\15\xa" . $Rf . "\55\x2d\x2d\x2d\55\x45\x4e\104\40\103\x45\x52\x54\x49\106\x49\103\101\x54\x45\x2d\x2d\55\55\55";
        return $Rf;
    }
    public static function desanitize_certificate($Rf)
    {
        $Rf = preg_replace("\x2f\133\15\xa\135\53\57", '', $Rf);
        $Rf = str_replace("\x2d\x2d\x2d\55\x2d\102\x45\107\x49\116\x20\103\105\122\x54\x49\x46\111\103\x41\124\x45\x2d\x2d\55\55\x2d", '', $Rf);
        $Rf = str_replace("\55\55\55\55\55\x45\116\x44\x20\x43\x45\122\x54\x49\106\111\x43\101\x54\105\55\55\x2d\55\x2d", '', $Rf);
        $Rf = str_replace("\x20", '', $Rf);
        return $Rf;
    }
    public static function mo_saml_show_test_result($s7, $wN, $jl)
    {
        ob_end_clean();
        $jl = $jl . "\57\160\154\x75\x67\151\156\x73\x2f\x61\x75\164\x68\145\156\164\x69\143\141\164\x69\157\156\x2f\x6d\151\156\151\157\x72\x61\156\147\x65\163\141\155\x6c\57";
        echo "\74\144\x69\166\x20\163\164\171\154\x65\x3d\42\146\x6f\156\164\x2d\146\x61\155\151\154\x79\72\x43\x61\154\x69\x62\x72\151\73\160\x61\x64\144\151\156\147\72\x30\40\x33\x25\73\x22\76";
        if (!empty($s7)) {
            goto Hx;
        }
        echo "\x3c\x64\x69\166\40\163\x74\171\x6c\145\x3d\42\143\x6f\154\x6f\162\72\40\43\141\71\x34\x34\x34\x32\x3b\x62\x61\x63\153\x67\162\x6f\165\156\144\55\143\157\x6c\157\162\72\x20\43\146\62\144\145\144\145\73\x70\x61\144\144\151\156\x67\x3a\x20\61\65\160\x78\x3b\x6d\141\x72\147\x69\156\x2d\x62\157\164\x74\157\155\72\x20\x32\60\160\x78\x3b\x74\145\x78\164\x2d\x61\x6c\151\x67\156\x3a\143\145\156\x74\x65\x72\x3b\142\157\162\144\145\x72\x3a\61\160\170\40\163\x6f\154\x69\144\40\43\x45\66\x42\x33\x42\62\73\x66\157\x6e\164\55\163\x69\x7a\145\x3a\x31\70\160\x74\73\x22\x3e\x54\105\x53\x54\40\106\x41\x49\x4c\105\x44\74\57\x64\151\166\x3e\15\12\x20\x20\x20\40\40\40\40\x20\x20\40\40\x20\40\x20\40\40\x20\40\x20\40\x3c\x64\151\166\x20\x73\164\x79\154\145\x3d\x22\x63\x6f\154\157\x72\72\40\x23\141\x39\x34\x34\x34\62\x3b\146\x6f\156\x74\x2d\163\151\172\x65\72\x31\x34\x70\164\x3b\x20\155\x61\x72\147\151\x6e\x2d\142\157\164\x74\157\x6d\72\62\60\160\170\73\x22\76\x57\x41\x52\116\x49\x4e\x47\x3a\40\123\x6f\x6d\145\x20\101\x74\x74\162\x69\142\x75\x74\145\x73\40\x44\x69\144\x20\x4e\157\164\x20\115\x61\164\143\x68\56\74\x2f\144\x69\x76\x3e\15\12\x20\40\40\40\40\40\x20\x20\40\40\40\40\40\40\x20\40\40\x20\x20\x20\74\x64\x69\166\x20\163\x74\171\154\x65\75\42\x64\151\x73\x70\154\141\x79\72\142\154\x6f\x63\153\x3b\164\145\170\x74\x2d\x61\x6c\151\x67\x6e\x3a\x63\x65\156\x74\145\x72\x3b\x6d\x61\162\147\x69\156\x2d\x62\157\164\x74\157\155\72\64\x25\73\x22\x3e\x3c\151\155\147\40\x73\x74\171\154\x65\x3d\42\167\x69\x64\164\x68\x3a\61\65\x25\73\42\163\x72\143\75\42" . $jl . "\151\x6d\141\147\145\x73\57\167\x72\x6f\156\x67\x2e\160\x6e\x67\x22\76\74\x2f\144\151\166\76";
        goto wE;
        Hx:
        echo "\74\144\151\166\40\x73\x74\x79\x6c\x65\75\x22\x63\157\x6c\x6f\162\72\x20\43\x33\143\x37\66\x33\x64\x3b\15\12\40\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\40\x20\x20\40\x20\40\40\40\142\141\143\153\147\162\x6f\165\156\x64\55\x63\157\x6c\157\x72\72\40\x23\144\146\x66\60\144\70\73\40\x70\x61\144\144\151\156\x67\72\62\45\73\155\141\162\147\151\156\x2d\x62\x6f\x74\164\x6f\x6d\72\x32\x30\x70\170\73\164\145\170\164\x2d\x61\x6c\151\147\156\x3a\x63\x65\156\164\x65\162\x3b\40\x62\x6f\x72\x64\x65\162\x3a\61\x70\170\40\163\157\154\151\144\x20\x23\x41\x45\x44\x42\71\101\x3b\40\x66\157\x6e\164\55\163\x69\x7a\145\72\x31\70\160\x74\73\42\x3e\124\x45\x53\x54\x20\x53\125\103\x43\105\x53\123\106\x55\114\x3c\x2f\x64\x69\x76\76\15\12\x20\x20\x20\40\40\40\40\x20\x20\x20\x20\40\x20\x20\x20\x20\x20\40\x20\40\74\144\x69\x76\40\x73\164\171\154\x65\x3d\x22\x64\151\x73\160\154\x61\x79\x3a\142\x6c\157\x63\153\73\164\145\x78\164\x2d\x61\154\151\x67\156\72\143\x65\156\x74\145\x72\73\x6d\x61\162\x67\151\x6e\x2d\x62\x6f\x74\x74\157\x6d\72\64\45\73\x22\76\74\x69\155\147\40\163\164\x79\154\x65\75\42\x77\x69\x64\164\150\72\x31\65\x25\x3b\42\x73\162\x63\75\x22" . $jl . "\x69\155\x61\x67\x65\x73\x2f\x67\x72\x65\x65\x6e\x5f\143\150\145\x63\153\56\160\156\147\x22\76\x3c\57\144\x69\166\x3e";
        wE:
        echo "\74\163\160\x61\156\40\163\164\x79\154\145\x3d\42\x66\x6f\x6e\x74\x2d\x73\151\172\145\x3a\61\64\x70\164\73\x22\76\74\x62\x3e\x48\145\154\x6c\x6f\x3c\x2f\x62\76\54\40" . $s7 . "\x3c\57\163\x70\141\156\76\x3c\142\162\57\76\74\x70\x20\163\164\171\154\x65\75\42\146\x6f\x6e\164\x2d\167\x65\151\x67\150\164\72\142\x6f\x6c\144\73\x66\157\156\164\x2d\x73\151\x7a\x65\72\61\64\160\164\x3b\155\x61\x72\147\151\156\x2d\x6c\x65\146\164\72\61\x25\73\x22\76\101\x54\124\122\111\102\125\x54\x45\123\40\x52\105\x43\x45\x49\x56\105\104\72\x3c\57\160\76\xd\12\x20\40\40\40\x20\x20\x20\40\40\40\x20\x20\x20\40\x20\40\x20\40\x20\40\74\x74\x61\x62\154\x65\40\x73\164\171\x6c\145\x3d\42\x62\x6f\x72\x64\x65\162\55\143\157\x6c\x6c\x61\160\x73\x65\72\143\157\154\x6c\x61\x70\x73\x65\x3b\142\x6f\162\x64\x65\162\x2d\163\x70\x61\143\x69\x6e\x67\72\x30\x3b\40\x64\151\x73\x70\x6c\x61\171\x3a\164\x61\142\x6c\145\73\x77\151\x64\x74\150\x3a\x31\60\60\45\x3b\x20\x66\157\x6e\x74\55\x73\x69\172\145\72\61\64\160\164\73\142\141\143\x6b\147\162\x6f\x75\x6e\144\x2d\x63\157\x6c\x6f\162\x3a\x23\105\x44\105\x44\x45\104\x3b\42\x3e\xd\12\x20\40\x20\40\x20\40\40\40\40\40\x20\40\x20\40\40\40\x20\40\x20\40\74\x74\x72\40\x73\164\171\154\x65\x3d\x22\164\x65\170\x74\55\141\x6c\x69\147\156\72\x63\145\156\x74\145\x72\73\42\76\x3c\164\144\40\163\x74\171\154\x65\x3d\42\x66\x6f\156\x74\55\x77\x65\151\147\150\164\x3a\142\157\154\x64\73\142\x6f\162\x64\145\162\72\62\160\x78\x20\x73\x6f\x6c\x69\x64\40\x23\x39\x34\x39\60\x39\x30\x3b\x70\x61\144\144\151\156\147\x3a\62\45\73\x22\76\x41\x54\124\122\111\102\x55\x54\105\40\x4e\101\x4d\x45\x3c\57\x74\x64\x3e\74\x74\144\40\163\164\171\154\145\x3d\42\146\x6f\x6e\x74\x2d\x77\145\x69\147\x68\x74\x3a\142\157\x6c\144\x3b\160\141\144\x64\x69\156\147\72\62\x25\73\142\157\x72\x64\145\162\72\x32\160\170\x20\163\x6f\154\151\x64\x20\43\x39\64\x39\60\71\60\73\40\x77\157\x72\144\x2d\x77\x72\141\160\x3a\142\x72\145\141\153\x2d\167\157\162\x64\x3b\42\76\x41\x54\x54\122\x49\102\125\x54\x45\40\x56\101\114\125\105\x3c\57\x74\x64\76\x3c\57\164\x72\76";
        if (!empty($wN)) {
            goto Vm;
        }
        echo "\x4e\x6f\40\101\x74\x74\x72\151\142\165\164\x65\163\x20\x52\x65\x63\x65\151\x76\145\144\56";
        goto Xt;
        Vm:
        foreach ($wN as $BI => $n2) {
            echo "\74\x74\162\76\74\x74\144\x20\x73\x74\171\x6c\x65\x3d\47\x66\x6f\156\164\x2d\x77\145\x69\x67\150\164\72\x62\157\x6c\144\73\142\157\x72\x64\x65\162\x3a\x32\160\170\x20\163\x6f\154\x69\144\40\x23\x39\64\71\60\x39\x30\x3b\160\x61\x64\x64\x69\x6e\x67\72\x32\45\x3b\47\x3e" . $BI . "\x3c\x2f\164\x64\x3e\x3c\164\x64\40\163\164\171\154\x65\x3d\x27\160\x61\144\x64\x69\156\x67\x3a\x32\x25\x3b\142\157\162\x64\145\x72\x3a\62\x70\x78\40\x73\157\154\x69\144\x20\43\71\64\x39\x30\71\x30\x3b\40\167\157\x72\144\x2d\x77\162\x61\x70\x3a\x62\x72\x65\141\153\55\x77\x6f\162\144\x3b\x27\76" . implode("\x3c\142\x72\57\76", (array) $n2) . "\x3c\57\x74\x64\x3e\x3c\x2f\x74\x72\x3e";
            FC:
        }
        Wr:
        Xt:
        echo "\x3c\57\x74\141\x62\x6c\x65\x3e\74\57\144\151\x76\76";
        echo "\74\x64\x69\x76\40\x73\x74\171\154\x65\75\42\x6d\141\162\147\x69\156\72\x33\x25\x3b\x64\151\x73\x70\x6c\141\x79\x3a\142\154\157\x63\153\73\x74\x65\x78\164\55\141\154\151\x67\x6e\x3a\x63\145\156\x74\145\x72\x3b\42\76\x3c\151\156\160\165\x74\x20\163\x74\x79\154\x65\x3d\42\x70\x61\x64\144\151\156\x67\x3a\61\45\73\x77\151\x64\164\150\x3a\61\x30\60\x70\170\x3b\x62\x61\x63\153\147\162\x6f\x75\156\x64\x3a\x20\43\60\x30\71\x31\x43\104\40\x6e\157\156\x65\x20\x72\145\x70\x65\141\x74\x20\163\x63\162\157\154\x6c\x20\60\45\40\60\45\x3b\x63\x75\162\x73\x6f\162\x3a\x20\x70\x6f\151\x6e\164\x65\x72\x3b\x66\x6f\156\164\x2d\x73\x69\172\x65\72\x31\65\160\170\x3b\x62\157\162\x64\145\x72\x2d\x77\x69\x64\x74\x68\72\40\61\x70\170\x3b\x62\157\x72\x64\145\x72\x2d\163\164\x79\154\145\72\x20\163\157\x6c\151\144\x3b\x62\157\162\144\x65\x72\x2d\162\x61\144\x69\165\163\72\x20\x33\x70\x78\73\x77\x68\151\x74\x65\55\163\160\x61\143\x65\72\x20\x6e\x6f\x77\x72\141\160\x3b\x62\157\x78\x2d\163\x69\x7a\x69\x6e\147\x3a\x20\142\x6f\x72\x64\x65\x72\x2d\x62\x6f\x78\x3b\142\157\162\144\145\x72\x2d\143\x6f\x6c\157\x72\x3a\40\x23\x30\x30\x37\63\101\101\73\x62\x6f\x78\x2d\x73\150\141\144\157\167\x3a\x20\60\x70\x78\x20\x31\x70\x78\40\60\160\170\40\162\147\x62\141\x28\x31\x32\60\x2c\x20\x32\60\60\54\40\x32\63\60\x2c\x20\x30\56\66\x29\x20\x69\156\163\x65\164\x3b\x63\x6f\154\157\162\x3a\40\x23\106\x46\x46\73\42\x74\171\x70\145\75\x22\142\165\164\164\x6f\156\x22\x20\166\x61\x6c\x75\145\75\42\104\x6f\156\x65\42\40\x6f\x6e\x43\154\x69\x63\x6b\x3d\x22\163\145\154\x66\x2e\143\x6c\x6f\x73\145\x28\x29\x3b\42\x3e\x3c\57\144\151\166\76";
        exit;
    }
    public static function postSAMLRequest($Qh, $al, $zQ)
    {
        echo "\x3c\x68\x74\x6d\154\x3e\x3c\x68\145\141\144\x3e\74\163\x63\162\151\x70\164\x20\163\x72\143\x3d\x27\150\164\164\160\x73\x3a\57\57\x63\x6f\144\145\x2e\152\161\x75\145\162\x79\x2e\x63\x6f\x6d\57\x6a\x71\x75\x65\x72\171\x2d\61\x2e\61\61\x2e\x33\x2e\155\x69\156\56\x6a\163\x27\76\74\x2f\x73\143\162\151\x70\164\76\x3c\163\x63\162\151\160\x74\x20\164\171\160\145\75\42\164\x65\x78\164\57\x6a\x61\166\x61\x73\143\162\151\160\x74\x22\76\44\50\x66\x75\156\x63\164\151\x6f\156\x28\51\173\x64\x6f\143\x75\155\x65\x6e\164\56\x66\157\x72\155\x73\x5b\x27\163\141\155\x6c\55\162\x65\161\x75\145\x73\164\x2d\146\x6f\162\x6d\47\135\x2e\163\165\x62\155\151\x74\50\51\73\175\x29\73\74\57\x73\143\162\x69\x70\x74\x3e\74\x2f\x68\145\x61\x64\x3e\74\142\x6f\x64\171\76\120\154\145\141\163\145\40\167\141\151\164\x2e\x2e\x2e\x3c\x66\x6f\162\x6d\x20\141\143\x74\151\157\x6e\x3d\x22" . $Qh . "\x22\x20\x6d\145\x74\150\x6f\144\75\42\160\x6f\163\x74\x22\40\151\x64\75\x22\163\x61\x6d\154\x2d\x72\x65\x71\165\145\x73\164\x2d\x66\157\x72\x6d\42\76\74\151\x6e\x70\165\x74\x20\x74\171\x70\145\75\42\x68\x69\144\144\x65\156\42\x20\156\141\155\145\x3d\x22\123\101\115\114\x52\x65\161\x75\145\x73\164\x22\40\x76\141\154\x75\145\x3d\x22" . $al . "\42\40\x2f\76\74\x69\x6e\x70\165\x74\40\x74\x79\x70\145\x3d\x22\x68\151\144\144\145\156\42\x20\x6e\141\x6d\145\x3d\x22\x52\x65\x6c\x61\171\123\x74\141\164\145\x22\40\x76\x61\154\x75\145\x3d\42" . htmlentities($zQ) . "\x22\x20\x2f\76\x3c\x2f\146\157\x72\155\76\74\57\x62\x6f\x64\171\x3e\74\57\x68\x74\x6d\x6c\x3e";
        exit;
    }
    public static function postSAMLResponse($Qh, $ak, $zQ)
    {
        echo "\74\150\x74\x6d\x6c\76\74\150\145\141\x64\x3e\74\x73\143\162\x69\160\x74\40\163\162\143\x3d\47\x68\164\x74\160\x73\72\57\57\x63\x6f\144\145\x2e\152\161\x75\x65\x72\x79\56\143\157\155\57\152\161\165\x65\x72\171\x2d\61\x2e\x31\61\56\x33\x2e\x6d\x69\156\x2e\152\x73\x27\76\74\x2f\163\x63\162\x69\x70\x74\76\x3c\x73\143\x72\151\x70\164\x20\164\x79\160\x65\75\x22\164\x65\170\x74\x2f\x6a\x61\166\x61\x73\143\162\151\x70\164\42\x3e\x24\50\x66\165\x6e\143\x74\x69\157\x6e\x28\51\173\x64\x6f\143\165\x6d\145\156\164\x2e\146\x6f\x72\x6d\x73\x5b\47\163\x61\x6d\x6c\55\x72\145\161\x75\145\163\164\x2d\146\x6f\x72\x6d\x27\135\x2e\163\x75\x62\x6d\151\164\x28\51\73\175\x29\x3b\74\57\163\x63\x72\151\160\164\x3e\74\57\x68\145\x61\x64\x3e\74\142\157\x64\171\x3e\120\154\x65\141\163\x65\40\167\141\151\164\56\x2e\56\x3c\x66\x6f\162\x6d\40\x61\143\x74\x69\x6f\156\75\42" . $Qh . "\42\40\155\x65\x74\150\157\144\x3d\42\160\x6f\x73\164\42\x20\151\x64\75\42\x73\141\155\154\55\x72\145\x71\x75\145\x73\164\55\146\x6f\162\x6d\42\x3e\x3c\x69\156\x70\x75\164\40\164\171\160\145\75\42\150\x69\144\x64\145\156\x22\x20\x6e\141\155\145\75\42\123\x41\x4d\x4c\122\145\163\160\157\156\163\x65\42\40\166\x61\x6c\165\x65\x3d\42" . $ak . "\x22\x20\x2f\x3e\74\151\x6e\160\x75\x74\40\164\x79\x70\145\x3d\x22\x68\151\x64\x64\145\x6e\42\40\x6e\141\x6d\145\75\42\x52\x65\154\x61\x79\123\x74\x61\164\145\42\40\166\141\x6c\165\145\75\x22" . htmlentities($zQ) . "\x22\40\x2f\76\74\57\x66\x6f\162\155\76\74\x2f\142\157\144\171\76\x3c\57\150\164\x6d\x6c\x3e";
        exit;
    }
    public static function insertSignature(XMLSecurityKeySAML $BI, array $Gz, DOMElement $nd = NULL, DOMNode $qF = NULL)
    {
        $ml = new XMLSecurityDSigSAML();
        $ml->setCanonicalMethod(XMLSecurityDSigSAML::EXC_C14N);
        switch ($BI->type) {
            case XMLSecurityKeySAML::RSA_SHA256:
                $gv = XMLSecurityDSigSAML::SHA256;
                goto Po;
            case XMLSecurityKeySAML::RSA_SHA384:
                $gv = XMLSecurityDSigSAML::SHA384;
                goto Po;
            case XMLSecurityKeySAML::RSA_SHA512:
                $gv = XMLSecurityDSigSAML::SHA512;
                goto Po;
            default:
                $gv = XMLSecurityDSigSAML::SHA1;
        }
        d4:
        Po:
        $ml->addReferenceList(array($nd), $gv, array("\x68\164\164\160\72\57\57\167\167\167\x2e\x77\x33\56\x6f\x72\x67\x2f\x32\60\60\x30\x2f\x30\71\x2f\170\x6d\154\144\x73\x69\147\43\x65\x6e\x76\145\x6c\157\160\x65\144\x2d\163\x69\x67\x6e\x61\x74\x75\x72\x65", XMLSecurityDSigSAML::EXC_C14N), array("\151\144\137\156\141\x6d\145" => "\111\x44", "\157\x76\x65\162\167\x72\151\164\145" => FALSE));
        $ml->sign($BI);
        foreach ($Gz as $Rf) {
            $ml->add509Cert($Rf, TRUE);
            Hi:
        }
        Jz:
        $ml->insertSignature($nd, $qF);
    }
    public static function __genDBUpdate($Y3, $RX)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        foreach ($RX as $BI => $n2) {
            $iB[] = $U4->quoteName($BI) . "\40\75\40" . $U4->quote($n2);
            Qy:
        }
        Ic:
        $rw->update($U4->quoteName($Y3))->set($iB)->where($U4->quoteName("\x69\x64") . "\40\75\x20\x31");
        $U4->setQuery($rw);
        $U4->execute();
    }
    public static function signXML($Gf, $SH, $J3, $Bl, $a9 = '')
    {
        $VF = array("\164\x79\160\145" => "\160\x72\x69\166\x61\164\x65");
        if ($Bl == "\122\x53\x41\x5f\123\110\x41\x32\65\66") {
            goto Cr;
        }
        if ($Bl == "\x52\x53\101\x5f\x53\110\x41\63\x38\64") {
            goto uE;
        }
        if ($Bl == "\x52\123\x41\x5f\x53\x48\101\x35\61\x32") {
            goto za;
        }
        $BI = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA1, $VF);
        goto RP;
        Cr:
        $BI = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA256, $VF);
        goto RP;
        uE:
        $BI = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA384, $VF);
        goto RP;
        za:
        $BI = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA512, $VF);
        RP:
        $BI->loadKey($J3, TRUE);
        $E_ = file_get_contents($SH);
        $yA = new DOMDocument();
        $yA->loadXML($Gf);
        $Bu = $yA->firstChild;
        if (!empty($a9)) {
            goto Tr;
        }
        self::insertSignature($BI, array($E_), $Bu);
        goto Pv;
        Tr:
        $fG = $yA->getElementsByTagName($a9)->item(0);
        self::insertSignature($BI, array($E_), $Bu, $fG);
        Pv:
        $cu = $Bu->ownerDocument->saveXML($Bu);
        $Ai = base64_encode($cu);
        return $Ai;
    }
    public static function getSAMLConfiguration($Qo = '')
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select("\x2a");
        $rw->from($U4->quoteName("\x23\x5f\x5f\155\151\x6e\x69\x6f\x72\141\x6e\x67\145\x5f\163\141\155\x6c\137\x63\x6f\x6e\146\x69\147"));
        if (empty($Qo)) {
            goto T2;
        }
        $fi = array($U4->quoteName("\x69\144\x70\x5f\x65\156\x74\151\164\171\x5f\x69\x64") . "\40\75\x20" . $U4->quote($Qo));
        $rw->where($fi);
        T2:
        $U4->setQuery($rw);
        $fC = $U4->loadAssocList();
        return $fC;
    }
    public static function getRoleMapping($uC)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select("\x2a");
        $rw->from($U4->quoteName("\43\137\137\x6d\x69\x6e\151\x6f\x72\141\156\x67\145\137\163\141\x6d\x6c\137\x72\x6f\154\145\137\155\141\x70\x70\151\156\147"));
        $rw->where($U4->quoteName("\151\144\160\137\x69\x64") . "\x20\75\40" . $uC["\151\x64"]);
        $U4->setQuery($rw);
        $TE = $U4->loadAssoc();
        return $TE;
    }
    public static function updateCurrentUserName($sP, $Os, $s7)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $Yj = array($U4->quoteName("\156\x61\x6d\x65") . "\x20\x3d\40" . $U4->quote($Os), $U4->quoteName("\x75\x73\x65\162\156\141\155\x65") . "\40\75\x20" . $U4->quote($s7));
        $fi = array($U4->quoteName("\x69\x64") . "\40\x3d\40" . $U4->quote($sP));
        $rw->update($U4->quoteName("\x23\137\137\x75\x73\x65\162\163"))->set($Yj)->where($fi);
        $U4->setQuery($rw);
        $U4->execute();
    }
    public static function updateUsernameToSessionId($s7, $Jg)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $Yj = array($U4->quoteName("\x75\163\x65\162\156\x61\155\145") . "\x20\x3d\x20" . $U4->quote($s7));
        $fi = array($U4->quoteName("\163\145\x73\x73\x69\157\x6e\137\151\x64") . "\40\x3d\x20" . $U4->quote($Jg));
        $rw->update($U4->quoteName("\43\137\137\x73\x65\x73\x73\x69\x6f\156"))->set($Yj)->where($fi);
        $U4->setQuery($rw);
        $U4->execute();
    }
    public static function auto_update_metadata($DL)
    {
        $wd = $DL["\155\144\x61\x74\x61\x5f\x73\x79\156\x63\x5f\x69\156\x74\x65\x72\166\141\154"];
        $sP = $DL["\x69\144"];
        $uG = time();
        if (!($uG >= $DL["\x6d\x65\x74\x61\144\141\x74\x61\137\143\x68\145\x63\x6b\x5f\x74\x69\155\x65\163\164\x61\155\x70"] || $DL["\155\x65\x74\141\144\x61\164\x61\137\143\150\x65\x63\153\137\x74\151\x6d\x65\x73\x74\x61\x6d\160"] == 0)) {
            goto x8;
        }
        if ($wd == "\150\157\165\x72\154\171") {
            goto Cl;
        }
        if ($wd == "\144\x61\151\x6c\171") {
            goto LL;
        }
        if ($wd == "\167\145\x65\153\x6c\171") {
            goto nI;
        }
        $Qp = 60 * 60 * 24 * 7 * 30;
        goto I5;
        nI:
        $Qp = 60 * 60 * 24 * 7;
        I5:
        goto lg;
        LL:
        $Qp = 60 * 60 * 24;
        lg:
        goto g6;
        Cl:
        $Qp = 60 * 60;
        g6:
        $uG = time() + $Qp;
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $Yj = array($U4->quoteName("\x6d\145\x74\141\144\141\x74\141\x5f\143\x68\x65\x63\x6b\137\x74\151\155\145\163\164\141\x6d\x70") . "\40\x3d\x20" . $U4->quote($uG));
        $fi = array($U4->quoteName("\151\144") . "\x20\x3d\x20" . $U4->quote($sP));
        $rw->update($U4->quoteName("\43\137\137\155\x69\156\151\157\x72\x61\156\x67\145\x5f\163\141\155\154\x5f\143\157\156\x66\151\x67"))->set($Yj)->where($fi);
        $U4->setQuery($rw);
        $U4->execute();
        $Qh = $DL["\155\145\x74\x61\x64\141\164\x61\137\165\162\x6c"];
        if (!($DL["\141\x75\164\157\x5f\163\x79\x6e\143\x5f\x65\156\x61\x62\154\145"] == "\x6f\x6e")) {
            goto OG;
        }
        require_once JPATH_SITE . DIRECTORY_SEPARATOR . "\x61\x64\x6d\x69\x6e\151\163\164\x72\x61\x74\x6f\162" . DIRECTORY_SEPARATOR . "\143\x6f\x6d\x70\x6f\x6e\145\x6e\x74\x73" . DIRECTORY_SEPARATOR . "\143\x6f\x6d\137\x6d\x69\156\x69\157\x72\x61\156\147\x65\x5f\163\x61\155\154" . DIRECTORY_SEPARATOR . "\x63\x6f\x6e\164\x72\x6f\154\154\x65\162\x73" . DIRECTORY_SEPARATOR . "\155\x79\x61\143\143\157\165\156\x74\56\x70\x68\160";
        $Qh = filter_var($Qh, FILTER_SANITIZE_URL);
        $WN = array("\x73\x73\154" => array("\166\145\x72\151\146\x79\x5f\x70\145\x65\162" => false, "\166\145\162\x69\x66\171\x5f\160\x65\x65\162\x5f\156\141\155\x65" => false));
        $fD = file_get_contents($Qh, false, stream_context_create($WN));
        UtilitiesSAML::auto_upload_metadata($fD, $sP);
        OG:
        x8:
    }
    public static function auto_upload_metadata($fD, $sP)
    {
        require_once JPATH_SITE . DIRECTORY_SEPARATOR . "\x61\144\x6d\x69\x6e\x69\x73\x74\162\x61\164\x6f\x72" . DIRECTORY_SEPARATOR . "\143\157\x6d\160\157\x6e\145\x6e\164\x73" . DIRECTORY_SEPARATOR . "\143\x6f\x6d\137\155\x69\156\151\157\162\x61\x6e\x67\x65\x5f\x73\141\x6d\154" . DIRECTORY_SEPARATOR . "\x68\x65\x6c\x70\145\x72\x73" . DIRECTORY_SEPARATOR . "\115\x65\x74\x61\144\x61\x74\x61\x52\145\x61\144\x65\162\x2e\160\150\160";
        $yA = new DOMDocument();
        $yA->loadXML($fD);
        restore_error_handler();
        $tD = $yA->firstChild;
        if (!empty($tD)) {
            goto TI;
        }
        return;
        goto NM;
        TI:
        $kx = new IDPMetadataReader($yA);
        $cg = $kx->getIdentityProviders();
        if (!empty($cg)) {
            goto H9;
        }
        return;
        H9:
        foreach ($cg as $BI => $rX) {
            $Ju = $rX->getLoginURL("\x48\124\124\x50\x2d\x52\145\x64\x69\x72\x65\143\x74");
            $AY = $rX->getLogoutURL("\x48\x54\x54\x50\x2d\x52\145\x64\x69\162\x65\x63\164");
            $zh = $rX->getEntityID();
            $jL = $rX->getSigningCertificate();
            $Ix = implode("\73", $jL);
            $U4 = JFactory::getDbo();
            $rw = $U4->getQuery(true);
            $Yj = array($U4->quoteName("\151\144\160\137\x65\156\164\x69\164\x79\x5f\x69\x64") . "\40\x3d\40" . $U4->quote(isset($zh) ? $zh : 0), $U4->quoteName("\163\x69\156\147\x6c\145\137\x73\151\x67\156\x6f\156\x5f\163\145\162\166\151\143\145\137\x75\x72\154") . "\40\x3d\40" . $U4->quote(isset($Ju) ? $Ju : 0), $U4->quoteName("\163\x69\156\147\154\145\x5f\x6c\x6f\x67\x6f\x75\164\x5f\x75\x72\154") . "\x20\75\40" . $U4->quote(isset($AY) ? $AY : 0), $U4->quoteName("\142\x69\156\144\x69\x6e\x67") . "\x20\x3d\x20" . $U4->quote("\x48\124\124\x50\x2d\122\x65\x64\x69\162\145\x63\x74"), $U4->quoteName("\143\x65\162\x74\151\x66\x69\x63\141\164\x65") . "\x20\75\40" . $U4->quote(isset($jL) ? $Ix : 0));
            $fi = array($U4->quoteName("\x69\x64") . "\40\75" . $U4->quote($sP));
            $rw->update($U4->quoteName("\43\137\x5f\155\x69\156\x69\157\162\x61\x6e\147\145\x5f\x73\x61\155\x6c\x5f\143\x6f\156\146\x69\x67"))->set($Yj)->where($fi);
            $U4->setQuery($rw);
            $U4->execute();
            goto QB;
            Jg:
        }
        QB:
        return;
        NM:
    }
    public static function getCustomerDetails()
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select("\52");
        $rw->from($U4->quoteName("\43\x5f\137\155\x69\x6e\x69\157\x72\141\156\x67\x65\x5f\163\141\x6d\x6c\x5f\x63\165\x73\164\157\155\x65\x72\x5f\x64\145\164\x61\151\x6c\163"));
        $rw->where($U4->quoteName("\x69\x64") . "\40\75\x20\61");
        $U4->setQuery($rw);
        $CI = $U4->loadAssoc();
        return $CI;
    }
    public static function getCustomerincmk_lk($Xh)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select($Xh);
        $rw->from("\43\137\x5f\x6d\151\x6e\x69\157\x72\141\156\147\x65\137\x73\x61\x6d\154\137\x63\x75\163\x74\157\155\145\x72\x5f\144\145\x74\x61\151\154\x73");
        $rw->where($U4->quoteName("\x69\144") . "\40\x3d\40" . $U4->quote(1));
        $U4->setQuery($rw);
        $hN = $U4->loadColumn();
        return $hN;
    }
    public static function nOfSP()
    {
        $H8 = Mo_Saml_Local_Util::getCustomerDetails();
        $g2 = new Mo_saml_Local_Customer();
        $hh = $H8["\x63\x75\163\x74\x6f\x6d\x65\162\137\153\145\x79"];
        $Ts = $H8["\x61\160\x69\137\153\145\x79"];
        $es = json_decode($g2->ccl($hh, $Ts), true);
        $Mp = isset($es["\156\x6f\117\x66\x53\120"]) ? $es["\x6e\x6f\117\x66\123\x50"] : 0;
        return $Mp;
    }
    public static function idpCnt()
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select(array("\52"));
        $rw->from($U4->quoteName("\x23\137\137\x6d\151\x6e\x69\157\162\141\x6e\147\145\137\x73\141\155\154\x5f\143\x6f\x6e\x66\x69\x67"));
        $U4->setQuery($rw);
        $CI = $U4->loadAssocList();
        $D_ = count($CI);
        return $D_;
    }
    public static function isIDPConfigured()
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select($U4->quoteName(array("\151\144")));
        $rw->from($U4->quoteName("\43\137\137\x6d\x69\156\151\x6f\162\141\x6e\147\x65\137\x73\x61\x6d\154\x5f\x63\157\x6e\x66\x69\x67"));
        $U4->setQuery($rw);
        $CI = $U4->loadObjectList();
        return $CI;
    }
    public static function generateCertificate($gZ, $Lu, $iS, $uF)
    {
        $O0 = JPATH_BASE;
        $BM = substr($O0, 0, strrpos($O0, "\141\x64\x6d\151\156\x69\x73\164\x72\x61\164\x6f\162"));
        $bT = $BM . "\x70\x6c\x75\x67\x69\156\x73" . DIRECTORY_SEPARATOR . "\141\165\164\150\145\156\x74\x69\x63\141\x74\151\x6f\156" . DIRECTORY_SEPARATOR . "\x6d\151\156\x69\157\x72\141\x6e\x67\145\163\141\155\x6c" . DIRECTORY_SEPARATOR . "\x73\x61\x6d\154\62" . DIRECTORY_SEPARATOR . "\143\x65\162\164" . DIRECTORY_SEPARATOR . "\157\160\145\x6e\163\x73\154\x2e\x63\156\146";
        $sv = array("\x63\157\156\x66\x69\147" => $bT, "\x64\151\x67\145\163\164\137\x61\x6c\x67" => "{$Lu}", "\x70\x72\x69\x76\x61\164\145\137\x6b\x65\x79\137\x62\151\x74\x73" => $iS, "\160\162\151\x76\x61\x74\x65\137\153\x65\171\x5f\164\x79\x70\x65" => OPENSSL_KEYTYPE_RSA);
        $AA = openssl_pkey_new($sv);
        $cP = openssl_csr_new($gZ, $AA, $sv);
        $uN = openssl_csr_sign($cP, null, $AA, $uF, $sv, time());
        openssl_x509_export($uN, $ui);
        openssl_pkey_export($AA, $D3, null, $sv);
        openssl_csr_export($cP, $WT);
        XF:
        if (!(($Um = openssl_error_string()) !== false)) {
            goto RE;
        }
        error_log($Um);
        goto XF;
        RE:
        $Gz = array("\x70\165\x62\154\151\x63\137\x6b\145\x79" => $ui, "\160\x72\151\x76\x61\164\145\x5f\x6b\x65\x79" => $D3);
        $ay = UtilitiesSAML::getCustom_CertificatePath("\x43\x75\x73\164\157\x6d\x50\165\x62\154\x69\x63\x43\x65\162\164\151\x66\151\x63\141\x74\x65\x2e\143\x72\164");
        file_put_contents($ay, $Gz["\x70\x75\142\x6c\x69\x63\x5f\153\x65\x79"]);
        $h0 = UtilitiesSAML::getCustom_CertificatePath("\103\165\x73\164\157\x6d\x50\162\151\x76\x61\x74\145\x43\145\x72\164\151\x66\151\x63\x61\x74\x65\56\x6b\x65\x79");
        file_put_contents($h0, $Gz["\160\162\x69\x76\x61\x74\145\x5f\x6b\x65\x79"]);
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $Yj = array($U4->quoteName("\x70\165\x62\x6c\151\x63\137\x63\x65\x72\x74\x69\146\x69\x63\141\x74\145") . "\x20\75\x20" . $U4->quote(isset($Gz["\160\165\142\154\151\143\137\x6b\145\171"]) ? $Gz["\160\165\x62\154\151\x63\137\153\145\x79"] : null), $U4->quoteName("\160\x72\151\x76\x61\164\x65\137\x63\145\x72\164\151\146\151\x63\141\x74\145") . "\40\75\40" . $U4->quote(isset($Gz["\x70\x72\151\x76\141\164\x65\x5f\x6b\145\171"]) ? $Gz["\160\x72\x69\x76\141\x74\145\x5f\153\145\171"] : null));
        $rw->update($U4->quoteName("\x23\x5f\x5f\x6d\x69\156\151\157\x72\141\156\147\x65\137\163\x61\x6d\x6c\x5f\x63\x6f\156\x66\x69\x67"))->set($Yj);
        $U4->setQuery($rw);
        $U4->execute();
    }
    public static function getCustom_CertificatePath($Ce)
    {
        $O0 = JPATH_BASE;
        $BM = substr($O0, 0, strrpos($O0, "\x61\x64\x6d\151\x6e\x69\x73\164\162\x61\164\157\x72"));
        $r_ = $BM . "\x70\154\165\x67\x69\156\163" . DIRECTORY_SEPARATOR . "\141\165\x74\150\145\156\164\151\x63\141\164\151\x6f\156" . DIRECTORY_SEPARATOR . "\x6d\151\156\x69\157\x72\x61\156\147\x65\x73\141\x6d\x6c" . DIRECTORY_SEPARATOR . "\x73\141\x6d\x6c\x32" . DIRECTORY_SEPARATOR . "\x63\145\162\x74" . DIRECTORY_SEPARATOR . $Ce;
        return $r_;
    }
    public static function get_public_private_certificate($vt, $x4)
    {
        if (!isset($vt)) {
            goto II;
        }
        foreach ($vt as $BI => $n2) {
            foreach ($n2 as $Oh => $de) {
                if (!($x4 == "\x70\165\142\x6c\151\143\x5f\x63\145\162\x74\151\146\151\143\141\x74\x65" && $Oh == "\160\165\142\x6c\x69\143\x5f\143\145\x72\x74\x69\146\151\143\x61\x74\145")) {
                    goto qC;
                }
                return $de;
                qC:
                if (!($x4 == "\160\x72\151\166\x61\x74\x65\137\143\145\162\164\x69\x66\x69\x63\x61\164\145" && $Oh == "\x70\x72\x69\x76\141\164\x65\x5f\x63\x65\x72\164\151\146\x69\143\x61\x74\x65")) {
                    goto qI;
                }
                return $de;
                qI:
                Ko:
            }
            qa:
            Rc:
        }
        TU:
        II:
    }
    public static function updateActivationStatusForUser($s7)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $Yj = array($U4->quoteName("\141\143\164\x69\166\x61\x74\151\157\156") . "\40\75\x20\60", $U4->quoteName("\142\x6c\157\x63\x6b") . "\x20\75\40\60");
        $fi = array($U4->quoteName("\x75\163\x65\x72\156\x61\x6d\145") . "\40\75\x20" . $U4->quote($s7));
        $rw->update($U4->quoteName("\x23\137\137\165\x73\145\162\x73"))->set($Yj)->where($fi);
        $U4->setQuery($rw);
        $U4->execute();
    }
    public static function getDomainMapping()
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select(array("\151\144\160\137\145\x6e\x74\x69\x74\171\x5f\x69\x64", "\144\157\x6d\x61\151\156\137\155\141\x70\160\151\156\147"));
        $rw->from($U4->quoteName("\x23\x5f\137\x6d\151\x6e\151\157\162\141\x6e\x67\145\137\x73\141\155\x6c\x5f\x63\157\x6e\146\151\147"));
        $U4->setQuery($rw);
        $fC = $U4->loadAssocList();
        return $fC;
    }
    public static function getConifById($sP)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select("\52");
        $rw->from($U4->quoteName("\43\137\x5f\155\151\156\x69\157\162\141\x6e\147\145\137\163\141\x6d\154\x5f\143\x6f\156\146\151\147"));
        $rw->where($U4->quoteName("\x69\144") . "\x20\75\x20" . $U4->quote($sP));
        $U4->setQuery($rw);
        $vt = $U4->loadAssoc();
        return $vt;
    }
    public static function isCustomerRegistered()
    {
        $H8 = Mo_Saml_Local_Util::getCustomerDetails();
        $Oo = $H8["\x73\164\141\164\x75\x73"];
        if (!Mo_Saml_Local_Util::is_customer_registered() || Mo_Saml_Local_Util::check($Oo) != "\164\x72\x75\x65") {
            goto TA;
        }
        return $uR = '';
        goto Fb;
        TA:
        return $uR = "\144\151\163\141\x62\x6c\145\x64";
        Fb:
    }
    public static function createUpdateUrl($UQ, $UJ, $di, $Ts, $p6, $f0, $nz)
    {
        $Ik = "\61\x31\x31\x31\61\x31\61\x31\x31\x31\61\61\x31\61\x31\x31" . $UQ;
        $Qh = $f0 . "\57\155\157\141\x73\57\x61\160\x69\57\160\x6c\165\147\151\156\57\144\x72\x75\x70\141\x6c\112\x6f\x6f\155\x6c\x61\125\160\x64\x61\x74\145\57" . $p6 . "\57" . $UJ . "\x2f" . $di . "\57";
        $Qw = openssl_cipher_iv_length($K0 = "\x41\x45\x53\x2d\61\x32\70\x2d\103\x42\x43");
        $f_ = openssl_random_pseudo_bytes($Qw);
        $Uw = openssl_encrypt($Ik, $K0, $Ts, $Eh = OPENSSL_RAW_DATA, $f_);
        return $Qh . str_replace(["\53", "\57", "\75"], ["\55", "\x5f", ''], base64_encode($Uw)) . "\x2f" . $nz . "\x2f\x6a\x6f\157\x6d\x6c\x61";
    }
    public static function updateUpgardeUrlInDb($Lg)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $iB[] = $U4->quoteName("\154\157\x63\141\164\x69\157\x6e") . "\x20\75\x20" . $U4->quote($Lg);
        $zD = "\x4d\151\x6e\151\157\x72\x61\156\147\x65\123\x61\155\x6c\123\123\x4f";
        $rw->update($U4->quoteName("\43\x5f\x5f\165\x70\x64\x61\x74\145\x5f\163\151\x74\x65\163"))->set($iB)->where($U4->quoteName("\x6e\x61\155\145") . "\x20\75\x20\x27" . $zD . "\x27");
        $U4->setQuery($rw);
        $U4->execute();
    }
    public static function getHostname()
    {
        return "\150\x74\164\160\x73\x3a\57\57\154\157\147\151\156\56\x78\x65\x63\x75\162\x69\146\x79\x2e\x63\x6f\155";
    }
    public static function createAndUpdateUpgardeUrl()
    {
        if (!(in_array("\x6f\160\x65\156\x73\163\154", get_loaded_extensions()) === FALSE)) {
            goto Nk;
        }
        return;
        Nk:
        $I3 = self::getHostname();
        $H8 = self::getCustomerDetails();
        if (!self::doWeHaveCorrectUpgardeUrl()) {
            goto zO;
        }
        return;
        zO:
        $Lg = self::createUpdateUrl(self::decrypt($H8["\x73\x6d\x6c\x5f\x6c\x6b"], $H8["\143\x75\x73\164\157\155\145\x72\137\164\157\x6b\x65\156"]), UtilitiesSAML::getLicensePlanName(), "\112\x4f\x4f\x4d\114\x41\x5f\123\101\x4d\114\137\x53\120\137\105\116\x54\105\x52\120\122\x49\123\105\x5f\120\x4c\125\107\111\x4e", $H8["\x61\160\151\x5f\x6b\145\x79"], $H8["\143\165\163\164\157\x6d\x65\x72\x5f\153\145\x79"], $I3, "\x63\x6f\x6d\x5f\155\x69\156\151\x6f\x72\141\156\x67\x65\x5f\x73\141\x6d\154");
        self::updateUpgardeUrlInDb($Lg);
    }
    public static function doWeHaveCorrectUpgardeUrl()
    {
        $zD = "\115\x69\156\151\x6f\162\141\156\x67\145\123\141\x6d\154\123\x53\117";
        $ML = "\43\x5f\x5f\x75\160\x64\x61\164\x65\x5f\163\151\164\x65\x73";
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select("\x2a");
        $rw->from($U4->quoteName($ML));
        $rw->where($U4->quoteName("\156\x61\x6d\x65") . "\40\75\40\47" . $zD . "\x27");
        $U4->setQuery($rw);
        $YO = $U4->loadAssocList();
        foreach ($YO as $BI => $n2) {
            if (!(stristr($n2["\x6c\x6f\x63\x61\x74\151\x6f\156"], "\144\x72\x75\160\x61\x6c\x4a\157\x6f\155\x6c\x61\125\x70\x64\141\164\x65") !== FALSE)) {
                goto xu;
            }
            return TRUE;
            xu:
            FD:
        }
        Ye:
        return FALSE;
    }
    public static function decrypt($K0, $BI)
    {
        $PD = rtrim(openssl_decrypt(base64_decode($K0), "\x61\145\x73\55\61\62\70\55\145\143\142", $BI, OPENSSL_RAW_DATA), "\x0");
        return trim($PD, "\x0\56\x2e\32");
    }
    public static function GetPluginVersion()
    {
        $U4 = JFactory::getDbo();
        $ut = $U4->getQuery(true)->select("\155\141\x6e\x69\x66\x65\x73\x74\137\x63\141\x63\150\x65")->from($U4->quoteName("\43\x5f\137\145\x78\164\x65\x6e\163\151\x6f\156\163"))->where($U4->quoteName("\x65\x6c\145\x6d\x65\x6e\x74") . "\x20\75\x20" . $U4->quote("\143\x6f\155\x5f\x6d\151\x6e\x69\157\x72\141\x6e\147\x65\137\163\141\x6d\154"));
        $U4->setQuery($ut);
        $NR = json_decode($U4->loadResult());
        return $NR->version;
    }
    public static function getPluginConfigurations($sP)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select("\x2a");
        $rw->from($U4->quoteName("\43\137\x5f\x6d\151\x6e\151\x6f\x72\141\156\x67\x65\x5f\x73\141\x6d\x6c\137\x63\x6f\x6e\x66\x69\147"));
        $fi = array($U4->quoteName("\151\144") . "\40\75\40" . $U4->quote($sP));
        $rw->where($fi);
        $U4->setQuery($rw);
        $fC = $U4->loadAssocList();
        return $fC;
    }
    public static function fetchDetails($P2, $ur, $kS, $Vw = TRUE)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select($P2);
        $rw->from($U4->quoteName($ur));
        if (!($Vw !== TRUE)) {
            goto KO;
        }
        foreach ($Vw as $BI => $n2) {
            $rw->where($U4->quoteName($BI) . "\x20\x3d\x20" . $U4->quote($n2));
            pM:
        }
        WS:
        KO:
        $U4->setQuery($rw);
        if ($kS == "\x6c\x6f\141\x64\103\157\x6c\x75\155\156") {
            goto sN;
        }
        if ($kS == "\x6c\157\x61\144\117\x62\152\145\143\164\114\x69\x73\x74") {
            goto Qi;
        }
        if ($kS == "\154\x6f\x61\x64\117\x62\x6a\x65\x63\x74") {
            goto qd;
        }
        if ($kS == "\x6c\157\141\x64\x52\x65\x73\165\x6c\164") {
            goto Eu;
        }
        if ($kS == "\x6c\x6f\141\144\122\157\167") {
            goto tp;
        }
        if ($kS == "\x6c\157\141\x64\122\x6f\x77\x4c\151\163\x74") {
            goto O1;
        }
        if ($kS == "\154\157\x61\144\101\x73\x73\x6f\x63\114\151\x73\164") {
            goto tG;
        }
        return $U4->loadAssoc();
        goto sp;
        tG:
        return $U4->loadAssocList();
        sp:
        goto o7;
        O1:
        return $U4->loadRowList();
        o7:
        goto y_;
        tp:
        return $U4->loadRow();
        y_:
        goto sS;
        Eu:
        return $U4->loadResult();
        sS:
        goto Dk;
        qd:
        return $U4->loadObject();
        Dk:
        goto A7;
        Qi:
        return $U4->loadObjectList();
        A7:
        goto N7;
        sN:
        return $U4->loadColumn();
        N7:
    }
    public static function isLoginReportAddonEnable()
    {
        $My = array("\155\x69\x6e\x69\x6f\x72\x61\156\x67\x65\154\x6f\x67\x69\156\162\145\160\157\x72\164");
        foreach ($My as $BI) {
            return self::checkExtensionEnabled($BI);
            xo:
        }
        RA:
    }
    public static function checkExtensionEnabled($Ec)
    {
        $U4 = JFactory::getDbo();
        $rw = $U4->getQuery(true);
        $rw->select("\145\156\141\x62\154\145\x64");
        $rw->from("\43\137\x5f\145\170\164\145\156\x73\x69\157\156\163");
        $rw->where($U4->quoteName("\x65\x6c\145\155\x65\156\x74") . "\x20\x3d\x20" . $U4->quote($Ec));
        $rw->where($U4->quoteName("\164\x79\x70\x65") . "\x20\75\40" . $U4->quote("\x70\x6c\165\x67\151\x6e"));
        $U4->setQuery($rw);
        return $U4->loadAssoc();
    }
    public static function mo_log($r1)
    {
        $eP = $_SERVER["\x44\117\103\125\x4d\105\x4e\124\x5f\x52\117\117\x54"] . "\57\154\157\x67\x2f\154\x6f\147\x2e\x6c\x6f\147";
        $rY = $_SERVER["\104\117\x43\125\x4d\105\116\x54\137\x52\117\117\x54"] . "\57\x6c\157\x67";
        if (file_exists($rY)) {
            goto nb;
        }
        mkdir($rY, 0777, true);
        nb:
        file_put_contents($eP, $r1 . "\12", FILE_APPEND);
    }
    public static function check_table_present($t_)
    {
        $MQ = 0;
        $Tr = JFactory::getDbo()->getTableList();
        foreach ($Tr as $ML) {
            if (!(strpos($ML, $t_) !== FALSE)) {
                goto yF;
            }
            $MQ = 1;
            yF:
            Sq:
        }
        s2:
        return $MQ;
    }
    public static function _cuc()
    {
        $xZ = self::loadCustomerDetails("\x23\x5f\137\x6d\151\x6e\x69\x6f\x72\x61\156\147\145\x5f\x73\141\155\154\x5f\x63\165\x73\x74\x6f\x6d\145\x72\137\x64\145\164\141\x69\x6c\x73");
        $Dv = strtotime($xZ["\154\x69\143\145\156\163\x65\x45\170\160\151\162\x79"]);
        $Dv = $Dv === FALSE || $Dv <= -62169987208 ? "\55" : date("\106\40\x6a\x2c\40\131\54\x20\147\x3a\151\40\x61", $Dv);
        $Xt = self::licenseExpiryDay();
        $sv = JFactory::getConfig();
        $S1 = $sv->get("\x73\151\x74\145\x6e\141\x6d\x65");
        $z3 = "\112\117\x4f\x4d\x4c\x41\40\123\101\x4d\114\x20\123\x50\x20\105\x6e\164\x65\x72\160\162\151\163\145";
        $QH = "\x4c\x69\143\x65\156\163\x65\40\x45\x78\160\x69\162\x65\x20\x6f\146\x20\x4a\x6f\x6f\155\x6c\x61\x20\x53\101\115\114\40\123\151\x6e\147\154\x65\40\123\x69\147\156\x2d\x4f\x6e\x20\174" . $S1;
        $Zp = "\110\x65\x6c\x6c\x6f\x2c\x3c\x62\162\40\57\x3e\x3c\x62\162\40\57\x3e\131\x6f\x75\162\40\154\151\x63\x65\x6e\163\145\40\x66\x6f\162\40\x3c\x62\76" . $z3 . "\x3c\57\142\x3e\40\x70\x6c\x61\156\x20\x69\163\x20\147\x6f\151\156\x67\40\164\157\40\x65\x78\160\151\x72\x65\40\157\156\x20" . $Dv . "\x20\146\x6f\162\x20\x79\157\165\x72\x20\167\145\x62\163\x69\x74\145\x3a\x20\74\142\76" . $S1 . "\x3c\x2f\142\x3e\56\74\142\x72\x20\x2f\x3e\x20\74\x62\162\x20\x2f\76\x20\x50\154\145\x61\163\x65\x20\162\x65\x6e\x65\167\40\171\x6f\165\x72\40\154\x69\x63\x65\x6e\x73\145\40\141\163\x20\163\157\x6f\x6e\x20\x61\x73\40\x70\x6f\163\163\x69\x62\154\x65\40\x74\157\40\162\x65\x63\x65\x69\x76\x65\x20\160\x6c\165\147\x69\x6e\40\x75\160\x64\x61\x74\145\163\40\x70\162\157\x76\151\x64\151\156\x67\x20\163\x65\143\x75\162\x69\x74\171\x20\160\141\x74\x63\x68\x65\163\x2c\x20\x62\165\147\40\x66\151\x78\x65\163\54\x20\156\x65\167\x20\146\145\141\x74\165\x72\145\x73\x2c\x20\x6f\162\40\x65\166\x65\156\40\x63\x6f\x6d\160\141\x74\x69\142\x69\x6c\151\x74\171\40\x61\144\152\165\x73\x74\155\145\156\x74\163\x2e\40\x49\146\x20\x79\x6f\165\40\167\141\156\x74\40\x74\157\x20\162\145\156\145\x77\x20\171\x6f\x75\162\40\154\151\x63\x65\156\163\x65\x20\160\x6c\x65\141\163\x65\40\162\145\141\143\x68\40\x6f\x75\x74\x20\x74\x6f\x20\x75\x73\x20\141\164\40\x3c\x62\76\x6a\157\x6f\x6d\154\141\x73\165\x70\160\x6f\162\x74\x40\170\145\x63\165\162\x69\146\x79\x2e\x63\157\x6d\74\x2f\x62\76\74\142\x72\40\x2f\x3e\74\142\162\x20\x2f\x3e\124\150\141\x6e\x6b\163\54\x3c\x62\x72\40\x2f\76\x6d\151\156\151\x4f\x72\x61\156\147\x65\x20\x54\145\141\x6d";
        $nI = "\x48\x65\x6c\x6c\x6f\54\74\x62\162\x20\x2f\76\74\x62\x72\40\57\x3e\131\x6f\x75\x72\40\x6c\151\143\145\x6e\163\x65\40\146\157\162\40\74\x62\76" . $z3 . "\74\x2f\x62\x3e\x20\160\154\x61\156\x20\x68\141\x73\40\145\x78\x70\x69\162\145\x64\x20\x6f\x6e\x20" . $Dv . "\40\146\x6f\x72\x20\171\x6f\x75\162\40\x77\x65\x62\163\151\x74\x65\72\40\74\x62\x3e" . $S1 . "\x3c\x2f\x62\x3e\56\x3c\142\x72\x20\57\76\40\x3c\x62\162\40\x2f\x3e\40\x50\154\x65\x61\x73\145\40\x72\x65\x6e\x65\167\x20\171\157\x75\162\x20\154\151\143\x65\156\163\x65\x20\141\x73\40\163\x6f\157\156\x20\141\163\x20\160\x6f\163\163\151\x62\x6c\x65\40\164\x6f\x20\162\x65\143\x65\151\166\145\x20\160\x6c\165\x67\151\156\x20\165\160\144\x61\164\x65\163\x20\160\x72\157\x76\151\x64\151\x6e\147\x20\163\145\x63\x75\x72\151\x74\x79\40\x70\x61\164\x63\x68\145\163\x2c\40\142\x75\x67\40\x66\x69\x78\x65\163\54\40\156\145\x77\x20\146\x65\x61\x74\x75\x72\145\163\x2c\40\157\162\40\145\x76\145\156\x20\143\x6f\155\160\x61\164\151\x62\151\154\151\x74\x79\x20\x61\144\152\165\x73\x74\x6d\x65\x6e\164\x73\56\40\x49\146\x20\x79\x6f\165\x20\167\x61\x6e\x74\x20\164\157\40\162\145\x6e\x65\167\x20\x79\157\x75\162\x20\x6c\x69\x63\145\x6e\163\145\x20\160\154\x65\141\x73\145\x20\162\145\141\143\x68\40\x6f\x75\x74\x20\164\157\40\165\x73\x20\x61\164\x20\x3c\142\x3e\152\157\157\155\x6c\x61\163\165\x70\160\157\x72\164\x40\170\145\143\165\162\151\x66\171\56\143\x6f\155\x3c\57\x62\x3e\74\x62\162\40\x2f\x3e\74\x62\x72\40\x2f\76\x54\150\x61\156\153\x73\54\74\142\x72\40\x2f\76\155\151\x6e\151\117\x72\x61\x6e\147\x65\x20\x54\145\141\x6d";
        $Y3 = "\x23\137\137\155\151\x6e\151\x6f\x72\141\x6e\147\x65\137\163\x61\155\x6c\137\x63\165\x73\164\x6f\155\145\x72\x5f\144\x65\x74\141\x69\x6c\163";
        $g2 = new Mo_saml_Local_Customer();
        if ($Xt <= 15 && $Xt > 5 && !$xZ["\x6d\151\156\151\x6f\162\141\x6e\147\x65\137\x66\x69\146\164\x65\145\x6e\137\x64\x61\x79\x73\x5f\x62\x65\x66\x6f\x72\145\x5f\x6c\x65\x78\160"]) {
            goto sG;
        }
        if ($Xt <= 5 && $Xt > 0 && !$xZ["\155\x69\x6e\151\157\x72\141\156\x67\x65\x5f\x66\x69\166\x65\x5f\144\141\x79\x73\137\x62\145\146\x6f\162\145\137\154\x65\x78\x70"]) {
            goto cV;
        }
        if ($Xt <= 0 && $Xt > -5 && !$xZ["\155\151\x6e\151\x6f\162\x61\x6e\x67\x65\137\141\146\x74\145\x72\x5f\x6c\x65\170\x70"]) {
            goto YQ;
        }
        if (!($Xt == -5 && !$xZ["\x6d\x69\x6e\x69\157\x72\x61\156\147\x65\137\141\146\x74\145\162\137\146\151\x76\x65\137\144\x61\171\163\x5f\x6c\x65\170\x70"])) {
            goto mr;
        }
        if (self::licensevalidity($Dv)) {
            goto gW;
        }
        self::_update_lid("\155\x69\x6e\x69\x6f\162\141\156\x67\x65\137\141\x66\x74\145\162\x5f\146\151\x76\x65\137\144\x61\x79\163\137\154\x65\x78\x70");
        json_decode($g2->send_email_alert($QH, $nI), true);
        gW:
        mr:
        goto yP;
        YQ:
        if (self::licensevalidity($Dv)) {
            goto Y3;
        }
        self::_update_lid("\155\151\x6e\x69\157\x72\x61\x6e\147\x65\137\141\146\x74\x65\x72\x5f\x6c\145\170\x70");
        json_decode($g2->send_email_alert($QH, $nI), true);
        Y3:
        yP:
        goto Tq;
        cV:
        if (self::licensevalidity($Dv)) {
            goto SZ;
        }
        self::_update_lid("\x6d\x69\156\151\157\x72\x61\x6e\x67\x65\137\146\x69\x76\145\x5f\x64\x61\171\x73\137\142\x65\146\157\x72\145\x5f\x6c\145\170\x70");
        json_decode($g2->send_email_alert($QH, $Zp), true);
        SZ:
        Tq:
        goto Iy;
        sG:
        if (self::licensevalidity($Dv)) {
            goto dl;
        }
        self::_update_lid("\155\151\156\151\157\x72\x61\x6e\147\x65\137\146\x69\x66\x74\x65\x65\156\137\144\x61\x79\x73\x5f\x62\x65\x66\157\162\145\137\154\x65\170\160");
        json_decode($g2->send_email_alert($QH, $Zp), true);
        dl:
        Iy:
    }
    public static function licenseExpiryDay()
    {
        $xZ = self::getExpiryDate();
        $Tz = intval((strtotime($xZ["\x6c\x69\x63\x65\156\x73\145\105\x78\x70\151\x72\171"]) - time()) / (60 * 60 * 24));
        return $Tz;
    }
    public static function licensevalidity($gi)
    {
        $H8 = self::loadCustomerDetails("\43\137\x5f\155\151\156\151\157\x72\x61\x6e\147\x65\137\163\x61\x6d\x6c\x5f\x63\x75\x73\164\x6f\155\145\x72\137\144\145\x74\141\151\154\163");
        $hh = isset($H8["\143\165\163\x74\x6f\x6d\x65\x72\137\x6b\x65\x79"]) ? $H8["\x63\x75\163\x74\157\x6d\x65\x72\x5f\153\x65\171"] : '';
        $Ts = isset($H8["\141\x70\151\137\153\x65\x79"]) ? $H8["\x61\x70\151\137\x6b\145\x79"] : '';
        $g2 = new Mo_saml_Local_Customer();
        $st = json_decode($g2->ccl($hh, $Ts), true);
        $SU = $st["\x6c\151\x63\145\x6e\x73\x65\105\170\x70\151\162\x79"];
        if ($SU > $gi) {
            goto OZ;
        }
        return FALSE;
        goto RX;
        OZ:
        $Y3 = "\43\x5f\x5f\155\x69\156\x69\157\162\x61\x6e\147\x65\x5f\163\x61\x6d\154\137\143\x75\163\164\157\155\145\162\137\x64\145\164\x61\x69\x6c\163";
        $RX = array("\x6c\x69\x63\x65\x6e\x73\145\x45\x78\x70\151\162\x79" => $SU);
        self::__genDBUpdate($Y3, $RX);
        return TRUE;
        RX:
    }
    public static function _update_lid($de)
    {
        $U4 = jFactory::getDbo();
        $rw = $U4->getQuery(true);
        $Yj = array($U4->quoteName($de) . "\x20\x3d\x20" . $U4->quote(1));
        $fi = array($U4->quoteName("\x69\144") . "\40\x3d\x20" . $U4->quote(1));
        $rw->update($U4->quoteName("\x23\x5f\x5f\x6d\x69\x6e\x69\x6f\x72\141\156\147\145\137\x73\x61\155\x6c\x5f\x63\x75\x73\164\x6f\x6d\145\x72\x5f\144\x65\x74\141\151\x6c\x73"))->set($Yj)->where($fi);
        $U4->setQuery($rw);
        $U4->execute();
    }
}
