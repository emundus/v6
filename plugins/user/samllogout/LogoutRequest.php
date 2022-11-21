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
    public function __construct(DOMElement $XJ = NULL)
    {
        $this->tagName = "\114\157\147\157\165\x74\122\145\x71\165\145\x73\x74";
        $this->id = UtilitiesSAML::generateID();
        $this->issueInstant = time();
        $this->certificates = array();
        $this->validators = array();
        if (!($XJ === NULL)) {
            goto eH;
        }
        return;
        eH:
        if ($XJ->hasAttribute("\111\104")) {
            goto M0;
        }
        throw new Exception("\x4d\151\163\x73\151\156\x67\x20\x49\x44\40\141\164\x74\x72\x69\x62\x75\164\145\x20\x6f\x6e\40\x53\x41\115\114\x20\x6d\145\163\163\141\x67\x65\56");
        M0:
        $this->id = $XJ->getAttribute("\x49\x44");
        if (!($XJ->getAttribute("\126\145\162\x73\151\157\x6e") !== "\x32\x2e\60")) {
            goto Ll;
        }
        throw new Exception("\x55\x6e\163\x75\x70\160\x6f\162\x74\145\x64\40\x76\x65\x72\x73\151\x6f\x6e\72\40" . $XJ->getAttribute("\126\145\x72\x73\151\x6f\156"));
        Ll:
        $this->issueInstant = UtilitiesSAML::xsDateTimeToTimestamp($XJ->getAttribute("\x49\163\x73\x75\145\111\x6e\163\x74\141\156\x74"));
        if (!$XJ->hasAttribute("\x44\145\163\164\151\156\141\164\151\x6f\x6e")) {
            goto Z4;
        }
        $this->destination = $XJ->getAttribute("\x44\x65\x73\164\151\156\x61\164\151\x6f\156");
        Z4:
        $EI = UtilitiesSAML::xpQuery($XJ, "\x2e\x2f\163\x61\155\x6c\x5f\x61\163\x73\145\x72\164\151\157\156\72\111\163\x73\165\x65\x72");
        if (empty($EI)) {
            goto hF;
        }
        $this->issuer = trim($EI[0]->textContent);
        hF:
        try {
            $bZ = UtilitiesSAML::validateElement($XJ);
            if (!($bZ !== FALSE)) {
                goto JT;
            }
            $this->certificates = $bZ["\103\x65\162\x74\151\x66\x69\143\141\164\145\x73"];
            $this->validators[] = array("\x46\165\x6e\143\x74\x69\157\156" => array("\125\x74\151\x6c\x69\164\x69\145\x73\123\101\115\114", "\166\x61\x6c\151\x64\x61\164\x65\123\151\x67\x6e\141\x74\165\162\x65"), "\104\141\x74\x61" => $bZ);
            JT:
        } catch (Exception $pI) {
        }
        $this->sessionIndexes = array();
        if (!$XJ->hasAttribute("\x4e\x6f\164\x4f\156\117\x72\101\146\x74\x65\x72")) {
            goto dk;
        }
        $this->notOnOrAfter = UtilitiesSAML::xsDateTimeToTimestamp($XJ->getAttribute("\116\x6f\164\x4f\x6e\117\x72\101\x66\x74\145\162"));
        dk:
        $IS = UtilitiesSAML::xpQuery($XJ, "\x2e\57\163\x61\x6d\154\x5f\x61\163\163\145\x72\164\x69\157\x6e\x3a\x4e\141\155\145\x49\104\40\x7c\x20\x2e\x2f\163\141\155\154\x5f\x61\163\x73\145\x72\x74\x69\157\x6e\72\x45\x6e\x63\162\x79\x70\x74\145\x64\111\x44\x2f\x78\x65\x6e\x63\72\105\156\x63\162\171\x70\x74\x65\144\x44\141\x74\141");
        if (empty($IS)) {
            goto lR;
        }
        if (count($IS) > 1) {
            goto WJ;
        }
        goto jF;
        lR:
        throw new Exception("\x4d\x69\163\163\151\156\147\40\74\163\x61\x6d\154\72\x4e\x61\x6d\145\111\104\76\x20\157\162\x20\x3c\x73\x61\155\154\72\105\156\x63\x72\x79\x70\164\x65\x64\x49\104\76\x20\x69\x6e\x20\74\x73\141\155\x6c\x70\x3a\114\157\147\x6f\x75\164\122\x65\161\x75\x65\163\x74\76\56");
        goto jF;
        WJ:
        throw new Exception("\115\157\162\x65\x20\164\x68\141\156\40\157\156\145\x20\74\x73\x61\155\x6c\72\x4e\x61\x6d\x65\x49\104\x3e\40\x6f\162\x20\74\x73\x61\x6d\154\x3a\105\156\143\162\x79\x70\164\x65\x64\104\76\40\151\156\x20\x3c\163\141\x6d\154\160\x3a\114\x6f\x67\x6f\165\164\122\145\161\x75\145\x73\164\x3e\56");
        jF:
        $IS = $IS[0];
        if ($IS->localName === "\105\156\143\x72\x79\x70\164\x65\x64\x44\141\x74\x61") {
            goto O_;
        }
        $this->nameId = UtilitiesSAML::parseNameId($IS);
        goto WK;
        O_:
        $this->encryptedNameId = $IS;
        WK:
        $OR = UtilitiesSAML::xpQuery($XJ, "\x2e\57\x73\x61\155\154\137\160\162\157\x74\x6f\143\157\x6c\72\x53\145\x73\163\x69\157\x6e\x49\x6e\144\x65\x78");
        foreach ($OR as $EB) {
            $this->sessionIndexes[] = trim($EB->textContent);
            W8:
        }
        Gk:
    }
    public function getNotOnOrAfter()
    {
        return $this->notOnOrAfter;
    }
    public function setNotOnOrAfter($Lj)
    {
        $this->notOnOrAfter = $Lj;
    }
    public function isNameIdEncrypted()
    {
        if (!($this->encryptedNameId !== NULL)) {
            goto an;
        }
        return TRUE;
        an:
        return FALSE;
    }
    public function encryptNameId(XMLSecurityKeySAML $Gw)
    {
        $B8 = new DOMDocument();
        $xO = $B8->createElement("\x72\x6f\x6f\164");
        $B8->appendChild($xO);
        SAML2_Utils::addNameId($xO, $this->nameId);
        $IS = $xO->firstChild;
        SAML2_Utils::getContainer()->debugMessage($IS, "\x65\156\143\x72\x79\x70\164");
        $pb = new XMLSecEncSAML();
        $pb->setNode($IS);
        $pb->type = XMLSecEncSAML::Element;
        $R9 = new XMLSecurityKeySAML(XMLSecurityKeySAML::AES128_CBC);
        $R9->generateSessionKey();
        $pb->encryptKey($Gw, $R9);
        $this->encryptedNameId = $pb->encryptNode($R9);
        $this->nameId = NULL;
    }
    public function decryptNameId(XMLSecurityKeySAML $Gw, array $ZE = array())
    {
        if (!($this->encryptedNameId === NULL)) {
            goto U_;
        }
        return;
        U_:
        $IS = SAML2_Utils::decryptElement($this->encryptedNameId, $Gw, $ZE);
        SAML2_Utils::getContainer()->debugMessage($IS, "\144\x65\143\x72\x79\x70\164");
        $this->nameId = SAML2_Utils::parseNameId($IS);
        $this->encryptedNameId = NULL;
    }
    public function getNameId()
    {
        if (!($this->encryptedNameId !== NULL)) {
            goto Dy;
        }
        throw new Exception("\101\164\x74\145\155\x70\x74\x65\144\40\x74\157\40\162\145\164\x72\x69\x65\166\x65\x20\145\x6e\x63\162\171\x70\x74\x65\x64\40\116\141\155\x65\111\x44\40\167\x69\x74\x68\157\x75\x74\x20\144\145\x63\x72\171\160\164\x69\156\x67\40\x69\164\x20\146\151\x72\163\164\x2e");
        Dy:
        return $this->nameId;
    }
    public function setNameId($IS)
    {
        $this->nameId = $IS;
    }
    public function getSessionIndexes()
    {
        return $this->sessionIndexes;
    }
    public function setSessionIndexes(array $OR)
    {
        $this->sessionIndexes = $OR;
    }
    public function getSessionIndex()
    {
        if (!empty($this->sessionIndexes)) {
            goto Ep;
        }
        return NULL;
        Ep:
        return $this->sessionIndexes[0];
    }
    public function setSessionIndex($EB)
    {
        if (is_null($EB)) {
            goto ZR;
        }
        $this->sessionIndexes = array($EB);
        goto Vj;
        ZR:
        $this->sessionIndexes = array();
        Vj:
    }
    public function getId()
    {
        return $this->id;
    }
    public function setId($TG)
    {
        $this->id = $TG;
    }
    public function getIssueInstant()
    {
        return $this->issueInstant;
    }
    public function setIssueInstant($QI)
    {
        $this->issueInstant = $QI;
    }
    public function getDestination()
    {
        return $this->destination;
    }
    public function setDestination($rD)
    {
        $this->destination = $rD;
    }
    public function getIssuer()
    {
        return $this->issuer;
    }
    public function setIssuer($EI)
    {
        $this->issuer = $EI;
    }
}
