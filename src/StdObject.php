<?php declare(strict_types=1);

namespace Pyjac\Opinmona;


// https://www.php.net/manual/en/language.types.object.php#114442
class StdObject 
{
    public function __construct(array $arguments = []) 
    {
        if (!empty($arguments)) {
            foreach ($arguments as $property => $argument) {
                $this->{$property} = $argument;
            }
        }
    }

    public function __call($method, $arguments) 
    {
        if (isset($this->{$method}) && is_callable($this->{$method})) {
            return call_user_func_array($this->{$method}, $arguments);
        } 
        
        throw new \Exception("Fatal error: Call to undefined method stdObject::{$method}()");
    }
}