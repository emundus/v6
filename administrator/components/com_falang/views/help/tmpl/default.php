<?php 
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2011, Think Network GmbH, Munich
 *
 * All rights reserved.  The Joom!Fish project is a set of extentions for
 * the content management system Joomla!. It enables Joomla!
 * to manage multi lingual sites especially in all dynamic information
 * which are stored in the database.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -----------------------------------------------------------------------------
 * $Id: default.php 1580 2011-04-16 17:11:41Z akede $
 * @package joomfish
 * @subpackage Views
 *
*/
defined('_JEXEC') or die('Restricted access'); ?>

<?php if (!empty( $this->sidebar)): ?>
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
