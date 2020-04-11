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
 *      \file       htdocs/ndfp/class/actions_ndfp.class.php
 *      \ingroup    ndfp
 *      \brief      File of class to manage trips and working credit notes
 */

require_once(DOL_DOCUMENT_ROOT ."/core/class/commonobject.class.php");
require_once(DOL_DOCUMENT_ROOT ."/core/lib/functions.lib.php");
require_once(DOL_DOCUMENT_ROOT ."/core/lib/functions2.lib.php");
require_once(DOL_DOCUMENT_ROOT.'/core/lib/price.lib.php');
require_once(DOL_DOCUMENT_ROOT."/core/lib/files.lib.php");

require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.facture.class.php';

require_once DOL_DOCUMENT_ROOT . '/core/modules/facture/modules_facture.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/supplier_invoice/modules_facturefournisseur.php';

dol_include_once("/ndfp/class/ndfp.class.php");
dol_include_once("/ndfp/class/html.form.ndfp.class.php");

/**
 *      \class      Ndfp
 *      \brief      
 */
class ActionsNdfp
{ 
	
	function formBuilddocOptions($parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $db, $form, $mysoc, $conf;
		
		$langs->load('ndfp@ndfp');
		
		$joinProof = GETPOST('join_proof') ? true : false;
		
		$addInput = false;
		$modulepart = $parameters['modulepart'];
		
		$out = '';

		if ($modulepart == 'ndfp')
		{
			$addInput = true;
			$text = $langs->trans('AddProofsToPDF');
		}
				
		if ($addInput)
		{
			$out.= $text. '&nbsp;<input type="checkbox" value="1" name="join_proof" id="join_proof" '.($joinProof ? 'checked="checked"' : '').'/><br />';
		}
			
		$this->resprints = $out;
		return 0;
	}
	


}


