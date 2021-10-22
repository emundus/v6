<?php

/**
 * @package     External_Login
 * @subpackage  Component
 * @author      Christophe Demko <chdemko@gmail.com>
 * @author      Ioannis Barounis <contact@johnbarounis.com>
 * @author      Alexandre Gandois <alexandre.gandois@etudiant.univ-lr.fr>
 * @copyright   Copyright (C) 2008-2018 Christophe Demko, Ioannis Barounis, Alexandre Gandois. All rights reserved.
 * @license     GNU General Public License, version 2. http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.chdemko.com
 */

// No direct access to this file
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$user = JFactory::getUser();

if (!count($this->items)){
	?>
	<tr class="row<?php echo $i % 2; ?>">
		<td colspan="4" class="center">
			<?php echo JText::_('COM_EXTERNALLOGIN_NO_RECORDS'); ?>
		</td>
	</tr>
	<?php 
} else {
 foreach($this->items as $i => $item): ?>
	<tr class="row<?php echo $i % 2; ?>">
		<td>
			<?php echo $this->escape(JText::_('COM_EXTERNALLOGIN_GRID_LOG_PRIORITY_' . $item->priority)); ?>
		</td>
		<td>
			<?php echo $this->escape($item->category); ?>
		</td>
		<td>
			<?php echo date('Y-m-d H:i:s', (int)$item->date); ?>
		</td>
		<td>
			<?php echo $this->escape($item->message); ?>
		</td>
	</tr>
<?php endforeach; }?>
