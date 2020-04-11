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
 *	\file       htdocs/core/triggers/interface_modNdfp_NdfpWorkflow.class.php
 *  \ingroup    agenda
 *  \brief      Trigger file for agenda module
 */

require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php';

/**
 *	\class      InterfaceNdfpWorkflow
 *  \brief      Class of triggered functions for ndfp module
 */
class InterfaceNdfpWorkflow
{
    var $db;
    var $error;

    var $date;
    var $duree;
    var $texte;
    var $desc;

    /**
     *   Constructor.
     *   @param      DB      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;

        $this->name = preg_replace('/^Interface/i','',get_class($this));
        $this->family = "agenda";
        $this->description = "Triggers of ndfp module add actions in agenda according to setup made in agenda setup.";
        $this->version = '1.8.3';                        // 'experimental' or 'dolibarr' or version
        $this->picto = 'ndfp@ndfp';
    }

    /**
     *   Return name of trigger file
     *   @return     string      Name of trigger file
     */
    function getName()
    {
        return $this->name;
    }

    /**
     *   Return description of trigger file
     *   @return     string      Description of trigger file
     */
    function getDesc()
    {
        return $this->description;
    }

    /**
     *   Return version of trigger file
     *   @return     string      Version of trigger file
     */
    function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'experimental') return $langs->trans("Experimental");
        elseif ($this->version == 'dolibarr') return DOL_VERSION;
        elseif ($this->version) return $this->version;
        else return $langs->trans("Unknown");
    }

    /**
     *      Function called when a Dolibarrr business event is done.
     *      All functions "run_trigger" are triggered if file is inside directory htdocs/includes/triggers
     *
     *      @param      action      Event code (COMPANY_CREATE, PROPAL_VALIDATE, ...)
     *      @param      object      Object action is done on
     *      @param      user        Object user
     *      @param      langs       Object langs
     *      @param      conf        Object conf
     *      @return     int         <0 if KO, 0 if no action are done, >0 if OK
     */
    function run_trigger($action, $object, $user, $langs, $conf)
    {
		$ok = 0;
		
		if (is_object($langs))
		{
			$langs->load("other");
			$langs->load('ndfp@ndfp');
			$langs->load("agenda");		
		}


		if ($conf->fournisseur->enabled && $conf->global->NDFP_AUTO_CLASSIFY)
		{
			if ($action == 'BILL_SUPPLIER_PAYED')
			{
				$object->fetchObjectLinked('', 'ndfp', $object->id, $object->element);

				if (count($object->linkedObjects['ndfp']) > 0)
				{
					$ndfp = array_shift($object->linkedObjects['ndfp']);
					$ndfp->set_paid($user);
					
				}							
			}
		}
		
		if ($action == 'NDFP_VALIDATE')
		{
			// Send mail if user different from author
			$fk_user_author = $object->fk_user_author;

			if ($fk_user_author != $user->id)
			{
				// Attached file
				$ref = dol_sanitizeFileName($object->ref);
				$filename = $ref . '.pdf';
				$file = $conf->ndfp->dir_output . '/' . $ref . '/' . $filename;

				if (! is_readable($file))
				{
					$result = $object->generate_pdf($user);
				}

				$arr_file = array();
				$arr_mime = array();
				$arr_name = array();

				$arr_file[] = $file;
				$arr_mime[] = dol_mimetype($filename);
				$arr_name[] = $filename;

				$substit = array(
					'__USER__' => $user->getFullName($langs),
					'__REF__' => $object->ref,
					'__SOCIETY__' => $conf->global->MAIN_INFO_SOCIETE_NOM
				);

				$message = $langs->transnoentities('NdfpValidationBody');
				$subject = $langs->transnoentities('NdfpValidationSubject');

				$subject = make_substitutions($subject, $substit);           
				$message = make_substitutions($message, $substit);
				
				$message = str_replace('\n',"<br />", $message);



				$from = $user->getFullName($langs);
				$from = $from .'<'.$user->email.'>';
				
				
				$u = new User($this->db);
				$u->fetch($fk_user_author);
				// Get email
				$email = $u->email;

				$to = $u->getFullName($langs);
				$to = $to .'<'.$email.'>';
				
				$mail = new CMailFile($subject, $to, $from, $message,  $arr_file, $arr_mime, $arr_name, '', '', 0, 1);
				$mail->sendfile();
			}
		}

		if ($action == 'NDFP_CANCEL')
		{
			// Send mail if user different from author
			$fk_user_author = $object->fk_user_author;

			if ($fk_user_author != $user->id)
			{
				// Attached file
				$ref = dol_sanitizeFileName($object->ref);
				$filename = $ref . '.pdf';
				$file = $conf->ndfp->dir_output . '/' . $ref . '/' . $filename;

				if (! is_readable($file))
				{
					$result = $object->generate_pdf($user);
				}

				$arr_file = array();
				$arr_mime = array();
				$arr_name = array();

				$arr_file[] = $file;
				$arr_mime[] = dol_mimetype($filename);
				$arr_name[] = $filename;

				$substit = array(
					'__USER__' => $user->getFullName($langs),
					'__REF__' => $object->ref,
					'__SOCIETY__' => $conf->global->MAIN_INFO_SOCIETE_NOM
				);

				$message = $langs->transnoentities('NdfpRejectionBody');
				$subject = $langs->transnoentities('NdfpValidationSubject');

				$subject = make_substitutions($subject, $substit);           
				$message = make_substitutions($message, $substit);
				
				$message = str_replace('\n',"<br />", $message);


				$from = $user->getFullName($langs);
				$from = $from .'<'.$user->email.'>';
				
				
				$u = new User($this->db);
				$u->fetch($fk_user_author);
				// Get email
				$email = $u->email;

				$to = $u->getFullName($langs);
				$to = $to .'<'.$email.'>';
				
				$mail = new CMailFile($subject, $to, $from, $message,  $arr_file, $arr_mime, $arr_name, '', '', 0, 1);
				$mail->sendfile();

			}
		}		

        if ($conf->agenda->enabled)
        {
         	if ($action == 'NDFP_VALIDATE' && $conf->global->NDFP_AGENDA_ACTIONAUTO_NDFP_VALIDATE)
         	{
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

				$object->actiontypecode = 'AC_OTH_AUTO';
				$object->actionmsg2 = $langs->transnoentities("NdfpValidatedInDolibarr",$object->ref);
				$object->actionmsg = $langs->transnoentities("NdfpValidatedInDolibarr",$object->ref);
				$object->actionmsg.= "\n".$langs->transnoentities("Author").': '.$user->login;

				$object->sendtoid = 0;
				$ok = 1;    
				
				
         	}
         	
         	if ($action == 'NDFP_SENTBYMAIL' && $conf->global->NDFP_AGENDA_ACTIONAUTO_NDFP_SENTBYMAIL)
         	{
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

				$object->actiontypecode = 'AC_EMAIL';
				$object->actionmsg2 = $langs->transnoentities("NdfpSentByEMail",$object->ref);
				$object->actionmsg = $langs->transnoentities("NdfpSentByEMail",$object->ref);
				$object->actionmsg.= "\n".$langs->transnoentities("Author").': '.$user->login;
				
				$object->sendtoid = 0;
				$ok = 1;         	
         	}
         	
         	if ($action == 'NDFP_PAID' && $conf->global->NDFP_AGENDA_ACTIONAUTO_NDFP_PAID)
         	{
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

				$object->actiontypecode = 'AC_OTH_AUTO';
				$object->actionmsg2 = $langs->transnoentities("NdfpPaidInDolibarr",$object->ref);
				$object->actionmsg = $langs->transnoentities("NdfpPaidInDolibarr",$object->ref);
				$object->actionmsg.= "\n".$langs->transnoentities("Author").': '.$user->login;
				
				$object->sendtoid = 0;
				$ok = 1;         	
         	}
         	
         	if ($action == 'NDFP_CANCEL' && $conf->global->NDFP_AGENDA_ACTIONAUTO_NDFP_CANCEL)
         	{
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

				$object->actiontypecode='AC_OTH_AUTO';
            	$object->actionmsg2=$langs->transnoentities("NdfpCanceledInDolibarr",$object->ref);
            	$object->actionmsg=$langs->transnoentities("NdfpCanceledInDolibarr",$object->ref);
            	$object->actionmsg.="\n".$langs->transnoentities("Author").': '.$user->login;
				
				$object->sendtoid = 0;
				$ok = 1; 
         	}
         	
			// Add entry in event table
			if ($ok)
			{
				$now = dol_now();


				$contactforaction = new Contact($this->db);
		        $societeforaction = new Societe($this->db);
		        if ($object->fk_soc > 0)    $societeforaction->fetch($object->fk_soc);

				// Insertion action
				
				$actioncomm = new ActionComm($this->db);
				$actioncomm->type_code   = $object->actiontypecode;		// code of parent table llx_c_actioncomm (will be deprecated)
				$actioncomm->code 		 = 'AC_'.$action;
				$actioncomm->label       = $object->actionmsg2;
				$actioncomm->note        = $object->actionmsg;
				$actioncomm->datep       = $now;
				$actioncomm->datef       = $now;
				$actioncomm->durationp   = 0;
				$actioncomm->punctual    = 1;
				$actioncomm->percentage  = -1;   // Not applicable
				$actioncomm->societe     = $societeforaction;
				$actioncomm->contact     = $contactforaction;
				$actioncomm->socid       = $societeforaction->id;
				$actioncomm->contactid   = $contactforaction->id;
				$actioncomm->authorid    = $user->id;   // User saving action
				$actioncomm->userownerid = $user->id;	// Owner of action
				//$actioncomm->userdone    = $user;	    // User doing action (not used anymore)
				//$actioncomm->userdoneid  = $user->id;	// User doing action (not used anymore)

				$actioncomm->fk_element  = $object->id;
				$actioncomm->elementtype = $object->element;

				$ret = $actioncomm->add($user);       // User qui saisit l'action

				if ($ret <= 0)
				{
					$error = "Failed to insert : ".$actioncomm->error." ";
					$this->error=$error;

					dol_syslog("interface_modAgenda_ActionsAuto.class.php: ".$this->error, LOG_ERR);
				}
			}         	
        }
		
		
		return 1;
    }

}
?>
