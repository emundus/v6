<?php


function sortAndAddAttrs($O9, $wQ)
{
    $CB = array();
    foreach ($wQ as $x1) {
        $CB[$x1->nodeName] = $x1;
        Bw:
    }
    XE:
    ksort($CB);
    foreach ($CB as $x1) {
        $O9->setAttribute($x1->nodeName, $x1->nodeValue);
        tY:
    }
    cy:
}
function canonical($P9, $O9, $qI)
{
    if ($P9->nodeType != XML_DOCUMENT_NODE) {
        goto b9;
    }
    $jD = $P9;
    goto Yv;
    b9:
    $jD = $P9->ownerDocument;
    Yv:
    if (!($O9->nodeType != XML_ELEMENT_NODE)) {
        goto T3;
    }
    if (!($O9->nodeType == XML_DOCUMENT_NODE)) {
        goto i8;
    }
    foreach ($O9->childNodes as $Il) {
        canonical($jD, $Il, $qI);
        z4:
    }
    ER:
    return;
    i8:
    if (!($O9->nodeType == XML_COMMENT_NODE && !$qI)) {
        goto NG;
    }
    return;
    NG:
    $P9->appendChild($jD->importNode($O9, TRUE));
    return;
    T3:
    $a1 = array();
    if ($O9->namespaceURI != '') {
        goto j0;
    }
    $lF = $jD->createElement($O9->nodeName);
    goto i1;
    j0:
    if ($O9->prefix == '') {
        goto Jr;
    }
    $MD = $P9->lookupPrefix($O9->namespaceURI);
    if ($MD == $O9->prefix) {
        goto Xm;
    }
    $lF = $jD->createElement($O9->nodeName);
    $a1[$O9->namespaceURI] = $O9->prefix;
    goto N4;
    Xm:
    $lF = $jD->createElementNS($O9->namespaceURI, $O9->nodeName);
    N4:
    goto R_;
    Jr:
    $lF = $jD->createElementNS($O9->namespaceURI, $O9->nodeName);
    R_:
    i1:
    $P9->appendChild($lF);
    $PY = new DOMXPath($O9->ownerDocument);
    $wQ = $PY->query("\141\164\164\x72\151\x62\x75\164\145\72\x3a\x2a\x5b\x6e\141\x6d\145\x73\160\141\143\x65\55\x75\x72\x69\x28\x2e\51\40\41\75\x20\42\x22\x5d", $O9);
    foreach ($wQ as $x1) {
        if (!(array_key_exists($x1->namespaceURI, $a1) && $a1[$x1->namespaceURI] == $x1->prefix)) {
            goto vM;
        }
        goto zs;
        vM:
        $MD = $P9->lookupPrefix($x1->namespaceURI);
        if ($MD != $x1->prefix) {
            goto Hb;
        }
        $a1[$x1->namespaceURI] = NULL;
        goto lF;
        Hb:
        $a1[$x1->namespaceURI] = $x1->prefix;
        lF:
        zs:
    }
    fP:
    if (!(count($a1) > 0)) {
        goto aj;
    }
    asort($a1);
    aj:
    foreach ($a1 as $mk => $MD) {
        if (!($MD != NULL)) {
            goto N7;
        }
        $lF->setAttributeNS("\150\x74\x74\x70\x3a\x2f\57\167\x77\x77\56\x77\63\56\157\162\x67\57\x32\60\60\x30\x2f\170\155\154\156\x73\x2f", "\170\155\x6c\x6e\x73\x3a" . $MD, $mk);
        N7:
        C5:
    }
    pA:
    if (!(count($a1) > 0)) {
        goto fe;
    }
    ksort($a1);
    fe:
    $wQ = $PY->query("\141\164\164\x72\151\142\x75\164\145\x3a\72\52\x5b\x6e\141\x6d\145\163\160\141\x63\145\x2d\x75\162\151\x28\56\51\40\75\x20\x22\42\x5d", $O9);
    sortAndAddAttrs($lF, $wQ);
    foreach ($a1 as $JY => $MD) {
        $wQ = $PY->query("\x61\164\x74\162\x69\x62\165\x74\145\x3a\x3a\x2a\x5b\x6e\141\155\145\163\x70\x61\x63\x65\55\165\x72\151\50\56\51\40\75\x20\42" . $JY . "\42\x5d", $O9);
        sortAndAddAttrs($lF, $wQ);
        IR:
    }
    pb:
    foreach ($O9->childNodes as $Il) {
        canonical($lF, $Il, $qI);
        ie:
    }
    qs:
}
function C14NGeneral($O9, $Wi = FALSE, $qI = FALSE)
{
    $m2 = explode("\56", PHP_VERSION);
    if (!($m2[0] > 5 || $m2[0] == 5 && $m2[1] >= 2)) {
        goto Qp;
    }
    return $O9->C14N($Wi, $qI);
    Qp:
    if (!(!$O9 instanceof DOMElement && !$O9 instanceof DOMDocument)) {
        goto f8;
    }
    return NULL;
    f8:
    if (!($Wi == FALSE)) {
        goto pY;
    }
    throw new Exception("\117\156\154\171\40\145\x78\x63\x6c\165\x73\151\166\x65\x20\143\141\x6e\x6f\156\151\x63\x61\x6c\151\172\141\164\x69\157\x6e\x20\151\163\x20\163\165\160\160\157\x72\x74\145\x64\x20\x69\x6e\x20\164\150\151\163\x20\x76\145\x72\x73\151\157\x6e\x20\x6f\146\x20\120\110\120");
    pY:
    $lb = new DOMDocument();
    canonical($lb, $O9, $qI);
    return $lb->saveXML($lb->documentElement, LIBXML_NOEMPTYTAG);
}
class XMLSecurityKeySAML
{
    const TRIPLEDES_CBC = "\x68\164\x74\160\x3a\x2f\57\167\x77\167\x2e\x77\x33\56\157\162\x67\x2f\x32\60\60\x31\57\60\x34\x2f\x78\x6d\154\145\x6e\x63\x23\x74\162\151\x70\154\x65\x64\x65\163\x2d\x63\x62\x63";
    const AES128_CBC = "\x68\x74\164\160\72\x2f\57\167\167\x77\56\x77\x33\56\x6f\x72\x67\x2f\x32\60\60\61\x2f\60\64\x2f\x78\155\x6c\x65\156\x63\x23\141\145\163\x31\62\70\x2d\143\142\143";
    const AES192_CBC = "\150\164\x74\x70\72\57\57\167\167\x77\56\x77\x33\56\x6f\162\x67\x2f\x32\60\x30\61\x2f\60\64\x2f\170\x6d\154\145\156\x63\x23\141\145\x73\x31\71\62\55\x63\x62\143";
    const AES256_CBC = "\x68\164\164\160\72\57\57\x77\167\x77\56\x77\x33\56\x6f\162\x67\57\x32\x30\x30\x31\57\x30\64\57\170\155\x6c\145\156\x63\x23\x61\145\x73\x32\65\x36\x2d\143\142\143";
    const RSA_1_5 = "\150\164\x74\x70\72\57\57\x77\167\x77\x2e\x77\63\x2e\157\x72\147\57\62\60\60\x31\x2f\x30\x34\57\x78\155\154\x65\x6e\143\43\162\163\141\x2d\61\137\65";
    const RSA_OAEP_MGF1P = "\x68\164\x74\160\x3a\x2f\x2f\167\167\x77\x2e\x77\x33\56\157\162\x67\x2f\62\x30\x30\x31\x2f\x30\64\57\x78\x6d\154\145\x6e\143\43\162\x73\141\55\x6f\x61\x65\160\55\155\147\x66\x31\x70";
    const DSA_SHA1 = "\x68\x74\164\160\72\57\57\167\167\x77\x2e\167\x33\x2e\157\162\147\x2f\62\x30\x30\x30\x2f\60\x39\57\170\155\x6c\144\x73\151\147\x23\x64\x73\x61\x2d\163\x68\141\x31";
    const RSA_SHA1 = "\x68\164\x74\160\72\x2f\57\167\x77\x77\56\x77\x33\x2e\x6f\162\x67\57\62\60\x30\60\57\60\x39\57\x78\x6d\x6c\144\163\151\x67\x23\162\163\141\55\x73\150\x61\x31";
    const RSA_SHA256 = "\150\164\164\160\x3a\x2f\x2f\167\167\167\56\167\x33\x2e\157\162\x67\57\62\x30\x30\x31\57\x30\64\57\x78\155\x6c\x64\x73\151\147\55\155\x6f\x72\x65\43\x72\x73\x61\55\163\x68\x61\62\65\x36";
    const RSA_SHA384 = "\x68\164\164\x70\72\57\57\x77\167\x77\56\167\x33\56\x6f\162\x67\57\62\x30\60\61\57\x30\64\x2f\x78\155\154\x64\163\x69\x67\x2d\155\157\162\145\43\162\x73\141\x2d\163\x68\141\63\70\64";
    const RSA_SHA512 = "\150\x74\x74\x70\72\x2f\x2f\167\167\x77\56\x77\63\x2e\157\162\x67\57\62\60\x30\61\57\60\x34\x2f\170\x6d\154\144\163\151\x67\55\155\157\x72\145\43\x72\163\x61\55\x73\150\141\65\61\62";
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
    public function __construct($Zc, $DX = NULL)
    {
        srand();
        switch ($Zc) {
            case XMLSecurityKeySAML::TRIPLEDES_CBC:
                $this->cryptParams["\x6c\151\x62\x72\141\x72\x79"] = "\155\143\162\x79\x70\x74";
                $this->cryptParams["\143\x69\x70\150\145\x72"] = MCRYPT_TRIPLEDES;
                $this->cryptParams["\155\x6f\x64\x65"] = MCRYPT_MODE_CBC;
                $this->cryptParams["\x6d\x65\164\150\x6f\x64"] = "\x68\x74\x74\160\x3a\57\57\x77\x77\x77\56\167\63\x2e\157\162\x67\x2f\x32\x30\x30\x31\57\x30\64\x2f\x78\155\154\145\x6e\143\x23\x74\162\151\160\x6c\x65\144\x65\x73\x2d\143\x62\143";
                $this->cryptParams["\x6b\x65\x79\x73\x69\x7a\145"] = 24;
                goto A4;
            case XMLSecurityKeySAML::AES128_CBC:
                $this->cryptParams["\154\151\142\162\141\162\171"] = "\155\143\x72\171\x70\164";
                $this->cryptParams["\x63\x69\160\x68\145\x72"] = MCRYPT_RIJNDAEL_128;
                $this->cryptParams["\155\157\x64\x65"] = MCRYPT_MODE_CBC;
                $this->cryptParams["\x6d\145\164\x68\157\144"] = "\150\x74\x74\160\72\57\x2f\x77\x77\167\56\167\x33\56\157\x72\147\x2f\x32\x30\60\x31\57\60\x34\x2f\x78\x6d\x6c\145\156\x63\x23\x61\x65\x73\x31\62\70\x2d\143\x62\x63";
                $this->cryptParams["\153\x65\171\x73\x69\x7a\145"] = 16;
                goto A4;
            case XMLSecurityKeySAML::AES192_CBC:
                $this->cryptParams["\154\151\x62\162\141\162\x79"] = "\155\x63\x72\171\160\x74";
                $this->cryptParams["\143\x69\x70\x68\145\x72"] = MCRYPT_RIJNDAEL_128;
                $this->cryptParams["\155\x6f\x64\x65"] = MCRYPT_MODE_CBC;
                $this->cryptParams["\x6d\x65\164\x68\x6f\144"] = "\150\164\164\x70\72\57\57\167\167\167\56\x77\x33\x2e\x6f\x72\147\x2f\62\x30\60\61\x2f\60\x34\x2f\x78\x6d\154\x65\156\143\43\141\145\x73\61\x39\62\x2d\x63\x62\x63";
                $this->cryptParams["\153\x65\171\x73\x69\x7a\x65"] = 24;
                goto A4;
            case XMLSecurityKeySAML::AES256_CBC:
                $this->cryptParams["\x6c\x69\142\162\141\162\171"] = "\x6d\143\x72\171\160\164";
                $this->cryptParams["\143\x69\x70\150\x65\x72"] = MCRYPT_RIJNDAEL_128;
                $this->cryptParams["\x6d\x6f\144\x65"] = MCRYPT_MODE_CBC;
                $this->cryptParams["\155\x65\x74\x68\157\144"] = "\x68\x74\164\160\x3a\x2f\57\167\x77\x77\x2e\x77\x33\x2e\x6f\162\147\57\x32\x30\60\x31\x2f\x30\x34\x2f\170\x6d\154\x65\156\143\x23\x61\x65\x73\x32\x35\66\x2d\x63\142\x63";
                $this->cryptParams["\x6b\145\x79\x73\151\172\x65"] = 32;
                goto A4;
            case XMLSecurityKeySAML::RSA_1_5:
                $this->cryptParams["\154\x69\x62\x72\141\162\x79"] = "\x6f\160\145\x6e\163\x73\154";
                $this->cryptParams["\160\141\x64\144\x69\156\147"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\x6d\x65\164\150\x6f\x64"] = "\x68\x74\x74\160\x3a\57\x2f\167\167\x77\x2e\x77\x33\56\157\162\147\x2f\62\x30\x30\x31\x2f\x30\64\57\x78\155\154\145\156\143\43\162\163\141\55\61\137\x35";
                if (!(is_array($DX) && !empty($DX["\164\x79\160\x65"]))) {
                    goto Ke;
                }
                if (!($DX["\x74\171\x70\x65"] == "\160\165\x62\x6c\x69\143" || $DX["\x74\171\x70\x65"] == "\x70\x72\151\x76\x61\x74\x65")) {
                    goto z9;
                }
                $this->cryptParams["\164\x79\160\x65"] = $DX["\x74\171\160\x65"];
                goto A4;
                z9:
                Ke:
                throw new Exception("\103\145\x72\x74\151\x66\151\x63\141\164\145\40\42\x74\171\160\145\x22\40\50\160\162\151\x76\141\164\x65\x2f\160\165\x62\154\x69\x63\x29\x20\155\x75\x73\164\40\x62\x65\40\x70\x61\x73\x73\145\144\40\x76\151\x61\40\160\141\162\x61\x6d\x65\164\x65\x72\163");
                return;
            case XMLSecurityKeySAML::RSA_OAEP_MGF1P:
                $this->cryptParams["\154\151\x62\x72\x61\162\x79"] = "\x6f\160\x65\x6e\x73\x73\x6c";
                $this->cryptParams["\x70\141\x64\144\151\x6e\147"] = OPENSSL_PKCS1_OAEP_PADDING;
                $this->cryptParams["\155\x65\x74\150\x6f\144"] = "\x68\x74\164\160\72\57\57\167\x77\167\x2e\x77\63\x2e\157\162\x67\57\x32\x30\60\x31\57\60\64\57\170\x6d\x6c\145\x6e\143\43\162\x73\141\55\157\141\x65\x70\x2d\x6d\x67\x66\61\160";
                $this->cryptParams["\150\141\x73\150"] = NULL;
                if (!(is_array($DX) && !empty($DX["\164\171\x70\145"]))) {
                    goto H3;
                }
                if (!($DX["\164\x79\160\x65"] == "\x70\x75\142\x6c\151\x63" || $DX["\x74\x79\x70\x65"] == "\x70\x72\x69\x76\141\164\x65")) {
                    goto wG;
                }
                $this->cryptParams["\164\x79\160\x65"] = $DX["\164\x79\x70\145"];
                goto A4;
                wG:
                H3:
                throw new Exception("\103\145\x72\x74\x69\x66\x69\x63\x61\x74\x65\40\42\164\x79\x70\145\42\x20\50\160\x72\151\166\141\164\x65\x2f\x70\165\x62\x6c\151\143\x29\x20\x6d\165\x73\x74\x20\x62\145\40\x70\x61\x73\x73\x65\144\x20\x76\x69\x61\40\x70\141\162\141\x6d\145\164\x65\x72\x73");
                return;
            case XMLSecurityKeySAML::RSA_SHA1:
                $this->cryptParams["\154\151\x62\162\141\162\x79"] = "\157\x70\145\x6e\163\x73\154";
                $this->cryptParams["\x6d\145\x74\x68\157\x64"] = "\150\x74\164\x70\x3a\57\57\x77\167\167\x2e\167\63\x2e\x6f\x72\147\57\62\60\x30\60\x2f\x30\71\57\170\x6d\154\x64\163\x69\147\43\x72\163\x61\55\163\x68\x61\x31";
                $this->cryptParams["\x70\141\x64\x64\151\x6e\147"] = OPENSSL_PKCS1_PADDING;
                if (!(is_array($DX) && !empty($DX["\x74\171\x70\145"]))) {
                    goto xu;
                }
                if (!($DX["\x74\171\x70\x65"] == "\160\165\x62\154\x69\143" || $DX["\164\x79\x70\145"] == "\160\x72\151\166\141\164\145")) {
                    goto a9;
                }
                $this->cryptParams["\164\171\x70\x65"] = $DX["\164\171\160\145"];
                goto A4;
                a9:
                xu:
                throw new Exception("\103\x65\162\x74\x69\146\151\x63\x61\164\x65\40\x22\x74\x79\x70\145\42\40\x28\x70\162\151\x76\x61\164\x65\x2f\x70\165\x62\154\151\143\51\40\155\x75\163\164\40\x62\x65\x20\160\141\163\163\x65\x64\40\x76\151\x61\40\x70\x61\x72\141\155\x65\164\x65\162\163");
                goto A4;
            case XMLSecurityKeySAML::RSA_SHA256:
                $this->cryptParams["\154\151\142\x72\141\x72\171"] = "\157\x70\x65\156\x73\163\x6c";
                $this->cryptParams["\155\145\164\150\157\144"] = "\x68\164\164\x70\x3a\x2f\57\x77\167\167\x2e\167\x33\56\157\x72\147\57\x32\x30\x30\61\x2f\60\x34\x2f\x78\155\154\144\163\x69\x67\x2d\155\157\x72\145\43\162\163\x61\55\163\x68\x61\x32\65\x36";
                $this->cryptParams["\160\141\144\144\151\x6e\147"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\144\151\147\145\x73\x74"] = "\x53\x48\101\62\x35\66";
                if (!(is_array($DX) && !empty($DX["\164\171\160\145"]))) {
                    goto Ud;
                }
                if (!($DX["\x74\171\x70\145"] == "\x70\x75\142\154\151\x63" || $DX["\164\x79\160\x65"] == "\160\162\x69\x76\x61\x74\145")) {
                    goto EC;
                }
                $this->cryptParams["\x74\171\x70\x65"] = $DX["\164\x79\160\x65"];
                goto A4;
                EC:
                Ud:
                throw new Exception("\x43\145\162\164\x69\x66\151\143\x61\x74\145\40\x22\164\171\160\x65\42\40\50\160\x72\151\166\141\x74\x65\57\x70\x75\142\x6c\151\143\x29\40\x6d\x75\163\164\40\142\145\x20\x70\141\163\163\145\144\x20\x76\x69\141\40\x70\x61\x72\x61\x6d\x65\x74\145\x72\163");
                goto A4;
            case XMLSecurityKeySAML::RSA_SHA384:
                $this->cryptParams["\x6c\151\x62\162\141\162\x79"] = "\x6f\160\x65\156\163\163\154";
                $this->cryptParams["\x6d\145\164\x68\157\x64"] = "\x68\x74\x74\160\72\x2f\x2f\167\x77\167\x2e\x77\63\x2e\157\x72\147\x2f\62\60\x30\61\x2f\60\x34\x2f\x78\x6d\x6c\x64\x73\151\147\55\155\x6f\162\145\x23\x72\x73\141\x2d\x73\x68\141\63\70\x34";
                $this->cryptParams["\160\141\x64\x64\x69\156\147"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\x64\151\x67\145\163\x74"] = "\x53\x48\x41\63\x38\64";
                if (!(is_array($DX) && !empty($DX["\164\x79\160\145"]))) {
                    goto qp;
                }
                if (!($DX["\x74\171\x70\145"] == "\x70\x75\x62\154\151\x63" || $DX["\164\171\160\x65"] == "\x70\162\151\166\x61\164\145")) {
                    goto Sl;
                }
                $this->cryptParams["\x74\x79\x70\145"] = $DX["\x74\171\x70\x65"];
                goto A4;
                Sl:
                qp:
            case XMLSecurityKeySAML::RSA_SHA512:
                $this->cryptParams["\x6c\151\x62\x72\x61\162\171"] = "\x6f\160\145\x6e\x73\x73\x6c";
                $this->cryptParams["\155\145\164\x68\157\x64"] = "\x68\x74\x74\x70\72\57\57\167\x77\x77\56\167\63\x2e\157\x72\147\x2f\62\60\60\x31\x2f\60\x34\57\x78\155\x6c\144\163\x69\x67\55\155\x6f\162\x65\43\162\x73\x61\x2d\163\150\141\65\61\62";
                $this->cryptParams["\160\141\x64\144\x69\156\147"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\x64\151\147\x65\163\x74"] = "\x53\x48\101\65\61\62";
                if (!(is_array($DX) && !empty($DX["\164\171\160\145"]))) {
                    goto U_;
                }
                if (!($DX["\164\171\x70\x65"] == "\160\x75\x62\154\151\x63" || $DX["\x74\x79\x70\x65"] == "\160\x72\x69\166\141\164\145")) {
                    goto at;
                }
                $this->cryptParams["\x74\171\x70\145"] = $DX["\x74\171\160\x65"];
                goto A4;
                at:
                U_:
            default:
                throw new Exception("\111\156\x76\141\154\x69\144\x20\x4b\145\171\40\124\x79\x70\x65");
                return;
        }
        tv:
        A4:
        $this->type = $Zc;
    }
    public function getSymmetricKeySize()
    {
        if (isset($this->cryptParams["\153\x65\x79\x73\x69\x7a\145"])) {
            goto eM;
        }
        return NULL;
        eM:
        return $this->cryptParams["\x6b\145\171\x73\151\172\145"];
    }
    public function generateSessionKey()
    {
        if (isset($this->cryptParams["\x6b\145\171\x73\151\172\145"])) {
            goto cA;
        }
        throw new Exception("\x55\156\153\x6e\x6f\x77\156\x20\153\145\x79\40\163\151\172\x65\x20\146\157\x72\x20\164\171\x70\145\40\x22" . $this->type . "\42\x2e");
        cA:
        $Qb = $this->cryptParams["\153\145\x79\163\x69\172\x65"];
        if (function_exists("\x6f\x70\145\x6e\163\163\154\137\x72\x61\156\x64\157\155\137\x70\163\x65\x75\144\157\x5f\142\171\164\145\163")) {
            goto Lc;
        }
        $TP = mcrypt_create_iv($Qb, MCRYPT_RAND);
        goto fj;
        Lc:
        $TP = openssl_random_pseudo_bytes($Qb);
        fj:
        if (!($this->type === XMLSecurityKeySAML::TRIPLEDES_CBC)) {
            goto Lp;
        }
        $Vv = 0;
        IL:
        if (!($Vv < strlen($TP))) {
            goto Xk;
        }
        $Av = ord($TP[$Vv]) & 0xfe;
        $wi = 1;
        $lg = 1;
        ra:
        if (!($lg < 8)) {
            goto e4;
        }
        $wi ^= $Av >> $lg & 1;
        tT:
        $lg++;
        goto ra;
        e4:
        $Av |= $wi;
        $TP[$Vv] = chr($Av);
        lk:
        $Vv++;
        goto IL;
        Xk:
        Lp:
        $this->key = $TP;
        return $TP;
    }
    public static function getRawThumbprint($LZ)
    {
        $AB = explode("\12", $LZ);
        $Vr = '';
        $HZ = FALSE;
        foreach ($AB as $OG) {
            if (!$HZ) {
                goto Pf;
            }
            if (!(strncmp($OG, "\x2d\55\55\55\x2d\105\x4e\104\x20\103\105\122\124\111\106\x49\103\101\124\105", 20) == 0)) {
                goto NI;
            }
            $HZ = FALSE;
            goto lQ;
            NI:
            $Vr .= trim($OG);
            goto Zu;
            Pf:
            if (!(strncmp($OG, "\55\55\x2d\x2d\55\x42\105\x47\111\116\x20\103\x45\122\124\x49\106\x49\x43\x41\124\x45", 22) == 0)) {
                goto RR;
            }
            $HZ = TRUE;
            RR:
            Zu:
            r1:
        }
        lQ:
        if (empty($Vr)) {
            goto p2;
        }
        return strtolower(sha1(base64_decode($Vr)));
        p2:
        return NULL;
    }
    public function loadKey($TP, $xM = FALSE, $hR = FALSE)
    {
        if ($xM) {
            goto V1;
        }
        $this->key = $TP;
        goto jv;
        V1:
        $this->key = file_get_contents($TP);
        jv:
        if ($hR) {
            goto V_;
        }
        $this->x509Certificate = NULL;
        goto wT;
        V_:
        $this->key = openssl_x509_read($this->key);
        openssl_x509_export($this->key, $xT);
        $this->x509Certificate = $xT;
        $this->key = $xT;
        wT:
        if ($this->cryptParams["\x6c\151\142\x72\141\162\x79"] == "\x6f\x70\145\156\163\x73\x6c") {
            goto cP;
        }
        if (!($this->cryptParams["\143\151\160\x68\x65\x72"] == MCRYPT_RIJNDAEL_128)) {
            goto Sy;
        }
        switch ($this->type) {
            case XMLSecurityKeySAML::AES256_CBC:
                if (!(strlen($this->key) < 25)) {
                    goto Vq;
                }
                throw new Exception("\x4b\x65\x79\40\x6d\x75\x73\164\x20\x63\x6f\x6e\x74\141\151\x6e\x20\x61\164\40\154\x65\141\x73\x74\x20\62\x35\x20\x63\150\x61\x72\x61\x63\164\x65\x72\x73\40\146\157\162\x20\x74\150\151\163\40\x63\x69\160\150\x65\x72");
                Vq:
                goto wl;
            case XMLSecurityKeySAML::AES192_CBC:
                if (!(strlen($this->key) < 17)) {
                    goto im;
                }
                throw new Exception("\x4b\x65\x79\x20\x6d\165\x73\164\x20\x63\157\156\x74\141\151\x6e\40\x61\x74\40\154\145\x61\x73\164\x20\61\67\x20\x63\x68\x61\162\141\x63\x74\145\162\163\40\146\157\x72\x20\164\x68\151\x73\40\143\x69\x70\x68\145\162");
                im:
                goto wl;
        }
        c4:
        wl:
        Sy:
        goto Ad;
        cP:
        if ($this->cryptParams["\x74\171\x70\x65"] == "\x70\x75\x62\154\151\x63") {
            goto k3;
        }
        $this->key = openssl_get_privatekey($this->key, $this->passphrase);
        goto zR;
        k3:
        if (!$hR) {
            goto Xl;
        }
        $this->X509Thumbprint = self::getRawThumbprint($this->key);
        Xl:
        $this->key = openssl_get_publickey($this->key);
        zR:
        Ad:
    }
    private function encryptMcrypt($Vr)
    {
        $wl = mcrypt_module_open($this->cryptParams["\143\151\x70\x68\x65\162"], '', $this->cryptParams["\x6d\157\x64\145"], '');
        $this->iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($wl), MCRYPT_RAND);
        mcrypt_generic_init($wl, $this->key, $this->iv);
        if (!($this->cryptParams["\155\x6f\x64\x65"] == MCRYPT_MODE_CBC)) {
            goto b1;
        }
        $Te = mcrypt_enc_get_block_size($wl);
        $Gg = $pA = strlen($Vr);
        N6:
        if (!($pA % $Te != $Te - 1)) {
            goto RC;
        }
        $Vr .= chr(rand(1, 127));
        k6:
        $pA++;
        goto N6;
        RC:
        $Vr .= chr($pA - $Gg + 1);
        b1:
        $nf = $this->iv . mcrypt_generic($wl, $Vr);
        mcrypt_generic_deinit($wl);
        mcrypt_module_close($wl);
        return $nf;
    }
    private function decryptMcrypt($Vr)
    {
        $wl = mcrypt_module_open($this->cryptParams["\x63\151\160\150\145\162"], '', $this->cryptParams["\x6d\x6f\x64\x65"], '');
        $PT = mcrypt_enc_get_iv_size($wl);
        $this->iv = substr($Vr, 0, $PT);
        $Vr = substr($Vr, $PT);
        mcrypt_generic_init($wl, $this->key, $this->iv);
        $IX = mdecrypt_generic($wl, $Vr);
        mcrypt_generic_deinit($wl);
        mcrypt_module_close($wl);
        if (!($this->cryptParams["\155\157\144\x65"] == MCRYPT_MODE_CBC)) {
            goto Nw;
        }
        $CZ = strlen($IX);
        $zc = substr($IX, $CZ - 1, 1);
        $IX = substr($IX, 0, $CZ - ord($zc));
        Nw:
        return $IX;
    }
    private function encryptOpenSSL($Vr)
    {
        if ($this->cryptParams["\x74\171\160\145"] == "\x70\x75\142\x6c\x69\x63") {
            goto Li;
        }
        if (openssl_private_encrypt($Vr, $nf, $this->key, $this->cryptParams["\160\x61\x64\x64\151\156\x67"])) {
            goto Wv;
        }
        throw new Exception("\x46\x61\x69\x6c\x75\162\145\x20\x65\156\143\x72\x79\x70\x74\151\x6e\x67\x20\104\x61\164\x61");
        return;
        Wv:
        goto L7;
        Li:
        if (openssl_public_encrypt($Vr, $nf, $this->key, $this->cryptParams["\x70\x61\144\x64\151\x6e\x67"])) {
            goto MK;
        }
        throw new Exception("\106\x61\x69\154\x75\x72\145\x20\145\x6e\x63\162\171\x70\x74\x69\156\x67\x20\104\141\x74\141");
        return;
        MK:
        L7:
        return $nf;
    }
    private function decryptOpenSSL($Vr)
    {
        if ($this->cryptParams["\164\x79\x70\x65"] == "\x70\165\x62\154\151\x63") {
            goto Rw;
        }
        if (openssl_private_decrypt($Vr, $bB, $this->key, $this->cryptParams["\x70\141\x64\144\151\x6e\147"])) {
            goto iQ;
        }
        throw new Exception("\x46\x61\x69\154\x75\162\145\x20\x64\145\143\162\171\x70\x74\151\x6e\x67\x20\104\141\x74\141");
        return;
        iQ:
        goto w3;
        Rw:
        if (openssl_public_decrypt($Vr, $bB, $this->key, $this->cryptParams["\160\141\144\x64\x69\x6e\147"])) {
            goto HS;
        }
        throw new Exception("\106\141\x69\154\x75\162\145\x20\x64\x65\x63\162\171\160\164\x69\x6e\x67\40\x44\x61\x74\141");
        return;
        HS:
        w3:
        return $bB;
    }
    private function signOpenSSL($Vr)
    {
        $Sj = OPENSSL_ALGO_SHA1;
        if (empty($this->cryptParams["\144\x69\147\x65\x73\x74"])) {
            goto K0;
        }
        $Sj = $this->cryptParams["\x64\x69\147\145\163\164"];
        K0:
        if (openssl_sign($Vr, $mR, $this->key, $Sj)) {
            goto uM;
        }
        throw new Exception("\106\141\151\x6c\x75\x72\x65\40\123\x69\x67\x6e\151\156\x67\40\104\x61\x74\141\x3a\x20" . openssl_error_string() . "\x20\55\40" . $Sj);
        return;
        uM:
        return $mR;
    }
    private function verifyOpenSSL($Vr, $mR)
    {
        $Sj = OPENSSL_ALGO_SHA1;
        if (empty($this->cryptParams["\144\x69\x67\x65\x73\164"])) {
            goto aC;
        }
        $Sj = $this->cryptParams["\144\151\x67\145\x73\x74"];
        aC:
        return openssl_verify($Vr, $mR, $this->key, $Sj);
    }
    public function encryptData($Vr)
    {
        switch ($this->cryptParams["\x6c\x69\x62\162\141\162\x79"]) {
            case "\x6d\143\x72\171\160\164":
                return $this->encryptMcrypt($Vr);
                goto zk;
            case "\x6f\x70\145\x6e\163\x73\154":
                return $this->encryptOpenSSL($Vr);
                goto zk;
        }
        iC:
        zk:
    }
    public function decryptData($Vr)
    {
        switch ($this->cryptParams["\154\151\142\x72\141\x72\171"]) {
            case "\155\x63\x72\x79\160\x74":
                return $this->decryptMcrypt($Vr);
                goto pm;
            case "\157\160\145\x6e\163\x73\x6c":
                return $this->decryptOpenSSL($Vr);
                goto pm;
        }
        ou:
        pm:
    }
    public function signData($Vr)
    {
        switch ($this->cryptParams["\154\x69\x62\162\141\x72\171"]) {
            case "\x6f\160\145\x6e\163\163\154":
                return $this->signOpenSSL($Vr);
                goto tz;
        }
        sp:
        tz:
    }
    public function verifySignature($Vr, $mR)
    {
        switch ($this->cryptParams["\154\x69\142\162\x61\x72\171"]) {
            case "\157\x70\145\x6e\x73\163\154":
                return $this->verifyOpenSSL($Vr, $mR);
                goto VF;
        }
        fb:
        VF:
    }
    public function getAlgorith()
    {
        return $this->cryptParams["\x6d\145\x74\x68\157\x64"];
    }
    static function makeAsnSegment($Zc, $cU)
    {
        switch ($Zc) {
            case 0x2:
                if (!(ord($cU) > 0x7f)) {
                    goto sO;
                }
                $cU = chr(0) . $cU;
                sO:
                goto WA;
            case 0x3:
                $cU = chr(0) . $cU;
                goto WA;
        }
        Kh:
        WA:
        $Q5 = strlen($cU);
        if ($Q5 < 128) {
            goto u_;
        }
        if ($Q5 < 0x100) {
            goto nf;
        }
        if ($Q5 < 0x10000) {
            goto wx;
        }
        $DS = NULL;
        goto P9;
        wx:
        $DS = sprintf("\45\x63\45\x63\45\143\45\x63\x25\163", $Zc, 0x82, $Q5 / 0x100, $Q5 % 0x100, $cU);
        P9:
        goto Z5;
        nf:
        $DS = sprintf("\45\x63\x25\x63\x25\143\45\x73", $Zc, 0x81, $Q5, $cU);
        Z5:
        goto hf;
        u_:
        $DS = sprintf("\45\x63\x25\143\45\163", $Zc, $Q5, $cU);
        hf:
        return $DS;
    }
    static function convertRSA($gZ, $pe)
    {
        $Yj = XMLSecurityKeySAML::makeAsnSegment(0x2, $pe);
        $eO = XMLSecurityKeySAML::makeAsnSegment(0x2, $gZ);
        $TQ = XMLSecurityKeySAML::makeAsnSegment(0x30, $eO . $Yj);
        $Zu = XMLSecurityKeySAML::makeAsnSegment(0x3, $TQ);
        $kG = pack("\x48\x2a", "\63\x30\x30\104\60\66\x30\x39\62\101\70\66\x34\70\70\66\106\67\x30\x44\x30\61\x30\61\60\x31\x30\65\60\x30");
        $VL = XMLSecurityKeySAML::makeAsnSegment(0x30, $kG . $Zu);
        $Fg = base64_encode($VL);
        $sb = "\55\55\55\55\55\x42\105\x47\x49\x4e\40\120\x55\x42\x4c\x49\x43\40\x4b\105\x59\55\x2d\55\55\x2d\12";
        $PU = 0;
        xd:
        if (!($Ui = substr($Fg, $PU, 64))) {
            goto zS;
        }
        $sb = $sb . $Ui . "\xa";
        $PU += 64;
        goto xd;
        zS:
        return $sb . "\55\x2d\x2d\x2d\x2d\105\116\104\40\120\125\x42\114\x49\103\x20\113\105\131\55\x2d\x2d\x2d\x2d\12";
    }
    public function serializeKey($rQ)
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
    public static function fromEncryptedKeyElement(DOMElement $O9)
    {
        $tl = new XMLSecEncSAML();
        $tl->setNode($O9);
        if ($zz = $tl->locateKey()) {
            goto tF;
        }
        throw new Exception("\125\x6e\x61\142\154\145\40\x74\157\x20\x6c\x6f\143\x61\x74\145\x20\141\x6c\147\157\x72\151\164\x68\155\40\146\157\x72\40\x74\x68\x69\x73\40\105\156\143\162\171\x70\164\145\x64\40\113\145\171");
        tF:
        $zz->isEncrypted = TRUE;
        $zz->encryptedCtx = $tl;
        XMLSecEncSAML::staticLocateKeyInfo($zz, $O9);
        return $zz;
    }
}
class XMLSecurityDSigSAML
{
    const XMLDSIGNS = "\150\x74\x74\x70\72\x2f\x2f\x77\167\167\x2e\x77\63\x2e\x6f\x72\147\57\x32\60\60\x30\57\x30\71\57\170\155\x6c\x64\163\151\x67\x23";
    const SHA1 = "\150\164\x74\x70\72\x2f\x2f\167\x77\x77\x2e\167\63\56\157\162\147\57\x32\60\x30\60\x2f\60\71\x2f\170\x6d\x6c\144\163\151\x67\x23\163\150\141\61";
    const SHA256 = "\x68\164\164\160\x3a\57\57\x77\167\x77\56\x77\x33\56\x6f\x72\x67\x2f\x32\x30\60\x31\x2f\60\64\57\x78\x6d\x6c\145\156\x63\x23\x73\x68\141\x32\x35\66";
    const SHA384 = "\x68\164\164\160\72\x2f\x2f\167\x77\x77\56\x77\x33\x2e\x6f\162\x67\57\62\x30\60\61\57\60\64\57\170\x6d\x6c\144\163\151\x67\x2d\155\x6f\x72\145\x23\163\150\141\x33\70\64";
    const SHA512 = "\150\x74\x74\160\72\x2f\x2f\x77\x77\x77\56\x77\x33\56\x6f\x72\147\x2f\62\60\x30\61\57\60\64\57\x78\x6d\154\145\156\x63\43\x73\150\x61\x35\x31\62";
    const RIPEMD160 = "\x68\x74\164\160\72\x2f\57\167\x77\x77\56\x77\63\x2e\x6f\x72\147\57\x32\x30\60\x31\x2f\x30\64\57\170\155\x6c\145\x6e\143\x23\x72\151\160\x65\155\x64\x31\x36\60";
    const C14N = "\150\164\x74\x70\72\x2f\x2f\167\x77\x77\x2e\167\x33\56\157\162\x67\57\x54\122\57\x32\x30\x30\x31\57\x52\105\x43\55\170\x6d\x6c\x2d\x63\61\64\156\55\x32\60\60\61\60\63\x31\65";
    const C14N_COMMENTS = "\150\164\164\160\x3a\57\57\x77\167\x77\x2e\x77\x33\x2e\157\x72\147\57\x54\122\x2f\x32\60\x30\x31\57\122\x45\103\55\x78\x6d\x6c\55\143\61\64\156\55\x32\x30\x30\61\x30\x33\x31\x35\x23\127\x69\x74\150\x43\157\155\x6d\x65\x6e\164\163";
    const EXC_C14N = "\x68\164\x74\x70\72\57\57\167\x77\167\x2e\x77\63\56\157\x72\147\57\x32\60\60\61\57\x31\60\x2f\x78\x6d\x6c\55\x65\170\x63\x2d\143\61\64\156\43";
    const EXC_C14N_COMMENTS = "\x68\164\x74\160\x3a\57\57\x77\167\167\x2e\167\x33\56\x6f\162\x67\57\x32\60\x30\x31\x2f\x31\60\x2f\170\155\154\55\x65\x78\143\x2d\x63\61\64\156\x23\x57\151\x74\150\x43\157\155\x6d\145\x6e\164\x73";
    const template = "\x3c\144\163\x3a\x53\151\x67\156\141\x74\x75\162\145\x20\170\x6d\x6c\156\x73\72\144\x73\x3d\42\150\164\x74\x70\x3a\57\57\167\167\x77\x2e\167\63\56\157\162\147\57\62\x30\60\x30\x2f\x30\x39\57\170\155\154\144\163\151\x67\x23\42\x3e\xd\12\x20\40\x3c\x64\x73\x3a\x53\x69\147\156\145\x64\111\156\x66\157\76\xd\xa\40\x20\x20\x20\74\x64\163\72\x53\x69\147\156\x61\164\165\162\x65\x4d\145\x74\150\157\144\40\x2f\x3e\xd\12\40\x20\74\57\x64\163\x3a\x53\x69\147\x6e\x65\x64\111\x6e\146\157\x3e\15\12\74\57\144\x73\72\x53\151\x67\x6e\141\164\165\x72\145\76";
    public $sigNode = NULL;
    public $idKeys = array();
    public $idNS = array();
    private $signedInfo = NULL;
    private $xPathCtx = NULL;
    private $canonicalMethod = NULL;
    private $prefix = "\144\x73";
    private $searchpfx = "\x73\x65\143\144\x73\151\x67";
    private $validatedNodes = NULL;
    public function __construct()
    {
        $ix = new DOMDocument();
        $ix->loadXML(XMLSecurityDSigSAML::template);
        $this->sigNode = $ix->documentElement;
    }
    private function resetXPathObj()
    {
        $this->xPathCtx = NULL;
    }
    private function getXPathObj()
    {
        if (!(empty($this->xPathCtx) && !empty($this->sigNode))) {
            goto AF;
        }
        $N2 = new DOMXPath($this->sigNode->ownerDocument);
        $N2->registerNamespace("\x73\145\x63\144\x73\151\147", XMLSecurityDSigSAML::XMLDSIGNS);
        $this->xPathCtx = $N2;
        AF:
        return $this->xPathCtx;
    }
    static function generate_GUID($MD = "\x70\x66\170")
    {
        $eV = md5(uniqid(rand(), true));
        $bx = $MD . substr($eV, 0, 8) . "\x2d" . substr($eV, 8, 4) . "\55" . substr($eV, 12, 4) . "\55" . substr($eV, 16, 4) . "\x2d" . substr($eV, 20, 12);
        return $bx;
    }
    public function locateSignature($q0)
    {
        if ($q0 instanceof DOMDocument) {
            goto eG;
        }
        $qW = $q0->ownerDocument;
        goto Bz;
        eG:
        $qW = $q0;
        Bz:
        if (!$qW) {
            goto pM;
        }
        $N2 = new DOMXPath($qW);
        $N2->registerNamespace("\163\x65\x63\144\163\x69\147", XMLSecurityDSigSAML::XMLDSIGNS);
        $le = "\56\x2f\57\163\x65\x63\x64\x73\151\147\72\123\151\x67\156\141\164\165\162\x65";
        $Mc = $N2->query($le, $q0);
        $this->sigNode = $Mc->item(0);
        return $this->sigNode;
        pM:
        return NULL;
    }
    public function createNewSignNode($JN, $UH = NULL)
    {
        $qW = $this->sigNode->ownerDocument;
        if (!is_null($UH)) {
            goto Ur;
        }
        $Il = $qW->createElementNS(XMLSecurityDSigSAML::XMLDSIGNS, $this->prefix . "\72" . $JN);
        goto KA;
        Ur:
        $Il = $qW->createElementNS(XMLSecurityDSigSAML::XMLDSIGNS, $this->prefix . "\x3a" . $JN, $UH);
        KA:
        return $Il;
    }
    public function setCanonicalMethod($VT)
    {
        switch ($VT) {
            case "\150\x74\164\x70\72\57\57\167\x77\167\56\167\63\x2e\157\x72\x67\57\124\x52\57\62\x30\x30\61\57\x52\105\103\55\170\155\x6c\55\143\61\x34\156\55\62\x30\x30\61\x30\x33\x31\x35":
            case "\x68\x74\x74\x70\x3a\x2f\x2f\167\x77\167\x2e\167\63\x2e\x6f\162\147\57\x54\122\57\62\60\x30\x31\x2f\x52\x45\103\x2d\x78\x6d\x6c\55\x63\61\x34\156\x2d\62\x30\60\x31\x30\x33\61\x35\x23\x57\151\x74\150\103\x6f\155\x6d\145\x6e\164\x73":
            case "\x68\x74\164\160\x3a\57\57\x77\x77\x77\x2e\x77\63\x2e\x6f\162\147\x2f\62\x30\x30\x31\57\x31\60\x2f\170\155\x6c\x2d\145\170\x63\x2d\x63\x31\64\x6e\43":
            case "\x68\x74\164\160\x3a\57\57\x77\x77\167\56\167\63\56\x6f\162\147\57\x32\60\x30\x31\57\61\x30\57\x78\155\154\x2d\145\x78\143\x2d\143\61\x34\x6e\x23\x57\x69\164\150\103\x6f\155\155\x65\x6e\164\x73":
                $this->canonicalMethod = $VT;
                goto Jf;
            default:
                throw new Exception("\111\156\x76\x61\x6c\x69\x64\x20\103\x61\156\x6f\156\x69\x63\141\154\40\115\x65\164\150\157\144");
        }
        Cn:
        Jf:
        if (!($N2 = $this->getXPathObj())) {
            goto wA;
        }
        $le = "\56\x2f" . $this->searchpfx . "\x3a\123\x69\147\x6e\145\x64\x49\x6e\x66\x6f";
        $Mc = $N2->query($le, $this->sigNode);
        if (!($xD = $Mc->item(0))) {
            goto IB;
        }
        $le = "\x2e\57" . $this->searchpfx . "\103\x61\x6e\x6f\156\x69\x63\141\x6c\151\x7a\x61\164\151\157\x6e\x4d\x65\164\150\x6f\144";
        $Mc = $N2->query($le, $xD);
        if ($jE = $Mc->item(0)) {
            goto jk;
        }
        $jE = $this->createNewSignNode("\103\x61\156\157\156\151\x63\x61\154\x69\x7a\141\164\x69\x6f\156\115\145\164\x68\x6f\x64");
        $xD->insertBefore($jE, $xD->firstChild);
        jk:
        $jE->setAttribute("\x41\x6c\147\157\162\151\x74\x68\155", $this->canonicalMethod);
        IB:
        wA:
    }
    private function canonicalizeData($Il, $mQ, $Mw = NULL, $K8 = NULL)
    {
        $Wi = FALSE;
        $zh = FALSE;
        switch ($mQ) {
            case "\150\164\164\x70\x3a\57\x2f\167\167\x77\56\x77\63\x2e\157\162\147\57\x54\x52\57\62\60\x30\61\x2f\x52\105\x43\x2d\x78\x6d\x6c\x2d\x63\61\x34\156\55\x32\60\60\61\x30\x33\x31\x35":
                $Wi = FALSE;
                $zh = FALSE;
                goto tS;
            case "\x68\164\x74\x70\x3a\57\x2f\x77\x77\167\x2e\x77\x33\56\157\162\147\x2f\x54\122\x2f\x32\x30\x30\61\57\122\105\103\x2d\x78\155\x6c\55\x63\x31\x34\156\x2d\62\60\60\x31\x30\x33\x31\65\43\x57\151\164\150\103\157\x6d\x6d\x65\x6e\164\163":
                $zh = TRUE;
                goto tS;
            case "\x68\164\164\160\x3a\57\57\167\x77\x77\x2e\167\63\56\x6f\162\147\57\x32\60\x30\x31\57\61\x30\x2f\170\155\x6c\55\145\x78\143\55\143\61\64\156\43":
                $Wi = TRUE;
                goto tS;
            case "\x68\x74\x74\x70\72\x2f\x2f\x77\x77\x77\x2e\167\63\56\x6f\162\147\57\62\x30\60\x31\57\x31\60\57\x78\155\x6c\55\x65\170\x63\x2d\143\x31\64\x6e\43\127\x69\164\150\x43\157\x6d\155\x65\156\x74\163":
                $Wi = TRUE;
                $zh = TRUE;
                goto tS;
        }
        jS:
        tS:
        $m2 = explode("\56", PHP_VERSION);
        if (!($m2[0] < 5 || $m2[0] == 5 && $m2[1] < 2)) {
            goto xs;
        }
        if (is_null($Mw)) {
            goto og;
        }
        throw new Exception("\x50\110\x50\40\65\x2e\62\56\60\40\x6f\162\40\x68\x69\x67\x68\145\x72\x20\151\163\40\x72\x65\161\165\151\x72\145\x64\x20\x74\157\40\160\x65\162\146\x6f\x72\x6d\x20\x58\120\x61\x74\x68\x20\124\x72\141\x6e\163\146\x6f\162\155\141\164\x69\x6f\x6e\163");
        og:
        return C14NGeneral($Il, $Wi, $zh);
        xs:
        $O9 = $Il;
        if (!($Il instanceof DOMNode && $Il->ownerDocument !== NULL && $Il->isSameNode($Il->ownerDocument->documentElement))) {
            goto Co;
        }
        $O9 = $Il->ownerDocument;
        Co:
        return $O9->C14N($Wi, $zh, $Mw, $K8);
    }
    public function canonicalizeSignedInfo()
    {
        $qW = $this->sigNode->ownerDocument;
        $mQ = NULL;
        if (!$qW) {
            goto wc;
        }
        $N2 = $this->getXPathObj();
        $le = "\56\x2f\x73\x65\143\x64\x73\151\147\x3a\x53\x69\x67\156\x65\144\111\156\146\x6f";
        $Mc = $N2->query($le, $this->sigNode);
        if (!($kh = $Mc->item(0))) {
            goto Ao;
        }
        $le = "\x2e\x2f\163\x65\x63\144\163\151\x67\72\103\141\x6e\157\x6e\x69\x63\141\x6c\151\172\x61\164\151\x6f\x6e\x4d\x65\164\150\x6f\144";
        $Mc = $N2->query($le, $kh);
        if (!($jE = $Mc->item(0))) {
            goto oN;
        }
        $mQ = $jE->getAttribute("\x41\154\147\157\162\x69\x74\x68\155");
        oN:
        $this->signedInfo = $this->canonicalizeData($kh, $mQ);
        return $this->signedInfo;
        Ao:
        wc:
        return NULL;
    }
    public function calculateDigest($W6, $Vr)
    {
        switch ($W6) {
            case XMLSecurityDSigSAML::SHA1:
                $yf = "\163\150\141\61";
                goto f2;
            case XMLSecurityDSigSAML::SHA256:
                $yf = "\x73\150\x61\62\x35\66";
                goto f2;
            case XMLSecurityDSigSAML::SHA384:
                $yf = "\163\150\x61\63\70\x34";
                goto f2;
            case XMLSecurityDSigSAML::SHA512:
                $yf = "\x73\150\x61\x35\x31\62";
                goto f2;
            case XMLSecurityDSigSAML::RIPEMD160:
                $yf = "\162\x69\160\x65\x6d\144\61\x36\x30";
                goto f2;
            default:
                throw new Exception("\x43\x61\x6e\x6e\x6f\164\x20\166\141\154\x69\x64\141\164\x65\x20\x64\x69\147\145\x73\164\72\x20\x55\x6e\x73\165\160\x70\x6f\162\x74\x65\x64\x20\101\x6c\147\x6f\162\x69\x74\x68\x20\74{$W6}\76");
        }
        ky:
        f2:
        if (function_exists("\x68\x61\163\x68")) {
            goto ju;
        }
        if (function_exists("\155\150\x61\x73\150")) {
            goto bk;
        }
        if ($yf === "\163\x68\x61\61") {
            goto ga;
        }
        throw new Exception("\170\x6d\x6c\x73\145\x63\x6c\151\142\x73\x20\x69\163\x20\x75\x6e\141\x62\154\145\x20\x74\x6f\x20\143\x61\x6c\143\165\x6c\141\164\145\x20\x61\40\x64\x69\147\145\163\164\x2e\40\115\141\x79\x62\145\x20\171\157\x75\x20\156\x65\145\x64\40\164\x68\145\x20\x6d\x68\x61\163\x68\40\154\151\x62\x72\141\162\171\x3f");
        goto tx;
        ju:
        return base64_encode(hash($yf, $Vr, TRUE));
        goto tx;
        bk:
        $yf = "\x4d\110\x41\x53\110\x5f" . strtoupper($yf);
        return base64_encode(mhash(constant($yf), $Vr));
        goto tx;
        ga:
        return base64_encode(sha1($Vr, TRUE));
        tx:
    }
    public function validateDigest($al, $Vr)
    {
        $N2 = new DOMXPath($al->ownerDocument);
        $N2->registerNamespace("\x73\x65\x63\144\x73\x69\147", XMLSecurityDSigSAML::XMLDSIGNS);
        $le = "\x73\x74\162\151\x6e\x67\x28\x2e\x2f\163\145\x63\144\163\151\147\72\x44\x69\x67\145\163\164\115\x65\x74\150\157\x64\57\x40\101\x6c\147\157\x72\151\x74\150\155\x29";
        $W6 = $N2->evaluate($le, $al);
        $Gh = $this->calculateDigest($W6, $Vr);
        $le = "\163\x74\x72\151\x6e\x67\50\x2e\x2f\163\145\143\x64\x73\x69\x67\x3a\104\x69\x67\145\163\x74\x56\141\154\x75\145\x29";
        $ql = $N2->evaluate($le, $al);
        return $Gh == $ql;
    }
    public function processTransforms($al, $eF, $F_ = TRUE)
    {
        $Vr = $eF;
        $N2 = new DOMXPath($al->ownerDocument);
        $N2->registerNamespace("\163\x65\143\144\x73\151\x67", XMLSecurityDSigSAML::XMLDSIGNS);
        $le = "\56\x2f\x73\x65\143\144\163\x69\x67\72\x54\x72\x61\x6e\x73\x66\x6f\x72\x6d\x73\57\163\145\x63\x64\163\x69\147\x3a\x54\x72\141\x6e\163\146\157\x72\155";
        $TR = $N2->query($le, $al);
        $I6 = "\x68\x74\x74\160\72\x2f\x2f\167\167\167\x2e\167\63\x2e\157\162\147\57\x54\x52\57\x32\x30\60\61\x2f\x52\x45\x43\55\x78\x6d\x6c\x2d\x63\x31\64\x6e\x2d\62\60\x30\61\60\x33\x31\x35";
        $Mw = NULL;
        $K8 = NULL;
        foreach ($TR as $UF) {
            $Nj = $UF->getAttribute("\x41\154\147\157\x72\x69\x74\150\x6d");
            switch ($Nj) {
                case "\150\164\x74\x70\x3a\57\57\x77\x77\167\x2e\x77\63\56\157\x72\x67\57\x32\60\60\61\x2f\61\60\57\x78\155\154\55\145\x78\143\55\143\61\64\x6e\43":
                case "\x68\x74\164\160\72\57\x2f\x77\167\x77\x2e\167\63\x2e\157\162\147\x2f\x32\x30\x30\x31\57\x31\60\x2f\x78\x6d\x6c\55\145\x78\143\55\x63\61\64\156\x23\127\151\x74\x68\x43\157\155\x6d\145\156\x74\x73":
                    if (!$F_) {
                        goto pi;
                    }
                    $I6 = $Nj;
                    goto aM;
                    pi:
                    $I6 = "\x68\x74\164\x70\x3a\x2f\x2f\167\167\x77\56\x77\x33\x2e\x6f\162\147\57\62\x30\x30\61\x2f\x31\60\x2f\x78\x6d\x6c\x2d\145\x78\x63\55\143\x31\x34\156\x23";
                    aM:
                    $Il = $UF->firstChild;
                    v8:
                    if (!$Il) {
                        goto Oz;
                    }
                    if (!($Il->localName == "\111\x6e\x63\x6c\165\x73\151\166\145\116\141\155\x65\163\x70\141\143\145\163")) {
                        goto HN;
                    }
                    if (!($il = $Il->getAttribute("\120\162\x65\x66\x69\x78\x4c\x69\x73\164"))) {
                        goto e6;
                    }
                    $g2 = array();
                    $Ol = explode("\40", $il);
                    foreach ($Ol as $il) {
                        $X1 = trim($il);
                        if (empty($X1)) {
                            goto Oe;
                        }
                        $g2[] = $X1;
                        Oe:
                        es:
                    }
                    nG:
                    if (!(count($g2) > 0)) {
                        goto vk;
                    }
                    $K8 = $g2;
                    vk:
                    e6:
                    goto Oz;
                    HN:
                    $Il = $Il->nextSibling;
                    goto v8;
                    Oz:
                    goto FP;
                case "\150\x74\164\160\72\57\57\167\x77\167\x2e\x77\63\x2e\157\162\147\57\124\x52\x2f\62\x30\x30\x31\x2f\x52\105\x43\x2d\170\155\x6c\x2d\x63\61\x34\156\x2d\62\60\60\61\60\x33\x31\x35":
                case "\x68\x74\x74\160\x3a\x2f\x2f\x77\167\x77\x2e\167\x33\56\x6f\x72\x67\57\x54\122\57\62\60\x30\61\57\x52\105\103\x2d\170\155\x6c\55\x63\61\x34\x6e\x2d\62\x30\60\61\60\x33\x31\x35\x23\x57\x69\x74\x68\103\157\x6d\x6d\x65\156\x74\x73":
                    if (!$F_) {
                        goto Tu;
                    }
                    $I6 = $Nj;
                    goto Xo;
                    Tu:
                    $I6 = "\x68\164\x74\160\x3a\57\57\x77\x77\x77\x2e\167\63\56\157\162\147\57\124\122\x2f\x32\x30\60\x31\57\122\105\x43\x2d\x78\155\x6c\55\x63\61\x34\156\x2d\x32\x30\x30\61\x30\x33\61\x35";
                    Xo:
                    goto FP;
                case "\x68\x74\x74\160\x3a\x2f\x2f\167\167\x77\x2e\167\x33\56\157\x72\x67\x2f\x54\x52\x2f\61\x39\x39\x39\57\122\x45\103\x2d\170\160\x61\x74\150\55\61\71\71\x39\61\61\x31\66":
                    $Il = $UF->firstChild;
                    UL:
                    if (!$Il) {
                        goto bG;
                    }
                    if (!($Il->localName == "\130\120\141\164\x68")) {
                        goto cZ;
                    }
                    $Mw = array();
                    $Mw["\161\x75\145\162\171"] = "\50\x2e\57\x2f\56\40\x7c\x20\56\x2f\x2f\x40\x2a\40\174\x20\x2e\57\57\156\x61\x6d\x65\163\x70\141\x63\145\x3a\x3a\x2a\51\133" . $Il->nodeValue . "\x5d";
                    $vG["\x6e\x61\155\x65\163\x70\141\143\145\163"] = array();
                    $xo = $N2->query("\x2e\x2f\156\x61\155\x65\163\160\141\x63\145\72\72\x2a", $Il);
                    foreach ($xo as $Xl) {
                        if (!($Xl->localName != "\170\155\x6c")) {
                            goto hR;
                        }
                        $Mw["\156\141\x6d\x65\x73\160\x61\143\x65\163"][$Xl->localName] = $Xl->nodeValue;
                        hR:
                        di:
                    }
                    LC:
                    goto bG;
                    cZ:
                    $Il = $Il->nextSibling;
                    goto UL;
                    bG:
                    goto FP;
            }
            v9:
            FP:
            Gi:
        }
        Pc:
        if (!$Vr instanceof DOMNode) {
            goto by;
        }
        $Vr = $this->canonicalizeData($eF, $I6, $Mw, $K8);
        by:
        return $Vr;
    }
    public function processRefNode($al)
    {
        $Gi = NULL;
        $F_ = TRUE;
        if ($O3 = $al->getAttribute("\125\122\111")) {
            goto EA;
        }
        $F_ = FALSE;
        $Gi = $al->ownerDocument;
        goto eC;
        EA:
        $ho = parse_url($O3);
        if (empty($ho["\x70\141\164\x68"])) {
            goto Rp;
        }
        $Gi = file_get_contents($ho);
        goto cf;
        Rp:
        if ($dV = $ho["\146\162\141\147\155\145\x6e\x74"]) {
            goto Hc;
        }
        $Gi = $al->ownerDocument;
        goto O1;
        Hc:
        $F_ = FALSE;
        $PY = new DOMXPath($al->ownerDocument);
        if (!($this->idNS && is_array($this->idNS))) {
            goto zq;
        }
        foreach ($this->idNS as $rR => $be) {
            $PY->registerNamespace($rR, $be);
            qy:
        }
        Iq:
        zq:
        $KX = "\100\111\144\75\42" . $dV . "\x22";
        if (!is_array($this->idKeys)) {
            goto Ib;
        }
        foreach ($this->idKeys as $pc) {
            $KX .= "\x20\157\x72\x20\x40{$pc}\x3d\x27{$dV}\47";
            G7:
        }
        Tb:
        Ib:
        $le = "\x2f\x2f\52\x5b" . $KX . "\x5d";
        $Gi = $PY->query($le)->item(0);
        O1:
        cf:
        eC:
        $Vr = $this->processTransforms($al, $Gi, $F_);
        if ($this->validateDigest($al, $Vr)) {
            goto We;
        }
        return FALSE;
        We:
        if (!$Gi instanceof DOMNode) {
            goto ze;
        }
        if (!empty($dV)) {
            goto xS;
        }
        $this->validatedNodes[] = $Gi;
        goto hy;
        xS:
        $this->validatedNodes[$dV] = $Gi;
        hy:
        ze:
        return TRUE;
    }
    public function getRefNodeID($al)
    {
        if (!($O3 = $al->getAttribute("\x55\x52\111"))) {
            goto tU;
        }
        $ho = parse_url($O3);
        if (!empty($ho["\160\x61\164\x68"])) {
            goto W5;
        }
        if (!($dV = $ho["\146\x72\141\x67\155\x65\156\x74"])) {
            goto yL;
        }
        return $dV;
        yL:
        W5:
        tU:
        return null;
    }
    public function getRefIDs()
    {
        $Cv = array();
        $qW = $this->sigNode->ownerDocument;
        $N2 = $this->getXPathObj();
        $le = "\x2e\57\163\x65\143\x64\x73\151\x67\72\123\x69\147\x6e\x65\x64\111\156\x66\157\57\163\145\143\144\x73\151\x67\72\x52\x65\146\x65\162\145\156\143\x65";
        $Mc = $N2->query($le, $this->sigNode);
        if (!($Mc->length == 0)) {
            goto wn;
        }
        throw new Exception("\122\145\146\x65\162\145\x6e\x63\x65\x20\x6e\x6f\x64\x65\163\40\x6e\157\x74\40\146\x6f\x75\156\144");
        wn:
        foreach ($Mc as $al) {
            $Cv[] = $this->getRefNodeID($al);
            hH:
        }
        D3:
        return $Cv;
    }
    public function validateReference()
    {
        $qW = $this->sigNode->ownerDocument;
        if ($qW->isSameNode($this->sigNode)) {
            goto kp;
        }
        $this->sigNode->parentNode->removeChild($this->sigNode);
        kp:
        $N2 = $this->getXPathObj();
        $le = "\56\x2f\163\145\143\x64\163\x69\x67\72\x53\x69\147\156\145\x64\111\x6e\146\157\57\163\145\x63\144\x73\x69\147\72\122\x65\146\145\162\x65\x6e\x63\x65";
        $Mc = $N2->query($le, $this->sigNode);
        if (!($Mc->length == 0)) {
            goto sf;
        }
        throw new Exception("\x52\x65\x66\x65\x72\x65\156\x63\145\40\156\157\144\145\163\40\x6e\x6f\164\x20\x66\157\x75\156\x64");
        sf:
        $this->validatedNodes = array();
        foreach ($Mc as $al) {
            if ($this->processRefNode($al)) {
                goto WO;
            }
            $this->validatedNodes = NULL;
            throw new Exception("\122\x65\x66\145\162\145\156\143\x65\x20\x76\141\154\x69\x64\141\164\x69\x6f\x6e\40\x66\x61\151\x6c\x65\144");
            WO:
            lf:
        }
        s6:
        return TRUE;
    }
    private function addRefInternal($kD, $Il, $Nj, $ln = NULL, $CX = NULL)
    {
        $MD = NULL;
        $aF = NULL;
        $Id = "\x49\x64";
        $wM = TRUE;
        $eY = FALSE;
        if (!is_array($CX)) {
            goto Vo;
        }
        $MD = empty($CX["\x70\162\x65\146\151\x78"]) ? NULL : $CX["\x70\x72\x65\146\151\170"];
        $aF = empty($CX["\160\162\145\146\151\170\137\156\x73"]) ? NULL : $CX["\160\x72\x65\146\151\170\x5f\156\x73"];
        $Id = empty($CX["\x69\144\x5f\156\141\x6d\145"]) ? "\x49\144" : $CX["\151\x64\137\x6e\141\x6d\145"];
        $wM = !isset($CX["\x6f\166\145\x72\x77\x72\151\164\x65"]) ? TRUE : (bool) $CX["\157\166\145\162\x77\162\x69\x74\145"];
        $eY = !isset($CX["\146\157\162\x63\145\137\x75\162\x69"]) ? FALSE : (bool) $CX["\x66\157\162\143\x65\x5f\165\x72\x69"];
        Vo:
        $Qv = $Id;
        if (empty($MD)) {
            goto Kw;
        }
        $Qv = $MD . "\x3a" . $Qv;
        Kw:
        $al = $this->createNewSignNode("\x52\x65\x66\x65\162\x65\156\x63\x65");
        $kD->appendChild($al);
        if (!$Il instanceof DOMDocument) {
            goto vw;
        }
        if ($eY) {
            goto f7;
        }
        goto ds;
        vw:
        $O3 = NULL;
        if ($wM) {
            goto n5;
        }
        $O3 = $Il->getAttributeNS($aF, $Id);
        n5:
        if (!empty($O3)) {
            goto Y2;
        }
        $O3 = XMLSecurityDSigSAML::generate_GUID();
        $Il->setAttributeNS($aF, $Qv, $O3);
        Y2:
        $al->setAttribute("\125\x52\111", "\43" . $O3);
        goto ds;
        f7:
        $al->setAttribute("\125\122\111", '');
        ds:
        $Fv = $this->createNewSignNode("\124\162\141\x6e\x73\146\157\162\155\163");
        $al->appendChild($Fv);
        if (is_array($ln)) {
            goto Qt;
        }
        if (!empty($this->canonicalMethod)) {
            goto Re;
        }
        goto W2;
        Qt:
        foreach ($ln as $UF) {
            $vV = $this->createNewSignNode("\x54\162\141\156\x73\146\157\162\155");
            $Fv->appendChild($vV);
            if (is_array($UF) && !empty($UF["\x68\x74\164\160\x3a\57\x2f\167\x77\167\x2e\x77\x33\x2e\x6f\x72\147\57\124\x52\57\x31\x39\x39\x39\57\122\x45\103\55\170\x70\x61\x74\150\x2d\x31\71\x39\71\61\x31\x31\x36"]) && !empty($UF["\150\x74\x74\160\72\57\57\167\x77\x77\56\167\63\x2e\x6f\x72\147\x2f\124\122\x2f\x31\x39\x39\x39\57\122\x45\x43\x2d\x78\x70\x61\164\150\55\61\x39\71\x39\x31\61\x31\x36"]["\161\165\145\162\171"])) {
                goto hx;
            }
            $vV->setAttribute("\101\154\x67\157\162\151\164\150\155", $UF);
            goto du;
            hx:
            $vV->setAttribute("\101\x6c\x67\157\162\x69\164\x68\155", "\x68\x74\x74\x70\x3a\57\x2f\x77\167\167\56\167\x33\x2e\x6f\x72\147\x2f\124\x52\57\x31\71\71\x39\57\122\x45\x43\x2d\x78\160\141\x74\150\55\x31\x39\71\x39\61\x31\x31\66");
            $pM = $this->createNewSignNode("\130\120\x61\x74\x68", $UF["\150\164\164\x70\x3a\57\57\167\x77\x77\x2e\167\x33\x2e\157\162\x67\x2f\x54\122\57\x31\x39\x39\x39\x2f\122\105\x43\x2d\x78\x70\141\x74\x68\x2d\61\71\71\71\x31\61\61\x36"]["\161\165\145\162\171"]);
            $vV->appendChild($pM);
            if (empty($UF["\150\x74\164\160\72\x2f\x2f\x77\x77\x77\56\x77\63\56\157\162\x67\x2f\x54\122\57\61\71\x39\x39\57\122\x45\x43\x2d\170\x70\141\164\x68\x2d\x31\71\x39\x39\x31\61\x31\66"]["\156\x61\155\x65\163\160\x61\143\x65\x73"])) {
                goto rn;
            }
            foreach ($UF["\150\x74\164\x70\x3a\x2f\x2f\x77\167\x77\x2e\167\63\56\157\162\x67\57\124\122\57\x31\71\71\x39\x2f\x52\105\103\55\x78\160\141\164\x68\x2d\61\x39\71\x39\x31\x31\61\66"]["\156\141\155\145\x73\x70\141\x63\x65\x73"] as $MD => $px) {
                $pM->setAttributeNS("\x68\164\x74\x70\72\x2f\x2f\x77\x77\x77\56\167\63\56\x6f\x72\x67\57\62\60\60\60\57\x78\155\154\156\163\x2f", "\170\155\x6c\x6e\163\72{$MD}", $px);
                kf:
            }
            Gu:
            rn:
            du:
            Iu:
        }
        AH:
        goto W2;
        Re:
        $vV = $this->createNewSignNode("\124\x72\141\x6e\x73\146\157\162\x6d");
        $Fv->appendChild($vV);
        $vV->setAttribute("\101\154\147\x6f\162\151\x74\150\155", $this->canonicalMethod);
        W2:
        $ca = $this->processTransforms($al, $Il);
        $Gh = $this->calculateDigest($Nj, $ca);
        $vb = $this->createNewSignNode("\x44\151\x67\145\163\x74\115\x65\x74\150\157\x64");
        $al->appendChild($vb);
        $vb->setAttribute("\101\x6c\147\x6f\x72\151\x74\x68\155", $Nj);
        $ql = $this->createNewSignNode("\104\x69\147\x65\163\x74\126\141\x6c\165\x65", $Gh);
        $al->appendChild($ql);
    }
    public function addReference($Il, $Nj, $ln = NULL, $CX = NULL)
    {
        if (!($N2 = $this->getXPathObj())) {
            goto Lj;
        }
        $le = "\56\57\163\145\x63\144\163\151\147\72\x53\151\147\x6e\145\144\111\x6e\x66\x6f";
        $Mc = $N2->query($le, $this->sigNode);
        if (!($Gb = $Mc->item(0))) {
            goto Lq;
        }
        $this->addRefInternal($Gb, $Il, $Nj, $ln, $CX);
        Lq:
        Lj:
    }
    public function addReferenceList($Cx, $Nj, $ln = NULL, $CX = NULL)
    {
        if (!($N2 = $this->getXPathObj())) {
            goto B2;
        }
        $le = "\x2e\x2f\163\145\x63\144\x73\x69\147\x3a\x53\151\x67\156\145\144\x49\x6e\x66\157";
        $Mc = $N2->query($le, $this->sigNode);
        if (!($Gb = $Mc->item(0))) {
            goto nK;
        }
        foreach ($Cx as $Il) {
            $this->addRefInternal($Gb, $Il, $Nj, $ln, $CX);
            w6:
        }
        k4:
        nK:
        B2:
    }
    public function addObject($Vr, $Ze = NULL, $sb = NULL)
    {
        $KD = $this->createNewSignNode("\x4f\142\152\145\x63\164");
        $this->sigNode->appendChild($KD);
        if (empty($Ze)) {
            goto DC;
        }
        $KD->setAtribute("\115\151\x6d\145\x54\171\x70\145", $Ze);
        DC:
        if (empty($sb)) {
            goto LX;
        }
        $KD->setAttribute("\x45\156\x63\x6f\x64\x69\156\147", $sb);
        LX:
        if ($Vr instanceof DOMElement) {
            goto Ic;
        }
        $l0 = $this->sigNode->ownerDocument->createTextNode($Vr);
        goto n_;
        Ic:
        $l0 = $this->sigNode->ownerDocument->importNode($Vr, TRUE);
        n_:
        $KD->appendChild($l0);
        return $KD;
    }
    public function locateKey($Il = NULL)
    {
        if (!empty($Il)) {
            goto Y6;
        }
        $Il = $this->sigNode;
        Y6:
        if ($Il instanceof DOMNode) {
            goto ux;
        }
        return NULL;
        ux:
        if (!($qW = $Il->ownerDocument)) {
            goto Cp;
        }
        $N2 = new DOMXPath($qW);
        $N2->registerNamespace("\163\145\x63\x64\163\151\147", XMLSecurityDSigSAML::XMLDSIGNS);
        $le = "\x73\164\162\x69\156\147\x28\56\57\x73\145\143\144\x73\x69\x67\x3a\123\151\x67\x6e\145\144\x49\x6e\146\x6f\57\163\x65\143\x64\x73\x69\x67\72\x53\151\x67\156\x61\164\x75\162\x65\x4d\x65\164\150\x6f\x64\x2f\100\x41\154\x67\157\x72\x69\164\x68\155\x29";
        $Nj = $N2->evaluate($le, $Il);
        if (!$Nj) {
            goto HM;
        }
        try {
            $zz = new XMLSecurityKeySAML($Nj, array("\164\x79\160\145" => "\160\x75\142\154\x69\x63"));
        } catch (Exception $jf) {
            return NULL;
        }
        return $zz;
        HM:
        Cp:
        return NULL;
    }
    public function verify($zz)
    {
        $qW = $this->sigNode->ownerDocument;
        $N2 = new DOMXPath($qW);
        $N2->registerNamespace("\x73\x65\x63\x64\x73\151\147", XMLSecurityDSigSAML::XMLDSIGNS);
        $le = "\x73\164\x72\x69\x6e\x67\50\56\x2f\x73\x65\x63\x64\x73\151\147\x3a\123\x69\147\156\141\x74\x75\162\x65\126\141\154\165\145\x29";
        $Yo = $N2->evaluate($le, $this->sigNode);
        if (!empty($Yo)) {
            goto lt;
        }
        throw new Exception("\125\156\141\142\x6c\x65\40\164\157\x20\x6c\157\x63\141\x74\145\x20\123\x69\x67\156\x61\164\165\162\145\x56\x61\154\165\145");
        lt:
        return $zz->verifySignature($this->signedInfo, base64_decode($Yo));
    }
    public function signData($zz, $Vr)
    {
        return $zz->signData($Vr);
    }
    public function sign($zz, $Ph = NULL)
    {
        if (!($Ph != NULL)) {
            goto Ry;
        }
        $this->resetXPathObj();
        $this->appendSignature($Ph);
        $this->sigNode = $Ph->lastChild;
        Ry:
        if (!($N2 = $this->getXPathObj())) {
            goto a0;
        }
        $le = "\x2e\57\x73\x65\143\x64\x73\x69\147\x3a\123\151\x67\156\145\x64\111\156\146\157";
        $Mc = $N2->query($le, $this->sigNode);
        if (!($Gb = $Mc->item(0))) {
            goto Sv;
        }
        $le = "\56\57\x73\145\143\144\x73\x69\x67\72\123\x69\x67\x6e\141\164\165\162\145\x4d\145\x74\x68\x6f\x64";
        $Mc = $N2->query($le, $Gb);
        $X_ = $Mc->item(0);
        $X_->setAttribute("\x41\x6c\x67\157\x72\x69\x74\150\155", $zz->type);
        $Vr = $this->canonicalizeData($Gb, $this->canonicalMethod);
        $Yo = base64_encode($this->signData($zz, $Vr));
        $XR = $this->createNewSignNode("\x53\x69\x67\x6e\141\x74\x75\x72\x65\126\141\x6c\x75\x65", $Yo);
        if ($nA = $Gb->nextSibling) {
            goto ni;
        }
        $this->sigNode->appendChild($XR);
        goto qz;
        ni:
        $nA->parentNode->insertBefore($XR, $nA);
        qz:
        Sv:
        a0:
    }
    public function appendCert()
    {
    }
    public function appendKey($zz, $rQ = NULL)
    {
        $zz->serializeKey($rQ);
    }
    public function insertSignature($Il, $FP = NULL)
    {
        $AW = $Il->ownerDocument;
        $rV = $AW->importNode($this->sigNode, TRUE);
        if ($FP == NULL) {
            goto sy;
        }
        return $Il->insertBefore($rV, $FP);
        goto Xq;
        sy:
        return $Il->insertBefore($rV);
        Xq:
    }
    public function appendSignature($eX, $nm = FALSE)
    {
        $FP = $nm ? $eX->firstChild : NULL;
        return $this->insertSignature($eX, $FP);
    }
    static function get509XCert($LZ, $YQ = TRUE)
    {
        $vw = XMLSecurityDSigSAML::staticGet509XCerts($LZ, $YQ);
        if (empty($vw)) {
            goto EF;
        }
        return $vw[0];
        EF:
        return '';
    }
    static function staticGet509XCerts($vw, $YQ = TRUE)
    {
        if ($YQ) {
            goto fa;
        }
        return array($vw);
        goto x2;
        fa:
        $Vr = '';
        $Ch = array();
        $AB = explode("\xa", $vw);
        $HZ = FALSE;
        foreach ($AB as $OG) {
            if (!$HZ) {
                goto n2;
            }
            if (!(strncmp($OG, "\x2d\x2d\x2d\55\55\x45\x4e\104\x20\x43\105\x52\124\x49\x46\x49\x43\x41\124\x45", 20) == 0)) {
                goto Lg;
            }
            $HZ = FALSE;
            $Ch[] = $Vr;
            $Vr = '';
            goto s_;
            Lg:
            $Vr .= trim($OG);
            goto K6;
            n2:
            if (!(strncmp($OG, "\x2d\x2d\x2d\x2d\55\x42\105\107\111\116\x20\103\105\122\x54\x49\106\111\x43\x41\x54\x45", 22) == 0)) {
                goto No;
            }
            $HZ = TRUE;
            No:
            K6:
            s_:
        }
        Cb:
        return $Ch;
        x2:
    }
    static function staticAdd509Cert($cM, $LZ, $YQ = TRUE, $j_ = False, $N2 = NULL, $CX = NULL)
    {
        if (!$j_) {
            goto qJ;
        }
        $LZ = file_get_contents($LZ);
        qJ:
        if ($cM instanceof DOMElement) {
            goto BS;
        }
        throw new Exception("\x49\156\166\141\x6c\x69\x64\x20\x70\x61\162\145\156\x74\40\x4e\x6f\144\x65\x20\160\x61\x72\x61\155\x65\x74\145\x72");
        BS:
        $Ye = $cM->ownerDocument;
        if (!empty($N2)) {
            goto Rd;
        }
        $N2 = new DOMXPath($cM->ownerDocument);
        $N2->registerNamespace("\x73\x65\x63\144\x73\x69\x67", XMLSecurityDSigSAML::XMLDSIGNS);
        Rd:
        $le = "\x2e\x2f\x73\145\143\x64\163\x69\147\x3a\x4b\145\x79\x49\x6e\x66\x6f";
        $Mc = $N2->query($le, $cM);
        $SV = $Mc->item(0);
        if ($SV) {
            goto DP;
        }
        $FF = FALSE;
        $SV = $Ye->createElementNS(XMLSecurityDSigSAML::XMLDSIGNS, "\x64\163\72\x4b\x65\x79\x49\156\146\157");
        $le = "\x2e\57\163\x65\x63\x64\x73\x69\x67\x3a\117\142\152\145\x63\x74";
        $Mc = $N2->query($le, $cM);
        if (!($DK = $Mc->item(0))) {
            goto JM;
        }
        $DK->parentNode->insertBefore($SV, $DK);
        $FF = TRUE;
        JM:
        if ($FF) {
            goto y_;
        }
        $cM->appendChild($SV);
        y_:
        DP:
        $vw = XMLSecurityDSigSAML::staticGet509XCerts($LZ, $YQ);
        $lU = $Ye->createElementNS(XMLSecurityDSigSAML::XMLDSIGNS, "\x64\x73\x3a\130\65\x30\x39\104\141\164\x61");
        $SV->appendChild($lU);
        $KE = FALSE;
        $pP = FALSE;
        if (!is_array($CX)) {
            goto vU;
        }
        if (empty($CX["\151\x73\163\x75\x65\162\x53\145\x72\151\141\x6c"])) {
            goto Ru;
        }
        $KE = TRUE;
        Ru:
        vU:
        foreach ($vw as $cX) {
            if (!$KE) {
                goto ez;
            }
            if (!($CH = openssl_x509_parse("\55\x2d\55\55\x2d\102\105\107\111\116\40\103\105\122\124\111\x46\111\x43\x41\x54\x45\55\x2d\55\55\55\12" . chunk_split($cX, 64, "\12") . "\55\x2d\55\55\x2d\105\x4e\x44\x20\x43\x45\x52\124\x49\106\x49\x43\x41\x54\x45\x2d\55\x2d\55\55\xa"))) {
                goto bu;
            }
            if (!($KE && !empty($CH["\x69\x73\x73\165\x65\162"]) && !empty($CH["\x73\x65\162\x69\141\154\116\165\155\142\x65\x72"]))) {
                goto f3;
            }
            if (is_array($CH["\151\x73\x73\x75\145\162"])) {
                goto tG;
            }
            $Cg = $CH["\x69\163\163\x75\145\x72"];
            goto Ev;
            tG:
            $Td = array();
            foreach ($CH["\x69\x73\x73\x75\145\162"] as $TP => $UH) {
                array_unshift($Td, "{$TP}\75{$UH}" . $Ps);
                w2:
            }
            kL:
            $Cg = implode("\54", $Td);
            Ev:
            $RV = $Ye->createElementNS(XMLSecurityDSigSAML::XMLDSIGNS, "\x64\x73\72\130\x35\x30\x39\111\x73\163\165\145\x72\123\145\162\x69\x61\x6c");
            $lU->appendChild($RV);
            $XO = $Ye->createElementNS(XMLSecurityDSigSAML::XMLDSIGNS, "\144\x73\x3a\x58\x35\60\x39\111\163\x73\x75\x65\162\x4e\x61\155\145", $Cg);
            $RV->appendChild($XO);
            $XO = $Ye->createElementNS(XMLSecurityDSigSAML::XMLDSIGNS, "\x64\163\x3a\x58\x35\x30\x39\x53\x65\x72\151\x61\154\116\x75\x6d\x62\x65\162", $CH["\163\x65\x72\151\141\x6c\x4e\x75\155\x62\x65\x72"]);
            $RV->appendChild($XO);
            f3:
            bu:
            ez:
            $kk = $Ye->createElementNS(XMLSecurityDSigSAML::XMLDSIGNS, "\x64\x73\x3a\130\x35\x30\71\103\145\162\164\x69\146\151\x63\141\x74\x65", $cX);
            $lU->appendChild($kk);
            HI:
        }
        Vn:
    }
    public function add509Cert($LZ, $YQ = TRUE, $j_ = False, $CX = NULL)
    {
        if (!($N2 = $this->getXPathObj())) {
            goto kS;
        }
        self::staticAdd509Cert($this->sigNode, $LZ, $YQ, $j_, $N2, $CX);
        kS:
    }
    public function getValidatedNodes()
    {
        return $this->validatedNodes;
    }
}
class XMLSecEncSAML
{
    const template = "\x3c\x78\145\x6e\143\x3a\105\156\x63\162\171\x70\164\145\144\104\x61\x74\141\x20\170\x6d\x6c\156\163\x3a\170\145\x6e\x63\75\x27\x68\x74\164\160\72\x2f\x2f\167\167\x77\56\167\63\x2e\157\162\x67\57\x32\x30\60\61\x2f\60\64\x2f\170\155\154\x65\x6e\143\43\47\76\xd\xa\x20\40\40\x3c\170\145\156\143\72\103\x69\160\150\x65\x72\x44\x61\x74\x61\76\15\12\x20\x20\40\40\40\x20\x3c\x78\145\x6e\143\72\103\151\160\x68\145\x72\x56\141\x6c\165\x65\76\x3c\x2f\x78\x65\x6e\x63\x3a\103\151\160\150\145\162\x56\x61\154\x75\x65\x3e\xd\xa\40\x20\x20\x3c\x2f\x78\x65\156\143\72\x43\151\x70\150\x65\162\104\141\164\x61\x3e\15\12\74\x2f\x78\x65\156\143\72\x45\156\x63\x72\171\x70\x74\145\x64\x44\x61\x74\x61\76";
    const Element = "\x68\x74\x74\160\72\x2f\x2f\167\167\x77\x2e\x77\x33\56\157\x72\x67\57\x32\60\60\x31\57\x30\x34\57\x78\x6d\154\x65\x6e\x63\x23\105\x6c\x65\x6d\145\156\x74";
    const Content = "\150\164\164\160\x3a\57\x2f\x77\167\x77\x2e\167\63\x2e\157\162\147\x2f\62\x30\x30\61\x2f\x30\x34\57\170\155\154\x65\156\x63\43\x43\x6f\156\x74\145\156\x74";
    const URI = 3;
    const XMLENCNS = "\150\x74\x74\160\x3a\57\57\x77\x77\x77\x2e\167\x33\x2e\x6f\162\x67\x2f\x32\x30\x30\x31\57\x30\x34\57\170\x6d\x6c\x65\x6e\143\x23";
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
    public function addReference($JN, $Il, $Zc)
    {
        if ($Il instanceof DOMNode) {
            goto k8;
        }
        throw new Exception("\x24\x6e\157\144\145\40\151\163\x20\156\x6f\x74\x20\x6f\146\x20\164\171\x70\x65\x20\x44\x4f\x4d\116\x6f\144\x65");
        k8:
        $l3 = $this->encdoc;
        $this->_resetTemplate();
        $KY = $this->encdoc;
        $this->encdoc = $l3;
        $EU = XMLSecurityDSigSAML::generate_GUID();
        $O9 = $KY->documentElement;
        $O9->setAttribute("\x49\144", $EU);
        $this->references[$JN] = array("\x6e\x6f\x64\x65" => $Il, "\164\x79\160\x65" => $Zc, "\145\156\143\156\157\x64\x65" => $KY, "\162\145\x66\165\162\x69" => $EU);
    }
    public function setNode($Il)
    {
        $this->rawNode = $Il;
    }
    public function encryptNode($zz, $uE = TRUE)
    {
        $Vr = '';
        if (!empty($this->rawNode)) {
            goto B6;
        }
        throw new Exception("\x4e\157\144\x65\x20\164\x6f\40\145\x6e\x63\x72\171\x70\x74\x20\x68\x61\x73\x20\156\x6f\x74\40\x62\145\145\156\40\x73\145\x74");
        B6:
        if ($zz instanceof XMLSecurityKeySAML) {
            goto NJ;
        }
        throw new Exception("\x49\156\x76\x61\x6c\x69\144\x20\x4b\x65\171");
        NJ:
        $qW = $this->rawNode->ownerDocument;
        $PY = new DOMXPath($this->encdoc);
        $dO = $PY->query("\57\x78\145\156\x63\72\x45\x6e\143\162\x79\160\164\x65\144\104\x61\164\x61\x2f\170\145\x6e\x63\72\103\x69\160\x68\145\162\x44\x61\164\141\57\170\145\x6e\143\x3a\x43\151\160\x68\145\162\126\141\154\x75\145");
        $TB = $dO->item(0);
        if (!($TB == NULL)) {
            goto vn;
        }
        throw new Exception("\105\162\162\x6f\162\x20\154\157\143\141\164\151\x6e\147\40\103\151\x70\150\145\x72\126\x61\154\x75\x65\x20\x65\x6c\145\x6d\145\x6e\164\40\x77\x69\x74\x68\x69\x6e\x20\x74\145\155\x70\154\141\x74\x65");
        vn:
        switch ($this->type) {
            case XMLSecEncSAML::Element:
                $Vr = $qW->saveXML($this->rawNode);
                $this->encdoc->documentElement->setAttribute("\x54\x79\160\145", XMLSecEncSAML::Element);
                goto XP;
            case XMLSecEncSAML::Content:
                $So = $this->rawNode->childNodes;
                foreach ($So as $Vz) {
                    $Vr .= $qW->saveXML($Vz);
                    Dh:
                }
                Qb:
                $this->encdoc->documentElement->setAttribute("\124\x79\x70\x65", XMLSecEncSAML::Content);
                goto XP;
            default:
                throw new Exception("\124\x79\160\145\x20\151\x73\x20\143\x75\x72\162\x65\x6e\164\x6c\171\40\x6e\x6f\x74\x20\163\165\160\160\x6f\162\164\145\x64");
                return;
        }
        Y1:
        XP:
        $EL = $this->encdoc->documentElement->appendChild($this->encdoc->createElementNS(XMLSecEncSAML::XMLENCNS, "\x78\x65\156\143\72\x45\x6e\x63\162\x79\x70\164\x69\157\x6e\115\x65\164\x68\x6f\x64"));
        $EL->setAttribute("\101\x6c\147\x6f\162\151\x74\x68\155", $zz->getAlgorith());
        $TB->parentNode->parentNode->insertBefore($EL, $TB->parentNode->parentNode->firstChild);
        $Ca = base64_encode($zz->encryptData($Vr));
        $UH = $this->encdoc->createTextNode($Ca);
        $TB->appendChild($UH);
        if ($uE) {
            goto MJ;
        }
        return $this->encdoc->documentElement;
        goto v_;
        MJ:
        switch ($this->type) {
            case XMLSecEncSAML::Element:
                if (!($this->rawNode->nodeType == XML_DOCUMENT_NODE)) {
                    goto Qa;
                }
                return $this->encdoc;
                Qa:
                $zd = $this->rawNode->ownerDocument->importNode($this->encdoc->documentElement, TRUE);
                $this->rawNode->parentNode->replaceChild($zd, $this->rawNode);
                return $zd;
                goto Gp;
            case XMLSecEncSAML::Content:
                $zd = $this->rawNode->ownerDocument->importNode($this->encdoc->documentElement, TRUE);
                nk:
                if (!$this->rawNode->firstChild) {
                    goto YC;
                }
                $this->rawNode->removeChild($this->rawNode->firstChild);
                goto nk;
                YC:
                $this->rawNode->appendChild($zd);
                return $zd;
                goto Gp;
        }
        bM:
        Gp:
        v_:
    }
    public function encryptReferences($zz)
    {
        $cx = $this->rawNode;
        $Fy = $this->type;
        foreach ($this->references as $JN => $P1) {
            $this->encdoc = $P1["\x65\x6e\x63\x6e\x6f\144\145"];
            $this->rawNode = $P1["\156\157\144\x65"];
            $this->type = $P1["\164\171\160\x65"];
            try {
                $xV = $this->encryptNode($zz);
                $this->references[$JN]["\x65\x6e\x63\x6e\157\144\145"] = $xV;
            } catch (Exception $jf) {
                $this->rawNode = $cx;
                $this->type = $Fy;
                throw $jf;
            }
            bL:
        }
        Tp:
        $this->rawNode = $cx;
        $this->type = $Fy;
    }
    public function getCipherValue()
    {
        if (!empty($this->rawNode)) {
            goto oD;
        }
        throw new Exception("\x4e\x6f\x64\x65\x20\x74\157\40\144\145\143\162\171\160\164\x20\x68\141\163\40\156\157\x74\40\142\145\145\x6e\40\163\x65\x74");
        oD:
        $qW = $this->rawNode->ownerDocument;
        $PY = new DOMXPath($qW);
        $PY->registerNamespace("\x78\x6d\x6c\x65\x6e\x63\162", XMLSecEncSAML::XMLENCNS);
        $le = "\56\x2f\x78\x6d\154\145\x6e\143\x72\x3a\103\x69\160\x68\x65\162\x44\x61\164\x61\x2f\x78\x6d\154\x65\156\143\162\72\103\x69\x70\x68\145\x72\x56\141\x6c\x75\x65";
        $Mc = $PY->query($le, $this->rawNode);
        $Il = $Mc->item(0);
        if ($Il) {
            goto m3;
        }
        return NULL;
        m3:
        return base64_decode($Il->nodeValue);
    }
    public function decryptNode($zz, $uE = TRUE)
    {
        if ($zz instanceof XMLSecurityKeySAML) {
            goto a2;
        }
        throw new Exception("\111\156\x76\141\154\151\x64\x20\x4b\145\171");
        a2:
        $yK = $this->getCipherValue();
        if ($yK) {
            goto Gn;
        }
        throw new Exception("\x43\141\x6e\x6e\x6f\164\x20\x6c\x6f\x63\x61\x74\x65\x20\x65\156\143\162\x79\160\164\145\x64\40\x64\x61\x74\141");
        goto Dk;
        Gn:
        $bB = $zz->decryptData($yK);
        if ($uE) {
            goto rk;
        }
        return $bB;
        goto f1;
        rk:
        switch ($this->type) {
            case XMLSecEncSAML::Element:
                $K3 = new DOMDocument();
                $K3->loadXML($bB);
                if (!($this->rawNode->nodeType == XML_DOCUMENT_NODE)) {
                    goto Mb;
                }
                return $K3;
                Mb:
                $zd = $this->rawNode->ownerDocument->importNode($K3->documentElement, TRUE);
                $this->rawNode->parentNode->replaceChild($zd, $this->rawNode);
                return $zd;
                goto kM;
            case XMLSecEncSAML::Content:
                if ($this->rawNode->nodeType == XML_DOCUMENT_NODE) {
                    goto u1;
                }
                $qW = $this->rawNode->ownerDocument;
                goto NT;
                u1:
                $qW = $this->rawNode;
                NT:
                $r9 = $qW->createDocumentFragment();
                $r9->appendXML($bB);
                $rQ = $this->rawNode->parentNode;
                $rQ->replaceChild($r9, $this->rawNode);
                return $rQ;
                goto kM;
            default:
                return $bB;
        }
        Hs:
        kM:
        f1:
        Dk:
    }
    public function encryptKey($S8, $WC, $X7 = TRUE)
    {
        if (!(!$S8 instanceof XMLSecurityKeySAML || !$WC instanceof XMLSecurityKeySAML)) {
            goto Bg;
        }
        throw new Exception("\x49\x6e\x76\141\x6c\x69\x64\40\x4b\145\171");
        Bg:
        $Yc = base64_encode($S8->encryptData($WC->key));
        $bb = $this->encdoc->documentElement;
        $w0 = $this->encdoc->createElementNS(XMLSecEncSAML::XMLENCNS, "\x78\145\x6e\x63\x3a\105\156\x63\162\x79\160\164\x65\144\x4b\145\x79");
        if ($X7) {
            goto wU;
        }
        $this->encKey = $w0;
        goto iS;
        wU:
        $SV = $bb->insertBefore($this->encdoc->createElementNS("\150\x74\164\x70\72\x2f\x2f\x77\167\167\56\x77\x33\56\x6f\x72\x67\57\62\60\x30\x30\x2f\60\x39\x2f\x78\x6d\x6c\x64\x73\151\147\43", "\x64\x73\151\x67\x3a\x4b\x65\x79\111\x6e\146\x6f"), $bb->firstChild);
        $SV->appendChild($w0);
        iS:
        $EL = $w0->appendChild($this->encdoc->createElementNS(XMLSecEncSAML::XMLENCNS, "\x78\x65\x6e\x63\72\x45\x6e\x63\162\x79\x70\x74\x69\157\156\x4d\145\x74\x68\x6f\x64"));
        $EL->setAttribute("\x41\x6c\x67\157\162\x69\x74\x68\155", $S8->getAlgorith());
        if (empty($S8->name)) {
            goto X7;
        }
        $SV = $w0->appendChild($this->encdoc->createElementNS("\x68\164\164\160\x3a\x2f\57\x77\x77\x77\56\167\x33\56\x6f\x72\x67\x2f\x32\60\x30\x30\x2f\60\71\57\x78\155\154\x64\x73\151\147\43", "\x64\163\x69\x67\x3a\x4b\145\x79\111\156\x66\x6f"));
        $SV->appendChild($this->encdoc->createElementNS("\150\164\x74\x70\x3a\57\x2f\x77\167\167\x2e\x77\x33\56\157\x72\x67\57\62\60\60\x30\57\x30\x39\x2f\170\155\154\x64\x73\151\147\43", "\x64\163\x69\147\x3a\x4b\145\171\116\x61\155\145", $S8->name));
        X7:
        $LK = $w0->appendChild($this->encdoc->createElementNS(XMLSecEncSAML::XMLENCNS, "\x78\x65\156\143\72\x43\151\160\150\x65\x72\104\x61\164\141"));
        $LK->appendChild($this->encdoc->createElementNS(XMLSecEncSAML::XMLENCNS, "\170\x65\x6e\143\72\103\x69\x70\150\145\x72\x56\x61\x6c\165\x65", $Yc));
        if (!(is_array($this->references) && count($this->references) > 0)) {
            goto ob;
        }
        $tG = $w0->appendChild($this->encdoc->createElementNS(XMLSecEncSAML::XMLENCNS, "\170\x65\156\x63\x3a\x52\145\146\145\x72\x65\156\143\x65\x4c\x69\x73\164"));
        foreach ($this->references as $JN => $P1) {
            $EU = $P1["\162\145\x66\165\x72\x69"];
            $KV = $tG->appendChild($this->encdoc->createElementNS(XMLSecEncSAML::XMLENCNS, "\x78\145\x6e\143\x3a\x44\x61\164\141\x52\x65\x66\x65\x72\x65\156\143\x65"));
            $KV->setAttribute("\125\122\111", "\x23" . $EU);
            fI:
        }
        m5:
        ob:
        return;
    }
    public function decryptKey($w0)
    {
        if ($w0->isEncrypted) {
            goto CS;
        }
        throw new Exception("\x4b\145\171\x20\151\163\40\156\x6f\x74\40\105\x6e\x63\162\171\160\164\x65\x64");
        CS:
        if (!empty($w0->key)) {
            goto Ey;
        }
        throw new Exception("\x4b\x65\x79\40\x69\x73\40\x6d\151\x73\163\151\x6e\x67\40\x64\141\x74\141\40\x74\x6f\x20\160\x65\x72\x66\157\x72\155\x20\164\x68\x65\40\x64\145\x63\x72\x79\x70\x74\x69\157\x6e");
        Ey:
        return $this->decryptNode($w0, FALSE);
    }
    public function locateEncryptedData($O9)
    {
        if ($O9 instanceof DOMDocument) {
            goto eB;
        }
        $qW = $O9->ownerDocument;
        goto ZX;
        eB:
        $qW = $O9;
        ZX:
        if (!$qW) {
            goto ir;
        }
        $N2 = new DOMXPath($qW);
        $le = "\57\57\x2a\x5b\154\x6f\143\x61\x6c\x2d\x6e\x61\x6d\x65\50\x29\75\x27\105\156\143\162\x79\160\164\x65\x64\104\141\164\141\47\40\x61\156\x64\40\156\141\155\x65\x73\160\141\x63\x65\55\x75\x72\x69\50\51\75\47" . XMLSecEncSAML::XMLENCNS . "\x27\x5d";
        $Mc = $N2->query($le);
        return $Mc->item(0);
        ir:
        return NULL;
    }
    public function locateKey($Il = NULL)
    {
        if (!empty($Il)) {
            goto qF;
        }
        $Il = $this->rawNode;
        qF:
        if ($Il instanceof DOMNode) {
            goto AV;
        }
        return NULL;
        AV:
        if (!($qW = $Il->ownerDocument)) {
            goto VE;
        }
        $N2 = new DOMXPath($qW);
        $N2->registerNamespace("\x58\115\114\x53\145\143\x45\x6e\143\123\101\115\114", XMLSecEncSAML::XMLENCNS);
        $le = "\x2e\x2f\x2f\x58\x4d\114\x53\x65\x63\x45\x6e\143\123\101\115\x4c\x3a\105\x6e\x63\162\x79\160\x74\151\157\x6e\115\x65\164\x68\157\x64";
        $Mc = $N2->query($le, $Il);
        if (!($tJ = $Mc->item(0))) {
            goto yi;
        }
        $mw = $tJ->getAttribute("\101\154\x67\157\x72\151\x74\x68\155");
        try {
            $zz = new XMLSecurityKeySAML($mw, array("\164\171\x70\x65" => "\160\162\151\166\141\164\145"));
        } catch (Exception $jf) {
            return NULL;
        }
        return $zz;
        yi:
        VE:
        return NULL;
    }
    static function staticLocateKeyInfo($qh = NULL, $Il = NULL)
    {
        if (!(empty($Il) || !$Il instanceof DOMNode)) {
            goto Hv;
        }
        return NULL;
        Hv:
        $qW = $Il->ownerDocument;
        if ($qW) {
            goto sw;
        }
        return NULL;
        sw:
        $N2 = new DOMXPath($qW);
        $N2->registerNamespace("\130\115\x4c\x53\145\x63\x45\x6e\143\123\x41\115\114", XMLSecEncSAML::XMLENCNS);
        $N2->registerNamespace("\x78\x6d\154\x73\145\143\x64\x73\151\x67", XMLSecurityDSigSAML::XMLDSIGNS);
        $le = "\56\x2f\x78\155\154\163\145\143\144\163\x69\x67\x3a\x4b\145\171\111\156\x66\x6f";
        $Mc = $N2->query($le, $Il);
        $tJ = $Mc->item(0);
        if ($tJ) {
            goto bd;
        }
        return $qh;
        bd:
        foreach ($tJ->childNodes as $Vz) {
            switch ($Vz->localName) {
                case "\113\145\x79\116\141\x6d\145":
                    if (empty($qh)) {
                        goto L0;
                    }
                    $qh->name = $Vz->nodeValue;
                    L0:
                    goto Jg;
                case "\113\145\171\x56\x61\154\x75\x65":
                    foreach ($Vz->childNodes as $HF) {
                        switch ($HF->localName) {
                            case "\104\123\101\113\x65\171\x56\141\154\x75\145":
                                throw new Exception("\104\123\x41\113\145\171\126\x61\154\165\x65\40\x63\x75\x72\162\145\156\164\x6c\171\40\x6e\x6f\164\x20\x73\165\x70\x70\157\x72\x74\145\x64");
                                goto Ik;
                            case "\122\x53\101\x4b\x65\x79\x56\x61\154\x75\145":
                                $gZ = NULL;
                                $pe = NULL;
                                if (!($Iu = $HF->getElementsByTagName("\115\157\x64\165\x6c\165\x73")->item(0))) {
                                    goto UG;
                                }
                                $gZ = base64_decode($Iu->nodeValue);
                                UG:
                                if (!($mq = $HF->getElementsByTagName("\105\x78\160\157\x6e\145\x6e\x74")->item(0))) {
                                    goto FB;
                                }
                                $pe = base64_decode($mq->nodeValue);
                                FB:
                                if (!(empty($gZ) || empty($pe))) {
                                    goto ww;
                                }
                                throw new Exception("\115\x69\163\x73\x69\x6e\x67\40\115\x6f\x64\165\154\x75\x73\40\x6f\x72\40\x45\x78\x70\x6f\156\145\156\164");
                                ww:
                                $Gf = XMLSecurityKeySAML::convertRSA($gZ, $pe);
                                $qh->loadKey($Gf);
                                goto Ik;
                        }
                        q_:
                        Ik:
                        sq:
                    }
                    u6:
                    goto Jg;
                case "\x52\x65\x74\162\151\145\x76\x61\154\115\145\164\150\x6f\144":
                    $Zc = $Vz->getAttribute("\x54\171\x70\145");
                    if (!($Zc !== "\150\x74\x74\160\72\57\x2f\167\167\x77\56\167\63\56\x6f\x72\147\x2f\x32\60\x30\x31\x2f\x30\64\x2f\x78\x6d\x6c\145\156\143\43\105\x6e\143\162\171\160\164\145\x64\113\x65\171")) {
                        goto Hk;
                    }
                    goto Jg;
                    Hk:
                    $O3 = $Vz->getAttribute("\x55\122\111");
                    if (!($O3[0] !== "\43")) {
                        goto nc;
                    }
                    goto Jg;
                    nc:
                    $Nv = substr($O3, 1);
                    $le = "\57\x2f\x58\x4d\x4c\x53\x65\143\x45\x6e\x63\x53\101\x4d\x4c\72\x45\156\143\162\171\x70\164\x65\144\x4b\145\x79\x5b\x40\111\144\75\47{$Nv}\x27\x5d";
                    $TD = $N2->query($le)->item(0);
                    if ($TD) {
                        goto NX;
                    }
                    throw new Exception("\x55\156\141\x62\154\x65\x20\164\157\40\x6c\x6f\143\x61\164\145\x20\105\156\143\x72\171\x70\x74\145\x64\x4b\145\171\x20\x77\x69\x74\150\40\100\111\x64\x3d\x27{$Nv}\x27\56");
                    NX:
                    return XMLSecurityKeySAML::fromEncryptedKeyElement($TD);
                case "\x45\156\x63\162\x79\160\164\x65\x64\113\x65\x79":
                    return XMLSecurityKeySAML::fromEncryptedKeyElement($Vz);
                case "\x58\x35\x30\x39\104\141\x74\x61":
                    if (!($Xv = $Vz->getElementsByTagName("\x58\65\x30\x39\103\x65\x72\164\151\146\x69\x63\x61\x74\x65"))) {
                        goto qd;
                    }
                    if (!($Xv->length > 0)) {
                        goto HY;
                    }
                    $Xi = $Xv->item(0)->textContent;
                    $Xi = str_replace(array("\15", "\12"), '', $Xi);
                    $Xi = "\55\55\55\x2d\55\x42\x45\107\111\116\x20\x43\x45\x52\124\111\106\x49\x43\x41\124\105\55\55\55\55\55\12" . chunk_split($Xi, 64, "\12") . "\55\55\55\55\x2d\105\116\x44\40\x43\x45\122\124\111\x46\x49\x43\101\124\x45\55\x2d\x2d\x2d\55\xa";
                    $qh->loadKey($Xi, FALSE, TRUE);
                    HY:
                    qd:
                    goto Jg;
            }
            rH:
            Jg:
            fM:
        }
        Nv:
        return $qh;
    }
    public function locateKeyInfo($qh = NULL, $Il = NULL)
    {
        if (!empty($Il)) {
            goto ae;
        }
        $Il = $this->rawNode;
        ae:
        return XMLSecEncSAML::staticLocateKeyInfo($qh, $Il);
    }
}
