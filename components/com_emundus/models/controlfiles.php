<?php
/**
 * Trombi Model for eMundus World Component
 * 
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Jonas Lerebours
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
 
class EmundusModelControlfiles extends JModelList
{
	function __construct()
	{
		parent::__construct();
		global $option;

		$mainframe = JFactory::getApplication();
		
		$filter_order     = $mainframe->getUserStateFromRequest(  $option.'filter_order', 'filter_order', 'lastname', 'cmd' );
        $filter_order_Dir = $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
 
        $this->setState('filter_order', $filter_order);
        $this->setState('filter_order_Dir', $filter_order_Dir);
		
	}
	
	
	function _buildContentOrderBy()
	{
        global $option;

		$mainframe = JFactory::getApplication();
 
                $orderby = '';
                $filter_order     = $this->getState('filter_order');
                $filter_order_Dir = $this->getState('filter_order_Dir');
				
				$can_be_ordering = array ('user', 'id', 'lastname', 'nationality', 'time_date');
                /* Error handling is never a bad thing*/
                if(!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)){
                        $orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
                }
 
                return $orderby;
	}
	
	  function getTotal()
  {
        // Load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->_buildQuery();
            $this->_total = $this->_getListCount($query);    
        }
        return $this->_total;
  }

	function _buildQuery()
	{
		$query = 'SELECT * FROM #__emundus_uploads as eu 
			LEFT JOIN #__emundus_users as u ON u.user_id=eu.user_id 
			WHERE u.schoolyear="'.$this->getCampaign().'"';
		return $query;
	} 

	function getFiles()
	{
		// Lets load the data if it doesn't already exist
		$query = $this->_buildQuery(); //die(print_r(count($this->_getList($query ,$this->getState('limitstart'), $this->getState('limit')))));
		//return $this->_getList($query ,$this->getState('limitstart'), $this->getState('limit'));
		return $this->_getList($query);
		
	} 
		
		
	/**
	 * Calls a function for every file in a folder.
	 *
	 * @author Vasil Rangelov a.k.a. boen_robot
	 *
	 * @param string $dir The directory to traverse.
	 * @param array $types The file types to call the function for. Leave as NULL to match all types.
	 * @param bool $recursive Whether to list subfolders as well.
	 * @param string $baseDir String to append at the beginning of every filepath that the callback will receive.
	 */
	function dir_walk($dir, $recursive = true, $baseDir = '', $tabfile, $firstuserid) {
		if ($dh = @opendir($baseDir.$dir)) {
			while (($file = readdir($dh)) !== false) { 
				if ($file === '' || $file === '.' || $file === '..' || 
					$file === '.'.DS || $file === 'index.html' || $file === 'tmp' || 
					$file === 'Thumbs.db' || $file === 'application.pdf' || 
					strpos($file, 'tn_') === 0 ) {
					continue;
				}
				if (is_file($baseDir.$dir.DS.$file) && $dir != 'files'.DS) {
					$tval['id'] = $dir;
					$tval['file'] = $file;
				/* 	array_push($tabfile['id'], $dir);*/
					if($dir >= $firstuserid)
						array_push($tabfile, $tval); 
					
				} elseif($recursive && is_dir($baseDir.$dir.$file)) {
				//print_r($tabfile);
					$tabfile = $this->dir_walk($file, $recursive, $baseDir.$dir, $tabfile, $firstuserid);
				}
			}
			closedir($dh);
			return $tabfile;
		}
	}
	
	function getCampaign()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT year as schoolyear FROM #__emundus_setup_campaigns WHERE published=1';
		$db->setQuery( $query );
		$syear = $db->loadRow();
		
		return $syear[0];
	}
	
	function _getFirstUserId () {
		$db = JFactory::getDBO();
		$query = 'SELECT min(user_id) FROM #__emundus_users WHERE schoolyear = "'.$this->getCampaign().'"';
		$db->setQuery( $query );
		$firstid = $db->loadRow();
		
		return $firstid[0];
	}
	
	function getListFiles () {
		$tabfile = array();
		return $this->dir_walk('files'.DS, true, JPATH_SITE.DS.'images'.DS.'emundus'.DS, $tabfile, $this->_getFirstUserId());
	}
}
?>