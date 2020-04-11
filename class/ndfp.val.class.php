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
 *      \file       htdocs/ndfp/class/ndfp.class.php
 *      \ingroup    ndfp
 *      \brief      File of class to manage trips and working credit notes
 */

require_once(DOL_DOCUMENT_ROOT ."/core/class/commonobject.class.php");


require_once(DOL_DOCUMENT_ROOT ."/core/lib/functions.lib.php");
require_once(DOL_DOCUMENT_ROOT ."/core/lib/functions2.lib.php");
require_once(DOL_DOCUMENT_ROOT.'/core/lib/price.lib.php');
require_once(DOL_DOCUMENT_ROOT."/core/lib/files.lib.php");

dol_include_once('/ndfp/class/ndfp.class.php');


/**
 *      \class      Ndfp
 *      \brief      Class to manage trips and working credit notes
 */
class NdfpVal extends CommonObject
{
	var $db;
	var $error;
	var $element = 'ndfp_val';
	var $table_element = 'ndfp_val';
	var $table_element_line = '';
	var $fk_element = 'fk_ndfp';
	var $ismultientitymanaged = 0;	// 0=No test on entity, 1=Test with field entity, 2=Test with link by societe

	var $id;

    var $tms = 0;
	var $fk_user = 0;
    var $fk_ndfp = 0;
    var $note = null;
    var $hash = null;

   /**
	*  \brief  Constructeur de la classe
	*  @param  DB          handler acces base de donnees
	*/
	function __construct($db)
	{
		global $conf;
		
		$this->db = $db;
	}


	/**
	 * Create object in database
	 *
	 * @param 	$user	User that creates
	 * @return 	int		<0 if KO, >0 if OK
	 */
	function create($user)
	{
		global $conf, $langs;

        $this->fk_user = $this->fk_user > 0 ? $this->fk_user : 0;
        $this->fk_ndfp = $this->fk_ndfp > 0 ? $this->fk_ndfp : 0;
        $this->note = $this->note ? trim($this->note) : '';
        $this->hash = $this->hash ? trim($this->hash) : '';

        $this->tms = dol_now();

        $this->db->begin();

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."ndfp_val (";
        $sql.= "`fk_user`";
        $sql.= ", `fk_ndfp`";
        $sql.= ", `note`";
        $sql.= ", `hash`";
        $sql.= ") ";
        $sql.= " VALUES (";
		$sql.= " ".$this->fk_user." ";
		$sql.= ", ".$this->fk_ndfp." ";
		$sql.= ", ".($this->note ? "'".$this->db->escape($this->note)."'" : "''");
		$sql.= ", ".($this->hash ? "'".$this->db->escape($this->hash)."'" : "''");
		$sql.= ")";

		dol_syslog("NdfpVal::create sql=".$sql, LOG_DEBUG);

		$result = $this->db->query($sql);

		if ($result)
		{
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."ndfp_val");

			$this->db->commit();
			return $this->id;
		}
		else
		{
			$this->error = $this->db->error()." sql=".$sql;
			$this->db->rollback();
			return -1;
		}

	}


	/**
	 * Update object in database
	 *
	 * @param 	$user	User that updates
	 * @return 	int		<0 if KO, >0 if OK
	 */
	function update($user)
	{
		global $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpValidationIdIsMissing');
			return -1;
        }

        $this->fk_user = $this->fk_user > 0 ? $this->fk_user : 0;
        $this->fk_ndfp = $this->fk_ndfp > 0 ? $this->fk_ndfp : 0;
        $this->note = $this->note ? trim($this->note) : '';
        $this->hash = $this->hash ? trim($this->hash) : '';

		$this->db->begin();
        $this->tms = dol_now();

		$sql = "UPDATE ".MAIN_DB_PREFIX."ndfp_val ";
		$sql .= "SET `fk_ndfp` = ".$this->fk_ndfp;
        $sql .= ", `fk_user` = ".$this->fk_user;
		$sql .= ", `note` = ".($this->note ? "'".$this->db->escape($this->note)."'" : "''");
		$sql .= ", `hash` = ".($this->hash ? "'".$this->db->escape($this->hash)."'" : "''");
        $sql .= " WHERE rowid = ".$this->id;

		dol_syslog("NdfpVal::update sql=".$sql, LOG_DEBUG);

		$result = $this->db->query($sql);
		if ($result)
		{
			$this->db->commit();
            $this->error = $langs->trans('NdfpValidationHasBeenUpdated');

			return 1;
		}
		else
		{
			$this->db->rollback();
			$this->error = $this->db->error()." sql=".$sql;
			return -1;
		}
	}

	/**
	 * Fetch object from database
	 *
	 * @param 	id	    Id of the note
     * @param 	ref  	Reference of the note
	 * @return 	int		<0 if KO, >0 if OK
	 */
	function fetch($id, $hash = '')
	{
	    global $conf, $langs;

        if (!$id && !$hash)
        {
			$this->error = $langs->trans('NdfpValidationIdIsMissing');
			return -1;
        }

		$sql = "SELECT n.`rowid`, n.`fk_ndfp`, n.`fk_user`, n.`note`, n.`hash`"; 
		$sql.= " FROM ".MAIN_DB_PREFIX."ndfp_val AS n";
        $sql.= " WHERE 1 ";
        if ($id)   $sql.= " AND n.rowid = ".$id;
        if ($hash)  $sql.= " AND n.hash = '".$this->db->escape($hash)."'";


		dol_syslog("NdfpVal::fetch sql=".$sql, LOG_DEBUG);

		$result = $this->db->query($sql);
		if ($result > 0)
		{

			$obj = $this->db->fetch_object($result);

			$this->id                = $obj->rowid;
            $this->fk_ndfp           = $obj->fk_ndfp;
            $this->fk_user          = $obj->fk_user;
			$this->note       = trim($obj->note);
			$this->hash      = trim($obj->hash);

			
            if ($this->id > 0)
            {
                return $this->id;
            }
            else
            {
                return 0;
            }
		}
		else
		{
			$this->error = $this->db->error()." sql=".$sql;
			return -1;
		}
	}




	/**
	 * Delete object from database
	 *
	 * @return 	int		<0 if KO, >0 if OK
	 */
	function delete($user)
	{

	    global $conf, $langs;



        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpValidationIdIsMissing');
			return -1;
        }
				
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."ndfp_val WHERE rowid = ".$this->id;

		dol_syslog("NdfpVal::delete sql=".$sql, LOG_DEBUG);

		$result = $this->db->query($sql);

		if ($result)
		{
            $this->error = $langs->trans('NdfpValidationHasBeenDeleted');
    		return 1;
		}
		else
		{
			$this->error = $this->db->error()." sql=".$sql;
			return -1;
		}
	}

}