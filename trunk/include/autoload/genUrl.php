<?php

/**
 * Gestion des URLs du site
 * Rappatriement / renvoi
 *
 */


class genUrl{

	/**
	 * Tableau qui stocke les url des rubriques et les cl��rang�e de ces rubriques
	 *
	 * @var array
	 */
	public $tabUrl;		
	
	private $lg;			//La langue en cours de la page
	public $roadSup;		//Le tableau gerant les rubriques qui ne sont pas dans la bdd
	private $otherLgParamsUrl;
	public $paramsUrl = array();
	public $topRubId;
	public $minisite;
	private $selectedArbo = array();
	private $recursDones = array();
	public $rubId = 0;
	
	function getTopRubId(){
		return $this->topRubId;
	}

	/**
	 *  Constructeur de la classe genUrl 
	 */
	function genUrl($lg=''){
		
		
		$this->lg = $lg;
			
		$this->minisite = false;
		$GLOBALS['tabUrl'] = array();
		$GLOBALS['urlCached'] = array();
		$this->parseUrl();
		
		$this->isMiniSite();
		
		$this->rootRow = $this->getSiteRoot();
		//$this->rootHomeId = $this->rootRow['rubrique_id'];

		
		
		
		$this->roadSup = array();
		$this->colorLevel = 'sd';
		
		
	}

	/**
	 * Langue courante
	 *
	 * @return string Langue actuelle
	 */
	function getLg(){
		return $this->lg;
	}
	
	/**
	 * Methode pour definir quelle rubrique contient l'ensemble du site
	 * C'est la rubrique qui est définit comme
	 * rubrique_type = 'siteroot'
	 * et dont l'url correspond au $_SERVER['HTTP_HOST'] puis en concatenant  dirname($_SERVER["SCRIPT_NAME"]);
	 *
	 */
	function getSiteRoot() {
		
		global $_Gconfig;
		$host = $_SERVER["HTTP_HOST"];
		$path = dirname($_SERVER["SCRIPT_NAME"]);
				

		$sql = 'SELECT * FROM s_rubrique 
					WHERE rubrique_type 
					IN ("'.RTYPE_SITEROOT.'","'.RTYPE_MENUROOT.'") 
					'.sqlRubriqueOnlyReal().' ';
		
		$cRes = GetAll($sql);		
			

		foreach($cRes as $res) {
					
			$rub = $GLOBALS['tabUrl'][$res['rubrique_id']] = array(
										'fkRub' => $res['fk_rubrique_id'],
										'gabarit'=>$res['fk_gabarit_id'],
										/*'isFolder'=>$res['rubrique_is_folder'],*/
										 'param'=>$res['rubrique_gabarit_param'],
										 'option'=>$res['rubrique_option'],
									     'type' => $res['rubrique_type'],
									     'webroot'=>($res['rubrique_type'] == RTYPE_SITEROOT ? $this->getDefWebRoot($res['rubrique_url_'.LG_DEF]) : ''	) 
									     );
									    							     

			reset($_Gconfig['LANGUAGES']);
			foreach($_Gconfig['LANGUAGES'] as $lg) {
				$GLOBALS['tabUrl'][$res['rubrique_id']]['link_'.$lg] = $res['rubrique_link_'.$lg];
				$GLOBALS['tabUrl'][$res['rubrique_id']]['titre_'.$lg] = $res['rubrique_titre_'.$lg];
				$GLOBALS['tabUrl'][$res['rubrique_id']]['url'.$lg] = $res['rubrique_url_'.$lg];				
			}
		}

		
		if(false && $this->minisite) {
			$row = $this->minisite_row;
			$this->homeId = $this->rootHomeId = $this->root_id = $row['rubrique_id'];
			
			
		} else {	
			$sql = 'SELECT * FROM s_rubrique
					 WHERE rubrique_type 
					 LIKE "'.RTYPE_SITEROOT.'" 
					 '.sqlRubriqueOnlyOnline().' 
					 '.lgFieldsLike("rubrique_url", '%;'.mes($host).';%',' OR ').'
					  ';
			$row = GetSingle($sql);
		
		}
		
		if(count($row)) {
			$this->currentRootHomeId =$this->homeId = $this->rootHomeId = $this->root_id = $row['rubrique_id'];
			$this->curWebRoot = $this->getDefWebRoot($row['rubrique_url_'.LG_DEF]);
			$this->TEMPLATE  = $row['rubrique_template'];
			
			
			return $row;		
			
		} else {
			
			$sql = 'SELECT * FROM s_rubrique WHERE rubrique_type 	
						LIKE "'.RTYPE_SITEROOT.'" '.sqlRubriqueOnlyOnline().' LIMIT 0,1';
			$row = GetSingle($sql);	
		
			if(count($row)) {
				if($this->minisite_row) {
					
					$this->homeId = $this->minisite_row['rubrique_id'];
					$this->rootHomeId = $row['rubrique_id'];
					$this->root_id = $this->minisite_row['rubrique_id'];
					$this->curWebRoot = $this->getDefWebRoot($this->minisite_row['rubrique_url_'.LG_DEF]);
					$this->TEMPLATE  = $row['rubrique_template'];
					$row = $this->minisite_row;
					$this->currentRootHomeId = $row['rubrique_id'];
				} else {
					$this->currentRootHomeId = $this->homeId = $this->rootHomeId = $this->root_id = $row['rubrique_id'];
					$this->curWebRoot = $this->getDefWebRoot($row['rubrique_url_'.LG_DEF]);
					$this->TEMPLATE  = $row['rubrique_template'];					
				}

				
				return $row;
			} else if(!isLoggedAsAdmin()) {
				diebug('NO_SITE_ROOT');		
			}
			
		}
		
	}
	
	
	function getDefWebRoot($str) {

		$et = explode(';',$str);
		foreach($et as $v) {
			if(strtolower($v) == strtolower($_SERVER['HTTP_HOST'])) {
				return $v;
			}
		}
		
		return $_SERVER['HTTP_HOST'];
		if(strlen($et[0])) {
			 return $et[0];
		} else {
			return $et[1];
		}
			
	}

	/**
	 * Retourne le cache des URLs
	 *
	 * @return unknown
	 */
	function getTabUrl(){
		return $GLOBALS['tabUrl'];
	}

	/**
	 * Retourne les chemins supplémentaires ajoutés au chemin de fer
	 *
	 * @return unknown
	 */
	function getRoadSup(){
		return $this->roadSup;
	}


	
	/**
	 * Definit si l'on est dans un minisite
	 *
	 * @return unknown
	 */
	function isMiniSite() {
		global $_Gconfig;

		$host = ($_SERVER["HTTP_HOST"]);

		if(strstr($host,$_Gconfig['minisite_sous_domaine']) && 'http://'.$_SERVER["HTTP_HOST"].'/' != WEB_URL) {
			
			$this->minisite = true;

			
			$this->minisite_nom = str_replace($_Gconfig['minisite_sous_domaine'],'',$host);
			$this->minisite_nom = str_replace('www.','',$this->minisite_nom);


			//print('Mini site nom : '.$this->minisite_nom);

			$sql = 'SELECT * FROM s_rubrique WHERE rubrique_url_fr = "'.$this->minisite_nom.'" 
						AND rubrique_type = "'.RTYPE_SITEROOT.'"';

			$row = GetSingle($sql,true);
			
			if(count($row)) {
				$this->minisite_row = $row;
				
				$this->selectedArbo[] = $row['rubrique_id'];
				$this->selectedArbo[] = $row['fk_rubrique_id'];

			} else {

				$this->minisite = false;
				return false;
			}
			


		}
		return $this->minisite;
	}
	

	

	/**
	 * Parse les élements de l'URL et récupère chaque partie sous forme de tableau
	 *
	 * @return array
	 */
	function parseUrl(){
		
		global $_Gconfig;
		$x_url = explode('?', $_SERVER['REQUEST_URI']);

		
		$x_url = str_replace('/index.html','/',$x_url);
		
		$x_url = $x_url[0];
		$x_url = explode('/_action/',$x_url);

		
		$this->action = ake($x_url,1) ? $x_url[1] : '';
		
		$this->splitAction();

		$x_url = explode('/'.GetParam('fake_folder_param').'/',$x_url[0]);

		$x_url[0] = str_replace(BU,'',$x_url[0]);
		
		$params = ake($x_url,1) ? $x_url[1] : '';

		$this->splitParams($params);

		$x_url = $x_url[0];

		$x_url = explode('/', $x_url);

		$this->parsedUrl = $x_url;
		
		$templg = $this->parsedUrl[1];
		
		global $_Gconfig;
		if($_Gconfig['onlyOneLgForever']) {
			
			define("LG",$_Gconfig['LANGUAGES'][0]);
			$this->lg = LG;
			mylocale(LG);
			define('TRADLG',false);
			
		} else {
			/**
			 * Si on est dans une seconde langue ( /fr-de/ )
			 */
			if( strpos($templg,'-')) {
				$templg = explode('-',$templg);
				$this->lg = $templg[0];
				if(!in_array($this->lg,$_Gconfig['LANGUAGES'])) {
					$this->lg = $this->getBrowserLang();
				}
				$this->tradlg = $templg[1];
				define('TRADLG',$this->tradlg);
				
				
			} else if(count($this->parsedUrl) > 1 && $templg) {
				
				/**
				 * Si on a a priori la langue en paramètres
				 */
					$this->lg = $templg;
				
					if(!in_array($this->lg,$_Gconfig['LANGUAGES'])) {
						$this->lg = $this->getBrowserLang();
					}
					
					define("LG",$this->lg);
					mylocale($this->lg);
					define('TRADLG',false);
				
				
			} else {
				
				$this->lg = empty($this->lg) ? $this->getBrowserLang() : $this->lg;
				
				define("LG",$this->lg);				
				mylocale($this->lg);
				define('TRADLG',false);
				
			}
		}
		
		
			
			
			
		$this->parsedUrl = $this->trimTab($this->parsedUrl);
			
			
			
		return $x_url;

	}

	
	/**
	 * Sépare les actions du reste de l'URL
	 *
	 */
	function splitAction() {

		$this->action = explode('/',$this->action);
		$this->action = end($this->action);

	}

	
	/**
	 * Sépare les paramètres /bdd/ du reste de l'URL
	 *
	 * @param unknown_type $params
	 */
	function splitParams($params) {

		$params = explode('/',$params);
		$paramNom = '';
		foreach($params as $param) {
			if(strlen($paramNom)) {
				if(substr($paramNom,-6) == '__list') {
					$paramNom = substr($paramNom,0,-6);
					$this->paramsUrl[$paramNom] =  $_REQUEST[$paramNom] = $_GET[$paramNom] = explode('_-_',urldecode($param));
				} else {
					$this->paramsUrl[$paramNom] =  $_REQUEST[$paramNom] = $_GET[$paramNom] = urldecode($param);
				}
				$paramNom = '';
			} else {
				$paramNom = $param;
			}
		}
		
		
	}

	
	/**
	 * Retourne l'URL courante dans la langue $lg
	 *
	 * @param unknown_type $lg
	 * @return unknown
	 */
	function getUrlInLg($lg) {
		
		return $this->buildUrlFromId(0,$lg,$this->paramsUrl);
		
	}


	/**
	 * Methode qui va parser l'URL et retourne l'identifiant de la rubrique
	 * selectionnée
	 *
	 * @return unknown
	 */
	function getRubId(){
		
		global $homeId,$_Gconfig;
		if(IN_ADMIN )  
		{
			$this->lg = LG_DEF;
			return false;			
		}
		
		if(!$this->rubId ) {

			
			
		
			if(count($this->parsedUrl) == 0){
				/**
				 * On est soit à la racine du site soit à la racine d'un minisite
				 * 
				 * @deprecated
				 			*/	
				if($this->minisite) {
					
					/**
					 * Si on pointe vers la premiere sous rubrique => 
					 * On selectionne au cas où ...
					 * 
					 * @deprecated 
					 */	
					if($_Gconfig['rubLinkToSub']) {
					
						$sql = 'SELECT rubrique_id FROM s_rubrique WHERE
								 fk_rubrique_id = '.sql($this->minisite_row['rubrique_id']).' 
								 	'.sqlRubriqueOnlyOnline().' 
								 	ORDER BY rubrique_ordre ASC LIMIT 0,1';
						
						$r = GetSingle($sql);
					
						if(count($r)) {
							$this->minisite_row['rubrique_id'] = $this->rubId = $r['rubrique_id'];
						}
					}	
					
					return $this->minisite_row['rubrique_id'];
								 

				} else if (false) {	
				}
				else {
					
					if($this->action == 'editer') {
						$sql = 'SELECT * FROM s_rubrique
								 WHERE fk_rubrique_version_id = '.$this->currentRootHomeId;
						$row = GetSingle($sql);
						#debug($row);
						$this->rubId  = $row['rubrique_id'];
					} else {
						$this->rubId = $this->currentRootHomeId;
					}
					return $this->rubId ;
				}
							
			} else {
				/**
				 * Sinon on sélectionne les rubriques correspondantes
				 */
				
				
				$select= 'SELECT ';
				$nbUrls = count($this->parsedUrl);
				for($i=1; $i <= $nbUrls; $i++){
					$select .= ' R'.$i.'.rubrique_id AS r'.$i.'_rubrique_id ,  ';
					$select .= ' R'.$i.'.fk_rubrique_id AS r'.$i.'_fk_rubrique_id ,  ';
					$select .= ' R'.$i.'.rubrique_type AS r'.$i.'_rubrique_type ,  ';
					$select .= ' R'.$i.'.fk_gabarit_id AS r'.$i.'_fk_gabarit_id ,  ';
					$select .= ' R'.$i.'.rubrique_gabarit_param AS r'.$i.'_rubrique_gabarit_param ,  ';					
					$select .= ' R'.$i.'.rubrique_option AS r'.$i.'_rubrique_option ,  ';					
					
					
					global $_Gconfig;
					reset($_Gconfig['LANGUAGES']);
					foreach($_Gconfig['LANGUAGES'] as $lg) {
						$select .= ' R'.$i.'.rubrique_url_'.$lg.' AS r'.$i.'_rubrique_url_'.$lg.' ,  ';	
						$select .= ' R'.$i.'.rubrique_titre_'.$lg.' AS r'.$i.'_rubrique_titre_'.$lg.' ,  ';	
						$select .= ' R'.$i.'.rubrique_link_'.$lg.' AS r'.$i.'_rubrique_link_'.$lg.' ,  ';			
					}	
					
					reset($_Gconfig['LANGUAGES']);
				}
				
				
				
				$select .= ' R1.rubrique_etat  from s_rubrique as R1 ';

				
				$from = '';
				$where = ' where R1.rubrique_url_' .$this->lg .'=\'' .$this->parsedUrl[count($this->parsedUrl)] .'\'';
				
				for($i=2; $i <=$nbUrls; $i++){
					$j = $i-1;
					$from .= ', s_rubrique as R' .$i;
					$where .= ' and R' .$j .'.fk_rubrique_id = R' .$i .'.rubrique_id 
							and R' .$i .'.rubrique_url_' .$this->lg .'=\'' .$this->parsedUrl[count($this->parsedUrl)-$j] .'\' ';
				}

				$CUR = $nbUrls+1;
				
				$from .= ', s_rubrique as R' .$CUR;
				$where .= ' and R' .$CUR .'.rubrique_type IN ("'.RTYPE_MENUROOT.'","'.RTYPE_SITEROOT.'") AND R'.$nbUrls.'.fk_rubrique_id = R'.$CUR.'.rubrique_id  ';
				
				
				
				if($this->minisite) {
					global $rootId ,$headRootId ,$footRootId;
					$where .= ' AND R'.$nbUrls.'.fk_rubrique_id IN ("'.$this->minisite_row['rubrique_id'].'") ';
					//debug($where);
				}
				

				if($this->action == 'editer') {

					$where .=  sqlRubriqueOnlyVersions('R1');

				} else {
					$where .=  sqlRubriqueOnlyReal('R1');

				}

				
				
				$sql = $select .$from .$where;
				
				$res = GetSingle($sql);
			//echo $sql;

			
				
				/**
				 * On a pas trouvé la rubrique 
				 * c'est donc une erreur 404
				 */
				if(count($res) == 0){
					
					header ('HTTP/1.1 404 Not Found');

					$GLOBALS['_gensite']->isCurrent404 = true;
					
					if(stristr($_SERVER['REQUEST_URI'],'css') || stristr($_SERVER['REQUEST_URI'],'js' || stristr($_SERVER['REQUEST_URI'],'jpeg') || stristr($_SERVER['REQUEST_URI'],'jpg') || stristr($_SERVER['REQUEST_URI'],'gif' || stristr($_SERVER['REQUEST_URI'],'png')))) {
						$this->die404();
					}
					
					$this->rubId = getRubFromGabarit('genSitemap');
					
					if(!$this->rubId) {
						$this->die404();
					}
					
					return $this->rubId;
					
				}else{					

					$this->topRubId = $res['r'.$nbUrls.'_rubrique_id'];
					$this->rubId = $res['r1_rubrique_id'];							

					/**
					 * Sinon on met en cache ce qu'on a trouvé pour la construction des URLs
					 */
					for($i=1; $i <= $nbUrls; $i++){
						
						$wr = $res['r'.$i.'_rubrique_type'] == RTYPE_SITEROOT ? $this->getDefWebRoot($res['r'.$i.'_rubrique_url_'.LG_DEF]) : '';
						
						
						$this->selectedArbo[] = $res['r'.$i.'_rubrique_id'];
						if(!akev($GLOBALS['tabUrl'],$res['r'.$i.'_rubrique_id'])) {
							
							$GLOBALS['tabUrl'][$res['r'.$i.'_rubrique_id']] = array(
										 'fkRub'=>$res['r'.$i.'_fk_rubrique_id'],
										 'gabarit'=>$res['r'.$i.'_fk_gabarit_id'],
										 'param'=>$res['r'.$i.'_rubrique_gabarit_param'],
										 'option'=>$res['r'.$i.'_rubrique_option'],
										 
									     'type' => $res['r'.$i.'_rubrique_type'],		
									     'selected'=> true,
									    /* 'isFolder'=> $res['r'.$i.'_rubrique_is_folder'],	*/
									    
										);
						
										
							reset($_Gconfig['LANGUAGES']);
							foreach($_Gconfig['LANGUAGES'] as $lg) {
								$GLOBALS['tabUrl'][$res['r'.$i.'_rubrique_id']]['link_'.$lg] = $res['r'.$i.'_rubrique_link_'.$lg];
								$GLOBALS['tabUrl'][$res['r'.$i.'_rubrique_id']]['titre_'.$lg] = $res['r'.$i.'_rubrique_titre_'.$lg];
								$GLOBALS['tabUrl'][$res['r'.$i.'_rubrique_id']]['url'.$lg] = $res['r'.$i.'_rubrique_url_'.$lg];
								
							}
						}
					}

					
				}
			}

		}
		
		
		/**
		 * Si vraiment on a pas trouvé de page => 404
		 * @deprecated Normalement on a deja retourne un 404 plus haut
		 */
		if($this->rubId == ''){
			header ('HTTP/1.1 404 Not Found');
			$GLOBALS['_gensite']->isCurrent404 = true;
			$this->rubId = getRubFromGabarit('genSitemap');
			if(!$this->rubId) {
				$this->die404();
			}
			return $this->rubId;
		}

		//debug($this->rubId);
		return $this->rubId;
	}

	
	function die404() {
		
		echo '<h1>Error 404</h1><p>The page can not be found</p><p><a href="/">Go back</a></p>';
		die();
						
	}
	
	/**
	 * Retourne la seconde langue acceptable
	 * @deprecated 
	 *
	 * @return unknown
	 */
	function otherLg() {
		
		return getOtherLg();
		global $_Gconfig;
		
		if(LG != LG_DEF)
			return LG_DEF;
		else 
		    return $_Gconfig['LANGUAGES'][1];
		    
		return ($this->lg == 'fr' ? 'en' : 'fr');

	}
	
	
	
	/**
	 * alias de 
	 * @uses myLocale
	 *
	 * @param unknown_type $lg
	 */
	function setLocale($lg) {
		myLocale($lg);
		
	}

	
	/**
	 * Vide les cases vide d'un tableau
	 *
	 * @param unknown_type $tab
	 * @return unknown
	 */
	function trimTab($tab){
		$newTab = array();
		global $_Gconfig;
		if($_Gconfig['onlyOneLgForever']) {
			$cpt = 2;
		} else {
			$cpt = 1;
		}
		foreach($tab as  $value){

			if(!empty($value)) {
				if($cpt>1) {
					$newTab[$cpt-1] = niceName($value);					
				}
				$cpt++;
			}
		}
		
		return $newTab;
	}


	/**
	 * Retourne l'URL courante dans l'autre langue
	 * @deprecated 
	 * 
	 * @return unknown
	 */
	function getUrlForOtherLg() {
		//debug($GLOBALS['tabUrl']);
		$p = is_array($this->otherLgParamsUrl) ?  $this->otherLgParamsUrl : $this->paramsUrl;

		//debug($this->otherLgParamsUrl);
		return $this->buildUrlFromId(0,$this->otherLg(),$p);

	}

	/**
	 * Construit l'URL complète vers une rubrique dans une langue donnée avec les paramètrse et les actions voulus
	 *
	 * @param int $rubId
	 * @param str $lg
	 * @param array $params
	 * @param array $action
	 * @return string
	 */
	function buildUrlFromId($rubId=0,$lg='',$params=array(),$action=''){
		
		global $_Gconfig;
		
		if($rubId == 0) {
			$rubId = $this->getRubId();			
		}
		
		if(!$rubId) {
			return;
		}
		$cachename = md5($rubId.$lg.var_export($params,true));
		
		if(ake($GLOBALS['urlCached'],$cachename) && !$action) {
			
			return $GLOBALS['urlCached'][$cachename];
		}
			
		if(!array_key_exists($rubId, $GLOBALS['tabUrl'])) {
			$this->reversRecursRub($rubId);
		}
		
	
		if($GLOBALS['tabUrl'][$rubId]['type'] == 'link') {			
			$url = GetLgValue('link',$GLOBALS['tabUrl'][$rubId],false);	
						
			$GLOBALS['urlCached'][$cachename] = $url;
			return $url;		
			
		} else {			
			$url = $this->buildUrl($rubId,$lg);		
			if(is_array($url)) {
				$params = array_merge($url[1],$params);
				$url = $url[0];
			}
			$url = path_concat(BU,$url,$this->addParams($params));	
			
			if(strlen($action )) {
				
				$url = path_concat($url,'_action',$action);
			}
		}
		
		/**
		 * Si on est dans un mini site en sous domaine
		 */
		if($this->curLinkRoot ) {			
			//$url = path_concat('http://',$this->curLinkRoot['url'.LG].$_Gconfig['minisite_sous_domaine'],$url);
			
			//$url = path_concat('http://',$GLOBALS['tabUrl'][$rubId]['webroot'],$url);
			
			if($this->curLinkRoot['url'.LG]) {
				if(strstr($_Gconfig['minisite_sous_domaine'],$this->curLinkRoot['url'.LG]) ) {
					$url = path_concat('http://',$this->curLinkRoot['url'.LG],$url);
				} else {
					$url = path_concat('http://',$this->curLinkRoot['url'.LG].$_Gconfig['minisite_sous_domaine'],$url);
				}
				
			} else  {
				$url = path_concat('http://',$this->curLinkRoot['rubrique_url_'.LG].$_Gconfig['minisite_sous_domaine'],$url);
			}
					
			
		} else {

			$rub = $rubId;
			
			while(!akev($GLOBALS['tabUrl'][$rub],'webroot') && $rub > 0 && $rub != 'NULL') {
				
				$rub = $GLOBALS['tabUrl'][$rub]['fkRub'];
				
			}
			

			if(akev($GLOBALS['tabUrl'][$rub],'webroot') && $GLOBALS['tabUrl'][$rub]['webroot'] != akev($GLOBALS['tabUrl'][$this->getRubId()],'webroot')) {
				$url = path_concat($_Gconfig['protocole'].'://',$GLOBALS['tabUrl'][$rub]['webroot'],$url);
			}

			

		}
		$bddPart = explode('/'.GetParam('fake_folder_param').'/',$url);
		if(count($bddPart) > 2) {
			$url = $bddPart[0].'/'.GetParam('fake_folder_param').'/'.$bddPart[1];
			if($bddPart[2]) {
				$url.= '/'.$bddPart[2];
			}
		}
		
		$GLOBALS['urlCached'][$cachename] = $url;
		
		return $url;
		
	}


	/**
	 * Retourne l'URL de la page courante avec des paramètres différents
	 *
	 * @param array $params
	 * @return string
	 */
	function getUrlWithParams($params) {
		return $this->buildUrlFromId(0,'',$params);
	}


	/**
	 * Retourne l'URL courante telle quelle
	 *
	 * @return string
	 */
	function getCurUrl() {
		return $this->buildUrlFromId(0,'',$this->paramsUrl);
	}

	
	
	/**
	 * Ajoute des paramètres à l'URL courant
	 *
	 * @param unknown_type $params
	 * @return unknown
	 */
	function addParams($params) {
		if(is_array($params) && count($params) > 0) {
			$url = ''.GetParam('fake_folder_param').'/';
			foreach($params as $k => $v) {
				if(is_array($v)) {
					
					$k = $k.'__list';
					//$v = implode('_-_',$v);
					$v = serialize($v);
				}
				$url = path_concat($url,$k);
				if($v) {
					$url = path_concat($url,urlencode($v));
				} else {
					$url .= '//';
				}
			}
			return $url;
		}
		return '';

	}



	/**
	 * Redefinit certains paramètre pour l'autre langue
	 * @deprecated 
	 *
	 * @param unknown_type $params
	 */
	function setOtherLgParams($params) {

		$this->otherLgParamsUrl = $params;
	}

	
	/**
	 * Tant qu'on a une rubrique au dessus, on remonte
	 *
	 * @param unknown_type $rubId
	 * @return unknown
	 */
	function reversRecursRub($rubId){
         global $_Gconfig;
		
         if(!$rubId)
         	return;
         	
		if(!is_array(akev($GLOBALS['tabUrl'],$rubId))) {
			$sql = 'select R1.* ,
				   R1.rubrique_id as rubId,
				   R2.rubrique_id as p_rubId,
				  
				   R2.fk_rubrique_id as p_fkRubId
				   from s_rubrique as R1, s_rubrique as R2
				   where R1.fk_rubrique_id = R2.rubrique_id
				   '.sqlRubriqueOnlyOnline('R1').'
				   and R1.rubrique_id = ' .sql($rubId);
			$res = GetSingle($sql);
			

			if(!is_array(akev($GLOBALS['tabUrl'],$res['rubId']))) {
				$rub = $GLOBALS['tabUrl'][$res['rubId']] = array(
											'fkRub' => $res['p_rubId'],
											'gabarit'=>$res['fk_gabarit_id'],
											 'param'=>$res['rubrique_gabarit_param'],
											/* 'isFolder'=>$res['rubrique_is_folder'],*/
											 'option'=>$res['rubrique_option'],
															 
										     'type' => $res['rubrique_type'],
										     'p_fkRubId' => $res['p_fkRubId'],
										     'selected'=> in_array($res['rubId'],$this->selectedArbo)	
										     );
										     
				if($res['rubrique_type'] == RTYPE_SITEROOT ) {
						$rub['webroot'] = $GLOBALS['tabUrl'][$res['rubId']]['webroot'] = $this->getDefWebRoot($res['rubrique_url_'.LG_DEF]);					
				}									     
	
				reset($_Gconfig['LANGUAGES']);
				foreach($_Gconfig['LANGUAGES'] as $lg) {
					$GLOBALS['tabUrl'][$res['rubId']]['link_'.$lg] = $res['rubrique_link_'.$lg];
					$GLOBALS['tabUrl'][$res['rubId']]['titre_'.$lg] = $res['rubrique_titre_'.$lg];
					$GLOBALS['tabUrl'][$res['rubId']]['url'.$lg] = $res['rubrique_url_'.$lg];				
				}
			} else {
				$rub  = $GLOBALS['tabUrl'][$res['rubId']];
			}
									     

		}else{
			$rub  = $GLOBALS['tabUrl'][$rubId];
		}

		if(akev($rub,'p_fkRubId') != NULL){
			return $this->reversRecursRub($rub['fkRub']);
		}

		return $GLOBALS['tabUrl'];
	}

	
	/**
	 * Construction de l'URL d'une page
	 *
	 * @param int $rubId Identifiant de la page
	 * @param str $lg Langue
	 * @return string URL
	 */
	function buildUrl($rubId,$lg){
		global $_Gconfig;
		
		$lg = strlen($lg) ? $lg : $this->lg;
		$reallg = $lg;
	
		/**
		 * Si la langue demandée n'est pas dans la liste des langues par défaut
		 * c'est que c'est une traduction ponctuelle
		 * donc : /fr-it/
		 */
		if(!in_array($lg,$_Gconfig['LANGUAGES'])){
			$lg = LG.'-'.$lg;
			$reallg = LG;
		}
		
		$url = '';
		$key = $rubId;
		
		if($_Gconfig['onlyOneLgForever']) {
			$lg = '';
		}
		
		/**
		 * Si on ne demande pas la page racine
		 */
		$this->curLinkRoot = array();
		if($rubId != $this->root_id) {
			while (array_key_exists($key, $GLOBALS['tabUrl'])){
				if($GLOBALS['tabUrl'][$key]['type'] != 'menuroot') {
					/**
					 * Distinction pour les "mini sites" en "siteroot" au milieu du site avec des regles d'URL à part
					 */
					if($GLOBALS['tabUrl'][$key]['type'] != 'siteroot') {
						$url = path_concat($GLOBALS['tabUrl'][$key]['url' .$reallg] ,$url);
						
					} else {
						$this->curLinkRoot = $GLOBALS['tabUrl'][$key];
						
						break;
					}
					
				}
				$key = $GLOBALS['tabUrl'][$key]['fkRub'];
			}
			
		}
		
		
		/**
		 * Si jamais on demande aux pages de pointer vers la premiere sous page
		 */
		//if($_Gconfig['rubLinkToSub'] && $rubId != $this->root_id && $GLOBALS['tabUrl'][$rubId]['type'] != RTYPE_SITEROOT) {
		if(
			$rubId != $this->root_id &&
			(
				$GLOBALS['tabUrl'][$rubId]['type'] == 'folder' ||		
				$GLOBALS['tabUrl'][$rubId]['type'] == RTYPE_SITEROOT
			)
			
			) {
		
			$subId = $rubId;
			//$subs = $this->recursRub($subId,1,1);		
			if(rubHasOption($GLOBALS['tabUrl'][$rubId]['option'],'dynSubRubs')) {
			
				$subs = getGabaritSubRubs(getRowFromId('s_rubrique',$rubId),$GLOBALS['tabUrl'][$rubId]['gabarit']);
				return array(path_concat('/'.$lg,$url),array($subs[0]['PARAM']=>$subs[0]['VALUE']));
			} else {
				$subs = $this->recursRub($subId,1,1);		
			}
			/**
			 * On parcourt $SUBS et pour chaque sous rubrique ayant au moins une sous rubrique on recommence
			 */
			
			
			while(count($subs)) {
				$subtab = array_shift($subs);
				
				$subId = $subtab['id'];
				if($subtab['type'] != 'menuroot') {
					$url = path_concat($url,$subtab['url'.$reallg]);
				}
				if($subtab['type'] == 'folder') {
					$subs =  $this->recursRub($subId,1,1);
				} else {
					break;
				}
			}
			
			if($subId != $rubId) {
				return $this->buildUrl($subId,$lg);
			}		
			
		}
		
		return path_concat('/'.$lg ,$url );
	}

	
	/**
	 * Methode parcourant toute l'arborescence du site et affiche toutes les urls
	 *
	 * @param int $rubId Rubrique mère
	 * @param int $curLevel Niveau actuel, laisser à 1 par défaut
	 * @param int $maxLevel Combien de récursion atteindre ?
	 * @return array Tableau 
	 */
	function recursRub($rubId, $curLevel=1, $maxLevel=99){
		
		if(!$rubId)	
			return false;
			
		

		global $_Gconfig;
		
		
		if(ake($GLOBALS,'recursDone') && ake($GLOBALS['recursDone'],$rubId.'-'.$maxLevel) ) {
			return $GLOBALS['recursDone'][$rubId.'-'.$maxLevel];
		} 
		
		
		
		/**
		 * Sélection de toutes ses sous rubriques
		 */
		$sql = 'SELECT
			   R2.*
			   from  s_rubrique as R2
			   where R2.fk_rubrique_id
			   ' .sqlParam($rubId).' 
			   ' .sqlRubriqueOnlyOnline('R2').'
			   ' .sqlRubriqueOnlyReal('R2').' 
			   AND rubrique_type != "menu" 
			   AND rubrique_type != "menuroot"  
			   ORDER BY R2.rubrique_ordre ASC' ;		
		
		$res = GetAll($sql);
		

		
		if(!count($res)&& USE_DYNSUBRUBS) {
			
			$r = getRowFromId('s_rubrique',$rubId);
			if(rubHasOption($r['rubrique_option'],'dynSubRubs')) {
				$subs = getGabaritSubRubs($r,$r['fk_gabarit_id']);
				foreach($subs as $v) {
						$k = getUrlFromId($rid).'_'.$v['VALUE'];
						$tabTemp[$k] = array(
							'fkRub'=>$rubId,
							'url'=>getUrlFromId($rid,LG,array($v['PARAM']=>$v['VALUE'])),
							'titre'=>$v['NAME'],
							'type'=>'fake'								
						);
						if($_REQUEST[$v['PARAM']] == $v['VALUE']) {
							$tabTemp[$k]['selected'] = true;
						}
						
						
				}
				
				return $tabTemp;
			}
		}
		
		/**
		 * On parcourt toutes les sous rubriques
		 */
		foreach($res as $sRub){
		
		
			if($sRub['rubrique_etat'] != 'en_ligne')
				$sRub['rubrique_titre_fr'] .=  ' '.t('invisible_rub');
				
			/**
			 * La rubrique en cours est elle selectionnee ?
			 */
			$sel = in_array($sRub['rubrique_id'],$this->selectedArbo);
			
			
			
			/**
			 * On stock le tabUrl du GenUrl
			 * Un cache temporaire
			 */
			$doIt = true;
			
			if(rubHasOption($sRub['rubrique_option'],'dynVisibility')) {
				
				$res = getGabaritVisibility($sRub['fk_gabarit_id']);
				
				if(!$res) {
					$doIt = false;
				}
				
			}
			
			if($doIt) {
				if(!ake( $GLOBALS['tabUrl'],$sRub['rubrique_id']) ) {
				    $GLOBALS['tabUrl'][$sRub['rubrique_id']] = array(
											     'fkRub' => $rubId,	
											     'gabarit'=>$sRub['fk_gabarit_id'],
												 'param'=>$sRub['rubrique_gabarit_param'],
												 'option'=>$sRub['rubrique_option'],		
												 												 
											     'type' => $sRub['rubrique_type'],
											    /* 'isFolder' => $sRub['rubrique_is_folder'],*/
											     'selected'=> $sel
											         
											     );
											     
					reset($_Gconfig['LANGUAGES']);
					
					foreach($_Gconfig['LANGUAGES'] as $lg) {
						$GLOBALS['tabUrl'][$sRub['rubrique_id']]['link_'.$lg] = $sRub['rubrique_link_'.$lg];
						$GLOBALS['tabUrl'][$sRub['rubrique_id']]['titre_'.$lg] = $sRub['rubrique_titre_'.$lg];
						$GLOBALS['tabUrl'][$sRub['rubrique_id']]['url'.$lg] = $sRub['rubrique_url_'.$lg];
					}
				
				}
				/**
				 * On récupere l'URL de cet élément
				 */
				$mu = $this->buildUrlFromId($sRub['rubrique_id']);
				
				/**
				 * Second tableau de stockage à retourner
				 */
				$tabTemp[$mu] = array('id'=>$sRub['rubrique_id'],
										'url' => $mu,
										    'titre' => GetLgValue('rubrique_titre',$sRub,false),
										    'type' => $sRub['rubrique_type'],										  
										  	'selected'=>$sel					    
										    );
				reset($_Gconfig['LANGUAGES']);
				foreach($_Gconfig['LANGUAGES'] as $lg) {
					$tabTemp[$mu]['url'.$lg] = $sRub['rubrique_url_'.$lg];				
				}									    
									    
	
					
				/**
				 * Et on fait la récursion 
				 */
				if($curLevel < $maxLevel) {
					if(rubHasOption($sRub['rubrique_option'],'dynSubRubs')) {
						$rid = $sRub['rubrique_id'];
						$subs = getGabaritSubRubs($sRub,$sRub['fk_gabarit_id']);
						
						foreach($subs as $v) {
							$tabTemp[$mu]['sub'][getUrlFromId($rid).'_'.$v['VALUE']] = array(
								'fkRub'=>$rid,
								'url'=>getUrlFromId($rid,LG,array($v['PARAM']=>$v['VALUE'])),
								'titre'=>$v['NAME'],
								'type'=>'fake'								
							);
						}
					} else {
						$tabTemp[$mu]['sub'] = $this->recursRub($sRub['rubrique_id'], $curLevel+1, $maxLevel);
					}
				}
					
				}
	}

		$GLOBALS['recursDone'][$rubId] = $tabTemp;
		return $tabTemp;
	}
	

	/* Methode permettant de construire le "chemin de fer" de la page en-cours */
	function buildRoad($curId=0){
		//global $rootId;

		if(!$curId) {
			$curId = $GLOBALS['site']->getCurId();
		}
		
		if(!array_key_exists($curId, $GLOBALS['tabUrl']))
			$this->reversRecursRub($curId);

		$lg = $this->lg;
		$key = $curId;
		$road = array();
		


		if($curId != $this->rootHomeId ){ //getParam('rub_home_id')
			$i=1;
			while (array_key_exists($key, $GLOBALS['tabUrl']) && $key &&  $i <= 100){
				
				$i++;
				
				if($GLOBALS['tabUrl'][$key]['type'] != RTYPE_MENUROOT) {							
					$road[] = array('id'=>$key,
								'titre' => getLgValue('titre',$GLOBALS['tabUrl'][$key]),
								'url' => $this->buildUrlFromId($key));
				}
				$key = $GLOBALS['tabUrl'][$key]['fkRub'];
			}
			
			

			$road[] = array('titre' => t('cp_txt_home'),
						'url' => $this->buildUrlFromId($this->rootHomeId));//getParam('rub_home_id')

			$road = array_reverse($road);

			foreach($this->roadSup as $r) {
				$road[] = $r;
			}
		}

		return $road;
	}

	
	/**
	 * La rubrique $row a t'elle une rubrique Après ?
	 *
	 * @param unknown_type $row
	 * @return unknown
	 */
	
	function hasNextRub($row) {

		$sql = 'SELECT rubrique_ordre FROM s_rubrique WHERE fk_rubrique_id ="'.$row['fk_rubrique_id'].'"  '.sqlRubriqueOnlyReal().' AND rubrique_ordre > "'.$row['rubrique_ordre'].'" ORDER BY rubrique_ordre';

		$res = GetAll($sql);
		if(count($res)) {
			return true;
		}
		return false;

	}

	/**
	 * La rubrique $row a t'elle une rubrique AVANT ?
	 *
	 * @param unknown_type $row
	 * @return unknown
	 */
	function hasPreviousRub($row) {
		$sql = 'SELECT rubrique_ordre FROM s_rubrique WHERE fk_rubrique_id ="'.$row['fk_rubrique_id'].'"  '.sqlRubriqueOnlyReal().' AND rubrique_ordre < "'.$row['rubrique_ordre'].'" ORDER BY rubrique_ordre';

		$res = GetAll($sql);
		if(count($res)) {
			return true;
		}
	}

	/* Methode qui permet d'ajouter un element au tableau des rubriques hors bdd */
	function addRoad($titre, $url=''){
		$this->roadSup[] = array('titre'=>$titre,'url'=>$url);
	}

	/* Methode qui retourne le niveau de profondeur */
	function getDepth($rubid=0) {
		$rubid = $rubid ? $rubid : $this->getRubId();

		if(!array_key_exists($curId, $GLOBALS['tabUrl']))
			$this->reversRecursRub($curId);
	}

	/* Methode qui permet de recuperer la langue du navigateur client */
	function getBrowserLang(){
		global $_Gconfig;
		$langs=explode(",",$_SERVER["HTTP_ACCEPT_LANGUAGE"]);
	
		foreach ($langs as $value) {
			$choice=substr($value,0,2);
			if(in_array($choice,$_Gconfig['LANGUAGES'])) {
				
				return $choice;
			}
		}
		return LG_DEF;

	}



}

?>
