<?php
/**
 * Dashboard model used for the new dashboard in homepage.
 *
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

use Joomla\CMS\Date\Date;

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus/models');

class EmundusModelDashboard extends JModelList
{
	var $_db = null;

	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->offset = JFactory::getApplication()->get('offset', 'UTC');

		$this->_db = JFactory::getDBO();

		try {
			$dateTime  = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
			$dateTime  = $dateTime->setTimezone(new DateTimeZone($this->offset));
			$this->now = $dateTime->format('Y-m-d H:i:s');
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/dashboard | Error at defining the offset datetime : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
		}
	}

	public function getDashboard($user_id)
	{
		$this->_db = JFactory::getDbo();
		$query     = $this->_db->getQuery(true);

		$current_profile = JFactory::getSession()->get('emundusUser')->profile;

		try {
			$query->select('id')
				->from($this->_db->quoteName('#__emundus_setup_dashboard'))
				->where($this->_db->quoteName('user') . ' = ' . $user_id)
				->andWhere($this->_db->quoteName('profile') . ' = ' . $current_profile);
			$this->_db->setQuery($query);

			return $this->_db->loadResult();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/dashboard | Error at getting the dashboard of user ' . $user_id . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
		}
	}

	public function createDashboard($user_id)
	{
		$this->_db = JFactory::getDbo();
		$query     = $this->_db->getQuery(true);

		$profile = JFactory::getSession()->get('emundusUser')->profile;

		try {
			$query->clear()
				->insert($this->_db->quoteName('#__emundus_setup_dashboard'))
				->set($this->_db->quoteName('user') . ' = ' . $user_id)
				->set($this->_db->quoteName('profile') . ' = ' . $profile)
				->set($this->_db->quoteName('updated_by') . ' = ' . $user_id);
			$this->_db->setQuery($query);
			$this->_db->execute();
			$dashboard = $this->_db->insertid();

			$query->clear()
				->select('parent_id,position')
				->from($this->_db->quoteName('#__emundus_widgets_repeat_access'))
				->where($this->_db->quoteName('default') . ' = 1')
				->andWhere($this->_db->quoteName('profile') . ' = ' . $profile);
			$this->_db->setQuery($query);
			$default_widgets = $this->_db->loadObjectList();

			foreach ($default_widgets as $default_widget) {
				$query->clear()
					->insert($this->_db->quoteName('#__emundus_setup_dashbord_repeat_widgets'))
					->set($this->_db->quoteName('parent_id') . ' = ' . $dashboard)
					->set($this->_db->quoteName('widget') . ' = ' . $default_widget->parent_id)
					->set($this->_db->quoteName('position') . ' = ' . $default_widget->position);
				$this->_db->setQuery($query);
				$this->_db->execute();
			}

			return true;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/dashboard | Error at creating a dashboard for user ' . $user_id . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function deleteDashboard($user_id)
	{
		$this->_db = JFactory::getDbo();
		$query     = $this->_db->getQuery(true);

		try {
			$query->delete('#__emundus_setup_dashboard')
				->where($this->_db->quoteName('user') . ' = ' . $user_id);
			$this->_db->setQuery($query);

			return $this->_db->execute();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/dashboard | Error at deleting the dashboard for user ' . $user_id . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function getallwidgetsbysize($size, $user_id)
	{
		$this->_db = JFactory::getDbo();
		$query     = $this->_db->getQuery(true);

		try {
			$profile = JFactory::getSession()->get('emundusUser')->profile;

			$query->clear()
				->select('ew.id,ew.name,ew.label,ew.size,ew.size_small,ew.type,ew.chart_type,ew.article_id,ew.params')
				->from($this->_db->quoteName('#__emundus_widgets', 'ew'))
				->leftJoin($this->_db->quoteName('#__emundus_widgets_repeat_access', 'ewra') . ' ON ' . $this->_db->quoteName('ewra.parent_id') . ' = ' . $this->_db->quoteName('ew.id'))
				->where($this->_db->quoteName('ew.name') . ' = ' . $this->_db->quote('custom'))
				->andWhere($this->_db->quoteName('ew.size') . ' = ' . $this->_db->quote($size))
				->andWhere($this->_db->quoteName('ewra.profile') . ' = ' . $this->_db->quote($profile))
				->andWhere($this->_db->quoteName('ew.type') . ' = ' . $this->_db->quote('chart'))
				->andWhere($this->_db->quoteName('ew.published') . ' = 1');
			$this->_db->setQuery($query);
			$widgets = $this->_db->loadObjectList();

			if (!empty($widgets)) {
				foreach ($widgets as $key => $widget) {
					$widgets[$key]->label = JText::_($widget->label);
				}
			}

			return $widgets;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/dashboard | Error when try to get all widgets : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return [];
		}
	}

	public function getwidgets($user_id)
	{
		$this->_db = JFactory::getDbo();
		$query     = $this->_db->getQuery(true);

		$profile = JFactory::getSession()->get('emundusUser')->profile;
		if (empty($profile)) {
			$query->select('profile')
				->from($this->_db->quoteName('#__emundus_users'))
				->where($this->_db->quoteName('user_id') . ' = ' . $user_id);
			$this->_db->setQuery($query);
			$profile = $this->_db->loadResult();
		}

		try {
			$query->clear()
				->select('ew.id,ew.name,ew.label,ew.params,ew.size,ew.size_small,ew.type,ew.class,esdr.position,ew.chart_type,ew.article_id')
				->from($this->_db->quoteName('#__emundus_setup_dashbord_repeat_widgets', 'esdr'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_dashboard', 'esd') . ' ON ' . $this->_db->quoteName('esd.id') . ' = ' . $this->_db->quoteName('esdr.parent_id'))
				->leftJoin($this->_db->quoteName('#__emundus_widgets', 'ew') . ' ON ' . $this->_db->quoteName('ew.id') . ' = ' . $this->_db->quoteName('esdr.widget'))
				->where($this->_db->quoteName('esd.user') . ' = ' . $this->_db->quote($user_id))
				->andWhere($this->_db->quoteName('esd.profile') . ' = ' . $this->_db->quote($profile))
				->order('esdr.position');
			$this->_db->setQuery($query);
			$widgets = $this->_db->loadObjectList();

			if (empty($widgets)) {
				$query->clear()
					->select('params')
					->from($this->_db->quoteName('#__modules'))
					->where($this->_db->quoteName('module') . ' LIKE ' . $this->_db->quote('mod_emundus_dashboard_vue'));

				$this->_db->setQuery($query);
				$modules = $this->_db->loadColumn();

				$widgets = array();

				foreach ($modules as $module) {
					$params = json_decode($module, true);
					if (in_array(JFactory::getSession()->get('emundusUser')->profile, $params['profile'])) {
						$widgets = $params['widgets'];
					}
				}

				if (!empty($widgets)) {
					$query->clear()
						->select('id,name,label,params,size,size_small,class,type,chart_type,article_id,params')
						->from($this->_db->quoteName('#__emundus_widgets'))
						->where($this->_db->quoteName('name') . ' IN (' . implode(',', $this->_db->quote($widgets)) . ')');
					$this->_db->setQuery($query);


					$widgets = $this->_db->loadObjectList();
				}
			}

			if (!empty($widgets)) {
				foreach ($widgets as $key => $widget) {
					$widgets[$key]->label = JText::_($widget->label);
				}
			}

			return $widgets;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/dashboard | Error when try to get widgets : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return [];
		}
	}

	public function updatemydashboard($widget, $position, $user_id)
	{
		$this->_db = JFactory::getDbo();
		$query     = $this->_db->getQuery(true);

		try {
			$dashboard = $this->getDashboard($user_id);

			$query->clear()
				->update($this->_db->quoteName('#__emundus_setup_dashbord_repeat_widgets'))
				->set($this->_db->quoteName('widget') . ' = ' . $this->_db->quote($widget))
				->where($this->_db->quoteName('position') . ' = ' . $this->_db->quote($position))
				->andWhere($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($dashboard));
			$this->_db->setQuery($query);

			return $this->_db->execute();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/dashboard | Error when try update my dashboard : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	public function renderchartbytag($id)
	{
		try {
			$query = $this->_db->getQuery(true);

			$query->select('eval')
				->from($this->_db->quoteName('#__emundus_widgets'))
				->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($id));
			$this->_db->setQuery($query);
			$value = $this->_db->loadResult();

			$request = explode('php|', $value);

			return eval("$request[1]");
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/dashboard | Error when get datas : ' . preg_replace("/[\r\n]/", " ", $e->getMessage()), JLog::ERROR, 'com_emundus');

			return array('dataset' => '');
		}
	}

	public function getarticle($id, $article)
	{
		try {
			$query = $this->_db->getQuery(true);

			$query->select('id,introtext')
				->from($this->_db->quoteName('#__content'))
				->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($article));
			$this->_db->setQuery($query);
			$value = $this->_db->loadObject();

			return $value->introtext;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/dashboard | Error when get content of an article : ' . preg_replace("/[\r\n]/", " ", $e->getMessage()), JLog::ERROR, 'com_emundus');

			return array('dataset' => '');
		}
	}

	/** Sciences PO */
	public function getfilescountbystatus()
	{
		$this->_db = JFactory::getDbo();
		$query     = $this->_db->getQuery(true);

		try {
			$query->select('*')
				->from($this->_db->quoteName('#__emundus_setup_status'));
			$this->_db->setQuery($query);
			$status = $this->_db->loadObjectList();

			$files = [];

			foreach ($status as $statu) {
				$file        = new stdClass;
				$file->label = $statu->value;

				$query->clear()
					->select('COUNT(id) as files')
					->from($this->_db->quoteName('#__emundus_campaign_candidature'))
					->where($this->_db->quoteName('status') . '=' . $this->_db->quote($statu->step));

				$this->_db->setQuery($query);
				$file->value = $this->_db->loadResult();
				$files[]     = $file;
			}

			return array('files' => $files, 'status' => $status);
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/dashboard | Error when try to get files count by status : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return array('files' => '', 'status' => '');
		}
	}

	public function getfilesbycampaign($cid)
	{
		$this->_db = JFactory::getDbo();
		$query     = $this->_db->getQuery(true);

		try {
			$query->select('COUNT(id) as files')
				->from($this->_db->quoteName('#__emundus_campaign_candidature'))
				->where($this->_db->quoteName('campaign_id') . '=' . $this->_db->quote($cid));

			$this->_db->setQuery($query);

			return $this->_db->loadResult();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/dashboard | Error when try to get files by campaign : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return 0;
		}
	}

	public function getusersbyday()
	{
		$this->_db = JFactory::getDbo();

		$dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
		$dateTime = $dateTime->setTimezone(new DateTimeZone($this->offset));

		try {
			$users = [];
			$days  = [];

			$query = 'SELECT COUNT(id) as users
                            FROM jos_users';
			$this->_db->setQuery($query);
			$totalUsers = $this->_db->loadResult();

			for ($d = 1; $d < 31; $d++) {
				$user  = new stdClass;
				$day   = new stdClass;
				$query = 'SELECT COUNT(id) as users
                            FROM jos_users
                            WHERE id != 62 AND YEAR(registerDate) = ' . $dateTime->format('Y') . ' AND MONTH(registerDate) = ' . $dateTime->format('m') . ' AND DAY(registerDate) = ' . $d;

				$this->_db->setQuery($query);
				$user->value = $this->_db->loadResult();
				$day->label  = (string) $d;
				$users[]     = $user;
				$days[]      = $day;
			}

			return array('users' => $users, 'days' => $days, 'total' => $totalUsers);
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/dashboard | Error when try to get users by day : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return array('users' => '', 'days' => '', 'total' => 0);
		}
	}

	public function getfilescountbystatusgroupbydate($program)
	{
		$query = $this->_db->getQuery(true);

		$category = [];

		try {
			$query->select('value as seriesname,step')
				->from($this->_db->quoteName('#__emundus_setup_status'));
			$this->_db->setQuery($query);
			$status = $this->_db->loadObjectList();
			foreach ($status as $key => $statu) {
				$status[$key]->data = [];
			}
			$dataset = $status;

			$query->clear()
				->select('min(cc.date_time)')
				->from($this->_db->quoteName('#__emundus_campaign_candidature', 'cc'));
			if (!empty($program)) {
				$query->leftJoin($this->_db->quoteName('#__emundus_setup_campaigns', 'sc') . ' ON ' . $this->_db->quoteName('sc.id') . ' = ' . $this->_db->quoteName('cc.campaign_id'))
					->where($this->_db->quoteName('sc.training') . ' LIKE ' . $this->_db->quote($program));
			}
			$this->_db->setQuery($query);
			$start_date = new DateTime($this->_db->loadResult());

			$end_date = new DateTime();

			$category[] = $start_date->format('d/m');

			while ($start_date < $end_date) {
				$query->clear()
					->select('count(cc.id) as value, cc.status as seriesname, cc.date_time as date')
					->from($this->_db->quoteName('#__emundus_campaign_candidature', 'cc'));
				if (!empty($program)) {
					$query->leftJoin($this->_db->quoteName('#__emundus_setup_campaigns', 'sc') . ' ON ' . $this->_db->quoteName('sc.id') . ' = ' . $this->_db->quoteName('cc.campaign_id'));
				}
				$query->where($this->_db->quoteName('cc.date_time') . ' < ' . $this->_db->quote($start_date->format('Y-m-d H:i:s')));
				if (!empty($program)) {
					$query->andWhere($this->_db->quoteName('sc.training') . ' LIKE ' . $this->_db->quote($program));
				}
				$query->group('cc.status');
				$this->_db->setQuery($query);
				$files = $this->_db->loadObjectList();
				if (!empty($files)) {
					foreach ($dataset as $key => $data) {
						foreach ($files as $index => $file) {
							if ($file->seriesname == $data->step) {
								$dataset[$key]->data[] = $file;
								break;
							}
						}
						$neededObject = array_filter(
							$files,
							function ($e) use (&$data) {
								return $e->seriesname == $data->step;
							}
						);
						if (empty($neededObject)) {
							$empty_file             = new stdClass;
							$empty_file->value      = "0";
							$empty_file->seriesname = $data->step;
							$empty_file->date       = $start_date->format('Y-m-d H:i:s');
							$dataset[$key]->data[]  = $empty_file;
						}
					}
				}
				else {
					foreach ($dataset as $key => $data) {
						if (empty($dataset[$key]->data)) {
							$empty_file             = new stdClass;
							$empty_file->value      = "0";
							$empty_file->seriesname = $data->step;
							$dataset[$key]->data[]  = $empty_file;
						}
					}
				}

				$start_date->modify('+1 week');
				if ($start_date < $end_date) {
					$category[] = $start_date->format('d/m');
				}
			}

			foreach ($category as $key => $date) {
				$value                 = $date;
				$category[$key]        = new stdClass();
				$category[$key]->label = $value;
			}


			return array('dataset' => $dataset, 'category' => $category);
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/dashboard | Error when try to get users by day : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return array('dataset' => '', 'category' => '');
		}
	}

	public function getfilescountbystatusandsession($program)
	{
		$query = $this->_db->getQuery(true);

		$category = [];


		try {
			$query->select('value as seriesname,step')
				->from($this->_db->quoteName('#__emundus_setup_status'));
			$this->_db->setQuery($query);
			$status = $this->_db->loadObjectList();
			foreach ($status as $key => $statu) {
				$status[$key]->data = [];
			}
			$dataset = $status;

			$query->clear()
				->select('sc.id,sc.label,stu.id as year')
				->from($this->_db->quoteName('#__emundus_setup_campaigns', 'sc'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_teaching_unity', 'stu') . ' ON ' . $this->_db->quoteName('stu.schoolyear') . ' LIKE ' . $this->_db->quoteName('sc.year'))
				->order('stu.id');
			if (!empty($program)) {
				$query->where($this->_db->quoteName('sc.training') . ' LIKE ' . $this->_db->quote($program));;
			}
			$this->_db->setQuery($query);
			$campaigns = $this->_db->loadObjectList();

			foreach ($campaigns as $campaign) {
				if ($campaign->year != 13 && $campaign->year != 16) {
					$category[] = $campaign->label;
				}
				$query->clear()
					->select('count(cc.id) as value, cc.status as seriesname')
					->from($this->_db->quoteName('#__emundus_campaign_candidature', 'cc'));
				if (!empty($program)) {
					$query->leftJoin($this->_db->quoteName('#__emundus_setup_campaigns', 'sc') . ' ON ' . $this->_db->quoteName('sc.id') . ' = ' . $this->_db->quoteName('cc.campaign_id'));
				}
				$query->where($this->_db->quoteName('cc.campaign_id') . ' = ' . $this->_db->quote($campaign->id));
				if (!empty($program)) {
					$query->andWhere($this->_db->quoteName('sc.training') . ' LIKE ' . $this->_db->quote($program));
				}
				$query->group('cc.status');
				$this->_db->setQuery($query);
				$files = $this->_db->loadObjectList();

				if (!empty($files)) {
					foreach ($dataset as $key => $data) {
						foreach ($files as $index => $file) {
							if ($file->seriesname == $data->step) {
								if ($campaign->year != 13 && $campaign->year != 16) {
									$dataset[$key]->data[] = $file;
								}
								else {
									$number_1                   = (int) $dataset[$key]->data[0]->value + (int) $file->value;
									$combine_file_1             = new stdClass;
									$combine_file_1->value      = (string) $number_1;
									$combine_file_1->seriesname = $data->step;

									$number_2                   = (int) $dataset[$key]->data[0]->value + (int) $file->value;
									$combine_file_2             = new stdClass;
									$combine_file_2->value      = (string) $number_2;
									$combine_file_2->seriesname = $data->step;

									$dataset[$key]->data[0] = $combine_file_1;
									$dataset[$key]->data[1] = $combine_file_2;
								}
								break;
							}
						}
						$neededObject = array_filter(
							$files,
							function ($e) use (&$data) {
								return $e->seriesname == $data->step;
							}
						);
						if (empty($neededObject)) {
							$empty_file             = new stdClass;
							$empty_file->value      = "0";
							$empty_file->seriesname = $data->step;
							if ($campaign->year != 13 && $campaign->year != 16) {
								$dataset[$key]->data[] = $empty_file;
							}
						}
					}
				}
				else {
					foreach ($dataset as $key => $data) {
						if (empty($dataset[$key]->data)) {
							$empty_file             = new stdClass;
							$empty_file->value      = "0";
							$empty_file->seriesname = $data->step;
							if ($campaign->year != 13 && $campaign->year != 16) {
								$dataset[$key]->data[] = $empty_file;
							}
						}
					}
				}
			}

			foreach ($category as $key => $date) {
				$value                 = $date;
				$category[$key]        = new stdClass();
				$category[$key]->label = explode('-', $value)[1];
			}

			return array('dataset' => $dataset, 'category' => $category);
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/dashboard | Error when try to get users by day : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return array('dataset' => '', 'category' => '');
		}
	}

	public function getfilescountbystatusandcourses($program, $session)
	{
		$query = $this->_db->getQuery(true);

		$category = [];

		try {
			$query->select('value as seriesname,step')
				->from($this->_db->quoteName('#__emundus_setup_status'));
			$this->_db->setQuery($query);
			$status = $this->_db->loadObjectList();
			foreach ($status as $key => $statu) {
				$status[$key]->data = [];
			}
			$dataset = $status;

			$query->clear()
				->select('id,cours_fr')
				->from($this->_db->quoteName('data_cours_universitaire'))
				->where($this->_db->quoteName('session') . ' IN (' . $session . ')');
			$this->_db->setQuery($query);
			$courses = $this->_db->loadObjectList();

			foreach ($courses as $course) {
				$category[] = $course->cours_fr;
				$query->clear()
					->select('count(cc.id) as value, cc.status as seriesname')
					->from($this->_db->quoteName('#__emundus_campaign_candidature', 'cc'))
					->leftJoin($this->_db->quoteName('jos_emundus_1002_00', 'cu') . ' ON ' . $this->_db->quoteName('cu.fnum') . ' = ' . $this->_db->quoteName('cc.fnum'));
				if (in_array($session, [1, 2])) {
					$query->where($this->_db->quoteName('cu.e_369_7829') . ' = ' . $this->_db->quote($course->id))
						->orWhere($this->_db->quoteName('cu.e_369_7832') . ' = ' . $this->_db->quote($course->id));
				}
				elseif (in_array($session, [3])) {
					$query->where($this->_db->quoteName('cu.e_369_8302') . ' = ' . $this->_db->quote($course->id));
				}
				$query->group('cc.status');
				$this->_db->setQuery($query);
				$files = $this->_db->loadObjectList();

				if (!empty($files)) {
					foreach ($dataset as $key => $data) {
						foreach ($files as $index => $file) {
							if ($file->seriesname == $data->step) {
								$dataset[$key]->data[] = $file;
								break;
							}
						}
						$neededObject = array_filter(
							$files,
							function ($e) use (&$data) {
								return $e->seriesname == $data->step;
							}
						);
						if (empty($neededObject)) {
							$empty_file             = new stdClass;
							$empty_file->value      = "0";
							$empty_file->seriesname = $data->step;
							$dataset[$key]->data[]  = $empty_file;
						}
					}
				}
				else {
					foreach ($dataset as $key => $data) {
						if (empty($dataset[$key]->data)) {
							$empty_file             = new stdClass;
							$empty_file->value      = "0";
							$empty_file->seriesname = $data->step;
							$dataset[$key]->data[]  = $empty_file;
						}
					}
				}
			}

			foreach ($category as $key => $date) {
				$value                 = $date;
				$category[$key]        = new stdClass();
				$category[$key]->label = $value;
			}

			return array('dataset' => $dataset, 'category' => $category);
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/dashboard | Error when try to get users by day : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return array('dataset' => '', 'category' => '');
		}
	}

	public function getfilescountbystatusandcoursesprecollege($session)
	{
		$query = $this->_db->getQuery(true);

		$category = [];

		try {
			$query->select('value as seriesname,step')
				->from($this->_db->quoteName('#__emundus_setup_status'));
			$this->_db->setQuery($query);
			$status = $this->_db->loadObjectList();
			foreach ($status as $key => $statu) {
				$status[$key]->data = [];
			}
			$dataset = $status;

			$query->clear()
				->select('id,course_fr')
				->from($this->_db->quoteName('data_cours_electif_pre_universitaire_session_' . $session))
				->where($this->_db->quoteName('published') . ' = 1');
			$this->_db->setQuery($query);
			$courses = $this->_db->loadObjectList();

			foreach ($courses as $course) {
				$category[] = $course->course_fr;
				$query->clear()
					->select('count(cc.id) as value, cc.status as seriesname')
					->from($this->_db->quoteName('#__emundus_campaign_candidature', 'cc'))
					->leftJoin($this->_db->quoteName('jos_emundus_1001_04', 'cu') . ' ON ' . $this->_db->quoteName('cu.fnum') . ' = ' . $this->_db->quoteName('cc.fnum'));
				if ($session == 1) {
					$query->where($this->_db->quoteName('cu.e_366_7803') . ' = ' . $this->_db->quote($course->id))
						->orWhere($this->_db->quoteName('cu.cours_voeu_2') . ' = ' . $this->_db->quote($course->id))
						->orWhere($this->_db->quoteName('cu.e_366_7805') . ' = ' . $this->_db->quote($course->id))
						->orWhere($this->_db->quoteName('cu.cours_voeu_1_1') . ' = ' . $this->_db->quote($course->id));
				}
				elseif ($session == 2) {
					$query->where($this->_db->quoteName('cu.e_366_7804') . ' = ' . $this->_db->quote($course->id))
						->orWhere($this->_db->quoteName('cu.cours_voeu_2_2') . ' = ' . $this->_db->quote($course->id))
						->orWhere($this->_db->quoteName('cu.e_366_7806') . ' = ' . $this->_db->quote($course->id))
						->orWhere($this->_db->quoteName('cu.cours_voeu_1_2') . ' = ' . $this->_db->quote($course->id));
				}
				$query->group('cc.status');
				$this->_db->setQuery($query);
				$files = $this->_db->loadObjectList();

				if (!empty($files)) {
					foreach ($dataset as $key => $data) {
						foreach ($files as $index => $file) {
							if ($file->seriesname == $data->step) {
								$dataset[$key]->data[] = $file;
								break;
							}
						}
						$neededObject = array_filter(
							$files,
							function ($e) use (&$data) {
								return $e->seriesname == $data->step;
							}
						);
						if (empty($neededObject)) {
							$empty_file             = new stdClass;
							$empty_file->value      = "0";
							$empty_file->seriesname = $data->step;
							$dataset[$key]->data[]  = $empty_file;
						}
					}
				}
				else {
					foreach ($dataset as $key => $data) {
						if (empty($dataset[$key]->data)) {
							$empty_file             = new stdClass;
							$empty_file->value      = "0";
							$empty_file->seriesname = $data->step;
							$dataset[$key]->data[]  = $empty_file;
						}
					}
				}
			}

			foreach ($category as $key => $date) {
				$value                 = $date;
				$category[$key]        = new stdClass();
				$category[$key]->label = $value;
			}

			return array('dataset' => $dataset, 'category' => $category);
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/dashboard | Error when try to get users by day : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return array('dataset' => '', 'category' => '');
		}
	}

	public function getfilescountbynationalities($program)
	{
		$query = $this->_db->getQuery(true);

		$category = [];

		try {
			$query->select('value as seriesname,step')
				->from($this->_db->quoteName('#__emundus_setup_status'));
			$this->_db->setQuery($query);
			$status = $this->_db->loadObjectList();
			foreach ($status as $key => $statu) {
				$status[$key]->data = [];
			}
			$dataset = $status;

			$query->clear()
				->select('id,label_fr as label')
				->from($this->_db->quoteName('data_nationality'));
			$this->_db->setQuery($query);
			$nationalities = $this->_db->loadObjectList();

			foreach ($nationalities as $nationality) {
				$query->clear()
					->select('count(cc.id) as value, cc.status as seriesname')
					->from($this->_db->quoteName('#__emundus_campaign_candidature', 'cc'))
					->leftJoin($this->_db->quoteName('jos_emundus_1001_00', 'n') . ' ON ' . $this->_db->quoteName('n.fnum') . ' = ' . $this->_db->quoteName('cc.fnum'));

				if (!empty($program)) {
					$query->leftJoin($this->_db->quoteName('#__emundus_setup_campaigns', 'sc') . ' ON ' . $this->_db->quoteName('sc.id') . ' = ' . $this->_db->quoteName('cc.campaign_id'));
				}

				$query->where($this->_db->quoteName('n.e_360_7752') . ' = ' . $this->_db->quote($nationality->id));

				if (!empty($program)) {
					$query->andWhere($this->_db->quoteName('sc.training') . ' LIKE ' . $this->_db->quote($program));
				}

				$query->group('cc.status');
				$this->_db->setQuery($query);
				$files = $this->_db->loadObjectList();

				if (!empty($files)) {
					foreach ($dataset as $key => $data) {
						foreach ($files as $index => $file) {
							if ($file->seriesname == $data->step) {
								$dataset[$key]->data[] = $file;
								break;
							}
						}
						$neededObject = array_filter(
							$files,
							function ($e) use (&$data) {
								return $e->seriesname == $data->step;
							}
						);
						if (empty($neededObject)) {
							$empty_file             = new stdClass;
							$empty_file->value      = "0";
							$empty_file->seriesname = $data->step;
							$dataset[$key]->data[]  = $empty_file;
						}
					}
					$category[] = $nationality->label;
				}
			}

			foreach ($category as $key => $date) {
				$value                 = $date;
				$category[$key]        = new stdClass();
				$category[$key]->label = $value;
			}

			return array('dataset' => $dataset, 'category' => $category);
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/dashboard | Error when try to get users by day : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return array('dataset' => '', 'category' => '');
		}
	}
	/** END **/
}
