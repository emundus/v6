<?php
/**
 * @package         Regular Labs Library
 * @version         22.4.18687
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Editor\Editor as JEditor;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Field;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

class JFormFieldRL_CodeEditor extends Field
{
	public $type = 'CodeEditor';

	protected function getInput()
	{
		$width  = $this->get('width', '100%');
		$height = $this->get('height', 400);
		$syntax = $this->get('syntax', 'html');

		$editor_plugin = JPluginHelper::getPlugin('editors', 'codemirror');

		if (empty($editor_plugin))
		{
			return
				'<textarea name="' . $this->name . '" style="'
				. 'width:' . (strpos($width, '%') ? $width : $width . 'px') . ';'
				. 'height:' . (strpos($height, '%') ? $height : $height . 'px') . ';'
				. '" id="' . $this->id . '">' . $this->value . '</textarea>';
		}

		RL_Document::script('regularlabs/codemirror.min.js');
		RL_Document::stylesheet('regularlabs/codemirror.min.css');

		JFactory::getDocument()->addScriptDeclaration("
			jQuery(document).ready(function($) {
				RegularLabsCodeMirror.init('" . $this->id . "');
			});
		");

		JFactory::getDocument()->addStyleDeclaration("
			#rl_codemirror_" . $this->id . " .CodeMirror {
			    height: " . $height . "px;
			    min-height: " . min($height, '100') . "px;
			}
		");

		return '<div class="rl_codemirror" id="rl_codemirror_' . $this->id . '">'
			. JEditor::getInstance('codemirror')->display(
				$this->name, htmlentities($this->value),
				$width, $height,
				80, 10,
				false,
				$this->id, null, null,
				['markerGutter' => false, 'activeLine' => true, 'syntax' => $syntax]
			)
			. '</div>';
	}
}
