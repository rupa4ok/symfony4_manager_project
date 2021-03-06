<?php

namespace App\Controller;

use App\Model\Company\Service\InnChecker\Checker;
use App\Model\Company\Service\InnChecker\Inn;
use App\Services\Redis\RedisHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private $redisHelper;
    
    public function __construct(RedisHelper $redisHelper)
    {
        $this->redisHelper = $redisHelper;
    }
    
    /**
     * @Route("/", name="home")
     * @param Checker $checker
     * @return     Response
     */
    public function index(Checker $checker)
    {
        $inn = $checker->check(new Inn(190275968));
        
        return $this->render(
            'app/home.html.twig',
            [
            'inn' => $inn
            ]
        );
    }
    
    /**
     * @param Request $request
     *
     * @Route("/set")
     *
     * @return Response
     */
    public function setAction(Request $request)
    {
        $key = 'test';
        $value = '123';
        
        $result = null;
        
        try {
            if ($key && $value) {
                $this->redisHelper->set($key, $value);
                $result = ['key' => $key, 'value' => $value];
            }
        } catch (\DomainException $e) {
            $result = $e->getMessage();
        }
        
        return new Response(json_encode($result));
    }
    
    /**
     * @param Request $request
     *
     * @Route("/get")
     *
     * @return Response
     */
    public function getAction(Request $request)
    {
        $key = $request->query->get('key');
        
        $result = null;
        
        try {
            if ($key) {
                $result = ['key' => $key, 'value' => $this->redisHelper->get($key)];
            }
        } catch (\DomainException $e) {
            $result = $e->getMessage();
        }
        
        return new Response(json_encode($result));
    }
}
