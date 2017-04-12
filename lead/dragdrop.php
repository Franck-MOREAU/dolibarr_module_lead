<?php

$res = @include '../../main.inc.php'; // For root directory
if (! $res)
	$res = @include '../../../main.inc.php'; // For "custom" directory
if (! $res)
	die("Include of main fails");

global $db, $user;
require_once DOL_DOCUMENT_ROOT . '/volvo/class/lead.extend.class.php';

$lead_id = GETPOST('id_lead');
$new_statut = GETPOST('new_statut');

$lead = new Leadext($db);
$res = $lead->fetch($lead_id);
if($res>0){
	$statut = explode('_', $new_statut);
	switch($statut[0]){
		case 'encours':
			$c_status= 5;
			switch($statut[1]){
				case 'chaude':
					$chaude =1;
					break;
				case 'froide':
					$chaude =0;
					break;
			}
			break;
		case 'traite':
			$_c_status = 6;
			$chaude=0;
			break;

		case 'perdu':
			$c_status=7;
			$chaude=0;
			break;

		case 'sanssuite':
			$c_status = 11;
			$chaude = 0;
			break;
	}

	$lead->fk_c_status = $c_statut;
	$lead->array_options['options_chaude'] = $chaude;
	$lead->update($user);
}else{
	echo $lead->error;
}
?>