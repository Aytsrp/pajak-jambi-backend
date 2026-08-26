<?php

namespace App\Enums;

enum VerificationType: string
{
    case Nik = 'nik';
    case Nop = 'nop';
    case Npwpd = 'npwpd';
}