<?php

namespace EnliteMonolog\Service;

use Zend\Stdlib\AbstractOptions;

class ErrorHandlerOptions extends AbstractOptions
{
    /** @var string|null */
    private $logger = null;

    /** @var array|boolean */
    private $errorLevelMap = array();

    /** @var mixed|null */
    private $exceptionLevel = null;

    /** @var mixed|null */
    private $fatalLevel = null;

    /**
     * @return null|string
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param null|string $logger
     * @return void
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return array|boolean
     */
    public function getErrorLevelMap()
    {
        return $this->errorLevelMap;
    }

    /**
     * @param array|boolean $errorLevelMap
     * @return void
     */
    public function setErrorLevelMap($errorLevelMap)
    {
        $this->errorLevelMap = $errorLevelMap;
    }

    /**
     * @return mixed|null
     */
    public function getExceptionLevel()
    {
        return $this->exceptionLevel;
    }

    /**
     * @param mixed|null $exceptionLevel
     * @return void
     */
    public function setExceptionLevel($exceptionLevel)
    {
        $this->exceptionLevel = $exceptionLevel;
    }

    /**
     * @return mixed|null
     */
    public function getFatalLevel()
    {
        return $this->fatalLevel;
    }

    /**
     * @param mixed|null $fatalLevel
     * @return void
     */
    public function setFatalLevel($fatalLevel)
    {
        $this->fatalLevel = $fatalLevel;
    }
}
