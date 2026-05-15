// =========================================================
// KONFIGURASI
// =========================================================

let keranjang = [];
let totalHarga = 0;
let semuaMenu = [];
let kategoriAktif = 'semua';

const BASE_URL = 'http://192.168.0.104:8000';

// Data contoh agar tampilan tetap muncul saat API belum aktif
const menuFallback = [
    {
        id: 1,
        name: 'Mie Goreng Telur',
        price: 12000,
        category: 'Makanan',
        code: 'BRG-051',
        stock: 120,
        image_url: 'https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&w=800&q=80'
    },
    {
        id: 2,
        name: 'Mie Goreng Telur + Sosis + Bakso',
        price: 18000,
        category: 'Makanan',
        code: 'BRG-052',
        stock: 33,
        image_url: 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=800&q=80'
    },
    {
        id: 3,
        name: 'Roti Bakar Madu Rasa',
        price: 15000,
        category: 'Makanan',
        code: 'BRG-083',
        stock: 32,
        image_url: 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?auto=format&fit=crop&w=800&q=80'
    },
    {
        id: 4,
        name: 'Martabak Manis Coklat',
        price: 15000,
        category: 'Makanan',
        code: 'BRG-041',
        stock: 3,
        image_url: 'https://images.unsplash.com/photo-1616690710400-a16d146927c5?auto=format&fit=crop&w=800&q=80'
    },
    {
        id: 5,
        name: 'Air Mineral 600ml',
        price: 5000,
        category: 'Minuman',
        code: 'BRG-001',
        stock: 120,
        image_url: 'https://images.unsplash.com/photo-1564419320461-6870880221ad?auto=format&fit=crop&w=800&q=80'
    },
    {
        id: 6,
        name: 'Es Teh Jumbo',
        price: 8000,
        category: 'Minuman',
        code: 'BRG-002',
        stock: 81,
        image_url: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=800&q=80'
    },
    {
        id: 7,
        name: 'Ocha 500ml',
        price: 8000,
        category: 'Minuman',
        code: 'BRG-003',
        stock: 32,
        image_url: 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?auto=format&fit=crop&w=800&q=80'
    }
];

document.addEventListener("DOMContentLoaded", function () {
    jalankanJam();

    if (document.getElementById('container-menu')) {
        ambilDataMenu();
        aktifkanPencarian();
    }
});

// =========================================================
// JAM
// =========================================================

function jalankanJam() {
    const jamElement = document.getElementById('jam-sekarang');

    if (!jamElement) return;

    setInterval(() => {
        const sekarang = new Date();
        jamElement.innerText = sekarang.toLocaleTimeString('id-ID');
    }, 1000);
}

// =========================================================
// MENU PRODUK
// =========================================================

async function ambilDataMenu() {
    const containerMenu = document.getElementById('container-menu');

    try {
        const response = await fetch(`${BASE_URL}/api/menu`, {
            headers: {
                'Accept': 'application/json'
            }
        });

        const hasil = await response.json();

        if (hasil.success) {
            semuaMenu = hasil.data.map((menu, index) => ({
                ...menu,
                category: menu.category || menu.kategori || tentukanKategori(menu.name),
                code: menu.code || `BRG-${String(index + 1).padStart(3, '0')}`,
                stock: menu.stock || menu.stok || 0
            }));

            renderMenu();
        } else {
            semuaMenu = menuFallback;
            renderMenu();
        }

    } catch (error) {
        console.error("Gagal mengambil menu:", error);
        semuaMenu = menuFallback;
        renderMenu();
    }
}

function renderMenu() {
    const containerMenu = document.getElementById('container-menu');
    const keyword = document.getElementById('search-menu')?.value.toLowerCase() || '';

    let dataMenu = semuaMenu.filter(menu => {
        const nama = menu.name.toLowerCase();
        const kategori = String(menu.category || '').toLowerCase();

        const cocokKeyword = nama.includes(keyword);
        const cocokKategori =
            kategoriAktif === 'semua' ||
            kategori.includes(kategoriAktif);

        return cocokKeyword && cocokKategori;
    });

    containerMenu.innerHTML = '';

    if (dataMenu.length === 0) {
        containerMenu.innerHTML = `
            <p class="loading-text">Menu tidak ditemukan.</p>
        `;
        return;
    }

    dataMenu.forEach(menu => {
        const pathGambar = getGambarMenu(menu);
        const kategori = menu.category || tentukanKategori(menu.name);
        const kode = menu.code || `BRG-${menu.id}`;
        const stok = menu.stock || menu.stok || 0;

        containerMenu.innerHTML += `
            <div class="product-card">
                <div class="product-image">
                    <img src="${pathGambar}" alt="${menu.name}">
                    <span class="product-badge">${kategori}</span>
                </div>

                <div class="product-info">
                    <h3>${menu.name}</h3>
                    <p class="product-code">${kode}</p>

                    <div class="product-bottom">
                        <div>
                            <p class="product-price">Rp ${parseInt(menu.price).toLocaleString('id-ID')}</p>
                            <p class="product-stock">Stok: ${stok}</p>
                        </div>

                        <button 
                            class="add-button"
                            onclick='tambahPesanan(${menu.id}, ${JSON.stringify(menu.name)}, ${menu.price})'>
                            +
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
}

function getGambarMenu(menu) {
    if (!menu.image_url) {
        return 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80';
    }

    if (String(menu.image_url).startsWith('http')) {
        return menu.image_url;
    }

    return `${BASE_URL}/images/menu/${menu.image_url}`;
}

function tentukanKategori(namaMenu) {
    const nama = namaMenu.toLowerCase();

    if (
        nama.includes('air') ||
        nama.includes('teh') ||
        nama.includes('kopi') ||
        nama.includes('ocha') ||
        nama.includes('jus') ||
        nama.includes('minum')
    ) {
        return 'Minuman';
    }

    return 'Makanan';
}

function filterKategori(kategori, button) {
    kategoriAktif = kategori;

    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    button.classList.add('active');
    renderMenu();
}

function aktifkanPencarian() {
    const inputSearch = document.getElementById('search-menu');

    if (!inputSearch) return;

    inputSearch.addEventListener('input', renderMenu);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'F2') {
            event.preventDefault();
            inputSearch.focus();
        }

        if (event.key === 'F9') {
            event.preventDefault();
            kirimPesanan();
        }
    });
}

// =========================================================
// KERANJANG
// =========================================================

function tambahPesanan(id, nama, harga) {
    const itemSudahAda = keranjang.find(item => item.id === id);

    if (itemSudahAda) {
        itemSudahAda.quantity += 1;
    } else {
        keranjang.push({
            id: id,
            nama: nama,
            harga: harga,
            quantity: 1
        });
    }

    updateTampilanKeranjang();
}

function updateTampilanKeranjang() {
    const daftarPesanan = document.getElementById('daftar-pesanan');
    const emptyCartBox = document.getElementById('empty-cart-box');
    const subtotalHarga = document.getElementById('subtotal-harga');
    const ppnHarga = document.getElementById('ppn-harga');
    const totalHargaElement = document.getElementById('total-harga');
    const btnBayar = document.getElementById('btn-bayar');

    if (!daftarPesanan) return;

    daftarPesanan.innerHTML = '';

    totalHarga = keranjang.reduce((total, item) => {
        return total + item.harga * item.quantity;
    }, 0);

    if (keranjang.length === 0) {
        daftarPesanan.classList.remove('active');

        if (emptyCartBox) emptyCartBox.style.display = 'grid';
        if (btnBayar) btnBayar.disabled = true;
    } else {
        daftarPesanan.classList.add('active');

        if (emptyCartBox) emptyCartBox.style.display = 'none';
        if (btnBayar) btnBayar.disabled = false;

        keranjang.forEach((item, index) => {
            daftarPesanan.innerHTML += `
                <li>
                    <div>
                        <p class="cart-item-name">${item.nama}</p>
                        <p>${item.quantity} x Rp ${item.harga.toLocaleString('id-ID')}</p>
                        <button class="btn-hapus" onclick="hapusPesanan(${index})">Hapus</button>
                    </div>

                    <p class="cart-item-price">
                        Rp ${(item.harga * item.quantity).toLocaleString('id-ID')}
                    </p>
                </li>
            `;
        });
    }

    const diskon = hitungDiskon(totalHarga);
    const subtotalSetelahDiskon = Math.max(totalHarga - diskon, 0);
    const ppn = Math.round(subtotalSetelahDiskon * 0.11);
    const totalAkhir = subtotalSetelahDiskon + ppn;

    if (subtotalHarga) subtotalHarga.innerText = 'Rp ' + totalHarga.toLocaleString('id-ID');
    if (ppnHarga) ppnHarga.innerText = 'Rp ' + ppn.toLocaleString('id-ID');
    if (totalHargaElement) totalHargaElement.innerText = 'Rp ' + totalAkhir.toLocaleString('id-ID');
}

function hitungDiskon(subtotal) {
    const tipeDiskon = document.getElementById('tipe-diskon')?.value || 'persen';
    const nilaiDiskon = parseInt(document.getElementById('nilai-diskon')?.value || 0);

    if (tipeDiskon === 'persen') {
        return Math.round(subtotal * nilaiDiskon / 100);
    }

    return nilaiDiskon;
}

function hapusPesanan(index) {
    if (keranjang[index].quantity > 1) {
        keranjang[index].quantity -= 1;
    } else {
        keranjang.splice(index, 1);
    }

    updateTampilanKeranjang();
}

function kosongkanKeranjang() {
    keranjang = [];
    totalHarga = 0;
    updateTampilanKeranjang();
}

// =========================================================
// KIRIM PESANAN
// =========================================================

async function kirimPesanan() {
    if (keranjang.length === 0) {
        alert("Keranjang masih kosong!");
        return;
    }

    const diskon = hitungDiskon(totalHarga);
    const subtotalSetelahDiskon = Math.max(totalHarga - diskon, 0);
    const ppn = Math.round(subtotalSetelahDiskon * 0.11);
    const totalAkhir = subtotalSetelahDiskon + ppn;

    const dataKeDatabase = {
        table_id: 1,
        total_price: totalAkhir,
        note: "Pesanan dari Kasir Web",
        items: keranjang.map(item => ({
            id: item.id,
            quantity: item.quantity,
            price: item.harga
        }))
    };

    try {
        const response = await fetch(`${BASE_URL}/api/pesanan`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(dataKeDatabase)
        });

        const hasil = await response.json();

        if (response.ok) {
            alert("Pembayaran berhasil dan pesanan masuk!");

            keranjang = [];
            totalHarga = 0;
            updateTampilanKeranjang();
        } else {
            console.error("Detail Error:", hasil);
            alert("Gagal mengirim pesanan: " + (hasil.message || "Cek console browser"));
        }

    } catch (error) {
        console.error("Koneksi Error:", error);
        alert("Koneksi ke server terputus.");
    }
}

// =========================================================
// ADMIN
// =========================================================

function loginAdmin() {
    const user = document.getElementById('username').value;
    const pass = document.getElementById('password').value;
    const errorMsg = document.getElementById('login-error');

    if (user === 'admin' && pass === '123') {
        document.getElementById('login-screen').style.display = 'none';
        document.getElementById('admin-dashboard').style.display = 'block';

        tampilkanPesananDiAdmin();
    } else {
        errorMsg.style.display = 'block';
    }
}

function logoutAdmin() {
    document.getElementById('admin-dashboard').style.display = 'none';
    document.getElementById('login-screen').style.display = 'block';

    document.getElementById('username').value = 'admin';
    document.getElementById('password').value = '123';
    document.getElementById('login-error').style.display = 'none';
}

async function tampilkanPesananDiAdmin() {
    const tabelBody = document.getElementById('tabel-pesanan-body');

    if (!tabelBody) return;

    try {
        const response = await fetch(`${BASE_URL}/api/orders`, {
            headers: {
                'Accept': 'application/json'
            }
        });

        const hasil = await response.json();

        if (hasil.success) {
            tabelBody.innerHTML = '';

            if (hasil.data.length === 0) {
                tabelBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="table-empty">Belum ada pesanan masuk.</td>
                    </tr>
                `;
                return;
            }

            hasil.data.forEach(order => {
                let detailItem = "Pesanan Web";

                if (order.order_items && order.order_items.length > 0) {
                    detailItem = order.order_items.map(item =>
                        `${item.menu_item ? item.menu_item.name : 'Menu'} (x${item.quantity})`
                    ).join(", ");
                } else if (order.note) {
                    detailItem = order.note;
                }

                const status = order.status || 'pending';
                const badgeClass = status === 'completed' || status === 'paid'
                    ? 'completed'
                    : 'pending';

                tabelBody.innerHTML += `
                    <tr>
                        <td>Meja ${order.table_id || '-'}</td>
                        <td>${detailItem}</td>
                        <td>Rp ${parseInt(order.total_price).toLocaleString('id-ID')}</td>
                        <td>
                            <span class="badge ${badgeClass}">
                                ${status}
                            </span>
                        </td>
                        <td>
                            <button class="btn-konfirmasi" onclick="updateStatus(${order.id}, 'completed')">
                                Tandai Lunas
                            </button>
                        </td>
                    </tr>
                `;
            });

        } else {
            tabelBody.innerHTML = `
                <tr>
                    <td colspan="5" class="table-empty">Data tidak ditemukan.</td>
                </tr>
            `;
        }

    } catch (error) {
        console.error("Gagal memuat data admin:", error);

        tabelBody.innerHTML = `
            <tr>
                <td colspan="5" class="table-empty" style="color: red;">
                    Gagal menghubungi server API.
                </td>
            </tr>
        `;
    }
}

async function updateStatus(idPesanan, statusBaru) {
    try {
        const response = await fetch(`${BASE_URL}/api/orders/${idPesanan}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status: statusBaru
            })
        });

        if (response.ok) {
            tampilkanPesananDiAdmin();
            alert("Status pesanan berhasil diperbarui!");
        } else {
            alert("Gagal memperbarui status ke server.");
        }

    } catch (error) {
        console.error("Error Updating Status:", error);
        alert("Terjadi kesalahan saat memperbarui status.");
    }
}