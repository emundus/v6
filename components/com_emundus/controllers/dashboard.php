<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      James Dean
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

use Joomla\CMS\Factory;

/**
 * Campaign Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusControllerDashboard extends JControllerLegacy
{
	protected $app;

	private $_user;
	private $m_dashboard;

    public function __construct($config = array())
    {
        parent::__construct($config);

		$this->app = Factory::getApplication();
		$this->m_dashboard = $this->getModel('Dashboard');
		$this->_user       = $this->app->getIdentity();
    }

    public function getallwidgetsbysize(){
        try {
			$size = $this->input->getInt('size');

			$widgets = $this->m_dashboard->getallwidgetsbysize($size, $this->_user->id);

            $tab = array('status' => 0, 'msg' => 'success', 'data' => $widgets);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getpalettecolors(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
			$menu   = $this->app->getMenu();
            $active = $menu->getActive();
            if(empty($active)){
                $menuid = 1079;
            } else {
                $menuid = $active->id;
            }

            $query->select('m.params')
                ->from($db->quoteName('#__modules','m'))
                ->leftJoin($db->quoteName('#__modules_menu','mm').' ON '.$db->quoteName('mm.moduleid').' = '.$db->quoteName('m.id'))
                ->where($db->quoteName('m.module') . ' LIKE ' . $db->quote('mod_emundus_dashboard_vue'))
                ->andWhere($db->quoteName('mm.menuid') . ' = ' . $menuid);

            $db->setQuery($query);
            $modules = $db->loadColumn();

            foreach ($modules as $module) {
                $params = json_decode($module, true);
                if (in_array(JFactory::getSession()->get('emundusUser')->profile,$params['profile'])) {
                    $colors = $params['colors'];
                }
            }

            $tab = array('status' => 0, 'msg' => 'success', 'data' => $colors);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getwidgets(){
        try {
			$widgets = $this->m_dashboard->getwidgets($this->_user->id);

            $tab = array('status' => 0, 'msg' => 'success', 'data' => $widgets);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatemydashboard(){
        try {
			$widget   = $this->input->getInt('widget');
			$position = $this->input->getInt('position');

			$result = $this->m_dashboard->updatemydashboard($widget, $position, $this->_user->id);

            $tab = array('status' => 0, 'msg' => 'success', 'data' => $result);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

	public function getfirstcoordinatorconnection()
	{
        try {
            $table = JTable::getInstance('user', 'JTable');
			$table->load($this->_user->id);

			$params = $this->_user->getParameters();
            if ($params->get('first_login_date')) {
                $register_at = $params->get('first_login_date');
            } else {
                $register_at = '0000-00-00 00:00:00';
            }

            $tab = array('msg' => 'success', 'data' => $register_at);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getfilters(){
        try {
			$widget = $this->input->getInt('widget');

            $tab = array('msg' => 'success', 'filters' => JFactory::getSession()->get('widget_filters_' . $widget));
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function renderchartbytag(){
        try {
			$widget  = $this->input->getInt('widget');
			$filters = $this->input->getRaw('filters');

            $session = JFactory::getSession();
            $session->set('widget_filters_' . $widget, $filters);

			$results = $this->m_dashboard->renderchartbytag($widget);

            $tab = array('msg' => 'success', 'dataset' => $results);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getarticle(){
        try {
			$widget  = $this->input->getInt('widget');
			$article = $this->input->getInt('article');

			$results = $this->m_dashboard->getarticle($widget, $article);

            $tab = array('msg' => 'success', 'data' => $results);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function geteval() {
		$response = ['status' => 0, 'msg' => JText::_('ACCESS_DENIED')];

		if (EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			try {
				$widget = $this->input->getInt('widget');

				$results = $this->m_dashboard->renderchartbytag($widget);
				$response = array('msg' => 'success', 'data' => $results, 'status' => 1);
			} catch (Exception $e) {
				$response['msg'] = $e->getMessage();
			}
		}

        echo json_encode((object)$response);
        exit;
    }
}
