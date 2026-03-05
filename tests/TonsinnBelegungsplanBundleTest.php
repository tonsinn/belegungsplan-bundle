<?php
/*
* This file is part of Contao.
*
* Copyright (c) 2017 Jan Karai
*
* @license LGPL-3.0-or-later
*/
namespace Tonsinn\BelegungsplanBundle\Tests;
use Tonsinn\BelegungsplanBundle\TonsinnBelegungsplanBundle;
use PHPUnit\Framework\TestCase;
/**
* Tests the TonsinnBelegungsplanBundle class.
*
* @author Jan Karai <http://www.sachsen-it.de>
*/
class TonsinnBelegungsplanBundleTest extends TestCase {
    /**
    * Tests the object instantiation.
    */
    public function testCanBeInstantiated() {
        $bundle = new TonsinnBelegungsplanBundle();
        $this->assertInstanceOf('Tonsinn\BelegungsplanBundle\TonsinnBelegungsplanBundle', $bundle);
    }
}
