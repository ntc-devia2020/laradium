<?php

namespace Laradium\Laradium\Base\Resources;

use function foo\func;
use Illuminate\Support\Facades\Route;
use Laradium\Laradium\Base\AbstractResource;
use Laradium\Laradium\Base\ColumnSet;
use Laradium\Laradium\Base\FieldSet;
use Laradium\Laradium\Base\Laradium;
use Laradium\Laradium\Base\Resource;
use Laradium\Laradium\Base\Table;
use Laradium\Laradium\Models\Menu;

Class MenuResource extends AbstractResource
{

    /**
     * @var Laradium
     */
    private $laradium;

    /**
     * @var string
     */
    protected $resource = Menu::class;

    /**
     * @var bool
     */
    protected $withoutCard = true;

    /**
     * MenuResource constructor.
     */
    public function __construct()
    {
        $this->laradium = app(Laradium::class);

        parent::__construct();
    }

    /**
     * @return Resource
     */
    public function resource()
    {
        $this->event('afterSave', function () {
            cache()->forget(Menu::$cacheKey);
        });

        return laradium()->resource(function (FieldSet $set) {
            $set->tabs()
                ->add('Items', function (FieldSet $set) {
                    $set->block(12)->fields(function (FieldSet $set) {
                        $set->tree('items')->fields(function (FieldSet $set) {
                            $set->select2('icon')->options(getFontAwesomeIcons());
                            $set->text('name')->rules('required|max:255')->translatable()->col(6);
                            $set->select('target')->options([
                                '_self'  => 'Self',
                                '_blank' => 'Blank',
                            ])->rules('required')->col(6);
                            $set->select('type')->options(Menu::$types);
                            $set->text('url')->rules('required_if:items.*.type,url|max:255')->translatable()->col(4);
                            $set->select('resource')->options($this->getResourceOptions())->rules('required_if:items.*.type,resource')->col(4);
                            $set->select('route')->options($this->getRouteOptions())->rules('required_if:items.*.type,route')->col(4);
                        })->sortable()->attr([
                            'key' => optional($this->getModel())->key
                        ]);
                    });
                })
                ->add('Basic', function (FieldSet $set) {
                    $set->block(12)->fields(function (FieldSet $set) {
                        $set->boolean('is_active');
                        $set->text('key')->rules('required|max:255');
                        $set->text('name')->rules('required|max:255')->translatable();
                    });
                });
        });
    }

    /**
     * @return Table
     */
    public function table()
    {
        return laradium()->table(function (ColumnSet $column) {
            $column->add('key');
            $column->add('name')->translatable();
            $column->add('is_active')->switchable()->raw();
        })->relations(['translations']);
    }

    /**
     * @return array
     */
    public function getResourceOptions(): array
    {
        $resources = collect($this->laradium->resources())->mapWithKeys(function ($resource) {
            return [$resource => (new $resource)->getBaseResource()->getName()];
        })->toArray();

        return array_merge(['' => '- Select -'], $resources);
    }

    /**
     * @return mixed
     */
    public function getRouteOptions()
    {
        $type = (request()->route()->menu && request()->route()->menu === '1') ? 'admin' : 'public';
        $routes = ['' => '- Select -'];

        foreach (Route::getRoutes() as $route) {
            if (!$route->getName() || !in_array('GET', $route->methods())) {
                continue;
            }

            $name = str_replace(['.', 'admin', 'index'], ' ', $route->getName());
            if ($this->filterRoute($type, $route)) {
                $routes[$route->getName()] = ucfirst(trim($name));
            }
        }

        asort($routes);

        return $routes;
    }

    /**
     * @param $type
     * @param $route
     * @return bool
     */
    private function filterRoute($type, $route): bool
    {
        $action = array_last(explode('.', $route->getName()));

        if ($type === 'admin') {
            return in_array('laradium', $route->middleware()) &&
                in_array($action, ['index', 'create', 'dashboard']) &&
                $route->getName() !== 'admin.index';
        }

        return !in_array('laradium', $route->middleware()) &&
            !in_array($action, ['data-table']) &&
            !count($route->parameterNames()) &&
            !str_contains($route->getName(), 'admin.');
    }
}