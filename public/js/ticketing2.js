
// ============================================================
// DATA — this runs entirely in-browser for now (JS arrays, no backend yet).
// Real field values sourced from: Air India private fare trade circular (AUSNT007, valid to 30 Sep '26),
// Air India BSP commission circular (1% eff 1 Jun 2025), Malaysia Airlines Reservations & Ticketing
// Guidelines (June 2026 v2 — 2% AU/NZ/SWP commission), and the AU IATA PCC 8T03 commission master list.
// A future step: move this into Laravel + MySQL with role-based API access (Manager can write,
// Ticketing Team is read-only) so the data persists and is shared across the whole team, not just this browser.
// ============================================================

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const TODAY = new Date("2026-08-18");
document.getElementById("topbar-date").textContent = TODAY.toLocaleDateString("en-AU", { day: "2-digit", month: "short", year: "numeric" });

function daysUntil(dateStr) { return Math.round((new Date(dateStr) - TODAY) / 86400000); }
function statusFor(f) { const d = daysUntil(f.validUntil); if (d < 0) return "expired"; if (d <= 7) return "soon"; return "active"; }
function statusBadge(status) {
  const map = { active: "Active", soon: "Expiring soon", expired: "Expired" };
  return `<span class="badge-status ${status}"><span class="dot"></span>${map[status]}</span>`;
}
function fmtDate(dateStr) { return new Date(dateStr).toLocaleDateString("en-AU", { day: "2-digit", month: "short", year: "numeric" }); }
function money(n, currency) { return `${currency || "AUD"} ${Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`; }

function calcFare(f) {
  const net = f.published * (1 - f.commissionPct / 100);
  const gross = net + f.markup;
  return { net, gross, margin: gross - net };
}

function sourceBadge(source) {
  return source === "private" ? `<span class="badge-source private">Private / Tour Code</span>` : `<span class="badge-source bsp">IATA/BSP Published</span>`;
}

function toast(message, type) {
  const stack = document.getElementById("toast-stack");
  const el = document.createElement("div");
  el.className = "toast" + (type === "danger" ? " danger" : "");
  el.innerHTML = `<span class="dot"></span>${message}`;
  stack.appendChild(el);
  setTimeout(() => { el.style.opacity = "0"; el.style.transition = "opacity .25s"; setTimeout(() => el.remove(), 250); }, 2600);
}

// ---------------- Ticketing Manager tab ----------------

// function renderManagerTable() {
//   const body = document.getElementById("manager-table-body");
//   if (!mockFares.length) { body.innerHTML = `<tr class="empty-row"><td colspan="12">No fares yet — add the first one above.</td></tr>`; return; }
//   body.innerHTML = mockFares.map(f => {
//     const c = calcFare(f);
//     return `<tr>
//       <td>${f.airline}</td>
//       <td class="route">${f.origin} → ${f.destination}</td>
//       <td>${f.cabin}</td>
//       <td>${sourceBadge(f.source)}</td>
//       <td class="num">${money(f.published, f.currency)}</td>
//       <td class="num">${f.commissionPct}%</td>
//       <td class="num">${money(c.net, f.currency)}</td>
//       <td class="num">${money(f.markup, f.currency)}</td>
//       <td class="num">${money(c.gross, f.currency)}</td>
//       <td>${fmtDate(f.validUntil)}</td>
//       <td>${statusBadge(statusFor(f))}</td>
//       <td class="row-actions">
//         <button class="btn small edit-btn" data-id="${f.id}">Edit</button>
//         <button class="btn small danger delete-btn" data-id="${f.id}">Delete</button>
//       </td>
//     </tr>`;
//   }).join("");
//   body.querySelectorAll(".edit-btn").forEach(b => b.addEventListener("click", () => loadFareIntoForm(b.dataset.id)));
//   body.querySelectorAll(".delete-btn").forEach(b => b.addEventListener("click", () => deleteFare(b.dataset.id)));
// }

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

["f-published", "f-commission-pct", "f-markup", "f-currency"].forEach(id => document.getElementById(id).addEventListener("input", updateCalcPreview));

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
    const [year, month, day] = dateString.split('-');

    const months = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];

    return `${day} ${months[parseInt(month) - 1]} ${year}`;
}
document.getElementById("fare-form-cancel").addEventListener("click", resetForm);
//edit -cancel -save buttons
document.addEventListener('click', function(e) {

    // EDIT
    if (e.target.classList.contains('edit-commission')) {

        const button = e.target;
        const id = button.dataset.id;

        const row = document.getElementById('commission-row-' + id);

        const airline = row.querySelector('.airline-text').innerText.trim();
        const code = row.querySelector('.code-text').innerText.trim();
        const numeric = row.querySelector('.numeric-text').innerText.trim();

        const au = row.querySelector('.au-text')
            .innerText.replace('%', '').trim();

        const exau = row.querySelector('.exau-text')
            .innerText.replace('%', '').trim();


        // ORIGINAL VALUES SAVE KARO
        row.dataset.airline = airline;
        row.dataset.code = code;
        row.dataset.numeric = numeric;
        row.dataset.au = au;
        row.dataset.exau = exau;


        // Replace text with inputs
        row.cells[0].innerHTML =
            `<input type="text" class="edit-input" value="${airline}">`;

        row.cells[1].innerHTML =
            `<input type="text" class="edit-input" value="${code}">`;

        row.cells[2].innerHTML =
            `<input type="text" class="edit-input" value="${numeric}">`;

        row.cells[3].innerHTML =
            `<input type="number" step="0.01" class="edit-input" value="${au}">`;

        row.cells[4].innerHTML =
            `<input type="number" step="0.01" class="edit-input" value="${exau}">`;


        // Edit -> Save
        button.innerText = 'Save';

        button.classList.remove('edit-commission');
        button.classList.add('save-commission');


        // =========================
        // CANCEL BUTTON
        // =========================

        const cancelButton = document.createElement('button');

        cancelButton.type = 'button';
        cancelButton.className = 'btn cancel-commission';
        cancelButton.dataset.id = id;
        cancelButton.innerText = 'Cancel';

        button.parentNode.appendChild(cancelButton);
    }

    // CANCEL
    else if (e.target.classList.contains('cancel-commission')) {

        const button = e.target;
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

        saveButton.innerText = 'Edit';

        saveButton.classList.remove('save-commission');
        saveButton.classList.add('edit-commission');

        // Cancel button remove
        button.remove();
    }
    // SAVE
    else if (e.target.classList.contains('save-commission')) {

        const button = e.target;
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

            return response.text();
        })
        .then(data => {

            console.log('Updated successfully');

            location.reload();

        })
        .catch(error => {

            console.error(error);

        });

    }

});

//----------------- Fare entry table edit/save/cancel -----------------
document.addEventListener('click', function(e) {

    // =========================
    // EDIT
    // =========================
    if (e.target.classList.contains('edit-fare-entry')) {

        const button = e.target;
        const id = button.dataset.id;

        const row = document.getElementById('fare-entry-row-' + id);

        const airline = row.querySelector('.airline-text').innerText.trim();
        const cabin = row.querySelector('.cabin-text').innerText.trim();
        const source = row.querySelector('.source-text').innerText.trim();

        const published = row.querySelector('.published-text').innerText.replace('AUD', '').replace(/,/g, '').trim();
        const discComm = row.querySelector('.disc-comm-text').innerText.replace('%', '').trim();
        const net = row.querySelector('.net-text').innerText.replace('AUD', '').replace(/,/g, '').trim();
        const markup = row.querySelector('.markup-text').innerText.replace('AUD', '').replace(/,/g, '').trim();
        const gross = row.querySelector('.gross-text').innerText.replace('AUD', '').replace(/,/g, '').trim();

        const validUntil = row.querySelector('.valid-until-value').innerText.trim();

        const status =
            row.querySelector('.status-text').innerText.trim();


        // Save original values
        row.dataset.airline = airline;
        row.dataset.cabin = cabin;
        row.dataset.source = source;
        row.dataset.published = published;
        row.dataset.discComm = discComm;
        row.dataset.net = net;
        row.dataset.markup = markup;
        row.dataset.gross = gross;
        row.dataset.validUntil = validUntil;
        row.dataset.status = status;


        // Make editable
        row.cells[0].innerHTML =
            `<input type="text" class="edit-input" value="${airline}">`;

        row.cells[2].innerHTML =
            `<input type="text" class="edit-input" value="${cabin}">`;

        row.cells[3].innerHTML =
            `<input type="text" class="edit-input" value="${source}">`;

        row.cells[4].innerHTML =
            `<input type="number" step="0.01" class="edit-input" value="${published}">`;

        row.cells[5].innerHTML =
            `<input type="number" step="0.1" class="edit-input" value="${discComm}">`;

        row.cells[6].innerHTML =
            `<input type="number" step="0.01" class="edit-input" value="${net}">`;

        row.cells[7].innerHTML =
            `<input type="number" step="0.01" class="edit-input" value="${markup}">`;

        row.cells[8].innerHTML =
            `<input type="number" step="0.01" class="edit-input" value="${gross}">`;

        row.cells[9].innerHTML =
            `<input type="date" class="edit-input" value="${validUntil}">`;

        row.cells[10].innerHTML =
            `<input type="text" class="edit-input" value="${status}">`;


        // Edit -> Save
        button.innerText = 'Save';

        button.classList.remove('edit-fare-entry');
        button.classList.add('save-fare-entry');


        // Cancel button
        const cancelButton = document.createElement('button');

        cancelButton.type = 'button';
        cancelButton.className = 'btn cancel-fare-entry';
        cancelButton.dataset.id = id;
        cancelButton.innerText = 'Cancel';

        button.parentNode.appendChild(cancelButton);
    }


    // =========================
    // CANCEL
    // =========================
    else if (e.target.classList.contains('cancel-fare-entry')) {

        const button = e.target;
        const id = button.dataset.id;

        const row =document.getElementById('fare-entry-row-' + id);

        const validUntil = row.dataset.validUntil;

        // Restore original values
        row.cells[0].innerHTML =
            `<span class="airline-text">${row.dataset.airline}</span>`;

        row.cells[2].innerHTML =
            `<span class="cabin-text">${row.dataset.cabin}</span>`;

        row.cells[3].innerHTML =
            `<span class="source-text">${row.dataset.source}</span>`;

        row.cells[4].innerHTML =
            `<span class="published-text">${row.dataset.published}</span>`;

        row.cells[5].innerHTML =
            `<span class="disc-comm-text">${row.dataset.discComm}</span>%`;

        row.cells[6].innerHTML =
            `<span class="net-text">${row.dataset.net}</span>`;

        row.cells[7].innerHTML =
            `<span class="markup-text">${row.dataset.markup}</span>`;

        row.cells[8].innerHTML =
            `<span class="gross-text">${row.dataset.gross}</span>`;

        row.cells[9].innerHTML =
            `<span class="valid-until-text">${formatValidUntil(row.dataset.validUntil)}</span>`;

        row.cells[10].innerHTML =
            `<span class="status-text">${row.dataset.status}</span>`;


        // Save -> Edit
        const saveButton =
            row.querySelector('.save-fare-entry');

        saveButton.innerText = 'Edit';

        saveButton.classList.remove('save-fare-entry');
        saveButton.classList.add('edit-fare-entry');


        // Remove Cancel
        button.remove();
    }


    // =========================
    // SAVE
    // =========================
    else if (e.target.classList.contains('save-fare-entry')) {

        const button = e.target;
        const id = button.dataset.id;

        const row =
            document.getElementById('fare-entry-row-' + id);

        const inputs =
            row.querySelectorAll('.edit-input');


        const airline = inputs[0].value;
        const cabin = inputs[1].value;
        const source = inputs[2].value;
        const published = inputs[3].value;
        const discComm = inputs[4].value;
        const net = inputs[5].value;
        const markup = inputs[6].value;
        const gross = inputs[7].value;
        const validUntil = inputs[8].value;
        const status = inputs[9].value;


        fetch('/fare-commission-entries/' + id, {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },

            body: JSON.stringify({

                _method: 'PUT',

                airline: airline,
                cabin: cabin,
                source: source,
                published: published,
                disc_comm: discComm,
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

            return response.text();

        })
        .then(data => {

            console.log('Updated successfully');

            location.reload();

        })
        .catch(error => {

            console.error('Update error:', error);

        });
    }


    // =========================
    // DELETE
    // =========================
    if (e.target.classList.contains('delete-fare-entry')) {

        const id = e.target.dataset.id;

        deleteFareEntry(id);
    }

});

//delete fare entry
function deleteFareEntry(id) {

    if (!confirm('Are you sure you want to delete this fare entry?')) {
        return;
    }

    fetch('/fare-commission-entries/' + id, {

        method: 'DELETE',

        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }

    })
    .then(async response => {

        const text = await response.text();

        console.log('Delete status:', response.status);
        console.log('Delete response:', text);

        if (!response.ok) {
            throw new Error('Delete failed');
        }

        return JSON.parse(text);
    })
    .then(data => {

        console.log('Deleted successfully');

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

// Commission master — editable inline (Manager tab only)
function renderMasterTable() {
  const q = (document.getElementById("master-search").value || "").trim().toLowerCase();
  const body = document.getElementById("master-table-body");
  const rows = commissionMaster.filter(m => !q || `${m.airline} ${m.code}`.toLowerCase().includes(q));
  if (!rows.length) { body.innerHTML = `<tr class="empty-row"><td colspan="6">No carriers match your search.</td></tr>`; return; }
  body.innerHTML = rows.map(m => {
    const idx = commissionMaster.indexOf(m);
    return `<tr data-idx="${idx}">
      <td>${m.airline}</td><td>${m.code}</td><td class="muted-cell">${m.numeric}</td>
      <td class="num master-au-cell">${m.auCommission}${typeof m.auCommission === "number" ? "%" : ""}</td>
      <td class="num">${m.exCommission}${typeof m.exCommission === "number" ? "%" : ""}</td>
      <td><button class="btn small master-edit-btn" data-idx="${idx}">Edit</button></td>
    </tr>`;
  }).join("");
  body.querySelectorAll(".master-edit-btn").forEach(b => b.addEventListener("click", () => startInlineMasterEdit(b.dataset.idx)));
}

function startInlineMasterEdit(idx) {
  const m = commissionMaster[idx];
  const row = document.querySelector(`#master-table-body tr[data-idx="${idx}"]`);
  if (!row) return;
  const cell = row.querySelector(".master-au-cell");
  const actionCell = row.lastElementChild;
  cell.innerHTML = `<input type="text" class="inline-edit-input" value="${m.auCommission}">`;
  actionCell.innerHTML = `<div class="row-actions">
      <button class="btn small primary master-save-btn">Save</button>
      <button class="btn small master-cancel-btn">Cancel</button>
    </div>`;
  const input = cell.querySelector("input");
  input.focus(); input.select();
  actionCell.querySelector(".master-save-btn").addEventListener("click", () => {
    const val = input.value.trim();
    m.auCommission = isNaN(Number(val)) || val === "" ? val : Number(val);
    toast(`Commission updated — ${m.airline} now ${m.auCommission}${typeof m.auCommission === "number" ? "%" : ""}`);
    refreshAll();
  });
  actionCell.querySelector(".master-cancel-btn").addEventListener("click", renderMasterTable);
  input.addEventListener("keydown", (e) => { if (e.key === "Enter") actionCell.querySelector(".master-save-btn").click(); if (e.key === "Escape") renderMasterTable(); });
}
document.getElementById("master-search").addEventListener("input", renderMasterTable);

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

function renderTicketingStats() {
  const active = mockFares.filter(f => statusFor(f) === "active");
  const soon = mockFares.filter(f => statusFor(f) === "soon");
  const avgComm = mockFares.length ? (mockFares.reduce((s, f) => s + f.commissionPct, 0) / mockFares.length) : 0;
  const totalMargin = active.concat(soon).reduce((s, f) => s + calcFare(f).margin, 0);
  document.getElementById("tt-stat-active").textContent = active.length;
  document.getElementById("tt-stat-expiring").textContent = soon.length;
  document.getElementById("tt-stat-avgcomm").textContent = avgComm.toFixed(1) + "%";
  document.getElementById("tt-stat-margin").textContent = "AUD " + totalMargin.toLocaleString(undefined, { maximumFractionDigits: 0 });
}

function renderTicketingTable() {
  const body = document.getElementById("ticketing-table-body");
  const rows = getTicketingRows();
  if (!rows.length) { body.innerHTML = `<tr class="empty-row"><td colspan="12">No fares match your filters.</td></tr>`; return; }
  body.innerHTML = rows.map(f => {
    const c = calcFare(f);
    return `<tr>
      <td>${f.airline}</td>
      <td class="route">${f.origin} → ${f.destination}</td>
      <td>${f.cabin}</td>
      <td>${sourceBadge(f.source)}</td>
      <td class="muted-cell">${f.tourCode || f.pcc ? (f.tourCode + (f.pcc ? " · " + f.pcc : "")) : "—"}</td>
      <td class="num">${money(f.published, f.currency)}</td>
      <td class="num">${f.commissionPct}%</td>
      <td class="num">${money(c.net, f.currency)}</td>
      <td class="num">${money(c.gross, f.currency)}</td>
      <td class="num">${money(c.margin, f.currency)}</td>
      <td>${fmtDate(f.validUntil)}</td>
      <td>${statusBadge(statusFor(f))}</td>
    </tr>`;
  }).join("");
}
["tt-search", "tt-filter-source", "tt-filter-status", "tt-sort"].forEach(id => document.getElementById(id).addEventListener("input", renderTicketingTable));

function renderTicketingMaster() {
  const q = (document.getElementById("tt-master-search").value || "").trim().toLowerCase();
  const body = document.getElementById("tt-master-table-body");
  const rows = commissionMaster.filter(m => !q || `${m.airline} ${m.code}`.toLowerCase().includes(q));
  if (!rows.length) { body.innerHTML = `<tr class="empty-row"><td colspan="5">No carriers match your search.</td></tr>`; return; }
  body.innerHTML = rows.map(m => `<tr>
      <td>${m.airline}</td><td>${m.code}</td><td class="muted-cell">${m.numeric}</td>
      <td class="num">${m.auCommission}${typeof m.auCommission === "number" ? "%" : ""}</td>
      <td class="num">${m.exCommission}${typeof m.exCommission === "number" ? "%" : ""}</td>
    </tr>`).join("");
}
document.getElementById("tt-master-search").addEventListener("input", renderTicketingMaster);

// ---------------- Sidebar stats ----------------

function renderSidebarStats() {
  document.getElementById("sb-active").textContent = mockFares.filter(f => statusFor(f) === "active").length;
  document.getElementById("sb-expiring").textContent = mockFares.filter(f => statusFor(f) === "soon").length;
  document.getElementById("sb-carriers").textContent = new Set(mockFares.map(f => f.airline)).size;
}

// ---------------- Nav ----------------

const TOPBAR_COPY = {
  manager: { title: "Ticketing Manager", desc: "Enter the published/net fare, the IATA/BSP commission or private tour-code discount, and the agency markup — the sell fare for the Ticketing Team is calculated automatically." },
  ticketing: { title: "Ticketing Team", desc: "Everything needed to issue a ticket correctly — net fare, applicable IATA/BSP commission or private discount, sell price, and margin." }
};

// document.querySelectorAll(".nav-item").forEach(item => {
//   item.addEventListener("click", () => {
//     document.querySelectorAll(".nav-item").forEach(b => b.classList.remove("active"));
//     document.querySelectorAll(".view").forEach(v => v.classList.remove("active"));
//     item.classList.add("active");
//     document.getElementById(`view-${item.dataset.view}`).classList.add("active");
//     const copy = TOPBAR_COPY[item.dataset.view];
//     document.getElementById("topbar-title").textContent = copy.title;
//     document.getElementById("topbar-desc").textContent = copy.desc;
//   });
// });

// ---------------- Init ----------------

function refreshAll() {
  renderManagerTable();
  renderMasterTable();
  renderTicketingStats();
  renderTicketingTable();
  renderTicketingMaster();
  renderSidebarStats();
}
updateCalcPreview();
refreshAll();

const searchForm = document.getElementById('master-search-form');

if (searchForm) {
    console.log('Search form found');
    searchForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const searchInput = document.getElementById('master-search');
        const search = searchInput.value.trim();

        const url = new URL(searchForm.action, window.location.origin);

        if (search !== '') {
            url.searchParams.set('search', search);
        }

        fetch(url.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Search request failed');
            }

            return response.json();
        })
        .then(data => {
            console.log('Search result:', data);

            const tbody = document.getElementById('master-table-body');

            tbody.innerHTML = '';

            data.airlineCommissions.forEach(commission => {
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

                        <td>
                            <button
                                type="button"
                                class="btn edit-commission"
                                data-id="${commission.id}">
                                Edit
                            </button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(error => {
            console.error('Search error:', error);
        });
    });
}