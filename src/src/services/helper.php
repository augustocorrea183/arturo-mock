<?php
/**
 * Created by PhpStorm.
 * User: acorrea
 * Date: 30/09/19
 * Time: 13:35
 */

namespace Services;

class Helper
{

    public function getPossibleProxy($allProxiesMocks, $server) {
        foreach ($allProxiesMocks as $ProxyMock) {
            // Remove wildcard
            $url = substr_replace($ProxyMock['url'], '', -1);
            $mockHash = md5(strtolower($url . $ProxyMock['method']));
            // Check conditions
            $pointer = '';
            $tail = '';
            $match = false;
            for ($i=0;$i<strlen($server['REQUEST_URI']);$i++) {
                $pointer .= $server['REQUEST_URI'][$i];
                $partialHash = md5(strtolower($pointer . $server['REQUEST_METHOD']));
                if ($match) {
                    $tail .= $server['REQUEST_URI'][$i];
                }
                if ($mockHash === $partialHash) {
                    $match = true;
                }
            }
        }
        if ($match) {
            $ProxyMock['url'] = $ProxyMock['url'] . $tail;
            return $ProxyMock;
        } else {
            return false;
        }

    }

}