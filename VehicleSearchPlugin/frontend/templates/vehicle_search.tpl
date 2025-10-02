{* Vehicle Search Plugin Template for JTL Shop 5.x *}
{* This template provides the vehicle search form functionality *}

{block name='vehicle-search-form'}
<div class="vehicle-search-wrapper" id="vehicleSearchPlugin">
    <form class="search-form" method="POST" action="{$ShopURL}/index.php" id="vehicleSearchForm">
        <!-- JTL Shop CSRF Token -->
        <input type="hidden" name="csrf_token" value="{$smarty.session.csrf_token}" />
        <input type="hidden" name="a" value="{$smarty.const.LINKTYP_ARTIKELSUCHE}" />
        <input type="hidden" name="s" value="{$smarty.session.sessionID}" />
        
        <!-- Search Type Selection -->
        <div class="form-group">
            <label class="form-label">Seçim Türü:</label>
            <select class="form-select" id="cAuswahltyp" name="cAuswahltyp">
                <option value="M" selected>Özellikler hakkında</option>
                <option value="K">Kategori ağacı aracılığıyla</option>
            </select>
        </div>

        <!-- Feature Mode Fields -->
        <div id="featureModeFields">
            <div class="form-group">
                <select class="form-select" id="bannerVehicleBrand" name="cHersteller" required>
                    <option value="">Fahrzeug-Marke wählen...</option>
                </select>
                <label class="form-label">Fahrzeug-Marke</label>
            </div>

            <div class="form-group">
                <select class="form-select" id="bannerVehicleModel" name="cModell" disabled>
                    <option value="">Fahrzeug-Modell wählen...</option>
                </select>
                <label class="form-label">Fahrzeug-Modell</label>
            </div>

            <div class="form-group">
                <select class="form-select" id="bannerVehicleType" name="cFahrzeugtyp" disabled>
                    <option value="">Fahrzeug-Typ wählen...</option>
                </select>
                <label class="form-label">Fahrzeug-Typ</label>
            </div>
        </div>

        <!-- Category Mode Fields -->
        <div id="categoryModeFields" style="display: none;">
            <div class="form-group">
                <select class="form-select" id="categorySelect" name="kKategorie">
                    <option value="">Kategori seçin...</option>
                </select>
                <label class="form-label">Kategori</label>
            </div>
        </div>

        <!-- Search Button -->
        <div class="form-group">
            <button type="submit" class="btn btn-primary" id="searchButton" disabled>
                <i class="fa fa-search"></i> Ara
            </button>
        </div>
    </form>

    <!-- Loading Indicator -->
    <div class="loading-indicator" id="loadingIndicator" style="display: none;">
        <div class="spinner"></div>
        <span>Yükleniyor...</span>
    </div>

    <!-- Search Results -->
    <div class="search-results" id="searchResults" style="display: none;">
        <h3>Arama Sonuçları</h3>
        <div class="results-container" id="resultsContainer">
            <!-- Results will be populated here -->
        </div>
    </div>
</div>

<style>
.vehicle-search-wrapper {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.search-form {
    display: grid;
    gap: 15px;
}

.form-group {
    position: relative;
}

.form-label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.form-select {
    width: 100%;
    padding: 10px 15px;
    border: 2px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    background: #fff;
    transition: border-color 0.3s ease;
}

.form-select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
}

.form-select:disabled {
    background: #f8f9fa;
    color: #6c757d;
    cursor: not-allowed;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #007bff;
    color: #fff;
}

.btn-primary:hover:not(:disabled) {
    background: #0056b3;
    transform: translateY(-1px);
}

.btn:disabled {
    background: #6c757d;
    cursor: not-allowed;
    transform: none;
}

.loading-indicator {
    text-align: center;
    padding: 20px;
    color: #666;
}

.spinner {
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    display: inline-block;
    margin-right: 10px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.search-results {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.search-results h3 {
    margin: 0 0 20px 0;
    color: #333;
}

.results-container {
    display: grid;
    gap: 15px;
}

.result-item {
    background: #fff;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #ddd;
    transition: box-shadow 0.3s ease;
}

.result-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .vehicle-search-wrapper {
        margin: 10px;
        padding: 15px;
    }
    
    .search-form {
        gap: 10px;
    }
    
    .form-select {
        font-size: 14px;
        padding: 8px 12px;
    }
    
    .btn {
        padding: 10px 20px;
        font-size: 14px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('vehicleSearchForm');
    const searchTypeSelect = document.getElementById('cAuswahltyp');
    const featureModeFields = document.getElementById('featureModeFields');
    const categoryModeFields = document.getElementById('categoryModeFields');
    const brandSelect = document.getElementById('bannerVehicleBrand');
    const modelSelect = document.getElementById('bannerVehicleModel');
    const typeSelect = document.getElementById('bannerVehicleType');
    const categorySelect = document.getElementById('categorySelect');
    const searchButton = document.getElementById('searchButton');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const searchResults = document.getElementById('searchResults');
    const resultsContainer = document.getElementById('resultsContainer');

    // CSRF Token
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    // Initialize
    loadManufacturers();
    loadCategories();

    // Search type change handler
    searchTypeSelect.addEventListener('change', function() {
        if (this.value === 'M') {
            featureModeFields.style.display = 'block';
            categoryModeFields.style.display = 'none';
            updateSearchButtonState();
        } else {
            featureModeFields.style.display = 'none';
            categoryModeFields.style.display = 'block';
            updateSearchButtonState();
        }
    });

    // Brand change handler
    brandSelect.addEventListener('change', function() {
        if (this.value) {
            loadVehicleModels(this.value);
            modelSelect.disabled = false;
        } else {
            modelSelect.innerHTML = '<option value="">Fahrzeug-Modell wählen...</option>';
            modelSelect.disabled = true;
            typeSelect.innerHTML = '<option value="">Fahrzeug-Typ wählen...</option>';
            typeSelect.disabled = true;
        }
        updateSearchButtonState();
    });

    // Model change handler
    modelSelect.addEventListener('change', function() {
        if (this.value) {
            loadVehicleTypes(this.value);
            typeSelect.disabled = false;
        } else {
            typeSelect.innerHTML = '<option value="">Fahrzeug-Typ wählen...</option>';
            typeSelect.disabled = true;
        }
        updateSearchButtonState();
    });

    // Category change handler
    categorySelect.addEventListener('change', function() {
        updateSearchButtonState();
    });

    // Form submission
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        performSearch();
    });

    // Load manufacturers
    function loadManufacturers() {
        makeAjaxRequest('getManufacturers', {}, function(response) {
            if (response.success) {
                brandSelect.innerHTML = '<option value="">Fahrzeug-Marke wählen...</option>';
                response.manufacturers.forEach(function(manufacturer) {
                    const option = document.createElement('option');
                    option.value = manufacturer.value;
                    option.textContent = manufacturer.text;
                    brandSelect.appendChild(option);
                });
            }
        });
    }

    // Load vehicle models
    function loadVehicleModels(manufacturerId) {
        makeAjaxRequest('getVehicleModels', {manufacturer_id: manufacturerId}, function(response) {
            if (response.success) {
                modelSelect.innerHTML = '<option value="">Fahrzeug-Modell wählen...</option>';
                response.models.forEach(function(model) {
                    const option = document.createElement('option');
                    option.value = model.value;
                    option.textContent = model.text;
                    modelSelect.appendChild(option);
                });
            }
        });
    }

    // Load vehicle types
    function loadVehicleTypes(modelName) {
        makeAjaxRequest('getVehicleTypes', {model_name: modelName}, function(response) {
            if (response.success) {
                typeSelect.innerHTML = '<option value="">Fahrzeug-Typ wählen...</option>';
                response.types.forEach(function(type) {
                    const option = document.createElement('option');
                    option.value = type.value;
                    option.textContent = type.text;
                    typeSelect.appendChild(option);
                });
            }
        });
    }

    // Load categories
    function loadCategories() {
        makeAjaxRequest('getCategories', {}, function(response) {
            if (response.success) {
                categorySelect.innerHTML = '<option value="">Kategori seçin...</option>';
                response.categories.forEach(function(category) {
                    const option = document.createElement('option');
                    option.value = category.value;
                    option.textContent = category.text;
                    categorySelect.appendChild(option);
                });
            }
        });
    }

    // Update search button state
    function updateSearchButtonState() {
        let canSearch = false;
        
        if (searchTypeSelect.value === 'M') {
            canSearch = brandSelect.value && modelSelect.value && typeSelect.value;
        } else {
            canSearch = categorySelect.value;
        }
        
        searchButton.disabled = !canSearch;
    }

    // Perform search
    function performSearch() {
        loadingIndicator.style.display = 'block';
        searchResults.style.display = 'none';
        
        // Log search statistics
        const searchData = {
            search_type: searchTypeSelect.value,
            manufacturer: brandSelect.value,
            model: modelSelect.value,
            vehicle_type: typeSelect.value,
            category: categorySelect.value,
            results: 0 // Will be updated after search
        };
        
        makeAjaxRequest('logSearch', searchData, function(response) {
            // Search logging completed
        });
        
        // Submit form normally for actual search
        searchForm.submit();
    }

    // Make AJAX request
    function makeAjaxRequest(action, data, callback) {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('csrf_token', csrfToken);
        
        Object.keys(data).forEach(key => {
            formData.append(key, data[key]);
        });
        
        fetch('{$PluginUrl}frontend/ajax.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(callback)
        .catch(error => {
            console.error('AJAX Error:', error);
        });
    }
});
</script>
{/block}