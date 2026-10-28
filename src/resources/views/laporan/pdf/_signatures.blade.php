<table class="signature-table">
    <tr>
        <td class="signature-date" colspan="2">Pekalongan, {{ now()->locale('id')->translatedFormat('d F Y') }}</td>
    </tr>
    <tr>
        <td class="signature-title">{{ $signatures[0]['title'] }}</td>
        <td class="signature-title">{{ $signatures[1]['title'] }}</td>
    </tr>
    <tr>
        <td class="signature-name">{{ $signatures[0]['name'] }}</td>
        <td class="signature-name">{{ $signatures[1]['name'] }}</td>
    </tr>
</table>
