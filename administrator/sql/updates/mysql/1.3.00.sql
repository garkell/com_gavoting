CREATE TABLE IF NOT EXISTS #__gavoting_motions (
id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
ordering INT(11)  DEFAULT '0',
state TINYINT(1)  DEFAULT '0',
checked_out INT(11)  DEFAULT '0',
checked_out_time DATETIME NULL DEFAULT NULL ,
created_by INT(11)  DEFAULT '0',
modified_by INT(11)  DEFAULT '0',
created_date DATETIME NULL DEFAULT NULL ,
modified_date DATETIME NULL DEFAULT NULL ,
motion TEXT NULL ,
mov_date DATETIME NULL DEFAULT NULL ,
mov_id INT(11)  NULL ,
sec_id INT(11)  NULL ,
agreed TINYINT(1)  NULL ,
agreed_date DATETIME NULL DEFAULT NULL ,
votes INT(11)  NOT NULL DEFAULT '0',
comment TEXT NULL ,
PRIMARY KEY (id)
) DEFAULT COLLATE=utf8mb4_unicode_ci;