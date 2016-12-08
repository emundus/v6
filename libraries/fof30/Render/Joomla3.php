<?php
/**
 * @package     FOF
 * @copyright   2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Render;

use FOF30\Container\Container;
use FOF30\Form\Form;
use JHtml;
use JText;

defined('_JEXEC') or die;

class Joomla3 extends AkeebaStrapper
{
	public function __construct(Container $container)
	{
		parent::__construct($container);

		$this->priority	 = 55;
		$this->enabled	 = version_compare(JVERSION, '3.0', 'ge');
	}

	/**
	 * Echoes any HTML to show before the view template
	 *
	 * @param   string    $view    The current view
	 * @param   string    $task    The current task
	 *
	 * @return  void
	 */
	public function preRender($view, $task)
	{
		$input = $this->container->input;
		$platform = $this->container->platform;

		$format	 = $input->getCmd('format', 'html');

		if (empty($format))
		{
			$format	 = 'html';
		}

		if ($format != 'html')
		{
			return;
		}

		if ($platform->isCli())
		{
			return;
		}

		\JHtml::_('behavior.core');
		\JHTML::_('jquery.framework', true);

		// Wrap output in various classes
		$version = new \JVersion;
		$versionParts = explode('.', $version->RELEASE);
		$minorVersion = str_replace('.', '', $version->RELEASE);
		$majorVersion = array_shift($versionParts);

		$classes = array();

		if ($platform->isBackend())
		{
			$area = $platform->isBackend() ? 'admin' : 'site';
			$option = $input->getCmd('option', '');
			$viewForCssClass = $input->getCmd('view', '');
			$layout = $input->getCmd('layout', '');
			$taskForCssClass = $input->getCmd('task', '');

			$classes = array(
				'joomla-version-' . $majorVersion,
				'joomla-version-' . $minorVersion,
				$area,
				$option,
				'view-' . $view,
				'view-' . $viewForCssClass,
				'layout-' . $layout,
				'task-' . $task,
				'task-' . $taskForCssClass,
				// We have a floating sidebar, they said. It looks great, they said. They must've been blind, I say!
				'j-toggle-main',
				'j-toggle-transition',
				'row-fluid',
			);

			$classes = array_unique($classes);
		}

		echo '<div id="akeeba-renderjoomla" class="' . implode($classes, ' ') . "\">\n";

		// Render the submenu and toolbar
		if ($input->getBool('render_toolbar', true))
		{
			$this->renderButtons($view, $task);
			$this->renderLinkbar($view, $task);
		}
	}

	/**
	 * Echoes any HTML to show after the view template
	 *
	 * @param   string    $view    The current view
	 * @param   string    $task    The current task
	 *
	 * @return  void
	 */
	public function postRender($view, $task)
	{
		$input = $this->container->input;
		$platform = $this->container->platform;

		$format	 = $input->getCmd('format', 'html');

		if (empty($format))
		{
			$format	 = 'html';
		}

		if ($format != 'html')
		{
			return;
		}

		// Closing tag only if we're not in CLI
		if ($platform->isCli())
		{
			return;
		}

		echo "</div>\n";    // Closes akeeba-renderjoomla div
	}

	/**
	 * Renders the submenu (link bar)
	 *
	 * @param   string    $view    The active view name
	 * @param   string    $task    The current task
	 *
	 * @return  void
	 */
	protected function renderLinkbar($view, $task)
	{
		$style = $this->getOption('linkbar_style', 'joomla');

		switch ($style)
		{
			case 'joomla':
				$this->renderLinkbar_joomla($view, $task);
				break;

			case 'classic':
			default:
				$this->renderLinkbar_classic($view, $task);
				break;
		}
	}

	/**
	 * Renders a label for a fieldset.
	 *
	 * @param   object  $field  The field of the label to render
	 * @param   Form   	&$form  The form to render
	 * @param 	string  $title  The title of the label
	 *
	 * @return 	string  The rendered label
	 */
	public function renderFieldsetLabel($field, Form &$form, $title)
	{
		$html = '';

		$labelClass	 = $field->labelClass ? $field->labelClass : $field->labelclass; // Joomla! 2.5/3.x use different case for the same name
		$required	 = $field->required;

		$tooltip = $form->getFieldAttribute($field->fieldname, 'tooltip', '', $field->group);

		if (!empty($tooltip))
		{
			JHtml::_('bootstrap.tooltip');

			$tooltipText = '<strong>' . JText::_($title) . '</strong><br />' . JText::_($tooltip);

			$html .= "\t\t\t\t" . '<label class="control-label hasTooltip ' . $labelClass . '" for="' . $field->id . '" title="' . $tooltipText . '" rel="tooltip">';
		}
		else
		{
			$html .= "\t\t\t\t" . '<label class="control-label ' . $labelClass . '" for="' . $field->id . '">';
		}

		$html .= JText::_($title);

		if ($required)
		{
			$html .= ' *';
		}

		$html .= "</label>\n";

		return $html;
	}

}