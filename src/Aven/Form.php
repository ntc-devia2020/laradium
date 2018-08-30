<?php

namespace Netcore\Aven\Aven;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Netcore\Aven\Aven\Fields\HasMany;
use Netcore\Aven\Aven\Fields\MorphsTo;
use Netcore\Aven\Aven\Fields\Tab;
use Netcore\Aven\Content\Aven\Fields\WidgetConstructor;

class Form
{

    /**
     * @var Collection
     */
    protected $fields;

    /**
     * @var Model
     */

    protected $model;
    /**
     * @var Resource
     */

    protected $resource;
    /**
     * @var array
     */
    protected $validationRules = [];

    /**
     * Form constructor.
     * @param $resource
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
        $this->fields = new Collection;
    }

    /**
     * @return $this
     */
    public function buildForm()
    {
        $resource = $this->resource;
        $fields = $resource->fieldSet()->fields();
        $this->model = $resource->model();

        foreach ($fields as $field) {
            if ($field instanceof Tab) {
                $tabFields = $field->setFieldSet($resource->fieldSet())->build();
                foreach ($tabFields as $tabField) {
                    $tabField->build();
                    $this->setValidationRules($tabField->getRules());

                    $this->fields->push($tabField);
                }
            } else {
                $field->build();
                $this->setValidationRules($field->getRules());

                $this->fields->push($field);
            }
        }

        return $this;
    }

    public function formatedResponse()
    {
        $fieldList = [];
        foreach ($this->fields as $field) {
            $fieldList[] = $field->formatedResponse($field);
        }

        return $fieldList;
    }

    /**
     * @return Collection
     */
    public function fields(): Collection
    {
        return $this->fields;
    }

    /**
     * @return Model
     */
    public function model(): Model
    {
        return $this->model;
    }

    /**
     * @return string
     */
    public function resourceName(): string
    {
        return str_replace('_', '-', $this->model()->getTable());
    }

    /**
     * @param $rules
     * @return $this
     */
    public function setValidationRules(
        $rules
    ) {
        $this->validationRules += $rules;

        return $this;
    }

    /**
     * @return array
     */
    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    /**
     * @param string $action
     * @return string
     */
    public function getAction($action = 'index'): string {
        $resource = $this->resourceName();
        if ($action == 'create') {
            return url('/admin/' . $resource . '/create');
        } elseif ($action == 'create') {
            return url('/admin/' . $resource . '/create');
        } elseif ($action == 'store') {
            return url('/admin/' . $resource);
        } elseif ($action == 'update') {
            return url('/admin/' . $resource . '/' . $this->model->id);
        }

        return url('/admin/' . $resource);
    }
}