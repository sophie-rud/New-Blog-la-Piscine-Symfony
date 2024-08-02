<?php


namespace App\Controller\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;


class IndexController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index()
    {
        return $this->render('public/page/index.html.twig');
        // var_dump('hello');die;
    }
}
