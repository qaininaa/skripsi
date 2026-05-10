<script>
// ── Admin: duplikat / hapus section via fetch (avoid nested form) ───────
function adminSectionAction(method, url) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content
                ?? document.querySelector('input[name="_token"]')?.value;
    fetch(url, {
        method: method,
        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
    })
    .then(async r => {
        const payload = await r.json().catch(() => ({}));
        if (!r.ok || payload?.ok === false) {
            const msg = payload?.message ?? 'Aksi gagal diproses.';
            if (typeof showAlertModal === 'function') {
                showAlertModal('Aksi Gagal', msg);
            } else {
                alert(msg);
            }
            return;
        }
        window.location.reload();
    })
    .catch(() => {
        if (typeof showAlertModal === 'function') {
            showAlertModal('Terjadi Kesalahan', 'Tidak bisa menghubungi server. Silakan coba lagi.');
        } else {
            alert('Tidak bisa menghubungi server. Silakan coba lagi.');
        }
    });
}

// ── Handover / Finish Monitoring modal ─────────────────────────────────
function _hmUpdateConfirmBtn() {
    const action   = document.querySelector('input[name="hm-action"]:checked')?.value;
    const username = document.getElementById('hm-username').value.trim();
    const password = document.getElementById('hm-password').value;
    document.getElementById('hm-confirm').disabled = !(action && username && password);
}

function openHandoverModal() {
    // Reset state
    document.querySelectorAll('input[name="hm-action"]').forEach(r => r.checked = false);
    // Pre-select default action (handover)
    const defaultRadio = document.querySelector('input[name="hm-action"][value="handover"]');
    if (defaultRadio) defaultRadio.checked = true;
    document.getElementById('hm-target-row')?.classList.add('hidden');
    document.getElementById('hm-analyst-error')?.classList.add('hidden');
    document.getElementById('hm-username').value = '';
    document.getElementById('hm-password').value = '';
    document.getElementById('hm-error').classList.add('hidden');
    const btn = document.getElementById('hm-confirm');
    btn.disabled = true;
    btn.textContent = 'Lanjutkan';
    document.getElementById('handover-modal').classList.remove('hidden');
    document.getElementById('hm-username').focus();
}

function closeHandoverModal() {
    document.getElementById('handover-modal').classList.add('hidden');
}

function onHmActionChange() {
    _hmUpdateConfirmBtn();
}

async function confirmHandover() {
    const action   = document.querySelector('input[name="hm-action"]:checked')?.value;
    const errEl    = document.getElementById('hm-error');
    const btn      = document.getElementById('hm-confirm');

    if (!action) {
        errEl.textContent = 'Pilih tindakan terlebih dahulu.';
        errEl.classList.remove('hidden');
        return;
    }

    const username = document.getElementById('hm-username').value.trim();
    const password = document.getElementById('hm-password').value;

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
            closeHandoverModal();
            document.getElementById('save-action-input').value = action;
            formDirty = false;
            document.getElementById('laporan-form').submit();
        } else {
            errEl.textContent = data.message ?? 'Username atau password salah.';
            errEl.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Lanjutkan';
        }
    } catch (e) {
        errEl.textContent = 'Terjadi kesalahan. Coba lagi.';
        errEl.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = 'Lanjutkan';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('hm-password')?.addEventListener('keydown', e => {
        if (e.key === 'Enter') confirmHandover();
    });
    document.getElementById('hm-username')?.addEventListener('input', _hmUpdateConfirmBtn);
    document.getElementById('hm-password')?.addEventListener('input', _hmUpdateConfirmBtn);
});

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
        title: 'Konfirmasi Estafet Laporan',
        desc:  'Setelah diteruskan, data yang sudah diisi tidak dapat diubah lagi. Masukkan username dan password Anda untuk melanjutkan.',
        btnText: 'Estafet',
        btnClass: 'bg-amber-500 hover:bg-amber-600',
    },
    submit: {
        title: 'Konfirmasi Kirim Laporan',
        desc:  'Setelah dikirim, data tidak dapat diubah lagi. Masukkan username dan password Anda untuk melanjutkan.',
        btnText: 'Kirim Laporan',
        btnClass: 'bg-sky-500 hover:bg-sky-600',
    },
    submit_revision: {
        title: 'Konfirmasi Kirim Revisi ke Supervisor',
        desc:  'Laporan akan langsung dikirim ke supervisor tanpa melalui tahap pembacaan. Masukkan username dan password Anda untuk melanjutkan.',
        btnText: 'Kirim ke Supervisor',
        btnClass: 'bg-emerald-500 hover:bg-emerald-600',
    },
};

function openSaveModal()    { openConfirmModal('save'); }
function openConfirmModal(action) {
    // Block save/handover/submit if any CFU input has an invalid value
    const invalidInputs = document.querySelectorAll('.cfu-input.border-red-400, .personnel-cfu-input.border-red-400');
    if (invalidInputs.length > 0) {
        showAlertModal(
            'Format Nilai CFU Tidak Valid',
            'Terdapat ' + invalidInputs.length + ' field CFU dengan nilai tidak valid.\n\nNilai yang diperbolehkan: bilangan bulat positif (misal: 1, 250), <1, atau TNTC.\n\nJika tidak ada koloni, gunakan <1. Nilai desimal, nol, dan negatif tidak diperbolehkan.'
        );
        invalidInputs[0].focus();
        return;
    }
    if (action === 'handover') {
        const missing = getMissingCols(1);
        if (missing.size > 0) {
            showAlertModal(
                'Data Belum Lengkap',
                'Kolom berikut belum diisi lengkap:\n\u2022 ' + [...missing].join('\n\u2022 ') + '\n\nIsi semua data sebelum melanjutkan.'
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
    const syncIncubatorOwnerInRow = (row) => {
        if (!row) return;

        const dateIn = row.querySelector('input[data-incubator-input="date_in"]');
        const timeIn = row.querySelector('input[data-incubator-input="time_in"]');
        const dateOut = row.querySelector('input[data-incubator-input="date_out"]');
        const timeOut = row.querySelector('input[data-incubator-input="time_out"]');

        const inOwner = row.querySelector('[data-incubator-owner="in"]');
        const outOwner = row.querySelector('[data-incubator-owner="out"]');

        const hasInValue = Boolean((dateIn?.value ?? '').trim()) || Boolean((timeIn?.value ?? '').trim());
        const hasOutValue = Boolean((dateOut?.value ?? '').trim()) || Boolean((timeOut?.value ?? '').trim());

        if (inOwner) {
            inOwner.classList.toggle('hidden', !hasInValue);

            if (hasInValue) {
                const nameEl = inOwner.querySelector('[data-incubator-owner-name]');
                if (nameEl) {
                    const existingName = (nameEl.dataset.existingName ?? '').trim();
                    const currentName = (nameEl.dataset.currentName ?? '').trim();
                    nameEl.textContent = existingName || currentName || 'N/A';
                }
            }
        }

        if (outOwner) {
            outOwner.classList.toggle('hidden', !hasOutValue);

            if (hasOutValue) {
                const nameEl = outOwner.querySelector('[data-incubator-owner-name]');
                if (nameEl) {
                    const existingName = (nameEl.dataset.existingName ?? '').trim();
                    const currentName = (nameEl.dataset.currentName ?? '').trim();
                    nameEl.textContent = existingName || currentName || 'N/A';
                }
            }
        }
    };

    const syncAllIncubatorOwners = () => {
        document.querySelectorAll('[data-incubator-entry-row]').forEach(syncIncubatorOwnerInRow);
    };

    document.getElementById('save-modal-password')?.addEventListener('keydown', e => {
        if (e.key === 'Enter') confirmSave();
    });

    // Re-validate CFU inputs on page load (restores red border after back()->withInput())
    document.querySelectorAll('.cfu-input').forEach(inp => {
        const raw = inp.value;
        if (raw === '') return;
        const valid = /^(<1|TNTC|[1-9][0-9]*)$/i.test(raw);
        inp.classList.toggle('border-red-400', !valid);
        inp.classList.toggle('ring-1',          !valid);
        inp.classList.toggle('ring-red-400',    !valid);
    });

    const focusInputName = @json(session('focus_input'));
    if (focusInputName) {
        const target = document.querySelector(`[name="${focusInputName}"]`);
        if (target) {
            target.focus({ preventScroll: true });
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    syncAllIncubatorOwners();

    document.addEventListener('input', (e) => {
        const target = e.target;
        if (!(target instanceof HTMLInputElement)) return;
        if (!target.matches('input[data-incubator-input]')) return;

        syncIncubatorOwnerInRow(target.closest('[data-incubator-entry-row]'));
    });

    document.addEventListener('change', (e) => {
        const target = e.target;
        if (!(target instanceof HTMLInputElement)) return;
        if (!target.matches('input[data-incubator-input]')) return;

        syncIncubatorOwnerInRow(target.closest('[data-incubator-entry-row]'));
    });
});

// CFU string helpers — support <1, TNTC, positive integers
function cfuToNum(v) {
    if (v === undefined || v === null || v === '') return null;
    if (v.toUpperCase() === 'TNTC') return Infinity;
    if (v === '<1') return 0;
    const n = parseInt(v, 10);
    return isNaN(n) || n <= 0 ? null : n;
}
function cfuSumStr(b, f) {
    const bn = cfuToNum(b);
    const fn = cfuToNum(f);
    if (bn === null && fn === null) return '';
    if (bn === Infinity || fn === Infinity) return 'TNTC';
    const sum = (bn ?? 0) + (fn ?? 0);
    if (sum === 0 && (b === '<1' || f === '<1')) return '<1';
    return String(sum);
}

// Auto-calculate T = B + F when user types in a CFU input
document.addEventListener('input', function (e) {
    if (!e.target.classList.contains('cfu-input')) return;

    // Validate input value — highlight red if not a recognised CFU value
    const raw = e.target.value;
    const valid = raw === '' || /^(<1|TNTC|[1-9][0-9]*)$/i.test(raw);
    e.target.classList.toggle('border-red-400', !valid);
    e.target.classList.toggle('ring-1',          !valid);
    e.target.classList.toggle('ring-red-400',    !valid);

    const loc = e.target.dataset.loc;
    const col = e.target.dataset.col;

    const bInput = document.querySelector(`[data-loc="${loc}"][data-col="${col}"][data-type="b"]`);
    const fInput = document.querySelector(`[data-loc="${loc}"][data-col="${col}"][data-type="f"]`);

    const tSpan = document.getElementById(`t-${loc}-${col}`);
    if (tSpan) {
        const tStr = cfuSumStr(bInput?.value, fInput?.value);
        tSpan.textContent = tStr !== '' ? tStr : '-';
        tSpan.className   = `text-[11px] font-semibold ${tStr !== '' ? 'text-gray-700' : 'text-gray-300'}`;
    }

    // Recalculate Kesimpulan for this location across all its inputs
    const konklusiCell = document.getElementById(`konklusi-${loc}`);
    if (konklusiCell) {
        let maxT = 0, maxF = 0, hasAny = false;
        // maxT = max of (B+F) per column for this location
        const colSet = new Set();
        document.querySelectorAll(`[data-loc="${loc}"][data-type="b"]`).forEach(inp => { if (inp.value !== '') colSet.add(inp.dataset.col); });
        document.querySelectorAll(`[data-loc="${loc}"][data-type="f"]`).forEach(inp => { if (inp.value !== '') colSet.add(inp.dataset.col); });
        colSet.forEach(c => {
            hasAny = true;
            const bInp = document.querySelector(`[data-loc="${loc}"][data-col="${c}"][data-type="b"]`);
            const fInp = document.querySelector(`[data-loc="${loc}"][data-col="${c}"][data-type="f"]`);
            maxT = Math.max(maxT, (cfuToNum(bInp?.value) ?? 0) + (cfuToNum(fInp?.value) ?? 0));
        });
        document.querySelectorAll(`[data-loc="${loc}"][data-type="f"]`).forEach(inp => {
            if (inp.value !== '') { maxF = Math.max(maxF, cfuToNum(inp.value) ?? 0); }
        });
        const alertT  = konklusiCell.dataset.alertT  !== '' ? parseFloat(konklusiCell.dataset.alertT)  : null;
        const alertF  = konklusiCell.dataset.alertF  !== '' ? parseFloat(konklusiCell.dataset.alertF)  : null;
        const actionT = konklusiCell.dataset.actionT !== '' ? parseFloat(konklusiCell.dataset.actionT) : null;
        const actionF = konklusiCell.dataset.actionF !== '' ? parseFloat(konklusiCell.dataset.actionF) : null;
        let label = '—', cls = 'text-gray-300 text-[11px]';
        if (hasAny) {
            const isTMS = (actionT !== null && maxT >= actionT) || (actionF !== null && maxF >= actionF);
            if (isTMS) { label = 'TMS'; cls = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-700'; }
            else       { label = 'MS';  cls = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700'; }
        }
        konklusiCell.innerHTML = `<span class="${cls}">${label}</span>`;

        // Recalculate section conclusion
        const sectionInstance = e.target.dataset.sectionInstance;
        if (sectionInstance) recalcSectionKonklusi(sectionInstance);
    }
});

function recalcSectionKonklusi(sectionInstance) {
    // sectionInstance is "{section_id}-{instance}" (e.g. "3-1" or "3-2")
    const sectionKey = sectionInstance;
    const el = document.getElementById(`section-konklusi-${sectionKey}`);
    if (!el) return;
    let hasTMS = false, hasAny = false, newVal = '';
    document.querySelectorAll(`.konklusi-cell[data-section-instance="${sectionInstance}"]`).forEach(cell => {
        const span = cell.querySelector('span');
        if (!span) return;
        const txt = span.textContent.trim();
        if (txt === 'TMS' || txt === 'MS') hasAny = true;
        if (txt === 'TMS') hasTMS = true;
    });
    if (!hasAny) {
        el.innerHTML = '<span class="text-xs text-gray-400 italic">Belum ada data</span>';
        newVal = '';
    } else if (hasTMS) {
        el.innerHTML = '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 border border-red-200">Tidak Memenuhi Spesifikasi <span class="font-bold">(TMS)</span></span>';
        newVal = 'TMS';
    } else {
        el.innerHTML = '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 border border-green-200">Memenuhi Spesifikasi <span class="font-bold">(MS)</span></span>';
        newVal = 'MS';
    }
    const hiddenInput = document.getElementById(`section-konklusi-input-${sectionKey}`);
    if (hiddenInput) hiddenInput.value = newVal;
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

// Kirim Revisi langsung ke Supervisor (skip reading phase)
function openRevisionSubmitFlow() {
    const myShift = {{ $myShift }};
    const missing = getMissingCols(myShift);
    if (missing.size > 0) {
        showAlertModal(
            'Data Belum Lengkap',
            'Kolom Shift ' + myShift + ' berikut belum diisi lengkap:\n\u2022 ' + [...missing].join('\n\u2022 ') + '\n\nIsi semua data sebelum melanjutkan.'
        );
        return;
    }
    openConfirmModal('submit_revision');
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

// ── Personnel: validate CFU + auto-calc T & Kesimpulan ─────────────────
(function () {
    function recalcPersonnelPoint(pointRow, personRow, methodId) {
        const bInp    = document.querySelector(`.personnel-cfu-input[data-prow="${pointRow}"][data-ptype="b"]`);
        const fInp    = document.querySelector(`.personnel-cfu-input[data-prow="${pointRow}"][data-ptype="f"]`);
        const tDisp   = document.getElementById(`pnt-${pointRow}`);
        const tHidden = document.getElementById(`pnt-h-${pointRow}`);
        const kDisp   = document.getElementById(`pkdisp-${pointRow}`);
        const kHidden = document.getElementById(`pkesimpulan-${pointRow}`);

        // Auto-calc T = B + F
        const tStr = cfuSumStr(bInp?.value, fInp?.value);
        if (tDisp) {
            tDisp.textContent = tStr || '—';
            tDisp.className   = tStr ? 'text-xs font-semibold text-gray-700' : 'text-xs text-gray-300';
        }
        if (tHidden) tHidden.value = tStr;

        // Kesimpulan: need selected class
        // Finger Dab: hidden input with class personnel-class-input (not type=radio)
        // Cawan Kontak: radio buttons
        const classChecked = document.querySelector(`.personnel-class-input[data-person="${personRow}"][type="radio"]:checked`);
        const classHidden  = document.querySelector(`.personnel-class-input[data-person="${personRow}"][type="hidden"]`);
        const cls = classChecked?.value || classHidden?.value || '';

        const lims = window.personnelLimits?.[methodId];
        if (!cls || !lims?.[cls]?.action) {
            if (kDisp)   { kDisp.textContent = '—'; kDisp.className = 'text-xs text-gray-300'; }
            if (kHidden) kHidden.value = '';
            return;
        }

        const bVal = cfuToNum(bInp?.value);
        const fVal = cfuToNum(fInp?.value);
        const tVal = cfuToNum(tStr);

        if (bVal === null && fVal === null) {
            if (kDisp)   { kDisp.textContent = '—'; kDisp.className = 'text-xs text-gray-300'; }
            if (kHidden) kHidden.value = '';
            return;
        }

        const lim    = lims[cls].action;
        const isTMS  = (tVal === Infinity) || (fVal === Infinity) ||
                       (lim.total !== null && tVal !== null && tVal >= lim.total) ||
                       (lim.fungi !== null && fVal !== null && fVal >= lim.fungi);
        const result = isTMS ? 'TMS' : 'MS';

        if (kDisp) {
            kDisp.textContent = result;
            kDisp.className   = result === 'MS'
                ? 'text-xs font-semibold text-green-600'
                : 'text-xs font-semibold text-red-600';
        }
        if (kHidden) kHidden.value = result;
    }

    // Validate a single personnel CFU input and style border
    function validatePersonnelCfu(inp) {
        const raw   = inp.value;
        const valid = raw === '' || /^(<1|TNTC|[1-9][0-9]*)$/i.test(raw);
        inp.classList.toggle('border-red-400',  !valid);
        inp.classList.toggle('ring-1',           !valid);
        inp.classList.toggle('ring-red-400',     !valid);
        inp.classList.toggle('border-gray-200',  valid);
    }

    // Input: validate CFU + recalc row
    document.addEventListener('input', function (e) {
        if (!e.target.classList.contains('personnel-cfu-input')) return;
        validatePersonnelCfu(e.target);
        recalcPersonnelPoint(
            e.target.dataset.prow,
            e.target.dataset.person,
            e.target.dataset.method
        );
    });

    // Change: class radio selected → recalc ALL points for that person
    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('personnel-class-input') || e.target.type !== 'radio') return;
        const personRow = e.target.dataset.person;
        const methodId  = e.target.dataset.method;
        const done = new Set();
        document.querySelectorAll(`.personnel-cfu-input[data-person="${personRow}"]`).forEach(inp => {
            if (!done.has(inp.dataset.prow)) {
                done.add(inp.dataset.prow);
                recalcPersonnelPoint(inp.dataset.prow, personRow, methodId);
            }
        });
    });

    // Page load: validate existing values + recalc all
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.personnel-cfu-input').forEach(validatePersonnelCfu);

        const done = new Set();
        document.querySelectorAll('.personnel-cfu-input').forEach(inp => {
            const key = inp.dataset.prow + '|' + inp.dataset.person + '|' + inp.dataset.method;
            if (!done.has(key)) {
                done.add(key);
                recalcPersonnelPoint(inp.dataset.prow, inp.dataset.person, inp.dataset.method);
            }
        });
    });
})();
</script>
