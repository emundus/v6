<?php


defined("\137\x4a\x45\130\x45\x43") or die;
class IDPMetadataReader
{
    private $identityProviders;
    private $serviceProviders;
    public function __construct(DOMNode $lL = NULL)
    {
        $this->identityProviders = array();
        $this->serviceProviders = array();
        $Kv = UtilitiesSAML::xpQuery($lL, "\x2e\57\x73\x61\x6d\154\x5f\155\145\x74\x61\x64\141\164\x61\x3a\105\156\x74\x69\x74\x79\104\145\x73\143\x72\151\160\x74\157\x72");
        foreach ($Kv as $AD) {
            $kA = UtilitiesSAML::xpQuery($AD, "\x2e\x2f\x73\x61\155\x6c\137\155\145\x74\x61\144\x61\164\141\72\x49\104\x50\x53\x53\117\x44\x65\x73\x63\x72\151\x70\x74\x6f\162");
            if (!(isset($kA) && !empty($kA))) {
                goto vD;
            }
            array_push($this->identityProviders, new IdentityProviders($AD));
            vD:
            YQ:
        }
        Cb:
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
    public function __construct(DOMElement $lL = NULL)
    {
        $this->idpName = '';
        $this->loginDetails = array();
        $this->logoutDetails = array();
        $this->signingCertificate = array();
        $this->encryptionCertificate = array();
        if (!$lL->hasAttribute("\x65\156\164\151\x74\171\x49\104")) {
            goto bN;
        }
        $this->entityID = $lL->getAttribute("\x65\156\x74\x69\164\171\111\x44");
        bN:
        if (!$lL->hasAttribute("\127\x61\x6e\x74\101\165\164\150\x6e\x52\x65\x71\x75\145\x73\x74\163\x53\151\x67\156\x65\144")) {
            goto iW;
        }
        $this->signedRequest = $lL->getAttribute("\127\x61\x6e\164\x41\x75\164\150\x6e\x52\145\161\x75\x65\163\164\x73\x53\151\147\x6e\145\x64");
        iW:
        $kA = UtilitiesSAML::xpQuery($lL, "\x2e\x2f\x73\141\155\x6c\137\155\x65\x74\x61\144\x61\x74\141\72\x49\x44\x50\x53\x53\x4f\x44\x65\x73\x63\162\151\160\164\157\162");
        if (count($kA) > 1) {
            goto W6;
        }
        if (empty($kA)) {
            goto le;
        }
        goto fI;
        W6:
        throw new Exception("\115\x6f\162\x65\40\164\x68\x61\x6e\x20\157\156\145\x20\74\111\104\120\x53\x53\117\x44\x65\x73\x63\162\x69\x70\164\157\x72\x3e\x20\x69\156\40\74\105\156\x74\151\x74\x79\104\145\163\x63\x72\x69\160\164\157\162\76\56");
        goto fI;
        le:
        throw new Exception("\115\151\163\x73\151\x6e\147\40\162\x65\x71\165\x69\162\145\144\40\x3c\x49\104\120\x53\x53\x4f\104\145\163\143\x72\151\x70\x74\157\162\76\40\x69\156\40\x3c\105\156\x74\x69\164\171\x44\145\x73\x63\162\x69\x70\164\x6f\162\x3e\56");
        fI:
        $T5 = $kA[0];
        $oz = UtilitiesSAML::xpQuery($lL, "\56\57\163\141\x6d\x6c\137\155\145\x74\x61\144\x61\164\141\x3a\105\170\x74\x65\x6e\163\151\157\x6e\163");
        if (!$oz) {
            goto B4;
        }
        $this->parseInfo($T5);
        B4:
        $this->parseSSOService($T5);
        $this->parseSLOService($T5);
        $this->parsex509Certificate($T5);
    }
    private function parseInfo($lL)
    {
        $y4 = UtilitiesSAML::xpQuery($lL, "\56\57\x6d\x64\165\x69\x3a\x55\111\x49\x6e\x66\157\57\155\x64\x75\x69\72\x44\151\x73\x70\x6c\x61\171\x4e\141\x6d\145");
        foreach ($y4 as $F6) {
            if (!($F6->hasAttribute("\170\x6d\154\x3a\x6c\141\x6e\147") && $F6->getAttribute("\170\x6d\154\x3a\x6c\141\x6e\147") == "\145\156")) {
                goto e_;
            }
            $this->idpName = $F6->textContent;
            e_:
            x9:
        }
        jH:
    }
    private function parseSSOService($lL)
    {
        $fI = UtilitiesSAML::xpQuery($lL, "\x2e\57\x73\x61\x6d\x6c\137\x6d\145\x74\141\x64\141\x74\141\x3a\x53\151\156\x67\154\145\123\151\x67\156\x4f\156\123\145\162\x76\x69\x63\145");
        foreach ($fI as $kX) {
            $GQ = str_replace("\165\x72\x6e\x3a\157\141\x73\x69\163\72\156\141\x6d\145\x73\72\164\143\x3a\123\x41\115\114\x3a\62\x2e\60\x3a\x62\x69\156\x64\x69\156\x67\163\x3a", '', $kX->getAttribute("\x42\x69\156\144\151\x6e\147"));
            $this->loginDetails = array_merge($this->loginDetails, array($GQ => $kX->getAttribute("\114\x6f\x63\x61\164\x69\x6f\156")));
            zt:
        }
        N2:
    }
    private function parseSLOService($lL)
    {
        $h3 = UtilitiesSAML::xpQuery($lL, "\56\57\163\141\155\x6c\137\155\145\164\x61\144\x61\x74\141\72\x53\x69\156\x67\x6c\145\114\157\x67\157\x75\164\x53\x65\162\166\151\x63\x65");
        foreach ($h3 as $zM) {
            $GQ = str_replace("\165\x72\x6e\x3a\x6f\141\x73\x69\x73\x3a\x6e\x61\x6d\x65\163\72\x74\143\72\x53\101\x4d\114\x3a\62\56\60\x3a\x62\x69\x6e\x64\151\156\147\x73\x3a", '', $zM->getAttribute("\x42\151\156\x64\x69\156\147"));
            $this->logoutDetails = array_merge($this->logoutDetails, array($GQ => $zM->getAttribute("\114\x6f\143\x61\164\151\157\156")));
            ek:
        }
        t1:
    }
    private function parsex509Certificate($lL)
    {
        foreach (UtilitiesSAML::xpQuery($lL, "\56\57\163\x61\155\154\137\155\x65\x74\x61\x64\141\164\x61\72\113\145\x79\x44\x65\x73\143\162\x69\160\164\157\x72") as $JE) {
            if ($JE->hasAttribute("\165\x73\145")) {
                goto Xz;
            }
            $this->parseSigningCertificate($JE);
            goto xn;
            Xz:
            if ($JE->getAttribute("\x75\x73\x65") == "\145\156\x63\x72\171\x70\x74\x69\157\156") {
                goto yg;
            }
            $this->parseSigningCertificate($JE);
            goto HM;
            yg:
            $this->parseEncryptionCertificate($JE);
            HM:
            xn:
            hw:
        }
        ND:
    }
    private function parseSigningCertificate($lL)
    {
        $i5 = UtilitiesSAML::xpQuery($lL, "\x2e\57\144\x73\x3a\113\x65\x79\x49\x6e\146\157\x2f\144\x73\x3a\130\x35\60\x39\104\x61\x74\141\x2f\x64\163\72\130\65\60\71\103\145\162\164\x69\x66\x69\x63\x61\x74\145");
        $ZQ = trim($i5[0]->textContent);
        $ZQ = str_replace(array("\xd", "\xa", "\11", "\x20"), '', $ZQ);
        if (empty($i5)) {
            goto HH;
        }
        array_push($this->signingCertificate, UtilitiesSAML::sanitize_certificate($ZQ));
        HH:
    }
    private function parseEncryptionCertificate($lL)
    {
        $i5 = UtilitiesSAML::xpQuery($lL, "\x2e\57\144\x73\x3a\x4b\x65\171\x49\x6e\x66\157\57\x64\163\x3a\130\x35\x30\71\104\141\x74\141\57\x64\x73\x3a\x58\x35\60\71\103\x65\162\164\151\x66\x69\143\x61\164\x65");
        $ZQ = trim($i5[0]->textContent);
        $ZQ = str_replace(array("\15", "\xa", "\x9", "\40"), '', $ZQ);
        if (empty($i5)) {
            goto A5;
        }
        array_push($this->encryptionCertificate, $ZQ);
        A5:
    }
    public function getIdpName()
    {
        return '';
    }
    public function getEntityID()
    {
        return $this->entityID;
    }
    public function getLoginURL($GQ)
    {
        return $this->loginDetails[$GQ];
    }
    public function getLogoutURL($GQ)
    {
        return isset($this->logoutDetails[$GQ]) ? $this->logoutDetails[$GQ] : '';
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
