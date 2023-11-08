<?php

defined('JPATH_BASE') or die;

$input = JFactory::getApplication()->input;
$db    = JFactory::getDBO();

$eMConfig = JComponentHelper::getParams('com_emundus');
$note     = $eMConfig->get('category_note', 'emplois-su');

$filters = true;
if (isset($data['view']->filterForm)) {
	$filters = $this->filterForm->getGroup('filter');
}

//filter for etablissement
$selected_etablissement = $input->get('filter_domaine', $this->state->get('filter.etablissement'));
$query                  = 'SELECT id, title FROM #__categories WHERE published=1 and extension like "com_contact" and ( note like "%' . $note . '%") order by title asc';
$db->setQuery($query);
$elements = $db->loadObjectList();


$options_etablissement   = array();
$options_etablissement[] = JHTML::_('select.option', "", JText::_('COM_EMUNDUS_PLEASE_SELECT'));
$i                       = 0;

foreach ($elements as $key => $value) :
	if ($value->id != 118) {

		if ($value->id == $this->state->get('filter.etablissement')) {
			$options_etablissement[] = JHTML::_('select.option', $value->id, $value->title, true);
		}
		else {
			$options_etablissement[] = JHTML::_('select.option', $value->id, $value->title);
		}
	}
endforeach;


//filter for domain
$selected_domaine = $input->get('filter_etablissement', $this->state->get('filter.domaine'));
$domaines         = @EmundusHelperFiles::getElementsValuesOther(2262);
$values           = $domaines->sub_values;
$labels           = $domaines->sub_labels;
$options          = array();
$options[]        = JHTML::_('select.option', "", JText::_('COM_EMUNDUS_PLEASE_SELECT'));
$i                = 0;
foreach ($labels as $key => $value) :

	if ($values[$i] == $this->state->get('filter.domaine')) {
		$options[] = JHTML::_('select.option', $values[$i], $value, true);
	}
	else {
		$options[] = JHTML::_('select.option', $values[$i], $value);
	}
	$i++;
endforeach;
?>

<fieldset id="filter-bar">
    <div class="filter-search fltlft">
		<?php if ($filters) : ?>
        <div class="criteria">
			<?php
			echo JText::_('COM_EMUNDUS_JOBS_SEARCH') . " ";
			?>
            <input type="text" name="filter_search" id="filter_search"
                   value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                   title="<?php echo JText::_('COM_EMUNDUS_SEARCH_FILTER_SUBMIT'); ?>"/>
        </div>
        <div class="criteria">
			<?php
			echo JText::_('COM_EMUNDUS_JOBS_DOMAINE') . " " . JHTML::_('select.genericlist', $options, 'filter_domaine', 'class="inputbox"', 'value', 'text', $selected_domaine);
			?>
        </div>
        <div class="criteria">
			<?php
			echo JText::_('COM_EMUNDUS_JOBS_ETABLISSEMENT') . " " . JHTML::_('select.genericlist', $options_etablissement, 'filter_etablissement', 'class="inputbox"', 'value', 'text', $selected_etablissement);
			?>
        </div>
        <div class="criteria">
			<?php
			echo JText::_('COM_EMUNDUS_JOBS_SERVICE') . " ";
			?>
            <input type="text" name="filter_service" id="filter_service"
                   value="<?php echo $this->escape($this->state->get('filter.service')); ?>"/>
        </div>
    </div>
    <div class="action">
        <button type="submit" class="btn btn-primary btn-xs"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
        <button type="button" class="btn btn-warning btn-xs"
                onclick="document.id('filter_search').value = '';document.id('filter_service').value = ''; this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
    </div>
	<?php endif; ?>
    </div>

</fieldset>
