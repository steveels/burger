<?php
require 'bdd.php';
$db = Database::connect();

if(isset($_POST['id']) && isset($_POST['qte'])){
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $qte = filter_var($_POST['qte'], FILTER_VALIDATE_INT);

    // On vérifie que la quantité est un entier et supérieur à 0
    if (filter_var($qte, FILTER_VALIDATE_INT) && $qte > 0){

        // récupère la ligne du panier sur laquelle on veut agir
        $stmt = $db->prepare('SELECT * FROM panier WHERE id = ?');
        $stmt->execute([$id]);
        $panier = $stmt->fetch(PDO::FETCH_ASSOC);
        // On vérifie que la ligne existe
        if($panier){
            $qteActuelle = $panier['qte'];
            $produitId = $panier['produit_id'];

            //  Récupère le produit correspondant à la ligne du panier
            $stmt = $db->prepare('SELECT * FROM items WHERE id = ?');
            $stmt->execute([$produitId]);
            $produit = $stmt->fetch(PDO::FETCH_ASSOC);

            // on calcule la différence entre la quantité mis à jour et la quantité stocké dans le panier 
            $diff = $qte - $panier['qte'];

            // On vérifie que la quantité en stock est suffisante
             if($produit && $produit['qte'] >= $diff){
             

            // On met à jour la quantité dans le panier
                $stmt = $db->prepare('UPDATE panier SET qte = ? WHERE id = ?');
                $successUpPanier = $stmt->execute([$qte, $id]);
            // On vérifie si la qte a bien été mise à jour dans le panier
                if($successUpPanier){
            // On met à jour la quantité en stock du produit
                    $stmtProd = $db->prepare('UPDATE items SET qte = qte - ? WHERE id = ?');
                    $successUpProd = $stmtProd->execute([$diff, $panier['produit_id']]);
            // SI la qte en stock a bien été mise à jouron renvoie success
                    if($successUpProd){
                        echo 'success';
                    } 
                    else {
                        echo 'error';
                    }

            }
            else {
                echo 'error';
            }
        }
        else {
            echo 'error';
        }
    }
    else {
        echo 'error';
    }
 
}
else {
    echo 'error';
}
}
else {
    echo 'error';
}

Database::disconnect();