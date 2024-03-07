<?php

namespace Ispahbod\Connect\Method;

trait Getter
{
    public function dcct($string)
    {
        $this->decode_content = $string;
        return $this;
    }

    public function content()
    {
        $array = [];
        if (!$this->responses) {
            return $this;
        }
        foreach ($this->responses as $response) {
            $decodedResponse = $response->getBody()->getContents();
            $array[] = $decodedResponse;
        }
        $this->responses = $array;
        return $this;
    }

    public function body()
    {
        $array = [];
        foreach ($this->responses as $response) {
            $decodedResponse = $response->getBody();
            $array[] = $decodedResponse;
        }
        $this->responses = $array;
        return $this;
    }


    public function toArray()
    {
        if (!$this->responses) {
            return $this;
        }
        if ($this->break) {
            return $this->response;
        }
        $array = [];
        foreach ($this->responses as $response) {
            $decodedResponse = json_decode($response, true);
            $array[] = $decodedResponse;
        }
        $this->responses = $array;
        return $this;
    }

    public function toDecode()
    {
        if ($this->break) {
            return $this->response;
        }
        $array = [];
        foreach ($this->responses as $response) {
            $decodedResponse = json_decode($response);
            $array[] = $decodedResponse;
        }
        $this->responses = $array;
        return $this;
    }

    public function statusCode()
    {
        $response = $this->response->getStatusCode();
        return $response;
    }
    public function getTime()
    {
        $event = $this->time;
        if ($event) {
            $executionTimeMillis = $event->getDuration();
            $executionTimeSeconds = $executionTimeMillis / 1000;
            $executionTimeDecimalSeconds = number_format($executionTimeSeconds, 3);
            return $executionTimeDecimalSeconds;
        }
    }

    public function error()
    {
        return $this->errors;
    }
}