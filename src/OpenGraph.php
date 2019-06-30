<?php

namespace shweshi\OpenGraph;

use DOMDocument;

class OpenGraph
{
    public function fetch($url, $allMeta = null, $lang = null)
    {
        $html = $this->curl_get_contents($url, $lang);
        /**
         * parsing starts here:.
         */
        return $this->get($html, $allMeta, $lang);
    }

    public function get($html, $allMeta = null, $lang = null)
    {
        /**
         * parsing starts here:.
         */
        $doc = new DOMDocument();
        @$doc->loadHTML('<?xml encoding="utf-8" ?>'.$html);

        $tags = $doc->getElementsByTagName('meta');
        $metadata = [];
        foreach ($tags as $tag) {
            $metaproperty = ($tag->hasAttribute('property')) ? $tag->getAttribute('property') : $tag->getAttribute('name');
            if (!$allMeta && $metaproperty && strpos($tag->getAttribute('property'), 'og:') === 0) {
                $key = strtr(substr($metaproperty, 3), '-', '_');
                $value = $tag->getAttribute('content');
            }
            if ($allMeta && $metaproperty) {
                $key = (strpos($metaproperty, 'og:') === 0) ? strtr(substr($metaproperty, 3), '-', '_') : $metaproperty;
                $value = $tag->getAttribute('content');
            }
            if (!empty($key)) {
                $metadata[$key] = $value;
            }
            /*
             * Verify image url
             */
            if (isset($metadata['image'])) {
                $isValidImageUrl = $this->verify_image_url($metadata['image']);
                if (!$isValidImageUrl) {
                    $metadata['image'] = '';
                }
            }
        }

        return $metadata;
    }

    protected function curl_get_contents($url, $lang)
    {
        $headers = [
          'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
          'Cache-Control: no-cache',
          'User-Agent: Curl',
        ];

        if ($lang) {
            array_push($headers, 'Accept-Language: '.$lang);
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
          CURLOPT_URL            => $url,
          CURLOPT_FAILONERROR    => false,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_ENCODING       => 'UTF-8',
          CURLOPT_MAXREDIRS      => 10,
          CURLOPT_TIMEOUT        => 30,
          CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST  => 'GET',
          CURLOPT_HTTPHEADER     => $headers,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    protected function verify_image_url($url)
    {
        $headers = get_headers($url);

        return stripos($headers[0], '200 OK') ? true : false;
    }
}
