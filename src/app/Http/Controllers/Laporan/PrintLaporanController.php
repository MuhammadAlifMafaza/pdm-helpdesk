<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PrintLaporanController extends Controller
{
    /**
     * Halaman cetak laporan: merender payload yang di-stash ke session oleh
     * LaporanExportService::print() ketika tombol "Print Preview" ditekan.
     *
     * Payload sengaja TIDAK dihapus setelah dibaca agar refresh (F5) saat
     * dialog cetak terbuka tetap menampilkan halaman yang sama; payload akan
     * tertimpa oleh klik "Print Preview" berikutnya.
     */
    public function __invoke(Request $request): Response|View|RedirectResponse
    {
        $payload = $request->session()->get('laporan.print');

        if (! is_array($payload)) {
            Notification::make()
                ->warning()
                ->title('Sesi cetak berakhir')
                ->body('Silakan ulangi dari halaman laporan.')
                ->send();

            return redirect()->to($request->user()?->homeUrl() ?? route('login'));
        }

        if (is_string($payload['html'] ?? null)) {
            return response($payload['html']);
        }

        if (isset($payload['view'], $payload['data'])) {
            return view($payload['view'], $payload['data']);
        }

        Notification::make()
            ->warning()
            ->title('Sesi cetak berakhir')
            ->body('Silakan ulangi dari halaman laporan.')
            ->send();

        return redirect()->to($request->user()?->homeUrl() ?? route('login'));
    }
}
