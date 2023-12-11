<?php
/**
 * @package     com_emundus
 * @subpackage  api
 * @author    eMundus.fr
 * @copyright (C) 2022 eMundus SOFTWARE. All rights reserved.
 * @license    GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace classes\api;

use JComponentHelper;
use JFactory;
use JLog;

defined('_JEXEC') or die('Restricted access');
class IxParapheur extends Api
{

	private $default_values_dossier = [
		'options' => [
			'confidentiel' => false,
			'circuitModifiable' => false,
			'documentModifiable' => false,
			'annexesSignables' => false,
			'signature' => 0,
			'autoriserRefusAssistant' => false,
			'autoriserDroitRemordSig' => false,
			'ajouterAnnotationPubliqueEtapeSignature' => false,
			'nePasCalculerCircuitHierarchique' => false,
			'remplacerCircuitHierarchiqueParResponsable' => false,
			'autoriserModificationAnnexes' => false,
			'fusionnerEtapesSuccessives' => false,
			'informerPersonnesEvolutionTraitement' => false,
			'informerPersonnesDebutTraitement' => false,
			'informerPersonnesFinTraitement' => false,
		],
		'annotations' => null,
	];

	public function __construct($entities = array())
	{
		parent::__construct();

		$this->setBaseUrl();
		$this->setClient();
		$this->setAuth();
		$this->setHeaders();
	}

	public function setBaseUrl(): void
	{
		$config = JComponentHelper::getParams('com_emundus');
		$this->baseUrl = $config->get('ixparapheur_api_base_url', '');
	}

	public function setHeaders(): void
	{
		$auth = $this->getAuth();

		$this->headers = array(
			'IXBUS_API' => $auth['app_token']
		);
	}

	public function setAuth(): void
	{
		$config = JComponentHelper::getParams('com_emundus');

		$this->auth['app_token'] = $config->get('ixparapheur_api_app_token', '');
	}

	public function getNatures($name = null): array
	{
		$natures = $this->get('nature');

		if($natures['status'] !== 200)
		{
			return array();
		}

		$natures = $natures['data']->payload;
		if(!empty($name)) {
			$natures = array_filter($natures, function($nature) use ($name) {
				return $nature->nom === $name;
			});
		}

		return array_values($natures);
	}

	public function getRedacteursNature($nature, $email = null): array
	{
		$redacteurs = $this->get('nature/'.$nature.'/redacteur');

		if($redacteurs['status'] !== 200)
		{
			return array();
		}

		$redacteurs = $redacteurs['data']->payload;
		if(!empty($email)) {
			$redacteurs = array_filter($redacteurs, function($redacteur) use ($email) {
				return $redacteur->email === $email;
			});
		}

		return $redacteurs;
	}

	public function getViseursNature($nature, $email = null): array
	{
		$viseurs = $this->get('nature/'.$nature.'/viseur');

		if($viseurs['status'] !== 200)
		{
			return array();
		}

		$viseurs = $viseurs['data']->payload;
		if(!empty($email)) {
			$viseurs = array_filter($viseurs, function($viseur) use ($email) {
				return $viseur->email === $email;
			});
		}

		return $viseurs;
	}

	public function getSignatairesNature($nature, $email = null): array
	{
		$signataires = $this->get('nature/'.$nature.'/signataire');

		if($signataires['status'] !== 200)
		{
			return array();
		}

		$signataires = $signataires['data']->payload;
		if(!empty($email)) {
			$signataires = array_filter($signataires, function($signataire) use ($email) {
				return $signataire->email === $email;
			});
		}

		return $signataires;
	}

	public function getServices($name = null, $user = null): array
	{
		$route = 'service';
		if(!empty($user)) {
			$route = 'service?idUtilisateur='.$user;
		}
		$services = $this->get($route);

		if($services['status'] !== 200)
		{
			return array();
		}

		$services = $services['data']->payload;
		if(!empty($name)) {
			$services = array_filter($services, function($service) use ($name) {
				return $service->nom === $name;
			});
		}

		return $services;
	}

	public function getFonctions($name = null): array
	{
		$fonctions = $this->get('fonction');

		if($fonctions['status'] !== 200)
		{
			return array();
		}

		$fonctions = $fonctions['data']->payload;
		if(!empty($name)) {
			$fonctions = array_filter($fonctions, function($fonction) use ($name) {
				return $fonction->nom === $name;
			});
		}

		return $fonctions;
	}

	public function getUtilisateurs($email = null): array
	{
		$users = $this->get('utilisateur');

		if($users['status'] !== 200)
		{
			return array();
		}

		$users = $users['data']->payload;
		if(!empty($email)) {
			$users = array_filter($users, function($user) use ($email) {
				return $user->email === $email;
			});
		}

		return $users;
	}

	public function getModelesCircuits($nature, $service, $name = null): array
	{
		$circuits = $this->get('circuit/'.$nature.'/'.$service);

		if($circuits['status'] !== 200)
		{
			return array();
		}

		$circuits = $circuits['data']->payload;
		if(!empty($name)) {
			$circuits = array_filter($circuits, function($circuit) use ($name) {
				return $circuit->nom === $name;
			});
		}

		return $circuits;
	}

	public function createDossier($dossier, $transmettre = false): array
	{
		if(empty($dossier['nature']) || empty($dossier['circuit']))
		{
			return array();
		}
		
		$datas = array_merge($this->default_values_dossier, $dossier);

		return $this->post('dossier?transmettre='.$transmettre, json_encode($datas));
	}

	public function updateDossier($idDossier,$datas): array
	{
		return $this->patch('dossier/'.$idDossier, json_encode($datas));
	}

	//TODO: Add filters parameter to get dossier by user, service, state
	public function getDossier($idDossier): array
	{
		return $this->get('dossier/'.$idDossier);
	}

	public function actionDossier($idDossier, $action = 'transmettre'): array
	{
		return $this->post('dossier/'.$idDossier.'/'.$action,json_encode(array()));
	}

	public function addDocument($idDossier, $datas): array
	{
		return $this->post('document/'.$idDossier,json_encode($datas));
	}

	public function deleteDocument($idDossier): array
	{
		return $this->delete('document/'.$idDossier);
	}

	public function getDocumentContent($idDocument): array
	{
		return $this->get('document/contenu/'.$idDocument);
	}

	public function updateDocumentContent($idDocument,$file): array
	{
		return $this->patch('document/contenu/'.$idDocument,json_encode($file));
	}
}