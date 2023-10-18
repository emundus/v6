<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=vote" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div id="page-vote" class="hk-row-fluid hikashop_backend_tile_edition">
<div class="hkc-md-6 hikashop_tile_block"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('MAIN_INFORMATION'); ?></div>
	<table class="admintable table" width="100%">
<?php
$this->setLayout('normal');
echo $this->loadTemplate();
?>
		<tr>
			<td class="key">
				<label for="data[vote][vote_published]">
					<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
				</label>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "data[vote][vote_published]" , '',@$this->element->vote_published); ?>
			</td>
		</tr>
	</table>
</div></div>
<div class="hkc-md-6 hikashop_tile_block"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('CUSTOMER'); ?></div>
	<table class="admintable table" width="100%">
		<tr>
			<td class="key">
				<label for="data[vote][vote_type]">
					<?php echo JText::_( 'HIKA_TYPE' ); ?>
				</label>
			</td>
			<td>
<?php
$values = array(
	JHTML::_('select.option', 'registered', JText::_('HIKA_REGISTERED')),
	JHTML::_('select.option', 'anonymous', JText::_('HIKA_ANONYMOUS')),
);
echo JHTML::_('hikaselect.radiolist', $values, 'data[vote][vote_user_type]', 'onchange="displayUserFields(this);"', 'value', 'text', $this->user_type);

?>
			</td>
		</tr>
	</table>
	<table id="hikashop_vote_anonymous"<?php echo $this->display_anon; ?> class="admintable table hikashop_vote_anonymous" width="100%">
		<tr>
			<td class="key">
				<label for="data[vote][vote_pseudo]">
					<?php echo JText::_( 'HIKA_USERNAME' ); ?>
				</label>
			</td>
			<td>
				<input type="text" size="100" name="data[vote][vote_pseudo]" value="<?php echo $this->escape($this->element->vote_pseudo); ?>" />

			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[vote][vote_email]">
					<?php echo JText::_( 'HIKA_EMAIL' ); ?>
				</label>
			</td>
			<td>
				<input type="text" size="100" name="data[vote][vote_email]" value="<?php echo $this->escape($this->element->vote_email); ?>"/>

			</td>
		</tr>
	</table>
	<table id="hikashop_vote_registered"<?php echo $this->display_reg; ?> class="admintable table hikashop_vote_registered" width="100%">
		<tr>
			<td class="key">
				<label for="data[vote][vote_user_id]">
					<?php echo JText::_( 'HIKA_USER' ); ?>
				</label>
			</td>
			<td>
<?php
echo $this->nameboxType->display(
	'data[vote][vote_user_id]',
	@$this->element->vote_user_id,
	hikashopNameboxType::NAMEBOX_SINGLE,
	'user',
	array(
		'delete' => false,
		'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
	)
);
?>
			</td>
		</tr>
	</table>

	<table class="admintable table" width="100%">
<?php if($this->config->get('vote_ip', 1)) { ?>
		<tr>
			<td class="key">
				<label for="data[vote][vote_ip]">
					<?php echo JText::_( 'HIKA_IP' ); ?>
				</label>
			</td>
			<td>
				<input type="text" size="100" name="data[vote][vote_ip]" value="<?php echo $this->escape($this->element->vote_ip); ?>" />
			</td>
		</tr>
<?php } ?>
	</table>
</div></div>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->vote_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="vote" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<script>
	function displayUserFields(el) {
		console.log(el);
		var anon = document.getElementById('hikashop_vote_anonymous'),
		reg = document.getElementById('hikashop_vote_registered');
		if(el.value == 'registered') {
			anon.style.display = 'none';
			reg.style.display = '';
		} else {
			anon.style.display = '';
			reg.style.display = 'none';
		}
	}
</script>
