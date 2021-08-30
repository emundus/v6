<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.core');
HTMLHelper::_('jquery.framework');
HTMLHelper::_('bootstrap.tooltip');

if (!EventbookingHelper::isJoomla4())
{
	HTMLHelper::_('formbehavior.chosen', 'select');
}

$document = Factory::getDocument();
$document->addStyleDeclaration(".hasTip{display:block !important}")
	->addScript(Uri::root(true) . '/media/com_eventbooking/js/admin-sponsor-default.min.js');

$translatable = Multilanguage::isEnabled() && count($this->languages);

if ($translatable)
{
    HTMLHelper::_('behavior.tabstate');
}

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$rowFluid        = $bootstrapHelper->getClassMapping('row-fluid');
$span6           = $bootstrapHelper->getClassMapping('span6');

$languageKeys = [
    'EB_ENTER_SPONSOR_NAME',
];

EventbookingHelperHtml::addJSStrings($languageKeys);

$editor = Editor::getInstance(Factory::getApplication()->get('editor'));
?>
<form action="index.php?option=com_eventbooking&view=sponsor" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('EB_EVENTS'); ?>
        </div>
        <div class="controls">
	        <?php echo EventbookingHelperHtml::getChoicesJsSelect($this->lists['event_id'], Text::_('EB_TYPE_OR_SELECT_SOME_EVENTS')) ; ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo  Text::_('EB_NAME'); ?>
        </div>
        <div class="controls">
            <input type="text" name="name" id="name" class="form-control" size="50" maxlength="250" value="<?php echo $this->item->name;?>" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo  Text::_('EB_LOGO'); ?>
        </div>
        <div class="controls">
	        <?php echo EventbookingHelperHtml::getMediaInput($this->item->logo, 'logo'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo  Text::_('EB_WEBSITE'); ?>
        </div>
        <div class="controls">
            <input class="form-control" type="url" name="website" id="website" size="50" maxlength="250" value="<?php echo $this->item->website;?>" />
        </div>
    </div>
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_( 'form.token' ); ?>
</form>