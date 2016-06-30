<?php

defined('JPATH_BASE') or die;

$input = JFactory::getApplication()->input;
/*jimport('joomla.form.form');
JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
$form = JForm::getInstance('com_emundus.job', 'job');*/

$filters = true;
if (isset($data['view']->filterForm))
{
	$filters = $this->filterForm->getGroup('filter');
}

//Filter for the field etablissement
//$selected_etablissement = $input->get('filter_etablissement',$this->state->get('filter.etablissement'));
//echo $form->getInput('filter_etablissement', null, $selected_etablissement);

//filter for domain
$selected_domaine = $input->get('filter_domaine',$this->state->get('filter.domaine'));
$domaines = @EmundusHelperFiles::getElementsValuesOther(2262);
$values = $domaines->sub_values;
$labels = $domaines->sub_labels;
$options = array();
$options[] = JHTML::_('select.option', "", JText::_('PLEASE_SELECT'));
$i=0;
foreach($labels as $key=>$value) :
    $options[] = JHTML::_('select.option', $values[$i], $value);
    $i++;
endforeach;
?>

<fieldset id="filter-bar">
	<div class="filter-search fltlft">
		<?php if ($filters) : ?>
            <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_EMUNDUS_SEARCH_FILTER_SUBMIT'); ?>" />
            <span class="glyphicon glyphicon-chevron-right"></span>
            <?php
            echo JText::_('COM_EMUNDUS_JOBS_DOMAINE') . " " . JHTML::_('select.genericlist', $options, 'filter_domaine', 'class="inputbox"', 'value', 'text', $selected_domaine);
            ?>
            <button type="submit" class="btn btn-primary btn-xs"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button type="button" class="btn btn-warning btn-xs" onclick="document.id('filter_search').value = ''; this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>

		<?php endif; ?>
	</div>

</fieldset>