<?php

/**
 *  \brief      	Define head array for tabs of ndfp setup pages
 *  \return			Array of head
 */
function ndfpadmin_prepare_head()
{
	global $langs, $conf, $user;
	$h = 0;
	$head = array();
	
	$head[$h][0] = dol_buildpath('/ndfp/admin/config.php', 1);
	$head[$h][1] = $langs->trans("Setup");
	$head[$h][2] = 'config';
	$h++;
	
	$head[$h][0] = dol_buildpath('/ndfp/admin/dict.php', 1);
	$head[$h][1] = $langs->trans("Dict");
	$head[$h][2] = 'dict';
	$h++;

	$head[$h][0] = dol_buildpath('/ndfp/upgrade/index.php', 1);
	$head[$h][1] = $langs->trans("Upgrade");
	$head[$h][2] = 'updgrade';
	$h++;
	
    return $head;
}

/**
 *  \brief      	Define head array for tabs of ndfp pages
 *  \return			Array of head
 */
function ndfp_prepare_head($id)
{
	global $langs, $conf, $user;
	$h = 0;
	$head = array();
	
	$head[$h][0] = dol_buildpath('/ndfp/ndfp.php',1).'?id='.$id;
	$head[$h][1] = $langs->trans('NdfpSheet');
	$head[$h][2] = 'ndfp';
	$h++;
	   
	$head[$h][0] = dol_buildpath('/ndfp/note.php',1).'?id='.$id;
	$head[$h][1] = $langs->trans('NdfpNotes');
	$head[$h][2] = 'notes';
	$h++; 
	        
	$head[$h][0] = dol_buildpath('/ndfp/document.php',1).'?id='.$id;
	$head[$h][1] = $langs->trans('NdfpAttachedFiles');
	$head[$h][2] = 'documents';
	$h++;   
	
	
	$head[$h][0] = dol_buildpath('/ndfp/ndfp.php',1).'?action=followup&id='.$id;
	$head[$h][1] = $langs->trans('Followup');
	$head[$h][2] = 'followup';
	$h++;

    return $head;
}

/**
 *  \brief      	Define head array for tabs of ndfp payment pages
 *  \return			Array of head
 */

function ndfppayment_prepare_head($id)
{
	global $langs, $conf, $user;
	$h = 0;
	$head = array();
	
	$head[$h][0] = dol_buildpath('/ndfp/payment.php',1).'?id='.$id;
	$head[$h][1] = $langs->trans('PaymentSheet');
	$head[$h][2] = 'payment';
	$h++;   
    
	$head[$h][0] = dol_buildpath('/ndfp/payment.php',1).'?action=followup&id='.$id;
	$head[$h][1] = $langs->trans('Followup');
	$head[$h][2] = 'followup';
	$h++;

    return $head;
}

/**
 *  \brief      	Define head array for tabs of ndfp statistics pages
 *  \return			Array of head
 */
function ndfpstats_prepare_head($filter = '')
{
	global $langs, $conf, $user;
	$h = 0;
	$head = array();
	
	$head[$h][0] = dol_buildpath('/ndfp/stats.php?source=months'.$filter,1);
	$head[$h][1] = $langs->trans('ByMonthYear');
	$head[$h][2] = 'months';
	$h++;   

	$head[$h][0] = dol_buildpath('/ndfp/stats.php?source=categories'.$filter,1);
	$head[$h][1] = $langs->trans('ByCategoryYear');
	$head[$h][2] = 'categories';
	$h++;   

	$head[$h][0] = dol_buildpath('/ndfp/stats.php?source=users'.$filter,1);
	$head[$h][1] = $langs->trans('ByUserYear');
	$head[$h][2] = 'users';
	$h++;   

    return $head;
}

?>