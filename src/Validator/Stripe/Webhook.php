<?php

namespace Utopia\Pay\Validator\Stripe;

class Webhook
{
    public const DEFAULT_TOLERANCE = 300;

    public const EXPECTED_SCHEME = 'v1';

    /**
     * Verifies the signature header sent by Stripe. Throws an
     * Exception\SignatureVerificationException exception if the verification fails for
     * any reason.
     *
     * @param  string  $payload the payload sent by Stripe
     * @param  string  $header the contents of the signature header sent by
     *  Stripe
     * @param  string  $secret secret used to generate the signature
     * @param  int  $tolerance maximum difference allowed between the header's
     *  timestamp and the current time
     * @return bool
     */
    public function isValid($payload, $header, $secret, $tolerance = null)
    {
        // Extract timestamp and signatures from header
        $timestamp = $this->getTimestamp($header);
        $signatures = $this->getSignatures($header, self::EXPECTED_SCHEME);
        if (-1 === $timestamp) {
            return false;
        }
        if (empty($signatures)) {
            return false;
        }

        // Check if expected signature is found in list of signatures from
        // header
        $signedPayload = "{$timestamp}.{$payload}";
        $expectedSignature = $this->computeSignature($signedPayload, $secret);
        $signatureFound = false;
        foreach ($signatures as $signature) {
            if (\hash_equals($expectedSignature, $signature)) {
                $signatureFound = true;

                break;
            }
        }
        if (! $signatureFound) {
            return false;
        }

        // Check if timestamp is within tolerance
        if (($tolerance > 0) && (\abs(\time() - $timestamp) > $tolerance)) {
            return false;
        }

        return true;
    }

    /**
     * Extracts the timestamp in a signature header.
     *
     * @param  string  $header the signature header
     * @return int the timestamp contained in the header, or -1 if no valid
     *  timestamp is found
     */
    private function getTimestamp($header)
    {
        $items = \explode(',', $header);

        foreach ($items as $item) {
            $itemParts = \explode('=', $item, 2);
            if ('t' === $itemParts[0]) {
                if (! \is_numeric($itemParts[1])) {
                    return -1;
                }

                return (int) ($itemParts[1]);
            }
        }

        return -1;
    }

    /**
     * Extracts the signatures matching a given scheme in a signature header.
     *
     * @param  string  $header the signature header
     * @param  string  $scheme the signature scheme to look for
     * @return array the list of signatures matching the provided scheme
     */
    private function getSignatures($header, $scheme)
    {
        $signatures = [];
        $items = \explode(',', $header);

        foreach ($items as $item) {
            $itemParts = \explode('=', $item, 2);
            if (\trim($itemParts[0]) === $scheme) {
                $signatures[] = $itemParts[1];
            }
        }

        return $signatures;
    }

    /**
     * Computes the signature for a given payload and secret.
     *
     * The current scheme used by Stripe ("v1") is HMAC/SHA-256.
     *
     * @param  string  $payload the payload to sign
     * @param  string  $secret the secret used to generate the signature
     * @return string the signature as a string
     */
    private function computeSignature($payload, $secret)
    {
        return \hash_hmac('sha256', $payload, $secret);
    }
}
