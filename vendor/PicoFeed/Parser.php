<?php

namespace PicoFeed;

require_once __DIR__.'/Logging.php';
require_once __DIR__.'/Filter.php';
require_once __DIR__.'/Encoding.php';

abstract class Parser
{
    protected $content = '';

    public $id = '';
    public $url = '';
    public $title = '';
    public $updated = '';
    public $items = array();


    abstract public function execute();


    public function __construct($content)
    {
        // Strip XML tag to avoid multiple encoding/decoding in next XML processing
        $this->content = $this->stripXmlTag($content);

        // Encode everything in UTF-8
        $this->content = Encoding::toUTF8($this->content);

        // Workarounds
        $this->content = $this->normalizeData($this->content);
    }


    public function filterHtml($str, $item_url)
    {
        $content = '';

        if ($str) {

            $filter = new Filter($str, $item_url);
            $content = $filter->execute();
        }

        return $content;
    }


    public function getXmlErrors()
    {
        $errors = array();

        foreach(\libxml_get_errors() as $error) {

            $errors[] = sprintf('XML error: %s (Line: %d - Column: %d - Code: %d)',
                $error->message,
                $error->line,
                $error->column,
                $error->code
            );
        }

        return implode(', ', $errors);
    }


    // Dirty quickfix before XML parsing
    public function normalizeData($data)
    {
        return str_replace("\xc3\x20", '', $data);
    }


    public function stripXmlTag($data)
    {
        if (strpos($data, '<?xml') !== false) {

            $data = substr($data, strrpos($data, '?>') + 2);
        }

        return $data;
    }


    // Trim whitespace from the begining, the end and inside a string and don't break utf-8 string
    public function stripWhiteSpace($value)
    {
        $value = str_replace("\r", "", $value);
        $value = str_replace("\t", "", $value);
        $value = str_replace("\n", "", $value);
        return trim($value);
    }
}
