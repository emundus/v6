<?php
/**
 * A cron task to run PHP code
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Filesystem\File;
use Fabrik\Helpers\Php;

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

/**
 * A cron task to run PHP code
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0
 */

class PlgFabrik_Cronphp extends PlgFabrik_Cron
{
	/**
	 * Check if the user can use the active element
	 *
	 * @param   string  $location  To trigger plugin on
	 * @param   string  $event     To trigger plugin on
	 *
	 * @return  bool can use or not
	 */

	public function canUse($location = null, $event = null)
	{
		return true;
	}

	/**
	 * Do the plugin action
	 *
	 * @param   array   &$data       array data to process
	 * @param   object  &$listModel  List model
	 * @return  int  number of records run, you can set this by setting the varaible $processed
	 * in either your included script in php code.
	 */

	public function process(&$data, &$listModel)
	{
		$params = $this->getParams();
		$filter = InputFilter::getInstance();
		$file = $filter->clean($params->get('cronphp_file'), 'CMD');


		$code = ['preCode' => trim($params->get('cronphp_params', ''))];
		if ($file != '-1') 
		{
			// Windows has \ in file path
			$file = str_replace("\\","/",JPATH_ROOT . '/plugins/fabrik_cron/php/scripts/' . $file);
			$code['file'] = $file;
		}
		$code['postCode'] = trim($params->get('cronphp_code', ''));

		$processed = null;

		FabrikWorker::clearEval();
		$php_result = Php::Eval([
		'code'=>$code,
			'vars'=>['data'=>$data, 'processed' => &$processed, 'listModel'=>$listModel]
		]);
		FabrikWorker::logEval($php_result, 'Caught exception on eval of cron php_code: %s');

		if (!empty($processed))
		{
			return (int) $processed;
		}

		return 0;
	}
}
