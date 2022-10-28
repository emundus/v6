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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

class plgEventbookingAttachments extends CMSPlugin
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
	 * Render setting form
	 *
	 * @param   EventbookingTableEvent  $row
	 *
	 * @return array
	 */
	public function onEditEvent($row)
	{
		if (!$this->canRun($row))
		{
			return;
		}

		return ['title' => Text::_('EB_ATTACHMENTS'),
		        'form'  => $this->drawSettingForm($row),
		];
	}

	/**
	 * Store setting into database, in this case, use params field of plans table
	 *
	 * @param   EventbookingTableEvent  $row
	 * @param   Boolean                 $isNew  true if create new plan, false if edit
	 */
	public function onAfterSaveEvent($row, $data, $isNew)
	{
		if (empty($data['attachments_plugin_rendered']))
		{
			return;
		}

		$app         = Factory::getApplication();
		$config      = EventbookingHelper::getConfig();
		$attachments = $app->input->files->get('attachments', [], 'raw');

		$pathUpload = JPATH_ROOT . '/' . ($config->attachments_path ?: 'media/com_eventbooking');

		$allowedExtensions = $config->attachment_file_types;

		if (!$allowedExtensions)
		{
			$allowedExtensions = 'doc|docx|ppt|pptx|pdf|zip|rar|bmp|gif|jpg|jepg|png|swf|zipx';
		}

		$allowedExtensions = explode('|', $allowedExtensions);
		$allowedExtensions = array_map('trim', $allowedExtensions);
		$allowedExtensions = array_map('strtolower', $allowedExtensions);

		$attachmentFiles = [];

		foreach ($attachments as $file)
		{
			$attachment = $file['attachment_file'];

			if ($attachment['name'])
			{
				$fileName = $attachment['name'];
				$fileExt  = File::getExt($fileName);

				if (in_array(strtolower($fileExt), $allowedExtensions))
				{
					$fileName = File::makeSafe($fileName);

					if ($app->isClient('administrator'))
					{
						File::upload($attachment['tmp_name'], $pathUpload . '/' . $fileName, false, true);
					}
					else
					{
						File::upload($attachment['tmp_name'], $pathUpload . '/' . $fileName);
					}

					$attachmentFiles[] = $fileName;
				}
			}
		}

		if (isset($data['existing_attachments']))
		{
			$attachmentFiles = array_merge($attachmentFiles, array_filter($data['existing_attachments']));
		}

		$row->attachment = implode('|', $attachmentFiles);
		$row->store();
	}

	/**
	 * Display form allows users to change settings on subscription plan add/edit screen
	 *
	 * @param   EventbookingTableEvent  $row
	 *
	 * @return string
	 */
	private function drawSettingForm($row)
	{
		$config = EventbookingHelper::getConfig();
		$form   = JForm::getInstance('attachments', JPATH_ROOT . '/plugins/eventbooking/attachments/form/attachments.xml');

		// List existing attachments here
		$layoutData = [
			'existingAttachmentsList' => EventbookingHelper::callOverridableHelperMethod('Helper', 'attachmentList', [explode('|', $row->attachment), $config, 'existing_attachments']),
			'form'                    => $form,
		];

		return EventbookingHelperHtml::loadCommonLayout('plugins/attachments.php', $layoutData);
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
