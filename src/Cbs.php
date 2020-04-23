<?php
/*
 * This file is part of the Laravel Cbs package.
 *
 * (c) Edward Paul <infinitypaul@live.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinitypaul\Cbs;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Infinitypaul\Cbs\Exceptions\InvalidPostException;
use Infinitypaul\Cbs\Exceptions\NotSetException;

class Cbs
{
    /**
     * Issue Secret Key from CBS.
     * @var string
     */
    protected $secretKey;

    /**
     * Issue Client ID from CBS.
     * @var string
     */
    protected $clientId;

    /**
     * Issue URL from CBS.
     * @var string
     */
    protected $baseUrl;

    /**
     * Issue Revenue Head from CBS.
     * @var int
     */
    protected $revenueHeads;

    /**
     * Issue Category ID from CBS Admin.
     * @var int
     */
    protected $categoryId;

    /**
     *  Response from requests made to CBS.
     * @var mixed
     */
    protected $response;

    /**
     *  Hashed Key.
     * @var mixed
     */
    protected $signature;

    /**
     * Payment Url - CBS payment page.
     * @var string
     */
    protected $url;

    /**
     *  Response from CBS.
     * @var mixed
     */
    protected $invoice = [];

    /**
     * Instance of Client.
     * @var Client
     */
    protected $client;

    public function __construct()
    {
        $this->setUrl();
        $this->setConstant();
        $this->checkConstant();
    }

    public function setUrl()
    {
        $this->baseUrl = Config::get('mode') === 'test' ? Config::get('cbs.testUrl') : Config::get('cbs.liveURL');
    }

    /**
     * Get secret key from CBS config file.
     */
    public function setConstant()
    {
        $this->secretKey = Config::get('cbs.secret');
        $this->clientId = Config::get('cbs.clientId');
        $this->revenueHeads = Config::get('cbs.revenueHead');
        $this->categoryId = Config::get('cbs.categoryId');
    }

    protected function checkConstant()
    {
        if (! $this->revenueHeads) {
            throw new NotSetException('Set Your Revenue Head');
        }
        if (! $this->clientId) {
            throw new NotSetException('Set Your Client Id');
        }
        if (! $this->secretKey) {
            throw new NotSetException('Set Your Secret Id');
        }
        if (! $this->categoryId) {
            throw new NotSetException('Set Your Category Id');
        }
        if (! $this->baseUrl) {
            throw new NotSetException('Set Your Test and Live Base Url');
        }
    }

    protected function setSignature($amount, $callback)
    {
        $amount = number_format((float) $amount, 2, '.', '');
        $string = $this->revenueHeads.$amount.$callback.$this->clientId;
        //dd($string);
        $this->signature = base64_encode(hash_hmac('sha256', $string, $this->secretKey, true));
        $this->setRequestOptions();
    }

    /**
     * Set options for making the Client request.
     */
    private function setRequestOptions()
    {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'CLIENTID' => $this->clientId,
                'Signature' => $this->signature,
            ],
        ]);
    }

    /**
     * @param $relativeUri
     * @param string $method
     * @param array $body
     *
     * @return \Infinitypaul\Cbs\Cbs
     * @throws \Infinitypaul\Cbs\Exceptions\NotSetException
     */
    private function setHttpResponse($relativeUri, $method, $body = [])
    {
        if (is_null($method)) {
            throw new NotSetException('Empty Method Not Allowed');
        }
        $this->response = $this->client->{strtolower($method)}(
            $this->baseUrl.$relativeUri,
            ['body' => json_encode($body)]
        );

        return $this;
    }

    private function getResponse()
    {
        return json_decode($this->response->getBody(), true);
    }

    /**
     * Initiate a payment request to Cbs
     * Included the option to pass the payload to this method for situations
     * when the payload is built on the fly (not passed to the controller from a view).
     *
     * @param null $data
     *
     * @return \Infinitypaul\Cbs\Cbs
     * @throws \Infinitypaul\Cbs\Exceptions\NotSetException
     */
    public function generateInvoice($data = null)
    {
        if ($data == null) {
            if (request()->has('payerID')) {
                $user = ['PayerId' => request()->payerID];
            } else {
                $user = [
                    'Recipient' => request()->full_name,
                    'Email' => request()->email,
                    'Address' => request()->address,
                    'PhoneNumber' => request()->mobile_number,
                    'TaxPayerIdentificationNumber' => request()->tin,
                ];
            }
            $TaxEntityInvoice = [
                'TaxEntity' => $user,
                'Amount' => intval(request()->amount),
                'InvoiceDescription' => request()->description,
                'CategoryId' => $this->categoryId,
            ];
            $data = [
                'RevenueHeadId' => $this->revenueHeads,
                'TaxEntityInvoice' => $TaxEntityInvoice,
                'CallBackURL' => request()->callback,
                'RequestReference' => ReferenceNumber::getHashedToken(),
                'Quantity' => request()->quantity,

            ];

            $this->setSignature(request()->amount, request()->callback);
            array_filter($data);
        }

        $this->setHttpResponse('/api/v1/invoice/create', 'POST', $data);

        return $this;
    }

    /**
     * Set the invoice data from the callback response.
     */
    public function setInvoice()
    {
        $this->generateInvoice();
        $this->invoice = $this->getResponse();

        return $this;
    }

    /**
     * Get the invoice data from the callback response.
     */
    public function getData()
    {
        return $this->invoice;
    }

    /**
     * Get the invoice payment url from the callback response.
     */
    public function redirectNow()
    {
        return redirect($this->invoice['ResponseObject']['PaymentURL']);
    }

    /**
     * Compute Mac Address.
     *
     * @param $invoiceNumber
     * @param $paymentRef
     * @param $amount
     *
     *
     * @return string
     */
    protected function computeMac($invoiceNumber, $paymentRef, $amount)
    {
        $amount = number_format((float) $amount, 2, '.', '');
        $string = $invoiceNumber.$amount.$paymentRef;

        return base64_encode(hash_hmac('sha256', $string, $this->secretKey, true));
    }

    /**
     * Get Payment details if the transaction was verified successfully.
     *
     * @throws \Infinitypaul\Cbs\Exceptions\InvalidPostException
     */
    public function getPaymentData()
    {
        $mac = $this->computeMac(request()->InvoiceNumber, request()->PaymentRef, request()->AmountPaid);
        if ($mac != request()->Mac) {
            throw new InvalidPostException('Invalid Call');
        } else {
            return request()->all();
        }
    }
}
