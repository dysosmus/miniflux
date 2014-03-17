<?php

namespace PicoFeed;

require_once __DIR__.'/Logging.php';
require_once __DIR__.'/Filter.php';
require_once __DIR__.'/Encoding.php';
require_once __DIR__.'/Grabber.php';

/**
 * Base parser class
 *
 * @author  Frederic Guillot
 * @package parser
 */
abstract class Parser
{
    /**
     * Hash algorithm used to generate item id, any value supported by PHP, see hash_algos()
     *
     * @access public
     * @static
     * @var string
     */
    public static $hashAlgo = 'crc32b'; // crc32b seems to be faster and shorter than other hash algorithms

    /**
     * Feed content (XML data)
     *
     * @access protected
     * @var string
     */
    protected $content = '';

    /**
     * Feed properties (values parsed)
     *
     * @access public
     */
    public $id = '';
    public $url = '';
    public $title = '';
    public $updated = '';
    public $language = '';
    public $items = array();

    /**
     * Content grabber parameters
     *
     * @access public
     */
    public $grabber = false;
    public $grabber_ignore_urls = array();
    public $grabber_timeout = null;
    public $grabber_user_agent = null;

    /**
     * Parse feed content
     *
     * @abstract
     * @access public
     * @return mixed
     */
    abstract public function execute();

    /**
     * Constructor
     *
     * @access public
     * @param  string  $content        Feed content
     * @param  string  $http_encoding  HTTP encoding (headers)
     */
    public function __construct($content, $http_encoding = '')
    {
        $xml_encoding = Filter::getEncodingFromXmlTag($content);
        Logging::log(\get_called_class().': HTTP Encoding "'.$http_encoding.'" ; XML Encoding "'.$xml_encoding.'"');

        // Strip XML tag to avoid multiple encoding/decoding in the next XML processing
        $this->content = Filter::stripXmlTag($content);

        // Encode everything in UTF-8
        if ($xml_encoding == 'windows-1251' || $http_encoding == 'windows-1251') {
            $this->content = Encoding::cp1251ToUtf8($this->content);
        }
        else {
            $this->content = Encoding::toUTF8($this->content);
        }

        // Workarounds
        $this->content = $this->normalizeData($this->content);
    }

    /**
     * Filter HTML for entry content
     *
     * @access public
     * @param  string  $item_content  Item content
     * @param  string  $item_url      Item URL
     * @return string                 Filtered content
     */
    public function filterHtml($item_content, $item_url)
    {
        $content = '';

        if ($this->grabber && ! in_array($item_url, $this->grabber_ignore_urls)) {
            $grabber = new Grabber($item_url);
            $grabber->download($this->grabber_timeout, $this->grabber_user_agent);
            if ($grabber->parse()) $item_content = $grabber->content;
        }

        if ($item_content) {
            $filter = new Filter($item_content, $item_url);
            $content = $filter->execute();
        }

        return $content;
    }

    /**
     * Get XML parser errors
     *
     * @access public
     * @return string
     */
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

    /**
     * Dirty quickfixes before XML parsing
     *
     * @access public
     * @param  string  $data Raw data
     * @return string        Normalized data
     */
    public function normalizeData($data)
    {
        $data = str_replace("\xc3\x20", '', $data);
        $data = $this->replaceEntityAttribute($data);
        return $data;
    }

    /**
     * Replace & by &amp; for each href attribute (Fix broken feeds)
     *
     * @access public
     * @param  string  $content Raw data
     * @return string           Normalized data
     */
    public function replaceEntityAttribute($content)
    {
        $content = preg_replace_callback('/href="[^"]+"/', function(array $matches) {
            return htmlspecialchars($matches[0], ENT_NOQUOTES, 'UTF-8', false);
        }, $content);

        return $content;
    }

    /**
     * Trim whitespace from the begining, the end and inside a string and don't break utf-8 string
     *
     * @access public
     * @param  string  $value  Raw data
     * @return string          Normalized data
     */
    public function stripWhiteSpace($value)
    {
        $value = str_replace("\r", "", $value);
        $value = str_replace("\t", "", $value);
        $value = str_replace("\n", "", $value);
        return trim($value);
    }

    /**
     * Generate a unique id for an entry (hash all arguments)
     *
     * @access public
     * @param  string  $args  Pieces of data to hash
     * @return string         Id
     */
    public function generateId()
    {
        return hash(self::$hashAlgo, implode(func_get_args()));
    }

    /**
     * Try to parse all date format for broken feeds
     *
     * @access public
     * @param  string  $value  Original date format
     * @return integer         Timestamp
     */
    public function parseDate($value)
    {
        // Format => truncate to this length if not null
        $formats = array(
            DATE_ATOM => null,
            DATE_RSS => null,
            DATE_COOKIE => null,
            DATE_ISO8601 => null,
            DATE_RFC822 => null,
            DATE_RFC850 => null,
            DATE_RFC1036 => null,
            DATE_RFC1123 => null,
            DATE_RFC2822 => null,
            DATE_RFC3339 => null,
            'D, d M Y H:i:s' => 25,
            'D, d M Y h:i:s' => 25,
            'D M d Y H:i:s' => 24,
            'Y-m-d H:i:s' => 19,
            'Y-m-d\TH:i:s' => 19,
            'd/m/Y H:i:s' => 19,
            'D, d M Y' => 16,
            'Y-m-d' => 10,
            'd-m-Y' => 10,
            'm-d-Y' => 10,
            'd.m.Y' => 10,
            'm.d.Y' => 10,
            'd/m/Y' => 10,
            'm/d/Y' => 10,
        );

        $value = trim($value);

        foreach ($formats as $format => $length) {

            $truncated_value = $value;
            if ($length !== null) {
                $truncated_value = substr($truncated_value, 0, $length);
            }

            $timestamp = $this->getValidDate($format, $truncated_value);
            if ($timestamp > 0) {
                return $timestamp;
            }
        }

        return time();
    }

    /**
     * Get a valid date from a given format
     *
     * @access public
     * @param  string  $format   Date format
     * @param  string  $value    Original date value
     * @return integer           Timestamp
     */
    public function getValidDate($format, $value)
    {
        $date = \DateTime::createFromFormat($format, $value);

        if ($date !== false) {
            $errors = \DateTime::getLastErrors();
            if ($errors['error_count'] === 0 && $errors['warning_count'] === 0) return $date->getTimestamp();
        }

        return 0;
    }

    /**
     * Hardcoded list of hostname/token to exclude from id generation
     *
     * @access public
     * @param  string  $url  URL
     * @return boolean
     */
    public function isExcludedFromId($url)
    {
        $exclude_list = array('ap.org', 'jacksonville.com');

        foreach ($exclude_list as $token) {
            if (strpos($url, $token) !== false) return true;
        }

        return false;
    }

    /**
     * Get xml:lang value
     *
     * @access public
     * @param  string  $xml  XML string
     * @return string        Language
     */
    public function getXmlLang($xml)
    {
        $dom = new \DOMDocument;
        $dom->loadXML($this->content);

        $xpath = new \DOMXPath($dom);
        return $xpath->evaluate('string(//@xml:lang[1])') ?: '';
    }

    /**
     * Return true if the given language is "Right to Left"
     *
     * @static
     * @access public
     * @param  string  $language  Language: fr-FR, en-US
     * @return bool
     */
    public static function isLanguageRTL($language)
    {
        $language = strtolower($language);

        // Arabic (ar-**)
        if (strpos($language, 'ar') === 0) return true;

        // Farsi (fa-**)
        if (strpos($language, 'fa') === 0) return true;

        // Urdu (ur-**)
        if (strpos($language, 'ur') === 0) return true;

        // Pashtu (ps-**)
        if (strpos($language, 'ps') === 0) return true;

        // Syriac (syr-**)
        if (strpos($language, 'syr') === 0) return true;

        // Divehi (dv-**)
        if (strpos($language, 'dv') === 0) return true;

        // Hebrew (he-**)
        if (strpos($language, 'he') === 0) return true;

        // Yiddish (yi-**)
        if (strpos($language, 'yi') === 0) return true;

        return false;
    }
}
