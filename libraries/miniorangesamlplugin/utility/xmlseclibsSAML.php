<?php


class XMLSecurityKeySAML
{
    const TRIPLEDES_CBC = "\x68\x74\164\160\x3a\x2f\x2f\167\x77\x77\56\167\63\x2e\x6f\x72\x67\x2f\x32\60\60\61\x2f\x30\64\x2f\170\155\154\145\156\143\x23\164\162\x69\x70\x6c\x65\144\x65\163\55\x63\142\143";
    const AES128_CBC = "\150\x74\164\x70\x3a\x2f\x2f\167\167\x77\56\x77\x33\56\157\x72\147\57\62\x30\60\61\x2f\60\x34\x2f\x78\x6d\x6c\145\156\x63\43\x61\x65\163\61\x32\70\x2d\143\142\x63";
    const AES192_CBC = "\150\x74\x74\160\x3a\57\x2f\x77\x77\x77\56\x77\x33\56\x6f\x72\147\57\x32\x30\x30\61\57\60\x34\57\170\155\154\x65\x6e\143\43\141\x65\x73\61\71\x32\x2d\x63\142\143";
    const AES256_CBC = "\x68\164\164\x70\72\x2f\x2f\x77\x77\x77\x2e\x77\x33\56\157\162\147\57\x32\60\60\x31\57\60\64\x2f\170\155\x6c\145\x6e\x63\x23\x61\x65\x73\x32\65\66\55\143\142\143";
    const RSA_1_5 = "\150\x74\164\160\x3a\x2f\57\x77\x77\167\x2e\167\x33\x2e\157\x72\147\57\x32\x30\60\61\57\x30\64\x2f\x78\155\154\x65\156\x63\43\x72\163\141\55\x31\137\x35";
    const RSA_OAEP_MGF1P = "\150\x74\x74\160\x3a\x2f\x2f\x77\x77\x77\56\167\x33\56\157\162\x67\x2f\62\60\x30\x31\x2f\60\64\x2f\170\155\154\145\156\143\x23\x72\163\141\x2d\157\141\x65\160\x2d\155\147\146\61\160";
    const DSA_SHA1 = "\150\x74\x74\160\72\x2f\57\167\167\167\56\167\63\x2e\157\x72\147\x2f\62\60\x30\60\x2f\x30\x39\x2f\x78\x6d\154\x64\x73\151\x67\43\x64\163\x61\x2d\x73\150\141\x31";
    const RSA_SHA1 = "\150\164\x74\x70\72\x2f\57\x77\x77\x77\x2e\x77\x33\x2e\x6f\x72\x67\x2f\x32\x30\60\x30\x2f\60\x39\57\170\155\x6c\144\x73\x69\x67\43\162\163\x61\55\163\x68\x61\x31";
    const RSA_SHA256 = "\150\164\164\160\72\57\x2f\167\x77\167\x2e\167\63\56\x6f\x72\x67\57\62\60\x30\61\57\x30\64\57\170\155\x6c\144\163\x69\x67\55\x6d\157\x72\145\43\x72\163\141\x2d\x73\150\x61\x32\x35\66";
    const RSA_SHA384 = "\150\x74\164\160\72\x2f\x2f\x77\167\167\x2e\x77\x33\x2e\157\162\147\57\x32\60\x30\x31\57\x30\x34\x2f\170\x6d\x6c\144\x73\151\147\55\155\157\162\145\x23\162\163\141\x2d\x73\150\141\x33\70\x34";
    const RSA_SHA512 = "\150\x74\164\x70\72\57\57\167\167\x77\x2e\167\x33\56\157\x72\x67\x2f\62\60\x30\61\57\60\x34\x2f\x78\155\154\x64\163\151\147\55\x6d\157\162\145\43\162\163\141\x2d\163\150\141\x35\x31\x32";
    const HMAC_SHA1 = "\150\x74\x74\160\72\x2f\57\x77\x77\167\56\167\63\x2e\x6f\x72\147\57\x32\60\60\60\x2f\60\71\57\170\x6d\154\144\x73\151\x67\43\150\x6d\x61\x63\x2d\x73\150\x61\61";
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
    public function __construct($vk, $tG = null)
    {
        switch ($vk) {
            case self::TRIPLEDES_CBC:
                $this->cryptParams["\x6c\x69\x62\x72\141\x72\x79"] = "\x6f\160\x65\156\163\x73\154";
                $this->cryptParams["\x63\151\x70\150\x65\x72"] = "\x64\x65\163\x2d\x65\144\x65\x33\x2d\x63\x62\143";
                $this->cryptParams["\164\171\160\145"] = "\x73\171\x6d\x6d\x65\x74\162\151\x63";
                $this->cryptParams["\x6d\145\164\150\157\144"] = "\x68\164\164\160\x3a\57\57\167\167\167\x2e\x77\x33\x2e\157\162\147\57\x32\60\60\61\57\60\64\x2f\170\x6d\154\x65\x6e\143\43\164\162\151\160\154\x65\144\x65\163\55\143\142\x63";
                $this->cryptParams["\153\x65\171\x73\151\x7a\x65"] = 24;
                $this->cryptParams["\142\154\157\x63\153\x73\151\x7a\145"] = 8;
                goto ix;
            case self::AES128_CBC:
                $this->cryptParams["\x6c\x69\x62\162\x61\162\171"] = "\157\x70\145\x6e\163\163\x6c";
                $this->cryptParams["\143\x69\160\x68\x65\x72"] = "\x61\x65\163\x2d\x31\62\x38\55\x63\142\x63";
                $this->cryptParams["\164\171\x70\145"] = "\x73\171\x6d\155\x65\x74\162\151\143";
                $this->cryptParams["\x6d\145\x74\150\157\144"] = "\x68\x74\164\160\x3a\57\57\x77\x77\167\x2e\x77\63\56\x6f\x72\147\x2f\x32\60\60\x31\x2f\x30\64\x2f\170\x6d\154\145\x6e\143\43\141\145\163\x31\x32\x38\x2d\x63\x62\x63";
                $this->cryptParams["\x6b\x65\x79\163\151\172\145"] = 16;
                $this->cryptParams["\x62\x6c\x6f\x63\x6b\x73\x69\x7a\145"] = 16;
                goto ix;
            case self::AES192_CBC:
                $this->cryptParams["\x6c\151\x62\162\141\162\x79"] = "\157\160\x65\x6e\x73\x73\154";
                $this->cryptParams["\x63\x69\160\x68\x65\162"] = "\141\145\x73\55\x31\71\62\55\x63\x62\x63";
                $this->cryptParams["\x74\x79\160\x65"] = "\163\171\x6d\x6d\145\164\162\151\x63";
                $this->cryptParams["\155\x65\164\150\157\144"] = "\x68\x74\164\160\x3a\x2f\x2f\x77\x77\x77\56\x77\63\56\157\162\147\57\x32\60\60\61\57\x30\x34\57\x78\155\x6c\x65\156\x63\43\x61\x65\x73\61\x39\62\55\143\142\x63";
                $this->cryptParams["\x6b\x65\171\163\151\x7a\145"] = 24;
                $this->cryptParams["\142\x6c\x6f\143\x6b\x73\151\x7a\145"] = 16;
                goto ix;
            case self::AES256_CBC:
                $this->cryptParams["\154\151\142\x72\141\x72\171"] = "\x6f\160\145\156\x73\x73\154";
                $this->cryptParams["\x63\151\x70\x68\x65\x72"] = "\x61\145\x73\x2d\62\x35\x36\x2d\x63\x62\143";
                $this->cryptParams["\x74\x79\x70\145"] = "\163\x79\x6d\x6d\145\164\162\x69\x63";
                $this->cryptParams["\155\x65\164\x68\x6f\144"] = "\x68\164\x74\160\x3a\x2f\x2f\x77\x77\167\x2e\x77\x33\56\157\x72\147\x2f\x32\60\60\61\x2f\60\64\57\170\x6d\x6c\x65\x6e\143\43\x61\x65\163\62\x35\66\x2d\x63\142\143";
                $this->cryptParams["\153\x65\171\163\x69\x7a\x65"] = 32;
                $this->cryptParams["\142\154\x6f\143\x6b\163\x69\x7a\145"] = 16;
                goto ix;
            case self::RSA_1_5:
                $this->cryptParams["\x6c\x69\142\162\x61\162\171"] = "\157\160\145\x6e\x73\163\x6c";
                $this->cryptParams["\160\x61\x64\144\151\156\x67"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\155\x65\x74\150\157\x64"] = "\x68\x74\x74\160\72\x2f\x2f\167\x77\167\x2e\167\x33\56\157\x72\x67\57\x32\x30\60\x31\x2f\60\x34\57\170\155\154\x65\x6e\143\43\162\x73\141\55\x31\x5f\x35";
                if (!(is_array($tG) && !empty($tG["\x74\x79\x70\x65"]))) {
                    goto S3;
                }
                if (!($tG["\x74\x79\x70\x65"] == "\160\165\x62\154\151\x63" || $tG["\x74\x79\x70\x65"] == "\x70\162\x69\x76\141\x74\145")) {
                    goto rg;
                }
                $this->cryptParams["\164\x79\x70\145"] = $tG["\x74\x79\x70\145"];
                goto ix;
                rg:
                S3:
                throw new Exception("\x43\x65\x72\x74\x69\146\151\143\141\164\x65\40\x22\x74\171\160\x65\x22\40\50\160\x72\x69\166\141\x74\x65\x2f\x70\x75\142\x6c\x69\143\x29\40\x6d\x75\163\x74\x20\142\145\40\x70\141\163\x73\x65\x64\40\166\x69\141\40\x70\x61\162\141\155\145\x74\x65\162\x73");
            case self::RSA_OAEP_MGF1P:
                $this->cryptParams["\x6c\151\x62\162\x61\162\x79"] = "\157\160\145\x6e\x73\x73\x6c";
                $this->cryptParams["\160\x61\x64\x64\151\156\147"] = OPENSSL_PKCS1_OAEP_PADDING;
                $this->cryptParams["\x6d\x65\x74\x68\x6f\x64"] = "\150\164\164\160\x3a\57\57\167\x77\x77\x2e\167\63\x2e\x6f\162\x67\x2f\62\x30\60\x31\57\60\x34\57\x78\x6d\154\145\x6e\x63\x23\162\163\x61\x2d\x6f\x61\x65\160\x2d\x6d\147\x66\x31\160";
                $this->cryptParams["\150\141\163\150"] = null;
                if (!(is_array($tG) && !empty($tG["\x74\x79\x70\145"]))) {
                    goto G6;
                }
                if (!($tG["\164\x79\x70\145"] == "\x70\x75\x62\x6c\151\143" || $tG["\164\171\x70\x65"] == "\160\x72\151\x76\x61\x74\x65")) {
                    goto fB;
                }
                $this->cryptParams["\164\171\x70\145"] = $tG["\x74\x79\160\x65"];
                goto ix;
                fB:
                G6:
                throw new Exception("\x43\x65\x72\x74\151\146\151\143\141\x74\145\x20\42\164\171\160\x65\42\40\50\x70\162\151\x76\141\x74\x65\57\x70\x75\x62\x6c\x69\143\x29\x20\x6d\165\163\x74\40\x62\x65\x20\160\141\163\x73\x65\144\x20\166\x69\x61\x20\x70\x61\x72\141\x6d\145\164\x65\162\163");
            case self::RSA_SHA1:
                $this->cryptParams["\x6c\151\142\162\x61\x72\171"] = "\x6f\x70\145\156\163\x73\x6c";
                $this->cryptParams["\155\145\x74\x68\x6f\144"] = "\x68\164\164\160\x3a\x2f\57\x77\x77\x77\x2e\x77\x33\x2e\157\x72\147\x2f\62\60\60\60\x2f\x30\71\x2f\170\155\x6c\x64\163\x69\147\43\162\163\x61\55\163\150\x61\61";
                $this->cryptParams["\160\141\x64\144\x69\x6e\147"] = OPENSSL_PKCS1_PADDING;
                if (!(is_array($tG) && !empty($tG["\164\x79\x70\x65"]))) {
                    goto kj;
                }
                if (!($tG["\164\171\x70\x65"] == "\x70\165\x62\154\151\143" || $tG["\x74\x79\160\x65"] == "\x70\x72\x69\x76\x61\164\145")) {
                    goto hr;
                }
                $this->cryptParams["\x74\x79\160\x65"] = $tG["\x74\x79\x70\x65"];
                goto ix;
                hr:
                kj:
                throw new Exception("\103\145\x72\164\x69\x66\x69\x63\x61\164\x65\x20\x22\x74\171\x70\145\x22\40\50\x70\162\151\x76\x61\164\145\x2f\160\165\142\x6c\x69\x63\51\40\x6d\x75\163\164\40\x62\x65\40\x70\141\x73\163\x65\144\x20\x76\151\x61\x20\x70\x61\x72\141\155\145\164\145\x72\x73");
            case self::RSA_SHA256:
                $this->cryptParams["\154\151\142\162\141\x72\x79"] = "\x6f\160\x65\x6e\x73\163\x6c";
                $this->cryptParams["\x6d\145\x74\150\157\x64"] = "\x68\164\164\x70\x3a\x2f\x2f\x77\x77\x77\56\167\x33\56\x6f\x72\x67\x2f\x32\x30\x30\x31\x2f\x30\x34\x2f\170\x6d\154\144\163\x69\147\x2d\x6d\x6f\162\145\x23\162\163\141\55\163\150\141\62\x35\x36";
                $this->cryptParams["\160\141\144\x64\x69\156\147"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\x64\151\147\x65\163\164"] = "\x53\x48\101\x32\65\66";
                if (!(is_array($tG) && !empty($tG["\x74\171\160\x65"]))) {
                    goto pm;
                }
                if (!($tG["\x74\x79\160\x65"] == "\x70\165\142\154\x69\143" || $tG["\164\171\x70\145"] == "\x70\x72\151\x76\141\x74\145")) {
                    goto dt;
                }
                $this->cryptParams["\164\x79\x70\145"] = $tG["\164\171\x70\145"];
                goto ix;
                dt:
                pm:
                throw new Exception("\x43\145\162\x74\x69\x66\151\x63\x61\164\145\40\x22\x74\171\x70\145\42\x20\50\x70\x72\151\x76\x61\164\145\57\160\165\142\x6c\x69\x63\x29\x20\155\165\x73\164\x20\x62\x65\40\160\x61\163\x73\x65\144\x20\166\x69\x61\40\x70\141\x72\141\x6d\x65\x74\x65\162\163");
            case self::RSA_SHA384:
                $this->cryptParams["\154\x69\x62\x72\x61\x72\171"] = "\157\x70\x65\x6e\x73\x73\x6c";
                $this->cryptParams["\x6d\x65\x74\x68\x6f\x64"] = "\x68\164\164\x70\x3a\57\57\x77\x77\x77\56\x77\63\56\157\x72\147\57\62\60\60\61\x2f\60\x34\57\170\x6d\154\144\x73\151\147\55\x6d\x6f\162\x65\43\162\163\x61\55\163\150\141\x33\70\x34";
                $this->cryptParams["\160\x61\144\x64\x69\156\x67"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\144\151\x67\x65\163\164"] = "\123\110\x41\63\70\64";
                if (!(is_array($tG) && !empty($tG["\164\x79\x70\x65"]))) {
                    goto Wo;
                }
                if (!($tG["\164\171\160\145"] == "\x70\165\142\x6c\x69\143" || $tG["\164\171\x70\145"] == "\x70\162\x69\166\x61\x74\x65")) {
                    goto Nz;
                }
                $this->cryptParams["\164\171\x70\145"] = $tG["\164\171\x70\x65"];
                goto ix;
                Nz:
                Wo:
                throw new Exception("\x43\145\162\x74\x69\146\151\x63\x61\164\x65\x20\x22\164\x79\160\145\x22\x20\x28\160\162\151\166\141\164\145\57\160\x75\142\x6c\x69\x63\51\x20\x6d\x75\x73\164\x20\142\x65\40\160\141\x73\163\145\144\40\166\x69\x61\40\x70\141\x72\141\x6d\145\x74\x65\x72\x73");
            case self::RSA_SHA512:
                $this->cryptParams["\154\x69\142\x72\x61\162\x79"] = "\157\160\145\x6e\163\163\154";
                $this->cryptParams["\x6d\145\164\150\157\144"] = "\x68\164\164\x70\72\x2f\57\167\x77\167\56\x77\x33\56\x6f\x72\147\57\62\60\60\x31\57\x30\64\57\x78\155\154\144\x73\151\147\55\x6d\157\x72\145\43\x72\163\x61\x2d\x73\150\141\x35\x31\x32";
                $this->cryptParams["\x70\141\x64\144\x69\x6e\x67"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\144\151\x67\145\163\164"] = "\123\110\101\x35\x31\x32";
                if (!(is_array($tG) && !empty($tG["\164\x79\160\145"]))) {
                    goto lg;
                }
                if (!($tG["\x74\171\160\x65"] == "\x70\x75\142\154\151\x63" || $tG["\x74\x79\x70\145"] == "\160\x72\151\x76\x61\164\x65")) {
                    goto hp;
                }
                $this->cryptParams["\x74\x79\160\x65"] = $tG["\164\x79\160\x65"];
                goto ix;
                hp:
                lg:
                throw new Exception("\103\x65\162\164\151\146\151\143\x61\164\x65\x20\x22\x74\171\x70\x65\42\40\x28\x70\x72\x69\x76\141\164\x65\x2f\x70\x75\142\154\151\143\x29\x20\x6d\x75\163\x74\x20\142\145\40\160\x61\163\163\145\x64\x20\166\x69\x61\40\160\141\x72\x61\x6d\145\x74\x65\x72\x73");
            case self::HMAC_SHA1:
                $this->cryptParams["\x6c\x69\142\x72\x61\162\171"] = $vk;
                $this->cryptParams["\x6d\x65\x74\150\x6f\144"] = "\150\164\164\160\x3a\57\x2f\x77\x77\x77\x2e\167\63\x2e\x6f\162\x67\x2f\62\60\x30\60\57\60\x39\x2f\x78\155\x6c\x64\x73\151\x67\43\x68\155\x61\143\55\x73\150\x61\61";
                goto ix;
            default:
                throw new Exception("\111\156\166\x61\x6c\x69\x64\x20\x4b\145\171\x20\x54\171\160\x65");
        }
        h5:
        ix:
        $this->type = $vk;
    }
    public function getSymmetricKeySize()
    {
        if (isset($this->cryptParams["\x6b\145\x79\x73\151\172\145"])) {
            goto jK;
        }
        return null;
        jK:
        return $this->cryptParams["\x6b\145\x79\x73\x69\x7a\145"];
    }
    public function generateSessionKey()
    {
        if (isset($this->cryptParams["\153\x65\171\x73\151\x7a\x65"])) {
            goto YB;
        }
        throw new Exception("\x55\156\x6b\156\x6f\x77\x6e\x20\153\145\x79\40\x73\x69\172\x65\40\x66\x6f\162\x20\x74\x79\x70\145\x20\42" . $this->type . "\x22\x2e");
        YB:
        $VX = $this->cryptParams["\153\145\171\x73\151\172\x65"];
        $uf = openssl_random_pseudo_bytes($VX);
        if (!($this->type === self::TRIPLEDES_CBC)) {
            goto q8;
        }
        $gL = 0;
        T3:
        if (!($gL < strlen($uf))) {
            goto ve;
        }
        $l0 = ord($uf[$gL]) & 0xfe;
        $OQ = 1;
        $nq = 1;
        tk:
        if (!($nq < 8)) {
            goto Tf;
        }
        $OQ ^= $l0 >> $nq & 1;
        L_:
        $nq++;
        goto tk;
        Tf:
        $l0 |= $OQ;
        $uf[$gL] = chr($l0);
        ta:
        $gL++;
        goto T3;
        ve:
        q8:
        $this->key = $uf;
        return $uf;
    }
    public static function getRawThumbprint($TU)
    {
        $eX = explode("\12", $TU);
        $lp = '';
        $W1 = false;
        foreach ($eX as $E0) {
            if (!$W1) {
                goto iA;
            }
            if (!(strncmp($E0, "\55\55\55\x2d\55\x45\x4e\x44\x20\x43\x45\122\124\x49\x46\x49\103\x41\x54\x45", 20) == 0)) {
                goto xc;
            }
            goto y_;
            xc:
            $lp .= trim($E0);
            goto oL;
            iA:
            if (!(strncmp($E0, "\55\55\55\55\55\x42\105\107\x49\116\x20\x43\105\122\124\x49\106\x49\103\x41\124\x45", 22) == 0)) {
                goto S_;
            }
            $W1 = true;
            S_:
            oL:
            GX:
        }
        y_:
        if (empty($lp)) {
            goto n3;
        }
        return strtolower(sha1(base64_decode($lp)));
        n3:
        return null;
    }
    public function loadKey($uf, $e4 = false, $zV = false)
    {
        if ($e4) {
            goto Yk;
        }
        $this->key = $uf;
        goto W5;
        Yk:
        $this->key = file_get_contents($uf);
        W5:
        if ($zV) {
            goto mP;
        }
        $this->x509Certificate = null;
        goto yu;
        mP:
        $this->key = openssl_x509_read($this->key);
        openssl_x509_export($this->key, $R6);
        $this->x509Certificate = $R6;
        $this->key = $R6;
        yu:
        if (!($this->cryptParams["\x6c\151\142\162\x61\x72\x79"] == "\x6f\x70\x65\x6e\163\x73\154")) {
            goto MB;
        }
        switch ($this->cryptParams["\164\171\x70\x65"]) {
            case "\160\165\142\154\x69\x63":
                if (!$zV) {
                    goto f5;
                }
                $this->X509Thumbprint = self::getRawThumbprint($this->key);
                f5:
                $this->key = openssl_get_publickey($this->key);
                if ($this->key) {
                    goto Jp;
                }
                throw new Exception("\125\x6e\x61\x62\154\x65\40\164\157\40\145\170\x74\162\141\143\164\x20\x70\x75\x62\x6c\x69\x63\40\153\x65\x79");
                Jp:
                goto s5;
            case "\160\x72\x69\166\141\164\x65":
                $this->key = openssl_get_privatekey($this->key, $this->passphrase);
                goto s5;
            case "\x73\x79\155\155\145\164\x72\151\x63":
                if (!(strlen($this->key) < $this->cryptParams["\x6b\x65\x79\x73\x69\x7a\145"])) {
                    goto Mt;
                }
                throw new Exception("\113\x65\171\x20\155\x75\x73\x74\x20\143\157\x6e\164\141\151\156\x20\x61\164\40\154\x65\141\x73\x74\x20\x32\65\40\143\x68\141\162\x61\x63\164\x65\162\163\40\146\157\x72\40\164\150\151\163\40\x63\151\160\150\x65\x72");
                Mt:
                goto s5;
            default:
                throw new Exception("\x55\x6e\153\156\x6f\x77\156\x20\164\171\x70\x65");
        }
        Sh:
        s5:
        MB:
    }
    private function padISO10126($lp, $bo)
    {
        if (!($bo > 256)) {
            goto xH;
        }
        throw new Exception("\102\154\157\143\153\x20\x73\151\x7a\x65\40\x68\x69\147\x68\145\x72\40\x74\150\141\156\40\x32\65\x36\40\156\157\164\x20\141\x6c\154\x6f\167\x65\144");
        xH:
        $DE = $bo - strlen($lp) % $bo;
        $DU = chr($DE);
        return $lp . str_repeat($DU, $DE);
    }
    private function unpadISO10126($lp)
    {
        $DE = substr($lp, -1);
        $GO = ord($DE);
        return substr($lp, 0, -$GO);
    }
    private function encryptSymmetric($lp)
    {
        $this->iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cryptParams["\143\x69\x70\x68\145\x72"]));
        $lp = $this->padISO10126($lp, $this->cryptParams["\x62\x6c\157\x63\x6b\x73\151\172\145"]);
        $Ra = openssl_encrypt($lp, $this->cryptParams["\143\x69\x70\x68\x65\x72"], $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->iv);
        if (!(false === $Ra)) {
            goto ha;
        }
        throw new Exception("\106\141\x69\154\x75\162\x65\x20\145\156\143\x72\x79\160\x74\151\156\147\x20\104\x61\164\141\x20\50\x6f\x70\145\x6e\163\163\x6c\40\x73\x79\155\155\x65\164\x72\151\143\x29\x20\x2d\40" . openssl_error_string());
        ha:
        return $this->iv . $Ra;
    }
    private function decryptSymmetric($lp)
    {
        $fn = openssl_cipher_iv_length($this->cryptParams["\143\x69\160\150\145\x72"]);
        $this->iv = substr($lp, 0, $fn);
        $lp = substr($lp, $fn);
        $Kg = openssl_decrypt($lp, $this->cryptParams["\143\x69\160\150\x65\x72"], $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->iv);
        if (!(false === $Kg)) {
            goto ip;
        }
        throw new Exception("\106\x61\151\x6c\x75\x72\145\x20\x64\145\x63\x72\171\160\164\x69\x6e\147\x20\x44\141\164\141\40\50\157\160\145\156\163\163\x6c\x20\163\171\x6d\155\145\164\162\x69\x63\51\x20\55\x20" . openssl_error_string());
        ip:
        return $this->unpadISO10126($Kg);
    }
    private function encryptPublic($lp)
    {
        if (openssl_public_encrypt($lp, $Ra, $this->key, $this->cryptParams["\160\x61\x64\x64\151\x6e\147"])) {
            goto KZ;
        }
        throw new Exception("\106\141\151\154\x75\162\145\40\145\156\143\x72\171\160\164\x69\x6e\147\x20\x44\141\164\141\x20\50\x6f\160\145\156\163\x73\154\40\160\165\142\154\x69\x63\x29\x20\x2d\x20" . openssl_error_string());
        KZ:
        return $Ra;
    }
    private function decryptPublic($lp)
    {
        if (openssl_public_decrypt($lp, $Kg, $this->key, $this->cryptParams["\160\141\x64\x64\x69\156\x67"])) {
            goto tG;
        }
        throw new Exception("\x46\x61\x69\154\x75\x72\x65\x20\144\x65\143\x72\x79\160\x74\x69\156\x67\40\104\141\164\141\40\50\157\160\x65\x6e\163\x73\x6c\x20\160\x75\x62\x6c\x69\x63\x29\40\x2d\40" . openssl_error_string());
        tG:
        return $Kg;
    }
    private function encryptPrivate($lp)
    {
        if (openssl_private_encrypt($lp, $Ra, $this->key, $this->cryptParams["\x70\141\144\x64\x69\x6e\x67"])) {
            goto E1;
        }
        throw new Exception("\x46\141\x69\154\165\162\x65\x20\x65\x6e\143\x72\171\x70\x74\x69\x6e\147\40\x44\141\x74\141\40\x28\x6f\160\145\x6e\x73\163\x6c\x20\x70\162\x69\x76\x61\x74\145\x29\x20\55\40" . openssl_error_string());
        E1:
        return $Ra;
    }
    private function decryptPrivate($lp)
    {
        if (openssl_private_decrypt($lp, $Kg, $this->key, $this->cryptParams["\x70\141\144\x64\x69\156\147"])) {
            goto Bj;
        }
        throw new Exception("\x46\x61\151\x6c\x75\x72\145\x20\x64\x65\x63\162\171\x70\164\151\x6e\147\40\104\141\x74\141\x20\50\x6f\x70\x65\x6e\163\163\x6c\x20\x70\162\x69\x76\x61\164\x65\x29\x20\x2d\40" . openssl_error_string());
        Bj:
        return $Kg;
    }
    private function signOpenSSL($lp)
    {
        $OM = OPENSSL_ALGO_SHA1;
        if (empty($this->cryptParams["\144\x69\147\x65\163\164"])) {
            goto xp;
        }
        $OM = $this->cryptParams["\144\x69\147\145\x73\x74"];
        xp:
        if (openssl_sign($lp, $tr, $this->key, $OM)) {
            goto HI;
        }
        throw new Exception("\106\x61\151\154\x75\x72\x65\40\123\x69\x67\x6e\151\156\147\40\x44\x61\164\x61\x3a\x20" . openssl_error_string() . "\x20\x2d\x20" . $OM);
        HI:
        return $tr;
    }
    private function verifyOpenSSL($lp, $tr)
    {
        $OM = OPENSSL_ALGO_SHA1;
        if (empty($this->cryptParams["\144\x69\147\x65\x73\164"])) {
            goto hX;
        }
        $OM = $this->cryptParams["\x64\x69\147\x65\x73\x74"];
        hX:
        return openssl_verify($lp, $tr, $this->key, $OM);
    }
    public function encryptData($lp)
    {
        if (!($this->cryptParams["\x6c\151\x62\x72\141\x72\171"] === "\x6f\x70\145\x6e\163\163\x6c")) {
            goto Nh;
        }
        switch ($this->cryptParams["\x74\171\x70\x65"]) {
            case "\163\x79\155\155\145\x74\x72\x69\x63":
                return $this->encryptSymmetric($lp);
            case "\160\x75\142\x6c\x69\143":
                return $this->encryptPublic($lp);
            case "\160\162\x69\166\x61\x74\x65":
                return $this->encryptPrivate($lp);
        }
        MF:
        Gl:
        Nh:
    }
    public function decryptData($lp)
    {
        if (!($this->cryptParams["\x6c\x69\x62\x72\x61\162\x79"] === "\157\160\x65\x6e\x73\x73\x6c")) {
            goto A3;
        }
        switch ($this->cryptParams["\164\171\160\x65"]) {
            case "\163\171\155\x6d\x65\164\x72\x69\x63":
                return $this->decryptSymmetric($lp);
            case "\160\165\142\x6c\x69\143":
                return $this->decryptPublic($lp);
            case "\x70\162\151\x76\141\x74\x65":
                return $this->decryptPrivate($lp);
        }
        Dt:
        Sn:
        A3:
    }
    public function signData($lp)
    {
        switch ($this->cryptParams["\x6c\x69\142\162\141\x72\171"]) {
            case "\x6f\160\x65\156\x73\x73\154":
                return $this->signOpenSSL($lp);
            case self::HMAC_SHA1:
                return hash_hmac("\x73\x68\x61\x31", $lp, $this->key, true);
        }
        Rh:
        gE:
    }
    public function verifySignature($lp, $tr)
    {
        switch ($this->cryptParams["\154\x69\x62\162\141\x72\x79"]) {
            case "\157\x70\145\x6e\163\x73\x6c":
                return $this->verifyOpenSSL($lp, $tr);
            case self::HMAC_SHA1:
                $vI = hash_hmac("\163\x68\141\x31", $lp, $this->key, true);
                return strcmp($tr, $vI) == 0;
        }
        Ww:
        Ms:
    }
    public function getAlgorith()
    {
        return $this->getAlgorithm();
    }
    public function getAlgorithm()
    {
        return $this->cryptParams["\155\x65\x74\x68\x6f\x64"];
    }
    public static function makeAsnSegment($vk, $Hl)
    {
        switch ($vk) {
            case 0x2:
                if (!(ord($Hl) > 0x7f)) {
                    goto NM;
                }
                $Hl = chr(0) . $Hl;
                NM:
                goto wu;
            case 0x3:
                $Hl = chr(0) . $Hl;
                goto wu;
        }
        n9:
        wu:
        $ph = strlen($Hl);
        if ($ph < 128) {
            goto py;
        }
        if ($ph < 0x100) {
            goto Ad;
        }
        if ($ph < 0x10000) {
            goto a5;
        }
        $g9 = null;
        goto fs;
        a5:
        $g9 = sprintf("\x25\143\x25\143\45\x63\45\143\45\x73", $vk, 0x82, $ph / 0x100, $ph % 0x100, $Hl);
        fs:
        goto Jf;
        Ad:
        $g9 = sprintf("\45\x63\x25\x63\45\x63\45\x73", $vk, 0x81, $ph, $Hl);
        Jf:
        goto Q0;
        py:
        $g9 = sprintf("\x25\x63\x25\x63\x25\x73", $vk, $ph, $Hl);
        Q0:
        return $g9;
    }
    public static function convertRSA($d2, $ft)
    {
        $S2 = self::makeAsnSegment(0x2, $ft);
        $pf = self::makeAsnSegment(0x2, $d2);
        $eG = self::makeAsnSegment(0x30, $pf . $S2);
        $Tb = self::makeAsnSegment(0x3, $eG);
        $XE = pack("\x48\52", "\63\60\60\104\x30\66\60\71\x32\101\70\66\x34\x38\70\66\106\x37\60\x44\x30\61\60\x31\x30\x31\x30\x35\60\60");
        $rB = self::makeAsnSegment(0x30, $XE . $Tb);
        $oN = base64_encode($rB);
        $x5 = "\55\x2d\x2d\55\55\102\105\107\111\116\x20\120\125\102\x4c\x49\103\x20\113\105\x59\x2d\55\x2d\55\55\xa";
        $B5 = 0;
        rl:
        if (!($J1 = substr($oN, $B5, 64))) {
            goto FG;
        }
        $x5 = $x5 . $J1 . "\12";
        $B5 += 64;
        goto rl;
        FG:
        return $x5 . "\x2d\x2d\55\x2d\x2d\105\x4e\x44\40\120\125\x42\x4c\111\103\x20\113\105\131\55\x2d\x2d\x2d\55\12";
    }
    public function serializeKey($b4)
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
    public static function fromEncryptedKeyElement(DOMElement $bj)
    {
        $Fg = new XMLSecEncSAML();
        $Fg->setNode($bj);
        if ($JO = $Fg->locateKey()) {
            goto XO;
        }
        throw new Exception("\125\156\141\x62\x6c\x65\x20\164\157\40\x6c\x6f\143\141\x74\x65\40\141\x6c\147\x6f\x72\151\164\x68\x6d\x20\146\157\162\40\x74\x68\151\x73\40\x45\156\143\x72\171\x70\x74\x65\x64\x20\x4b\145\x79");
        XO:
        $JO->isEncrypted = true;
        $JO->encryptedCtx = $Fg;
        XMLSecEncSAML::staticLocateKeyInfo($JO, $bj);
        return $JO;
    }
}
class XMLSecurityDSigSAML
{
    const XMLDSIGNS = "\x68\x74\x74\x70\x3a\x2f\x2f\167\167\x77\56\x77\x33\56\x6f\x72\147\57\62\x30\60\60\x2f\60\x39\x2f\x78\155\154\x64\x73\x69\x67\43";
    const SHA1 = "\x68\x74\x74\x70\x3a\x2f\x2f\x77\x77\167\x2e\x77\x33\56\157\x72\x67\x2f\x32\60\x30\x30\57\x30\x39\57\x78\155\154\144\163\x69\x67\x23\x73\150\141\x31";
    const SHA256 = "\x68\x74\164\x70\x3a\57\x2f\x77\167\167\x2e\x77\x33\x2e\x6f\x72\147\57\x32\x30\60\61\57\60\x34\57\170\x6d\x6c\145\156\143\43\163\x68\x61\62\x35\x36";
    const SHA384 = "\150\164\164\x70\x3a\x2f\57\x77\x77\x77\56\x77\63\56\157\162\x67\x2f\x32\60\60\x31\x2f\60\x34\57\x78\155\154\144\163\x69\x67\55\155\x6f\162\x65\43\x73\150\141\63\70\64";
    const SHA512 = "\150\164\x74\160\72\x2f\57\x77\167\167\56\x77\63\56\157\x72\147\x2f\62\60\60\x31\57\x30\64\57\x78\155\154\145\156\x63\43\x73\x68\141\x35\x31\x32";
    const RIPEMD160 = "\x68\x74\164\160\72\57\x2f\167\167\x77\x2e\167\x33\56\157\x72\147\57\x32\x30\x30\61\x2f\x30\64\x2f\170\155\x6c\x65\156\x63\43\x72\151\160\145\x6d\144\x31\x36\60";
    const C14N = "\x68\x74\164\x70\72\57\57\x77\167\167\56\167\63\56\157\x72\147\x2f\x54\122\x2f\x32\60\x30\61\x2f\x52\105\103\55\170\155\154\x2d\143\x31\x34\156\x2d\x32\60\x30\61\x30\63\x31\x35";
    const C14N_COMMENTS = "\x68\164\164\x70\72\57\57\x77\167\x77\x2e\x77\63\x2e\x6f\x72\147\x2f\124\122\57\62\x30\60\61\x2f\x52\x45\103\55\170\x6d\154\x2d\143\x31\64\x6e\55\x32\60\60\61\x30\63\x31\x35\43\x57\x69\164\x68\103\x6f\155\155\x65\x6e\x74\163";
    const EXC_C14N = "\150\x74\x74\x70\x3a\x2f\57\167\167\167\56\x77\x33\56\157\162\x67\x2f\x32\60\x30\x31\57\x31\60\57\x78\155\154\55\145\170\143\x2d\x63\61\x34\156\x23";
    const EXC_C14N_COMMENTS = "\150\164\164\x70\x3a\x2f\57\x77\167\x77\x2e\x77\63\x2e\157\162\147\x2f\62\60\60\x31\x2f\x31\x30\57\x78\x6d\x6c\x2d\x65\x78\143\x2d\143\x31\64\156\x23\x57\x69\x74\x68\x43\157\155\x6d\x65\x6e\x74\x73";
    const template = "\x3c\x64\x73\x3a\123\x69\147\156\141\x74\x75\162\x65\x20\x78\155\154\156\x73\x3a\x64\x73\x3d\42\x68\x74\x74\x70\72\x2f\57\167\167\167\x2e\x77\x33\56\157\162\x67\57\62\x30\x30\x30\57\60\x39\x2f\x78\155\x6c\144\163\x69\147\43\42\x3e\xd\xa\x20\40\74\x64\163\x3a\x53\x69\x67\x6e\x65\x64\111\156\x66\157\x3e\15\xa\40\x20\x20\x20\74\x64\163\72\x53\151\147\x6e\x61\x74\x75\162\145\x4d\145\164\x68\157\x64\40\x2f\x3e\15\12\40\x20\74\x2f\144\x73\72\123\151\147\x6e\145\144\x49\156\146\157\x3e\xd\12\74\57\144\x73\72\123\x69\147\x6e\x61\164\x75\x72\x65\76";
    const BASE_TEMPLATE = "\74\x53\x69\147\156\x61\164\165\162\145\x20\x78\x6d\x6c\x6e\x73\x3d\x22\150\164\x74\160\x3a\57\57\167\167\x77\56\x77\x33\x2e\157\x72\147\57\62\x30\x30\x30\57\x30\71\x2f\170\155\154\144\163\x69\x67\x23\x22\x3e\xd\xa\40\40\74\123\x69\x67\156\145\144\111\156\146\157\x3e\xd\12\40\x20\40\40\74\123\151\147\x6e\141\x74\x75\162\x65\115\x65\164\x68\x6f\x64\40\57\76\15\xa\x20\x20\74\x2f\x53\x69\x67\156\145\x64\x49\x6e\146\157\x3e\xd\xa\74\x2f\x53\151\147\156\x61\164\x75\162\145\x3e";
    public $sigNode = null;
    public $idKeys = array();
    public $idNS = array();
    private $signedInfo = null;
    private $xPathCtx = null;
    private $canonicalMethod = null;
    private $prefix = '';
    private $searchpfx = "\163\x65\x63\x64\163\x69\x67";
    private $validatedNodes = null;
    public function __construct($ZA = "\144\x73")
    {
        $aA = self::BASE_TEMPLATE;
        if (empty($ZA)) {
            goto bI;
        }
        $this->prefix = $ZA . "\x3a";
        $Sp = array("\x3c\123", "\x3c\57\x53", "\170\155\154\156\x73\75");
        $aU = array("\x3c{$ZA}\72\x53", "\74\x2f{$ZA}\x3a\x53", "\x78\155\x6c\156\163\72{$ZA}\x3d");
        $aA = str_replace($Sp, $aU, $aA);
        bI:
        $ET = new DOMDocument();
        $ET->loadXML($aA);
        $this->sigNode = $ET->documentElement;
    }
    private function resetXPathObj()
    {
        $this->xPathCtx = null;
    }
    private function getXPathObj()
    {
        if (!(empty($this->xPathCtx) && !empty($this->sigNode))) {
            goto Eq;
        }
        $lo = new DOMXPath($this->sigNode->ownerDocument);
        $lo->registerNamespace("\x73\x65\143\144\163\151\147", self::XMLDSIGNS);
        $this->xPathCtx = $lo;
        Eq:
        return $this->xPathCtx;
    }
    public static function generateGUID($ZA = "\160\146\x78")
    {
        $Hf = md5(uniqid(mt_rand(), true));
        $Jq = $ZA . substr($Hf, 0, 8) . "\55" . substr($Hf, 8, 4) . "\x2d" . substr($Hf, 12, 4) . "\x2d" . substr($Hf, 16, 4) . "\x2d" . substr($Hf, 20, 12);
        return $Jq;
    }
    public static function generate_GUID($ZA = "\160\146\x78")
    {
        return self::generateGUID($ZA);
    }
    public function locateSignature($KS, $HY = 0)
    {
        if ($KS instanceof DOMDocument) {
            goto lp;
        }
        $ax = $KS->ownerDocument;
        goto VQ;
        lp:
        $ax = $KS;
        VQ:
        if (!$ax) {
            goto sB;
        }
        $lo = new DOMXPath($ax);
        $lo->registerNamespace("\163\x65\143\x64\x73\x69\x67", self::XMLDSIGNS);
        $bH = "\56\x2f\57\163\x65\x63\x64\x73\x69\x67\x3a\x53\151\147\156\x61\x74\165\x72\145";
        $P0 = $lo->query($bH, $KS);
        $this->sigNode = $P0->item($HY);
        return $this->sigNode;
        sB:
        return null;
    }
    public function createNewSignNode($B9, $wZ = null)
    {
        $ax = $this->sigNode->ownerDocument;
        if (!is_null($wZ)) {
            goto QF;
        }
        $Ro = $ax->createElementNS(self::XMLDSIGNS, $this->prefix . $B9);
        goto zs;
        QF:
        $Ro = $ax->createElementNS(self::XMLDSIGNS, $this->prefix . $B9, $wZ);
        zs:
        return $Ro;
    }
    public function setCanonicalMethod($F8)
    {
        switch ($F8) {
            case "\150\164\164\160\x3a\57\57\x77\167\167\56\x77\63\56\157\x72\147\57\124\x52\x2f\62\x30\60\x31\x2f\122\x45\103\55\x78\x6d\154\x2d\x63\61\x34\x6e\55\x32\x30\60\61\x30\63\x31\x35":
            case "\x68\164\x74\160\x3a\x2f\57\x77\x77\167\56\x77\63\56\157\x72\147\x2f\x54\x52\57\62\60\x30\x31\x2f\122\105\x43\55\170\155\x6c\x2d\x63\61\x34\156\x2d\x32\60\x30\61\x30\x33\61\x35\x23\127\x69\164\x68\x43\157\155\155\145\156\164\163":
            case "\150\164\164\160\x3a\57\57\x77\x77\x77\x2e\x77\63\x2e\157\162\147\57\62\x30\x30\61\57\x31\x30\x2f\170\155\x6c\x2d\145\170\143\55\143\x31\64\x6e\x23":
            case "\x68\x74\x74\160\72\x2f\57\167\167\x77\x2e\167\x33\56\157\162\147\57\62\60\60\61\57\x31\60\x2f\x78\x6d\x6c\x2d\145\x78\x63\x2d\143\x31\64\x6e\43\127\x69\164\x68\x43\x6f\155\155\145\156\x74\163":
                $this->canonicalMethod = $F8;
                goto za;
            default:
                throw new Exception("\x49\156\x76\141\154\x69\x64\x20\103\141\156\157\156\151\x63\x61\x6c\40\x4d\x65\164\150\x6f\144");
        }
        jx:
        za:
        if (!($lo = $this->getXPathObj())) {
            goto ol;
        }
        $bH = "\56\x2f" . $this->searchpfx . "\72\123\x69\147\156\x65\x64\111\156\146\157";
        $P0 = $lo->query($bH, $this->sigNode);
        if (!($kc = $P0->item(0))) {
            goto bG;
        }
        $bH = "\x2e\x2f" . $this->searchpfx . "\103\141\x6e\x6f\x6e\151\x63\x61\154\151\x7a\x61\x74\x69\157\x6e\x4d\145\x74\x68\157\144";
        $P0 = $lo->query($bH, $kc);
        if ($Rg = $P0->item(0)) {
            goto aV;
        }
        $Rg = $this->createNewSignNode("\103\141\x6e\157\x6e\x69\x63\x61\x6c\151\172\141\x74\x69\157\x6e\x4d\x65\164\x68\157\x64");
        $kc->insertBefore($Rg, $kc->firstChild);
        aV:
        $Rg->setAttribute("\101\x6c\147\157\x72\151\164\x68\x6d", $this->canonicalMethod);
        bG:
        ol:
    }
    private function canonicalizeData($Ro, $HB, $i5 = null, $g3 = null)
    {
        $CR = false;
        $Mq = false;
        switch ($HB) {
            case "\150\164\164\x70\72\x2f\57\167\167\167\56\x77\x33\x2e\x6f\x72\x67\57\x54\122\x2f\62\x30\x30\61\x2f\122\x45\103\55\x78\155\154\55\x63\61\64\156\55\62\x30\60\61\x30\x33\61\65":
                $CR = false;
                $Mq = false;
                goto uL;
            case "\150\164\x74\160\72\57\x2f\167\x77\x77\56\167\x33\56\x6f\162\x67\x2f\x54\x52\57\62\x30\60\x31\x2f\x52\105\x43\x2d\x78\x6d\154\x2d\x63\61\x34\x6e\x2d\62\60\x30\61\60\x33\61\x35\43\127\151\x74\x68\103\x6f\155\155\145\x6e\164\163":
                $Mq = true;
                goto uL;
            case "\150\x74\x74\x70\72\x2f\x2f\x77\167\167\56\x77\x33\x2e\157\162\x67\x2f\62\60\60\61\57\x31\x30\x2f\170\155\x6c\55\145\170\x63\55\x63\61\64\x6e\x23":
                $CR = true;
                goto uL;
            case "\150\164\164\160\72\x2f\x2f\x77\167\167\56\x77\x33\x2e\157\162\147\x2f\x32\60\x30\61\57\61\x30\57\x78\x6d\x6c\x2d\x65\170\x63\55\x63\x31\x34\156\x23\x57\x69\x74\x68\103\x6f\155\x6d\145\x6e\x74\163":
                $CR = true;
                $Mq = true;
                goto uL;
        }
        jZ:
        uL:
        if (!(is_null($i5) && $Ro instanceof DOMNode && $Ro->ownerDocument !== null && $Ro->isSameNode($Ro->ownerDocument->documentElement))) {
            goto Wd;
        }
        $bj = $Ro;
        mQ:
        if (!($kf = $bj->previousSibling)) {
            goto tR;
        }
        if (!($kf->nodeType == XML_PI_NODE || $kf->nodeType == XML_COMMENT_NODE && $Mq)) {
            goto gf;
        }
        goto tR;
        gf:
        $bj = $kf;
        goto mQ;
        tR:
        if (!($kf == null)) {
            goto BD;
        }
        $Ro = $Ro->ownerDocument;
        BD:
        Wd:
        return $Ro->C14N($CR, $Mq, $i5, $g3);
    }
    public function canonicalizeSignedInfo()
    {
        $ax = $this->sigNode->ownerDocument;
        $HB = null;
        if (!$ax) {
            goto SM;
        }
        $lo = $this->getXPathObj();
        $bH = "\56\57\x73\x65\143\144\x73\x69\147\72\x53\x69\147\x6e\x65\x64\x49\156\x66\157";
        $P0 = $lo->query($bH, $this->sigNode);
        if (!($J7 = $P0->item(0))) {
            goto Ao;
        }
        $bH = "\x2e\x2f\163\145\143\144\x73\151\147\x3a\x43\141\156\157\156\151\x63\141\154\151\x7a\141\x74\151\157\x6e\115\x65\164\150\x6f\144";
        $P0 = $lo->query($bH, $J7);
        if (!($Rg = $P0->item(0))) {
            goto Zk;
        }
        $HB = $Rg->getAttribute("\x41\x6c\x67\x6f\x72\151\x74\x68\x6d");
        Zk:
        $this->signedInfo = $this->canonicalizeData($J7, $HB);
        return $this->signedInfo;
        Ao:
        SM:
        return null;
    }
    public function calculateDigest($Le, $lp, $vM = true)
    {
        switch ($Le) {
            case self::SHA1:
                $FM = "\163\150\x61\x31";
                goto LT;
            case self::SHA256:
                $FM = "\163\150\x61\x32\x35\x36";
                goto LT;
            case self::SHA384:
                $FM = "\163\x68\x61\63\70\64";
                goto LT;
            case self::SHA512:
                $FM = "\163\x68\x61\65\x31\x32";
                goto LT;
            case self::RIPEMD160:
                $FM = "\162\x69\160\x65\x6d\x64\x31\x36\60";
                goto LT;
            default:
                throw new Exception("\103\141\x6e\x6e\x6f\x74\40\x76\141\154\x69\x64\141\x74\x65\40\x64\x69\147\145\163\164\x3a\40\x55\156\x73\x75\x70\160\157\162\164\x65\144\40\101\154\147\157\162\151\164\x68\x6d\40\74{$Le}\x3e");
        }
        wy:
        LT:
        $oa = hash($FM, $lp, true);
        if (!$vM) {
            goto Re;
        }
        $oa = base64_encode($oa);
        Re:
        return $oa;
    }
    public function validateDigest($Ia, $lp)
    {
        $lo = new DOMXPath($Ia->ownerDocument);
        $lo->registerNamespace("\x73\x65\x63\x64\x73\x69\x67", self::XMLDSIGNS);
        $bH = "\x73\x74\x72\x69\x6e\x67\x28\x2e\x2f\x73\x65\143\144\x73\x69\x67\x3a\104\x69\x67\145\163\164\x4d\x65\164\150\157\x64\57\100\101\x6c\147\157\162\x69\x74\x68\155\x29";
        $Le = $lo->evaluate($bH, $Ia);
        $uI = $this->calculateDigest($Le, $lp, false);
        $bH = "\163\164\162\x69\x6e\x67\50\56\x2f\163\x65\x63\x64\x73\x69\x67\72\104\x69\x67\x65\x73\164\126\141\154\165\145\x29";
        $VI = $lo->evaluate($bH, $Ia);
        return $uI == base64_decode($VI);
    }
    public function processTransforms($Ia, $ls, $LC = true)
    {
        $lp = $ls;
        $lo = new DOMXPath($Ia->ownerDocument);
        $lo->registerNamespace("\163\x65\143\x64\x73\151\x67", self::XMLDSIGNS);
        $bH = "\56\x2f\163\x65\143\x64\x73\x69\x67\x3a\124\162\141\x6e\x73\x66\x6f\162\155\163\57\x73\x65\x63\x64\x73\151\x67\72\x54\162\141\156\x73\x66\157\x72\x6d";
        $xT = $lo->query($bH, $Ia);
        $gE = "\x68\164\164\160\72\57\x2f\x77\167\x77\56\167\x33\x2e\157\162\147\57\124\x52\x2f\x32\x30\x30\61\x2f\122\105\103\55\170\x6d\x6c\55\x63\61\x34\x6e\55\62\60\60\x31\60\x33\61\65";
        $i5 = null;
        $g3 = null;
        foreach ($xT as $GE) {
            $BL = $GE->getAttribute("\101\154\x67\x6f\x72\x69\164\150\155");
            switch ($BL) {
                case "\x68\x74\164\x70\x3a\57\x2f\x77\167\167\x2e\x77\x33\x2e\x6f\x72\147\x2f\x32\60\60\x31\57\x31\x30\x2f\170\155\x6c\55\x65\170\x63\55\143\61\64\156\x23":
                case "\150\164\x74\160\x3a\57\57\167\167\x77\x2e\167\x33\56\157\x72\x67\57\62\x30\x30\x31\57\61\60\x2f\170\155\x6c\55\x65\x78\x63\x2d\143\x31\64\x6e\x23\x57\x69\164\150\103\157\x6d\x6d\x65\156\164\163":
                    if (!$LC) {
                        goto Ln;
                    }
                    $gE = $BL;
                    goto V0;
                    Ln:
                    $gE = "\x68\x74\x74\x70\x3a\x2f\x2f\167\167\167\x2e\167\x33\x2e\157\x72\x67\57\62\x30\60\61\57\61\x30\x2f\170\x6d\x6c\55\145\170\143\55\143\61\64\156\43";
                    V0:
                    $Ro = $GE->firstChild;
                    te:
                    if (!$Ro) {
                        goto KH;
                    }
                    if (!($Ro->localName == "\111\156\143\x6c\x75\163\x69\x76\145\x4e\141\155\x65\163\160\x61\143\145\x73")) {
                        goto Ox;
                    }
                    if (!($E2 = $Ro->getAttribute("\120\x72\145\146\151\170\114\x69\x73\164"))) {
                        goto nZ;
                    }
                    $qj = array();
                    $Cr = explode("\40", $E2);
                    foreach ($Cr as $E2) {
                        $Wm = trim($E2);
                        if (empty($Wm)) {
                            goto DK;
                        }
                        $qj[] = $Wm;
                        DK:
                        cI:
                    }
                    Mb:
                    if (!(count($qj) > 0)) {
                        goto st;
                    }
                    $g3 = $qj;
                    st:
                    nZ:
                    goto KH;
                    Ox:
                    $Ro = $Ro->nextSibling;
                    goto te;
                    KH:
                    goto MS;
                case "\x68\x74\164\x70\72\x2f\x2f\167\x77\167\x2e\167\x33\x2e\157\162\x67\x2f\124\122\57\62\x30\60\61\x2f\122\x45\x43\x2d\170\x6d\154\x2d\x63\x31\x34\156\55\62\x30\60\x31\60\63\61\x35":
                case "\x68\164\164\x70\x3a\57\57\x77\167\x77\56\167\x33\x2e\157\162\147\x2f\124\122\x2f\x32\60\60\61\x2f\x52\105\103\x2d\170\155\x6c\55\x63\x31\x34\x6e\55\62\x30\60\61\x30\63\61\x35\43\127\x69\164\x68\x43\x6f\155\x6d\145\156\164\x73":
                    if (!$LC) {
                        goto Rg;
                    }
                    $gE = $BL;
                    goto Ki;
                    Rg:
                    $gE = "\150\x74\x74\x70\x3a\x2f\57\167\167\167\x2e\167\x33\56\157\162\x67\x2f\124\122\57\62\60\x30\61\x2f\122\x45\103\x2d\170\155\154\x2d\x63\61\64\x6e\x2d\62\x30\60\61\x30\x33\61\x35";
                    Ki:
                    goto MS;
                case "\150\x74\x74\160\72\x2f\x2f\x77\167\x77\x2e\x77\x33\x2e\x6f\x72\x67\57\124\122\x2f\x31\71\71\x39\57\x52\x45\103\x2d\x78\160\141\164\150\x2d\x31\71\71\71\61\61\x31\x36":
                    $Ro = $GE->firstChild;
                    Sc:
                    if (!$Ro) {
                        goto Lx;
                    }
                    if (!($Ro->localName == "\x58\120\141\164\x68")) {
                        goto q6;
                    }
                    $i5 = array();
                    $i5["\x71\165\x65\x72\x79"] = "\x28\x2e\57\x2f\x2e\40\174\x20\56\x2f\x2f\100\52\x20\x7c\40\x2e\x2f\57\x6e\141\155\145\163\x70\141\x63\x65\72\72\52\51\x5b" . $Ro->nodeValue . "\x5d";
                    $Ox["\156\141\155\145\x73\x70\x61\x63\145\163"] = array();
                    $jm = $lo->query("\56\57\156\x61\x6d\x65\x73\x70\141\143\145\x3a\x3a\52", $Ro);
                    foreach ($jm as $uO) {
                        if (!($uO->localName != "\x78\x6d\x6c")) {
                            goto eW;
                        }
                        $i5["\156\141\x6d\145\x73\160\x61\x63\145\163"][$uO->localName] = $uO->nodeValue;
                        eW:
                        eZ:
                    }
                    uF:
                    goto Lx;
                    q6:
                    $Ro = $Ro->nextSibling;
                    goto Sc;
                    Lx:
                    goto MS;
            }
            d3:
            MS:
            Ye:
        }
        C9:
        if (!$lp instanceof DOMNode) {
            goto Fi;
        }
        $lp = $this->canonicalizeData($ls, $gE, $i5, $g3);
        Fi:
        return $lp;
    }
    public function processRefNode($Ia)
    {
        $tU = null;
        $LC = true;
        if ($dE = $Ia->getAttribute("\x55\122\x49")) {
            goto fk;
        }
        $LC = false;
        $tU = $Ia->ownerDocument;
        goto gp;
        fk:
        $DG = parse_url($dE);
        if (empty($DG["\x70\141\164\x68"])) {
            goto St;
        }
        $tU = file_get_contents($DG);
        goto Uq;
        St:
        if ($DX = $DG["\146\x72\x61\x67\x6d\x65\x6e\164"]) {
            goto xj;
        }
        $tU = $Ia->ownerDocument;
        goto tI;
        xj:
        $LC = false;
        $gb = new DOMXPath($Ia->ownerDocument);
        if (!($this->idNS && is_array($this->idNS))) {
            goto g4;
        }
        foreach ($this->idNS as $NH => $so) {
            $gb->registerNamespace($NH, $so);
            i5:
        }
        oz:
        g4:
        $D5 = "\100\111\144\x3d\x22" . $DX . "\x22";
        if (!is_array($this->idKeys)) {
            goto mt;
        }
        foreach ($this->idKeys as $QR) {
            $D5 .= "\x20\157\162\x20\100{$QR}\x3d\x27{$DX}\47";
            ZE:
        }
        Io:
        mt:
        $bH = "\x2f\57\52\133" . $D5 . "\x5d";
        $tU = $gb->query($bH)->item(0);
        tI:
        Uq:
        gp:
        $lp = $this->processTransforms($Ia, $tU, $LC);
        if ($this->validateDigest($Ia, $lp)) {
            goto y6;
        }
        return false;
        y6:
        if (!$tU instanceof DOMNode) {
            goto vl;
        }
        if (!empty($DX)) {
            goto M8;
        }
        $this->validatedNodes[] = $tU;
        goto BW;
        M8:
        $this->validatedNodes[$DX] = $tU;
        BW:
        vl:
        return true;
    }
    public function getRefNodeID($Ia)
    {
        if (!($dE = $Ia->getAttribute("\x55\122\x49"))) {
            goto hs;
        }
        $DG = parse_url($dE);
        if (!empty($DG["\160\141\164\x68"])) {
            goto Cy;
        }
        if (!($DX = $DG["\146\162\141\x67\x6d\145\156\x74"])) {
            goto Nt;
        }
        return $DX;
        Nt:
        Cy:
        hs:
        return null;
    }
    public function getRefIDs()
    {
        $O_ = array();
        $lo = $this->getXPathObj();
        $bH = "\56\x2f\x73\145\143\x64\163\151\x67\x3a\x53\x69\147\156\x65\144\x49\x6e\146\157\x2f\x73\x65\x63\x64\163\151\x67\x3a\122\145\146\x65\x72\x65\156\143\x65";
        $P0 = $lo->query($bH, $this->sigNode);
        if (!($P0->length == 0)) {
            goto m8;
        }
        throw new Exception("\x52\x65\146\x65\162\x65\x6e\143\x65\40\156\x6f\x64\x65\163\x20\156\x6f\x74\40\146\157\x75\x6e\144");
        m8:
        foreach ($P0 as $Ia) {
            $O_[] = $this->getRefNodeID($Ia);
            Id:
        }
        Yb:
        return $O_;
    }
    public function validateReference()
    {
        $MA = $this->sigNode->ownerDocument->documentElement;
        if ($MA->isSameNode($this->sigNode)) {
            goto Wy;
        }
        if (!($this->sigNode->parentNode != null)) {
            goto AA;
        }
        $this->sigNode->parentNode->removeChild($this->sigNode);
        AA:
        Wy:
        $lo = $this->getXPathObj();
        $bH = "\x2e\x2f\163\145\x63\x64\x73\151\147\72\x53\151\x67\156\145\144\111\x6e\146\157\x2f\163\145\143\x64\x73\x69\147\72\122\x65\x66\145\162\x65\156\143\x65";
        $P0 = $lo->query($bH, $this->sigNode);
        if (!($P0->length == 0)) {
            goto NV;
        }
        throw new Exception("\x52\x65\x66\145\x72\145\x6e\x63\145\40\x6e\x6f\144\x65\x73\40\x6e\157\164\x20\146\157\x75\156\144");
        NV:
        $this->validatedNodes = array();
        foreach ($P0 as $Ia) {
            if ($this->processRefNode($Ia)) {
                goto A5;
            }
            $this->validatedNodes = null;
            throw new Exception("\122\145\146\x65\162\145\x6e\143\x65\40\x76\141\x6c\x69\x64\x61\x74\x69\157\156\x20\146\x61\x69\154\145\144");
            A5:
            aB:
        }
        K6:
        return true;
    }
    private function addRefInternal($zd, $Ro, $BL, $Rs = null, $sw = null)
    {
        $ZA = null;
        $Am = null;
        $Zh = "\111\144";
        $gS = true;
        $o8 = false;
        if (!is_array($sw)) {
            goto QM;
        }
        $ZA = empty($sw["\160\162\x65\x66\x69\x78"]) ? null : $sw["\x70\162\x65\x66\x69\170"];
        $Am = empty($sw["\x70\162\x65\x66\x69\170\x5f\x6e\163"]) ? null : $sw["\160\x72\145\146\151\170\137\156\163"];
        $Zh = empty($sw["\151\x64\x5f\x6e\141\x6d\x65"]) ? "\111\x64" : $sw["\151\x64\137\x6e\x61\x6d\145"];
        $gS = !isset($sw["\x6f\166\145\x72\x77\162\151\x74\145"]) ? true : (bool) $sw["\157\x76\x65\x72\x77\x72\151\x74\x65"];
        $o8 = !isset($sw["\x66\x6f\x72\143\x65\137\x75\x72\151"]) ? false : (bool) $sw["\x66\157\x72\143\x65\x5f\165\x72\x69"];
        QM:
        $uh = $Zh;
        if (empty($ZA)) {
            goto QZ;
        }
        $uh = $ZA . "\x3a" . $uh;
        QZ:
        $Ia = $this->createNewSignNode("\122\x65\146\145\162\x65\x6e\143\145");
        $zd->appendChild($Ia);
        if (!$Ro instanceof DOMDocument) {
            goto TH;
        }
        if ($o8) {
            goto Uc;
        }
        goto Pv;
        TH:
        $dE = null;
        if ($gS) {
            goto RI;
        }
        $dE = $Am ? $Ro->getAttributeNS($Am, $Zh) : $Ro->getAttribute($Zh);
        RI:
        if (!empty($dE)) {
            goto g_;
        }
        $dE = self::generateGUID();
        $Ro->setAttributeNS($Am, $uh, $dE);
        g_:
        $Ia->setAttribute("\125\122\111", "\43" . $dE);
        goto Pv;
        Uc:
        $Ia->setAttribute("\x55\x52\x49", '');
        Pv:
        $pj = $this->createNewSignNode("\124\162\x61\x6e\x73\146\x6f\162\x6d\163");
        $Ia->appendChild($pj);
        if (is_array($Rs)) {
            goto Zf;
        }
        if (!empty($this->canonicalMethod)) {
            goto Lq;
        }
        goto gQ;
        Zf:
        foreach ($Rs as $GE) {
            $oY = $this->createNewSignNode("\x54\162\141\156\x73\x66\x6f\x72\x6d");
            $pj->appendChild($oY);
            if (is_array($GE) && !empty($GE["\x68\x74\x74\x70\72\x2f\57\167\167\x77\56\167\x33\x2e\157\162\x67\x2f\x54\x52\x2f\61\71\x39\71\57\122\105\103\55\170\160\x61\x74\x68\x2d\61\71\x39\71\61\x31\x31\x36"]) && !empty($GE["\x68\164\x74\160\x3a\57\57\167\x77\x77\x2e\167\63\56\x6f\162\147\x2f\124\x52\x2f\61\x39\x39\71\57\122\x45\103\55\170\x70\x61\x74\150\55\61\x39\x39\71\61\x31\x31\x36"]["\x71\165\145\x72\x79"])) {
                goto h8;
            }
            $oY->setAttribute("\x41\154\x67\157\162\151\164\150\155", $GE);
            goto oO;
            h8:
            $oY->setAttribute("\101\154\147\x6f\x72\151\x74\x68\x6d", "\150\164\164\x70\72\57\57\167\167\x77\56\167\x33\56\157\x72\147\57\x54\x52\57\x31\x39\x39\x39\57\122\105\x43\x2d\170\160\x61\164\x68\55\61\71\71\71\61\x31\x31\x36");
            $EV = $this->createNewSignNode("\130\120\x61\x74\x68", $GE["\x68\164\164\x70\72\57\57\x77\167\167\56\167\x33\x2e\x6f\162\x67\57\124\122\57\61\71\x39\71\x2f\x52\x45\103\x2d\170\160\141\164\150\55\61\x39\71\x39\x31\x31\61\66"]["\161\165\145\162\x79"]);
            $oY->appendChild($EV);
            if (empty($GE["\150\164\164\160\x3a\57\x2f\167\x77\x77\56\x77\x33\x2e\157\x72\x67\x2f\x54\x52\57\x31\x39\x39\x39\57\x52\105\x43\x2d\170\x70\141\x74\150\x2d\x31\x39\x39\71\61\x31\x31\66"]["\x6e\141\155\145\163\160\x61\x63\x65\x73"])) {
                goto gz;
            }
            foreach ($GE["\150\x74\x74\x70\72\x2f\57\x77\x77\167\x2e\x77\x33\56\x6f\x72\147\x2f\x54\122\x2f\61\x39\x39\x39\x2f\x52\x45\x43\55\x78\x70\x61\x74\x68\55\x31\x39\x39\x39\x31\61\61\66"]["\x6e\x61\155\145\163\x70\x61\x63\x65\163"] as $ZA => $gQ) {
                $EV->setAttributeNS("\150\164\164\160\72\x2f\57\x77\167\x77\56\x77\63\56\157\x72\147\x2f\x32\x30\60\x30\57\170\x6d\x6c\x6e\163\57", "\x78\155\154\x6e\x73\x3a{$ZA}", $gQ);
                VT:
            }
            j4:
            gz:
            oO:
            o_:
        }
        hQ:
        goto gQ;
        Lq:
        $oY = $this->createNewSignNode("\x54\x72\x61\156\x73\x66\x6f\x72\155");
        $pj->appendChild($oY);
        $oY->setAttribute("\101\x6c\147\x6f\x72\151\164\150\x6d", $this->canonicalMethod);
        gQ:
        $Ml = $this->processTransforms($Ia, $Ro);
        $uI = $this->calculateDigest($BL, $Ml);
        $Q4 = $this->createNewSignNode("\x44\151\147\145\x73\164\115\145\164\x68\157\x64");
        $Ia->appendChild($Q4);
        $Q4->setAttribute("\x41\x6c\147\x6f\x72\151\164\x68\x6d", $BL);
        $VI = $this->createNewSignNode("\104\x69\147\x65\163\164\126\x61\x6c\x75\145", $uI);
        $Ia->appendChild($VI);
    }
    public function addReference($Ro, $BL, $Rs = null, $sw = null)
    {
        if (!($lo = $this->getXPathObj())) {
            goto lO;
        }
        $bH = "\56\57\x73\x65\143\144\163\x69\147\x3a\x53\x69\x67\156\x65\144\x49\x6e\x66\x6f";
        $P0 = $lo->query($bH, $this->sigNode);
        if (!($ne = $P0->item(0))) {
            goto yD;
        }
        $this->addRefInternal($ne, $Ro, $BL, $Rs, $sw);
        yD:
        lO:
    }
    public function addReferenceList($Pk, $BL, $Rs = null, $sw = null)
    {
        if (!($lo = $this->getXPathObj())) {
            goto SH;
        }
        $bH = "\56\x2f\163\x65\x63\x64\x73\x69\147\72\123\151\147\x6e\x65\144\111\156\146\x6f";
        $P0 = $lo->query($bH, $this->sigNode);
        if (!($ne = $P0->item(0))) {
            goto Tr;
        }
        foreach ($Pk as $Ro) {
            $this->addRefInternal($ne, $Ro, $BL, $Rs, $sw);
            Nf:
        }
        XM:
        Tr:
        SH:
    }
    public function addObject($lp, $Ei = null, $x5 = null)
    {
        $I7 = $this->createNewSignNode("\x4f\142\152\145\143\x74");
        $this->sigNode->appendChild($I7);
        if (empty($Ei)) {
            goto TQ;
        }
        $I7->setAttribute("\115\151\x6d\x65\x54\171\x70\x65", $Ei);
        TQ:
        if (empty($x5)) {
            goto iO;
        }
        $I7->setAttribute("\x45\156\x63\157\x64\151\x6e\x67", $x5);
        iO:
        if ($lp instanceof DOMElement) {
            goto RY;
        }
        $Z4 = $this->sigNode->ownerDocument->createTextNode($lp);
        goto aU;
        RY:
        $Z4 = $this->sigNode->ownerDocument->importNode($lp, true);
        aU:
        $I7->appendChild($Z4);
        return $I7;
    }
    public function locateKey($Ro = null)
    {
        if (!empty($Ro)) {
            goto TO;
        }
        $Ro = $this->sigNode;
        TO:
        if ($Ro instanceof DOMNode) {
            goto yL;
        }
        return null;
        yL:
        if (!($ax = $Ro->ownerDocument)) {
            goto YW;
        }
        $lo = new DOMXPath($ax);
        $lo->registerNamespace("\x73\x65\x63\x64\163\x69\147", self::XMLDSIGNS);
        $bH = "\x73\164\162\151\x6e\147\50\x2e\x2f\163\x65\x63\144\x73\151\x67\x3a\123\x69\x67\156\145\144\111\x6e\x66\x6f\57\163\145\143\x64\163\151\147\72\123\x69\x67\x6e\141\x74\165\162\x65\x4d\145\164\x68\x6f\144\x2f\x40\101\154\147\157\x72\151\164\x68\x6d\x29";
        $BL = $lo->evaluate($bH, $Ro);
        if (!$BL) {
            goto Di;
        }
        try {
            $JO = new XMLSecurityKeySAML($BL, array("\x74\171\160\x65" => "\160\165\x62\154\x69\143"));
        } catch (Exception $R8) {
            return null;
        }
        return $JO;
        Di:
        YW:
        return null;
    }
    public function verify($JO)
    {
        $ax = $this->sigNode->ownerDocument;
        $lo = new DOMXPath($ax);
        $lo->registerNamespace("\163\145\143\x64\163\151\x67", self::XMLDSIGNS);
        $bH = "\163\164\x72\151\x6e\x67\x28\x2e\x2f\163\145\x63\x64\163\x69\147\x3a\123\x69\147\x6e\x61\164\x75\162\145\x56\x61\x6c\165\x65\x29";
        $UB = $lo->evaluate($bH, $this->sigNode);
        if (!empty($UB)) {
            goto Vl;
        }
        throw new Exception("\125\156\x61\x62\x6c\145\x20\164\157\40\154\x6f\143\141\x74\x65\x20\123\151\x67\156\x61\x74\x75\x72\145\x56\x61\x6c\165\145");
        Vl:
        return $JO->verifySignature($this->signedInfo, base64_decode($UB));
    }
    public function signData($JO, $lp)
    {
        return $JO->signData($lp);
    }
    public function sign($JO, $fd = null)
    {
        if (!($fd != null)) {
            goto LD;
        }
        $this->resetXPathObj();
        $this->appendSignature($fd);
        $this->sigNode = $fd->lastChild;
        LD:
        if (!($lo = $this->getXPathObj())) {
            goto OK;
        }
        $bH = "\56\x2f\163\145\x63\x64\163\151\147\72\x53\151\x67\156\x65\x64\111\x6e\146\x6f";
        $P0 = $lo->query($bH, $this->sigNode);
        if (!($ne = $P0->item(0))) {
            goto Qq;
        }
        $bH = "\x2e\57\x73\145\143\144\163\151\147\72\x53\x69\x67\156\x61\x74\x75\162\x65\115\x65\x74\x68\157\144";
        $P0 = $lo->query($bH, $ne);
        $hl = $P0->item(0);
        $hl->setAttribute("\x41\x6c\x67\x6f\x72\151\x74\x68\155", $JO->type);
        $lp = $this->canonicalizeData($ne, $this->canonicalMethod);
        $UB = base64_encode($this->signData($JO, $lp));
        $G7 = $this->createNewSignNode("\123\151\147\156\x61\x74\x75\162\145\126\x61\x6c\165\145", $UB);
        if ($LV = $ne->nextSibling) {
            goto X1;
        }
        $this->sigNode->appendChild($G7);
        goto x3;
        X1:
        $LV->parentNode->insertBefore($G7, $LV);
        x3:
        Qq:
        OK:
    }
    public function appendCert()
    {
    }
    public function appendKey($JO, $b4 = null)
    {
        $JO->serializeKey($b4);
    }
    public function insertSignature($Ro, $OC = null)
    {
        $ti = $Ro->ownerDocument;
        $Du = $ti->importNode($this->sigNode, true);
        if ($OC == null) {
            goto IP;
        }
        return $Ro->insertBefore($Du, $OC);
        goto IH;
        IP:
        return $Ro->insertBefore($Du);
        IH:
    }
    public function appendSignature($Us, $n_ = false)
    {
        $OC = $n_ ? $Us->firstChild : null;
        return $this->insertSignature($Us, $OC);
    }
    public static function get509XCert($TU, $YA = true)
    {
        $Ms = self::staticGet509XCerts($TU, $YA);
        if (empty($Ms)) {
            goto hw;
        }
        return $Ms[0];
        hw:
        return '';
    }
    public static function staticGet509XCerts($Ms, $YA = true)
    {
        if ($YA) {
            goto F0;
        }
        return array($Ms);
        goto r3;
        F0:
        $lp = '';
        $sQ = array();
        $eX = explode("\xa", $Ms);
        $W1 = false;
        foreach ($eX as $E0) {
            if (!$W1) {
                goto LE;
            }
            if (!(strncmp($E0, "\x2d\x2d\x2d\55\55\x45\x4e\x44\40\103\105\x52\x54\111\x46\111\103\x41\124\x45", 20) == 0)) {
                goto rD;
            }
            $W1 = false;
            $sQ[] = $lp;
            $lp = '';
            goto al;
            rD:
            $lp .= trim($E0);
            goto D4;
            LE:
            if (!(strncmp($E0, "\55\55\55\55\x2d\x42\x45\107\111\x4e\40\x43\105\x52\124\111\x46\111\x43\101\x54\x45", 22) == 0)) {
                goto fq;
            }
            $W1 = true;
            fq:
            D4:
            al:
        }
        ao:
        return $sQ;
        r3:
    }
    public static function staticAdd509Cert($wW, $TU, $YA = true, $F1 = false, $lo = null, $sw = null)
    {
        if (!$F1) {
            goto mn;
        }
        $TU = file_get_contents($TU);
        mn:
        if ($wW instanceof DOMElement) {
            goto K9;
        }
        throw new Exception("\111\x6e\x76\141\x6c\x69\144\40\x70\x61\162\x65\156\164\x20\116\157\144\x65\40\x70\x61\x72\141\x6d\x65\x74\x65\162");
        K9:
        $Xc = $wW->ownerDocument;
        if (!empty($lo)) {
            goto Rs;
        }
        $lo = new DOMXPath($wW->ownerDocument);
        $lo->registerNamespace("\x73\145\143\x64\163\x69\x67", self::XMLDSIGNS);
        Rs:
        $bH = "\56\x2f\x73\145\x63\x64\163\151\x67\x3a\113\x65\x79\x49\156\x66\x6f";
        $P0 = $lo->query($bH, $wW);
        $SP = $P0->item(0);
        $uS = '';
        if (!$SP) {
            goto Hv;
        }
        $E2 = $SP->lookupPrefix(self::XMLDSIGNS);
        if (empty($E2)) {
            goto rx;
        }
        $uS = $E2 . "\x3a";
        rx:
        goto af;
        Hv:
        $E2 = $wW->lookupPrefix(self::XMLDSIGNS);
        if (empty($E2)) {
            goto NL;
        }
        $uS = $E2 . "\72";
        NL:
        $Ul = false;
        $SP = $Xc->createElementNS(self::XMLDSIGNS, $uS . "\x4b\145\171\111\x6e\146\157");
        $bH = "\56\57\163\x65\x63\144\x73\x69\x67\x3a\117\x62\152\145\x63\164";
        $P0 = $lo->query($bH, $wW);
        if (!($UZ = $P0->item(0))) {
            goto Bl;
        }
        $UZ->parentNode->insertBefore($SP, $UZ);
        $Ul = true;
        Bl:
        if ($Ul) {
            goto I9;
        }
        $wW->appendChild($SP);
        I9:
        af:
        $Ms = self::staticGet509XCerts($TU, $YA);
        $c2 = $Xc->createElementNS(self::XMLDSIGNS, $uS . "\x58\x35\60\71\104\x61\164\x61");
        $SP->appendChild($c2);
        $hu = false;
        $MG = false;
        if (!is_array($sw)) {
            goto LL;
        }
        if (empty($sw["\x69\163\163\x75\x65\162\x53\145\162\151\x61\x6c"])) {
            goto AP;
        }
        $hu = true;
        AP:
        if (empty($sw["\163\x75\x62\152\x65\x63\164\x4e\x61\155\x65"])) {
            goto ep;
        }
        $MG = true;
        ep:
        LL:
        foreach ($Ms as $I0) {
            if (!($hu || $MG)) {
                goto H8;
            }
            if (!($tL = openssl_x509_parse("\x2d\x2d\55\55\x2d\102\105\107\x49\x4e\x20\103\x45\x52\124\111\106\x49\103\101\x54\105\55\x2d\x2d\55\55\12" . chunk_split($I0, 64, "\12") . "\x2d\55\x2d\x2d\55\x45\x4e\104\x20\103\105\x52\x54\x49\x46\111\103\101\124\105\x2d\x2d\x2d\x2d\55\12"))) {
                goto zY;
            }
            if (!($MG && !empty($tL["\163\165\x62\x6a\x65\143\164"]))) {
                goto jl;
            }
            if (is_array($tL["\163\x75\142\152\145\x63\164"])) {
                goto z1;
            }
            $U9 = $tL["\151\163\x73\x75\x65\162"];
            goto zQ;
            z1:
            $dL = array();
            foreach ($tL["\x73\x75\142\x6a\145\x63\164"] as $uf => $wZ) {
                if (is_array($wZ)) {
                    goto QR;
                }
                array_unshift($dL, "{$uf}\x3d{$wZ}");
                goto RS;
                QR:
                foreach ($wZ as $xm) {
                    array_unshift($dL, "{$uf}\x3d{$xm}");
                    Ws:
                }
                Hx:
                RS:
                v2:
            }
            Kc:
            $U9 = implode("\54", $dL);
            zQ:
            $rb = $Xc->createElementNS(self::XMLDSIGNS, $uS . "\x58\65\x30\x39\123\165\x62\x6a\145\x63\164\116\141\155\x65", $U9);
            $c2->appendChild($rb);
            jl:
            if (!($hu && !empty($tL["\151\x73\x73\x75\145\x72"]) && !empty($tL["\163\x65\162\151\141\x6c\116\165\155\x62\x65\162"]))) {
                goto zl;
            }
            if (is_array($tL["\x69\163\163\x75\145\162"])) {
                goto FE;
            }
            $LB = $tL["\151\x73\x73\165\x65\162"];
            goto rb;
            FE:
            $dL = array();
            foreach ($tL["\151\163\163\x75\x65\162"] as $uf => $wZ) {
                array_unshift($dL, "{$uf}\x3d{$wZ}");
                c6:
            }
            ty:
            $LB = implode("\x2c", $dL);
            rb:
            $kh = $Xc->createElementNS(self::XMLDSIGNS, $uS . "\x58\65\x30\71\x49\163\163\165\x65\x72\123\x65\x72\x69\x61\154");
            $c2->appendChild($kh);
            $YP = $Xc->createElementNS(self::XMLDSIGNS, $uS . "\130\x35\x30\71\x49\163\163\x75\x65\162\116\141\x6d\x65", $LB);
            $kh->appendChild($YP);
            $YP = $Xc->createElementNS(self::XMLDSIGNS, $uS . "\x58\65\x30\71\x53\x65\x72\151\x61\154\x4e\x75\x6d\x62\145\162", $tL["\x73\x65\x72\151\141\x6c\116\165\x6d\142\145\x72"]);
            $kh->appendChild($YP);
            zl:
            zY:
            H8:
            $Mp = $Xc->createElementNS(self::XMLDSIGNS, $uS . "\x58\65\60\x39\103\145\162\x74\x69\146\x69\143\141\x74\x65", $I0);
            $c2->appendChild($Mp);
            cN:
        }
        wv:
    }
    public function add509Cert($TU, $YA = true, $F1 = false, $sw = null)
    {
        if (!($lo = $this->getXPathObj())) {
            goto qH;
        }
        self::staticAdd509Cert($this->sigNode, $TU, $YA, $F1, $lo, $sw);
        qH:
    }
    public function appendToKeyInfo($Ro)
    {
        $wW = $this->sigNode;
        $Xc = $wW->ownerDocument;
        $lo = $this->getXPathObj();
        if (!empty($lo)) {
            goto dA;
        }
        $lo = new DOMXPath($wW->ownerDocument);
        $lo->registerNamespace("\163\145\x63\x64\163\151\147", self::XMLDSIGNS);
        dA:
        $bH = "\56\57\163\x65\143\144\163\151\x67\x3a\113\x65\x79\x49\x6e\146\x6f";
        $P0 = $lo->query($bH, $wW);
        $SP = $P0->item(0);
        if ($SP) {
            goto Qe;
        }
        $uS = '';
        $E2 = $wW->lookupPrefix(self::XMLDSIGNS);
        if (empty($E2)) {
            goto G4;
        }
        $uS = $E2 . "\72";
        G4:
        $Ul = false;
        $SP = $Xc->createElementNS(self::XMLDSIGNS, $uS . "\x4b\x65\171\111\156\x66\157");
        $bH = "\56\x2f\163\145\x63\x64\163\x69\147\x3a\117\142\x6a\x65\143\x74";
        $P0 = $lo->query($bH, $wW);
        if (!($UZ = $P0->item(0))) {
            goto tz;
        }
        $UZ->parentNode->insertBefore($SP, $UZ);
        $Ul = true;
        tz:
        if ($Ul) {
            goto GW;
        }
        $wW->appendChild($SP);
        GW:
        Qe:
        $SP->appendChild($Ro);
        return $SP;
    }
    public function getValidatedNodes()
    {
        return $this->validatedNodes;
    }
}
class XMLSecEncSAML
{
    const template = "\x3c\170\x65\x6e\143\x3a\x45\x6e\x63\162\171\160\x74\x65\x64\104\x61\x74\x61\40\170\155\x6c\156\163\72\170\145\x6e\143\75\47\150\164\164\160\72\57\x2f\167\x77\x77\x2e\x77\63\x2e\x6f\162\x67\x2f\x32\60\60\61\x2f\x30\x34\x2f\170\155\x6c\x65\156\x63\x23\47\x3e\xd\xa\40\x20\x20\x3c\170\x65\156\x63\x3a\103\151\x70\x68\x65\x72\x44\141\x74\141\76\15\xa\40\40\x20\x20\40\x20\x3c\170\x65\156\143\x3a\x43\x69\160\x68\145\x72\126\141\x6c\165\145\76\x3c\57\x78\x65\x6e\x63\72\103\151\160\150\x65\x72\126\x61\154\165\145\x3e\15\12\40\x20\40\x3c\57\170\x65\x6e\143\72\103\x69\x70\150\145\162\x44\141\x74\x61\76\xd\12\x3c\x2f\170\x65\156\143\72\105\x6e\x63\162\x79\x70\x74\x65\144\104\x61\x74\141\76";
    const Element = "\150\164\x74\x70\x3a\x2f\x2f\167\167\x77\56\167\x33\x2e\157\162\x67\57\62\x30\x30\61\57\x30\64\x2f\170\155\x6c\x65\156\143\43\x45\x6c\x65\155\145\156\x74";
    const Content = "\150\x74\x74\160\72\x2f\x2f\167\x77\x77\x2e\x77\x33\x2e\157\162\147\57\x32\x30\x30\x31\57\x30\x34\x2f\170\x6d\154\145\x6e\143\43\103\x6f\156\164\x65\x6e\x74";
    const URI = 3;
    const XMLENCNS = "\150\x74\x74\x70\72\x2f\x2f\x77\x77\167\x2e\167\x33\56\x6f\162\x67\x2f\62\60\x30\x31\57\x30\x34\57\x78\x6d\x6c\x65\156\x63\43";
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
    public function addReference($B9, $Ro, $vk)
    {
        if ($Ro instanceof DOMNode) {
            goto xx;
        }
        throw new Exception("\x24\x6e\157\144\145\x20\151\x73\x20\x6e\x6f\164\40\157\146\x20\164\171\x70\x65\40\x44\x4f\x4d\116\x6f\144\x65");
        xx:
        $D3 = $this->encdoc;
        $this->_resetTemplate();
        $Dh = $this->encdoc;
        $this->encdoc = $D3;
        $X5 = XMLSecurityDSigSAML::generateGUID();
        $bj = $Dh->documentElement;
        $bj->setAttribute("\111\x64", $X5);
        $this->references[$B9] = array("\156\157\144\145" => $Ro, "\x74\x79\x70\x65" => $vk, "\x65\x6e\x63\156\x6f\x64\x65" => $Dh, "\x72\x65\x66\x75\x72\x69" => $X5);
    }
    public function setNode($Ro)
    {
        $this->rawNode = $Ro;
    }
    public function encryptNode($JO, $aU = true)
    {
        $lp = '';
        if (!empty($this->rawNode)) {
            goto cK;
        }
        throw new Exception("\x4e\x6f\144\x65\x20\x74\x6f\40\x65\156\143\162\171\160\x74\40\150\141\163\40\156\157\x74\40\x62\x65\x65\156\x20\x73\x65\164");
        cK:
        if ($JO instanceof XMLSecurityKeySAML) {
            goto ga;
        }
        throw new Exception("\111\x6e\166\x61\x6c\x69\x64\40\113\145\x79");
        ga:
        $ax = $this->rawNode->ownerDocument;
        $gb = new DOMXPath($this->encdoc);
        $YH = $gb->query("\57\170\145\156\x63\x3a\105\156\143\x72\x79\160\x74\145\144\104\141\x74\x61\57\x78\145\x6e\143\72\103\x69\160\x68\145\162\104\x61\164\141\x2f\170\145\156\143\x3a\103\x69\160\150\145\x72\126\x61\x6c\165\x65");
        $i8 = $YH->item(0);
        if (!($i8 == null)) {
            goto yW;
        }
        throw new Exception("\x45\x72\x72\157\x72\x20\x6c\x6f\143\x61\164\151\156\147\40\x43\151\x70\x68\145\162\126\x61\x6c\x75\145\40\x65\x6c\x65\x6d\x65\x6e\164\x20\x77\x69\x74\150\151\x6e\x20\x74\x65\155\160\154\141\x74\145");
        yW:
        switch ($this->type) {
            case self::Element:
                $lp = $ax->saveXML($this->rawNode);
                $this->encdoc->documentElement->setAttribute("\x54\x79\x70\145", self::Element);
                goto K2;
            case self::Content:
                $Ta = $this->rawNode->childNodes;
                foreach ($Ta as $Vs) {
                    $lp .= $ax->saveXML($Vs);
                    RG:
                }
                op:
                $this->encdoc->documentElement->setAttribute("\x54\x79\160\145", self::Content);
                goto K2;
            default:
                throw new Exception("\124\171\160\x65\40\151\x73\x20\143\165\162\162\x65\x6e\164\x6c\x79\x20\x6e\x6f\164\40\163\x75\x70\x70\157\x72\164\145\144");
        }
        vq:
        K2:
        $vP = $this->encdoc->documentElement->appendChild($this->encdoc->createElementNS(self::XMLENCNS, "\x78\145\156\x63\x3a\105\156\143\x72\x79\x70\164\151\157\x6e\115\x65\x74\150\x6f\x64"));
        $vP->setAttribute("\x41\x6c\147\x6f\162\x69\164\x68\x6d", $JO->getAlgorithm());
        $i8->parentNode->parentNode->insertBefore($vP, $i8->parentNode->parentNode->firstChild);
        $uj = base64_encode($JO->encryptData($lp));
        $wZ = $this->encdoc->createTextNode($uj);
        $i8->appendChild($wZ);
        if ($aU) {
            goto yz;
        }
        return $this->encdoc->documentElement;
        goto uY;
        yz:
        switch ($this->type) {
            case self::Element:
                if (!($this->rawNode->nodeType == XML_DOCUMENT_NODE)) {
                    goto h7;
                }
                return $this->encdoc;
                h7:
                $PZ = $this->rawNode->ownerDocument->importNode($this->encdoc->documentElement, true);
                $this->rawNode->parentNode->replaceChild($PZ, $this->rawNode);
                return $PZ;
            case self::Content:
                $PZ = $this->rawNode->ownerDocument->importNode($this->encdoc->documentElement, true);
                NJ:
                if (!$this->rawNode->firstChild) {
                    goto Yd;
                }
                $this->rawNode->removeChild($this->rawNode->firstChild);
                goto NJ;
                Yd:
                $this->rawNode->appendChild($PZ);
                return $PZ;
        }
        eR:
        Pd:
        uY:
    }
    public function encryptReferences($JO)
    {
        $K5 = $this->rawNode;
        $xA = $this->type;
        foreach ($this->references as $B9 => $wy) {
            $this->encdoc = $wy["\x65\156\143\156\157\x64\145"];
            $this->rawNode = $wy["\156\157\x64\145"];
            $this->type = $wy["\x74\x79\160\x65"];
            try {
                $AH = $this->encryptNode($JO);
                $this->references[$B9]["\x65\x6e\143\x6e\157\144\x65"] = $AH;
            } catch (Exception $R8) {
                $this->rawNode = $K5;
                $this->type = $xA;
                throw $R8;
            }
            Y2:
        }
        Qt:
        $this->rawNode = $K5;
        $this->type = $xA;
    }
    public function getCipherValue()
    {
        if (!empty($this->rawNode)) {
            goto t6;
        }
        throw new Exception("\116\x6f\x64\x65\40\164\x6f\40\x64\x65\143\162\171\x70\164\x20\150\x61\x73\x20\x6e\157\164\40\142\x65\145\x6e\40\163\145\164");
        t6:
        $ax = $this->rawNode->ownerDocument;
        $gb = new DOMXPath($ax);
        $gb->registerNamespace("\170\155\x6c\145\x6e\143\x72", self::XMLENCNS);
        $bH = "\x2e\57\170\x6d\154\x65\156\x63\162\x3a\x43\151\160\150\145\162\104\x61\164\141\x2f\x78\x6d\154\x65\x6e\143\x72\72\103\151\x70\150\145\162\x56\141\x6c\165\145";
        $P0 = $gb->query($bH, $this->rawNode);
        $Ro = $P0->item(0);
        if ($Ro) {
            goto ML;
        }
        return null;
        ML:
        return base64_decode($Ro->nodeValue);
    }
    public function decryptNode($JO, $aU = true)
    {
        if ($JO instanceof XMLSecurityKeySAML) {
            goto Oa;
        }
        throw new Exception("\111\x6e\166\x61\154\151\144\40\x4b\145\x79");
        Oa:
        $hR = $this->getCipherValue();
        if ($hR) {
            goto Jh;
        }
        throw new Exception("\x43\141\156\x6e\x6f\164\x20\154\157\143\x61\164\x65\40\145\156\x63\162\171\x70\164\145\144\x20\144\141\164\141");
        goto aj;
        Jh:
        $Kg = $JO->decryptData($hR);
        if ($aU) {
            goto q3;
        }
        return $Kg;
        goto Sm;
        q3:
        switch ($this->type) {
            case self::Element:
                $jq = new DOMDocument();
                $jq->loadXML($Kg);
                if (!($this->rawNode->nodeType == XML_DOCUMENT_NODE)) {
                    goto m_;
                }
                return $jq;
                m_:
                $PZ = $this->rawNode->ownerDocument->importNode($jq->documentElement, true);
                $this->rawNode->parentNode->replaceChild($PZ, $this->rawNode);
                return $PZ;
            case self::Content:
                if ($this->rawNode->nodeType == XML_DOCUMENT_NODE) {
                    goto cw;
                }
                $ax = $this->rawNode->ownerDocument;
                goto Bt;
                cw:
                $ax = $this->rawNode;
                Bt:
                $q2 = $ax->createDocumentFragment();
                $q2->appendXML($Kg);
                $b4 = $this->rawNode->parentNode;
                $b4->replaceChild($q2, $this->rawNode);
                return $b4;
            default:
                return $Kg;
        }
        H1:
        MG:
        Sm:
        aj:
    }
    public function encryptKey($aj, $aS, $Jo = true)
    {
        if (!(!$aj instanceof XMLSecurityKeySAML || !$aS instanceof XMLSecurityKeySAML)) {
            goto Uk;
        }
        throw new Exception("\111\x6e\166\x61\x6c\151\x64\40\x4b\x65\x79");
        Uk:
        $yS = base64_encode($aj->encryptData($aS->key));
        $px = $this->encdoc->documentElement;
        $qX = $this->encdoc->createElementNS(self::XMLENCNS, "\170\145\x6e\x63\x3a\x45\x6e\143\x72\x79\160\164\145\144\x4b\145\171");
        if ($Jo) {
            goto Cm;
        }
        $this->encKey = $qX;
        goto sb;
        Cm:
        $SP = $px->insertBefore($this->encdoc->createElementNS("\x68\x74\x74\x70\72\x2f\57\x77\x77\167\56\167\x33\x2e\157\x72\x67\57\62\60\60\x30\x2f\60\x39\57\170\x6d\x6c\x64\x73\x69\x67\x23", "\144\163\x69\147\72\113\x65\x79\111\x6e\x66\157"), $px->firstChild);
        $SP->appendChild($qX);
        sb:
        $vP = $qX->appendChild($this->encdoc->createElementNS(self::XMLENCNS, "\x78\x65\156\143\x3a\105\x6e\143\162\x79\x70\x74\151\x6f\156\x4d\145\x74\150\157\x64"));
        $vP->setAttribute("\101\x6c\x67\x6f\162\x69\164\150\x6d", $aj->getAlgorith());
        if (empty($aj->name)) {
            goto lD;
        }
        $SP = $qX->appendChild($this->encdoc->createElementNS("\x68\x74\164\160\72\57\57\x77\167\x77\56\167\63\x2e\x6f\x72\147\x2f\x32\60\60\x30\x2f\60\x39\x2f\170\155\x6c\144\163\151\x67\43", "\144\x73\x69\x67\72\x4b\x65\x79\x49\156\146\157"));
        $SP->appendChild($this->encdoc->createElementNS("\150\164\x74\160\x3a\57\57\167\x77\167\56\x77\63\56\x6f\162\x67\57\x32\x30\60\60\57\60\x39\x2f\170\155\154\x64\163\x69\147\x23", "\x64\x73\x69\x67\x3a\113\145\x79\x4e\x61\155\x65", $aj->name));
        lD:
        $SM = $qX->appendChild($this->encdoc->createElementNS(self::XMLENCNS, "\x78\145\x6e\x63\72\103\x69\x70\x68\145\x72\104\141\164\141"));
        $SM->appendChild($this->encdoc->createElementNS(self::XMLENCNS, "\x78\145\156\x63\72\x43\151\160\x68\x65\x72\126\141\x6c\x75\x65", $yS));
        if (!(is_array($this->references) && count($this->references) > 0)) {
            goto z6;
        }
        $NE = $qX->appendChild($this->encdoc->createElementNS(self::XMLENCNS, "\x78\145\x6e\143\x3a\x52\x65\x66\x65\x72\145\x6e\143\x65\x4c\x69\x73\164"));
        foreach ($this->references as $B9 => $wy) {
            $X5 = $wy["\x72\145\x66\165\x72\151"];
            $sL = $NE->appendChild($this->encdoc->createElementNS(self::XMLENCNS, "\x78\x65\x6e\143\x3a\104\141\x74\x61\122\145\x66\145\162\145\x6e\143\145"));
            $sL->setAttribute("\x55\122\x49", "\x23" . $X5);
            fX:
        }
        nE:
        z6:
        return;
    }
    public function decryptKey($qX)
    {
        if ($qX->isEncrypted) {
            goto hi;
        }
        throw new Exception("\x4b\145\171\x20\x69\x73\40\156\157\164\x20\x45\x6e\143\x72\x79\x70\164\x65\144");
        hi:
        if (!empty($qX->key)) {
            goto cj;
        }
        throw new Exception("\x4b\x65\171\40\x69\x73\40\155\x69\163\x73\151\156\147\40\x64\x61\x74\x61\x20\x74\x6f\x20\x70\x65\162\146\x6f\162\x6d\x20\x74\x68\x65\x20\144\x65\x63\x72\171\x70\164\x69\157\156");
        cj:
        return $this->decryptNode($qX, false);
    }
    public function locateEncryptedData($bj)
    {
        if ($bj instanceof DOMDocument) {
            goto ut;
        }
        $ax = $bj->ownerDocument;
        goto N1;
        ut:
        $ax = $bj;
        N1:
        if (!$ax) {
            goto Vb;
        }
        $lo = new DOMXPath($ax);
        $bH = "\x2f\57\52\x5b\x6c\x6f\143\141\x6c\x2d\x6e\x61\x6d\x65\50\51\x3d\47\105\x6e\x63\x72\x79\x70\164\x65\x64\x44\x61\164\x61\x27\40\x61\156\x64\x20\156\141\x6d\x65\x73\x70\141\143\145\55\x75\x72\151\50\51\x3d\47" . self::XMLENCNS . "\47\135";
        $P0 = $lo->query($bH);
        return $P0->item(0);
        Vb:
        return null;
    }
    public function locateKey($Ro = null)
    {
        if (!empty($Ro)) {
            goto H7;
        }
        $Ro = $this->rawNode;
        H7:
        if ($Ro instanceof DOMNode) {
            goto VJ;
        }
        return null;
        VJ:
        if (!($ax = $Ro->ownerDocument)) {
            goto VH;
        }
        $lo = new DOMXPath($ax);
        $lo->registerNamespace("\x58\115\x4c\x53\x65\143\105\x6e\143\x53\x41\x4d\x4c", self::XMLENCNS);
        $bH = "\x2e\57\x2f\130\115\x4c\x53\x65\x63\105\156\143\x53\101\x4d\x4c\72\105\x6e\143\162\171\160\164\151\x6f\x6e\115\x65\164\x68\157\144";
        $P0 = $lo->query($bH, $Ro);
        if (!($eg = $P0->item(0))) {
            goto j6;
        }
        $vn = $eg->getAttribute("\x41\154\147\157\162\x69\x74\x68\x6d");
        try {
            $JO = new XMLSecurityKeySAML($vn, array("\164\171\x70\x65" => "\x70\162\151\x76\141\x74\x65"));
        } catch (Exception $R8) {
            return null;
        }
        return $JO;
        j6:
        VH:
        return null;
    }
    public static function staticLocateKeyInfo($Ad = null, $Ro = null)
    {
        if (!(empty($Ro) || !$Ro instanceof DOMNode)) {
            goto lz;
        }
        return null;
        lz:
        $ax = $Ro->ownerDocument;
        if ($ax) {
            goto x6;
        }
        return null;
        x6:
        $lo = new DOMXPath($ax);
        $lo->registerNamespace("\130\x4d\x4c\x53\145\143\x45\x6e\x63\123\101\115\x4c", self::XMLENCNS);
        $lo->registerNamespace("\170\155\154\x73\x65\143\144\163\151\147", XMLSecurityDSigSAML::XMLDSIGNS);
        $bH = "\56\57\x78\155\154\x73\x65\143\x64\x73\151\x67\72\x4b\x65\171\x49\156\x66\x6f";
        $P0 = $lo->query($bH, $Ro);
        $eg = $P0->item(0);
        if ($eg) {
            goto ng;
        }
        return $Ad;
        ng:
        foreach ($eg->childNodes as $Vs) {
            switch ($Vs->localName) {
                case "\113\145\x79\x4e\141\155\145":
                    if (empty($Ad)) {
                        goto FK;
                    }
                    $Ad->name = $Vs->nodeValue;
                    FK:
                    goto QQ;
                case "\x4b\x65\x79\126\141\x6c\165\145":
                    foreach ($Vs->childNodes as $IX) {
                        switch ($IX->localName) {
                            case "\x44\x53\101\x4b\145\x79\x56\x61\x6c\x75\145":
                                throw new Exception("\104\123\101\x4b\x65\x79\126\141\154\x75\145\40\x63\x75\162\x72\145\156\x74\x6c\x79\x20\x6e\x6f\164\x20\x73\x75\160\x70\x6f\162\164\x65\x64");
                            case "\x52\123\x41\x4b\145\x79\x56\141\x6c\x75\145":
                                $d2 = null;
                                $ft = null;
                                if (!($DT = $IX->getElementsByTagName("\x4d\157\x64\x75\x6c\165\163")->item(0))) {
                                    goto dT;
                                }
                                $d2 = base64_decode($DT->nodeValue);
                                dT:
                                if (!($Hh = $IX->getElementsByTagName("\x45\x78\x70\157\x6e\x65\x6e\x74")->item(0))) {
                                    goto LV;
                                }
                                $ft = base64_decode($Hh->nodeValue);
                                LV:
                                if (!(empty($d2) || empty($ft))) {
                                    goto a2;
                                }
                                throw new Exception("\x4d\x69\x73\163\151\x6e\x67\40\x4d\x6f\144\165\x6c\x75\x73\x20\x6f\162\x20\105\x78\x70\x6f\156\x65\156\x74");
                                a2:
                                $D4 = XMLSecurityKeySAML::convertRSA($d2, $ft);
                                $Ad->loadKey($D4);
                                goto Zl;
                        }
                        bl:
                        Zl:
                        hY:
                    }
                    uX:
                    goto QQ;
                case "\x52\145\164\x72\x69\x65\x76\x61\154\115\x65\164\150\157\x64":
                    $vk = $Vs->getAttribute("\x54\x79\160\x65");
                    if (!($vk !== "\150\164\x74\160\72\x2f\x2f\167\x77\x77\x2e\167\x33\56\157\x72\x67\57\x32\x30\60\61\57\x30\x34\57\170\x6d\154\x65\156\143\43\105\x6e\143\x72\171\x70\x74\x65\x64\113\145\171")) {
                        goto oB;
                    }
                    goto QQ;
                    oB:
                    $dE = $Vs->getAttribute("\x55\122\111");
                    if (!($dE[0] !== "\x23")) {
                        goto aP;
                    }
                    goto QQ;
                    aP:
                    $xd = substr($dE, 1);
                    $bH = "\x2f\57\130\x4d\114\123\x65\x63\x45\x6e\x63\123\101\115\x4c\x3a\x45\156\x63\162\x79\160\x74\x65\144\x4b\145\171\133\x40\111\x64\75\x27{$xd}\47\x5d";
                    $G8 = $lo->query($bH)->item(0);
                    if ($G8) {
                        goto mN;
                    }
                    throw new Exception("\125\x6e\x61\x62\x6c\x65\40\x74\x6f\x20\154\x6f\143\x61\x74\145\40\105\156\x63\162\171\160\x74\145\144\x4b\145\x79\x20\x77\x69\x74\x68\x20\100\x49\x64\x3d\x27{$xd}\x27\x2e");
                    mN:
                    return XMLSecurityKeySAML::fromEncryptedKeyElement($G8);
                case "\105\x6e\143\x72\171\160\164\145\x64\x4b\x65\171":
                    return XMLSecurityKeySAML::fromEncryptedKeyElement($Vs);
                case "\x58\65\x30\71\104\141\164\x61":
                    if (!($jo = $Vs->getElementsByTagName("\x58\65\x30\x39\x43\x65\162\164\151\x66\x69\143\141\x74\x65"))) {
                        goto PI;
                    }
                    if (!($jo->length > 0)) {
                        goto yX;
                    }
                    $CW = $jo->item(0)->textContent;
                    $CW = str_replace(array("\xd", "\xa", "\40"), '', $CW);
                    $CW = "\x2d\x2d\x2d\x2d\x2d\102\105\107\x49\116\40\x43\x45\x52\124\111\106\111\103\x41\x54\x45\55\x2d\x2d\x2d\x2d\xa" . chunk_split($CW, 64, "\xa") . "\55\x2d\55\x2d\55\x45\116\x44\40\103\x45\122\x54\111\x46\x49\103\101\x54\x45\55\x2d\55\55\x2d\xa";
                    $Ad->loadKey($CW, false, true);
                    yX:
                    PI:
                    goto QQ;
            }
            SD:
            QQ:
            b8:
        }
        Za:
        return $Ad;
    }
    public function locateKeyInfo($Ad = null, $Ro = null)
    {
        if (!empty($Ro)) {
            goto wd;
        }
        $Ro = $this->rawNode;
        wd:
        return self::staticLocateKeyInfo($Ad, $Ro);
    }
}
