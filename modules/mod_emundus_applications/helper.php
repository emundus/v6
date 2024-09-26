<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_users_latest
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class modemundusApplicationsHelper {

	// get users sorted by activation date
	static function getApplications($layout, $order_by, $params = null) {
		$applications = [];
		$user = JFactory::getUser();
		$db	= JFactory::getDbo();
		$query = $db->getQuery(true);

		// Test if the table used for showing the title exists.
		// If it doesn't then we just continue without a title.
		$has_table = false;
		if ($layout == '_:hesam') {
			$query->select($db->quoteName('id'))
				->from($db->quoteName('#__emundus_projet'))
				->setLimit('1');

			try {
				$db->setQuery($query);
				$has_table = $db->loadResult();
			} catch (Exception $e) {
				$has_table = false;
			}
		}

		$select = 'ecc.id as ccid, ecc.date_time AS campDateTime, ecc.*, esc.*, ess.step, ess.value, ess.class, ecc.published as published,p.label as programme,p.color as tag_color,ecc.tab as tab_id,ecct.name as tab_name,ecct.ordering as tab_ordering';

		// CCI-RS layout needs to get the start and end date of each application
		if ($layout == '_:ccirs') {
			$select .= ', t.date_start as date_start, t.date_end as date_end, p.id as pid, p.url as url ';
		}

		// Hesam layout needs to get the title from the information about the project.
		if ($has_table) {
			$select .= ', pro.titre, pro.id AS search_engine_page, pro.question ';
		}

		$query->clear()
			->select($select)
			->from($db->quoteName('#__emundus_campaign_candidature', 'ecc'))
			->leftJoin($db->quoteName('#__emundus_campaign_candidature_tabs', 'ecct') . ' ON ecct.id=ecc.tab')
			->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON esc.id=ecc.campaign_id')
			->leftJoin($db->quoteName('#__emundus_setup_status', 'ess') . ' ON ess.step=ecc.status')
			->leftJoin($db->quoteName('#__emundus_setup_programmes', 'p') . ' ON p.code = esc.training');

		if ($layout == '_:ccirs') {
			$query->leftJoin($db->quoteName('#__emundus_setup_teaching_unity', 't') . ' ON t.session_code = esc.session_code');
		}

		if ($has_table) {
			$query->leftJoin($db->quoteName('#__emundus_projet', 'pro') . ' ON pro.fnum=ecc.fnum');
		}

		$query->where('ecc.applicant_id ='.$user->id);

		if (!empty($params)) {
            $selected_campaigns = $params->get('selected_campaigns', []);

            if (!empty($selected_campaigns)) {
                $exclusion = $params->get('selected_campaigns_exclusion', false);

                if ($exclusion) {
                    $query->andWhere('ecc.campaign_id NOT IN (' . implode(', ', $selected_campaigns) . ')');
                } else {
                    $query->andWhere('ecc.campaign_id IN (' . implode(', ', $selected_campaigns) . ')');
                }
            }

            $show_status = $params->get('show_status', '') !== '' ? explode(',', $params->get('show_status', '')) : null;
            if(!empty($show_status)) {
                $query->andWhere('ecc.status IN (' . implode(', ', $show_status) . ')');
            }
        }

		$order_by_session = JFactory::getSession()->get('applications_order_by');
		switch ($order_by_session) {
			case 'status':
				$query->order('ess.ordering ASC,ecc.date_time DESC');
				break;
			case 'campaigns':
				$query->order('esc.start_date DESC,ess.ordering ASC,ecc.date_time DESC');
				break;
			case 'last_update':
				$query->order('ecc.updated DESC,ecc.date_time DESC');
				break;
			case 'programs':
				$query->order('esc.training ASC,ess.ordering ASC,ecc.date_time DESC');
				break;
			case 'years':
				$query->order('esc.year DESC,ess.ordering ASC,ecc.date_time DESC');
				break;
			default:
				$query->order($order_by);
				break;
		}

		try {
			$db->setQuery($query);
			$applications = $db->loadObjectList('fnum');
		} catch (Exception $e) {
			JLog::add('Module emundus applications failed to get applications for user ' . $user->id .  ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
		}

		return $applications;
	}

    // get State of the files (published, removed, archived)
    static function getStatusFiles() {
		$states = [];
        $user = JFactory::getUser();

		if (!empty($user->id)) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select([$db->quoteName('published'), $db->quoteName('fnum')])
				->from($db->quoteName('#__emundus_campaign_candidature'))
				->where($db->quoteName('applicant_id').'='.$user->id);

			try {
				$db->setQuery($query);
				$states = $db->loadAssocList('fnum');
			} catch (Exception $e) {
				JLog::add('Module emundus applications failed to get state of files for user ' . $user->id .  ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $states;
    }

	// get poll id of the appllicant
	static function getPoll() {
		$user 	= JFactory::getUser();
		$db		= JFactory::getDbo();

		$query = 'SELECT id
					FROM #__emundus_survey AS es
					WHERE es.user ='.$user->id;

		$db->setQuery($query);
		$id = $db->loadResult();
		return $id>0?$id:0;
	}

	static function getOtherCampaigns($uid) {

		$db = JFactory::getDbo();

		$query = 'SELECT c.id,c.label
					FROM #__emundus_setup_campaigns AS c
					LEFT JOIN #__emundus_setup_programmes AS p ON p.code LIKE c.training
					WHERE c.published = 1
					AND p.apply_online = 1
					AND c.end_date >= NOW()
					AND c.start_date <= NOW()
					AND c.id NOT IN (
						select campaign_id
						from #__emundus_campaign_candidature
						where applicant_id='. $uid .'
					)';
		try {
			$db->setQuery($query);
			return $db->loadAssocList();
		} catch (Exception $e) {
			JLog::add("Error at query : ".$query, JLog::ERROR, 'com_emundus');
			return false;
		}
	}

	static function getFutureYearCampaigns($uid) {

		$db = JFactory::getDbo();

		$query = 'SELECT c.id,c.label
					FROM #__emundus_setup_campaigns AS c
					LEFT JOIN #__emundus_setup_programmes AS p ON p.code LIKE c.training
					WHERE c.published = 1
				  	AND p.apply_online = 1
					AND c.end_date >= NOW()
					AND c.start_date <= NOW()
					AND c.year NOT IN (
						select sc.year
						from #__emundus_campaign_candidature as cc
						LEFT JOIN #__emundus_setup_campaigns as sc ON sc.id = cc.campaign_id
						where applicant_id='. $uid .'
					)';

		try {
			$db->setQuery($query);
			return $db->loadAssocList();
		} catch (Exception $e) {
			JLog::add("Error at query : ".$query, JLog::ERROR, 'com_emundus');
			return false;
		}
	}

	static function getAvailableCampaigns() {

		$db = JFactory::getDbo();

		$query = 'SELECT c.id,c.label
					FROM #__emundus_setup_campaigns AS c
					LEFT JOIN #__emundus_setup_programmes AS p ON p.code LIKE c.training
					WHERE c.published = 1
					AND p.apply_online = 1
					AND c.end_date >= NOW()
					AND c.start_date <= NOW()';

		try {
			$db->setQuery($query);
			return $db->loadAssocList();
		} catch (Exception $e) {
			JLog::add("Error at query : ".$query, JLog::ERROR, 'com_emundus');
			return false;
		}
	}

	static function getDrhApplications() {
		$user = JFactory::getUser();
		$db	= JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select(['ecc.*', 'esc.*', $db->quoteName('ess.step'), $db->quoteName('ess.value'), $db->quoteName('ess.class'), $db->quoteName('t.date_start', 'date_start'), $db->quoteName('t.date_end', 'date_end'), $db->quoteName('p.id', 'pid'), $db->quoteName('p.url', 'url')])
			->from($db->quoteName('#__emundus_campaign_candidature', 'ecc'))
			->leftJoin($db->quoteName('#__emundus_setup_campaigns','esc').' ON '.$db->quoteName('esc.id').' = '.$db->quoteName('ecc.campaign_id'))
			->leftJoin($db->quoteName('#__emundus_setup_status','ess').' ON '.$db->quoteName('ess.step').' = '.$db->quoteName('ecc.status'))
			->leftJoin($db->quoteName('#__emundus_setup_teaching_unity','t').' ON '.$db->quoteName('t.session_code').' = '.$db->quoteName('esc.session_code'))
			->leftJoin($db->quoteName('#__emundus_setup_programmes','p').' ON '.$db->quoteName('p.code').' = '.$db->quoteName('esc.training'))
			->leftJoin($db->quoteName('#__emundus_setup_thematiques', 'th').' ON '.$db->quoteName('th.id').' = '.$db->quoteName('p.programmes'))
			->where($db->quoteName('ecc.applicant_id').' IN (
				SELECT '.$db->quoteName('user').'
				FROM '.$db->quoteName('#__emundus_user_entreprise','eu').' WHERE '.$db->quoteName('eu.cid').' IN (
					SELECT '.$db->quoteName('euu.cid').' 
					FROM '.$db->quoteName('#__emundus_user_entreprise','euu').' WHERE '.$db->quoteName('euu.user').' = '.$user->id.' AND '.$db->quoteName('euu.profile').' = 1002 
					)
				) AND '.$db->quoteName('p.published').' = 1 AND '.$db->quoteName('t.published').' = 1 AND '.$db->quoteName('th.published').' = 1 AND '. $db->quoteName('ecc.company_id') . ' IS NOT NULL ')
			->group([$db->quoteName('esc.id')])
			->order($db->quoteName('ecc.date_submitted').' DESC');

		try {
            $db->setQuery($query);
            return $db->loadAssocList('fnum');
        } catch (Exception $e) {
            JLog::add("Error at query : ".preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }

	}

	/** Get all contact offers for the fnums.
	 *  This also includes contact offers sent with this offer attached.
	 *
	 * @param $fnums
	 *
	 * @return array
	 * @since version
	 */
    static function getContactOffers($fnums) {
		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'cifre.php');
		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'messages.php');
		$m_cifre = new EmundusModelCifre();
		$m_messages = new EmundusModelMessages();

		$fnums = $m_cifre->getContactsByFnums($fnums);

		// Here we organize fnums by profile in order to have the split contact cards in HESAM.
	    $return = [];
	    foreach ($fnums as $fnum => $offers) {

	    	foreach ($offers as $key => $data) {
			    $data['unread'] = $m_messages->getUnread($data['applicant_id']);

			    if ($data['favorite'] === '1') {
				    // Place favorite at the front of the array.
				    $return[$fnum][$data['profile_id']][0] = $data;
				    ksort($return[$fnum][$data['profile_id']]);
			    } else {
			    	// We use $key+1 to avoid the case where $key is 0, we need to ensure the favorite is first.
				    $return[$fnum][$data['profile_id']][$key+1] = $data;
			    }
		    }
	    }

	    return $return;
    }

	/** Get all chat requests for a user.
	 *
	 * @param $user
	 *
	 * @return array
	 * @since version
	 */
	static function getChatRequests($user) {
		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'cifre.php');
		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'messages.php');
		$m_cifre = new EmundusModelCifre();
		$m_messages = new EmundusModelMessages();

		$return = $m_cifre->getChatRequestsByUser($user);

		foreach ($return as $key => $data) {
			$return[$key]['unread'] = $m_messages->getUnread($data['applicant_id']);
		}
		return $return;
	}

    /** Get delivery data for a fnum.
     *
     * @param $fnum
     *
     * @return Object | null
     * @since 1.7
     */
    static function getDeliveryData($fnum) {
        $db	= JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select([$db->quoteName('tracking_number'), $db->quoteName('tracking_link')])
            ->from($db->quoteName('#__emundus_admission'))
            ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));

        try {
            $db->setQuery($query);
            return $db->loadObject();
        } catch(Exception $e) {
            JLog::add("Error at query : ".preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return null;
        }
    }

    static function getHikashopOrder($fnumInfos, $cancelled = false) {


        $eMConfig = JComponentHelper::getParams('com_emundus');

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $em_application_payment = $eMConfig->get('application_payment', 'user');

        if ($cancelled) {
            $order_status = array('cancelled');
        } else {
            $order_status = array('confirmed');
            switch ($eMConfig->get('accept_other_payments', 0)) {
                case 1:
                    array_push($order_status, 'created');
                    break;
                case 3:
                    array_push($order_status, 'pending');
                    break;
                case 4:
                    array_push($order_status, 'created', 'pending');
                    break;
                default:
                    // No need to push to the array
                    break;

            }
        }

        $query
            ->select([$db->quoteName('jhos.orderstatus_namekey'), $db->quoteName('jhos.orderstatus_color'), $db->quoteName('eh.order_id')])
            ->from($db->quoteName('#__emundus_hikashop', 'eh'))
            ->leftJoin($db->quoteName('#__hikashop_order','ho').' ON '.$db->quoteName('ho.order_id').' = '.$db->quoteName('eh.order_id'))
            ->leftJoin($db->quoteName('#__hikashop_orderstatus','jhos').' ON '.$db->quoteName('jhos.orderstatus_namekey').' = '.$db->quoteName('ho.order_status'))
            ->where($db->quoteName('ho.order_status') . ' IN (' . implode(", ", $db->quote($order_status)) . ')')
            ->order($db->quoteName('order_created') . ' DESC');

        switch ($em_application_payment) {

            default :
            case 'fnum' :
                $query
                    ->where($db->quoteName('eh.fnum') . ' = ' . $fnumInfos->fnum);
                break;

            case 'user' :
                $query
                    ->where($db->quoteName('eh.user') . ' = ' . $fnumInfos->applicant_id);
                break;

            case 'campaign' :
                $query
                    ->where($db->quoteName('eh.campaign_id') . ' = ' . $fnumInfos->id)
                    ->where($db->quoteName('eh.user') . ' = ' . $fnumInfos->applicant_id);
                break;

            case 'status' :
                $em_application_payment_status = $eMConfig->get('application_payment_status', '0');
                $payment_status = explode(',', $em_application_payment_status);

                if (in_array($fnumInfos->status, $payment_status)) {
                    $query
                        ->where($db->quoteName('eh.status') . ' = ' . $fnumInfos->status)
                        ->where($db->quoteName('eh.fnum') . ' = ' . $fnumInfos->fnum);
                } else{
                    $query
                        ->where($db->quoteName('eh.fnum') . ' = ' . $fnumInfos->fnum);
                }
                break;
        }
        try {
            $db->setQuery($query);
            return $db->loadObject();
        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * $param $application object
     * $param $custom_actions array
     * $param $key int
     *
     * @return void
     */
	public static function displayCustomActions($application, $custom_actions, $key = 0)
    {
        $html = '';

        if (!empty($custom_actions) && !empty($application)) {
            foreach ($custom_actions as $custom_action_key => $custom_action) {

                if (!empty($custom_action->display_condition)) {
                    $condition = str_replace('{fnum}', $application->fnum, $custom_action->display_condition);

                    // eval is evil, but we have no choice here
                    try {
                        if (!eval($condition)) {
                            continue;
                        }
                    } catch (Exception $e) {
                        JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
                        continue;
                    }
                }

                if (in_array($application->status, $custom_action->mod_em_application_custom_action_status)) {
                    if ($custom_action->mod_em_application_custom_action_type == 2) {
                        $html .= '<div class="em-flex-row px-2.5 py-2">';
                        if ($custom_action->mod_em_application_custom_action_icon) {
                            $html .= '<span class="material-icons-outlined em-font-size-16 em-mr-8">' . $custom_action->mod_em_application_custom_action_icon . '</span>';
                        }

                        $html .= '<span id="actions_button_custom_' . $custom_action_key . '" 
                                    class="em-text-neutral-900 em-pointer em-custom-action-launch-action" 
                                    data-text="' . $custom_action->mod_em_application_custom_action_new_status_message . '"
                                    data-fnum="' . $application->fnum . '"  
                                  >' . JText::_($custom_action->mod_em_application_custom_action_label) . '</span>';
                        $html .= '</div>';
                    } else if (!empty($custom_action->mod_em_application_custom_action_link)) {
                        $link = str_replace('{fnum}', $application->fnum, $custom_action->mod_em_application_custom_action_link);
                        $target = $custom_action->mod_em_application_custom_action_link_blank ? 'target="_blank"' : '';

                        $html .= '<a id="actions_button_custom_' . $custom_action_key .'_card_tab' . $key . '" 
                                    class="em-text-neutral-900 em-pointer em-flex-row"
                                    href="' . $link . '" ' . $target . '>';

                        if ($custom_action->mod_em_application_custom_action_icon) {
                            $html .= '<span class="material-icons-outlined em-font-size-16 em-mr-8">' . $custom_action->mod_em_application_custom_action_icon . '</span>';
                        }

                        $html .= JText::_($custom_action->mod_em_application_custom_action_label) .  '</a>';
                    }
                }
            }
        }

        echo $html;
	}

    static function getNbComments($ccid, $current_user) {
        $nb_comments = 0;

        if (!empty($ccid)) {
            if (!class_exists('EmundusModelComments')) {
                require_once(JPATH_ROOT . '/components/com_emundus/models/comments.php');
            }
            $m_comments = new EmundusModelComments();
            $comments = $m_comments->getComments($ccid, $current_user, true);

            $nb_comments = count($comments);
        }

        return $nb_comments;
    }

    static function getCommentsPageBaseUrl()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('alias')
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('link') . ' LIKE "%view=application&layout=history%"');

        $db->setQuery($query);
        $alias = $db->loadResult();

        return $alias;
    }
}
