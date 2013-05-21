<?php

namespace PicoFeed;

require_once __DIR__.'/Filter.php';


abstract class Parser
{
    protected $content = '';

    public $id = '';
    public $url = '';
    public $title = '';
    public $updated = '';
    public $items = array();
    public $debug = false;


    abstract public function execute();


    public function __construct($content)
    {
        $this->content = $content;
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


    public function displayXmlErrors()
    {
        foreach(\libxml_get_errors() as $error) {

            printf("Message: %s\nLine: %d\nColumn: %d\nCode: %d\n",
                $error->message,
                $error->line,
                $error->column,
                $error->code
            );
        }
    }


    // Dirty quickfix before XML parsing
    public function normalizeData($data)
    {
        return str_replace("\xc3\x20", '', $data);
    }
}
