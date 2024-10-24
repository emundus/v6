<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('bootstrap.tooltip');


// Load user_profile plugin language
$lang = JFactory::getLanguage();
$lang->load('plg_user_profile', JPATH_ADMINISTRATOR);

require_once (JPATH_SITE.'/components/com_emundus/helpers/cache.php');
$hash = EmundusHelperCache::getCurrentGitHash();

$document = JFactory::getDocument();
$document->addStyleSheet("templates/g5_helium/html/com_users/profile/style/com_users_profile.css?".$hash);

$user_module = JModuleHelper::getModule('mod_emundus_user_dropdown');
$back_url = '/';
if(!empty($user_module->id)) {
    $params = json_decode($user_module->params);
    $link_edit_profile = $params->link_edit_profile;
	$menu = JFactory::getApplication()->getMenu()->getItem($link_edit_profile);
    if(!empty($menu->id)) {
	    $back_url = JRoute::_($menu->link . '&Itemid=' . $menu->id);
    }
}
?>
<div class="profile-edit<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<div class="page-header">
			<h1>
				<?php echo $this->escape($this->params->get('page_heading')); ?>
			</h1>
		</div>
	<?php endif; ?>
	<script type="text/javascript">
		Joomla.twoFactorMethodChange = function(e)
		{
			var selectedPane = 'com_users_twofactor_' + jQuery('#jform_twofactor_method').val();

			jQuery.each(jQuery('#com_users_twofactor_forms_container>div'), function(i, el)
			{
				if (el.id != selectedPane)
				{
					jQuery('#' + el.id).hide(0);
				}
				else
				{
					jQuery('#' + el.id).show(0);
				}
			});
		}
	</script>
	<form id="member-profile" action="<?php echo JRoute::_('index.php?option=com_users&task=profile.save'); ?>" method="post" class="form-validate form-horizontal well" enctype="multipart/form-data">
        <div>
            <a class="em-back-button em-pointer" style="justify-content: start; padding: 20px 0" href="<?php echo $back_url ?>">
                <span class="material-icons em-mr-4">navigate_before</span>
				<?php echo JText::_('GO_BACK'); ?>
            </a>
        </div>
        <?php // Iterate through the form fieldsets and display each one. ?>
		<?php foreach ($this->form->getFieldsets() as $group => $fieldset) : ?>
			<?php $fields = $this->form->getFieldset($group); ?>
			<?php if (count($fields)) : ?>
				<fieldset<?php if($fieldset->label == 'PLG_USER_EMUNDUS_PROFILE_SLIDER_LABEL') : ?> style="display: none"<?php endif; ?>>
					<?php // If the fieldset has a label set, display it as the legend. ?>
					<?php if (isset($fieldset->label)) : ?>
                        <div class="em-heading-form">
						<h1>
							<?php echo JText::_($fieldset->label); ?>
						</h1>
                        </div>
					<?php endif; ?>
					<?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
						<p>
							<?php echo $this->escape(JText::_($fieldset->description)); ?>
						</p>
					<?php endif; ?>
					<?php // Iterate through the fields in the set and display them. ?>
					<?php foreach ($fields as $field) : ?>
						<?php // If the field is hidden, just display the input. ?>
						<?php if ($field->hidden) : ?>
							<?php echo $field->input; ?>
						<?php else : ?>
                            <?php if ($field->fieldname != "name" && $field->fieldname != "username") :?>
                                <div class="control-group">
                                    <div class="control-label">
                                        <?php echo $field->label; ?>
                                        <?php /*if (!$field->required && $field->type !== 'Spacer') : */?><!--
                                            <span class="optional">
                                                <?php /*echo JText::_('COM_USERS_OPTIONAL'); */?>
                                            </span>
                                        --><?php /*endif; */?>
                                    </div>
                                    <div class="controls">
                                        <?php if ($field->fieldname === 'password1') : ?>
                                            <?php // Disables autocomplete ?>
                                            <input type="password" style="display:none">
                                        <?php endif; ?>
                                        <?php echo $field->input; ?>
                                    </div>
                                </div>
                            <?php else :?>
                                <div class="control-group hidden">
                                    <div class="control-label">
                                        <?php echo $field->label; ?>
                                        <?php if (!$field->required && $field->type !== 'Spacer') : ?>
                                            <span class="optional">
                                                <?php echo JText::_('COM_USERS_OPTIONAL'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="controls">
                                        <?php if ($field->fieldname === 'password1') : ?>
                                            <?php // Disables autocomplete ?>
                                            <input type="password" style="display:none">
                                        <?php endif; ?>
                                        <?php echo $field->input; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
						<?php endif; ?>
					<?php endforeach; ?>
				</fieldset>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php if (count($this->twofactormethods) > 1) : ?>
			<fieldset>
				<legend><?php echo JText::_('COM_USERS_PROFILE_TWO_FACTOR_AUTH'); ?></legend>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_twofactor_method-lbl" for="jform_twofactor_method" class="hasTooltip"
							title="<?php echo '<strong>' . JText::_('COM_USERS_PROFILE_TWOFACTOR_LABEL') . '</strong><br />' . JText::_('COM_USERS_PROFILE_TWOFACTOR_DESC'); ?>">
							<?php echo JText::_('COM_USERS_PROFILE_TWOFACTOR_LABEL'); ?>
						</label>
					</div>
					<div class="controls">
						<?php echo JHtml::_('select.genericlist', $this->twofactormethods, 'jform[twofactor][method]', array('onchange' => 'Joomla.twoFactorMethodChange()'), 'value', 'text', $this->otpConfig->method, 'jform_twofactor_method', false); ?>
					</div>
				</div>
				<div id="com_users_twofactor_forms_container">
					<?php foreach ($this->twofactorform as $form) : ?>
						<?php $style = $form['method'] == $this->otpConfig->method ? 'display: block' : 'display: none'; ?>
						<div id="com_users_twofactor_<?php echo $form['method']; ?>" style="<?php echo $style; ?>">
							<?php echo $form['form']; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</fieldset>
			<fieldset>
				<legend>
					<?php echo JText::_('COM_USERS_PROFILE_OTEPS'); ?>
				</legend>
				<div class="alert alert-info">
					<?php echo JText::_('COM_USERS_PROFILE_OTEPS_DESC'); ?>
				</div>
				<?php if (empty($this->otpConfig->otep)) : ?>
					<div class="alert alert-warning">
						<?php echo JText::_('COM_USERS_PROFILE_OTEPS_WAIT_DESC'); ?>
					</div>
				<?php else : ?>
					<?php foreach ($this->otpConfig->otep as $otep) : ?>
						<span class="span3">
							<?php echo substr($otep, 0, 4); ?>-<?php echo substr($otep, 4, 4); ?>-<?php echo substr($otep, 8, 4); ?>-<?php echo substr($otep, 12, 4); ?>
						</span>
					<?php endforeach; ?>
					<div class="clearfix"></div>
				<?php endif; ?>
			</fieldset>
		<?php endif; ?>
		<div class="control-group">
			<div class="controls">
				<a class="btn" href="#" onclick="history.go(-1);" title="<?php echo JText::_('JCANCEL'); ?>">
					<?php echo JText::_('JCANCEL'); ?>
				</a>
                <button type="submit" class="btn btn-primary validate">
					<?php echo JText::_('JSUBMIT'); ?>
                </button>
				<input type="hidden" name="option" value="com_users" />
				<input type="hidden" name="task" value="profile.save" />
			</div>
		</div>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
<?php
	$user = JFactory::getSession()->get('emundusUser');
?>
<script type="text/javascript">
    if(document.getElementById("jform_emundus_profile_lastname") != null) {
        document.getElementById("jform_emundus_profile_lastname").value = "<?php echo $user->lastname; ?>";
    }
    if(document.getElementById("jform_emundus_profile_firstname") != null) {
        document.getElementById("jform_emundus_profile_firstname").value = "<?php echo $user->firstname; ?>";
    }
    if(document.getElementById("jform_emundus_profile_email") != null) {
        document.getElementById("jform_emundus_profile_email").value = "<?php echo $user->email; ?>";
        document.getElementById("jform_username").value = "<?php echo $user->email; ?>";
    }


    // Update username when you change your email.
    jQuery('#jform_email1').keyup(function () {
        jQuery('#jform_username').val(this.value);
        jQuery('#jform_emundus_profile_email').val(this.value);
    });
</script>
