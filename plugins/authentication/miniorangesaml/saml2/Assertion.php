<?php


class SAML2_Assertion
{
    private $id;
    private $issueInstant;
    private $issuer;
    private $nameId;
    private $encryptedNameId;
    private $encryptedAttribute;
    private $encryptionKey;
    private $notBefore;
    private $notOnOrAfter;
    private $validAudiences;
    private $sessionNotOnOrAfter;
    private $sessionIndex;
    private $authnInstant;
    private $authnContextClassRef;
    private $authnContextDecl;
    private $authnContextDeclRef;
    private $AuthenticatingAuthority;
    private $attributes;
    private $nameFormat;
    private $signatureKey;
    private $certificates;
    private $signatureData;
    private $requiredEncAttributes;
    private $SubjectConfirmation;
    protected $wasSignedAtConstruction = FALSE;
    public function __construct(DOMElement $XT = NULL)
    {
        $this->id = UtilitiesSAML::generateId();
        $this->issueInstant = UtilitiesSAML::generateTimestamp();
        $this->issuer = '';
        $this->authnInstant = UtilitiesSAML::generateTimestamp();
        $this->attributes = array();
        $this->nameFormat = "\x75\x72\156\x3a\157\x61\x73\x69\x73\72\156\x61\x6d\145\163\x3a\164\x63\72\x53\101\x4d\x4c\72\x31\x2e\61\72\x6e\x61\155\145\151\144\55\146\x6f\x72\x6d\x61\164\72\x75\156\163\160\145\x63\x69\146\151\x65\144";
        $this->certificates = array();
        $this->AuthenticatingAuthority = array();
        $this->SubjectConfirmation = array();
        if (!($XT === NULL)) {
            goto hO;
        }
        return;
        hO:
        if (!($XT->localName === "\105\x6e\x63\x72\x79\160\164\x65\x64\x41\x73\x73\145\x72\x74\x69\x6f\156")) {
            goto q8;
        }
        $oa = UtilitiesSAML::xpQuery($XT, "\x2e\57\x78\145\x6e\143\72\x45\x6e\x63\x72\171\x70\164\145\144\104\x61\164\x61");
        $Jy = UtilitiesSAML::xpQuery($XT, "\x2e\x2f\170\x65\x6e\x63\72\x45\156\143\x72\x79\x70\164\x65\144\104\x61\164\x61\57\x64\163\x3a\113\x65\x79\x49\156\x66\x6f\x2f\170\x65\156\x63\72\105\156\143\162\x79\160\x74\145\144\x4b\145\x79");
        $xp = '';
        if (empty($Jy)) {
            goto o3;
        }
        $xp = $Jy[0]->firstChild->getAttribute("\101\154\147\x6f\162\x69\164\x68\x6d");
        goto GB;
        o3:
        $Jy = UtilitiesSAML::xpQuery($XT, "\x2e\57\x78\x65\x6e\x63\x3a\105\x6e\143\162\x79\x70\164\x65\x64\x4b\145\x79\57\170\x65\156\143\x3a\x45\x6e\x63\x72\171\160\164\151\x6f\156\115\145\164\x68\157\144");
        $xp = $Jy[0]->getAttribute("\101\x6c\x67\157\162\151\164\x68\x6d");
        GB:
        $t9 = UtilitiesSAML::getEncryptionAlgorithm($xp);
        if (count($oa) === 0) {
            goto hJ;
        }
        if (count($oa) > 1) {
            goto bp;
        }
        goto k8;
        hJ:
        throw new Exception("\115\x69\163\x73\151\x6e\147\40\x65\156\143\162\171\x70\164\145\x64\40\144\141\164\x61\40\x69\x6e\x20\74\x73\x61\155\x6c\x3a\105\156\143\x72\171\160\164\145\x64\x41\163\x73\x65\x72\x74\x69\x6f\x6e\76\x2e");
        goto k8;
        bp:
        throw new Exception("\x4d\x6f\x72\x65\40\x74\150\x61\156\40\x6f\x6e\x65\40\145\156\143\162\x79\160\164\145\x64\x20\x64\141\x74\x61\40\x65\x6c\145\x6d\145\x6e\164\40\151\156\40\x3c\x73\141\155\154\72\x45\156\143\162\x79\x70\x74\145\x64\101\x73\x73\x65\x72\164\x69\157\x6e\76\x2e");
        k8:
        $da = new XMLSecurityKeySAML($t9, array("\164\x79\160\x65" => "\x70\162\151\x76\x61\164\x65"));
        $kk = UtilitiesSAML::getCustomerDetails();
        $M5 = '';
        if (!isset($kk["\x73\160\137\x62\141\x73\x65\137\165\162\x6c"])) {
            goto RT;
        }
        $M5 = $kk["\163\160\137\x62\x61\163\x65\x5f\x75\x72\x6c"];
        RT:
        $wA = JURI::base();
        if (empty($M5)) {
            goto jj;
        }
        $wA = $M5;
        jj:
        $kk = UtilitiesSAML::getSAMLConfiguration();
        $xY = UtilitiesSAML::get_public_private_certificate($kk, "\x70\x72\x69\x76\141\164\x65\137\143\145\162\x74\x69\146\151\x63\141\164\145");
        if ($xY == null || $xY == '') {
            goto ew;
        }
        $nT = UtilitiesSAML::getCustom_CertificatePath("\x43\165\163\x74\157\x6d\120\x72\x69\166\x61\164\145\103\145\162\x74\x69\146\151\x63\x61\164\x65\56\153\145\171");
        goto Rf;
        ew:
        $nT = dirname(__FILE__) . DIRECTORY_SEPARATOR . "\143\145\x72\164" . DIRECTORY_SEPARATOR . "\163\160\x2d\153\145\x79\x2e\x6b\x65\x79";
        Rf:
        $da->loadKey($nT, TRUE);
        try {
            $XT = UtilitiesSAML::decryptElement($oa[0], $da);
        } catch (Exception $yV) {
            echo "\103\141\165\147\x68\x74\40\x65\x78\x63\x65\160\164\151\157\156\72\40", $yV->getMessage(), "\xa";
        }
        q8:
        if ($XT->hasAttribute("\111\x44")) {
            goto XI;
        }
        throw new Exception("\115\x69\x73\163\x69\x6e\x67\40\x49\104\40\141\x74\164\162\x69\x62\165\x74\x65\x20\x6f\x6e\x20\x53\x41\x4d\114\40\141\163\163\x65\162\x74\x69\157\156\56");
        XI:
        $this->id = $XT->getAttribute("\x49\104");
        if (!($XT->getAttribute("\x56\x65\x72\163\151\157\156") !== "\x32\56\x30")) {
            goto YX;
        }
        throw new Exception("\x55\156\163\165\160\x70\157\x72\x74\145\144\x20\x76\x65\x72\163\x69\157\x6e\x3a\40" . $XT->getAttribute("\126\x65\x72\163\151\157\156"));
        YX:
        $this->issueInstant = UtilitiesSAML::xsDateTimeToTimestamp($XT->getAttribute("\x49\x73\163\x75\x65\111\x6e\x73\164\x61\x6e\164"));
        $F0 = UtilitiesSAML::xpQuery($XT, "\56\x2f\x73\141\155\x6c\x5f\141\163\x73\145\x72\164\x69\x6f\x6e\72\x49\x73\163\x75\145\x72");
        if (!empty($F0)) {
            goto Zr;
        }
        throw new Exception("\115\x69\x73\163\151\x6e\147\40\x3c\163\x61\x6d\x6c\x3a\x49\163\x73\x75\145\x72\76\x20\x69\x6e\40\x61\x73\x73\145\x72\164\x69\x6f\156\56");
        Zr:
        $this->issuer = trim($F0[0]->textContent);
        $this->parseConditions($XT);
        $this->parseAuthnStatement($XT);
        $this->parseAttributes($XT);
        $this->parseEncryptedAttributes($XT);
        $this->parseSignature($XT);
        $this->parseSubject($XT);
    }
    private function parseSubject(DOMElement $XT)
    {
        $yh = UtilitiesSAML::xpQuery($XT, "\56\57\x73\x61\155\x6c\137\141\163\163\x65\x72\164\x69\157\156\x3a\123\165\x62\152\145\143\x74");
        if (empty($yh)) {
            goto v2;
        }
        if (count($yh) > 1) {
            goto h7;
        }
        goto Bt;
        v2:
        return;
        goto Bt;
        h7:
        throw new Exception("\115\x6f\x72\145\40\164\x68\141\156\x20\x6f\156\x65\x20\74\163\x61\155\x6c\72\123\165\x62\152\145\143\x74\x3e\x20\x69\156\40\x3c\163\141\155\154\72\101\163\163\145\x72\164\151\x6f\156\76\56");
        Bt:
        $yh = $yh[0];
        $eS = UtilitiesSAML::xpQuery($yh, "\56\x2f\x73\x61\155\154\x5f\141\163\x73\145\x72\x74\151\157\156\x3a\x4e\x61\x6d\x65\x49\104\x20\174\x20\x2e\57\x73\141\x6d\x6c\137\x61\x73\163\145\162\x74\151\157\x6e\72\x45\x6e\x63\162\x79\160\x74\x65\x64\111\x44\x2f\x78\x65\156\x63\x3a\105\x6e\143\x72\171\160\164\x65\144\104\x61\164\x61");
        if (empty($eS)) {
            goto QC;
        }
        if (count($eS) > 1) {
            goto Il;
        }
        goto RX;
        QC:
        throw new Exception("\115\151\163\163\x69\x6e\147\x20\74\x73\141\155\x6c\72\x4e\141\155\145\111\104\76\40\x6f\162\40\x3c\163\141\x6d\154\x3a\105\x6e\x63\x72\x79\x70\x74\145\144\111\x44\76\x20\151\x6e\x20\74\163\x61\155\x6c\x3a\x53\x75\142\152\x65\143\x74\76\x2e");
        goto RX;
        Il:
        throw new Exception("\x4d\157\x72\x65\40\164\150\x61\x6e\40\x6f\156\145\40\x3c\x73\141\x6d\154\x3a\116\141\155\x65\111\104\76\40\157\x72\40\74\163\x61\155\154\x3a\x45\156\143\x72\171\x70\164\x65\x64\x44\76\x20\151\x6e\x20\x3c\163\141\x6d\154\72\123\165\142\x6a\145\143\164\x3e\x2e");
        RX:
        $eS = $eS[0];
        if ($eS->localName === "\105\x6e\x63\162\171\x70\x74\145\144\x44\x61\x74\x61") {
            goto Pa;
        }
        $this->nameId = UtilitiesSAML::parseNameId($eS);
        goto Z0;
        Pa:
        $this->encryptedNameId = $eS;
        Z0:
    }
    private function parseConditions(DOMElement $XT)
    {
        $o6 = UtilitiesSAML::xpQuery($XT, "\x2e\x2f\x73\x61\155\x6c\137\x61\163\x73\145\162\164\151\x6f\x6e\x3a\103\157\156\144\151\164\x69\157\x6e\x73");
        if (empty($o6)) {
            goto J9;
        }
        if (count($o6) > 1) {
            goto Tt;
        }
        goto Oy;
        J9:
        return;
        goto Oy;
        Tt:
        throw new Exception("\x4d\157\x72\145\x20\164\150\x61\156\40\157\x6e\145\x20\x3c\x73\x61\155\x6c\x3a\x43\x6f\156\x64\x69\164\x69\x6f\x6e\163\x3e\x20\x69\x6e\x20\x3c\x73\x61\155\x6c\72\101\163\163\145\162\x74\x69\x6f\x6e\76\x2e");
        Oy:
        $o6 = $o6[0];
        if (!$o6->hasAttribute("\116\x6f\x74\102\x65\146\157\162\x65")) {
            goto zq;
        }
        $qK = UtilitiesSAML::xsDateTimeToTimestamp($o6->getAttribute("\116\x6f\x74\102\x65\x66\x6f\162\145"));
        if (!($this->notBefore === NULL || $this->notBefore < $qK)) {
            goto OF;
        }
        $this->notBefore = $qK;
        OF:
        zq:
        if (!$o6->hasAttribute("\x4e\157\164\117\156\117\x72\x41\146\164\x65\x72")) {
            goto i2;
        }
        $Zc = UtilitiesSAML::xsDateTimeToTimestamp($o6->getAttribute("\116\157\x74\117\156\x4f\x72\x41\146\x74\145\x72"));
        if (!($this->notOnOrAfter === NULL || $this->notOnOrAfter > $Zc)) {
            goto pK;
        }
        $this->notOnOrAfter = $Zc;
        pK:
        i2:
        $mj = $o6->firstChild;
        SB:
        if (!($mj !== NULL)) {
            goto pG;
        }
        if (!$mj instanceof DOMText) {
            goto up;
        }
        goto ri;
        up:
        if (!($mj->namespaceURI !== "\165\162\x6e\72\157\141\163\x69\163\x3a\x6e\141\x6d\145\x73\x3a\164\143\x3a\123\x41\x4d\x4c\x3a\x32\x2e\x30\x3a\141\x73\163\x65\162\164\x69\157\156")) {
            goto ZN;
        }
        throw new Exception("\x55\156\x6b\156\x6f\167\x6e\x20\156\141\x6d\x65\x73\x70\x61\x63\x65\40\157\146\40\143\x6f\156\144\x69\x74\x69\157\156\x3a\x20" . var_export($mj->namespaceURI, TRUE));
        ZN:
        switch ($mj->localName) {
            case "\101\x75\x64\151\145\x6e\x63\145\x52\145\163\x74\x72\x69\x63\x74\151\x6f\156":
                $i5 = UtilitiesSAML::extractStrings($mj, "\165\162\x6e\72\157\141\x73\x69\163\x3a\x6e\x61\155\145\x73\72\164\143\x3a\123\x41\115\x4c\x3a\62\56\x30\72\x61\x73\x73\x65\162\164\151\157\x6e", "\101\x75\x64\151\x65\156\143\145");
                if ($this->validAudiences === NULL) {
                    goto Vh;
                }
                $this->validAudiences = array_intersect($this->validAudiences, $i5);
                goto cl;
                Vh:
                $this->validAudiences = $i5;
                cl:
                goto jd;
            case "\x4f\156\x65\124\x69\x6d\145\125\163\145":
                goto jd;
            case "\x50\162\157\170\171\122\145\163\x74\x72\x69\143\164\x69\x6f\156":
                goto jd;
            default:
                throw new Exception("\x55\x6e\x6b\x6e\157\167\x6e\40\143\x6f\156\144\151\164\151\157\x6e\x3a\x20" . var_export($mj->localName, TRUE));
        }
        wk:
        jd:
        ri:
        $mj = $mj->nextSibling;
        goto SB;
        pG:
    }
    private function parseAuthnStatement(DOMElement $XT)
    {
        $CT = UtilitiesSAML::xpQuery($XT, "\56\x2f\163\141\x6d\154\137\141\163\163\x65\x72\164\x69\x6f\x6e\x3a\101\165\x74\x68\x6e\123\164\x61\164\145\155\145\156\x74");
        if (empty($CT)) {
            goto v3;
        }
        if (count($CT) > 1) {
            goto nE;
        }
        goto Xy;
        v3:
        $this->authnInstant = NULL;
        return;
        goto Xy;
        nE:
        throw new Exception("\115\157\x72\145\x20\164\x68\x61\164\x20\157\156\x65\x20\74\x73\141\x6d\154\72\101\165\x74\x68\156\x53\164\x61\164\145\155\x65\156\x74\x3e\x20\x69\156\x20\x3c\x73\141\x6d\x6c\x3a\x41\163\163\x65\162\x74\x69\x6f\x6e\76\40\156\157\164\x20\163\165\x70\x70\x6f\x72\x74\145\144\x2e");
        Xy:
        $lb = $CT[0];
        if ($lb->hasAttribute("\x41\x75\x74\x68\156\111\156\x73\164\141\156\x74")) {
            goto vK;
        }
        throw new Exception("\115\x69\163\163\x69\156\x67\40\x72\145\161\x75\151\162\145\144\x20\101\x75\x74\x68\156\x49\x6e\163\x74\141\156\164\x20\x61\x74\164\x72\151\x62\165\164\145\40\157\156\x20\74\163\141\155\154\72\101\x75\x74\x68\x6e\123\x74\141\x74\x65\x6d\145\156\x74\x3e\56");
        vK:
        $this->authnInstant = UtilitiesSAML::xsDateTimeToTimestamp($lb->getAttribute("\x41\165\x74\150\x6e\111\x6e\x73\164\141\156\x74"));
        if (!$lb->hasAttribute("\123\145\x73\163\x69\157\156\116\157\164\117\x6e\117\x72\101\146\164\145\162")) {
            goto iE;
        }
        $this->sessionNotOnOrAfter = UtilitiesSAML::xsDateTimeToTimestamp($lb->getAttribute("\x53\x65\x73\163\x69\x6f\x6e\x4e\157\x74\x4f\x6e\x4f\162\x41\146\164\145\162"));
        iE:
        if (!$lb->hasAttribute("\123\145\x73\163\x69\x6f\156\111\156\x64\145\x78")) {
            goto NK;
        }
        $this->sessionIndex = $lb->getAttribute("\x53\x65\x73\x73\x69\157\x6e\x49\x6e\x64\145\x78");
        NK:
        $this->parseAuthnContext($lb);
    }
    private function parseAuthnContext(DOMElement $uQ)
    {
        $he = UtilitiesSAML::xpQuery($uQ, "\x2e\57\x73\141\x6d\154\137\141\x73\163\x65\x72\164\151\x6f\156\72\101\165\x74\150\156\103\x6f\156\164\x65\x78\164");
        if (count($he) > 1) {
            goto Qb;
        }
        if (empty($he)) {
            goto Ve;
        }
        goto PB;
        Qb:
        throw new Exception("\x4d\x6f\x72\145\40\x74\x68\x61\156\x20\157\156\145\x20\x3c\x73\x61\155\x6c\x3a\101\165\164\x68\156\103\x6f\156\164\x65\170\164\x3e\40\151\156\x20\74\x73\141\155\x6c\x3a\x41\165\164\150\156\x53\164\x61\164\x65\x6d\x65\x6e\164\x3e\x2e");
        goto PB;
        Ve:
        throw new Exception("\115\x69\163\163\x69\x6e\x67\40\162\145\161\x75\x69\x72\x65\x64\40\74\163\141\155\154\x3a\x41\x75\164\150\156\x43\157\156\x74\145\x78\164\x3e\x20\x69\x6e\40\74\163\141\x6d\154\72\x41\165\x74\x68\x6e\x53\164\141\x74\145\x6d\145\156\164\76\x2e");
        PB:
        $qI = $he[0];
        $NQ = UtilitiesSAML::xpQuery($qI, "\56\57\x73\x61\155\x6c\137\141\163\x73\x65\162\164\151\157\x6e\72\101\165\164\150\156\103\x6f\x6e\164\x65\170\164\x44\x65\x63\x6c\x52\145\146");
        if (count($NQ) > 1) {
            goto ks;
        }
        if (count($NQ) === 1) {
            goto dM;
        }
        goto YR;
        ks:
        throw new Exception("\x4d\x6f\x72\x65\40\164\x68\x61\x6e\x20\157\156\x65\40\x3c\163\141\155\154\72\x41\165\x74\x68\156\x43\157\156\164\x65\170\164\x44\145\143\x6c\122\x65\146\x3e\40\146\157\x75\156\x64\77");
        goto YR;
        dM:
        $this->setAuthnContextDeclRef(trim($NQ[0]->textContent));
        YR:
        $ic = UtilitiesSAML::xpQuery($qI, "\56\x2f\163\141\x6d\x6c\x5f\141\x73\163\145\x72\x74\151\x6f\x6e\x3a\101\165\164\x68\156\103\x6f\156\x74\145\x78\164\104\145\143\x6c");
        if (count($ic) > 1) {
            goto N1;
        }
        if (count($ic) === 1) {
            goto g8;
        }
        goto JW;
        N1:
        throw new Exception("\x4d\x6f\162\145\40\164\150\141\156\40\157\x6e\x65\40\74\163\x61\155\x6c\72\x41\x75\164\150\x6e\103\157\156\164\x65\170\x74\x44\145\x63\154\76\x20\x66\x6f\x75\156\144\77");
        goto JW;
        g8:
        $this->setAuthnContextDecl(new SAML2_XML_Chunk($ic[0]));
        JW:
        $OJ = UtilitiesSAML::xpQuery($qI, "\x2e\x2f\x73\x61\155\154\137\141\163\163\145\162\164\151\157\x6e\x3a\x41\165\x74\150\x6e\x43\157\156\164\x65\x78\x74\103\154\141\x73\163\x52\x65\x66");
        if (count($OJ) > 1) {
            goto Y1;
        }
        if (count($OJ) === 1) {
            goto pi;
        }
        goto D0;
        Y1:
        throw new Exception("\x4d\x6f\162\x65\x20\x74\x68\x61\156\40\x6f\x6e\x65\40\x3c\163\x61\155\x6c\x3a\101\165\x74\x68\156\x43\x6f\x6e\164\145\x78\164\x43\x6c\141\x73\x73\122\x65\146\76\40\x69\x6e\x20\74\x73\x61\155\x6c\x3a\101\x75\x74\x68\156\x43\157\x6e\x74\x65\x78\164\76\56");
        goto D0;
        pi:
        $this->setAuthnContextClassRef(trim($OJ[0]->textContent));
        D0:
        if (!(empty($this->authnContextClassRef) && empty($this->authnContextDecl) && empty($this->authnContextDeclRef))) {
            goto Fk;
        }
        throw new Exception("\x4d\151\x73\x73\151\156\147\40\x65\151\164\x68\145\x72\40\x3c\163\141\155\x6c\72\101\x75\164\150\156\103\157\x6e\x74\x65\170\x74\x43\154\141\x73\x73\x52\x65\146\x3e\40\157\x72\40\74\163\x61\x6d\x6c\x3a\x41\165\x74\150\156\x43\157\x6e\164\145\x78\164\104\145\x63\x6c\x52\x65\x66\76\x20\x6f\x72\40\74\163\x61\155\154\72\x41\x75\164\150\x6e\x43\157\156\164\145\x78\x74\x44\145\143\154\76");
        Fk:
        $this->AuthenticatingAuthority = UtilitiesSAML::extractStrings($qI, "\x75\x72\156\72\157\141\163\x69\163\72\x6e\141\155\145\x73\72\164\x63\x3a\x53\x41\115\114\72\x32\x2e\x30\72\x61\163\163\145\x72\164\x69\x6f\156", "\101\x75\164\150\145\156\164\x69\x63\141\164\151\156\147\101\x75\x74\150\157\162\x69\164\x79");
    }
    private function parseAttributes(DOMElement $XT)
    {
        $CX = TRUE;
        $VM = UtilitiesSAML::xpQuery($XT, "\x2e\57\163\x61\155\154\x5f\x61\163\x73\145\162\164\x69\157\x6e\72\101\x74\164\162\x69\x62\165\164\145\123\164\141\x74\145\155\145\156\164\x2f\x73\141\155\154\137\x61\163\163\x65\162\x74\x69\x6f\x6e\x3a\x41\164\164\162\x69\x62\165\164\x65");
        foreach ($VM as $kk) {
            if ($kk->hasAttribute("\x4e\141\x6d\x65")) {
                goto sA;
            }
            throw new Exception("\115\151\163\163\x69\156\147\40\x6e\141\155\x65\x20\x6f\x6e\40\x3c\163\x61\155\x6c\72\x41\164\x74\x72\151\142\165\164\x65\76\40\x65\x6c\x65\x6d\145\x6e\x74\56");
            sA:
            $k4 = $kk->getAttribute("\x4e\x61\x6d\x65");
            if ($kk->hasAttribute("\116\141\x6d\x65\106\157\162\x6d\141\x74")) {
                goto J_;
            }
            $Dt = "\165\162\156\x3a\x6f\141\163\x69\x73\x3a\156\141\155\x65\163\x3a\x74\143\x3a\x53\101\115\114\x3a\x31\56\x31\x3a\x6e\141\155\145\151\144\x2d\146\x6f\x72\155\x61\164\x3a\165\x6e\x73\x70\145\143\151\146\x69\x65\144";
            goto lL;
            J_:
            $Dt = $kk->getAttribute("\116\x61\155\x65\106\157\162\x6d\x61\x74");
            lL:
            if ($CX) {
                goto fk;
            }
            if (!($this->nameFormat !== $Dt)) {
                goto s2;
            }
            $this->nameFormat = "\165\x72\x6e\72\x6f\141\x73\151\x73\72\x6e\x61\155\145\x73\72\x74\x63\72\x53\101\115\x4c\x3a\61\x2e\x31\72\x6e\141\155\x65\x69\144\55\x66\157\x72\x6d\x61\x74\72\x75\156\163\x70\145\x63\x69\146\151\145\144";
            s2:
            goto bE;
            fk:
            $this->nameFormat = $Dt;
            $CX = FALSE;
            bE:
            if (array_key_exists($k4, $this->attributes)) {
                goto q7;
            }
            $this->attributes[$k4] = array();
            q7:
            $ay = UtilitiesSAML::xpQuery($kk, "\x2e\57\163\141\155\x6c\x5f\141\163\x73\145\162\x74\151\x6f\x6e\72\101\x74\x74\162\x69\142\x75\x74\x65\126\141\154\x75\145");
            foreach ($ay as $w7) {
                $this->attributes[$k4][] = trim($w7->textContent);
                Iw:
            }
            Ne:
            y0:
        }
        t1:
    }
    private function parseEncryptedAttributes(DOMElement $XT)
    {
        $this->encryptedAttribute = UtilitiesSAML::xpQuery($XT, "\x2e\x2f\163\141\x6d\154\x5f\141\x73\x73\145\162\164\x69\157\156\72\x41\164\x74\162\x69\142\x75\164\145\123\164\141\x74\145\x6d\145\156\164\57\x73\x61\x6d\154\137\x61\x73\163\x65\162\164\151\x6f\156\72\105\x6e\143\x72\x79\x70\x74\x65\x64\x41\x74\x74\162\x69\142\165\164\x65");
    }
    private function parseSignature(DOMElement $XT)
    {
        $SD = UtilitiesSAML::validateElement($XT);
        if (!($SD !== FALSE)) {
            goto l2;
        }
        $this->wasSignedAtConstruction = TRUE;
        $this->certificates = $SD["\103\x65\162\164\151\146\151\143\141\x74\x65\163"];
        $this->signatureData = $SD;
        l2:
    }
    public function validate(XMLSecurityKeySAML $da)
    {
        if (!($this->signatureData === NULL)) {
            goto MJ;
        }
        return FALSE;
        MJ:
        UtilitiesSAML::validateSignature($this->signatureData, $da);
        return TRUE;
    }
    public function getId()
    {
        return $this->id;
    }
    public function setId($Yd)
    {
        $this->id = $Yd;
    }
    public function getIssueInstant()
    {
        return $this->issueInstant;
    }
    public function setIssueInstant($L6)
    {
        $this->issueInstant = $L6;
    }
    public function getIssuer()
    {
        return $this->issuer;
    }
    public function setIssuer($F0)
    {
        $this->issuer = $F0;
    }
    public function getNameId()
    {
        if (!($this->encryptedNameId !== NULL)) {
            goto rf;
        }
        throw new Exception("\x41\x74\x74\145\x6d\160\164\145\x64\40\164\x6f\40\162\x65\x74\x72\x69\145\166\145\x20\145\156\x63\x72\x79\x70\164\145\x64\40\x4e\141\x6d\145\111\x44\x20\167\151\x74\x68\x6f\x75\x74\40\x64\145\143\x72\x79\x70\x74\x69\156\x67\x20\x69\164\40\146\x69\x72\x73\x74\x2e");
        rf:
        return $this->nameId;
    }
    public function setNameId($eS)
    {
        $this->nameId = $eS;
    }
    public function isNameIdEncrypted()
    {
        if (!($this->encryptedNameId !== NULL)) {
            goto m0;
        }
        return TRUE;
        m0:
        return FALSE;
    }
    public function encryptNameId(XMLSecurityKeySAML $da)
    {
        $JK = new DOMDocument();
        $bx = $JK->createElement("\x72\157\x6f\164");
        $JK->appendChild($bx);
        UtilitiesSAML::addNameId($bx, $this->nameId);
        $eS = $bx->firstChild;
        UtilitiesSAML::getContainer()->debugMessage($eS, "\145\156\143\162\x79\160\x74");
        $l6 = new XMLSecEncSAML();
        $l6->setNode($eS);
        $l6->type = XMLSecEncSAML::Element;
        $ES = new XMLSecurityKeySAML(XMLSecurityKeySAML::AES128_CBC);
        $ES->generateSessionKey();
        $l6->encryptKey($da, $ES);
        $this->encryptedNameId = $l6->encryptNode($ES);
        $this->nameId = NULL;
    }
    public function decryptNameId(XMLSecurityKeySAML $da, array $Vy = array())
    {
        if (!($this->encryptedNameId === NULL)) {
            goto MS;
        }
        return;
        MS:
        $eS = UtilitiesSAML::decryptElement($this->encryptedNameId, $da, $Vy);
        UtilitiesSAML::getContainer()->debugMessage($eS, "\x64\145\143\x72\x79\x70\x74");
        $this->nameId = UtilitiesSAML::parseNameId($eS);
        $this->encryptedNameId = NULL;
    }
    public function decryptAttributes(XMLSecurityKeySAML $da, array $Vy = array())
    {
        if (!($this->encryptedAttribute === NULL)) {
            goto BD;
        }
        return;
        BD:
        $CX = TRUE;
        $VM = $this->encryptedAttribute;
        foreach ($VM as $Kc) {
            $kk = UtilitiesSAML::decryptElement($Kc->getElementsByTagName("\x45\156\x63\x72\171\x70\x74\x65\144\x44\x61\164\x61")->item(0), $da, $Vy);
            if ($kk->hasAttribute("\x4e\x61\x6d\145")) {
                goto wD;
            }
            throw new Exception("\115\151\x73\163\151\156\147\x20\156\x61\155\x65\40\157\x6e\40\x3c\x73\x61\155\x6c\x3a\101\164\164\162\151\142\x75\x74\x65\76\x20\145\x6c\x65\x6d\145\x6e\164\56");
            wD:
            $k4 = $kk->getAttribute("\x4e\x61\x6d\145");
            if ($kk->hasAttribute("\x4e\x61\x6d\x65\106\x6f\x72\155\x61\164")) {
                goto PO;
            }
            $Dt = "\165\x72\156\72\157\141\x73\x69\163\x3a\x6e\x61\x6d\x65\x73\72\x74\143\72\123\x41\x4d\x4c\72\62\56\x30\72\x61\164\164\x72\x6e\141\155\145\55\146\157\x72\x6d\141\x74\x3a\165\156\163\160\x65\143\151\146\151\x65\x64";
            goto om;
            PO:
            $Dt = $kk->getAttribute("\116\x61\x6d\145\x46\157\162\x6d\141\164");
            om:
            if ($CX) {
                goto h8;
            }
            if (!($this->nameFormat !== $Dt)) {
                goto QE;
            }
            $this->nameFormat = "\165\162\x6e\72\157\141\163\x69\163\72\x6e\x61\155\x65\163\x3a\164\143\x3a\123\x41\115\x4c\x3a\62\56\x30\72\141\x74\164\162\156\141\x6d\x65\x2d\146\157\x72\155\141\x74\x3a\x75\x6e\163\x70\145\143\x69\146\151\145\x64";
            QE:
            goto Dc;
            h8:
            $this->nameFormat = $Dt;
            $CX = FALSE;
            Dc:
            if (array_key_exists($k4, $this->attributes)) {
                goto vR;
            }
            $this->attributes[$k4] = array();
            vR:
            $ay = UtilitiesSAML::xpQuery($kk, "\56\x2f\x73\141\155\154\137\141\x73\x73\x65\x72\164\151\157\156\x3a\101\164\164\162\x69\142\165\164\145\x56\141\154\165\x65");
            foreach ($ay as $w7) {
                $this->attributes[$k4][] = trim($w7->textContent);
                Lw:
            }
            d3:
            Se:
        }
        Jr:
    }
    public function getNotBefore()
    {
        return $this->notBefore;
    }
    public function setNotBefore($qK)
    {
        $this->notBefore = $qK;
    }
    public function getNotOnOrAfter()
    {
        return $this->notOnOrAfter;
    }
    public function setNotOnOrAfter($Zc)
    {
        $this->notOnOrAfter = $Zc;
    }
    public function setEncryptedAttributes($tF)
    {
        $this->requiredEncAttributes = $tF;
    }
    public function getValidAudiences()
    {
        return $this->validAudiences;
    }
    public function setValidAudiences(array $Ay = NULL)
    {
        $this->validAudiences = $Ay;
    }
    public function getAuthnInstant()
    {
        return $this->authnInstant;
    }
    public function setAuthnInstant($lt)
    {
        $this->authnInstant = $lt;
    }
    public function getSessionNotOnOrAfter()
    {
        return $this->sessionNotOnOrAfter;
    }
    public function setSessionNotOnOrAfter($w5)
    {
        $this->sessionNotOnOrAfter = $w5;
    }
    public function getSessionIndex()
    {
        return $this->sessionIndex;
    }
    public function setSessionIndex($Hh)
    {
        $this->sessionIndex = $Hh;
    }
    public function getAuthnContext()
    {
        if (empty($this->authnContextClassRef)) {
            goto H0;
        }
        return $this->authnContextClassRef;
        H0:
        if (empty($this->authnContextDeclRef)) {
            goto LE;
        }
        return $this->authnContextDeclRef;
        LE:
        return NULL;
    }
    public function setAuthnContext($Vo)
    {
        $this->setAuthnContextClassRef($Vo);
    }
    public function getAuthnContextClassRef()
    {
        return $this->authnContextClassRef;
    }
    public function setAuthnContextClassRef($Ft)
    {
        $this->authnContextClassRef = $Ft;
    }
    public function setAuthnContextDecl(SAML2_XML_Chunk $vU)
    {
        if (empty($this->authnContextDeclRef)) {
            goto zU;
        }
        throw new Exception("\101\165\x74\x68\156\x43\x6f\156\164\x65\x78\164\104\145\143\154\x52\145\x66\40\x69\x73\40\x61\x6c\x72\x65\141\144\x79\40\x72\145\x67\x69\x73\164\x65\162\x65\144\41\x20\115\141\171\x20\157\x6e\x6c\171\x20\150\141\166\145\40\145\151\x74\150\145\162\40\141\x20\x44\x65\x63\154\40\x6f\x72\x20\141\x20\104\x65\143\x6c\x52\x65\x66\54\40\x6e\x6f\x74\x20\x62\x6f\x74\150\x21");
        zU:
        $this->authnContextDecl = $vU;
    }
    public function getAuthnContextDecl()
    {
        return $this->authnContextDecl;
    }
    public function setAuthnContextDeclRef($h8)
    {
        if (empty($this->authnContextDecl)) {
            goto PX;
        }
        throw new Exception("\x41\x75\164\150\156\x43\157\156\164\x65\170\x74\104\x65\x63\154\x20\151\163\x20\141\154\x72\145\x61\144\171\x20\162\x65\x67\x69\x73\164\x65\162\145\x64\x21\40\x4d\x61\x79\40\157\x6e\154\x79\40\x68\x61\166\x65\x20\x65\x69\164\150\x65\x72\x20\x61\x20\104\145\x63\154\40\x6f\x72\x20\141\40\104\x65\143\x6c\122\145\146\54\40\x6e\x6f\x74\x20\142\157\164\x68\41");
        PX:
        $this->authnContextDeclRef = $h8;
    }
    public function getAuthnContextDeclRef()
    {
        return $this->authnContextDeclRef;
    }
    public function getAuthenticatingAuthority()
    {
        return $this->AuthenticatingAuthority;
    }
    public function setAuthenticatingAuthority($hn)
    {
        $this->AuthenticatingAuthority = $hn;
    }
    public function getAttributes()
    {
        return $this->attributes;
    }
    public function setAttributes(array $VM)
    {
        $this->attributes = $VM;
    }
    public function getAttributeNameFormat()
    {
        return $this->nameFormat;
    }
    public function setAttributeNameFormat($Dt)
    {
        $this->nameFormat = $Dt;
    }
    public function getSubjectConfirmation()
    {
        return $this->SubjectConfirmation;
    }
    public function setSubjectConfirmation(array $nm)
    {
        $this->SubjectConfirmation = $nm;
    }
    public function getSignatureKey()
    {
        return $this->signatureKey;
    }
    public function setSignatureKey(XMLSecurityKeySAML $I3 = NULL)
    {
        $this->signatureKey = $I3;
    }
    public function getEncryptionKey()
    {
        return $this->encryptionKey;
    }
    public function setEncryptionKey(XMLSecurityKeySAML $d7 = NULL)
    {
        $this->encryptionKey = $d7;
    }
    public function setCertificates(array $KJ)
    {
        $this->certificates = $KJ;
    }
    public function getCertificates()
    {
        return $this->certificates;
    }
    public function getWasSignedAtConstruction()
    {
        return $this->wasSignedAtConstruction;
    }
    public function toXML(DOMNode $i0 = NULL)
    {
        if ($i0 === NULL) {
            goto qL;
        }
        $jI = $i0->ownerDocument;
        goto sJ;
        qL:
        $jI = new DOMDocument();
        $i0 = $jI;
        sJ:
        $bx = $jI->createElementNS("\165\x72\x6e\x3a\157\x61\163\x69\x73\x3a\x6e\141\155\x65\163\72\164\143\x3a\123\x41\115\114\72\62\x2e\60\72\x61\x73\x73\145\x72\x74\x69\157\x6e", "\163\141\155\154\72" . "\101\163\163\x65\162\x74\x69\x6f\156");
        $i0->appendChild($bx);
        $bx->setAttributeNS("\x75\162\156\72\x6f\x61\163\151\x73\72\156\141\x6d\x65\163\x3a\x74\x63\x3a\x53\x41\x4d\114\x3a\x32\56\60\x3a\160\162\x6f\x74\x6f\x63\157\154", "\x73\x61\x6d\x6c\x70\72\164\x6d\x70", "\164\155\160");
        $bx->removeAttributeNS("\165\x72\156\72\x6f\141\163\x69\163\x3a\x6e\x61\x6d\x65\163\x3a\x74\143\x3a\123\x41\115\x4c\72\x32\x2e\60\72\x70\162\x6f\x74\x6f\143\x6f\154", "\164\x6d\x70");
        $bx->setAttributeNS("\x68\x74\x74\x70\72\57\x2f\x77\167\x77\56\167\x33\x2e\157\162\x67\57\x32\x30\x30\61\57\130\x4d\x4c\x53\143\150\145\155\141\x2d\x69\x6e\163\164\141\x6e\143\145", "\x78\163\151\72\x74\x6d\160", "\164\155\160");
        $bx->removeAttributeNS("\150\164\x74\160\x3a\x2f\57\x77\x77\x77\x2e\167\x33\x2e\x6f\x72\147\57\62\x30\x30\61\57\x58\x4d\x4c\123\x63\150\x65\x6d\x61\55\x69\156\x73\164\x61\156\x63\145", "\164\155\x70");
        $bx->setAttributeNS("\x68\x74\x74\160\72\57\x2f\x77\167\167\56\167\63\x2e\157\x72\x67\x2f\62\60\x30\x31\57\130\115\114\123\x63\150\145\x6d\141", "\170\163\x3a\164\155\x70", "\164\155\x70");
        $bx->removeAttributeNS("\x68\164\164\160\72\57\x2f\167\167\x77\x2e\x77\63\x2e\x6f\162\x67\57\62\60\x30\x31\x2f\130\x4d\x4c\123\x63\x68\145\x6d\x61", "\x74\x6d\x70");
        $bx->setAttribute("\x49\104", $this->id);
        $bx->setAttribute("\x56\145\x72\x73\151\x6f\x6e", "\x32\56\60");
        $bx->setAttribute("\x49\x73\163\165\145\x49\x6e\x73\x74\141\156\164", gmdate("\131\x2d\155\55\144\134\x54\x48\72\151\72\x73\x5c\132", $this->issueInstant));
        $F0 = UtilitiesSAML::addString($bx, "\165\x72\x6e\72\x6f\x61\163\151\163\72\x6e\141\x6d\x65\x73\72\x74\143\x3a\123\x41\x4d\x4c\72\62\x2e\60\72\141\163\163\145\x72\164\151\157\x6e", "\x73\x61\155\154\72\x49\x73\163\165\x65\162", $this->issuer);
        $this->addSubject($bx);
        $this->addConditions($bx);
        $this->addAuthnStatement($bx);
        if ($this->requiredEncAttributes == FALSE) {
            goto f3;
        }
        $this->addEncryptedAttributeStatement($bx);
        goto nI;
        f3:
        $this->addAttributeStatement($bx);
        nI:
        if (!($this->signatureKey !== NULL)) {
            goto L7;
        }
        UtilitiesSAML::insertSignature($this->signatureKey, $this->certificates, $bx, $F0->nextSibling);
        L7:
        return $bx;
    }
    private function addSubject(DOMElement $bx)
    {
        if (!($this->nameId === NULL && $this->encryptedNameId === NULL)) {
            goto T4;
        }
        return;
        T4:
        $yh = $bx->ownerDocument->createElementNS("\x75\162\156\x3a\157\x61\x73\151\x73\72\156\141\x6d\x65\x73\72\x74\x63\72\123\101\115\114\x3a\x32\x2e\x30\x3a\x61\x73\x73\x65\162\x74\x69\157\x6e", "\163\141\155\154\72\x53\165\x62\152\x65\143\x74");
        $bx->appendChild($yh);
        if ($this->encryptedNameId === NULL) {
            goto QG;
        }
        $oK = $yh->ownerDocument->createElementNS("\165\x72\x6e\72\157\141\163\151\163\x3a\x6e\141\x6d\145\163\72\164\x63\72\x53\x41\x4d\114\72\62\x2e\60\72\141\x73\x73\x65\x72\164\x69\157\x6e", "\163\141\155\154\72" . "\105\x6e\143\x72\171\160\x74\145\144\111\104");
        $yh->appendChild($oK);
        $oK->appendChild($yh->ownerDocument->importNode($this->encryptedNameId, TRUE));
        goto K9;
        QG:
        UtilitiesSAML::addNameId($yh, $this->nameId);
        K9:
        foreach ($this->SubjectConfirmation as $fM) {
            $fM->toXML($yh);
            Op:
        }
        C_:
    }
    private function addConditions(DOMElement $bx)
    {
        $jI = $bx->ownerDocument;
        $o6 = $jI->createElementNS("\165\162\x6e\x3a\157\x61\163\151\163\x3a\x6e\141\x6d\145\163\72\164\143\x3a\123\x41\x4d\x4c\72\x32\x2e\x30\72\x61\x73\x73\x65\162\x74\151\157\156", "\x73\141\x6d\x6c\x3a\103\157\156\x64\x69\164\151\157\x6e\x73");
        $bx->appendChild($o6);
        if (!($this->notBefore !== NULL)) {
            goto vu;
        }
        $o6->setAttribute("\x4e\157\x74\102\145\x66\x6f\162\x65", gmdate("\x59\x2d\x6d\x2d\x64\x5c\x54\110\x3a\x69\72\163\x5c\132", $this->notBefore));
        vu:
        if (!($this->notOnOrAfter !== NULL)) {
            goto xx;
        }
        $o6->setAttribute("\116\157\x74\117\x6e\117\162\x41\146\x74\145\x72", gmdate("\131\x2d\x6d\55\x64\x5c\124\110\x3a\151\72\x73\134\132", $this->notOnOrAfter));
        xx:
        if (!($this->validAudiences !== NULL)) {
            goto XT;
        }
        $yk = $jI->createElementNS("\x75\x72\x6e\x3a\157\141\163\151\x73\x3a\156\x61\155\x65\x73\x3a\x74\x63\72\x53\101\x4d\x4c\72\62\56\60\72\141\163\x73\x65\x72\164\x69\157\156", "\x73\141\155\x6c\x3a\101\165\144\x69\x65\x6e\143\x65\122\145\163\164\162\x69\x63\x74\151\x6f\x6e");
        $o6->appendChild($yk);
        UtilitiesSAML::addStrings($yk, "\165\162\156\x3a\x6f\141\163\x69\163\72\156\x61\155\145\x73\72\164\143\72\x53\x41\115\114\x3a\62\x2e\60\72\x61\163\x73\x65\162\164\x69\x6f\x6e", "\x73\x61\x6d\154\x3a\101\x75\x64\x69\145\x6e\143\x65", FALSE, $this->validAudiences);
        XT:
    }
    private function addAuthnStatement(DOMElement $bx)
    {
        if (!($this->authnInstant === NULL || $this->authnContextClassRef === NULL && $this->authnContextDecl === NULL && $this->authnContextDeclRef === NULL)) {
            goto Na;
        }
        return;
        Na:
        $jI = $bx->ownerDocument;
        $uQ = $jI->createElementNS("\165\x72\156\x3a\157\x61\x73\x69\x73\72\156\141\155\x65\x73\72\164\143\72\x53\101\x4d\x4c\72\x32\56\x30\72\141\163\x73\x65\162\x74\151\157\x6e", "\x73\x61\x6d\154\x3a\x41\165\x74\150\156\x53\164\x61\x74\x65\155\145\x6e\164");
        $bx->appendChild($uQ);
        $uQ->setAttribute("\101\165\x74\x68\x6e\111\x6e\163\x74\x61\156\164", gmdate("\x59\x2d\155\x2d\144\x5c\x54\110\x3a\x69\72\163\x5c\132", $this->authnInstant));
        if (!($this->sessionNotOnOrAfter !== NULL)) {
            goto Rd;
        }
        $uQ->setAttribute("\123\x65\x73\163\151\x6f\156\x4e\x6f\164\117\156\117\162\x41\x66\164\x65\162", gmdate("\131\x2d\155\55\144\x5c\x54\110\72\x69\72\163\x5c\x5a", $this->sessionNotOnOrAfter));
        Rd:
        if (!($this->sessionIndex !== NULL)) {
            goto HK;
        }
        $uQ->setAttribute("\x53\145\x73\x73\x69\x6f\x6e\x49\156\x64\145\x78", $this->sessionIndex);
        HK:
        $qI = $jI->createElementNS("\x75\162\x6e\x3a\157\x61\x73\151\163\72\x6e\x61\155\145\x73\72\164\x63\72\x53\x41\115\114\x3a\62\56\60\72\141\163\163\x65\x72\x74\x69\157\x6e", "\163\141\x6d\154\x3a\x41\165\164\x68\156\x43\157\156\x74\x65\170\x74");
        $uQ->appendChild($qI);
        if (empty($this->authnContextClassRef)) {
            goto Wy;
        }
        UtilitiesSAML::addString($qI, "\165\162\x6e\72\x6f\141\x73\x69\x73\x3a\156\x61\x6d\145\163\x3a\x74\x63\x3a\123\101\x4d\x4c\72\x32\56\60\72\x61\x73\163\145\162\x74\151\157\156", "\163\141\x6d\x6c\72\101\x75\x74\150\x6e\x43\x6f\156\164\x65\170\164\x43\154\141\x73\x73\122\x65\146", $this->authnContextClassRef);
        Wy:
        if (empty($this->authnContextDecl)) {
            goto b2;
        }
        $this->authnContextDecl->toXML($qI);
        b2:
        if (empty($this->authnContextDeclRef)) {
            goto Wt;
        }
        UtilitiesSAML::addString($qI, "\165\162\156\72\x6f\141\163\151\x73\72\x6e\141\155\x65\163\72\164\143\x3a\123\x41\x4d\x4c\72\x32\x2e\x30\x3a\141\163\x73\x65\x72\x74\x69\157\x6e", "\x73\x61\155\154\x3a\x41\165\164\150\156\103\157\x6e\x74\x65\x78\x74\x44\x65\x63\x6c\x52\x65\146", $this->authnContextDeclRef);
        Wt:
        UtilitiesSAML::addStrings($qI, "\165\162\156\x3a\x6f\x61\x73\x69\163\72\x6e\141\x6d\x65\x73\x3a\x74\x63\x3a\x53\101\115\114\x3a\62\56\60\x3a\x61\x73\x73\x65\162\x74\151\x6f\x6e", "\x73\141\155\154\72\101\x75\x74\x68\145\x6e\164\151\x63\x61\164\x69\x6e\147\x41\165\x74\x68\157\x72\151\164\x79", FALSE, $this->AuthenticatingAuthority);
    }
    private function addAttributeStatement(DOMElement $bx)
    {
        if (!empty($this->attributes)) {
            goto YL;
        }
        return;
        YL:
        $jI = $bx->ownerDocument;
        $sQ = $jI->createElementNS("\165\162\x6e\72\x6f\x61\x73\151\x73\x3a\x6e\141\x6d\145\163\x3a\164\x63\72\x53\x41\x4d\x4c\72\x32\56\60\72\141\x73\163\x65\x72\x74\x69\x6f\156", "\x73\x61\155\x6c\72\x41\164\x74\162\151\x62\165\164\x65\123\164\x61\164\145\155\x65\156\x74");
        $bx->appendChild($sQ);
        foreach ($this->attributes as $k4 => $ay) {
            $kk = $jI->createElementNS("\x75\162\156\x3a\x6f\x61\163\x69\163\x3a\x6e\x61\x6d\145\x73\72\x74\143\72\123\101\115\x4c\x3a\62\56\60\x3a\x61\x73\163\x65\162\164\x69\157\156", "\x73\x61\x6d\x6c\72\101\164\164\162\151\x62\165\x74\145");
            $sQ->appendChild($kk);
            $kk->setAttribute("\x4e\141\x6d\145", $k4);
            if (!($this->nameFormat !== "\165\x72\x6e\72\157\x61\x73\x69\163\x3a\156\x61\x6d\x65\163\72\164\143\x3a\123\x41\x4d\114\x3a\62\x2e\60\x3a\x61\x74\x74\162\x6e\x61\x6d\145\x2d\x66\157\162\x6d\141\164\x3a\165\156\163\160\145\x63\151\x66\x69\x65\x64")) {
                goto sF;
            }
            $kk->setAttribute("\116\141\155\x65\106\x6f\x72\155\x61\164", $this->nameFormat);
            sF:
            foreach ($ay as $w7) {
                if (is_string($w7)) {
                    goto VB;
                }
                if (is_int($w7)) {
                    goto AM;
                }
                $X7 = NULL;
                goto oJ;
                VB:
                $X7 = "\170\163\72\163\164\x72\x69\x6e\147";
                goto oJ;
                AM:
                $X7 = "\x78\x73\72\151\x6e\x74\145\x67\x65\x72";
                oJ:
                $zS = $jI->createElementNS("\165\162\156\x3a\157\141\x73\151\x73\x3a\156\141\x6d\145\163\72\x74\x63\72\123\x41\x4d\x4c\x3a\62\x2e\60\72\x61\163\163\x65\x72\164\151\157\x6e", "\163\x61\155\x6c\x3a\x41\x74\164\162\x69\x62\x75\x74\145\x56\x61\x6c\165\x65");
                $kk->appendChild($zS);
                if (!($X7 !== NULL)) {
                    goto XU;
                }
                $zS->setAttributeNS("\150\164\x74\x70\72\x2f\x2f\167\x77\x77\x2e\x77\x33\x2e\157\162\147\57\62\x30\60\x31\57\x58\x4d\x4c\123\143\x68\145\x6d\x61\x2d\151\156\163\164\141\156\x63\145", "\170\x73\151\x3a\x74\171\160\x65", $X7);
                XU:
                if (!is_null($w7)) {
                    goto JZ;
                }
                $zS->setAttributeNS("\150\x74\x74\160\72\57\x2f\167\x77\167\x2e\167\x33\56\x6f\162\x67\x2f\62\60\x30\61\57\130\x4d\114\x53\143\x68\145\x6d\141\x2d\x69\156\x73\164\141\x6e\143\145", "\x78\163\x69\x3a\156\151\154", "\164\162\165\145");
                JZ:
                if ($w7 instanceof DOMNodeList) {
                    goto kE;
                }
                $zS->appendChild($jI->createTextNode($w7));
                goto uO;
                kE:
                $AM = 0;
                uo:
                if (!($AM < $w7->length)) {
                    goto yJ;
                }
                $mj = $jI->importNode($w7->item($AM), TRUE);
                $zS->appendChild($mj);
                V3:
                $AM++;
                goto uo;
                yJ:
                uO:
                Ss:
            }
            Q3:
            vr:
        }
        JD:
    }
    private function addEncryptedAttributeStatement(DOMElement $bx)
    {
        if (!($this->requiredEncAttributes == FALSE)) {
            goto yP;
        }
        return;
        yP:
        $jI = $bx->ownerDocument;
        $sQ = $jI->createElementNS("\x75\x72\156\72\157\x61\163\151\x73\x3a\156\141\155\x65\x73\x3a\164\x63\72\123\101\x4d\x4c\x3a\x32\x2e\60\72\x61\x73\x73\145\x72\164\x69\x6f\156", "\x73\141\155\154\72\x41\x74\x74\x72\151\x62\x75\164\x65\123\164\x61\164\145\x6d\145\156\164");
        $bx->appendChild($sQ);
        foreach ($this->attributes as $k4 => $ay) {
            $r6 = new DOMDocument();
            $kk = $r6->createElementNS("\165\x72\156\72\157\x61\x73\151\163\x3a\x6e\141\x6d\145\x73\72\x74\x63\x3a\123\101\x4d\x4c\x3a\x32\56\60\x3a\x61\163\163\x65\x72\x74\151\x6f\156", "\x73\x61\155\154\72\x41\164\x74\x72\x69\142\x75\x74\x65");
            $kk->setAttribute("\116\141\155\x65", $k4);
            $r6->appendChild($kk);
            if (!($this->nameFormat !== "\165\162\x6e\72\157\141\x73\x69\x73\x3a\156\x61\155\145\x73\72\x74\x63\72\x53\101\x4d\114\72\x32\x2e\x30\72\141\x74\164\162\156\x61\155\x65\x2d\146\x6f\162\155\141\164\72\x75\x6e\163\x70\x65\143\151\146\151\x65\144")) {
                goto JK;
            }
            $kk->setAttribute("\x4e\141\x6d\145\x46\x6f\162\155\141\x74", $this->nameFormat);
            JK:
            foreach ($ay as $w7) {
                if (is_string($w7)) {
                    goto VX;
                }
                if (is_int($w7)) {
                    goto Rz;
                }
                $X7 = NULL;
                goto CL;
                VX:
                $X7 = "\170\163\x3a\163\x74\x72\151\156\x67";
                goto CL;
                Rz:
                $X7 = "\x78\163\x3a\x69\156\164\145\147\145\162";
                CL:
                $zS = $r6->createElementNS("\x75\162\156\x3a\x6f\141\x73\151\x73\x3a\156\141\x6d\145\163\72\164\x63\72\123\x41\115\114\x3a\x32\x2e\x30\72\x61\163\x73\145\x72\164\151\x6f\156", "\x73\x61\155\x6c\x3a\x41\164\x74\162\151\142\x75\x74\x65\x56\x61\154\165\145");
                $kk->appendChild($zS);
                if (!($X7 !== NULL)) {
                    goto kK;
                }
                $zS->setAttributeNS("\x68\164\x74\160\x3a\x2f\x2f\167\167\x77\x2e\167\x33\x2e\x6f\162\147\57\62\60\x30\61\x2f\x58\115\114\123\x63\150\145\x6d\x61\x2d\x69\156\x73\164\141\x6e\x63\x65", "\170\163\x69\72\x74\x79\x70\x65", $X7);
                kK:
                if ($w7 instanceof DOMNodeList) {
                    goto oQ;
                }
                $zS->appendChild($r6->createTextNode($w7));
                goto Mk;
                oQ:
                $AM = 0;
                SN:
                if (!($AM < $w7->length)) {
                    goto K3;
                }
                $mj = $r6->importNode($w7->item($AM), TRUE);
                $zS->appendChild($mj);
                Pm:
                $AM++;
                goto SN;
                K3:
                Mk:
                ta:
            }
            x_:
            $ck = new XMLSecEncSAML();
            $ck->setNode($r6->documentElement);
            $ck->type = "\x68\164\x74\160\x3a\57\57\167\167\x77\x2e\x77\x33\x2e\157\162\x67\57\62\60\x30\x31\57\60\64\x2f\170\155\154\145\x6e\143\x23\x45\x6c\x65\x6d\145\156\164";
            $ES = new XMLSecurityKeySAML(XMLSecurityKeySAML::AES256_CBC);
            $ES->generateSessionKey();
            $ck->encryptKey($this->encryptionKey, $ES);
            $Z0 = $ck->encryptNode($ES);
            $Hu = $jI->createElementNS("\x75\162\156\72\x6f\141\163\151\163\72\156\141\155\145\163\72\x74\x63\72\123\x41\115\x4c\x3a\x32\56\60\x3a\x61\x73\163\x65\x72\x74\151\x6f\x6e", "\x73\x61\x6d\154\72\x45\156\143\162\171\x70\164\145\144\101\164\164\x72\x69\142\x75\x74\x65");
            $sQ->appendChild($Hu);
            $PG = $jI->importNode($Z0, TRUE);
            $Hu->appendChild($PG);
            qV:
        }
        kr:
    }
    public function getSignatureData()
    {
        return $this->signatureData;
    }
}
