<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikamarket::completeLink('config'); ?>" method="post" name="adminForm" id="adminForm">
	<table id="hikamarket_acl_list" class="adminlist pad0 table table-striped table-hover">
		<thead>
			<tr>
				<th class="hikamarket_acl_name_title title" style="min-width:200px;"><?php echo JText::_('HIKA_NAME'); ?></th>
<?php
	foreach($this->groups as $group) {
?>
				<th class="hikamarket_acl_group_<?php echo $group->id; ?>_title title titletoggle"><?php echo $group->title; ?></th>
<?php
	}
?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo count($this->groups) + 1; ?>">
				</td>
			</tr>
		</tfoot>
		<tbody>
<?php
$k = 0;
function hikamarket_draw_acl_line($acls, $full_key, $tree, &$t, &$k, &$aclData, $depth = 0, $visible = true) {
	foreach($acls as $key => $value) {
		$name = (is_array($value)) ? $key : $value;
		$isParent = (is_array($value));
		$styles = '';
		$trJs = '';
		$my_key = trim($full_key . '/' . $name, '/');

		if(!$visible) $styles .= 'display:none;';
		if($isParent) {
			$styles .= 'cursor:pointer;';
			$trJs = 'window.localPage.expand(\''.$my_key.'\');';
		}

		$aclData[$my_key] = array();

?>
			<tr class="row<?php echo $k; ?>" data-acl="<?php echo $name; ?>" data-parent="<?php echo $full_key; ?>"<?php echo $isParent?' data-folder="1"':''; ?> style="<?php echo $styles;?>">
				<td onclick="<?php echo $trJs; ?>">
<?php
		echo str_repeat('<img src="'.HIKAMARKET_IMAGES.'otree/line.gif" style="vertical-align:middle;" alt="" />', $depth);
		if($isParent) {
			echo '<img src="'.HIKAMARKET_IMAGES.'otree/plus.gif" style="vertical-align:middle;" alt="" />';
			echo '<img src="'.HIKAMARKET_IMAGES.'otree/folder.gif" style="vertical-align:middle;" alt="" />';
		} else {
			echo '<img src="'.HIKAMARKET_IMAGES.'otree/join.gif" style="vertical-align:middle;" alt="" />';
			echo '<img src="'.HIKAMARKET_IMAGES.'otree/page.gif" style="vertical-align:middle;" alt="" />';
		}
?>
					<?php echo $name; ?>
				</td>
<?php
		foreach($t->groups as $key => $group) {
			$v = 0;
			if(in_array($my_key, $t->aclConfig[$group->id]))
				$v = 12;
			else if(in_array('!'.$my_key, $t->aclConfig[$group->id]))
				$v = 11;

			if($v == 0) {
				$parentKeys = explode('/', $full_key);
				while(!empty($parentKeys) && $v == 0) {
					$n = implode('/', $parentKeys);
					if(isset($aclData[$n]['g'.$group->id]) && $aclData[$n]['g'.$group->id] > 10)
						$v = $aclData[$n]['g'.$group->id] - 10;
					array_pop($parentKeys);
				}
			}
			if($v == 0) {
				for($l = $key - 1; $l >= 0 && $v == 0; $l--) {
					$g = $t->groups[$l];
					if((int)$g->lft < (int)$group->lft && (int)$g->rgt > (int)$group->rgt && isset($aclData[$my_key]['g'.$g->id]) && $aclData[$my_key]['g'.$g->id] > 10)
						$v = $aclData[$my_key]['g'.$g->id] - 10;
				}
			}
			if($v == 0 && !empty($full_key)) {
				$v = $aclData[$full_key]['g'.$group->id];
			}

			$aclData[$my_key]['g'.$group->id] = $v;

			$class = array();
			if($v > 10)
				$class[] = ' set';
			if($v == 1 || $v == 11)
				$class[] = ' unpublished';
			if($v == 2 || $v == 12)
				$class[] = ' published';

			if(!empty($class))
				$class = ' class="'.implode(' ', $class).'"';
			else
				$class = '';
?>
				<td data-group="<?php echo $group->id; ?>" onclick="localPage.process(this);"<?php echo $class; ?>><span class="acl-icon"></span></td>
<?php
		}
?>
			</tr>
<?php
		$k = 1 - $k;

		if($isParent) {
			hikamarket_draw_acl_line($value, $my_key, $tree, $t, $k, $aclData, $depth + 1, ($depth+1 < 2));
		}
	}
}
	$aclData = array();
	hikamarket_draw_acl_line($this->acls, '', null, $this, $k, $aclData);
?>
		</tbody>
	</table>
<script type="text/javascript">
window.hikashop.ready(function(){
	window.hikashop.cleanTableRows('hikamarket_acl_list');
});
if(!window.localPage)
	window.localPage = {};
window.localPage.aclGroups = {
<?php
	foreach($this->groups as $k => $group) {
		$children = array();
		foreach($this->groups as $g) {
			if($g->lft > $group->lft && $g->rgt < $group->rgt) {
				$children[] = $g->id;
			}
		}
		if($k > 0)
			echo ','."\r\n";
		echo "\t" . $group->id . ':[' . implode(',', $children) . ']';
	}
	echo "\r\n";
?>
};
window.localPage.aclGroupsOrder = [<?php
	$g = array();
	foreach($this->groups as $group) {
		$g[] = $group->id;
	}
	echo implode(',', $g);
?>];
window.localPage.aclData = <?php echo json_encode($aclData); ?>;
window.localPage.expand = function(name) {
	var d = document,
		k = 0, c = '', line = null, attr = null,
		l = name.length,
		tbl = d.getElementById('hikamarket_acl_list'),
		lines = tbl.getElementsByTagName('tr');
	for(var i = 0; i < lines.length; i++) {
		line = lines[i];
		attr = line.getAttribute('data-parent');
		if(attr == name) {
			if( line.style.display == 'none')
				line.style.display = '';
			else
				line.style.display = 'none';
		} else if(attr && attr.substring(0, l) == name && line.style.display != 'none') {
			line.style.display = 'none';
		}
	}
	window.hikashop.cleanTableRows('hikamarket_acl_list');
}
window.localPage.process = function(el) {
	var d = document, o = window.Oby, t = this,
		parentGroup = [],
		status = 0,
		aclName = el.parentNode.getAttribute('data-acl'),
		aclParent = el.parentNode.getAttribute('data-parent'),
		group = parseInt(el.getAttribute('data-group'));

	if(aclParent && aclParent.length > 0)
		aclName = aclParent + '/' + aclName;
	status = t.getCell(aclName, group);

	if(status < 0)
		return;

	for(var i in t.aclGroups) {
		if(t.aclGroups[i].indexOf(group) >= 0) {
			parentGroup[parentGroup.length] = i;
		}
	}

	if(status < 10) {
		t.aclData[aclName]['g'+group] = 12;
		t.setAclValue(group, aclName, 1);
	} else if(status == 12) {
		t.aclData[aclName]['g'+group] = 11;
		t.setAclValue(group, aclName, 2);
	} else  {
		var s = 0, p = 0, n = null, parents = aclParent.split('/');

		while(parents.length > 0 && s == 0) {
			n = parents.join('/');
			p = t.getCell(n, group);
			if(p > 10) s = p - 10;
			parents.pop();
		}

		for(var i = parentGroup.length - 1; s == 0 && i >= 0; i--) {
			p = t.getCell(aclName, parentGroup[i]);
			if(p > 10) s = p - 10;
		}

		if(s === 0) {
			p = t.getCell(aclParent, group);
			if(p > 0) s = p;
		}

		t.aclData[aclName]['g'+group] = s;
		t.setAclValue(group, aclName, 3);
	}
	t.reprocess();
	t.display();
};
window.localPage.getCell = function(aclName, group) {
	var t = this, a = t.aclData[aclName];
	if(a) return a['g' + group];
	return -1;
};
window.localPage.setAclValue = function(group, aclName, mode) {
	var t = this, d = document,
		input = d.getElementById('aclinput_' + group),
		values = [], inv = '!' + aclName, f = 0;

	if(!input)
		return;
	if(input.value.length > 0)
		values = input.value.split(',');

	for(var i = 0; i < values.length; i++) {
		if(values[i] == aclName && mode == 1) return;
		if(values[i] == inv && mode == 2) return;

		if(values[i] == aclName && mode == 2)
			f++;
		if((values[i] == aclName || values[i] == inv) && mode == 3)
			f++;

		if(f > 0 && values[i+f] && values[i+f].length > 0)
			values[i] = values[i+f];
	}
	if(f) {
		for(var i = 0; i < f; i++)
			values.pop();
	}
	if(mode == 1) values[values.length] = aclName;
	if(mode == 2) values[values.length] = inv;
	input.value = values.join(',');
};
window.localPage.reprocess = function() {
	var t = this, data = t.aclData,
		l = null, p = null, v = null, g = 0, s = 0, w = 0;
	for(var n in data) {
		if(!data.hasOwnProperty(n))
			continue;
		l = data[n];
		for(var j = 0; j < t.aclGroupsOrder.length; j++) {
			g = t.aclGroupsOrder[j];
			v = l['g'+g] || 0;
			if(v > 10)
				continue;
			s = 0;
			p = n.split('/');
			while(p.length > 0 && s == 0) {
				w = data[p.join('/')]['g'+g];
				if(w > 10) s = w - 10;
				p.pop();
			}
			for(var i = (j-1); s === 0 && i >= 0; i--) {
				var k = t.aclGroupsOrder[i];
				if(t.aclGroups[k].indexOf(g) >= 0 && l['g'+k] > 0) { // was "> 10" before
					if(l['g'+k] > 10)
						s = l['g'+k] - 10;
					else
						s = l['g'+k];
				}
			}
			if(s === 0) {
				p = n.split('/');
				p.pop();
				if(p.length > 0)
					s = data[p.join('/')]['g'+g];
			}
			t.aclData[n]['g'+g] = s;
		}
	}
}
window.localPage.display = function() {
	var t = this, d = document, o = window.Oby,
		tbl = d.getElementById('hikamarket_acl_list'),
		lines = null, line = null, cells = null, aclName = '', g = null, v = null,
		tmp = tbl.firstChild;

	while(tmp && tmp.nodeName.toLowerCase() != 'tbody') {
		tmp = tmp.nextSibling;
	}
	if(!tmp) return;

	lines = tmp.childNodes;
	for(var i = 0; i < lines.length; i++) {
		line = lines[i];
		if(line && line.nodeName.toLowerCase() != 'tr')
			continue;

		cells = line.childNodes;

		aclName = line.getAttribute('data-acl');
		if(line.getAttribute('data-parent') && line.getAttribute('data-parent').length > 0)
			aclName = line.getAttribute('data-parent') + '/' + aclName;
		tmp = t.aclData[aclName];

		for(var k = 0, j = 0; k < cells.length; k++) {
			if(cells[k] && cells[k].nodeName.toLowerCase() != 'td')
				continue;
			j++;
			if(j == 1)
				continue;

			g = t.aclGroupsOrder[ j-2 ];
			v = tmp['g' + g];
			if(v > 10)
				o.addClass(cells[k], 'set');
			else
				o.removeClass(cells[k], 'set');

			if(v == 1 || (v - 10) == 1) {
				o.addClass(cells[k], 'unpublished');
				o.removeClass(cells[k], 'published');
			} else if(v == 2 || (v - 10) == 2) {
				o.addClass(cells[k], 'published');
				o.removeClass(cells[k], 'unpublished');
			} else {
				o.removeClass(cells[k], 'published');
				o.removeClass(cells[k], 'unpublished');
			}
		}
	}
};
</script>
	<div style="clear:both" class="clr"></div>
<?php
	foreach($this->groups as $group) {
		$config = '';
		if(!empty($this->aclConfig[$group->id]))
			$config = implode(',', $this->aclConfig[$group->id]);
		echo '<input type="hidden" id="aclinput_'.$group->id.'" name="data[acl][' . $group->id . ']" value="' . $config . '"/>' . "\r\n";
		unset($config);
	}
?>
	<input type="hidden" name="acl_type" value="<?php echo $this->acl_type; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
