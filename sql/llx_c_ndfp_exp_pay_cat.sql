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


CREATE TABLE IF NOT EXISTS `llx_c_ndfp_exp_pay_cat`(
  `rowid`          int(11)  AUTO_INCREMENT,
  `label`           varchar(48) NOT NULL, 
  `fk_parent`		int(11) DEFAULT 1 NOT NULL,   
  `active`		int(11) DEFAULT 1 NOT NULL,	
  `autoclass`		int(11) DEFAULT 0 NOT NULL,	          
  PRIMARY KEY (`rowid`)    
)ENGINE=innodb DEFAULT CHARSET=utf8;


INSERT IGNORE INTO llx_c_ndfp_exp_pay_cat (`rowid`, `label`, `fk_parent`, `active`, `autoclass`) VALUES (1, 'TIP', 0, 1, 0);
INSERT IGNORE INTO llx_c_ndfp_exp_pay_cat (`rowid`, `label`, `fk_parent`, `active`, `autoclass`) VALUES (2, 'Virement', 0, 1, 0);
INSERT IGNORE INTO llx_c_ndfp_exp_pay_cat (`rowid`, `label`, `fk_parent`, `active`, `autoclass`) VALUES (3, 'Prélèvement', 0, 1, 0);
INSERT IGNORE INTO llx_c_ndfp_exp_pay_cat (`rowid`, `label`, `fk_parent`, `active`, `autoclass`) VALUES (4, 'Espèces', 0, 1, 0);
INSERT IGNORE INTO llx_c_ndfp_exp_pay_cat (`rowid`, `label`, `fk_parent`, `active`, `autoclass`) VALUES (5, 'Carte Bancaire', 0, 1, 0);
INSERT IGNORE INTO llx_c_ndfp_exp_pay_cat (`rowid`, `label`, `fk_parent`, `active`, `autoclass`) VALUES (6, 'Chèque', 0, 1, 0);

