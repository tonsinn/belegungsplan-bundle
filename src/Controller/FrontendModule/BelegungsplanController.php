<?php

declare(strict_types=1);

namespace Mailwurm\BelegungsplanBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Input;
use Contao\ModuleModel;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('belegungsplan', category: 'belegung', template: 'mod_belegungsplan_table')]
class BelegungsplanController extends AbstractFrontendModuleController
{
    public function __construct(private readonly Connection $db)
    {
    }

    /**
     * Template-Auswahl vor createTemplate() einsetzen, da die Closure in
     * AbstractFragmentController den Namen zum Erstellungszeitpunkt einfriert.
     * setName() in getResponse() käme zu spät – die Closure würde immer das
     * Default-Template 'mod_belegungsplan_table' rendern.
     */
    public function __invoke(Request $request, ModuleModel $model, string $section, array|null $classes = null): Response
    {
        if ($model->belegungsplan_template && !$model->customTpl) {
            $model->customTpl = $model->belegungsplan_template;
        }

        return parent::__invoke($request, $model, $section, $classes);
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $GLOBALS['TL_CSS'][] = 'bundles/mailwurmbelegungsplan/belegungsplan.css||static';

        // Contao erkennt Template-Namen ohne Slash als Legacy und setzt headline als String.
        // Die Twig-Basevorlage braucht aber headline.text / headline.tag_name.
        $headlineData = StringUtil::deserialize($model->headline, true);
        $template->headline = [
            'text'     => $headlineData['value'] ?? '',
            'tag_name' => $headlineData['unit'] ?? 'h1',
        ];

        $categories = StringUtil::deserialize($model->belegungsplan_categories);
        $months = StringUtil::deserialize($model->belegungsplan_month);
        $showAusgabe = $model->belegungsplan_showAusgabe;
        $anzahlMonate = (int) $model->belegungsplan_anzahlMonate;

        if (empty($categories) || (empty($months) && $showAusgabe === 'standard')) {
            return $template->getResponse();
        }

        $arrInfo = [];
        $intStartAuswahl = 0;
        $intEndeAuswahl = 0;
        $intYear = '';
        $arrJahre = [];

        if ($showAusgabe === 'standard') {
            sort($months, SORT_NUMERIC);
            $intMax = (int) max($months);
            $intYear = Input::get('belegyear');
            $blnClearInput = false;

            if ($intYear === null) {
                $intYear = $intMax < (int) date('n') ? (int) date('Y') + 1 : (int) date('Y');
                $blnClearInput = true;
            } else {
                if (!empty($intYear)) {
                    if (!is_numeric($intYear) || strlen((string)$intYear) !== 4) {
                        $arrInfo[] = '1. ' . ($GLOBALS['TL_LANG']['mailwurm_belegung']['info'][1] ?? '');
                    } elseif ((int)$intYear < (int) date('Y')) {
                        $arrInfo[] = '4. ' . ($GLOBALS['TL_LANG']['mailwurm_belegung']['info'][2] ?? '');
                    }
                }
            }

            $intMinYear = $intMax < (int) date('n') ? (int) date('Y') + 1 : (int) date('Y');

            if (!empty($intYear) && empty($arrInfo)) {
                $intStartAuswahl = (int) mktime(0, 0, 0, 1, 1, (int)$intYear);
                $intEndeAuswahl = (int) mktime(23, 59, 59, 12, 31, (int)$intYear);
            }
        } elseif ($showAusgabe === 'automatic') {
            $intStartAuswahl = (int) mktime(0, 0, 0, (int)date('m'), 1, (int)date('Y'));
            $intEndeAuswahl = (int) strtotime('+' . $anzahlMonate . ' Months', $intStartAuswahl) - 1;
        } elseif ($showAusgabe === 'individuell') {
            $aStart = StringUtil::deserialize($model->belegungsplan_individuellMonateStart);
            $aEnde = StringUtil::deserialize($model->belegungsplan_individuellMonateEnde);
            if (empty($aStart) || empty($aEnde)) {
                $arrInfo[] = '6. ' . ($GLOBALS['TL_LANG']['mailwurm_belegung']['info'][4] ?? '');
            } else {
                $iDaysEnd = date('t', (int) mktime(0, 0, 0, (int)$aEnde['unit'], 1, (int)$aEnde['value']));
                $intStartAuswahl = (int) mktime(0, 0, 0, (int)$aStart['unit'], 1, (int)$aStart['value']);
                $intEndeAuswahl = (int) mktime(23, 59, 59, (int)$aEnde['unit'], (int)$iDaysEnd, (int)$aEnde['value']);
            }
        }
if (empty($arrInfo)) {
            $arrCategorieObjekte = $this->getCategorieObjekte($categories);
            if (empty($arrCategorieObjekte)) {
                $arrInfo[] = '3. ' . ($GLOBALS['TL_LANG']['mailwurm_belegung']['info'][0] ?? '');
            } else {
                $arrCategorieObjekteCalender = $this->getObjekteCalender($intStartAuswahl, $intEndeAuswahl, $arrCategorieObjekte, $showAusgabe, $months);
                if ($showAusgabe === 'standard') {
                    $arrJahre = $this->getYears($intMinYear, $intYear);
                }
            }
        }

        if (!empty($arrInfo)) {
            $template->info = $arrInfo;
        } else {
            $template->display_year = $intYear;
            $template->number_year = count($arrJahre);
            $template->selectable_year = $arrJahre;
            $template->CategorieObjekteCalender = $this->sortNachWizard($arrCategorieObjekteCalender ?? [], $categories);

            $arrFeiertage = $this->getFeiertage($intStartAuswahl, $intEndeAuswahl);

            if ($showAusgabe === 'standard') {
                $template->Month = $this->dataMonth($months, $intStartAuswahl, $arrFeiertage);
            } else {
                $template->Month = $this->dataMonthIndividuell($intStartAuswahl, $intEndeAuswahl, $arrFeiertage);
            }

            $template->Frei = $GLOBALS['TL_LANG']['mailwurm_belegung']['legende']['frei'] ?? '';
            $template->Belegt = $GLOBALS['TL_LANG']['mailwurm_belegung']['legende']['belegt'] ?? '';
            $template->RgbaFrei = $this->rgba($model->belegungsplan_color_frei, $model->belegungsplan_opacity_frei, '76,174,76');
            $template->RgbaBelegt = $this->rgba($model->belegungsplan_color_belegt, $model->belegungsplan_opacity_belegt, '212,63,58');
            $template->RgbaText = $this->rgba($model->belegungsplan_color_text, $model->belegungsplan_opacity_text, '51,51,51');
            $template->RgbaRahmen = $this->rgba($model->belegungsplan_color_rahmen, $model->belegungsplan_opacity_rahmen, '221,221,221');
            $template->AnzeigeLegende = $model->belegungsplan_anzeige_legende;
            $template->AnzeigeKategorie = $model->belegungsplan_anzeige_kategorie;
            $template->AnzeigeWochenende = $model->belegungsplan_anzeige_wochenende;
            $template->VollBelegung      = (bool) $model->belegungsplan_vollbelegung;
            $template->RgbaBgWochenende = $this->rgba($model->belegungsplan_bgcolor_wochenende, $model->belegungsplan_opacity_bg_wochenende, '238,238,238');
            $template->RgbaWochenendetext = $this->rgba($model->belegungsplan_color_wochenendetext, $model->belegungsplan_opacity_wochenendetext, '51,51,51');
            $template->RgbaKategorie = $this->rgba($model->belegungsplan_color_kategorie, $model->belegungsplan_opacity_kategorie, '200,200,200');
            $template->RgbaKategorietext = $this->rgba($model->belegungsplan_color_kategorietext, $model->belegungsplan_opacity_kategorietext, '51,51,51');
            $template->LinkKategorie = $model->belegungsplan_anzeige_linkKategorie;
            $template->RgbaLinkKategorie = $this->rgba($model->belegungsplan_color_linkKategorie, $model->belegungsplan_opacity_linkKategorie, '51,51,51');
            $template->KategorieDecorationLine = 'text-decoration: '.($model->belegungsplan_kategorieDecorationLine ?: 'none').';';
            $template->KategorieDecorationStyle = 'text-decoration-style: '.($model->belegungsplan_kategorieDecorationStyle ?: 'solid').';';
            $template->LinkText = $model->belegungsplan_anzeige_linkText;
            $template->RgbaLinkText = $this->rgba($model->belegungsplan_color_linkText, $model->belegungsplan_opacity_linkText, '51,51,51');
            $template->TextDecorationLine = 'text-decoration: '.($model->belegungsplan_textDecorationLine ?: 'none').';';
            $template->TextDecorationStyle = 'text-decoration-style: '.($model->belegungsplan_textDecorationStyle ?: 'solid').';';
            $template->RgbaTextLegendeFrei = $this->rgba($model->belegungsplan_color_legende_frei, $model->belegungsplan_opacity_legende, '51,51,51');
            $template->RgbaTextLegendeBelegt = $this->rgba($model->belegungsplan_color_legende_belegt, $model->belegungsplan_opacity_legende, '51,51,51');
        }

        return $template->getResponse();
    }
private function rgba(mixed $color, mixed $opacity, string $defaultColor, string $defaultOpacity = '1.0'): string
    {
        $color = (string) $color;
        $opacity = (string) $opacity;
        return 'rgba(' . ($color ?: $defaultColor) . ',' . ($opacity ?: $defaultOpacity) . ')';
    }

    private function getCategorieObjekte(array $categories): array
    {
        $result = [];
        $rows = $this->db->fetchAllAssociative("
            SELECT tbc.id as CategoryID, tbc.title as CategoryTitle,
                   tbc.titlelink as CategoryTitleLink, tbc.target as CategoryTarget,
                   tbc.linkTitle as CategoryLinkTitle, tbc.cssID as CategoryLinkCSS,
                   tbo.id as ObjektID, tbo.name as ObjektName,
                   tbo.infotext as ObjektInfoText, tbo.titlelink as ObjektTitleLink,
                   tbo.target as ObjektTarget, tbo.linkTitle as ObjektLinkTitle,
                   tbo.cssID as ObjektLinkCSS, tbo.showInfotext as ObjektShowInfotext,
                   tbo.sorting as ObjektSortierung
            FROM tl_belegungsplan_category tbc
            JOIN tl_belegungsplan_objekte tbo ON tbo.pid = tbc.id
            WHERE tbo.published = '1'
        ");

        foreach ($rows as $row) {
            if (!in_array($row['CategoryID'], $categories)) {
                continue;
            }
            $objekt = [
                'ObjektID'           => (int) $row['ObjektID'],
                'ObjektName'         => StringUtil::specialchars($row['ObjektName']),
                'ObjektInfoText'     => StringUtil::specialchars($row['ObjektInfoText']),
                'ObjektTitleLink'    => StringUtil::specialchars($row['ObjektTitleLink']),
                'ObjektTarget'       => StringUtil::specialchars($row['ObjektTarget']),
                'ObjektLinkTitle'    => StringUtil::specialchars($row['ObjektLinkTitle']),
                'ObjektLinkCSS'      => StringUtil::deserialize($row['ObjektLinkCSS']),
                'ObjektShowInfotext' => StringUtil::specialchars($row['ObjektShowInfotext']),
            ];
            if (isset($result[$row['CategoryID']])) {
                $result[$row['CategoryID']]['Objekte'][$row['ObjektSortierung']] = $objekt;
            } else {
                $result[$row['CategoryID']] = [
                    'CategoryTitle'     => StringUtil::specialchars($row['CategoryTitle']),
                    'CategoryTitleLink' => StringUtil::specialchars($row['CategoryTitleLink']),
                    'CategoryTarget'    => StringUtil::specialchars($row['CategoryTarget']),
                    'CategoryLinkTitle' => StringUtil::specialchars($row['CategoryLinkTitle']),
                    'CategoryLinkCSS'   => StringUtil::deserialize($row['CategoryLinkCSS']),
                    'Objekte'           => [$row['ObjektSortierung'] => $objekt],
                ];
            }
        }
        return $result;
    }

    private function getFeiertage(int $intStart, int $intEnde): array
    {
        $result = [];
        $rows = $this->db->fetchAllAssociative("
            SELECT DAY(FROM_UNIXTIME(startDate)) as Tag,
                   MONTH(FROM_UNIXTIME(startDate)) as Monat,
                   YEAR(FROM_UNIXTIME(startDate)) as Jahr,
                   title, ausgabe, hintergrund, opacity, textcolor, textopacity, showTitleText
            FROM tl_belegungsplan_feiertage
            WHERE startDate >= ? AND startDate <= ?
        ", [$intStart, $intEnde]);

        foreach ($rows as $row) {
            if ($row['ausgabe']) {
                $result[$row['Jahr']][$row['Monat']][$row['Tag']] = [
                    'Title'         => $row['title'],
                    'Style'         => " style='background-color:rgba(".$row['hintergrund'].",".$row['opacity'].");color:rgba(".$row['textcolor'].",".$row['textopacity'].");cursor:pointer;'",
                    'ShowTitleText' => $row['showTitleText'],
                ];
            } else {
                $result[$row['Jahr']][$row['Monat']][$row['Tag']] = [
                    'Title'         => $row['title'],
                    'ShowTitleText' => $row['showTitleText'],
                ];
            }
        }
        return $result;
    }

    private function getYears(int $intMinYear, mixed $intYear): array
    {
        $years = [];
        $rows = $this->db->fetchAllAssociative("
            SELECT DISTINCT Start FROM (
                SELECT YEAR(FROM_UNIXTIME(tbc.startDate)) as Start
                FROM tl_belegungsplan_calender tbc
                JOIN tl_belegungsplan_objekte tbo ON tbc.pid = tbo.id
                WHERE YEAR(FROM_UNIXTIME(tbc.startDate)) >= :minYear
                AND tbo.published = '1'
                UNION
                SELECT YEAR(FROM_UNIXTIME(tbc.endDate)) as Start
                FROM tl_belegungsplan_calender tbc
                JOIN tl_belegungsplan_objekte tbo ON tbc.pid = tbo.id
                WHERE YEAR(FROM_UNIXTIME(tbc.endDate)) >= :minYear
                AND tbo.published = '1'
            ) years
            ORDER BY Start ASC
        ", ['minYear' => $intMinYear]);

        foreach ($rows as $row) {
            $years[] = [
                'single_year' => $row['Start'],
                'year_href'   => '?belegyear=' . $row['Start'],
                'active'      => $row['Start'] == $intYear ? 1 : 0,
            ];
        }
        return $years;
    }
private function dataMonth(array $arrMonth, int $intStartAuswahl, array $arrFeiertage): array
    {
        $result = [];
        $intJahr = (int) date('Y', $intStartAuswahl);
        foreach ($arrMonth as $value) {
            $iDayMonths = (int) date('t', mktime(0, 0, 0, (int)$value, 1, $intJahr));
            $result[$intJahr][$value]['Name'] = $GLOBALS['TL_LANG']['mailwurm_belegung']['month'][$value] ?? '';
            $result[$intJahr][$value]['TageMonat'] = $iDayMonths;
            $result[$intJahr][$value]['ColSpan'] = $iDayMonths + 1;
            $intFirstDay = (int) date('N', mktime(0, 0, 0, (int)$value, 1, $intJahr));
            for ($f = 1, $i = $intFirstDay; $f <= $iDayMonths; $f++) {
                $strClass = '';
                $result[$intJahr][$value]['Days'][$f]['Day'] = $GLOBALS['TL_LANG']['mailwurm_belegung']['day'][$i] ?? '';
                $result[$intJahr][$value]['Days'][$f]['DayCut'] = $GLOBALS['TL_LANG']['mailwurm_belegung']['short_cut_day'][$i] ?? '';
                $result[$intJahr][$value]['Days'][$f]['DayWeekNum'] = $i;
                if (!empty($arrFeiertage[$intJahr][$value][$f])) {
                    $strClass = 'holiday';
                    $result[$intJahr][$value]['Days'][$f]['Title'] = $arrFeiertage[$intJahr][$value][$f]['Title'];
                    $result[$intJahr][$value]['Days'][$f]['Style'] = $arrFeiertage[$intJahr][$value][$f]['Style'] ?? '';
                    $result[$intJahr][$value]['Days'][$f]['ShowTitleText'] = $arrFeiertage[$intJahr][$value][$f]['ShowTitleText'];
                }
                if (empty($strClass)) {
                    $strClass = $i === 6 ? 'saturday' : ($i === 7 ? 'sunday' : '');
                }
                $result[$intJahr][$value]['Days'][$f]['Class'] = trim($strClass);
                $i === 7 ? $i = 1 : $i++;
            }
        }
        return $result;
    }

    private function dataMonthIndividuell(int $intStartAuswahl, int $intEndeAuswahl, array $arrFeiertage): array
    {
        $result = [];
        $intStartMonat = (int) date('n', $intStartAuswahl);
        $intStartJahr = (int) date('Y', $intStartAuswahl);
        $intEndeMonat = (int) date('n', $intEndeAuswahl);
        $intEndeJahr = (int) date('Y', $intEndeAuswahl);

        for ($y = $intStartJahr; $y <= $intEndeJahr; $y++) {
            $m = ($y === $intStartJahr) ? $intStartMonat : 1;
            for (; ; $m++) {
                $iDayMonths = (int) date('t', mktime(0, 0, 0, $m, 1, $y));
                $result[$y][$m]['Name'] = $GLOBALS['TL_LANG']['mailwurm_belegung']['month'][$m] ?? '';
                $result[$y][$m]['TageMonat'] = $iDayMonths;
                $result[$y][$m]['ColSpan'] = $iDayMonths + 1;
                $intFirstDay = (int) date('N', mktime(0, 0, 0, $m, 1, $y));
                for ($f = 1, $i = $intFirstDay; $f <= $iDayMonths; $f++) {
                    $strClass = '';
                    $result[$y][$m]['Days'][$f]['Day'] = $GLOBALS['TL_LANG']['mailwurm_belegung']['day'][$i] ?? '';
                    $result[$y][$m]['Days'][$f]['DayCut'] = $GLOBALS['TL_LANG']['mailwurm_belegung']['short_cut_day'][$i] ?? '';
                    $result[$y][$m]['Days'][$f]['DayWeekNum'] = $i;
                    if (!empty($arrFeiertage[$y][$m][$f])) {
                        $strClass = 'holiday';
                        $result[$y][$m]['Days'][$f]['Title'] = $arrFeiertage[$y][$m][$f]['Title'];
                        $result[$y][$m]['Days'][$f]['Style'] = $arrFeiertage[$y][$m][$f]['Style'] ?? '';
                        $result[$y][$m]['Days'][$f]['ShowTitleText'] = $arrFeiertage[$y][$m][$f]['ShowTitleText'];
                    }
                    if (empty($strClass)) {
                        $strClass = $i === 6 ? 'saturday' : ($i === 7 ? 'sunday' : '');
                    }
                    $result[$y][$m]['Days'][$f]['Class'] = trim($strClass);
                    $i === 7 ? $i = 1 : $i++;
                }
                if ($m === 12 || ($y === $intEndeJahr && $m === $intEndeMonat)) {
                    break;
                }
            }
            if ($y === $intEndeJahr) {
                break;
            }
        }
        return $result;
    }

    private function sortNachWizard(array $arrCategorieObjekteCalender, array $arrBelegungsplanCategory): array
    {
        $arrHelper = array_flip($arrBelegungsplanCategory);
        foreach ($arrHelper as $key => $value) {
            if (array_key_exists($key, $arrCategorieObjekteCalender)) {
                $arrHelper[$key] = $arrCategorieObjekteCalender[$key];
                ksort($arrHelper[$key]['Objekte']);
            } else {
                unset($arrHelper[$key]);
            }
        }
        return $arrHelper;
    }
private function includeCalender(int $intBuchungsStartJahr, int $intBuchungsEndeJahr, int $intY, mixed $existing, int $z): string
    {
        if ($intBuchungsStartJahr !== $intBuchungsEndeJahr) {
            if ($intY === ($z === 0 ? $intBuchungsStartJahr : $intBuchungsEndeJahr)) {
                return $z === 0 ? '0#1' : '1#0';
            }
            return '1#1';
        }
        if (isset($existing)) {
            return '1#1';
        }
        return $z === 0 ? '0#1' : '1#0';
    }

    private function getObjekteCalender(int $intStartAuswahl, int $intEndeAuswahl, array $arrCategorieObjekte, string $showAusgabe, array $months): array
    {
        $rows = $this->db->fetchAllAssociative("
            SELECT tbo.id as ObjektID, tbo.sorting as ObjektSortierung, tbcat.id as CategoryID,
                (CASE WHEN tbc.startDate < :start THEN DAY(FROM_UNIXTIME(:start)) ELSE DAY(FROM_UNIXTIME(tbc.startDate)) END) as StartTag,
                (CASE WHEN tbc.startDate < :start THEN MONTH(FROM_UNIXTIME(:start)) ELSE MONTH(FROM_UNIXTIME(tbc.startDate)) END) as StartMonat,
                (CASE WHEN tbc.startDate < :start THEN YEAR(FROM_UNIXTIME(:start)) ELSE YEAR(FROM_UNIXTIME(tbc.startDate)) END) as StartJahr,
                YEAR(FROM_UNIXTIME(tbc.startDate)) as BuchungsStartJahr,
                (CASE WHEN tbc.endDate > :ende THEN DAY(FROM_UNIXTIME(:ende)) ELSE DAY(FROM_UNIXTIME(tbc.endDate)) END) as EndeTag,
                (CASE WHEN tbc.endDate > :ende THEN MONTH(FROM_UNIXTIME(:ende)) ELSE MONTH(FROM_UNIXTIME(tbc.endDate)) END) as EndeMonat,
                (CASE WHEN tbc.endDate > :ende THEN YEAR(FROM_UNIXTIME(:ende)) ELSE YEAR(FROM_UNIXTIME(tbc.endDate)) END) as EndeJahr,
                YEAR(FROM_UNIXTIME(tbc.endDate)) as BuchungsEndeJahr
            FROM tl_belegungsplan_calender tbc
            JOIN tl_belegungsplan_objekte tbo ON tbc.pid = tbo.id
            JOIN tl_belegungsplan_category tbcat ON tbo.pid = tbcat.id
            WHERE tbo.published = '1'
            AND tbc.startDate <= tbc.endDate
            AND ((tbc.startDate < :start AND tbc.endDate >= :start)
                OR (tbc.startDate >= :start AND tbc.endDate <= :ende)
                OR (tbc.startDate < :ende AND tbc.endDate > :ende))
        ", ['start' => $intStartAuswahl, 'ende' => $intEndeAuswahl]);

        foreach ($rows as $row) {
            for ($d = (int)$row['StartTag'], $m = (int)$row['StartMonat'], $e = (int)date('t', mktime(0,0,0,(int)$row['StartMonat'],1,(int)$row['StartJahr'])), $y = (int)$row['StartJahr'], $z = 0;;) {
                $catId = $row['CategoryID'];
                $sort = $row['ObjektSortierung'];
                $existing = $arrCategorieObjekte[$catId]['Objekte'][$sort]['Calender'][$y][$m][$d] ?? null;

                if ($showAusgabe === 'standard' && !in_array($m, $months)) {
                    // skip
                } else {
                    if ($z === 0 && $y === (int)$row['EndeJahr'] && $m === (int)$row['EndeMonat'] && $d === (int)$row['EndeTag']) {
                        // Tagesaufenthalt: Anreise und Abreise am selben Tag → voll belegt
                        $arrCategorieObjekte[$catId]['Objekte'][$sort]['Calender'][$y][$m][$d] = '1#1';
                    } elseif ($z === 0) {
                        $arrCategorieObjekte[$catId]['Objekte'][$sort]['Calender'][$y][$m][$d] =
                            $this->includeCalender((int)$row['BuchungsStartJahr'], (int)$row['BuchungsEndeJahr'], $y, $existing, 0);
                    } elseif ($y === (int)$row['EndeJahr'] && $m === (int)$row['EndeMonat'] && $d === (int)$row['EndeTag']) {
                        $arrCategorieObjekte[$catId]['Objekte'][$sort]['Calender'][$y][$m][$d] =
                            $this->includeCalender((int)$row['BuchungsStartJahr'], (int)$row['BuchungsEndeJahr'], $y, $existing, 1);
                        break;
                    } else {
                        $arrCategorieObjekte[$catId]['Objekte'][$sort]['Calender'][$y][$m][$d] = '1#1';
                    }
                }

                if ($y === (int)$row['EndeJahr'] && $m === (int)$row['EndeMonat'] && $d === (int)$row['EndeTag']) {
                    break;
                }

                if ($d === $e) {
                    if ($m === 12) { $m = 1; $y++; } else { $m++; }
                    $d = 0;
                    $e = (int) date('t', mktime(0, 0, 0, $m, 1, $y));
                }
                $d++;
                $z++;
            }
        }
        return $arrCategorieObjekte;
    }
}
