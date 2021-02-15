<?php

namespace App\Service;

use DOMDocument;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyService
{
    private HttpClientInterface $httpClient;
    private CacheInterface $cache;

    public function __construct(HttpClientInterface $httpClient, CacheInterface $cache)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    private function fillCurrency(): array
    {
        $result = [];
        $charCodeTag = 'CharCode';
        $exportedValueTags = ['Value', 'Nominal', 'Name'];

        $xmlCurrency = $this->httpClient->request('GET', 'http://www.cbr.ru/scripts/XML_daily.asp')
            ->getContent();

        $xml = new DOMDocument();

        if ($xml->loadXML($xmlCurrency)) {
            $root = $xml->documentElement;
            $items = $root->getElementsByTagName('Valute');

            foreach ($items as $item)
            {
                $code = $item->getElementsByTagName($charCodeTag)->item(0)->nodeValue;

                foreach ($exportedValueTags as $valueTag) {
                    $value = $item->getElementsByTagName($valueTag)->item(0)->nodeValue;
                    $result[$code][$valueTag] = $value;
                }
            }
        }

        return $result;
    }

    private function getCurrency()
    {
        return $this->cache->get('currency', function (ItemInterface $item) {
            $item->expiresAfter(3600);

            return $this->fillCurrency();
        });
    }

    public function getAllData()
    {
        return $this->getCurrency();
    }

    public function calcCurrency($value, $fromCurrency, $toCurrency): float|bool
    {
        $currencies = $this->getCurrency();

        $currencies['RUB'] = [
          'Value' => 1,
          'Nominal' => 1
        ];

        if (!array_key_exists($fromCurrency, $currencies) || !array_key_exists($toCurrency, $currencies)) {
            return false;
        }

        return floatval($value) * floatval($currencies[$fromCurrency]['Value']) / floatval($currencies[$fromCurrency]['Nominal'])
            * floatval($currencies[$toCurrency]['Nominal']) / floatval($currencies[$toCurrency]['Value']);
    }
}
