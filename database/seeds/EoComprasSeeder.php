<?php

use App\Libro;
use App\Compra;
use App\Empresa;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EoComprasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $this->restos();
        return;

        $empresa = array(1,9,12,16);
        $e='1,9,12,16';



        //insert into contadores SELECT DISTINCT 0,empresa,tienda,ejercicio, '1', 'S', '1', '1', '1', 'A', '1', '1', '1', 'sys','2019-01-01','00:00:00' from albaranes where ejercicio <= 2010

        Compra::truncate();

        $empresa_ant = $tienda_ant = -1;
        $ejercicio = 2019;
        $contadores = DB::connection('evaoro')->select('select * from contadores WHERE compras > 1 ORDER BY id');
        foreach ($contadores as $contador){

            if ($contador->tienda == 3)
                $empresa_id = 7;
            else
                $empresa_id = $contador->empresa;

                session(['empresa' => Empresa::find($empresa_id)]);

                $libro = Libro::find($contador->id);

            //     $empresa_ant = $contador->empresa;
            //     $tienda_ant = $contador->tienda;
            // }

           // if (in_array($contador->empresa, $empresa)) // comentar if para cargar todas las empresas
            $this->crearCompras($contador->empresa,$contador->tienda,$libro,$contador->ejercicio);

        }

        // $fase_incorrecta = DB::select('SELECT id FROM klt_compras where tipo_id = 2 and fase_id = 4 and id in (select compra_id from klt_comlines where fecha_liquidado is not null)');
        // foreach ($parcial_liquidados as $c){

        //     DB::update('UPDATE klt_compras SET fase_id = 7 WHERE id='.$c->id);

        // }

        // $parcial_liquidados = DB::select('SELECT id FROM klt_compras where fase_id = 4 and id in (select compra_id from klt_comlines where fecha_liquidado IS NOT NULL)');
        // foreach ($parcial_liquidados as $c){

        //     DB::update('UPDATE klt_compras SET fase_id = 6 WHERE id='.$c->id);

        // }




    }

    private function restos(){

        session(['empresa' => Empresa::find(1)]);

        Compra::truncate();
        $libro = Libro::find(2);

        $this->crearCompras(1,1,$libro,2009);
    }

    private function crearCompras($empresa,$tienda,$libro,$eje){

        //$bloqueo = "1/3"; // "1/1
        //$eje = 2000;

        $data=array();

        // $reg = DB::connection('evaoro')->select('select * from albaranes where empresa='.$empresa.
        //                 ' and tienda = '.$tienda.
        //                 ' and comven="C" and year(fechacomp) = '.$eje.' order by empresa,tienda,id');

        $reg = DB::connection('evaoro')->select('select * from albaranes where id in(31,62,95,99)');


        $i=0;
        foreach ($reg as $row){
            $i++;

            // if ($row->id == 16131){
            //     \Log::info($row);
            // }

            $grupo_id = $libro->grupo_id;
            $serie_com = $libro->serie_com;
            $empresa_id = $libro->empresa_id;


            // if(in_array($row->empresa, [12,13,14,15,16])){

            // }
            // CREATE TEMPORARY TABLE xx
            // update klt_compras set fase_id = 6 where id in (select id from xx)

            if (strlen($row->notas) >= 191)
                $row->notas = substr($row->notas,0,190);

            $data[]=array(
                'id'        => $row->id,
                'empresa_id' => $empresa_id,
                'grupo_id'=> $grupo_id,
                'dias_custodia' => 30,
                'ejercicio' => $row->ejercicio,
                'serie_com' => $serie_com,
                'albaran' => $row->albaran,
                'cliente_id'=> $row->cliente,
                'tipo_id' => $row->tipo,
                'fecha_compra' => $row->fechacomp,
                'fecha_bloqueo'=> $this->Bloqueo($row->fechacomp, $libro->semdia_bloqueo),
                'fecha_renovacion' => $row->fecharecu,
                'fecha_recogida' => $row->fecharecogida=="0000-00-00" ? null : $row->fecharecogida,
                'importe' => $row->importe,
                'importe_renovacion' => $row->impren,
                'importe_acuenta' => $row->impacuenta,
                'interes' => $row->interes,
                'fase_id' => $row->estado==6 ? 7: $row->estado,
                'factura' => $row->factura == 0 ? null : $row->factura,
                'fecha_factura' => $row->fechafac=="0000-00-00" ? null : $row->fechafac,
                'serie_fac' => $row->serie,
                'papeleta' => $row->papeleta==0 ? null : $row->papeleta,
                'notas' => $row->notas,
                'username' => $row->sysusr,
                'created_at' => $row->sysfum.' '.$row->syshum,
                'updated_at' => $row->sysfum.' '.$row->syshum,
            );

            if ($i % 1000 == 0){
                DB::table('compras')->insert($data);
                $data=array();
            }
        }

        DB::table('compras')->insert($data);
    }

    public function Bloqueo($fecha, $semdia){



        $s = explode("/", $semdia);
        // $fecha = date('Y-m-d', strtotime($fecha));
        //$f = Carbon::createFromFormat('Y-m-d h:i:s', $fecha);
        $f = Carbon::parse($fecha);

        $f->addWeek($s[0]);

        return $f->subDays($f->dayOfWeek - $s[1]);

    }

}
