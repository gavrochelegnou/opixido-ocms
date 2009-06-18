<?php


/*
$form->gen("rubrique_bg_img");
$form->gen("rubrique_bg_color");
$form->gen("rubrique_bg_motif_img");
*/


if($_REQUEST['curId'] != "new") {

if(rubriqueIsAPage($form) || true ) {

	//$form->gen('fk_paragraphe_id');

	$form->gen("fk_gabarit_id"); //,"",""," onchange='checkRubriqueType()' ");

	/**
	 * Si on a un gabarit particulier
	 */
	if($form->tab_default_field['fk_gabarit_id']) {
	
		/**
		 * Quel gabarit
		 */
		$gab = getGabarit($form->tab_default_field['fk_gabarit_id']);
		$gabNom = $gab['gabarit_classe'];
		$gabFold =  $gab['gabarit_plugin'] ? PLUGINS_FOLDER.'/'.$gab['gabarit_plugin'] : 'bdd';
		
		/**
		 * On l'inclu
		 */
		$GLOBALS['gb_obj']->includeFile($gabNom.'.php',$gabFold);
		//ini_set('error_reporting',E_ALL);
		//print_r($gabNom::$ocms_params);
		//$t = $gabNom::$ocms_params;
		//debug($t);
		
		/**
		 * Si il a une methode pour connaitre ses paramètres
		 */
		$r = array();
		if(method_exists($gabNom,'ocms_getParams')) {
			$r = call_user_method('ocms_getParams',$gabNom);
		}
		
		$plugs = GetPlugins();
		foreach($plugs as $v) {
			if(class_exists($v.'Admin') && method_exists($v.'Admin','ocms_getParams')) {
				//debug(call_user_method('ocms_getParams',$v.'Admin'));
				//debug($v.'Admin');
				$className = $v.'Admin';
				$res = call_user_method('ocms_getParams',$className);
				//debug($res);
				$r = array_merge($r,$res);
			}
		}
		
		if(!$this->editMode) {
			echo '<div style="display:inline;" class="genform_txt">'.t($gabNom.'_params').'</div>
			<div class="genform_champ">';
			$sf = new simpleForm();
			
			$defVals = SplitParams($form->tab_default_field['rubrique_gabarit_param'],";","=");

			$defV = $defVals;

			
			foreach($r as $nom=>$type) {

				echo $sf->getLabel(array('label'=>t($nom)));
				
				if(is_array($type)) {
					$vals = $type[1];
					$type = $type[0];
				}
				if($type == 'selectm') {
					echo $sf->getSelect(array('id'=>$nom,'value'=>$vals,'selected'=>$defV[$nom]),true);
					
				} else
				if($type == 'select') {
					echo $sf->getSelect(array('id'=>$nom,'value'=>$vals,'selected'=>$defV[$nom]));
					
				} else {
					echo $sf->getInputText(array('id'=>$nom,"value"=>$defV[$nom]));
				}
				
				echo '<br/>';
			}
			
			echo '</div><br/>';
			
			$GLOBALS['nomsTech'] = $noms = implode('","',array_keys($r));
			
			?>
			<script type="text/javascript">
			
				window.FieldsToTech = Array("<?=$GLOBALS['nomsTech']?>");
			
				function updateFieldsToTech() {
					texte = '';
					
					for ( p in window.FieldsToTech) {
						ob = gid(window.FieldsToTech[p]);
						val = ob.value;
						if(ob.multiple) {
							val = ""
							for (var i = 0; i < ob.options.length; i++)  {
								if (ob.options[ i ].selected && ob.options[ i ].value) {
									val += (ob.options[ i ].value) +",";
								}
								
							}
							val = val.substring(0,val.length-1);
						}

						texte += window.FieldsToTech[p]+"="+val+";";
					}
					
					gid("genform_rubrique_gabarit_param").value = texte;
					
				}
				
				for ( p in window.FieldsToTech) {
					gid(window.FieldsToTech[p]).onchange = updateFieldsToTech;
				}
						
			</script>
			
			<?php
		
		}
		
		
		
		//debug(evaluate($str));
		
		//debug($r);
		
		//
		// print_r(genContact::$ocms_params);
		
		$form->gen("rubrique_gabarit_param");
		$form->gen("rubrique_dyntitle");
		$form->gen("rubrique_dynvisibility");
		
	}
	 	

}

if($form->tab_default_field['rubrique_type'] == RTYPE_SITEROOT) {
	$form->gen("rubrique_template");
}

$form->gen("rubrique_type");	
//$form->gen("FAUXPARA");	

}

?>