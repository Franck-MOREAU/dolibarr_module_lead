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
require_once DOL_DOCUMENT_ROOT . '/volvo/class/lead.extend.class.php';

// Security check
if (! $user->rights->lead->read)
	accessforbidden();

$search_commercial = GETPOST("search_commercial", 'int');
$year = GETPOST('year');

if (GETPOST("button_removefilter_x")) {
	$search_commercial = '';
	$year = dol_print_date(dol_now(),'Y');
}

$search_commercial_disabled = 0;
if (empty($user->rights->volvo->stat_all)){
	$search_commercial = $user->id;
	$search_commercial_disabled = 1;
}

$user_included=array();
$sqlusers = "SELECT fk_user FROM " . MAIN_DB_PREFIX . "usergroup_user WHERE fk_usergroup = 1";
$resqlusers  = $db->query($sqlusers);
if($resqlusers){
	while ($users = $db->fetch_object($resqlusers)){
		$user_included[] = $users->fk_user;
	}
}

$form = new Form($db);
$formlead = new FormLead($db);
$object1 = new Leadext($db);
$object2 = new Leadext($db);
$object3 = new Leadext($db);
$object4 = new Leadext($db);
$object5 = new Leadext($db);
$formother = new FormOther($db);

$title = 'Portefeuille d\'affaire';

llxHeader('', $title);
if($search_commercial == -1) $search_commercial='';
if($year == -1) $year='';
if(!empty($search_commercial)){
	$filter1['t.fk_user_resp'] =$search_commercial;
	$filter2['t.fk_user_resp'] =$search_commercial;
	$filter3['t.fk_user_resp'] =$search_commercial;
	$filter4['t.fk_user_resp'] =$search_commercial;
	$filter5['t.fk_user_resp'] =$search_commercial;
}
if(!empty($year)){
	$filter3['t.ref'] ='/' . substr($year, 2);
	$filter4['t.ref'] ='/' . substr($year, 2);
	$filter5['t.ref'] ='/' . substr($year, 2);
}
$filter1['t.fk_c_status !IN'] = '6,7,11';
$filter2['t.fk_c_status !IN'] = '6,7,11';
$filter3['t.fk_c_status !IN'] = '5,7,11';
$filter4['t.fk_c_status !IN'] = '6,5,11';
$filter5['t.fk_c_status !IN'] = '6,5,7';
$filter1['leadextra.chaude'] =1;
$filter2['leadextra.chaude IS NULL'] =1;
$resql1 = $object1->fetch_all('DESC', 't.ref','' , 0, $filter1);
$resql2 = $object2->fetch_all('DESC', 't.ref','' , 0, $filter2);
$resql3 = $object3->fetch_all('DESC', 't.ref','' , 0, $filter3);
$resql4 = $object4->fetch_all('DESC', 't.ref','' , 0, $filter4);
$resql5 = $object5->fetch_all('DESC', 't.ref','' , 0, $filter5);
$mid1 = $resql1/2;
$mid2 = $resql2/2;
$mid3 = $resql3/2;
print $resql2;
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

print '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" name="search_form">' . "\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<th class="liste_titre" align="center">Année: ';
$formother->select_year($year,'year',1, 5, 0);
print '</th>';
print '<th class="liste_titre" align="center">Commercial: '. $form->select_dolusers($search_commercial,'search_commercial',1,array(),$search_commercial_disabled,$user_included) . '</th>';
print '<th class="liste_titre" align="center">';
print '<div align="left"><input class="liste_titre" type="image" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/search.png" value="' . dol_escape_htmltag($langs->trans("Search")) . '" title="' . dol_escape_htmltag($langs->trans("Search")) . '">';
print '&nbsp;<input type="image" class="liste_titre" name="button_removefilter" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/searchclear.png" value="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '" title="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '"></div>';
print '</th>';
print "</tr>";
print '</table>';

print '<table class="border" width="100%">';
print '<tr class="liste_titre">';
print '<td class="liste_titre" align="center" colspan="2">En Cours Chaudes</td>';
print '<td class="liste_titre" align="center" colspan="2">Traités</td>';
print '<td class="liste_titre" align="center">Perdues</td>';
print '<td class="liste_titre" align="center">Sans Suite</td>';
print "</tr>\n";

print '<tr>';
print '<td class="colone"><div id="encours_chaude_1" class="dropper" ondrop="drop(event)" ondragover="allowDrop(event)" style="height:540px; width:219px; overflow: auto;">';
$i=0;
foreach ($object1->lines as $line){
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
	if ($i>= $mid1|| $mid1 == 0){
		print '</div></td>';
		print '<td class="colone"><div id="encours_chaude_2" class="dropper" ondrop="drop(event)" ondragover="allowDrop(event)" style="height:540px; width:219px; overflow: auto;">';
		$i =-1*$i;
	}
}
if($resql1==0){
	print '</div></td>';
	print '<td class="colone"><div id="encours_chaude_2" class="dropper" ondrop="drop(event)" ondragover="allowDrop(event)" style="height:540px; width:219px; overflow: auto;">';
}
print '</div></td>';
print '<td class="colone" rowspan="3"><div id="traite_1" class="dropper" ondrop="drop(event)" ondragover="allowDrop(event)" style="height:700px; width:219px; overflow: auto;">';
$i=0;
foreach ($object3->lines as $line){
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
	if ($i>= $mid3){
		print '</div></td>';
		print '<td class="colone" rowspan="3"><div id="traite_2" class="dropper" ondrop="drop(event)" ondragover="allowDrop(event)" style="height:700px; width:219px; overflow: auto;">';
		$i =-1*$i;
	}
}
if($resql3 == 0){
	print '</div></td>';
	print '<td class="colone" rowspan="3"><div id="traite_2" class="dropper" ondrop="drop(event)" ondragover="allowDrop(event)" style="height:700px; width:219px; overflow: auto;">';
}
print '</div></td>';
print '<td class="colone" rowspan="3"><div id="perdu" class="dropper" ondrop="drop(event)" ondragover="allowDrop(event)" style="height:700px; width:219px; overflow: auto;">';
foreach ($object4->lines as $line){
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
	$lead = new Leadext($db);
	$lead->fetch($line->id);
	$cmd = $lead->getnbchassisreal();
	if($cmd>0){
		$drag = "false";
	}else{
		$drag = "true";
	}
	print'<div class="cal_event cal_event_busy"  draggable="'. $drag . '" ondragstart="drag(event,this)" id="'. $line->id . '" style="background: -webkit-gradient(linear, left top, left bottom, from('.$color.'), to('.$color2.'));';
	print 'border-radius:6px; margin-bottom: 3px; width:200px;">';
	print $img . ' ';
	print $line->ref . '</br>';
	print $line->thirdparty->name;
	print '</div>';
}
print '</div></td>';
print '<td class="colone"rowspan="3"><div id="sanssuite" class="dropper" ondrop="drop(event)" ondragover="allowDrop(event)" style="height:700px; width:219px; overflow: auto;">';
foreach ($object5->lines as $line){
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
}
print '</div></td>';
print '</tr>';
print '<tr class="liste_titre">';
print '<td class="liste_titre" align="center" colspan="2">En Cours Froides</td>';
print '</tr>';
print '<tr>';
print '<td class="colone"><div id="encours_froide_1" class="dropper" ondrop="drop(event)" ondragover="allowDrop(event)" style="height:140px; width:219px; overflow: auto;">';
$i=0;
foreach ($object2->lines as $line){
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
	if ($i>= $mid2){
		print '</div></td>';
		print '<td class="colone"><div id="encours_froide_2" class="dropper" ondrop="drop(event)" ondragover="allowDrop(event)" style="height:140px; width:219px; overflow: auto;">';
		$i =-1*$i;
	}
}
if($resql2==0){
	print '</div></td>';
	print '<td class="colone"><div id="encours_froide_2" class="dropper" ondrop="drop(event)" ondragover="allowDrop(event)" style="height:140px; width:219px; overflow: auto;">';
}
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

dol_fiche_end();
llxFooter();
$db->close();
