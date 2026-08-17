const markBtn = document.getElementById('markBtn');
const statusEl = document.getElementById('status');

markBtn.addEventListener('click', () => {
    statusEl.textContent = "Getting your location...";

    if (!navigator.geolocation) {
        statusEl.textContent = "Geolocation is not supported by your browser.";
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            fetch('mark_attendance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ latitude: lat, longitude: lng })
            })
            .then(res => res.json())
            .then(data => {
                statusEl.textContent = data.message;
                statusEl.style.color = data.success ? "green" : "red";
            })
            .catch(() => {
                statusEl.textContent = "Something went wrong. Try again.";
                statusEl.style.color = "red";
            });
        },
        () => {
            statusEl.textContent = "Location access denied. Enable GPS/location to mark attendance.";
            statusEl.style.color = "red";
        }
    );
});

const checkoutBtn = document.getElementById('checkoutBtn');

checkoutBtn.addEventListener('click', () => {
    statusEl.textContent = "Checking out...";

    fetch('mark_checkout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        statusEl.textContent = data.message;
        statusEl.style.color = data.success ? "green" : "red";
    })
    .catch(() => {
        statusEl.textContent = "Something went wrong. Try again.";
        statusEl.style.color = "red";
    });
});