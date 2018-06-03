<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Redmine;

class ProjectController extends Controller
{
    /**
     * @Route("/")
     */
    public function number()
    {
        $number = mt_rand(0, 100);

        $client = new Redmine\Client('https://redmine.ekreative.com', '2fda745bb4cdd835fdf41ec1fab82a13ddc1a54c');

        dump($client->project->all());

        return $this->render('project/list.html.twig', array(
            'number' => $number,
        ));
    }
}