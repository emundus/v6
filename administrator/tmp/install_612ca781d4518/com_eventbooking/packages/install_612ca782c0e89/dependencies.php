<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Registry\Registry;

class plgEventBookingDependencies extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $db;

	/**
	 * Constructor.
	 *
	 * @param $subject
	 * @param $config
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		Factory::getLanguage()->load('plg_eventbooking_dependencies', JPATH_ADMINISTRATOR);
	}

	/**
	 * Render setting form
	 *
	 * @param   JTable  $row
	 *
	 * @return mixed
	 */
	public function onEditEvent($row)
	{
		if (!$this->canRun($row))
		{
			return;
		}

		ob_start();

		$this->drawSettingForm($row);

		return ['title' => Text::_('PLG_EB_DEPENDENCY_EVENTS'),
		        'form'  => ob_get_clean(),
		];
	}

	/**
	 * Store setting into database, in this case, use params field of events table
	 *
	 * @param   EventbookingTableEvent  $row
	 * @param   bool                    $isNew  true if create new plan, false if edit
	 */
	public function onAfterSaveEvent($row, $data, $isNew)
	{
		if (!$this->canRun($row))
		{
			return;
		}

		$params = new Registry($row->params);

		$params->set('dependency_event_ids', $data['dependency_event_ids']);
		$params->set('dependency_type', $data['dependency_type']);

		$row->params = $params->toString();

		$row->store();
	}

	/**
	 * Display form allows users to change settings on subscription plan add/edit screen
	 *
	 * @param   object  $row
	 */
	private function drawSettingForm($row)
	{
		if ($row->id)
		{
			$params             = new Registry($row->params);
			$dependencyEventIds = $params->get('dependency_event_ids');
			$dependencyType     = $params->get('dependency_type', 'all');
		}
		else
		{
			$dependencyEventIds = '';
			$dependencyType     = 'all';
		}

		?>
        <div class="control-group">
            <div class="control-label">
                <?php echo EventbookingHelperHtml::getFieldLabel('dependency_event_ids', Text::_('PLG_EB_DEPENDENCY_EVENT_IDS'), Text::_('PLG_EB_DEPENDENCY_EVENT_IDS_EXPLAIN')) ; ?>
            </div>
            <div class="controls">
                <input type="text" name="dependency_event_ids" value="<?php echo $dependencyEventIds ?>" class="input-xxlarge form-control" />
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
				<?php echo EventbookingHelperHtml::getFieldLabel('dependency_type', Text::_('PLG_EB_DEPENDENCY_TYPE')) ; ?>
            </div>
            <div class="controls">
                <?php
                $options   = [];
                $options[] = HTMLHelper::_('select.option', 'all', Text::_('PLG_EB_DEPENDENCY_TYPE_ALL'));
                $options[] = HTMLHelper::_('select.option', 'one', Text::_('PLG_EB_DEPENDENCY_TYPE_ONE'));

                echo HTMLHelper::_('select.genericlist', $options, 'dependency_type', 'class="form-select"', 'value', 'text', $dependencyType);
                ?>
            </div>
        </div>
		<?php
	}

	/**
	 * Method to check to see whether the plugin should run
	 *
	 * @param   EventbookingTableEvent  $row
	 *
	 * @return bool
	 */
	private function canRun($row)
	{
		if ($this->app->isClient('site') && !$this->params->get('show_on_frontend'))
		{
			return false;
		}

		return true;
	}
}
