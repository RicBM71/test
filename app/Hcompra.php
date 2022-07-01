<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hcompra extends Model
{

    protected $fillable = [
        'empresa_id', 'grupo_id', 'dias_custodia', 'ejercicio', 'serie_com', 'albaran', 'cliente_id', 'tipo_id',
        'fecha_compra', 'fecha_bloqueo', 'fecha_renovacion', 'fecha_recogida', 'importe',
        'importe_renovacion', 'importe_acuenta', 'interes', 'fase_id', 'factura', 'fecha_factura',
        'serie_fac', 'papeleta', 'notas', 'username', 'retencion', 'interes_recuperacion', 'importe_recuperacion', 'almacen_id',
        'id', 'operacion', 'username_his', 'created_his', 'compra_id', 'created_at', 'updated_at',
    ];

    protected $appends = ['alb_ser'];

    public function getAlbSerAttribute()
    {

        return $this->serie_com . " " . $this->albaran . '-' . substr($this->ejercicio, -2);

    }

    public function grupo()
    {
        return ($this->belongsTo(Grupo::class));
    }

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

    public function hcomlines()
    {
        return $this->hasMany(Hcomline::class, 'compra_id', 'compra_id');
    }

    public function hdepositos()
    {
        return $this->hasMany(Hdeposito::class);
    }

    public static function getCambios($compra_id)
    {

        return Hcompra::where('compra_id', $compra_id)
            ->where('operacion', 'I')
            ->get()
            ->count();
    }

    public static function scopeFecha($query, $d, $h, $tipo = 'C')
    {

        if ($d == '') {
            $d = date('Y-m-') . '01';
        }

        if ($h == '') {
            $h = date('Y-m-d');
        }

        if ($tipo == "C") {
            return $query->whereDate('fecha_compra', '>=', $d)
                ->whereDate('fecha_compra', '<=', $h);
        } elseif ($tipo == "R") {
            return $query->whereDate('fecha_recogida', '>=', $d)
                ->whereDate('fecha_recogida', '<=', $h);
        } elseif ($tipo == "F") {
            return $query->whereDate('fecha_factura', '>=', $d)
                ->whereDate('fecha_factura', '<=', $h);
        } elseif ($tipo == "N") {
            return $query->whereDate('fecha_renovacion', '>=', $d)
                ->whereDate('fecha_renovacion', '<=', $h);
        } else {
            return $query->whereDate('updated_at', '>=', $d)
                ->whereDate('updated_at', '<=', $h);
        }

    }

    public static function scopeNumero($query, $albaran)
    {
        if ($albaran == '') {
            return $query;
        }

        $albaran = str_replace('.', '-', $albaran);

        if (strstr($albaran, '-') === false) {
            $ejercicio = date('Y');
        } else {
            $arr     = preg_split('/-/', $albaran);
            $albaran = $arr[0];
            if ($arr[1] > 0) {
                $ejercicio = '20' . $arr[1];
            } else {
                $ejercicio = date('Y');
            }

        }

        return $query->where('ejercicio', $ejercicio)
            ->where('albaran', $albaran);

    }

}
