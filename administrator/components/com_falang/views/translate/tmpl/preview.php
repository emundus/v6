<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

global $mainframe;
// JURI::base() returns admin path so go up one level
$live_site = JURI::base()."..";
$base = '<base href="'.$live_site.'/index.html" />';
JFactory::getDocument()->addCustomTag($base);

$editor		= JFactory::getEditor();

?>
	<script>

	var form = window.parent.document.adminForm	;
	var title = form.refField_title.value;
	var title_orig = form.origText_title.value;

	var alltext="";
	var alltext_orig = window.parent.document.getElementById("original_value_introtext").innerHTML;

	if (window.parent.getRefField){
		alltext = window.parent.getRefField("introtext");
		if (window.parent.getRefField("fulltext")) {
			alltext += window.parent.getRefField("fulltext");
		}
		else if (form.refField_fulltext) {
			alltext += form.refField_fulltext.value;
		}
	}
	else {
		alltext = window.top.<?php echo $editor->getContent('refField_introtext') ?>;
		alltext += window.top.<?php echo $editor->getContent('refField_fulltext') ?>;
	}
	alltext_orig += window.parent.document.getElementById("original_value_fulltext").innerHTML;

	</script>
<table align="center" width="100%" cellspacing="2" cellpadding="2" border="0">
	<tr>
		<th ><h2><?php echo JText::_("Original");?></h2></th>
		<th ><h2><?php echo JText::_("Translation");?></h2></th>
	</tr>
	<tr>
		<td class="contentheading" style="width:50%!important"><script>document.write(title_orig);</script></td>
		<td class="contentheading" ><script>document.write(title);</script></td>
	</tr>
	<tr>
		<script>document.write("<td valign=\"top\" >" + alltext_orig + "</td>");</script>
		<script>document.write("<td valign=\"top\" >" + alltext + "</td>");</script>
	</tr>
	<tr>
		<td align="center" colspan="2"><a href="javascript:;" onClick="window.print(); return false"><?php echo JText::_("Print");?></a></td>
	</tr>
</table>
