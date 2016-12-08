<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\AdminTools\Admin\View\ControlPanel\Html */

// Protect from unauthorized access
defined('_JEXEC') or die;

$logUrl = 'index.php?option=com_admintools&view=SecurityExceptions&datefrom=%s&dateto=%s&groupbydate=0&groupbytype=0';

/** @var \Akeeba\AdminTools\Admin\Model\SecurityExceptions $logsModel */
$logsModel = $this->getContainer()->factory->model('SecurityExceptions')->tmpInstance();

?>
<h3><?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_STATS'); ?></h3>
<table width="100%" class="table table-striped">
	<tbody>
	<tr class="row0">
		<td width="75%">
			<a href="<?php echo sprintf($logUrl, (gmdate('Y') - 1) . '-01-01 00:00:00', (gmdate('Y') - 1) . '-12-31 23:59:59') ?>">
				<?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_STATS_LASTYEAR'); ?>
			</a>
		</td>
		<td style="text-align:right" width="25%">
			<?php
			echo $logsModel
				->datefrom((gmdate('Y') - 1) . '-01-01 00:00:00')
				->dateto((gmdate('Y') - 1) . '-12-31 23:59:59')
				->count();
			?>
		</td>
	</tr>
	<tr class="row1">
		<td>
			<a href="<?php echo sprintf($logUrl, gmdate('Y') . '-01-01', gmdate('Y') . '-12-31 23:59:59') ?>">
				<?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_STATS_THISYEAR'); ?>
			</a>
		</td>
		<td style="text-align:right">
			<?php
				echo $logsModel->reset()
				->datefrom(gmdate('Y') . '-01-01')
				->dateto(gmdate('Y') . '-12-31 23:59:59')
				->count()
			?>
		</td>
	</tr>
	<tr class="row0">
		<?php
		$y = gmdate('Y');
		$m = gmdate('m');
		if ($m == 1)
		{
			$m = 12;
			$y -= 1;
		}
		else
		{
			$m -= 1;
		}
		switch ($m)
		{
			case 1:
			case 3:
			case 5:
			case 7:
			case 8:
			case 10:
			case 12:
				$lmday = 31;
				break;
			case 4:
			case 6:
			case 9:
			case 11:
				$lmday = 30;
				break;
			case 2:
				if (!($y % 4) && ($y % 400))
				{
					$lmday = 29;
				}
				else
				{
					$lmday = 28;
				}
		}
		if ($y < 2011)
		{
			$y = 2011;
		}
		if ($m < 1)
		{
			$m = 1;
		}
		if ($lmday < 1)
		{
			$lmday = 1;
		}
		?>
		<td>
			<a href="<?php echo sprintf($logUrl, $y . '-' . $m . '-01', $y . '-' . $m . '-' . $lmday . ' 23:59:59') ?>">
				<?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_STATS_LASTMONTH'); ?>
			</a>
		</td>
		<td style="text-align:right">
			<?php echo $logsModel->reset()
				->datefrom($y . '-' . $m . '-01')
				->dateto($y . '-' . $m . '-' . $lmday . ' 23:59:59')
				->count()
			?>
		</td>
	</tr>
	<tr class="row1">
		<?php
		switch (gmdate('m'))
		{
			case 1:
			case 3:
			case 5:
			case 7:
			case 8:
			case 10:
			case 12:
				$lmday = 31;
				break;
			case 4:
			case 6:
			case 9:
			case 11:
				$lmday = 30;
				break;
			case 2:
				$y = gmdate('Y');
				if (!($y % 4) && ($y % 400))
				{
					$lmday = 29;
				}
				else
				{
					$lmday = 28;
				}
		}
		if ($lmday < 1)
		{
			$lmday = 28;
		}
		?>
		<td>
			<a href="<?php echo sprintf($logUrl, gmdate('Y') . '-' . gmdate('m') . '-01', gmdate('Y') . '-' . gmdate('m') . '-' . $lmday . ' 23:59:59') ?>">
				<?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_STATS_THISMONTH'); ?>
			</a>
		</td>
		<td style="text-align:right">
			<?php echo $logsModel->reset()
				->datefrom(gmdate('Y') . '-' . gmdate('m') . '-01')
				->dateto(gmdate('Y') . '-' . gmdate('m') . '-' . $lmday . ' 23:59:59')
				->count()
			?>
		</td>
	</tr>
	<tr class="row0">
		<td width="75%">
			<a href="<?php echo sprintf($logUrl, gmdate('Y-m-d', time() - 7 * 24 * 3600), gmdate('Y-m-d')) ?>">
				<?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_STATS_LAST7DAYS'); ?>
			</a>
		</td>
		<td style="text-align:right" width="25%">
			<?php echo $logsModel->reset()
				->datefrom(gmdate('Y-m-d', time() - 7 * 24 * 3600))
				->dateto(gmdate('Y-m-d'))
				->count()
			?>
		</td>
	</tr>
	<tr class="row1">
		<?php
		$date = new DateTime();
		$date->setDate(gmdate('Y'), gmdate('m'), gmdate('d'));
		$date->modify("-1 day");
		$yesterday = $date->format("Y-m-d");
		$date->modify("+1 day")
		?>
		<td width="75%">
			<a href="<?php echo sprintf($logUrl, $yesterday, $date->format("Y-m-d")) ?>">
				<?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_STATS_YESTERDAY'); ?>
			</a>
		</td>
		<td style="text-align:right" width="25%">
			<?php echo $logsModel->reset()
				->datefrom($yesterday)
				->dateto($date->format("Y-m-d"))
				->count()
			?>
		</td>
	</tr>
	<tr class="row0">
		<?php
		$expiry = clone $date;
		$expiry->modify('+1 day');
		?>
		<td width="75%">
			<a href="<?php echo sprintf($logUrl, $date->format("Y-m-d"), $expiry->format("Y-m-d")) ?>">
				<strong><?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_STATS_TODAY'); ?></strong>
			</a>
		</td>
		<td style="text-align:right" width="25%">
			<strong>
				<?php echo $logsModel->reset()
					->datefrom($date->format("Y-m-d"))
					->dateto($expiry->format("Y-m-d"))
					->count()
				?>
			</strong>
		</td>
	</tr>
	</tbody>
</table>