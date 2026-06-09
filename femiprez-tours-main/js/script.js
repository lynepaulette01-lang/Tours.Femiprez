const hamburger = document.querySelector('.hamburger');
const navLinks = document.querySelector('.nav-links');
const filterButtons = document.querySelectorAll('.filter-btn');
const destinationCards = document.querySelectorAll('.destination-card');

if (hamburger && navLinks) {
  hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('open');
    hamburger.classList.toggle('open');
  });
}

const applyDestinationFilter = (filter) => {
  const categories = document.querySelectorAll('.destinations-category');
  
  filterButtons.forEach((btn) => {
    btn.classList.toggle('active', btn.dataset.filter === filter);
  });

  categories.forEach((category) => {
    if (filter === 'all' || category.dataset.category === filter) {
      category.style.display = '';
    } else {
      category.style.display = 'none';
    }
  });

  // Scroll to section if specific filter is selected
  if (filter !== 'all') {
    const sectionId = `${filter}-section`;
    const section = document.getElementById(sectionId);
    if (section) {
      setTimeout(() => {
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 100);
    }
  }
};

const getFilterFromHash = () => {
  const hash = window.location.hash.replace('#', '');
  const validFilters = Array.from(filterButtons).map((btn) => btn.dataset.filter);
  return validFilters.includes(hash) ? hash : 'all';
};

if (filterButtons.length) {
  filterButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const filter = button.dataset.filter;
      applyDestinationFilter(filter);
      window.history.replaceState(null, '', `#${filter}`);
    });
  });

  applyDestinationFilter(getFilterFromHash());
}

// Back to Top Button
const createBackToTopButton = () => {
  const button = document.createElement('button');
  button.id = 'back-to-top';
  button.innerHTML = '<i class="fas fa-arrow-up"></i>';
  button.setAttribute('aria-label', 'Back to top');
  document.body.appendChild(button);

  window.addEventListener('scroll', () => {
    if (window.scrollY > 300) {
      button.classList.add('visible');
    } else {
      button.classList.remove('visible');
    }
  });

  button.addEventListener('click', () => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth',
    });
  });
};

createBackToTopButton();
