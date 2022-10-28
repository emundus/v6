<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2021 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

if ($this->config->use_https)
{
	$ssl = 1;
}
else
{
	$ssl = 0;
}

EventbookingHelperJquery::colorbox('eb-modal');

Factory::getDocument()->addScriptDeclaration('
    function cancelRegistration(registrantId)
    {
        var form = document.adminForm ;

        if (confirm("' . Text::_('EB_CANCEL_REGISTRATION_CONFIRM') . '"))
        {
            form.task.value = "registrant.cancel" ;
            form.id.value = registrantId ;
            form.submit() ;
        }
    }
');
?>
<div id="eb-search-result-page-table-layout" class="eb-container">
	<h1 class="eb-page-heading"><?php echo $this->escape(Text::_('EB_SEARCH_RESULT')); ?></h1>
	<form method="post" name="adminForm" id="adminForm" action="<?php echo Route::_('index.php?option=com_eventbooking&view=search&layout=table&Itemid='.$this->Itemid); ?>">
		<?php
		if (count($this->items))
		{
			$layoutData = [
				'items'           => $this->items,
				'config'          => $this->config,
				'Itemid'          => $this->Itemid,
				'nullDate'        => $this->nullDate,
				'ssl'             => $ssl,
				'viewLevels'      => $this->viewLevels,
				'bootstrapHelper' => $this->bootstrapHelper,
			];

			echo EventbookingHelperHtml::loadCommonLayout('common/events_table.php', $layoutData);

			if ($this->pagination->total > $this->pagination->limit)
			{
			?>
				<div class="pagination">
					<?php echo $this->pagination->getPagesLinks(); ?>
				</div>
			<?php
			}
		}
		else
		{
		?>
			<p class="text-info"><?php echo Text::_('EB_NO_EVENTS_FOUND') ?></p>
		<?php
		}
		?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="id" value="" />
		<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	</form>
</div>