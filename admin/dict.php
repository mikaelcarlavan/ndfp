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
 *      \file       htdocs/ndfp/admin/dict.php
 *		\ingroup    ndfp
 *		\brief      Page to display dictionaries for module
 */

$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory

require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");
dol_include_once('/ndfp/class/ndfp.class.php');
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


//
$tables = array();

$c_ndfp_exp = new StdClass();
$c_ndfp_exp->name = "DictionnaryExp";
$c_ndfp_exp->mysqlname = MAIN_DB_PREFIX."c_ndfp_exp";
$c_ndfp_exp->url = dol_buildpath('/ndfp/admin/dictexp.php', 1);

$c_ndfp_exp_tax_range = new StdClass();
$c_ndfp_exp_tax_range->name = "DictionnaryTaxRange";
$c_ndfp_exp_tax_range->mysqlname = MAIN_DB_PREFIX."c_ndfp_exp_tax_range";
$c_ndfp_exp_tax_range->url = dol_buildpath('/ndfp/admin/dictexptaxrange.php', 1);

$c_ndfp_exp_tax_cat = new StdClass();
$c_ndfp_exp_tax_cat->name = "DictionnaryTaxCat";
$c_ndfp_exp_tax_cat->mysqlname = MAIN_DB_PREFIX."c_ndfp_exp_tax_cat";
$c_ndfp_exp_tax_cat->url = dol_buildpath('/ndfp/admin/dictexptaxcat.php', 1);

$c_ndfp_exp_pay_cat = new StdClass();
$c_ndfp_exp_pay_cat->name = "DictionnaryPayCat";
$c_ndfp_exp_pay_cat->mysqlname = MAIN_DB_PREFIX."c_ndfp_exp_pay_cat";
$c_ndfp_exp_pay_cat->url = dol_buildpath('/ndfp/admin/dictexppaycat.php', 1);

$c_ndfp_exp_tax = new StdClass();
$c_ndfp_exp_tax->name = "DictionnaryTax";
$c_ndfp_exp_tax->mysqlname = MAIN_DB_PREFIX."c_ndfp_exp_tax";
$c_ndfp_exp_tax->url = dol_buildpath('/ndfp/admin/dictexptax.php', 1);


$tables[] = $c_ndfp_exp;
$tables[] = $c_ndfp_exp_tax_range;
$tables[] = $c_ndfp_exp_tax_cat;
$tables[] = $c_ndfp_exp_pay_cat;
$tables[] = $c_ndfp_exp_tax;

$head = ndfpadmin_prepare_head();
$current_head = 'dict';

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
/*
 * View
 */

require_once("../tpl/admin.dict.tpl.php");

$db->close();

?>
