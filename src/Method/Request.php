<?php

namespace Ispahbod\Connect\Method;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Psr7\MultipartStream;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Stopwatch\Stopwatch;

trait Request
{
    public function reset()
    {
        $this->method = [];
        $this->json = [];
        $this->form_params = [];
        $this->stream = [];
        $this->query = [];
        $this->multipart = [];
        $this->url = [];
    }

    /**
     * @throws GuzzleException
     */
    public function send()
    {
        $method_count = max(1, is_array($this->method) ? count($this->method) : false);
        $json_count = max(1, count($this->json) > 1 ? count($this->json) : false);
        $params_count = max(1, count($this->form_params) > 1 ? count($this->form_params) : false);
        $stream_count = max(1, count($this->stream) > 1 ? count($this->stream) : false);
        $query_count = max(1, count($this->query) > 1 ? count($this->query) : false);
        $multipart_count = max(1, count($this->multipart) > 1 ? count($this->multipart) : false);
        $url_count = max(1, count($this->url) > 1 ? count($this->url) : false);
        $counts = [
            'method' => $method_count,
            'json' => $json_count,
            'form_params' => $params_count,
            'stream' => $stream_count,
            'query' => $query_count,
            'multipart' => $multipart_count,
            'url' => $url_count
        ];
        arsort($counts);
        $filteredCounts = [];
        $request_count = max($counts);
        foreach ($counts as $index => $count) {
            if ($count > 1) {
                $filteredCounts[$index] = $count;
            }
        }
        $this->multiple = ($request_count > 1) ? true : false;
        if ($this->multiple) {
            $promises = [];
            $options = [
                'verify' => $this->verify,
                'timeout' => $this->timeout,
                'curl' => [
                    CURLOPT_TCP_KEEPALIVE => $this->alive,
                    CURLOPT_TCP_KEEPIDLE => $this->idle,
                    CURLOPT_TCP_KEEPINTVL => $this->intval,
                ],
                'compress' => $this->compress,
                'http_errors' => $this->http_errors,
            ];
            if ($this->decode_content) {
                $options['decode_content'] = $this->decode_content;
            }
            if ($this->withRedirect) {
                $options['allow_redirects'] = $this->withRedirect;
            }
            for ($i = 0; $i < $request_count; $i++) {
                $options['headers'] = $this->headers[$i] ?? end($this->headers);
                $this->method = empty($this->method) ? ['GET'] : $this->method;
                $method = (is_array($this->method) && count($this->method) > 1) ? $this->method[$i] ?? end($this->method) : reset($this->method);
                $url = (is_array($this->url) && count($this->url) > 1) ? $this->url[$i] ?? end($this->url) : reset($this->url);
                if (!empty($this->form_params)) {
                    $options['form_params'] = $this->form_params[$i] ?? end($this->form_params);
                } elseif (!empty($this->json)) {
                    $options['json'] = $this->json[$i] ?? end($this->json);
                } elseif (!empty($this->stream)) {
                    $options['body'] = isset($this->body[$i]) ? new MultipartStream($this->body[$i]) : new MultipartStream($this->body);
                } elseif (!empty($this->query)) {
                    $options['query'] = $this->query[$i] ?? end($this->query);
                } elseif (!empty($this->multipart)) {
                    $options['multipart'] = $this->multipart[$i] ?? end($this->multipart);
                }
                if ($this->async) {
                    $options['headers'] = !$options['headers'] ? [] : $options['headers'];
                    $promises[] = $this->client->requestAsync($method, $this->base . $url, $options);
                } else {
                    $this->responses[] = $this->client->request($method, $this->base . $url, $options);
                }
            }
            if (empty($this->responses)) {
                $responses = Utils::settle($promises)->wait();
                foreach ($responses as $response) {
                    if ($response['state'] === 'fulfilled') {
                        $this->responses[] = $response['value'];
                    } else {
                        $this->responses[] = null;
                    }
                }
            }
            $this->reset();
            return $this;
        } else {
            $headers = end($this->headers);
            $options = [
                'headers' => $headers ?: [],
                'timeout' => $this->timeout,
                'verify' => $this->verify,
                'curl' => [
                    CURLOPT_TCP_KEEPALIVE => $this->alive,
                    CURLOPT_TCP_KEEPIDLE => $this->idle,
                    CURLOPT_TCP_KEEPINTVL => $this->intval,
                ],
                'compress' => $this->compress,
                'http_errors' => $this->http_errors,
            ];

            if ($this->withRedirect) {
                $options['allow_redirects'] = $this->withRedirect;
            }
            if (!empty($this->form_params)) {
                $options['form_params'] = end($this->form_params);
            } elseif (!empty($this->json)) {
                $options['json'] = end($this->json);
            } elseif (!empty($this->stream)) {
                $options['body'] = new MultipartStream(end($this->stream));
            } elseif (!empty($this->query)) {
                $options['query'] = end($this->query);
            } elseif (!empty($this->multipart)) {
                $options['multipart'] = end($this->multipart);
            }

            $this->method = empty($this->method) ? ['GET'] : $this->method;
            $method = (is_array($this->method) && count($this->method) == 1) ? reset($this->method) : $this->method;
            $url = (is_array($this->url) && count($this->url) == 1) ? reset($this->url) : $this->url;
            $url = $url == [] ? '' : $url;
            try {
                $stopwatch = new Stopwatch();
                if ($this->loop > 1) {
                    $promises = [];
                    for ($i = 0; $i < $this->loop; $i++) {
                        if ($this->async) {
                            $promises[] = $this->client->requestAsync($method, $this->base . $url, $options);
                        } else {
                            $this->responses[] = $this->client->request($method, $this->base . $url, $options);
                        }
                    }
                    if (empty($this->responses)) {
                        $responses = Utils::settle($promises)->wait();
                        foreach ($responses as $response) {
                            if ($response['state'] === 'fulfilled') {
                                $this->responses[] = $response['value'];
                            } else {
                                $this->responses[] = null;
                            }
                        }
                    }
                    $this->reset();
                    return $this;
                }
                $event = $stopwatch->start('request');
                $response = $this->client->request($method, $this->base . $url, $options);
                $event->stop();
                $this->time = $stopwatch->getEvent('request');
                $this->response = $response;
                $this->reset();
                return $this;
            } catch (Exception $e) {
                $this->errors = $e;
                return $this;
            } catch (GuzzleException) {
            }
        }
    }

    public static function crawler($html): Crawler
    {
        $result = new Crawler($html);
        return $result;
    }

    public function get()
    {
        if ($this->responses) {
            $collection = new Response($this->responses);
        } else {
            $collection = new Response($this->response);
        }
        return $collection;
    }
}