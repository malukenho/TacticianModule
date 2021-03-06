<?php
namespace TacticianModule\Locator;

use League\Tactician\Exception\MissingHandlerException;
use League\Tactician\Handler\Locator\HandlerLocator;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class ZendLocator implements HandlerLocator, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Retrieves the handler for a specified command
     *
     * @param string $commandName
     *
     * @return object
     *
     * @throws MissingHandlerException
     */
    public function getHandlerForCommand($commandName)
    {
        $handlerMap = $this->getServiceLocator()->get('config')['tactician']['handler-map'];

        if (!isset($handlerMap[$commandName])) {
            throw MissingHandlerException::forCommand($commandName);
        }

        $serviceNameOrFQCN = $handlerMap[$commandName];

        try {
            $handler = $this->getServiceLocator()->get($serviceNameOrFQCN);
            if (is_object($handler)) {
                return $handler;
            }
        } catch (ServiceNotFoundException $e) {
            // Further check exists for class availability.
            // If not, Exception will be thrown anyway.
        }

        if (class_exists($serviceNameOrFQCN)) {
            return new $serviceNameOrFQCN();
        }

        throw MissingHandlerException::forCommand($commandName);
    }
}
