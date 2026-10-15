<?php

declare(strict_types=1);

namespace App\Enum;

enum MailType: string
{
    case CONTRAT_DE_VENTE   = 'CONTRAT_DE_VENTE';



    public function label(): string
    {
        return match ($this) {
            self::CONTRAT_DE_VENTE   => 'Contrat de vente',
        };
    }

    /** @return array<string,string> */
    public static function choices(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->label()] = $case->value;
        }
        return $out;
    }
}
