<?php

namespace Laradium\Laradium\Models;

use Dimsav\Translatable\Translatable;

class MenuItem extends \Baum\Node
{

    use Translatable;

    /**
     *
     * @var array
     */
    protected $fillable = [
        'is_active',
        'target',
        'sequence_no',
        'icon',
        'type',
        'route',
        'resource',
        'data_attributes',
        'parent_id',
    ];

    /**
     * @var array
     */
    protected $translatedAttributes = [
        'name',
        'url',
        'locale',
    ];

    /**
     * @var array
     */
    protected $with = ['translations'];

    /**
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->whereIsActive(true);
    }

    /**
     * @return string
     */
    public function getUrlAttribute()
    {
        if ($this->type === 'resource') {
            return $this->getUrlFromResource();
        }

        if ($this->type === 'route') {
            return $this->getUrlFromRoute();
        }

        return $this->translateOrNew(app()->getLocale())->url ?? '';
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        if (!$this->resource) {
            return null;
        }

        if (!class_exists($this->resource)) {
            return null;
        }

        return new $this->resource;
    }

    /**
     * @return string
     */
    private function getUrlFromResource()
    {
        if (!$this->resource) {
            return '';
        }

        if (!class_exists($this->resource)) {
            return '';
        }

        $resource = new $this->resource;
        $slug = str_replace('/', '.', $resource->getBaseResource()->getSlug());

        return $resource->isShared() ? route($slug . '.index') : route('admin.' . $slug . '.index');
    }

    /**
     * @return string
     */
    private function getUrlFromRoute()
    {
        try {
            return route($this->route);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @return array
     */
    public function getDataAttributes(): array
    {
        $delimiter = strpos($this->data_attributes, ',') !== false ? ',' : ' ';
        $attributeString = explode($delimiter, $this->data_attributes);
        $attributes = [];

        foreach ($attributeString as $attribute) {
            $attr = strstr($attribute, '=', true);
            $value = ltrim(strstr($attribute, '='), '=');
            $value = trim($value, '"');

            $attributes[$attr] = $value;
        }

        return $attributes;
    }
}
