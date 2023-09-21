<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */

class EmundusViewAmetys extends JViewLegacy
{
	protected $itemId;
	protected $task;
	protected $token;

	private $app;
	private $user;
	private $jdocument;

	protected $ametys_sync_default_eval;
	protected $ametys_sync_default_decision;
	protected $ametys_sync_default_synthesis;

	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->app = Factory::getApplication();
		if (version_compare(JVERSION, '4.0', '>'))
		{
			$this->user = $this->app->getIdentity();
			$this->jdocument = $this->app->getDocument();
			$wa	   = $this->jdocument->getWebAssetManager();
			$wa->registerAndUseScript('media/com_emundus/lib/Semantic-UI-CSS-master/components/daterangepicker/moment.min.js','moment_js');
			$wa->registerAndUseScript('media/com_emundus/lib/Semantic-UI-CSS-master/components/daterangepicker/daterangepicker.min.js','datepicker_js');
			$wa->registerAndUseStyle('media/com_emundus/lib/Semantic-UI-CSS-master/components/daterangepicker/daterangepicker.min.css','daterangepicker_css');
		} else {
			$this->user = Factory::getUser();
			$this->jdocument = Factory::getDocument();
			$this->jdocument->addScript("media/com_emundus/lib/Semantic-UI-CSS-master/components/daterangepicker/moment.min.js" );
			$this->jdocument->addScript("media/com_emundus/lib/Semantic-UI-CSS-master/components/daterangepicker/daterangepicker.min.js" );
			$this->jdocument->addStyleSheet("media/com_emundus/lib/Semantic-UI-CSS-master/components/daterangepicker/daterangepicker.min.css" );
		}
	}

    public function display($tpl = null)
    {
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->user->id))
		{
			die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
		}

		$params = ComponentHelper::getParams('com_emundus');
		$this->ametys_sync_default_eval = $params->get('ametys_sync_default_eval', null);
		$this->ametys_sync_default_decision = $params->get('ametys_sync_default_decision', null);
		$this->ametys_sync_default_synthesis = $params->get('ametys_sync_default_synthesis', null);

		// overide css
		$menu = $this->app->getMenu();
        $current_menu = $menu->getActive();
        $menu_params = $menu->getParams($current_menu->id);

		$page_heading = $menu_params->get('page_heading', '');
		if (!empty($page_heading)) {
			if (version_compare(JVERSION, '4.0', '>'))
			{
				$wa	   = $this->jdocument->getWebAssetManager();
				$wa->registerAndUseStyle('media/com_emundus/lib/Semantic-UI-CSS-master/components/site.'.$page_heading.'.css','site_css');
			} else {
				$this->jdocument->addStyleSheet("media/com_emundus/lib/Semantic-UI-CSS-master/components/site.".$page_heading.".css" );
			}
		}

	    $this->itemId = $current_menu->id;
	    $this->task   = $this->app->input->getInt('task', null);
	    $this->token  = $this->app->input->getInt('token', null);

		parent::display($tpl);
	}

}
?>

