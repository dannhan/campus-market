<?php

namespace App\Services\Report;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use ZipArchive;

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
     * Compute Excel column letter from 1-based index (1 -> A, 19 -> S, 27 -> AA).
     */
    public static function getColumnLetter(int $colIndex): string
    {
        $letter = '';
        while ($colIndex > 0) {
            $modulo = ($colIndex - 1) % 26;
            $letter = chr(65 + $modulo).$letter;
            $colIndex = (int) (($colIndex - $modulo) / 26);
        }

        return $letter;
    }

    /**
     * Generate genuine Microsoft Excel OpenXML (.xlsx) spreadsheet.
     */
    public function exportXlsx(Builder $query): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'cm_export_').'.xlsx';

        $zip = new ZipArchive;
        if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat arsip XLSX untuk ekspor laporan.');
        }

        // 1. [Content_Types].xml
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>');

        // 2. _rels/.rels
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

        // 3. xl/_rels/workbook.xml.rels
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>');

        // 4. xl/workbook.xml
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Laporan Transaksi" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>');

        // 5. xl/styles.xml
        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <numFmts count="1">
    <numFmt numFmtId="164" formatCode="&quot;Rp &quot;#,##0"/>
  </numFmts>
  <fonts count="2">
    <font><name val="Calibri"/><sz val="11"/><color rgb="FF1E293B"/></font>
    <font><b/><name val="Calibri"/><sz val="11"/><color rgb="FFFFFFFF"/></font>
  </fonts>
  <fills count="3">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF0F766E"/><bgColor indexed="64"/></patternFill></fill>
  </fills>
  <borders count="3">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border>
      <left style="thin"><color rgb="FFCBD5E1"/></left>
      <right style="thin"><color rgb="FFCBD5E1"/></right>
      <top style="thin"><color rgb="FFCBD5E1"/></top>
      <bottom style="thin"><color rgb="FFCBD5E1"/></bottom>
      <diagonal/>
    </border>
    <border>
      <left style="thin"><color rgb="FF0D9488"/></left>
      <right style="thin"><color rgb="FF0D9488"/></right>
      <top style="thin"><color rgb="FF0D9488"/></top>
      <bottom style="thin"><color rgb="FF0D9488"/></bottom>
      <diagonal/>
    </border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="5">
    <!-- 0: text left -->
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="left" vertical="center"/>
    </xf>
    <!-- 1: header center -->
    <xf numFmtId="0" fontId="1" fillId="2" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center" wrapText="1"/>
    </xf>
    <!-- 2: currency right -->
    <xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="right" vertical="center"/>
    </xf>
    <!-- 3: text center -->
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center"/>
    </xf>
    <!-- 4: number center -->
    <xf numFmtId="1" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center"/>
    </xf>
  </cellXfs>
  <cellStyles count="1">
    <cellStyle name="Normal" xfId="0" builtinId="0"/>
  </cellStyles>
</styleSheet>';
        $zip->addFromString('xl/styles.xml', $stylesXml);

        // 6. xl/worksheets/sheet1.xml
        $widths = [6, 22, 20, 20, 30, 18, 20, 22, 20, 22, 18, 16, 20, 18, 18, 18, 22, 22, 18];
        $colXml = '';
        foreach ($widths as $idx => $w) {
            $c = $idx + 1;
            $colXml .= "<col min=\"{$c}\" max=\"{$c}\" width=\"{$w}\" customWidth=\"1\"/>";
        }

        $rowXml = '<row r="1" ht="26" customHeight="1">';
        foreach (self::HEADERS as $idx => $header) {
            $ref = self::getColumnLetter($idx + 1).'1';
            $val = htmlspecialchars($header, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $rowXml .= "<c r=\"{$ref}\" t=\"inlineStr\" s=\"1\"><is><t>{$val}</t></is></c>";
        }
        $rowXml .= '</row>';

        $rowIndex = 2;
        $dataIndex = 1;

        $query->chunk(100, function ($transactions) use (&$rowXml, &$rowIndex, &$dataIndex) {
            foreach ($transactions as $transaction) {
                $row = $this->mapRow($transaction, $dataIndex++);
                $rowXml .= "<row r=\"{$rowIndex}\" ht=\"20\">";

                foreach ($row as $colIdx => $val) {
                    $ref = self::getColumnLetter($colIdx + 1).$rowIndex;

                    if (in_array($colIdx, [10, 11, 12], true)) {
                        // Currency numeric
                        $num = (float) $val;
                        $rowXml .= "<c r=\"{$ref}\" s=\"2\"><v>{$num}</v></c>";
                    } elseif ($colIdx === 0) {
                        // Integer row number
                        $num = (int) $val;
                        $rowXml .= "<c r=\"{$ref}\" s=\"4\"><v>{$num}</v></c>";
                    } elseif (in_array($colIdx, [1, 2, 3, 15, 17, 18], true)) {
                        // Center aligned text
                        $escaped = htmlspecialchars((string) $val, ENT_QUOTES | ENT_XML1, 'UTF-8');
                        $rowXml .= "<c r=\"{$ref}\" t=\"inlineStr\" s=\"3\"><is><t>{$escaped}</t></is></c>";
                    } else {
                        // Left aligned text
                        $escaped = htmlspecialchars((string) $val, ENT_QUOTES | ENT_XML1, 'UTF-8');
                        $rowXml .= "<c r=\"{$ref}\" t=\"inlineStr\" s=\"0\"><is><t>{$escaped}</t></is></c>";
                    }
                }

                $rowXml .= '</row>';
                $rowIndex++;
            }
        });

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetViews>
    <sheetView tabSelected="1" workbookViewId="0"/>
  </sheetViews>
  <sheetFormatPr defaultRowHeight="20"/>
  <cols>'.$colXml.'</cols>
  <sheetData>'.$rowXml.'</sheetData>
</worksheet>';

        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        $content = (string) file_get_contents($tmpFile);
        @unlink($tmpFile);

        return $content;
    }
}
