// =========================================================
// BAGIAN 1: LOGIKA UNTUK HALAMAN PELANGGAN (CASH DIG-4SR)
// =========================================================

let keranjang = [];
let totalHarga = 0;

// Jalankan fungsi ini saat halaman pertama kali dibuka
document.addEventListener("DOMContentLoaded", function() {
    if (document.getElementById('container-menu')) {
        ambilDataMenu();
    }
});

// FUNGSI 1: MENGAMBIL DATA MENU DARI API LARAVEL
async function ambilDataMenu() {
    const containerMenu = document.getElementById('container-menu');

    try {
        // Panggil API Laravel
        const response = await fetch('http://192.168.0.110:8000/api/menu', {
            headers: {
                'Accept': 'application/json'
            }
        });
        const hasil = await response.json();

        if (hasil.success) {
            const daftarMenu = hasil.data;
            containerMenu.innerHTML = ''; // Kosongkan kontainer sebelum diisi data

            // Looping data dari database
            daftarMenu.forEach(menu => {
                
                // Logika pengecekan gambar dari kode Anda
                const pathGambar = menu.image_url 
                    ? `http://192.168.0.110:8000/images/menu/${menu.image_url}` 
                    : 'https://via.placeholder.com/150'; // Gambar cadangan jika di database kosong

                // Memasukkan data ke dalam HTML
                containerMenu.innerHTML += `
                    <div class="menu-card">
                        <img src="${pathGambar}" alt="${menu.name}" class="menu-img-style" style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;">
                        
                        <h3>${menu.name}</h3>
                        <p>Rp ${parseInt(menu.price).toLocaleString('id-ID')}</p>
                        <button class="btn-pesan" onclick="tambahPesanan('${menu.name}', ${menu.price})">Tambah</button>
                    </div>
                `;
            });
        }
    } catch (error) {
        console.error("Gagal mengambil menu:", error);
        containerMenu.innerHTML = '<p style="text-align:center; color:red;">Gagal memuat menu. Pastikan server Laravel sudah menyala di IP yang benar.</p>';
    }
}

// FUNGSI 2: LOGIKA KERANJANG BELANJA
function tambahPesanan(namaMenu, harga) {
    keranjang.push({ nama: namaMenu, harga: harga });
    totalHarga += harga;
    
    // Cek apakah elemen daftar pesanan ada
    if (document.getElementById('daftar-pesanan')) {
        updateTampilanKeranjang();
    }
    alert(namaMenu + " berhasil ditambahkan ke pesanan!");
}

function hapusPesanan(index) {
    totalHarga -= keranjang[index].harga;
    keranjang.splice(index, 1);
    updateTampilanKeranjang();
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
                </li>
            `;
        });
    }
    teksTotalHarga.innerText = 'Rp ' + totalHarga.toLocaleString('id-ID');
}

// FUNGSI 3: MENGIRIM PESANAN KE DASHBOARD ADMIN (VIA LOCALSTORAGE)
function kirimPesanan() {
    if (keranjang.length === 0) {
        alert("Pilih menu terlebih dahulu sebelum mengirim pesanan!");
        return;
    }

    // Membuat objek data pesanan baru
    const pesananBaru = {
        id: Date.now(),
        meja: Math.floor(Math.random() * 10) + 1, // Nomor meja acak
        items: keranjang.map(i => i.nama).join(", "),
        total: totalHarga,
        status: "Menunggu"
    };

    // Menyimpan pesanan ke LocalStorage agar bisa dibaca oleh halaman Admin
    let semuaPesanan = JSON.parse(localStorage.getItem('databasePesanan')) || [];
    semuaPesanan.push(pesananBaru);
    localStorage.setItem('databasePesanan', JSON.stringify(semuaPesanan));

    alert("Pesanan berhasil dikirim ke dapur! Silakan siapkan pembayaran Anda di kasir.");
    
    // Reset keranjang
    keranjang = [];
    totalHarga = 0;
    updateTampilanKeranjang();
    window.location.href = "#beranda";
}


// =========================================================
// BAGIAN 2: LOGIKA UNTUK HALAMAN ADMIN
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
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';
    document.getElementById('login-error').style.display = 'none';
}

function tampilkanPesananDiAdmin() {
    const tabelBody = document.getElementById('tabel-pesanan-body');
    if (!tabelBody) return;

    // Ambil data dari LocalStorage
    const semuaPesanan = JSON.parse(localStorage.getItem('databasePesanan')) || [];
    tabelBody.innerHTML = '';

    semuaPesanan.forEach((p, index) => {
        let warnaBadge = p.status === "Menunggu" ? "#ffc107" : (p.status === "Diproses" ? "#17a2b8" : "#28a745");
        
        tabelBody.innerHTML += `
            <tr>
                <td>Meja ${p.meja}</td>
                <td>${p.items}</td>
                <td>Rp ${p.total.toLocaleString('id-ID')}</td>
                <td><span class="badge" style="background:${warnaBadge}; color:white; padding:5px 10px; border-radius:15px;">${p.status}</span></td>
                <td>
                    <button class="btn-status" onclick="updateStatus(${index}, 'Diproses')" style="background:#17a2b8; color:white; border:none; padding:5px; border-radius:3px; cursor:pointer;">Masak</button>
                    <button class="btn-status" onclick="updateStatus(${index}, 'Selesai')" style="background:#28a745; color:white; border:none; padding:5px; border-radius:3px; cursor:pointer;">Selesai</button>
                    <button class="btn-danger" onclick="hapusData(${index})" style="background:#dc3545; color:white; border:none; padding:5px; border-radius:3px; cursor:pointer;">Hapus</button>
                </td>
            </tr>
        `;
    });
}

function updateStatus(index, statusBaru) {
    let semuaPesanan = JSON.parse(localStorage.getItem('databasePesanan'));
    semuaPesanan[index].status = statusBaru;
    localStorage.setItem('databasePesanan', JSON.stringify(semuaPesanan));
    tampilkanPesananDiAdmin();
}

function hapusData(index) {
    if(confirm("Yakin ingin menghapus pesanan ini?")) {
        let semuaPesanan = JSON.parse(localStorage.getItem('databasePesanan'));
        semuaPesanan.splice(index, 1);
        localStorage.setItem('databasePesanan', JSON.stringify(semuaPesanan));
        tampilkanPesananDiAdmin();
    }
}