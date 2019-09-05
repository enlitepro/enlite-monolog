<?php

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologOptions;
use Monolog\Test\TestCase;

/**
 * @covers \EnliteMonolog\Service\MonologOptions
 */
final class MonologOptionsTest extends TestCase
{
    /** @var MonologOptions */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new MonologOptions();
    }

    public function testGetDefaultName(): void
    {
        self::assertEquals('EnliteMonolog', $this->sut->getName());
    }

    public function testSetName(): void
    {
        $this->sut->setName($expected = 'MyLogger');

        self::assertEquals($expected, $this->sut->getName());
    }

    public function testGetDefaultHandlers(): void
    {
        $actual = $this->sut->getHandlers();

        self::assertIsArray($actual);
        self::assertCount(0, $actual);
    }

    public function testSetHandlers(): void
    {
        $this->sut->setHandlers([
            $expected = 'MyHandlerService',
        ]);

        self::assertIsArray($this->sut->getHandlers());
        self::assertContains($expected, $this->sut->getHandlers());
    }

    public function testGetDefaultProcessors(): void
    {
        $actual = $this->sut->getProcessors();

        self::assertIsArray($actual);
        self::assertCount(0, $actual);
    }

    public function testSetProcessors(): void
    {
        $this->sut->setProcessors([
            $expected = 'MyProcessorService',
        ]);

        self::assertIsArray($this->sut->getProcessors());
        self::assertContains($expected, $this->sut->getProcessors());
    }
}
