(() => {
  document.addEventListener('DOMContentLoaded', () => {
    const page = document.body.dataset.page;
    if (page === 'booking') {
      initBookingPage();
    } else if (page === 'cancel') {
      initCancelPage();
    } else if (page === 'admin') {
      initAdminPage();
    } else {
      initLandingPage();
    }
  });

  function initLandingPage() {
    // No-op for now
  }

  function initCancelPage() {
    const form = document.querySelector('.cancel-form');
    if (!form) return;
    const button = form.querySelector('button');
    form.addEventListener('submit', () => {
      if (!button) return;
      button.classList.add('is-loading');
      button.disabled = true;
    });
  }

  function initAdminPage() {
    const textarea = document.getElementById('memberCodes');
    if (textarea) {
      textarea.addEventListener('input', () => {
        textarea.style.height = 'auto';
        textarea.style.height = `${textarea.scrollHeight}px`;
      });
      textarea.dispatchEvent(new Event('input'));
    }
  }

  function initBookingPage() {
    const state = {
      selectedDate: getTodayISO(),
      availability: {},
      selections: [],
    };

    const dateStrip = document.getElementById('dateStrip');
    const slotGrid = document.getElementById('slotGrid');
    const nextButton = document.getElementById('nextButton');
    const modal = document.getElementById('memberModal');
    const closeModalBtn = document.getElementById('closeModal');
    const memberForm = document.getElementById('memberForm');
    const confirmButton = document.getElementById('confirmBooking');
    const formError = document.getElementById('formError');
    const summary = document.getElementById('selectedSummary');
    const confirmationPanel = document.getElementById('confirmationPanel');
    const confirmationMessage = document.getElementById('confirmationMessage');
    const bookAnotherBtn = document.getElementById('bookAnother');

    if (!dateStrip || !slotGrid || !nextButton || !memberForm || !confirmButton || !summary || !confirmationPanel || !confirmationMessage || !modal) {
      return;
    }

    renderDateStrip();
    loadAvailability(state.selectedDate);
    updateNextButton();

    nextButton?.addEventListener('click', () => {
      if (state.selections.length === 0) return;
      populateSummary();
      openModal();
    });

    closeModalBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (event) => {
      if (event.target === modal) {
        closeModal();
      }
    });

    memberForm?.addEventListener('input', () => {
      confirmButton.disabled = !memberForm.checkValidity();
    });

    memberForm?.addEventListener('submit', async (event) => {
      event.preventDefault();
      if (!memberForm.checkValidity() || state.selections.length === 0) return;
      formError.textContent = '';
      confirmButton.classList.add('is-loading');
      confirmButton.disabled = true;

      const memberPayload = {
        memberCode: memberForm.memberCode.value.trim().toUpperCase(),
        memberName: memberForm.memberName.value.trim(),
        memberEmail: memberForm.memberEmail.value.trim(),
      };

      try {
        const confirmations = [];
        for (const selection of state.selections) {
          const holdResponse = await fetch('/api/hold.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              date: state.selectedDate,
              hour: selection.hour,
              reformer: selection.reformer,
            }),
          });

          const holdData = await safeJson(holdResponse);
          if (!holdResponse.ok || !holdData.ok) {
            throw new Error(mapHoldError(holdData.error));
          }

          const bookResponse = await fetch('/api/book.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              holdToken: holdData.holdToken,
              memberCode: memberPayload.memberCode,
              memberName: memberPayload.memberName,
              memberEmail: memberPayload.memberEmail,
              date: state.selectedDate,
              hour: selection.hour,
              reformer: selection.reformer,
            }),
          });

          const bookData = await safeJson(bookResponse);
          if (!bookResponse.ok || !bookData.ok) {
            throw new Error(mapBookingError(bookData.error));
          }

          confirmations.push(bookData.booking);
        }

        handleSuccess(confirmations);
      } catch (error) {
        formError.textContent = error instanceof Error ? error.message : 'Unable to complete booking.';
        confirmButton.disabled = false;
      } finally {
        confirmButton.classList.remove('is-loading');
      }
    });

    bookAnotherBtn?.addEventListener('click', () => {
      window.location.reload();
    });

    function renderDateStrip() {
      if (!dateStrip) return;
      dateStrip.innerHTML = '';
      const days = buildDateRange();
      days.forEach((day) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `date-card${day.iso === state.selectedDate ? ' active' : ''}`;
        button.innerHTML = `
          <span class="day-name">${day.dayName}</span>
          <span class="day-number">${day.dayNumber}</span>
          <span class="month">${day.month}</span>
        `;
        button.addEventListener('click', () => {
          if (state.selectedDate === day.iso) return;
          state.selectedDate = day.iso;
          state.selections = [];
          renderDateStrip();
          loadAvailability(day.iso);
          updateNextButton();
        });
        dateStrip.appendChild(button);
      });
    }

    async function loadAvailability(date) {
      const tbody = slotGrid?.querySelector('tbody');
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="3" class="loading-row">Loading availability…</td></tr>';
      }

      try {
        const response = await fetch(`/api/availability.php?date=${date}`);
        const data = await safeJson(response);
        if (!response.ok || !data.ok) {
          throw new Error('Unable to load availability.');
        }
        state.availability = data.slots || {};
        renderGrid();
      } catch (error) {
        if (tbody) {
          tbody.innerHTML = `<tr><td colspan="3" class="loading-row">${error instanceof Error ? error.message : 'No slots available.'}</td></tr>`;
        }
      }
    }

    function renderGrid() {
      if (!slotGrid) return;
      const tbody = document.createElement('tbody');

      // Prime hours: 6am–midnight first, then off-peak 12am–5am at bottom
      const primeHours = [];
      for (let h = 6; h < 24; h++) primeHours.push(h);
      const offPeakHours = [];
      for (let h = 0; h < 6; h++) offPeakHours.push(h);

      const appendHourRow = (hour) => {
        const row = document.createElement('tr');
        const timeCell = document.createElement('td');
        timeCell.textContent = formatTimeLabel(hour);
        row.appendChild(timeCell);

        ['1', '2'].forEach((reformer) => {
          const cell = document.createElement('td');
          const inner = document.createElement('span');
          inner.classList.add('grid-cell');
          inner.dataset.hour = String(hour);
          inner.dataset.reformer = reformer;

          const statusKey = reformer === '1' ? 'reformer1' : 'reformer2';
          const status = state.availability?.[hour]?.[statusKey] || 'available';

          let stateClass = 'slot-available';
          let label = 'Available';
          if (status === 'booked') { stateClass = 'slot-booked'; label = 'Booked'; }
          if (status === 'hold')   { stateClass = 'slot-hold';   label = 'On Hold'; }
          inner.classList.add(stateClass);

          if (isSlotSelected(hour, reformer)) {
            inner.classList.add('slot-selected');
            label = '✓ Selected';
            if (isWholeRoomSelected(hour)) {
              inner.classList.add('slot-whole-room');
            }
          }

          inner.textContent = label;

          if (status === 'available') {
            inner.style.cursor = 'pointer';
            inner.addEventListener('click', () => toggleSelection(hour, reformer));
          }

          cell.appendChild(inner);
          row.appendChild(cell);
        });

        tbody.appendChild(row);
      };

      primeHours.forEach(appendHourRow);

      // Off-peak divider
      const dividerRow = document.createElement('tr');
      dividerRow.classList.add('off-peak-divider');
      const dividerCell = document.createElement('td');
      dividerCell.colSpan = 3;
      dividerCell.textContent = '🌙 Late night / early morning';
      dividerRow.appendChild(dividerCell);
      tbody.appendChild(dividerRow);

      offPeakHours.forEach(appendHourRow);

      slotGrid.querySelector('tbody')?.replaceWith(tbody);
    }

    function toggleSelection(hour, reformer) {
      const bothIndex = state.selections.findIndex((sel) => sel.hour === hour && sel.reformer === 'both');
      if (bothIndex >= 0) {
        state.selections.splice(bothIndex, 1);
        updateAfterSelection();
        return;
      }

      const existingIndex = state.selections.findIndex((sel) => sel.hour === hour && sel.reformer === reformer);
      if (existingIndex >= 0) {
        state.selections.splice(existingIndex, 1);
      } else {
        state.selections.push({ hour, reformer });
        const counterpart = reformer === '1' ? '2' : '1';
        const otherIndex = state.selections.findIndex((sel) => sel.hour === hour && sel.reformer === counterpart);
        if (otherIndex >= 0) {
          state.selections = state.selections.filter((sel) => sel.hour !== hour);
          state.selections.push({ hour, reformer: 'both' });
        }
      }

      updateAfterSelection();
    }

    function updateAfterSelection() {
      updateNextButton();
      renderGrid();
    }

    function updateNextButton() {
      if (!nextButton) return;
      const count = state.selections.length;
      nextButton.disabled = count === 0;
      nextButton.textContent = count > 0 ? `Next: Enter Details (${count})` : 'Next: Enter Details';
    }

    function isSlotSelected(hour, reformer) {
      return state.selections.some((sel) => sel.hour === hour && (sel.reformer === reformer || sel.reformer === 'both'));
    }

    function isWholeRoomSelected(hour) {
      return state.selections.some((sel) => sel.hour === hour && sel.reformer === 'both');
    }

    function openModal() {
      if (!modal) return;
      modal.classList.add('active');
      modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
      if (!modal) return;
      modal.classList.remove('active');
      modal.setAttribute('aria-hidden', 'true');
    }

    function populateSummary() {
      if (!summary) return;
      const list = state.selections
        .map((sel) => {
          const time = formatTimeLabel(sel.hour);
          const reformer = sel.reformer === 'both' ? 'Whole Studio' : `Reformer ${sel.reformer}`;
          return `<div class="summary-row"><strong>${time}</strong> · ${reformer}</div>`;
        })
        .join('');
      summary.innerHTML = `<p>${formatFriendlyDate(state.selectedDate)}</p>${list}`;
    }

    function handleSuccess(bookings) {
      closeModal();
      const main = document.querySelector('main');
      if (main) {
        main.hidden = true;
      }
      confirmationPanel.hidden = false;
      const lines = bookings.map((booking) => {
        const label = booking.reformer === 'both' ? 'Whole Studio' : `Reformer ${booking.reformer}`;
        return `${formatDateLabel(booking.booking_date)} — ${formatTimeLabel(Number(booking.slot_hour))} (${label})`;
      });
      confirmationMessage.innerHTML = lines.map((line) => `<div>${line}</div>`).join('');
    }

    function mapHoldError(code) {
      if (code === 'slot_taken') return 'That slot was just taken. Please pick another time.';
      return 'Unable to hold that slot.';
    }

    function mapBookingError(code) {
      if (code === 'invalid_code') return 'Member code not recognised.';
      if (code === 'hold_expired') return 'Hold expired. Please select the slot again.';
      return 'Unable to complete booking.';
    }
  }

  function buildDateRange() {
    const days = [];
    const today = new Date();
    for (let i = 0; i <= 14; i += 1) {
      const date = new Date(today);
      date.setDate(today.getDate() + i);
      days.push({
        iso: formatISODate(date),
        dayName: new Intl.DateTimeFormat('en-AU', { weekday: 'short' }).format(date),
        dayNumber: date.getDate(),
        month: new Intl.DateTimeFormat('en-AU', { month: 'short' }).format(date),
      });
    }
    return days;
  }

  function getTodayISO() {
    return formatISODate(new Date());
  }

  function formatISODate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  function formatTimeLabel(hour) {
    const date = new Date();
    date.setHours(hour, 0, 0, 0);
    return new Intl.DateTimeFormat('en-AU', {
      hour: 'numeric',
      minute: '2-digit',
    }).format(date);
  }

  function formatFriendlyDate(iso) {
    const [year, month, day] = iso.split('-').map(Number);
    const date = new Date(year, month - 1, day);
    return new Intl.DateTimeFormat('en-AU', { weekday: 'long', day: 'numeric', month: 'long' }).format(date);
  }

  function formatDateLabel(iso) {
    const [year, month, day] = iso.split('-').map(Number);
    const date = new Date(year, month - 1, day);
    return new Intl.DateTimeFormat('en-AU', { weekday: 'short', day: 'numeric', month: 'short' }).format(date);
  }

  async function safeJson(response) {
    try {
      return await response.json();
    } catch (_) {
      return {};
    }
  }
})();
