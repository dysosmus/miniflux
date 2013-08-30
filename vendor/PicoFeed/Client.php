<?php

namespace PicoFeed;

require_once __DIR__.'/Logging.php';

abstract class Client
{
    public $etag = '';
    public $last_modified = '';
    public $is_modified = true;
    public $content = '';
    public $url = '';
    public $timeout = 10;
    public $max_redirects = 5;
    public $max_body_size = 2097152; // 2MB
    public $user_agent = 'PicoFeed (https://github.com/fguillot/picoFeed)';


    public static function create($adapter = null)
    {
        return $adapter ?: self::chooseAdapter();
    }


    public static function chooseAdapter()
    {
        if (function_exists('curl_init')) {

            require_once __DIR__.'/Clients/Curl.php';
            return new Clients\Curl;

        } else if (ini_get('allow_url_fopen')) {

            require_once __DIR__.'/Clients/Stream.php';
            return new Clients\Stream;
        }

        throw new \LogicException('You must have "allow_url_fopen=1" or curl extension installed');
    }


    public function execute()
    {
        if ($this->url === '') {
            throw new \LogicException('The URL is missing');
        }

        Logging::log(\get_called_class().' Fetch URL: '.$this->url);
        Logging::log(\get_called_class().' Etag provided: '.$this->etag);
        Logging::log(\get_called_class().' Last-Modified provided: '.$this->last_modified);

        $response = $this->doRequest();

        if (is_array($response)) {

            if ($response['status'] == 304) {
                $this->is_modified = false;
                Logging::log(\get_called_class().' Resource not modified');
            }
            else if ($response['status'] == 404) {
                Logging::log(\get_called_class().' Resource not found');
            }
            else {
                $this->etag = isset($response['headers']['ETag']) ? $response['headers']['ETag'] : '';
                $this->last_modified = isset($response['headers']['Last-Modified']) ? $response['headers']['Last-Modified'] : '';
                $this->content = $response['body'];
            }
        }
    }


    public function parseHeaders(array $lines)
    {
        $status = 200;
        $headers = array();

        foreach ($lines as $line) {

            if (strpos($line, 'HTTP') === 0/* && strpos($line, '301') === false && strpos($line, '302') === false*/) {
                $status = (int) substr($line, 9, 3);
            }
            else if (strpos($line, ':') !== false) {

                @list($name, $value) = explode(': ', $line);
                if ($value) $headers[trim($name)] = trim($value);
            }
        }

        Logging::log(\get_called_class().' HTTP status code: '.$status);

        foreach ($headers as $name => $value) {
            Logging::log(\get_called_class().' HTTP header: '.$name.' => '.$value);
        }

        return array($status, $headers);
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
}