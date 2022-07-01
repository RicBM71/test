<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChangePathDocs extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $start = Carbon::now();

        $data = DB::table('clidocs')->select('clidocs.id','cliente_id','fecha_dni','file1', 'file2')
                    ->join('clientes','clientes.id','=','cliente_id')
                    //->where('cliente_id', '>', 100000)
                    ->orderBy('cliente_id')
                    ->get();

        $r = 0;
        foreach ($data as $clidoc) {

            if (str_contains($clidoc->file1, 'eje'))
                continue;

            $clidoc->fecha_dni = $this->validar_fecha($clidoc->fecha_dni);

            $y = Carbon::parse($clidoc->fecha_dni)->format('Y');
            $m = Carbon::parse($clidoc->fecha_dni)->format('m');


            $path1 = $clidoc->file1 > '' ? explode( '/', $clidoc->file1) : null;
            $path2 = $clidoc->file2 > '' ? explode( '/', $clidoc->file2) : null;

            $new_path1 = $path1 != null ? $path1[0]."/"."eje".$y."/".$m."/".$path1[2] : null;
            $new_path2 = $path2 != null ? $path2[0]."/"."eje".$y."/".$m."/".$path2[2] : null;

            try {
                Storage::disk('docs')->move($clidoc->file1, $new_path1);
            } catch (\Exception $th) {
                Log::warning("Error al mover F1; ID;".$clidoc->id."; CLIE;".$clidoc->cliente_id);
                $new_path1 = null;
            }

            try {
                Storage::disk('docs')->move($clidoc->file2, $new_path2);
            } catch (\Exception $th) {
                //Log::warning("Error al mover F2; ID;".$clidoc->id."; CLIE;".$clidoc->cliente_id);
                $new_path2 = null;
            }


            DB::table('clidocs')
                ->where('id', $clidoc->id)
                ->update([
                    'file1' => $new_path1,
                    'file2' => $new_path2
                ]);


            $r++;

            // if ($r % 200 == 0)
            //     break;

        }

        echo "ok ->".$r,"  => ".Carbon::now()->diffForHumans($start);

    }

    private function validar_fecha($value){

        $f = Carbon::parse($value);

        if ($f->format('Y') == 9999) return $value;

        $dt = Carbon::now();


        return ($f->diffInYears($dt) > 10) ? '2031-'.$f->format('m').'-'.$f->format('d') : $value;

    }
}
