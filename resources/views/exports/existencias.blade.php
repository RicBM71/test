<table>
    <thead>
    <tr>
        <th>Fecha</th>
        <th>Concepto</th>
        <th>Importe</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $item)
        <tr>
            <td>{{ getFecha($item['fecha']) }}</td>

            <td>{{ $nombres[$item['detalle_id'] - 1]}}</td>
            <td>{{ getDecimalExcel($item['importe']) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
