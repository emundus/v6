<?php


function sortAndAddAttrs($g3, $tU)
{
    $iE = array();
    foreach ($tU as $JV) {
        $iE[$JV->nodeName] = $JV;
        jF:
    }
    lm:
    ksort($iE);
    foreach ($iE as $JV) {
        $g3->setAttribute($JV->nodeName, $JV->nodeValue);
        J7:
    }
    nJ:
}
function canonical($Du, $g3, $C5)
{
    if ($Du->nodeType != XML_DOCUMENT_NODE) {
        goto bb;
    }
    $D3 = $Du;
    goto bd;
    bb:
    $D3 = $Du->ownerDocument;
    bd:
    if (!($g3->nodeType != XML_ELEMENT_NODE)) {
        goto RZ;
    }
    if (!($g3->nodeType == XML_DOCUMENT_NODE)) {
        goto mN;
    }
    foreach ($g3->childNodes as $mj) {
        canonical($D3, $mj, $C5);
        vU:
    }
    zF:
    return;
    mN:
    if (!($g3->nodeType == XML_COMMENT_NODE && !$C5)) {
        goto Hj;
    }
    return;
    Hj:
    $Du->appendChild($D3->importNode($g3, TRUE));
    return;
    RZ:
    $VT = array();
    if ($g3->namespaceURI != '') {
        goto lT;
    }
    $q2 = $D3->createElement($g3->nodeName);
    goto Ao;
    lT:
    if ($g3->prefix == '') {
        goto SI;
    }
    $Bv = $Du->lookupPrefix($g3->namespaceURI);
    if ($Bv == $g3->prefix) {
        goto ba;
    }
    $q2 = $D3->createElement($g3->nodeName);
    $VT[$g3->namespaceURI] = $g3->prefix;
    goto X_;
    ba:
    $q2 = $D3->createElementNS($g3->namespaceURI, $g3->nodeName);
    X_:
    goto aT;
    SI:
    $q2 = $D3->createElementNS($g3->namespaceURI, $g3->nodeName);
    aT:
    Ao:
    $Du->appendChild($q2);
    $rG = new DOMXPath($g3->ownerDocument);
    $tU = $rG->query("\141\x74\164\x72\151\142\x75\164\x65\72\x3a\x2a\133\156\141\x6d\x65\163\160\141\x63\x65\55\165\162\151\x28\x2e\x29\x20\x21\x3d\x20\42\42\135", $g3);
    foreach ($tU as $JV) {
        if (!(array_key_exists($JV->namespaceURI, $VT) && $VT[$JV->namespaceURI] == $JV->prefix)) {
            goto sI;
        }
        goto sr;
        sI:
        $Bv = $Du->lookupPrefix($JV->namespaceURI);
        if ($Bv != $JV->prefix) {
            goto Eb;
        }
        $VT[$JV->namespaceURI] = NULL;
        goto GR;
        Eb:
        $VT[$JV->namespaceURI] = $JV->prefix;
        GR:
        sr:
    }
    EQ:
    if (!(count($VT) > 0)) {
        goto nP;
    }
    asort($VT);
    nP:
    foreach ($VT as $pc => $Bv) {
        if (!($Bv != NULL)) {
            goto cO;
        }
        $q2->setAttributeNS("\150\x74\164\160\x3a\x2f\x2f\167\x77\167\x2e\x77\63\x2e\157\162\147\57\62\60\60\60\57\170\155\154\x6e\163\57", "\170\x6d\x6c\x6e\163\x3a" . $Bv, $pc);
        cO:
        kq:
    }
    Sl:
    if (!(count($VT) > 0)) {
        goto Dy;
    }
    ksort($VT);
    Dy:
    $tU = $rG->query("\x61\x74\x74\x72\x69\142\165\x74\x65\x3a\72\x2a\133\156\x61\x6d\145\x73\x70\141\x63\145\x2d\165\162\x69\50\56\x29\x20\x3d\x20\x22\42\x5d", $g3);
    sortAndAddAttrs($q2, $tU);
    foreach ($VT as $Xq => $Bv) {
        $tU = $rG->query("\x61\x74\x74\162\151\142\x75\164\x65\x3a\x3a\x2a\133\x6e\141\x6d\145\x73\x70\141\x63\145\x2d\x75\x72\151\x28\x2e\51\40\x3d\40\x22" . $Xq . "\x22\x5d", $g3);
        sortAndAddAttrs($q2, $tU);
        QK:
    }
    IH:
    foreach ($g3->childNodes as $mj) {
        canonical($q2, $mj, $C5);
        D6:
    }
    MO:
}
function C14NGeneral($g3, $y6 = FALSE, $C5 = FALSE)
{
    $sC = explode("\x2e", PHP_VERSION);
    if (!($sC[0] > 5 || $sC[0] == 5 && $sC[1] >= 2)) {
        goto Qj;
    }
    return $g3->C14N($y6, $C5);
    Qj:
    if (!(!$g3 instanceof DOMElement && !$g3 instanceof DOMDocument)) {
        goto PT;
    }
    return NULL;
    PT:
    if (!($y6 == FALSE)) {
        goto u2;
    }
    throw new Exception("\117\156\154\171\x20\x65\x78\143\154\x75\163\151\x76\145\40\x63\x61\x6e\157\x6e\151\143\x61\154\x69\x7a\x61\164\151\x6f\156\x20\151\163\40\163\165\160\x70\157\x72\164\x65\144\x20\151\x6e\x20\x74\150\x69\163\x20\x76\x65\x72\x73\x69\157\x6e\40\157\x66\40\x50\110\x50");
    u2:
    $eP = new DOMDocument();
    canonical($eP, $g3, $C5);
    return $eP->saveXML($eP->documentElement, LIBXML_NOEMPTYTAG);
}
class XMLSecurityKeySAML
{
    const TRIPLEDES_CBC = "\x68\x74\x74\x70\x3a\57\57\x77\x77\167\x2e\167\x33\56\157\x72\147\x2f\62\x30\60\x31\x2f\x30\x34\x2f\x78\155\x6c\145\x6e\x63\43\x74\162\x69\x70\x6c\145\x64\145\x73\55\143\x62\x63";
    const AES128_CBC = "\150\164\x74\x70\72\x2f\57\x77\167\x77\x2e\x77\63\56\x6f\x72\147\57\62\60\x30\61\57\60\64\x2f\x78\155\154\x65\156\x63\43\141\x65\163\x31\62\x38\55\143\x62\x63";
    const AES192_CBC = "\150\164\x74\160\x3a\x2f\x2f\167\167\x77\x2e\167\63\56\x6f\x72\147\x2f\62\60\60\61\x2f\x30\x34\x2f\x78\x6d\x6c\145\x6e\x63\x23\x61\145\163\x31\71\x32\x2d\x63\x62\x63";
    const AES256_CBC = "\150\x74\x74\160\72\x2f\x2f\x77\x77\167\x2e\x77\63\x2e\157\x72\x67\x2f\x32\60\60\61\57\60\x34\57\170\155\154\x65\156\143\x23\141\145\163\62\x35\x36\x2d\143\x62\143";
    const RSA_1_5 = "\150\164\164\160\72\57\x2f\x77\x77\167\56\x77\63\x2e\x6f\162\147\x2f\62\x30\60\x31\57\x30\x34\x2f\170\x6d\154\x65\156\143\x23\x72\x73\x61\55\61\137\65";
    const RSA_OAEP_MGF1P = "\x68\164\x74\160\72\57\57\167\167\167\x2e\167\x33\56\157\x72\147\57\62\x30\x30\61\57\x30\x34\x2f\170\155\154\x65\156\143\43\162\x73\141\55\157\141\145\160\55\155\147\x66\61\x70";
    const DSA_SHA1 = "\x68\164\x74\160\72\x2f\57\x77\x77\167\56\167\x33\x2e\x6f\162\x67\x2f\62\x30\x30\60\57\60\71\x2f\170\155\154\x64\x73\x69\x67\43\x64\163\141\55\x73\150\x61\x31";
    const RSA_SHA1 = "\x68\164\164\x70\72\x2f\x2f\167\167\x77\x2e\167\63\x2e\x6f\x72\147\x2f\x32\x30\60\60\x2f\x30\x39\x2f\170\155\154\x64\x73\151\147\43\162\163\141\x2d\x73\x68\141\x31";
    const RSA_SHA256 = "\x68\164\164\x70\72\x2f\x2f\167\167\167\56\167\x33\x2e\157\162\147\57\x32\60\60\x31\57\x30\x34\x2f\170\155\x6c\x64\163\151\x67\x2d\155\157\162\145\x23\x72\163\x61\55\163\150\x61\62\65\66";
    const RSA_SHA384 = "\150\164\x74\160\72\x2f\57\x77\167\x77\56\x77\63\56\157\x72\147\x2f\62\x30\x30\x31\57\x30\x34\x2f\x78\x6d\x6c\x64\x73\x69\x67\55\155\157\x72\145\43\162\x73\141\x2d\163\x68\141\x33\x38\x34";
    const RSA_SHA512 = "\150\x74\x74\x70\72\57\x2f\x77\x77\167\56\x77\63\56\x6f\162\x67\57\62\x30\60\61\x2f\x30\x34\57\x78\x6d\154\x64\163\x69\x67\x2d\x6d\157\162\145\x23\162\163\141\55\x73\150\141\x35\61\x32";
    private $cryptParams = array();
    public $type = 0;
    public $key = NULL;
    public $passphrase = '';
    public $iv = NULL;
    public $name = NULL;
    public $keyChain = NULL;
    public $isEncrypted = FALSE;
    public $encryptedCtx = NULL;
    public $guid = NULL;
    private $x509Certificate = NULL;
    private $X509Thumbprint = NULL;
    public function __construct($X7, $DX = NULL)
    {
        srand();
        switch ($X7) {
            case XMLSecurityKeySAML::TRIPLEDES_CBC:
                $this->cryptParams["\154\151\x62\x72\141\x72\171"] = "\x6d\x63\162\171\x70\164";
                $this->cryptParams["\x63\151\x70\150\x65\x72"] = MCRYPT_TRIPLEDES;
                $this->cryptParams["\155\157\x64\x65"] = MCRYPT_MODE_CBC;
                $this->cryptParams["\x6d\145\x74\x68\x6f\144"] = "\150\x74\x74\x70\x3a\x2f\x2f\167\167\x77\x2e\167\63\56\x6f\x72\147\57\62\60\x30\61\57\60\x34\x2f\170\x6d\x6c\x65\156\143\x23\164\x72\x69\160\154\145\x64\x65\163\55\x63\142\143";
                $this->cryptParams["\x6b\145\171\x73\151\x7a\x65"] = 24;
                goto Lt;
            case XMLSecurityKeySAML::AES128_CBC:
                $this->cryptParams["\x6c\151\x62\x72\141\162\171"] = "\155\143\x72\171\160\x74";
                $this->cryptParams["\x63\151\160\x68\x65\162"] = MCRYPT_RIJNDAEL_128;
                $this->cryptParams["\155\157\144\x65"] = MCRYPT_MODE_CBC;
                $this->cryptParams["\x6d\x65\164\150\x6f\x64"] = "\x68\x74\164\x70\x3a\x2f\57\x77\167\167\56\167\63\x2e\157\x72\x67\57\62\x30\x30\61\57\x30\x34\x2f\170\x6d\154\145\x6e\x63\43\141\x65\163\x31\x32\70\x2d\143\x62\x63";
                $this->cryptParams["\153\x65\171\x73\x69\x7a\145"] = 16;
                goto Lt;
            case XMLSecurityKeySAML::AES192_CBC:
                $this->cryptParams["\154\151\x62\x72\x61\x72\x79"] = "\155\143\x72\171\x70\x74";
                $this->cryptParams["\143\151\160\150\x65\162"] = MCRYPT_RIJNDAEL_128;
                $this->cryptParams["\x6d\157\144\145"] = MCRYPT_MODE_CBC;
                $this->cryptParams["\155\145\x74\150\x6f\x64"] = "\150\x74\x74\x70\x3a\x2f\x2f\x77\167\167\56\x77\x33\56\x6f\162\x67\x2f\62\x30\x30\x31\x2f\x30\64\57\170\155\x6c\x65\156\143\x23\141\x65\163\61\71\62\x2d\143\x62\143";
                $this->cryptParams["\153\x65\x79\163\151\172\145"] = 24;
                goto Lt;
            case XMLSecurityKeySAML::AES256_CBC:
                $this->cryptParams["\x6c\x69\142\x72\141\162\171"] = "\155\143\x72\x79\160\164";
                $this->cryptParams["\x63\151\x70\x68\145\x72"] = MCRYPT_RIJNDAEL_128;
                $this->cryptParams["\x6d\x6f\144\x65"] = MCRYPT_MODE_CBC;
                $this->cryptParams["\155\145\164\x68\157\x64"] = "\x68\x74\x74\160\72\57\x2f\x77\x77\167\56\167\x33\x2e\x6f\x72\x67\x2f\62\x30\x30\61\x2f\x30\x34\57\x78\155\x6c\x65\156\x63\43\141\145\x73\62\65\66\55\143\x62\143";
                $this->cryptParams["\153\x65\x79\x73\x69\x7a\x65"] = 32;
                goto Lt;
            case XMLSecurityKeySAML::RSA_1_5:
                $this->cryptParams["\154\151\142\162\141\x72\171"] = "\x6f\160\145\156\x73\163\154";
                $this->cryptParams["\160\x61\x64\x64\x69\x6e\x67"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\155\145\x74\150\157\144"] = "\x68\x74\164\x70\72\x2f\57\x77\x77\167\x2e\x77\x33\x2e\157\162\x67\x2f\62\60\x30\61\57\60\64\x2f\x78\155\154\145\156\x63\x23\x72\x73\141\55\x31\137\x35";
                if (!(is_array($DX) && !empty($DX["\164\x79\160\x65"]))) {
                    goto eB;
                }
                if (!($DX["\x74\171\160\x65"] == "\x70\165\x62\154\151\143" || $DX["\164\x79\160\145"] == "\x70\162\x69\x76\141\x74\145")) {
                    goto qh;
                }
                $this->cryptParams["\x74\x79\160\145"] = $DX["\164\x79\160\145"];
                goto Lt;
                qh:
                eB:
                throw new Exception("\103\145\x72\x74\x69\x66\151\143\141\164\x65\x20\42\164\171\x70\x65\42\40\x28\x70\162\x69\x76\x61\x74\x65\x2f\160\165\142\154\x69\x63\51\40\x6d\x75\163\x74\x20\142\x65\x20\x70\141\163\x73\x65\x64\x20\166\151\141\x20\x70\x61\x72\x61\x6d\145\x74\145\162\163");
                return;
            case XMLSecurityKeySAML::RSA_OAEP_MGF1P:
                $this->cryptParams["\154\x69\x62\162\141\x72\171"] = "\x6f\x70\x65\x6e\x73\163\154";
                $this->cryptParams["\x70\141\144\144\151\x6e\x67"] = OPENSSL_PKCS1_OAEP_PADDING;
                $this->cryptParams["\155\145\x74\x68\157\x64"] = "\x68\x74\x74\160\x3a\x2f\57\167\167\x77\56\167\x33\x2e\157\x72\147\x2f\62\60\x30\x31\57\x30\x34\57\x78\155\x6c\x65\x6e\x63\43\162\x73\141\x2d\157\141\145\x70\x2d\x6d\147\146\61\x70";
                $this->cryptParams["\x68\141\163\x68"] = NULL;
                if (!(is_array($DX) && !empty($DX["\x74\x79\x70\145"]))) {
                    goto P4;
                }
                if (!($DX["\164\171\160\145"] == "\x70\165\x62\x6c\x69\143" || $DX["\164\171\160\x65"] == "\x70\162\151\166\141\x74\145")) {
                    goto Vv;
                }
                $this->cryptParams["\164\171\160\x65"] = $DX["\164\171\x70\145"];
                goto Lt;
                Vv:
                P4:
                throw new Exception("\103\x65\162\x74\151\x66\151\x63\141\x74\145\40\x22\164\x79\160\145\x22\40\x28\x70\x72\x69\x76\x61\x74\145\57\x70\x75\x62\x6c\x69\143\51\40\155\x75\x73\164\x20\142\145\x20\x70\x61\x73\163\x65\x64\40\166\151\x61\x20\160\141\x72\141\x6d\x65\164\145\x72\x73");
                return;
            case XMLSecurityKeySAML::RSA_SHA1:
                $this->cryptParams["\154\x69\142\x72\x61\x72\x79"] = "\x6f\x70\145\x6e\x73\163\x6c";
                $this->cryptParams["\x6d\x65\164\150\157\x64"] = "\150\164\x74\x70\72\x2f\x2f\x77\167\167\56\x77\63\56\x6f\x72\x67\57\62\60\60\x30\x2f\60\x39\57\170\155\x6c\x64\x73\x69\147\43\x72\163\x61\x2d\x73\x68\x61\61";
                $this->cryptParams["\160\141\x64\144\x69\156\x67"] = OPENSSL_PKCS1_PADDING;
                if (!(is_array($DX) && !empty($DX["\164\171\x70\x65"]))) {
                    goto N4;
                }
                if (!($DX["\x74\171\x70\x65"] == "\x70\x75\142\x6c\151\143" || $DX["\x74\x79\x70\x65"] == "\160\x72\x69\166\141\164\x65")) {
                    goto bz;
                }
                $this->cryptParams["\164\x79\x70\x65"] = $DX["\164\x79\x70\145"];
                goto Lt;
                bz:
                N4:
                throw new Exception("\103\x65\162\164\x69\x66\151\x63\141\164\145\40\x22\x74\171\160\145\x22\x20\x28\160\162\x69\166\141\164\x65\57\160\x75\142\154\x69\143\x29\40\x6d\165\x73\x74\x20\x62\x65\x20\160\x61\163\x73\145\x64\40\x76\x69\x61\40\x70\x61\162\141\x6d\x65\164\145\x72\x73");
                goto Lt;
            case XMLSecurityKeySAML::RSA_SHA256:
                $this->cryptParams["\x6c\x69\x62\162\141\162\171"] = "\157\160\145\156\x73\x73\154";
                $this->cryptParams["\155\145\x74\x68\157\x64"] = "\x68\164\x74\x70\x3a\57\57\167\x77\167\x2e\x77\x33\56\x6f\x72\147\57\x32\60\x30\61\57\60\x34\x2f\170\155\x6c\144\x73\x69\147\x2d\x6d\x6f\x72\145\43\162\163\x61\x2d\163\150\x61\x32\65\66";
                $this->cryptParams["\x70\x61\144\144\x69\156\147"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\x64\151\x67\x65\x73\x74"] = "\x53\x48\x41\62\65\66";
                if (!(is_array($DX) && !empty($DX["\164\x79\160\145"]))) {
                    goto Vu;
                }
                if (!($DX["\164\x79\160\x65"] == "\160\165\x62\154\151\x63" || $DX["\x74\171\x70\145"] == "\160\162\151\166\141\164\x65")) {
                    goto Cn;
                }
                $this->cryptParams["\x74\171\x70\145"] = $DX["\x74\171\160\x65"];
                goto Lt;
                Cn:
                Vu:
                throw new Exception("\103\145\x72\x74\151\x66\151\143\141\164\x65\40\42\x74\x79\x70\x65\x22\40\x28\160\x72\x69\166\x61\x74\x65\x2f\160\x75\x62\x6c\151\x63\x29\x20\155\x75\x73\x74\40\142\145\40\160\x61\x73\x73\x65\x64\40\x76\x69\141\40\160\x61\162\x61\x6d\x65\164\x65\x72\x73");
                goto Lt;
            case XMLSecurityKeySAML::RSA_SHA384:
                $this->cryptParams["\154\x69\x62\162\141\162\171"] = "\x6f\x70\x65\x6e\x73\x73\154";
                $this->cryptParams["\x6d\145\x74\x68\x6f\x64"] = "\150\x74\x74\160\x3a\57\57\x77\x77\x77\56\167\x33\56\157\x72\x67\57\62\60\x30\x31\57\60\x34\x2f\x78\x6d\154\144\x73\x69\x67\x2d\x6d\157\x72\145\x23\162\163\141\55\x73\x68\x61\x33\x38\64";
                $this->cryptParams["\x70\x61\144\x64\x69\x6e\x67"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\144\x69\x67\x65\x73\x74"] = "\123\x48\x41\63\x38\x34";
                if (!(is_array($DX) && !empty($DX["\x74\171\160\x65"]))) {
                    goto m5;
                }
                if (!($DX["\x74\171\x70\145"] == "\x70\165\x62\154\x69\143" || $DX["\164\171\160\145"] == "\160\162\x69\x76\x61\x74\x65")) {
                    goto RO;
                }
                $this->cryptParams["\x74\171\x70\x65"] = $DX["\x74\x79\x70\145"];
                goto Lt;
                RO:
                m5:
            case XMLSecurityKeySAML::RSA_SHA512:
                $this->cryptParams["\x6c\x69\142\162\141\x72\x79"] = "\157\x70\145\156\163\163\x6c";
                $this->cryptParams["\155\145\x74\x68\157\x64"] = "\x68\x74\164\x70\72\57\x2f\x77\x77\167\56\167\63\56\x6f\x72\x67\57\x32\60\x30\61\x2f\x30\x34\57\x78\x6d\154\144\x73\151\x67\55\x6d\x6f\x72\x65\43\x72\x73\141\x2d\x73\x68\x61\x35\x31\62";
                $this->cryptParams["\x70\x61\x64\144\x69\156\x67"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\x64\x69\147\x65\163\164"] = "\x53\x48\x41\65\61\x32";
                if (!(is_array($DX) && !empty($DX["\164\171\x70\145"]))) {
                    goto EW;
                }
                if (!($DX["\164\x79\x70\145"] == "\x70\x75\142\x6c\151\143" || $DX["\164\x79\160\x65"] == "\x70\162\x69\x76\x61\x74\145")) {
                    goto vO;
                }
                $this->cryptParams["\x74\x79\160\145"] = $DX["\164\x79\x70\x65"];
                goto Lt;
                vO:
                EW:
            default:
                throw new Exception("\x49\x6e\x76\x61\x6c\x69\144\40\x4b\x65\x79\40\x54\171\x70\145");
                return;
        }
        pk:
        Lt:
        $this->type = $X7;
    }
    public function getSymmetricKeySize()
    {
        if (isset($this->cryptParams["\153\x65\x79\163\151\172\145"])) {
            goto b_;
        }
        return NULL;
        b_:
        return $this->cryptParams["\x6b\x65\x79\x73\x69\x7a\145"];
    }
    public function generateSessionKey()
    {
        if (isset($this->cryptParams["\153\x65\171\x73\x69\x7a\145"])) {
            goto hZ;
        }
        throw new Exception("\125\156\153\156\157\x77\156\40\153\x65\171\40\x73\151\172\145\x20\x66\157\162\40\164\x79\160\145\40\x22" . $this->type . "\x22\x2e");
        hZ:
        $xM = $this->cryptParams["\x6b\145\171\163\151\x7a\x65"];
        if (function_exists("\x6f\160\145\x6e\163\163\154\137\x72\141\156\144\157\155\x5f\160\x73\x65\x75\144\157\x5f\x62\171\164\x65\163")) {
            goto yT;
        }
        $da = mcrypt_create_iv($xM, MCRYPT_RAND);
        goto VJ;
        yT:
        $da = openssl_random_pseudo_bytes($xM);
        VJ:
        if (!($this->type === XMLSecurityKeySAML::TRIPLEDES_CBC)) {
            goto yB;
        }
        $AM = 0;
        Zx:
        if (!($AM < strlen($da))) {
            goto oZ;
        }
        $AB = ord($da[$AM]) & 0xfe;
        $vz = 1;
        $Q3 = 1;
        D1:
        if (!($Q3 < 8)) {
            goto i0;
        }
        $vz ^= $AB >> $Q3 & 1;
        nL:
        $Q3++;
        goto D1;
        i0:
        $AB |= $vz;
        $da[$AM] = chr($AB);
        X9:
        $AM++;
        goto Zx;
        oZ:
        yB:
        $this->key = $da;
        return $da;
    }
    public static function getRawThumbprint($J2)
    {
        $QN = explode("\xa", $J2);
        $oa = '';
        $E7 = FALSE;
        foreach ($QN as $Iw) {
            if (!$E7) {
                goto E1;
            }
            if (!(strncmp($Iw, "\x2d\x2d\x2d\x2d\55\105\x4e\104\40\103\x45\x52\124\111\106\111\103\x41\x54\x45", 20) == 0)) {
                goto Wu;
            }
            $E7 = FALSE;
            goto w_;
            Wu:
            $oa .= trim($Iw);
            goto RY;
            E1:
            if (!(strncmp($Iw, "\55\55\55\x2d\55\x42\105\x47\111\116\x20\103\x45\x52\x54\x49\x46\111\x43\101\x54\x45", 22) == 0)) {
                goto ro;
            }
            $E7 = TRUE;
            ro:
            RY:
            Z5:
        }
        w_:
        if (empty($oa)) {
            goto To;
        }
        return strtolower(sha1(base64_decode($oa)));
        To:
        return NULL;
    }
    public function loadKey($da, $bm = FALSE, $Uy = FALSE)
    {
        if ($bm) {
            goto h4;
        }
        $this->key = $da;
        goto SO;
        h4:
        $this->key = file_get_contents($da);
        SO:
        if ($Uy) {
            goto OC;
        }
        $this->x509Certificate = NULL;
        goto Wj;
        OC:
        $this->key = openssl_x509_read($this->key);
        openssl_x509_export($this->key, $d_);
        $this->x509Certificate = $d_;
        $this->key = $d_;
        Wj:
        if ($this->cryptParams["\154\x69\x62\162\141\x72\x79"] == "\x6f\160\x65\156\x73\x73\154") {
            goto sC;
        }
        if (!($this->cryptParams["\x63\x69\160\150\x65\162"] == MCRYPT_RIJNDAEL_128)) {
            goto PD;
        }
        switch ($this->type) {
            case XMLSecurityKeySAML::AES256_CBC:
                if (!(strlen($this->key) < 25)) {
                    goto yK;
                }
                throw new Exception("\113\145\x79\x20\155\165\163\x74\x20\x63\x6f\156\x74\x61\x69\x6e\40\141\164\x20\154\145\x61\163\x74\x20\x32\x35\x20\x63\150\x61\162\x61\x63\164\x65\162\x73\x20\146\x6f\x72\40\164\150\x69\x73\40\x63\151\x70\x68\145\x72");
                yK:
                goto Wr;
            case XMLSecurityKeySAML::AES192_CBC:
                if (!(strlen($this->key) < 17)) {
                    goto Ks;
                }
                throw new Exception("\x4b\x65\x79\x20\155\x75\163\x74\40\143\x6f\x6e\164\x61\151\156\40\141\164\40\x6c\x65\x61\163\164\x20\61\x37\40\x63\150\x61\x72\x61\143\x74\x65\162\163\x20\146\x6f\x72\40\164\x68\x69\x73\40\143\x69\160\x68\x65\162");
                Ks:
                goto Wr;
        }
        Cz:
        Wr:
        PD:
        goto hV;
        sC:
        if ($this->cryptParams["\164\x79\x70\x65"] == "\160\165\x62\x6c\151\143") {
            goto JU;
        }
        $this->key = openssl_get_privatekey($this->key, $this->passphrase);
        goto lI;
        JU:
        if (!$Uy) {
            goto p2;
        }
        $this->X509Thumbprint = self::getRawThumbprint($this->key);
        p2:
        $this->key = openssl_get_publickey($this->key);
        lI:
        hV:
    }
    private function encryptMcrypt($oa)
    {
        $ry = mcrypt_module_open($this->cryptParams["\143\x69\x70\150\145\x72"], '', $this->cryptParams["\155\157\144\145"], '');
        $this->iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($ry), MCRYPT_RAND);
        mcrypt_generic_init($ry, $this->key, $this->iv);
        if (!($this->cryptParams["\155\x6f\144\x65"] == MCRYPT_MODE_CBC)) {
            goto NB;
        }
        $lX = mcrypt_enc_get_block_size($ry);
        $oZ = $wG = strlen($oa);
        x4:
        if (!($wG % $lX != $lX - 1)) {
            goto RN;
        }
        $oa .= chr(rand(1, 127));
        oe:
        $wG++;
        goto x4;
        RN:
        $oa .= chr($wG - $oZ + 1);
        NB:
        $jm = $this->iv . mcrypt_generic($ry, $oa);
        mcrypt_generic_deinit($ry);
        mcrypt_module_close($ry);
        return $jm;
    }
    private function decryptMcrypt($oa)
    {
        $ry = mcrypt_module_open($this->cryptParams["\x63\x69\160\150\x65\x72"], '', $this->cryptParams["\x6d\157\144\x65"], '');
        $NB = mcrypt_enc_get_iv_size($ry);
        $this->iv = substr($oa, 0, $NB);
        $oa = substr($oa, $NB);
        mcrypt_generic_init($ry, $this->key, $this->iv);
        $z1 = mdecrypt_generic($ry, $oa);
        mcrypt_generic_deinit($ry);
        mcrypt_module_close($ry);
        if (!($this->cryptParams["\x6d\x6f\144\x65"] == MCRYPT_MODE_CBC)) {
            goto I8;
        }
        $Dr = strlen($z1);
        $dU = substr($z1, $Dr - 1, 1);
        $z1 = substr($z1, 0, $Dr - ord($dU));
        I8:
        return $z1;
    }
    private function encryptOpenSSL($oa)
    {
        if ($this->cryptParams["\164\x79\160\x65"] == "\160\165\142\x6c\x69\x63") {
            goto ID;
        }
        if (openssl_private_encrypt($oa, $jm, $this->key, $this->cryptParams["\160\x61\144\x64\x69\x6e\x67"])) {
            goto p9;
        }
        throw new Exception("\x46\x61\151\x6c\165\x72\x65\40\x65\156\x63\162\x79\x70\x74\x69\x6e\147\x20\x44\141\164\141");
        return;
        p9:
        goto vY;
        ID:
        if (openssl_public_encrypt($oa, $jm, $this->key, $this->cryptParams["\160\141\144\144\151\156\147"])) {
            goto ub;
        }
        throw new Exception("\106\141\x69\154\165\x72\x65\40\x65\156\x63\x72\x79\160\164\151\x6e\x67\x20\x44\141\164\141");
        return;
        ub:
        vY:
        return $jm;
    }
    private function decryptOpenSSL($oa)
    {
        if ($this->cryptParams["\x74\x79\160\145"] == "\160\165\x62\154\151\x63") {
            goto Kg;
        }
        if (openssl_private_decrypt($oa, $VD, $this->key, $this->cryptParams["\160\141\144\144\151\156\147"])) {
            goto wG;
        }
        throw new Exception("\106\x61\x69\154\x75\162\x65\x20\144\x65\143\162\171\x70\x74\x69\x6e\x67\40\104\x61\x74\x61");
        return;
        wG:
        goto zB;
        Kg:
        if (openssl_public_decrypt($oa, $VD, $this->key, $this->cryptParams["\160\141\x64\144\x69\x6e\147"])) {
            goto lw;
        }
        throw new Exception("\106\141\151\154\165\162\145\40\x64\145\143\x72\171\x70\164\151\x6e\147\40\104\141\164\x61");
        return;
        lw:
        zB:
        return $VD;
    }
    private function signOpenSSL($oa)
    {
        $t9 = OPENSSL_ALGO_SHA1;
        if (empty($this->cryptParams["\x64\151\147\145\163\x74"])) {
            goto n_;
        }
        $t9 = $this->cryptParams["\144\x69\x67\145\163\164"];
        n_:
        if (openssl_sign($oa, $Qt, $this->key, $t9)) {
            goto VN;
        }
        throw new Exception("\x46\141\x69\x6c\165\162\145\40\x53\x69\x67\156\151\x6e\147\x20\104\x61\164\141\x3a\x20" . openssl_error_string() . "\x20\55\40" . $t9);
        return;
        VN:
        return $Qt;
    }
    private function verifyOpenSSL($oa, $Qt)
    {
        $t9 = OPENSSL_ALGO_SHA1;
        if (empty($this->cryptParams["\144\x69\x67\x65\163\x74"])) {
            goto m_;
        }
        $t9 = $this->cryptParams["\x64\x69\147\145\163\x74"];
        m_:
        return openssl_verify($oa, $Qt, $this->key, $t9);
    }
    public function encryptData($oa)
    {
        switch ($this->cryptParams["\154\151\142\162\x61\162\171"]) {
            case "\x6d\143\x72\171\160\x74":
                return $this->encryptMcrypt($oa);
                goto Um;
            case "\157\160\x65\x6e\x73\x73\154":
                return $this->encryptOpenSSL($oa);
                goto Um;
        }
        Ov:
        Um:
    }
    public function decryptData($oa)
    {
        switch ($this->cryptParams["\154\151\142\162\141\x72\x79"]) {
            case "\155\x63\x72\x79\x70\x74":
                return $this->decryptMcrypt($oa);
                goto We;
            case "\x6f\160\145\x6e\x73\x73\x6c":
                return $this->decryptOpenSSL($oa);
                goto We;
        }
        ut:
        We:
    }
    public function signData($oa)
    {
        switch ($this->cryptParams["\x6c\x69\142\x72\x61\x72\171"]) {
            case "\x6f\160\x65\x6e\x73\163\154":
                return $this->signOpenSSL($oa);
                goto ur;
        }
        ET:
        ur:
    }
    public function verifySignature($oa, $Qt)
    {
        switch ($this->cryptParams["\x6c\x69\142\x72\141\162\x79"]) {
            case "\x6f\x70\145\x6e\163\163\154":
                return $this->verifyOpenSSL($oa, $Qt);
                goto JG;
        }
        y9:
        JG:
    }
    public function getAlgorith()
    {
        return $this->cryptParams["\x6d\x65\x74\150\157\x64"];
    }
    static function makeAsnSegment($X7, $Rs)
    {
        switch ($X7) {
            case 0x2:
                if (!(ord($Rs) > 0x7f)) {
                    goto Td;
                }
                $Rs = chr(0) . $Rs;
                Td:
                goto Cs;
            case 0x3:
                $Rs = chr(0) . $Rs;
                goto Cs;
        }
        zI:
        Cs:
        $hu = strlen($Rs);
        if ($hu < 128) {
            goto LY;
        }
        if ($hu < 0x100) {
            goto Ow;
        }
        if ($hu < 0x10000) {
            goto nA;
        }
        $Cs = NULL;
        goto Zs;
        nA:
        $Cs = sprintf("\45\143\x25\143\x25\143\x25\x63\x25\163", $X7, 0x82, $hu / 0x100, $hu % 0x100, $Rs);
        Zs:
        goto Ls;
        Ow:
        $Cs = sprintf("\x25\143\45\143\45\x63\x25\x73", $X7, 0x81, $hu, $Rs);
        Ls:
        goto K6;
        LY:
        $Cs = sprintf("\45\x63\x25\143\x25\x73", $X7, $hu, $Rs);
        K6:
        return $Cs;
    }
    static function convertRSA($Fx, $QV)
    {
        $k0 = XMLSecurityKeySAML::makeAsnSegment(0x2, $QV);
        $ae = XMLSecurityKeySAML::makeAsnSegment(0x2, $Fx);
        $M7 = XMLSecurityKeySAML::makeAsnSegment(0x30, $ae . $k0);
        $jk = XMLSecurityKeySAML::makeAsnSegment(0x3, $M7);
        $D2 = pack("\110\52", "\x33\x30\x30\x44\60\66\60\x39\x32\x41\70\x36\x34\70\70\66\x46\x37\x30\x44\60\x31\x30\x31\x30\x31\x30\65\60\60");
        $Xi = XMLSecurityKeySAML::makeAsnSegment(0x30, $D2 . $jk);
        $af = base64_encode($Xi);
        $Ie = "\55\x2d\55\x2d\x2d\x42\x45\107\111\116\x20\120\x55\102\114\x49\x43\x20\113\x45\x59\55\55\55\x2d\x2d\xa";
        $fv = 0;
        T5:
        if (!($rd = substr($af, $fv, 64))) {
            goto eZ;
        }
        $Ie = $Ie . $rd . "\xa";
        $fv += 64;
        goto T5;
        eZ:
        return $Ie . "\55\55\55\x2d\x2d\105\116\x44\x20\x50\125\x42\x4c\x49\103\x20\x4b\x45\x59\x2d\x2d\x2d\55\55\xa";
    }
    public function serializeKey($cF)
    {
    }
    public function getX509Certificate()
    {
        return $this->x509Certificate;
    }
    public function getX509Thumbprint()
    {
        return $this->X509Thumbprint;
    }
    public static function fromEncryptedKeyElement(DOMElement $g3)
    {
        $NF = new XMLSecEncSAML();
        $NF->setNode($g3);
        if ($qZ = $NF->locateKey()) {
            goto rh;
        }
        throw new Exception("\125\x6e\141\142\x6c\x65\40\x74\x6f\x20\x6c\x6f\x63\141\164\x65\40\141\154\x67\x6f\162\151\164\x68\x6d\x20\146\x6f\162\x20\x74\150\151\163\x20\x45\x6e\x63\x72\x79\x70\x74\x65\x64\x20\x4b\145\x79");
        rh:
        $qZ->isEncrypted = TRUE;
        $qZ->encryptedCtx = $NF;
        XMLSecEncSAML::staticLocateKeyInfo($qZ, $g3);
        return $qZ;
    }
}
class XMLSecurityDSigSAML
{
    const XMLDSIGNS = "\x68\164\164\160\72\x2f\57\x77\x77\167\x2e\x77\63\56\157\162\x67\x2f\62\x30\x30\x30\57\x30\x39\57\x78\x6d\x6c\x64\x73\151\x67\43";
    const SHA1 = "\150\164\164\160\x3a\x2f\x2f\167\167\x77\56\x77\x33\x2e\x6f\x72\x67\x2f\62\x30\60\x30\x2f\x30\x39\x2f\x78\155\x6c\144\x73\151\x67\x23\x73\150\x61\61";
    const SHA256 = "\150\x74\164\160\x3a\57\57\x77\x77\x77\56\x77\63\x2e\157\x72\147\57\x32\60\x30\x31\57\x30\x34\57\170\x6d\154\x65\x6e\143\x23\163\x68\x61\x32\x35\66";
    const SHA384 = "\x68\x74\164\x70\x3a\x2f\57\x77\x77\167\56\x77\63\56\157\x72\x67\x2f\x32\x30\x30\x31\x2f\60\64\57\x78\x6d\154\144\x73\151\147\55\155\157\162\x65\43\x73\x68\x61\x33\x38\x34";
    const SHA512 = "\150\164\x74\160\x3a\x2f\57\x77\x77\x77\56\167\63\56\x6f\162\147\x2f\62\x30\x30\61\57\60\64\57\170\x6d\154\145\x6e\x63\x23\x73\x68\x61\x35\61\62";
    const RIPEMD160 = "\150\x74\x74\160\72\57\x2f\x77\167\167\x2e\x77\x33\56\157\162\147\x2f\x32\60\60\61\x2f\x30\x34\57\x78\x6d\154\145\x6e\143\x23\x72\151\x70\145\155\x64\x31\66\60";
    const C14N = "\x68\x74\x74\x70\x3a\57\57\x77\167\167\56\x77\x33\x2e\157\162\x67\x2f\x54\x52\x2f\x32\x30\x30\x31\x2f\x52\105\103\55\x78\x6d\x6c\55\x63\61\64\156\55\62\60\60\61\60\63\61\65";
    const C14N_COMMENTS = "\150\x74\x74\x70\x3a\x2f\x2f\x77\x77\167\56\167\63\x2e\x6f\162\x67\x2f\124\122\57\x32\x30\60\x31\57\x52\x45\103\55\x78\155\x6c\55\143\x31\x34\x6e\55\x32\x30\60\x31\60\63\61\65\43\x57\x69\164\x68\x43\157\155\x6d\x65\x6e\164\163";
    const EXC_C14N = "\150\x74\164\x70\72\x2f\57\x77\167\167\56\x77\63\56\157\162\147\x2f\x32\x30\x30\x31\57\61\x30\x2f\170\155\x6c\x2d\145\x78\x63\x2d\x63\x31\x34\x6e\x23";
    const EXC_C14N_COMMENTS = "\x68\164\164\x70\x3a\57\57\x77\167\x77\56\x77\x33\56\157\162\x67\x2f\62\60\x30\x31\57\61\60\57\x78\x6d\154\55\145\x78\x63\55\143\61\64\156\43\x57\151\x74\x68\103\x6f\155\x6d\145\156\164\x73";
    const template = "\74\x64\163\x3a\123\x69\147\x6e\x61\164\165\162\145\40\x78\155\154\156\163\72\x64\x73\75\42\150\x74\164\160\x3a\57\x2f\167\167\167\56\167\x33\x2e\x6f\162\147\x2f\62\60\60\60\x2f\60\x39\57\170\x6d\x6c\x64\x73\151\147\43\42\x3e\15\12\x20\40\x3c\x64\163\72\123\151\147\x6e\x65\x64\x49\156\146\157\76\xd\xa\40\x20\x20\40\x3c\x64\x73\72\123\x69\147\x6e\141\164\x75\162\145\115\x65\x74\150\x6f\x64\x20\x2f\x3e\15\12\40\x20\x3c\x2f\144\163\72\x53\151\147\156\145\x64\111\156\x66\x6f\x3e\xd\xa\74\57\x64\163\72\123\151\147\156\x61\164\165\162\x65\76";
    public $sigNode = NULL;
    public $idKeys = array();
    public $idNS = array();
    private $signedInfo = NULL;
    private $xPathCtx = NULL;
    private $canonicalMethod = NULL;
    private $prefix = "\x64\163";
    private $searchpfx = "\163\145\143\x64\163\151\147";
    private $validatedNodes = NULL;
    public function __construct()
    {
        $Q7 = new DOMDocument();
        $Q7->loadXML(XMLSecurityDSigSAML::template);
        $this->sigNode = $Q7->documentElement;
    }
    private function resetXPathObj()
    {
        $this->xPathCtx = NULL;
    }
    private function getXPathObj()
    {
        if (!(empty($this->xPathCtx) && !empty($this->sigNode))) {
            goto kC;
        }
        $o7 = new DOMXPath($this->sigNode->ownerDocument);
        $o7->registerNamespace("\x73\x65\x63\x64\x73\151\x67", XMLSecurityDSigSAML::XMLDSIGNS);
        $this->xPathCtx = $o7;
        kC:
        return $this->xPathCtx;
    }
    static function generate_GUID($Bv = "\160\x66\170")
    {
        $WW = md5(uniqid(rand(), true));
        $p_ = $Bv . substr($WW, 0, 8) . "\x2d" . substr($WW, 8, 4) . "\x2d" . substr($WW, 12, 4) . "\55" . substr($WW, 16, 4) . "\55" . substr($WW, 20, 12);
        return $p_;
    }
    public function locateSignature($lk)
    {
        if ($lk instanceof DOMDocument) {
            goto vT;
        }
        $JK = $lk->ownerDocument;
        goto aF;
        vT:
        $JK = $lk;
        aF:
        if (!$JK) {
            goto Fd;
        }
        $o7 = new DOMXPath($JK);
        $o7->registerNamespace("\163\145\x63\x64\x73\x69\147", XMLSecurityDSigSAML::XMLDSIGNS);
        $fu = "\56\x2f\x2f\x73\x65\x63\144\163\x69\x67\x3a\123\x69\147\x6e\x61\x74\x75\x72\145";
        $mn = $o7->query($fu, $lk);
        $this->sigNode = $mn->item(0);
        return $this->sigNode;
        Fd:
        return NULL;
    }
    public function createNewSignNode($k4, $w7 = NULL)
    {
        $JK = $this->sigNode->ownerDocument;
        if (!is_null($w7)) {
            goto fw;
        }
        $mj = $JK->createElementNS(XMLSecurityDSigSAML::XMLDSIGNS, $this->prefix . "\72" . $k4);
        goto Oi;
        fw:
        $mj = $JK->createElementNS(XMLSecurityDSigSAML::XMLDSIGNS, $this->prefix . "\x3a" . $k4, $w7);
        Oi:
        return $mj;
    }
    public function setCanonicalMethod($xp)
    {
        switch ($xp) {
            case "\x68\x74\164\160\x3a\57\x2f\x77\167\x77\56\x77\63\x2e\157\162\147\57\124\x52\57\x32\x30\60\61\x2f\x52\x45\103\55\x78\155\154\x2d\x63\x31\64\156\x2d\x32\x30\x30\x31\x30\63\61\65":
            case "\x68\164\x74\x70\72\x2f\57\167\x77\167\x2e\167\x33\56\x6f\162\x67\x2f\124\x52\57\x32\x30\60\x31\x2f\122\x45\103\x2d\170\155\154\x2d\x63\x31\x34\x6e\55\x32\x30\x30\61\60\63\61\x35\43\127\151\164\150\103\x6f\x6d\x6d\145\x6e\164\x73":
            case "\x68\164\164\x70\72\57\57\x77\167\x77\56\167\63\56\157\x72\147\57\62\60\x30\61\x2f\61\x30\57\170\x6d\x6c\x2d\x65\170\x63\x2d\143\61\64\156\x23":
            case "\150\164\x74\160\72\x2f\x2f\x77\167\x77\56\x77\63\x2e\x6f\x72\x67\x2f\62\x30\x30\x31\x2f\x31\x30\57\x78\155\x6c\55\145\x78\143\x2d\143\x31\64\x6e\x23\x57\151\164\150\x43\x6f\155\x6d\145\x6e\164\x73":
                $this->canonicalMethod = $xp;
                goto pW;
            default:
                throw new Exception("\x49\156\x76\x61\x6c\151\x64\40\x43\141\x6e\157\x6e\x69\143\x61\154\40\115\x65\x74\150\157\x64");
        }
        wn:
        pW:
        if (!($o7 = $this->getXPathObj())) {
            goto ec;
        }
        $fu = "\x2e\x2f" . $this->searchpfx . "\x3a\x53\x69\147\156\x65\x64\x49\156\x66\x6f";
        $mn = $o7->query($fu, $this->sigNode);
        if (!($TZ = $mn->item(0))) {
            goto ln;
        }
        $fu = "\x2e\57" . $this->searchpfx . "\x43\141\156\x6f\156\x69\x63\x61\x6c\151\172\x61\164\x69\157\x6e\115\145\164\x68\157\144";
        $mn = $o7->query($fu, $TZ);
        if ($ix = $mn->item(0)) {
            goto Fu;
        }
        $ix = $this->createNewSignNode("\103\141\156\157\156\x69\143\141\x6c\151\172\x61\x74\151\x6f\156\115\145\164\x68\157\144");
        $TZ->insertBefore($ix, $TZ->firstChild);
        Fu:
        $ix->setAttribute("\101\154\147\x6f\162\x69\x74\150\x6d", $this->canonicalMethod);
        ln:
        ec:
    }
    private function canonicalizeData($mj, $v2, $cd = NULL, $Cv = NULL)
    {
        $y6 = FALSE;
        $Vf = FALSE;
        switch ($v2) {
            case "\x68\x74\x74\160\x3a\57\x2f\167\167\167\56\x77\63\x2e\x6f\162\x67\57\x54\x52\x2f\x32\x30\x30\61\x2f\x52\105\x43\x2d\170\x6d\154\55\x63\x31\x34\156\x2d\62\60\60\61\x30\x33\x31\x35":
                $y6 = FALSE;
                $Vf = FALSE;
                goto AH;
            case "\150\x74\x74\x70\x3a\57\x2f\x77\x77\x77\x2e\x77\x33\x2e\157\x72\147\57\x54\122\57\62\x30\x30\61\x2f\122\105\103\55\x78\155\x6c\55\x63\61\x34\156\x2d\62\x30\60\x31\60\63\61\65\43\x57\x69\x74\x68\103\x6f\155\155\145\156\164\x73":
                $Vf = TRUE;
                goto AH;
            case "\x68\164\164\x70\x3a\x2f\57\x77\167\x77\x2e\x77\63\x2e\x6f\162\147\57\x32\x30\60\x31\57\61\60\57\x78\155\x6c\55\x65\170\143\x2d\x63\x31\x34\x6e\43":
                $y6 = TRUE;
                goto AH;
            case "\x68\x74\164\160\x3a\57\x2f\167\x77\x77\56\167\x33\x2e\x6f\x72\147\57\x32\x30\60\x31\x2f\x31\60\x2f\170\x6d\154\x2d\x65\x78\x63\x2d\143\61\x34\156\43\127\x69\x74\x68\103\x6f\x6d\155\x65\156\164\163":
                $y6 = TRUE;
                $Vf = TRUE;
                goto AH;
        }
        zY:
        AH:
        $sC = explode("\56", PHP_VERSION);
        if (!($sC[0] < 5 || $sC[0] == 5 && $sC[1] < 2)) {
            goto pb;
        }
        if (is_null($cd)) {
            goto N8;
        }
        throw new Exception("\120\110\120\x20\65\56\x32\x2e\x30\40\157\x72\x20\150\x69\x67\x68\145\162\x20\x69\163\x20\x72\145\x71\x75\151\162\145\x64\x20\164\x6f\40\160\x65\162\146\157\162\155\40\x58\x50\141\x74\150\40\x54\162\141\156\x73\x66\x6f\x72\155\x61\164\x69\x6f\156\163");
        N8:
        return C14NGeneral($mj, $y6, $Vf);
        pb:
        $g3 = $mj;
        if (!($mj instanceof DOMNode && $mj->ownerDocument !== NULL && $mj->isSameNode($mj->ownerDocument->documentElement))) {
            goto Kx;
        }
        $g3 = $mj->ownerDocument;
        Kx:
        return $g3->C14N($y6, $Vf, $cd, $Cv);
    }
    public function canonicalizeSignedInfo()
    {
        $JK = $this->sigNode->ownerDocument;
        $v2 = NULL;
        if (!$JK) {
            goto O9;
        }
        $o7 = $this->getXPathObj();
        $fu = "\56\57\x73\145\x63\x64\x73\151\147\72\123\x69\x67\x6e\x65\144\111\156\x66\x6f";
        $mn = $o7->query($fu, $this->sigNode);
        if (!($fq = $mn->item(0))) {
            goto nF;
        }
        $fu = "\56\x2f\x73\145\143\144\x73\151\x67\72\x43\x61\156\157\x6e\151\143\x61\154\x69\172\x61\164\151\x6f\156\x4d\145\x74\150\x6f\x64";
        $mn = $o7->query($fu, $fq);
        if (!($ix = $mn->item(0))) {
            goto dB;
        }
        $v2 = $ix->getAttribute("\101\x6c\147\157\x72\x69\164\x68\155");
        dB:
        $this->signedInfo = $this->canonicalizeData($fq, $v2);
        return $this->signedInfo;
        nF:
        O9:
        return NULL;
    }
    public function calculateDigest($H9, $oa)
    {
        switch ($H9) {
            case XMLSecurityDSigSAML::SHA1:
                $Zs = "\163\x68\x61\x31";
                goto Ai;
            case XMLSecurityDSigSAML::SHA256:
                $Zs = "\x73\x68\141\x32\x35\66";
                goto Ai;
            case XMLSecurityDSigSAML::SHA384:
                $Zs = "\x73\x68\x61\63\x38\64";
                goto Ai;
            case XMLSecurityDSigSAML::SHA512:
                $Zs = "\x73\150\x61\x35\61\x32";
                goto Ai;
            case XMLSecurityDSigSAML::RIPEMD160:
                $Zs = "\162\x69\160\145\155\x64\x31\66\x30";
                goto Ai;
            default:
                throw new Exception("\x43\141\x6e\x6e\157\164\40\x76\x61\x6c\x69\x64\x61\164\145\40\144\151\147\145\163\x74\72\40\x55\156\x73\165\x70\160\157\x72\x74\x65\x64\40\x41\154\147\x6f\x72\x69\x74\x68\x20\74{$H9}\76");
        }
        hB:
        Ai:
        if (function_exists("\150\x61\163\x68")) {
            goto Tu;
        }
        if (function_exists("\x6d\x68\141\163\x68")) {
            goto ZY;
        }
        if ($Zs === "\x73\150\141\x31") {
            goto rn;
        }
        throw new Exception("\x78\x6d\x6c\x73\x65\x63\154\151\142\163\x20\x69\x73\40\x75\x6e\141\142\154\x65\x20\164\x6f\x20\143\141\154\143\x75\154\x61\164\145\x20\141\40\144\x69\147\145\163\164\x2e\x20\x4d\x61\x79\x62\145\x20\x79\157\x75\x20\x6e\145\145\144\40\164\150\145\x20\155\150\x61\163\x68\x20\x6c\x69\142\x72\141\162\x79\x3f");
        goto B3;
        Tu:
        return base64_encode(hash($Zs, $oa, TRUE));
        goto B3;
        ZY:
        $Zs = "\115\x48\101\123\x48\137" . strtoupper($Zs);
        return base64_encode(mhash(constant($Zs), $oa));
        goto B3;
        rn:
        return base64_encode(sha1($oa, TRUE));
        B3:
    }
    public function validateDigest($Kn, $oa)
    {
        $o7 = new DOMXPath($Kn->ownerDocument);
        $o7->registerNamespace("\x73\x65\x63\144\x73\x69\x67", XMLSecurityDSigSAML::XMLDSIGNS);
        $fu = "\163\164\162\x69\156\147\x28\56\x2f\x73\x65\143\x64\x73\151\147\72\104\x69\147\145\x73\x74\x4d\x65\164\150\x6f\x64\x2f\100\x41\x6c\147\157\x72\151\x74\150\155\51";
        $H9 = $o7->evaluate($fu, $Kn);
        $cN = $this->calculateDigest($H9, $oa);
        $fu = "\163\164\x72\x69\x6e\147\x28\x2e\x2f\163\145\x63\x64\x73\151\x67\72\x44\151\x67\145\x73\164\126\x61\x6c\x75\145\51";
        $Vp = $o7->evaluate($fu, $Kn);
        return $cN == $Vp;
    }
    public function processTransforms($Kn, $I6, $go = TRUE)
    {
        $oa = $I6;
        $o7 = new DOMXPath($Kn->ownerDocument);
        $o7->registerNamespace("\163\145\143\144\x73\151\x67", XMLSecurityDSigSAML::XMLDSIGNS);
        $fu = "\56\57\x73\145\x63\144\163\x69\147\x3a\x54\162\141\x6e\163\x66\x6f\x72\x6d\x73\57\163\x65\143\x64\163\x69\147\x3a\124\x72\141\x6e\163\x66\157\x72\155";
        $or = $o7->query($fu, $Kn);
        $UT = "\x68\x74\x74\x70\72\x2f\x2f\x77\x77\167\x2e\x77\x33\56\x6f\x72\147\57\124\122\57\62\x30\60\x31\x2f\122\105\x43\55\170\155\154\55\x63\x31\x34\156\x2d\62\60\x30\61\60\63\61\65";
        $cd = NULL;
        $Cv = NULL;
        foreach ($or as $s0) {
            $eo = $s0->getAttribute("\x41\154\x67\157\x72\x69\164\x68\155");
            switch ($eo) {
                case "\150\x74\164\160\x3a\57\x2f\x77\x77\167\56\167\63\56\x6f\x72\147\x2f\62\x30\60\x31\57\61\x30\x2f\x78\155\154\55\x65\x78\143\x2d\x63\61\64\156\x23":
                case "\x68\164\164\x70\x3a\x2f\57\167\167\167\x2e\167\x33\x2e\x6f\162\147\x2f\62\60\60\61\x2f\x31\60\x2f\170\x6d\x6c\x2d\145\170\x63\x2d\143\x31\x34\x6e\43\127\151\x74\x68\x43\x6f\155\155\145\156\164\163":
                    if (!$go) {
                        goto sY;
                    }
                    $UT = $eo;
                    goto Lq;
                    sY:
                    $UT = "\x68\164\x74\160\72\x2f\x2f\167\x77\167\x2e\x77\x33\56\157\x72\147\x2f\62\60\60\x31\x2f\x31\60\x2f\x78\x6d\154\55\145\x78\143\x2d\143\x31\64\x6e\x23";
                    Lq:
                    $mj = $s0->firstChild;
                    DY:
                    if (!$mj) {
                        goto sO;
                    }
                    if (!($mj->localName == "\111\x6e\x63\154\165\163\151\x76\145\116\x61\155\x65\163\160\141\143\x65\x73")) {
                        goto ZE;
                    }
                    if (!($Ra = $mj->getAttribute("\120\162\145\146\151\170\x4c\x69\163\x74"))) {
                        goto zS;
                    }
                    $zh = array();
                    $AK = explode("\40", $Ra);
                    foreach ($AK as $Ra) {
                        $ZR = trim($Ra);
                        if (empty($ZR)) {
                            goto gu;
                        }
                        $zh[] = $ZR;
                        gu:
                        K4:
                    }
                    ZK:
                    if (!(count($zh) > 0)) {
                        goto vd;
                    }
                    $Cv = $zh;
                    vd:
                    zS:
                    goto sO;
                    ZE:
                    $mj = $mj->nextSibling;
                    goto DY;
                    sO:
                    goto dh;
                case "\150\164\x74\x70\x3a\x2f\x2f\x77\167\167\x2e\167\x33\x2e\157\162\147\x2f\124\122\57\x32\x30\x30\x31\x2f\x52\x45\103\x2d\170\155\x6c\x2d\143\x31\x34\156\55\62\x30\x30\61\x30\x33\61\x35":
                case "\150\x74\164\160\x3a\57\x2f\x77\x77\167\56\167\x33\56\x6f\162\147\x2f\x54\x52\x2f\62\x30\x30\x31\x2f\x52\105\x43\55\170\x6d\154\55\x63\61\x34\x6e\55\62\60\x30\61\60\63\61\65\x23\x57\x69\164\150\x43\x6f\155\155\145\156\x74\163":
                    if (!$go) {
                        goto u_;
                    }
                    $UT = $eo;
                    goto j6;
                    u_:
                    $UT = "\150\164\x74\x70\72\57\57\167\167\x77\56\x77\63\x2e\157\x72\147\57\x54\x52\57\62\60\x30\61\x2f\x52\105\103\x2d\170\155\x6c\x2d\143\x31\64\156\x2d\x32\x30\60\61\60\x33\61\x35";
                    j6:
                    goto dh;
                case "\150\164\164\160\72\x2f\57\x77\167\x77\x2e\167\x33\x2e\157\162\147\57\124\x52\57\61\71\71\x39\57\x52\x45\103\55\x78\x70\x61\164\150\55\61\71\x39\71\x31\61\61\66":
                    $mj = $s0->firstChild;
                    Ay:
                    if (!$mj) {
                        goto JJ;
                    }
                    if (!($mj->localName == "\130\120\141\164\x68")) {
                        goto fC;
                    }
                    $cd = array();
                    $cd["\161\165\x65\x72\x79"] = "\50\56\x2f\x2f\x2e\x20\174\40\x2e\57\57\100\52\x20\x7c\x20\x2e\57\57\x6e\x61\155\x65\163\160\141\143\145\72\x3a\x2a\51\x5b" . $mj->nodeValue . "\x5d";
                    $j4["\156\x61\x6d\x65\x73\x70\x61\143\x65\x73"] = array();
                    $GK = $o7->query("\x2e\57\156\x61\x6d\145\163\x70\141\143\x65\72\x3a\x2a", $mj);
                    foreach ($GK as $DZ) {
                        if (!($DZ->localName != "\170\155\154")) {
                            goto qj;
                        }
                        $cd["\156\x61\x6d\x65\x73\x70\x61\x63\x65\x73"][$DZ->localName] = $DZ->nodeValue;
                        qj:
                        Yq:
                    }
                    HM:
                    goto JJ;
                    fC:
                    $mj = $mj->nextSibling;
                    goto Ay;
                    JJ:
                    goto dh;
            }
            cd:
            dh:
            oX:
        }
        Qk:
        if (!$oa instanceof DOMNode) {
            goto BL;
        }
        $oa = $this->canonicalizeData($I6, $UT, $cd, $Cv);
        BL:
        return $oa;
    }
    public function processRefNode($Kn)
    {
        $vB = NULL;
        $go = TRUE;
        if ($GX = $Kn->getAttribute("\x55\x52\111")) {
            goto xM;
        }
        $go = FALSE;
        $vB = $Kn->ownerDocument;
        goto I7;
        xM:
        $vx = parse_url($GX);
        if (empty($vx["\160\141\164\150"])) {
            goto Og;
        }
        $vB = file_get_contents($vx);
        goto W4;
        Og:
        if ($Dy = $vx["\146\162\141\147\155\145\x6e\164"]) {
            goto Sw;
        }
        $vB = $Kn->ownerDocument;
        goto ry;
        Sw:
        $go = FALSE;
        $rG = new DOMXPath($Kn->ownerDocument);
        if (!($this->idNS && is_array($this->idNS))) {
            goto Uq;
        }
        foreach ($this->idNS as $iR => $kK) {
            $rG->registerNamespace($iR, $kK);
            LH:
        }
        q3:
        Uq:
        $iC = "\x40\111\144\x3d\42" . $Dy . "\42";
        if (!is_array($this->idKeys)) {
            goto py;
        }
        foreach ($this->idKeys as $cw) {
            $iC .= "\40\157\x72\40\100{$cw}\75\x27{$Dy}\47";
            vm:
        }
        xj:
        py:
        $fu = "\57\57\52\133" . $iC . "\135";
        $vB = $rG->query($fu)->item(0);
        ry:
        W4:
        I7:
        $oa = $this->processTransforms($Kn, $vB, $go);
        if ($this->validateDigest($Kn, $oa)) {
            goto lE;
        }
        return FALSE;
        lE:
        if (!$vB instanceof DOMNode) {
            goto PP;
        }
        if (!empty($Dy)) {
            goto gH;
        }
        $this->validatedNodes[] = $vB;
        goto SR;
        gH:
        $this->validatedNodes[$Dy] = $vB;
        SR:
        PP:
        return TRUE;
    }
    public function getRefNodeID($Kn)
    {
        if (!($GX = $Kn->getAttribute("\125\122\x49"))) {
            goto j0;
        }
        $vx = parse_url($GX);
        if (!empty($vx["\x70\x61\164\x68"])) {
            goto YO;
        }
        if (!($Dy = $vx["\x66\162\x61\x67\x6d\x65\x6e\x74"])) {
            goto oK;
        }
        return $Dy;
        oK:
        YO:
        j0:
        return null;
    }
    public function getRefIDs()
    {
        $OH = array();
        $JK = $this->sigNode->ownerDocument;
        $o7 = $this->getXPathObj();
        $fu = "\x2e\57\x73\145\143\x64\x73\x69\x67\x3a\123\x69\147\156\145\144\x49\x6e\146\157\x2f\x73\x65\x63\x64\x73\151\147\x3a\122\x65\146\145\162\x65\x6e\x63\145";
        $mn = $o7->query($fu, $this->sigNode);
        if (!($mn->length == 0)) {
            goto GF;
        }
        throw new Exception("\x52\145\x66\x65\162\x65\x6e\x63\x65\40\x6e\157\144\x65\163\x20\156\157\x74\40\x66\x6f\x75\x6e\x64");
        GF:
        foreach ($mn as $Kn) {
            $OH[] = $this->getRefNodeID($Kn);
            bW:
        }
        to:
        return $OH;
    }
    public function validateReference()
    {
        $JK = $this->sigNode->ownerDocument;
        if ($JK->isSameNode($this->sigNode)) {
            goto xZ;
        }
        $this->sigNode->parentNode->removeChild($this->sigNode);
        xZ:
        $o7 = $this->getXPathObj();
        $fu = "\x2e\x2f\x73\145\x63\x64\163\151\147\x3a\123\151\147\x6e\145\144\x49\x6e\x66\x6f\x2f\163\145\x63\144\163\x69\x67\x3a\122\x65\x66\x65\162\x65\156\x63\x65";
        $mn = $o7->query($fu, $this->sigNode);
        if (!($mn->length == 0)) {
            goto yF;
        }
        throw new Exception("\x52\145\146\x65\162\145\x6e\x63\145\x20\156\x6f\x64\x65\163\x20\x6e\x6f\164\x20\x66\x6f\165\156\144");
        yF:
        $this->validatedNodes = array();
        foreach ($mn as $Kn) {
            if ($this->processRefNode($Kn)) {
                goto IL;
            }
            $this->validatedNodes = NULL;
            throw new Exception("\x52\x65\x66\145\x72\x65\156\x63\x65\40\166\141\x6c\x69\x64\141\x74\151\157\x6e\x20\x66\x61\x69\x6c\x65\x64");
            IL:
            md:
        }
        jC:
        return TRUE;
    }
    private function addRefInternal($oN, $mj, $eo, $er = NULL, $Ef = NULL)
    {
        $Bv = NULL;
        $pH = NULL;
        $wH = "\x49\x64";
        $O1 = TRUE;
        $EK = FALSE;
        if (!is_array($Ef)) {
            goto dm;
        }
        $Bv = empty($Ef["\x70\x72\145\x66\151\170"]) ? NULL : $Ef["\160\x72\x65\x66\151\x78"];
        $pH = empty($Ef["\x70\x72\145\x66\151\x78\x5f\x6e\163"]) ? NULL : $Ef["\x70\x72\145\x66\151\x78\137\156\x73"];
        $wH = empty($Ef["\151\144\137\156\x61\x6d\x65"]) ? "\111\144" : $Ef["\151\144\137\156\x61\x6d\145"];
        $O1 = !isset($Ef["\x6f\166\145\162\x77\x72\x69\164\x65"]) ? TRUE : (bool) $Ef["\x6f\x76\x65\x72\167\x72\x69\x74\145"];
        $EK = !isset($Ef["\x66\157\x72\x63\x65\137\165\162\151"]) ? FALSE : (bool) $Ef["\146\x6f\x72\x63\x65\137\165\162\x69"];
        dm:
        $ie = $wH;
        if (empty($Bv)) {
            goto ax;
        }
        $ie = $Bv . "\72" . $ie;
        ax:
        $Kn = $this->createNewSignNode("\x52\145\146\145\162\x65\x6e\143\145");
        $oN->appendChild($Kn);
        if (!$mj instanceof DOMDocument) {
            goto un;
        }
        if ($EK) {
            goto FV;
        }
        goto Kt;
        un:
        $GX = NULL;
        if ($O1) {
            goto xr;
        }
        $GX = $mj->getAttributeNS($pH, $wH);
        xr:
        if (!empty($GX)) {
            goto Hw;
        }
        $GX = XMLSecurityDSigSAML::generate_GUID();
        $mj->setAttributeNS($pH, $ie, $GX);
        Hw:
        $Kn->setAttribute("\125\122\111", "\43" . $GX);
        goto Kt;
        FV:
        $Kn->setAttribute("\125\x52\x49", '');
        Kt:
        $sh = $this->createNewSignNode("\124\162\141\156\163\x66\x6f\162\155\x73");
        $Kn->appendChild($sh);
        if (is_array($er)) {
            goto gy;
        }
        if (!empty($this->canonicalMethod)) {
            goto bP;
        }
        goto qZ;
        gy:
        foreach ($er as $s0) {
            $V5 = $this->createNewSignNode("\x54\x72\141\156\163\x66\x6f\162\x6d");
            $sh->appendChild($V5);
            if (is_array($s0) && !empty($s0["\150\164\164\160\72\x2f\x2f\x77\167\x77\x2e\x77\63\x2e\x6f\x72\147\x2f\x54\122\57\61\71\71\x39\x2f\122\105\103\55\x78\x70\141\164\150\55\x31\71\x39\71\x31\x31\61\66"]) && !empty($s0["\x68\x74\164\x70\72\57\57\167\167\167\56\x77\63\x2e\x6f\x72\x67\x2f\124\x52\57\x31\71\71\x39\57\122\105\x43\x2d\x78\x70\141\x74\x68\55\61\x39\71\71\61\61\61\x36"]["\161\165\145\162\x79"])) {
                goto ei;
            }
            $V5->setAttribute("\101\154\x67\x6f\162\x69\164\x68\x6d", $s0);
            goto iZ;
            ei:
            $V5->setAttribute("\x41\x6c\x67\157\x72\151\164\150\155", "\150\164\x74\160\x3a\57\x2f\167\x77\x77\56\x77\x33\x2e\x6f\x72\147\x2f\x54\122\x2f\x31\x39\71\71\x2f\x52\x45\x43\55\x78\160\141\x74\150\x2d\61\x39\71\71\61\61\61\x36");
            $va = $this->createNewSignNode("\130\x50\141\164\x68", $s0["\x68\164\164\160\72\57\57\167\x77\x77\x2e\x77\x33\56\157\162\x67\57\x54\x52\57\x31\x39\x39\71\x2f\122\x45\x43\55\x78\160\x61\x74\x68\x2d\x31\71\71\x39\x31\61\x31\66"]["\x71\165\x65\x72\171"]);
            $V5->appendChild($va);
            if (empty($s0["\x68\x74\164\x70\72\57\57\x77\167\x77\x2e\167\63\56\157\x72\x67\57\124\x52\x2f\61\71\x39\x39\57\x52\105\x43\x2d\170\x70\x61\164\150\x2d\x31\x39\x39\71\61\61\61\x36"]["\x6e\141\x6d\x65\x73\x70\x61\x63\145\163"])) {
                goto OB;
            }
            foreach ($s0["\x68\x74\x74\x70\72\x2f\57\167\167\x77\56\167\63\x2e\157\162\x67\57\124\x52\57\61\x39\x39\71\x2f\122\x45\x43\55\x78\x70\x61\164\150\55\x31\x39\x39\71\x31\x31\x31\x36"]["\x6e\141\155\x65\163\x70\x61\143\145\x73"] as $Bv => $e6) {
                $va->setAttributeNS("\150\164\164\x70\72\57\x2f\x77\167\x77\x2e\167\x33\56\157\x72\x67\x2f\x32\60\60\60\57\x78\155\154\x6e\x73\57", "\170\155\154\x6e\x73\72{$Bv}", $e6);
                zd:
            }
            s1:
            OB:
            iZ:
            bA:
        }
        sc:
        goto qZ;
        bP:
        $V5 = $this->createNewSignNode("\124\x72\x61\156\163\x66\157\162\x6d");
        $sh->appendChild($V5);
        $V5->setAttribute("\101\x6c\x67\x6f\x72\x69\x74\x68\x6d", $this->canonicalMethod);
        qZ:
        $yw = $this->processTransforms($Kn, $mj);
        $cN = $this->calculateDigest($eo, $yw);
        $DG = $this->createNewSignNode("\x44\x69\147\x65\x73\x74\x4d\x65\164\x68\x6f\x64");
        $Kn->appendChild($DG);
        $DG->setAttribute("\x41\154\x67\x6f\x72\151\164\x68\x6d", $eo);
        $Vp = $this->createNewSignNode("\104\151\147\x65\163\164\126\141\x6c\165\x65", $cN);
        $Kn->appendChild($Vp);
    }
    public function addReference($mj, $eo, $er = NULL, $Ef = NULL)
    {
        if (!($o7 = $this->getXPathObj())) {
            goto aW;
        }
        $fu = "\x2e\x2f\x73\x65\x63\x64\163\151\147\72\123\x69\147\156\x65\144\111\156\146\157";
        $mn = $o7->query($fu, $this->sigNode);
        if (!($Hy = $mn->item(0))) {
            goto tb;
        }
        $this->addRefInternal($Hy, $mj, $eo, $er, $Ef);
        tb:
        aW:
    }
    public function addReferenceList($KZ, $eo, $er = NULL, $Ef = NULL)
    {
        if (!($o7 = $this->getXPathObj())) {
            goto H_;
        }
        $fu = "\x2e\x2f\163\x65\143\144\x73\151\147\x3a\x53\151\147\156\x65\x64\111\x6e\146\157";
        $mn = $o7->query($fu, $this->sigNode);
        if (!($Hy = $mn->item(0))) {
            goto R4;
        }
        foreach ($KZ as $mj) {
            $this->addRefInternal($Hy, $mj, $eo, $er, $Ef);
            h3:
        }
        Mm:
        R4:
        H_:
    }
    public function addObject($oa, $Oj = NULL, $Ie = NULL)
    {
        $Lh = $this->createNewSignNode("\x4f\x62\152\145\x63\164");
        $this->sigNode->appendChild($Lh);
        if (empty($Oj)) {
            goto ZJ;
        }
        $Lh->setAtribute("\115\151\x6d\145\124\171\160\145", $Oj);
        ZJ:
        if (empty($Ie)) {
            goto S8;
        }
        $Lh->setAttribute("\105\x6e\x63\x6f\x64\151\156\147", $Ie);
        S8:
        if ($oa instanceof DOMElement) {
            goto PQ;
        }
        $iN = $this->sigNode->ownerDocument->createTextNode($oa);
        goto kn;
        PQ:
        $iN = $this->sigNode->ownerDocument->importNode($oa, TRUE);
        kn:
        $Lh->appendChild($iN);
        return $Lh;
    }
    public function locateKey($mj = NULL)
    {
        if (!empty($mj)) {
            goto Kw;
        }
        $mj = $this->sigNode;
        Kw:
        if ($mj instanceof DOMNode) {
            goto Ij;
        }
        return NULL;
        Ij:
        if (!($JK = $mj->ownerDocument)) {
            goto Iz;
        }
        $o7 = new DOMXPath($JK);
        $o7->registerNamespace("\x73\145\143\x64\x73\x69\x67", XMLSecurityDSigSAML::XMLDSIGNS);
        $fu = "\x73\x74\x72\151\x6e\x67\50\x2e\57\163\x65\143\144\x73\151\147\72\x53\151\147\x6e\x65\144\x49\156\x66\157\x2f\163\145\143\144\x73\151\x67\x3a\123\151\x67\156\141\x74\x75\x72\x65\x4d\145\x74\150\x6f\144\57\100\x41\x6c\147\157\x72\x69\x74\150\x6d\x29";
        $eo = $o7->evaluate($fu, $mj);
        if (!$eo) {
            goto sv;
        }
        try {
            $qZ = new XMLSecurityKeySAML($eo, array("\164\171\x70\x65" => "\x70\165\142\x6c\151\x63"));
        } catch (Exception $yV) {
            return NULL;
        }
        return $qZ;
        sv:
        Iz:
        return NULL;
    }
    public function verify($qZ)
    {
        $JK = $this->sigNode->ownerDocument;
        $o7 = new DOMXPath($JK);
        $o7->registerNamespace("\163\145\143\144\x73\151\x67", XMLSecurityDSigSAML::XMLDSIGNS);
        $fu = "\x73\164\162\151\x6e\147\50\x2e\x2f\x73\x65\x63\x64\163\151\147\72\x53\151\147\156\x61\164\x75\162\x65\126\141\154\165\145\x29";
        $J8 = $o7->evaluate($fu, $this->sigNode);
        if (!empty($J8)) {
            goto Uf;
        }
        throw new Exception("\x55\156\x61\x62\154\145\40\164\x6f\40\154\x6f\x63\x61\x74\145\40\123\x69\x67\156\x61\x74\x75\162\x65\126\141\154\165\x65");
        Uf:
        return $qZ->verifySignature($this->signedInfo, base64_decode($J8));
    }
    public function signData($qZ, $oa)
    {
        return $qZ->signData($oa);
    }
    public function sign($qZ, $Ov = NULL)
    {
        if (!($Ov != NULL)) {
            goto E3;
        }
        $this->resetXPathObj();
        $this->appendSignature($Ov);
        $this->sigNode = $Ov->lastChild;
        E3:
        if (!($o7 = $this->getXPathObj())) {
            goto dF;
        }
        $fu = "\56\57\x73\145\143\144\163\151\x67\72\x53\151\x67\156\x65\x64\111\156\146\x6f";
        $mn = $o7->query($fu, $this->sigNode);
        if (!($Hy = $mn->item(0))) {
            goto Vb;
        }
        $fu = "\56\57\x73\x65\x63\144\x73\x69\147\72\123\x69\147\156\141\x74\x75\x72\145\x4d\x65\x74\150\x6f\144";
        $mn = $o7->query($fu, $Hy);
        $H2 = $mn->item(0);
        $H2->setAttribute("\x41\154\x67\157\162\x69\x74\x68\x6d", $qZ->type);
        $oa = $this->canonicalizeData($Hy, $this->canonicalMethod);
        $J8 = base64_encode($this->signData($qZ, $oa));
        $EY = $this->createNewSignNode("\123\x69\x67\x6e\x61\164\x75\162\x65\126\x61\154\x75\145", $J8);
        if ($c3 = $Hy->nextSibling) {
            goto vH;
        }
        $this->sigNode->appendChild($EY);
        goto cK;
        vH:
        $c3->parentNode->insertBefore($EY, $c3);
        cK:
        Vb:
        dF:
    }
    public function appendCert()
    {
    }
    public function appendKey($qZ, $cF = NULL)
    {
        $qZ->serializeKey($cF);
    }
    public function insertSignature($mj, $uR = NULL)
    {
        $jI = $mj->ownerDocument;
        $Ir = $jI->importNode($this->sigNode, TRUE);
        if ($uR == NULL) {
            goto Ej;
        }
        return $mj->insertBefore($Ir, $uR);
        goto b0;
        Ej:
        return $mj->insertBefore($Ir);
        b0:
    }
    public function appendSignature($hA, $HB = FALSE)
    {
        $uR = $HB ? $hA->firstChild : NULL;
        return $this->insertSignature($hA, $uR);
    }
    static function get509XCert($J2, $U5 = TRUE)
    {
        $Sw = XMLSecurityDSigSAML::staticGet509XCerts($J2, $U5);
        if (empty($Sw)) {
            goto nM;
        }
        return $Sw[0];
        nM:
        return '';
    }
    static function staticGet509XCerts($Sw, $U5 = TRUE)
    {
        if ($U5) {
            goto jo;
        }
        return array($Sw);
        goto k6;
        jo:
        $oa = '';
        $T3 = array();
        $QN = explode("\12", $Sw);
        $E7 = FALSE;
        foreach ($QN as $Iw) {
            if (!$E7) {
                goto jv;
            }
            if (!(strncmp($Iw, "\55\x2d\x2d\55\x2d\x45\x4e\104\40\103\105\x52\124\x49\106\111\103\101\124\x45", 20) == 0)) {
                goto EK;
            }
            $E7 = FALSE;
            $T3[] = $oa;
            $oa = '';
            goto PL;
            EK:
            $oa .= trim($Iw);
            goto Du;
            jv:
            if (!(strncmp($Iw, "\x2d\55\55\55\x2d\x42\x45\107\111\116\40\x43\x45\122\x54\111\x46\111\x43\101\x54\105", 22) == 0)) {
                goto Wb;
            }
            $E7 = TRUE;
            Wb:
            Du:
            PL:
        }
        YN:
        return $T3;
        k6:
    }
    static function staticAdd509Cert($Cp, $J2, $U5 = TRUE, $Pi = False, $o7 = NULL, $Ef = NULL)
    {
        if (!$Pi) {
            goto yn;
        }
        $J2 = file_get_contents($J2);
        yn:
        if ($Cp instanceof DOMElement) {
            goto eD;
        }
        throw new Exception("\x49\x6e\166\141\154\x69\x64\x20\x70\141\162\x65\156\164\x20\x4e\x6f\144\145\x20\160\x61\x72\x61\155\145\164\x65\162");
        eD:
        $CJ = $Cp->ownerDocument;
        if (!empty($o7)) {
            goto qX;
        }
        $o7 = new DOMXPath($Cp->ownerDocument);
        $o7->registerNamespace("\163\145\x63\x64\x73\x69\x67", XMLSecurityDSigSAML::XMLDSIGNS);
        qX:
        $fu = "\x2e\57\x73\x65\143\x64\163\151\147\72\x4b\145\171\x49\x6e\x66\157";
        $mn = $o7->query($fu, $Cp);
        $Ru = $mn->item(0);
        if ($Ru) {
            goto JI;
        }
        $j9 = FALSE;
        $Ru = $CJ->createElementNS(XMLSecurityDSigSAML::XMLDSIGNS, "\144\x73\x3a\113\x65\171\111\156\x66\157");
        $fu = "\x2e\x2f\163\145\143\144\x73\x69\x67\x3a\x4f\x62\x6a\x65\143\x74";
        $mn = $o7->query($fu, $Cp);
        if (!($sL = $mn->item(0))) {
            goto Mt;
        }
        $sL->parentNode->insertBefore($Ru, $sL);
        $j9 = TRUE;
        Mt:
        if ($j9) {
            goto Tz;
        }
        $Cp->appendChild($Ru);
        Tz:
        JI:
        $Sw = XMLSecurityDSigSAML::staticGet509XCerts($J2, $U5);
        $sU = $CJ->createElementNS(XMLSecurityDSigSAML::XMLDSIGNS, "\144\x73\72\130\65\x30\71\104\x61\x74\141");
        $Ru->appendChild($sU);
        $UO = FALSE;
        $P7 = FALSE;
        if (!is_array($Ef)) {
            goto mJ;
        }
        if (empty($Ef["\x69\163\163\165\x65\x72\x53\145\162\x69\x61\154"])) {
            goto gn;
        }
        $UO = TRUE;
        gn:
        mJ:
        foreach ($Sw as $I7) {
            if (!$UO) {
                goto MM;
            }
            if (!($R2 = openssl_x509_parse("\x2d\55\x2d\55\55\x42\105\x47\111\116\x20\x43\105\122\124\111\x46\x49\103\101\124\x45\x2d\x2d\55\x2d\55\12" . chunk_split($I7, 64, "\12") . "\x2d\55\x2d\55\55\x45\116\104\40\x43\105\122\124\x49\106\111\x43\x41\x54\105\55\55\55\x2d\x2d\12"))) {
                goto Z3;
            }
            if (!($UO && !empty($R2["\x69\163\163\165\x65\x72"]) && !empty($R2["\163\145\x72\x69\x61\154\116\165\x6d\142\x65\x72"]))) {
                goto pD;
            }
            if (is_array($R2["\151\163\x73\165\145\162"])) {
                goto G2;
            }
            $Yq = $R2["\x69\x73\x73\x75\145\x72"];
            goto tT;
            G2:
            $Rv = array();
            foreach ($R2["\x69\x73\x73\165\x65\x72"] as $da => $w7) {
                array_unshift($Rv, "{$da}\x3d{$w7}" . $F0);
                PE:
            }
            lR:
            $Yq = implode("\x2c", $Rv);
            tT:
            $zC = $CJ->createElementNS(XMLSecurityDSigSAML::XMLDSIGNS, "\144\x73\72\130\65\60\71\x49\163\x73\x75\145\x72\123\x65\x72\151\141\154");
            $sU->appendChild($zC);
            $Je = $CJ->createElementNS(XMLSecurityDSigSAML::XMLDSIGNS, "\x64\x73\72\x58\65\x30\x39\x49\x73\163\x75\x65\162\x4e\x61\155\145", $Yq);
            $zC->appendChild($Je);
            $Je = $CJ->createElementNS(XMLSecurityDSigSAML::XMLDSIGNS, "\x64\x73\x3a\x58\65\60\71\123\145\x72\151\x61\154\x4e\x75\155\142\145\162", $R2["\163\x65\162\151\141\154\x4e\x75\155\x62\x65\x72"]);
            $zC->appendChild($Je);
            pD:
            Z3:
            MM:
            $bu = $CJ->createElementNS(XMLSecurityDSigSAML::XMLDSIGNS, "\144\163\72\130\65\x30\71\103\145\162\164\151\146\151\143\141\164\x65", $I7);
            $sU->appendChild($bu);
            j5:
        }
        gY:
    }
    public function add509Cert($J2, $U5 = TRUE, $Pi = False, $Ef = NULL)
    {
        if (!($o7 = $this->getXPathObj())) {
            goto wh;
        }
        self::staticAdd509Cert($this->sigNode, $J2, $U5, $Pi, $o7, $Ef);
        wh:
    }
    public function getValidatedNodes()
    {
        return $this->validatedNodes;
    }
}
class XMLSecEncSAML
{
    const template = "\74\170\145\156\143\72\x45\156\x63\x72\x79\x70\x74\x65\144\x44\141\x74\141\40\x78\155\x6c\x6e\x73\72\170\x65\156\x63\x3d\47\x68\164\164\160\72\57\57\x77\x77\x77\x2e\167\x33\56\157\162\147\57\62\60\x30\61\x2f\60\64\57\170\x6d\154\145\156\x63\43\47\x3e\15\12\x20\x20\40\x3c\170\145\156\143\x3a\103\x69\160\x68\145\x72\x44\141\x74\x61\x3e\15\12\x20\x20\x20\40\x20\x20\74\x78\x65\156\x63\72\x43\151\160\150\145\x72\126\x61\154\165\x65\76\x3c\57\x78\145\x6e\x63\72\103\x69\x70\x68\x65\162\x56\x61\x6c\x75\x65\76\15\xa\40\40\x20\x3c\x2f\170\x65\x6e\143\72\103\x69\160\x68\145\162\x44\x61\164\x61\x3e\xd\12\74\57\170\x65\x6e\143\72\105\156\x63\162\x79\160\x74\x65\144\104\x61\x74\x61\x3e";
    const Element = "\150\164\164\x70\x3a\57\x2f\167\167\x77\x2e\x77\63\x2e\x6f\162\147\57\62\x30\60\x31\57\60\x34\x2f\x78\155\154\145\x6e\143\x23\105\x6c\x65\155\145\156\x74";
    const Content = "\150\164\164\160\72\57\x2f\167\167\167\x2e\x77\63\x2e\157\162\x67\57\x32\x30\60\x31\x2f\60\64\57\170\x6d\154\x65\x6e\143\x23\103\157\x6e\164\145\156\164";
    const URI = 3;
    const XMLENCNS = "\150\x74\164\160\72\x2f\57\x77\x77\167\56\167\63\56\157\x72\147\x2f\x32\60\x30\61\57\60\x34\57\x78\155\x6c\145\156\143\x23";
    private $encdoc = NULL;
    private $rawNode = NULL;
    public $type = NULL;
    public $encKey = NULL;
    private $references = array();
    public function __construct()
    {
        $this->_resetTemplate();
    }
    private function _resetTemplate()
    {
        $this->encdoc = new DOMDocument();
        $this->encdoc->loadXML(XMLSecEncSAML::template);
    }
    public function addReference($k4, $mj, $X7)
    {
        if ($mj instanceof DOMNode) {
            goto Uy;
        }
        throw new Exception("\x24\x6e\x6f\x64\145\40\x69\163\x20\156\x6f\x74\40\x6f\x66\x20\164\x79\160\x65\40\x44\117\x4d\116\x6f\x64\145");
        Uy:
        $e1 = $this->encdoc;
        $this->_resetTemplate();
        $rh = $this->encdoc;
        $this->encdoc = $e1;
        $dO = XMLSecurityDSigSAML::generate_GUID();
        $g3 = $rh->documentElement;
        $g3->setAttribute("\111\x64", $dO);
        $this->references[$k4] = array("\x6e\157\144\145" => $mj, "\164\171\160\145" => $X7, "\x65\156\143\x6e\x6f\144\145" => $rh, "\162\x65\x66\x75\x72\x69" => $dO);
    }
    public function setNode($mj)
    {
        $this->rawNode = $mj;
    }
    public function encryptNode($qZ, $zu = TRUE)
    {
        $oa = '';
        if (!empty($this->rawNode)) {
            goto HC;
        }
        throw new Exception("\116\157\144\x65\40\164\157\40\x65\x6e\x63\162\171\x70\164\40\150\141\163\40\156\x6f\164\40\142\145\145\156\40\163\145\x74");
        HC:
        if ($qZ instanceof XMLSecurityKeySAML) {
            goto R5;
        }
        throw new Exception("\111\x6e\x76\x61\x6c\151\x64\40\x4b\145\171");
        R5:
        $JK = $this->rawNode->ownerDocument;
        $rG = new DOMXPath($this->encdoc);
        $GC = $rG->query("\x2f\170\x65\156\143\x3a\105\x6e\143\162\x79\x70\164\145\x64\104\141\164\141\x2f\x78\x65\x6e\x63\x3a\103\151\160\150\145\x72\104\x61\164\x61\57\170\x65\x6e\x63\x3a\x43\x69\x70\x68\145\162\126\141\154\x75\x65");
        $KN = $GC->item(0);
        if (!($KN == NULL)) {
            goto Pl;
        }
        throw new Exception("\x45\162\x72\157\x72\40\154\157\x63\141\164\151\x6e\147\x20\103\x69\x70\150\x65\162\126\x61\x6c\x75\145\40\x65\154\145\x6d\145\x6e\x74\40\x77\x69\x74\150\151\156\40\x74\145\x6d\160\154\141\164\x65");
        Pl:
        switch ($this->type) {
            case XMLSecEncSAML::Element:
                $oa = $JK->saveXML($this->rawNode);
                $this->encdoc->documentElement->setAttribute("\124\171\160\145", XMLSecEncSAML::Element);
                goto AB;
            case XMLSecEncSAML::Content:
                $v6 = $this->rawNode->childNodes;
                foreach ($v6 as $RE) {
                    $oa .= $JK->saveXML($RE);
                    BK:
                }
                UY:
                $this->encdoc->documentElement->setAttribute("\x54\171\160\145", XMLSecEncSAML::Content);
                goto AB;
            default:
                throw new Exception("\124\x79\160\145\x20\x69\163\x20\x63\x75\162\162\x65\x6e\x74\x6c\171\x20\x6e\157\164\x20\x73\x75\160\160\157\162\164\x65\144");
                return;
        }
        zo:
        AB:
        $ON = $this->encdoc->documentElement->appendChild($this->encdoc->createElementNS(XMLSecEncSAML::XMLENCNS, "\170\145\156\x63\x3a\105\x6e\x63\162\171\x70\164\151\x6f\x6e\x4d\x65\164\150\x6f\x64"));
        $ON->setAttribute("\101\154\x67\157\162\151\164\150\155", $qZ->getAlgorith());
        $KN->parentNode->parentNode->insertBefore($ON, $KN->parentNode->parentNode->firstChild);
        $wm = base64_encode($qZ->encryptData($oa));
        $w7 = $this->encdoc->createTextNode($wm);
        $KN->appendChild($w7);
        if ($zu) {
            goto en;
        }
        return $this->encdoc->documentElement;
        goto FA;
        en:
        switch ($this->type) {
            case XMLSecEncSAML::Element:
                if (!($this->rawNode->nodeType == XML_DOCUMENT_NODE)) {
                    goto Bi;
                }
                return $this->encdoc;
                Bi:
                $nG = $this->rawNode->ownerDocument->importNode($this->encdoc->documentElement, TRUE);
                $this->rawNode->parentNode->replaceChild($nG, $this->rawNode);
                return $nG;
                goto Pc;
            case XMLSecEncSAML::Content:
                $nG = $this->rawNode->ownerDocument->importNode($this->encdoc->documentElement, TRUE);
                qY:
                if (!$this->rawNode->firstChild) {
                    goto Iu;
                }
                $this->rawNode->removeChild($this->rawNode->firstChild);
                goto qY;
                Iu:
                $this->rawNode->appendChild($nG);
                return $nG;
                goto Pc;
        }
        l3:
        Pc:
        FA:
    }
    public function encryptReferences($qZ)
    {
        $wF = $this->rawNode;
        $C6 = $this->type;
        foreach ($this->references as $k4 => $wR) {
            $this->encdoc = $wR["\x65\x6e\143\x6e\x6f\144\x65"];
            $this->rawNode = $wR["\156\157\144\x65"];
            $this->type = $wR["\164\171\x70\x65"];
            try {
                $uU = $this->encryptNode($qZ);
                $this->references[$k4]["\x65\x6e\143\x6e\157\144\x65"] = $uU;
            } catch (Exception $yV) {
                $this->rawNode = $wF;
                $this->type = $C6;
                throw $yV;
            }
            HS:
        }
        fO:
        $this->rawNode = $wF;
        $this->type = $C6;
    }
    public function getCipherValue()
    {
        if (!empty($this->rawNode)) {
            goto bB;
        }
        throw new Exception("\x4e\157\144\145\40\164\157\x20\x64\x65\143\162\171\160\x74\40\150\141\163\x20\x6e\157\164\x20\142\x65\x65\156\40\x73\145\164");
        bB:
        $JK = $this->rawNode->ownerDocument;
        $rG = new DOMXPath($JK);
        $rG->registerNamespace("\170\x6d\x6c\145\x6e\143\x72", XMLSecEncSAML::XMLENCNS);
        $fu = "\56\57\170\155\154\x65\x6e\143\x72\x3a\x43\x69\160\x68\145\162\x44\x61\164\x61\57\x78\155\154\145\156\x63\162\72\103\151\160\150\x65\x72\x56\x61\x6c\165\145";
        $mn = $rG->query($fu, $this->rawNode);
        $mj = $mn->item(0);
        if ($mj) {
            goto v9;
        }
        return NULL;
        v9:
        return base64_decode($mj->nodeValue);
    }
    public function decryptNode($qZ, $zu = TRUE)
    {
        if ($qZ instanceof XMLSecurityKeySAML) {
            goto O4;
        }
        throw new Exception("\111\156\166\x61\154\151\144\40\113\x65\171");
        O4:
        $gc = $this->getCipherValue();
        if ($gc) {
            goto rC;
        }
        throw new Exception("\103\x61\x6e\156\x6f\164\40\154\157\143\141\x74\145\40\145\x6e\143\162\x79\160\x74\x65\144\x20\x64\141\x74\141");
        goto Ev;
        rC:
        $VD = $qZ->decryptData($gc);
        if ($zu) {
            goto yD;
        }
        return $VD;
        goto IO;
        yD:
        switch ($this->type) {
            case XMLSecEncSAML::Element:
                $pV = new DOMDocument();
                $pV->loadXML($VD);
                if (!($this->rawNode->nodeType == XML_DOCUMENT_NODE)) {
                    goto eE;
                }
                return $pV;
                eE:
                $nG = $this->rawNode->ownerDocument->importNode($pV->documentElement, TRUE);
                $this->rawNode->parentNode->replaceChild($nG, $this->rawNode);
                return $nG;
                goto b6;
            case XMLSecEncSAML::Content:
                if ($this->rawNode->nodeType == XML_DOCUMENT_NODE) {
                    goto CT;
                }
                $JK = $this->rawNode->ownerDocument;
                goto hC;
                CT:
                $JK = $this->rawNode;
                hC:
                $pN = $JK->createDocumentFragment();
                $pN->appendXML($VD);
                $cF = $this->rawNode->parentNode;
                $cF->replaceChild($pN, $this->rawNode);
                return $cF;
                goto b6;
            default:
                return $VD;
        }
        Bn:
        b6:
        IO:
        Ev:
    }
    public function encryptKey($vV, $rq, $Zq = TRUE)
    {
        if (!(!$vV instanceof XMLSecurityKeySAML || !$rq instanceof XMLSecurityKeySAML)) {
            goto TN;
        }
        throw new Exception("\111\x6e\x76\x61\x6c\151\x64\x20\x4b\x65\x79");
        TN:
        $q4 = base64_encode($vV->encryptData($rq->key));
        $bx = $this->encdoc->documentElement;
        $uk = $this->encdoc->createElementNS(XMLSecEncSAML::XMLENCNS, "\x78\x65\156\143\72\105\156\x63\x72\x79\x70\164\x65\144\x4b\145\x79");
        if ($Zq) {
            goto m7;
        }
        $this->encKey = $uk;
        goto Hl;
        m7:
        $Ru = $bx->insertBefore($this->encdoc->createElementNS("\150\x74\164\160\72\x2f\x2f\167\167\167\x2e\167\63\x2e\157\x72\147\57\62\x30\x30\60\x2f\60\x39\x2f\x78\x6d\x6c\x64\163\151\x67\43", "\x64\x73\x69\147\x3a\x4b\145\171\111\x6e\x66\157"), $bx->firstChild);
        $Ru->appendChild($uk);
        Hl:
        $ON = $uk->appendChild($this->encdoc->createElementNS(XMLSecEncSAML::XMLENCNS, "\170\x65\156\x63\x3a\x45\x6e\x63\x72\171\x70\x74\151\157\x6e\x4d\x65\164\150\157\x64"));
        $ON->setAttribute("\x41\x6c\x67\157\162\151\164\150\x6d", $vV->getAlgorith());
        if (empty($vV->name)) {
            goto jQ;
        }
        $Ru = $uk->appendChild($this->encdoc->createElementNS("\150\164\164\x70\x3a\x2f\x2f\167\167\x77\56\x77\x33\x2e\157\162\x67\57\x32\x30\x30\60\57\x30\71\x2f\170\x6d\x6c\144\163\151\x67\43", "\144\x73\151\x67\72\x4b\x65\171\111\x6e\x66\157"));
        $Ru->appendChild($this->encdoc->createElementNS("\x68\164\164\x70\72\x2f\57\167\167\167\56\x77\63\x2e\157\x72\x67\x2f\x32\x30\60\x30\x2f\x30\71\x2f\170\155\154\x64\163\x69\147\x23", "\x64\x73\151\147\72\x4b\145\x79\116\x61\155\145", $vV->name));
        jQ:
        $Kk = $uk->appendChild($this->encdoc->createElementNS(XMLSecEncSAML::XMLENCNS, "\170\x65\156\143\72\103\151\x70\150\x65\x72\104\141\x74\141"));
        $Kk->appendChild($this->encdoc->createElementNS(XMLSecEncSAML::XMLENCNS, "\170\x65\156\x63\72\x43\151\x70\150\x65\162\126\x61\x6c\x75\x65", $q4));
        if (!(is_array($this->references) && count($this->references) > 0)) {
            goto U2;
        }
        $HT = $uk->appendChild($this->encdoc->createElementNS(XMLSecEncSAML::XMLENCNS, "\x78\145\156\x63\x3a\122\145\x66\145\x72\x65\156\143\x65\x4c\151\163\x74"));
        foreach ($this->references as $k4 => $wR) {
            $dO = $wR["\x72\145\x66\165\x72\x69"];
            $zx = $HT->appendChild($this->encdoc->createElementNS(XMLSecEncSAML::XMLENCNS, "\x78\x65\x6e\143\72\x44\141\x74\141\122\145\x66\x65\162\145\x6e\143\145"));
            $zx->setAttribute("\125\122\x49", "\43" . $dO);
            LF:
        }
        m4:
        U2:
        return;
    }
    public function decryptKey($uk)
    {
        if ($uk->isEncrypted) {
            goto bh;
        }
        throw new Exception("\x4b\x65\171\x20\151\163\x20\156\x6f\164\x20\x45\156\143\162\x79\160\164\145\144");
        bh:
        if (!empty($uk->key)) {
            goto gc;
        }
        throw new Exception("\x4b\145\171\40\151\163\40\155\151\163\163\151\x6e\x67\40\144\141\164\x61\x20\164\157\40\x70\x65\162\x66\157\162\155\x20\x74\150\x65\40\x64\145\x63\162\x79\x70\164\x69\157\156");
        gc:
        return $this->decryptNode($uk, FALSE);
    }
    public function locateEncryptedData($g3)
    {
        if ($g3 instanceof DOMDocument) {
            goto V6;
        }
        $JK = $g3->ownerDocument;
        goto aR;
        V6:
        $JK = $g3;
        aR:
        if (!$JK) {
            goto LT;
        }
        $o7 = new DOMXPath($JK);
        $fu = "\x2f\x2f\x2a\x5b\154\157\x63\x61\x6c\55\156\x61\155\145\50\51\75\x27\105\156\143\x72\x79\x70\164\145\144\x44\x61\164\x61\x27\x20\141\156\x64\x20\156\141\x6d\x65\x73\x70\141\x63\x65\x2d\x75\x72\151\50\51\75\x27" . XMLSecEncSAML::XMLENCNS . "\47\x5d";
        $mn = $o7->query($fu);
        return $mn->item(0);
        LT:
        return NULL;
    }
    public function locateKey($mj = NULL)
    {
        if (!empty($mj)) {
            goto Yr;
        }
        $mj = $this->rawNode;
        Yr:
        if ($mj instanceof DOMNode) {
            goto Za;
        }
        return NULL;
        Za:
        if (!($JK = $mj->ownerDocument)) {
            goto ef;
        }
        $o7 = new DOMXPath($JK);
        $o7->registerNamespace("\x58\115\x4c\x53\x65\x63\x45\x6e\143\123\x41\x4d\114", XMLSecEncSAML::XMLENCNS);
        $fu = "\56\57\x2f\130\x4d\114\123\x65\143\105\156\143\123\101\x4d\114\x3a\105\x6e\143\162\171\160\164\151\157\156\x4d\x65\x74\150\x6f\x64";
        $mn = $o7->query($fu, $mj);
        if (!($rD = $mn->item(0))) {
            goto kV;
        }
        $RX = $rD->getAttribute("\101\x6c\x67\x6f\162\x69\164\150\x6d");
        try {
            $qZ = new XMLSecurityKeySAML($RX, array("\x74\171\x70\x65" => "\x70\x72\151\x76\141\164\145"));
        } catch (Exception $yV) {
            return NULL;
        }
        return $qZ;
        kV:
        ef:
        return NULL;
    }
    static function staticLocateKeyInfo($Kq = NULL, $mj = NULL)
    {
        if (!(empty($mj) || !$mj instanceof DOMNode)) {
            goto gz;
        }
        return NULL;
        gz:
        $JK = $mj->ownerDocument;
        if ($JK) {
            goto gD;
        }
        return NULL;
        gD:
        $o7 = new DOMXPath($JK);
        $o7->registerNamespace("\x58\x4d\x4c\123\x65\x63\x45\156\143\123\x41\115\114", XMLSecEncSAML::XMLENCNS);
        $o7->registerNamespace("\170\x6d\154\163\145\143\x64\163\x69\x67", XMLSecurityDSigSAML::XMLDSIGNS);
        $fu = "\x2e\x2f\170\155\154\x73\x65\x63\x64\x73\x69\147\x3a\113\x65\171\111\x6e\x66\157";
        $mn = $o7->query($fu, $mj);
        $rD = $mn->item(0);
        if ($rD) {
            goto lZ;
        }
        return $Kq;
        lZ:
        foreach ($rD->childNodes as $RE) {
            switch ($RE->localName) {
                case "\113\145\x79\116\141\155\145":
                    if (empty($Kq)) {
                        goto ah;
                    }
                    $Kq->name = $RE->nodeValue;
                    ah:
                    goto Z9;
                case "\x4b\145\x79\x56\x61\154\165\145":
                    foreach ($RE->childNodes as $aD) {
                        switch ($aD->localName) {
                            case "\x44\x53\x41\113\x65\171\126\141\x6c\x75\x65":
                                throw new Exception("\x44\123\101\113\145\171\x56\x61\x6c\165\x65\40\143\165\162\162\145\x6e\164\154\x79\x20\x6e\x6f\164\40\x73\165\x70\160\157\x72\x74\x65\x64");
                                goto fs;
                            case "\x52\123\101\113\x65\x79\x56\x61\154\x75\145":
                                $Fx = NULL;
                                $QV = NULL;
                                if (!($G0 = $aD->getElementsByTagName("\x4d\x6f\144\165\x6c\165\x73")->item(0))) {
                                    goto Kk;
                                }
                                $Fx = base64_decode($G0->nodeValue);
                                Kk:
                                if (!($Uk = $aD->getElementsByTagName("\105\x78\x70\x6f\156\x65\x6e\164")->item(0))) {
                                    goto jy;
                                }
                                $QV = base64_decode($Uk->nodeValue);
                                jy:
                                if (!(empty($Fx) || empty($QV))) {
                                    goto v0;
                                }
                                throw new Exception("\115\x69\x73\x73\151\156\147\40\115\157\x64\165\x6c\x75\x73\x20\x6f\x72\40\105\x78\x70\157\x6e\145\156\x74");
                                v0:
                                $tf = XMLSecurityKeySAML::convertRSA($Fx, $QV);
                                $Kq->loadKey($tf);
                                goto fs;
                        }
                        An:
                        fs:
                        rN:
                    }
                    rj:
                    goto Z9;
                case "\x52\x65\164\162\x69\145\x76\141\154\x4d\145\x74\150\x6f\144":
                    $X7 = $RE->getAttribute("\x54\x79\x70\145");
                    if (!($X7 !== "\150\x74\x74\160\72\x2f\57\x77\x77\x77\56\x77\x33\56\x6f\x72\x67\57\x32\60\60\x31\x2f\60\64\57\x78\x6d\x6c\x65\156\143\x23\105\x6e\143\162\171\160\x74\145\x64\113\x65\x79")) {
                        goto uh;
                    }
                    goto Z9;
                    uh:
                    $GX = $RE->getAttribute("\x55\x52\x49");
                    if (!($GX[0] !== "\43")) {
                        goto Wl;
                    }
                    goto Z9;
                    Wl:
                    $Yd = substr($GX, 1);
                    $fu = "\57\57\x58\x4d\x4c\x53\x65\x63\x45\x6e\x63\x53\101\115\114\x3a\105\156\x63\162\x79\160\x74\x65\144\113\145\171\133\100\x49\x64\75\47{$Yd}\x27\x5d";
                    $kV = $o7->query($fu)->item(0);
                    if ($kV) {
                        goto et;
                    }
                    throw new Exception("\x55\x6e\141\142\154\145\40\x74\x6f\x20\154\x6f\143\x61\164\145\x20\x45\x6e\143\x72\171\x70\x74\x65\144\x4b\145\x79\x20\x77\x69\x74\x68\40\x40\111\x64\75\x27{$Yd}\x27\x2e");
                    et:
                    return XMLSecurityKeySAML::fromEncryptedKeyElement($kV);
                case "\x45\x6e\143\162\x79\x70\x74\145\144\113\145\x79":
                    return XMLSecurityKeySAML::fromEncryptedKeyElement($RE);
                case "\x58\65\60\x39\x44\141\x74\x61":
                    if (!($mL = $RE->getElementsByTagName("\x58\x35\60\x39\103\145\x72\164\x69\x66\x69\143\x61\x74\145"))) {
                        goto PS;
                    }
                    if (!($mL->length > 0)) {
                        goto Cx;
                    }
                    $cX = $mL->item(0)->textContent;
                    $cX = str_replace(array("\xd", "\xa"), '', $cX);
                    $cX = "\55\55\55\x2d\x2d\x42\x45\107\111\116\x20\x43\105\x52\x54\x49\106\x49\103\x41\x54\105\55\x2d\x2d\55\x2d\xa" . chunk_split($cX, 64, "\12") . "\55\55\55\x2d\55\105\116\104\40\103\x45\x52\124\x49\x46\x49\103\x41\x54\105\55\x2d\x2d\x2d\55\12";
                    $Kq->loadKey($cX, FALSE, TRUE);
                    Cx:
                    PS:
                    goto Z9;
            }
            MA:
            Z9:
            Zi:
        }
        qA:
        return $Kq;
    }
    public function locateKeyInfo($Kq = NULL, $mj = NULL)
    {
        if (!empty($mj)) {
            goto YH;
        }
        $mj = $this->rawNode;
        YH:
        return XMLSecEncSAML::staticLocateKeyInfo($Kq, $mj);
    }
}
