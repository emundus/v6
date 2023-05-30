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

$pageClass = $this->params->get('pageclass_sfx', '');

if ($pageClass !== '') :
	echo '<div class="' . $pageClass . '">';
endif;

if ($this->tablePicker != '') : ?>
	<div style="text-align:right"><?php echo FText::_('COM_FABRIK_LIST') ?>: <?php echo $this->tablePicker; ?></div>
<?php
endif;

if ($this->params->get('show_page_heading')) :
	echo '<h1>' . $this->params->get('page_heading') . '</h1>';
endif;
?>
<form class="fabrikForm form-search" action="<?php echo $this->table->action;?>" method="post" id="<?php echo $this->formid;?>" name="fabrikList">

<?php
if ($this->hasButtons):
	echo $this->loadTemplate('buttons');
endif;
?>
<div class="filter-data-align">
    <?php
if ($this->showFilters && $this->bootShowFilters) :
	echo $this->layoutFilters();
endif;
//for some really ODD reason loading the headings template inside the group
//template causes an error as $this->_path['template'] doesn't contain the correct
// path to this template - go figure!
$headingsHtml = $this->loadTemplate('headings');
$notes = $this->params->get('note', '');
if(!empty($notes)){
    $notes = explode(',',$notes);
}
$showTitle = $this->params->get('show-title');
echo $this->loadTemplate('tabs');
?>

<div class="fabrikDataContainer em-w-100">

<?php foreach ($this->pluginBeforeList as $c) :
	echo $c;
endforeach;
?>

    <?php if ($showTitle == 1) : ?>
    <div class="page-header">
        <div class="em-flex-row em-flex-space-between">
            <h2><?php echo $this->table->label;?></h2>
            <?php if(!in_array('list_only', $notes) && !in_array('grid_only', $notes)) : ?>
                <div class="em-flex-row em-gap-8">
                    <span onclick="switchView('grid')" class="em-pointer material-icons-outlined fabrik-switch-view-icon" id="fabrik_switch_view_grid_icon">grid_view</span>
                    <span onclick="switchView('list')" class="em-pointer material-icons-outlined fabrik-switch-view-icon" id="fabrik_switch_view_list_icon">menu</span>
                </div>
            <?php endif; ?>
        </div>
        <div class="em-list-intro">
	        <?php echo $this->table->intro; ?>
        </div>
    </div>
    <?php endif; ?>
    <div class="em-mt-8">

        <?php if ($showTitle == 0) : ?>
        <div class="em-flex-row em-mb-12 em-pointer em-w-max-content" onclick="history.go(-1)">
            <span style="border-radius: 8px !important;" class="material-icons-outlined em-repeat-card-no-padding">chevron_left</span>
        </div>
        <?php endif; ?>


        <?php echo $this->nav;?>
    </div>
    <div class="em-grid-3-2-1 <?php echo $this->list->class;?>" id="list_<?php echo $this->table->renderid;?>" >
        <?php if(empty($this->rows) || empty($this->rows[0])) : ?>
            <div class="emptyDataMessage" style="<?php echo $this->emptyStyle?>">
		        <?php echo $this->emptyDataMessage; ?>
            </div>
        <?php endif; ?>
        <?php
        $gCounter = 0;
        foreach ($this->rows as $groupedBy => $group) :
        if ($this->isGrouped) : ?>
            <div>
                <?php echo $this->layoutGroupHeading($groupedBy, $group); ?>
            </div>
        <?php endif ?>
        <?php
        foreach ($group as $this->_row) :
	        echo $this->loadTemplate('row');
        endforeach;
	        $gCounter++;
        endforeach;

        if ($this->hasCalculations) : ?>
			<div>
				<div class="fabrik_calculations">

				<?php
				foreach ($this->headings as $key => $heading) :
					$h = $this->headingClass[$key];
					$style = empty($h['style']) ? '' : 'style="' . $h['style'] . '"';?>
					<div class="<?php echo $h['class']?>" <?php echo $style?>>
						<?php
						$cal = $this->calculations[$key];
						echo array_key_exists($groupedBy, $cal->grouped) ? $cal->grouped[$groupedBy] : $cal->calc;
						?>
					</div>
				<?php
				endforeach;
				?>

				</div>
			</div>
        <?php endif ?>
    </div>
	<?php print_r($this->hiddenFields);?>
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
    document.addEventListener('DOMContentLoaded', () => {
        let view_mode = localStorage.getItem('view_mode');

        <?php if(in_array('list_only', $notes)) : ?>
        view_mode = 'list';
        <?php elseif(in_array('grid_only', $notes)) : ?>
        view_mode = 'grid';
        <?php endif; ?>

        console.log(view_mode);
        // Check view mode
        if(view_mode === null){
            localStorage.setItem('view_mode', 'grid');
            view_mode = 'grid';
        }

        switchView(view_mode);

        // Load skeleton
        let header = document.querySelector('.page-header');
        if (header) {
            document.querySelector('.page-header h2').style.opacity = 0;
            document.querySelector('.page-header .em-list-intro').style.opacity = 0;
            header.classList.add('skeleton');
        }

        let filters = document.querySelector('.filtertable');
        if (filters){
            filters.style.opacity = 0;
            document.querySelector('.fabrikFiltersBlock').classList.add('skeleton');
        }

        let submit_button = document.querySelector('.fabrik_filter_submit');
        if(submit_button){
            submit_button.style.opacity = 0;
            document.querySelector('#fabrikFiltersButtonSubmit').classList.add('skeleton');
        }

        let nav = document.querySelector('.fabrikNav');
        if(nav){
            document.querySelector('.fabrikNav div').style.opacity = 0;
            nav.classList.add('skeleton');
        }

        let cards = document.querySelectorAll('.fabrik_row');
        let elts_p = document.querySelectorAll('.fabrik_row p');
        let elts_div = document.querySelectorAll('.fabrik_row div');
        for (elt_div of elts_div) {
            elt_div.style.opacity = 0;
        }
        for (elt_p of elts_p) {
            elt_p.style.opacity = 0;
        }
        for (card of cards) {
            card.classList.add('skeleton');
        }
    });

    function switchView(view){
        localStorage.setItem('view_mode', view);
        let list = document.getElementById('list_<?php echo $this->table->renderid;?>');

        switch (view){
            case 'grid':
                <?php if(!in_array('list_only', $notes) && !in_array('grid_only', $notes)) : ?>
                    document.getElementById('fabrik_switch_view_grid_icon').classList.add('active');
                    document.getElementById('fabrik_switch_view_list_icon').classList.remove('active');
                <?php endif; ?>
                updateStyleOfClass('fabrikImageBackground', 'display', 'block');
                list.classList.remove('em-grid-1');
                list.classList.add('em-grid-3-2-1');
                break;
            case 'list':
                <?php if(!in_array('list_only', $notes) && !in_array('grid_only', $notes)) : ?>
                    document.getElementById('fabrik_switch_view_grid_icon').classList.remove('active');
                    document.getElementById('fabrik_switch_view_list_icon').classList.add('active');
                <?php endif; ?>
                updateStyleOfClass('fabrikImageBackground', 'display', 'none');
                list.classList.remove('em-grid-3-2-1');
                list.classList.add('em-grid-1');
                break;
        }
    }

    function updateStyleOfClass(className, style, value) {
        var elements = document.querySelectorAll('.'+className);
        elements.forEach(element => {
            element.style[style] = value;
        });
    }
</script>
