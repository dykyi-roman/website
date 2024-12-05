document.addEventListener('DOMContentLoaded', function() {
    const searchButton = document.querySelector('.btn-primary.btn-lg');
    const searchInput = document.querySelector('.form-control.form-control-lg');
    const servicesGrid = document.querySelector('.services-grid');
    const servicesContainer = document.querySelector('.services-grid .row.g-4');
    const listViewButton = document.getElementById('list-view-button');
    const gridViewButton = document.getElementById('grid-view-button');

    // Initialize view buttons
    if (listViewButton && gridViewButton) {
        listViewButton.addEventListener('click', () => {
            servicesGrid.classList.remove('grid-view');
            servicesGrid.classList.add('list-view');
            listViewButton.classList.add('active');
            gridViewButton.classList.remove('active');
            localStorage.setItem('viewPreference', 'list');
        });

        gridViewButton.addEventListener('click', () => {
            servicesGrid.classList.remove('list-view');
            servicesGrid.classList.add('grid-view');
            gridViewButton.classList.add('active');
            listViewButton.classList.remove('active');
            localStorage.setItem('viewPreference', 'grid');
        });

        // Set initial view based on stored preference or default to list
        const viewPreference = localStorage.getItem('viewPreference') || 'list';
        if (viewPreference === 'grid') {
            servicesGrid.classList.add('grid-view');
            gridViewButton.classList.add('active');
        } else {
            servicesGrid.classList.add('list-view');
            listViewButton.classList.add('active');
        }
    }

    // Utility function to safely render features
    function renderFeatures(features) {
        if (!Array.isArray(features) || features.length === 0) {
            return '<li class="text-muted">No features available</li>';
        }
        return features.map(feature =>
            `<div class="feature-item"><i class="fas fa-check-circle text-success me-2"></i>${feature}</div>`
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

    if (searchButton && searchInput && servicesContainer) {
        searchButton.addEventListener('click', function() {
            const searchTerm = searchInput.value.trim();

            // Show loading state
            servicesContainer.innerHTML = `
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
                servicesContainer.innerHTML = '';

                // Check if services exist
                if (services.length === 0) {
                    servicesContainer.innerHTML = `
                        <div class="col-12 text-center">
                            <p class="text-muted">No services found matching your search.</p>
                        </div>
                    `;
                    return;
                }

                // Populate services
                services.forEach(service => {
                    const {filledStars, emptyStars} = renderStarRating(service.rating);
                    const serviceCard = `
                        <div class="col-12 col-md-6 col-lg-3 service-item">
                            <div class="card service-card h-100">
                                <div class="card-body">
                                    <div class="service-content">
                                        <!-- Image and Reviews -->
                                        <div class="service-image-section">
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
                                            <button class="btn btn-share" data-service-id="${service.id}">
                                                <i class="fas fa-share-alt"></i>
                                            </button>
                                        </div>

                                        <!-- Service Details -->
                                        <div class="service-details">
                                            <h3 class="card-title">${service.title || 'Unnamed Service'}</h3>
                                            <p class="card-text">${service.description || 'No description available'}</p>
                                            <div class="service-meta mt-3">
                                                <span class="badge bg-secondary me-2">${service.category || 'Uncategorized'}</span>
                                            </div>
                                        </div>

                                        <!-- Features and Price -->
                                        <div class="service-footer">
                                            ${window.appUser === 'true'
                                                ? ''
                                                : '<button class="btn-favorite" data-service-id="" data-action="register"><i class="far fa-heart"></i></button>'}
                                            <div class="service-features mb-3">
                                                ${renderFeatures(service.features)}
                                            </div>
                                            <div class="price-booking">
                                                <div class="service-price mb-2">
                                                    <span class="price">${service.price}</span>
                                                </div>
                                                <button class="btn btn-primary">Book Now</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    servicesContainer.insertAdjacentHTML('beforeend', serviceCard);
                });
            })
            .catch(error => {
                console.error('Search Error:', error);
                servicesContainer.innerHTML = `
                    <div class="col-12 text-center">
                        <p class="text-danger">An error occurred while searching. Please try again later.</p>
                    </div>
                `;
            });
        });
    }
});
