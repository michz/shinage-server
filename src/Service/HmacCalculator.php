<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

readonly class HmacCalculator implements HmacCalculatorInterface
{
    private const string HASH_FUNCTION = 'sha3-512';
    private const string DATA_GLUE_KEY_VALUE = ':';
    private const string DATA_GLUE_ITEMS = '|';

    public function __construct(
        private string $secret,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function calculate(array $data): string
    {
        $dataList = [];
        foreach ($data as $key => $value) {
            $dataList[] = $key . self::DATA_GLUE_KEY_VALUE . $value;
        }
        $dataString = \implode(self::DATA_GLUE_ITEMS, $dataList);
        return \hash_hmac(
            self::HASH_FUNCTION,
            $dataString,
            $this->secret,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function verify(array $data, string $hmac): bool
    {
        return $hmac === $this->calculate($data);
    }
}
