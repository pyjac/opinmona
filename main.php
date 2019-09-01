<?php

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



$api = new TestApi();
$e = \Exception::class;

$cb = CircuitBreakerFactory::fromMethod($api, 'send', $e);
var_dump($cb->invoke(2));
var_dump($cb->invoke(2));
var_dump($cb->invoke(2));
var_dump($cb->invoke(2));

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

