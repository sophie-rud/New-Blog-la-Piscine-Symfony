<?php

declare(strict_types=1);

namespace App\Controller\Admin;


use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


// Mettre une route avant une classe => la route donnée avant la classe vient en préfixe de toutes les routes des méthodes appelées dans la classe
#[Route('/admin/categories')]
class AdminCategoryController extends AbstractController {

    #[Route('/', name: 'admin_categories_list')]
    public function adminListCategories(CategoryRepository $categoryRepository): Response {

        $categories = $categoryRepository->findAll();


        return $this->render('admin/page/categories/adminCategoriesList.html.twig', [
            'categories' => $categories
        ]);
    }


    #[Route('/insert', name: 'admin_category_insert')]
    public function insertCategory(Request $request, EntityManagerInterface $entityManager): Response {

        $category = new Category();

        $categoryCreateForm = $this->createForm(CategoryType::class, $category);

        $categoryCreateForm->handleRequest($request);

        if ($categoryCreateForm->isSubmitted() && $categoryCreateForm->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Nouvelle catégorie ajoutée !');

            return $this->redirectToRoute('admin_categories_list');
        }

        $categoryCreateFormView = $categoryCreateForm->createView();
        return $this->render('admin/page/categories/insertCategory.html.twig', [
            'categoryForm' => $categoryCreateFormView,
        ]);
    }


    #[Route('/delete/{id}', name: 'admin_category_delete')]
    public function deleteCategory(int $id, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): Response {

        $category = $categoryRepository->find($id);

        if (!$category) {
            $html404 = $this->renderView('admin/page/page404.html.twig');
            return new Response($html404, 404);
        }

        /* try { */
            $entityManager->remove($category);
            $entityManager->flush();

            $this->addFlash('success', 'La catégorie a été supprimée');
            /* } catch(\Exception $exception){
            return $this->renderView('admin/page/error.html.twig', [
                'errorMessage' => $exception->getMessage()
            ]);
        } */

            return $this->redirectToRoute('admin_categories_list');
    }



    #[Route('/update/{id}', name: 'admin_category_update')]
    public function updateCategory(int $id, Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): Response {

        $category = $categoryRepository->find($id);

        $categoryUpdateForm = $this->createForm(CategoryType::class, $category);

        $categoryUpdateForm->handleRequest($request);

        if ($categoryUpdateForm->isSubmitted() && $categoryUpdateForm->isValid()) {
            $category->setUpdatedAt(new \DateTime('now'));
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'La catégorie a été modifiée');

            // return $this->redirectToRoute('admin_categories_list');
        }

        $categoryUpdateFormView = $categoryUpdateForm->createView();
        return $this->render('admin/page/categories/updateCategory.html.twig', [
            'categoryUpdateForm' => $categoryUpdateFormView
        ]);
    }

}