<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.core');
HTMLHelper::_('jquery.framework');

if (EventbookingHelper::isJoomla4())
{
	HTMLHelper::_('script', 'system/showon.js', array('version' => 'auto', 'relative' => true));

	$tabApiPrefix = 'uitab.';
}
else
{
	HTMLHelper::_('formbehavior.chosen', 'select#event_id,select#category_id');
	HTMLHelper::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));

	$tabApiPrefix = 'bootstrap.';
}

$rootUri = Uri::root(true);

HTMLHelper::_('bootstrap.tooltip');

$document = Factory::getDocument();
$document->addStyleDeclaration(".hasTip{display:block !important}");
$document->addScript($rootUri . '/media/com_eventbooking/js/admin-field-default.min.js');

$document->addScriptOptions('validateRules', EventbookingHelper::validateRules());
$document->addScriptOptions('siteUrl', Uri::base(true));

$translatable = Multilanguage::isEnabled() && count($this->languages);

if ($translatable && !EventbookingHelper::isJoomla4())
{
	HTMLHelper::_('behavior.tabstate');
}

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$rowFluid        = $bootstrapHelper->getClassMapping('row-fluid');
$span6           = $bootstrapHelper->getClassMapping('span6');

$languageKeys = [
	'EB_ENTER_FIELD_NAME',
	'EB_ENTER_FIELD_TITLE',
];

$hasCustomSettings = file_exists(__DIR__ . '/default_custom_settings.php');

$useTab = $translatable || $hasCustomSettings;

EventbookingHelperHtml::addJSStrings($languageKeys);
?>
<form action="index.php?option=com_eventbooking&view=field" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
<div class="<?php echo $rowFluid; ?>">
<?php
if ($useTab)
{
	echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'field', array('active' => 'general-page'));
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'field', 'general-page', Text::_('EB_GENERAL', true));
}
?>
	<div class="<?php echo $span6; ?>">
		<fieldset class="form-horizontal options-form">
			<legend><?php echo Text::_('EB_BASIC'); ?></legend>
			<?php
			if ($this->config->custom_field_by_category)
			{
			?>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('EB_CATEGORY'); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getChoicesJsSelect($this->lists['category_id'], Text::_('EB_TYPE_OR_SELECT_SOME_CATEGORIES')) ; ?>
					</div>
				</div>
			<?php
			}
			else
			{
			?>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('EB_FIELD_ASSIGNMENT'); ?>
					</div>
					<div class="controls">
						<?php echo $this->lists['assignment'] ; ?>
					</div>
				</div>
				<div class="control-group" id="events_selection_container"<?php if ($this->assignment == 0) echo 'style="display:none;"'; ?>>
					<div class="control-label">
						<?php echo Text::_('EB_EVENT'); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getChoicesJsSelect($this->lists['event_id'], Text::_('EB_TYPE_OR_SELECT_SOME_EVENTS')) ; ?>
					</div>
				</div>
				<?php
			}
			?>

			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('name', Text::_('EB_NAME'), Text::_('EB_FIELD_NAME_REQUIREMENT')); ?>
				</div>
				<div class="controls">
					<input class="form-control" type="text" name="name" id="name" size="50" maxlength="250" value="<?php echo $this->item->name;?>" <?php if ($this->item->is_core) echo 'readonly="readonly"' ;?> />
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo  Text::_('EB_TITLE'); ?>
				</div>
				<div class="controls">
					<input class="form-control" type="text" name="title" id="title" size="50" maxlength="250" value="<?php echo $this->item->title;?>" />
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('EB_DISPLAY_IN'); ?>
				</div>
				<div class="controls">
					<?php echo $this->lists['display_in']; ?>
				</div>
			</div>
            <?php
                if ($this->config->activate_waitinglist_feature)
                {
                ?>
                    <div class="control-group">
                        <div class="control-label">
			                <?php echo Text::_('EB_SHOW_ON_REGISTRATION_TYPE'); ?>
                        </div>
                        <div class="controls">
			                <?php echo $this->lists['show_on_registration_type']; ?>
                        </div>
                    </div>
                <?php
                }
            ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('EB_ACCESS'); ?>
				</div>
				<div class="controls">
					<?php echo $this->lists['access']; ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('EB_REQUIRED'); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('required', $this->item->required); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('EB_PUBLISHED'); ?>
				</div>
				<div class="controls">
					<?php echo $this->lists['published']; ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('only_show_for_first_member', Text::_('EB_ONLY_SHOW_FOR_FIRST_GROUP_MEMBER'), Text::_('EB_ONLY_SHOW_FOR_FIRST_GROUP_MEMBER_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('only_show_for_first_member', $this->item->only_show_for_first_member); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('only_require_for_first_member', Text::_('EB_ONLY_REQUIRE_FOR_FIRST_GROUP_MEMBER'), Text::_('EB_ONLY_REQUIRE_FOR_FIRST_GROUP_MEMBER_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('only_require_for_first_member', $this->item->only_require_for_first_member); ?>
				</div>
			</div>
            <div class="control-group">
                <div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('hide_for_first_group_member', Text::_('EB_HIDE_FOR_FIRST_GROUP_MEMBER'), Text::_('EB_HIDE_FOR_FIRST_GROUP_MEMBER_EXPLAIN')); ?>
                </div>
                <div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('hide_for_first_group_member', $this->item->hide_for_first_group_member); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('not_required_for_first_group_member', Text::_('EB_NOT_REQUIRED_FOR_FIRST_GROUP_MEMBER'), Text::_('EB_NOT_REQUIRED_FOR_FIRST_GROUP_MEMBER_EXPLAIN')); ?>
                </div>
                <div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('not_required_for_first_group_member', $this->item->not_required_for_first_group_member); ?>
                </div>
            </div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_on_registrants', Text::_('EB_SHOW_ON_REGISTRANTS'), Text::_('EB_SHOW_ON_REGISTRANTS_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_on_registrants', $this->item->show_on_registrants); ?>
				</div>
			</div>
            <div class="control-group">
                <div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_on_public_registrants_list', Text::_('EB_SHOW_ON_PUBLIC_REGISTRANTS'), Text::_('EB_SHOW_ON_PUBLIC_REGISTRANTS_EXPLAIN')); ?>
                </div>
                <div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_on_public_registrants_list', $this->item->show_on_public_registrants_list); ?>
                </div>
            </div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('hide_on_email', Text::_('EB_HIDE_ON_EMAIL'), Text::_('EB_HIDE_ON_EMAIL_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('hide_on_email', $this->item->hide_on_email); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('hide_on_export', Text::_('EB_HIDE_ON_EXPORT'), Text::_('EB_HIDE_ON_EXPORT_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('hide_on_export', $this->item->hide_on_export); ?>
				</div>
			</div>
			<?php
				if ($this->item->id && in_array($this->item->display_in, array(0, 1, 2, 3)))
				{
				?>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('receive_confirmation_email', Text::_('EB_RECEIVE_CONFIRMATION_EMAIL'), Text::_('EB_RECEIVE_CONFIRMATION_EMAIL_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<?php echo EventbookingHelperHtml::getBooleanInput('receive_confirmation_email', $this->item->receive_confirmation_email); ?>
						</div>
					</div>
				<?php
				}
			?>
            <div class="control-group">
                <div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('populate_from_previous_registration', Text::_('EB_POPULATE_FROM_PREVIOUS_REGISTRATION')); ?>
                </div>
                <div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('populate_from_previous_registration', $this->item->populate_from_previous_registration); ?>
                </div>
            </div>
			<div class="control-group">
				<div class="control-label">
					<?php echo  Text::_('EB_DESCRIPTION'); ?>
				</div>
				<div class="controls">
					<textarea rows="5" cols="50" name="description" class="form-control input-xlarge"><?php echo $this->item->description;?></textarea>
				</div>
			</div>
			<?php
			if (isset($this->lists['field_mapping']))
			{
			?>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('field_mapping', Text::_('EB_FIELD_MAPPING'), Text::_('EB_FIELD_MAPPING_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo $this->lists['field_mapping'] ; ?>
					</div>
				</div>
			<?php
			}

			if (isset($this->lists['newsletter_field_mapping']))
			{
			?>
                <div class="control-group">
                    <div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('newsletter_field_mapping', Text::_('EB_NEWSLETTER_FIELD_MAPPING'), Text::_('EB_NEWSLETTER_FIELD_MAPPING_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
						<?php echo $this->lists['newsletter_field_mapping'] ; ?>
                    </div>
                </div>
			<?php
			}
			?>
		</fieldset>
	</div>
	<div class="<?php echo $span6; ?>">
		<fieldset class="form-horizontal options-form">
			<legend><?php echo Text::_('EB_FIELD_SETTINGS'); ?></legend>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('EB_FIELD_TYPE'); ?>
				</div>
				<div class="controls">
					<?php echo $this->lists['fieldtype']; ?>
				</div>
			</div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowon(array('fieldtype' => ['Number', 'Range'])); ?>'>
                <div class="control-label">
					<?php echo Text::_('EB_MAX'); ?>
                </div>
                <div class="controls">
                    <input type="text" name="max" value="<?php echo $this->item->max; ?>" class="input-small form-control" />
                </div>
            </div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowon(array('fieldtype' => ['Number', 'Range'])); ?>'>
                <div class="control-label">
					<?php echo Text::_('EB_MIN'); ?>
                </div>
                <div class="controls">
                    <input type="text" name="min" value="<?php echo $this->item->min; ?>" class="input-small form-control" />
                </div>
            </div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowon(array('fieldtype' => ['Number', 'Range'])); ?>'>
                <div class="control-label">
					<?php echo Text::_('EB_STEP'); ?>
                </div>
                <div class="controls">
                    <input type="text" name="step" value="<?php echo $this->item->step; ?>" class="input-small form-control" />
                </div>
            </div>
			<div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowon(array('fieldtype' => ['List', 'SQL'])); ?>'>
				<div class="control-label">
					<?php echo Text::_('EB_MULTIPLE'); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('multiple', $this->item->multiple); ?>
				</div>
			</div>
			<div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowon(array('fieldtype' => ['List', 'Checkboxes', 'Radio'])); ?>'>
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('values', Text::_('EB_VALUES'), Text::_('EB_EACH_ITEM_LINE')); ?>
				</div>
				<div class="controls">
					<textarea rows="5" cols="50" name="values" class="input-xlarge form-control"><?php echo $this->item->values; ?></textarea>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('default_values', Text::_('EB_DEFAULT_VALUES'), Text::_('EB_EACH_ITEM_LINE')); ?>
				</div>
				<div class="controls">
					<textarea rows="5" cols="50" name="default_values" class="input-xlarge form-control"><?php echo $this->item->default_values; ?></textarea>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo Text::_('EB_FEE_FIELD') ; ?></div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('fee_field', $this->item->fee_field); ?>
				</div>
			</div>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowon(array('fee_field' => '1')); ?>'>
                <div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('discountable', Text::_('EB_DISCOUNTABLE'), Text::_('EB_DISCOUNTABLE_EXPLAIN')); ?>
                </div>
                <div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('discountable', $this->item->discountable); ?>
                </div>
            </div>
			<div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowon(array('fieldtype' => ['List', 'Checkboxes', 'Radio'])); ?>'>
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('fee_values', Text::_('EB_FEE_VALUES'), Text::_('EB_EACH_ITEM_LINE')); ?>
				</div>
				<div class="controls">
					<textarea rows="5" cols="50" name="fee_values" class="input-xlarge form-control"><?php echo $this->item->fee_values; ?></textarea>
				</div>
			</div>
            <?php
            $showOnData = array(
	            'fieldtype' => array('Text', 'Number', 'List', 'Countries', 'Checkboxes', 'Radio', 'Range'),
	            'fee_field' => '1'
            );
            ?>
			<div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowon($showOnData); ?>'>
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('fee_formula', Text::_('EB_FEE_FORMULA'), Text::_('EB_FEE_FORMULA_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<input type="text" class="form-control" size="50" name="fee_formula" value="<?php echo $this->item->fee_formula ; ?>" />
				</div>
			</div>

			<div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowon(array('fieldtype' => ['List', 'Checkboxes', 'Radio'])); ?>'>
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('quantity_field', Text::_('EB_QUANTITY_FIELD')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('quantity_field', $this->item->quantity_field); ?>
				</div>
			</div>
			<?php
			$showOnData = array(
				'fieldtype' => array('List', 'Checkboxes', 'Radio', 'Range'),
				'quantity_field' => '1'
			);
			?>
			<div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowon($showOnData); ?>'>
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('quantity_values', Text::_('EB_QUANTITY_VALUES')); ?>
				</div>
				<div class="controls">
					<textarea rows="5" cols="50" name="quantity_values" class="input-xlarge form-control"><?php echo $this->item->quantity_values; ?></textarea>
				</div>
			</div>
			<?php
			$showOnData = array(
				'fieldtype' => array('List', 'Checkboxes', 'Radio')
			);
			?>
            <div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowon($showOnData); ?>'>
                <div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('filterable', Text::_('EB_FILTERABLE'), Text::_('EB_FILTERABLE_EXPLAIN')); ?>
                </div>
                <div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('filterable', $this->item->filterable); ?>
                </div>
            </div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('EB_DEPEND_ON_FIELD');?>
				</div>
				<div class="controls">
					<?php echo $this->lists['depend_on_field_id']; ?>
				</div>
			</div>
			<div class="control-group" id="depend_on_options_container" style="display: <?php echo $this->item->depend_on_field_id ? '' : 'none'; ?>">
				<div class="control-label">
					<?php echo Text::_('EB_DEPEND_ON_OPTIONS');?>
				</div>
				<div class="controls" id="options_container">
					<?php
					if (count($this->dependOptions))
					{
					?>
                        <div class="<?php echo $bootstrapHelper->getClassMapping('row-fluid'); ?>">
                            <?php
                                $span4Class = $bootstrapHelper->getClassMapping('span4');

                                for ($i = 0 , $n = count($this->dependOptions) ; $i < $n ; $i++)
                                {
	                                $value = $this->dependOptions[$i] ;
	                            ?>
                                    <div class="<?php echo $span4Class; ?>">
                                        <input value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>" type="checkbox" class="form-check-input" name="depend_on_options[]" <?php if (in_array($value, $this->dependOnOptions)) echo 'checked="checked"'; ?>><?php echo $value;?>
                                    </div>
                                <?php
                                }
                            ?>
                        </div>
					<?php
					}
					?>
				</div>
			</div>
		</fieldset>
		<fieldset class="form-horizontal options-form">
			<legend><?php echo Text::_('EB_DISPLAY_SETTINGS'); ?></legend>
			<div class="control-group">
				<div class="control-label">
					<?php echo  Text::_('EB_CSS_CLASS'); ?>
				</div>
				<div class="controls">
					<input class="form-control" type="text" name="css_class" id="css_class" size="10" maxlength="250" value="<?php echo $this->item->css_class;?>" />
				</div>
			</div>
			<div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowon(array('fieldtype' => ['Text', 'Textarea'])); ?>'>
				<div class="control-label">
					<?php echo  Text::_('EB_PLACE_HOLDER'); ?>
				</div>
				<div class="controls">
					<input class="form-control" type="text" name="place_holder" id="place_holder" size="50" maxlength="250" value="<?php echo $this->item->place_holder;?>" />
				</div>
			</div>
			<div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowon(array('fieldtype' => ['Text', 'Checkboxes', 'Radio', 'List'])); ?>'>
				<div class="control-label">
					<?php echo  Text::_('EB_SIZE'); ?>
				</div>
				<div class="controls">
					<input class="form-control" type="text" name="size" id="size" size="10" maxlength="250" value="<?php echo $this->item->size;?>" />
				</div>
			</div>
			<div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowon(array('fieldtype' => ['Text', 'Textarea'])); ?>'>
				<div class="control-label">
					<?php echo  Text::_('EB_MAX_LENGTH'); ?>
				</div>
				<div class="controls">
					<input class="form-control" type="text" name="max_length" id="max_lenth" size="50" maxlength="250" value="<?php echo $this->item->max_length;?>" />
				</div>
			</div>
			<div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowon(array('fieldtype' => ['Textarea'])); ?>'>
				<div class="control-label">
					<?php echo  Text::_('EB_ROWS'); ?>
				</div>
				<div class="controls">
					<input class="form-control" type="text" name="rows" id="rows" size="10" maxlength="250" value="<?php echo $this->item->rows;?>" />
				</div>
			</div>
			<div class="control-group" data-showon='<?php echo EventbookingHelperHtml::renderShowon(array('fieldtype' => ['Textarea'])); ?>'>
				<div class="control-label">
					<?php echo  Text::_('EB_COLS'); ?>
				</div>
				<div class="controls">
					<input class="form-control" type="text" name="cols" id="cols" size="10" maxlength="250" value="<?php echo $this->item->cols;?>" />
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo  Text::_('EB_EXTRA'); ?>
				</div>
				<div class="controls">
					<input class="form-control" type="text" name="extra_attributes" id="extra" size="40" maxlength="250" value="<?php echo $this->item->extra_attributes;?>" />
				</div>
			</div>
            <div class="control-group">
                <div class="control-label">
					<?php echo  Text::_('EB_POSITION'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['position']; ?>
                </div>
            </div>
		</fieldset>
        <fieldset class="form-horizontal options-form">
            <legend><?php echo Text::_('EB_VALIDATION'); ?></legend>
            <div class="control-group">
                <div class="control-label">
			        <?php echo Text::_('EB_DATATYPE_VALIDATION') ; ?>
                </div>
                <div class="controls">
			        <?php echo $this->lists['datatype_validation']; ?>
                </div>
            </div>
            <div class="control-group validation-rules">
                <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('validation_rules', Text::_('EB_VALIDATION_RULES'), Text::_('EB_VALIDATION_RULES_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input type="text" class="form-control input-xlarge" size="50" name="validation_rules" value="<?php echo $this->item->validation_rules ; ?>" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
					<?php echo  Text::_('EB_SERVER_VALIDATION_RULES'); ?>
                </div>
                <div class="controls">
                    <input class="input-xlarge form-control" type="text" name="server_validation_rules" id="server_validation_rules" size="10" maxlength="250" value="<?php echo $this->item->server_validation_rules;?>" />
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
			        <?php echo  Text::_('EB_VALIDATION_ERROR_MESSAGE'); ?>
                </div>
                <div class="controls">
                    <input class="input-xlarge form-control" type="text" name="validation_error_message" id="validation_error_message" size="10" maxlength="250" value="<?php echo $this->item->validation_error_message;?>" />
                </div>
            </div>
        </fieldset>
	</div>
<?php

if ($useTab)
{
	echo HTMLHelper::_($tabApiPrefix . 'endTab');
}

if ($hasCustomSettings)
{
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'field', 'custom-settings-page', Text::_('EB_CUSTOM_SETTINGS', true));
	echo $this->loadTemplate('custom_settings');
	echo HTMLHelper::_($tabApiPrefix . 'endTab');
}

if ($translatable)
{
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'field', 'translation-page', Text::_('EB_TRANSLATION', true));
	echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'field-translation', array('active' => 'translation-page-'.$this->languages[0]->sef));
	foreach ($this->languages as $language)
	{
		$sef = $language->sef;
		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'field-translation', 'translation-page-' . $sef, $language->title . ' <img src="' . $rootUri . '/media/mod_languages/images/' . $language->image . '.gif" />');
		?>
		<div class="control-group">
			<div class="control-label">
				<?php echo  Text::_('EB_TITLE'); ?>
			</div>
			<div class="controls">
				<input class="input-xlarge form-control" type="text" name="title_<?php echo $sef; ?>" id="title_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'title_'.$sef}; ?>" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('EB_DESCRIPTION'); ?>
			</div>
			<div class="controls">
				<textarea class="input-xlarge form-control" rows="5" cols="50" name="description_<?php echo $sef; ?>"><?php echo $this->item->{'description_'.$sef};?></textarea>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('EB_VALUES'); ?>
			</div>
			<div class="controls">
				<textarea class="input-xlarge form-control" rows="5" cols="50" name="values_<?php echo $sef; ?>"><?php echo $this->item->{'values_'.$sef}; ?></textarea>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('EB_DEFAULT_VALUES'); ?>
			</div>
			<div class="controls">
				<textarea class="input-xlarge form-control" rows="5" cols="50" name="default_values_<?php echo $sef; ?>"><?php echo $this->item->{'default_values_'.$sef}; ?></textarea>
			</div>
		</div>
        <div class="control-group">
            <div class="control-label">
				<?php echo  Text::_('EB_PLACE_HOLDER'); ?>
            </div>
            <div class="controls">
                <input class="input-xlarge form-control" type="text" name="place_holder_<?php echo $sef; ?>" id="place_holder_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'place_holder_'.$sef}; ?>" />
            </div>
        </div>
		<?php
		echo HTMLHelper::_($tabApiPrefix . 'endTab');
	}
	echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
	echo HTMLHelper::_($tabApiPrefix . 'endTab');
}


if ($useTab)
{
	echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
}
?>
	<div class="clearfix"></div>
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
	<input type="hidden" name="task" value="" />	
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>