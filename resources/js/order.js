const orderBuilds = document.getElementById('orderBuilds');
const checkOutComponents = document.getElementById('checkOutComponents');
const orderBuildsSection = document.getElementById('orderBuildsSection');
const checkOutComponentsSection = document.getElementById('checkOutComponentsSection');

function setActiveTab(tab) {
    if (tab === 'order') {
        orderBuilds.classList.add('active');
        checkOutComponents.classList.remove('active');
        orderBuildsSection.classList.remove('hide');
        checkOutComponentsSection.classList.add('hide');
    } else {
        orderBuilds.classList.remove('active');
        checkOutComponents.classList.add('active');
        orderBuildsSection.classList.add('hide');
        checkOutComponentsSection.classList.remove('hide');
    }

    // Update the URL query string without reloading the page
    const url = new URL(window.location);
    url.searchParams.set('tab', tab);
    window.history.replaceState(null, '', url);
}

orderBuilds.addEventListener('click', () => setActiveTab('order'));
checkOutComponents.addEventListener('click', () => setActiveTab('checkout'));
