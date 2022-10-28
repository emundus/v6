<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

class PlgContentEBStipeasyimage extends CMSPlugin
{
	public function __construct(&$subject, $config = [])
	{
		if (!file_exists(JPATH_LIBRARIES . '/easylib/vendor/autoload.php'))
		{
			return;
		}

		parent::__construct($subject, $config);

		JLoader::discover('Stip', JPATH_LIBRARIES . '/easylib/classes/');
	}

	/**
	 * @param   JForm    $form
	 * @param   integer  $data
	 *
	 * @return bool
	 */
	public function onContentPrepareForm($form, $data)
	{
		$name = $form->getName();

		if (!in_array($name, ['com_eventbooking.image']))
		{
			return true;
		}

		$sizes   = [];
		$sizes[] = [
			'id'      => 1,
			'width'   => (int) $this->params->get('event_image_width', 800),
			'height'  => (int) $this->params->get('event_image_height', 600),
			'folder'  => '/images/com_eventbooking/',
			'cancrop' => true,
		];

		$ajax_url      = "index.php?option=com_ajax&amp;plugin=ebstipeasyimage&amp;group=content&amp;format=json";
		$change_fields = [
			'image' => [
				'attributes' => [
					'sizes'       => json_encode($sizes),
					'show_select' => true,
					'label'       => Text::_('EB_IMAGE'),
				],
				'group'      => 'images',
			],
		];

		$easyForm = new StipEasyimageFormHelper();
		$easyForm->setFormFields($form, $data, true, $ajax_url, $change_fields, []);

		echo $form->renderField('image', 'images');
		echo $form->renderField('stip_select_image', 'images');

		return true;
	}

	public function onAjaxEbStipeasyimage()
	{
		return StipEasyimageFormHelper::processAjax((int) $this->params->get('quality'));
	}
}
