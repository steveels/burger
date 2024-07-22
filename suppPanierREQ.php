<?php 
require 'bdd.php';
$db = Database::connect();

if(isset($_POST['id'])){
    $idPanier = htmlspecialchars($_POST['id']);

    $stmtPanier = $db->prepare("SELECT * FROM panier WHERE id = :id");
    $stmtPanier-> bindValue('id', $idPanier, PDO::PARAM_INT);
    $stmtPanier->execute();
    $panier = $stmtPanier->fetch(PDO::FETCH_ASSOC);

    if($panier){
        $qtePanier = $panier['qte'];
        $idProduit = $panier['produit_id'];

        // Update stock produit
        $stmtProduit = $db->prepare("UPDATE items SET qte = qte + ? WHERE id = ?");
        $stmtProduit->execute([$qtePanier, $idProduit]);

        // Supprimer l'entrée dans panier
        $stmtdelete = $db->prepare("DELETE FROM panier WHERE id = ?");
       if($stmtdelete->execute([$idPanier])){
        echo'success';
       }else{
        echo'error';
       }
    }else{
        echo'error';
       }
}else{
    echo'error';
   }