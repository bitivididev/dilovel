<?php


namespace App\Components\Blade;

/**
 * Class SectionDirective
 * @package App\Components\Blade
 */
class SectionDirective implements BladeDirectiveInterface
{
    /**
     * @return mixed|string
     */
    public function getDirectiveRegexPattern()
    {
        return '/@section\((?P<yield_name>.*)\)\n*(?P<html_content>.*)\n*@endsection/sU';
    }

    /**
     * @param string $template
     * @return mixed|string|string[]|null
     */
    public function replaceTemplate(string $template)
    {
        return  preg_replace_callback($this->getDirectiveRegexPattern(), static function ($find) {
            return $find['html_content'];
        }, $template);
    }
}