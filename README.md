# GP PHP SDK
The GP SDK for PHP provides a native interface to the GP API.
This is still a work in progress (WIP) project and hence it will be on the master branch.

# Installation
```bash
composer require lesterchan/gp-php-sdk
```
# Usage
## OAuth
### Generate a web URL that provides a web interface for OAuth authentication.
This helps the end-user login or register for GP.
```php
/**
 * @param string $codeVerifier Code verifier
 * @param string $requestToken Request token
 * @param string $redirectUri Redirect URI
 * @param string $scope Scope (payment.one_time_charge or payment.recurring_charge)
 */
$gp->getOauthAuthorizeUrl($codeVerifier, $requestToken, $redirectUri, $scope);
```
### Generate the oauth token by passing ​code​ received in the return URL from GP.
```php
/**
 * @param string $code Code
 * @param string $redirectUri Redirect URI
 * @param string $codeVerifier Code verifier
 */
$gp->getAccessToken($code, $redirectUri, $codeVerifier);
```
## One-Time Charge
### Initialise.
```php
/**
 * @param string $partnerId Partner ID
 * @param string $partnerSecret Partner Secret
 * @param string $clientId Client ID
 * @param string $clientSecret Client Secret
 * @param string $merchantId Merchant ID
 */
$gp = new GP\OneTimeCharge($partnerId, $partnerSecret, $clientId, $clientSecret, $merchantId);
```
### Set up the details required to initiate a one-time payment.
```php
/**
 * @param string $txId order ID
 * @param string $groupTxId partner transaction ID
 * @param int $amount ​Transaction amount as integer
 * @param string $description description of the charge (optional)
 */
$gp->initCharge($txId, $groupTxId, $amount, $description);
```
### Complete the payment authorised by the user.
```php
/**
 * @param string $accessToken OAuth access token
 * @param string $partnerTxId partner transaction ID
 */
$gp->completeCharge($accessToken, $partnerTxId);
```

## Tokenization
### Initialise.
```php
/**
 * @param string $partnerId Partner ID
 * @param string $partnerSecret Partner Secret
 * @param string $clientId Client ID
 * @param string $clientSecret Client Secret
 * @param string $merchantId Merchant ID
 */
$gp = new GP\Tokenization($partnerId, $partnerSecret, $clientId, $clientSecret, $merchantId);
```
### Initiate the binding process with customer credits.
```php
/**
 * @param string $txId order ID
 */
$gp->bind($txId);
```
### Charge a customer who has completed the bind process with GP.
```php
/**
 * @param string $accessToken OAuth access token
 * @param string $txId order ID
 * @param string $groupTxId partner transaction ID
 * @param int $amount ​Transaction amount as integer
 * @param string $description description of the charge (optional)
 */
$gp->charge($accessToken, $txId, $groupTxId, $amount, $description);
```
### Deactivate the token generated during the binding process.
```php
/**
 * @param string $accessToken OAuth access token
 * @param string $txId order ID
 */
$gp->unbind($accessToken, $txId);
```
### View the wallet balance of the bound user.
```php
/**
 * @param string $accessToken OAuth access token
 */
$gp->getWalletInfo($accessToken);
```

## Status/Refunds
### Check the status of a transaction.
```php
/**
 * @param string $accessToken OAuth access token
 * @param string $txId order ID
 */
$gp->checkChargeStatus($accessToken, $txId);
```
### Refund a full or partial refunds for a specific transaction.
```php
/**
 * @param string $accessToken OAuth access token
 * @param string $txId order ID
 * @param string $groupTxId partner transaction ID
 * @param string $originTxID original partner transaction ID
 * @param int $amount ​Transaction amount as integer
 * @param string $description description of the charge (optional)
 */
$gp->refund($accessToken, $txId, $groupTxId, $originTxID, $amount, $description);
```
### Check the status of a refund transaction.
```php
/**
 * @param string $accessToken OAuth access token
 * @param string $txId order ID
 */
$gp->checkRefundStatus($accessToken, $txId);
```

## POS
### Initialise.
```php
/**
 * @param string $partnerId Partner ID
 * @param string $partnerSecret Partner Secret
 * @param string $merchantId Merchant ID
 * @param string $terminalId Terminal ID
 */
$gp = new GP\Pos($partnerId, $partnerSecret, $merchantId, $terminalId);
```
### Creates a payment order and returns a QR code.
```php
/**
 * @param string $txId order ID
 * @param int $amount ​Transaction amount as integer
 */
$gp->createMerchantPresentQrCode($txId, $amount);
```
### Performs a payment transaction which charges from the wallet associated with the request QR code.
```php
/**
 * @param string $txId order ID
 * @param int $amount ​Transaction amount as integer
 * @param string $qrCode QR code being scanned
 */
$gp->performConsumerPresentQrCode($txId, $amount, $qrCode);
```
### Returns details for a payment transaction or refund transaction.
```php
/**
 * @param string $txId order ID
 */
$gp->qrCodeInquiry($txId);
```
### Cancels a pending payment.
```php
/**
 * @param string $origTxID Original order ID
 */
$gp->cancelTransaction($origTxID);
```
### Refunds a previously successful payment.
```php
/**
 * @param string $txID order ID
 * @param string $origTxID Original order ID
 * @param int $amount ​Transaction amount as integer
 */
$gp->refundTransaction($txID, $origTxID, $amount);
```

## Others
### By default, the SDK will be using Staging URL. To switch to Production URL, use:
```php
$gp->useProduction();
```
### Defaults to SG. Accepted values are SG, MY, VN, and PH.
```php
/**
 * @param string $countryCode Country code (alpha-2)
 */
$gp->setCountryCode($countryCode);
```
### Defaults to SGD. Accepted values are SGD, MYR, VND, and PHP.
```php
/**
 * @param string $currency Currency
 */
$gp->setCurrency($currency);
```
### Generate nonce.
```php
/**
 * @param int $length Length
 */
$gp->generateNonce($length);
```

Please refer to the [GP PHP SDK Sample Code Repository](https://github.com/lesterchan/gp-php-sdk-sample) for some sample codes.