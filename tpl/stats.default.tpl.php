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

/**	    \file       htdocs/ndfp/tpl/stats.default.tpl.php
 *		\ingroup    ndfp
 *		\brief      Credit notes statistics default view
 */

llxHeader('');

echo ($message ? dol_htmloutput_mesg($message, '', ($error ? 'error' : 'ok'), 0) : '');

dol_fiche_head($head, $current_head, $langs->trans('StatsNdfp'));


?>

<table class="notopnoleftnopadd" width="100%">
<tr>
<td align="center" valign="top">

<form name="stats" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
<input type="hidden" name="source" id="source" value="<?php echo $source; ?>" />
<table class="border" width="100%">
	<tr>
		<td class="liste_titre" colspan="2"><?php echo $langs->trans("Filter"); ?></td>
	</tr>
	<tr>
		<td><?php echo $langs->trans("Society"); ?></td>
		<td><?php echo $html->select_company($socid, 'socid', '', 1); ?></td>
	</tr>
	<?php if ($source != 'users') { ?>
	<tr>
		<td><?php echo $langs->trans("User"); ?></td>
		<td><?php echo $html->select_users($userid, 'userid', 1); ?></td>
	</tr>
	<?php } ?>
	<tr>
		<td><?php echo $langs->trans("DateStart"); ?></td>
		<td><?php echo $html->select_date($startDate, 'd', 0, 0, 0,"statsdate"); ?></td>
	</tr>
	<tr>
		<td><?php echo $langs->trans("DateEnd"); ?></td>
		<td><?php echo $html->select_date($endDate, 'f', 0, 0, 0,"statsdate"); ?></td>
	</tr>
	<tr>
		<td align="center" colspan="2">
			<input type="submit" name="submit" class="button" value="<?php echo $langs->trans("Refresh"); ?>">
		</td>
	</tr>
</table>
</form>
<br /><br />


<?php if (count($dataAll) > 0){ ?>
<table class="border" width="100%">
<tr>
	<td align="center"><?php echo $searchStartYear; ?></td>
	<td align="center"><?php echo $langs->trans("NumberOfNotes"); ?></td>
	<td align="center"><?php echo $langs->trans("AmountTotal"); ?></td>
	<td align="center"><?php echo $langs->trans("AmountAverage"); ?></td>
</tr>

<?php foreach ($dataAll as $dataBySource) { ?>	
	<tr>
		<td align="center">
			<?php echo $dataBySource[0]; ?>
		</td>
		<td align="center">
			<?php echo $dataBySource[1][0]; ?>
		</td>
		<td align="right">
			<?php echo price(price2num($dataBySource[1][1], 'MT'), 1, '', 1, - 1, - 1, $conf->currency); ?>
		</td>
		<td align="right">
			<?php echo price(price2num($dataBySource[1][2], 'MT'), 1, '', 1, - 1, - 1, $conf->currency); ?>
		</td>
	</tr>
<?php } ?>
<tr class="liste_total">
	<td align="center">
		<?php echo $langs->trans('Total'); ?>
	</td>
	<td align="center">
		<?php echo $totalAll[0][0]; ?>
	</td>
	<td align="right">
		<?php echo price(price2num($totalAll[0][1], 'MT'), 1, '', 1, - 1, - 1, $conf->currency); ?>
	</td>
	<td align="right">
		&nbsp;
	</td>
</tr>
</table>
<br />
<table class="border" width="100%">
<tr>
	<td align="center"><?php echo $previousStartYear; ?></td>
	<td align="center"><?php echo $langs->trans("NumberOfNotes"); ?></td>
	<td align="center"><?php echo $langs->trans("AmountTotal"); ?></td>
	<td align="center"><?php echo $langs->trans("AmountAverage"); ?></td>
</tr>

<?php foreach ($dataAll as $dataBySource) { ?>	
	<tr>
		<td align="center">
			<?php echo $dataBySource[0]; ?>
		</td>
		<td align="center">
			<?php echo $dataBySource[2][0]; ?>
		</td>
		<td align="right">
			<?php echo price(price2num($dataBySource[2][1], 'MT'), 1, '', 1, - 1, - 1, $conf->currency); ?>
		</td>
		<td align="right">
			<?php echo price(price2num($dataBySource[2][2], 'MT'), 1, '', 1, - 1, - 1, $conf->currency); ?>
		</td>
	</tr>
<?php } ?>
<tr class="liste_total">
	<td align="center">
		<?php echo $langs->trans('Total'); ?>
	</td>
	<td align="center">
		<?php echo $totalAll[1][0]; ?>
	</td>
	<td align="right">
		<?php echo price(price2num($totalAll[1][1], 'MT'), 1, '', 1, - 1, - 1, $conf->currency); ?>
	</td>
	<td align="right">
		&nbsp;
	</td>
</tr>

</table>
<?php } ?>

</td>
<td align="center" valign="top">
	<table class="border" width="100%">
		<tr valign="top">
			<td align="center">
				<?php echo $graphNumberOutput; ?><br />
				<?php echo $graphAmountOutput; ?><br />
				<?php echo $graphAverageOutput; ?><br />
			</td>
		</tr>
	</table>
</td>
</tr>
</table>


<br />

<?php dol_fiche_end(); ?>

<?php llxFooter(''); ?>

