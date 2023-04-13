<?php


include_once "\x41\163\163\145\162\164\x69\x6f\x6e\56\x70\x68\x70";
class SAML2_Response
{
    private $assertions;
    private $destination;
    private $certificates;
    private $signatureData;
    public function __construct(DOMElement $XT = NULL)
    {
        $this->assertions = array();
        $this->certificates = array();
        if (!($XT === NULL)) {
            goto Oz;
        }
        return;
        Oz:
        $SD = UtilitiesSAML::validateElement($XT);
        if (!($SD !== FALSE)) {
            goto uv;
        }
        $this->certificates = $SD["\103\x65\162\x74\151\x66\x69\143\141\x74\145\x73"];
        $this->signatureData = $SD;
        uv:
        if (!$XT->hasAttribute("\104\145\163\164\x69\156\141\164\x69\x6f\x6e")) {
            goto y8;
        }
        $this->destination = $XT->getAttribute("\x44\145\x73\164\x69\x6e\141\164\151\x6f\156");
        y8:
        $mj = $XT->firstChild;
        tI:
        if (!($mj !== NULL)) {
            goto U8;
        }
        if (!($mj->namespaceURI !== "\x75\162\x6e\x3a\x6f\x61\163\151\x73\x3a\156\x61\155\145\x73\x3a\164\x63\72\123\x41\115\x4c\x3a\x32\x2e\x30\72\x61\x73\163\145\x72\164\151\157\x6e")) {
            goto tj;
        }
        goto fu;
        tj:
        if (!($mj->localName === "\101\163\163\x65\x72\x74\x69\x6f\156" || $mj->localName === "\x45\156\143\162\x79\x70\164\145\x64\x41\163\x73\x65\x72\164\151\x6f\156")) {
            goto RG;
        }
        $this->assertions[] = new SAML2_Assertion($mj);
        RG:
        fu:
        $mj = $mj->nextSibling;
        goto tI;
        U8:
    }
    public function getAssertions()
    {
        $pd = isset($this->assertions) ? $this->assertions : '';
        if (empty($pd)) {
            goto dv;
        }
        return $pd;
        goto EY;
        dv:
        echo "\122\x45\106\72\x20\112\60\101\x3a\40\x53\x6f\155\x65\x74\x68\151\156\147\40\x77\x65\156\164\x20\x77\x72\157\156\x67\x20\164\157\x20\x6c\157\147\151\x6e\x2e\40\x50\154\x65\x61\163\x65\x20\x63\157\156\164\141\143\x74\x20\171\x6f\x75\x72\x20\141\144\x6d\151\156\151\x73\x74\162\x61\x74\157\x72";
        exit;
        EY:
    }
    public function setAssertions(array $pd)
    {
        $this->assertions = $pd;
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
