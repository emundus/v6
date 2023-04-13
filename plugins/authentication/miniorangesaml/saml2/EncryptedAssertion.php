<?php


class SAML2_EncryptedAssertion
{
    private $encryptedData;
    public function __construct(DOMElement $XT = NULL)
    {
        if (!($XT === NULL)) {
            goto TZ;
        }
        return;
        TZ:
        $oa = UtilitiesSAML::xpQuery($XT, "\56\x2f\x78\x65\156\x63\72\x45\156\x63\x72\171\x70\x74\x65\x64\104\141\164\x61");
        if (count($oa) === 0) {
            goto kd;
        }
        if (count($oa) > 1) {
            goto F1;
        }
        goto lS;
        kd:
        throw new Exception("\x4d\x69\163\x73\x69\156\x67\40\x65\x6e\143\x72\171\160\164\145\144\x20\x64\141\164\x61\40\x69\x6e\x20\74\x73\x61\155\x6c\72\105\x6e\143\x72\171\x70\x74\145\144\x41\163\163\145\x72\x74\x69\157\156\x3e\x2e");
        goto lS;
        F1:
        throw new Exception("\115\x6f\162\145\x20\164\x68\x61\156\x20\157\156\145\40\x65\156\143\x72\x79\x70\x74\145\x64\x20\144\x61\164\x61\40\x65\154\145\x6d\x65\x6e\x74\x20\151\x6e\x20\74\x73\x61\155\x6c\72\105\156\x63\162\171\x70\x74\145\144\x41\x73\x73\145\x72\164\151\157\x6e\76\x2e");
        lS:
        $this->encryptedData = $oa[0];
    }
    public function setAssertion(SAML2_Assertion $RL, XMLSecurityKeySAML $da)
    {
        $XT = $RL->toXML();
        $l6 = new XMLSecEncSAML();
        $l6->setNode($XT);
        $l6->type = XMLSecEncSAML::Element;
        switch ($da->type) {
            case XMLSecurityKeySAML::TRIPLEDES_CBC:
            case XMLSecurityKeySAML::AES128_CBC:
            case XMLSecurityKeySAML::AES192_CBC:
            case XMLSecurityKeySAML::AES256_CBC:
                $ES = $da;
                goto yc;
            case XMLSecurityKeySAML::RSA_1_5:
            case XMLSecurityKeySAML::RSA_OAEP_MGF1P:
                $ES = new XMLSecurityKeySAML(XMLSecurityKeySAML::AES128_CBC);
                $ES->generateSessionKey();
                $l6->encryptKey($da, $ES);
                goto yc;
            default:
                throw new Exception("\125\x6e\x6b\x6e\157\x77\156\40\153\145\171\40\164\x79\x70\145\40\x66\x6f\x72\40\145\x6e\x63\x72\171\x70\x74\x69\157\x6e\x3a\x20" . $da->type);
        }
        Ew:
        yc:
        $this->encryptedData = $l6->encryptNode($ES);
    }
    public function getAssertion()
    {
        $to = "\55\55\x2d\55\x2d\x42\x45\x47\x49\116\x20\x52\123\101\x20\x50\x52\x49\126\101\124\105\x20\x4b\105\x59\55\55\x2d\55\x2d\15\12\115\x49\x49\x43\x58\121\111\102\x41\x41\x4b\102\147\x51\104\x48\x35\x59\x78\x62\x51\143\61\144\x55\x76\x68\125\110\x58\x67\x2b\x53\x56\x63\117\x6a\x71\163\x6d\x55\x72\x64\142\150\143\145\x7a\105\x50\165\163\x47\167\x62\x78\67\x59\x75\102\x74\53\115\x53\xd\xa\x4d\x4c\122\166\66\x54\142\57\x6b\x34\117\x34\57\60\x36\x39\126\104\x49\144\x70\x6d\105\x77\x39\x5a\x74\x4c\x62\120\154\114\160\x7a\105\111\x6a\105\x39\x38\x41\x6b\x4c\x6d\153\x4b\146\60\x52\126\127\x68\111\116\166\x50\x67\114\125\107\x4c\113\x44\x30\xd\12\60\x6b\x42\122\x69\156\x75\x67\x58\x37\167\154\x38\157\110\111\164\x35\131\x49\64\71\106\x76\x33\x38\142\x7a\x38\x47\162\x6e\x46\x42\x45\102\57\67\64\x49\146\x2b\106\112\x4b\x79\x73\x30\x52\x43\x62\x6d\x48\64\x32\x65\x46\167\111\x44\101\121\x41\102\15\12\x41\157\x47\102\x41\114\x51\x61\164\x71\x4e\151\x58\153\x34\x2f\145\60\x38\117\x58\x6c\x43\x41\x63\x39\146\102\x34\66\114\142\x31\x43\151\66\x47\x48\x76\127\123\127\164\57\x7a\x6e\157\x62\x74\122\x35\152\x4f\105\167\x6a\132\145\53\120\x43\x4f\x61\x74\15\xa\127\x6e\x6c\x4d\124\166\61\x32\65\x74\103\x67\x48\x2b\x47\x6b\x36\x51\x37\105\x57\x4c\153\x35\111\157\x73\147\x47\120\104\x56\53\x59\x57\x59\x47\142\x43\x62\151\x33\x48\60\53\x4a\123\120\160\x50\151\104\x35\63\106\x64\x4f\122\103\64\103\63\x49\x4e\15\12\124\x72\x50\x46\101\164\x71\x42\x51\111\x46\x34\160\x78\x6b\x31\144\70\x4f\x69\141\61\x5a\160\x76\x51\112\70\x4f\124\107\x72\123\120\113\x50\151\110\157\x4d\x58\107\x67\142\x57\111\x6e\x42\x41\x6b\105\x41\x39\141\x73\155\x41\145\114\115\121\150\x6f\x79\15\xa\67\x6e\x73\x35\66\166\112\146\101\x53\x4c\x37\166\x6b\63\x38\104\153\125\x58\146\x7a\127\x4b\160\x71\x65\167\x52\151\147\x2b\71\x6f\162\x71\160\71\x31\130\x54\x6e\62\123\x78\x4f\x77\x59\144\x59\x2f\x49\110\110\x45\123\x46\x52\162\x35\142\x77\x48\x6c\15\xa\x59\x74\113\x6b\162\x4d\x39\120\x73\x77\x4a\102\x41\116\102\116\156\x32\x35\172\x65\x62\142\x72\x67\111\x63\114\165\x2f\x44\x68\x59\164\63\145\x59\120\x5a\53\102\161\144\x4a\x6c\x67\x55\x76\x6f\x71\x79\117\x54\x48\x50\61\x79\152\71\172\171\102\x4d\171\15\xa\x6e\x31\172\x78\63\152\x48\161\x6b\x4d\160\64\102\x45\x4c\x36\105\53\156\131\x48\x51\71\x6a\x6b\144\x34\x32\62\x2f\x63\x47\112\x67\60\103\x51\105\x6b\x79\x71\x49\61\x78\x54\122\152\65\x41\x32\156\160\124\x33\127\101\160\53\x77\110\x78\62\151\112\xd\12\x68\145\165\145\x6a\x49\x53\67\x71\105\144\x46\x75\x5a\104\71\61\x74\x7a\121\x77\127\101\x6f\65\103\127\x66\x35\163\x5a\104\67\x6e\156\101\60\111\147\x67\67\112\154\142\x51\x79\172\146\114\115\104\111\x4d\111\142\x49\x38\154\x38\103\x51\121\x44\x44\xd\12\153\x54\60\x77\127\53\132\131\x59\114\67\160\103\65\x46\107\x56\114\x57\131\104\117\115\x49\123\x63\132\102\x5a\131\163\x65\x44\61\x30\x78\x6d\117\x41\x61\164\x78\144\116\x66\64\x7a\x47\171\127\65\160\x75\167\x4f\142\x35\144\150\x4f\x33\62\x4e\121\xd\12\x35\x71\62\166\x6a\x53\107\162\147\x78\131\x2b\x68\125\67\x37\155\x36\x41\x35\x41\x6b\x41\x4d\130\x56\x62\125\x61\x49\106\x39\x79\x46\x4c\104\x34\x71\x6e\114\x73\x46\163\63\x4c\114\x36\x45\x4f\x72\147\x2b\127\x4f\x43\103\x49\x70\x77\151\x6e\151\x33\x4a\15\xa\x32\x53\147\122\x51\x45\x4a\164\64\x6e\160\x70\x45\x2b\x46\x4d\101\x45\x4f\x4e\101\x73\164\x46\123\103\153\106\131\x74\x76\x45\x4b\x45\x4d\125\165\x68\x78\170\143\x53\160\70\15\xa\x2d\55\x2d\55\x2d\105\x4e\x44\x20\x52\x53\101\40\x50\122\111\x56\x41\x54\x45\x20\x4b\x45\x59\x2d\x2d\x2d\55\55";
        $da = new XMLSecurityKeySAML(XMLSecurityKeySAML::RSA_OAEP_MGF1P, array("\164\x79\160\x65" => "\160\162\151\166\141\164\145"));
        $da->loadKey($to);
        $kW = UtilitiesSAML::decryptElement($this->encryptedData, $da);
        return new SAML2_Assertion($kW);
    }
    public function toXML(DOMNode $i0 = NULL)
    {
        if ($i0 === NULL) {
            goto O1;
        }
        $jI = $i0->ownerDocument;
        goto TD;
        O1:
        $jI = new DOMDocument();
        $i0 = $jI;
        TD:
        $bx = $jI->createElementNS(SAML2_Const::NS_SAML, "\163\141\155\154\x3a" . "\x45\x6e\x63\x72\x79\160\x74\x65\144\x41\x73\163\145\162\164\151\157\156");
        $i0->appendChild($bx);
        $bx->appendChild($jI->importNode($this->encryptedData, TRUE));
        return $bx;
    }
}
