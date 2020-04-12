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

dol_include_once("/ndfp/class/ndfp.class.php");
dol_include_once("/ndfp/lib/ndfp.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/html.form.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/functions2.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/doleditor.class.php");


$langs->load('companies');
$langs->load('ndfp@ndfp');
$langs->load('main');

$id = GETPOST('id');
$ref = GETPOST('ref');

$action = GETPOST('action');

if (!$user->rights->ndfp->myactions->read && !$user->rights->ndfp->allactions->read)
{
    accessforbidden();
}

//Init error
$error = false;
$message = false;


$ndfp = new Ndfp($db);


if ($id > 0 || !empty($ref))
{
    $result = $ndfp->fetch($id, $ref);

    if ($result < 0)
    {
	    header("Location: ".dol_buildpath('/ndfp/list.php', 1));
    }
}
else
{
    header("Location: ".dol_buildpath('/ndfp/list.php', 1));
}



if ($action == 'setcomments')
{
    $result = $ndfp->call($action, array($user));

    if ($result > 0)
    {

        $message = $ndfp->error; //
    }
    else
    {
        $message = $ndfp->error;
        $error = true;
    }
}



$html = new Form($db);

// Prepare head
$head = ndfp_prepare_head($ndfp->id);
$current_head = 'notes';

$societestatic = new Societe($db);
$userstatic = new User($db);


$societestatic->fetch($ndfp->fk_soc);
$userstatic->fetch($ndfp->fk_user);

$button = '';
if ($user->rights->ndfp->myactions->create){
    if ($action == 'edit'){
        $button = '<input type="submit" class="button" name="bouton" value="'.$langs->trans('Validate').'" />';
    }else{
        $button = '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$ndfp->id.'&amp;action=edit">'.$langs->trans('Modify').'</a>';
    }
}else{
    accessforbidden();
}

$userCommentEditor = new DolEditor('comment_user', $ndfp->comment_user, '',200,'dolibarr_notes','',false,true, isset($conf->fckeditor) ? $conf->fckeditor->enabled : false, ROWS_6,50);
$adminCommentEditor = new DolEditor('comment_admin', $ndfp->comment_admin, '',200,'dolibarr_notes','',false,true, isset($conf->fckeditor) ? $conf->fckeditor->enabled : false, ROWS_6,50);


include 'tpl/ndfp.note.tpl.php';

$db->close();


?>
