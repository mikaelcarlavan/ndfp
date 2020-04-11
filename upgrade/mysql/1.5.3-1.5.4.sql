--
--

ALTER TABLE `llx_ndfp_det` ADD `cur_iso` VARCHAR(30) NOT NULL;
ALTER TABLE `llx_ndfp_det` ADD `rate` DOUBLE(24,8) NOT NULL DEFAULT 1;
ALTER TABLE `llx_ndfp_det` ADD `total_ht_cur` DOUBLE(24,8) NOT NULL DEFAULT 0;
ALTER TABLE `llx_ndfp_det` ADD `total_ttc_cur` DOUBLE(24,8) NOT NULL DEFAULT 0;