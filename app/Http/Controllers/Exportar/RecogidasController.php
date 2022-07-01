<?php

namespace App\Http\Controllers\Exportar;

use App\Compra;
use App\Hcompra;
use Carbon\Carbon;
use App\Scopes\EmpresaScope;
use Illuminate\Http\Request;
use App\Rules\RangoFechaRule;
use App\Exports\RecogidasExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class RecogidasController extends Controller
{
    public function index()
    {

    }

    public function submit(Request $request)
    {

        $data = $request->validate([
            'fecha_d'     => ['required', 'date', new RangoFechaRule($request->fecha_d, $request->fecha_h)],
            'fecha_h'     => ['required', 'date'],
            'norecogidas' => ['required', 'boolean'],
            'todas'       => ['required', 'boolean'],
        ]);

        return $this->detalle($data);

    }

    private function detalle($data)
    {

        if ($data['todas'] == true) {

            if ($data['norecogidas']) {
                $result = Compra::withoutGlobalScope(EmpresaScope::class)
                    ->with(['empresa', 'cliente', 'fase', 'allcomlines.clase'])
                    ->where('tipo_id', 1)
                    ->where('fase_id', 4)
                //->whereDate('compras.fecha_recogida', '<=', date('Y-m-d'))
                    ->whereDate('compras.fecha_recogida', '>=', $data['fecha_d'])
                    ->whereDate('compras.fecha_recogida', '<=', $data['fecha_h'])
                    ->whereIn('compras.empresa_id', session('empresas_usuario'))
                    ->orderBy('empresa_id')
                    ->orderBy('fecha_compra')
                    ->orderBy('albaran')
                    ->get();
            } else {
                $result = Compra::withoutGlobalScope(EmpresaScope::class)
                    ->with(['empresa', 'cliente', 'fase', 'allcomlines.clase'])
                    ->where('tipo_id', 1)
                    ->whereIn('fase_id', [4, 5])
                    ->whereDate('compras.fecha_recogida', '>=', $data['fecha_d'])
                    ->whereDate('compras.fecha_recogida', '<=', $data['fecha_h'])
                    ->whereIn('compras.empresa_id', session('empresas_usuario'))
                    ->orderBy('empresa_id')
                    ->orderBy('fecha_compra')
                    ->orderBy('albaran')
                    ->get();
            }
        } else {
            if ($data['norecogidas']) {
                $result = Compra::with(['empresa', 'cliente', 'fase', 'allcomlines.clase'])
                    ->where('tipo_id', 1)
                    ->where('fase_id', 4)
                //->whereDate('compras.fecha_recogida', '<=', date('Y-m-d'))
                    ->whereDate('compras.fecha_recogida', '>=', $data['fecha_d'])
                    ->whereDate('compras.fecha_recogida', '<=', $data['fecha_h'])
                    ->whereIn('compras.empresa_id', session('empresas_usuario'))
                    ->orderBy('empresa_id')
                    ->orderBy('fecha_compra')
                    ->orderBy('albaran')
                    ->get();
            } else {
                $result = Compra::with(['empresa', 'cliente', 'fase', 'allcomlines.clase'])
                    ->where('tipo_id', 1)
                    ->whereIn('fase_id', [4, 5])
                    ->whereDate('compras.fecha_recogida', '>=', $data['fecha_d'])
                    ->whereDate('compras.fecha_recogida', '<=', $data['fecha_h'])
                    ->whereIn('compras.empresa_id', session('empresas_usuario'))
                    ->orderBy('empresa_id')
                    ->orderBy('fecha_compra')
                    ->orderBy('albaran')
                    ->get();
            }

        }

        return $result;

    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'fecha_recogida' => ['nullable', 'date'],
        ]);

        $compra = Compra::withoutGlobalScope(EmpresaScope::class)->findOrFail($id);

        $this->createHis($compra, 'R');

        // no le dejo porque no afecta y porque si no desbloquea el permiso propietario
        $data['username'] = $request->user()->username;

        $compra->update($data);

        if (request()->wantsJson()) {
            return [
                'compra'  => $compra->load(['cliente', 'grupo', 'tipo', 'fase']),
                'message' => 'Se ha modificado la fecha de recogida',
            ];
        }

    }

    private function createHis($compra, $operacion)
    {

        $data                 = $compra->toArray();
        $data['id']           = null;
        $data['compra_id']    = $compra->id;
        $data['operacion']    = $operacion;
        $data['username_his'] = session('username');
        $data['created_his']  = Carbon::now();

        Hcompra::create($data);

    }

    /**
     * Recibe las facturas por request, previamente de $this->lisfac()
     *
     * @param Request $request
     * @return void
     */
    public function excel(Request $request)
    {

        $data = $request->validate([
            'fecha_d'     => ['required', 'date', new RangoFechaRule($request->fecha_d, $request->fecha_h)],
            'fecha_h'     => ['required', 'date'],
            'norecogidas' => ['required', 'boolean'],
            'todas'       => ['required', 'boolean'],
        ]);

        return Excel::download(new RecogidasExport($this->detalle($data), 'Recogidas'), 'file.xlsx');

    }
}
