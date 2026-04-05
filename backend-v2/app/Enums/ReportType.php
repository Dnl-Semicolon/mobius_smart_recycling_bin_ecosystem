<?php

namespace App\Enums;

enum ReportType: string
{
    case DamagedBin = 'damaged_bin';
    case WrongDetection = 'wrong_detection';
    case Overflow = 'overflow';
    case Contamination = 'contamination';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::DamagedBin => 'Damaged Bin',
            self::WrongDetection => 'Wrong Detection',
            self::Overflow => 'Overflow',
            self::Contamination => 'Contamination',
            self::Other => 'Other',
        };
    }
}
