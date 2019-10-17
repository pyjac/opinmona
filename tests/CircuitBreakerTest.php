<?php declare(strict_types=1);

namespace Pyjac\Opinmona\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Pyjac\Opinmona\CircuitBreaker;
use Pyjac\Opinmona\Circuit;
use Pyjac\Opinmona\CircuitStateStorage;
use Pyjac\Opinmona\CircuitBreakerOpenException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CircuitBreakerTest extends TestCase {

    /** @var CircuitBreaker  */
    private $circuitBreaker;
    /** @var CircuitStateStorage */
    private $storage;

    private $maxFailures = 20;

    private $retryTimeout = 60;

    private $testObject;

    /** @var Closure|string */
    private $onError;

    /** @var Circuit */
    private $circuit;

    protected function setUp(): void
    {
        $this->circuit = $this->createMock(Circuit::class);
        $this->storage = $this->createMock(CircuitStateStorage::class);
        $this->onError = Exception::class;
        $this->circuitBreaker = $this->getMockBuilder(CircuitBreaker::class)
                     ->setMethods(['getStorage'])
                     ->setConstructorArgs([$this->circuit, $this->onError, new ArrayAdapter(), $this->maxFailures, $this->retryTimeout])
                     ->getMock();

        $this->circuitBreaker->method('getStorage')->willReturn($this->storage);
    }

    public function testCircuitBreakerThrowsCircuitBreakerOpenExceptionWhenNotAvailable()
    {
        $this->expectException(CircuitBreakerOpenException::class);

        $lastTest = time() + ($this->retryTimeout * 2);
        $this->storage->method('getFailures')->willReturn($this->maxFailures + 1);
        $this->storage->method('getLastTest')->willReturn($lastTest);
        $this->circuitBreaker->invoke();
    }


    public function testCircuitBreakerDecreaseFailuresCountWhenNoErrorAndFailureCountGreaterThanZeroButLessThanMaxFailures()
    {
        $this->storage->method('getFailures')->willReturn($this->maxFailures - 1);
        $this->storage
             ->expects($this->once())
             ->method('decreaseFailuresCount');
        $this->storage
             ->expects($this->once())
             ->method('setLastTest');

        $this->circuitBreaker->invoke();
    }

    public function testCircuitBreakerIncreaseFailuresCountWhenErrorIsException()
    {
        $this->circuit->method('invoke')
        ->willThrowException(new Exception());

        $this->storage
             ->expects($this->once())
             ->method('increaseFailuresCount');

        $this->storage
             ->expects($this->once())
             ->method('setLastTest');

        $this->circuitBreaker->invoke();
    }

    public function testCircuitBreakerIncreaseFailuresCountWhenErrorIsResolvedWithCallback()
    {
        $errorCheck = function($response, $circuitObject)  {
            return true;
        };

        $circuitBreaker = $this->getMockBuilder(CircuitBreaker::class)
                     ->setMethods(['getStorage'])
                     ->setConstructorArgs([$this->circuit, $errorCheck, new ArrayAdapter(), $this->maxFailures, $this->retryTimeout])
                     ->getMock();
        
        $circuitBreaker->method('getStorage')->willReturn($this->storage);


        $this->storage
             ->expects($this->once())
             ->method('increaseFailuresCount');

        $this->storage
             ->expects($this->once())
             ->method('setLastTest');

        $circuitBreaker->invoke();
    }
}