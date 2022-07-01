<?php

namespace App\Http\Controllers\Utilidades;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Jobs\CalcularExistenciaJob;
use App\Http\Controllers\Controller;
use App\Traits\CalcularExistenciasTrait;

class CalcularExistenciasController extends Controller
{

    use CalcularExistenciasTrait;

    public function submit(){

        $dt = Carbon::today();

        dispatch(new CalcularExistenciaJob());



        //$this->valorDepositos($dt);

        //$this->valorInventario($dt);

    }



}
