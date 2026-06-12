const formatRupiah = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(value);

const cart = new Map();
let bookPageIndex = 0;
let isBookAnimating = false;
let activeReceipt = null;

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
    const taxRate = Number(document.getElementById('tax-rate')?.value ?? 11);
    const discountAmount = discountType === 'persen'
        ? Math.round(subtotal * Math.min(discountValue, 100) / 100)
        : Math.min(discountValue, subtotal);
    const taxable = Math.max(subtotal - discountAmount, 0);
    const tax = Math.round(taxable * taxRate / 100);
    const total = taxable + tax;

    const subtotalText = document.getElementById('subtotal-harga');
    const discountText = document.getElementById('diskon-harga');
    const taxText = document.getElementById('ppn-harga');
    const totalText = document.getElementById('total-harga');

    if (subtotalText) subtotalText.textContent = formatRupiah(subtotal);
    if (discountText) discountText.textContent = `- ${formatRupiah(discountAmount)}`;
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
                <button type="button" class="btn-hapus" data-remove="${item.id}" aria-label="Hapus ${item.name}">
                    <img src="/images/icon/Icon Hapus.png" alt="">
                </button>
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

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function getReceiptData(id) {
    const holder = document.getElementById(`receipt-data-${id}`);
    if (!holder) {
        return null;
    }

    try {
        return JSON.parse(holder.textContent);
    } catch (error) {
        return null;
    }
}

function receiptHtml(receipt) {
    const items = (receipt.items ?? []).map((item) => `
        <tr>
            <td>
                <strong>${escapeHtml(item.name)}</strong>
                <span>${Number(item.quantity)} x ${formatRupiah(Number(item.price ?? 0))}</span>
            </td>
            <td>${formatRupiah(Number(item.subtotal ?? 0))}</td>
        </tr>
    `).join('');
    const discountRow = Number(receipt.discountAmount ?? 0) > 0
        ? `<div><span>Diskon</span><strong>- ${formatRupiah(Number(receipt.discountAmount))}</strong></div>`
        : '';

    return `
        <article class="receipt-paper">
            <header>
                <h3>${escapeHtml(receipt.store?.name ?? 'Rumah Makan 4SR')}</h3>
                <p>${escapeHtml(receipt.store?.address ?? '')}</p>
                <p>${escapeHtml(receipt.store?.phone || receipt.store?.whatsapp || '')}</p>
            </header>

            <section class="receipt-meta">
                <div><span>No</span><strong>${escapeHtml(receipt.transactionCode)}</strong></div>
                <div><span>Tanggal</span><strong>${escapeHtml(receipt.date)}</strong></div>
                <div><span>Meja</span><strong>${escapeHtml(receipt.table)}</strong></div>
                <div><span>Kasir</span><strong>${escapeHtml(receipt.cashier)}</strong></div>
            </section>

            <table>
                <tbody>${items || '<tr><td colspan="2">Tidak ada item.</td></tr>'}</tbody>
            </table>

            <section class="receipt-total">
                <div><span>Subtotal</span><strong>${formatRupiah(Number(receipt.subtotal ?? 0))}</strong></div>
                ${discountRow}
                <div><span>Pajak</span><strong>${formatRupiah(Number(receipt.tax ?? 0))}</strong></div>
                <div class="grand-total"><span>Total</span><strong>${formatRupiah(Number(receipt.total ?? 0))}</strong></div>
                <div><span>Metode</span><strong>${escapeHtml(receipt.paymentMethod ?? 'Tunai')}</strong></div>
                <div><span>Status</span><strong>${escapeHtml(receipt.status ?? '-')}</strong></div>
            </section>

            <footer>Terima kasih sudah berkunjung.</footer>
        </article>
    `;
}

function showReceipt(id) {
    const receipt = getReceiptData(id);
    const modal = document.getElementById('receipt-modal');
    const preview = document.getElementById('receipt-preview');

    if (!receipt || !modal || !preview) {
        return;
    }

    activeReceipt = receipt;
    preview.innerHTML = receiptHtml(receipt);
    modal.hidden = false;
}

function printReceipt(receipt) {
    if (!receipt) {
        return;
    }

    const printWindow = window.open('', '_blank', 'width=420,height=720');
    if (!printWindow) {
        alert('Popup cetak diblokir. Izinkan popup browser untuk mencetak struk.');
        return;
    }

    printWindow.document.write(`
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <title>${escapeHtml(receipt.transactionCode)}</title>
            <style>
                * { box-sizing: border-box; }
                body { color: #111827; font-family: Arial, sans-serif; margin: 0; padding: 18px; }
                .receipt-paper { margin: 0 auto; max-width: 320px; }
                header { border-bottom: 1px dashed #9ca3af; padding-bottom: 12px; text-align: center; }
                h3 { font-size: 18px; margin: 0 0 6px; }
                p { color: #4b5563; font-size: 12px; margin: 2px 0; }
                .receipt-meta, .receipt-total { border-bottom: 1px dashed #9ca3af; padding: 12px 0; }
                .receipt-meta div, .receipt-total div { display: flex; font-size: 12px; justify-content: space-between; margin: 6px 0; gap: 16px; }
                .receipt-meta strong, .receipt-total strong { text-align: right; }
                table { border-bottom: 1px dashed #9ca3af; border-collapse: collapse; margin-top: 10px; width: 100%; }
                td { font-size: 12px; padding: 8px 0; vertical-align: top; }
                td:last-child { text-align: right; white-space: nowrap; }
                td span { color: #6b7280; display: block; margin-top: 3px; }
                .grand-total { font-size: 15px !important; font-weight: 800; }
                footer { font-size: 12px; padding-top: 14px; text-align: center; }
            </style>
        </head>
        <body>${receiptHtml(receipt)}</body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
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
    const sidebarToggleIcon = sidebarToggle?.querySelector('.sidebar-toggle-icon');
    const syncSidebarToggleIcon = (isCollapsed) => {
        if (!sidebarToggle || !sidebarToggleIcon) {
            return;
        }

        sidebarToggle.setAttribute('aria-label', isCollapsed ? 'Tampilkan sidebar' : 'Ciutkan sidebar');
        sidebarToggleIcon.src = isCollapsed ? sidebarToggle.dataset.showIcon : sidebarToggle.dataset.hideIcon;
    };

    if (localStorage.getItem('sidebar-collapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
        syncSidebarToggleIcon(true);
    } else {
        syncSidebarToggleIcon(false);
    }

    sidebarToggle?.addEventListener('click', () => {
        const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebar-collapsed', String(isCollapsed));
        syncSidebarToggleIcon(isCollapsed);
    });

    const recapType = document.getElementById('recap-type');
    const dailyInput = document.querySelector('[data-period-input="daily"]');
    const monthlyInput = document.querySelector('[data-period-input="monthly"]');
    const yearlyInput = document.querySelector('[data-period-input="yearly"]');
    const syncPeriodInputs = () => {
        const isDaily = recapType?.value === 'daily';
        const isYearly = recapType?.value === 'yearly';
        if (dailyInput) dailyInput.hidden = !isDaily;
        if (monthlyInput) monthlyInput.hidden = isYearly || isDaily;
        if (yearlyInput) yearlyInput.hidden = !isYearly;
    };

    syncPeriodInputs();
    recapType?.addEventListener('change', syncPeriodInputs);

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
            if (button.dataset.reportTab) {
                return;
            }

            document.querySelectorAll('.category-btn').forEach((item) => item.classList.remove('active'));
            button.classList.add('active');
            filterProducts();
        });
    });

    document.querySelectorAll('[data-report-tab]').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll('[data-report-tab]').forEach((item) => item.classList.remove('active'));
            document.querySelectorAll('[data-report-panel]').forEach((panel) => panel.classList.remove('active'));
            button.classList.add('active');
            document.querySelector(`[data-report-panel="${button.dataset.reportTab}"]`)?.classList.add('active');
        });
    });

    document.querySelectorAll('[data-copy-link]').forEach((button) => {
        button.addEventListener('click', async () => {
            const link = button.dataset.copyLink;
            try {
                await navigator.clipboard.writeText(link);
                button.textContent = 'Tersalin';
                setTimeout(() => {
                    button.textContent = 'Salin Link';
                }, 1200);
            } catch (error) {
                window.prompt('Salin link pelanggan:', link);
            }
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
    const tableModal = document.getElementById('table-modal');
    const openTableModal = document.getElementById('open-table-modal');
    const closeTableModal = document.getElementById('close-table-modal');
    const cancelTableModal = document.getElementById('cancel-table-modal');
    const deleteTableModal = document.getElementById('delete-table-modal');
    const openDeleteTableModal = document.getElementById('open-delete-table-modal');
    const closeDeleteTableModal = document.getElementById('close-delete-table-modal');
    const cancelDeleteTableModal = document.getElementById('cancel-delete-table-modal');
    const deleteTableForm = document.getElementById('delete-table-form');
    const deleteTableSelect = document.getElementById('delete-table-select');
    const categoryModal = document.getElementById('category-modal');
    const openCategoryModal = document.getElementById('open-category-modal');
    const closeCategoryModal = document.getElementById('close-category-modal');
    const cancelCategoryModal = document.getElementById('cancel-category-modal');
    const receiptModal = document.getElementById('receipt-modal');
    const closeReceiptModal = document.getElementById('close-receipt-modal');
    const cancelReceiptModal = document.getElementById('cancel-receipt-modal');
    const printReceiptModal = document.getElementById('print-receipt-modal');

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
        if (menuForm.elements.unit) menuForm.elements.unit.value = data.unit ?? 'Pcs';
        menuForm.elements.description.value = data.description ?? '';
        menuForm.elements.image_url.value = data.imageUrl ?? '';
        if (menuForm.elements.image_file) menuForm.elements.image_file.value = '';
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
                unit: button.dataset.unit,
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

    openTableModal?.addEventListener('click', () => {
        if (tableModal) {
            tableModal.hidden = false;
            document.getElementById('table-number')?.focus();
        }
    });

    closeTableModal?.addEventListener('click', () => {
        if (tableModal) tableModal.hidden = true;
    });

    cancelTableModal?.addEventListener('click', () => {
        if (tableModal) tableModal.hidden = true;
    });

    tableModal?.addEventListener('click', (event) => {
        if (event.target === tableModal) {
            tableModal.hidden = true;
        }
    });

    openDeleteTableModal?.addEventListener('click', () => {
        if (deleteTableModal) {
            deleteTableModal.hidden = false;
            deleteTableSelect?.focus();
        }
    });

    closeDeleteTableModal?.addEventListener('click', () => {
        if (deleteTableModal) deleteTableModal.hidden = true;
    });

    cancelDeleteTableModal?.addEventListener('click', () => {
        if (deleteTableModal) deleteTableModal.hidden = true;
    });

    deleteTableModal?.addEventListener('click', (event) => {
        if (event.target === deleteTableModal) {
            deleteTableModal.hidden = true;
        }
    });

    deleteTableForm?.addEventListener('submit', (event) => {
        if (!deleteTableSelect?.value) {
            event.preventDefault();
            alert('Pilih meja yang ingin dihapus.');
            return;
        }

        deleteTableForm.action = deleteTableSelect.value;
        if (!confirm('Hapus meja ini dari daftar meja?')) {
            event.preventDefault();
        }
    });

    openCategoryModal?.addEventListener('click', () => {
        if (categoryModal) {
            categoryModal.hidden = false;
            document.getElementById('category-name')?.focus();
        }
    });

    closeCategoryModal?.addEventListener('click', () => {
        if (categoryModal) categoryModal.hidden = true;
    });

    cancelCategoryModal?.addEventListener('click', () => {
        if (categoryModal) categoryModal.hidden = true;
    });

    categoryModal?.addEventListener('click', (event) => {
        if (event.target === categoryModal) {
            categoryModal.hidden = true;
        }
    });

    deleteMenuForm?.addEventListener('submit', (event) => {
        if (!confirm('Hapus barang ini dari daftar menu?')) {
            event.preventDefault();
        }
    });

    document.querySelectorAll('.receipt-view-button').forEach((button) => {
        button.addEventListener('click', () => showReceipt(button.dataset.receiptId));
    });

    document.querySelectorAll('.receipt-print-button').forEach((button) => {
        button.addEventListener('click', () => {
            printReceipt(getReceiptData(button.dataset.receiptId));
        });
    });

    closeReceiptModal?.addEventListener('click', () => {
        if (receiptModal) receiptModal.hidden = true;
    });

    cancelReceiptModal?.addEventListener('click', () => {
        if (receiptModal) receiptModal.hidden = true;
    });

    receiptModal?.addEventListener('click', (event) => {
        if (event.target === receiptModal) {
            receiptModal.hidden = true;
        }
    });

    printReceiptModal?.addEventListener('click', () => {
        printReceipt(activeReceipt);
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
