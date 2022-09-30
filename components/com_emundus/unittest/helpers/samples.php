<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2015 eMundus. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');
jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

include_once(JPATH_SITE.'/components/com_emundus/models/users.php');
include_once(JPATH_SITE.'/components/com_emundus/models/formbuilder.php');
include_once(JPATH_SITE.'/components/com_emundus/models/settings.php');
include_once(JPATH_SITE.'/components/com_emundus/api/FileSynchronizer.php');

/**
 * eMundus Component Query Helper
 *
 * @static
 * @package     Joomla
 * @subpackage  eMundus
 * @since 1.5
 */
class EmundusUnittestHelperSamples
{
    public function createSampleUser($profile = 9,$username = 'user.test@emundus.fr')
    {
        $m_users = new EmundusModelUsers;

        $user = clone(JFactory::getUser(0));
        $user->name = 'USER Test';
        $user->username = $username;
        $user->email = $username;
        $user->password = md5('test1234');
        $user->registerDate = date('Y-m-d H:i:s');
        $user->lastvisitDate = date('Y-m-d H:i:s');
        $user->groups = array();
        $user->block = 0;

        $other_param['firstname'] 		= 'Test';
        $other_param['lastname'] 		= 'USER';
        $other_param['profile'] 		= $profile;
        $other_param['em_oprofiles'] 	= '';
        $other_param['univ_id'] 		= 0;
        $other_param['em_groups'] 		= '';
        $other_param['em_campaigns'] 	= '1';
        $other_param['news'] 			= '';

        $acl_aro_groups = $m_users->getDefaultGroup($profile);
        $user->groups = $acl_aro_groups;

        $usertype = $m_users->found_usertype($acl_aro_groups[0]);
        $user->usertype = $usertype;

        $user->save();
        $m_users->addEmundusUser($user->id, $other_param);

        return $user;
    }

    public function createSampleFile($cid,$uid){
        $m_formbuilder = new EmundusModelFormbuilder;

        return $m_formbuilder->createTestingFile($cid,$uid);
    }

    public function createSampleTag(){
        $m_settings = new EmundusModelSettings;

        return $m_settings->createTag()->id;
    }

    public function createSampleStatus(){
        $m_settings = new EmundusModelSettings;

        return $m_settings->createStatus()->step;
    }

    public function createSampleForm($prid = 9, $label = ['fr' => 'Formulaire Tests unitaires', 'en' => 'form for unit tests'], $intro = ['fr' => 'Ce formulaire est un formulaire de test eMundus, utilisé uniquement pour tester le bon fonctionnement de la plateforme.', 'en' => '']) {
        $m_formbuilder = new EmundusModelFormbuilder;
        return $m_formbuilder->createFabrikForm($prid, $label, $intro);
    }

    public function createSampleGroup() {
        $data = [];
        $m_formbuilder = new EmundusModelFormbuilder;

        $form_id = $this->createSampleForm();

        if (!empty($form_id)) {
            $group = $m_formbuilder->createGroup(['fr' => 'Groupe Tests unitaires', 'en' => 'Group Unit tests'] , $form_id);

            if (!empty($group['group_id'])) {
                $group_id = $group['group_id'];

                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                $query->select('params')
                    ->from('#__fabrik_groups')
                    ->where('id = ' . $group_id);

                $db->setQuery($query);

                $params = $db->loadResult();
                $params = json_decode($params, true);

                $params['is_sample'] = true;

                $query->clear()
                    ->update('#__fabrik_groups')
                    ->set('params = ' . $db->quote(json_encode($params)))
                    ->where('id = ' . $group_id);

                $db->setQuery($query);
                $db->execute();

                $data = array(
                    'form_id' => $form_id,
                    'group_id' => $group_id
                );
            }
        }

        return $data;
    }

    public function deleteSampleGroup($group_id) {
        $deleted = false;
        if (!empty($group_id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('params')
                ->from('#__fabrik_groups')
                ->where('id = ' . $group_id);

            $db->setQuery($query);

            $params = $db->loadResult();
            $params = json_decode($params, true);

            if ($params['is_sample']) {
                $query->clear()
                    ->delete('#__fabrik_groups')
                    ->where('id = ' . $group_id);

                $db->setQuery($query);
                $deleted = $db->execute();
            }
        }

        return $deleted;
    }

    public function deleteSampleForm($form_id) {
        $deleted = false;
        if (!empty($form_id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->delete('#__fabrik_groups')
                ->where('id = ' . $form_id);

            $db->setQuery($query);
            $deleted = $db->execute();
        }

        return $deleted;
    }
}
