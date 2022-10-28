<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2021 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

use Joomla\CMS\Language\Text;

?>
<div id="eb-event-page" class="eb-container">
<h1 class="eb-page-heading"><?php echo Text::_('EB_REGISTRATION_FAILURE'); ?></h1>
<table width="100%">
	<tr>
		<td colspan="2" align="left">
			<?php echo  Text::_('EB_FAILURE_MESSAGE'); ?>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<?php echo Text::_('EB_REASON'); ?>
		</td>
		<td>
			<p class="info"><?php echo $this->reason; ?></p>
		</td>
	</tr>	
</table>
</div>