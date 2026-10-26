// ============================================
// DOM ELEMENTS
// ============================================

const addProductModal = document.getElementById('addProductModal');
const productForm = document.getElementById('productForm');
const btnAdd = document.querySelector('.btn-add');
const layout = document.querySelector('.admin-layout');
const sidebarToggle = document.querySelector('.sidebar-toggle');
const sidebar = document.querySelector('.admin-sidebar');
const sidebarBackdrop = document.querySelector('.sidebar-backdrop');

// ============================================
// MODAL FUNCTIONS
// ============================================

function openModal() {
    if (!addProductModal) return;
    addProductModal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    if (!addProductModal) return;
    addProductModal.classList.remove('active');
    document.body.style.overflow = 'auto';
    
    // إعادة تعيين النموذج للإضافة الجديدة
    resetModalToAdd();
}

function resetModalToAdd() {
    if (!productForm) return;
    productForm.reset();
    document.querySelector('.modal-title').textContent = 'إضافة منتج جديد';
    document.querySelector('.btn-submit').textContent = 'إضافة المنتج';
    
    // تفريغ حقول المميزات
    document.querySelectorAll('input[name="features[]"]').forEach(input => input.value = '');
    
    // إزالة تحديد مربع "مميز"
    const featuredCheck = document.getElementById('featuredCheck');
    if(featuredCheck) featuredCheck.checked = false;

    // حذف hidden input للـ ID إن وجد
    const hiddenId = document.getElementById('productId');
    if (hiddenId) hiddenId.remove();
}

// إغلاق عند الضغط خارج المودال
if (addProductModal) {
    addProductModal.addEventListener('click', function (e) {
        if (e.target === this) closeModal();
    });
}

// إغلاق بـ ESC
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeModal();
});

// ============================================
// DELETE (Animation + PHP)
// ============================================

function handleDeleteAnimation(event) {
    const link = event.currentTarget;
    const row = link.closest('tr');
    const name = row.querySelector('.product-name').textContent;

    // إذا Cancel → نوقف
    if (!confirm(`هل تريد حذف "${name}" ؟`)) {
        event.preventDefault();
        return;
    }

    // نوقف الانتقال مؤقتًا
    event.preventDefault();

    // Animation
    row.style.animation = 'slideOutLeft 0.4s ease forwards';

    // بعد الأنيميشن نذهب إلى PHP
    setTimeout(() => {
        window.location.href = link.href;
    }, 400);
}

// ============================================
// EDIT (فتح Modal مع تحميل البيانات)
// ============================================

function handleEditAnimation(event) {
    event.preventDefault();

    const link = event.currentTarget;
    const url = new URL(link.href, window.location.origin);
    const productId = url.searchParams.get('edit');
    const row = link.closest('tr');

    if (!productId) {
        showToast('معرّف المنتج غير متاح', 'error');
        return;
    }

    // Animation
    row.style.animation = 'pulse 0.5s ease';

    // جلب بيانات المنتج من الـ PHP API
    setTimeout(() => {
        fetch(`get_product.php?id=${productId}`)
            .then(res => {
                if (!res.ok) throw new Error('فشل جلب البيانات');
                return res.json();
            })
            .then(product => {
                // ملء النموذج بالبيانات الأساسية
                document.getElementById('productName').value = product.name || '';
                document.getElementById('productCategory').value = product.category || '';
                document.getElementById('pricePerDay').value = product.price || '';
                document.getElementById('totalStock').value = product.quantity || '';
                document.getElementById('productDescription').value = product.description || '';

                // 👇 1. تحديد حالة "مميز" بناءً على البيانات القادمة من قاعدة البيانات 👇
                const featuredCheck = document.getElementById('featuredCheck');
                if (featuredCheck) {
                    featuredCheck.checked = (product.featured == 1);
                }

                // 👇 2. تعبئة حقول "المميزات" 👇
                const featureInputs = document.querySelectorAll('input[name="features[]"]');
                
                // أولاً: تفريغ الحقول لضمان عدم بقاء بيانات من منتج آخر
                featureInputs.forEach(input => input.value = '');
                
                // ثانياً: تعبئة الحقول بالمميزات الجديدة إن وجدت
                if (product.features && Array.isArray(product.features)) {
                    product.features.forEach((feature, index) => {
                        if (index < featureInputs.length) {
                            featureInputs[index].value = feature;
                        }
                    });
                }

                // إنشاء hidden input للـ ID
                let hiddenId = document.getElementById('productId');
                if (!hiddenId) {
                    hiddenId = document.createElement('input');
                    hiddenId.type = 'hidden';
                    hiddenId.id = 'productId';
                    hiddenId.name = 'id';
                    productForm.insertBefore(hiddenId, productForm.firstChild);
                }
                hiddenId.value = productId;

                // تغيير العنوان والزر
                document.querySelector('.modal-title').textContent = 'تعديل المنتج';
                document.querySelector('.btn-submit').textContent = 'حفظ التعديلات';

                // فتح المودال
                openModal();
            })
            .catch(err => {
                console.error('Error:', err);
                showToast('خطأ في جلب بيانات المنتج: ' + err.message, 'error');
            });
    }, 300);
}

// ============================================
// TOAST
// ============================================

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideInRight 0.4s ease';
    }, 10);

    setTimeout(() => {
        toast.classList.add('toast-close');
        setTimeout(() => toast.remove(), 400);
    }, 3000);
}

// ============================================
// FORM SUBMISSION
// ============================================

function handleAddProduct(event) {
    event.preventDefault();
    if (!productForm) return;
    // السماح بالإرسال العادي للـ form
    productForm.submit();
}

// ============================================
// EVENTS
// ============================================

// فتح المودال للإضافة
if (btnAdd) {
    btnAdd.addEventListener('click', openModal);
}

// ربط الأزرار
function attachButtons() {
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', handleDeleteAnimation);
    });

    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', handleEditAnimation);
    });
}

document.addEventListener('DOMContentLoaded', attachButtons);

// sidebar responsive
document.addEventListener('DOMContentLoaded', () => {
    if (!layout || !sidebarToggle) return;

    if (window.innerWidth <= 768) {
        layout.classList.remove('sidebar-open');
    }

    sidebarToggle.addEventListener('click', () => {
        layout.classList.toggle('sidebar-open');
    });

    sidebarBackdrop?.addEventListener('click', () => {
        layout.classList.remove('sidebar-open');
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            layout.classList.remove('sidebar-open');
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            layout.classList.remove('sidebar-open');
        }
    });
});

// ============================================
// ANIMATIONS ON LOAD
// ============================================

window.addEventListener('load', () => {
    document.querySelectorAll('.table-wrapper tbody tr').forEach((row, i) => {
        row.style.animation = `slideUpFadeIn 0.5s ease ${i * 0.1}s both`;
    });
});

// ============================================
// CHECK FOR EDIT MODE ON PAGE LOAD
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const editId = urlParams.get('edit');
    
    if (editId) {
        const editButton = document.querySelector(`.btn-edit[href*="edit=${editId}"]`);
        if (editButton) {
            editButton.click();
            
            // إزالة المعامل من الـ URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }
});


// ── Sidebar Mobile Drawer Toggle ──
// أضف هذا الكود في <script> في نهاية كل صفحة PHP
(function () {
    const toggle   = document.querySelector('.sidebar-toggle');
    const sidebar  = document.querySelector('.admin-sidebar');
    const backdrop = document.querySelector('.sidebar-backdrop');

    if (!toggle || !sidebar) return;

    function openSidebar() {
        sidebar.classList.add('open');
        backdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        backdrop.classList.remove('active');
        document.body.style.overflow = '';
    }

    toggle.addEventListener('click', function () {
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });

    backdrop.addEventListener('click', closeSidebar);

    // إغلاق بـ Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSidebar();
    });
})();