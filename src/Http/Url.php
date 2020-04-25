<?php


namespace App\Http;


/**
 * Class Url
 * @package App\Http
 */
class Url
{

    /**
     * @var array
     */
    private array $request = [];

    /**
     * @var array
     */
    private array  $server = [];

    /**
     * Url constructor.
     */
    public function __construct()
    {
        $this->request = $_REQUEST;
        $this->server = $_SERVER;
    }

    /**
     * @return string
     */
    function getSchema()
    {
        return strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, strpos($_SERVER["SERVER_PROTOCOL"], '/'))) . '://';
    }

    /**
     * @return string
     */
    function base()
    {

        $base = sprintf(
            '%s%s',
            $this->getSchema(),
            $_SERVER['SERVER_NAME'],

        );
        $port = $this->port();
        if (isset($port)) {
            $base = rtrim($base, '/');
            return "$base:$port";
        }
        return $base;


    }

    public function port()
    {
        return $this->server['SERVER_PORT'];
    }


    /**
     * @return string
     */
    public function full()
    {
        return sprintf('%s%s', $this->base(), $this->server['REQUEST_URI']);
    }

    /**
     * @return array|false|int|string|null
     */
    public function parse()
    {
        return parse_url($this->full());
    }

    /**
     * @return array
     */
    function query()
    {
        parse_str($this->parse()['query'], $array);
        return $array;
    }

    public function path()
    {
        return $this->parse()['path'];
    }
}