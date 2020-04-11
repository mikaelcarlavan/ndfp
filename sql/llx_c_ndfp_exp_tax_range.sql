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

CREATE TABLE IF NOT EXISTS `llx_c_ndfp_exp_tax_range`(
  `rowid`          int(11)  AUTO_INCREMENT,
  `fk_cat`		int(11) DEFAULT 1 NOT NULL,
  `range`        double DEFAULT 0 NOT NULL,   
  `active`		int(11) DEFAULT 1 NOT NULL,		          
  PRIMARY KEY (`rowid`)    
)ENGINE=innodb DEFAULT CHARSET=utf8;


INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (1, 4, 0, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (2, 4, 5000, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (3, 4, 20000, 1);

INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (4, 5, 0, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (5, 5, 5000, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (6, 5, 20000, 1);

INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (7, 6, 0, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (8, 6, 5000, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (9, 6, 20000, 1);

INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (10, 7, 0, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (11, 7, 5000, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (12, 7, 20000, 1);

INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (13, 8, 0, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (14, 8, 5000, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (15, 8, 20000, 1);
--
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (16, 9, 0, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (17, 9, 5000, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (18, 9, 20000, 1);
--  
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (19, 10, 0, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (20, 10, 3000, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (21, 10, 6000, 1);

INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (22, 11, 0, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (23, 11, 3000, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (24, 11, 6000, 1);

INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (25, 12, 0, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (26, 12, 3000, 1);
INSERT IGNORE INTO llx_c_ndfp_exp_tax_range (`rowid`, `fk_cat`, `range`, `active`) VALUES (27, 12, 6000, 1);
--


