document.addEventListener('DOMContentLoaded', async function() {
    console.log('Module::Search::Created');
    // Function to show search spinner
    function showSearchSpinner() {
        const searchItemsSection = document.getElementById('search-items');
        if (searchItemsSection) {
            // Remove any existing spinner
            const existingSpinner = searchItemsSection.querySelector('.spinner-overlay');
            if (existingSpinner) {
                existingSpinner.remove();
            }

            // Create spinner overlay
            const spinnerOverlay = document.createElement('div');
            spinnerOverlay.classList.add('spinner-overlay');
            spinnerOverlay.innerHTML = `
                <div class="spinner-container">
                    <div class="spinner"></div>
                </div>
            `;
            
            // Position the spinner within the search items section
            searchItemsSection.style.position = 'relative';
            searchItemsSection.appendChild(spinnerOverlay);
        }
    }

    // Function to hide search spinner
    function hideSearchSpinner() {
        const searchItemsSection = document.getElementById('search-items');
        if (searchItemsSection) {
            const spinnerOverlay = searchItemsSection.querySelector('.spinner-overlay');
            if (spinnerOverlay) {
                spinnerOverlay.remove();
            }
        }
    }

    // Get current language or default to English
    const currentLang = localStorage.getItem('locale') || 'en';
    const t = await loadTranslations(currentLang);

    const searchButton = document.querySelector('.btn-primary.btn-lg');
    const searchInput = document.querySelector('.search-input');
    const servicesGrid = document.querySelector('.items-grid');
    const servicesContainer = document.querySelector('.items-grid .row.g-4');
    const listViewButton = document.getElementById('list-view-button');
    const gridViewButton = document.getElementById('grid-view-button');
    const orderFilterButton = document.getElementById('order-filter-button');
    const serviceFilterButton = document.getElementById('service-filter-button');

    const itemsPerPage = 10;
    let currentFilter = '';

    // Function to get URL parameters
    function getUrlParams() {
        const params = new URLSearchParams(window.location.search);
        return {
            query: params.get('query') || '',
            page: parseInt(params.get('page')) || 1,
            filter: params.get('filter') || '',
            order: params.get('order') || 'date_desc'
        };
    }

    // Function to update URL parameters
    function updateUrlParams(query, page, filter, order) {
        const url = new URL(window.location.href);
        if (query) {
            url.searchParams.set('query', query);
        } else {
            url.searchParams.delete('query');
        }
        url.searchParams.set('page', page);
        if (filter) {
            url.searchParams.set('filter', filter);
        } else {
            url.searchParams.delete('filter');
        }
        if (order) {
            url.searchParams.set('order', order);
        } else {
            url.searchParams.delete('order');
        }
        window.history.pushState({}, '', url);
    }

    // Initialize filter buttons
    if (orderFilterButton && serviceFilterButton) {
        // Check if filter-toggle exists in localStorage
        if (!localStorage.getItem('filter-toggle')) {
            // Set default to service filter
            localStorage.setItem('filter-toggle', 'services');
            currentFilter = 'services';
            serviceFilterButton.classList.add('active');
        } else {
            // Load saved filter state from localStorage
            currentFilter = localStorage.getItem('filter-toggle');
            if (currentFilter === 'orders') {
                orderFilterButton.classList.add('active');
            } else if (currentFilter === 'services') {
                serviceFilterButton.classList.add('active');
            }
        }

        orderFilterButton.addEventListener('click', () => {
            if (!orderFilterButton.classList.contains('active')) {
                currentFilter = 'orders';
                orderFilterButton.classList.add('active');
                serviceFilterButton.classList.remove('active');
                localStorage.setItem('filter-toggle', currentFilter);
                const params = getUrlParams();
                ordersSearch(searchInput.value.trim(), 1, currentFilter, params.order);
            }
        });

        serviceFilterButton.addEventListener('click', () => {
            if (!serviceFilterButton.classList.contains('active')) {
                currentFilter = 'services';
                serviceFilterButton.classList.add('active');
                orderFilterButton.classList.remove('active');
                localStorage.setItem('filter-toggle', currentFilter);
                const params = getUrlParams();
                servicesSearch(searchInput.value.trim(), 1, currentFilter, params.order);
            }
        });

        // Set initial filter based on URL parameter or localStorage
        const params = getUrlParams();
        if (params.filter) {
            currentFilter = params.filter;
            if (currentFilter === 'orders') {
                orderFilterButton.classList.add('active');
            } else if (currentFilter === 'services') {
                serviceFilterButton.classList.add('active');
            }
            localStorage.setItem('filter-toggle', currentFilter);
        }
    }

    // Initialize view buttons
    if (listViewButton && gridViewButton) {
        // Check if view-toggle exists in localStorage
        if (!localStorage.getItem('view-toggle')) {
            // Set default to list view
            localStorage.setItem('view-toggle', 'list');
            servicesGrid.classList.add('list-view');
            listViewButton.classList.add('active');
        }

        listViewButton.addEventListener('click', () => {
            servicesGrid.classList.remove('grid-view');
            servicesGrid.classList.add('list-view');
            listViewButton.classList.add('active');
            gridViewButton.classList.remove('active');
            localStorage.setItem('view-toggle', 'list');
        });

        gridViewButton.addEventListener('click', () => {
            servicesGrid.classList.remove('list-view');
            servicesGrid.classList.add('grid-view');
            gridViewButton.classList.add('active');
            listViewButton.classList.remove('active');
            localStorage.setItem('view-toggle', 'grid');
        });

        // Set initial view based on stored preference
        const viewPreference = localStorage.getItem('view-toggle');
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
        return imageUrl || 'images/default-item-image.webp';
    }

    // Utility function to safely get review count
    function getReviewCount(reviewCount) {
        return Number(reviewCount) || 0;
    }

    // Function to render pagination
    function renderPagination(currentPage, totalPages) {
        // Prevent multiple pagination renders
        const existingPaginationContainer = servicesGrid.parentNode.querySelector('.pagination-container');
        if (existingPaginationContainer) {
            existingPaginationContainer.remove();
        }
        
        const paginationContainer = document.createElement('div');
        paginationContainer.className = 'pagination-container';
        
        const pagination = document.createElement('ul');
        pagination.className = 'pagination';
        
        // Prevent multiple search calls
        let isSearching = false;

        // Previous button
        const prevLi = document.createElement('li');
        const prevButton = document.createElement('button');
        prevButton.innerHTML = '<i class="fas fa-chevron-left"></i>';
        prevButton.disabled = currentPage === 1;
        prevButton.addEventListener('click', () => {
            if (isSearching) return;
            isSearching = true;
            
            const params = getUrlParams();
            if (currentFilter === 'orders') {
                ordersSearch(searchInput.value.trim(), currentPage - 1, currentFilter, params.order);
            } else {
                servicesSearch(searchInput.value.trim(), currentPage - 1, currentFilter, params.order);
            }

            setTimeout(() => { isSearching = false; }, 1000);
        });
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
                button.addEventListener('click', () => {
                    if (isSearching) return;
                    isSearching = true;
                    
                    const params = getUrlParams();
                    if (currentFilter === 'orders') {
                        ordersSearch(searchInput.value.trim(), i, currentFilter, params.order);
                    } else {
                        servicesSearch(searchInput.value.trim(), i, currentFilter, params.order);
                    }

                    setTimeout(() => { isSearching = false; }, 1000);
                });
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
        nextButton.addEventListener('click', () => {
            if (isSearching) return;
            isSearching = true;
            
            const params = getUrlParams();
            if (currentFilter === 'orders') {
                ordersSearch(searchInput.value.trim(), currentPage + 1, currentFilter, params.order);
            } else {
                servicesSearch(searchInput.value.trim(), currentPage + 1, currentFilter, params.order);
            }
            
            setTimeout(() => { isSearching = false; }, 1000);
        });
        nextLi.appendChild(nextButton);
        pagination.appendChild(nextLi);
        
        paginationContainer.appendChild(pagination);
        return paginationContainer;
    }

    // Function to perform services search
    async function servicesSearch(query, page = 1, filter = '', order = '') {
        // Update URL parameters
        updateUrlParams(query, page, filter, order);

        // Show spinner
        showSearchSpinner();

        // Fetch services from API
        fetch(`/api/v1/services/search?query=${encodeURIComponent(query)}&page=${page}&limit=${itemsPerPage}&order=${order}`, {
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
            .then(async data => {
                // Clear previous results
                servicesContainer.innerHTML = '';

                // Check if services exist
                if (!data.items || data.items.length === 0) {
                    servicesContainer.innerHTML = `
                    <div class="col-12 text-center">
                        <p class="text-muted">${t.no_services_found}</p>
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
                        : '<button class="btn-favorite" title="Add to favorite" data-item-id="" data-action="register"><i class="far fa-heart"></i></button>'}
                                        <div class="item-features mb-3">
                                            ${renderFeatures(service.features)}
                                        </div>
                                        <div class="price-booking">
                                            <div class="item-price mb-2">
                                                <span class="price">${service.price}</span>
                                            </div>
                                            <button class="btn btn-primary w-100">${t.book_now}</button>
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-share" title="${t.share}" data-item-id="${service.id}">
                                     <i class="fas fa-share-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                    servicesContainer.insertAdjacentHTML('beforeend', serviceCard);
                });

                // Remove any existing pagination
                const existingPaginationContainer = servicesGrid.parentNode.querySelector('.pagination-container');
                if (existingPaginationContainer) {
                    existingPaginationContainer.remove();
                }

                // Add pagination after the services grid if there are multiple pages
                if (data.total_pages > 1) {
                    const paginationElement = renderPagination(data.page, data.total_pages);
                    servicesGrid.parentNode.insertBefore(paginationElement, servicesGrid.nextSibling);
                }
            })
            .catch(error => {
                console.error('Search Error:', error);
                servicesContainer.innerHTML = `
                <div class="col-12 text-center">
                    <p class="text-danger">${t.error_occurred}</p>
                </div>
            `;
            })
            .finally(() => {
                // Hide spinner
                hideSearchSpinner();
            });
    }

    // Function to perform orders search
    async function ordersSearch(query, page = 1, filter = '', order = '') {
        // Update URL parameters
        updateUrlParams(query, page, 'orders', order);

        // Show spinner
        showSearchSpinner();

        // Fetch orders from API
        fetch(`/api/v1/orders/search?query=${encodeURIComponent(query)}&order=${order}&page=${page}&limit=${itemsPerPage}&filter=${filter}`, {
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
            .then(async data => {
                // Clear previous results
                servicesContainer.innerHTML = '';

                // Check if orders exist
                if (!data.items || data.items.length === 0) {
                    servicesContainer.innerHTML = `
                    <div class="col-12 text-center">
                        <p class="text-muted">${t.no_orders_found}</p>
                    </div>
                `;
                    return;
                }

                // Populate orders
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
                        : '<button class="btn-favorite" title="Add to favorite" data-item-id="" data-action="register"><i class="far fa-heart"></i></button>'}
                                        <div class="item-features mb-3">
                                            ${renderFeatures(service.features)}
                                        </div>
                                        <div class="price-booking">
                                            <div class="item-price mb-2">
                                                <span class="price">${service.price}</span>
                                            </div>
                                            <button class="btn btn-primary w-100">${t.book_now}</button>
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-share" title="${t.share}" data-item-id="${service.id}">
                                     <i class="fas fa-share-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                    servicesContainer.insertAdjacentHTML('beforeend', serviceCard);
                });

                // Remove any existing pagination
                const existingPaginationContainer = servicesGrid.parentNode.querySelector('.pagination-container');
                if (existingPaginationContainer) {
                    existingPaginationContainer.remove();
                }

                // Add pagination after the orders grid if there are multiple pages
                if (data.total_pages > 1) {
                    const paginationElement = renderPagination(data.page, data.total_pages);
                    servicesGrid.parentNode.insertBefore(paginationElement, servicesGrid.nextSibling);
                }
            })
            .catch(error => {
                console.error('Search Error:', error);
                servicesContainer.innerHTML = `
                <div class="col-12 text-center">
                    <p class="text-danger">${t.error_occurred}</p>
                </div>
            `;
            })
            .finally(() => {
                // Hide spinner
                hideSearchSpinner();
            });
    }

    if (searchButton && searchInput && servicesContainer) {
        // Check URL parameters immediately on page load
        const params = getUrlParams();
        if (params.query || params.page > 1 || params.filter || params.order) {
            searchInput.value = params.query;
            if (currentFilter === 'services') {
                servicesSearch(params.query, params.page, params.filter, params.order);
            } else if (currentFilter === 'orders') {
                ordersSearch(params.query, params.page, params.filter, params.order);
            }
        }

        searchButton.addEventListener('click', () => {
            const query = searchInput.value.trim();
            const params = getUrlParams();
            if (currentFilter === 'services') {
                servicesSearch(query, 1, currentFilter, params.order);
                updateUrlParams(query, 1, currentFilter, params.order);
            } else if (currentFilter === 'orders') {
                ordersSearch(query, 1, currentFilter, params.order);
                updateUrlParams(query, 1, currentFilter, params.order);
            }
        });

        // Add event listener for 'Enter' key
        searchInput.addEventListener('keyup', (event) => {
            if (event.key === 'Enter') {
                const query = searchInput.value.trim();
                const params = getUrlParams();
                if (currentFilter === 'services') {
                    servicesSearch(query, 1, currentFilter, params.order);
                } else if (currentFilter === 'orders') {
                    ordersSearch(query, 1, currentFilter, params.order);
                }
                updateUrlParams(query, 1, currentFilter, params.order);
            }
        });
    }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        const params = getUrlParams();
        searchInput.value = params.query || '';
        currentFilter = params.filter || '';
        if (currentFilter === 'orders') {
            ordersSearch(params.query, params.page, currentFilter, params.order);
        } else {
            servicesSearch(params.query, params.page, currentFilter, params.order);
        }
    });

    // Handle search example clicks
    const searchExamples = document.querySelectorAll('.search-example-link');

    searchExamples.forEach(example => {
        example.addEventListener('click', function(e) {
            e.preventDefault();
            searchInput.value = this.textContent.trim();
            searchInput.focus();
        });
    });
});
