<?php
/**
 * Created by PhpStorm.
 * User: acorrea
 * Date: 30/09/19
 * Time: 13:35
 */

namespace Repositories;

class Hd
{
    private $defaultStorageDirectory;

    public function __construct ($defaultStorageDirectory) {
        $this->defaultStorageDirectory = $defaultStorageDirectory;
    }

    public function save($data, $fileName, $directory=false) {
        $data = json_encode($data);
        $directory = ($directory) ? $directory : $this->defaultStorageDirectory;
        return file_put_contents($directory . $fileName, $data);
    }

    public function load($fileName, $directory=false) {
        $directory = ($directory) ? $directory : $this->defaultStorageDirectory;
        if (file_exists($directory . $fileName)) {
            $data = file_get_contents($directory . $fileName);
            return json_decode($data, true);
        }
        return false;
    }

    public function delete($fileName, $directory=false) {
        $directory = ($directory) ? $directory : $this->defaultStorageDirectory;
        return unlink($directory . $fileName);
    }

    public function getFilesFromDirectory($directory) {
        $dir = [];
        $d = dir($directory);
        while (false !== ($entry = $d->read())) {
            if($entry !== '..' && $entry !== '.') {
                $dir[] = $entry;
            }
        }
        $d->close();
        return $dir;
    }

    public function deleteMockById($id) {
        $mockList = $this->getAllOrderMocks();
        $deleted = false;
        foreach ($mockList as $listKey => $list) {
            foreach ($list as $mockKey => $mock) {
                if($mock['id'] == $id) {
                    $deleted = $mockList[$listKey][$mockKey];
                    unset($mockList[$listKey][$mockKey]);
                    $fileName = $this->getFileNameByMock($mock);
                    $result = $mockList[$listKey];
                    if(count($result) === 0) {
                        $this->delete($fileName);
                    } else {
                        $this->save($result, $fileName);
                    }
                }
            }
        }
        return $deleted;
    }

    public function getAllOrderMocks() {
        $files = $this->getFilesFromDirectory($this->defaultStorageDirectory);
        $response = [];
        foreach ($files as $file) {
            $response[] = $this->load($file);
        }
        return $response;
    }

    protected function getFileNameByMock($mock) {
        $url = strtolower($mock['url'].$mock['method']);
        return md5($url);
    }

    public function validateNotUniqueEnabled($fileName, $newMock, $id=null) {
        $mocks = $this->load($fileName);
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
        $fileName = $this->getFileNameByMock($mock);
        if($this->validateNotUniqueEnabled($fileName, $mock, $id)) {
            return ['error' => 'There should only be one action enable|proxy|record'];
        }
        $data = $this->load($fileName);
        $mock['id'] = $this->getId();
        $data[] = $this->encodePayload($mock);
        $this->save($data, $fileName);
        return $mock;
    }

    public function update($id, $json) {
        $mock = $this->reformatMock(json_decode($json, true));
        $fileName = $this->getFileNameByMock($mock);
        if($this->validateNotUniqueEnabled($fileName, $mock, $id)) {
            return ['error' => 'There should only be one action enable|proxy|record'];
        }
        $this->deleteMockById($id);
        $this->add(
            $mock,
            $id
        );
        return $mock;
    }

    public function decodePayload($mock) {
        if($mock['payload'] == "") {
            return $mock;
        }
        $mock['payload'] = base64_decode($mock['payload']);
        return $mock;
    }

    public function encodePayload($mock) {
        if($mock['payload'] == "") {
            return $mock;
        }
        $mock['payload'] = base64_encode($mock['payload']);
        return $mock;
    }

    public function getMockByFileName($fileName) {
        $mocks = $this->load($fileName);
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
        $files = $this->getFilesFromDirectory($this->defaultStorageDirectory);
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