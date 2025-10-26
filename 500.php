<?php
/**
 * Custom 500 Error Page - Valley by Night
 */
http_response_code(500);
include 'includes/header.php';
?>

<div class="error-page-container">
    <div class="error-content">
        <h1 class="error-title">500</h1>
        <h2 class="error-subtitle">The Darkness Consumes</h2>
        <p class="error-description">
            The ancient powers have been disrupted. Something has gone terribly wrong in the shadows.
            Our thralls are working to restore the balance, but for now, you must wait.
        </p>
        
        <div class="error-details">
            <p class="error-info">
                <strong>What happened?</strong><br>
                An internal server error has occurred. This has been logged for the Storyteller to investigate.
            </p>
        </div>
        
        <div class="error-actions">
            <a href="/" class="btn btn-primary">Return to the Chronicle</a>
            <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
            <a href="mailto:admin@websitetalkingheads.com" class="btn btn-secondary">Report This Error</a>
        </div>
        
        <div class="error-decorative">
            <p class="gothic-quote">"When the blood runs thin, chaos follows. We must restore the covenant."</p>
        </div>
    </div>
</div>

<style>
.error-page-container {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 60vh;
    padding: 2rem;
    background: linear-gradient(135deg, #1a0f0f 0%, #2d1b1b 50%, #1a0f0f 100%);
}

.error-content {
    text-align: center;
    max-width: 700px;
    color: #f5e6d3;
}

.error-title {
    font-family: 'IM Fell English', serif;
    font-size: 8rem;
    color: #8b0000;
    margin: 0;
    text-shadow: 0 0 20px rgba(139, 0, 0, 0.5), 0 0 40px rgba(139, 0, 0, 0.3);
    line-height: 1;
}

.error-subtitle {
    font-family: 'Libre Baskerville', serif;
    font-size: 2.5rem;
    color: #c9a882;
    margin: 1rem 0;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.error-description {
    font-family: 'Source Serif Pro', serif;
    font-size: 1.1rem;
    line-height: 1.8;
    color: #d4c4b8;
    margin: 2rem 0;
}

.error-details {
    background: rgba(139, 0, 0, 0.1);
    border: 2px solid rgba(139, 0, 0, 0.3);
    border-radius: 8px;
    padding: 1.5rem;
    margin: 2rem 0;
}

.error-info {
    font-family: 'Source Serif Pro', serif;
    font-size: 0.95rem;
    line-height: 1.6;
    color: #d4c4b8;
    margin: 0;
    text-align: left;
}

.error-info strong {
    color: #c9a882;
    font-family: 'Libre Baskerville', serif;
}

.error-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin: 3rem 0;
    flex-wrap: wrap;
}

.btn {
    display: inline-block;
    padding: 12px 30px;
    font-family: 'Libre Baskerville', serif;
    font-size: 1rem;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.btn-primary {
    background: linear-gradient(135deg, #780606 0%, #5a0202 100%);
    color: #f5e6d3;
    border: 2px solid #8b0000;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #8b0000 0%, #780606 100%);
    box-shadow: 0 0 20px rgba(139, 0, 0, 0.5);
    transform: translateY(-2px);
}

.btn-secondary {
    background: transparent;
    color: #c9a882;
    border: 2px solid #8b0000;
}

.btn-secondary:hover {
    background: rgba(139, 0, 0, 0.2);
    color: #f5e6d3;
    border-color: #8b0000;
}

.error-decorative {
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 1px solid rgba(139, 0, 0, 0.3);
}

.gothic-quote {
    font-family: 'Libre Baskerville', serif;
    font-style: italic;
    font-size: 0.95rem;
    color: #8b0000;
    margin: 0;
    text-shadow: 0 0 10px rgba(139, 0, 0, 0.3);
}

@media (max-width: 768px) {
    .error-title {
        font-size: 5rem;
    }
    
    .error-subtitle {
        font-size: 1.8rem;
    }
    
    .error-description {
        font-size: 1rem;
    }
    
    .btn {
        padding: 10px 20px;
        font-size: 0.9rem;
    }
    
    .error-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .error-actions .btn {
        width: 100%;
        max-width: 300px;
    }
    
    .error-details {
        padding: 1rem;
    }
    
    .error-info {
        font-size: 0.9rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
