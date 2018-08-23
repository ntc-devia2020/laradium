<?php

namespace Netcore\Aven\Aven\Fields;


use Netcore\Aven\Aven\Field;

class Boolean extends Field
{

    /**
     * @var string
     */
    protected $view = 'aven::admin.fields.boolean';

    public function formatedResponse($field = null)
    {
        $field = !is_null($field) ? $field : $this;

        $attributes = collect($field->getNameAttributeList())->map(function ($item, $index) {
            if ($item == '__ID__') {
                return '__ID' . ($index + 1) . '__';
            } else {
                return $item;
            }
        });

        $field->setNameAttributeList($attributes->toArray());

        $attributes = $attributes->filter(function ($item) {
            return str_contains($item, '__ID');
        });


        return [
            'type'                   => strtolower(array_last(explode('\\', get_class($field)))),
            'name'                   => $field->getNameAttribute(),
            'label'                  => $field->getLabel(),
            'replacemenetAttributes' => $attributes->toArray(),
            'checked'                => $field->getValue() == 1,
        ];
    }
}