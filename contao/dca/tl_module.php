<?php
use Contao\System;
use Contao\Backend;
use Contao\Input;
use Contao\Environment;
use Contao\StringUtil;
use Contao\DataContainer;
/**
 * Contao Open Source CMS
 *
 * Copyright (c) Mathias Ebert, based on work from Jan Karai
 *
 * @license LGPL-3.0-or-later
 *
 */
/**
 * onload_callback prueft beim Laden des Modul ob Felder auf disabled gesetzt werden muessen
 */
$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] = [\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'setDisabled'];
/**
 * Add palettes to tl_module
 */
// Add a palette selector
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'belegungsplan_anzeige_kategorie';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'belegungsplan_anzeige_legende';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'belegungsplan_anzeige_wochenende';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'belegungsplan_showAusgabe';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'belegungsplan_anzeige_linkText';
$GLOBALS['TL_DCA']['tl_module']['palettes']['belegungsplan'] =
    '{title_legend},name,headline,type;' .
    '{config_legend},belegungsplan_categories,belegungsplan_showAusgabe;' .
    '{belegung_farben_legend},'.
        'belegungsplan_color_frei,belegungsplan_opacity_frei,belegungsplan_reset_frei,'.
        'belegungsplan_color_belegt,belegungsplan_opacity_belegt,belegungsplan_reset_belegt,'.
        'belegungsplan_color_text,belegungsplan_opacity_text,belegungsplan_reset_text,'.
        'belegungsplan_color_rahmen,belegungsplan_opacity_rahmen,belegungsplan_reset_rahmen,'.
        'belegungsplan_anzeige_legende,'.
        'belegungsplan_anzeige_kategorie,'.
        'belegungsplan_anzeige_wochenende,'.
        'belegungsplan_anzeige_linkText,'.
        'belegungsplan_vollbelegung;'.
    '{template_legend},belegungsplan_template;' .
    '{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['belegungsplan_showAusgabe_standard'] = 'belegungsplan_month';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['belegungsplan_showAusgabe_automatic'] = 'belegungsplan_anzahlMonate';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['belegungsplan_showAusgabe_individuell'] = 'belegungsplan_individuellMonateStart,belegungsplan_individuellMonateEnde';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['belegungsplan_anzeige_linkText'] = 'belegungsplan_color_linkText,belegungsplan_opacity_linkText,belegungsplan_textDecorationLine,belegungsplan_textDecorationStyle,belegungsplan_reset_linkText';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['belegungsplan_anzeige_kategorie'] = 'belegungsplan_color_kategorie,belegungsplan_opacity_kategorie,belegungsplan_reset_kategorie,belegungsplan_color_kategorietext,belegungsplan_opacity_kategorietext,belegungsplan_reset_kategorietext';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['belegungsplan_anzeige_legende'] = 'belegungsplan_color_legende_frei,belegungsplan_color_legende_belegt,belegungsplan_opacity_legende,belegungsplan_reset_legende';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['belegungsplan_anzeige_wochenende'] = 'belegungsplan_bgcolor_wochenende,belegungsplan_opacity_bg_wochenende,belegungsplan_reset_bg_wochenende,belegungsplan_color_wochenendetext,belegungsplan_opacity_wochenendetext,belegungsplan_reset_wochenendetext';
/**
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_categories'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_categories'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'foreignKey'			=> 'tl_belegungsplan_category.title',
	'eval'					=> array('multiple'=>true, 'mandatory'=>true),
	'sql'					=> "blob NULL"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_showAusgabe'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_showAusgabe'],
	'inputType'				=> 'radio',
	'options'				=> array('standard', 'automatic', 'individuell'),
	'default'				=> 'standard',
	'reference'				=> &$GLOBALS['TL_LANG']['tl_module'],
	'explanation'			=> 'belegungsplan_showAusgabe',
	'eval'					=> array('mandatory'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50', 'style'=>'margin:10px', 'helpwizard'=>true),
	'sql'					=> "varchar(11) NOT NULL default 'standard'"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_month'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_month'],
	'exclude'				=> true,
	'inputType' => 'checkbox',
	'options'				=> $GLOBALS['TL_LANG']['tl_module']['belegungsplan_month']['month'] ?? [],
	'eval'					=> array('multiple'=>true, 'mandatory'=>true, 'tl_class'=>'m12'),
	'sql'					=> "blob NULL"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_anzahlMonate'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_anzahlMonate'],
	'exclude'				=> true,
	'inputType'				=> 'text',
	'eval'					=> array('size'=>1, 'rgxp'=>'natural', 'mandatory'=>true, 'maxval'=>100, 'minval'=>1, 'tl_class'=>'w25 m12'),
	'sql'					=> "smallint(5) unsigned NOT NULL default 1"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_individuellMonateStart'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_individuellMonateStart'],
	'exclude'				=> true,
	'inputType'				=> 'MonthYearWizard',
	'options'				=> $GLOBALS['TL_LANG']['tl_module']['belegungsplan_month']['month'] ?? [],
	'eval'					=> array('rgxp'=>'natural', 'mandatory'=>true, 'maxlength'=>4, 'tl_class'=>'w25 m12', 'style'=>'width:120px;margin-left:15px', 'placeholder'=>($GLOBALS['TL_LANG']['tl_module']['jahr'] ?? 'Jahr')),
	'sql'					=> "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_individuellMonateEnde'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_individuellMonateEnde'],
	'exclude'				=> true,
	'inputType'				=> 'MonthYearWizard',
	'options'				=> $GLOBALS['TL_LANG']['tl_module']['belegungsplan_month']['month'] ?? [],
	'eval'					=> array('rgxp'=>'natural', 'mandatory'=>true, 'maxlength'=>4, 'tl_class'=>'w25 m12', 'style'=>'width:120px;margin-left:15px', 'placeholder'=>($GLOBALS['TL_LANG']['tl_module']['jahr'] ?? 'Jahr')),
	'save_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'verifyEndDate']
	),
	'sql'					=> "varchar(255) NOT NULL default ''"
);
// ------------------------- Farbauswahl freie Tage -------------------------------------------------------------------
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_color_frei'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_color_frei'],
	'exclude'				=> true,
	'inputType'				=> 'text',
	'default'				=> '76,174,76',
	'explanation'			=> 'belegungsplan_color_frei',
	'load_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'loadColor']
	),
	'eval'					=> array('maxlength'=>6, 'minlength'=>6, 'mandatory'=>true, 'colorpicker'=>true, 'isHexColor'=>true, 'decodeEntities'=>true, 'tl_class'=>'w25 wizard clr', 'helpwizard'=>true),
	'save_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'saveColor']
	),
	'sql'					=> "varchar(20) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_opacity_frei'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_opacity'],
	'exclude'				=> true,
	'inputType'				=> 'select',
	'default'				=> '1.0',
	'options'				=> array('1.0','0.9','0.8','0.7','0.6','0.5','0.4','0.3','0.2','0.1'),
	'eval'					=> array('tl_class'=>'w25'),
	'sql'					=> "varchar(3) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_reset_frei'] = array(
	'input_field_callback'	=> [\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'renderResetButton'],
	'sql'					=> "char(1) COLLATE ascii_bin NOT NULL default ''",
);
// ------------------------- Farbauswahl belegte Tage -------------------------------------------------------------------
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_color_belegt'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_color_belegt'],
	'exclude'				=> true,
	'inputType'				=> 'text',
	'default'				=> '212,63,58',
	'explanation'			=> 'belegungsplan_color_belegt',
	'load_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'loadColor']
	),
	'eval'					=> array('maxlength'=>6, 'minlength'=>6, 'mandatory'=>true, 'colorpicker'=>true, 'isHexColor'=>true, 'decodeEntities'=>true, 'tl_class'=>'w25 wizard clr', 'helpwizard'=>true),
	'save_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'saveColor']
	),
	'sql'					=> "varchar(20) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_opacity_belegt'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_opacity'],
	'exclude'				=> true,
	'inputType'				=> 'select',
	'default'				=> '1.0',
	'options'				=> array('1.0','0.9','0.8','0.7','0.6','0.5','0.4','0.3','0.2','0.1'),
	'eval'					=> array('tl_class'=>'w25'),
	'sql'					=> "varchar(3) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_reset_belegt'] = array(
	'input_field_callback'	=> [\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'renderResetButton'],
	'sql'					=> "char(1) COLLATE ascii_bin NOT NULL default ''",
);
// -------------------------- Eigener Text ------------------------------------------------------------------
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_color_text'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_color_text'],
	'exclude'				=> true,
	'inputType'				=> 'text',
	'default'				=> '51,51,51',
	'explanation'			=> 'belegungsplan_color_text',
	'load_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'loadColor']
	),
	'eval'					=> array('maxlength'=>6, 'minlength'=>6, 'mandatory'=>true, 'colorpicker'=>true, 'isHexColor'=>true, 'decodeEntities'=>true, 'tl_class'=>'w25 wizard clr', 'helpwizard'=>true),
	'save_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'saveColor']
	),
	'sql'					=> "varchar(20) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_opacity_text'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_opacity'],
	'exclude'				=> true,
	'inputType'				=> 'select',
	'default'				=> '1.0',
	'options'				=> array('1.0','0.9','0.8','0.7','0.6','0.5','0.4','0.3','0.2','0.1'),
	'eval'					=> array('tl_class'=>'w25'),
	'sql'					=> "varchar(3) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_reset_text'] = array(
	'input_field_callback'	=> [\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'renderResetButton'],
	'sql'					=> "char(1) COLLATE ascii_bin NOT NULL default ''",
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_anzeige_linkText'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_anzeige_linkText'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'explanation'			=> 'belegungsplan_anzeige_linkText',
	'eval'					=> array('submitOnChange'=>true, 'tl_class'=>'w50 m12 wizard clr', 'helpwizard'=>true),
	'sql'					=> "char(1) COLLATE ascii_bin NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_color_linkText'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_color_linkText'],
	'exclude'				=> true,
	'inputType'				=> 'text',
	'default'				=> '102,16,242',
	'explanation'			=> 'belegungsplan_color_linkText',
	'load_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'loadColor']
	),
	'eval'					=> array('maxlength'=>6, 'minlength'=>6, 'mandatory'=>true, 'colorpicker'=>true, 'isHexColor'=>true, 'decodeEntities'=>true, 'tl_class'=>'w25 wizard clr', 'helpwizard'=>true),
	'save_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'saveColor']
	),
	'sql'					=> "varchar(20) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_opacity_linkText'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_opacity'],
	'exclude'				=> true,
	'inputType'				=> 'select',
	'default'				=> '1.0',
	'options'				=> array('1.0','0.9','0.8','0.7','0.6','0.5','0.4','0.3','0.2','0.1'),
	'eval'					=> array('tl_class'=>'w25'),
	'sql'					=> "varchar(3) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_textDecorationLine'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_textDecorationLine'],
	'exclude'				=> true,
	'inputType'				=> 'select',
	'default'				=> 'none',
	'explanation'			=> 'belegungsplan_textDecorationLine',
	'options'				=> array('none', 'underline', 'overline', 'line-through', 'underline overline', 'underline overline line-through'),
	'eval'					=> array('tl_class'=>'w50 clr', 'helpwizard'=>true, 'submitOnChange'=>true),
	'sql'					=> "varchar(32) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_textDecorationStyle'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_textDecorationStyle'],
	'exclude'				=> true,
	'inputType'				=> 'select',
	'default'				=> 'solid',
	'explanation'			=> 'belegungsplan_textDecorationStyle',
	'options'				=> array('solid', 'double', 'dotted', 'dashed', 'wavy'),
	'eval'					=> array('tl_class'=>'w50', 'helpwizard'=>true),
	'sql'					=> "varchar(8) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_reset_linkText'] = array(
	'input_field_callback'	=> [\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'renderResetButton'],
	'sql'					=> "char(1) COLLATE ascii_bin NOT NULL default ''",
);
// -------------------------- Rahmen Einstellungen ------------------------------------------------------------------
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_color_rahmen'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_color_rahmen'],
	'exclude'				=> true,
	'inputType'				=> 'text',
	'default'				=> '221,221,221',
	'explanation'			=> 'belegungsplan_color_rahmen',
	'load_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'loadColor']
	),
	'eval'					=> array('maxlength'=>6, 'minlength'=>6, 'mandatory'=>true, 'colorpicker'=>true, 'isHexColor'=>true, 'decodeEntities'=>true, 'tl_class'=>'w25 wizard clr', 'helpwizard'=>true),
	'save_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'saveColor']
	),
	'sql'					=> "varchar(20) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_opacity_rahmen'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_opacity'],
	'exclude'				=> true,
	'inputType'				=> 'select',
	'default'				=> '1.0',
	'options'				=> array('1.0','0.9','0.8','0.7','0.6','0.5','0.4','0.3','0.2','0.1'),
	'eval'					=> array('tl_class'=>'w25'),
	'sql'					=> "varchar(3) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_reset_rahmen'] = array(
	'input_field_callback'	=> [\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'renderResetButton'],
	'sql'					=> "char(1) COLLATE ascii_bin NOT NULL default ''",
);
// ------------------------- Kategorie Einstellungen -------------------------------------------------------------------
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_anzeige_kategorie'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_anzeige_kategorie'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'default'				=> '1',
	'explanation'			=> 'belegungsplan_anzeige_kategorie',
	'eval'					=> array('submitOnChange'=>true, 'tl_class'=>'w50 wizard clr m12', 'helpwizard'=>true),
	'sql'					=> "char(1) COLLATE ascii_bin NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_color_kategorie'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_color_kategorie'],
	'exclude'				=> true,
	'inputType'				=> 'text',
	'default'				=> '204,204,204',
	'explanation'			=> 'belegungsplan_color_kategorie',
	'load_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'loadColor']
	),
	'eval'					=> array('maxlength'=>6, 'minlength'=>6, 'mandatory'=>true, 'colorpicker'=>true, 'isHexColor'=>true, 'decodeEntities'=>true, 'tl_class'=>'w25 wizard clr', 'helpwizard'=>true),
	'save_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'saveColor']
	),
	'sql'					=> "varchar(20) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_opacity_kategorie'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_opacity'],
	'exclude'				=> true,
	'inputType'				=> 'select',
	'default'				=> '1.0',
	'options'				=> array('1.0','0.9','0.8','0.7','0.6','0.5','0.4','0.3','0.2','0.1'),
	'eval'					=> array('tl_class'=>'w25'),
	'sql'					=> "varchar(3) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_reset_kategorie'] = array(
	'input_field_callback'	=> [\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'renderResetButton'],
	'sql'					=> "char(1) COLLATE ascii_bin NOT NULL default ''",
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_color_kategorietext'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_color_kategorietext'],
	'exclude'				=> true,
	'inputType'				=> 'text',
	'default'				=> '0,0,0',
	'explanation'			=> 'belegungsplan_color_kategorietext',
	'load_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'loadColor']
	),
	'eval'					=> array('maxlength'=>6, 'minlength'=>6, 'mandatory'=>true, 'colorpicker'=>true, 'isHexColor'=>true, 'decodeEntities'=>true, 'tl_class'=>'w25 wizard clr', 'helpwizard'=>true),
	'save_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'saveColor']
	),
	'sql'					=> "varchar(20) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_opacity_kategorietext'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_opacity'],
	'exclude'				=> true,
	'inputType'				=> 'select',
	'default'				=> '1.0',
	'options'				=> array('1.0','0.9','0.8','0.7','0.6','0.5','0.4','0.3','0.2','0.1'),
	'eval'					=> array('tl_class'=>'w25'),
	'sql'					=> "varchar(3) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_reset_kategorietext'] = array(
	'input_field_callback'	=> [\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'renderResetButton'],
	'sql'					=> "char(1) COLLATE ascii_bin NOT NULL default ''",
);
// ------------------------- Legende Einstellungen -------------------------------------------------------------------
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_anzeige_legende'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_anzeige_legende'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'default'				=> '1',
	'explanation'			=> 'belegungsplan_anzeige_legende',
	'eval'					=> array('submitOnChange'=>true, 'tl_class'=>'w50 wizard clr', 'helpwizard'=>true),
	'sql'					=> "char(1) COLLATE ascii_bin NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_color_legende_frei'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_color_legende_frei'],
	'exclude'				=> true,
	'inputType'				=> 'text',
	'default'				=> '255,255,255',
	'explanation'			=> 'belegungsplan_color_legende_frei',
	'load_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'loadColor']
	),
	'eval'					=> array('maxlength'=>6, 'minlength'=>6, 'mandatory'=>true, 'colorpicker'=>true, 'isHexColor'=>true, 'decodeEntities'=>true, 'tl_class'=>'w25 wizard clr', 'helpwizard'=>true),
	'save_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'saveColor']
	),
	'sql'					=> "varchar(20) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_color_legende_belegt'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_color_legende_belegt'],
	'exclude'				=> true,
	'inputType'				=> 'text',
	'default'				=> '255,255,255',
	'explanation'			=> 'belegungsplan_color_legende_belegt',
	'load_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'loadColor']
	),
	'eval'					=> array('maxlength'=>6, 'minlength'=>6, 'mandatory'=>true, 'colorpicker'=>true, 'isHexColor'=>true, 'decodeEntities'=>true, 'tl_class'=>'w25 wizard', 'helpwizard'=>true),
	'save_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'saveColor']
	),
	'sql'					=> "varchar(20) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_opacity_legende'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_opacity'],
	'exclude'				=> true,
	'inputType'				=> 'select',
	'default'				=> '1.0',
	'options'				=> array('1.0','0.9','0.8','0.7','0.6','0.5','0.4','0.3','0.2','0.1'),
	'eval'					=> array('tl_class'=>'w25'),
	'sql'					=> "varchar(3) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_reset_legende'] = array(
	'input_field_callback'	=> [\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'renderResetButton'],
	'sql'					=> "char(1) COLLATE ascii_bin NOT NULL default ''",
);
// ------------------------- Wochenende Einstellungen ---------------------------------------------------------
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_anzeige_wochenende'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_anzeige_wochenende'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'default'				=> '1',
	'explanation'			=> 'belegungsplan_anzeige_wochenende',
	'eval'					=> array('submitOnChange'=>true, 'tl_class'=>'w50 wizard clr', 'helpwizard'=>true),
	'sql'					=> "char(1) COLLATE ascii_bin NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_bgcolor_wochenende'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_bgcolor_wochenende'],
	'exclude'				=> true,
	'inputType'				=> 'text',
	'default'				=> '204,204,204',
	'explanation'			=> 'belegungsplan_bgcolor_wochenende',
	'load_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'loadColor']
	),
	'eval'					=> array('maxlength'=>6, 'minlength'=>6, 'mandatory'=>true, 'colorpicker'=>true, 'isHexColor'=>true, 'decodeEntities'=>true, 'tl_class'=>'w25 wizard clr', 'helpwizard'=>true),
	'save_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'saveColor']
	),
	'sql'					=> "varchar(20) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_opacity_bg_wochenende'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_opacity'],
	'exclude'				=> true,
	'inputType'				=> 'select',
	'default'				=> '1.0',
	'options'				=> array('1.0','0.9','0.8','0.7','0.6','0.5','0.4','0.3','0.2','0.1'),
	'eval'					=> array('tl_class'=>'w25'),
	'sql'					=> "varchar(3) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_reset_bg_wochenende'] = array(
	'input_field_callback'	=> [\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'renderResetButton'],
	'sql'					=> "char(1) COLLATE ascii_bin NOT NULL default ''",
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_color_wochenendetext'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_color_wochenendetext'],
	'exclude'				=> true,
	'inputType'				=> 'text',
	'default'				=> '51,51,51',
	'explanation'			=> 'belegungsplan_color_wochenendetext',
	'load_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'loadColor']
	),
	'eval'					=> array('maxlength'=>6, 'minlength'=>6, 'mandatory'=>true, 'colorpicker'=>true, 'isHexColor'=>true, 'decodeEntities'=>true, 'tl_class'=>'w25 wizard clr', 'helpwizard'=>true),
	'save_callback'			=> array
	(
		[\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'saveColor']
	),
	'sql'					=> "varchar(20) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_opacity_wochenendetext'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_opacity'],
	'exclude'				=> true,
	'inputType'				=> 'select',
	'default'				=> '1.0',
	'options'				=> array('1.0','0.9','0.8','0.7','0.6','0.5','0.4','0.3','0.2','0.1'),
	'eval'					=> array('tl_class'=>'w25'),
	'sql'					=> "varchar(3) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_reset_wochenendetext'] = array(
	'input_field_callback'	=> [\Tonsinn\BelegungsplanBundle\EventListener\DataContainer\BelegungsplanModuleListener::class, 'renderResetButton'],
	'sql'					=> "char(1) COLLATE ascii_bin NOT NULL default ''",
);
// ------------------------- Vollbelegung (Anreise/Abreise als belegt anzeigen) ---------------------------------------
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_vollbelegung'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_vollbelegung'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => 'w50 m12 clr'),
	'sql'       => "char(1) COLLATE ascii_bin NOT NULL default ''"
);
// ------------------------- Template-Einstellungen -------------------------------------------------------------------
$GLOBALS['TL_DCA']['tl_module']['fields']['belegungsplan_template'] = array(
	'label'					=> &$GLOBALS['TL_LANG']['tl_module']['belegungsplan_template'],
	'exclude'				=> true,
	'inputType'				=> 'select',
	'options_callback'		=> static function ()
	{
		return \Contao\Controller::getTemplateGroup('mod_belegungsplan_');
	},
	'eval'					=> array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
	'sql'					=> "varchar(64) NOT NULL default ''"
);

