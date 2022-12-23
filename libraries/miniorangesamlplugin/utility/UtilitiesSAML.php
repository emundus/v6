<?php


include "\170\155\x6c\x73\145\x63\154\151\x62\163\x53\x41\115\114\x2e\160\150\160";
require_once JPATH_SITE . DIRECTORY_SEPARATOR . "\141\x64\155\151\x6e\151\x73\x74\162\x61\164\157\162" . DIRECTORY_SEPARATOR . "\143\x6f\x6d\160\x6f\x6e\x65\156\164\x73" . DIRECTORY_SEPARATOR . "\x63\x6f\155\x5f\x6d\151\156\151\157\x72\x61\x6e\x67\145\137\x73\141\x6d\154" . DIRECTORY_SEPARATOR . "\x68\x65\154\160\x65\x72\x73" . DIRECTORY_SEPARATOR . "\x6d\x6f\55\163\141\x6d\x6c\55\143\165\x73\x74\x6f\155\145\x72\x2d\163\x65\164\165\160\x2e\160\x68\x70";
class UtilitiesSAML
{
    public static function saveUserInDB($post, $KX)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $JZ = date("\131\55\x6d\55\x64\40\110\72\151\72\x73");
        $GA = array("\x6e\x61\x6d\x65", "\x75\x73\145\162\x6e\141\155\x65", "\x65\155\141\x69\154", "\160\x61\163\x73\167\157\x72\x64", "\162\145\147\x69\163\164\145\162\104\x61\x74\x65", "\160\x61\162\x61\155\x73");
        $y0 = array($jt->quote($post["\156\x61\155\145"]), $jt->quote($post["\165\163\145\162\156\141\x6d\145"]), $jt->quote($post["\x65\155\x61\x69\x6c"]), $jt->quote($post["\x70\141\163\163\x77\x6f\162\144"]), $jt->quote($JZ), $jt->quote(''));
        $bH->insert($jt->quoteName("\43\137\x5f\165\163\x65\162\163"))->columns($jt->quoteName($GA))->values(implode("\54", $y0));
        $jt->setQuery($bH);
        $jt->execute();
    }
    public static function updateUserGroup($z_, $C1)
    {
        $z_ = (int) $z_;
        $C1 = (int) $C1;
        $user = JFactory::getUser($z_);
        if (\in_array($C1, $user->groups)) {
            goto OO;
        }
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $GA = array("\x75\x73\x65\x72\137\151\x64", "\147\162\157\165\160\x5f\x69\x64");
        $y0 = array($jt->quote($z_), $jt->quote($C1));
        $bH->insert($jt->quoteName("\x23\137\137\x75\163\145\x72\137\x75\163\145\x72\x67\x72\157\165\x70\137\155\x61\160"))->columns($jt->quoteName($GA))->values(implode("\54", $y0));
        $jt->setQuery($bH);
        $jt->execute();
        OO:
        $JG = JFactory::getUser($z_);
        $JG->groups = $user->groups;
        if (!JFactory::getSession()->getId()) {
            goto Z_;
        }
        $JG = JFactory::getUser();
        if (!($JG->id == $z_)) {
            goto lh;
        }
        $JG->groups = $user->groups;
        lh:
        Z_:
        return true;
    }
    public static function addUser($hD)
    {
        $ZP = "\43\137\137\x6d\x69\156\151\x6f\162\x61\156\x67\145\x5f\x73\x61\x6d\154\137\143\165\163\164\x6f\x6d\145\162\137\x64\x65\x74\x61\x69\x6c\163";
        $k0 = array("\165\x73\x72\x6c\x6d\164" => $hD + 1);
        self::__genDBUpdate($ZP, $k0);
    }
    public static function showErrorMessage()
    {
        echo "\40\40\x20\x20\x20\40\x20\40\x3c\x64\151\x76\x20\163\164\x79\x6c\145\75\x22\146\157\156\x74\x2d\146\141\155\x69\154\x79\x3a\x43\141\154\x69\142\162\151\x3b\160\141\144\144\x69\156\147\x3a\x30\x20\x33\45\x3b\x22\76\xa\40\40\x20\x20\x20\40\x20\40\40\x20\40\40\x3c\x64\151\166\40\x73\164\171\154\145\x3d\x22\x63\157\x6c\157\162\x3a\40\x23\141\x39\64\x34\64\62\73\142\141\143\x6b\x67\162\x6f\165\156\144\x2d\143\157\154\157\162\72\x20\x23\146\62\144\145\x64\145\73\160\141\144\144\x69\x6e\x67\x3a\40\x31\x35\x70\170\x3b\155\141\x72\147\x69\156\55\x62\x6f\x74\x74\157\x6d\x3a\x20\x32\60\x70\170\73\164\x65\170\x74\x2d\x61\x6c\151\147\156\72\x63\x65\x6e\164\145\x72\x3b\x62\157\x72\x64\x65\162\x3a\x31\x70\170\40\x73\157\154\x69\144\40\x23\105\66\x42\63\x42\x32\73\x66\157\x6e\x74\55\163\x69\x7a\145\x3a\61\70\x70\x74\x3b\x22\76\40\105\122\x52\x4f\x52\74\57\144\x69\166\x3e\12\x20\40\40\40\x20\x20\x20\x20\x20\x20\x20\40\74\x64\x69\166\40\x73\164\x79\154\145\x3d\x22\x63\157\154\x6f\x72\72\40\43\141\71\x34\64\64\62\73\146\x6f\156\x74\55\x73\x69\x7a\145\x3a\61\x34\160\164\x3b\x20\x6d\x61\x72\147\x69\156\55\x62\157\x74\x74\157\155\x3a\x32\x30\160\x78\x3b\x22\x3e\12\40\x20\40\40\40\x20\x20\40\40\40\x20\x20\40\x20\40\x20\74\x70\76\x3c\x73\164\162\157\x6e\x67\76\x45\x72\162\157\162\x3a\x20\74\57\163\x74\x72\157\x6e\x67\76\x20\x50\154\x65\x61\x73\145\40\x63\157\x6e\164\x61\143\164\x20\164\x6f\40\x79\157\165\162\40\x41\144\155\x6e\x69\163\164\x72\x61\x74\x6f\162\x2e\74\x2f\160\x3e\xa\40\40\x20\40\40\40\x20\40\40\40\40\40\40\x20\x20\40\x3c\x70\76\74\x73\164\x72\x6f\x6e\147\x3e\122\145\x66\145\x72\145\x6e\x63\x65\x20\x4e\157\72\40\74\x2f\x73\164\162\157\156\x67\x3e\112\117\x31\60\x31\12\40\40\40\x20\40\40\40\x20\x20\x20\40\x20\74\x2f\144\x69\x76\76\xa\40\x20\x20\40\40\40\x20\x20\40\40\40\40\74\x66\x6f\x72\x6d\x20\x61\x63\x74\151\157\x6e\75\42";
        echo JURI::root();
        echo "\42\76\xa\x20\40\x20\x20\x20\x20\40\40\x20\x20\x20\x20\x20\40\x20\40\74\144\x69\x76\40\163\x74\x79\154\x65\75\42\155\x61\x72\147\151\x6e\x3a\63\45\73\x64\151\x73\160\154\141\171\x3a\x62\154\157\143\x6b\73\x74\x65\x78\x74\55\x61\154\151\147\156\72\x63\x65\x6e\164\145\162\73\x22\x3e\12\x20\40\x20\x20\x20\x20\40\40\x20\40\x20\40\40\40\40\x20\40\40\40\x20\x3c\x69\156\160\x75\x74\x20\163\x74\x79\x6c\145\75\x22\160\141\x64\144\x69\156\x67\x3a\61\45\x3b\167\151\x64\164\150\72\61\x30\60\160\x78\73\x62\x61\x63\153\147\162\157\165\x6e\x64\x3a\40\x23\60\60\x39\61\x43\x44\x20\156\157\x6e\x65\40\162\x65\160\x65\141\x74\x20\x73\143\x72\x6f\x6c\154\40\60\45\40\60\x25\x3b\143\165\x72\163\157\x72\x3a\x20\160\x6f\151\156\164\x65\162\x3b\x66\x6f\156\x74\x2d\x73\151\x7a\x65\72\x31\65\160\170\73\x62\x6f\162\x64\145\x72\x2d\x77\151\144\164\150\72\x20\61\x70\x78\73\x62\157\x72\144\145\162\x2d\163\164\171\x6c\x65\x3a\x20\x73\x6f\154\151\144\73\x62\x6f\x72\x64\x65\x72\x2d\x72\141\x64\151\165\x73\x3a\40\x33\160\170\x3b\x77\150\151\164\x65\55\x73\160\141\143\145\x3a\x20\156\x6f\x77\162\141\x70\73\x62\x6f\170\55\x73\151\172\x69\156\x67\x3a\x20\142\157\x72\144\x65\162\55\142\157\x78\73\x62\x6f\x72\x64\145\x72\x2d\x63\157\154\x6f\162\x3a\x20\43\60\60\67\x33\x41\101\x3b\x62\157\x78\x2d\163\x68\x61\144\157\x77\x3a\40\60\x70\x78\x20\61\x70\x78\x20\x30\x70\170\40\162\147\x62\x61\50\61\62\60\x2c\x20\62\x30\x30\x2c\x20\x32\63\60\x2c\x20\x30\56\x36\x29\x20\x69\156\x73\145\x74\73\x63\x6f\x6c\x6f\162\72\x20\x23\106\x46\x46\73\42\40\164\171\160\x65\x3d\42\163\165\142\155\x69\x74\x22\x20\166\141\154\x75\145\x3d\x22\x44\x6f\x6e\x65\42\40\157\156\103\154\151\143\153\x3d\42\x73\x65\x6c\146\56\143\154\157\x73\145\x28\51\x3b\x22\x3e\xa\40\x20\40\x20\x20\40\x20\x20\x20\40\40\x20\x20\40\x20\x20\74\x2f\144\x69\166\76\12\40\40\40\40\40\40\x20\x20\40\40\x20\x20\x3c\x2f\x66\157\x72\155\x3e\12\x20\40\40\x20\40\x20\x20\40\74\57\144\x69\x76\76\12\40\40\x20\x20\x20\x20\x20\40";
        exit;
    }
    public static function getUserGroupID($v1)
    {
        foreach ($v1 as $fP) {
            $Xy = $fP;
            mF:
        }
        eY:
        return $Xy;
    }
    public static function loadUserGroups()
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->select("\x2a");
        $bH->from($jt->quoteName("\43\x5f\x5f\165\163\x65\x72\147\162\x6f\165\160\163"));
        $jt->setQuery($bH);
        return $jt->loadRowList();
    }
    public static function CheckUserGroup($Xy)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->select("\164\x69\x74\x6c\145");
        $bH->from($jt->quoteName("\x23\x5f\x5f\x75\163\x65\162\x67\x72\x6f\x75\x70\163"));
        $bH->where($jt->quoteName("\151\144") . "\x20\x3d\40" . $jt->quote($Xy));
        $jt->setQuery($bH);
        return $jt->loadResult();
    }
    public static function getLoginUserGroupId($xd)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->select("\147\x72\157\165\160\137\x69\144");
        $bH->from($jt->quoteName("\43\137\137\x75\x73\x65\162\x5f\165\x73\x65\162\147\x72\x6f\x75\x70\137\x6d\x61\160"));
        $bH->where($jt->quoteName("\165\163\145\x72\x5f\x69\144") . "\x20\75\40" . $jt->quote($xd));
        $jt->setQuery($bH);
        return $jt->loadColumn();
    }
    public static function isSuperUser()
    {
        $user = JFactory::getUser();
        return $user->get("\x69\x73\122\157\x6f\164");
    }
    public static function getJoomlaCmsVersion()
    {
        $Jd = new JVersion();
        return $Jd->getShortVersion();
    }
    public static function renewalMessage($R5, $pS, $XD)
    {
        $on = "\40";
        $jI = "\40";
        if (!($XD == "\x70\x6c\x75\147\x69\156")) {
            goto xS;
        }
        $on = "\167\x69\144\x74\x68\x3a\x38\60\x25\x3b\x66\x6c\157\x61\x74\72\x6c\x65\x66\x74\x3b\x6d\x61\x72\147\151\156\55\x6c\x65\x66\164\x3a\61\60\45";
        xS:
        if (!($R5["\x4c\x69\143\x65\156\163\x65\105\x78\160\151\x72\145\144"] == 1)) {
            goto hM;
        }
        $jI = "\74\144\x69\x76\40\143\x6c\x61\x73\163\x3d\x22\x62\141\x63\x6b\147\x72\x6f\165\156\144\x5f\x63\157\x6c\x6f\162\x5f\165\160\144\141\164\x65\x5f\x6d\x65\x73\163\141\x67\145\x20\x6d\163\x2d\x61\x75\x74\x6f\x22\x20\x73\x74\x79\x6c\145\x3d\42" . $on . "\x64\151\163\160\x6c\x61\x79\72\142\x6c\157\143\153\x3b\x63\x6f\x6c\x6f\x72\72\162\x65\x64\73\x62\141\x63\x6b\147\x72\157\165\156\x64\55\x63\157\154\x6f\162\72\x72\x67\142\x61\50\62\65\61\x2c\x20\62\x33\x32\x2c\x20\60\x2c\40\x30\x2e\61\65\51\73\x20\x62\x6f\162\144\145\162\x3a\163\157\154\151\144\40\x31\160\x78\40\x72\147\x62\x61\50\x32\x35\x35\54\x20\x30\54\40\71\x2c\x20\x30\x2e\63\66\51\73\160\141\144\144\x69\156\147\x3a\40\61\60\x70\170\x20\x3b\155\x61\162\147\x69\x6e\72\x20\x31\x30\x70\x78\x20\73\42\76\xa\40\40\40\x20\40\x20\x20\x20\x20\x20\40\40\131\157\x75\162\40\x6d\151\x6e\151\x4f\162\141\x6e\147\x65\40\x4a\x6f\x6f\x6d\154\x61\x20\123\101\115\114\40\x53\x50\x20\160\154\x75\x67\151\156\40\154\x69\143\x65\x6e\163\x65\x20\150\141\x73\40\x65\x78\x70\x69\162\x65\x64\40\157\x6e\40\74\163\164\x72\x6f\156\147\76\x20" . $pS . "\74\57\163\x74\162\157\x6e\147\76\x2e\40\x54\x68\151\163\x20\150\x61\x73\x20\x70\x72\x65\x76\x65\156\164\145\144\40\171\157\165\x20\146\162\157\x6d\x20\162\x65\x63\145\151\x76\151\156\147\40\x61\x6e\171\x20\160\154\x75\x67\151\156\x20\x75\160\x64\x61\x74\145\163\40\143\x6f\156\164\x61\x69\156\x69\156\x67\x20\163\145\143\x75\x72\x69\x74\171\40\x70\x61\x74\143\x68\145\163\x2c\x20\x62\165\x67\x20\x66\151\170\x65\163\54\40\x6e\x65\167\x20\146\x65\x61\x74\165\x72\145\163\x2c\40\x61\x6e\144\40\x65\166\x65\156\40\x63\157\155\160\x61\x74\x69\142\151\x6c\151\x74\x79\x20\143\150\x61\156\147\145\163\x2e\40\x46\157\x72\x20\146\x75\162\x74\150\145\162\40\151\156\x71\x75\151\162\x79\40\x70\154\145\141\x73\x65\40\143\157\x6e\x74\x61\143\164\40\x3c\x61\40\x73\x74\171\154\x65\75\42\x63\x6f\x6c\157\x72\72\x72\x65\144\73\x22\40\150\x72\x65\x66\x3d\x22\155\x61\151\x6c\x74\157\72\152\157\157\155\154\141\163\165\x70\x70\157\x72\164\100\x78\145\x63\x69\x72\x69\x66\x79\x2e\x63\157\155\42\x3e\x3c\x73\164\x72\157\x6e\x67\x3e\152\157\157\x6d\x6c\141\x73\165\160\x70\157\162\164\x40\170\x65\x63\165\162\151\146\x79\x2e\143\x6f\155\74\x2f\163\164\162\x6f\156\147\x3e\74\x2f\x61\x3e\xa\40\x20\x20\40\x20\40\x20\x20\x20\x20\x20\40\x3c\57\x64\151\x76\76";
        hM:
        if (!($R5["\114\151\x63\x65\156\163\x65\x45\170\x70\151\x72\x79"] == 1)) {
            goto J9;
        }
        $jI = "\74\x64\151\x76\40\x63\154\141\163\163\x3d\42\x62\141\143\x6b\147\162\x6f\x75\156\144\x5f\x63\x6f\x6c\x6f\162\x5f\x75\x70\x64\141\x74\x65\x5f\155\x65\163\163\x61\x67\x65\x20\x6d\163\x2d\141\x75\x74\157\x22\x20\x73\164\171\154\145\x3d\x22" . $on . "\x64\x69\x73\160\154\x61\171\72\142\154\x6f\143\x6b\x3b\x63\157\154\x6f\x72\x3a\162\145\x64\x20\x3b\x20\142\x61\x63\x6b\x67\x72\x6f\165\156\x64\55\x63\157\154\x6f\162\72\x72\147\142\141\x28\x32\x35\x31\54\40\62\63\x32\54\x20\60\x2c\40\60\56\x31\x35\51\x3b\x20\x62\x6f\x72\x64\145\x72\72\x73\x6f\x6c\x69\x64\40\61\x70\170\x20\x72\147\142\141\50\62\x35\x35\x2c\x20\60\54\x20\71\54\40\x30\x2e\x33\x36\51\73\160\141\144\144\x69\x6e\x67\72\40\x31\60\x70\x78\40\x3b\155\x61\162\147\151\156\x3a\x20\61\x30\x70\x78\73\42\76\12\40\40\40\x20\40\40\40\40\x20\x20\x20\x20\x59\x6f\165\x72\40\x6d\151\x6e\151\117\x72\141\x6e\x67\x65\x20\112\157\157\x6d\154\x61\x20\x53\x41\115\114\40\123\x50\x20\x70\x6c\x75\147\x69\x6e\x20\x6c\x69\x63\x65\156\163\145\x20\167\151\x6c\x6c\40\145\170\160\151\162\x65\x20\x6f\x6e\x3c\163\x74\x72\157\156\x67\76\x20" . $pS . "\x3c\x2f\x73\x74\x72\157\x6e\x67\76\x2e\x20\x54\x68\151\x73\x20\x68\141\163\40\160\162\145\x76\x65\156\164\145\x64\x20\171\157\165\40\x66\x72\157\155\40\x72\x65\x63\x65\151\166\151\156\147\x20\x61\x6e\171\40\x70\154\x75\147\151\x6e\x20\165\x70\x64\x61\x74\145\x73\40\x63\x6f\x6e\x74\x61\x69\x6e\151\x6e\147\40\x73\x65\x63\165\x72\x69\x74\171\40\160\x61\x74\x63\x68\145\x73\54\x20\x62\165\x67\x20\146\151\170\x65\163\x2c\x20\x6e\x65\x77\x20\146\x65\x61\164\x75\162\x65\163\x2c\x20\141\156\144\40\x65\166\x65\156\x20\143\157\x6d\160\141\x74\x69\x62\151\154\151\164\171\40\x63\150\141\156\147\x65\x73\x2e\x20\106\x6f\x72\x20\146\x75\x72\164\150\x65\162\40\151\156\161\165\151\x72\171\x20\160\154\x65\141\x73\x65\x20\143\x6f\156\164\x61\143\x74\40\x3c\x61\40\x73\x74\x79\154\x65\75\42\143\157\154\x6f\162\72\x72\x65\x64\x3b\x22\x20\x68\162\x65\146\75\42\x6d\141\x69\154\164\157\72\152\157\157\155\154\x61\x73\x75\x70\160\157\162\x74\x40\170\145\143\x69\162\x69\x66\171\x2e\143\157\155\x22\76\x3c\163\x74\x72\157\156\147\x3e\152\x6f\157\x6d\x6c\x61\x73\x75\x70\x70\157\x72\164\x40\170\x65\143\x75\162\x69\146\171\x2e\x63\x6f\155\x3c\57\x73\164\x72\x6f\156\x67\76\x3c\57\141\x3e\12\40\40\40\40\x20\x20\x20\40\40\x20\x20\x20\x3c\x2f\144\151\x76\76";
        J9:
        return $jI;
    }
    public static function checkIsLicenseExpired()
    {
        $XP = self::getExpiryDate();
        $XO = isset($XP["\154\x69\143\x65\156\x73\145\x45\170\x70\151\162\x79"]) ? $XP["\154\151\143\145\x6e\163\145\105\x78\160\x69\162\171"] : "\60\60\x30\60\x2d\x30\x30\x2d\x30\60\40\60\60\72\x30\60\72\60\x30";
        $Di = intval((strtotime($XO) - time()) / (60 * 60 * 24));
        $R5 = array();
        $R5["\x4c\151\143\145\156\163\x65\x45\x78\x70\x69\x72\x79"] = $Di > 0 && $Di < 31 ? TRUE : FALSE;
        $R5["\x4c\x69\143\x65\156\163\x65\x45\170\160\x69\x72\x65\144"] = $Di > -30 && $Di < 0 ? TRUE : FALSE;
        $R5["\x4c\151\x63\145\x6e\163\145\x54\162\x69\141\x6c\105\x78\160\151\162\x79"] = $Di > 0 && $Di < 2 ? TRUE : FALSE;
        return $R5;
    }
    public static function getExpiryDate()
    {
        $jt = JFactory::getDbo();
        $jt->setQuery($jt->getQuery(true)->select("\x2a")->from("\x23\x5f\x5f\x6d\x69\156\x69\157\162\141\x6e\x67\145\137\x73\x61\155\154\x5f\143\165\163\x74\157\155\x65\162\137\x64\x65\164\141\x69\154\163"));
        return $jt->loadAssoc();
    }
    public static function showLicenseExpiryMessage($R5)
    {
        $N5 = self::getExpiryDate();
        $xH = strtotime($N5["\154\x69\x63\145\156\x73\x65\x45\170\160\151\x72\x79"]);
        $xH = $xH === FALSE || $xH <= -62169987208 ? "\55" : date("\106\x20\152\54\40\x59\x2c\40\x67\72\151\40\141", $xH);
        $XP = JFactory::getApplication()->input->get->getArray();
        $oO = isset($_SERVER["\x52\105\121\x55\x45\123\124\x5f\125\122\111"]) ? $_SERVER["\122\105\x51\125\x45\x53\124\137\x55\x52\111"] : '';
        $TQ = substr($oO, "\x2d\62\x33");
        $Yz = isset($XP["\157\160\x74\151\x6f\156"]) ? $XP["\157\160\x74\151\x6f\156"] : '';
        $S5 = Joomla\CMS\Factory::getApplication();
        if (!($S5->getName() == "\x61\x64\155\x69\x6e\x69\163\164\x72\x61\164\x6f\162")) {
            goto k6;
        }
        $user = JFactory::getUser();
        $Z8 = self::IsUserSuperUser($user);
        k6:
        $iT = self::getUpgradeURL(self::getLicenseType());
        if (!(($Yz === "\143\x6f\x6d\x5f\155\x69\x6e\151\x6f\x72\141\156\x67\145\x5f\163\x61\x6d\154" || $TQ == "\141\x64\155\x69\x6e\x69\x73\x74\x72\141\164\157\162\57\x69\x6e\x64\145\x78\x2e\x70\x68\x70") && $Z8)) {
            goto FF;
        }
        if ($R5["\114\x69\143\x65\156\163\x65\105\x78\160\x69\x72\145\144"] || $R5["\x4c\x69\143\145\x6e\163\x65\105\170\160\x69\162\x79"]) {
            goto eS;
        }
        return 0;
        goto nH;
        eS:
        return 1;
        nH:
        FF:
    }
    public static function getLicenseType()
    {
        return "\x4a\x4f\117\115\x4c\x41\x5f\123\101\115\x4c\x5f\123\120\137\x45\x4e\124\105\x52\x50\x52\x49\x53\x45\x5f\x50\x4c\x55\x47\x49\116";
    }
    public static function getUpgradeURL($Xn)
    {
        $L3 = self::getUserEmail();
        return "\x68\x74\164\x70\x73\x3a\57\57\154\157\x67\x69\156\x2e\x78\145\143\165\x72\x69\146\171\x2e\143\x6f\155\x2f\155\x6f\141\163\x2f\154\x6f\147\151\x6e\77\165\x73\145\162\156\141\155\145\75" . $L3 . "\46\162\x65\144\x69\x72\x65\x63\x74\x55\x72\154\75\150\164\164\160\x73\72\x2f\x2f\x6c\157\x67\x69\x6e\56\170\145\x63\165\162\151\x66\x79\x2e\x63\157\155\57\155\x6f\x61\163\57\141\144\155\x69\x6e\x2f\143\x75\163\164\x6f\155\145\x72\x2f\x6c\151\143\x65\x6e\163\145\162\145\x6e\145\x77\141\x6c\x73\x3f\x72\145\x6e\145\x77\x61\x6c\x72\145\x71\165\x65\x73\x74\75" . $Xn;
    }
    public static function getUserEmail()
    {
        $BI = self::loadCustomerDetails("\43\x5f\x5f\155\x69\156\x69\x6f\162\141\156\x67\x65\x5f\163\x61\x6d\x6c\x5f\143\165\x73\164\x6f\155\145\x72\137\144\x65\x74\x61\151\x6c\x73");
        return $bz = isset($BI["\x65\155\x61\x69\x6c"]) ? $BI["\x65\x6d\141\x69\154"] : '';
    }
    public static function loadCustomerDetails($Vr)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->select("\x2a");
        $bH->from($jt->quoteName($Vr));
        $bH->where($jt->quoteName("\151\144") . "\40\x3d\x20\x31");
        $jt->setQuery($bH);
        $I5 = $jt->loadAssoc();
        return $I5;
    }
    public static function IsUserSuperUser($user)
    {
        if (!$user->authorise("\x63\x6f\x72\x65\56\x61\x64\x6d\151\x6e")) {
            goto yx;
        }
        return true;
        yx:
        $Xy = UtilitiesSAML::getLoginUserGroupId($user->id);
        foreach ($Xy as $P1) {
            $KE = UtilitiesSAML::CheckUserGroup($P1);
            if (!($KE == "\x41\144\155\151\x6e\151\x73\x74\162\x61\164\x6f\x72")) {
                goto nM;
            }
            return true;
            nM:
            wk:
        }
        so:
        return false;
    }
    public static function IsUserManager($user)
    {
        $Xy = UtilitiesSAML::getLoginUserGroupId($user->id);
        foreach ($Xy as $P1) {
            $KE = UtilitiesSAML::CheckUserGroup($P1);
            if (!($KE == "\115\141\156\x61\x67\x65\x72")) {
                goto e_;
            }
            return true;
            e_:
            Tl:
        }
        lk:
        return false;
    }
    public static function loadDBValues($mF)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->select("\x2a");
        $bH->from($jt->quoteName("\x23\137\137\x6d\x69\x6e\151\x6f\162\141\156\147\145\137\x73\141\x6d\x6c\137\x63\157\x6e\x66\x69\147"));
        $bH->where($jt->quoteName("\x69\144\x70\137\156\x61\155\x65") . "\x20\x3d\x20" . $jt->quote($mF));
        $jt->setQuery($bH);
        return $jt->loadAssoc();
    }
    public static function getLicensePlanName()
    {
        return "\x6a\157\157\x6d\x6c\x61\x5f\163\x61\155\x6c\137\x73\163\x6f\137\x65\156\164\145\x72\160\162\x69\x73\145\x5f\160\x6c\141\156";
    }
    public static function generateID()
    {
        return "\137" . self::stringToHex(self::generateRandomBytes(21));
    }
    public static function stringToHex($l8)
    {
        $bu = '';
        $gL = 0;
        Ke:
        if (!($gL < strlen($l8))) {
            goto M9;
        }
        $bu .= sprintf("\x25\60\62\x78", ord($l8[$gL]));
        x0:
        $gL++;
        goto Ke;
        M9:
        return $bu;
    }
    public static function generateRandomBytes($ph, $ox = TRUE)
    {
        return openssl_random_pseudo_bytes($ph);
    }
    public static function _custom_redirect($jI, $D1)
    {
        $S5 = JFactory::getApplication();
        $S5->enqueueMessage($jI, $D1);
        $S5->redirect(JRoute::_("\151\156\144\145\170\x2e\x70\150\160\77\x6f\x70\x74\x69\157\156\x3d\143\157\x6d\137\x6d\x69\156\151\x6f\162\141\156\147\x65\137\x73\141\x6d\x6c\46\x74\x61\x62\x3d\x64\157\x6d\x61\151\x6e\x5f\x6d\x61\x70\160\151\x6e\147"));
    }
    public static function getUserProfileData($hf, $CY)
    {
        $uJ = array();
        if (!(isset($CY) && !empty($CY))) {
            goto uk;
        }
        $CY = json_decode($CY, true);
        foreach ($CY as $Sx) {
            $uf = $Sx["\x61\164\x74\162\137\x6e\141\x6d\x65"];
            $wZ = $Sx["\x61\x74\164\x72\137\166\141\154\x75\145"];
            if (!(isset($hf[$wZ]) && !empty($hf[$wZ]))) {
                goto v6;
            }
            $Qd = array();
            $Qd["\160\x72\157\146\x69\x6c\145\x5f\153\145\x79"] = $uf;
            $a2 = $hf[$wZ];
            if (!is_array($a2)) {
                goto kV;
            }
            $a2 = $a2[0];
            kV:
            if (!(isset($a2) && !empty($a2))) {
                goto Rp;
            }
            $Qd["\160\x72\x6f\x66\151\154\145\x5f\166\141\x6c\165\145"] = trim($a2);
            array_push($uJ, $Qd);
            Rp:
            v6:
            Le:
        }
        sP:
        uk:
        return $uJ;
    }
    public static function removeIfExistsUserId($Y7)
    {
        $BI = self::getUserFieldDataFromTable($Y7);
        if (!$BI) {
            goto eb;
        }
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $EC = array($jt->quoteName("\151\x74\145\155\x5f\151\x64") . "\x20\x3d\x20" . $jt->quote($Y7));
        $bH->delete($jt->quoteName("\43\x5f\137\146\x69\145\x6c\144\x73\137\166\x61\154\x75\x65\163"));
        $bH->where($EC);
        $jt->setQuery($bH);
        $jt->execute();
        eb:
    }
    public static function getUserFieldDataFromTable($Y7)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->select("\x66\x69\x65\154\144\137\151\144");
        $bH->from("\43\x5f\x5f\x66\151\x65\x6c\x64\x73\137\166\x61\154\165\x65\x73");
        $bH->where($jt->quoteName("\151\x74\x65\155\137\151\144") . "\40\x3d\40" . $jt->quote($Y7));
        $jt->setQuery($bH);
        return $jt->loadColumn();
    }
    public static function gttrlval()
    {
        $wk = UtilitiesSAML::getCustomerDetails();
        $D1 = $wk["\163\164\141\x74\165\163"];
        if (!(Mo_Saml_Local_Util::is_customer_registered() && Mo_Saml_Local_Util::check($D1) == "\164\x72\x75\x65")) {
            goto KY;
        }
        $oe = new Mo_saml_Local_Customer();
        $yq = $wk["\x63\165\163\164\x6f\x6d\x65\162\x5f\153\145\171"];
        $iL = $wk["\141\x70\151\137\153\x65\x79"];
        $XP = json_decode($oe->ccl($yq, $iL), true);
        if (!($XP != "\x6e\x75\154\154")) {
            goto sm;
        }
        self::saveTvalue($XP["\164\x72\x69\x61\154"]);
        sm:
        KY:
    }
    public static function getUserProfileDataFromTable($Y7)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->select("\x70\x72\157\146\151\x6c\x65\x5f\x6b\x65\171");
        $bH->from("\x23\137\x5f\165\163\x65\x72\x5f\x70\x72\x6f\x66\151\154\145\163");
        $bH->where($jt->quoteName("\165\x73\145\162\x5f\151\144") . "\40\x3d\x20" . $jt->quote($Y7));
        $jt->setQuery($bH);
        return $jt->loadColumn();
    }
    public static function getIdFromFields($B9)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->select("\151\144");
        $bH->from("\43\137\x5f\146\151\145\154\144\x73");
        $bH->where($jt->quoteName("\x6e\141\155\x65") . "\x20\75\x20" . $jt->quote($B9));
        $jt->setQuery($bH);
        return $jt->loadObject();
    }
    public static function selectMaxOrdering($Y7)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->select("\115\101\x58\50\x6f\x72\x64\x65\162\151\x6e\x67\x29");
        $bH->from($jt->quoteName("\x23\x5f\137\x75\x73\145\x72\x5f\160\x72\157\x66\151\154\x65\x73"));
        $bH->where($jt->quoteName("\165\x73\x65\162\137\151\x64") . "\x20\x3d\x20" . $jt->quote($Y7));
        $jt->setQuery($bH);
        $Tu = $jt->loadResult();
        return isset($Tu) && !empty($Tu) ? $Tu : "\x30";
    }
    public static function saveTvalue($wZ)
    {
        $wZ = Mo_saml_Local_Util::encrypt($wZ);
        $ZP = "\x23\137\137\155\151\x6e\x69\x6f\x72\x61\x6e\147\x65\x5f\x73\x61\155\154\x5f\x63\165\163\164\x6f\x6d\145\162\x5f\144\145\x74\x61\x69\154\x73";
        $k0 = array("\164\162\x69\x73\164\x73" => $wZ);
        self::__genDBUpdate($ZP, $k0);
    }
    public static function _remove_domain_mapp($Oz)
    {
        if (empty($Oz) || '' == $Oz) {
            goto xO;
        }
        self::__remove_domain_mapping_value($Oz);
        $jI = "\x44\x6f\x6d\141\x69\x6e\40\150\141\x73\x20\x62\145\x65\x6e\40\162\145\x6d\x6f\x76\145\144\40\163\165\143\143\x65\163\x73\x66\165\154\154\171\56";
        $D1 = "\163\165\x63\x63\x65\x73\163";
        self::_custom_redirect($jI, $D1);
        goto tP;
        xO:
        $jI = "\x45\x72\162\x6f\x72\40\160\162\157\x63\145\x73\x73\x69\x6e\x67\40\171\x6f\165\162\x20\162\x65\161\165\145\163\164\x2e\40\120\154\x65\141\163\145\40\164\x72\171\40\141\x67\141\x69\156\56";
        $D1 = "\x65\x72\x72\x6f\162";
        self::_custom_redirect($jI, $D1);
        tP:
    }
    public static function rmvlk()
    {
        $S5 = JFactory::getApplication();
        $oe = new Mo_saml_Local_Customer();
        $KH = json_decode($oe->update_status(), true);
        if (strcasecmp($KH["\163\164\x61\x74\165\x73"], "\123\125\x43\x43\105\123\123") == 0) {
            goto V5;
        }
        $Zf = "\x45\162\162\157\x72\x20\x72\145\x6d\x6f\x76\x69\x6e\x67\40\x79\157\165\x72\x20\154\x69\x63\145\156\x73\145\x20\x6b\145\x79\x2e\40\120\154\145\141\163\x65\40\164\x72\171\40\x61\x67\141\x69\156\x20\157\162\40\x63\x6f\x6e\x74\141\143\x74\x20\165\x73\x20\x61\x74\40\74\x61\40\150\162\145\146\x3d\x22\155\x61\151\154\164\157\x3a\152\x6f\x6f\x6d\x6c\141\x73\x75\x70\160\157\x72\x74\100\170\x65\x63\x75\x72\x69\146\x79\56\143\x6f\155\42\76\152\157\157\155\154\141\163\165\x70\x70\x6f\x72\x74\100\170\x65\143\x75\162\x69\x66\x79\x2e\143\x6f\x6d\x20\74\57\141\76";
        $S5->enqueueMessage($Zf, "\x65\162\162\x6f\x72");
        goto K4;
        V5:
        $ZP = "\x23\137\137\x6d\151\x6e\151\157\162\141\156\x67\145\137\163\x61\155\x6c\137\x63\x75\163\164\x6f\x6d\x65\x72\x5f\x64\145\164\141\151\x6c\163";
        $k0 = array("\x65\x6d\x61\151\x6c" => '', "\x70\141\x73\x73\x77\x6f\162\x64" => '', "\x61\144\155\151\x6e\137\160\x68\x6f\156\145" => '', "\x63\165\x73\x74\x6f\155\x65\x72\137\153\x65\x79" => '', "\x63\165\x73\x74\157\x6d\145\162\137\x74\x6f\153\145\156" => '', "\141\160\151\x5f\153\145\x79" => '', "\154\157\x67\151\x6e\137\163\x74\x61\x74\x75\163" => 1, "\163\x74\x61\x74\x75\x73" => '', "\x73\x6d\154\x5f\154\x6b" => '', "\x73\164\141\x74\x75\163" => '', "\156\145\x77\x5f\162\x65\147\151\163\164\162\x61\x74\151\x6f\x6e" => 0, "\x65\155\x61\151\x6c\137\x63\x6f\x75\x6e\164" => 0, "\x69\156\137\x63\155\x70" => '', "\x74\x72\x69\163\x74\163" => false);
        self::__genDBUpdate($ZP, $k0);
        $S5->enqueueMessage("\x59\x6f\165\x72\40\x61\143\143\x6f\165\156\164\x20\150\141\x73\40\x62\x65\145\156\x20\x72\145\x6d\x6f\x76\x65\144\x20\163\x75\x63\x63\x65\x73\163\146\x75\x6c\154\171\x2e", "\163\x75\x63\143\x65\x73\163");
        K4:
    }
    public static function __remove_domain_mapping_value($Oz)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->clear("\x2a");
        $ss = array($jt->quoteName("\144\157\155\141\x69\x6e\137\155\141\160\160\x69\x6e\x67") . "\40\x3d\x20" . $jt->quote(''));
        $EC = array($jt->quoteName("\151\144\x70\137\156\x61\155\x65") . "\x20\75\40" . $jt->quote($Oz));
        $bH->update($jt->quoteName("\43\137\137\155\x69\x6e\151\x6f\162\x61\x6e\x67\145\x5f\163\141\x6d\154\137\143\x6f\x6e\146\x69\147"))->set($ss)->where($EC);
        $jt->setQuery($bH);
        $jt->execute();
    }
    public static function fetchTLicense()
    {
        $wk = self::getCustomerDetails();
        $yq = $wk["\x63\x75\163\x74\x6f\155\145\x72\137\153\145\x79"];
        $iL = $wk["\x61\160\x69\x5f\x6b\145\x79"];
        $oe = new Mo_saml_Local_Customer();
        $XP = json_decode($oe->ccl($yq, $iL), true);
        $po = $XP["\x6c\x69\143\x65\x6e\163\145\x45\x78\x70\151\x72\171"];
        $E5 = $XP["\163\x75\x70\x70\157\162\164\105\x78\x70\151\162\x79"];
        $wZ = Mo_saml_Local_Util::encrypt($XP["\x74\x72\x69\x61\154"]);
        $WN = $XP["\156\x6f\x4f\146\123\120"];
        $FB = self::getExpiryDate();
        $xH = strtotime($FB["\154\151\143\145\156\x73\x65\x45\x78\x70\x69\x72\171"]);
        $ic = strtotime($FB["\x73\x75\160\160\x6f\x72\164\105\170\x70\x69\162\171"]);
        $Dm = Mo_saml_Local_Util::encrypt($FB["\x74\x72\x69\x73\164\x73"]);
        $pY = $FB["\156\157\x53\x50"];
        if (!(strtotime($po) > $xH)) {
            goto rK;
        }
        $ZP = "\x23\x5f\137\x6d\x69\156\151\157\x72\141\x6e\147\145\x5f\163\141\155\154\137\143\165\163\164\157\155\145\x72\x5f\x64\145\x74\x61\x69\154\163";
        $k0 = array("\154\151\143\x65\x6e\163\145\x45\170\x70\x69\162\x79" => $po);
        self::__genDBUpdate($ZP, $k0);
        rK:
        if (!(strtotime($E5) > $ic)) {
            goto yn;
        }
        $ZP = "\x23\137\x5f\155\151\x6e\151\157\x72\141\156\x67\x65\x5f\x73\x61\155\154\x5f\x63\x75\x73\164\x6f\x6d\x65\x72\x5f\x64\x65\164\x61\x69\154\x73";
        $k0 = array("\163\165\x70\x70\x6f\162\164\x45\170\x70\x69\x72\171" => $E5);
        self::__genDBUpdate($ZP, $k0);
        yn:
        if (!($wZ != $Dm)) {
            goto Ri;
        }
        $ZP = "\43\x5f\137\155\x69\x6e\x69\x6f\x72\141\156\x67\x65\137\x73\141\x6d\154\x5f\143\x75\x73\x74\x6f\x6d\x65\x72\x5f\144\145\164\x61\151\x6c\x73";
        $k0 = array("\x74\162\x69\163\x74\x73" => $Dm);
        self::__genDBUpdate($ZP, $k0);
        Ri:
        if (!($WN != $pY)) {
            goto O2;
        }
        $ZP = "\43\137\137\x6d\x69\156\151\x6f\x72\x61\x6e\x67\x65\x5f\163\x61\155\154\x5f\143\165\163\x74\157\155\145\x72\137\x64\145\164\141\x69\154\163";
        $k0 = array("\156\x6f\x53\120" => $WN);
        UtilitiesSAML::__genDBUpdate($ZP, $k0);
        O2:
    }
    public static function createAuthnRequest($Ai, $Th, $MB, $CO, $CS = "\x66\x61\154\x73\x65", $j0 = "\110\x54\x54\120\x2d\x52\x65\x64\151\162\145\x63\164")
    {
        self::createAndUpdateUpgardeUrl();
        $oj = "\74\x3f\x78\x6d\x6c\40\x76\145\x72\x73\151\x6f\156\x3d\42\61\56\x30\42\x20\x65\156\x63\157\144\x69\x6e\x67\x3d\42\125\124\106\55\x38\x22\x3f\x3e" . "\74\163\x61\x6d\154\x70\x3a\101\165\164\150\156\122\x65\x71\165\145\x73\164\x20\170\x6d\x6c\x6e\163\x3a\163\141\x6d\154\160\75\42\x75\162\x6e\72\157\141\163\x69\163\72\156\141\155\145\x73\72\164\143\72\x53\101\115\x4c\72\x32\x2e\x30\x3a\160\x72\x6f\x74\x6f\143\x6f\154\42\40\111\x44\x3d\42" . self::generateID() . "\42\x20\126\x65\162\x73\151\157\x6e\75\42\x32\56\60\42\x20\111\163\x73\x75\x65\x49\156\163\164\141\x6e\x74\x3d\42" . self::generateTimestamp() . "\x22";
        if (!($CS == "\x74\x72\x75\145")) {
            goto c7;
        }
        $oj .= "\40\x46\x6f\x72\x63\x65\x41\x75\x74\150\156\x3d\42\164\x72\165\x65\x22";
        c7:
        $oj .= "\40\x50\162\x6f\164\x6f\x63\x6f\154\102\x69\x6e\144\x69\156\x67\x3d\42\x75\x72\156\x3a\157\x61\x73\x69\163\72\x6e\x61\x6d\145\x73\72\164\143\72\x53\x41\115\x4c\x3a\62\x2e\60\x3a\142\151\x6e\144\x69\x6e\x67\x73\72\x48\x54\x54\120\55\120\117\x53\x54\x22\x20\x41\x73\x73\x65\162\x74\151\157\156\103\157\x6e\163\x75\155\145\162\x53\x65\162\x76\151\x63\x65\x55\x52\x4c\x3d\42" . $Ai . "\x22\x20\104\145\x73\x74\x69\x6e\x61\164\151\x6f\156\x3d\42" . $MB . "\x22\x3e\74\x73\141\155\x6c\72\111\163\x73\x75\x65\x72\40\170\x6d\x6c\156\163\x3a\x73\x61\x6d\x6c\75\x22\165\162\156\x3a\157\x61\x73\151\163\72\x6e\141\x6d\145\x73\72\164\x63\72\123\x41\115\114\72\x32\56\60\x3a\x61\163\163\145\162\x74\x69\157\156\x22\76" . $Th . "\74\57\x73\141\155\x6c\x3a\x49\163\163\165\145\x72\76\x3c\163\141\x6d\154\160\72\116\141\x6d\x65\111\x44\x50\157\x6c\151\x63\x79\x20\x41\x6c\154\x6f\x77\103\x72\145\141\164\145\75\42\164\162\x75\145\42\x20\106\157\162\x6d\141\164\x3d\42" . $CO . "\x22\12\x20\x20\40\x20\40\40\x20\40\x20\40\x20\40\x20\40\x20\40\x20\40\40\40\40\x20\40\x20\57\x3e\74\x2f\x73\141\x6d\154\x70\72\x41\165\x74\x68\156\122\x65\x71\x75\x65\163\x74\x3e";
        if (!(empty($j0) || $j0 == "\110\x54\x54\120\55\122\x65\144\x69\x72\145\x63\x74")) {
            goto M7;
        }
        $Q1 = gzdeflate($oj);
        $oP = base64_encode($Q1);
        $AW = urlencode($oP);
        $oj = $AW;
        M7:
        return $oj;
    }
    public static function gt_lk_trl()
    {
        $wk = self::getCustomerDetails();
        $yq = $wk["\143\x75\x73\x74\157\x6d\x65\162\x5f\153\145\x79"];
        $iL = $wk["\x61\x70\151\x5f\153\145\171"];
        $oe = new Mo_saml_Local_Customer();
        $XP = json_decode($oe->ccl($yq, $iL), true);
        $A5 = self::getExpiryDate();
        if ($XP != "\x6e\165\154\154") {
            goto C4;
        }
        $pS = $XP["\154\x69\143\145\156\163\x65\105\170\x70\151\x72\x79"];
        $Mw = 10;
        goto ea;
        C4:
        $pS = isset($XP["\154\151\143\145\156\x73\x65\x45\170\x70\x69\x72\171"]) ? $XP["\154\x69\x63\145\x6e\x73\x65\105\170\x70\151\x72\x79"] : $A5["\x6c\151\x63\145\x6e\163\145\105\170\x70\x69\x72\171"];
        $Mw = isset($XP["\156\157\x4f\x66\125\x73\x65\x72\163"]) ? $XP["\156\x6f\117\146\x55\x73\145\x72\x73"] : 10;
        ea:
        $Di = intval((strtotime($pS) - time()) / (60 * 60 * 24));
        $R5 = array();
        $R5["\114\151\x63\x65\156\163\145\105\x78\x70\x69\162\x65\144"] = 0 > $Di ? TRUE : FALSE;
        $R5["\116\157\157\146\125\163\145\x72\163"] = $Mw;
        return $R5;
    }
    public static function createLogoutRequest($lU, $Th, $MB, $LG = "\110\124\124\x50\x2d\x52\x65\144\x69\162\x65\143\164", $WY = '')
    {
        $oj = "\74\x3f\170\155\x6c\40\166\145\162\x73\x69\x6f\x6e\75\x22\61\x2e\60\x22\x20\145\156\143\x6f\144\x69\156\x67\75\x22\125\124\106\x2d\x38\42\77\x3e" . "\74\x73\x61\155\154\160\x3a\114\x6f\147\157\165\164\122\x65\x71\165\145\163\164\x20\170\155\154\156\163\x3a\163\x61\155\154\160\x3d\42\165\162\x6e\x3a\x6f\x61\163\151\163\72\x6e\141\155\x65\163\x3a\164\143\72\123\101\x4d\114\x3a\62\56\60\x3a\x70\x72\157\x74\x6f\143\x6f\x6c\x22\40\170\155\x6c\156\163\72\x73\x61\155\154\x3d\x22\165\x72\x6e\x3a\157\141\x73\151\x73\x3a\x6e\141\x6d\145\x73\72\x74\143\x3a\123\x41\x4d\x4c\x3a\62\56\60\72\x61\x73\x73\x65\162\x74\x69\157\156\x22\40\x49\x44\x3d\42" . self::generateID() . "\x22\40\x49\x73\x73\165\145\111\x6e\x73\x74\x61\156\x74\75\x22" . self::generateTimestamp() . "\x22\40\x56\x65\162\163\151\157\x6e\75\x22\x32\x2e\60\x22\x20\104\145\163\164\x69\x6e\141\164\x69\157\x6e\75\x22" . $MB . "\x22\x3e\xa\x9\11\11\11\x9\x9\x3c\163\141\x6d\154\72\111\x73\163\x75\x65\162\x20\170\155\154\x6e\x73\72\x73\x61\155\x6c\x3d\42\x75\162\x6e\x3a\157\x61\163\x69\x73\x3a\156\x61\x6d\145\163\x3a\164\x63\72\x53\x41\x4d\114\72\x32\56\x30\72\141\163\163\x65\x72\164\151\157\156\42\76" . $Th . "\x3c\57\x73\141\x6d\x6c\x3a\x49\x73\163\x75\x65\x72\x3e\xa\11\11\11\x9\11\x9\74\x73\x61\155\154\x3a\x4e\141\x6d\145\111\104\x20\170\x6d\x6c\x6e\163\x3a\x73\x61\x6d\x6c\x3d\x22\165\x72\x6e\x3a\x6f\141\x73\151\163\72\x6e\141\x6d\x65\x73\x3a\x74\x63\x3a\123\x41\x4d\x4c\x3a\x32\56\60\72\141\163\163\145\162\x74\151\x6f\x6e\x22\x3e" . $lU . "\74\x2f\x73\141\x6d\x6c\72\x4e\141\155\145\111\x44\76";
        if (empty($WY)) {
            goto D5;
        }
        $oj .= "\74\163\141\x6d\x6c\160\x3a\123\x65\x73\x73\151\x6f\156\x49\x6e\x64\145\170\x3e" . $WY . "\x3c\x2f\163\x61\x6d\x6c\x70\72\123\x65\163\x73\x69\157\x6e\x49\156\x64\145\x78\76";
        D5:
        $oj .= "\74\x2f\x73\x61\x6d\154\160\72\x4c\157\x67\x6f\x75\164\122\145\161\x75\145\x73\x74\76";
        if (!(empty($LG) || $LG == "\x48\x54\124\120\x2d\122\x65\144\151\x72\x65\x63\x74")) {
            goto EN;
        }
        $Q1 = gzdeflate($oj);
        $oP = base64_encode($Q1);
        $AW = urlencode($oP);
        $oj = $AW;
        EN:
        return $oj;
    }
    public static function rmvextnsns()
    {
        self::rmvlk();
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $ss = array($jt->quoteName("\145\156\141\x62\x6c\x65\144") . "\40\x3d\x20" . $jt->quote(0));
        $EC = array($jt->quoteName("\x65\x6c\145\155\145\x6e\x74") . "\x20\x3d\40" . $jt->quote("\160\x6b\x67\x5f\x6d\151\156\151\157\x72\141\x6e\147\x65\x73\x61\x6d\154\163\163\157") . "\117\122" . $jt->quoteName("\x65\154\145\155\145\156\x74") . "\40\x3d\40" . $jt->quote("\x73\x61\155\x6c\x72\145\144\151\x72\x65\143\164") . "\117\x52" . $jt->quoteName("\x65\154\x65\155\145\156\164") . "\x20\75\x20" . $jt->quote("\155\151\x6e\x69\x6f\162\141\x6e\x67\x65\x73\x61\155\154") . "\117\122" . $jt->quoteName("\145\154\145\155\145\x6e\x74") . "\x20\75\x20" . $jt->quote("\155\151\156\151\157\x72\141\x6e\x67\x65\x73\141\x6d\x6c\x70\154\165\x67\x69\x6e") . "\117\x52" . $jt->quoteName("\145\x6c\x65\x6d\x65\156\x74") . "\40\75\40" . $jt->quote("\143\157\155\x5f\155\x69\156\151\x6f\162\x61\156\147\x65\137\x73\141\x6d\154") . "\117\x52" . $jt->quoteName("\x65\154\145\x6d\x65\x6e\x74") . "\x20\75\40" . $jt->quote("\x73\x61\x6d\154\154\157\147\x6f\165\x74"));
        $bH->update($jt->quoteName("\x23\x5f\137\145\x78\x74\145\156\x73\151\x6f\156\163"))->set($ss)->where($EC);
        $jt->setQuery($bH);
        $BI = $jt->execute();
        $S5 = JFactory::getApplication();
        $S5->enqueueMessage("\131\x6f\x75\162\x20\124\x72\151\141\x6c\40\160\145\162\151\157\144\40\x68\141\163\x20\145\170\x70\x69\x72\145\144", "\x65\x72\162\157\x72");
        $S5->redirect(JRoute::_("\x69\156\x64\x65\x78\x2e\160\x68\x70"));
    }
    public static function createLogoutResponse($gu, $Th, $MB, $LG = "\x48\124\x54\120\55\x52\x65\x64\x69\x72\x65\x63\164")
    {
        $oj = "\74\77\x78\x6d\x6c\40\x76\145\x72\163\x69\x6f\x6e\75\x22\x31\56\60\x22\40\145\156\x63\157\144\x69\156\x67\x3d\x22\125\x54\106\x2d\70\42\77\x3e" . "\x3c\x73\141\155\154\x70\72\x4c\x6f\147\x6f\165\x74\x52\145\163\160\157\156\163\145\x20\170\155\154\156\163\72\163\141\x6d\x6c\x70\75\42\x75\x72\156\72\157\141\163\x69\x73\x3a\x6e\x61\x6d\x65\163\72\x74\x63\x3a\x53\101\x4d\114\x3a\62\56\x30\72\160\x72\x6f\x74\157\143\157\x6c\42\x20\170\155\x6c\x6e\163\x3a\x73\x61\155\x6c\75\x22\165\162\156\x3a\157\x61\x73\151\163\72\x6e\x61\x6d\145\163\72\x74\143\x3a\123\101\115\114\x3a\x32\x2e\x30\72\141\x73\x73\145\162\x74\x69\x6f\156\42\x20" . "\111\x44\75\x22" . self::generateID() . "\42\x20" . "\x56\145\162\163\x69\157\x6e\75\x22\62\56\x30\x22\40\x49\163\163\165\145\111\156\x73\x74\141\156\x74\x3d\42" . self::generateTimestamp() . "\x22\40" . "\x44\x65\163\x74\151\156\x61\164\x69\157\x6e\x3d\42" . $MB . "\x22\40" . "\111\156\122\x65\163\x70\x6f\156\x73\145\124\157\75\42" . $gu . "\x22\76" . "\x3c\163\x61\155\154\x3a\x49\x73\163\x75\145\x72\40\170\x6d\x6c\x6e\163\72\163\x61\x6d\154\x3d\42\x75\x72\156\x3a\157\141\163\x69\x73\72\x6e\141\x6d\x65\x73\x3a\164\x63\72\x53\101\115\114\x3a\62\56\x30\x3a\x61\163\x73\145\x72\x74\x69\157\x6e\x22\x3e" . $Th . "\74\57\x73\141\x6d\154\x3a\x49\163\x73\x75\x65\x72\x3e" . "\74\163\x61\155\x6c\160\x3a\123\164\x61\164\165\x73\76\x3c\163\141\155\x6c\x70\x3a\123\164\141\164\165\x73\x43\x6f\144\145\x20\x56\x61\154\165\x65\x3d\42\165\x72\x6e\72\157\141\x73\x69\163\72\156\x61\x6d\145\x73\x3a\x74\x63\72\123\101\x4d\114\72\62\56\x30\72\163\164\x61\164\x75\x73\72\x53\x75\143\x63\145\x73\x73\42\x2f\76\x3c\57\163\141\x6d\154\x70\72\123\164\141\x74\165\163\76\x3c\x2f\x73\141\155\154\160\72\114\x6f\147\157\165\164\122\x65\x73\160\x6f\x6e\x73\x65\x3e";
        if (!(empty($LG) || $LG == "\x48\124\x54\x50\55\122\145\x64\x69\x72\145\143\164")) {
            goto GN;
        }
        $Q1 = gzdeflate($oj);
        $oP = base64_encode($Q1);
        $AW = urlencode($oP);
        $oj = $AW;
        GN:
        return $oj;
    }
    public static function generateTimestamp($Q_ = NULL)
    {
        if (!($Q_ === NULL)) {
            goto yZ;
        }
        $Q_ = time();
        yZ:
        return gmdate("\x59\x2d\155\55\x64\x5c\x54\110\72\151\x3a\x73\x5c\x5a", $Q_);
    }
    public static function xpQuery(DOMNode $Ro, $bH)
    {
        static $z8 = NULL;
        if ($Ro instanceof DOMDocument) {
            goto oJ;
        }
        $ax = $Ro->ownerDocument;
        goto RU;
        oJ:
        $ax = $Ro;
        RU:
        if (!($z8 === NULL || !$z8->document->isSameNode($ax))) {
            goto s3;
        }
        $z8 = new DOMXPath($ax);
        $z8->registerNamespace("\x73\x6f\x61\x70\x2d\145\x6e\x76", "\150\x74\x74\160\x3a\57\x2f\163\143\x68\x65\155\141\x73\x2e\x78\x6d\154\x73\x6f\x61\160\x2e\157\162\147\57\163\157\x61\x70\57\145\x6e\166\145\154\157\160\x65\57");
        $z8->registerNamespace("\163\141\155\x6c\x5f\160\x72\x6f\x74\x6f\x63\x6f\154", "\165\x72\x6e\x3a\157\x61\163\x69\163\72\x6e\141\x6d\x65\x73\x3a\x74\x63\x3a\123\101\x4d\x4c\x3a\62\x2e\60\72\x70\x72\x6f\x74\157\143\x6f\x6c");
        $z8->registerNamespace("\163\x61\x6d\x6c\x5f\141\x73\x73\x65\162\x74\151\157\x6e", "\x75\x72\x6e\72\157\x61\x73\x69\163\72\156\x61\x6d\145\x73\x3a\164\143\72\123\x41\115\114\x3a\62\x2e\x30\72\x61\163\x73\145\162\164\x69\157\156");
        $z8->registerNamespace("\163\x61\155\154\137\155\145\x74\141\x64\141\x74\x61", "\165\162\156\72\x6f\x61\163\x69\163\x3a\156\x61\x6d\145\x73\x3a\164\143\72\123\101\115\114\x3a\62\x2e\60\x3a\155\x65\x74\141\144\141\164\x61");
        $z8->registerNamespace("\x64\163", "\x68\x74\164\160\72\57\57\x77\167\x77\56\167\63\x2e\157\x72\x67\57\62\x30\x30\60\x2f\60\71\x2f\x78\155\x6c\144\x73\151\147\x23");
        $z8->registerNamespace("\x78\145\x6e\143", "\150\x74\164\x70\72\x2f\57\x77\x77\167\x2e\167\63\x2e\x6f\x72\x67\57\x32\60\60\x31\x2f\x30\64\x2f\170\x6d\x6c\145\156\x63\43");
        s3:
        $uq = $z8->query($bH, $Ro);
        $bu = array();
        $gL = 0;
        R0:
        if (!($gL < $uq->length)) {
            goto GA;
        }
        $bu[$gL] = $uq->item($gL);
        f0:
        $gL++;
        goto R0;
        GA:
        return $bu;
    }
    public static function parseNameId(DOMElement $i9)
    {
        $bu = array("\x56\x61\x6c\165\x65" => trim($i9->textContent));
        foreach (array("\116\x61\x6d\145\121\x75\141\x6c\x69\x66\x69\x65\162", "\x53\120\116\141\155\145\121\x75\141\154\151\146\151\145\x72", "\x46\x6f\162\155\x61\164") as $Vc) {
            if (!$i9->hasAttribute($Vc)) {
                goto Xz;
            }
            $bu[$Vc] = $i9->getAttribute($Vc);
            Xz:
            X4:
        }
        OZ:
        return $bu;
    }
    public static function get_message_and_cause($R5, $Sj)
    {
        $uY = array();
        if ($R5 && $Sj) {
            goto nl;
        }
        if ($R5) {
            goto hb;
        }
        if (!$Sj) {
            goto jc;
        }
        $uY["\x6d\163\x67"] = "\120\154\145\141\163\x65\40\143\157\x6e\164\141\143\x74\40\171\x6f\165\x72\40\141\144\155\x69\156\151\163\x74\162\x61\164\157\x72\x2e";
        $uY["\143\141\x75\x73\145"] = "\x55\163\145\162\40\154\x69\155\151\164\40\145\x78\x63\145\145\x64\x65\x64\56";
        jc:
        goto i1;
        hb:
        $uY["\x6d\x73\x67"] = "\120\x6c\x65\x61\x73\x65\40\x63\x6f\x6e\164\141\x63\x74\x20\x79\157\x75\162\x20\141\x64\155\x69\156\151\x73\x74\x72\141\x74\157\162\x2e";
        $uY["\143\x61\x75\x73\x65"] = "\x4c\x69\x63\145\156\163\x65\40\145\170\x70\x69\162\x79\x20\x64\141\164\x65\40\145\170\143\145\145\x64\145\x64\56";
        i1:
        goto yK;
        nl:
        $uY["\155\x73\x67"] = "\x50\x6c\x65\x61\x73\145\x20\143\x6f\156\x74\x61\143\x74\40\171\x6f\x75\x72\x20\x61\x64\x6d\151\156\x69\x73\164\162\141\x74\157\162\x2e";
        $uY["\143\141\165\x73\145"] = "\114\151\x63\145\x6e\163\x65\40\145\x78\160\151\x72\171\40\144\141\x74\x65\x20\x61\156\144\40\x75\163\x65\x72\x20\x6c\151\155\x69\x74\x20\145\x78\143\145\x65\x64\145\144\x2e";
        yK:
        return $uY;
    }
    public static function xsDateTimeToTimestamp($UN)
    {
        $VD = array();
        $kG = "\57\x5e\50\x5c\144\134\144\134\144\x5c\x64\51\55\x28\134\144\x5c\x64\51\x2d\50\x5c\x64\134\144\51\x54\x28\x5c\144\134\x64\x29\72\x28\134\x64\134\x64\x29\x3a\50\x5c\144\x5c\x64\x29\x28\x3f\72\134\x2e\134\144\x2b\x29\x3f\x5a\x24\57\x44";
        if (!(preg_match($kG, $UN, $VD) == 0)) {
            goto I2;
        }
        throw new Exception("\111\x6e\166\141\x6c\x69\x64\x20\123\x41\x4d\114\x32\x20\x74\x69\x6d\x65\x73\164\141\x6d\x70\40\x70\x61\163\163\145\144\x20\x74\157\x20\170\x73\x44\141\164\145\x54\151\155\145\x54\x6f\124\x69\155\145\x73\164\x61\155\x70\x3a\40" . $UN);
        I2:
        $GK = intval($VD[1]);
        $qv = intval($VD[2]);
        $Kn = intval($VD[3]);
        $XV = intval($VD[4]);
        $TW = intval($VD[5]);
        $bQ = intval($VD[6]);
        $pH = gmmktime($XV, $TW, $bQ, $qv, $Kn, $GK);
        return $pH;
    }
    public static function extractStrings(DOMElement $b4, $JY, $Jt)
    {
        $bu = array();
        $Ro = $b4->firstChild;
        ck:
        if (!($Ro !== NULL)) {
            goto K5;
        }
        if (!($Ro->namespaceURI !== $JY || $Ro->localName !== $Jt)) {
            goto dJ;
        }
        goto VC;
        dJ:
        $bu[] = trim($Ro->textContent);
        VC:
        $Ro = $Ro->nextSibling;
        goto ck;
        K5:
        return $bu;
    }
    public static function validateElement(DOMElement $px)
    {
        $iy = new XMLSecurityDSigSAML();
        $iy->idKeys[] = "\111\104";
        $Du = self::xpQuery($px, "\x2e\x2f\144\163\72\123\x69\147\156\x61\164\x75\162\145");
        if (count($Du) === 0) {
            goto rB;
        }
        if (count($Du) > 1) {
            goto Ez;
        }
        goto zK;
        rB:
        return FALSE;
        goto zK;
        Ez:
        echo "\x58\115\x4c\123\x65\143\x3a\40\155\157\x72\x65\40\164\150\x61\x6e\40\157\156\145\40\163\151\x67\156\x61\164\x75\x72\x65\x20\x65\154\x65\x6d\x65\156\164\x20\x69\156\x20\x72\157\157\x74\x2e";
        exit;
        zK:
        $Du = $Du[0];
        $iy->sigNode = $Du;
        $iy->canonicalizeSignedInfo();
        if ($iy->validateReference()) {
            goto XF;
        }
        echo "\x58\115\x4c\x73\x65\x63\72\40\x64\151\147\145\163\x74\40\x76\x61\x6c\x69\x64\x61\x74\x69\x6f\156\x20\x66\141\x69\x6c\x65\144";
        exit;
        XF:
        $b9 = FALSE;
        foreach ($iy->getValidatedNodes() as $Qh) {
            if ($Qh->isSameNode($px)) {
                goto ic;
            }
            if ($px->parentNode instanceof DOMDocument && $Qh->isSameNode($px->ownerDocument)) {
                goto MD;
            }
            goto gK;
            ic:
            $b9 = TRUE;
            goto c_;
            goto gK;
            MD:
            $b9 = TRUE;
            goto c_;
            gK:
            Lg:
        }
        c_:
        if ($b9) {
            goto D_;
        }
        echo "\x58\x4d\114\x53\x65\143\72\x20\124\150\145\40\162\x6f\157\164\40\x65\154\x65\x6d\145\x6e\164\40\x69\163\x20\x6e\x6f\x74\40\163\x69\147\156\145\x64\x2e";
        exit;
        D_:
        $zW = array();
        foreach (self::xpQuery($Du, "\56\57\144\x73\72\113\x65\171\111\x6e\x66\x6f\x2f\x64\163\72\x58\65\60\x39\x44\141\164\141\57\x64\x73\x3a\130\x35\x30\x39\x43\145\x72\164\x69\x66\x69\x63\141\164\145") as $xS) {
            $tL = trim($xS->textContent);
            $tL = str_replace(array("\xd", "\12", "\x9", "\40"), '', $tL);
            $zW[] = $tL;
            Z3:
        }
        UM:
        $bu = array("\123\151\x67\156\x61\164\165\x72\x65" => $iy, "\x43\145\x72\164\x69\x66\x69\143\141\164\x65\x73" => $zW);
        return $bu;
    }
    public static function show_error_messages($jI, $UU)
    {
        echo "\40\x20\40\40\x20\x20\x20\40\x20\x3c\x64\151\x76\40\x73\164\x79\154\145\75\x22\146\x6f\156\164\55\x66\141\155\x69\x6c\x79\72\x43\141\x6c\151\142\x72\151\73\160\141\144\x64\151\156\147\x3a\x30\x20\x33\x25\x3b\42\76\12\x20\40\40\40\x20\40\40\40\40\40\x20\x20\74\x64\x69\166\x20\x73\164\x79\154\x65\75\42\143\157\154\x6f\x72\x3a\40\x23\141\x39\64\x34\x34\x32\x3b\x62\x61\x63\x6b\147\162\x6f\165\156\x64\55\x63\157\154\x6f\x72\x3a\x20\x23\146\62\x64\145\x64\145\x3b\x70\141\x64\144\x69\156\147\72\x20\x31\x35\160\x78\x3b\x6d\x61\x72\x67\151\156\x2d\x62\x6f\164\x74\x6f\155\72\x20\x32\x30\x70\170\x3b\164\x65\170\164\x2d\141\154\151\x67\156\72\x63\x65\156\164\145\x72\x3b\x62\157\162\x64\145\x72\72\61\x70\x78\40\163\x6f\154\151\x64\40\x23\x45\66\x42\63\x42\62\x3b\146\x6f\156\x74\55\x73\x69\x7a\x65\x3a\61\70\160\x74\x3b\x22\x3e\40\x45\122\x52\x4f\122\74\57\144\151\166\76\xa\x20\40\40\40\40\x20\x20\x20\x20\40\40\40\x3c\144\x69\x76\x20\163\x74\171\154\145\75\42\x63\x6f\154\x6f\162\x3a\x20\43\x61\x39\x34\64\64\62\x3b\x66\157\x6e\x74\55\x73\151\172\x65\x3a\61\64\x70\x74\73\x20\155\x61\162\147\x69\x6e\x2d\142\157\x74\164\x6f\x6d\x3a\62\x30\160\170\73\42\x3e\12\x20\40\40\40\x20\40\40\x20\40\x20\x20\40\x20\x20\40\40\x3c\x70\x3e\x3c\x73\x74\162\x6f\156\147\76\x45\x72\x72\157\x72\72\40\74\57\163\164\x72\x6f\156\x67\x3e";
        echo $jI;
        echo "\x3c\x2f\x70\76\12\x20\x20\40\x20\40\x20\40\x20\40\40\40\40\40\40\x20\x20\x3c\x70\76\x3c\x73\x74\x72\157\156\147\76\x50\x6f\x73\x73\x69\142\154\145\40\x43\141\165\x73\145\72\x20\x3c\57\x73\x74\x72\157\156\x67\x3e";
        echo $UU;
        echo "\74\x2f\x70\x3e\12\40\40\x20\x20\x20\40\x20\40\40\40\x20\40\x3c\57\144\151\x76\x3e\xa\x20\x20\40\x20\40\x20\x20\40\x20\x20\40\40\x3c\x66\x6f\162\155\40\x61\x63\164\x69\157\156\x3d\42";
        echo JURI::root();
        echo "\x22\76\12\40\x20\x20\40\40\x20\40\x20\x20\x20\x20\40\x20\x20\x20\x20\x3c\144\151\x76\40\x73\x74\x79\x6c\145\75\42\x6d\141\162\x67\151\156\72\x33\45\x3b\144\151\163\x70\154\141\x79\x3a\142\x6c\157\x63\x6b\73\x74\x65\x78\164\55\x61\x6c\151\147\x6e\x3a\x63\x65\156\164\145\162\x3b\x22\x3e\xa\40\x20\40\40\x20\40\x20\x20\x20\x20\x20\40\x20\x20\40\40\x20\x20\40\40\x3c\x69\x6e\x70\165\x74\x20\x73\x74\171\x6c\145\75\42\160\141\x64\144\151\156\147\72\x31\x25\73\167\151\x64\x74\150\x3a\x31\60\60\160\x78\73\142\x61\143\x6b\x67\162\157\x75\156\x64\x3a\40\43\60\x30\71\61\x43\x44\x20\x6e\157\156\145\40\x72\x65\x70\145\141\164\40\163\143\x72\157\154\x6c\40\60\x25\40\60\45\73\x63\x75\x72\163\157\162\72\x20\160\157\x69\156\164\x65\x72\73\x66\x6f\156\164\55\163\x69\x7a\x65\72\61\x35\160\x78\73\x62\157\x72\144\145\162\x2d\167\x69\144\164\x68\72\40\61\x70\170\x3b\x62\157\162\144\145\162\x2d\x73\164\x79\x6c\145\72\x20\x73\157\x6c\151\144\x3b\x62\157\162\144\145\162\55\162\141\144\x69\x75\x73\72\40\x33\x70\x78\73\167\150\151\x74\x65\x2d\x73\160\x61\143\145\72\x20\156\x6f\167\x72\x61\160\73\142\x6f\x78\x2d\x73\x69\x7a\x69\156\x67\72\40\142\157\162\x64\x65\x72\x2d\142\x6f\170\x3b\x62\157\x72\144\x65\x72\x2d\x63\x6f\x6c\157\x72\72\x20\x23\60\x30\x37\x33\101\x41\73\142\157\170\x2d\163\x68\x61\144\157\167\x3a\40\60\x70\x78\x20\x31\160\x78\40\60\x70\x78\40\162\x67\142\x61\50\61\62\x30\x2c\x20\x32\60\x30\x2c\x20\62\x33\60\x2c\40\60\56\66\51\x20\x69\x6e\163\x65\164\x3b\143\x6f\154\157\162\x3a\x20\43\106\106\106\x3b\42\x20\164\x79\x70\x65\75\42\163\165\x62\x6d\151\x74\42\x20\x76\141\x6c\x75\x65\75\x22\104\157\x6e\145\x22\40\157\156\103\154\x69\x63\153\75\x22\163\145\x6c\146\x2e\143\154\157\x73\145\50\51\x3b\x22\x3e\12\40\x20\x20\40\40\40\x20\40\x20\x20\40\40\x20\40\40\x20\74\57\x64\x69\x76\x3e\12\x20\x20\x20\40\40\40\x20\40\x20\x20\40\40\74\x2f\x66\x6f\162\155\76\xa\40\40\x20\40\40\40\x20\40\74\57\144\x69\166\76\12\40\40\x20\40\x20\40\x20\x20";
        exit;
    }
    public static function validateSignature(array $PV, XMLSecurityKeySAML $uf)
    {
        $iy = $PV["\123\x69\x67\156\141\x74\x75\162\145"];
        $ui = self::xpQuery($iy->sigNode, "\56\57\x64\x73\x3a\123\x69\x67\x6e\145\x64\111\x6e\x66\x6f\x2f\x64\163\x3a\123\x69\x67\156\x61\164\x75\x72\145\115\145\x74\150\157\x64");
        if (!empty($ui)) {
            goto jy;
        }
        throw new Exception("\115\x69\163\163\x69\156\147\40\123\151\147\x6e\141\164\x75\162\x65\115\145\x74\x68\157\x64\40\x65\154\145\155\145\156\x74\56");
        jy:
        $ui = $ui[0];
        if ($ui->hasAttribute("\x41\x6c\x67\x6f\162\151\x74\x68\155")) {
            goto sd;
        }
        throw new Exception("\115\x69\x73\163\x69\x6e\x67\40\101\x6c\147\157\x72\151\164\x68\155\55\141\x74\x74\162\151\142\x75\x74\145\40\x6f\156\40\123\x69\147\x6e\141\164\x75\162\145\115\145\164\x68\157\x64\40\145\154\145\x6d\x65\x6e\x74\56");
        sd:
        $OM = $ui->getAttribute("\x41\x6c\x67\x6f\162\151\x74\x68\155");
        if (!($uf->type === XMLSecurityKeySAML::RSA_SHA1 && $OM !== $uf->type)) {
            goto ud;
        }
        $uf = self::castKey($uf, $OM);
        ud:
        if ($iy->verify($uf)) {
            goto QK;
        }
        throw new Exception("\x55\x6e\x61\142\154\x65\x20\x74\157\x20\x76\x61\154\x69\144\x61\164\x65\x20\x53\151\x67\x6e\x61\164\x75\162\145");
        QK:
    }
    public static function castKey(XMLSecurityKeySAML $uf, $BL, $vk = "\x70\165\142\154\x69\x63")
    {
        if (!($uf->type === $BL)) {
            goto ab;
        }
        return $uf;
        ab:
        $SP = openssl_pkey_get_details($uf->key);
        if (!($SP === FALSE)) {
            goto sR;
        }
        throw new Exception("\125\x6e\x61\142\x6c\145\x20\164\157\x20\x67\145\x74\x20\153\145\171\x20\x64\x65\164\141\x69\154\x73\40\146\162\x6f\155\x20\130\x4d\x4c\x53\145\x63\165\162\151\x74\x79\113\145\x79\x53\x41\x4d\x4c\56");
        sR:
        if (isset($SP["\153\x65\171"])) {
            goto SA;
        }
        throw new Exception("\115\x69\x73\x73\151\156\x67\40\x6b\145\171\x20\x69\156\x20\160\165\x62\154\151\x63\x20\153\x65\x79\40\x64\145\164\141\151\154\x73\56");
        SA:
        $rC = new XMLSecurityKeySAML($BL, array("\164\x79\160\x65" => $vk));
        $rC->loadKey($SP["\x6b\145\171"]);
        return $rC;
    }
    public static function processResponse($jw, $qe, $CK, SAML2_Response $KH, $Qz, $pk)
    {
        $cy = $KH->getDestination();
        if (!($cy !== NULL && $cy !== $jw)) {
            goto EC;
        }
        echo "\x44\145\163\x74\151\156\x61\164\151\157\x6e\40\x69\x6e\x20\x72\145\x73\x70\x6f\x6e\x73\145\40\144\157\145\163\x6e\47\x74\x20\x6d\141\164\x63\x68\x20\x74\150\145\40\143\x75\162\162\x65\156\164\x20\125\x52\x4c\56\40\x44\145\163\x74\x69\156\141\x74\151\157\x6e\x20\151\163\x20\42" . $cy . "\42\54\40\143\x75\x72\162\x65\156\x74\x20\x55\x52\x4c\x20\x69\x73\40\x22" . $jw . "\x22\x2e";
        exit;
        EC:
        try {
            $p2 = self::checkSign($qe, $CK, $Qz, $pk);
        } catch (Exception $R8) {
        }
        return $p2;
    }
    public static function checkSign($qe, $CK, $Qz, $pk)
    {
        $zW = $CK["\103\x65\162\164\151\146\x69\143\141\164\x65\163"];
        if (count($zW) === 0) {
            goto wE;
        }
        $U0 = self::findCertificate($qe, $zW, $pk);
        goto gg;
        wE:
        $TU = $Qz;
        $TU = explode("\73", $TU);
        $U0 = $TU[0];
        gg:
        $E7 = NULL;
        $uf = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA1, array("\x74\171\160\x65" => "\160\165\142\154\x69\143"));
        $uf->loadKey($U0);
        try {
            self::validateSignature($CK, $uf);
            return TRUE;
        } catch (Exception $R8) {
            echo "\126\141\x6c\151\x64\141\164\151\157\x6e\40\x77\x69\x74\x68\x20\x6b\x65\171\x20\x66\x61\151\154\145\144\x20\167\151\x74\x68\x20\145\x78\x63\145\x70\x74\151\x6f\156\x3a\40" . $R8->getMessage();
            $E7 = $R8;
        }
        if ($E7 !== NULL) {
            goto hm;
        }
        return FALSE;
        goto gC;
        hm:
        throw $E7;
        gC:
    }
    public static function validateIssuerAndAudience($Fl, $f8, $dU)
    {
        $Th = current($Fl->getAssertions())->getIssuer();
        $ek = current(current($Fl->getAssertions())->getValidAudiences());
        if (strcmp($dU, $Th) === 0) {
            goto nI;
        }
        echo "\x49\x73\x73\165\x65\162\40\x63\x61\x6e\x6e\157\164\x20\x62\145\40\166\145\x72\x69\146\151\145\x64\x2e";
        exit;
        goto ua;
        nI:
        if (strcmp($ek, $f8) === 0) {
            goto Fr;
        }
        $KZ = "\111\x6e\x76\141\154\151\x64\x20\x61\165\x64\x69\145\x6e\143\145\40\x55\x52\111\x2e";
        $UU = "\x45\x78\160\x65\x63\x74\x65\144\x20" . $f8 . "\x2c\x20\x66\x6f\165\x6e\144\40" . $ek;
        self::show_error_messages($KZ, $UU);
        goto Xo;
        Fr:
        return TRUE;
        Xo:
        ua:
    }
    private static function findCertificate($Dw, $zW, $pk)
    {
        $ex = $zW[0];
        foreach ($zW as $TU) {
            $rr = strtolower(sha1(base64_decode($TU)));
            if (!in_array($rr, $Dw, TRUE)) {
                goto P7;
            }
            $sT = "\x2d\x2d\55\x2d\x2d\x42\x45\x47\x49\116\x20\x43\x45\122\124\111\x46\111\x43\x41\x54\105\x2d\x2d\x2d\55\x2d\12" . chunk_split($TU, 64) . "\x2d\55\55\x2d\x2d\105\x4e\x44\40\103\105\122\124\111\x46\111\103\101\124\x45\x2d\55\x2d\x2d\55\xa";
            return $sT;
            P7:
            B9:
        }
        NP:
        $ex = self::sanitize_certificate($ex);
        $HD = Jfactory::getApplication()->input->request->getArray();
        if (array_key_exists("\x52\x65\x6c\141\x79\123\x74\x61\x74\x65", $HD) && $HD["\122\145\x6c\x61\171\123\x74\x61\x74\x65"] == "\164\145\x73\164\126\x61\x6c\x69\144\141\x74\145") {
            goto TL;
        }
        echo "\x20\74\144\x69\166\x20\x73\x74\x79\x6c\x65\x3d\x22\143\x6f\154\157\162\x3a\40\x23\141\x39\64\64\x34\62\73\146\157\156\164\x2d\163\151\172\x65\x3a\61\64\160\164\x3b\x20\155\x61\x72\x67\x69\156\x2d\142\x6f\x74\164\157\x6d\x3a\x32\60\x70\x78\73\42\76\74\160\x3e\x3c\x62\76\x45\x72\x72\x6f\x72\x3a\x20\x3c\x2f\x62\76\x57\x65\40\143\157\165\154\x64\40\156\157\x74\x20\x73\x69\x67\x6e\x20\171\x6f\x75\40\x69\x6e\x2e\40\120\x6c\x65\x61\x73\x65\40\x63\157\x6e\x74\141\x63\164\x20\x79\x6f\165\x72\x20\x41\144\155\151\x6e\151\x73\x74\162\x61\164\157\162\56\x3c\57\x70\76\x3c\57\144\151\x76\76";
        goto kC;
        TL:
        echo "\74\x64\151\x76\x20\163\x74\x79\x6c\x65\75\x22\x66\x6f\156\164\x2d\146\x61\x6d\x69\154\x79\x3a\103\x61\154\151\x62\162\151\73\x70\141\144\x64\151\x6e\147\x3a\x30\x20\x33\x25\73\x22\x3e\xa\x20\40\x20\x20\40\40\x20\40\x20\40\40\x20\x20\x20\40\x20\74\144\151\166\x20\163\x74\171\154\145\x3d\42\143\x6f\154\x6f\x72\72\x20\x23\x61\71\x34\64\64\62\x3b\142\x61\x63\x6b\147\162\157\x75\156\144\55\x63\157\154\x6f\x72\72\40\43\146\62\x64\x65\x64\145\73\160\x61\x64\144\151\x6e\x67\x3a\40\x31\x35\x70\170\73\x6d\x61\162\x67\x69\156\55\x62\157\x74\164\x6f\155\x3a\x20\x32\x30\x70\x78\73\164\x65\170\164\x2d\x61\154\151\147\x6e\72\x63\x65\156\x74\145\162\x3b\x62\157\162\x64\145\x72\x3a\61\160\170\40\163\x6f\x6c\x69\144\x20\43\105\x36\x42\x33\x42\x32\x3b\146\157\x6e\164\x2d\x73\x69\x7a\x65\x3a\61\x38\x70\164\x3b\42\x3e\40\105\122\122\117\122\74\57\x64\x69\x76\x3e\12\x9\x9\x20\x20\x20\40\x20\x20\x20\x20\x20\x20\40\40\74\x64\x69\x76\40\x73\164\x79\154\145\75\x22\143\x6f\154\x6f\162\72\x20\x23\x61\71\64\x34\64\x32\x3b\146\157\156\164\x2d\x73\x69\172\145\72\x31\x34\x70\x74\x3b\40\155\141\162\147\151\x6e\x2d\x62\x6f\164\164\157\x6d\72\62\60\x70\x78\73\x22\76\74\x70\76\74\163\164\x72\157\156\147\76\105\x72\162\x6f\x72\72\40\74\57\x73\x74\162\x6f\156\x67\x3e\125\156\141\x62\154\145\x20\x74\157\x20\x66\151\x6e\144\x20\x61\40\x63\145\162\164\x69\x66\x69\143\x61\x74\x65\x20\x6d\141\x74\x63\150\151\156\147\x20\x74\150\x65\x20\x63\157\156\x66\x69\x67\x75\162\145\144\x20\x66\151\156\147\x65\x72\x70\x72\151\156\x74\56\x3c\57\160\76\12\11\11\40\x20\x20\40\40\40\40\40\x20\x20\40\40\x9\11\74\x70\76\74\163\164\x72\157\156\147\x3e\120\x6f\163\x73\x69\142\x6c\145\40\x43\x61\165\163\x65\72\40\74\x2f\163\x74\162\x6f\156\147\76\103\x6f\x6e\x74\x65\x6e\164\40\157\x66\40\47\130\x2e\65\x30\71\40\x43\145\162\164\x69\x66\x69\x63\141\164\x65\x27\40\x66\x69\x65\154\x64\40\151\x6e\40\123\x65\162\x76\151\x63\x65\40\x50\162\157\166\151\x64\145\162\40\123\x65\x74\164\x69\x6e\x67\x73\40\x69\163\x20\x69\x6e\143\x6f\x72\x72\145\143\164\x3c\57\x70\76\12\x9\x9\x9\x9\40\40\40\40\40\40\x20\40\40\x20\x20\40\74\x70\x3e\74\142\76\x45\170\x70\x65\143\x74\145\x64\x20\166\x61\x6c\165\145\72\x3c\57\x62\76" . $ex . "\x3c\57\160\76";
        echo str_repeat("\46\156\x62\163\x70\x3b", 15);
        echo "\74\57\144\151\x76\x3e\xa\x20\x20\x20\x20\x20\40\x20\x20\x20\40\x3c\x64\151\x76\40\x73\x74\x79\x6c\x65\75\x22\155\x61\162\x67\x69\x6e\x3a\x33\x25\x3b\x64\151\x73\160\x6c\x61\x79\72\142\154\x6f\143\153\73\164\x65\x78\164\x2d\x61\154\151\147\156\x3a\x63\x65\x6e\x74\145\x72\x3b\x22\x3e\xa\x9\11\x9\11\x20\40\x20\40\x3c\x66\157\162\155\x20\141\143\x74\151\x6f\x6e\x3d\42\151\156\144\145\x78\56\160\x68\x70\x22\76\12\11\x9\11\x9\x20\x20\40\x20\x3c\x64\151\x76\40\x73\x74\x79\x6c\x65\75\42\x6d\x61\162\147\x69\156\72\x33\45\73\144\x69\x73\x70\x6c\x61\171\72\x62\x6c\x6f\x63\153\x3b\x74\145\170\164\55\141\154\x69\147\x6e\72\143\145\x6e\x74\x65\162\73\42\76\74\x69\156\160\165\164\x20\x73\164\171\154\x65\75\42\160\x61\x64\144\151\x6e\147\x3a\x31\45\73\x77\x69\x64\x74\x68\x3a\61\x30\x30\x70\x78\73\x62\141\143\153\147\x72\157\x75\156\x64\x3a\40\x23\60\60\x39\61\103\104\x20\x6e\157\156\x65\x20\x72\145\160\145\141\164\40\x73\x63\162\x6f\x6c\x6c\40\60\x25\40\60\45\73\143\x75\x72\163\x6f\x72\72\x20\x70\x6f\151\156\164\145\x72\73\146\157\156\164\x2d\163\151\172\x65\x3a\x31\x35\x70\170\73\x62\157\x72\144\145\x72\x2d\x77\x69\x64\x74\150\x3a\40\x31\x70\170\x3b\142\157\162\x64\x65\162\55\x73\164\171\154\x65\x3a\40\163\157\x6c\x69\144\x3b\142\157\x72\x64\x65\x72\x2d\x72\141\x64\151\165\163\x3a\40\x33\160\x78\x3b\167\x68\x69\164\145\x2d\x73\160\141\143\x65\x3a\40\156\157\167\162\141\160\x3b\142\157\170\55\163\x69\172\x69\156\147\x3a\x20\142\157\x72\x64\x65\162\55\x62\x6f\x78\x3b\x62\157\162\144\x65\162\x2d\143\x6f\154\x6f\162\x3a\x20\43\x30\60\x37\63\x41\101\73\x62\x6f\x78\x2d\x73\150\141\144\x6f\167\x3a\x20\60\160\x78\x20\61\x70\x78\40\60\160\x78\x20\162\147\x62\x61\50\61\62\60\54\x20\x32\60\x30\54\x20\x32\x33\60\54\40\x30\x2e\66\51\x20\x69\x6e\x73\145\x74\x3b\x63\157\154\157\x72\x3a\40\x23\106\106\106\x3b\x22\164\x79\160\145\x3d\42\142\x75\164\x74\x6f\x6e\x22\40\166\141\x6c\x75\x65\x3d\42\x44\x6f\156\145\42\x20\157\x6e\103\154\x69\x63\153\x3d\x22\x73\x65\x6c\x66\56\x63\x6c\x6f\163\145\50\x29\73\42\76\x3c\57\x64\151\166\x3e";
        kC:
        exit;
    }
    private static function doDecryptElement(DOMElement $hR, XMLSecurityKeySAML $Jk, array &$kq)
    {
        $Kv = new XMLSecEncSAML();
        $Kv->setNode($hR);
        $Kv->type = $hR->getAttribute("\124\171\x70\145");
        $bL = $Kv->locateKey($hR);
        if ($bL) {
            goto SU;
        }
        throw new Exception("\x43\157\x75\x6c\x64\40\x6e\x6f\164\x20\x6c\x6f\x63\141\164\x65\40\153\145\x79\x20\x61\154\x67\157\162\151\x74\150\x6d\x20\151\156\x20\x65\156\143\x72\x79\160\x74\145\x64\40\x64\x61\x74\x61\x2e");
        SU:
        $VG = $Kv->locateKeyInfo($bL);
        if ($VG) {
            goto c8;
        }
        throw new Exception("\x43\x6f\165\x6c\x64\40\x6e\x6f\164\40\x6c\157\143\141\x74\x65\x20\x3c\144\163\151\147\72\113\145\171\111\156\146\x6f\76\x20\146\157\x72\40\164\x68\145\40\x65\x6e\143\162\171\x70\164\x65\144\x20\153\145\171\x2e");
        c8:
        $Lr = $Jk->getAlgorith();
        if ($VG->isEncrypted) {
            goto Ry;
        }
        $Qb = $bL->getAlgorith();
        if (!($Lr !== $Qb)) {
            goto ZF;
        }
        throw new Exception("\x41\154\147\157\162\x69\x74\150\155\40\x6d\x69\x73\x6d\x61\164\143\x68\40\142\x65\164\167\x65\145\x6e\x20\x69\156\160\165\x74\x20\153\x65\171\x20\x61\x6e\144\40\x6b\x65\171\40\x69\x6e\40\x6d\145\163\x73\x61\x67\145\x2e\40" . "\113\145\x79\x20\167\141\163\72\40" . var_export($Lr, TRUE) . "\x3b\40\155\145\163\163\x61\x67\145\40\167\141\x73\72\x20" . var_export($Qb, TRUE));
        ZF:
        $bL = $Jk;
        goto hO;
        Ry:
        $yH = $VG->getAlgorith();
        if (!in_array($yH, $kq, TRUE)) {
            goto rm;
        }
        throw new Exception("\x41\154\x67\x6f\x72\151\x74\x68\x6d\40\144\151\x73\141\x62\154\x65\x64\72\x20" . var_export($yH, TRUE));
        rm:
        if (!($yH === XMLSecurityKeySAML::RSA_OAEP_MGF1P && $Lr === XMLSecurityKeySAML::RSA_1_5)) {
            goto S0;
        }
        $Lr = XMLSecurityKeySAML::RSA_OAEP_MGF1P;
        S0:
        if (!($Lr !== $yH)) {
            goto Xn;
        }
        throw new Exception("\x41\154\147\157\162\x69\164\150\x6d\40\x6d\x69\x73\x6d\141\164\x63\x68\x20\142\x65\164\167\145\145\x6e\40\x69\x6e\160\x75\x74\x20\x6b\145\x79\x20\141\x6e\x64\x20\x6b\x65\171\x20\x75\x73\x65\x64\x20\164\x6f\x20\x65\x6e\143\x72\x79\x70\x74\x20" . "\40\164\150\x65\x20\x73\171\x6d\x6d\x65\x74\x72\x69\x63\40\153\145\171\x20\146\157\x72\40\164\150\145\x20\x6d\x65\163\163\141\147\x65\56\40\x4b\x65\x79\40\167\141\163\x3a\40" . var_export($Lr, TRUE) . "\73\40\x6d\145\163\x73\141\147\145\40\167\141\163\x3a\40" . var_export($yH, TRUE));
        Xn:
        $qX = $VG->encryptedCtx;
        $VG->key = $Jk->key;
        $tO = $bL->getSymmetricKeySize();
        if (!($tO === NULL)) {
            goto Lv;
        }
        throw new Exception("\x55\x6e\153\x6e\x6f\x77\156\40\153\145\x79\40\x73\151\172\x65\x20\x66\157\162\40\x65\x6e\x63\162\x79\160\164\151\x6f\x6e\40\x61\x6c\147\157\162\x69\x74\x68\155\x3a\x20" . var_export($bL->type, TRUE));
        Lv:
        try {
            $uf = $qX->decryptKey($VG);
            if (!(strlen($uf) != $tO)) {
                goto AM;
            }
            throw new Exception("\x55\x6e\145\x78\x70\x65\x63\164\x65\x64\x20\x6b\145\171\40\163\x69\172\145\x20\50" . strlen($uf) * 8 . "\x62\x69\x74\163\x29\40\x66\x6f\x72\40\x65\156\143\x72\171\160\164\x69\x6f\156\40\141\154\147\x6f\162\151\x74\x68\x6d\72\x20" . var_export($bL->type, TRUE));
            AM:
        } catch (Exception $R8) {
            $FT = $qX->getCipherValue();
            $Pe = openssl_pkey_get_details($VG->key);
            $Pe = sha1(serialize($Pe), TRUE);
            $uf = sha1($FT . $Pe, TRUE);
            if (strlen($uf) > $tO) {
                goto ys;
            }
            if (strlen($uf) < $tO) {
                goto lu;
            }
            goto XJ;
            ys:
            $uf = substr($uf, 0, $tO);
            goto XJ;
            lu:
            $uf = str_pad($uf, $tO);
            XJ:
        }
        $bL->loadkey($uf);
        hO:
        $BL = $bL->getAlgorith();
        if (!in_array($BL, $kq, TRUE)) {
            goto Gu;
        }
        throw new Exception("\x41\154\147\157\162\x69\x74\x68\x6d\40\144\151\x73\141\x62\x6c\145\x64\72\x20" . var_export($BL, TRUE));
        Gu:
        $Kg = $Kv->decryptNode($bL, FALSE);
        $i9 = "\x3c\162\x6f\157\164\x20\170\x6d\x6c\x6e\163\x3a\163\x61\155\x6c\75\x22\x75\162\x6e\72\x6f\141\163\x69\163\72\156\141\x6d\x65\163\x3a\x74\x63\72\x53\x41\115\x4c\x3a\62\x2e\x30\72\141\x73\x73\x65\162\164\x69\x6f\x6e\x22\x20" . "\170\155\x6c\x6e\163\x3a\170\163\151\x3d\42\150\x74\x74\160\x3a\x2f\57\167\167\x77\x2e\x77\x33\56\x6f\x72\147\x2f\x32\x30\x30\61\x2f\x58\x4d\114\123\x63\150\x65\x6d\x61\x2d\x69\x6e\163\x74\141\x6e\143\x65\42\76" . $Kg . "\74\x2f\x72\157\157\x74\76";
        $sE = new DOMDocument();
        if (@$sE->loadXML($i9)) {
            goto Qh;
        }
        throw new Exception("\x46\x61\x69\154\145\x64\40\x74\x6f\40\160\x61\x72\163\x65\x20\x64\x65\143\x72\171\x70\164\x65\144\x20\x58\115\x4c\x2e\40\x4d\x61\171\142\x65\40\164\x68\x65\x20\x77\162\x6f\x6e\147\40\163\150\141\x72\x65\144\x6b\x65\x79\40\x77\141\163\40\165\x73\x65\144\x3f");
        Qh:
        $GS = $sE->firstChild->firstChild;
        if (!($GS === NULL)) {
            goto UD;
        }
        throw new Exception("\x4d\x69\x73\x73\x69\x6e\147\x20\x65\x6e\143\162\x79\x70\x74\x65\144\x20\x65\x6c\145\x6d\145\156\164\x2e");
        UD:
        if ($GS instanceof DOMElement) {
            goto Hc;
        }
        throw new Exception("\104\x65\x63\x72\171\x70\x74\145\x64\x20\145\x6c\145\x6d\x65\156\x74\40\x77\x61\163\x20\156\x6f\x74\40\x61\143\164\x75\141\154\x6c\171\x20\141\40\x44\117\x4d\105\x6c\x65\x6d\x65\156\164\x2e");
        Hc:
        return $GS;
    }
    public static function decryptElement(DOMElement $hR, XMLSecurityKeySAML $Jk, array $kq = array())
    {
        try {
            return self::doDecryptElement($hR, $Jk, $kq);
        } catch (Exception $R8) {
            $fT = UtilitiesSAML::getSAMLConfiguration();
            $qZ = self::get_public_private_certificate($fT, "\160\x75\x62\154\x69\x63\x5f\143\145\x72\164\x69\146\x69\x63\x61\x74\145");
            $c3 = JPATH_BASE . DIRECTORY_SEPARATOR . "\160\x6c\165\x67\151\x6e\163" . DIRECTORY_SEPARATOR . "\x61\x75\x74\x68\x65\156\x74\151\143\x61\164\x69\x6f\156" . DIRECTORY_SEPARATOR . "\155\151\x6e\x69\157\x72\x61\x6e\x67\x65\163\x61\x6d\x6c" . DIRECTORY_SEPARATOR . "\x73\141\155\154\x32" . DIRECTORY_SEPARATOR . "\x63\145\162\164" . DIRECTORY_SEPARATOR . "\163\160\x2d\x63\145\x72\164\x69\x66\151\143\141\164\x65\x2e\x63\162\164";
            if (!empty($qZ)) {
                goto zp;
            }
            $ex = file_get_contents($c3);
            $jI = "\74\163\164\162\x6f\156\x67\76\x50\x6f\x73\163\x69\142\x6c\x65\x20\x43\x61\x75\163\145\72\x20\74\x2f\163\x74\162\157\156\x67\76\111\x66\x20\x79\x6f\x75\40\150\141\166\x65\x20\x72\x65\x6d\x6f\166\145\x64\x20\143\x75\x73\164\x6f\x6d\x20\143\x65\x72\x74\151\146\x69\x63\141\x74\x65\40\x74\150\x65\156\40\x70\x6c\x65\141\163\x65\40\x75\160\144\x61\x74\145\40\x74\x68\151\x73\40\144\x65\x66\x61\165\154\x74\x20\x70\165\142\154\x69\x63\x20\143\145\x72\164\151\x66\151\x63\141\x74\x65\40\151\x6e\40\171\x6f\165\162\40\x49\104\120\40\x73\x69\x64\x65\56";
            goto fh;
            zp:
            $ex = $qZ;
            $jI = "\x3c\x73\164\x72\157\x6e\x67\76\120\157\163\163\151\142\x6c\x65\x20\x43\141\165\x73\x65\x3a\x20\x3c\x2f\163\x74\x72\157\156\147\x3e\x49\146\x20\x79\157\x75\x20\150\x61\166\x65\40\165\x70\154\x6f\141\x64\x65\144\x20\x63\165\x73\164\x6f\155\x20\143\145\x72\164\151\146\x69\143\141\164\145\40\164\x68\145\156\x20\160\x6c\x65\141\x73\x65\x20\x75\160\x64\x61\x74\145\40\164\x68\151\163\40\x6e\x65\167\40\x63\165\163\x74\x6f\x6d\40\x70\x75\x62\x6c\x69\143\x20\143\x65\162\x74\x69\146\x69\x63\x61\164\x65\x20\x69\156\x20\x79\157\165\162\40\111\104\120\40\163\x69\x64\x65\56";
            fh:
            echo "\x3c\144\151\166\x20\163\x74\x79\x6c\x65\75\x22\146\x6f\156\164\x2d\146\141\155\151\x6c\171\72\103\141\154\x69\142\162\x69\x3b\160\141\x64\144\151\x6e\147\x3a\x30\x20\63\x25\x3b\42\76";
            echo "\x3c\x64\151\x76\x20\163\164\x79\x6c\x65\x3d\42\143\157\x6c\157\162\x3a\x20\43\x61\x39\64\64\64\62\73\142\141\143\x6b\147\162\x6f\165\156\x64\55\143\157\154\x6f\x72\x3a\x20\x23\146\x32\x64\x65\144\145\73\x70\x61\144\x64\151\x6e\x67\x3a\x20\61\65\x70\x78\x3b\x6d\x61\162\x67\x69\x6e\55\142\x6f\164\164\157\x6d\72\x20\x32\60\160\x78\73\164\145\x78\x74\x2d\x61\x6c\151\x67\156\x3a\x63\145\156\x74\x65\162\x3b\142\x6f\162\144\x65\x72\x3a\61\x70\170\40\163\x6f\154\x69\144\40\43\x45\x36\x42\63\102\x32\73\146\x6f\156\x74\x2d\163\151\172\x65\72\x31\70\x70\164\73\42\x3e\40\x45\122\122\117\122\74\x2f\144\151\166\76\xa\40\40\40\40\40\x20\x20\40\40\x20\x20\x20\x20\x20\x20\40\40\x20\40\x20\x3c\144\151\166\40\163\x74\171\154\x65\x3d\x22\143\x6f\x6c\157\162\x3a\40\x23\x61\x39\64\x34\x34\62\x3b\146\x6f\x6e\164\55\163\151\172\x65\x3a\61\x34\160\164\73\x20\x6d\141\x72\147\151\x6e\55\x62\x6f\x74\164\157\155\72\x32\60\160\170\73\42\x3e\x3c\160\x3e\x3c\x73\x74\x72\x6f\x6e\147\x3e\x45\162\x72\x6f\x72\x3a\x20\74\x2f\x73\x74\x72\157\156\x67\x3e\x55\x6e\141\x62\154\x65\x20\164\x6f\x20\x66\151\x6e\144\40\141\40\143\145\162\164\x69\146\151\x63\x61\164\145\x20\x6d\141\164\x63\x68\151\x6e\x67\x20\164\x68\x65\x20\x63\157\156\146\151\x67\x75\x72\145\144\x20\146\x69\156\x67\x65\162\x70\162\151\156\x74\x2e\74\x2f\x70\x3e\12\x20\x20\40\x20\40\x20\x20\x20\40\40\40\40\40\40\x20\x20\40\x20\40\x20\x20\x20\x20\x20\74\160\x3e" . $jI . "\x3c\57\x70\x3e\12\x9\x9\x9\x20\x20\x20\40\40\x20\40\40\x20\40\40\x20\40\x20\x20\40\x20\40\x3c\160\76\74\142\76\105\x78\160\145\x63\164\145\144\40\x76\x61\x6c\165\145\72\x20\x3c\57\x62\x3e" . $ex . "\74\x2f\x70\76";
            echo str_repeat("\46\x6e\142\163\x70\73", 15);
            echo "\x3c\x2f\144\x69\x76\x3e\xa\40\40\x20\x20\x20\40\x20\x20\x20\40\x20\40\x20\40\40\40\x20\x20\40\x20\40\x20\x20\x20\74\x64\x69\x76\x20\163\164\171\x6c\145\75\42\155\141\x72\x67\151\x6e\x3a\63\x25\x3b\144\151\x73\160\x6c\141\171\72\142\154\157\143\x6b\x3b\164\145\x78\x74\55\141\x6c\x69\x67\156\72\x63\145\156\x74\145\x72\x3b\x22\76\xa\x20\40\40\40\x20\40\x20\40\x20\40\x20\x20\x20\40\40\40\x20\40\x20\x20\x20\x20\40\40\x3c\146\x6f\x72\155\x20\x61\x63\x74\x69\x6f\x6e\x3d\42\151\156\x64\x65\x78\56\x70\x68\x70\x22\76\12\x20\x20\x20\x20\40\40\40\40\40\x20\40\40\x20\40\x20\40\x20\40\40\40\x20\40\40\x20\x20\x20\x20\x20\x3c\x64\x69\x76\x20\x73\164\171\154\x65\x3d\x22\x6d\141\x72\x67\151\x6e\72\x33\45\73\x64\x69\x73\x70\x6c\141\171\x3a\142\x6c\157\x63\153\x3b\x74\145\170\164\x2d\x61\x6c\151\x67\x6e\x3a\143\x65\156\164\x65\x72\73\42\x3e\x3c\x69\x6e\x70\x75\164\40\x73\x74\x79\154\145\x3d\42\160\141\x64\x64\x69\x6e\x67\x3a\61\x25\73\167\151\x64\x74\x68\x3a\x31\x30\60\x70\170\x3b\142\x61\143\x6b\x67\162\x6f\x75\156\x64\x3a\x20\43\x30\x30\71\x31\103\104\x20\156\157\156\145\40\x72\x65\x70\145\x61\164\40\163\x63\162\157\154\x6c\40\x30\x25\40\x30\x25\x3b\143\x75\x72\x73\157\162\72\x20\160\x6f\x69\x6e\164\x65\x72\x3b\146\x6f\156\x74\55\163\151\x7a\145\x3a\61\x35\x70\x78\73\142\x6f\x72\144\145\162\x2d\x77\151\x64\164\x68\72\x20\61\x70\x78\73\x62\157\162\144\145\162\55\x73\x74\171\x6c\x65\72\x20\163\x6f\x6c\x69\x64\x3b\142\x6f\x72\144\x65\x72\x2d\x72\x61\144\x69\x75\163\72\40\x33\160\170\x3b\167\x68\x69\x74\x65\x2d\x73\x70\x61\x63\x65\x3a\40\x6e\157\x77\x72\141\160\73\x62\157\x78\55\x73\x69\172\x69\156\147\72\40\142\157\162\x64\x65\162\x2d\x62\x6f\x78\x3b\x62\x6f\162\x64\145\162\55\x63\157\x6c\157\162\72\x20\43\60\60\x37\x33\101\x41\73\x62\x6f\170\x2d\x73\150\x61\x64\157\167\x3a\x20\x30\x70\170\40\x31\160\x78\x20\60\x70\x78\40\x72\147\142\141\50\x31\x32\x30\x2c\x20\62\60\x30\54\x20\x32\x33\x30\54\40\60\x2e\x36\51\x20\x69\x6e\163\x65\x74\73\x63\157\x6c\157\162\x3a\40\x23\x46\106\x46\x3b\x22\x74\x79\160\x65\75\x22\142\165\x74\x74\157\x6e\x22\x20\166\141\154\x75\x65\x3d\x22\x44\157\x6e\145\42\x20\157\156\103\154\151\143\x6b\x3d\42\163\145\154\x66\56\x63\154\x6f\x73\x65\x28\51\x3b\42\76\x3c\57\144\151\166\76";
            exit;
        }
    }
    public static function get_mapped_groups($rt, $rX)
    {
        $fP = array();
        foreach ($rt as $b8 => $IA) {
            if (!(!empty($b8) && in_array($b8, $rX))) {
                goto yN;
            }
            $fP[] = $IA;
            yN:
            Gv:
        }
        gO:
        return array_unique($fP);
    }
    public static function get_role_based_redirect_values($rt, $rX)
    {
        $fP = array();
        foreach ($rt as $b8 => $IA) {
            if (empty($b8)) {
                goto yI;
            }
            if (!($b8 == $rX)) {
                goto cB;
            }
            $fP = $IA;
            cB:
            yI:
            wh:
        }
        rP:
        return $fP;
    }
    public static function get_user_from_joomla($KX, $nE, $bz)
    {
        $jt = JFactory::getDBO();
        switch ($KX) {
            case "\165\x73\145\162\x6e\x61\155\145":
                $bH = $jt->getQuery(true)->select("\151\144")->from("\43\137\x5f\165\x73\145\x72\x73")->where("\x75\163\x65\162\x6e\x61\x6d\x65\x3d" . $jt->quote($nE));
                goto Ll;
            case "\145\x6d\x61\151\x6c":
            default:
                $bH = $jt->getQuery(true)->select("\151\144")->from("\x23\x5f\x5f\165\163\x65\x72\163")->where("\x65\x6d\141\x69\154\x3d" . $jt->quote($bz));
                goto Ll;
        }
        aX:
        Ll:
        $jt->setQuery($bH);
        $BI = $jt->loadObject();
        return $BI;
    }
    public static function get_user_credentials($nE)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true)->select("\151\144\x2c\x20\x70\141\x73\163\167\x6f\162\x64")->from("\43\137\x5f\165\x73\x65\x72\x73")->where("\165\x73\145\162\156\141\155\145\x3d" . $jt->quote($nE));
        $jt->setQuery($bH);
        return $jt->loadObject();
    }
    public static function getEncryptionAlgorithm($F8)
    {
        switch ($F8) {
            case "\x68\164\164\x70\72\x2f\x2f\x77\167\x77\x2e\167\x33\56\x6f\x72\147\57\x32\60\60\61\57\x30\64\x2f\x78\155\x6c\x65\156\143\43\164\162\151\x70\154\x65\144\x65\x73\x2d\143\x62\x63":
                return XMLSecurityKeySAML::TRIPLEDES_CBC;
                goto O0;
            case "\150\x74\164\x70\72\57\57\x77\167\x77\x2e\x77\x33\x2e\157\x72\147\x2f\x32\x30\60\x31\57\60\x34\57\x78\155\x6c\x65\156\143\43\x61\x65\x73\x31\62\x38\55\x63\x62\143":
                return XMLSecurityKeySAML::AES128_CBC;
            case "\150\x74\x74\160\72\57\57\167\167\x77\56\x77\63\x2e\x6f\x72\x67\x2f\62\x30\60\61\x2f\x30\64\x2f\170\155\154\145\x6e\x63\x23\141\145\x73\x31\71\62\55\143\x62\x63":
                return XMLSecurityKeySAML::AES192_CBC;
                goto O0;
            case "\x68\164\164\160\72\57\x2f\167\x77\167\56\x77\x33\x2e\157\x72\147\x2f\x32\60\x30\61\57\x30\64\57\170\x6d\154\x65\156\x63\43\141\x65\x73\x32\x35\66\55\x63\x62\143":
                return XMLSecurityKeySAML::AES256_CBC;
                goto O0;
            case "\x68\x74\x74\x70\72\57\x2f\x77\x77\x77\56\167\63\x2e\157\162\147\x2f\62\60\60\61\57\x30\64\x2f\x78\x6d\x6c\x65\x6e\143\43\162\x73\141\x2d\x31\x5f\65":
                return XMLSecurityKeySAML::RSA_1_5;
                goto O0;
            case "\150\164\164\x70\x3a\57\57\167\167\167\56\x77\63\56\157\x72\x67\57\x32\60\x30\61\x2f\60\64\x2f\170\x6d\x6c\145\x6e\x63\43\x72\163\x61\x2d\x6f\141\x65\x70\55\155\x67\146\x31\x70":
                return XMLSecurityKeySAML::RSA_OAEP_MGF1P;
                goto O0;
            case "\150\164\x74\160\72\x2f\x2f\167\167\167\56\167\x33\x2e\x6f\x72\147\57\62\60\60\x30\x2f\x30\71\57\x78\x6d\154\x64\163\x69\x67\43\x64\163\x61\55\163\x68\141\x31":
                return XMLSecurityKeySAML::DSA_SHA1;
                goto O0;
            case "\x68\164\x74\160\72\x2f\57\167\x77\167\x2e\167\x33\x2e\x6f\x72\x67\57\62\x30\x30\60\x2f\60\x39\57\x78\x6d\x6c\x64\x73\151\147\x23\162\x73\x61\55\x73\150\141\x31":
                return XMLSecurityKeySAML::RSA_SHA1;
                goto O0;
            case "\150\164\x74\x70\x3a\57\57\x77\167\167\56\x77\x33\56\157\162\x67\x2f\x32\60\60\61\57\x30\64\x2f\170\155\154\144\163\x69\147\55\x6d\157\162\145\x23\x72\163\x61\55\x73\x68\x61\x32\x35\66":
                return XMLSecurityKeySAML::RSA_SHA256;
                goto O0;
            case "\x68\x74\164\x70\72\57\x2f\x77\x77\x77\56\x77\x33\56\157\162\147\x2f\x32\60\x30\x31\x2f\x30\x34\x2f\170\155\x6c\144\163\x69\147\55\155\157\162\145\x23\162\x73\141\55\x73\x68\x61\x33\x38\64":
                return XMLSecurityKeySAML::RSA_SHA384;
                goto O0;
            case "\x68\164\164\x70\72\57\57\167\167\167\x2e\167\63\56\x6f\162\147\57\62\x30\x30\61\57\x30\x34\x2f\170\155\154\x64\163\151\x67\x2d\x6d\x6f\162\x65\43\162\163\x61\x2d\163\150\141\65\x31\x32":
                return XMLSecurityKeySAML::RSA_SHA512;
                goto O0;
            default:
                throw new Exception("\111\156\x76\141\154\x69\144\40\105\156\x63\162\171\x70\x74\151\157\156\40\x4d\145\x74\150\x6f\144\x3a\x20" . $F8);
                goto O0;
        }
        ST:
        O0:
    }
    public static function sanitize_certificate($Ay)
    {
        $Ay = preg_replace("\x2f\133\15\12\135\53\x2f", '', $Ay);
        $Ay = str_replace("\55", '', $Ay);
        $Ay = str_replace("\102\x45\107\x49\116\40\x43\105\x52\x54\111\106\x49\103\101\x54\105", '', $Ay);
        $Ay = str_replace("\105\116\104\40\x43\105\x52\124\x49\x46\x49\x43\x41\124\105", '', $Ay);
        $Ay = str_replace("\40", '', $Ay);
        $Ay = chunk_split($Ay, 64, "\15\xa");
        $Ay = "\x2d\x2d\x2d\55\x2d\102\105\x47\x49\116\x20\103\105\122\124\111\x46\x49\x43\x41\124\x45\x2d\x2d\55\x2d\x2d\xd\12" . $Ay . "\55\x2d\55\x2d\55\x45\116\x44\40\x43\x45\122\124\x49\x46\x49\x43\101\x54\x45\x2d\55\x2d\55\x2d";
        return $Ay;
    }
    public static function desanitize_certificate($Ay)
    {
        $Ay = preg_replace("\57\x5b\xd\xa\x5d\53\57", '', $Ay);
        $Ay = str_replace("\55\55\x2d\55\x2d\102\x45\107\x49\116\40\x43\x45\122\x54\x49\106\111\103\x41\124\x45\x2d\x2d\x2d\x2d\x2d", '', $Ay);
        $Ay = str_replace("\x2d\x2d\x2d\x2d\x2d\105\116\104\x20\103\105\122\124\111\x46\111\x43\101\124\x45\x2d\55\x2d\55\55", '', $Ay);
        $Ay = str_replace("\x20", '', $Ay);
        return $Ay;
    }
    public static function mo_saml_show_test_result($nE, $KC, $Nd)
    {
        ob_end_clean();
        $Nd = $Nd . "\57\x70\154\x75\x67\x69\x6e\163\57\141\x75\164\x68\145\156\x74\x69\143\x61\x74\x69\x6f\x6e\x2f\155\151\156\151\x6f\162\141\x6e\x67\x65\163\x61\155\154\57";
        echo "\74\x64\151\x76\40\x73\x74\171\154\x65\75\42\x66\x6f\156\x74\x2d\146\x61\155\151\154\171\72\103\x61\154\151\142\162\x69\x3b\x70\x61\144\x64\x69\x6e\x67\x3a\60\x20\63\x25\73\42\76";
        if (!empty($nE)) {
            goto WT;
        }
        echo "\x3c\x64\x69\166\x20\x73\x74\171\154\x65\75\42\143\157\154\157\x72\72\40\43\x61\71\64\64\64\62\73\142\x61\143\153\147\x72\x6f\x75\156\144\55\143\157\154\157\x72\72\x20\43\x66\62\144\145\144\x65\73\160\141\144\144\151\156\147\72\x20\61\x35\160\170\x3b\155\x61\x72\147\151\x6e\x2d\x62\x6f\x74\164\x6f\155\72\x20\62\60\x70\x78\x3b\164\x65\170\x74\x2d\x61\154\151\147\x6e\72\143\145\156\x74\145\x72\73\x62\157\x72\x64\x65\x72\72\x31\160\x78\40\x73\157\x6c\x69\144\40\x23\105\x36\x42\63\x42\x32\73\x66\x6f\156\164\x2d\x73\151\172\x65\x3a\x31\70\x70\x74\73\42\x3e\124\x45\x53\124\40\106\101\x49\x4c\x45\104\x3c\x2f\144\x69\166\x3e\12\40\x20\x20\40\40\40\40\x20\x20\40\40\40\40\x20\x20\40\x20\40\40\40\x3c\x64\x69\x76\x20\163\164\171\154\x65\75\x22\143\x6f\x6c\x6f\x72\x3a\40\43\141\71\x34\64\x34\62\73\146\x6f\156\x74\x2d\163\151\172\145\72\x31\x34\x70\164\x3b\x20\155\x61\x72\x67\151\156\55\142\157\164\164\157\x6d\x3a\62\60\x70\170\x3b\x22\76\127\101\122\116\x49\x4e\x47\72\x20\123\157\x6d\x65\x20\101\x74\164\x72\x69\142\x75\x74\x65\x73\40\104\151\x64\40\x4e\x6f\x74\x20\115\141\x74\x63\150\x2e\74\57\x64\151\166\x3e\12\x20\40\x20\40\x20\40\40\40\x20\40\x20\x20\x20\40\x20\40\40\40\40\40\x3c\144\x69\x76\x20\x73\x74\x79\x6c\145\75\x22\144\x69\x73\160\x6c\x61\x79\72\142\154\x6f\143\153\x3b\164\145\x78\x74\x2d\141\x6c\x69\x67\156\72\143\145\x6e\164\145\162\73\x6d\141\162\147\151\156\x2d\142\x6f\164\x74\157\155\x3a\x34\x25\73\x22\76\x3c\151\155\147\40\163\164\x79\x6c\145\75\x22\x77\151\x64\x74\x68\72\61\x35\45\x3b\x22\163\x72\x63\75\x22" . $Nd . "\151\x6d\x61\x67\x65\x73\x2f\167\x72\157\x6e\147\56\160\156\x67\x22\x3e\x3c\57\144\x69\166\x3e";
        goto m2;
        WT:
        echo "\x3c\x64\151\166\x20\x73\164\171\154\145\x3d\x22\x63\x6f\154\x6f\x72\72\x20\x23\x33\143\x37\66\63\x64\x3b\12\40\40\40\40\x20\40\40\x20\40\x20\x20\x20\x20\x20\x20\40\x20\40\x20\x20\142\x61\143\x6b\x67\x72\157\165\156\x64\55\143\157\154\x6f\x72\72\x20\x23\x64\146\146\x30\x64\x38\73\40\160\x61\144\x64\151\156\x67\x3a\x32\x25\x3b\x6d\x61\162\147\x69\x6e\x2d\142\157\x74\x74\157\x6d\x3a\62\x30\x70\x78\73\164\x65\170\164\x2d\141\154\x69\147\x6e\x3a\143\x65\x6e\164\145\162\x3b\x20\x62\157\162\144\145\x72\72\61\160\x78\x20\163\157\154\151\x64\x20\x23\101\x45\104\x42\71\101\73\x20\146\x6f\x6e\x74\55\163\x69\x7a\145\x3a\x31\70\160\164\x3b\42\76\x54\x45\123\x54\40\x53\125\x43\103\105\x53\123\106\x55\114\x3c\57\x64\x69\x76\76\xa\40\40\x20\40\40\x20\40\x20\x20\40\x20\40\x20\40\x20\x20\x20\40\x20\x20\x3c\x64\x69\166\x20\163\x74\x79\154\145\x3d\x22\x64\x69\x73\160\x6c\x61\171\72\x62\154\157\x63\x6b\x3b\164\145\x78\164\x2d\x61\x6c\x69\147\156\x3a\x63\145\156\x74\145\x72\x3b\x6d\141\x72\x67\x69\x6e\55\x62\x6f\x74\164\157\155\72\x34\x25\73\42\76\74\151\x6d\147\40\163\x74\x79\154\145\x3d\42\167\x69\x64\164\x68\x3a\x31\x35\x25\x3b\42\163\162\143\x3d\x22" . $Nd . "\x69\x6d\x61\147\x65\163\57\147\162\145\145\x6e\x5f\x63\150\x65\x63\x6b\56\160\x6e\x67\x22\76\74\x2f\144\x69\x76\x3e";
        m2:
        echo "\x3c\163\160\141\x6e\40\x73\x74\171\154\145\x3d\42\146\157\x6e\x74\55\163\x69\172\145\x3a\x31\64\160\x74\x3b\42\x3e\74\142\x3e\110\x65\x6c\x6c\157\74\x2f\x62\76\54\40" . $nE . "\x3c\57\x73\160\141\156\x3e\x3c\142\x72\x2f\x3e\x3c\160\40\163\164\x79\154\145\75\x22\x66\157\156\164\x2d\167\145\151\147\150\164\x3a\x62\157\x6c\144\73\146\157\x6e\x74\55\163\151\172\145\72\61\64\x70\x74\x3b\155\141\x72\147\151\156\x2d\x6c\x65\146\x74\72\61\45\73\42\x3e\x41\x54\x54\x52\x49\x42\125\x54\x45\123\x20\122\x45\x43\105\x49\x56\x45\104\x3a\74\x2f\160\x3e\12\x20\40\x20\40\40\40\x20\40\40\x20\x20\x20\40\40\x20\40\40\40\x20\40\x3c\164\x61\142\x6c\145\x20\x73\164\171\154\x65\75\42\x62\157\x72\144\145\x72\55\143\157\154\x6c\141\160\x73\145\x3a\x63\x6f\154\x6c\x61\x70\163\145\73\x62\157\x72\x64\x65\x72\x2d\x73\160\x61\143\x69\x6e\x67\72\x30\73\x20\x64\151\x73\160\x6c\x61\x79\72\x74\141\x62\154\x65\73\x77\151\x64\164\x68\72\61\x30\x30\45\x3b\40\146\157\156\164\55\163\151\172\x65\72\61\x34\160\164\73\142\x61\x63\x6b\147\162\x6f\x75\156\x64\55\x63\157\154\x6f\x72\x3a\43\x45\x44\105\x44\105\104\73\x22\x3e\xa\40\x20\40\x20\x20\x20\x20\x20\x20\x20\40\40\x20\40\x20\x20\x20\x20\x20\40\x3c\164\162\40\163\x74\x79\x6c\145\x3d\42\164\x65\x78\164\55\x61\154\x69\x67\x6e\x3a\143\145\x6e\x74\145\162\x3b\x22\76\x3c\164\144\40\x73\x74\x79\154\145\x3d\x22\x66\x6f\156\164\55\x77\x65\151\147\150\x74\x3a\142\x6f\x6c\144\x3b\142\157\162\x64\145\162\72\62\160\170\40\x73\157\x6c\x69\x64\x20\x23\x39\64\x39\x30\x39\60\73\x70\141\x64\x64\151\x6e\x67\72\x32\45\73\42\x3e\101\x54\x54\x52\x49\x42\x55\x54\x45\40\116\x41\x4d\105\74\x2f\164\x64\76\74\164\144\40\x73\164\171\x6c\x65\75\x22\146\157\x6e\x74\55\167\145\151\147\150\x74\72\142\157\154\144\x3b\x70\141\x64\x64\151\x6e\147\x3a\x32\x25\x3b\x62\x6f\162\x64\145\x72\72\x32\160\x78\40\x73\x6f\x6c\151\x64\x20\43\71\x34\71\60\71\x30\x3b\x20\167\157\162\x64\55\x77\162\x61\160\72\x62\162\x65\x61\153\55\167\x6f\x72\x64\73\x22\76\x41\124\x54\122\111\x42\x55\124\x45\x20\126\101\x4c\x55\x45\74\x2f\164\144\x3e\x3c\57\164\x72\x3e";
        if (!empty($KC)) {
            goto Ev;
        }
        echo "\116\157\x20\101\x74\164\x72\x69\142\x75\x74\145\x73\40\x52\x65\x63\x65\x69\x76\x65\144\56";
        goto Uh;
        Ev:
        foreach ($KC as $uf => $wZ) {
            echo "\74\164\x72\76\74\164\x64\x20\163\x74\171\154\145\x3d\47\146\157\156\164\x2d\x77\x65\151\x67\x68\164\x3a\x62\157\154\144\x3b\x62\157\162\144\145\x72\x3a\62\160\170\x20\x73\157\154\151\144\40\x23\71\x34\71\60\71\60\x3b\160\141\x64\x64\x69\156\x67\72\62\45\73\x27\76" . $uf . "\74\x2f\x74\x64\x3e\x3c\x74\144\x20\x73\x74\171\154\x65\75\47\160\141\144\x64\151\x6e\x67\72\x32\x25\73\x62\x6f\x72\144\x65\162\72\x32\x70\x78\x20\163\157\154\x69\x64\40\x23\x39\x34\x39\x30\x39\60\73\40\x77\x6f\x72\144\x2d\x77\162\x61\160\x3a\x62\x72\x65\x61\x6b\55\167\x6f\162\144\x3b\47\x3e" . implode("\74\142\162\x2f\76", (array) $wZ) . "\74\57\164\x64\x3e\x3c\x2f\x74\162\x3e";
            HS:
        }
        EL:
        Uh:
        echo "\x3c\57\164\141\142\x6c\x65\x3e\x3c\x2f\144\151\166\x3e";
        echo "\74\x64\x69\166\40\x73\x74\x79\x6c\x65\x3d\42\x6d\141\x72\147\151\156\x3a\x33\x25\x3b\x64\x69\x73\x70\154\x61\x79\72\x62\x6c\157\143\x6b\73\x74\x65\170\164\x2d\x61\x6c\x69\147\156\x3a\143\145\x6e\164\x65\162\73\42\x3e\x3c\x69\x6e\160\x75\164\40\x73\164\x79\x6c\145\75\x22\x70\x61\x64\x64\151\x6e\x67\72\61\x25\x3b\167\x69\x64\164\x68\72\61\60\x30\160\170\73\x62\141\143\153\x67\x72\157\165\x6e\144\72\40\x23\x30\x30\71\x31\103\x44\40\156\157\156\x65\40\x72\x65\160\145\141\x74\x20\163\x63\162\157\x6c\154\x20\60\x25\x20\x30\x25\73\x63\165\162\x73\157\x72\72\40\x70\157\x69\x6e\164\x65\162\x3b\146\157\x6e\x74\55\163\151\x7a\x65\72\61\x35\160\x78\x3b\142\x6f\162\144\x65\162\55\x77\x69\x64\x74\x68\x3a\x20\x31\160\170\73\142\x6f\x72\144\145\162\x2d\x73\164\171\154\x65\72\x20\163\157\154\151\144\x3b\x62\157\162\x64\145\x72\x2d\162\141\144\x69\165\x73\x3a\40\63\160\x78\73\x77\150\151\x74\x65\55\x73\160\141\143\x65\x3a\x20\156\157\x77\x72\141\x70\73\x62\157\x78\x2d\x73\151\172\x69\x6e\147\72\40\x62\157\162\144\145\162\x2d\x62\157\170\73\142\x6f\x72\x64\x65\x72\x2d\x63\x6f\154\x6f\x72\72\x20\x23\x30\x30\67\63\x41\101\x3b\x62\157\170\55\163\150\x61\144\157\167\72\x20\60\160\x78\x20\x31\160\170\x20\x30\160\170\x20\x72\147\142\x61\x28\61\x32\60\x2c\x20\x32\60\x30\x2c\40\x32\63\60\54\x20\60\x2e\66\51\x20\151\156\163\x65\164\x3b\x63\x6f\154\x6f\162\72\x20\43\x46\x46\x46\x3b\x22\x74\x79\160\x65\x3d\42\x62\165\164\164\157\x6e\x22\40\166\141\154\165\145\75\42\104\x6f\x6e\x65\42\40\157\x6e\x43\154\x69\143\153\x3d\42\x73\x65\x6c\146\x2e\143\x6c\157\163\x65\x28\x29\73\42\76\74\x2f\x64\x69\166\76";
        exit;
    }
    public static function postSAMLRequest($vl, $fz, $pk)
    {
        echo "\x3c\x68\x74\155\154\76\x3c\150\x65\141\x64\x3e\x3c\x73\143\x72\x69\x70\164\x20\x73\162\x63\75\x27\x68\x74\164\160\x73\72\57\x2f\x63\157\144\145\56\x6a\x71\x75\x65\x72\171\56\x63\157\155\x2f\x6a\x71\x75\145\x72\171\55\x31\56\x31\61\x2e\63\x2e\155\x69\156\x2e\152\x73\x27\x3e\74\x2f\x73\x63\162\x69\160\x74\76\x3c\163\143\162\x69\160\164\40\x74\x79\x70\x65\75\x22\x74\145\170\x74\57\152\x61\166\x61\163\x63\162\151\x70\x74\42\x3e\44\50\x66\x75\156\143\x74\151\x6f\156\x28\x29\x7b\144\157\143\165\155\x65\156\x74\x2e\146\x6f\x72\155\163\x5b\47\x73\141\x6d\154\x2d\x72\145\161\x75\145\163\x74\x2d\x66\157\162\x6d\x27\135\56\163\165\x62\155\x69\164\x28\x29\73\x7d\x29\x3b\x3c\57\163\x63\x72\x69\x70\x74\x3e\74\57\150\x65\141\x64\x3e\x3c\x62\157\x64\x79\76\x50\154\145\x61\x73\x65\x20\167\x61\x69\164\x2e\56\x2e\74\146\x6f\x72\x6d\40\x61\x63\x74\x69\x6f\x6e\x3d\42" . $vl . "\x22\x20\155\x65\x74\x68\x6f\x64\75\42\x70\x6f\x73\x74\42\40\x69\144\75\42\163\x61\x6d\154\x2d\162\x65\161\x75\145\163\164\55\146\x6f\x72\x6d\x22\76\x3c\151\x6e\x70\165\164\x20\164\171\160\145\x3d\x22\150\x69\x64\144\x65\x6e\x22\x20\156\141\x6d\x65\75\42\123\101\x4d\x4c\x52\x65\x71\165\145\163\164\42\x20\x76\x61\x6c\x75\145\75\x22" . $fz . "\x22\40\57\76\74\x69\x6e\x70\x75\x74\40\x74\x79\x70\x65\75\x22\x68\151\x64\x64\145\156\x22\40\x6e\141\155\145\x3d\x22\122\145\154\141\x79\x53\x74\141\x74\x65\42\x20\x76\x61\154\x75\145\x3d\x22" . htmlentities($pk) . "\42\40\x2f\x3e\74\x2f\146\x6f\162\x6d\x3e\x3c\57\x62\157\144\171\76\74\57\x68\x74\155\154\76";
        exit;
    }
    public static function postSAMLResponse($vl, $aw, $pk)
    {
        echo "\x3c\x68\164\x6d\x6c\76\x3c\150\x65\141\x64\x3e\74\x73\143\162\x69\x70\x74\x20\163\x72\x63\x3d\x27\150\x74\164\x70\163\72\57\57\143\157\144\x65\x2e\x6a\x71\165\x65\x72\171\56\x63\x6f\155\57\152\x71\165\145\x72\171\x2d\x31\x2e\x31\61\x2e\63\56\155\151\x6e\56\152\163\x27\x3e\x3c\57\163\x63\x72\151\160\164\76\x3c\x73\x63\x72\x69\x70\164\x20\164\x79\x70\x65\75\42\164\145\170\164\x2f\152\141\166\x61\x73\x63\x72\x69\x70\x74\42\76\x24\x28\x66\165\156\x63\x74\151\157\x6e\x28\x29\x7b\x64\157\x63\165\155\145\156\x74\x2e\146\157\162\x6d\x73\x5b\47\163\141\155\x6c\x2d\x72\145\161\165\x65\x73\164\x2d\146\x6f\162\155\x27\135\56\x73\x75\x62\155\151\164\50\x29\73\x7d\51\x3b\x3c\x2f\163\143\162\x69\x70\164\76\x3c\x2f\150\x65\141\x64\x3e\x3c\142\x6f\144\171\76\120\x6c\x65\141\x73\145\40\x77\x61\151\x74\x2e\56\x2e\x3c\146\x6f\x72\155\40\141\143\164\x69\x6f\156\75\x22" . $vl . "\x22\40\x6d\145\x74\150\x6f\x64\75\42\x70\157\x73\x74\42\x20\151\x64\x3d\x22\x73\141\155\154\55\x72\x65\161\x75\145\163\164\x2d\146\157\x72\155\42\76\x3c\151\156\x70\165\164\40\x74\x79\x70\x65\x3d\x22\x68\151\x64\144\145\156\x22\40\x6e\141\155\145\x3d\x22\x53\101\115\x4c\x52\145\163\x70\157\x6e\163\145\42\x20\166\x61\154\x75\145\75\42" . $aw . "\x22\40\x2f\76\74\151\156\x70\x75\x74\x20\x74\171\160\x65\75\42\150\x69\x64\x64\x65\x6e\42\40\x6e\141\155\145\75\x22\122\x65\x6c\141\171\123\x74\141\x74\x65\42\x20\166\x61\x6c\165\145\75\x22" . htmlentities($pk) . "\x22\40\57\x3e\x3c\x2f\x66\157\162\155\76\74\57\x62\157\x64\x79\x3e\74\x2f\150\164\155\x6c\x3e";
        exit;
    }
    public static function insertSignature(XMLSecurityKeySAML $uf, array $zW, DOMElement $px = NULL, DOMNode $n_ = NULL)
    {
        $iy = new XMLSecurityDSigSAML();
        $iy->setCanonicalMethod(XMLSecurityDSigSAML::EXC_C14N);
        switch ($uf->type) {
            case XMLSecurityKeySAML::RSA_SHA256:
                $vk = XMLSecurityDSigSAML::SHA256;
                goto VV;
            case XMLSecurityKeySAML::RSA_SHA384:
                $vk = XMLSecurityDSigSAML::SHA384;
                goto VV;
            case XMLSecurityKeySAML::RSA_SHA512:
                $vk = XMLSecurityDSigSAML::SHA512;
                goto VV;
            default:
                $vk = XMLSecurityDSigSAML::SHA1;
        }
        F9:
        VV:
        $iy->addReferenceList(array($px), $vk, array("\x68\x74\x74\160\x3a\x2f\x2f\167\x77\x77\x2e\167\x33\56\x6f\x72\147\57\62\60\x30\60\x2f\60\71\57\x78\155\154\144\163\x69\147\43\x65\156\166\x65\154\157\x70\145\x64\x2d\163\151\x67\x6e\x61\164\x75\x72\145", XMLSecurityDSigSAML::EXC_C14N), array("\x69\144\137\x6e\x61\155\145" => "\111\104", "\157\166\x65\x72\167\162\151\x74\145" => FALSE));
        $iy->sign($uf);
        foreach ($zW as $Ay) {
            $iy->add509Cert($Ay, TRUE);
            pz:
        }
        zw:
        $iy->insertSignature($px, $n_);
    }
    public static function __genDBUpdate($ZP, $k0)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        foreach ($k0 as $uf => $wZ) {
            $ER[] = $jt->quoteName($uf) . "\40\x3d\40" . $jt->quote($wZ);
            FZ:
        }
        gB:
        $bH->update($jt->quoteName($ZP))->set($ER)->where($jt->quoteName("\x69\x64") . "\40\75\40\x31");
        $jt->setQuery($bH);
        $jt->execute();
    }
    public static function signXML($i9, $c3, $ou, $PQ, $Fz = '')
    {
        $w5 = array("\x74\x79\x70\x65" => "\x70\162\x69\x76\x61\164\x65");
        if ($PQ == "\x52\123\101\137\x53\x48\x41\x32\x35\66") {
            goto Kk;
        }
        if ($PQ == "\x52\123\101\137\123\x48\x41\x33\70\64") {
            goto MH;
        }
        if ($PQ == "\122\x53\x41\137\x53\110\x41\65\x31\62") {
            goto xi;
        }
        $uf = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA1, $w5);
        goto lB;
        Kk:
        $uf = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA256, $w5);
        goto lB;
        MH:
        $uf = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA384, $w5);
        goto lB;
        xi:
        $uf = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_SHA512, $w5);
        lB:
        $uf->loadKey($ou, TRUE);
        $Zb = file_get_contents($c3);
        $ti = new DOMDocument();
        $ti->loadXML($i9);
        $bj = $ti->firstChild;
        if (!empty($Fz)) {
            goto kF;
        }
        self::insertSignature($uf, array($Zb), $bj);
        goto YG;
        kF:
        $mg = $ti->getElementsByTagName($Fz)->item(0);
        self::insertSignature($uf, array($Zb), $bj, $mg);
        YG:
        $h9 = $bj->ownerDocument->saveXML($bj);
        $yR = base64_encode($h9);
        return $yR;
    }
    public static function getSAMLConfiguration($zB = '')
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->select("\x2a");
        $bH->from($jt->quoteName("\43\137\137\x6d\151\156\x69\157\162\x61\x6e\x67\145\x5f\163\x61\155\x6c\137\x63\x6f\x6e\x66\x69\x67"));
        if (empty($zB)) {
            goto pj;
        }
        $EC = array($jt->quoteName("\x69\x64\160\137\145\x6e\164\151\x74\x79\x5f\151\144") . "\40\x3d\40" . $jt->quote($zB));
        $bH->where($EC);
        pj:
        $jt->setQuery($bH);
        $hf = $jt->loadAssocList();
        return $hf;
    }
    public static function getRoleMapping($wU)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->select("\52");
        $bH->from($jt->quoteName("\x23\137\x5f\x6d\151\156\x69\157\x72\x61\156\x67\x65\137\163\x61\155\x6c\x5f\x72\x6f\x6c\145\x5f\155\141\x70\x70\151\x6e\147"));
        $bH->where($jt->quoteName("\x69\144\x70\137\151\x64") . "\x20\75\x20" . $wU["\151\x64"]);
        $jt->setQuery($bH);
        $vx = $jt->loadAssoc();
        return $vx;
    }
    public static function updateCurrentUserName($xd, $B9)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $ss = array($jt->quoteName("\156\141\155\x65") . "\40\x3d\x20" . $jt->quote($B9));
        $EC = array($jt->quoteName("\x69\x64") . "\x20\x3d\x20" . $jt->quote($xd));
        $bH->update($jt->quoteName("\43\x5f\137\165\163\x65\x72\x73"))->set($ss)->where($EC);
        $jt->setQuery($bH);
        $jt->execute();
    }
    public static function updateUsernameToSessionId($nE, $bw)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $ss = array($jt->quoteName("\165\163\145\x72\x6e\x61\x6d\145") . "\40\75\x20" . $jt->quote($nE));
        $EC = array($jt->quoteName("\x73\145\163\163\151\157\156\x5f\151\x64") . "\40\x3d\40" . $jt->quote($bw));
        $bH->update($jt->quoteName("\x23\x5f\x5f\163\145\163\x73\x69\x6f\156"))->set($ss)->where($EC);
        $jt->setQuery($bH);
        $jt->execute();
    }
    public static function auto_update_metadata($Ik)
    {
        $yG = $Ik["\155\144\x61\x74\141\137\163\171\x6e\x63\x5f\151\x6e\164\x65\162\166\x61\x6c"];
        $xd = $Ik["\x69\144"];
        $UN = time();
        if (!($UN >= $Ik["\155\x65\164\141\144\141\x74\141\x5f\x63\x68\145\143\153\137\x74\151\x6d\x65\163\164\141\155\x70"] || $Ik["\x6d\x65\x74\x61\144\x61\x74\x61\137\x63\150\145\143\153\137\x74\151\155\145\163\x74\141\x6d\160"] == 0)) {
            goto La;
        }
        if ($yG == "\150\x6f\165\x72\154\171") {
            goto OC;
        }
        if ($yG == "\x64\x61\x69\x6c\x79") {
            goto eh;
        }
        if ($yG == "\x77\145\145\x6b\x6c\171") {
            goto wc;
        }
        $QT = 60 * 60 * 24 * 7 * 30;
        goto uC;
        wc:
        $QT = 60 * 60 * 24 * 7;
        uC:
        goto tf;
        eh:
        $QT = 60 * 60 * 24;
        tf:
        goto Dq;
        OC:
        $QT = 60 * 60;
        Dq:
        $UN = time() + $QT;
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $ss = array($jt->quoteName("\x6d\145\164\x61\144\141\164\141\x5f\143\x68\x65\143\x6b\x5f\x74\x69\155\145\163\164\141\x6d\x70") . "\40\x3d\x20" . $jt->quote($UN));
        $EC = array($jt->quoteName("\x69\144") . "\40\x3d\40" . $jt->quote($xd));
        $bH->update($jt->quoteName("\43\137\137\155\x69\x6e\x69\x6f\162\141\156\x67\x65\x5f\163\141\155\x6c\x5f\x63\x6f\156\146\x69\147"))->set($ss)->where($EC);
        $jt->setQuery($bH);
        $jt->execute();
        $vl = $Ik["\x6d\145\x74\x61\144\141\164\x61\137\x75\x72\154"];
        if (!($Ik["\x61\x75\164\x6f\x5f\163\171\x6e\x63\137\x65\x6e\141\142\154\145"] == "\x6f\156")) {
            goto Ud;
        }
        require_once JPATH_SITE . DIRECTORY_SEPARATOR . "\141\144\x6d\x69\156\x69\163\x74\x72\141\164\x6f\x72" . DIRECTORY_SEPARATOR . "\143\x6f\x6d\160\x6f\156\x65\x6e\164\163" . DIRECTORY_SEPARATOR . "\x63\x6f\x6d\x5f\x6d\151\x6e\151\157\x72\x61\x6e\x67\x65\137\163\x61\x6d\x6c" . DIRECTORY_SEPARATOR . "\x63\x6f\156\x74\x72\x6f\x6c\x6c\x65\162\163" . DIRECTORY_SEPARATOR . "\x6d\171\141\x63\x63\157\165\156\164\56\160\x68\x70";
        $vl = filter_var($vl, FILTER_SANITIZE_URL);
        $K3 = array("\163\163\154" => array("\166\145\162\151\x66\171\x5f\160\145\145\162" => false, "\166\145\162\151\146\171\x5f\160\145\x65\x72\x5f\x6e\141\155\145" => false));
        $dD = file_get_contents($vl, false, stream_context_create($K3));
        UtilitiesSAML::auto_upload_metadata($dD, $xd);
        Ud:
        La:
    }
    public static function auto_upload_metadata($dD, $xd)
    {
        require_once JPATH_SITE . DIRECTORY_SEPARATOR . "\x61\x64\x6d\151\156\x69\x73\164\x72\141\164\x6f\162" . DIRECTORY_SEPARATOR . "\143\x6f\155\160\x6f\x6e\x65\156\x74\x73" . DIRECTORY_SEPARATOR . "\143\157\x6d\x5f\155\x69\x6e\151\x6f\162\x61\x6e\147\145\137\163\141\155\154" . DIRECTORY_SEPARATOR . "\x68\145\x6c\x70\145\x72\163" . DIRECTORY_SEPARATOR . "\x4d\145\x74\141\144\x61\x74\x61\122\145\141\x64\x65\x72\x2e\x70\150\160";
        $ti = new DOMDocument();
        $ti->loadXML($dD);
        restore_error_handler();
        $KL = $ti->firstChild;
        if (!empty($KL)) {
            goto ht;
        }
        return;
        goto D9;
        ht:
        $o_ = new IDPMetadataReader($ti);
        $zn = $o_->getIdentityProviders();
        if (!empty($zn)) {
            goto yH;
        }
        return;
        yH:
        foreach ($zn as $uf => $WH) {
            $FR = $WH->getLoginURL("\110\x54\124\120\55\122\145\144\151\x72\145\x63\164");
            $v4 = $WH->getLogoutURL("\110\124\x54\x50\55\x52\145\144\151\162\x65\143\x74");
            $LL = $WH->getEntityID();
            $Ck = $WH->getSigningCertificate();
            $nh = implode("\x3b", $Ck);
            $jt = JFactory::getDbo();
            $bH = $jt->getQuery(true);
            $ss = array($jt->quoteName("\151\x64\160\137\x65\x6e\164\151\164\171\x5f\x69\144") . "\40\75\40" . $jt->quote(isset($LL) ? $LL : 0), $jt->quoteName("\163\151\x6e\x67\x6c\145\x5f\x73\x69\147\156\x6f\156\137\163\145\162\166\x69\x63\x65\x5f\165\x72\154") . "\x20\75\40" . $jt->quote(isset($FR) ? $FR : 0), $jt->quoteName("\x73\x69\156\147\x6c\x65\x5f\154\157\147\x6f\165\x74\x5f\165\162\154") . "\x20\x3d\40" . $jt->quote(isset($v4) ? $v4 : 0), $jt->quoteName("\x62\151\x6e\x64\x69\156\x67") . "\x20\75\40" . $jt->quote("\x48\124\x54\120\55\122\145\x64\x69\162\145\143\164"), $jt->quoteName("\x63\145\162\x74\151\x66\x69\143\141\164\145") . "\40\75\x20" . $jt->quote(isset($Ck) ? $nh : 0));
            $EC = array($jt->quoteName("\x69\144") . "\x20\x3d" . $jt->quote($xd));
            $bH->update($jt->quoteName("\x23\137\x5f\x6d\151\x6e\x69\157\x72\x61\x6e\x67\x65\137\163\141\x6d\154\x5f\x63\x6f\x6e\x66\151\x67"))->set($ss)->where($EC);
            $jt->setQuery($bH);
            $jt->execute();
            goto A8;
            wq:
        }
        A8:
        return;
        D9:
    }
    public static function getCustomerDetails()
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->select("\x2a");
        $bH->from($jt->quoteName("\x23\x5f\x5f\155\151\156\x69\157\162\141\x6e\147\145\137\x73\141\155\x6c\x5f\x63\x75\x73\164\157\x6d\145\x72\x5f\x64\x65\164\141\151\x6c\163"));
        $bH->where($jt->quoteName("\151\144") . "\40\x3d\40\x31");
        $jt->setQuery($bH);
        $F_ = $jt->loadAssoc();
        return $F_;
    }
    public static function getCustomerincmk_lk($by)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->select($by);
        $bH->from("\43\137\137\x6d\151\x6e\151\x6f\162\141\156\x67\145\x5f\x73\x61\155\x6c\x5f\x63\165\163\164\157\x6d\145\x72\x5f\x64\x65\164\141\151\154\x73");
        $bH->where($jt->quoteName("\x69\x64") . "\40\x3d\40" . $jt->quote(1));
        $jt->setQuery($bH);
        $BI = $jt->loadColumn();
        return $BI;
    }
    public static function nOfSP()
    {
        $wk = Mo_Saml_Local_Util::getCustomerDetails();
        $oe = new Mo_saml_Local_Customer();
        $yq = $wk["\x63\165\x73\164\157\x6d\x65\162\137\153\x65\x79"];
        $iL = $wk["\x61\x70\151\137\153\145\x79"];
        $KH = json_decode($oe->ccl($yq, $iL), true);
        $p3 = isset($KH["\156\x6f\117\146\x53\120"]) ? $KH["\x6e\157\117\x66\123\120"] : 0;
        return $p3;
    }
    public static function idpCnt()
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->select(array("\x2a"));
        $bH->from($jt->quoteName("\x23\x5f\x5f\x6d\x69\156\x69\x6f\x72\141\x6e\x67\x65\137\163\141\x6d\154\137\x63\x6f\156\146\x69\x67"));
        $jt->setQuery($bH);
        $F_ = $jt->loadAssocList();
        $Yu = count($F_);
        return $Yu;
    }
    public static function isIDPConfigured()
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->select($jt->quoteName(array("\151\144")));
        $bH->from($jt->quoteName("\x23\x5f\137\x6d\x69\x6e\x69\157\162\x61\156\x67\x65\137\163\141\155\154\x5f\x63\157\x6e\x66\151\147"));
        $jt->setQuery($bH);
        $F_ = $jt->loadObjectList();
        return $F_;
    }
    public static function generateCertificate($Mb, $VQ, $vz, $OJ)
    {
        $Rh = JPATH_BASE;
        $sC = substr($Rh, 0, strrpos($Rh, "\x61\x64\x6d\x69\156\151\x73\164\162\141\x74\x6f\x72"));
        $dF = $sC . "\x70\x6c\x75\147\x69\x6e\x73" . DIRECTORY_SEPARATOR . "\141\x75\164\x68\145\x6e\x74\x69\143\x61\164\x69\x6f\156" . DIRECTORY_SEPARATOR . "\155\151\156\151\157\x72\141\156\147\x65\x73\x61\155\x6c" . DIRECTORY_SEPARATOR . "\163\141\x6d\154\62" . DIRECTORY_SEPARATOR . "\143\145\162\x74" . DIRECTORY_SEPARATOR . "\x6f\160\145\156\x73\x73\x6c\x2e\143\156\146";
        $Wo = array("\143\x6f\156\146\x69\x67" => $dF, "\x64\151\x67\145\x73\x74\x5f\x61\154\147" => "{$VQ}", "\160\162\151\x76\141\164\145\137\153\145\x79\137\x62\x69\164\x73" => $vz, "\160\162\x69\166\141\164\145\137\x6b\145\171\x5f\164\x79\x70\145" => OPENSSL_KEYTYPE_RSA);
        $Xd = openssl_pkey_new($Wo);
        $F6 = openssl_csr_new($Mb, $Xd, $Wo);
        $zi = openssl_csr_sign($F6, null, $Xd, $OJ, $Wo, time());
        openssl_x509_export($zi, $SH);
        openssl_pkey_export($Xd, $Cq, null, $Wo);
        openssl_csr_export($F6, $Do);
        sA:
        if (!(($R8 = openssl_error_string()) !== false)) {
            goto DU;
        }
        error_log($R8);
        goto sA;
        DU:
        $zW = array("\x70\165\142\x6c\x69\x63\137\153\145\171" => $SH, "\160\x72\x69\x76\x61\x74\x65\137\x6b\145\171" => $Cq);
        $UV = UtilitiesSAML::getCustom_CertificatePath("\x43\x75\163\x74\x6f\155\120\165\x62\x6c\x69\x63\x43\145\x72\x74\x69\x66\x69\143\141\164\145\x2e\143\x72\x74");
        file_put_contents($UV, $zW["\160\165\x62\x6c\x69\143\x5f\153\145\171"]);
        $zX = UtilitiesSAML::getCustom_CertificatePath("\x43\x75\163\164\157\x6d\x50\162\x69\x76\141\x74\145\103\x65\x72\x74\151\x66\151\x63\141\x74\x65\x2e\153\x65\x79");
        file_put_contents($zX, $zW["\x70\x72\151\166\141\x74\145\137\153\145\171"]);
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $ss = array($jt->quoteName("\x70\165\142\x6c\x69\143\137\143\x65\162\x74\x69\146\151\143\x61\x74\145") . "\40\75\40" . $jt->quote(isset($zW["\x70\165\x62\154\x69\143\137\153\145\x79"]) ? $zW["\x70\165\x62\x6c\x69\143\x5f\153\x65\171"] : null), $jt->quoteName("\x70\x72\151\x76\141\x74\x65\137\x63\x65\162\164\151\146\x69\143\141\x74\x65") . "\40\75\x20" . $jt->quote(isset($zW["\x70\162\x69\x76\141\164\145\137\153\145\171"]) ? $zW["\x70\162\x69\166\x61\x74\x65\x5f\153\145\x79"] : null));
        $bH->update($jt->quoteName("\43\137\x5f\x6d\x69\x6e\x69\157\x72\141\x6e\x67\x65\x5f\x73\x61\155\x6c\x5f\x63\x6f\156\146\151\147"))->set($ss);
        $jt->setQuery($bH);
        $jt->execute();
    }
    public static function getCustom_CertificatePath($C_)
    {
        $Rh = JPATH_BASE;
        $sC = substr($Rh, 0, strrpos($Rh, "\141\x64\x6d\x69\156\x69\x73\164\162\x61\x74\157\x72"));
        $gn = $sC . "\x70\154\165\147\x69\x6e\x73" . DIRECTORY_SEPARATOR . "\x61\165\164\150\x65\x6e\x74\151\143\141\x74\151\157\156" . DIRECTORY_SEPARATOR . "\155\151\x6e\151\157\162\x61\x6e\x67\x65\x73\x61\155\x6c" . DIRECTORY_SEPARATOR . "\163\141\155\154\62" . DIRECTORY_SEPARATOR . "\143\145\x72\164" . DIRECTORY_SEPARATOR . $C_;
        return $gn;
    }
    public static function get_public_private_certificate($fT, $Fv)
    {
        if (!isset($fT)) {
            goto VP;
        }
        foreach ($fT as $uf => $wZ) {
            foreach ($wZ as $QL => $Wm) {
                if (!($Fv == "\160\165\x62\x6c\x69\x63\x5f\143\145\162\x74\151\146\151\143\x61\164\145" && $QL == "\x70\165\142\154\151\143\137\x63\145\162\164\151\x66\151\143\x61\164\x65")) {
                    goto z4;
                }
                return $Wm;
                z4:
                if (!($Fv == "\160\162\151\166\141\x74\x65\x5f\143\x65\162\x74\x69\x66\151\x63\x61\164\145" && $QL == "\x70\162\x69\x76\x61\164\145\137\x63\x65\162\x74\151\x66\151\143\x61\164\x65")) {
                    goto bq;
                }
                return $Wm;
                bq:
                TS:
            }
            W2:
            bg:
        }
        EP:
        VP:
    }
    public static function updateActivationStatusForUser($nE)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $ss = array($jt->quoteName("\x61\143\x74\151\x76\x61\x74\x69\x6f\156") . "\40\75\40\x30", $jt->quoteName("\142\x6c\157\143\x6b") . "\x20\x3d\40\x30");
        $EC = array($jt->quoteName("\x75\x73\145\162\156\x61\155\x65") . "\40\x3d\x20" . $jt->quote($nE));
        $bH->update($jt->quoteName("\x23\137\137\165\163\x65\162\163"))->set($ss)->where($EC);
        $jt->setQuery($bH);
        $jt->execute();
    }
    public static function getDomainMapping()
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->select(array("\x69\144\160\x5f\145\x6e\x74\x69\164\171\137\x69\x64", "\144\157\x6d\x61\151\156\x5f\x6d\141\x70\x70\151\x6e\147"));
        $bH->from($jt->quoteName("\x23\137\137\x6d\151\156\x69\x6f\162\141\156\147\145\x5f\x73\141\x6d\154\137\143\x6f\156\x66\151\x67"));
        $jt->setQuery($bH);
        $hf = $jt->loadAssocList();
        return $hf;
    }
    public static function getConifById($xd)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->select("\x2a");
        $bH->from($jt->quoteName("\43\137\x5f\x6d\x69\156\x69\x6f\x72\x61\156\147\x65\x5f\x73\x61\155\154\137\143\157\156\x66\x69\x67"));
        $bH->where($jt->quoteName("\x69\144") . "\x20\75\40" . $jt->quote($xd));
        $jt->setQuery($bH);
        $fT = $jt->loadAssoc();
        return $fT;
    }
    public static function isCustomerRegistered()
    {
        $wk = Mo_Saml_Local_Util::getCustomerDetails();
        $D1 = $wk["\x73\164\x61\x74\x75\x73"];
        if (!Mo_Saml_Local_Util::is_customer_registered() || Mo_Saml_Local_Util::check($D1) != "\x74\x72\165\145") {
            goto y2;
        }
        return $C3 = '';
        goto yk;
        y2:
        return $C3 = "\144\151\163\141\x62\x6c\145\x64";
        yk:
    }
    public static function createUpdateUrl($aZ, $et, $fZ, $iL, $ga, $Dy, $T7)
    {
        $g6 = "\61\x31\61\x31\61\61\x31\61\61\x31\61\61\x31\x31\61\x31" . $aZ;
        $vl = $Dy . "\x2f\x6d\157\141\x73\57\x61\x70\x69\x2f\x70\x6c\165\147\151\x6e\x2f\x64\162\x75\160\x61\154\x4a\157\x6f\155\154\141\125\160\x64\x61\164\x65\57" . $ga . "\57" . $et . "\x2f" . $fZ . "\x2f";
        $je = openssl_cipher_iv_length($cf = "\x41\x45\x53\55\x31\x32\x38\x2d\103\102\x43");
        $PC = openssl_random_pseudo_bytes($je);
        $yA = openssl_encrypt($g6, $cf, $iL, $sw = OPENSSL_RAW_DATA, $PC);
        return $vl . str_replace(["\x2b", "\57", "\x3d"], ["\55", "\137", ''], base64_encode($yA)) . "\x2f" . $T7 . "\x2f\x6a\157\157\x6d\154\141";
    }
    public static function updateUpgardeUrlInDb($lH)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $ER[] = $jt->quoteName("\154\157\x63\x61\x74\151\157\156") . "\x20\75\40" . $jt->quote($lH);
        $zb = "\115\151\156\x69\x6f\162\x61\156\147\x65\x53\141\155\154\x53\123\x4f";
        $bH->update($jt->quoteName("\43\137\x5f\165\x70\x64\x61\164\x65\137\163\151\x74\145\x73"))->set($ER)->where($jt->quoteName("\156\141\155\x65") . "\x20\x3d\x20\47" . $zb . "\47");
        $jt->setQuery($bH);
        $jt->execute();
    }
    public static function getHostname()
    {
        return "\x68\x74\164\160\163\x3a\x2f\57\154\157\147\x69\x6e\56\x78\145\143\x75\x72\151\146\171\56\143\x6f\155";
    }
    public static function createAndUpdateUpgardeUrl()
    {
        if (!(in_array("\157\160\145\x6e\163\x73\x6c", get_loaded_extensions()) === FALSE)) {
            goto E6;
        }
        return;
        E6:
        $oc = self::getHostname();
        $wk = self::getCustomerDetails();
        if (!self::doWeHaveCorrectUpgardeUrl()) {
            goto ZU;
        }
        return;
        ZU:
        $lH = self::createUpdateUrl(self::decrypt($wk["\x73\x6d\x6c\x5f\154\x6b"], $wk["\x63\165\163\164\157\x6d\x65\x72\137\x74\x6f\153\x65\156"]), UtilitiesSAML::getLicensePlanName(), "\x4a\117\117\x4d\114\101\137\123\101\115\x4c\137\x53\x50\x5f\105\116\124\105\x52\120\x52\x49\x53\105\x5f\x50\x4c\125\107\111\116", $wk["\141\160\151\137\153\x65\x79"], $wk["\143\165\163\164\157\155\145\x72\x5f\x6b\x65\x79"], $oc, "\143\157\x6d\137\x6d\x69\156\x69\x6f\x72\141\x6e\147\x65\x5f\x73\x61\155\x6c");
        self::updateUpgardeUrlInDb($lH);
    }
    public static function doWeHaveCorrectUpgardeUrl()
    {
        $zb = "\x4d\x69\x6e\x69\x6f\x72\141\156\x67\x65\x53\141\x6d\x6c\123\x53\x4f";
        $Vr = "\x23\x5f\137\x75\x70\144\x61\x74\x65\x5f\x73\x69\x74\x65\x73";
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->select("\52");
        $bH->from($jt->quoteName($Vr));
        $bH->where($jt->quoteName("\156\x61\x6d\145") . "\40\75\40\x27" . $zb . "\x27");
        $jt->setQuery($bH);
        $oZ = $jt->loadAssocList();
        foreach ($oZ as $uf => $wZ) {
            if (!(stristr($wZ["\154\x6f\143\x61\x74\x69\x6f\x6e"], "\144\x72\165\160\x61\154\112\157\157\x6d\154\141\x55\160\144\141\x74\145") !== FALSE)) {
                goto dd;
            }
            return TRUE;
            dd:
            jC:
        }
        Jo:
        return FALSE;
    }
    public static function decrypt($cf, $uf)
    {
        $Hl = rtrim(openssl_decrypt(base64_decode($cf), "\x61\x65\x73\x2d\61\x32\70\x2d\x65\x63\142", $uf, OPENSSL_RAW_DATA), "\0");
        return trim($Hl, "\0\56\x2e\x1a");
    }
    public static function GetPluginVersion()
    {
        $jt = JFactory::getDbo();
        $b3 = $jt->getQuery(true)->select("\155\141\156\151\146\x65\163\x74\137\x63\141\143\x68\145")->from($jt->quoteName("\x23\x5f\x5f\145\x78\x74\145\156\163\151\x6f\156\163"))->where($jt->quoteName("\145\x6c\145\155\145\156\164") . "\x20\75\40" . $jt->quote("\x63\157\x6d\137\155\151\x6e\151\157\162\141\x6e\x67\x65\x5f\163\x61\x6d\x6c"));
        $jt->setQuery($b3);
        $FX = json_decode($jt->loadResult());
        return $FX->version;
    }
    public static function getPluginConfigurations($xd)
    {
        $jt = JFactory::getDbo();
        $bH = $jt->getQuery(true);
        $bH->select("\x2a");
        $bH->from($jt->quoteName("\43\137\x5f\155\x69\x6e\151\x6f\x72\x61\x6e\147\x65\137\x73\x61\x6d\154\137\x63\157\x6e\x66\x69\147"));
        $EC = array($jt->quoteName("\x69\144") . "\x20\75\40" . $jt->quote($xd));
        $bH->where($EC);
        $jt->setQuery($bH);
        $hf = $jt->loadAssocList();
        return $hf;
    }
}
