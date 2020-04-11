<?php

/* Copyright (C) 2012      Mikael Carlavan        <contact@mika-carl.fr>
 *                                                http://www.mikael-carlavan.fr
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */


/**
 *      \file       htdocs/ndfp/upgrade/index.php
 *		\ingroup    ndfp
 *		\brief      Page to upgrade ndfp module
 */

$res=@include("./../../main.inc.php");				// For root directory
if (! $res) $res=@include("./../../../main.inc.php");	// For "custom" directory

require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");
dol_include_once("/ndfp/class/ndfp.class.php");
dol_include_once("/ndfp/lib/ndfp.lib.php");

$langs->load("admin");
$langs->load("companies");
$langs->load("ndfp@ndfp");
$langs->load("other");
$langs->load("errors");

$moduleVersion = '2.0.7';
$migrationScript = array(
		        array('from'=>'1.2.1', 'to'=>'1.2.2'),
                        array('from'=>'1.2.2', 'to'=>'1.2.3'),
                        array('from'=>'1.2.3', 'to'=>'1.3.1'), 
                        array('from'=>'1.3.1', 'to'=>'1.4.1'),
                        array('from'=>'1.4.1', 'to'=>'1.4.2'),
                        array('from'=>'1.5.3', 'to'=>'1.5.4'),
                        array('from'=>'1.5.4', 'to'=>'1.6.0'),
                        array('from'=>'1.6.0', 'to'=>'1.6.1'),
                        array('from'=>'1.6.1', 'to'=>'1.6.2'),
                        array('from'=>'1.6.2', 'to'=>'1.7.0'),
                        array('from'=>'1.7.0', 'to'=>'1.8.3'),
                        array('from'=>'1.8.3', 'to'=>'1.9.2'),
                        array('from'=>'1.9.2', 'to'=>'2.0.2'),
                        array('from'=>'2.0.2', 'to'=>'2.0.3'),
                        array('from'=>'2.0.3', 'to'=>'2.0.4'),
                        array('from'=>'2.0.4', 'to'=>'2.0.5'),
                        array('from'=>'2.0.5', 'to'=>'2.0.6'),
                        array('from'=>'2.0.6', 'to'=>'2.0.7')
                        );

$head = ndfpadmin_prepare_head();
$current_head = 'upgrade';

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';

       
require_once("./../tpl/upgrade.index.tpl.php");

$db->close();
?>