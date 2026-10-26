const layout = document.querySelector('.admin-layout');
const sidebarToggle = document.querySelector('.sidebar-toggle');
const sidebarBackdrop = document.querySelector('.sidebar-backdrop');
const searchInput = document.getElementById('bookingSearch');
const statusFilter = document.getElementById('bookingStatusFilter');
const rows = Array.from(document.querySelectorAll('#bookingsBody tr'));

function animateCards() {
    document.querySelectorAll('.booking-stat-card').forEach((card, index) => {
        card.style.animationDelay = `${index * 0.08}s`;
    });
}

function calculateStats(visibleRows) {
    const total = visibleRows.length;
    const confirmed = visibleRows.filter(row => row.dataset.status === 'confirmed').length;
    const pending = visibleRows.filter(row => row.dataset.status === 'pending').length;
    const cancelled = visibleRows.filter(row => row.dataset.status === 'cancelled').length;

    document.getElementById('totalBookings').textContent = total;
    document.getElementById('confirmedBookings').textContent = confirmed;
    document.getElementById('pendingBookings').textContent = pending;
    document.getElementById('cancelledBookings').textContent = cancelled;
}

function filterRows() {
    const search = (searchInput?.value || '').trim().toLowerCase();
    const selectedStatus = statusFilter?.value || 'all';

    const visibleRows = [];
    rows.forEach((row) => {
        const name = row.dataset.name || '';
        const phone = row.dataset.phone || '';
        const status = row.dataset.status || '';

        const matchesSearch = !search || name.includes(search) || phone.includes(search);
        const matchesStatus = selectedStatus === 'all' || status === selectedStatus;

        if (matchesSearch && matchesStatus) {
            row.classList.remove('hide-row');
            visibleRows.push(row);
        } else {
            row.classList.add('hide-row');
        }
    });

    calculateStats(visibleRows);
}

function initSidebarToggle() {
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
}

function animateRows() {
    rows.forEach((row, index) => {
        row.style.animation = `slideUpFadeIn 0.45s ease ${index * 0.07}s both`;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initSidebarToggle();
    animateCards();
    animateRows();
    filterRows();

    searchInput?.addEventListener('input', filterRows);
    statusFilter?.addEventListener('change', filterRows);
});
