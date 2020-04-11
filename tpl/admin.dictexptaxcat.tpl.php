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


/**	    \file       htdocs/ndfp/tpl/admin.dictexptaxcat.tpl.php
 *		\ingroup    ndfp
 *		\brief      Admin setup view
 */

llxHeader("", $langs->trans("NdfpSetup"));

echo ($message ? dol_htmloutput_mesg($message, '', ($error ? 'error' : 'ok'), 0) : '');



print_fiche_titre($langs->trans("DictionnarySetup"), $linkback, 'setup');

dol_fiche_head($head, 'dict', $langs->trans("Ndfp"));

echo $langs->trans('ExpTaxCatDesc');

echo $formconfirm;

?>
<br />
<br />
    

    <table class="noborder" width="100%">
        <tr class="liste_titre">
            <td><?php echo $langs->trans('Label'); ?></td>
            <td><?php echo $langs->trans('ParentCategory'); ?></td>
            <td colspan="3">&nbsp;</td>
        </tr>

        <tr class="impair">
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
			<input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>" />		
            <td><input type="text"  class="flat" size="32" name="label" value="" /></td>
            <td><?php echo $ndfpHtml->select_parent_cats('', 'fk_parent'); ?></td>
            <td colspan="3" align="right">
                <input type="submit" class="button" name="actionadd" value="<?php echo $langs->trans("Add"); ?>" />
            </td>
		</form>
        </tr>

        <tr>
            <td colspan="5">&nbsp;</td>
        </tr>

        <?php if ($num > $listlimit){ ?>
        <tr class="none">
            <td align="right" colspan="5">
                    <?php print_fleche_navigation($page, $_SERVER["PHP_SELF"],'',($num > $listlimit),$langs->trans("Page").' '.($page+1)); ?>
            </td>
        </tr>
        <?php } ?>

        <?php
            if (count($cats)){
            $i = 0;
        ?>
        <tr class="liste_titre">
            <?php print_liste_field_titre($langs->trans('Label'), $_SERVER['PHP_SELF'], "c.label",($page ? 'page='.$page.'&':''),"","",$sortfield,$sortorder); ?>
            <?php print_liste_field_titre($langs->trans('ParentCategory'), $_SERVER['PHP_SELF'], "c.fk_parent",($page ? 'page='.$page.'&':''),"","",$sortfield,$sortorder); ?>
            <?php print_liste_field_titre($langs->trans("Status"),$_SERVER['PHP_SELF'],"c.active",($page?'page='.$page.'&':''),"",'align="center"',$sortfield,$sortorder); ?>
            <td colspan="2"  class="liste_titre">&nbsp;</td>
        </tr>
        <?php foreach ($cats as $id => $cat){ ?>
            <tr class="<?php echo ($i%2==0 ? 'impair' : 'pair'); ?>">
            <td>
                <?php if ($action == 'modify' && $id == $rowid){ ?>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>" />
                        <input type="hidden" name="rowid" value="<?php echo $id; ?>" />
                        <input type="hidden" name="active" value="<?php echo $cat->active; ?>" />
                        <input type="hidden" name="page" value="<?php echo $page; ?>" />

                        <input type="text" name="label" class="flat" size="32" value="<?php echo $langs->trans($cat->label); ?>" />
                <?php }else{
                        echo $langs->trans($cat->label);
                } ?>
            </td>
            <td>
                <?php if ($action == 'modify' && $id == $rowid){
                         echo $ndfpHtml->select_parent_cats($cat->fk_parent, 'fk_parent');
                     }else{
                        echo (isset($cats[$cats[$id]->fk_parent]) ? $langs->trans($cats[$cats[$id]->fk_parent]->label) : '');
                } ?>
            </td>
                <?php if ($action == 'modify' && $id == $rowid){ ?>
                    <td colspan="3" align="right">
                        <input type="submit" class="button" name="actionmodify" value="<?php echo $langs->trans("Modify"); ?>" />
                        <input type="submit" class="button" name="actioncancel" value="<?php echo $langs->trans("Cancel"); ?>" />
                    </td>
					</form>
                     <?php }else{ ?>
                    <td align="center" nowrap="nowrap">
                        <?php echo $cat->activate; ?>
                    </td>
                    <td align="center">
                        <?php echo $cat->modify; ?>
                    </td>
                    <td align="center">
                        <?php echo $cat->delete; ?>
                    </td>
                <?php } ?>

            </tr>
        <?php
            $i++;
            }
        ?>

        <?php } ?>
    </table>
<br />

<?php llxFooter(''); ?>
