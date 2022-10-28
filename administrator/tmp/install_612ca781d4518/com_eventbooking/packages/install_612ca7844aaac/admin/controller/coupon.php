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
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

class EventbookingControllerCoupon extends EventbookingController
{
	/**
	 * Method to import coupon codes from a csv file
	 */
	public function import()
	{
		$inputFile = $this->input->files->get('input_file');
		$fileName  = $inputFile ['name'];
		$fileExt   = strtolower(File::getExt($fileName));

		if (!in_array($fileExt, ['csv', 'xls', 'xlsx']))
		{
			$this->setRedirect('index.php?option=com_eventbooking&view=coupon&layout=import', Text::_('Invalid File Type. Only CSV, XLS and XLS file types are supported'));

			return;
		}

		/* @var  EventbookingModelCoupon $model */
		$model = $this->getModel('Coupon');
		try
		{
			$numberImportedCoupons = $model->import($inputFile['tmp_name'], $inputFile['name']);
			$this->setRedirect('index.php?option=com_eventbooking&view=coupons', Text::sprintf('EB_NUMBER_COUPONS_IMPORTED', $numberImportedCoupons));
		}
		catch (Exception $e)
		{
			$this->setRedirect('index.php?option=com_eventbooking&view=coupon&layout=import');
			$this->setMessage($e->getMessage(), 'error');
		}
	}

	/**
	 * Export Coupons into a CSV file
	 */
	public function export()
	{
		set_time_limit(0);
		$model = $this->getModel('coupons');

		/* @var EventbookingModelCoupons $model */

		$model->setState('limitstart', 0)
			->setState('limit', 0)
			->setState('filter_order', 'tbl.id')
			->setState('filter_order_Dir', 'ASC');
		$rows = $model->getData();

		if (count($rows) == 0)
		{
			$this->setMessage(Text::_('There are no coupons to export'));
			$this->setRedirect('index.php?option=com_eventbooking&view=coupons');

			return;
		}

		$fields = [
			'event',
			'code',
			'discount',
			'coupon_type',
			'times',
			'used',
			'valid_from',
			'valid_to',
			'published',
		];

		$db       = Factory::getDbo();
		$query    = $db->getQuery(true);
		$nullDate = $db->getNullDate();

		// Prepare data
		foreach ($rows as $row)
		{
			if ($row->event_id == -1)
			{
				$row->event = '';
			}
			else
			{
				$query->clear()
					->select('a.id')
					->from('#__eb_events AS a')
					->leftJoin('#__eb_coupon_events AS b ON a.id = b.event_id')
					->where('b.coupon_id=' . (int) $row->id);
				$db->setQuery($query);
				$row->event = implode(',', $db->loadColumn());
			}

			$row->discount = round($row->discount, 2);

			if ($row->valid_from != $nullDate && $row->valid_from)
			{
				$row->valid_from = HTMLHelper::_('date', $row->valid_from, 'Y-m-d', null);
			}
			else
			{
				$row->valid_from = '';
			}

			if ($row->valid_to != $nullDate && $row->valid_to)
			{
				$row->valid_to = HTMLHelper::_('date', $row->valid_to, 'Y-m-d', null);
			}
			else
			{
				$row->valid_to = '';
			}
		}

		PluginHelper::importPlugin('eventbooking');
		// Give plugin a chance to process export data
		$results = $this->app->triggerEvent('onBeforeExportDataToXLSX', [$rows, $fields, [], 'coupons_list.xlsx']);

		if (count($results) && $filename = $results[0])
		{
			// There is a plugin handles export, it return the filename, so we just process download the file
			$this->processDownloadFile($filename);

			return;
		}

		EventbookingHelperData::excelExport($fields, $rows, 'coupons_list');
	}

	/**
	 * Batch coupon generation
	 */
	public function batch()
	{
		$model = $this->getModel('Coupon');
		$model->batch($this->input);
		$this->setRedirect('index.php?option=com_eventbooking&view=coupons', Text::_('EB_COUPONS_SUCCESSFULLY_GENERATED'));
	}
}
