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


CREATE TABLE IF NOT EXISTS `llx_c_ndfp_exp_tax_cat`(
  `rowid`          int(11)  AUTO_INCREMENT,
  `label`           varchar(48) NOT NULL, 
  `fk_parent`		int(11) DEFAULT 1 NOT NULL,   
  `active`		int(11) DEFAULT 1 NOT NULL,	          
  PRIMARY KEY (`rowid`)    
)ENGINE=innodb DEFAULT CHARSET=utf8;


INSERT IGNORE INTO llx_c_ndfp_exp_tax_cat (`rowid`, `label`, `fk_parent`, `active`) VALUES (1, 'AutoCat', 0, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_cat (`rowid`, `label`, `fk_parent`, `active`) VALUES (2, 'CycloCat', 0, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_cat (`rowid`, `label`, `fk_parent`, `active`) VALUES (3, 'MotoCat', 0, 1);

INSERT IGNORE INTO llx_c_ndfp_exp_tax_cat (`rowid`, `label`, `fk_parent`, `active`) VALUES (4, 'Auto3CV', 1, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_cat (`rowid`, `label`, `fk_parent`, `active`) VALUES (5, 'Auto4CV', 1, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_cat (`rowid`, `label`, `fk_parent`, `active`) VALUES (6, 'Auto5CV', 1, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_cat (`rowid`, `label`, `fk_parent`, `active`) VALUES (7, 'Auto6CV', 1, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_cat (`rowid`, `label`, `fk_parent`, `active`) VALUES (8, 'Auto7PCV', 1, 1);

INSERT IGNORE INTO llx_c_ndfp_exp_tax_cat (`rowid`, `label`, `fk_parent`, `active`) VALUES (9, 'Cyclo', 2, 1);

INSERT IGNORE INTO llx_c_ndfp_exp_tax_cat (`rowid`, `label`, `fk_parent`, `active`) VALUES (10, 'Moto12CV', 3, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_cat (`rowid`, `label`, `fk_parent`, `active`) VALUES (11, 'Moto345CV', 3, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_cat (`rowid`, `label`, `fk_parent`, `active`) VALUES (12, 'Moto5PCV', 3, 1);
