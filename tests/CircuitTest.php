<?php declare(strict_types=1);

namespace Pyjac\Opinmona\Tests;

use PHPUnit\Framework\TestCase;
use Pyjac\Opinmona\Circuit;
use BadMethodCallException;
use InvalidArgumentException;

class CircuitTest extends TestCase {

    public function testCreateWithObject()
    {
        $testInstance = new class {
        
            public function testMethod() : int
            { 
                return 1;
            }
        };

        $circuit = Circuit::create($testInstance, 'testMethod');

        $this->assertSame('testMethod', $circuit->getMethod());
        $this->assertSame($testInstance, $circuit->getInstance());
        
    }

    public function testCreateWithObjectWithUndefinedMethodThrowsBadMethodCallException()
    {
        $this->expectException(BadMethodCallException::class);

        $testInstance = new class {
        
            public function __invoke() : int
            { 
                return 1;
            }
        };

        $circuit = Circuit::create($testInstance, 'testMethod');
        
    }

    public function testCreateWithCallableObject()
    {
        $testInstance = new class {
        
            public function __invoke() : int
            { 
                return 1;
            }
        };

        $circuit = Circuit::create($testInstance);
        $this->assertSame('__invoke', $circuit->getMethod());
        $this->assertSame($testInstance, $circuit->getInstance());
    }

    public function testCreateWithFunction()
    {
        $testFunc =  function() : int
            { 
                return 1;
            };

        $circuit = Circuit::create($testFunc);
        $this->assertSame('invoke', $circuit->getMethod());
    }


    public function testCreateThrowsInvalidArgumentExceptionWhenObjectNotCallableAndWithUndefinedMethodIsProvided()
    {

        $this->expectExceptionMessage('create expects an object with method name or is callable');
        $this->expectException(InvalidArgumentException::class);


        $testInstance = new class {
        
            public function invoke() : int
            { 
                return 1;
            }
        };

       Circuit::create($testInstance);
    }

    public function testCreateThrowsInvalidArgumentExceptionWhenNonObjectIsProvided()
    {

        $this->expectExceptionMessage('create expects an object or a callable');
        $this->expectException(InvalidArgumentException::class);

        Circuit::create(1);
    }
}