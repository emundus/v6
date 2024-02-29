<?php
/**
 * Colour Picker Element
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.colourpicker
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
use Joomla\CMS\Factory;
use Joomla\CMS\Profiler\Profiler;
use Symfony\Component\Yaml\Yaml;

defined('_JEXEC') or die('Restricted access');

/**
 * Plugin element to render colour picker
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.colorpicker
 * @since       3.0
 */
class PlgFabrik_ElementIban extends PlgFabrik_Element
{

	public function render($data, $repeatCounter = 0)
	{
		$properties = $this->inputProperties($repeatCounter);

		if (is_array($this->getFormModel()->data))
		{
			$data = $this->getFormModel()->data;
		}

		$layout                 = $this->getLayout('form');
		$layoutData             = new stdClass;
		$layoutData->attributes = $properties;
		$layoutData->value      = $this->getValue($data, $repeatCounter);

		$encrypt_datas = $this->getParams()->get('encrypt_datas', 1);
		if($encrypt_datas == 1 && substr($layoutData->value, -1) === '=')
		{
			$layoutData->value = $this->decrypt($this->getValue($data, $repeatCounter));
		}

		return $layout->render($layoutData);
	}

	public function renderListData($data, stdClass &$thisRow, $opts = array())
	{
		$profiler = Profiler::getInstance('Application');
		JDEBUG ? $profiler->mark("renderListData: {$this->element->plugin}: start: {$this->element->name}") : null;

		$data              = FabrikWorker::JSONtoData($data, true);
		$layout            = $this->getLayout('list');
		$displayData       = new stdClass;
		$displayData->data = $data;

		return $layout->render($displayData);
	}

	public function elementJavascript($repeatCounter)
	{
		$id   = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->bicMapping = $this->getBICMapping();

		return array('FbEmundusIban', $id, $opts);
	}

	public function validate($data, $repeatCounter = 0)
	{
		if (is_array($data))
		{
			$data = implode('', $data);
		}

		$data = preg_replace('/\s+/', '', $data);
		if($data)
		{
			return $this->verify_iban($data);
		}

		return true;
	}

	/**
	 * Get validation error - run through JText
	 *
	 * @return  string
	 */
	public function getValidationErr()
	{
		return FText::_('PLG_ELEMENT_IBAN_INVALID');
	}

	/**
	 * Manipulates posted form data for insertion into database
	 *
	 * @param   mixed  $val   This elements posted form data
	 * @param   array  $data  Posted form data
	 *
	 * @return  mixed
	 */
	public function storeDatabaseFormat($val, $data)
	{
		if(is_string($val))
		{
			$val = preg_replace('/\s+/', '', $val);
		}

		$encrypt_datas = $this->getParams()->get('encrypt_datas', 1);
		if($encrypt_datas == 1)
		{
			$val = $this->encrypt($val);
		}

		return $val;
	}

	private function verify_iban($iban)
	{
		if(strlen($iban) < 5) return false;
		$iban = strtolower(str_replace(' ','',$iban));
		$Countries = array('al'=>28,'ad'=>24,'at'=>20,'az'=>28,'bh'=>22,'be'=>16,'ba'=>20,'br'=>29,'bg'=>22,'cr'=>21,'hr'=>21,'cy'=>28,'cz'=>24,'dk'=>18,'do'=>28,'ee'=>20,'fo'=>18,'fi'=>18,'fr'=>27,'ge'=>22,'de'=>22,'gi'=>23,'gr'=>27,'gl'=>18,'gt'=>28,'hu'=>28,'is'=>26,'ie'=>22,'il'=>23,'it'=>27,'jo'=>30,'kz'=>20,'kw'=>30,'lv'=>21,'lb'=>28,'li'=>21,'lt'=>20,'lu'=>20,'mk'=>19,'mt'=>31,'mr'=>27,'mu'=>30,'mc'=>27,'md'=>24,'me'=>22,'nl'=>18,'no'=>15,'pk'=>24,'ps'=>29,'pl'=>28,'pt'=>25,'qa'=>29,'ro'=>24,'sm'=>27,'sa'=>24,'rs'=>22,'sk'=>24,'si'=>19,'es'=>24,'se'=>24,'ch'=>21,'tn'=>24,'tr'=>26,'ae'=>23,'gb'=>22,'vg'=>24);
		$Chars = array('a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,'i'=>18,'j'=>19,'k'=>20,'l'=>21,'m'=>22,'n'=>23,'o'=>24,'p'=>25,'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,'x'=>33,'y'=>34,'z'=>35);

		if(array_key_exists(substr($iban,0,2), $Countries) && strlen($iban) == $Countries[substr($iban,0,2)]){

			$MovedChar = substr($iban, 4).substr($iban,0,4);
			$MovedCharArray = str_split($MovedChar);
			$NewString = "";

			foreach($MovedCharArray AS $key => $value){
				if(!is_numeric($MovedCharArray[$key])){
					if(!isset($Chars[$MovedCharArray[$key]])) return false;
					$MovedCharArray[$key] = $Chars[$MovedCharArray[$key]];
				}
				$NewString .= $MovedCharArray[$key];
			}

			if($this->bcmod($NewString, '97') == 1)
			{
				return true;
			}
		}
		return false;
	}

	private function bcmod( $x, $y )
	{
		// how many numbers to take at once? carefull not to exceed (int)
		$take = 5;
		$mod = '';

		do
		{
			$a = (int)$mod.substr( $x, 0, $take );
			$x = substr( $x, $take );
			$mod = $a % $y;
		}
		while ( strlen($x) );

		return (int)$mod;
	}

	private function decrypt($val)
	{
		//Define cipher
		$cipher = "aes-128-cbc";

		//Generate a 256-bit encryption key
		$encryption_key = Factory::getConfig()->get('secret');

		//Data to decrypt
		return openssl_decrypt($val, $cipher, $encryption_key, 0);
	}

	private function encrypt($val)
	{
		//Define cipher
		$cipher = "aes-128-cbc";

		//Generate a 256-bit encryption key
		$encryption_key = Factory::getConfig()->get('secret');

		//Data to encrypt
		return openssl_encrypt($val, $cipher, $encryption_key, 0);
	}

	private function getBICMapping()
	{
		$yaml = Yaml::parse(file_get_contents('plugins/fabrik_element/iban/assets/bic.yaml'));
		return $yaml;
	}
}
