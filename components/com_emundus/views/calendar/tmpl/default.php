<?php
/**
 * @version     1.0.0
 * @package     com_emundus
 * @copyright   Copyright (C) 2015. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      emundus <dev@emundus.fr> - http://www.emundus.fr
 */
// no direct access
defined('_JEXEC') or die;

JHTML::_('behavior.tooltip');
JHTML::stylesheet(JURI::Base().'media/com_emundus/css/emundus_panel.css' );
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'calendar.php');
JLoader::import('components.com_dpcalendar.libraries.dpcalendar.syncplugin', JPATH_ADMINISTRATOR);
JPluginHelper::importPlugin( 'dpcalendar' );

jimport('joomla.log.log');
JLog::addLogger(
    array(
        // Sets file name
        'text_file' => 'com_emundus.calendar_sync.php'
    ),
    JLog::ALL,
    array('com_emundus')
);


$sync = new EmundusModelCalendar;
$sync->authenticateClient();
$sync->saveCategoriesCalendar();
$sync->insertCategoriesCalendar();
$sync->getCalId();
$sync->updateTitleForBookingsByCandidate();


?>
<style type="text/css">
  #btnDelete {
        position: relative;
        bottom: 237px;   
        left: 750px;            
  }
</style>
<div class="container-fluid">
	<div class="col-md-12 main-panel">
    <input type="hidden" id="addCalendarForm" value="<?php echo $this->calendarFormId; ?>"></input>
    <div id="buttonCalendar" class="col-md-12">
    </div>

		<div id="calendar">
    </div>
	</div>	
</div>


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
      <div class="modal-footer modalFooterCalendar">
      <button type="button" class = "btn btn-primary" name="btnBook" value="book">BOOK</button>
      </div>
    </div>
  </div>
</div>



<script type="text/javascript">
	window.onload = showCalendar();

</script>
	
	</form>
</div>
