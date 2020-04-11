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
 *      \file       htdocs/ndfp/tpl/ugrade.upgrade.tpl.php
 *		\ingroup    ndfp
 *		\brief      Upgrade ndfp module upgrade view
 */

llxHeader("", $langs->trans("NdfpSetup"));

echo ($message ? dol_htmloutput_mesg($message, '', ($error ? 'error' : 'ok'), 0) : '');


print_fiche_titre($langs->trans("NdfpSetup"), $linkback, 'setup');

dol_fiche_head($head, 'updgrade', $langs->trans("Ndfp"));
?>

<?php if (!$error){ ?>
	<h3><?php echo $langs->trans("DatabaseMigration"); ?></h3>
	<table cellspacing="0" cellpadding="1" border="0" width="100%">
	<?php if ($db->connected == 1){ ?>
			<tr>
				<td nowrap="nowrap"><?php echo $langs->trans("ServerConnection"); ?> : <?php echo $conf->db->host; ?></td>
				<td align="right"><?php echo $langs->trans("OK"); ?></td>
			</tr>
	<?php }else{ ?>
			<tr>
				<td><?php echo $langs->trans("ErrorFailedToConnectToDatabase", $conf->db->name); ?></td>
				<td align="right"><?php echo $langs->transnoentities("Error"); ?></td>
			</tr>
	<?php } ?>
			   
	<?php if($db->database_selected == 1){ ?>
			<tr>
				<td nowrap="nowrap"><?php echo $langs->trans("DatabaseConnection")." : ".$conf->db->name; ?></td>
				<td align="right"><?php echo $langs->trans("OK"); ?></td>
			</tr>
	<?php }else{ ?>
			<tr>
				<td><?php echo $langs->trans("ErrorFailedToConnectToDatabase", $conf->db->name); ?></td>
				<td align="right"><?php echo $langs->transnoentities("Error"); ?></td>
			</tr>
	<?php } ?>

	 <?php if($db->database_selected == 1 && $db->connected == 1){ ?>                     
			<tr>
				<td><?php echo $langs->trans("ServerVersion") ;?></td>
				<td align="right"><?php echo $db->getVersion(); ?></td>
			</tr> 
	  <?php } ?>
			
		<tr>
			<td colspan="2"><?php echo $langs->trans("PleaseBePatient"); ?></td>
		</tr> 
		
		<?php foreach($fileList as $file){ ?>
			<tr>
				<td nowrap><?php echo $langs->trans("ChoosedMigrateScript"); ?></td>
				<td align="right"><?php echo $file; ?></td>
			</tr>

			<?php $ok = run_sql($directory.$file, 0, '', 1); }  ?>                                         
<?php } ?>
                  


<?php llxFooter(''); ?>
