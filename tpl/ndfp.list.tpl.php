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

/**	    \file       htdocs/ndfp/tpl/ndfp.note.tpl.php
 *		\ingroup    ndfp
 *		\brief      Ndfp module expenses list view
 */


llxHeader();


print_barre_liste($langs->trans('Ndfps'), $page, 'ndfp.php', '', $sortfield, $sortorder, '', $num);

?>

<form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
<table class="liste" width="100%">
<tr class="liste_titre">
            <?php print_liste_field_titre($langs->trans('Ref'),$_SERVER['PHP_SELF'],'n.ref','','filter='.$filter,'',$sortfield,$sortorder); ?>
            <?php print_liste_field_titre($langs->trans('DateStart'),$_SERVER['PHP_SELF'],'n.dates','','filter='.$filter,'align="center"',$sortfield,$sortorder); ?>
            <?php print_liste_field_titre($langs->trans('DateEnd'),$_SERVER['PHP_SELF'],"n.datee",'','filter='.$filter,'align="center"',$sortfield,$sortorder); ?>
            <?php print_liste_field_titre($langs->trans('User'),$_SERVER['PHP_SELF'],'u.lastname','','filter='.$filter,'',$sortfield,$sortorder); ?>
            <?php print_liste_field_titre($langs->trans('Society'),$_SERVER['PHP_SELF'],'soc_name','','filter='.$filter,'',$sortfield,$sortorder); ?>
            <?php print_liste_field_titre($langs->trans('Total_HT'),$_SERVER['PHP_SELF'],'n.total_ht','','filter='.$filter,'align="right"',$sortfield,$sortorder); ?>
            <?php print_liste_field_titre($langs->trans('Total_TTC'),$_SERVER['PHP_SELF'],'n.total_ttc','','filter='.$filter,'align="right"',$sortfield,$sortorder); ?>
            <?php print_liste_field_titre($langs->trans('Paid'),$_SERVER['PHP_SELF'],'already_paid','','filter='.$filter,'align="right"',$sortfield,$sortorder); ?>
            <?php if ($conf->global->NDFP_INVOICES) { ?>
            <?php print_liste_field_titre($langs->trans('Billed'),$_SERVER['PHP_SELF'],'n.billed','','filter='.$filter,'align="right"',$sortfield,$sortorder); ?>
            <?php } ?>
            <?php print_liste_field_titre($langs->trans('Status'),$_SERVER['PHP_SELF'],'n.statut','','filter='.$filter,'align="right"',$sortfield,$sortorder); ?>
</tr>
<tr class="liste_titre">
    <td class="liste_titre" align="left">
        <input class="flat" size="10" type="text" name="search_ref" value="<?php echo $search_ref; ?>" />
    </td>
    <td class="liste_titre" align="center">
        <input class="flat" type="text" size="1" maxlength="2" name="search_month_s" value="<?php echo $search_month_s; ?>" />
        <?php $htmlother->select_year($search_year_s ? $search_year_s : -1,'search_year_s', 1, 20, 5); ?>
    </td>
    <td class="liste_titre" align="center">
        <input class="flat" type="text" size="1" maxlength="2" name="search_month_e" value="<?php echo $search_month_e; ?>" />
        <?php $htmlother->select_year($search_year_e ? $search_year_e : -1,'search_year_e', 1, 20, 5); ?>    
    </td>
    <td class="liste_titre" align="left">
        <input class="flat" type="text" name="search_user" value="<?php echo $search_user; ?>" />
    </td>
    <td class="liste_titre" align="left">
        <input class="flat" type="text" name="search_soc" value="<?php echo $search_soc; ?>" />
    </td>    
    <td class="liste_titre" align="right">
        <input class="flat" type="text" size="10" name="search_ht_amount" value="<?php echo $search_ht_amount; ?>" />
    </td>
    <td class="liste_titre" align="right">
        <input class="flat" type="text" size="10" name="search_ttc_amount" value="<?php echo $search_ttc_amount; ?>" />
    </td>
    <td class="liste_titre" align="right">
        &nbsp;
    </td>
    <td class="liste_titre" align="right" colspan="<?php echo $conf->global->NDFP_INVOICES ? 2 : 1; ?>">
        <?php echo $ndfpHtml->select_ndfp_status($search_statut, 'search_statut'); ?>
    	<input type="hidden" name="filter" value="<?php echo $filter; ?>" />
        <input type="image" class="liste_titre" name="button_search" src="<?php echo DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" value="'.dol_escape_htmltag($langs->trans("Search"));?>" title="<?php echo dol_escape_htmltag($langs->trans("Search")); ?>" />
    </td>
</tr>
            
<?php 
    if (count($ndfps) > 0){
    	$totalht = 0;
		$totalttc = 0;
    	$totalpaid = 0;
    	$i = 0;
    	
    	foreach($ndfps AS $ndfp){ ?>
        <tr class="<?php echo $i%2 == 0 ? 'impair' : 'pair'; ?>">
           <td nowrap="nowrap">
                <table class="nobordernopadding">
                    <tr class="nocellnopadd">
                        <td class="nobordernopadding" nowrap="nowrap"><?php echo $ndfp->url;?></td>
                        <td width="16" align="right" class="nobordernopadding">
                            <?php $formfile->show_documents('ndfp',$ndfp->filename,$ndfp->filedir,$ndfp->urlsource,'','','',1,'',1); ?>
                        </td>
                    </tr>
                </table>
            </td>
            <td align="center" nowrap><?php echo dol_print_date($ndfp->dates,'day'); ?></td>
            <td align="center" nowrap><?php echo dol_print_date($ndfp->datee,'day'); ?></td>
            <td><?php echo $ndfp->username; ?></td>
            <td><?php echo $ndfp->society; ?></td>
            <td align="right"><?php echo price($ndfp->total_ht); ?></td>
            <td align="right"><?php echo price($ndfp->total_ttc); ?></td>
            <td align="right"><?php echo price($ndfp->already_paid); ?></td>
			<?php if ($conf->global->NDFP_INVOICES) { ?>
			 <td align="right">
				<?php echo yn($ndfp->billed); ?>
			</td>   
			<?php } ?>
            <td align="right" nowrap="nowrap"><?php echo $ndfp->statut; ?></td>        
        </tr>
    <?php
	    	$totalht += $ndfp->total_ht;
			$totalttc += $ndfp->total_ttc;
	    	$totalpaid += $ndfp->already_paid;
	    	
	    	$i++;
		}
	?>
		<tr class="liste_total">
            <td colspan="5">Total (<?php echo $i.' '.$langs->trans('Ndfps').', '.$langs->trans('RemainderToPay').' : '.price($totalttc - $totalpaid); ?>)</td>
            <td align="right"><?php echo price($totalht); ?></td>
            <td align="right"><?php echo price($totalttc); ?></td>
            <td align="right"><?php echo price($totalpaid); ?></td>
            <td align="right" colspan="<?php echo $conf->global->NDFP_INVOICES ? 2 : 1; ?>">&nbsp;</td>
        </tr>
<?php }else{ ?>
     <tr class="impair">
        <td colspan="<?php echo $conf->global->NDFP_INVOICES ? 10 : 9; ?>"><?php echo $langs->trans("NoResults"); ?></td>
    </tr>    
<?php } ?>
</table>
</form>
            
<?php llxFooter(''); ?>
