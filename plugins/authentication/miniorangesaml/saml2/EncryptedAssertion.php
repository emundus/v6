<?php


class SAML2_EncryptedAssertion
{
    private $encryptedData;
    public function __construct(DOMElement $ZQ = NULL)
    {
        if (!($ZQ === NULL)) {
            goto FS;
        }
        return;
        FS:
        $Vr = UtilitiesSAML::xpQuery($ZQ, "\x2e\57\x78\x65\156\x63\72\105\156\143\x72\171\160\164\x65\144\x44\141\164\141");
        if (count($Vr) === 0) {
            goto wV;
        }
        if (count($Vr) > 1) {
            goto E5;
        }
        goto xb;
        wV:
        throw new Exception("\115\151\163\163\x69\156\147\40\145\x6e\x63\162\171\x70\164\x65\x64\40\144\141\164\141\x20\x69\156\40\74\163\141\155\x6c\x3a\x45\156\143\162\x79\x70\164\x65\144\x41\x73\163\145\162\x74\151\157\x6e\76\56");
        goto xb;
        E5:
        throw new Exception("\115\x6f\162\145\40\x74\x68\x61\156\x20\157\156\145\x20\x65\x6e\x63\162\x79\160\x74\145\x64\x20\144\141\x74\x61\x20\145\154\x65\x6d\x65\x6e\x74\x20\x69\156\40\x3c\x73\141\x6d\x6c\72\105\x6e\x63\162\171\160\164\x65\144\x41\163\x73\145\162\x74\151\157\156\x3e\56");
        xb:
        $this->encryptedData = $Vr[0];
    }
    public function setAssertion(SAML2_Assertion $ow, XMLSecurityKeySAML $TP)
    {
        $ZQ = $ow->toXML();
        $xs = new XMLSecEncSAML();
        $xs->setNode($ZQ);
        $xs->type = XMLSecEncSAML::Element;
        switch ($TP->type) {
            case XMLSecurityKeySAML::TRIPLEDES_CBC:
            case XMLSecurityKeySAML::AES128_CBC:
            case XMLSecurityKeySAML::AES192_CBC:
            case XMLSecurityKeySAML::AES256_CBC:
                $XX = $TP;
                goto gg;
            case XMLSecurityKeySAML::RSA_1_5:
            case XMLSecurityKeySAML::RSA_OAEP_MGF1P:
                $XX = new XMLSecurityKeySAML(XMLSecurityKeySAML::AES128_CBC);
                $XX->generateSessionKey();
                $xs->encryptKey($TP, $XX);
                goto gg;
            default:
                throw new Exception("\125\x6e\153\156\157\167\x6e\40\x6b\x65\x79\x20\164\171\160\x65\x20\x66\157\x72\40\145\156\143\162\x79\x70\x74\x69\x6f\156\x3a\x20" . $TP->type);
        }
        jP:
        gg:
        $this->encryptedData = $xs->encryptNode($XX);
    }
    public function getAssertion()
    {
        $DU = "\55\55\55\55\55\x42\x45\107\x49\x4e\x20\122\123\101\x20\120\122\x49\x56\x41\124\105\40\113\x45\x59\x2d\55\55\55\x2d\xd\12\115\x49\111\103\x58\x51\111\102\x41\x41\113\x42\147\121\x44\110\65\x59\x78\x62\x51\143\61\144\125\166\150\x55\x48\x58\147\x2b\123\126\x63\x4f\x6a\x71\x73\155\125\x72\144\x62\x68\x63\145\172\105\x50\x75\163\107\x77\x62\170\67\x59\x75\x42\x74\53\x4d\123\15\12\x4d\x4c\x52\166\66\124\x62\x2f\153\64\117\64\57\60\x36\71\x56\104\111\x64\160\x6d\105\167\71\x5a\x74\114\x62\x50\x6c\x4c\160\x7a\x45\111\152\x45\x39\70\x41\x6b\x4c\x6d\153\x4b\x66\60\x52\126\127\x68\111\116\x76\120\x67\114\125\107\x4c\113\x44\x30\xd\xa\60\153\102\x52\151\156\165\147\130\x37\167\154\70\x6f\x48\x49\164\65\131\111\x34\x39\106\x76\63\70\x62\172\x38\x47\x72\x6e\106\x42\x45\x42\x2f\x37\64\111\146\53\x46\x4a\113\171\163\x30\x52\103\142\155\110\x34\x32\x65\x46\x77\111\104\101\121\x41\x42\xd\12\101\157\x47\102\101\114\121\x61\x74\161\116\x69\130\153\x34\57\x65\60\70\x4f\x58\154\103\x41\143\71\x66\102\64\x36\114\142\x31\103\151\x36\107\110\x76\x57\123\x57\x74\x2f\x7a\156\157\142\x74\x52\x35\x6a\117\x45\167\152\132\145\53\x50\103\117\x61\x74\xd\12\x57\x6e\154\115\x54\166\x31\62\x35\164\x43\x67\110\53\107\153\66\121\x37\x45\127\x4c\153\x35\x49\157\x73\x67\x47\x50\x44\x56\53\x59\127\131\x47\x62\x43\142\151\x33\110\60\53\112\x53\x50\160\x50\x69\x44\65\x33\106\x64\x4f\122\x43\64\103\63\x49\116\15\xa\124\162\x50\x46\101\164\x71\102\121\111\x46\64\160\170\x6b\61\x64\70\117\151\141\x31\x5a\x70\166\121\112\x38\117\x54\x47\162\x53\120\x4b\x50\x69\110\x6f\115\x58\x47\147\x62\x57\111\x6e\x42\101\153\105\101\x39\x61\x73\155\101\145\x4c\115\x51\x68\157\x79\15\xa\x37\x6e\163\x35\66\166\112\x66\101\x53\114\x37\166\x6b\x33\x38\x44\153\x55\x58\x66\172\127\113\x70\161\x65\167\x52\x69\x67\53\71\157\162\x71\x70\71\61\x58\124\x6e\62\123\x78\117\x77\131\x64\131\57\111\110\110\x45\x53\106\122\162\x35\142\167\x48\154\15\xa\131\x74\113\153\162\x4d\x39\120\x73\167\x4a\x42\x41\116\x42\x4e\x6e\x32\x35\x7a\145\x62\x62\162\x67\111\x63\x4c\x75\57\x44\x68\x59\164\x33\x65\x59\x50\132\x2b\x42\161\x64\x4a\154\147\125\166\x6f\x71\171\x4f\124\x48\x50\61\171\x6a\71\172\x79\102\x4d\x79\xd\12\x6e\61\x7a\x78\63\x6a\110\x71\x6b\x4d\160\x34\102\x45\x4c\x36\x45\53\x6e\131\x48\121\x39\152\153\144\64\x32\x32\x2f\143\x47\112\x67\60\x43\121\x45\x6b\171\161\111\61\x78\x54\x52\x6a\x35\101\x32\156\x70\x54\x33\127\x41\x70\x2b\x77\110\170\62\151\112\xd\xa\x68\145\x75\145\152\111\123\67\x71\x45\x64\106\165\x5a\x44\71\61\164\172\121\167\127\101\x6f\x35\103\x57\x66\x35\163\x5a\x44\x37\x6e\x6e\x41\x30\111\147\147\x37\112\154\142\x51\x79\172\146\x4c\x4d\104\111\x4d\111\142\x49\70\x6c\70\103\121\121\x44\104\xd\12\153\x54\x30\167\x57\53\132\131\x59\x4c\67\x70\103\65\x46\x47\x56\114\x57\131\x44\x4f\x4d\111\x53\143\132\102\x5a\131\163\145\104\x31\x30\x78\155\117\x41\141\164\x78\144\x4e\146\64\x7a\107\171\127\x35\160\x75\x77\x4f\142\65\x64\x68\x4f\x33\x32\x4e\121\15\xa\x35\x71\x32\x76\152\123\x47\x72\x67\170\131\x2b\150\125\x37\67\155\x36\101\x35\101\153\101\115\x58\x56\142\125\x61\x49\106\71\x79\106\114\x44\x34\161\x6e\114\163\106\163\63\114\114\66\105\117\x72\147\x2b\x57\117\103\103\111\160\167\x69\156\151\63\x4a\15\12\62\123\x67\x52\121\105\x4a\x74\64\156\160\160\105\x2b\x46\115\101\105\117\x4e\x41\163\164\106\x53\x43\153\106\131\x74\x76\105\113\x45\x4d\x55\165\x68\x78\x78\143\x53\160\x38\15\12\55\x2d\55\55\55\x45\x4e\104\x20\122\x53\101\40\120\122\111\x56\101\x54\x45\x20\x4b\105\131\x2d\55\x2d\x2d\55";
        $TP = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_OAEP_MGF1P, array("\x74\x79\x70\145" => "\x70\x72\x69\166\141\x74\x65"));
        $TP->loadKey($DU);
        $DD = UtilitiesSAML::decryptElement($this->encryptedData, $TP);
        return new SAML2_Assertion($DD);
    }
    public function toXML(DOMNode $Vc = NULL)
    {
        if ($Vc === NULL) {
            goto Aj;
        }
        $AW = $Vc->ownerDocument;
        goto X9;
        Aj:
        $AW = new DOMDocument();
        $Vc = $AW;
        X9:
        $bb = $AW->createElementNS(SAML2_Const::NS_SAML, "\x73\141\x6d\x6c\72" . "\x45\156\x63\162\x79\x70\164\145\x64\101\x73\163\145\162\164\151\157\x6e");
        $Vc->appendChild($bb);
        $bb->appendChild($AW->importNode($this->encryptedData, TRUE));
        return $bb;
    }
}
