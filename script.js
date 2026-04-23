// =========================================================
// BAGIAN 1: LOGIKA UNTUK HALAMAN PELANGGAN (CASH DIG-4SR)
// =========================================================

let keranjang = [];
let totalHarga = 0;
const BASE_URL = 'http://192.168.0.110:8000'; // Ganti IP ini jika berubah

document.addEventListener("DOMContentLoaded", function() {
    if (document.getElementById('container-menu')) {
        ambilDataMenu();
    }
});

// FUNGSI 1: MENGAMBIL DATA MENU DARI API LARAVEL
async function ambilDataMenu() {
    const containerMenu = document.getElementById('container-menu');
    try {
        const response = await fetch(`${BASE_URL}/api/menu`, {
            headers: { 'Accept': 'application/json' }
        });
        const hasil = await response.json();

        if (hasil.success) {
            const daftarMenu = hasil.data;
            containerMenu.innerHTML = ''; 

            daftarMenu.forEach(menu => {
                const pathGambar = menu.image_url 
                    ? `${BASE_URL}/images/menu/${menu.image_url}` 
                    : 'https://via.placeholder.com/150';

                containerMenu.innerHTML += `
                <div class="menu-card">
                    <img src="${pathGambar}" class="menu-img-style" style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;">
                    <h3>${menu.name}</h3>
                    <p>Rp ${parseInt(menu.price).toLocaleString('id-ID')}</p>
                    <button class="btn-pesan" onclick="tambahPesanan(${menu.id}, '${menu.name}', ${menu.price})">Tambah</button>
                </div>
                `;
            });
        }
    } catch (error) {
        console.error("Gagal mengambil menu:", error);
        containerMenu.innerHTML = '<p style="text-align:center; color:red;">Gagal memuat menu. Periksa koneksi ke server.</p>';
    }
}

// FUNGSI 2: LOGIKA KERANJANG
function tambahPesanan(id, nama, harga) {
    keranjang.push({ id: id, nama: nama, harga: harga });
    totalHarga += harga;
    
    if (document.getElementById('daftar-pesanan')) {
        updateTampilanKeranjang();
    }
    alert(nama + " ditambah!");
}

function updateTampilanKeranjang() {
    const daftarPesanan = document.getElementById('daftar-pesanan');
    const teksTotalHarga = document.getElementById('total-harga');
    if (!daftarPesanan) return; 

    daftarPesanan.innerHTML = '';
    if (keranjang.length === 0) {
        daftarPesanan.innerHTML = '<li class="empty-cart">Belum ada menu yang dipilih.</li>';
    } else {
        keranjang.forEach((item, index) => {
            daftarPesanan.innerHTML += `
                <li>
                    <span>${item.nama}</span>
                    <span>Rp ${item.harga.toLocaleString('id-ID')} 
                        <button class="btn-hapus" onclick="hapusPesanan(${index})">X</button>
                    </span>
                </li>`;
        });
    }
    teksTotalHarga.innerText = 'Rp ' + totalHarga.toLocaleString('id-ID');
}

function hapusPesanan(index) {
    totalHarga -= keranjang[index].harga;
    keranjang.splice(index, 1);
    updateTampilanKeranjang();
}

// FUNGSI 3: MENGIRIM PESANAN KE API LARAVEL
async function kirimPesanan() {
    if (keranjang.length === 0) return alert("Pilih menu dulu!");

    const dataKeDatabase = {
        table_id: 1, 
        total_price: totalHarga,
        note: "Pesanan dari Web",
        items: keranjang.map(item => ({
            id: item.id,       // menu_item_id
            quantity: 1,       // jumlah
            price: item.harga  
        }))
    };

    try {
        const response = await fetch(`${BASE_URL}/api/pesanan`, { // Endpoint POST Pesanan
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(dataKeDatabase)
        });

        const hasil = await response.json();

        if (response.ok) {
            alert("Pesanan Berhasil Masuk ke Database!");
            keranjang = [];
            totalHarga = 0;
            updateTampilanKeranjang();
            window.location.href = "#beranda";
        } else {
            console.error("Detail Error:", hasil);
            alert("Gagal: " + (hasil.message || "Cek Console"));
        }
    } catch (error) {
        console.error("Koneksi Error:", error);
        alert("Koneksi ke server terputus.");
    }
}


// =========================================================
// BAGIAN 2: LOGIKA UNTUK HALAMAN ADMIN (DIPERBAIKI)
// =========================================================

function loginAdmin() {
    const user = document.getElementById('username').value;
    const pass = document.getElementById('password').value;
    const errorMsg = document.getElementById('login-error');

    if (user === 'admin' && pass === '123') {
        document.getElementById('login-screen').style.display = 'none';
        document.getElementById('admin-dashboard').style.display = 'block';
        
        // Panggil fungsi untuk mengambil data pesanan dari Laravel
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

// FUNGSI MENGAMBIL DATA PESANAN DARI LARAVEL
async function tampilkanPesananDiAdmin() {
    const tabelBody = document.getElementById('tabel-pesanan-body');
    if (!tabelBody) return;

    try {
        // Menggunakan endpoint /api/orders sesuai kode Anda yang terakhir
        const response = await fetch(`${BASE_URL}/api/orders`, {
            headers: { 'Accept': 'application/json' }
        });
        const hasil = await response.json();

        if (hasil.success) {
            tabelBody.innerHTML = ''; // Kosongkan tabel
            
            if (hasil.data.length === 0) {
                tabelBody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Belum ada pesanan masuk.</td></tr>';
                return;
            }

            hasil.data.forEach(order => {
                // Tentukan warna label berdasarkan status
                const badgeColor = order.status === 'pending' ? '#ffc107' : '#28a745';
                
                // Gabungkan item pesanan. Jika order_items tidak ada, pakai fallback
                let detailItem = "Pesanan Web";
                if (order.order_items && order.order_items.length > 0) {
                    detailItem = order.order_items.map(item => 
                        `${item.menu_item ? item.menu_item.name : 'Menu'} (x${item.quantity})`
                    ).join(", ");
                } else if (order.note) {
                    detailItem = order.note;
                }

                tabelBody.innerHTML += `
                    <tr>
                        <td>Meja ${order.table_id || '-'}</td>
                        <td>${detailItem}</td>
                        <td>Rp ${parseInt(order.total_price).toLocaleString('id-ID')}</td>
                        <td><span class="badge" style="background:${badgeColor}; color:white; padding:5px 10px; border-radius:15px;">${order.status || 'pending'}</span></td>
                        <td>
                            <button class="btn-konfirmasi" onclick="updateStatus(${order.id}, 'completed')" style="background:#28a745; color:white; border:none; padding:5px 10px; border-radius:3px; cursor:pointer;">
                                Tandai Lunas
                            </button>
                        </td>
                    </tr>
                `;
            });
        } else {
            tabelBody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Data tidak ditemukan.</td></tr>';
        }
    } catch (error) {
        console.error("Gagal memuat data admin:", error);
        tabelBody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:red;">Gagal menghubungi server API.</td></tr>';
    }
}

// FUNGSI UPDATE STATUS PESANAN KE LARAVEL
async function updateStatus(idPesanan, statusBaru) {
    try {
        // Saya sesuaikan rutenya menjadi /api/orders agar sama dengan GET
        const response = await fetch(`${BASE_URL}/api/orders/${idPesanan}`, {
            method: 'PUT',
            headers: { 
                'Content-Type': 'application/json', 
                'Accept': 'application/json' 
            },
            body: JSON.stringify({ status: statusBaru })
        });

        if (response.ok) {
            tampilkanPesananDiAdmin(); // Refresh tabel otomatis
        } else {
            alert("Gagal memperbarui status ke server.");
        }
    } catch (error) {
        console.error("Error Updating Status:", error);
    }
}