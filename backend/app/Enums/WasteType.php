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
}
