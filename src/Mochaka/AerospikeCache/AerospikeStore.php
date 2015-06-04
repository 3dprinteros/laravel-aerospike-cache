<?php namespace Mochaka\AerospikeCache;


class AerospikeStore implements \Illuminate\Contracts\Cache\Store
{

    /**
     * The Aerospike instance.
     *
     * @var \Aerospike
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
    public function __construct(\Aerospike $aerospike, $prefix = '', $namespace = '3dprinteros')
    {
        $this->aerospike = $aerospike;
        $this->namespace = $namespace;
        $this->prefix = $prefix;
    }

    public function get($key)
    {
        $status = $this->aerospike->get($this->getKey($key), $record);
        if ($status == \Aerospike::OK) {
            return $record['bins'];
        }
        return null;
    }

    public function put($key, $value, $minutes, $prefix = null)
    {
        $this->aerospike->put($this->getKey($key, $prefix), $value, $minutes * 60);
    }

    public function decrement($key, $value = 1, $prefix = null)
    {
        $this->aerospike->decrement($this->getKey($key, $prefix), $value);
    }

    public function increment($key, $value = 1, $prefix = null)
    {
        $this->aerospike->increment($this->getKey($key, $prefix), '', $value);
    }

    public function forever($key, $value, $prefix = null)
    {
        $this->aerospike->put($this->getKey($key, $prefix), $value);
    }

    public function forget($key, $prefix = null)
    {
        $this->aerospike->remove($this->getKey($key, $prefix));
    }

    public function flush()
    {
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    private function getKey($key, $prefix = null)
    {
        if (!$prefix) {
            $prefix = $this->prefix;
        }
        return $this->aerospike->initKey($this->namespace, $prefix, $key);
    }

}