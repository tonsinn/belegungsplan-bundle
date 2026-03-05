<?php

declare(strict_types=1);

namespace Tonsinn\BelegungsplanBundle\EventListener\DataContainer;

use Contao\Backend;
use Contao\Config;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Date;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

class BelegungsplanObjekteListener
{
    public function __construct(private readonly Connection $db)
    {
    }#[AsCallback(table: 'tl_belegungsplan_objekte', target: 'list.sorting.child_record')]
    public function listQuestions(array $arrRow): string
    {
        $key = $arrRow['published'] ? 'published' : 'unpublished';
        $date = Date::parse(Config::get('datimFormat'), $arrRow['tstamp']);
        return '<div class="cte_type ' . $key . '">' . $date . '</div>
<div class="limit_height">' . StringUtil::specialchars($arrRow['name']) .
        (!empty($arrRow['infotext']) ? '<span style="color:#b3b3b3;padding-left:3px">[' . StringUtil::specialchars($arrRow['infotext']) . ']</span>' : '') .
        '</div>';
    }

    #[AsCallback(table: 'tl_belegungsplan_objekte', target: 'list.operations.editheader.button')]
    public function editHeader(array $row, string $href, string $label, string $title, string $icon, string $attributes): string
    {
        return sprintf('<a href="%s" title="%s"%s>%s</a> ',
            Backend::addToUrl($href . '&amp;id=' . $row['id']),
            StringUtil::specialchars($title),
            $attributes,
            Image::getHtml($icon, $label)
        );
    }

    #[AsCallback(table: 'tl_belegungsplan_objekte', target: 'list.operations.toggle.button')]
    public function toggleIcon(array $row, ?string $href, string $label, string $title, string $icon, string $attributes): string
    {
        if (Input::get('tid')) {
            $this->toggleVisibility((int) Input::get('tid'), Input::get('state') === '1');
            Backend::redirect(Backend::getReferer());
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);
        if (!$row['published']) {
            $icon = 'invisible.svg';
        }

        return sprintf('<a href="%s" title="%s"%s>%s</a> ',
            Backend::addToUrl($href),
            StringUtil::specialchars($title),
            $attributes,
            Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"')
        );
    }
#[AsCallback(table: 'tl_belegungsplan_objekte', target: 'fields.cssID.save')]
    public function setEmptyCssID(mixed $varValue, DataContainer $dc): mixed
    {
        $arrSet = StringUtil::deserialize(Input::post('cssID'));
        if (empty($arrSet[0]) && empty($arrSet[1])) {
            $varValue = '';
        }
        return $varValue;
    }

    private function toggleVisibility(int $intId, bool $blnVisible): void
    {
        $this->db->update('tl_belegungsplan_objekte', [
            'tstamp'    => time(),
            'published' => $blnVisible ? '1' : '',
        ], ['id' => $intId]);
    }
}
