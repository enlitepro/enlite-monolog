<?php

namespace EnliteMonologTest\Module;

use EnliteMonolog\Module;
use PHPUnit\Framework\TestCase;

/**
 * @covers \EnliteMonolog\Module
 */
class ModuleTest extends TestCase
{
    /** @var Module */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new Module();
    }
    
    public function testGetAutoloaderConfig()
    {
        $actual = $this->sut->getAutoloaderConfig();
        
        self::assertInternalType('array', $actual);
    }
    
    public function testAutoloaderConfigIsSerializable()
    {
        self::assertInternalType('array', unserialize(serialize($this->sut->getAutoloaderConfig())));
    }
    
    public function testGetConfig()
    {
        $actual = $this->sut->getConfig();
        
        self::assertInternalType('array', $actual);
    }
    
    public function testConfigIsSerializable()
    {
        self::assertInternalType('array', unserialize(serialize($this->sut->getConfig())));
    }
}
