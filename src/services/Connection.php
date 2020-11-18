<?php

namespace needletail\needletail\services;

use craft\base\Component;
use Needletail\Entities\Bucket;
use Needletail\Client;
use needletail\needletail\Needletail;

class Connection extends Component
{
    public $readClient = null;
    public $writeClient = null;

    /**
     * @return Client
     */
    public function getReadClient()
    {
        if (! $this->readClient )
            $this->setReadClient();

        return $this->readClient;
    }

    public function getWriteClient()
    {
        if (! $this->writeClient )
            $this->setWriteClient();

        return $this->writeClient;
    }

    public function setReadClient($apiKey = null)
    {
        $this->readClient = new \Needletail\Client(
            $apiKey ?? Needletail::$plugin->getSettings()->getApiReadKey()
        );

        return $this;
    }

    public function setWriteClient($apiKey = null)
    {
        $this->writeClient = new \Needletail\Client(
            $apiKey ?? Needletail::$plugin->getSettings()->getApiWriteKey()
        );

        return $this;
    }

    public function initBucket($name)
    {
        $bucket = (new Bucket())
            ->setName($name);

        return $this->getWriteClient()->buckets()->create($bucket) instanceof Bucket;
    }

    /**
     * List all
     * @return array
     */
    public function listBuckets()
    {
       return $this->getReadClient()->buckets()->all();
    }

    public function search(array $params)
    {
        return $this->getReadClient()->search($params);
    }

    public function bulk($name, array $params = [])
    {
        $bucket = $this->getWriteClient()->buckets()->find($name);

        return $bucket->bulkDocuments()->create($params);
    }

    public function update($name, $data)
    {
        $bucket = $this->getWriteClient()->buckets()->find($name);
        return $bucket->documents()->create($data);
    }

    public function delete($name, $id)
    {
        $bucket = $this->getWriteClient()->buckets()->find($name);
        return $bucket->documents()->destroy($id);
    }
}