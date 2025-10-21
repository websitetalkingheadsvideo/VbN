/**
 * Database-Driven Character Questionnaire JavaScript
 * Handles question navigation and clan scoring with cinematic category display
 */

// Global variables
let currentQuestion = 1;
let totalQuestions = questionsData.length;
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

// Check for admin mode
if (window.location.search.includes("admin=true")) {
    isAdmin = true;
    document.getElementById("admin-debug-panel").style.display = "block";
}

// Initialize the questionnaire
document.addEventListener("DOMContentLoaded", function() {
    updateProgress();
    updateNavigation();
    showCurrentCategory();
    
    // Add event listeners
    document.getElementById("next-btn").addEventListener("click", nextQuestion);
    document.getElementById("prev-btn").addEventListener("click", prevQuestion);
    document.getElementById("submit-btn").addEventListener("click", submitQuestionnaire);
    document.getElementById("retake-btn").addEventListener("click", retakeQuestionnaire);
    document.getElementById("create-character-btn").addEventListener("click", createCharacter);
    
    // Add radio button change listeners
    document.querySelectorAll("input[type=\"radio\"]").forEach(radio => {
        radio.addEventListener("change", handleAnswerChange);
    });
    
    // Debug panel toggle
    const debugToggle = document.getElementById("debug-toggle");
    if (debugToggle) {
        debugToggle.addEventListener("click", toggleDebugPanel);
    }
});

function showCurrentCategory() {
    const currentQuestionElement = document.querySelector(`[data-question="${currentQuestion}"]`);
    if (currentQuestionElement) {
        const category = currentQuestionElement.getAttribute("data-category");
        const categoryHeader = document.querySelector(`[data-category="${category}"]`);
        
        // Hide all category headers
        document.querySelectorAll(".category-header").forEach(header => {
            header.style.display = "none";
        });
        
        // Show current category header
        if (categoryHeader) {
            categoryHeader.style.display = "block";
        }
    }
}

function handleAnswerChange(event) {
    const questionNumber = parseInt(event.target.name.split("_")[1]);
    const answerValue = parseInt(event.target.value);
    const clanWeights = event.target.getAttribute("data-clan-weights");
    
    // Store the answer
    answers[questionNumber] = answerValue;
    
    // Update clan scores
    if (clanWeights) {
        updateClanScores(clanWeights);
    }
    
    // Update navigation
    updateNavigation();
    
    // Update debug panel if visible
    if (debugPanelVisible) {
        updateDebugPanel();
    }
}

function updateClanScores(clanWeights) {
    // Parse clan weights (format: "ventrue:3,tremere:2")
    const weights = clanWeights.split(",");
    weights.forEach(weight => {
        const [clan, points] = weight.split(":");
        if (clan && points && clanScores.hasOwnProperty(clan)) {
            clanScores[clan] += parseInt(points);
        }
    });
}

function nextQuestion() {
    if (currentQuestion < totalQuestions) {
        // Hide current question
        const currentElement = document.querySelector(`[data-question="${currentQuestion}"]`);
        if (currentElement) {
            currentElement.classList.remove("active");
        }
        
        currentQuestion++;
        
        // Show next question
        const nextElement = document.querySelector(`[data-question="${currentQuestion}"]`);
        if (nextElement) {
            nextElement.classList.add("active");
        }
        
        updateProgress();
        updateNavigation();
        showCurrentCategory();
        
        // Scroll to top of question
        nextElement.scrollIntoView({ behavior: "smooth", block: "start" });
    }
}

function prevQuestion() {
    if (currentQuestion > 1) {
        // Hide current question
        const currentElement = document.querySelector(`[data-question="${currentQuestion}"]`);
        if (currentElement) {
            currentElement.classList.remove("active");
        }
        
        currentQuestion--;
        
        // Show previous question
        const prevElement = document.querySelector(`[data-question="${currentQuestion}"]`);
        if (prevElement) {
            prevElement.classList.add("active");
        }
        
        updateProgress();
        updateNavigation();
        showCurrentCategory();
        
        // Scroll to top of question
        prevElement.scrollIntoView({ behavior: "smooth", block: "start" });
    }
}

function updateProgress() {
    const progressFill = document.getElementById("progress-fill");
    const currentQuestionSpan = document.getElementById("current-question");
    const totalQuestionsSpan = document.getElementById("total-questions");
    
    const progress = (currentQuestion / totalQuestions) * 100;
    progressFill.style.width = progress + "%";
    currentQuestionSpan.textContent = currentQuestion;
    totalQuestionsSpan.textContent = totalQuestions;
}

function updateNavigation() {
    const prevBtn = document.getElementById("prev-btn");
    const nextBtn = document.getElementById("next-btn");
    const submitBtn = document.getElementById("submit-btn");
    
    // Previous button
    prevBtn.disabled = currentQuestion === 1;
    
    // Next/Submit button
    const hasAnswer = answers.hasOwnProperty(currentQuestion);
    
    if (currentQuestion === totalQuestions) {
        nextBtn.style.display = "none";
        submitBtn.style.display = hasAnswer ? "inline-block" : "none";
    } else {
        nextBtn.style.display = "inline-block";
        submitBtn.style.display = "none";
        nextBtn.disabled = !hasAnswer;
    }
}

function submitQuestionnaire() {
    // Calculate final clan scores
    const sortedClans = Object.entries(clanScores)
        .sort(([,a], [,b]) => b - a);
    
    const topClan = sortedClans[0][0];
    const topScore = sortedClans[0][1];
    
    // Show results
    showResults(topClan, topScore, sortedClans);
}

function showResults(topClan, topScore, allScores) {
    // Hide questionnaire form
    document.querySelector(".questionnaire-form").style.display = "none";
    
    // Show results section
    const resultsSection = document.getElementById("results-section");
    resultsSection.style.display = "block";
    
    // Update clan information
    const clanInfo = {
        ventrue: {
            name: "Ventrue",
            description: "The Blue Bloods - Leaders, manipulators, and the aristocracy of vampire society.",
            logo: "images/clans/ventrue.png"
        },
        tremere: {
            name: "Tremere", 
            description: "The Warlocks - Mystical scholars who seek power through blood magic and ancient knowledge.",
            logo: "images/clans/tremere.png"
        },
        brujah: {
            name: "Brujah",
            description: "The Rabble - Passionate rebels who fight against injustice and authority.",
            logo: "images/clans/brujah.png"
        },
        nosferatu: {
            name: "Nosferatu",
            description: "The Sewer Rats - Information brokers who lurk in the shadows and know all secrets.",
            logo: "images/clans/nosferatu.png"
        },
        malkavian: {
            name: "Malkavian",
            description: "The Lunatics - Prophets and madmen who see truths others cannot comprehend.",
            logo: "images/clans/malkavian.png"
        },
        toreador: {
            name: "Toreador",
            description: "The Degenerates - Artists and hedonists who seek beauty and passion in all things.",
            logo: "images/clans/toreador.png"
        },
        gangrel: {
            name: "Gangrel",
            description: "The Outlanders - Wild survivors who embrace their animalistic nature.",
            logo: "images/clans/gangrel.png"
        }
    };
    
    const clan = clanInfo[topClan];
    document.getElementById("clan-name").textContent = clan.name;
    document.getElementById("clan-description").textContent = clan.description;
    document.getElementById("clan-logo").src = clan.logo;
    document.getElementById("clan-logo").alt = clan.name + " Logo";
    
    // Scroll to results
    resultsSection.scrollIntoView({ behavior: "smooth" });
    
    // Update debug panel with final scores
    if (debugPanelVisible) {
        updateDebugPanel();
    }
}

function retakeQuestionnaire() {
    // Reset everything
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
    
    // Hide results, show form
    document.getElementById("results-section").style.display = "none";
    document.querySelector(".questionnaire-form").style.display = "block";
    
    // Reset all radio buttons
    document.querySelectorAll("input[type=\"radio\"]").forEach(radio => {
        radio.checked = false;
    });
    
    // Show first question
    document.querySelectorAll(".question-section").forEach(section => {
        section.classList.remove("active");
    });
    document.querySelector(`[data-question="1"]`).classList.add("active");
    
    updateProgress();
    updateNavigation();
    showCurrentCategory();
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: "smooth" });
}

function createCharacter() {
    // Redirect to character creation with clan pre-selected
    const topClan = Object.entries(clanScores)
        .sort(([,a], [,b]) => b - a)[0][0];
    
    window.location.href = `lotn_char_create.php?clan=${topClan}`;
}

function toggleDebugPanel() {
    debugPanelVisible = !debugPanelVisible;
    const panel = document.getElementById("admin-debug-panel");
    const toggle = document.getElementById("debug-toggle");
    
    if (debugPanelVisible) {
        panel.style.display = "block";
        toggle.textContent = "Hide";
        updateDebugPanel();
    } else {
        panel.style.display = "none";
        toggle.textContent = "Show";
    }
}

function updateDebugPanel() {
    const scoresDisplay = document.getElementById("clan-scores-display");
    const answersDisplay = document.getElementById("current-answers-display");
    
    // Sort clans by score
    const sortedScores = Object.entries(clanScores)
        .sort(([,a], [,b]) => b - a);
    
    // Display clan scores
    scoresDisplay.innerHTML = "<h4>Clan Scores:</h4>";
    sortedScores.forEach(([clan, score]) => {
        const div = document.createElement("div");
        div.innerHTML = `<strong>${clan.charAt(0).toUpperCase() + clan.slice(1)}:</strong> ${score} points`;
        scoresDisplay.appendChild(div);
    });
    
    // Display current answers
    answersDisplay.innerHTML = "<h4>Current Answers:</h4>";
    Object.entries(answers).forEach(([question, answer]) => {
        const div = document.createElement("div");
        div.innerHTML = `Question ${question}: Answer ${answer}`;
        answersDisplay.appendChild(div);
    });
}
