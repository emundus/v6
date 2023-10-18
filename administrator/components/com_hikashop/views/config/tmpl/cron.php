<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="page-cron" class="hk-container-fluid">

<div class="hkc-lg-12 hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('CRON'); ?></div>
<table class="hk_config_table table" style="width:100%">

	<tr>
		<td colspan="2"><?php echo $this->elements->cron_edit; ?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('cron_frequency');?>><?php echo JText::_('MIN_DELAY'); ?></td>
		<td><?php
			echo $this->delayType->display('config[cron_frequency]', $this->config->get('cron_frequency', 0), 0);
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('cron_next');?>><?php echo JText::_('NEXT_RUN'); ?></td>
		<td><?php
			echo hikashop_getDate($this->config->get('cron_next'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('cron_url');?>><?php echo JText::_('CRON_URL'); ?></td>
		<td>
			<a href="<?php echo $this->elements->cron_url; ?>" target="_blank"><?php
				echo $this->elements->cron_url;
			?></a>
		</td>
	</tr>

</table>
	</div></div>
</div>

<div class="hkc-lg-6 hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('REPORT'); ?></div>
<table class="hk_config_table table" style="width:100%;margin-bottom:0;">

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('cron_sendreport');?>><?php echo JText::_('REPORT_SEND'); ?></td>
		<td><?php
			echo $this->elements->cron_sendreport;
		?></td>
	</tr>
</table>
<table class="hk_config_table table" style="width:100%" id="cronreportdetail">
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('cron_sendto');?>><?php echo JText::_('REPORT_SEND_TO'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[cron_sendto]" size="50" value="<?php echo $this->config->get('cron_sendto'); ?>">
		</td>
	</tr>
	<tr>
		<td colspan="2"><?php
			echo $this->elements->editReportEmail;
		?></td>
	</tr>

</table>
	</div></div>
</div>

<div class="hkc-lg-6 hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('REPORT'); ?></div>
<table class="hk_config_table table" style="width:100%;margin-bottom:0;">

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('cron_savereport');?>><?php echo JText::_('REPORT_SAVE'); ?></td>
		<td><?php
			echo $this->elements->cron_savereport;
		?></td>
	</tr>
</table>
<table class="hk_config_table table" style="width:100%" id="cronreportsave">
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('cron_savepath');?>><?php echo JText::_('REPORT_SAVE_TO'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[cron_savepath]" size="60" value="<?php echo $this->config->get('cron_savepath'); ?>">
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<?php echo $this->elements->deleteReport;?>
			<?php echo $this->elements->seeReport; ?>
		</td>
	</tr>

</table>
	</div></div>
</div>

<div class="hkc-lg-12 hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('LAST_CRON'); ?></div>
<table class="hk_config_table table" style="width:100%">

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('cron_last');?>><?php echo JText::_('LAST_RUN'); ?></td>
		<td><?php
			echo hikashop_getDate($this->config->get('cron_last'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('cron_fromip');?>><?php echo JText::_('CRON_TRIGGERED_IP'); ?></td>
		<td><?php
			echo $this->config->get('cron_fromip');
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('cron_report');?>><?php echo JText::_('REPORT'); ?></td>
		<td><?php
			echo $this->config->get('cron_report');
		?></td>
	</tr>

</table>
	</div></div>
</div>

</div>
