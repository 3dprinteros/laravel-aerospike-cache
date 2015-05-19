<?php namespace Mochaka\AerospikeCache;


class AerospikeStore implements \Illuminate\Contracts\Cache\Store
{

    /**
     * The Aerospike instance.
     *
     * @var \Memcache
     */
    protected $aerospike;

    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;

    /**
     * The namespace for the cache
     *
     * @var int|string
     */
    protected $namespace;

    /**
     * Create a new Aerospike store.
     *
     * @param  \Aerospike $aerospike
     * @param  string     $prefix
     * @param  string     $namespace
     * @return void
     */
    public function __construct(\Aerospike $aerospike, $prefix = '', $namespace = 0)
    {
        $this->aerospike = $aerospike;
        $this->namespace = $namespace;
        $this->prefix = strlen($prefix) > 0 ? $prefix.':' : '';
    }

    public function get($key)
    {
        $status = $this->aerospike->get($this->getKey($key), $record);
        if ($status == \Aerospike::OK) {
            return $record['bins'];
        }
        return null;
    }

    public function put($key, $value, $minutes)
    {
        $this->aerospike->set($this->getKey($key), $value, $minutes * 60);
    }

    public function increment($key, $value = 1)
    {
        $this->aerospike->increment($this->getKey($key), '', $value);
    }

    public function decrement($key, $value = 1)
    {
        $this->aerospike->decrement($this->getKey($key), $value);
    }

    public function forever($key, $value)
    {
        $this->aerospike->set($this->getKey($key), $value);
    }

    public function forget($key)
    {
        $this->aerospike->remove($this->getKey($key));
    }

    public function flush()
    {
        $this->aerospike->flush();
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    private function getKey($key)
    {
        return $this->aerospike->initKey($this->namespace, $this->prefix, $key);
    }

}