<?php

namespace GP;

/**
 * Class Pos
 */
class Pos extends GP
{
    /**
     * Terminal ID.
     *
     * @var string
     */
    private $terminalId;

    /**
     * Merchant ID Key.
     *
     * @var string
     */
    private $merchantIdKey;

    /**
     * Pos constructor.
     *
     * @param string $partnerId Partner ID
     * @param string $partnerSecret Partner Secret
     * @param string $merchantId Merchant ID
     * @param string $terminalId Terminal ID
     */
    public function __construct(string $partnerId, string $partnerSecret, string $merchantId, string $terminalId)
    {
        parent::__construct($partnerId, $partnerSecret, '', '', $merchantId);

        $this->terminalId = $terminalId;
        $this->merchantIdKey = base64_decode('Z3JhYklE');
    }

    /**
     * Creates a payment order with a unique reference (txID) and returns a QR code string encoded with merchant detail, amount and txID.
     *
     * @param string $txId order ID
     * @param int $amount ​Transaction amount as integer
     *
     * @return object
     */
    public function createMerchantPresentQrCode(string $txId, int $amount): object
    {
        $data = [
            'amount' => $amount,
            'msgID' => $this->generateNonce(),
            $this->merchantIdKey => $this->merchantId,
            'terminalID' => $this->terminalId,
            'currency' => $this->currency,
            'partnerTxID' => $txId,
        ];

        $date = $this->generateHeaderDate();
        $requestUri = $this->getPartnerUrlPath('v1', '/terminal/qrcode/create');
        $hmacSignature = $this->generateHmacSignature('POST', 'application/json', $date, $requestUri, json_encode($data));

        return $this->callApi('POST', $requestUri, [
            'Date' => $date,
            'Authorization' => $this->partnerId . ':' . $hmacSignature,
        ], $data);
    }

    /**
     * Performs a payment transaction which charges from the wallet associated with the request QR code and payouts to request merchant ID.
     *
     * @param string $txId order ID
     * @param int $amount ​Transaction amount as integer
     * @param string $qrCode QR code being scanned
     *
     * @return object
     */
    public function performConsumerPresentQrCode(string $txId, int $amount, string $qrCode): object
    {
        $data = [
            'amount' => $amount,
            'msgID' => $this->generateNonce(),
            $this->merchantIdKey => $this->merchantId,
            'terminalID' => $this->terminalId,
            'currency' => $this->currency,
            'partnerTxID' => $txId,
            'code' => $qrCode,
        ];

        $date = $this->generateHeaderDate();
        $requestUri = $this->getPartnerUrlPath('v1', '/terminal/transaction/perform');
        $hmacSignature = $this->generateHmacSignature('POST', 'application/json', $date, $requestUri, json_encode($data));

        return $this->callApi('POST', $requestUri, [
            'Date' => $date,
            'Authorization' => $this->partnerId . ':' . $hmacSignature,
        ], $data);
    }

    /**
     * Returns details for a payment transaction or refund transaction.
     *
     * @param string $txId order ID
     *
     * @return object
     */
    public function qrCodeInquiry(string $txId): object
    {
        $data = [
            'msgID' => $this->generateNonce(),
            $this->merchantIdKey => $this->merchantId,
            'terminalID' => $this->terminalId,
            'currency' => $this->currency,
            'txType' => 'P2M',
            'partnerTxID' => $txId,
        ];

        $date = $this->generateHeaderDate();
        $requestUri = $this->getPartnerUrlPath('v1', '/terminal/transaction/' . $txId);
        $hmacSignature = $this->generateHmacSignature('GET', 'application/json', $date, $requestUri, json_encode($data));

        return $this->callApi('GET', $requestUri, [
            'Date' => $date,
            'Authorization' => $this->partnerId . ':' . $hmacSignature,
        ], $data);
    }

    /**
     * Cancels a pending payment. Cancel can be done when the payment status is still unknown after 30 seconds.
     * Payments which have been successfully charged, cannot be cancelled, use the /refund method instead.
     *
     * @param string $origTxID Original order ID
     *
     * @return object
     */
    public function cancelTransaction(string $origTxID): object
    {
        $data = [
            'msgID' => $this->generateNonce(),
            $this->merchantIdKey => $this->merchantId,
            'terminalID' => $this->terminalId,
            'currency' => $this->currency,
            'origPartnerTxID' => 'origTxID',
        ];

        $date = $this->generateHeaderDate();
        $requestUri = $this->getPartnerUrlPath('v1', '/terminal/transaction/' . $origTxID . '/cancel');
        $hmacSignature = $this->generateHmacSignature('PUT', 'application/json', $date, $requestUri, json_encode($data));

        return $this->callApi('PUT', $requestUri, [
            'Date' => $date,
            'Authorization' => $this->partnerId . ':' . $hmacSignature,
        ], $data);
    }

    /**
     * Refunds a previously successful payment, returning a unique refund reference(txID) for this request.
     * Refunding can be done on the full amount or a partial amount.
     * Multiple (partial) refunds will be accepted as long as their sum doesn't exceed the charged amount.
     * You can only request refund within 30 days of the charge.
     *
     * @param string $txID order ID
     * @param string $origTxID Original order ID
     * @param int $amount ​Transaction amount as integer
     *
     * @return object
     */
    public function refundTransaction(string $txID, string $origTxID, int $amount): object
    {
        $data = [
            'amount' => $amount,
            'msgID' => $this->generateNonce(),
            $this->merchantIdKey => $this->merchantId,
            'terminalID' => $this->terminalId,
            'currency' => $this->currency,
            'partnerTxID' => $txID,
            'origPartnerTxID' => $origTxID,
        ];

        $date = $this->generateHeaderDate();
        $requestUri = $this->getPartnerUrlPath('v1', '/terminal/transaction/' . $origTxID . '/refund');
        $hmacSignature = $this->generateHmacSignature('PUT', 'application/json', $date, $requestUri, json_encode($data));

        return $this->callApi('PUT', $requestUri, [
            'Date' => $date,
            'Authorization' => $this->partnerId . ':' . $hmacSignature,
        ], $data);
    }
}