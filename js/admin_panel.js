/**
 * Admin Panel - Character Management JavaScript
 * Handles search, filter, sort, and delete functionality
 */

// State management
let currentFilter = 'all';
let currentSort = { column: 'id', direction: 'desc' };
let deleteCharacterId = null;
let currentPage = 1;
let pageSize = 20;
let allRows = [];

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Store all rows for pagination
    allRows = Array.from(document.querySelectorAll('.character-row'));
    
    initializeFilters();
    initializeSearch();
    initializeSorting();
    initializeDeleteButtons();
    initializePagination();
});

// Filter functionality
function initializeFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Update active state
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Get filter type
            currentFilter = this.dataset.filter;
            
            // Apply filter
            applyFilters();
        });
    });
}

// Search functionality
function initializeSearch() {
    const searchInput = document.getElementById('characterSearch');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            applyFilters();
        });
    }
}

// Apply both filter and search
function applyFilters() {
    const searchTerm = document.getElementById('characterSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.character-row');
    
    let visibleRows = [];
    
    rows.forEach(row => {
        const type = row.dataset.type;
        const name = row.dataset.name.toLowerCase();
        
        // Check filter
        let showByFilter = true;
        if (currentFilter === 'pcs' && type !== 'pc') {
            showByFilter = false;
        } else if (currentFilter === 'npcs' && type !== 'npc') {
            showByFilter = false;
        }
        
        // Check search
        let showBySearch = true;
        if (searchTerm && !name.includes(searchTerm)) {
            showBySearch = false;
        }
        
        // Track visible rows
        if (showByFilter && showBySearch) {
            row.classList.remove('filtered-out');
            visibleRows.push(row);
        } else {
            row.classList.add('filtered-out');
        }
    });
    
    // Reset to page 1 when filters change
    currentPage = 1;
    updatePagination(visibleRows);
}

// Sorting functionality
function initializeSorting() {
    const headers = document.querySelectorAll('.character-table th[data-sort]');
    
    headers.forEach(header => {
        header.addEventListener('click', function() {
            const column = this.dataset.sort;
            
            // Toggle direction if same column, otherwise start with ascending
            if (currentSort.column === column) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.column = column;
                currentSort.direction = 'asc';
            }
            
            // Update header styling
            headers.forEach(h => {
                h.classList.remove('sorted-asc', 'sorted-desc');
            });
            this.classList.add('sorted-' + currentSort.direction);
            
            // Sort table
            sortTable(column, currentSort.direction);
        });
    });
}

function sortTable(column, direction) {
    const tbody = document.querySelector('.character-table tbody');
    const rows = Array.from(tbody.querySelectorAll('.character-row'));
    
    rows.sort((a, b) => {
        let aVal, bVal;
        
        // Get values based on column
        const aCells = a.querySelectorAll('td');
        const bCells = b.querySelectorAll('td');
        
        switch(column) {
            case 'id':
                aVal = parseInt(aCells[0].textContent);
                bVal = parseInt(bCells[0].textContent);
                break;
            case 'character_name':
                aVal = aCells[1].textContent.trim().toLowerCase();
                bVal = bCells[1].textContent.trim().toLowerCase();
                break;
            case 'player_name':
                aVal = aCells[2].textContent.trim().toLowerCase();
                bVal = bCells[2].textContent.trim().toLowerCase();
                break;
            case 'clan':
                aVal = aCells[3].textContent.trim().toLowerCase();
                bVal = bCells[3].textContent.trim().toLowerCase();
                break;
            case 'generation':
                aVal = parseInt(aCells[4].textContent);
                bVal = parseInt(bCells[4].textContent);
                break;
            case 'finalized':
                aVal = aCells[5].textContent.includes('Final') ? 1 : 0;
                bVal = bCells[5].textContent.includes('Final') ? 1 : 0;
                break;
            case 'created_at':
                aVal = new Date(aCells[6].textContent);
                bVal = new Date(bCells[6].textContent);
                break;
            default:
                return 0;
        }
        
        // Compare
        let comparison = 0;
        if (aVal > bVal) comparison = 1;
        if (aVal < bVal) comparison = -1;
        
        return direction === 'asc' ? comparison : -comparison;
    });
    
    // Re-append rows in sorted order
    rows.forEach(row => tbody.appendChild(row));
}

// Delete functionality
function initializeDeleteButtons() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            deleteCharacterId = this.dataset.id;
            const characterName = this.dataset.name;
            const isFinalized = this.dataset.finalized === '1';
            
            // Show modal
            document.getElementById('deleteCharacterName').textContent = characterName;
            
            if (isFinalized) {
                document.getElementById('deleteWarning').style.display = 'block';
            } else {
                document.getElementById('deleteWarning').style.display = 'none';
            }
            
            document.getElementById('deleteModal').classList.add('active');
        });
    });
    
    // Confirm delete button
    document.getElementById('confirmDeleteBtn').addEventListener('click', confirmDelete);
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    deleteCharacterId = null;
}

function confirmDelete() {
    if (!deleteCharacterId) return;
    
    fetch('delete_character_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            character_id: deleteCharacterId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const row = document.querySelector(`button[data-id="${deleteCharacterId}"]`).closest('tr');
            row.remove();
            closeDeleteModal();
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            alert('Error deleting character: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert('Error deleting character. Check console for details.');
    });
}

// Pagination functionality
function initializePagination() {
    updatePagination();
}

function changePageSize() {
    pageSize = parseInt(document.getElementById('pageSize').value);
    currentPage = 1;
    updatePagination();
}

function updatePagination(visibleRows = null) {
    // Get all visible rows (not filtered out)
    if (!visibleRows) {
        visibleRows = Array.from(document.querySelectorAll('.character-row:not(.filtered-out)'));
    }
    
    const totalVisible = visibleRows.length;
    const totalPages = Math.ceil(totalVisible / pageSize);
    
    // Hide all rows first
    document.querySelectorAll('.character-row').forEach(row => {
        row.classList.add('hidden');
    });
    
    // Show only rows for current page
    const startIndex = (currentPage - 1) * pageSize;
    const endIndex = Math.min(startIndex + pageSize, totalVisible);
    
    for (let i = startIndex; i < endIndex; i++) {
        if (visibleRows[i]) {
            visibleRows[i].classList.remove('hidden');
        }
    }
    
    // Update pagination info
    const showing = totalVisible === 0 ? 0 : startIndex + 1;
    document.getElementById('paginationInfo').textContent = 
        `Showing ${showing}-${endIndex} of ${totalVisible} characters`;
    
    // Generate pagination buttons
    const buttonsDiv = document.getElementById('paginationButtons');
    buttonsDiv.innerHTML = '';
    
    if (totalPages <= 1) return;
    
    // Previous button
    if (currentPage > 1) {
        const prevBtn = createPageButton('← Prev', currentPage - 1);
        buttonsDiv.appendChild(prevBtn);
    }
    
    // Page number buttons
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            const pageBtn = createPageButton(i, i);
            if (i === currentPage) pageBtn.classList.add('active');
            buttonsDiv.appendChild(pageBtn);
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.style.color = '#666';
            dots.style.padding = '0 5px';
            buttonsDiv.appendChild(dots);
        }
    }
    
    // Next button
    if (currentPage < totalPages) {
        const nextBtn = createPageButton('Next →', currentPage + 1);
        buttonsDiv.appendChild(nextBtn);
    }
}

function createPageButton(text, page) {
    const btn = document.createElement('button');
    btn.className = 'page-btn';
    btn.textContent = text;
    btn.onclick = () => goToPage(page);
    return btn;
}

function goToPage(page) {
    currentPage = page;
    updatePagination();
}

