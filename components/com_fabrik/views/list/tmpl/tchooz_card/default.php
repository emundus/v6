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


    <div class="mod_emundus_campaign__content em-w-100 tabs" id="<?php if ($this->showFilters) : ?>catalogue_container<?php endif; ?>">
        <div id="current_1" class="mod_emundus_campaign__list">
            <div class="fabrikDataContainer">
	            <?php if ($this->showFilters) : ?>
                <div class="em-flex-row em-flex-space-between em-mb-16">
                    <h1><?php echo $this->table->label; ?></h1>
                    <div class="em-flex-row-justify-end em-gap-8 fabrik-switch-view-buttons">
                        <button type="button" onclick="switchView('list')" class="em-pointer material-icons-outlined fabrik-switch-view-icon active" id="fabrik_switch_view_list_icon">menu</button>
                        <button type="button" onclick="switchView('grid')" class="em-pointer material-icons-outlined fabrik-switch-view-icon" id="fabrik_switch_view_grid_icon">grid_view</button>
                    </div>
                </div>
	            <?php if ($this->showFilters) : ?>
                <div class="em-w-100 em-mb-16">
                        <?php echo $this->nav;?>
                </div>
                <?php endif; ?>

	            <?php endif; ?>

				<?php foreach ($this->pluginBeforeList as $c) :
					echo $c;
				endforeach;
				?>
	            <?php if ($this->showFilters) : ?>
                <div class="mod_emundus_campaign__list_items_tabs" id="list_<?php echo $this->table->renderid; ?>">
		            <?php
		            foreach ($this->rows as $groupedBy => $group)
		            {

			            foreach ($group as $this->_row)
			            {
				            echo $this->loadTemplate('tabs');
			            }
		            }
		            ?>
                </div>
	            <?php endif; ?>
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

<script>

  window.addEventListener('DOMContentLoaded', (event) => {
	  let selected_view = sessionStorage.getItem('catalogue___selected_view');
	  if (selected_view !== null) {
		  this.switchView(selected_view);
	  }
  });

    function switchView(view) {
        sessionStorage.setItem("catalogue___selected_view", view);
        switch (view){
            case 'list':
                document.getElementById("catalogue_container").classList.add("tabs");
                document.getElementById("catalogue_container").classList.remove("cards");
                document.getElementById("fabrik_switch_view_list_icon").classList.add("active");
                document.getElementById("fabrik_switch_view_grid_icon").classList.remove("active");
                break;
            case 'grid':
                document.getElementById("catalogue_container").classList.remove("tabs");
                document.getElementById("catalogue_container").classList.add("cards");
                document.getElementById("fabrik_switch_view_list_icon").classList.remove("active");
                document.getElementById("fabrik_switch_view_grid_icon").classList.add("active");
                break;

        }
    }

    if(screen.width < 768) {
        document.getElementById("catalogue_container").classList.remove("tabs");
        document.getElementById("catalogue_container").classList.add("cards");
        document.getElementById("fabrik_switch_view_list_icon").classList.remove("active");
        document.getElementById("fabrik_switch_view_list_icon").classList.add("hidden");
        document.getElementById("fabrik_switch_view_grid_icon").classList.add("active");
    }
</script>
