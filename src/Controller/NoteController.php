<?php

namespace App\Controller;

use App\Repository\NoteRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notes')]
class NoteController extends AbstractController
{
    #[Route('/', name: 'app_note_all', methods: ['GET'])]
    public function all(NoteRepository $nr): Response
    {
        
        return $this->render('note/all.html.twig', [
            'allNotes' => $nr->findBy(
                ['is_public' => true ],  
                ['created_at' => 'DESC' ]
            )
        ]);
    }

    
    #[Route('/{slug}', name: 'app_note_show', methods: ['GET'])]
    public function show(string $slug, NoteRepository $nr): Response
    {
        return $this->render('note/show.html.twig', [
            'note' => $nr->findOneBySlug($slug)
        ]);
    }
    
    #[Route('/{username}', name: 'app_note_user')]
    public function userNotes(
        string $username, 
        UserRepository $user,
        ): Response
    {
        
        $creator = $user->findOneByUsername($username);
        return $this->render('note/user.html.twig', [
            'creator' => $creator,
            'userNotes' => $creator->getNotes()
        ]);
    }
    
    #[Route('/new', name: 'app_note_new', methods: ['GET', 'POST'])]
    public function new(): Response
    {
       
        return $this->render('note/new.html.twig', []);
    }
    
    #[Route('/edit/{slug}', name: 'app_note_edit', methods: ['GET', 'POST'])]
    public function edit(string $slug, NoteRepository $nr): Response
    {
       $note = $nr->findOneBySlug($slug);
        return $this->render('note/edit.html.twig', []);
    }
    #[Route('/delete/{slug}', name: 'app_note_delete', methods: ['POST'])]
    public function delete(string $slug, NoteRepository $nr): Response
    {
       $note = $nr->findOneBySlug($slug);
       $this->addFlash('success', 'Your code snippet has been deleted');
        return $this->redirectToRoute('app_note_user');
    }
}