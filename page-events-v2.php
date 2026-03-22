<?php
/**
 * Template Name: Events V2
 * @package Flohmarkt_Blog
 */

get_header(); ?>

<div class="events-v2-body">

<!-- HERO -->
<section class="events-v2-hero">
  <h1>Alle <span>Flohmarkt-Termine</span><br>auf einen Blick</h1>
  <p>Der größte Veranstaltungskalender für Flohmärkte, Trödelmärkte, Antikmarkt und mehr — täglich neue Events für ganz Deutschland.</p>
  <div class="hero-stats">
    <div class="hero-stat"><span class="num" id="stat-total">–</span><span class="lbl">Events gesamt</span></div>
    <div class="hero-stat"><span class="num" id="stat-cities">–</span><span class="lbl">Städte</span></div>
    <div class="hero-stat"><span class="num" id="stat-types">10</span><span class="lbl">Markttypen</span></div>
    <div class="hero-stat"><span class="num">25+</span><span class="lbl">Neue Events täglich</span></div>
  </div>
  <div class="search-bar">
    <span>🔍</span>
    <input type="text" id="search-input" placeholder="Stadt, Veranstaltung oder Markttyp suchen…">
    <button class="search-btn" onclick="doSearch()">Suchen</button>
  </div>
</section>

<!-- MAIN -->
<div class="main-wrap">
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <!-- Mini calendar -->
    <div class="sidebar-card">
      <h3>📅 Kalender</h3>
      <div class="mini-cal">
        <div class="mini-cal-header">
          <button class="mini-cal-nav" onclick="calNav(-1)">‹</button>
          <h4 id="cal-month-label"></h4>
          <button class="mini-cal-nav" onclick="calNav(1)">›</button>
        </div>
        <div class="mini-cal-grid" id="cal-days-header">
          <div class="cal-day-label">Mo</div><div class="cal-day-label">Di</div>
          <div class="cal-day-label">Mi</div><div class="cal-day-label">Do</div>
          <div class="cal-day-label">Fr</div><div class="cal-day-label">Sa</div>
          <div class="cal-day-label">So</div>
        </div>
        <div class="mini-cal-grid" id="cal-grid"></div>
      </div>
    </div>

    <!-- Market type filter -->
    <div class="sidebar-card">
      <h3>🏷️ Markttyp</h3>
      <div class="filter-group" id="type-filters"></div>
    </div>

    <!-- State filter -->
    <div class="sidebar-card">
      <h3>🗺️ Bundesland</h3>
      <select class="state-select" id="state-filter" onchange="applyFilters()">
        <option value="">Alle Bundesländer</option>
        <option>Baden-Württemberg</option><option>Bayern</option>
        <option>Berlin</option><option>Brandenburg</option>
        <option>Bremen</option><option>Hamburg</option>
        <option>Hessen</option><option>Mecklenburg-Vorpommern</option>
        <option>Niedersachsen</option><option>Nordrhein-Westfalen</option>
        <option>Rheinland-Pfalz</option><option>Saarland</option>
        <option>Sachsen</option><option>Sachsen-Anhalt</option>
        <option>Schleswig-Holstein</option><option>Thüringen</option>
      </select>
    </div>

    <!-- Quick date filters -->
    <div class="sidebar-card">
      <h3>⏱️ Zeitraum</h3>
      <div class="filter-group">
        <label><input type="radio" name="daterange" value="all" checked onchange="applyFilters()"> Alle kommenden</label>
        <label><input type="radio" name="daterange" value="today" onchange="applyFilters()"> Heute</label>
        <label><input type="radio" name="daterange" value="week" onchange="applyFilters()"> Diese Woche</label>
        <label><input type="radio" name="daterange" value="weekend" onchange="applyFilters()"> Dieses Wochenende</label>
        <label><input type="radio" name="daterange" value="month" onchange="applyFilters()"> Dieser Monat</label>
        <label><input type="radio" name="daterange" value="next30" onchange="applyFilters()"> Nächste 30 Tage</label>
      </div>
    </div>

    <!-- Extra filters -->
    <div class="sidebar-card">
      <h3>⚙️ Weitere Filter</h3>
      <div class="filter-group">
        <label><input type="checkbox" id="f-free" onchange="applyFilters()"> Eintritt frei</label>
        <label><input type="checkbox" id="f-dogs" onchange="applyFilters()"> Hunde erlaubt 🐕</label>
        <label><input type="checkbox" id="f-indoor" onchange="applyFilters()"> Hallenmarkt</label>
        <label><input type="checkbox" id="f-outdoor" onchange="applyFilters()"> Freiluftmarkt</label>
        <label><input type="checkbox" id="f-nacht" onchange="applyFilters()"> Nachtmarkt</label>
      </div>
    </div>
  </aside>

  <!-- FEED -->
  <main>
    <div class="feed-header">
      <h2>Veranstaltungen</h2>
      <div class="view-toggle">
        <button class="view-btn active" id="btn-grid" onclick="setView('grid')">⊞ Kacheln</button>
        <button class="view-btn" id="btn-list" onclick="setView('list')">☰ Liste</button>
        <button class="view-btn" id="btn-map" onclick="setView('map')">🗺 Karte</button>
      </div>
    </div>

    <div class="sort-bar">
      <span style="font-size:0.82em;color:var(--ink-l);align-self:center;">Sortieren:</span>
      <button class="sort-pill active" onclick="setSort('date',this)">📅 Datum</button>
      <button class="sort-pill" onclick="setSort('city',this)">📍 Stadt</button>
      <button class="sort-pill" onclick="setSort('type',this)">🏷️ Typ</button>
      <button class="sort-pill" onclick="setSort('size',this)">📏 Größe</button>
    </div>

    <div id="status-area"></div>

    <div id="view-grid" class="events-grid" style="display:grid"></div>
    <div id="view-list" style="display:none; display:flex; flex-direction:column; gap:12px;"></div>
    <div id="view-map" style="display:none">
      <div id="map-container">
        <div class="map-placeholder">
          <span class="map-icon">🗺️</span>
          <h3 style="margin-bottom:8px;font-family:'Playfair Display',serif;">Kartenansicht</h3>
          <p>Binden Sie Google Maps oder Leaflet.js ein und verwenden Sie die <code>lat</code>/<code>lng</code> Koordinaten aus den Event-Metadaten.</p>
        </div>
      </div>
    </div>

    <div class="load-more-wrap">
      <button class="load-more-btn" id="load-more-btn" onclick="loadMore()">Weitere Events laden</button>
    </div>
  </main>
</div>

<!-- EVENT MODAL -->
<div class="modal-overlay" id="modal-overlay" onclick="closeModal(event)">
  <div class="modal" id="modal-box" onclick="event.stopPropagation()">
    <div class="modal-header">
      <div>
        <div class="modal-type-badge" id="m-badge"></div>
        <h2 class="modal-title" id="m-title"></h2>
      </div>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body">
      <div class="modal-quick" id="m-quick"></div>
      <p class="modal-desc" id="m-desc"></p>
      <div class="modal-section">
        <h3>📋 Alle Details</h3>
        <table class="modal-table" id="m-table"></table>
      </div>
      <div class="modal-section" id="m-highlights-section">
        <h3>✨ Highlights</h3>
        <div class="modal-highlights" id="m-highlights"></div>
      </div>
      <div class="modal-section" id="m-tips-section">
        <h3>💡 Besuchertipps</h3>
        <ul class="modal-tips" id="m-tips"></ul>
      </div>
    </div>
    <div class="modal-footer">
      <a href="#" class="btn-primary" id="m-wp-link" target="_blank">📄 Vollständige Eventseite</a>
      <button class="btn-secondary" onclick="addToCalendar()">📅 In Kalender speichern</button>
      <button class="btn-secondary" onclick="shareEvent()">🔗 Teilen</button>
    </div>
  </div>
</div>

<!-- FOOTER -->
<footer class="events-v2-footer">
  <p>🛍️ Flohmarkt-Kalender Deutschland — Täglich automatisch aktualisiert<br>
  Alle Angaben ohne Gewähr. Bitte prüfen Sie die Details direkt beim Veranstalter.</p>
</footer>

</div>

<?php get_footer(); ?>
