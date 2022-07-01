<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Halbaran extends Model
{
    protected $table = 'halbaranes';

    protected $dates   = ['fecha_albaran', 'fecha_factura', 'fecha_notificacion', 'created_his'];
    protected $appends = ['fac_ser', 'alb_ser', 'alb_sere', 'fac_sere'];

    protected $casts = [
        'fecha_albaran'      => 'date:Y-m-d',
        'fecha_factura'      => 'date:Y-m-d',
        'fecha_notificacion' => 'date:Y-m-d',
        'created_his'        => 'date:Y-m-d',
    ];

    protected $fillable = [
        'empresa_id', 'tipo_id', 'serie_albaran', 'albaran', 'fase_id',
        'cliente_id', 'fecha_albaran', 'fecha_factura', 'factura',
        'serie_factura', 'tipo_factura', 'clitxt', 'fecha_notificacion', 'online', 'iva_no_residente',
        'notas_int', 'motivo_id', 'factura_txt', 'albaran_abonado_id', 'username', 'cuenta_id', 'fpago_id', 'notas_ext',
        'procedencia_empresa_id', 'facturar', 'taller_id', 'express', 'pedido', 'validado', 'albaran_id', 'operacion', 'username_his', 'created_his',
    ];

    public function cliente()
    {
    	return ($this->belongsTo(Cliente::class));
    }

    public function tipo()
    {
    	return ($this->belongsTo(Tipo::class));
    }

    public function fase()
    {
    	return ($this->belongsTo(Fase::class));
    }

    public function fpago()
    {
    	return ($this->belongsTo(Fpago::class));
    }

    public function cuenta()
    {
    	return ($this->belongsTo(Cuenta::class));
    }

    public function motivo()
    {
    	return ($this->belongsTo(Motivo::class));
    }

    public function taller()
    {
    	return ($this->belongsTo(Taller::class));
    }

    public function procedencia()
    {
    	return ($this->belongsTo(Empresa::class,'procedencia_empresa_id'));
    }

    public function halbalins()
    {
        return $this->hasMany(Halbalins::class, 'albaran_id', 'albaran_id');
    }

}
