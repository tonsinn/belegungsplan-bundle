<?php

declare(strict_types=1);

namespace Mailwurm\BelegungsplanBundle\EventListener\DataContainer;

use Contao\Backend;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Date;
use Contao\Image;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

class BelegungsplanCalenderListener
{
    public function __construct(private readonly Connection $db)
    {
    }#[AsCallback(table: 'tl_belegungsplan_calender', target: 'list.sorting.child_record')]
    public function listCalender(array $arrRow): string
    {
        $startDate = Date::parse('d.m.Y', $arrRow['startDate']);
        $endDate = Date::parse('d.m.Y', $arrRow['endDate']);
        return '<div class="cte_type published">' . $startDate . ' - ' . $endDate . '</div>
<div class="limit_height">' . StringUtil::specialchars($arrRow['gast']) .
        ($arrRow['endDate'] < $arrRow['startDate'] ? ' ' . Image::getHtml('error.svg', $GLOBALS['TL_LANG']['tl_belegungsplan_calender']['endDateListError'] ?? '', 'title="' . ($GLOBALS['TL_LANG']['tl_belegungsplan_calender']['endDateListError'] ?? '') . '"') : '') .
        ($arrRow['ueberschneidung'] ? ' ' . Image::getHtml('error_404.svg', $GLOBALS['TL_LANG']['tl_belegungsplan_calender']['ueberschneidung'][0] ?? '', 'title="' . ($GLOBALS['TL_LANG']['tl_belegungsplan_calender']['ueberschneidung'][0] ?? '') . '"') : '') .
        '</div>';
    }

    #[AsCallback(table: 'tl_belegungsplan_calender', target: 'fields.startDate.save')]
    public function setEndDate(mixed $varValue, DataContainer $dc): mixed
    {
        if ($varValue && !$dc->activeRecord?->endDate) {
            $this->db->update('tl_belegungsplan_calender', ['endDate' => $varValue], ['id' => $dc->id]);
        }
        return $varValue;
    }

    #[AsCallback(table: 'tl_belegungsplan_calender', target: 'fields.endDate.save')]
    public function loadEndDate(mixed $varValue, DataContainer $dc): mixed
    {
        if (!$varValue) {
            return $varValue;
        }
        $startDate = $dc->activeRecord?->startDate ?? 0;
        if ($varValue < $startDate) {
            throw new \RuntimeException($GLOBALS['TL_LANG']['tl_belegungsplan_calender']['endDateError'] ?? 'End date must be after start date');
        }
        if ($varValue === $startDate) {
            throw new \RuntimeException($GLOBALS['TL_LANG']['tl_belegungsplan_calender']['sameDateError'] ?? 'Start and end date must be different');
        }
        return $varValue;
    }#[AsCallback(table: 'tl_belegungsplan_calender', target: 'config.onsubmit')]
    public function loadUeberschneidung(DataContainer $dc): void
    {
        if (!$dc->id) {
            return;
        }

        $current = $this->db->fetchAssociative(
            'SELECT id, pid, startDate, endDate FROM tl_belegungsplan_calender WHERE id = ?',
            [$dc->id]
        );

        if (!$current) {
            return;
        }

        // Reset overlap flag for current record
        $this->db->update('tl_belegungsplan_calender', ['ueberschneidung' => ''], ['id' => $dc->id]);

        // Find overlapping records
        $overlapping = $this->db->fetchAllAssociative(
            'SELECT id FROM tl_belegungsplan_calender
             WHERE pid = ? AND id != ? AND startDate < ? AND endDate > ?',
            [$current['pid'], $current['id'], $current['endDate'], $current['startDate']]
        );

        if (!empty($overlapping)) {
            $this->db->update('tl_belegungsplan_calender', ['ueberschneidung' => '1'], ['id' => $dc->id]);
            foreach ($overlapping as $row) {
                $this->db->update('tl_belegungsplan_calender', ['ueberschneidung' => '1'], ['id' => $row['id']]);
            }
        } else {
            // Check if this record was previously overlapping with others - recheck them
            $siblings = $this->db->fetchAllAssociative(
                'SELECT id, startDate, endDate FROM tl_belegungsplan_calender WHERE pid = ? AND id != ?',
                [$current['pid'], $current['id']]
            );

            foreach ($siblings as $sibling) {
                $siblingOverlap = $this->db->fetchAllAssociative(
                    'SELECT id FROM tl_belegungsplan_calender
                     WHERE pid = ? AND id != ? AND startDate < ? AND endDate > ?',
                    [$current['pid'], $sibling['id'], $sibling['endDate'], $sibling['startDate']]
                );
                $this->db->update('tl_belegungsplan_calender',
                    ['ueberschneidung' => empty($siblingOverlap) ? '' : '1'],
                    ['id' => $sibling['id']]
                );
            }
        }
    }

    #[AsCallback(table: 'tl_belegungsplan_calender', target: 'config.ondelete')]
    public function calenderOndeleteCallback(DataContainer $dc): void
    {
        if (!$dc->id) {
            return;
        }

        $current = $this->db->fetchAssociative(
            'SELECT pid FROM tl_belegungsplan_calender WHERE id = ?',
            [$dc->id]
        );

        if (!$current) {
            return;
        }

        $siblings = $this->db->fetchAllAssociative(
            'SELECT id, startDate, endDate FROM tl_belegungsplan_calender WHERE pid = ? AND id != ?',
            [$current['pid'], $dc->id]
        );

        foreach ($siblings as $sibling) {
            $siblingOverlap = $this->db->fetchAllAssociative(
                'SELECT id FROM tl_belegungsplan_calender
                 WHERE pid = ? AND id != ? AND startDate < ? AND endDate > ?',
                [$current['pid'], $sibling['id'], $sibling['endDate'], $sibling['startDate']]
            );
            $this->db->update('tl_belegungsplan_calender',
                ['ueberschneidung' => empty($siblingOverlap) ? '' : '1'],
                ['id' => $sibling['id']]
            );
        }
    }
}
