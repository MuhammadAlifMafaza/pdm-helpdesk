@extends('laporan.print.layout')

@section('content')
    @php
        $formatReportDate = static function ($value, string $format): string {
            return blank($value)
                ? '-'
                : \Illuminate\Support\Carbon::parse($value)->format($format);
        };
    @endphp

    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="10%">Tanggal</th>
                <th width="25%">Petugas</th>
                <th width="12%">Lokasi</th>
                <th width="43%">Deskripsi Kegiatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse(collect($records) as $index => $row)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="center">{{ $formatReportDate(data_get($row, 'tanggal_kegiatan'), 'd/m/Y') }}</td>
                    <td>{{ data_get($row, 'nama_teknisi') ?? '-' }}</td>
                    <td class="center">{{ data_get($row, 'lokasi') ?? '-' }}</td>
                    <td>{{ data_get($row, 'deskripsi') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td class="empty-row" colspan="5">Tidak ada kegiatan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
