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

/**	    \file       htdocs/ndfp/tpl/admin.config.tpl.php
 *		\ingroup    ndfp
 *		\brief      Admin setup view
 */
 
llxHeader("", $langs->trans("NdfpSetup"));

echo ($message ? dol_htmloutput_mesg($message, '', ($error ? 'error' : 'ok'), 0) : '');


print_fiche_titre($langs->trans("NdfpSetup"), $linkback, 'setup');

dol_fiche_head($head, 'config', $langs->trans("Ndfp"));

?>
<br />
<?php print_titre($langs->trans("NdfpNumberingModule")); ?>

<table class="noborder" width="100%">
    <tr class="liste_titre">
        <td><?php echo $langs->trans("Name"); ?></td>
        <td><?php echo $langs->trans("Description"); ?></td>
        <td nowrap><?php echo $langs->trans("Example"); ?></td>
        <td align="center" width="60"><?php echo $langs->trans("Status"); ?></td>
        <td align="center" width="16"><?php echo $langs->trans("Infos"); ?></td>
    </tr>

    <?php for($i=0; $i<count($modules); $i++){ ?>
    <tr class="<?php echo ($i%2 == 0 ? 'impair' : 'pair'); ?>">
        <td width="100">
            <?php echo $modules[$i]->name; ?>
        </td>
        
        <td>
            <?php echo $modules[$i]->info; ?>
        </td>

        <td nowrap="nowrap">
            <?php echo $modules[$i]->example; ?>
        </td>

        <td align="center">
            <?php echo $modules[$i]->state; ?>
        </td>

        <td align="center">
            <?php        
            echo $modules[$i]->tooltip;
            echo $modules[$i]->error;   
            ?>
        </td>
    </tr>        
    <?php } ?>
</table>
<br />

<?php print_titre($langs->trans("NdfpPDFModules")); ?>

<table class="noborder" width="100%">
    <tr class="liste_titre">
        <td><?php echo $langs->trans("Name"); ?></td>
        <td><?php echo $langs->trans("Description"); ?></td>
        <td align="center" width="60"><?php echo $langs->trans("Status"); ?></td>
        <td align="center" width="60"><?php echo $langs->trans("Default"); ?></td>
        <td align="center" width="32" colspan="2"><?php echo $langs->trans("Infos"); ?></td>
    </tr>

    <?php for($i=0; $i<count($modules2); $i++){ ?>
    <tr class="<?php echo ($i%2 == 0 ? 'impair' : 'pair'); ?>">
	    <td width="100">
	       <?php echo $modules2[$i]->name; ?>
	    </td>
        
        <td>
	       <?php echo $modules2[$i]->desc; ?>                     
	    </td>
        
        <td align="center">
            <?php echo $modules2[$i]->active; ?>
        </td>
	                  
        <td align="center">
            <?php echo $modules2[$i]->default; ?>
        </td>

        <td align="center">
	        <?php echo $modules2[$i]->info; ?>
	    </td>
	                          
	    <td align="center">
            <?php echo $modules2[$i]->preview; ?>
	   </td>

    </tr>        
    <?php } ?>
</table>

<br />
<?php print_titre($langs->trans("PathToDocuments")); ?>

<table class="noborder" width="100%">
    <tr class="liste_titre">
        <td><?php echo $langs->trans("Name"); ?></td>
        <td><?php echo $langs->trans("Value"); ?></td>
    </tr>
    <tr class="impair">
        <td width="140"><?php echo $langs->trans("PathDirectory"); ?></td>
        <td><?php echo $conf->ndfp->dir_output; ?></td>
    </tr>
</table>

<br />

<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="POST">
<input type="hidden" name="token" value="<?php echo newToken(); ?>">
<input type="hidden" name="action" value="update">


<div class="titre"><?php echo $langs->trans("NdfpOptions"); ?></div>
<table class="noborder" width="100%">
	<tr class="liste_titre">
        <td width="30%"><?php echo $langs->trans("Name"); ?></td>
        <td><?php echo $langs->trans("Value"); ?></td>
    </tr>
    <tr class="impair">
        <td><?php echo $langs->trans("AutoClassifyNotes"); ?></td>
        <td><?php echo $linkAutoClassifyNotes; ?></td>
    </tr>
    <tr class="pair">
        <td><?php echo $langs->trans("ValidationGroup"); ?></td>
        <td><?php echo $html->select_dolgroups($conf->global->NDFP_VALIDATION_GID, 'validation_gid', 1); ?></td>
    </tr> 
    <tr class="impair">
        <td><?php echo $langs->trans("MultiCurrencyNotes"); ?></td>
        <td><?php echo $linkActivateMultiCurrency; ?></td>
    </tr>  
    <tr class="impair">
        <td><?php echo $langs->trans("DisplayDatesOnBill"); ?></td>
        <td><?php echo $linkDisplayDatesOnBill; ?></td>
    </tr>   
</table>

<center>
<input type="submit" name="save" class="button" value="<?php echo $langs->trans("Save"); ?>">
</center>

</form>
<br />
<div class="titre"><?php $langs->trans("NdfpAutoEvents"); ?></div>

<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="POST">
<input type="hidden" name="token" value="<?php echo newToken(); ?>">
<input type="hidden" name="action" value="saveautoevents">


<table class="noborder" width="100%">
	<tr class="liste_titre">
		<td colspan="2"><?php echo $langs->trans("ActionsEvents"); ?></td>
		<td>
			<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?action=selectall"><?php echo $langs->trans("All"); ?></a>
			/
			<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?action=selectnone"><?php echo $langs->trans("None"); ?></a>
		</td>
	</tr>
	<?php
	$var=true;
	foreach ($triggers as $trigger)
	{
		$var=!$var;
		$key='NDFP_AGENDA_ACTIONAUTO_'.$trigger['code'];
		$value=isset($conf->global->$key) ? $conf->global->$key : '';
		?>
		<tr <?php echo $bc[$var]; ?>>
			<td><?php echo $trigger['code']; ?></td>
			<td><?php echo $trigger['label']; ?></td>
			<td align="right" width="40">
				<input <?php echo $bc[$var]; ?> type="checkbox" name="<?php echo $key; ?>" value="1"<?php echo ((($action=='selectall'||$value) && $action!="selectnone")?' checked="checked"':''); ?>>
			</td>
		</tr>
		<?php
	}
	?>
</table>

<br />
<center>
<input type="submit" name="save" class="button" value="<?php echo $langs->trans("Save"); ?>">
</center>


</form>

<?php llxFooter(''); ?>
