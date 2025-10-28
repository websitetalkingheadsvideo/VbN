<?php
/**
 * Rulebooks Search Interface
 * Web interface for searching through VTM/MET rulebooks
 */

session_start();
require_once __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rulebook Search - VbN</title>
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/header.css">
    <style>
        .search-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .search-box {
            background: rgba(26, 15, 15, 0.8);
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .search-input {
            width: 100%;
            padding: 15px;
            font-size: 18px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #8b0000;
            color: #fff;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .filters {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            color: #ccc;
            font-size: 14px;
        }

        .filter-group select {
            padding: 8px 12px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #8b0000;
            color: #fff;
            border-radius: 4px;
        }

        .search-button {
            padding: 12px 30px;
            background: #8b0000;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }

        .search-button:hover {
            background: #a00000;
        }

        .results-container {
            background: rgba(26, 15, 15, 0.8);
            padding: 20px;
            border-radius: 8px;
            min-height: 300px;
        }

        .result-item {
            background: rgba(0, 0, 0, 0.3);
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 4px;
            border-left: 3px solid #8b0000;
        }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 10px;
        }

        .result-title {
            color: #8b0000;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .result-meta {
            color: #999;
            font-size: 14px;
        }

        .result-snippet {
            color: #ccc;
            line-height: 1.6;
            margin-top: 10px;
        }

        .result-snippet mark {
            background: #8b0000;
            color: #fff;
            padding: 2px 4px;
            border-radius: 2px;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .no-results {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 18px;
        }

        .books-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .book-card {
            background: rgba(0, 0, 0, 0.3);
            padding: 15px;
            border-radius: 4px;
            border-left: 3px solid #8b0000;
        }

        .book-title {
            color: #8b0000;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .book-info {
            color: #999;
            font-size: 14px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .tab {
            padding: 10px 20px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid #8b0000;
            color: #fff;
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .tab.active {
            background: #8b0000;
        }

        .tab:hover {
            background: rgba(139, 0, 0, 0.5);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>

<div class="search-container">
    <h1 style="color: #8b0000; text-align: center; margin-bottom: 30px;">Rulebook Search</h1>

    <div class="tabs">
        <div class="tab active" onclick="switchTab('search')">Search Content</div>
        <div class="tab" onclick="switchTab('books')">Browse Books</div>
    </div>

    <!-- Search Tab -->
    <div id="search-tab" class="tab-content active">
        <div class="search-box">
            <input type="text" 
                   id="searchInput" 
                   class="search-input" 
                   placeholder="Search for disciplines, clans, rules, lore..." 
                   onkeypress="if(event.key === 'Enter') performSearch()">
            
            <div class="filters">
                <div class="filter-group">
                    <label>Category</label>
                    <select id="categoryFilter">
                        <option value="">All Categories</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>System</label>
                    <select id="systemFilter">
                        <option value="">All Systems</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Results</label>
                    <select id="limitFilter">
                        <option value="25">25</option>
                        <option value="50" selected>50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                
                <button class="search-button" onclick="performSearch()">Search</button>
            </div>
        </div>

        <div id="resultsContainer" class="results-container">
            <div class="no-results">Enter a search term to begin</div>
        </div>
    </div>

    <!-- Books Tab -->
    <div id="books-tab" class="tab-content">
        <div class="search-box">
            <div class="filters">
                <div class="filter-group">
                    <label>Category</label>
                    <select id="booksCategory" onchange="loadBooks()">
                        <option value="">All Categories</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>System</label>
                    <select id="booksSystem" onchange="loadBooks()">
                        <option value="">All Systems</option>
                    </select>
                </div>
            </div>
        </div>

        <div id="booksContainer" class="results-container">
            <div class="loading">Loading books...</div>
        </div>
    </div>
</div>

<script>
// Load filters on page load
window.addEventListener('DOMContentLoaded', function() {
    loadFilters();
});

function switchTab(tab) {
    // Update tab buttons
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');
    
    // Update tab content
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.getElementById(tab + '-tab').classList.add('active');
    
    // Load books if switching to books tab
    if (tab === 'books') {
        loadBooks();
    }
}

async function loadFilters() {
    try {
        const response = await fetch('api_rulebooks_search.php?action=filters');
        const data = await response.json();
        
        if (data.success) {
            // Populate category filters
            const categorySelects = [document.getElementById('categoryFilter'), document.getElementById('booksCategory')];
            categorySelects.forEach(select => {
                data.filters.categories.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat;
                    option.textContent = cat;
                    select.appendChild(option);
                });
            });
            
            // Populate system filters
            const systemSelects = [document.getElementById('systemFilter'), document.getElementById('booksSystem')];
            systemSelects.forEach(select => {
                data.filters.systems.forEach(sys => {
                    const option = document.createElement('option');
                    option.value = sys;
                    option.textContent = sys;
                    select.appendChild(option);
                });
            });
        }
    } catch (error) {
        console.error('Failed to load filters:', error);
    }
}

async function performSearch() {
    const query = document.getElementById('searchInput').value.trim();
    const category = document.getElementById('categoryFilter').value;
    const system = document.getElementById('systemFilter').value;
    const limit = document.getElementById('limitFilter').value;
    
    if (!query) {
        return;
    }
    
    const container = document.getElementById('resultsContainer');
    container.innerHTML = '<div class="loading">Searching...</div>';
    
    try {
        const params = new URLSearchParams({
            action: 'search',
            q: query,
            limit: limit
        });
        
        if (category) params.append('category', category);
        if (system) params.append('system', system);
        
        const response = await fetch('api_rulebooks_search.php?' + params);
        const data = await response.json();
        
        if (data.success) {
            displayResults(data.results);
        } else {
            container.innerHTML = `<div class="no-results">Error: ${data.error}</div>`;
        }
    } catch (error) {
        container.innerHTML = `<div class="no-results">Search failed: ${error.message}</div>`;
    }
}

function displayResults(results) {
    const container = document.getElementById('resultsContainer');
    
    if (results.length === 0) {
        container.innerHTML = '<div class="no-results">No results found</div>';
        return;
    }
    
    let html = '';
    results.forEach(result => {
        html += `
            <div class="result-item">
                <div class="result-header">
                    <div>
                        <div class="result-title">${result.book_title}</div>
                        <div class="result-meta">
                            ${result.category} • ${result.system_type} • Page ${result.page_number}
                        </div>
                    </div>
                    <div class="result-meta">
                        Relevance: ${result.relevance.toFixed(2)}
                    </div>
                </div>
                <div class="result-snippet">${result.snippet}</div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

async function loadBooks() {
    const category = document.getElementById('booksCategory').value;
    const system = document.getElementById('booksSystem').value;
    const container = document.getElementById('booksContainer');
    
    container.innerHTML = '<div class="loading">Loading books...</div>';
    
    try {
        const params = new URLSearchParams({ action: 'books' });
        if (category) params.append('category', category);
        if (system) params.append('system', system);
        
        const response = await fetch('api_rulebooks_search.php?' + params);
        const data = await response.json();
        
        if (data.success) {
            displayBooks(data.books);
        } else {
            container.innerHTML = `<div class="no-results">Error: ${data.error}</div>`;
        }
    } catch (error) {
        container.innerHTML = `<div class="no-results">Failed to load books: ${error.message}</div>`;
    }
}

function displayBooks(books) {
    const container = document.getElementById('booksContainer');
    
    if (books.length === 0) {
        container.innerHTML = '<div class="no-results">No books found</div>';
        return;
    }
    
    let html = '<div class="books-list">';
    books.forEach(book => {
        html += `
            <div class="book-card">
                <div class="book-title">${book.title}</div>
                <div class="book-info">
                    Category: ${book.category}<br>
                    System: ${book.system_type}<br>
                    Pages: ${book.page_count}<br>
                    ${book.book_code ? `Code: ${book.book_code}<br>` : ''}
                    Status: ${book.status}
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    container.innerHTML = html;
}
</script>

</body>
</html>

