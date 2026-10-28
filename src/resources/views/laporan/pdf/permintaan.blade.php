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
        <table class="data-table">
            <thead>
                <tr>
                    <th width="4%">No.</th>
                    <th width="13%">Kode Pengajuan</th>
                    <th width="15%">Pemohon</th>
                    <th width="16%">Nama Barang</th>
                    <th width="7%">Jumlah</th>
                    <th width="12%">Status</th>
                    <th width="12%">Hasil</th>
                    <th width="10%">Mulai</th>
                    <th width="10%">Selesai</th>
                    <th width="11%">Durasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $row)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $row->kode_pengajuan ?? '-' }}</td>
                        <td>{{ $row->nama_pemohon ?? '-' }}</td>
                        <td>{{ $row->pengajuan?->nama_barang ?? $row->nama_barang ?? '-' }}</td>
                        <td class="center">{{ $row->pengajuan?->jumlah ?? '-' }}</td>
                        <td class="center">{{ $row->pengajuan?->status_label ?? $row->status_label ?? '-' }}</td>
                        <td class="center">{{ $row->outcome_label ?? $row->outcome ?? '-' }}</td>
                        <td class="center">{{ $row->pengajuan?->waktu_mulai?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="center">{{ $row->pengajuan?->waktu_selesai?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td>{{ $row->pengajuan?->durasi_pengerjaan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="empty-row" colspan="10">Tidak ada data pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @include('laporan.pdf._signatures')
    </main>
</body>

</html>
