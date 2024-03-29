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

CREATE TABLE IF NOT EXISTS `llx_ndfp`
(
    `rowid`             int(11) AUTO_INCREMENT,
    `cur_iso`           varchar(3)        NOT NULL,
    `ref`               varchar(30)       NOT NULL,
    `entity`            int(11) DEFAULT 1 NOT NULL,
    `datec`             datetime          NOT NULL,
    `dates`             datetime,
    `datee`             datetime,
    `datef`             date,
    `fk_user`           int(11)           NOT NULL,
    `fk_user_author`    int(11)           NOT NULL,
    `statut`            int(11) DEFAULT 0 NOT NULL,
    `fk_soc`            int(11) DEFAULT 0 NOT NULL,
    `fk_project`        int(11) DEFAULT 0 NOT NULL,
    `fk_cat`            int(11) DEFAULT 0,
    `fk_mode_reglement` int(11) DEFAULT 0,
    `billed`            int(11) DEFAULT 0,
    `previous_exp`      int(11) DEFAULT 0,
    `total_tva`         double(24,8)  DEFAULT 0,
    `total_ht`          double(24,8)  DEFAULT 0,
    `total_ttc`         double(24,8)  DEFAULT 0,
    `description`       text,
    `note_public`       text,
    `note_provate`      text,
    `comment_user`      text    DEFAULT NULL,
    `comment_admin`     text    DEFAULT NULL,
    `tms`               timestamp         NOT NULL,
    `date_valid`        datetime          NULL DEFAULT NULL,
    `fk_user_valid`     int(11) DEFAULT 0 NOT NULL,
    PRIMARY KEY(`rowid`)
) ENGINE = innodb
  DEFAULT CHARSET = utf8;
