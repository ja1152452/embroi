document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`search.php?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(product => {
                            const resultItem = document.createElement('a');
                            resultItem.href = `product.php?id=${product.id}`;
                            resultItem.className = 'search-result-item';
                            resultItem.innerHTML = `
                                <img src="${product.image}" alt="${product.name}">
                                <div class="search-result-info">
                                    <h6>${product.name}</h6>
                                    <p class="price">₱${parseFloat(product.price).toFixed(2)}</p>
                                </div>
                            `;
                            searchResults.appendChild(resultItem);
                        });
                        searchResults.style.display = 'block';
                    } else {
                        searchResults.innerHTML = '<div class="no-results">No products found</div>';
                        searchResults.style.display = 'block';
                    }
                });
        }, 300);
    });

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
}); 