document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (!form) return;

    form.addEventListener('submit', function(event) {
        let errors = [];

        // 1. RÉCUPÉRATION DES CHAMPS (S'ils existent dans la page)
        const nom      = document.getElementsByName('nom')[0];
        const prenom   = document.getElementsByName('prenom')[0];
        const cin      = document.getElementsByName('cin')[0];
        const pseudo   = document.getElementsByName('pseudo')[0];
        const password = document.getElementsByName('pwd')[0];

        // 2. RÈGLES COMMUNES (Inscription & Connexion)

        // Test Pseudo (Uniquement des lettres)
        const pseudoRegex = /^[A-Za-z]+$/;
        if (pseudo && !pseudoRegex.test(pseudo.value)) {
            errors.push("Le pseudo doit contenir uniquement des lettres.");
        }

        // Test Mot de passe (min 8 car. lettres/chiffres, finit par $ ou #)
        const pwdRegex = /^[A-Za-z0-9]{7,}[\$#]$/;
        if (password && !pwdRegex.test(password.value)) {
            errors.push("Le mot de passe doit faire au moins 8 caractères et finir par $ ou #.");
        }

        // 3. RÈGLES SPÉCIFIQUES À L'INSCRIPTION
        // (Ces tests ne s'activent que si les champs Nom, Prénom ou CIN sont présents)

        if (nom && prenom && cin) {
            // Test CIN (8 chiffres)
            const cinRegex = /^[0-9]{8}$/;
            if (!cinRegex.test(cin.value)) {
                errors.push("Le numéro CIN doit comporter exactement 8 chiffres.");
            }

            // Vérification vide pour Nom/Prénom
            if (nom.value.trim() === "" || prenom.value.trim() === "") {
                errors.push("Le nom et le prénom sont obligatoires.");
            }
        }

        // 4. BLOQUER L'ENVOI SI ERREUR — affichage via DOM
        // Supprimer l'ancien bloc d'erreurs s'il existe
        const oldDiv = document.getElementById('js-errors');
        if (oldDiv) oldDiv.parentNode.removeChild(oldDiv);

        if (errors.length > 0) {
            event.preventDefault();

            // Créer le conteneur d'erreurs
            const errorDiv = document.createElement('div');
            errorDiv.setAttribute('id', 'js-errors');
            errorDiv.setAttribute('style', 'color:red; font-weight:bold; margin-bottom:10px; text-align:center;');

            // Créer un <p> par erreur et l'ajouter au div
            errors.forEach(function(msg) {
                const p = document.createElement('p');
                p.setAttribute('style', 'margin:4px 0;');

                const icon = document.createTextNode('\u26A0 ');
                const text = document.createTextNode(msg);

                p.appendChild(icon);
                p.appendChild(text);
                errorDiv.appendChild(p);
            });

            // Insérer le div au début du fieldset (ou du form)
            const fieldset = form.querySelector('fieldset');
            const target = fieldset ? fieldset : form;
            target.insertBefore(errorDiv, target.firstChild);

            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});
