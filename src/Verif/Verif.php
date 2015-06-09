<?php
namespace Verif;

class Verif
{
    public $protocol = 'http://';
    public $host = 'localhost/';
    public $prefix = '';
    public $path = '';
    public $content = false;
    public $errors = [];
    public $uri;
    public $report_title;

    public function __construct($title)
    {
        $this->report_title = $title;
    }

    /**
     * Set the domain we will query
     *
     * @param string $host Domain name, /ex: www.foo.bar
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;
        $this->setURI();

        return $this;
    }

    /**
     * Set the protocol (http, https...) we will use
     *
     * @param string $protocol Protocol used
     * @return $this
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol . '://';
        $this->setURI();

        return $this;
    }

    /**
     * Set the path that prefix that will be prepended to the path
     *
     * @param string $prefix Prefix for the path
     * @return $this
     */
    public function setPathPrefix($prefix)
    {
        $this->prefix = $prefix;
        $this->setURI();

        return $this;
    }

    /**
     * Set the path that will be appended to the domain name
     *
     * @param string $path Path of the query, will be appended to the Host
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        $this->setURI();

        return $this;
    }

    /**
     * Set the full URI
     */
    protected function setURI()
    {
        $this->uri = $this->protocol . $this->host . '/' . $this->prefix . $this->path;
    }

    /**
     * Manually set an error
     */
    public function setError($message)
    {
        $this->errors[] = $message;

        return $this;
    }

    /**
     * Fetch the content at the specified URI  and stores that in $content
     *
     * @return $this
     */
    public function fetchContent()
    {
        // Set stream options
        $opts = [
          'http' => ['ignore_errors' => true]
        ];

        // Create the stream context
        $context = stream_context_create($opts);

        // Open the file using the defined context
        $this->content = file_get_contents($this->uri, false, $context);

        return $this;
    }

    /**
     * Get the content stored in cache
     *
     * @return string Remote content that was fetched and cached
     */
    public function getContent()
    {
        return $this->content;
    }
    /**
     * Get the HTTP response code with a GET call
     *
     * @return int The response code
     */
    public function getHTTPResponseCode()
    {
        $headers = get_headers($this->uri);

        return (int) substr($headers[0], 9, 3);
    }

    /**
     * Return the content currently in cache
     *
     * @return mixed The content as a string or False
     */
    public function getCachedContent()
    {
        return $this->content;
    }

    /**
     * Check if the resource is Json data
     * If False, an error will be added to the error stack
     *
     * @return boolean True if Json, False otherwise
     */
    public function isJSON()
    {
        if ($this->content == false) {
            $this->fetchContent();
        }

        $data   = json_decode($this->content);
        $status = is_string($this->content)
            && (is_object($data) or is_array($data))
            && json_last_error() == JSON_ERROR_NONE;

        if (! $status) {
            $this->errors[] =  $this->uri . ' is not valid Json';
        }

        return $status;
    }

    /**
     * Check that the HTTP response code is the one expected
     * @param  int      $code HTTP code such as 200, 301, 404…
     * @return boolean        True if matches, False is not
     */
    public function hasResponseCode($code)
    {
        if ($code == $this->getHTTPResponseCode()) {
            return true;
        }

        $this->errors[] = $this->uri . ' HTTP expected: ' . $code . ' HTTP: received: ' . $this->getHTTPResponseCode();

        return false;
    }

    /**
     * Get a report of errors
     *
     * @return int Return 0 if no error, 1 if there are errors (useful for Travis)
     */
    public function report()
    {
        $title = 'Report for ' . $this->report_title . "\n";
        $delimiter = str_repeat("-", strlen($title)) . "\n";
        print $delimiter;
        print $title;
        print $delimiter;

        if (empty($this->errors)) {
            print "All tests processed without errors\n\n";
            return 0;
        }

        print "Error:\n";
        foreach ($this->errors as $error) {
            print $error . "\n";
        }
        print "\n";

        return 1;
    }
}
