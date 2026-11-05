<?php

namespace App\Services\Report;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TransactionExportService
{
    /**
     * Header columns for the export.
     */
    public const HEADERS = [
        'No',
        'Kode Transaksi',
        'Tanggal Transaksi',
        'Tanggal Selesai',
        'Produk',
        'Kategori',
        'Nama Penjual',
        'Kampus Penjual',
        'Nama Pembeli',
        'Kampus Pembeli',
        'Nilai Barang (Rp)',
        'Ongkir (Rp)',
        'Total Transaksi (Rp)',
        'Metode Pembayaran',
        'Channel Pembayaran',
        'Proteksi Rekber',
        'Metode Pengiriman',
        'Status Pembayaran',
        'Status Transaksi',
    ];

    /**
     * Build the filtered transaction query.
     */
    public function buildQuery(Request $request): Builder
    {
        $query = Transaction::query()
            ->with([
                'product.category',
                'seller.student.university',
                'buyer.student.university',
                'originUniversity',
                'destinationUniversity',
                'escrowPayment',
            ])
            ->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($paymentMethod = $request->input('payment_method')) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($request->filled('use_escrow')) {
            $query->where('use_escrow', $request->boolean('use_escrow'));
        }

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($q = $request->input('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('transaction_code', 'like', "%{$q}%")
                    ->orWhereHas('product', fn ($pq) => $pq->where('title', 'like', "%{$q}%"))
                    ->orWhereHas('buyer', fn ($bq) => $bq->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('seller', fn ($sq) => $sq->where('name', 'like', "%{$q}%"));
            });
        }

        return $query;
    }

    /**
     * Map a transaction instance to an exportable array row.
     */
    public function mapRow(Transaction $transaction, int $index): array
    {
        $sellerUniv = $transaction->seller?->student?->university?->name
            ?? $transaction->originUniversity?->name
            ?? '-';

        $buyerUniv = $transaction->buyer?->student?->university?->name
            ?? $transaction->destinationUniversity?->name
            ?? '-';

        $escrowLabel = $transaction->use_escrow ? 'Ya (Rekber Aktif)' : 'Tidak';

        $deliveryLabel = match ($transaction->delivery_method) {
            Transaction::DELIVERY_METHOD_COD => 'COD Kampus',
            Transaction::DELIVERY_METHOD_PICKUP => 'Pickup di Kampus',
            Transaction::DELIVERY_METHOD_COURIER => 'Kurir Ekspedisi ('.($transaction->courier_name ?? 'Kurir').')',
            default => ucfirst((string) $transaction->delivery_method),
        };

        $paymentStatusLabel = match ($transaction->payment_status) {
            Transaction::PAYMENT_STATUS_CONFIRMED => 'Lunas & Dikonfirmasi',
            Transaction::PAYMENT_STATUS_PAID => 'Telah Dibayar',
            Transaction::PAYMENT_STATUS_PENDING => 'Menunggu Pembayaran',
            Transaction::PAYMENT_STATUS_CANCELLED => 'Dibatalkan',
            default => ucfirst((string) ($transaction->payment_status ?? 'Pending')),
        };

        $statusLabel = match ($transaction->status) {
            Transaction::STATUS_COMPLETED => 'Selesai',
            Transaction::STATUS_PROCESSING => 'Diproses',
            Transaction::STATUS_PENDING => 'Menunggu Pembayaran',
            Transaction::STATUS_CANCELLED => 'Dibatalkan',
            default => ucfirst((string) $transaction->status),
        };

        $amount = (float) ($transaction->amount ?? 0);
        $shipping = (float) ($transaction->shipping_cost ?? 0);
        $total = (float) ($transaction->total_amount ?? ($amount + $shipping));

        return [
            $index,
            $transaction->transaction_code,
            $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i:s') : '-',
            $transaction->confirmed_at ? $transaction->confirmed_at->format('Y-m-d H:i:s') : '-',
            $transaction->product?->title ?? 'Produk Dihapus',
            $transaction->product?->category?->name ?? '-',
            $transaction->seller?->name ?? 'User Dihapus',
            $sellerUniv,
            $transaction->buyer?->name ?? 'User Dihapus',
            $buyerUniv,
            $amount,
            $shipping,
            $total,
            $transaction->payment_method_label ?? strtoupper((string) $transaction->payment_method),
            $transaction->payment_channel_label ?? strtoupper((string) $transaction->payment_channel),
            $escrowLabel,
            $deliveryLabel,
            $paymentStatusLabel,
            $statusLabel,
        ];
    }

    /**
     * Generate standard Excel-compatible CSV with UTF-8 BOM.
     */
    public function exportCsv(Builder $query): string
    {
        $output = fopen('php://temp', 'r+');

        // Add UTF-8 BOM for Microsoft Excel recognition
        fwrite($output, "\xEF\xBB\xBF");

        // Write header row
        fputcsv($output, self::HEADERS);

        $index = 1;
        $query->chunk(100, function ($transactions) use ($output, &$index) {
            foreach ($transactions as $transaction) {
                fputcsv($output, $this->mapRow($transaction, $index++));
            }
        });

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content;
    }

    /**
     * Generate native Microsoft Excel XML SpreadsheetML (.xls/.xlsx).
     */
    public function exportExcelXml(Builder $query): string
    {
        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<?mso-application progid="Excel.Sheet"?>';
        $xml[] = '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"';
        $xml[] = ' xmlns:o="urn:schemas-microsoft-com:office:office"';
        $xml[] = ' xmlns:x="urn:schemas-microsoft-com:office:excel"';
        $xml[] = ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"';
        $xml[] = ' xmlns:html="http://www.w3.org/TR/REC-html40">';

        // Styles
        $xml[] = ' <Styles>';
        $xml[] = '  <Style ss:ID="Default" ss:Name="Normal">';
        $xml[] = '   <Alignment ss:Vertical="Center"/>';
        $xml[] = '   <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#1E293B"/>';
        $xml[] = '  </Style>';
        $xml[] = '  <Style ss:ID="Header">';
        $xml[] = '   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>';
        $xml[] = '   <Borders>';
        $xml[] = '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0D9488"/>';
        $xml[] = '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0D9488"/>';
        $xml[] = '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0D9488"/>';
        $xml[] = '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0D9488"/>';
        $xml[] = '   </Borders>';
        $xml[] = '   <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/>';
        $xml[] = '   <Interior ss:Color="#0F766E" ss:Pattern="Solid"/>';
        $xml[] = '  </Style>';
        $xml[] = '  <Style ss:ID="Currency">';
        $xml[] = '   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>';
        $xml[] = '   <NumberFormat ss:Format="&quot;Rp &quot;#,##0"/>';
        $xml[] = '  </Style>';
        $xml[] = '  <Style ss:ID="Center">';
        $xml[] = '   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>';
        $xml[] = '  </Style>';
        $xml[] = ' </Styles>';

        // Worksheet
        $xml[] = ' <Worksheet ss:Name="Laporan Transaksi">';
        $xml[] = '  <Table ss:DefaultRowHeight="20">';

        // Column widths
        $columnWidths = [
            40, 130, 120, 120, 180, 120, 130, 140, 130, 140, 100, 90, 110, 120, 120, 110, 140, 130, 110,
        ];
        foreach ($columnWidths as $w) {
            $xml[] = '   <Column ss:Width="'.$w.'"/>';
        }

        // Header Row
        $xml[] = '   <Row ss:Height="26" ss:StyleID="Header">';
        foreach (self::HEADERS as $header) {
            $xml[] = '    <Cell><Data ss:Type="String">'.htmlspecialchars($header, ENT_XML1, 'UTF-8').'</Data></Cell>';
        }
        $xml[] = '   </Row>';

        // Data Rows
        $index = 1;
        $query->chunk(100, function ($transactions) use (&$xml, &$index) {
            foreach ($transactions as $transaction) {
                $row = $this->mapRow($transaction, $index++);
                $xml[] = '   <Row>';

                foreach ($row as $colIdx => $val) {
                    if (in_array($colIdx, [10, 11, 12], true)) {
                        // Numeric currency column
                        $xml[] = '    <Cell ss:StyleID="Currency"><Data ss:Type="Number">'.(float) $val.'</Data></Cell>';
                    } elseif ($colIdx === 0) {
                        // Index number
                        $xml[] = '    <Cell ss:StyleID="Center"><Data ss:Type="Number">'.(int) $val.'</Data></Cell>';
                    } elseif (in_array($colIdx, [1, 2, 3], true)) {
                        $xml[] = '    <Cell ss:StyleID="Center"><Data ss:Type="String">'.htmlspecialchars((string) $val, ENT_XML1, 'UTF-8').'</Data></Cell>';
                    } else {
                        $xml[] = '    <Cell><Data ss:Type="String">'.htmlspecialchars((string) $val, ENT_XML1, 'UTF-8').'</Data></Cell>';
                    }
                }

                $xml[] = '   </Row>';
            }
        });

        $xml[] = '  </Table>';
        $xml[] = ' </Worksheet>';
        $xml[] = '</Workbook>';

        return implode("\n", $xml);
    }
}
