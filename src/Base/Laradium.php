<?php

namespace Laradium\Laradium\Base;

use Illuminate\Support\Collection;
use Laradium\Laradium\Registries\ApiResourceRegistry;
use Laradium\Laradium\Registries\ResourceRegistry;

class Laradium
{

    /**
     * @var \Illuminate\Foundation\Application|mixed
     */
    protected $resourceRegistry;

    /**
     * @var \Illuminate\Foundation\Application|mixed
     */
    protected $apiResourceRegistry;

    /**
     * @var array
     */
    protected $baseResources = [
        'vendor/laradium/laradium/src/Base/Resources'            => 'Laradium\\Laradium\\Base\\Resources\\',
        'vendor/laradium/laradium-content/src/Base/Resources'    => 'Laradium\\Laradium\\Content\\Base\\Resources\\',
        'vendor/laradium/laradium-permission/src/Base/Resources' => 'Laradium\\Laradium\\Permission\\Base\\Resources\\',
    ];

    /**
     * Laradium constructor.
     */
    public function __construct()
    {
        $this->resourceRegistry = app(ResourceRegistry::class);
        $this->apiResourceRegistry = app(ApiResourceRegistry::class);
    }

    /**
     * @param $resource
     * @return mixed
     */
    public function register($resource)
    {
        return $this->resourceRegistry->register($resource);
    }

    /**
     * @param $resource
     * @return mixed
     */
    public function registerApi($resource)
    {
        return $this->apiResourceRegistry->register($resource);
    }

    /**
     * @return array
     */
    public function resources(): array
    {
        $baseResources = [];
        $projectResources = [];

        // Project resources
        $resources = config('laradium.resource_path', 'App\\Laradium\\Resources');
        $namespace = app()->getNamespace();
        $resourcePath = str_replace($namespace, '', $resources);
        $resourcePath = str_replace('\\', '/', $resourcePath);
        $resourcePath = app_path($resourcePath);
        if (file_exists($resourcePath)) {
            foreach (\File::files($resourcePath) as $path) {
                $resource = $path->getPathname();
                $baseName = basename($resource, '.php');
                $resource = $resources . '\\' . $baseName;
                $projectResources[] = $resource;
            }
        }

        // CMS resources
        foreach ($this->baseResources as $path => $namespace) {
            $resourcesPath = base_path($path);

            if (file_exists($resourcesPath)) {
                foreach (\File::allFiles($resourcesPath) as $resourcePath) {
                    $resource = $resourcePath->getPathname();
                    $baseName = basename($resource, '.php');
                    $resource = $namespace . $baseName;

                    // Check if there is a overridden resource in the project
                    if ($this->resourceExists($projectResources, $baseName)) {
                        continue;
                    }

                    $baseResources[] = $resource;
                }
            }
        }

        return array_merge($baseResources, $projectResources);
    }

    /**
     * @return array
     */
    public function apiResources(): array
    {
        $resourceList = [];
        $resources = config('laradium.resource_path', 'App\\Laradium\\Resources\\Api') . '\\Api';
        $namespace = app()->getNamespace();
        $resourcePath = str_replace($namespace, '', $resources);
        $resourcePath = str_replace('\\', '/', $resourcePath);
        $resourcePath = app_path($resourcePath);
        if (file_exists($resourcePath)) {
            foreach (\File::allFiles($resourcePath) as $path) {
                $resource = $path->getPathname();
                $baseName = basename($resource, '.php');
                $resource = $resources . '\\' . $baseName;
                $resourceList[] = $resource;
            }
        }

        return $resourceList;
    }

    /**
     * @return Collection
     */
    public function all()
    {
        return $this->resourceRegistry->all();
    }

    /**
     * @param $resources
     * @param $resource
     * @return bool
     */
    protected function resourceExists($resources, $resource): bool
    {
        foreach ($resources as $res) {
            $className = array_last(explode('\\', $res));
            if ($className === $resource) {
                return true;
            }
        }

        return false;
    }
}