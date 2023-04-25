<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

if (!empty( $this->sidebar)): ?>
<div id="j-sidebar-container" class="span2">
    <?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>


<div id="jfhelp">
    <?php if (FALANG_J30 ) { ?>
    	<div class="row-fluid">
        <div class="span10">
    <?php } else { ?>
	<div id="content" class="col width-70">
    <?php }  ?>
		<?php include($this->get('helppath'));?>
	</div>
	<div id="adminJFSidebar">
		<div id="infosidebar">
			<?php echo $this->loadTemplate('sidemenu');?>
			<?php echo $this->loadTemplate('credits');?>
		</div>
	</div>
    <?php if (FALANG_J30 ) { ?>
        </div>
    <?php }  ?>

    </div>
<div class="clr"></div>
