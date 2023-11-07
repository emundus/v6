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

use Joomla\CMS\Factory;

class EmundusModelControlfiles extends JModelList
{
	private $db;
	private $app;

	function __construct()
	{
		parent::__construct();
		global $option;

		$this->app = Factory::getApplication();

		if (version_compare(JVERSION, '4.0', '>'))
		{
			$this->db = Factory::getContainer()->get('DatabaseDriver');
		}
		else
		{
			$this->db = JFactory::getDBO();
		}

		$filter_order     = $this->app->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'lastname', 'cmd');
		$filter_order_Dir = $this->app->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'asc', 'word');

        $this->setState('filter_order', $filter_order);
        $this->setState('filter_order_Dir', $filter_order_Dir);

	}


	function _buildContentOrderBy()
	{
        global $option;

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
        if (empty($this->_total)) {
            $query = $this->_buildQuery();
            $this->_total = $this->_getListCount($query);
        }
        return $this->_total;
  }

	function _buildQuery()
	{
		$query = $this->db->getQuery(true);

		$query->select('*')
			->from($this->db->quoteName('#__emundus_uploads', 'eu'))
			->leftJoin($this->db->quoteName('#__emundus_users', 'u') . ' ON ' . $this->db->quoteName('u.user_id') . ' = ' . $this->db->quoteName('eu.user_id'))
			->where($this->db->quoteName('u.schoolyear') . ' = ' . $this->db->quote($this->getCampaign()));

		return $query;
	}

	function getFiles()
	{
		$query = $this->_buildQuery();

		return $this->_getList($query);

	}


	/**
	 * Calls a function for every file in a folder.
	 *
	 * @param string $dir The directory to traverse.
	 * @param array $types The file types to call the function for. Leave as NULL to match all types.
	 * @param bool $recursive Whether to list subfolders as well.
	 * @param string $baseDir String to append at the beginning of every filepath that the callback will receive.
	 *
	 * @author Vasil Rangelov a.k.a. boen_robot
	 *
	 */
	function dir_walk($dir, $recursive, $baseDir, $tabfile, $firstuserid) {
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
					if($dir >= $firstuserid)
					{
						array_push($tabfile, $tval);
					}

				}
				elseif ($recursive && is_dir($baseDir . $dir . $file))
				{
					$tabfile = $this->dir_walk($file, $recursive, $baseDir.$dir, $tabfile, $firstuserid);
				}
			}
			closedir($dh);
			return $tabfile;
		}
	}

	function getCampaign()
	{
		$query = $this->db->getQuery(true);

		$query->select('year')
			->from($this->db->quoteName('#__emundus_setup_campaigns'))
			->where($this->db->quoteName('published') . ' = 1');
		$this->db->setQuery($query);
		$syear = $this->db->loadRow();

		return $syear[0];
	}

	function _getFirstUserId()
	{
		$query = $this->db->getQuery(true);

		$query->select('min(user_id)')
			->from($this->db->quoteName('#__emundus_users'))
			->where($this->db->quoteName('schoolyear') . ' = ' . $this->db->quote($this->getCampaign()));
		$this->db->setQuery($query);
		$firstid = $this->db->loadRow();

		return $firstid[0];
	}

	function getListFiles () {
		$tabfile = array();
		return $this->dir_walk('files'.DS, true, JPATH_SITE.DS.'images'.DS.'emundus'.DS, $tabfile, $this->_getFirstUserId());
	}
}
?>
