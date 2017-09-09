<?php

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologOptions;

/**
 * @covers \EnliteMonolog\Service\MonologOptions
 */
class MonologOptionsTest extends \PHPUnit_Framework_TestCase
{
    /** @var MonologOptions */
    private $sut;

    protected function setUp()
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

        self::assertInternalType('array', $actual);
        self::assertCount(0, $actual);
    }
    
    public function testSetHandlers()
    {
        $this->sut->setHandlers(array(
            $expected = 'MyHandlerService',
        ));

        self::assertInternalType('array', $this->sut->getHandlers());
        self::assertContains($expected, $this->sut->getHandlers());
    }
    
    public function testGetDefaultProcessors()
    {
        $actual = $this->sut->getProcessors();

        self::assertInternalType('array', $actual);
        self::assertCount(0, $actual);
    }
    
    public function testSetProcessors()
    {
        $this->sut->setProcessors(array(
            $expected = 'MyProcessorService',
        ));

        self::assertInternalType('array', $this->sut->getProcessors());
        self::assertContains($expected, $this->sut->getProcessors());
    }
}
