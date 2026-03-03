<?php

declare(strict_types=1);

namespace Mailwurm\BelegungsplanBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;

class BelegungsplanModuleListener
{
    #[AsCallback(table: 'tl_module', target: 'config.onload')]
    public function setDisabled(DataContainer $dc): void
    {
        // Placeholder - subpalette switching handled via submitOnChange
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
            $arrEnd = \Contao\StringUtil::deserialize($varValue);
            if (!empty($arrStart) && !empty($arrEnd)) {
                $startTs = mktime(0, 0, 0, (int)($arrStart['unit'] ?? 1), 1, (int)($arrStart['value'] ?? date('Y')));
                $endTs = mktime(0, 0, 0, (int)($arrEnd['unit'] ?? 1), 1, (int)($arrEnd['value'] ?? date('Y')));
                if ($endTs < $startTs) {
                    throw new \RuntimeException($GLOBALS['TL_LANG']['tl_module']['belegungsplan_individuellMonateEnde_error'] ?? 'End date must be after start date');
                }
            }
        }
        return $varValue;
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
public function loadColor(mixed $varValue, DataContainer $dc): mixed
    {
        return $this->rgbToHex($varValue);
    }

    public function saveColor(mixed $varValue, DataContainer $dc): mixed
    {
        return $this->hexToRgb($varValue);
    }
}
