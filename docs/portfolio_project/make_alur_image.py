from pathlib import Path
from PIL import Image, ImageDraw, ImageFont
root = Path(r"C:\Users\Asus\OneDrive\Dokumen\Semester 6\MPTI\CashDig-4SR")
out = root / "docs" / "portfolio_project" / "alur_sistem_analisis_kebutuhan.png"
out.parent.mkdir(parents=True, exist_ok=True)
img = Image.new("RGB", (1900, 1200), "white")
d = ImageDraw.Draw(img)
try:
    title = ImageFont.truetype("arialbd.ttf", 38)
    head = ImageFont.truetype("arialbd.ttf", 26)
    font = ImageFont.truetype("arial.ttf", 22)
    small = ImageFont.truetype("arial.ttf", 19)
except Exception:
    title = head = font = small = ImageFont.load_default()

d.text((70, 50), "Alur Sistem dan Analisis Kebutuhan Fitur Cash-Dig / Sikasir-4SR", fill="#0B2545", font=title)
d.text((70, 100), "Bukti rancangan kontribusi analisis kebutuhan, alur menu-pesanan, modul admin, stok, dan laporan.", fill="#555555", font=font)

boxes = [
    (90, 220, 390, 370, "Customer / Kasir", ["Melihat menu", "Memilih meja", "Menambah item"]),
    (520, 220, 820, 370, "Halaman Menu", ["Kategori menu", "Harga dan stok", "Keranjang"]),
    (950, 220, 1250, 370, "Proses Order", ["Validasi item", "Hitung PPN", "Simpan pesanan"]),
    (1380, 220, 1680, 370, "Database", ["orders", "order_items", "menu_items"]),
    (950, 560, 1250, 710, "Dashboard Admin", ["Lihat pesanan", "Status proses", "Status selesai"]),
    (520, 560, 820, 710, "Barang & Stok", ["CRUD menu", "Update stok", "Status aktif"]),
    (90, 560, 390, 710, "Laporan", ["Riwayat transaksi", "Pendapatan", "Laba kotor"]),
]

def box(x1,y1,x2,y2,title_text, lines):
    d.rounded_rectangle([x1,y1,x2,y2], radius=18, fill="#F7FAFC", outline="#1F4D78", width=3)
    d.rectangle([x1,y1,x2,y1+46], fill="#E8EEF5", outline="#1F4D78", width=2)
    d.text((x1+18,y1+10), title_text, fill="#0B2545", font=head)
    yy = y1+64
    for line in lines:
        d.text((x1+22, yy), "- " + line, fill="#222222", font=font)
        yy += 30

def arrow(a,b,label=""):
    d.line([a,b], fill="#404040", width=4)
    ex,ey=b; sx,sy=a
    if ex > sx:
        d.polygon([(ex,ey),(ex-18,ey-10),(ex-18,ey+10)], fill="#404040")
    elif ex < sx:
        d.polygon([(ex,ey),(ex+18,ey-10),(ex+18,ey+10)], fill="#404040")
    elif ey > sy:
        d.polygon([(ex,ey),(ex-10,ey-18),(ex+10,ey-18)], fill="#404040")
    else:
        d.polygon([(ex,ey),(ex-10,ey+18),(ex+10,ey+18)], fill="#404040")
    if label:
        mx,my=(sx+ex)//2,(sy+ey)//2
        d.rounded_rectangle([mx-120,my-20,mx+120,my+20], radius=8, fill="white", outline="#D0D7DE")
        d.text((mx-105,my-12), label, fill="#333333", font=small)

for b in boxes:
    box(*b)

arrow((390,295),(520,295),"pilih menu")
arrow((820,295),(950,295),"checkout")
arrow((1250,295),(1380,295),"simpan data")
arrow((1530,370),(1100,560),"data order")
arrow((950,635),(820,635),"kelola stok")
arrow((520,635),(390,635),"rekap")
arrow((1100,560),(1100,370),"update status")

# requirement notes
notes_x, notes_y = 90, 870
d.rounded_rectangle([notes_x, notes_y, 1810, 1080], radius=18, fill="#FFF8E8", outline="#D8B65A", width=3)
d.text((notes_x+24, notes_y+22), "Kebutuhan fitur yang diturunkan dari alur sistem", fill="#7A5A00", font=head)
requirements = [
    "FR: tampilkan menu aktif, filter kategori, keranjang, submit pesanan, CRUD menu, validasi stok, status order, riwayat, dan laporan.",
    "NFR: keamanan halaman admin, integritas stok, kemudahan penggunaan, performa query, dan audit transaksi melalui timestamp.",
    "Front-end: halaman kasir/customer, dashboard admin, barang & stok, riwayat transaksi, laporan, dan pengaturan.",
    "Back-end: route Laravel, controller, model, transaksi database, autentikasi, dan relasi data.",
]
y = notes_y + 70
for req in requirements:
    d.text((notes_x+32, y), "- " + req, fill="#222222", font=font)
    y += 34

img.save(out)
print(out)
