<?php

namespace App\Http\Controllers\Ventas;

use App\Albalin;
use App\Albaran;
use App\Halbalin;
use Carbon\Carbon;
use App\Scopes\EmpresaScope;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Http\Requests\Ventas\StoreAlbalinRequest;
use App\Http\Requests\Ventas\UpdateAlbalinRequest;

class AlbalinsController extends Controller
{
    public function load(Request $request)
    {

        $data = $request->validate([
            'albaran_id' => ['required', 'integer'],
        ]);

        if (request()->wantsJson()) {
            return [
                'lineas'  => Albalin::with(['producto' => function ($query) {
                    $query->withTrashed();
                }, 'producto.clase', 'producto.garantia'])->AlbaranId($data['albaran_id'])->get(),
                'totales' => Albalin::totalAlbaranByAlb($data['albaran_id']),
                'albaran' => Albaran::withOutGlobalScope(EmpresaScope::class)->with(['cliente', 'tipo', 'fase', 'motivo', 'fpago', 'cuenta', 'fpago', 'procedencia'])->findOrFail($data['albaran_id']),
            ];
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAlbalinRequest $request)
    {
        $data = $request->validated();

        // if ($data['unidades'] == 0){
        //     $producto = Producto::with('iva')->findOrFail($data['producto_id']);
        //     if ($producto->iva->rebu == false){
        //         $data['unidades'] = 1;
        //     }
        // }

        $data['importe_venta'] = importeLinea($data);

        $data['empresa_id'] = session()->get('empresa')->id;
        $data['username']   = $request->user()->username;

        $reg = Albalin::create($data);

        if (request()->wantsJson()) {
            return [
                'lineas'  => Albalin::with(['producto' => function ($query) {
                    $query->withTrashed();}])->AlbaranId($reg->albaran_id)->get(),
                'totales' => Albalin::totalAlbaranByAlb($reg->albaran_id),
            ];
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAlbalinRequest $request, Albalin $albalin)
    {

        $data = $request->validated();

        $this->createHis($albalin, 'U');

        $data['username']      = $request->user()->username;
        $data['importe_venta'] = importeLinea($data);

        $albalin->update($data);

        if (request()->wantsJson()) {
            return [
                // 'lineas' => Albalin::with('producto')->AlbaranId($albalin->albaran_id)->get(),
                // 'totales' => Albalin::totalAlbaranByAlb($albalin->albaran_id)
            ];
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Albalin $albalin)
    {
        // $this->authorize('delete', $albalin);

        $albalin->forceDelete();

        if (request()->wantsJson()) {
            return [
                'lineas'  => Albalin::with('producto')->AlbaranId($albalin->albaran_id)->get(),
                'totales' => Albalin::totalAlbaranByAlb($albalin->albaran_id),
            ];
        }

    }

    private function createHis($albalin, $operacion)
    {

        $data                 = $albalin->toArray();
        $data['id']           = null;
        $data['albalin_id']   = $albalin->id;
        $data['operacion']    = $operacion;
        $data['username_his'] = session('username');
        $data['created_his']  = Carbon::now();

        Halbalin::create($data);

    }

}
