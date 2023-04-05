<?php
/**
 * Admin Element Edit:publishing Tmpl
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.0
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

echo HTMLHelper::_('tabs.panel', Text::_('COM_FABRIK_PUBLISHING'), 'settings');
$panels = array(
	array('heading' => Text::_('COM_FABRIK_ELEMENT_LABEL_PUBLISHING_DETAILS'),
		'fieldset' => 'publishing'),

	array('heading' => Text::_('COM_FABRIK_ELEMENT_LABEL_RSS'),
		'fieldset' => 'rss'),

	array('heading' => Text::_('COM_FABRIK_ELEMENT_LABEL_TIPS'),
		'fieldset' => 'tips')
);

echo HTMLHelper::_('sliders.start', 'element-sliders-publishingsettings-' . $this->item->id, array('useCookie' => 1));

foreach ($panels as $panel) :
	echo HTMLHelper::_('sliders.panel', $panel['heading'], $panel['fieldset'] . '-details');
			?>
			<fieldset class="adminform">
				<ul class="adminformlist">
					<?php foreach ($this->form->getFieldset($panel['fieldset']) as $field) :?>
					<li>
						<?php echo $field->label;
						echo $field->input; ?>
					</li>
					<?php endforeach; ?>
				</ul>
			</fieldset>
<?php
endforeach;
echo HTMLHelper::_('sliders.end');
