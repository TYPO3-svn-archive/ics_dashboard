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

require_once(PATH_t3lib . 'class.t3lib_userauth.php');
require_once(PATH_t3lib . 'class.t3lib_page.php');
require_once(PATH_t3lib . 'class.t3lib_tstemplate.php');
require_once(PATH_tslib . 'class.tslib_fe.php');
require_once(PATH_tslib . 'class.tslib_gifbuilder.php');
require_once(PATH_tslib . 'class.tslib_feuserauth.php');
require_once(PATH_tslib . 'class.tslib_content.php');
require_once(PATH_tslib . 'class.tslib_pibase.php');
//require_once(t3lib_extMgm::extPath('pagepath', 'class.tx_pagepath_api.php'));
require_once(t3lib_extMgm::extPath('lang', 'lang.php'));
require_once(t3lib_extMgm::extPath('ics_dashboard', 'pi1/class.tx_icsdashboard_pi1.php'));

// error_log('ajaxresponder: start');

/**
 * Renderer for ajax calls from plugin ics_dashboard
 *
 * @author	Mickaël Paillard <mickael@in-cite.net>
 */
class tx_icsdashboard_ajaxresponder extends tslib_pibase
{
	var $prefixId;		// Same as class name
	var $scriptRelPath;	// Path to this script relative to the extension dir.
	var $extKey        = 'ics_dashboard';	// The extension key.
	var $icsdashboardPi1;
	/****************************************************/
	
	function main($content, $conf)
	{
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		$this->pi_initPIflexForm();
		$this->init();
		$content=t3lib_div::view_array($this->piVars);
		if(isset($_GET['dashboard'])){
			$id_dashboard=$_GET['dashboard'];
			$content=t3lib_div::view_array($_GET);
			foreach ($_GET['ics'.$id_dashboard] as $position=>$item){
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_icsdashboard_register','uid='.$item.' AND fe_user='.$GLOBALS['TSFE']->fe_user->user['uid'],array('position'=>$position));
			}
		}
		if((isset($this->piVars['suppr']))&&($this->piVars['suppr']==1)){
			if ((isset($this->piVars['uid']))&&($this->piVars['uid']>0)){
				$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_icsdashboard_register','uid='.$this->piVars['uid']);
			}
		}
		
		return $content;
	}
	function init()
	{
		$prefix1 = t3lib_div::_GP('tx_icsdashboard_pi1');
		if(isset($prefix1) && !empty($prefix1))
		{
			$this->piVars = t3lib_div::_GP('tx_icsdashboard_pi1');
			$this->scriptRelPath = 'pi1/class.tx_icsdashboard_pi1.php';
			$this->prefixId = 'tx_icsdashboard_pi1';
		}
		
		if((!isset($_GET['L']) || empty($_GET['L'])) || $_GET['L'] == 0)
			$this->LLkey = 'fr';
		else
		{
			if($_GET['L'] == 1)
				$this->LLkey = 'default';
		}
		
		if(isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]) && 
		!empty($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]))
			$this->conf = array_merge(unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]),$this->conf);
		
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		
		$this->icsdashboardPi1 = t3lib_div::makeInstance('tx_icsdashboard_pi1');

	}
	
	function includeTCA()
	{
		tslib_fe::includeTCA(0);
	}
	
}
global $TYPO3_CONF_VARS;
$TSFE = new tslib_fe($TYPO3_CONF_VARS, 330, 0);
$TSFE->config = array();
$TSFE->connectToDB();
$TSFE->initFEuser();
$TSFE->initTemplate();
$TSFE->convPOSTCharset();
$TSFE->settingLanguage();
$TSFE->settingLocale();
$TSFE->sys_page = new t3lib_pageSelect();
$TSFE->sys_page->init(false);

$fob = new tx_icsdashboard_ajaxresponder();
$fob->includeTCA();
$output = $fob->main('','');
header('Content-type: text/html; charset=utf-8'); 
header('Content-Length: ' . strlen($output));

// error_log('ajaxresponder: dump output');
echo $output;
