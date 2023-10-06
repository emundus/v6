<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Note. It is important to remove spaces between elements.
?>
<ul class="<?php echo $class_sfx;?>"<?php
	$tag = '';
	if ($params->get('tag_id')!=NULL) {
		$tag = $params->get('tag_id').'';
		echo ' id="'.$tag.'"';
	}
?>>
<?php
foreach ($list as $i => &$item) :
	$item->anchor_css="item";
	$class = 'item-'.$item->id;
	if ($item->id == $active_id) {
		$class .= ' current';
	}

	if (in_array($item->id, $path)) {
		$class .= ' active';
	}
	elseif ($item->type == 'alias') {
		$aliasToId = $item->params->get('aliasoptions');
		if (count($path) > 0 && $aliasToId == $path[count($path)-1]) {
			$class .= ' active';
		}
		elseif (in_array($aliasToId, $path)) {
			$class .= ' alias-parent-active';
		}
	}

	if ($item->deeper) {
		$class .= ' deeper';
	}

	if ($item->parent) {
		$class .= ' parent';
	}

	if (!empty($class)) {
		$class = ' class="'.trim($class) .'"';
	}

	echo '<li'.$class.'>';

	// Render the menu item.
	switch ($item->type) :
		case 'separator':
		case 'url':
		case 'component':
			require JModuleHelper::getLayoutPath('mod_emundusmenu', 'default_'.$item->type);
			break;

		default:
			require JModuleHelper::getLayoutPath('mod_emundusmenu', 'default_url');
			break;
	endswitch;

	// The next item is deeper.
	if ($item->deeper) {
		echo '<div class="dropdown ';
		if(($item->level+1)==3){
			echo 'flyout';
		}
		echo'"><div class="column"><ul class="level'.($item->level+1).'">';
	}
	// The next item is shallower.
	elseif ($item->shallower) {
		echo '</li>';
		echo str_repeat('</ul></div></div></li>', $item->level_diff);
	}
	// The next item is on the same level.
	else {
		echo '</li>';
	}
endforeach;
?></ul>


<script>
    // get profile color
    let url = window.location.origin+'/index.php?option=com_emundus&controller=users&task=getprofilecolor';
    fetch(url, {
    method: 'GET',
    }).then((response) => {
    if (response.ok) {
    return response.json();
    }
    throw new Error(Joomla.JText._('COM_EMUNDUS_ERROR_OCCURED'));
    }).then((result) => {
    if(result.status) {

    let profile_color = result.data[0].class;
    let profile_state = result.data[0].published;

    let label_colors = {
    'lightpurple' : '--em-purple-1',
    'purple' : '--em-purple-2',
    'darkpurple' : '--em-purple-2',
    'lightblue' : '--em-light-blue-1',
    'blue' : '--em-blue-2',
    'darkblue' : '--em-blue-3',
    'lightgreen' : '--em-green-1',
    'green' : '--em-green-2',
    'darkgreen' : '--em-green-2',
    'lightyellow' : '--em-yellow-1',
    'yellow' : '--em-yellow-2',
    'darkyellow' : '--em-yellow-2',
    'lightorange' : '--em-orange-1',
    'orange' : '--em-orange-2',
    'darkorange' : '--em-orange-2',
    'lightred' : '--em-red-1',
    'red' : '--em-red-2',
    'darkred' : '--em-red-2',
    'lightpink' : '--em-pink-1',
    'pink' : '--em-pink-2',
    'darkpink' : '--em-pink-2',
    };

    if(profile_state == 1) { // it's an applicant profile

    console.log('candidat');

    let root = document.querySelector(':root');
    let css_var = getComputedStyle(root).getPropertyValue("--em-primary-color");

    document.documentElement.style.setProperty("--em-profile-color", css_var);

    } else {    // it's a coordinator profile

    console.log('gestionnaire');

    if(profile_color != '') {

    profile_color = profile_color.split('-')[1];

    if(label_colors[profile_color] != undefined) {
    let root = document.querySelector(':root');
    let css_var = getComputedStyle(root).getPropertyValue(label_colors[profile_color]);

    document.documentElement.style.setProperty("--em-profile-color", css_var);
    }
    }
    }
    }
    });
</script>