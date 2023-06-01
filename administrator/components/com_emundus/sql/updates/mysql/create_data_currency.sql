create table data_currency
(
    id        int unsigned auto_increment
        primary key,
    symbol    varchar(255)          not null,
    iso3      varchar(255)          not null,
    format    char(10) default '%i' not null,
    name      varchar(255)          not null,
    published tinyint  default 0    not null,
    displayed tinyint  default 0    not null,
    constraint currency_code
        unique (iso3)
)
    engine = MyISAM
    charset = utf8mb3;
