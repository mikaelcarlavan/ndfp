--
--
--
ALTER TABLE `llx_ndfp_det` ADD `fk_cat` INT NOT NULL DEFAULT '0';
ALTER TABLE `llx_ndfp_det` ADD `previous_exp` INT NOT NULL DEFAULT '0';
UPDATE `llx_ndfp_det` nd LEFT JOIN `llx_ndfp` n ON nd.fk_ndfp = n.rowid SET nd.fk_cat = n.fk_cat WHERE 1;
