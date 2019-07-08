<?php

namespace needletail\needletail\services;

use craft\base\Component;
use Needletail\Bucket;
use Needletail\Client;
use needletail\needletail\Needletail;
use Needletail\NeedletailResult;

class Connection extends Component
{
    public $activeClient = null;

    /**
     * @return Client
     */
    public function getClient()
    {
        if ( $this->activeClient )
            return $this->activeClient;

        $this->setClient();

        return $this->activeClient;
    }

    public function setClient($apiReadKey = null, $apiWriteKey = null)
    {
        $this->activeClient = new \Needletail\Client(
            $apiReadKey ?? Needletail::$plugin->getSettings()->getApiReadKey(),
            $apiWriteKey ?? Needletail::$plugin->getSettings()->getApiWriteKey()
        );

        return $this;
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

    public function search($bucket, array $params)
    {
        $bucket = $this->getClient()->initBucket($bucket);

        return $bucket->search($params);
    }

    public function bulk($bucket, $array = [])
    {
        $bucket = $this->getClient()->initBucket($bucket);
        return $bucket->bulk($array);
    }

    public function update($bucket, $data)
    {
        $id = $data['id'];
        unset($data['id']);
        $bucket = $this->getClient()->initBucket($bucket);
        return $bucket->updateDocument($id, $data);
    }

    public function delete($bucket, $id)
    {
        $bucket = $this->getClient()->initBucket($bucket);
        return $bucket->deleteDocument($id);
    }
}