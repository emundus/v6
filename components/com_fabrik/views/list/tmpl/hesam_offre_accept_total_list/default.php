<?php
/**
 * Bootstrap List Template - Default
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
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

if ($this->showTitle == 1) : ?>
	<div class="page-header">
		<h1><?php echo $this->table->label;?></h1>
	</div>
<?php
endif;

// Intro outside of form to allow for other lists/forms to be injected.
echo $this->table->intro;

?>
<form class="fabrikForm form-search" action="<?php echo $this->table->action;?>" method="post" id="<?php echo $this->formid;?>" name="fabrikList">

<?php
if ($this->hasButtons):
	echo $this->loadTemplate('buttons');
endif;

if ($this->showFilters && $this->bootShowFilters) :
	echo $this->layoutFilters();
endif;
//for some really ODD reason loading the headings template inside the group
//template causes an error as $this->_path['template'] doesn't contain the correct
// path to this template - go figure!
$headingsHtml = $this->loadTemplate('headings');
echo $this->loadTemplate('tabs');
?>

<div class="fabrikDataContainer">

    <?php foreach ($this->pluginBeforeList as $c) {
        echo $c;
    }

    $headingTitle = [];
    $dataValues = [];
    $data = array();
    $i = 0;

    if (!empty($this->rows)) {
        foreach ($this->headings as $head => $value) {
            if($head != 'fabrik_select')
                $headingTitle[] = $value;
        }
        foreach ($this->rows as $k => $v) {
            foreach ($this->headings as $key => $val) {
                $raw = $key.'_raw';
                if (array_key_exists($raw, $v[0]->data)) {
                    $dataValues[] = $v[0]->data->$key;
                }
            }
            if (array_key_exists('fabrik_view_url', $v[0]->data)) {
                $data[$i]['fabrik_view_url'] = $v[0]->data->fabrik_view_url;
            }
            $i = $i + 1;

        }
    }
    ?>


    <div class="em-search-engine-data">
        <table class="dataTable">
        <?php
        $gCounter = 0;

        for($i = 0; $i < sizeof($headingTitle); $i++) :?>
            <tr class="row-<?php echo $i; ?>">
                <td class="row-<?php echo $i; ?>-title"><?php echo $headingTitle[$i]; ?></td>
                <td class="row-<?php echo $i; ?>-data"><?php echo $dataValues[$i]; ?></td>
            </tr>
        <?php endfor; ?>

        <tfoot>
        <tr class="fabrik___heading">
            <?php if (!empty($data)) :?>
                <td colspan="<?php echo count($this->headings);?>">
                    <?php echo $this->nav;?>
                </td>
            <?php endif; ?>
        </tr>
        </tfoot>


        <?php if ($this->hasCalculations) : ?>
            <tfoot>
            <tr class="fabrik_calculations">

                <?php foreach ($this->headings as $key => $heading) :
                    $h = $this->headingClass[$key];
                    $style = empty($h['style']) ? '' : 'style="' . $h['style'] . '"'; ?>
                    <td class="<?php echo $h['class']?>" <?php echo $style?>>
                        <?php
                        $cal = $this->calculations[$key];
                        echo array_key_exists($groupedBy, $cal->grouped) ? $cal->grouped[$groupedBy] : $cal->calc;
                        ?>
                    </td>
                <?php
                endforeach;
                ?>

            </tr>
            </tfoot>
        <?php endif ?>
        </table>
    </div>

    <?php print_r($this->hiddenFields);?>
</div>
</form>
<?php
echo $this->table->outro;
if ($pageClass !== '') :
	echo '</div>';
endif;
?>
