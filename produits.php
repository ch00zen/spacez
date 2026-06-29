<?php
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $productId = (int)($_POST['product_id'] ?? 0);
    $name = trim($_POST['product_name'] ?? '');
    $price = (float)($_POST['product_price'] ?? 0);
    $stock = (int)($_POST['product_stock'] ?? 0);

    if ($productId > 0) {
        $stmt = $pdo->prepare('UPDATE Produit SET nom = ?, pu = ?, qte = ? WHERE id = ?');
        $stmt->execute([$name, $price, $stock, $productId]);
        header('Location: produits.php?message=' . urlencode('Produit mis à jour'));
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO Produit (nom, pu, qte) VALUES (?, ?, ?)');
    $stmt->execute([$name, $price, $stock]);
    header('Location: produits.php?message=' . urlencode('Produit ajouté'));
    exit;
}

$editProductId = (int)($_GET['edit_product'] ?? 0);
$editProduct = null;
if ($editProductId > 0) {
    $editStmt = $pdo->prepare('SELECT * FROM Produit WHERE id = ?');
    $editStmt->execute([$editProductId]);
    $editProduct = $editStmt->fetch();
}

$products = $pdo->query('SELECT * FROM Produit ORDER BY nom')->fetchAll();
?>
<section class="hero-panel">
    <div>
        <p class="eyebrow">Produits</p>
        <h1>Administrez votre catalogue avec une élégance premium.</h1>
        <p>Ajoutez, mettez à jour et suivez le stock de chaque produit.</p>
    </div>
</section>

<section class="content-grid two-col">
    <div class="card">
        <h3><?= $editProduct ? 'Modifier le produit' : 'Ajouter un produit' ?></h3>
        <form method="post" class="stack">
            <input type="hidden" name="save_product" value="1">
            <input type="hidden" name="product_id" value="<?= $editProduct ? (int)$editProduct['id'] : '' ?>">
            <input type="text" name="product_name" placeholder="Nom du produit" value="<?= $editProduct ? e($editProduct['nom']) : '' ?>" required>
            <input type="number" step="0.01" name="product_price" placeholder="Prix unitaire" value="<?= $editProduct ? e($editProduct['pu']) : '' ?>" required>
            <input type="number" name="product_stock" placeholder="Quantité en stock" value="<?= $editProduct ? e($editProduct['qte']) : '' ?>" required>
            <button type="submit"><?= $editProduct ? 'Mettre à jour' : 'Ajouter' ?></button>
        </form>
    </div>
    <div class="card">
        <h3>Catalogue</h3>
        <div class="list">
            <?php foreach ($products as $product): ?>
                <div class="list-item">
                    <div>
                        <strong><?= e($product['nom']) ?></strong>
                        <span><?= number_format((float)$product['pu'], 2, ',', ' ') ?> XOF • Stock : <?= (int)$product['qte'] ?></span>
                    </div>
                    <a href="produits.php?edit_product=<?= (int)$product['id'] ?>">Modifier</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>