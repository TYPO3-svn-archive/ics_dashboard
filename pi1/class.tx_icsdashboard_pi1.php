<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 In Cite Solution <technique@in-cite.net>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Dashboard' for the 'ics_dashboard' extension.
 *
 * @author	Mickaël Paillard <mickael@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_icsdashboard
 */
class tx_icsdashboard_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_icsdashboard_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_icsdashboard_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'ics_dashboard';	// The extension key.
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_loadLL();
		$this->init();
		$use_even=($this->cObj->getSubpart(file_get_contents($this->template), '###TEMPLATE_MODULE_EVEN###')!='');
		$this->pi_setPiVarDefaults();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		$user_uid=0;
		$content='';
		$id_dashboard=($this->conf['tsPath'])?$this->conf['tsPath']:$this->cObj->data['uid'];
		$iddashboard=str_replace('.','',$id_dashboard);
		
		/*if($GLOBALS['TSFE']->fe_user->user['uid']==6){
			$registers = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_icsdashboard_register');
			$count=0;
			while( $register = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($registers) ) {
				$SameUserRegisters = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_icsdashboard_register','fe_user='.$register['fe_user']);
				$themodules=array();
				while( $SameUserRegister = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($SameUserRegisters) ) {
					$themodules[$SameUserRegister['module_uid']]=$SameUserRegister['module_uid'];
				}
				if ((count($themodules)==3)&&(in_array(5,$themodules))&&(in_array(6,$themodules))&&(in_array(4,$themodules))){
					$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_icsdashboard_register','fe_user='.$register['fe_user']);
					$count++;
				}
			}
			$content='registered deletion :'.t3lib_div::view_array($count);
		}*/
		
		if(( isset($this->piVars[ 'reset' ]) )&& (isset($this->piVars['id_dashboard']))){
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_icsdashboard_register','fe_user='.$GLOBALS['TSFE']->fe_user->user['uid'].' AND id_dashboard=\''.$this->piVars['id_dashboard'].'\'');
		}
		if ($this->conf['modules']!=''){
			if ($id_dashboard){
				$activeModules=($GLOBALS['TSFE']->fe_user->user['uid'])?$this->getUserModules($GLOBALS['TSFE']->fe_user->user['uid'],$id_dashboard):$this->conf['modules'];
				$limit=((isset($this->conf['nbMaxModules']))&&(($this->conf['nbMaxModules'])))?$this->conf['nbMaxModules']:'';
				$modules = ($GLOBALS['TSFE']->fe_user->user['uid'])?$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title,module_uid,pi_flexforms,id_dashboard',
					'tx_icsdashboard_register',
					'uid IN ('.$activeModules.')',
					'',
					'position',
					$limit
				):$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,content,name,pid,sys_language_uid',
						'tx_icsdashboard_modules',
						'uid IN ('.$activeModules.')'.
						$this->cObj->enableFields('tx_icsdashboard_modules'),
						'','',
					$limit
					);
				$even = false;
				$count = 1;
				while( $module = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($modules) ) {
					if(!$GLOBALS['TSFE']->fe_user->user['uid'])
							$module['module_uid']=$module['uid'];
					$default_modules = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,content,name,content_add,pid,sys_language_uid',
						'tx_icsdashboard_modules',
						'uid='.$module['module_uid'].
						$this->cObj->enableFields('tx_icsdashboard_modules')
					);
					
					if($default_module = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($default_modules)){
						//$default_module = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_icsdashboard_modules', $default_module, $GLOBALS['TSFE']->sys_language_uid);
						if(!$GLOBALS['TSFE']->fe_user->user['uid'])
							$module['title']=$default_module['name'];
						$markerArray=array();
						$module_content='';
						$module_content_add='';
						$contents=explode(',',$default_module['content']);
						$modules_flexforms=array();
						foreach ($contents as $key=>$content_uid){
							$rContents = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tt_content','uid='.$content_uid.$this->cObj->enableFields('tt_content'));
							if( $aContent = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rContents) ) {
								// check for user's flexform configuration for this module
								if ($module['pi_flexforms']!=''){
									$module_flexforms=unserialize($module['pi_flexforms']);
									if (isset($module_flexforms[$content_uid])){
										$aContent['pi_flexform']=$module_flexforms[$content_uid];
									}
								}
								$modules_flexforms[$module['module_uid']]=t3lib_div::xml2array($aContent['pi_flexform']);
								//Render module content
								$module_content .= $this->renderContentObject($aContent);
							}
						}
						$add_contents=explode(',',$default_module['content_add']);
						
						foreach ($add_contents as $key=>$content_uid){
							$rContents = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tt_content','uid='.$content_uid.$this->cObj->enableFields('tt_content'));
							if( $aContent = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rContents) ) {
								$aDefaultConf=t3lib_div::xml2array($aContent['pi_flexform']);
								if ($aContent['pi_flexform']!=''){
									$aDefaultConf['data'][$module['module_uid']]=($modules_flexforms[$module['module_uid']]!='')?$modules_flexforms[$module['module_uid']]:$aDefaultConf['data'][$module['module_uid']];
									$flexObj = t3lib_div::makeInstance('t3lib_flexformtools');
									$allFlexforms=$flexObj->flexArray2Xml($aDefaultConf, false);
									$aContent['pi_flexform']=$allFlexforms;
								}
								
								$module_content_add .= $this->renderContentObject($aContent);
							}
						}
						
						$markerArray['###EDIT_ICONS###']='<div class="ics_dashboard_edit_tools ics_dashboard_edit_tools_'.$iddashboard.'"></div>';
						$markerArray['###TITLE###']=($module['title']!='')?stripslashes($module['title']):stripslashes($default_module['name']);
						$markerArray['###CONTENT###']=$module_content;
						$markerArray['###CONTENT_ADD###']=$module_content_add;
						$markerArray['###MODULE_ID###']='ics'.$iddashboard.'_'.$module['uid'];
						$markerArray['###MODULE_CLASS###']='ics_dashboard_module ics_dashboard_moduletype_'.$default_module['uid'];
						if ($limit==$count)
							$markerArray['###MODULE_CLASS###'].=' last';
						if($even && $use_even){
							$content.= $this->viewTemplate('###TEMPLATE_MODULE_EVEN###',$markerArray);
						}
						elseif(($this->cObj->getSubpart(file_get_contents($this->template), '###TEMPLATE_MODULE_'.$count.'###')!='')){
							$content.= $this->viewTemplate('###TEMPLATE_MODULE_'.$count.'###',$markerArray);
						}
						else{
							$content.= $this->viewTemplate('###TEMPLATE_MODULE###',$markerArray);
						}
						$even = !$even;
						$count++;
					}
				}
				 
			}
		}
		$content=($content!='')?'<div id="ics_dashboard_dashboard_'.$iddashboard.'">'.$content.'</div>':'';
		$markerArray=array();
		$ddsort=($this->conf['ddsort']>0)?'true':'false';
		$showTools=(($this->conf['edit']>0)||($this->conf['minus']>0)||($this->conf['suppr']>0))?'true':'false';
		$showTheTools=(($this->conf['edit']>0)||($this->conf['minus']>0)||($this->conf['suppr']>0))?';showTheTools'.$iddashboard.'();showAddTool'.$iddashboard.'()':'';
		$markerArray['###ENABLE_EDIT_CLASS###']='ics_dashboard_add_edit_toollink ics_dashboard_add_edit_toollink_'.$iddashboard;
		$markerArray['###ENABLE_EDIT_JS###']=html_entity_decode('add_edit_tools(\''.$iddashboard.'\','.$ddsort.','.$showTools.')'.$showTheTools.';return false;');
		$enable_edit= $this->viewTemplate('###TEMPLATE_ENABLE_EDIT###',$markerArray);
		$markerArray=array();
		$markerArray['###USER_NAME###']=($GLOBALS['TSFE']->fe_user->user['name']!='')?$GLOBALS['TSFE']->fe_user->user['name']:$GLOBALS['TSFE']->fe_user->user['username'];
		$markerArray['###MODULES###']=$content;
		$content= $this->viewTemplate('###TEMPLATE_ENABLE_EDIT###',$markerArray);
		$markerArray['###ENABLE_EDIT###']=($GLOBALS['TSFE']->fe_user->user['uid'])?$enable_edit:'';
		
		$content= $this->viewTemplate('###TEMPLATE_DASHBOARD###',$markerArray);
		
		preg_match_all('/###TSOBJECT([0-9]*)###/is', $content,$aMatches);
		foreach((array)$aMatches[0] as $sMarker) {
			$sObjectName=strtolower(str_replace('###', '', $sMarker));
			$markerArray[$sMarker]=($this->conf[$sObjectName]!='')?$this->cObj->cObjGetSingle($this->conf[$sObjectName],$this->conf[$sObjectName.'.']):'';
		}
		
		$content= $this->viewTemplate('###TEMPLATE_DASHBOARD###',$markerArray);
		
		return $this->pi_wrapInBaseClass($content);
	}
	
	/**
	 * Recupere les modules de l utilisateur, cree les inscriptions par defaut s il le faut
	 *
	 * @param	int			utilisateur uid
	 * @param	int			identifiant du dashboard
	 * @return	string		liste uid de modules separes par des virgules
	 */
	function getUserModules($user_uid,$dashboard){
		$modules = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','tx_icsdashboard_register', 'fe_user='.$user_uid.' AND id_dashboard=\''.$dashboard.'\''.$this->cObj->enableFields('tx_icsdashboard_register'),'','position');
		$modules_list='';
		while( $module = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($modules) ) {
			$modules_list.=($modules_list=='')?$module['uid']:','.$module['uid'];
		}
		if ($modules_list==''){
			$modules_list=$this->conf['modules'];
			$register=explode(',',$modules_list);
			foreach ($register as $key=>$register){
				$values= array('fe_user' => $user_uid,
                             'module_uid' => $register,
                             'id_dashboard' => $dashboard,
							 'crdate' => time(),
							 'pid' =>$this->conf['pid']
							 );
				$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_icsdashboard_register', $values);
			}
			$conf = array(
					'parameter' => $GLOBALS['TSFE']->id,
					'addQueryString' => 1,
					'addQueryString.' => array('exclude'=>'uid,L,tx_icsdashboard_pi1[reset],tx_icsdashboard_pi1[id_dashboard]'),
					'returnLast' => 'url',
				);
				header('Location: '.t3lib_div::locationHeaderUrl($this->cObj->typoLink('', $conf)));
				exit();
		}
		return $modules_list;
	}
	
	/**
	 * Retourne le nombre de module affiche sur un bureau pour un utilisateur
	 *
	 * @param	int			utilisateur uid
	 * @param	int			identifiant du dashboard
	 * @return	int		nombre de modules affiches
	 */
	function countUserModules($user_uid,$dashboard){
		$modules = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','tx_icsdashboard_register', 'fe_user='.$user_uid.' AND id_dashboard=\''.$dashboard.'\''.$this->cObj->enableFields('tx_icsdashboard_register'),'','position');
		$modules_list='';
		while( $module = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($modules) ) {
			$modules_list.=($modules_list=='')?$module['uid']:','.$module['uid'];
		}
		if ($modules_list==''){
			$modules_list=$this->conf['modules'];
		}	
		return count(explode(',',$modules_list));
	}
	
	
	/**
	 * Retourne le html de l'enregistrement de tt_content passe en parametre
	 *
	 * @param	array			content record
	 * @return	string			HTML content
	 */
	function renderContentObject($pi_aContent){
		$lCObj = t3lib_div::makeInstance("tslib_cObj");
		$lCObj->start($pi_aContent,'tt_content');
		
		return $lCObj->cObjGetSingle('< tt_content', '', '');
	}
	
	
	function init()
	{
		$this->pi_initPIflexForm();
		$piFlexForm = $this->cObj->data['pi_flexform'];
		// Traverse the entire array based on the language...
		// and assign each configuration option to $this->conf array...
		if (is_array($piFlexForm)){
			foreach ( $piFlexForm['data'] as $sheet => $data )
			foreach ( $data as $lang => $value )
			foreach ( $value as $key => $val ){
				if($this->pi_getFFvalue($piFlexForm, $key, $sheet)!=''){
					$this->conf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
				}
			}
		}
		if(empty($this->conf))
			$this->conf = array();
		
		if(isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]) && 
		!empty($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]))
			$this->conf = array_merge(unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]),$this->conf);
		
		$id_dashboard=($this->conf['tsPath'])?$this->conf['tsPath']:$this->cObj->data['uid'];
		$iddashboard=str_replace('.','',$id_dashboard);
		
		$this->template = $this->getTemplateFile('');
		$this->conf['ddsort']=((isset($this->conf['ddsort']))&&($this->conf['ddsort']==0))?0:1;
		$this->conf['edit']=((isset($this->conf['edit']))&&($this->conf['edit']==0))?0:1;
		$this->conf['add']=((isset($this->conf['add']))&&($this->conf['add']==0))?0:1;
		$this->conf['reset']=((isset($this->conf['reset']))&&($this->conf['reset']==0))?0:1;
		//((isset($this->conf['nbMaxModules']))&&(($this->conf['nbMaxModules'])<=($this->countUserModules($GLOBALS['TSFE']->fe_user->user['uid'],$id_dashboard))))
		$this->conf['suppr']=((isset($this->conf['suppr']))&&($this->conf['suppr']==0))?0:1;
		
		$this->conf['pid']=((isset($this->conf['pid']))&&($this->conf['pid']>0))?$this->conf['pid']:0;
		
		$this->incCssFile(t3lib_extMgm::siteRelPath($this->extKey) . 'res/css/default.css');
		// $this->incJsFile(t3lib_extMgm::siteRelPath($this->extKey) . 'res/js/jquery-ui-1.8.5.custom.min.js'); // 1.8.9 fourni par SOLR
		
		$link_conf_add = array(
			'parameter' =>((isset($this->conf['edit_page']))&&($this->conf['edit_page']>0))?$this->conf['edit_page']:$GLOBALS['TSFE']->id,
			'returnLast' => 'url',
			'additionalParams' => '&tx_icsdashboard_pi2[new]=1&tx_icsdashboard_pi2[id_dashboard]='.$id_dashboard.'&tx_icsdashboard_pi2[backUid]='.$GLOBALS['TSFE']->id
		);
		$link_conf_reset = array(
			'parameter' =>$GLOBALS['TSFE']->id,
			'returnLast' => 'url',
			'additionalParams' => '&tx_icsdashboard_pi1[reset]=1&tx_icsdashboard_pi1[id_dashboard]='.$id_dashboard
		);
		$link_conf_edit = array(
			'parameter' =>((isset($this->conf['edit_page']))&&($this->conf['edit_page']>0))?$this->conf['edit_page']:$GLOBALS['TSFE']->id,
			'returnLast' => 'url',
		);
		//var_dump($link_conf_edit);
		$markerArray=array();
		$markerArray['###ADD_CLASS###']='ics_dashboard_add_tool';
		$markerArray['###ADD_LINK###']=$this->cObj->typoLink('', $link_conf_add);
		$add_tool_content= ($this->conf['add'])?$this->viewTemplate('###TEMPLATE_ADD_TOOL###',$markerArray):'';
		
		$markerArray=array();
		$markerArray['###RESET_CLASS###']='ics_dashboard_reset_tool';
		$markerArray['###RESET_LINK###']=$this->cObj->typoLink('', $link_conf_reset);
		$reset_tool_content= ($this->conf['reset'])?$this->viewTemplate('###TEMPLATE_RESET_TOOL###',$markerArray):'';
		
		$markerArray=array();
		$markerArray['###EDIT_CLASS###']='ics_dashboard_edit_tool';
		$markerArray['###EDIT_LINK###']=$this->cObj->typoLink('', $link_conf_edit);
		$edit_tool_content= $this->viewTemplate('###TEMPLATE_EDIT_TOOL###',$markerArray);
		
		$markerArray=array();
		$markerArray['###SUPPR_CLASS###']='ics_dashboard_suppr_tool';
		$suppr_tool_content= $this->viewTemplate('###TEMPLATE_SUPPR_TOOL###',$markerArray);
		
		$markerArray=array();
		$markerArray['###EDIT_TOOL###']=($this->conf['edit'])?$edit_tool_content:'';
		$markerArray['###SUPPR_TOOL###']=($this->conf['suppr'])?$suppr_tool_content:'';
		$tools_content= $this->viewTemplate('###TEMPLATE_TOOLS###',$markerArray);
		$add_allow_js=((isset($this->conf['nbMaxModules']))&&(($this->conf['nbMaxModules'])))?
			'if (jQuery("#ics_dashboard_dashboard_'.$iddashboard.'").children(".ics_dashboard_module").size()<'.$this->conf['nbMaxModules'].'){':'';
		$add_js=($add_tool_content=='')?'':'
			function showAddTool'.$iddashboard.'(){
				var cook = jQuery.cookie(\'ics_desktop_edit'.$iddashboard.'\');
				if(cook){
					'.(($add_allow_js!='')?$add_allow_js:'').'
					jQuery("#ics_dashboard_dashboard_'.$iddashboard.'").each(function () {
						jQuery(this).append(\''.$add_tool_content.'\');
						jQuery(this).children(\'ics_dashboard_add_tool\').attr("onclick","addModule(\''.$iddashboard.'\');return false;");
					});
					'.(($add_allow_js!='')?'}':'').'
				}
			}
		';
		$ddsort=($this->conf['ddsort']>0)?'true':'false';
		$showTools=(($this->conf['edit']>0)||($this->conf['minus']>0)||($this->conf['suppr']>0))?'true':'false';
		$showTheTools=(($this->conf['edit']>0)||($this->conf['minus']>0)||($this->conf['suppr']>0))?';showTheTools'.$iddashboard.'();showAddTool'.$iddashboard.'()':'';
		$showTools_js='
		// jQuery.noConflict();
		function showTheTools'.$iddashboard.'(){
			var cook = jQuery.cookie(\'ics_desktop_edit'.$iddashboard.'\');
			
			if(cook){
				jQuery("#ics_dashboard_dashboard_'.$iddashboard.'").append(\''.$reset_tool_content.'\');
				jQuery(".ics_dashboard_edit_tools_'.$iddashboard.'").append(\''.$tools_content.'\');
				jQuery(".ics_dashboard_edit_tools_'.$iddashboard.' .ics_dashboard_edit_tool").each(function () {
					var moduleuid=jQuery(this).parents(".ics_dashboard_module").attr("id").substring(jQuery(this).parents(".ics_dashboard_module").attr("id").indexOf("ics'.$iddashboard.'_",0)+("'.$iddashboard.'".length+4)	);
					var moduleuid=jQuery(this).parents(".ics_dashboard_module").attr("id").substring(jQuery(this).parents(".ics_dashboard_module").attr("id").indexOf("ics'.$iddashboard.'_",0)+("'.$iddashboard.'".length+4)	);
					if(jQuery(this).attr("href").indexOf("?")>0){
						jQuery(this).attr("href",jQuery(this).attr("href")+"&tx_icsdashboard_pi2[uid]="+moduleuid+"&tx_icsdashboard_pi2[backUid]='.$GLOBALS['TSFE']->id.'");
					}
					else{
						jQuery(this).attr("href",jQuery(this).attr("href")+"?tx_icsdashboard_pi2[uid]="+moduleuid+"&tx_icsdashboard_pi2[backUid]='.$GLOBALS['TSFE']->id.'");
					}
				});
		
				//jQuery(".ics_dashboard_edit_tools_'.$iddashboard.' .ics_dashboard_suppr_tool").each(function () {
				
				jQuery(".ics_dashboard_edit_tools_'.$iddashboard.' .ics_dashboard_suppr_tool").click(function () {
						var moduleuid=jQuery(this).parents(".ics_dashboard_module").attr("id").substring(jQuery(this).parents(".ics_dashboard_module").attr("id").indexOf("ics'.$iddashboard.'_",0)+("'.$iddashboard.'".length+4)	);
						supprModule(moduleuid,\''.$iddashboard.'\',\''.$this->pi_getLL('delete_message').'\');
				});
				
				//jQuery(this).attr("onclick","supprModule(\'"+moduleuid+"\',\''.$iddashboard.'\',\''.$this->pi_getLL('delete_message').'\');return false;");
				//});
			}
		}
		'.$add_js.'
		//jQuery(document).ready(function() {
		//	init_edit_tools(\''.$iddashboard.'\','.$ddsort.','.$showTools.')'.$showTheTools.';
		//});
		//jQuery(document).ready(function() {
			//init_edit_tools(\''.$iddashboard.'\','.$ddsort.','.$showTools.');
		//});
		';
		
		
		$this->incJsFile($showTools_js,true);
		
		$this->incJsFile(t3lib_extMgm::siteRelPath($this->extKey) . 'res/js/default.js');
	}
	/**
	 * Get subpart of template and replace values with markers array
	 *
	 * @param	string		Subtemplate name
	 * @param	array		markers/values
	 * @return	subtemplate HTML
	 */
	function viewTemplate($nametemplate, $markers){
		$cObj = t3lib_div::makeInstance('tslib_cObj');
		$cObj->setParent($this->cObj->data,$this->cObj->currentRecord);
		$templatename = basename($this->template);
        $content = $cObj->getSubpart(file_get_contents($this->template), $nametemplate);
		$content = $cObj->substituteMarkerArray($content, $markers);
		return $content;
	}
	
	
	 /**
	 * Retrieve template file name.
	 *
	 * @param $mode string The rendering mode.
	 * @return string The template filename and path.
	 */
	function getTemplateFile($mode)
	{
		$template = '';
		$templates = $this->getTemplateFiles($mode);
		if (!empty($templates))
			$template = $templates[0];
		return $template;
	}
	
    /**
	 * Retrieve available template file names.
	 *
	 * @param $mode string The rendering mode.
	 * @return array All available template filename <ith full path.
	 */
	function getTemplateFiles($mode)
	{
		$templates = array();
		if (isset($this->conf['templatePath']) && is_dir(t3lib_div::getFileAbsFileName($this->conf['templatePath'])))
		{
			if (isset($this->conf['defaultTemplate']) && is_file(t3lib_div::getFileAbsFileName($this->conf['templatePath']) . $this->conf['defaultTemplate']))
			{
				$templates[] = t3lib_div::getFileAbsFileName($this->conf['templatePath']) . $this->conf['defaultTemplate'];
			}
			if (isset($this->conf['template']) && is_file(t3lib_div::getFileAbsFileName($this->conf['templatePath']) . $this->conf['template']))
			{
				$templates[] = t3lib_div::getFileAbsFileName($this->conf['templatePath']) . $this->conf['template'];
			}
		}
		if (isset($this->conf[$mode]['templatePath']) && is_dir(t3lib_div::getFileAbsFileName($this->conf[$mode]['templatePath'])))
		{
			if (isset($this->conf[$mode]['defaultTemplate']) && is_file(t3lib_div::getFileAbsFileName($this->conf[$mode]['templatePath']) . $this->conf[$mode]['defaultTemplate']))
			{
				$templates[] = t3lib_div::getFileAbsFileName($this->conf[$mode]['templatePath']) . $this->conf[$mode]['defaultTemplate'];
			}
			if (isset($this->conf[$mode]['template']) && is_file(t3lib_div::getFileAbsFileName($this->conf[$mode]['templatePath']) . $this->conf[$mode]['template']))
			{
				$templates[] = t3lib_div::getFileAbsFileName($this->conf[$mode]['templatePath']) . $this->conf[$mode]['template'];
			}
		}
		array_reverse($templates);
		// TODO: plugin configuration take precedence.
		return $templates;
	}
	
	
	/**
	* Function to insert Javascript at Ext. Runtime
	*
	* @param string $script Input the Script Name to insert JS
	* @return
	*/
	
	function incJsFile($script,$jsCode = false) {
		if(!$jsCode)
			$js = '<script src="'.$script.'" type="text/javascript"><!-- //--></script>';
		else
		{
			$js .= '<script type="text/javascript">
				'.$script.'
			</script>';
		}
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] .= $js;
	}
	
	/**
	* Function to insert CSS
	*
	* @param string $cssFile Input the Css Name to insert Css
	* @return
	*/
	
	function incCssFile($cssFile) {
		$css = '<link type="text/css" href="' . $cssFile . '" rel="stylesheet" />';
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] .= $css;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_dashboard/pi1/class.tx_icsdashboard_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_dashboard/pi1/class.tx_icsdashboard_pi1.php']);
}

?>
