SET autocommit = 0;

START TRANSACTION;

SET @menu_id = (SELECT id FROM jos_menu WHERE alias LIKE 'campagne-info-do-not-delete');


SET @mod_id = (
    SELECT GROUP_CONCAT(module.id separator ',')
    FROM jos_modules module
    LEFT JOIN jos_modules_menu menu ON menu.moduleid = module.id
    WHERE module.module = 'mod_jumi' AND module.published = 1 AND menu.menuid = @menu_id
    );

IF @mod_id IS NOT NULL
    THEN
        BEGIN
            ## UNPUBLISH UNNECESSARY JUMI
            UPDATE jos_modules SET published = 0  WHERE id IN (@mod_id);

            ## UNLINK THE MODULE FROM THE PROGRAM MENU
            DELETE FROM jos_modules_menu WHERE moduleid IN (@mod_id);
        END;
END IF;

IF @menu_id IS NOT NULL
    THEN
        BEGIN
            ## WE HAVE TO PUBLISH THE MENU TO GET THE PARAMS
            UPDATE jos_menu SET published = 1 WHERE id = @menu_id;
        END;
END IF;

## PUBLISH NEW CAMPAIGN MODULE
INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, publish_up, published, module, access, showtitle, params, client_id, language) VALUES (18, 'Single Campaign', '', '', 3, 'content-bottom-a', 0, '2020-07-27 10:15:23', 1, 'mod_emundus_campaign', 9, 0, '{"mod_em_campaign_intro":"","mod_em_campaign_list_tab":["current","futur","past","all"],"mod_em_campaign_groupby":"month","mod_em_campaign_orderby":"end_date","mod_em_campaign_order_type":"asc","mod_em_campaign_itemid":"2700","mod_em_campaign_itemid2":"1531","mod_em_campaign_layout":"single_campaign","mod_em_campaign_class":"blue","mod_em_campaign_link":"login","mod_em_campaign_date_format":"d\\/m\\/Y \\u00e0 H\\\\hi","mod_em_campaign_get_teaching_unity":"0","mod_em_campaign_get_link":"0","mod_em_campaign_show_camp_start_date":"0","mod_em_campaign_show_camp_end_date":"1","mod_em_campaign_show_formation_start_date":"0","mod_em_campaign_show_formation_end_date":"0","mod_em_campaign_show_admission_start_date":"0","mod_em_campaign_show_admission_end_date":"0","mod_em_campaign_show_nav_order":"1","mod_em_campaign_show_timezone":"1","mod_em_campaign_show_localedate":"0","mod_em_program_code":"","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"campaign-title","style":"0"}', 0, '*');

SET @new_mod := LAST_INSERT_ID();

## LINK THE MODULE TO THE MENU
INSERT INTO jos_modules_menu (moduleid, menuid) VALUES (@new_mod, @menu_id);

COMMIT;

