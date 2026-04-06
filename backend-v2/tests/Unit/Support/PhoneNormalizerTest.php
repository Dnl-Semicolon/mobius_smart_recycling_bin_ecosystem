<?php

use App\Support\PhoneNormalizer;

test('it normalizes malaysian mobile numbers to e164', function () {
    expect(PhoneNormalizer::normalize('0123456789'))->toBe('+60123456789');
    expect(PhoneNormalizer::normalize('012-345 6789'))->toBe('+60123456789');
    expect(PhoneNormalizer::normalize('+60123456789'))->toBe('+60123456789');
    expect(PhoneNormalizer::normalize('60123456789'))->toBe('+60123456789');
    expect(PhoneNormalizer::normalize('011-1234 5678'))->toBe('+601112345678');
    expect(PhoneNormalizer::normalize('010-123 4567'))->toBe('+60101234567');
});

test('it rejects invalid malaysian mobile numbers', function () {
    expect(PhoneNormalizer::normalize('03-1234 5678'))->toBeNull();
    expect(PhoneNormalizer::normalize('014-1234 5678'))->toBeNull();
    expect(PhoneNormalizer::normalize(''))->toBeNull();
    expect(PhoneNormalizer::normalize(null))->toBeNull();
});

test('it formats valid numbers for display', function () {
    expect(PhoneNormalizer::formatForDisplay('+60123456789'))->toBe('012-345 6789');
    expect(PhoneNormalizer::formatForDisplay('+601112345678'))->toBe('011-1234 5678');
});
