<?php


class SAML2_LogoutRequest
{
    private $tagName;
    private $id;
    private $issuer;
    private $destination;
    private $issueInstant;
    private $certificates;
    private $validators;
    private $notOnOrAfter;
    private $encryptedNameId;
    private $nameId;
    private $sessionIndexes;
    public function __construct(DOMElement $E0 = NULL)
    {
        $this->tagName = "\114\157\x67\x6f\x75\x74\x52\145\x71\x75\145\163\x74";
        $this->id = UtilitiesSAML::generateID();
        $this->issueInstant = time();
        $this->certificates = array();
        $this->validators = array();
        if (!($E0 === NULL)) {
            goto Ss;
        }
        return;
        Ss:
        if ($E0->hasAttribute("\x49\x44")) {
            goto lI;
        }
        throw new Exception("\x4d\151\163\163\x69\x6e\147\x20\111\104\40\141\164\164\x72\x69\x62\165\164\145\x20\157\156\x20\123\x41\115\x4c\40\x6d\145\163\x73\x61\147\145\x2e");
        lI:
        $this->id = $E0->getAttribute("\111\104");
        if (!($E0->getAttribute("\x56\x65\162\163\151\x6f\x6e") !== "\62\x2e\x30")) {
            goto Px;
        }
        throw new Exception("\125\x6e\163\165\x70\160\157\x72\164\145\144\40\x76\x65\x72\x73\151\x6f\x6e\72\40" . $E0->getAttribute("\x56\145\x72\163\x69\x6f\x6e"));
        Px:
        $this->issueInstant = UtilitiesSAML::xsDateTimeToTimestamp($E0->getAttribute("\x49\x73\163\165\145\111\x6e\163\x74\141\156\x74"));
        if (!$E0->hasAttribute("\104\145\x73\164\x69\156\141\x74\151\157\156")) {
            goto bQ;
        }
        $this->destination = $E0->getAttribute("\104\x65\163\164\x69\x6e\141\x74\x69\157\x6e");
        bQ:
        $uT = UtilitiesSAML::xpQuery($E0, "\56\57\163\x61\x6d\154\137\141\163\x73\x65\x72\164\x69\x6f\156\x3a\x49\163\163\x75\145\x72");
        if (empty($uT)) {
            goto SA;
        }
        $this->issuer = trim($uT[0]->textContent);
        SA:
        try {
            $v_ = UtilitiesSAML::validateElement($E0);
            if (!($v_ !== FALSE)) {
                goto Tz;
            }
            $this->certificates = $v_["\x43\145\162\x74\x69\x66\x69\x63\x61\164\145\163"];
            $this->validators[] = array("\106\x75\x6e\143\164\x69\157\x6e" => array("\x55\164\x69\x6c\151\164\151\145\163\123\x41\x4d\114", "\166\141\x6c\151\144\141\164\145\x53\x69\147\x6e\141\x74\165\162\x65"), "\x44\x61\164\x61" => $v_);
            Tz:
        } catch (Exception $ia) {
        }
        $this->sessionIndexes = array();
        if (!$E0->hasAttribute("\x4e\x6f\x74\x4f\156\117\x72\101\x66\x74\145\x72")) {
            goto IS;
        }
        $this->notOnOrAfter = UtilitiesSAML::xsDateTimeToTimestamp($E0->getAttribute("\x4e\157\x74\x4f\156\117\162\101\x66\x74\x65\162"));
        IS:
        $gD = UtilitiesSAML::xpQuery($E0, "\56\57\163\x61\155\154\137\141\x73\163\x65\162\x74\151\x6f\156\72\116\141\155\145\111\104\40\174\x20\x2e\57\x73\141\x6d\154\x5f\141\x73\163\x65\x72\x74\x69\157\x6e\x3a\105\x6e\143\x72\x79\x70\164\145\144\x49\104\57\170\x65\x6e\x63\72\105\156\x63\162\171\x70\x74\145\144\104\x61\x74\141");
        if (empty($gD)) {
            goto b9;
        }
        if (count($gD) > 1) {
            goto XZ;
        }
        goto fY;
        b9:
        throw new Exception("\x4d\151\x73\x73\151\156\147\x20\74\x73\x61\x6d\x6c\72\116\x61\x6d\145\x49\x44\x3e\40\157\162\x20\74\x73\x61\155\154\x3a\105\x6e\143\x72\171\160\164\x65\144\x49\104\x3e\x20\151\x6e\40\x3c\x73\x61\x6d\154\160\72\x4c\x6f\147\157\x75\164\122\x65\x71\165\x65\163\164\76\56");
        goto fY;
        XZ:
        throw new Exception("\115\157\162\x65\x20\x74\x68\x61\156\40\157\x6e\145\40\x3c\x73\141\x6d\x6c\72\116\x61\x6d\145\111\x44\76\x20\x6f\162\x20\x3c\163\x61\x6d\x6c\x3a\x45\x6e\143\162\171\160\164\145\x64\104\x3e\x20\151\x6e\x20\74\163\141\155\x6c\160\x3a\114\x6f\x67\157\165\164\122\x65\x71\165\145\x73\164\76\x2e");
        fY:
        $gD = $gD[0];
        if ($gD->localName === "\x45\156\143\x72\171\x70\x74\145\144\104\141\x74\x61") {
            goto l3;
        }
        $this->nameId = UtilitiesSAML::parseNameId($gD);
        goto K2;
        l3:
        $this->encryptedNameId = $gD;
        K2:
        $Dx = UtilitiesSAML::xpQuery($E0, "\x2e\57\x73\x61\x6d\x6c\x5f\x70\x72\157\164\x6f\x63\157\x6c\x3a\123\145\x73\163\151\x6f\156\x49\156\x64\145\x78");
        foreach ($Dx as $U_) {
            $this->sessionIndexes[] = trim($U_->textContent);
            FH:
        }
        WW:
    }
    public function getNotOnOrAfter()
    {
        return $this->notOnOrAfter;
    }
    public function setNotOnOrAfter($DH)
    {
        $this->notOnOrAfter = $DH;
    }
    public function isNameIdEncrypted()
    {
        if (!($this->encryptedNameId !== NULL)) {
            goto jK;
        }
        return TRUE;
        jK:
        return FALSE;
    }
    public function encryptNameId(XMLSecurityKeySAML $ep)
    {
        $FE = new DOMDocument();
        $Mw = $FE->createElement("\162\157\157\164");
        $FE->appendChild($Mw);
        SAML2_Utils::addNameId($Mw, $this->nameId);
        $gD = $Mw->firstChild;
        SAML2_Utils::getContainer()->debugMessage($gD, "\x65\156\x63\x72\171\x70\164");
        $D0 = new XMLSecEncSAML();
        $D0->setNode($gD);
        $D0->type = XMLSecEncSAML::Element;
        $aI = new XMLSecurityKeySAML(XMLSecurityKeySAML::AES128_CBC);
        $aI->generateSessionKey();
        $D0->encryptKey($ep, $aI);
        $this->encryptedNameId = $D0->encryptNode($aI);
        $this->nameId = NULL;
    }
    public function decryptNameId(XMLSecurityKeySAML $ep, array $Q2 = array())
    {
        if (!($this->encryptedNameId === NULL)) {
            goto f6;
        }
        return;
        f6:
        $gD = SAML2_Utils::decryptElement($this->encryptedNameId, $ep, $Q2);
        SAML2_Utils::getContainer()->debugMessage($gD, "\144\x65\x63\162\171\160\164");
        $this->nameId = SAML2_Utils::parseNameId($gD);
        $this->encryptedNameId = NULL;
    }
    public function getNameId()
    {
        if (!($this->encryptedNameId !== NULL)) {
            goto rq;
        }
        throw new Exception("\101\164\164\x65\155\160\164\x65\144\40\x74\x6f\x20\x72\x65\x74\x72\151\x65\166\x65\x20\x65\x6e\x63\x72\171\x70\164\x65\x64\x20\x4e\x61\x6d\145\x49\x44\x20\x77\151\x74\x68\157\x75\164\x20\x64\x65\143\162\x79\160\164\151\x6e\x67\40\x69\x74\x20\x66\151\x72\x73\164\x2e");
        rq:
        return $this->nameId;
    }
    public function setNameId($gD)
    {
        $this->nameId = $gD;
    }
    public function getSessionIndexes()
    {
        return $this->sessionIndexes;
    }
    public function setSessionIndexes(array $Dx)
    {
        $this->sessionIndexes = $Dx;
    }
    public function getSessionIndex()
    {
        if (!empty($this->sessionIndexes)) {
            goto vr;
        }
        return NULL;
        vr:
        return $this->sessionIndexes[0];
    }
    public function setSessionIndex($U_)
    {
        if (is_null($U_)) {
            goto l2;
        }
        $this->sessionIndexes = array($U_);
        goto ln;
        l2:
        $this->sessionIndexes = array();
        ln:
    }
    public function getId()
    {
        return $this->id;
    }
    public function setId($f9)
    {
        $this->id = $f9;
    }
    public function getIssueInstant()
    {
        return $this->issueInstant;
    }
    public function setIssueInstant($rZ)
    {
        $this->issueInstant = $rZ;
    }
    public function getDestination()
    {
        return $this->destination;
    }
    public function setDestination($yV)
    {
        $this->destination = $yV;
    }
    public function getIssuer()
    {
        return $this->issuer;
    }
    public function setIssuer($uT)
    {
        $this->issuer = $uT;
    }
}
