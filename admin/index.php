<?php
#
# This file is part of oCMS.
#
# oCMS is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# oCMS is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with oCMS. If not, see <http://www.gnu.org/licenses/>.
#
# @author Celio Conort / Opixido 
# @copyright opixido 2009
# @link http://code.google.com/p/opixido-ocms/
# @package ocms
#

ob_start();

define('IN_ADMIN',true);

error_reporting(E_ALL & ~E_NOTICE);

require_once('../include/include.php');


/* On aura toujours besoin de ca */

$gb_obj = new genBase();

$gb_obj->includeConfig();

$gb_obj->includeBase();

$gb_obj->includeGlobal();

$t = getmicrotime();

$genMessages = new genMessages();

$gb_obj->includeAdmin();

$lg = $_SESSION['lg'] = $_REQUEST['lg'] ? $_REQUEST['lg'] :(  $_SESSION['lg']  ? $_SESSION['lg'] : getBrowserLang() );
define('LG',$lg);
   		
   		
initPlugins();

$gs_obj = new genSecurity();

$gs_obj->needAuth();

loadParams();


if(isset($_REQUEST['reindex']) ) {
	
die();
}
if(isset($_REQUEST['popup'])){

	$gpopup = new genAdminPopup();
	$gpopup->gen();

}else if(isset($_REQUEST['gfa'])) {

	$gpopup = new genPopupAdmin($_REQUEST['curTable'],$_REQUEST['curId']);
	$gpopup->gen();
	
} else  if(isset($_REQUEST['xhr'])) {
	
	$gpopup = new genXhrAdmin($_REQUEST['curTable'],$_REQUEST['curId']);
	$gpopup->gen();
	
} else {

	$gadmin = new genAdmin($_REQUEST['curTable'],$_REQUEST['curId']);	
	
	$gadmin->gen();    


}


$genMessages->gen();


if(strstr($_SERVER['REMOTE_ADDR'],'192.168.1.') || strstr($_SERVER['REMOTE_ADDR'],'82.67.200.175') || $_REQUEST['debug']) {

	print($profileSTR);

}
