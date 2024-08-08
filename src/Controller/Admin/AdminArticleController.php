<?php

// lié au typage
// typer permet de repérer plus rapidement les erreurs (si le type renvoyé n'est pas le type attendu, par exemple, s'assurer du type de réponse renvoyé, et éviter les bugs)
declare(strict_types=1);


namespace App\Controller\Admin;


use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


class AdminArticleController extends AbstractController {

    // Annotation qui permet de créer une route dès que la fonction adminListArticle est appelée
    #[Route('/admin/articles', name: 'admin_articles_list')]
    public function adminListArticle(ArticleRepository $articleRepository):Response {

        // Dans $articles, on stocke le résultat de la méthode appelée, qui fait un select sur $articleRepository. Ici findAll(), donc tous les articles.
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



    // Annotation qui permet de créer une route dès que la fonction insertArticle est appelée
    #[Route('admin/articles/insert', name: 'admin_article_insert')]
   public function insertArticle(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, ParameterBagInterface $params) {

        // On crée une nouvelle instance de la classe Article (de l'entité)
        $article = new Article();

        // on génère une instance de la classe de gabarit de formulaire, et on la lie avec l'entité Article
        // La variable $articleCreateForm contient donc l'instance de formulaire liée à l'entité choisie
        $articleCreateForm = $this->createForm(ArticleType::class, $article);

        // On lie le formulaire à la requête
        // Gère la récupération des données et les stocke dans l'entité
        $articleCreateForm->handleRequest($request);

        // Si le formulaire est soumis (posté) et complété avec des données valides (qui respectent les contraintes de champs)
        if ($articleCreateForm->isSubmitted() && $articleCreateForm->isValid()) {

            // On récupère le fichier depuis le formulaire
            $imageFile = $articleCreateForm->get('image')->getData();

            // Si un fichier est bien soumis
            if ($imageFile) {
                // On récupère le nom du fichier
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // On "nettoie" le nom du fichier avec $slugger->slug() (retire les caractères spéciaux...)
                $safeFilename = $slugger->slug($originalFilename);
                // On ajoute un identifiant unique au nom
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    // On récupère le chemin de la racine du projet
                    $rootPath = $params->get('kernel.project_dir');
                    // On déplace le fichier dans le dossier indiqué dans le chemin d'accès. On renomme
                    $imageFile->move($rootPath . '/public/uploads', $newFilename);
                } catch (FileException $e) {
                    dd($e->getMessage());
                }

                // On stocke le nom du fichier dans la propriété image de l'entité article
                $article->setImage($newFilename);
            }


            // On prépare la requête sql,
            $entityManager->persist($article);
            // puis on l'exécute.
            $entityManager->flush();

            // On affiche un message pour informer l'utilisateur du succès de la requête
            $this->addFlash('success', 'Article enregistré !');

            // On fait une redirection sur la page du formulaire d'insertion
            // plus logique de retomber sur la liste des articles ou page de mise à jour des articles
            return $this->redirectToRoute('admin_article_insert');

        }

        // Avec la méthode createView(), on génère une instance de 'vue' du formulaire, pour le render
       $articleCreateFormView = $articleCreateForm->createView();

        // On retourne une réponse http (le fichier html du formulaire)
        return $this->render('admin/page/articles/insertArticle.html.twig', [
           'articleForm' => $articleCreateFormView
        ]);

    }


    #[Route('admin/articles/update/{id}', name: 'admin_article_update')]
    public function updateArticle(int $id, Request $request, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): Response {

        // on effectue un select par id sur les données récupérées de la table Article, on stocke le résultat de notre sélection dans $article.
        $article = $articleRepository->find($id);

        // on génère une instance de la classe de gabarit de formulaire, et on la lie avec l'entité Article
        $articleCreateForm = $this->createForm(ArticleType::class, $article);

        // On lie le formulaire à la requête
        $articleCreateForm->handleRequest($request);

        // Si le formulaire est soumis (posté) et complété avec des données valides (qui respectent les contraintes de champs)
        // On prépare la requête sql, puis on l'exécute.
        if ($articleCreateForm->isSubmitted() && $articleCreateForm->isValid()) {
            // $this->setUpdatedAt(new \DateTime('NOW'));
            $entityManager->persist($article);
            $entityManager->flush();

            // On affiche un message pour informer l'utilisateur du succès de la requête
            $this->addFlash('success', 'Article enregistré !');

        }

        // On génère une instance de 'vue' du formulaire, pour le render,
        $articleCreateFormView = $articleCreateForm->createView();
        // et on retourne une réponse http
        return $this->render('admin/page/articles/updateArticle.html.twig', [
            'articleForm' => $articleCreateFormView
        ]);
    }

}


