<?php
// src/Controller/ProductController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query;

class ProductController extends AbstractController
{
    /**
     * @Route("/shop/product/{id}", name="product_show")
     */
    public function show($id)
    {
        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        return new Response('Просмотр продукта: '.$product->getName().' '.$product->getDescription().' Цена: '.$product->getPrice());
    }
    /**
     * @Route("/shop/id")
     */
    public function authorAction() {
        return $this->render('/author.html.twig');
    }
    /**
     * @Route("/shop/all", name="shop_all")
     */
    public function displayAction() {
        $bk = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findAll();
        return $this->render('/display.html.twig', array('data' => $bk));
    }
    /**
     * @Route("/shop/new", name="shop_new")
     */
    public function newAction(Request $request) {
        $product = new Product();
        $form = $this->createFormBuilder($product)
            ->add('name', TextType::class, array('label' => 'Название'))
            ->add('description', TextType::class, array('label' => 'Описание'))
            ->add('price', TextType::class,  array('label' => 'Цена'))
            ->add('save', SubmitType::class, array('label' => 'Создать'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            $doct = $this->getDoctrine()->getManager();

            $doct->persist($product);
            $doct->flush();

            return $this->redirectToRoute('shop_all');
        } else {
            return $this->render('/new.html.twig', array(
                'form' => $form->createView(),
            ));
        }
    }
    /**
     * @Route("/shop/update/{id}", name = "shop_update" )
     */
    public function updateAction($id, Request $request) {
        $doct = $this->getDoctrine()->getManager();
        $bk = $doct->getRepository(Product::class)->find($id);

        if (!$bk) {
            throw $this->createNotFoundException(
                'Продукт не найден: '.$id
            );
        }
        $form = $this->createFormBuilder($bk)
            ->add('name', TextType::class, array('label' => 'Название'))
            ->add('description', TextType::class, array('label' => 'Описание'))
            ->add('price', TextType::class,  array('label' => 'Цена'))
            ->add('save', SubmitType::class, array('label' => 'Изменить'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            $doct = $this->getDoctrine()->getManager();

            // tells Doctrine you want to save the Product
            $doct->persist($product);

            //executes the queries (i.e. the INSERT query)
            $doct->flush();
            return $this->redirectToRoute('shop_all');
        } else {
            return $this->render('/update.html.twig', array(
                'form' => $form->createView(),
            ));
        }
    }
    /**
     * @Route("/shop/delete/{id}", name="shop_delete")
     */
    public function deleteAction($id) {
        $doct = $this->getDoctrine()->getManager();
        $bk = $doct->getRepository(Product::class)->find($id);

        if (!$bk) {
            throw $this->createNotFoundException('Продукт не найден: '.$id);
        }
        $doct->remove($bk);
        $doct->flush();
        return $this->redirectToRoute('shop_all');
    }
}
