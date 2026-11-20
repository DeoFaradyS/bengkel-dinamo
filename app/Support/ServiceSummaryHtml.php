<?php

namespace App\Support;

// Ringkasan biaya servis untuk kolom kanan (form ubah dan halaman detail admin).
// Daftar minimalis: judul kecil, nama di kiri, jumlah di kanan, rincian qty x harga
// di baris kedua, subtotal, lalu total. Dipakai bersama supaya keduanya sama persis.
class ServiceSummaryHtml
{
    /**
     * @param  array<int, array{name: string, total: float, detail?: string}>  $jasa
     * @param  array<int, array{name: string, total: float, detail?: string}>  $parts
     */
    public static function render(array $jasa, array $parts, float $transport): string
    {
        $total = self::sum($jasa) + self::sum($parts) + $transport;

        $html = '<div style="display:flex;flex-direction:column;gap:1.25rem;font-size:.875rem;line-height:1.4;color:#18181b;">';
        $html .= self::block('Jasa', $jasa, 'Belum ada jasa');
        $html .= self::block('Suku cadang', $parts, 'Belum ada suku cadang');

        if ($transport > 0) {
            $html .= '<div>' . self::heading('Transport')
                . self::row('Ongkos BBM', self::rupiah($transport)) . '</div>';
        }

        $html .= '<div style="display:flex;justify-content:space-between;align-items:baseline;gap:1rem;padding-top:1rem;border-top:1px solid #e4e4e7;">'
            . '<span style="font-weight:600;">Total Servis</span>'
            . '<span style="font-size:1.25rem;font-weight:700;white-space:nowrap;font-variant-numeric:tabular-nums;">' . e(self::rupiah($total)) . '</span>'
            . '</div>';

        return $html . '</div>';
    }

    private static function block(string $title, array $rows, string $empty): string
    {
        $html = '<div>' . self::heading($title);

        if ($rows === []) {
            return $html . '<div style="color:#a1a1aa;padding:.25rem 0;">' . e($empty) . '</div></div>';
        }

        foreach ($rows as $r) {
            $html .= self::row($r['name'], self::rupiah($r['total']), $r['detail'] ?? '');
        }

        if (count($rows) > 1) {
            $html .= '<div style="display:flex;justify-content:space-between;gap:1rem;padding-top:.5rem;margin-top:.25rem;border-top:1px dashed #e4e4e7;color:#71717a;">'
                . '<span>Subtotal</span>'
                . '<span style="white-space:nowrap;font-variant-numeric:tabular-nums;">' . e(self::rupiah(self::sum($rows))) . '</span>'
                . '</div>';
        }

        return $html . '</div>';
    }

    private static function heading(string $title): string
    {
        return '<div style="font-size:.75rem;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:#71717a;margin-bottom:.5rem;">'
            . e($title) . '</div>';
    }

    private static function row(string $name, string $amount, string $detail = ''): string
    {
        return '<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;padding:.25rem 0;">'
            . '<div style="min-width:0;"><div>' . e($name) . '</div>'
            . ($detail !== '' ? '<div style="font-size:.75rem;color:#a1a1aa;">' . e($detail) . '</div>' : '')
            . '</div>'
            . '<span style="white-space:nowrap;font-variant-numeric:tabular-nums;">' . e($amount) . '</span>'
            . '</div>';
    }

    private static function sum(array $rows): float
    {
        return (float) array_sum(array_column($rows, 'total'));
    }

    public static function rupiah(float $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}
