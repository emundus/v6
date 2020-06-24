alter table jos_emundus_cifre_links
    add user_to_favorite tinyint null;

alter table jos_emundus_cifre_links
    add user_from_favorite tinyint null;

alter table jos_emundus_cifre_links
    add user_to_notify tinyint null;

alter table jos_emundus_cifre_links
    add user_from_notify tinyint null;
