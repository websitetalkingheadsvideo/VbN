<?php
/**
 * Custom 404 Error Page - Valley by Night
 */
http_response_code(404);
include 'includes/header.php';
?>

<div class="error-page-container">
    <div class="error-content">
        <h1 class="error-title">404</h1>
        <h2 class="error-subtitle">Lost in the Shadows</h2>
        <p class="error-description">
            The page you seek has vanished into the darkness of the night.
            Perhaps it was never meant to be, or it has been claimed by forces beyond our understanding.
        </p>
        
        <div class="error-actions">
            <a href="/" class="btn btn-primary">Return to the Chronicle</a>
            <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
        </div>
        
        <div class="error-decorative">
            <p class="gothic-quote">"In the darkness, we find our way. But some paths lead to nothingness."</p>
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
    max-width: 600px;
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
}
</style>

<?php include 'includes/footer.php'; ?>
