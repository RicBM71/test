<?php

namespace App\Http\Controllers\Mto;

use App\Iva;
use App\Tag;
use App\Clase;
use App\Marca;
use App\Estado;
use App\Almacen;
use App\Cliente;
use App\Empresa;
use App\Quilate;
use App\Etiqueta;
use App\Garantia;
use App\Producto;
use App\Categoria;
use Carbon\Carbon;
use App\Traits\SessionTrait;
use Illuminate\Http\Request;
use App\Rules\RangoFechaRule;
use App\Exports\ProductosExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProducto;
use App\Rules\MaxDiasRangoFechaRule;
use App\Scopes\EmpresaProductoScope;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\UpdateProducto;

class ProductosController extends Controller
{

    use SessionTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (request()->session()->has('filtro_pro')) {
            $data = $this->miFiltro();
        } else {
            // if (auth()->user()->hasRole('Admin'))
            //     $data = Producto::withTrashed()->with(['clase','estado','destino','empresa'])->orderBy('id','desc')
            //                             ->where('updated_at','>=',Carbon::now()->subDays(30))
            //                             ->get()
            //                             ->take(50);
            // else
            $data = Producto::with(['clase', 'estado', 'destino', 'empresa'])->orderBy('id', 'desc')
                ->where('updated_at', '>=', Carbon::now()->subDays(30))
                ->get()
                ->take(50);

        }

        if (request()->wantsJson()) {
            return $data;
        }

    }

    public function filtrar(Request $request)
    {

        $data = $this->validar($request);

        session(['filtro_pro' => $data]);

        return $this->miFiltro();

    }

    private function miFiltro()
    {

        //ini_set('memory_limit', '512M');

        $data = request()->session()->get('filtro_pro');

        if (session('parametros')->aislar_empresas == false) {

            $data = Producto::withOutGlobalScope(EmpresaProductoScope::class)->with(['clase', 'estado', 'destino', 'empresa', 'cliente', 'etiqueta'])
                ->localizacion($data['sinscope'])
                ->empresa($data['empresa_id'])
                ->destino($data['destino_empresa_id'])
                ->alta($data['alta'])
                ->referencia($data['referencia'])
                ->fecha($data['fecha_d'], $data['fecha_h'], $data['tipo_fecha'])
                ->clase($data['clase_id'])
                ->estado($data['estado_id'])
                ->notasNombre($data['notas'])
                ->refPol($data['ref_pol'])
                ->precioPeso($data['precio'])
                ->quilates($data['quilates'])
                ->online($data['online'])
                ->internos($data['interno'])
                ->asociado($data['cliente_id'])
                ->categoria($data['categoria_id'])
                ->etiqueta($data['etiqueta_id'])
                ->almacen($data['almacen_id'])
                ->marca($data['marca_id'])
                ->caracteristicas($data['caracteristicas'])
                ->orderBy('id', 'desc')
                ->get()
                ->take(1999);

        } else {

            $data = Producto::with(['clase', 'estado', 'destino', 'empresa', 'cliente', 'etiqueta'])
                ->alta($data['alta'])
                ->referencia($data['referencia'])
                ->fecha($data['fecha_d'], $data['fecha_h'], $data['tipo_fecha'])
                ->clase($data['clase_id'])
                ->estado($data['estado_id'])
                ->notasNombre($data['notas'])
                ->refPol($data['ref_pol'])
                ->precioPeso($data['precio'])
                ->quilates($data['quilates'])
                ->online($data['online'])
                ->internos($data['interno'])
                ->asociado($data['cliente_id'])
                ->categoria($data['categoria_id'])
                ->etiqueta($data['etiqueta_id'])
                ->marca($data['marca_id'])
                ->orderBy('id', 'desc')
                ->get()
                ->take(1999);

        }

        return $data;

    }

    // private function miFiltro2(){

    //     $data = request()->session()->get('filtro_pro');

    //     if (esAdmin() && session('parametros')->aislar_empresas == false && $data['sinscope']==true){
    //         if ($data['alta'] == false)
    //             $data = Producto::withOutGlobalScope(EmpresaProductoScope::class)->withTrashed()->with(['clase','estado','destino','empresa'])
    //                         ->where('destino_empresa_id', session('empresa_id'))
    //                         ->referencia($data['referencia'])
    //                         ->fecha($data['fecha_d'],$data['fecha_h'],$data['tipo_fecha'])
    //                         ->clase($data['clase_id'])
    //                         ->estado($data['estado_id'])
    //                         ->empresa($data['empresa_id'])
    //                         ->notasNombre($data['notas'])
    //                         ->refPol($data['ref_pol'])
    //                         ->precioPeso($data['precio'])
    //                         ->quilates($data['quilates'])
    //                         ->online($data['online'])
    //                         ->internos($data['interno'])
    //                         ->asociado($data['cliente_id'])
    //                         ->orderBy('id','desc')
    //                         ->get()
    //                         ->take(999);
    //         else{

    //             $data = Producto::withOutGlobalScope(EmpresaProductoScope::class)->with(['clase','estado','destino','empresa'])
    //                     ->where('destino_empresa_id', session('empresa_id'))
    //                     ->referencia($data['referencia'])
    //                     ->fecha($data['fecha_d'], $data['fecha_h'],$data['tipo_fecha'])
    //                     ->clase($data['clase_id'])
    //                     ->estado($data['estado_id'])
    //                     ->empresa($data['empresa_id'])
    //                     ->notasNombre($data['notas'])
    //                     ->refPol($data['ref_pol'])
    //                     ->precioPeso($data['precio'])
    //                     ->quilates($data['quilates'])
    //                     ->online($data['online'])
    //                     ->internos($data['interno'])
    //                     ->asociado($data['cliente_id'])
    //                     ->orderBy('id','desc')
    //                     ->get()
    //                     ->take(999);
    //         }

    //     }
    //     else{
    //         if ($data['alta'] == false)
    //             $data = Producto::withTrashed()->with(['clase','estado','destino','empresa'])
    //                         ->where('destino_empresa_id', session('empresa_id'))
    //                         ->referencia($data['referencia'])
    //                         ->fecha($data['fecha_d'],$data['fecha_h'],$data['tipo_fecha'])
    //                         ->clase($data['clase_id'])
    //                         ->estado($data['estado_id'])
    //                         ->empresa($data['empresa_id'])
    //                         ->notasNombre($data['notas'])
    //                         ->refPol($data['ref_pol'])
    //                         ->precioPeso($data['precio'])
    //                         ->quilates($data['quilates'])
    //                         ->online($data['online'])
    //                         ->internos($data['interno'])
    //                         ->asociado($data['cliente_id'])
    //                         ->orderBy('id','desc')
    //                         ->get()
    //                         ->take(999);
    //         else{

    //             $data = Producto::with(['clase','estado','destino','empresa'])
    //                     ->where('destino_empresa_id', session('empresa_id'))
    //                     ->referencia($data['referencia'])
    //                     ->fecha($data['fecha_d'], $data['fecha_h'],$data['tipo_fecha'])
    //                     ->clase($data['clase_id'])
    //                     ->estado($data['estado_id'])
    //                     ->empresa($data['empresa_id'])
    //                     ->notasNombre($data['notas'])
    //                     ->refPol($data['ref_pol'])
    //                     ->precioPeso($data['precio'])
    //                     ->quilates($data['quilates'])
    //                     ->online($data['online'])
    //                     ->internos($data['interno'])
    //                     ->asociado($data['cliente_id'])
    //                     ->orderBy('id','desc')
    //                     ->get()
    //                     ->take(999);
    //         }
    //     }
    //     return $data;

    // }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', new Producto);

        if (request()->wantsJson()) {
            return [
                'clases'   => Clase::selGrupoClase(),
                'estados'  => Estado::selEstados(),
                'quilates' => Quilate::selQuilates(),
            ];
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProducto $request)
    {
        $this->authorize('create', new Producto);

        $data = $request->validated();

        $data['empresa_id'] = session()->get('empresa')->id;
        if ($data['destino_empresa_id'] == null) {
            $data['destino_empresa_id'] = session()->get('empresa')->id;
        }

        $data['almacen_id'] = session()->get('empresa')->almacen_id;

        //$data['etiqueta_id']  = $data['clase_id'] == 3 ? 3 : 1;

        //if ($data['precio_venta'] == 0)
        //    $data['precio_venta'] = $data['precio_coste'] + round($data['precio_coste'] * 30 / 100, 0);

        if ($data['clase_id'] != 1) {
            $data['quilates'] = null;
        }

        $data['username']         = $request->user()->username;
        $data['precio_ecommerce'] = $data['precio_venta'];

        $reg = Producto::create($data);

        $data['referencia'] = session('empresa')->sigla . session('empresa')->setIncrementaProducto($reg->id);

        // if (session('empresa')->ult_producto == 0)
        //     $data['referencia']=session('empresa')->sigla.$reg->id;
        // else{
        //     \Log::info('pasa');
        //     $data['referencia']=session('empresa')->sigla.session('empresa')->setIncrementaProducto();
        // }

        $reg->update($data);

        $reg->load('clase', 'empresa');

        if (request()->wantsJson()) {
            return ['producto' => $reg, 'message' => 'EL registro ha sido creado'];
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $producto = Producto::withOutGlobalScope(EmpresaProductoScope::class)->withTrashed()
            ->with(['estado', 'clase', 'iva', 'destino'])
            ->where('id', $id)
            ->whereIn('empresa_id', session('empresas_usuario'))
            ->firstOrFail();

        if (request()->wantsJson()) {
            return [
                'producto'   => $producto->load('clase', 'empresa', 'tags'),
                'empresas'   => Empresa::selEmpresas()->Venta()->get(),
                'clases'     => Clase::selGrupoClase(),
                'estados'    => Estado::selEstados(),
                'ivas'       => Iva::selIvas(),
                'almacenes'  => Almacen::selAlmacenes(),
                'etiquetas'  => Etiqueta::selEstados(),
                'asociados'  => Cliente::selAsociados(),
                'quilates'   => Quilate::selQuilates(),
                'garantias'  => Garantia::selGarantias(),
                'marcas'     => Marca::selMarcas(),
                'categorias' => Categoria::selCategorias(),
                'stock_real' => Producto::getStockReal($producto->id),
                'tags'       => Tag::selTags(),
            ];
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        if (hasDesLoc()) {
            // con esto cambiamos de empresa si la empresa no coincide
            $producto   = Producto::withOutGlobalScope(EmpresaProductoScope::class)->withTrashed()->findOrFail($id);
            $collection = session('empresas_usuario');
            if ($collection->search($producto->empresa_id, true) === false && $collection->search($producto->destino_empresa_id, true) === false) {
                return abort(404, "No se ha encontrado el registro");
            }

            if ($producto->empresa_id != session('empresa_id') && $producto->destino_empresa_id != session('empresa_id')) {
                $parametros = $this->loadSession($producto->empresa_id);
            } else {
                $parametros = false;
            }
        } else {

            if (hasEdtPro()) {
                $producto = Producto::withTrashed()->findOrFail($id);
            } else {
                $producto = Producto::findOrFail($id);
            }

            $parametros = false;
        }

        if ($producto->deleted_at != null) {
            $this->show($producto->id);
            return;
        }

        $this->authorize('update', $producto);

        //$tags = $producto->load('tags');

        if (request()->wantsJson()) {
            return [
                'parametros' => $parametros,
                'producto'   => $producto->load('clase', 'empresa', 'tags'),
                'empresas'   => Empresa::selEmpresas()->Venta()->get(),
                'clases'     => Clase::selGrupoClase(),
                'estados'    => Estado::selEstados(),
                'ivas'       => Iva::selIvas(),
                'almacenes'  => Almacen::selAlmacenes(),
                'etiquetas'  => Etiqueta::selEstados(),
                'asociados'  => Cliente::selAsociados(),
                'quilates'   => Quilate::selQuilates(),
                'garantias'  => Garantia::selGarantias(),
                'marcas'     => Marca::selMarcas(),
                'categorias' => Categoria::selCategorias(),
                'stock_real' => Producto::getStockReal($producto->id),
                'tags'       => Tag::selTags(),
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
    public function update(UpdateProducto $request, Producto $producto)
    {
        $this->authorize('update', $producto);

        $data = $request->validated();

        $clase = Clase::findOrfail($data['clase_id']);

        if ($clase->stockable == false) {
            $data['stock'] = 1;
        }

        if ($data['precio_ecommerce'] == 0 && $data['precio_venta'] > 0) {
            $data['precio_ecommerce'] = $data['precio_venta'];
        }

        $data['username'] = $request->user()->username;

        $producto->update($data);

        $tags = collect($data['tags'])->pluck('id')->toArray();
        //$t = $tags->toArray();

        $producto->tags()->sync($tags);

        if (request()->wantsJson()) {
            return [
                'producto'   => $producto->load('clase', 'empresa', 'tags'),
                'stock_real' => Producto::getStockReal($producto->id),
                'message'    => 'EL producto ha sido modificado',
            ];
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        // comprobamos si el producto existe en algún albarán
        // LO QUITO, SI NO FUNCIONA Relación en albalín, que en principio si, habrá que cambiar por estado_id a 1.

        // if (DB::table('albalins')->where('producto_id', $id)->exists()){
        //     return abort(411, 'El producto existe en albaranes, no se puede borrar');
        // }

        if (hasEdtPro()) {
            $producto = Producto::withOutGlobalScope(EmpresaProductoScope::class)->withTrashed()->findOrFail($id);
        } else {
            $producto = Producto::findOrFail($id);
        }

        if (!esRoot() && $producto->estado_id > 2) {
            abort(411, 'No se puede borrar el producto');
        }

        // \Log::info($producto);

        if ($producto->trashed()) {
            $producto->restore();
            $msg = "Registro restaurado!";
        } else {
            if (hasEdtPro() && hasHardDel()) {
                $msg = "Registro eliminado permanentemente!";
                $producto->forceDelete();
            } else {
                $msg                = "Registro eliminado!";
                $producto->username = session('username');
                $producto->save();
                $producto->delete();
            }
        }

        // if (esRoot())
        //     $data = Producto::with(['clase','estado','destino'])->withTrashed()->get();
        // else
        //     $data = Producto::with(['clase','estado','destino'])->all();

        if (request()->wantsJson()) {
            return [
                'producto' => $producto->load('clase', 'empresa'),
                'msg'      => $msg,
            ];
        }
    }

    /**
     * Recibe las facturas por request, previamente de $this->lisfac()
     *
     * @param Request $request
     * @return void
     */
    public function excel(Request $request)
    {

        if (!hasConsultas()) {
            return abort(403, auth()->user()->name . ' NO tiene permiso de acceso - Consultas');
        }

        $data = $this->validar($request);

        session(['filtro_pro' => $data]);

        $collect = $this->miFiltro();

        return Excel::download(new ProductosExport($collect, 'Productos ' . session('empresa')->razon), 'inventario.xlsx');

    }

    private function validar($request)
    {

        return $request->validate([
            'fecha_d'            => ['date', 'nullable'],
            'fecha_h'            => ['date', 'nullable'],
            'clase_id'           => ['integer', 'nullable'],
            'estado_id'          => ['integer', 'nullable'],
            'marca_id'           => ['integer', 'nullable'],
            'categoria_id'       => ['integer', 'nullable'],
            'referencia'         => ['string', 'nullable'],
            'ref_pol'            => ['string', 'nullable'],
            'precio'             => ['string', 'nullable'],
            'notas'              => ['string', 'nullable'],
            'quilates'           => ['integer', 'nullable'],
            'online'             => ['boolean'],
            'alta'               => ['string', 'required'],
            'cliente_id'         => ['nullable', 'integer'],
            'tipo_fecha'         => ['string', 'required'],
            'fecha_d'            => ['nullable', 'date', new RangoFechaRule($request->fecha_d, $request->fecha_h)],
            'fecha_h'            => ['nullable', 'date', new MaxDiasRangoFechaRule($request->fecha_d, $request->fecha_h)],
            'empresa_id'         => ['nullable', 'integer'],
            'destino_empresa_id' => ['nullable', 'integer'],
            'etiqueta_id'        => ['nullable', 'integer'],
            'almacen_id'         => ['nullable', 'integer'],
            'sinscope'           => ['boolean'],
            'interno'            => ['string', 'required'],
            'caracteristicas'    => ['nullable', 'string'],
        ]);

    }

}
