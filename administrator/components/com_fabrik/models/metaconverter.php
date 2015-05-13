<?php
/**
 * Fabrik Admin Group Model
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.5
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Fabrik Admin View Model.Handles storing a 'view' to a json file
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       3.5
 */

trait MetaConverter
{

	protected function getMetaItem($view)
	{
		$folder = JPATH_ADMINISTRATOR . '/components/com_fabcck/models/views/';

		if (!JFolder::exists($folder))
		{
			JFolder::create($folder);
		}

		$file = $folder . $view . '.json';

		if (!JFile::exists($file))
		{
			$output = $this->buildViewFromDb($pk);
			$output = json_encode($output, JSON_PRETTY_PRINT);
			JFile::write($file, $output);
		}

		$item = JFile::read($file);
		$item = json_decode($item);

		return $item;
	}
	protected function viewNameFromId($pk, $tbl)
	{
		$query = $this->db->getQuery(true);
		$query->select('label, view')->from($tbl)->where('id = ' . (int) $pk);
		$view = $this->db->setQuery($query)->loadObject();

		return $view->view == '' ? JStringNormalise::toUnderscoreSeparated($view->label) : $view->view;
	}

	protected function buildViewFromDb($pk)
	{
		$output = new stdClass;
		$query = $this->db->getQuery(true);
		$query->select('*')->from('#__fabrik_lists')->where('id = ' . (int) $pk);
		$row = $this->db->setQuery($query)->loadObject();
		$params = new JRegistry($row->params);
		echo "<pre>";print_r($row);
		print_r($params);
		$output->title = $output->name = JStringNormalise::toUnderscoreSeparated($row->label);
		$output->ucm = false;
		$output->history = false;
		$output->database = new stdClass;
		$this->joins($output, $params);

		$output->created = $row->created;
		$output->created_by = $row->created_by_alias;

		$this->access($output, $params, $row);

		$output->list = new stdClass;
		$output->list->intro = $row->introduction;
		$output->list->heading = $row->label;
		$output->list->publishing = new stdClass;
		$output->list->publishing->published = $row->published;
		$output->list->publishing->up = $row->publish_up;
		$output->list->publishing->down = $row->publish_down;
		$output->list->nav = new stdClass;
		$output->list->nav->limit = (int) $row->rows_per_page;
		$output->list->nav->show = (bool) $params->get('show-table-nav', true);
		$output->list->nav->show_displaynum = (bool) $params->get('show_displaynum', true);
		$output->list->nav->show_total = (bool) $params->get('show-total', true);
		$output->list->nav->show_all_option = (bool) $params->get('showall-records', false);
		$output->list->template = $row->template;

		$output->list->order = [];
		$by = json_decode($row->order_by);

		if (!empty($by))
		{
			$orderElements = $this->fieldFromElementId($by);
			$dirs = json_decode($row->order_dir);

			for ($i = 0; $i < count($by); $i ++)
			{
				$order = new stdClass;
				$order->by = $orderElements[$i];
				$order->dir = $dirs[$i];
				$output->list->order[] = $order;
			}
		}

		$this->filters($output, $row, $params);
		$this->prefilters($output, $params);

		$output->list->toggle_cols = (bool) $params->get('toggle_cols');
		$output->list->list_filter_cols = (int) $params->get('list_filter_cols', 1);
		$output->list->empty_data_msg = $params->get('empty_data_msg');
		$output->list->outro = $params->get('outro');
		$output->list->show_add = $params->get('show-table-add');
		$output->list->sef_slug = $this->fieldFromElementId($params->get('sef-slug'));
		$output->list->admin_template = $params->get('admin_template');
		$output->list->show_title = $params->get('show-title');

		$this->pdf($output, $params);
		$this->bootstrap($output, $params);
		$this->tabs($output, $params);

		$output->list->action_method = $params->get('actionMethod', 'default');
		$output->list->checkbox_locaiton = $params->get('checkboxLocation', 'end');
		$output->list->note = $params->get('note');

		$output->list->alter_existing_db_cols = (bool) $params->get('alter_existing_db_cols');
		$output->list->process_jplugins = (bool) $params->get('process_jplugins');
		$output->list->enable_single_sorting = (bool) $params->get('enable_single_sorting');
		$output->list->collation = $params->get('collation');
		$output->list->disable_caching = (bool) $params->get('disable_caching', false);
		$output->list->distinct = (bool) $params->get('distinct', true);
		$output->list->join_display = $params->get('join-display');
		$output->list->delete_joined_rows = $params->get('delete-joined-rows');
		$output->list->show_related_add = $params->get('show_related_add');
		$output->list->show_related_info = $params->get('show_related_info');
		$output->list->isview = (bool) $params->get('isview', false);
		$this->groupby($output, $params, $row);
		$this->urls($output, $params);
		$this->feeds($output, $params);
		$this->csv($output, $params);
		$this->jSearch($output, $params);
		// Load in form - $row->form_id;
		// Build database structur - $row->db_table_name
		// Assign conncetion $row->connection_id

		return $output;
	}

	private function access(&$output, $params, $row)
	{
		$output->access = new stdClass;
		$output->access->list = (int) $row->access;

		$output->access->details = (int) $params->get('allow_view_details');
		$output->access->edit  = (int) $params->get('allow_edit_details');
		$output->access->edit_field = (int) $params->get('allow_edit_details2');
		$output->access->add  = (int) $params->get('allow_add');
		$output->access->delete = (int) $params->get('allow_delete');
		$output->access->delete_field = (int) $params->get('allow_delete2');
		$output->access->drop  = (int) $params->get('allow_drop');
	}

	private function jSearch(&$output, $params)
	{
		$search = new stdClass;

		$search->use = (bool) $params->get('search_use');
		$search->title = $params->get('search_title');
		$search->description = $params->get('search_description');
		$search->date = $params->get('search_date');
		$search->link_type = $params->get('link_type');
		$output->list->jsearch = $search;
	}

	private function csv(&$output, $params)
	{
		$csv = new stdClass;
		$csv->import_frontend = (bool) $params->get('csv_import_frontend');
		$csv->export_frontend = (bool) $params->get('csv_export_frontend');
		$csv->fullname = $params->get('csvfullname');
		$csv->step = (int) $params->get('csv_export_step');
		$csv->nl = $params->get('newline_csv_export');
		$csv->custom_qs = $params->get('csv_custom_qs');
		$csv->frontend_selection = (bool) $params->get('csv_frontend_selection');
		$csv->incfilters = (bool) $params->get('incfilters');
		$csv->format = $params->get('csv_format');
		$csv->which_elements = $params->get('csv_which_elements');
		$csv->show_in_csv = $params->get('show_in_csv');
		$csv->elements = $params->get('csv_elements');
		$csv->include_data = (bool) $params->get('csv_include_data');
		$csv->include_raw_data = (bool) $params->get('csv_include_raw_data');
		$csv->include_calculations = (bool) $params->get('csv_include_calculations');
		$csv->encoding = $params->get('csv_encoding');
		$output->list->csv = $csv;
	}

	private function feeds(&$output, $params)
	{
		$output->list->rss = new stdClass;
		$output->list->rss->show = (bool) $params->get('rss');
		$output->list->rss->title = $params->get('feed_title');
		$output->list->rss->date = $params->get('feed_date');
		$output->list->rss->image = $params->get('feed_image_src');
		$output->list->rss->limit = (int) $params->get('rsslimit');
		$output->list->rss->limitmax = (int) $params->get('rsslimitmax');
	}

	private function joins(&$output, $params)
	{
		$output->database->joins = array();
		$types = $params->get('join_type');
		$from = $params->get('join_from_table');
		$to = $params->get('table_join');
		$fromKeys = $params->get('table_key');
		$toKeys = $params->get('table_join_key');
		$repeat = $params->get('join_repeat');

		for ($i = 0; $i < count($types); $i ++)
		{
			$join = new stdClass;
			$join->type = $types[$i];
			$join->from = $from[$i];
			$join->to = $to[$i];
			$fromKey = $this->db->qn($join->from . '.' . $fromKeys[$i]);
			$toKey = $this->db->qn($join->to . '.' . $toKeys[$i]);
			$join->on = $fromKey . ' = ' . $toKey;
			$join->repeat = (bool) $repeat[$i][0];
			$output->database->joins[] = $join;
		}

	}
	private function prefilters(&$output, $params)
	{
		$glue = $params->get('filter-join');
		$fields = $params->get('filter-fields');
		$conditions = $params->get('filter-conditions');
		$values = $params->get('filter-value');
		$eval = $params->get('filter-eval');
		$access = $params->get('filter-access');
		$grouped = $params->get('filter-grouped');

		$new = array();
		$groupedFilter = array();

		for ($i = 0; $i < count($grouped); $i++)
		{
			$thisFilter = new stdClass;
			$thisFilter->glue = $glue[$i];
			$thisFilter->where = $fields[$i];
			$thisFilter->condition = $conditions[$i];
			$thisFilter->value = $values[$i];
			$thisFilter->eval = $eval[$i];
			$thisFilter->access = $access[$i];

			$groupedFilter[] = $thisFilter;

			if ($grouped[$i] == 0)
			{
				$new[] = $groupedFilter;
				$groupedFilter = array();
			}
		}
		if (!empty($groupedFilter))
		{
			$new[] = $groupedFilter;
		}

		$output->list->prefilters = $new;
	}

	private function groupby(&$output, $params, $row)
	{
		$output->list->groupby = new stdClass;
		$output->list->groupby->field = $row->group_by;
		$output->list->groupby->access = (int) $params->get('group_by_access');
		$output->list->groupby->order = $params->get('group_by_order');
		$output->list->groupby->template = $params->get('group_by_template');
		$output->list->groupby->dir = $params->get('group_by_order_dir');
		$output->list->groupby->start_collapsed = (bool) $params->get('group_by_start_collapsed');
		$output->list->groupby->collapse_others = (bool) $params->get('group_by_collapse_others');
	}

	private function tabs(&$output, $params)
	{
		$tabs = new stdClass;
		$tabs->fields = $params->get('tabs_field');
		$tabs->max = (int) $params->get('tabs_max');
		$tabs->all = (bool) $params->get('tabs_all');
		$output->list->tabs = $tabs;
	}

	private function urls(&$output, $params)
	{
		$urls = new stdClass;
		$urls->detail = new stdClass;
		$urls->detail->url = $params->get('detailurl');
		$urls->detail->label = $params->get('detaillabel');
		$urls->edit = new stdClass;
		$urls->edit->url = $params->get('editurl');
		$urls->edit->label = $params->get('editlabel');
		$urls->add = new stdClass;
		$urls->add->url = $params->get('addurl');
		$urls->add->label = $params->get('addlabel');
		$output->list->urls = $urls;
	}
	private function filters(&$output, $row, $params)
	{
		$output->list->filters = new stdClass;
		$output->list->filters->action = $row->filter_action;
		$output->list->filters->show = (bool) $params->get('show-table-filters');
		$output->list->filters->advanced = (bool) $params->get('advanced-filter');

		$output->list->filters->searchall = new stdClass;
		$output->list->filters->searchall->label = $params->get('search-all-label');
		$output->list->filters->searchall->show = (bool) $params->get('search-mode');
		$output->list->filters->searchall->advanced = (bool) $params->get('search-mode-advanced');
		$output->list->filters->searchall->query = array();

		$searchFields = $params->get('list_search_elements', '');
		if (is_string($searchFields) && $searchFields != '')
		{
			$searchFields = json_decode($searchFields);

			if (isset($searchFields->search_elements))
			{
				$searchFields = $searchFields->search_elements;
				$searchFields = $this->fieldFromElementId($searchFields);

				foreach ($searchFields as $searchField)
				{
					$search = new stdClass;
					$search->type = 'LIKE';
					$search->fields = array();
					$field = new stdClass;
					$field->name = $searchField;
					$field->weight = 1;
					$search->fields[] = $field;
					$output->list->filters->searchall->query[] = $search;
				}
			}
		}

		$output->list->filters->filter = array();

		$output->list->filters->requried = (bool) $params->get('require-filter');
		$output->list->filters->dropdown_method = (int) $params->get('filter-dropdown-method');
	}

	private function pdf(&$output, $params)
	{
		$output->list->pdf = new stdClass;
		$output->list->pdf->show = (bool) $params->get('pdf');
		$output->list->pdf->template = $params->get('pdf_template');
		$output->list->pdf->orientation = $params->get('pdf_orientation');
		$output->list->pdf->size = $params->get('pdf_size', 'A4');
	}

	private function bootstrap(&$output, $params)
	{
		$output->list->bootstrap = new stdClass;
		$output->list->bootstrap->striped = (bool) $params->get('bootstrap_stripped_class');
		$output->list->bootstrap->bordered = (bool) $params->get('bootstrap_bordered_class');
		$output->list->bootstrap->condensed = (bool) $params->get('bootstrap_condensed_class');
		$output->list->bootstrap->hover = (bool) $params->get('bootstrap_hover_class');

		// @TODO work out mapping of responsive elements & class
		$output->list->bootstrap->responsive = array();
	}

	private function fieldFromElementId($id)
	{
		$id = (array) $id;
		if (empty($id))
		{
			return array();
		}
		$query = $this->db->getQuery(true);
		$query->select('name')->from('#__fabrik_elements')->where('id IN (' . implode(',', $id) . ')');

		return $this->db->setQuery($query)->loadColumn();
	}

}
