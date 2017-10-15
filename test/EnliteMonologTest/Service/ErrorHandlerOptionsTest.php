<?php

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\ErrorHandlerOptions;

/**
 * @covers \EnliteMonolog\Service\ErrorHandlerOptions
 */
class ErrorHandlerOptionsTest extends \PHPUnit_Framework_TestCase
{
    /** @var ErrorHandlerOptions */
    private $sut;

    protected function setUp()
    {
        $this->sut = new ErrorHandlerOptions();
    }

    public function testGetLogger()
    {
        self::assertNull($this->sut->getLogger());

        $this->sut->setLogger('FooBar');

        self::assertSame('FooBar', $this->sut->getLogger());
    }

    public function testDisableLogger()
    {
        $this->sut->setLogger(false);

        self::assertFalse($this->sut->getLogger());
    }

    public function testGetErrorLevelMap()
    {
        self::assertCount(0, $this->sut->getErrorLevelMap());

        $this->sut->setErrorLevelMap(array(
            \E_ERROR => 'critical',
        ));

        self::assertSame(array(\E_ERROR => 'critical'), $this->sut->getErrorLevelMap());
    }

    public function testDisableErrorHandler()
    {
        $this->sut->setErrorLevelMap(false);

        self::assertFalse($this->sut->getErrorLevelMap());
    }

    public function testGetExceptionLevel()
    {
        self::assertNull($this->sut->getExceptionLevel());

        $this->sut->setExceptionLevel('critical');

        self::assertSame('critical', $this->sut->getExceptionLevel());
    }

    public function testDisableExceptionHandler()
    {
        $this->sut->setExceptionLevel(false);

        self::assertFalse($this->sut->getExceptionLevel());
    }

    public function testGetFatalLevel()
    {
        self::assertNull($this->sut->getFatalLevel());

        $this->sut->setFatalLevel('critical');

        self::assertSame('critical', $this->sut->getFatalLevel());
    }

    public function testDisableFatalHandler()
    {
        $this->sut->setFatalLevel(false);

        self::assertFalse($this->sut->getFatalLevel());
    }
}
