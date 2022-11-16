<?php


defined("\137\112\105\130\105\103") or die;
class IDPMetadataReader
{
    private $identityProviders;
    private $serviceProviders;
    public function __construct(DOMNode $UR = NULL)
    {
        $this->identityProviders = array();
        $this->serviceProviders = array();
        $dP = UtilitiesSAML::xpQuery($UR, "\56\57\x73\141\x6d\154\137\155\145\164\141\144\141\164\x61\72\105\156\x74\151\164\171\104\145\x73\x63\162\x69\x70\164\x6f\162");
        foreach ($dP as $H6) {
            $C1 = UtilitiesSAML::xpQuery($H6, "\56\x2f\163\141\155\x6c\137\x6d\x65\x74\141\144\x61\164\141\72\111\104\x50\123\123\117\x44\145\x73\x63\162\151\160\x74\157\x72");
            if (!(isset($C1) && !empty($C1))) {
                goto BQ;
            }
            array_push($this->identityProviders, new IdentityProviders($H6));
            BQ:
            uQ:
        }
        GT:
    }
    public function getIdentityProviders()
    {
        return $this->identityProviders;
    }
    public function getServiceProviders()
    {
        return $this->serviceProviders;
    }
}
class IdentityProviders
{
    private $idpName;
    private $entityID;
    private $loginDetails;
    private $logoutDetails;
    private $signingCertificate;
    private $encryptionCertificate;
    private $signedRequest;
    public function __construct(DOMElement $UR = NULL)
    {
        $this->idpName = '';
        $this->loginDetails = array();
        $this->logoutDetails = array();
        $this->signingCertificate = array();
        $this->encryptionCertificate = array();
        if (!$UR->hasAttribute("\x65\x6e\164\x69\x74\171\111\104")) {
            goto Dl;
        }
        $this->entityID = $UR->getAttribute("\x65\x6e\x74\151\164\x79\111\x44");
        Dl:
        if (!$UR->hasAttribute("\x57\141\156\x74\101\165\x74\x68\156\122\x65\161\165\145\163\164\x73\x53\151\147\156\x65\x64")) {
            goto GU;
        }
        $this->signedRequest = $UR->getAttribute("\127\x61\156\164\101\165\x74\x68\156\x52\145\x71\x75\x65\163\x74\x73\123\x69\x67\x6e\x65\x64");
        GU:
        $C1 = UtilitiesSAML::xpQuery($UR, "\x2e\57\163\141\155\x6c\x5f\x6d\x65\164\141\x64\x61\x74\141\72\111\x44\120\x53\x53\117\x44\145\x73\x63\x72\x69\x70\164\157\x72");
        if (count($C1) > 1) {
            goto Zb;
        }
        if (empty($C1)) {
            goto WT;
        }
        goto nQ;
        Zb:
        throw new Exception("\x4d\x6f\x72\x65\x20\164\x68\141\156\x20\157\x6e\x65\40\74\x49\x44\x50\123\x53\117\x44\x65\x73\x63\162\x69\x70\x74\x6f\162\x3e\40\x69\156\x20\x3c\105\x6e\164\x69\x74\171\104\145\x73\143\162\x69\160\x74\x6f\162\x3e\56");
        goto nQ;
        WT:
        throw new Exception("\x4d\151\163\x73\x69\x6e\147\x20\162\x65\161\165\151\162\x65\x64\40\74\x49\104\x50\x53\123\x4f\x44\145\x73\x63\162\151\160\164\157\x72\x3e\x20\x69\156\40\x3c\x45\x6e\x74\151\164\x79\x44\145\x73\143\162\x69\160\x74\157\162\x3e\x2e");
        nQ:
        $Dc = $C1[0];
        $MH = UtilitiesSAML::xpQuery($UR, "\x2e\57\163\x61\x6d\x6c\x5f\155\145\x74\x61\144\141\x74\x61\72\105\x78\x74\145\x6e\163\x69\157\x6e\163");
        if (!$MH) {
            goto mL;
        }
        $this->parseInfo($Dc);
        mL:
        $this->parseSSOService($Dc);
        $this->parseSLOService($Dc);
        $this->parsex509Certificate($Dc);
    }
    private function parseInfo($UR)
    {
        $Aq = UtilitiesSAML::xpQuery($UR, "\56\57\x6d\x64\x75\x69\72\125\111\111\x6e\x66\x6f\x2f\x6d\x64\165\151\x3a\104\x69\163\160\x6c\141\x79\116\x61\155\145");
        foreach ($Aq as $UC) {
            if (!($UC->hasAttribute("\170\155\154\x3a\154\x61\156\147") && $UC->getAttribute("\x78\155\x6c\72\154\141\x6e\x67") == "\145\156")) {
                goto Mu;
            }
            $this->idpName = $UC->textContent;
            Mu:
            Cf:
        }
        D4:
    }
    private function parseSSOService($UR)
    {
        $lH = UtilitiesSAML::xpQuery($UR, "\56\57\x73\x61\x6d\x6c\137\155\145\x74\141\x64\141\x74\x61\x3a\123\151\156\x67\x6c\x65\x53\151\x67\x6e\x4f\156\123\145\x72\166\x69\143\145");
        foreach ($lH as $F3) {
            $I2 = str_replace("\165\162\x6e\72\x6f\x61\x73\x69\x73\x3a\156\x61\x6d\x65\163\x3a\164\x63\72\x53\101\115\x4c\x3a\62\x2e\60\72\x62\151\156\x64\x69\156\147\x73\72", '', $F3->getAttribute("\102\151\156\144\x69\x6e\x67"));
            $this->loginDetails = array_merge($this->loginDetails, array($I2 => $F3->getAttribute("\x4c\x6f\x63\x61\x74\151\x6f\x6e")));
            g8:
        }
        GK:
    }
    private function parseSLOService($UR)
    {
        $Hj = UtilitiesSAML::xpQuery($UR, "\56\57\x73\141\x6d\154\x5f\155\x65\x74\x61\144\x61\164\x61\x3a\x53\151\156\x67\x6c\145\x4c\157\147\157\x75\x74\123\145\x72\x76\151\x63\x65");
        foreach ($Hj as $Y2) {
            $I2 = str_replace("\165\x72\156\x3a\x6f\x61\x73\x69\x73\72\x6e\141\155\x65\x73\x3a\x74\x63\72\123\101\115\x4c\x3a\x32\x2e\x30\72\x62\x69\156\x64\x69\156\147\x73\72", '', $Y2->getAttribute("\102\x69\156\x64\151\156\x67"));
            $this->logoutDetails = array_merge($this->logoutDetails, array($I2 => $Y2->getAttribute("\114\157\x63\x61\164\151\157\x6e")));
            eJ:
        }
        Mq:
    }
    private function parsex509Certificate($UR)
    {
        foreach (UtilitiesSAML::xpQuery($UR, "\56\x2f\163\141\155\x6c\137\x6d\145\x74\141\144\x61\x74\141\x3a\x4b\145\171\104\x65\163\143\x72\151\160\x74\157\x72") as $K_) {
            if ($K_->hasAttribute("\x75\x73\145")) {
                goto H2;
            }
            $this->parseSigningCertificate($K_);
            goto yG;
            H2:
            if ($K_->getAttribute("\165\x73\145") == "\145\156\x63\x72\171\160\164\x69\x6f\x6e") {
                goto ik;
            }
            $this->parseSigningCertificate($K_);
            goto IB;
            ik:
            $this->parseEncryptionCertificate($K_);
            IB:
            yG:
            qj:
        }
        Vj:
    }
    private function parseSigningCertificate($UR)
    {
        $kg = UtilitiesSAML::xpQuery($UR, "\x2e\57\144\x73\72\113\x65\x79\x49\x6e\x66\x6f\57\x64\x73\x3a\x58\x35\x30\x39\x44\x61\x74\141\57\x64\x73\72\x58\x35\60\x39\103\x65\x72\x74\151\x66\x69\143\141\x74\x65");
        $OG = trim($kg[0]->textContent);
        $OG = str_replace(array("\15", "\xa", "\x9", "\40"), '', $OG);
        if (empty($kg)) {
            goto MS;
        }
        array_push($this->signingCertificate, UtilitiesSAML::sanitize_certificate($OG));
        MS:
    }
    private function parseEncryptionCertificate($UR)
    {
        $kg = UtilitiesSAML::xpQuery($UR, "\56\x2f\x64\x73\72\113\x65\171\111\156\x66\x6f\57\144\163\x3a\130\x35\x30\x39\104\141\164\141\57\x64\x73\x3a\130\x35\x30\71\x43\x65\162\x74\x69\146\x69\143\141\164\x65");
        $OG = trim($kg[0]->textContent);
        $OG = str_replace(array("\15", "\12", "\11", "\x20"), '', $OG);
        if (empty($kg)) {
            goto KT;
        }
        array_push($this->encryptionCertificate, $OG);
        KT:
    }
    public function getIdpName()
    {
        return '';
    }
    public function getEntityID()
    {
        return $this->entityID;
    }
    public function getLoginURL($I2)
    {
        return $this->loginDetails[$I2];
    }
    public function getLogoutURL($I2)
    {
        return isset($this->logoutDetails[$I2]) ? $this->logoutDetails[$I2] : '';
    }
    public function getLoginDetails()
    {
        return $this->loginDetails;
    }
    public function getLogoutDetails()
    {
        return $this->logoutDetails;
    }
    public function getSigningCertificate()
    {
        return $this->signingCertificate;
    }
    public function getEncryptionCertificate()
    {
        return $this->encryptionCertificate[0];
    }
    public function isRequestSigned()
    {
        return $this->signedRequest;
    }
}
class ServiceProviders
{
}
