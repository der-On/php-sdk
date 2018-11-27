<?php
/**
 * This class defines unit tests to verify metadata functionalities.
 *
 * Copyright (C) 2018 Heidelpay GmbH
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/test/unit
 */
namespace heidelpay\MgwPhpSdk\test\integration;

use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\Metadata;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;

class MetadataTest extends BasePaymentTest
{
    /**
     * Verify Metadata is automatically generated and added to the heidelpay object.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public function heidelpayShouldAutomaticallyProvideAMetadataObject()
    {
        $metadata = $this->heidelpay->getMetadata();

        /** @noinspection UnnecessaryAssertionInspection */
        $this->assertInstanceOf(Metadata::class, $metadata);
        $this->assertSame($this->heidelpay, $metadata->getParentResource());
    }

    /**
     * Verify SDK-Data is initially set.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public function metadataShouldSetSDKDataAutomatically()
    {
        $metaData = new Metadata();
        $this->assertEquals(Heidelpay::SDK_TYPE, $metaData->getSdkType());
        $this->assertEquals(Heidelpay::SDK_VERSION, $metaData->getSdkVersion());
    }
}
