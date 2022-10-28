 <?php
 /**
  * @version
  * @copyright  Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
  * @license            GNU General Public License version 2 or later; see LICENSE.txt
  */

 defined('JPATH_BASE') or die;

  /**
   * An example custom profile plugin.
   *
   * @package           Joomla.Plugins
   * @subpackage        user.profile
   * @version           1.6
   */
  class plgUserEmundus_Profile extends JPlugin
  {
		/**
		 * Constructor
		 *
		 * @access      protected
		 * @param       object  $subject The object to observe
		 * @param       array   $config  An array that holds the plugin configuration
		 * @since       1.5
		 */
		public function __construct(& $subject, $config)
		{
			parent::__construct($subject, $config);
			$this->loadLanguage();
			//JFormHelper::addFieldPath(dirname(__FILE__) . '/fields');
		}

        /**
         * @param       string  The context for the data
         * @param       int             The user id
         * @param       object
         * @return      boolean
         * @since       2.5
         */
        function onContentPrepareData($context, $data)
        {
                // Check we are manipulating a valid form.
                if (!in_array($context, array('com_users.profile','com_users.registration','com_users.user','com_admin.profile'))){
                        return true;
                }

                $userId = isset($data->id) ? $data->id : 0;

                // Load the profile data from the database.
                $db = JFactory::getDbo();
                $db->setQuery(
                        'SELECT profile_key, profile_value FROM #__user_profiles' .
                        ' WHERE user_id = '.(int) $userId .
                        ' AND profile_key LIKE \'emundus_profile.%\'' .
                        ' ORDER BY ordering'
                );
                $results = $db->loadRowList();

                // Check for a database error.
                if ($db->getErrorNum()) {
                        $this->_subject->setError($db->getErrorMsg());
                        return false;
                }

                // Merge the profile data.
                $data->emundus_profile = array();

                foreach ($results as $v) {
                        $k = str_replace('emundus_profile.', '', $v[0]);
                        $data->emundus_profile[$k] = json_decode($v[1], true);
                }

                return true;
        }

        /**
         * @param       JForm   The form to be altered.
         * @param       array   The associated data for the form.
         * @return      boolean
         * @since       1.6
         */
        function onContentPrepareForm($form, $data)
        {
          // Load user_profile plugin language
          //$lang = JFactory::getLanguage();
          //$lang->load('plg_user_emundus_profile', JPATH_ADMINISTRATOR);

          if (!($form instanceof JForm)) {
            $this->_subject->setError('JERROR_NOT_A_FORM');
              return false;
          }
          // Check we are manipulating a valid form.
          $name = $form->getName();
          if (!in_array($form->getName(), array('com_users.profile', 'com_users.registration','com_users.user','com_admin.profile'))) {
            return true;
          }

				// Add the registration fields to the form.
				JForm::addFormPath(dirname(__FILE__) . '/profiles');
				$form->loadFile('profile', false);

				$fields = array(
					'lastname',
					'firstname',
					'profile',
					'campaign',
          'newsletter',
          'cgu',
          'alert'
				);

        foreach ($fields as $field)
        {
                // Case using the users manager in admin
                if ($name == 'com_users.user')
                {
                        // Remove the field if it is disabled in registration and profile
                        if ($this->params->get('register-require_' . $field, 1) == 0
                                && $this->params->get('profile-require_' . $field, 1) == 0)
                        {
                                $form->removeField($field, 'emundus_profile');
                        }
                }
                // Case registration
                elseif ($name == 'com_users.registration')
                {
                        // Toggle whether the field is required.
                        if ($this->params->get('register-require_' . $field, 1) > 0)
                        {
                                $form->setFieldAttribute($field, 'required', ($this->params->get('register-require_' . $field) == 2) ? 'required' : '', 'emundus_profile');
                        }
                        else
                        {
                                $form->removeField($field, 'emundus_profile');
                        }
                }
                // Case profile in site or admin
                elseif ($name == 'com_users.profile' || $name == 'com_admin.profile')
                {
                        // Toggle whether the field is required.
                        if ($this->params->get('profile-require_' . $field, 1) > 0)
                        {
                                $form->setFieldAttribute($field, 'required', ($this->params->get('profile-require_' . $field) == 2) ? 'required' : '', 'emundus_profile');
                        }
                        else
                        {
                                $form->removeField($field, 'emundus_profile');
                        }
                }
        }

        return true;
        }

        function onUserAfterSave($data, $isNew, $result, $error)
        {
            $userId = JArrayHelper::getValue($data, 'id', 0, 'int');

            if ($userId && $result && isset($data['emundus_profile']) && (count($data['emundus_profile'])))
            {
                try
                {
                    $db = JFactory::getDbo();
                    $db->setQuery('DELETE FROM #__user_profiles WHERE user_id = '.$userId.' AND profile_key LIKE \'emundus_profile.%\'');
                    if (!$db->execute()) {
                            throw new Exception($db->getErrorMsg());
                    }

                    $tuples = array();
                    $order  = 1;
                    foreach ($data['emundus_profile'] as $k => $v) {
                            $tuples[] = '('.$userId.', '.$db->quote('emundus_profile.'.$k).', '.$db->quote(json_encode($v)).', '.$order++.')';
                    }

                    $db->setQuery('INSERT INTO #__user_profiles VALUES '.implode(', ', $tuples));
                    if (!$db->execute()) {
                            throw new Exception($db->getErrorMsg());
                    }
                }
                catch (JException $e) {
                        $this->_subject->setError($e->getMessage());
                        return false;
                }
            }

            return true;
        }

        /**
         * Remove all user profile information for the given user ID
         *
         * Method is called after user data is deleted from the database
         *
         * @param       array           $user           Holds the user data
         * @param       boolean         $success        True if user was succesfully stored in the database
         * @param       string          $msg            Message
         */
        function onUserAfterDelete($user, $success, $msg)
        {
                if (!$success) {
                        return false;
                }

                $userId = JArrayHelper::getValue($user, 'id', 0, 'int');

                if ($userId)
                {
                        try
                        {
                                $db = JFactory::getDbo();
                                $db->setQuery(
                                        'DELETE FROM #__user_profiles WHERE user_id = '.$userId .
                                        " AND profile_key LIKE 'emundus_profile.%'"
                                );

                                if (!$db->query()) {
                                        throw new Exception($db->getErrorMsg());
                                }
                        }
                        catch (JException $e)
                        {
                                $this->_subject->setError($e->getMessage());
                                return false;
                        }
                }

                return true;
        }


 }
?>
