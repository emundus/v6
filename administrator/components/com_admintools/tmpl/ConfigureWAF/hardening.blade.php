<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var Akeeba\AdminTools\Admin\View\ConfigureWAF\Html $this */

?>
<div class="akeeba-form-group">
	<label
			for="leakedpwd"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LEAKEDPWD')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LEAKEDPWD_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LEAKEDPWD')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'leakedpwd', $this->wafconfig['leakedpwd'])
</div>

<div class="akeeba-form-group">
	<label
			for="leakedpwd"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LEAKEDPWD_GROUPS')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LEAKEDPWD_GROUPS_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LEAKEDPWD_GROUPS')
	</label>

	@jhtml('access.usergroup', 'leakedpwd_groups[]', $this->wafconfig['leakedpwd_groups'], [
		'multiple' => true, 'size' => 5, 'class' => 'advancedSelect'
	], false)

</div>

<div class="akeeba-form-group">
	<label for="nonewadmins"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_NONEWADMINS')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_NONEWADMINS_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_NONEWADMINS')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'nonewadmins', $this->wafconfig['nonewadmins'])
</div>

<div class="akeeba-form-group">
	<label for="nonewfrontendadmins"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_NONEWFRONTENDADMINS')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_NONEWFRONTENDADMINS_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_NONEWFRONTENDADMINS')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'nonewfrontendadmins', $this->wafconfig['nonewfrontendadmins'])
</div>

<div class="akeeba-form-group">
	<label for="configmonitor_global"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CONFIGMONITORGLOBAL')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CONFIGMONITORGLOBAL_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CONFIGMONITORGLOBAL')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'configmonitor_global', $this->wafconfig['configmonitor_global'])
</div>

<div class="akeeba-form-group">
	<label for="configmonitor_components"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CONFIGMONITORCOMPONENTS')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CONFIGMONITORCOMPONENTS_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CONFIGMONITORCOMPONENTS')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'configmonitor_components', $this->wafconfig['configmonitor_components'])
</div>

<div class="akeeba-form-group">
	<label for="configmonitor_action"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CONFIGMONITORACTION')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CONFIGMONITORACTION_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CONFIGMONITORACTION')
	</label>

	{{ \Akeeba\AdminTools\Admin\Helper\Select::configMonitorAction('configmonitor_action', [], $this->wafconfig['configmonitor_action']) }}
</div>

<div class="akeeba-form-group">
	<label for="criticalfiles"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CRITICALFILES')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CRITICALFILES_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CRITICALFILES')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'criticalfiles', $this->wafconfig['criticalfiles'])
</div>

<div class="akeeba-form-group">
	<label for="criticalfiles_global"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CRITICALFILES_GLOBAL')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CRITICALFILES_GLOBAL_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CRITICALFILES_GLOBAL')
	</label>

	<textarea id="criticalfiles_global" name="criticalfiles_global"
			  rows="5">{{{ $this->wafconfig['criticalfiles_global'] }}}</textarea>
</div>

<div class="akeeba-form-group">
	<label for="superuserslist"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SUPERUSERSLIST')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SUPERUSERSLIST_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SUPERUSERSLIST')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'superuserslist', $this->wafconfig['superuserslist'])
</div>

<div class="akeeba-form-group">
	<label
			for="resetjoomlatfa"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_RESETJOOMLATFA')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_RESETJOOMLATFA_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_RESETJOOMLATFA')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'resetjoomlatfa', $this->wafconfig['resetjoomlatfa'])
</div>

<div class="akeeba-form-group">
	<label for="nofesalogin"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_NOFESALOGIN')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_NOFESALOGIN_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_NOFESALOGIN')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'nofesalogin', $this->wafconfig['nofesalogin'])
</div>

<div class="akeeba-form-group">
	<label
			for="trackfailedlogins"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_TRACKFAILEDLOGINS')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_TRACKFAILEDLOGINS_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_TRACKFAILEDLOGINS')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'trackfailedlogins', $this->wafconfig['trackfailedlogins'])
</div>

<div class="akeeba-form-group">
	<label
			for="logusernames"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LOGUSERNAMES')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LOGUSERNAMES_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LOGUSERNAMES')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'logusernames', $this->wafconfig['logusernames'])
</div>

<?php
// Detect user registration and activation type
$disabled    = false;
$message     = '';
$classes     = [];
$messageType = 'info';

$userParams = \Joomla\CMS\Component\ComponentHelper::getParams('com_users');

// User registration disabled
if (!$userParams->get('allowUserRegistration'))
{
	$classes['disabled'] = 'true';
	$disabled            = true;
	$message             = 'COM_ADMINTOOLS_LBL_CONFIGUREWAF_ALERT_NOREGISTRATION';
}
// Super User user activation
elseif ($userParams->get('useractivation') == 2)
{
	$messageType = 'warning';
	$message = 'COM_ADMINTOOLS_LBL_CONFIGUREWAF_ALERT_ADMINACTIVATION';
}
// No user activation
elseif ($userParams->get('useractivation') == 0)
{
	$classes['disabled'] = 'disabled';
	$disabled            = true;
	$message             = 'COM_ADMINTOOLS_LBL_CONFIGUREWAF_ALERT_NOUSERACTIVATION';
}
?>

<div class="akeeba-form-group">
	<label
			for="deactivateusers"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DEACTIVATEUSERS')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DEACTIVATEUSERS_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DEACTIVATEUSERS')
	</label>

	<div class="akeeba-form--inline">
		<input class="akeeba-input-mini" type="text" size="5" name="deactivateusers_num" {{ $disabled ? 'disabled="true"': '' }}
			   value="{{{ $this->wafconfig['deactivateusers_num'] }}}" />
		<span>@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_DEACTIVATENUMFREQ')</span>
		<input class="akeeba-input-mini" type="text" size="5" name="deactivateusers_numfreq" {{ $disabled ? 'disabled="true"': '' }}
			   value="{{{ $this->wafconfig['deactivateusers_numfreq'] }}}" />
		{{ \Akeeba\AdminTools\Admin\Helper\Select::trsfreqlist('deactivateusers_frequency', $classes, $this->wafconfig['deactivateusers_frequency']) }}
		<div style="margin-top:10px" class="akeeba-block--{{ $messageType }}">
			@lang($message)
		</div>
	</div>
</div>

<div class="akeeba-form-group">
	<label
			for="consolewarn"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CONSOLEWARN')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CONSOLEWARN_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CONSOLEWARN')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'consolewarn', $this->wafconfig['consolewarn'])
</div>

<div class="akeeba-form-group">
	<label
			for="filteremailregistration"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_FILTER_REGISTRATION')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_FILTER_REGISTRATION_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_FILTER_REGISTRATION')
	</label>

	<div class="akeeba-toggle">
		<input type="radio" class="radio-allow" name="filteremailregistration" {{ $this->wafconfig['filteremailregistration'] == 'block' ? 'checked ' : '' }}
			   id="filteremailregistration-2" value="allow">
		<label for="filteremailregistration-2"
			   class="green">@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_FILTER_REGISTRATION_ALLOW')</label>
		<input type="radio" class="radio-block" name="filteremailregistration" {{ $this->wafconfig['filteremailregistration'] == 'allow' ? '' : 'checked ' }}
			   id="filteremailregistration-1" value="block">
		<label for="filteremailregistration-1"
			   class="red">@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_FILTER_REGISTRATION_BLOCK')</label>
	</div>
</div>

<div class="akeeba-form-group">
	<label
			for="blockedemaildomains"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_BLOCKEDEMAILDOMAINS')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_BLOCKEDEMAILDOMAINS_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_BLOCKEDEMAILDOMAINS')
	</label>

	<textarea id="blockedemaildomains" name="blockedemaildomains"
			  rows="5">{{{ $this->wafconfig['blockedemaildomains'] }}}</textarea>
</div>

<h4>@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_HEAD')</h4>

<div class="akeeba-form-group">
	<label
			for="disableobsoleteadmins"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'disableobsoleteadmins', $this->wafconfig['disableobsoleteadmins'])
</div>

<div class="akeeba-form-group">
	<label
			for="disableobsoleteadmins_freq"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_FREQ')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_FREQ_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_FREQ')
	</label>

	<input class="akeeba-input-mini" type="text" size="5" name="disableobsoleteadmins_freq"
		   value="{{{ $this->wafconfig['disableobsoleteadmins_freq'] }}}" />
</div>

<div class="akeeba-form-group">
	<label
			for="disableobsoleteadmins_groups"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_GROUPS')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_GROUPS_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_GROUPS')
	</label>

	@jhtml('access.usergroup', 'disableobsoleteadmins_groups[]', $this->wafconfig['disableobsoleteadmins_groups'], [
		'multiple' => true, 'size' => 5, 'class' => 'advancedSelect'
	], true)
</div>

<div class="akeeba-form-group">
	<label
			for="disableobsoleteadmins_maxdays"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_MAXDAYS')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_MAXDAYS_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_MAXDAYS')
	</label>

	<input class="akeeba-input-mini" type="text" size="5" name="disableobsoleteadmins_maxdays"
		   value="{{{ $this->wafconfig['disableobsoleteadmins_maxdays'] }}}" />
</div>

<div class="akeeba-form-group">
	<label for="disableobsoleteadmins_action"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_ACTION')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_ACTION_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_ACTION')
	</label>

	{{ \Akeeba\AdminTools\Admin\Helper\Select::disableObsoleteAdminsAction('disableobsoleteadmins_action', [], $this->wafconfig['disableobsoleteadmins_action']) }}
</div>

<div class="akeeba-form-group">
	<label
			for="disableobsoleteadmins_protected"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_PROTECTED')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_PROTECTED_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_PROTECTED')
	</label>

	<?php echo \Akeeba\AdminTools\Admin\Helper\Select::backendUsers('disableobsoleteadmins_protected[]', [
		'multiple' => true, 'size' => 10, 'class' => 'advancedSelect',
	], $this->wafconfig['disableobsoleteadmins_protected']) ?>
</div>
