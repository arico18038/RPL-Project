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
    public function inventory(Request $request)
    {
        $selectedCategory = $request->input('category_id', 'all');
        $menuQuery = MenuItem::with('category')
            ->orderBy('category_id')
            ->orderBy('name');

        if ($selectedCategory !== 'all') {
            $menuQuery->where('category_id', $selectedCategory);
        }

        $lowStockCount = (clone $menuQuery)->where('stock', '<', 20)->count();
        $menus = $menuQuery->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $tables = RestaurantTable::orderBy('number')->get();

        return view('admin.inventory', compact('menus', 'categories', 'tables', 'lowStockCount', 'selectedCategory'));
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
        $todayOrders = Order::whereDate('created_at', today());
        $totalToday = (clone $todayOrders)->count();
        $pendingToday = (clone $todayOrders)->where('status', 'pending')->count();
        $processingToday = (clone $todayOrders)->where('status', 'processing')->count();

        $orders = Order::with(['order_items.menu_item', 'table'])
            ->whereDate('created_at', today())
            ->latest()
            ->paginate(15);

        return view('admin.index', compact('orders', 'totalToday', 'pendingToday', 'processingToday'));
    }

    public function history(Request $request)
    {
        $recapType = $request->input('recap_type', 'monthly');
        if (!in_array($recapType, ['daily', 'monthly', 'yearly'], true)) {
            $recapType = 'monthly';
        }

        $selectedDate = $this->resolveDate($request->input('date'));
        $selectedMonth = $this->resolveMonth($request->input('month'));
        $selectedYear = $this->resolveYear($request->input('year'));

        $query = Order::with(['order_items.menu_item', 'table'])
            ->whereIn('status', ['completed', 'processing', 'pending']);

        if ($recapType === 'daily') {
            $query->whereDate('created_at', $selectedDate->format('Y-m-d'));
            $periodLabel = 'Tanggal ' . $selectedDate->format('d/m/Y');
        } elseif ($recapType === 'yearly') {
            $query->whereYear('created_at', $selectedYear);
            $periodLabel = "Tahun {$selectedYear}";
        } else {
            $query->whereYear('created_at', $selectedMonth->year)
                ->whereMonth('created_at', $selectedMonth->month);
            $periodLabel = 'Bulan ' . $selectedMonth->format('m/Y');
        }

        $summaryOrders = (clone $query)->get();
        $orders = $query->latest()->paginate(15)->withQueryString();
        $totalSales = $summaryOrders->sum('total_price');
        $grossProfit = max($totalSales * 0.45, 0);
        $totalTransactions = $summaryOrders->count();

        return view('admin.history', [
            'orders' => $orders,
            'profile' => SiteSetting::profileContent(),
            'totalSales' => $totalSales,
            'grossProfit' => $grossProfit,
            'totalTransactions' => $totalTransactions,
            'recapType' => $recapType,
            'selectedDate' => $selectedDate->format('Y-m-d'),
            'selectedMonth' => $selectedMonth->format('Y-m'),
            'selectedYear' => $selectedYear,
            'periodLabel' => $periodLabel,
        ]);
    }

    public function report(Request $request)
    {
        [$startDate, $endDate] = $this->resolveReportPeriod($request);

        $orders = Order::with('order_items')
            ->whereIn('status', ['completed', 'processing', 'pending'])
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->oldest()
            ->get();
        $totalSales = $orders->sum('total_price');
        $totalExpense = $totalSales * 0.61;
        $grossProfit = max($totalSales - $totalExpense, 0);
        $chartData = $this->buildReportChart($orders, $startDate, $endDate);

        return view('admin.report', compact('orders', 'totalSales', 'totalExpense', 'grossProfit', 'startDate', 'endDate', 'chartData'));
    }

    public function exportReport(Request $request)
    {
        [$startDate, $endDate] = $this->resolveReportPeriod($request);
        $orders = Order::with(['order_items.menu_item', 'table'])
            ->whereIn('status', ['completed', 'processing', 'pending'])
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->oldest()
            ->get();
        $fileName = 'laporan-' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.xlsx';
        $exportPath = $this->createReportSpreadsheet($orders, $startDate, $endDate);

        return response()
            ->download($exportPath, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }

    public function settings()
    {
        return view('admin.settings', [
            'profile' => SiteSetting::profileContent(),
        ]);
    }

    public function receiptSettings()
    {
        return view('admin.settings-receipt', [
            'salesSettings' => SiteSetting::salesSettings(),
        ]);
    }

    public function userSettings()
    {
        return redirect()->route('admin.settings');
    }

    public function dataSettings()
    {
        return view('admin.settings-data', [
            'tables' => RestaurantTable::orderBy('number')->get(),
        ]);
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
        $category = Category::findOrFail($validated['category_id']);
        $imageUrl = $this->resolveMenuImage($request, $validated['image_url'] ?? null);
        $isAvailable = (bool) $validated['is_available'];

        MenuItem::create([
            'name' => $validated['name'],
            'code' => $this->generateMenuCode(),
            'category' => $this->legacyCategoryValue($category),
            'category_id' => $validated['category_id'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'unit' => $validated['unit'],
            'description' => $validated['description'] ?? null,
            'image_url' => $imageUrl,
            'is_active' => $isAvailable,
            'is_available' => $isAvailable,
        ]);

        return redirect()
            ->route('admin.index')
            ->with('success', 'Menu baru berhasil ditambahkan.');
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Kategori tersebut sudah tersedia.',
            'name.max' => 'Nama kategori maksimal 80 karakter.',
            'description.max' => 'Deskripsi kategori maksimal 255 karakter.',
        ]);

        Category::create($validated);

        return redirect()
            ->route('admin.index')
            ->with('success', 'Kategori baru berhasil ditambahkan.');
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
        $category = Category::findOrFail($validated['category_id']);
        $imageUrl = $this->resolveMenuImage($request, $validated['image_url'] ?? null, $menu->image_url);
        $isAvailable = (bool) $validated['is_available'];

        $menu->update([
            'name' => $validated['name'],
            'category' => $this->legacyCategoryValue($category),
            'category_id' => $validated['category_id'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'unit' => $validated['unit'],
            'description' => $validated['description'] ?? null,
            'image_url' => $imageUrl,
            'is_active' => $isAvailable,
            'is_available' => $isAvailable,
        ]);

        return redirect()
            ->route('admin.index')
            ->with('success', 'Menu berhasil diperbarui.');
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:120'],
            'store_address' => ['nullable', 'string', 'max:500'],
            'store_phone' => ['nullable', 'string', 'max:30'],
            'store_whatsapp' => ['nullable', 'string', 'max:30'],
            'store_npwp' => ['nullable', 'string', 'max:40'],
            'store_logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ], [
            'store_name.required' => 'Nama toko wajib diisi.',
            'store_logo_file.image' => 'Logo toko harus berupa gambar.',
            'store_logo_file.max' => 'Ukuran logo maksimal 2MB.',
        ]);

        $profile = SiteSetting::profileContent();
        $values = [
            'store_name' => $validated['store_name'],
            'store_address' => $validated['store_address'] ?? '',
            'store_phone' => $validated['store_phone'] ?? '',
            'store_whatsapp' => $validated['store_whatsapp'] ?? '',
            'store_npwp' => $validated['store_npwp'] ?? '',
            'store_logo' => $profile['store_logo'],
        ];

        if ($request->hasFile('store_logo_file')) {
            $values['store_logo'] = $this->storePublicUpload($request->file('store_logo_file'), 'store');
        }

        SiteSetting::saveMany($values);

        return redirect()
            ->route('admin.settings')
            ->with('success', 'Profil toko berhasil diperbarui.');
    }

    public function updateReceipt(Request $request)
    {
        $validated = $request->validate([
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'discount_enabled' => ['nullable', Rule::in(['0', '1'])],
            'discount_type' => ['required', Rule::in(['persen', 'rupiah'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
        ], [
            'tax_rate.required' => 'Nilai pajak wajib diisi.',
            'tax_rate.numeric' => 'Nilai pajak harus berupa angka.',
            'discount_value.required' => 'Nilai diskon wajib diisi.',
            'discount_value.numeric' => 'Nilai diskon harus berupa angka.',
        ]);

        if ($validated['discount_type'] === 'persen' && (float) $validated['discount_value'] > 100) {
            return back()
                ->withInput()
                ->withErrors(['discount_value' => 'Diskon persen maksimal 100%.']);
        }

        SiteSetting::saveMany([
            'tax_rate' => (string) $validated['tax_rate'],
            'discount_enabled' => $request->boolean('discount_enabled') ? '1' : '0',
            'discount_type' => $validated['discount_type'],
            'discount_value' => (string) $validated['discount_value'],
        ]);

        return redirect()
            ->route('admin.settings.receipt')
            ->with('success', 'Pengaturan diskon dan pajak berhasil diperbarui.');
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
            'unit' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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
            'unit.required' => 'Satuan wajib dipilih.',
            'unit.max' => 'Satuan maksimal 30 karakter.',
            'description.max' => 'Deskripsi maksimal 1000 karakter.',
            'image_url.url' => 'URL gambar harus berupa tautan yang valid.',
            'image_url.max' => 'URL gambar terlalu panjang. Maksimal 2048 karakter.',
            'image_file.image' => 'File gambar harus berupa gambar.',
            'image_file.max' => 'Ukuran gambar maksimal 2MB.',
            'is_available.required' => 'Status aktif wajib dipilih.',
        ]);
    }

    private function resolveMenuImage(Request $request, ?string $imageUrl, ?string $currentImage = null): ?string
    {
        if ($request->hasFile('image_file')) {
            return $this->storePublicUpload($request->file('image_file'), 'menu');
        }

        return $this->normalizeImageUrl($imageUrl) ?: $currentImage;
    }

    private function generateMenuCode(): string
    {
        $nextId = (int) (MenuItem::max('id') ?? 0) + 1;

        do {
            $code = 'BRG-' . str_pad((string) $nextId, 3, '0', STR_PAD_LEFT);
            $nextId++;
        } while (MenuItem::where('code', $code)->exists());

        return $code;
    }

    private function legacyCategoryValue(Category $category): string
    {
        $name = strtolower($category->slug ?? $category->name);

        if (str_contains($name, 'makan')) {
            return 'makanan';
        }

        if (str_contains($name, 'minum')) {
            return 'minuman';
        }

        return 'lainnya';
    }

    private function storePublicUpload($file, string $folder): string
    {
        $directory = public_path('uploads/' . $folder);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $name = uniqid($folder . '-', true) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $name);

        return 'uploads/' . $folder . '/' . $name;
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
        $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbook('Barang dan Stok'));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRels());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->xlsxInventorySheet());
        $zip->close();

        return $path;
    }

    private function createReportSpreadsheet($orders, Carbon $startDate, Carbon $endDate): string
    {
        $exportDir = storage_path('app/exports');
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0775, true);
        }

        $path = tempnam($exportDir, 'laporan-');
        $zip = new \ZipArchive();

        if ($zip->open($path, \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Gagal membuat file Excel laporan.');
        }

        $zip->addFromString('[Content_Types].xml', $this->xlsxContentTypes());
        $zip->addFromString('_rels/.rels', $this->xlsxRootRels());
        $zip->addFromString('docProps/app.xml', $this->xlsxAppProperties());
        $zip->addFromString('docProps/core.xml', $this->xlsxCoreProperties());
        $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbook('Laporan'));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRels());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->xlsxReportSheet($orders, $startDate, $endDate));
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
                        $menu->unit ?? (str_contains(strtolower($categoryName), 'minuman') ? 'Botol' : 'Pcs'),
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

    private function xlsxReportSheet($orders, Carbon $startDate, Carbon $endDate): string
    {
        $rows = [
            ['Laporan Transaksi Rumah Makan 4SR'],
            ['Periode', $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y')],
            [],
            ['No. Transaksi', 'Tanggal/Jam', 'Meja', 'Total Item', 'Total Pembayaran', 'Estimasi Pengeluaran', 'Laba Kotor', 'Status'],
        ];

        foreach ($orders as $order) {
            $total = (float) $order->total_price;
            $expense = $total * 0.61;

            $rows[] = [
                'TRX-' . ($order->created_at?->format('Y') ?? date('Y')) . '-' . str_pad((string) $order->id, 3, '0', STR_PAD_LEFT),
                $order->created_at?->format('d/m/Y H:i') ?? '-',
                $order->table ? 'Meja ' . $order->table->number : '-',
                (string) $order->order_items->sum('quantity'),
                'Rp ' . number_format($total, 0, ',', '.'),
                'Rp ' . number_format($expense, 0, ',', '.'),
                'Rp ' . number_format(max($total - $expense, 0), 0, ',', '.'),
                $order->status,
            ];
        }

        $totalSales = $orders->sum('total_price');
        $totalExpense = $totalSales * 0.61;
        $rows[] = [];
        $rows[] = ['Total transaksi', (string) $orders->count()];
        $rows[] = ['Total pendapatan', 'Rp ' . number_format($totalSales, 0, ',', '.')];
        $rows[] = ['Total pengeluaran', 'Rp ' . number_format($totalExpense, 0, ',', '.')];
        $rows[] = ['Laba kotor', 'Rp ' . number_format(max($totalSales - $totalExpense, 0), 0, ',', '.')];

        return $this->xlsxRowsToSheet($rows);
    }

    private function xlsxRowsToSheet(array $rows): string
    {
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
            . '<cols><col min="1" max="10" width="22" customWidth="1"/></cols>'
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

    private function xlsxWorkbook(string $sheetName = 'Barang dan Stok'): string
    {
        $sheetName = htmlspecialchars($sheetName, ENT_QUOTES | ENT_XML1, 'UTF-8');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . $sheetName . '" sheetId="1" r:id="rId1"/></sheets>'
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

    private function resolveDate(?string $date): Carbon
    {
        if ($date) {
            try {
                return Carbon::parse($date)->startOfDay();
            } catch (\Throwable) {
                //
            }
        }

        return now()->startOfDay();
    }

    private function resolveYear(?string $year): string
    {
        if ($year && preg_match('/^\d{4}$/', $year)) {
            return $year;
        }

        return now()->format('Y');
    }

    private function resolveReportPeriod(Request $request): array
    {
        try {
            $startDate = $request->filled('start_date')
                ? Carbon::parse($request->input('start_date'))->startOfDay()
                : now()->startOfMonth();
        } catch (\Throwable) {
            $startDate = now()->startOfMonth();
        }

        try {
            $endDate = $request->filled('end_date')
                ? Carbon::parse($request->input('end_date'))->endOfDay()
                : now()->endOfMonth();
        } catch (\Throwable) {
            $endDate = now()->endOfMonth();
        }

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        return [$startDate, $endDate];
    }

    private function buildReportChart($orders, Carbon $startDate, Carbon $endDate): array
    {
        $days = [];
        $cursor = $startDate->copy()->startOfDay();
        while ($cursor->lte($endDate) && count($days) < 31) {
            $key = $cursor->format('Y-m-d');
            $sales = $orders
                ->filter(fn ($order) => $order->created_at?->format('Y-m-d') === $key)
                ->sum('total_price');

            $days[] = [
                'label' => $cursor->format('d/m'),
                'sales' => (float) $sales,
                'expense' => (float) ($sales * 0.61),
            ];

            $cursor->addDay();
        }

        if ($days === []) {
            $days[] = ['label' => now()->format('d/m'), 'sales' => 0, 'expense' => 0];
        }

        $maxValue = max(1, collect($days)->max(fn ($day) => max($day['sales'], $day['expense'])));
        $days = collect($days)->map(function ($day) use ($maxValue) {
            $profit = max($day['sales'] - $day['expense'], 0);

            return array_merge($day, [
                'profit' => $profit,
                'salesHeight' => max(3, ($day['sales'] / $maxValue) * 100),
                'expenseHeight' => max(3, ($day['expense'] / $maxValue) * 100),
                'profitHeight' => max(3, ($profit / $maxValue) * 100),
            ]);
        })->all();

        return [
            'days' => $days,
            'maxValue' => $maxValue,
        ];
    }
}
