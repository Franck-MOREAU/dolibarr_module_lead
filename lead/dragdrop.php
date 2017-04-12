<?php

$res = @include '../../main.inc.php'; // For root directory
if (! $res)
	$res = @include '../../../main.inc.php'; // For "custom" directory
if (! $res)
	die("Include of main fails");

global $db;
require_once DOL_DOCUMENT_ROOT . '/volvo/class/lead.extend.class.php';

$lead_id = GETPOST('id_lead');
$new_statut = GETPOST('new_statut');

$lead = new Leadext($db);
$res = $lead->fetch($lead_id);

echo 'l\'affaire N°' . $lead_id . ' prend le statut ' . $new_statut . ' la recherche de l\'affaire a retournée le résultat: ' . $res;
?>