<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Halbalin extends Model
{
    protected $table = 'halbalins';

    protected $fillable = [
        'albaran_id', 'empresa_id', 'producto_id', 'unidades', 'importe_unidad',
        'precio_coste', 'importe_venta', 'iva_id', 'iva', 'username', 'notas', 'descuento',
        'albalin_id', 'operacion', 'username_his', 'created_his',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

}
