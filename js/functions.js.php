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
 *	\file       htdocs/ndfp/js/functions.php
 *	\ingroup    ndfp
 *	\brief      Javascript functions to create a note
 */
define('NOTOKENRENEWAL', 1);

$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory

$lineid = GETPOST('rowid') ? GETPOST('rowid', 'int') : 0;

$db->close();
?>

$(document).ready(function() {

	$('#qty').bind('keyup', null, function(){	

		updateTotals();
   		
		return true;
	});
	
	$('#milestone').bind('change', null, function(){	
		
		var value = $(this).val();

		if (value == '1')
		{	
			$("#total_ttc").attr('disabled', 'disabled');
			$("#total_ht").attr('disabled', 'disabled');
			$("#fk_tva").attr('disabled', 'disabled');
			
		}
		else
		{
			$("#fk_tva").removeAttr('disabled');
			$("#total_ttc").removeAttr('disabled');  
			$("#total_ht").removeAttr('disabled'); 		
		}
   		
		return true;
	});
	
	$('#total_ttc').bind('keyup', null, function(){	
		
		updateHT();
   		
		return true;
	});

	$('#total_ht').bind('keyup', null, function(){	
		
		updateTTC();
   		
		return true;
	});

	$('#fk_exp').bind('change', null, function(){	
		
		updateTVA();

		$("#qty").val('1');   		
		$("#total_ht").val('0,00');
		$("#total_ttc").val('0,00');
		   		
		return true;
	});	
	
	$('#fk_tva_det').bind('change', null, function(){	
		
		updateMilestoneTVA();

		return true;
	});
		
	$('#fk_tva').bind('change', null, function(){	

		updateHT();
		   		
		return true;
	});

		
	$("select[name='fk_ndfp_tax_det']").change(function(){	
		
		updateMilestoneTVA();
		   		
		return true;
	});

	$("select[name='fk_cat']").change(function(){	
		
		updatePreviousExp();
		   		
		return;
	});

	$('#previousexp').bind('change', null, function(){	

		updateTotals();
   		
		return true;
	});

	updateMilestoneTVA();
				
});


function updateHT()
{
	if ($( "#addexpense" ).length) {
		$.post( "<?php echo dol_buildpath('/ndfp/ajax.php?action=updateht', 1); ?>", $( "#addexpense" ).serialize(), function( data ) {
		
			var typeData = jQuery.parseJSON(data);
			$('#total_ht').val(typeData.totalht);  
		});
	}
}

function updateTTC()
{
	if ($( "#addexpense" ).length) {
		$.post( "<?php echo dol_buildpath('/ndfp/ajax.php?action=updatettcfromht', 1); ?>", $( "#addexpense" ).serialize(), function( data ) {
		
			var typeData = jQuery.parseJSON(data);
			$('#total_ttc').val(typeData.totalttc);  
		});
	}
}

function updateTotals()
{
	if ($( "#addexpense" ).length) {
		$.post( "<?php echo dol_buildpath('/ndfp/ajax.php?action=updatettc', 1); ?>", $( "#addexpense" ).serialize(), function( data ) {

			var typeData = jQuery.parseJSON(data);
			$('#total_ttc').val(typeData.totalttc);
			$('#total_ht').val(typeData.totalht);   
		});
	}
}

function updateTVA()
{
	var value = $('#fk_exp').val();

	$.get("<?php echo dol_buildpath('/ndfp/ajax.php?action=gettva&fk_exp=', 1); ?>"+value, function(data) {
		
		var typeData = jQuery.parseJSON(data);
		$('#fk_tva option[value="'+parseFloat(typeData.tva)+'"]').prop('selected', true);

		return true;		
	});
}

function updatePreviousExp()
{
	if ($( "#addexpense" ).length) {
		$.post( "<?php echo dol_buildpath('/ndfp/ajax.php?action=getfees', 1); ?>", $( "#addexpense" ).serialize(), function( data ) {

		var typeData = jQuery.parseJSON(data);
		$('#previous_exp').val(typeData.previousexp);  

		updateTotals();
		});
	}

    if ($( "#createexpense" ).length) {
        $.post( "<?php echo dol_buildpath('/ndfp/ajax.php?action=getfees', 1); ?>", $( "#createexpense" ).serialize(), function( data ) {

            var typeData = jQuery.parseJSON(data);
            $('#previous_exp').val(typeData.previousexp);
        });
    }
}

function updateMilestoneTVA()
{
	if ($( "#addtvaline" ).length) {
		$.post( "<?php echo dol_buildpath('/ndfp/ajax.php?action=updatetva', 1); ?>", $( "#addtvaline" ).serialize(), function( data ) {

		var typeData = jQuery.parseJSON(data);
		$('#total_tva').val(typeData.totaltva);  
		});	
	}
}