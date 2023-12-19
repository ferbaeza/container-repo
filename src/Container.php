<?php

namespace Src;

/**
 * Container Class
 */
class Container
{
    /**
     * bindings
     * @var array
     */
    private array $bindings = [];

    /**
     * set function
     * @param  string $nombre
     * @param  mixed $resolver
     * @return void
     */
    public function set(string $nombre, $resolver)
    {
        if (!array_key_exists($nombre, $this->bindings)) {
            $this->bindings[$nombre] = $resolver;
        }
    }

    /**
     * get function
     * @param  string $nombre
     * @return mixed
     */
    public function get($name)
    {
        if (!isset($this->bindings[$name])) {
            throw new \Exception("Target binding [$name] does not exist.");
        }
        $factory = $this->bindings[$name];
        return $factory($this);
    }

    /**
     * getAll function
     * @return void
     */
    public function getAll()
    {
        return $this->bindings;
    }

    /**
     * build function
     * @param  string $class
     */
    public function build(string $class)
    {
        try {
            $reflection = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new \Exception("Class {$class} not found", 0, $e);
        }

        if (!$reflection->isInstantiable()) {
            throw new \Exception("Class {$class} is not instantiable");
        }

        $constructor = $reflection->getConstructor();
        if (is_null($constructor)) {
            return new $class;
        }

        $parameters = $constructor->getParameters();
        $dependencias = [];
        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencias[] = $parameter->getDefaultValue();
                } else
                if ($parameter->isVariadic()) {
                    $dependencias[] = [];
                } else {
                    throw new \Exception("Cannot resolve class {$class}");
                }
            }
            $dependencia = null; 
            $name = $type->getName();
            try {
                $dependencia = $this->get($name);
                $dependencias[] = $dependencia;
            } catch (\Exception $e) {
                if ($parameter->isOptional()) {
                    $dependencias[] = $parameter->getDefaultValue();
                } else {
                    $dependencia =  $this->build($parameter->getType()->getName());
                    $this->set($name, $dependencia);
                }
                $dependencias[] = $dependencia;
            }
        }
        return $reflection->newInstanceArgs($dependencias);
    }
}
