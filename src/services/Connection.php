<?php

namespace needletail\needletail\services;

use craft\base\Component;
use Needletail\Bucket;
use Needletail\Client;
use needletail\needletail\Needletail;
use Needletail\NeedletailResult;

class Connection extends Component
{
    /**
     * @return Client
     */
    public function getClient()
    {
        return new \Needletail\Client(Needletail::$plugin->getSettings()->getApiReadKey() . "As", Needletail::$plugin->getSettings()->getApiWriteKey());
    }

    public function initBucket($name)
    {
        return $this->getClient()->initBucket($name) instanceof Bucket;
    }

    /**
     * List all
     * @return array
     */
    public function listBuckets()
    {
        if ($buckets = $this->getClient()->list()->buckets)
            return $buckets->toArray();

        return [];
    }

    public function bulk($bucket, $array = [])
    {
        $bucket = $this->getClient()->initBucket($bucket);
        return $bucket->bulk($array);
    }
}