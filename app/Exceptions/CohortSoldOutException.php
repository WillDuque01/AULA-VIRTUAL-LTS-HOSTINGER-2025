<?php

namespace App\Exceptions;

use App\Models\CohortTemplate;
use RuntimeException;

class CohortSoldOutException extends RuntimeException
{
    public static function forTemplate(CohortTemplate $template): self
    {
        return new self(__('La cohorte ":name" ya no tiene cupos disponibles.', [
            'name' => $template->name,
        ]));
    }
}


