<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\AdminCommentType;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminCommentController extends AbstractController
{
    /**
     * Permet de lister tous les commentaires utilisateurs
     * 
     * @Route("/admin/comments", name="admin_comments_index")
     * 
     * @return Response
     */
    public function index(CommentRepository $commentRepository)
    {
        // $this->getDoctrine()->getRepository(Comment::class)
        return $this->render('admin/comment/index.html.twig', [
            'comments' =>$commentRepository->findAll()
        ]);
    }

    /**
     * Permet de modifier un commentaire
     * 
     * @Route("admin/comments/{id}/edit", name="admin_comment_edit")
     * 
     * @return Response
     */
    public function edit(Comment $comment, Request $request) {

        $form = $this->createForm(AdminCommentType::class, $comment);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            $this->addFlash(
                'success',
                "Le commentaire {$comment->getId()} a bien été enregistré"
            );
        }

        return $this->render('admin/comment/edit.html.twig', [
            'form' => $form->createView(),
            'comment' => $comment
        ]);
    }

    /**
     * Permet de supprimer un commentaire
     *
     * @Route("admin/comments/{id}/delete", name="admin_comment_delete")
     * 
     * @param Comment $comment
     * @return Response
     */
    function delete(Comment $comment) {
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($comment);
        $manager->flush();

        $this->addFlash(
            'success', 
            "Le commentaire de {$comment->getAuthor()->getFullName()} a bien été supprimé !"
        );

        return $this->redirectToRoute('admin_comments_index');

    }



}
