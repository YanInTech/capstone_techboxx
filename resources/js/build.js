const customBuildBtn = document.getElementById('customBuildBtn');
const generateBuildBtn = document.getElementById('generateBuildBtn');
const amdBtn = document.getElementById('amdBtn');
const intelBtn = document.getElementById('intelBtn');
const budgetSection = document.querySelector('.budget-section');
const generateButton = document.querySelector('.generate-button');
const generalUseBtn = document.getElementById('generalUseBtn');
const gamingBtn = document.getElementById('gamingBtn');
const graphicsIntensiveBtn = document.getElementById('graphicsIntensiveBtn');
const budget = document.getElementById('budget');
const generateBtn = document.getElementById('generateBtn');
const loadingSpinner = document.getElementById('loadingSpinner');
const buildSectionButtons = document.querySelectorAll('#buildSection button');
const catalogList = document.querySelector('.catalog-list');
const catalogItem = document.querySelectorAll('.catalog-item');
const buildSection = document.getElementById('buildSection');
const summarySection = document.getElementById('summarySection');
const summaryTableBody = document.getElementById("summaryTableBody");


let currentBrandFilter = '';     // e.g. "amd" or "intel"
let currentCategoryFilter = '';  // e.g. "gaming"
let currentTypeFilter = '';      // e.g. "cpu"
let currentBudget = null;

function applyAllFilters() {
    catalogItem.forEach(item => {
        const itemType = item.getAttribute('data-type');
        const itemName = item.getAttribute('data-name').toLowerCase();
        const itemCategory = item.getAttribute('data-category').toLowerCase();
        const itemPrice = parseFloat(item.getAttribute('data-price'));

        let show = true;

        // Type filter (e.g. cpu, gpu, etc.)
        if (currentTypeFilter && itemType !== currentTypeFilter) {
            show = false;
        }

        // Brand filter (e.g. amd, intel, but only applies to CPU)
        if (currentBrandFilter) {
            const brand = currentBrandFilter.toLowerCase();
            if (itemType === 'cpu' && !itemName.includes(brand)) {
                show = false;
            }
        }

        // Category filter (e.g. gaming, general use, etc.)
        if (currentCategoryFilter) {
            const category = currentCategoryFilter.toLowerCase();
            if (itemCategory !== category) {
                show = false;
            }
        }

        // Budget filter
        if (currentBudget !== null && itemPrice > currentBudget) {
            show = false;
        }

        item.classList.toggle('hidden', !show);
    });
}


// FILTER CPU
function filterCPUByBrand(brand) {
    catalogItem.forEach(item => {
        const type = item.getAttribute('data-type');
        const name = item.getAttribute('data-name').toLowerCase();

        if (type === 'cpu') {
            if (name.includes(brand.toLowerCase())) {
                item.classList.remove('hidden');
            }
            else {
                item.classList.add('hidden');
            }
        }
        else {
            item.classList.remove('hidden');
        }
    })
}

// FILTER CATEGORY 
function filterByBuildCategory(category) {
    catalogItem.forEach(item => {
        const build = item.getAttribute('data-category').toLowerCase();

        if (build === category.toLowerCase()) {
            item.classList.remove('hidden');
        }
        else {
            item.classList.add('hidden');
        }
    })
}

customBuildBtn.addEventListener('click', function() {
    currentBudget = null;

    generateBuildBtn.classList.remove('active');
    buildSection.classList.remove('hidden');

    customBuildBtn.classList.add('active');
    budgetSection.classList.add('hidden');
    generateButton.classList.add('hidden');
});

generateBuildBtn.addEventListener('click', function() {
    generateBuildBtn.classList.add('active');
    buildSection.classList.add('hidden');

    customBuildBtn.classList.remove('active');
    budgetSection.classList.remove('hidden');
    generateButton.classList.remove('hidden');
});

amdBtn.addEventListener('click', function() {
    currentBrandFilter = 'amd';
    amdBtn.classList.add('active');

    intelBtn.classList.remove('active');

    applyAllFilters();
});

intelBtn.addEventListener('click', function() {
    currentBrandFilter = 'intel';

    intelBtn.classList.add('active');

    amdBtn.classList.remove('active');
    
    applyAllFilters();
});

generalUseBtn.addEventListener('click', function() {
    currentCategoryFilter = 'general use';

    generalUseBtn.classList.add('active');

    gamingBtn.classList.remove('active');
    graphicsIntensiveBtn.classList.remove('active');

    applyAllFilters();
});

gamingBtn.addEventListener('click', function() {
    currentCategoryFilter = 'gaming';

    gamingBtn.classList.add('active');

    generalUseBtn.classList.remove('active');
    graphicsIntensiveBtn.classList.remove('active');
    
    applyAllFilters();
});

graphicsIntensiveBtn.addEventListener('click', function() {
    currentCategoryFilter = 'graphics intensive';

    graphicsIntensiveBtn.classList.add('active');

    gamingBtn.classList.remove('active');
    generalUseBtn.classList.remove('active');

    applyAllFilters();
});

generateBtn.addEventListener('click', () => {
    const value = parseFloat(budget.value);

    if (!isNaN(value)) {
        currentBudget = value;
    } else {
        currentBudget = null;
    }

    applyAllFilters();
    
    budgetSection.classList.add('hidden');
    generateButton.classList.add('hidden');
    buildSection.classList.remove('hidden');
    loadingSpinner.classList.remove('hidden');

    const formattedBudget = currentBudget?.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' }) || "any";
    const category = currentCategoryFilter || "any category";
    const brand = currentBrandFilter || "any";

    loadingText.textContent = `Getting recommendations for ${category} with ${brand} CPU within ${formattedBudget} budget\nUser Budget: ${formattedBudget}`;

    // DATA ANALYTICS
    fetch("/techboxx/build/generate-build", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            category : currentCategoryFilter,
            cpuBrand: currentBrandFilter,
            useBudget: currentBudget
        })
    })
    .then(res => res.json())
    .then(data => { 
        loadingSpinner.classList.add('hidden');

        console.log(data);
        summaryTableBody.innerHTML = '';

        let totalPrice = 0;

        Object.values(data).forEach(item => {
            // Ensure price is a valid number (handle string with commas too)
            const price = parseFloat(item.price.toString().replace(/,/g, ''));
            if (isNaN(price)) {
                console.warn(`Invalid price for item:`, item);
                return;
            }

            totalPrice += price;

            let row = '';
            row += `<tr>`;
            row += `<td><p>${item.name}</p></td>`;
            row += `<td><p>1</p></td>`;
            row += `<td><p>₱${price.toFixed(2)}</p></td>`;
            row += `</tr>`;

            summaryTableBody.innerHTML += row;
        });

        // Add total price row
        let totalRow = '';
        totalRow += `<tr>`;
        totalRow += `<td colspan="2"><p><strong>Total</strong></p></td>`;
        totalRow += `<td><p><strong>₱${totalPrice.toFixed(2)}</strong></p></td>`;
        totalRow += `</tr>`;

        summaryTableBody.innerHTML += totalRow;

        // Show summary UI
        summarySection.classList.remove("hidden");
        document.getElementById('summaryTab').classList.add('active');
        componentsSection.classList.add("hidden");

        Object.entries(data).forEach(([key, item]) => {
            console.log([key, item]);
            let buttonSelector = null;

            if (key === 'pc_case') {
                key = 'case';
            }

            if (key === 'storage') {
                // Use item.type (either 'ssd' or 'hdd') to match the correct button
                buttonSelector = `button[data-type="${item.type}"]`;
            } else {
                // For other types of items, match by the key
                buttonSelector = `button[data-type="${key}"]`;
            }

            const button = document.querySelector(buttonSelector);
            if (button) {
                const selectedName = button.querySelector('.selected-name');
                if (selectedName) {
                    // Update the button text for storage based on its type
                    if (key === 'storage') {
                        if (item.type === 'ssd') {
                            selectedName.textContent = `${item.name}`;
                        } else if (item.type === 'hdd') {
                            selectedName.textContent = `${item.name}`;
                        }
                    } else {
                        // For non-storage items, just set the name
                        selectedName.textContent = item.name;
                    }
                }
            }
        })
    })
    .catch(err => {
        console.error("Error:", err);
        loadingSpinner.classList.add('hidden');
    });
});


buildSectionButtons.forEach(button => {
    button.addEventListener('click', () => {
        currentTypeFilter = button.getAttribute('data-type');

        // UPDATE CATALOG HEADER TITLE
        const catalogTitle = document.getElementById('catalogTitle');
        catalogTitle.textContent = currentTypeFilter.charAt(0).toUpperCase() + currentTypeFilter.slice(1);

        buildSectionButtons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        catalogList.classList.remove('hidden');

        summarySection.classList.add("hidden");
        document.getElementById('summaryTab').classList.remove('active');
        componentsSection.classList.remove("hidden");
        document.getElementById('componentsTab').classList.add('active');

        applyAllFilters();
    })
});

document.querySelectorAll('.catalog-item').forEach(item => {
    item.addEventListener('click', () => {
        const type = item.getAttribute('data-type');
        const name = item.getAttribute('data-name');
        const price = parseFloat(item.getAttribute('data-price'));
        const componentId = item.getAttribute('data-id');
        const imageUrl = item.getAttribute('data-image');

        // STORE SELECTED COMPONENT
        selectedComponents[type] = { name, price, imageUrl };
        sessionStorage.setItem(type, JSON.stringify(selectedComponents));

        // FIND THE MATCHING BUTTON
        const targetButton = document.querySelector(`#buildSection button[data-type="${type}"]`);
        if (targetButton) {
            const span = targetButton.querySelector('.selected-name');
            if (span) {
                span.textContent = name;
            }

            // STORE SELECTED ID ON BUTTON FOR VALIDATIONS
            targetButton.setAttribute('data-selected-id', componentId);
        }

        // UPDATE DRAGGABLE IMAGE
        const draggable = document.getElementById(type);
        if (draggable && imageUrl) {
            draggable.innerHTML = `
                <img src="${imageUrl}" alt="${name}" >
                <p>${type.toUpperCase()}</p>
                `;
        }

        updateSummaryTable();
    })
});

document.getElementById('componentsTab').addEventListener('click', () => {
    document.getElementById('componentsSection').classList.remove('hidden');
    document.getElementById('summarySection').classList.add('hidden');
    document.getElementById('componentsTab').classList.add('active');
    document.getElementById('summaryTab').classList.remove('active');

});

document.getElementById('summaryTab').addEventListener('click', () => {
    document.getElementById('componentsSection').classList.add('hidden');
    document.getElementById('summarySection').classList.remove('hidden');
    document.getElementById('componentsTab').classList.remove('active');
    document.getElementById('summaryTab').classList.add('active');
});

// SUMMARY DISPLAY
const selectedComponents = {};

function updateSummaryTable() {
    const tbody = document.querySelector('#summaryTableBody');
    tbody.innerHTML = ''; // CLEAR OLD ENTRIES

    let totalPrice = 0;

    for (const [type, component] of Object.entries(selectedComponents)) {
        const row = document.createElement('tr');

        const nameCell = document.createElement('td');
        nameCell.innerHTML = `<p>${component.name}</p>`;

        const qtyCell = document.createElement('td');
        qtyCell.innerHTML = `<p>1</p>`;

        const priceCell = document.createElement('td');
        priceCell.innerHTML = `<p>₱${component.price.toFixed(2)}</p>`;

        row.appendChild(nameCell);
        row.appendChild(qtyCell);
        row.appendChild(priceCell);

        tbody.appendChild(row);

        totalPrice += component.price;

    }

        // Step 3: Add total row at the end
    const totalRow = document.createElement('tr');

    const totalLabelCell = document.createElement('td');
    totalLabelCell.setAttribute('colspan', '2'); // Span across name and quantity columns
    totalLabelCell.innerHTML = `<p><strong>Total</strong></p>`;

    const totalPriceCell = document.createElement('td');
    totalPriceCell.innerHTML = `<p><strong>₱${totalPrice.toFixed(2)}</strong></p>`;

    totalRow.appendChild(totalLabelCell);
    totalRow.appendChild(totalPriceCell);

    tbody.appendChild(totalRow);
}

// ADD DATE TODAY ON THE SUMMARY TAB
window.addEventListener('DOMContentLoaded', () => {
    const dateElement = document.getElementById('buildDate');
    if (dateElement) {
        const today = new Date();
        const formatted = today.toLocaleDateString('en-PH', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
        }); 
        dateElement.textContent = formatted;
    }
});