<?php
defined('_JEXEC') or die;
$doc = JFactory::getDocument();
?>

<ol class="breadcrumb<?php echo $moduleclass_sfx; ?>">
<?php if ($params->get('showHere', 1))
	{
		echo '<li><span class="showHere">' .JText::_('MOD_BREADCRUMBS_HERE').'</span></li>';
	}
?>
<?php for ($i = 0; $i < $count; $i ++) :
	// Workaround for duplicate Home when using multilanguage
	if ($i == 1 && !empty($list[$i]->link) && !empty($list[$i - 1]->link) && $list[$i]->link == $list[$i - 1]->link) {
		continue;
	}
	// If not the last item in the breadcrumbs add the separator
	if ($i < $count - 1)
	{
		if (!empty($list[$i]->link)) {
			echo '<li><a href="'.$list[$i]->link.'" class="pathway">'.$list[$i]->name.'</a></li>';
		} else {
			echo '<li><span>';
			echo $list[$i]->name;
			echo '</span></li>';
		}
		if ($i < $count - 2)
		{
			//echo ' '.$separator.' ';
		}
	}  elseif ($params->get('showLast', 1)) { // when $i == $count -1 and 'showLast' is true
		if ($i > 0)
		{
			//echo ' '.$separator.' ';
		}
		echo '<li><span>';
		echo $list[$i]->name;
		echo '</span></li>';
	}
endfor; 
if(!empty($separator)){
			$sty = '.breadcrumb > li + li:before
{

  content: "'. $separator.'";
}
';
 $doc->addStyleDeclaration($sty);
			}
?>
</ol>