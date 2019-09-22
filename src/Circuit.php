<?php declare(strict_types=1);

namespace Pyjac\Opinmona;


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
        if (\is_object($instance)) {
            if ($method !== '') {

                return new self($instance, $method);
            }

            if (\is_callable($instance)) {
                $obj = new StdObject();
                $obj->invoke = $instance;
    
                return new self($obj, 'invoke');
            }
        }

        if (\is_callable($instance)) {
            $obj = new StdObject();
            $obj->invoke = $instance;

            return new self($obj, 'invoke');
        }

        throw new \Exception("Something is wrong");
    }
}