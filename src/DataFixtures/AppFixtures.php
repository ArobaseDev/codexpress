<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Like;
use App\Entity\Note;
use App\Entity\User;
use App\Entity\Category;
use App\Entity\Network;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private $slug = null;
    private $hash = null;
    
    public function __construct(
        private SluggerInterface $slugger,
        UserPasswordHasherInterface $hasher
        )
    {
       
        $this->slug = $slugger; // Initialisation du slug à partir du titre
        $this->hash = $hasher; // Initialisation du slug à partir du titre
    }
    
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
       # Tableau contenant les catégories
        $categories = [
            'HTML' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/html5/html5-plain.svg',
            'CSS' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/css3/css3-plain.svg',
            'JavaScript' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/javascript/javascript-plain.svg',
            'PHP' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/php/php-plain.svg',
            'SQL' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/postgresql/postgresql-plain.svg',
            'JSON' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/json/json-plain.svg',
            'Python' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/python/python-plain.svg',
            'Ruby' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/ruby/ruby-plain.svg',
            'C++' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/cplusplus/cplusplus-plain.svg',
            'Go' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/go/go-wordmark.svg',
            'bash' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/bash/bash-plain.svg',
            'Markdown' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/markdown/markdown-original.svg',
            'Java' => 'https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/java/java-original-wordmark.svg',
        ];

        $categoryArray = [];

        foreach ($categories as $title => $icon) {  
            $category = new Category();
            $category
            ->setTitle($title)
            ->setIcon($icon);
            array_push($categoryArray, $category); // Ajout de l'objet
            $manager->persist($category);
        }

        $users = []; //  Stockage des utilisateurs dans un tableau pour les likes
        $notes = []; //  

        // Création de 10 utilisateurs aléatoires
        for ($i = 0; $i < 10; $i++) {
            $username = $faker->userName;
            $usernameFinal = $this->slug->slug($username);
        //    $userImage = $faker->
            $user = new User();
            $user
                ->setEmail($usernameFinal .'@'. $faker->freeEmailDomain)
                ->setUsername($username)
                ->setPassword($this->hash->hashPassword($user, 'admin'))
                ->setRoles(['ROLE_USER'])
                ->setImage("https://avatar.iran.liara.run/public");  // Sachant que l'utilisateur peux ne pas avoir d'image de profil. On peux ici créér la logique pour les Network et les Likes.    
                $manager->persist($user);
                $users[] = $user;  // Ajout de l'utilisateur dans le tableau users
        }
        //     Création de 10 notes aléatoires
            for ($j = 0; $j < 10; $j++) {
                $note = new Note();
                $note
                    ->setTitle($faker->sentence)
                    ->setSlug($this->slug->slug($note->getTitle()))
                    ->setContent($faker->paragraphs(4, true))
                    ->setPublic($faker->boolean(50))
                    ->setViews($faker->numberBetween(100, 10000))
                    ->setCreator($user)
                    ->setCategory($faker->randomElement($categoryArray))
                    ;          
                $manager->persist($note);
                $notes[] = $note; // Ajout de la note au tableau
            }   
    
        // Génération de Likes aléatoires
        foreach ($notes as $note) {
            // Chaque note peux recevoir ou pas des likes 
            if ($faker->boolean(70)) { 
                // Sélection aléatoire d'utilisateurs qui vont aimer la note
                $randomUsers = $faker->randomElements($users, $faker->numberBetween(0, count($users)));

                foreach ($randomUsers as $user) {
                    if ($faker->boolean(50)) { // 50% de chance que l'utilisateur aime la note
                        $like = new Like();
                        $like
                            ->setCreator($user)
                            ->setNote($note);
                        
                        $manager->persist($like);
                    }
                }
            }
        }

               // Génération des Networks 
               foreach ($users as $user) {
                if ($faker->boolean(60)) { // 60% de chance qu'un utilisateur ait des réseaux
                    $numberOfNetworks = $faker->numberBetween(0, 3); // Chaque utilisateur peut avoir entre 0 et 3 réseaux
    
                    for ($i = 0; $i < $numberOfNetworks; $i++) {
                        $network = new Network();
                        $network
                            ->setName($faker->company) 
                            ->setUrl($faker->url) 
                            ->setCreator($user); 
                        
                        $manager->persist($network);
                    }
                }
            }


          
        $manager->flush();
    }
}