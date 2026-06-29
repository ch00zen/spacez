<?php
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_client'])) {
    $stmt = $pdo->prepare('INSERT INTO Client (nom, prenom, tel, adresse) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        trim($_POST['nom'] ?? ''),
        trim($_POST['prenom'] ?? ''),
        trim($_POST['tel'] ?? ''),
        trim($_POST['adresse'] ?? ''),
    ]);
    header('Location: clients.php?message=' . urlencode('Client ajouté avec succès'));
    exit;
}

$clients = $pdo->query('SELECT * FROM Client ORDER BY nom, prenom')->fetchAll();
?>
<section class="hero-panel">
    <div>
        <p class="eyebrow">Clients</p>
        <h1>Construisez une relation durable avec chaque client.</h1>
        <p>Centralisez les informations de vos prospects et clients en quelques clics.</p>
    </div>
</section>

<section class="content-grid two-col">
    <div class="card">
        <h3>Ajouter un client</h3>
        <form method="post" class="stack">
            <input type="hidden" name="add_client" value="1">
            <input type="text" name="nom" placeholder="Nom" required>
            <input type="text" name="prenom" placeholder="Prénom" required>
            <input type="tel" name="tel" placeholder="Téléphone" required>
            <input type="text" name="adresse" placeholder="Adresse" required>
            <button type="submit">Enregistrer</button>
        </form>
    </div>
    <div class="card">
        <h3>Liste des clients</h3>
        <div class="list">
            <?php foreach ($clients as $client): ?>
                <div class="list-item">
                    <div>
                        <strong><?= e($client['prenom'] . ' ' . $client['nom']) ?></strong>
                        <span><?= e($client['tel']) ?> • <?= e($client['adresse']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>