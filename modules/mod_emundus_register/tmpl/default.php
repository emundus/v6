<?php
defined('_JEXEC') or die;

echo '
<div class="dropdown clearfix">
   <ul role="menu" aria-labelledby="dropdownMenu" style="display:block;position:static;margin-left:5px;">
	  ' . JText::_('ETAPES') . '
	  <li><a tabindex="-1" class="btn btn-info" href ="' . $urlresult . $result['code'] . '">' . JText::_('MOD_EM_REGISTER') . '</a></li>
    </ul>
  </div>';
?>


