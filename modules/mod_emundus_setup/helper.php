<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;

defined('_JEXEC') or die;

/**
 * Helper for mod_emundus_setup
 *
 * @since  1.5
 */
class ModEmundusSetupHelper
{
	static function getReferentSetup($paths, $attachments = [])
	{
		$setups = [];

		if(version_compare(JVERSION, '4.0', '>=')) {
			$db = Factory::getContainer()->get('DatabaseDriver');
		}
		else {
			$db = Factory::getDbo();
		}

		$query = $db->getQuery(true);
		$campaigns_step1 = [];

		foreach ($paths as $path) {
			switch($path) {
				// We send email to referent and referent upload a recommendation letter
				case 1:
					// 1. We search forms with emundus_referent_letter plugin
					$query->clear()
						->select('id,params')
						->from($db->quoteName('#__fabrik_forms'))
						->where($db->quoteName('params') . ' LIKE ' . $db->quote('%emundusreferentletter%'));
					$db->setQuery($query);
					$forms = $db->loadObjectList();

					// 2. We search campaigns with the forms
					foreach ($forms as $form)
					{
						$params = json_decode($form->params, true);

						$query->clear()
							->select('esc.id, esc.label,esc.profile_id')
							->from($db->quoteName('#__menu', 'm'))
							->leftJoin($db->quoteName('#__emundus_setup_profiles', 'esp') . ' ON ' . $db->quoteName('esp.menutype') . ' = ' . $db->quoteName('m.menutype'))
							->leftJoin($db->quoteName('#__emundus_setup_campaigns','esc').' ON '.$db->quoteName('esc.profile_id').' = '.$db->quoteName('esp.id'))
							->where($db->quoteName('m.link') . ' LIKE ' . $db->quote('index.php?option=com_fabrik&view=form&formid='.$form->id))
							->where($db->quoteName('esc.published') . ' = 1');
						$db->setQuery($query);
						$campaigns = $db->loadObjectList();

						if(!empty($campaigns))
						{
							foreach ($campaigns as $campaign) {
								$query->clear()
									->select('esa.value')
									->from($db->quoteName('#__emundus_setup_attachment_profiles','esap'))
									->leftJoin($db->quoteName('#__emundus_setup_attachments','esa').' ON '.$db->quoteName('esa.id').' = '.$db->quoteName('esap.attachment_id'))
									->where($db->quoteName('esap.profile_id') . ' = ' . $db->quote($campaign->profile_id))
									->where($db->quoteName('esap.attachment_id') . ' IN (4,6,21)');
								$db->setQuery($query);
								$attachment_letters = $db->loadColumn();

								if(count($attachment_letters) > 0)
								{
									$campaigns_step1[] = $campaign->id;

									$references_count = count(explode(',', $params['emails']));
									$email_tmpl       = $params['email_tmpl'];
									if (!empty($email_tmpl))
									{
										$query->clear()
											->select('id, subject')
											->from($db->quoteName('#__emundus_setup_emails'))
											->where($db->quoteName('lbl') . ' = ' . $db->quote($email_tmpl));
										$db->setQuery($query);
										$email_tmpl = $db->loadObject();
									}

									$setup       = [
										'campaign'        => $campaign->label,
										'references_count' => $references_count,
										'email_tmpl'       => $email_tmpl->subject,
										'email_tmpl_id'    => $email_tmpl->id,
										'attachments'      => implode(',',$attachment_letters),
									];
									$setups[1][] = $setup;
								}
							}

						}
					}
					
					break;
				case 2:
					// 1. We search forms with emundus_referent_letter plugin
					$query->clear()
						->select('id,params')
						->from($db->quoteName('#__fabrik_forms'))
						->where($db->quoteName('params') . ' LIKE ' . $db->quote('%emundusreferentletter%'));
					$db->setQuery($query);
					$forms = $db->loadObjectList();

					// 2. We search campaigns with the forms
					foreach ($forms as $form)
					{
						$params = json_decode($form->params, true);

						$query->clear()
							->select('esc.id,esc.label,esc.profile_id')
							->from($db->quoteName('#__menu', 'm'))
							->leftJoin($db->quoteName('#__emundus_setup_profiles', 'esp') . ' ON ' . $db->quoteName('esp.menutype') . ' = ' . $db->quoteName('m.menutype'))
							->leftJoin($db->quoteName('#__emundus_setup_campaigns','esc').' ON '.$db->quoteName('esc.profile_id').' = '.$db->quoteName('esp.id'))
							->where($db->quoteName('m.link') . ' LIKE ' . $db->quote('index.php?option=com_fabrik&view=form&formid='.$form->id))
							->where($db->quoteName('esc.published') . ' = 1')
							->group('esc.id');
						$db->setQuery($query);
						$campaigns = $db->loadObjectList();

						foreach ($campaigns as $campaign) {
							$query->clear()
								->select('count(id)')
								->from($db->quoteName('#__emundus_setup_attachment_profiles'))
								->where($db->quoteName('profile_id') . ' = ' . $db->quote($campaign->profile_id))
								->where($db->quoteName('attachment_id') . ' IN (4,6,21)');
							$db->setQuery($query);
							$attachment_letters = $db->loadResult();

							if($attachment_letters == 0) {
								$references_count = count(explode(',', $params['emails']));
								$email_tmpl       = $params['email_tmpl'];
								if(!empty($email_tmpl)) {
									$query->clear()
										->select('id, subject')
										->from($db->quoteName('#__emundus_setup_emails'))
										->where($db->quoteName('lbl') . ' = ' . $db->quote($email_tmpl));
									$db->setQuery($query);
									$email_tmpl = $db->loadObject();
								}
								$setup = [
									'campaign' => $campaign->label,
									'references_count' => $references_count,
									'email_tmpl' => $email_tmpl->subject,
									'email_tmpl_id' => $email_tmpl->id,
									'attachments' => null,
								];
								$setups[2][] = $setup;
							}
						}
					}

					break;
				case 3:
					if(!empty($attachments))
					{
						$query->clear()
							->select('esc.id, esc.label, esc.profile_id, group_concat(esa.value) as attachments')
							->from($db->quoteName('#__emundus_setup_campaigns', 'esc'))
							->leftJoin($db->quoteName('#__emundus_setup_attachment_profiles', 'esp') . ' ON ' . $db->quoteName('esp.profile_id') . ' = ' . $db->quoteName('esc.profile_id'))
							->leftJoin($db->quoteName('#__emundus_setup_attachments', 'esa') . ' ON ' . $db->quoteName('esa.id') . ' = ' . $db->quoteName('esp.attachment_id'))
							->where($db->quoteName('esp.attachment_id') . ' IN (' . implode(',', $db->quote($attachments)) . ')')
							->group('esc.id');
						$db->setQuery($query);
						$campaigns = $db->loadObjectList();

						if (!empty($campaigns))
						{
							foreach ($campaigns as $campaign)
							{
								if(!in_array($campaign->id, $campaigns_step1))
								{
									$setup       = [
										'campaign'         => $campaign->label,
										'references_count' => count(explode(',',$campaign->attachments)),
										'email_tmpl'       => null,
										'email_tmpl_id'    => null,
										'attachments'      => $campaign->attachments
									];
									$setups[3][] = $setup;
								}
							}
						}
					}
					break;
			}
		}

		return $setups;
	}
}
