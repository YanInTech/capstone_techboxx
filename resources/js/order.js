const orderBuilds = document.getElementById('orderBuilds');
const checkOutComponents = document.getElementById('checkOutComponents');
const orderBuildsSection = document.getElementById('orderBuildsSection');
const checkOutComponentsSection = document.getElementById('checkOutComponentsSection');

orderBuilds.addEventListener('click', function() {
    orderBuilds.classList.add('active');
    checkOutComponents.classList.remove('active');
    orderBuildsSection.classList.remove('hide');
    checkOutComponentsSection.classList.add('hide');
})

checkOutComponents.addEventListener('click', function() {
    orderBuilds.classList.remove('active');
    checkOutComponents.classList.add('active');
    orderBuildsSection.classList.add('hide');
    checkOutComponentsSection.classList.remove('hide');
})

