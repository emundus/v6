<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2021 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>

<div id="eb-waiting-list-complete-page" class="eb-container">
    <h1 class="eb-page-heading"><?php echo Text::_('EB_WATIINGLIST_COMPLETE'); ?></h1>
    <div id="eb-message" class="eb-message"><?php echo HTMLHelper::_('content.prepare', $this->message); ?></div>
</div>