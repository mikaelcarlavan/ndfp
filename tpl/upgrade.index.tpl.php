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
 *      \file       htdocs/ndfp/tpl/ugrade.default.tpl.php
 *		\ingroup    ndfp
 *		\brief      Upgrade ndfp module default view
 */

llxHeader("", $langs->trans("NdfpSetup"));

echo (!empty($message) ? dol_htmloutput_mesg($message, '', ($error ? 'error' : 'ok'), 0) : '');


print_fiche_titre($langs->trans("NdfpSetup"), $linkback, 'setup');

dol_fiche_head($head, 'updgrade', $langs->trans("Ndfp"));

?>

<br />


<center>
	<img src="./../img/logo_ndfp.png" alt="Ndfp logo" /><br />
	<?php echo $langs->trans("Module70300Name"); ?> <?php echo $moduleVersion; ?><br /><br />
</center>            
<?php echo $langs->trans("NdfpInstallMode"); ?><br />

<table width="100%" class="listofchoices">

<?php foreach ($migrationScript as $migArray){             
			$versionFrom = $migArray['from'];
			$versionTo = $migArray['to'];

			$upgradeUrl = "upgrade.php?action=upgrade&amp;from=".$versionFrom."&amp;to=".$versionTo;
?>
	<tr class="listofchoices">
		<td class="listofchoices" nowrap="nowrap" align="center">
			<b><?php echo $langs->trans("Upgrade").'<br />'.$versionFrom.' -> '.$versionTo; ?></b>
		</td>
		<td class="listofchoices">
		   <?php echo $langs->trans("UpgradeDesc"); ?>
		</td>
		<td class="listofchoices" align="center">
			<a href="<?php echo $upgradeUrl; ?>"><?php echo $langs->trans("Start"); ?></a>
		</td>
	</tr>                               
<?php } ?>       
</table>
  


<?php llxFooter(''); ?>
    