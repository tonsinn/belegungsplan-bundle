<?php

declare(strict_types=1);

namespace Mailwurm\BelegungsplanBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Image;
use Contao\StringUtil;
use Contao\Backend;

class BelegungsplanCategoryListener
{
    #[AsCallback(table: 'tl_belegungsplan_category', target: 'list.operations.editheader.button')]
    public function editHeader(array $row, string $href, string $label, string $title, string $icon, string $attributes): string
    {
        return sprintf(
            '<a href="%s" title="%s"%s>%s</a> ',
            Backend::addToUrl($href . '&amp;id=' . $row['id']),
            StringUtil::specialchars($title),
            $attributes,
            Image::getHtml($icon, $label)
        );
    }

    #[AsCallback(table: 'tl_belegungsplan_category', target: 'list.operations.delete.button')]
    public function deleteCategory(array $row, string $href, string $label, string $title, string $icon, string $attributes): string
    {
        return sprintf(
            '<a href="%s" title="%s"%s>%s</a> ',
            Backend::addToUrl($href . '&amp;id=' . $row['id']),
            StringUtil::specialchars($title),
            $attributes,
            Image::getHtml($icon, $label)
        );
    }
}
