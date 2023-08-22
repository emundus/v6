<?php
/**
 * eMundus Form Template
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$form      = $this->form;
$model     = $this->getModel();
$groupTmpl = $model->editable ? 'group' : 'group_details';
$active    = ($form->error != '') ? '' : ' fabrikHide';

$eMConfig = JComponentHelper::getParams('com_emundus');
$display_required_icon = $eMConfig->get('display_required_icon', 1);

$pageClass = $this->params->get('pageclass_sfx', '');

if ($pageClass !== '') :
	echo '<div class="' . $pageClass . '">';
endif;

if ($this->params->get('show_page_heading', 1)) : ?>
    <div class="componentheading<?php echo $this->params->get('pageclass_sfx') ?>">
		<?php echo $this->escape($this->params->get('page_heading')); ?>
    </div>
<?php
endif;
?>
<div class="emundus-form p-6">
    <div class="mb-0 fabrikMainError alert alert-error fabrikError<?php echo $active ?>">
        <span class="material-icons">cancel</span>
		<?php echo $form->error; ?>
    </div>
    <div class="mb-8">
        <div class="mt-8">
	        <?php if ($this->params->get('show-title', 1)) : ?>
                <?php if($display_required_icon == 0) : ?>
                    <p class="mb-2 text-neutral-600"><?= JText::_('COM_FABRIK_REQUIRED_ICON_NOT_DISPLAYED') ?></p>
                <?php endif; ?>
                <div class="page-header">
			        <?php $title = trim(preg_replace('/^([^-]+ - )/', '', $form->label)); ?>
                    <h2 class="after-em-border after:bg-red-800"><?= JText::_($title) ?></h2>
                </div>
	        <?php endif; ?>
        </div>


        <div class="em-form-intro mt-4">
            <?php
            echo trim($form->intro);
            ?>
        </div>
    </div>
    <form method="post" <?php echo $form->attribs ?>>
		<?php
		echo $this->plugintop;
		?>
        
        <?php
        $buttons_tmpl = $this->loadTemplate('buttons');
        $related_datas_tmpl = $this->loadTemplate('relateddata');
        ?>

        <?php if (!empty($buttons_tmpl) || !empty($related_datas_tmpl)) : ?>
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
        <?php endif; ?>

		<?php
		foreach ($this->groups as $group) :
			$this->group = $group;
			?>

            <fieldset class="mb-6 <?php echo $group->class; ?> <?php if ($group->columns > 1) {
				echo 'fabrikGroupColumns-' . $group->columns . ' fabrikGroupColumns';
			} ?>" id="group<?php echo $group->id; ?>" style="<?php echo $group->css; ?>">
                <?php if(($group->showLegend && !empty($group->title)) || !empty($group->intro)) : ?>
                <div class="mb-7">
                    <?php
                    if ($group->showLegend) :?>
                        <h3 class="after-em-border after:bg-neutral-500"><?php echo $group->title; ?></h3>
                    <?php
                    endif;

                    if (!empty($group->intro)) : ?>
                        <div class="groupintro mt-4"><?php echo $group->intro ?></div>
                    <?php endif; ?>

	                <?php if(!empty($group->maxRepeat) && $group->maxRepeat > 1) : ?>
                        <p class="em-text-neutral-600 mt-2"><?php echo JText::sprintf('COM_FABRIK_REPEAT_GROUP_MAX',$group->maxRepeat) ?></p>
	                <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php

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
        if (applicantFormClass) {
            applicantFormClass.classList.remove('applicant-form');
        }

        // Load skeleton
        let header = document.querySelector('.page-header');
        if (header) {
            document.querySelector('.page-header h1').style.opacity = 0;
            header.classList.add('skeleton');
        }
        let intro = document.querySelector('.em-form-intro');
        if (intro) {
            let content = document.querySelector('.em-form-intro').children;
            if (content.length > 0) {
                for (const child of content) {
                    child.style.opacity = 0;
                }
            }
            intro.classList.add('skeleton');
        }
        let grouptitle = document.querySelectorAll('.fabrikGroup .legend');
        for (title of grouptitle) {
            title.style.opacity = 0;
        }
        grouptitle = document.querySelectorAll('.fabrikGroup h2');
        for (title of grouptitle){
            title.style.opacity = 0;
        }
        let groupintro = document.querySelector('.groupintro');
        if (groupintro) {
            groupintro.style.opacity = 0;
        }

        let elements = document.querySelectorAll('.fabrikGroup .row-fluid');
        let elements_fields = document.querySelectorAll('.fabrikElementContainer');
        for (field of elements_fields) {
            field.style.opacity = 0;
        }
        for (elt of elements) {
            let elt_container = elt.querySelector('.fabrikElementContainer');
            if (elt_container !== null && !elt_container.classList.contains('fabrikHide')) {
                elt.style.marginTop = '24px';
            }
            elt.classList.add('skeleton');
        }
    });
</script>
