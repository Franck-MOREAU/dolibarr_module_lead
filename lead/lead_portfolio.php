<?php
/*
 * Copyright (C) 2014-2016 Florian HENRY <florian.henry@atm-consulting.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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
 * \file lead/lead/list.php
 * \ingroup lead
 * \brief list of lead
 */
$res = @include '../../main.inc.php'; // For root directory
if (! $res)
	$res = @include '../../../main.inc.php'; // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once '../class/lead.class.php';
require_once '../lib/lead.lib.php';
require_once '../class/html.formlead.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT . '/volvo/class/reprise.class.php';
require_once DOL_DOCUMENT_ROOT . '/volvo/class/lead.extend.class.php';

global $user;

$reprise = new Reprise($db);

// Security check
if (! $user->rights->lead->read)
	accessforbidden();

$do_action = GETPOST('do_action','int');

$form = new Form($db);
$formlead = new FormLead($db);
$object = new Leadext($db);
$formother = new FormOther($db);

$title = 'Portefeuille d\'affaire';

llxHeader('', $title);

$filter['t.fk_user_resp'] =4;
$filter['t.fk_c_status !IN'] = '6,7,11';

$resql = $object->fetch_all($sortorder, $sortfield, $conf->liste_limit, $offset, $filter);
$mid = $resql/2;

if ($resql != - 1) {
	print '<script>';
	print 'function allowDrop(ev) {';
    print 'ev.preventDefault();';
	print '}';

	print 'function drag(ev,ui) {';
    print 'ev.dataTransfer.setData("element", ev.target.id);';
    print '}';

	print 'function drop(ev, source) {';
    print 'ev.preventDefault();';
    print 'var element = ev.dataTransfer.getData("element");';
    print 'var dest = ev.target.className;';
    print "if (ev.target.className.indexOf('cal_event cal_event_busy')!=-1){";
    print 'dest = ev.target.parentNode.id;';
    print 'ev.target.parentNode.appendChild(document.getElementById(element));';
    print '}';
    print "if (ev.target.className.indexOf('dropper')!=-1){";
    print 'dest = ev.target.id;';
    print 'ev.target.appendChild(document.getElementById(element));';
    print '}';
    print '$.ajax({';
    print 'method: "POST",';
    print 'url: "dragdrop.php",';
    print 'data: { nom: element, org: dest }';
  	print '})';
  	print '.done(function(msg) {';
   	print 'alert( "Data Saved: " + msg );';
   	print '});';
	print '}';
	print '</script>';


	print '<table class="border" width="100%">';
	print '<tr class="liste_titre">';
	print '<td class="liste_titre" align="center" colspan="2">En Cours</td>';
	print '<td class="liste_titre" align="center">Traités</td>';
	print '<td class="liste_titre" align="center">Perdues</td>';
	print '<td class="liste_titre" align="center">Sans Suite</td>';
	print "</tr>\n";


	$i=array();
	print '<tr>';
	print '<td class="colone"><div id="encours" class="dropper" ondrop="drop(event)" ondragover="allowDrop(event)" style="height:700px; width:215px; overflow: scroll;">';
	$i=0;
	foreach ($object->lines as $line){
		$line->fetch_thirdparty();
		if($line->array_options['options_type']==1){
			$img = img_picto('porteur', 'reception.png@volvo');
		}elseif($line->array_options['options_type']==2){
			$img = img_picto('porteur', 'tracteur.png@volvo');
		}
		if($line->array_options['options_gamme'] == 1){
			$color = '#56ff56';
			$color2= '#00ff00';
		}elseif($line->array_options['options_gamme'] == 2){
			$color = '#ff5656';
			$color2= '#ff0000';
		}elseif($line->array_options['options_gamme'] == 18){
			$color = '#ffaa56';
			$color2= '#ff7f00';
		}elseif($line->array_options['options_gamme'] == 3){
			$color = '#aad4ff';
			$color2= '#56aaff';
		}elseif($line->array_options['options_gamme'] == 4){
			$color = '#aa56ff';
			$color2= '#7f00ff';
		}else{
			$color = '#cccccc';
			$color2= '#b2b2b2';
		}
		print'<div class="cal_event cal_event_busy"  draggable="true" ondragstart="drag(event,this)" id="'. $line->id . '" style="background: -webkit-gradient(linear, left top, left bottom, from('.$color.'), to('.$color2.'));';
		print 'border-radius:6px; margin-bottom: 3px; width:200px;">';
		print $img . ' ';
		print $line->ref . '</br>';
		print $line->thirdparty->name;
		print '</div>';


		$i++;
		if ($i>= $mid){
			print '</div></td>';
			print '<td class="colone"><div id="encours2" class="dropper" ondrop="drop(event)" ondragover="allowDrop(event)" style="height:700px; width:250px; overflow: auto;">';
			$i =-1*$i;
		}
	}
	print '</div></td>';
	print '<td class="colone"><div id="traite" class="dropper" ondrop="drop(event)" ondragover="allowDrop(event)" style="height:700px; width:250px; overflow: auto;">';
	print '</div></td>';
	print '<td class="colone"><div id="perdu" class="dropper" ondrop="drop(event)" ondragover="allowDrop(event)" style="height:700px; width:250px; overflow: auto;">';
	print '</div></td>';
	print '<td class="colone"><div id="sanssuite" class="dropper" ondrop="drop(event)" ondragover="allowDrop(event)" style="height:700px; width:250px; overflow: auto;">';
	print '</div></td>';
	print '</tr>';
	print "</table>";

	print '</form>';

	print '<script type="text/javascript" language="javascript">' . "\n";
	print '$(document).ready(function() {
					$("#totalamountguess").append("' . price($totalamountguess) . $langs->getCurrencySymbol($conf->currency) . '");
					$("#totalamountreal").append("' . price($totalamountreal) . $langs->getCurrencySymbol($conf->currency) . '");
					$("#totalmargin").append("' . price($totalmargin) . $langs->getCurrencySymbol($conf->currency) . '");
					$("#totalmarginreal").append("' . price($totalmarginreal) . $langs->getCurrencySymbol($conf->currency) . '");
			});';
	print "\n" . '</script>' . "\n";
} else {
	setEventMessages(null, $object->errors, 'errors');
}

dol_fiche_end();
llxFooter();
$db->close();
