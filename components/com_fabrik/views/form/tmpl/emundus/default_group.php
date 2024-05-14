<?php
/**
 * Bootstrap Form Template - Group
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$rowStarted      = false;
$layout          = FabrikHelperHTML::getLayout('form.fabrik-control-group');
$gridStartLayout = FabrikHelperHTML::getLayout('grid.fabrik-grid-start');
$gridEndLayout   = FabrikHelperHTML::getLayout('grid.fabrik-grid-end');
$model     = $this->getModel();
$element_ids = $model->getElementIds();

foreach ($this->elements as $element) :
    $element->element_fabrik_id = $element_ids[$this->index_element_id];
    $this->index_element_id++;
	$this->element = $element;
	$this->class = 'fabrikErrorMessage';

    // Don't display hidden element's as otherwise they wreck multi-column layouts
	if (trim($element->error) !== '') :
		$element->error = $element->error;
		$element->containerClass .= ' error';
		$this->class .= ' help-inline text-danger';
	endif;

	if ($element->startRow) :
		echo $gridStartLayout->render(new stdClass);
		$rowStarted = true;
	endif;

	$style = $element->hidden ? 'style="display:none"' : '';
	$span  = $element->hidden ? '' : ' ' . $element->span;

	$displayData = array(
		'class' => $element->containerClass,
		'style' => $style,
		'span' => $span
	);

	$labelsAbove = $element->labels;

	if ($labelsAbove == 1)
	{
		$displayData['row'] = $this->loadTemplate('group_labels_above');
	}
	elseif ($labelsAbove == 2)
	{
		$displayData['row'] = $this->loadTemplate('group_labels_none');
	}
	elseif ($element->span == FabrikHelperHTML::getGridSpan(12) || $element->span == '' || $labelsAbove == 0)
	{
		$displayData['row'] = $this->loadTemplate('group_labels_side');
	}
	else
	{
		// Multi columns - best to use simplified layout with labels above field
		$displayData['row'] = $this->loadTemplate('group_labels_above');
	}

    $eMConfig = JComponentHelper::getParams('com_emundus');
    $allow_applicant_to_comment = $eMConfig->get('allow_applicant_to_comment', 0);
     if ($allow_applicant_to_comment) {
        ?>
        <div class="fabrik-element-emundus-container flex flex-row justify-items-start items-start">
            <div <?= $style ?>>
                <span class="material-icons-outlined cursor-pointer comment-icon" data-target-type="element" data-target-id="<?= $element->element_fabrik_id ?>">comment</span>
            </div>
            <?= $layout->render((object) $displayData); ?>
        </div>
        <?php
    } else {
       echo $layout->render((object) $displayData);
    }
    ?>
    <?php
	if ($element->endRow) :
		echo $gridEndLayout->render(new stdClass);
		$rowStarted = false;
	endif;
endforeach;

// If the last element was not closing the row add an additional div
if ($rowStarted === true) :
	echo $gridEndLayout->render(new stdClass);
endif;
