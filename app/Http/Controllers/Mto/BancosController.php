<?php

namespace App\Http\Controllers\Mto;

use App\Banco;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BancosController extends Controller
{
/**
 * Display a listing of the resource.
 *
 * @return \Illuminate\Http\Response
 */
    public function index()
    {

        $data = Banco::all();

        if (request()->wantsJson()) {
            return $data;
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //$this->authorize('create', new Almacen);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->authorize('create', Banco::class);

        $data = $request->validate([
            'nombre'  => ['required', 'string', 'max:50'],
            'entidad' => ['required', 'string', 'max:4'],
        ]);

        $reg = Banco::create($data);

        if (request()->wantsJson()) {
            return ['banco' => $reg, 'message' => 'EL registro ha sido creado'];
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id (id del archivo)
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Banco $banco)
    {
        $this->authorize('update', $banco);

        if (request()->wantsJson()) {
            return [
                'banco' => $banco,
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
    public function update(Request $request, Banco $banco)
    {
        $this->authorize('update', $banco);

        $data = $request->validate([
            'nombre'  => ['required', 'string', 'max:50'],
            'entidad' => ['required', 'string', 'max:4'],
            'bic'     => ['required', 'string', 'max:12'],
        ]);

        $banco->update($data);

        if (request()->wantsJson()) {
            return ['banco' => $banco, 'message' => 'EL registro ha sido modificado'];
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Banco $banco)
    {
        $this->authorize('delete', $banco);

        $banco->delete();

        if (request()->wantsJson()) {
            return response()->json(Banco::all());
        }
    }
}
