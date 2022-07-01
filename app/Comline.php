<?php

namespace App;

use App\Scopes\EmpresaScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Comline extends Model

{

    protected $dates =['fecha_liquidado'];

    protected $fillable = [
        'compra_id','empresa_id','concepto','grabaciones','colores','clase_id','peso_gr','importe',
        'importe_venta','iva', 'importe_gr','quilates','fecha_liquidado','username','retencion'
    ];

    protected $casts = [
        'fecha_liquidado' => 'date:Y-m-d',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new EmpresaScope);

        // static::created(function($comline){

        //     $comline->setTotalesCompra($comline);

        // });

        // static::updated(function($comline){

        //     $comline->setTotalesCompra($comline);

        // });

        // static::deleted(function($comline){

        //     $comline->setTotalesCompra($comline);

        // });
    }

    public function setTotalesCompra($comline){

        $total = $comline->totalCompra($comline->compra_id);

        try {

            $compra = Compra::with('cliente')->findOrfail($comline->compra_id);

            $libro = Libro::where('ejercicio', getEjercicio($compra->fecha_compra))
                            ->where('grupo_id', $compra->grupo_id)
                            ->firstOrFail();

            if (is_null($total->importe)){
                $total->importe = 0;
            }

            // Gestiona el tramo de corte para aplicar %min de interes a partir de un valor mínimo de compra.
            if ($libro->tramo > 0){
                if ($total->importe <= $libro->tramo){
                    $compra->interes = $libro->interes_min;
                    $compra->interes_recuperacion = $libro->interes_min;
                    $data['interes'] = $compra->interes;
                    $data['interes_recuperacion'] = $compra->interes_recuperacion;
                }else{

                    $compra->interes = $compra->cliente->interes > 0 ? $compra->cliente->interes : $libro->interes;
                    $compra->interes_recuperacion = $compra->cliente->interes_recuperacion > 0 ? $compra->cliente->interes : $libro->interes_recuperacion;

                    $data['interes'] = $compra->interes;
                    $data['interes_recuperacion'] = $compra->interes_recuperacion;
                }
            }

            $data['username'] = session('username');
            $data['importe'] = $total->importe;
            $data['importe_renovacion'] = round($total->importe * $compra->interes / 100, 0);
            $data['importe_recuperacion'] = round($total->importe * $compra->interes_recuperacion / 100, 0);

            $compra->update($data);

        } catch (\Exception $th) {

        }


    }

    public function compra(){
        return $this->belongsTo(Compra::class);
    }

    public function clase()
    {
    	return $this->belongsTo(Clase::class);
    }


    public function setConceptoAttribute($concepto)
    {
        $this->attributes['concepto'] = strtoupper($concepto);

    }

    public function setGrabacionesAttribute($grabaciones)
    {
        $this->attributes['grabaciones'] = strtoupper($grabaciones);

    }

    public function setColoresAttribute($colores)
    {
        $this->attributes['colores'] = strtoupper($colores);

    }
    public function setQuilatesAttribute($quilates)
    {
        $this->attributes['quilates'] = strtoupper($quilates);

    }

    // public function getQuilatesK()
    // {
    //     if ($this->attributes['quilates'] != null)
    //         return $this->attributes['quilates'].'K';
    //     else
    //         return null;
    // }



    public function scopeCompraId($query, $compra_id)
    {
        // esto permite relacionar retencion e iva en el objeto producto, aquí no hace falta pero lo usaremos.
        //$query->with(['producto','producto.retencion','producto.iva'])
        $query->with(['clase'])
              ->where('compra_id', '=', $compra_id );

    }

    // public function scopeQuilates($query, $quilates)
    // {

    //     if ($quilates > 0)
    //         $query->where('quilates', '=', $quilates);

    // }

    public static function totalCompra($id){
        return DB::table('comlines')
                ->select(DB::raw('SUM(peso_gr) AS peso_gr, ROUND(SUM(importe), 2) AS importe'))
                ->where('compra_id', $id)
                ->first();
    }

    public static function  setLiquidado($id, $data){
        DB::table('comlines')
            ->where('id', $id)
            ->update($data);
    }

    public static function scopeLiquidados($query, $fecha){
        $query->whereDate('fecha_liquidado',$fecha);
    }

    public static function scopeClase($query, $clase_id){
        $query->where('clase_id',$clase_id);
    }
}

