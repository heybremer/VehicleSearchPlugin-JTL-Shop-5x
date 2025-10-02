/**
 * Vehicle Search Plugin JavaScript for JTL Shop 5.x
 * 
 * @package Plugin\VehicleSearchPlugin\Frontend
 * @author Bremer Sitzbezüge
 * @version 1.0.0
 */

(function() {
    'use strict';

    // Plugin configuration
    const config = window.VehicleSearchPlugin || {};
    
    // DOM elements
    let searchForm, searchTypeSelect, featureModeFields, categoryModeFields;
    let brandSelect, modelSelect, typeSelect, categorySelect;
    let searchButton, loadingIndicator, searchResults, resultsContainer;

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeElements();
        bindEvents();
        loadInitialData();
    });

    /**
     * Initialize DOM elements
     */
    function initializeElements() {
        searchForm = document.getElementById('vehicleSearchForm');
        searchTypeSelect = document.getElementById('cAuswahltyp');
        featureModeFields = document.getElementById('featureModeFields');
        categoryModeFields = document.getElementById('categoryModeFields');
        brandSelect = document.getElementById('bannerVehicleBrand');
        modelSelect = document.getElementById('bannerVehicleModel');
        typeSelect = document.getElementById('bannerVehicleType');
        categorySelect = document.getElementById('categorySelect');
        searchButton = document.getElementById('searchButton');
        loadingIndicator = document.getElementById('loadingIndicator');
        searchResults = document.getElementById('searchResults');
        resultsContainer = document.getElementById('resultsContainer');
    }

    /**
     * Bind event listeners
     */
    function bindEvents() {
        if (searchTypeSelect) {
            searchTypeSelect.addEventListener('change', handleSearchTypeChange);
        }

        if (brandSelect) {
            brandSelect.addEventListener('change', handleBrandChange);
        }

        if (modelSelect) {
            modelSelect.addEventListener('change', handleModelChange);
        }

        if (categorySelect) {
            categorySelect.addEventListener('change', handleCategoryChange);
        }

        if (searchForm) {
            searchForm.addEventListener('submit', handleFormSubmit);
        }
    }

    /**
     * Load initial data
     */
    function loadInitialData() {
        loadManufacturers();
        loadCategories();
    }

    /**
     * Handle search type change
     */
    function handleSearchTypeChange() {
        const value = this.value;
        
        if (value === 'M') {
            showElement(featureModeFields);
            hideElement(categoryModeFields);
        } else {
            hideElement(featureModeFields);
            showElement(categoryModeFields);
        }
        
        updateSearchButtonState();
    }

    /**
     * Handle brand change
     */
    function handleBrandChange() {
        const value = this.value;
        
        if (value) {
            loadVehicleModels(value);
            enableElement(modelSelect);
        } else {
            clearSelect(modelSelect);
            disableElement(modelSelect);
            clearSelect(typeSelect);
            disableElement(typeSelect);
        }
        
        updateSearchButtonState();
    }

    /**
     * Handle model change
     */
    function handleModelChange() {
        const value = this.value;
        
        if (value) {
            loadVehicleTypes(value);
            enableElement(typeSelect);
        } else {
            clearSelect(typeSelect);
            disableElement(typeSelect);
        }
        
        updateSearchButtonState();
    }

    /**
     * Handle category change
     */
    function handleCategoryChange() {
        updateSearchButtonState();
    }

    /**
     * Handle form submission
     */
    function handleFormSubmit(e) {
        e.preventDefault();
        performSearch();
    }

    /**
     * Load manufacturers
     */
    function loadManufacturers() {
        makeAjaxRequest('getManufacturers', {}, function(response) {
            if (response.success && brandSelect) {
                clearSelect(brandSelect);
                addOption(brandSelect, '', 'Fahrzeug-Marke wählen...');
                
                response.manufacturers.forEach(function(manufacturer) {
                    addOption(brandSelect, manufacturer.value, manufacturer.text);
                });
            }
        });
    }

    /**
     * Load vehicle models
     */
    function loadVehicleModels(manufacturerId) {
        showLoading();
        
        makeAjaxRequest('getVehicleModels', {manufacturer_id: manufacturerId}, function(response) {
            hideLoading();
            
            if (response.success && modelSelect) {
                clearSelect(modelSelect);
                addOption(modelSelect, '', 'Fahrzeug-Modell wählen...');
                
                response.models.forEach(function(model) {
                    addOption(modelSelect, model.value, model.text);
                });
            }
        });
    }

    /**
     * Load vehicle types
     */
    function loadVehicleTypes(modelName) {
        showLoading();
        
        makeAjaxRequest('getVehicleTypes', {model_name: modelName}, function(response) {
            hideLoading();
            
            if (response.success && typeSelect) {
                clearSelect(typeSelect);
                addOption(typeSelect, '', 'Fahrzeug-Typ wählen...');
                
                response.types.forEach(function(type) {
                    addOption(typeSelect, type.value, type.text);
                });
            }
        });
    }

    /**
     * Load categories
     */
    function loadCategories() {
        makeAjaxRequest('getCategories', {}, function(response) {
            if (response.success && categorySelect) {
                clearSelect(categorySelect);
                addOption(categorySelect, '', 'Kategori seçin...');
                
                response.categories.forEach(function(category) {
                    addOption(categorySelect, category.value, category.text);
                });
            }
        });
    }

    /**
     * Update search button state
     */
    function updateSearchButtonState() {
        if (!searchButton) return;
        
        let canSearch = false;
        
        if (searchTypeSelect && searchTypeSelect.value === 'M') {
            canSearch = brandSelect && brandSelect.value && 
                       modelSelect && modelSelect.value && 
                       typeSelect && typeSelect.value;
        } else {
            canSearch = categorySelect && categorySelect.value;
        }
        
        if (canSearch) {
            enableElement(searchButton);
        } else {
            disableElement(searchButton);
        }
    }

    /**
     * Perform search
     */
    function performSearch() {
        if (!searchForm) return;
        
        showLoading();
        
        // Log search statistics
        const searchData = {
            search_type: searchTypeSelect ? searchTypeSelect.value : '',
            manufacturer: brandSelect ? brandSelect.value : '',
            model: modelSelect ? modelSelect.value : '',
            vehicle_type: typeSelect ? typeSelect.value : '',
            category: categorySelect ? categorySelect.value : '',
            results: 0
        };
        
        makeAjaxRequest('logSearch', searchData, function(response) {
            // Search logging completed
        });
        
        // Submit form normally for actual search
        searchForm.submit();
    }

    /**
     * Make AJAX request
     */
    function makeAjaxRequest(action, data, callback) {
        if (!config.pluginUrl) {
            console.error('Plugin URL not configured');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', action);
        formData.append('csrf_token', config.csrfToken || '');
        
        Object.keys(data).forEach(key => {
            formData.append(key, data[key]);
        });
        
        fetch(config.pluginUrl + 'frontend/ajax.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(callback)
        .catch(error => {
            console.error('AJAX Error:', error);
            hideLoading();
        });
    }

    /**
     * Utility functions
     */
    function showElement(element) {
        if (element) element.style.display = 'block';
    }

    function hideElement(element) {
        if (element) element.style.display = 'none';
    }

    function enableElement(element) {
        if (element) {
            element.disabled = false;
            element.classList.remove('disabled');
        }
    }

    function disableElement(element) {
        if (element) {
            element.disabled = true;
            element.classList.add('disabled');
        }
    }

    function clearSelect(select) {
        if (select) {
            select.innerHTML = '';
        }
    }

    function addOption(select, value, text) {
        if (select) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = text;
            select.appendChild(option);
        }
    }

    function showLoading() {
        if (loadingIndicator) {
            loadingIndicator.style.display = 'block';
        }
    }

    function hideLoading() {
        if (loadingIndicator) {
            loadingIndicator.style.display = 'none';
        }
    }

    // Expose public API
    window.VehicleSearchPluginAPI = {
        loadManufacturers: loadManufacturers,
        loadVehicleModels: loadVehicleModels,
        loadVehicleTypes: loadVehicleTypes,
        loadCategories: loadCategories,
        performSearch: performSearch,
        updateSearchButtonState: updateSearchButtonState
    };

})();
