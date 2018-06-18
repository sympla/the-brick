<?php

namespace Sympla\Search\DocGen;

class Generator
{

    private $allRoutes;
    private $docArray;

    public function __construct()
    {
    }

    public function run()
    {
        $this->handle();
    }

    private function handle()
    {
        $this->setRoutes();
        $this->generate();
        $this->createJsonFile();
    }

    private function createJsonFile()
    {
        $storage = app('Storage');
        $storage::disk('local')
            ->put('the-brick/doc.json', json_encode($this->docArray, JSON_PRETTY_PRINT));
    }

    /**
     * Doc generate
     * @return void
     */
    private function generate()
    {
        foreach ($this->allRoutes as $route) {
            $docArray = [];

            if ($route->getAction()['uses'] != '' && is_string($route->getAction()['uses'])) {
                $uses = explode('@', $route->getAction()['uses']);
                $class = $uses[0];

                if (class_exists($class)) {
                    $class = $this->getReflectionClass($class);
                    if ($class->hasMethod($uses[1])) {
                        $docArray = $this->parseDoc($class
                                ->getMethod($uses[1])
                                ->getDocComment()
                            ) ?? [];
                    }
                }

                if (isset($docArray['negotiate'])) {
                    $class = $docArray['negotiate'];
                    $class = $this->getReflectionClass(
                        (config('the-brick-search.models.namespace_prefix') ?? 'App\\').$class
                    );
                    $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
                    $filters = [];

                    foreach ($methods as $method) {
                        if (strpos($method->name, 'scope') !== false) {
                            $filters[] =  [
                                'name' => lcfirst(str_replace('scope', '', $method->name)),
                                'description' => $this->parseDoc($method->getDocComment())['negotiateDesc'] ?? ''
                            ];
                        }
                    }

                    $this->docArray[] = [
                        'route' => $route->getPath(),
                        'methods' => $route->getMethods(),
                        'uses' => $route->getAction()['uses'],
                        'description' => $docArray['negotiateDesc'] ?? '',
                        'filters' => $filters
                    ];
                }
            }
        }
    }

    private function setRoutes()
    {
        $route = app('route');
        $this->allRoutes = $route::getRoutes();
        return $this;
    }

    private function getReflectionClass($class)
    {
        return new \ReflectionClass($class);
    }

    private function parseDoc($str)
    {
        $result = null;

        if (preg_match_all('/@(\w+)\s+(.*)\r?\n/m', $str, $matches)) {
            $result = array_combine($matches[1], $matches[2]);
        }

        return $result;
    }
}