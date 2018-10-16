<?php
/**
 * This class defines integration tests to verify interface and functionality of the payment method GiroPay.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/tests/integration/payment_types
 */
namespace heidelpay\MgwPhpSdk\test\integration\PaymentTypes;

use heidelpay\MgwPhpSdk\Constants\ApiResponseCodes;
use heidelpay\MgwPhpSdk\Constants\Currency;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Giropay;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;

class GiropayTest extends BasePaymentTest
{
    /**
     * Verify a GiroPay resource can be created.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException
     */
    public function giroPayShouldBeCreatable()
    {
        /** @var Giropay $giropay */
        $giropay = new Giropay();
        $giropay = $this->heidelpay->createPaymentType($giropay);
        $this->assertInstanceOf(Giropay::class, $giropay);
        $this->assertNotNull($giropay->getId());
    }

    /**
     * Verify that an exception is thrown when giropay authorize is called.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws \PHPUnit\Framework\Exception
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException
     */
    public function giroPayShouldThrowExceptionOnAuthorize()
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        /** @var Giropay $giropay */
        $giropay = new Giropay();
        $giropay = $this->heidelpay->createPaymentType($giropay);
        $giropay->authorize(1.0, Currency::EURO, self::RETURN_URL);
    }

    /**
     * Verify that GiroPay is chargeable.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException
     */
    public function giroPayShouldBeChargeable()
    {
        /** @var Giropay $giropay */
        $giropay = new Giropay();
        $giropay = $this->heidelpay->createPaymentType($giropay);

        /** @var Charge $charge */
        $charge = $giropay->charge(1.0, Currency::EURO, self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotNull($charge->getId());
        $this->assertNotNull($charge->getRedirectUrl());

        $fetchCharge = $this->heidelpay->fetchChargeById($charge->getPayment()->getId(), $charge->getId());
        $this->assertEquals($charge->expose(), $fetchCharge->expose());
    }

    /**
     * Verify a GiroPay object can be fetched from the api.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException
     */
    public function giroPayCanBeFetched()
    {
        $giropay = $this->heidelpay->createPaymentType(new Giropay());
        $fetchedGiropay = $this->heidelpay->fetchPaymentType($giropay->getId());
        $this->assertInstanceOf(Giropay::class, $fetchedGiropay);
        $this->assertEquals($giropay->getId(), $fetchedGiropay->getId());
    }
}
