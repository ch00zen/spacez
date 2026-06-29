<?php
require_once __DIR__ . '/includes/header.php';

$clients = $pdo->query('SELECT COUNT(*) AS total FROM Client')->fetchColumn();
$products = $pdo->query('SELECT COUNT(*) AS total FROM Produit')->fetchColumn();
$orders = $pdo->query('SELECT COUNT(*) AS total FROM Commande')->fetchColumn();
$payments = $pdo->query('SELECT COUNT(*) AS total FROM Paiement')->fetchColumn();
?>
<section class="hero-panel">
    <div>
        <p class="eyebrow">Tableau de bord</p>
        <h1>Une expérience commerciale élégante, fluide et premium.</h1>
        <p>Gérez vos clients, produits, commandes, factures et paiements depuis une interface moderne inspirée de l’univers Apple.</p>
    </div>
    <div class="hero-card">
        <div class="stats">
            <div><strong><?= (int)$clients ?></strong><span>Clients</span></div>
            <div><strong><?= (int)$products ?></strong><span>Produits</span></div>
            <div><strong><?= (int)$orders ?></strong><span>Commandes</span></div>
            <div><strong><?= (int)$payments ?></strong><span>Paiements</span></div>
        </div>
    </div>
</section>

<section class="content-grid three-col">
    <a class="card link-card" href="clients.php">
        <h3>Clients</h3>
        <p>Gérez vos clients et leur historique.</p>
    </a>
    <a class="card link-card" href="produits.php">
        <h3>Produits</h3>
        <p>Ajoutez, modifiez et suivez les stocks.</p>
    </a>
    <a class="card link-card" href="commandes.php">
        <h3>Commandes</h3>
        <p>Suivez les commandes et les livreurs assignés.</p>
    </a>
    <a class="card link-card" href="factures.php">
        <h3>Factures</h3>
        <p>Consultez les factures générées automatiquement.</p>
    </a>
    <a class="card link-card" href="paiements.php">
        <h3>Paiements</h3>
        <p>Enregistrez les paiements liés aux factures.</p>
    </a>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>