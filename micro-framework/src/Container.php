<?php


namespace App;

class Container
{
   

    /** @var array<string, callable> Recettes de création */
    private array $factories = [];


    /** @var array<string, object> Cache des instances (singleton) */
    private array $instances = [];


    public function set(string $name, callable $factory): void
    {
        $this->factories[$name] = $factory;
    }

    

    public function get(string $name): object
    {
        
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        
        if (!isset($this->factories[$name])) {
            throw new \RuntimeException("Service inconnu : $name");
        }

      
        $this->instances[$name] = ($this->factories[$name])($this);
        return $this->instances[$name];
    }
    public function has(string $name): bool
    {
        return isset($this->factories[$name]) || isset($this->instances[$name]);
    }
}
