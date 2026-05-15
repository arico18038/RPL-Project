const formatRupiah = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(value);

const cart = new Map();

function updateClock() {
    const clock = document.getElementById('jam-sekarang');
    if (clock) {
        clock.textContent = new Date().toLocaleTimeString('id-ID');
    }
}

function renderCart() {
    const list = document.getElementById('daftar-pesanan');
    const empty = document.getElementById('empty-cart-box');
    const inputs = document.getElementById('order-items-inputs');
    const payButton = document.getElementById('btn-bayar');

    if (!list || !empty || !inputs || !payButton) {
        return;
    }

    list.innerHTML = '';
    inputs.innerHTML = '';

    const items = Array.from(cart.values());
    empty.style.display = items.length ? 'none' : 'grid';
    list.classList.toggle('active', items.length > 0);
    payButton.disabled = items.length === 0;

    items.forEach((item, index) => {
        const row = document.createElement('li');
        row.innerHTML = `
            <div>
                <div class="cart-item-name">${item.name}</div>
                <div>${item.quantity} x ${formatRupiah(item.price)}</div>
                <button type="button" class="btn-hapus" data-remove="${item.id}">Hapus</button>
            </div>
            <div class="cart-item-price">${formatRupiah(item.price * item.quantity)}</div>
        `;
        list.appendChild(row);

        inputs.insertAdjacentHTML('beforeend', `
            <input type="hidden" name="items[${index}][id]" value="${item.id}">
            <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
        `);
    });

    updateTotals();
}

function updateTotals() {
    const subtotal = Array.from(cart.values()).reduce((total, item) => total + item.price * item.quantity, 0);
    const total = subtotal;

    document.getElementById('subtotal-harga').textContent = formatRupiah(subtotal);
    document.getElementById('total-harga').textContent = formatRupiah(total);
}

function filterProducts() {
    const search = document.getElementById('search-menu')?.value.toLowerCase() ?? '';
    const activeCategory = document.querySelector('.category-btn.active')?.dataset.category ?? 'semua';

    document.querySelectorAll('.product-card').forEach((card) => {
        const matchesSearch = card.dataset.name.includes(search);
        const matchesCategory = activeCategory === 'semua' || card.dataset.category === activeCategory;
        card.style.display = matchesSearch && matchesCategory ? '' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', () => {
    updateClock();
    setInterval(updateClock, 1000);

    document.querySelectorAll('.add-button').forEach((button) => {
        button.addEventListener('click', () => {
            const id = button.dataset.id;
            const item = cart.get(id) ?? {
                id,
                name: button.dataset.name,
                price: Number(button.dataset.price),
                quantity: 0,
            };

            item.quantity += 1;
            cart.set(id, item);
            renderCart();
        });
    });

    document.getElementById('daftar-pesanan')?.addEventListener('click', (event) => {
        const removeId = event.target.dataset.remove;
        if (removeId) {
            cart.delete(removeId);
            renderCart();
        }
    });

    document.getElementById('btn-clear-cart')?.addEventListener('click', () => {
        cart.clear();
        renderCart();
    });

    document.querySelectorAll('.category-btn').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.category-btn').forEach((item) => item.classList.remove('active'));
            button.classList.add('active');
            filterProducts();
        });
    });

    document.getElementById('search-menu')?.addEventListener('input', filterProducts);
    document.getElementById('tipe-diskon')?.addEventListener('change', updateTotals);
    document.getElementById('nilai-diskon')?.addEventListener('input', updateTotals);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'F2') {
            event.preventDefault();
            document.getElementById('search-menu')?.focus();
        }

        if (event.key === 'F9' && cart.size > 0) {
            event.preventDefault();
            document.getElementById('order-form')?.requestSubmit();
        }
    });
});
