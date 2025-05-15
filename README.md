# 🌿 E-Firma – Gestion Agricole Intelligente Multiplateforme

---

## 📘 Description du Projet

**E-Firma** est un projet développé dans le cadre du module *PIDEV 3A* à **Esprit School of Engineering**.  
Il s’agit d’une plateforme complète permettant aux **agriculteurs**, **clients**, **vétérinaires** et **experts** d’interagir via deux interfaces synchronisées : une application Web (Symfony) et une application Desktop (JavaFX).

### Objectifs :
- Digitaliser la gestion des parcelles et cultures
- Proposer un système de **vente de produits agricoles avec livraison**
- Offrir une interface de **réservation de services** (consultations, expertises, etc.)
- Intégrer des fonctionnalités **météo, sol, IA (recommandation de cultures)**

---

##  Table des Matières

- [Installation](#installation)
- [Utilisation](#utilisation)
- [Fonctionnalités](#fonctionnalités)
- [Contribution](#contribution)
- [Hébergement](#Hébergement)
- [Licence](#licence)

---

##  Installation

### 1. Clonez le repository :

```bash
git clone https://github.com/SaifGharbi/e-firma.git
cd e-firma
```

### 2. Symfony (Web)
```bash
cd web-symfony/
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
symfony serve
```

### 3. JavaFX (Desktop)
- Ouvrir dans IntelliJ / VSCode
- Lancer `Main.java` dans `com.agriapp.Main`
- Nécessite Java 17

### 4. Flask (IA - Recommandation)
```bash
cd flask-ml-api/
pip install -r requirements.txt
python CROP_ai.py
```

---

##  Utilisation

###  Application Web (Symfony)

- Connexion / inscription (hashage sécurisé `$2y$`)
- Gestion des parcelles, cultures et utilisateurs
- Statistiques & visualisation (OpenStreetMap)
- Vente de produits (avec livraison)
- Réservation de services avec vétérinaires ou experts
- Suspension temporaire des comptes (admin)

###  Application JavaFX

- Authentification synchronisée avec Symfony
- Consultation météo (OpenWeatherMap) & sol (SoilGrids)
- Recommandation de cultures (modèle IA)
- Ajout de produits agricoles
- Réservation de services (experts / vétérinaires)

---

##  Fonctionnalités

- ✅ Gestion multi-utilisateur : clients, agriculteurs, vétérinaires, experts
- ✅ Vente de produits & livraison intégrée
- ✅ Système de services/rendez-vous
- ✅ API Flask pour recommandation intelligente
- ✅ Intégration OpenWeatherMap & SoilGrids
- ✅ Authentification sécurisée (bcrypt)
- ✅ Suspension temporaire + notifications par email

---

##  Contribution

Nous remercions tous ceux qui ont contribué à ce projet ! ❤️

### Contributeurs :
- [@utilisateur1](https://github.com/saifgharbi) – Gestion des Parcelles + AI
- [@utilisateur2](https://github.com/nouranelammouchi7) – Gestion Produits + ChatBot
- [@utilisateur3](https://github.com/ahmedmelki) – Gestion des livraisons 
- [@utilisateur4](https://github.com/medsaidneffati) – Gestion Services/rendez vous
- [@utilisateur5](https://github.com/hadidilina5) – Gestion User + APIS

###  Comment contribuer ?

1. Fork le projet :
   - Clique sur le bouton **Fork** en haut à droite de la page GitHub

2. Clone ton fork :
```bash
git clone https://github.com/SaifGharbi/e-firma.git
cd e-firma
```

3. Crée une branche :
```bash
git checkout -b feature/ma-contribution
```

4. Push et crée une Pull Request :
```bash
git push origin feature/ma-contribution
```

---

## Hébergement

Le projet est hébergé comme suit :

- 🔗 **Application Web (Symfony)** : déployée localement via `symfony serve`
- 💻 **Application JavaFX** : exécutable localement via IntelliJ/VS Code
- 📦 **Dépôt public GitHub** : [https://github.com/SaifGharbi/e-firma](https://github.com/SaifGharbi/e-firma)


##  Licence

Ce projet est sous la licence **MIT**.

```
MIT License

Copyright (c) 2025

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files...
```

Pour plus de détails, voir le fichier [LICENSE](./LICENSE).

---

## 🏫 Remerciements

Projet développé dans le cadre du module **PIDEV 3A** à *Esprit School of Engineering*.  
Merci à tous nos enseignants et partenaires open data (OpenWeatherMap, SoilGrids).
