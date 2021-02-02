<?php

namespace lbs\catalogue\conf;

class MongoConnection {

    static private $connection;

    static public function createConnexion(){
        self::$connection = (new \MongoDB\Client("mongodb://dbcat/"));
    }

    static public function getCatalogue(){
        return self::$connection->catalogue;
    }

    

}