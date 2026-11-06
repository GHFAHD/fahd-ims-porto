import './bootstrap';
import {
    createIcons,
    Boxes,
    LayoutDashboard,
    Package,
    ArrowLeftRight,
    FileBarChart,
    Settings,
    Menu,
    X,
    ChevronRight,
    ChevronDown,
    Bell,
    Building2,
    AlertTriangle,
    ArrowDownLeft,
    ArrowUpRight,
    Clock,
    Mail,
    Lock,
    LogOut,
    ShieldCheck,
    AlertCircle,
    CheckCircle2,
    ArrowRight,
    ArrowLeft,
    KeyRound,
    Plus,
    Search,
    RotateCcw,
    Pencil,
    Trash2,
    Tag,
    Truck,
    Scale,
    Save,
    SlidersHorizontal,
    Download,
    Printer,
} from 'lucide';

const initLucideIcons = () => {
    createIcons({
        icons: {
            Boxes,
            LayoutDashboard,
            Package,
            ArrowLeftRight,
            FileBarChart,
            Settings,
            Menu,
            X,
            ChevronRight,
            ChevronDown,
            Bell,
            Building2,
            AlertTriangle,
            ArrowDownLeft,
            ArrowUpRight,
            Clock,
            Mail,
            Lock,
            LogOut,
            ShieldCheck,
            AlertCircle,
            CheckCircle2,
            ArrowRight,
            ArrowLeft,
            KeyRound,
            Plus,
            Search,
            RotateCcw,
            Pencil,
            Trash2,
            Tag,
            Truck,
            Scale,
            Save,
            SlidersHorizontal,
            Download,
            Printer,
        },
    });
};

const initSidebarToggle = () => {
    const sidebar = document.getElementById('ims-sidebar');
    const backdrop = document.getElementById('ims-sidebar-backdrop');
    const toggles = document.querySelectorAll('[data-sidebar-toggle]');

    if (!sidebar || !backdrop) return;

    const toggleSidebar = () => {
        const isClosed = sidebar.classList.contains('-translate-x-full');
        sidebar.classList.toggle('-translate-x-full', !isClosed);
        backdrop.classList.toggle('hidden', !isClosed);
    };

    toggles.forEach((btn) => btn.addEventListener('click', toggleSidebar));
    backdrop.addEventListener('click', toggleSidebar);
};

const initDemoCredentialFill = () => {
    const demoButtons = document.querySelectorAll('[data-demo-email]');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    if (!demoButtons.length || !emailInput || !passwordInput) return;

    demoButtons.forEach((button) => {
        button.addEventListener('click', () => {
            emailInput.value = button.getAttribute('data-demo-email') || '';
            passwordInput.value = button.getAttribute('data-demo-password') || 'password';
            emailInput.focus();
        });
    });
};

const initStockMutationCalculator = () => {
    const productSelect = document.getElementById('mutation_product_id');
    const typeSelect = document.getElementById('mutation_type');
    const qtyInput = document.getElementById('mutation_quantity');

    if (!productSelect || !typeSelect || !qtyInput) return;

    const previewSku = document.getElementById('preview_sku');
    const previewCategory = document.getElementById('preview_category');
    const previewMinStock = document.getElementById('preview_min_stock');
    const previewPrevious = document.getElementById('preview_previous_stock');
    const previewCurrent = document.getElementById('preview_current_stock');
    const qtyLabel = document.getElementById('mutation_quantity_label');
    const typeHint = document.getElementById('mutation_type_hint');
    const qtyWarning = document.getElementById('mutation_quantity_warning');

    const updatePreview = () => {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const prevStock = parseInt(selectedOption?.getAttribute('data-stock') || '0', 10);
        const minStock = parseInt(selectedOption?.getAttribute('data-min-stock') || '0', 10);
        const unit = selectedOption?.getAttribute('data-unit') || 'unit';
        const sku = selectedOption?.getAttribute('data-sku') || '-';
        const category = selectedOption?.getAttribute('data-category') || '-';
        const type = typeSelect.value;
        const rawQty = parseInt(qtyInput.value || '0', 10);
        const qty = Number.isNaN(rawQty) ? 0 : rawQty;

        if (previewSku) previewSku.textContent = sku;
        if (previewCategory) previewCategory.textContent = category;
        if (previewMinStock) previewMinStock.textContent = `${minStock} ${unit}`;
        if (previewPrevious) previewPrevious.textContent = `${prevStock} ${unit}`;

        let nextStock = prevStock;
        if (type === 'in') {
            nextStock = prevStock + qty;
            if (qtyLabel) qtyLabel.innerHTML = 'Kuantitas Masuk <span class="text-red-500">*</span>';
            if (typeHint) typeHint.textContent = 'Menambahkan jumlah barang masuk ke saldo stok saat ini.';
            qtyInput.min = '1';
        } else if (type === 'out') {
            nextStock = prevStock - qty;
            if (qtyLabel) qtyLabel.innerHTML = 'Kuantitas Keluar <span class="text-red-500">*</span>';
            if (typeHint) typeHint.textContent = 'Mengurangi saldo stok (tidak boleh melebihi stok tersedia).';
            qtyInput.min = '1';
        } else {
            nextStock = qty;
            if (qtyLabel) qtyLabel.innerHTML = 'Stok Fisik Aktual (Opname) <span class="text-red-500">*</span>';
            if (typeHint) typeHint.textContent = 'Menetapkan nilai stok fisik baru dan menghitung selisih otomatis.';
            qtyInput.min = '0';
        }

        const isInvalidOut = type === 'out' && productSelect.value !== '' && qty > prevStock;

        if (qtyWarning) {
            qtyWarning.classList.toggle('hidden', !isInvalidOut);
        }

        if (previewCurrent) {
            previewCurrent.textContent = `${nextStock} ${unit}`;
            previewCurrent.className = 'font-mono font-bold text-base ' + (
                nextStock < 0
                    ? 'text-red-600'
                    : (nextStock <= minStock ? 'text-amber-600' : 'text-emerald-600')
            );
        }
    };

    productSelect.addEventListener('change', updatePreview);
    typeSelect.addEventListener('change', updatePreview);
    qtyInput.addEventListener('input', updatePreview);

    updatePreview();
};

document.addEventListener('DOMContentLoaded', () => {
    initLucideIcons();
    initSidebarToggle();
    initDemoCredentialFill();
    initStockMutationCalculator();
});
