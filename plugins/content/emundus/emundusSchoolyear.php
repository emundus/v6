<?php 

/*ini_set('display_errors', 1);
ini_set('log_errors', 1);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);*/

/**
 * @package		Joomla
 * @subpackage	eMundus
 * @copyright	Copyright (C) 2016 eMundus SAS (http://www.emundus.fr). All rights reserved.
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die();

jimport( 'joomla.plugin.plugin' );

/**
 * eater content plugin - Ensemble de comportement liés au process de sélection des postes ATER
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 		1.5
 */
class plgContentemundusSchoolyear extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param object $params  The object that holds the plugin parameters
	 * @since 1.5
	 */
	 
	//JPlugin::loadLanguage( 'plg_content_eater' );
	
	function plgContentemundusSchoolyear( &$subject, $params )
	{
		parent::__construct( $subject, $params );
	}

	/**
	 * eater prepare content method
	 *
	 * Method is called by the view
	 *
	 * @param 	usekey		l'espace dans lequel le plugin doit s'executer
	 */
	
	function onPrepareContent( &$article, &$params, $limitstart )
	{
		// Get plugin info
		$plugin =& JPluginHelper::getPlugin('content', 'emundusSchoolyear');
		$params = new JParameter( $plugin->params );
		
		// simple performance check to determine whether bot should process further
		$botRegex = ($params->get( 'Botregex' ) != '') ? $params->get( 'Botregex' ) : 'emundusSchoolyear';
		
		if (JString::strpos( $article->text, $botRegex) === false) {
			return true;
		}
		$regex = "#{" .$botRegex ."\s(.*?)}#s";	
		
		$article->text = preg_replace_callback($regex, array($this, 'checkProcess'), $article->text);
	}

	function checkProcess( $match )
	{
		global $mainframe;
		$user =& JFactory::getSession()->get('emundusUser');
		if ($user->profile <= 2) {
			JHTML::_('behavior.tooltip'); 
			JHTML::_('behavior.modal');
			$baseurl = JURI::base();
			$db = & JFactory::getDBO();
	
			//load language
			JPlugin::loadLanguage( 'com_emundus' );
	
			$match = $match[0];
			$match = trim($match, "{");
			$match = trim($match, "}");
			$match = explode(" ", $match);
			array_shift($match);
	
			foreach ($match as $m) {
				$m = explode("=", $m);
				switch ($m[0]) {
					case 'usekey':
						$usekey = $m[1];
						break;
				}
			}
			// Récupération de l'année en cours
			$query = 'SELECT schoolyear FROM #__emundus_setup_profiles WHERE id = 9';
			$db->setQuery( $query );
			$schoolyear=$db->loadResult();
	
			//JURI::base();
			//  SCHOOLYEAR
			//
			if ($usekey=='schoolyear') {			
				if (isset($schoolyear) && $schoolyear!='') {
					echo '<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST"/><input type="hidden" name="url" value="'.JURI::getInstance()->toString().'"/>
						<dl id="system-message">
						<dt class="message">Message</dt>
						<dd class="message message fade">
							<ul>
								<li>'.JText::_('CURRENT_SCHOOLYEAR').'<input type="text" name="schoolyear" value="'.$schoolyear.'"/><input type="submit" name="setSchoolyear" onclick="document.pressed=this.name" value="'.JText::_('UPDATE_SCHOOLYEAR').'"/></li>
							</ul>
						</dd>
						</dl>
						</form>';
				} else {
					echo '
						<dl id="system-message">
						<dt class="notice">Annonce</dt>
						<dd class="notice message fade">
							<ul>
								<li>'.JText::_('SCHOOLYEAR_NOT_SET').'<input type="text" name="schoolyear" value="'.$schoolyear.'"/><input type="submit" name="setSchoolyear" onclick="document.pressed=this.name" value="'.JText::_('SET_SCHOOLYEAR').'"/></li>
							</ul>
						</dd>
						</dl>';
				}
			} 
			echo '
			<script>
			function OnSubmitForm() {
				switch(document.pressed) {
					case "setSchoolyear": 
						document.adminForm.action ="index.php?option=com_emundus&controller=users&task=setSchoolyear";
					break;
					default: return false;
				}
				return true;
			}
			</script>';
		}
	}
}