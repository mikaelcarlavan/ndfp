--
--
ALTER TABLE `llx_ndfp_det` CHANGE `fk_tva` `fk_tva` DOUBLE NULL DEFAULT '0';
ALTER TABLE `llx_ndfp_tva_det` CHANGE `fk_tva` `fk_tva` DOUBLE NULL DEFAULT '0';
ALTER TABLE `llx_c_exp` CHANGE `fk_tva` `fk_tva` DOUBLE NULL DEFAULT '0';
ALTER TABLE `llx_c_exp` ADD `fk_product` INT NOT NULL DEFAULT '0' AFTER `fk_tva`;
UPDATE `llx_ndfp_det` nd LEFT JOIN `llx_c_tva` t ON t.rowid = nd.fk_tva SET nd.fk_tva = t.taux;
UPDATE `llx_ndfp_tva_det` nt LEFT JOIN `llx_c_tva` t ON t.rowid = nt.fk_tva SET nt.fk_tva = t.taux;
UPDATE `llx_c_exp` e LEFT JOIN `llx_c_tva` t ON t.rowid = e.fk_tva SET e.fk_tva = t.taux;
--
