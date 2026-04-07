<script>
// ── Simpan Draft / Estafet: password confirmation modal ────────────────
let _pendingAction = 'save';

const _modalConfig = {
    save: {
        title: 'Konfirmasi Simpan Draft',
        desc:  'Masukkan username dan password Anda untuk menyimpan.',
        btnText: 'Simpan',
        btnClass: 'bg-sky-500 hover:bg-sky-600',
    },
    handover: {
        title: 'Konfirmasi Estafet ke Shift 2',
        desc:  'Setelah diteruskan, data Shift 1 tidak dapat diubah lagi. Masukkan username dan password Anda untuk melanjutkan.',
        btnText: 'Estafet',
        btnClass: 'bg-amber-500 hover:bg-amber-600',
    },
    submit: {
        title: 'Konfirmasi Kirim Laporan',
        desc:  'Setelah dikirim, data tidak dapat diubah lagi. Masukkan username dan password Anda untuk melanjutkan.',
        btnText: 'Kirim Laporan',
        btnClass: 'bg-sky-500 hover:bg-sky-600',
    },
};

function openSaveModal()    { openConfirmModal('save'); }
function openConfirmModal(action) {
    if (action === 'handover') {
        const missing = getMissingCols(1);
        if (missing.size > 0) {
            showAlertModal(
                'Data Belum Lengkap',
                'Kolom Shift 1 berikut belum diisi lengkap:\n\u2022 ' + [...missing].join('\n\u2022 ') + '\n\nIsi semua data sebelum melanjutkan.'
            );
            return;
        }
    }
    _pendingAction = action;
    const cfg = _modalConfig[action];
    document.getElementById('save-modal-title').textContent   = cfg.title;
    document.getElementById('save-modal-desc').textContent    = cfg.desc;
    const btn = document.getElementById('save-modal-confirm');
    btn.textContent = cfg.btnText;
    btn.className   = `px-4 py-2 rounded-lg text-white text-sm font-medium disabled:opacity-50 ${cfg.btnClass}`;
    btn.disabled    = false;
    document.getElementById('save-modal-username').value = '';
    document.getElementById('save-modal-password').value = '';
    document.getElementById('save-modal-error').classList.add('hidden');
    // Show supervisor dropdown only for submit action
    const supRow = document.getElementById('supervisor-select-row');
    if (action === 'submit') {
        supRow.classList.remove('hidden');
        document.getElementById('save-modal-supervisor').value = '';
        document.getElementById('save-modal-supervisor-error').classList.add('hidden');
    } else {
        supRow.classList.add('hidden');
    }
    document.getElementById('save-modal').classList.remove('hidden');
    document.getElementById('save-modal-username').focus();
}

function closeSaveModal() {
    document.getElementById('save-modal').classList.add('hidden');
}

async function confirmSave() {
    const action   = _pendingAction;
    const username = document.getElementById('save-modal-username').value.trim();
    const password = document.getElementById('save-modal-password').value;
    const errEl    = document.getElementById('save-modal-error');
    const btn      = document.getElementById('save-modal-confirm');

    // Validate supervisor selection for submit action
    if (action === 'submit') {
        const supId = document.getElementById('save-modal-supervisor').value;
        const supErr = document.getElementById('save-modal-supervisor-error');
        if (!supId) {
            supErr.classList.remove('hidden');
            return;
        }
        supErr.classList.add('hidden');
    }

    if (!username || !password) {
        errEl.textContent = 'Username dan password harus diisi.';
        errEl.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Memeriksa...';
    errEl.classList.add('hidden');

    try {
        const res = await fetch('{{ route('laporan.verify-password') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                             ?? document.querySelector('input[name="_token"]')?.value,
            },
            body: JSON.stringify({ username, password }),
        });

        const data = await res.json();

        if (data.ok) {
            closeSaveModal();
            document.getElementById('save-action-input').value = action;
            if (action === 'submit') {
                document.getElementById('save-supervisor-input').value =
                    document.getElementById('save-modal-supervisor').value;
            }
            formDirty = false;
            document.getElementById('laporan-form').submit();
        } else {
            errEl.textContent = data.message ?? 'Username atau password salah.';
            errEl.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = _modalConfig[action].btnText;
        }
    } catch (e) {
        errEl.textContent = 'Terjadi kesalahan. Coba lagi.';
        errEl.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = _modalConfig[action].btnText;
    }
}

// Close modal on Enter key in password field
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('save-modal-password')?.addEventListener('keydown', e => {
        if (e.key === 'Enter') confirmSave();
    });
});

// Auto-calculate T = B + F when user types in a CFU input
document.addEventListener('input', function (e) {
    if (!e.target.classList.contains('cfu-input')) return;

    const loc = e.target.dataset.loc;
    const col = e.target.dataset.col;

    const bInput = document.querySelector(`[data-loc="${loc}"][data-col="${col}"][data-type="b"]`);
    const fInput = document.querySelector(`[data-loc="${loc}"][data-col="${col}"][data-type="f"]`);

    const b = parseFloat(bInput?.value) >= 0 ? parseFloat(bInput.value) : 0;
    const f = parseFloat(fInput?.value) >= 0 ? parseFloat(fInput.value) : 0;

    const tSpan = document.getElementById(`t-${loc}-${col}`);
    if (tSpan) {
        const hasValue = (bInput?.value !== '' || fInput?.value !== '');
        const tVal = b + f;
        tSpan.textContent  = hasValue ? (Number.isInteger(tVal) ? tVal : parseFloat(tVal.toPrecision(10))) : '-';
        tSpan.className    = `text-[11px] font-semibold ${hasValue ? 'text-gray-700' : 'text-gray-300'}`;
    }

    // Recalculate Kesimpulan for this location across all its inputs
    const konklusiCell = document.getElementById(`konklusi-${loc}`);
    if (konklusiCell) {
        let maxB = 0, maxF = 0, hasAny = false;
        document.querySelectorAll(`[data-loc="${loc}"][data-type="b"]`).forEach(inp => {
            if (inp.value !== '') { hasAny = true; maxB = Math.max(maxB, parseFloat(inp.value) || 0); }
        });
        document.querySelectorAll(`[data-loc="${loc}"][data-type="f"]`).forEach(inp => {
            if (inp.value !== '') { hasAny = true; maxF = Math.max(maxF, parseFloat(inp.value) || 0); }
        });
        const alertB  = konklusiCell.dataset.alertB  !== '' ? parseFloat(konklusiCell.dataset.alertB)  : null;
        const alertF  = konklusiCell.dataset.alertF  !== '' ? parseFloat(konklusiCell.dataset.alertF)  : null;
        const actionB = konklusiCell.dataset.actionB !== '' ? parseFloat(konklusiCell.dataset.actionB) : null;
        const actionF = konklusiCell.dataset.actionF !== '' ? parseFloat(konklusiCell.dataset.actionF) : null;
        let label = '—', cls = 'text-gray-300 text-[11px]';
        if (hasAny) {
            const isTMS = (actionB !== null && maxB >= actionB) || (actionF !== null && maxF >= actionF);
            const isAlert = !isTMS && ((alertB !== null && maxB >= alertB) || (alertF !== null && maxF >= alertF));
            if (isTMS)       { label = 'TMS';   cls = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-700'; }
            else if (isAlert){ label = 'Alert'; cls = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-yellow-100 text-yellow-700'; }
            else             { label = 'MS';    cls = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700'; }
        }
        konklusiCell.innerHTML = `<span class="${cls}">${label}</span>`;

        // Recalculate section conclusion
        const sectionId = e.target.dataset.sectionId;
        if (sectionId) recalcSectionKonklusi(sectionId);
    }
});

function recalcSectionKonklusi(sectionId) {
    const el = document.getElementById(`section-konklusi-${sectionId}`);
    if (!el) return;
    let hasTMS = false, hasAny = false;
    document.querySelectorAll(`.konklusi-cell[data-section-id="${sectionId}"]`).forEach(cell => {
        const span = cell.querySelector('span');
        if (!span) return;
        const txt = span.textContent.trim();
        if (txt === 'TMS' || txt === 'Alert' || txt === 'MS') hasAny = true;
        if (txt === 'TMS') hasTMS = true;
    });
    if (!hasAny) {
        el.innerHTML = '<span class="text-xs text-gray-400 italic">Belum ada data</span>';
    } else if (hasTMS) {
        el.innerHTML = '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 border border-red-200">Tidak Memenuhi Spesifikasi <span class="font-bold">(TMS)</span></span>';
    } else {
        el.innerHTML = '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 border border-green-200">Memenuhi Spesifikasi <span class="font-bold">(MS)</span></span>';
    }
}

// Deselectable analis radio (click same button again to clear)
function toggleAnalis(inkKey, field, value, btn) {
    const hidden = document.getElementById(`radio-val-${inkKey}-${field}`);
    if (!hidden) return;
    const isSelected = hidden.value === value;
    hidden.value = isSelected ? '' : value;
    const group = btn.parentElement;
    group.querySelectorAll('.inkubasi-radio-btn').forEach(b => {
        const active = !isSelected && b.dataset.value === value;
        b.classList.toggle('bg-sky-50', active);
        b.classList.toggle('border-sky-300', active);
        b.classList.toggle('text-sky-700', active);
        b.classList.toggle('border-gray-200', !active);
        b.classList.toggle('bg-white', !active);
        b.classList.toggle('text-gray-600', !active);
    });
    formDirty = true;
}

// Shift assignment toggle
function setAssignment(secId, col, shift) {
    const myShift = {{ $myShift }};
    const hidden = document.getElementById(`sa-${secId}-${col}`);
    if (hidden) hidden.value = shift;
    for (const s of [1, 2]) {
        const btn = document.getElementById(`sa-btn-${secId}-${col}-${s}`);
        if (!btn) continue;
        if (s === shift) {
            btn.className = 'px-1.5 py-0.5 text-[10px] rounded font-semibold transition-colors ' + (s === 1 ? 'bg-sky-500 text-white' : 'bg-amber-500 text-white');
        } else {
            btn.className = 'px-1.5 py-0.5 text-[10px] rounded font-semibold transition-colors bg-gray-100 text-gray-500 hover:bg-gray-200';
        }
    }
    // Toggle editability of column inputs based on assignment
    const isMine = (shift === myShift);
    document.querySelectorAll(`input[data-section-id="${secId}"][data-col="${col}"]`).forEach(inp => {
        inp.disabled = !isMine;
        inp.classList.toggle('bg-gray-100', !isMine);
        inp.classList.toggle('bg-white', isMine);
        if (!isMine) inp.value = '';
    });
    // Also toggle time inputs in the same column (JAM)
    const colCell = document.querySelectorAll(`input[name*="entries"][name*="[${col}]"][name*="start_time"]`);
    colCell.forEach(inp => {
        const row = inp.closest('tr');
        if (!row) return;
        const secInput = row.querySelector(`input[data-section-id="${secId}"]`);
        if (!secInput) return;
        inp.disabled = !isMine;
        inp.classList.toggle('bg-gray-100', !isMine);
        inp.classList.toggle('bg-white', isMine);
        if (!isMine) inp.value = '';
    });
    formDirty = true;
}

// Custom alert modal
function showAlertModal(title, msg) {
    document.getElementById('alert-modal-title').textContent = title;
    document.getElementById('alert-modal-msg').textContent = msg;
    document.getElementById('alert-modal').classList.remove('hidden');
}
function closeAlertModal() {
    document.getElementById('alert-modal').classList.add('hidden');
}

// Custom confirm modal
let _confirmCallback = null;
function showConfirmModal(title, msg, btnLabel, onConfirm) {
    document.getElementById('confirm-modal-title').textContent = title;
    document.getElementById('confirm-modal-msg').textContent = msg;
    document.getElementById('confirm-modal-ok').textContent = btnLabel;
    _confirmCallback = onConfirm;
    document.getElementById('confirm-modal').classList.remove('hidden');
}
function closeConfirmModal() {
    document.getElementById('confirm-modal').classList.add('hidden');
    _confirmCallback = null;
}
function doConfirm() {
    const cb = _confirmCallback;
    closeConfirmModal();
    if (cb) cb();
}

// Helper: returns Set of missing exposure labels for a given shift
function getMissingCols(checkShift) {
    const missing = new Set();
    document.querySelectorAll('input[id^="sa-"]').forEach(inp => {
        const m = inp.id.match(/^sa-(\d+)-(\d+)$/);
        if (!m) return;
        const secId = m[1], col = m[2];
        if (parseInt(inp.value) !== checkShift) return;
        const bInputs = document.querySelectorAll(`input[data-section-id="${secId}"][data-col="${col}"][data-type="b"]`);
        if (!bInputs.length) return;
        bInputs.forEach(bi => { if (!bi.dataset.optional && bi.value === '') missing.add('Exposure ' + col); });
    });
    return missing;
}

// Kirim Laporan: validate then open password modal
function openSubmitFlow() {
    const myShift = {{ $myShift }};
    const missing = getMissingCols(myShift);
    if (missing.size > 0) {
        showAlertModal(
            'Data Belum Lengkap',
            'Kolom Shift ' + myShift + ' berikut belum diisi lengkap:\n\u2022 ' + [...missing].join('\n\u2022 ') + '\n\nIsi semua data sebelum melanjutkan.'
        );
        return;
    }
    openConfirmModal('submit');
}

// Validate assigned columns before handover or submit
function validateAction(action) {
    const myShift = {{ $myShift }};
    const checkShift = action === 'handover' ? 1 : myShift;
    const missing = getMissingCols(checkShift);
    if (missing.size > 0) {
        showAlertModal(
            'Data Belum Lengkap',
            'Kolom Shift ' + checkShift + ' berikut belum diisi lengkap:\n\u2022 ' + [...missing].join('\n\u2022 ') + '\n\nIsi semua data sebelum melanjutkan.'
        );
        return false;
    }
    return true;
}

// Warn before navigating away if form has been changed
let formDirty = false;
document.getElementById('laporan-form')?.addEventListener('change', () => { formDirty = true; });
document.getElementById('laporan-form')?.addEventListener('submit', () => { formDirty = false; });
window.addEventListener('beforeunload', (e) => {
    if (formDirty) {
        e.preventDefault();
        e.returnValue = '';
    }
});
</script>
