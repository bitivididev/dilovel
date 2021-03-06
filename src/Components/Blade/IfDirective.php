<?php


namespace App\Components\Blade;

class IfDirective implements BladeDirectiveInterface
{

    /**
     * @inheritDoc
     */
    public function replaceTemplate(string $template)
    {
        return preg_replace_callback($this->getDirectiveRegexPattern(), static function ($f) {
            return '<?php if' . $f[1] . ':?>';
        }, $template);
    }

    /**
     * @inheritDoc
     */
    public function getDirectiveRegexPattern()
    {
        return '/@if(.*)/';
    }
}
