<?php
require 'bdd.php';
session_start();
 $db = Database::connect();
 $userTemp = $_SESSION['userTemp'] ?? null;
 $userId = $_SESSION['userId'] ?? null;



 if(!empty($userId)){
   $query = 'SELECT panier.*, items.name, items.price , items.image
             FROM panier
             INNER JOIN  items ON panier.produit_id = items.id
             WHERE user_id = ?';
   $stmt = $db->prepare($query);
   $stmt->execute([$userId]);
   $panier = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
 }else{
    $query = 'SELECT pa.*, p.name, p.price, p.image
              FROM panier pa
              INNER JOIN  items p ON pa.produit_id = p.id 
              WHERE userTemp = ? AND user_id IS NULL';
    $stmt = $db->prepare($query);
    $stmt->execute([$userTemp]);
    $panier = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
}
$totalPanier=0 ;
$montantTVA=0;
$montantTTC=0;
$montantReduction=0;
$reduction =  0;
$coupon = 0;
$reduc2 = $_SESSION['reduction'] ?? 0;
       

 if(!empty($_POST['code'])){
    $codeCoupon = htmlspecialchars($_POST['code']);
    $query = 'SELECT * FROM coupons WHERE code = ?';
    $reqCode = $db->prepare($query);
    $reqCode->execute([$codeCoupon]);
    $coupon = $reqCode->fetch(PDO::FETCH_ASSOC);
  
    if($coupon){
       
        // coupon valide

        $dateActuelle = date('Y-m-d H:i:s');
        if ($dateActuelle >= $coupon['debut'] && $dateActuelle <= $coupon['fin']) {
        $reduction = $coupon['remise'];
        $_SESSION['coupon'] = $coupon;
        $_SESSION['reduction'] = $reduction;
        $reduc2 = $reduction;
    }
}
else{
    // coupon invalid
    $msg = " Attention : le code remise saisi est incorrect !";
    header('Location: panier.php?msg='.$msg);
    
   


}

} 
foreach ($panier as $item) {
            $totalPanier += $item['price'] * $item['qte']; 
            $montantTVA = $totalPanier * 0.2; 
            $montantTTC = $totalPanier + $montantTVA; 
            $montantReduction = $totalPanier * $reduc2 / 100;
            $montantTTC -= $montantReduction; 
        
}


 
Database::disconnect();


?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
        <link rel="stylesheet" href="styles.css">

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <link href="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
        <link href='http://fonts.googleapis.com/css?family=Holtwood+One+SC' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    </head>

    <body>
        <div class="cart ">
           <?php if(empty($panier)) { ?>
            <div class="alert alert-danger" role="alert" style="text-align:center;">
                Votre panier est vide !
            </div>
            <?php } ?>
            
        
            <div class="cart-container">
                <div class="row justify-content-between">
                    <div class="col-12">
                        <div class="">
                            <div class="">
                                <table class="table table-bordered mb-30">
                                    <thead>
                                        <tr>
                                            <th scope="col"></th>
                                            <th scope="col">Image</th>
                                            <th scope="col">Produit</th>
                                            <th scope="col">Prix unitaire</th>
                                            <th scope="col">Quantité</th>
                                            <th scope="col">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach($panier as $item): ?>
                                        <tr>
                                            <th scope="row" class="btn-delete" data-id="<?= $item['id']; ?>">
                                                <a href="">
                                                    <i class="bi bi-archive"></i>
                                                </a>
                                            </th>
                                            <td >
                                                <img src="images/<?= $item['image']; ?>" alt="<?= htmlspecialchars($item['name']); ?>" style="width:100px">
                                             
                                            </td>
                                            <td ><?= htmlspecialchars($item['name']); ?>
                                                <a href=""></a><br>
                                               
                                            </td>
                                            <td class="prix-unitaire"><?= htmlspecialchars($item['price']); ?></td>
                                            <td>
                                                <div class="quantity"
                                                    style="display:flex; justify-content:center; align-items:center">

                                                    <button class='btn btn-secondary btn-sm changeQte' data-id="<?= $item['id']; ?>" data-action="decrease">-</button>
                                                     <span><?= htmlspecialchars($item['qte']); ?></span>
                                                     <button class='btn btn-secondary btn-sm changeQte' data-id="<?= $item['id']; ?>" data-action="increase">+</button>
                                                </div>
                                            </td>
                                            <td class="sous-total"><?= $item['price'] * $item['qte']; ?> €</td>
                                        </tr>
                                        
                                      
                                        
                                         <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Coupon -->
                    <div class="col-12 col-lg-6">
                        <div class=" mb-30">
                            <h6>Avez vous un coupon?</h6>
                            <p>Entrer le code de la remise</p>
                            <?php if(isset($_GET['msg'])) { ?>
                            <div class="alert alert-danger" role="alert">
                            <?= $_GET['msg']?>
                            </div>
                            <?php } ?>

                            <div class="alert alert-primary" role="alert">
                                Vous avez ajouté un code de réduction !
                            </div>
                        <!-- Coupon -->
                            <div class="coupon-form">
                                <form action="panier.php" method="POST">
                                    <input type="text" class="form-control" name="code" placeholder="Entrer le code" >
                                    <button type="submit" class="btn btn-primary"
                                        style="margin-top:20px">Valider</button>
                                </form>
                            </div>
                            <br>

                            <!-- Coupon -->


                        </div>
                    </div>



                    <div class="col-12 col-lg-5">
                        <div class=" mb-30">
                            <h5 class="mb-3">Total panier</h5>
                            <div class="">
                                <table class="table mb-3">
                              
                                
                                    <tbody>
                                    
                                        <tr>
                                            <td>Total produit HT</td>
                                            <td id='HT' class='total-panier'> <?= $totalPanier; ?>€</td>
                                        </tr>
                                        <tr>
                                            <td>TVA</td>
                                            <td id="TVA"  ><?= $montantTVA; ?> €</td>
                                        </tr>
                                        <tr>
                                            <td>Remise</td> 
                                            
                                            <td id='remise'><?= $montantReduction ; ?>€ </td>
                                        </tr>

                                        <tr>
                                            <td>TOTAL TTC</td>
                                            
                                            <td id='TTC'> <?= $montantTTC; ?>€</td>
                                        </tr>
                                 
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
                <a class="btn btn-primary" href="index.php"><span class="bi-arrow-left"></span> Retour</a>
            </div>
        </div>
    </body>

</html>
<script>

  
    // Mettre à jour la quantité
    // On récupère tous les boutons de changement de quantité
    document.querySelectorAll('.changeQte').forEach(function(btn){
        //  On crée un écouteur d'événement sur chaque bouton
   
        btn.addEventListener('click', function(e){
            const action = this.dataset.action 
            const id = this.dataset.id
            // On récupère le tr sur lequel se trouve le span
            let row = this.closest('tr')
            let qteEle = row.querySelector('span')
            let sousTotal = row.querySelector('.sous-total')
            let prixUnitaire = parseFloat(row.querySelector('.prix-unitaire').textContent )//textContent récupère le contenu textuel ou chiffre de l'élément//
            let totalPanier = 0
            let tva=0.2
            let remise = parseFloat(document.querySelector('#remise').textContent); 

            

        
           
        
            
            // On récupère la qte 
            let newQte = parseInt(qteEle.textContent)
            
            

            if(action === 'increase'){
                newQte++
            }
            if(action === 'decrease' && newQte > 1){
                newQte--
            }

            // qteEle.textContent = newQte

            // On créer unerequête asynchrone pour mettre à jour la qte en bdd
            fetch('upQteREQ.php', {
                // method : comment on envoie les données
                method: 'POST',
                // headers  sous quelle forme on envoie les données
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                // Les données qu'on envoie
                body: `id=${id}&qte=${newQte}`
           
            })

            // Réagir à la réponse
            .then(response => response.text())
            .then(data => {
                    if(data.trim() === 'success'){
                        qteEle.textContent = newQte
                           //Mettre à jour le sous total
                           sousTotal.textContent = ( prixUnitaire * newQte ).toFixed(2) + '€'
                          

                          


                        //Mettre à jour le total du panier
                        document.querySelectorAll('.sous-total').forEach(function(total){
                          totalPanier += parseFloat(total.textContent)

                        })
                        
                    
                     
                        
                            
                        document.querySelector('.total-panier').textContent = totalPanier.toFixed(2) + '€'
                        document.querySelector('#TVA').textContent = (totalPanier * tva).toFixed(2) + ' €'
                        document.querySelector('#TTC').textContent = ( (totalPanier + (totalPanier * tva))-(totalPanier * <?=  $reduc2 ?>/100)).toFixed(2) + ' €'
                        document.querySelector('#remise').textContent = (totalPanier * <?=  $reduc2  ?>/100).toFixed(2) + ' €'
                            
                      
        
            
                    
        
                    }else{
                        console.log("Erreur")
                    }
            }) 
             



        })

    })


    // Supprimer un produit du panier
    document.querySelectorAll('.btn-delete').forEach(function(btn){
      btn.addEventListener('click', function(e){
        const id = this.dataset.id
        let row = this.closest('tr')
        const confirmation = confirm('Voulez-vous vraiment supprimer ce produit ?')

        if(confirmation){
          fetch('suppPanierREQ.PHP',{
            method:'POST', 
            headers:{
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${id}`
          })
          .then(response =>response.text())
          .then(data => {
          if(data.trim() === 'success'){
            row.remove()
            let totalPanier = 0
            document.querySelectorAll('.sous-total').forEach(function(st){
                totalPanier += parseFloat(st.textContent)
            })

           
            document.querySelector('.total-panier').textContent = totalPanier.toFixed(2) + '€'
            document.querySelector('#TVA').textContent = (totalPanier * tva).toFixed(2) + ' €'
            document.querySelector('#TTC').textContent = ( (totalPanier + (totalPanier * tva))-(totalPanier * <?=  $reduc2  ?>/100)).toFixed(2) + ' €'
            document.querySelector('#remise').textContent = (totalPanier * <?=  $reduc2  ?>/100).toFixed(2) + ' €'
        
          }else{
            console.log(`La suppression a échoué ${data}`)
          }
          })
        }
      })
    })


  </script>