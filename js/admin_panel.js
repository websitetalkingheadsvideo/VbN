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
    initializeViewButtons();
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
            case 'status':
                aVal = aCells[5].textContent.trim().toLowerCase();
                bVal = bCells[5].textContent.trim().toLowerCase();
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

// View functionality
function initializeViewButtons() {
    const viewButtons = document.querySelectorAll('.view-btn');
    
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            viewCharacter(this.dataset.id);
        });
    });
}

// Delete functionality
function initializeDeleteButtons() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            deleteCharacterId = this.dataset.id;
            const characterName = this.dataset.name;
            const isFinalized = this.dataset.status === 'finalized';
            
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
    // Page size change handler
    const pageSizeSelect = document.getElementById('pageSize');
    if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', function() {
            pageSize = parseInt(this.value);
            currentPage = 1;
            updatePagination();
        });
    }
    
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

// View character functionality
let currentViewMode = 'compact';
let currentViewData = null;

function viewCharacter(characterId) {
    document.getElementById('viewModal').classList.add('active');
    document.getElementById('viewCharacterContent').innerHTML = 'Loading...';
    
    fetch('view_character_api.php?id=' + characterId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentViewData = data;
                document.getElementById('viewCharacterName').textContent = data.character.character_name;
                renderCharacterView(currentViewMode);
            } else {
                document.getElementById('viewCharacterContent').innerHTML = '<p style="color: red;">Error: ' + data.message + '</p>';
            }
        })
        .catch(error => {
            document.getElementById('viewCharacterContent').innerHTML = '<p style="color: red;">Error loading character</p>';
            console.error(error);
        });
}

function setViewMode(mode, event) {
    currentViewMode = mode;
    document.querySelectorAll('.mode-btn').forEach(btn => btn.classList.remove('active'));
    if (event && event.target) {
        event.target.classList.add('active');
    }
    if (currentViewData) {
        renderCharacterView(mode);
    }
}

function renderCharacterView(mode) {
    const char = currentViewData.character;
    let html = '';
    
    if (mode === 'compact') {
        // Compact view - essential info only
        html = '<div class="character-details compact">';
        html += '<div class="info-grid">';
        html += '<p><strong>Player:</strong> ' + char.player_name + '</p>';
        html += '<p><strong>Clan:</strong> ' + char.clan + '</p>';
        html += '<p><strong>Generation:</strong> ' + char.generation + 'th</p>';
        html += '<p><strong>Concept:</strong> ' + char.concept + '</p>';
        html += '<p><strong>Nature:</strong> ' + char.nature + '</p>';
        html += '<p><strong>Demeanor:</strong> ' + char.demeanor + '</p>';
        html += '</div>';
        
        if (currentViewData.traits && currentViewData.traits.length > 0) {
            const physical = currentViewData.traits.filter(t => t.trait_category === 'Physical');
            const social = currentViewData.traits.filter(t => t.trait_category === 'Social');
            const mental = currentViewData.traits.filter(t => t.trait_category === 'Mental');
            
            html += '<h3>Traits</h3>';
            if (physical.length > 0) html += '<p><strong>Physical:</strong> ' + physical.map(t => t.trait_name).join(', ') + '</p>';
            if (social.length > 0) html += '<p><strong>Social:</strong> ' + social.map(t => t.trait_name).join(', ') + '</p>';
            if (mental.length > 0) html += '<p><strong>Mental:</strong> ' + mental.map(t => t.trait_name).join(', ') + '</p>';
        }
        
        if (currentViewData.disciplines && currentViewData.disciplines.length > 0) {
            html += '<h3>Disciplines</h3>';
            html += '<p>' + currentViewData.disciplines.map(d => d.discipline_name + ' ' + d.level).join(', ') + '</p>';
        }
        
        html += '</div>';
    } else {
        // Full view - all details
        html = '<div class="character-details full">';
        
        html += '<h3>Basic Information</h3>';
        html += '<div class="info-grid">';
        html += '<p><strong>Player:</strong> ' + char.player_name + '</p>';
        html += '<p><strong>Chronicle:</strong> ' + char.chronicle + '</p>';
        html += '<p><strong>Nature:</strong> ' + char.nature + '</p>';
        html += '<p><strong>Demeanor:</strong> ' + char.demeanor + '</p>';
        html += '<p><strong>Concept:</strong> ' + char.concept + '</p>';
        html += '<p><strong>Clan:</strong> ' + char.clan + '</p>';
        html += '<p><strong>Generation:</strong> ' + char.generation + 'th</p>';
        html += '<p><strong>Sire:</strong> ' + (char.sire || 'Unknown') + '</p>';
        html += '</div>';
        
        // Traits
        if (currentViewData.traits && currentViewData.traits.length > 0) {
            const physical = currentViewData.traits.filter(t => t.trait_category === 'Physical');
            const social = currentViewData.traits.filter(t => t.trait_category === 'Social');
            const mental = currentViewData.traits.filter(t => t.trait_category === 'Mental');
            
            html += '<h3>Physical Traits (' + physical.length + ')</h3>';
            html += '<div class="trait-list">';
            physical.forEach(t => html += '<span class="trait-badge">' + t.trait_name + '</span>');
            html += '</div>';
            
            html += '<h3>Social Traits (' + social.length + ')</h3>';
            html += '<div class="trait-list">';
            social.forEach(t => html += '<span class="trait-badge">' + t.trait_name + '</span>');
            html += '</div>';
            
            html += '<h3>Mental Traits (' + mental.length + ')</h3>';
            html += '<div class="trait-list">';
            mental.forEach(t => html += '<span class="trait-badge">' + t.trait_name + '</span>');
            html += '</div>';
        }
        
        // Abilities
        if (currentViewData.abilities && currentViewData.abilities.length > 0) {
            html += '<h3>Abilities</h3>';
            html += '<div class="trait-list">';
            currentViewData.abilities.forEach(a => {
                html += '<span class="trait-badge">' + a.ability_name + ' ' + a.level + '</span>';
            });
            html += '</div>';
        }
        
        // Disciplines
        if (currentViewData.disciplines && currentViewData.disciplines.length > 0) {
            html += '<h3>Disciplines</h3>';
            html += '<div class="trait-list">';
            currentViewData.disciplines.forEach(d => {
                html += '<span class="trait-badge">' + d.discipline_name + ' ' + d.level + '</span>';
            });
            html += '</div>';
        }
        
        // Backgrounds
        if (currentViewData.backgrounds && currentViewData.backgrounds.length > 0) {
            html += '<h3>Backgrounds</h3>';
            html += '<div class="trait-list">';
            currentViewData.backgrounds.forEach(b => {
                html += '<span class="trait-badge">' + b.background_name + ' ' + b.level + '</span>';
            });
            html += '</div>';
        }
        
        // Morality
        if (currentViewData.morality) {
            const m = currentViewData.morality;
            html += '<h3>Morality & Virtues</h3>';
            html += '<div class="info-grid">';
            html += '<p><strong>Humanity:</strong> ' + m.humanity + '/10</p>';
            html += '<p><strong>Willpower:</strong> ' + m.willpower_current + '/' + m.willpower_permanent + '</p>';
            html += '<p><strong>Conscience:</strong> ' + m.conscience + '</p>';
            html += '<p><strong>Self-Control:</strong> ' + m.self_control + '</p>';
            html += '<p><strong>Courage:</strong> ' + m.courage + '</p>';
            html += '</div>';
        }
        
        // Merits & Flaws
        if (currentViewData.merits_flaws && currentViewData.merits_flaws.length > 0) {
            const merits = currentViewData.merits_flaws.filter(m => m.type === 'merit');
            const flaws = currentViewData.merits_flaws.filter(m => m.type === 'flaw');
            
            if (merits.length > 0) {
                html += '<h3>Merits</h3>';
                html += '<div class="trait-list">';
                merits.forEach(m => html += '<span class="trait-badge">' + m.name + ' (' + m.point_value + ')</span>');
                html += '</div>';
            }
            
            if (flaws.length > 0) {
                html += '<h3>Flaws</h3>';
                html += '<div class="trait-list">';
                flaws.forEach(f => html += '<span class="trait-badge">' + f.name + ' (' + f.point_value + ')</span>');
                html += '</div>';
            }
        }
        
        // Status
        if (currentViewData.status) {
            const s = currentViewData.status;
            html += '<h3>Status & Resources</h3>';
            html += '<div class="info-grid">';
            html += '<p><strong>Health:</strong> ' + s.health_levels + '</p>';
            html += '<p><strong>Blood Pool:</strong> ' + s.blood_pool_current + '/' + s.blood_pool_maximum + '</p>';
            if (s.sect_status) html += '<p><strong>Sect Status:</strong> ' + s.sect_status + '</p>';
            if (s.clan_status) html += '<p><strong>Clan Status:</strong> ' + s.clan_status + '</p>';
            html += '</div>';
        }
        
        html += '</div>';
    }
    
    document.getElementById('viewCharacterContent').innerHTML = html;
}

function closeViewModal() {
    document.getElementById('viewModal').classList.remove('active');
}

