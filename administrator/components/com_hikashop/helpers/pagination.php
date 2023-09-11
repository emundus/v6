<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
jimport('joomla.html.pagination');

class hikashopBridgePaginationHelper extends JPagination {
	var $hikaSuffix = '';
	var $form = '';

	function getPagesLinks() {
		$app = JFactory::getApplication();

		$lang = JFactory::getLanguage();
		$lang->load('lib_joomla');

		$data = $this->_buildDataObject();

		$list = array();
		$itemOverride = false;
		$listOverride = false;

		$chromePath = JPATH_THEMES.DS.$app->getTemplate().DS.'html'.DS.'pagination.php';
		if(file_exists($chromePath)) {
			require_once ($chromePath);
			if(function_exists('pagination_list_render')) {
				$listOverride = true;
				if(HIKASHOP_J30 && hikashop_isClient('administrator'))
					$itemOverride = true;
			}
		}

		if ($data->all->base !== null) {
			$list['all']['active'] = true;
			$list['all']['data'] = ($itemOverride) ? pagination_item_active($data->all) : $this->_item_active($data->all);
		} else {
			$list['all']['active'] = false;
			$list['all']['data'] = ($itemOverride) ? pagination_item_inactive($data->all) : $this->_item_inactive($data->all);
		}
		$data->start->start = true;
		if ($data->start->base !== null) {
			$list['start']['active'] = true;
			$list['start']['data'] = ($itemOverride) ? pagination_item_active($data->start) : $this->_item_active($data->start);
			$list['start']['base'] = $data->start->base;
		} else {
			$list['start']['active'] = false;
			$list['start']['data'] = ($itemOverride) ? pagination_item_inactive($data->start) : $this->_item_inactive($data->start);
		}
		$data->previous->previous = true;
		if ($data->previous->base !== null) {
			$list['previous']['active'] = true;
			$list['previous']['data'] = ($itemOverride) ? pagination_item_active($data->previous) : $this->_item_active($data->previous);
			$list['previous']['base'] = $data->previous->base;
		} else {
			$list['previous']['active'] = false;
			$list['previous']['data'] = ($itemOverride) ? pagination_item_inactive($data->previous) : $this->_item_inactive($data->previous);
		}

		$list['pages'] = array(); //make sure it exists
		foreach ($data->pages as $i => $page) {
			if ($page->base !== null) {
				$list['pages'][$i]['active'] = true;
				$list['pages'][$i]['data'] = ($itemOverride) ? pagination_item_active($page) : $this->_item_active($page);
			} else {
				$list['pages'][$i]['active'] = false;
				$list['pages'][$i]['data'] = ($itemOverride) ? pagination_item_inactive($page) : $this->_item_inactive($page);
			}
		}
		$data->next->next = true;
		if ($data->next->base !== null) {
			$list['next']['active'] = true;
			$list['next']['data'] = ($itemOverride) ? pagination_item_active($data->next) : $this->_item_active($data->next);
			$list['next']['base'] = $data->next->base;
		} else {
			$list['next']['active'] = false;
			$list['next']['data'] = ($itemOverride) ? pagination_item_inactive($data->next) : $this->_item_inactive($data->next);
		}
		$data->end->end = true;
		if ($data->end->base !== null) {
			$list['end']['active'] = true;
			$list['end']['data'] = ($itemOverride) ? pagination_item_active($data->end) : $this->_item_active($data->end);
			$list['end']['base'] = $data->end->base;
		} else {
			$list['end']['active'] = false;
			$list['end']['data'] = ($itemOverride) ? pagination_item_inactive($data->end) : $this->_item_inactive($data->end);
		}

		if($this->total > $this->limit)
			return ($listOverride) ? pagination_list_render($list) : $this->_list_render($list);
		return '';
	}

	function _list_render($list){
		if (HIKASHOP_J40) {
			$html = '<ul class="pagination hikashop_pagination">';
			$start_class = '';
			if (!$list['start']['active'])
			$start_class = 'disabled ';
			$html .= '<li class="'.$start_class.'page-item">';
			if(isset($list['start']['base']))
			$html .= "<a href=\"".$this->_link($list['start']['base'])."\" class=\"pagenav_previous_chevron page-link\">".
				'<span class="fas fa-angle-double-left" aria-hidden="true"></span>'.
			'</a>';
			else
			$html .= '<span class="page-link" aria-hidden="true">'.
				'<span class="fas fa-angle-double-left" aria-hidden="true"></span>'.
			'</span>';
			$html .= '</li>';

			$prev_class = '';
			if (!$list['previous']['active'])
				$prev_class = 'disabled ';

			$html .= '<li class="'.$prev_class.'page-item">';
			if(isset($list['previous']['base']))
				$html .= "<a href=\"".$this->_link($list['previous']['base'])."\" class=\"pagenav_previous_chevron page-link\">".
					'<span class="fas fa-angle-left" aria-hidden="true"></span>'.
				'</a>';
			else
				$html .= '<span class="page-link" aria-hidden="true">'.
					'<span class="fas fa-angle-left" aria-hidden="true"></span>'.
				'</span>';
			$html .= '</li>';

			foreach( $list['pages'] as $page ) {
				$page_class = '';
				if (!$page['active'])
					$page_class = 'active ';

				$html .= '<li class="'.$page_class.'page-item">';
				$html .= $page['data'];
				$html .= '</li>';
			}

			$next_class = '';
			if (!$list['next']['active'])
				$next_class = 'disabled ';
			$html .= '<li class="'.$next_class.'page-item">';
			if(isset($list['next']['base']))
				$html .= "<a href=\"".$this->_link($list['next']['base'])."\" class=\"pagenav_next_chevron page-link\">".
					'<span class="fas fa-angle-right" aria-hidden="true"></span>'.
				'</a>';
			else
				$html .= '<span class="page-link" aria-hidden="true">'.
					'<span class="fas fa-angle-right" aria-hidden="true"></span>'.
				'</span>';
			$html .= '</li>';

			$end_class = '';
			if (!$list['end']['active'])
				$end_class = 'disabled ';

			$html .= '<li class="'.$end_class.'page-item">';
			if(isset($list['end']['base']))
				$html .= "<a href=\"".$this->_link($list['end']['base'])."\" class=\"pagenav_end_chevron page-link\">".
					'<span class="fas fa-angle-double-right" aria-hidden="true"></span>'.
				'</a>';
			else
				$html .= '<span class="page-link" aria-hidden="true">'.
					'<span class="fas fa-angle-double-right" aria-hidden="true"></span>'.
				'</span>';
			$html .= '</li>
				</ul>';
		}
		else {
			$html = null;
			if(isset($list['start']['base']))
				$html .= "<a href=\"".$this->_link($list['start']['base'])."\" class=\"pagenav_start_chevron\"> &lt;&lt; </a>";
			else
				$html .= '<span class="pagenav_start_chevron">&lt;&lt; </span>';

			$html .= $list['start']['data'];

			if(isset($list['previous']['base']))
				$html .= "<a href=\"".$this->_link($list['previous']['base'])."\" class=\"pagenav_previous_chevron\"> &lt; </a>";
			else
				$html .= '<span class="pagenav_previous_chevron"> &lt; </span>';

			$html .= $list['previous']['data'];

			foreach( $list['pages'] as $page ) {
				$html .= ' '.$page['data'];
			}

			$html .= ' '. $list['next']['data'];

			if(isset($list['next']['base']))
				$html .= "<a href=\"".$this->_link($list['next']['base'])."\" class=\"pagenav_next_chevron\"> &gt; </a>";
			else
				$html .= '<span class="pagenav_next_chevron"> &gt;</span>';

			$html .= ' '. $list['end']['data'];

			if(isset($list['end']['base']))
				$html .= "<a href=\"".$this->_link($list['end']['base'])."\" class=\"pagenav_end_chevron\"> &gt;&gt; </a>";
			else
				$html .= '<span class="pagenav_end_chevron"> &gt;&gt;</span>';
		}
		return $html;
	}

	function addMetaLinks() {
		$data = $this->_buildDataObject();
		$doc = JFactory::getDocument();
		$attribs = array();
		if ($data->previous->base !== null) {
			$href = $this->_link($data->previous->base);
			$doc->addHeadLink( $href, 'prev', 'rel', $attribs );
		}
		if ($data->next->base !== null) {
			$href = $this->_link($data->next->base);
			$doc->addHeadLink( $href, 'next', 'rel', $attribs );
		}
	}

	function _link($start){
		$current_url = hikashop_currentURL();
		$ret_start = false;
		$ret_limit = false;
		if(isset($_GET['limitstart'.$this->hikaSuffix])){
			$ret_start = true;
			$old_start = hikaInput::get()->getInt('limitstart'.$this->hikaSuffix);
			$current_url = preg_replace('#limitstart'.$this->hikaSuffix.'(=|-)[0-9]+#','limitstart'.$this->hikaSuffix.'${1}'.$start, $current_url);
		}

		if(isset($_POST['limit'.$this->hikaSuffix]) || isset($_GET['limit'.$this->hikaSuffix])){
			if(isset($_GET['limit'.$this->hikaSuffix])) $ret_limit = true;
			$old_limit = hikaInput::get()->getInt('limit'.$this->hikaSuffix);
			$current_url = preg_replace('#limit'.$this->hikaSuffix.'(=|-)[0-9]+#','limit'.$this->hikaSuffix.'${1}'.$this->limit, $current_url);
		}

		if($ret_start && $ret_limit)
			return $current_url;
		elseif($ret_start && !$ret_limit)
			return $current_url . '&limit='.$this->limit;

		$sep = '?';
		if(strpos($current_url, '?'))
			$sep = '&';
		return $current_url.$sep.'limitstart'.$this->hikaSuffix.'='.$start.'&limit='.$this->limit;
	}

	function _list_footer($list) {
		$html = '<div class="list-footer pagination pagination-toolbar clearfix">'."\n";
		$display = JText::_('JGLOBAL_DISPLAY_NUM');

		$html .= "\n<div class=\"limit\">".$display.$list['limitfield']."</div>";
		$html .= $list['pageslinks'];
		$html .= "\n<div class=\"counter\">".$list['pagescounter']."</div>";

		$html .= "\n<input type=\"hidden\" name=\"limitstart".$this->hikaSuffix."\" value=\"".$list['limitstart']."\" />";
		$html .= "\n</div>";

		return $html;
	}

	function getListFooter($minimum = -1) {
		$limit = $minimum;
		if($limit == -1)
			$limit = !empty($this->limit) ? $this->limit : 20;

		$list = array(
			'limit'	=> $limit,
			'limitstart' => $this->limitstart,
			'total' => $this->total,
			'limitfield' => $this->getLimitBox($minimum),
			'pagescounter' => $this->getPagesCounter(),
			'pageslinks' => $this->getPagesLinks(),
		);

		if(HIKASHOP_J30) {
			if(empty($this->prefix))
				$this->prefix = '';
			$list['prefix'] = $this->prefix;
			if(function_exists('pagination_list_footer')) {
				$ret = pagination_list_footer($list);
				if(strpos($ret, $list['limitfield']) === false) {
					$display = JText::_('JGLOBAL_DISPLAY_NUM');
					$ret = "\n<div class=\"limit\">".$display.$list['limitfield'] ."</div>" . $ret;
				}
				if(strpos($ret, 'name="limitstart'.$this->hikaSuffix.'"') === false)
					$ret .= "<input type=\"hidden\" name=\"limitstart".$this->hikaSuffix."\" value=\"".$list['limitstart']."\" />";
				if(strpos($ret, 'class="list-footer"') === false) {
					$ret = '<div class="list-footer">'."\n".$ret."\n</div>";
				}
				return $ret;
			}
		}
		return $this->_list_footer($list);
	}

	function getLimitBox($minimum = -1) {
		$limits = array ();
		if($minimum == -1) {
			$app = JFactory::getApplication();
			$minimum = $app->getCfg('list_limit');
		}
		for ($i = $minimum; $i <= $minimum*5; $i += $minimum) {
			$limits[] = JHTML::_('select.option', $i);
		}

		$config = hikashop_config();
		if($config->get('pagination_viewall', 1))
			$limits[] = JHTML::_('select.option', '0', JText::_('HIKA_ALL'));

		if(!HIKASHOP_J30){
			$viewall = $this->_viewall;
		} else {
			$viewall = @$this->viewall;
		}

		return JHTML::_('select.genericlist',  $limits, 'limit'.$this->hikaSuffix, 'class="chzn-done '.HK_FORM_SELECT_CLASS.' hikashop_pagination_dropdown" size="1" onchange="this.form.submit()"', 'value', 'text', $viewall ? 0 : $this->limit);
	}

	function HK_item_active($item) {
		if(function_exists('pagination_item_active')) {
			$link = $item->link;
			$item->link = 'hikashop_pagination_link';
			$html = pagination_item_active($item);

			$b = (int)$item->base;
			if($b <= 0) $b = 0;

			$html = str_replace(
				array('hikashop_pagination_link'),
				array($this->_link($b)),
				$html
			);
			return $html;
		}

		$class = 'pagenav page-link';
		$specials = array('start','end','previous','next');
		foreach($specials as $special) {
			if(!empty($item->$special)) {
				$class.=' hikashop_'.$special.'_link';
			}
		}
		if($item->base > 0)
			return "<a href=\"".$this->_link($item->base)."\" class=\"".$class."\" title=\"".$item->text."\">".$item->text."</a>";
		return "<a href=\"".$this->_link('0')."\" class=\"".$class."\" title=\"".$item->text."\">".$item->text."</a>";
	}

	function HK_item_inactive($item) {
		if(function_exists('pagination_item_inactive'))
			return pagination_item_inactive($item);

		$app = JFactory::getApplication();
		if (hikashop_isClient('administrator'))
			return '<span class="page-link">'.$item->text.'</span>';

		$class = 'pagenav page-link';
		if(!is_numeric($item->text)){
			$class .= ' pagenav_text';
		}
		return '<span class="'.$class.'">'.$item->text."</span>";
	}
}

$jversion = preg_replace('#[^0-9\.]#i','',JVERSION);
if(version_compare($jversion,'3.8.0','>=')) {
	class hikashopPaginationHelper extends hikashopBridgePaginationHelper {
		function _item_active(Joomla\CMS\Pagination\PaginationObject $item) {
			return parent::HK_item_active($item);
		}

		function _item_inactive(Joomla\CMS\Pagination\PaginationObject $item) {
			return parent::HK_item_inactive($item);
		}
	}
} else if(HIKASHOP_J30) {
	class hikashopPaginationHelper extends hikashopBridgePaginationHelper {
		function _item_active(JPaginationObject $item) {
			return parent::HK_item_active($item);
		}

		function _item_inactive(JPaginationObject $item) {
			return parent::HK_item_inactive($item);
		}
	}
} else {
	class hikashopPaginationHelper extends hikashopBridgePaginationHelper {
		function _item_active(&$item) {
			$class = 'pagenav';
			$specials = array('start','end','previous','next');
			foreach($specials as $special) {
				if(!empty($item->$special)) {
					$class.=' hikashop_'.$special.'_link';
				}
			}
			if($item->base>0)
				return "<a href=\"".$this->_link($item->base)."\" class=\"".$class."\" title=\"".$item->text."\" >".$item->text."</a>";
			return "<a href=\"".$this->_link('0')."\" class=\"".$class."\" title=\"".$item->text."\" >".$item->text."</a>";
		}

		function _item_inactive(&$item) {
			$app = JFactory::getApplication();
			if (hikashop_isClient('administrator'))
				return "<span>".$item->text."</span>";

			$class = 'pagenav';
			if(!is_numeric($item->text)){
				$class .= ' pagenav_text';
			}
			return '<span class="'.$class.'">'.$item->text."</span>";
		}
	}
}
