## Opinmona


Circuit Breaker


### Sample Usage
```php

require __DIR__ . '/vendor/autoload.php';

use Pyjac\Opinmona\CircuitBreakerFactory;


class TestApi {
    public function send(string $a) {
        return $a;
    }
}

class TestApiCallable {
    public function __invoke(string $a)
    {
        return $a;
    }
}

$testApiFunction = function (string $a){
    return $a;
};


// Class method
$api = new TestApi();
$e = \Exception::class;

$cb = CircuitBreakerFactory::fromMethod($api, 'send', $e);
var_dump($cb->invoke(2));
var_dump($cb->invoke(2));
var_dump($cb->invoke(2));
var_dump($cb->invoke(2));

// Error Check function
$fe = function() {
    return false;
};

$api = new TestApiCallable();
$cb = CircuitBreakerFactory::fromCallable($api, $fe);
var_dump($cb->invoke(5));
var_dump($cb->invoke(2));


$cb = CircuitBreakerFactory::fromCallable($testApiFunction, $fe);
var_dump($cb->invoke(50));
var_dump($cb->invoke(20));



// updating options and Storage
use Symfony\Component\Cache\Adapter\ArrayAdapter;

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
var_dump($cb->invoke(5));
var_dump($cb->invoke(2));

```


### Default Options

| Value                 | Description                                | Default |
|-----------------------|--------------------------------------------|---------|
| ttl                   | Time-to-live for circuit state cache value | 3600    |
| cachePrefix           | Cache Prefix                               | ''      |
| maxFailures           | Max Failures                               | 3       |
| retryTimeoutInSeconds | retry Timeout In Seconds                   | 1       |