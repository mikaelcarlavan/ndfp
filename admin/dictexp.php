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
 *      \file       htdocs/ndfp/admin/dictexp.php
 *		\ingroup    ndfp
 *		\brief      Page to display expense types
 */

$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory

require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");
dol_include_once("/ndfp/class/ndfp.class.php");
dol_include_once("/ndfp/lib/ndfp.lib.php");

$langs->load("admin");
$langs->load("ndfp@ndfp");
$langs->load("other");
$langs->load("errors");

if (!$user->admin)
{
   accessforbidden();
}

//Init error
$error = false;
$message = false;

$acts[0] = "activate";
$acts[1] = "disable";
$actl[0] = img_picto($langs->trans("Disabled"),'off');
$actl[1] = img_picto($langs->trans("Activated"),'on');

$listoffset = GETPOST('listoffset');
$listlimit = GETPOST('listlimit')>0 ? GETPOST('listlimit') : 1000;

$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page",'int');

$action = GETPOST("action",'alpha');
$confirm = GETPOST("confirm",'alpha');

$active = GETPOST("active");
$fk_tva = GETPOST("fk_tva") ? GETPOST("fk_tva") : 0;
$fk_product = GETPOST("fk_product", "int") ? GETPOST("fk_product", "int") : 0;
$label = GETPOST("label");
$code = strtoupper(GETPOST("code"));
$accountancy_code = GETPOST("accountancy_code");

$rowid = GETPOST("rowid",'int');

if ($page == -1 || empty($page))
{
    $page = 0 ;
}

$offset = $listlimit * $page ;
$pageprev = $page - 1;
$pagenext = $page + 1;

$cancel = (isset($_POST['actioncancel']) ? true : false);
$add = (isset($_POST['actionadd']) ? true : false);
$modify = (isset($_POST['actionmodify']) ? true : false);

//
$html = new Form($db);

$formconfirm = '';

if ($action == 'delete')
{
    $formconfirm = $html->formconfirm($_SERVER["PHP_SELF"].'?'.($page?'page='.$page.'&':'').'sortfield='.$sortfield.'&sortorder='.$sortorder.'&rowid='.$rowid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_delete','', 'no', 0);
}

if ($add || $modify)
{
    if (empty($code) || empty($label))
    {
        $error = true;
        $message = $langs->trans('MissionField');
    }

    // Check TVA
    $tva_id = floatval($fk_tva);

    if (!$error)
    {

        if ($add)
        {
            $sql  = " INSERT INTO ".MAIN_DB_PREFIX."c_ndfp_exp (`code`, `label`, `fk_tva`, `active`, `accountancy_code`, `fk_product`)";
            $sql .= " VALUES ('".$db->escape($code)."', '".$db->escape($label)."', ".$tva_id.", 1,";
            $sql .= " '".$db->escape($accountancy_code)."', ".$fk_product.")";
        }
        else
        {
            $sql  = " UPDATE ".MAIN_DB_PREFIX."c_ndfp_exp  SET `label` = '".$db->escape($label)."', `code` = '".$db->escape($code)."',";
            $sql .= " `fk_tva` = ".$tva_id.", `active` = ".$active.", `fk_product` = ".$fk_product.",";
            $sql .= " `accountancy_code` = '".$db->escape($accountancy_code)."'";
            $sql .= " WHERE `rowid` = ".$rowid;
        }

        dol_syslog("Dictexp sql=".$sql);

        $result = $db->query($sql);

        if ($result > 0)
        {
            $message = ($add ? $langs->trans('ExpAdded') : $langs->trans('ExpUpdated'));
        }
        else
        {
            $error = true;
            $message = ($add ? $langs->trans('ExpNotAdded') : $langs->trans('ExpNotUpdated'));
        }
    }
}

if ($cancel)
{
   $rowid = 0;
}

if ($action == $acts[0])       // activate
{
    $sql = "UPDATE ".MAIN_DB_PREFIX."c_ndfp_exp SET `active` = 1 WHERE rowid = ".$rowid;
    dol_syslog("Dictexp sql=".$sql);

    $result = $db->query($sql);
    if ($result)
    {
        $message = $langs->trans('ExpUpdated');
    }
    else
    {
        $error = true;
        $message = $langs->trans('ExpNotUpdated');
    }
}

if ($action == $acts[1])       // disable
{
    $sql = "UPDATE ".MAIN_DB_PREFIX."c_ndfp_exp SET `active` = 0 WHERE rowid = ".$rowid;
    dol_syslog("Dictexp sql=".$sql);

    $result = $db->query($sql);
    if ($result > 0)
    {
        $message = $langs->trans('ExpUpdated');
    }
    else
    {
        $error = true;
        $message = $langs->trans('ExpNotUpdated');
    }
}

if ($action == 'confirm_delete' && $confirm == 'yes')       // delete
{

    $sql = "DELETE FROM ".MAIN_DB_PREFIX."c_ndfp_exp WHERE rowid = ".$rowid;

    dol_syslog("Dictexp sql=".$sql);
    $result = $db->query($sql);

    if ($result > 0)
    {
        $message = $langs->trans('ExpDeleted');
    }
    else
    {
        $error = true;
        $message = $langs->trans('ExpNotDeleted');
    }
}

//
$exps = array();

$sql  = " SELECT e.rowid, e.label, e.code, e.fk_tva as taux, e.active, e.accountancy_code, e.fk_product, p.label as product_label";
$sql .= " FROM ".MAIN_DB_PREFIX."c_ndfp_exp e";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product p ON p.rowid = e.fk_product";
$sql .= " WHERE 1";
$sql .= (!empty($sortfield) ? " ORDER BY ".$sortfield : " ORDER BY e.code");
$sql .= (!empty($sortorder) ? " ".strtoupper($sortorder) : " ASC");
$sql .= $db->plimit($listlimit+1,$offset);


dol_syslog("Dictexp sql=".$sql, LOG_DEBUG);
$result = $db->query($sql);


if ($result)
{
    $num = $db->num_rows($result);
    $i = 0;

    if ($num)
    {
        while ($i < $num)
        {
            $obj = $db->fetch_object($result);

            $exps[$i] = $obj;
            $exps[$i]->activate = '<a href="'.$_SERVER["PHP_SELF"].'?'.($page?'page='.$page.'&':'').'sortfield='.$sortfield.'&sortorder='.$sortorder.'&rowid='.$obj->rowid.'&amp;action='.$acts[$obj->active].'">'.$actl[$obj->active].'</a>';
            $exps[$i]->modify = '<a href="'.$_SERVER["PHP_SELF"].'?'.($page?'page='.$page.'&':'').'sortfield='.$sortfield.'&sortorder='.$sortorder.'&rowid='.$obj->rowid.'&amp;action=modify">'.img_edit().'</a>';
            $exps[$i]->delete = '<a href="'.$_SERVER["PHP_SELF"].'?'.($page?'page='.$page.'&':'').'sortfield='.$sortfield.'&sortorder='.$sortorder.'&rowid='.$obj->rowid.'&amp;action=delete">'.img_delete().'</a>';

            $i++;
        }
    }
}


$head = ndfpadmin_prepare_head(); 
$current_head = 'dict';



$linkback = '<a href="'.dol_buildpath('/ndfp/admin/dict.php', 1).'">'.$langs->trans("BackToDictionnariesList").'</a>';
/*
 * View
 */

require_once("../tpl/admin.dictexp.tpl.php");

$db->close();

?>
