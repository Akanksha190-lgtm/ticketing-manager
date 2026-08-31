
// ============================================================
// DATA — this runs entirely in-browser for now (JS arrays, no backend yet).
// Real field values sourced from: Air India private fare trade circular (AUSNT007, valid to 30 Sep '26),
// Air India BSP commission circular (1% eff 1 Jun 2025), Malaysia Airlines Reservations & Ticketing
// Guidelines (June 2026 v2 — 2% AU/NZ/SWP commission), and the AU IATA PCC 8T03 commission master list.
// A future step: move this into Laravel + MySQL with role-based API access (Manager can write,
// Ticketing Team is read-only) so the data persists and is shared across the whole team, not just this browser.
// ============================================================

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const topbarDate = document.getElementById("topbar-date");
if (topbarDate) {
    const TODAY = new Date();

    topbarDate.textContent = TODAY.toLocaleDateString("en-AU", {
        day: "2-digit",
        month: "short",
        year: "numeric"
    });
}

function daysUntil(dateStr) { return Math.round((new Date(dateStr) - TODAY) / 86400000); }
function statusFor(f) { const d = daysUntil(f.validUntil); if (d < 0) return "expired"; if (d <= 7) return "soon"; return "active"; }
function resolveStatusClass(statusText) {
  const value = String(statusText || '').trim().toLowerCase();
  if (!value) return 'active';
  if (value.includes('active')) return 'active';
  if (value.includes('expir')) return 'soon';
  if (value.includes('expired')) return 'expired';
  return value;
}
function statusBadge(status) {
  const normalized = resolveStatusClass(status);
  const map = { active: "Active", soon: "Expiring soon", expired: "Expired" };
  return `<span class="badge-status ${normalized}"><span class="dot"></span>${map[normalized]}</span>`;
}
function renderStatusBadge(statusText) {
  const text = String(statusText || '').trim();
  const statusClass = resolveStatusClass(text);
  return `<span class="status-text status-badge ${statusClass}"><span class="dot"></span>${text}</span>`;
}
function fmtDate(dateStr) { return new Date(dateStr).toLocaleDateString("en-AU", { day: "2-digit", month: "short", year: "numeric" }); }
function money(n, currency) { return `${currency || "AUD"} ${Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`; }

function calcFare(f) {
  const net = f.published * (1 - f.commissionPct / 100);
  const gross = net + f.markup;
  return { net, gross, margin: gross - net };
}

function normalizeSourceName(source) {
  const value = String(source || '').trim().toLowerCase();
  if (!value) return '';
  if (value.includes('private')) return 'PRIVATE/TOUR CODE';
  if (value.includes('iata') || value.includes('bsp')) return 'IATA/BSP PUBLISHED';
  return value;
}

function sourceBadge(source) {
  const normalized = String(source || '').trim().toLowerCase();
  const isPrivate = normalized.includes('private');
  const label = isPrivate ? 'Private / Tour Code' : 'IATA/BSP Published';
  const klass = isPrivate ? 'private' : 'bsp';
  return `<span class="source-text badge-source ${klass}">${label}</span>`;
}

function toast(message, type = 'success') {

    const stack = document.getElementById('toast-stack');

    if (!stack) {
        console.error('toast-stack not found');
        return;
    }

    const el = document.createElement('div');

    el.textContent = message;

    el.style.position = 'fixed';
    el.style.bottom = '20px';
    el.style.right = '20px';
    el.style.zIndex = '999999';

    el.style.padding = '12px 20px';

    // Navy
    el.style.backgroundColor = 'var(--brand-navy)';

    el.style.color = '#fff';

    el.style.borderRadius = '6px';
    el.style.fontSize = '14px';
    el.style.fontWeight = '500';

    el.style.boxShadow = 'var(--shadow-lg)';

    stack.appendChild(el);

    setTimeout(() => {
        el.style.opacity = '0';
        el.style.transition = 'opacity .25s';

        setTimeout(() => {
            el.remove();
        }, 250);

    }, 2600);
}

function updateCalcPreview() {
  const published = Number(document.getElementById("f-published").value) || 0;
  const commissionPct = Number(document.getElementById("f-commission-pct").value) || 0;
  const markup = Number(document.getElementById("f-markup").value) || 0;
  const currency = document.getElementById("f-currency").value;
  const net = published * (1 - commissionPct / 100);
  const gross = net + markup;
  document.getElementById("preview-net").textContent = money(net, currency);
  document.getElementById("preview-gross").textContent = money(gross, currency);
  document.getElementById("preview-margin").textContent = money(gross - net, currency);
}

["f-published", "f-commission-pct", "f-markup", "f-currency"]
    .forEach(id => {
        const element = document.getElementById(id);

        if (element) {
            element.addEventListener("input", updateCalcPreview);
        }
    });

function loadFareIntoForm(id) {
  const f = mockFares.find(x => x.id == id);
  if (!f) return;
  document.getElementById("fare-id").value = f.id;
  document.getElementById("f-airline").value = f.airline;
  document.getElementById("f-airline-code").value = f.airlineCode;
  document.getElementById("f-origin").value = f.origin;
  document.getElementById("f-destination").value = f.destination;
  document.getElementById("f-cabin").value = f.cabin;
  document.getElementById("f-source").value = f.source;
  document.getElementById("f-tourcode").value = f.tourCode;
  document.getElementById("f-pcc").value = f.pcc;
  document.getElementById("f-published").value = f.published;
  document.getElementById("f-currency").value = f.currency;
  document.getElementById("f-commission-pct").value = f.commissionPct;
  document.getElementById("f-markup").value = f.markup;
  document.getElementById("f-travel-from").value = f.travelFrom;
  document.getElementById("f-travel-to").value = f.travelTo;
  document.getElementById("f-valid-until").value = f.validUntil;
  document.getElementById("f-internal-notes").value = f.internalNotes || "";
  document.getElementById("f-customer-notes").value = f.customerNotes || "";
  document.getElementById("fare-form-submit").textContent = "Save changes";
  document.getElementById("fare-form-cancel").style.display = "inline-flex";
  document.getElementById("form-mode-tag").textContent = "Editing #" + f.id;
  updateCalcPreview();
  document.getElementById("fare-form").scrollIntoView({ behavior: "smooth", block: "start" });
}

function resetForm() {
  document.getElementById("fare-form").reset();
  document.getElementById("fare-id").value = "";
  document.getElementById("f-currency").value = "AUD";
  document.getElementById("fare-form-submit").textContent = "Add fare";
  document.getElementById("fare-form-cancel").style.display = "none";
  document.getElementById("form-mode-tag").textContent = "New";
  updateCalcPreview();
}

function deleteFare(id) {
  const f = mockFares.find(x => x.id == id);
  if (!f) return;
  if (!confirm(`Delete the ${f.airline} ${f.origin} → ${f.destination} fare? This can't be undone.`)) return;
  mockFares = mockFares.filter(x => x.id != id);
  toast(`Fare deleted — ${f.airline} ${f.origin} → ${f.destination}`, "danger");
  refreshAll();
}
//to format date 
function formatValidUntil(dateString) {

    if (!dateString) return '-';

    // Remove time part
    const dateOnly = String(dateString).split(/[T ]/)[0];

    const [year, month, day] = dateOnly.split('-');

    const months = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];
    if (!year || !month || !day) {
        return dateString;
    }

    return `${day} ${months[parseInt(month) - 1]} ${year}`;
}

const cancelButton = document.getElementById("fare-form-cancel");

if (cancelButton) {
    cancelButton.addEventListener("click", resetForm);
}

//add fare submit 

const fareForm = document.getElementById('fare-form');

if (fareForm) {

    fareForm.addEventListener('submit', function (e) {

        e.preventDefault();

        const formData = new FormData(fareForm);

        fetch(fareForm.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(async response => {

            const data = await response.json();

            console.log('CREATE RESPONSE:', data);

            if (!response.ok) {
                throw new Error(data.message || 'Failed to create fare');
            }

            return data;
        })
        .then(data => {

            if (data.success) {

                toast(data.message || 'Created successfully.','success');

                // form close/reset
                fareForm.reset();
               
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }

        })
        .catch(error => {

            console.error('Create fare error:', error);

            toast(
                error.message || 'Something went wrong.',
                'danger'
            );
        });
    });
}
//edit -cancel -save buttons
document.addEventListener('click', function(e) {

    const buttonTarget = e.target.closest('button');

    if (!buttonTarget) return;

    // EDIT
    if (buttonTarget.classList.contains('edit-commission')) {

        const button = buttonTarget;
        const id = button.dataset.id;

        const row = document.getElementById('commission-row-' + id);

        const airline = row.querySelector('.airline-text').innerText.trim();
        const code = row.querySelector('.code-text').innerText.trim();
        const numeric = row.querySelector('.numeric-text').innerText.trim();

        const au = row.querySelector('.au-text').innerText.replace('%', '').trim();

        const exau = row.querySelector('.exau-text').innerText.replace('%', '').trim();

        // ORIGINAL VALUES SAVE 
        row.dataset.airline = airline;
        row.dataset.code = code;
        row.dataset.numeric = numeric;
        row.dataset.au = au;
        row.dataset.exau = exau;


        // Replace text with inputs
        row.cells[0].innerHTML =
            `<input type="text" class="edit-input form-control form-control-sm" value="${airline}">`;

        row.cells[1].innerHTML =
            `<input type="text" class="edit-input form-control form-control-sm" value="${code}">`;

        row.cells[2].innerHTML =
            `<input type="text" class="edit-input form-control form-control-sm" value="${numeric}">`;

        row.cells[3].innerHTML =
            `<input type="number" step="0.01" class="edit-input form-control form-control-sm" value="${au}">`;

        row.cells[4].innerHTML =
            `<input type="number" step="0.01" class="edit-input form-control form-control-sm" value="${exau}">`;


        // Edit -> Save
        button.innerHTML = '<i class="bi bi-check" title="Save"></i>';

        button.classList.remove('edit-commission');
        button.classList.add('save-commission');


        // =========================
        // CANCEL BUTTON
        // =========================

        const cancelButton = document.createElement('button');

        cancelButton.type = 'button';
        cancelButton.className = 'btn cancel-commission';
        cancelButton.dataset.id = id;
        cancelButton.innerHTML = '<i class="bi bi-x" title="Cancel"></i>';

        button.parentNode.appendChild(cancelButton);
    }

    // CANCEL
    else if (buttonTarget.classList.contains('cancel-commission')) {

        const button = buttonTarget;
        const id = button.dataset.id;

        const row = document.getElementById('commission-row-' + id);

        // Original values wapas show karo
        row.cells[0].innerHTML =
            `<span class="airline-text">${row.dataset.airline}</span>`;

        row.cells[1].innerHTML =
            `<span class="code-text">${row.dataset.code}</span>`;

        row.cells[2].innerHTML =
            `<span class="numeric-text">${row.dataset.numeric}</span>`;

        row.cells[3].innerHTML =
            `<span class="au-text">${row.dataset.au}%</span>`;

        row.cells[4].innerHTML =
            `<span class="exau-text">${row.dataset.exau}%</span>`;

        // Save button ko Edit bana do
        const saveButton = row.querySelector('.save-commission');

        saveButton.innerHTML = '<i class="bi bi-pencil-square" title="Edit"></i>';

        saveButton.classList.remove('save-commission');
        saveButton.classList.add('edit-commission');

        // Cancel button remove
        button.remove();
    }
    // SAVE
    else if (buttonTarget.classList.contains('save-commission')) {

        const button = buttonTarget;
        const id = button.dataset.id;

        const row = document.getElementById('commission-row-' + id);

        const inputs = row.querySelectorAll('.edit-input');

        const airline = inputs[0].value;
        const code = inputs[1].value;
        const numeric = inputs[2].value;
        const au = inputs[3].value;
        const exau = inputs[4].value;


        fetch('/airline-commissions/' + id, {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },

            body: JSON.stringify({

                _method: 'PUT',

                airline: airline,
                code: code,
                numeric: numeric,
                au_commission: au,
                ex_au_commission: exau

            })

        })

        .then(response => {

            if (!response.ok) {
                throw new Error('Update failed');
            }

            return response.json();
        })
        .then(data => {

            console.log('Updated successfully');

           if (data.success) 
            {
                toast(data.message || 'Updated successfully.','success');

                setTimeout(() => {
                    location.reload();
                }, 1000);

            } else {

                toast(data.message || 'Update failed.','danger');

            }

        })
        .catch(error => {

            console.error(error);

        });

    }

});

//----------------- Fare entry table edit/save/cancel -----------------
document.addEventListener('click', function(e) {

    const buttonTarget = e.target.closest('button');

    if (!buttonTarget) return;

    // =========================
    // EDIT
    // =========================
    if (buttonTarget.classList.contains('edit-fare-entry')) {

        const button = buttonTarget;
        const id = button.dataset.id;

        const row = document.getElementById('fare-entry-row-' + id);

        const codeCell = row.querySelector('.route-code-cell');

        if (codeCell) {
            row.dataset.originalCodeHtml = codeCell.innerHTML;
        }

        const airline = row.querySelector('.airline-text').innerText.trim();
        const origin = row.dataset.origin;
        const destination = row.dataset.destination;
        const originCode = row.dataset.originCode || '';
        const destinationCode = row.dataset.destinationCode || '';

        const cabin = row.querySelector('.cabin-text').innerText.trim();
        const source = row.querySelector('.source-text').innerText.trim();
        row.dataset.source = normalizeSourceName(source);

        const published = row.querySelector('.published-text').innerText.replace(/[A-Z]{3}\s*/i, '').replace(/,/g, '').trim();
        const auComm = row.querySelector('.disc-comm-text').innerText.replace('%', '').trim();
        const net = row.querySelector('.net-text').innerText.replace(/[A-Z]{3}\s*/i, '').replace(/,/g, '').trim();
        const markup = row.querySelector('.markup-text').innerText.replace(/[A-Z]{3}\s*/i, '').replace(/,/g, '').trim();
        const gross = row.querySelector('.gross-text').innerText.replace(/[A-Z]{3}\s*/i, '').replace(/,/g, '').trim();

        // const validUntil = row.querySelector('.valid-until-value').innerText.trim();
        const validUntilElement = row.querySelector('.valid-until-value');

        let validUntil = '';

        if (validUntilElement) {
            const text = validUntilElement.innerText.trim();

            // if already YYYY-MM-DD hai
            if (/^\d{4}-\d{2}-\d{2}$/.test(text)) {
                validUntil = text;
            }

            // if 22 Aug 2026 hai
            else {
                const [day, monthName, year] = text.split(' ');

                const months = {Jan: '01',Feb: '02',Mar: '03',Apr: '04',May: '05',Jun: '06',Jul: '07',Aug: '08',Sep: '09',Oct: '10',Nov: '11',Dec: '12'};

                validUntil =`${year}-${months[monthName]}-${day.padStart(2, '0')}`;
            }
        }
        const status =row.querySelector('.status-text').innerText.trim();
        const currency = row.dataset.currency;
        // Save original values
        row.dataset.airline = airline;
        row.dataset.cabin = cabin;
        row.dataset.source = source;
        row.dataset.published = published;
        row.dataset.auComm = auComm;
        row.dataset.net = net;
        row.dataset.markup = markup;
        row.dataset.gross = gross;
        row.dataset.validUntil = validUntil;
        row.dataset.status = status;
        row.dataset.origin = origin;
        row.dataset.destination = destination;
        row.dataset.currency = currency;
        row.dataset.originCode = originCode;
        row.dataset.destinationCode = destinationCode;

        // Make editable
        row.cells[0].innerHTML = `<input type="text" class="edit-input form-control form-control-sm" value="${airline}">`;

        const routeValue = `${origin} (${originCode}) → ${destination} (${destinationCode})`;

        row.cells[1].innerHTML = `<input type="text" class="edit-input route-edit form-control form-control-sm" value="${routeValue}" placeholder="Origin (CODE) → Destination (CODE)">`;
        row.cells[2].innerHTML = `<input type="text" class="edit-input form-control form-control-sm" value="${cabin}">`;

        row.cells[3].innerHTML = `<input type="text" class="edit-input form-control form-control-sm" value="${source}">`;

        row.cells[4].innerHTML = `<input type="number" step="0.01" class="edit-input form-control form-control-sm" value="${published}">`;

        row.cells[5].innerHTML = `<input type="number" step="0.1" class="edit-input form-control form-control-sm" value="${auComm}">`;

        row.cells[6].innerHTML = `<input type="number" step="0.01" class="edit-input form-control form-control-sm" value="${net}">`;

        row.cells[7].innerHTML = `<input type="number" step="0.01" class="edit-input form-control form-control-sm" value="${markup}">`;

        row.cells[8].innerHTML = `<input type="number" step="0.01" class="edit-input form-control form-control-sm" value="${gross}">`;

        row.cells[9].innerHTML = `<input type="date" class="edit-input form-control form-control-sm" value="${validUntil}">`;

        row.cells[10].innerHTML = `<input type="text" class="edit-input form-control form-control-sm" value="${status}">`;


        // Edit -> Save
        button.innerHTML = '<i class="bi bi-check" title="Save"></i>';

        button.classList.remove('edit-fare-entry');
        button.classList.add('save-fare-entry');


        // Cancel button
        const cancelButton = document.createElement('button');

        cancelButton.type = 'button';
        cancelButton.className = 'btn cancel-fare-entry';
        cancelButton.dataset.id = id;
        cancelButton.innerHTML = '<i class="bi bi-x" title="Cancel"></i>';

        button.parentNode.appendChild(cancelButton);
    }


    // =========================
    // CANCEL
    // =========================
    else if (buttonTarget.classList.contains('cancel-fare-entry')) {

        const button = buttonTarget;
        const id = button.dataset.id;

        const row =document.getElementById('fare-entry-row-' + id);

        const validUntil = row.dataset.validUntil;
        const codeCell = row.querySelector('.route-code-cell');
        const originCode = row.dataset.originCode || '';
        const destinationCode = row.dataset.destinationCode || '';

        if (codeCell && row.dataset.originalCodeHtml !== undefined) {
            codeCell.innerHTML = row.dataset.originalCodeHtml;
        }
        // Restore original values
        row.cells[0].innerHTML =`<span class="airline-text">${row.dataset.airline}</span>`;

        row.cells[1].innerHTML = `<strong class="route-text">${row.dataset.origin} (${originCode})  → ${row.dataset.destination} (${destinationCode})</strong>
        ${!originCode || !destinationCode? `<button type="button" class="btn btn-sm btn-outline-primary ms-2 add-route-code-btn" data-bs-toggle="modal" data-bs-target="#routeCodeModal${id}">Add Code</button>`: ''
        }`;

        row.cells[2].innerHTML =`<span class="cabin-text">${row.dataset.cabin}</span>`;

        row.cells[3].innerHTML = sourceBadge(String(row.dataset.source || '').toLowerCase());

        row.cells[4].innerHTML =`<span class="published-text"><strong><span class="currency-text">${row.dataset.currency}</span> ${row.dataset.published}</span></strong`;

        row.cells[5].innerHTML =`<span class="disc-comm-text"><strong>${row.dataset.auComm}</span>%</strong>`;

        row.cells[6].innerHTML =
            `<span class="net-text"><strong><span class="currency-text">${row.dataset.currency}</span> ${row.dataset.net}</span><strong>`;

        row.cells[7].innerHTML =`<span class="markup-text"><strong><span class="currency-text">${row.dataset.currency}</span> ${row.dataset.markup}</span><strong>`;

        row.cells[8].innerHTML =`<span class="gross-text"><strong><span class="currency-text">${row.dataset.currency}</span> ${row.dataset.gross}</span></strong>`;

        row.cells[9].innerHTML =`<span class="valid-until-value">${formatValidUntil(row.dataset.validUntil)}</span>`;

        row.cells[10].innerHTML = renderStatusBadge(row.dataset.status);


        // Save -> Edit
        const saveButton = row.querySelector('.save-fare-entry');

        saveButton.innerHTML = '<i class="bi bi-pencil-square" title="Edit"></i>';

        saveButton.classList.remove('save-fare-entry');
        saveButton.classList.add('edit-fare-entry');


        // Remove Cancel
        button.remove();
    }


    // =========================
    // SAVE
    // =========================
    else if (buttonTarget.classList.contains('save-fare-entry')) {

        const button = buttonTarget;
        const id = button.dataset.id;

        const row = document.getElementById('fare-entry-row-' + id);

        const inputs = row.querySelectorAll('.edit-input');

        const airline_id = inputs[0].value;
        const routeMatch = inputs[1].value.match(/^(.+?)\s*\(([^()]*)\)\s*→\s*(.+?)\s*\(([^()]*)\)$/);

        if (!routeMatch) {
            toast('Route format must be: Origin (CODE) → Destination (CODE)', 'danger');
            return;
        }

        const origin = routeMatch[1].trim();
        const originCode = routeMatch[2].trim();
        const destination = routeMatch[3].trim();
        const destinationCode = routeMatch[4].trim();
        const cabin = inputs[2].value;
        const source = normalizeSourceName(inputs[3].value);
        const published = inputs[4].value;
        const auComm = inputs[5].value;
        const net = inputs[6].value;
        const markup = inputs[7].value;
        const gross = inputs[8].value;
        const validUntil = inputs[9].value;
        const status = inputs[10].value;


        fetch('/fare-commission-entries/' + id, {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },

            body: JSON.stringify({

                _method: 'PUT',

                airline_id: airline_id,
                origin: origin,
                origin_code: originCode,
                destination: destination,
                destination_code: destinationCode,
                cabin: cabin,
                source: source,
                published: published,
                au_commission: auComm,
                net: net,
                markup: markup,
                gross: gross,
                valid_until: validUntil,
                status: status

            })

        })
        .then(response => {

            if (!response.ok) {
                throw new Error('Update failed');
            }

            return response.json();

        })
        .then(data => {

            if (data.success) 
            {
                const row = document.getElementById('fare-entry-row-' + id);
                if (row) {
                    row.dataset.status = status;
                    row.dataset.source = source;
                    row.cells[3].innerHTML = sourceBadge(source);
                    row.cells[10].innerHTML = renderStatusBadge(status);
                }

                toast(data.message || 'Updated successfully.','success');
                
                setTimeout(() => {
                    location.reload();
                }, 1000);

            } else {

                toast(data.message || 'Update failed.','danger');

            }

        })
        .catch(error => {

            console.error('Update error:', error);

        });
    }


    // =========================
    // DELETE
    // =========================
    if (buttonTarget.classList.contains('delete-fare-entry')) {

        const id = buttonTarget.dataset.id;

        confirmDeleteFareEntry(id);
    }

});

//delete fare entry
function confirmDeleteFareEntry(id) {

    const stack = document.getElementById('toast-stack');

    if (!stack) {
        deleteFareEntry(id);
        return;
    }

    const confirmation = document.createElement('div');
    confirmation.className = 'delete-confirmation';
    confirmation.style.position = 'fixed';
    confirmation.style.inset = '0';
    confirmation.style.zIndex = '1000000';
    confirmation.style.display = 'flex';
    confirmation.style.alignItems = 'center';
    confirmation.style.justifyContent = 'center';
    confirmation.style.backgroundColor = 'rgba(15, 23, 42, 0.45)';
    confirmation.innerHTML = `
        <div style="width: min(380px, calc(100% - 32px)); padding: 24px; border-radius: 10px; background: #fff; color: var(--text-primary); box-shadow: var(--shadow-lg); text-align: center;">
            <i class="bi bi-exclamation-triangle" style="font-size: 32px; color: var(--status-critical);"></i>
            <h5 style="margin: 12px 0 8px;">Are you sure?</h5>
            <p style="margin: 0 0 20px; color: var(--text-muted);">Do you want to delete this fare entry?</p>
            <div style="display: flex; justify-content: center; gap: 10px;">
                <button type="button" class="btn confirm-cancel-button cancel-delete-fare">Cancel</button>
                <button type="button" class="btn confirm-delete-button confirm-delete-fare"><i class="bi bi-trash"></i> Delete</button>
            </div>
        </div>
    `;

    stack.appendChild(confirmation);

    confirmation.querySelector('.confirm-delete-fare').addEventListener('click', () => {
        confirmation.remove();
        deleteFareEntry(id);
    });

    confirmation.querySelector('.cancel-delete-fare').addEventListener('click', () => {
        confirmation.remove();
    });
}

function deleteFareEntry(id) {

    fetch('/fare-commission-entries/' + id, {

        method: 'DELETE',

        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }

    })
    .then(async response => {

        const text = await response.text();

        console.log('Delete response:', text);

        if (!response.ok) {
            throw new Error('Delete failed');
        }

        return JSON.parse(text);
    })
    .then(data => {

       if (data.success) {
            toast(data.message ?? 'Deleted successfully.');

            setTimeout(() => {
                location.reload();
            }, 1000);
        }

        // row remove without page reload
        const row = document.getElementById(
            'fare-entry-row-' + id
        );

        if (row) {
            row.remove();
        }

    })
    .catch(error => {

        console.error('Delete error:', error);

    });
}

// ---------------- Ticketing Team tab (read-only) ----------------

function getTicketingRows() {
  const q = (document.getElementById("tt-search").value || "").trim().toLowerCase();
  const source = document.getElementById("tt-filter-source").value;
  const status = document.getElementById("tt-filter-status").value;
  const sort = document.getElementById("tt-sort").value;
  let rows = mockFares.filter(f => {
    const hay = `${f.airline} ${f.origin} ${f.destination}`.toLowerCase();
    if (q && !hay.includes(q)) return false;
    if (source && f.source !== source) return false;
    if (status && statusFor(f) !== status) return false;
    return true;
  });
  rows.sort((a, b) => {
    if (sort === "commission_desc") return b.commissionPct - a.commissionPct;
    if (sort === "margin_desc") return calcFare(b).margin - calcFare(a).margin;
    return new Date(a.validUntil) - new Date(b.validUntil);
  });
  return rows;
}

// ---------------- Nav ----------------

const TOPBAR_COPY = {
  manager: { title: "Ticketing Manager", desc: "Enter the published/net fare, the IATA/BSP commission or private tour-code discount, and the agency markup — the sell fare for the Ticketing Team is calculated automatically." },
  ticketing: { title: "Ticketing Team", desc: "Everything needed to issue a ticket correctly — net fare, applicable IATA/BSP commission or private discount, sell price, and margin." }
};

// ---------------- Init ----------------

const searchForm = document.getElementById('master-search-form');

if (searchForm) {
    searchForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const searchInput = document.getElementById('master-search');
        const search = searchInput.value.trim();

        const url = new URL(searchForm.action, window.location.origin);

        if (search !== '') {
            const parameter = url.pathname.endsWith('/ticketing-team') ? 'master_search' : 'search';
            url.searchParams.set(parameter, search);
        }

        fetch(url.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async response => {

            console.log('STATUS:', response.status);

            if (!response.ok) {
                const text = await response.text();
                console.error('SERVER RESPONSE:', text);
                throw new Error('Search request failed');
            }

            return response.json();
        })
        .then(data => {

            const tbody = document.getElementById('master-table-body');

            tbody.innerHTML = '';

            data.airlineCommissions.forEach(commission => {

                const commissionActions = document.getElementById('view-manager') ? `
                            <td>
                                <button type="button" class="btn edit-commission" data-id="${commission.id}"><i class="bi bi-pencil-square" title="Edit"></i></button>
                            </td>` : '';

                tbody.innerHTML += `
                    <tr id="commission-row-${commission.id}">
                        <td>
                            <span class="airline-text">
                                ${commission.airline ?? ''}
                            </span>
                        </td>

                        <td>
                            <span class="code-text">
                                ${commission.code ?? ''}
                            </span>
                        </td>

                        <td>
                            <span class="numeric-text">
                                ${commission.numeric ?? ''}
                            </span>
                        </td>

                        <td>
                            <span class="au-text">
                                ${parseFloat(commission.au_commission ?? 0).toFixed(2)}%
                            </span>
                        </td>

                        <td>
                            <span class="exau-text">
                                ${parseFloat(commission.ex_au_commission ?? 0).toFixed(2)}%
                            </span>
                        </td>

                        ${commissionActions}
                    </tr>
                `;
            });
        })
        .catch(error => {
            console.error('Search error:', error);
        });
    });
}

const fareSearchForm = document.getElementById('fare-search-form');

if (fareSearchForm) {

    fareSearchForm.addEventListener('submit', function (e) {

        e.preventDefault();

        const searchInput = document.getElementById('fare-search');

        const search = searchInput.value.trim();

        const url = new URL(fareSearchForm.action,window.location.origin);

        if (search !== '') {
            url.searchParams.set('fare_search', search);
        }

        // Source
        const sourceSelect = document.getElementById('fare-source');

        if (sourceSelect && sourceSelect.value !== '') {
            url.searchParams.set('source', sourceSelect.value);
        }

        // Status
        const statusSelect = document.getElementById('fare-status');

        if (statusSelect && statusSelect.value !== '') {
            url.searchParams.set('status', statusSelect.value);
        }

        url.searchParams.set('type', 'fare');

        fetch(url.toString(), {
            method: 'GET',

            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })

        .then(async response => {

            console.log('FARE STATUS:', response.status);
            
            if (!response.ok) {

                const text = await response.text();

                console.error(
                    'FARE SERVER RESPONSE:',
                    text
                );

                throw new Error('Fare search request failed');
            }

            return response.json();
        })

        .then(data => {

            const tbody = document.getElementById('ticketing-table-body');

            if (!tbody) {
                console.error('fare-table-body not found');
                return;
            }

            tbody.innerHTML = '';

            data.farecomissentry.forEach(entry => {
                const net = parseFloat(entry.net ?? 0);
                const gross = parseFloat(entry.gross ?? 0);

                const margin = gross - net;
                tbody.innerHTML += `
                    <tr>

                        <td>
                            ${entry.airline?.airline ?? ''}
                        </td>

                        <td>
                            <strong>
                                ${entry.route ? `${entry.route.origin ?? ''} (${entry.route.origin_code ?? ''}) → ${entry.route.destination ?? ''} (${entry.route.destination_code ?? ''})` : `${entry.origin ?? ''} → ${entry.destination ?? ''}`
                                }
                            </strong>
                        </td>

                        <td>
                            ${entry.cabin ? entry.cabin.name : '' }
                        </td>

                        <td>
                            ${entry.fare_source ? entry.fare_source.name : '' }
                        </td>

                        <td>
                            ${entry.tour_code ?? ''}
                            ${entry.pcc_iata_ref ?? ''}
                        </td>

                        <td>
                            ${entry.published ?? ''}
                        </td>

                        <td>
                            ${parseFloat(entry.airline?.au_commission ?? 0).toFixed(2)}%
                        </td>

                        <td>
                            ${entry.net ?? ''}
                        </td>

                        <td>
                            ${entry.gross ?? ''}
                        </td>

                        <td>
                            ${margin.toFixed(2)}
                        </td>

                        <td>
                            ${formatValidUntil(entry.valid_until)}
                        </td>

                        <td>
                            ${entry.status ?? ''}
                        </td>

                    </tr>
                `;
            });
        })

        .catch(error => {

            console.error(
                'Fare Search Error:',
                error
            );

        });
    });
}

// helper functions 
function capitalize(value) {
    if (!value) return '';

    return value.charAt(0).toUpperCase() + value.slice(1);
}


function formatDateTime(date) {
    if (!date) return '';

    return new Date(date).toLocaleString();
}

//history panel open/close
document.addEventListener('click', function (e) {

    if (e.target.closest('.view-history')) {

        const button = e.target.closest('.view-history');

        const id = button.dataset.id;

        const panel = document.getElementById('historyPanel');
        const overlay = document.getElementById('historyOverlay');
        const body = document.getElementById('historyBody');

        // Open panel
        panel.classList.add('active');
        overlay.classList.add('active');

        // Loading
        body.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border spinner-border-sm"></div>
                <p class="mt-2 text-muted">
                    Loading history...
                </p>
            </div>
        `;

        fetch(`/fare-commission-entries/${id}/history`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {

            if (!response.ok) {
                throw new Error('Failed to load history');
            }

            return response.json();
        })
        .then(data => {

            if (!data.success) {
                throw new Error('History not found');
            }

            renderHistory(data.history);
        })
        .catch(error => {

            console.error('History error:', error);

            body.innerHTML = `
                <div class="alert alert-danger">
                    Failed to load history.
                </div>
            `;
        });
    }
});

function renderHistory(history) {

    const body = document.getElementById('historyBody');

    if (!history.length) {

        body.innerHTML = `
            <div class="text-center py-5 text-muted">
                <i class="bi bi-clock-history fs-2"></i>

                <p class="mt-2 mb-0">
                    No history found.
                </p>
            </div>
        `;

        return;
    }

    body.innerHTML = history.map(log => {

        let actionClass = 'secondary';
        let actionIcon = 'bi-clock';

        if (log.action === 'created') {
            actionClass = 'success';
            actionIcon = 'bi-plus-circle';
        }

        if (log.action === 'updated') {
            actionClass = 'primary';
            actionIcon = 'bi-pencil-square';
        }

        if (log.action === 'deleted') {
            actionClass = 'danger';
            actionIcon = 'bi-trash';
        }

        return `
            <div class="history-event">

                <div class="history-event-header">

                    <span class="badge bg-${actionClass}">
                        <i class="bi ${actionIcon}"></i>
                        ${capitalize(log.action)}
                    </span>

                    <span class="history-date">
                        ${formatDateTime(log.created_at)}
                        <span class="history-user">
                            by ${log.user?.name || 'Unknown User'}
                        </span>
                    </span>

                </div>

                ${renderHistoryValues(log)}

            </div>
        `;

    }).join('');
}

function renderHistoryValues(log) {

    if (log.action === 'created') {
        return `
            <div class="history-summary created">
                Record created
            </div>
        `;
    }

    if (log.action === 'deleted') {
        return `
            <div class="history-summary deleted">
                Record deleted
            </div>
        `;
    }

    if (log.action === 'updated' && log.old_values && log.new_values)
    {
        const oldValues = typeof log.old_values === 'string'? JSON.parse(log.old_values): log.old_values;

        const newValues = typeof log.new_values === 'string'? JSON.parse(log.new_values): log.new_values;

        let changes = '';

        Object.keys(newValues).forEach(key => {

            const oldRawValue = oldValues[key] ?? '-';
            const newRawValue = newValues[key] ?? '-';

            let oldValue = oldRawValue;
            let newValue = newRawValue;

            if (key === 'valid_until' || key === 'created_at' || key === 'updated_at' || key === 'travel_from' || key === 'travel_to') {
                oldValue = formatValidUntil(oldRawValue);
                newValue = formatValidUntil(newRawValue);
            }
            if (String(oldRawValue) !== String(newRawValue)) {

                const label = key
                    .replaceAll('_', ' ')
                    .replace(/\b\w/g, char => char.toUpperCase());

                changes += `
                    <div class="history-change">

                        <span class="history-field">
                            ${label}
                        </span>

                        <span class="history-old">
                            ${oldValue}
                        </span>

                        <i class="bi bi-arrow-right"></i>

                        <span class="history-new">
                            ${newValue}
                        </span>

                    </div>
                `;
            }
        });

        if (!changes) {
            return `
                <div class="text-muted small">
                    No field changes
                </div>
            `;
        }

        return `
            <div class="history-changes">
                ${changes}
            </div>
        `;
    }

    return '';
}

const closeHistoryBtn = document.getElementById('closeHistory');
const historyOverlayBtn = document.getElementById('historyOverlay');

if(closeHistoryBtn){
    document.getElementById('closeHistory').addEventListener('click', closeHistoryPanel);
}
if(historyOverlayBtn){
    document.getElementById('historyOverlay').addEventListener('click', closeHistoryPanel);
}
function closeHistoryPanel() {

    document.getElementById('historyPanel').classList.remove('active');

    document.getElementById('historyOverlay').classList.remove('active');
}

//for route add codes 
document.addEventListener('submit', function (e) {

    if (!e.target.matches('[id^="routeCodeForm"]')) {
        return;
    }

    e.preventDefault();

    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');

    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerText = 'Saving...';
    }

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',

        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },

        body: formData
    })

    .then(response => {

        if (!response.ok) {
            throw new Error('Update failed');
        }
        return response.json();

    })

    .then(data => {

        if (data.success) {

            // Message
            toast(data.message || 'Route codes updated successfully.','success');

            // Modal close
            const modalElement = form.closest('.modal');

            if (modalElement) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                modal.hide();
            }

            // Button normal state
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerText = 'Save Codes';
            }
            // Frontend route text update
            const rowId = form.dataset.rowId;
            const row = document.getElementById(rowId);

            if (row) {
                const origin = row.dataset.origin;
                const destination = row.dataset.destination;

                const originCode = data.origin_code || '';
                const destinationCode = data.destination_code || '';

                // Update dataset
                row.dataset.originCode = originCode;
                row.dataset.destinationCode = destinationCode;

                // Route cell
                const routeCell = row.querySelector('.route-code-cell');

                if (routeCell) {

                    routeCell.innerHTML = `<strong class="route-text">${origin} (${originCode}) → ${destination} (${destinationCode})</strong>${!originCode || !destinationCode? `<button type="button" class="btn btn-sm btn-outline-primary ms-2 add-route-code-btn" data-bs-toggle="modal" data-bs-target="#routeCodeModal${row.id.replace('fare-entry-row-', '')}">Add Code</button>`: ''}`;
                }
            }

            } else {

                toast(data.message || 'Update failed.','danger');
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerText = 'Save Codes';
                }
            }

    })
    .catch(error => {
        console.error('Route code update error:', error);
        toast('Something went wrong while updating route codes.','danger');

        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerText = 'Save Codes';
        }
    });

});

