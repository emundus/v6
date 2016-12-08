<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Form\Field;

defined('_JEXEC') or die;

use FOF30\Form\Field\Text;

class SourceURLWithExternalLink extends Text
{
	public function getRepeatable()
	{
		$source = $this->value;
		$icon   = rtrim(\JUri::base(), '/').'/components/com_admintools/media/images/external-icon.gif';

		$html  = '<a href="'.(strstr($source, '://') ? $source : '../' . $source).'" target="_blank">';
		$html .=    htmlentities($source).'&nbsp;';
		$html .=    '<img src="'.$icon.'" border="0" />';
		$html .= '</a>';

		return $html;
	}
}