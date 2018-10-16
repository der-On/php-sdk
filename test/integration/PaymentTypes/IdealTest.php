<?php
/**
 * This class defines integration tests to verify interface and functionality of the payment method Ideal.
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
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Ideal;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;

class IdealTest extends BasePaymentTest
{
    /**
     * Verify Ideal payment type is creatable.
     *
     * @test
     *
     * @return Ideal
     *
     * @throws HeidelpayApiException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException
     */
    public function idealShouldBeCreatable(): Ideal
    {
        /** @var Ideal $ideal */
        $ideal = new Ideal();
        $ideal->setBic('RABONL2U');
        $this->heidelpay->createPaymentType($ideal);
        $this->assertInstanceOf(Ideal::class, $ideal);
        $this->assertNotNull($ideal->getId());

        return $ideal;
    }

    /**
     * Verify that ideal is not authorizable.
     *
     * @test
     *
     * @param Ideal $ideal
     *
     * @throws HeidelpayApiException
     * @throws \PHPUnit\Framework\Exception
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException
     * @depends idealShouldBeCreatable
     */
    public function idealShouldThrowExceptionOnAuthorize(Ideal $ideal)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);
        $ideal->authorize(1.0, Currency::EURO, self::RETURN_URL);
    }

    /**
     * Verify that ideal payment type is chargeable.
     *
     * @test
     * @depends idealShouldBeCreatable
     *
     * @param Ideal $ideal
     *
     * @throws HeidelpayApiException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException
     */
    public function idealShouldBeChargeable(Ideal $ideal)
    {
        $charge = $ideal->charge(1.0, Currency::EURO, self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotNull($charge->getId());
        $this->assertNotNull($charge->getRedirectUrl());

        $fetchCharge = $this->heidelpay->fetchChargeById($charge->getPayment()->getId(), $charge->getId());
        $this->assertEquals($charge->expose(), $fetchCharge->expose());
    }

    /**
     * Verify ideal payment type can be fetched.
     *
     * @test
     * @depends idealShouldBeCreatable
     *
     * @param Ideal $ideal
     *
     * @throws HeidelpayApiException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException
     */
    public function idealTypeCanBeFetched(Ideal $ideal)
    {
        $fetchedIdeal = $this->heidelpay->fetchPaymentType($ideal->getId());
        $this->assertInstanceOf(Ideal::class, $fetchedIdeal);
        $this->assertEquals($ideal->getId(), $fetchedIdeal->getId());
    }
}
