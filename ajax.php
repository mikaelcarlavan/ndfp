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
 *	\file       htdocs/ndfp/ndfp.php
 *	\ingroup    ndfp
 *	\brief      Page to create/modify/view a credit note or all credit notes
 */

$res=@include("../main.inc.php");					// For root directory
if (! $res) $res=@include("../../main.inc.php");	// For "custom" directory

dol_include_once("/ndfp/class/ndfp.class.php");

require_once(DOL_DOCUMENT_ROOT."/core/lib/functions2.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/date.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/doleditor.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/files.lib.php");
require_once(DOL_DOCUMENT_ROOT."/compta/facture/class/facture.class.php");


$langs->load('companies');
$langs->load('ndfp@ndfp');
$langs->load('main');

if (!$user->rights->ndfp->myactions->read && !$user->rights->ndfp->allactions->read)
{
    accessforbidden();
}

$id=GETPOST('id', 'int');
$rowid = GETPOST('rowid', 'int') ? GETPOST('rowid', 'int') : 0;
$oid=GETPOST('oid', 'int');
$ref=GETPOST('ref', 'alpha');

$action=GETPOST('action', 'alpha');

/*
 * View
 */
$ndfp = new Ndfp($db);



if ($id > 0 || !empty($ref))
{
    $ndfp->fetch($id, $ref);
}

$data = new stdClass();


if ($action == 'updateht')
{
	$fk_tva = GETPOST('fk_tva');
	$total_ttc = GETPOST('total_ttc');
	$total_ttc = price2num($total_ttc);

	$fk_tva = $fk_tva ? price2num($fk_tva) : 0;

	$out = $total_ttc/(1 + $fk_tva/100);
	$out = price2num(price($out), 'MT');
	$out = price($out);

	$data->totalht = $out;
}

if ($action == 'updatettcfromht')
{
	$fk_tva = GETPOST('fk_tva');
	$total_ht = GETPOST('total_ht');
	$total_ht = price2num($total_ht);

	$fk_tva = $fk_tva ? price2num($fk_tva) : 0;

	$out = $total_ht * (1 + $fk_tva/100);
	$out = price2num(price($out), 'MT');
	$out = price($out);

	$data->totalttc = $out;
}

if ($action == 'updatettc')
{
	$fk_exp = GETPOST('fk_exp', 'int') ? GETPOST('fk_exp', 'int') : 0;
	$previousexp = GETPOST('previousexp', 'int') ? GETPOST('previousexp', 'int') : 0;
	$qty = GETPOST('qty', 'int') ? GETPOST('qty', 'int') : 0;
	$fk_cat = GETPOST('fk_cat', 'int') ? GETPOST('fk_cat', 'int') : 0;
	$exp = $ndfp->get_expense($fk_exp);

	$fk_tva = GETPOST('fk_tva');
	$fk_tva = $fk_tva ? price2num($fk_tva) : 0;

	if ($exp->code == 'EX_KME')
	{
		$total_ht = $ndfp->compute_travel_fees($qty, $fk_cat, $previous_exp);
	}
	else
	{
		$total_ht = GETPOST('total_ht');
		$total_ht = price2num($total_ht);		
	}
	

	$out = $total_ht * (1 + $fk_tva/100);
	$out = price2num(price($out), 'MT');
	
	$data->totalttc = price($out);

	$out = $out/(1 + $fk_tva/100);
	$out = price2num(price($out), 'MT');

	$data->totalht = price($out);

}

if ($action == 'updatetva')
{
	$fk_ndfp_tax_det = GETPOST('fk_ndfp_tax_det', 'int') ? GETPOST('fk_ndfp_tax_det', 'int') : 0;
	$rowid = GETPOST('lineid', 'int') ? GETPOST('lineid', 'int') : 0;

	$fk_tva = GETPOST('fk_tva_det');
	$fk_tva = $fk_tva ? price2num($fk_tva) : 0;

	$line = new NdfpTaxLine($db);
	$result = $line->fetch($fk_ndfp_tax_det);

	$total_ht = price2num($line->total_ht);
	$total_ttc = price2num($line->total_ttc);

	$tva = $fk_tva;

	if ($line->price_base_type == 'HT')
	{
		$out = $total_ht * ($tva/100);
	}
	else
	{
		$ancien_taux = $line->get_all_tva($rowid);
		$nouveau_taux = $ancien_taux + $tva;

		$total_ht = $total_ttc/(1 + ($nouveau_taux/100));
		$total_ht = price2num(price($total_ht), 'MT');

		$tva_lines = $line->get_tva_lines();
		$total_tva = 0;
		if (count($tva_lines))
		{
			foreach ($tva_lines as $tva_line)
			{			
				$tva  = price2num($tva_line->tva_tx);
				$tva_ligne = 0;
				if ($rowid != $tva_line->rowid)
				{
					$tva_ligne = $total_ht * ($tva/100);
					$tva_ligne = price2num(price($tva_ligne), 'MT');
				}

				$total_tva += $tva_ligne;
			}		
		}


		$out = $total_ttc - $total_ht - $total_tva;	
	}

	$out = price2num(price($out), 'MT');
	$out = price($out);

	$data->totaltva = $out;
}

if ($action == 'gettva')
{
	$fk_exp = GETPOST('fk_exp', 'int') ? GETPOST('fk_exp', 'int') : 0;
	$tva = $ndfp->get_expense($fk_exp);
	$out = $tva->fk_tva ? $tva->fk_tva : 0;

	$data->tva = $out;
}

if ($action == 'getfees')
{
	$userid = GETPOST('fk_user') ? GETPOST('fk_user') : 0;
	$fk_cat = GETPOST('fk_cat') ? GETPOST('fk_cat') : 0;
	$rowid = GETPOST('lineid') ? GETPOST('lineid') : 0;

	$out = $ndfp->get_user_fees($userid, $fk_cat, $rowid);

	$data->previousexp = $out;
}	

echo json_encode($data);

$db->close();

?>