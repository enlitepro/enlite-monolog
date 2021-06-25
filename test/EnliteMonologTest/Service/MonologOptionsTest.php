<?php

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologOptions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \EnliteMonolog\Service\MonologOptions
 */
class MonologOptionsTest extends TestCase
{
    /** @var MonologOptions */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new MonologOptions();
    }
    
    public function testGetDefaultName()
    {
        self::assertEquals('EnliteMonolog', $this->sut->getName());
    }
    
    public function testSetName()
    {
        $this->sut->setName($expected = 'MyLogger');
        
        self::assertEquals($expected, $this->sut->getName());
    }
    
    public function testGetDefaultHandlers()
    {
        $actual = $this->sut->getHandlers();

        self::assertIsArray($actual);
        self::assertCount(0, $actual);
    }
    
    public function testSetHandlers()
    {
        $this->sut->setHandlers(array(
            $expected = 'MyHandlerService',
        ));

        self::assertIsArray($this->sut->getHandlers());
        self::assertContains($expected, $this->sut->getHandlers());
    }
    
    public function testGetDefaultProcessors()
    {
        $actual = $this->sut->getProcessors();

        self::assertIsArray($actual);
        self::assertCount(0, $actual);
    }
    
    public function testSetProcessors()
    {
        $this->sut->setProcessors(array(
            $expected = 'MyProcessorService',
        ));

        self::assertIsArray($this->sut->getProcessors());
        self::assertContains($expected, $this->sut->getProcessors());
    }
}
