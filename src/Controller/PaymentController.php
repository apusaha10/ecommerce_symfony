<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ShopcartRepository;
use App\Repository\OrdersRepository;
//use App\Entity\Orders;
//stripe namespace
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class PaymentController extends AbstractController
{
    /**
     * @Route("/payment", name="payment")
     */
    public function index(): Response
    {
        return $this->render('payment/index.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }
    /**
     * @Route("/checkout", name="checkout", methods={"GET","POST"})
     */
    public function checkout(Request $request,  $stripeSK, ShopcartRepository $shopcartRepository): Response
    {
        //Shpocart data is added here 

        $user = $this->getUser();
        $userid = $user->getid();
        $total = $shopcartRepository->getUserShopCartTotal($userid);
        // Getting product data from shopcart.
        $product = $shopcartRepository->getUserShopcart($userid);

        // print_r($total); 


        Stripe::setApiKey($stripeSK);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => [
                [
                    
                    'price_data' => [
                    'currency'     => 'usd',
                    'product_data' => [
                        'name' => 'Total Amount'
                      ],

                        // 'product_data' => $product,
                        'unit_amount' => $total,
                    ],
                     'quantity'   => 1,
                ]
            ],

            'mode'                 => 'payment',
            'success_url'          => $this->generateUrl('success_url', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url'           => $this->generateUrl('cancel_url', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
        // $orders = new Orders();
        //         $entityManager = $this->getDoctrine()->getManager();
        //         $orders->setUserid($userid);
        //         $orders->setAmount($total);

        //         $orders->setStatus('New');
        //         $entityManager->persist($orders);
        //         $entityManager->flush();


        return $this->redirect($session->url, 303);
    }

    /**
     * @Route("/success-url", name="success_url")
     */

    public function successUrl(): Response
    {
        return $this->render('payment/success.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }
    /**
     * @Route("/cancel-url", name="cancel_url")
     */

    public function cancelUrl(): Response
    {
        return $this->render('payment/cancel.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }
}
