<?php

declare(strict_types=1);

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'commercial_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

function getDbConnection(): PDO
{
    global $host, $port, $dbname, $user, $pass;

    $serverDsn = "mysql:host={$host};port={$port};charset=utf8mb4";
    $pdo = new PDO($serverDsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}`");
    $pdo->exec("USE `{$dbname}`");

    return $pdo;
}

function ensureSchema(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS Client (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            prenom VARCHAR(100) NOT NULL,
            tel VARCHAR(30) NOT NULL,
            adresse VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS Produit (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(150) NOT NULL,
            pu DECIMAL(10,2) NOT NULL,
            qte INT NOT NULL DEFAULT 0
        ) ENGINE=InnoDB"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS Gerant (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            prenom VARCHAR(100) NOT NULL,
            tel VARCHAR(30) NOT NULL
        ) ENGINE=InnoDB"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS Livreur (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            prenom VARCHAR(100) NOT NULL,
            tel VARCHAR(30) NOT NULL,
            matricule_moto VARCHAR(50) NOT NULL
        ) ENGINE=InnoDB"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS Commande (
            id INT AUTO_INCREMENT PRIMARY KEY,
            date_cmd DATE NOT NULL DEFAULT (CURRENT_DATE),
            montant DECIMAL(10,2) NOT NULL DEFAULT 0,
            statut VARCHAR(50) NOT NULL DEFAULT 'En cours',
            id_client INT NOT NULL,
            id_livreur INT NOT NULL,
            CONSTRAINT fk_commande_client FOREIGN KEY (id_client) REFERENCES Client(id),
            CONSTRAINT fk_commande_livreur FOREIGN KEY (id_livreur) REFERENCES Livreur(id)
        ) ENGINE=InnoDB"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS DetailsCommande (
            id_prod INT NOT NULL,
            id_commande INT NOT NULL,
            qte INT NOT NULL,
            montant DECIMAL(10,2) NOT NULL,
            PRIMARY KEY (id_prod, id_commande),
            CONSTRAINT fk_details_produit FOREIGN KEY (id_prod) REFERENCES Produit(id),
            CONSTRAINT fk_details_commande FOREIGN KEY (id_commande) REFERENCES Commande(id) ON DELETE CASCADE
        ) ENGINE=InnoDB"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS Facture (
            id INT AUTO_INCREMENT PRIMARY KEY,
            date_fact DATE NOT NULL DEFAULT (CURRENT_DATE),
            montant DECIMAL(10,2) NOT NULL,
            id_commande INT NOT NULL,
            CONSTRAINT fk_facture_commande FOREIGN KEY (id_commande) REFERENCES Commande(id) ON DELETE CASCADE
        ) ENGINE=InnoDB"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS Paiement (
            id INT AUTO_INCREMENT PRIMARY KEY,
            date_paie DATE NOT NULL DEFAULT (CURRENT_DATE),
            montant DECIMAL(10,2) NOT NULL,
            type VARCHAR(50) NOT NULL,
            id_facture INT NOT NULL,
            CONSTRAINT fk_paiement_facture FOREIGN KEY (id_facture) REFERENCES Facture(id) ON DELETE CASCADE
        ) ENGINE=InnoDB"
    );

    $pdo->exec("INSERT IGNORE INTO Gerant (id, nom, prenom, tel) VALUES (1, 'Mouh', 'Ali', '0600000000')");
    $pdo->exec("INSERT IGNORE INTO Livreur (id, nom, prenom, tel, matricule_moto) VALUES (1, 'Diallo', 'Ibrahima', '0712345678', 'MOTO-101')");
    $pdo->exec("INSERT IGNORE INTO Livreur (id, nom, prenom, tel, matricule_moto) VALUES (2, 'Sow', 'Fatou', '0787654321', 'MOTO-202')");
    $pdo->exec("INSERT IGNORE INTO Client (id, nom, prenom, tel, adresse) VALUES (1, 'Kone', 'Aicha', '0765432109', 'Bamako')");
    $pdo->exec("INSERT IGNORE INTO Client (id, nom, prenom, tel, adresse) VALUES (2, 'Traore', 'Moussa', '0776543210', 'Sikasso')");
    $pdo->exec("INSERT IGNORE INTO Produit (id, nom, pu, qte) VALUES (1, 'Café Arabica', 2500.00, 120)");
    $pdo->exec("INSERT IGNORE INTO Produit (id, nom, pu, qte) VALUES (2, 'Thé Vert', 1800.00, 80)");
    $pdo->exec("INSERT IGNORE INTO Produit (id, nom, pu, qte) VALUES (3, 'Sucre', 1200.00, 200)");
}
