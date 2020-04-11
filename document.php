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

$res=@include("../main.inc.php");					// For root directory
if (! $res) $res=@include("../../main.inc.php");	// For "custom" directory

require_once(DOL_DOCUMENT_ROOT."/core/class/html.form.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/html.formfile.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/functions2.lib.php");

dol_include_once("/ndfp/class/ndfp.class.php");
dol_include_once("/ndfp/lib/ndfp.lib.php");



$langs->load('ndfp@ndfp');
$langs->load('main');
$langs->load('other');

$id = GETPOST('id');
$ref = GETPOST('ref');
$confirm	= GETPOST('confirm');
$action = GETPOST('action');

if (!$user->rights->ndfp->myactions->read && !$user->rights->ndfp->allactions->read)
{
    accessforbidden();
}

$object = new Ndfp($db);


if ($id > 0 || !empty($ref))
{
    $result = $object->fetch($id, $ref);

    if ($result < 0)
    {
	    header("Location: ".dol_buildpath('/ndfp/index.php', 1));
    }

}
else
{
    header("Location: ".dol_buildpath('/ndfp/index.php', 1));
}

$error = false;
$message = false;
$formconfirm = false;

$html = new Form($db);
$formfile = new FormFile($db);

// Envoi fichier
if (GETPOST('sendit') && ! empty($conf->global->MAIN_UPLOAD_DOC))
{
	if ($object->id > 0)
    {
    	$upload_dir = $conf->ndfp->dir_output .'/'. dol_sanitizeFileName($object->ref);
    	$resupload = dol_add_file_process($upload_dir,0,1,'userfile');
    	
    	// Convert to PDF
    }
}

/*
if ($_POST["sendit"])
{

	

	if (dol_mkdir($upload_dir) >= 0)
	{
		dol_move_uploaded_file($_FILES['userfile']['tmp_name'], $upload_dir . "/" . $_FILES['userfile']['name'],0,0,$_FILES['userfile']['error']);

        if (is_numeric($resupload) && $resupload > 0)
		{
			$message = $langs->trans("FileTransferComplete");
            $error = false;
		}
		else
		{
			$langs->load("errors");
			$error = true;
			if ($resupload < 0)	// Unknown error
			{
				$message = $langs->trans("ErrorFileNotUploaded");
			}
			else if (preg_match('/ErrorFileIsInfectedWithAVirus/',$resupload))	// Files infected by a virus
			{
				$message = $langs->trans("ErrorFileIsInfectedWithAVirus");
			}
			else	// Known error
			{
				$message = $langs->trans("ErrorFileNotUploaded");
			}
		}
	}

}
*/
// Delete
if ($action == 'confirm_deletefile' && $confirm == 'yes')
{
	$upload_dir = $conf->ndfp->dir_output;// .'/'. dol_sanitizeFileName($object->ref);

	$file = $upload_dir . '/' . $_GET['urlfile'];
	dol_delete_file($file, 0, 0, 0, 'FILE_DELETE', $object);

	$message = $langs->trans("FileHasBeenRemoved");
}

// Get all files
$sortfield  = GETPOST("sortfield", 'alpha');
$sortorder  = GETPOST("sortorder", 'alpha');
$page       = GETPOST("page", 'int');

if ($page == -1 || empty($page))
{
    $page = 0;
}

$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

if (!$sortorder) $sortorder = "ASC";
if (!$sortfield) $sortfield = "name";


$upload_dir = $conf->ndfp->dir_output .'/'. dol_sanitizeFileName($object->ref);

$filearray = dol_dir_list($upload_dir, "files", 0, '', '\.meta$', $sortfield, (strtolower($sortorder) == 'desc' ? SORT_DESC : SORT_ASC), 1);
$totalsize = 0;
foreach($filearray as $key => $file)
{
	$totalsize += $file['size'];
}


if ($action == 'delete')
{
	$formconfirm = $html->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&urlfile='.urldecode($_GET["urlfile"]), $langs->trans('DeleteFile'), $langs->trans('ConfirmDeleteFile'), 'confirm_deletefile', '', 0, 0);
}

/**
/* Default view
**/
// Prepare head
$head = ndfp_prepare_head($object->id);
$current_head = 'documents';

$userstatic = new User($db);
$userstatic->fetch($object->fk_user);

$can_upload = 0;

if ($user->rights->ndfp->allactions->create)
{
    $can_upload = 1;
}

if ($object->fk_user == $user->id)
{
    $can_upload = 1;
}


include 'tpl/ndfp.document.tpl.php';

$db->close();

?>