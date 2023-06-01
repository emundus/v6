<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
if(!HIKASHOP_J40)
    return;
$extraButtons = Joomla\CMS\Helper\AuthenticationHelper::getLoginButtons('hikashop_checkout_form');
if(empty($extraButtons))
    return;

$url = base64_encode(Joomla\CMS\Uri\Uri::getInstance()->toString());
foreach ($extraButtons as $button) {
    $dataAttributeKeys = array_filter(array_keys($button), function ($key) {
        return substr($key, 0, 5) == 'data-';
    });
?>
	<div class="form-login__submit form-group">
		<button type="button"
				class="btn btn-secondary w-100 <?php echo isset($button['class']) ? $button['class'] : '' ?>"
				<?php foreach ($dataAttributeKeys as $key) : ?>
					<?php echo $key ?>="<?php echo $button[$key] ?>"
				<?php endforeach; ?>
				<?php if ($button['onclick']) : ?>
				onclick="<?php echo $button['onclick'] ?>"
				<?php endif; ?>
				title="<?php echo Joomla\CMS\Language\Text::_($button['label']) ?>"
				id="<?php echo $button['id'] ?>"
				>
			<?php if (!empty($button['icon'])) : ?>
				<span class="<?php echo $button['icon'] ?>"></span>
			<?php elseif (!empty($button['image'])) : ?>
				<?php echo $button['image']; ?>
			<?php elseif (!empty($button['svg'])) : ?>
				<?php echo $button['svg']; ?>
			<?php endif; ?>
			<?php echo Joomla\CMS\Language\Text::_($button['label']) ?>
		</button>
	</div>
<?php
}
?>
<input name="username" id="hidden_input_for_webauthn_compat" type="hidden"/>
<input name="return" id="hidden_second_input_for_webauthn_compat" type="hidden" value="<?php echo $url; ?>"/>
<script>
window.hikashop.ready(function(){
	document.querySelector('.hikashop_checkout_login #username').addEventListener('change', (event) => {
		document.getElementById('hidden_input_for_webauthn_compat').value = event.target.value ;
	});
});
</script>
