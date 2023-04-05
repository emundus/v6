CREATE TABLE IF NOT EXISTS "#__securitycheckpro_blacklist" (
"ip" character varying(26) NOT NULL,
PRIMARY KEY ("ip")
);

CREATE TABLE IF NOT EXISTS "#__securitycheckpro_whitelist" (
"ip" character varying(26) NOT NULL,
PRIMARY KEY ("ip")
);