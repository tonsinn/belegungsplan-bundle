<?php
use Contao\System;
use Contao\Backend;
use Contao\Input;
use Contao\StringUtil;
use Contao\Image;
use Contao\Date;
use Contao\Config;
use Contao\Versions;
use Contao\DataContainer;
use Contao\BackendUser;
/**
 * Contao Open Source CMS
 *
 * Copyright (c) Jan Karai
 *
 * @license LGPL-3.0-or-later
 *
 * @author Jan Karai <https://www.sachsen-it.de>
 */

/**
 * Load tl_content language file
 */
System::loadLanguageFile('tl_content');
 
/**
 * Table tl_belegungsplan_feiertage
 */
$GLOBALS['TL_DCA']['tl_belegungsplan_feiertage'] = array
(
	// Config
	'config' => array
	(
		'dataContainer' => \Contao\DC_Table::class,
		'switchToEdit'                => true,
		'enableVersioning'            => true,
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary'
			)
		)
	),
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'				=> 1,
			'fields'			=> array('startDate DESC'),
			'flag'				=> 9,
			'panelLayout'		=> 'search,limit'
		),
		'label' => array
		(
			'fields'			=> array('title'),
			'format'			=> '%s',
			'label_callback'	=> [\Mailwurm\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanFeiertageListener::class, 'listCalender']
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'			=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'			=> 'act=select',
				'class'			=> 'header_edit_all',
				'attributes'	=> 'onclick="Backend.getScrollOffset()" accesskey="e"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'	=> &$GLOBALS['TL_LANG']['tl_belegungsplan_feiertage']['edit'],
				'href'	=> 'act=edit',
				'icon'	=> 'edit.svg'
			),
			'delete' => array
			(
				'label'			=> &$GLOBALS['TL_LANG']['tl_belegungsplan_feiertage']['delete'],
				'href'			=> 'act=delete',
				'icon'			=> 'delete.svg',
			)
		)
	),
	// Palettes
	'palettes' => array
	(
		'__selector__'			=> array('ausgabe'),
		'default'				=> '{title_legend},title,showTitleText,author;{date_legend},startDate;{color_legend},ausgabe'
	),
	// Subpalettes
	'subpalettes' => array
	(
		'ausgabe'				=> 'hintergrund,opacity,reset,textcolor,textopacity,textreset'
	),
	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'				=> "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array
		(
			'sql'				=> "int(10) unsigned NOT NULL default '0'"
		),
		'title' => array
		(
			'label'				=> &$GLOBALS['TL_LANG']['tl_belegungsplan_feiertage']['title'],
			'exclude'			=> true,
			'search'			=> true,
			'filter'			=> true,
			'inputType'			=> 'text',
			'eval'				=> array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'				=> "varchar(255) NOT NULL default ''"
		),
		'showTitleText' => array
		(
			'label'				=> &$GLOBALS['TL_LANG']['tl_belegungsplan_feiertage']['showTitleText'],
			'exclude'			=> true,
			'inputType'			=> 'checkbox',
			'eval'				=> array('tl_class'=>'w50 m12'),
			'sql'				=> "char(1) COLLATE ascii_bin NOT NULL default '1'"
		),
		'author' => array
		(
			'label'				=> &$GLOBALS['TL_LANG']['tl_belegungsplan_feiertage']['author'],
			'default'			=> BackendUser::getInstance()->id,
			'exclude'			=> true,
			'search'			=> true,
			'filter'			=> true,
			'sorting'			=> true,
			'flag'				=> 11,
			'inputType'			=> 'select',
			'foreignKey'		=> 'tl_user.name',
			'eval'				=> array('doNotCopy'=>true, 'chosen'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50 clr'),
			'sql'				=> "int(10) unsigned NOT NULL default '0'",
			'relation'			=> array('type'=>'belongsTo', 'load'=>'eager')
		),
		'startDate' => array
		(
			'label'				=> &$GLOBALS['TL_LANG']['tl_belegungsplan_feiertage']['startDate'],
			'exclude'			=> true,
			'search'			=> true,
			'filter'			=> true,
			'sorting'			=> true,
			'flag'				=> 8,
			'inputType'			=> 'text',
			'eval'				=> array('rgxp'=>'date', 'mandatory'=>true, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'save_callback'		=> array
			(
				[\Mailwurm\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanFeiertageListener::class, 'getVorhanden']
			),
			'sql'				=> "int(10) unsigned NULL"
		),
		'ausgabe' => array
		(
			'label'				=> &$GLOBALS['TL_LANG']['tl_belegungsplan_feiertage']['ausgabe'],
			'exclude'			=> true,
			'inputType'			=> 'checkbox',
			'default'			=> '1',
			'eval'				=> array('submitOnChange'=>true, 'tl_class'=>'w50 m12 wizard', 'helpwizard'=>true),
			'sql'				=> "char(1) NOT NULL default ''"
		),
		'hintergrund' => array
		(
			'label'				=> &$GLOBALS['TL_LANG']['tl_belegungsplan_feiertage']['hintergrund'],
			'exclude'			=> true,
			'inputType'			=> 'text',
			'default'			=> '91,192,222',
			'explanation'		=> 'feiertage_hintergrund',
			'load_callback'		=> array
			(
				[\Mailwurm\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanFeiertageListener::class, 'setRgbToHexTextcolor']
			),
			'eval'				=> array('maxlength'=>6, 'minlength'=>6, 'mandatory'=>true, 'colorpicker'=>true, 'isHexColor'=>true, 'decodeEntities'=>true, 'tl_class'=>'w33 wizard clr', 'helpwizard'=>true),
			'save_callback'		=> array
			(
				[\Mailwurm\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanFeiertageListener::class, 'setRgbToHexHintergrund']
			),
			'sql'				=> "varchar(20) NOT NULL default ''"
		),
		'opacity' => array
		(
			'label'				=> &$GLOBALS['TL_LANG']['tl_belegungsplan_feiertage']['opacity'],
			'exclude'			=> true,
			'inputType'			=> 'select',
			'default'			=> '1.0',
			'options'			=> array('1.0','0.9','0.8','0.7','0.6','0.5','0.4','0.3','0.2','0.1'),
			'eval'				=> array('tl_class'=>'w25'),
			'sql'				=> "varchar(3) NOT NULL default ''"
		),
		'reset' => array
		(
			'eval'					=> array('submitOnChange'=>true)
		),
		'textcolor' => array
		(
			'label'				=> &$GLOBALS['TL_LANG']['tl_belegungsplan_feiertage']['textcolor'],
			'exclude'			=> true,
			'inputType'			=> 'text',
			'default'			=> '51,51,51',
			'explanation'		=> 'feiertage_textcolor',
			'load_callback'		=> array
			(
				[\Mailwurm\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanFeiertageListener::class, 'setRgbToHexTextcolor']
			),
			'eval'				=> array('maxlength'=>6, 'minlength'=>6, 'mandatory'=>true, 'colorpicker'=>true, 'isHexColor'=>true, 'decodeEntities'=>true, 'tl_class'=>'w33 wizard clr', 'helpwizard'=>true),
			'save_callback'		=> array
			(
				[\Mailwurm\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanFeiertageListener::class, 'setRgbToHexHintergrund']
			),
			'sql'				=> "varchar(20) NOT NULL default ''"
		),
		'textopacity' => array
		(
			'label'				=> &$GLOBALS['TL_LANG']['tl_belegungsplan_feiertage']['opacity'],
			'exclude'			=> true,
			'inputType'			=> 'select',
			'default'			=> '1.0',
			'options'			=> array('1.0','0.9','0.8','0.7','0.6','0.5','0.4','0.3','0.2','0.1'),
			'eval'				=> array('tl_class'=>'w25'),
			'sql'				=> "varchar(3) NOT NULL default ''"
		),
		'textreset' => array
		(
			'eval'					=> array('submitOnChange'=>true),
		)
	)
);
