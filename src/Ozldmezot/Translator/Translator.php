<?php namespace Ozldmezot\Translator;


class Translator {

    public $extension = '.php';
    public $path      = 'lang/';

    public function __construct(string $fallback_locale, string $locale, string $path)
    {
        $this->fallback_locale = $fallback_locale;
        $this->locale = $locale;
        $this->path   = $this->suffix($path);
    }

    protected function suffix($string)
    {
        $needle = '/';
        if (substr($string, -1) == $needle) {
            return $string;
        }

        return $string . '/';
    }
    public function translate(string $key, array $data = [], string $locale = null)
    {
        $segments = explode('.', $key);

        $file =  array_shift($segments);
        $locale = $locale ?? $this->locale;
        if (!isset($this->mapping[$locale][$key])) {
            $this->mapping[$locale][$key] = $this->load(
                $locale, $this->fallback_locale, $file);
        }
        $mapping = $this->mapping[$locale][$key];

        $fallback = implode('.', $segments);
        foreach($segments as $segment)
        {
            if (isset($mapping[$segment])) {
                $mapping = $mapping[$segment];
            } else {
                return $fallback;
            }
        }

        if (is_string($mapping)) {
            return $this->replace($mapping, $data);
        }

        return $fallback;
    }

    protected function replace(string $string, array $data)
    {
        foreach($data as $key => $value) {
            $string = str_replace('%' . $key, $value, $string);
        }

        return $string;
    }

    protected function fetch(string $file)
    {
        if(is_file($file))
            return include $file;
        return [];
    }

    protected function load(string $locale, string $fallback_locale, string $key)
    {
        $path = $this->path.'/'.$locale . '/' . $key . $this->extension;
        $mapping = $this->fetch($path);
        $fallback_path = $this->path.'/'.$fallback_locale . '/' . $key . $this->extension;
        $fallback_mapping = $this->fetch($fallback_path);
        return array_merge($fallback_mapping, $mapping);

    }
}

