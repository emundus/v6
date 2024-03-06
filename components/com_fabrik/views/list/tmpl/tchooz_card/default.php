<?php
/**
 * Bootstrap List Template - Default
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$app = Factory::getApplication();
$document = $app->getDocument();
$document->addStyleSheet('modules/mod_emundus_campaign/css/mod_emundus_campaign_tchooz.css');

$pageClass = $this->params->get('pageclass_sfx', '');

if ($pageClass !== '') :
	echo '<div class="' . $pageClass . '">';
endif;

if ($this->tablePicker != '') : ?>
    <div style="text-align:right"><?php echo Text::_('COM_FABRIK_LIST') ?>: <?php echo $this->tablePicker; ?></div>
<?php
endif;

if ($this->params->get('show_page_heading')) :
	echo '<h1>' . $this->params->get('page_heading') . '</h1>';
endif;

if ($this->showTitle == 1) : ?>
    <div class="page-header em-flex-row em-flex-space-between emundus-list-page-header">
       <?php if (!$this->showFilters) : ?>
          <h1><?php echo $this->table->label; ?></h1>
       <?php endif; ?>
		<?php if ($this->showAdd) : ?>

            <div><a class="addbutton addRecord em-primary-button em-w-max-content"
                    href="<?php echo $this->addRecordLink; ?>">
					<?php echo Text::_($this->addLabel); ?>
                </a></div>
		<?php
		endif; ?>
    </div>
<?php
endif;

// Intro outside of form to allow for other lists/forms to be injected.
?>
<div class="page-intro <?php if ($this->showTitle != 1) : ?>em-mt-32<?php endif; ?>">
	<?php echo $this->table->intro; ?>
</div>


<form class="fabrikForm form-search em-mt-32 em-flex-column <?php if ($this->showFilters) : ?>catalogue_content_container<?php endif; ?>" action="<?php echo $this->table->action; ?>" method="post"
      id="<?php echo $this->formid; ?>" name="fabrikList">
	<?php
	if ($this->hasButtons):
		echo $this->loadTemplate('buttons');
	endif;

	if ($this->showFilters && $this->bootShowFilters) :
		echo $this->layoutFilters();
	endif;

	?>


    <div class="mod_emundus_campaign__content em-w-100 <?php if ($this->showFilters) : ?>catalogue_cards_container<?php endif; ?>">
        <div id="current_1" class="mod_emundus_campaign__list">
            <div class="fabrikDataContainer">
	            <?php if ($this->showFilters) : ?>
                    <h1 class="em-mb-24"><?php echo $this->table->label; ?></h1>
	            <?php endif; ?>

				<?php foreach ($this->pluginBeforeList as $c) :
					echo $c;
				endforeach;
				?>
                <div class="mod_emundus_campaign__list_items" id="list_<?php echo $this->table->renderid; ?>">
					<?php
					foreach ($this->rows as $groupedBy => $group)
					{
						foreach ($group as $this->_row)
						{
							echo $this->loadTemplate('row');
						}
					}
					?>
                </div>
				<?php print_r($this->hiddenFields); ?>
            </div>
        </div>
    </div>
</form>
<?php
echo $this->table->outro;
if ($pageClass !== '') :
	echo '</div>';
endif;
?>
