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
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;

class plgEventBookingGallery extends CMSPlugin
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

		return ['title' => Text::_('EB_GALLERY'),
		        'form'  => $this->drawSettingForm($row),
		];
	}

	/**
	 * Store selected images for event in galleries database
	 *
	 * @param   EventbookingTableEvent  $row
	 * @param   bool                    $isNew  true if create new event, false if edit
	 */
	public function onAfterSaveEvent($row, $data, $isNew)
	{
		if (!$this->canRun($row))
		{
			return;
		}

		$images      = isset($data['gallery']) ? $data['gallery'] : [];
		$ids         = [];
		$ordering    = 1;
		$thumbWidth  = $this->params->get('thumb_width', 150);
		$thumbHeight = $this->params->get('thumb_height', 150);


		foreach ($images as $image)
		{
			/* @var EventbookingTableGallery $rowGallery */
			$rowGallery = Table::getInstance('Gallery', 'EventbookingTable');
			$rowGallery->bind($image);

			// Prevent gallery data being moved to new event on saveAsCopy
			if ($isNew)
			{
				$rowGallery->id = 0;
			}

			$rowGallery->event_id = $row->id;
			$rowGallery->ordering = $ordering++;
			$rowGallery->store();

			// Resize the image
			if ($rowGallery->image && file_exists(JPATH_ROOT . '/' . $rowGallery->image))
			{
				$fileName  = basename($rowGallery->image);
				$imagePath = JPATH_ROOT . '/' . $rowGallery->image;
				$thumbDir  = JPATH_ROOT . '/' . substr($rowGallery->image, 0, strlen($rowGallery->image) - strlen($fileName)) . '/thumbs';

				if (!Folder::exists($thumbDir))
				{
					Folder::create($thumbDir);
				}

				$thumbImagePath = $thumbDir . '/' . $fileName;
				$fileExt        = File::getExt($fileName);
				$image          = new JImage($imagePath);

				if ($fileExt == 'PNG')
				{
					$imageType = IMAGETYPE_PNG;
				}
				elseif ($fileExt == 'GIF')
				{
					$imageType = IMAGETYPE_GIF;
				}
				elseif (in_array($fileExt, ['JPG', 'JPEG']))
				{
					$imageType = IMAGETYPE_JPEG;
				}
				else
				{
					$imageType = '';
				}

				$image->cropResize($thumbWidth, $thumbHeight, false)
					->toFile($thumbImagePath, $imageType);
			}

			$ids[] = $rowGallery->id;
		}

		if (!$isNew)
		{
			$db    = $this->db;
			$query = $db->getQuery(true);
			$query->delete('#__eb_galleries')
				->where('event_id = ' . $row->id);

			if (count($ids))
			{
				$query->where('id NOT IN (' . implode(',', $ids) . ')');
			}

			$db->setQuery($query)
				->execute();
		}
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
		$form                = JForm::getInstance('gallery', JPATH_ROOT . '/plugins/eventbooking/gallery/form/gallery.xml');
		$formData['gallery'] = [];

		// Load existing speakers for this event
		if ($row->id)
		{
			$db    = $this->db;
			$query = $db->getQuery(true)
				->select('*')
				->from('#__eb_galleries')
				->where('event_id = ' . $row->id)
				->order('ordering');
			$db->setQuery($query);

			foreach ($db->loadObjectList() as $image)
			{
				$formData['gallery'][] = [
					'id'    => $image->id,
					'title' => $image->title,
					'image' => $image->image,
				];
			}
		}

		// Trigger content plugin
		PluginHelper::importPlugin('content');
		Factory::getApplication()->triggerEvent('onContentPrepareForm', [$form, $formData]);

		$form->bind($formData);

		return EventbookingHelperHtml::loadCommonLayout('plugins/gallery_form.php', ['form' => $form]);
	}

	/**
	 * Display event gallery
	 *
	 * @param   EventbookingTableEvent  $row
	 *
	 * @return array|void
	 */
	public function onEventDisplay($row)
	{
		$eventId = $row->parent_id ?: $row->id;
		$db      = $this->db;
		$query   = $db->getQuery(true)
			->select('*')
			->from('#__eb_galleries')
			->where('event_id = ' . $eventId)
			->order('ordering');

		$db->setQuery($query);
		$images = $db->loadObjectList();


		if (empty($images))
		{
			return;
		}

		ob_start();
		$this->drawGallery($images);
		$form = ob_get_clean();

		return ['title'    => Text::_('PLG_EB_GALLERY'),
		        'form'     => $form,
		        'position' => $this->params->get('output_position', 'before_register_buttons'),
		        'name'     => $this->_name,
		];
	}

	/**
	 * Display event gallery
	 *
	 * @param   array  $images
	 *
	 * @throws Exception
	 */
	private function drawGallery($images)
	{
		$document = Factory::getDocument();
		$rootUrl  = Uri::root(true);

		$document->addScript($rootUrl . '/media/com_eventbooking/assets/js/baguetteBox/baguetteBox.min.js');
		$document->addStyleSheet($rootUrl . '/media/com_eventbooking/assets/js/baguetteBox/baguetteBox.min.css');

		echo EventbookingHelperHtml::loadCommonLayout('plugins/gallery.php', ['images' => $images]);
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

		if ($row->parent_id > 0)
		{
			return false;
		}

		return true;
	}
}
