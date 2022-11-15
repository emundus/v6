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
    public function __construct(DOMElement $ZQ = NULL)
    {
        $this->id = UtilitiesSAML::generateId();
        $this->issueInstant = UtilitiesSAML::generateTimestamp();
        $this->issuer = '';
        $this->authnInstant = UtilitiesSAML::generateTimestamp();
        $this->attributes = array();
        $this->nameFormat = "\165\x72\156\x3a\157\x61\163\x69\x73\x3a\156\x61\x6d\x65\163\72\x74\x63\x3a\123\101\x4d\114\x3a\x31\56\x31\72\x6e\141\x6d\145\151\144\x2d\x66\x6f\162\155\141\164\72\165\x6e\163\160\x65\143\151\146\151\145\x64";
        $this->certificates = array();
        $this->AuthenticatingAuthority = array();
        $this->SubjectConfirmation = array();
        if (!($ZQ === NULL)) {
            goto yR;
        }
        return;
        yR:
        if (!($ZQ->localName === "\x45\156\x63\x72\171\x70\164\145\144\x41\x73\x73\145\x72\x74\151\x6f\156")) {
            goto Xu;
        }
        $Vr = UtilitiesSAML::xpQuery($ZQ, "\x2e\57\x78\145\156\143\x3a\105\x6e\x63\x72\x79\160\x74\x65\x64\x44\x61\x74\x61");
        $gx = UtilitiesSAML::xpQuery($ZQ, "\56\57\x78\145\156\x63\72\105\156\x63\x72\x79\x70\x74\x65\144\x44\x61\164\x61\57\x64\163\72\113\x65\171\x49\156\146\x6f\x2f\x78\145\x6e\143\72\x45\x6e\143\162\x79\x70\x74\x65\x64\113\145\x79");
        $VT = '';
        if (empty($gx)) {
            goto ft;
        }
        $VT = $gx[0]->firstChild->getAttribute("\101\x6c\147\157\162\x69\164\150\155");
        goto KO;
        ft:
        $gx = UtilitiesSAML::xpQuery($ZQ, "\56\57\170\x65\156\x63\x3a\105\156\x63\162\x79\160\x74\145\144\x4b\145\x79\x2f\170\x65\156\x63\x3a\105\x6e\x63\x72\x79\x70\x74\x69\x6f\156\115\145\164\150\x6f\x64");
        $VT = $gx[0]->getAttribute("\101\x6c\x67\x6f\x72\x69\164\x68\x6d");
        KO:
        $Sj = UtilitiesSAML::getEncryptionAlgorithm($VT);
        if (count($Vr) === 0) {
            goto c5;
        }
        if (count($Vr) > 1) {
            goto z0;
        }
        goto sx;
        c5:
        throw new Exception("\x4d\151\163\x73\x69\x6e\147\40\145\x6e\143\x72\x79\x70\x74\145\144\x20\144\141\x74\x61\x20\x69\x6e\40\74\x73\141\155\154\x3a\x45\156\143\162\171\x70\x74\145\x64\x41\163\x73\x65\x72\x74\x69\x6f\156\76\56");
        goto sx;
        z0:
        throw new Exception("\x4d\x6f\x72\x65\40\164\150\141\156\x20\x6f\156\145\x20\x65\x6e\143\162\171\160\164\x65\x64\x20\144\x61\x74\141\x20\145\154\x65\x6d\x65\156\x74\x20\151\156\x20\x3c\x73\141\x6d\x6c\x3a\x45\x6e\x63\162\171\160\x74\x65\144\x41\x73\x73\x65\x72\164\151\x6f\x6e\x3e\x2e");
        sx:
        $TP = new XMLSecurityKeySAML($Sj, array("\x74\x79\160\145" => "\160\162\151\166\141\x74\145"));
        $d1 = UtilitiesSAML::getCustomerDetails();
        $GV = '';
        if (!isset($d1["\163\x70\137\142\141\x73\145\137\165\x72\x6c"])) {
            goto QK;
        }
        $GV = $d1["\163\160\137\x62\x61\x73\x65\x5f\x75\162\x6c"];
        QK:
        $hc = JURI::base();
        if (empty($GV)) {
            goto NZ;
        }
        $hc = $GV;
        NZ:
        $d1 = UtilitiesSAML::getSAMLConfiguration();
        $Hi = UtilitiesSAML::get_public_private_certificate($d1, "\160\x72\151\166\141\164\x65\137\143\x65\162\164\151\146\x69\143\x61\x74\x65");
        if ($Hi == null || $Hi == '') {
            goto Ss;
        }
        $nx = UtilitiesSAML::getCustom_CertificatePath("\x43\165\163\x74\157\x6d\x50\x72\151\x76\141\164\x65\x43\145\x72\x74\x69\x66\x69\x63\141\x74\x65\x2e\153\x65\171");
        goto Eq;
        Ss:
        $nx = dirname(__FILE__) . DIRECTORY_SEPARATOR . "\143\145\x72\164" . DIRECTORY_SEPARATOR . "\x73\x70\x2d\153\145\x79\x2e\153\x65\171";
        Eq:
        $TP->loadKey($nx, TRUE);
        try {
            $ZQ = UtilitiesSAML::decryptElement($Vr[0], $TP);
        } catch (Exception $jf) {
            echo "\x43\x61\165\x67\x68\x74\40\x65\170\x63\x65\x70\x74\151\157\156\72\40", $jf->getMessage(), "\12";
        }
        Xu:
        if ($ZQ->hasAttribute("\111\104")) {
            goto yQ;
        }
        throw new Exception("\x4d\151\163\x73\151\156\x67\x20\x49\104\40\x61\x74\x74\x72\151\142\165\x74\145\x20\157\156\40\x53\x41\115\114\40\x61\x73\x73\x65\162\x74\x69\x6f\x6e\56");
        yQ:
        $this->id = $ZQ->getAttribute("\111\x44");
        if (!($ZQ->getAttribute("\x56\x65\x72\x73\151\157\156") !== "\62\56\60")) {
            goto WE;
        }
        throw new Exception("\125\156\163\x75\160\x70\x6f\x72\x74\x65\144\x20\x76\145\x72\163\151\157\156\x3a\x20" . $ZQ->getAttribute("\x56\x65\162\163\x69\x6f\156"));
        WE:
        $this->issueInstant = UtilitiesSAML::xsDateTimeToTimestamp($ZQ->getAttribute("\x49\x73\x73\x75\x65\111\156\x73\x74\141\156\164"));
        $Ps = UtilitiesSAML::xpQuery($ZQ, "\56\x2f\163\141\155\154\137\x61\163\x73\145\162\164\151\157\x6e\x3a\111\163\x73\x75\x65\x72");
        if (!empty($Ps)) {
            goto jt;
        }
        throw new Exception("\x4d\x69\163\163\x69\x6e\x67\x20\74\163\141\x6d\x6c\x3a\111\x73\163\x75\x65\162\x3e\40\151\x6e\x20\141\x73\163\145\162\x74\151\157\156\56");
        jt:
        $this->issuer = trim($Ps[0]->textContent);
        $this->parseConditions($ZQ);
        $this->parseAuthnStatement($ZQ);
        $this->parseAttributes($ZQ);
        $this->parseEncryptedAttributes($ZQ);
        $this->parseSignature($ZQ);
        $this->parseSubject($ZQ);
    }
    private function parseSubject(DOMElement $ZQ)
    {
        $uJ = UtilitiesSAML::xpQuery($ZQ, "\x2e\x2f\x73\x61\155\x6c\137\141\x73\163\x65\162\x74\x69\157\x6e\x3a\123\x75\142\x6a\145\143\164");
        if (empty($uJ)) {
            goto uU;
        }
        if (count($uJ) > 1) {
            goto wM;
        }
        goto BC;
        uU:
        return;
        goto BC;
        wM:
        throw new Exception("\115\157\x72\145\40\164\150\141\156\40\157\156\145\40\74\163\141\x6d\154\72\x53\165\x62\152\x65\x63\164\x3e\40\151\156\40\74\163\x61\155\154\x3a\101\x73\163\145\162\x74\x69\157\x6e\76\x2e");
        BC:
        $uJ = $uJ[0];
        $MG = UtilitiesSAML::xpQuery($uJ, "\x2e\57\x73\x61\x6d\154\x5f\x61\x73\163\145\x72\x74\x69\x6f\156\72\116\141\155\x65\111\x44\x20\x7c\40\56\57\x73\x61\x6d\x6c\137\141\x73\x73\x65\162\164\151\157\x6e\72\x45\156\143\162\x79\160\x74\x65\x64\x49\x44\x2f\x78\x65\x6e\143\72\x45\x6e\143\x72\171\160\x74\x65\144\104\x61\164\x61");
        if (empty($MG)) {
            goto eV;
        }
        if (count($MG) > 1) {
            goto Kk;
        }
        goto hh;
        eV:
        throw new Exception("\115\151\163\163\151\156\x67\x20\x3c\x73\x61\x6d\x6c\x3a\x4e\x61\x6d\x65\111\x44\76\40\157\162\x20\x3c\x73\141\155\154\x3a\105\156\143\162\x79\x70\x74\145\x64\111\x44\x3e\x20\x69\x6e\x20\74\163\x61\x6d\154\72\123\165\142\152\x65\143\x74\x3e\x2e");
        goto hh;
        Kk:
        throw new Exception("\115\x6f\x72\x65\x20\x74\x68\141\x6e\40\x6f\156\145\40\74\x73\141\x6d\x6c\x3a\116\141\x6d\145\111\x44\76\x20\x6f\x72\x20\74\x73\x61\155\154\x3a\x45\156\143\162\171\x70\164\x65\144\104\x3e\40\x69\x6e\40\x3c\x73\141\155\x6c\x3a\123\165\x62\x6a\145\143\x74\76\x2e");
        hh:
        $MG = $MG[0];
        if ($MG->localName === "\x45\x6e\x63\x72\x79\x70\164\x65\144\x44\x61\164\x61") {
            goto Dp;
        }
        $this->nameId = UtilitiesSAML::parseNameId($MG);
        goto M4;
        Dp:
        $this->encryptedNameId = $MG;
        M4:
    }
    private function parseConditions(DOMElement $ZQ)
    {
        $Oc = UtilitiesSAML::xpQuery($ZQ, "\56\x2f\x73\x61\155\x6c\137\141\x73\x73\145\x72\x74\151\x6f\156\72\103\x6f\156\144\151\x74\x69\157\156\x73");
        if (empty($Oc)) {
            goto p1;
        }
        if (count($Oc) > 1) {
            goto Ob;
        }
        goto is;
        p1:
        return;
        goto is;
        Ob:
        throw new Exception("\115\157\162\145\x20\164\150\141\156\40\157\156\145\x20\74\163\x61\155\x6c\72\103\157\156\x64\x69\x74\x69\157\x6e\x73\76\40\x69\x6e\40\x3c\x73\141\155\x6c\x3a\101\x73\163\145\x72\164\x69\157\156\76\56");
        is:
        $Oc = $Oc[0];
        if (!$Oc->hasAttribute("\116\x6f\164\x42\x65\x66\x6f\162\x65")) {
            goto vQ;
        }
        $Ev = UtilitiesSAML::xsDateTimeToTimestamp($Oc->getAttribute("\x4e\x6f\x74\102\145\146\157\162\x65"));
        if (!($this->notBefore === NULL || $this->notBefore < $Ev)) {
            goto zG;
        }
        $this->notBefore = $Ev;
        zG:
        vQ:
        if (!$Oc->hasAttribute("\x4e\x6f\x74\x4f\156\117\162\x41\146\164\x65\162")) {
            goto RT;
        }
        $jv = UtilitiesSAML::xsDateTimeToTimestamp($Oc->getAttribute("\116\x6f\x74\x4f\156\x4f\162\x41\146\164\145\162"));
        if (!($this->notOnOrAfter === NULL || $this->notOnOrAfter > $jv)) {
            goto M7;
        }
        $this->notOnOrAfter = $jv;
        M7:
        RT:
        $Il = $Oc->firstChild;
        s4:
        if (!($Il !== NULL)) {
            goto iV;
        }
        if (!$Il instanceof DOMText) {
            goto Mv;
        }
        goto oq;
        Mv:
        if (!($Il->namespaceURI !== "\165\162\x6e\72\157\141\163\151\x73\x3a\156\x61\x6d\x65\x73\72\164\x63\72\123\101\x4d\x4c\x3a\62\x2e\60\x3a\x61\x73\x73\145\162\x74\x69\x6f\156")) {
            goto XL;
        }
        throw new Exception("\125\156\153\156\x6f\x77\156\x20\x6e\x61\x6d\145\163\x70\141\143\x65\40\x6f\146\40\143\157\x6e\x64\151\164\151\x6f\156\72\40" . var_export($Il->namespaceURI, TRUE));
        XL:
        switch ($Il->localName) {
            case "\x41\165\144\151\145\x6e\143\145\x52\x65\x73\x74\162\151\x63\x74\x69\157\x6e":
                $s9 = UtilitiesSAML::extractStrings($Il, "\165\x72\x6e\x3a\x6f\141\x73\x69\163\x3a\156\x61\x6d\x65\x73\72\x74\143\x3a\123\101\115\x4c\72\62\56\x30\72\x61\x73\x73\x65\x72\x74\151\157\x6e", "\x41\165\x64\151\x65\156\143\145");
                if ($this->validAudiences === NULL) {
                    goto xy;
                }
                $this->validAudiences = array_intersect($this->validAudiences, $s9);
                goto UN;
                xy:
                $this->validAudiences = $s9;
                UN:
                goto lz;
            case "\x4f\x6e\x65\124\151\x6d\x65\125\163\x65":
                goto lz;
            case "\120\x72\x6f\x78\x79\122\145\x73\x74\162\151\143\x74\x69\x6f\x6e":
                goto lz;
            default:
                throw new Exception("\x55\x6e\153\x6e\157\167\156\x20\143\157\x6e\x64\x69\x74\151\157\x6e\x3a\40" . var_export($Il->localName, TRUE));
        }
        Gz:
        lz:
        oq:
        $Il = $Il->nextSibling;
        goto s4;
        iV:
    }
    private function parseAuthnStatement(DOMElement $ZQ)
    {
        $Ul = UtilitiesSAML::xpQuery($ZQ, "\56\x2f\163\x61\155\154\137\141\163\163\145\162\164\151\157\156\x3a\x41\165\x74\x68\156\123\164\x61\x74\x65\x6d\145\x6e\x74");
        if (empty($Ul)) {
            goto Hm;
        }
        if (count($Ul) > 1) {
            goto UP;
        }
        goto Ow;
        Hm:
        $this->authnInstant = NULL;
        return;
        goto Ow;
        UP:
        throw new Exception("\x4d\157\x72\x65\40\x74\150\x61\164\x20\x6f\156\x65\40\74\x73\141\155\x6c\72\101\165\x74\150\156\x53\164\x61\164\x65\155\x65\x6e\x74\76\x20\x69\156\x20\74\163\x61\155\154\x3a\x41\x73\163\145\162\x74\151\157\156\x3e\40\x6e\x6f\164\40\163\165\x70\160\x6f\162\x74\x65\144\56");
        Ow:
        $NJ = $Ul[0];
        if ($NJ->hasAttribute("\101\165\164\150\156\x49\x6e\163\164\141\156\x74")) {
            goto ek;
        }
        throw new Exception("\x4d\x69\x73\163\151\x6e\x67\40\162\x65\x71\165\x69\x72\x65\144\40\x41\x75\x74\x68\x6e\x49\156\163\x74\x61\156\x74\x20\x61\164\164\x72\x69\142\165\x74\145\x20\x6f\156\40\74\163\x61\x6d\x6c\72\x41\165\x74\x68\x6e\123\164\x61\164\x65\x6d\145\x6e\164\x3e\56");
        ek:
        $this->authnInstant = UtilitiesSAML::xsDateTimeToTimestamp($NJ->getAttribute("\101\165\164\x68\156\111\x6e\163\164\141\x6e\x74"));
        if (!$NJ->hasAttribute("\x53\145\x73\x73\x69\157\x6e\116\157\x74\x4f\x6e\x4f\162\x41\146\x74\x65\162")) {
            goto r7;
        }
        $this->sessionNotOnOrAfter = UtilitiesSAML::xsDateTimeToTimestamp($NJ->getAttribute("\123\x65\163\x73\151\x6f\x6e\x4e\x6f\x74\x4f\x6e\x4f\162\101\x66\x74\x65\162"));
        r7:
        if (!$NJ->hasAttribute("\123\x65\163\163\x69\x6f\156\111\x6e\x64\145\170")) {
            goto iw;
        }
        $this->sessionIndex = $NJ->getAttribute("\123\x65\x73\x73\x69\x6f\x6e\x49\x6e\144\x65\170");
        iw:
        $this->parseAuthnContext($NJ);
    }
    private function parseAuthnContext(DOMElement $vZ)
    {
        $Sy = UtilitiesSAML::xpQuery($vZ, "\x2e\57\x73\x61\x6d\154\x5f\141\163\x73\145\162\164\x69\157\x6e\x3a\101\x75\164\150\x6e\103\157\x6e\x74\x65\170\x74");
        if (count($Sy) > 1) {
            goto S3;
        }
        if (empty($Sy)) {
            goto UJ;
        }
        goto v4;
        S3:
        throw new Exception("\115\157\162\145\x20\164\x68\x61\156\40\x6f\x6e\145\x20\x3c\x73\141\155\x6c\x3a\x41\x75\164\150\x6e\103\157\x6e\x74\145\170\x74\76\x20\x69\156\x20\x3c\x73\141\x6d\x6c\x3a\101\x75\x74\x68\x6e\x53\x74\141\164\145\155\145\x6e\164\76\56");
        goto v4;
        UJ:
        throw new Exception("\115\x69\163\163\151\156\147\40\162\x65\x71\165\x69\162\145\x64\40\x3c\x73\141\155\154\72\x41\165\164\150\x6e\x43\x6f\x6e\164\145\170\164\x3e\x20\151\156\x20\x3c\x73\x61\155\x6c\72\101\165\x74\x68\156\123\x74\141\x74\x65\x6d\x65\156\164\76\x2e");
        v4:
        $bN = $Sy[0];
        $Ex = UtilitiesSAML::xpQuery($bN, "\x2e\x2f\163\x61\x6d\x6c\x5f\x61\x73\163\x65\x72\x74\x69\x6f\156\x3a\101\165\164\x68\156\x43\x6f\x6e\164\145\x78\164\104\x65\143\x6c\x52\145\x66");
        if (count($Ex) > 1) {
            goto ID;
        }
        if (count($Ex) === 1) {
            goto VL;
        }
        goto bf;
        ID:
        throw new Exception("\115\x6f\162\145\40\164\150\141\x6e\x20\x6f\156\x65\40\74\x73\x61\155\154\72\101\165\164\150\156\103\157\156\x74\x65\x78\x74\x44\x65\x63\x6c\122\x65\146\76\x20\146\x6f\x75\x6e\144\77");
        goto bf;
        VL:
        $this->setAuthnContextDeclRef(trim($Ex[0]->textContent));
        bf:
        $Jh = UtilitiesSAML::xpQuery($bN, "\56\x2f\x73\x61\x6d\154\x5f\141\x73\163\145\162\164\151\x6f\x6e\72\101\x75\164\x68\156\x43\x6f\x6e\x74\145\170\164\x44\145\143\x6c");
        if (count($Jh) > 1) {
            goto V9;
        }
        if (count($Jh) === 1) {
            goto qe;
        }
        goto Nx;
        V9:
        throw new Exception("\x4d\x6f\162\x65\40\x74\150\x61\x6e\x20\x6f\x6e\x65\40\74\163\141\x6d\154\72\x41\x75\164\150\x6e\103\x6f\x6e\164\145\170\x74\104\145\x63\154\x3e\40\x66\157\x75\x6e\x64\77");
        goto Nx;
        qe:
        $this->setAuthnContextDecl(new SAML2_XML_Chunk($Jh[0]));
        Nx:
        $oS = UtilitiesSAML::xpQuery($bN, "\56\57\163\x61\155\x6c\137\141\163\x73\x65\x72\164\151\157\x6e\72\x41\x75\x74\x68\156\103\157\x6e\x74\x65\x78\164\x43\x6c\141\163\163\122\x65\x66");
        if (count($oS) > 1) {
            goto rX;
        }
        if (count($oS) === 1) {
            goto E8;
        }
        goto A7;
        rX:
        throw new Exception("\115\x6f\x72\x65\x20\x74\150\x61\156\x20\157\x6e\145\x20\74\x73\x61\155\154\x3a\x41\x75\164\150\156\x43\x6f\x6e\164\145\170\x74\x43\154\141\163\163\122\145\146\x3e\40\151\156\40\x3c\x73\x61\155\x6c\x3a\101\x75\x74\150\156\103\x6f\156\x74\145\x78\164\x3e\56");
        goto A7;
        E8:
        $this->setAuthnContextClassRef(trim($oS[0]->textContent));
        A7:
        if (!(empty($this->authnContextClassRef) && empty($this->authnContextDecl) && empty($this->authnContextDeclRef))) {
            goto hG;
        }
        throw new Exception("\x4d\151\x73\163\151\x6e\147\x20\x65\151\x74\x68\x65\x72\x20\74\163\x61\155\x6c\x3a\101\x75\x74\150\156\103\x6f\x6e\x74\145\x78\x74\x43\154\141\x73\163\122\x65\146\76\x20\x6f\x72\x20\x3c\163\x61\x6d\154\72\x41\165\x74\x68\x6e\103\x6f\156\164\145\170\164\x44\x65\x63\x6c\x52\145\x66\x3e\40\157\162\x20\74\x73\141\155\x6c\x3a\x41\x75\x74\x68\x6e\x43\x6f\156\164\145\170\x74\104\x65\143\x6c\76");
        hG:
        $this->AuthenticatingAuthority = UtilitiesSAML::extractStrings($bN, "\x75\162\x6e\72\157\x61\x73\151\x73\x3a\x6e\141\155\x65\163\x3a\x74\143\x3a\x53\x41\115\114\72\62\56\60\72\141\163\x73\x65\162\x74\151\x6f\x6e", "\101\x75\164\150\x65\x6e\x74\x69\x63\x61\164\151\x6e\x67\101\165\x74\x68\157\x72\x69\x74\x79");
    }
    private function parseAttributes(DOMElement $ZQ)
    {
        $Io = TRUE;
        $Mm = UtilitiesSAML::xpQuery($ZQ, "\56\57\163\x61\155\154\137\141\x73\x73\x65\x72\164\x69\157\x6e\x3a\101\x74\x74\162\x69\142\x75\x74\145\123\x74\x61\164\145\x6d\x65\x6e\164\x2f\163\x61\x6d\x6c\137\141\163\x73\145\162\164\151\157\156\72\x41\x74\164\x72\x69\142\165\164\x65");
        foreach ($Mm as $d1) {
            if ($d1->hasAttribute("\x4e\141\x6d\x65")) {
                goto Vh;
            }
            throw new Exception("\115\x69\163\163\x69\156\147\40\156\141\155\145\40\x6f\x6e\x20\x3c\163\141\155\x6c\72\101\164\x74\x72\151\142\165\164\x65\76\40\145\154\x65\x6d\145\x6e\164\x2e");
            Vh:
            $JN = $d1->getAttribute("\116\141\155\145");
            if ($d1->hasAttribute("\116\141\155\x65\x46\x6f\162\x6d\x61\164")) {
                goto Wi;
            }
            $qU = "\x75\162\x6e\72\157\141\163\151\163\x3a\156\141\x6d\x65\x73\x3a\x74\x63\72\x53\101\115\114\72\61\x2e\61\72\x6e\x61\x6d\x65\151\144\x2d\x66\157\162\155\x61\x74\x3a\165\x6e\x73\x70\x65\x63\151\146\151\x65\144";
            goto tJ;
            Wi:
            $qU = $d1->getAttribute("\x4e\x61\x6d\x65\106\157\162\155\141\164");
            tJ:
            if ($Io) {
                goto DX;
            }
            if (!($this->nameFormat !== $qU)) {
                goto HB;
            }
            $this->nameFormat = "\x75\x72\x6e\72\157\x61\163\151\x73\x3a\x6e\x61\x6d\x65\163\72\164\143\72\123\101\115\x4c\72\x31\x2e\x31\72\x6e\141\x6d\x65\x69\144\55\x66\x6f\x72\155\x61\164\x3a\165\156\163\x70\145\143\151\146\151\145\144";
            HB:
            goto WZ;
            DX:
            $this->nameFormat = $qU;
            $Io = FALSE;
            WZ:
            if (array_key_exists($JN, $this->attributes)) {
                goto ay;
            }
            $this->attributes[$JN] = array();
            ay:
            $nr = UtilitiesSAML::xpQuery($d1, "\x2e\x2f\x73\x61\155\x6c\x5f\141\163\163\x65\x72\x74\151\x6f\156\x3a\101\x74\164\162\x69\x62\x75\164\x65\x56\141\154\x75\x65");
            foreach ($nr as $UH) {
                $this->attributes[$JN][] = trim($UH->textContent);
                kk:
            }
            JU:
            Db:
        }
        wq:
    }
    private function parseEncryptedAttributes(DOMElement $ZQ)
    {
        $this->encryptedAttribute = UtilitiesSAML::xpQuery($ZQ, "\x2e\57\163\x61\x6d\x6c\x5f\x61\163\163\x65\x72\x74\151\157\x6e\72\101\x74\164\x72\151\x62\165\164\145\x53\164\141\164\x65\x6d\145\156\164\x2f\x73\141\155\154\137\141\163\x73\x65\162\x74\x69\157\x6e\72\x45\156\x63\162\x79\160\x74\145\x64\101\164\164\x72\x69\142\x75\x74\145");
    }
    private function parseSignature(DOMElement $ZQ)
    {
        $AM = UtilitiesSAML::validateElement($ZQ);
        if (!($AM !== FALSE)) {
            goto bV;
        }
        $this->wasSignedAtConstruction = TRUE;
        $this->certificates = $AM["\103\145\162\164\x69\x66\x69\143\141\x74\x65\x73"];
        $this->signatureData = $AM;
        bV:
    }
    public function validate(XMLSecurityKeySAML $TP)
    {
        if (!($this->signatureData === NULL)) {
            goto te;
        }
        return FALSE;
        te:
        UtilitiesSAML::validateSignature($this->signatureData, $TP);
        return TRUE;
    }
    public function getId()
    {
        return $this->id;
    }
    public function setId($Nv)
    {
        $this->id = $Nv;
    }
    public function getIssueInstant()
    {
        return $this->issueInstant;
    }
    public function setIssueInstant($w7)
    {
        $this->issueInstant = $w7;
    }
    public function getIssuer()
    {
        return $this->issuer;
    }
    public function setIssuer($Ps)
    {
        $this->issuer = $Ps;
    }
    public function getNameId()
    {
        if (!($this->encryptedNameId !== NULL)) {
            goto Zm;
        }
        throw new Exception("\x41\x74\x74\x65\155\160\x74\145\144\x20\x74\x6f\x20\x72\145\164\x72\x69\145\166\x65\x20\x65\156\143\162\x79\160\x74\145\x64\40\116\141\155\x65\111\104\x20\167\x69\x74\150\x6f\165\x74\40\144\x65\x63\x72\x79\160\164\x69\x6e\x67\40\151\164\x20\x66\151\162\163\164\56");
        Zm:
        return $this->nameId;
    }
    public function setNameId($MG)
    {
        $this->nameId = $MG;
    }
    public function isNameIdEncrypted()
    {
        if (!($this->encryptedNameId !== NULL)) {
            goto r0;
        }
        return TRUE;
        r0:
        return FALSE;
    }
    public function encryptNameId(XMLSecurityKeySAML $TP)
    {
        $qW = new DOMDocument();
        $bb = $qW->createElement("\x72\157\157\164");
        $qW->appendChild($bb);
        UtilitiesSAML::addNameId($bb, $this->nameId);
        $MG = $bb->firstChild;
        UtilitiesSAML::getContainer()->debugMessage($MG, "\145\x6e\x63\162\x79\160\164");
        $xs = new XMLSecEncSAML();
        $xs->setNode($MG);
        $xs->type = XMLSecEncSAML::Element;
        $XX = new XMLSecurityKeySAML(XMLSecurityKeySAML::AES128_CBC);
        $XX->generateSessionKey();
        $xs->encryptKey($TP, $XX);
        $this->encryptedNameId = $xs->encryptNode($XX);
        $this->nameId = NULL;
    }
    public function decryptNameId(XMLSecurityKeySAML $TP, array $Tk = array())
    {
        if (!($this->encryptedNameId === NULL)) {
            goto y4;
        }
        return;
        y4:
        $MG = UtilitiesSAML::decryptElement($this->encryptedNameId, $TP, $Tk);
        UtilitiesSAML::getContainer()->debugMessage($MG, "\144\145\x63\x72\x79\x70\164");
        $this->nameId = UtilitiesSAML::parseNameId($MG);
        $this->encryptedNameId = NULL;
    }
    public function decryptAttributes(XMLSecurityKeySAML $TP, array $Tk = array())
    {
        if (!($this->encryptedAttribute === NULL)) {
            goto CW;
        }
        return;
        CW:
        $Io = TRUE;
        $Mm = $this->encryptedAttribute;
        foreach ($Mm as $a6) {
            $d1 = UtilitiesSAML::decryptElement($a6->getElementsByTagName("\105\x6e\143\162\171\160\164\145\x64\104\x61\x74\141")->item(0), $TP, $Tk);
            if ($d1->hasAttribute("\x4e\141\155\x65")) {
                goto ow;
            }
            throw new Exception("\115\151\x73\x73\x69\156\x67\x20\156\x61\x6d\145\x20\157\x6e\40\74\163\141\155\154\x3a\x41\164\x74\162\x69\x62\x75\x74\x65\x3e\40\145\x6c\x65\x6d\x65\156\164\x2e");
            ow:
            $JN = $d1->getAttribute("\x4e\x61\155\145");
            if ($d1->hasAttribute("\x4e\141\155\145\106\x6f\x72\155\141\164")) {
                goto kg;
            }
            $qU = "\x75\x72\x6e\x3a\x6f\141\x73\151\163\72\x6e\141\155\145\163\x3a\x74\143\x3a\x53\101\x4d\114\72\x32\x2e\60\x3a\141\164\164\162\156\141\x6d\x65\x2d\146\157\162\155\x61\x74\x3a\x75\x6e\163\x70\145\x63\151\146\151\145\144";
            goto gS;
            kg:
            $qU = $d1->getAttribute("\x4e\x61\155\145\106\x6f\162\155\141\x74");
            gS:
            if ($Io) {
                goto V7;
            }
            if (!($this->nameFormat !== $qU)) {
                goto LI;
            }
            $this->nameFormat = "\x75\162\x6e\x3a\x6f\x61\x73\x69\163\x3a\156\x61\155\145\163\x3a\x74\x63\72\x53\x41\115\x4c\x3a\x32\x2e\60\72\141\x74\x74\162\x6e\x61\x6d\145\x2d\146\x6f\x72\x6d\x61\x74\x3a\x75\x6e\x73\160\145\143\x69\x66\151\145\144";
            LI:
            goto Pz;
            V7:
            $this->nameFormat = $qU;
            $Io = FALSE;
            Pz:
            if (array_key_exists($JN, $this->attributes)) {
                goto aW;
            }
            $this->attributes[$JN] = array();
            aW:
            $nr = UtilitiesSAML::xpQuery($d1, "\x2e\x2f\163\141\155\x6c\137\141\x73\x73\x65\162\164\151\x6f\x6e\72\x41\164\164\162\x69\x62\165\164\x65\x56\x61\x6c\x75\x65");
            foreach ($nr as $UH) {
                $this->attributes[$JN][] = trim($UH->textContent);
                fU:
            }
            XM:
            fl:
        }
        pZ:
    }
    public function getNotBefore()
    {
        return $this->notBefore;
    }
    public function setNotBefore($Ev)
    {
        $this->notBefore = $Ev;
    }
    public function getNotOnOrAfter()
    {
        return $this->notOnOrAfter;
    }
    public function setNotOnOrAfter($jv)
    {
        $this->notOnOrAfter = $jv;
    }
    public function setEncryptedAttributes($J_)
    {
        $this->requiredEncAttributes = $J_;
    }
    public function getValidAudiences()
    {
        return $this->validAudiences;
    }
    public function setValidAudiences(array $Xx = NULL)
    {
        $this->validAudiences = $Xx;
    }
    public function getAuthnInstant()
    {
        return $this->authnInstant;
    }
    public function setAuthnInstant($Oj)
    {
        $this->authnInstant = $Oj;
    }
    public function getSessionNotOnOrAfter()
    {
        return $this->sessionNotOnOrAfter;
    }
    public function setSessionNotOnOrAfter($vc)
    {
        $this->sessionNotOnOrAfter = $vc;
    }
    public function getSessionIndex()
    {
        return $this->sessionIndex;
    }
    public function setSessionIndex($XJ)
    {
        $this->sessionIndex = $XJ;
    }
    public function getAuthnContext()
    {
        if (empty($this->authnContextClassRef)) {
            goto UE;
        }
        return $this->authnContextClassRef;
        UE:
        if (empty($this->authnContextDeclRef)) {
            goto Q3;
        }
        return $this->authnContextDeclRef;
        Q3:
        return NULL;
    }
    public function setAuthnContext($Ht)
    {
        $this->setAuthnContextClassRef($Ht);
    }
    public function getAuthnContextClassRef()
    {
        return $this->authnContextClassRef;
    }
    public function setAuthnContextClassRef($AR)
    {
        $this->authnContextClassRef = $AR;
    }
    public function setAuthnContextDecl(SAML2_XML_Chunk $Ba)
    {
        if (empty($this->authnContextDeclRef)) {
            goto N1;
        }
        throw new Exception("\x41\165\x74\x68\x6e\103\157\x6e\x74\145\x78\x74\104\x65\143\154\122\x65\x66\40\151\x73\40\x61\154\x72\x65\x61\x64\171\40\162\x65\x67\x69\163\x74\145\x72\145\x64\x21\40\115\141\x79\40\x6f\156\154\x79\x20\150\141\166\x65\40\145\x69\164\150\x65\162\40\x61\40\104\145\143\x6c\x20\x6f\x72\x20\141\40\104\145\143\x6c\x52\x65\146\x2c\x20\x6e\157\164\x20\142\x6f\164\x68\x21");
        N1:
        $this->authnContextDecl = $Ba;
    }
    public function getAuthnContextDecl()
    {
        return $this->authnContextDecl;
    }
    public function setAuthnContextDeclRef($pW)
    {
        if (empty($this->authnContextDecl)) {
            goto uE;
        }
        throw new Exception("\x41\165\164\150\156\x43\x6f\156\164\145\x78\x74\x44\145\143\x6c\x20\151\x73\x20\141\x6c\x72\145\141\x64\171\x20\x72\x65\147\151\x73\164\145\x72\145\x64\41\x20\x4d\x61\x79\x20\157\156\154\171\40\150\x61\x76\x65\40\x65\x69\164\x68\145\x72\40\x61\40\x44\145\x63\x6c\x20\x6f\x72\40\x61\x20\x44\145\143\x6c\x52\145\x66\x2c\40\x6e\x6f\164\40\142\157\x74\150\x21");
        uE:
        $this->authnContextDeclRef = $pW;
    }
    public function getAuthnContextDeclRef()
    {
        return $this->authnContextDeclRef;
    }
    public function getAuthenticatingAuthority()
    {
        return $this->AuthenticatingAuthority;
    }
    public function setAuthenticatingAuthority($QQ)
    {
        $this->AuthenticatingAuthority = $QQ;
    }
    public function getAttributes()
    {
        return $this->attributes;
    }
    public function setAttributes(array $Mm)
    {
        $this->attributes = $Mm;
    }
    public function getAttributeNameFormat()
    {
        return $this->nameFormat;
    }
    public function setAttributeNameFormat($qU)
    {
        $this->nameFormat = $qU;
    }
    public function getSubjectConfirmation()
    {
        return $this->SubjectConfirmation;
    }
    public function setSubjectConfirmation(array $Jg)
    {
        $this->SubjectConfirmation = $Jg;
    }
    public function getSignatureKey()
    {
        return $this->signatureKey;
    }
    public function setSignatureKey(XMLSecurityKeySAML $lL = NULL)
    {
        $this->signatureKey = $lL;
    }
    public function getEncryptionKey()
    {
        return $this->encryptionKey;
    }
    public function setEncryptionKey(XMLSecurityKeySAML $uR = NULL)
    {
        $this->encryptionKey = $uR;
    }
    public function setCertificates(array $xg)
    {
        $this->certificates = $xg;
    }
    public function getCertificates()
    {
        return $this->certificates;
    }
    public function getWasSignedAtConstruction()
    {
        return $this->wasSignedAtConstruction;
    }
    public function toXML(DOMNode $Vc = NULL)
    {
        if ($Vc === NULL) {
            goto Uz;
        }
        $AW = $Vc->ownerDocument;
        goto Qi;
        Uz:
        $AW = new DOMDocument();
        $Vc = $AW;
        Qi:
        $bb = $AW->createElementNS("\x75\x72\156\x3a\157\x61\x73\151\163\72\x6e\x61\x6d\145\163\72\164\143\x3a\123\x41\115\x4c\72\62\x2e\60\72\141\x73\163\145\162\x74\x69\x6f\x6e", "\x73\141\x6d\x6c\72" . "\x41\x73\x73\x65\x72\164\151\x6f\x6e");
        $Vc->appendChild($bb);
        $bb->setAttributeNS("\165\162\x6e\x3a\157\x61\x73\151\163\72\156\x61\x6d\145\163\x3a\164\x63\x3a\x53\x41\x4d\114\x3a\62\x2e\60\x3a\160\x72\x6f\164\x6f\143\x6f\x6c", "\x73\x61\155\x6c\x70\72\164\x6d\160", "\164\155\x70");
        $bb->removeAttributeNS("\165\x72\156\72\157\x61\x73\151\x73\x3a\x6e\141\x6d\x65\163\72\x74\143\72\x53\101\115\x4c\72\62\56\x30\72\160\162\x6f\164\x6f\143\x6f\x6c", "\x74\x6d\x70");
        $bb->setAttributeNS("\x68\x74\164\x70\x3a\x2f\57\x77\x77\167\56\167\x33\56\x6f\162\x67\57\62\x30\x30\61\57\130\115\x4c\123\x63\150\x65\155\141\x2d\151\x6e\163\x74\141\x6e\x63\x65", "\170\x73\151\x3a\x74\155\x70", "\x74\x6d\x70");
        $bb->removeAttributeNS("\150\x74\x74\x70\72\57\x2f\167\x77\x77\x2e\x77\63\56\x6f\x72\147\x2f\x32\60\x30\x31\57\130\115\x4c\x53\143\x68\145\155\141\55\151\x6e\163\164\x61\x6e\x63\x65", "\x74\x6d\x70");
        $bb->setAttributeNS("\x68\164\x74\x70\x3a\57\57\167\x77\x77\56\167\x33\x2e\x6f\x72\147\x2f\x32\x30\x30\61\57\x58\x4d\114\123\143\x68\145\155\141", "\170\x73\72\x74\x6d\x70", "\164\155\160");
        $bb->removeAttributeNS("\150\x74\164\160\72\x2f\57\x77\x77\167\x2e\167\x33\56\157\x72\x67\57\62\60\60\x31\57\130\115\114\x53\x63\150\x65\x6d\141", "\x74\155\160");
        $bb->setAttribute("\111\104", $this->id);
        $bb->setAttribute("\126\x65\x72\x73\151\157\156", "\x32\56\x30");
        $bb->setAttribute("\111\x73\163\x75\x65\x49\x6e\163\x74\141\x6e\164", gmdate("\x59\x2d\155\x2d\x64\134\124\x48\x3a\x69\x3a\163\134\132", $this->issueInstant));
        $Ps = UtilitiesSAML::addString($bb, "\x75\162\x6e\72\x6f\141\163\151\x73\x3a\156\141\x6d\x65\163\x3a\x74\143\x3a\123\101\x4d\x4c\x3a\x32\56\60\x3a\141\x73\x73\145\162\x74\x69\x6f\x6e", "\x73\x61\x6d\154\x3a\x49\163\x73\x75\x65\162", $this->issuer);
        $this->addSubject($bb);
        $this->addConditions($bb);
        $this->addAuthnStatement($bb);
        if ($this->requiredEncAttributes == FALSE) {
            goto JP;
        }
        $this->addEncryptedAttributeStatement($bb);
        goto lR;
        JP:
        $this->addAttributeStatement($bb);
        lR:
        if (!($this->signatureKey !== NULL)) {
            goto sm;
        }
        UtilitiesSAML::insertSignature($this->signatureKey, $this->certificates, $bb, $Ps->nextSibling);
        sm:
        return $bb;
    }
    private function addSubject(DOMElement $bb)
    {
        if (!($this->nameId === NULL && $this->encryptedNameId === NULL)) {
            goto jF;
        }
        return;
        jF:
        $uJ = $bb->ownerDocument->createElementNS("\x75\x72\x6e\x3a\x6f\x61\163\x69\163\72\x6e\141\x6d\x65\163\x3a\164\x63\72\x53\101\115\x4c\72\62\56\x30\x3a\x61\x73\x73\x65\x72\x74\x69\x6f\156", "\163\x61\155\154\72\123\165\x62\152\145\x63\x74");
        $bb->appendChild($uJ);
        if ($this->encryptedNameId === NULL) {
            goto sh;
        }
        $te = $uJ->ownerDocument->createElementNS("\165\x72\156\x3a\157\x61\x73\151\x73\x3a\156\x61\x6d\145\x73\72\x74\x63\72\123\101\x4d\x4c\x3a\x32\56\60\72\141\163\x73\x65\162\x74\x69\x6f\156", "\163\141\155\154\x3a" . "\x45\156\143\162\x79\160\164\145\x64\111\x44");
        $uJ->appendChild($te);
        $te->appendChild($uJ->ownerDocument->importNode($this->encryptedNameId, TRUE));
        goto vT;
        sh:
        UtilitiesSAML::addNameId($uJ, $this->nameId);
        vT:
        foreach ($this->SubjectConfirmation as $XB) {
            $XB->toXML($uJ);
            Ih:
        }
        T5:
    }
    private function addConditions(DOMElement $bb)
    {
        $AW = $bb->ownerDocument;
        $Oc = $AW->createElementNS("\x75\162\x6e\72\157\x61\163\151\163\x3a\156\141\x6d\x65\x73\x3a\164\x63\x3a\x53\101\x4d\114\x3a\x32\x2e\x30\72\141\x73\x73\x65\162\164\x69\157\x6e", "\x73\141\155\154\x3a\x43\x6f\x6e\x64\151\164\x69\x6f\156\163");
        $bb->appendChild($Oc);
        if (!($this->notBefore !== NULL)) {
            goto j1;
        }
        $Oc->setAttribute("\116\x6f\x74\x42\145\x66\157\x72\x65", gmdate("\131\55\x6d\x2d\144\134\124\110\72\151\x3a\163\x5c\x5a", $this->notBefore));
        j1:
        if (!($this->notOnOrAfter !== NULL)) {
            goto ZD;
        }
        $Oc->setAttribute("\x4e\157\x74\117\x6e\117\x72\101\x66\164\x65\162", gmdate("\x59\x2d\x6d\x2d\x64\x5c\124\x48\x3a\151\72\x73\134\132", $this->notOnOrAfter));
        ZD:
        if (!($this->validAudiences !== NULL)) {
            goto dm;
        }
        $e7 = $AW->createElementNS("\165\x72\156\72\x6f\141\x73\151\163\x3a\x6e\141\x6d\145\x73\x3a\x74\x63\72\x53\101\115\x4c\x3a\62\56\x30\x3a\x61\163\163\x65\x72\164\x69\157\156", "\x73\x61\155\154\x3a\101\x75\144\151\x65\156\143\145\x52\x65\163\164\x72\151\143\x74\151\x6f\156");
        $Oc->appendChild($e7);
        UtilitiesSAML::addStrings($e7, "\x75\x72\156\x3a\157\x61\163\x69\x73\x3a\156\141\155\x65\x73\x3a\x74\143\72\123\101\115\114\x3a\x32\56\60\72\x61\163\x73\145\x72\x74\151\x6f\x6e", "\x73\141\x6d\154\x3a\x41\x75\144\x69\145\156\x63\x65", FALSE, $this->validAudiences);
        dm:
    }
    private function addAuthnStatement(DOMElement $bb)
    {
        if (!($this->authnInstant === NULL || $this->authnContextClassRef === NULL && $this->authnContextDecl === NULL && $this->authnContextDeclRef === NULL)) {
            goto CI;
        }
        return;
        CI:
        $AW = $bb->ownerDocument;
        $vZ = $AW->createElementNS("\x75\162\156\x3a\157\x61\163\151\x73\72\x6e\x61\155\145\163\x3a\164\x63\x3a\x53\101\115\x4c\x3a\62\x2e\60\72\141\163\x73\x65\162\x74\x69\157\x6e", "\163\141\x6d\x6c\x3a\101\165\164\150\156\x53\x74\141\164\145\155\x65\156\164");
        $bb->appendChild($vZ);
        $vZ->setAttribute("\101\x75\x74\x68\156\x49\156\163\164\x61\x6e\x74", gmdate("\131\55\155\x2d\x64\x5c\124\110\x3a\x69\72\x73\x5c\x5a", $this->authnInstant));
        if (!($this->sessionNotOnOrAfter !== NULL)) {
            goto ri;
        }
        $vZ->setAttribute("\123\145\163\x73\x69\157\x6e\x4e\x6f\x74\x4f\156\x4f\x72\101\x66\164\145\x72", gmdate("\131\55\x6d\55\x64\x5c\124\110\72\151\x3a\x73\x5c\x5a", $this->sessionNotOnOrAfter));
        ri:
        if (!($this->sessionIndex !== NULL)) {
            goto fE;
        }
        $vZ->setAttribute("\123\145\x73\163\151\x6f\156\111\156\x64\x65\x78", $this->sessionIndex);
        fE:
        $bN = $AW->createElementNS("\x75\x72\x6e\72\x6f\x61\163\151\x73\x3a\x6e\141\155\x65\x73\x3a\164\143\72\x53\x41\115\114\x3a\x32\56\60\72\x61\x73\x73\145\x72\x74\151\x6f\x6e", "\163\141\x6d\x6c\72\x41\165\164\150\156\103\x6f\x6e\164\145\x78\164");
        $vZ->appendChild($bN);
        if (empty($this->authnContextClassRef)) {
            goto wE;
        }
        UtilitiesSAML::addString($bN, "\x75\x72\x6e\72\157\141\x73\151\163\72\156\x61\x6d\x65\x73\72\164\143\x3a\x53\101\x4d\114\x3a\62\56\60\x3a\x61\x73\x73\x65\162\x74\151\x6f\x6e", "\x73\x61\x6d\154\72\x41\165\164\x68\x6e\x43\x6f\x6e\164\x65\x78\164\103\x6c\x61\x73\x73\x52\x65\146", $this->authnContextClassRef);
        wE:
        if (empty($this->authnContextDecl)) {
            goto ap;
        }
        $this->authnContextDecl->toXML($bN);
        ap:
        if (empty($this->authnContextDeclRef)) {
            goto jI;
        }
        UtilitiesSAML::addString($bN, "\165\162\x6e\x3a\157\x61\163\x69\163\x3a\156\x61\155\145\163\x3a\x74\143\x3a\123\x41\115\114\72\x32\x2e\60\72\x61\163\x73\145\162\x74\x69\x6f\156", "\163\141\155\154\x3a\x41\165\164\150\156\103\157\156\164\145\x78\x74\x44\145\x63\154\122\145\146", $this->authnContextDeclRef);
        jI:
        UtilitiesSAML::addStrings($bN, "\x75\x72\156\x3a\157\x61\x73\151\x73\x3a\x6e\x61\x6d\145\163\x3a\164\x63\x3a\x53\x41\115\x4c\72\62\x2e\60\x3a\x61\163\x73\145\x72\164\x69\x6f\156", "\163\141\155\x6c\x3a\x41\x75\164\150\145\x6e\164\151\143\x61\164\x69\x6e\x67\x41\x75\x74\150\157\162\x69\164\171", FALSE, $this->AuthenticatingAuthority);
    }
    private function addAttributeStatement(DOMElement $bb)
    {
        if (!empty($this->attributes)) {
            goto UM;
        }
        return;
        UM:
        $AW = $bb->ownerDocument;
        $TH = $AW->createElementNS("\165\x72\x6e\x3a\x6f\x61\163\151\163\x3a\156\141\x6d\145\x73\72\x74\143\72\123\101\x4d\x4c\72\62\x2e\x30\x3a\x61\163\163\x65\x72\164\x69\x6f\x6e", "\163\x61\x6d\154\x3a\101\164\x74\x72\x69\x62\165\164\x65\x53\164\141\164\145\155\145\156\x74");
        $bb->appendChild($TH);
        foreach ($this->attributes as $JN => $nr) {
            $d1 = $AW->createElementNS("\165\162\156\72\x6f\x61\x73\151\x73\72\156\141\155\145\x73\72\164\x63\72\123\x41\115\x4c\x3a\x32\x2e\60\x3a\141\x73\163\145\162\x74\x69\157\156", "\163\x61\x6d\154\72\101\x74\x74\x72\x69\x62\x75\x74\145");
            $TH->appendChild($d1);
            $d1->setAttribute("\116\x61\x6d\145", $JN);
            if (!($this->nameFormat !== "\x75\162\x6e\x3a\157\x61\x73\x69\163\x3a\x6e\141\155\145\163\72\164\143\72\x53\101\x4d\114\72\62\x2e\60\x3a\141\164\x74\x72\x6e\x61\155\x65\55\146\157\x72\x6d\141\x74\72\165\x6e\163\x70\145\x63\151\146\x69\x65\144")) {
                goto qi;
            }
            $d1->setAttribute("\x4e\141\155\x65\x46\157\162\x6d\x61\164", $this->nameFormat);
            qi:
            foreach ($nr as $UH) {
                if (is_string($UH)) {
                    goto Ox;
                }
                if (is_int($UH)) {
                    goto O7;
                }
                $Zc = NULL;
                goto I7;
                Ox:
                $Zc = "\x78\x73\x3a\x73\x74\162\x69\156\147";
                goto I7;
                O7:
                $Zc = "\170\x73\x3a\x69\156\164\145\147\x65\x72";
                I7:
                $UL = $AW->createElementNS("\165\162\156\72\157\x61\x73\151\163\x3a\156\141\155\145\x73\x3a\x74\143\x3a\x53\x41\x4d\114\72\62\56\x30\72\x61\163\x73\x65\x72\164\151\157\156", "\163\x61\x6d\x6c\x3a\101\164\x74\162\151\x62\165\164\145\126\141\154\x75\145");
                $d1->appendChild($UL);
                if (!($Zc !== NULL)) {
                    goto mc;
                }
                $UL->setAttributeNS("\x68\x74\164\x70\72\x2f\x2f\167\x77\167\56\167\63\x2e\x6f\x72\x67\57\x32\60\x30\61\57\x58\115\114\123\x63\x68\x65\155\x61\55\x69\156\163\164\x61\156\x63\x65", "\x78\x73\x69\x3a\x74\171\160\145", $Zc);
                mc:
                if (!is_null($UH)) {
                    goto d7;
                }
                $UL->setAttributeNS("\x68\x74\x74\x70\72\57\x2f\x77\x77\167\x2e\x77\63\56\x6f\162\147\57\x32\60\x30\x31\x2f\130\115\114\123\x63\150\x65\155\141\55\151\156\163\164\141\156\x63\145", "\170\x73\x69\72\x6e\x69\x6c", "\x74\162\x75\145");
                d7:
                if ($UH instanceof DOMNodeList) {
                    goto sP;
                }
                $UL->appendChild($AW->createTextNode($UH));
                goto wo;
                sP:
                $Vv = 0;
                Fj:
                if (!($Vv < $UH->length)) {
                    goto cN;
                }
                $Il = $AW->importNode($UH->item($Vv), TRUE);
                $UL->appendChild($Il);
                VU:
                $Vv++;
                goto Fj;
                cN:
                wo:
                xQ:
            }
            Z7:
            Ft:
        }
        D2:
    }
    private function addEncryptedAttributeStatement(DOMElement $bb)
    {
        if (!($this->requiredEncAttributes == FALSE)) {
            goto XS;
        }
        return;
        XS:
        $AW = $bb->ownerDocument;
        $TH = $AW->createElementNS("\x75\162\156\x3a\157\x61\x73\151\163\x3a\156\141\155\x65\x73\72\x74\x63\x3a\123\x41\x4d\x4c\x3a\x32\56\60\x3a\141\x73\x73\145\162\x74\x69\x6f\x6e", "\163\141\155\x6c\72\101\164\x74\162\x69\142\x75\164\145\x53\164\x61\x74\145\155\145\x6e\x74");
        $bb->appendChild($TH);
        foreach ($this->attributes as $JN => $nr) {
            $ev = new DOMDocument();
            $d1 = $ev->createElementNS("\165\162\x6e\72\157\x61\x73\x69\x73\x3a\x6e\x61\155\145\163\72\x74\x63\72\x53\x41\x4d\x4c\x3a\62\x2e\x30\72\x61\x73\x73\x65\x72\x74\x69\x6f\x6e", "\x73\141\155\154\x3a\101\x74\164\x72\x69\142\x75\164\145");
            $d1->setAttribute("\116\141\x6d\145", $JN);
            $ev->appendChild($d1);
            if (!($this->nameFormat !== "\165\x72\x6e\72\x6f\141\x73\x69\163\72\x6e\x61\155\145\163\72\x74\143\x3a\123\101\115\x4c\x3a\62\x2e\x30\x3a\x61\164\x74\162\x6e\x61\155\x65\x2d\x66\x6f\x72\x6d\141\164\x3a\165\156\163\160\145\x63\x69\x66\x69\x65\x64")) {
                goto li;
            }
            $d1->setAttribute("\116\x61\155\x65\x46\157\x72\155\141\x74", $this->nameFormat);
            li:
            foreach ($nr as $UH) {
                if (is_string($UH)) {
                    goto cc;
                }
                if (is_int($UH)) {
                    goto gR;
                }
                $Zc = NULL;
                goto PL;
                cc:
                $Zc = "\x78\163\72\x73\164\162\x69\156\147";
                goto PL;
                gR:
                $Zc = "\170\163\72\151\156\164\145\x67\x65\162";
                PL:
                $UL = $ev->createElementNS("\x75\x72\x6e\72\x6f\141\163\x69\x73\x3a\156\141\x6d\x65\163\72\164\143\72\x53\x41\115\x4c\x3a\x32\56\x30\72\141\x73\x73\145\x72\x74\x69\157\156", "\163\141\x6d\154\72\x41\164\164\x72\151\x62\x75\x74\x65\x56\141\x6c\165\145");
                $d1->appendChild($UL);
                if (!($Zc !== NULL)) {
                    goto Y9;
                }
                $UL->setAttributeNS("\x68\x74\x74\160\72\57\57\167\x77\167\56\x77\63\x2e\157\x72\147\57\62\60\60\61\x2f\x58\x4d\114\123\x63\150\145\x6d\141\55\151\156\163\164\x61\156\x63\145", "\x78\163\151\72\164\171\160\x65", $Zc);
                Y9:
                if ($UH instanceof DOMNodeList) {
                    goto wt;
                }
                $UL->appendChild($ev->createTextNode($UH));
                goto bQ;
                wt:
                $Vv = 0;
                NS:
                if (!($Vv < $UH->length)) {
                    goto zW;
                }
                $Il = $ev->importNode($UH->item($Vv), TRUE);
                $UL->appendChild($Il);
                l9:
                $Vv++;
                goto NS;
                zW:
                bQ:
                wj:
            }
            Gs:
            $VX = new XMLSecEncSAML();
            $VX->setNode($ev->documentElement);
            $VX->type = "\150\164\164\x70\x3a\x2f\57\x77\167\x77\x2e\x77\63\x2e\x6f\162\x67\57\62\x30\60\61\57\60\64\x2f\x78\155\154\145\156\143\x23\x45\154\x65\x6d\145\156\x74";
            $XX = new XMLSecurityKeySAML(XMLSecurityKeySAML::AES256_CBC);
            $XX->generateSessionKey();
            $VX->encryptKey($this->encryptionKey, $XX);
            $pl = $VX->encryptNode($XX);
            $O2 = $AW->createElementNS("\x75\x72\156\72\x6f\141\163\x69\163\x3a\x6e\141\155\145\x73\x3a\164\x63\72\x53\101\115\114\72\x32\56\x30\72\x61\x73\x73\x65\162\x74\151\x6f\x6e", "\x73\x61\x6d\x6c\x3a\x45\156\x63\x72\171\160\x74\x65\x64\101\164\x74\x72\151\x62\x75\x74\x65");
            $TH->appendChild($O2);
            $OH = $AW->importNode($pl, TRUE);
            $O2->appendChild($OH);
            TK:
        }
        qj:
    }
    public function getSignatureData()
    {
        return $this->signatureData;
    }
}
