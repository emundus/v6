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

//filter for doctoral_school
$doctoral_school = $input->get('doctoral_school',$this->state->get('filter.doctoral_school'));

$ed = @EmundusHelperFiles::getElements(array('csc'), array(4359));
$query_params = json_decode($ed[0]->element_attribs);
$option_list =  @EmundusHelperFiles::buildOptions($ed[0]->element_name, $query_params);
$options[] = JHTML::_('select.option', "", JText::_('COM_EMUNDUS_PLEASE_SELECT'));
$i=0;
if(!empty($option_list))
{
    foreach($option_list as $value)
    {
        $options[] = JHTML::_('select.option', $value->elt_key, $value->elt_val);
        $i++;
    }
}

?>

<fieldset id="filter-bar">
	<div class="filter-search fltlft">
		<?php if ($filters) : ?>
            <?php
            echo JText::_('COM_EMUNDUS_SEARCH_BY_KEYWORDS');
            ?>
            <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_EMUNDUS_THESIS_SEARCH'); ?>" />
            <span class="glyphicon glyphicon-chevron-right"></span>
            <?php
            echo JText::_('COM_EMUNDUS_SEARCH_BY_THESIS_ED') . " " . JHTML::_('select.genericlist', $options, 'doctoral_school', 'class="inputbox"', 'value', 'text', $doctoral_school);
            ?>
            <button type="submit" class="btn btn-primary btn-xs"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button type="button" class="btn btn-warning btn-xs" onclick="document.id('filter_search').value = '';document.id('doctoral_school').value = ''; this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>

		<?php endif; ?>
	</div>

</fieldset>
