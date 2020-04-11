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
 *	\file       htdocs/ndfp/list.php
 *	\ingroup    ndfp
 *	\brief      Page to list ndfp
 */

$res=@include("../main.inc.php");                   // For root directory
if (! $res) $res=@include("../../main.inc.php");    // For "custom" directory

require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT."/core/class/html.formfile.class.php";

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/CMailFile.class.php';

dol_include_once("/ndfp/class/ndfp.class.php");
dol_include_once("/ndfp/class/html.form.ndfp.class.php");

if (! empty($conf->projet->enabled)) 
{
	require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
}


$langs->load("ndfp@ndfp");

$action = GETPOST('action','aZ09');
$massaction = GETPOST('massaction','alpha');
$confirm = GETPOST('confirm','alpha');
$toselect = GETPOST('toselect', 'array');
$contextpage = GETPOST('contextpage','aZ') ? GETPOST('contextpage','aZ') : 'ndfplist';


$optioncss = GETPOST('optioncss','alpha');
$search_btn = GETPOST('button_search','alpha');
$search_remove_btn = GETPOST('button_removefilter','alpha');

$search_syear = GETPOST("search_syear","int");
$search_smonth = GETPOST("search_smonth","int");

$search_eyear = GETPOST("search_eyear","int");
$search_emonth = GETPOST("search_emonth","int");

$search_ref = GETPOST('search_ref','alpha')!='' ? GETPOST('search_ref','alpha') : GETPOST('sref','alpha');

$search_user_author_id = GETPOST('search_user_author_id','int');

$search_fk_soc = GETPOST('search_fk_soc','int');
$search_fk_user = GETPOST('search_fk_user','int');
$search_fk_project = GETPOST('search_fk_project','int');

$search_soc = GETPOST('search_soc', 'alpha');
$search_user = GETPOST('search_user', 'alpha');
$search_project = GETPOST('search_project', 'alpha');

$search_total_ht = GETPOST('search_total_ht');
$search_total_ttc = GETPOST('search_total_ttc');

$search_statut = GETPOST('search_statut');

$filter = GETPOST('filter', 'alpha');

// Security check
$id = GETPOST('id','int');
if (!($user->rights->ndfp->myactions->read || $user->rights->ndfp->allactions->read))
{
    accessforbidden();
}

$diroutputmassaction = $conf->ndfp->dir_output . '/temp/massgeneration/'.$user->id;

// Load variable for pagination
$limit = GETPOST('limit','int')?GETPOST('limit','int'):$conf->liste_limit;
$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page",'int');
if (empty($page) || $page == -1 || !empty($search_btn) || !empty($search_remove_btn) || (empty($toselect) && $massaction === '0')) { $page = 0; }     // If $page is not defined, or '' or -1
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortfield) $sortfield='n.ref';
if (! $sortorder) $sortorder='DESC';

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$object = new Ndfp($db);
$hookmanager->initHooks(array('ndfplist'));
$extrafields = new ExtraFields($db);

// fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label('ndfp');
$search_array_options=$extrafields->getOptionalsFromPost($object->table_element,'','search_');

// List of fields to search into when doing a "search in all"
$fieldstosearchall = array(
	'n.ref'=>'Ref',
);

$arrayfields=array(
	'n.ref'=>array('label'=>$langs->trans("Ref"), 'checked'=>1),

    'n.dates'=>array('label'=>$langs->trans("DateStart"), 'checked'=>1),
	'n.datee'=>array('label'=>$langs->trans("DateEnd"), 'checked'=>1),

	'u.lastname'=>array('label'=>$langs->trans("User"), 'checked'=>1),
	's.nom'=>array('label'=>$langs->trans("Society"), 'checked'=>1),
	'n.fk_project'=>array('label'=>$langs->trans("Project"), 'checked'=>1),

    'n.description'=>array('label'=>$langs->trans("Description"), 'checked'=>1),
	'n.total_ht'=>array('label'=>$langs->trans("Total_HT"), 'checked'=>1),
	'n.total_ttc'=>array('label'=>$langs->trans("Total_TTC"), 'checked'=>1),

    'n.datec'=>array('label'=>$langs->trans("DateCreation"), 'checked'=>1),
    
    'n.statut'=>array('label'=>$langs->trans("Status"), 'checked'=>1),

	'n.tms'=>array('label'=>$langs->trans("DateModificationShort"), 'checked'=>0, 'position'=>500),
);

/*
 * Actions
 */

$error = 0;

// Selection of new fields
include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

// Purge search criteria
if (GETPOST('button_removefilter_x','alpha') || GETPOST('button_removefilter.x','alpha') || GETPOST('button_removefilter','alpha')) // All tests are required to be compatible with all browsers
{
    
    $search_syear = '';
    $search_smonth = '';
    $search_sday = '';

    $search_eyear = '';
    $search_emonth = '';
    $search_eday = '';

    $search_ref = '';

    $search_user_author_id = '';

    $search_fk_soc = '';
    $search_fk_user = '';
    $search_fk_project = '';

    $search_soc = '';
    $search_user = '';
    $search_project = '';

    $search_total_th = '';
    $search_total_ttc = '';
    $search_statut = '';

    $toselect = '';
    $search_array_options = array();
}


/*
 * View
 */

$now=dol_now();

$form = new Form($db);
$formother = new FormOther($db);
$ndfpform = new NdfpForm($db);
$formfile = new FormFile($db);

$userstatic = new User($db);

$title = $langs->trans("ListNdfp");
$help_url = "";

$sql = 'SELECT';
if ($sall) $sql = 'SELECT DISTINCT';

$sql.= " n.rowid, n.ref, n.datec, n.tms, n.total_ht, n.total_ttc, n.fk_project, n.fk_user, n.statut, n.fk_soc, n.dates, n.datee, n.description, n.billed, ";
$sql.= " u.rowid as uid, u.lastname, u.firstname, s.nom AS soc_name, s.rowid AS soc_id, u.login, n.total_tva";
if (!empty($conf->projet->enabled))
{
    $sql.= ", p.rowid as pid, p.title as ptitle, p.ref as pref";
}
$sql.= " FROM ".MAIN_DB_PREFIX."ndfp as n";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON n.fk_user = u.rowid";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON s.rowid = n.fk_soc";
if (!empty($conf->projet->enabled))
{
    $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."projet as p ON p.rowid = n.fk_project";
}

$sql.= ' WHERE n.entity IN ('.getEntity('ndfp').')';

if ($search_ref) $sql .= natural_search('n.ref', $search_ref);
if ($sall) $sql .= natural_search(array_keys($fieldstosearchall), $sall);

if ($search_soc) $sql .= natural_search('s.nom', $search_soc);
if ($search_project) $sql .= natural_search('p.ref', $search_project);

if ($filter == 'unpaid')
{
    $sql.= " AND n.statut = 1";
}

if ($fk_soc > 0)
{
    $sql .= " AND n.fk_soc = ".$fk_soc;
}

if ($fk_user > 0)
{
    $sql .= " AND n.fk_user = ".$fk_user;
}

if ($user->rights->ndfp->myactions->read && !$user->rights->ndfp->allactions->read)
{
    $sql .= " AND n.fk_user = ".$user->id;
}

if ($search_user)
{
    $sql.= ' AND (u.lastname LIKE \'%'.$db->escape(trim($search_user)).'%\' OR u.firstname LIKE \'%'.$db->escape(trim($search_user)).'%\')';
}


if ($search_total_ht)
{
    $sql.= ' AND n.total_ht = '.$db->escape(price2num(trim($search_total_ht)));
}
if ($search_total_ttc)
{
    $sql.= ' AND n.total_ttc = '.$db->escape(price2num(trim($search_total_ttc)));
}


if ($search_smonth > 0)
{
    if ($search_syear > 0)
    $sql.= " AND n.dates BETWEEN '".$db->idate(dol_get_first_day($search_syear,$search_smonth,false))."' AND '".$db->idate(dol_get_last_day($search_syear,$search_smonth,false))."'";
    else
    $sql.= " AND date_format(n.dates, '%m') = '".$search_smonth."'";
}
else if ($search_syear > 0)
{
    $sql.= " AND n.dates BETWEEN '".$db->idate(dol_get_first_day($search_syear,1,false))."' AND '".$db->idate(dol_get_last_day($search_syear,12,false))."'";
}

if ($search_emonth > 0)
{
    if ($search_eyear > 0)
    $sql.= " AND n.datee BETWEEN '".$db->idate(dol_get_first_day($search_eyear,$search_emonth,false))."' AND '".$db->idate(dol_get_last_day($search_eyear,$search_emonth,false))."'";
    else
    $sql.= " AND date_format(n.datee, '%m') = '".$search_emonth."'";
}
else if ($search_eyear > 0)
{
    $sql.= " AND n.datee BETWEEN '".$db->idate(dol_get_first_day($search_eyear,1,false))."' AND '".$db->idate(dol_get_last_day($search_eyear,12,false))."'";
}

if (is_numeric($search_statut))
{
    $sql.= " AND n.statut  = ".$search_statut;
}

$sql.= $db->order($sortfield,$sortorder);

// Count total nb of records
$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
{
	$result = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($result);

	if (($page * $limit) > $nbtotalofrecords)	// if total resultset is smaller then paging size (filtering), goto and load page 0
	{
		$page = 0;
		$offset = 0;
	}
}

$sql.= $db->plimit($limit + 1,$offset);
//print $sql;

$resql = $db->query($sql);
if ($resql)
{
	$title = $langs->trans('Ndfps');

	$num = $db->num_rows($resql);

	$arrayofselected=is_array($toselect)?$toselect:array();

	if ($num == 1 && ! empty($conf->global->MAIN_SEARCH_DIRECT_OPEN_IF_ONLY_ONE) && $sall)
	{
		$obj = $db->fetch_object($resql);
		$id = $obj->rowid;
		
		$url = dol_buildpath('/ndfp/ndfp.php', 1).'?id='.$id;

		header("Location: ".$url);
		exit;
	}

	llxHeader('',$title,$help_url);

	$param='';

	if (! empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) $param.='&contextpage='.urlencode($contextpage);
	if ($limit > 0 && $limit != $conf->liste_limit) $param.='&limit='.urlencode($limit);
	if ($sall)					$param.='&sall='.urlencode($sall);

	if ($search_smonth)      		$param.='&search_smonth='.urlencode($search_smonth);
	if ($search_syear)       		$param.='&search_syear='.urlencode($search_syear);

	if ($search_emonth)      		$param.='&search_emonth='.urlencode($search_emonth);
	if ($search_eyear)       		$param.='&search_eyear='.urlencode($search_dyear);

	if ($search_fk_user > 0) 		$param.='&search_fk_user='.urlencode($search_fk_user);
	if ($search_fk_project > 0) 	$param.='&search_fk_project='.urlencode($search_fk_project);
    if ($search_fk_soc > 0) 		$param.='&search_fk_soc='.urlencode($search_fk_soc);
    
	if ($search_soc) 		$param.='&search_soc='.urlencode($search_soc);
	if ($search_user) 		$param.='&search_user='.urlencode($search_user);
	if ($search_project) 		$param.='&search_project='.urlencode($search_project);

	if ($search_ref)      		$param.='&search_ref='.urlencode($search_ref);

	if ($search_statut) 	$param.='&search_statut='.urlencode($search_statut);

	if ($search_user_author_id > 0) 		$param.='&search_user_author_id='.urlencode($search_user_author_id);

	if ($optioncss != '')       $param.='&optioncss='.urlencode($optioncss);


	$massactionbutton='';

	$newcardbutton='';
	if ($contextpage == 'ndfplist' && $user->rights->ndfp->creer)
	{
		$newcardbutton='<a class="butActionNew" href="'.dol_buildpath('/ndfp/ndfp.php?action=create', 2).'"><span class="valignmiddle">'.$langs->trans('NewNdfp').'</span>';
		$newcardbutton.= '<span class="fa fa-plus-circle valignmiddle"></span>';
		$newcardbutton.= '</a>';
	}

	// Lines of title fields
	print '<form method="POST" id="searchFormList" action="'.$_SERVER["PHP_SELF"].'">';
	if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
	print '<input type="hidden" name="action" value="list">';
	print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
	print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
	print '<input type="hidden" name="page" value="'.$page.'">';
	print '<input type="hidden" name="contextpage" value="'.$contextpage.'">';


	print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'ndfp@ndfp', 0, $newcardbutton, '', $limit);

	if ($sall)
	{
		foreach($fieldstosearchall as $key => $val) $fieldstosearchall[$key]=$langs->trans($val);
		print '<div class="divsearchfieldfilter">'.$langs->trans("FilterOnInto", $sall) . join(', ',$fieldstosearchall).'</div>';
	}

	$moreforfilter='';

	// If the user can view other users
	if ($user->rights->user->user->lire)
	{
		$moreforfilter.='<div class="divsearchfield">';
		$moreforfilter.=$langs->trans('CreatedByUsers'). ': ';
		$moreforfilter.=$form->select_dolusers($search_user_author_id, 'search_user_author_id', 1, '', 0, '', '', 0, 0, 0, '', 0, '', 'maxwidth200');
	 	$moreforfilter.='</div>';
	}

    if (! empty($moreforfilter))
	{
		print '<div class="liste_titre liste_titre_bydiv centpercent">';
		print $moreforfilter;
		print '</div>';
	}

	$varpage=empty($contextpage)?$_SERVER["PHP_SELF"]:$contextpage;
	$selectedfields='';//$form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);	// This also change content of $arrayfields

	print '<div class="div-table-responsive">';
	print '<table class="tagtable liste'.($moreforfilter?" listwithfilterbefore":"").'">'."\n";

	print '<tr class="liste_titre_filter">';
	
	// Ref
	if (! empty($arrayfields['n.ref']['checked']))
	{
		print '<td class="liste_titre">';
		print '<input class="flat" size="6" type="text" name="search_ref" value="'.$search_ref.'">';
		print '</td>';
	}
	
	// Date de reception
	if (! empty($arrayfields['n.dates']['checked']))
	{
		print '<td class="liste_titre nowraponall" align="left">';
		print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="search_smonth" value="'.$search_smonth.'">';
		$formother->select_year($search_syear?$search_syear:-1,'search_syear',1, 20, 5);
		print '</td>';
    }
    
	
	// Date de reception
	if (! empty($arrayfields['n.datee']['checked']))
	{
		print '<td class="liste_titre nowraponall" align="left">';
		print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="search_emonth" value="'.$search_emonth.'">';
		$formother->select_year($search_eyear?$search_eyear:-1,'search_eyear',1, 20, 5);
		print '</td>';
    }
 
    if (! empty($arrayfields['u.lastname']['checked']))
	{
		print '<td class="liste_titre">';
		print '<input class="flat" size="20" type="text" name="search_user" value="'.$search_user.'">';
		print '</td>';
	}

	if (! empty($arrayfields['s.nom']['checked']))
	{
		print '<td class="liste_titre">';
		print '<input class="flat" size="20" type="text" name="search_soc" value="'.$search_soc.'">';
		print '</td>';
    }
  
	if (! empty($arrayfields['n.fk_project']['checked']))
	{
		print '<td class="liste_titre">';
		print '<input class="flat" size="20" type="text" name="search_project" value="'.$search_project.'">';
		print '</td>';
    }

	if (! empty($arrayfields['n.description']['checked']))
	{
		print '<td class="liste_titre">';
		print '&nbsp;';
		print '</td>';
    }

    if (! empty($arrayfields['n.total_ht']['checked']))
	{
		print '<td class="liste_titre">';
		print '<input class="flat" size="10" type="text" name="search_total_ht" value="'.$search_total_ht.'">';
		print '</td>';
    }

    if (! empty($arrayfields['n.total_ttc']['checked']))
	{
		print '<td class="liste_titre">';
		print '<input class="flat" size="10" type="text" name="search_total_ttc" value="'.$search_total_ttc.'">';
		print '</td>';
    }

    	// Date de saisie
	if (! empty($arrayfields['n.datec']['checked']))
	{
		print '<td class="liste_titre nowraponall" align="left">';
		if (! empty($conf->global->MAIN_LIST_FILTER_ON_DAY)) print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="search_dday" value="'.$search_dday.'">';
		print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="search_dmonth" value="'.$search_dmonth.'">';
		$formother->select_year($search_dyear?$search_dyear:-1,'search_dyear',1, 20, 5);
		print '</td>';
    }
    
	// Conditionnement
	if (! empty($arrayfields['n.statut']['checked']))
	{
		print '<td class="liste_titre">';
		print $ndfpform->select_ndfp_status($search_statut, 'search_statut', '', true);
		print '</td>';
	}

	// Date modification
	if (! empty($arrayfields['n.tms']['checked']))
	{
		print '<td class="liste_titre">';
		print '</td>';
	}

	// Action column
	print '<td class="liste_titre" align="middle">';
	$searchpicto=$form->showFilterButtons();
	print $searchpicto;
	print '</td>';

	print "</tr>\n";

	// Fields title
	print '<tr class="liste_titre">';


	if (! empty($arrayfields['n.ref']['checked']))            print_liste_field_titre($arrayfields['n.ref']['label'],$_SERVER["PHP_SELF"],'n.ref','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['n.dates']['checked']))            print_liste_field_titre($arrayfields['n.dates']['label'],$_SERVER["PHP_SELF"],'n.dates','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['n.datee']['checked']))            print_liste_field_titre($arrayfields['n.datee']['label'],$_SERVER["PHP_SELF"],'n.datee','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['u.lastname']['checked']))            print_liste_field_titre($arrayfields['u.lastname']['label'],$_SERVER["PHP_SELF"],'','',$param,'',$sortfield,$sortorder);

	if (! empty($arrayfields['s.nom']['checked']))            print_liste_field_titre($arrayfields['s.nom']['label'],$_SERVER["PHP_SELF"],'s.nom','',$param,'',$sortfield,$sortorder);
    
    if (! empty($arrayfields['n.fk_project']['checked']))            print_liste_field_titre($arrayfields['n.fk_project']['label'],$_SERVER["PHP_SELF"],'n.fk_project','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['n.description']['checked']))            print_liste_field_titre($arrayfields['n.description']['label'],$_SERVER["PHP_SELF"],'n.description','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['n.total_ht']['checked']))            print_liste_field_titre($arrayfields['n.total_ht']['label'],$_SERVER["PHP_SELF"],'n.total_ht','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['n.total_ttc']['checked']))            print_liste_field_titre($arrayfields['n.total_ttc']['label'],$_SERVER["PHP_SELF"],'n.total_ttc','',$param,'',$sortfield,$sortorder);

	if (! empty($arrayfields['n.datec']['checked']))     print_liste_field_titre($arrayfields['n.datec']['label'],$_SERVER["PHP_SELF"],'n.datec','',$param,'',$sortfield,$sortorder);
    if (! empty($arrayfields['n.statut']['checked']))     print_liste_field_titre($arrayfields['n.statut']['label'],$_SERVER["PHP_SELF"],'n.statut','',$param,'',$sortfield,$sortorder);
    if (! empty($arrayfields['n.tms']['checked']))       print_liste_field_titre($arrayfields['n.tms']['label'],$_SERVER["PHP_SELF"],"e.tms","",$param,'align="left" class="nowrap"',$sortfield,$sortorder);

	print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"],"",'',$param,'align="center"',$sortfield,$sortorder,'maxwidthsearch ');
	print '</tr>'."\n";

	$i=0;
	$totalarray=array();
	while ($i < min($num,$limit))
	{
        $userstatic = new User($db);
        $ndfpstatic = new Ndfp($db);
        $societestatic = new Societe($db);

		$obj = $db->fetch_object($resql);

        if (!empty($conf->projet->enabled))
        {
            $projectstatic = new Project($db);

            $projectstatic->ref  = $obj->pref;
            $projectstatic->title = $obj->ptitle;
            $projectstatic->id = $obj->pid;
        }


        $userstatic->lastname  = $obj->lastname;
        $userstatic->firstname = $obj->firstname;
        $userstatic->id = $obj->uid;

        $societestatic->id = $obj->soc_id;
        $societestatic->name = $obj->soc_name;

        $ndfpstatic->id = $obj->rowid;
        $ndfpstatic->ref = $obj->ref;
        $ndfpstatic->statut = $obj->statut;

        $ndfpstatic->already_paid = $ndfpstatic->get_amount_payments_done();
        $ndfpstatic->filename = dol_sanitizeFileName($obj->ref);
        $ndfpstatic->filedir = $conf->ndfp->dir_output . '/' . dol_sanitizeFileName($obj->ref);
        $ndfpstatic->urlsource = dol_buildpath('/ndfp/ndfp.php', 1).'?id='.$obj->rowid;


		print '<tr class="oddeven">';

		// Ref
		if (! empty($arrayfields['n.ref']['checked']))
		{
			print '<td class="nowrap">';

            print '<table class="nobordernopadding">';
            print '<tr class="nocellnopadd">';
            print '    <td class="nobordernopadding" nowrap="nowrap">'.$ndfpstatic->getnomUrl(1).'</td>';
            print '    <td width="16" align="right" class="nobordernopadding">';
            $formfile->show_documents('ndfp',$ndfpstatic->filename,$ndfpstatic->filedir,$ndfpstatic->urlsource,'','','',1,'',1);
            print '    </td>';
            print '</tr>';
            print '</table>';
			print '</td>';
			if (! $i) $totalarray['nbfield']++;
		}
        
		if (! empty($arrayfields['n.dates']['checked']))
		{
			print '<td align="left">';
			print dol_print_date($db->jdate($obj->dates), 'day');
			print '</td>';
			if (! $i) $totalarray['nbfield']++;
		}

		if (! empty($arrayfields['n.datee']['checked']))
		{
			print '<td align="left">';
			print dol_print_date($db->jdate($obj->datee), 'day');
			print '</td>';
			if (! $i) $totalarray['nbfield']++;
		}

		if (! empty($arrayfields['u.lastname']['checked']))
		{
			print '<td align="left">';
			print $userstatic->getNomUrl(1);
			print '</td>';
			if (! $i) $totalarray['nbfield']++;
		}

		if (! empty($arrayfields['s.nom']['checked']))
		{
			print '<td align="left">';
			print ($obj->fk_soc > 0 ? $societestatic->getNomUrl(1) : '&nbsp;');
			print '</td>';
			if (! $i) $totalarray['nbfield']++;
		}

		if (! empty($arrayfields['n.fk_project']['checked']))
		{
            print '<td align="left">';
            if (!empty($conf->projet->enabled))
            {
                print ($obj->fk_project > 0 ? $projectstatic->getNomUrl(1) : '&nbsp;');
            }
            else
            {
                print '&nbsp;';
            }
			print '</td>';
			if (! $i) $totalarray['nbfield']++;
		}


		if (! empty($arrayfields['n.description']['checked']))
		{
			print '<td align="left">';
			print $obj->description;
			print '</td>';
			if (! $i) $totalarray['nbfield']++;
		}

		if (! empty($arrayfields['n.total_ht']['checked']))
		{
			print '<td align="left">';
			print price($obj->total_ht);
			print '</td>';
			if (! $i) $totalarray['nbfield']++;
		}

		if (! empty($arrayfields['n.total_ttc']['checked']))
		{
			print '<td align="left">';
			print price($obj->total_ttc);
			print '</td>';
			if (! $i) $totalarray['nbfield']++;
		}

		// 
		if (! empty($arrayfields['n.datec']['checked']))
		{
			print '<td align="left">';
			print dol_print_date($db->jdate($obj->datec), 'day');
			print '</td>';
			if (! $i) $totalarray['nbfield']++;
		}

        		// 
		if (! empty($arrayfields['n.statut']['checked']))
		{
			print '<td align="left">';
			print $ndfpstatic->get_lib_statut(5, $ndfpstatic->already_paid);
			print '</td>';
			if (! $i) $totalarray['nbfield']++;
		}


		// Date modification
		if (! empty($arrayfields['n.tms']['checked']))
		{
			print '<td align="left" class="nowrap">';
			print dol_print_date($db->jdate($obj->tms), 'dayhour', 'tzuser');
			print '</td>';
			if (! $i) $totalarray['nbfield']++;
		}

		// Action column
		print '<td class="nowrap" align="center">';
		print '&nbsp;';
		print '</td>';
		if (! $i) $totalarray['nbfield']++;

		print "</tr>\n";

		$i++;
	}

	$db->free($resql);

	print '</table>'."\n";
	print '</div>';

	print '</form>'."\n";

}
else
{
	dol_print_error($db);
}

// End of page
llxFooter();
$db->close();
