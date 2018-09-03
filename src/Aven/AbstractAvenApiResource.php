<?php

namespace Netcore\Aven\Aven;

use Illuminate\Http\Request;
use Netcore\Aven\Traits\Crud;

abstract class AbstractAvenApiResource
{
    use Crud;

    /**
     * @var
     */
    protected $model;

    /**
     * @var string
     */
    protected $resource;

    /**
     * @var array
     */
    protected $events = [];

    /**
     * AbstractAvenResource constructor.
     */
    public function __construct()
    {
        $this->model = new $this->resource;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $model = $this->model;
        $api = $this->api()->setModel($model);

        if (count($api->getRelations())) {
            $model = $model->with($api->getRelations())->select('*');
        } else {
            $model = $model->select('*');
        }

        if ($api->getWhere()) {
            $model = $model->where($api->getWhere());
        }

        $model = $model->get();

        $data = $model->map(function ($row, $key) use ($api) {
            foreach ($api->fields() as $field) {
                $value = $field['modify'] ?? $row->{$field['name']};

                $attributes[$field['name']] = $value;
            }

            return $attributes;
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * @param null $id
     * @return array
     */
    public function getForm($id = null)
    {
        if ($id) {
            $model = $this->model->find($id);
        } else {
            $model = $this->model;
        }

        $resource = $this->resource();
        $form = new Form($resource->setModel($model)->build());
        $form->buildForm();
        $response = $form->formatedResponse();

        return ([
            'languages' => collect(translate()->languages())->map(function ($item, $index) {
                $item['is_current'] = $index == 0;

                return $item;
            })->toArray(),
            'inputs' => $response,
            'tabs' => $resource->fieldSet()->tabs()->toArray()
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $model = $this->model;

        $resource = $this->resource();
        $form = new Form($resource->setModel($model)->build());
        $form->buildForm();

        return response()->json([
            'success' => true,
            'data' => $form->formatedResponse()
        ]);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \ReflectionException
     */
    public function store(Request $request)
    {
        $model = $this->model;

        $resource = $this->resource();
        $form = new Form($resource->setModel($model)->build());
        $form->buildForm();

        $request->validate($form->getValidationRules());

        if (isset($this->events['beforeSave'])) {
            $this->events['beforeSave']($this->model, $request);
        }

        $this->updateResource($request->except('_token'), $model);

        if (isset($this->events['afterSave'])) {
            $this->events['afterSave']($this->model, $request);
        }

        if ($request->ajax()) {
            return [
                'success' => 'Resource successfully created!',
                'redirect' => url()->previous()
            ];
        }

        return back()->withSuccess('Resource successfully created!');
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $model = $this->model;
        $api = $this->api()->setModel($model);

        if (count($api->getRelations())) {
            $model = $model->with($api->getRelations())->select('*');
        } else {
            $model = $model->select('*');
        }

        if ($api->getWhere()) {
            $model = $model->where($api->getWhere());
        }

        $model = $model->findOrFail($id);

        $data = $api->fields()->mapWithKeys(function ($field) use ($model) {
            $value = $field['modify'] ?? $model->{$field['name']};

            return [$field['name'] => $value];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $model = $this->model;
        $api = $this->api()->setModel($model);

        if ($api->getWhere()) {
            $model = $model->where($api->getWhere());
        }

        $model = $model->findOrFail($id);

        $resource = $this->resource();
        $form = new Form($resource->setModel($model)->build());
        $form->buildForm();

        return response()->json([
            'success' => true,
            'data' => $form->formatedResponse()
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws \ReflectionException
     */
    public function update(Request $request, $id)
    {
        $model = $this->model;
        $api = $this->api()->setModel($model);

        if ($api->getWhere()) {
            $model = $model->where($api->getWhere());
        }

        $model = $model->findOrFail($id);

        $resource = $this->resource();
        $form = new Form($resource->setModel($model)->build());
        $form->buildForm();

        $request->validate($form->getValidationRules());

        if (isset($this->events['beforeSave'])) {
            $this->events['beforeSave']($this->model, $request);
        }

        $this->updateResource($request->except('_token'), $model);

        if (isset($this->events['afterSave'])) {
            $this->events['afterSave']($this->model, $request);
        }

        if ($request->ajax()) {
            return [
                'success' => 'Resource successfully updated!',
                'data' => $this->getForm($model->id)
            ];
        }

        return back()->withSuccess('Resource successfully updated!');
    }

    /**
     * @param Request $request
     * @param $id
     * @return array
     */
    public function destroy(Request $request, $id)
    {
        $model = $this->model;

        if ($api->getWhere()) {
            $model = $model->where($api->getWhere());
        }

        $model = $model->findOrFail($id);

        $model->delete();

        if ($request->ajax()) {
            return [
                'success' => true,
                'message' => 'Resource successfully deleted!'
            ];
        }

        return back()->withSuccess('Resource successfully deleted!');
    }

    /**
     * @param $name
     * @param \Closure $callable
     * @return $this
     */
    protected function registerEvent($name, \Closure $callable)
    {
        $this->events[$name] = $callable;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResourceName()
    {
        return $this->model->getTable();
    }

    /**
     * @return mixed
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * @return \Netcore\Aven\Aven\Api
     */
    abstract protected function api();
}