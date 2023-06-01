CREATE TABLE data_currency LIKE jos_hikashop_currency;

INSERT INTO data_currency SELECT * FROM jos_hikashop_currency;

ALTER TABLE data_currency DROP COLUMN currency_percent_fee;
ALTER TABLE data_currency DROP COLUMN currency_modified;
ALTER TABLE data_currency DROP COLUMN currency_locale;
ALTER TABLE data_currency DROP COLUMN currency_rate;

ALTER TABLE data_currency RENAME COLUMN currency_id TO id;
ALTER TABLE data_currency RENAME COLUMN currency_name TO name;
ALTER TABLE data_currency RENAME COLUMN currency_symbol TO symbol;
ALTER TABLE data_currency RENAME COLUMN currency_code TO iso3;
ALTER TABLE data_currency RENAME COLUMN currency_format TO format;
ALTER TABLE data_currency RENAME COLUMN currency_displayed TO displayed;
ALTER TABLE data_currency RENAME COLUMN currency_published TO published;

UPDATE data_currency SET published = 1 WHERE iso3 = 'EUR';
UPDATE data_currency SET published = 1 WHERE iso3 = 'USD';
UPDATE data_currency SET published = 1 WHERE iso3 = 'GBP';
UPDATE data_currency SET published = 1 WHERE iso3 = 'JPY';
