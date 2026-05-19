  //About
function animateValue(obj, start, end, duration) {
  let startTimestamp = null;
  const step = (timestamp) => {
    if (!startTimestamp) startTimestamp = timestamp;
    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
    obj.innerHTML = Math.floor(progress * (end - start) + start);
    if (progress < 1) {
      window.requestAnimationFrame(step);
    }
  };
  window.requestAnimationFrame(step);
}

// Function to load HTML content
function loadHTML(file, elementId) {
  fetch(file)
      .then(response => response.text())
      .then(data => document.getElementById(elementId).innerHTML = data);
}

//Open Html file
function openHtmlFile(x) {
  window.location.href = x;
}



// Function to save values and navigate
function booknow() {
  const cost = document.getElementById("cost").value;

  localStorage.setItem("cost", cost);

  // Navigate to the second page
  window.location.href = "pay.html";
  window.location.href = "final.html";
}

// Function to load values on the second page
function loadValues() {
  const price = document.getElementById("price");
  const pack = document.getElementById("pack");
  const finalAmt = document.getElementById("final_amount");

  const costt = localStorage.getItem("cost");
  const finalPrice = localStorage.getItem("final_price");

  if (price && costt) price.value = costt;
  if (finalAmt && finalPrice) finalAmt.value = finalPrice + '/-';

  const abc=parseInt(costt);
  switch(abc){
    case 4200:
      pack.value='Kuakata';
      break;
    case 7200:
      pack.value='Saint Martins Island';
      break;
    case 4600:
      pack.value='Sajek';
      break;
    case 12000:
      pack.value='Sundarbans';
      break;
    case 4500:
      pack.value='Bandarban';
      break;
    case 5600:
      pack.value="Cox's Bazar";
      break;
    case 3800:
      pack.value='Khagrachari';
      break;
    case 5000:
      pack.value='Sreemangal';
      break;
    case 21500:
      pack.value='Ladakh';
      break;
    case 19500:
      pack.value='Bali';
      break;
  }
}

function showOverlayWithSpinner() {
  const overlay = document.querySelector('.spinner-box');
  overlay.style.display = 'flex'; // Show the overlay with spinner
  setTimeout(() => {
    overlay.style.display = 'none'; // Hide it after 2 seconds
  }, 1500);
}


function promo(x){
  const promo= document.getElementById('promo');
  const coupon=document.getElementById('coupon');
  if(x=='RIR15'){
    coupon.value='-'+(price.value*0.15);
  }
}

// ===== TOUR COUNTDOWN FROM JSON =====
function loadTourCountdown(slug) {
  fetch('tour_schedules.json')
    .then(r => r.json())
    .then(data => {
      const tour = data[slug];
      if (!tour) return;

      // Set bookings count
      const bookingsEl = document.getElementById('tour-bookings');
      if (bookingsEl) bookingsEl.textContent = tour.bookings_count || 0;

      // Start live countdown
      const countdownEl = document.getElementById('tour-countdown');
      if (!countdownEl) return;

      function updateCountdown() {
        const target = new Date(tour.next_tour).getTime();
        const now = new Date().getTime();
        const diff = target - now;

        if (diff <= 0) {
          countdownEl.textContent = 'Tour Started!';
          return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const secs = Math.floor((diff % (1000 * 60)) / 1000);

        countdownEl.textContent = days + 'd ' + hours + 'h ' + mins + 'm ' + secs + 's';
      }

      updateCountdown();
      setInterval(updateCountdown, 1000);
    })
    .catch(() => {});
}

// ===== LOAD CHATBOT WIDGET =====
(function() {
  var s = document.createElement('script');
  s.src = 'chatbot_widget.js';
  s.defer = true;
  document.body.appendChild(s);
})();
