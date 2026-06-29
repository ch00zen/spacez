<?php
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_order'])) {
        $clientId = (int)($_POST['client_id'] ?? 0);
        $livreurId = (int)($_POST['livreur_id'] ?? 0);
        $productIds = $_POST['line_product'] ?? [];
        $quantities = $_POST['line_qty'] ?? [];
        $lines = [];

        foreach ($productIds as $index => $productId) {
            $productId = (int)$productId;
            $qty = max(0, (int)($quantities[$index] ?? 0));
            if ($productId > 0 && $qty > 0) {
                $lines[] = ['id' => $productId, 'qty' => $qty];
            }
        }

        if ($clientId > 0 && $livreurId > 0 && $lines) {
            $total = 0.0;
            foreach ($lines as $line) {
                $product = $pdo->prepare('SELECT pu FROM Produit WHERE id = ?');
                $product->execute([$line['id']]);
                $productData = $product->fetch();
                if ($productData) {
                    $total += (float)$productData['pu'] * $line['qty'];
                }
            }

            $orderStmt = $pdo->prepare('INSERT INTO Commande (montant, statut, id_client, id_livreur) VALUES (?, ?, ?, ?)');
            $orderStmt->execute([$total, 'En cours', $clientId, $livreurId]);
            $orderId = (int)$pdo->lastInsertId();

            foreach ($lines as $line) {
                $product = $pdo->prepare('SELECT pu FROM Produit WHERE id = ?');
                $product->execute([$line['id']]);
                $productData = $product->fetch();
                if ($productData) {
                    $detailStmt = $pdo->prepare('INSERT INTO DetailsCommande (id_prod, id_commande, qte, montant) VALUES (?, ?, ?, ?)');
                    $detailStmt->execute([$line['id'], $orderId, $line['qty'], (float)$productData['pu'] * $line['qty']]);
                    $stockStmt = $pdo->prepare('UPDATE Produit SET qte = qte - ? WHERE id = ?');
                    $stockStmt->execute([$line['qty'], $line['id']]);
                }
            }

            $invoiceStmt = $pdo->prepare('INSERT INTO Facture (montant, id_commande) VALUES (?, ?)');
            $invoiceStmt->execute([$total, $orderId]);
            header('Location: commandes.php?message=' . urlencode('Commande créée et facture générée'));
            exit;
        }
    }

    if (isset($_POST['update_status'])) {
        $stmt = $pdo->prepare('UPDATE Commande SET statut = ? WHERE id = ?');
        $stmt->execute([trim($_POST['statut'] ?? ''), (int)($_POST['order_id'] ?? 0)]);
        header('Location: commandes.php?message=' . urlencode('Statut mis à jour'));
        exit;
    }

    if (isset($_POST['assign_livreur'])) {
        $stmt = $pdo->prepare('UPDATE Commande SET id_livreur = ? WHERE id = ?');
        $stmt->execute([(int)($_POST['livreur_id'] ?? 0), (int)($_POST['order_id'] ?? 0)]);
        header('Location: commandes.php?message=' . urlencode('Livreur assigné'));
        exit;
    }
}

$clients = $pdo->query('SELECT * FROM Client ORDER BY nom, prenom')->fetchAll();
$products = $pdo->query('SELECT * FROM Produit ORDER BY nom')->fetchAll();
$livreurs = $pdo->query('SELECT * FROM Livreur ORDER BY nom, prenom')->fetchAll();
$orders = $pdo->query('SELECT c.id, c.date_cmd, c.montant, c.statut, c.id_client, c.id_livreur, cl.nom AS client_nom, cl.prenom AS client_prenom, l.nom AS livreur_nom, l.prenom AS livreur_prenom FROM Commande c JOIN Client cl ON cl.id = c.id_client JOIN Livreur l ON l.id = c.id_livreur ORDER BY c.id DESC')->fetchAll();
?>
<section class="hero-panel">
    <div>
        <p class="eyebrow">Commandes</p>
        <h1>Suivez chaque commande de bout en bout.</h1>
        <p>Créez des commandes, assignez un livreur et gardez l’état visible en temps réel.</p>
    </div>
</section>

<section class="content-grid two-col">
    <div class="card">
        <h3>Nouvelle commande</h3>
        <form method="post" class="stack">
            <input type="hidden" name="create_order" value="1">
            <select name="client_id" required>
                <option value="">Sélectionner un client</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?= (int)$client['id'] ?>"><?= e($client['prenom'] . ' ' . $client['nom']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="livreur_id" required>
                <option value="">Sélectionner un livreur</option>
                <?php foreach ($livreurs as $livreur): ?>
                    <option value="<?= (int)$livreur['id'] ?>"><?= e($livreur['prenom'] . ' ' . $livreur['nom']) ?></option>
                <?php endforeach; ?>
            </select>
            <div id="lines">
                <div class="order-line">
                    <select name="line_product[]" required>
                        <option value="">Produit</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= (int)$product['id'] ?>"><?= e($product['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="line_qty[]" min="1" placeholder="Quantité" required>
                </div>
            </div>
            <button type="button" class="secondary-btn" id="add-line">Ajouter une ligne</button>
            <button type="submit">Créer la commande</button>
        </form>
    </div>
    <div class="card">
        <h3>Suivi des commandes</h3>
        <div class="list">
            <?php foreach ($orders as $order): ?>
                <div class="list-item stack-item">
                    <div>
                        <strong>Commande #<?= (int)$order['id'] ?></strong>
                        <span><?= e($order['client_prenom'] . ' ' . $order['client_nom']) ?> • <?= e($order['livreur_prenom'] . ' ' . $order['livreur_nom']) ?></span>
                    </div>
                    <div class="actions">
                        <form method="post" class="inline-form">
                            <input type="hidden" name="update_status" value="1">
                            <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                            <select name="statut">
                                <?php foreach (['En cours', 'Expédiée', 'Livrée', 'Annulée'] as $status): ?>
                                    <option value="<?= e($status) ?>" <?= $status === $order['statut'] ? 'selected' : '' ?>><?= e($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">MAJ</button>
                        </form>
                        <form method="post" class="inline-form">
                            <input type="hidden" name="assign_livreur" value="1">
                            <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                            <select name="livreur_id">
                                <?php foreach ($livreurs as $livreur): ?>
                                    <option value="<?= (int)$livreur['id'] ?>" <?= (int)$livreur['id'] === (int)$order['id_livreur'] ? 'selected' : '' ?>><?= e($livreur['prenom'] . ' ' . $livreur['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Assigner</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<script>
    document.getElementById('add-line')?.addEventListener('click', function() {
        const lines = document.getElementById('lines');
        const line = document.createElement('div');
        line.className = 'order-line';
        line.innerHTML = `
        <select name="line_product[]" required>
            <option value="">Produit</option>
            <?php foreach ($products as $product): ?>
                <option value="<?= (int)$product['id'] ?>"><?= e($product['nom']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="line_qty[]" min="1" placeholder="Quantité" required>
    `;
        lines.appendChild(line);
    });
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>