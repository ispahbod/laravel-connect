<?php

namespace Ispahbod\Connect\Method;

trait Setter
{
    protected $base = '';
    protected $decode_content = '';
    private $time = 0;
    protected $loop = 1;
    protected $intval = 15;
    protected $timeout = 30;
    protected $idle = 10;
    protected $async = false;
    protected $withRedirect = false;
    protected $http_errors = false;
    protected $multiple = false;
    protected $verify = false;
    protected $compress = true;
    protected $alive = true;
    protected $url = [];
    protected $response = [];
    protected $errors = [];
    protected $headers = [];
    protected $json = [];
    protected $query = [];
    protected $multipart = [];
    protected $form_params = [];
    protected $responses = [];
    protected $stream = [];
    protected $body = [];
    protected $method = [];

    public function config(array $array)
    {
        foreach ($array as $key => $value) {
            switch ($key) {
                case 'method':
                    $this->method($value);
                    break;
                case 'url':
                    $this->url($value);
                    break;
                case 'headers':
                    $this->headers = $value;
                    break;
                case 'loop':
                    $this->loop = $value;
                case 'async':
                    $this->async = $value;
                case 'http_errors':
                    $this->http_errors = $value;
                case 'timeout':
                    $this->timeout = $value;
                case 'alive':
                    $this->alive = $value;
                case 'idle':
                    $this->idle = $value;
                case 'verify':
                    $this->verify = $value;
                case 'compress':
                    $this->compress = $value;
            }
        }
        return $this;
    }

    public function base(string $url)
    {
        $this->base = urldecode($url);
        return $this;
    }

    public function method(...$methods)
    {
        $this->method[] = (count($methods) == 1)
            ? strtoupper($methods[0])
            : array_map('strtoupper', $methods);

        return $this;
    }

    public function post($url = '')
    {
        $this->method[] = strtoupper('post');
        return $this;
    }

    public function put()
    {
        $this->method[] = strtoupper('put');
        return $this;
    }

    public function url(...$urls)
    {
        if (count($urls) == 1 && is_array($urls[0])) {
            $array = array_map('urldecode', $urls[0]);
            foreach ($array as $row) {
                $this->url[] = $row;
            }
        } else {
            $array = array_map('urldecode', $urls);
            foreach ($array as $row) {
                $this->url[] = $row;
            }
        }
        return $this;
    }

    public function withHeaders(...$headers)
    {
        foreach ($headers as $header) {
            $this->headers[] = $header;
        }
        return $this;
    }

    public function timeout(...$timeout)
    {
        $this->timeout = (count($timeout) == 1)
            ? $timeout[0]
            : $timeout;
        return $this;
    }

    public function verify(bool $verify = true)
    {
        $this->verify = $verify;
        return $this;
    }

    public function compress(bool $compress = true)
    {
        $this->compress = $compress;
        return $this;
    }

    public function async(bool $async = true)
    {
        $this->async = $async;
        return $this;
    }

    public function withRedirect(int $max = -1, bool $strict = false, bool $referer = true, bool $track_redirects = true, callable $on_redirect = null)
    {
        $this->withRedirect = [
            'max' => $max,
            'strict' => $strict,
            'referer' => $referer,
            'track_redirects' => $track_redirects,
            'on_redirect' => true,
        ];
        return $this;
    }

    public function httpErrors(bool $error = true)
    {
        $this->http_errors = $error;
        return $this;
    }

    public function proxy(bool $proxy = true)
    {
        $this->proxy = $proxy;
        return $this;
    }

    public function emptyFilter()
    {
        $this->json = array_map('array_filter', $this->json);
        $this->query = array_map('array_filter', $this->query);
        $this->multipart = array_map('array_filter', $this->multipart);
        $this->form_params = array_map('array_filter', $this->form_params);
        $this->stream = array_map('array_filter', $this->stream);
        $this->body = array_map('array_filter', $this->body);
        return $this;
    }

    public function json(...$json)
    {
        $this->json[] = (count($json) == 1)
            ? $json[0]
            : $json;
        return $this;
    }

    public function query(...$query)
    {
        $this->query[] = (count($query) == 1)
            ? $query[0]
            : $query;
        return $this;
    }

    public function multipart(...$multipart)
    {
        $this->multipart[] = (count($multipart) == 1)
            ? $multipart[0]
            : $multipart;
        return $this;
    }

    public function formParams(...$form_params)
    {
        $this->form_params[] = (count($form_params) == 1)
            ? $form_params[0]
            : $form_params;
        return $this;
    }

    public function stream(...$stream)
    {
        $this->stream[] = (count($stream) == 1)
            ? $stream[0]
            : $stream;
        return $this;
    }

    public function keepAlive(bool $alive = true)
    {
        $this->alive = $alive;
        return $this;
    }

    public function keepIdle(int $idle)
    {
        $this->idle = $idle;
        return $this;
    }

    public function intval(int $intval)
    {
        $this->intval = $intval;
        return $this;
    }

    public function loop(int $loop, $async = false)
    {
        $this->loop = $loop;
        if ($async) {
            $this->async = true;
        }
        return $this;
    }


    public function data(array $json = [], array $form_params = [], array $query = [], array $stream = [], array $multipart = [])
    {
        if ($json) {
            $this->json($json);
        } elseif ($form_params) {
            $this->formParams($form_params);
        } elseif ($query) {
            $this->query($query);
        } elseif ($stream) {
            $this->stream($stream);
        } elseif ($multipart) {
            $this->multipart($multipart);
        }
        return $this;
    }
}