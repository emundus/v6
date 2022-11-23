<?php


include_once "\x41\163\x73\x65\162\x74\x69\157\x6e\x2e\160\x68\x70";
class SAML2_Response
{
    private $assertions;
    private $destination;
    private $certificates;
    private $signatureData;
    public function __construct(DOMElement $ZQ = NULL)
    {
        $this->assertions = array();
        $this->certificates = array();
        if (!($ZQ === NULL)) {
            goto RQ;
        }
        return;
        RQ:
        $AM = UtilitiesSAML::validateElement($ZQ);
        if (!($AM !== FALSE)) {
            goto CU;
        }
        $this->certificates = $AM["\x43\145\162\x74\151\146\151\143\141\164\x65\x73"];
        $this->signatureData = $AM;
        CU:
        if (!$ZQ->hasAttribute("\104\145\163\x74\151\156\x61\164\x69\157\156")) {
            goto Jx;
        }
        $this->destination = $ZQ->getAttribute("\104\x65\163\x74\x69\x6e\141\x74\151\157\156");
        Jx:
        $Il = $ZQ->firstChild;
        lK:
        if (!($Il !== NULL)) {
            goto BK;
        }
        if (!($Il->namespaceURI !== "\x75\x72\x6e\x3a\x6f\x61\163\151\163\x3a\156\141\x6d\145\163\72\x74\x63\72\123\101\115\x4c\72\x32\x2e\x30\x3a\141\163\x73\145\x72\x74\x69\x6f\156")) {
            goto c8;
        }
        goto Pv;
        c8:
        if (!($Il->localName === "\101\163\x73\145\x72\x74\x69\157\x6e" || $Il->localName === "\x45\156\x63\162\171\160\164\145\144\101\163\x73\145\162\x74\151\x6f\x6e")) {
            goto Rv;
        }
        $this->assertions[] = new SAML2_Assertion($Il);
        Rv:
        Pv:
        $Il = $Il->nextSibling;
        goto lK;
        BK:
    }
    public function getAssertions()
    {
        return $this->assertions;
    }
    public function setAssertions(array $Pf)
    {
        $this->assertions = $Pf;
    }
    public function getDestination()
    {
        return $this->destination;
    }
    public function getCertificates()
    {
        return $this->certificates;
    }
    public function getSignatureData()
    {
        return $this->signatureData;
    }
}
