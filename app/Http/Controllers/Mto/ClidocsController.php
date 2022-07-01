<?php

namespace App\Http\Controllers\Mto;

use App\Clidoc;
use App\Cliente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Requests\StoreClidocs;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class ClidocsController extends Controller
{

    public function create($cliente_id, $compra_id=0){

        $cliente = Cliente::findOrFail($cliente_id);
        $validez = $cliente->fecha_dni;

        if (request()->wantsJson())
            return [
                'cliente'   => $cliente,
                'compra_id' => $compra_id,
                'validez'   => $validez
            ];
    }


    public function store(StoreClidocs $request)
    {

        $data = $request->validated();


        $data['username'] = $request->user()->username;

        $y = Carbon::parse($data['validez'])->format('Y');
        $m = Carbon::parse($data['validez'])->format('m');
        $d = Carbon::parse($data['validez'])->format('d');

        $disco = session('parametros')->carpeta_docs."/";

        $subcarpeta = $disco."data".intdiv($data['cliente_id']+1000,1000) * 1000;

        $subcarpeta = $disco.'/eje'.$y.'/'.$m;

        // $data['file1']=$subcarpeta.'/'.$data['cliente_id'].'A.jpg';

        // $img = str_replace('data:image/jpeg;base64,','',$data['img1']);
        // Storage::disk('docs')->put($data['file1'], base64_decode($img));

        // if(!is_null($data['img2'])){
        //     $img = str_replace('data:image/jpeg;base64,','',$data['img2']);
        //     $data['file2']=$subcarpeta.'/'.$data['cliente_id'].'R.jpg';
        //     Storage::disk('docs')->put($data['file2'], base64_decode($img));

        // }

        $path1 = $subcarpeta.'/'.$data['cliente_id'].'A.dat';
        $path2 = $subcarpeta.'/'.$data['cliente_id'].'R.dat';

        $this->crearImagen($data['img1'], $path1);
        $data['file1']=$path1;

        if(!is_null($data['img2'])){
            $this->crearImagen($data['img2'], $path2);
            $data['file2']=$path2;
        }

        $reg = Clidoc::create($data);

        if (request()->wantsJson())
            return ['clidoc'=>$reg, 'message' => 'EL registro ha sido creado'];
    }

    public function crearImagen($dataUrl,$file){

		if (is_null($dataUrl)) return null;

		$dataUrlParts = explode( ",", $dataUrl);

		$imgdata =  $this->setEncryptImg($dataUrlParts[1]);

        Storage::disk('docs')->put($file, $imgdata);

		return;

    }

    // lo desactivo de momento
	public function setEncryptImg($img){

		if (is_null($img)) return null;

		//return $img;

		return Crypt::encryptString($img);
	}



     /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Clidoc $clidoc)
    {
        $clidoc->delete();

        if (request()->wantsJson()){
            return [
                'message' =>  'documentación borrada',
            ];
        }
    }

    public function renove(Clidoc $clidoc)
    {

        $t = '.'.date('ymdhis').'.ren';

        $file1 = \str_replace('.dat',$t,$clidoc->file1);
        $file2 = \str_replace('.dat',$t,$clidoc->file2);

        try {
            Storage::disk('docs')->move($clidoc->file1, $file1);
            //code...
        } catch (\Exception $e) {
            //throw $th;
        }
        try {
            Storage::disk('docs')->move($clidoc->file2, $file2);
            //code...
        } catch (\Exception $e) {
            //throw $th;
        }


        $clidoc->delete();

        if (request()->wantsJson()){
            return [
                'message' =>  'documentación renovada',
            ];
        }

    }

}
