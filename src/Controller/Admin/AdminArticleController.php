<?php

// lié au typage
// typer permet de repérer plus rapidement les erreurs (si le type renvoyé n'est pas le type attendu, par exemple, s'assurer du type de réponse renvoyé, et éviter les bugs)
declare(strict_types=1);


namespace App\Controller\Admin;


use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class AdminArticleController extends AbstractController {

    // Annotation qui permet de créer une route dès que la fonction adminListArticle est appelée
    #[Route('/admin/articles', name: 'admin_articles_list')]
    public function adminListArticle(ArticleRepository $articleRepository):Response {

        // Dans $articles, on stocke le résultat de la méthod appelée, qui fait un select sur $articleRepository. Ici findAll(), donc tous les articles.
        $articles = $articleRepository->findAll();

        // On retourne une réponse http en html
        return $this->render('admin/page/articles/adminArticlesList.html.twig', [
            'articles' => $articles
            ]);
    }



    #[Route('/admin/articles/show/{id}', name: 'admin_article_show')]
    public function adminShowArticle(int $id, ArticleRepository $articleRepository):Response {

        // Dans $article, on stocke le résultat de notre recherche par id dans les données de la table Article
        $article = $articleRepository->find($id);

        // Si aucun article n'est trouvé avec l'id cheché ou que l'article n'est pas publié, on affiche une page d'erreur et on renvoie un code d'erreur http
        if (!$article || !$article->isPublished()) {
            $html404 = $this->renderView('public/page404.html.twig');
            return new Response($html404, 404);
        }

        // On retourne une réponse http
        return $this->render('admin/page/articles/adminArticleShow.html.twig', [
            'article' => $article
        ]);

    }



    #[Route('/admin/articles/delete/{id}', name: 'admin_article_delete')]

    // On passe en paramètres de la fonction la classe ArticleRepository (car on va effectuer un select) et EntityManagerInterface (c)ar on va faire une modification des données (avec un delete) en bdd).
    public function adminDeleteArticle(int $id, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): Response {

        // Dans $article, on stocke le résultat de notre recherche par id dans les données de la table Article
        $article = $articleRepository->find($id);

        // Si aucun article n'est trouvé avec l'id recherché,
        if (!$article) {
            $html404 = $this->renderView('admin/page/page404.html.twig');
            return new Response($html404, 404);
        }

        // Le try catch permet d'éxecuter du code tout en récupérant les erreurs potentielles afin de les gérer correctement
        try {
            // On utilise la classe EntityManager pour préparer la requête SQL delete.
            $entityManager->remove($article);
            // On exécute la requête SQL
            $entityManager->flush();

            // permet d'enregistrer un message dans la session de PHP
            // ce message sera affiché grâce à twig sur la prochaine page
            $this->addFlash('success', 'L\'article a bien été supprimé !');

            // Si l'exécution du try a échoué, catch est exécuté et on renvoie une réponse http avec un message d'erreur
        } catch(\Exception $exception){
            return $this->renderView('admin/page/error.html.twig', [
                'errorMessage' => $exception->getMessage()
            ]);
        }


        // On fait une redirection sur la page admin d'affichage des articles
        return $this->redirectToRoute('admin_articles_list');
    }

}