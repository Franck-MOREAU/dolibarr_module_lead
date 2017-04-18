<?php

$res = @include '../../main.inc.php'; // For root directory
if (! $res)
	$res = @include '../../../main.inc.php'; // For "custom" directory
if (! $res)
	die("Include of main fails");

global $db, $user;
require_once DOL_DOCUMENT_ROOT . '/volvo/class/lead.extend.class.php';
dol_include_once('/core/class/extrafields.class.php');

$lead_id = GETPOST('id_lead');
$new_statut = GETPOST('new_statut');
$action = GETPOST('action');
$form = new Form($db);
$extrafields = new ExtraFields($db);



$lead = new Leadext($db);

$extralabels = $extrafields->fetch_name_optionals_label($lead->table_element);
$sql = "SELECT rowid, motif FROM " . MAIN_DB_PREFIX . 'c_volvo_motif_perte_lead WHERE active=1';
$resql = $db->query($sql);
if($resql){
	while($obj = $db->fetch_object($resql)){
		$array_motif[$obj->rowid]=$obj->motif;
	}
}

$res = $lead->fetch($lead_id);
if($res>0){
	if(empty($action)){
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
				$c_status = 6;
				$chaude=0;
				break;

			case 'perdu':
				$formconfirm = '';
				$formquestion = array(
						array(
								'type' => 'hidden',
								'name' => 'id_lead',
								'value' => $lead_id
						),
						array(
								'type' => 'hidden',
								'name' => 'action',
								'value' => "confirm_move"
						),
						array(
								'type' => 'hidden',
								'name' => 'new_statut',
								'value' =>  $new_statut
						));
				$sql = "SELECT rowid, motif FROM " . MAIN_DB_PREFIX . 'c_volvo_motif_perte_lead WHERE active=1';
				$resql = $db->query($sql);
				if($resql){
					while($obj = $db->fetch_object($resql)){
						$array_motif[$obj->rowid]=$obj->motif;
					}
				}

				$checked = explode(',', $lead->array_options['options_motif']);
				$formquestion[]=array(
						'type' => 'other',
						'value' => 'Motif de perte:');


				foreach ($array_motif as $id => $motif){
					if (in_array($id, $checked)){
						$val = true;
					}else{
						$val = false;
					}
						$formquestion[]= array(
								'type' => 'checkbox',
								'name' => 'options_motif' . $id,
								'label' => $motif,
								'value' => $val
						);
				}
				$formquestion[]=array(
						'type' => 'other',
						'value' => '</br>');
				$formquestion[]=array(
								'type' => 'other',
								'name' => 'options_marque',
								'label' => 'Marque traitée',
								'value' => $extrafields->showInputField("marque", $lead->array_options["options_marque"])
						);

				$formconfirm = formconfirm('"javascript:drop2()"', 'test1', 'Cloture de l\'affaire', 'confirm_move', $formquestion, 0, 1,500);
				echo $formconfirm;
				break;

			case 'sanssuite':
				$c_status = 11;
				$chaude = 0;
				break;
		}

		$lead->fk_c_status = $c_status;
		$lead->array_options['options_chaude'] = $chaude;
		$res = $lead->update($user);
		echo $lead->error;
	}else{
		echo $lead->error;
	}
}elseif($action=='confirm_move'){
	$c_status=7;
	$chaude=0;
	$lead->fk_c_status = $c_status;
	$lead->array_options['options_chaude'] = $chaude;
	$res = $lead->update($user);
	echo $lead->error;
}

function formconfirm($page, $title, $question, $action, $formquestion='', $selectedchoice="", $useajax=0, $height=200, $width=500)
{
	global $langs,$conf;
	global $useglobalvars;

	$more='';
	$formconfirm='';
	$inputok=array();
	$inputko=array();

	// Clean parameters
	$newselectedchoice=empty($selectedchoice)?"no":$selectedchoice;

	if (is_array($formquestion) && ! empty($formquestion))
	{
		// First add hidden fields and value
		foreach ($formquestion as $key => $input)
		{
			if (is_array($input) && ! empty($input))
			{
				if ($input['type'] == 'hidden')
				{
					$more.='<input type="hidden" id="'.$input['name'].'" name="'.$input['name'].'" value="'.dol_escape_htmltag($input['value']).'">'."\n";
				}
			}
		}

		// Now add questions
		$more.='<table class="paddingrightonly" width="100%">'."\n";
		$more.='<tr><td colspan="3">'.(! empty($formquestion['text'])?$formquestion['text']:'').'</td></tr>'."\n";
		foreach ($formquestion as $key => $input)
		{
			if (is_array($input) && ! empty($input))
			{
				$size=(! empty($input['size'])?' size="'.$input['size'].'"':'');

				if ($input['type'] == 'text')
				{
					$more.='<tr><td>'.$input['label'].'</td><td colspan="2" align="left"><input type="text" class="flat" id="'.$input['name'].'" name="'.$input['name'].'"'.$size.' value="'.$input['value'].'" /></td></tr>'."\n";
				}
				else if ($input['type'] == 'password')
				{
					$more.='<tr><td>'.$input['label'].'</td><td colspan="2" align="left"><input type="password" class="flat" id="'.$input['name'].'" name="'.$input['name'].'"'.$size.' value="'.$input['value'].'" /></td></tr>'."\n";
				}
				else if ($input['type'] == 'select')
				{
					$more.='<tr><td>';
					if (! empty($input['label'])) $more.=$input['label'].'</td><td valign="top" colspan="2" align="left">';
					$more.=$this->selectarray($input['name'],$input['values'],$input['default'],1);
					$more.='</td></tr>'."\n";
				}
				else if ($input['type'] == 'checkbox')
				{
					$more.='<tr>';
					$more.='<td>'.$input['label'].' </td><td align="left">';
					$more.='<input type="checkbox" class="flat" id="'.$input['name'].'" name="'.$input['name'].'"';
					if (! is_bool($input['value']) && $input['value'] != 'false') $more.=' checked';
					if (is_bool($input['value']) && $input['value']) $more.=' checked';
					if (isset($input['disabled'])) $more.=' disabled';
					$more.=' /></td>';
					$more.='<td align="left">&nbsp;</td>';
					$more.='</tr>'."\n";
				}
				else if ($input['type'] == 'radio')
				{
					$i=0;
					foreach($input['values'] as $selkey => $selval)
					{
						$more.='<tr>';
						if ($i==0) $more.='<td valign="top">'.$input['label'].'</td>';
						else $more.='<td>&nbsp;</td>';
						$more.='<td width="20"><input type="radio" class="flat" id="'.$input['name'].'" name="'.$input['name'].'" value="'.$selkey.'"';
						if ($input['disabled']) $more.=' disabled';
						$more.=' /></td>';
						$more.='<td align="left">';
						$more.=$selval;
						$more.='</td></tr>'."\n";
						$i++;
					}
				}
				else if ($input['type'] == 'date')
				{
					$more.='<tr><td>'.$input['label'].'</td>';
					$more.='<td colspan="2" align="left">';
					$more.=$this->select_date($input['value'],$input['name'],0,0,0,'',1,0,1);
					$more.='</td></tr>'."\n";
					$formquestion[] = array('name'=>$input['name'].'day');
					$formquestion[] = array('name'=>$input['name'].'month');
					$formquestion[] = array('name'=>$input['name'].'year');
					$formquestion[] = array('name'=>$input['name'].'hour');
					$formquestion[] = array('name'=>$input['name'].'min');
				}
				else if ($input['type'] == 'other')
				{
					$more.='<tr><td>';
					if (! empty($input['label'])) $more.=$input['label'].'</td><td colspan="2" align="left">';
					$more.=$input['value'];
					$more.='</td></tr>'."\n";
				}
			}
		}
		$more.='</table>'."\n";
	}

	// JQUI method dialog is broken with jmobile, we use standard HTML.
	// Note: When using dol_use_jmobile or no js, you must also check code for button use a GET url with action=xxx and check that you also output the confirm code when action=xxx
	// See page product/card.php for example
	if (! empty($conf->dol_use_jmobile)) $useajax=0;
	if (empty($conf->use_javascript_ajax)) $useajax=0;

	if ($useajax)
	{
		$autoOpen=true;
		$dialogconfirm='dialog-confirm';
		$button='';
		if (! is_numeric($useajax))
		{
			$button=$useajax;
			$useajax=1;
			$autoOpen=false;
			$dialogconfirm.='-'.$button;
		}
		$pageyes=$page;
		$pageno=($useajax == 2 ? $page.(preg_match('/\?/',$page)?'&':'?').'confirm=no':'');
		// Add input fields into list of fields to read during submit (inputok and inputko)
		if (is_array($formquestion))
		{
			foreach ($formquestion as $key => $input)
			{
				//print "xx ".$key." rr ".is_array($input)."<br>\n";
				if (is_array($input) && isset($input['name'])) array_push($inputok,$input['name']);
				if (isset($input['inputko']) && $input['inputko'] == 1) array_push($inputko,$input['name']);
			}
		}

		$formconfirm.= ($question ? '<div class="confirmmessage">'.img_help('','').' '.$question . '</div>': '');

		// Show JQuery confirm box. Note that global var $useglobalvars is used inside this template
		$formconfirm.= '<div id="'.$dialogconfirm.'" title="'.dol_escape_htmltag($title).'" style="display: none;">';
		if (! empty($more)) {
			$formconfirm.= '<div class="confirmquestions">'.$more.'</div>';
		}

		$formconfirm.= '</div>'."\n";

		$formconfirm.= "\n<!-- begin ajax form_confirm page=".$page." -->\n";
		$formconfirm.= '<script type="text/javascript">'."\n";
		$formconfirm.= 'jQuery(document).ready(function() {
            $(function() {
            	$( "#'.$dialogconfirm.'" ).dialog(
            	{
                    autoOpen: '.($autoOpen ? "true" : "false").',';
		if ($newselectedchoice == 'no')
		{
			$formconfirm.='
						open: function() {
            				$(this).parent().find("button.ui-button:eq(2)").focus();
						},';
		}
		$formconfirm.='
                    resizable: false,
                    height: "'.$height.'",
                    width: "'.$width.'",
                    modal: true,
                    closeOnEscape: false,
                    buttons: {
                        "'.dol_escape_js($langs->transnoentities("Yes")).'": function() {
                        	var options="";
                        	var inputok = '.json_encode($inputok).';
                         	var pageyes = "'.dol_escape_js(! empty($pageyes)?$pageyes:'').'";
                         	if (inputok.length>0) {
                         		$.each(inputok, function(i, inputname) {
                         			var more = "";
                         			if ($("#" + inputname).attr("type") == "checkbox") { more = ":checked"; }
                         		    if ($("#" + inputname).attr("type") == "radio") { more = ":checked"; }
                         			var inputvalue = $("#" + inputname + more).val();
                         			if (typeof inputvalue == "undefined") { inputvalue=""; }
                         			options += "&" + inputname + "=" + inputvalue;
                         		});
                         	}
                         	var urljump = pageyes + (pageyes.indexOf("?") < 0 ? "?" : "") + options;
                         	//alert(urljump);
            				drop2(options)
                            $(this).dialog("close");
                        },
                        "'.dol_escape_js($langs->transnoentities("No")).'": function() {
                        	var options = "";
                         	var inputko = '.json_encode($inputko).';
                         	var pageno="'.dol_escape_js(! empty($pageno)?$pageno:'').'";
                         	if (inputko.length>0) {
                         		$.each(inputko, function(i, inputname) {
                         			var more = "";
                         			if ($("#" + inputname).attr("type") == "checkbox") { more = ":checked"; }
                         			var inputvalue = $("#" + inputname + more).val();
                         			if (typeof inputvalue == "undefined") { inputvalue=""; }
                         			options += "&" + inputname + "=" + inputvalue;
                         		});
                         	}
                         	var urljump=pageno + (pageno.indexOf("?") < 0 ? "?" : "") + options;
                         	//alert(urljump);
                            $(this).dialog("close");
                        }
                    }
                }
                );

            	var button = "'.$button.'";
            	if (button.length > 0) {
                	$( "#" + button ).click(function() {
                		$("#'.$dialogconfirm.'").dialog("open");
        			});
                }
            });
            });
			function drop2(options) {
				alert( "Résultat: " + options);
			}
            </script>';
		$formconfirm.= "<!-- end ajax form_confirm -->\n";
	}
	else
	{
		$formconfirm.= "\n<!-- begin form_confirm page=".$page." -->\n";

		$formconfirm.= '<form method="POST" action="'.$page.'" class="notoptoleftroright">'."\n";
		$formconfirm.= '<input type="hidden" name="action" value="'.$action.'">'."\n";
		$formconfirm.= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";

		$formconfirm.= '<table width="100%" class="valid">'."\n";

		// Line title
		$formconfirm.= '<tr class="validtitre"><td class="validtitre" colspan="3">'.img_picto('','recent').' '.$title.'</td></tr>'."\n";

		// Line form fields
		if ($more)
		{
			$formconfirm.='<tr class="valid"><td class="valid" colspan="3">'."\n";
			$formconfirm.=$more;
			$formconfirm.='</td></tr>'."\n";
		}

		// Line with question
		$formconfirm.= '<tr class="valid">';
		$formconfirm.= '<td class="valid">'.$question.'</td>';
		$formconfirm.= '<td class="valid">';
		$formconfirm.= $this->selectyesno("confirm",$newselectedchoice);
		$formconfirm.= '</td>';
		$formconfirm.= '<td class="valid" align="center"><input class="button" type="submit" value="'.$langs->trans("Validate").'"></td>';
		$formconfirm.= '</tr>'."\n";

		$formconfirm.= '</table>'."\n";

		$formconfirm.= "</form>\n";
		$formconfirm.= '<br>';

		$formconfirm.= "<!-- end form_confirm -->\n";
	}

	return $formconfirm;
}
?>