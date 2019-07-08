<?php

namespace needletail\needletail\services;

use craft\base\Component;
use needletail\needletail\Needletail;

class Query extends Component
{
    private $connection;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->connection = Needletail::$plugin->connection;
    }

    public function usingKey($publicApiKey)
    {
        $this->connection->setClient($publicApiKey);

        return $this;
    }

    public function search($bucket, $params = [])
    {
        $prefix = Needletail::$plugin->settings->getBucketPrefix();
        return Needletail::$plugin->connection->search($prefix.$bucket, $params)->toArray();
    }
}