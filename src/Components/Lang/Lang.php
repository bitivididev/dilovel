<?php


namespace App\Components\Lang;

use App\Components\Arr\DotNotation;
use App\Components\Http\Session;

/**
 * Class Lang
 * @package App\Components\Lang
 */
class Lang
{
    /**
     * @var array
     */
    private static array $langFiles = [];

    /**
     * @param string $folderName
     * @return bool
     */
    public static function set(string $folderName = 'en'): bool
    {
        return Session::getInstance()->set('system_language', $folderName);
    }

    /**
     * @param string $dotNotation
     * @return string
     */
    public static function get(string $dotNotation): ?string
    {
        [$file, $dot] = explode('.', $dotNotation, 2);
        $path = sprintf('src/Application/Lang/%s/%s.php', self::getName(), $file);
        if (!isset(self::$langFiles[$path])) {
            self::$langFiles[$path]=require $path;
        }
        return DotNotation::getInstance()->getValueByKey($dot, self::$langFiles[$path]);
    }

    /**
     * @return mixed|string
     */
    public static function getName()
    {
        return Session::getInstance()->get('system_language') ?? 'en';
    }
}
