<?php

namespace Laradium\Laradium\Services\Crud\Workers;

class PasswordWorker extends AbstractWorker
{
    /**
     * @return void
     */
    public function beforeSave(): void
    {
        foreach ($this->formData as $fieldName => $value) {
            if (!str_contains($fieldName, '_confirmation') && $value) {
                $this->formData[$fieldName] = bcrypt($value);
            } else {
                unset($this->formData[$fieldName]);
            }
        }
    }

    /**
     * @return void
     */
    public function afterSave(): void
    {
        //
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->formData;
    }
}