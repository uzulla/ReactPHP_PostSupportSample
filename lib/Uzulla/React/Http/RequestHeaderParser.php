<?php

namespace Uzulla\React\Http;

use Evenement\EventEmitter;
use Guzzle\Parser\Message\MessageParser;

/**
 * @event headers
 * @event error
 */
class RequestHeaderParser extends EventEmitter
{
    private $buffer = '';
    private $maxSize = 4096000; // max post length as you like

    private $contentLength = 0;
    private $headerStr = '';
    private $headerRead=false;

    public function feed($data)
    {
        if (strlen($this->buffer) + strlen($data) > $this->maxSize) {
            $this->emit('error', array(new \OverflowException("Maximum header size of {$this->maxSize} exceeded."), $this));

            return;
        }

        $this->buffer .= $data;

        $match = [];
        if(1===preg_match('|Content-Length:[ ]?([0-9]+)|ui', $this->buffer, $match)){
            $this->contentLength = (int)$match[1];
        }

        if (false !== strpos($this->buffer, "\r\n\r\n")) {
            list($headerStr, $bodyBuffer) = explode("\r\n\r\n", $data, 2);
            $this->headerStr = $headerStr;
            $this->buffer = $bodyBuffer;
            $this->headerRead = true;
        }

        if($this->headerRead && strlen($this->buffer) >= $this->contentLength){
            list($request, $bodyBuffer) = $this->parseRequest($this->headerStr, $this->buffer);

            $this->emit('headers', array($request, $bodyBuffer));
            $this->removeAllListeners();
        }
    }

    public function parseRequest($headers, $bodyBuffer)
    {
        $parser = new MessageParser();
        $parsed = $parser->parseRequest($headers);

        $parsedQuery = array();
        if ($parsed['request_url']['query']) {
            parse_str($parsed['request_url']['query'], $parsedQuery);
        }

        $parsedParams = [];
        if (strlen($bodyBuffer)>0){
            parse_str($bodyBuffer, $parsedParams);
        }

        $request = new \Uzulla\React\Http\Request(
            $parsed['method'],
            $parsed['request_url']['path'],
            $parsedQuery,
            $parsedParams,
            $parsed['version'],
            $parsed['headers']
        );

        return array($request, $bodyBuffer);
    }
}
