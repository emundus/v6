<?php
/**
 * @package     FOF
 * @copyright Copyright (c)2010-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Utils\FEFHelper;

use FOF30\Container\Container;

abstract class Html
{
	/**
	 * Helper function to create Javascript code required for table ordering
	 *
	 * @param	string	$order	Current order
	 *
	 * @return string	Javascript to add to the page
	 */
	public static function jsOrderingBackend($order)
	{
		$escapedOrder = addslashes($order);
		$js = <<< JS

;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
// due to missing trailing semicolon and/or newline in their code.
Joomla.orderTable = function () {
	var table = document.getElementById("sortTable");
	var direction = document.getElementById("directionTable");
	var order = table.options[table.selectedIndex].value;
	if (order != '$escapedOrder')
	{
		var dirn = 'asc';
	}
	else
	{
		var dirn = direction.options[direction.selectedIndex].value;
	}
	Joomla.tableOrdering(order, dirn, '');
}

JS;
		return $js;
	}

	/**
	 * Creates the required HTML code for backend pagination and sorting
	 *
	 * @param	\JPagination	$pagination	Pagination object
	 * @param 	array			$sortFields	Fields allowed to be sorted
	 * @param 	string			$order		Ordering field
	 * @param 	string			$order_Dir	Ordering direction (ASC, DESC)
	 *
	 * @return string
	 */
	public static function selectOrderingBackend($pagination, $sortFields, $order, $order_Dir)
	{
		$searchLimit	= \JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');
		$orderingDescr 	= \JText::_('JFIELD_ORDERING_DESC');
		$orderingAsc	= \JText::_('JGLOBAL_ORDER_ASCENDING');
		$orderingDesc	= \JText::_('JGLOBAL_ORDER_DESCENDING');
		$sortBy			= \JText::_('JGLOBAL_SORT_BY');
		$sortOptions	= \JHtml::_('select.options', $sortFields, 'value', 'text', $order);

		$ascSelected	= ($order_Dir == 'asc') ? 'selected="selected"' : "";
		$descSelected	= ($order_Dir == 'desc') ? 'selected="selected"' : "";

		$html = <<<HTML
		<div class="akeeba-filter-bar akeeba-filter-bar--right">
			<div class="akeeba-filter-element akeeba-form-group">
				<label for="limit" class="element-invisible">
					{$searchLimit}
				</label>
				{$pagination->getLimitBox()}
			</div>

			<div class="akeeba-filter-element akeeba-form-group">
				<label for="directionTable" class="element-invisible">
					{$orderingDescr}
				</label>
				<select name="directionTable" id="directionTable" class="input-medium custom-select" onchange="Joomla.orderTable()">
					<option value="">
						{$orderingDescr}
					</option>
					<option value="asc" {$ascSelected}>
						{$orderingAsc}
					</option>
					<option value="desc" {$descSelected}>
						{$orderingDesc}
					</option>
				</select>
			</div>

			<div class="akeeba-filter-element akeeba-form-group">
				<label for="sortTable" class="element-invisible">
					{$sortBy}
				</label>
				<select name="sortTable" id="sortTable" class="input-medium custom-select" onchange="Joomla.orderTable()">
					<option value="">
						{$sortBy}
					</option>
					{$sortOptions}
				</select>
			</div>
		</div>
HTML;

		return $html;
	}
}