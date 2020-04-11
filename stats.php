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
 *	\file       htdocs/ndfp/stats.php
 *	\ingroup    ndfp
 *	\brief      Page to display credit notes stats
 */

$res=@include("../main.inc.php");					// For root directory
if (! $res) $res=@include("../../main.inc.php");	// For "custom" directory

require_once(DOL_DOCUMENT_ROOT."/core/class/dolgraph.class.php");

dol_include_once("/ndfp/class/ndfp.class.php");
dol_include_once("/ndfp/class/ndfp.stats.class.php");
dol_include_once("/ndfp/lib/ndfp.lib.php");


$width = 500;
$height = 200;

$langs->load("ndfp@ndfp");
$langs->load('main');
$langs->load('other');

if (!$user->rights->ndfp->myactions->read && !$user->rights->ndfp->allactions->read)
{
    accessforbidden();
}

$message = false;
$error = false;


$yearsArray = array();
$oldYear = 0;
// Get parameters
$userid = GETPOST('userid','int'); 
$socid = GETPOST('socid','int'); 
$source = GETPOST('source', 'alpha') ? GETPOST('source', 'alpha') : 'months';

if ($socid < 0)
{
	$socid = 0;
}

if (!$user->rights->ndfp->allactions->read)
{
	$userid = $user->id;
}

/*
if ($userid < 0)
{
	$userid = 0;
}*/
// Security check
if ($user->societe_id > 0)
{
    $action = '';
    $socid = $user->societe_id;
}

$now  = dol_now();
$currentYear = strftime("%Y", $now);
$previousYear = $currentYear - 1;

$dyear = GETPOST('dyear','int') ? GETPOST('dyear','int') : 0; 
$fyear = GETPOST('fyear','int') ? GETPOST('fyear','int') : 0; 

$dmonth = GETPOST('dmonth','int'); 
$dday = GETPOST('dday','int'); 
$fmonth = GETPOST('fmonth','int'); 
$fday = GETPOST('fday','int'); 


$searchStartYear = $dyear;
$searchEndYear = $fyear;
$previousStartYear = $searchStartYear - 1;
$previousEndYear = $searchEndYear - 1;

if ($previousStartYear > 0)
{
	$startDate = dol_mktime(12, 0 , 0, $dmonth, $dday, $searchStartYear);
	$endDate = dol_mktime(12, 0 , 0, $fmonth, $fday, $searchEndYear);
	$previousStartDate = dol_mktime(12, 0 , 0, $dmonth, $dday, $previousStartYear);
	$previousEndDate = dol_mktime(12, 0 , 0, $fmonth, $fday, $previousEndYear);

}
else
{
	$searchStartYear = $currentYear;
	$previousStartYear = $previousYear;

	$startDate = $searchStartYear ? $searchStartYear : dol_get_first_day($currentYear);
	$endDate = $searchEndYear ? $searchEndYear : dol_get_last_day($currentYear);
	$previousStartDate = $previousStartDate ? $previousStartDate : dol_get_first_day($previousYear);
	$previousEndDate = $previousEndDate ? $previousEndDate : dol_get_last_day($previousYear);
}


$filter = '&userid='.$userid.'&socid='.$socid.'&dyear='.$dyear.'&fyear='.$fyear;
$filter.= '&dmonth='.$dmonth.'&dday='.$dday.'&fmonth='.$fmonth.'&fday='.$fday;

//$year = GETPOST('year')>0 ? GETPOST('year'): strftime("%Y", $now);

//$startYear = $year-1;
//$endYear = $year;

// Define output dir
$dir = $conf->ndfp->dir_output.'/temp';
dol_mkdir($dir);


// Get data
$stats = new NdfpStats($db, $socid, $userid);

// Build graphic number of object
if ($source == 'months')
{
	$dataNumber = $stats->getNumberOfNotesByMonth($startDate, $endDate, $previousStartDate, $previousEndDate);
	$dataAmount = $stats->getTotalOfNotesByMonth($startDate, $endDate, $previousStartDate, $previousEndDate);
	$dataAverage = $stats->getAverageOfNotesByMonth($startDate, $endDate, $previousStartDate, $previousEndDate);
	list($dataAll, $totalAll) = $stats->getAllOfNotesByMonth($startDate, $endDate, $previousStartDate, $previousEndDate);
}
else if ($source == 'categories')
{
	$dataNumber = $stats->getNumberOfNotesByCategory($startDate, $endDate, $previousStartDate, $previousEndDate);
	$dataAmount = $stats->getTotalOfNotesByCategory($startDate, $endDate, $previousStartDate, $previousEndDate);
	$dataAverage = $stats->getAverageOfNotesByCategory($startDate, $endDate, $previousStartDate, $previousEndDate);
	list($dataAll, $totalAll) = $stats->getAllOfNotesByCategory($startDate, $endDate, $previousStartDate, $previousEndDate);
}
else
{
	$dataNumber = $stats->getNumberOfNotesByUser($startDate, $endDate, $previousStartDate, $previousEndDate);
	$dataAmount = $stats->getTotalOfNotesByUser($startDate, $endDate, $previousStartDate, $previousEndDate);
	$dataAverage = $stats->getAverageOfNotesByUser($startDate, $endDate, $previousStartDate, $previousEndDate);
	list($dataAll, $totalAll) = $stats->getAllOfNotesByUser($startDate, $endDate, $previousStartDate, $previousEndDate);
}

// Create images
$outputNumberByMonthImage = "notes_number_by_source_year-".$searchStartYear.".png";
$fileNumberByMonthName = $dir ."/" .$outputNumberByMonthImage;
$fileNumberByMonthUrl = DOL_URL_ROOT.'/viewimage.php?modulepart=ndfp_temp&amp;file='.$outputNumberByMonthImage;

$outputAmountByMonthImage = "notes_amount_by_source_year-".$searchStartYear.".png";
$fileAmountByMonthName = $dir ."/" .$outputAmountByMonthImage;
$fileAmountByMonthUrl = DOL_URL_ROOT.'/viewimage.php?modulepart=ndfp_temp&amp;file='.$outputAmountByMonthImage;

$outputAverageByMonthImage = "notes_average_amount_by_source_year-".$searchStartYear.".png";
$fileAverageByMonthName = $dir ."/" .$outputAverageByMonthImage;
$fileAverageByMonthUrl = DOL_URL_ROOT.'/viewimage.php?modulepart=ndfp_temp&amp;file='.$outputAverageByMonthImage;

$graphNumberOutput = '';
$graphAmountOutput = '';
$graphAverageOutput = '';

// Create graphs
$graphNumber = new DolGraph();
$graphAmount = new DolGraph();
$graphAverage = new DolGraph();

$message = $graphNumber->isGraphKo();
if (! $message)
{
	$graphNumber->SetData($dataNumber);
	$graphNumber->SetPrecisionY(0);
	
	$legend = array($searchStartYear, $previousStartYear);
	/*$i = $startYear;
	while ($i <= $endYear)
	{
		$legend[] = $i;
		$i++;
	}*/
	
	$graphNumber->SetLegend($legend);
	$graphNumber->SetMaxValue($graphNumber->GetCeilMaxValue());
	$graphNumber->SetWidth($width);
	$graphNumber->SetHeight($height);
	$graphNumber->SetYLabel($langs->trans("NumberOfNotes"));
	$graphNumber->SetShading(3);
	$graphNumber->SetHorizTickIncrement(1);
	$graphNumber->SetPrecisionY(0);
	$graphNumber->mode='depth';
	if ($source == 'months')
	{
		$graphNumber->SetTitle($langs->trans("NumberOfNotesByMonth"));
	}
	else if ($source == 'categories')
	{
		$graphNumber->SetTitle($langs->trans("NumberOfNotesByCategory"));
	}
	else
	{
		$graphNumber->SetTitle($langs->trans("NumberOfNotesByUser"));
	}
	

	$graphNumber->draw($fileNumberByMonthName, $fileNumberByMonthUrl);
	
	$graphNumberOutput = $graphNumber->show();
}
else
{
	$error = true;
}

$message = $graphAmount->isGraphKo();
if (! $message)
{
	$graphAmount->SetData($dataAmount);
	$graphAmount->SetPrecisionY(0);
	
	$legend = array($searchStartYear, $previousStartYear);
	
	$graphAmount->SetLegend($legend);
	$graphAmount->SetMaxValue($graphAmount->GetCeilMaxValue());
	$graphAmount->SetWidth($width);
	$graphAmount->SetHeight($height);
	$graphAmount->SetYLabel($langs->trans("AmountNotes"));
	$graphAmount->SetShading(3);
	$graphAmount->SetHorizTickIncrement(1);
	$graphAmount->SetPrecisionY(0);
	$graphAmount->mode='depth';
	if ($source == 'months')
	{
		$graphAmount->SetTitle($langs->trans("AmountOfNotesByMonth"));
	}
	else if ($source == 'categories')
	{
		$graphAmount->SetTitle($langs->trans("AmountOfNotesByCategory"));
	}
	else
	{
		$graphAmount->SetTitle($langs->trans("AmountOfNotesByUser"));
	}

	$graphAmount->draw($fileAmountByMonthName, $fileAmountByMonthUrl);
	
	$graphAmountOutput = $graphAmount->show();
}
else
{
	$error = true;
}

$message = $graphAverage->isGraphKo();
if (! $message)
{
	$graphAverage->SetData($dataAverage);
	$graphAverage->SetPrecisionY(0);
	
	$legend = array($searchStartYear, $previousStartYear);
	//$legend[] = $endYear; // Display average only for this year

	
	$graphAverage->SetLegend($legend);
	$graphAverage->SetMaxValue($graphAverage->GetCeilMaxValue());
	$graphAverage->SetWidth($width);
	$graphAverage->SetHeight($height);
	$graphAverage->SetYLabel($langs->trans("AverageAmountNotes"));
	$graphAverage->SetShading(3);
	$graphAverage->SetHorizTickIncrement(1);
	$graphAverage->SetPrecisionY(0);
	$graphAverage->mode='depth';
	if ($source == 'months')
	{
		$graphAverage->SetTitle($langs->trans("AverageAmountOfNotesByMonth"));
	}
	else if ($source == 'categories')
	{
		$graphAverage->SetTitle($langs->trans("AverageAmountOfNotesByCategory"));
	}
	else
	{
		$graphAverage->SetTitle($langs->trans("AverageAmountOfNotesByUser"));
	}

	$graphAverage->draw($fileAverageByMonthName, $fileAverageByMonthUrl);
	
	$graphAverageOutput = $graphAverage->show();
}
else
{
	$error = true;
}

	
// View
$html = new Form($db);

$title = $langs->trans("NdfpStatistics");

// Prepare head
$head = ndfpstats_prepare_head($filter);
$current_head = $source;

include 'tpl/stats.default.tpl.php';

$db->close();

?>