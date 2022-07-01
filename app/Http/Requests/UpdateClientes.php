<?php

namespace App\Http\Requests;

use App\Rules\FechaDni;
use App\Rules\MaxFechaDni;
use App\Rules\ValidarDniCif;
use Illuminate\Validation\Rule;
use App\Rules\FechaNacimientoRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClientes extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $data = [

            'nombre'            => ['required', 'string', 'max:30'],
            'apellidos'         => ['required', 'string', 'max:45'],
            'razon'             => ['nullable','string', 'max:70'],
            'direccion'         => ['nullable','string', 'max:50'],
            'cpostal'           => ['nullable','string', 'max:10'],
            'poblacion'         => ['nullable','string', 'max:40'],
            'provincia'         => ['nullable','string', 'max:30'],
            'telefono1'         => ['nullable','string', 'max:15'],
            'telefono2'         => ['nullable','string', 'max:15'],
            'tfmovil'           => ['nullable','string', 'max:15'],
            'email'             => ['nullable','email', 'max:50'],
            'tipodoc'           => ['string'],
            'dni'               => ['required', new ValidarDniCif($this->tipodoc),Rule::unique('clientes')->ignore($this->route('cliente')->id)->where(function ($query) {
                return $query->where('empresa_id', session()->get('empresa')->comun_empresa_id);
            })],
            'fecha_nacimiento'  => ['nullable','date', new FechaNacimientoRule()],
            'fecha_baja'        => ['nullable','date'],
            'nacpro'            => ['nullable','string', 'max:30'],
            'nacpais'            => ['nullable','string', 'max:40'],
            'fecha_dni'         => ['nullable','date', new FechaDni(), new MaxFechaDni()],
            'notas'             => ['nullable','string'],
            'bloqueado'         => ['string'],
            'iva_no_residente'        => ['boolean'],
            'facturar'          => ['boolean'],
            'vip'               => ['boolean'],
            'listar_347'        => ['boolean'],
            'asociado'          => ['boolean'],
            'iban'              => ['nullable','iban', 'max:50'],
            'bic'               => ['nullable','bic', 'max:11'],
            'fpago_id'          => ['integer'],
            'interes'           => ['numeric','required'],
            'interes_recuperacion' => ['numeric','required'],
            'descuento'         => ['numeric','required'],
            'notificar_iban'    => ['boolean'],
            'ecommerce_id'      => ['nullable','integer']
        ];
        // Rule::unique('clientes')
        //     ->ignore($this->route('cliente')->id)->where(function ($query) {return $query->where('empresa_id', session()->get('empresa')->id; })];

        //  if ($this->filled('cif'))
        //     $data['cif'] = ['required',   Rule::unique('clientes')->ignore($this->route('cliente')->id)->where(function ($query) {
        //         return $query->where('empresa_id', xx);
        //    })];



        return $data;
    }
}
