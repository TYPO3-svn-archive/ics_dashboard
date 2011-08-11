<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_icsdashboard_modules'] = array (
	'ctrl' => $TCA['tx_icsdashboard_modules']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid,l10n_parent,l10n_diffsource,hidden,fe_group,name,description,multi,content,content_config,content_add'
	),
	'feInterface' => $TCA['tx_icsdashboard_modules']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (        
            'exclude' => 1,
            'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
            'config' => array (
                'type'                => 'select',
                'foreign_table'       => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => array(
                    array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
                    array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
                )
            )
        ),
        'l10n_parent' => array (        
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude'     => 1,
            'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
            'config'      => array (
                'type'  => 'select',
                'items' => array (
                    array('', 0),
                ),
                'foreign_table'       => 'tx_icsdashboard_modules',
                'foreign_table_where' => 'AND tx_icsdashboard_modules.pid=###CURRENT_PID### AND tx_icsdashboard_modules.sys_language_uid IN (-1,0)',
            )
        ),
        'l10n_diffsource' => array (        
            'config' => array (
                'type' => 'passthrough'
            )
        ),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		'name' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_dashboard/locallang_db.xml:tx_icsdashboard_modules.name',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
			)
		),
		'content' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_dashboard/locallang_db.xml:tx_icsdashboard_modules.content',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tt_content',	
				'size' => 4,	
				'minitems' => 0,
				'maxitems' => 10,
			)
		),
		'content_config' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_dashboard/locallang_db.xml:tx_icsdashboard_modules.content_config',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tt_content',	
				'size' => 4,	
				'minitems' => 0,
				'maxitems' => 10,
			)
		),
		'content_add' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_dashboard/locallang_db.xml:tx_icsdashboard_modules.content_add',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tt_content',	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 10,
			)
		),
		'description' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_dashboard/locallang_db.xml:tx_icsdashboard_modules.description',		
			'config' => array (
				'type' => 'text',	
				'size' => '30',	
			)
		),
		'multi' => array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:ics_dashboard/locallang_db.xml:tx_icsdashboard_modules.multi',		
			'config'  => array (
				'type'    => 'check',
				'default' => '1'
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;;;1-1-1,l10n_parent,l10n_diffsource,hidden;;1;;1-1-1,name,description, multi,content,content_config,content_add')
	),
	'palettes' => array (
		'1' => array('showitem' => 'fe_group')
	)
);



$TCA['tx_icsdashboard_register'] = array (
	'ctrl' => $TCA['tx_icsdashboard_register']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,fe_user,module_uid,title,position,id_dashboard'
	),
	'feInterface' => $TCA['tx_icsdashboard_register']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'fe_user' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_dashboard/locallang_db.xml:tx_icsdashboard_register.fe_user',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'fe_users',	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'module_uid' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_dashboard/locallang_db.xml:tx_icsdashboard_register.module_uid',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tx_icsdashboard_modules',	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'title' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_dashboard/locallang_db.xml:tx_icsdashboard_register.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',
			)
		),
		'position' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_dashboard/locallang_db.xml:tx_icsdashboard_register.position',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'range' => array ('lower'=>0,'upper'=>1000),	
				'eval' => 'int',
			)
		),
		'id_dashboard' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ics_dashboard/locallang_db.xml:tx_icsdashboard_register.id_dashboard',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, fe_user, module_uid, title, position,id_dashboard')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);
?>