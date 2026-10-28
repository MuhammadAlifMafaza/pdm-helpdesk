{{-- Penyesuaian halaman cetak untuk browser (menimpa _styles milik DomPDF) --}}

{{-- Ukuran fisik eksplisit agar dialog cetak browser tidak kembali ke portrait/Letter. --}}
@page {
    size: 297mm 210mm;
    margin: 14mm;
}

{{-- Kop cetak: statis di awal dokumen (posisi fixed ala DomPDF tak berlaku di browser) --}}
.document-header {
    position: static;
    height: auto;
    left: auto;
    right: auto;
    top: auto;
    margin-bottom: 6px;
}

{{-- Toolbar layar --}}
.print-toolbar {
    display: flex;
    gap: 8px;
    justify-content: center;
    margin: 0 auto 12px;
    max-width: 297mm;
}

.print-toolbar button {
    background: #4472c4;
    border: 1px solid #111;
    border-radius: 6px;
    color: #fff;
    cursor: pointer;
    font: inherit;
    font-weight: 600;
    padding: 8px 18px;
}

.print-toolbar button:last-child {
    background: #fff;
    color: #111;
}

{{-- Chrome layar saja: lembar putih di atas latar abu --}}
@media screen {
    body {
        background: #e5e7eb;
        padding: 14px;
    }

    .sheet {
        background: #fff;
        box-shadow: 0 1px 4px rgb(0 0 0 / 0.15);
        margin: 0 auto;
        max-width: 297mm;
        padding: 14mm;
    }
}

@media print {
    {{-- Sembunyikan toolbar --}}
    .no-print {
        display: none !important;
    }

    html,
    body {
        margin: 0;
        padding: 0;
        width: 100%;
    }

    .sheet {
        box-shadow: none;
        margin: 0;
        max-width: none;
        padding: 0;
        width: 100%;
    }

    .data-table {
        table-layout: fixed;
        width: 100%;
    }

    .data-table th,
    .data-table td {
        overflow-wrap: anywhere;
    }

    .data-table thead {
        display: table-header-group;
    }

    .data-table tr,
    .signature-table {
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .section-title {
        break-after: avoid;
        page-break-after: avoid;
    }

    {{-- Warna latar (header tabel biru, kop) tetap tercetak --}}
    * {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
