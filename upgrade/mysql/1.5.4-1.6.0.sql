--
--
ALTER TABLE `llx_ndfp` ADD `fk_mode_reglement` INT(11) NOT NULL DEFAULT 0;
ALTER TABLE `llx_ndfp` ADD `billed` INT(11) NOT NULL DEFAULT 0;
--

-- Field billed
UPDATE `llx_ndfp` SET billed = 1 WHERE rowid IN (SELECT `fk_source` FROM `llx_element_element` WHERE `sourcetype` = 'ndfp' AND `targettype` = 'facture');