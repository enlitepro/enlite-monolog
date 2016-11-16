<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonolog\Service;


use Zend\Stdlib\AbstractOptions;

class MonologOptions extends AbstractOptions
{

    /**
     * Logger name
     *
     * @var string
     */
    protected $name = 'EnliteMonolog';

    /**
     * Handlers
     *
     * @var array
     */
    protected $handlers = array();

    /**
     * @var array
     */
    protected $processors = array();

    /**
     * Register logger as error, exception, and fatal handler.
     *
     * @var bool
     */
    protected $isErrorHandler = false;

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param array $handlers
     */
    public function setHandlers(array $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * @return array
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * @param array $processors
     */
    public function setProcessors($processors = array())
    {
        $this->processors = $processors;
    }

    /**
     * @return array
     */
    public function getProcessors()
    {
        return $this->processors;
    }

    /**
     * @return boolean
     */
    public function isErrorHandler()
    {
        return $this->isErrorHandler;
    }

    /**
     * @param boolean $isErrorHandler
     * @return void
     */
    public function setIsErrorHandler($isErrorHandler)
    {
        $this->isErrorHandler = (bool) $isErrorHandler;
    }
}