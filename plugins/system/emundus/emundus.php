<?php
/**
 * @package	eMundus for Joomla!
 * @version	1.39.1
 * @author	emundus.fr
 * @copyright	(C) 2024 eMundus All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Gantry\Framework\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Plugin\CMSPlugin;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! eMundus system
 *
 * @package     Joomla.Plugin
 * @subpackage  System
 * @since       3.0
 */
class plgSystemEmundus extends JPlugin
{

	private $label_colors = [
		'lightpurple' => '--em-purple-2',
		'purple'=> '--em-purple-2',
		'darkpurple'=> '--em-purple-2',
		'lightblue'=> '--em-light-blue-2',
		'blue'=> '--em-blue-2',
		'darkblue'=> '--em-blue-3',
		'lightgreen'=> '--em-green-2',
		'green'=> '--em-green-2',
		'darkgreen'=> '--em-green-2',
		'lightyellow'=> '--em-yellow-2',
		'yellow'=> '--em-yellow-2',
		'darkyellow'=> '--em-yellow-2',
		'lightorange'=> '--em-orange-2',
		'orange'=> '--em-orange-2',
		'darkorange'=> '--em-orange-2',
		'lightred'=> '--em-red-1',
		'red'=> '--em-red-2',
		'darkred'=> '--em-red-2',
		'pink'=> '--em-pink-2',
		'default'=> '--neutral-600',
	];


	/**
	 * Constructor
	 *
	 * @param   object &$subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 *
	 * @since    1.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		
		// Could be component was uninstalled but not the plugin
		if (!File::exists(JPATH_SITE . '/components/com_emundus/emundus.php'))
		{
			return;
		}
	}

	public function onBeforeCompileHead()
	{
		if(version_compare(JVERSION,'3.7','<'))
		{
			return;
		}

		$app = Factory::getApplication();
		if($app->isClient('administrator')) {
			if(empty($_REQUEST['option']) || $_REQUEST['option'] != 'com_emundus')
				return;
		}

		$doc = Factory::getDocument();
		$head = $doc->getHeadData();

		if(empty($head['scripts']))
			return;

		$js_files = array('jquery.js', 'jquery.min.js', 'jquery-noconflict.js', 'jquery.ui.core.js', 'jquery.ui.core.min.js');
		$newScripts = array();
		foreach($head['scripts'] as $file => $data) {
			foreach($js_files as $js_file) {
				if(strpos($file,'media/jui/js/'.$js_file)=== false)
					continue;
				$newScripts[$file] = $data;
			}
		}
		foreach($head['scripts'] as $file => $data){
			if(!isset($newScripts[$file]))
				$newScripts[$file] = $data;
		}
		$head['scripts'] = $newScripts;

		if (!Factory::getUser()->guest)
		{
			$profile_details = null;
			$e_session = $app->getSession()->get('emundusUser');

			if (!empty($e_session->profile))
			{
				require_once JPATH_ROOT . '/components/com_emundus/models/users.php';
				$m_users = $app->bootComponent('com_emundus')->getMVCFactory()->createModel('Users', 'EmundusModel');

				$profile_details = $m_users->getProfileDetails($e_session->profile);

				if(strpos($profile_details->class, 'label-') !== false)
				{
					$profile_details->class = str_replace('label-','--em-',$profile_details->class);
				}
				elseif(!empty($this->label_colors[$profile_details->class])) {
					$profile_details->class = $this->label_colors[$profile_details->class];
				}

				$profile_font = $profile_details->published !== 1 ? '--em-coordinator-font' : '--em-applicant-font';
				$profile_font_title = $profile_details->published !== 1 ? '--em-coordinator-font-title' : '--em-applicant-font-title';

				$style = ':root {
					--em-profile-color: var(' . $profile_details->class . ');
					--em-profile-font: var(' . $profile_font . ');
					--em-profile-font-title: var(' . $profile_font_title . ');
				}';

				$doc->addStyleDeclaration($style);
			}
		}

		$doc->setHeadData($head);
	}

	/**
	 * Insert classes into the body tag depending on user profile
	 *
	 * @return  void
	 */
	public function onAfterRender()
	{
		$app = Factory::getApplication();

		if ($app->isClient('site'))
		{
			$body = $app->getBody();

			$e_session = $app->getSession()->get('emundusUser');

			// Define class via emundus profile
			if (!empty($e_session))
			{
				$class = $e_session->applicant == 1 ? 'em-applicant' : 'em-coordinator';
			}
			else
			{
				$class = 'em-guest';
			}

			preg_match_all(\chr(1) . '(<div.*\s+id="g-page-surround".*>)' . \chr(1) . 'i', $body, $matches);
			foreach ($matches[0] as $match)
			{
				if (!strpos($match, 'class='))
				{
					$replace = '<div id="g-page-surround" class="' . $class . '">';
					$body    = str_replace($match, $replace, $body);
				}
			}

			preg_match_all(\chr(1) . '(<main.*\s+id="g-main-mainbody".*>)' . \chr(1) . 'i', $body, $matches);
			foreach ($matches[0] as $match)
			{
				if (!strpos($match, 'class='))
				{
					$replace = '<main id="g-main-mainbody" role="main">';
					$body    = str_replace($match, $replace, $body);
				}
			}

			preg_match_all(\chr(1) . '(<footer.*\s+id="g-footer".*>)' . \chr(1) . 'i', $body, $matches);
			foreach ($matches[0] as $match)
			{
				if (!strpos($match, 'class='))
				{
					$replace = '<footer id="g-footer" role="contentinfo">';
					$body    = str_replace($match, $replace, $body);
				}
			}

			$app->setBody($body);
		}
	}
}
