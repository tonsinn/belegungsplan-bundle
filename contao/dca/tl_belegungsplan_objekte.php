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
 * Table tl_belegungsplan_objekte
 */
$GLOBALS['TL_DCA']['tl_belegungsplan_objekte'] = array(
	'config' => array(
		'dataContainer' => \Contao\DC_Table::class,
		'ptable'                      => 'tl_belegungsplan_category',
		'ctable'                      => array('tl_belegungsplan_calender'),
		'switchToEdit'                => true,
		'enableVersioning'            => true,
		'sql' => array(
			'keys' => array(
				'id' => 'primary',
				'pid,published,sorting' => 'index'
			)
		)
	),
	// List
	'list' => array(
		'sorting' => array(
			'mode'                    => 4,
			'fields'                  => array('sorting'),
			'panelLayout'             => 'filter;sort,search,limit',
			'headerFields'            => array('title'),
			'child_record_callback'   => [\Mailwurm\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanObjekteListener::class, 'listQuestions']
		),
		'global_operations' => array(
			'all' => array(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			)
		),
		'operations' => array(
			'edit' => array(
				'label'               => &$GLOBALS['TL_LANG']['tl_belegungsplan_objekte']['edit'],
				'href'                => 'table=tl_belegungsplan_calender',
				'icon'                => 'cssimport.svg'
			),
			'editheader' => array(
				'label'               => &$GLOBALS['TL_LANG']['tl_belegungsplan_objekte']['editheader'],
				'href'                => 'act=edit',
				'icon'                => 'edit.svg',
				'button_callback'     => [\Mailwurm\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanObjekteListener::class, 'editHeader']
			),
			'delete' => array(
				'label'               => &$GLOBALS['TL_LANG']['tl_belegungsplan_objekte']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.svg',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'toggle' => array(
				'label'               => &$GLOBALS['TL_LANG']['tl_belegungsplan_objekte']['toggle'],
				'icon'                => 'visible.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback'     => [\Mailwurm\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanObjekteListener::class, 'toggleIcon']
			),
			'show' => array(
				'label'               => &$GLOBALS['TL_LANG']['tl_belegungsplan_objekte']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.svg'
			)
		)
	),
	// Palettes
	'palettes' => array(
		'__selector__'                => array(),
		'default'                     => '{title_legend},name,author,infotext,showInfotext;{hyperlink_legend:hide},titlelink,target,linkTitle,cssID;{publish_legend},published'
	),
	// Subpalettes
	'subpalettes' => array(),
	// Fields
	'fields' => array(
		'id' => array(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'pid' => array(
			'foreignKey'              => 'tl_belegungsplan_category.title',
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => array('type'=>'belongsTo', 'load'=>'eager')
		),
		'sorting' => array(
			'label'                   => &$GLOBALS['TL_LANG']['MSC']['sorting'],
			'sorting'                 => true,
			'flag'                    => 11,
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'tstamp' => array(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'name' => array(
			'label'                   => &$GLOBALS['TL_LANG']['tl_belegungsplan_objekte']['name'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
			'sql'                     => "varchar(128) NOT NULL default ''"
		),
		'author' => array(
			'label'                   => &$GLOBALS['TL_LANG']['tl_belegungsplan_objekte']['author'],
			'default' => BackendUser::getInstance() ? BackendUser::getInstance()->id : 0,
			'exclude'                 => true,
			'search'                  => true,
			'filter'                  => true,
			'sorting'                 => true,
			'flag'                    => 11,
			'inputType'               => 'select',
			'foreignKey'              => 'tl_user.name',
			'eval'                    => array('doNotCopy'=>true, 'chosen'=>true, 'mandatory'=>false, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => array('type'=>'belongsTo', 'load'=>'eager')
		),
		'infotext' => array(
			'label'                   => &$GLOBALS['TL_LANG']['tl_belegungsplan_objekte']['infotext'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50 clr'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'showInfotext' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_belegungsplan_objekte']['showInfotext'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50 m12'),
			'sql'                     => "char(1) COLLATE ascii_bin NOT NULL default '1'"
		),
		'titlelink' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_belegungsplan_objekte']['titlelink'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'rgxp'=>'url', 'maxlength'=>255, 'decodeEntities'=>true, 'dcaPicker'=>true, 'addWizardClass'=>false, 'tl_class'=>'w50'),
			'sql'                     => "text NULL"
		),
		'target' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_belegungsplan_objekte']['target'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50 m12'),
			'sql'                     => "char(1) COLLATE ascii_bin NOT NULL default ''"
		),
		'linkTitle' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_belegungsplan_objekte']['linkTitle'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'cssID' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_belegungsplan_objekte']['cssID'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('multiple'=>true, 'size'=>2, 'tl_class'=>'w50 clr'),
			'save_callback'	=> array
			(
				[\Mailwurm\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanObjekteListener::class, 'setEmptyCssID']
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'published' => array(
			'label'                   => &$GLOBALS['TL_LANG']['tl_belegungsplan_objekte']['published'],
			'exclude'                 => true,
			'filter'                  => true,
			'flag'                    => 2,
			'inputType'               => 'checkbox',
			'eval'                    => array('doNotCopy'=>true),
			'sql'                     => "char(1) COLLATE ascii_bin NOT NULL default ''"
		)
	)
);
