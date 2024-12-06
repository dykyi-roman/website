document.addEventListener('DOMContentLoaded', function() {
    const searchButton = document.querySelector('.btn-primary.btn-lg');
    const searchInput = document.querySelector('.form-control.form-control-lg');
    const servicesGrid = document.querySelector('.items-grid');
    const servicesContainer = document.querySelector('.items-grid .row.g-4');
    const listViewButton = document.getElementById('list-view-button');
    const gridViewButton = document.getElementById('grid-view-button');

    const itemsPerPage = 10;

    // Function to get URL parameters
    function getUrlParams() {
        const params = new URLSearchParams(window.location.search);
        return {
            query: params.get('query') || '',
            page: parseInt(params.get('page')) || 1
        };
    }

    // Function to update URL parameters
    function updateUrlParams(query, page) {
        const url = new URL(window.location.href);
        if (query) {
            url.searchParams.set('query', query);
        } else {
            url.searchParams.delete('query');
        }
        url.searchParams.set('page', page);
        window.history.pushState({}, '', url);
    }

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

    // Function to render pagination
    function renderPagination(currentPage, totalPages) {
        const paginationContainer = document.createElement('div');
        paginationContainer.className = 'pagination-container';
        
        const pagination = document.createElement('ul');
        pagination.className = 'pagination';
        
        // Previous button
        const prevLi = document.createElement('li');
        const prevButton = document.createElement('button');
        prevButton.innerHTML = '<i class="fas fa-chevron-left"></i>';
        prevButton.disabled = currentPage === 1;
        prevButton.addEventListener('click', () => performSearch(searchInput.value.trim(), currentPage - 1));
        prevLi.appendChild(prevButton);
        pagination.appendChild(prevLi);
        
        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (
                i === 1 || // First page
                i === totalPages || // Last page
                (i >= currentPage - 1 && i <= currentPage + 1) // Pages around current
            ) {
                const li = document.createElement('li');
                const button = document.createElement('button');
                button.textContent = i;
                if (i === currentPage) {
                    button.classList.add('active');
                }
                button.addEventListener('click', () => performSearch(searchInput.value.trim(), i));
                li.appendChild(button);
                pagination.appendChild(li);
            } else if (
                (i === currentPage - 2 && currentPage > 3) ||
                (i === currentPage + 2 && currentPage < totalPages - 2)
            ) {
                const li = document.createElement('li');
                const span = document.createElement('button');
                span.textContent = '...';
                span.disabled = true;
                li.appendChild(span);
                pagination.appendChild(li);
            }
        }
        
        // Next button
        const nextLi = document.createElement('li');
        const nextButton = document.createElement('button');
        nextButton.innerHTML = '<i class="fas fa-chevron-right"></i>';
        nextButton.disabled = currentPage === totalPages;
        nextButton.addEventListener('click', () => performSearch(searchInput.value.trim(), currentPage + 1));
        nextLi.appendChild(nextButton);
        pagination.appendChild(nextLi);
        
        paginationContainer.appendChild(pagination);
        return paginationContainer;
    }

    // Function to perform search
    function performSearch(query, page = 1) {
        // Update URL parameters
        updateUrlParams(query, page);

        // Show loading state
        servicesContainer.innerHTML = `
            <div class="col-12 text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;

        // Remove existing pagination if any
        const existingPagination = document.querySelector('.pagination-container');
        if (existingPagination) {
            existingPagination.remove();
        }

        // Fetch services from API
        fetch(`/api/service/search?query=${encodeURIComponent(query)}&page=${page}&limit=${itemsPerPage}`, {
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
        .then(data => {
            // Clear previous results
            servicesContainer.innerHTML = '';

            // Check if services exist
            if (!data.items || data.items.length === 0) {
                servicesContainer.innerHTML = `
                    <div class="col-12 text-center">
                        <p class="text-muted">No services found matching your search.</p>
                    </div>
                `;
                return;
            }

            // Populate services
            data.items.forEach(service => {
                const {filledStars, emptyStars} = renderStarRating(service.rating);
                const serviceCard = `
                    <div class="col-6 col-md-6 col-lg-3 item-item">
                        <div class="card item-card h-100">
                            <div class="card-body">
                                <div class="item-content">
                                    <!-- Image and Reviews -->
                                    <div class="item-image-section">
                                        <div class="item-image-container position-relative">
                                            <img src="${getImageUrl(service.image_url)}" 
                                                 class="img-fluid rounded item-image" 
                                                 alt="${service.title || 'Service Image'}">
                                        </div>
                                        <div class="item-reviews mt-2 text-center">
                                            <span class="text-warning">
                                                ${filledStars}${emptyStars}
                                            </span>
                                            <small class="d-block">(${getReviewCount(service.review_count)} reviews)</small>
                                        </div>
                                    </div>

                                    <!-- Service Details -->
                                    <div class="item-details">
                                        <h3 class="card-title"><a href="${service.url}" target="_blank">${service.title || 'Unnamed Service'}</a></h3>
                                        <p class="card-text">${service.description || 'No description available'}</p>
                                        <div class="item-meta mt-3">
                                            <span class="badge bg-secondary me-2">${service.category || 'Uncategorized'}</span>
                                        </div>
                                    </div>

                                    <!-- Features and Price -->
                                    <div class="item-footer">
                                        ${window.appUser === 'true'
                                            ? ''
                                            : '<button class="btn-favorite" data-item-id="" data-action="register"><i class="far fa-heart"></i></button>'}
                                        <div class="item-features mb-3">
                                            ${renderFeatures(service.features)}
                                        </div>
                                        <div class="price-booking">
                                            <div class="item-price mb-2">
                                                <span class="price">${service.price}</span>
                                            </div>
                                            <button class="btn btn-primary">Book Now</button>
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-share" data-item-id="${service.id}">
                                     <i class="fas fa-share-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                servicesContainer.insertAdjacentHTML('beforeend', serviceCard);
            });

            // Add pagination after the services grid
            const paginationElement = renderPagination(data.page, data.total_pages);
            servicesGrid.parentNode.insertBefore(paginationElement, servicesGrid.nextSibling);
        })
        .catch(error => {
            console.error('Search Error:', error);
            servicesContainer.innerHTML = `
                <div class="col-12 text-center">
                    <p class="text-danger">An error occurred while searching. Please try again later.</p>
                </div>
            `;
        });
    }

    if (searchButton && searchInput && servicesContainer) {
        // Check URL parameters immediately on page load
        const params = getUrlParams();
        if (params.query || params.page > 1) {
            searchInput.value = params.query;
            performSearch(params.query, params.page);
        }

        searchButton.addEventListener('click', () => performSearch(searchInput.value.trim(), 1));
        
        // Add enter key support
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch(searchInput.value.trim(), 1);
            }
        });

        // Handle browser back/forward buttons
        window.addEventListener('popstate', function() {
            const params = getUrlParams();
            searchInput.value = params.query || '';
            performSearch(params.query, params.page);
        });
    }
});
