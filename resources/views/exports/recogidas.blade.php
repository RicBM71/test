<table>
    <thead>
        <tr>
            <th colspan="6">RECOGIDAS</th>
        </tr>
        <tr>
            <th>EMPRESA</th>
            <th>COMPRA</th>
            <th>IMPORTE</th>
            <th>CLIENTE</th>
            <th>F. COMPRA</th>
            <th>F. RECOGIDA</th>
            <th>Fase</th>
            <th>Clase</th>
        </tr>
    </thead>
    <tbody>
    @foreach($data as $item)
        <tr>
            <td>{{ $item->empresa->nombre}}</td>
            <td>{{ $item->serie_com.' '.$item->albaran.'-'.substr(getEjercicio($item->fecha_compra), -2)}}</td>
            <td>{{ $item->importe}}</td>
            <td>{{ $item->cliente->razon}}</td>
            <td>{{ getFecha($item->fecha_compra)}}</td>
            <td>{{ getFecha($item->fecha_recogida)}}</td>
            <td>{{ $item->fase->nombre}}</td>
            <td>{{ $item->allcomlines->pluck('clase')->pluck('nombre')->unique()->implode(', ')}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
