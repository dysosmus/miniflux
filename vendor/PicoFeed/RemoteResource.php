<?php

namespace PicoFeed;

class RemoteResource
{
    public $user_agent;
    public $timeout;
    public $url;
    public $etag;
    public $last_modified;
    public $is_modified = true;
    public $content = '';


    public function __construct($url, $timeout = 5, $user_agent = 'PicoFeed (https://github.com/fguillot/picoFeed)')
    {
        $this->url = $url;
        $this->timeout = $timeout;
        $this->user_agent = $user_agent;

        return $this;
    }


    public function setLastModified($last_modified)
    {
        $this->last_modified = $last_modified;
        return $this;
    }


    public function getLastModified()
    {
        return $this->last_modified;
    }


    public function setEtag($etag)
    {
        $this->etag = $etag;
        return $this;
    }


    public function getEtag()
    {
        return $this->etag;
    }


    public function getUrl()
    {
        return $this->url;
    }


    public function getContent()
    {
        return $this->content;
    }


    public function isModified()
    {
        return $this->is_modified;
    }


    public function execute()
    {
        $response = $this->makeRequest();

        $this->etag = isset($response['headers']['ETag']) ? $response['headers']['ETag'] : '';
        $this->last_modified = isset($response['headers']['Last-Modified']) ? $response['headers']['Last-Modified'] : '';

        if ($response['status'] == 304) {

            $this->is_modified = false;
        }
        else if ($response['status'] == 301 || $response['status'] == 302) {

            if (isset($response['headers']['Location'])) {

                $this->url = $response['headers']['Location'];
            }
            else if (isset($response['headers']['location'])) {

                $this->url = $response['headers']['location'];
            }

            $this->execute();
        }
        else {

            $this->content = $response['body'];
        }
    }


    public function makeRequest()
    {
        $http_code = 200;
        $http_body = '';
        $http_headers = array();

        if (! function_exists('curl_init')) {

            $http_body = @file_get_contents($this->url);
        }
        else {

            $headers = array('Connection: close');

            if ($this->etag) $headers[] = 'If-None-Match: '.$this->etag;
            if ($this->last_modified) $headers[] = 'If-Modified-Since: '.$this->last_modified;

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $this->url);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_ENCODING, '');

            // Don't check SSL certificates (for auto-signed certificates...)
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $http_response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $http_body = '';
            $http_headers = array();

            curl_close($ch);

            $lines = explode("\r\n", $http_response);
            $body_start = 0;
            $i = 0;

            foreach ($lines as $line) {

                if ($line === '') {

                    $body_start = $i;
                    break;
                }
                else if (($p = strpos($line, ':')) !== false) {

                    $key = substr($line, 0, $p);
                    $value = substr($line, $p + 1);

                    $http_headers[trim($key)] = trim($value);
                }

                $i++;
            }

            $http_body = implode("\r\n", array_splice($lines, $i + 1));
        }

        return array(
            'status' => $http_code,
            'body' => $http_body,
            'headers' => $http_headers
        );
    }
}