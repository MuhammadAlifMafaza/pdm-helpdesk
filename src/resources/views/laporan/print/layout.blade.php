<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        @include('laporan.pdf._styles')
        @include('laporan.print._print-styles')
    </style>
</head>

<body>
    {{-- Toolbar: hanya tampil di layar, tidak ikut tercetak (.no-print) --}}
    <div class="print-toolbar no-print">
        <button type="button" onclick="window.print()">&#128424; Cetak</button>
        <button type="button"
            onclick="history.length > 1 ? history.back() : window.location.assign('/')">&#8592; Kembali</button>
    </div>

    <div class="sheet">
        {{-- Kop surat (asset URL, bukan path file seperti versi DomPDF) --}}
        <div class="document-header">
            <table>
                <tr>
                    <td class="header-logo"><img src="{{ asset('images/logo-iwima.png') }}" alt="Logo IWIMA"></td>
                    <td class="header-identity">
                        <div class="institution">INSTITUT WIDYA PRATAMA PEKALONGAN</div>
                        <div class="department">KABID TEKNIS DAN PERAWATAN INFRASTRUKTUR</div>
                        <div class="address">JL. PATRIOT NO.25 PEKALONGAN, (0285) 427817, FAX (0285) 427815</div>
                    </td>
                    <td class="header-code">{{ $documentCode }}</td>
                </tr>
            </table>
        </div>

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

            @yield('content')

            @include('laporan.pdf._signatures')
        </main>
    </div>

    <script>
        // Buka dialog print browser secara otomatis saat halaman siap.
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>

</html>
