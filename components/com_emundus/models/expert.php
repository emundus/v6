<?php
/**
 * Expert  Model for eMundus Component
 *
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

class EmundusModelExpert extends JModelList {

	protected $_db;
	protected $app;

	public function __construct(){
		parent::__construct();

		$this->app = Factory::getApplication();
		$this->_db = Factory::getDBO();
	}

	public function getSetupByFnum($fnum)
	{
		$setup = null;
		$query = $this->_db->getQuery(true);

		try
		{
			if(!empty($fnum))
			{
				$query->clear()
					->select('esc.id, esc.training')
					->from($this->_db->quoteName('#__emundus_campaign_candidature', 'ecc'))
					->leftJoin($this->_db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $this->_db->quoteName('esc.id') . ' = ' . $this->_db->quoteName('ecc.campaign_id'))
					->where($this->_db->quoteName('ecc.fnum') . ' = ' . $this->_db->quote($fnum));
				$this->_db->setQuery($query);
				$fnumInfos = $this->_db->loadObject();
			}

			$query->clear()
				->select('esfr.end_date,esfr.digital_sign,esfr.letter_id,esfr.attachment_id,esfr.must_validate,esfr.notify_email,esfrr.application_elements')
				->from($this->_db->quoteName('#__emundus_setup_files_request', 'esfr'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_files_request_1115_repeat','esfrr').' ON '.$this->_db->quoteName('esfrr.parent_id').' = '.$this->_db->quoteName('esfr.id'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_files_request_1115_repeat_repeat_campaigns','esfrrc').' ON '.$this->_db->quoteName('esfrrc.parent_id').' = '.$this->_db->quoteName('esfrr.id'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_files_request_1115_repeat_repeat_programme','esfrrp').' ON '.$this->_db->quoteName('esfrrp.parent_id').' = '.$this->_db->quoteName('esfrr.id'))
				->where($this->_db->quoteName('esfrrc.campaigns') . ' = ' . $this->_db->quote($fnumInfos->id))
				->orWhere($this->_db->quoteName('esfrrp.programme') . ' = ' . $this->_db->quote($fnumInfos->training));
			$this->_db->setQuery($query);
			$setup = $this->_db->loadObject();
		}
		catch (Exception $e)
		{
			Log::add('Error in getSetupByFnum: ' . $e->getMessage(), Log::ERROR, 'com_emundus');
		}

		return $setup;
	}
}
