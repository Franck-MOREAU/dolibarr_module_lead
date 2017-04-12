<?php

$res = @include '../../main.inc.php'; // For root directory
if (! $res)
	$res = @include '../../../main.inc.php'; // For "custom" directory
if (! $res)
	die("Include of main fails");

$lead_id = GETPOST('id_lead');
$new_statut = GETPOST('new_statut');
echo 'l\'affaire N°' . $lead_id . ' prend le statut ' . $new_statut;
?>