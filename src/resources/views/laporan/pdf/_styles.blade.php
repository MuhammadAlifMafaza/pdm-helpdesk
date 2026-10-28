@page {
    margin: 32mm 12mm 16mm;
}

* {
    box-sizing: border-box;
}

body {
    color: #111;
    font-family: Arial, sans-serif;
    font-size: 10px;
    margin: 0;
}

.document-header {
    border-bottom: 1.5px solid #111;
    height: 25mm;
    left: 0;
    position: fixed;
    right: 0;
    top: -25mm;
}

.document-header table,
.signature-table,
.info-table {
    border-collapse: collapse;
    width: 100%;
}

.document-header td {
    border: 0;
    padding: 0;
    vertical-align: middle;
}

.header-logo { width: 18%; }
.header-logo img { height: 21mm; width: 21mm; }
.header-identity { text-align: center; width: 67%; }
.header-identity .institution { font-size: 15px; font-weight: bold; }
.header-identity .department { font-size: 12px; font-weight: bold; margin-top: 3px; }
.header-identity .address { font-size: 9px; margin-top: 5px; }
.header-code { font-size: 9px; font-weight: bold; text-align: right; vertical-align: top !important; width: 15%; }
.report-title { margin: 0 0 2px; text-align: center; }
.report-title h1 { font-size: 15px; margin: 0; text-transform: uppercase; }
.report-title p { font-size: 11px; margin: 4px 0 12px; }
.info-table { margin-bottom: 8px; }
.info-table td { border: 0; padding: 2px 0; }
.info-label { font-weight: bold; width: 18%; }
.info-separator { width: 2%; }
.data-table { border-collapse: collapse; margin-top: 8px; page-break-inside: auto; width: 100%; }
.data-table thead { display: table-header-group; }
.data-table tr { page-break-inside: avoid; }
.data-table th,
.data-table td { border: 1px solid #111; padding: 5px 4px; vertical-align: middle; }
.data-table th { background: #4472c4; color: #fff; font-weight: bold; text-align: center; }
.data-table td.center { text-align: center; }
.section-title { font-size: 11px; font-weight: bold; margin: 12px 0 5px; }
.signature-table { margin-top: 22px; page-break-inside: avoid; }
.signature-table td { border: 0; padding: 0; text-align: center; vertical-align: top; width: 50%; }
.signature-date { padding-bottom: 18px !important; text-align: right !important; }
.signature-title { font-weight: bold; height: 18mm; }
.signature-name { font-weight: bold; text-decoration: underline; }
.empty-row { padding: 14px !important; text-align: center; }
