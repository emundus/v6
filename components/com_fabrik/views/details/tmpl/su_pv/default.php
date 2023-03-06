<?php
/**
 * Bootstrap Details Template
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

echo "<style>
	* {
		font-family: 'Calibri, Sans-Serif';
	}
	header {
        position: fixed;
        top: 0cm;
        left: 0cm;
        right: 0cm;
		height: 1.5cm;
	}
	body {
		margin-top: 2cm;
	}
</style>";

$form = $this->form;
$model = $this->getModel();

if ($this->params->get('show_page_heading', 1)) : ?>
    <div class="componentheading<?= $this->params->get('pageclass_sfx')?>">
		<?= $this->escape($this->params->get('page_heading')); ?>
    </div>
<?php
endif;

$logo_module = JModuleHelper::getModuleById('90');
preg_match('#src="(.*?)"#i', $logo_module->content, $tab);
$logo = JPATH_BASE.DS.$tab[1];

if ($this->params->get('show-title', 1)) :?>
<header>
    <img src="https://www.emundus.fr/images/logo-sorbonne.png" height="100%"/>
    <h3 style="color: #395c9b; float:right;"><?php $title = explode(' - ', $form->label); echo !empty($title[1])?JText::_(trim($title[1])):JText::_(trim($title[0])); ?></h3>
</header>
<body>
<?php
endif;

echo $form->intro;
echo '<div class="fabrikForm fabrikDetails '.(($this->isMambot)?'fabrikIsMambot':'').' id="' . $form->formid . '">';

echo $this->plugintop;
echo $this->loadTemplate('buttons');
echo $this->loadTemplate('relateddata');

foreach ($this->groups as $group) :
	$this->group = $group;
	?>

    <div class="<?= $group->class; ?>" id="group<?= $group->id;?>" style="<?= $group->css;?>">

		<?php
		if ($group->showLegend && $group->id !== '697' && $group->id !== '717') :?>
            <h3 class="legend">
                <span><?= $group->title;?></span>
            </h3>
		<?php endif;

		if (!empty($group->intro)) : ?>
            <div class="groupintro"><?= $group->intro ?></div>
		<?php
		endif;

		// Load the group template - this can be :
		//  * default_group.php - standard group non-repeating rendered as an unordered list
		//  * default_repeatgroup.php - repeat group rendered as an unordered list
		//  * default_repeatgroup_table.php - repeat group rendered in a table.

		$this->elements = $group->elements;

		if ($group->id === '696' || $group->id === '716') :?>

            <h5><?= $this->elements['campaign_id']->value[0]; ?></h5>
            <div>
				<?= $this->elements['group_id']->label_raw; ?> <u><?= $this->elements['group_id']->value[0]; ?></u> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<?= $this->elements['date_time']->label_raw; ?> <u><?= date('d/m/Y' , strtotime($this->elements['date_time']->value)); ?></u>
            </div>

		<?php elseif ($group->id === '697' || $group->id === '717') :?>
            <br>
            <br>
            <u><?= $group->title; ?></u>
            <table>
                <tr role="row">
                    <th><?= $this->elements['name']->label_raw; ?></th><th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th><th><?= $this->elements['present']->label_raw; ?></th>
                </tr>
				<?php foreach ($group->subgroups as $sgroup) :?>
                    <tr role="row"><td><?= $sgroup['name']->value; ?></td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td> <?= ($sgroup['present']->value[0] === '1')? 'oui' : 'non'; ?></td></tr>
				<?php endforeach; ?>
            </table>
		<?php elseif ($group->id === '698' || $group->id === '718') :?>
            <br>
            <br>
            <br>
            <table width="100%">
                <u><?= $this->elements['decision']->label_raw; ?></u>
                <br>
				<?= $this->elements['decision']->value; ?>
            </table>

		<?php else :?>
			<?= $this->loadTemplate($group->tmpl); ?>
		<?php endif;

		if (!empty($group->outro)) : ?>
            <div class="groupoutro"><?= $group->outro ?></div>
		<?php
		endif;
		?>
    </div>
<?php
endforeach;

echo $this->pluginbottom;
echo $this->loadTemplate('actions');
echo '</div>';
echo $form->outro;
echo $this->pluginend;
?>
</body>
