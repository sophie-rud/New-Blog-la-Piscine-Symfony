<?php

declare(strict_types=1);

namespace App\Controller\Public;


use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class CategoryController extends AbstractController {

    // Annotation qui permet de créer une route dès que la fonction suivante est appelée
    #[Route('/categories', name: 'categories_list')]
    public function listCategory(CategoryRepository $categoryRepository):Response {

        // Dans $categories, on stocke le résultat de notre select sur $categoryRepository. Ici findAll(), donc toutes les catégories.
        $categories = $categoryRepository->findAll();

        // On retourne une réponse http en html
        return $this->render('public/page/categories/categoriesList.html.twig', [
            'categories' => $categories
        ]);
    }


    #[Route('/categories/show/{id}', name: 'category_show')]
    // on type les données entrées en paramètres. On type la classe CategoryRepository et on stocke la nouvelle instance dans la variable $categoryRepository
    public function showCategory(int $id, CategoryRepository $categoryRepository):Response {
        // Dans $category, on stocke le résultat de notre recherche dans les données de la table Category
        // $categoryRepository->find($id) équivaut à un select where id = id recherché
        $category = $categoryRepository->find($id);


        // Si aucune catégorie n'est trouvée, on affiche une page erreur 404
        if (!$category) {
            $html404 = $this->renderView('public/page/page404.html.twig');
            // on retourne une reponse http avec le html page 404 et le code http 404.
            return new Response($html404, 404);
        }


        // On retourne une réponse http en html
        return $this->render('public/page/categories/categoryShow.html.twig', [
            'category' => $category
        ]);
    }
}