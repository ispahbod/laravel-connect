<?php

namespace Ispahbod\Connect\Method;

use DOMDocument;
use DOMXPath;
use Error;
use Symfony\Component\DomCrawler\Crawler;

class Response
{
    private array $responses;

    public function __construct($responses = [])
    {
        if (is_array($responses)) {
            $this->responses = $responses;
        } else {
            $this->responses = [$responses];
        }

    }

    public function operations($response, $operations)
    {
        try {
            $result = $response;
            $mappings = [
                'body' => function ($result) {
                    return $result->getBody();
                },
                'content' => function ($result) {
                    return $result->getBody()->getContents() ?? '';
                },
                'array' => function ($result) {
                    return json_decode($result, true);
                },
                'decode' => function ($result) {
                    return json_decode($result);
                },
                'xpath' => function ($result) {
                    $dom = new DOMDocument();
                    @$dom->loadHTML($result);
                    return new DOMXPath($dom);
                },
                'crawler' => function ($result) {
                    if (!is_string($result)) {
                        return new Crawler($result->getBody()->getContents());
                    }
                    return new Crawler($result);
                },
            ];
            foreach ($operations as $operation) {
                if (isset($mappings[$operation])) {
                    $result = $mappings[$operation]($result);
                }
            }
            return $result;
        } catch (Error) {
            return false;
        }
    }


    public function first(...$operations)
    {
        $response = $this->responses[0] ?? null;
        return $this->operations($response, $operations);
    }

    public function end(...$operations)
    {
        $response = end($this->responses);
        return $this->operations($response, $operations);

    }

    public function item(int $item, ...$operations)
    {
        $response = $this->responses[$item];
        return $this->operations($response, $operations);
    }

    public function eachOperations(...$operations): array
    {
        $responses = [];
        foreach ($this->responses as $response) {
            $responses[] = $this->operations($response, $operations);
        }
        if (in_array('merge', $operations)) {
            $combinedArray = [];
            foreach ($responses as $subArray) {
                $combinedArray = array_merge($combinedArray, $subArray);
            }
            return $combinedArray;
        }

        return $responses;
    }

    public function responses(){
        return $this->responses;
    }
    public function each(callable $callback)
    {
        foreach ($this->responses as $key => $response) {
            $this->responses[$key] = $callback($key, $response);
        }
        return $this;
    }
}