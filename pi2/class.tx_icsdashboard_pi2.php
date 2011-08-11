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
class tx_icsdashboard_pi2 extends tslib_pibase {
	var $prefixId      = 'tx_icsdashboard_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_icsdashboard_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'ics_dashboard';	// The extension key.
	var $confError	   = false;
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->init();
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!

		if( !$this->confError ){
			if( isset($this->piVars[ 'new' ]) ){
		 		$content .= '<h2>'.$this->pi_getLL('new_title').'</h2>';
				$unique_modules =$this->getUserUniqueModules($GLOBALS['TSFE']->fe_user->user['uid'],$this->piVars[ 'id_dashboard' ]);
				$dispomodules = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_icsdashboard_modules','sys_language_uid=0 AND (uid IN ('.$this->conf['dispo_modules'].'))'.(($unique_modules!='')?'AND(uid NOT IN('.$unique_modules.'))':'').$this->cObj->enableFields('tx_icsdashboard_modules'));
				
				$conf = array(
					'parameter' => $GLOBALS['TSFE']->id,
					'addQueryString' => 1,
					'addQueryString.' => array('exclude'=>'uid,L,tx_icsdashboard_pi2[new]'),
				);
				$list_modules='';
				while( $dispomodule = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dispomodules) ) {
					$conf['additionalParams']='&tx_icsdashboard_pi2[add]='.$dispomodule['uid'];
					$dispomodule = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_icsdashboard_modules', $dispomodule, $GLOBALS['TSFE']->sys_language_uid);
					$list_modules.='<li>'.$this->cObj->typoLink($dispomodule['name'], $conf).'<br/>
										<span class="description">'.$this->pi_RTEcssText($dispomodule['description']).'</span>
									</li>';
				}
				$content.=($list_modules!='')?'<ul class="'.$this->extKey.'_modules_list">'.$list_modules.'</ul>':'';
				
			}
			elseif( isset($this->piVars[ 'uid' ]) ){
				$content .= '<h2>'.$this->pi_getLL('modify_title').'</h2>';
				//récupérer l'register
				$errormsg='';
				$registers = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title,module_uid',
					'tx_icsdashboard_register',
					'fe_user='.$GLOBALS['TSFE']->fe_user->user['uid'].
					' AND uid='.$this->piVars[ 'uid' ]
					.$this->cObj->enableFields('tx_icsdashboard_register')
				);
				while($register=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($registers)){
					$modules = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,name,content_config,pid,sys_language_uid',
						'tx_icsdashboard_modules',
						'uid='.$register[ 'module_uid' ].' AND sys_language_uid=0'
						.$this->cObj->enableFields('tx_icsdashboard_modules')
					);
					while($module=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($modules)){
						$module = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_icsdashboard_modules', $module, $GLOBALS['TSFE']->sys_language_uid);
						$content.='<p class="error">'.$errormsg.'</p>';
						$content.='
						<form name="'.$this->extKey.'_register'.$register['uid'].'" action="'.$this->pi_linkTP_keepPIvars_url().'" method="post">
							<fieldset>
								<div class="line">
									<label for="'.$this->extKey.'_register'.$register['uid'].'_title">'.$this->pi_getLL('title').'</label>
									<input type="text" name="'.$this->extKey.'_register'.$register['uid'].'[title]" id="'.$this->extKey.'_register'.$register['uid'].'_title" value="'.(($register['title']!='')?htmlspecialchars(stripslashes($register['title'])):htmlspecialchars(stripslashes($module['name']))).'"/>
								</div>
							</fieldset>
							<fieldset class="submits">
								<input type="submit" class="bouton" name="'.$this->extKey.'_register'.$register['uid'].'rename" value="'.$this->pi_getLL('rename').'"/>
							</fieldset>
						';
						if ($module['content_config']!=''){
							$contents=explode(',',$module['content_config']);
							foreach ($contents as $key=>$content_uid){
								$lCObj = t3lib_div::makeInstance("tslib_cObj");
								$lConf = array('tables' => 'tt_content', 'source'=>'tt_content_'.$content_uid);
								$content .= $lCObj->RECORDS($lConf);
							}
						}
						$content.='</form>';
					}
				}
				if (isset($_POST[$this->extKey.'_register'.$this->piVars[ 'uid' ]]['title'])){
					$registers = $GLOBALS['TYPO3_DB']->exec_SELECTquery('module_uid',
					'tx_icsdashboard_register',
					'fe_user='.$GLOBALS['TSFE']->fe_user->user['uid'].
					' AND uid='.$this->piVars[ 'uid' ]
					.$this->cObj->enableFields('tx_icsdashboard_register')
					);
					while($register=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($registers)){
						$modules = $GLOBALS['TYPO3_DB']->exec_SELECTquery('content_config,pid,sys_language_uid',
							'tx_icsdashboard_modules',
							'uid='.$register[ 'module_uid' ].' AND sys_language_uid=0'
							.$this->cObj->enableFields('tx_icsdashboard_modules')
						);
						while($module=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($modules)){
							$module = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_icsdashboard_modules', $module, $GLOBALS['TSFE']->sys_language_uid);
							if ($errormsg==''){
							$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_icsdashboard_register','uid='.$this->piVars[ 'uid' ], $_POST[$this->extKey.'_register'.$this->piVars[ 'uid' ]]);					
							}
							if ($module['content_config']==''){
							$conf = array(
								'parameter' => $this->piVars[ 'backUid' ],
								'addQueryString' => 1,
								'addQueryString.' => array('exclude'=>'uid,L,&tx_icsdashboard_pi2[id_dashboard],tx_icsdashboard_pi2[backUid],tx_icsdashboard_pi2[add]'),
								'returnLast' => 'url',
							);
							//$content.=t3lib_div::makeRedirectUrl($this->cObj->typoLink('', $conf));
							header('Location: '.t3lib_div::makeRedirectUrl($this->cObj->typoLink('', $conf)));
							exit();
							
							}
						}
					}
				}
			}
			elseif( isset($this->piVars[ 'add' ]) ){
				$id_register=$this->ModuleUserRegister($this->piVars[ 'add' ],$GLOBALS['TSFE']->fe_user->user['uid'],$this->piVars[ 'id_dashboard' ]);
				if (($this->conf['editafteradd'])&&($id_register)){
					$conf = array(
						'parameter' => $GLOBALS['TSFE']->id,
						'addQueryString' => 1,
						'addQueryString.' => array('exclude'=>'uid,L,tx_icsdashboard_pi2[add]'),
						'additionalParams' => '&tx_icsdashboard_pi2[uid]='.$id_register,
						'returnLast' => 'url',
					);
				}
				else{
					$conf = array(
						'parameter' => $this->piVars[ 'backUid' ],
						'addQueryString' => 1,
						'addQueryString.' => array('exclude'=>'uid,L,&tx_icsdashboard_pi2[id_dashboard],tx_icsdashboard_pi2[backUid],tx_icsdashboard_pi2[add]'),
						'returnLast' => 'url',
					);
				}
				header('Location: '.t3lib_div::makeRedirectUrl($this->cObj->typoLink('', $conf)));
				exit();
			}
		}
		//$content.=t3lib_div::view_array($_POST);
		
		//récupérer les contents de cette register
		return $this->pi_wrapInBaseClass($content);
	}
	
	function init()
	{
		$this->pi_initPIflexForm();
		$piFlexForm = $this->cObj->data['pi_flexform'];
		// Traverse the entire array based on the language...
		// and assign each configuration option to $this->conf array...
		
		//test
		//$this->piVars[ 'id_dashboard' ] = 'lib.dashboard';
		//$this->piVars[ 'id_dashboard' ] = 363;
		
		$fields_Pi1_toGet = array('nbMaxModules','pid');
		if (is_array($piFlexForm)){
			foreach ( $piFlexForm['data'] as $sheet => $data )
				foreach ( $data as $lang => $value )
					foreach ( $value as $key => $val ){
						if($this->pi_getFFvalue($piFlexForm, $key, $sheet)!=''){
							$this->conf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
						}
					}
		}
		if( isset($this->piVars[ 'id_dashboard' ]) ){
			if( is_numeric($this->piVars[ 'id_dashboard' ]) ){
				$where_clause = 'uid = '. $this->piVars[ 'id_dashboard' ] . ' AND list_type="'.$this->extKey . '_pi1" ' . $this->cObj->enableFields('tt_content');
				$ret = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('pi_flexform', 'tt_content', $where_clause, '', '', 1);
				if(count($ret)>0){
					$pi1FlexForm = t3lib_div::xml2array($ret[0]['pi_flexform']);
					if (is_array($pi1FlexForm)){
						foreach ( $pi1FlexForm['data'] as $sheet => $data )
							foreach ( $data as $lang => $value )
								foreach ( $value as $key => $val ){
									if($this->pi_getFFvalue($pi1FlexForm, $key, $sheet)!='' && in_array($key,$fields_Pi1_toGet) ){
										$this->conf[$key] = $this->pi_getFFvalue($pi1FlexForm, $key, $sheet);
									}
								}
					}
					else
						$this->confError = true;
				}
			}
			elseif(is_string($this->piVars[ 'id_dashboard' ])){
				$key = trim($this->piVars[ 'id_dashboard' ]);
				$cF = t3lib_div::makeInstance('t3lib_TSparser');
				
				$conf = $cF->getVal($key,$GLOBALS['TSFE']->tmpl->setup);
				if( is_array($conf[1]) )
					foreach ($conf[1] as $key => $val ){
						if( in_array($key,$fields_Pi1_toGet) ){
							$this->conf[$key] = $val;
						}
					}
			}
			else
				$this->confError = true;
		}
		elseif (!( isset($this->piVars[ 'uid' ]) ))
			$this->confError = true;
		
		$this->conf['editafteradd']=((isset($this->conf['editafteradd']))&&($this->conf['editafteradd']==0))?0:1;
		if(isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]) && 
		!empty($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]))
			$this->conf = array_merge(unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]),$this->conf);
		
		$this->template = $this->getTemplateFile('');
		$this->incCssFile(t3lib_extMgm::siteRelPath($this->extKey) . 'res/css/default.css');
		
		/*$id_dashboard=($this->conf['iddashboard'])?$this->conf['iddashboard']:$this->cObj->data['uid'];
		
		$link_conf = array(
			'parameter' =>((isset($this->conf['edit_page']))&&($this->conf['edit_page']>0))?$this->conf['edit_page']:$GLOBALS['TSFE']->id,
			'returnLast' => 'url',
		);
		
		###LABEL_TITLE###
		###INPUT_TITLE###
		###SUBMIT###
		###CONFIG_TOOL###*/
		
		$this->incJsFile(t3lib_extMgm::siteRelPath($this->extKey) . 'res/js/default.js');
	}
	
	
	/**
	 * Recupere les modules a insertion unique pour un bureau d 1 utilisateur 
	 *
	 * @param	int			utilisateur uid
	 * @param	int			identifiant du dashboard
	 * @return	string		liste uid de modules a insertion unique separes par des virgules
	 */
	function getUserUniqueModules($user_uid,$dashboard){
		$modules = $GLOBALS['TYPO3_DB']->exec_SELECTquery('module_uid,uid','tx_icsdashboard_register', 'fe_user='.$user_uid.' AND id_dashboard=\''.$dashboard.'\''.$this->cObj->enableFields('tx_icsdashboard_register'),'','position');
		$modules_list='';
		while( $module = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($modules)) {
			$gemodules = $GLOBALS['TYPO3_DB']->exec_SELECTquery('multi','tx_icsdashboard_modules','uid='.$module['module_uid'].$this->cObj->enableFields('tx_icsdashboard_modules'));
			while( $gemodule = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($gemodules) ) {
				if ($gemodule['multi']==0)
					$modules_list.=($modules_list=='')?$module['module_uid']:','.$module['module_uid'];
			}
		}
		return $modules_list;
	}
	
	/**
	* Inscris l'utilisateur a un module pour un bureau
	*
	* @param	int			module uid	
	* @param	int			utilisateur uid
	* @param	int			identifiant du dashboard
	*/
	function ModuleUserRegister($module_uid,$user_uid,$dashboard){
		$unique_module =array_flip(explode(',',$this->getUserUniqueModules($user_uid,$dashboard)));
		$dispo_module =array_flip(explode(',',$this->conf['dispo_modules']));
		
		$modules = $GLOBALS['TYPO3_DB']->exec_SELECTquery('position','tx_icsdashboard_register', 'fe_user='.$user_uid.' AND id_dashboard=\''.$dashboard.'\''.$this->cObj->enableFields('tx_icsdashboard_register'),'','position DESC');
		$position = 0;
		if( $module = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($modules)) {
			$position= $module['position']+1;
		}
		// verifie si on peut ajouter ce module pour ce bureau
		if((!isset($unique_module[$module_uid]))&&(isset($dispo_module[$module_uid]))&&(count($module)<$this->conf['nbMaxModules'])){
			$values= array('fe_user' => $user_uid,
						'module_uid' => $module_uid,
						'position' => $position,
                        'id_dashboard' => $dashboard,
						'pid' =>$this->conf['pid']
				 );
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_icsdashboard_register', $values);
			return $GLOBALS['TYPO3_DB']->sql_insert_id();
		}
		else return false;
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
	* @param string $cssFile Input the Css Name to insert JS
	* @return
	*/	
	function incCssFile($cssFile) {
		$css = '<link type="text/css" href="' . $cssFile . '" rel="stylesheet" />';
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] .= $css;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_dashboard/pi2/class.tx_icsdashboard_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_dashboard/pi2/class.tx_icsdashboard_pi2.php']);
}

?>