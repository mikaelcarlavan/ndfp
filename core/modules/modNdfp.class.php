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
 * 		\defgroup   modNdfp     Module Ndfp
 *      \file       htdocs/core/modules/modNdfp.class.php
 *      \ingroup    modNdfp
 *      \brief      Description and activation file for module modNdfp
 */
include_once(DOL_DOCUMENT_ROOT ."/core/modules/DolibarrModules.class.php");


/**
 * 		\class      modNdfp
 *      \brief      Description and activation class for module modNdfp
 */
class modNdfp extends DolibarrModules
{
	/**
	 *   \brief      Constructor. Define names, constants, directories, boxes, permissions
	 *   \param      DB      Database handler
	 */
	function __construct($db)
	{
        global $langs, $conf;

        $this->db = $db;
		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 70300;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'ndfp';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "hr";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = 'ndfp';
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "Gestion avancée des notes de frais et déplacements.";
		$this->editor_name = 'Mikael Carlavan';
		$this->editor_url = 'http://www.mika-carl.fr';

		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '2.0.6';
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 0;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto = 'ndfp@ndfp';

		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		// for default path (eg: /mymodule/core/xxxxx) (0=disable, 1=enable)
		// for specific path of parts (eg: /mymodule/core/modules/barcode)
		// for specific css file (eg: /mymodule/css/mymodule.css.php)
		//$this->module_parts = array(
		//                        	'triggers' => 0,                                 	// Set this to 1 if module has its own trigger directory (core/triggers)
		//							'login' => 0,                                    	// Set this to 1 if module has its own login method directory (core/login)
		//							'substitutions' => 0,                            	// Set this to 1 if module has its own substitution function file (core/substitutions)
		//							'menus' => 0,                                    	// Set this to 1 if module has its own menus handler directory (core/menus)
		//							'theme' => 0,                                    	// Set this to 1 if module has its own theme directory (core/theme)
		//                        	'tpl' => 0,                                      	// Set this to 1 if module overwrite template dir (core/tpl)
		//							'barcode' => 0,                                  	// Set this to 1 if module has its own barcode directory (core/modules/barcode)
		//							'models' => 0,                                   	// Set this to 1 if module has its own models directory (core/modules/xxx)
		//							'css' => array('/mymodule/css/mymodule.css.php'),	// Set this to relative path of css file if module has its own css file
	 	//							'js' => array('/mymodule/js/mymodule.js'),          // Set this to relative path of js file if module must load a js on all pages
		//							'hooks' => array('hookcontext1','hookcontext2')  	// Set here all hooks context managed by module
		//							'workflow' => array('WORKFLOW_MODULE1_YOURACTIONTYPE_MODULE2'=>array('enabled'=>'! empty($conf->module1->enabled) && ! empty($conf->module2->enabled)', 'picto'=>'yourpicto@mymodule')) // Set here all workflow context managed by module
		//                        );
		$this->module_parts = array(
				'hooks' => array('formfile'),
				'triggers' => 1
				);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mymodule/temp");
		$this->dirs = array("/ndfp/temp");
		$r=0;

		// Config pages. Put here list of php page names stored in admmin directory used to setup module.
		$this->config_page_url = array('config.php@ndfp');

		// Dependencies
		$this->hidden = false;			// A condition to hide module
		$this->requiredby = array();	// List of module ids to disable if this one is disabled
		$this->warnings_activation = array();                     // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		$this->warnings_activation_ext = array(); 

		$this->depends = array();		// List of modules id that must be enabled if this module is enabled
		$this->conflictwith = array('modExpenseReport');
		$this->phpmin = array(5,0);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,2);	// Minimum version of Dolibarr required by module
		$this->langfiles = array("ndfp@ndfp");

		// Constants
		$this->const = array(0 => array('NDFP_ADDON','chaine','uranus','',0),
                             1 => array('NDFP_ADDON_PDF','chaine','calamar','', 0),
                             2 => array('NDFP_SUBPERMCATEGORY_FOR_DOCUMENTS', 'chaine', 'myactions', '', 0),
                             3 => array('NDFP_PRICE_BASE_INVOICES','chaine','HT','',0));

        $this->tabs = array();

        // Dictionnaries
        $this->dictionnaries = array();

        // Boxes
		// Add here list of php file(s) stored in includes/boxes that contains class to show a box.
        $this->boxes = array();			// List of boxes
	    $r = 0;
        $this->boxes[$r][1] = "box_ndfp@ndfp";

		// Permissions
		$this->rights = array();		// Permission array used by this module
		$r = 0;
		$this->rights[$r][0] = 70301;
		$this->rights[$r][1] = 'Créer/Modifier les notes de frais liées à ce compte';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'create';


		$r++;
		$this->rights[$r][0] = 70302;
		$this->rights[$r][1] = 'Voir les notes de frais liées à ce compte';
		$this->rights[$r][2] = 'r';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'read';

		$r++;
		$this->rights[$r][0] = 70303;
		$this->rights[$r][1] = 'Supprimer les notes de frais liées à ce compte';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'delete';

		$r++;
		$this->rights[$r][0] = 70304;
		$this->rights[$r][1] = 'Envoyer par mail les notes de frais liées à ce compte';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'send';

		$r++;
		$this->rights[$r][0] = 70305;
		$this->rights[$r][1] = 'Valider les notes de frais liées à ce compte';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'validate';

		$r++;
		$this->rights[$r][0] = 70306;
		$this->rights[$r][1] = 'Dévalider les notes de frais liées à ce compte';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'unvalidate';


		$r++;
		$this->rights[$r][0] = 70307;
		$this->rights[$r][1] = 'Convertir les notes de frais liées à ce compte en factures fournisseur';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'bill';
 
 		$r++;
		$this->rights[$r][0] = 70308;
		$this->rights[$r][1] = 'Convertir les notes de frais liées à ce compte en factures clientes';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'clientbill';

		$r++;
		$this->rights[$r][0] = 70309;
		$this->rights[$r][1] = 'Emettre des paiements sur les notes de frais liées à ce compte';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'payment';

 		$r++;
		$this->rights[$r][0] = 70311;
		$this->rights[$r][1] = 'Créer/Modifier les notes de frais de tout le monde';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'allactions';
        $this->rights[$r][5] = 'create';


		$r++;
		$this->rights[$r][0] = 70312;
		$this->rights[$r][1] = 'Voir les notes de frais de tout le monde';
		$this->rights[$r][2] = 'r';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'allactions';
        $this->rights[$r][5] = 'read';

		$r++;
		$this->rights[$r][0] = 70313;
		$this->rights[$r][1] = 'Supprimer les notes de frais de tout le monde';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'allactions';
        $this->rights[$r][5] = 'delete';


		$r++;
		$this->rights[$r][0] = 70314;
		$this->rights[$r][1] = 'Envoyer par mail les notes de frais de tout le monde';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'allactions';
        $this->rights[$r][5] = 'send';

		$r++;
		$this->rights[$r][0] = 70315;
		$this->rights[$r][1] = 'Valider les notes de frais de tout le monde';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'allactions';
        $this->rights[$r][5] = 'validate';

		$r++;
		$this->rights[$r][0] = 70316;
		$this->rights[$r][1] = 'Dévalider les notes de frais de tout le monde';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'allactions';
        $this->rights[$r][5] = 'unvalidate';



		$r++;
		$this->rights[$r][0] = 70317;
		$this->rights[$r][1] = 'Exporter les notes de frais de tout le monde';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'allactions';
        $this->rights[$r][5] = 'export';

		$r++;
		$this->rights[$r][0] = 70318;
		$this->rights[$r][1] = 'Convertir les notes de frais de tout le monde en factures fournisseurs';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'allactions';
        $this->rights[$r][5] = 'bill';
 
 		$r++;
		$this->rights[$r][0] = 70319;
		$this->rights[$r][1] = 'Convertir les notes de frais de tout le monde en factures clientes';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'allactions';
        $this->rights[$r][5] = 'clientbill';

		$r++;
		$this->rights[$r][0] = 70321;
		$this->rights[$r][1] = 'Emettre des paiements sur les notes de frais de tout le monde';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'allactions';
        $this->rights[$r][5] = 'payment';

		// Main menu entries
		$this->menu = array();			// List of menus to add

        $r=0;
		$mainmenu = 'hrm';
		
		if ($mainmenu == 'ndfp')
		{
			$this->menu[$r]=array(	'fk_menu'=>0,			                // Put 0 if this is a top menu
								'type'=>'top',			                // This is a Top menu entry
								'titre'=>$langs->trans('NdfpMenuTitle'),
								'mainmenu'=>'ndfp',
								'leftmenu'=>'',
								'url'=>'/ndfp/ndfp.php',
								'langs'=> 'ndfp@ndfp',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>30,
								'enabled'=>'$conf->ndfp->enabled ', //&& (!(! empty($conf->comptabilite->enabled) || ! empty($conf->accounting->enabled) || ! empty($conf->facture->enabled) || ! empty($conf->deplacement->enabled) || ! empty($conf->don->enabled) || ! empty($conf->tax->enabled)))	
								'perms'=>'$user->rights->ndfp->allactions->read || $user->rights->ndfp->myactions->read',			                // Use 'perms'=>'$user->rights->report->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);	
		}

		$r++;
		$this->menu[$r]=array(
			'fk_menu'=>'fk_mainmenu='.$mainmenu.'',			// Put 0 if this is a top menu
			'type'=> 'left',			// This is a Top menu entry
			'titre'=> $langs->trans('NdfpMenuTitle'),
			'mainmenu'=> $mainmenu,
			'leftmenu'=> 'ndfp',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
			'url'=> '/ndfp/ndfp.php',
			'langs'=> 'ndfp@ndfp',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=> 100,
			'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
			'perms'=> '$user->rights->ndfp->allactions->read || $user->rights->ndfp->myactions->read',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=> '',
			'user'=> 2	// 0=Menu for internal users, 1=external users, 2=both
		);

		$r++;

        $this->menu[$r]=array(
            'fk_menu'=>'fk_mainmenu='.$mainmenu.',fk_leftmenu=ndfp',			// Put 0 if this is a top menu
        	'type'=> 'left',			// This is a Top menu entry
        	'titre'=> $langs->trans('NewNdfp'),
        	'mainmenu'=> $mainmenu,
        	'leftmenu'=> 'ndfp_new',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
			'url'=> '/ndfp/ndfp.php?action=create',
			'langs'=> 'ndfp@ndfp',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=> 101,
			'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
			'perms'=> '$user->rights->ndfp->allactions->create || $user->rights->ndfp->myactions->create',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=> '',
			'user'=> 2
        );

        $r++;
        $this->menu[$r]=array(
            'fk_menu'=>'fk_mainmenu='.$mainmenu.',fk_leftmenu=ndfp',			// Put 0 if this is a top menu
        	'type'=> 'left',			// This is a Top menu entry
        	'titre'=> $langs->trans('ListNdfp'),
        	'mainmenu'=> $mainmenu,
        	'leftmenu'=> 'ndfp_list',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
			'url'=> '/ndfp/ndfp.php?action=list',
			'langs'=> 'ndfp@ndfp',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=> 102,
			'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
			'perms'=> '$user->rights->ndfp->allactions->read || $user->rights->ndfp->myactions->read',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=> '',
			'user'=> 2
        );

        $r++;
        $this->menu[$r]=array(
            'fk_menu'=>'fk_mainmenu='.$mainmenu.',fk_leftmenu=ndfp',			// Put 0 if this is a top menu
        	'type'=> 'left',			// This is a Top menu entry
        	'titre'=> $langs->trans('UnpaidNdfp'),
        	'mainmenu'=> $mainmenu,
        	'leftmenu'=> 'ndfp_unpaid',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
			'url'=> '/ndfp/ndfp.php?filter=unpaid',
			'langs'=> 'ndfp@ndfp',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=> 103,
			'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
			'perms'=> '$user->rights->ndfp->allactions->read || $user->rights->ndfp->myactions->read',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=> '',
			'user'=> 2
        );

        $r++;
        $this->menu[$r]=array(
            'fk_menu'=>'fk_mainmenu='.$mainmenu.',fk_leftmenu=ndfp',			// Put 0 if this is a top menu
        	'type'=> 'left',			// This is a Top menu entry
        	'titre'=> $langs->trans('PaymentsNdfp'),
        	'mainmenu'=> $mainmenu,
        	'leftmenu'=> 'ndfp_payment',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
			'url'=> '/ndfp/payment.php',
			'langs'=> 'ndfp@ndfp',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=> 104,
			'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
			'perms'=> '$user->rights->ndfp->allactions->read || $user->rights->ndfp->myactions->read',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=> '',
			'user'=> 2
        );
        
		$r++;
        $this->menu[$r]=array(
            'fk_menu'=>'fk_mainmenu='.$mainmenu.',fk_leftmenu=ndfp',			// Put 0 if this is a top menu
        	'type'=> 'left',			// This is a Top menu entry
        	'titre'=> $langs->trans('StatsNdfp'),
        	'mainmenu'=> $mainmenu,
        	'leftmenu'=> 'ndfp_stats',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
			'url'=> '/ndfp/stats.php',
			'langs'=> 'ndfp@ndfp',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=> 105,
			'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
			'perms'=> '$user->rights->ndfp->allactions->read || $user->rights->ndfp->myactions->read',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=> '',
			'user'=> 2
        );


		// Exports
		//--------
		$r=1;
 
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='UsersNotesAndNotesLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='bill';
		$this->export_permission[$r]=array(array("ndfp","allactions","export"));
		$this->export_fields_array[$r]=array(
		'n.rowid'=>"Id",
		'n.ref'=>"Ref",
		'n.datec'=>"CreationDate",
		'n.dates'=>"DateStart",
		'n.datee'=>"DateEnd",
		'n.statut'=>"Statut",
		'n.total_tva'=>"Total_TVA",
		'n.total_ht'=>"Total_HT",
		'n.total_ttc'=>"Total_TTC",
		'n.description'=>"Desc",
		'n.comment_user'=>"UserComment",
		'n.comment_admin'=>"AdminComment",
		'n.cur_iso'=>"Currency",		
		'nd.rowid'=>'LineId',
		'nd.comment'=>"Comment",
		'nd.ref_ext'=>"ExternalReference",
		'nd.dated'=>"DateStart",
		'nd.datef'=>"DateEnd",
		'nd.qty'=>"Qty",
		'nd.total_ht'=>"Total_HT",
		'nd.total_tva'=>"Total_TVA",
		'nd.total_ttc'=>"Total_TTC",
		't.taux'=>'TVA',
		'e.label' => 'ExpenseType',
		'u.lastname' => 'Lastname',
		'u.firstname' => 'Firstname',
		'tc.label'=> 'TaxRating',
		'p.title' => 'Project',
		's.nom' => 'Society');

		$this->export_TypeFields_array[$r]=array(
		'n.rowid'=>"Numeric",
		'n.ref'=>"Text",
		'n.datec'=>"Date",
		'n.dates'=>"Date",
		'n.datee'=>"Date",
		'n.statut'=>"Status",
		'n.total_tva'=>"Numeric",
		'n.total_ht'=>"Numeric",
		'n.total_ttc'=>"Numeric",
		'n.description'=>"Text",
		'n.comment_user'=>"Text",
		'n.comment_admin'=>"Text",
		'n.cur_iso'=>"Text",		
		'nd.rowid'=>'Numeric',
		'nd.comment'=>"Text",
		'nd.ref_ext'=>"Text",
		'nd.dated'=>"Date",
		'nd.datef'=>"Date",
		'nd.qty'=>"Numeric",
		'nd.total_ht'=>"Numeric",
		'nd.total_tva'=>"Numeric",
		'nd.total_ttc'=>"Numeric",
		't.taux'=>'Numeric',
		'e.label' => 'Text',
		'u.lastname' => 'Text',
		'u.firstname' => 'Text',
		'tc.label'=> 'Text',
		'p.title' => 'Text',
		's.nom' => 'Text');

		$this->export_entities_array[$r]=array(
		'n.rowid'=>"bill:NdfpSing",
		'n.ref'=>"bill:NdfpSing",
		'n.datec'=>"bill:NdfpSing",
		'n.dates'=>"bill:NdfpSing",
		'n.datee'=>"bill:NdfpSing",
		'n.statut'=>"bill:NdfpSing",
		'n.total_tva'=>"bill:NdfpSing",
		'n.total_ht'=>"bill:NdfpSing",
		'n.total_ttc'=>"bill:NdfpSing",
		'n.description'=>"bill:NdfpSing",
		'n.comment_user'=>"bill:NdfpSing",
		'n.comment_admin'=>"bill:NdfpSing",		
		'n.cur_iso'=>"bill:NdfpSing",
		'u.lastname' => 'user',
		'u.firstname' => 'user',
		'tc.label'=> 'bill:TaxRating',
		'p.title' => 'project:Project',
		's.nom' => 'company',		
		'nd.rowid'=>'bill:ExpenseLine',
		'nd.comment'=>"bill:ExpenseLine",
		'nd.ref_ext'=>"bill:ExpenseLine",
		'nd.dated'=>"bill:ExpenseLine",
		'nd.datef'=>"bill:ExpenseLine",
		'nd.qty'=>"bill:ExpenseLine",
		'nd.total_ht'=>"bill:ExpenseLine",
		'nd.total_tva'=>"bill:ExpenseLine",
		'nd.total_ttc'=>"bill:ExpenseLine",
		't.taux'=>'bill:ExpenseLine',
		'e.label' => 'bill:ExpenseLine');
				
		$this->export_dependencies_array[$r]=array(
		'ndfp_det'=>'nd.rowid'); // To add unique key if we ask a field of a child to avoid the DISTINCT to discard them

		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM ('.MAIN_DB_PREFIX.'ndfp AS n,'.MAIN_DB_PREFIX.'ndfp_det AS nd)';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'c_ndfp_exp AS e ON nd.fk_exp = e.rowid';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'c_ndfp_exp_tax_cat AS tc ON n.fk_cat = tc.rowid';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'c_tva AS t ON t.rowid = nd.fk_tva';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'user AS u ON u.rowid = n.fk_user';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'projet AS p ON p.rowid = n.fk_project';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'societe AS s ON s.rowid = n.fk_soc';
		$this->export_sql_end[$r] .=' WHERE n.rowid = nd.fk_ndfp';
		$this->export_sql_end[$r] .=' AND n.entity = '.$conf->entity;		
	}

	/**
	 *		Function called when module is enabled.
	 *		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *		It also creates data directories.
	 *      @return     int             1 if OK, 0 if KO
	 */
	function init($options = '')
	{
	   global $conf;

		$sql = array();

		$result = $this->load_tables();

		return $this->_init($sql, $options);
	}

	/**
	 *		Function called when module is disabled.
	 *      Remove from database constants, boxes and permissions from Dolibarr database.
	 *		Data directories are not deleted.
	 *      @return     int             1 if OK, 0 if KO
	 */
	function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql, $options);
	}


	/**
	 *		\brief		Create tables, keys and data required by module
	 * 					Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * 					and create data commands must be stored in directory /mymodule/sql/
	 *					This function is called by this->init.
	 * 		\return		int		<=0 if KO, >0 if OK
	 */
	function load_tables()
	{
		return $this->_load_tables('/ndfp/sql/');
	}
}

?>
