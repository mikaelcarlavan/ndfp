-- ============================================================================
-- Copyright (C) 2012 Mikael Carlavan  <mcarlavan@qis-network.com>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- ============================================================================

CREATE TABLE IF NOT EXISTS `llx_c_ndfp_exp`(
  `rowid`          int(11)  AUTO_INCREMENT,
  `code`         varchar(12) UNIQUE NOT NULL,
  `label`      varchar(48) NOT NULL,
  `fk_tva`		double(24,8)  DEFAULT 0,  
  `fk_product`     int(11) DEFAULT 0 NOT NULL,  
  `active`		int(11) DEFAULT 1 NOT NULL,	
  `accountancy_code`  varchar(48) NOT NULL,
  PRIMARY KEY (`rowid`)    
)ENGINE=innodb DEFAULT CHARSET=utf8;

INSERT IGNORE INTO llx_c_ndfp_exp (`rowid`, `code`, `label`, `fk_tva`, `fk_product`, `active`, `accountancy_code`) VALUES (1, 'EX_FLI', 'Flight', 5.5, 0, 1, '');
INSERT IGNORE INTO llx_c_ndfp_exp (`rowid`, `code`, `label`, `fk_tva`, `fk_product`, `active`, `accountancy_code`) VALUES (2, 'EX_TRA', 'Train', 5.5, 0, 1, '');
INSERT IGNORE INTO llx_c_ndfp_exp (`rowid`, `code`, `label`, `fk_tva`, `fk_product`, `active`, `accountancy_code`) VALUES (3, 'EX_TAX', 'Taxi', 5.5, 0, 1, '');
INSERT IGNORE INTO llx_c_ndfp_exp (`rowid`, `code`, `label`, `fk_tva`, `fk_product`, `active`, `accountancy_code`) VALUES (4, 'EX_RES', 'Restaurant', 10, 0, 1, '');
INSERT IGNORE INTO llx_c_ndfp_exp (`rowid`, `code`, `label`, `fk_tva`, `fk_product`, `active`, `accountancy_code`) VALUES (5, 'EX_OTH', 'Other', 0, 0, 1, '');
INSERT IGNORE INTO llx_c_ndfp_exp (`rowid`, `code`, `label`, `fk_tva`, `fk_product`, `active`, `accountancy_code`) VALUES (6, 'EX_FUE', 'Fuel', 20, 0, 1, '');
INSERT IGNORE INTO llx_c_ndfp_exp (`rowid`, `code`, `label`, `fk_tva`, `fk_product`, `active`, `accountancy_code`) VALUES (7, 'EX_KME', 'Km', 0, 0, 1, '');
INSERT IGNORE INTO llx_c_ndfp_exp (`rowid`, `code`, `label`, `fk_tva`, `fk_product`, `active`, `accountancy_code`) VALUES (8, 'EX_HOT', 'Hotel', 10, 0, 1, '');
INSERT IGNORE INTO llx_c_ndfp_exp (`rowid`, `code`, `label`, `fk_tva`, `fk_product`, `active`, `accountancy_code`) VALUES (9, 'EX_PAR', 'Parking', 20, 0, 1, '');
INSERT IGNORE INTO llx_c_ndfp_exp (`rowid`, `code`, `label`, `fk_tva`, `fk_product`, `active`, `accountancy_code`) VALUES (10, 'EX_TOL', 'Toll', 20, 0, 1, '');



