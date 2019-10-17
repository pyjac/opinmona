## Opinmona

Opinmona is an implementation of [Circuit Breaker](https://martinfowler.com/bliki/CircuitBreaker.html) in PHP.


###  Usage
Opinmona serves as a wrapper around methods or any callable you want to circuit break.


####  Usage for object method
```php

use Pyjac\Opinmona\CircuitBreakerFactory;
use Pyjac\Opinmona\CircuitBreakerOpenException;

class TestApi {
    public function send(string $a) {
        return $a;
    }
}

$api = new TestApi();
$cb = CircuitBreakerFactory::fromMethod($api, 'send');
try {
    $cb->invoke(2);
} catch(CircuitBreakerOpenException $e) {
    // 
}

```

####  Usage for callable class

```php

use Pyjac\Opinmona\CircuitBreakerFactory;
use Pyjac\Opinmona\CircuitBreakerOpenException;

class TestApiCallable {
    public function __invoke(string $a)
    {
        return $a;
    }
}

$api = new TestApiCallable();

$cb = CircuitBreakerFactory::fromCallable($api);

try {
    $cb->invoke(2);
} catch(CircuitBreakerOpenException $e) {
    // 
}
```


####  Usage for function

```php

use Pyjac\Opinmona\CircuitBreakerFactory;
use Pyjac\Opinmona\CircuitBreakerOpenException;

$testApiFunction = function (string $a) {
    return $a;
};

$cb = CircuitBreakerFactory::fromCallable($testApiFunction);

try {
    $cb->invoke('something');
} catch(CircuitBreakerOpenException $e) {
    // 
}
```




### Updating options and Storage

```php

use Pyjac\Opinmona\CircuitBreakerFactory;
use Pyjac\Opinmona\CircuitBreakerOpenException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;


class TestApiCallable {
    public function __invoke(string $a)
    {
        return $a;
    }
}


$options = [
    'ttl' => 5600,
    'cachePrefix' => 'my-key',
    'maxFailures' => 10,
    'retryTimeoutInSeconds' => 2,
];


// Error Check function
$errorCheck = function($response, $circuitObject)  {
    return false;
};

$api = new TestApiCallable();
$cb = CircuitBreakerFactory::fromCallable($api, $errorCheck, new ArrayAdapter(), $options);
try {
    $cb->invoke(5)
} catch(CircuitBreakerOpenException $e) {

};

```


### Default Options

| Value                 | Description                                | Default |
|-----------------------|--------------------------------------------|---------|
| ttl                   | Time-to-live for circuit state cache value | 3600    |
| cachePrefix           | Cache Prefix                               | ''      |
| maxFailures           | Max Failures                               | 3       |
| retryTimeoutInSeconds | retry Timeout In Seconds                   | 1       |