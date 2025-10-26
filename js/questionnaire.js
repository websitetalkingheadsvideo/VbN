/**
 * Questionnaire JavaScript - Valley by Night
 * Handles the random question questionnaire functionality
 */

// Global variables
let currentQuestion = 1;
const totalQuestions = 20;

// Clan tracking system
const clanTracking = {
    ventrue: 0,
    tremere: 0,
    brujah: 0,
    nosferatu: 0,
    malkavian: 0,
    toreador: 0,
    gangrel: 0
};

// Clan descriptions
const clanDescriptions = {
    ventrue: "The Blue Bloods, rulers and leaders who command respect through authority and tradition. You are drawn to power, leadership, and the responsibility that comes with ruling over others.",
    tremere: "The Warlocks, masters of blood magic and ancient secrets. You are scholarly, mysterious, and drawn to the arcane arts and hidden knowledge.",
    brujah: "The Rabble, passionate rebels who fight for their beliefs. You are driven by strong emotions, justice, and the desire to challenge the status quo.",
    nosferatu: "The Sewer Rats, masters of information and hidden in the shadows. You are resourceful, secretive, and understand that knowledge is the true power.",
    malkavian: "The Lunatics, touched by madness but gifted with insight. You see the world differently, often speaking in riddles but possessing deep wisdom.",
    toreador: "The Degenerates, artists and aesthetes who find beauty in all things. You are passionate about art, beauty, and the finer things in unlife.",
    gangrel: "The Outlanders, wild and untamed creatures of the night. You are independent, primal, and connected to the natural world and animal instincts."
};

// Popup state
let popupVisible = false;
let adminDebugVisible = false;
let isAdmin = false;

// Initialize questionnaire
document.addEventListener('DOMContentLoaded', function() {
    console.log('Random Questionnaire loaded with', questionsData.length, 'questions');
    checkAdminStatus();
    setupEventListeners();
    updateProgress();
    updateNavigationButtons();
    initializeTrackingPopup();
    
    // Animate first question category
    setTimeout(() => {
        const firstCategory = document.querySelector('.question-section.active .question-category');
        if (firstCategory) {
            firstCategory.classList.add('animate-in');
        }
    }, 300);
});

function setupEventListeners() {
    // Radio button changes
    const radioButtons = document.querySelectorAll('input[type="radio"]');
    radioButtons.forEach(radio => {
        radio.addEventListener('change', handleAnswerChange);
    });
    
    // Next button
    const nextBtn = document.getElementById('next-btn');
    if (nextBtn) {
        nextBtn.addEventListener('click', nextQuestion);
    }
    
    // Submit button
    const submitBtn = document.getElementById('submit-btn');
    if (submitBtn) {
        submitBtn.addEventListener('click', submitQuestionnaire);
    }
    
    // Tracking popup controls
    const trackingToggle = document.getElementById('tracking-toggle');
    const trackingClose = document.getElementById('tracking-close');
    
    if (trackingToggle) {
        trackingToggle.addEventListener('click', toggleTrackingPopup);
    }
    
    if (trackingClose) {
        trackingClose.addEventListener('click', hideTrackingPopup);
    }
    
    // Admin debug popup controls
    const adminDebugToggle = document.getElementById('admin-debug-toggle');
    const adminDebugClose = document.getElementById('admin-debug-close');
    
    if (adminDebugToggle) {
        adminDebugToggle.addEventListener('click', toggleAdminDebugPopup);
    }
    
    if (adminDebugClose) {
        adminDebugClose.addEventListener('click', hideAdminDebugPopup);
    }
}

function handleAnswerChange(event) {
    console.log('Answer selected:', event.target.value);
    
    // Enable next button (scoring will happen when Next is clicked)
    const nextBtn = document.getElementById('next-btn');
    if (nextBtn) {
        nextBtn.disabled = false;
    }
}

function nextQuestion() {
    // Get the selected answer for current question and update clan tracking
    const currentSection = document.querySelector(`[data-question="${currentQuestion}"]`);
    const selectedAnswer = currentSection.querySelector('input[type="radio"]:checked');
    
    if (selectedAnswer) {
        const questionNumber = parseInt(selectedAnswer.name.split('_')[1]);
        const answerValue = parseInt(selectedAnswer.value);
        updateClanTracking(questionNumber, answerValue);
        
        // Update admin debug if visible
        if (isAdmin && adminDebugVisible) {
            updateAdminDebugDisplay();
        }
    }
    
    // Fade out current question
    if (currentSection) {
        currentSection.classList.add('fade-out');
        
        // Wait for fade out, then move to next question
        setTimeout(() => {
            currentSection.classList.remove('active', 'fade-out');
            
            // Move to next question
            currentQuestion++;
            
            if (currentQuestion <= totalQuestions) {
                const nextSection = document.querySelector(`[data-question="${currentQuestion}"]`);
                if (nextSection) {
                    nextSection.classList.add('active');
                    
                    // Animate category entry with delay
                    const categoryElement = nextSection.querySelector('.question-category');
                    if (categoryElement) {
                        setTimeout(() => {
                            categoryElement.classList.add('animate-in');
                        }, 200); // Small delay for cinematic effect
                    }
                    
                    updateProgress();
                    updateNavigationButtons();
                }
            } else {
                console.log('Questionnaire complete!');
                // TODO: Handle completion
            }
        }, 500); // Wait for fade transition
    }
}

function submitQuestionnaire(event) {
    event.preventDefault();
    console.log('Questionnaire submitted!');
    
    // Fade out the questionnaire form
    const questionnaireForm = document.getElementById('questionnaire-form');
    const progressSection = document.querySelector('.progress-section');
    
    if (questionnaireForm) {
        questionnaireForm.style.transition = 'opacity 0.8s ease-in-out';
        questionnaireForm.style.opacity = '0';
        
        // After fade out, hide form and show results
        setTimeout(() => {
            questionnaireForm.style.display = 'none';
            if (progressSection) progressSection.style.display = 'none';
            
            // Calculate and display results
            showResults();
        }, 800);
    }
}

function updateProgress() {
    const progressFill = document.getElementById('progress-fill');
    const currentQuestionSpan = document.getElementById('current-question');
    
    if (progressFill) {
        const progressPercent = (currentQuestion / totalQuestions) * 100;
        progressFill.style.width = `${progressPercent}%`;
    }
    
    if (currentQuestionSpan) {
        currentQuestionSpan.textContent = currentQuestion;
    }
}

function updateNavigationButtons() {
    const nextBtn = document.getElementById('next-btn');
    const submitBtn = document.getElementById('submit-btn');
    
    if (currentQuestion === totalQuestions) {
        if (nextBtn) nextBtn.style.display = 'none';
        if (submitBtn) submitBtn.style.display = 'inline-block';
    } else {
        if (nextBtn) nextBtn.style.display = 'inline-block';
        if (submitBtn) submitBtn.style.display = 'none';
    }
    
    // Disable next button until answer is selected
    if (nextBtn) {
        nextBtn.disabled = true;
    }
}

// Clan tracking functions
function updateClanTracking(questionNumber, answerValue) {
    // Find the question data
    const questionData = questionsData[questionNumber - 1];
    if (!questionData) {
        console.log('No question data found for question', questionNumber);
        return;
    }
    
    // Get clan weights for this answer (database fields are clanWeight1, clanWeight2, etc.)
    const clanWeightsField = `clanWeight${answerValue}`;
    const clanWeights = questionData[clanWeightsField];
    
    console.log('Looking for field:', clanWeightsField);
    console.log('Question data:', questionData);
    console.log('Clan weights found:', clanWeights);
    
    if (!clanWeights) {
        console.log('No clan weights found for field:', clanWeightsField);
        return;
    }
    
    try {
        // Parse clan weights (comma-separated format: "tremere:3,nosferatu:2,malkavian:2")
        const weights = {};
        const weightPairs = clanWeights.split(',');
        
        weightPairs.forEach(pair => {
            const [clan, points] = pair.split(':');
            if (clan && points) {
                weights[clan.trim()] = parseInt(points.trim());
            }
        });
        
        console.log('Parsed weights:', weights);
        
        // Update clan tracking
        Object.keys(weights).forEach(clan => {
            if (clanTracking.hasOwnProperty(clan)) {
                clanTracking[clan] += weights[clan];
            }
        });
        
        console.log('Clan tracking updated:', clanTracking);
        updateTrackingDisplay();
        
    } catch (e) {
        console.log('Error parsing clan weights:', e);
        console.log('Raw clan weights:', clanWeights);
    }
}

function updateTrackingDisplay() {
    const trackingContent = document.getElementById('tracking-content');
    if (!trackingContent) {
        console.log('tracking-content element not found');
        return;
    }
    
    let html = '';
    Object.keys(clanTracking).forEach(clan => {
        html += `
            <div class="clan-score">
                <span class="clan-name">${clan}</span>
                <span class="clan-points">${clanTracking[clan]}</span>
            </div>
        `;
    });
    
    trackingContent.innerHTML = html;
    console.log('Tracking display updated with:', html);
}

function initializeTrackingPopup() {
    updateTrackingDisplay();
}

function toggleTrackingPopup() {
    const popup = document.getElementById('tracking-popup');
    const toggle = document.getElementById('tracking-toggle');
    
    console.log('toggleTrackingPopup called');
    console.log('popup element:', popup);
    console.log('toggle element:', toggle);
    
    if (!popup || !toggle) {
        console.log('Missing popup or toggle element');
        return;
    }
    
    popupVisible = !popupVisible;
    console.log('popupVisible is now:', popupVisible);
    
    if (popupVisible) {
        popup.style.display = 'block';
        toggle.textContent = 'Hide Clan Scores';
        console.log('Showing popup');
    } else {
        popup.style.display = 'none';
        toggle.textContent = 'Show Clan Scores';
        console.log('Hiding popup');
    }
}

function hideTrackingPopup() {
    const popup = document.getElementById('tracking-popup');
    const toggle = document.getElementById('tracking-toggle');
    
    if (!popup || !toggle) return;
    
    popupVisible = false;
    popup.style.display = 'none';
    toggle.textContent = 'Show Clan Scores';
}

// Admin debug functions
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
    
    // Show admin debug button if admin
    if (isAdmin) {
        const adminDebugToggle = document.getElementById('admin-debug-toggle');
        if (adminDebugToggle) {
            adminDebugToggle.style.display = 'block';
        }
    }
    
    console.log('Admin status:', isAdmin);
}

function updateAdminDebugDisplay() {
    const debugContent = document.getElementById('admin-debug-content');
    if (!debugContent) return;
    
    const debugInfo = {
        currentQuestion: currentQuestion,
        totalQuestions: totalQuestions,
        clanTracking: clanTracking,
        questionsData: questionsData,
        timestamp: new Date().toLocaleTimeString()
    };
    
    debugContent.innerHTML = `
        <div><strong>Current Question:</strong> ${currentQuestion}/${totalQuestions}</div>
        <div><strong>Timestamp:</strong> ${debugInfo.timestamp}</div>
        <div><strong>Clan Tracking Object:</strong></div>
        <div class="admin-debug-json">${JSON.stringify(clanTracking, null, 2)}</div>
        <div><strong>Questions Data (First Question):</strong></div>
        <div class="admin-debug-json">${JSON.stringify(questionsData[0] || {}, null, 2)}</div>
    `;
}

function toggleAdminDebugPopup() {
    const popup = document.getElementById('admin-debug-popup');
    const toggle = document.getElementById('admin-debug-toggle');
    
    if (!popup || !toggle) return;
    
    adminDebugVisible = !adminDebugVisible;
    
    if (adminDebugVisible) {
        popup.style.display = 'block';
        toggle.textContent = 'Hide Debug';
        updateAdminDebugDisplay();
    } else {
        popup.style.display = 'none';
        toggle.textContent = 'Admin Debug';
    }
}

function hideAdminDebugPopup() {
    const popup = document.getElementById('admin-debug-popup');
    const toggle = document.getElementById('admin-debug-toggle');
    
    if (!popup || !toggle) return;
    
    adminDebugVisible = false;
    popup.style.display = 'none';
    toggle.textContent = 'Admin Debug';
}

// Results functions
function showResults() {
    const resultsSection = document.getElementById('results-section');
    if (!resultsSection) return;
    
    // Hide questionnaire header
    const questionnaireHeader = document.querySelector('.questionnaire-header');
    if (questionnaireHeader) {
        questionnaireHeader.style.display = 'none';
    }
    
    // Determine winning clan
    const winningClan = determineWinningClan();
    console.log('Winning clan:', winningClan);
    
    // Display results
    displayClanResult(winningClan);
    displayAllClanScores();
    
    // Show results section with fade in
    resultsSection.style.display = 'block';
    setTimeout(() => {
        resultsSection.classList.add('active');
    }, 100);
    
    // Setup results action buttons
    setupResultsActions();
}

function determineWinningClan() {
    let maxScore = -1;
    let winningClan = 'ventrue'; // default fallback
    
    Object.keys(clanTracking).forEach(clan => {
        if (clanTracking[clan] > maxScore) {
            maxScore = clanTracking[clan];
            winningClan = clan;
        }
    });
    
    return winningClan;
}

function displayClanResult(clan) {
    const clanLogo = document.getElementById('clan-logo');
    const clanName = document.getElementById('clan-name');
    const clanDescription = document.getElementById('clan-description');
    
    if (clanLogo) {
        clanLogo.src = `images/Clan Logos/LogoClan${capitalizeFirst(clan)}.webp`;
        clanLogo.alt = `${capitalizeFirst(clan)} Clan Logo`;
    }
    
    if (clanName) {
        clanName.textContent = capitalizeFirst(clan);
    }
    
    if (clanDescription) {
        clanDescription.textContent = clanDescriptions[clan] || 'A mysterious clan with ancient secrets.';
    }
}

function displayAllClanScores() {
    const allClanScores = document.getElementById('all-clan-scores');
    if (!allClanScores) return;
    
    // Sort clans by score (highest first)
    const sortedClans = Object.keys(clanTracking).sort((a, b) => clanTracking[b] - clanTracking[a]);
    
    let html = '';
    sortedClans.forEach(clan => {
        const score = clanTracking[clan];
        html += `
            <div class="clan-score">
                <span class="clan-name">${capitalizeFirst(clan)}</span>
                <span class="clan-points">${score}</span>
            </div>
        `;
    });
    
    allClanScores.innerHTML = html;
}

function setupResultsActions() {
    const retakeBtn = document.getElementById('retake-btn');
    const createCharacterBtn = document.getElementById('create-character-btn');
    
    if (retakeBtn) {
        retakeBtn.addEventListener('click', retakeQuestionnaire);
    }
    
    if (createCharacterBtn) {
        createCharacterBtn.addEventListener('click', createCharacter);
    }
}

function retakeQuestionnaire() {
    // Reset everything and reload the page
    window.location.reload();
}

function createCharacter() {
    // Redirect to character creation with clan data
    const winningClan = determineWinningClan();
    const clanData = encodeURIComponent(JSON.stringify({
        clan: winningClan,
        scores: clanTracking
    }));
    
    // For now, just redirect to character sheet
    // In the future, this could pass the clan data
    window.location.href = 'character_sheet.php';
}

function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function showTestClanResults(clan) {
    // Set the specified clan as the winning clan
    clanTracking[clan] = 100; // High score to ensure this clan wins
    
    // Hide questionnaire header and form
    const questionnaireHeader = document.querySelector('.questionnaire-header');
    if (questionnaireHeader) {
        questionnaireHeader.style.display = 'none';
    }
    
    const questionnaireForm = document.getElementById('questionnaire-form');
    if (questionnaireForm) {
        questionnaireForm.style.display = 'none';
    }
    
    // Show results section
    const resultsSection = document.getElementById('results-section');
    if (resultsSection) {
        resultsSection.style.display = 'block';
        resultsSection.classList.add('active');
        
        // Set clan data
        const clanLogo = document.getElementById('clan-logo');
        const clanName = document.getElementById('clan-name');
        const clanDescription = document.getElementById('clan-description');
        const allClanScores = document.getElementById('all-clan-scores');
        
        if (clanLogo) {
            clanLogo.src = `images/Clan Logos/LogoClan${capitalizeFirst(clan)}.webp`;
            clanLogo.alt = `${capitalizeFirst(clan)} Clan Logo`;
        }
        
        if (clanName) {
            clanName.textContent = capitalizeFirst(clan);
        }
        
        if (clanDescription) {
            clanDescription.textContent = clanDescriptions[clan];
        }
        
        if (allClanScores) {
            allClanScores.innerHTML = generateAllClanScores();
        }
    }
}

function showBrujahResults() {
    showTestClanResults('brujah');
}
