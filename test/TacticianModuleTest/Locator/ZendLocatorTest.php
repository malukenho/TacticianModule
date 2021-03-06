<?php
namespace TacticianModuleTest\Locator;

use League\Tactician\Exception\MissingHandlerException;
use TacticianModule\Locator\ZendLocator;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\Exception\ServiceNotFoundException;

class ZendLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $serviceLocator;

    /**
     * @var ZendLocator
     */
    protected $locator;

    public function setUp()
    {
        $this->serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();

        $this->locator = new ZendLocator();
        $this->locator->setServiceLocator($this->serviceLocator);
    }

    public function testGetHandlerForCommandShouldThrowExceptionOnMissingCommandHandler()
    {
        $this->serviceLocator->expects($this->once())
            ->method('get')
            ->with($this->equalTo('config'))
            ->will($this->returnValue([
                'tactician' => [
                    'handler-map' => []
                ],
            ]));

        $this->setExpectedException(MissingHandlerException::class);
        $this->locator->getHandlerForCommand('command');
    }

    public function testGetHandlerForCommandShouldThrowExceptionOnMissingServiceNameAndMissingClass()
    {
        $this->serviceLocator->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('config'))
            ->will($this->returnValue([
                'tactician' => [
                    'handler-map' => [
                        'command' => 'handler',
                    ]
                ],
            ]));

        $this->serviceLocator->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('handler'))
            ->will($this->throwException(new ServiceNotFoundException));

        $this->setExpectedException(MissingHandlerException::class);
        $this->locator->getHandlerForCommand('command');
    }

    public function testGetHandlerForCommandShouldAllowFQCN()
    {
        $this->serviceLocator->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('config'))
            ->will($this->returnValue([
                'tactician' => [
                    'handler-map' => [
                        'command' => \stdClass::class,
                    ]
                ],
            ]));

        $this->serviceLocator->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo(\stdClass::class))
            ->will($this->throwException(new ServiceNotFoundException));

        $this->assertInstanceOf(\stdClass::class, $this->locator->getHandlerForCommand('command'));
    }

    public function testGetHandlerForCommandShouldThrowExceptionWhenServiceLocatorReturnsNonObject()
    {
        $this->serviceLocator->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('config'))
            ->will($this->returnValue([
                'tactician' => [
                    'handler-map' => [
                        'command' => 'handler',
                    ]
                ],
            ]));

        $this->serviceLocator->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('handler'))
            ->will($this->returnValue([]));

        $this->setExpectedException(MissingHandlerException::class);
        $this->locator->getHandlerForCommand('command');
    }
}
