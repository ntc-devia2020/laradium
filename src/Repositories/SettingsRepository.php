<?php

namespace Laradium\Laradium\Repositories;

use Laradium\Laradium\Models\Setting;
use Laradium\Laradium\Models\SettingTranslation;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SettingsRepository
{

    /**
     * @var mixed
     */
    protected $cachedSettings;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    protected $cacheKey;

    /**
     * SettingRepository constructor.
     */
    public function __construct()
    {
        $this->cacheKey = config('laradium-setting.cache_key', 'laradium::settings');

        $this->cachedSettings = cache()->rememberForever($this->cacheKey, function () {
            $settings = Setting::all()->keyBy('key')->map(function ($item) {
                return $item->toArray();
            });

            return $settings;
        });
    }

    /**
     * Get specified setting/s
     *
     * @param array|string $keys
     * @param array|null $default
     * @return array|string
     */
    public function get($keys, $default = null)
    {
        $settings = $this->cachedSettings;

        if (is_array($keys)) {
            $array = [];
            foreach ($keys as $index => $key) {
                $setting = $settings->get($key);
                $array[$key] = $setting ? $this->getValue($setting) : (is_array($default) ? (isset($default[$index]) ? $default[$index] : null) : $default);
            }

            return $array;
        }

        $setting = $settings->get($keys);

        return $setting ? $this->getValue($setting) : (is_array($default) ? (isset($default[0]) ? $default[0] : null) : $default);
    }

    /**
     * @param $setting
     * @return null
     */
    private function getValue($setting)
    {
        if ($setting['is_translatable']) {
            $translation = collect(array_get($setting, 'translations'))->where('locale', app()->getLocale())->first();

            if ($translation) {
                if ($setting['type'] === 'file') {
                    $file = SettingTranslation::find($translation['id'])->file;

                    return is_file(public_path('uploads/' . $file->path())) ? $file->url() : null;
                }

                return $translation['value'];
            }

            return null;
        }

        //yes its ugly. yes it needs to be redone. yes I am a lazy fuck
        if ($setting['type'] === 'file') {
            $file = Setting::find($setting['id'])->file;

            return is_file(public_path('uploads/' . $file->path())) ? $file->url() : null;
        }

        return $setting['non_translatable_value'];
    }

    /**
     * Get all settings
     *
     * @return array
     */
    public function all(): array
    {
        $settings = [];

        foreach ($this->cachedSettings as $setting) {
            $settings[$setting['key']] = $this->getValue($setting);
        }

        return $settings;
    }

    /**
     * Get grouped settings
     *
     * @return array
     */
    public function grouped()
    {
        return $this->cachedSettings->groupBy('group');
    }

    /**
     * Seed settings
     *
     * @param $data
     * @return void
     * @throws \Exception
     */
    public function seed($data)
    {
        if (!is_array($data)) {
            throw new \Exception('Passed settings should be an array');
        }

        foreach ($data as $item) {
            if (!isset($item['group'])) {
                throw new \Exception('Group does not exist for key: ' . $item['key']);
            }

            $item['key'] = implode('.', [
                $item['group'],
                $item['key']
            ]);

            // File
            if (isset($item['type']) && $item['type'] === 'file' && isset($item['file']) && isset($item['file']['file'])) {
                $file = $item['file']['file'];

                $item['type'] = 'file';
                $item['file'] = null;

                if (is_file($file)) {
                    $file = new \Symfony\Component\HttpFoundation\File\File($file);
                    $file = new UploadedFile($file, $file->getBasename(), $file->getMimeType(), null, null, true);
                    $item['file'] = $file;
                }
            }

            $setting = Setting::firstOrCreate([
                'key' => $item['key']
            ], array_except($item, 'value'));

            $translations = [];
            if (isset($item['value']) && is_array($item['value'])) {
                foreach ($item['value'] as $locale => $value) {
                    $translations[] = [
                        'locale' => $locale,
                        'value'  => $value
                    ];
                }
            } else {
                $setting->update([
                    'non_translatable_value' => isset($item['value']) ? $item['value'] : ''
                ]);
            }

            foreach ($translations as $translation) {
                $setting->translations()->firstOrCreate($translation);
            }
        }
    }

    /**
     * Clear cache
     *
     * @return bool
     * @throws \Exception
     */
    public function clearCache(): bool
    {
        return cache()->forget($this->cacheKey);
    }
}