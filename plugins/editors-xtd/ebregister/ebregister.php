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

class plgButtonEbregister extends CMSPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 *
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Display the button
	 *
	 * @return array A four element array of (article_id, article_title, category_id, object)
	 */
	public function onDisplay($name)
	{

		$app = Factory::getApplication();
		if ($app->isClient('site'))
		{
			return;
		}
		$js = "
			function jSelectEbregister(id) {
				var tag = '{ebregister '+id+'}';
				jInsertEditorText(tag, '" . $name . "');
				SqueezeBox.close();
			}";
		Factory::getDocument()->addScriptDeclaration($js);
		HTMLHelper::_('behavior.modal');
		$link = 'index.php?option=com_eventbooking&amp;view=events&amp;layout=modal&amp;function=jSelectEbregister&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';

		$button = new JObject();
		$button->set('modal', true);
		$button->set('link', $link);
		$button->set('text', Text::_('EB Register'));
		$button->set('name', 'ebregister');
		$button->set('options', "{handler: 'iframe', size: {x: 770, y: 400}}");
		$button->set('class', 'btn');

		return $button;
	}
}
