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
    public function createSampleUser($profile = 9)
    {
        $m_users = new EmundusModelUsers;

        $user = clone(JFactory::getUser(0));
        $user->name = 'USER Test';
        $user->username = 'user.test@emundus.fr';
        $user->email = 'user.test@emundus.fr';
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
}
