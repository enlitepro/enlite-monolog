<?php

namespace EnliteMonologTest\Module;

use EnliteMonolog\Module;
use PHPUnit\Framework\TestCase;

/**
 * @covers \EnliteMonolog\Module
 */
final class ModuleTest extends TestCase
{
    /** @var Module */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new Module();
    }

    public function testGetConfig(): void
    {
        $actual = $this->sut->getConfig();

        self::assertIsArray($actual);
    }

    public function testConfigIsSerializable(): void
    {
        self::assertIsArray(unserialize(serialize($this->sut->getConfig())));
    }
}
