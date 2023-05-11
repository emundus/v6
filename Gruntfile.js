module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        joomla: {
            src: 'component/',
            build: 'build/'
        },
        /*sass: {
            dist: {
                options: {
                    sourcemap: 'none',
                    style: 'compressed'
                },
                files: {
                    '<%= joomla.build %>media/com_emundus/css/global.min.css': '<%= joomla.src %>media/com_emundus/scss/global.scss'
                }
            }
        },*/
        clean: {
            build: {
                src: ['<%= joomla.build %>']
            }
        },
        compress: {
            /* COMPONENT */
            component: {
                options: {
                    archive: function() {
                        return 'build/com_' + grunt.config.get('pkg.name') + '_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'components/com_emundus/', src: ['**/*'], dest: 'com_<%= pkg.name %>_<%= pkg.version %>/site/' },
                    { expand: true, cwd: 'administrator/components/com_emundus/', src: ['**/*'], dest: 'com_<%= pkg.name %>_<%= pkg.version %>/admin/' },
                    { expand: true, cwd: 'media/com_emundus/', src: ['**/*'], dest: 'com_<%= pkg.name %>_<%= pkg.version %>/media/' },
                    { expand: true, cwd: 'media/com_emundus/', src: ['**/*'], dest: 'com_<%= pkg.name %>_<%= pkg.version %>/media/' },
                    { expand: true, cwd: 'administrator/components/com_emundus/', src: ['emundus.xml'], dest: 'com_<%= pkg.name %>_<%= pkg.version %>/' },
                    { expand: true, cwd: 'administrator/components/com_emundus/', src: ['com_emundus.manifest.class.php'], dest: 'com_<%= pkg.name %>_<%= pkg.version %>/' },
                ]
            },
            /* LIBRARY */
            library: {
                options: {
                    archive: function() {
                        return 'build/lib_' + grunt.config.get('pkg.name') + '_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'libraries/emundus/', src: ['**/*'], dest: 'lib_<%= pkg.name %>_<%= pkg.version %>/' },
                ]
            },
            /* MODULES */
            mod_emundus_announcements: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_announcements_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_announcements/', src: ['**/*'], dest: 'mod_emundus_announcements_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_applications: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_applications_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_applications/', src: ['**/*'], dest: 'mod_emundus_applications_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_banner: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_banner_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_banner/', src: ['**/*'], dest: 'mod_emundus_banner_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_book_interview: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_book_interview_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_book_interview/', src: ['**/*'], dest: 'mod_emundus_book_interview_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_calendar_add: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_calendar_add_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_calendar_add/', src: ['**/*'], dest: 'mod_emundus_calendar_add_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_calendar_create_timeslots: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_calendar_create_timeslots_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_calendar_create_timeslots/', src: ['**/*'], dest: 'mod_emundus_calendar_create_timeslots_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_campaign: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_campaign_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_campaign/', src: ['**/*'], dest: 'mod_emundus_campaign_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_campaign_dropfiles: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_campaign_dropfiles_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_campaign_dropfiles/', src: ['**/*'], dest: 'mod_emundus_campaign_dropfiles_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_cas: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_cas_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_cas/', src: ['**/*'], dest: 'mod_emundus_cas_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_category_search: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_category_search_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_category_search/', src: ['**/*'], dest: 'mod_emundus_category_search_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_checklist: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_checklist_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_checklist/', src: ['**/*'], dest: 'mod_emundus_checklist_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_cifre_offers: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_cifre_offers_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_cifre_offers/', src: ['**/*'], dest: 'mod_emundus_cifre_offers_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_cifre_suggestions: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_cifre_suggestions_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_cifre_suggestions/', src: ['**/*'], dest: 'mod_emundus_cifre_suggestions_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_custom: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_custom_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_custom/', src: ['**/*'], dest: 'mod_emundus_custom_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_dashboard_vue: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_dashboard_vue_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_dashboard_vue/', src: ['**/*'], dest: 'mod_emundus_dashboard_vue_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_eb_googlemap: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_eb_googlemap_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_eb_googlemap/', src: ['**/*'], dest: 'mod_emundus_eb_googlemap_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_evaluations: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_evaluations_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_evaluations/', src: ['**/*'], dest: 'mod_emundus_evaluations_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_favorites: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_favorites_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_favorites/', src: ['**/*'], dest: 'mod_emundus_favorites_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_footer: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_footer_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_footer/', src: ['**/*'], dest: 'mod_emundus_footer_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_graphs: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_graphs_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_graphs/', src: ['**/*'], dest: 'mod_emundus_graphs_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_help: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_help_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_help/', src: ['**/*'], dest: 'mod_emundus_help_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_internet_explorer: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_internet_explorer_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_internet_explorer/', src: ['**/*'], dest: 'mod_emundus_internet_explorer_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_message_notification: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_message_notification_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_message_notification/', src: ['**/*'], dest: 'mod_emundus_message_notification_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_messenger_notifications: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_messenger_notifications_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_messenger_notifications/', src: ['**/*'], dest: 'mod_emundus_messenger_notifications_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_payment: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_payment_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_payment/', src: ['**/*'], dest: 'mod_emundus_payment_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_qcm: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_qcm_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_qcm/', src: ['**/*'], dest: 'mod_emundus_qcm_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_query_builder: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_query_builder_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_query_builder/', src: ['**/*'], dest: 'mod_emundus_query_builder_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_register: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_register_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_register/', src: ['**/*'], dest: 'mod_emundus_register_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_rsst_signalement_list: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_rsst_signalement_list_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_rsst_signalement_list/', src: ['**/*'], dest: 'mod_emundus_rsst_signalement_list_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_send_application: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_send_application_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_send_application/', src: ['**/*'], dest: 'mod_emundus_send_application_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_stat: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_stat_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_stat/', src: ['**/*'], dest: 'mod_emundus_stat_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_stat_filter: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_stat_filter_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_stat_filter/', src: ['**/*'], dest: 'mod_emundus_stat_filter_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_switch_profile: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_switch_profile_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_switch_profile/', src: ['**/*'], dest: 'mod_emundus_switch_profile_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_tutorial: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_tutorial_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_tutorial/', src: ['**/*'], dest: 'mod_emundus_tutorial_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_update: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_update_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_update/', src: ['**/*'], dest: 'mod_emundus_update_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_user_dropdown: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_user_dropdown_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_user_dropdown/', src: ['**/*'], dest: 'mod_emundus_user_dropdown_<%= pkg.version %>/' },
                ]
            },
            mod_emundus_yousign_embed: {
                options: {
                    archive: function() {
                        return 'build/mod_emundus_yousign_embed_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundus_yousign_embed/', src: ['**/*'], dest: 'mod_emundus_yousign_embed_<%= pkg.version %>/' },
                ]
            },
            mod_emundusflow: {
                options: {
                    archive: function() {
                        return 'build/mod_emundusflow_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundusflow/', src: ['**/*'], dest: 'mod_emundusflow_<%= pkg.version %>/' },
                ]
            },
            mod_emundusmenu: {
                options: {
                    archive: function() {
                        return 'build/mod_emundusmenu_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundusmenu/', src: ['**/*'], dest: 'mod_emundusmenu_<%= pkg.version %>/' },
                ]
            },
            mod_emunduspanel: {
                options: {
                    archive: function() {
                        return 'build/mod_emunduspanel_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emunduspanel/', src: ['**/*'], dest: 'mod_emunduspanel_<%= pkg.version %>/' },
                ]
            },
            mod_emundusuniv: {
                options: {
                    archive: function() {
                        return 'build/mod_emundusuniv_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emundusuniv/', src: ['**/*'], dest: 'mod_emundusuniv_<%= pkg.version %>/' },
                ]
            },
            mod_emunduswhosonline: {
                options: {
                    archive: function() {
                        return 'build/mod_emunduswhosonline_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'modules/mod_emunduswhosonline/', src: ['**/*'], dest: 'mod_emunduswhosonline_<%= pkg.version %>/' },
                ]
            },
            /* AUTHENTICATION PLUGINS */
            plg_emundus_authentication_emundus: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_authentication_emundus_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/authentication/emundus/', src: ['**/*'], dest: 'plg_emundus_authentication_emundus_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_authentication_emundus_oauth2: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_authentication_emundus_oauth2_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/authentication/emundus_oauth2/', src: ['**/*'], dest: 'plg_emundus_authentication_emundus_oauth2_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_authentication_emundus_oauth2_cci: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_authentication_emundus_oauth2_cci_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/authentication/emundus_oauth2_cci/', src: ['**/*'], dest: 'plg_emundus_authentication_emundus_oauth2_cci_<%= pkg.version %>/' },
                ]
            },
            /* EMUNDUS PLUGINS */
            plg_emundus_add_tag: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_add_tag_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/emundus/add_tag/', src: ['**/*'], dest: 'plg_emundus_add_tag_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_aurion_sync_setup_campaigns_excelia: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_aurion_sync_setup_campaigns_excelia_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/emundus/aurion_sync_setup_campaigns_excelia/', src: ['**/*'], dest: 'plg_emundus_aurion_sync_setup_campaigns_excelia_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_aurion_sync_setup_programs: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_aurion_sync_setup_programs_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/emundus/aurion_sync_setup_programs/', src: ['**/*'], dest: 'plg_emundus_aurion_sync_setup_programs_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_aurion_sync_setup_teaching_unity: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_aurion_sync_setup_teaching_unity_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/emundus/aurion_sync_setup_teaching_unity/', src: ['**/*'], dest: 'plg_emundus_aurion_sync_setup_teaching_unity_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_custom_event_handler: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_custom_event_handler_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/emundus/custom_event_handler/', src: ['**/*'], dest: 'plg_emundus_custom_event_handler_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_excelia_aurion_export: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_excelia_aurion_export_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/emundus/excelia_aurion_export/', src: ['**/*'], dest: 'plg_emundus_excelia_aurion_export_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_generate_opi_by_status: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_generate_opi_by_status_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/emundus/generate_opi_by_status/', src: ['**/*'], dest: 'plg_emundus_generate_opi_by_status_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_hesam_tutorial_events: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_hesam_tutorial_events_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/emundus/hesam_tutorial_events/', src: ['**/*'], dest: 'plg_emundus_hesam_tutorial_events_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_hopitaux_paris_auto_final_grade: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_hopitaux_paris_auto_final_grade_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/emundus/hopitaux_paris_auto_final_grade/', src: ['**/*'], dest: 'plg_emundus_hopitaux_paris_auto_final_grade_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_hopitaux_paris_create_reference: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_hopitaux_paris_create_reference_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/emundus/hopitaux_paris_create_reference/', src: ['**/*'], dest: 'plg_emundus_hopitaux_paris_create_reference_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_limit_obtained_alert: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_limit_obtained_alert_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/emundus/limit_obtained_alert/', src: ['**/*'], dest: 'plg_emundus_limit_obtained_alert_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_referent_status: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_referent_status_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/emundus/referent_status/', src: ['**/*'], dest: 'plg_emundus_referent_status_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_send_file_archive: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_send_file_archive_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/emundus/send_file_archive/', src: ['**/*'], dest: 'plg_emundus_send_file_archive_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_setup_category: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_setup_category_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/emundus/setup_category/', src: ['**/*'], dest: 'plg_emundus_setup_category_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_sync_file: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_sync_file_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/emundus/sync_file/', src: ['**/*'], dest: 'plg_emundus_sync_file_<%= pkg.version %>/' },
                ]
            },
            /* FABRIK CRON PLUGINS */
            plg_emundus_fabrik_cron_emundusapogee: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_cron_emundusapogee_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_cron/emundusapogee/', src: ['**/*'], dest: 'plg_emundus_fabrik_cron_emundusapogee_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_cron_emunduscampaignstartcall: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_cron_emunduscampaignstartcall_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_cron/emunduscampaignstartcall/', src: ['**/*'], dest: 'plg_emundus_fabrik_cron_emunduscampaignstartcall_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_cron_emunduscriteriaeval: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_cron_emunduscriteriaeval_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_cron/emunduscriteriaeval/', src: ['**/*'], dest: 'plg_emundus_fabrik_cron_emunduscriteriaeval_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_cron_emundusehespsiscole: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_cron_emundusehespsiscole_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_cron/emundusehespsiscole/', src: ['**/*'], dest: 'plg_emundus_fabrik_cron_emundusehespsiscole_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_cron_emundusevaluatorrecall: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_cron_emundusevaluatorrecall_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_cron/emundusevaluatorrecall/', src: ['**/*'], dest: 'plg_emundus_fabrik_cron_emundusevaluatorrecall_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_cron_emundusexportpdf: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_cron_emundusexportpdf_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_cron/emundusexportpdf/', src: ['**/*'], dest: 'plg_emundus_fabrik_cron_emundusexportpdf_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_cron_emundusglobalrecall: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_cron_emundusglobalrecall_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_cron/emundusglobalrecall/', src: ['**/*'], dest: 'plg_emundus_fabrik_cron_emundusglobalrecall_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_cron_emundushesamautounpublish: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_cron_emundushesamautounpublish_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_cron/emundushesamautounpublish/', src: ['**/*'], dest: 'plg_emundus_fabrik_cron_emundushesamautounpublish_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_cron_emundushesamrecap: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_cron_emundushesamrecap_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_cron/emundushesamrecap/', src: ['**/*'], dest: 'plg_emundus_fabrik_cron_emundushesamrecap_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_cron_emundusmessengernotify: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_cron_emundusmessengernotify_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_cron/emundusmessengernotify/', src: ['**/*'], dest: 'plg_emundus_fabrik_cron_emundusmessengernotify_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_cron_emundusnantesscholargpush: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_cron_emundusnantesscholargpush_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_cron/emundusnantesscholargpush/', src: ['**/*'], dest: 'plg_emundus_fabrik_cron_emundusnantesscholargpush_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_cron_emundusrecall: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_cron_emundusrecall_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_cron/emundusrecall/', src: ['**/*'], dest: 'plg_emundus_fabrik_cron_emundusrecall_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_cron_emundusrecallmissingdoc: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_cron_emundusrecallmissingdoc_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_cron/emundusrecallmissingdoc/', src: ['**/*'], dest: 'plg_emundus_fabrik_cron_emundusrecallmissingdoc_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_cron_emundusreferentrecall: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_cron_emundusreferentrecall_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_cron/emundusreferentrecall/', src: ['**/*'], dest: 'plg_emundus_fabrik_cron_emundusreferentrecall_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_cron_evaluatorwithtagsrecall: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_cron_evaluatorwithtagsrecall_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_cron/evaluatorwithtagsrecall/', src: ['**/*'], dest: 'plg_emundus_fabrik_cron_evaluatorwithtagsrecall_<%= pkg.version %>/' },
                ]
            },

            /* FABRIK FORM PLUGINS */
            plg_emundus_fabrik_form_emundusassigntoevaluator: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusassigntoevaluator_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusassigntoevaluator/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusassigntoevaluator_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusassigntogroup: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusassigntogroup_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusassigntogroup/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusassigntogroup_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusassigntoinstitution: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusassigntoinstitution_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusassigntoinstitution/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusassigntoinstitution_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusassigntousers: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusassigntousers_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusassigntousers/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusassigntousers_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emunduscampaign: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emunduscampaign_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emunduscampaign/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emunduscampaign_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emunduscampaigncheck: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emunduscampaigncheck_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emunduscampaigncheck/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emunduscampaigncheck_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emunduscompletefilestatuschange: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emunduscompletefilestatuschange_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emunduscompletefilestatuschange/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emunduscompletefilestatuschange_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusconfirmpost: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusconfirmpost_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusconfirmpost/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusconfirmpost_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusconfirmpostbyelement: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusconfirmpostbyelement_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusconfirmpostbyelement/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusconfirmpostbyelement_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusconfirmpostehesp: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusconfirmpostehesp_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusconfirmpostehesp/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusconfirmpostehesp_<%= pkg.version %>/' },
                ]
            },plg_emundus_fabrik_form_emundusconfirmpostgkqcm: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusconfirmpostgkqcm_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusconfirmpostgkqcm/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusconfirmpostgkqcm_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusconfirmrgpd: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusconfirmrgpd_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusconfirmrgpd/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusconfirmrgpd_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusdocusign: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusdocusign_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusdocusign/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusdocusign_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusduplicatedata: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusduplicatedata_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusduplicatedata/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusduplicatedata_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusencryptdatas: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusencryptdatas_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusencryptdatas/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusencryptdatas_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusexpertagreement: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusexpertagreement_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusexpertagreement/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusexpertagreement_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusfinalgrade: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusfinalgrade_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusfinalgrade/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusfinalgrade_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundushikashopaddtocart: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundushikashopaddtocart_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundushikashopaddtocart/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundushikashopaddtocart_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusimportcsv: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusimportcsv_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusimportcsv/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusimportcsv_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusisapplicationsent: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusisapplicationsent_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusisapplicationsent/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusisapplicationsent_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusisevaluatedbyme: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusisevaluatedbyme_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusisevaluatedbyme/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusisevaluatedbyme_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusisevaluationconfirmed: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusisevaluationconfirmed_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusisevaluationconfirmed/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusisevaluationconfirmed_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusisqcmcomplete: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusisqcmcomplete_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusisqcmcomplete/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusisqcmcomplete_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emunduspushfiletoapi: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emunduspushfiletoapi_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emunduspushfiletoapi/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emunduspushfiletoapi_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusredirect: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusredirect_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusredirect/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusredirect_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusreferentletter: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusreferentletter_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusreferentletter/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusreferentletter_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusrequestrgpd: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusrequestrgpd_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusrequestrgpd/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusrequestrgpd_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusrgpd: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusrgpd_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusrgpd/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusrgpd_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundussendemail: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundussendemail_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundussendemail/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundussendemail_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundussetprofile: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundussetprofile_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundussetprofile/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundussetprofile_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundussetprofilebyelement: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundussetprofilebyelement_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundussetprofilebyelement/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundussetprofilebyelement_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundussetstatus: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundussetstatus_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundussetstatus/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundussetstatus_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundustriggers: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundustriggers_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundustriggers/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundustriggers_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusupdatepdf: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusupdatepdf_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusupdatepdf/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusupdatepdf_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusupdatestatusonbeforeload: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusupdatestatusonbeforeload_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusupdatestatusonbeforeload/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusupdatestatusonbeforeload_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusyousign: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusyousign_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusyousign/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusyousign_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emunduszoommeeting: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emunduszoommeeting_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emunduszoommeeting/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emunduszoommeeting_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_form_emundusreferentletterhopitauxparis: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_form_emundusreferentletterhopitauxparis_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_form/emundusreferentletterhopitauxparis/', src: ['**/*'], dest: 'plg_emundus_fabrik_form_emundusreferentletterhopitauxparis_<%= pkg.version %>/' },
                ]
            },



            /* FABRIK ELEMENT PLUGINS */
            plg_emundus_fabrik_element_emundus_fileupload: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_element_emundus_fileupload_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_element/emundus_fileupload/', src: ['**/*'], dest: 'plg_emundus_fabrik_element_emundus_fileupload_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_fabrik_element_emundusreferent: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_fabrik_element_emundusreferent_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/fabrik_element/emundusreferent/', src: ['**/*'], dest: 'plg_emundus_fabrik_element_emundusreferent_<%= pkg.version %>/' },
                ]
            },
            /* HIKASHOP PLUGINS */
            plg_emundus_hikashop_emundus_hikashop: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_hikashop_emundus_hikashop_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/hikashop/emundus_hikashop/', src: ['**/*'], dest: 'plg_emundus_hikashop_emundus_hikashop_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_hikashop_emundusprice: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_hikashop_emundusprice_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/hikashop/emundusprice/', src: ['**/*'], dest: 'plg_emundus_hikashop_emundusprice_<%= pkg.version %>/' },
                ]
            },
            /* SEARCH PLUGINS */
            plg_emundus_search_emundus: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_search_emundus_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/search/emundus/', src: ['**/*'], dest: 'plg_emundus_search_emundus_<%= pkg.version %>/' },
                ]
            },
            /* SYSTEM PLUGINS */
            plg_emundus_system_emundus_ametys: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_system_emundus_ametys_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/system/emundus_ametys/', src: ['**/*'], dest: 'plg_emundus_system_emundus_ametys_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_system_emundus_block_user: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_system_emundus_block_user_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/system/emundus_block_user/', src: ['**/*'], dest: 'plg_emundus_system_emundus_block_user_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_system_emundus_caslogin: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_system_emundus_caslogin_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/system/emundus_caslogin/', src: ['**/*'], dest: 'plg_emundus_system_emundus_caslogin_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_system_emundus_conditional_redirect: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_system_emundus_conditional_redirect_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/system/emundus_conditional_redirect/', src: ['**/*'], dest: 'plg_emundus_system_emundus_conditional_redirect_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_system_emundus_period: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_system_emundus_period_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/system/emundus_period/', src: ['**/*'], dest: 'plg_emundus_system_emundus_period_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_system_emundusregistrationredirect: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_system_emundusregistrationredirect_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/system/emundusregistrationredirect/', src: ['**/*'], dest: 'plg_emundus_system_emundusregistrationredirect_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_system_emunduswaitingroom: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_system_emunduswaitingroom_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/system/emunduswaitingroom/', src: ['**/*'], dest: 'plg_emundus_system_emunduswaitingroom_<%= pkg.version %>/' },
                ]
            },
            /* USER PLUGINS */
            plg_emundus_user_emundus: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_user_emundus_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/user/emundus/', src: ['**/*'], dest: 'plg_emundus_user_emundus_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_user_emundus_ambassade_baudin: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_user_emundus_ambassade_baudin_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/user/emundus_ambassade_baudin/', src: ['**/*'], dest: 'plg_emundus_user_emundus_ambassade_baudin_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_user_emundus_ametys: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_user_emundus_ametys_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/user/emundus_ametys/', src: ['**/*'], dest: 'plg_emundus_user_emundus_ametys_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_user_emundus_assign_to_files: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_user_emundus_assign_to_files_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/user/emundus_assign_to_files/', src: ['**/*'], dest: 'plg_emundus_user_emundus_assign_to_files_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_user_emundus_claroline: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_user_emundus_claroline_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/user/emundus_claroline/', src: ['**/*'], dest: 'plg_emundus_user_emundus_claroline_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_user_emundus_password_update_email: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_user_emundus_password_update_email_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/user/emundus_password_update_email/', src: ['**/*'], dest: 'plg_emundus_user_emundus_password_update_email_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_user_emundus_profile: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_user_emundus_profile_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/user/emundus_profile/', src: ['**/*'], dest: 'plg_emundus_user_emundus_profile_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_user_emundus_registration_email: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_user_emundus_registration_email_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/user/emundus_registration_email/', src: ['**/*'], dest: 'plg_emundus_user_emundus_registration_email_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_user_emundus_su: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_user_emundus_su_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/user/emundus_su/', src: ['**/*'], dest: 'plg_emundus_user_emundus_su_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_user_emundus_su_csc: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_user_emundus_su_csc_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/user/emundus_su_csc/', src: ['**/*'], dest: 'plg_emundus_user_emundus_su_csc_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_user_emundus_su_emplois: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_user_emundus_su_emplois_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/user/emundus_su_emplois/', src: ['**/*'], dest: 'plg_emundus_user_emundus_su_emplois_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_user_emundus_su_forminnov: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_user_emundus_su_forminnov_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/user/emundus_su_forminnov/', src: ['**/*'], dest: 'plg_emundus_user_emundus_su_forminnov_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_user_emundus_su_pepite: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_user_emundus_su_pepite_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/user/emundus_su_pepite/', src: ['**/*'], dest: 'plg_emundus_user_emundus_su_pepite_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_user_emundus_su_tt_associate_user: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_user_emundus_su_tt_associate_user_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/user/emundus_su_tt_associate_user/', src: ['**/*'], dest: 'plg_emundus_user_emundus_su_tt_associate_user_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_user_emundus_univ_poitiers: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_user_emundus_univ_poitiers_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/user/emundus_univ_poitiers/', src: ['**/*'], dest: 'plg_emundus_user_emundus_univ_poitiers_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_user_emundus_user_recap: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_user_emundus_user_recap_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/user/emundus_user_recap/', src: ['**/*'], dest: 'plg_emundus_user_emundus_user_recap_<%= pkg.version %>/' },
                ]
            },
            plg_emundus_user_emundus_welcome_message: {
                options: {
                    archive: function() {
                        return 'build/plg_emundus_user_emundus_welcome_message_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    { expand: true, cwd: 'plugins/user/emundus_welcome_message/', src: ['**/*'], dest: 'plg_emundus_user_emundus_welcome_message_<%= pkg.version %>/' },
                ]
            },

            /* PACKAGE */
            package: {
                options: {
                    archive: function() {
                        return 'build/pkg_' + grunt.config.get('pkg.name') + '_' + grunt.config.get('pkg.version') + '.zip';
                    }
                },
                files: [
                    {
                        expand: true, cwd: 'build/', src: ['**'], dest: ''
                    },
                    { src: ['pkg_tchooz.xml','pkg_tchooz.manifest.class.php'] }
                ]
            }
        },
    });

    //grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-compress');

    grunt.registerTask('build', ['clean', 'compress']);
};
