<?php

namespace App\Http\Controllers\Exportar;

use Illuminate\Http\Request;
use App\Exports\ValorExiExport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ValorExistenciasController extends Controller
{

    public function submit(Request $request)
    {

        if (!auth()->user()->hasPermissionTo('consultas')) {
            return abort(403, auth()->user()->name . ' NO tiene permiso de acceso - Consultas');
        }

        $data = $request->validate([
            'fecha_d' => ['required', 'date'],
            'fecha_h' => ['required', 'date'],
            'todas'   => ['required', 'boolean'],
        ]);

        $data = DB::table('existencias')
            ->select(DB::raw('nombre, fecha, detalle_id, importe'))
            ->join('empresas', 'empresas.id', '=', 'existencias.empresa_id')
            ->when($data['todas'], function ($query) {
                return $query->whereIn('empresa_id', session('empresas_usuario'));
            })
            ->when(!$data['todas'], function ($query) {
                return $query->where('empresa_id', session('empresa_id'));
            })
            ->whereDate('fecha', '>=', $data['fecha_d'])
            ->whereDate('fecha', '<=', $data['fecha_h'])
            ->whereNull('deleted_at')
            ->orderBy('nombre')
            ->orderBy('fecha')
            ->orderBy('detalle_id')
            ->get();

        if (request()->wantsJson()) {
            return $data;
        }

    }

    public function excel(Request $request)
    {

        $items = $request->validate(['data' => 'array']);

        return Excel::download(new ValorExiExport($items['data']), 'ValorEx.xlsx');

    }

}
