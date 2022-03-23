update jos_menu
set link = 'index.php?option=com_emundus&view=campaigns'
where link LIKE 'index.php?option=com_emundus_onboard&view=campaign';

update jos_falang_content
set value = 'index.php?option=com_emundus&view=campaigns'
where value LIKE 'index.php?option=com_emundus_onboard&view=campaign';

delete from jos_menu WHERE link LIKE 'index.php?option=com_emundus_onboard&view=program';
delete from jos_falang_content WHERE value LIKE 'index.php?option=com_emundus_onboard&view=program';

update jos_menu
set link = 'index.php?option=com_emundus&view=form'
where link LIKE 'index.php?option=com_emundus_onboard&view=form';

update jos_falang_content
set value = 'index.php?option=com_emundus&view=form'
where value LIKE 'index.php?option=com_emundus_onboard&view=form';

update jos_menu
set link = 'index.php?option=com_emundus&view=emails'
where link LIKE 'index.php?option=com_emundus_onboard&view=email';

update jos_falang_content
set value = 'index.php?option=com_emundus&view=emails'
where value LIKE 'index.php?option=com_emundus_onboard&view=email';

update jos_menu
set link = 'index.php?option=com_emundus&view=settings'
where link LIKE 'index.php?option=com_emundus_onboard&view=settings';

update jos_falang_content
set value = 'index.php?option=com_emundus&view=settings'
where value LIKE 'index.php?option=com_emundus_onboard&view=settings';
