<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Form\ConfirmationType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/article", name="admin_article_")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="list")
     */
    public function index(ArticleRepository $repository)
    {
        return $this->render('admin/article/index.html.twig', [
            'articles' => $repository->findAll()
        ]);
    }

    /**
     * @Route("/new", name="add")
     */
    public function add(Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(ArticleType::class);
        // HandleRequest permet de récupérer les données POST et de procéder à la validation
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * getData() permet de récupérer les données de formulaire
             * Elle retourne par défaut un tableau des champs du formulaire
             * ou il retourne un objet de la classe a laquelle il est lié
             */
            /** @var Article $article */
            $article = $form->getData();

            $em->persist($article);
            $em->flush();

            $this->addFlash('success', 'l\'artice a été créé');
            return $this->redirectToRoute('admin_article_edit', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('admin/article/add.html.twig', [
            'article_form' => $form->createView()
        ]);
    }

    /**
     * @Route ("/edit/{id}", name="edit")
     */
    public function edit(Article $article, Request $request, EntityManagerInterface $em)
    {
        /**
         * On peut pré-remplir un formulaire en passant un 2eme argument à createForm
         * On passe un tableau assoc ou un objet si le formulaire est lié à une classe
         */
        $form = $this->createForm(ArticleType::class, $article);
        // Le formulaire va directement modifier l'objet
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Article mis à jour' );
        }

        return $this->render('admin/article/edit.html.twig', [
            'article' => $article,
            'article_form' => $form->createView()
        ]);
    }

    /**
     * @Route("/suppression/{id}", name="delete")
     */
    public function delete(Article $article, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(ConfirmationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->remove($article);
            $em->flush();

            $this->addFlash('info', 'l\'article ' . $article->getTitle() . ' a bien été supprimé.');
            return $this->redirectToRoute('admin_article_list');
        }

        return $this->render('admin/article/delete.html.twig', [
            'delete_form' => $form->createView(),
            'article' => $article
        ]);
    }
}
