<?php
/**
 * This is the controller for the Invoice Factoring example.
 * It is called when the pay button on the index page is clicked.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link  https://docs.unzer.com/
 *
 * @author  Simon Gabriel <development@unzer.com>
 *
 * @package  UnzerSDK\examples
 */

/** Require the constants of this example */
require_once __DIR__ . '/Constants.php';

/** @noinspection PhpIncludeInspection */
/** Require the composer autoloader file */
require_once __DIR__ . '/../../../../autoload.php';

use UnzerSDK\examples\ExampleDebugHandler;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Unzer;
use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\CustomerFactory;
use UnzerSDK\Resources\EmbeddedResources\Address;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use UnzerSDK\Resources\PaymentTypes\InvoiceFactoring;

session_start();
session_unset();

$clientMessage = 'Something went wrong. Please try again later.';
$merchantMessage = 'Something went wrong. Please try again later.';

function redirect($url, $merchantMessage = '', $clientMessage = '')
{
    $_SESSION['merchantMessage'] = $merchantMessage;
    $_SESSION['clientMessage']   = $clientMessage;
    header('Location: ' . $url);
    die();
}

// Catch API errors, write the message to your log and show the ClientMessage to the client.
try {
    // Create an Unzer object using your private key and register a debug handler if you want to.
    $heidelpay = new Unzer(HEIDELPAY_PHP_PAYMENT_API_PRIVATE_KEY);
    $heidelpay->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

    /** @var InvoiceFactoring $invoiceFactoring */
    $invoiceFactoring = $heidelpay->createPaymentType(new InvoiceFactoring());

    // A customer with matching addresses is mandatory for Invoice Factoring payment type
    $customer = CustomerFactory::createCustomer('Max', 'Mustermann');
    $address  = new Address();
    $address->setName('Max Mustermann')
        ->setStreet('Vangerowstr. 18')
        ->setCity('Heidelberg')
        ->setZip('69155')
        ->setCountry('DE');
    $customer->setBirthDate('2000-02-12')->setBillingAddress($address)->setShippingAddress($address);

    $orderId = 'o' . str_replace(['0.', ' '], '', microtime(false));

    // A Basket is mandatory for Invoice Factoring payment type
    $basketItem = (new BasketItem('Hat', 100.00, 119.00, 1))
        ->setAmountGross(119.0)
        ->setAmountVat(19.0);
    $basket = new Basket($orderId, 119.0, 'EUR', [$basketItem]);

    $transaction = $invoiceFactoring->charge(119.0, 'EUR', CONTROLLER_URL, $customer, $orderId, null, $basket);

    // You'll need to remember the shortId to show it on the success or failure page
    $_SESSION['ShortId'] = $transaction->getShortId();
    $_SESSION['PaymentId'] = $transaction->getPaymentId();
    $_SESSION['additionalPaymentInformation'] =
        sprintf(
            "Please transfer the amount of %f %s to the following account:<br /><br />"
            . "Holder: %s<br/>"
            . "IBAN: %s<br/>"
            . "BIC: %s<br/><br/>"
            . "<i>Please use only this identification number as the descriptor: </i><br/>"
            . "%s",
            $transaction->getAmount(),
            $transaction->getCurrency(),
            $transaction->getHolder(),
            $transaction->getIban(),
            $transaction->getBic(),
            $transaction->getDescriptor()
        );

    // To avoid redundant code this example redirects to the general ReturnController which contains the code example to handle payment results.
    redirect(RETURN_CONTROLLER_URL);

} catch (UnzerApiException $e) {
    $merchantMessage = $e->getMerchantMessage();
    $clientMessage = $e->getClientMessage();
} catch (RuntimeException $e) {
    $merchantMessage = $e->getMessage();
}
// Write the merchant message to your log.
// Show the client message to the customer (it is localized).
redirect(FAILURE_URL, $merchantMessage, $clientMessage);
