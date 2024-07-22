<?php
session_start();
require 'bdd.php';
// opérateur de fusion, opérateur de coalescence 
// Méthode racourcie d'une structure ternaire 
// Si $_SESSION['userTemp'] existe alors $userTemp = $_SESSION['userTemp'] sinon $userTemp = null
$userTemp = $_SESSION['userTemp'] ?? null;
$userId = $_SESSION['userId'] ?? null;
$produitId = $_GET['id'] ?? null;
$qte = 1;

$db= Database::connect();


if($produitId && isset($userTemp)){
    $produitId = filter_var($produitId, FILTER_VALIDATE_INT);

    // Récupérer le produit sur lequel on a cliqué
    $stmt = $db->prepare('SELECT * FROM items WHERE id = ?');
    $stmt->execute([$produitId]);
    $produit = $stmt ->fetch(PDO::FETCH_ASSOC);

    if($produit && $produit['qte']>= $qte){

  
        //  Décrémenter la qte en stock 
        $newqteProd = $produit['qte'] - $qte;
        $upStmt = $db->prepare('UPDATE items SET qte = ? WHERE id = ?');
        $upStmt->execute([$newqteProd, $produitId]);

        // Ajouter le produit au panier ou mettre à jour la qte dans le panier existant 
        $query = 'SELECT * FROM panier WHERE produit_id = ? AND ((userTemp = ? AND user_id IS NULL) OR user_id = ?)';
        $stmt = $db->prepare($query);
        $stmt->execute([$produitId, $userTemp, $userId]);
        $panierProd = $stmt->fetch(PDO::FETCH_ASSOC);
    

        if($panierProd){
            // UPDATE la qte dans le panier
            $newQtePanier = $panierProd['qte'] + 1;
            $upStmt = $db->prepare('UPDATE panier SET qte = ? WHERE id = ?');
            $upStmt->execute([$newQtePanier, $panierProd['id']]);

            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }else{
        
            // INSERT du produit dans le panier 
            $dateAdd = (new DateTime())->format('Y-m-d H:i:s');
            $issertStmt = $db->prepare('INSERT INTO panier (produit_id, qte, userTemp, user_id, date) VALUES (?, ?, ?, ?, ?)');
            $issertStmt->execute([$produitId, $qte, $userTemp, $userId, $dateAdd]);

            header('Location: ' . $_SERVER['HTTP_REFERER']);

        }

    }

}else{
    header('Location: index.php');
}



Database::disconnect();