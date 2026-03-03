<?php

declare(strict_types=1);

namespace Mailwurm\BelegungsplanBundle\EventListener\DataContainer;

use Contao\Backend;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Date;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

class BelegungsplanFeiertageListener
{
    public function __construct(private readonly Connection $db)
    {
    }#[AsCallback(table: 'tl_belegungsplan_feiertage', target: 'list.label.label')]
    public function listCalender(array $arrRow, string $label): string
    {
        return Date::parse('d.m.Y', $arrRow['startDate']) . ' - ' . StringUtil::specialchars($arrRow['title']);
    }

    #[AsCallback(table: 'tl_belegungsplan_feiertage', target: 'fields.hintergrund.load')]
    public function setRgbToHexHintergrund(mixed $varValue, DataContainer $dc): mixed
    {
        return $this->rgbToHex($varValue);
    }

    #[AsCallback(table: 'tl_belegungsplan_feiertage', target: 'fields.hintergrund.save')]
    public function setHexToRgbHintergrund(mixed $varValue, DataContainer $dc): mixed
    {
        return $this->hexToRgb($varValue, $dc);
    }

    #[AsCallback(table: 'tl_belegungsplan_feiertage', target: 'fields.textcolor.load')]
    public function setRgbToHexTextcolor(mixed $varValue, DataContainer $dc): mixed
    {
        return $this->rgbToHex($varValue);
    }

    #[AsCallback(table: 'tl_belegungsplan_feiertage', target: 'fields.textcolor.save')]
    public function setHexToRgbTextcolor(mixed $varValue, DataContainer $dc): mixed
    {
        return $this->hexToRgb($varValue, $dc);
    }

    #[AsCallback(table: 'tl_belegungsplan_feiertage', target: 'fields.startDate.save')]
    public function getVorhanden(mixed $varValue, DataContainer $dc): mixed
    {
        if (!$varValue) {
            return $varValue;
        }
        $existing = $this->db->fetchOne(
            'SELECT id FROM tl_belegungsplan_feiertage WHERE startDate = ? AND id != ?',
            [$varValue, $dc->id]
        );
        if ($existing) {
            throw new \RuntimeException($GLOBALS['TL_LANG']['tl_belegungsplan_feiertage']['bereitsVorhanden'] ?? 'This date already exists');
        }
        return $varValue;
    }private function rgbToHex(mixed $varValue): mixed
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

    private function hexToRgb(mixed $varValue, DataContainer $dc): mixed
    {
        if (empty($varValue)) {
            return $varValue;
        }
        if (strlen($varValue) !== 6) {
            throw new \RuntimeException($GLOBALS['TL_LANG']['tl_belegungsplan_feiertage']['setHexToRgb'] ?? 'Invalid hex color');
        }
        $r = hexdec(substr($varValue, 0, 2));
        $g = hexdec(substr($varValue, 2, 2));
        $b = hexdec(substr($varValue, 4, 2));
        return $r . ',' . $g . ',' . $b;
    }
}
