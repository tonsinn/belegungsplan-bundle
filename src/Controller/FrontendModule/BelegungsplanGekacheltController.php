<?php

declare(strict_types=1);

namespace Tonsinn\BelegungsplanBundle\Controller\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;

#[AsFrontendModule('belegungsplan_gekachelt', category: 'belegung', template: 'mod_belegungsplan_bootstrap')]
class BelegungsplanGekacheltController extends BelegungsplanController
{
}
