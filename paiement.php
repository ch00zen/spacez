<?php
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_payment'])) {
    $invoiceId = (int)($_POST['invoice_id'] ?? 0);
    $amount = (float)($_POST['payment_amount'] ?? 0);
    $type = trim($_POST['payment_type'] ?? '');
    if ($invoiceId > 0 && $amount > 0) {
        $stmt = $pdo->prepare('INSERT INTO Paiement (montant, type, id_facture) VALUES (?, ?, ?)');
        $stmt->execute([$amount, $type, $invoiceId]);
        header('Location: paiements.php?message=' . urlencode('Paiement enregistré'));
        exit;
    }
}

$invoices = $pdo->query('SELECT f.id, cl.nom, cl.prenom FROM Facture f JOIN Commande c ON c.id = f.id_commande JOIN Client cl ON cl.id = c.id_client ORDER BY f.id DESC')->fetchAll();
$payments = $pdo->query('SELECT p.id, p.date_paie, p.montant, p.type, p.id_facture FROM Paiement p ORDER BY p.id DESC')->fetchAll();
?>
<section class="hero-panel">
    <div>
        <p class="eyebrow">Paiements</p>
        <h1>Enregistrez chaque règlement avec précision.</h1>
        <p>Suivez les paiements associés à chaque facture.</p>
    </div>
</section>

<section class="content-grid two-col">
    <div class="card">
        <h3>Ajouter un paiement</h3>
        <form method="post" class="stack">
            <input type="hidden" name="add_payment" value="1">
            <select name="invoice_id" required>
                <option value="">Sélectionner une facture</option>
                <?php foreach ($invoices as $invoice): ?>
                    <option value="<?= (int)$invoice['id'] ?>">Facture #<?= (int)$invoice['id'] ?> – <?= e($invoice['prenom'] . ' ' . $invoice['nom']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" step="0.01" name="payment_amount" placeholder="Montant" required>
            <input type="text" name="payment_type" placeholder="Type de paiement" required>
            <button type="submit">Enregistrer</button>
        </form>
    </div>
    <div class="card">
        <h3>Historique des paiements</h3>
        <div class="list">
            <?php foreach ($payments as $payment): ?>
                <div class="list-item">
                    <div>
                        <strong><?= e($payment['type']) ?></strong>
                        <span><?= number_format((float)$payment['montant'], 2, ',', ' ') ?> XOF • Facture #<?= (int)$payment['id_facture'] ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>