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

if ($resql != - 1) {
	print '<script>';
	print 'function allowDrop(ev) {';
    print 'ev.preventDefault();';
	print '}';

	print 'function drag(ev) {';
    print 'ev.dataTransfer.setData("text", ev.target.id);';
	print '}';

	print 'function drop(ev) {';
    print 'ev.preventDefault();';
    print 'var data = ev.dataTransfer.getData("text");';
    print 'ev.target.appendChild(document.getElementById(data));';
	print '}';
	print '</script>';


	print '<table class="border" width="100%">';
	print '<tr class="liste_titre">';
	print '<td class="liste_titre" align="center" widht="25%">En Cours</td>';
	print '<td class="liste_titre" align="center" widht="25%">Traités</td>';
	print '<td class="liste_titre" align="center" widht="25%">Perdues</td>';
	print '<td class="liste_titre" align="center" widht="25%">Sans Suite</td>';
	print "</tr>\n";


	$i=array();
	foreach ($object->lines as $line){
		print '<tr>';
		print'<td id="div'. $line->id . '" draggable="true" ondragstart="drag(event)" class="cal_event cal_event_busy">' .$line->ref .'</div>';
		$i[]=$line->id;
		print '<td id="recp1' . $line->id . '" ondrop="drop(event)" ondragover="allowDrop(event)">';
		print '</td>';
		print '<td id="recp2' . $line->id . '" ondrop="drop(event)" ondragover="allowDrop(event)">';;
		print '</td>';
		print '<td id="recp2' . $line->id . '" ondrop="drop(event)" ondragover="allowDrop(event)">';;
		print '</td>';
		print '</tr>';
	}
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
