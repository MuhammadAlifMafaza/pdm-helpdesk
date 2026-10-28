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
                <th width="4%">No.</th>
                <th width="12%">Kode Pengajuan</th>
                <th width="14%">Pemohon</th>
                <th width="15%">Nama Barang</th>
                <th width="6%">Jumlah</th>
                <th width="10%">Status</th>
                <th width="10%">Hasil</th>
                <th width="10%">Mulai</th>
                <th width="10%">Selesai</th>
                <th width="9%">Durasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse(collect($records) as $index => $row)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ data_get($row, 'kode_pengajuan') ?? '-' }}</td>
                    <td>{{ data_get($row, 'nama_pemohon') ?? '-' }}</td>
                    <td>{{ data_get($row, 'pengajuan.nama_barang') ?? data_get($row, 'nama_barang') ?? '-' }}</td>
                    <td class="center">{{ data_get($row, 'pengajuan.jumlah') ?? '-' }}</td>
                    <td class="center">{{ data_get($row, 'pengajuan.status_label') ?? data_get($row, 'status_label') ?? '-' }}</td>
                    <td class="center">{{ data_get($row, 'outcome_label') ?? data_get($row, 'outcome') ?? '-' }}</td>
                    <td class="center">{{ $formatReportDate(data_get($row, 'pengajuan.waktu_mulai'), 'd/m/Y H:i') }}</td>
                    <td class="center">{{ $formatReportDate(data_get($row, 'pengajuan.waktu_selesai'), 'd/m/Y H:i') }}</td>
                    <td>{{ data_get($row, 'pengajuan.durasi_pengerjaan') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td class="empty-row" colspan="10">Tidak ada data pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
