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
 *	\file       htdocs/core/modules/ndfp/doc/pdf_calamar.modules.php
 *	\ingroup    ndfp
 *	\brief      File of class to generate credit notes PDF from calamar model
 */

dol_include_once('/ndfp/core/modules/ndfp/modules_ndfp.php');
require_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/functions2.lib.php");
require_once(DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php');
if (! empty($conf->projet->enabled))
{
    require_once(DOL_DOCUMENT_ROOT."/core/lib/project.lib.php");
    require_once(DOL_DOCUMENT_ROOT."/projet/class/project.class.php");
}

/**
 *	\class      pdf_calamar
 *	\brief      Classe permettant de generer les notes de frais au modele Calamar
 */

class pdf_calamar extends ModelePDFNdfp
{
	var $emetteur;	// Objet societe qui emet

    var $phpmin = array(4,3,0); // Minimum version of PHP required by module
    var $version = 'dolibarr';


	/**
	 *		Constructor
	 *		@param		db		Database access handler
	 */
	function __construct($db)
	{
		global $conf,$langs,$mysoc;

		$langs->load("main");
		$langs->load('ndfp@ndfp');

		$this->db = $db;
		$this->name = "calamar";
		$this->description = $langs->trans('PDFCalamarDescription');

		// Dimension page pour format A4
		$this->type = 'pdf';
		$this->page_largeur = 210;
		$this->page_hauteur = 297;
		$this->format = array($this->page_largeur, $this->page_hauteur);
		$this->marge_gauche=10;
		$this->marge_droite=10;
		$this->marge_haute=10;
		$this->marge_basse=10;

		$this->option_logo = 1;                    // Affiche logo
		$this->option_multilang = 1;               // Dispo en plusieurs langues

		// Defini position des colonnes
		$this->posxdesc = $this->marge_gauche+1;
		$this->posxdated = 90;
		//$this->posxdatef = 90;


		$this->posxref = 110;
		$this->posxqty = 127;
        $this->posxtotalht = 140;
		$this->posxtotalttc = 160;

		$this->allowedTypes = array(
			IMAGETYPE_GIF, 
			IMAGETYPE_JPEG, 
			IMAGETYPE_PNG, 
			IMAGETYPE_BMP, 
			IMAGETYPE_TIFF_II, 
			IMAGETYPE_TIFF_MM, 
			IMAGETYPE_IFF
		);


	}


	/**
     *  Function to build pdf onto disk
     *  @param      object          Id of object to generate
     *  @param      outputlangs     Lang output object
     *  @param      srctemplatepath Full path of source filename for generator using a template file
     *  @return     int             1=OK, 0=KO
	 */
	function write_file($object, $outputlangs, $srctemplatepath='')
	{
		global $user, $langs, $conf, $mysoc;

		$joinProof = GETPOST('join_proof') ? true : false;
						
		if (! is_object($outputlangs)) $outputlangs = $langs;

		if (! empty($conf->global->MAIN_USE_FPDF)) $outputlangs->charset_output='ISO-8859-1';

		$outputlangs->load("main");
		$outputlangs->load("dict");
		$outputlangs->load("companies");
		$outputlangs->load("ndfp@ndfp");

		$default_font_size = pdf_getPDFFontSize($outputlangs);

        $already_paid = $object->get_amount_payments_done();
		// Get user
        $userstatic = new User($this->db);
        $userstatic->fetch($object->fk_user);
        $this->emetteur = $userstatic;

		if ($conf->ndfp->dir_output)
		{

			// Definition of $dir and $file

			if ($object->specimen)
			{
				$dir = $conf->ndfp->dir_output;
				$file = $dir . "/SPECIMEN.pdf";
			}
			else
			{
				$objectref = dol_sanitizeFileName($object->ref);
				$dir = $conf->ndfp->dir_output . "/" . $objectref;
				$file = $dir . "/" . $objectref . ".pdf";
			}
			if (! file_exists($dir))
			{
				if (dol_mkdir($dir) < 0)
				{
					$this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
					return 0;
				}
			}

			if (file_exists($dir))
			{

                $pdf = pdf_getInstance($this->format);
                $nblignes = count($object->lines);

                if (class_exists('TCPDF'))
                {
                    $pdf->setPrintHeader(false);
                    $pdf->setPrintFooter(false);
                }

                $pdf->SetFont(pdf_getPDFFont($outputlangs));

				$pdf->Open();
				$pagenb = 0;
				$pdf->SetDrawColor(128,128,128);

				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("NdfpSing"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("NdfpSing"));
				if ($conf->global->MAIN_DISABLE_PDF_COMPRESSION) $pdf->SetCompression(false);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right
				$pdf->SetAutoPageBreak(1,0);

				// New page
				$pdf->AddPage();
				$pagenb++;

				$this->_pagehead($pdf, $object, 1, $outputlangs);


				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->MultiCell(0, 3, '');		// Set interline to 3
				$pdf->SetTextColor(0,0,0);

				$tab_top = 90;
				$tab_top_newpage = 50;
				$tab_height = 110;
				$tab_height_newpage = 150;

				$iniY = $tab_top + 7;
				$curY = $tab_top + 7;
				$nexY = $tab_top + 7;

				// Loop on each lines
				for ($i = 0 ; $i < $nblignes ; $i++)
				{
					$curY = $nexY;
                    $curX = $this->posxdesc-1;

                    $pdf->SetFont('','', $default_font_size - 1);   // Into loop to work with multipage

					// Description of product line
					//$label_expense = dol_print_date($object->lines[$i]->dated, '%d/%m/%Y'). '-'.dol_print_date($object->lines[$i]->datef, '%d/%m/%Y');
					
                    $label_expense = $outputlangs->transnoentities($object->lines[$i]->label);
                    			

					$label_expense.= ($object->lines[$i]->comment ? ' - '.$object->lines[$i]->comment : '');
			

					if ($object->lines[$i]->cur_iso != $object->cur_iso)
					{
						$label_expense.= ' - '.price2num($object->lines[$i]->total_ht_cur, 'MT').' '.$object->lines[$i]->cur_iso. ' (Tx : '.price2num($object->lines[$i]->rate, 'MU').')';
					}

					// Tax rating
					if ($object->lines[$i]->fk_cat && $object->lines[$i]->code == 'EX_KME')
					{
						$label_expense .= "\n\r".$outputlangs->convToOutputCharset($object->get_tax_rating_label($object->lines[$i]->fk_cat, $outputlangs));
						$label_expense .= "\n\r".$outputlangs->transnoentities("PreviousExpPDF", $object->lines[$i]->previous_exp);
					}
													
                    $pdf->SetXY ($this->posxdesc, $curY);
                    $pdf->MultiCell($this->posxdated-$this->posxdesc-1, 3, $label_expense, 0, 'L', 0);

					$dated = dol_print_date($object->lines[$i]->dated, '%d/%m/%Y');
					$pdf->SetXY ($this->posxdated, $curY);
					$pdf->MultiCell($this->posxref-$this->posxdated-1, 3, $dated, 0, 'L', 0);
	
					/*$datef = dol_print_date($object->lines[$i]->datef, '%d/%m/%Y');
					$pdf->SetXY ($this->posxdatef, $curY);
					$pdf->MultiCell($this->posxref-$this->posxdatef-1, 3, $datef, 0, 'L', 0);
					*/

					// External reference
                    $ext_ref = $object->lines[$i]->ref_ext;
					$pdf->SetXY ($this->posxref, $curY);
					$pdf->MultiCell($this->posxqty-$this->posxref-1, 3, $ext_ref, 0, 'R');
					
                    // VAT Rate
                    /*$vat_rate = $object->lines[$i]->taux.'%';
					$pdf->SetXY ($this->posxtva, $curY);
					$pdf->MultiCell($this->posxqty-$this->posxtva-1, 3, $vat_rate, 0, 'R');*/

					// Quantity
					$qty = $object->lines[$i]->qty;
					$pdf->SetXY ($this->posxqty, $curY);
					$pdf->MultiCell($this->posxtotalht-$this->posxqty-1, 3, $qty, 0, 'R');	 // Enough for 6 chars


					// HT Total
					$total_ht = price($object->lines[$i]->total_ht);
					$pdf->SetXY ($this->posxtotalht, $curY);
					$pdf->MultiCell($this->posxtotalttc-$this->posxtotalht-1, 3, $total_ht, 0, 'R', 0);

					// TTC Total
					$total_ttc = price($object->lines[$i]->total_ttc);
					$pdf->SetXY ($this->posxtotalttc, $curY);
					$pdf->MultiCell(($this->page_largeur-$this->marge_droite)-$this->posxtotalttc-1, 3, $total_ttc, 0, 'R', 0);

					$linestatic = new NdfpLine($this->db);
					$linestatic->rowid = $object->lines[$i]->rowid;
					$linestatic->milestone = $object->lines[$i]->milestone;
					
					$vat_lines = $linestatic->get_tva_lines();
					
					$skipLines = 0;
					if (count($vat_lines) > 0)
					{
						foreach ($vat_lines as $vat_line)
						{
							if ($vat_line->total_tva > 0)
							{
								$curY += 4;
								$skipLines++;
								
								$total_tva = price($vat_line->total_tva);
								$tva_tx = $vat_line->fk_tva > 0 ? price($vat_line->tva_tx).'%' : '';
								$tva_line = $outputlangs->transnoentities("VATLinePDF", $tva_tx, $total_tva);
								$pdf->SetXY ($this->posxtotalttc, $curY);
								$pdf->MultiCell(($this->page_largeur-$this->marge_droite)-$this->posxtotalttc-1, 3, $tva_line, 0, 'R', 0);
							}				
						}
					}
					
//
					$offset = 2 + dol_nboflines_bis($label_expense, 52, $outputlangs->charset_output)*4;
					$nexY += ($offset + 4*$skipLines);    // Passe espace entre les lignes
//
					if ($i < ($nblignes - 1))	// If it's not last line
					{

						$follow_desc = $outputlangs->transnoentities($object->lines[$i]->label);
						$nblineFollowDesc = dol_nboflines_bis($follow_desc, 52, $outputlangs->charset_output)*4;
					}
					else	// If it's last line
					{
						$nblineFollowDesc = 0;
					}

					// Test if a new page is required
					if ($pagenb == 1)
					{
						$tab_top_in_current_page = $tab_top;
						$tab_height_in_current_page = $tab_height;
					}
					else
					{
						$tab_top_in_current_page = $tab_top_newpage;
						$tab_height_in_current_page = $tab_height_newpage;
					}

					if (($nexY+$nblineFollowDesc) > ($tab_top_in_current_page+$tab_height_in_current_page) && $i < ($nblignes - 1))
					{
					    if ($pagenb == 1)
						{
							$this->_table($pdf,  $object, $tab_top, $tab_height + 20, $nexY, $outputlangs);
						}
						else
						{
							$this->_table($pdf,  $object, $tab_top_newpage, $tab_height_newpage, $nexY, $outputlangs);
						}

						$this->_pagefoot($pdf,$object,$outputlangs);

						 //New page
						$pdf->AddPage();
						$pagenb++;
						$this->_pagehead($pdf, $object, 0, $outputlangs);
						$pdf->SetFont('','', $default_font_size - 1);
						$pdf->MultiCell(0, 3, '');		// Set interline to 3
						$pdf->SetTextColor(0,0,0);

						$nexY = $tab_top_newpage + 8;
					}

				}

				
				// Show square
				if ($pagenb == 1)
				{
					$this->_table($pdf,  $object, $tab_top, $tab_height, $nexY, $outputlangs);
					$bottomlasttab = $tab_top + $tab_height + 1;
				}
				else
				{
					$this->_table($pdf, $object, $tab_top_newpage, $tab_height_newpage, $nexY, $outputlangs);
					$bottomlasttab = $tab_top_newpage + $tab_height_newpage + 1;
				}

				// Affiche zone totaux
				$posy = $this->_totals_table($pdf, $object, $already_paid, $bottomlasttab, $outputlangs);

				$this->_visas_table($pdf, $object, $posy, $outputlangs);
				
				// Affiche zone versements
				if ($already_paid > 0)
				{
					$this->_payments_table($pdf, $object, $posy, $outputlangs);
				}				
				
				// Footpage
				$this->_pagefoot($pdf, $object, $outputlangs);
				
				// Add proofs
				if ($joinProof)
				{
					$upload_dir = $conf->ndfp->dir_output .'/'. dol_sanitizeFileName($object->ref);

					$filearray = dol_dir_list($upload_dir, "files", 0, '', '\.meta$', '', SORT_DESC, 1);
					
					foreach($filearray as $key => $proofFile)
					{
						$proofFilename = $proofFile['name'];
						$proofFilepath = $proofFile['fullname'];
						
						
						if ($proofFilename != dol_sanitizeFileName($object->ref).'.pdf')
						{							
							$ext = pathinfo($proofFilepath, PATHINFO_EXTENSION);
							$ext = strtolower($ext);
							
							if ($ext == 'pdf')
							{
								$pagesNbr = $pdf->setSourceFile($proofFilepath);
								for ($p = 1; $p <= $pagesNbr; $p++)
								{
									$templateIdx = $pdf->ImportPage($p);
									$size = $pdf->getTemplatesize($templateIdx);
									
									$portrait = $size['h'] > $size['w'] ? true : false;
									
									$pdf->AddPage($portrait ? 'P' : 'L');
									$pagenb++;	
									
									$pdf->useTemplate($templateIdx);
									
								}								
							}
							else
							{
								list($width, $height, $type) = @getimagesize($proofFilepath);
								
								
								if (in_array($type, $this->allowedTypes))
								{
									$ratio = $width/$height;
									$portrait = $height > $width ? true : false;
									
									if ($ratio > 1)
									{
										if ($portrait)
										{
											$pageWidth = $this->page_largeur-($this->marge_gauche + $this->marge_droite);
										}
										else
										{
											$pageWidth = $this->page_hauteur-($this->marge_haute + $this->marge_basse);
										}
										
										$pageHeight = $pageWidth / $ratio; 
									}
									else
									{
										if ($portrait)
										{
											$pageHeight = $this->page_hauteur-($this->marge_haute + $this->marge_basse);
										}
										else
										{
											$pageHeight = $this->page_largeur-($this->marge_gauche + $this->marge_droite);
										}									
										
										$pageWidth = $pageHeight * $ratio; 
									}
									
									$pdf->AddPage($portrait ? 'P' : 'L');
									$pagenb++;									
									
									// Paste image in PDF
									$pdf->Image($proofFilepath, $this->marge_gauche, $this->marge_haute, $pageWidth, $pageHeight);
								}
							}													
						}
					}

				}
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();

				if (!empty($conf->global->NDFP_DEMO_MODE))
				{
					pdf_watermark($pdf,$outputlangs,$this->page_hauteur,$this->page_largeur, 'mm', 'SPECIMEN');
				}

				$pdf->Close();

				$pdf->Output($file,'F');
				if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));

				return 1;   //
			}
			else
			{
				$this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
				return 0;
			}
		}
		else
		{
			$this->error=$langs->trans("ErrorConstantNotDefined","NDFP_OUTPUTDIR");
			return 0;
		}

		$this->error = $langs->trans("ErrorUnknown");
		return 0;   // Erreur par defaut
	}


	/**
	 *   	\brief      Show header of page
	 *      \param      pdf             Object PDF
	 *      \param      object          Object ndfp
	 *      \param      showaddress     0=no, 1=yes
	 *      \param      outputlangs		Object lang for output
	 */
	function _pagehead(&$pdf, $object, $showaddress=1, $outputlangs)
	{
		global $conf, $langs, $mysoc;

		$outputlangs->load("main");
		$outputlangs->load("ndfp@ndfp");

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		pdf_pagehead($pdf,$outputlangs,$this->page_hauteur);


		$pdf->SetTextColor(0,0,60);
		$pdf->SetFont('','B', $default_font_size + 3);

		$posy=$this->marge_haute;

		$pdf->SetXY($this->marge_gauche,$posy);

		// Logo
		$logo=$conf->mycompany->dir_output.'/logos/'.$mysoc->logo;
		if ($mysoc->logo)
		{
			if (is_readable($logo))
			{
				$pdf->Image($logo, $this->marge_gauche, $posy, 0, 24);	// width=0 (auto), max height=24
			}
			else
			{
				$pdf->SetTextColor(200,0,0);
				$pdf->SetFont('','B',$default_font_size - 2);
				$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound",$logo), 0, 'L');
				$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
			}
		}
		else
		{
			$text = $mysoc->name;
			$pdf->MultiCell(100, 4, $outputlangs->convToOutputCharset($text), 0, 'L');
		}

        // Title
		$pdf->SetFont('','B', $default_font_size + 3);
		$pdf->SetXY(100, $posy);
		$pdf->SetTextColor(0,0,60);
		$title = $outputlangs->transnoentities("NdfpSing");
		$pdf->MultiCell(100, 4, $title, '' , 'R');

        // Reference
		$pdf->SetFont('','B', $default_font_size + 2);
		$posy += 6;
		$pdf->SetXY(100,$posy);
		$pdf->SetTextColor(0,0,60);
		$pdf->MultiCell(100, 4, $outputlangs->transnoentities("Ref")." : " . $outputlangs->convToOutputCharset($object->ref), '', 'R');


        // Creation date
		$posy += 6;
		$pdf->SetFont('','', $default_font_size - 1);
		$pdf->SetXY(100, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$pdf->MultiCell(100, 3, $outputlangs->transnoentities("CreationDate")." : " . dol_print_date($object->datec,"day",false,$outputlangs), '', 'R');


		if ($showaddress)
		{
			// Sender properties
			$carac_emetteur = "";
			// Office phone
			if ($this->emetteur->office_phone)
			{
            	$carac_emetteur .= ($carac_emetteur ? "\n" : '' ).$outputlangs->transnoentities("OfficePhone")." : ".$outputlangs->convToOutputCharset($this->emetteur->office_phone);
        	}
        	
			// User mobile
			if ($this->emetteur->user_mobile)
			{
				$carac_emetteur .= ($carac_emetteur ? "\n" : '' ).$outputlangs->transnoentities("MobilePhone")." : ".$outputlangs->convToOutputCharset($this->emetteur->user_mobile);
			}
			// Email
			if ($this->emetteur->email)
			{
				$carac_emetteur .= ($carac_emetteur ? "\n" : '' ).$outputlangs->transnoentities("Email")." : ".$outputlangs->convToOutputCharset($this->emetteur->email);
			}


			// Note properties
			$carac_note = "";
			// Society
			if ($object->fk_soc > 0)
			{
				$societestatic = new Societe($this->db);
				$result = $societestatic->fetch($object->fk_soc);

				if ($result > 0)
				{
					$carac_note .= ($carac_note ? "\n" : '' ).$outputlangs->transnoentities("Society")." : ".$outputlangs->convToOutputCharset($societestatic->name);
				}
			}
			// Description
			if ($object->description)
			{
				$carac_note .= ($carac_note ? "\n" : '' ).$outputlangs->transnoentities("Desc")." : ".$outputlangs->transnoentities($object->description);
			}

			// Start date
			if ($object->dates)
			{
				$carac_note .= ($carac_note ? "\n" : '' ).$outputlangs->transnoentities("DateStart")." : ".$outputlangs->convToOutputCharset(dol_print_date($object->dates, "day", false, $outputlangs));
			}
			// End date
			if ($object->datee)
			{
				$carac_note .= ($carac_note ? "\n" : '' ).$outputlangs->transnoentities("DateEnd")." : ".$outputlangs->convToOutputCharset(dol_print_date($object->datee, "day", false, $outputlangs));
			}
			// Project
			if ($conf->projet->enabled && $object->fk_project > 0)
			{
				$projectstatic = new Project($this->db);
				$result = $projectstatic->fetch($object->fk_project);

				if ($result > 0)
				{
					$carac_note .= ($carac_note ? "\n" : '' ).$outputlangs->transnoentities("Project")." : ".$outputlangs->convToOutputCharset($projectstatic->title);
				}
			}

			// Show sender
			$posy = 42;
			$posx = $this->marge_gauche;
			$hautcadre = 40;

			// Show sender frame
			$pdf->SetTextColor(0,0,0);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetXY($posx,$posy-5);
			$pdf->MultiCell(66,5, $outputlangs->transnoentities("User")." :", 0, 'L');
			$pdf->SetXY($posx,$posy);
			$pdf->SetFillColor(230,230,230);
			$pdf->MultiCell(82, $hautcadre, "", 0, 'R', 1);
			$pdf->SetTextColor(0,0,60);

			// Show sender name
			$pdf->SetXY($posx+2,$posy+3);
			$pdf->SetFont('','B', $default_font_size);
			$pdf->MultiCell(80, 4, $outputlangs->convToOutputCharset($this->emetteur->getFullName($outputlangs)), 0, 'L');

			// Show sender information
			$pdf->SetXY($posx+2,$posy+8);
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->MultiCell(80, 4, $carac_emetteur, 0, 'L');

			// Show details note
			$posy = 42;
			$posx = 100;

			// Show details frame
			$pdf->SetTextColor(0,0,0);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetXY($posx+2,$posy-5);
			$pdf->MultiCell(80,5, $outputlangs->transnoentities("NdfpDetails")." :",0,'L');
			$pdf->rect($posx, $posy, 100, $hautcadre);

			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->SetXY($posx+2,$posy+3);
			$pdf->MultiCell(86,4, $carac_note, 0, 'L');		
		
		}
		



	}

	/**
	 *   	\brief      Show footer of page
	 *   	\param      pdf     		PDF factory
	 * 		\param		object			Object
	 *      \param      outputlangs		Object lang for output
	 * 		\remarks	Need this->emetteur object
	 */
	function _pagefoot(&$pdf,$object,$outputlangs)
	{
	    global $mysoc;

		return pdf_pagefoot($pdf,$outputlangs,'',$mysoc,$this->marge_basse,$this->marge_gauche,$this->page_hauteur,$object);
	}

	/**
	 *   Display square of lines
	 *   @param      pdf     PDF
	 */
	function _table(&$pdf, $object, $tab_top, $tab_height, $nexY, $outputlangs)
	{
		global $conf;

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		// Amount in (at tab_top - 1)
		$pdf->SetTextColor(0,0,0);
		$pdf->SetFont('','', $default_font_size - 2);
		$titre = $outputlangs->transnoentities("AmountInCurrency",$outputlangs->transnoentitiesnoconv("Currency".$object->cur_iso));
		$pdf->SetXY($this->page_largeur - $this->marge_droite - ($pdf->GetStringWidth($titre) + 3), $tab_top-4);
		$pdf->MultiCell(($pdf->GetStringWidth($titre) + 3), 2, $titre);

		$pdf->SetDrawColor(128,128,128);

		// Rect prend une longueur en 3eme param et 4eme param
		$pdf->Rect($this->marge_gauche, $tab_top, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $tab_height);
		// line prend une position y en 2eme param et 4eme param
		$pdf->line($this->marge_gauche, $tab_top+5, $this->page_largeur-$this->marge_droite, $tab_top+5);

		$pdf->SetFont('','', $default_font_size - 1);

		$pdf->SetXY ($this->posxdesc-1, $tab_top+1);
		$pdf->MultiCell($this->posxdated-$this->posxdesc+3,2, $outputlangs->transnoentities("Designation"),'','L');


		$pdf->line($this->posxdated-1, $tab_top, $this->posxdated-1, $tab_top + $tab_height);
		$pdf->SetXY ($this->posxdated-3, $tab_top+1);
		$pdf->MultiCell($this->posxref-$this->posxdated+3,2, $outputlangs->transnoentities("Date"),'','C');
			
		/*$pdf->line($this->posxdatef-1, $tab_top, $this->posxdatef-1, $tab_top + $tab_height);
		$pdf->SetXY ($this->posxdatef-3, $tab_top+1);
		$pdf->MultiCell($this->posxref-$this->posxdatef+3,2, $outputlangs->transnoentities("DateEnd"),'','C');
		*/
        $pdf->line($this->posxref-1, $tab_top, $this->posxref-1, $tab_top + $tab_height);
        $pdf->SetXY ($this->posxref-3, $tab_top+1);
        $pdf->MultiCell($this->posxqty-$this->posxref+3,2, $outputlangs->transnoentities("ExternalReference"),'','C');
		
        //$pdf->line($this->posxtva-1, $tab_top, $this->posxtva-1, $tab_top + $tab_height);
        //$pdf->SetXY ($this->posxtva-3, $tab_top+1);
        //$pdf->MultiCell($this->posxqty-$this->posxtva+3,2, $outputlangs->transnoentities("TVA"),'','C');


		$pdf->line($this->posxqty-1, $tab_top, $this->posxqty-1, $tab_top + $tab_height);
		$pdf->SetXY ($this->posxqty-1, $tab_top+1);
		$pdf->MultiCell($this->posxtotalht-$this->posxqty+3,2, $outputlangs->transnoentities("Qty"),'','C');

		$pdf->line($this->posxtotalht-1, $tab_top, $this->posxtotalht-1, $tab_top + $tab_height);
		$pdf->SetXY ($this->posxtotalht-1, $tab_top+1);
		$pdf->MultiCell($this->posxtotalttc-$this->posxtotalht+3,2, $outputlangs->transnoentities("Total_HT"),'','C');

		$pdf->line($this->posxtotalttc-1, $tab_top, $this->posxtotalttc-1, $tab_top + $tab_height);
		$pdf->SetXY ($this->posxtotalttc-1, $tab_top+1);
		$pdf->MultiCell(25,2, $outputlangs->transnoentities("Total_TTC"),'','C');

	}

	/**
	 *	\brief      Display the total
	 *	@param      pdf             PDF object
	 *	@param      object          Ndfp object
	 *	@param      deja_regle      Amount already paid
	 *	@param		posy			Starting position
	 *	@param		outputlangs		langs object
	 *	@return     y               Final position
	 */
	function _totals_table(&$pdf, $object, $already_paid, $posy, $outputlangs)
	{
		global $conf, $mysoc;

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		$tab2_top = $posy;
		$tab2_hl = 4;

		// Total table
		$lltot = 200;
        $col1x = 120;
        $col2x = 170;
        $largcol2 = $lltot - $col2x;

		$useborder = 0;
        $index = 0;

        $pdf->SetFont('','', $default_font_size - 1);

		// Total HT
		$pdf->SetFillColor(255,255,255);
		$pdf->SetXY ($col1x, $tab2_top + 0);
		$pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->transnoentities("Total_HT"), 0, 'L', 1);
		$pdf->SetXY ($col2x, $tab2_top + 0);
		$pdf->MultiCell($largcol2, $tab2_hl, price($object->total_ht), 0, 'R', 1);


		// Show VAT by rates and total
        $index++;
		$pdf->SetFillColor(248,248,248);

        $pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);
		$pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->transnoentities("Total_TVA"), 0, 'L', 1);
		$pdf->SetXY ($col2x, $tab2_top + $tab2_hl * $index);
		$pdf->MultiCell($largcol2, $tab2_hl, price($object->total_tva), 0, 'R', 1);


	   // Total TTC
		$index++;
		$pdf->SetTextColor(0,0,60);
		$pdf->SetFillColor(224,224,224);

		$pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);
		$pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->transnoentities("Total_TTC"), $useborder, 'L', 1);
		$pdf->SetXY ($col2x, $tab2_top + $tab2_hl * $index);
		$pdf->MultiCell($largcol2, $tab2_hl, price($object->total_ttc), $useborder, 'R', 1);


		$pdf->SetTextColor(0,0,0);

		$remaining_to_pay = price2num($object->total_ttc - $already_paid, 'MT');

		if ($object->statut == 2)
        {
            $remaining_to_pay = 0;
        }

		if ($already_paid > 0)
		{
			// Already paid
			$index++;
			$pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);
			$pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->transnoentities("Paid"), 0, 'L', 0);
			$pdf->SetXY ($col2x, $tab2_top + $tab2_hl * $index);
			$pdf->MultiCell($largcol2, $tab2_hl, price($already_paid), 0, 'R', 0);


			$index++;
			$pdf->SetTextColor(0,0,60);
			$pdf->SetFillColor(224,224,224);

			$pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);
			$pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->transnoentities("RemainderToPay"), $useborder, 'L', 1);
			$pdf->SetXY ($col2x, $tab2_top + $tab2_hl * $index);
			$pdf->MultiCell($largcol2, $tab2_hl, price($remaining_to_pay), $useborder, 'R', 1);

			// Fin
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->SetTextColor(0,0,0);
		}

		$index++;

		return ($tab2_top + ($tab2_hl * $index));
	}

	/**
	 *  \brief Show payments table
     *  @param      pdf             Object PDF
     *  @param      object          Object Ndfp
     *  @param      posy            Position y in PDF
     *  @param      outputlangs     Object langs for output
     *  @return     int             <0 if KO, >0 if OK
	 */
	function _payments_table(&$pdf, $object, $posy, $outputlangs)
	{
		$tab3_posx = 120;
		$tab3_top = $posy + 8;
		$tab3_width = 80;
		$tab3_height = 4;

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		$pdf->SetFont('','', $default_font_size - 2);
		$pdf->SetXY ($tab3_posx, $tab3_top - 5);
		$pdf->MultiCell(60, 5, $outputlangs->transnoentities("PaymentsAlreadyDone"), 0, 'L', 0);

		$pdf->line($tab3_posx, $tab3_top-1+$tab3_height, $tab3_posx+$tab3_width, $tab3_top-1+$tab3_height);

		$pdf->SetFont('','', $default_font_size - 4);
		$pdf->SetXY ($tab3_posx, $tab3_top );
		$pdf->MultiCell(20, 3, $outputlangs->transnoentities("Payment"), 0, 'L', 0);
		$pdf->SetXY ($tab3_posx+21, $tab3_top );
		$pdf->MultiCell(20, 3, $outputlangs->transnoentities("Amount"), 0, 'L', 0);
		$pdf->SetXY ($tab3_posx+40, $tab3_top );
		$pdf->MultiCell(20, 3, $outputlangs->transnoentities("Type"), 0, 'L', 0);
		$pdf->SetXY ($tab3_posx+58, $tab3_top );
		$pdf->MultiCell(20, 3, $outputlangs->transnoentities("Num"), 0, 'L', 0);

		$y = 0;

		$pdf->SetFont('','', $default_font_size - 4);

        $payments = $object->get_payments();

		// Loop on each payment
		foreach ($payments AS $payment)
        {
			$y += 3;

			$pdf->SetXY ($tab3_posx, $tab3_top+$y );
			$pdf->MultiCell(20, 3, dol_print_date($this->db->jdate($payment->dp), 'day', false, $outputlangs, true), 0, 'L', 0);

            $pdf->SetXY ($tab3_posx+21, $tab3_top+$y);
			$pdf->MultiCell(20, 3, price($payment->amount), 0, 'L', 0);

			$pdf->SetXY ($tab3_posx+40, $tab3_top+$y);
			$pdf->MultiCell(20, 3, $outputlangs->trans("PaymentTypeShort" . $payment->payment_code), 0, 'L', 0);

            $pdf->SetXY ($tab3_posx+58, $tab3_top+$y);
			$pdf->MultiCell(30, 3, $payment->payment_number, 0, 'L', 0);

			$pdf->line($tab3_posx, $tab3_top+$y+3, $tab3_posx+$tab3_width, $tab3_top+$y+3 );
        }

	}
	
	/**
	 *  \brief Show Visa table
     *  @param      pdf             Object PDF
     *  @param      object          Object Ndfp
     *  @param      posy            Position y in PDF
     *  @param      outputlangs     Object langs for output
     *  @return     int             <0 if KO, >0 if OK
	 */
	function _visas_table(&$pdf, $object, $posy, $outputlangs)
	{
		$posx = 10;
		$posy+= 0;
		$width = 40;
		$height = 40;
		
		$default_font_size = pdf_getPDFFontSize($outputlangs);
		$pdf->SetTextColor(0,0,0);
		$pdf->SetFont('','', $default_font_size - 2);
		$pdf->SetXY($posx+2,$posy-5);
		$pdf->MultiCell(40,5, $outputlangs->transnoentities("UserVisa"),0,'L');
		$pdf->rect($posx, $posy, $width, $height);

		$posx += 45;
		
		$pdf->SetTextColor(0,0,0);
		$pdf->SetFont('','', $default_font_size - 2);
		$pdf->SetXY($posx+2,$posy-5);
		$pdf->MultiCell(40,5, $outputlangs->transnoentities("ManagerVisa"),0,'L');
		$pdf->rect($posx, $posy, $width, $height);
	}	
}

?>
