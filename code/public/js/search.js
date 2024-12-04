document.addEventListener('DOMContentLoaded', function() {
    const searchButton = document.querySelector('.btn-primary.btn-lg');
    const searchInput = document.querySelector('.form-control.form-control-lg');
    const servicesGrid = document.querySelector('.services-grid .row.g-4');

    // Utility function to safely render features
    function renderFeatures(features) {
        if (!Array.isArray(features) || features.length === 0) {
            return '<li class="text-muted">No features available</li>';
        }
        return features.map(feature => 
            `<li><i class="fas fa-check text-success me-2"></i>${feature}</li>`
        ).join('');
    }

    // Utility function to render star rating
    function renderStarRating(rating) {
        const numRating = Number(rating) || 0;
        return {
            filledStars: '★'.repeat(Math.min(Math.max(numRating, 0), 5)),
            emptyStars: '☆'.repeat(Math.max(5 - numRating, 0))
        };
    }

    // Utility function to safely get image URL
    function getImageUrl(imageUrl) {
        return imageUrl || '/path/to/default-image.jpg';
    }

    // Utility function to safely get review count
    function getReviewCount(reviewCount) {
        return Number(reviewCount) || 0;
    }

    if (searchButton && searchInput && servicesGrid) {
        searchButton.addEventListener('click', function() {
            const searchTerm = searchInput.value.trim();

            // Show loading state
            servicesGrid.innerHTML = `
                <div class="col-12 text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;

            // Fetch services from API
            fetch(`/api/service/search?query=${encodeURIComponent(searchTerm)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(services => {
                // Clear previous results
                servicesGrid.innerHTML = '';

                // Check if services exist
                if (services.length === 0) {
                    servicesGrid.innerHTML = `
                        <div class="col-12 text-center">
                            <p class="text-muted">No services found matching your search.</p>
                        </div>
                    `;
                    return;
                }

                // Populate services grid
                services.forEach(service => {
                    const { filledStars, emptyStars } = renderStarRating(service.rating);
                    const serviceCard = `
                        <div class="col-12 px-0">
                            <div class="card service-card">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <!-- Left column: Image and Reviews -->
                                        <div class="col-2 col-md-2">
                                            <div class="service-image-container position-relative">
                                                <img src="${getImageUrl(service.image_url)}" 
                                                     class="img-fluid rounded service-image" 
                                                     alt="${service.title || 'Service Image'}">
                                            </div>
                                            <div class="service-reviews mt-2 text-center">
                                                <span class="text-warning">
                                                    ${filledStars}${emptyStars}
                                                </span>
                                                <small class="d-block">(${getReviewCount(service.review_count)} reviews)</small>
                                            </div>
                                        </div>

                                        <!-- Center column: Service Details -->
                                        <div class="col-6 col-md-6">
                                            <h3 class="card-title">${service.title || 'Unnamed Service'}</h3>
                                            <p class="card-text">${service.description || 'No description available'}</p>
                                            <div class="service-meta mt-3">
                                                <span class="badge bg-secondary me-2">${service.category || 'Uncategorized'}</span>
                                            </div>
                                        </div>

                                        <!-- Right column: Features and Price -->
                                        <div class="col-4 col-md-4 text-end">
                                            <div class="service-features mb-3">
                                                <ul class="list-unstyled">
                                                    ${renderFeatures(service.features)}
                                                </ul>
                                            </div>
                                            <div class="service-pricing">
                                                <h4 class="text-primary">${service.price}</h4>
                                                <button class="btn btn-outline-primary mt-2">Book Now</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    servicesGrid.insertAdjacentHTML('beforeend', serviceCard);
                });
            })
            .catch(error => {
                console.error('Search Error:', error);
                servicesGrid.innerHTML = `
                    <div class="col-12 text-center">
                        <p class="text-danger">An error occurred while searching. Please try again later.</p>
                    </div>
                `;
            });
        });
    }
});
