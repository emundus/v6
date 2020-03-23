<?php
defined('_JEXEC') or die;
$anchor_css = $item->anchor_css ? $item->anchor_css : '';
$class = rtrim($class,'"');
$class = str_replace($class, $class.' '.$anchor_css.'"',$class); 
$app	= JFactory::getApplication();

$title = $item->anchor_title ? 'title="'.$item->anchor_title.'" ' : '';
if ($item->menu_image) {
		$item->params->get('menu_text', 1 ) ?
		$linktype = '<img src="'.$item->menu_image.'" alt="'.$item->title.'" /><span class="image-title">'.$item->title.'</span> ' :
		$linktype = '<img src="'.$item->menu_image.'" alt="'.$item->title.'" />';
}
else { $linktype = $item->title;
}

if(isset($newparams)){

$m_pos = $app->getTemplate(true)->params->get( $newparams->position . 'ms');

?>
<?php
switch ($item->browserNav) :
	default:
	case 0:
 ?>
<a <?php echo $class; ?> href="<?php echo $item->flink; ?>" <?php if (strpos($class, 'dropdown-toggle') !== false){echo'data-toggle="dropdown"';} ?> <?php echo $title; ?>><span class="menuchildicon"></span><?php echo $linktype; ?><?php if((!$justifychk && $m_pos == 'h_menu')  || $m_pos == 'v_menu') {echo '</a>';}

// else{ echo '</a>';}
      
      break;
	case 1:
		// _blank
?><a <?php echo $class; ?> href="<?php echo $item->flink; ?>" target="_blank" <?php if (strpos($class, 'dropdown-toggle') !== false){echo'data-toggle="dropdown"';} ?>  <?php echo $title; ?>><span class="menuchildicon"></span><?php echo $linktype; ?><?php if((!$justifychk && $m_pos == 'h_menu') || $m_pos == 'v_menu'){echo '</a>';}

// else{ echo '</a>';}
		break;
	case 2:
	// window.open
?><a <?php echo $class; ?> href="<?php echo $item->flink; ?>" onclick="window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes');return false;" <?php if (strpos($class, 'dropdown-toggle') !== false){echo'data-toggle="dropdown"';} ?> <?php echo $title; ?>><span class="menuchildicon"></span><?php echo $linktype; ?>
<?php if((!$justifychk && $m_pos == 'h_menu')  || $m_pos == 'v_menu'){echo '</a>';}

// else{ echo '</a>';}
		break;
endswitch;
}
else{
	$attributes = array();

if ($item->anchor_title)
{
	$attributes['title'] = $item->anchor_title;
}

if ($item->anchor_css)
{
	$attributes['class'] = $item->anchor_css;
}

if ($item->anchor_rel)
{
	$attributes['rel'] = $item->anchor_rel;
}

$linktype = $item->title;

if ($item->menu_image)
{
	if ($item->menu_image_css)
	{
		$image_attributes['class'] = $item->menu_image_css;
		$linktype = JHtml::_('image', $item->menu_image, $item->title, $image_attributes);
	}
	else
	{
		$linktype = JHtml::_('image', $item->menu_image, $item->title);
	}

	if ($item->params->get('menu_text', 1))
	{
		$linktype .= '<span class="image-title">' . $item->title . '</span>';
	}
}

if ($item->browserNav == 1)
{
	$attributes['target'] = '_blank';
}
elseif ($item->browserNav == 2)
{
	$options = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes';

	$attributes['onclick'] = "window.open(this.href, 'targetWindow', '" . $options . "'); return false;";
}

echo JHtml::_('link', JFilterOutput::ampReplace(htmlspecialchars($item->flink, ENT_COMPAT, 'UTF-8', false)), $linktype, $attributes);

}
 ?>