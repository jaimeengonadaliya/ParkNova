<?php
require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_navbar.php';
?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-md-8">
            <h1 class="fw-bold text-primary mb-2">Parking Locator</h1>
            <p class="text-secondary mb-0">Find the nearest ParkNova smart parking location near you.</p>
        </div>
    </div>

    <div class="card-3d overflow-hidden mb-5">
        <div id="map" style="height: 600px; width: 100%;"></div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="glass-panel p-4 h-100">
                <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-location-arrow me-2"></i>Current View</h5>
                <p class="text-secondary small">Showing parking lots in <span class="text-primary fw-bold">Surat, Gujarat</span>.</p>
                <div class="d-grid mt-4">
                    <button class="btn-3d justify-content-center" onclick="map.setCenter({lat: 21.1702, lng: 72.8311})"><i class="fa-solid fa-crosshairs me-2"></i> Recenter Map</button>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="glass-panel p-4 h-100">
                <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-list-check me-2"></i>Nearby Locations</h5>
                <div class="list-group list-group-flush bg-transparent">
                    <div class="list-group-item bg-transparent border-secondary border-opacity-10 px-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1 text-primary">Downtown Plaza Parking</h6>
                                <p class="small text-secondary mb-0">Main Street, City Center</p>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">0.5 km away</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY"></script>
<script>
let map;
function initMap() {
    map = new google.maps.Map(document.getElementById("map"), {
        center: {lat: 21.1702, lng: 72.8311},
        zoom: 13,
        styles: [
            {
                "featureType": "all",
                "elementType": "labels.text.fill",
                "stylers": [{"color": "#7c93a3"}]
            },
            {
                "featureType": "administrative",
                "elementType": "labels.text.fill",
                "stylers": [{"color": "#444444"}]
            },
            {
                "featureType": "landscape",
                "elementType": "all",
                "stylers": [{"color": "#f2f2f2"}]
            },
            {
                "featureType": "poi",
                "elementType": "all",
                "stylers": [{"visibility": "off"}]
            },
            {
                "featureType": "road",
                "elementType": "all",
                "stylers": [{"saturation": -100}, {"lightness": 45}]
            },
            {
                "featureType": "road.highway",
                "elementType": "all",
                "stylers": [{"visibility": "simplified"}]
            },
            {
                "featureType": "road.arterial",
                "elementType": "labels.icon",
                "stylers": [{"visibility": "off"}]
            },
            {
                "featureType": "transit",
                "elementType": "all",
                "stylers": [{"visibility": "off"}]
            },
            {
                "featureType": "water",
                "elementType": "all",
                "stylers": [{"color": "#4361ee"}, {"visibility": "on"}]
            }
        ]
    });

    new google.maps.Marker({
        position: {lat: 21.1702, lng: 72.8311},
        map: map,
        title: "ParkNova Parking",
        icon: {
            url: "https://maps.google.com/mapfiles/ms/icons/blue-dot.png"
        }
    });
}
window.onload = initMap;
</script>

<?php require_once __DIR__ . '/includes_footer.php'; ?>



