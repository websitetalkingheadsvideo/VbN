/**
 * Admin Panel - Character Management JavaScript
 * Handles search, filter, sort, and delete functionality
 */

// State management
let currentFilter = 'all';
let currentClanFilter = 'all';
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
    initializeClanFilter();
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

// Clan filter functionality
function initializeClanFilter() {
    const clanFilter = document.getElementById('clanFilter');
    
    if (clanFilter) {
        clanFilter.addEventListener('change', function() {
            currentClanFilter = this.value;
            console.log('Clan filter changed to:', currentClanFilter);
            applyFilters();
        });
    }
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

// Apply filter, clan filter, and search
function applyFilters() {
    const searchTerm = document.getElementById('characterSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.character-row');
    
    let visibleRows = [];
    
    rows.forEach(row => {
        const type = row.dataset.type;
        const name = row.dataset.name.toLowerCase();
        const clan = row.dataset.clan || '';
        
        // Check filter (PC/NPC)
        let showByFilter = true;
        if (currentFilter === 'pcs' && type !== 'pc') {
            showByFilter = false;
        } else if (currentFilter === 'npcs' && type !== 'npc') {
            showByFilter = false;
        }
        
        // Check clan filter
        let showByClan = true;
        if (currentClanFilter !== 'all' && clan !== currentClanFilter) {
            showByClan = false;
        }
        
        // Debug logging
        if (currentClanFilter !== 'all') {
            console.log(`Character: ${name}, Clan: "${clan}", Filter: "${currentClanFilter}", Show: ${showByClan}`);
        }
        
        // Check search
        let showBySearch = true;
        if (searchTerm && !name.includes(searchTerm)) {
            showBySearch = false;
        }
        
        // Track visible rows
        if (showByFilter && showByClan && showBySearch) {
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
let modalClickHandler = null;

function viewCharacter(characterId) {
    const modal = document.getElementById('viewModal');
    modal.classList.add('active');
    document.getElementById('characterHeader').innerHTML = '';
    document.getElementById('viewCharacterContent').innerHTML = 'Loading...';
    
    // Remove any existing click handler to prevent duplicates
    if (modalClickHandler) {
        modal.removeEventListener('click', modalClickHandler);
    }
    
    // Add click-outside-to-close handler
    modalClickHandler = (e) => {
        // Only close if clicking the backdrop itself (not the modal content)
        if (e.target === modal) {
            closeViewModal();
        }
    };
    modal.addEventListener('click', modalClickHandler);
    
    fetch('view_character_api.php?id=' + characterId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentViewData = data;
                document.getElementById('viewCharacterName').textContent = data.character.character_name;
                // Debug: log abilities and disciplines
                console.log('Abilities:', data.abilities);
                console.log('Disciplines:', data.disciplines);
                renderCharacterView(currentViewMode);
            } else {
                document.getElementById('characterHeader').innerHTML = '';
                document.getElementById('viewCharacterContent').innerHTML = '<p style="color: red;">Error: ' + data.message + '</p>';
            }
        })
        .catch(error => {
            document.getElementById('characterHeader').innerHTML = '';
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
    
    // Toggle compact mode class on modal
    const modalContent = document.querySelector('.modal-content.large-modal');
    if (modalContent) {
        if (mode === 'compact') {
            modalContent.classList.add('compact-mode');
        } else {
            modalContent.classList.remove('compact-mode');
        }
    }
    
    if (currentViewData) {
        renderCharacterView(mode);
    }
}

function renderCharacterView(mode) {
    const char = currentViewData.character;
    let headerHtml = '';
    let contentHtml = '';
    
    function clanLogoUrl(clan) {
        if (!clan) return null;
        const basePath = '../images/Clan%20Logos/';
        const clean = String(clan)
            .replace(/[\u{1F300}-\u{1FAFF}]/gu, '') // strip emoji
            .trim()
            .toLowerCase();
        const map = {
            'assamite': 'LogoClanAssamite.webp',
            'brujah': 'LogoClanBrujah.webp',
            'followers of set': 'LogoClanFollowersofSet.webp', // note lowercase 'of' in filename
            'gangrel': 'LogoClanGangrel.webp',
            'giovanni': 'LogoClanGiovanni.webp',
            'lasombra': 'LogoClanLasombra.webp',
            'malkavian': 'LogoClanMalkavian.webp',
            'nosferatu': 'LogoClanNosferatu.webp',
            'ravnos': 'LogoClanRavnos.webp',
            'toreador': 'LogoClanToreador.webp',
            'tremere': 'LogoClanTremere.webp',
            'tzimisce': 'LogoClanTzimisce.webp',
            'ventrue': 'LogoClanVentrue.webp',
            'caitiff': 'LogoBloodlineCaitiff.webp'
        };
        const file = map[clean];
        if (!file) return null;
        const url = basePath + file;
        return url;
    }
    
    // Build header section (two-column layout)
    const hasPortrait = !!char.character_image;
    const fallbackUrl = char.clan_logo_url || clanLogoUrl(char.clan);
    const imageUrl = hasPortrait ? ('../uploads/characters/' + char.character_image) : fallbackUrl;
    
    headerHtml += '<div class="character-info-column">';
    headerHtml += '<div class="character-info-row"><span class="character-info-label">Player</span><span class="character-info-value">' + (char.player_name || 'NPC') + '</span></div>';
    headerHtml += '<div class="character-info-row"><span class="character-info-label">Chronicle</span><span class="character-info-value">' + (char.chronicle || 'N/A') + '</span></div>';
    headerHtml += '<div class="character-info-row"><span class="character-info-label">Clan</span><span class="character-info-value">' + (char.clan || 'Unknown') + '</span></div>';
    headerHtml += '<div class="character-info-row"><span class="character-info-label">Generation</span><span class="character-info-value">' + (char.generation || 'N/A') + 'th</span></div>';
    headerHtml += '<div class="character-info-row"><span class="character-info-label">Nature</span><span class="character-info-value">' + (char.nature || 'N/A') + '</span></div>';
    headerHtml += '<div class="character-info-row"><span class="character-info-label">Demeanor</span><span class="character-info-value">' + (char.demeanor || 'N/A') + '</span></div>';
    headerHtml += '<div class="character-info-row"><span class="character-info-label">Sire</span><span class="character-info-value">' + (char.sire || 'Unknown') + '</span></div>';
    headerHtml += '<div class="character-info-row"><span class="character-info-label">Concept</span><span class="character-info-value">' + (char.concept || 'N/A') + '</span></div>';
    headerHtml += '</div>';
    
    headerHtml += '<div class="character-image-column">';
    headerHtml += '<div class="character-image-wrapper">';
    if (imageUrl) {
        headerHtml += '<img src="' + imageUrl + '" alt="Character portrait" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'block\';" />';
    }
    headerHtml += '<span class="character-image-placeholder" style="display: ' + (imageUrl ? 'none' : 'block') + ';">No Image</span>';
    headerHtml += '</div>';
    headerHtml += '</div>';
    
    document.getElementById('characterHeader').innerHTML = headerHtml;
    
    if (mode === 'compact') {
        // Compact view - essential info only (header already rendered above)
        contentHtml = '<div class="character-details compact">';
        
        if (currentViewData.traits && currentViewData.traits.length > 0) {
            const physical = currentViewData.traits.filter(t => t.trait_category === 'Physical');
            const social = currentViewData.traits.filter(t => t.trait_category === 'Social');
            const mental = currentViewData.traits.filter(t => t.trait_category === 'Mental');
            
            contentHtml += '<h3>Traits</h3>';
            if (physical.length > 0) contentHtml += '<p><strong>Physical:</strong> ' + physical.map(t => t.trait_name).join(', ') + '</p>';
            if (social.length > 0) contentHtml += '<p><strong>Social:</strong> ' + social.map(t => t.trait_name).join(', ') + '</p>';
            if (mental.length > 0) contentHtml += '<p><strong>Mental:</strong> ' + mental.map(t => t.trait_name).join(', ') + '</p>';
        }
        
        if (currentViewData.disciplines && currentViewData.disciplines.length > 0) {
            contentHtml += '<h3>Disciplines</h3>';
            contentHtml += '<p>' + currentViewData.disciplines.map(d => d.discipline_name + ' ' + d.level).join(', ') + '</p>';
        }
        
        contentHtml += '</div>';
    } else {
        // Full view - all details (header already rendered above)
        contentHtml = '<div class="character-details full">';
        
        // XP Information
        contentHtml += '<h3>Experience Points</h3>';
        contentHtml += '<div class="row g-3 mt-2">';
        contentHtml += '<div class="col-lg-4 col-md-4 col-sm-6"><p><strong>Total XP:</strong> ' + (char.total_xp || 0) + '</p></div>';
        contentHtml += '<div class="col-lg-4 col-md-4 col-sm-6"><p><strong>Spent XP:</strong> ' + (char.spent_xp || 0) + '</p></div>';
        contentHtml += '<div class="col-lg-4 col-md-4 col-sm-12"><p><strong>Available XP:</strong> ' + ((char.total_xp || 0) - (char.spent_xp || 0)) + '</p></div>';
        contentHtml += '</div>';
        
        // Traits
        if (currentViewData.traits && currentViewData.traits.length > 0) {
            const physical = currentViewData.traits.filter(t => t.trait_category === 'Physical');
            const social = currentViewData.traits.filter(t => t.trait_category === 'Social');
            const mental = currentViewData.traits.filter(t => t.trait_category === 'Mental');
            
            contentHtml += '<div class="row g-4 mb-4">';
            contentHtml += '<div class="col-md-6">';
            if (physical.length > 0) {
                contentHtml += '<h3>Physical Traits (' + physical.length + ')</h3>';
                contentHtml += '<div class="trait-list">';
                physical.forEach(t => {
                    const xpInfo = t.xp_cost ? ' (XP: ' + t.xp_cost + ')' : '';
                    contentHtml += '<span class="trait-badge">' + t.trait_name + xpInfo + '</span>';
                });
                contentHtml += '</div>';
            }
            contentHtml += '</div>';
            
            contentHtml += '<div class="col-md-6">';
            if (social.length > 0) {
                contentHtml += '<h3>Social Traits (' + social.length + ')</h3>';
                contentHtml += '<div class="trait-list">';
                social.forEach(t => {
                    const xpInfo = t.xp_cost ? ' (XP: ' + t.xp_cost + ')' : '';
                    contentHtml += '<span class="trait-badge">' + t.trait_name + xpInfo + '</span>';
                });
                contentHtml += '</div>';
            }
            contentHtml += '</div>';
            contentHtml += '</div>';
            
            if (mental.length > 0) {
                contentHtml += '<h3>Mental Traits (' + mental.length + ')</h3>';
                contentHtml += '<div class="trait-list">';
                mental.forEach(t => {
                    const xpInfo = t.xp_cost ? ' (XP: ' + t.xp_cost + ')' : '';
                    contentHtml += '<span class="trait-badge">' + t.trait_name + xpInfo + '</span>';
                });
                contentHtml += '</div>';
            }
        }
        
        // Abilities - always show section header
        contentHtml += '<h3>Abilities</h3>';
        if (currentViewData.abilities && currentViewData.abilities.length > 0) {
            // Group by category (case-insensitive matching)
            const talents = currentViewData.abilities.filter(a => a.ability_category && a.ability_category.toLowerCase() === 'talents');
            const skills = currentViewData.abilities.filter(a => a.ability_category && a.ability_category.toLowerCase() === 'skills');
            const knowledges = currentViewData.abilities.filter(a => a.ability_category && a.ability_category.toLowerCase() === 'knowledges');
            const uncategorized = currentViewData.abilities.filter(a => !a.ability_category || 
                (a.ability_category.toLowerCase() !== 'talents' && 
                 a.ability_category.toLowerCase() !== 'skills' && 
                 a.ability_category.toLowerCase() !== 'knowledges'));
            
            if (talents.length > 0 || skills.length > 0 || knowledges.length > 0 || uncategorized.length > 0) {
                contentHtml += '<div class="row g-4 mb-4">';
                if (talents.length > 0) {
                    contentHtml += '<div class="col-md-6">';
                    contentHtml += '<h4>Talents</h4>';
                    contentHtml += '<div class="trait-list">';
                    talents.forEach(a => {
                        let badge = a.ability_name + ' x' + a.level;
                        if (a.specialization) badge += ' (' + a.specialization + ')';
                        if (a.xp_cost) badge += ' [XP: ' + a.xp_cost + ']';
                        contentHtml += '<span class="trait-badge">' + badge.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>';
                    });
                    contentHtml += '</div>';
                    contentHtml += '</div>';
                }
                
                if (skills.length > 0) {
                    contentHtml += '<div class="col-md-6">';
                    contentHtml += '<h4>Skills</h4>';
                    contentHtml += '<div class="trait-list">';
                    skills.forEach(a => {
                        let badge = a.ability_name + ' x' + a.level;
                        if (a.specialization) badge += ' (' + a.specialization + ')';
                        if (a.xp_cost) badge += ' [XP: ' + a.xp_cost + ']';
                        contentHtml += '<span class="trait-badge">' + badge.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>';
                    });
                    contentHtml += '</div>';
                    contentHtml += '</div>';
                }
                contentHtml += '</div>';
                
                if (knowledges.length > 0) {
                    contentHtml += '<h4>Knowledges</h4>';
                    contentHtml += '<div class="trait-list">';
                    knowledges.forEach(a => {
                        let badge = a.ability_name + ' x' + a.level;
                        if (a.specialization) badge += ' (' + a.specialization + ')';
                        if (a.xp_cost) badge += ' [XP: ' + a.xp_cost + ']';
                        contentHtml += '<span class="trait-badge">' + badge.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>';
                    });
                    contentHtml += '</div>';
                }
                
                if (uncategorized.length > 0) {
                    contentHtml += '<h4>Other Abilities</h4>';
                    contentHtml += '<div class="trait-list">';
                    uncategorized.forEach(a => {
                        let badge = a.ability_name + ' x' + a.level;
                        if (a.specialization) badge += ' (' + a.specialization + ')';
                        if (a.xp_cost) badge += ' [XP: ' + a.xp_cost + ']';
                        if (a.ability_category) badge += ' [' + a.ability_category + ']';
                        contentHtml += '<span class="trait-badge">' + badge.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>';
                    });
                    contentHtml += '</div>';
                }
            } else {
                contentHtml += '<p class="empty-state">No abilities recorded.</p>';
            }
        } else {
            contentHtml += '<p class="empty-state">No abilities recorded.</p>';
        }
        
        // Disciplines - always show section header
        contentHtml += '<h3>Disciplines</h3>';
        if (currentViewData.disciplines && currentViewData.disciplines.length > 0) {
            contentHtml += '<div class="discipline-list">';
            currentViewData.disciplines.forEach(d => {
                const discName = (d.discipline_name || 'Unknown').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                const level = d.level || 0;
                const xpInfo = d.xp_cost ? ' [XP: ' + d.xp_cost + ']' : '';
                const powerCount = d.power_count || (d.powers ? d.powers.length : 0);
                const isCustom = d.is_custom || false;
                
                contentHtml += '<div class="discipline-item">';
                contentHtml += '<div style="width: 100%;">';
                contentHtml += '<div style="display: flex; justify-content: space-between; align-items: center;">';
                contentHtml += '<strong>' + discName + ' ' + level + '</strong>';
                if (powerCount > 0) {
                    contentHtml += '<span style="color: #c4a037;">' + powerCount + ' powers</span>';
                } else if (isCustom) {
                    contentHtml += '<span style="color: #999; font-style: italic;">Custom/Path</span>';
                }
                contentHtml += '</div>';
                
                // Show powers if available
                if (d.powers && d.powers.length > 0) {
                    contentHtml += '<div class="powers-list" style="margin-top: 8px; padding-left: 20px;">';
                    d.powers.forEach(power => {
                        const powerName = (power.power_name || 'Unknown').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                        contentHtml += '<div style="color: #c4a037; font-size: 0.9em;">• ' + powerName + ' <span style="color: #999; font-size: 0.85em;">(Level ' + (power.level || 0) + ')</span></div>';
                    });
                    contentHtml += '</div>';
                }
                
                contentHtml += '</div>';
                contentHtml += '</div>';
            });
            contentHtml += '</div>';
        } else {
            contentHtml += '<p class="empty-state">No disciplines recorded.</p>';
        }
        
        // Backgrounds
        if (currentViewData.backgrounds && currentViewData.backgrounds.length > 0) {
            contentHtml += '<h3>Backgrounds</h3>';
            contentHtml += '<div class="trait-list">';
            currentViewData.backgrounds.forEach(b => {
                let badge = b.background_name + ' x' + b.level;
                if (b.xp_cost) badge += ' [XP: ' + b.xp_cost + ']';
                contentHtml += '<span class="trait-badge">' + badge + '</span>';
            });
            contentHtml += '</div>';
        }
        
        // Morality & Virtues
        if (currentViewData.morality) {
            const m = currentViewData.morality;
            contentHtml += '<h3>Morality & Virtues</h3>';
            contentHtml += '<div class="row g-3 mt-2">';
            if (m.path_name) contentHtml += '<div class="col-lg-4 col-md-4 col-sm-6"><p><strong>Path:</strong> ' + m.path_name + ' (' + (m.path_rating || 'N/A') + ')</p></div>';
            if (m.humanity !== null && m.humanity !== undefined) contentHtml += '<div class="col-lg-4 col-md-4 col-sm-6"><p><strong>Humanity:</strong> ' + m.humanity + '/10</p></div>';
            contentHtml += '<div class="col-lg-4 col-md-4 col-sm-6"><p><strong>Willpower:</strong> ' + (m.willpower_current || 0) + '/' + (m.willpower_permanent || 0) + '</p></div>';
            if (m.conscience !== null && m.conscience !== undefined) contentHtml += '<div class="col-lg-4 col-md-4 col-sm-6"><p><strong>Conscience:</strong> ' + m.conscience + '</p></div>';
            if (m.self_control !== null && m.self_control !== undefined) contentHtml += '<div class="col-lg-4 col-md-4 col-sm-6"><p><strong>Self-Control:</strong> ' + m.self_control + '</p></div>';
            if (m.courage !== null && m.courage !== undefined) contentHtml += '<div class="col-lg-4 col-md-4 col-sm-6"><p><strong>Courage:</strong> ' + m.courage + '</p></div>';
            contentHtml += '</div>';
        }
        
        // Merits & Flaws
        if (currentViewData.merits_flaws && currentViewData.merits_flaws.length > 0) {
            const merits = currentViewData.merits_flaws.filter(m => m.type === 'merit');
            const flaws = currentViewData.merits_flaws.filter(m => m.type === 'flaw');
            
            contentHtml += '<div class="row g-4 mb-4">';
            if (merits.length > 0) {
                contentHtml += '<div class="col-md-6">';
                contentHtml += '<h3>Merits</h3>';
                merits.forEach(m => {
                    let badge = m.name + ' (' + m.point_value + ')';
                    if (m.xp_bonus) badge += ' [XP Bonus: ' + m.xp_bonus + ']';
                    contentHtml += '<div class="merit-flaw-item">';
                    contentHtml += '<span class="trait-badge">' + badge.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>';
                    if (m.category) contentHtml += '<span class="item-category">' + m.category.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>';
                    if (m.description) {
                        const descEscaped = m.description.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
                        contentHtml += '<p class="item-description">' + descEscaped.replace(/\n/g, '<br>') + '</p>';
                    }
                    contentHtml += '</div>';
                });
                contentHtml += '</div>';
            }
            
            if (flaws.length > 0) {
                contentHtml += '<div class="col-md-6">';
                contentHtml += '<h3>Flaws</h3>';
                flaws.forEach(f => {
                    let badge = f.name + ' (' + f.point_value + ')';
                    if (f.xp_bonus) badge += ' [XP Bonus: ' + f.xp_bonus + ']';
                    contentHtml += '<div class="merit-flaw-item">';
                    contentHtml += '<span class="trait-badge">' + badge.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>';
                    if (f.category) contentHtml += '<span class="item-category">' + f.category.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>';
                    if (f.description) {
                        const descEscaped = f.description.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
                        contentHtml += '<p class="item-description">' + descEscaped.replace(/\n/g, '<br>') + '</p>';
                    }
                    contentHtml += '</div>';
                });
                contentHtml += '</div>';
            }
            contentHtml += '</div>';
        }
        
        // Status & Resources - always show section
        contentHtml += '<h3>Status & Resources</h3>';
        if (currentViewData.status) {
            const s = currentViewData.status;
            contentHtml += '<div class="row g-3 mt-2">';
            contentHtml += '<div class="col-md-6"><p><strong>Health Levels:</strong> ' + (s.health_levels || 'N/A') + '</p></div>';
            contentHtml += '<div class="col-md-6"><p><strong>Blood Pool:</strong> ' + (s.blood_pool_current || 0) + '/' + (s.blood_pool_maximum || 0) + '</p></div>';
            if (s.sect_status) contentHtml += '<div class="col-md-6"><p><strong>Sect Status:</strong> ' + s.sect_status + '</p></div>';
            if (s.clan_status) contentHtml += '<div class="col-md-6"><p><strong>Clan Status:</strong> ' + s.clan_status + '</p></div>';
            if (s.city_status) contentHtml += '<div class="col-md-6"><p><strong>City Status:</strong> ' + s.city_status + '</p></div>';
            contentHtml += '</div>';
        } else {
            contentHtml += '<p class="empty-state">No status information recorded.</p>';
        }
        
        // Custom Data
        contentHtml += '<h3>Custom Data</h3>';
        if (char.custom_data) {
            try {
                const customData = typeof char.custom_data === 'string' ? JSON.parse(char.custom_data) : char.custom_data;
                contentHtml += '<div class="text-content">';
                contentHtml += '<pre class="custom-data-json">' + JSON.stringify(customData, null, 2).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</pre>';
                contentHtml += '</div>';
            } catch (e) {
                contentHtml += '<div class="text-content">' + char.custom_data.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>') + '</div>';
            }
        } else {
            contentHtml += '<p class="empty-state">No custom data recorded.</p>';
        }
        
        // Coterie
        contentHtml += '<h3>Coterie</h3>';
        if (currentViewData.coteries && currentViewData.coteries.length > 0) {
            contentHtml += '<div class="row g-3 mt-2">';
            currentViewData.coteries.forEach(c => {
                contentHtml += '<div class="col-md-6">';
                contentHtml += '<div class="coterie-card">';
                contentHtml += '<h4>' + (c.coterie_name || 'Unknown Coterie').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</h4>';
                if (c.coterie_type) contentHtml += '<p><strong>Type:</strong> ' + c.coterie_type.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</p>';
                if (c.role) contentHtml += '<p><strong>Role:</strong> ' + c.role.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</p>';
                if (c.description) {
                    const descEscaped = c.description.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
                    contentHtml += '<p><strong>Description:</strong> ' + descEscaped.replace(/\n/g, '<br>') + '</p>';
                }
                if (c.notes) {
                    const notesEscaped = c.notes.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
                    contentHtml += '<p><strong>Notes:</strong> ' + notesEscaped.replace(/\n/g, '<br>') + '</p>';
                }
                contentHtml += '</div>';
                contentHtml += '</div>';
            });
            contentHtml += '</div>';
        } else {
            contentHtml += '<p class="empty-state">No coterie associations recorded.</p>';
        }
        
        // Relationships
        contentHtml += '<h3>Relationships</h3>';
        if (currentViewData.relationships && currentViewData.relationships.length > 0) {
            contentHtml += '<div class="row g-3 mt-2">';
            currentViewData.relationships.forEach(r => {
                contentHtml += '<div class="col-md-6">';
                contentHtml += '<div class="relationship-card">';
                contentHtml += '<h4>' + (r.related_character_name || 'Unknown Character').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</h4>';
                if (r.relationship_type) contentHtml += '<p><strong>Type:</strong> ' + r.relationship_type.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</p>';
                if (r.relationship_subtype) contentHtml += '<p><strong>Subtype:</strong> ' + r.relationship_subtype.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</p>';
                if (r.strength) contentHtml += '<p><strong>Strength:</strong> ' + r.strength.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</p>';
                if (r.description) {
                    const descEscaped = r.description.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
                    contentHtml += '<p><strong>Description:</strong> ' + descEscaped.replace(/\n/g, '<br>') + '</p>';
                }
                contentHtml += '</div>';
                contentHtml += '</div>';
            });
            contentHtml += '</div>';
        } else {
            contentHtml += '<p class="empty-state">No relationships recorded.</p>';
        }
        
        // Biography
        if (char.biography) {
            contentHtml += '<h3>Biography</h3>';
            const bioEscaped = char.biography.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            contentHtml += '<div class="text-content">' + bioEscaped.replace(/\n/g, '<br>') + '</div>';
        }
        
        // Equipment
        if (char.equipment) {
            contentHtml += '<h3>Equipment</h3>';
            const equipEscaped = char.equipment.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            contentHtml += '<div class="text-content">' + equipEscaped.replace(/\n/g, '<br>') + '</div>';
        }
        
        // Notes
        if (char.notes) {
            contentHtml += '<h3>Notes</h3>';
            const notesEscaped = char.notes.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            contentHtml += '<div class="text-content">' + notesEscaped.replace(/\n/g, '<br>') + '</div>';
        }
        
        // Metadata
        contentHtml += '<h3>Character Metadata</h3>';
        contentHtml += '<div class="row g-3 mt-2">';
        if (char.created_at) {
            const created = new Date(char.created_at);
            contentHtml += '<div class="col-md-6"><p><strong>Created:</strong> ' + created.toLocaleString() + '</p></div>';
        }
        if (char.updated_at) {
            const updated = new Date(char.updated_at);
            contentHtml += '<div class="col-md-6"><p><strong>Last Updated:</strong> ' + updated.toLocaleString() + '</p></div>';
        }
        contentHtml += '</div>';
        
        contentHtml += '</div>';
    }
    
    document.getElementById('viewCharacterContent').innerHTML = contentHtml;
}

function closeViewModal() {
    const modal = document.getElementById('viewModal');
    modal.classList.remove('active');
    // Remove click handler when closing
    if (modalClickHandler) {
        modal.removeEventListener('click', modalClickHandler);
        modalClickHandler = null;
    }
}

