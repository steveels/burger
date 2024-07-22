<?php
require 'bdd.php';
session_start();
$db = Database::connect();
if (!isset($_SESSION['userTemp'])){
  $_SESSION['userTemp'] = time();
}

$userTemp = $_SESSION['userTemp'] ?? null;
$userId = $_SESSION['userId'] ?? null;
 
//1er methode pour afficher le nb de produits selectionné (par produits)
 /*if(!empty($userId)){
   $query = 'SELECT count(qte) as nbProduits
             FROM panier
             WHERE user_id = ?';
   $stmt = $db->prepare($query);
   $stmt->execute([$userId]);
   $nbProduits = $stmt->fetch(PDO::FETCH_ASSOC);
  
 }else{
    $query = 'SELECT count(qte) as nbProduits
              FROM panier 
              WHERE userTemp = ? AND user_id IS NULL';
    $stmt = $db->prepare($query);
    $stmt->execute([$userTemp]);
    $nbProduits = $stmt->fetch(PDO::FETCH_ASSOC);
 
}*/

//2eme methode pour afficher le nb de produits selectionné (par quantité)
if(!empty($userId)){
  $query = 'SELECT sum(qte) as nbProduits
            FROM panier
            WHERE user_id = ?';
  $stmt = $db->prepare($query);
  $stmt->execute([$userId]);
  $nbProduits = $stmt->fetch(PDO::FETCH_ASSOC);
 
}else{
   $query = 'SELECT sum(qte) as nbProduits
             FROM panier 
             WHERE userTemp = ? AND user_id IS NULL';
   $stmt = $db->prepare($query);
   $stmt->execute([$userTemp]);
   $nbProduits = $stmt->fetch(PDO::FETCH_ASSOC);

}




$categories = $db->query('SELECT * FROM categories')->fetchAll(PDO::FETCH_ASSOC);

if(isset($_SESSION['userId']))
{
    $id = htmlspecialchars($_SESSION['userId']);

    // Vérifier si l'utilisateur existe en bdd avec cet Id
    $stmt = $db -> prepare('SELECT * FROM utilisateurs WHERE id = :id');
    $stmt -> bindValue('id', $id, PDO::PARAM_INT);
    $stmt -> execute();

    $user = $stmt -> fetch(PDO::FETCH_ASSOC);
}


?>


<!DOCTYPE html>
<html>
    <head>
        <title>Burger Code</title>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <link href="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
        
        <link href='http://fonts.googleapis.com/css?family=Holtwood+One+SC' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
    <div class="container site">
        <div style="text-align:center; display:flex; justify-content:center; align-items:center" class="text-logo">
            <h1>Burger Doe</h1>
           
            <a href="panier.html" class="bi bi-basket3 cart-icon"> </a>
             <?php if(isset($_SESSION['userId']))
            {
              ?>
                <span class="text-hello">Bonjour <?= htmlspecialchars($user['nom']); ?></span>
                <?php
            }
            ?>
            
        </div>

        <!-- Navigation menu -->
        <nav>
            <ul class="nav nav-pills" role="tablist">
                <?php foreach ($categories as $cat) { ?>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?= htmlspecialchars($cat['id']) == 1 ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="<?= '#tab' . htmlspecialchars($cat['id']); ?>" role="tab">
                            <?= htmlspecialchars($cat['name']); ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
            <!-- Vérifie si l'utilisateur est connecté (isset($_SESSION['userId']))
            Vérifie si le rôle utilisateur est défini (isset($_SESSION['userRole']))
            Vérifie si le rôle est admin
            Si oui, affiche un élément de menu réservé aux admins -->
            <?php if (isset($_SESSION['userId']) && isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'admin') { ?>
              <ul class="navbar-nav ms-auto">
        
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <span>BackOffice</span>
                
              </a>
              
              <ul class="dropdown-menu">

                <li><a class="dropdown-item" href="admin/index.php">Update Product</a></li>
                <li><a class="dropdown-item" href="admin/insert.php">Commande</a></li>
                <li><a class="dropdown-item" href="deconnexion.php">Déconnexion</a></li>
              </ul>
            </li>
 
  
  <?php } ?>
            <ul class="navbar-nav ms-auto">
          <?php if (isset($_SESSION['userId'])) { ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle"></i>
              </a>
              
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="">Profil</a></li>
                <li><a class="dropdown-item" href="">Commande</a></li>
                <li><a class="dropdown-item" href="deconnexion.php">Déconnexion</a></li>
              </ul>
            </li>
          <?php } else { ?>
            <li class="nav-item">
              <a class="nav-link" href="inscription.php">Inscription / connexion</a>
            </li>
          <?php } ?>
          <li class="nav-item">
            <a class="nav-link" href="panier.php">
              <i class="bi bi-bag"></i>
              <span class='badge bg-primary'><?= $nbProduits ['nbProduits']; ?> </span>
            </a>
          </li>
        </ul>
        </nav>
        
        <!-- Items -->
        <div class="tab-content">
            <?php foreach ($categories as $cat) { ?>
                <div class="tab-pane <?= htmlspecialchars($cat['id']) == 1 ? 'active' : '' ?>" id="<?= 'tab' . htmlspecialchars($cat['id']); ?>" role="tabpanel">
                    <div class="row">
                        <?php
                        $query = 'SELECT * FROM items WHERE category = ?';
                        $stmt = $db->prepare($query);
                        $stmt->execute([$cat['id']]);
                        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($products as $product) { ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="img-thumbnail">
                                    <img src="images/<?= $product['image'] ?>" class="img-fluid" alt="<?= htmlspecialchars($product['name']); ?>">
                                    <div class="price"><?= filter_var($product['price'], FILTER_VALIDATE_FLOAT) . " €" ?></div>
                                    <div class="caption">
                                        <h4><?= htmlspecialchars($product['name']); ?></h4>
                                        <p><?= htmlspecialchars($product['description']); ?></p>
                                        <a href="addPanier.php?id=<?= htmlspecialchars($product['id']); ?>" class="btn btn-order" role="button"  ><span class="bi-cart-fill"></span> Commander</a>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php 
        Database::disconnect();
       ?>
        <script>

        </script>
</body>

</html>