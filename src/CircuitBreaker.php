<?php declare(strict_types=1);

namespace Pyjac\Opinmona;

use Symfony\Component\Cache\Adapter\AdapterInterface;

class CircuitBreaker
{
    /** @var CircuitStateStorage */
    private $storage;

    /** @var int */
    private $maxFailures;

    /** @var int */
    private $retryTimeout;

    public function __construct($circuit, $error, AdapterInterface $storage, int $maxFailures = 20, int $retryTimeout = 60)
    {
        $this->circuit = $circuit;
        $this->error = $error;
        $this->storage = new CircuitStateStorage($circuit->getStateName(), $storage);
        $this->maxFailures = $maxFailures;
        $this->retryTimeout = $retryTimeout;
    }

    public function invoke(...$args) 
    {
        if ($this->isAvailable() === false) {
            throw new CircuitBreakerOpenException();
        }
        
        $response = null;
        
        if (is_callable($this->error)) {
            $response = $this->circuit->invoke($args);
            $isError = call_user_func_array($this->error, [$response, $this->circuit->getInstance()]);
            if ($isError) {
                $this->reportFailure();
            } else {
                $this->reportSuccess();
            }
        } else if (is_string($this->error)) {
            try {
                $response = $this->circuit->invoke($args);
                $this->reportSuccess();
            }
            catch (\Exception $e)
            {
                switch (get_class($e))
                {
                    case $this->error:
                        $this->reportFailure();
                        break;
                    default:
                        throw new $e;
                }
            }
        }

        return $response;
    }

    protected function setFailures($value)
    {
        $this->storage->setFailures($value); 
        $this->storage->setLastTest(time());
    }

    private function isAvailable(): bool
    {
        return $this->isClosed() || $this->isHalfOpen();
    }

    public function isClosed(): bool
    {
        $failures = $this->storage->getFailures();
        $maxFailures = $this->maxFailures;
        
        return $failures < $maxFailures;
    }

    private function isHalfOpen(): bool
    {
        $lastFailureTime = $this->storage->getLastTest();
        if ($lastFailureTime == 0) {
            return false;
        }

        if ((time() - $lastFailureTime) > $this->retryTimeout) {
            $this->storage->setFailures($this->maxFailures);
            $this->storage->setLastTest(time());

            return true;
        }

        return false;
    }

    private function reportFailure()
    {
        $this->storage->increaseFailuresCount();
        $this->storage->setLastTest(time());
    }

    private function reportSuccess()
    {
        $failures = $this->storage->getFailures();
        $maxFailures = $this->maxFailures;
        if ($failures > $maxFailures) {
            // there were more failures than max failures
            // we have to reset failures count to max-1
            $this->storage->setFailures($maxFailures - 1);
        } elseif ($failures > 0) {
            // if we are between max and 0 we decrease by 1 on each
            // success so we will go down to 0 after some time
            // but we are still more sensitive to failures
            $this->storage->decreaseFailuresCount();
        }
        $this->storage->setLastTest(time());
        
        // if there are no failures reported we do not
        // have to do anything on success (system operational)
    }
}
