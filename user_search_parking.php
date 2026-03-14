<?php
require_once __DIR__ . '/config_db.php';
require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_navbar.php';

// Initial fetch if no AJAX
$stmt = $pdo->query("SELECT * FROM parking_locations LIMIT 10");
$initial_parkings = $stmt->fetchAll();
?>

<div class="container py-5">
    <div class="row mb-5 justify-content-center">
        <div class="col-md-8 text-center glass-panel p-5 rounded-4 border border-primary border-opacity-25 shadow-lg">
            <h2 class="fw-bold mb-3 text-primary">Find Parking <span class="text-gradient">Near You</span></h2>
            <p class="text-secondary mb-4 fs-5">Search by city, location or parking lot name.</p>
            
            <div class="input-group input-group-lg shadow-3d rounded-pill overflow-hidden bg-surface border border-secondary border-opacity-25">
                <span class="input-group-text bg-transparent border-0 ps-4 text-primary"><i class="fa-solid fa-square-parking fa-beat"></i></span>
                <input type="text" id="searchInput" class="form-control bg-transparent border-0 shadow-none ps-2 fs-5 text-primary" placeholder="e.g. Mumbai, Airport, Downtown..." autocomplete="off">
                <button class="btn btn-primary px-4 fw-bold shadow-none m-1 rounded-pill" type="button" id="searchBtn">Search</button>
            </div>
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col">
            <h4 class="fw-bold mb-0">Available Locations</h4>
        </div>
        <div class="col-auto">
            <span class="text-muted small" id="resultCount"><?= count($initial_parkings) ?> locations found</span>
        </div>
    </div>

    <div class="row g-4" id="parkingResults">
        <?php foreach ($initial_parkings as $parking): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card-3d h-100 overflow-hidden d-flex flex-column shadow-soft">
                    <div class="parking-card-img position-relative" style="height: 180px;">
                        <img src="<?= getCityImage($parking['city']) ?>" 
                             class="w-100 h-100 object-fit-cover transition-transform" alt="<?= htmlspecialchars($parking['parking_name']) ?>">
                        <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-gradient-dark pt-5">
                            <h5 class="mb-0 text-white fw-bold"><?= htmlspecialchars($parking['parking_name']) ?></h5>
                        </div>
                    </div>
                    <div class="card-body p-4 bg-surface flex-grow-1 d-flex flex-column">
                        <div class="d-flex align-items-start mb-3 flex-grow-1">
                            <i class="fa-solid fa-location-crosshairs text-primary mt-1 me-2 fs-5"></i>
                            <div>
                                <p class="mb-0 fw-bold text-primary"><?= htmlspecialchars($parking['address']) ?></p>
                                <span class="text-secondary small fw-bold text-uppercase"><?= htmlspecialchars($parking['city']) ?></span>
                            </div>
                        </div>
                        
                        <div class="row g-2 mb-4 text-center">
                            <div class="col-6">
                                <div class="glass-panel p-2 rounded-3 border-0">
                                    <div class="small text-secondary text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 1px; font-weight: 800;">Rate</div>
                                    <div class="fw-bold text-gradient fs-5">₹<?= number_format($parking['price_per_hour'], 2) ?> <span class="fw-bold text-secondary small fs-6">/hr</span></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="glass-panel p-2 rounded-3 border-0">
                                    <div class="small text-secondary text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 1px; font-weight: 800;">Capacity</div>
                                    <div class="fw-bold text-primary fs-5"><?= $parking['total_slots'] ?> <span class="fw-bold text-secondary small fs-6">slots</span></div>
                                </div>
                            </div>
                        </div>
                        
                        <a href="<?= $base_url ?>/user_book_slot.php?parking=<?= $parking['parking_id'] ?>" class="btn-primary-3d w-100 text-decoration-none justify-content-center mt-auto">View & Book <i class="fa-solid fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const resultsContainer = document.getElementById('parkingResults');
    const resultCount = document.getElementById('resultCount');

    function performSearch() {
        const query = searchInput.value;
        const btnOriginalText = searchBtn.innerHTML;
        
        searchBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Searching...';
        searchBtn.disabled = true;

        fetch(`<?= $base_url ?>/ajax_get_parking.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                searchBtn.innerHTML = btnOriginalText;
                searchBtn.disabled = false;
                
                if (data.status === 'success') {
                    renderResults(data.data);
                } else {
                    resultsContainer.innerHTML = `<div class="col-12 text-center text-danger"><i class="fa-solid fa-triangle-exclamation fs-3 mb-2"></i><p>Error loading results: ${data.message}</p></div>`;
                }
            })
            .catch(error => {
                searchBtn.innerHTML = btnOriginalText;
                searchBtn.disabled = false;
                console.error('Error:', error);
                resultsContainer.innerHTML = `<div class="col-12 text-center text-danger"><i class="fa-solid fa-network-wired fs-3 mb-2"></i><p>Network error occurred.</p></div>`;
            });
    }

    function renderResults(parkings) {
        resultsContainer.innerHTML = '';
        resultCount.textContent = `${parkings.length} location${parkings.length !== 1 ? 's' : ''} found`;

        if (parkings.length === 0) {
            resultsContainer.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="bg-light d-inline-block p-4 rounded-circle mb-3"><i class="fa-regular fa-face-frown text-muted fa-3x"></i></div>
                    <h4 class="fw-bold text-muted">No parking lots found</h4>
                    <p class="text-muted">Try adjusting your search criteria or searching for a different city.</p>
                </div>
            `;
            return;
        }

        const getCityImageJS = (city) => {
            const c = city.toLowerCase().trim();
            const imgs = {
                'ahmedabad': 'https://images.unsplash.com/photo-1625471644753-33230623ae1c?auto=format&fit=crop&q=80&w=800',
                'surat': 'https://images.unsplash.com/photo-1595658658481-d53d3f999875?auto=format&fit=crop&q=80&w=800',
                'mumbai': 'https://images.unsplash.com/photo-1566552881510-bd019795d28b?auto=format&fit=crop&q=80&w=800',
                'pune': 'https://images.unsplash.com/photo-1584893717470-34863c0a59f6?auto=format&fit=crop&q=80&w=800',
                'delhi': 'https://images.unsplash.com/photo-1587474260584-1b3574e91819?auto=format&fit=crop&q=80&w=800',
                'bangalore': 'https://images.unsplash.com/photo-1596701062351-8c2c14d1fcd0?auto=format&fit=crop&q=80&w=800'
            };
            return imgs[c] || 'https://images.unsplash.com/photo-1506521781263-d8422e82f27a?auto=format&fit=crop&q=80&w=800';
        };

        parkings.forEach(parking => {
            const price = parseFloat(parking.price_per_hour).toFixed(2);
            const html = `
                <div class="col-md-6 col-lg-4 animate-up visible">
                    <div class="card-3d h-100 overflow-hidden d-flex flex-column shadow-soft">
                        <div class="parking-card-img position-relative" style="height: 180px;">
                            <img src="${getCityImageJS(parking.city)}" 
                                 class="w-100 h-100 object-fit-cover transition-transform" alt="${escapeHtml(parking.parking_name)}">
                            <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-gradient-dark pt-5">
                                <h5 class="mb-0 text-white fw-bold">${escapeHtml(parking.parking_name)}</h5>
                            </div>
                        </div>
                        <div class="card-body p-4 bg-surface flex-grow-1 d-flex flex-column">
                            <div class="d-flex align-items-start mb-3 flex-grow-1">
                                <i class="fa-solid fa-location-crosshairs text-primary mt-1 me-2 fs-5"></i>
                                <div>
                                    <p class="mb-0 fw-bold text-primary">${escapeHtml(parking.address)}</p>
                                    <span class="text-secondary small fw-bold text-uppercase">${escapeHtml(parking.city)}</span>
                                </div>
                            </div>
                            
                            <div class="row g-2 mb-4 text-center">
                                <div class="col-6">
                                    <div class="glass-panel p-2 rounded-3 border-0">
                                        <div class="small text-secondary text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 1px; font-weight: 800;">Rate</div>
                                        <div class="fw-bold text-gradient fs-5">₹${price} <span class="fw-bold text-secondary small fs-6">/hr</span></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="glass-panel p-2 rounded-3 border-0">
                                        <div class="small text-secondary text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 1px; font-weight: 800;">Capacity</div>
                                        <div class="fw-bold text-primary fs-5">${parking.total_slots} <span class="fw-bold text-secondary small fs-6">slots</span></div>
                                    </div>
                                </div>
                            </div>
                            
                            <a href="<?= $base_url ?>/user_book_slot.php?parking=${parking.parking_id}" class="btn-primary-3d w-100 text-decoration-none justify-content-center mt-auto">View & Book <i class="fa-solid fa-arrow-right ms-2"></i></a>
                        </div>
                    </div>
                </div>
            `;
            resultsContainer.insertAdjacentHTML('beforeend', html);
        });
    }

    function escapeHtml(unsafe) {
        return (unsafe || '').toString()
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    searchBtn.addEventListener('click', performSearch);
    
    // Auto search while typing with debounce
    let timeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            performSearch();
        }, 500);
    });
});
</script>

<?php require_once __DIR__ . '/includes_footer.php'; ?>



