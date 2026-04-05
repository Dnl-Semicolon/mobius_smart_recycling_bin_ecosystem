<?php

namespace App\Enums;

enum WasteType: string
{
    case PaperCup = 'paper_cup';
    case PlasticCup = 'plastic_cup';
    case Lid = 'lid';
    case Straw = 'straw';
    case Napkin = 'napkin';
    case LiquidWaste = 'liquid_waste';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }

    public function iconColor(): string
    {
        return match ($this) {
            self::PaperCup => 'text-amber-500',
            self::PlasticCup => 'text-blue-500',
            self::Lid => 'text-gray-500',
            self::Straw => 'text-pink-500',
            self::Napkin => 'text-orange-400',
            self::LiquidWaste => 'text-cyan-500',
        };
    }

    public function bgColor(): string
    {
        return match ($this) {
            self::PaperCup => 'bg-amber-50',
            self::PlasticCup => 'bg-blue-50',
            self::Lid => 'bg-gray-50',
            self::Straw => 'bg-pink-50',
            self::Napkin => 'bg-orange-50',
            self::LiquidWaste => 'bg-cyan-50',
        };
    }

    public function borderColor(): string
    {
        return match ($this) {
            self::PaperCup => 'border-l-amber-400',
            self::PlasticCup => 'border-l-blue-400',
            self::Lid => 'border-l-gray-400',
            self::Straw => 'border-l-pink-400',
            self::Napkin => 'border-l-orange-400',
            self::LiquidWaste => 'border-l-cyan-400',
        };
    }

    public function isCup(): bool
    {
        return in_array($this, [self::PaperCup, self::PlasticCup]);
    }

    public function chartColor(): string
    {
        return match ($this) {
            self::PaperCup => '#f59e0b',
            self::PlasticCup => '#3b82f6',
            self::Lid => '#6b7280',
            self::Straw => '#ec4899',
            self::Napkin => '#f97316',
            self::LiquidWaste => '#06b6d4',
        };
    }
}
