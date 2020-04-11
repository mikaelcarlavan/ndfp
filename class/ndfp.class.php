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
require_once(DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php');
require_once(DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php');
require_once(DOL_DOCUMENT_ROOT ."/fourn/class/fournisseur.facture.class.php");
require_once(DOL_DOCUMENT_ROOT ."/compta/facture/class/facture.class.php");

require_once(DOL_DOCUMENT_ROOT ."/core/lib/functions.lib.php");
require_once(DOL_DOCUMENT_ROOT ."/core/lib/functions2.lib.php");
require_once(DOL_DOCUMENT_ROOT.'/core/lib/price.lib.php');
require_once(DOL_DOCUMENT_ROOT."/core/lib/files.lib.php");
dol_include_once('/ndfp/core/modules/ndfp/modules_ndfp.php');
dol_include_once('/ndfp/class/ndfp.val.class.php');

/**
 *      \class      Ndfp
 *      \brief      Class to manage trips and working credit notes
 */
class Ndfp extends CommonObject
{
	var $db;
	var $error;
	var $element = 'ndfp';
	var $table_element = 'ndfp';
	var $table_element_line = 'ndfp_det';
	var $fk_element = 'fk_ndfp';
	var $ismultientitymanaged = 0;	// 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
    var $restrictiononfksoc = 0;

	var $id;

    var $cur_iso;
    var $ref;
    var $entity;
	var $datec;
    var $tms;
    var $dates;
    var $datee;

    var $datef;

	var $date_valid = 0;
	var $fk_user_author;
	var $fk_user_valid = 0;
	var $fk_user = 0;
    //! 0=draft,
    //! 1=validated (need to be paid),
    //! 2=classified paid completely,
    //! 3=classified abandoned and no payment done
    //! 4=waiting validation...
    var $statut;

    var $fk_soc = 0;
    var $fk_project = 0;

    var $fk_cat = 0;

	var $fk_mode_reglement = 0;
	var $billed = 0;
	
    var $total_tva = 0;
    var $total_ht = 0;
    var $total_ttc = 0;

	var $mode_reglement;
	var $autoclass = 0;
	
    var $description;
	var $comment_user;
	var $comment_admin;

	var $model_pdf;


    var $specimen = 0;
    
    var $lines = array();
    var $tva_lines = array();
    var $tax_lines = array();
    var $milestone_lines = array();
    
    var $line;

   /**
	*  \brief  Constructeur de la classe
	*  @param  DB          handler acces base de donnees
	*/
	function __construct($db)
	{
		global $conf;
		
		$this->cur_iso = $conf->currency;
		$this->db = $db;
	}

   /**
	*  \brief  Execute action
	*  @param  action      action to execute
    *  @param  args        arguments array
    *  @return int         <0 if KO, >0 if OK
	*/
    function call($action, $args)
    {
        global $langs;

        if (empty($action))
        {
            return 0;
        }

        if (method_exists($this, $action))
        {
            $result = call_user_func_array(array($this, $action), $args);

            return $result;
        }
        else
        {
            //$this->error = $langs->trans('ActionDoesNotExist');
            return 0;
        }
    }

	/**
	 * \brief Check parameters prior inserting or updating the DB
	 *
	 * @return 	int		<0 if KO, >0 if OK
	 */
     function check_parameters()
     {
		global $conf, $langs;

        // Check parameters
        if (empty($this->ref)){
			$this->error = $langs->trans('ErrorBadParameterRef');
			return -1;
        }

        if (empty($this->datee) || empty($this->dates)){
			$this->error = $langs->trans('ErrorBadParameterDates');
			return -1;
        }

        if ($this->datee < $this->dates){
			$this->error = $langs->trans('ErrorBadParameterDateDoNotMatch');
			return -1;
        }

        if ($this->fk_user <= 0){
			$this->error = $langs->trans('ErrorBadParameterUser');
			return -1;
        }

        if ($this->fk_cat < 0){
			$this->error = $langs->trans('ErrorBadParameterCat');
			return -1;
        }


        if ($this->fk_project < 0){
			$this->error = $langs->trans('ErrorBadParameterProject');
			return -1;
        }

        if (empty($this->cur_iso)){
			$this->error = $langs->trans('ErrorBadParameterCurrency');
			return -1;
        }

        return 1;
     }

	/**
	 * \brief Check user permissions
	 *
	 * @return 	int		<0 if KO, >0 if OK
	 */
    function check_user_rights($user, $action = 'create')
    {
        global $langs;


        if ($this->id > 0)
        {
            if ($this->fk_user == $user->id)
            {
                if ($user->rights->ndfp->myactions->$action)
                {
                    return 1;
                }
            }
            else
            {
                if (!$user->rights->ndfp->allactions->$action)
                {
                    $this->error = $langs->trans('NotEnoughPermissions');
                    return -1;
                }
            }
        }
        else
        {
            if ($user->rights->ndfp->myactions->$action)
            {
                return 1;
            }
        }


        if ($user->rights->ndfp->allactions->$action)
        {
            return 1;
        }


        $this->error = $langs->trans('NotEnoughPermissions');
        return -1;
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

        $result = $this->check_parameters();

        if ($result < 0){
            return -1;
        }

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }

        $this->comment_admin = ($user->admin ? $this->comment_admin : '');
        $this->statut = 0;//Enforce draft
        $this->datec = dol_now();
        $this->fk_user_author = $user->id;
        $this->datef = dol_now();
        $this->tms = dol_now();

        $this->db->begin();

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."ndfp (";
        $sql.= "`ref`";
        $sql.= ", `cur_iso`";
        $sql.= ", `entity`";
        $sql.= ", `datec`";
        $sql.= ", `dates`";
        $sql.= ", `datee`";
        $sql.= ", `datef`";
        $sql.= ", `fk_user_author`";
		$sql.= ", `fk_user`";
        $sql.= ", `statut`";
        $sql.= ", `fk_soc`";
        $sql.= ", `fk_project`";
        $sql.= ", `fk_cat`";
        $sql.= ", `fk_mode_reglement`";
        $sql.= ", `billed`";
        $sql.= ", `total_tva`";
        $sql.= ", `total_ht`";
        $sql.= ", `total_ttc`";
        $sql.= ", `description`";
        $sql.= ", `comment_user`";
        $sql.= ", `comment_admin`";
        $sql.= ", `model_pdf`";
        $sql.= ", `tms`";
        $sql.= ") ";
        $sql.= " VALUES (";
		$sql.= " '".$this->ref."' ";
		$sql.= ", '".$this->cur_iso."' ";
		$sql.= ", '".$conf->entity."' ";
        $sql.= ", '".$this->db->idate($this->datec)."'";
        $sql.= ", '".$this->db->idate($this->dates)."'";
        $sql.= ", '".$this->db->idate($this->datee)."'";
        $sql.= ", '".$this->db->idate($this->datef)."'";
		$sql.= ", ".$this->fk_user_author;
		$sql.= ", ".$this->fk_user;
		$sql.= ", ".$this->statut;
		$sql.= ", ".$this->fk_soc;
        $sql.= ", ".$this->fk_project;
		$sql.= ", ".$this->fk_cat;
		$sql.= ", ".$this->fk_mode_reglement;
		$sql.= ", ".$this->billed;
		$sql.= ", ".$this->total_tva;
		$sql.= ", ".$this->total_ht;
		$sql.= ", ".$this->total_ttc;
		$sql.= ", ".($this->description ? "'".$this->db->escape($this->description)."'" : "''");
		$sql.= ", ".($this->comment_user ? "'".$this->db->escape($this->comment_user)."'" : "''");
		$sql.= ", ".($this->comment_admin ? "'".$this->db->escape($this->comment_admin)."'" : "''");
		$sql.= ", '".$this->db->escape($this->model_pdf)."'";
        $sql.= ", '".$this->db->idate($this->tms)."'";
		$sql.= ")";

		dol_syslog("Ndfp::create sql=".$sql, LOG_DEBUG);

		$result = $this->db->query($sql);

		if ($result)
		{
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."ndfp");
            $this->ref = '(PROV'.$this->id.')';

			$result = $this->update($user);
			if ($result > 0)
			{
				$this->db->commit();
				return $this->id;
			}
			else
			{
				$this->db->rollback();
				return -1;
			}
		}
		else
		{
			$this->error = $this->db->error()." sql=".$sql;
			$this->db->rollback();
			return -1;
		}

	}

    /**
     *		\brief Load an object from its id and create a new one in database
     *		@param      fromid     		Id of object to clone
     *      @param      user     		User who clones
     * 	 	@return		int				New id of clone
     */
    function create_from_clone($fromid, $user)
    {
        global $conf, $langs;

        // Load new object
        $object = new Ndfp($this->db);
        $object->fetch($fromid);

        $object->id = 0;

        // Clear fields
        $object->ref = 'PROV';
    	$object->fk_user_author = $user->id;



        $object->lines = array();

        // Create clone
        $result = $object->create($user);

        if ($result < 0)
        {
            return -1;
        }

        // Add lines
        foreach($this->lines AS $line)
        {
            // Insert line
            $this->line  = new NdfpLine($this->db);

            $this->line->fk_ndfp        = $object->id;

			$this->line->comment          = $line->comment;
			$this->line->ref_ext          = $line->ref_ext;
			
            $this->line->qty            = $line->qty;
            $this->line->rate           = $line->rate;
            $this->line->cur_iso        = $line->cur_iso ? $line->cur_iso : $object->cur_iso;
            $this->line->dated          = $line->dated;
            $this->line->datef          = $line->datef;

            $this->line->fk_user_author = $user->id;
            $this->line->fk_tva         = $line->fk_tva;
            $this->line->fk_exp         = $line->fk_exp;

            $this->line->total_ht       = $line->total_ht;
            $this->line->total_ttc      = $line->total_ttc;
			$this->line->price_base_type= $line->price_base_type;

            $result = $this->line->insert();

            if ($result < 0)
            {
                $this->error = $this->line->error;

                return -2;
            }
        }

        $result = $this->fetch($object->id);

        if ($result > 0)
        {
            $this->generate_pdf($user);

            // Triggers call
            include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
            $interface = new Interfaces($this->db);
            $result = $interface->run_triggers('NDFP_CLONE', $this, $user, $langs, $conf);

            if ($result < 0)
            {
                $this->error = $langs->trans('ErrorCallingTrigger');
                return -1;
            }

       	    $this->error = $langs->trans('ExpAdded');

            return $object->id;

        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;

            return -1;
        }
    }

	/**
	 * Add object in database
	 *
	 * @param 	$user	User that adds
	 * @return 	int		<0 if KO, >0 if OK
	 */
	function add($user)
	{
	    global $conf, $langs;

        $fk_user = GETPOST('fk_user');
        $fk_soc = GETPOST('fk_soc');
        $fk_cat = GETPOST('fk_cat');
        $fk_project = ($conf->projet->enabled ? GETPOST('fk_project') : 0);

		$fk_mode_reglement = GETPOST('fk_mode_reglement') ? GETPOST('fk_mode_reglement') : 0;
		$billed = GETPOST('billed') ? GETPOST('billed') : 0;
		
        $model = GETPOST('model');
        $currency = GETPOST('currency');
        $description = trim(GETPOST('description', 'chaine'));
        $note_public = trim(GETPOST('comment_user', 'chaine'));
        $note = trim(GETPOST('comment_admin', 'chaine'));

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }

        $start_date = dol_mktime(12, 0 , 0, $_POST['dmonth'], $_POST['dday'], $_POST['dyear']);
        $end_date = dol_mktime(12, 0 , 0, $_POST['fmonth'], $_POST['fday'], $_POST['fyear']);

        $this->ref            = '(PROV)';
        $this->cur_iso        = $currency;

        $this->entity         = $conf->entity;
        $this->dates          = $start_date;
        $this->datee          = $end_date;

        $this->fk_user        = $fk_user;
        $this->fk_soc         = $fk_soc;
        $this->fk_project     = $fk_project;

        $this->fk_cat        = $fk_cat;
		$this->billed        = $billed;
		$this->fk_mode_reglement        = $fk_mode_reglement;
		
        $this->total_tva      = 0;
        $this->total_ht      = 0;
        $this->total_ttc      = 0;
		
        $this->description      = $description;
        $this->comment_user	    = $note_public;
        $this->comment_admin    = $note;

        $this->model_pdf        = $model;
        $this->statut      = 0;
		
        $result = $this->check_parameters();


        if ($result < 0)
        {
            return -1;
        }


        $id = $this->create($user);

        if ($id > 0)
        {
            $this->fetch($id);
            $this->error = $langs->trans('NdfpHasBeenAdded');
            return 1;
        }
        else
        {
            //$this->error = $langs->trans('ErrorAddingNdfp');
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

		// Check parameters
        $result = $this->check_parameters();

        if ($result < 0)
        {
            return -1;
        }

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        $result = $this->check_user_rights($user, 'create');

        if ($result < 0)
        {
            return -1;
        }

		$this->db->begin();
        $this->tms = dol_now();

		$sql = "UPDATE ".MAIN_DB_PREFIX."ndfp ";
		$sql .= "SET `ref` = '".$this->ref."'";
		$sql .= ", `dates` = '".$this->db->idate($this->dates)."'";
		$sql .= ", `datee` = '".$this->db->idate($this->datee)."'";
		$sql .= ", `fk_user` = ".$this->fk_user;
        $sql .= ", `statut` = ".$this->statut;
        $sql .= ", `fk_soc` = ".$this->fk_soc;
        $sql .= ", `fk_project` = ".$this->fk_project;
		$sql .= ", `fk_cat` = ".$this->fk_cat;
		$sql .= ", `fk_mode_reglement` = ".$this->fk_mode_reglement;
		$sql .= ", `billed` = ".$this->billed;
		$sql .= ", `total_tva` = ".$this->total_tva;
        $sql .= ", `total_ht` = ".$this->total_ht;
        $sql .= ", `total_ttc` = ".$this->total_ttc;
		$sql .= ", `description` = ".($this->description ? "'".$this->db->escape($this->description)."'" : "''");
		$sql .= ", `comment_user` = ".($this->comment_user ? "'".$this->db->escape($this->comment_user)."'" : "''");
		$sql .= ", `comment_admin` = ".($this->comment_admin ? "'".$this->db->escape($this->comment_admin)."'" : "''");
        $sql .= ", `tms` = '".$this->db->idate($this->tms)."'";
        $sql .= " WHERE rowid = ".$this->id;

		dol_syslog("Ndfp::update sql=".$sql, LOG_DEBUG);

		$result = $this->db->query($sql);
		if ($result)
		{
			$this->db->commit();
            $this->error = $langs->trans('NdfpHasBeenUpdated');

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
	 * 	\brief Update timestamp of note
	 *	@return	 int  <0 si ko, >0 si ok
	 */
	function update_tms()
	{
		global $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }


        // Update timestamp of ndfp
        $tms = dol_now();

        $sql = " UPDATE ".MAIN_DB_PREFIX."ndfp SET tms = '".$this->db->idate($tms)."' WHERE rowid = ".$this->id;
        dol_syslog("Ndfp::update_tms sql=".$sql, LOG_DEBUG);

        $result = $this->db->query($sql);

        if ($result > 0)
        {
            $this->tms = $tms;
            return 1;
        }
        else
        {
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
	function fetch($id, $ref = '')
	{
	    global $conf, $langs;

        if (!$id && empty($ref)){
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

		$sql = "SELECT n.`rowid`, n.`cur_iso`, n.`ref`, n.`entity`, n.`datec`, n.`dates`, n.`datee`, n.`datef`, n.`date_valid`"; 
		$sql.= ", n.`fk_user_author`, n.`fk_user_valid`, n.`fk_user`, n.`fk_soc`, n.`fk_project`, n.`statut`";
        $sql.= ", n.`fk_cat`, n.`fk_mode_reglement`, n.`billed`, n.`total_tva`, n.`total_ht`, n.`total_ttc`";
        $sql.= ", n.`description`, n.`comment_user`, n.`comment_admin`, n.`model_pdf`, n.`tms`, c.`label` as payment_label, c.autoclass";
		$sql.= " FROM ".MAIN_DB_PREFIX."ndfp AS n";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."c_ndfp_exp_pay_cat AS c ON c.rowid = n.fk_mode_reglement";
        $sql.= " WHERE n.entity = ".$conf->entity;
        if ($id)   $sql.= " AND n.rowid = ".$id;
        if ($ref)  $sql.= " AND n.ref = '".$this->db->escape($ref)."'";


		dol_syslog("Ndfp::fetch sql=".$sql, LOG_DEBUG);

		$result = $this->db->query($sql);
		if ($result)
		{

			$obj = $this->db->fetch_object($result);

			$this->id                = $obj->rowid;
			$this->ref               = $obj->ref;
            $this->cur_iso           = $obj->cur_iso;
			$this->entity            = $obj->entity;
			$this->datec             = $this->db->jdate($obj->datec);
			$this->dates             = $this->db->jdate($obj->dates);
            $this->datee             = $this->db->jdate($obj->datee);
			$this->datef             = $this->db->jdate($obj->datef);
			$this->date_valid        = $this->db->jdate($obj->date_valid);
			$this->fk_user_author    = $obj->fk_user_author;
			$this->fk_user_valid     = $obj->fk_user_valid;
			$this->fk_user           = $obj->fk_user;

            $this->fk_soc            = $obj->fk_soc;
            $this->fk_project        = $obj->fk_project;

			$this->statut            = $obj->statut;
			$this->fk_cat            = $obj->fk_cat;
			
			$this->fk_mode_reglement            = $obj->fk_mode_reglement;
			$this->mode_reglement   = $obj->fk_mode_reglement > 0 ? $obj->payment_label : '';
			$this->autoclass   		= $obj->autoclass ? 1 : 0;
			$this->billed            = $obj->billed ? 1 : 0;

            $this->total_tva           = $obj->total_tva;
            $this->total_ht           = $obj->total_ht;
            $this->total_ttc           = $obj->total_ttc;

			$this->description       = trim($obj->description);
			$this->comment_user      = $obj->comment_user;
			$this->comment_admin     = $obj->comment_admin;

            $this->tms               = $this->db->jdate($obj->tms);
			
            if ($this->id)
            {
                $result = $this->fetch_lines();
                if ($this->fk_cat > 0)
                {
					$this->load_coefs($this->fk_cat);
				}
				
				$this->fetchObjectLinked($this->id, $this->element, '', 'invoice_supplier');
				
                if ($result > 0)
                {
                    return 1;
                }
                else
                {
                    return -1;
                }
            }
		}
		else
		{
			$this->error = $this->db->error()." sql=".$sql;
			return -1;
		}
	}

	/**
	 * Fetch object lines from database
	 *
	 * @return 	int		<0 if KO, >0 if OK
	 */
	function fetch_lines()
	{
	    global $conf, $langs;

        if (!$this->id){
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        $this->lines = array();
		$this->tva_lines = array();
		$this->tax_lines = array();
		$this->milestone_lines = array();
		
		$tx = array();

		$sql = "SELECT SUM(nt.fk_tva) AS total_tx, nt.fk_ndfp_det, nt.fk_ndfp";
		$sql.= " FROM ".MAIN_DB_PREFIX."ndfp_tva_det AS nt";
        $sql.= " WHERE nt.fk_ndfp = ".$this->id;
		$sql.= " GROUP BY nt.fk_ndfp_det";

		
		dol_syslog("Ndfp::fetch_lines sql=".$sql, LOG_DEBUG);
		
		$result = $this->db->query($sql);
		if ($result)
		{
            $num = $this->db->num_rows($result);
            $i = 0;

            if ($num)
            {
                while ($i < $num)
                {
        			$obj = $this->db->fetch_object($result);
        			$tx[$obj->fk_ndfp_det] = $obj->total_tx;					
                    $i++;
                }
            }
        }
 
 		$sql = "SELECT  nd.rowid, nd.comment, nd.fk_cat, nd.previous_exp, nd.ref_ext, nd.fk_ndfp, nd.datec, nd.dated, nd.datef, nd.fk_user_author, nd.fk_exp,";
        $sql.= " nd.fk_tva, nd.qty, nd.rate, nd.cur_iso, nd.total_ht_cur, nd.total_ttc_cur, nd.total_ht, nd.total_ttc, nd.total_tva,";
        $sql.= " nd.tms, nd.milestone, e.label, e.code, e.fk_product, p.fk_product_type";
		$sql.= " FROM ".MAIN_DB_PREFIX."ndfp_det AS nd";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."c_ndfp_exp AS e ON e.rowid = nd.fk_exp";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product AS p ON p.rowid = e.fk_product";
        $sql.= " WHERE nd.fk_ndfp = ".$this->id;
		$sql.= " ORDER BY nd.dated";

		// Include total of VAT rate under tva_tx
		dol_syslog("Ndfp::fetch_lines sql=".$sql, LOG_DEBUG);

		$result = $this->db->query($sql);
		$anyMilestone = false;
		if ($result)
		{
            $num = $this->db->num_rows($result);
            $i = 0;

            if ($num)
            {
                while ($i < $num)
                {
        			$obj = $this->db->fetch_object($result);
        			$this->lines[$i]           = $obj;

					$this->lines[$i]->cur_iso   = $obj->cur_iso ? $obj->cur_iso : $this->cur_iso;// Compatibility

                    $this->lines[$i]->dated	    = $obj->dated ? $this->db->jdate($obj->dated) : '';
                    $this->lines[$i]->datef		= $obj->datef ? $this->db->jdate($obj->datef) : '';
                    $this->lines[$i]->datec		= $this->db->jdate($obj->datec);

                    $this->lines[$i]->tms		= $this->db->jdate($obj->tms);

					$this->lines[$i]->milestone = $obj->milestone ? 1 : 0;
					$this->lines[$i]->comment	= trim($obj->comment);
					$this->lines[$i]->ref_ext	= trim($obj->ref_ext);
					//$this->lines[$i]->tva_tx 	= $obj->taux;
					$this->lines[$i]->tva_tx 	= $obj->fk_tva ? $obj->fk_tva : 0;

                    $this->lines[$i]->fk_cat    = $obj->fk_cat ? $obj->fk_cat : 0;
                    $this->lines[$i]->previous_exp    = $obj->previous_exp ? $obj->previous_exp : 0;

					
					
					if ($this->lines[$i]->milestone)
					{
						$anyMilestone = true;
						
						$this->milestone_lines[] = $this->lines[$i];
						$this->lines[$i]->tva_tx = $this->lines[$i]->total_ht > 0 ? price2num(100*($this->lines[$i]->total_ttc/$this->lines[$i]->total_ht - 1), 2) : 0; 	
					}
					
                    $i++;
                }
            }

			if ($anyMilestone)
			{
				$sql = "SELECT  td.rowid, td.fk_ndfp, td.fk_ndfp_det, td.fk_ndfp_tax_det, td.fk_tva, td.total_tva, td.tms, nd.fk_exp, nd.comment, nd.ref_ext, nd.dated, nd.datef, ";
				$sql.= " xt.total_ht, nd.fk_ndfp, e.label, e.fk_product, p.fk_product_type";
				$sql.= " FROM ".MAIN_DB_PREFIX."ndfp_tva_det AS td";
				$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."ndfp_tax_det AS xt ON xt.rowid = td.fk_ndfp_tax_det";
				$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."ndfp_det AS nd ON nd.rowid = td.fk_ndfp_det";
				$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."c_ndfp_exp AS e ON e.rowid = nd.fk_exp";
                $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product AS p ON p.rowid = e.fk_product";
				//$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."c_tva AS t ON t.rowid = td.fk_tva";
				$sql.= " WHERE td.fk_ndfp = ".$this->id;
				$sql.= " ORDER BY td.rowid";
		
				dol_syslog("Ndfp::fetch_lines sql=".$sql, LOG_DEBUG);

				$result = $this->db->query($sql);
				if ($result > 0)
				{
					$num = $this->db->num_rows($result);
					$i = 0;

					if ($num)
					{
						while ($i < $num)
						{
							$obj = $this->db->fetch_object($result);
							$this->tva_lines[$i]           = $obj;
							$this->tva_lines[$i]->tva_tx 	= $obj->fk_tva ? $obj->fk_tva : 0; // Compatibility
							//$this->tva_lines[$i]->tva_tx 	= $obj->taux ? $obj->taux : 0; // Compatibility
							$this->tva_lines[$i]->tms		= $this->db->jdate($obj->tms);
							$this->tva_lines[$i]->comment	= trim($obj->comment);
					
							$i++;
						}
					}

				}
				else
				{
					$this->error = $this->db->error()." sql=".$sql;
					return -1;
				}
			
				$sql = "SELECT  td.rowid, td.fk_ndfp, td.fk_ndfp_det, td.tms, nd.fk_exp, nd.comment, ";
				$sql.= " td.total_ht, td.total_ttc, td.total_ht_cur, td.total_ttc_cur, td.price_base_type, e.label";
				$sql.= " FROM ".MAIN_DB_PREFIX."ndfp_tax_det AS td";
				$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."ndfp_det AS nd ON nd.rowid = td.fk_ndfp_det";
				$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."c_ndfp_exp AS e ON e.rowid = nd.fk_exp";
				$sql.= " WHERE td.fk_ndfp = ".$this->id;
				$sql.= " ORDER BY td.rowid";
		
				dol_syslog("Ndfp::fetch_lines sql=".$sql, LOG_DEBUG);

				$result = $this->db->query($sql);
				
				
				if ($result > 0)
				{
					$num = $this->db->num_rows($result);
					$i = 0;

					if ($num)
					{
						while ($i < $num)
						{
							$obj = $this->db->fetch_object($result);
							$this->tax_lines[$i]           = $obj;
							$this->tax_lines[$i]->tms		= $this->db->jdate($obj->tms);
							$this->tax_lines[$i]->comment	= trim($obj->comment);
							$this->tax_lines[$i]->price_base_type = $this->tax_lines[$i]->price_base_type ? $this->tax_lines[$i]->price_base_type : 'HT';
                            $this->tax_lines[$i]->amount_tax = $this->tax_lines[$i]->price_base_type == 'HT' ? $this->tax_lines[$i]->total_ht : $this->tax_lines[$i]->total_ttc;
							
							$i++;
						}
					}					
				}
				else
				{
					$this->error = $this->db->error()." sql=".$sql;
					return -1;
				}
				
			}	
			
			return 1;						
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

        $result = $this->check_user_rights($user, 'delete');
        if ($result < 0)
        {
            return -1;
        }

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

		if (count($this->linkedObjects['invoice_supplier']) > 0)
		{
							
			foreach ($this->linkedObjects['invoice_supplier'] as $is)
			{					
				$this->deleteObjectLinked($is->id, 'invoice_supplier');
			}														
		}
				
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."ndfp WHERE rowid = ".$this->id;

		dol_syslog("Ndfp::delete sql=".$sql, LOG_DEBUG);

		$result = $this->db->query($sql);

		if ($result)
		{
            $sql = "DELETE FROM ".MAIN_DB_PREFIX."actioncomm WHERE fk_element = ".$this->id." AND elementtype = 'ndfp'";
    		dol_syslog("Ndfp::delete sql=".$sql, LOG_DEBUG);
    		$result = $this->db->query($sql);
 
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."ndfp_det WHERE fk_ndfp = ".$this->id;
			dol_syslog("Ndfp::delete sql=".$sql);
			$result = $this->db->query($sql); 

   			$sql = "DELETE FROM ".MAIN_DB_PREFIX."ndfp_tax_det WHERE fk_ndfp = ".$this->id;
			dol_syslog("Ndfp::delete sql=".$sql);
			$result = $this->db->query($sql); 
						   		
   			$sql = "DELETE FROM ".MAIN_DB_PREFIX."ndfp_tva_det WHERE fk_ndfp = ".$this->id;
			dol_syslog("Ndfp::delete sql=".$sql);
			$result = $this->db->query($sql); 
					
    		if ($result)
    		{
                $this->error = $langs->trans('NdfpHasBeenDeleted');
    			return 1;
    		}
    		else
    		{
    			$this->error = $this->db->error()." sql=".$sql;
    			return -1;
    		}
		}
		else
		{
			$this->error = $this->db->error()." sql=".$sql;
			return -1;
		}
	}

	/**
	 * Validate object
	 *
	 * @param 	user	User who validates
     * @param 	force_number	Set the new reference
	 * @return 	int		<0 if KO, >0 if OK
	 */
    function validate($user, $force_number='')
    {
        global $conf,$langs;

        /*$result = $this->check_user_rights($user, 'validate');
        if ($result < 0)
        {
            return -1;
        }
        */
        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        // Protection
        if ($this->statut != 0 && $this->statut != 4)
		{
            $this->error = $langs->trans('NotADraft');
            return -1;
        }

        $this->db->begin();

        // Define new ref
        if ($force_number)
        {
            $num = $force_number;
        }
        else if (preg_match('/^[\(]?PROV/i', $this->ref))
        {
            $num = $this->get_next_num_ref($this->fk_soc);
        }
        else
        {
            $num = $this->ref;
        }

        if ($num)
        {
            // Validate
            $this->tms = dol_now();
			$this->date_valid = dol_now();

            $sql = " UPDATE ".MAIN_DB_PREFIX."ndfp";
            $sql.= " SET ref='".$num."', statut = 1, tms = '".$this->db->idate($this->tms)."'";
			$sql.= " , date_valid = '".$this->db->idate($this->tms)."', fk_user_valid = ".$user->id;
            $sql.= " WHERE rowid = ".$this->id;

            dol_syslog("Ndfp::validate sql=".$sql);
            $resql = $this->db->query($sql);
            if (!$resql)
            {
                $this->error = $this->db->error()." sql=".$sql;
                $this->db->rollback();
                return -1;
            }


        	$this->oldref = '';


            if (preg_match('/^[\(]?PROV/i', $this->ref))
            {

                $ndfpref = dol_sanitizeFileName($this->ref);
                $snumndfp = dol_sanitizeFileName($num);

                $dirsource = $conf->ndfp->dir_output.'/'.$ndfpref;
                $dirdest = $conf->ndfp->dir_output.'/'.$snumndfp;

                if (file_exists($dirsource))
                {
                    dol_syslog("Ndfp::validate rename dir ".$dirsource." into ".$dirdest);

                    if (@rename($dirsource, $dirdest))
                    {
                    	$this->oldref = $ndfpref;

                        dol_syslog("Rename ok");
                        dol_delete_file($conf->ndfp->dir_output.'/'.$snumndfp.'/'.$ndfpref.'.*');
                    }
                }
            }

        	$this->ref = $num;
            $this->statut = 1;

            // Trigger calls
            include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");

            $interface = new Interfaces($this->db);
            $result = $interface->run_triggers('NDFP_VALIDATE',$this,$user,$langs,$conf);
            if ($result < 0) {
                $this->error = $langs->trans('ErrorCallingTrigger');
                $this->db->rollback();
                return -1;
            }

        }
        else
        {
            $this->error = $langs->trans('CanNotDetermineNewReference');
            $this->db->rollback();
            return -1;
        }


        $this->db->commit();
        $this->error = $langs->trans('NdfpHasBeenValidated');
        return 1;
    }

    /**
     *	\brief      Close the note
     *	@param      user        User who closes
     *	@return     int         <0 si ok, >0 si ok
     */
    function set_canceled($user)
    {
        global $conf, $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        $this->db->begin();

        $this->tms = dol_now();

        $sql = " UPDATE ".MAIN_DB_PREFIX."ndfp SET statut = 3, tms = '".$this->db->idate($this->tms)."' WHERE rowid = ".$this->id;

        dol_syslog("Ndfp::set_canceled sql=".$sql, LOG_DEBUG);

        $resql = $this->db->query($sql);
        if ($resql)
        {
            $this->statut = 3;

            include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
            $interface = new Interfaces($this->db);
            $result = $interface->run_triggers('NDFP_CANCEL',$this,$user,$langs,$conf);

            if ($result < 0)
            {
                $this->error = $langs->trans('ErrorCallingTrigger');
                $this->db->rollback();
                return -1;
            }

            $this->db->commit();
            $this->error = $langs->trans('NdfpHasBeenCanceled');
            return 1;

        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();
            return -2;
        }
    }

    /**
     *		\brief		Set validating status
     *		@param		user		Object user that modify
     *		@return		int			<0 if KO, >0 if OK
     */
    function set_validating($user)
    {
        global $conf, $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }


        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }

        $this->tms = dol_now();

        $sql = " UPDATE ".MAIN_DB_PREFIX."ndfp SET statut = 4,  tms = '".$this->db->idate($this->tms)."' WHERE rowid = ".$this->id;

        dol_syslog("Ndfp::set_validating sql=".$sql, LOG_DEBUG);

        $resql = $this->db->query($sql);

        if ($resql > 0)
        {
            $this->statut = 4;
            $this->error = $langs->trans('NdfpHasBeenModified');
            return 1;
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -1;
        }

    }
    
    /**
     *		\brief		Set draft status
     *		@param		user		Object user that modify
     *		@return		int			<0 if KO, >0 if OK
     */
    function set_draft($user)
    {
        global $conf, $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        if ($this->statut == 0)
        {
            $this->error = $langs->trans('AlreadyADraft');
            return -1;
        }

        $result = $this->check_user_rights($user, 'validate');
        if ($result < 0)
        {
            return -1;
        }

        $this->tms = dol_now();

        $sql = " UPDATE ".MAIN_DB_PREFIX."ndfp SET statut = 0,  tms = '".$this->db->idate($this->tms)."' WHERE rowid = ".$this->id;

        dol_syslog("Ndfp::set_draft sql=".$sql, LOG_DEBUG);

        $resql = $this->db->query($sql);

        if ($resql > 0)
        {
            $this->statut = 0;
            $this->error = $langs->trans('NdfpHasBeenModified');
            return 1;
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -1;
        }

    }

    /**
     *		\brief		Set unbilled
     *		@param		user		Object user that modify
     *		@return		int			<0 if KO, >0 if OK
     */
    function set_unbilled($user)
    {
        global $conf, $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }

        $this->tms = dol_now();

        $sql = " UPDATE ".MAIN_DB_PREFIX."ndfp SET billed = 0, tms = '".$this->db->idate($this->tms)."' WHERE rowid = ".$this->id;

        dol_syslog("Ndfp::set_billed sql=".$sql, LOG_DEBUG);

        $result = $this->db->query($sql);

        if ($result > 0)
        {
            $this->fetch($this->id);
            
            $this->error = $langs->trans('NdfpHasBeenUpdated');
            return 1;
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -1;
        }
    }
    
    /**
     *		\brief		Set billed
     *		@param		user		Object user that modify
     *		@return		int			<0 if KO, >0 if OK
     */
    function set_billed($user)
    {
        global $conf, $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }

        $this->tms = dol_now();

        $sql = " UPDATE ".MAIN_DB_PREFIX."ndfp SET billed = 1, tms = '".$this->db->idate($this->tms)."' WHERE rowid = ".$this->id;

        dol_syslog("Ndfp::set_billed sql=".$sql, LOG_DEBUG);

        $result = $this->db->query($sql);

        if ($result > 0)
        {
            $this->fetch($this->id);
            
            $this->error = $langs->trans('NdfpHasBeenUpdated');
            return 1;
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -1;
        }
    }
    
    /**
     *		\brief		Set payment mode
     *		@param		user		Object user that modify
     *		@return		int			<0 if KO, >0 if OK
     */
    function set_paymentmode($user)
    {
        global $conf, $langs;

        $fk_mode_reglement = GETPOST('fk_mode_reglement');

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        if ($this->statut != 0)
        {
            $this->error = $langs->trans('AlreadyADraft');
            return -1;
        }

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }

        $this->tms = dol_now();

        $sql = " UPDATE ".MAIN_DB_PREFIX."ndfp SET fk_mode_reglement = ".$this->db->escape($fk_mode_reglement).", tms = '".$this->db->idate($this->tms)."' WHERE rowid = ".$this->id;

        dol_syslog("Ndfp::set_paymentmode sql=".$sql, LOG_DEBUG);

        $result = $this->db->query($sql);

        if ($result > 0)
        {
            $this->fetch($this->id);
            
            $this->error = $langs->trans('NdfpHasBeenUpdated');
            return 1;
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -1;
        }
    }


    /**
     *		\brief		Set client
     *		@param		user		Object user that modify
     *		@return		int			<0 if KO, >0 if OK
     */
    function set_thirdparty($user)
    {
        global $conf, $langs;

        $fk_soc = GETPOST('fk_soc');

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        if ($this->statut != 0)
        {
            $this->error = $langs->trans('AlreadyADraft');
            return -1;
        }

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }

        if ($fk_soc < 0){
			$this->error = $langs->trans('ErrorBadParameterCat');
			return -1;
        }

        $this->tms = dol_now();

        $sql = " UPDATE ".MAIN_DB_PREFIX."ndfp SET fk_soc = ".$this->db->escape($fk_soc).", tms = '".$this->db->idate($this->tms)."' WHERE rowid = ".$this->id;

        dol_syslog("Ndfp::set_thirdparty sql=".$sql, LOG_DEBUG);

        $result = $this->db->query($sql);

        if ($result > 0)
        {
            $this->fk_soc = $fk_soc;
            $this->error = $langs->trans('NdfpHasBeenUpdated');
            return 1;
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -1;
        }
    }

    
    /**
     *		\brief		Set user
     *		@param		user		Object user that modify
     *		@return		int			<0 if KO, >0 if OK
     */
    function setfk_user($user)
    {
        global $conf, $langs;

        $fk_user = GETPOST('fk_user');

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        if ($this->statut != 0)
        {
            $this->error = $langs->trans('NotADraft');
            return -1;
        }

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }


        if ($fk_user < 0){
			$this->error = $langs->trans('ErrorBadParameterUser');
			return -1;
        }

        $this->tms = dol_now();
        $sql = " UPDATE ".MAIN_DB_PREFIX."ndfp SET fk_user = ".$this->db->escape($fk_user).", tms = '".$this->db->idate($this->tms)."' WHERE rowid = ".$this->id;

        dol_syslog("Ndfp::setfk_user sql=".$sql, LOG_DEBUG);

        $result = $this->db->query($sql);

        if ($result > 0)
        {
            $this->fk_user = $fk_user;
            
     
            //$this->update_lines();
            
            $this->fetch($this->id);
            
            $this->error = $langs->trans('NdfpHasBeenUpdated');

            return 1;
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -1;
        }
    }

    /**
     *		\brief		Set comments (user and admin if enough rights)
     *		@param		user		Object user that modify
     *		@return		int			<0 if KO, >0 if OK
     */
    function setcomments($user)
    {
        global $conf, $langs;

        $comment_user = trim(GETPOST('comment_user', 'chaine'));
        $comment_admin = trim(GETPOST('comment_admin', 'chaine'));

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        if ($this->statut != 0)
        {
            $this->error = $langs->trans('NotADraft');
            return -1;
        }

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }

        $this->tms = dol_now();

        $sql = " UPDATE ".MAIN_DB_PREFIX."ndfp SET comment_user = '".$this->db->escape($comment_user)."', ";
        $sql.= ($user->admin ? "comment_admin = '".$this->db->escape($comment_admin)."', " : "")." tms = '".$this->db->idate($this->tms)."' WHERE rowid = ".$this->id;

        dol_syslog("Ndfp::setcomments sql=".$sql, LOG_DEBUG);

        $result = $this->db->query($sql);

        if ($result > 0)
        {
            $this->comment_user = $comment_user;
            $this->comment_admin = $comment_admin;

            $this->error = $langs->trans('NdfpHasBeenUpdated');
            return 1;
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -1;
        }
    }

    /**
     *		\brief		Set description
     *		@param		user		Object user that modify
     *		@return		int			<0 if KO, >0 if OK
     */
    function setdesc($user)
    {
        global $conf, $langs;

        $description = trim(GETPOST('description', 'chaine'));

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        if ($this->statut != 0)
        {
            $this->error = $langs->trans('NotADraft');
            return -1;
        }

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }

        $this->tms = dol_now();

        $sql = " UPDATE ".MAIN_DB_PREFIX."ndfp SET description = '".$this->db->escape($description)."', tms = '".$this->db->idate($this->tms)."' WHERE rowid = ".$this->id;
        dol_syslog("Ndfp::setdesc sql=".$sql, LOG_DEBUG);

        $result = $this->db->query($sql);

        if ($result > 0)
        {
            $this->description = $description;
            $this->error = $langs->trans('NdfpHasBeenUpdated');
            return 1;
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -1;
        }
    }

    /**
     *		\brief		Set project
     *		@param		user		Object user that modify
     *		@return		int			<0 if KO, >0 if OK
     */
    function classin($user)
    {
        global $conf, $langs;

        $fk_project = ($conf->projet->enabled ? GETPOST('fk_project') : 0);

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        if ($this->statut != 0)
        {
            $this->error = $langs->trans('AlreadyADraft');
            return -1;
        }

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }

        $this->tms = dol_now();
        $sql = " UPDATE ".MAIN_DB_PREFIX."ndfp SET fk_project = ".$this->db->escape($fk_project).", tms = '".$this->db->idate($this->tms)."' WHERE rowid = ".$this->id;
        dol_syslog("Ndfp::classin sql=".$sql, LOG_DEBUG);

        $result = $this->db->query($sql);

        if ($result > 0)
        {
            $this->fk_project = $fk_project;
            $this->error = $langs->trans('NdfpHasBeenUpdated');
            return 1;
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -1;
        }
    }


    /**
     *		\brief		Set date start
     *		@param		user		Object user that modify
     *		@return		int			<0 if KO, >0 if OK
     */
    function setdates($user)
    {
        global $conf, $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        if ($this->statut != 0)
        {
            $this->error = $langs->trans('AlreadyADraft');
            return -1;
        }

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }

        $dates = dol_mktime(12,0,0, $_POST['datesmonth'], $_POST['datesday'], $_POST['datesyear']);
        if ($this->datee < $dates)
        {
			$this->error = $langs->trans('ErrorBadParameterDateDoNotMatch');
			return -1;
        }

        $this->tms = dol_now();

        $sql = " UPDATE ".MAIN_DB_PREFIX."ndfp SET `dates` = '".$this->db->idate($dates)."', tms = '".$this->db->idate($this->tms)."' WHERE rowid = ".$this->id;
        dol_syslog("Ndfp::setdates sql=".$sql, LOG_DEBUG);

        $result = $this->db->query($sql);

        if ($result > 0)
        {
            $this->dates = $dates;
            $this->error = $langs->trans('NdfpHasBeenUpdated');
            return 1;
        }
        else
        {
            $this->db->rollback();

            return -1;
        }
    }

    /**
     *		\brief		Set date end
     *		@param		user		Object user that modify
     *		@return		int			<0 if KO, >0 if OK
     */
    function setdatee($user)
    {
        global $conf, $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        if ($this->statut != 0)
        {
            $this->error = $langs->trans('AlreadyADraft');
            return -1;
        }

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }

        $datee = dol_mktime(12,0,0,$_POST['dateemonth'],$_POST['dateeday'],$_POST['dateeyear']);

        if ($datee < $this->dates)
        {
			$this->error = $langs->trans('ErrorBadParameterDateDoNotMatch');
			return -1;
        }

        $this->tms = dol_now();

        $sql = " UPDATE ".MAIN_DB_PREFIX."ndfp SET `datee` = '".$this->db->idate($datee)."', tms = '".$this->db->idate($this->tms)."' WHERE rowid = ".$this->id;
        dol_syslog("Ndfp::setdates sql=".$sql, LOG_DEBUG);

        $result = $this->db->query($sql);

        if ($result > 0)
        {
            $this->datee = $datee;
            $this->error = $langs->trans('NdfpHasBeenUpdated');
            return 1;
        }
        else
        {
            $this->db->rollback();

            return -1;
        }
    }

    /**
     *      \brief                  Check if the note can be changed to unpaid and do it if so
     *      @param      user        Object user
     *      @return     int         <0 si ko, >0 si ok
     */
    function reopen($user)
    {
        global $conf, $langs;


        $result = $this->set_unpaid($user);

        return $result;
    }

    /**
     *      \brief                  Check if the note can be changed to paid and do it if so
     *      @param      user        Object user
     *      @return     int         <0 si ko, >0 si ok
     */
    function confirm_paid($user)
    {
        global $conf, $langs;

        $confirm = GETPOST('confirm');

        if ($confirm == 'yes')
        {
            $already_paid  = $this->get_amount_payments_done();
            $remain_to_pay = price2num($this->total_ttc - $already_paid, 'MT');

            if ($remain_to_pay == 0)
            {
                $result = $this->set_paid($user);

                if ($result > 0)
                {
                    $this->error = $langs->trans('NdfpHasBeenUpdated');
                }

                return $result;
            }
            else
            {
                $this->error = $langs->trans('CanNotSetToPaid');
                return -1;
            }
        }
        else
        {
            $this->error = $langs->trans('ActionCanceled');
            return -1;
        }


    }
    /**
     *      \brief                  Modify note to paid
     *      @param      user        Object user
     *      @return     int         <0 si ko, >0 si ok
     */
    function set_paid($user)
    {
        global $conf, $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }

        $this->db->begin();

        $this->tms = dol_now();
        $sql = "UPDATE ".MAIN_DB_PREFIX."ndfp SET statut = 2, tms = '".$this->db->idate($this->tms)."' WHERE rowid = ".$this->id;

        dol_syslog("Ndfp::set_paid sql=".$sql);
        $result = $this->db->query($sql);

        if ($result)
        {
            include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
            $interface = new Interfaces($this->db);
            $result = $interface->run_triggers('NDFP_PAID',$this, $user, $langs, $conf);
            if ($result < 0)
            {
                $this->error = $langs->trans('ErrorCallingTrigger');
                $this->db->rollback();
                return -1;
            }

            $this->db->commit();

            $this->statut = 2;
            $this->error = $langs->trans('NdfpHasBeenUpdated');

            return 1;
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -1;
        }
    }

    /**
     *      \brief                  Modify note to unpaid
     *      @param      user        Object user
     *      @return     int         <0 si ko, >0 si ok
     */
    function set_unpaid($user)
    {
        global $conf, $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }

        if ($this->statut == 0)
        {
            $this->error = $langs->trans('NdfpIsDraftAndCanNotBeUnpaid');
            return -1;
        }

        $this->db->begin();
        $this->tms = dol_now();

        $sql = "UPDATE ".MAIN_DB_PREFIX."ndfp SET statut = 1, tms = '".$this->db->idate($this->tms)."' WHERE rowid = ".$this->id;

        dol_syslog("Ndfp::set_unpaid sql=".$sql);
        $result = $this->db->query($sql);

        if ($result)
        {
            include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
            $interface = new Interfaces($this->db);
            $result = $interface->run_triggers('NDFP_UNPAID',$this, $user, $langs, $conf);
            if ($result < 0)
            {
                $this->error = $langs->trans('ErrorCallingTrigger');
                $this->db->rollback();
                return -1;
            }

            $this->db->commit();

            $this->statut = 1;
            $this->error = $langs->trans('NdfpHasBeenUpdated');

            return 1;
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -1;
        }
    }

    /**
     *		\brief		Build PDF
     *		@param		user		Object user that modify
     *		@return		int			<0 if KO, >0 if OK
     */
    function builddoc($user)
    {
        global $conf, $langs;

        if (GETPOST('model'))
        {
            $this->setDocModel($user, GETPOST('model'));
        }

        $result = $this->generate_pdf($user);

        return $result;
    }

    /**
     *		\brief		Confirm delete line
     *		@param		user		Object user that confirms
     *		@return		int			<0 if KO, >0 if OK
     */
    function confirm_deletetaxline($user)
    {

        global $conf, $langs;

        $lineid = GETPOST('lineid');
        $confirm = GETPOST('confirm');

        $result = $this->check_user_rights($user, 'delete');

        if ($result < 0)
        {
            return -1;
        }

        if ($confirm == 'yes')
        {
            $result = $this->deletetaxline($lineid, $user);

            if ($result > 0)
            {
                $this->generate_pdf($user);
                $this->error = $langs->trans('TaxDeleted');

                return 1;
            }
            else
            {
                return -1;
            }
        }
        else
        {
            $this->error = $langs->trans('ActionCanceled');
            return -1;
        }

    }
    
    /**
     *		\brief		Confirm delete line
     *		@param		user		Object user that confirms
     *		@return		int			<0 if KO, >0 if OK
     */
    function confirm_deletetvaline($user)
    {

        global $conf, $langs;

        $lineid = GETPOST('lineid');
        $confirm = GETPOST('confirm');

        $result = $this->check_user_rights($user, 'delete');

        if ($result < 0)
        {
            return -1;
        }

        if ($confirm == 'yes')
        {
            $result = $this->deletetvaline($lineid, $user);

            if ($result > 0)
            {
                $this->generate_pdf($user);
                $this->error = $langs->trans('TvaDeleted');

                return 1;
            }
            else
            {
                return -1;
            }
        }
        else
        {
            $this->error = $langs->trans('ActionCanceled');
            return -1;
        }

    }
    
    /**
     *		\brief		Confirm delete line
     *		@param		user		Object user that confirms
     *		@return		int			<0 if KO, >0 if OK
     */
    function confirm_deleteline($user)
    {

        global $conf, $langs;

        $lineid = GETPOST('lineid');
        $confirm = GETPOST('confirm');

        $result = $this->check_user_rights($user, 'delete');

        if ($result < 0)
        {
            return -1;
        }

        if ($confirm == 'yes')
        {
            $result = $this->deleteline($lineid, $user);

            if ($result > 0)
            {
                $this->generate_pdf($user);
                $this->error = $langs->trans('ExpDeleted');

                return 1;
            }
            else
            {
                return -1;
            }
        }
        else
        {
            $this->error = $langs->trans('ActionCanceled');
            return -1;
        }

    }

    /**
     *      \brief      Confirm client bill
     *      @param      user        Object user that confirms
     *      @return     int         <0 if KO, >0 if OK
     */
    function confirm_clientbill($user)
    {

        global $conf, $langs;

        $confirm = GETPOST('confirm');

        $result = $this->check_user_rights($user, 'bill');

        if ($result < 0)
        {
            return -1;
        }

        if ($confirm == 'yes')
        {
 
                
            if (count($this->linkedObjects['facture']) > 0)
            {
                $this->error = $langs->trans("ClientBillAlreadyExist");
                return -1;
            }
                         
            $socid = GETPOST('socid', 'int');
            $now = dol_now();
            
            // Create supplier invoice   
            $object = new Facture($this->db);
           
            $object->ref           = '';
            $object->ref_int       = $this->ref;
            $object->socid         = $socid;
            $object->date          = $now;
            $object->note_public   = $this->comment_user;
            $object->note_private  = $this->comment_admin;
            $object->cond_reglement_id = 1;
            $object->mode_reglement_id = 0;
            $object->fk_project    = $this->fk_project ? $this->fk_project : null;

            $object->origin    = 'ndfp';
            $object->origin_id = $this->id;

            $id = $object->create($user);        

            if ($id > 0)
            {
    
                $lines = $this->lines;

                $num = count($lines);
                for ($i = 0; $i < $num; $i++)
                {
                    $desc = $langs->trans($lines[$i]->label);
                    
                    $fk_product_type = $lines[$i]->fk_product_type ? $lines[$i]->fk_product_type : 0;

                    // Dates
                    $date_start = $lines[$i]->dated;
                    $date_end = $lines[$i]->datef;

                    $tva_tx = $lines[$i]->tva_tx;              
                    //$tva_tx = ($lines[$i]->total_ttc/$lines[$i]->total_ht - 1) * 100;  

                    $result = $object->addline(
                        $desc,
                        $lines[$i]->total_ht,
                        1,
                        $tva_tx,
                        '',
                        '',
                        $lines[$i]->fk_product,
                        0,
                        $date_start,
                        $date_end,
                        0,
                        0,
                        0,
                        'TTC',
                        $lines[$i]->total_ttc,
                        $fk_product_type
                    );   
                }              
                
                $object->validate($user);
                $object->add_object_linked($this->element, $this->id);  
                // 
                $this->error = $langs->trans('ClientInvoiceCreated', $object->ref);
                
                $this->fetchObjectLinked($this->id, $this->element, '', 'facture');
                
                return $id;

            }
            else
            {
               $this->error = $langs->trans('ErrorClientBillingNdfp');
               return -1;
            }
        }
        else
        {
            $this->error = $langs->trans('ActionCanceled');
            return -1;
        }

    }

    /**
     *      \brief      Confirm bill
     *      @param      user        Object user that confirms
     *      @return     int         <0 if KO, >0 if OK
     */
    function confirm_bill($user)
    {

        global $conf, $langs, $mysoc;

        $confirm = GETPOST('confirm');

        $result = $this->check_user_rights($user, 'bill');

        if ($result < 0)
        {
            return -1;
        }

        if ($confirm == 'yes')
        {
 
                
            if (count($this->linkedObjects['invoice_supplier']) > 0)
            {
                $this->error = $langs->trans("BillAlreadyExist");
                return -1;
            }
                         
            $socid = GETPOST('socid', 'int');
            $now = dol_now();
            
            // Create supplier invoice   
            $object = new FactureFournisseur($this->db);
           
            $object->ref           = '';
            $object->ref_supplier  = $this->ref;
            $object->socid         = $socid;
            $object->libelle       = '';
            $object->date          = $now;
            $object->date_echeance = '';
            $object->note_public   = $this->comment_user;
            $object->note_private  = $this->comment_admin;
            $object->cond_reglement_id = 1;
            $object->mode_reglement_id = 0;
            $object->fk_project    = $this->fk_project ? $this->fk_project : null;

            $object->origin    = 'ndfp';
            $object->origin_id = $this->id;

            $id = $object->create($user);        

            if ($id > 0)
            {
                $lines = $this->lines;

                $num = count($lines);
                for ($i = 0; $i < $num; $i++)
                {
                    // Dates
                    $date_start = $conf->global->NDFP_DATES_ON_BILL ? $lines[$i]->dated : '';
                    $date_end = $conf->global->NDFP_DATES_ON_BILL ? $lines[$i]->datef : '';
                    $fk_product = $lines[$i]->fk_product;
                    $fk_product_type = $lines[$i]->fk_product_type ? $lines[$i]->fk_product_type : 0;

                    $total_ht = price2num(price($lines[$i]->total_ht));
                    $total_ttc = price2num(price($lines[$i]->total_ttc));
                    $total_tva = price2num(price($lines[$i]->total_tva));
                    $tva_tx = price2num(price($lines[$i]->tva_tx));

                    $tva_tx = $lines[$i]->milestone ? 100 * $total_tva/$total_ht : $tva_tx;                        
     
                    $desc = empty($conf->global->NDFP_DATES_ON_BILL) ? dol_print_date($lines[$i]->dated, '%d/%m/%Y').' - ' : '';
                    $desc.= $langs->trans($lines[$i]->label);
                    $desc.= ($lines[$i]->comment ? ' - '.$lines[$i]->comment : '');

                    $result = $object->addline(
                        $desc,
                        $total_ttc,
                        $tva_tx,
                        0,
                        0,
                        1,
                        $fk_product,
                        0,
                        $date_start,
                        $date_end,
                        0,
                        '',
                        'TTC',
                        $fk_product_type
                    );
                    

                }
                
                $object->fetch($object->id);
                
                $object->update_price(0, '0');
                
                $object->validate($user);
                $object->add_object_linked($this->element, $this->id);  
                // 
                $this->error = $langs->trans('InvoiceCreated', $object->ref);
                
                $this->fetchObjectLinked($this->id, $this->element, '', 'invoice_supplier');
                
                return $id;

            }
            else
            {
               $this->error = $langs->trans('ErrorBillingNdfp');
               return -1;
            }
        }
        else
        {
            $this->error = $langs->trans('ActionCanceled');
            return -1;
        }

    }
    
    /**
     *		\brief		Confirm validation
     *		@param		user		Object user that confirms
     *		@return		int			<0 if KO, >0 if OK
     */
    function confirm_valid($user)
    {

        global $conf, $langs;

        $confirm = GETPOST('confirm');

        $result = $this->check_user_rights($user, 'validate');

        if ($result < 0)
        {
            return -1;
        }

        if ($confirm == 'yes')
        {
            $result = $this->validate($user);

            if ($result > 0)
            {
            	if ($this->autoclass)
            	{
            		$this->set_paid($user);
            	}
            	
                $this->generate_pdf($user);
                $this->error = $langs->trans('NdfpHasBeenValidated');

                return 1;

            }
            else
            {
               $this->error = $langs->trans('ErrorUpdatingNdfp');
               return -1;
            }
        }
        else
        {
            $this->error = $langs->trans('ActionCanceled');
            return -1;
        }

    }


    /**
     *		\brief		Remove joined file
     *		@param		user		Object user that removes
     *		@return		int			<0 if KO, >0 if OK
     */
    function remove_file($user)
    {

        global $conf, $langs;

        $urlfile = GETPOST('file');

		$upload_dir = $conf->ndfp->dir_output;

		$file = $upload_dir . '/' . $urlfile;
		dol_delete_file($file, 0, 0, 0, 'FILE_DELETE', $this);


        $this->error = $langs->trans("FileHasBeenRemoved");
        return 1;

    }
    
    /**
     *		\brief		Confirm delete
     *		@param		user		Object user that confirms
     *		@return		int			<0 if KO, >0 if OK
     */
    function confirm_delete($user)
    {

        global $conf, $langs;

        $confirm = GETPOST('confirm');

        $result = $this->check_user_rights($user, 'delete');

        if ($result < 0)
        {
            return -1;
        }

        if ($confirm == 'yes')
        {
            $result = $this->delete($user);

            if ($result > 0)
            {
                $this->error = $langs->trans('NdfpHasBeenDeleted');
                return 1;
            }
            else
            {
               $this->error = $langs->trans('ErrorDeletingNdfp');
               return -1;

            }
        }
        else
        {
            $this->error = $langs->trans('ActionCanceled');
            return -1;
        }


    }

    /**
     *		\brief		Confirm canceled
     *		@param		user		Object user that confirms
     *		@return		int			<0 if KO, >0 if OK
     */
    function confirm_canceled($user)
    {

        global $conf, $langs;

        $confirm = GETPOST('confirm');

        $result = $this->check_user_rights($user, 'create');

        if ($result < 0)
        {
            return -1;
        }

        if ($confirm == 'yes')
        {
            $result = $this->set_canceled($user);

            if ($result > 0)
            {
                $this->error = $langs->trans('NdfpHasBeenCanceled');
                return 1;
            }
            else
            {
               $this->error = $langs->trans('ErrorCancelingNdfp');
               return -1;

            }
        }
        else
        {
            $this->error = $langs->trans('ActionCanceled');
            return -1;
        }

    }

    /**
     *		\brief		Confirm clone
     *		@param		user		Object user that confirms
     *		@return		int			<0 if KO, >0 if OK
     */
    function confirm_clone($user)
    {

        global $conf, $langs;

        $confirm = GETPOST('confirm');

        $result = $this->check_user_rights($user, 'create');

        if ($result < 0)
        {
            return -1;
        }

        if ($confirm == 'yes')
        {
            $newid = $this->create_from_clone($this->id, $user);

            if ($newid > 0)
            {
                $this->error = $langs->trans('NdfpHasBeenAdded');
                $this->fetch($newid);

                return $newid;
            }
            else
            {
                $this->error = $langs->trans('ErrorAddingNdfp');
                return -1;
            }
        }
        else
        {
            $this->error = $langs->trans('ActionCanceled');
            return -1;
        }

    }

    /**
     *		\brief		Modify note
     *		@param		user		Object user that modifies
     *		@return		int			<0 if KO, >0 if OK
     */
    function modif($user)
    {

        global $conf, $langs;

        $result = $this->check_user_rights($user, 'validate');

        $totalPaid = 0;
        $payments = $this->get_payments();
        foreach ($payments AS $payment)
        {
            $totalPaid += $payment->amount;
        }

        if ($totalPaid == 0)
        {
            $result = $this->set_draft($user);

            if ($result > 0)
            {

                $result = $this->generate_pdf($user);

                return $result;
            }
            else
            {
                return -1;
            }
        }
        else
        {
            $this->error = $langs->trans('NdfpCanNotBeSetToDraft');
        }

    }

   /**
     *		\brief		Send note
     *		@param		user		Object user that sents
     *		@return		int			<0 if KO, >0 if OK
     */
    function send($user)
    {

        global $conf, $langs;

        if ($_POST['addfile'] || $_POST['removedfile'] || $_POST['cancel'])
        {
            return 0;
        }

        $result = $this->check_user_rights($user, 'send');
        if ($result < 0)
        {
            return -1;
        }

        if (!$this->id)
        {
            $this->error = $langs->trans('NdfpIdIsMissing');
            return -1;
        }

        $langs->load('mails');

        $subject = '';

        $this->generate_pdf($user);

        $ref = dol_sanitizeFileName($this->ref);
        $file = $conf->ndfp->dir_output . '/' . $ref . '/' . $ref . '.pdf';

        if (is_readable($file))
        {
            if ($_POST['sendto'])
            {
                $sendto = $_POST['sendto'];
                $sendtoid = 0;
            }

            if (dol_strlen($sendto))
            {
                $langs->load("commercial");

                $from = $_POST['fromname'] . ' <' . $_POST['frommail'] .'>';
                $replyto = $_POST['replytoname']. ' <' . $_POST['replytomail'].'>';

                $message = $_POST['message'];


                $sendtocc = $_POST['sendtocc'];
                $deliveryreceipt = $_POST['deliveryreceipt'];


                if (dol_strlen($_POST['subject']))
                {
                   $subject = $_POST['subject'];
                }
                else
                {
                   $subject = $langs->transnoentities('NdfpSing').' '.$this->ref;
                }


                // Create form object
                include_once(DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php');

                $formmail = new FormMail($db);

                $attachedfiles = $formmail->get_attached_files();
                $filepath = $attachedfiles['paths'];
                $filename = $attachedfiles['names'];
                $mimetype = $attachedfiles['mimes'];
                

                $mailfile = new CMailFile($subject, $sendto, $from, $message, $filepath, $mimetype, $filename, $sendtocc, '', $deliveryreceipt);

                if ($mailfile->error)
                {
                    $this->error = $mailfile->error;
                    return -1;
                }
                else
                {
                    $result = $mailfile->sendfile();
                    if ($result)
                    {
                        // Appel des triggers
                        include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
                        $interface = new Interfaces($this->db);
                        $result = $interface->run_triggers('NDFP_SENTBYMAIL',$this, $user, $langs, $conf);

                        if ($result < 0)
                        {
                            $this->error = $langs->trans('ErrorCallingTrigger');
                            return -1;
                        }

                        $this->error = $langs->trans('NdfpHasBeenSent');
                        return 1;
                    }
                    else
                    {
                        $this->error = $mailfile->error;
                        return -1;
                    }
                }
            }
            else
            {
                $langs->load("other");
                $this->error = $langs->trans('ErrorMailRecipientIsEmpty');

                return -1;
            }
        }
        else
        {
            $langs->load("other");
            $this->error = $langs->trans('ErrorCantReadFile',$file);

            return -1;
        }
    }

    /**
     *		\brief		Generate PDF
     *		@param		user		Object user that modify
     *		@return		int			<0 if KO, >0 if OK
     */
    function generate_pdf($user)
    {
        global $conf, $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        $outputlangs = $langs;
        $newlang = '';
        if ($conf->global->MAIN_MULTILANGS && empty($newlang) && ! empty($_REQUEST['lang_id'])) $newlang = $_REQUEST['lang_id'];
        if ($conf->global->MAIN_MULTILANGS && empty($newlang)) $newlang = $user->lang;

        if (! empty($newlang))
        {
            $outputlangs = new Translate("", $conf);
            $outputlangs->setDefaultLang($newlang);
        }

    	$result = $this->fetch($this->id);
        if ($result < 0)
        {
            return -1;
        }

        $result = ndfp_pdf_create($this->db, $this, '', $this->modelpdf, $outputlangs);

        return $result;
    }
    
    /**
     *		\brief		Initialize an example of note with random values
     */
    function init_as_specimen()
    {
        global $user, $langs, $conf, $mysoc;

        // Initialize parameters
		// For random external reference
		$length = 8;
		$characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
					
        $now = dol_now();

        $this->id = 0;
        $this->ref = 'SPECIMEN';
        $this->specimen = 1;

        $this->entity       = $conf->entity;
        $this->datec = $now; // Confirmation date
        $this->datef = $now; // Creation date for ref numbering

		$this->tms = $now;

        $this->cur_iso           = 'EUR';
		$this->entity            = $conf->entity;
		$this->datec             = $now;
		$this->dates             = $now;
        $this->datee             = $now;
		$this->datef             = $now;
		$this->fk_user_author    = $user->id;
		$this->fk_user           = $user->id;

        $this->fk_soc            = 0;
        $this->fk_project        = 0;

		$this->statut            = 1;
		$this->fk_cat            = 6;

        $this->total_tva           = 0;
        $this->total_ht           = 0;
        $this->total_ttc           = 0;
		
		
		$this->description       = $langs->transnoentities('SpecimenDescription');
		$this->comment_user      = '';
		$this->comment_admin     = '';

         // Get lines        
         
        $sql = " SELECT e.rowid, e.code, e.label, e.fk_tva";
		$sql.= " FROM ".MAIN_DB_PREFIX."c_ndfp_exp AS e";
        $sql.= " WHERE e.code <> 'EX_KME' ";
             

		dol_syslog("Ndfp::init_as_specimen sql=".$sql, LOG_DEBUG);

		$result = $this->db->query($sql);
		if ($result)
		{
            $num = $this->db->num_rows($result);
            $i = 0;

            if ($num)
            {
                while ($i < $num)
                {
        			$obj = $this->db->fetch_object($result);
        			$this->lines[$i]           = $obj;

                    $this->lines[$i]->dated			    = $now;
                    $this->lines[$i]->datef				= $now;
                    $this->lines[$i]->datec				= $now;
                    $this->lines[$i]->fk_user_author		= $user->id;
                    $this->lines[$i]->fk_exp		       = $obj->rowid;
                    $this->lines[$i]->taux		       = $obj->fk_tva;
                    $this->lines[$i]->qty			        = 1;
					
					$this->lines[$i]->ref_ext = '';
					$this->lines[$i]->comment = $langs->trans('ThisIsAComment');

					$this->lines[$i]->rate          	= 1;
                    $this->lines[$i]->total_ttc          = rand(1, 1000);
                    $this->lines[$i]->total_ttc_cur      = $this->lines[$i]->total_ttc;
                    $this->lines[$i]->total_ht           = price2num($this->lines[$i]->total_ttc/(1 + $obj->taux/100), 'MT');
                    $this->lines[$i]->total_ht_cur       = price2num($this->lines[$i]->total_ttc/(1 + $obj->taux/100), 'MT');
                    $this->lines[$i]->total_tva          = price2num($this->lines[$i]->total_ttc - $this->lines[$i]->total_ht);

                    $this->lines[$i]->tms					= $now;

                    $this->total_tva   += $this->lines[$i]->total_tva;
                    $this->total_ht    += $this->lines[$i]->total_ht;
                    $this->total_ttc   += $this->lines[$i]->total_ttc;

					// Generate random reference
					for ($p = 0; $p < $length; $p++) 
					{
						$this->lines[$i]->ref_ext .= $characters[rand(0, strlen($characters)-1)];
					}
					
                    $i++;
                }
            }

			return 1;
		}
		else
		{
			$this->error = $this->db->error()." sql=".$sql;
			return -1;
		}
    }

     /**
     *      \brief Return next reference of confirmation not already used (or last reference)
     *      @param	   soc  		           objet company
     *      @param     mode                    'next' for next value or 'last' for last value
     *      @return    string                  free ref or last ref
     */
    function get_next_num_ref($soc, $mode = 'next')
    {
        global $conf, $langs;

        $langs->load("nfdp");

        // Clean parameters (if not defined or using deprecated value)
        if (empty($conf->global->NDFP_ADDON)){
            $conf->global->NDFP_ADDON = 'mod_ndfp_saturne';
        }else if ($conf->global->NDFP_ADDON == 'saturne'){
            $conf->global->NDFP_ADDON = 'mod_ndfp_saturne';
        }else if ($conf->global->NDFP_ADDON == 'uranus'){
            $conf->global->NDFP_ADDON = 'mod_ndfp_uranus';
        }

        $included = false;

        $classname = $conf->global->NDFP_ADDON;
        $file = $classname.'.php';

        // Include file with class
        $dir = '/ndfp/core/modules/ndfp/';
        $included=dol_include_once($dir.$file);

        if (! $included)
        {
            $this->error = $langs->trans('FailedToIncludeNumberingFile');
            return -1;
        }

        $obj = new $classname();

        $numref = "";
        $numref = $obj->getNumRef($soc, $this, $mode);

        if ($numref != "")
        {
            return $numref;
        }
        else
        {
            return -1;
        }
    }


    /**
     *  \brief Return label of object status
     *  @param      mode            0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=short label + picto
     *  @param      alreadypaid     0=No payment already done, 1=Some payments already done
     *  @return     string          Label
     */
    function get_lib_statut($mode = 0, $alreadypaid = -1)
    {
        return $this->lib_statut($this->statut, $mode, $alreadypaid);
    }

    /**
     *  \brief Return category name
     *  @return     string          Label
     */
    function get_cat_name()
    {
        global $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        $sql  = " SELECT c.rowid, p.label AS plabel, c.label AS clabel";
        $sql .= " FROM ".MAIN_DB_PREFIX."c_ndfp_exp_tax_cat AS c";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_ndfp_exp_tax_cat AS p ON p.rowid = c.fk_parent";
        $sql .= " WHERE c.rowid = ".$this->fk_cat;

        dol_syslog("Ndfp::get_cat_name sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);

        $name = '';

        if ($resql)
        {
            $num = $this->db->num_rows($resql);
            $i = 0;

            if ($num)
            {
                $obj = $this->db->fetch_object($resql);

                $name = $langs->trans($obj->plabel) .' - '. $langs->trans($obj->clabel);
            }
        }

        return $name;

    }



    /**
     *    	\brief      Renvoi le libelle d'un statut donne
     *    	\param      statut        	Id statut
     *    	\param      mode          	0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
     *		\param		alreadypaid	    Montant deja paye
     *    	\return     string        	Libelle du statut
     */
    function lib_statut($statut, $mode = 0,$alreadypaid = -1)
    {
        global $langs;

        if ($mode == 0){

            if ($statut == 0) return $langs->trans('NdfpStatusDraft');
            if ($statut == 2) return $langs->trans('NdfpStatusPaid');
            if ($statut == 3 && $alreadypaid <= 0) return $langs->trans('NdfpStatusClosedUnpaid');
            if ($statut == 3 && $alreadypaid > 0) return $langs->trans('NdfpStatusClosedPaidPartially');
            if ($statut == 4) return $langs->trans('NdfpStatusValidating');
            if ($alreadypaid <= 0) return $langs->trans('NdfpStatusNotPaid');

            return $langs->trans('NdfpStatusStarted');
        }

        if ($mode == 1)
        {
            if ($statut == 0) return $langs->trans('NdfpShortStatusDraft');
            if ($statut == 2) return $langs->trans('NdfpShortStatusPaid');
            if ($statut == 3 && $alreadypaid <= 0) return $langs->trans('NdfpShortStatusClosedUnpaid');
            if ($statut == 3 && $alreadypaid > 0) return $langs->trans('NdfpShortStatusClosedPaidPartially');
            if ($statut == 4) return $langs->trans('NdfpShortStatusValidating');
            if ($alreadypaid <= 0) return $langs->trans('NdfpShortStatusNotPaid');

            return $langs->trans('NdfpShortStatusStarted');
        }

        if ($mode == 2)
        {
            if ($statut == 0) return img_picto($langs->trans('NdfpStatusDraft'),'statut0').' '.$langs->trans('NdfpShortStatusDraft');
            if ($statut == 2) return img_picto($langs->trans('NdfpStatusPaid'),'statut6').' '.$langs->trans('NdfpShortStatusPaid');
            if ($statut == 3 && $alreadypaid <= 0) return img_picto($langs->trans('NdfpStatusCanceled'),'statut5').' '.$langs->trans('NdfpShortStatusCanceled');
            if ($statut == 3 && $alreadypaid > 0) return img_picto($langs->trans('NdfpStatusClosedPaidPartially'),'statut7').' '.$langs->trans('NdfpShortStatusClosedPaidPartially');
            if ($statut == 4) return img_picto($langs->trans('NdfpStatusValidating'),'statut3').' '.$langs->trans('NdfpShortStatusNotPaid');
            if ($alreadypaid <= 0) return img_picto($langs->trans('NdfpStatusNotPaid'),'statut1').' '.$langs->trans('NdfpShortStatusNotPaid');
            

            return img_picto($langs->trans('NdfpStatusStarted'),'statut3').' '.$langs->trans('NdfpShortStatusStarted');
        }

        if ($mode == 3)
        {
            if ($statut == 0) return img_picto($langs->trans('NdfpStatusDraft'),'statut0');
            if ($statut == 2) return img_picto($langs->trans('NdfpStatusPaid'),'statut6');
            if ($statut == 3 && $alreadypaid <= 0) return img_picto($langs->trans('NdfpStatusCanceled'),'statut5');
            if ($statut == 3 && $alreadypaid > 0) return img_picto($langs->trans('NdfpStatusClosedPaidPartially'),'statut7');
            if ($statut == 4) return img_picto($langs->trans('NdfpStatusValidating'),'statut3');
            if ($alreadypaid <= 0) return img_picto($langs->trans('NdfpStatusNotPaid'),'statut1');

            return img_picto($langs->trans('NdfpStatusStarted'),'statut3');

        }

        if ($mode == 4)
        {
            if ($statut == 0) return img_picto($langs->trans('NdfpStatusDraft'),'statut0').' '.$langs->trans('NdfpStatusDraft');
            if ($statut == 2) return img_picto($langs->trans('NdfpStatusPaid'),'statut6').' '.$langs->trans('NdfpStatusPaid');
            if ($statut == 3 && $alreadypaid <= 0) return img_picto($langs->trans('NdfpStatusCanceled'),'statut5').' '.$langs->trans('NdfpStatusCanceled');
            if ($statut == 3 && $alreadypaid > 0) return img_picto($langs->trans('NdfpStatusClosedPaidPartially'),'statut7').' '.$langs->trans('NdfpStatusClosedPaidPartially');
            if ($statut == 4) return img_picto($langs->trans('NdfpStatusValidating'),'statut3').' '.$langs->trans('NdfpStatusValidating');
            if ($alreadypaid <= 0) return img_picto($langs->trans('NdfpStatusNotPaid'),'statut1').' '.$langs->trans('NdfpStatusNotPaid');

            return img_picto($langs->trans('NdfpStatusStarted'),'statut3').' '.$langs->trans('NdfpStatusStarted');

        }

        if ($mode == 5)
        {
            if ($statut == 0) return $langs->trans('NdfpShortStatusDraft').' '.img_picto($langs->trans('NdfpStatusDraft'),'statut0');
            if ($statut == 2) return $langs->trans('NdfpShortStatusPaid').' '.img_picto($langs->trans('NdfpStatusPaid'),'statut6');
            if ($statut == 3 && $alreadypaid <= 0) return $langs->trans('NdfpShortStatusCanceled').' '.img_picto($langs->trans('NdfpStatusCanceled'),'statut5');
            if ($statut == 3 && $alreadypaid > 0) return $langs->trans('NdfpShortStatusClosedPaidPartially').' '.img_picto($langs->trans('NdfpStatusClosedPaidPartially'),'statut7');
            if ($statut == 4) return $langs->trans('NdfpShortStatusValidating').' '.img_picto($langs->trans('NdfpStatusValidating'),'statut3');
            if ($alreadypaid <= 0) return $langs->trans('NdfpShortStatusNotPaid').' '.img_picto($langs->trans('NdfpStatusNotPaid'),'statut1');

            return $langs->trans('NdfpShortStatusStarted').' '.img_picto($langs->trans('NdfpStatusStarted'),'statut3');
        }
    }

    /**
     *      Return clicable link of object (with eventually picto)
     *
     *      @param      withpicto       Add picto into link
     *      @param      option          Where point the link
     *      @param      max             Maxlength of ref
     *      @return     string          String with URL
     */
    function getNomUrl($withpicto = 0, $option = '', $max = 0, $short = 0)
    {
        global $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        $result = '';

        $url = dol_buildpath('/ndfp/ndfp.php', 1).'?id='.$this->id;

        if ($short) return $url;

        $picto = 'bill';

        $label = $langs->trans("ShowNdfp").': '.$this->ref;

        if ($withpicto) $result.= ('<a href="'.$url.'">'.img_object($label, $picto).'</a>');
        if ($withpicto && $withpicto != 2) $result.= ' ';
        if ($withpicto != 2) $result.= '<a href="'.$url.'">'.($max ? dol_trunc($this->ref, $max) : $this->ref).'</a>';

        return $result;
    }

    /**
     *      \brief Return tax rating label
     *      @return     string          String
     */
    function get_tax_rating_label($fk_cat, $langs)
    {
        global $langs;

        $result = '';

        $sql  = " SELECT p.label AS parent_label, c.label AS child_label";
        $sql .= " FROM ".MAIN_DB_PREFIX."c_ndfp_exp_tax_cat AS p";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_ndfp_exp_tax_cat AS c ON c.fk_parent = p.rowid";
        $sql .= " WHERE c.rowid = ".$fk_cat;

        dol_syslog("Ndfp::get_tax_rating_label sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql)
        {
            $nump = $this->db->num_rows($resql);
            if ($nump)
            {
                $obj = $this->db->fetch_object($resql);
                $result = $langs->transnoentities($obj->parent_label)." - ".$langs->transnoentities($obj->child_label);
            }
        }

        return $result;


    }

    /**
     *      \brief Return payments of note
     *      @return     array    List of payments
     */
	function get_payments()
	{
	    global $langs, $conf;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
        }

        $payments = array();
		
       // Payments already done
  
        $sql = 'SELECT np.datep as dp, np.amount as total, np.payment_number, np.rowid,';
        $sql.= ' c.code as payment_code, c.libelle as payment_label, nd.amount';
        $sql.= ' FROM '.MAIN_DB_PREFIX.'ndfp_pay_det as nd';
        $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'ndfp_pay as np ON np.rowid = nd.fk_ndfp_payment';
        $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_paiement as c ON np.fk_payment = c.id';
        $sql.= ' WHERE nd.fk_ndfp = '.$this->id;
        $sql.= ' ORDER BY dp';  

        dol_syslog("Ndfp::get_payments sql=".$sql, LOG_DEBUG);
        $result = $this->db->query($sql);

        if ($result)
        {
            $num = $this->db->num_rows($result);
    
            if ($num > 0)
            {
                $i = 0;

                while ($i < $num)
                {
                    $payment = $this->db->fetch_object($result);

                    if ($langs->trans("PaymentType".$payment->payment_code) != ("PaymentType".$payment->payment_code)){
                        $payment->label = $langs->trans("PaymentType".$payment->payment_code);
                    }else{
                        $payment->label = $payment->payment_label;
                    }

                    $payment->url = dol_buildpath('/ndfp/payment.php', 1).'?id='.$payment->rowid;
                    $payments[] = $payment;
        
                    $i++;
                }
            }
            
        }
  


        if (isset($this->linkedObjects['invoice_supplier']) && $conf->fournisseur->enabled)
        {
            if (count($this->linkedObjects['invoice_supplier']) > 0)
            {
                foreach ($this->linkedObjects['invoice_supplier'] as $invoice)
                {
                    $fk_invoice = $invoice->id;

                    $sql = 'SELECT p.datep as dp, p.num_paiement as payment_number, p.rowid, p.fk_bank,';
                    $sql.= ' c.id as paiement_type, c.code as payment_code, c.libelle as payment_label, pf.amount';
                    $sql.= ' FROM '.MAIN_DB_PREFIX.'paiementfourn as p';
                    $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_paiement as c ON p.fk_paiement = c.id';
                    $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'paiementfourn_facturefourn as pf ON pf.fk_paiementfourn = p.rowid';
                    $sql.= ' WHERE pf.fk_facturefourn = '.$fk_invoice;
                    $sql.= ' ORDER BY p.datep';		
                                                
                    dol_syslog("Ndfp::get_payments sql=".$sql, LOG_DEBUG);
                    $result = $this->db->query($sql);

                    if ($result)
                    {
                        $num = $this->db->num_rows($result);
                
                        if ($num > 0)
                        {
                            $i = 0;

                            while ($i < $num)
                            {
                                $payment = $this->db->fetch_object($result);
                                //$supplierPayment = new PaiementFourn($this->db);
                                //$supplierPayment->fetch($obj->rowid);

                                if ($langs->trans("PaymentType".$payment->payment_code) != ("PaymentType".$payment->payment_code)){
                                    $payment->label = $langs->trans("PaymentType".$payment->payment_code);
                                }else{
                                    $payment->label = $payment->payment_label;
                                }

                                $payment->url = DOL_MAIN_URL_ROOT.'/fourn/paiement/card.php?id='.$payment->rowid;
                                $payments[] = $payment;
                    
                                $i++;
                            }
                        }
                        
                    }
                    else
                    {
                        $this->error = $this->db->error()." sql=".$sql;

                    }
                }
            }	
        }
		

		return $payments;	

	}

    /**
     * 	\brief Return amount of payments already done
     *	@return		int		Amount of payment already done, <0 if KO
     */
    function get_amount_payments_done()
    {
        global $langs, $conf;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
        }
        
		$amount = 0;

    
        $sql = 'SELECT SUM(amount) AS amount';
        $sql.= ' FROM '.MAIN_DB_PREFIX.'ndfp_pay_det';
        $sql.= ' WHERE fk_ndfp = '.$this->id;

        dol_syslog("Ndfp::get_amount_payments_done sql=".$sql, LOG_DEBUG);

        $resql = $this->db->query($sql);
        if ($resql)
        {
            $obj = $this->db->fetch_object($resql);
     
            $amount += $obj->amount;
        }
   

        if (isset($this->linkedObjects['invoice_supplier']) && $conf->fournisseur->enabled)
        {
            if (count($this->linkedObjects['invoice_supplier']) > 0)
            {
                foreach ($this->linkedObjects['invoice_supplier'] as $invoice)
                {

                    $fk_invoice = $invoice->id;			
                
                    $sql = 'SELECT SUM(amount) AS amount';
                    $sql.= ' FROM '.MAIN_DB_PREFIX.'paiementfourn_facturefourn';
                    $sql.= ' WHERE fk_facturefourn = '.$fk_invoice;		
                    

                    dol_syslog("Ndfp::get_amount_payments_done sql=".$sql, LOG_DEBUG);

                    $resql=$this->db->query($sql);
                    if ($resql)
                    {
                        $obj = $this->db->fetch_object($resql);
                

                        $amount += $obj->amount;
                    }
                    else
                    {
                        $this->error = $this->db->error()." sql=".$sql;
                        //return -1;
                    }
                }
            }

        }
		

       	return $amount;
    }


    function is_supplier_invoice_paid()
    {
        global $langs, $conf;

        if (!$this->id)
        {
            $this->error = $langs->trans('NdfpIdIsMissing');
        }
        
   
        $amount = 0;
        if (isset($this->linkedObjects['invoice_supplier']) && $conf->fournisseur->enabled)
        {
            if (count($this->linkedObjects['invoice_supplier']) > 0)
            {
                foreach ($this->linkedObjects['invoice_supplier'] as $invoice)
                {

                    if ($invoice->paye)
                    {
                        $amount += $invoice->total_ttc;
                    }
                }
            }
        }
        
        $paid = ($amount == $this->total_ttc ? true : false);

        return $paid;

    }

     /**
     *  \brief Return if a note can be deleted
     *	Rule is:
     *  If note a definitive ref, is last, without payment -> yes
     *  If note is draft and has a temporary ref -> yes
     *  @return    int         <0 if KO, 0=no, 1=yes
     */
    function is_erasable()
    {
        global $conf;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        // on verifie si la note est en numerotation provisoire
        $ref = substr($this->ref, 1, 4);

        // If not a draft note and not temporary invoice
        if ($ref != 'PROV')
        {
            $maxref = $this->get_next_num_ref($this->fk_soc, 'last');
            $payment = $this->get_amount_payments_done();

            if ($maxref == $this->ref && $payment == 0)
            {
                return 1;
            }
        }
        else if ($this->statut == 0 && $ref == 'PROV')
        {
            return 1;
        }

        return 0;
    }

     /**
     *  \brief Send validation request to validation group
     *  @return    int         <0 if KO, 1 if OK
     */
	function ask_valid($user)
	{
		global $conf, $langs;
		
        $users = array();
        if ($user->fk_user > 0)
        {
            $manager = new User($this->db);
            $manager->fetch($user->fk_user);

            $users[$manager->id] = $manager;
        }
        else
        {
            $gid = $conf->global->NDFP_VALIDATION_GID ? $conf->global->NDFP_VALIDATION_GID : 0;

            // Get users from this group
            $group = new UserGroup($this->db);
            $group->fetch($gid);
            
            
            $users = $group->members;           
        }

		
		if (count($users) > 0)
		{
			foreach ($users as $uid => $u)
			{


                $now = dol_now();
                $hash = dol_hash($this->ref.$now); // MD5

                $val = new NdfpVal($this->db);
                $val->fk_user = $user->id;
                $val->fk_ndfp = $this->id;
                $val->hash = $hash;
                $val->note = '';
                $val->create($user);

                // Create links
                $accept = dol_buildpath('/ndfp/validation.php', 2).'?action=accept&hash='.$hash;
                $refuse = dol_buildpath('/ndfp/validation.php', 2).'?action=refuse&hash='.$hash;

                // Attached file
                $ref = dol_sanitizeFileName($this->ref);
                $filename = $ref . '.pdf';
                $file = $conf->ndfp->dir_output . '/' . $ref . '/' . $filename;

                if (! is_readable($file))
                {
                    $result = $this->generate_pdf($user);
                }

                $arr_file = array();
                $arr_mime = array();
                $arr_name = array();

                $arr_file[] = $file;
                $arr_mime[] = dol_mimetype($filename);
                $arr_name[] = $filename;

                $substit = array(
                    '__USER__' => $user->getFullName($langs),
                    '__REF__' => $this->ref,
                    '__LINK_OK__' => $accept,
                    '__LINK_KO__' => $refuse,
                    '__SOCIETY__' => $conf->global->MAIN_INFO_SOCIETE_NOM
                );

                $message = $langs->transnoentities('ValidationBody');
                $subject = $langs->transnoentities('ValidationSubject');

                $subject = make_substitutions($subject, $substit);           
                $message = make_substitutions($message, $substit);
                
                $message = str_replace('\n',"<br />", $message);

				// Get email
				$email = $u->email;

				$from = $user->getFullName($langs);
				$from = $from .'<'.$user->email.'>';
				
				$to = $u->getFullName($langs);
				$to = $to .'<'.$email.'>';
				
				$mail = new CMailFile($subject, $to, $from, $message,  $arr_file, $arr_mime, $arr_name, '', '', 0, 1);
				$mail->sendfile();
			}
			
			$this->set_validating($user);
			
			$this->error = $langs->trans('ValidationSent');
			return 1;
		}
		else
		{
			$this->error = $langs->trans('NoValidationGroup');
			return -1;
		}
	
	}


     /**
     *  \brief Check if there is at least one multiline
     *  @return    int         <0 if KO, 1 if OK
     */
	function any_multilines()
	{
        global $conf;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }
        
        $lines = $this->lines;
        $milestone = false;
        
        foreach ($lines as $line)
        {
        	if ($line->milestone)
        	{
        		$milestone = true;
        	}
        }	
        
        return $milestone;
	
	}	
     /**
     *  \brief Return list of available actions (displayed as buttons)
     *
     *  @param     action       Current action
     *  @return    array        List of buttons
     */
    function get_available_actions($action)
    {
        global $user, $langs, $conf;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

		$gid = $conf->global->NDFP_VALIDATION_GID ? $conf->global->NDFP_VALIDATION_GID : 0;

		// Get users from this group
        $validator = $this->check_user_rights($user, 'validate') > 0 ? true : false;
        // Check user
        $childs = $user->get_children();
        

        if (count($childs))
        {
            foreach ($childs as $child)
            {
                if ($child->id == $this->fk_user_author)
                {
                    $validator = true;
                }
            }           
        }

        if (!$validator && $gid > 0)
        {
            $group = new UserGroup($this->db);
            $groups = $group->listGroupsForUser($user->id);

            if (count($groups))
            {
                foreach ($groups as $id => $g)
                {
                    if ($id == $gid)
                    {
                        $validator = true;
                    }   
                }                
            }     
        }
		
        $alreadyPaid  = $this->get_amount_payments_done();
        $supplierInvoiceIsPaid = $this->is_supplier_invoice_paid();

        $remainToPay = price2num($this->total_ttc - $alreadyPaid, 'MT');


        $buttons = array();
        if ($user->societe_id != 0 && ($action == 'presend' || $action == 'valid' || $action == 'editline')){
            return $buttons;
        }

        // Edit a credit note already validated
        if (($this->statut == 1 || $this->statut == 4) && $remainToPay == $this->total_ttc){
            if ($this->check_user_rights($user, 'create') > 0){
                $buttons[] = '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$this->id.'&amp;action=modif">'.$langs->trans('Modify').'</a>';
            }else{
                $buttons[] = '<span class="butActionRefused" title="'.$langs->trans("NotEnoughPermissions").'">'.$langs->trans('Modify').'</span>';
            }
        }

        // Reopen a standard paid expense
        if (($this->statut == 2 || $this->statut == 3)){				// A paid expense
            if ($this->check_user_rights($user, 'create') > 0){
                $buttons[] =  '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$this->id.'&amp;action=reopen">'.$langs->trans('ReOpen').'</a>';
            }else{
                $buttons[] = '<span class="butActionRefused" title="'.$langs->trans("NotEnoughPermissions").'">'.$langs->trans('ReOpen').'</a>';
            }

        }

        // Validate
        $displayButton = false;
        if ($this->check_user_rights($user, 'validate') > 0 && $validator && $this->statut == 4)
        {
        	$displayButton = true;
        }
        
        if ($this->check_user_rights($user, 'validate') > 0 && $validator && $this->statut == 0 && $this->fk_user_author == $user->id)
        {
        	$displayButton = true;
        }
        
        if ($this->check_user_rights($user, 'validate') > 0 && $validator && $this->statut == 0 && $user->admin)
        {
        	$displayButton = true;
        }
                        
        if (($this->statut == 0 || $this->statut == 4) && count($this->lines) > 0 /* && $this->total_ttc >= 0*/ ){
            if ($displayButton){
                $buttons[] = '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$this->id.'&amp;action=valid">'.$langs->trans('Validate').'</a>';
            }else{
            	if ($this->statut == 4)
            	{
                	$buttons[] = '<span class="butActionRefused" title="'.$langs->trans("DisabledBecauseUnderValidation").'">'.$langs->trans('AskValidation').'</span>';
                }
                else
                {
                	$buttons[] = '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$this->id.'&amp;action=ask_valid">'.$langs->trans('AskValidation').'</a>';                
                }
                
            }

        }

        // Send by mail
        if ($this->statut == 1 || $this->statut == 2){
            if ($this->check_user_rights($user, 'send') > 0){
                $buttons[] = '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$this->id.'&amp;action=presend&amp;mode=init">'.$langs->trans('SendByMail').'</a>';
            }else{
                $buttons[] = '<a class="butActionRefused" href="#">'.$langs->trans('SendByMail').'</a>';
            }
        }

		
        // Create payment
		if (isset($this->linkedObjects['invoice_supplier']) && count($this->linkedObjects['invoice_supplier']) > 0 && $this->statut == 1)
		{
			$buttons[] = '<span class="butActionRefused" title="'.$langs->trans("DisabledBecauseBillAlreadyExist").'">'.$langs->trans('DoPayment').'</span>';
		}
		else
		{
			if ($this->statut == 1)
			{
				if ($this->check_user_rights($user, 'payment') > 0)
				{
					if ($remainToPay == 0)
					{
					  $buttons[] = '<span class="butActionRefused" title="'.$langs->trans("DisabledBecauseRemainderToPayIsZero").'">'.$langs->trans('DoPayment').'</span>';
					}
					else
					{
					  $buttons[] = '<a class="butAction" href="'.dol_buildpath('/ndfp/payment.php', 1).'?fk_user='.$this->fk_user.'&amp;action=create">'.$langs->trans('DoPayment').'</a>';
					}
				}
				else
				{
					$buttons[] = '<a class="butActionRefused" href="#">'.$langs->trans('DoPayment').'</a>';
				}

			}
		}

        // Classify paid
        if ($this->statut == 1 && ($remainToPay == 0 || $supplierInvoiceIsPaid))
        {
            if ($this->check_user_rights($user, 'create') > 0){
                $buttons[] = '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$this->id.'&amp;action=paid">'.$langs->trans('ClassifyPaid').'</a>';
            }else{
                $buttons[] = '<a class="butActionRefused" href="#">'.$langs->trans('ClassifyPaid').'</a>';
            }
        }

        // Classify 'closed not completely paid' (possible si validee et pas encore classee payee)
        if ($this->statut == 1 && $alreadyPaid == 0)
        {
            if ($this->check_user_rights($user, 'create') > 0){
                if ($alreadyPaid > 0){
                    $buttons[] =  '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$this->id.'&amp;action=canceled">'.$langs->trans('ClassifyPaidPartially').'</a>';
                }else{
                    $buttons[] =  '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$this->id.'&amp;action=canceled">'.$langs->trans('ClassifyCanceled').'</a>';
                }
            }else{
                if ($alreadyPaid > 0){
                    $buttons[] =  '<a class="butActionRefused" href="#">'.$langs->trans('ClassifyPaidPartially').'</a>';
                }else{
                    $buttons[] =  '<a class="butActionRefused" href="#">'.$langs->trans('ClassifyCanceled').'</a>';
                }
            }
        }


        // Clone
        if ($this->check_user_rights($user, 'create') > 0){
            $buttons[] = '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$this->id.'&amp;action=clone">'.$langs->trans("ToClone").'</a>';
        }else{
            $buttons[] = '<a class="butActionRefused" href="#">'.$langs->trans('ToClone').'</a>';
        }

        // Bill
        if ($conf->facture->enabled && $this->statut == 1)
        {
            if ($this->check_user_rights($user, 'clientbill') > 0)
            {
                
                if (isset($this->linkedObjects['facture']) && count($this->linkedObjects['facture']) > 0)
                {
                    $buttons[] = '<span class="butActionRefused" title="'.$langs->trans("DisabledBecauseBillAlreadyExist").'">'.$langs->trans('ClientBillNdfp').'</span>';
                }
                else
                {
                    $buttons[] = '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$this->id.'&amp;action=clientbill">'.$langs->trans('ClientBillNdfp').'</a>';              
                }                         
            }
            else
            {
                $buttons[] = '<span class="butActionRefused" title="'.$langs->trans("NotEnoughPermissions").'">'.$langs->trans('ClientBillNdfp').'</span>';
            }
        }

		// Bill
		if ($conf->fournisseur->enabled && $this->statut == 1)
		{
            if ($this->check_user_rights($user, 'bill') > 0)
            {
            	
            	if (isset($this->linkedObjects['invoice_supplier']) && count($this->linkedObjects['invoice_supplier']) > 0)
            	{
					$buttons[] = '<span class="butActionRefused" title="'.$langs->trans("DisabledBecauseBillAlreadyExist").'">'.$langs->trans('BillNdfp').'</span>';
            	}
            	else
            	{
					if ($remainToPay == 0)
					{
					  $buttons[] = '<span class="butActionRefused" title="'.$langs->trans("DisabledBecauseRemainderToPayIsZero").'">'.$langs->trans('BillNdfp').'</span>';
					}
					else if ($alreadyPaid != 0)
					{
						$buttons[] = '<span class="butActionRefused" title="'.$langs->trans("DisabledBecausePayments").'">'.$langs->trans('BillNdfp').'</span>';
					}
					else
					{
					  $buttons[] = '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$this->id.'&amp;action=bill">'.$langs->trans('BillNdfp').'</a>';
					}            	
            	}                         
            }
            else
            {
                $buttons[] = '<span class="butActionRefused" title="'.$langs->trans("NotEnoughPermissions").'">'.$langs->trans('BillNdfp').'</span>';
            }
        }
        
        // Delete
        if ($this->check_user_rights($user, 'delete') > 0)
        {
            if (!$this->is_erasable())
            {
              $buttons[] =  '<a class="butActionRefused" href="#" title="'.$langs->trans("DisabledBecauseNotErasable").'">'.$langs->trans('Delete').'</a>';
            }
            elseif ($alreadyPaid > 0)
            {
              $buttons[] =  '<a class="butActionRefused" href="#" title="'.$langs->trans("DisabledBecausePayments").'">'.$langs->trans('Delete').'</a>';
            }
            else
            {
              $buttons[] =  '<a class="butActionDelete" href="'.$_SERVER['PHP_SELF'].'?id='.$this->id.'&amp;action=delete">'.$langs->trans('Delete').'</a>';
            }
        }else{
            $buttons[] = '<a class="butActionRefused" href="#">'.$langs->trans('Delete').'</a>';
        }

        return $buttons;
    }

    /**
     *		\brief		Determine if tax lines can be added to the note
     *		\param		action		Current action
     *		\param		int			0 if KO, 1 if OK
     */
    function can_add_tax($action)
    {
        global $langs;

        $add = 0;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

		$any_multilines = $this->any_multilines();
		
        if ($this->statut == 0 && $action <> 'valid' && $action <> 'editline' && $action <> 'edittaxline' && $action <> 'edittvaline' && $any_multilines){
            $add = 1;
        }

        return $add;
    }
    

    function dellink($user)
    {

        $dellinkid = GETPOST('dellinkid','int');

        if ($dellinkid > 0)
        {
            $result = $this->deleteObjectLinked(0, '', 0, '', $dellinkid);

     
            if ($result)
            {
                $this->fetchObjectLinked($this->id, $this->element, '', 'invoice_supplier');
            
                return 1;

            }
            else
            {
                return -1;
            }

        }

        return 1;

    }
    /**
     *		\brief		Determine if vat lines can be added to the note
     *		\param		action		Current action
     *		\param		int			0 if KO, 1 if OK
     */
    function can_add_tva($action)
    {
        global $langs;

        $add = 0;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

		$any_multilines = $this->any_multilines();
		
        if ($this->statut == 0 && $action <> 'valid' && $action <> 'editline' && $action <> 'edittaxline' && $action <> 'edittvaline' && $any_multilines){
            $add = 1;
        }

        return $add;
    }
    
    /**
     *		\brief		Determine if expenses can be added to the note
     *		\param		action		Current action
     *		\param		int			0 if KO, 1 if OK
     */
    function can_add_expenses($action)
    {
        global $langs;

        $add = 0;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

        if ($this->statut == 0 && $action <> 'valid' && $action <> 'editline' && $action <> 'edittaxline' && $action <> 'edittvaline'){
            $add = 1;
        }

        return $add;
    }


     /**
     *  \brief Get expense
     *
     *  @param     fk_exp            Id of the expense
     *  @return    object          Object of the expense
     */
    function get_expense($fk_exp, $fk_tva = 0)
    {
        global $langs;

        // Check parameters
        if (empty($fk_exp) || $fk_exp < 0)
        {
			$this->error = $langs->trans('ErrorBadParameterExpense');
			return -1;
        }

        $sql  = "SELECT e.code, e.fk_tva FROM ".MAIN_DB_PREFIX."c_ndfp_exp AS e";
        $sql.= " WHERE e.rowid = ".$fk_exp;

        dol_syslog("Ndfp::get_expense sql=".$sql, LOG_DEBUG);

        $result = $this->db->query($sql);

        if ($result)
        {
            $num = $this->db->num_rows($result);
            if ($num)
            {
                $obj = $this->db->fetch_object($result);
            
                return $obj;

            }
            else
            {
                $this->error = $langs->trans('ErrorBadParameterExpense');
                return -1;
            }

        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            return -1;
        }
    }
    

    /**
     *  \brief Add a tax line to an expense
     *
     *  @param     user             User who adds
     *  @return    int              <0 if KO, id the line added if OK
     */
    function addtaxline($user)
    {
        global $conf, $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }


        $fk_ndfp_det     = GETPOST('fk_ndfp_det') ? GETPOST('fk_ndfp_det') : 0;		
		$price_base_type = GETPOST('price_base_type', 'alpha') ? GETPOST('price_base_type', 'alpha') : 'HT';
		
		
        $amount_tax  = GETPOST('amount_tax') ? GETPOST('amount_tax') : 0;
        $amount_tax  = price2num($amount_tax);
                

		$total_ttc_cur  = $amount_tax;
		$total_ht_cur  = $amount_tax;
		
        $fk_user_author = $user->id;
        $fk_ndfp = $this->id;

        if ($this->statut != 0)
        {
            $this->error = $langs('CanNotModifyLinesOnNonDraftNote');
            return -1;
        }

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }

		
        if ($this->statut == 0)
        {
        
			$line = new NdfpLine($this->db);
			$result = $line->fetch($fk_ndfp_det);
			
            if ($result < 0)
            {
                return -1;
            }
            
            $rate =  $line->rate;       
			
			
			// Apply rate
			$total_ht = $total_ht_cur * $rate;
			$total_ttc = $total_ttc_cur * $rate;
							

            // Insert line
            $this->line  = new NdfpTaxLine($this->db);
            $this->line->fk_ndfp = $this->id;
            $this->line->fk_ndfp_det = $fk_ndfp_det;
            

            $this->line->fk_user_author = $fk_user_author;

			$this->line->price_base_type = $price_base_type;
            $this->line->total_ht = $total_ht;
            $this->line->total_ht_cur = $total_ht_cur;
            $this->line->total_tva = 0;
            $this->line->total_ttc = $total_ttc;
			$this->line->total_ttc_cur = $total_ttc_cur;
			
            $result = $this->line->insert();

            if ($result > 0)
            {
                $result = $this->update_totals();

                if ($result > 0)
                {
                    $this->fetch_lines();

                    $this->generate_pdf($user);
               	    $this->error = $langs->trans('TaxAdded');

                    return $this->line->rowid;
                }
                else
                {
                    $this->error = $this->db->error()." sql=".$sql;

                    return -1;
                }
            }
            else
            {
                $this->error = $this->line->error;

                return -2;
            }
        }
    }
        
     /**
     *  \brief Add a VAT line on an expense
     *
     *  @param     user             User who adds
     *  @return    int              <0 if KO, id the line added if OK
     */
    function addtvaline($user)
    {
        global $conf, $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }


        $fk_tva     = GETPOST('fk_tva_det') ? GETPOST('fk_tva_det') : 0;
        $total_tva     = GETPOST('total_tva') ? GETPOST('total_tva') : 0;
		$fk_ndfp_tax_det     = GETPOST('fk_ndfp_tax_det') ? GETPOST('fk_ndfp_tax_det') : 0;
		
        $fk_user_author = $user->id;
        $fk_ndfp = $this->id;

        if ($this->statut != 0)
        {
            $this->error = $langs('CanNotModifyLinesOnNonDraftNote');
            return -1;
        }

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }


        if ($this->statut == 0)
        {
           

			$line = new NdfpTaxLine($this->db);
			$result = $line->fetch($fk_ndfp_tax_det);
			
            if ($result < 0)
            {
                return -1;
            }


			$tva = price2num($fk_tva);
			$total_ht = price2num($line->total_ht);
			$total_ttc = price2num($line->total_ttc);
		
			// Compute total TVA
            if ($line->price_base_type == 'HT')
            {
                $total_tva = $total_ht * ($tva/100);
            }
            else
            {
                $ancien_taux = $line->get_all_tva();
                $nouveau_taux = $ancien_taux + $tva;

                $total_ht = $total_ttc/(1 + ($nouveau_taux/100));
                $total_ht = price2num(price($total_ht), 'MT');

                $tva_lines = $line->get_tva_lines();
                $total_tva = 0;
                if (count($tva_lines) > 0)
                {
                    foreach ($tva_lines as $tva_line)
                    {           
                        $tva  = price2num($tva_line->tva_tx);
                        $tva_ligne = $total_ht * ($tva/100);
                        $tva_ligne = price2num(price($tva_ligne), 'MT');
                        
                        $tline  = new NdfpTvaLine($this->db);
                        $tline->fetch($tva_line->rowid);
                        $tline->total_tva = $tva_ligne;
                        $tline->fk_tva = $tva_line->tva_tx;
                        $tline->update();

                        $total_tva += $tva_ligne;
                    }                   
                }

                $total_tva = $total_ttc - $total_ht - $total_tva; 
            }
							
			$total_tva = price2num($total_tva, 'MT');
            
            
            // Insert TVA line
			$this->line = new NdfpTvaLine($this->db);
			$this->line->fk_ndfp = $this->id;
			$this->line->fk_ndfp_det = $line->fk_ndfp_det;
			$this->line->fk_ndfp_tax_det = $fk_ndfp_tax_det;
			$this->line->fk_tva = $fk_tva;
			$this->line->total_tva = $total_tva;
			
            $result = $this->line->insert();

            if ($result > 0)
            {            	
                $result = $this->update_totals();

				
                if ($result > 0)
                {
                    $this->fetch_lines();

                    $this->generate_pdf($user);
               	    $this->error = $langs->trans('TvaAdded');

                    return $this->line->rowid;
                }
                else
                {
                    $this->error = $this->db->error()." sql=".$sql;

                    return -1;
                }
            }
            else
            {
                $this->error = $this->line->error;

                return -2;
            }
        }
    }
        
     /**
     *  \brief Add an expense to the note
     *
     *  @param     user             User who adds
     *  @return    int              <0 if KO, id the line added if OK
     */
    function addline($user)
    {
        global $conf, $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

		dol_syslog("Ndfp::addline data=".serialize($_POST), LOG_DEBUG);
		
		
        $dated = dol_mktime($_POST['eshour'], $_POST['esmin'], $_POST['essec'], $_POST['esmonth'], $_POST['esday'], $_POST['esyear']);
        $datef = null;//dol_mktime($_POST['eehour'], $_POST['eemin'], $_POST['eesec'], $_POST['eemonth'], $_POST['eeday'], $_POST['eeyear']);

        $qty        = GETPOST('qty');
        $rate       = GETPOST('rate') ? GETPOST('rate') : 1;
        $currency   = GETPOST('currency') ? GETPOST('currency') : $this->cur_iso;
        $fk_exp     = GETPOST('fk_exp');
        $fk_tva     = GETPOST('fk_tva') ? GETPOST('fk_tva') : 0;
        $fk_cat     = GETPOST('fk_cat') ? GETPOST('fk_cat') : 0;
        $previous_exp     = GETPOST('previous_exp') ? GETPOST('previous_exp') : 0;

        //$label      = GETPOST('label');
		
		//$price_base_type = GETPOST('price_base_type', 'alpha') ? GETPOST('price_base_type', 'alpha') : 'HT';
		$milestone = GETPOST('milestone', 'int') ? true : false;
		
		$comment      = GETPOST('comment');
		$ref_ext      = GETPOST('ref_ext');
		
		
        $total_ttc  = GETPOST('total_ttc') ? GETPOST('total_ttc') : 0;
        $total_ttc  = price2num($total_ttc);

        $total_ht  = GETPOST('total_ht') ? GETPOST('total_ht') : 0;
        $total_ht  = price2num($total_ht);
                
		$total_ttc_cur  = $total_ttc;
		$total_ht_cur  = $total_ht;
		
        $fk_user_author = $user->id;
        $fk_ndfp = $this->id;

        if ($this->statut != 0)
        {
            $this->error = $langs->trans('CanNotModifyLinesOnNonDraftNote');
            return -1;
        }

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }


        if ($this->statut == 0)
        {
            $expobj = $this->get_expense($fk_exp);
  

            if ($expobj < 0)
            {
                return -1;
            }

            $tva  = price2num($fk_tva);//price2num($tvaobj->taux);


            $km_exp = ($expobj->code == 'EX_KME') ? true : false;
			
            if ($km_exp)
            {
            	$total_ttc = $this->compute_travel_fees($qty, $fk_cat, $previous_exp);
                $total_ht = $total_ttc/(1 + $tva/100);  
            }

			if ($total_ttc > 0 && $total_ht == 0)//Button fix
			{
				$total_ht = $total_ttc/(1 + $tva/100); 
				$total_ht = price2num(price($total_ht), 'MT'); 
			}

			if ($total_ttc == 0 && $total_ht > 0)//Button fix
			{
				$total_ttc = $total_ht * (1 + $tva/100);
				$total_ttc = price2num(price($total_ttc), 'MT'); 
			}
			
			$total_ht_cur  = $total_ht;
			$total_ttc_cur = $total_ttc;

			// Apply rate		
			$total_ttc = $total_ttc_cur * $rate;
			$total_ht = $total_ht_cur * $rate;	
															
			if ($milestone)
			{
				$total_ttc = 0;
				$total_ht = 0;
				$total_ttc_cur = 0;
				$total_ht_cur = 0;				
			}

			// Compute total TVA
			$total_tva = $total_ttc - $total_ht;						
			           			

            // Insert line
            $this->line  = new NdfpLine($this->db);
            $this->line->fk_ndfp = $this->id;
            
            $this->line->qty = $qty;

            $this->line->rate = $rate;
            $this->line->cur_iso = $currency;
            $this->line->dated = $dated;
            $this->line->datef = $datef;

            $this->line->fk_user_author = $fk_user_author;
            $this->line->fk_tva = $fk_tva;
            $this->line->fk_exp = $fk_exp;

            $this->line->fk_cat = $fk_cat;
            $this->line->previous_exp = $previous_exp;

			//$this->line->price_base_type = $price_base_type;
            $this->line->total_ht = $total_ht;
            $this->line->total_ht_cur = $total_ht_cur;
            $this->line->total_tva = $total_tva;
            $this->line->total_ttc = $total_ttc;
			$this->line->total_ttc_cur = $total_ttc_cur;
			
			$this->line->milestone = $milestone;
			$this->line->comment = $comment;
			$this->line->ref_ext = $ref_ext;
			
			
            $result = $this->line->insert();
			
            if ($result > 0)
            {
                //$this->update_lines();
            	$result = $this->update_totals();
				
                if ($result > 0)
                {
                    $this->fetch_lines();

                    $this->generate_pdf($user);
               	    $this->error = $langs->trans('ExpAdded');

                    return $this->line->rowid;
                }
                else
                {
                    $this->error = $this->db->error()." sql=".$sql;

                    return -1;
                }
            }
            else
            {
                $this->error = $this->line->error;					
                return -2;
            }
        }
    }
     /**
     *  \brief Update an expense of a note
     *
     *  @param     user           User who updates
     *  @return    int              <0 if KO, id the line added if OK
     */
    function updateline($user)
    {
        global $conf, $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }
		
		dol_syslog("updateline data=".serialize($_POST), LOG_DEBUG);
		
        $dated = dol_mktime($_POST['eshour'], $_POST['esmin'], $_POST['essec'], $_POST['esmonth'], $_POST['esday'], $_POST['esyear']);
        $datef = null;//dol_mktime($_POST['eehour'], $_POST['eemin'], $_POST['eesec'], $_POST['eemonth'], $_POST['eeday'], $_POST['eeyear']);


        $fk_ndfp    = $this->id;

        $lineid     = GETPOST('lineid');
        $qty        = GETPOST('qty');
        $rate       = GETPOST('rate') ? GETPOST('rate') : 1;
        $currency   = GETPOST('currency') ? GETPOST('currency') : $this->cur_iso;        
        $fk_exp     = GETPOST('fk_exp');
        $fk_tva     = GETPOST('fk_tva') ? GETPOST('fk_tva') : 0;
        $fk_cat     = GETPOST('fk_cat') ? GETPOST('fk_cat') : 0;
        $currency   = GETPOST('currency');
        $previous_exp     = GETPOST('previous_exp') ? GETPOST('previous_exp') : 0;
        //$price_base_type = GETPOST('price_base_type', 'alpha') ? GETPOST('price_base_type', 'alpha') : 'HT';
        
        $milestone = GETPOST('milestone', 'int') ? true : false;
        
        $total_ttc  = GETPOST('total_ttc') ? GETPOST('total_ttc') : 0;
        $total_ttc  = price2num($total_ttc);

        $total_ht  = GETPOST('total_ht') ? GETPOST('total_ht') : 0;
        $total_ht  = price2num($total_ht);
                
		$total_ttc_cur  = $total_ttc;
		$total_ht_cur  = $total_ht;
		
		$comment      = GETPOST('comment');
		$ref_ext      = GETPOST('ref_ext');
		
        if ($this->statut != 0)
        {
            $this->error = $langs('CanNotModifyLinesOnNonDraftNote');
            return -1;
        }

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }

        //Fetch line
        $this->line  = new NdfpLine($this->db);


        $result = $this->line->fetch($lineid);

        if ($result < 0)
        {
            $this->error = $langs->trans('LineDoesNotExist');
            return -1;
        }


        if ($this->statut == 0)
        {

            $expobj = $this->get_expense($fk_exp);


            if ($expobj < 0)
            {
                return -1;
            }

            $tva  = price2num($fk_tva);


            $km_exp = ($expobj->code == 'EX_KME') ? true : false;
            
            if ($km_exp)
            {
                $total_ttc = $this->compute_travel_fees($qty, $fk_cat, $previous_exp);
                $total_ht = $total_ttc/(1 + $tva/100);  
            }

			if ($total_ttc > 0 && $total_ht == 0)//Button fix
			{
				$total_ht = $total_ttc/(1 + $tva/100); 
				$total_ht = price2num(price($total_ht), 'MT'); 
			}

			if ($total_ttc == 0 && $total_ht > 0)//Button fix
			{
				$total_ttc = $total_ht * (1 + $tva/100);
				$total_ttc = price2num(price($total_ttc), 'MT'); 
			}
						
			$total_ht_cur  = $total_ht;
			$total_ttc_cur = $total_ttc;

			// Apply rate		
			$total_ttc = $total_ttc_cur * $rate;
			$total_ht = $total_ht_cur * $rate;	

			if ($this->line->milestone)
			{
				$total_ttc = 0;
				$total_ht = 0;
				$total_ttc_cur = 0;
				$total_ht_cur = 0;				
			}
			
			$total_tva = $total_ttc - $total_ht;
			
            // Update line
            $this->line->qty = $qty;
            $this->line->rate = $rate;
            $this->line->cur_iso = $currency;
            $this->line->dated = $dated;
            $this->line->datef = $datef;

            $this->line->fk_tva = $fk_tva;
            $this->line->fk_exp = $fk_exp;
            $this->line->fk_cat = $fk_cat;

            $this->line->total_ht = $total_ht;
            $this->line->total_ht_cur = $total_ht_cur;
            $this->line->total_tva = $total_tva;
            $this->line->total_ttc = $total_ttc;
			$this->line->total_ttc_cur = $total_ttc_cur;
			
			$this->line->comment = $comment;
			$this->line->ref_ext = $ref_ext;
			
            $result = $this->line->update();

            if ($result > 0)
            {
                //$this->update_lines();
				$result = $this->update_totals();
				if ($result < 0)
				{
					return -1;
				}
				
				$this->update_totals();
				
				if ($result > 0)
				{
					

					$this->fetch_lines();
													
					$this->generate_pdf($user);

					$this->error = $langs->trans('ExpUpdated');
					return $this->line->rowid;
				}
				else
				{
					$this->error = $this->line->error;

					return -1;
				}
            }
            else
            {
                $this->error = $this->line->error;

                return -2;
            }
        }
    }

     /**
     *  \brief Update a tax line
     *
     *  @param     user           User who updates
     *  @return    int              <0 if KO, id the line added if OK
     */
    function updatetaxline($user)
    {
        global $conf, $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

		$lineid     = GETPOST('lineid');
			
 
        $amount_tax  = GETPOST('amount_tax') ? GETPOST('amount_tax') : 0;
        $amount_tax  = price2num($amount_tax);
		
        $fk_user_author = $user->id;
        $fk_ndfp = $this->id;
		
        if ($this->statut != 0)
        {
            $this->error = $langs('CanNotModifyLinesOnNonDraftNote');
            return -1;
        }

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }

        //Fetch line
        $this->line  = new NdfpTaxLine($this->db);


        $result = $this->line->fetch($lineid);

        if ($result < 0)
        {
            $this->error = $langs->trans('LineDoesNotExist');
            return -1;
        }


        if ($this->statut == 0)
        {

			$line = new NdfpLine($this->db);
			$result = $line->fetch($this->line->fk_ndfp_det);

            
            if ($result < 0)
            {
                return -1;
            }
            			            
          	$rate =  $line->rate;       
			
			$total_ht_cur = $amount_tax;	
			$total_ttc_cur = $amount_tax;

            $total_ht = $total_ht_cur;
            $total_ttc = $total_ttc_cur;

            if ($this->line->price_base_type != 'HT')
            {
                $tva = $this->line->get_all_tva();

                $total_ht = $total_ttc/(1 + ($tva/100));               
            }

			// Apply rate
			$total_ht = $total_ht * $rate;
			$total_ttc = $total_ttc * $rate;
							
            $total_ht = price2num(price($total_ht), 'MT');
            $total_ttc = price2num(price($total_ttc), 'MT');

            // Update line
            $this->line->total_ht = $total_ht;
            $this->line->total_ht_cur = $total_ht_cur;
            $this->line->total_tva = 0;
            $this->line->total_ttc = $total_ttc;
			$this->line->total_ttc_cur = $total_ttc_cur;
			
            $result = $this->line->update();

            if ($result > 0)
            {
                if ($this->line->price_base_type != 'HT')
                {
                    $tva_lines = $this->line->get_tva_lines();
                    if (count($tva_lines) > 0)
                    {
                        foreach ($tva_lines as $tva_line)
                        {           
                            $tva  = price2num($tva_line->tva_tx);

                            $tva_ligne = $total_ht * ($tva/100);
                            $tva_ligne = price2num(price($tva_ligne), 'MT');

                            $tline  = new NdfpTvaLine($this->db);
                            $tline->fetch($tva_line->rowid);
                            $tline->total_tva = $tva_ligne;
                            $tline->fk_tva = $tva_line->tva_tx;
                            $tline->update();               
                        }                   
                    }
                }

                $result = $this->update_totals();

                if ($result > 0)
                {
                    $this->fetch_lines();

                    $this->generate_pdf($user);
               	    $this->error = $langs->trans('TaxUpdated');

                    return $this->line->rowid;
                }
                else
                {
                    $this->error = $this->db->error()." sql=".$sql;

                    return -1;
                }
            }
            else
            {
                $this->error = $this->line->error;

                return -2;
            }


        }
    }
    
     /**
     *  \brief Update a VAT line
     *
     *  @param     user           User who updates
     *  @return    int              <0 if KO, id the line added if OK
     */
    function updatetvaline($user)
    {
        global $conf, $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

		$lineid     = GETPOST('lineid');
		$total_tva     = GETPOST('total_tva') ? GETPOST('total_tva') : 0;
        $fk_tva     = GETPOST('fk_tva_det') ? GETPOST('fk_tva_det') : 0;
		
        $fk_user_author = $user->id;
        $fk_ndfp = $this->id;
		
        if ($this->statut != 0)
        {
            $this->error = $langs('CanNotModifyLinesOnNonDraftNote');
            return -1;
        }

        $result = $this->check_user_rights($user, 'create');
        if ($result < 0)
        {
            return -1;
        }

        //Fetch line
        $this->line  = new NdfpTvaLine($this->db);


        $result = $this->line->fetch($lineid);

        if ($result < 0)
        {
            $this->error = $langs->trans('LineDoesNotExist');
            return -1;
        }


        if ($this->statut == 0)
        {

			$line = new NdfpTaxLine($this->db);
			$result = $line->fetch($this->line->fk_ndfp_tax_det);
			
            

            if ($result < 0)
            {
                return -1;
            }

			

			$total_ht = price2num($line->total_ht);
			$total_ttc = price2num($line->total_ttc);
		    $tva = price2num($fk_tva);

            

			// Compute total TVA
            if ($line->price_base_type == 'HT')
            {
                $total_tva = $total_ht * ($tva/100);
            }
            else
            {
                $ancien_taux = $line->get_all_tva($lineid);
                $nouveau_taux = $ancien_taux + $tva;

                $total_ht = $total_ttc/(1 + ($nouveau_taux/100));
                $total_ht = price2num(price($total_ht), 'MT');

                $tva_lines = $line->get_tva_lines();
                $total_tva = 0;
                if (count($tva_lines) > 0)
                {
                    foreach ($tva_lines as $tva_line)
                    {           
                        $tva  = price2num($tva_line->tva_tx);
                        if ($lineid != $tva_line->rowid)
                        {
                            $tva_ligne = $total_ht * ($tva/100);
                            $tva_ligne = price2num(price($tva_ligne), 'MT');

                            $tline  = new NdfpTvaLine($this->db);
                            $tline->fetch($tva_line->rowid);
                            $tline->total_tva = $tva_ligne;
                            $tline->fk_tva = $tva_line->tva_tx;
                            $tline->update();

                            $total_tva += $tva_ligne;
                        }                       
                    }                   
                }

                $total_tva = $total_ttc - $total_ht - $total_tva; 
            }
			

			$total_tva = price2num($total_tva, 'MT');
			
            // Update line
            $this->line->fk_tva = $fk_tva;
            $this->line->total_tva = $total_tva;


            $result = $this->line->update();

            if ($result > 0)
            {

				// Update line
                $result = $this->update_totals();
	
				if ($result > 0)
				{
					$this->fetch_lines();
													
					$this->generate_pdf($user);

					$this->error = $langs->trans('TvaUpdated');
					return $this->line->rowid;
				}
				else
				{
					$this->error = $this->line->error;

					return -1;
				}
            }
            else
            {
                $this->error = $this->line->error;

                return -2;
            }
        }
    }

     /**
     *  \brief Delete a tax line from a note
     *
     *  @param     user            User who creates
     *  @return    int              <0 if KO, id the line added if OK
     */
    function deletetaxline($lineid, $user)
    {
        global $langs, $conf;

        if ($this->statut != 0)
        {
            $this->error = $langs('CanNotModifyLinesOnNonDraftNote');
            return -1;
        }

        $this->line  = new NdfpTaxLine($this->db);


        $result = $this->line->fetch($lineid);

        if ($result < 0)
        {
            $this->error = $langs->trans('LineDoesNotExist');
            return -1;
        }

		$line = new NdfpLine($this->db);
		$result = $line->fetch($this->line->fk_ndfp_det);

        if ($result < 0)
        {
            return -1;
        }
		
        if ($this->line->delete() > 0)
        {        	        	
    		$result = $this->update_totals();

        	if ($result > 0)
        	{
        		
				$this->fetch_lines();
                $this->generate_pdf($user);

        		return 1;
        	}
        	else
        	{
        		$this->error = $this->db->error()." sql=".$sql;
        		return -1;
        	}
        }
        else
        {
        	$this->error = $line->error;
        	return -1;
        }
    }
    
     /**
     *  \brief Delete a VAT line from a note
     *
     *  @param     user            User who creates
     *  @return    int              <0 if KO, id the line added if OK
     */
    function deletetvaline($lineid, $user)
    {
        global $langs, $conf;

        if ($this->statut != 0)
        {
            $this->error = $langs('CanNotModifyLinesOnNonDraftNote');
            return -1;
        }

        $this->line  = new NdfpTvaLine($this->db);


        $result = $this->line->fetch($lineid);

        if ($result < 0)
        {
            $this->error = $langs->trans('LineDoesNotExist');
            return -1;
        }

		$line = new NdfpLine($this->db);
		$result = $line->fetch($this->line->fk_ndfp_det);

        if ($result < 0)
        {
            return -1;
        }
        		
        if ($this->line->delete() > 0)
        {
        	
    		$result = $this->update_totals();

        	if ($result > 0)
        	{
				$this->fetch_lines();
                $this->generate_pdf($user);

        		return 1;
        	}
        	else
        	{
        		$this->error = $this->db->error()." sql=".$sql;
        		return -1;
        	}
        }
        else
        {
        	$this->error = $line->error;
        	return -1;
        }
    }
    	
     /**
     *  \brief Delete an expense from a note
     *
     *  @param     user            User who creates
     *  @return    int              <0 if KO, id the line added if OK
     */
    function deleteline($lineid, $user)
    {
        global $langs, $conf;

        if ($this->statut != 0)
        {
            $this->error = $langs('CanNotModifyLinesOnNonDraftNote');
            return -1;
        }

        $line  = new NdfpLine($this->db);


        $result = $line->fetch($lineid);

        if ($result < 0)
        {
            $this->error = $langs->trans('LineDoesNotExist');
            return -1;
        }

        if ($line->delete() > 0)
        {
			//$result = $this->update_lines();
			if ($result < 0)
			{
				return -1;
			}
        	
    		$result = $this->update_totals();

        	if ($result > 0)
        	{
				$this->fetch_lines();
                $this->generate_pdf($user);

        		return 1;
        	}
        	else
        	{
        		$this->error = $this->db->error()." sql=".$sql;
        		return -1;
        	}
        }
        else
        {
        	$this->error = $line->error;
        	return -1;
        }
    }

     /**
     *  \brief Update the totals of the note based on the lines added
     *
     *  @return    int              <0 if KO, >0 if OK
     */
    function update_totals()
    {
        global $langs;

        if (!$this->id)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }
				
		// Update TVA
        $sql = 'SELECT td.fk_ndfp_tax_det, xt.price_base_type, SUM(td.total_tva) AS tot_tva';
        $sql.= ' FROM '.MAIN_DB_PREFIX.'ndfp_tva_det td';
        $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'ndfp_tax_det xt ON xt.rowid = td.fk_ndfp_tax_det';
        $sql.= ' WHERE td.fk_ndfp = '.$this->id;
        $sql.= ' GROUP BY td.fk_ndfp_tax_det';

        dol_syslog("Ndfp::update_totals sql=".$sql, LOG_DEBUG);

        $result = $this->db->query($sql);

        if ($result)
        {
            $num = $this->db->num_rows($result);

            if ($num)
            {
            	$i = 0;
            	while ($i < $num)
            	{
					$obj = $this->db->fetch_object($result);

					$fk_ndfp_tax_det = $obj->fk_ndfp_tax_det;
					$total_tva = $obj->tot_tva;
					$price_base_type = $obj->price_base_type;
					
					if ($price_base_type == 'HT')
					{
						$sql = 'UPDATE '.MAIN_DB_PREFIX.'ndfp_tax_det SET total_ttc = total_ht + '.$total_tva.'';
						$sql.= ' WHERE rowid = '.$fk_ndfp_tax_det;
					}
					else
					{
						$sql = 'UPDATE '.MAIN_DB_PREFIX.'ndfp_tax_det SET total_ht = total_ttc - '.$total_tva.'';
						$sql.= ' WHERE rowid = '.$fk_ndfp_tax_det;					
					}
					
					dol_syslog("Ndfp::update_totals sql=".$sql, LOG_DEBUG);
					$r = $this->db->query($sql);
					
					if ($r < 0)
					{
						$this->error = $this->db->error()." sql=".$sql;
						return -1;
					}
					
					$i++;					
                }
            }
            else
            {
                $sql = "UPDATE ".MAIN_DB_PREFIX."ndfp_tax_det SET total_ttc = total_ht WHERE price_base_type = 'HT'";
                $sql.= " AND fk_ndfp = ".$this->id;

                    
                dol_syslog("Ndfp::update_totals sql=".$sql, LOG_DEBUG);
                $r = $this->db->query($sql);

                if ($r < 0)
                {
                    $this->error = $this->db->error()." sql=".$sql;
                    return -1;
                }

                $sql = "UPDATE ".MAIN_DB_PREFIX."ndfp_tax_det SET total_ht = total_ttc WHERE price_base_type = 'TTC'";
                $sql.= " AND fk_ndfp = ".$this->id;

                    
                dol_syslog("Ndfp::update_totals sql=".$sql, LOG_DEBUG);
                $r = $this->db->query($sql);

                if ($r < 0)
                {
                    $this->error = $this->db->error()." sql=".$sql;
                    return -1;
                }
            }
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            return -1;
        }
        		
		
		// Update taxes
      	$sql = 'SELECT td.fk_ndfp_det, SUM(td.total_ht_cur) AS tot_ht_cur, SUM(td.total_ht) AS tot_ht,';
      	$sql.= ' SUM(td.total_ttc_cur) AS tot_ttc_cur, SUM(td.total_ttc) AS tot_ttc';
        $sql.= ' FROM '.MAIN_DB_PREFIX.'ndfp_tax_det td';
        $sql.= ' WHERE td.fk_ndfp = '.$this->id;
        $sql.= ' GROUP BY td.fk_ndfp_det';

        dol_syslog("Ndfp::update_totals sql=".$sql, LOG_DEBUG);

        $result = $this->db->query($sql);

        if ($result)
        {
            $num = $this->db->num_rows($result);

            if ($num)
            {
            	$i = 0;
            	while ($i < $num)
            	{
					$obj = $this->db->fetch_object($result);

					$fk_ndfp_det = $obj->fk_ndfp_det;
					$total_ttc = $obj->tot_ttc;
					$total_ht = $obj->tot_ht;
					$total_ttc_cur = $obj->tot_ttc_cur;
					$total_ht_cur = $obj->tot_ht_cur;
					
					$total_tva = $total_ttc - $total_ht;
					

					$sql = 'UPDATE '.MAIN_DB_PREFIX.'ndfp_det SET total_tva = '.$total_tva.', total_ttc = '.$total_ttc.', total_ht = '.$total_ht.', ';
					$sql.= ' total_ht_cur = '.$total_ht_cur.', total_ttc_cur = '.$total_ttc_cur;
					$sql.= ' WHERE rowid = '.$fk_ndfp_det;

					dol_syslog("Ndfp::update_totals sql=".$sql, LOG_DEBUG);
					$r = $this->db->query($sql);
					
					if ($r < 0)
					{
						$this->error = $this->db->error()." sql=".$sql;
						return -1;
					}
					
					$i++;					
                }
            }
            else
            {
                $sql = "UPDATE ".MAIN_DB_PREFIX."ndfp_det SET total_ht = 0, total_ttc = 0, total_ht_cur = 0, total_ttc_cur = 0, total_tva = 0 WHERE milestone = 1";
                $sql.= " AND fk_ndfp = ".$this->id;

                    
                dol_syslog("Ndfp::update_totals sql=".$sql, LOG_DEBUG);
                $r = $this->db->query($sql);

                if ($r < 0)
                {
                    $this->error = $this->db->error()." sql=".$sql;
                    return -1;
                }              
            }
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            return -1;
        }
        		
		// Update note
        $sql = 'SELECT SUM(total_tva) AS tot_tva, SUM(total_ht) AS tot_ht, SUM(total_ttc) AS tot_ttc';
        $sql.= ' FROM '.MAIN_DB_PREFIX.'ndfp_det';
        $sql.= ' WHERE fk_ndfp = '.$this->id;
        $sql.= ' GROUP BY fk_ndfp';

        dol_syslog("Ndfp::update_totals sql=".$sql, LOG_DEBUG);

        $result = $this->db->query($sql);

        if ($result)
        {
            $num = $this->db->num_rows($result);

            if ($num > 0)
            {
                $obj = $this->db->fetch_object($result);

                $this->total_ht = $obj->tot_ht;
                $this->total_ttc = $obj->tot_ttc;
                $this->total_tva = $this->total_ttc - $this->total_ht;
            }
            else // all expenses have been removed
            {
                $this->total_ht = 0;
                $this->total_ttc = 0;
                $this->total_tva = 0;

            }

            $sql = 'UPDATE '.MAIN_DB_PREFIX.'ndfp SET total_ttc = '.$this->total_ttc.',';
            $sql.= ' total_ht = '.$this->total_ht.', total_tva = '.$this->total_tva;
            $sql.= ' WHERE rowid = '.$this->id;

			dol_syslog("Ndfp::update_totals sql=".$sql, LOG_DEBUG);
            $result = $this->db->query($sql);
            
            if ($result)
            {
                return 1;
            }
            else
            {
                $this->error = $this->db->error()." sql=".$sql;
                return -1;
            }
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            return -1;
        }
    }



    /**
     *  \brief Load travel fees rules
     *
     *  @param     fk_cat           Category of the vehicule used
     *  @return    int              <0 if KO, total ttc if OK
     */
    function load_coefs($fk_cat)
    {
        global $langs;

        $this->ranges = array();

		
        if ($fk_cat < 0)
        {
			$this->error = $langs->trans('ErrorBadParameterCat');
			return -1;
        }

        $sql  = " SELECT r.range, t.offset, t.coef";
        $sql .= " FROM ".MAIN_DB_PREFIX."c_ndfp_exp_tax t";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_ndfp_exp_tax_range r ON r.rowid = t.fk_range";
        $sql .= " WHERE t.fk_cat = ".$fk_cat;
        $sql .= " ORDER BY r.range ASC";

        dol_syslog("Ndfp::compute_total_km sql=".$sql, LOG_DEBUG);

        $result = $this->db->query($sql);
		
        if ($result)
        {
            $num = $this->db->num_rows($result);

            if ($num)
            {
                for ($i = 0; $i < $num; $i++)
                {
                    $obj = $this->db->fetch_object($result);

                    $this->ranges[$i] = $obj;
                }


                return 1;
            }
            else
            {
                $this->error = $langs->trans('TaxUndefinedForThisCategory');

                return -1;
            }

        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;

            return -1;
        }
    }
    
    /**
     *  \brief Compute the cost of the kilometers expense based on the number of kilometers and the vehicule category
     *
     *  @param     fk_cat           Category of the vehicule used
     *  @param     qty              Number of kilometers
     *  @param     tva              VAT rate
     *  @return    int              <0 if KO, total ttc if OK
     */
    function get_coefs($fk_cat, $qty)
    {
        global $langs;
        
        $coef = 0;        
        $offset = 0;


        if ($qty < 0)
        {
			$this->error = $langs->trans('ErrorBadParameterQty');
			return -1;
        }

        $this->load_coefs($fk_cat);
        
        //Clean
        $total_qty = price2num($qty);

		$ranges = $this->ranges;
		
		$num = count($ranges);

		if ($num)
		{
			for ($i = 0; $i < $num; $i++)
			{
				if ($i < ($num - 1))
				{
					if ($total_qty > $ranges[$i]->range && $total_qty <= $ranges[$i+1]->range)
					{
						$coef = $ranges[$i]->coef;
						$offset = $ranges[$i]->offset;
					}
				}
				else
				{
					if ($total_qty > $ranges[$i]->range)
					{
						$coef = $ranges[$i]->coef;
						$offset = $ranges[$i]->offset;
					}
				}
						  
			}
	
		}

		$coef = floatval($coef);
		$offset = floatval($offset);
		
		return array($coef, $offset);

    }
    
     /**
     *  \brief Compute the cost of the kilometers expense based on the number of kilometers and the vehicule category
     *
     *  @param     fk_cat           Category of the vehicule used
     *  @param     qty              Number of kilometers
     *  @param     tva              VAT rate
     *  @return    int              <0 if KO, total ttc if OK
     */
    function compute_total_km($fk_cat, $qty, $tva)
    {
        global $langs;


		list($coef, $offset) = $this->get_coefs($fk_cat, $qty);
         
		
        $total_ttc = floatval($offset) + floatval($coef) * $qty;
		$total_ttc = price2num($total_ttc, 'MT');
		
		return $total_ttc;
    }
    
    function compute_travel_fees($qty = 0, $fk_cat = 0, $previous_exp = 0)
    {

        $total_qty = $qty + $previous_exp;  
        
        
        $previous_fees = $this->compute_total_km($fk_cat, $previous_exp, $tva);
        $total_ttc = $this->compute_total_km($fk_cat, $total_qty, $tva);
        
        
        $total_ttc = $total_ttc - $previous_fees;
        return $total_ttc;
    }

    function getStartEndTms()
    {
        global $conf, $user;

        $year = dol_print_date(dol_now(), '%Y');
        $month = dol_print_date(dol_now(), '%m');


        $start_month = $conf->global->SOCIETE_FISCAL_MONTH_START ? $conf->global->SOCIETE_FISCAL_MONTH_START : 1;

        if ($month < $start_month)
        {
            $start_year = $year - 1;

            if ($start_month > 1)
            {
                $end_month = $start_month - 1;
                $end_year = $year;
            }
            else
            {
                $end_month = 12;
                $end_year = $start_year;
            }
        }
        else
        {
            $start_year = $year;

            if ($start_month > 1)
            {
                $end_month = $start_month - 1;
                $end_year = $year + 1;
            }
            else
            {
                $end_month = 12;
                $end_year = $start_year;
            }

        }

        $start_day = 1;
        $end_day = cal_days_in_month(CAL_GREGORIAN, $end_month, $end_year);
                
        $startTms = dol_mktime(0, 0 , 0, $start_month, $start_day, $start_year);
        $endTms = dol_mktime(23, 59, 59, $end_month, $end_day, $end_year);

        return array($startTms, $endTms);
    }

     /**
     *  \brief Get the total number of kilometers for a given user and for the current year
     *
     *  @param     userid           User id
     *  @return    int              Number of kilometers
     */    
    function get_user_fees($userid = 0, $fk_cat = 0, $currentLineId = 0)
    {
		global $conf, $langs;
		
		// Current year
		$year = date('Y');
		$total_fees = 0;
		list($startTms, $endTms) = $this->getStartEndTms();

		$kmExpId = dol_getIdFromCode($this->db, 'EX_KME', 'c_ndfp_exp', 'code', 'rowid');		
            		
        
      	/*$sql = "SELECT SUM(nd.qty) AS total_qty, SUM(nd.previous_exp) as total_previous_exp";
        $sql.= " FROM ".MAIN_DB_PREFIX."ndfp_det as nd";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."ndfp as n ON nd.fk_ndfp = n.rowid";
        $sql.= " WHERE nd.fk_exp = ".$kmExpId." AND nd.fk_cat = ".$fk_cat." AND nd.rowid <> ".$currentLineId." AND n.entity = ".$conf->entity." AND n.fk_user = ".$userid;
        $sql.= " AND (n.dates BETWEEN '".$this->db->idate(dol_get_first_day($year,1,false))."' AND '".$this->db->idate(dol_get_last_day($year,12,false))."')";
        */
        $sql = "SELECT nd.qty, nd.previous_exp";
        $sql.= " FROM ".MAIN_DB_PREFIX."ndfp_det as nd";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."ndfp as n ON nd.fk_ndfp = n.rowid";
        $sql.= " WHERE nd.fk_exp = ".$kmExpId." AND nd.fk_cat = ".$fk_cat." AND nd.rowid <> ".$currentLineId." AND n.entity = ".$conf->entity." AND n.fk_user = ".$userid;
        $sql.= " AND (n.dates BETWEEN '".$this->db->idate($startTms)."' AND '".$this->db->idate($endTms)."') ORDER BY nd.rowid DESC LIMIT 1";

        dol_syslog("Ndfp::get_user_fees sql=".$sql, LOG_DEBUG);
        $result = $this->db->query($sql);
        
		if ($result)
		{
			$num = $this->db->num_rows($result);

			if ($num)
			{
				$obj = $this->db->fetch_object($result);
				$total_fees = intval($obj->qty) + intval($obj->previous_exp);
			}
		}
		
		return $total_fees;            
    
    }

     /**
     *  \brief Get the total number of kilometers for a given user and for the current year
     *
     *  @param     userid           User id
     *  @return    int              Number of kilometers
     */    
    function get_user_cat_fees($userid = 0, $fk_cat = 0)
    {
        global $conf, $langs;
        
        // Current year
        $year = date('Y');
        $total_fees = 0;
        list($startTms, $endTms) = $this->getStartEndTms();

        $kmExpId = dol_getIdFromCode($this->db, 'EX_KME', 'c_ndfp_exp', 'code', 'rowid');        
                    
        $sql = "SELECT SUM(nd.qty) AS total_qty";
        $sql.= " FROM ".MAIN_DB_PREFIX."ndfp_det as nd";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."ndfp as n ON nd.fk_ndfp = n.rowid";
        $sql.= " WHERE nd.fk_exp = ".$kmExpId." nd.fk_cat = ".$fk_cat." AND n.statut > 0 AND n.entity = ".$conf->entity." AND n.fk_user = ".$userid;
        $sql.= " AND (n.dates BETWEEN '".$this->db->idate($startTms)."' AND '".$this->db->idate($endTms)."')";

        dol_syslog("Ndfp::get_user_cat_fees sql=".$sql, LOG_DEBUG);
        $result = $this->db->query($sql);
        
        if ($result)
        {
            $num = $this->db->num_rows($result);

            if ($num)
            {
                $obj = $this->db->fetch_object($result);
                $total_fees = intval($obj->total_qty);
            }
        }
        
        return $total_fees;            
    
    }

}


/**
 *	\class      	NdfpLine
 *	\brief      	Class to manage expenses of a credit note
 *	\remarks		Uses lines of llx_ndfp_det tables
 */
class NdfpLine
{
    var $db;
    var $error;

    var $oldline;

    //! From llx_ndfp_det
    var $rowid;
    var $fk_ndfp;


	var $comment;
	var $ref_ext;
	
    var $datec;
    var $dated;
    var $datef;

    var $fk_user_author;

    var $fk_exp;
    var $fk_tva;
    
    var $fk_cat = 0;
    var $previous_exp = 0;

    var $qty;
	var $rate;
	var $cur_iso;

    var $total_ht;
    var $total_ht_cur;
    var $total_ttc_cur;
    var $total_ttc;
    var $total_tva;

	var $milestone;
	
    var $tms;

   /**
	*  \brief  Constructeur de la classe
	*  @param  DB          handler acces base de donnees
	*/
    function NdfpLine($DB)
    {
        $this->db = $DB;
    }

	/**
	 * \brief Check parameters prior inserting or updating the DB
	 *
	 * @return 	int		<0 if KO, >0 if OK
	 */
     function check_parameters()
     {
		global $conf, $langs;


        // Check parameters
        if (empty($this->fk_exp)){
			$this->error = $langs->trans('ErrorBadParameterExp');
			return -1;
        }

        if (!empty($this->datef) && !empty($this->dated)) {
            if ($this->datef < $this->dated){
                $this->error = $langs->trans('ErrorBadParameterDateDoNotMatch');
                return -1;
            }
        }


         if ($this->qty <= 0){
			$this->error = $langs->trans('ErrorBadParameterQty');
			return -1;
        }

        if ($this->fk_tva < 0){
			$this->error = $langs->trans('ErrorBadParameterTva');
			return -1;
        }

        if ($this->fk_ndfp < 0){
			$this->error = $langs->trans('ErrorBadParameterNdfp');
			return -1;
        }


        return 1;
     }

	/*
	 * \brief Get VAT lines for this exepense
	 *
	 * @param 	rowid	Id of the line
	 * @return 	array	
	 */
    function get_tva_lines()
    {

	    global $langs;

		$tva_lines = array();
		
        if (!$this->rowid){
			$this->error = $langs->trans('LineIdIsMissing');
			return -1;
        }

		if ($this->milestone)
		{
			$sql = 'SELECT nt.rowid, nt.fk_ndfp, nt.fk_ndfp_tax_det, nt.fk_ndfp_det, nt.total_tva, nt.fk_tva, nt.tms';
			$sql.= ' FROM '.MAIN_DB_PREFIX.'ndfp_tva_det as nt';
			//$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_tva AS t ON t.taux = nt.fk_tva';
			$sql.= ' WHERE nt.fk_ndfp_det = '.$this->rowid;
		
			dol_syslog("NdfpLine::fetch sql=".$sql);

			$result = $this->db->query($sql);
			$num = 0;

			if ($result)
			{
				$num = $this->db->num_rows($result);

				if ($num)
				{
					$i = 0;
					while ($i < $num)
					{
						$obj = $this->db->fetch_object($result);

						$tva_lines[$i] = $obj;
					
						$tva_lines[$i]->fk_tva = $obj->fk_tva ? $obj->fk_tva : 0;
						$tva_lines[$i]->tva_tx = $obj->fk_tva ? $obj->fk_tva : 0;//$obj->taux ? $obj->taux : 0;
						$tva_lines[$i]->tms	= $this->db->jdate($obj->tms); 
						$i++;         
					}
				}
			
				return $tva_lines;
			}
			else
			{
				$this->error = $this->db->error()." sql=".$sql;

				return -1;
			}
        }
        else
        {
        	$this->fetch($this->rowid);
        	

        	$tva_line = new StdClass();
        	$tva_line->tva_tx = $this->fk_tva;//$tva->taux;
        	$tva_line->fk_tva = $this->fk_tva;
        	$tva_line->total_tva = $this->total_tva;
        	$tva_line->tms = $this->tms;
        	
        	$tva_lines[] = $tva_line;
        	
        	return $tva_lines;
        }
    }  
    
	/**
	 * \brief Fetch object from database
	 *
	 * @param 	rowid	Id of the line
	 * @return 	int		<0 if KO, >0 if OK
	 */
    function fetch($rowid)
    {

	    global $langs;

        if (!$rowid){
			$this->error = $langs->trans('LineIdIsMissing');
			return -1;
        }

        $sql = 'SELECT nd.rowid, nd.fk_ndfp, nd.comment, nd.fk_cat, nd.ref_ext, nd.dated, nd.datef, nd.datec, nd.fk_user_author,';
        $sql.= ' nd.fk_exp, nd.fk_tva, nd.qty, nd.rate, nd.cur_iso, nd.total_ht_cur, nd.total_ttc_cur, nd.total_ht, nd.milestone,';
        $sql.= ' nd.total_ttc, nd.total_tva, nd.tms';
        $sql.= ' FROM '.MAIN_DB_PREFIX.'ndfp_det as nd';
        $sql.= ' WHERE nd.rowid = '.$rowid;


        dol_syslog("NdfpLine::fetch sql=".$sql);

        $result = $this->db->query($sql);
        $num = 0;

        if ($result)
        {
            $num = $this->db->num_rows($result);

            if ($num)
            {
                $obj = $this->db->fetch_object($result);

                $this->rowid				= $obj->rowid;
                $this->fk_ndfp			    = $obj->fk_ndfp;
                //$this->label		        = $obj->label;

				$this->comment				= $obj->comment;
				$this->ref_ext				= $obj->ref_ext;
				
                $this->dated			    = $obj->dated ? $this->db->jdate($obj->dated) : '';
                $this->datef				= $obj->datef ? $this->db->jdate($obj->datef) : '';
                $this->datec				= $this->db->jdate($obj->datec);

                $this->fk_user_author		= $obj->fk_user_author;

                $this->fk_exp				= $obj->fk_exp;
                $this->fk_tva			    = $obj->fk_tva ? $obj->fk_tva : 0;

                $this->fk_cat               = $obj->fk_cat ? $obj->fk_cat : 0;
                $this->previous_exp         = $obj->previous_exp ? $obj->previous_exp : 0;

                $this->qty			        = $obj->qty;
                $this->rate			        = $obj->rate;
                $this->cur_iso			    = $obj->cur_iso;
                
                $this->total_ht_cur			= $obj->total_ht_cur;
                $this->total_ttc_cur		= $obj->total_ttc_cur;
                $this->total_ht				= $obj->total_ht;
                $this->total_tva			= $obj->total_tva;
                $this->total_ttc			= $obj->total_ttc;
				
				$this->milestone			= $obj->milestone ? 1 : 0; 
                $this->tms					= $this->db->jdate($obj->tms);

           
                
                return 1;
            }
            else
            {
                $this->error = $langs->trans('LineDoesNotExist');
                return -1;
            }
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;

            return -1;
        }
    }



    
    /**
     *	\brief     	Insert line in database
     *	@param      notrigger		1 no triggers
     *	@return		int				<0 if KO, >0 if OK
     */
    function insert($notrigger = 0)
    {
        global $langs, $user, $conf;

        $result = $this->check_parameters();

        if ($result < 0)
        {
            return -1;
        }

        // Clean parameters
		$this->comment = trim($this->comment);
		$this->ref_ext = trim($this->ref_ext);
		
        $this->tms = dol_now();
        $this->datec = dol_now();

        $this->total_ttc = price2num($this->total_ttc, 'MT');
        $this->total_ht_cur = price2num($this->total_ht_cur, 'MT');
        $this->total_ttc_cur = price2num($this->total_ttc_cur, 'MT');
        $this->total_ht = price2num($this->total_ht, 'MT');
        $this->total_tva = price2num($this->total_ttc - $this->total_ht);

        $this->fk_cat = $this->fk_cat ? $this->fk_cat : 0;
        $this->previous_exp = $this->previous_exp ? $this->previous_exp : 0;

        $this->db->begin();

        //
        $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'ndfp_det';
        $sql.= ' (fk_ndfp, comment, fk_cat, previous_exp, ref_ext, dated, datef, datec, fk_user_author,';
        $sql.= ' fk_exp, fk_tva, qty, rate, cur_iso, total_ht_cur, total_ttc_cur,';
        $sql.= ' total_ht, total_ttc, total_tva, milestone, tms)';
        $sql.= " VALUES (".$this->fk_ndfp.",";
		$sql.= " ".($this->comment ? "'".$this->db->escape($this->comment)."'" : "''")." ,";
        $sql.= " ".$this->fk_cat.", ";
        $sql.= " ".$this->previous_exp.", ";
		$sql.= " ".($this->ref_ext ? "'".$this->db->escape($this->ref_ext)."'" : "''")." ,";
        $sql.= $this->dated ? "'".$this->db->idate($this->dated)."'," : 'NULL,';
        $sql.= $this->datef ? "'".$this->db->idate($this->datef)."'," : 'NULL,';
        $sql.= $this->datec ? "'".$this->db->idate($this->datec)."'," : 'NULL,';
        $sql.= " ".$user->id.", ";
        $sql.= " ".$this->fk_exp.", ";
        $sql.= " ".$this->fk_tva.",";
        $sql.= " ".$this->qty.",";
        $sql.= " ".$this->rate.",";
        $sql.= ($this->cur_iso ? "'".$this->db->escape($this->cur_iso)."'" : "''")." ,";
        $sql.= " ".$this->total_ht_cur.",";
        $sql.= " ".$this->total_ttc_cur.",";
        $sql.= " ".$this->total_ht.",";
		$sql.= " ".$this->total_ttc.",";
		$sql.= " ".$this->total_tva.",";
		$sql.= ($this->milestone ? "1" : "0")." ,";
        $sql.= " '".$this->db->idate($this->tms)."'";
        $sql.= ')';

        dol_syslog("NdfpLine::insert sql=".$sql);

        $resql = $this->db->query($sql);
        if ($resql)
        {
            $this->rowid = $this->db->last_insert_id(MAIN_DB_PREFIX.'ndfp_det');

            $this->update_ndfp_tms();

            if (! $notrigger)
            {
                // Appel des triggers
                include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
                $interface = new Interfaces($this->db);
                $result = $interface->run_triggers('LINENDFP_INSERT', $this, $user ,$langs, $conf);
                if ($result < 0) {
                    $this->error = $langs->trans('ErrorCallingTrigger');
                    $this->db->rollback();
                    return -1;
                }
                // Fin appel triggers
            }

            $this->db->commit();

            return $this->rowid;

        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -2;
        }
    }

    /**
     *  	\brief Update line into database
     *		@return		int		<0 if KO, >0 if OK
     */
    function update($notrigger = 0)
    {
        global $langs, $user, $conf;

        $result = $this->check_parameters();

        if ($result < 0)
        {
            return -1;
        }

        if (!$this->rowid){
			$this->error = $langs->trans('LineIdIsMissing');
			return -1;
        }

        // Clean parameters
		$this->comment = trim($this->comment);
		$this->ref_ext = trim($this->ref_ext);
		
        $this->total_ttc = price2num($this->total_ttc, 'MT');
        $this->total_ht_cur = price2num($this->total_ht_cur, 'MT');
        $this->total_ttc_cur = price2num($this->total_ttc_cur, 'MT');
        $this->total_ht = price2num($this->total_ht, 'MT');
        $this->total_tva = price2num($this->total_ttc - $this->total_ht);
        $this->tms = dol_now();

        $this->fk_cat = $this->fk_cat ? $this->fk_cat : 0;
        $this->previous_exp = $this->previous_exp ? $this->previous_exp : 0;

        $this->db->begin();

        // Mise a jour ligne en base
        $sql = "UPDATE ".MAIN_DB_PREFIX."ndfp_det SET";
		$sql.= " `comment` = '".$this->db->escape($this->comment)."',";
		$sql.= " `ref_ext` = '".$this->db->escape($this->ref_ext)."',";
        $sql.= " `dated` = ".($this->dated ? "'".$this->db->idate($this->dated)."'," : 'null,');
        $sql.= " `datef` = ".($this->datef ? "'".$this->db->idate($this->datef)."'," : 'null,');
        $sql.= " `fk_exp` = ".$this->fk_exp.",";
        $sql.= " `fk_cat` = ".$this->fk_cat.",";
        $sql.= " `previous_exp` = ".$this->previous_exp.",";
        $sql.= " `fk_tva` = ".$this->fk_tva.",";
        $sql.= " `qty` = ".$this->qty.",";
        $sql.= " `rate` = ".$this->rate.",";
        $sql.= " `cur_iso` = '".$this->db->escape($this->cur_iso)."',";
        $sql.= " `total_ht_cur` = ".$this->total_ht_cur.",";
        $sql.= " `total_ttc_cur` = ".$this->total_ttc_cur.",";
        $sql.= " `total_ht` = ".$this->total_ht.",";
		$sql.= " `total_ttc` = ".$this->total_ttc.",";
		$sql.= " `total_tva` = ".$this->total_tva.",";
        $sql.= " `tms` = '".$this->db->idate($this->tms)."'";
        $sql.= " WHERE `rowid` = ".$this->rowid;

        dol_syslog("NdfpLine::update sql=".$sql);

        $result = $this->db->query($sql);
        if ($result)
        {
            $this->update_ndfp_tms();

            if (! $notrigger)
            {
                // Appel des triggers
                include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
                $interface = new Interfaces($this->db);
                $result = $interface->run_triggers('LINENDFP_UPDATE', $this, $user, $langs, $conf);
                if ($result < 0) {
                    $this->error = $langs->trans('ErrorCallingTrigger');
                    $this->db->rollback();
                    return -1;
                }
                // Fin appel triggers
            }
            $this->db->commit();

            return 1;
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -2;
        }
    }

	/**
	 * 	\brief Delete line in database
	 *	@return	 int  <0 si ko, >0 si ok
	 */
	function delete($notrigger = 0)
	{
		global $conf,$langs,$user;

        if (!$this->rowid){
			$this->error = $langs->trans('LineIdIsMissing');
			return -1;
        }

		$this->db->begin();

		$sql = "DELETE FROM ".MAIN_DB_PREFIX."ndfp_det WHERE rowid = ".$this->rowid;

		dol_syslog("NdfpLine::delete sql=".$sql, LOG_DEBUG);
        $result = $this->db->query($sql);

		if ($result)
		{
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."ndfp_tva_det WHERE fk_ndfp_det = ".$this->rowid;
			dol_syslog("NdfpLine::delete sql=".$sql);
			$result = $this->db->query($sql);

			$sql = "DELETE FROM ".MAIN_DB_PREFIX."ndfp_tax_det WHERE fk_ndfp_det = ".$this->rowid;
			dol_syslog("NdfpLine::delete sql=".$sql);
			$result = $this->db->query($sql);
			        
		    $this->update_ndfp_tms();

            if (! $notrigger)
            {
    			// Calling triggers
    			include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
    			$interface = new Interfaces($this->db);
    			$result = $interface->run_triggers('LINENDFP_DELETE',$this,$user,$langs,$conf);
    			if ($result < 0)
                {
                    $this->error = $langs->trans('ErrorCallingTrigger');
                    $this->db->rollback();
                    return -1;
                }
            }
			// Fin appel triggers

			$this->db->commit();

			return 1;
		}
		else
		{
			$this->error = $this->db->error()." sql=".$sql;
			$this->db->rollback();

			return -1;
		}
	}

	/**
	 * 	\brief Update timestamp of note
	 *	@return	 int  <0 si ko, >0 si ok
	 */
	function update_ndfp_tms()
	{
		global $conf, $langs, $user;

        if (!$this->rowid)
        {
			$this->error = $langs->trans('LineIdIsMissing');
			return -1;
        }


        // Update timestamp of ndfp
        $tms = dol_now();

        $sql = " UPDATE ".MAIN_DB_PREFIX."ndfp SET tms = '".$this->db->idate($tms)."' WHERE rowid = ".$this->fk_ndfp;
        dol_syslog("NdfpLine::update_ndfp_tims sql=".$sql, LOG_DEBUG);

        $result = $this->db->query($sql);

        if ($result > 0)
        {
            return 1;
        }
        else
        {
            $this->error=$this->db->error()." sql=".$sql;
            return -1;
        }

	}

    /**
     *      \brief     	Update totals of the line
     *		@return		int		<0 si ko, >0 si ok
     */
    function update_total()
    {

        if (!$this->rowid){
			$this->error = $langs->trans('LineIdIsMissing');
			return -1;
        }

        dol_syslog("NdfpLine::update_total", LOG_DEBUG);
        $this->tms = dol_now();

        $this->db->begin();

		$this->total_ttc = price2num($this->total_ttc, 'MT');
		$this->total_ttc_cur = price2num($this->total_ttc_cur, 'MT');
        $this->total_ht_cur = price2num($this->total_ht_cur, 'MT');
        $this->total_ht = price2num($this->total_ht, 'MT');
        $this->total_tva = price2num($this->total_ttc - $this->total_ht);
        //
        $sql = "UPDATE ".MAIN_DB_PREFIX."ndfp_det SET";
        $sql.= " `total_ht` = ".$this->total_ht.",";
        $sql.= " `total_ht_cur` = ".$this->total_ht_cur.",";
        $sql.= " `total_ttc_cur` = ".$this->total_ttc_cur.",";
        $sql.= " `total_tva` = ".$this->total_tva.",";
        $sql.= " `total_ttc` = ".$this->total_ttc.",";
        $sql.= " `tms` = '".$this->db->idate($this->tms)."'";
        $sql.= " WHERE rowid = ".$this->rowid;

        $resql = $this->db->query($sql);
        if ($resql)
        {
            $this->db->commit();
            return 1;
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -2;
        }
    }

}

/**
 *	\class      	NdfpTvaLine
 *	\brief      	Class to manage TVA expenses of a credit note
 *	\remarks		Uses lines of llx_ndfp_tva_det tables
 */
class NdfpTvaLine
{
    var $db;
    var $error;

    var $oldline;

    //! From llx_ndfp_det
    var $rowid;
    var $fk_ndfp_det;
    var $fk_ndfp_tax_det;
	var $fk_ndfp;
	var $fk_user_author;
    var $fk_tva;

    var $total_tva;
	
    var $tms;

   /**
	*  \brief  Constructeur de la classe
	*  @param  DB          handler acces base de donnees
	*/
    function NdfpTvaLine($DB)
    {
        $this->db = $DB;
    }

	/**
	 * \brief Check parameters prior inserting or updating the DB
	 *
	 * @return 	int		<0 if KO, >0 if OK
	 */
     function check_parameters()
     {
		global $conf, $langs;


        // Check parameters
        if (empty($this->fk_ndfp_det)){
			$this->error = $langs->trans('ErrorBadParameterNdfp');
			return -1;
        }

        if (empty($this->fk_ndfp_tax_det)){
			$this->error = $langs->trans('ErrorBadParameterNdfp');
			return -1;
        }
        
        if (empty($this->fk_ndfp)){
			$this->error = $langs->trans('ErrorBadParameterNdfp');
			return -1;
        }	
        
        if ($this->fk_tva < 0){
			$this->error = $langs->trans('ErrorBadParameterTva');
			return -1;
        }


        if ($this->total_tva < 0){
			$this->error = $langs->trans('ErrorBadParameterTVATotal');
			return -1;
        }


        return 1;
     }

	/**
	 * \brief Fetch object from database
	 *
	 * @param 	rowid	Id of the line
	 * @return 	int		<0 if KO, >0 if OK
	 */
    function fetch($rowid)
    {

	    global $langs;

        if (!$rowid){
			$this->error = $langs->trans('LineIdIsMissing');
			return -1;
        }

        $sql = 'SELECT nt.rowid, nt.fk_ndfp, nt.fk_ndfp_det, nt.fk_ndfp_tax_det, nt.fk_user_author, nt.total_tva, nt.fk_tva, nt.tms';
        $sql.= ' FROM '.MAIN_DB_PREFIX.'ndfp_tva_det as nt';
        $sql.= ' WHERE nt.rowid = '.$rowid;

        dol_syslog("NdfpTvaLine::fetch sql=".$sql);

        $result = $this->db->query($sql);
        $num = 0;

        if ($result)
        {
            $num = $this->db->num_rows($result);

            if ($num)
            {
                $obj = $this->db->fetch_object($result);

                $this->rowid				= $obj->rowid;
                $this->fk_ndfp_det			= $obj->fk_ndfp_det;
                $this->fk_ndfp_tax_det		= $obj->fk_ndfp_tax_det;
				$this->fk_ndfp				= $obj->fk_ndfp;
                $this->fk_tva			    = $obj->fk_tva ? $obj->fk_tva : 0;
				$this->fk_user_author		= $obj->fk_user_author;
                $this->total_tva			= $obj->total_tva;
				 
                $this->tms					= $this->db->jdate($obj->tms);           
                
                return 1;//
            }
            else
            {
                $this->error = $langs->trans('LineDoesNotExist');
                return -1;
            }
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;

            return -1;
        }
    }

    /**
     *	\brief     	Insert line in database
     *	@param      notrigger		1 no triggers
     *	@return		int				<0 if KO, >0 if OK
     */
    function insert($notrigger = 0)
    {
        global $langs, $user, $conf;

        $result = $this->check_parameters();

        if ($result < 0)
        {
            return -1;
        }

        // Clean parameters		
        $this->tms = dol_now();

        $this->total_tva = price2num($this->total_tva, 'MT');

        $this->db->begin();

        //
        $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'ndfp_tva_det';
        $sql.= ' (fk_ndfp_det, fk_ndfp_tax_det, fk_ndfp, fk_user_author, fk_tva, total_tva, tms)';
        $sql.= " VALUES (".$this->fk_ndfp_det.",";
        $sql.= " ".$this->fk_ndfp_tax_det.",";
        $sql.= " ".$this->fk_ndfp.",";
        $sql.= " ".$user->id.",";
        $sql.= " ".$this->fk_tva.",";
		$sql.= " ".$this->total_tva.",";
        $sql.= " '".$this->db->idate($this->tms)."'";
        $sql.= ')';

        dol_syslog("NdfpTvaLine::insert sql=".$sql);

        $resql = $this->db->query($sql);
        if ($resql)
        {
            $this->rowid = $this->db->last_insert_id(MAIN_DB_PREFIX.'ndfp_tva_det');

            if (! $notrigger)
            {
                // Appel des triggers
                include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
                $interface = new Interfaces($this->db);
                $result = $interface->run_triggers('LINETVANDFP_INSERT', $this, $user ,$langs, $conf);
                if ($result < 0) {
                    $this->error = $langs->trans('ErrorCallingTrigger');
                    $this->db->rollback();
                    return -1;
                }
                // Fin appel triggers
            }

            $this->db->commit();

            return $this->rowid;

        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -2;
        }
    }

    /**
     *  	\brief Update line into database
     *		@return		int		<0 if KO, >0 if OK
     */
    function update($notrigger = 0)
    {
        global $langs, $user, $conf;

        $result = $this->check_parameters();

        if ($result < 0)
        {
            return -1;
        }

        if (!$this->rowid){
			$this->error = $langs->trans('LineIdIsMissing');
			return -1;
        }

        // Clean parameters
        $this->total_tva = price2num($this->total_tva, 'MT');
        $this->tms = dol_now();


        $this->db->begin();

        // Mise a jour ligne en base
        $sql = "UPDATE ".MAIN_DB_PREFIX."ndfp_tva_det SET";
        $sql.= " `fk_ndfp_det` = ".$this->fk_ndfp_det.",";
        $sql.= " `fk_ndfp_tax_det` = ".$this->fk_ndfp_tax_det.",";
        $sql.= " `fk_ndfp` = ".$this->fk_ndfp.",";
        $sql.= " `fk_tva` = ".$this->fk_tva.",";
		$sql.= " `total_tva` = ".$this->total_tva.",";
        $sql.= " `tms` = '".$this->db->idate($this->tms)."'";
        $sql.= " WHERE `rowid` = ".$this->rowid;

        dol_syslog("NdfpTvaLine::update sql=".$sql);

        $result = $this->db->query($sql);
        if ($result)
        {

            if (! $notrigger)
            {
                // Appel des triggers
                include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
                $interface = new Interfaces($this->db);
                $result = $interface->run_triggers('LINETVANDFP_UPDATE', $this, $user, $langs, $conf);
                if ($result < 0) {
                    $this->error = $langs->trans('ErrorCallingTrigger');
                    $this->db->rollback();
                    return -1;
                }
                // Fin appel triggers
            }
            $this->db->commit();

            return 1;
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -2;
        }
    }

	/**
	 * 	\brief Delete line in database
	 *	@return	 int  <0 si ko, >0 si ok
	 */
	function delete($notrigger = 0)
	{
		global $conf,$langs,$user;

        if (!$this->rowid){
			$this->error = $langs->trans('LineIdIsMissing');
			return -1;
        }

		$this->db->begin();

		$sql = "DELETE FROM ".MAIN_DB_PREFIX."ndfp_tva_det WHERE rowid = ".$this->rowid;

		dol_syslog("NdfpTvaLine::delete sql=".$sql, LOG_DEBUG);
        $result = $this->db->query($sql);

		if ($result)
		{
            if (! $notrigger)
            {
    			// Calling triggers
    			include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
    			$interface = new Interfaces($this->db);
    			$result = $interface->run_triggers('LINETVANDFP_DELETE',$this,$user,$langs,$conf);
    			if ($result < 0)
                {
                    $this->error = $langs->trans('ErrorCallingTrigger');
                    $this->db->rollback();
                    return -1;
                }
            }
			// Fin appel triggers

			$this->db->commit();

			return 1;
		}
		else
		{
			$this->error = $this->db->error()." sql=".$sql;
			$this->db->rollback();

			return -1;
		}
	}
}


/**
 *	\class      	NdfpTaxLine
 *	\brief      	Class to manage Taxes expenses of a credit note
 *	\remarks		Uses lines of llx_ndfp_tax_det tables
 */
class NdfpTaxLine
{
    var $db;
    var $error;

    var $oldline;

    //! From llx_ndfp_tax_det      
    var $rowid;
    var $fk_ndfp_det;
	var $fk_ndfp;
	var $fk_user_author;

	var $total_ht;
	var $total_ttc;
	var $total_ht_cur;
	var $total_ttc_cur;
	
	var $price_base_type;	
    var $tms;

   /**
	*  \brief  Constructeur de la classe
	*  @param  DB          handler acces base de donnees
	*/
    function NdfpTaxLine($DB)
    {
        $this->db = $DB;
    }

	/**
	 * \brief Check parameters prior inserting or updating the DB
	 *
	 * @return 	int		<0 if KO, >0 if OK
	 */
     function check_parameters()
     {
		global $conf, $langs;


        // Check parameters
        if (empty($this->fk_ndfp_det)){
			$this->error = $langs->trans('ErrorBadParameterNdfp');
			return -1;
        }

        if (empty($this->fk_ndfp)){
			$this->error = $langs->trans('ErrorBadParameterNdfp');
			return -1;
        }	        


        return 1;
     }

	/**
	 * \brief Fetch object from database
	 *
	 * @param 	rowid	Id of the line
	 * @return 	int		<0 if KO, >0 if OK
	 */
    function fetch($rowid)
    {

	    global $langs;

        if (!$rowid)
        {
			$this->error = $langs->trans('LineIdIsMissing');
			return -1;
        }
		
        $sql = 'SELECT nt.rowid, nt.fk_ndfp_det, nt.fk_ndfp, nt.fk_user_author, nt.total_ht,';
        $sql.= ' nt.total_ttc, nt.total_ht_cur, nt.total_ttc_cur, nt.price_base_type, nt.tms';
        $sql.= ' FROM '.MAIN_DB_PREFIX.'ndfp_tax_det as nt';
        $sql.= ' WHERE nt.rowid = '.$rowid;

        dol_syslog("NdfpTaxLine::fetch sql=".$sql);

        $result = $this->db->query($sql);
        $num = 0;

        if ($result)
        {
            $num = $this->db->num_rows($result);

            if ($num)
            {
                $obj = $this->db->fetch_object($result);

                $this->rowid				= $obj->rowid;
                $this->fk_ndfp_det			= $obj->fk_ndfp_det;
				$this->fk_ndfp				= $obj->fk_ndfp;
                $this->fk_user_author		= $obj->fk_user_author;

                $this->total_ht				= $obj->total_ht;
				$this->total_ttc			= $obj->total_ttc;
				$this->total_ht_cur			= $obj->total_ht_cur;
				$this->total_ttc_cur		= $obj->total_ttc_cur;
				
				$this->price_base_type		= $obj->price_base_type ? $obj->price_base_type : 'HT'; 
                $this->tms					= $this->db->jdate($obj->tms);           
                
                return 1;
            }
            else
            {
                $this->error = $langs->trans('LineDoesNotExist');
                return -1;
            }
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;

            return -1;
        }
    }

    /**
     *	\brief     	Insert line in database
     *	@param      notrigger		1 no triggers
     *	@return		int				<0 if KO, >0 if OK
     */
    function insert($notrigger = 0)
    {
        global $langs, $user, $conf;

        $result = $this->check_parameters();

        if ($result < 0)
        {
            return -1;
        }
		
		
        // Clean parameters		
        $this->tms = dol_now();
	
		$this->total_ht = price2num($this->total_ht, 'MT');
		$this->total_ttc = price2num($this->total_ttc, 'MT');
		$this->total_ht_cur = price2num($this->total_ht_cur, 'MT');
        $this->total_ttc_cur = price2num($this->total_ttc_cur, 'MT');

        $this->db->begin();			
        
        $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'ndfp_tax_det';
        $sql.= ' (fk_ndfp_det, fk_ndfp, fk_user_author, total_ht, total_ttc, total_ht_cur, total_ttc_cur, price_base_type, tms)';
        $sql.= " VALUES (".$this->fk_ndfp_det.",";
        $sql.= " ".$this->fk_ndfp.",";
        $sql.= " ".$this->fk_user_author.",";
        $sql.= " ".$this->total_ht.",";
		$sql.= " ".$this->total_ttc.",";
		$sql.= " ".$this->total_ht_cur.",";
		$sql.= " ".$this->total_ttc_cur.",";
		$sql.= " '".$this->price_base_type."',";
        $sql.= " '".$this->db->idate($this->tms)."'";
        $sql.= ')';

        dol_syslog("NdfpTaxLine::insert sql=".$sql);

        $resql = $this->db->query($sql);
        if ($resql)
        {
            $this->rowid = $this->db->last_insert_id(MAIN_DB_PREFIX.'ndfp_tax_det');

            if (! $notrigger)
            {
                // Appel des triggers
                include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
                $interface = new Interfaces($this->db);
                $result = $interface->run_triggers('LINETAXNDFP_INSERT', $this, $user ,$langs, $conf);
                if ($result < 0) {
                    $this->error = $langs->trans('ErrorCallingTrigger');
                    $this->db->rollback();
                    return -1;
                }
                // Fin appel triggers
            }

            $this->db->commit();

            return $this->rowid;

        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -2;
        }
        
    }

    /**
     *  	\brief Update line into database
     *		@return		int		<0 if KO, >0 if OK
     */
    function update($notrigger = 0)
    {
        global $langs, $conf, $user;

        $result = $this->check_parameters();

        if ($result < 0)
        {
            return -1;
        }

        if (!$this->rowid){
			$this->error = $langs->trans('LineIdIsMissing');
			return -1;
        }

		
        // Clean parameters
		$this->total_ht = price2num($this->total_ht, 'MT');
		$this->total_ttc = price2num($this->total_ttc, 'MT');
		$this->total_ht_cur = price2num($this->total_ht_cur, 'MT');
        $this->total_ttc_cur = price2num($this->total_ttc_cur, 'MT');
        
        $this->tms = dol_now();


        $this->db->begin();

        // Mise a jour ligne en base
        $sql = "UPDATE ".MAIN_DB_PREFIX."ndfp_tax_det SET";
        $sql.= " `fk_ndfp_det` = ".$this->fk_ndfp_det.",";
        $sql.= " `fk_ndfp` = ".$this->fk_ndfp.",";
        $sql.= " `fk_user_author` = ".$this->fk_user_author.",";
		$sql.= " `total_ht` = ".$this->total_ht.",";
		$sql.= " `total_ttc` = ".$this->total_ttc.",";
		$sql.= " `total_ht_cur` = ".$this->total_ht_cur.",";
		$sql.= " `total_ttc_cur` = ".$this->total_ttc_cur.",";
		$sql.= " `price_base_type` = '".$this->price_base_type."',";
        $sql.= " `tms` = '".$this->db->idate($this->tms)."'";
        $sql.= " WHERE `rowid` = ".$this->rowid;

        dol_syslog("NdfpTaxLine::update sql=".$sql);

        $result = $this->db->query($sql);
        if ($result)
        {

            if (! $notrigger)
            {
                // Appel des triggers
                include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
                $interface = new Interfaces($this->db);
                $result = $interface->run_triggers('LINETAXNDFP_UPDATE', $this, $user, $langs, $conf);
                if ($result < 0) {
                    $this->error = $langs->trans('ErrorCallingTrigger');
                    $this->db->rollback();
                    return -1;
                }
                // Fin appel triggers
            }
            $this->db->commit();

            return 1;
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();

            return -2;
        }
        
    }

	/**
	 * 	\brief Delete line in database
	 *	@return	 int  <0 si ko, >0 si ok
	 */
	function delete($notrigger = 0)
	{
		global $conf,$langs,$user;

        if (!$this->rowid){
			$this->error = $langs->trans('LineIdIsMissing');
			return -1;
        }

		$this->db->begin();

		$sql = "DELETE FROM ".MAIN_DB_PREFIX."ndfp_tax_det WHERE rowid = ".$this->rowid;

		dol_syslog("NdfpTaxLine::delete sql=".$sql, LOG_DEBUG);
        $result = $this->db->query($sql);

		if ($result)
		{
		
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."ndfp_tva_det WHERE fk_ndfp_tax_det = ".$this->rowid;

			dol_syslog("NdfpTaxLine::delete sql=".$sql, LOG_DEBUG);
			$result = $this->db->query($sql);
        		
            if (! $notrigger)
            {
    			// Calling triggers
    			include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
    			$interface = new Interfaces($this->db);
    			$result = $interface->run_triggers('LINETAXNDFP_DELETE',$this,$user,$langs,$conf);
    			if ($result < 0)
                {
                    $this->error = $langs->trans('ErrorCallingTrigger');
                    $this->db->rollback();
                    return -1;
                }
            }
			// Fin appel triggers

			$this->db->commit();

			return 1;
		}
		else
		{
			$this->error = $this->db->error()." sql=".$sql;
			$this->db->rollback();

			return -1;
		}
	}
	
     /**
     *  \brief Update VAT of the expense
     *
     *  @return    int              <0 if KO, >0 if OK
     */
    function get_all_tva($rowid = 0)
    {
        global $langs;

        if (!$this->rowid)
        {
			$this->error = $langs->trans('NdfpIdIsMissing');
			return -1;
        }

		        				
		// Get all VAt lines for this expense and compute total tva for each
		$tva_lines = $this->get_tva_lines();
        $total_tx = 0;
		foreach ($tva_lines as $tva_line)
		{			
			$tva  = price2num($tva_line->tva_tx);
            if ($rowid != $tva_line->rowid)
            {
                $total_tx += $tva;
            }
			
		}

		return $total_tx;
    }
    
	/**
	 * \brief Get VAT lines for this exepense
	 *
	 * @param 	rowid	Id of the line
	 * @return 	array	
	 */
    function get_tva_lines()
    {

	    global $langs;

		$tva_lines = array();
		
        if (!$this->rowid){
			$this->error = $langs->trans('LineIdIsMissing');
			return -1;
        }


        $sql = 'SELECT nt.rowid, nt.fk_ndfp, nt.fk_ndfp_tax_det, nt.fk_ndfp_det, nt.total_tva, nt.fk_tva, nt.tms';
        $sql.= ' FROM '.MAIN_DB_PREFIX.'ndfp_tva_det as nt';
        $sql.= ' WHERE nt.fk_ndfp_tax_det = '.$this->rowid;
		
        dol_syslog("NdfpTaxLine::fetch sql=".$sql);

        $result = $this->db->query($sql);
        $num = 0;

        if ($result)
        {
            $num = $this->db->num_rows($result);

            if ($num)
            {
            	$i = 0;
            	while ($i < $num)
            	{
					$obj = $this->db->fetch_object($result);

					$tva_lines[$i] = $obj;
					
					$tva_lines[$i]->fk_tva = $obj->fk_tva ? $obj->fk_tva : 0;
					$tva_lines[$i]->tva_tx = $obj->fk_tva ? $obj->fk_tva : 0;//$obj->taux ? $obj->taux : 0;
					$tva_lines[$i]->tms	= $this->db->jdate($obj->tms); 
                 	$i++;         
                }
            }
            
            return $tva_lines;
        }
        else
        {
            $this->error = $this->db->error()." sql=".$sql;

            return -1;
        }
    }    	
}