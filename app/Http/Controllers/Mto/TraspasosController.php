<?php

namespace App\Http\Controllers\Mto;

use App\Caja;
use App\Empresa;
use App\Traspaso;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TraspasosController extends Controller
{
    public function index()
    {

        if (request()->session()->has('filtro_tra')){
            $data = $this->miFiltro();
        }else{
            $data = Traspaso::with(['empresa','proveedora'])
                        ->where('situacion_id','<=',2)
                        ->Where(function($query) {
                            $query->whereIn('empresa_id', session('empresas_usuario'))
                                  ->orWhereIn('proveedora_empresa_id', session('empresas_usuario'));
                        })
                        ->orderBy('fecha')
                        ->get()
                        ->take(99);
        }

        if (request()->wantsJson())
            return $data;
    }

    public function filtrar(Request $request)
    {


        $data = $request->validate([
            'situacion_id'=>['required','integer'],
        ]);

        session(['filtro_tra' => $data]);

        return $this->miFiltro();

    }

    private function miFiltro(){
        $data = request()->session()->get('filtro_tra');

        return Traspaso::with(['empresa','proveedora'])
                        ->where('situacion_id', $data['situacion_id'])
                        ->Where(function($query) {
                            $query->whereIn('empresa_id', session('empresas_usuario'))
                                  ->orWhereIn('proveedora_empresa_id', session('empresas_usuario'));
                        })
                        ->get();

    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', new Traspaso);

        if (request()->wantsJson())
            return [
                'empresas' => Empresa::selEmpresas()->proveedora()->get()
            ];

    }

     /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', new Traspaso);

        $data = $request->validate([
            'fecha'                  => ['required', 'date'],
            'fecha'                  => ['required', 'date'],
            'importe_solicitado'     => ['required', 'numeric'],
            'proveedora_empresa_id'  => ['required', 'integer'],
        ]);

        if ($data['proveedora_empresa_id'] == session('empresa')->id){
            return abort(404, 'Empresa provedora y destinataria son iguales');
        }

        $data['empresa_id'] = session('empresa')->id;
        $data['username'] = $request->user()->username;
        $data['solicitante_user_id'] = $request->user()->id;

        $reg = Traspaso::create($data);

        if (request()->wantsJson())
            return ['traspaso'=>$reg, 'message' => 'EL registro ha sido creado'];
    }

      /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Traspaso $traspaso)
    {
        //dd(session('empresas_usuario')->toArray());

        if (!in_array($traspaso->empresa_id, session('empresas_usuario')->toArray()) &&
            !in_array($traspaso->proveedora_empresa_id, session('empresas_usuario')->toArray())
            )
            abort(404, "No autorizado!");

        $traspaso = $traspaso->load(['solicitante','autorizado','receptor']);

        if (request()->wantsJson())
            return [
                'traspaso' => $traspaso,
                'saldo'     =>getCurrency(Caja::saldoByEmpresa($traspaso->proveedora_empresa_id,$traspaso->fecha)),
                'empresas' => Empresa::selEmpresas()->get()
            ];
    }

      /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Traspaso $traspaso)
    {

        $this->authorize('update', $traspaso);

        $data = $request->validate([
            'importe_solicitado'    => ['required','numeric'],
            'importe_entregado'     => ['required','numeric'],
            'fecha'                 => ['required','date'],
            'proveedora_empresa_id' => ['required', 'integer'],
        ]);

        if ($traspaso->situacion_id == 1 && $traspaso->importe_entregado == 0 && $data['importe_entregado'] <> 0){
                // autoriza
            $data['autorizado_user_id'] = $request->user()->id;
            $data['situacion_id'] = 2;
        }

        if ($traspaso->situacion_id == 2){
            if ($data['importe_entregado'] <> 0){
                // confirma la entrega
                $data['receptor_user_id'] = $request->user()->id;
                $data['situacion_id'] = 3;
            }else{
                // deshace la operación, será un supervisor.
                $data['autorizado_user_id'] = 0;
                $data['receptor_user_id']   = 0;
                $data['situacion_id']       = 1;
            }
        }

        if ($traspaso->situacion_id == 3){
            return abort(411, 'Operación ya autorizada!');
        }

        $data['username'] = $request->user()->username;


        $traspaso->update($data);

        $traspaso = Traspaso::with(['solicitante','autorizado','receptor'])->findOrfail($traspaso->id);

        if ($traspaso->situacion_id == 3){
            $this->crearApunteCaja($traspaso);
        }

        if (request()->wantsJson())
            return [
                'traspaso'=>$traspaso,
                'saldo'     =>getCurrency(Caja::saldoByEmpresa($traspaso->proveedora_empresa_id,$traspaso->fecha)),
                'message' => 'EL registro ha sido modificado'
            ];
    }

     /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Traspaso $traspaso)
    {
        $this->authorize('delete', $traspaso);

        $traspaso->delete();

        if (request()->wantsJson()){
            return Traspaso::with(['empresa','proveedora'])
            ->orderBy('fecha')
            ->get()
            ->take(100);
        }
    }

    private function crearApunteCaja($traspaso){

        $empresa_proveedora = Empresa::findOrfail($traspaso->proveedora_empresa_id);

        $data = [
            'empresa_id' => $traspaso->proveedora_empresa_id,
            'fecha'      => $traspaso->fecha,
            'dh'         => 'D',
            'nombre'     => "TRASPASO DE FONDOS A ".session('empresa')->nombre,
            'importe'    => $traspaso->importe_entregado,
            'manual'     => 'N',
            'username'   => $traspaso->username
        ];

        Caja::create($data);

        $data['empresa_id'] = $traspaso->empresa_id;
        $data['dh']='H';
        $data['nombre'] = "TRASPASO DE FONDOS de ".$empresa_proveedora->nombre;

        Caja::create($data);

    }



}
