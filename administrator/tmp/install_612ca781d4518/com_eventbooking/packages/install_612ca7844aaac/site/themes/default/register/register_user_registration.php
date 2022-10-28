<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

/**
 * Layout variables
 * -----------------
 * @var   string $username
 * @var   string $controlGroupClass
 * @var   string $controlLabelClass
 * @var   string $controlsClass
 */

$params = ComponentHelper::getParams('com_users');
$minimumLength = $params->get('minimum_length', 4);
($minimumLength) ? $minSize = ",minSize[$minimumLength]" : $minSize = "";

$bootstrapHelper   = $this->bootstrapHelper;
?>
<div class="<?php echo $controlGroupClass;  ?>">
	<div class="<?php echo $controlLabelClass; ?>">
		<?php echo  Text::_('EB_USERNAME') ?><span class="required">*</span>
	</div>
	<div class="<?php echo $controlsClass; ?>">
		<input type="text" name="username" id="username1" class="input-large validate[required,minSize[2],ajax[ajaxUserCall]]<?php echo $bootstrapHelper->getFrameworkClass('uk-input',1); ?>" value="<?php echo $this->escape($this->input->getUsername('username')); ?>" />
	</div>
</div>
<div class="<?php echo $controlGroupClass;  ?>">
	<div class="<?php echo $controlLabelClass; ?>">
		<?php echo  Text::_('EB_PASSWORD') ?><span class="required">*</span>
	</div>
	<div class="<?php echo $controlsClass; ?>">
		<input type="password" name="password1" id="password1" class="input-large validate[required<?php echo $minSize;?>]<?php echo $bootstrapHelper->getFrameworkClass('uk-input',1); ?>" value=""/>
	</div>
</div>
<div class="<?php echo $controlGroupClass;  ?>">
	<div class="<?php echo $controlLabelClass; ?>">
		<?php echo  Text::_('EB_RETYPE_PASSWORD') ?><span class="required">*</span>
	</div>
	<div class="<?php echo $controlsClass; ?>">
		<input type="password" name="password2" id="password2" class="input-large validate[required,equals[password1]]<?php echo $bootstrapHelper->getFrameworkClass('uk-input',1); ?>" value="" />
	</div>
</div>