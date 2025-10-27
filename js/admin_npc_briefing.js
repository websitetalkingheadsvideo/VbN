/**
 * Admin NPC Briefing - JavaScript
 * Handles table interactions, briefing modal, and note editing
 */

let currentCharacterId = null;
let currentMode = 'view';
let currentPage = 1;
let pageSize = 20;
let sortColumn = 'id';
let sortDirection = 'desc';

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    updatePagination();
});

function initializeEventListeners() {
    // Briefing buttons
    document.querySelectorAll('.briefing-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const characterId = this.getAttribute('data-id');
            openBriefingModal(characterId);
        });
    });

    // Edit buttons
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const characterId = this.getAttribute('data-id');
            openEditNotesModal(characterId);
        });
    });

    // Table sorting
    document.querySelectorAll('th[data-sort]').forEach(th => {
        th.addEventListener('click', function() {
            const column = this.getAttribute('data-sort');
            sortTable(column);
        });
    });

    // Filters
    document.getElementById('clanFilter')?.addEventListener('change', applyFilters);
    document.getElementById('characterSearch')?.addEventListener('input', applyFilters);
    document.getElementById('pageSize')?.addEventListener('change', function() {
        pageSize = parseInt(this.value);
        currentPage = 1;
        updatePagination();
    });
}

function openBriefingModal(characterId) {
    currentCharacterId = characterId;
    const modal = document.getElementById('briefingModal');
    modal.classList.add('active');
    
    // Fetch character data
    fetch(`api_npc_briefing.php?id=${characterId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayBriefing(data);
            } else {
                document.getElementById('briefingContent').innerHTML = 
                    `<p style="color: red;">Error: ${data.message}</p>`;
            }
        })
        .catch(error => {
            document.getElementById('briefingContent').innerHTML = 
                `<p style="color: red;">Error loading character data: ${error}</p>`;
        });
}

function closeBriefingModal() {
    const modal = document.getElementById('briefingModal');
    modal.classList.remove('active');
    currentCharacterId = null;
    currentMode = 'view';
}

function openEditNotesModal(characterId) {
    currentCharacterId = characterId;
    const modal = document.getElementById('editNotesModal');
    
    // Fetch current notes
    fetch(`api_npc_briefing.php?id=${characterId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const char = data.character;
                document.getElementById('editCharacterName').textContent = char.character_name;
                document.getElementById('editAgentNotes').value = char.agentNotes || '';
                document.getElementById('editActingNotes').value = char.actingNotes || '';
                modal.classList.add('active');
            } else {
                alert('Error loading character data: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error loading character data: ' + error);
        });
}

function closeEditNotesModal() {
    const modal = document.getElementById('editNotesModal');
    modal.classList.remove('active');
    currentCharacterId = null;
}

function saveNotesFromEdit() {
    const agentNotes = document.getElementById('editAgentNotes').value;
    const actingNotes = document.getElementById('editActingNotes').value;
    
    const saveBtn = document.getElementById('saveEditNotesBtn');
    const originalText = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';
    
    fetch('api_update_npc_notes.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            character_id: currentCharacterId,
            agentNotes: agentNotes,
            actingNotes: actingNotes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            saveBtn.textContent = 'Saved!';
            setTimeout(() => {
                closeEditNotesModal();
            }, 500);
        } else {
            alert('Error saving notes: ' + data.message);
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;
        }
    })
    .catch(error => {
        alert('Error saving notes: ' + error);
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    });
}

function setBriefingMode(mode, event) {
    currentMode = mode;
    
    // Update button states
    document.querySelectorAll('.mode-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Show/hide save button
    const saveBtn = document.getElementById('saveNotesBtn');
    if (mode === 'edit') {
        saveBtn.style.display = 'block';
    } else {
        saveBtn.style.display = 'none';
    }
    
    // Re-fetch and display data
    if (currentCharacterId) {
        fetch(`api_npc_briefing.php?id=${currentCharacterId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayBriefing(data);
                }
            });
    }
}

function displayBriefing(data) {
    const char = data.character;
    const traits = data.traits;
    const abilities = data.abilities;
    const disciplines = data.disciplines;
    const backgrounds = data.backgrounds;
    
    // Update modal title
    document.getElementById('briefingCharacterName').textContent = char.character_name;
    
    let html = '';
    
    if (currentMode === 'view') {
        // Agent View Mode
        html = `
            <h3>CORE IDENTITY</h3>
            <div class="info-grid">
                <p><strong>Nature:</strong> ${char.nature || 'Not set'}</p>
                <p><strong>Demeanor:</strong> ${char.demeanor || 'Not set'}</p>
                <p><strong>Concept:</strong> ${char.concept || 'Not set'}</p>
                <p><strong>Clan:</strong> ${char.clan || 'Unknown'}</p>
                <p><strong>Generation:</strong> ${char.generation}th</p>
                <p><strong>Sire:</strong> ${char.sire || 'Unknown'}</p>
            </div>
            
            <h3>TRAITS</h3>
            <div class="trait-section">
                <p><strong>Physical:</strong></p>
                <div class="trait-list">
                    ${formatTraits(traits.physical)}
                </div>
                
                <p><strong>Social:</strong></p>
                <div class="trait-list">
                    ${formatTraits(traits.social)}
                </div>
                
                <p><strong>Mental:</strong></p>
                <div class="trait-list">
                    ${formatTraits(traits.mental)}
                </div>
            </div>
            
            ${abilities.length > 0 ? `
            <h3>KEY ABILITIES</h3>
            <div class="ability-list">
                ${formatAbilities(abilities)}
            </div>
            ` : ''}
            
            ${disciplines.length > 0 ? `
            <h3>DISCIPLINES</h3>
            <div class="ability-list">
                ${formatDisciplines(disciplines)}
            </div>
            ` : ''}
            
            ${backgrounds.length > 0 ? `
            <h3>BACKGROUNDS</h3>
            <div class="ability-list">
                ${formatBackgrounds(backgrounds)}
            </div>
            ` : ''}
            
            ${char.biography ? `
            <h3>BIOGRAPHY</h3>
            <p>${char.biography.replace(/\n/g, '<br>')}</p>
            ` : ''}
            
            ${char.agentNotes ? `
            <h3>AGENT NOTES</h3>
            <p>${char.agentNotes.replace(/\n/g, '<br>')}</p>
            ` : '<h3>AGENT NOTES</h3><p style="color: #888; font-style: italic;">No agent notes yet. Switch to Edit Notes mode to add them.</p>'}
            
            ${char.actingNotes ? `
            <h3>ACTING NOTES (Post-Session)</h3>
            <p>${char.actingNotes.replace(/\n/g, '<br>')}</p>
            ` : '<h3>ACTING NOTES (Post-Session)</h3><p style="color: #888; font-style: italic;">No acting notes yet. Switch to Edit Notes mode to add them.</p>'}
        `;
    } else {
        // Edit Mode
        html = `
            <h3>AGENT NOTES</h3>
            <p style="color: #b8a090; font-size: 0.9em; margin-bottom: 10px;">
                AI-formatted briefing with nature/demeanor, traits, and key information for playing this character.
            </p>
            <textarea id="agentNotesField">${char.agentNotes || ''}</textarea>
            
            <h3>ACTING NOTES (Post-Session)</h3>
            <p style="color: #b8a090; font-size: 0.9em; margin-bottom: 10px;">
                Your notes after playing this character in a session.
            </p>
            <textarea id="actingNotesField">${char.actingNotes || ''}</textarea>
        `;
    }
    
    document.getElementById('briefingContent').innerHTML = html;
}

function formatTraits(traitArray) {
    if (!traitArray || traitArray.length === 0) {
        return '<span style="color: #888; font-style: italic;">None</span>';
    }
    return traitArray.map(trait => {
        const className = trait.is_negative ? 'trait-badge negative' : 'trait-badge';
        return `<span class="${className}">${trait.name}</span>`;
    }).join('');
}

function formatAbilities(abilities) {
    if (!abilities || abilities.length === 0) {
        return '<span style="color: #888; font-style: italic;">None</span>';
    }
    
    // Format as comma-separated list with "x" notation
    return abilities.map(a => {
        let display = `${a.ability_name} x${a.level}`;
        if (a.specialization) {
            display += ` (${a.specialization})`;
        }
        return display;
    }).join(', ');
}

function formatDisciplines(disciplines) {
    if (!disciplines || disciplines.length === 0) {
        return '<span style="color: #888; font-style: italic;">None</span>';
    }
    
    return disciplines.map(d => `${d.discipline_name} x${d.level}`).join(', ');
}

function formatBackgrounds(backgrounds) {
    if (!backgrounds || backgrounds.length === 0) {
        return '<span style="color: #888; font-style: italic;">None</span>';
    }
    
    return backgrounds.map(b => {
        let display = `${b.background_name} x${b.level}`;
        if (b.description) {
            display += ` (${b.description})`;
        }
        return display;
    }).join(', ');
}

function saveNotes() {
    const agentNotes = document.getElementById('agentNotesField')?.value || null;
    const actingNotes = document.getElementById('actingNotesField')?.value || null;
    
    const saveBtn = document.getElementById('saveNotesBtn');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';
    
    fetch('api_update_npc_notes.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            character_id: currentCharacterId,
            agentNotes: agentNotes,
            actingNotes: actingNotes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Notes saved successfully!');
            saveBtn.textContent = 'Save Notes';
            saveBtn.disabled = false;
        } else {
            alert('Error saving notes: ' + data.message);
            saveBtn.textContent = 'Save Notes';
            saveBtn.disabled = false;
        }
    })
    .catch(error => {
        alert('Error saving notes: ' + error);
        saveBtn.textContent = 'Save Notes';
        saveBtn.disabled = false;
    });
}

function sortTable(column) {
    if (sortColumn === column) {
        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn = column;
        sortDirection = 'asc';
    }
    
    // Update header indicators
    document.querySelectorAll('th[data-sort]').forEach(th => {
        th.classList.remove('sorted-asc', 'sorted-desc');
    });
    
    const header = document.querySelector(`th[data-sort="${column}"]`);
    header.classList.add(`sorted-${sortDirection}`);
    
    // Sort rows
    const tbody = document.querySelector('#characterTable tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        let aVal = a.cells[getColumnIndex(column)].textContent.trim();
        let bVal = b.cells[getColumnIndex(column)].textContent.trim();
        
        // Handle numeric columns
        if (column === 'id' || column === 'generation') {
            aVal = parseInt(aVal);
            bVal = parseInt(bVal);
        }
        
        if (sortDirection === 'asc') {
            return aVal > bVal ? 1 : -1;
        } else {
            return aVal < bVal ? 1 : -1;
        }
    });
    
    rows.forEach(row => tbody.appendChild(row));
    currentPage = 1;
    updatePagination();
}

function getColumnIndex(column) {
    const columns = ['id', 'character_name', 'clan', 'generation', 'status', 'created_at'];
    return columns.indexOf(column);
}

function applyFilters() {
    const clanFilter = document.getElementById('clanFilter').value;
    const searchTerm = document.getElementById('characterSearch').value.toLowerCase();
    
    const rows = document.querySelectorAll('#characterTable tbody tr');
    
    rows.forEach(row => {
        const clan = row.getAttribute('data-clan');
        const name = row.getAttribute('data-name').toLowerCase();
        
        let showRow = true;
        
        if (clanFilter !== 'all' && clan !== clanFilter) {
            showRow = false;
        }
        
        if (searchTerm && !name.includes(searchTerm)) {
            showRow = false;
        }
        
        if (showRow) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    });
    
    currentPage = 1;
    updatePagination();
}

function updatePagination() {
    const rows = document.querySelectorAll('#characterTable tbody tr:not(.hidden)');
    const totalRows = rows.length;
    const totalPages = Math.ceil(totalRows / pageSize);
    
    // Hide all rows first
    rows.forEach((row, index) => {
        const startIndex = (currentPage - 1) * pageSize;
        const endIndex = startIndex + pageSize;
        
        if (index >= startIndex && index < endIndex) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update pagination info
    const startNum = totalRows > 0 ? (currentPage - 1) * pageSize + 1 : 0;
    const endNum = Math.min(currentPage * pageSize, totalRows);
    document.getElementById('paginationInfo').textContent = 
        `Showing ${startNum}-${endNum} of ${totalRows} NPCs`;
    
    // Update pagination buttons
    const buttonsContainer = document.getElementById('paginationButtons');
    buttonsContainer.innerHTML = '';
    
    if (totalPages > 1) {
        // Previous button
        if (currentPage > 1) {
            const prevBtn = createPageButton('‹ Prev', currentPage - 1);
            buttonsContainer.appendChild(prevBtn);
        }
        
        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                const pageBtn = createPageButton(i, i);
                if (i === currentPage) {
                    pageBtn.classList.add('active');
                }
                buttonsContainer.appendChild(pageBtn);
            } else if (i === currentPage - 3 || i === currentPage + 3) {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.style.padding = '8px';
                ellipsis.style.color = '#b8a090';
                buttonsContainer.appendChild(ellipsis);
            }
        }
        
        // Next button
        if (currentPage < totalPages) {
            const nextBtn = createPageButton('Next ›', currentPage + 1);
            buttonsContainer.appendChild(nextBtn);
        }
    }
}

function createPageButton(text, page) {
    const btn = document.createElement('button');
    btn.textContent = text;
    btn.className = 'page-btn';
    btn.addEventListener('click', () => {
        currentPage = page;
        updatePagination();
    });
    return btn;
}

// Close modal on outside click
window.addEventListener('click', function(event) {
    const modal = document.getElementById('briefingModal');
    if (event.target === modal) {
        closeBriefingModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeBriefingModal();
        closeEditNotesModal();
    }
});

// Close edit modal on outside click
window.addEventListener('click', function(event) {
    const editModal = document.getElementById('editNotesModal');
    if (event.target === editModal) {
        closeEditNotesModal();
    }
});

