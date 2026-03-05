<?php

declare(strict_types=1);

namespace Tonsinn\BelegungsplanBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class TonsinnBelegungsplanBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
