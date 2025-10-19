/**
 * Character Questionnaire JavaScript
 * Handles question navigation and clan scoring
 */

// Global variables
let currentQuestion = 1;
let totalQuestions = 19;
let answers = {};
let clanScores = {
    ventrue: 0,
    tremere: 0,
    brujah: 0,
    nosferatu: 0,
    malkavian: 0,
    toreador: 0,
    gangrel: 0
};

// Admin debug panel
let isAdmin = false;
let debugPanelVisible = false;

// Clan scoring matrix - maps answers to clan points
const scoringMatrix = {
    'embrace_type': {
        'voluntary': { ventrue: 3, tremere: 2 },
        'ritualistic': { tremere: 3, nosferatu: 2 },
        'accidental': { brujah: 3, gangrel: 2 },
        'supernatural': { malkavian: 3, nosferatu: 2 }
    },
    'personality': {
        'passionate': { brujah: 2, toreador: 2 },
        'calculating': { ventrue: 2, tremere: 2 },
        'impulsive': { brujah: 2, gangrel: 2 },
        'compassionate': { toreador: 2, malkavian: 1 },
        'sardonic': { malkavian: 2, nosferatu: 2 },
        'pragmatic': { ventrue: 2, tremere: 2 }
    },
    'supernatural_power': {
        'strength': { brujah: 3, gangrel: 2 },
        'manipulation': { ventrue: 3, tremere: 2 },
        'knowledge': { tremere: 3, malkavian: 2 },
        'survival': { gangrel: 3, nosferatu: 2 }
    },
    'personal_goal': {
        'survival': { gangrel: 3, nosferatu: 2 },
        'revenge': { brujah: 3, nosferatu: 2 },
        'knowledge': { tremere: 3, malkavian: 2 },
        'redemption': { toreador: 3, malkavian: 1 }
    },
    'human_society_view': {
        'superior_detached': { ventrue: 3, tremere: 2 },
        'curious_change': { toreador: 3, malkavian: 2 },
        'protect_control': { ventrue: 2, tremere: 3 },
        'conflicted': { toreador: 2, malkavian: 3 }
    },
    'supernatural_beings_view': {
        'threats_eliminate': { brujah: 3, gangrel: 2 },
        'potential_allies': { tremere: 3, toreador: 2 },
        'curious_subjects': { tremere: 2, malkavian: 3 },
        'annoying_complications': { nosferatu: 3, gangrel: 2 }
    },
    'hidden_secret': {
        'hidden_talent': { toreador: 3, tremere: 2 },
        'past_trauma': { nosferatu: 3, malkavian: 2 },
        'forbidden_desire': { malkavian: 3, toreador: 2 },
        'supernatural_weakness': { nosferatu: 2, gangrel: 3 }
    },
    'greatest_fear': {
        'solitude': { nosferatu: 3, malkavian: 2 },
        'loss_humanity': { toreador: 3, tremere: 2 },
        'exposure_nature': { nosferatu: 2, tremere: 3 },
        'becoming_powerful': { gangrel: 3, brujah: 2 }
    },
    'scenario_politics': {
        'humiliated_underground': { nosferatu: 3, brujah: 2 },
        'true_potential': { ventrue: 3, tremere: 2 },
        'unexpected_allies': { tremere: 2, toreador: 3 }
    },
    'scenario_encounter': {
        'withdrawn_isolation': { nosferatu: 3, gangrel: 2 },
        'embrace_mystery': { malkavian: 3, tremere: 2 },
        'seek_others': { toreador: 3, ventrue: 2 }
    },
    'scenario_hunt': {
        'ethical_struggle': { toreador: 3, tremere: 2 },
        'ruthless_efficient': { brujah: 3, gangrel: 2 },
        'balance_restraint': { ventrue: 3, tremere: 2 }
    },
    'scenario_bloodline': {
        'connected_legacy': { tremere: 3, ventrue: 2 },
        'burden_existence': { nosferatu: 3, malkavian: 2 },
        'protective_knowledge': { tremere: 2, ventrue: 3 }
    },
    'scenario_threat': {
        'diplomatic_resolution': { ventrue: 3, tremere: 2 },
        'direct_combat': { brujah: 3, gangrel: 2 },
        'strategic_analysis': { tremere: 3, ventrue: 2 }
    },
    'workplace_betrayal': {
        'document_build_case': { nosferatu: 3, tremere: 2 },
        'confront_publicly': { brujah: 3, gangrel: 2 },
        'learn_political_game': { ventrue: 3, tremere: 2 }
    },
    'family_crisis': {
        'take_charge_research': { tremere: 3, ventrue: 2 },
        'hard_truth_consequences': { brujah: 3, gangrel: 2 },
        'protect_parents': { toreador: 3, nosferatu: 2 }
    },
    'social_dilemma': {
        'redirect_conversation': { ventrue: 3, toreador: 2 },
        'call_out_bully': { brujah: 3, gangrel: 2 },
        'support_victim_later': { tremere: 3, nosferatu: 2 }
    },
    'moral_test': {
        'refuse_deception_ultimatum': { brujah: 3, gangrel: 2 },
        'distance_avoid_involvement': { nosferatu: 3, gangrel: 2 },
        'strategic_positioning': { ventrue: 3, tremere: 2 }
    },
    'power_opportunity': {
        'build_relationships_balance': { ventrue: 3, toreador: 2 },
        'loyalty_uncompromised': { brujah: 3, gangrel: 2 },
        'excel_advocate_strength': { tremere: 3, nosferatu: 2 }
    },
    'life_choice': {
        'take_job_support_plan': { tremere: 3, ventrue: 2 },
        'turn_down_family_loyalty': { toreador: 3, gangrel: 2 },
        'negotiate_delayed_start': { ventrue: 3, tremere: 2 }
    }
};

// Initialize questionnaire when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Character Questionnaire loaded');
    initializeQuestionnaire();
});

/**
 * Initialize questionnaire functionality
 */
function initializeQuestionnaire() {
    console.log('Initializing questionnaire...');
    
    // Check if user is admin
    checkAdminStatus();
    
    // Initialize sessionStorage for clan scores
    initializeSessionStorage();
    
    // Set up event listeners
    setupEventListeners();
    
    // Update progress display
    updateProgress();
    
    // Update navigation buttons
    updateNavigationButtons();
    
    // Initialize debug panel if admin
    if (isAdmin) {
        initializeDebugPanel();
    }
    
    console.log('Questionnaire initialized');
}

/**
 * Check if user is admin (simple check for demo)
 */
function checkAdminStatus() {
    // Simple admin check - in production, this would be server-side
    const urlParams = new URLSearchParams(window.location.search);
    const adminParam = urlParams.get('admin');
    
    // Check for admin parameter or specific username
    if (adminParam === 'true' || adminParam === '1') {
        isAdmin = true;
    }
    
    // Also check if username contains 'admin' (for testing)
    const usernameElement = document.querySelector('.username');
    if (usernameElement && usernameElement.textContent.toLowerCase().includes('admin')) {
        isAdmin = true;
    }
    
    console.log('Admin status:', isAdmin);
}

/**
 * Initialize sessionStorage for clan scores
 */
function initializeSessionStorage() {
    // Check if this is a new quiz session
    const quizSession = sessionStorage.getItem('quizSessionId');
    const currentSession = Date.now().toString();
    
    if (!quizSession || quizSession !== currentSession) {
        // New quiz session - reset everything
        sessionStorage.setItem('quizSessionId', currentSession);
        sessionStorage.setItem('clanScores', JSON.stringify(clanScores));
        sessionStorage.setItem('questionnaireAnswers', JSON.stringify({}));
        console.log('New quiz session started - variables reset');
    } else {
        // Continue existing session
        clanScores = JSON.parse(sessionStorage.getItem('clanScores'));
        answers = JSON.parse(sessionStorage.getItem('questionnaireAnswers'));
        console.log('Continuing existing quiz session');
    }
}

/**
 * Set up event listeners
 */
function setupEventListeners() {
    // Next button
    const nextBtn = document.getElementById('next-btn');
    if (nextBtn) {
        nextBtn.addEventListener('click', nextQuestion);
    }
    
    // Previous button
    const prevBtn = document.getElementById('prev-btn');
    if (prevBtn) {
        prevBtn.addEventListener('click', previousQuestion);
    }
    
    // Submit button
    const submitBtn = document.getElementById('submit-btn');
    if (submitBtn) {
        submitBtn.addEventListener('click', submitQuestionnaire);
    }
    
    // Retake button
    const retakeBtn = document.getElementById('retake-btn');
    if (retakeBtn) {
        retakeBtn.addEventListener('click', retakeQuestionnaire);
    }
    
    // Create character button
    const createCharacterBtn = document.getElementById('create-character-btn');
    if (createCharacterBtn) {
        createCharacterBtn.addEventListener('click', createCharacter);
    }
    
    // Radio button changes
    const radioButtons = document.querySelectorAll('input[type="radio"]');
    radioButtons.forEach(radio => {
        radio.addEventListener('change', handleAnswerChange);
    });
    
    // Checkbox changes (for personality traits)
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', handleCheckboxChange);
    });
    
    // Debug panel toggle
    const debugToggle = document.getElementById('debug-toggle');
    if (debugToggle) {
        debugToggle.addEventListener('click', toggleDebugPanel);
    }
}

/**
 * Handle answer change (radio buttons)
 */
function handleAnswerChange(event) {
    const questionName = event.target.name;
    const answerValue = event.target.value;
    
    // Store answer
    answers[questionName] = answerValue;
    sessionStorage.setItem('questionnaireAnswers', JSON.stringify(answers));
    
    // Update clan scores
    updateClanScores(questionName, answerValue);
    
    // Update navigation buttons
    updateNavigationButtons();
    
    console.log(`Answer changed: ${questionName} = ${answerValue}`);
    
    // Update debug panel if admin
    if (isAdmin) {
        updateDebugPanel();
    }
}

/**
 * Handle checkbox change (personality traits)
 */
function handleCheckboxChange(event) {
    const questionName = event.target.name;
    const answerValue = event.target.value;
    const isChecked = event.target.checked;
    
    // Get all checked personality traits
    const checkedTraits = Array.from(document.querySelectorAll('input[name="personality[]"]:checked'))
        .map(checkbox => checkbox.value);
    
    // Store answer as array
    answers[questionName] = checkedTraits;
    sessionStorage.setItem('questionnaireAnswers', JSON.stringify(answers));
    
    // Update selection counter
    updatePersonalityCounter();
    
    // Update clan scores for personality question
    updatePersonalityScores(checkedTraits);
    
    // Update navigation buttons
    updateNavigationButtons();
    
    console.log(`Personality traits changed: ${checkedTraits.join(', ')}`);
    
    // Update debug panel if admin
    if (isAdmin) {
        updateDebugPanel();
    }
}

/**
 * Update clan scores based on answer
 */
function updateClanScores(questionName, answerValue) {
    if (scoringMatrix[questionName] && scoringMatrix[questionName][answerValue]) {
        const points = scoringMatrix[questionName][answerValue];
        
        Object.keys(points).forEach(clan => {
            clanScores[clan] += points[clan];
        });
        
        sessionStorage.setItem('clanScores', JSON.stringify(clanScores));
        console.log('Clan scores updated:', clanScores);
    }
}

/**
 * Update personality counter
 */
function updatePersonalityCounter() {
    const checkedTraits = document.querySelectorAll('input[name="personality[]"]:checked');
    const counter = document.getElementById('personality-count');
    
    if (counter) {
        counter.textContent = checkedTraits.length;
        
        // Change color based on count
        if (checkedTraits.length === 3) {
            counter.style.color = '#8B0000';
            counter.style.fontWeight = 'bold';
        } else if (checkedTraits.length > 3) {
            counter.style.color = '#ff6b6b';
            counter.style.fontWeight = 'bold';
        } else {
            counter.style.color = '#b8a090';
            counter.style.fontWeight = 'normal';
        }
    }
}

/**
 * Update clan scores for personality traits
 */
function updatePersonalityScores(checkedTraits) {
    // Reset personality scores first
    Object.keys(clanScores).forEach(clan => {
        // Remove previous personality scores (approximate)
        clanScores[clan] = Math.max(0, clanScores[clan] - 6); // Rough estimate
    });
    
    // Add scores for selected traits
    checkedTraits.forEach(trait => {
        if (scoringMatrix['personality'] && scoringMatrix['personality'][trait]) {
            const points = scoringMatrix['personality'][trait];
            Object.keys(points).forEach(clan => {
                clanScores[clan] += points[clan];
            });
        }
    });
    
    sessionStorage.setItem('clanScores', JSON.stringify(clanScores));
    console.log('Personality clan scores updated:', clanScores);
}

/**
 * Go to next question
 */
function nextQuestion() {
    if (currentQuestion < totalQuestions) {
        // Hide current question
        const currentSection = document.querySelector(`[data-question="${currentQuestion}"]`);
        if (currentSection) {
            currentSection.classList.remove('active');
        }
        
        // Show next question
        currentQuestion++;
        const nextSection = document.querySelector(`[data-question="${currentQuestion}"]`);
        if (nextSection) {
            nextSection.classList.add('active');
        }
        
        // Update progress
        updateProgress();
        updateNavigationButtons();
        
        console.log(`Moved to question ${currentQuestion}`);
    }
}

/**
 * Go to previous question
 */
function previousQuestion() {
    if (currentQuestion > 1) {
        // Hide current question
        const currentSection = document.querySelector(`[data-question="${currentQuestion}"]`);
        if (currentSection) {
            currentSection.classList.remove('active');
        }
        
        // Show previous question
        currentQuestion--;
        const prevSection = document.querySelector(`[data-question="${currentQuestion}"]`);
        if (prevSection) {
            prevSection.classList.add('active');
        }
        
        // Update progress
        updateProgress();
        updateNavigationButtons();
        
        console.log(`Moved to question ${currentQuestion}`);
    }
}

/**
 * Update progress bar and question counter
 */
function updateProgress() {
    const progressFill = document.getElementById('progress-fill');
    const currentQuestionSpan = document.getElementById('current-question');
    const totalQuestionsSpan = document.getElementById('total-questions');
    
    if (progressFill) {
        const progressPercent = (currentQuestion / totalQuestions) * 100;
        progressFill.style.width = `${progressPercent}%`;
    }
    
    if (currentQuestionSpan) {
        currentQuestionSpan.textContent = currentQuestion;
    }
    
    if (totalQuestionsSpan) {
        totalQuestionsSpan.textContent = totalQuestions;
    }
}

/**
 * Update navigation buttons state
 */
function updateNavigationButtons() {
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const submitBtn = document.getElementById('submit-btn');
    
    // Previous button
    if (prevBtn) {
        prevBtn.disabled = currentQuestion === 1;
    }
    
    // Next/Submit button
    if (currentQuestion === totalQuestions) {
        if (nextBtn) nextBtn.style.display = 'none';
        if (submitBtn) submitBtn.style.display = 'inline-block';
    } else {
        if (nextBtn) nextBtn.style.display = 'inline-block';
        if (submitBtn) submitBtn.style.display = 'none';
    }
    
    // Check if current question is answered
    const currentSection = document.querySelector(`[data-question="${currentQuestion}"]`);
    if (currentSection) {
        let isAnswered = false;
        
        if (currentQuestion === 2) {
            // Special handling for personality question (checkboxes)
            const checkboxes = currentSection.querySelectorAll('input[type="checkbox"]');
            const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
            isAnswered = checkedCount === 3;
        } else {
            // Regular radio button questions
            const radioButtons = currentSection.querySelectorAll('input[type="radio"]');
            isAnswered = Array.from(radioButtons).some(radio => radio.checked);
        }
        
        if (nextBtn && currentQuestion < totalQuestions) {
            nextBtn.disabled = !isAnswered;
        }
        
        if (submitBtn && currentQuestion === totalQuestions) {
            submitBtn.disabled = !isAnswered;
        }
    }
}

/**
 * Submit questionnaire and show results
 */
function submitQuestionnaire(event) {
    event.preventDefault();
    
    console.log('Submitting questionnaire...');
    console.log('Final answers:', answers);
    console.log('Final clan scores:', clanScores);
    
    // Determine winning clan
    const winningClan = determineWinningClan();
    
    // Show results
    showResults(winningClan);
}

/**
 * Determine winning clan based on scores
 */
function determineWinningClan() {
    let maxScore = 0;
    let winningClan = 'ventrue';
    
    Object.keys(clanScores).forEach(clan => {
        if (clanScores[clan] > maxScore) {
            maxScore = clanScores[clan];
            winningClan = clan;
        }
    });
    
    return winningClan;
}

/**
 * Show results section
 */
function showResults(winningClan) {
    const form = document.getElementById('questionnaire-form');
    const resultsSection = document.getElementById('results-section');
    
    if (form) form.style.display = 'none';
    if (resultsSection) resultsSection.style.display = 'block';
    
    // Update clan information using existing elements
    updateClanResult(winningClan);
}

/**
 * Update clan result with logo and information
 */
function updateClanResult(clan) {
    const clanData = getClanData(clan);
    
    // Map clan names to logo files
    const clanLogos = {
        ventrue: 'svgs/LogoClanVentrue.webp',
        tremere: 'svgs/LogoClanTremere.webp',
        brujah: 'svgs/LogoClanBrujah.webp',
        nosferatu: 'svgs/LogoClanNosferatu.webp',
        malkavian: 'svgs/LogoClanMalkavian.webp',
        toreador: 'svgs/LogoClanToreador.webp',
        gangrel: 'svgs/LogoClanGangrel.webp'
    };
    
    const logoPath = clanLogos[clan] || 'svgs/LogoClanVentrue.webp'; // fallback
    
    // Update the existing elements
    const clanLogo = document.getElementById('clan-logo');
    const clanNameElement = document.getElementById('clan-name');
    const clanDescriptionElement = document.getElementById('clan-description');
    
    if (clanLogo) {
        clanLogo.src = logoPath;
        clanLogo.alt = `${clanData.name} Clan Logo`;
    }
    if (clanNameElement) {
        clanNameElement.textContent = clanData.name;
    }
    if (clanDescriptionElement) {
        clanDescriptionElement.textContent = clanData.description;
    }
    
    console.log(`Clan result updated: ${clanData.name} with logo ${logoPath}`);
}

/**
 * Get clan data
 */
function getClanData(clan) {
    const clanData = {
        ventrue: {
            name: 'Ventrue',
            description: 'The aristocratic Ventrue are natural leaders and social manipulators. They excel at politics, business, and commanding others through their natural charisma and supernatural powers of domination.'
        },
        tremere: {
            name: 'Tremere',
            description: 'The scholarly Tremere are masters of blood magic and ancient knowledge. They seek power through understanding the supernatural world and manipulating it through their unique thaumaturgy.'
        },
        brujah: {
            name: 'Brujah',
            description: 'The passionate Brujah are rebels and idealists who fight against injustice. They combine raw physical power with intellectual fervor, often leading social movements and revolutions.'
        },
        nosferatu: {
            name: 'Nosferatu',
            description: 'The secretive Nosferatu are information brokers and spies. Their curse makes them hideous, but they compensate with incredible stealth abilities and vast networks of informants.'
        },
        malkavian: {
            name: 'Malkavian',
            description: 'The enigmatic Malkavians are touched by madness but gifted with prophetic insight. They see truths others cannot perceive, though their fractured minds make them unpredictable.'
        },
        toreador: {
            name: 'Toreador',
            description: 'The artistic Toreador are passionate about beauty, art, and culture. They are the most human-like vampires, often maintaining close ties to mortal society through their appreciation of aesthetics.'
        },
        gangrel: {
            name: 'Gangrel',
            description: 'The wild Gangrel are survivalists who embrace their animal nature. They are the most bestial vampires, often living on the fringes of society and developing close bonds with nature.'
        }
    };
    
    return clanData[clan] || clanData.ventrue;
}

/**
 * Retake questionnaire
 */
function retakeQuestionnaire() {
    // Clear session storage and start new session
    sessionStorage.removeItem('quizSessionId');
    sessionStorage.removeItem('clanScores');
    sessionStorage.removeItem('questionnaireAnswers');
    
    // Reset variables
    currentQuestion = 1;
    answers = {};
    clanScores = {
        ventrue: 0,
        tremere: 0,
        brujah: 0,
        nosferatu: 0,
        malkavian: 0,
        toreador: 0,
        gangrel: 0
    };
    
    // Reset form
    const form = document.getElementById('questionnaire-form');
    const resultsSection = document.getElementById('results-section');
    
    if (form) {
        form.style.display = 'block';
        form.reset();
    }
    
    if (resultsSection) resultsSection.style.display = 'none';
    
    // Show first question
    document.querySelectorAll('.question-section').forEach(section => {
        section.classList.remove('active');
    });
    
    const firstSection = document.querySelector('[data-question="1"]');
    if (firstSection) {
        firstSection.classList.add('active');
    }
    
    // Update display
    updateProgress();
    updateNavigationButtons();
    
    // Update debug panel if admin
    if (isAdmin) {
        updateDebugPanel();
    }
    
    console.log('Questionnaire reset - new session started');
}

/**
 * Create character (placeholder)
 */
function createCharacter() {
    console.log('Creating character...');
    // TODO: Redirect to character creation page with clan pre-selected
    alert('Character creation functionality will be implemented next!');
}

/**
 * Initialize debug panel for admin users
 */
function initializeDebugPanel() {
    const debugPanel = document.getElementById('admin-debug-panel');
    if (debugPanel) {
        debugPanel.style.display = 'block';
        debugPanelVisible = true;
        updateDebugPanel();
    }
}

/**
 * Toggle debug panel visibility
 */
function toggleDebugPanel() {
    const debugPanel = document.getElementById('admin-debug-panel');
    const toggleBtn = document.getElementById('debug-toggle');
    
    if (debugPanel && toggleBtn) {
        debugPanelVisible = !debugPanelVisible;
        debugPanel.style.display = debugPanelVisible ? 'block' : 'none';
        toggleBtn.textContent = debugPanelVisible ? 'Hide' : 'Show';
    }
}

/**
 * Update debug panel with current scores and answers
 */
function updateDebugPanel() {
    if (!isAdmin) return;
    
    const clanScoresDisplay = document.getElementById('clan-scores-display');
    const currentAnswersDisplay = document.getElementById('current-answers-display');
    
    if (clanScoresDisplay) {
        clanScoresDisplay.innerHTML = generateClanScoresHTML();
    }
    
    if (currentAnswersDisplay) {
        currentAnswersDisplay.innerHTML = generateAnswersHTML();
    }
}

/**
 * Generate HTML for clan scores display
 */
function generateClanScoresHTML() {
    let html = '';
    
    // Sort clans by score (highest first)
    const sortedClans = Object.keys(clanScores).sort((a, b) => clanScores[b] - clanScores[a]);
    
    sortedClans.forEach(clan => {
        const score = clanScores[clan];
        html += `
            <div class="clan-score-item">
                <span class="clan-name-debug">${clan}</span>
                <span class="clan-score-value">${score}</span>
            </div>
        `;
    });
    
    return html;
}

/**
 * Generate HTML for current answers display
 */
function generateAnswersHTML() {
    let html = '<div class="answers-section"><div class="answers-title">Current Answers:</div>';
    
    Object.keys(answers).forEach(question => {
        const answer = answers[question];
        let displayAnswer = '';
        
        if (Array.isArray(answer)) {
            displayAnswer = answer.join(', ');
        } else {
            displayAnswer = answer;
        }
        
        html += `
            <div class="answer-item">
                <strong>${question}:</strong> ${displayAnswer}
            </div>
        `;
    });
    
    html += '</div>';
    return html;
}
