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
 *      \file       htdocs/ndfp/upgrade/upgrade.php
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
$langs->load("install");

$versionFrom = GETPOST("from", '', 3);
$versionTo = GETPOST("to", '', 3);
$action = GETPOST("action");


$error = false;
$message = '';

if ($action != "upgrade")
{
    $error = true;
    $message = $langs->trans("ErrorWrongParameters");
}

$directory = "mysql/";	

$fileList = array();
$i = 0;
$ok = 0;
$from = '^'.$versionFrom;
$to = $versionTo.'\.sql$';

// Get files list
$filesInDirectory = array();
$h = opendir($directory);
if (is_resource($h))
{
    while (($file = readdir($h))!==false)
    {
        if (preg_match('/\.sql$/i', $file)) $filesInDirectory[] = $file;
    }
    sort($filesInDirectory);
}
else
{
    $error = true;
    $message = $langs->trans("ErrorCanNotReadDir", $dir);

}

// Define which file to run
foreach($filesInDirectory as $file)
{
    if (preg_match('/'.$from.'/i', $file))
    {
        $fileList[] = $file;
    }
    else if (preg_match('/'.$to.'/i', $file))	// First test may be false if we migrate from x.y.* to x.y.*
    {
        $fileList[] = $file;
    }
}

$head = ndfpadmin_prepare_head();
$current_head = 'upgrade';

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';

require_once("./../tpl/upgrade.default.tpl.php");

$db->close();
?>