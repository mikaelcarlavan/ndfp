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

CREATE TABLE IF NOT EXISTS `llx_c_ndfp_exp_tax`(
  `rowid`          int(11)  AUTO_INCREMENT,
  `fk_cat`	int(11) DEFAULT 0 NOT NULL,
  `fk_range`	int(11) DEFAULT 0 NOT NULL,	  	  
  `coef`        double DEFAULT 0 NOT NULL,  
  `offset`      double DEFAULT 0 NOT NULL,	          
  PRIMARY KEY (`rowid`)    
)ENGINE=innodb DEFAULT CHARSET=utf8;


INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (1, 4, 1, 0.410, 0);
INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (2, 4, 2, 0.245, 824);
INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (3, 4, 3, 0.286, 0);

INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (4, 5, 4, 0.493, 0);
INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (5, 5, 5, 0.277, 1082);
INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (6, 5, 6, 0.332, 0);

INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (7, 6, 7, 0.543, 0);
INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (8, 6, 8, 0.305, 1188);
INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (9, 6, 9, 0.364, 0);

INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (10, 7, 10, 0.568, 0);
INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (11, 7, 11, 0.320, 1244);
INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (12, 7, 12, 0.382, 0);

INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (13, 8, 13, 0.595, 0);
INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (14, 8, 14, 0.337, 1288);
INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (15, 8, 15, 0.401, 0);

--

INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (16, 9, 16, 0.269, 0);
INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (17, 9, 17, 0.063, 412);
INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (18, 9, 18, 0.146, 0);

--
INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (19, 10, 19, 0.338, 0);
INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (20, 10, 20, 0.084, 760);
INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (21, 10, 21, 0.211, 0);

INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (22, 11, 22, 0.400, 0);
INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (23, 11, 23, 0.070, 989);
INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (24, 11, 24, 0.235, 0);

INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (25, 12, 25, 0.518, 0);
INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (26, 12, 26, 0.067, 1351);
INSERT IGNORE INTO llx_c_ndfp_exp_tax (`rowid`, `fk_cat`, `fk_range`, `coef`, `offset`) VALUES (27, 12, 27, 0.292, 0);
