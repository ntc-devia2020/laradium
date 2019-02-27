<?php

namespace Laradium\Laradium\Base\Resources;

use Laradium\Laradium\Base\AbstractResource;
use Laradium\Laradium\Base\ColumnSet;
use Laradium\Laradium\Base\FieldSet;
use Laradium\Laradium\Models\Setting;

Class SettingResource extends AbstractResource
{
    /**
     * @var string
     */
    protected $resource = Setting::class;

    /**
     * @var array
     */
    protected $actions = ['edit'];

    /**
     * @return \Laradium\Laradium\Base\Resource
     */
    public function resource()
    {
        $this->event('afterSave', function () {
            setting()->clearCache();
        });

        return laradium()->resource(function (FieldSet $set) {
            $fieldType = $set->getModel()->type;

            if ($set->getModel()->is_translatable) {
                $set->$fieldType($fieldType === 'file' ? 'file' : 'value')
                    ->translatable()
                    ->label($set->getModel()->name);
            } else if ($fieldType === 'file') {
                $set->$fieldType('file')->label($set->getModel()->name);
            } else {
                if ($fieldType) {
                    $set->$fieldType('non_translatable_value')->label($set->getModel()->name);
                }
            }
        });
    }

    /**
     * @return \Laradium\Laradium\Base\Table
     */
    public function table()
    {
        return laradium()->table(function (ColumnSet $column) {

            $column->add('name')->modify(function ($row) {
                return ($row->is_translatable ? $this->translatableIcon() . ' ' : '') . $row->name;
            });

            $column->add('value')->modify(function ($item) {
                return $this->modifyValueColumn($item);
            })->editable()->translatable()->notSortable();

        })->dataTable(false)
            ->tabs([
                'group' => Setting::select('group')->groupBy('group')->get()->mapWithKeys(function ($setting) {
                    return [
                        $setting->group => ucfirst(str_replace('-', ' ', $setting->group))
                    ];
                })->all()
            ])
            ->search(function ($query) {
                if (request()->has('search') && isset(request()->input('search')['value']) && !empty(request()->input('search')['value'])) {
                    $searchTerm = request()->input('search')['value'];

                    $query->where('group', request()->input('group'))
                        ->where(function ($query) use ($searchTerm) {
                            $query->whereTranslationLike('value', '%' . $searchTerm . '%')
                                ->orWhere('non_translatable_value', 'LIKE', '%' . $searchTerm . '%')
                                ->orWhere('name', 'LIKE', '%' . $searchTerm . '%');
                        });
                }
            });
    }

    /**
     * @param $item
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string|array
     * @throws \Throwable
     */
    public function modifyValueColumn($item)
    {
        //we do not want to display textarea content in table
        if ($item->type === 'textarea') {
            return [
                'type'  => 'textarea',
                'value' => '- too long to show -'
            ];
        }

        if ($item->type === 'file') {
            if ($item->is_translatable) {
                $html = '';
                foreach ($item->translations as $translation) {
                    $html .= view('laradium::admin.table._partials.file', [
                        'item' => $translation,
                        'locale' => $translation->locale
                    ])->render();
                }

                return [
                    'type'  => 'file',
                    'value' => $html
                ];
            }

            return [
                'type'  => 'file',
                'value' => view('laradium::admin.table._partials.file', ['item' => $item])->render()
            ];
        }

        if ($item->is_translatable) {
            $html = '';
            foreach ($item->translations as $translation) {
                $html .= '<li><b>' . strtoupper($translation->locale) . ': </b>' . $translation->value . '</li>';
            }

            return [
                'translatable' => true,
                'type'         => $item->type,
                'value'        => $html
            ];
        }

        return [
            'column' => 'non_translatable_value',
            'type'   => 'text',
            'value'  => $item->non_translatable_value ? e($item->non_translatable_value) : ''
        ];
    }

    /**
     * @return string
     */
    public function translatableIcon()
    {
        return '<span data-toggle="tooltip" data-placement="top" title="" data-original-title="Value is translatable"><i class="fa fa-language"></i></span>';
    }

}
