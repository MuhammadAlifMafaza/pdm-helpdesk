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
                    <th width="4%">No</th>
                    <th width="10%">Tanggal</th>
                    <th width="25%">Petugas</th>
                    <th width="12%">Lokasi</th>
                    <th width="43%">Deskripsi Kegiatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $row)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td class="center">{{ $row->tanggal_kegiatan?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $row->nama_teknisi ?? '-' }}</td>
                        <td class="center">{{ $row->lokasi ?? '-' }}</td>
                        <td>{{ $row->deskripsi ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="empty-row" colspan="5">Tidak ada kegiatan pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @include('laporan.pdf._signatures')
    </main>
</body>

</html>
