<?php

namespace App\Enum;

enum Status: string
{
    case Paid = 'PAID';
    case NotPaid = 'NOTPAID';
}
