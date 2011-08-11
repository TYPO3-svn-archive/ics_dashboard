<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_icsdashboard_pi1.php', '_pi1', 'list_type', 0);
t3lib_extMgm::addPItoST43($_EXTKEY, 'pi2/class.tx_icsdashboard_pi2.php', '_pi2', 'list_type', 0);

$TYPO3_CONF_VARS['FE']['eID_include']['ics_dashboard'] = 'typo3conf/ext/ics_dashboard/lib/class.tx_icsdashboard_ajaxresponder.php';
?>