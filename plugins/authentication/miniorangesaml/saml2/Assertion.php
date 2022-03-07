<?php
defined('_JEXEC') or die;
/**
 * This file is part of miniOrange SAML plugin.
 *
 * miniOrange SAML plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * miniOrange SAML plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with miniOrange SAML plugin.  If not, see <http://www.gnu.org/licenses/>.
 */
 
//include 'SAML_Utilities.php';
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
	
    public function __construct(DOMElement $xml = NULL)
    {
        $this->id = SAML_Utilities::generateId();
        $this->issueInstant = SAML_Utilities::generateTimestamp();
        $this->issuer = '';
        $this->authnInstant = SAML_Utilities::generateTimestamp();
        $this->attributes = array();
        $this->nameFormat = 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';
        $this->certificates = array();
        $this->AuthenticatingAuthority = array();
        $this->SubjectConfirmation = array();

        if ($xml === NULL) {
            return;
        }
		
		
		
        if (!$xml->hasAttribute('ID')) {
            throw new Exception('Missing ID attribute on SAML assertion.');
        }
        $this->id = $xml->getAttribute('ID');
		
        if ($xml->getAttribute('Version') !== '2.0') {
            /* Currently a very strict check. */
            throw new Exception('Unsupported version: ' . $xml->getAttribute('Version'));
        }

        $this->issueInstant = SAML_Utilities::xsDateTimeToTimestamp($xml->getAttribute('IssueInstant'));
		
        $issuer = SAML_Utilities::xpQuery($xml, './saml_assertion:Issuer');
        if (empty($issuer)) {
            throw new Exception('Missing <saml:Issuer> in assertion.');
        }
        $this->issuer = trim($issuer[0]->textContent);
		
        $this->parseConditions($xml);
        $this->parseAuthnStatement($xml);
        $this->parseAttributes($xml);
        $this->parseSignature($xml);
        $this->parseSubject($xml);
		
		
    }

    /**
     * Parse subject in assertion.
     *
     * @param DOMElement $xml The assertion XML element.
     * @throws Exception
     */
    private function parseSubject(DOMElement $xml)
    {
        $subject = SAML_Utilities::xpQuery($xml, './saml_assertion:Subject');
        if (empty($subject)) {
            /* No Subject node. */

            return;
        } elseif (count($subject) > 1) {
            throw new Exception('More than one <saml:Subject> in <saml:Assertion>.');
        }

        $subject = $subject[0];

        $nameId = SAML_Utilities::xpQuery(
            $subject,
            './saml_assertion:NameID | ./saml_assertion:EncryptedID/xenc:EncryptedData'
        );
        if (empty($nameId)) {
			if (array_key_exists ( 'RelayState', $_REQUEST ) && ($_REQUEST['RelayState'] == 'testValidate') ) {
			echo '<div style="font-family:Calibri;padding:0 3%;">';
			echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div>
			<div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong> NameID not found in SAML Response.</p>
			<p>Please ask your Identity Provider to send the NameID attribute in SAML Response. </p>
			</div>
			<div style="margin:3%;display:block;text-align:center;">
			<div style="margin:3%;display:block;text-align:center;"><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="Done" onClick="self.close();"></div>';
							exit;
			}
			else{
			 echo ' <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>We could not sign you in. Please contact your Administrator.</p></div>
                  <div style="margin:3%;display:block;text-align:center;">
                        <form action='.JURI::root().'><input style="padding:1%;width:150px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="submit" value="Back to home"></form></div>';
                  exit;
			}
			
        } elseif (count($nameId) > 1) {
            throw new Exception('More than one <saml:NameID> or <saml:EncryptedD> in <saml:Subject>.');
        }
        $nameId = $nameId[0];
        if ($nameId->localName === 'EncryptedData') {
            /* The NameID element is encrypted. */
            $this->encryptedNameId = $nameId;
        } else {
            $this->nameId = SAML_Utilities::parseNameId($nameId);
        }

    }

    /**
     * Parse conditions in assertion.
     *
     * @param DOMElement $xml The assertion XML element.
     * @throws Exception
     */
    private function parseConditions(DOMElement $xml)
    {
        $conditions = SAML_Utilities::xpQuery($xml, './saml_assertion:Conditions');
        if (empty($conditions)) {
            /* No <saml:Conditions> node. */

            return;
        } elseif (count($conditions) > 1) {
            throw new Exception('More than one <saml:Conditions> in <saml:Assertion>.');
        }
        $conditions = $conditions[0];

        if ($conditions->hasAttribute('NotBefore')) {
            $notBefore = SAML_Utilities::xsDateTimeToTimestamp($conditions->getAttribute('NotBefore'));
            if ($this->notBefore === NULL || $this->notBefore < $notBefore) {
                $this->notBefore = $notBefore;
            }
        }
        if ($conditions->hasAttribute('NotOnOrAfter')) {
            $notOnOrAfter = SAML_Utilities::xsDateTimeToTimestamp($conditions->getAttribute('NotOnOrAfter'));
            if ($this->notOnOrAfter === NULL || $this->notOnOrAfter > $notOnOrAfter) {
                $this->notOnOrAfter = $notOnOrAfter;
            }
        }

        for ($node = $conditions->firstChild; $node !== NULL; $node = $node->nextSibling) {
            if ($node instanceof DOMText) {
                continue;
            }
            if ($node->namespaceURI !== 'urn:oasis:names:tc:SAML:2.0:assertion') {
                throw new Exception('Unknown namespace of condition: ' . var_export($node->namespaceURI, TRUE));
            }
            switch ($node->localName) {
                case 'AudienceRestriction':
                    $audiences = SAML_Utilities::extractStrings($node, 'urn:oasis:names:tc:SAML:2.0:assertion', 'Audience');
                    if ($this->validAudiences === NULL) {
                        /* The first (and probably last) AudienceRestriction element. */
                        $this->validAudiences = $audiences;

                    } else {
                        /*
                         * The set of AudienceRestriction are ANDed together, so we need
                         * the subset that are present in all of them.
                         */
                        $this->validAudiences = array_intersect($this->validAudiences, $audiences);
                    }
                    break;
                case 'OneTimeUse':
                    /* Currently ignored. */
                    break;
                case 'ProxyRestriction':
                    /* Currently ignored. */
                    break;
                default:
                    throw new Exception('Unknown condition: ' . var_export($node->localName, TRUE));
            }
        }

    }

    /**
     * Parse AuthnStatement in assertion.
     *
     * @param DOMElement $xml The assertion XML element.
     * @throws Exception
     */
    private function parseAuthnStatement(DOMElement $xml)
    {
        $authnStatements = SAML_Utilities::xpQuery($xml, './saml_assertion:AuthnStatement');
        if (empty($authnStatements)) {
            $this->authnInstant = NULL;

            return;
        } elseif (count($authnStatements) > 1) {
            throw new Exception('More that one <saml:AuthnStatement> in <saml:Assertion> not supported.');
        }
        $authnStatement = $authnStatements[0];

        if (!$authnStatement->hasAttribute('AuthnInstant')) {
            throw new Exception('Missing required AuthnInstant attribute on <saml:AuthnStatement>.');
        }
        $this->authnInstant = SAML_Utilities::xsDateTimeToTimestamp($authnStatement->getAttribute('AuthnInstant'));

        if ($authnStatement->hasAttribute('SessionNotOnOrAfter')) {
            $this->sessionNotOnOrAfter = SAML_Utilities::xsDateTimeToTimestamp($authnStatement->getAttribute('SessionNotOnOrAfter'));
        }

        if ($authnStatement->hasAttribute('SessionIndex')) {
            $this->sessionIndex = $authnStatement->getAttribute('SessionIndex');
        }

        $this->parseAuthnContext($authnStatement);
    }

    /**
     * Parse AuthnContext in AuthnStatement.
     *
     * @param DOMElement $authnStatementEl
     * @throws Exception
     */
    private function parseAuthnContext(DOMElement $authnStatementEl)
    {
        // Get the AuthnContext element
        $authnContexts = SAML_Utilities::xpQuery($authnStatementEl, './saml_assertion:AuthnContext');
        if (count($authnContexts) > 1) {
            throw new Exception('More than one <saml:AuthnContext> in <saml:AuthnStatement>.');
        } elseif (empty($authnContexts)) {
            throw new Exception('Missing required <saml:AuthnContext> in <saml:AuthnStatement>.');
        }
        $authnContextEl = $authnContexts[0];

        // Get the AuthnContextDeclRef (if available)
        $authnContextDeclRefs = SAML_Utilities::xpQuery($authnContextEl, './saml_assertion:AuthnContextDeclRef');
        if (count($authnContextDeclRefs) > 1) {
            throw new Exception(
                'More than one <saml:AuthnContextDeclRef> found?'
            );
        } elseif (count($authnContextDeclRefs) === 1) {
            $this->setAuthnContextDeclRef(trim($authnContextDeclRefs[0]->textContent));
        }

        // Get the AuthnContextDecl (if available)
        $authnContextDecls = SAML_Utilities::xpQuery($authnContextEl, './saml_assertion:AuthnContextDecl');
        if (count($authnContextDecls) > 1) {
            throw new Exception(
                'More than one <saml:AuthnContextDecl> found?'
            );
        } elseif (count($authnContextDecls) === 1) {
            $this->setAuthnContextDecl(new SAML2_XML_Chunk($authnContextDecls[0]));
        }

        // Get the AuthnContextClassRef (if available)
        $authnContextClassRefs = SAML_Utilities::xpQuery($authnContextEl, './saml_assertion:AuthnContextClassRef');
        if (count($authnContextClassRefs) > 1) {
            throw new Exception('More than one <saml:AuthnContextClassRef> in <saml:AuthnContext>.');
        } elseif (count($authnContextClassRefs) === 1) {
            $this->setAuthnContextClassRef(trim($authnContextClassRefs[0]->textContent));
        }

        // Constraint from XSD: MUST have one of the three
        if (empty($this->authnContextClassRef) && empty($this->authnContextDecl) && empty($this->authnContextDeclRef)) {
            throw new Exception(
                'Missing either <saml:AuthnContextClassRef> or <saml:AuthnContextDeclRef> or <saml:AuthnContextDecl>'
            );
        }

        $this->AuthenticatingAuthority = SAML_Utilities::extractStrings(
            $authnContextEl,
            'urn:oasis:names:tc:SAML:2.0:assertion',
            'AuthenticatingAuthority'
        );
    }

    /**
     * Parse attribute statements in assertion.
     *
     * @param DOMElement $xml The XML element with the assertion.
     * @throws Exception
     */
    private function parseAttributes(DOMElement $xml)
    {
        $firstAttribute = TRUE;
        $attributes = SAML_Utilities::xpQuery($xml, './saml_assertion:AttributeStatement/saml_assertion:Attribute');
        foreach ($attributes as $attribute) {
            if (!$attribute->hasAttribute('Name')) {
                throw new Exception('Missing name on <saml:Attribute> element.');
            }
            $name = $attribute->getAttribute('Name');

            if ($attribute->hasAttribute('NameFormat')) {
                $nameFormat = $attribute->getAttribute('NameFormat');
            } else {
                $nameFormat = 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';
            }

            if ($firstAttribute) {
                $this->nameFormat = $nameFormat;
                $firstAttribute = FALSE;
            } else {
                if ($this->nameFormat !== $nameFormat) {
                    $this->nameFormat = 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';
                }
            }

            if (!array_key_exists($name, $this->attributes)) {
                $this->attributes[$name] = array();
            }

            $values = SAML_Utilities::xpQuery($attribute, './saml_assertion:AttributeValue');
            foreach ($values as $value) {
                $this->attributes[$name][] = trim($value->textContent);
            }
        }
    }

 

    /**
     * Parse signature on assertion.
     *
     * @param DOMElement $xml The assertion XML element.
     */
    private function parseSignature(DOMElement $xml)
    {
        /* Validate the signature element of the message. */
        $sig = SAML_Utilities::validateElement($xml);
		
        if ($sig !== FALSE) {
            $this->wasSignedAtConstruction = TRUE;
            $this->certificates = $sig['Certificates'];
            $this->signatureData = $sig;
        }
    }

    /**
     * Validate this assertion against a public key.
     *
     * If no signature was present on the assertion, we will return FALSE.
     * Otherwise, TRUE will be returned. An exception is thrown if the
     * signature validation fails.
     *
     * @param  XMLSecurityKey $key The key we should check against.
     * @return boolean        TRUE if successful, FALSE if it is unsigned.
     */
    public function validate(XMLSecurityKey $key)
    {
        if ($this->signatureData === NULL) {
            return FALSE;
        }

        SAML_Utilities::validateSignature($this->signatureData, $key);

        return TRUE;
    }

    /**
     * Retrieve the identifier of this assertion.
     *
     * @return string The identifier of this assertion.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the identifier of this assertion.
     *
     * @param string $id The new identifier of this assertion.
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Retrieve the issue timestamp of this assertion.
     *
     * @return int The issue timestamp of this assertion, as an UNIX timestamp.
     */
    public function getIssueInstant()
    {
        return $this->issueInstant;
    }

    /**
     * Set the issue timestamp of this assertion.
     *
     * @param int $issueInstant The new issue timestamp of this assertion, as an UNIX timestamp.
     */
    public function setIssueInstant($issueInstant)
    {
        $this->issueInstant = $issueInstant;
    }

    /**
     * Retrieve the issuer if this assertion.
     *
     * @return string The issuer of this assertion.
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * Set the issuer of this message.
     *
     * @param string $issuer The new issuer of this assertion.
     */
    public function setIssuer($issuer)
    {
        $this->issuer = $issuer;
    }

    /**
     * Retrieve the NameId of the subject in the assertion.
     *
     * The returned NameId is in the format used by SAML_Utilities::addNameId().
     *
     * @see SAML_Utilities::addNameId()
     * @return array|NULL The name identifier of the assertion.
     * @throws Exception
     */
    public function getNameId()
    {
        if ($this->encryptedNameId !== NULL) {
            throw new Exception('Attempted to retrieve encrypted NameID without decrypting it first.');
        }

        return $this->nameId;
    }

    /**
     * Set the NameId of the subject in the assertion.
     *
     * The NameId must be in the format accepted by SAML_Utilities::addNameId().
     *
     * @see SAML_Utilities::addNameId()
     * @param array|NULL $nameId The name identifier of the assertion.
     */
    public function setNameId($nameId)
    {
        $this->nameId = $nameId;
    }

    /**
     * Check whether the NameId is encrypted.
     *
     * @return TRUE if the NameId is encrypted, FALSE if not.
     */
    public function isNameIdEncrypted()
    {
        if ($this->encryptedNameId !== NULL) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Encrypt the NameID in the Assertion.
     *
     * @param XMLSecurityKey $key The encryption key.
     */
    public function encryptNameId(XMLSecurityKey $key)
    {
        /* First create a XML representation of the NameID. */
        $doc = new DOMDocument();
        $root = $doc->createElement('root');
        $doc->appendChild($root);
        SAML_Utilities::addNameId($root, $this->nameId);
        $nameId = $root->firstChild;

        SAML_Utilities::getContainer()->debugMessage($nameId, 'encrypt');

        /* Encrypt the NameID. */
        $enc = new XMLSecEnc();
        $enc->setNode($nameId);
        // @codingStandardsIgnoreStart
        $enc->type = XMLSecEnc::Element;
        // @codingStandardsIgnoreEnd

        $symmetricKey = new XMLSecurityKey(XMLSecurityKey::AES128_CBC);
        $symmetricKey->generateSessionKey();
        $enc->encryptKey($key, $symmetricKey);

        $this->encryptedNameId = $enc->encryptNode($symmetricKey);
        $this->nameId = NULL;
    }

    /**
     * Decrypt the NameId of the subject in the assertion.
     *
     * @param XMLSecurityKey $key       The decryption key.
     * @param array          $blacklist Blacklisted decryption algorithms.
     */
    public function decryptNameId(XMLSecurityKey $key, array $blacklist = array())
    {
        if ($this->encryptedNameId === NULL) {
            /* No NameID to decrypt. */

            return;
        }

        $nameId = SAML_Utilities::decryptElement($this->encryptedNameId, $key, $blacklist);
        SAML_Utilities::getContainer()->debugMessage($nameId, 'decrypt');
        $this->nameId = SAML_Utilities::parseNameId($nameId);

        $this->encryptedNameId = NULL;
    }

  

    /**
     * Retrieve the earliest timestamp this assertion is valid.
     *
     * This function returns NULL if there are no restrictions on how early the
     * assertion can be used.
     *
     * @return int|NULL The earliest timestamp this assertion is valid.
     */
    public function getNotBefore()
    {
        return $this->notBefore;
    }

    /**
     * Set the earliest timestamp this assertion can be used.
     *
     * Set this to NULL if no limit is required.
     *
     * @param int|NULL $notBefore The earliest timestamp this assertion is valid.
     */
    public function setNotBefore($notBefore)
    {
        $this->notBefore = $notBefore;
    }

    /**
     * Retrieve the expiration timestamp of this assertion.
     *
     * This function returns NULL if there are no restrictions on how
     * late the assertion can be used.
     *
     * @return int|NULL The latest timestamp this assertion is valid.
     */
    public function getNotOnOrAfter()
    {
        return $this->notOnOrAfter;
    }

    /**
     * Set the expiration timestamp of this assertion.
     *
     * Set this to NULL if no limit is required.
     *
     * @param int|NULL $notOnOrAfter The latest timestamp this assertion is valid.
     */
    public function setNotOnOrAfter($notOnOrAfter)
    {
        $this->notOnOrAfter = $notOnOrAfter;
    }



    /**
     * Retrieve the audiences that are allowed to receive this assertion.
     *
     * This may be NULL, in which case all audiences are allowed.
     *
     * @return array|NULL The allowed audiences.
     */
    public function getValidAudiences()
    {
        return $this->validAudiences;
    }

    /**
     * Set the audiences that are allowed to receive this assertion.
     *
     * This may be NULL, in which case all audiences are allowed.
     *
     * @param array|NULL $validAudiences The allowed audiences.
     */
    public function setValidAudiences(array $validAudiences = NULL)
    {
        $this->validAudiences = $validAudiences;
    }

    /**
     * Retrieve the AuthnInstant of the assertion.
     *
     * @return int|NULL The timestamp the user was authenticated, or NULL if the user isn't authenticated.
     */
    public function getAuthnInstant()
    {
        return $this->authnInstant;
    }


    /**
     * Set the AuthnInstant of the assertion.
     *
     * @param int|NULL $authnInstant Timestamp the user was authenticated, or NULL if we don't want an AuthnStatement.
     */
    public function setAuthnInstant($authnInstant)
    {
        $this->authnInstant = $authnInstant;
    }

    /**
     * Retrieve the session expiration timestamp.
     *
     * This function returns NULL if there are no restrictions on the
     * session lifetime.
     *
     * @return int|NULL The latest timestamp this session is valid.
     */
    public function getSessionNotOnOrAfter()
    {
        return $this->sessionNotOnOrAfter;
    }

    /**
     * Set the session expiration timestamp.
     *
     * Set this to NULL if no limit is required.
     *
     * @param int|NULL $sessionNotOnOrAfter The latest timestamp this session is valid.
     */
    public function setSessionNotOnOrAfter($sessionNotOnOrAfter)
    {
        $this->sessionNotOnOrAfter = $sessionNotOnOrAfter;
    }

    /**
     * Retrieve the session index of the user at the IdP.
     *
     * @return string|NULL The session index of the user at the IdP.
     */
    public function getSessionIndex()
    {
        return $this->sessionIndex;
    }

    /**
     * Set the session index of the user at the IdP.
     *
     * Note that the authentication context must be set before the
     * session index can be inluded in the assertion.
     *
     * @param string|NULL $sessionIndex The session index of the user at the IdP.
     */
    public function setSessionIndex($sessionIndex)
    {
        $this->sessionIndex = $sessionIndex;
    }

    /**
     * Retrieve the authentication method used to authenticate the user.
     *
     * This will return NULL if no authentication statement was
     * included in the assertion.
     *
     * Note that this returns either the AuthnContextClassRef or the AuthnConextDeclRef, whose definition overlaps
     * but is slightly different (consult the specification for more information).
     * This was done to work around an old bug of Shibboleth ( https://bugs.internet2.edu/jira/browse/SIDP-187 ).
     * Should no longer be required, please use either getAuthnConextClassRef or getAuthnContextDeclRef.
     *
     * @deprecated use getAuthnContextClassRef
     * @return string|NULL The authentication method.
     */
    public function getAuthnContext()
    {
        if (!empty($this->authnContextClassRef)) {
            return $this->authnContextClassRef;
        }
        if (!empty($this->authnContextDeclRef)) {
            return $this->authnContextDeclRef;
        }
        return NULL;
    }

    /**
     * Set the authentication method used to authenticate the user.
     *
     * If this is set to NULL, no authentication statement will be
     * included in the assertion. The default is NULL.
     *
     * @deprecated use setAuthnContextClassRef
     * @param string|NULL $authnContext The authentication method.
     */
    public function setAuthnContext($authnContext)
    {
        $this->setAuthnContextClassRef($authnContext);
    }

    /**
     * Retrieve the authentication method used to authenticate the user.
     *
     * This will return NULL if no authentication statement was
     * included in the assertion.
     *
     * @return string|NULL The authentication method.
     */
    public function getAuthnContextClassRef()
    {
        return $this->authnContextClassRef;
    }

    /**
     * Set the authentication method used to authenticate the user.
     *
     * If this is set to NULL, no authentication statement will be
     * included in the assertion. The default is NULL.
     *
     * @param string|NULL $authnContextClassRef The authentication method.
     */
    public function setAuthnContextClassRef($authnContextClassRef)
    {
        $this->authnContextClassRef = $authnContextClassRef;
    }

    /**
     * Set the authentication context declaration.
     *
     * @param \SAML2_XML_Chunk $authnContextDecl
     * @throws Exception
     */
    public function setAuthnContextDecl(SAML2_XML_Chunk $authnContextDecl)
    {
        if (!empty($this->authnContextDeclRef)) {
            throw new Exception(
                'AuthnContextDeclRef is already registered! May only have either a Decl or a DeclRef, not both!'
            );
        }

        $this->authnContextDecl = $authnContextDecl;
    }

    /**
     * Get the authentication context declaration.
     *
     * See:
     * @url http://docs.oasis-open.org/security/saml/v2.0/saml-authn-context-2.0-os.pdf
     *
     * @return \SAML2_XML_Chunk|NULL
     */
    public function getAuthnContextDecl()
    {
        return $this->authnContextDecl;
    }

    /**
     * Set the authentication context declaration reference.
     *
     * @param string $authnContextDeclRef
     * @throws Exception
     */
    public function setAuthnContextDeclRef($authnContextDeclRef)
    {
        if (!empty($this->authnContextDecl)) {
            throw new Exception(
                'AuthnContextDecl is already registered! May only have either a Decl or a DeclRef, not both!'
            );
        }

        $this->authnContextDeclRef = $authnContextDeclRef;
    }

    /**
     * Get the authentication context declaration reference.
     * URI reference that identifies an authentication context declaration.
     *
     * The URI reference MAY directly resolve into an XML document containing the referenced declaration.
     *
     * @return string
     */
    public function getAuthnContextDeclRef()
    {
        return $this->authnContextDeclRef;
    }

    /**
     * Retrieve the AuthenticatingAuthority.
     *
     *
     * @return array
     */
    public function getAuthenticatingAuthority()
    {
        return $this->AuthenticatingAuthority;
    }

    /**
     * Set the AuthenticatingAuthority
     *
     *
     * @param array.
     */
    public function setAuthenticatingAuthority($authenticatingAuthority)
    {
        $this->AuthenticatingAuthority = $authenticatingAuthority;
    }

    /**
     * Retrieve all attributes.
     *
     * @return array All attributes, as an associative array.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Replace all attributes.
     *
     * @param array $attributes All new attributes, as an associative array.
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Retrieve the NameFormat used on all attributes.
     *
     * If more than one NameFormat is used in the received attributes, this
     * returns the unspecified NameFormat.
     *
     * @return string The NameFormat used on all attributes.
     */
    public function getAttributeNameFormat()
    {
        return $this->nameFormat;
    }

    /**
     * Set the NameFormat used on all attributes.
     *
     * @param string $nameFormat The NameFormat used on all attributes.
     */
    public function setAttributeNameFormat($nameFormat)
    {
        $this->nameFormat = $nameFormat;
    }

    /**
     * Retrieve the SubjectConfirmation elements we have in our Subject element.
     *
     * @return array Array of SAML2_XML_saml_SubjectConfirmation elements.
     */
    public function getSubjectConfirmation()
    {
        return $this->SubjectConfirmation;
    }

    /**
     * Set the SubjectConfirmation elements that should be included in the assertion.
     *
     * @param array $SubjectConfirmation Array of SAML2_XML_saml_SubjectConfirmation elements.
     */
    public function setSubjectConfirmation(array $SubjectConfirmation)
    {
        $this->SubjectConfirmation = $SubjectConfirmation;
    }

    /**
     * Retrieve the private key we should use to sign the assertion.
     *
     * @return XMLSecurityKey|NULL The key, or NULL if no key is specified.
     */
    public function getSignatureKey()
    {
        return $this->signatureKey;
    }

    /**
     * Set the private key we should use to sign the assertion.
     *
     * If the key is NULL, the assertion will be sent unsigned.
     *
     * @param XMLSecurityKey|NULL $signatureKey
     */
    public function setSignatureKey(XMLsecurityKey $signatureKey = NULL)
    {
        $this->signatureKey = $signatureKey;
    }

    /**
     * Return the key we should use to encrypt the assertion.
     *
     * @return XMLSecurityKey|NULL The key, or NULL if no key is specified..
     *
     */
    public function getEncryptionKey()
    {
        return $this->encryptionKey;
    }

    /**
     * Set the private key we should use to encrypt the attributes.
     *
     * @param XMLSecurityKey|NULL $Key
     */
    public function setEncryptionKey(XMLSecurityKey $Key = NULL)
    {
        $this->encryptionKey = $Key;
    }

    /**
     * Set the certificates that should be included in the assertion.
     *
     * The certificates should be strings with the PEM encoded data.
     *
     * @param array $certificates An array of certificates.
     */
    public function setCertificates(array $certificates)
    {
        $this->certificates = $certificates;
    }

    /**
     * Retrieve the certificates that are included in the assertion.
     *
     * @return array An array of certificates.
     */
    public function getCertificates()
    {
        return $this->certificates;
    }

    /**
     * @return bool
     */
    public function getWasSignedAtConstruction()
    {
        return $this->wasSignedAtConstruction;
    }

    /**
     * Convert this assertion to an XML element.
     *
     * @param  DOMNode|NULL $parentElement The DOM node the assertion should be created in.
     * @return DOMElement   This assertion.
     */
    public function toXML(DOMNode $parentElement = NULL)
    {
        if ($parentElement === NULL) {
            $document = new DOMDocument();
            $parentElement = $document;
        } else {
            $document = $parentElement->ownerDocument;
        }

        $root = $document->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml:' . 'Assertion');
        $parentElement->appendChild($root);

        /* Ugly hack to add another namespace declaration to the root element. */
        $root->setAttributeNS('urn:oasis:names:tc:SAML:2.0:protocol', 'samlp:tmp', 'tmp');
        $root->removeAttributeNS('urn:oasis:names:tc:SAML:2.0:protocol', 'tmp');
        $root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:tmp', 'tmp');
        $root->removeAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'tmp');
        $root->setAttributeNS('http://www.w3.org/2001/XMLSchema', 'xs:tmp', 'tmp');
        $root->removeAttributeNS('http://www.w3.org/2001/XMLSchema', 'tmp');

        $root->setAttribute('ID', $this->id);
        $root->setAttribute('Version', '2.0');
        $root->setAttribute('IssueInstant', gmdate('Y-m-d\TH:i:s\Z', $this->issueInstant));

        $issuer = SAML_Utilities::addString($root, 'urn:oasis:names:tc:SAML:2.0:assertion', 'saml:Issuer', $this->issuer);

        $this->addSubject($root);
        $this->addConditions($root);
        $this->addAuthnStatement($root);
        if ($this->requiredEncAttributes == FALSE) {
            $this->addAttributeStatement($root);
        } else {
            $this->addEncryptedAttributeStatement($root);
        }

        if ($this->signatureKey !== NULL) {
            SAML_Utilities::insertSignature($this->signatureKey, $this->certificates, $root, $issuer->nextSibling);
        }

        return $root;
    }

    /**
     * Add a Subject-node to the assertion.
     *
     * @param DOMElement $root The assertion element we should add the subject to.
     */
    private function addSubject(DOMElement $root)
    {
        if ($this->nameId === NULL && $this->encryptedNameId === NULL) {
            /* We don't have anything to create a Subject node for. */

            return;
        }

        $subject = $root->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml:Subject');
        $root->appendChild($subject);

        if ($this->encryptedNameId === NULL) {
            SAML_Utilities::addNameId($subject, $this->nameId);
        } else {
            $eid = $subject->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml:' . 'EncryptedID');
            $subject->appendChild($eid);
            $eid->appendChild($subject->ownerDocument->importNode($this->encryptedNameId, TRUE));
        }

        foreach ($this->SubjectConfirmation as $sc) {
            $sc->toXML($subject);
        }
    }


    /**
     * Add a Conditions-node to the assertion.
     *
     * @param DOMElement $root The assertion element we should add the conditions to.
     */
    private function addConditions(DOMElement $root)
    {
        $document = $root->ownerDocument;

        $conditions = $document->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml:Conditions');
        $root->appendChild($conditions);

        if ($this->notBefore !== NULL) {
            $conditions->setAttribute('NotBefore', gmdate('Y-m-d\TH:i:s\Z', $this->notBefore));
        }
        if ($this->notOnOrAfter !== NULL) {
            $conditions->setAttribute('NotOnOrAfter', gmdate('Y-m-d\TH:i:s\Z', $this->notOnOrAfter));
        }

        if ($this->validAudiences !== NULL) {
            $ar = $document->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml:AudienceRestriction');
            $conditions->appendChild($ar);

            SAML_Utilities::addStrings($ar, 'urn:oasis:names:tc:SAML:2.0:assertion', 'saml:Audience', FALSE, $this->validAudiences);
        }
    }


    /**
     * Add a AuthnStatement-node to the assertion.
     *
     * @param DOMElement $root The assertion element we should add the authentication statement to.
     */
    private function addAuthnStatement(DOMElement $root)
    {
        if ($this->authnInstant === NULL ||
            (
                $this->authnContextClassRef === NULL &&
                $this->authnContextDecl === NULL &&
                $this->authnContextDeclRef === NULL
            )
        ) {
            /* No authentication context or AuthnInstant => no authentication statement. */

            return;
        }

        $document = $root->ownerDocument;

        $authnStatementEl = $document->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml:AuthnStatement');
        $root->appendChild($authnStatementEl);

        $authnStatementEl->setAttribute('AuthnInstant', gmdate('Y-m-d\TH:i:s\Z', $this->authnInstant));

        if ($this->sessionNotOnOrAfter !== NULL) {
            $authnStatementEl->setAttribute('SessionNotOnOrAfter', gmdate('Y-m-d\TH:i:s\Z', $this->sessionNotOnOrAfter));
        }
        if ($this->sessionIndex !== NULL) {
            $authnStatementEl->setAttribute('SessionIndex', $this->sessionIndex);
        }

        $authnContextEl = $document->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml:AuthnContext');
        $authnStatementEl->appendChild($authnContextEl);

        if (!empty($this->authnContextClassRef)) {
            SAML_Utilities::addString(
                $authnContextEl,
                'urn:oasis:names:tc:SAML:2.0:assertion',
                'saml:AuthnContextClassRef',
                $this->authnContextClassRef
            );
        }
        if (!empty($this->authnContextDecl)) {
            $this->authnContextDecl->toXML($authnContextEl);
        }
        if (!empty($this->authnContextDeclRef)) {
            SAML_Utilities::addString(
                $authnContextEl,
                'urn:oasis:names:tc:SAML:2.0:assertion',
                'saml:AuthnContextDeclRef',
                $this->authnContextDeclRef
            );
        }

        SAML_Utilities::addStrings(
            $authnContextEl,
            'urn:oasis:names:tc:SAML:2.0:assertion',
            'saml:AuthenticatingAuthority',
            FALSE,
            $this->AuthenticatingAuthority
        );
    }


    /**
     * Add an AttributeStatement-node to the assertion.
     *
     * @param DOMElement $root The assertion element we should add the subject to.
     */
    private function addAttributeStatement(DOMElement $root)
    {
        if (empty($this->attributes)) {
            return;
        }

        $document = $root->ownerDocument;

        $attributeStatement = $document->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml:AttributeStatement');
        $root->appendChild($attributeStatement);

        foreach ($this->attributes as $name => $values) {
            $attribute = $document->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml:Attribute');
            $attributeStatement->appendChild($attribute);
            $attribute->setAttribute('Name', $name);

            if ($this->nameFormat !== 'urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified') {
                $attribute->setAttribute('NameFormat', $this->nameFormat);
            }

            foreach ($values as $value) {
                if (is_string($value)) {
                    $type = 'xs:string';
                } elseif (is_int($value)) {
                    $type = 'xs:integer';
                } else {
                    $type = NULL;
                }

                $attributeValue = $document->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml:AttributeValue');
                $attribute->appendChild($attributeValue);
                if ($type !== NULL) {
                    $attributeValue->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:type', $type);
                }
                if (is_null($value)) {
                    $attributeValue->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:nil', 'true');
                }

                if ($value instanceof DOMNodeList) {
                    for ($i = 0; $i < $value->length; $i++) {
                        $node = $document->importNode($value->item($i), TRUE);
                        $attributeValue->appendChild($node);
                    }
                } else {
                    $attributeValue->appendChild($document->createTextNode($value));
                }
            }
        }
    }


    /**
     * Add an EncryptedAttribute Statement-node to the assertion.
     *
     * @param DOMElement $root The assertion element we should add the Encrypted Attribute Statement to.
     */
    private function addEncryptedAttributeStatement(DOMElement $root)
    {
        if ($this->requiredEncAttributes == FALSE) {
            return;
        }

        $document = $root->ownerDocument;

        $attributeStatement = $document->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml:AttributeStatement');
        $root->appendChild($attributeStatement);

        foreach ($this->attributes as $name => $values) {
            $document2 = new DOMDocument();
            $attribute = $document2->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml:Attribute');
            $attribute->setAttribute('Name', $name);
            $document2->appendChild($attribute);

            if ($this->nameFormat !== 'urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified') {
                $attribute->setAttribute('NameFormat', $this->nameFormat);
            }

            foreach ($values as $value) {
                if (is_string($value)) {
                    $type = 'xs:string';
                } elseif (is_int($value)) {
                    $type = 'xs:integer';
                } else {
                    $type = NULL;
                }

                $attributeValue = $document2->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml:AttributeValue');
                $attribute->appendChild($attributeValue);
                if ($type !== NULL) {
                    $attributeValue->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:type', $type);
                }

                if ($value instanceof DOMNodeList) {
                    for ($i = 0; $i < $value->length; $i++) {
                        $node = $document2->importNode($value->item($i), TRUE);
                        $attributeValue->appendChild($node);
                    }
                } else {
                    $attributeValue->appendChild($document2->createTextNode($value));
                }
            }
            /*Once the attribute nodes are built, the are encrypted*/
            $EncAssert = new XMLSecEnc();
            $EncAssert->setNode($document2->documentElement);
            $EncAssert->type = 'http://www.w3.org/2001/04/xmlenc#Element';
            /*
             * Attributes are encrypted with a session key and this one with
             * $EncryptionKey
             */
            $symmetricKey = new XMLSecurityKey(XMLSecurityKey::AES256_CBC);
            $symmetricKey->generateSessionKey();
            $EncAssert->encryptKey($this->encryptionKey, $symmetricKey);
            $EncrNode = $EncAssert->encryptNode($symmetricKey);

            $EncAttribute = $document->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml:EncryptedAttribute');
            $attributeStatement->appendChild($EncAttribute);
            $n = $document->importNode($EncrNode, TRUE);
            $EncAttribute->appendChild($n);
        }
    }
	
	public function getSignatureData()
	{
		return $this->signatureData;
	}

}
