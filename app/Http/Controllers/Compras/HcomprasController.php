<?php

namespace App\Http\Controllers\Compras;

use App\Compra;
use App\Comline;
use App\Hcompra;
use App\Deposito;
use App\Hcomline;
use App\Hdeposito;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HcomprasController extends Controller
{

    public function index()
    {

        if (request()->wantsJson()) {
            return Hcompra::with(['cliente', 'grupo', 'tipo', 'fase'])
                ->fecha(request('desde'), request('hasta'))->orderBy('id', 'desc')
                ->numero(request('numero'))
                ->get()->take(500);
        }

    }

    public function show(Hcompra $hcompra)
    {

        $hcompra->load(['cliente', 'grupo', 'grupo', 'tipo', 'fase']);

        $grabaciones   = false;
        $dias_cortesia = 7;

        if (request()->wantsJson()) {
            return [
                'hcompra'       => $hcompra,
                'hdepositos'    => Hdeposito::with(['concepto'])->where('compra_id', $hcompra->compra_id)->get(),
                'hcomlines'     => Hcomline::with(['clase'])->where('compra_id', $hcompra->compra_id)->get(),
                'grabaciones'   => $grabaciones,
                'dias_cortesia' => $dias_cortesia,
            ];
        }

    }

    public function historial($compra_id)
    {

        if (!hasReaCom()) {
            return abort(403, auth()->user()->name . ' NO tiene permiso de acceso - Reabrir');
        }

        if (request()->wantsJson()) {
            return Hcompra::where('compra_id', $compra_id)
            //->where('operacion', 'I')
                ->orderBy('created_his', 'desc')
                ->get();
        }

    }

    public function restore(Hcompra $hcompra)
    {

        if (!esRoot()) {
            return abort(403, auth()->user()->name . ' NO tiene permiso de acceso - Root');
        }

        $data = $hcompra->toArray();

        //$data['username']= 'Restaurada';
        //$data['created_at']=Carbon::now();
        //$data['updated_at']=Carbon::now();

        $compra = Compra::create($data);

        $lineas = HComline::where('compra_id', $hcompra->compra_id)->get();
        foreach ($lineas as $linea) {
            $l              = $linea->toArray();
            $l['compra_id'] = $compra->id;
            Comline::create($l);
        }

        $depositos = HDeposito::where('compra_id', $hcompra->compra_id)->get();
        foreach ($depositos as $deposito) {
            $dep              = $deposito->toArray();
            $dep['compra_id'] = $compra->id;
            Deposito::create($dep);
        }

        // $hcompra->delete();

        if (request()->wantsJson()) {
            return [
                'compra' => $compra,
            ];
        }

    }
}
