<?php
/**
* Vote Model for eMundus Component
*
* @package    Joomla
* @subpackage eMundus
*             components/com_emundus/emundus.php
* @link       http://www.emundus.fr
* @license    GNU/GPL
* @author     HUBINET Brice
*/

// No direct access

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;


class EmundusModelVote extends JModelList
{
	private $_app;
	private $_user;
	protected $_db;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_app = Factory::getApplication();
		$this->_user = $this->_app->getIdentity();
	}

	/**
	 * @param $user User
	 *
	 * @return array
	 *
	 * @since version
	 */
	public function getVotesByUser($user = null, $email = null, $ip = null)
	{
		if (empty($user)) {
			$user = $this->_user;
		}

		if(empty($ip)) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		if($user->id == 0) {
			$votes = $this->_app->getSession()->get('votes_'.$ip, null);
		} else {
			$votes = $this->_app->getSession()->get('votes_'.$user->id, null);
		}

		if(is_null($votes)) {
			$query = $this->_db->getQuery(true);

			try {
				$query->select('v.id,v.user,v.ccid,v.firstname,v.lastname,v.email,v.ip')
					->from($this->_db->quoteName('#__emundus_vote', 'v'));
				if ($user->id == 0) {
					$query->where($this->_db->quoteName('v.ip') . ' = ' . $this->_db->quote($ip));
					if(!empty($email)) {
						$query->orWhere($this->_db->quoteName('v.email') . ' LIKE ' . $this->_db->quote($email));
					}
				}
				else {
					$query->where($this->_db->quoteName('v.user') . ' = ' . $this->_db->quote($user->id));
				}

				$this->_db->setQuery($query);
				$votes = $this->_db->loadObjectList();

				$this->_app->getSession()->set('votes', $votes);
			}
			catch (Exception $e) {
				JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
			}
		}

		return $votes;
	}

	/**
	 * @param $email
	 * @param $ccid
	 * @param $uid
	 *
	 * @return bool
	 *
	 * @since version
	 */
	public function vote($email,$ccid,$uid,$ip = null)
	{
		$voted = false;

		$query = $this->_db->getQuery(true);

		if(empty($ip)) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		try {
			$query->select('v.id')
				->from($this->_db->quoteName('#__emundus_vote', 'v'))
				->where($this->_db->quoteName('v.ccid') . ' = ' . $this->_db->quote($ccid));
			if(!empty($uid)) {
				$query->where($this->_db->quoteName('v.user') . ' = ' . $this->_db->quote($uid));
			}
			else {
				$query->extendWhere(
					'AND',
					[
						$this->_db->quoteName('v.ip') . ' = ' . $this->_db->quote($ip),
						$this->_db->quoteName('email') . ' LIKE ' . $this->_db->quote($email)
					],
					'OR'
				);
			}
			$this->_db->setQuery($query);
			$vote = $this->_db->loadResult();

			if(empty($vote)){
				$columns = array(
					$this->_db->quoteName('ccid'),
					$this->_db->quoteName('email'),
					$this->_db->quoteName('ip'),
				);

				if(!empty($uid)) {
					$columns[] = $this->_db->quoteName('user');
				}

				$values = array(
					$this->_db->quote($ccid),
					$this->_db->quote($email),
					$this->_db->quote($ip),
				);

				if(!empty($uid)) {
					$values[] = $this->_db->quote($uid);
				}

				$query->clear()
					->insert($this->_db->quoteName('#__emundus_vote'))
					->columns($columns)
					->values(implode(',', $values));
				$this->_db->setQuery($query);
				$voted = $this->_db->execute();

				if($voted) {
					if(empty($uid)) {
						$this->_app->getSession()->clear('votes_'.$ip);
					} else {
						$this->_app->getSession()->clear('votes_'.$uid);
					}

					$emConfig = ComponentHelper::getComponent('com_emundus')->params;
					$email_tmpl = $emConfig->get('default_email_tmpl_vote','');

					if(!empty($email_tmpl)) {
						if(!empty($uid)) {
							$user = Factory::getUser($uid);
							$data['name'] = $user->name;
						}
						else {
							$data['name'] = '';
						}

						$template   = $this->_app->getTemplate(true);
						$params     = $template->params;
						$config	 = Factory::getConfig();

						if (!empty($params->get('logo')->custom->image)) {
							$logo = json_decode(str_replace("'", "\"", $params->get('logo')->custom->image), true);
							$logo = !empty($logo['path']) ? JURI::base().$logo['path'] : "";

						} else {
							$logo_module = ModuleHelper::getModuleById('90');
							preg_match('#src="(.*?)"#i', $logo_module->content, $tab);
							$pattern = "/^(?:ftp|https?|feed)?:?\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
        (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
        (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
        (?:[\w#!:\.\?\+\|=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";

							if (preg_match($pattern, $tab[1])) {
								$tab[1] = parse_url($tab[1], PHP_URL_PATH);
							}

							$logo = JURI::base().$tab[1];
						}

						$post = [
							'USER_NAME' => $data['name'],
							'USER_EMAIL' => $email,
							'SITE_NAME' => $config->get('sitename'),
							'LOGO' => $logo
						];

						require_once JPATH_SITE . '/components/com_emundus/controllers/messages.php';
						$c_messages = new EmundusControllerMessages();
						$c_messages->sendEmailNoFnum($email,$email_tmpl,$post,$uid);
					}
				}
			}
		}
		catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		return $voted;
	}
}

?>