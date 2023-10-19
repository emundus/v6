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

use Joomla\CMS\Factory;


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
	public function getVotesByUser($user = null, $email = null): array
	{
		$votes = $this->_app->getSession()->get('votes', null);

		if(is_null($votes)) {
			if (empty($user)) {
				$user = $this->_user;
			}

			$query = $this->_db->getQuery(true);

			$ip = $_SERVER['REMOTE_ADDR'];

			try {
				$query->select('v.id,v.user,v.ccid,v.firstname,v.lastname,v.email,v.ip')
					->from($this->_db->quoteName('#__emundus_vote', 'v'));
				if ($user->guest == 1) {
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
	public function vote($email,$ccid,$uid): bool
	{
		$voted = false;

		$query = $this->_db->getQuery(true);

		$ip = $_SERVER['REMOTE_ADDR'];

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
					$this->_app->getSession()->clear('votes');
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