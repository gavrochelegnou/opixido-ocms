<?php

global $isTopNavRub, $noCopyTable, $tab_noCopyField,$tabForms,$uploadRep,$relations,$relinv,$tablerel,$searchField,$specialUpload,$previewField,$orderFields,$adminMenus,$rteFields,$neededFields,$neededSymbol,$uploadFields,$mailFields,$validateFields,$adminInfos,$gs_roles,$gs_actions,$formsRep,$frontAdminTrads,$gr_on, $rootId, $homeId, $headRootId, $footRootId,$basePath,$baseWebPath, $lexiqueId,$languages,$google_key,$_Gconfig,$adminTypesToMail,$functionField,$multiVersionField,$_Gconfig;


//session_set_cookie_params ( 3600 );
session_start();
$_SESSION['cache'][UNIQUE_SITE] = array();

header('Content-Type: text/html; charset=utf-8');

define('RTYPE_SITEROOT','siteroot');
define('RTYPE_MENUROOT','menuroot');
define('RTYPE_LINK','link');

define('VERSION_FIELD','fk_version');
define('MULTIVERSION_FIELD','ocms_version');
define('MULTIVERSION_FIELDNAME','ocms_version_name');
define('MULTIVERSION_STATE','ocms_etat');
define('ONLINE_FIELD','en_ligne');

define('crypto_cipher','rijndael-256');
define('crypto_mode','cbc');


/**
 * Quel champs pour la date de mise a jour
 */
$_Gconfig['field_date_maj'] = 'ocms_date_modif';
$_Gconfig['field_date_crea'] = 'ocms_date_crea';


$_Gconfig['noCopyField'] = array(VERSION_FIELD,ONLINE_FIELD,MULTIVERSION_FIELD,MULTIVERSION_FIELDNAME,MULTIVERSION_STATE,$_Gconfig['field_date_maj'],$_Gconfig['field_date_crea']);

/* Vrai debut de configuration */

$uploadRep="fichier/";
$basePath = $_Gconfig['basePath'];
$baseWebPath = $_Gconfig['baseWebPath'];


$_Gconfig['cacheGlobales'] = array('recursDone','tabUrl','urlCached');
$_Gconfig['field_creator'] = 'ocms_creator';

/**
 * TABFORMS
 * $tabForms['TABLE']['pages'] = array(FORMULAIRES);
 * $tabForms['TABLE']['titre'] = array(CHAMPS);
 */
$tabForms = array();
$tabForms['s_rubrique']['pages'] = array(
					'contenu'=>array('form.rubrique.php'),
					'referencement'=>array('form.rubrique4.php'),
					'proprietes'=>array('form.rubrique1.php')
					);
//,'form.rubrique3.php','form.rubrique5.php','form.rubrique6.php','form.rubrique7.php'
$tabForms['s_rubrique']['titre'] = array('rubrique_titre');

$tabForms['s_plugin']['titre'] = array('plugin_nom','plugin_actif');
$tabForms['s_plugin']['pages'] = array('form.plugin.php');

$tabForms['s_gabarit']['pages'] = array('form.gabarit.php');
$tabForms['s_gabarit']['titre'] = array('gabarit_titre');

$tabForms['s_admin']['pages'] = array('form.admin.php','admin_droits_specifiques'=>'form.admin2.php');
$tabForms['s_admin']['titre'] = array('admin_nom');



$tabForms['s_paragraphe']['pages'] = array('form.paragraphe.type.php');
$tabForms['s_paragraphe']['titre'] = array('paragraphe_titre','paragraphe_contenu','fk_para_type_id','paragraphe_img_1'); //,'paragraphe_ordre'


$tabForms['s_para_type']['pages'] = array('form.s_para_type.php');
$tabForms['s_para_type']['titre'] = array('para_type_titre');



$tabForms['s_trad']['pages'] = array('form.trad.php');
$tabForms['s_trad']['titre'] = array('trad_id', 'trad');


$tabForms['s_admin_trad']['pages'] = array('form.admin_trad.php');
$tabForms['s_admin_trad']['titre'] = array('admin_trad_id', 'admin_trad');

$tabForms['s_param']['pages'] = array('form.param.php');
$tabForms['s_param']['titre'] = array('param_id','param_description', 'param_valeur');


$tabForms['s_role']['pages'] = array('form.s_role.php');
$tabForms['s_role']['titre'] = array('role_nom');


$tabForms['s_role_table']['pages'] = array('form.admin_role_table.php');
$tabForms['s_role_table']['titre'] = array('role_table_table');


/**
 * Quand on modifie le champ X on reload le formulaire
 *
 * $_Gconfig['reloadOnChange'] = array('TABLE.CHAMP');
 */
$_Gconfig['reloadOnChange'] = array('s_rubrique.fk_gabarit_id','s_rubrique.rubrique_type');


/**
 *  On definit les relations
 *  simples (clef externe)
 *
 *  $relations['TABLE']['FK_CHAMP'] = 'FK_TABLE';
 */
$relations = array();
$relations['s_rubrique']['fk_gabarit_id'] = 's_gabarit';
$relations['s_rubrique']['fk_creator_id'] = 's_admin';
$relations['s_paragraphe']['fk_para_type_id'] = 's_para_type';
$relations['s_gabarit']['fk_default_rubrique_id'] = 's_rubrique';


/**
 *  Relations inverses
 *  Toutes les entrees de X table qui pointent vers moi
 *
 *  $relinv['TABLE PARENTE']['NOM_DU_FAUX_CHAMP'] = array('TABLE FILLE','CLEF EXTERNE');
 */
$relinv = array();
$relinv['s_rubrique']['fk_paragraphe_id'] = array('s_paragraphe','fk_rubrique_id');
$relinv['s_rubrique']['fk_rubrique_id'] = array('s_rubrique','fk_rubrique_id');
$relinv['s_role']['fk_role_table_id'] = array('s_role_table','fk_role_id');

$relinv['s_plugin']['fk_trad_id'] = array('s_trad','fk_plugin_id');

$relinv['s_plugin']['fk_param_id'] = array('s_param','fk_plugin_id');



$_Gconfig['ajaxRelinv']['s_rubrique']['FAUXPARA'] = array('s_paragraphe','fk_rubrique_id',array('fk_para_type_id','paragraphe_titre','paragraphe_contenu','param_img_1'));





$relinv['s_plugin']['fk_admin_trad_id'] = array('s_admin_trad','fk_plugin_id');

/**
 *  On definit les tables de relation
 *
 *  $tablerel['TABLE_RELATION'] = array('FK_CHAMP1'=>'FK_TABLE1','FK_CHAMP2'=>'FK_TABLE2');
 */

$tablerel = array();

$tablerel['s_admin_role'] = array('fk_role_id'=>'s_role','fk_admin_id'=>'s_admin');


/**
 * Liste des champs "Cherchables" dans l'admin
 * $searchField['TABLE'] = array('CHAMPS');
 */
$searchField  = array();
$searchField['s_rubrique'] = array('rubrique_id', 'rubrique_titre','rubrique_titre', 'rubrique_etat','fk_creator_id');
$searchField['s_admin'] = array('admin_nom','admin_login','s_admin_role');




/**
 * Liste des champs de type PASSWORD
 *
 * $_Gconfig['passwordFields'] = array('CHAMPS');
 */
$_Gconfig['passwordFields'] = array('admin_pwd');


//'rubrique_date_crea','rubrique_date_modif','rubrique_date_publi'

/* Comment on upload les fichiers
        Parametres posibles :

        *FIELD*
        *NAME*
        *ID*
        *TABLE*
        *EXT*
*/



/**
 *  Version par defaut pour tous les champs non speciaux
 **/
$specialUpload["genfile_default"]["genfile_default"]["system"] = $basePath."/fichier/*TABLE*/*ID*/";
$specialUpload["genfile_default"]["genfile_default"]["name"] = "*FIELD*_*NAME*.*EXT*";
$specialUpload["genfile_default"]["genfile_default"]["web"] = "/fichier/*TABLE*/*ID*/";



/**
 * On definit la liste des champs
 * qui afficheront un bouton preview
 * et les champs a afficher
 *
 * $previewField['TABLE']['CLEF EXTERNE'] = 'CHAMP DE LA TABLE EXTERNE A AFFICHER';
 */

$previewField  = array();
//$previewField['s_paragraphe']['fk_para_type_id'] = 'para_type_vignette';


/**
 *      On definit la liste des champs
 *       qui gerent l'ordre
 *       On ajoute et regenere l'ordre directement
 *
 *  $orderFields['TABLE'] = array('CHAMP_ORDRE','EVENTUELLEMENT LE CHAMP DE SELECTION (seulement pour le fk = ....');
 */

$orderFields  = array();
$orderFields['s_rubrique'] = array('rubrique_ordre','fk_rubrique_id');
$orderFields['s_paragraphe'] = array('paragraphe_ordre','fk_rubrique_id');







/**
 * Liste des champs IMAGES / UPLOAD ou l'on retaille l'image automatiquement
 * si elle est plus large ou pour haute que les valeurs ci-dessous
 *
 * $_Gconfig['imageAutoResize'] = array('FIELD_NAME'=>array(MAXWIDTH,MAXHEIGHT));
 *
 */




/**
 *  Liste des champs qui doivent etre en RTE (Wysiwyg)
 *  $rteFields = array('CHAMPS');
 **/
$rteFields = array('paragraphe_contenu','paragraphe_contenu');


/**
 *  Liste des champs obligatoires
 *
 * $neededFields = array(CHAMPS);
 **/
$neededFields = array('fk_collection_id','rubrique_titre','publi_titre','auteur_nom');


/**
 *  Symbole utilise pour signaler un champ obligatoire
 **/
$neededSymbol = "<span class='genform_neededsymbol'>*</span>";


/**
 *  Liste des champs de type UPLOAD
 *
 *  $uploadFields = array(CHAMPS OU FIN DE CHAMPS);
 **/

// NE PAS METTRE _img DANS CE TABLEAU SINON CA UPLOAD LES ALT DES IMAGES
//$uploadFields = array('_download','_screenshot', '_flag','fichier_fichier', '_swf', '_pdf', '_pdf1', '_pdf2', '_picto','img_1','img_1', '_img', 'img','1_img','2_img','3_img','4_img','_vignette','paragraphe_file_1','paragraphe_file_1','image_img','img_2','img_2_en','img_2_es');

$uploadFields = array('paragraphe_img_1', 'paragraphe_img_1_en','paragraphe_img_1','paragraphe_img_2', 'paragraphe_img_2_en','paragraphe_img_2','paragraphe_file_1', 'paragraphe_file_1_en');

/**
 *  Liste des champs de type MAIL
 *
 * $mailFields[] = CHAMP;
 **/
$mailFields = array('_mail','_courriel', '_email');



/**
 * Images à retailler automatiquement
 * RETAILLAGE EXACT !
 * Ne conserve pas les proportions
 *
 * $_Gconfig['imageAutoResize']['CHAMP'] = array(LARGEUR,HAUTEUR);
 */
/*
$_Gconfig['imageAutoResize']['rubrique_fond_img']  = array(683,204);
$_Gconfig['imageAutoResize']['paragraphe_img_1']  = array(500,500);
$_Gconfig['imageAutoResize']['paragraphe_img_1_en']  = array(500,500);
$_Gconfig['imageAutoResize']['paragraphe_img_1_es']  = array(500,500);
$_Gconfig['imageAutoResize']['paragraphe_img_2']  = array(500,500);
$_Gconfig['imageAutoResize']['paragraphe_img_2_en']  = array(500,500);
$_Gconfig['imageAutoResize']['paragraphe_img_2_es']  = array(500,500);
$_Gconfig['imageAutoResize']['image_img']  = array(727,204);
*/

$_Gconfig['imageAutoResizeExact'] = array();




/*****************************
 * GenUser / GenAdmin
 ******************************/

/*
        Les champs qui definissent si l'objet est visibile ou non
        pas encore utilises */
$validateFields = array();
$validateFields['s_rubrique'] = 'rubrique_visible';


/*
 Champs qui vont s'afficher avec le numerotage des lignes
 et une police à largeur fixe
*/
$_Gconfig['codeFields'] = array('para_type_template','para_type_dirty_template','bloc_home_code');


/**
 * Gestion des champs de type "Lien" ou "URL"
 */
$_Gconfig['urlFields'] = array('_url','_link_1','_lien_','_link');


/*
 Extensions de fichier non autorises
*/

$_Gconfig['notAllowedFileExtension'] = array('php','php3','php4','cgi','php5','sh','phtml');

/*
        Liste des tables ou l'on renvoit sur le meme enregistrement pour edition apres insertion
*/
$_Gconfig['updateAfterInsert'] = array(/*'t_publication',*/'t_site');


/* Table des administrateur, et clef externe pour les appropriations */
$adminInfos = array('s_admin'=>'fk_admin_id');



/******************************************
 * Actions à effectuer par objet
 */

/**
 * Rubriques
 */
$_Gconfig['rowActions']['s_rubrique'] = array('validate'=>true,'unvalidate'=>true,'moveRubrique'=>true);
//,'translate'=>true


//'ask_for_validation'=>true,'refuse'=>true,

/**
 * Plugins
 */
$_Gconfig['rowActions']['s_plugin'] = array('InstallPlugin'=>true,'UninstallPlugin'=>true);


/*******************************************
 * Liste des actions effectuables PAR TABLE
 *
 * Penser a rajouter pour chaque action dans le gs_roles['ROLE']['TABLE'] = array('actions'=>array('MES ACTIONS',...)
 * Pour autoriser les roles voulus à effectuer cette action
 *
 */
$_Gconfig['tableActions'] = array();

# Réindexer les campagnes
#$_Gconfig['tableActions']['s_image']= array('t_campagne_reindex');



/*******************************************
 * Gestion des actions globales à l'ensemble du site
 */
$_Gconfig['globalActions'] = array();

# Vider le cache
$_Gconfig['globalActions'][]= 'emptyCache';

# Reindexer le site pour la recherche et la tester
# $_Gconfig['globalActions'][]= 'reIndexSearch';
# $_Gconfig['globalActions'][]= 'testSearch';
# $_Gconfig['globalActions'][]= 'mostUsedWords';

# Afficher le phpInfo
$_Gconfig['globalActions'][]= 'showPhpInfo';

# Executer une requete SQL
$_Gconfig['globalActions'][]= 'executeSql';

# Réencoder les mots de passe
//$_Gconfig['globalActions'][]= 'encodePasswords';

# Ajouter ou supprimer des champs de langue
$_Gconfig['globalActions'][]= 'changeTranslations';
$_Gconfig['globalActions'][]= 'recheckTranslations';
$_Gconfig['globalActions'][]= 'setAllUrls';




/*
        Gestion des actions POST edition
         gr_onsave => Apres avoir la sauvegarde on efectue cette action
 */

$gr_on = array();

//$gr_on['save']['s_images'] = 's_images_updateImageSize';

$gr_on['insert']['s_rubrique'][] = 's_rubrique_createAll';
$gr_on['beforeDelete']['s_rubrique'][]  = 's_rubrique_beforeDelete';
$gr_on['update']['s_rubrique'][]  = 's_rubrique_update';
$gr_on['save']['s_admin'][]  = 's_admin_update';





/* Repertoire des formulaires */
$formsRep = gen_include_path."/forms/";


$functionField['admin_pwd']['before'] = 'decodePassword';
$functionField['admin_pwd']['after'] = 'encodePassword';



/***************
    GenFrontAdmin
    *********************/


$frontAdminTrads = array();
$frontAdminTrads["s_rubrique"] = "Rubrique";


$frontAdminTrads["trad_fr"] = "le Texte";
$frontAdminTrads["s_trad"] = "cette Traduction";
$frontAdminTrads["s_imagev"] = "cette Image Parametrable";



/**********************************************
        Tableau des champ a ne pas dupliquer
*********************************************/

$tab_noCopyField = array();
$tab_noCopyField['s_rubrique'] = array('rubrique_id', 'fk_rubrique_version_id');
$tab_noCopyField['s_paragraphe'] = array('fk_rubrique_id');


$noCopyTable = array('t_collection', 'r_rubrique_lexique', 's_paragraphe');


/**
 * Liste des clefs etrangeres  simple qui ne peuvent pas etre vide
 */

$_Gconfig['genform']['nonEmptyForeignKey'] = array(
	's_paragraphe.fk_para_type_id'
 );



 /**
  * Champs et relations à indexer pour chaque table
  */

$_Gconfig['iSearches']['s_rubrique']['relations'] = array(
											'fk_paragraphe_id'=>array('paragraphe_titre_'.LG_DEF, 'paragraphe_contenu_'.LG_DEF),
											'fk_mecenat_id'=>array('mecenat_titre','mecenat_titre_en', 'mecenat_texte','mecenat_texte_en','mecenat_stitre','mecenat_stitre_en')
										);




/**
 * Plus utilisé je pense ...
 * Déprécié pour $_Gconfig['versionedTable']
 * @deprecated

$multiVersionField['s_rubrique'] = 'fk_rubrique_version_id';
 */

/**
 * Liste des tables avec plusieurs versions possibles
 * $_Gconfig['multiVersionTable'][] = 'table';
 */

$_Gconfig['multiVersionTable'] = array();


/**
 * VersionedTable : Liste des tables ayant une possibilité de "double" version avec validation
 * CHACUNE DE CES TABLES DOIT AVOIR UN CHAMP ONLINE_FIELD (en_ligne)
 * ET UN CHAMP VERSION_FIELD (fk_version)
 */
$_Gconfig['versionedTable'] = array('t_publication');


/**
 * Definit les tables qui ont un statut en_ligne OUI/NON
 * CHACUNE DE CES TABLES DOIT AVOIR UN CHAMP ONLINE_FIELD (en_ligne)
 */
$_Gconfig['hideableTable'] = array('t_breve');


/**
 * Liste des extensions de fichiers de type images
 */
$_Gconfig['imageExtensions']=array('jpg','jpeg','gif','png','bmp');


/**
 * A voir ...
 */
$adminTypesToMail = array('valideur','administrateur');


/**
 * Tables à dupliquer en meme temps que les enregistrements de la rubrique
 */
$_Gconfig['duplicateWithRubrique'] = array('s_paragraphe');

/**
 * RELINV EN AJAX
 */
$_Gconfig['ajaxRelinv']['TABLE']['NOM_DU_FAUX_CHAMP'] = array('SOUS_TABLE','CLEF EXTERNE',array('LISTE DES CHAMPS A AFFICHER'));


/**
 *   On liste les tables par menu
 *  Ca simplifie le tout
 */
$_Gconfig['adminMenus'] = array();
$_Gconfig['adminMenus'][] = array( 's_rubrique');



$_Gconfig['adminMenus']['menu_admin'] = array( 's_admin','s_plugin','s_param','s_trad','s_admin_trad') ;



$_Gconfig['nonMassAction'] = array('edit','view');


define('ADMIN_PICTOS_FOLDER',ADMIN_URL.'pictos_stock/tango/');
define('ADMIN_PICTOS_ARBO_SIZE','16x16');
define('ADMIN_PICTOS_FORM_SIZE','22x22');
define('ADMIN_PICTOS_FRONT_SIZE','22x22');
define('ADMIN_PICTOS_BIG_SIZE','32x32');

define('FRONT_PICTOS_FOLDER','/img/pictos');

define('PLUGINS_FOLDER','plugins/');

define('DEFAULT_URL_VALUE','http://www.');

$GLOBALS['cans']= array();

?>