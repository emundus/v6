<?php

defined('_JEXEC') or die('Restricted access');
if(!defined('DS')){
define('DS',DIRECTORY_SEPARATOR);
}

require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

class modEmundusDocumentHelper {
	public function getDocObligatoire() {
		$user = JFactory::getSession()->get('emundusUser');
		$db = JFactory::getDBO();

		$query='SELECT id, link FROM #__menu WHERE alias like "checklist%" AND menutype like "%'.$user->menutype.'"';
		$db->setQuery( $query );
		$itemid = $db->loadAssoc();

		$query='SELECT esa.value, esap.id as _id, esa.id, esa.allowed_types
			FROM #__emundus_setup_attachment_profiles esap
			JOIN #__emundus_setup_attachments esa ON esa.id = esap.attachment_id
			WHERE esap.displayed = 1 AND esap.mandatory = 1 AND esap.profile_id ='.$user->profile.' 
			ORDER BY esa.ordering';

		$db->setQuery( $query );
		$forms = $db->loadObjectList();
		$retour = "</ul>";
		if (count($forms) > 0) {
			foreach ($forms as $form) {
				$query = 'SELECT count(id) FROM #__emundus_uploads up
							WHERE up.user_id = '.$user->id.' AND up.attachment_id = '.$form->id.' AND fnum like '.$db->Quote($user->fnum);
				$db->setQuery( $query );
				$cpt = $db->loadResult();
				$link 	= '<a id="'.$form->id.'" class="document" href="'.$itemid['link'].'&Itemid='.$itemid['id'].'#a'.$form->id.'">';
				if ($cpt==0)
					$class	= 'need_missing';
				else
					$class	= 'need_ok';
				$endlink= '</a>';
				$retour .= '<li class="em_module '.$class.'" title="'.$form->allowed_types.'"><div class="em_form em-checklist">'.$link.$form->value.$endlink.'</div></li>';
			}
		}
		$retour .= "</li>";
		unset($link);
		unset($endlink);

		return $retour;
	}

	public function getDocOptionnel() {
		$user = JFactory::getSession()->get('emundusUser');
		$db = JFactory::getDBO();

		$document = JFactory::getDocument();
		$document->addStyleSheet("media/com_emundus/css/emundus_checklist.css" );

		$query='SELECT id, link FROM #__menu WHERE alias like "checklist%" AND menutype like "%'.$user->menutype.'"';
		$db->setQuery( $query );
		$itemid = $db->loadAssoc();

		$retour = "</ul>";
		$query='SELECT esa.value, esap.id as _id, esa.id, esa.allowed_types
			FROM #__emundus_setup_attachment_profiles esap
			JOIN #__emundus_setup_attachments esa ON esa.id = esap.attachment_id
			WHERE esap.displayed = 1 AND esap.mandatory = 0 AND esap.profile_id ='.$user->profile.'   
			ORDER BY esa.ordering';
				$db->setQuery( $query );
				$forms = $db->loadObjectList();
				foreach ($forms as $form) {
					$query = 'SELECT count(id) FROM #__emundus_uploads up
								WHERE up.user_id = '.$user->id.' AND up.attachment_id = '.$form->id.' AND fnum like '.$db->Quote($user->fnum);
					$db->setQuery( $query );
					$cpt = $db->loadResult();
					$link 	= '<a id="'.$form->id.'" class="document" href="'.$itemid['link'].'&Itemid='.$itemid['id'].'#a'.$form->id.'">';
					if ($cpt==0)
						$class	= 'need_missing_fac';
					else
						$class	= 'need_ok';
					$endlink= '</a>';
					$retour .= '<li class="em_module '.$class.'" title="'.$form->allowed_types.'"><div class="em_form em-checklist">'.$link.$form->value.$endlink.'</div></li>';
				}
		$retour .= "</ul>";

		unset($link);
		unset($endlink);

		return $retour;
	}

	public function getDocCharges() {
		$current_user = JFactory::getSession()->get('emundusUser');
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

        $retour = "";

		$query
			->select($db->quoteName('campaign_id'))
			->from($db->quoteName('#__emundus_campaign_candidature'))
			->where($db->quoteName('fnum') . " LIKE " . $db->quote($current_user->fnum));
		$db->setQuery($query);

		$cid = $db->loadResult();

		$query= $db->getQuery(true);
		$query
			->clear()
			->select($db->quoteName('sap.attachment_id'))
			->from($db->quoteName('#__emundus_setup_attachments', 'sa'))
			->join('INNER', $db->quoteName('#__emundus_setup_attachment_profiles', 'sap') . ' ON (' . $db->quoteName('sa.id') . ' = ' . $db->quoteName('sap.attachment_id') . ')')
			->where($db->quoteName('sap.displayed') . ' = 0');

		$db->setQuery($query);
		$attachid =  $db->loadColumn();

		if(!empty($attachid)) {
            $query->clear()
                ->select(array($db->quoteName('u.id'), $db->quoteName('u.filename'), $db->quoteName('sa.lbl'), $db->quoteName('sa.value'), $db->quoteName('sa.allowed_types')))
                ->from($db->quoteName('#__emundus_uploads', 'u'))
                ->join('INNER', $db->quoteName('#__emundus_setup_attachments', 'sa') . ' ON (' . $db->quoteName('sa.id') . ' = ' . $db->quoteName('u.attachment_id') . ')')
                ->where($db->quoteName('user_id') . " = " . $current_user->id . " AND " . $db->quoteName('campaign_id') . " = " . $cid . " AND " . $db->quoteName('fnum') . " = " . $current_user->fnum . " AND " . $db->quoteName('attachment_id') . " IN (" . implode(',', $attachid) . " )");
            $db->setQuery($query);

            $result = $db->loadObjectList();
            if (!empty($result)) {

                foreach ($result as $res) {
                    $retour .= '<li class="em_module need_ok ' . $res->lbl . '" title="' . $res->allowed_types . '">
				<div class="em_form em_checklist"><a href="/images/emundus/files/' . $current_user->id . '/' . $res->filename . '" target="_blank">' . JTEXT::_($res->value) . '</a></div></li>';
                }
            }
        }

		return $retour;
	}
}
