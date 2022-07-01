<?php

namespace App\Http\Controllers\Mto;

use App\Rfid;
use App\Clase;
use App\Producto;
use App\Recuento;
use App\Categoria;
use Carbon\Carbon;
use App\Scopes\EmpresaScope;
use Illuminate\Http\Request;
use App\Exports\RecuentoExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Scopes\EmpresaProductoScope;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StoreRecuentoRequest;

class RecuentosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$data = Recuento::with(['producto','rfid','estado'])->get();
        $data = Recuento::withOutGlobalScope(EmpresaScope::class)
                    ->select('referencia','producto_id','productos.nombre AS nombre','precio_coste','rfids.nombre AS rfid','estados.nombre AS estado',
                             'productos.deleted_at', 'productos.notas', 'rfid_id', 'recuentos.id AS recuento_id', 'productos.empresa_id AS origen',
                             'productos.destino_empresa_id AS destino','recuentos.empresa_id','productos.peso_gr')
                    ->join('productos','productos.id','=','producto_id')
                    ->join('rfids','rfids.id','=','rfid_id')
                    ->join('estados','estados.id','=','productos.estado_id')
                    ->where('recuentos.empresa_id', session('empresa_id'))
                    ->get();

        if (request()->wantsJson())
            return $data;
    }

    public function filtrar(Request $request)
    {

        $data = $request->validate([
            'rfid_id'  => ['nullable','integer'],
            'clase_id' => ['nullable','integer'],
            'categoria_id' => ['nullable','integer'],
            'alta'     => ['boolean'],
        ]);

        session(['filtro_rec' => $data]);

        if (request()->wantsJson()){
            return $this->seleccionar();
        }

    }

    /**
     *  @param array $data // condiciones where genéricas
     *  @param array $doc  // condiciones para documentos
     */
    private function seleccionar(){

        $data = session('filtro_rec');

        // if ($data['clase_id'] == null){
        //     $op_clase = '>=';
        //     $data['clase_id'] = 0;
        // }else{
        //     $op_clase = '=';
        // }

        $clase_id = $data['clase_id'];
        $categoria_id = $data['categoria_id'];

        $collection = Recuento::withOutGlobalScope(EmpresaScope::class)
                    ->select('referencia','producto_id','productos.nombre AS nombre','precio_coste','rfids.nombre AS rfid','estados.nombre AS estado',
                             'productos.deleted_at','productos.notas','rfid_id', 'recuentos.id AS recuento_id', 'productos.empresa_id AS origen',
                             'productos.destino_empresa_id AS destino','recuentos.empresa_id','productos.peso_gr')
                    ->join('productos','productos.id','=','producto_id')
                    ->join('rfids','rfids.id','=','rfid_id')
                    ->join('estados','estados.id','=','productos.estado_id')
                    ->where('recuentos.empresa_id', session('empresa_id'))
                    ->rfid($data['rfid_id'])
                    ->when($clase_id > 0, function ($query) use ($clase_id) {
                        return $query->where('productos.clase_id', '=', $clase_id);
                    })
                    ->when($categoria_id > 0, function ($query) use ($categoria_id) {
                        return $query->where('productos.categoria_id', '=', $categoria_id);
                    })
                    ->get();

        if ($data['alta'] == true){
            $filtered = $collection->where('deleted_at', null);

            foreach ($filtered as $row){
                $a[]=$row;
            }
            return $a;
        }

        return $collection;

        // $recuento = Recuento::with(['producto','rfid','estado'])
        //                     ->rfid($data['rfid_id'])
        //                     ->get();

        // if ($data['clase_id'] == null)
        //     return $recuento;
        // else{

        //     $response = array();
        //     foreach ($recuento as $row){

        //         if ($row->producto != null){
        //             if ($row->producto->clase_id != $data['clase_id'])
        //                 continue;

        //             $response[] = $row;
        //         }

        //     }

        //     return $response;

        // }


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        if (request()->wantsJson())
            return [
                'rfids'    => Rfid::selRfid(),
                'clases'   => Clase::selGrupoClase(),
                'categorias'   => Categoria::selCategorias(),
                'recuentos' => Recuento::with(['producto','rfid','estado'])->get(),
            ];

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRecuentoRequest $request)
    {

        $data = $request->validated();

        $data['empresa_id'] = session('empresa_id');

        if ($data['prefijo'] != null){
            $ref = strtoupper($data['prefijo']).$data['referencia'];
            try {

                $producto = Producto::withOutGlobalScope(EmpresaProductoScope::class)
                                ->withTrashed()
                                ->where('referencia',$ref)
                                ->firstOrFail();
            } catch (\Exception $e) {
                return abort(404, 'Producto/referencia no existe');
            }
        }else{
            try {
                $producto = Producto::withOutGlobalScope(EmpresaProductoScope::class)
                                ->withTrashed()
                                ->findOrFail($data['referencia']);
              //  \Log::info($producto);
            } catch (\Exception $e) {
                \Log::info($e);
                return abort(404, 'Producto/id no existe');
            }
        }

        if ($producto->empresa_id == session('empresa_id') || $producto->destino_empresa_id == session('empresa_id'))
            if ($producto->estado_id == 4)
                $rfid_id = 5;
            else
                $rfid_id = 1;
        else
            $rfid_id = 2;

        $data['producto_id']=$producto->id;
        $data['fecha']=$data['fecha'];
        $data['estado_id']=$producto->estado_id;
        $data['rfid_id']=$rfid_id;


        try {
            $reg = Recuento::create($data);
        } catch (\Exception $th) {
            return abort(411, 'El producto ya está en recuento');
        }

        $reg->load(['producto','rfid','estado']);

        if (request()->wantsJson())
            return ['recuento'=>$reg, 'message' => 'EL registro ha sido creado'];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Recuento $recuento)
    {
        $data = $request->validate([
            'rfid_id' => ['required', 'integer'],
        ]);

        if ($data['rfid_id'] == 3)
            $data['rfid_id'] = 13;
        else
            $data['rfid_id'] = 3;

        $recuento->update($data);

        $recuento->load(['producto','rfid','estado']);

        if (request()->wantsJson())
            return [
                'rfid'      => $recuento->rfid->nombre,
                'rfid_id'   => $data['rfid_id'],
                'message'   => 'EL registro ha sido modificado'];

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Recuento $recuento)
    {
        $recuento->delete();

        return ['msg' => 'Producto de recuento borrado'];
    }

    /**
     *
     * Rellena el recuento con piezas que deberían existir en recuento
     *
     * @return void
     */
    public function close(Request $request){


        $data = $request->validate([
            'fecha' => ['required', 'date'],
        ]);


        $productos = DB::table('productos')->select('*')
                    //->whereRaw('('.DB::getTablePrefix().'productos.empresa_id = '.session('empresa_id').' OR destino_empresa_id = '.session('empresa_id').')')
                    ->where('destino_empresa_id', session('empresa_id'))
                    ->whereIn('estado_id', [1,2,3])
                    ->whereRaw(DB::getTablePrefix().'productos.id NOT IN (SELECT producto_id FROM '.DB::getTablePrefix().'recuentos WHERE empresa_id = '.session('empresa_id').')')
                    ->whereNull('deleted_at')
                    ->get();

        $insert=array();
        foreach ($productos as $row){

            $insert[]=array(
                'empresa_id'         => session('empresa_id'),
                'fecha'              => $data['fecha'],
                'producto_id'        => $row->id,
                'estado_id'          => $row->estado_id,
                'rfid_producto_id'   => $row->id,
                'destino_empresa_id' => session('empresa_id'),
                'rfid_id'            => 3,
                'username'           => session('username'),
            );

        }

        DB::table('recuentos')->insert($insert);

        $data = Recuento::withOutGlobalScope(EmpresaScope::class)
                    ->select('referencia','producto_id','productos.nombre AS nombre','precio_coste','rfids.nombre AS rfid','estados.nombre AS estado',
                            'productos.deleted_at', 'productos.notas', 'rfid_id', 'recuentos.id AS recuento_id', 'productos.empresa_id AS origen',
                            'productos.destino_empresa_id AS destino','productos.peso_gr')
                    ->join('productos','productos.id','=','producto_id')
                    ->join('rfids','rfids.id','=','rfid_id')
                    ->join('estados','estados.id','=','productos.estado_id')
                    ->where('recuentos.empresa_id', session('empresa_id'))
                    ->get();

        //return Recuento::with(['producto','rfid','estado'])->get();


    }

    public function reset(Request $request){

        if (!hasEdtPro())
            return abort(411,'No autorizado');

        if ($request->reset == true)
            DB::table('recuentos')->where('empresa_id', session('empresa_id'))->delete();
        else
            DB::table('recuentos')->where('empresa_id',session('empresa_id'))
                ->where('rfid_id',3)
                ->delete();

        return response('Recuento borrado', 200);

    }


    /**
     * Recibe las facturas por request, previamente de $this->lisfac()
     *
     * @param Request $request
     * @return void
     */
    public function excel(Request $request){

        // foreach ($request->data as $row){
        //     \Log::info($row->producto_id);
        // }

        return Excel::download(new RecuentoExport($request->data, 'Recuento '.session('empresa')->razon), 'recuento.xlsx');


    }

    public function estados(Request $request){

      // return "lll";

        if (request()->wantsJson())
            return DB::table('recuentos')
                ->select(DB::raw('COUNT(*) as registros, nombre'))
                ->join('rfids','rfids.id','=','rfid_id')
                ->groupBy('nombre')
                ->get();

    }

    public function test(Request $request){


        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'codigos' => ['required'],
        ]);

        $lista = explode("\n", $data['codigos']);

        $rec['empresa_id'] = session('empresa_id');

        $insert = array();
        foreach ($lista as $referencia){

            try {

                $producto = Producto::withOutGlobalScope(EmpresaProductoScope::class)
                                ->withTrashed()
                                ->where('referencia',$referencia)
                                ->firstOrFail();
            } catch (\Exception $e) {
                continue;
            }

            if ($producto->empresa_id == session('empresa_id') || $producto->destino_empresa_id == session('empresa_id'))
                if ($producto->estado_id == 4)
                    $rfid_id = 5;
                else
                    $rfid_id = 1;
            else
                $rfid_id = 2;

            $rec['producto_id']=$producto->id;
            $rec['fecha']=$data['fecha'];
            $rec['estado_id']=$producto->estado_id;
            $rec['rfid_id']=$rfid_id;

            $insert[]=$rec;

        }

        DB::table('recuentos')->insertOrIgnore($insert);

        if (request()->wantsJson())
            return response('Procesado ok!', 200);


    }

    public function reubicar(Request $request, Recuento $recuento)
    {


        if ($recuento->rfid_id == 2)
            $data['rfid_id'] = 12;
        else
            $data['rfid_id'] = 2;

        $recuento->update($data);

        $data_pro = [
            'destino_empresa_id' => session('empresa_id'),
            'username' => session('username'),
            'updated_at' => Carbon::now()
        ];

        DB::table('productos')->where('id', $recuento->producto_id)
            ->update($data_pro);

        $recuento->load(['producto','rfid','estado']);

        if (request()->wantsJson())
            return [
                'rfid'      => $recuento->rfid->nombre,
                'rfid_id'   => $data['rfid_id'],
                'message'   => 'EL registro ha sido modificado'];

    }



}
