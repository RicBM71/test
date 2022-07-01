<?php

namespace App\Http\Controllers\Exportar;

use App\Empresa;
use Illuminate\Http\Request;
use App\Rules\RangoFechaRule;
use App\Exports\VendepoExport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class VentasDepositoController extends Controller
{

    public function index(){

        if (!hasConsultas()){
            return abort(403,auth()->user()->name.' NO tiene permiso de acceso - Consultas');
        }

        if (request()->wantsJson())
            return [
                'empresas' => Empresa::selEmpresas()->get(),
            ];

    }

    public function ventas(Request $request){

        if (!hasConsultas()){
            return abort(403,auth()->user()->name.' NO tiene permiso de acceso - Consultas');
        }

        $data = $request->validate([
            'fecha_d'  => ['required','date', new RangoFechaRule($request->fecha_d, $request->fecha_h)],
            'fecha_h'  => ['required','date'],
            'facturado'=> ['required','string', 'max:1'],
            'tipo'     => ['required','string', 'max:1'],
            'procedencia_empresa_id'     => ['nullable','integer'],
        ]);

        if ($data['tipo'] == 'D'){
            $registros = $this->deposito($data['fecha_d'], $data['fecha_h'], $data['facturado'], $data['procedencia_empresa_id']);
            $txt_tipo = 'en Depósito';
        }
        elseif($data['tipo'] == 'P'){
            $txt_tipo = 'a Comisión';
            $registros = $this->comision($data['fecha_d'], $data['fecha_h'], $data['facturado'], $data['procedencia_empresa_id']);
        }
        elseif($data['tipo'] == 'R'){
            $txt_tipo = 'Reubicados';
            $registros = $this->reubicados($data['fecha_d'], $data['fecha_h'], $data['facturado'], $data['procedencia_empresa_id']);
        }
        elseif($data['tipo'] == 'S'){
            $txt_tipo = 'Sin Reubicar';
            $registros = $this->sinreubicar($data['fecha_d'], $data['fecha_h'], $data['facturado'], $data['procedencia_empresa_id']);
        }
        else{
            $txt_tipo = 'Todas';
            $registros = $this->todas($data['fecha_d'], $data['fecha_h'], $data['facturado'], $data['procedencia_empresa_id']);
        }

        $txt_fac = $data['facturado'] == 'F' ? 'Facturados' : 'No facturados';

        $titulo = "Ventas ".$txt_tipo.": ".session('empresa')->nombre.' ('.$txt_fac.')';

        if (request()->wantsJson())
            return[
                'registros' => $registros,
                'titulo'    => $titulo
            ];
    }


    public function excel(Request $request){

        return Excel::download(new VendepoExport($request->data, $request->titulo), 'vendepo.xlsx');

    }


    public function deposito($d, $h, $facturado, $procedencia_empresa_id){


        $campo_fecha = $facturado=='V' ? 'fecha_albaran' : 'fecha_factura';
        $campo_alb   = $facturado=='V' ? ',serie_albaran AS serie, albaran AS numero' : ',serie_factura AS serie, factura AS numero';

        $campo_fecha_where = $facturado=='V' ? 'albaranes.fecha_albaran' : 'fecha_factura';

        $select=DB::getTablePrefix().'empresas.nombre AS empresa'.$campo_alb.','.$campo_fecha.' AS fecha,'.
                DB::getTablePrefix().'productos.referencia AS referencia,'.
                DB::getTablePrefix().'productos.nombre AS producto,'.
                DB::getTablePrefix().'albalins.importe_venta AS importe_venta,'.
                DB::getTablePrefix().'albalins.precio_coste AS precio_coste,'.
                DB::getTablePrefix().'empresas.sigla,'.
                DB::getTablePrefix().'ep.sigla AS procede';

        $albaranes = DB::table('albaranes')
                        ->select(DB::raw($select))
                        // ->join('empresas','albaranes.procedencia_empresa_id','=','empresas.id')
                        ->join('empresas','albaranes.empresa_id','=','empresas.id')
                        ->join('albalins','albalins.albaran_id','=','albaranes.id')
                        ->join('productos','albalins.producto_id','=','productos.id')
                        ->join('empresas AS ep','albaranes.procedencia_empresa_id','=','ep.id')
                        // ->where('albaranes.empresa_id', session('empresa')->id)
                        ->when($procedencia_empresa_id > 0, function ($query) use ($procedencia_empresa_id) {
                            return $query->where('procedencia_empresa_id', '=', $procedencia_empresa_id);
                        })
                        ->whereIn('albaranes.empresa_id', session('empresas_usuario'))
                        ->where('albaranes.tipo_id', 3)
                        // ->where('albaranes.procedencia_empresa_id', '>', 0)
                        ->where('albaranes.fase_id', '<>', 10)
                        ->where('albaranes.facturar', true)
                        ->whereDate($campo_fecha_where,'>=', $d)
                        ->whereDate($campo_fecha_where,'<=', $h)
                        ->whereNull('albalins.deleted_at')
                        ->whereRaw(DB::getTablePrefix().'productos.empresa_id <> '.DB::getTablePrefix().'productos.destino_empresa_id')
                        ->whereNull('productos.cliente_id')
                        ->orderBy('empresa')
                        ->orderBy('fecha')
                        ->orderBy('numero')
                        ->get();

        return $albaranes;

    }

    public function comision($d, $h, $facturado, $procedencia_empresa_id){


        $campo_fecha = $facturado=='V' ? 'fecha_albaran' : 'fecha_factura';
        $campo_alb   = $facturado=='V' ? ',serie_albaran AS serie, albaran AS numero' : ',serie_factura AS serie, factura AS numero';
        $campo_fecha_where = $facturado=='V' ? 'albaranes.fecha_albaran' : 'fecha_factura';

        $select=DB::getTablePrefix().'clientes.razon AS empresa'.$campo_alb.','.$campo_fecha.' AS fecha,'.
                DB::getTablePrefix().'productos.referencia AS referencia,'.
                DB::getTablePrefix().'productos.nombre AS producto,'.
                DB::getTablePrefix().'albalins.importe_venta AS importe_venta,'.
                DB::getTablePrefix().'albalins.precio_coste AS precio_coste,'.
                DB::getTablePrefix().'empresas.sigla,'.
                DB::getTablePrefix().'ep.sigla AS procede';

        $albaranes = DB::table('albaranes')
                        ->select(DB::raw($select))
                        ->join('albalins','albalins.albaran_id','=','albaranes.id')
                        ->join('empresas','albaranes.empresa_id','=','empresas.id')
                        ->join('productos','albalins.producto_id','=','productos.id')
                        ->join('clientes','productos.cliente_id','=','clientes.id')
                        ->join('empresas AS ep','albaranes.procedencia_empresa_id','=','ep.id')
                        ->whereIn('albaranes.empresa_id', session('empresas_usuario'))
                        ->when($procedencia_empresa_id > 0, function ($query) use ($procedencia_empresa_id) {
                            return $query->where('procedencia_empresa_id', '=', $procedencia_empresa_id);
                        })
                        // ->where('albaranes.empresa_id', session('empresa')->id)
                        ->where('albaranes.tipo_id', 3)
                        ->where('albaranes.fase_id', '<>', 10)
                        ->where('albaranes.facturar', true)
                        ->whereDate($campo_fecha_where,'>=', $d)
                        ->whereDate($campo_fecha_where,'<=', $h)
                        ->whereNull('albalins.deleted_at')
                        ->where('productos.cliente_id', '>', 0)
                        ->orderBy('empresa')
                        ->orderBy('fecha')
                        ->orderBy('numero')
                        ->get();

        return $albaranes;

    }

    public function todas($d, $h, $facturado, $procedencia_empresa_id){


        $campo_fecha = $facturado=='V' ? 'fecha_albaran' : 'fecha_factura';
        $campo_alb   = $facturado=='V' ? ',serie_albaran AS serie, albaran AS numero' : ',serie_factura AS serie, factura AS numero';

        $select=DB::getTablePrefix().'empresas.nombre AS empresa'.$campo_alb.','.$campo_fecha.' AS fecha,'.
                DB::getTablePrefix().'productos.referencia AS referencia,'.
                DB::getTablePrefix().'productos.nombre AS producto,'.
                DB::getTablePrefix().'albalins.importe_venta AS importe_venta,'.
                DB::getTablePrefix().'albalins.precio_coste AS precio_coste,'.
                DB::getTablePrefix().'empresas.sigla,'.
                DB::getTablePrefix().'ep.sigla AS procede';

        $albaranes = DB::table('albaranes')
                        ->select(DB::raw($select))
                        ->join('empresas','albaranes.empresa_id','=','empresas.id')
                        ->join('albalins','albalins.albaran_id','=','albaranes.id')
                        ->join('productos','albalins.producto_id','=','productos.id')
                        ->join('empresas AS ep','albaranes.procedencia_empresa_id','=','ep.id')
                        // ->where('albaranes.empresa_id', session('empresa')->id)
                        ->when($procedencia_empresa_id > 0, function ($query) use ($procedencia_empresa_id) {
                            return $query->where('procedencia_empresa_id', '=', $procedencia_empresa_id);
                        })
                        ->whereIn('albaranes.empresa_id', session('empresas_usuario'))
                        ->where('albaranes.tipo_id', 3)
                        ->where('albaranes.fase_id', '<>', 10)
                        ->where('albaranes.facturar', true)
                        ->whereRaw(DB::getTablePrefix().'productos.empresa_id <> '.DB::getTablePrefix().'productos.destino_empresa_id')
                        ->whereDate('albaranes.updated_at','>=', $d)
                        ->whereDate('albaranes.updated_at','<=', $h)
                        // ->whereDate($campo_fecha,'>=', $d)
                        // ->whereDate($campo_fecha,'<=', $h)
                        // ->whereNull('albaranes.procedencia_empresa_id')
                        ->whereNull('albalins.deleted_at')
                        ->orderBy('empresa')
                        ->orderBy('fecha')
                        ->orderBy('numero')
                        ->get();

        return $albaranes;

    }

    public function reubicados($d, $h, $facturado, $procedencia_empresa_id){


        $campo_fecha = $facturado=='V' ? 'fecha_albaran' : 'fecha_factura';
        $campo_alb   = $facturado=='V' ? ',serie_albaran AS serie, albaran AS numero' : ',serie_factura AS serie, factura AS numero';

        $select=DB::getTablePrefix().'empresas.nombre AS empresa'.$campo_alb.','.$campo_fecha.' AS fecha,'.
                DB::getTablePrefix().'productos.referencia AS referencia,'.
                DB::getTablePrefix().'productos.nombre AS producto,'.
                DB::getTablePrefix().'albalins.importe_venta AS importe_venta,'.
                DB::getTablePrefix().'albalins.precio_coste AS precio_coste,'.
                DB::getTablePrefix().'empresas.sigla,'.
                DB::getTablePrefix().'ep.sigla AS procede';

        $albaranes = DB::table('albaranes')
                        ->select(DB::raw($select))
                        ->join('empresas','albaranes.empresa_id','=','empresas.id')
                        ->join('albalins','albalins.albaran_id','=','albaranes.id')
                        ->join('productos','albalins.producto_id','=','productos.id')
                        ->join('empresas AS ep','albaranes.procedencia_empresa_id','=','ep.id')
                        // ->where('albaranes.empresa_id', session('empresa')->id)
                        ->when($procedencia_empresa_id > 0, function ($query) use ($procedencia_empresa_id) {
                            return $query->where('procedencia_empresa_id', '=', $procedencia_empresa_id);
                        })
                        ->whereIn('albaranes.empresa_id', session('empresas_usuario'))
                        ->where('albaranes.tipo_id', 3)
                        ->where('albaranes.fase_id', '<>', 10)
                        ->where('albaranes.facturar', true)
                        ->whereRaw(DB::getTablePrefix().'productos.empresa_id <> '.DB::getTablePrefix().'productos.destino_empresa_id')
                        ->whereDate($campo_fecha,'>=', $d)
                        ->whereDate($campo_fecha,'<=', $h)
                        ->where('albaranes.procedencia_empresa_id','>',0)
                        ->whereNull('albalins.deleted_at')
                        ->orderBy('empresa')
                        ->orderBy('fecha')
                        ->orderBy('numero')
                        ->get();

        return $albaranes;

    }

    public function sinreubicar($d, $h, $facturado, $procedencia_empresa_id){


        $campo_fecha = $facturado=='V' ? 'fecha_albaran' : 'fecha_factura';
        $campo_alb   = $facturado=='V' ? ',serie_albaran AS serie, albaran AS numero' : ',serie_factura AS serie, factura AS numero';

        $select=DB::getTablePrefix().'empresas.nombre AS empresa'.$campo_alb.','.$campo_fecha.' AS fecha,'.
                DB::getTablePrefix().'productos.referencia AS referencia,'.
                DB::getTablePrefix().'productos.nombre AS producto,'.
                DB::getTablePrefix().'albalins.importe_venta AS importe_venta,'.
                DB::getTablePrefix().'albalins.precio_coste AS precio_coste,'.
                DB::getTablePrefix().'empresas.sigla,'.
                DB::getTablePrefix().'ep.sigla AS procede';

        $albaranes = DB::table('albaranes')
                        ->select(DB::raw($select))
                        ->join('empresas','albaranes.empresa_id','=','empresas.id')
                        ->join('albalins','albalins.albaran_id','=','albaranes.id')
                        ->join('productos','albalins.producto_id','=','productos.id')
                        ->join('empresas AS ep','albaranes.procedencia_empresa_id','=','ep.id')
                        // ->where('albaranes.empresa_id', session('empresa')->id)
                        ->when($procedencia_empresa_id > 0, function ($query) use ($procedencia_empresa_id) {
                            return $query->where('procedencia_empresa_id', '=', $procedencia_empresa_id);
                        })
                        ->whereIn('albaranes.empresa_id', session('empresas_usuario'))
                        ->where('albaranes.tipo_id', 3)
                        ->where('albaranes.fase_id', '<>', 10)
                        ->where('albaranes.facturar', true)
                        ->whereRaw(DB::getTablePrefix().'productos.empresa_id <> '.DB::getTablePrefix().'productos.destino_empresa_id')
                        // ->whereDate($campo_fecha,'>=', $d)
                        // ->whereDate($campo_fecha,'<=', $h)
                        ->whereDate('albaranes.updated_at','>=', $d)
                        ->whereDate('albaranes.updated_at','<=', $h)
                        ->whereNull('albaranes.procedencia_empresa_id')
                        ->whereNull('albalins.deleted_at')
                        ->orderBy('empresa')
                        ->orderBy('fecha')
                        ->orderBy('numero')
                        ->get();

        return $albaranes;

    }



}
