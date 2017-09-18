<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_emundus
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
 
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>

<style type="text/css">
  .heading:hover{
    cursor: default;
    
  }

</style>



<div class="container-fluid">
	<div class="col-md-12 main-panel">
		<div id="listInterviews">
    </div></div>

<div class="modal fade" id="em-modal-form" style="z-index:99999" tabindex="-1" role="dialog" aria-labelledby="em-modal-actions" aria-hidden="true" data-keyboard="false" data-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
         <button type="button" class="close" data-dismiss="modal" onclick="window.location.reload()">&times;</button>
        <h4 class="modal-title" id="em-modal-actions-title"><?php echo JText::_('LOADING');?></h4>
      </div>
      <div class="modal-body">
        <img src="<?php echo JURI::Base(); ?>media/com_emundus/images/icones/loader-line.gif">
      </div>
      <div class="modal-footer">
      <button type="button" class = "btn btn-success" name="btnBook" value="book">Yes</button>
      </div>
    </div>
  </div>
</div>


<script type="text/javascript">

window.onload = listInterviewCoordinator();
		
</script>