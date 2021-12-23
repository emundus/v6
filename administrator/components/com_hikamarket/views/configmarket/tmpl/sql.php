<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikamarket::completeLink('config&task=sql'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<fieldset>
		<legend>SQL Request</legend>
		<textarea name="sql_data" rows="8" style="width:100%"><?php echo @$this->sql_data; ?></textarea>
	</fieldset>
	<div style="overflow-x:auto;max-height:300px;">
<?php
		if(!empty($this->query_result)) {
			if(is_array($this->query_result)) {
				echo '<table class="adminlist table table-striped" style="width:100%"><thead>';
				$head = array_keys(get_object_vars(reset($this->query_result)));
				foreach($head as $h) {
					echo '<th>'.$h.'</th>';
				}
				reset($this->query_result);

				echo '</thead><tbody>';
				foreach($this->query_result as $result) {
					echo '<tr>';
					foreach($head as $h) {
						echo '<td>'.nl2br(htmlentities($result->$h)).'</td>';
					}
					echo '</tr>';
				}
				echo '</tbody></table>';
			} else {
				echo $this->query_result;
			}
		} else if(!empty($this->sql_data)) {
			echo JText::_('HIKA_NO_SQL');
		}
?>
	</div>

	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
