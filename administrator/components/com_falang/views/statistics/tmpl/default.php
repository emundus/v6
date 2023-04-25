<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

?>
<script language="javascript" type="text/javascript">
  function updateResultDiv( resultInfo, type ) {
	resultDiv = document.getElementById("statistic_results");
	if( type == 'div' ) {
		resultDiv.innerHTML = resultInfo.innerHTML;
	} else {
		resultDiv.innerHTML = resultInfo;
	}
  }

</script>
<form action="index.php" method="post" name="adminForm">
<table class="adminform">
	<tr>
		<td width="55%" valign="top">
			<div id="cpanel">
				<?php
				$link = 'index.php?option=com_falang&amp;task=statistics.check&amp;type=translation_status';
				$this->_quickiconButton( $link, 'icon-48-checktranslations.png', JText::_('Check Translation Status'), '/administrator/components/com_falang/assets/images/', 'ajaxFrame', "updateResultDiv('" .JText::_('Processing'). "', 'text');" );
				$link = 'index.php?option=com_falang&amp;task=statistics.check&amp;type=original_status';
				$this->_quickiconButton( $link, 'icon-48-checktranslations.png', JText::_('Check Original Status'), '/administrator/components/com_falang/assets/images/', 'ajaxFrame', "updateResultDiv('" .JText::_('Processing'). "', 'text');" );
				?>
			</div>
		</td>
		<td width="45%" valign="top">
			<div style="width: 98%; height: 100%;">
				<h3><?php echo JText::_('Statistics info');?></h3>
				<div id="statistic_results"><?php echo JText::_('STATISTICS_INTRO');?></div>
			</div>
			<iframe style="display: none;" id="ajaxFrame" name="ajaxFrame" ></iframe>
		</td>
	</tr>
</table>

<input type="hidden" name="option" value="com_falang" />
<input type="hidden" name="task" value="statistics.overview" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="<?php echo JSession::getFormToken(); ?>" value="1" />
</form>
