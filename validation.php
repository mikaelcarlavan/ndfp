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
 *  \file       htdocs/ndfp/ndfp.php
 *  \ingroup    ndfp
 *  \brief      Page to create/modify/view a credit note or all credit notes
 */
define("NOLOGIN",'1');

$res=@include("../main.inc.php");                   // For root directory
if (! $res) $res=@include("../../main.inc.php");    // For "custom" directory

dol_include_once("/ndfp/class/ndfp.class.php");
dol_include_once("/ndfp/class/ndfp.val.class.php");
dol_include_once("/ndfp/class/html.form.ndfp.class.php");

require_once(DOL_DOCUMENT_ROOT."/core/class/html.form.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/functions2.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/date.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/doleditor.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/files.lib.php");


$langs->load('companies');
$langs->load('ndfp@ndfp');
$langs->load('main');


$hash = GETPOST('hash', 'alpha');
$action = GETPOST('action', 'alpha');


//Init error
$error = false;
$message = false;

/*
 * View
 */
$ndfp = new Ndfp($db);
$val = new NdfpVal($db);


$html = new Form($db);


$result = $val->fetch(0, $hash);

if ($result <= 0)
{
    $error = true;
    $message = $langs->trans('ValidationHasNotBeenFound');
}
else
{
    $id = $val->fk_ndfp ? $val->fk_ndfp : 0;
    $result = $ndfp->fetch($id);

    if ($result <= 0)
    {
        $error = true;
        $message = $langs->trans('NdfpDoesNotExist');
    }
    else
    {
        if ($action == 'accept')
        {
            $ndfp->validate($user);
            $val->delete($user);

            $error = false;
            $message = $langs->trans('NdfpHasBeenValidated');
        }
        else if ($action == 'refuse')
        {
            $ndfp->set_canceled($user);
            $val->delete($user);

            $error = false;
            $message = $langs->trans('NdfpHasBeenCanceled');
        }
        else
        {
            $error = true;
            $message = $langs->trans('ActionNotExist');        
        }
    }    
}

        
// Define logo and logosmall
$urlLogo = '';
$societyName = $mysoc->name;

if (! empty($mysoc->logo) && is_readable($conf->mycompany->dir_output.'/logos/'.$mysoc->logo))
{
    $urlLogo = DOL_URL_ROOT.'/viewimage.php?modulepart=companylogo&amp;file='.urlencode($mysoc->logo);
}
elseif (!empty($mysoc->logo_small) && is_readable($conf->mycompany->dir_output.'/logos/thumbs/'.$mysoc->logo_small))
{
    $urlLogo = DOL_URL_ROOT.'/viewimage.php?modulepart=companylogo&amp;file='.urlencode('thumbs/'.$mysoc->logo_small);
}

include 'tpl/ndfp.validation.tpl.php';

$db->close();

?>