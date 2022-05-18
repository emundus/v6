##✨ Nouvelles fonctionnalités ✨
- Cette version continue l'harmonisation visuelle sur le formulaire candidat
- Intégration d'un module d'interconnexion avec le système Apogée
- Amélioration de l'interconnexion avec le système CAS
  - Il est maintenant possible de récupérer tous les attributs définis par le CAS
  - La récupération de ces attributs permet de corriger le problème reporté pour les noms composés
- Amélioration de la gestion des phases
  - Vous pouvez dorénavant définir des dates de début et de fin pour chaque phase 

##🔥 Corrections majeures 🔥
- La génération du code programme pouvait poser problème lors d'un copier/coller à la création d'une campagne
- Lors d'un changement de statut l'information qu'un mail va être envoyé était dans certains cas erronée
- Optimisation des performances globales
  - Mise en place d'une file d'attente lors d'un certain nombre de connexions dépassées (définis à 5000 connexions par défaut)
  - Le temps de récupération des balises dynamique était proportionnelle au nombre de balises dynamique
- A la copie d'un dossier certaines données n'étaient pas copiées sur le nouveau
- Prévisualisation des documents : problème de compatibilité avec la dernière version de Safari
