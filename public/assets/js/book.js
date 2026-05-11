/* ============================================================
   RUS CAB — book.js
   Map, routing, fare calculation, and payment modal logic
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── Init Map ─────────────────────────────────────────── */
  const map = L.map('map').setView([21.1702, 72.8311], 13);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  const routeControl = L.Routing.control({
    waypoints: [null],
    show: false,
    addWaypoints: false,
    routeWhileDragging: false,
    createMarker: () => null
  }).addTo(map);

  let pMarker = null;
  let dMarker = null;
  let currentDist = 0;

  /* Marker icons */
  const pickupIcon = L.divIcon({
    html: '<div class="map-marker-pin green"><i class="fas fa-circle-dot"></i></div>',
    className: '',
    iconSize: [32, 32],
    iconAnchor: [16, 32]
  });

  const dropIcon = L.divIcon({
    html: '<div class="map-marker-pin red"><i class="fas fa-location-dot"></i></div>',
    className: '',
    iconSize: [32, 32],
    iconAnchor: [16, 32]
  });

  /* ── Reverse Geocoding ───────────────────────────────── */
  async function getAddress(lat, lng, displayId, loaderId) {
    document.getElementById(loaderId).style.display = 'flex';
    try {
      const res  = await fetch(
        `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`
      );
      const data = await res.json();
      document.getElementById(displayId).value =
        data.display_name.split(',').slice(0, 3).join(', ');
    } catch {
      document.getElementById(displayId).value =
        `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
    }
    document.getElementById(loaderId).style.display = 'none';
  }

  /* ── Map Click Logic ─────────────────────────────────── */
  map.on('click', e => {
    if (!pMarker) {
      /* First click — set pickup */
      pMarker = L.marker(e.latlng, { icon: pickupIcon })
        .addTo(map)
        .bindPopup('<strong>Pickup</strong>')
        .openPopup();
      getAddress(e.latlng.lat, e.latlng.lng, 'p_disp', 'p_load');

    } else if (!dMarker) {
      /* Second click — set dropoff + calculate route */
      dMarker = L.marker(e.latlng, { icon: dropIcon })
        .addTo(map)
        .bindPopup('<strong>Drop-off</strong>')
        .openPopup();
      getAddress(e.latlng.lat, e.latlng.lng, 'd_disp', 'd_load');
      routeControl.setWaypoints([pMarker.getLatLng(), dMarker.getLatLng()]);

    } else {
      /* Third click — reset everything */
      map.removeLayer(pMarker);
      map.removeLayer(dMarker);
      pMarker = null;
      dMarker = null;
      currentDist = 0;
      document.getElementById('p_disp').value = '';
      document.getElementById('d_disp').value = '';
      document.getElementById('price_card').classList.remove('visible');
      routeControl.setWaypoints([null]);
    }
  });

  /* ── Route Found — calculate fare ───────────────────── */
  routeControl.on('routesfound', e => {
    currentDist = (e.routes[0].summary.totalDistance / 1000).toFixed(2);
    calculateFare();
  });

  /* ── Fare Calculation ────────────────────────────────── */
  function calculateFare() {
    if (currentDist === 0) return;

    const carSelect = document.getElementById('car_type');
    const rate      = Number(carSelect.options[carSelect.selectedIndex].dataset.rate);
    const carName   = carSelect.value;
    let   fare      = Math.round(currentDist * rate);
    if (fare < 40) fare = 40; // minimum fare

    document.getElementById('h_dist').value  = currentDist;
    document.getElementById('h_fare').value  = fare;
    document.getElementById('dist_txt').textContent  = `${currentDist} km`;
    document.getElementById('fare_txt').textContent  = `₹${fare}`;
    document.getElementById('car_label').textContent = `RUS ${carName}`;
    document.getElementById('price_card').classList.add('visible');
  }

  /* Re-calculate when vehicle type changes */
  document.getElementById('car_type').addEventListener('change', calculateFare);

  /* ── Form Submit → Payment modal check ──────────────── */
  document.getElementById('bookingForm').addEventListener('submit', e => {
    const method = document.getElementById('pay_method').value;
    const fare   = document.getElementById('h_fare').value;

    if (!fare) {
      alert('Please select a pickup and drop-off location on the map first.');
      e.preventDefault();
      return;
    }

    if (method === 'Cash') return; /* submit normally */

    e.preventDefault();
    openPaymentModal(method, fare);
  });

  /* ── Payment Modal ───────────────────────────────────── */
  function openPaymentModal(method, fare) {
    const modal   = document.getElementById('paymentModal');
    const upiUI   = document.getElementById('upi_ui');
    const cardUI  = document.getElementById('card_ui');

    modal.classList.add('open');

    if (method === 'UPI') {
      upiUI.style.display  = 'block';
      cardUI.style.display = 'none';
      document.getElementById('modal_fare_upi').textContent = fare;
      document.getElementById('qr_img').src =
        `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=upi://pay?pa=ruscab@paytm%26am=${fare}`;
    } else {
      cardUI.style.display = 'block';
      upiUI.style.display  = 'none';
      document.getElementById('modal_fare_card').textContent = fare;
    }
  }

  window.finalizeBooking = () => document.getElementById('bookingForm').submit();
  window.closeModal = () => document.getElementById('paymentModal').classList.remove('open');

  /* Close on overlay click */
  document.getElementById('paymentModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) window.closeModal();
  });

});