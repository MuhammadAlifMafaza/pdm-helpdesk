<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @include('laporan.pdf._styles')
    </style>
</head>

<body>
    @include('laporan.pdf._header')
    <main>
        <div class="report-title">
            <h1>{{ $title }}</h1>
            <p>{{ $documentNumber }}</p>
        </div>
        <table class="info-table">
            <tr>
                <td class="info-label">Periode</td>
                <td class="info-separator">:</td>
                <td>{{ $periode }}</td>
            </tr>
            <tr>
                <td class="info-label">Tgl. Dibuat</td>
                <td class="info-separator">:</td>
                <td>{{ now()->locale('id')->translatedFormat('d F Y') }}</td>
            </tr>
        </table>
        @php
            $completedRecords = $records->filter(function ($row) {
                $status = strtolower((string) ($row->status_label ?? $row->status ?? ''));
                return str_contains($status, 'close') || str_contains($status, 'selesai') || str_contains($status, 'baik');
            });
            $pendingRecords = $records->reject(fn($row) => $completedRecords->contains($row));
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
                        <td>{{ $row->tiket?->kode_tiket ?? $row->kode_tiket ?? '-' }}</td>
                        <td>{{ $row->nama_pemohon ?? '-' }}</td>
                        <td>{{ $row->lokasi ?? '-' }}</td>
                        <td class="center">{{ $row->tiket?->created_at?->format('d/m/Y') ?? $row->waktu_mulai?->format('d/m/Y') ?? '-' }}</td>
                        <td class="center">{{ $row->tiket?->waktu_selesai?->format('d/m/Y') ?? $row->waktu_selesai?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $row->tiket?->keluhan ?? $row->service_category ?? '-' }}</td>
                        <td class="center">{{ $row->tiket?->status_label ?? $row->status_label ?? '-' }}</td>
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
                        <td>{{ $row->tiket?->kode_tiket ?? $row->kode_tiket ?? '-' }}</td>
                        <td>{{ $row->nama_pemohon ?? '-' }}</td>
                        <td>{{ $row->lokasi ?? '-' }}</td>
                        <td class="center">{{ $row->tiket?->created_at?->format('d/m/Y') ?? $row->waktu_mulai?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $row->tiket?->keluhan ?? $row->service_category ?? '-' }}</td>
                        <td class="center">{{ $row->tiket?->status_label ?? $row->status_label ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="empty-row" colspan="7">Tidak ada PC yang masih dalam proses.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @include('laporan.pdf._signatures')
    </main>
</body>

</html>
