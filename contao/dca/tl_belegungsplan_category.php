<?php

declare(strict_types=1);

use Contao\System;
use Contao\BackendUser;
use Mailwurm\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanCategoryListener;

System::loadLanguageFile('tl_content');

$GLOBALS['TL_DCA']['tl_belegungsplan_category'] = [
    'config' => [
        'dataContainer' => \Contao\DC_Table::class,
        'ctable'           => ['tl_belegungsplan_objekte'],
        'switchToEdit'     => true,
        'enableVersioning' => true,
        'sql' => [
            'keys' => ['id' => 'primary'],
        ],
    ],
'list' => [
        'sorting' => [
            'mode'        => 1,
            'fields'      => ['title'],
            'flag'        => 1,
            'panelLayout' => 'search,limit',
        ],
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_belegungsplan_category']['edit'],
                'href'  => 'table=tl_belegungsplan_objekte',
                'icon'  => 'cssimport.svg',
            ],
            'editheader' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_belegungsplan_category']['editheader'],
                'href'            => 'act=edit',
                'icon'            => 'edit.svg',
                'button_callback' => [BelegungsplanCategoryListener::class, 'editHeader'],
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_belegungsplan_category']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'Wirklich löschen?\'))return false;Backend.getScrollOffset()"',
                'button_callback' => [BelegungsplanCategoryListener::class, 'deleteCategory'],
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_belegungsplan_category']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg',
            ],
        ],
    ],
    'palettes' => [
        'default' => '{title_legend},title,author;{hyperlink_legend:hide},titlelink,target,linkTitle,cssID',
    ],
'fields' => [
        'id' => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_belegungsplan_category']['title'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'author' => [
            'label'      => &$GLOBALS['TL_LANG']['tl_belegungsplan_category']['author'],
            'default' => BackendUser::getInstance() ? BackendUser::getInstance()->id : 0,
            'exclude'    => true,
            'search'     => true,
            'filter'     => true,
            'sorting'    => true,
            'flag'       => 11,
            'inputType'  => 'select',
            'foreignKey' => 'tl_user.name',
            'eval'       => ['doNotCopy' => true, 'chosen' => true, 'mandatory' => false, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'titlelink' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_belegungsplan_category']['titlelink'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => false, 'rgxp' => 'url', 'maxlength' => 255, 'decodeEntities' => true, 'dcaPicker' => true, 'addWizardClass' => false, 'tl_class' => 'w50'],
            'sql'       => "text NULL",
        ],
        'target' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_belegungsplan_category']['target'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 m12'],
            'sql'       => "char(1) COLLATE ascii_bin NOT NULL default ''",
        ],
        'linkTitle' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_belegungsplan_category']['linkTitle'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'cssID' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_belegungsplan_category']['cssID'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['multiple' => true, 'size' => 2, 'tl_class' => 'w50 clr'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
    ],
];
