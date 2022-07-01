<?php

namespace App\Http\Controllers\Ventas;

use App\Tipo;
use App\Cobro;
use App\Fpago;
use App\Cuenta;
use App\Estado;
use App\Fixing;
use App\Taller;
use App\Albalin;
use App\Albaran;
use App\Empresa;
use App\Contador;
use App\Halbaran;
use Carbon\Carbon;
use App\Scopes\EmpresaScope;
use App\Traits\SessionTrait;
use Illuminate\Http\Request;
use App\Exports\AlbaranesExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\Ventas\FiltroAlbRequest;
use App\Http\Requests\Ventas\StoreAlbaranRequest;
use App\Http\Requests\Ventas\UpdateAlbaranRequest;

class AlbaranesController extends Controller
{

    use SessionTrait;

    public function index()
    {

        if (request()->session()->has('filtro_ven')) {
            $data = $this->miFiltro();
        } else {
            $data = Albaran::with(['cliente', 'albalins', 'tipo', 'fase'])
                ->tipo(null)
                ->where('fecha_albaran', '=', Carbon::today())
                ->get();
        }

        if (request()->wantsJson()) {
            return $data;
        }

    }

    public function filtrar(FiltroAlbRequest $request)
    {

        $data = $request->validated();

        session(['filtro_ven' => $data]);

        return $this->miFiltro();

    }

    private function miFiltro()
    {

        $data = request()->session()->get('filtro_ven');

        if ($data['reservas']) {
            return Albaran::with(['cliente', 'albalins', 'tipo', 'fase'])
                ->tipo(3)
                ->fase(10)
                ->get();
        } else {
            return Albaran::with(['cliente', 'albalins', 'tipo', 'fase'])
                ->tipo($data['tipo_id'])
                ->fecha($data['fecha_d'], $data['fecha_h'], $data['quefecha'])
                ->fpago($data['fpago_id'])
                ->fase($data['fase_id'])
                ->facturados($data['facturado'])
                ->depositos($data['depositos'])
                ->pedido($data['pedido'])
                ->clitxt($data['clitxt'])
                ->get();
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $this->authorize('create', Albaran::class);

        // if (!auth()->user()->hasPermissionTo('addcom')){
        //     return abort(422,auth()->user()->name.' NO tiene permiso para crear compras');
        // }

        if (request()->wantsJson()) {
            return [
                'tipos' => Tipo::selTiposWithContador(),
            ];
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAlbaranRequest $request)
    {

        $this->authorize('create', Albaran::class);

        $data = $request->validated();

        $fecha_albaran = Carbon::parse($data['fecha_albaran']);
        $ejercicio     = $fecha_albaran->format('Y');

        $contador = Contador::incrementaContador($ejercicio, $data['tipo_id']);

        $data['albaran']       = $contador['ult_albaran'];
        $data['serie_albaran'] = $contador['serie_albaran'];
        $data['fase_id']       = 10;

        $data['fpago_id'] = $data['tipo_id'] == 3 ? null : 2;

        if ($data['fpago_id'] == 2) {
            $iban = Cuenta::defecto()->first();
            if ($iban != null) {
                $data['cuenta_id'] = $iban->id;
            }

        }

        $data['empresa_id'] = session()->get('empresa')->id;
        //    $data['procedencia_empresa_id'] =  session()->get('empresa')->id;

        $data['username'] = $request->user()->username;

        $reg = Albaran::create($data);

        if (request()->wantsJson()) {
            return [
                'albaran'  => $reg,
                'contador' => $contador,
                'message'  => 'EL registro ha sido creado',
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

        $albarane = Albaran::withOutGlobalScope(EmpresaScope::class)->findOrFail($id);

        if ($albarane->tipo_id == 4 && !hasConsultas()) {
            return abort(404, 'No se ha podido cargar este albarán - Consultas Requerido');
        }

        // con esto cambiamos de empresa si la empresa no coincide
        $collection = session('empresas_usuario');
        if ($collection->search($albarane->empresa_id, true) === false) {
            return abort(404, "No se ha encontrado el registro");
        }

        if ($albarane->empresa_id != session('empresa_id')) {
            $parametros = $this->loadSession($albarane->empresa_id);
        } else {
            $parametros = false;
        }

        $this->authorize('update', $albarane);

        if (request()->wantsJson()) {
            return [
                'parametros' => $parametros,
                'albaran'    => $albarane->load(['cliente', 'tipo', 'fase', 'motivo', 'fpago', 'cuenta', 'procedencia']),
                'cuentas'    => Cuenta::selCuentas(),
                'fpagos'     => Fpago::selFpagos(),
                'talleres'   => Taller::selTalleres(),
                'empresas'   => Empresa::selEmpresas()->Venta()->get(),
                'fixing'     => Fixing::getFixDia(1, date('Y-m-d'), $albarane->fecha_albaran),
                'envios'     => Estado::enviosActivos(),
            ];
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        try {
            $albarane = Albaran::withOutGlobalScope(EmpresaScope::class)->findOrFail($id);
        } catch (\Exception $e) {
            try {
                $albarane = Albaran::withTrashed()->withOutGlobalScope(EmpresaScope::class)->findOrFail($id);
            } catch (\Exception $e) {
                return abort(404, 'El albarán no existe');
            }
        }

        if ($albarane->tipo_id == 4 && !hasConsultas()) {
            return abort(404, 'No se ha podido cargar este albarán - Consultas Requerido');
        }

        // con esto cambiamos de empresa si la empresa no coincide
        $collection = session('empresas_usuario');
        if ($collection->search($albarane->empresa_id, true) === false) {
            return abort(404, "No se ha encontrado el registro");
        }

        if ($albarane->empresa_id != session('empresa_id')) {
            $parametros = $this->loadSession($albarane->empresa_id);
        } else {
            $parametros = false;
        }

        $this->authorize('update', $albarane);

        if (request()->wantsJson()) {
            return [
                'parametros' => $parametros,
                'albaran'    => $albarane->load(['cliente', 'tipo', 'fase', 'motivo', 'fpago', 'cuenta', 'procedencia']),
                'cuentas'    => Cuenta::selCuentas(),
                'fpagos'     => Fpago::selFpagos(),
                'talleres'   => Taller::selTalleres(),
                'empresas'   => Empresa::selEmpresas()->Venta()->get(),
                'albalin'    => Albalin::withTrashed()->with(['producto'])->where('albaran_id', $albarane->id)->get(),
                'cobroslin'  => Cobro::withTrashed()->with(['fpago'])->where('albaran_id', $albarane->id)->get(),
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
    public function update(UpdateAlbaranRequest $request, Albaran $albarane)
    {

        // $this->authorize('update', $cliente);
        $data = $request->validated();

        if (getEjercicio($data['fecha_albaran']) != getEjercicio($albarane->fecha_albaran)) {
            return abort(403, "Ejercicio difiere de contador de albarán");
        }

        $data['username'] = $request->user()->username;

        $this->createHis($albarane, 'U');

        $albarane->update($data);

        if (request()->wantsJson()) {
            return [
                'albaran' => $albarane->load(['cliente', 'tipo', 'fase', 'motivo', 'fpago', 'cuenta', 'procedencia']),
                'message' => 'EL cliente ha sido modificado',
            ];
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Albaran $albarane)
    {

        $this->authorize('delete', $albarane);

        $albarane->forceDelete();

        $contador = Contador::restaContadorAlbaran(getEjercicio($albarane->fecha_venta), $albarane->albaran, $albarane->tipo_id);

        if (request()->wantsJson()) {
            return $contador;
        }
    }

    /**
     * Factura Manual
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function facturar(Request $request, Albaran $albarane)
    {

        if (session('empresa')->getFlag(6)) {
            return abort(403, 'Esta empresa no permite facturar albaranes!');
        }

        //  if (session('empresa')->id != session('empresa')->deposito_empresa_id)
        if ($albarane->tipo_id == 3) {
            if ($this->verificarSiHayProductosEnDeposito($albarane)) {
                return abort(411, 'Hay productos en depósito, no se puede facturar, reubicar albarán');

            }
        }

        $this->createHis($albarane, 'U');

        $ejercicio = getEjercicio(Carbon::today());

        $contador = Contador::incrementaFactura($ejercicio, $albarane->tipo_id, 1);

        $data = [
            'fecha_factura' => Carbon::today(),
            'factura'       => $contador['ult_factura'],
            'tipo_factura'  => 1,
            'serie_factura' => $contador['serie_factura'],
            'factura_txt'   => $ejercicio . $contador['serie_factura'] . $contador['ult_factura'],
            'username'      => $request->user()->username,
        ];

        $albarane->update($data);

        //Albaran::with(\['cliente','tipo','fase','motivo','fpago','cuenta','procedencia'\])->findOrFail($albarane->id);

        if (request()->wantsJson()) {
            return [
                'albaran' => $albarane->load(['cliente', 'tipo', 'fase', 'motivo', 'fpago', 'cuenta', 'procedencia']),
                'message' => 'EL cliente ha sido modificado',
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
    public function desfacturar(Request $request, Albaran $albarane)
    {

        // $this->authorize('update', $cliente);
        $this->createHis($albarane, 'U');

        $ejercicio = getEjercicio($albarane->fecha_factura);

        $contador = Contador::restaContadorFactura(
            $ejercicio,
            $albarane->tipo_id,
            $albarane->tipo_factura,
            $albarane->factura);

        $data = [
            'factura'            => null,
            'tipo_factura'       => 0,
            'fecha_factura'      => null,
            'serie_factura'      => null,
            'factura_txt'        => null,
            'fecha_notificacion' => null,
            'username'           => $request->user()->username,
        ];

        $albarane->update($data);

        if (request()->wantsJson()) {
            return [
                'albaran'  => $albarane->load(['cliente', 'tipo', 'fase', 'motivo', 'fpago', 'cuenta', 'procedencia']),
                'contador' => $contador,
            ];
        }

    }

    private function verificarSiHayProductosEnDeposito($albaran)
    {

        $albaran->load('albalins.producto');

        foreach ($albaran->albalins as $row) {

            //if ($row->producto->destino_empresa_id != $row->producto->empresa_id || $row->producto->cliente_id > 0){
            if ($row->producto->empresa_id != $row->empresa_id) {
                return true;
                break;
            }

        }

        return false;

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function facauto(Request $request, Albaran $albarane)
    {

        if (session('empresa')->getFlag(6)) {
            return abort(403, 'Esta empresa no permite facturar albaranes!');
        }

        // $this->authorize('update', $cliente);
        //if (session('empresa')->id != session('empresa')->deposito_empresa_id)
        if ($albarane->tipo_id == 3) {
            if ($this->verificarSiHayProductosEnDeposito($albarane)) {
                return abort(411, 'Hay productos en depósito, no se puede facturar, reubicar albarán');
            }
        }

        $data = $request->validate([
            'fecha_factura' => ['required', 'date'],
            'factura'       => ['required', 'integer'],
            'tipo_factura'  => ['required', 'integer'],
        ]);

        $ejercicio = getEjercicio($data['fecha_factura']);

        $contador = Contador::incrementaFactura(
            $ejercicio,
            $albarane->tipo_id,
            $data['tipo_factura'],
            $data['factura']);

        $data_upd = [
            'fecha_factura' => $data['fecha_factura'],
            'factura'       => $contador['ult_factura'],
            'tipo_factura'  => $data['tipo_factura'],
            'serie_factura' => $contador['serie_factura'],
            'factura_txt'   => $ejercicio . $contador['serie_factura'] . $contador['ult_factura'],
            'username'      => $request->user()->username,
        ];

        $this->createHis($albarane, 'U');

        try {
            $albarane->update($data_upd);
        } catch (\Exception $e) {
            if ($data['factura'] == 0) {
                Contador::restaContadorFactura(
                    $ejercicio,
                    $albarane->tipo_id,
                    $data['tipo_factura'],
                    $data['factura']);
            }

            return abort(411, 'Factura duplicada, revisar contadores! ');
        }

        if (request()->wantsJson()) {
            return [
                'albaran' => $albarane->load(['cliente', 'tipo', 'fase', 'motivo', 'fpago', 'cuenta', 'procedencia']),
                'message' => 'EL albarán ha sido modificado',
            ];
        }

    }

    public function fase(Request $request, Albaran $albarane)
    {
        $data = $request->validate([
            'fase_id' => ['integer', 'required'],
        ]);

        $data['username'] = $request->user()->username;

        $this->createHis($albarane, 'U');

        $albarane->update($data);

        if (request()->wantsJson()) {
            return [
                'message' => 'EL registro ha sido modificado',
            ];
        }

    }

    /**
     * Factura Manual
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function actfac(Request $request, Albaran $albarane)
    // {

    //     $data = [
    //         'facturar' => !$albarane->facturar,
    //         'username' => $request->user()->username,
    //     ];

    //     $this->createHis($albarane, 'U');

    //     $albarane->update($data);

    //     if (request()->wantsJson()) {
    //         return [
    //             'albaran' => $albarane->load(['cliente', 'tipo', 'fase', 'motivo', 'fpago', 'cuenta', 'procedencia']),
    //             'message' => 'EL albarán ha sido modificado',
    //         ];
    //     }

    // }

    /**
     * Recibe las facturas por request, previamente de $this->lisfac()
     *
     * @param Request $request
     * @return void
     */
    public function excel(Request $request)
    {

        return Excel::download(new AlbaranesExport($request->data), 'albaranes.xlsx');

    }

    private function createHis($albaran, $operacion)
    {

        $data                 = $albaran->toArray();
        $data['id']           = null;
        $data['albaran_id']   = $albaran->id;
        $data['operacion']    = $operacion;
        $data['username_his'] = session('username');
        $data['created_his']  = Carbon::now();

        Halbaran::create($data);

    }

}
