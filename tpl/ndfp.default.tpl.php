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

/**     \file       htdocs/ndfp/tpl/ndfp.default.tpl.php
 *      \ingroup    ndfp
 *      \brief      Ndfp module default view
 */

llxHeader('', '', '', '', 0, 0, array('/ndfp/js/functions.js.php?rowid='.$lineid.'&id='.$ndfp->id.'&fk_cat='.$ndfp->fk_cat));

echo ($message ? dol_htmloutput_mesg($message, '', ($error ? 'error' : 'ok'), 0) : '');

dol_fiche_head($head, $current_head, $langs->trans('Ndfp'));


        
?>

<?php echo $formconfirm ? $formconfirm : ''; ?>

<table class="border" width="100%">
    <tr>
        <td width="20%"><?php echo $langs->trans('Ref'); ?></td>
        <td colspan="5"><?php echo $html->showrefnav($ndfp,'ref','',1,'ref','ref',''); ?></td>
    </tr>

    <tr>
        <td>
            <table class="nobordernopadding" width="100%">
                <tr>
                    <td><?php echo $langs->trans('Company'); ?></td>
                    <td colspan="5" align="right">
                        <?php if ($ndfp->statut == 0){ ?>
                            <a href="<?php echo $_SERVER["PHP_SELF"].'?action=editsoc&amp;id='.$ndfp->id; ?>">
                                <?php echo img_edit($langs->trans('SetLinkToThirdParty'),1); ?>
                            </a>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </td>
        <td colspan="5">
            <?php
            if ($action == 'editsoc'){
                $html->form_thirdparty($_SERVER['PHP_SELF'].'?id='.$ndfp->id,$ndfp->fk_soc,'fk_soc');
            }else if ($ndfp->fk_soc > 0){ ?>
            &nbsp;<?php echo $societestatic->getNomUrl(1,'compta'); ?>
            &nbsp; (<a href="<?php echo $_SERVER['PHP_SELF'].'?fk_soc='.$ndfp->fk_soc; ?>"><?php echo $langs->trans('OtherNdfp'); ?></a>)
            <?php } ?>
        </td>
    </tr>

    <tr>
        <td>
            <table class="nobordernopadding" width="100%">
                <tr>
                    <td><?php echo $langs->trans('User'); ?></td>
                    <td colspan="5" align="right">
                        <?php if ($ndfp->statut == 0 && count($ndfp->lines) == 0){ ?>
                         <a href="<?php echo $_SERVER["PHP_SELF"].'?action=edituser&amp;id='.$ndfp->id; ?>">
                            <?php echo img_edit($langs->trans('SetUser'),1); ?>
                        </a>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </td>
        <td colspan="5">
            <?php
            if ($action == 'edituser'){
                $html->form_users($_SERVER['PHP_SELF'].'?id='.$ndfp->id,$ndfp->fk_user,'fk_user');
            }else{ ?>
            &nbsp;<?php echo $userstatic->getNomUrl(1); ?>
            &nbsp; (<a href="<?php echo $_SERVER['PHP_SELF'].'?fk_user='.$ndfp->fk_user; ?>"><?php echo $langs->trans('OtherNdfp'); ?></a>)
            <?php } ?>
        </td>
    </tr>

    <tr>
        <td width="20%">
            <table class="nobordernopadding" width="100%">
                <tr>
                    <td><?php echo $langs->trans('Desc'); ?></td>
                    <td colspan="5" align="right">
                        <?php if ($ndfp->statut == 0){ ?>
                         <a href="<?php echo $_SERVER["PHP_SELF"].'?action=editdesc&amp;id='.$ndfp->id; ?>">
                            <?php echo img_edit($langs->trans('SetDesc'),1); ?>
                        </a>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </td>
        <td colspan="5">
            <?php if ($action == 'editdesc'){ ?>
            <form method="POST" action="<?php echo $_SERVER["PHP_SELF"].'?id='.$ndfp->id; ?>" name="formdesc">
                <input type="hidden" name="action" value="setdesc" />
                <input type="hidden" name="token" value="<?php echo newToken(); ?>" />
                <table class="nobordernopadding" cellpadding="0" cellspacing="0">
                    <tr>
                        <td><input type="text" name="description" value="<?php echo $ndfp->description; ?>" size="52"/></td>
                        <td align="left"><input type="submit" class="button" value="<?php echo $langs->trans('Modify'); ?>" /></td>
                    </tr>
                </table>
            </form>
            <?php }else{ echo $ndfp->description; } ?>
        </td>

    </tr>

    <tr>
        <td width="20%">
            <table class="nobordernopadding" width="100%">
                <tr>
                    <td><?php echo $langs->trans('PaymentMode'); ?></td>
                    <td colspan="5" align="right">
                         <?php if ($ndfp->statut == 0){ ?>
                         <a href="<?php echo $_SERVER["PHP_SELF"].'?action=edit_paymentmode&amp;id='.$ndfp->id; ?>">
                            <?php echo img_edit($langs->trans('SetModeReglement'),1); ?>
                        </a>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </td>
        <td colspan="5">
            <?php if ($action == 'edit_paymentmode'){ ?>
            <form method="POST" action="<?php echo $_SERVER["PHP_SELF"].'?id='.$ndfp->id; ?>" name="formdesc">
                <input type="hidden" name="action" value="set_paymentmode" />
                <input type="hidden" name="token" value="<?php echo newToken(); ?>" />
                <table class="nobordernopadding" cellpadding="0" cellspacing="0">
                    <tr>
                        <td><?php echo $ndfpHtml->select_payment_modes($ndfp->fk_mode_reglement,'fk_mode_reglement'); ?></td>
                        <td align="left"><input type="submit" class="button" value="<?php echo $langs->trans('Modify'); ?>" /></td>
                    </tr>
                </table>
            </form>
            <?php }else{
                echo $ndfp->mode_reglement;
            } ?>
        </td>

    </tr>
        
    <tr>
        <td>
            <table class="nobordernopadding" width="100%">
                <tr>
                    <td>
                    <?php echo $langs->trans('DateStart'); ?>
                    </td>
                    <?php if ($action != 'editdates' && $ndfp->statut == 0){ ?>
                        <td align="right">
                            <a href="<?php echo $_SERVER["PHP_SELF"].'?action=editdates&amp;id='.$ndfp->id; ?>"><?php echo img_edit($langs->trans('SetDate'),1); ?></a>
                        </td>
                    <?php } ?>
                </tr>
            </table>
        </td>
        <td colspan="3">
            <?php if ($action == 'editdates'){
                    $html->form_date($_SERVER['PHP_SELF'].'?id='.$ndfp->id, $ndfp->dates, 'dates');
                }else{
                    echo dol_print_date($ndfp->dates, 'daytext');
                } ?>
        </td>
        <td rowspan="<?php echo $nbrows; ?>" colspan="2" valign="top">
            <table class="nobordernopadding" width="100%">
                <tr class="liste_titre">
                    <td><?php echo $langs->trans('PaymentsNdfp'); ?></td>
                    <td><?php echo $langs->trans('Type'); ?></td>
                    <td align="right"><?php echo $langs->trans('Amount'); ?></td>
                    <td width="18">&nbsp;</td>
                </tr>

                <?php for($k=0; $k < count($payments); $k++){ ?>
                 <tr class="<?php echo ($k%2==0 ? 'pair' : 'impair'); ?>">
                     <td>
                         <a href="<?php echo $payments[$k]->url; ?>">
                            <?php echo img_object($langs->trans('ShowPayment'),'payment').' '.dol_print_date($db->jdate($payments[$k]->dp),'day'); ?>
                         </a>
                     </td>
                     <td><?php echo $payments[$k]->label.' '.$payments[$k]->payment_number; ?></td>
                     <td align="right"><?php echo price($payments[$k]->amount); ?></td>
                     <td>&nbsp;</td>
                 </tr>
                <?php } ?>


               <tr>
                    <td colspan="2" align="right">
                    <?php echo $langs->trans('AlreadyPaid'); ?>
                    </td>
                    <td align="right">
                        <?php echo price($already_paid); ?>
                    </td>
                    <td>&nbsp;</td>
               </tr>


                <?php if ($ndfp->statut == 3){ ?>
                    <tr>
                        <td colspan="2" align="right" nowrap="1">

                        <?php
                            echo $html->textwithpicto($langs->trans("Abandoned").':',$langs->trans("HelpAbandonOther"),-1);
                         ?>
                        </td>
                        <td align="right">
                        <?php echo price($ndfp->total_ttc - $already_paid); ?>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php $remain_to_pay_for_display = 0;
                } ?>

                <tr>
                    <td colspan="2" align="right">
                        <?php echo $langs->trans("Billed"); ?> :
                    </td>
                    <td align="right" style="border: 1px solid;">
                    <?php echo price($ndfp->total_ttc); ?>
                    </td>
                    <td>&nbsp;</td>
                </tr>

                <tr>
                    <td colspan="2" align="right">
                    <?php
                        echo $langs->trans('RemainderToPay').' :';
                    ?>
                    </td>
                    <td align="right" style="border: 1px solid;" bgcolor="#f0f0f0">
                        <b><?php echo price($remain_to_pay_for_display); ?></b>
                    </td>
                    <td nowrap="nowrap">&nbsp;</td>
                </tr>
            </table>
       </td>
    </tr>

    <tr>
        <td>
            <table class="nobordernopadding" width="100%">
                <tr>
                    <td>
                    <?php echo $langs->trans('DateEnd'); ?>
                    </td>
                    <?php if ($action != 'editdatee' && $ndfp->statut == 0){ ?>
                        <td align="right">
                            <a href="<?php echo $_SERVER["PHP_SELF"].'?action=editdatee&amp;id='.$ndfp->id; ?>"><?php echo img_edit($langs->trans('SetDate'),1); ?></a>
                        </td>
                    <?php } ?>
                </tr>
            </table>
        </td>
        <td colspan="3">
            <?php if ($action == 'editdatee'){
                    $html->form_date($_SERVER['PHP_SELF'].'?id='.$ndfp->id, $ndfp->datee, 'datee');
                }else{
                    echo dol_print_date($ndfp->datee, 'daytext');
                } ?>
        </td>
    </tr>

    <tr>
        <td><?php echo $langs->trans('HTAmount'); ?></td>
        <td align="right" colspan="3" nowrap><?php echo price($ndfp->total_ht, 1, '', 1, - 1, - 1, $conf->currency); ?></td>
    </tr>

     <tr>
        <td><?php echo $langs->trans('TVAAmount'); ?></td>
        <td align="right" colspan="3" nowrap><?php echo price($ndfp->total_tva, 1, '', 1, - 1, - 1, $conf->currency); ?></td>
    </tr>

    <tr>
        <td><?php echo $langs->trans('TTCAmount'); ?></td>
        <td align="right" colspan="3" nowrap><?php echo price($ndfp->total_ttc, 1, '', 1, - 1, - 1, $conf->currency); ?></td>
    </tr>

     <tr>
        <td><?php echo $langs->trans('Status'); ?></td>
        <td align="left" colspan="3"><?php echo $ndfp->get_lib_statut(4, $already_paid); ?></td>
    </tr>

    <?php if ($conf->projet->enabled){ ?>
    <tr>
        <td>
           <table class="nobordernopadding" width="100%">
                <tr>
                    <td><?php echo $langs->trans('Project'); ?></td>
                <?php if ($action != 'setproject' && $ndfp->statut == 0){ ?>
                    <td align="right">
                        <a href="<?php echo $_SERVER["PHP_SELF"].'?action=setproject&amp;id='.$ndfp->id; ?>">
                            <?php echo img_edit($langs->trans('SetProject'),1); ?>
                        </a>
                    </td>
                <?php } ?>
                </tr>
           </table>
        </td>
        <td colspan="3">        
                <?php if ($action == 'setproject'){ ?>
                    <form method="POST" action="<?php echo $_SERVER["PHP_SELF"].'?id='.$ndfp->id; ?>" name="formdesc">
                        <input type="hidden" name="action" value="classin" />
                        <input type="hidden" name="token" value="<?php echo newToken(); ?>" />
                        <table class="nobordernopadding" cellpadding="0" cellspacing="0">
                            <tr>
                                <td><?php $html->form_project($_SERVER['PHP_SELF'].'?id='.$ndfp->id, $ndfp->fk_soc, $ndfp->fk_project,'fk_project'); ?></td>
                            </tr>
                        </table>
                    </form>                
                    
               <?php }else{
                    if ($projectstatic){
                        echo $projectstatic->getNomUrl(1);
                    }
                } ?>
        </td>
   </tr>
   <?php } ?>
</table>
<br />

<table id="tablelines" class="noborder" width="100%">
<?php if ($numLines > 0){ ?>
    <tr class="liste_titre nodrag nodrop">
        <td><?php echo $langs->trans('Type'); ?></td>
        <td align="right" width="90"><?php echo $langs->trans('Date'); ?></td>
        <td align="right" width="70"><?php echo $langs->trans('ExternalReference'); ?></td>
        <td align="right" width="50"><?php echo $langs->trans('Qty'); ?></td>
        <td align="right" width="70"><?php echo $langs->trans('MilestoneExpense'); ?></td>
        <td align="right" width="50"><?php echo $langs->trans('TVA'); ?></td>
        <td align="right" width="70"><?php echo $langs->trans('Total_HT'); ?> (<?php echo $langs->trans('Currency'.$ndfp->cur_iso); ?>)</td>
        <td align="right" width="70"><?php echo $langs->trans('Total_TTC'); ?> (<?php echo $langs->trans('Currency'.$ndfp->cur_iso); ?>)</td>
        <td width="50">&nbsp;</td>
    </tr>


<?php

for($i = 0; $i < $numLines; $i++){
    $line = $ndfp->lines[$i];
    $otherExpense = false;
    $kmExpense = false;
    
    if ($action == 'editline' && $lineid == $line->rowid){ ?>

    <form name="addexpense" id="addexpense" action="<?php echo $_SERVER["PHP_SELF"].'?id='.$ndfp->id; ?>" method="POST">
    <input type="hidden" name="token" value="<?php  echo newToken(); ?>" />
    <input type="hidden" name="action" value="updateline" />
    <input type="hidden" name="id" value="<?php echo $ndfp->id; ?>" />
    <input type="hidden" name="lineid" value="<?php echo $line->rowid; ?>" />
    <input type="hidden" name="fk_user" id="fk_user" value="<?php echo $ndfp->fk_user; ?>" />

    <?php } ?>

    <tr class="<?php echo ($i%2==0 ? 'impair' : 'pair'); ?>">
        <?php if ($action == 'editline' && $lineid == $line->rowid){ ?>
            <td colspan="2">           
                <?php echo $langs->trans("ExpenseType"); ?> : <select class="flat" id="fk_exp" name="fk_exp">
                    <?php for ($k=0; $k<count($predefined_expenses); $k++){ ?>
                        <option value="<?php echo $predefined_expenses[$k]->rowid; ?>" <?php echo ($predefined_expenses[$k]->rowid==$line->fk_exp ? 'selected="selected"' : ''); ?> >
                            <?php echo $langs->trans($predefined_expenses[$k]->label); ?>
                        </option>
                    <?php } ?>
                </select>
                <br />
                <?php echo $langs->trans("Date"); ?> : <?php echo $html->select_date($line->dated, 'es', 0, 0, 0,"addexpense"); ?>
                <br />
                <?php if ($conf->global->NDFP_MULTI_CURRENCIES) { ?>
                    <?php echo $langs->trans("Currency"); ?> : <?php echo $html->select_currency($line->cur_iso, 'currency' ); ?><br />
                    <?php echo $langs->trans("QtyRate"); ?> : <input type="text" size="8" id="rate" name="rate" value="<?php echo $line->rate; ?>" /><br />
                <?php }else{ ?>
                    <input type="hidden" name="rate" value="1" />
                    <input type="hidden" name="currency" value="<?php echo $ndfp->cur_iso; ?>" />
                <?php } ?>
                <?php echo $langs->trans("Comment"); ?> : <input type="text" size="60" id="comment" name="comment" value="<?php echo $line->comment; ?>" /><br />
                <?php echo $langs->trans("TaxRating"); ?> : <?php echo $ndfpHtml->select_cat($line->fk_cat, 'fk_cat'); ?><br />
                <?php echo $langs->trans("PreviousExp"); ?> : <input type="text" size="60" id="previous_exp" name="previous_exp" value="<?php echo $line->previous_exp; ?>" />
            </td>
            <?php }else{ ?>
            <td>    
                <?php echo $langs->trans($line->label);  ?> <em><?php echo ($line->comment ? ' - '. $line->comment : ''); ?></em><br />
                <em><?php echo ($line->fk_cat && $line->code == 'EX_KME' ? $cats[$line->fk_cat] : ''); ?></em>
            </td>
            <td width="90" align="right" nowrap="nowrap">
                <?php echo dol_print_date($line->dated, '%d/%m/%Y'); ?>
            </td>      
        <?php } ?>

        <td width="70" align="right" nowrap="nowrap">
            <?php if ($action == 'editline' && $lineid == $line->rowid){ ?>
                <input type="text" size="10" id="ref_ext" name="ref_ext" value="<?php echo $line->ref_ext; ?>" />
            <?php }else{
                echo ($line->ref_ext ? $line->ref_ext : '');
                }
            ?>
        </td>       
        <td align="right" nowrap="nowrap">
            <?php if ($action == 'editline' && $lineid == $line->rowid){ ?>
                <input type="text" size="8" id="qty" name="qty" value="<?php echo $line->qty; ?>" />
            <?php }else{ echo $line->qty; } ?>
        </td>

    
        <td align="right" nowrap="nowrap">
            <?php echo yn($line->milestone); ?>
        </td>

        <td align="right" nowrap="nowrap">
            <?php if ($action == 'editline' && $lineid == $line->rowid && !$line->milestone){
                 echo $html->load_tva('fk_tva', $line->fk_tva, $mysoc, $mysoc);
                }else{ echo $line->milestone ? '' : price($line->tva_tx).'%'; } ?>
        </td>
        
        <td align="right" nowrap="nowrap">
            <?php if ($action == 'editline' && $lineid == $line->rowid && !$line->milestone){ ?>
                <input type="text" size="8" id="total_ht" name="total_ht" value="<?php echo price($line->total_ht_cur); ?>" />
            <?php }else{ echo price($line->total_ht); } ?>
        </td>


        <td align="right" nowrap="nowrap">
            <?php if ($action == 'editline' && $lineid == $line->rowid && !$line->milestone){ ?>
                <input type="text" size="8" id="total_ttc" name="total_ttc" value="<?php echo price($line->total_ttc_cur); ?>" />
            <?php }else{ echo price($line->total_ttc); } ?>
        </td>
        
        <?php if ($action == 'editline' && $lineid == $line->rowid){ ?>
        <td align="right">
            <input type="submit" class="button" name="save" value="<?php echo $langs->trans("Save"); ?>" />&nbsp;<input type="submit" class="button" name="cancel" value="<?php echo $langs->trans("Cancel"); ?>" />
        </td>

        <?php }else{ ?>
            <?php if ($ndfp->statut == 0) { ?>
            <td align="right">
                <a href="<?php echo $_SERVER["PHP_SELF"].'?id='.$ndfp->id.'&amp;action=editline&amp;lineid='.$line->rowid; ?>">
                    <?php echo img_edit(); ?>
                </a>
                <a href="<?php echo $_SERVER["PHP_SELF"].'?id='.$ndfp->id.'&amp;action=ask_deleteline&amp;lineid='.$line->rowid; ?>">
                    <?php echo img_delete(); ?>
                </a>
            </td>
        <?php }else{ ?>
        <td>&nbsp;</td>
        <?php } } ?>
    </tr> 
    </form>
<?php } ?>


<?php } ?>


<?php if ($can_add_expenses){ ?>

<tr class="liste_titre nodrag nodrop">
    <td colspan="2"><?php echo $langs->trans("AddNewExpense"); ?></td>
    <td align="right"><?php echo $langs->trans('ExternalReference'); ?></td>
    <td align="right"><?php echo $langs->trans('Qty'); ?></td>
    <td align="right"><?php echo $langs->trans('MilestoneExpense'); ?><?php echo $html->textwithpicto('', $langs->trans('MilestoneTooltilp'), 1, 'help', '', 0, 3); ?></td>
    <td align="right"><?php echo $langs->trans('TVA'); ?></td>
    <td align="right"><?php echo $langs->trans('Total_HT'); ?> (<?php echo $langs->trans('Currency'.$ndfp->cur_iso); ?>)</td>
    <td align="right"><?php echo $langs->trans('Total_TTC'); ?> (<?php echo $langs->trans('Currency'.$ndfp->cur_iso); ?>)</td>
    <td>&nbsp;</td>
</tr>

<form name="addexpense" id="addexpense" action="<?php echo $_SERVER["PHP_SELF"].'?id='.$ndfp->id; ?>" method="POST">
<input type="hidden" name="token" value="<?php echo newToken(); ?>" />
<input type="hidden" name="action" value="addline" />
<input type="hidden" name="id" value="<?php echo $ndfp->id; ?>" />
<input type="hidden" name="fk_user" id="fk_user" value="<?php echo $ndfp->fk_user; ?>" />

<tr class="pair">
    <td colspan="2">
     <?php echo $langs->trans("ExpenseType"); ?> : <select class="flat" id="fk_exp" name="fk_exp">
            <?php for ($i=0; $i<count($predefined_expenses); $i++){ ?>
                <option value="<?php echo $predefined_expenses[$i]->rowid; ?>" <?php echo ($i==0 ? 'selected="selected"' : ''); ?> >
                    <?php echo $langs->trans($predefined_expenses[$i]->label); ?>
                </option>
            <?php } ?>
        </select>
    <br />
    <?php echo $langs->trans("Date"); ?> : <?php echo $html->select_date($ndfp->dates, 'es', 0, 0, 0,"addexpense"); ?>
    <br />
    <?php if ($conf->global->NDFP_MULTI_CURRENCIES) { ?>
        <?php echo $langs->trans("Currency"); ?> : <?php echo $html->select_currency($ndfp->cur_iso, 'currency' ); ?><br />
        <?php echo $langs->trans("QtyRate"); ?> : <input type="text" size="8" id="rate" name="rate" value="<?php echo $line->rate; ?>" /><br />
    <?php }else{ ?>
        <input type="hidden" name="rate" value="1" />
        <input type="hidden" name="currency" value="<?php echo $ndfp->cur_iso; ?>" />
    <?php } ?>
    <?php echo $langs->trans("Comment"); ?> : <input type="text" size="60" id="comment" name="comment" value="" /><br />
    <?php echo $langs->trans("TaxRating"); ?> : <?php echo $ndfpHtml->select_cat($ndfp->fk_cat, 'fk_cat'); ?><br />
    <?php echo $langs->trans("PreviousExp"); ?> : <input type="text" size="60" id="previous_exp" name="previous_exp" value="" />
    </td>
    
    <td align="right"><input type="text" size="10" id="ref_ext" name="ref_ext" value="" /></td>
    <td align="right"><input type="text" size="8" id="qty" name="qty" value="1" /></td>
    <td align="right"><?php echo $html->selectyesno('milestone', 0, 1); ?></td>
    <td align="right"><?php echo $html->load_tva('fk_tva', $default_line_tva, $mysoc, $mysoc); ?></td>
    <td align="right"><input type="text" size="8" id="total_ht" name="total_ht" value="" /></td>
    <td align="right"><input type="text" size="8" id="total_ttc" name="total_ttc" value="" /></td>
    
    <td align="center" align="right">
        <input type="submit" class="button" value="<?php echo $langs->trans("Add"); ?>" name="addline" />
    </td>
</tr>

</form>
<?php } ?>



<?php if ($numTaxLines > 0){ ?>
    <tr class="liste_titre nodrag nodrop">
        <td colspan="5"><?php echo $langs->trans('TaxLines'); ?></td>
        <td align="right" width="70"><?php echo $langs->trans('PriceBaseType'); ?></td>
        <td align="right" width="70">&nbsp;</td>
        <td align="right" width="70"><?php echo $langs->trans('Amount'); ?> (<?php echo $langs->trans('Currency'.$ndfp->cur_iso); ?>)</td>
        <td width="50">&nbsp;</td>
    </tr>


<?php

for($i = 0; $i < $numTaxLines; $i++){
    $line = $ndfp->tax_lines[$i];

    
    if ($action == 'edittaxline' && $lineid == $line->rowid){ ?>

    <form action="<?php echo $_SERVER["PHP_SELF"].'?id='.$ndfp->id; ?>" method="POST">
    <input type="hidden" name="token" value="<?php  echo newToken(); ?>" />
    <input type="hidden" name="action" value="updatetaxline" />
    <input type="hidden" name="id" value="<?php echo $ndfp->id; ?>" />
    <input type="hidden" name="lineid" value="<?php echo $line->rowid; ?>" />

    <?php } ?>

    <tr class="<?php echo ($i%2==0 ? 'impair' : 'pair'); ?>">
        <td colspan="5">    
            <?php echo $langs->trans($line->label);  ?> <em><?php echo ($line->comment ? ' - '. $line->comment : ''); ?></em>
        </td>

    
        <td align="right" nowrap="nowrap">
            <?php echo $line->price_base_type; ?>
        </td>

        <td align="right">&nbsp;</td>

        <td align="right" nowrap="nowrap">
            <?php if ($action == 'edittaxline' && $lineid == $line->rowid){ ?>
                <input type="text" size="8" id="amount_tax" name="amount_tax" value="<?php echo price($line->amount_tax); ?>" />
            <?php }else{ echo price($line->amount_tax); } ?>
        </td>
        
        <?php if ($action == 'edittaxline' && $lineid == $line->rowid){ ?>
        <td align="right">
            <input type="submit" class="button" name="save" value="<?php echo $langs->trans("Save"); ?>" />&nbsp;<input type="submit" class="button" name="cancel" value="<?php echo $langs->trans("Cancel"); ?>" />
        </td>

        <?php }else{ ?>
            <?php if ($ndfp->statut == 0) { ?>
            <td align="right">
                <a href="<?php echo $_SERVER["PHP_SELF"].'?id='.$ndfp->id.'&amp;action=edittaxline&amp;lineid='.$line->rowid; ?>">
                    <?php echo img_edit(); ?>
                </a>
                <a href="<?php echo $_SERVER["PHP_SELF"].'?id='.$ndfp->id.'&amp;action=ask_deletetaxline&amp;lineid='.$line->rowid; ?>">
                    <?php echo img_delete(); ?>
                </a>
            </td>
        <?php }else{ ?>
        <td>&nbsp;</td>
        <?php } } ?>
    </tr> 
    </form>
<?php } ?>


<?php } ?>


<?php if ($can_add_tax){ ?>

<tr class="liste_titre nodrag nodrop">
    <td colspan="5"><?php echo $langs->trans("AddNewTax"); ?></td>
    <td align="right"><?php echo $langs->trans('PriceBaseType'); ?></td>
    <td>&nbsp;</td>
    <td align="right"><?php echo $langs->trans('Amount'); ?> (<?php echo $langs->trans('Currency'.$ndfp->cur_iso); ?>)</td>
    <td>&nbsp;</td>
</tr>

<form action="<?php echo $_SERVER["PHP_SELF"].'?id='.$ndfp->id; ?>" method="POST">
<input type="hidden" name="token" value="<?php echo newToken(); ?>" />
<input type="hidden" name="action" value="addtaxline" />
<input type="hidden" name="id" value="<?php echo $ndfp->id; ?>" />

<tr class="pair">
    <td  colspan="5">
        <select class="flat" id="fk_ndfp_det" name="fk_ndfp_det">
            <?php for ($i=0; $i<count($ndfp->milestone_lines); $i++){ 
                $amount = $ndfp->milestone_lines[$i]->total_ht;
                $amount = price2num($amount, 'MT');
            ?>
                <option value="<?php echo $ndfp->milestone_lines[$i]->rowid; ?>" <?php echo ($i==0 ? 'selected="selected"' : ''); ?> >
                    <?php echo $langs->trans($ndfp->milestone_lines[$i]->label).' ('.$amount.' '.$ndfp->cur_iso.')'; ?> <?php echo ($ndfp->milestone_lines[$i]->comment ? ' - '. $ndfp->milestone_lines[$i]->comment : ''); ?>
                </option>
            <?php } ?>
        </select>
    </td>
    <td align="right">
        <?php echo $html->selectPriceBaseType('HT','price_base_type'); ?>
    </td>

    <td align="right" nowrap="nowrap">
        &nbsp;
    </td>

    <td align="right">
        <input type="text" size="8" id="amount_tax" name="amount_tax" value="0,00" />
    </td>


    <td align="center" align="right">
        <input type="submit" class="button" value="<?php echo $langs->trans("Add"); ?>" name="addtaxline" />
    </td>
</tr>

</form>
<?php } ?>

<?php if ($numTVALines > 0){ ?>
    <tr class="liste_titre nodrag nodrop">
        <td colspan="5"><?php echo $langs->trans('TvaLines'); ?></td>
        <td align="right" width="70"><?php echo $langs->trans('TVA'); ?></td>
        <td align="right" width="70">&nbsp;</td>
        <td align="right" width="70"><?php echo $langs->trans('Total_TVA'); ?> (<?php echo $langs->trans('Currency'.$ndfp->cur_iso); ?>)</td>
        <td width="50">&nbsp;</td>
    </tr>


<?php

for($i = 0; $i < $numTVALines; $i++){
    $line = $ndfp->tva_lines[$i];

    
    if ($action == 'edittvaline' && $lineid == $line->rowid){ ?>

    <form name="addtvaline" id="addtvaline" action="<?php echo $_SERVER["PHP_SELF"].'?id='.$ndfp->id; ?>" method="POST">
    <input type="hidden" name="token" value="<?php  echo newToken(); ?>" />
    <input type="hidden" name="action" value="updatetvaline" />
    <input type="hidden" name="id" value="<?php echo $ndfp->id; ?>" />
    <input type="hidden" name="lineid" value="<?php echo $line->rowid; ?>" />
    <input type="hidden" id="fk_ndfp_tax_det" name="fk_ndfp_tax_det" value="<?php echo $line->fk_ndfp_tax_det; ?>" />
    
    <?php } ?>

    <tr class="<?php echo ($i%2==0 ? 'impair' : 'pair'); ?>">
        <td colspan="5">    
            <?php echo $langs->trans($line->label);  ?> <em><?php echo ($line->comment ? ' - '. $line->comment : ''); ?></em>
        </td>

    
        <td align="right" nowrap="nowrap">
            <?php if ($action == 'edittvaline' && $lineid == $line->rowid){
                 echo $html->load_tva('fk_tva_det', $line->tva_tx, $mysoc, $mysoc);
                }else{ echo $line->tva_tx.'%'; } ?>
        </td>


        <td align="right" nowrap="nowrap">
            &nbsp;
        </td>

        <td align="right" nowrap="nowrap">
            <?php if ($action == 'edittvaline' && $lineid == $line->rowid){ ?>
                <input type="text" size="8" id="total_tva" name="total_tva" value="<?php echo price($line->total_tva); ?>" />         
            <?php }else{ ?>
                <?php echo price($line->total_tva); ?>
            <?php } ?>
        </td>
        
        <?php if ($action == 'edittvaline' && $lineid == $line->rowid){ ?>
        <td align="right">
            <input type="submit" class="button" name="save" value="<?php echo $langs->trans("Save"); ?>" />&nbsp;<input type="submit" class="button" name="cancel" value="<?php echo $langs->trans("Cancel"); ?>" />
        </td>

        <?php }else{ ?>
            <?php if ($ndfp->statut == 0) { ?>
            <td align="right">
                <a href="<?php echo $_SERVER["PHP_SELF"].'?id='.$ndfp->id.'&amp;action=edittvaline&amp;lineid='.$line->rowid; ?>">
                    <?php echo img_edit(); ?>
                </a>
                <a href="<?php echo $_SERVER["PHP_SELF"].'?id='.$ndfp->id.'&amp;action=ask_deletetvaline&amp;lineid='.$line->rowid; ?>">
                    <?php echo img_delete(); ?>
                </a>
            </td>
        <?php }else{ ?>
        <td>&nbsp;</td>
        <?php } } ?>
    </tr> 
    </form>
<?php } ?>


<?php } ?>


<?php if ($can_add_tva){ ?>

<tr class="liste_titre nodrag nodrop">
    <td colspan="5"><?php echo $langs->trans("AddNewTVA"); ?></td>
    <td align="right"><?php echo $langs->trans('TVA'); ?></td>
    <td align="right">&nbsp;</td>
    <td align="right"><?php echo $langs->trans('Total_TVA'); ?> (<?php echo $langs->trans('Currency'.$ndfp->cur_iso); ?>)</td>
    <td>&nbsp;</td>
</tr>

<form name="addtvaline" id="addtvaline" action="<?php echo $_SERVER["PHP_SELF"].'?id='.$ndfp->id; ?>" method="POST">
<input type="hidden" name="token" value="<?php echo newToken(); ?>" />
<input type="hidden" name="action" value="addtvaline" />
<input type="hidden" name="id" value="<?php echo $ndfp->id; ?>" />

<tr class="pair">
    <td  colspan="5">
        <select class="flat" id="fk_ndfp_tax_det" name="fk_ndfp_tax_det">
            <?php for ($i=0; $i<count($ndfp->tax_lines); $i++){ 
                $amount = $ndfp->tax_lines[$i]->price_base_type == 'HT' ? $ndfp->tax_lines[$i]->total_ht : $ndfp->tax_lines[$i]->total_ttc;
                $amount = price2num($amount, 'MT');
            ?>
                <option value="<?php echo $ndfp->tax_lines[$i]->rowid; ?>" <?php echo ($i==0 ? 'selected="selected"' : ''); ?> >
                    <?php echo $langs->trans($ndfp->tax_lines[$i]->label).' ('.$amount.' '.$ndfp->cur_iso.' '.$ndfp->tax_lines[$i]->price_base_type.')'; ?>  <?php echo ($ndfp->tax_lines[$i]->comment ? ' - '. $ndfp->tax_lines[$i]->comment : ''); ?>
                </option>
            <?php } ?>
        </select>
    </td>
    <td align="right"><?php echo $html->load_tva('fk_tva_det', $selected_vat, $mysoc, $mysoc); ?></td>
    <td align="right" nowrap="nowrap">
        &nbsp;
    </td>
    <td align="right">
        <input type="text" size="8" id="total_tva" name="total_tva" value="0,00" />
    </td>
    
    <td align="center" align="right">
        <input type="submit" class="button" value="<?php echo $langs->trans("Add"); ?>" name="addtvaline" />
    </td>
</tr>

</form>
<?php } ?>
</table>
</div>

<?php if ($action != 'presend'){ ?>
    <div class="tabsAction">
    <?php foreach ($buttons as $button)
    {
        echo $button;
    }
    ?>
    </div>

    <br />
    <table width="100%">
        <tr>
            <td width="50%" valign="top">
                <?php $formfile->show_documents('ndfp', $filename, $filedir, $urlsource, $genallowed, $delallowed, $ndfp->modelpdf,1,0,0,28,0,'','','',$user->lang,''); ?>
                <br />
				<?php $linktoelem = $html->showLinkToObjectBlock($ndfp, null, array('ndfp')); ?>
				<?php $somethingshown = $html->showLinkedObjectBlock($ndfp, $linktoelem); ?>
            </td>

            <td valign="top" width="50%">
                <?php $formactions->showactions($ndfp, 'ndfp', $ndfp->fk_soc); ?>
            </td>
            
            
        </tr>
    </table>

<?php }else{ ?>

    <?php $formmail->show_form(); ?>

<?php } ?>

<br />

<?php dol_fiche_end(); ?>

<?php llxFooter(''); ?>

