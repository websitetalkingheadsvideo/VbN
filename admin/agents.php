<?php
// /admin/agents.php
// Agents dashboard for Valley by Night
// Updated 2025-11-09: VbN Agents Page Styling + Link Integration
// NOTE: This page is read-only and uses a local array for now.
// Do NOT add CLI, Powershell, or remote DB discovery here.

$extra_css = ['css/admin-agents.css'];

$agents = [
    [
        "name" => "Character Agent",
        "slug" => "character_agent",
        "description" => "Monitors character JSON files to keep lore consistent, generate briefs, and suggest plot hooks.",
        "data_access" => [
            "/agents/character_agent/data/Characters/",
            "/agents/character_agent/data/History/",
            "/agents/character_agent/data/Plots/"
        ],
        "purpose" => "Detect new character sheets, validate fields, create character briefs, and flag continuity issues.",
        "status" => "Active",
        "last_event" => "Waiting for new character JSON...",
        "actions" => [
            [
                "label" => "View Reports",
                "url" => "/agents/character_agent/reports/"
            ],
            [
                "label" => "View Config",
                "url" => "/agents/character_agent/config/settings.json",
                "target" => "_blank",
                "rel" => "noopener"
            ]
        ]
    ],
    [
        "name" => "Laws Agent",
        "slug" => "laws_agent",
        "description" => "Ask canon questions across the MET library, surface rule citations, and validate mechanics before pushing updates.",
        "data_access" => [
            "/admin/laws_agent.php",
            "/agents/laws_agent/reference/"
        ],
        "purpose" => "Provide storytellers with lore, mechanics, and citation support on demand.",
        "status" => "Active",
        "last_event" => "Responded to Camarilla tradition query moments ago.",
        "actions" => [
            [
                "label" => "Launch Laws Agent",
                "url" => "/admin/laws_agent.php"
            ]
        ]
    ],
    // Future agents can be appended here.
];

include __DIR__ . '/../includes/header.php';
?>

<div class="admin-panel-container agents-panel container-fluid py-4 px-3 px-md-4">
    <div class="mb-4">
        <h1 class="display-5 text-light fw-bold mb-1">👥 Agents</h1>
        <p class="agents-intro lead fst-italic mb-0">Automated helpers that keep Valley by Night data fresh, consistent, and actionable.</p>
    </div>

    <div class="agents-grid row g-3 g-lg-4 mb-4">
        <?php foreach ($agents as $agent): ?>
            <div class="col-12 col-md-6 col-xl-4">
                <article class="agent-card h-100">
                    <div class="agent-card-header">
                        <h3 class="agent-card-title"><?= htmlspecialchars($agent['name']); ?></h3>
                        <span class="agent-status" data-status="<?= htmlspecialchars(strtolower($agent['status'])); ?>">
                            <?= htmlspecialchars($agent['status']); ?>
                        </span>
                    </div>
                    <p class="agent-card-description mb-0">
                        <?= htmlspecialchars($agent['description']); ?>
                    </p>
                    <div class="agent-card-section">
                        <p class="agent-card-subtitle mb-2">Purpose</p>
                        <p class="agent-card-text mb-0"><?= htmlspecialchars($agent['purpose']); ?></p>
                    </div>
                    <div class="agent-card-section">
                        <p class="agent-card-subtitle mb-2">Data Access</p>
                        <ul class="agent-path-list">
                            <?php foreach ($agent['data_access'] as $path): ?>
                                <li><span class="agent-path"><?= htmlspecialchars($path); ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <p class="agent-card-meta mb-0">Last event: <?= htmlspecialchars($agent['last_event']); ?></p>
                    <?php if (!empty($agent['actions'])): ?>
                        <div class="agent-action-group mt-3 d-flex flex-column flex-sm-row gap-2">
                            <?php foreach ($agent['actions'] as $action): ?>
                                <a href="<?= htmlspecialchars($action['url']); ?>"
                                   class="btn btn-outline-danger btn-sm w-100"
                                   <?php if (!empty($action['target'])): ?>target="<?= htmlspecialchars($action['target']); ?>"<?php endif; ?>
                                   <?php if (!empty($action['rel'])): ?>rel="<?= htmlspecialchars($action['rel']); ?>"<?php endif; ?>>
                                    <?= htmlspecialchars($action['label']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            </div>
        <?php endforeach; ?>
    </div>

    <section class="planned-agents-panel" id="planned-agents">
        <h2 class="mb-2">Planned Agents</h2>
        <p class="mb-3">Coming soon — additional automated agents to deepen chronicle support:</p>
        <ul class="planned-agents-list mb-0">
            <li>Boon Agent — monitors boons and favors, integrating with Harpy and Talons systems.</li>
            <li>Influence Agent — surfaces mortal influence opportunities across Bureaucracy, Law, Finance, and more.</li>
            <li>Rumor Agent — spins ambient rumors based on recent character or location changes.</li>
            <li>Lore/History Agent — answers city history questions and maintains a living timeline.</li>
        </ul>
    </section>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
