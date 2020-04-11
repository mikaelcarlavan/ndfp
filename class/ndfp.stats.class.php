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
 *      \file       htdocs/ndfp/class/ndfp.stats.class.php
 *      \ingroup    ndfp
 *      \brief      File of class to credit notes statistics
 */

include_once DOL_DOCUMENT_ROOT . "/core/lib/date.lib.php";
dol_include_once('/ndfp/class/ndfp.class.php');

/**
 *      \class      NdfpStats
 *      \brief      Class to manage credit notes statistics
 */
class NdfpStats
{
    var $socid;
    var $userid;
	
	/**
     * 	Constructor
     *
	 * 	@param	DoliDB		$db			Database handler
	 * 	@param 	int			$socid		Id third party
     * 	@param	int			$userid    	Id user for filter
	 * 	@return NdfpStats
	 */
	function __construct($db, $socid, $userid = 0)
	{
		global $conf;

		$this->db = $db;
        $this->socid = $socid;
        $this->userid = $userid;		
	}

 	/**
	 * 	\brief Return the number of notes by month for a given year
	 *
	 *	@return	array			Array of values
	 */
	function getNumberOfNotesByMonth($startDate, $endDate, $previousStartDate, $previousEndDate)
	{
		global $langs;

		$results = array();
		$data = array();

		$results[0]  = $this->_getNumberOfNotesByMonth($startDate, $endDate);
		$results[1]  = $this->_getNumberOfNotesByMonth($previousStartDate, $previousEndDate);	

		for ($i = 0; $i < 12; $i++)
		{
			$data[$i][] = $langs->transnoentitiesnoconv('MonthShort'.sprintf("%02d", $i+1));
			$data[$i][] = isset($results[0][$i]) ? $results[0][$i] : 0;
			$data[$i][] = isset($results[1][$i]) ? $results[1][$i] : 0;

		}

		return $data;
	}

 	/**
	 * 	\brief Return the total of notes by month for a given year
	 *
	 *	@return	array			Array of values
	 */
	function getTotalOfNotesByMonth($startDate, $endDate, $previousStartDate, $previousEndDate)
	{
		global $langs;

		$results = array();
		$data = array();

		$results[0]  = $this->_getTotalOfNotesByMonth($startDate, $endDate);
		$results[1]  = $this->_getTotalOfNotesByMonth($previousStartDate, $previousEndDate);	

		for ($i = 0; $i < 12; $i++)
		{
			$data[$i][] = $langs->transnoentitiesnoconv('MonthShort'.sprintf("%02d", $i+1));
			$data[$i][] = isset($results[0][$i]) ? $results[0][$i] : 0;
			$data[$i][] = isset($results[1][$i]) ? $results[1][$i] : 0;

		}

		return $data;
	}

	/**
	 * 	\brief Return the total of notes by month for a given year
	 *
	 *	@return	array			Array of values
	 */
	function getAverageOfNotesByMonth($startDate, $endDate, $previousStartDate, $previousEndDate)
	{
		global $langs;

		$results = array();
		$data = array();

		$results[0]  = $this->_getAverageOfNotesByMonth($startDate, $endDate);
		$results[1]  = $this->_getAverageOfNotesByMonth($previousStartDate, $previousEndDate);

		for ($i = 0; $i < 12; $i++)
		{
			$data[$i][] = $langs->transnoentitiesnoconv('MonthShort'.sprintf("%02d", $i+1));
			$data[$i][] = isset($results[0][$i]) ? $results[0][$i] : 0;
			$data[$i][] = isset($results[1][$i]) ? $results[1][$i] : 0;

		}

		return $data;
	}

	/**
	 * 	\brief Return the total of notes by month for a given year
	 *
	 *	@return	array			Array of values
	 */
	function getAllOfNotesByMonth($startDate, $endDate, $previousStartDate, $previousEndDate)
	{
		global $langs;

		$results = array();
		$data = array();

		$results[0]  = $this->_getAllOfNotesByMonth($startDate, $endDate);
		$results[1]  = $this->_getAllOfNotesByMonth($previousStartDate, $previousEndDate);
		
		for ($i = 0; $i < 12; $i++)
		{
			$data[$i][] = $langs->transnoentitiesnoconv('MonthShort'.sprintf("%02d", $i+1));
			$data[$i][] = isset($results[0][$i]) ? $results[0][$i] : array(0,0,0);
			$data[$i][] = isset($results[1][$i]) ? $results[1][$i] : array(0,0,0);
		}

		$total = array(array(0,0), array(0,0));
		for ($i = 0; $i < 12; $i++)
		{
			$total[0][0] += $data[$i][1][0];
			$total[0][1] += $data[$i][1][1];
			$total[1][0] += $data[$i][2][0];
			$total[1][1] += $data[$i][2][1];
		}

		return array($data, $total);
	}

 	/**
	 * 	\brief Return the number of notes by category for a given year
	 *
	 *	@return	array			Array of values
	 */
	function getNumberOfNotesByCategory($startDate, $endDate, $previousStartDate, $previousEndDate)
	{
		global $langs;

		$results = array();
		$data = array();

		$results[0]  = $this->_getNumberOfNotesByCategory($startDate, $endDate);
		$results[1]  = $this->_getNumberOfNotesByCategory($previousStartDate, $previousEndDate);	

		$categories = $this->_getCategories();
		if (count($categories))
		{
			$i = 0;
			foreach ($categories as $id => $label)
			{
				$data[$i][] = $langs->transnoentitiesnoconv($label);
				$data[$i][] = isset($results[0][$id]) ? $results[0][$id] : 0;
				$data[$i][] = isset($results[1][$id]) ? $results[1][$id] : 0;

				$i++;
			}
		}

		return $data;
	}

 	/**
	 * 	\brief Return the total of notes by category for a given year
	 *
	 *	@return	array			Array of values
	 */
	function getTotalOfNotesByCategory($startDate, $endDate, $previousStartDate, $previousEndDate)
	{
		global $langs;

		$results = array();
		$data = array();

		$results[0]  = $this->_getTotalOfNotesByCategory($startDate, $endDate);
		$results[1]  = $this->_getTotalOfNotesByCategory($previousStartDate, $previousEndDate);	

		$categories = $this->_getCategories();
		if (count($categories))
		{
			$i = 0;
			foreach ($categories as $id => $label)
			{
				$data[$i][] = $langs->transnoentitiesnoconv($label);
				$data[$i][] = isset($results[0][$id]) ? $results[0][$id] : 0;
				$data[$i][] = isset($results[1][$id]) ? $results[1][$id] : 0;

				$i++;
			}
		}

		return $data;
	}

	/**
	 * 	\brief Return the total of notes by category for a given year
	 *
	 *	@return	array			Array of values
	 */
	function getAverageOfNotesByCategory($startDate, $endDate, $previousStartDate, $previousEndDate)
	{
		global $langs;

		$results = array();
		$data = array();

		$results[0]  = $this->_getAverageOfNotesByCategory($startDate, $endDate);
		$results[1]  = $this->_getAverageOfNotesByCategory($previousStartDate, $previousEndDate);

		$categories = $this->_getCategories();
		if (count($categories))
		{
			$i = 0;
			foreach ($categories as $id => $label)
			{
				$data[$i][] = $langs->transnoentitiesnoconv($label);
				$data[$i][] = isset($results[0][$id]) ? $results[0][$id] : 0;
				$data[$i][] = isset($results[1][$id]) ? $results[1][$id] : 0;

				$i++;
			}
		}

		return $data;
	}

	/**
	 * 	\brief Return the total of notes by category for a given year
	 *
	 *	@return	array			Array of values
	 */
	function getAllOfNotesByCategory($startDate, $endDate, $previousStartDate, $previousEndDate)
	{
		global $langs;

		$results = array();
		$data = array();

		
		$results[0]  = $this->_getAllOfNotesByCategory($startDate, $endDate);
		$results[1]  = $this->_getAllOfNotesByCategory($previousStartDate, $previousEndDate);

		$categories = $this->_getCategories();
		$i = 0;
		if (count($categories))
		{
			foreach ($categories as $id => $label)
			{
				$data[$i][] = $langs->transnoentitiesnoconv($label);
				$data[$i][] = isset($results[0][$id]) ? $results[0][$id] : array(0,0,0);
				$data[$i][] = isset($results[1][$id]) ? $results[1][$id] : array(0,0,0);

				$i++;
			}
		}

		$total = array(array(0,0), array(0,0));
		for ($k = 0; $k < $i; $k++)
		{
			$total[0][0] += $data[$k][1][0];
			$total[0][1] += $data[$k][1][1];
			$total[1][0] += $data[$k][2][0];
			$total[1][1] += $data[$k][2][1];
		}

		return array($data, $total);
	}

 	/**
	 * 	\brief Return the number of notes by user for a given year
	 *
	 *	@return	array			Array of values
	 */
	function getNumberOfNotesByUser($startDate, $endDate, $previousStartDate, $previousEndDate)
	{
		global $langs;

		$results = array();
		$data = array();

		$results[0]  = $this->_getNumberOfNotesByUser($startDate, $endDate);
		$results[1]  = $this->_getNumberOfNotesByUser($previousStartDate, $previousEndDate);

		$users = $this->_getUsers();
		if (count($users))
		{
			$i = 0;
			foreach ($users as $id => $user)
			{
				$data[$i][] = $user;
				$data[$i][] = isset($results[0][$id]) ? $results[0][$id] : 0;
				$data[$i][] = isset($results[1][$id]) ? $results[1][$id] : 0;

				$i++;
			}
		}

		return $data;
	}

 	/**
	 * 	\brief Return the total of notes by user for a given year
	 *
	 *	@return	array			Array of values
	 */
	function getTotalOfNotesByUser($startDate, $endDate, $previousStartDate, $previousEndDate)
	{
		global $langs;

		$results = array();
		$data = array();

		$results[0]  = $this->_getTotalOfNotesByUser($startDate, $endDate);
		$results[1]  = $this->_getTotalOfNotesByUser($previousStartDate, $previousEndDate);

		$users = $this->_getUsers();
		if (count($users))
		{
			$i = 0;
			foreach ($users as $id => $user)
			{
				$data[$i][] = $user;
				$data[$i][] = isset($results[0][$id]) ? $results[0][$id] : 0;
				$data[$i][] = isset($results[1][$id]) ? $results[1][$id] : 0;

				$i++;
			}
		}

		return $data;
	}

	/**
	 * 	\brief Return the total of notes by user for a given year
	 *
	 *	@return	array			Array of values
	 */
	function getAverageOfNotesByUser($startDate, $endDate, $previousStartDate, $previousEndDate)
	{
		global $langs;

		$results = array();
		$data = array();

		$results[0]  = $this->_getAverageOfNotesByUser($startDate, $endDate);
		$results[1]  = $this->_getAverageOfNotesByUser($previousStartDate, $previousEndDate);

		$users = $this->_getUsers();
		if (count($users))
		{
			$i = 0;
			foreach ($users as $id => $user)
			{
				$data[$i][] = $user;
				$data[$i][] = isset($results[0][$id]) ? $results[0][$id] : 0;
				$data[$i][] = isset($results[1][$id]) ? $results[1][$id] : 0;

				$i++;
			}
		}

		return $data;
	}

	/**
	 * 	\brief Return the total of notes by user for a given year
	 *
	 *	@return	array			Array of values
	 */
	function getAllOfNotesByUser($startDate, $endDate, $previousStartDate, $previousEndDate)
	{
		global $langs;

		$results = array();
		$data = array();

		
		$results[0]  = $this->_getAllOfNotesByUser($startDate, $endDate);
		$results[1]  = $this->_getAllOfNotesByUser($previousStartDate, $previousEndDate);

		$users = $this->_getUsers();
		$i = 0;
		if (count($users))
		{
			foreach ($users as $id => $user)
			{		
				if (isset($results[0][$id]) || isset($results[1][$id])) {
					$data[$i][] = $user;
					$data[$i][] = isset($results[0][$id]) ? $results[0][$id] : array(0,0,0);
					$data[$i][] = isset($results[1][$id]) ? $results[1][$id] : array(0,0,0);
					$i++;				
				}		
			}
		}

		$total = array(array(0,0), array(0,0));
		for ($k = 0; $k < $i; $k++)
		{
			$total[0][0] += $data[$k][1][0];
			$total[0][1] += $data[$k][1][1];
			$total[1][0] += $data[$k][2][0];
			$total[1][1] += $data[$k][2][1];
		}

		return array($data, $total);
	}

	/**
	 * 	\brief 
	 *
	 *	@return	array			Array of values
	 */
	function _getNumberOfNotesByMonth($startDate, $endDate)
	{
		global $langs;

		$results = array();

		$sql = "SELECT MONTH(n.dates) as dm, COUNT(n.total_ttc) as total";
		$sql.= " FROM ".MAIN_DB_PREFIX."ndfp n";
		$sql.= " WHERE n.dates BETWEEN '".$this->db->idate($startDate)."' AND '".$this->db->idate($endDate)."'";
		$sql.= $this->socid > 0 ? " AND n.fk_soc = ".$this->socid : "";
		$sql.= $this->userid > 0 ? " AND n.fk_user = ".$this->userid : "";
		$sql.= " GROUP BY dm";
        $sql.= $this->db->order('dm','DESC');
				
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$row = $this->db->fetch_object($resql);

				$month = $row->dm > 0 ? $row->dm : -1;
				
				if ($month > 0) {
					$results[$month-1] = $row->total;
				}
				

				$i++;
			}

			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
		}	

		return $results;
	}

	/**
	 * 	\brief 
	 *
	 *	@return	array			Array of values
	 */
	function _getTotalOfNotesByMonth($startDate, $endDate)
	{
		global $langs;

		$results = array();

		$sql = "SELECT MONTH(n.dates) as dm, SUM(n.total_ttc) as total";
		$sql.= " FROM ".MAIN_DB_PREFIX."ndfp n";
		$sql.= " WHERE n.dates BETWEEN '".$this->db->idate($startDate)."' AND '".$this->db->idate($endDate)."'";
		$sql.= $this->socid > 0 ? " AND n.fk_soc = ".$this->socid : "";
		$sql.= $this->userid > 0 ? " AND n.fk_user = ".$this->userid : "";
		$sql.= " GROUP BY dm";
        $sql.= $this->db->order('dm','DESC');
		
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$row = $this->db->fetch_object($resql);

				$month = $row->dm > 0 ? $row->dm : -1;
				
				if ($month > 0) {
					$results[$month-1] = $row->total;
				}
				

				$i++;
			}

			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
		}	

		return $results;
	}

	/**
	 * 	\brief 
	 *
	 *	@return	array			Array of values
	 */
	function _getAverageOfNotesByMonth($startDate, $endDate)
	{
		global $langs;

		$results = array();

		$sql = "SELECT MONTH(n.dates) as dm, AVG(n.total_ttc) as total";
		$sql.= " FROM ".MAIN_DB_PREFIX."ndfp n";
		$sql.= " WHERE n.dates BETWEEN '".$this->db->idate($startDate)."' AND '".$this->db->idate($endDate)."'";
		$sql.= $this->socid > 0 ? " AND n.fk_soc = ".$this->socid : "";
		$sql.= $this->userid > 0 ? " AND n.fk_user = ".$this->userid : "";
		$sql.= " GROUP BY dm";
        $sql.= $this->db->order('dm','DESC');
		
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$row = $this->db->fetch_object($resql);

				$month = $row->dm > 0 ? $row->dm : -1;
				
				if ($month > 0) {
					$results[$month-1] = $row->total;
				}
				

				$i++;
			}

			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
		}			

		return $results;
	}	

	/**
	 * 	\brief 
	 *
	 *	@return	array			Array of values
	 */
	function _getAllOfNotesByMonth($startDate, $endDate)
	{
		global $langs;

		$results = array();

		$sql = "SELECT MONTH(n.dates) as dm, COUNT(n.total_ttc) as num, SUM(n.total_ttc) as total, AVG(n.total_ttc) as moy";
		$sql.= " FROM ".MAIN_DB_PREFIX."ndfp n";
		$sql.= " WHERE n.dates BETWEEN '".$this->db->idate($startDate)."' AND '".$this->db->idate($endDate)."'";
		$sql.= $this->socid > 0 ? " AND n.fk_soc = ".$this->socid : "";
		$sql.= $this->userid > 0 ? " AND n.fk_user = ".$this->userid : "";
		$sql.= " GROUP BY dm";
        $sql.= $this->db->order('dm','DESC');
		
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$row = $this->db->fetch_object($resql);
				$month = $row->dm > 0 ? $row->dm : -1;
				
				if ($month > 0) {
					$results[$month-1] = array($row->num, $row->total, $row->moy);
				}
				
				$i++;
			}
			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
		}		

		return $results;
	}
	
	/**
	 * 	\brief 
	 *
	 *	@return	array			Array of values
	 */
	function _getNumberOfNotesByCategory($startDate, $endDate)
	{
		global $langs;

		$results = array();

		$sql = "SELECT nd.fk_exp, COUNT(nd.fk_ndfp) as total";
		$sql.= " FROM ".MAIN_DB_PREFIX."ndfp_det nd";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."ndfp n ON n.rowid = nd.fk_ndfp";
		$sql.= " WHERE n.dates BETWEEN '".$this->db->idate($startDate)."' AND '".$this->db->idate($endDate)."'";
		$sql.= $this->socid > 0 ? " AND n.fk_soc = ".$this->socid : "";
		$sql.= $this->userid > 0 ? " AND n.fk_user = ".$this->userid : "";
		$sql.= " GROUP BY nd.fk_exp";
        $sql.= $this->db->order('nd.fk_exp','ASC');
		
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$row = $this->db->fetch_object($resql);

				$fk_exp = $row->fk_exp > 0 ? $row->fk_exp : -1;
				
				if ($fk_exp > 0) {
					$results[$fk_exp] = $row->total;
				}
				

				$i++;
			}

			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
		}	

		return $results;
	}

	/**
	 * 	\brief 
	 *
	 *	@return	array			Array of values
	 */
	function _getTotalOfNotesByCategory($startDate, $endDate)
	{
		global $langs;

		$results = array();

		$sql = "SELECT nd.fk_exp, SUM(nd.total_ttc) as total";
		$sql.= " FROM ".MAIN_DB_PREFIX."ndfp_det nd";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."ndfp n ON n.rowid = nd.fk_ndfp";
		$sql.= " WHERE n.dates BETWEEN '".$this->db->idate($startDate)."' AND '".$this->db->idate($endDate)."'";
		$sql.= $this->socid > 0 ? " AND n.fk_soc = ".$this->socid : "";
		$sql.= $this->userid > 0 ? " AND n.fk_user = ".$this->userid : "";
		$sql.= " GROUP BY nd.fk_exp";
        $sql.= $this->db->order('nd.fk_exp','ASC');
		
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$row = $this->db->fetch_object($resql);

				$fk_exp = $row->fk_exp > 0 ? $row->fk_exp : -1;
				
				if ($fk_exp > 0) {
					$results[$fk_exp] = $row->total;
				}
				

				$i++;
			}

			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
		}		

		return $results;
	}

	/**
	 * 	\brief 
	 *
	 *	@return	array			Array of values
	 */
	function _getAverageOfNotesByCategory($startDate, $endDate)
	{
		global $langs;

		$results = array();

		$sql = "SELECT nd.fk_exp, AVG(nd.total_ttc) as total";
		$sql.= " FROM ".MAIN_DB_PREFIX."ndfp_det nd";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."ndfp n ON n.rowid = nd.fk_ndfp";
		$sql.= " WHERE n.dates BETWEEN '".$this->db->idate($startDate)."' AND '".$this->db->idate($endDate)."'";
		$sql.= $this->socid > 0 ? " AND n.fk_soc = ".$this->socid : "";
		$sql.= $this->userid > 0 ? " AND n.fk_user = ".$this->userid : "";
		$sql.= " GROUP BY nd.fk_exp";
        $sql.= $this->db->order('nd.fk_exp','ASC');
		
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$row = $this->db->fetch_object($resql);


				$fk_exp = $row->fk_exp > 0 ? $row->fk_exp : -1;
				
				if ($fk_exp > 0) {
					$results[$fk_exp] = $row->total;
				}
				

				$i++;
			}

			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
		}			

		return $results;
	}	

	/**
	 * 	\brief 
	 *
	 *	@return	array			Array of values
	 */
	function _getAllOfNotesByCategory($startDate, $endDate)
	{
		global $langs;

		$results = array();

		$sql = "SELECT nd.fk_exp, COUNT(nd.fk_ndfp) as num, SUM(nd.total_ttc) as total, AVG(nd.total_ttc) as moy";
		$sql.= " FROM ".MAIN_DB_PREFIX."ndfp_det nd";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."ndfp n ON n.rowid = nd.fk_ndfp";
		$sql.= " WHERE n.dates BETWEEN '".$this->db->idate($startDate)."' AND '".$this->db->idate($endDate)."'";
		$sql.= $this->socid > 0 ? " AND n.fk_soc = ".$this->socid : "";
		$sql.= $this->userid > 0 ? " AND n.fk_user = ".$this->userid : "";
		$sql.= " GROUP BY nd.fk_exp";
		$sql.= $this->db->order('nd.fk_exp','ASC');

		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$row = $this->db->fetch_object($resql);

				$fk_exp = $row->fk_exp > 0 ? $row->fk_exp : -1;
				
				if ($fk_exp > 0) {
					$results[$fk_exp] = array($row->num, $row->total, $row->moy);
				}
				
				$i++;
			}
			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
		}		

		return $results;
	}

	/**
	 * 	\brief 
	 *
	 *	@return	array			Array of values
	 */
	function _getCategories()
	{
		global $langs;

		$results = array();

		$sql = " SELECT e.rowid, e.code, e.label, e.fk_tva";
		$sql.= " FROM ".MAIN_DB_PREFIX."c_ndfp_exp AS e";
		$sql.= $this->db->order('e.label','ASC');

		
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$row = $this->db->fetch_object($resql);

				$results[$row->rowid] = $row->label;
				
				$i++;
			}
			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
		}		

		return $results;
	}

	/**
	 * 	\brief 
	 *
	 *	@return	array			Array of values
	 */
	function _getUsers()
	{
		global $langs;

		$results = array();

		$sql = " SELECT u.rowid";
		$sql.= " FROM ".MAIN_DB_PREFIX."user AS u";
		$sql.= $this->db->order('u.lastname','ASC');

		
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$row = $this->db->fetch_object($resql);
				$userstatic = new User($this->db);
				$userstatic->fetch($row->rowid);

				$results[$row->rowid] = $userstatic->getFullName($langs);
				
				$i++;
			}
			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
		}		

		return $results;
	}

	/**
	 * 	\brief 
	 *
	 *	@return	array			Array of values
	 */
	function _getNumberOfNotesByUser($startDate, $endDate)
	{
		global $langs;

		$results = array();

		$sql = "SELECT n.fk_user, COUNT(n.fk_user) as total";
		$sql.= " FROM ".MAIN_DB_PREFIX."ndfp n";
		$sql.= " WHERE n.dates BETWEEN '".$this->db->idate($startDate)."' AND '".$this->db->idate($endDate)."'";
		$sql.= $this->socid > 0 ? " AND n.fk_soc = ".$this->socid : "";
		//$sql.= $this->userid > 0 ? " AND n.fk_user = ".$this->userid : "";
		$sql.= " GROUP BY n.fk_user";
        $sql.= $this->db->order('n.fk_user','ASC');
		
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$row = $this->db->fetch_object($resql);

				$fk_user = $row->fk_user > 0 ? $row->fk_user : -1;
				
				if ($fk_user > 0) {
					$results[$fk_user] = $row->total;
				}
				

				$i++;
			}

			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
		}	

		return $results;
	}

	/**
	 * 	\brief 
	 *
	 *	@return	array			Array of values
	 */
	function _getTotalOfNotesByUser($startDate, $endDate)
	{
		global $langs;

		$results = array();

		$sql = "SELECT n.fk_user, SUM(n.total_ttc) as total";
		$sql.= " FROM ".MAIN_DB_PREFIX."ndfp n";
		$sql.= " WHERE n.dates BETWEEN '".$this->db->idate($startDate)."' AND '".$this->db->idate($endDate)."'";
		$sql.= $this->socid > 0 ? " AND n.fk_soc = ".$this->socid : "";
		//$sql.= $this->userid > 0 ? " AND n.fk_user = ".$this->userid : "";
		$sql.= " GROUP BY n.fk_user";
        $sql.= $this->db->order('n.fk_user','ASC');
		
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$row = $this->db->fetch_object($resql);

				$fk_user = $row->fk_user > 0 ? $row->fk_user : -1;
				
				if ($fk_user > 0) {
					$results[$fk_user] = $row->total;
				}
				

				$i++;
			}

			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
		}	

		return $results;
	}

	/**
	 * 	\brief 
	 *
	 *	@return	array			Array of values
	 */
	function _getAverageOfNotesByUser($startDate, $endDate)
	{
		global $langs;

		$results = array();

		$sql = "SELECT n.fk_user, AVG(n.total_ttc) as total";
		$sql.= " FROM ".MAIN_DB_PREFIX."ndfp n";
		$sql.= " WHERE n.dates BETWEEN '".$this->db->idate($startDate)."' AND '".$this->db->idate($endDate)."'";
		$sql.= $this->socid > 0 ? " AND n.fk_soc = ".$this->socid : "";
		//$sql.= $this->userid > 0 ? " AND n.fk_user = ".$this->userid : "";
		$sql.= " GROUP BY n.fk_user";
        $sql.= $this->db->order('n.fk_user','ASC');
		
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$row = $this->db->fetch_object($resql);

				$fk_user = $row->fk_user > 0 ? $row->fk_user : -1;
				
				if ($fk_user > 0) {
					$results[$fk_user] = $row->total;
				}
				

				$i++;
			}

			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
		}			

		return $results;
	}	

	/**
	 * 	\brief 
	 *
	 *	@return	array			Array of values
	 */
	function _getAllOfNotesByUser($startDate, $endDate)
	{
		global $langs;

		$results = array();

		$sql = "SELECT n.fk_user, COUNT(n.total_ttc) as num, SUM(n.total_ttc) as total, AVG(n.total_ttc) as moy";
		$sql.= " FROM ".MAIN_DB_PREFIX."ndfp n";
		$sql.= " WHERE n.dates BETWEEN '".$this->db->idate($startDate)."' AND '".$this->db->idate($endDate)."'";
		$sql.= $this->socid > 0 ? " AND n.fk_soc = ".$this->socid : "";
		//$sql.= $this->userid > 0 ? " AND n.fk_user = ".$this->userid : "";
		$sql.= " GROUP BY n.fk_user";
		$sql.= $this->db->order('n.fk_user','ASC');
		
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$row = $this->db->fetch_object($resql);
				$fk_user = $row->fk_user > 0 ? $row->fk_user : -1;
				
				if ($fk_user > 0) {
					$results[$fk_user] = array($row->num, $row->total, $row->moy);
				}
				
				$i++;
			}
			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
		}		

		return $results;
	}
}