<?php

// lié au typage
// typer permet de repérer plus rapidement les erreurs (si le type renvoyé n'est pas le type attendu, par exemple, s'assurer du type de réponse renvoyé, et éviter les bugs)
declare(strict_types=1);

// Création d'un namespace, qui indique le chemin de la classe courante
namespace App\Controller\Public;

// On appelle le namespace des classes Symfony qu'on utilise, Symfony fera le require vers ces classes
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Nouvelle classe ArticleController qui hérite de la classe AbstractController
class ArticleController extends AbstractController {

    // Annotation qui permet de créer une route dès que la fonction listArticle est appelée
    #[Route('/articles', name: 'articles_list')]
    public function listArticle(ArticleRepository $articleRepository):Response {

        // Les données de la nouvelle instance de la classe ArticleRepository sont stockées dans la variable $articleRepository
        // Dans $articles, on stocke le résultat de notre select sur $articleRepository. Ici findAll(), donc tous les articles.
        $articles = $articleRepository->findAll();

        // On retourne une réponse http en html
        return $this->render('public/page/articles/articlesList.html.twig', [
            'articles' => $articles
            ]);
    }


    #[Route('/articles/show/{id}', name: 'article_show')]
    public function showArticle(int $id, ArticleRepository $articleRepository):Response {
        // Dans $article, on stocke le résultat de notre recherche par id dans les données de la table Article
        $article = $articleRepository->find($id);

        // Si aucun article n'est trouvé, on affiche une page erreur 404
        if (!$article || !$article->isPublished()) {
            $html404 = $this->renderView('public/page/page404.html.twig');
            return new Response($html404, 404);
        }

        // On retourne une réponse http en html
        return $this->render('public/page/articles/articleShow.html.twig', [
            'article' => $article
        ]);

    }

}