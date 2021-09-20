<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

if (count($this->plugins) > 1)
{
?>
	<div id="eb-plugins-output" class="clearfix">
        <?php
            echo HTMLHelper::_('bootstrap.startTabSet', 'eb-event-plugins-output', array('active' => 'eb-plugin-page-0'));

            $count = 0;

            foreach ($this->plugins as $plugin)
            {
                if (is_array($plugin) && array_key_exists('title', $plugin) && array_key_exists('form', $plugin))
                {
	                echo HTMLHelper::_('bootstrap.addTab', 'eb-event-plugins-output', 'eb-plugin-page-' . $count, $plugin['title']);
	                echo $plugin['form'];
	                echo HTMLHelper::_('bootstrap.endTab');

	                $count++;
                }
            }

            echo HTMLHelper::_('bootstrap.endTabSet');
        ?>
	</div>	
<?php
}
else
{
	$plugin = $this->plugins[0];
?>
	<div id="eb-plugins">
		<h3 class="eb-tabbed-plugin-header<?php if (!empty($plugin['name'])) echo ' eb-plugin-' . $plugin['name']; ?>"><?php echo $plugin['title']; ?></h3>
		<div class="eb-plugin-output">
			<?php echo $plugin['form']; ?>
		</div>
		<div class="clearfix"></div>
	</div>
<?php
}
