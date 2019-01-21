<?php

namespace Laradium\Laradium\Base;

use Illuminate\Database\Eloquent\Model;
use Laradium\Laradium\Base\Fields\Tab;

class Element
{
    /**
     * @var
     */
    private $fieldSet;

    /**
     * @var
     */
    private $name;

    /**
     * @var
     */
    private $closure;

    /**
     * @var bool
     */
    private $isTranslatable = false;

    /**
     * @var
     */
    private $model;

    /**
     * @var
     */
    private $fields;

    /**
     * @var array
     */
    private $validationRules = [];

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * Col constructor.
     * @param $parameters
     * @param Model $model
     */
    public function __construct($parameters, Model $model)
    {
        $this->model = $model;
        $this->fieldSet = new FieldSet;
    }

    /**
     * @return array
     */
    public function formattedResponse(): array
    {
        return [
            'name'   => $this->name,
            'slug'   => str_slug($this->name, '_'),
            'type'   => str_slug($this->getType()),
            'fields' => $this->fields,
            'config' => [
                'is_translatable' => $this->isTranslatable,
                'col'             => $this->name,
            ],
            'attr'   => $this->getAttributes()
        ];
    }

    /**
     * @return $this
     */
    public function build(): self
    {
        $fieldSet = $this->fieldSet;
        $fieldSet->model($this->model);
        $closure = $this->closure;

        if ($closure) {
            $closure($fieldSet);
        }

        $fields = [];
        foreach ($fieldSet->fields() as $field) {
            if ($field instanceof Tab) {
                $field->model($this->getModel());
            }

            $field->build();

            if ($field->isTranslatable()) {
                $this->isTranslatable = true;
            }

            $this->validationRules = array_merge($this->validationRules, $field->getValidationRules());

            $fields[] = $field->formattedResponse();
        }

        $this->fields = $fields;

        return $this;
    }


    /**
     * @param $closure
     * @return $this
     */
    public function fields($closure): self
    {
        $this->closure = $closure;

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
     * @return bool
     */
    public function isTranslatable(): bool
    {
        return $this->isTranslatable;
    }

    /**
     * @param $value
     * @return $this
     */
    public function model($value): self
    {
        $this->model = $value;

        return $this;
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @return mixed
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsTranslatable(): bool
    {
        return $this->isTranslatable;
    }

    /**
     * @param $value
     * @return $this
     */
    public function type($value): self
    {
        $this->type = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type ?: strtolower(array_last(explode('\\', get_class($this))));
    }

    /**
     * @param $value
     * @return $this
     */
    public function attributes($value): self
    {
        $this->attributes = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}