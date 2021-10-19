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

class EventbookingHelperCertificate
{
	/**
	 * Generate certificate for the given registration records
	 *
	 * @param   array      $rows
	 * @param   RADConfig  $config
	 *
	 * @return array
	 */
	public static function generateCertificates($rows, $config)
	{
		EventbookingHelper::loadLanguage();

		// Options for PDF object
		$options          = [];
		$options['title'] = 'Certificate';
		$options['type']  = 'certificate';

		if ($config->get('certificate_page_orientation'))
		{
			$options['PDF_PAGE_ORIENTATION'] = $config->get('certificate_page_orientation');
		}

		if ($config->get('certificate_page_format'))
		{
			$options['PDF_PAGE_FORMAT'] = $config->get('certificate_page_format');
		}

		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);
		$events = [];
		$pages  = [];

		foreach ($rows as $row)
		{
			if (!isset($events[$row->event_id]))
			{
				$fieldSuffix            = EventbookingHelper::getFieldSuffix($row->language);
				$events[$row->event_id] = EventbookingHelperDatabase::getEvent($row->event_id, null, $fieldSuffix);
			}

			$rowEvent = $events[$row->event_id];

			$certificateOptions = static::getCertificatePageOptions($rowEvent, $config);

			if (EventbookingHelper::isValidMessage($rowEvent->certificate_layout))
			{
				$certificateLayout = $rowEvent->certificate_layout;
			}
			else
			{
				$certificateLayout = $config->certificate_layout;
			}

			if ($rowEvent->collect_member_information === '')
			{
				$collectMemberInformation = $config->collect_member_information;
			}
			else
			{
				$collectMemberInformation = $rowEvent->collect_member_information;
			}

			if ($row->is_group_billing && $collectMemberInformation)
			{
				$query->clear()
					->select('*')
					->from('#__eb_registrants')
					->where('group_id = ' . $row->id);
				$db->setQuery($query);
				$rowMembers = $db->loadObjectList();

				foreach ($rowMembers as $rowMember)
				{
					$page                           = new stdClass;
					$page->options                  = $certificateOptions;
					$replaces                       = EventbookingHelperRegistration::getRegistrationReplaces($rowMember, $rowEvent);
					$replaces['certificate_number'] = EventbookingHelper::callOverridableHelperMethod('Helper', 'formatCertificateNumber', [$rowMember->id, $config]);
					$replaces['registration_date']  = HTMLHelper::_('date', $row->register_date, $config->date_format);

					$output = $certificateLayout;

					foreach ($replaces as $key => $value)
					{
						$key    = strtoupper($key);
						$output = str_ireplace("[$key]", $value, $output);
					}

					$page->content = $output;
					$pages[]       = $page;
				}
			}
			else
			{
				$page          = new stdClass;
				$page->options = $certificateOptions;

				$replaces = EventbookingHelperRegistration::getRegistrationReplaces($row, $rowEvent, 0, $config->multiple_booking);

				$replaces['certificate_number'] = EventbookingHelper::callOverridableHelperMethod('Helper', 'formatCertificateNumber', [$row->id, $config]);
				$replaces['registration_date']  = HTMLHelper::_('date', $row->register_date, $config->date_format);

				foreach ($replaces as $key => $value)
				{
					$key               = strtoupper($key);
					$certificateLayout = str_ireplace("[$key]", $value, $certificateLayout);
				}

				$page->content = $certificateLayout;
				$pages[]       = $page;
			}
		}

		if (count($rows) > 1)
		{
			$fileName = 'certificates_' . date('Y-m-d') . '.pdf';
		}
		else
		{
			$row      = $rows[0];
			$fileName = EventbookingHelper::callOverridableHelperMethod('Helper', 'formatCertificateNumber', [$row->id, $config]) . '.pdf';
		}

		$filePath = JPATH_ROOT . '/media/com_eventbooking/certificates/' . $fileName;

		EventbookingHelperPdf::generatePDFFile($pages, $filePath, $options);

		return [$fileName, $filePath];
	}

	/**
	 * Get background image option for certificate
	 *
	 * @param $rowEvent
	 * @param $config
	 *
	 * @return array
	 */
	protected static function getCertificatePageOptions($rowEvent, $config)
	{
		$options = [];

		if ($rowEvent->certificate_bg_image)
		{
			$backgroundImage = $rowEvent->certificate_bg_image;
		}
		else
		{
			$backgroundImage = $config->get('default_certificate_bg_image');
		}

		if ($backgroundImage && file_exists(JPATH_ROOT . '/' . $backgroundImage))
		{
			$backgroundImagePath = JPATH_ROOT . '/' . $backgroundImage;

			if ($rowEvent->certificate_bg_left > 0)
			{
				$certificateBgLeft = $rowEvent->certificate_bg_left;
			}
			elseif ($config->default_certificate_bg_left > 0)
			{
				$certificateBgLeft = $config->default_certificate_bg_left;
			}
			else
			{
				$certificateBgLeft = 0;
			}

			if ($rowEvent->certificate_bg_top > 0)
			{
				$certificateBgTop = $rowEvent->certificate_bg_top;
			}
			elseif ($config->default_certificate_bg_top > 0)
			{
				$certificateBgTop = $config->certificate_ticket_bg_top;
			}
			else
			{
				$certificateBgTop = 0;
			}

			if ($rowEvent->certificate_bg_width > 0)
			{
				$certificateBgWidth = $rowEvent->certificate_bg_width;
			}
			elseif ($config->default_certificate_bg_width > 0)
			{
				$certificateBgWidth = $config->default_certificate_bg_width;
			}
			else
			{
				$certificateBgWidth = 0;
			}

			if ($rowEvent->certificate_bg_height > 0)
			{
				$certificateBgHeight = $rowEvent->ticket_bg_height;
			}
			elseif ($config->default_certificate_bg_height > 0)
			{
				$certificateBgHeight = $config->default_certificate_bg_height;
			}
			else
			{
				$certificateBgHeight = 0;
			}

			$options['bg_image']  = $backgroundImagePath;
			$options['bg_left']   = $certificateBgLeft;
			$options['bg_top']    = $certificateBgTop;
			$options['bg_width']  = $certificateBgWidth;
			$options['bg_height'] = $certificateBgHeight;
		}

		return $options;
	}
}