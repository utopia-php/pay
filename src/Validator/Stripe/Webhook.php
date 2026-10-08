<?php

declare(strict_types=1);

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
     */
    public function isValid(string $payload, string $header, string $secret, ?int $tolerance = null): bool
    {
        // Extract timestamp and signatures from header
        $timestamp = $this->getTimestamp($header);
        $signatures = $this->getSignatures($header, self::EXPECTED_SCHEME);
        if (-1 === $timestamp) {
            return false;
        }
        if ($signatures === []) {
            return false;
        }

        // Check if expected signature is found in list of signatures from
        // header
        $signedPayload = "{$timestamp}.{$payload}";
        $expectedSignature = $this->computeSignature($signedPayload, $secret);
        $signatureFound = array_any($signatures, fn ($signature): bool => \hash_equals($expectedSignature, $signature));
        if (! $signatureFound) {
            return false;
        }
        // Check if timestamp is within tolerance
        return $tolerance <= 0 || \abs(\time() - $timestamp) <= $tolerance;
    }

    /**
     * Extracts the timestamp in a signature header.
     *
     * @param  string  $header the signature header
     * @return int the timestamp contained in the header, or -1 if no valid
     *  timestamp is found
     */
    private function getTimestamp(string $header): int
    {
        $items = \explode(',', $header);

        foreach ($items as $item) {
            $itemParts = \explode('=', $item, 2);
            if ('t' === $itemParts[0]) {
                if (!isset($itemParts[1]) || !\ctype_digit($itemParts[1])) {
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
     * @return list<string> the list of signatures matching the provided scheme
     */
    private function getSignatures(string $header, string $scheme): array
    {
        $signatures = [];
        $items = \explode(',', $header);

        foreach ($items as $item) {
            $itemParts = \explode('=', $item, 2);
            if (\trim($itemParts[0]) === $scheme && isset($itemParts[1])) {
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
    private function computeSignature(string $payload, string $secret): string
    {
        return \hash_hmac('sha256', $payload, $secret);
    }
}
