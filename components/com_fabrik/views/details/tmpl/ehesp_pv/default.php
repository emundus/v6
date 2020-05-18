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


$form = $this->form;
$model = $this->getModel();
?>

    <div id="footdompdf">
        <span class="footleft"><?php echo $this->table->label;?></span>
        <span class="pagenum">Page </span>
    </div>
    <div id="headerdompdf" class="em-headerdompdf">
        <img style="width: 75px;" src="https://ehesp.emundus.io/images/custom/ehesp_4c_zone_reserve_HD_petit.png" height="110" width="110"/>
        <div class="em-headerdompdf-title">
            <h1><?= $form->label; ?></h1>
            <span><?= $this->data["jos_emundus_pv___campaign_id"];?></span>
            <span class="second-span">Commission du <div class="commission-date"><?= $this->data["jos_emundus_pv___date_commission"];?></div></span>
        </div>
    </div>
<?php
echo $this->plugintop;
echo $this->loadTemplate('buttons');
echo $this->loadTemplate('relateddata');

foreach ($this->groups as $group) :
    $this->group = $group;
    ?>

    <div class="<?php echo $group->class; ?>" id="group<?php echo $group->id;?>" style="<?php echo $group->css;?> ">

        <?php
        if ($group->showLegend) :?>
            <h3 class="legend">
                <span style="font-family: 'Signika', sans-serif;"><?php echo $group->title;?></span>
            </h3>
        <?php endif;

        if (!empty($group->intro)) : ?>
            <div class="groupintro"><?php echo $group->intro ?></div>
        <?php
        endif;

        // Load the group template - this can be :
        //  * default_group.php - standard group non-repeating rendered as an unordered list
        //  * default_repeatgroup.php - repeat group rendered as an unordered list
        //  * default_repeatgroup_table.php - repeat group rendered in a table.

        $this->elements = $group->elements;
        echo $this->loadTemplate($group->tmpl);

        if (!empty($group->outro)) : ?>
            <div class="groupoutro"><?php echo $group->outro ?></div>
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
