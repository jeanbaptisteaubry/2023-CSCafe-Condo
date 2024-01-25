<?php

use App\Modele\Modele_Catalogue;
use App\Modele\Modele_Commande;
use App\Modele\Modele_Entreprise;
use App\Modele\Modele_Salarie;
use App\Modele\Modele_Utilisateur;
use App\Vue\Vue_Categories_Liste;
use App\Vue\Vue_Connexion_Formulaire_client;
use App\Vue\Vue_ConsentementRGPD;
use App\Vue\Vue_Entreprise_Gerer_Compte;
use App\Vue\Vue_Mail_Confirme;
use App\Vue\Vue_Mail_ReinitMdp;
use App\Vue\Vue_Menu_Administration;
use App\Vue\Vue_Menu_Entreprise_Client;
use App\Vue\Vue_Menu_Entreprise_Salarie;
use App\Vue\Vue_Produits_Info_Clients;
use App\Vue\Vue_Structure_BasDePage;
use App\Vue\Vue_Structure_Entete;

use PHPMailer\PHPMailer\PHPMailer;
//Ce contrôleur gère le formulaire de connexion pour les visiteurs

$Vue->setEntete(new Vue_Structure_Entete());

switch ($action) {
    case "reinitmdpconfirm":

          //comme un qqc qui manque... je dis ça ! je dis rien !

        $Vue->addToCorps(new Vue_Mail_Confirme());

        break;
    case "reinitmdp":


        $Vue->addToCorps(new Vue_Mail_ReinitMdp());

        break;
    case "Se connecter" :
        echo 1;
        if (isset($_REQUEST["compte"]) and isset($_REQUEST["password"])) {
            //Si tous les paramètres du formulaire sont bons

            $utilisateur = Modele_Utilisateur::Utilisateur_Select_ParLogin($_REQUEST["compte"]);

            if ($utilisateur != null) {
                //error_log("utilisateur : " . $utilisateur["idUtilisateur"]);
                if ($utilisateur["desactiver"] == 0) {
                    if ($_REQUEST["password"] == $utilisateur["motDePasse"]) {
                        $_SESSION["idUtilisateur"] = $utilisateur["idUtilisateur"];
                        //error_log("idUtilisateur : " . $_SESSION["idUtilisateur"]);
                        $_SESSION["idCategorie_utilisateur"] = $utilisateur["idCategorie_utilisateur"];
                        //error_log("idCategorie_utilisateur : " . $_SESSION["idCategorie_utilisateur"]);
                        switch ($utilisateur["idCategorie_utilisateur"]) {
                            case 1:
                                $_SESSION["typeConnexionBack"] = "administrateurLogiciel"; //Champ inutile, mais bien pour voir ce qu'il se passe avec des étudiants !
                                $Vue->setMenu(new Vue_Menu_Administration( $_SESSION["idCategorie_utilisateur"]));
                                break;
                            case 2:
                                $_SESSION["typeConnexionBack"] = "utilisateurCafe";
                                //If pas rgpd
                                 ///Afficher vue rgpd
                                ///
                                ///
                                //$Vue->addToCorps(new Vue_ConsentementRGPD());
                                //else

                                //{
                                $Vue->setMenu(new Vue_Menu_Administration( $_SESSION["idCategorie_utilisateur"]));
                                //
                                break;
                            case 3:
                                $_SESSION["typeConnexionBack"] = "entrepriseCliente";
                                //error_log("idUtilisateur : " . $_SESSION["idUtilisateur"]);
                                $_SESSION["idEntreprise"] = Modele_Entreprise::Entreprise_Select_Par_IdUtilisateur($_SESSION["idUtilisateur"])["idEntreprise"];


                                $Vue->setEntete(new Vue_Structure_Entete());
                                $Vue->setMenu(new Vue_Menu_Entreprise_Client());
                                $Vue->addToCorps(new Vue_Entreprise_Gerer_Compte());
                                $Vue->setBasDePage(new Vue_Structure_BasDePage());
                                break;
                            case 4:
                                $_SESSION["typeConnexionBack"] = "salarieEntrepriseCliente";
                                $_SESSION["idSalarie"] = $utilisateur["idUtilisateur"];
                                $_SESSION["idEntreprise"] = Modele_Salarie::Salarie_Select_byId($_SESSION["idUtilisateur"])["idEntreprise"];
                                $Vue->setEntete(new Vue_Structure_Entete());
                                $quantiteMenu = Modele_Commande::Panier_Quantite($_SESSION["idEntreprise"]);

                                $Vue->setMenu(new Vue_Menu_Entreprise_Salarie($quantiteMenu));

                                //Vue_Entreprise_Client_ Menu();
                                $listeCategorie = Modele_Catalogue::Categorie_Select_Tous();
                                $Vue->addToCorps(new Vue_Categories_Liste($listeCategorie, false));
                                $listeProduit = Modele_Catalogue::Produits_Select_Libelle_Categ("client");
                                $Vue->addToCorps(new Vue_Produits_Info_Clients($listeProduit));
                                break;
                        }

                    } else {//mot de passe pas bon
                        $msgError = "Mot de passe erroné";

                        $Vue->addToCorps(new Vue_Connexion_Formulaire_client($msgError));

                    }
                } else {
                    $msgError = "Compte désactivé";

                    $Vue->addToCorps(new Vue_Connexion_Formulaire_client($msgError));

                }
            } else {
                $msgError = "Identification invalide";

                $Vue->addToCorps(new Vue_Connexion_Formulaire_client($msgError));
            }
        } else {
            $msgError = "Identification incomplete";

            $Vue->addToCorps(new Vue_Connexion_Formulaire_client($msgError));
        }
    break;
    default:

        $Vue->addToCorps(new Vue_Connexion_Formulaire_client());

        break;
}


$Vue->setBasDePage(new Vue_Structure_BasDePage());