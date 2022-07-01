<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;

class ExistenciaExport implements FromView
{

    use Exportable;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {

        $nombres = ['Recompras', 'Compras', 'Inventario'];

        return view('exports.existencias', [
            'data'    => $this->data,
            'nombres' => $nombres,
        ]);

    }
}
