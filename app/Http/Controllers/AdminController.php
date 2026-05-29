<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function inventory()
    {
        $menus = MenuItem::with('category')
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();
        $categories = Category::orderBy('name')->get();
        $tables = RestaurantTable::orderBy('number')->get();
        $lowStockCount = $menus->filter(fn ($menu) => $menu->stock < 20)->count();

        return view('admin.inventory', compact('menus', 'categories', 'tables', 'lowStockCount'));
    }

    public function exportInventory()
    {
        $fileName = 'barang-stok-' . now()->format('Ymd-His') . '.xlsx';
        $exportPath = $this->createInventorySpreadsheet();

        return response()
            ->download($exportPath, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }

    public function orders()
    {
        $orders = Order::with(['order_items.menu_item', 'table'])->latest()->get();

        return view('admin.index', compact('orders'));
    }

    public function history(Request $request)
    {
        $recapType = $request->input('recap_type', 'monthly');
        if (!in_array($recapType, ['monthly', 'yearly'], true)) {
            $recapType = 'monthly';
        }

        $selectedMonth = $this->resolveMonth($request->input('month'));
        $selectedYear = $this->resolveYear($request->input('year'));

        $query = Order::with(['order_items.menu_item', 'table'])
            ->whereIn('status', ['completed', 'processing', 'pending']);

        if ($recapType === 'yearly') {
            $query->whereYear('created_at', $selectedYear);
            $periodLabel = "Tahun {$selectedYear}";
        } else {
            $query->whereYear('created_at', $selectedMonth->year)
                ->whereMonth('created_at', $selectedMonth->month);
            $periodLabel = 'Bulan ' . $selectedMonth->format('m/Y');
        }

        $orders = $query->latest()->get();
        $totalSales = $orders->sum('total_price');
        $grossProfit = max($totalSales * 0.45, 0);

        return view('admin.history', [
            'orders' => $orders,
            'totalSales' => $totalSales,
            'grossProfit' => $grossProfit,
            'recapType' => $recapType,
            'selectedMonth' => $selectedMonth->format('Y-m'),
            'selectedYear' => $selectedYear,
            'periodLabel' => $periodLabel,
        ]);
    }

    public function report()
    {
        $orders = Order::with('order_items')
            ->whereIn('status', ['completed', 'processing', 'pending'])
            ->latest()
            ->get();
        $totalSales = $orders->sum('total_price');
        $totalExpense = $totalSales * 0.61;
        $grossProfit = max($totalSales - $totalExpense, 0);

        return view('admin.report', compact('orders', 'totalSales', 'totalExpense', 'grossProfit'));
    }

    public function settings()
    {
        return view('admin.settings');
    }

    public function aboutSettings()
    {
        return view('admin.settings-about', [
            'about' => SiteSetting::aboutContent(),
        ]);
    }

    public function updateAbout(Request $request)
    {
        $validated = $request->validate([
            'about_kicker' => ['required', 'string', 'max:80'],
            'about_title' => ['required', 'string', 'max:160'],
            'about_description' => ['required', 'string', 'max:1200'],
            'about_feature_1_title' => ['required', 'string', 'max:80'],
            'about_feature_1_text' => ['required', 'string', 'max:400'],
            'about_feature_2_title' => ['required', 'string', 'max:80'],
            'about_feature_2_text' => ['required', 'string', 'max:400'],
            'about_feature_3_title' => ['required', 'string', 'max:80'],
            'about_feature_3_text' => ['required', 'string', 'max:400'],
        ], [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute terlalu panjang.',
        ]);

        SiteSetting::saveMany($validated);

        return redirect()
            ->route('admin.settings.about')
            ->with('success', 'Konten Tentang Kami berhasil diperbarui.');
    }

    public function storeMenu(Request $request)
    {
        $validated = $this->validateMenu($request);

        MenuItem::create([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'description' => $validated['description'] ?? null,
            'image_url' => $this->normalizeImageUrl($validated['image_url'] ?? null),
            'is_available' => (bool) $validated['is_available'],
        ]);

        return redirect()
            ->route('admin.index')
            ->with('success', 'Menu baru berhasil ditambahkan.');
    }

    public function storeTable(Request $request)
    {
        $validated = $request->validate([
            'number' => ['required', 'integer', 'min:1', 'max:999', 'unique:tables,number'],
        ], [
            'number.required' => 'Nomor meja wajib diisi.',
            'number.integer' => 'Nomor meja harus berupa angka.',
            'number.min' => 'Nomor meja minimal 1.',
            'number.max' => 'Nomor meja maksimal 999.',
            'number.unique' => 'Nomor meja sudah terdaftar.',
        ]);

        RestaurantTable::create([
            'number' => $validated['number'],
            'qr_code' => route('customer.menu.table', $validated['number']),
        ]);

        return redirect()
            ->route('admin.index')
            ->with('success', 'Meja ' . $validated['number'] . ' berhasil ditambahkan.');
    }

    public function destroyTable(RestaurantTable $table)
    {
        if (Order::where('table_id', $table->id)->exists()) {
            return redirect()
                ->route('admin.index')
                ->withErrors(['number' => 'Meja ' . $table->number . ' tidak dapat dihapus karena sudah digunakan pada pesanan.']);
        }

        $number = $table->number;
        $table->delete();

        return redirect()
            ->route('admin.index')
            ->with('success', 'Meja ' . $number . ' berhasil dihapus.');
    }

    public function updateMenu(Request $request, MenuItem $menu)
    {
        $validated = $this->validateMenu($request);

        $menu->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'description' => $validated['description'] ?? null,
            'image_url' => $this->normalizeImageUrl($validated['image_url'] ?? null),
            'is_available' => (bool) $validated['is_available'],
        ]);

        return redirect()
            ->route('admin.index')
            ->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroyMenu(MenuItem $menu)
    {
        OrderItem::where('menu_item_id', $menu->id)->update(['menu_item_id' => null]);
        $menu->delete();

        return redirect()
            ->route('admin.index')
            ->with('success', 'Menu berhasil dihapus.');
    }

    public function markProcessing(Order $order)
    {
        $order->update(['status' => 'processing']);

        return back()->with('success', 'Pesanan sedang diproses.');
    }

    public function markCompleted(Order $order)
    {
        $order->update(['status' => 'completed']);

        return back()->with('success', 'Pesanan selesai.');
    }

    private function validateMenu(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'is_available' => ['required', Rule::in(['0', '1'])],
        ], [
            'name.required' => 'Nama barang wajib diisi.',
            'name.max' => 'Nama barang maksimal 255 karakter.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak tersedia.',
            'price.required' => 'Harga jual wajib diisi.',
            'price.numeric' => 'Harga jual harus berupa angka.',
            'price.min' => 'Harga jual tidak boleh kurang dari 0.',
            'stock.required' => 'Stok wajib diisi.',
            'stock.integer' => 'Stok harus berupa angka bulat.',
            'stock.min' => 'Stok tidak boleh kurang dari 0.',
            'description.max' => 'Deskripsi maksimal 1000 karakter.',
            'image_url.url' => 'URL gambar harus berupa tautan yang valid.',
            'image_url.max' => 'URL gambar terlalu panjang. Maksimal 2048 karakter.',
            'is_available.required' => 'Status aktif wajib dipilih.',
        ]);
    }

    private function normalizeImageUrl(?string $imageUrl): ?string
    {
        if (!$imageUrl) {
            return null;
        }

        $parts = parse_url($imageUrl);
        if (($parts['host'] ?? '') && str_contains($parts['host'], 'google.') && isset($parts['query'])) {
            parse_str($parts['query'], $query);

            if (!empty($query['imgurl'])) {
                return $query['imgurl'];
            }
        }

        return $imageUrl;
    }

    private function createInventorySpreadsheet(): string
    {
        $exportDir = storage_path('app/exports');
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0775, true);
        }

        $path = tempnam($exportDir, 'barang-stok-');
        $zip = new \ZipArchive();

        if ($zip->open($path, \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Gagal membuat file Excel.');
        }

        $zip->addFromString('[Content_Types].xml', $this->xlsxContentTypes());
        $zip->addFromString('_rels/.rels', $this->xlsxRootRels());
        $zip->addFromString('docProps/app.xml', $this->xlsxAppProperties());
        $zip->addFromString('docProps/core.xml', $this->xlsxCoreProperties());
        $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRels());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->xlsxInventorySheet());
        $zip->close();

        return $path;
    }

    private function xlsxInventorySheet(): string
    {
        $rows = [[
            'Kode barang',
            'Nama barang',
            'Kategori',
            'Satuan',
            'Harga beli',
            'Harga jual',
            'Stok',
            'Min. stok',
            'Status',
            'Deskripsi',
        ]];

        MenuItem::with('category')
            ->orderBy('category_id')
            ->orderBy('name')
            ->chunk(100, function ($menus) use (&$rows) {
                foreach ($menus as $menu) {
                    $sellPrice = (float) $menu->price;
                    $buyPrice = $sellPrice * 0.55;
                    $categoryName = $menu->category?->name ?? $menu->getAttribute('category') ?? '-';
                    $isAvailable = $menu->getAttribute('is_available') ?? $menu->getAttribute('is_active') ?? false;

                    $rows[] = [
                        $menu->getAttribute('code') ?: 'BRG-' . str_pad((string) $menu->id, 3, '0', STR_PAD_LEFT),
                        $menu->name,
                        $categoryName,
                        str_contains(strtolower($categoryName), 'minuman') ? 'Botol' : 'Pcs',
                        'Rp ' . number_format($buyPrice, 0, ',', '.'),
                        'Rp ' . number_format($sellPrice, 0, ',', '.'),
                        (string) $menu->stock,
                        '20',
                        $isAvailable ? 'Aktif' : 'Nonaktif',
                        $menu->description ?? '-',
                    ];
                }
            });

        $sheetRows = collect($rows)
            ->map(function ($row, $rowIndex) {
                $cells = collect($row)
                    ->map(fn ($value, $columnIndex) => $this->xlsxCell($columnIndex, $rowIndex + 1, (string) $value))
                    ->implode('');

                return '<row r="' . ($rowIndex + 1) . '">' . $cells . '</row>';
            })
            ->implode('');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<cols><col min="1" max="10" width="20" customWidth="1"/></cols>'
            . '<sheetData>' . $sheetRows . '</sheetData>'
            . '</worksheet>';
    }

    private function xlsxCell(int $columnIndex, int $rowIndex, string $value): string
    {
        $cell = chr(65 + $columnIndex) . $rowIndex;
        $escaped = htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');

        return '<c r="' . $cell . '" t="inlineStr"><is><t>' . $escaped . '</t></is></c>';
    }

    private function xlsxContentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . '</Types>';
    }

    private function xlsxRootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private function xlsxWorkbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Barang dan Stok" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function xlsxWorkbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>';
    }

    private function xlsxAppProperties(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" '
            . 'xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>CashDig-4SR</Application>'
            . '</Properties>';
    }

    private function xlsxCoreProperties(): string
    {
        $createdAt = now()->toIso8601String();

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
            . 'xmlns:dc="http://purl.org/dc/elements/1.1/" '
            . 'xmlns:dcterms="http://purl.org/dc/terms/" '
            . 'xmlns:dcmitype="http://purl.org/dc/dcmitype/" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:title>Data Barang dan Stok</dc:title>'
            . '<dc:creator>CashDig-4SR</dc:creator>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $createdAt . '</dcterms:created>'
            . '</cp:coreProperties>';
    }

    private function resolveMonth(?string $month): Carbon
    {
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            try {
                return Carbon::createFromFormat('Y-m-d', "{$month}-01")->startOfMonth();
            } catch (\Throwable) {
                //
            }
        }

        return now()->startOfMonth();
    }

    private function resolveYear(?string $year): string
    {
        if ($year && preg_match('/^\d{4}$/', $year)) {
            return $year;
        }

        return now()->format('Y');
    }
}
