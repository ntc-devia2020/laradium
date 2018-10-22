<?php

namespace Laradium\Laradium\Base\Fields;

use Illuminate\Database\Eloquent\Model;
use Laradium\Laradium\Base\Field;
use Laradium\Laradium\Base\FieldSet;

class HasOne extends Field
{

    /**
     * @var FieldSet
     */
    protected $fieldSet;

    /**
     * @var string
     */
    protected $relationName;

    /**
     * @var
     */
    protected $fields;

    /**
     * MorphsTo constructor.
     * @param $parameters
     * @param Model $model
     */
    public function __construct($parameters, Model $model)
    {
        parent::__construct($parameters, $model);

        $this->fieldSet = new FieldSet;
        $this->relationName = $this->name;
    }

    /**
     * @param array $parentAttributeList
     * @param null $model
     * @return $this|Field
     */
    public function build($parentAttributeList = [], $model = null)
    {
        $this->parentAttributeList = $parentAttributeList;
        $fields = $this->fieldSet->fields();
        $fieldList = [];
        $rules = [];

        $model = $this->model()->{$this->relationName};

        $attributeList = array_merge($this->parentAttributeList, [
            $this->relationName,
        ]);

        foreach ($fields as $field) {
            $clonedField = clone $field;
            if (!$model) {
                $model = $this->model()->{$this->relationName}()->getModel();
            }

            $clonedField->setModel($model);
            $clonedField->build($attributeList, $model);

            $fieldList[] = $clonedField;
            $rules += $clonedField->getRules();
        }

        if ($rules) {
            $this->validationRules = $rules;
        }

        $this->fields = $fieldList;

        return $this;
    }

    /**
     * @param null $f
     * @return array
     */
    public function formattedResponse($f = null)
    {
        $f = !is_null($f) ? $f : $this;

        $items = [];

        foreach ($f->fields as $field) {
            $items[] = $field->formattedResponse();
        }

        return [
            'type'   => 'has-one',
            'tab'    => $this->tab(),
            'col'    => $this->col,
            'name'   => ucfirst($f->relationName),
            'label'  => ucfirst(str_singular($f->relationName)),
            'show'   => false,
            'fields' => $items
        ];
    }

    /**
     * @param $closure
     * @return $this
     */
    public function fields($closure)
    {
        $fieldSet = $this->fieldSet;
        $fieldSet->setModel($this->model());
        $closure($fieldSet);

        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function relation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->model()->{$this->relationName}();
    }
}