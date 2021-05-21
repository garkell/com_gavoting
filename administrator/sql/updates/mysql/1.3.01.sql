ALTER TABLE `#__gavoting_motions` ADD `votes_against` INT(11) NOT NULL DEFAULT '0' AFTER `votes`;
ALTER TABLE `#__gavoting_motions` CHANGE `votes` `votes_for` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__gavoting_voters` ADD `motion_id` INT(11) NOT NULL DEFAULT '0' AFTER `cat_id`;
