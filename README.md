#  <cite><div align="center"><img src="logo_carnus.png" width="325" height="150">
#  <cite><div align="center"><font color="(0,68,88)">Service GTB pour bâtiments communaux</font></div></cite>

---
### Table des matières :

* <a href="#CONT"> Contexte du projet</a>
* <a href="#OBJ">Objectifs du Projet</a>
* <a href="#OBJP">Objectifs Personnels</a>
* <a href="#LOGI"> Logiciels utilisés</a>
* <a href="#ARC"> Architecture du projet</a>
---

<a id="CONT"></a>
## <cite><font color="#00506b"> Contexte du projet</font></cite>

Le SMICA a pour objet la recherche, la veille technologique, l’accompagnement, le développement, la formation et la gestion de services et usages dans le domaine numérique pour l’ensemble de ses adhérents. Il intervient dans de nombreux domaines : gestion administrative (état civil, élections, paie, comptabilité, facturation), maintenance du matériel informatique, hébergement des données, dématérialisation des échanges avec les services de l’Etat, profil acheteur, réalisation de sites internet, systèmes d’information géographique, …

Les mairies sont en charge de bâtiments communaux qui représentent un coût de fonctionnement et des responsabilités en termes de gestion d’accès de qualité d’air et autres. Celles-ci trouveraient un grand intérêt à profiter d’un système de GTB leur permettant de suivre et commander l’ensemble de leurs bâtiments.

C’est pour cela que le SMICA désire proposer un nouveau service de Gestion Technique du Bâtiment à leurs adhérents pour un coût acceptable.

---
<a id="OBJ"></a>
## <cite><font color="#00506b"> Objectifs du projet</font></cite>

Le principal objectif de ce projet est de permettre au SMICA de proposer à ses clients une solution de gestion d’informations fiable et peu coûteuse

Ce projet permettra dans un second temps aux élèves en charge de ce dernier de se “professionnaliser” en s’impliquant dans les tâches attribuées. Il permettra une évaluation de leurs compétences dans le milieu professionnel en vue de l'examen du BTS. 

---
<a id="OBJP"></a>
## <cite><font color="#00506b"> Objectifs personnels</font></cite>

Mon but durant ce projet est de développer la partie frontend du site internet. Je dois développer un site qui s'adaptera en fonction de l'utilisateur qui se connecte, ce privilège se mettra en place grâce à un grade dans la base de données et qui sera analysé lors de la conenxion afin de modifier l'affihage du site. En parallèle je mettrai en place une double authentification lors de la connexion afin de permettre d'ajouter une couche de sécurité. Je devrai ensuite récupérer les valeurs à afficher sur la base de données commune en générant, en JavaScript, une API qui permettra un affichage de ces valeurs. Comme dit plus haut cette API devra s'adapter, s'empiler et s'effacer en fonction de l'utilisateur connecté.

---
<a id="LOGI"></a>
## <cite><font color="00506b"> Logiciels utilisés</font></cite>

[![Développement Web](https://img.shields.io/badge/HTML-CSS-yellow)](https://www.w3.org/) [![PHP SQL](https://img.shields.io/badge/PHP-MySQL-8A2BE2)](https://www.php.net/) [![Visual Studio Code](https://img.shields.io/badge/Visual%20Studio%20Code-2a52be)](https://www.carnus.fr/) ![GitHub git](https://img.shields.io/badge/GitHub-git-fd5800) ![Markdown](https://img.shields.io/badge/M%20⬇-191970) <br><img src="vaultwarden.png" width="150" height="30"> 

---
<a id="ARC"></a>
## <cite><font color="00506b"> Architecture du projet</font></cite>

📦Projet GTB
┗ 📂Code
  ┣ 📂admin
  ┃ ┣ 📜admin.php
  ┃ ┣ 📜ajout.php
  ┃ ┣ 📜ajouter_batiment.php
  ┃ ┣ 📜ajouter_client.php
  ┃ ┣ 📜compteadm.php
  ┃ ┣ 📜confirmation_client.php
  ┃ ┣ 📜confirmation.php
  ┃ ┗ 📜users.php
  ┣ 📂backend
  ┃ ┣ 📜config.php
  ┃ ┣ 📜dbcontroller.php
  ┃ ┣ 📜login.php
  ┃ ┣ 📜logout.php
  ┃ ┣ 📜register.php
  ┃ ┣ 📜style.css
  ┃ ┗ 📜style2.css
  ┣ 📂images
  ┃ ┣ 🖼️fond.jpg
  ┃ ┗ 🖼️smica.png
  ┣ 📂responsable
  ┃ ┣ 📜compteresp.php
  ┃ ┣ 📜responsable.php
  ┃ ┗ 📜update_consigne.php
  ┣ 📂utilisateur
  ┃ ┣ 📜compteuser.php
  ┃ ┗ 📜utilisateur.php
  ┣ 📜index.php
  ┣ 📜index2.php
  ┣ 📜mairie.sql
  ┗ 📜registration.sql


--- 
<details>

[:arrow_up:](#top)
