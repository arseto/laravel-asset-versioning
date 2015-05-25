<?php namespace EscapeWork\Assets;

use Illuminate\Foundation\Application as App;
use Illuminate\Config\Repository as Config;
use Illuminate\Cache\Repository as Cache;

class Asset
{

    /**
     * @var Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @var Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var Illuminate\Cache\Repository
     */
    protected $cache;

    public function __construct(App $app, Config $config, Cache $cache)
    {
        $this->app    = $app;
        $this->config = $config;
        $this->cache  = $cache;
    }

    public function v($path)
    {
        if (! in_array($this->app->environment(), $this->config->get('laravel-asset-versioning::environments'))) {
            return $this->asset($path);
        }

        return $this->asset($this->replaceVersion($path));
    }

    public function path($extension)
    {
        $type    = $this->config->get('laravel-asset-versioning::types.' . $extension);
        if ($this->app->environment() == 'local') {
            return $this->asset($type['origin_dir']);
        }
        return $this->asset($type['dist_dir']) . '/' . $this->cache->get('laravel-asset-versioning.version');
    }


    public function replaceVersion($path)
    {
        $version    = $this->cache->get('laravel-asset-versioning.version');
        $file      = explode('.', $path);
        $extension = $file[count($file) - 1];
        $type      = $this->config->get('assets.types.' . $extension);

        if (! $type) {
            return $path;
        }

        if (! preg_match("#^\/?" . $type['origin_dir'] . "#", $path)) {
            return $path;
        }

        return str_replace($type['origin_dir'], $type['dist_dir'].'/' . $version, $path);
    }

    public function asset($path)
    {
        return asset($path);
    }
}
