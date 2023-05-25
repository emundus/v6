<?php
/**
 * Bootstrap Form Template
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$form = $this->form;
$model = $this->getModel();
$groupTmpl = $model->editable ? 'group' : 'group_details';
$active = ($form->error != '') ? '' : ' fabrikHide';

$pageClass = $this->params->get('pageclass_sfx', '');

if ($pageClass !== '') :
	echo '<div class="' . $pageClass . '">';
endif;

if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>">
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</div>
<?php
endif;
?>
<div class="emundus-form">
<?php if ($this->params->get('show-title', 1)) :?>
<div class="page-header">
    <?php $title = trim(preg_replace('/^([^-]+ - )/', '', $form->label));?>
    <h1><?= JText::_($title) ?></h1>
</div>
<?php endif; ?>

    <div class="em-form-intro">
<?php
echo $form->intro;
?>
    </div>
<form method="post" <?php echo $form->attribs?>>
<?php
echo $this->plugintop;
?>

<div class="fabrikMainError alert alert-error fabrikError<?php echo $active?>">
	<button class="close" data-dismiss="alert">×</button>
	<?php echo $form->error; ?>
</div>

<div class="row-fluid nav">
	<div class="<?php echo FabrikHelperHTML::getGridSpan(6); ?> pull-right">
		<?php
		echo $this->loadTemplate('buttons');
		?>
	</div>
	<div class="<?php echo FabrikHelperHTML::getGridSpan(6); ?>">
		<?php
		echo $this->loadTemplate('relateddata');
		?>
	</div>
</div>

<?php
foreach ($this->groups as $group) :
	$this->group = $group;
	?>

	<fieldset class="<?php echo $group->class; ?> <?php if($group->columns > 1) { echo 'fabrikGroupColumns-'. $group->columns.' fabrikGroupColumns'; } ?>" id="group<?php echo $group->id;?>" style="<?php echo $group->css;?>">
		<?php
		if ($group->showLegend) :?>
			<span class="legend"><?php echo $group->title;?></span>
		<?php
		endif;

		if (!empty($group->intro)) : ?>
			<div class="groupintro"><?php echo $group->intro ?></div>
		<?php
		endif;

		/* Load the group template - this can be :
		 *  * default_group.php - standard group non-repeating rendered as an unordered list
		 *  * default_repeatgroup.php - repeat group rendered as an unordered list
		 *  * default_repeatgroup_table.php - repeat group rendered in a table.
		 */
		$this->elements = $group->elements;
		echo $this->loadTemplate($group->tmpl);

		if (!empty($group->outro)) : ?>
			<div class="groupoutro"><?php echo $group->outro ?></div>
		<?php
		endif;
	?>
	</fieldset>
<?php
endforeach;
if ($model->editable) : ?>
<div class="fabrikHiddenFields">
	<?php echo $this->hiddenFields; ?>
</div>
<?php
endif;

echo $this->pluginbottom;
echo $this->loadTemplate('actions');
?>
</form>
<?php
echo $form->outro;
echo $this->pluginend;
echo FabrikHelperHTML::keepalive();

if ($pageClass !== '') :
	echo '</div>';
endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Set sidebar sticky depends on height of header
        const headerNav = document.getElementById('g-navigation');
        const sidebar = document.querySelector('.view-form #g-sidebar');
        if (headerNav && sidebar) {
            document.querySelector('.view-form #g-sidebar').style.top = headerNav.offsetHeight + 'px';
        }

        // Remove applicant-form class if needed
        const applicantFormClass = document.querySelector('div.applicant-form');
        if(applicantFormClass){
            applicantFormClass.classList.remove('applicant-form');
        }

        // Load skeleton
        let header = document.querySelector('.page-header');
        if(header) {
            document.querySelector('.page-header h1').style.opacity = 0;
            header.classList.add('skeleton');
        }
        let intro = document.querySelector('.em-form-intro');
        if(intro) {
            let content = document.querySelector('.em-form-intro').children;
            if(content.length > 0) {
                for (const child of content) {
                    child.style.opacity = 0;
                }
            }
            intro.classList.add('skeleton');
        }
        let grouptitle = document.querySelectorAll('.fabrikGroup .legend');
        for (title of grouptitle){
            title.style.opacity = 0;
        }
        let groupintro = document.querySelector('.groupintro');
        if (groupintro) {
            groupintro.style.opacity = 0;
        }

        let elements = document.querySelectorAll('.fabrikGroup .row-fluid');
        let elements_fields = document.querySelectorAll('.fabrikElementContainer');
        for (field of elements_fields){
            field.style.opacity = 0;
        }
        for (elt of elements){
            let elt_container = elt.querySelector('.fabrikElementContainer');
            if(elt_container !== null && !elt_container.classList.contains('fabrikHide')) {
                elt.style.marginTop = '24px';
            }
            elt.classList.add('skeleton');
        }
    });
</script>
