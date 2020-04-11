--
--
ALTER TABLE `llx_ndfp` ADD `date_valid` DATETIME NOT NULL;
ALTER TABLE `llx_ndfp` ADD `fk_user_valid` INT NOT NULL;

ALTER TABLE `llx_ndfp_det` DROP `label`;
ALTER TABLE `llx_ndfp_det` ADD `comment` TEXT;
ALTER TABLE `llx_ndfp_det` ADD `ref_ext` VARCHAR(30) NOT NULL;

ALTER TABLE `llx_c_exp` ADD `accountancy_code`  VARCHAR(48) NOT NULL;


--
TRUNCATE TABLE `llx_c_exp_tax_cat`;

INSERT INTO llx_c_exp_tax_cat (`label`, `fk_parent`, `active`) values ('AutoCat', 0, 1);
INSERT INTO llx_c_exp_tax_cat (`label`, `fk_parent`, `active`) values ('CycloCat', 0, 1);
INSERT INTO llx_c_exp_tax_cat (`label`, `fk_parent`, `active`) values ('MotoCat', 0, 1);

INSERT INTO llx_c_exp_tax_cat (`label`, `fk_parent`, `active`) values ('Auto3CV', 1, 1);
INSERT INTO llx_c_exp_tax_cat (`label`, `fk_parent`, `active`) values ('Auto4CV', 1, 1);
INSERT INTO llx_c_exp_tax_cat (`label`, `fk_parent`, `active`) values ('Auto5CV', 1, 1);
INSERT INTO llx_c_exp_tax_cat (`label`, `fk_parent`, `active`) values ('Auto6CV', 1, 1);
INSERT INTO llx_c_exp_tax_cat (`label`, `fk_parent`, `active`) values ('Auto7PCV', 1, 1);

INSERT INTO llx_c_exp_tax_cat (`label`, `fk_parent`, `active`) values ('Cyclo', 2, 1);

INSERT INTO llx_c_exp_tax_cat (`label`, `fk_parent`, `active`) values ('Moto12CV', 3, 1);
INSERT INTO llx_c_exp_tax_cat (`label`, `fk_parent`, `active`) values ('Moto345CV', 3, 1);
INSERT INTO llx_c_exp_tax_cat (`label`, `fk_parent`, `active`) values ('Moto5PCV', 3, 1);
--

TRUNCATE TABLE `llx_c_exp_tax_range`;

INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (4, 0, 1);
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (4, 5000, 1);
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (4, 20000, 1);

INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (5, 0, 1);
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (5, 5000, 1);
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (5, 20000, 1);

INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (6, 0, 1);
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (6, 5000, 1);
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (6, 20000, 1);

INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (7, 0, 1);
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (7, 5000, 1);
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (7, 20000, 1);

INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (8, 0, 1);
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (8, 5000, 1);
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (8, 20000, 1);
--
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (9, 0, 1);
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (9, 5000, 1);
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (9, 20000, 1);
--  
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (10, 0, 1);
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (10, 3000, 1);
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (10, 6000, 1);

INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (11, 0, 1);
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (11, 3000, 1);
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (11, 6000, 1);

INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (12, 0, 1);
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (12, 3000, 1);
INSERT INTO llx_c_exp_tax_range (`fk_cat`, `range`, `active`) values (12, 6000, 1);
--

TRUNCATE TABLE `llx_c_exp_tax`;

INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (4, 1, 0.405, 0);
INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (4, 2, 0.242, 818);
INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (4, 3, 0.283, 0);

INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (5, 4, 0.487, 0);
INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (5, 5, 0.274, 1063);
INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (5, 6, 0.327, 0); 

INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (6, 7, 0.536, 0); 
INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (6, 8, 0.300, 1180); 
INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (6, 9, 0.359, 0); 

INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (7, 10, 0.561, 0); 
INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (7, 11, 0.316, 1223); 
INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (7, 12, 0.377, 0); 

INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (8, 13, 0.587, 0); 
INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (8, 14, 0.332, 1278); 
INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (8, 15, 0.396, 0); 

--

INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (9, 16, 0.266, 0); 
INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (9, 17, 0.063, 406); 
INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (9, 18, 0.144, 0);

--
INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (10, 19, 0.333, 0); 
INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (10, 20, 0.083, 750); 
INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (10, 21, 0.208, 0);

INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (11, 22, 0.395, 0); 
INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (11, 23, 0.069, 978); 
INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (11, 24, 0.232, 0);

INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (12, 25, 0.511, 0); 
INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (12, 26, 0.067, 1332); 
INSERT INTO llx_c_exp_tax (`fk_cat`, `fk_range`, `coef`, `offset`) values (12, 27, 0.289, 0);
