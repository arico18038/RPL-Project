const formatRupiah = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(value);

const cart = new Map();
let bookPageIndex = 0;
let isBookAnimating = false;

function updateClock() {
    const clock = document.getElementById('jam-sekarang');
    if (!clock) {
        return;
    }

    clock.textContent = `${new Date().toLocaleTimeString('id-ID')} WIB`;
}

function getCartItems() {
    return Array.from(cart.values());
}

function updateTotals() {
    const subtotal = getCartItems().reduce((total, item) => total + item.price * item.quantity, 0);
    const discountType = document.getElementById('tipe-diskon')?.value ?? 'persen';
    const discountValue = Number(document.getElementById('nilai-diskon')?.value ?? 0);
    const discountAmount = discountType === 'persen'
        ? Math.round(subtotal * Math.min(discountValue, 100) / 100)
        : Math.min(discountValue, subtotal);
    const taxable = Math.max(subtotal - discountAmount, 0);
    const tax = Math.round(taxable * 0.11);
    const total = taxable + tax;

    const subtotalText = document.getElementById('subtotal-harga');
    const taxText = document.getElementById('ppn-harga');
    const totalText = document.getElementById('total-harga');

    if (subtotalText) subtotalText.textContent = formatRupiah(subtotal);
    if (taxText) taxText.textContent = formatRupiah(tax);
    if (totalText) totalText.textContent = formatRupiah(total);
}

function renderCart() {
    const list = document.getElementById('daftar-pesanan');
    const empty = document.getElementById('empty-cart-box');
    const inputs = document.getElementById('order-items-inputs');
    const payButton = document.getElementById('btn-bayar');
    const cartCount = document.getElementById('cart-count');

    if (!list || !empty || !inputs || !payButton) {
        return;
    }

    const items = getCartItems();
    const hasStockProblem = items.some((item) => item.quantity > item.stock);
    list.innerHTML = '';
    inputs.innerHTML = '';

    empty.style.display = items.length ? 'none' : 'grid';
    list.classList.toggle('active', items.length > 0);
    payButton.disabled = items.length === 0 || hasStockProblem;

    const itemCount = items.reduce((total, item) => total + item.quantity, 0);
    if (cartCount) {
        cartCount.textContent = `${itemCount} Item`;
        cartCount.classList.toggle('active', itemCount > 0);
    }

    items.forEach((item, index) => {
        const row = document.createElement('li');
        row.innerHTML = `
            <div class="cart-line-top">
                <div class="cart-item-name">${item.name}</div>
                <div class="qty-control">
                    <button type="button" data-decrease="${item.id}">−</button>
                    <strong>${item.quantity}</strong>
                    <button type="button" data-increase="${item.id}">+</button>
                </div>
            </div>
            <div class="cart-meta">${formatRupiah(item.price)} x ${item.quantity} | Stok: ${item.stock}</div>
            <div class="cart-line-bottom">
                <div class="cart-item-price">${formatRupiah(item.price * item.quantity)}</div>
                <button type="button" class="btn-hapus" data-remove="${item.id}">▱</button>
            </div>
        `;
        list.appendChild(row);

        inputs.insertAdjacentHTML('beforeend', `
            <input type="hidden" name="items[${index}][id]" value="${item.id}">
            <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
        `);
    });

    updateTotals();
}

function addItem(button) {
    const id = button.dataset.id;
    const item = cart.get(id) ?? {
        id,
        name: button.dataset.name,
        price: Number(button.dataset.price),
        stock: Number(button.dataset.stock),
        quantity: 0,
    };

    if (item.stock <= 0 || item.quantity >= item.stock) {
        alert(`Stok ${item.name} hanya tersisa ${item.stock}. Sesuaikan jumlah pesanan sebelum bayar.`);
        return;
    }

    item.quantity += 1;
    cart.set(id, item);
    renderCart();
}

function changeQuantity(id, direction) {
    const item = cart.get(id);
    if (!item) {
        return;
    }

    if (direction > 0 && item.quantity >= item.stock) {
        alert(`Stok ${item.name} hanya tersisa ${item.stock}.`);
        return;
    }

    item.quantity += direction;
    if (item.quantity <= 0) {
        cart.delete(id);
    } else {
        cart.set(id, item);
    }

    renderCart();
}

function filterProducts() {
    const search = document.getElementById('search-menu')?.value.toLowerCase() ?? '';
    const activeCategory = document.querySelector('.category-btn.active')?.dataset.category ?? 'semua';

    document.querySelectorAll('.product-card').forEach((card) => {
        const matchesSearch = card.dataset.name.includes(search);
        const matchesCategory = activeCategory === 'semua' || card.dataset.category === activeCategory;
        card.style.display = matchesSearch && matchesCategory ? '' : 'none';
    });

    document.querySelectorAll('.book-page').forEach((page) => {
        const visibleItems = page.querySelectorAll('.book-menu-item:not([style*="display: none"])').length;
        const matchesCategory = activeCategory === 'semua' || page.dataset.category === activeCategory;
        page.dataset.available = visibleItems > 0 && matchesCategory ? 'true' : 'false';
    });

    bookPageIndex = 0;
    updateBookPages();
}

function getAvailableBookPages() {
    return Array.from(document.querySelectorAll('.book-page'))
        .filter((page) => page.dataset.available !== 'false');
}

function updateBookPages() {
    const pages = getAvailableBookPages();
    const allPages = document.querySelectorAll('.book-page');
    const prevButton = document.querySelector('.book-nav.prev');
    const nextButton = document.querySelector('.book-nav.next');

    allPages.forEach((page) => {
        page.classList.remove('active', 'flipping-next', 'flipping-prev', 'enter-next', 'enter-prev');
        if (page.dataset.available === 'false') {
            page.style.display = 'none';
        } else {
            page.style.display = '';
        }
    });

    if (pages.length === 0) {
        if (prevButton) prevButton.disabled = true;
        if (nextButton) nextButton.disabled = true;
        return;
    }

    bookPageIndex = Math.max(0, Math.min(bookPageIndex, pages.length - 1));
    pages[bookPageIndex].classList.add('active');

    if (prevButton) prevButton.disabled = bookPageIndex === 0;
    if (nextButton) nextButton.disabled = bookPageIndex === pages.length - 1;
}

function turnBookPage(direction) {
    const pages = getAvailableBookPages();
    const targetIndex = bookPageIndex + direction;

    if (isBookAnimating || targetIndex < 0 || targetIndex >= pages.length) {
        return;
    }

    const currentPage = pages[bookPageIndex];
    const nextPage = pages[targetIndex];
    isBookAnimating = true;

    currentPage.classList.remove('active');
    currentPage.classList.add(direction > 0 ? 'flipping-next' : 'flipping-prev');
    nextPage.classList.add(direction > 0 ? 'enter-next' : 'enter-prev');

    setTimeout(() => {
        currentPage.classList.remove('flipping-next', 'flipping-prev');
        nextPage.classList.remove('enter-next', 'enter-prev');
        bookPageIndex = targetIndex;
        isBookAnimating = false;
        updateBookPages();
    }, 520);
}

document.addEventListener('DOMContentLoaded', () => {
    updateClock();
    setInterval(updateClock, 1000);

    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (localStorage.getItem('sidebar-collapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
        if (sidebarToggle) {
            sidebarToggle.setAttribute('aria-label', 'Tampilkan sidebar');
            sidebarToggle.textContent = '>';
        }
    }

    sidebarToggle?.addEventListener('click', () => {
        const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebar-collapsed', String(isCollapsed));
        sidebarToggle.setAttribute('aria-label', isCollapsed ? 'Tampilkan sidebar' : 'Ciutkan sidebar');
        sidebarToggle.textContent = isCollapsed ? '>' : '<';
    });

    document.querySelectorAll('.add-button').forEach((button) => {
        button.addEventListener('click', () => addItem(button));
    });

    document.getElementById('daftar-pesanan')?.addEventListener('click', (event) => {
        const removeId = event.target.dataset.remove;
        const increaseId = event.target.dataset.increase;
        const decreaseId = event.target.dataset.decrease;

        if (removeId) {
            cart.delete(removeId);
            renderCart();
        }

        if (increaseId) {
            changeQuantity(increaseId, 1);
        }

        if (decreaseId) {
            changeQuantity(decreaseId, -1);
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

    document.querySelector('.book-nav.prev')?.addEventListener('click', () => {
        turnBookPage(-1);
    });

    document.querySelector('.book-nav.next')?.addEventListener('click', () => {
        turnBookPage(1);
    });

    const menuModal = document.getElementById('menu-modal');
    const openMenuModal = document.getElementById('open-menu-modal');
    const closeMenuModal = document.getElementById('close-menu-modal');
    const cancelMenuModal = document.getElementById('cancel-menu-modal');
    const menuForm = document.getElementById('menu-form');
    const menuFormMethod = document.getElementById('menu-form-method');
    const menuModalTitle = document.getElementById('menu-modal-title');
    const menuModalDescription = document.getElementById('menu-modal-description');
    const deleteMenuForm = document.getElementById('delete-menu-form');
    const deleteMenuButton = document.getElementById('delete-menu-button');

    const setMenuModalMode = (mode, data = {}) => {
        if (!menuModal || !menuForm || !menuFormMethod) {
            return;
        }

        const isEdit = mode === 'edit';
        menuForm.action = isEdit ? data.updateUrl : menuForm.dataset.storeUrl;
        menuFormMethod.value = isEdit ? 'PUT' : 'POST';
        if (menuModalTitle) menuModalTitle.textContent = isEdit ? 'Edit Barang' : 'Tambah Barang Baru';
        if (menuModalDescription) {
            menuModalDescription.textContent = isEdit
                ? 'Ubah detail barang yang sudah tersimpan.'
                : 'Masukkan detail barang baru.';
        }

        menuForm.elements.name.value = data.name ?? '';
        menuForm.elements.category_id.value = data.categoryId ?? '';
        menuForm.elements.price.value = data.price ?? '';
        menuForm.elements.stock.value = data.stock ?? '100';
        menuForm.elements.description.value = data.description ?? '';
        menuForm.elements.image_url.value = data.imageUrl ?? '';
        menuForm.elements.is_available.value = data.isAvailable ?? '1';

        if (deleteMenuForm && deleteMenuButton) {
            deleteMenuForm.action = data.deleteUrl ?? '#';
            deleteMenuButton.hidden = !isEdit;
        }

        menuModal.hidden = false;
    };

    openMenuModal?.addEventListener('click', () => {
        setMenuModalMode('create');
    });

    document.querySelectorAll('.edit-menu-button').forEach((button) => {
        button.addEventListener('click', () => {
            setMenuModalMode('edit', {
                updateUrl: button.dataset.updateUrl,
                deleteUrl: button.dataset.deleteUrl,
                name: button.dataset.name,
                categoryId: button.dataset.categoryId,
                price: button.dataset.price,
                stock: button.dataset.stock,
                description: button.dataset.description,
                imageUrl: button.dataset.imageUrl,
                isAvailable: button.dataset.isAvailable,
            });
        });
    });

    closeMenuModal?.addEventListener('click', () => {
        menuModal.hidden = true;
    });

    cancelMenuModal?.addEventListener('click', () => {
        menuModal.hidden = true;
    });

    menuModal?.addEventListener('click', (event) => {
        if (event.target === menuModal) {
            menuModal.hidden = true;
        }
    });

    deleteMenuForm?.addEventListener('submit', (event) => {
        if (!confirm('Hapus barang ini dari daftar menu?')) {
            event.preventDefault();
        }
    });

    updateBookPages();

    document.getElementById('search-menu')?.addEventListener('input', filterProducts);
    document.getElementById('tipe-diskon')?.addEventListener('change', updateTotals);
    document.getElementById('nilai-diskon')?.addEventListener('input', updateTotals);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'F2') {
            event.preventDefault();
            document.getElementById('search-menu')?.focus();
        }

        if (event.key === 'F7') {
            event.preventDefault();
            document.getElementById('nilai-diskon')?.focus();
        }

        if (event.key === 'F9' && cart.size > 0) {
            event.preventDefault();
            document.getElementById('order-form')?.requestSubmit();
        }
    });
});
