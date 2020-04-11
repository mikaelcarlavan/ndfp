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
 *	\file       htdocs/ndfp/class/html.form.ndfp.class.php
 *  \ingroup    ndfp
 *	\brief      File of class with all html predefined components
 */


/**
 *	\class      NdfpForm
 *	\brief      Class to manage generation of HTML components
 *	\remarks	Only common components must be here.
 */
class NdfpForm
{
    var $db;
    var $error;




    /**
     * Constructor
     * @param      $DB      Database handler
     */
    function __construct($DB)
    {
        $this->db = $DB;
    }

    /**
     *    Return combo list of payments parent category
     *    @param     selected         Id preselected category
     *    @param     htmlname         Name of html select object
     *    @param     htmloption       Options html on select object
     *    @return    string           HTML string with select
     */
    function select_parent_payment_cats($selected='',$htmlname='fk_cat', $htmloption='')
    {
        global $conf, $langs;

         // Get categories
        $parentCats = array();
        $catsGroupByParent = array();
        $formconfirm = '';
        
        
        $sql  = " SELECT c.rowid, c.label, c.fk_parent";
        $sql .= " FROM ".MAIN_DB_PREFIX."c_ndfp_exp_pay_cat c";
        $sql .= " WHERE c.active = 1 AND c.fk_parent = 0";
        $sql .= " ORDER BY c.rowid ASC";
        
        dol_syslog("NdfpForm::select_parent_payment_cats sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        

               
        if ($resql)
        {
            $num = $this->db->num_rows($resql);
            $i = 0;
            
            if ($num)
            {
                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($resql);
                    
					$parentCats[$obj->rowid] = $obj;

                         
                    $i++;
                }
            }
        }
        

        //Build select
        $select = '<select class="flat" name = "'.$htmlname.'" '.$htmloption.'>';
        $select .= '<option value="0"></option>'; 
        foreach ($parentCats as $catid => $parent)
        {
            $select .= '<option value="'.$catid.'" '.($catid == $selected ? 'selected="selected"' : '').'>'.$langs->trans($parent->label).'</option>';
        }
        
        $select .= '</select>';

        return $select;
    }
    
    /**
     *    Return combo list of vehicules parent category
     *    @param     selected         Id preselected category
     *    @param     htmlname         Name of html select object
     *    @param     htmloption       Options html on select object
     *    @return    string           HTML string with select
     */
    function select_parent_cats($selected='',$htmlname='fk_cat', $htmloption='')
    {
        global $conf, $langs;

         // Get categories
        $parentCats = array();
        $catsGroupByParent = array();
        $formconfirm = '';
        
        
        $sql  = " SELECT c.rowid, c.label, c.fk_parent";
        $sql .= " FROM ".MAIN_DB_PREFIX."c_ndfp_exp_tax_cat c";
        $sql .= " WHERE c.active = 1 AND c.fk_parent = 0";
        $sql .= " ORDER BY c.rowid ASC";
        
        dol_syslog("NdfpForm::select_cat sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        

               
        if ($resql)
        {
            $num = $this->db->num_rows($resql);
            $i = 0;
            
            if ($num)
            {
                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($resql);
                    
					$parentCats[$obj->rowid] = $obj;

                         
                    $i++;
                }
            }
        }
        

        //Build select
        $select = '<select class="flat" name = "'.$htmlname.'" '.$htmloption.'>';
        $select .= '<option value="0"></option>'; 
        foreach ($parentCats as $catid => $parent)
        {
            $select .= '<option value="'.$catid.'" '.($catid == $selected ? 'selected="selected"' : '').'>'.$langs->trans($parent->label).'</option>';
        }
        
        $select .= '</select>';

        return $select;
    }

    /**
     *    Return combo list of vehicules category
     *    @param     selected         Id preselected category
     *    @param     htmlname         Name of html select object
     *    @param     htmloption       Options html on select object
     *    @return    string           HTML string with select
     */
    function select_payment_modes($selected='',$htmlname='fk_mode_reglement', $htmloption='')
    {
        global $conf, $langs;

         // Get categories
        $parentCats = array();
        $catsGroupByParent = array();
        $formconfirm = '';
        
        
        $sql  = " SELECT c.rowid, c.label, c.fk_parent";
        $sql .= " FROM ".MAIN_DB_PREFIX."c_ndfp_exp_pay_cat c";
        $sql .= " WHERE c.active = 1";
        $sql .= " ORDER BY c.rowid ASC";
        
        dol_syslog("NdfpForm::select_payment_modes sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        

               
        if ($resql)
        {
            $num = $this->db->num_rows($resql);
            $i = 0;
            
            if ($num)
            {
                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($resql);
                    

                    if ($obj->fk_parent == 0)
                    {
                        $parentCats[$obj->rowid] = $obj;
                    }
                    else
                    {
                        $catsGroupByParent[$obj->fk_parent][] = $obj;
                    } 
                         
                    $i++;
                }
            }
        }
        

        //Build select
        $select = '<select class="flat" name = "'.$htmlname.'" '.$htmloption.'>';
        $select .= '<option value="0"></option>'; 
        foreach ($parentCats as $catid => $parent)
        {
            if (isset($catsGroupByParent[$parent->rowid]))
            {
                $childCats = $catsGroupByParent[$parent->rowid];
                
                $select .= '<optgroup label="'.$langs->trans($parent->label).'">';
                foreach ($childCats as $childCat)
                {
                   $select .= '<option value="'.$childCat->rowid.'" '.($childCat->rowid == $selected ? 'selected="selected"' : '').'>'.$langs->trans($childCat->label).'</option>';
                }
                
                $select .= '</optgroup>';                
            }
            else
            {
                $select .= '<option value="'.$catid.'" '.($catid == $selected ? 'selected="selected"' : '').'>'.$langs->trans($parent->label).'</option>';
            }
        }
        
        $select .= '</select>';

        return $select;
    }
    
    /**
     *    Return combo list of vehicules category
     *    @param     selected         Id preselected category
     *    @param     htmlname         Name of html select object
     *    @param     htmloption       Options html on select object
     *    @return    string           HTML string with select
     */
    function select_cat($selected='',$htmlname='fk_cat', $htmloption='')
    {
        global $conf, $langs;

         // Get categories
        $parentCats = array();
        $catsGroupByParent = array();
        $formconfirm = '';
        
        
        $sql  = " SELECT c.rowid, c.label, c.fk_parent";
        $sql .= " FROM ".MAIN_DB_PREFIX."c_ndfp_exp_tax_cat c";
        $sql .= " WHERE c.active = 1";
        $sql .= " ORDER BY c.rowid ASC";
        
        dol_syslog("NdfpForm::select_cat sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        

               
        if ($resql)
        {
            $num = $this->db->num_rows($resql);
            $i = 0;
            
            if ($num)
            {
                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($resql);
                    

                    if ($obj->fk_parent == 0)
                    {
                        $parentCats[$obj->rowid] = $obj;
                    }
                    else
                    {
                        $catsGroupByParent[$obj->fk_parent][] = $obj;
                    } 
                         
                    $i++;
                }
            }
        }
        

        //Build select
        $select = '<select class="flat" id = "'.$htmlname.'" name = "'.$htmlname.'" '.$htmloption.'>';
        $select .= '<option value="0"></option>'; 
        foreach ($parentCats as $catid => $parent)
        {
            if (isset($catsGroupByParent[$parent->rowid]))
            {
                $childCats = $catsGroupByParent[$parent->rowid];
                
                $select .= '<optgroup label="'.$langs->trans($parent->label).'">';
                foreach ($childCats as $childCat)
                {
                   $select .= '<option value="'.$childCat->rowid.'" '.($childCat->rowid == $selected ? 'selected="selected"' : '').'>'.$langs->trans($childCat->label).'</option>';
                }
                
                $select .= '</optgroup>';                
            }
            else
            {
                $select .= '<option value="'.$catid.'" '.($catid == $selected ? 'selected="selected"' : '').'>'.$langs->trans($parent->label).'</option>';
            }
        }
        
        $select .= '</select>';

        return $select;
    }


    /**
     *    Return combo list of expenses
     *    @param     selected         Id preselected expense
     *    @param     htmlname         Name of html select object
     *    @param     htmloption       Options html on select object
     *    @return    string           HTML string with select
     */
    function select_expense($selected='',$htmlname = 'fk_exp', $htmloption='')
    {
        global $conf, $langs;

         // Get expenses
        
        $expenses = array();
        
        $sql  = " SELECT e.rowid, e.code, e.fk_tva, e.label, t.taux";
        $sql .= " FROM ".MAIN_DB_PREFIX."c_ndfp_exp e";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_tva t";
        $sql .= " ON e.fk_tva = t.rowid WHERE e.active = 1";
        $sql .= " ORDER BY e.rowid DESC";
        
        dol_syslog("NdfpForm::select_expense sql=".$sql, LOG_DEBUG);
    
        $result = $this->db->query($sql);
        if ($result)
        {
            $num = $this->db->num_rows($result);
            if ($num)
            {
                for ($i = 0; $i < $num; $i++)
                {
                    $obj = $this->db->fetch_object($result);
                    
                    $expenses[$i]  = $obj;
                }
            }  
            
        }
        

        //Build select
        $select = '<select class="flat" id="'.$htmlname.'" name="'.$htmlname.'" '.$htmloption.'>';
        foreach ($expenses as $expense)
        {
            $select .= '<option value="'.$expense->rowid.'" '.($expense->rowid == $selected ? 'selected="selected"' : '').'>'.$langs->trans($expense->label).'</option>';           
        }
        $select .= '</select>';

        return $select;
    }

    /**
     *    Return combo list of status
     *    @param     selected         Id preselected status
     *    @param     htmlname         Name of html select object
     *    @param     htmloption       Options html on select object
     *    @return    string           HTML string with select
     */
    function select_ndfp_status($selected='', $htmlname = 'statut', $htmloption='')
    {
        global $conf, $langs;

         // Get status
        
        $status = array(0, 1, 2, 3, 4);

        $ndfp = new Ndfp($this->db);

        //Build select
        $select = '<select class="flat" id="'.$htmlname.'" name="'.$htmlname.'" '.$htmloption.'>';
        $select.= '<option value=""></option>';           
        foreach ($status as $stat)
        {
            $select .= '<option value="'.$stat.'" '.(is_numeric($selected) && $stat == $selected ? 'selected="selected"' : '').'>'.$ndfp->lib_statut($stat, 1).'</option>';           
        }
        $select .= '</select>';

        return $select;
    }

    /**
     *  \brief Return all categories name indexed by id
     *  @return     array      labels
     */
    function get_cats_name()
    {
        global $langs;
        
        $cats = array();
        
        $sql  = " SELECT c.rowid, p.label AS plabel, c.label AS clabel";
        $sql .= " FROM ".MAIN_DB_PREFIX."c_ndfp_exp_tax_cat AS c";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_ndfp_exp_tax_cat AS p ON p.rowid = c.fk_parent";
        
        dol_syslog("NdfpForm::get_cats_name sql=".$sql, LOG_DEBUG);
        $result = $this->db->query($sql);        
        
        if ($result)
        {
            $num = $this->db->num_rows($result);
            $i = 0;
            
            if ($num)
            {
                while($i < $num)
                {
                    $obj = $this->db->fetch_object($result);
                    
                    $cats[$obj->rowid] = $langs->trans($obj->plabel) .' - '. $langs->trans($obj->clabel);
                    $i++;                    
                }
            }
        }
        
        return $cats;
        
    }

    /**
     *  \brief Return all notes attached to soc
     *    @param     socid         Id of society
     *    @param     alreadyinvoiced        Display notes already invoiced
     *  @return     array      notes
     */
    function get_notes_for_obj($fk_soc = 0, $fk_project = 0, $alreadyinvoiced = false)
    {
        global $langs, $conf, $user, $object;
        
        $ndfps = array();

		$sql = "SELECT n.rowid as id, n.ref, n.description, n.tms, n.cur_iso, n.total_ht, n.total_ttc, n.fk_user, n.statut, n.fk_soc, n.dates, n.datee,";
        $sql.= " u.rowid as uid, u.lastname, u.firstname, s.nom AS soc_name, s.rowid AS soc_id, u.login, n.total_tva";
        $sql.= " FROM ".MAIN_DB_PREFIX."ndfp as n";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON n.fk_user = u.rowid";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON s.rowid = n.fk_soc";
        $sql.= " WHERE n.statut > 0 AND n.entity = ".$conf->entity;
        $sql.= $fk_soc > 0 ? " AND n.fk_soc = ".$fk_soc : "";
        $sql.= $fk_project > 0 ? " AND n.fk_project = ".$fk_project : "";
        if (!$alreadyinvoiced)
        {
        	$sql.= " AND n.rowid NOT IN (SELECT fk_source FROM ".MAIN_DB_PREFIX."element_element WHERE sourcetype = 'ndfp' AND targettype = 'facture')";      
        }
        
        dol_syslog("NdfpForm::get_notes_for_soc sql=".$sql, LOG_DEBUG);
        $result = $this->db->query($sql);        
        
        if ($result)
        {
            $num = $this->db->num_rows($result);
            $i = 0;
            
            if ($num)
            {
                while($i < $num)
                {
                    $userstatic = new User($db);
                    $ndfpstatic = new Ndfp($db);
                    $societestatic = new Societe($db);

                    $obj = $this->db->fetch_object($resql);

                    $ndfps[$i] = $obj;


                    $userstatic->lastname  = $obj->lastname;
                    $userstatic->firstname = $obj->firstname;
                    $userstatic->id = $obj->uid;

                    $societestatic->id = $obj->soc_id;
                    $societestatic->name = $obj->soc_name;

                    $ndfpstatic->id = $obj->rowid;
                    $ndfpstatic->ref = $obj->ref;
                    $ndfpstatic->statut = $obj->statut;

                    $ndfps[$i]->mdate = $this->db->jdate($obj->tms);
                    $ndfps[$i]->username = $userstatic->getNomUrl(1);
                    $ndfps[$i]->total_ttc = $obj->total_ttc;
                    $ndfps[$i]->url = $ndfpstatic->getNomUrl(1);

                    $ndfps[$i]->society = ($obj->fk_soc > 0 ? $societestatic->getNomUrl(1) : '');


                    $ndfps[$i]->statut = $ndfpstatic->get_lib_statut(5, $obj->already_paid);

                    $ndfps[$i]->dates = $this->db->jdate($obj->dates);
                    $ndfps[$i]->datee = $this->db->jdate($obj->datee);

                    $ndfps[$i]->filename = dol_sanitizeFileName($obj->ref);
                    $ndfps[$i]->filedir = $conf->ndfp->dir_output . '/' . dol_sanitizeFileName($obj->ref);
                    $ndfps[$i]->urlsource = $_SERVER['PHP_SELF'].'?id='.$obj->rowid;
                    
                    $i++;                    
                }
            }
        }
        
        return $ndfps;
        
    }

    /**
     *  \brief Return all notes attached to object
     *    @param     socid         Id ofo society
     *  @return     array      notes
     */
    function select_notes_for_obj(&$object, $selected='', $htmlname = 'fk_ndfp', $htmloption='')
    {
        global $langs, $conf, $user;
        
        $select = '<select class="flat" id="'.$htmlname.'" name="'.$htmlname.'" '.$htmloption.'>';
        
        $fk_soc = $object->socid;
        $fk_project = $object->fk_project;
        
        
        $ndfps = $this->get_notes_for_obj($fk_soc, $fk_project);

		foreach ($ndfps as $ndfp)
		{
			$fk_user = $ndfp->fk_user;
			
			$description = $ndfp->ref;					
			$description.= ($ndfp->description ? ' '.$ndfp->description : '');
			
			$userstatic = new User($this->db);
			if ($fk_user > 0)
			{
				$userstatic->fetch($fk_user);
				
				if ($userstatic->id > 0)
				{
					$description.= ' '.$userstatic->getFullName($langs);
				}
			}			
			$description.= ' '.$ndfp->cur_iso;
			
            $select .= '<option value="'.$ndfp->id.'" '.($ndfp->id == $selected ? 'selected="selected"' : '').'>'.$description.'</option>';           
		
		}
        $select .= '</select>';

        return $select;        
    }
    
    /**
     *  \brief Return all notes attached to soc
     *    @param     socid         Id ofo society
     *  @return     array      notes
     */
    function select_notes_for_soc($fk_soc = 0, $selected='', $htmlname = 'fk_ndfp', $htmloption='')
    {
        global $langs, $conf, $user;
        
        $select = '<select class="flat" id="'.$htmlname.'" name="'.$htmlname.'" '.$htmloption.'>';
        $ndfps = $this->get_notes_for_obj($fk_soc);

		foreach ($ndfps as $ndfp)
		{
			$fk_user = $ndfp->fk_user;
			
			$description = $ndfp->ref;					
			$description.= ($ndfp->description ? ' '.$ndfp->description : '');
			
			$userstatic = new User($this->db);
			if ($fk_user > 0)
			{
				$userstatic->fetch($fk_user);
				
				if ($userstatic->id > 0)
				{
					$description.= ' '.$userstatic->getFullName($langs);
				}
			}			
			$description.= ' '.$ndfp->cur_iso;
			
            $select .= '<option value="'.$ndfp->id.'" '.($ndfp->id == $selected ? 'selected="selected"' : '').'>'.$description.'</option>';           
		
		}
        $select .= '</select>';

        return $select;        
    }
       	
}