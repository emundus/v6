<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

defined( '_JEXEC' ) or die;

use JchOptimize\Core\Helper;
use JchOptimize\Platform\Plugin;
use JchOptimize\Platform\Utility;
use Joomla\CMS\HTML\HTMLHelper;

include_once JPATH_LIBRARIES . '/fof40/include.php';
include_once JPATH_ADMINISTRATOR . '/components/com_jchoptimize/autoload.php';
include_once JPATH_ADMINISTRATOR . '/components/com_jchoptimize/version.php';

class JFormFieldAjax extends JFormField
{
	protected $type = 'ajax';


	public function setup( SimpleXMLElement $element, $value, $group = null )
	{
		$params = Plugin::getPluginParams();

		if ( ! defined( 'JCH_DEBUG' ) )
		{
			define( 'JCH_DEBUG', ( $params->get( 'debug', 0 ) && JDEBUG ) );
		}

		$script_options = array( 'framework' => false, 'relative' => true );

		HTMLHelper::_( 'jquery.framework', true, null, false );

		$oDocument = JFactory::getDocument();
		$sScript   = '';

		$options = [ 'version' => JCH_VERSION ];
		$oDocument->addStyleSheet( JUri::root( true ) . '/media/com_jchoptimize/css/admin.css', $options );
		$oDocument->addScript( JUri::root( true ) . '/media/com_jchoptimize/js/admin-joomla.js', $options );

		if ( version_compare( JVERSION, '3.99.99', '>' ) )
		{
			$oDocument->addStyleSheet( JUri::root( true ) . '/media/vendor/chosen/css/chosen.css' );
			$oDocument->addScript( JUri::root( true ) . '/media/vendor/chosen/js/chosen.jquery.js' );
			$oDocument->addScriptDeclaration( 'jQuery(document).ready(function() { 
	jQuery(\'.jch-exclude\').chosen({
		width: "80%"	
	});
});' );
		}

		$ajax_url = JRoute::_( 'index.php?option=com_jchoptimize&view=Ajax', false, JRoute::TLS_IGNORE, true );

		$sScript .= <<<JS
jQuery(document).ready(function() {
    jQuery('.collapsible').collapsible();
  });
			
var jch_observers = [];        
var jch_ajax_url = '$ajax_url';

JS;

		$oDocument->addScriptDeclaration( $sScript );
		JHtml::script( 'com_jchoptimize/jquery.collapsible.js', $script_options );

		return false;
	}

	protected function getInput()
	{
		return false;
	}
}
