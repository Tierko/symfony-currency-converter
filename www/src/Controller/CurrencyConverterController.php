<?php

namespace App\Controller;

use App\Service\CurrencyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CurrencyConverterController extends AbstractController
{
    #[Route('/currency/all', name: 'currency_converter_all', methods: ['GET'])]
    public function all(CurrencyService $currencyService): Response
    {
        return $this->json($currencyService->getAllData());
    }

    #[Route('/currency/calc/{value}/{fromCurrency}/{toCurrency}', name: 'currency_converter_calc', methods: ['GET'])]
    public function calc($value, $fromCurrency, $toCurrency, CurrencyService $currencyService): Response
    {
        $result = $currencyService->calcCurrency($value, $fromCurrency, $toCurrency);

        if (!$result) {
            return $this->json('Something went wrong. Check params', Response::HTTP_BAD_REQUEST);
        }

        return $this->json($result);
    }
}
