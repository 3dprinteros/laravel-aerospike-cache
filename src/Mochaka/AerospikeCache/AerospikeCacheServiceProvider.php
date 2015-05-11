<?php namespace Mochaka\AerospikeCache;

use Aerospike;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AerospikeCacheServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            'aerospike', function ($app) {
                $config = $this->app['config'];

                if ($config['cache.stores.aerospike.servers']) {
                    $config = $config['cache.stores.aerospike.servers'];
                } else {
                    $config = ["hosts" => [["addr" => "localhost", "port" => 3000]]];
                }

                $db = new Aerospike($config);

                return $db;
            }
        );

        $this->app->singleton(
            'aerospike.store', function ($app) {
                $prefix = $this->app['config']['cache.prefix'];

                return new Repository(new AerospikeStore($app['aerospike'], $prefix));
            }
        );
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->extendCache($this->app);
    }

    /**
     * Add the aerospike driver to the cache manager.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function extendCache(Application $app)
    {
        $app->resolving(
            'cache', function (CacheManager $cache) {
                $cache->extend(
                    'aerospike', function ($app) {
                        return $app['aerospike.store'];
                    }
                );
            }
        );
    }
}