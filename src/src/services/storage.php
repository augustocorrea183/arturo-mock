<?php
/**
 * Created by PhpStorm.
 * User: acorrea
 * Date: 30/09/19
 * Time: 13:35
 */

namespace Services;

class Storage
{
    public function __construct ($storageType, $mongo, $hd) {
        $this->driver = ($storageType == 'mongo') ? $mongo : $hd;
    }

    public function save($data, $namespace, $directory=false) {
        return $this->driver->save($data, $namespace, $directory);
    }

    public function load($namespace, $directory=false) {
        return $this->driver->load($namespace, $directory);
    }

    public function delete($namespace, $directory=false) {
        return $this->driver->delete($directory . $namespace);
    }

    public function getFilesFromDirectory($directory) {
        return $this->driver->getFilesFromDirectory($directory);
    }

    public function deleteMockById($id) {
        return $this->driver->deleteMockById($id);
    }

    public function getAllMocks() {
        return $this->driver->getAllMocks();
    }

    public function getMockById($id) {
        return $this->driver->getMockById($id);
    }

    public function update($id, $json) {
        return $this->driver->update($id, $json);
    }

    public function add($mock) {
        return $this->driver->add($mock);
    }

    public function getMockByFileName($namespace) {
        return $this->driver->getMockByFileName($namespace);
    }

    public function getProxiesMocks() {
        $mocks = $this->getAllMocks();
        $response = [];
        foreach ($mocks as $mock) {
            if($mock['state'] === 'proxy' && substr($mock['url'], -1) === '*') {
                $response[] = $mock;
            }
        }
        return $response;
    }

}