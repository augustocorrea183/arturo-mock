<?php
/**
 * Created by PhpStorm.
 * User: acorrea
 * Date: 30/09/19
 * Time: 13:35
 */

namespace Repositories;

class Mongo
{
    private $mongo;
    private $database;

    public function __construct ($mongo, $database) {
        $this->mongo = $mongo;
        $this->database = $database;
    }

    public function getAllOrderMocks() {
        $files = $this->getFilesFromDirectory();
        $response = [];
        foreach ($files as $file) {
            $response[] = $this->load($file);
        }
        return $response;
    }

    public function save($entry, $collection, $directory=false) {
        if(isset($entry['_id'])) {
            unset($entry['_id']);
        }
        $database = $this->database;
        return $this->mongo->$database->$collection->insertOne($entry);
    }

    public function load($collection, $directory=false) {
        $database = $this->database;
        $cursor = $this->mongo->$database->$collection->find([]);
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);
        $data = $cursor->toArray();
        return $data;
    }

    public function deleteCollection($collection) {
        $database = $this->database;
        return $this->mongo->$database->dropCollection($collection);
    }

    public function deleteMockById ($id) {
        $database = $this->database;
        $collections = $this->getFilesFromDirectory();
        $result = null;
        foreach ($collections as $collection) {
            $documents = $this->load($collection);
            foreach ($documents as $document) {
                if($document['id'] == $id) {
                    $result = $this->mongo->$database->$collection->deleteOne(
                        [
                            '_id' => new \MongoDB\BSON\ObjectId($document['_id']),
                            'id' => $id
                        ]
                    );
                    if(count($documents) === 1) {
                        $this->deleteCollection($collection);
                    }
                }
            }
        }
        return $result;
    }

    public function getFilesFromDirectory($directory=false) {
        $database = $this->database;
        $collections = $this->mongo->$database->listCollections();
        $collectionList = [];
        foreach ($collections as $collection) {
            $collectionList[] = $collection->getName();
        }
        return $collectionList;
    }

    protected function getFileNameByMock($mock) {
        $url = strtolower($mock['url'].$mock['method']);
        return md5($url);
    }

    public function validateNotUniqueEnabled($collection, $newMock, $id=null) {
        $mocks = $this->load($collection);
        if(!$mocks) {
            return false;
        }
        foreach ($mocks as $mock) {
            if($mock['state'] !== 'disable' && $mock['id'] === $id) {
                return false;
            }
        }
        foreach ($mocks as $mock) {
            if($mock['state'] !== 'disable' && $newMock['state'] !== 'disable') {
                return true;
            }
        }
        return false;
    }

    public function add($mock, $id=null) {
        if(!isset($mock['url']) || !isset($mock['method'])) {
            return ['error' => 'Please, url and method are mandatory'];
        }
        $collection = $this->getFileNameByMock($mock);
        if($this->validateNotUniqueEnabled($collection, $mock, $id)) {
            return ['error' => 'There should only be one action enable|proxy|record'];
        }
        $mock['id'] = $this->getId();
        $data = $this->encodePayload($mock);
        $this->save($data, $collection);
        return $mock;
    }

    public function update($id, $json) {
        $mock = $this->reformatMock(json_decode($json, true));
        $collection = $this->getFileNameByMock($mock);
        if($this->validateNotUniqueEnabled($collection, $mock, $id)) {
            return ['error' => 'There should only be one action enable|proxy|record'];
        }
        $this->deleteMockById($id);
        $this->add(
            $mock,
            $id
        );
        return $mock;
    }

    protected function decodePayload($mock) {
        if($mock['payload'] == "") {
            return $mock;
        }
        $mock['payload'] = base64_decode($mock['payload']);
        return $mock;
    }

    protected function encodePayload($mock) {
        if($mock['payload'] == "") {
            return $mock;
        }
        $mock['payload'] = base64_encode($mock['payload']);
        return $mock;
    }

    public function getMockByFileName($collection) {
        $mocks = $this->load($collection);
        if (!$mocks) {
            return false;
        }
        foreach ($mocks as $mock) {
            if ($mock['state'] == 'enable' || $mock['state'] == 'proxy' || $mock['state'] == 'record') {
                return $this->decodePayload($mock);
            }
        }
    }

    public function getId() {
        return uniqid();
    }

    protected function reformatMock($mock) {
        $mock['statusCode'] = (int) $mock['statusCode'];
        $mock['id'] = (int) $mock['id'];
        return $mock;
    }

    public function getAllMocks() {
        $files = $this->getFilesFromDirectory();
        $response = [];
        foreach ($files as $file) {
            $mocks = $this->load($file);
            foreach ($mocks as $mock) {
                $response[] = $this->decodePayload($mock);
            }
        }
        return $response;
    }

    public function getMockById($id) {
        $mocks = $this->getAllMocks();
        foreach ($mocks as $mock) {
            if ($mock['id'] == $id) {
                return $mock;
            }
        }
        return false;
    }

}