<?php declare(strict_types=1);

namespace Pyjac\Opinmona;

use Closure;

class Circuit 
{

    private $instance;

    private $method;

    public function __construct($instance, string $method)
    {
        $this->instance = $instance;
        $this->method = $method;
    }

    public function getInstance() 
    {
        return $this->instance;
    }

    public function getMethod() : string
    {
        return $this->method;
    }

    public function invoke($args) 
    {
        return call_user_func_array([$this->getInstance(), $this->getMethod()], $args);
    }


    public function getStateName(): string
    {
        return str_replace('\\', '.', get_class($this->getInstance())) . '.' . $this->method;
    }

    public static function create($instance, string $method = ''): self
    {
        $obj = null;
        if (\is_object($instance) && !$instance instanceof Closure) {
            if ($method !== '') {
                if (method_exists($instance, $method) === false) {
                    throw new \BadMethodCallException();
                }

                return new self($instance, $method);
            }

            if (\is_callable($instance)) {
                return new self($instance, '__invoke');
            }

            throw new \InvalidArgumentException("create expects an object with method name or is callable");
        }

       if (\is_callable($instance)) {
            $obj = new StdObject();
            $method = 'invoke';
            $obj->$method = $instance;
            return new self($obj, $method);
        } 
        
        throw new \InvalidArgumentException("create expects an object or a callable");
    }
}