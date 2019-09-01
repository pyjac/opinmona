<?php declare(strict_types=1);

namespace Pyjac\Opinmona;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CircuitBreakerFactory
{

    public static function fromMethod($instance, string $method, $error = \Exception::class, AdapterInterface $storage = null, $options = []) : CircuitBreaker
    {
        $options = self::mergeDefaultOptions($options);
        $circuit = Circuit::create($instance, $method);
        return self::create($circuit, $error, $storage, $options);
    }

    public static function fromCallable(Callable $instance, $error = \Exception::class, AdapterInterface $storage = null, $options = []) : CircuitBreaker
    {
        $options = self::mergeDefaultOptions($options);
        $circuit = Circuit::create($instance);
        return self::create($circuit, $error, $storage, $options);
    }

    private static function create($circuit, $error, AdapterInterface $storage = null, array $options) : CircuitBreaker
    {
        if (is_null($storage)) {
            $storage = new ArrayAdapter();
        }
        
        return new CircuitBreaker($circuit, $error, $storage, $options['maxFailures'], $options['retryTimeoutInSeconds']);
    }

    private static function mergeDefaultOptions(array $options) : array
    {
        return array_merge([
            'ttl' => 3600,
            'cachePrefix' => '',
            'maxFailures' => 3,
            'retryTimeoutInSeconds' => 1,
        ], $options);
    }
}