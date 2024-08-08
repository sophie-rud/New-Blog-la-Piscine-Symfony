// On récupère tous les éléments HTML ayant pour classe "js-admin-article-delete" (les 'boutons' de suppression, qui vont permettre d'afficher les popup)
// On les stocke dans une variable (const)

const deleteArticleButtons = document.querySelectorAll('.js-admin-article-delete');

// Pour chaque 'bouton' de suppression trouvé,
deleteArticleButtons.forEach((deleteArticleButton) => {

    // On ajoute  un event listener au click : quand le 'bouton' est cliqué, une fonction de callback est exécutée
    // fonction de callback : c'est une fonction qui sera executée quand le click sera fait.
    // addEventListener attend qu'il se passe l'évènement passé en paramètre pour agir.
    deleteArticleButton.addEventListener('click', () => {

        // on sélectionne l'élément suivant (nextElementSibling), donc la popup
        const popup = deleteArticleButton.nextElementSibling;

        // et on l'affiche en modifiant son display en css
        popup.style.display = 'block';

    });
})