@extends('laporan.print.layout')

@section('content')
    @php
        $records = collect($records);
        $completedRecords = $records->filter(function ($row) {
            $status = mb_strtolower((string) (data_get($row, 'status_label') ?? data_get($row, 'status') ?? ''));
            return str_contains($status, 'close') || str_contains($status, 'selesai') || str_contains($status, 'baik');
        });
        $pendingRecords = $records->reject(fn ($row) => $completedRecords->contains($row));
        $formatReportDate = static function ($value, string $format): string {
            return blank($value)
                ? '-'
                : \Illuminate\Support\Carbon::parse($value)->format($format);
        };
    @endphp

    <div class="section-title">A. PC SELESAI DICEK &amp; DIPERBAIKI</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No.</th>
                <th width="12%">Kode Tiket</th>
                <th width="15%">Pemohon</th>
                <th width="12%">Lokasi</th>
                <th width="12%">Tgl Masuk</th>
                <th width="12%">Tgl Selesai</th>
                <th width="21%">Keterangan</th>
                <th width="12%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($completedRecords as $index => $row)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ data_get($row, 'tiket.kode_tiket') ?? data_get($row, 'kode_tiket') ?? '-' }}</td>
                    <td>{{ data_get($row, 'nama_pemohon') ?? '-' }}</td>
                    <td>{{ data_get($row, 'lokasi') ?? '-' }}</td>
                    <td class="center">{{ $formatReportDate(data_get($row, 'tiket.created_at') ?? data_get($row, 'waktu_mulai'), 'd/m/Y') }}</td>
                    <td class="center">{{ $formatReportDate(data_get($row, 'tiket.waktu_selesai') ?? data_get($row, 'waktu_selesai'), 'd/m/Y') }}</td>
                    <td>{{ data_get($row, 'tiket.keluhan') ?? data_get($row, 'service_category') ?? '-' }}</td>
                    <td class="center">{{ data_get($row, 'tiket.status_label') ?? data_get($row, 'status_label') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td class="empty-row" colspan="8">Tidak ada PC yang selesai diperbaiki.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">B. PC MASIH DALAM PROSES / MENUNGGU</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No.</th>
                <th width="14%">Kode Tiket</th>
                <th width="17%">Pemohon</th>
                <th width="14%">Lokasi</th>
                <th width="14%">Tgl Masuk</th>
                <th width="23%">Kerusakan / Masalah</th>
                <th width="14%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendingRecords as $index => $row)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ data_get($row, 'tiket.kode_tiket') ?? data_get($row, 'kode_tiket') ?? '-' }}</td>
                    <td>{{ data_get($row, 'nama_pemohon') ?? '-' }}</td>
                    <td>{{ data_get($row, 'lokasi') ?? '-' }}</td>
                    <td class="center">{{ $formatReportDate(data_get($row, 'tiket.created_at') ?? data_get($row, 'waktu_mulai'), 'd/m/Y') }}</td>
                    <td>{{ data_get($row, 'tiket.keluhan') ?? data_get($row, 'service_category') ?? '-' }}</td>
                    <td class="center">{{ data_get($row, 'tiket.status_label') ?? data_get($row, 'status_label') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td class="empty-row" colspan="7">Tidak ada PC yang masih dalam proses.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
