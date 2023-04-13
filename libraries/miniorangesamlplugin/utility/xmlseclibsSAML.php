<?php


class XMLSecurityKeySAML
{
    const TRIPLEDES_CBC = "\x68\x74\164\x70\72\57\x2f\x77\167\167\x2e\167\x33\56\x6f\x72\147\57\x32\x30\x30\61\x2f\x30\x34\57\x78\155\154\145\156\143\x23\x74\162\x69\160\154\145\x64\x65\x73\55\x63\x62\x63";
    const AES128_CBC = "\x68\x74\x74\160\72\57\57\167\167\167\56\x77\63\56\x6f\x72\147\x2f\62\x30\60\61\x2f\60\64\x2f\x78\155\x6c\145\x6e\x63\x23\x61\x65\163\x31\62\x38\x2d\x63\142\143";
    const AES192_CBC = "\x68\164\x74\x70\72\x2f\57\167\x77\167\x2e\167\63\56\x6f\162\x67\x2f\62\60\60\61\57\60\64\57\x78\155\154\145\x6e\143\x23\x61\145\x73\61\x39\62\x2d\x63\142\x63";
    const AES256_CBC = "\x68\164\x74\x70\x3a\x2f\57\x77\167\167\56\x77\63\x2e\x6f\x72\147\x2f\x32\60\60\61\x2f\60\64\x2f\170\x6d\154\145\156\x63\x23\x61\145\x73\62\x35\x36\x2d\x63\142\143";
    const RSA_1_5 = "\150\x74\x74\x70\72\57\x2f\x77\167\167\x2e\167\x33\56\x6f\x72\147\x2f\62\x30\60\61\x2f\x30\64\57\170\155\154\x65\156\x63\43\162\163\x61\55\x31\x5f\x35";
    const RSA_OAEP_MGF1P = "\150\x74\x74\160\x3a\57\x2f\x77\167\167\56\x77\x33\x2e\157\162\x67\57\62\60\x30\61\x2f\x30\64\57\170\155\154\145\156\x63\43\162\x73\141\55\157\x61\x65\160\55\155\x67\146\61\160";
    const DSA_SHA1 = "\x68\x74\x74\x70\72\57\x2f\167\x77\x77\x2e\x77\x33\x2e\157\162\x67\57\x32\x30\x30\x30\57\x30\x39\x2f\x78\x6d\x6c\144\163\x69\x67\43\x64\163\141\55\163\x68\x61\x31";
    const RSA_SHA1 = "\x68\x74\x74\160\72\x2f\x2f\x77\x77\167\56\x77\63\56\157\x72\x67\57\x32\60\x30\x30\x2f\60\71\57\170\x6d\154\144\x73\x69\x67\x23\162\x73\141\x2d\x73\150\141\x31";
    const RSA_SHA256 = "\150\164\x74\160\x3a\57\57\x77\x77\x77\x2e\167\63\56\157\162\x67\x2f\62\x30\60\x31\x2f\x30\x34\x2f\x78\x6d\x6c\144\x73\151\147\55\x6d\157\162\x65\43\x72\163\x61\55\x73\x68\x61\x32\x35\66";
    const RSA_SHA384 = "\150\x74\x74\160\72\57\57\x77\167\x77\56\x77\x33\x2e\157\162\x67\57\62\60\60\61\x2f\x30\x34\57\x78\155\154\x64\x73\151\147\55\x6d\157\x72\x65\43\162\163\141\x2d\163\x68\x61\x33\x38\x34";
    const RSA_SHA512 = "\x68\164\x74\x70\x3a\57\57\167\x77\x77\56\167\63\x2e\157\x72\x67\57\62\60\x30\61\x2f\x30\64\x2f\170\x6d\x6c\x64\163\x69\147\x2d\x6d\x6f\162\145\x23\162\x73\141\55\x73\150\141\x35\x31\62";
    const HMAC_SHA1 = "\150\164\164\160\72\57\x2f\167\x77\x77\x2e\x77\x33\56\157\x72\x67\57\x32\60\x30\60\x2f\60\71\57\x78\155\x6c\x64\163\x69\x67\x23\150\x6d\141\143\55\x73\150\141\61";
    private $cryptParams = array();
    public $type = 0;
    public $key = null;
    public $passphrase = '';
    public $iv = null;
    public $name = null;
    public $keyChain = null;
    public $isEncrypted = false;
    public $encryptedCtx = null;
    public $guid = null;
    private $x509Certificate = null;
    private $X509Thumbprint = null;
    public function __construct($gv, $ZU = null)
    {
        switch ($gv) {
            case self::TRIPLEDES_CBC:
                $this->cryptParams["\154\151\142\x72\x61\162\x79"] = "\x6f\x70\145\x6e\x73\x73\154";
                $this->cryptParams["\x63\151\160\150\145\162"] = "\144\145\163\x2d\145\x64\x65\x33\55\x63\142\143";
                $this->cryptParams["\x74\171\160\145"] = "\x73\x79\155\155\145\164\x72\151\143";
                $this->cryptParams["\155\145\164\x68\x6f\144"] = "\150\164\164\160\x3a\x2f\x2f\x77\x77\167\x2e\x77\63\56\x6f\162\x67\x2f\62\x30\x30\x31\57\x30\64\x2f\170\x6d\x6c\x65\x6e\x63\43\164\162\x69\160\154\145\x64\145\163\55\x63\x62\143";
                $this->cryptParams["\x6b\x65\171\x73\151\172\145"] = 24;
                $this->cryptParams["\142\x6c\x6f\x63\153\163\151\x7a\x65"] = 8;
                goto qv;
            case self::AES128_CBC:
                $this->cryptParams["\x6c\x69\x62\x72\141\162\171"] = "\x6f\160\145\156\163\x73\x6c";
                $this->cryptParams["\x63\x69\160\x68\x65\x72"] = "\x61\145\163\55\61\62\x38\x2d\x63\x62\x63";
                $this->cryptParams["\x74\171\160\x65"] = "\163\x79\x6d\155\145\x74\162\x69\143";
                $this->cryptParams["\155\x65\x74\150\157\144"] = "\150\x74\x74\x70\x3a\x2f\x2f\167\167\x77\x2e\x77\x33\56\x6f\162\x67\x2f\62\60\60\61\57\x30\x34\57\170\155\154\x65\156\x63\x23\141\x65\x73\61\x32\x38\x2d\143\x62\143";
                $this->cryptParams["\x6b\145\171\163\x69\172\x65"] = 16;
                $this->cryptParams["\x62\x6c\x6f\143\x6b\163\151\172\145"] = 16;
                goto qv;
            case self::AES192_CBC:
                $this->cryptParams["\154\x69\142\162\141\x72\171"] = "\x6f\160\145\x6e\163\163\x6c";
                $this->cryptParams["\x63\151\x70\150\145\162"] = "\141\x65\163\55\x31\71\x32\55\143\142\143";
                $this->cryptParams["\x74\x79\x70\x65"] = "\x73\171\155\x6d\145\164\x72\x69\143";
                $this->cryptParams["\155\145\x74\x68\x6f\x64"] = "\x68\x74\x74\160\x3a\57\57\167\167\167\56\x77\x33\x2e\157\162\147\x2f\x32\x30\60\x31\x2f\x30\x34\x2f\x78\x6d\154\145\x6e\143\43\x61\x65\163\x31\71\x32\x2d\x63\142\x63";
                $this->cryptParams["\x6b\x65\171\163\x69\172\x65"] = 24;
                $this->cryptParams["\142\x6c\x6f\x63\x6b\x73\x69\172\145"] = 16;
                goto qv;
            case self::AES256_CBC:
                $this->cryptParams["\154\x69\x62\162\141\x72\171"] = "\157\160\145\156\163\163\x6c";
                $this->cryptParams["\143\151\160\150\x65\162"] = "\x61\x65\163\55\62\x35\x36\55\x63\x62\143";
                $this->cryptParams["\x74\171\160\145"] = "\163\x79\155\155\145\164\x72\151\143";
                $this->cryptParams["\155\x65\x74\x68\157\x64"] = "\x68\x74\164\160\x3a\57\57\167\x77\167\x2e\167\x33\x2e\x6f\162\x67\x2f\x32\x30\60\x31\x2f\x30\64\x2f\x78\155\154\x65\156\x63\43\x61\x65\x73\x32\65\66\x2d\143\x62\x63";
                $this->cryptParams["\x6b\145\x79\163\x69\x7a\145"] = 32;
                $this->cryptParams["\x62\154\x6f\x63\153\x73\x69\x7a\x65"] = 16;
                goto qv;
            case self::RSA_1_5:
                $this->cryptParams["\x6c\x69\x62\x72\x61\x72\171"] = "\x6f\x70\x65\x6e\x73\163\x6c";
                $this->cryptParams["\x70\141\144\144\x69\156\147"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\155\x65\164\150\157\144"] = "\x68\164\x74\160\72\x2f\x2f\x77\167\167\56\x77\63\56\x6f\x72\147\57\x32\60\x30\x31\x2f\60\x34\x2f\170\155\x6c\x65\156\143\43\x72\x73\141\55\x31\x5f\65";
                if (!(is_array($ZU) && !empty($ZU["\164\x79\160\x65"]))) {
                    goto cr;
                }
                if (!($ZU["\164\171\x70\145"] == "\160\165\142\154\x69\x63" || $ZU["\164\171\x70\145"] == "\160\162\151\x76\x61\164\145")) {
                    goto An;
                }
                $this->cryptParams["\x74\x79\160\145"] = $ZU["\164\x79\160\x65"];
                goto qv;
                An:
                cr:
                throw new Exception("\x43\145\162\x74\151\x66\x69\143\141\164\x65\x20\42\x74\171\x70\x65\x22\40\x28\160\x72\151\x76\x61\x74\x65\x2f\160\x75\x62\154\x69\143\51\40\155\x75\163\164\40\142\x65\x20\160\x61\163\163\145\144\40\166\151\141\40\x70\x61\x72\141\155\x65\x74\145\x72\163");
            case self::RSA_OAEP_MGF1P:
                $this->cryptParams["\x6c\151\x62\x72\x61\x72\171"] = "\x6f\x70\x65\156\163\163\154";
                $this->cryptParams["\x70\x61\x64\x64\151\x6e\x67"] = OPENSSL_PKCS1_OAEP_PADDING;
                $this->cryptParams["\x6d\145\x74\150\157\144"] = "\150\164\x74\x70\x3a\57\57\x77\167\x77\56\167\63\56\x6f\162\x67\x2f\62\x30\x30\61\57\x30\64\x2f\170\155\x6c\x65\x6e\143\43\162\163\141\x2d\x6f\x61\145\x70\55\155\x67\x66\x31\160";
                $this->cryptParams["\150\x61\x73\150"] = null;
                if (!(is_array($ZU) && !empty($ZU["\x74\171\160\145"]))) {
                    goto xU;
                }
                if (!($ZU["\x74\x79\160\145"] == "\x70\x75\142\x6c\151\x63" || $ZU["\164\171\160\145"] == "\x70\162\x69\166\x61\164\x65")) {
                    goto QW;
                }
                $this->cryptParams["\164\x79\x70\x65"] = $ZU["\164\171\160\x65"];
                goto qv;
                QW:
                xU:
                throw new Exception("\103\145\162\x74\151\x66\x69\x63\x61\x74\145\x20\x22\164\x79\160\x65\x22\40\50\x70\x72\151\166\x61\x74\x65\57\x70\x75\142\x6c\x69\x63\51\x20\x6d\165\x73\x74\x20\x62\145\x20\160\141\x73\x73\x65\144\x20\x76\x69\141\x20\160\141\162\141\155\145\164\145\x72\163");
            case self::RSA_SHA1:
                $this->cryptParams["\154\x69\x62\x72\x61\162\x79"] = "\157\x70\145\156\163\163\x6c";
                $this->cryptParams["\155\x65\x74\x68\x6f\144"] = "\x68\164\164\160\x3a\57\x2f\x77\x77\x77\56\x77\63\x2e\x6f\162\147\x2f\x32\60\60\x30\57\x30\x39\57\x78\155\x6c\144\x73\x69\x67\x23\x72\163\141\55\163\x68\x61\61";
                $this->cryptParams["\160\141\x64\144\151\x6e\147"] = OPENSSL_PKCS1_PADDING;
                if (!(is_array($ZU) && !empty($ZU["\x74\171\160\x65"]))) {
                    goto GM;
                }
                if (!($ZU["\x74\x79\160\x65"] == "\160\165\x62\154\x69\x63" || $ZU["\x74\x79\x70\x65"] == "\x70\162\x69\x76\141\164\145")) {
                    goto mJ;
                }
                $this->cryptParams["\x74\x79\x70\x65"] = $ZU["\164\x79\160\x65"];
                goto qv;
                mJ:
                GM:
                throw new Exception("\x43\x65\x72\x74\x69\x66\x69\x63\141\x74\x65\40\42\x74\171\160\145\42\40\x28\160\162\151\166\x61\x74\145\x2f\160\165\x62\154\151\143\51\x20\155\165\x73\x74\x20\x62\x65\40\x70\141\x73\163\145\144\40\166\x69\x61\x20\160\x61\162\141\x6d\145\x74\x65\162\163");
            case self::RSA_SHA256:
                $this->cryptParams["\154\151\x62\x72\x61\x72\x79"] = "\157\x70\x65\x6e\x73\x73\x6c";
                $this->cryptParams["\155\x65\x74\x68\157\x64"] = "\x68\164\164\160\x3a\x2f\x2f\167\x77\167\x2e\167\x33\x2e\x6f\162\x67\57\x32\x30\60\61\57\60\x34\x2f\170\x6d\x6c\144\163\x69\147\x2d\155\157\162\x65\x23\x72\x73\141\55\x73\x68\141\x32\x35\x36";
                $this->cryptParams["\x70\x61\x64\x64\151\156\x67"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\144\151\147\x65\x73\x74"] = "\123\110\x41\62\x35\x36";
                if (!(is_array($ZU) && !empty($ZU["\x74\171\x70\x65"]))) {
                    goto nC;
                }
                if (!($ZU["\x74\x79\160\x65"] == "\160\165\x62\x6c\151\x63" || $ZU["\x74\x79\160\145"] == "\160\x72\x69\166\141\x74\x65")) {
                    goto ex;
                }
                $this->cryptParams["\164\x79\x70\x65"] = $ZU["\164\x79\160\x65"];
                goto qv;
                ex:
                nC:
                throw new Exception("\103\x65\x72\164\151\146\x69\x63\141\x74\x65\x20\42\164\x79\160\x65\x22\x20\50\160\162\151\166\141\164\145\57\x70\x75\x62\x6c\151\x63\x29\40\x6d\165\163\164\x20\x62\x65\40\160\x61\163\x73\145\144\40\x76\151\x61\x20\160\141\x72\141\x6d\x65\x74\x65\162\163");
            case self::RSA_SHA384:
                $this->cryptParams["\154\x69\142\x72\141\x72\171"] = "\x6f\160\x65\156\x73\x73\x6c";
                $this->cryptParams["\155\x65\x74\x68\157\x64"] = "\x68\x74\164\x70\72\57\x2f\167\167\167\x2e\x77\63\56\157\162\147\x2f\x32\x30\60\x31\57\x30\64\x2f\x78\155\154\x64\x73\x69\x67\55\x6d\157\162\x65\x23\x72\x73\x61\55\163\150\x61\x33\x38\x34";
                $this->cryptParams["\160\x61\144\x64\x69\x6e\147"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\144\151\x67\x65\163\164"] = "\123\110\x41\x33\70\64";
                if (!(is_array($ZU) && !empty($ZU["\x74\171\x70\x65"]))) {
                    goto bP;
                }
                if (!($ZU["\x74\x79\x70\x65"] == "\160\165\x62\154\x69\143" || $ZU["\164\x79\x70\x65"] == "\160\162\151\x76\141\x74\145")) {
                    goto XL;
                }
                $this->cryptParams["\x74\171\160\145"] = $ZU["\x74\171\160\145"];
                goto qv;
                XL:
                bP:
                throw new Exception("\x43\145\162\x74\151\146\151\x63\141\164\145\x20\x22\x74\171\160\x65\42\x20\50\x70\x72\x69\x76\x61\x74\145\x2f\x70\x75\142\x6c\151\143\51\x20\x6d\x75\163\164\x20\142\145\40\x70\141\163\x73\x65\x64\40\x76\x69\141\x20\x70\x61\x72\141\155\145\x74\145\x72\x73");
            case self::RSA_SHA512:
                $this->cryptParams["\154\x69\142\162\141\162\171"] = "\157\x70\x65\156\x73\163\154";
                $this->cryptParams["\x6d\145\x74\150\157\x64"] = "\150\164\x74\160\x3a\x2f\57\167\x77\x77\56\167\63\x2e\157\162\x67\57\62\60\60\x31\x2f\60\64\x2f\170\155\x6c\x64\163\151\x67\x2d\x6d\x6f\x72\145\x23\x72\x73\x61\55\x73\x68\141\x35\x31\x32";
                $this->cryptParams["\x70\x61\x64\144\151\156\147"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\144\151\147\145\163\164"] = "\123\x48\101\65\x31\x32";
                if (!(is_array($ZU) && !empty($ZU["\x74\x79\160\145"]))) {
                    goto jz;
                }
                if (!($ZU["\x74\171\x70\145"] == "\160\165\x62\154\x69\x63" || $ZU["\164\171\160\x65"] == "\160\x72\151\x76\141\x74\145")) {
                    goto lD;
                }
                $this->cryptParams["\164\x79\x70\x65"] = $ZU["\x74\171\160\x65"];
                goto qv;
                lD:
                jz:
                throw new Exception("\103\145\x72\164\151\x66\x69\143\141\164\145\40\x22\x74\x79\160\x65\x22\40\50\160\x72\151\166\x61\x74\145\x2f\x70\165\x62\x6c\x69\143\51\40\x6d\x75\x73\x74\40\x62\145\40\160\x61\x73\163\x65\x64\40\166\151\x61\x20\x70\141\x72\x61\155\x65\164\x65\x72\x73");
            case self::HMAC_SHA1:
                $this->cryptParams["\x6c\x69\142\162\x61\162\171"] = $gv;
                $this->cryptParams["\155\145\x74\x68\157\x64"] = "\x68\164\164\x70\72\57\x2f\x77\167\x77\56\167\x33\56\x6f\x72\147\57\62\x30\x30\x30\x2f\60\x39\x2f\x78\x6d\154\x64\x73\x69\x67\43\x68\155\141\x63\x2d\163\x68\141\x31";
                goto qv;
            default:
                throw new Exception("\x49\156\x76\x61\154\x69\x64\40\x4b\x65\171\x20\x54\171\x70\x65");
        }
        Wp:
        qv:
        $this->type = $gv;
    }
    public function getSymmetricKeySize()
    {
        if (isset($this->cryptParams["\153\x65\171\x73\x69\172\145"])) {
            goto PB;
        }
        return null;
        PB:
        return $this->cryptParams["\x6b\145\x79\x73\x69\x7a\x65"];
    }
    public function generateSessionKey()
    {
        if (isset($this->cryptParams["\153\145\x79\x73\x69\172\145"])) {
            goto vx;
        }
        throw new Exception("\x55\156\x6b\156\x6f\167\x6e\40\153\145\x79\x20\x73\151\172\145\x20\146\157\x72\x20\x74\171\160\x65\x20\42" . $this->type . "\x22\x2e");
        vx:
        $Zb = $this->cryptParams["\153\x65\x79\x73\151\x7a\x65"];
        $BI = openssl_random_pseudo_bytes($Zb);
        if (!($this->type === self::TRIPLEDES_CBC)) {
            goto oB;
        }
        $Me = 0;
        B_:
        if (!($Me < strlen($BI))) {
            goto hi;
        }
        $Jx = ord($BI[$Me]) & 0xfe;
        $Ab = 1;
        $Ob = 1;
        M6:
        if (!($Ob < 8)) {
            goto pH;
        }
        $Ab ^= $Jx >> $Ob & 1;
        zk:
        $Ob++;
        goto M6;
        pH:
        $Jx |= $Ab;
        $BI[$Me] = chr($Jx);
        Sd:
        $Me++;
        goto B_;
        hi:
        oB:
        $this->key = $BI;
        return $BI;
    }
    public static function getRawThumbprint($rG)
    {
        $bF = explode("\12", $rG);
        $ni = '';
        $I7 = false;
        foreach ($bF as $PF) {
            if (!$I7) {
                goto jx;
            }
            if (!(strncmp($PF, "\55\55\x2d\55\55\x45\116\x44\x20\103\x45\122\124\x49\x46\x49\x43\x41\x54\105", 20) == 0)) {
                goto IB;
            }
            goto IE;
            IB:
            $ni .= trim($PF);
            goto A5;
            jx:
            if (!(strncmp($PF, "\55\55\x2d\x2d\x2d\x42\105\107\111\116\x20\x43\105\x52\124\111\106\x49\x43\101\x54\105", 22) == 0)) {
                goto Zq;
            }
            $I7 = true;
            Zq:
            A5:
            Hu:
        }
        IE:
        if (empty($ni)) {
            goto i9;
        }
        return strtolower(sha1(base64_decode($ni)));
        i9:
        return null;
    }
    public function loadKey($BI, $T1 = false, $B5 = false)
    {
        if ($T1) {
            goto VS;
        }
        $this->key = $BI;
        goto Q0;
        VS:
        $this->key = file_get_contents($BI);
        Q0:
        if ($B5) {
            goto Js;
        }
        $this->x509Certificate = null;
        goto R7;
        Js:
        $this->key = openssl_x509_read($this->key);
        openssl_x509_export($this->key, $nu);
        $this->x509Certificate = $nu;
        $this->key = $nu;
        R7:
        if (!($this->cryptParams["\x6c\x69\142\162\141\x72\171"] == "\x6f\x70\145\156\163\x73\x6c")) {
            goto JJ;
        }
        switch ($this->cryptParams["\164\x79\x70\x65"]) {
            case "\x70\x75\142\x6c\151\x63":
                if (!$B5) {
                    goto iz;
                }
                $this->X509Thumbprint = self::getRawThumbprint($this->key);
                iz:
                $this->key = openssl_get_publickey($this->key);
                if ($this->key) {
                    goto YA;
                }
                throw new Exception("\125\156\x61\142\x6c\x65\x20\x74\x6f\40\145\x78\x74\162\141\143\x74\x20\x70\165\142\154\151\143\x20\153\x65\x79");
                YA:
                goto BC;
            case "\x70\x72\x69\x76\141\164\x65":
                $this->key = openssl_get_privatekey($this->key, $this->passphrase);
                goto BC;
            case "\x73\171\155\x6d\145\x74\162\151\x63":
                if (!(strlen($this->key) < $this->cryptParams["\153\x65\x79\x73\x69\x7a\x65"])) {
                    goto FY;
                }
                throw new Exception("\x4b\x65\171\x20\x6d\165\163\x74\40\143\x6f\x6e\164\x61\151\156\40\x61\164\40\x6c\145\141\163\164\40\62\x35\x20\143\x68\141\x72\x61\x63\x74\x65\162\x73\40\146\x6f\162\40\164\x68\151\x73\40\143\x69\x70\150\145\162");
                FY:
                goto BC;
            default:
                throw new Exception("\x55\156\x6b\156\x6f\167\x6e\40\164\171\160\145");
        }
        Ni:
        BC:
        JJ:
    }
    private function padISO10126($ni, $KB)
    {
        if (!($KB > 256)) {
            goto rz;
        }
        throw new Exception("\x42\x6c\157\143\x6b\40\x73\151\x7a\145\x20\150\151\147\150\x65\162\x20\x74\150\141\156\40\62\65\66\40\156\x6f\164\40\x61\x6c\x6c\x6f\167\145\x64");
        rz:
        $wR = $KB - strlen($ni) % $KB;
        $rR = chr($wR);
        return $ni . str_repeat($rR, $wR);
    }
    private function unpadISO10126($ni)
    {
        $wR = substr($ni, -1);
        $WF = ord($wR);
        return substr($ni, 0, -$WF);
    }
    private function encryptSymmetric($ni)
    {
        $this->iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cryptParams["\143\151\160\x68\x65\x72"]));
        $ni = $this->padISO10126($ni, $this->cryptParams["\x62\154\x6f\143\153\163\151\172\145"]);
        $CT = openssl_encrypt($ni, $this->cryptParams["\x63\x69\160\150\145\162"], $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->iv);
        if (!(false === $CT)) {
            goto Tx;
        }
        throw new Exception("\106\x61\x69\154\165\162\145\x20\x65\156\x63\162\x79\160\164\151\x6e\x67\x20\x44\x61\x74\141\x20\50\x6f\x70\x65\x6e\x73\x73\x6c\x20\163\171\155\x6d\145\164\162\x69\x63\51\40\55\40" . openssl_error_string());
        Tx:
        return $this->iv . $CT;
    }
    private function decryptSymmetric($ni)
    {
        $RP = openssl_cipher_iv_length($this->cryptParams["\143\151\160\150\145\162"]);
        $this->iv = substr($ni, 0, $RP);
        $ni = substr($ni, $RP);
        $N5 = openssl_decrypt($ni, $this->cryptParams["\x63\151\x70\x68\145\162"], $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->iv);
        if (!(false === $N5)) {
            goto sW;
        }
        throw new Exception("\106\x61\x69\x6c\x75\x72\145\x20\x64\x65\143\162\171\160\x74\151\156\147\x20\x44\x61\x74\x61\40\50\x6f\x70\145\156\x73\x73\154\40\163\171\155\x6d\145\x74\x72\151\143\x29\40\55\40" . openssl_error_string());
        sW:
        return $this->unpadISO10126($N5);
    }
    private function encryptPublic($ni)
    {
        if (openssl_public_encrypt($ni, $CT, $this->key, $this->cryptParams["\160\x61\144\144\151\156\x67"])) {
            goto T0;
        }
        throw new Exception("\106\141\151\x6c\165\x72\x65\x20\145\156\x63\x72\171\160\x74\x69\x6e\147\40\104\x61\x74\x61\40\x28\157\x70\x65\156\x73\163\x6c\40\160\x75\x62\154\x69\143\x29\x20\55\x20" . openssl_error_string());
        T0:
        return $CT;
    }
    private function decryptPublic($ni)
    {
        if (openssl_public_decrypt($ni, $N5, $this->key, $this->cryptParams["\x70\141\x64\x64\151\156\x67"])) {
            goto yw;
        }
        throw new Exception("\106\x61\151\154\165\x72\145\x20\x64\x65\x63\x72\171\x70\164\151\156\x67\40\104\141\x74\141\x20\50\157\160\x65\156\x73\x73\x6c\x20\160\x75\142\x6c\151\x63\x29\x20\55\x20" . openssl_error_string());
        yw:
        return $N5;
    }
    private function encryptPrivate($ni)
    {
        if (openssl_private_encrypt($ni, $CT, $this->key, $this->cryptParams["\160\141\144\x64\151\156\147"])) {
            goto ON;
        }
        throw new Exception("\106\141\x69\154\165\x72\x65\x20\145\x6e\x63\x72\x79\x70\x74\151\x6e\x67\40\x44\141\164\x61\x20\x28\x6f\160\145\x6e\x73\163\x6c\x20\x70\x72\151\166\141\x74\x65\x29\x20\x2d\x20" . openssl_error_string());
        ON:
        return $CT;
    }
    private function decryptPrivate($ni)
    {
        if (openssl_private_decrypt($ni, $N5, $this->key, $this->cryptParams["\160\x61\144\144\x69\156\147"])) {
            goto ns;
        }
        throw new Exception("\x46\141\x69\x6c\165\162\x65\x20\x64\145\143\x72\x79\x70\164\151\x6e\147\x20\x44\141\x74\x61\x20\x28\x6f\160\x65\156\163\x73\x6c\40\160\x72\x69\166\141\164\x65\51\40\55\40" . openssl_error_string());
        ns:
        return $N5;
    }
    private function signOpenSSL($ni)
    {
        $iU = OPENSSL_ALGO_SHA1;
        if (empty($this->cryptParams["\x64\151\147\x65\163\164"])) {
            goto rj;
        }
        $iU = $this->cryptParams["\x64\x69\147\145\163\164"];
        rj:
        if (openssl_sign($ni, $ju, $this->key, $iU)) {
            goto G0;
        }
        throw new Exception("\106\x61\x69\x6c\x75\x72\x65\40\123\x69\x67\156\x69\x6e\x67\x20\104\141\164\141\72\40" . openssl_error_string() . "\x20\x2d\40" . $iU);
        G0:
        return $ju;
    }
    private function verifyOpenSSL($ni, $ju)
    {
        $iU = OPENSSL_ALGO_SHA1;
        if (empty($this->cryptParams["\x64\x69\x67\x65\163\x74"])) {
            goto mC;
        }
        $iU = $this->cryptParams["\x64\151\147\x65\x73\164"];
        mC:
        return openssl_verify($ni, $ju, $this->key, $iU);
    }
    public function encryptData($ni)
    {
        if (!($this->cryptParams["\154\x69\x62\x72\x61\162\x79"] === "\157\160\x65\x6e\x73\x73\154")) {
            goto BQ;
        }
        switch ($this->cryptParams["\x74\x79\160\145"]) {
            case "\163\171\155\155\x65\x74\x72\x69\143":
                return $this->encryptSymmetric($ni);
            case "\x70\165\x62\154\x69\143":
                return $this->encryptPublic($ni);
            case "\x70\x72\x69\x76\141\164\145":
                return $this->encryptPrivate($ni);
        }
        IJ:
        a8:
        BQ:
    }
    public function decryptData($ni)
    {
        if (!($this->cryptParams["\154\151\142\162\x61\x72\x79"] === "\157\160\x65\156\163\163\154")) {
            goto oC;
        }
        switch ($this->cryptParams["\x74\x79\160\145"]) {
            case "\163\x79\x6d\155\145\164\x72\x69\143":
                return $this->decryptSymmetric($ni);
            case "\x70\x75\142\154\x69\143":
                return $this->decryptPublic($ni);
            case "\160\x72\151\166\x61\164\145":
                return $this->decryptPrivate($ni);
        }
        we:
        SQ:
        oC:
    }
    public function signData($ni)
    {
        switch ($this->cryptParams["\x6c\x69\142\x72\141\x72\x79"]) {
            case "\x6f\x70\x65\156\x73\x73\154":
                return $this->signOpenSSL($ni);
            case self::HMAC_SHA1:
                return hash_hmac("\x73\x68\141\x31", $ni, $this->key, true);
        }
        Yj:
        pv:
    }
    public function verifySignature($ni, $ju)
    {
        switch ($this->cryptParams["\154\x69\x62\162\x61\x72\171"]) {
            case "\x6f\x70\145\156\x73\x73\154":
                return $this->verifyOpenSSL($ni, $ju);
            case self::HMAC_SHA1:
                $dd = hash_hmac("\163\150\x61\x31", $ni, $this->key, true);
                return strcmp($ju, $dd) == 0;
        }
        lH:
        bF:
    }
    public function getAlgorith()
    {
        return $this->getAlgorithm();
    }
    public function getAlgorithm()
    {
        return $this->cryptParams["\x6d\x65\x74\x68\x6f\144"];
    }
    public static function makeAsnSegment($gv, $PD)
    {
        switch ($gv) {
            case 0x2:
                if (!(ord($PD) > 0x7f)) {
                    goto rN;
                }
                $PD = chr(0) . $PD;
                rN:
                goto Xn;
            case 0x3:
                $PD = chr(0) . $PD;
                goto Xn;
        }
        rC:
        Xn:
        $cM = strlen($PD);
        if ($cM < 128) {
            goto U7;
        }
        if ($cM < 0x100) {
            goto ct;
        }
        if ($cM < 0x10000) {
            goto Iz;
        }
        $gc = null;
        goto pK;
        Iz:
        $gc = sprintf("\x25\x63\45\x63\45\143\45\143\45\163", $gv, 0x82, $cM / 0x100, $cM % 0x100, $PD);
        pK:
        goto jE;
        ct:
        $gc = sprintf("\45\x63\x25\143\x25\x63\45\x73", $gv, 0x81, $cM, $PD);
        jE:
        goto C2;
        U7:
        $gc = sprintf("\45\x63\x25\x63\x25\163", $gv, $cM, $PD);
        C2:
        return $gc;
    }
    public static function convertRSA($gf, $IM)
    {
        $sM = self::makeAsnSegment(0x2, $IM);
        $IN = self::makeAsnSegment(0x2, $gf);
        $sQ = self::makeAsnSegment(0x30, $IN . $sM);
        $jo = self::makeAsnSegment(0x3, $sQ);
        $GE = pack("\x48\52", "\x33\x30\60\x44\60\x36\60\71\x32\101\70\66\x34\70\70\66\106\67\x30\104\60\61\60\61\x30\61\60\65\60\x30");
        $sd = self::makeAsnSegment(0x30, $GE . $jo);
        $ZY = base64_encode($sd);
        $Ia = "\x2d\x2d\55\x2d\x2d\x42\105\x47\x49\116\x20\x50\125\x42\x4c\111\x43\x20\x4b\105\131\55\x2d\55\x2d\x2d\12";
        $l_ = 0;
        Ww:
        if (!($IS = substr($ZY, $l_, 64))) {
            goto dc;
        }
        $Ia = $Ia . $IS . "\xa";
        $l_ += 64;
        goto Ww;
        dc:
        return $Ia . "\x2d\55\x2d\x2d\55\105\116\104\x20\120\125\102\114\111\103\40\x4b\x45\x59\x2d\55\x2d\x2d\x2d\xa";
    }
    public function serializeKey($zn)
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
    public static function fromEncryptedKeyElement(DOMElement $Bu)
    {
        $zV = new XMLSecEncSAML();
        $zV->setNode($Bu);
        if ($r4 = $zV->locateKey()) {
            goto lK;
        }
        throw new Exception("\125\156\x61\x62\x6c\145\x20\x74\157\x20\x6c\x6f\x63\141\x74\x65\40\141\154\147\x6f\x72\x69\164\150\x6d\x20\x66\157\162\x20\x74\x68\151\x73\x20\x45\156\x63\162\x79\160\164\x65\x64\x20\113\145\x79");
        lK:
        $r4->isEncrypted = true;
        $r4->encryptedCtx = $zV;
        XMLSecEncSAML::staticLocateKeyInfo($r4, $Bu);
        return $r4;
    }
}
class XMLSecurityDSigSAML
{
    const XMLDSIGNS = "\x68\164\x74\x70\72\57\x2f\x77\x77\167\56\167\63\x2e\157\x72\147\x2f\62\60\60\x30\57\60\x39\57\170\x6d\154\x64\x73\x69\x67\43";
    const SHA1 = "\150\164\164\160\x3a\x2f\x2f\167\167\167\56\x77\x33\56\x6f\x72\x67\57\x32\x30\x30\x30\57\60\71\x2f\x78\155\154\x64\x73\151\147\x23\163\150\x61\x31";
    const SHA256 = "\x68\164\164\x70\x3a\x2f\x2f\x77\x77\167\x2e\x77\63\x2e\157\162\147\x2f\x32\60\60\61\57\x30\64\x2f\170\x6d\x6c\x65\x6e\143\43\x73\150\x61\x32\65\x36";
    const SHA384 = "\x68\x74\164\160\x3a\x2f\57\x77\x77\x77\56\167\x33\56\x6f\x72\147\x2f\62\x30\x30\61\57\60\64\57\x78\x6d\154\144\163\151\x67\x2d\155\x6f\x72\145\43\x73\150\141\63\x38\x34";
    const SHA512 = "\x68\x74\164\x70\x3a\57\x2f\x77\167\167\x2e\167\x33\x2e\157\162\x67\57\62\x30\x30\61\57\x30\x34\57\x78\x6d\x6c\145\x6e\x63\43\x73\150\141\x35\61\62";
    const RIPEMD160 = "\x68\164\164\x70\72\57\x2f\167\167\x77\56\167\x33\56\x6f\x72\147\x2f\x32\60\60\61\57\60\64\x2f\x78\155\x6c\145\156\143\43\162\x69\160\145\x6d\x64\61\66\60";
    const C14N = "\x68\164\x74\x70\72\57\x2f\167\167\x77\x2e\x77\63\x2e\157\x72\147\x2f\124\x52\x2f\x32\x30\60\x31\57\122\x45\103\x2d\x78\155\154\55\143\61\64\156\55\x32\60\60\61\x30\63\x31\x35";
    const C14N_COMMENTS = "\150\164\x74\x70\x3a\x2f\57\x77\167\x77\x2e\167\63\56\157\x72\147\x2f\124\x52\57\62\60\60\61\x2f\x52\x45\x43\55\170\x6d\x6c\55\143\x31\64\156\x2d\x32\x30\60\x31\x30\63\x31\x35\x23\127\x69\x74\150\x43\157\155\155\145\156\164\x73";
    const EXC_C14N = "\150\164\164\x70\72\57\x2f\x77\x77\x77\x2e\x77\x33\56\x6f\162\147\57\x32\60\x30\x31\x2f\61\x30\57\170\155\x6c\x2d\145\170\x63\55\143\61\x34\x6e\43";
    const EXC_C14N_COMMENTS = "\150\x74\164\x70\x3a\x2f\57\167\x77\x77\56\x77\63\x2e\x6f\162\x67\57\62\60\60\x31\57\x31\x30\x2f\x78\x6d\154\x2d\145\x78\143\x2d\143\x31\x34\x6e\x23\127\151\x74\150\103\x6f\x6d\x6d\145\156\x74\163";
    const template = "\74\x64\x73\72\x53\151\147\156\141\x74\x75\162\x65\40\x78\155\154\156\163\x3a\x64\163\75\x22\x68\x74\x74\x70\x3a\x2f\57\167\167\167\x2e\x77\x33\56\x6f\162\147\x2f\x32\x30\x30\60\57\x30\71\57\170\155\154\144\163\x69\147\x23\x22\x3e\15\xa\40\x20\74\144\163\x3a\123\151\x67\156\x65\x64\111\156\x66\157\x3e\xd\12\40\40\x20\x20\74\144\163\x3a\x53\151\x67\x6e\x61\x74\165\162\x65\x4d\x65\164\x68\x6f\144\x20\x2f\x3e\15\xa\x20\x20\x3c\57\144\163\72\123\x69\x67\x6e\145\x64\111\156\146\x6f\76\xd\xa\x3c\57\x64\163\x3a\123\x69\147\x6e\x61\164\165\162\145\76";
    const BASE_TEMPLATE = "\x3c\x53\151\147\x6e\x61\x74\x75\162\x65\x20\170\x6d\x6c\156\x73\x3d\42\150\x74\x74\x70\72\x2f\57\167\167\x77\56\x77\63\56\x6f\162\x67\57\62\60\60\60\57\x30\x39\x2f\170\x6d\x6c\x64\x73\151\x67\x23\x22\76\15\12\x20\x20\74\123\x69\147\156\x65\x64\111\x6e\x66\x6f\x3e\xd\xa\40\x20\40\x20\74\123\x69\x67\x6e\141\x74\x75\x72\x65\115\145\x74\150\157\x64\40\x2f\x3e\xd\12\x20\40\x3c\x2f\123\151\x67\156\x65\144\x49\x6e\x66\157\x3e\15\12\x3c\x2f\x53\x69\x67\x6e\141\x74\165\162\145\x3e";
    public $sigNode = null;
    public $idKeys = array();
    public $idNS = array();
    private $signedInfo = null;
    private $xPathCtx = null;
    private $canonicalMethod = null;
    private $prefix = '';
    private $searchpfx = "\163\x65\x63\x64\163\x69\147";
    private $validatedNodes = null;
    public function __construct($p2 = "\x64\x73")
    {
        $Ko = self::BASE_TEMPLATE;
        if (empty($p2)) {
            goto J1;
        }
        $this->prefix = $p2 . "\x3a";
        $c7 = array("\74\123", "\x3c\57\123", "\170\x6d\x6c\x6e\x73\75");
        $C6 = array("\74{$p2}\72\x53", "\74\x2f{$p2}\72\123", "\170\x6d\154\x6e\163\x3a{$p2}\75");
        $Ko = str_replace($c7, $C6, $Ko);
        J1:
        $sS = new DOMDocument();
        $sS->loadXML($Ko);
        $this->sigNode = $sS->documentElement;
    }
    private function resetXPathObj()
    {
        $this->xPathCtx = null;
    }
    private function getXPathObj()
    {
        if (!(empty($this->xPathCtx) && !empty($this->sigNode))) {
            goto jD;
        }
        $mS = new DOMXPath($this->sigNode->ownerDocument);
        $mS->registerNamespace("\163\x65\143\144\163\x69\147", self::XMLDSIGNS);
        $this->xPathCtx = $mS;
        jD:
        return $this->xPathCtx;
    }
    public static function generateGUID($p2 = "\x70\x66\170")
    {
        $KI = md5(uniqid(mt_rand(), true));
        $xe = $p2 . substr($KI, 0, 8) . "\x2d" . substr($KI, 8, 4) . "\x2d" . substr($KI, 12, 4) . "\55" . substr($KI, 16, 4) . "\x2d" . substr($KI, 20, 12);
        return $xe;
    }
    public static function generate_GUID($p2 = "\160\x66\170")
    {
        return self::generateGUID($p2);
    }
    public function locateSignature($Br, $iQ = 0)
    {
        if ($Br instanceof DOMDocument) {
            goto sq;
        }
        $Zw = $Br->ownerDocument;
        goto N6;
        sq:
        $Zw = $Br;
        N6:
        if (!$Zw) {
            goto L4;
        }
        $mS = new DOMXPath($Zw);
        $mS->registerNamespace("\x73\x65\x63\144\x73\x69\x67", self::XMLDSIGNS);
        $rw = "\x2e\x2f\57\x73\145\143\x64\163\151\x67\72\x53\x69\x67\156\x61\164\x75\x72\x65";
        $pA = $mS->query($rw, $Br);
        $this->sigNode = $pA->item($iQ);
        return $this->sigNode;
        L4:
        return null;
    }
    public function createNewSignNode($Os, $n2 = null)
    {
        $Zw = $this->sigNode->ownerDocument;
        if (!is_null($n2)) {
            goto h0;
        }
        $El = $Zw->createElementNS(self::XMLDSIGNS, $this->prefix . $Os);
        goto of;
        h0:
        $El = $Zw->createElementNS(self::XMLDSIGNS, $this->prefix . $Os, $n2);
        of:
        return $El;
    }
    public function setCanonicalMethod($kS)
    {
        switch ($kS) {
            case "\150\x74\164\160\x3a\x2f\x2f\167\167\167\56\167\63\56\x6f\x72\147\57\x54\x52\57\62\x30\60\x31\x2f\x52\105\x43\x2d\x78\x6d\x6c\55\143\x31\64\156\x2d\62\60\x30\x31\x30\63\x31\65":
            case "\150\164\x74\160\72\57\x2f\x77\167\167\x2e\167\x33\56\x6f\162\x67\x2f\124\122\57\62\60\60\x31\x2f\x52\x45\103\55\170\155\154\55\x63\x31\64\x6e\55\x32\x30\x30\61\60\x33\x31\x35\x23\127\x69\x74\x68\103\157\155\x6d\x65\x6e\x74\163":
            case "\150\164\x74\160\72\x2f\57\x77\167\x77\56\167\x33\x2e\x6f\x72\147\57\x32\x30\x30\61\x2f\61\x30\57\170\155\x6c\55\145\170\143\x2d\143\x31\64\x6e\43":
            case "\x68\164\164\x70\72\57\57\167\167\x77\x2e\x77\x33\x2e\x6f\x72\147\57\x32\60\60\x31\57\61\x30\57\x78\x6d\x6c\55\x65\170\x63\x2d\x63\x31\64\x6e\x23\127\151\x74\150\103\x6f\155\x6d\145\x6e\x74\x73":
                $this->canonicalMethod = $kS;
                goto dd;
            default:
                throw new Exception("\x49\156\166\x61\154\x69\144\40\103\x61\x6e\157\156\x69\x63\141\154\x20\115\145\164\x68\x6f\144");
        }
        WC:
        dd:
        if (!($mS = $this->getXPathObj())) {
            goto oo;
        }
        $rw = "\x2e\x2f" . $this->searchpfx . "\72\123\151\147\156\x65\x64\x49\x6e\146\157";
        $pA = $mS->query($rw, $this->sigNode);
        if (!($tl = $pA->item(0))) {
            goto sB;
        }
        $rw = "\56\x2f" . $this->searchpfx . "\103\x61\x6e\x6f\156\x69\x63\x61\154\151\x7a\141\164\151\157\x6e\115\x65\164\150\x6f\x64";
        $pA = $mS->query($rw, $tl);
        if ($of = $pA->item(0)) {
            goto Kb;
        }
        $of = $this->createNewSignNode("\x43\141\x6e\x6f\156\x69\143\x61\x6c\x69\172\x61\x74\x69\x6f\156\115\x65\164\150\157\144");
        $tl->insertBefore($of, $tl->firstChild);
        Kb:
        $of->setAttribute("\101\x6c\x67\x6f\162\151\x74\x68\155", $this->canonicalMethod);
        sB:
        oo:
    }
    private function canonicalizeData($El, $XH, $ll = null, $gD = null)
    {
        $Sd = false;
        $gl = false;
        switch ($XH) {
            case "\150\164\x74\x70\72\57\57\167\x77\167\56\x77\x33\x2e\157\x72\x67\x2f\x54\x52\57\x32\x30\x30\61\57\x52\x45\x43\x2d\x78\x6d\154\55\143\61\64\x6e\55\x32\60\60\x31\x30\63\x31\65":
                $Sd = false;
                $gl = false;
                goto Z7;
            case "\x68\164\x74\x70\x3a\x2f\x2f\x77\167\x77\x2e\x77\63\x2e\157\162\147\x2f\124\122\57\62\x30\x30\x31\57\x52\105\x43\55\x78\155\154\x2d\143\61\64\156\x2d\x32\x30\60\x31\x30\x33\x31\x35\x23\x57\151\x74\x68\103\x6f\x6d\155\145\156\x74\x73":
                $gl = true;
                goto Z7;
            case "\150\x74\164\x70\72\x2f\x2f\x77\167\x77\x2e\x77\x33\x2e\157\162\147\57\62\x30\x30\x31\57\x31\x30\x2f\170\x6d\x6c\x2d\x65\x78\143\x2d\x63\x31\64\156\43":
                $Sd = true;
                goto Z7;
            case "\150\x74\x74\x70\x3a\57\57\167\167\167\x2e\167\x33\x2e\157\162\x67\x2f\x32\60\x30\61\x2f\x31\60\57\x78\x6d\154\55\x65\x78\143\55\x63\x31\64\156\43\127\x69\x74\150\103\157\155\x6d\x65\156\164\163":
                $Sd = true;
                $gl = true;
                goto Z7;
        }
        uU:
        Z7:
        if (!(is_null($ll) && $El instanceof DOMNode && $El->ownerDocument !== null && $El->isSameNode($El->ownerDocument->documentElement))) {
            goto DM;
        }
        $Bu = $El;
        IG:
        if (!($mv = $Bu->previousSibling)) {
            goto ta;
        }
        if (!($mv->nodeType == XML_PI_NODE || $mv->nodeType == XML_COMMENT_NODE && $gl)) {
            goto ya;
        }
        goto ta;
        ya:
        $Bu = $mv;
        goto IG;
        ta:
        if (!($mv == null)) {
            goto F5;
        }
        $El = $El->ownerDocument;
        F5:
        DM:
        return $El->C14N($Sd, $gl, $ll, $gD);
    }
    public function canonicalizeSignedInfo()
    {
        $Zw = $this->sigNode->ownerDocument;
        $XH = null;
        if (!$Zw) {
            goto Gt;
        }
        $mS = $this->getXPathObj();
        $rw = "\56\x2f\163\145\x63\x64\x73\151\147\x3a\123\x69\147\x6e\x65\x64\x49\156\x66\x6f";
        $pA = $mS->query($rw, $this->sigNode);
        if (!($YP = $pA->item(0))) {
            goto Od;
        }
        $rw = "\x2e\x2f\163\x65\143\144\163\151\147\x3a\103\x61\156\157\156\x69\x63\141\x6c\x69\172\x61\164\x69\x6f\156\x4d\145\164\150\157\144";
        $pA = $mS->query($rw, $YP);
        if (!($of = $pA->item(0))) {
            goto l1;
        }
        $XH = $of->getAttribute("\x41\154\147\x6f\162\151\x74\150\155");
        l1:
        $this->signedInfo = $this->canonicalizeData($YP, $XH);
        return $this->signedInfo;
        Od:
        Gt:
        return null;
    }
    public function calculateDigest($aL, $ni, $P0 = true)
    {
        switch ($aL) {
            case self::SHA1:
                $JG = "\x73\x68\x61\61";
                goto vO;
            case self::SHA256:
                $JG = "\163\x68\141\x32\65\66";
                goto vO;
            case self::SHA384:
                $JG = "\163\x68\141\x33\x38\64";
                goto vO;
            case self::SHA512:
                $JG = "\163\x68\141\65\61\x32";
                goto vO;
            case self::RIPEMD160:
                $JG = "\162\x69\160\x65\155\x64\x31\x36\60";
                goto vO;
            default:
                throw new Exception("\x43\141\x6e\156\157\164\40\166\141\x6c\151\x64\x61\164\x65\40\x64\x69\147\x65\x73\x74\72\x20\125\156\163\x75\x70\x70\x6f\x72\x74\x65\x64\40\101\x6c\x67\x6f\162\151\x74\x68\155\40\x3c{$aL}\76");
        }
        W3:
        vO:
        $q0 = hash($JG, $ni, true);
        if (!$P0) {
            goto fJ;
        }
        $q0 = base64_encode($q0);
        fJ:
        return $q0;
    }
    public function validateDigest($LG, $ni)
    {
        $mS = new DOMXPath($LG->ownerDocument);
        $mS->registerNamespace("\x73\x65\143\x64\163\151\147", self::XMLDSIGNS);
        $rw = "\x73\164\162\x69\156\x67\50\56\x2f\x73\x65\x63\144\x73\x69\x67\72\104\151\x67\x65\163\164\115\x65\164\150\157\x64\57\x40\101\x6c\147\x6f\x72\151\164\150\x6d\x29";
        $aL = $mS->evaluate($rw, $LG);
        $z_ = $this->calculateDigest($aL, $ni, false);
        $rw = "\x73\164\162\151\x6e\x67\50\56\57\x73\145\x63\x64\163\151\147\x3a\x44\x69\x67\145\163\164\126\x61\154\x75\x65\x29";
        $Om = $mS->evaluate($rw, $LG);
        return $z_ == base64_decode($Om);
    }
    public function processTransforms($LG, $Tn, $aN = true)
    {
        $ni = $Tn;
        $mS = new DOMXPath($LG->ownerDocument);
        $mS->registerNamespace("\163\145\x63\144\163\151\147", self::XMLDSIGNS);
        $rw = "\x2e\57\x73\x65\x63\x64\x73\151\147\72\124\162\x61\x6e\163\146\157\162\x6d\x73\57\x73\x65\143\144\163\x69\x67\72\124\x72\141\156\163\146\x6f\x72\155";
        $uj = $mS->query($rw, $LG);
        $et = "\x68\164\x74\x70\x3a\x2f\x2f\x77\167\x77\x2e\x77\63\56\157\x72\x67\x2f\x54\x52\x2f\x32\60\x30\61\x2f\122\105\x43\x2d\x78\155\x6c\x2d\x63\61\64\156\55\62\60\60\x31\x30\63\x31\x35";
        $ll = null;
        $gD = null;
        foreach ($uj as $Ug) {
            $tw = $Ug->getAttribute("\101\x6c\x67\157\x72\x69\x74\x68\x6d");
            switch ($tw) {
                case "\150\164\164\x70\72\57\57\x77\x77\x77\x2e\167\63\x2e\157\162\147\57\x32\60\60\61\x2f\61\x30\57\x78\155\x6c\x2d\x65\x78\x63\x2d\x63\61\64\156\43":
                case "\x68\x74\x74\x70\72\x2f\57\x77\167\x77\x2e\x77\63\56\157\162\x67\57\62\60\x30\61\57\61\60\57\170\155\x6c\x2d\145\170\143\55\x63\61\64\156\43\x57\x69\164\x68\x43\x6f\x6d\x6d\145\x6e\x74\x73":
                    if (!$aN) {
                        goto fz;
                    }
                    $et = $tw;
                    goto Iq;
                    fz:
                    $et = "\x68\164\x74\x70\72\x2f\x2f\167\167\x77\x2e\x77\x33\x2e\157\x72\x67\x2f\x32\60\60\x31\x2f\x31\x30\57\170\x6d\x6c\55\145\x78\x63\55\x63\x31\64\156\x23";
                    Iq:
                    $El = $Ug->firstChild;
                    ff:
                    if (!$El) {
                        goto gb;
                    }
                    if (!($El->localName == "\111\156\143\x6c\165\x73\151\166\145\116\141\x6d\145\x73\x70\x61\x63\x65\x73")) {
                        goto Ug;
                    }
                    if (!($tr = $El->getAttribute("\120\162\145\146\151\170\x4c\151\163\164"))) {
                        goto Pj;
                    }
                    $tB = array();
                    $Gq = explode("\40", $tr);
                    foreach ($Gq as $tr) {
                        $de = trim($tr);
                        if (empty($de)) {
                            goto qi;
                        }
                        $tB[] = $de;
                        qi:
                        Ro:
                    }
                    D6:
                    if (!(count($tB) > 0)) {
                        goto an;
                    }
                    $gD = $tB;
                    an:
                    Pj:
                    goto gb;
                    Ug:
                    $El = $El->nextSibling;
                    goto ff;
                    gb:
                    goto j1;
                case "\150\x74\164\x70\72\57\x2f\x77\x77\167\x2e\x77\63\56\157\x72\x67\x2f\x54\x52\x2f\62\60\x30\x31\x2f\122\105\x43\x2d\x78\155\x6c\x2d\143\61\64\156\55\62\x30\60\x31\60\x33\61\x35":
                case "\x68\x74\x74\160\72\x2f\x2f\x77\x77\167\x2e\x77\63\x2e\x6f\x72\147\57\x54\122\57\62\x30\x30\x31\57\x52\x45\x43\55\170\155\x6c\x2d\x63\61\x34\x6e\x2d\62\60\60\x31\60\x33\61\65\x23\127\151\164\x68\103\157\155\x6d\x65\156\x74\163":
                    if (!$aN) {
                        goto b3;
                    }
                    $et = $tw;
                    goto tP;
                    b3:
                    $et = "\x68\x74\x74\x70\72\x2f\x2f\x77\167\x77\56\167\63\x2e\x6f\162\x67\57\124\122\x2f\x32\60\x30\x31\x2f\122\105\103\x2d\x78\155\x6c\55\143\x31\x34\x6e\55\x32\60\60\61\x30\x33\x31\65";
                    tP:
                    goto j1;
                case "\x68\x74\x74\x70\72\57\57\x77\x77\x77\56\167\63\x2e\x6f\162\147\x2f\124\x52\x2f\61\x39\x39\71\57\122\x45\103\x2d\170\x70\141\x74\x68\55\61\71\71\71\61\61\x31\66":
                    $El = $Ug->firstChild;
                    XD:
                    if (!$El) {
                        goto Ly;
                    }
                    if (!($El->localName == "\130\120\x61\164\150")) {
                        goto Ca;
                    }
                    $ll = array();
                    $ll["\x71\x75\x65\x72\x79"] = "\x28\56\57\57\x2e\x20\x7c\x20\x2e\x2f\x2f\100\x2a\40\174\x20\x2e\57\x2f\x6e\141\155\145\x73\160\x61\x63\x65\72\72\52\51\133" . $El->nodeValue . "\135";
                    $Lk["\x6e\141\155\145\163\160\141\143\x65\x73"] = array();
                    $mu = $mS->query("\x2e\x2f\x6e\x61\x6d\x65\x73\x70\141\143\x65\72\72\x2a", $El);
                    foreach ($mu as $i5) {
                        if (!($i5->localName != "\170\155\x6c")) {
                            goto JG;
                        }
                        $ll["\156\x61\155\x65\163\x70\x61\x63\145\x73"][$i5->localName] = $i5->nodeValue;
                        JG:
                        cp:
                    }
                    PD:
                    goto Ly;
                    Ca:
                    $El = $El->nextSibling;
                    goto XD;
                    Ly:
                    goto j1;
            }
            x0:
            j1:
            jm:
        }
        FP:
        if (!$ni instanceof DOMNode) {
            goto mx;
        }
        $ni = $this->canonicalizeData($Tn, $et, $ll, $gD);
        mx:
        return $ni;
    }
    public function processRefNode($LG)
    {
        $D7 = null;
        $aN = true;
        if ($OW = $LG->getAttribute("\x55\x52\x49")) {
            goto xx;
        }
        $aN = false;
        $D7 = $LG->ownerDocument;
        goto yi;
        xx:
        $vz = parse_url($OW);
        if (empty($vz["\x70\x61\x74\x68"])) {
            goto ok;
        }
        $D7 = file_get_contents($vz);
        goto qY;
        ok:
        if ($FN = $vz["\146\162\x61\147\x6d\x65\x6e\164"]) {
            goto fw;
        }
        $D7 = $LG->ownerDocument;
        goto d1;
        fw:
        $aN = false;
        $kY = new DOMXPath($LG->ownerDocument);
        if (!($this->idNS && is_array($this->idNS))) {
            goto Gv;
        }
        foreach ($this->idNS as $v9 => $A2) {
            $kY->registerNamespace($v9, $A2);
            w3:
        }
        QP:
        Gv:
        $Jc = "\x40\x49\144\75\42" . $FN . "\x22";
        if (!is_array($this->idKeys)) {
            goto xD;
        }
        foreach ($this->idKeys as $Ra) {
            $Jc .= "\40\x6f\162\x20\x40{$Ra}\75\x27{$FN}\x27";
            w2:
        }
        Bd:
        xD:
        $rw = "\x2f\57\x2a\133" . $Jc . "\x5d";
        $D7 = $kY->query($rw)->item(0);
        d1:
        qY:
        yi:
        $ni = $this->processTransforms($LG, $D7, $aN);
        if ($this->validateDigest($LG, $ni)) {
            goto hB;
        }
        return false;
        hB:
        if (!$D7 instanceof DOMNode) {
            goto a4;
        }
        if (!empty($FN)) {
            goto Cp;
        }
        $this->validatedNodes[] = $D7;
        goto F9;
        Cp:
        $this->validatedNodes[$FN] = $D7;
        F9:
        a4:
        return true;
    }
    public function getRefNodeID($LG)
    {
        if (!($OW = $LG->getAttribute("\x55\x52\111"))) {
            goto Q8;
        }
        $vz = parse_url($OW);
        if (!empty($vz["\160\141\x74\x68"])) {
            goto UO;
        }
        if (!($FN = $vz["\x66\162\141\x67\x6d\145\156\164"])) {
            goto Ia;
        }
        return $FN;
        Ia:
        UO:
        Q8:
        return null;
    }
    public function getRefIDs()
    {
        $vn = array();
        $mS = $this->getXPathObj();
        $rw = "\56\x2f\163\145\x63\144\163\151\147\72\123\x69\x67\x6e\145\x64\111\156\146\157\x2f\x73\x65\143\144\163\151\x67\x3a\x52\145\146\x65\x72\x65\x6e\x63\x65";
        $pA = $mS->query($rw, $this->sigNode);
        if (!($pA->length == 0)) {
            goto MD;
        }
        throw new Exception("\122\145\x66\145\x72\x65\156\143\x65\40\156\x6f\144\145\x73\40\x6e\x6f\x74\x20\x66\x6f\x75\156\144");
        MD:
        foreach ($pA as $LG) {
            $vn[] = $this->getRefNodeID($LG);
            VU:
        }
        FM:
        return $vn;
    }
    public function validateReference()
    {
        $RN = $this->sigNode->ownerDocument->documentElement;
        if ($RN->isSameNode($this->sigNode)) {
            goto RQ;
        }
        if (!($this->sigNode->parentNode != null)) {
            goto B4;
        }
        $this->sigNode->parentNode->removeChild($this->sigNode);
        B4:
        RQ:
        $mS = $this->getXPathObj();
        $rw = "\x2e\x2f\163\x65\143\x64\163\x69\147\x3a\x53\x69\x67\x6e\145\x64\111\x6e\x66\157\57\x73\145\143\144\163\151\147\x3a\x52\145\146\145\162\x65\x6e\x63\145";
        $pA = $mS->query($rw, $this->sigNode);
        if (!($pA->length == 0)) {
            goto ye;
        }
        throw new Exception("\x52\x65\x66\145\162\145\156\143\145\x20\156\157\144\145\x73\x20\156\x6f\x74\40\x66\x6f\165\x6e\144");
        ye:
        $this->validatedNodes = array();
        foreach ($pA as $LG) {
            if ($this->processRefNode($LG)) {
                goto p2;
            }
            $this->validatedNodes = null;
            throw new Exception("\122\145\146\145\x72\x65\x6e\x63\145\x20\x76\x61\154\151\144\x61\164\x69\x6f\x6e\x20\x66\x61\151\x6c\x65\x64");
            p2:
            br:
        }
        wV:
        return true;
    }
    private function addRefInternal($u3, $El, $tw, $uI = null, $Eh = null)
    {
        $p2 = null;
        $Jr = null;
        $EO = "\x49\x64";
        $Vx = true;
        $bL = false;
        if (!is_array($Eh)) {
            goto n2;
        }
        $p2 = empty($Eh["\160\x72\145\146\x69\170"]) ? null : $Eh["\x70\x72\145\x66\151\170"];
        $Jr = empty($Eh["\160\x72\145\x66\151\170\x5f\156\x73"]) ? null : $Eh["\x70\x72\145\x66\x69\x78\x5f\156\x73"];
        $EO = empty($Eh["\151\144\x5f\156\141\x6d\x65"]) ? "\111\x64" : $Eh["\151\x64\137\x6e\141\x6d\145"];
        $Vx = !isset($Eh["\x6f\x76\x65\162\167\x72\151\x74\x65"]) ? true : (bool) $Eh["\157\x76\145\162\x77\x72\151\x74\x65"];
        $bL = !isset($Eh["\146\x6f\162\143\145\137\x75\162\151"]) ? false : (bool) $Eh["\x66\x6f\x72\x63\x65\137\x75\x72\151"];
        n2:
        $XA = $EO;
        if (empty($p2)) {
            goto A0;
        }
        $XA = $p2 . "\x3a" . $XA;
        A0:
        $LG = $this->createNewSignNode("\122\x65\x66\145\162\x65\156\x63\145");
        $u3->appendChild($LG);
        if (!$El instanceof DOMDocument) {
            goto x2;
        }
        if ($bL) {
            goto Vw;
        }
        goto gl;
        x2:
        $OW = null;
        if ($Vx) {
            goto Tg;
        }
        $OW = $Jr ? $El->getAttributeNS($Jr, $EO) : $El->getAttribute($EO);
        Tg:
        if (!empty($OW)) {
            goto tw;
        }
        $OW = self::generateGUID();
        $El->setAttributeNS($Jr, $XA, $OW);
        tw:
        $LG->setAttribute("\x55\122\111", "\x23" . $OW);
        goto gl;
        Vw:
        $LG->setAttribute("\x55\x52\x49", '');
        gl:
        $vM = $this->createNewSignNode("\x54\x72\141\156\x73\x66\x6f\x72\x6d\163");
        $LG->appendChild($vM);
        if (is_array($uI)) {
            goto fW;
        }
        if (!empty($this->canonicalMethod)) {
            goto w5;
        }
        goto d8;
        fW:
        foreach ($uI as $Ug) {
            $Nd = $this->createNewSignNode("\124\x72\x61\x6e\163\146\157\162\155");
            $vM->appendChild($Nd);
            if (is_array($Ug) && !empty($Ug["\x68\x74\164\160\x3a\x2f\57\x77\167\167\x2e\x77\x33\56\x6f\162\x67\x2f\124\x52\57\x31\x39\71\71\57\x52\105\103\x2d\x78\x70\141\x74\x68\x2d\x31\71\71\71\61\61\61\x36"]) && !empty($Ug["\x68\x74\164\160\x3a\x2f\x2f\167\167\x77\56\x77\x33\56\x6f\x72\x67\57\124\122\57\61\x39\71\71\57\122\105\103\55\x78\160\141\x74\x68\55\61\x39\x39\71\x31\61\61\66"]["\161\x75\145\x72\171"])) {
                goto Fq;
            }
            $Nd->setAttribute("\x41\154\147\x6f\x72\x69\x74\x68\x6d", $Ug);
            goto B2;
            Fq:
            $Nd->setAttribute("\101\154\147\x6f\162\151\164\150\155", "\150\x74\164\x70\x3a\57\57\167\x77\167\x2e\x77\x33\56\x6f\162\x67\57\124\122\x2f\61\71\x39\71\57\x52\x45\x43\x2d\170\160\141\x74\150\x2d\x31\x39\71\x39\61\x31\61\x36");
            $kP = $this->createNewSignNode("\130\x50\x61\x74\150", $Ug["\x68\164\164\x70\72\x2f\x2f\x77\x77\x77\x2e\x77\63\56\x6f\162\147\x2f\x54\122\x2f\x31\x39\x39\71\57\122\x45\103\55\x78\x70\x61\164\x68\x2d\x31\71\71\71\61\61\61\x36"]["\x71\x75\145\x72\x79"]);
            $Nd->appendChild($kP);
            if (empty($Ug["\150\164\164\160\x3a\57\57\167\x77\167\x2e\x77\63\56\x6f\x72\x67\57\124\122\x2f\61\71\71\x39\x2f\x52\105\x43\x2d\x78\160\x61\x74\x68\x2d\x31\71\71\x39\61\x31\x31\x36"]["\x6e\141\155\145\163\x70\141\143\x65\x73"])) {
                goto WY;
            }
            foreach ($Ug["\150\164\164\x70\72\57\57\167\167\167\56\167\x33\56\157\x72\x67\57\124\122\57\61\71\x39\71\57\122\105\x43\55\x78\x70\141\x74\150\x2d\x31\71\71\71\x31\61\61\x36"]["\156\x61\155\145\163\160\x61\143\145\163"] as $p2 => $sR) {
                $kP->setAttributeNS("\x68\x74\x74\x70\72\x2f\57\x77\x77\167\x2e\x77\63\x2e\x6f\x72\147\x2f\x32\x30\x30\x30\57\x78\155\154\x6e\x73\x2f", "\170\155\154\156\163\x3a{$p2}", $sR);
                oJ:
            }
            zF:
            WY:
            B2:
            Us:
        }
        gg:
        goto d8;
        w5:
        $Nd = $this->createNewSignNode("\x54\162\x61\x6e\x73\x66\157\162\x6d");
        $vM->appendChild($Nd);
        $Nd->setAttribute("\101\x6c\147\x6f\162\x69\164\150\x6d", $this->canonicalMethod);
        d8:
        $ti = $this->processTransforms($LG, $El);
        $z_ = $this->calculateDigest($tw, $ti);
        $xO = $this->createNewSignNode("\104\x69\147\x65\x73\164\115\x65\x74\150\x6f\144");
        $LG->appendChild($xO);
        $xO->setAttribute("\x41\x6c\147\x6f\x72\x69\164\150\x6d", $tw);
        $Om = $this->createNewSignNode("\104\x69\147\145\163\x74\126\x61\x6c\165\x65", $z_);
        $LG->appendChild($Om);
    }
    public function addReference($El, $tw, $uI = null, $Eh = null)
    {
        if (!($mS = $this->getXPathObj())) {
            goto p5;
        }
        $rw = "\x2e\x2f\x73\145\143\x64\163\x69\x67\72\x53\x69\x67\x6e\145\144\x49\x6e\x66\x6f";
        $pA = $mS->query($rw, $this->sigNode);
        if (!($HJ = $pA->item(0))) {
            goto FZ;
        }
        $this->addRefInternal($HJ, $El, $tw, $uI, $Eh);
        FZ:
        p5:
    }
    public function addReferenceList($KS, $tw, $uI = null, $Eh = null)
    {
        if (!($mS = $this->getXPathObj())) {
            goto lQ;
        }
        $rw = "\x2e\x2f\163\145\x63\144\163\151\147\x3a\123\x69\147\x6e\x65\144\111\x6e\x66\x6f";
        $pA = $mS->query($rw, $this->sigNode);
        if (!($HJ = $pA->item(0))) {
            goto CF;
        }
        foreach ($KS as $El) {
            $this->addRefInternal($HJ, $El, $tw, $uI, $Eh);
            la:
        }
        l6:
        CF:
        lQ:
    }
    public function addObject($ni, $DN = null, $Ia = null)
    {
        $QN = $this->createNewSignNode("\117\142\152\145\143\x74");
        $this->sigNode->appendChild($QN);
        if (empty($DN)) {
            goto X8;
        }
        $QN->setAttribute("\115\x69\155\x65\124\171\160\x65", $DN);
        X8:
        if (empty($Ia)) {
            goto Zv;
        }
        $QN->setAttribute("\105\156\x63\157\x64\x69\x6e\147", $Ia);
        Zv:
        if ($ni instanceof DOMElement) {
            goto yK;
        }
        $mr = $this->sigNode->ownerDocument->createTextNode($ni);
        goto Lk;
        yK:
        $mr = $this->sigNode->ownerDocument->importNode($ni, true);
        Lk:
        $QN->appendChild($mr);
        return $QN;
    }
    public function locateKey($El = null)
    {
        if (!empty($El)) {
            goto P0;
        }
        $El = $this->sigNode;
        P0:
        if ($El instanceof DOMNode) {
            goto rE;
        }
        return null;
        rE:
        if (!($Zw = $El->ownerDocument)) {
            goto QV;
        }
        $mS = new DOMXPath($Zw);
        $mS->registerNamespace("\x73\145\143\x64\x73\x69\147", self::XMLDSIGNS);
        $rw = "\x73\164\162\151\156\147\50\56\x2f\x73\x65\143\144\163\151\147\x3a\123\151\147\x6e\145\144\x49\x6e\x66\157\x2f\163\145\x63\x64\x73\x69\x67\x3a\123\x69\x67\156\141\164\165\162\x65\x4d\x65\164\150\157\x64\57\100\x41\154\x67\x6f\x72\151\x74\x68\155\x29";
        $tw = $mS->evaluate($rw, $El);
        if (!$tw) {
            goto Kl;
        }
        try {
            $r4 = new XMLSecurityKeySAML($tw, array("\164\171\160\145" => "\x70\x75\x62\x6c\x69\x63"));
        } catch (Exception $Um) {
            return null;
        }
        return $r4;
        Kl:
        QV:
        return null;
    }
    public function verify($r4)
    {
        $Zw = $this->sigNode->ownerDocument;
        $mS = new DOMXPath($Zw);
        $mS->registerNamespace("\163\145\143\144\163\x69\x67", self::XMLDSIGNS);
        $rw = "\163\164\162\x69\x6e\147\x28\56\x2f\163\145\143\x64\163\151\147\x3a\x53\151\x67\156\x61\x74\165\162\145\x56\x61\x6c\x75\x65\51";
        $Z5 = $mS->evaluate($rw, $this->sigNode);
        if (!empty($Z5)) {
            goto V2;
        }
        throw new Exception("\x55\156\x61\142\x6c\145\40\x74\157\40\154\x6f\143\141\x74\x65\40\123\151\x67\156\x61\x74\x75\162\145\126\141\x6c\165\145");
        V2:
        return $r4->verifySignature($this->signedInfo, base64_decode($Z5));
    }
    public function signData($r4, $ni)
    {
        return $r4->signData($ni);
    }
    public function sign($r4, $y8 = null)
    {
        if (!($y8 != null)) {
            goto fS;
        }
        $this->resetXPathObj();
        $this->appendSignature($y8);
        $this->sigNode = $y8->lastChild;
        fS:
        if (!($mS = $this->getXPathObj())) {
            goto Zu;
        }
        $rw = "\x2e\x2f\163\x65\x63\144\163\x69\147\x3a\123\x69\147\156\145\144\x49\156\x66\157";
        $pA = $mS->query($rw, $this->sigNode);
        if (!($HJ = $pA->item(0))) {
            goto G1;
        }
        $rw = "\56\x2f\x73\x65\143\x64\x73\151\147\72\123\151\x67\156\x61\x74\x75\162\x65\x4d\x65\x74\150\157\x64";
        $pA = $mS->query($rw, $HJ);
        $vj = $pA->item(0);
        $vj->setAttribute("\101\154\x67\x6f\x72\151\x74\150\x6d", $r4->type);
        $ni = $this->canonicalizeData($HJ, $this->canonicalMethod);
        $Z5 = base64_encode($this->signData($r4, $ni));
        $Vc = $this->createNewSignNode("\123\x69\147\x6e\141\164\x75\162\145\x56\x61\x6c\165\x65", $Z5);
        if ($Qa = $HJ->nextSibling) {
            goto BE;
        }
        $this->sigNode->appendChild($Vc);
        goto SN;
        BE:
        $Qa->parentNode->insertBefore($Vc, $Qa);
        SN:
        G1:
        Zu:
    }
    public function appendCert()
    {
    }
    public function appendKey($r4, $zn = null)
    {
        $r4->serializeKey($zn);
    }
    public function insertSignature($El, $pM = null)
    {
        $yA = $El->ownerDocument;
        $Tt = $yA->importNode($this->sigNode, true);
        if ($pM == null) {
            goto lz;
        }
        return $El->insertBefore($Tt, $pM);
        goto pG;
        lz:
        return $El->insertBefore($Tt);
        pG:
    }
    public function appendSignature($FI, $qF = false)
    {
        $pM = $qF ? $FI->firstChild : null;
        return $this->insertSignature($FI, $pM);
    }
    public static function get509XCert($rG, $vh = true)
    {
        $N2 = self::staticGet509XCerts($rG, $vh);
        if (empty($N2)) {
            goto u8;
        }
        return $N2[0];
        u8:
        return '';
    }
    public static function staticGet509XCerts($N2, $vh = true)
    {
        if ($vh) {
            goto WF;
        }
        return array($N2);
        goto ot;
        WF:
        $ni = '';
        $d9 = array();
        $bF = explode("\12", $N2);
        $I7 = false;
        foreach ($bF as $PF) {
            if (!$I7) {
                goto Gz;
            }
            if (!(strncmp($PF, "\55\55\x2d\x2d\x2d\105\x4e\104\40\103\x45\x52\x54\x49\x46\x49\x43\101\124\x45", 20) == 0)) {
                goto Gh;
            }
            $I7 = false;
            $d9[] = $ni;
            $ni = '';
            goto l4;
            Gh:
            $ni .= trim($PF);
            goto qg;
            Gz:
            if (!(strncmp($PF, "\x2d\55\55\55\x2d\x42\105\x47\x49\116\x20\x43\105\122\x54\x49\x46\x49\103\101\x54\105", 22) == 0)) {
                goto rn;
            }
            $I7 = true;
            rn:
            qg:
            l4:
        }
        DH:
        return $d9;
        ot:
    }
    public static function staticAdd509Cert($nL, $rG, $vh = true, $tu = false, $mS = null, $Eh = null)
    {
        if (!$tu) {
            goto Rn;
        }
        $rG = file_get_contents($rG);
        Rn:
        if ($nL instanceof DOMElement) {
            goto BB;
        }
        throw new Exception("\x49\156\166\x61\x6c\151\x64\40\160\x61\162\x65\x6e\164\x20\x4e\157\144\x65\40\160\x61\x72\x61\x6d\x65\164\145\162");
        BB:
        $LL = $nL->ownerDocument;
        if (!empty($mS)) {
            goto rY;
        }
        $mS = new DOMXPath($nL->ownerDocument);
        $mS->registerNamespace("\163\x65\143\144\163\151\x67", self::XMLDSIGNS);
        rY:
        $rw = "\56\57\x73\x65\x63\144\x73\151\147\72\x4b\145\x79\x49\156\x66\x6f";
        $pA = $mS->query($rw, $nL);
        $NO = $pA->item(0);
        $Qx = '';
        if (!$NO) {
            goto Af;
        }
        $tr = $NO->lookupPrefix(self::XMLDSIGNS);
        if (empty($tr)) {
            goto bx;
        }
        $Qx = $tr . "\x3a";
        bx:
        goto wD;
        Af:
        $tr = $nL->lookupPrefix(self::XMLDSIGNS);
        if (empty($tr)) {
            goto gs;
        }
        $Qx = $tr . "\72";
        gs:
        $kk = false;
        $NO = $LL->createElementNS(self::XMLDSIGNS, $Qx . "\x4b\145\x79\x49\x6e\x66\157");
        $rw = "\x2e\57\x73\x65\x63\x64\163\151\147\72\x4f\142\x6a\x65\143\x74";
        $pA = $mS->query($rw, $nL);
        if (!($BQ = $pA->item(0))) {
            goto mR;
        }
        $BQ->parentNode->insertBefore($NO, $BQ);
        $kk = true;
        mR:
        if ($kk) {
            goto hL;
        }
        $nL->appendChild($NO);
        hL:
        wD:
        $N2 = self::staticGet509XCerts($rG, $vh);
        $yH = $LL->createElementNS(self::XMLDSIGNS, $Qx . "\x58\x35\x30\x39\x44\x61\164\141");
        $NO->appendChild($yH);
        $Up = false;
        $y7 = false;
        if (!is_array($Eh)) {
            goto DT;
        }
        if (empty($Eh["\x69\163\x73\x75\x65\x72\x53\145\x72\x69\141\154"])) {
            goto Kn;
        }
        $Up = true;
        Kn:
        if (empty($Eh["\x73\165\142\x6a\x65\x63\x74\x4e\141\x6d\145"])) {
            goto Hk;
        }
        $y7 = true;
        Hk:
        DT:
        foreach ($N2 as $Xg) {
            if (!($Up || $y7)) {
                goto WA;
            }
            if (!($dL = openssl_x509_parse("\x2d\x2d\55\x2d\55\x42\105\x47\111\116\x20\x43\105\122\x54\x49\106\x49\x43\x41\124\x45\55\55\x2d\x2d\x2d\xa" . chunk_split($Xg, 64, "\xa") . "\x2d\55\55\x2d\x2d\105\116\104\40\x43\x45\122\x54\x49\x46\x49\x43\x41\x54\105\55\x2d\x2d\55\x2d\xa"))) {
                goto jN;
            }
            if (!($y7 && !empty($dL["\x73\165\142\152\x65\x63\164"]))) {
                goto pQ;
            }
            if (is_array($dL["\x73\165\142\x6a\x65\143\164"])) {
                goto H1;
            }
            $nQ = $dL["\151\x73\163\x75\145\x72"];
            goto ki;
            H1:
            $IW = array();
            foreach ($dL["\163\165\142\152\145\143\164"] as $BI => $n2) {
                if (is_array($n2)) {
                    goto Nt;
                }
                array_unshift($IW, "{$BI}\x3d{$n2}");
                goto rs;
                Nt:
                foreach ($n2 as $mx) {
                    array_unshift($IW, "{$BI}\x3d{$mx}");
                    mV:
                }
                Pp:
                rs:
                hh:
            }
            H6:
            $nQ = implode("\54", $IW);
            ki:
            $In = $LL->createElementNS(self::XMLDSIGNS, $Qx . "\130\65\60\71\x53\165\x62\x6a\x65\143\164\116\x61\155\145", $nQ);
            $yH->appendChild($In);
            pQ:
            if (!($Up && !empty($dL["\x69\x73\x73\165\145\x72"]) && !empty($dL["\x73\145\162\x69\141\154\x4e\165\x6d\x62\145\162"]))) {
                goto ks;
            }
            if (is_array($dL["\151\x73\163\x75\145\x72"])) {
                goto Cb;
            }
            $pI = $dL["\x69\x73\x73\165\x65\162"];
            goto b5;
            Cb:
            $IW = array();
            foreach ($dL["\151\163\x73\165\145\x72"] as $BI => $n2) {
                array_unshift($IW, "{$BI}\x3d{$n2}");
                UG:
            }
            Jc:
            $pI = implode("\54", $IW);
            b5:
            $b9 = $LL->createElementNS(self::XMLDSIGNS, $Qx . "\130\x35\x30\x39\111\163\x73\165\x65\162\x53\145\162\x69\141\x6c");
            $yH->appendChild($b9);
            $Y5 = $LL->createElementNS(self::XMLDSIGNS, $Qx . "\x58\65\60\x39\x49\x73\163\x75\x65\162\116\x61\155\145", $pI);
            $b9->appendChild($Y5);
            $Y5 = $LL->createElementNS(self::XMLDSIGNS, $Qx . "\x58\65\60\x39\123\x65\162\151\x61\x6c\116\165\x6d\x62\x65\162", $dL["\163\x65\162\x69\141\154\116\x75\x6d\x62\x65\x72"]);
            $b9->appendChild($Y5);
            ks:
            jN:
            WA:
            $fb = $LL->createElementNS(self::XMLDSIGNS, $Qx . "\130\65\x30\x39\x43\x65\162\x74\x69\x66\x69\x63\141\x74\x65", $Xg);
            $yH->appendChild($fb);
            eH:
        }
        uj:
    }
    public function add509Cert($rG, $vh = true, $tu = false, $Eh = null)
    {
        if (!($mS = $this->getXPathObj())) {
            goto ID;
        }
        self::staticAdd509Cert($this->sigNode, $rG, $vh, $tu, $mS, $Eh);
        ID:
    }
    public function appendToKeyInfo($El)
    {
        $nL = $this->sigNode;
        $LL = $nL->ownerDocument;
        $mS = $this->getXPathObj();
        if (!empty($mS)) {
            goto hn;
        }
        $mS = new DOMXPath($nL->ownerDocument);
        $mS->registerNamespace("\163\145\x63\144\163\x69\147", self::XMLDSIGNS);
        hn:
        $rw = "\x2e\57\163\x65\143\x64\163\151\x67\x3a\113\x65\x79\111\x6e\146\157";
        $pA = $mS->query($rw, $nL);
        $NO = $pA->item(0);
        if ($NO) {
            goto X_;
        }
        $Qx = '';
        $tr = $nL->lookupPrefix(self::XMLDSIGNS);
        if (empty($tr)) {
            goto Wi;
        }
        $Qx = $tr . "\x3a";
        Wi:
        $kk = false;
        $NO = $LL->createElementNS(self::XMLDSIGNS, $Qx . "\x4b\x65\x79\x49\156\146\157");
        $rw = "\56\57\x73\x65\x63\x64\163\151\x67\72\117\x62\x6a\145\x63\x74";
        $pA = $mS->query($rw, $nL);
        if (!($BQ = $pA->item(0))) {
            goto eh;
        }
        $BQ->parentNode->insertBefore($NO, $BQ);
        $kk = true;
        eh:
        if ($kk) {
            goto eW;
        }
        $nL->appendChild($NO);
        eW:
        X_:
        $NO->appendChild($El);
        return $NO;
    }
    public function getValidatedNodes()
    {
        return $this->validatedNodes;
    }
}
class XMLSecEncSAML
{
    const template = "\x3c\170\x65\156\143\72\105\x6e\143\162\171\160\164\145\x64\104\x61\x74\x61\40\170\155\154\x6e\x73\x3a\170\145\156\143\75\x27\x68\x74\x74\x70\72\x2f\x2f\x77\167\x77\56\x77\x33\56\157\162\147\57\62\x30\60\x31\57\60\x34\57\170\x6d\154\145\x6e\143\x23\47\x3e\15\12\x20\40\x20\x3c\170\x65\x6e\x63\72\103\x69\160\x68\x65\x72\104\x61\164\141\76\xd\12\40\40\40\x20\x20\x20\x3c\170\145\156\x63\x3a\x43\x69\160\150\145\x72\126\x61\x6c\165\x65\x3e\x3c\57\170\145\156\143\x3a\103\151\160\150\145\162\126\141\x6c\x75\145\76\15\xa\x20\40\x20\x3c\57\x78\x65\156\x63\72\x43\x69\160\x68\145\x72\104\x61\x74\x61\x3e\xd\12\74\57\x78\145\x6e\x63\72\x45\x6e\143\x72\x79\160\x74\145\x64\x44\141\164\x61\76";
    const Element = "\x68\x74\x74\x70\72\57\x2f\x77\x77\x77\x2e\x77\x33\56\x6f\x72\147\x2f\x32\x30\x30\61\x2f\x30\x34\57\170\x6d\x6c\145\156\143\x23\x45\154\145\x6d\x65\x6e\164";
    const Content = "\x68\164\x74\160\x3a\57\57\x77\167\x77\x2e\x77\x33\x2e\x6f\162\x67\57\x32\60\x30\x31\57\60\x34\57\170\x6d\x6c\x65\x6e\143\x23\103\157\156\x74\x65\x6e\164";
    const URI = 3;
    const XMLENCNS = "\x68\164\164\160\x3a\x2f\x2f\x77\x77\167\x2e\167\63\x2e\x6f\x72\x67\x2f\62\60\60\61\57\x30\64\57\170\155\x6c\x65\156\x63\43";
    private $encdoc = null;
    private $rawNode = null;
    public $type = null;
    public $encKey = null;
    private $references = array();
    public function __construct()
    {
        $this->_resetTemplate();
    }
    private function _resetTemplate()
    {
        $this->encdoc = new DOMDocument();
        $this->encdoc->loadXML(self::template);
    }
    public function addReference($Os, $El, $gv)
    {
        if ($El instanceof DOMNode) {
            goto Tp;
        }
        throw new Exception("\x24\x6e\157\x64\x65\40\151\x73\x20\156\157\x74\x20\157\146\40\164\x79\x70\x65\40\x44\117\x4d\116\157\144\x65");
        Tp:
        $RB = $this->encdoc;
        $this->_resetTemplate();
        $dC = $this->encdoc;
        $this->encdoc = $RB;
        $tC = XMLSecurityDSigSAML::generateGUID();
        $Bu = $dC->documentElement;
        $Bu->setAttribute("\111\x64", $tC);
        $this->references[$Os] = array("\156\x6f\x64\x65" => $El, "\164\171\x70\x65" => $gv, "\145\x6e\x63\156\157\x64\145" => $dC, "\162\145\146\x75\162\x69" => $tC);
    }
    public function setNode($El)
    {
        $this->rawNode = $El;
    }
    public function encryptNode($r4, $C6 = true)
    {
        $ni = '';
        if (!empty($this->rawNode)) {
            goto v3;
        }
        throw new Exception("\x4e\x6f\144\x65\40\164\157\40\x65\156\x63\162\171\x70\x74\x20\x68\x61\x73\40\x6e\x6f\164\40\x62\x65\145\156\40\163\145\x74");
        v3:
        if ($r4 instanceof XMLSecurityKeySAML) {
            goto NS;
        }
        throw new Exception("\111\x6e\x76\x61\154\x69\144\x20\113\145\x79");
        NS:
        $Zw = $this->rawNode->ownerDocument;
        $kY = new DOMXPath($this->encdoc);
        $dQ = $kY->query("\x2f\x78\145\x6e\x63\x3a\x45\156\x63\162\171\x70\x74\x65\x64\104\x61\x74\x61\x2f\x78\x65\x6e\x63\72\x43\x69\160\150\x65\x72\104\x61\x74\141\x2f\x78\145\156\x63\72\x43\x69\x70\x68\x65\162\x56\x61\154\x75\145");
        $XW = $dQ->item(0);
        if (!($XW == null)) {
            goto Vv;
        }
        throw new Exception("\105\162\x72\157\162\40\x6c\157\143\141\164\x69\156\x67\x20\103\x69\160\x68\145\162\x56\141\x6c\165\x65\x20\x65\154\x65\155\145\156\x74\x20\167\151\x74\150\151\x6e\x20\164\145\x6d\x70\154\x61\164\145");
        Vv:
        switch ($this->type) {
            case self::Element:
                $ni = $Zw->saveXML($this->rawNode);
                $this->encdoc->documentElement->setAttribute("\124\x79\160\x65", self::Element);
                goto oW;
            case self::Content:
                $LE = $this->rawNode->childNodes;
                foreach ($LE as $Nk) {
                    $ni .= $Zw->saveXML($Nk);
                    FG:
                }
                kz:
                $this->encdoc->documentElement->setAttribute("\124\x79\x70\x65", self::Content);
                goto oW;
            default:
                throw new Exception("\x54\171\x70\x65\40\x69\x73\x20\143\165\162\162\x65\x6e\164\x6c\x79\40\156\x6f\x74\x20\163\x75\160\x70\x6f\x72\x74\145\x64");
        }
        Vg:
        oW:
        $o_ = $this->encdoc->documentElement->appendChild($this->encdoc->createElementNS(self::XMLENCNS, "\170\x65\x6e\143\x3a\105\156\143\x72\171\x70\164\x69\x6f\x6e\x4d\x65\164\150\157\144"));
        $o_->setAttribute("\x41\154\147\157\x72\x69\164\x68\155", $r4->getAlgorithm());
        $XW->parentNode->parentNode->insertBefore($o_, $XW->parentNode->parentNode->firstChild);
        $yF = base64_encode($r4->encryptData($ni));
        $n2 = $this->encdoc->createTextNode($yF);
        $XW->appendChild($n2);
        if ($C6) {
            goto ul;
        }
        return $this->encdoc->documentElement;
        goto tt;
        ul:
        switch ($this->type) {
            case self::Element:
                if (!($this->rawNode->nodeType == XML_DOCUMENT_NODE)) {
                    goto FS;
                }
                return $this->encdoc;
                FS:
                $PE = $this->rawNode->ownerDocument->importNode($this->encdoc->documentElement, true);
                $this->rawNode->parentNode->replaceChild($PE, $this->rawNode);
                return $PE;
            case self::Content:
                $PE = $this->rawNode->ownerDocument->importNode($this->encdoc->documentElement, true);
                oi:
                if (!$this->rawNode->firstChild) {
                    goto F6;
                }
                $this->rawNode->removeChild($this->rawNode->firstChild);
                goto oi;
                F6:
                $this->rawNode->appendChild($PE);
                return $PE;
        }
        rm:
        K8:
        tt:
    }
    public function encryptReferences($r4)
    {
        $zR = $this->rawNode;
        $yd = $this->type;
        foreach ($this->references as $Os => $ev) {
            $this->encdoc = $ev["\145\x6e\143\156\157\144\x65"];
            $this->rawNode = $ev["\156\157\x64\145"];
            $this->type = $ev["\x74\x79\x70\145"];
            try {
                $tJ = $this->encryptNode($r4);
                $this->references[$Os]["\x65\156\143\156\x6f\x64\x65"] = $tJ;
            } catch (Exception $Um) {
                $this->rawNode = $zR;
                $this->type = $yd;
                throw $Um;
            }
            je:
        }
        xw:
        $this->rawNode = $zR;
        $this->type = $yd;
    }
    public function getCipherValue()
    {
        if (!empty($this->rawNode)) {
            goto Jo;
        }
        throw new Exception("\x4e\157\x64\145\x20\x74\x6f\40\144\x65\x63\x72\171\160\x74\40\150\x61\x73\x20\156\157\x74\x20\x62\145\x65\156\40\163\145\164");
        Jo:
        $Zw = $this->rawNode->ownerDocument;
        $kY = new DOMXPath($Zw);
        $kY->registerNamespace("\170\155\154\x65\x6e\x63\x72", self::XMLENCNS);
        $rw = "\x2e\57\170\155\x6c\x65\x6e\143\x72\72\103\151\x70\150\145\x72\104\141\164\141\57\x78\155\x6c\145\156\x63\162\72\103\x69\x70\x68\x65\162\x56\141\x6c\x75\x65";
        $pA = $kY->query($rw, $this->rawNode);
        $El = $pA->item(0);
        if ($El) {
            goto d3;
        }
        return null;
        d3:
        return base64_decode($El->nodeValue);
    }
    public function decryptNode($r4, $C6 = true)
    {
        if ($r4 instanceof XMLSecurityKeySAML) {
            goto uz;
        }
        throw new Exception("\x49\156\x76\141\x6c\151\144\x20\x4b\x65\x79");
        uz:
        $Jz = $this->getCipherValue();
        if ($Jz) {
            goto UD;
        }
        throw new Exception("\103\141\156\156\157\164\x20\154\157\143\141\164\x65\40\x65\x6e\x63\162\171\x70\164\145\144\40\144\141\164\141");
        goto D2;
        UD:
        $N5 = $r4->decryptData($Jz);
        if ($C6) {
            goto bA;
        }
        return $N5;
        goto kW;
        bA:
        switch ($this->type) {
            case self::Element:
                $kf = new DOMDocument();
                $kf->loadXML($N5);
                if (!($this->rawNode->nodeType == XML_DOCUMENT_NODE)) {
                    goto it;
                }
                return $kf;
                it:
                $PE = $this->rawNode->ownerDocument->importNode($kf->documentElement, true);
                $this->rawNode->parentNode->replaceChild($PE, $this->rawNode);
                return $PE;
            case self::Content:
                if ($this->rawNode->nodeType == XML_DOCUMENT_NODE) {
                    goto ko;
                }
                $Zw = $this->rawNode->ownerDocument;
                goto kR;
                ko:
                $Zw = $this->rawNode;
                kR:
                $eS = $Zw->createDocumentFragment();
                $eS->appendXML($N5);
                $zn = $this->rawNode->parentNode;
                $zn->replaceChild($eS, $this->rawNode);
                return $zn;
            default:
                return $N5;
        }
        CS:
        ht:
        kW:
        D2:
    }
    public function encryptKey($cf, $ny, $xl = true)
    {
        if (!(!$cf instanceof XMLSecurityKeySAML || !$ny instanceof XMLSecurityKeySAML)) {
            goto nD;
        }
        throw new Exception("\111\156\x76\x61\154\151\x64\40\x4b\145\x79");
        nD:
        $IY = base64_encode($cf->encryptData($ny->key));
        $nd = $this->encdoc->documentElement;
        $wU = $this->encdoc->createElementNS(self::XMLENCNS, "\170\x65\156\x63\x3a\x45\156\143\x72\171\x70\x74\x65\144\x4b\x65\171");
        if ($xl) {
            goto CN;
        }
        $this->encKey = $wU;
        goto rg;
        CN:
        $NO = $nd->insertBefore($this->encdoc->createElementNS("\x68\x74\x74\x70\72\57\x2f\167\167\x77\x2e\x77\63\x2e\157\x72\x67\57\62\x30\60\x30\x2f\x30\x39\x2f\170\155\x6c\x64\163\x69\147\x23", "\x64\163\x69\x67\x3a\113\x65\x79\111\156\146\157"), $nd->firstChild);
        $NO->appendChild($wU);
        rg:
        $o_ = $wU->appendChild($this->encdoc->createElementNS(self::XMLENCNS, "\170\145\x6e\x63\x3a\x45\156\x63\162\x79\x70\x74\x69\157\x6e\x4d\x65\164\150\x6f\144"));
        $o_->setAttribute("\x41\x6c\147\157\162\x69\x74\x68\x6d", $cf->getAlgorith());
        if (empty($cf->name)) {
            goto cM;
        }
        $NO = $wU->appendChild($this->encdoc->createElementNS("\x68\164\164\160\x3a\x2f\57\x77\x77\x77\x2e\x77\63\56\157\x72\147\57\x32\60\60\x30\x2f\60\x39\57\170\155\154\x64\163\151\x67\43", "\x64\163\151\147\72\x4b\x65\171\x49\x6e\x66\x6f"));
        $NO->appendChild($this->encdoc->createElementNS("\150\164\164\160\72\57\57\167\x77\167\56\x77\63\x2e\157\162\147\x2f\62\x30\x30\60\57\x30\71\57\x78\155\x6c\x64\x73\151\x67\43", "\144\163\x69\x67\72\x4b\x65\x79\116\141\x6d\145", $cf->name));
        cM:
        $j5 = $wU->appendChild($this->encdoc->createElementNS(self::XMLENCNS, "\x78\145\156\x63\x3a\103\151\160\150\x65\x72\104\141\164\141"));
        $j5->appendChild($this->encdoc->createElementNS(self::XMLENCNS, "\x78\x65\156\x63\72\103\151\x70\150\x65\162\126\141\154\x75\145", $IY));
        if (!(is_array($this->references) && count($this->references) > 0)) {
            goto Jf;
        }
        $MW = $wU->appendChild($this->encdoc->createElementNS(self::XMLENCNS, "\x78\x65\156\143\x3a\122\x65\146\x65\x72\x65\x6e\143\145\114\151\x73\164"));
        foreach ($this->references as $Os => $ev) {
            $tC = $ev["\162\145\146\165\162\151"];
            $wZ = $MW->appendChild($this->encdoc->createElementNS(self::XMLENCNS, "\x78\145\x6e\x63\x3a\104\x61\164\x61\x52\145\146\145\x72\145\156\x63\x65"));
            $wZ->setAttribute("\x55\x52\111", "\43" . $tC);
            cq:
        }
        RL:
        Jf:
        return;
    }
    public function decryptKey($wU)
    {
        if ($wU->isEncrypted) {
            goto s4;
        }
        throw new Exception("\113\145\x79\x20\x69\163\x20\x6e\x6f\164\40\x45\x6e\x63\x72\x79\x70\164\145\x64");
        s4:
        if (!empty($wU->key)) {
            goto tx;
        }
        throw new Exception("\113\145\171\40\x69\x73\x20\155\151\163\x73\151\156\x67\x20\x64\x61\164\141\40\x74\157\40\160\x65\x72\x66\x6f\x72\155\x20\x74\150\x65\40\x64\145\x63\162\171\160\x74\x69\157\156");
        tx:
        return $this->decryptNode($wU, false);
    }
    public function locateEncryptedData($Bu)
    {
        if ($Bu instanceof DOMDocument) {
            goto og;
        }
        $Zw = $Bu->ownerDocument;
        goto lu;
        og:
        $Zw = $Bu;
        lu:
        if (!$Zw) {
            goto xK;
        }
        $mS = new DOMXPath($Zw);
        $rw = "\x2f\x2f\x2a\133\154\x6f\x63\141\x6c\x2d\x6e\x61\x6d\x65\x28\51\x3d\x27\105\156\x63\x72\171\x70\x74\145\x64\x44\x61\x74\141\47\40\x61\156\144\40\x6e\x61\155\x65\x73\160\x61\x63\x65\x2d\x75\162\151\x28\51\x3d\47" . self::XMLENCNS . "\47\135";
        $pA = $mS->query($rw);
        return $pA->item(0);
        xK:
        return null;
    }
    public function locateKey($El = null)
    {
        if (!empty($El)) {
            goto OJ;
        }
        $El = $this->rawNode;
        OJ:
        if ($El instanceof DOMNode) {
            goto gj;
        }
        return null;
        gj:
        if (!($Zw = $El->ownerDocument)) {
            goto yM;
        }
        $mS = new DOMXPath($Zw);
        $mS->registerNamespace("\130\115\x4c\x53\145\143\x45\x6e\143\x53\x41\x4d\x4c", self::XMLENCNS);
        $rw = "\56\x2f\57\130\115\x4c\x53\x65\143\x45\156\143\123\x41\x4d\x4c\x3a\x45\x6e\143\x72\x79\x70\164\151\157\156\x4d\145\164\150\157\x64";
        $pA = $mS->query($rw, $El);
        if (!($m2 = $pA->item(0))) {
            goto Uw;
        }
        $uf = $m2->getAttribute("\101\x6c\x67\157\162\151\x74\x68\155");
        try {
            $r4 = new XMLSecurityKeySAML($uf, array("\164\171\x70\x65" => "\160\x72\151\166\141\x74\x65"));
        } catch (Exception $Um) {
            return null;
        }
        return $r4;
        Uw:
        yM:
        return null;
    }
    public static function staticLocateKeyInfo($jk = null, $El = null)
    {
        if (!(empty($El) || !$El instanceof DOMNode)) {
            goto cW;
        }
        return null;
        cW:
        $Zw = $El->ownerDocument;
        if ($Zw) {
            goto Lm;
        }
        return null;
        Lm:
        $mS = new DOMXPath($Zw);
        $mS->registerNamespace("\x58\x4d\114\123\x65\x63\x45\156\143\x53\x41\x4d\x4c", self::XMLENCNS);
        $mS->registerNamespace("\x78\x6d\x6c\163\145\143\x64\x73\151\x67", XMLSecurityDSigSAML::XMLDSIGNS);
        $rw = "\56\57\170\x6d\x6c\x73\145\x63\144\163\151\x67\x3a\x4b\x65\171\x49\x6e\x66\157";
        $pA = $mS->query($rw, $El);
        $m2 = $pA->item(0);
        if ($m2) {
            goto M9;
        }
        return $jk;
        M9:
        foreach ($m2->childNodes as $Nk) {
            switch ($Nk->localName) {
                case "\x4b\145\171\x4e\x61\x6d\x65":
                    if (empty($jk)) {
                        goto Wt;
                    }
                    $jk->name = $Nk->nodeValue;
                    Wt:
                    goto Ri;
                case "\113\x65\x79\126\x61\154\165\145":
                    foreach ($Nk->childNodes as $yK) {
                        switch ($yK->localName) {
                            case "\x44\x53\x41\x4b\145\171\x56\x61\x6c\165\x65":
                                throw new Exception("\x44\x53\101\x4b\145\171\126\x61\154\x75\x65\40\x63\x75\x72\162\145\x6e\164\154\171\x20\x6e\x6f\x74\40\163\165\x70\160\x6f\162\x74\145\x64");
                            case "\122\x53\101\113\x65\171\126\141\154\x75\x65":
                                $gf = null;
                                $IM = null;
                                if (!($Of = $yK->getElementsByTagName("\x4d\157\x64\165\154\165\x73")->item(0))) {
                                    goto Pq;
                                }
                                $gf = base64_decode($Of->nodeValue);
                                Pq:
                                if (!($rb = $yK->getElementsByTagName("\x45\x78\160\157\156\145\x6e\x74")->item(0))) {
                                    goto oF;
                                }
                                $IM = base64_decode($rb->nodeValue);
                                oF:
                                if (!(empty($gf) || empty($IM))) {
                                    goto zp;
                                }
                                throw new Exception("\115\x69\163\x73\x69\156\x67\40\115\x6f\x64\165\x6c\x75\163\x20\x6f\x72\40\x45\x78\x70\x6f\156\x65\x6e\164");
                                zp:
                                $Ee = XMLSecurityKeySAML::convertRSA($gf, $IM);
                                $jk->loadKey($Ee);
                                goto mb;
                        }
                        C0:
                        mb:
                        nn:
                    }
                    Nz:
                    goto Ri;
                case "\122\145\x74\162\151\145\x76\x61\x6c\x4d\x65\164\150\x6f\x64":
                    $gv = $Nk->getAttribute("\x54\x79\160\145");
                    if (!($gv !== "\x68\x74\x74\x70\x3a\57\57\x77\x77\x77\56\x77\x33\56\x6f\162\x67\57\62\60\x30\x31\57\60\x34\57\x78\155\x6c\x65\156\143\x23\105\156\x63\x72\x79\x70\164\145\144\x4b\145\171")) {
                        goto uv;
                    }
                    goto Ri;
                    uv:
                    $OW = $Nk->getAttribute("\x55\122\111");
                    if (!($OW[0] !== "\x23")) {
                        goto vI;
                    }
                    goto Ri;
                    vI:
                    $sP = substr($OW, 1);
                    $rw = "\57\57\x58\x4d\114\123\x65\143\105\x6e\143\x53\101\x4d\114\72\105\x6e\143\162\171\160\164\x65\x64\x4b\x65\171\x5b\x40\111\144\75\x27{$sP}\x27\x5d";
                    $wz = $mS->query($rw)->item(0);
                    if ($wz) {
                        goto bN;
                    }
                    throw new Exception("\x55\156\x61\142\x6c\145\40\x74\157\x20\x6c\x6f\143\141\164\145\x20\x45\x6e\143\162\171\x70\x74\x65\144\x4b\145\171\40\x77\x69\x74\x68\40\x40\111\144\75\x27{$sP}\47\x2e");
                    bN:
                    return XMLSecurityKeySAML::fromEncryptedKeyElement($wz);
                case "\105\x6e\143\162\x79\x70\164\x65\x64\113\x65\x79":
                    return XMLSecurityKeySAML::fromEncryptedKeyElement($Nk);
                case "\x58\65\x30\x39\x44\x61\x74\141":
                    if (!($PS = $Nk->getElementsByTagName("\130\65\x30\x39\x43\145\x72\164\151\x66\x69\143\x61\x74\145"))) {
                        goto nH;
                    }
                    if (!($PS->length > 0)) {
                        goto qx;
                    }
                    $na = $PS->item(0)->textContent;
                    $na = str_replace(array("\xd", "\xa", "\40"), '', $na);
                    $na = "\x2d\x2d\x2d\x2d\55\102\105\x47\x49\116\x20\103\105\x52\x54\x49\x46\x49\x43\101\x54\105\x2d\55\55\x2d\x2d\xa" . chunk_split($na, 64, "\12") . "\x2d\x2d\x2d\x2d\55\x45\116\x44\40\103\x45\x52\124\x49\x46\x49\103\101\124\105\55\55\x2d\x2d\55\12";
                    $jk->loadKey($na, false, true);
                    qx:
                    nH:
                    goto Ri;
            }
            PP:
            Ri:
            D5:
        }
        VO:
        return $jk;
    }
    public function locateKeyInfo($jk = null, $El = null)
    {
        if (!empty($El)) {
            goto op;
        }
        $El = $this->rawNode;
        op:
        return self::staticLocateKeyInfo($jk, $El);
    }
}
