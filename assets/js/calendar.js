// assets/js/calendar.js
function initCalendar(targetId, events) {
    const root = document.getElementById(targetId);
    root.innerHTML = '';
    events.forEach(ev => {
      const el = document.createElement('div');
      el.className = 'cal-event';
      el.innerHTML = `<strong>${ev.titre}</strong><div>${ev.date_debut} → ${ev.date_fin}</div>`;
      el.style.borderLeft = '6px solid ' + (ev.couleur || 'gray');
      root.appendChild(el);
    });
  }
  