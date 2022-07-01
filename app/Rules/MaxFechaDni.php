<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class MaxFechaDni implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine si mayor de 10 años no permite documento
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        $f = Carbon::parse($value);

        if ($f->format('Y') == 9999)
            return true;

        $dt = Carbon::now();

        return !($f->diffInYears($dt) > 10);

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Ejercicio excede límite';
    }
}
