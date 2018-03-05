
--
-- Contraintes pour la table `jos_emundus_academic`
--
ALTER TABLE `jos_emundus_academic`
  ADD CONSTRAINT `jos_emundus_academic_ibfk_1` FOREIGN KEY (`user`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_academic_ibfk_2` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_admission`
--
ALTER TABLE `jos_emundus_admission`
  ADD CONSTRAINT `jos_emundus_admission_ibfk_1` FOREIGN KEY (`user`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_admission_ibfk_2` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_admission_ibfk_3` FOREIGN KEY (`campaign_id`) REFERENCES `jos_emundus_setup_campaigns` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_campaign_candidature`
--
ALTER TABLE `jos_emundus_campaign_candidature`
  ADD CONSTRAINT `jos_emundus_campaign_candidature_ibfk_1` FOREIGN KEY (`status`) REFERENCES `jos_emundus_setup_status` (`step`) ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_campaign_candidature_ibfk_2` FOREIGN KEY (`applicant_id`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_campaign_candidature_ibfk_3` FOREIGN KEY (`campaign_id`) REFERENCES `jos_emundus_setup_campaigns` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_comments`
--
ALTER TABLE `jos_emundus_comments`
  ADD CONSTRAINT `jos_emundus_comments_ibfk_1` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_comments_ibfk_2` FOREIGN KEY (`applicant_id`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_comments_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `jos_emundus_users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_comments_ibfk_4` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_cv`
--
ALTER TABLE `jos_emundus_cv`
  ADD CONSTRAINT `jos_emundus_cv_ibfk_1` FOREIGN KEY (`user`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_cv_ibfk_2` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_cv_573_repeat`
--
ALTER TABLE `jos_emundus_cv_573_repeat`
  ADD CONSTRAINT `jos_emundus_cv_573_repeat_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `jos_emundus_cv` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_declaration`
--
ALTER TABLE `jos_emundus_declaration`
  ADD CONSTRAINT `jos_emundus_declaration_ibfk_1` FOREIGN KEY (`user`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_declaration_ibfk_2` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_declaration_ibfk_3` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_emailalert`
--
ALTER TABLE `jos_emundus_emailalert`
  ADD CONSTRAINT `jos_emundus_emailalert_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_emailalert_ibfk_2` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_evaluations`
--
ALTER TABLE `jos_emundus_evaluations`
  ADD CONSTRAINT `jos_emundus_evaluations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_evaluations_ibfk_3` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_evaluations_ibfk_4` FOREIGN KEY (`user`) REFERENCES `jos_emundus_users` (`user_id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_files_request`
--
ALTER TABLE `jos_emundus_files_request`
  ADD CONSTRAINT `jos_emundus_files_request_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_files_request_ibfk_2` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_files_request_ibfk_3` FOREIGN KEY (`attachment_id`) REFERENCES `jos_emundus_setup_attachments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_files_request_ibfk_4` FOREIGN KEY (`campaign_id`) REFERENCES `jos_emundus_setup_campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_filters`
--
ALTER TABLE `jos_emundus_filters`
  ADD CONSTRAINT `jos_emundus_filters_ibfk_1` FOREIGN KEY (`user`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_final_grade`
--
ALTER TABLE `jos_emundus_final_grade`
  ADD CONSTRAINT `jos_emundus_final_grade_ibfk_3` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_final_grade_ibfk_4` FOREIGN KEY (`student_id`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_final_grade_ibfk_5` FOREIGN KEY (`user`) REFERENCES `jos_emundus_users` (`user_id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_funding`
--
ALTER TABLE `jos_emundus_funding`
  ADD CONSTRAINT `jos_emundus_funding_ibfk_1` FOREIGN KEY (`user`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_funding_ibfk_2` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_groups`
--
ALTER TABLE `jos_emundus_groups`
  ADD CONSTRAINT `jos_emundus_groups_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `jos_emundus_setup_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_groups_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_group_assoc`
--
ALTER TABLE `jos_emundus_group_assoc`
  ADD CONSTRAINT `jos_emundus_group_assoc_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `jos_emundus_setup_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_group_assoc_ibfk_2` FOREIGN KEY (`action_id`) REFERENCES `jos_emundus_setup_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_group_assoc_ibfk_3` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_languages`
--
ALTER TABLE `jos_emundus_languages`
  ADD CONSTRAINT `jos_emundus_languages_ibfk_1` FOREIGN KEY (`user`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_languages_ibfk_2` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_languages_583_repeat`
--
ALTER TABLE `jos_emundus_languages_583_repeat`
  ADD CONSTRAINT `jos_emundus_languages_583_repeat_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `jos_emundus_languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_languages_584_repeat`
--
ALTER TABLE `jos_emundus_languages_584_repeat`
  ADD CONSTRAINT `jos_emundus_languages_584_repeat_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `jos_emundus_languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_languages_585_repeat`
--
ALTER TABLE `jos_emundus_languages_585_repeat`
  ADD CONSTRAINT `jos_emundus_languages_585_repeat_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `jos_emundus_languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_personal_detail`
--
ALTER TABLE `jos_emundus_personal_detail`
  ADD CONSTRAINT `jos_emundus_personal_detail_ibfk_1` FOREIGN KEY (`user`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_personal_detail_ibfk_2` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_references`
--
ALTER TABLE `jos_emundus_references`
  ADD CONSTRAINT `jos_emundus_references_ibfk_1` FOREIGN KEY (`user`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_references_ibfk_2` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_residences`
--
ALTER TABLE `jos_emundus_residences`
  ADD CONSTRAINT `jos_emundus_residences_ibfk_1` FOREIGN KEY (`user`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_residences_ibfk_2` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_setup_attachment_profiles`
--
ALTER TABLE `jos_emundus_setup_attachment_profiles`
  ADD CONSTRAINT `jos_emundus_setup_attachment_profiles_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `jos_emundus_setup_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_setup_attachment_profiles_ibfk_2` FOREIGN KEY (`attachment_id`) REFERENCES `jos_emundus_setup_attachments` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_setup_emails`
--
ALTER TABLE `jos_emundus_setup_emails`
  ADD CONSTRAINT `jos_emundus_setup_emails_ibfk_1` FOREIGN KEY (`email_tmpl`) REFERENCES `jos_emundus_email_templates` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_setup_emails_trigger`
--
ALTER TABLE `jos_emundus_setup_emails_trigger`
  ADD CONSTRAINT `jos_emundus_setup_emails_trigger_ibfk_1` FOREIGN KEY (`step`) REFERENCES `jos_emundus_setup_status` (`step`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_setup_emails_trigger_ibfk_2` FOREIGN KEY (`programme_id`) REFERENCES `jos_emundus_setup_programmes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_setup_emails_trigger_ibfk_3` FOREIGN KEY (`email_id`) REFERENCES `jos_emundus_setup_emails` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_setup_emails_trigger_repeat_group_id`
--
ALTER TABLE `jos_emundus_setup_emails_trigger_repeat_group_id`
  ADD CONSTRAINT `jos_emundus_setup_emails_trigger_repeat_group_id_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `jos_emundus_setup_emails_trigger` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_setup_emails_trigger_repeat_profile_id`
--
ALTER TABLE `jos_emundus_setup_emails_trigger_repeat_profile_id`
  ADD CONSTRAINT `jos_emundus_setup_emails_trigger_repeat_profile_id_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `jos_emundus_setup_emails_trigger` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_setup_emails_trigger_repeat_programme_id`
--
ALTER TABLE `jos_emundus_setup_emails_trigger_repeat_programme_id`
  ADD CONSTRAINT `jos_emundus_setup_emails_trigger_repeat_programme_id_ibfk_1` FOREIGN KEY (`programme_id`) REFERENCES `jos_emundus_setup_programmes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_setup_emails_trigger_repeat_programme_id_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `jos_emundus_setup_emails_trigger` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_setup_emails_trigger_repeat_user_id`
--
ALTER TABLE `jos_emundus_setup_emails_trigger_repeat_user_id`
  ADD CONSTRAINT `jos_emundus_setup_emails_trigger_repeat_user_id_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `jos_emundus_setup_emails_trigger` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_setup_groups_repeat_course`
--
ALTER TABLE `jos_emundus_setup_groups_repeat_course`
  ADD CONSTRAINT `jos_emundus_setup_groups_repeat_course_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `jos_emundus_setup_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_survey`
--
ALTER TABLE `jos_emundus_survey`
  ADD CONSTRAINT `jos_emundus_survey_ibfk_1` FOREIGN KEY (`user`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_survey_ibfk_2` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_tag_assoc`
--
ALTER TABLE `jos_emundus_tag_assoc`
  ADD CONSTRAINT `jos_emundus_tag_assoc_ibfk_2` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_tag_assoc_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `jos_emundus_users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_tag_assoc_ibfk_4` FOREIGN KEY (`applicant_id`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_uploads`
--
ALTER TABLE `jos_emundus_uploads`
  ADD CONSTRAINT `jos_emundus_uploads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_uploads_ibfk_2` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_uploads_ibfk_3` FOREIGN KEY (`attachment_id`) REFERENCES `jos_emundus_setup_attachments` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_users`
--
ALTER TABLE `jos_emundus_users`
  ADD CONSTRAINT `jos_emundus_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `jos_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_users_assoc`
--
ALTER TABLE `jos_emundus_users_assoc`
  ADD CONSTRAINT `jos_emundus_users_assoc_ibfk_1` FOREIGN KEY (`fnum`) REFERENCES `jos_emundus_campaign_candidature` (`fnum`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_users_assoc_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_users_assoc_ibfk_3` FOREIGN KEY (`action_id`) REFERENCES `jos_emundus_setup_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_users_profiles`
--
ALTER TABLE `jos_emundus_users_profiles`
  ADD CONSTRAINT `jos_emundus_users_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_users_profiles_ibfk_2` FOREIGN KEY (`profile_id`) REFERENCES `jos_emundus_setup_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_emundus_users_profiles_history`
--
ALTER TABLE `jos_emundus_users_profiles_history`
  ADD CONSTRAINT `jos_emundus_users_profiles_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_emundus_users_profiles_history_ibfk_2` FOREIGN KEY (`profile_id`) REFERENCES `jos_emundus_setup_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jos_jcrm_group_contact`
--
ALTER TABLE `jos_jcrm_group_contact`
  ADD CONSTRAINT `jos_jcrm_group_contact_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `jos_jcrm_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jos_jcrm_group_contact_ibfk_2` FOREIGN KEY (`contact_id`) REFERENCES `jos_jcrm_contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
