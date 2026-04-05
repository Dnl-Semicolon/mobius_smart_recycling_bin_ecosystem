<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Open = 'open';
    case Investigating = 'investigating';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
