DROP TABLE IF EXISTS `#__gafoodbank_foodorders`;

DELETE from `#__content_types` WHERE `type_alias` = 'com_gafoodbank';
DELETE from `#__content_types` WHERE `type_alias` = 'com_gafoodbank.category';
DELETE from `#__categories` WHERE `extension` = 'com_gafoodbank';
