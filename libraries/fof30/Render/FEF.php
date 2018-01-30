<?php
/**
 * @package     FOF
 * @copyright Copyright (c)2010-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Render;

use FOF30\Container\Container;
use FOF30\Form\Form;
use JHtml;
use JText;

defined('_JEXEC') or die;

/**
 * Renderer class for use with Akeeba FEF
 *
 * Renderer options
 * linkbar_style        Style for linkbars: joomla3|classic. Default: joomla3
 * custom_css           Comma-separated list of custom CSS files to load _after_ the main FEF CSS file, e.g.
 *                      media://com_foo/css/bar.min.css,media://com_foo/css/baz.min.css
 *
 * @package FOF30\Render
 */
class FEF extends Joomla3
{
	public function __construct(Container $container)
	{
		parent::__construct($container);

		$helperFile = JPATH_SITE . '/media/fef/fef.php';

		if (!class_exists('AkeebaFEFHelper') && is_file($helperFile))
		{
			include_once $helperFile;
		}

		$this->priority	 = 50;
		$this->enabled	 = class_exists('AkeebaFEFHelper');
	}

	/**
	 * Echoes any HTML to show before the view template. We override it to load the CSS files required for FEF.
	 *
	 * @param   string    $view    The current view
	 * @param   string    $task    The current task
	 *
	 * @return  void
	 */
	public function preRender($view, $task)
	{
		\AkeebaFEFHelper::load();

		$this->loadCustomCss();

		parent::preRender($view, $task);
	}


	/**
	 * Opens the FEF styling wrapper element. Our component's output will be inside this wrapper.
	 *
	 * @param   array  $classes  An array of additional CSS classes to add to the outer page wrapper element.
	 *
	 * @return  void
	 */
	protected function openPageWrapper($classes)
	{
		$customClasses = implode($classes, ' ');
		echo <<< HTML
<div id="akeeba-renderer-fef" class="akeeba-renderer-fef $customClasses">

HTML;
	}

	/**
	 * Close the FEF styling wrapper element.
	 *
	 * @return  void
	 */
	protected function closePageWrapper()
	{
		echo <<< HTML
</div>

HTML;

	}

	/**
	 * Loads the custom CSS files defined in the custom_css renderer option.
	 */
	private function loadCustomCss()
	{
		$custom_css_raw = $this->getOption('custom_css', '');
		$custom_css_raw = trim($custom_css_raw);

		if (empty($custom_css_raw))
		{
			return;
		}

		$files        = explode(',', $custom_css_raw);
		$mediaVersion = $this->container->mediaVersion;

		foreach ($files as $file)
		{
			$file = trim($file);

			if (empty($file))
			{
				continue;
			}

			$this->container->template->addCSS($file, $mediaVersion);
		}
	}

}
