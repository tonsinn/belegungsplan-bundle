<?php

declare(strict_types=1);

namespace Tonsinn\BelegungsplanBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Symfony\Component\HttpFoundation\RequestStack;

class BelegungsplanModuleListener
{
    /**
     * Maps each reset field to the color/opacity fields it resets
     * and their default hex values / opacity values.
     */
    private const RESET_MAP = [
        'belegungsplan_reset_frei' => [
            'colors'    => ['belegungsplan_color_frei' => '4CAE4C'],
            'opacities' => ['belegungsplan_opacity_frei' => '1.0'],
        ],
        'belegungsplan_reset_belegt' => [
            'colors'    => ['belegungsplan_color_belegt' => 'D43F3A'],
            'opacities' => ['belegungsplan_opacity_belegt' => '1.0'],
        ],
        'belegungsplan_reset_text' => [
            'colors'    => ['belegungsplan_color_text' => '333333'],
            'opacities' => ['belegungsplan_opacity_text' => '1.0'],
        ],
        'belegungsplan_reset_rahmen' => [
            'colors'    => ['belegungsplan_color_rahmen' => 'DDDDDD'],
            'opacities' => ['belegungsplan_opacity_rahmen' => '1.0'],
        ],
        'belegungsplan_reset_legende' => [
            'colors'    => [
                'belegungsplan_color_legende_frei'   => 'FFFFFF',
                'belegungsplan_color_legende_belegt' => 'FFFFFF',
            ],
            'opacities' => ['belegungsplan_opacity_legende' => '1.0'],
        ],
        'belegungsplan_reset_kategorie' => [
            'colors'    => ['belegungsplan_color_kategorie' => 'CCCCCC'],
            'opacities' => ['belegungsplan_opacity_kategorie' => '1.0'],
        ],
        'belegungsplan_reset_kategorietext' => [
            'colors'    => ['belegungsplan_color_kategorietext' => '000000'],
            'opacities' => ['belegungsplan_opacity_kategorietext' => '1.0'],
        ],
        'belegungsplan_reset_linkText' => [
            'colors'    => ['belegungsplan_color_linkText' => '6610F2'],
            'opacities' => ['belegungsplan_opacity_linkText' => '1.0'],
        ],
        'belegungsplan_reset_bg_wochenende' => [
            'colors'    => ['belegungsplan_bgcolor_wochenende' => 'CCCCCC'],
            'opacities' => ['belegungsplan_opacity_bg_wochenende' => '1.0'],
        ],
        'belegungsplan_reset_wochenendetext' => [
            'colors'    => ['belegungsplan_color_wochenendetext' => '333333'],
            'opacities' => ['belegungsplan_opacity_wochenendetext' => '1.0'],
        ],
    ];

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    #[AsCallback(table: 'tl_module', target: 'config.onload')]
    public function setDisabled(DataContainer $dc): void
    {
        // Placeholder – subpalette switching handled via submitOnChange
    }

    #[AsCallback(table: 'tl_module', target: 'fields.belegungsplan_individuellMonateEnde.save')]
    public function verifyEndDate(mixed $varValue, DataContainer $dc): mixed
    {
        if (empty($varValue)) {
            return $varValue;
        }
        $start = \Contao\Input::post('belegungsplan_individuellMonateStart');
        if (!empty($start)) {
            $arrStart = \Contao\StringUtil::deserialize($start);
            $arrEnd   = \Contao\StringUtil::deserialize($varValue);
            if (!empty($arrStart) && !empty($arrEnd)) {
                $startTs = mktime(0, 0, 0, (int)($arrStart['unit'] ?? 1), 1, (int)($arrStart['value'] ?? date('Y')));
                $endTs   = mktime(0, 0, 0, (int)($arrEnd['unit']   ?? 1), 1, (int)($arrEnd['value']   ?? date('Y')));
                if ($endTs < $startTs) {
                    throw new \RuntimeException($GLOBALS['TL_LANG']['tl_module']['sameDateError'] ?? 'End date must be after start date');
                }
            }
        }
        return $varValue;
    }

    /**
     * load_callback for color fields.
     * If a reset was requested for this field, return the stored default hex value.
     */
    public function loadColor(mixed $varValue, DataContainer $dc): mixed
    {
        $session    = $this->requestStack->getSession();
        $sessionKey = 'blp_reset_' . $dc->field;
        if ($session->has($sessionKey)) {
            $default = $session->get($sessionKey);
            $session->remove($sessionKey);
            return $default; // already hex
        }
        return $this->rgbToHex($varValue);
    }

    /**
     * load_callback for opacity fields.
     * If a reset was requested for this field, return the stored default opacity value.
     */
    public function loadOpacity(mixed $varValue, DataContainer $dc): mixed
    {
        $session    = $this->requestStack->getSession();
        $sessionKey = 'blp_reset_' . $dc->field;
        if ($session->has($sessionKey)) {
            $default = $session->get($sessionKey);
            $session->remove($sessionKey);
            return $default;
        }
        return $varValue;
    }

    /**
     * save_callback for color fields – converts hex → RGB.
     */
    public function saveColor(mixed $varValue, DataContainer $dc): mixed
    {
        return $this->hexToRgb($varValue);
    }

    /**
     * Renders a submit button for resetting fields to their default values.
     */
    public function renderResetButton(DataContainer $dc, string $xlabel): string
    {
        $label = $GLOBALS['TL_LANG']['tl_module']['reset'] ?? 'Zurücksetzen';
        return sprintf(
            '<div class="widget w25"><h3>&nbsp;</h3><button type="submit" name="%s" value="1" class="tl_submit">%s</button></div>',
            htmlspecialchars($dc->field),
            htmlspecialchars($label)
        );
    }

    /**
     * onsubmit_callback: checks which reset button was clicked and stores
     * the default values for the associated fields in the session.
     */
    #[AsCallback(table: 'tl_module', target: 'config.onsubmit')]
    public function handleResetButtons(DataContainer $dc): void
    {
        $session = $this->requestStack->getSession();
        foreach (self::RESET_MAP as $resetField => $map) {
            if (\Contao\Input::post($resetField) === '1') {
                foreach ($map['colors'] ?? [] as $field => $default) {
                    $session->set('blp_reset_' . $field, $default);
                }
                foreach ($map['opacities'] ?? [] as $field => $default) {
                    $session->set('blp_reset_' . $field, $default);
                }
            }
        }
    }

    private function rgbToHex(mixed $varValue): mixed
    {
        if (empty($varValue)) {
            return $varValue;
        }
        $arrRgb = explode(',', $varValue);
        if (count($arrRgb) !== 3) {
            return $varValue;
        }
        return sprintf('%02x%02x%02x', (int)trim($arrRgb[0]), (int)trim($arrRgb[1]), (int)trim($arrRgb[2]));
    }

    private function hexToRgb(mixed $varValue): mixed
    {
        if (empty($varValue) || strlen($varValue) !== 6) {
            return $varValue;
        }
        $r = hexdec(substr($varValue, 0, 2));
        $g = hexdec(substr($varValue, 2, 2));
        $b = hexdec(substr($varValue, 4, 2));
        return $r . ',' . $g . ',' . $b;
    }
}
