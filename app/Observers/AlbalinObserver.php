<?php

namespace App\Observers;

use App\Cobro;
use App\Albalin;
use App\Albaran;
use App\Halbalin;
use Carbon\Carbon;
use App\Traits\EstadoProductoTrait;

class AlbalinObserver
{

    use EstadoProductoTrait;

    /**
     * Handle the albalin "created" event.
     *
     * @param  \App\Albalin  $albalin
     * @return void
     */
    public function created(Albalin $albalin)
    {

        $this->setEstadoProducto($albalin->producto_id, 3);

        $this->updateFaseAlbaran($albalin->albaran_id);

    }

    /**
     * Handle the albalin "updated" event.
     *
     * @param  \App\Albalin  $albalin
     * @return void
     */
    public function updated(Albalin $albalin)
    {
        $this->updateFaseAlbaran($albalin->albaran_id);
    }

    /**
     * Handle the albalin "deleted" event.
     *
     * @param  \App\Albalin  $albalin
     * @return void
     */
    public function deleted(Albalin $albalin)
    {

        $data                 = $albalin->toArray();
        $data['id']           = null;
        $data['albalin_id']   = $albalin->id;
        $data['operacion']    = 'D';
        $data['username_his'] = session('username');
        $data['created_his']  = Carbon::now();

        Halbalin::create($data);

        $this->setEstadoProducto($albalin->producto_id, 2);

        // $data=[
        //     'estado_id'=> 2,
        //     'username' => session('username')
        // ];

        // Producto::where('id', $albalin->producto_id)
        //         ->where('estado_id','<>', 5) // no tocamos los genéricos
        //         ->where('stock', 1)
        //         ->update($data);

        $this->updateFaseAlbaran($albalin->albaran_id);
    }

    /**
     * Cambia fase en función de cobros y lineas de albarán
     * En Observer CobroObserver y AlbalinObserver
     *
     * @param integer $albaran_id
     * @return void
     */
    private function updateFaseAlbaran($albaran_id)
    {

        try {

            $albaran = Albaran::findOrFail($albaran_id);

            if ($albaran->fase_id == 13) {
                return;
            }

            $totales        = Albalin::totalAlbaranByAlb($albaran_id);
            $cobros_acuenta = Cobro::getAcuentaByAlbaran($albaran_id);

            $fase_id = ($cobros_acuenta >= $totales['total'] && $totales['total'] != 0) ? 11 : 10;

            $albaran->update(['fase_id' => $fase_id,
                'online'                    => false,
                'username'                  => session('username')]);

        } catch (\Exception $e) {

        }

        // Albaran::where('id',$albaran_id)
        //         ->where('fase_id','<=',11)
        //         ->update(['fase_id'=>$fase_id,'username'=>session('username')]);

    }

}
