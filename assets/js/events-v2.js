// ═══════════════════════════════════════════════════════════
// DATA — WordPress REST API integration
// ═══════════════════════════════════════════════════════════
const WP_BASE    = (typeof flohmarktAjax !== 'undefined' && flohmarktAjax.siteurl) ? flohmarktAjax.siteurl : window.location.origin;
const WP_API     = `${WP_BASE}/wp-json/wp/v2/events`;
const PAGE_SIZE  = 25;

const TYPE_CONFIG = {
  'flohmarkt':           { label:'Flohmarkt',          emoji:'🛍️', color:'#c94a1e', bg:'#fde8e2', text:'#7a2910' },
  'troedelmarkt':        { label:'Trödelmarkt',         emoji:'🏷️', color:'#c9860e', bg:'#fdf0da', text:'#7a4f08' },
  'antikmarkt':          { label:'Antikmarkt',          emoji:'🏺', color:'#7b3fa0', bg:'#f3e8fb', text:'#4a1e6a' },
  'kinderflohmarkt':     { label:'Kinderflohmarkt',     emoji:'🧸', color:'#2d8a50', bg:'#e4f7ec', text:'#164a28' },
  'nachtflohmarkt':      { label:'Nachtflohmarkt',      emoji:'🌙', color:'#1e3a5f', bg:'#dce8f8', text:'#0c1e36' },
  'hofflohmarkt':        { label:'Hofflohmarkt',        emoji:'🏡', color:'#1a7a6a', bg:'#dbf3ef', text:'#0c3d35' },
  'vintage_markt':       { label:'Vintage-Markt',       emoji:'📻', color:'#a33040', bg:'#fbe4e7', text:'#5c1820' },
  'secondhand':          { label:'Secondhand',           emoji:'♻️', color:'#1e6a9e', bg:'#dceef8', text:'#0c3a58' },
  'buecher_markt':       { label:'Büchermarkt',         emoji:'📚', color:'#6d4c41', bg:'#f3e5df', text:'#3b2218' },
  'schallplatten_markt': { label:'Schallplattenmarkt',  emoji:'🎵', color:'#1a237e', bg:'#dde2f5', text:'#0c1040' }
};

// ── State ──
let allEvents    = [];
let filtered     = [];
let displayCount = PAGE_SIZE;
let currentSort  = 'date';
let currentView  = 'grid';
let selectedDate = null;
let calYear, calMonth;
let activeModal  = null;

// ── Bootstrap ──
document.addEventListener('DOMContentLoaded', () => {
  const now = new Date();
  calYear  = now.getFullYear();
  calMonth = now.getMonth();
  buildTypeFilters();
  loadEvents();
});

// ── Load events from WP REST API ──
async function loadEvents() {
  showStatus('⏳ Lade Events…');
  try {
    const url = `${WP_API}?per_page=100&status=publish&_fields=id,title,slug,link,excerpt,meta&_v=${Date.now()}`;
    console.log('Fetching events from:', url);
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) throw new Error('API error ' + res.status);
    const data = await res.json();
    console.log('Events received:', data.length);
    allEvents = data.map(transformWPEvent);
    console.log('Transformed events:', allEvents.length);
  } catch(err) {
    console.error('WP API error:', err.message);
    showStatus('❌ Fehler beim Laden der Events. API-Endpunkt nicht erreichbar.');
    return;
  }
  initStats();
  applyFilters();
  renderCalendar();
  clearStatus();
}

function transformWPEvent(wp) {
  const m = wp.meta || {};
  return {
    id:          wp.id,
    title:       wp.title?.rendered || wp.title || '',
    date:        m._event_date     || '',
    time:        m._event_time     || '09:00',
    end_time:    m._event_end_time || '17:00',
    city:        m._event_city     || '',
    zip:         m._event_zip      || '',
    state:       m._event_state    || '',
    address:     m._event_address  || '',
    venue:       m._event_venue    || '',
    lat:         parseFloat(m._event_lat) || null,
    lng:         parseFloat(m._event_lng) || null,
    organizer:   m._event_organizer || '',
    contact:     m._event_contact   || '',
    website:     m._event_website   || wp.link || '',
    ticket_price: m._event_ticket_price || '',
    seller_fee:  m._event_seller_fee || '',
    parking:     m._event_parking   || '',
    transport:   m._event_transport || '',
    dogs_allowed: m._event_dogs_allowed === '1',
    indoor_outdoor: m._event_indoor_outdoor || 'outdoor',
    accessibility: m._event_accessibility || '',
    event_type:  m._event_type     || 'flohmarkt',
    market_size: m._event_market_size || '',
    num_stands:  parseInt(m._event_num_stands) || null,
    recurring:   m._event_recurring || 'einmalig',
    description: (wp.excerpt?.rendered || '').replace(/<[^>]*>/g,'').trim(),
    wp_link:     wp.link || `${WP_BASE}/?p=${wp.id}`,
    highlights:  m._event_highlights ? m._event_highlights.split('|') : [],
    visitor_tips: m._event_tips ? m._event_tips.split('|') : []
  };
}

// ── Filter & sort ──
function applyFilters() {
  const searchInput = document.getElementById('search-input');
  if (!searchInput) return;
  const search   = (searchInput.value || '').toLowerCase();
  
  const stateFilter = document.getElementById('state-filter');
  const state    = stateFilter ? stateFilter.value : '';
  
  const dateRangeEl = document.querySelector('input[name="daterange"]:checked');
  const dateRange = dateRangeEl ? dateRangeEl.value : 'all';
  
  const onlyFree = document.getElementById('f-free')?.checked;
  const onlyDogs = document.getElementById('f-dogs')?.checked;
  const onlyIndoor  = document.getElementById('f-indoor')?.checked;
  const onlyOutdoor = document.getElementById('f-outdoor')?.checked;
  const onlyNacht   = document.getElementById('f-nacht')?.checked;

  const activeTypes = [...document.querySelectorAll('.type-cb:checked')].map(cb => cb.value);
  const today = new Date(); today.setHours(0,0,0,0);

  filtered = allEvents.filter(e => {
    // Date
    const eDate = new Date(e.date); eDate.setHours(0,0,0,0);
    // Include events from today onwards
    if (eDate < today) return false;

    // Selected calendar day
    if (selectedDate && e.date !== selectedDate) return false;

    // Date range
    if (dateRange === 'today') {
      if (e.date !== today.toISOString().slice(0,10)) return false;
    } else if (dateRange === 'week') {
      const end = new Date(today); end.setDate(end.getDate() + 6);
      if (eDate > end) return false;
    } else if (dateRange === 'weekend') {
      const day = eDate.getDay();
      if (day !== 6 && day !== 0) return false;
    } else if (dateRange === 'month') {
      if (eDate.getMonth() !== today.getMonth() || eDate.getFullYear() !== today.getFullYear()) return false;
    } else if (dateRange === 'next30') {
      const end = new Date(today); end.setDate(end.getDate() + 30);
      if (eDate > end) return false;
    }

    if (state && e.state !== state) return false;
    if (activeTypes.length && !activeTypes.includes(e.event_type)) return false;
    if (search && !(`${e.title} ${e.city} ${e.state} ${e.event_type} ${e.description}`).toLowerCase().includes(search)) return false;
    if (onlyFree   && !e.ticket_price?.toLowerCase().includes('frei')) return false;
    if (onlyDogs   && !e.dogs_allowed) return false;
    if (onlyIndoor  && e.indoor_outdoor !== 'indoor') return false;
    if (onlyOutdoor && e.indoor_outdoor !== 'outdoor') return false;
    if (onlyNacht   && e.event_type !== 'nachtflohmarkt') return false;
    return true;
  });

  // Sort
  if (currentSort === 'date')  filtered.sort((a,b) => a.date.localeCompare(b.date));
  if (currentSort === 'city')  filtered.sort((a,b) => a.city.localeCompare(b.city));
  if (currentSort === 'type')  filtered.sort((a,b) => a.event_type.localeCompare(b.event_type));
  if (currentSort === 'size')  filtered.sort((a,b) => (b.num_stands||0) - (a.num_stands||0));

  displayCount = PAGE_SIZE;
  renderEvents();
  renderCalendar();
  updateFilterCounts();
}

function renderEvents() {
  const slice  = filtered.slice(0, displayCount);
  const hasMore = filtered.length > displayCount;
  const loadMoreBtn = document.getElementById('load-more-btn');
  if (loadMoreBtn) {
    loadMoreBtn.disabled  = !hasMore;
    loadMoreBtn.textContent = hasMore ? `Weitere Events laden (${filtered.length - displayCount} verbleibend)` : 'Alle Events geladen';
  }

  if (currentView === 'grid') renderGrid(slice);
  else if (currentView === 'list') renderList(slice);
}

function renderGrid(events) {
  const el = document.getElementById('view-grid');
  if (!el) return;
  if (!events.length) { el.innerHTML = emptyState(); return; }
  el.innerHTML = events.map((e,i) => cardHTML(e,i)).join('');
}

function renderList(events) {
  const el = document.getElementById('view-list');
  if (!el) return;
  if (!events.length) { el.innerHTML = emptyState(); return; }
  el.innerHTML = events.map((e,i) => listItemHTML(e,i)).join('');
}

function cardHTML(e, i) {
  const tc   = TYPE_CONFIG[e.event_type] || TYPE_CONFIG['flohmarkt'];
  const date = formatDate(e.date);
  const delay = (i % PAGE_SIZE) * 40;
  return `<div class="event-card" style="animation-delay:${delay}ms" onclick="openModal(${e.id})">
    <div class="card-stripe" style="background:${tc.color}"></div>
    <div class="card-body">
      <div class="card-meta">
        <span class="card-type-badge" style="background:${tc.bg};color:${tc.text}">${tc.emoji} ${tc.label}</span>
        <span class="card-date-badge">${formatDateShort(e.date)}</span>
      </div>
      <div class="card-title">${e.title}</div>
      <div class="card-location">📍 ${e.city}${e.state?', <em style="font-style:normal;opacity:0.7">'+e.state+'</em>':''}</div>
      <div class="card-details">
        <span class="card-pill">⏰ ${e.time}–${e.end_time} Uhr</span>
        ${e.num_stands ? `<span class="card-pill">🏪 ~${e.num_stands} Stände</span>` : ''}
        ${e.ticket_price?.toLowerCase().includes('frei') ? '<span class="card-pill" style="background:#e8f5e9;color:#2d6a4f">✓ Eintritt frei</span>' : ''}
        ${e.dogs_allowed ? '<span class="card-pill">🐕</span>' : ''}
      </div>
      ${e.description ? `<div class="card-desc">${e.description}</div>` : ''}
    </div>
    <div class="card-footer">
      <span class="card-city-tag">📅 ${date.dow}, ${date.day}. ${date.month}</span>
      <button class="card-cta" onclick="event.stopPropagation();openModal(${e.id})">Details →</button>
    </div>
  </div>`;
}

function listItemHTML(e, i) {
  const tc  = TYPE_CONFIG[e.event_type] || TYPE_CONFIG['flohmarkt'];
  const dd  = formatDate(e.date);
  const delay = (i % PAGE_SIZE) * 30;
  return `<div class="event-list-item" style="animation-delay:${delay}ms" onclick="openModal(${e.id})">
    <div class="list-date-col">
      <div class="list-date-day">${dd.day}</div>
      <div class="list-date-month">${dd.monthShort}</div>
    </div>
    <div class="list-divider"></div>
    <div class="list-info">
      <div class="list-title">${e.title}</div>
      <div class="list-sub">📍 ${e.city}${e.state?', '+e.state:''} &bull; ⏰ ${e.time}–${e.end_time} Uhr${e.venue?' &bull; '+e.venue:''}</div>
      <div class="list-tags">
        <span class="card-type-badge" style="background:${tc.bg};color:${tc.text}">${tc.emoji} ${tc.label}</span>
        ${e.num_stands?`<span class="card-pill">~${e.num_stands} Stände</span>`:''}
        ${e.ticket_price?.toLowerCase().includes('frei')?'<span class="card-pill" style="background:#e8f5e9;color:#2d6a4f">Eintritt frei</span>':''}
      </div>
    </div>
    <div class="list-cta">
      <button class="card-cta">Details →</button>
    </div>
  </div>`;
}

function emptyState() {
  return `<div class="empty-state" style="grid-column:1/-1">
    <span class="es-icon">🔍</span>
    <h3>Keine Events gefunden</h3>
    <p style="margin-top:8px;font-size:0.88em;">Versuchen Sie andere Filter oder einen anderen Zeitraum.</p>
  </div>`;
}

// ── Mini calendar ──
function renderCalendar() {
  const calMonthLabel = document.getElementById('cal-month-label');
  if (!calMonthLabel) return;
  const label = new Date(calYear, calMonth, 1).toLocaleDateString('de-DE',{month:'long',year:'numeric'});
  calMonthLabel.textContent = label;

  const todayStr = new Date().toISOString().slice(0,10);
  const eventDates = new Set(allEvents.map(e => e.date));
  const filteredDates = new Set(filtered.map(e => e.date));

  const firstDay = new Date(calYear, calMonth, 1);
  const lastDay  = new Date(calYear, calMonth+1, 0);
  let startDow   = firstDay.getDay(); // 0=Sun
  startDow = startDow === 0 ? 6 : startDow - 1; // Mon=0

  const grid = document.getElementById('cal-grid');
  if (!grid) return;
  let html = '';

  // Padding before
  const prevLast = new Date(calYear, calMonth, 0).getDate();
  for (let p = startDow - 1; p >= 0; p--) {
    html += `<div class="cal-day other-month">${prevLast - p}</div>`;
  }

  for (let d = 1; d <= lastDay.getDate(); d++) {
    const dateStr = `${calYear}-${String(calMonth+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    const cls = [
      'cal-day',
      dateStr === todayStr ? 'today' : '',
      filteredDates.has(dateStr) ? 'has-event' : '',
      selectedDate === dateStr ? 'selected' : '',
      !filteredDates.has(dateStr) && eventDates.has(dateStr) ? 'has-event' : ''
    ].filter(Boolean).join(' ');
    html += `<div class="${cls}" onclick="selectCalDay('${dateStr}')">${d}</div>`;
  }

  // Padding after
  const totalCells = Math.ceil((startDow + lastDay.getDate()) / 7) * 7;
  for (let a = 1; a <= totalCells - startDow - lastDay.getDate(); a++) {
    html += `<div class="cal-day other-month">${a}</div>`;
  }

  grid.innerHTML = html;
}

window.calNav = function(dir) {
  calMonth += dir;
  if (calMonth > 11) { calMonth = 0; calYear++; }
  if (calMonth < 0)  { calMonth = 11; calYear--; }
  renderCalendar();
};

window.selectCalDay = function(dateStr) {
  if (selectedDate === dateStr) {
    selectedDate = null;
    document.querySelectorAll('.cal-day.selected').forEach(el => el.classList.remove('selected'));
  } else {
    selectedDate = dateStr;
  }
  const allRadio = document.querySelector('input[name="daterange"][value="all"]');
  if (allRadio) allRadio.checked = true;
  applyFilters();
};

// ── Type filter init ──
function buildTypeFilters() {
  const el = document.getElementById('type-filters');
  if (!el) return;
  el.innerHTML = Object.entries(TYPE_CONFIG).map(([k,v]) =>
    `<label>
      <input type="checkbox" class="type-cb" value="${k}" checked onchange="applyFilters()">
      <span class="type-dot" style="background:${v.color}"></span>
      ${v.emoji} ${v.label}
      <span class="count" id="cnt-${k}">–</span>
    </label>`
  ).join('');
}

function updateFilterCounts() {
  for (const type of Object.keys(TYPE_CONFIG)) {
    const el = document.getElementById('cnt-'+type);
    if (el) el.textContent = allEvents.filter(e => e.event_type === type).length;
  }
}

// ── Stats ──
function initStats() {
  const totalStat = document.getElementById('stat-total');
  if (totalStat) totalStat.textContent = allEvents.length;
  
  const citiesStat = document.getElementById('stat-cities');
  if (citiesStat) citiesStat.textContent = new Set(allEvents.map(e=>e.city)).size;
  
  const headerCount = document.getElementById('header-count');
  if (headerCount) headerCount.textContent = `${allEvents.length} Events geladen`;
}

// ── View / sort controls ──
window.setView = function(v) {
  currentView = v;
  ['grid','list','map'].forEach(id => {
    const el = document.getElementById('view-'+id);
    if (el) el.style.display = id===v ? (id==='grid'?'grid':id==='list'?'flex':'block') : 'none';
    document.getElementById('btn-'+id)?.classList.toggle('active', id===v);
  });
  if (v !== 'map') renderEvents();
};

window.setSort = function(s, btn) {
  currentSort = s;
  document.querySelectorAll('.sort-pill').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  applyFilters();
};

window.loadMore = function() {
  displayCount += PAGE_SIZE;
  renderEvents();
};

window.doSearch = function() { applyFilters(); };
const searchInput = document.getElementById('search-input');
if (searchInput) {
  searchInput.addEventListener('keyup', e => { if(e.key==='Enter') doSearch(); });
}

// ── Modal ──
window.openModal = function(id) {
  const e = allEvents.find(ev => ev.id === id);
  if (!e) return;
  activeModal = e;
  const tc = TYPE_CONFIG[e.event_type] || TYPE_CONFIG['flohmarkt'];

  const mBadge = document.getElementById('m-badge');
  if (mBadge) mBadge.innerHTML = `<span style="background:${tc.bg};color:${tc.text};padding:4px 12px;border-radius:6px;font-size:0.8em;font-weight:600;">${tc.emoji} ${tc.label}</span>`;
  
  const mTitle = document.getElementById('m-title');
  if (mTitle) mTitle.textContent = e.title;
  
  const mDesc = document.getElementById('m-desc');
  if (mDesc) mDesc.textContent = e.description || '';

  const quick = [
    { icon:'📅', text: formatDateLong(e.date) },
    { icon:'⏰', text: `${e.time}–${e.end_time} Uhr` },
    { icon:'📍', text: `${e.city}${e.state?', '+e.state:''}` },
    ...(e.venue ? [{ icon:'🏛️', text: e.venue }] : []),
    ...(e.ticket_price ? [{ icon:'🎟️', text: e.ticket_price }] : []),
    ...(e.num_stands ? [{ icon:'🏪', text: `ca. ${e.num_stands} Stände` }] : []),
    ...(e.dogs_allowed ? [{ icon:'🐕', text: 'Hunde erlaubt' }] : [])
  ];
  const mQuick = document.getElementById('m-quick');
  if (mQuick) mQuick.innerHTML = quick.map(p =>
    `<div class="modal-pill">${p.icon} ${p.text}</div>`).join('');

  const rows = [
    ['📅 Datum',       formatDateLong(e.date)],
    ['⏰ Uhrzeit',     `${e.time} – ${e.end_time} Uhr`],
    ['📍 Ort',         `${e.city}${e.state?', '+e.state:''}${e.zip?' ('+e.zip+')':''}`],
    ...(e.venue    ? [['🏛️ Veranstaltungsort', e.venue]] : []),
    ...(e.address  ? [['🏠 Adresse',  e.address]] : []),
    ...(e.organizer? [['👤 Veranstalter', e.organizer+(e.contact?' — '+e.contact:'')]] : []),
    ['🏷️ Markttyp',    tc.label],
    ...(e.ticket_price?[['🎟️ Eintritt',  e.ticket_price]] : []),
    ...(e.seller_fee?  [['💰 Standgebühr', e.seller_fee]] : []),
    ...(e.num_stands?  [['🏪 Anz. Stände', `ca. ${e.num_stands}`]] : []),
    ...(e.market_size? [['📏 Größe',  e.market_size]] : []),
    ...(e.indoor_outdoor?[['🌤️ Lage', e.indoor_outdoor==='indoor'?'Hallenmarkt':e.indoor_outdoor==='outdoor'?'Freiluftmarkt':'Teilweise überdacht']] : []),
    ['🔄 Turnus',      e.recurring || 'einmalig'],
    ...(e.parking ?    [['🅿️ Parken', e.parking]] : []),
    ...(e.transport ?  [['🚇 ÖPNV',   e.transport]] : []),
    ...(e.accessibility?[['♿ Barrierefreiheit', e.accessibility]] : [])
  ];
  const mTable = document.getElementById('m-table');
  if (mTable) mTable.innerHTML = rows.map((r,i) =>
    `<tr style="background:${i%2?'#faf8f5':'#fff'}"><td>${r[0]}</td><td>${r[1]}</td></tr>`).join('');

  // Highlights
  const mHighlightsSection = document.getElementById('m-highlights-section');
  if (mHighlightsSection) {
    if (e.highlights?.length) {
      mHighlightsSection.style.display = 'block';
      const mHighlights = document.getElementById('m-highlights');
      if (mHighlights) mHighlights.innerHTML = e.highlights.map(h =>
        `<div class="modal-highlight">⭐ ${h}</div>`).join('');
    } else {
      mHighlightsSection.style.display = 'none';
    }
  }

  // Tips
  const mTipsSection = document.getElementById('m-tips-section');
  if (mTipsSection) {
    if (e.visitor_tips?.length) {
      mTipsSection.style.display = 'block';
      const mTips = document.getElementById('m-tips');
      if (mTips) mTips.innerHTML = e.visitor_tips.map(t =>
        `<li>${t}</li>`).join('');
    } else {
      mTipsSection.style.display = 'none';
    }
  }

  const mWpLink = document.getElementById('m-wp-link');
  if (mWpLink) mWpLink.href = e.wp_link || e.website || '#';
  
  const modalOverlay = document.getElementById('modal-overlay');
  if (modalOverlay) {
    modalOverlay.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
};

window.closeModal = function(evt) {
  const modalOverlay = document.getElementById('modal-overlay');
  if (evt && evt.target !== modalOverlay && evt.type !== 'click') return;
  if (modalOverlay) {
    modalOverlay.classList.remove('open');
    document.body.style.overflow = '';
  }
  activeModal = null;
};
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

window.addToCalendar = function() {
  if (!activeModal) return;
  const e = activeModal;
  const start = `${e.date.replace(/-/g,'')}T${e.time.replace(':','')}00`;
  const end   = `${e.date.replace(/-/g,'')}T${e.end_time.replace(':','')}00`;
  const url = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(e.title)}&dates=${start}/${end}&details=${encodeURIComponent(e.description||'')}&location=${encodeURIComponent([e.address,e.venue,e.city].filter(Boolean).join(', '))}`;
  window.open(url, '_blank');
};

window.shareEvent = function() {
  if (!activeModal) return;
  const e = activeModal;
  if (navigator.share) {
    navigator.share({ title: e.title, text: `${e.title} — ${formatDateLong(e.date)} in ${e.city}`, url: e.wp_link || window.location.href });
  } else {
    navigator.clipboard.writeText(e.wp_link || window.location.href).then(() => alert('Link kopiert!'));
  }
};

// ── Status ──
function showStatus(msg) {
  const statusArea = document.getElementById('status-area');
  if (statusArea) statusArea.innerHTML = `<div class="status-banner">${msg}</div>`;
}
function clearStatus() { 
  const statusArea = document.getElementById('status-area');
  if (statusArea) statusArea.innerHTML = ''; 
}

// ── Date formatting ──
const MONTHS    = ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'];
const MONTHS_S  = ['Jan','Feb','Mär','Apr','Mai','Jun','Jul','Aug','Sep','Okt','Nov','Dez'];
const DAYS      = ['So','Mo','Di','Mi','Do','Fr','Sa'];

function formatDate(str) {
  const d = new Date(str);
  return { day: d.getDate(), month: MONTHS[d.getMonth()], monthShort: MONTHS_S[d.getMonth()], dow: DAYS[d.getDay()], year: d.getFullYear() };
}
function formatDateShort(str) {
  const d = new Date(str);
  return `${String(d.getDate()).padStart(2,'0')}.${String(d.getMonth()+1).padStart(2,'0')}.${d.getFullYear()}`;
}
function formatDateLong(str) {
  const d = new Date(str);
  return `${DAYS[d.getDay()]}, ${d.getDate()}. ${MONTHS[d.getMonth()]} ${d.getFullYear()}`;
}
