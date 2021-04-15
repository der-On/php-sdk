<?php
/** @noinspection PhpComposerExtensionStubsInspection */

/**
 * This is a wrapper for the applepay http adapter (CURL).
 *
 * Copyright (C) 2021 - today Unzer E-Com GmbH
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
 * @link https://dev.unzer.com/
 *
 * @author David Owusu <development@unzer.com>
 *
 * @package  UnzerSDK\Adapter
 */
namespace UnzerSDK\Adapter;

use UnzerSDK\Constants\ApplepayValidationDomains;
use UnzerSDK\Exceptions\ApplepayMerchantValidationException;
use UnzerSDK\Resources\ExternalResources\ApplepaySession;
use UnzerSDK\Services\EnvironmentService;

class ApplepayAdapter
{
    private $request;

    /**
     * @param string          $merchantValidationURL                     URL for merchant validation request
     * @param ApplepaySession $applePaySession                           Containing applepay session data.
     * @param string          $merchantValidationCertificatePath         Path to merchant identification certificate
     * @param string|null     $merchantValidationCertificateKeyChainPath
     *
     * @return string|null
     *
     * @throws ApplepayMerchantValidationException
     */
    public function validateApplePayMerchant(
        string $merchantValidationURL,
        ApplepaySession $applePaySession,
        string $merchantValidationCertificatePath,
        ?string $merchantValidationCertificateKeyChainPath = null
    ): ?string {
        if (!$this->validMerchantValidationDomain($merchantValidationURL)) {
            throw new ApplepayMerchantValidationException('Invalid URL used merchantValidation request.');
        }
        $payload = $applePaySession->jsonSerialize();
        $this->init(
            $merchantValidationURL,
            $payload,
            $merchantValidationCertificatePath,
            $merchantValidationCertificateKeyChainPath
        );
        $sessionResponse = $this->execute();
        $this->close();
        return $sessionResponse;
    }

    /**
     * Check whether domain of merchantValidationURL is allowed for validation request.
     *
     * @param string $merchantValidationURL URL used for merchant validation request.
     *
     */
    public function validMerchantValidationDomain(string $merchantValidationURL): bool
    {
        $domain = explode('/', $merchantValidationURL)[2] ?? '';

        $UrlList = ApplepayValidationDomains::ALLOWED_VALIDATION_URLS;
        return in_array($domain, $UrlList);
    }

    /**
     * {@inheritDoc}
     */
    public function init($url, $payload, $sslCert, $caCert = null): void
    {
        $timeout = EnvironmentService::getTimeout();
        $curlVerbose = EnvironmentService::isCurlVerbose();

        $this->request = curl_init($url);
        $this->setOption(CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $this->setOption(CURLOPT_POST, 1);
        $this->setOption(CURLOPT_DNS_USE_GLOBAL_CACHE, false);
        $this->setOption(CURLOPT_POSTFIELDS, $payload);
        $this->setOption(CURLOPT_FAILONERROR, false);
        $this->setOption(CURLOPT_TIMEOUT, $timeout);
        $this->setOption(CURLOPT_CONNECTTIMEOUT, $timeout);
        $this->setOption(CURLOPT_HTTP200ALIASES, (array)400);
        $this->setOption(CURLOPT_RETURNTRANSFER, 1);
        $this->setOption(CURLOPT_SSL_VERIFYPEER, 1);
        $this->setOption(CURLOPT_SSL_VERIFYHOST, 2);
        $this->setOption(CURLOPT_VERBOSE, $curlVerbose);
        $this->setOption(CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);

        $this->setOption(CURLOPT_SSLCERT, $sslCert);
        if (isset($caCert)) {
            $this->setOption(CURLOPT_CAINFO, $caCert);
        }
    }

    /**
     * Sets curl option.
     *
     * @param $name
     * @param $value
     */
    private function setOption($name, $value): void
    {
        curl_setopt($this->request, $name, $value);
    }

    /**
     * {@inheritDoc}
     *
     * @throws ApplepayMerchantValidationException
     */
    public function execute(): ?string
    {
        $response = curl_exec($this->request);
        $error = curl_error($this->request);
        $errorNo = curl_errno($this->request);

        switch ($errorNo) {
            case 0:
                return $response;
                break;
            case CURLE_OPERATION_TIMEDOUT:
                $errorMessage = 'Timeout: The Applepay API seems to be not available at the moment!';
                break;
            default:
                $errorMessage = $error . ' (curl_errno: ' . $errorNo . ').';
                break;
        }
        throw new ApplepayMerchantValidationException($errorMessage);
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        curl_close($this->request);
    }

    /**
     * @inheritDoc
     */
    public function getResponseCode(): string
    {
        return curl_getinfo($this->request, CURLINFO_HTTP_CODE);
    }
}
