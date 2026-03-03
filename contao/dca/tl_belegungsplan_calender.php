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
 * Table tl_belegungsplan_calender
 */
$GLOBALS['TL_DCA']['tl_belegungsplan_calender'] = array
(
	// Config
	'config' => array
	(
		'dataContainer' => \Contao\DC_Table::class,
		'ptable'				=> 'tl_belegungsplan_objekte',
		'ctable'				=> array('tl_content'),
		'switchToEdit'			=> true,
		'enableVersioning'		=> true,
		'onsubmit_callback'		=> array([\Mailwurm\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanCalenderListener::class, 'loadUeberschneidung']),
		'ondelete_callback'		=> array([\Mailwurm\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanCalenderListener::class, 'calenderOndeleteCallback']),
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				'pid' => 'index'
			)
		)
	),
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'fields'                  => array('startDate DESC'),
			'headerFields'            => array('name'),
			'panelLayout'             => 'filter;sort,search,limit',
			'child_record_callback'   => [\Mailwurm\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanCalenderListener::class, 'listCalender']
		),
		'label' => array
		(
			'fields'                  => array('gast', 'startDate', 'endDate'),
			'format'                  => '%s'
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_belegungsplan_calender']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.svg'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_belegungsplan_calender']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.svg',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			)
		)
	),
	// Palettes
	'palettes' => array
	(
		'__selector__'				=> array('dauer'),
		'default'					=> '{title_legend},gast,author;{day_legend},dauer',
		'oneday'					=> '{title_legend},gast,author;{day_legend},dauer;{date_legend},startDate',
		'moreday'					=> '{title_legend},gast,author;{day_legend},dauer;{date_legend},startDate,endDate'
	),
	// Subpalettes
	'subpalettes' => array(
	),
	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'pid' => array
		(
			'foreignKey'              => 'tl_belegungsplan_objekte.name',
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => array('type'=>'belongsTo', 'load'=>'eager')
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'gast' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_belegungsplan_calender']['gast'],
			'exclude'                 => true,
			'search'                  => true,
			'filter'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'author' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_belegungsplan_calender']['author'],
			'default' => BackendUser::getInstance() ? BackendUser::getInstance()->id : 0,
			'exclude'                 => true,
			'search'                  => true,
			'filter'                  => true,
			'sorting'                 => true,
			'flag'                    => 11,
			'inputType'               => 'select',
			'foreignKey'              => 'tl_user.name',
			'eval'                    => array('doNotCopy'=>true, 'chosen'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => array('type'=>'belongsTo', 'load'=>'eager')
		),
		'startDate' => array
		(
			'label'			=> &$GLOBALS['TL_LANG']['tl_belegungsplan_calender']['startDate'],
			'exclude'		=> true,
			'search'		=> true,
			'filter'		=> true,
			'sorting'		=> true,
			'flag'			=> 8,
			'inputType'		=> 'text',
			'eval'			=> array('rgxp'=>'date', 'mandatory'=>true, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'save_callback'	=> array
			(
				[\Mailwurm\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanCalenderListener::class, 'setEndDate']
			),
			'sql'			=> "int(10) unsigned NULL"
		),
		'endDate' => array
		(
			'label'			=> &$GLOBALS['TL_LANG']['tl_belegungsplan_calender']['endDate'],
			'exclude'		=> true,
			'search'		=> true,
			'filter'		=> true,
			'sorting'		=> true,
			'flag'			=> 8,
			'inputType'		=> 'text',
			'eval'			=> array('rgxp'=>'date', 'mandatory'=>true, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'save_callback'	=> array
			(
				[\Mailwurm\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanCalenderListener::class, 'loadEndDate']
			),
			'sql'			=> "int(10) unsigned NULL"
		),
		'ueberschneidung' => array
		(
			'label'			=> &$GLOBALS['TL_LANG']['tl_belegungsplan_calender']['ueberschneidung'],
			'exclude'		=> true,
			'inputType'		=> 'text',
			'sql'			=> "text NULL"
		),
		'dauer' => array
		(
			'label'			=> &$GLOBALS['TL_LANG']['tl_belegungsplan_calender']['dauer'],
			'inputType'		=> 'radio',
			'options'		=> array('oneday', 'moreday'),
			'default'		=> 'moreday',
			'reference'		=> &$GLOBALS['TL_LANG']['tl_belegungsplan_calender'],
			'explanation'	=> &$GLOBALS['TL_LANG']['tl_belegungsplan_calender'],
			'eval'			=> array('mandatory'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50', 'style'=>'margin:10px'),
			'sql'			=> "varchar(8) NOT NULL default ''"
		)
	)
);

