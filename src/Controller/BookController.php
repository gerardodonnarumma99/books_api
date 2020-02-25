<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\BookEntityRepository;
use FOS\RestBundle\Controller\Annotations as Rest;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Deserializer\Deserializer;

class BookController extends AbstractFOSRestController
{
    /**
     * @Route("api/v1/books", name="createBook", methods="POST")
     */
    public function addBook(Request $request)
    {
	try{
	   $bookRequest=json_decode($request->getContent(),1,512,JSON_THROW_ON_ERROR);
           $book=new Book();
           $book->setTitle($bookRequest['title']);
           $book->setPrice($bookRequest['price']);

	   $entityManager = $this->getDoctrine()->getManager();
	   $entityManager->persist($book);
	   $entityManager->flush();
           $jsonContent=$this->get('serializer')->serialize($book,'json');

	   return new Response($jsonContent);
	}
	catch(\JsonException $exception) {
	   return new Response($exception->getMessage(),400);
	}
    }

    /**
     * @Route("api/v1/books/{id}", name="getOneBook", methods="GET")
     */
    public function getOneBook($id)
    {
	$repository=$this->getDoctrine()->getRepository(Book::class);
	$book=$repository->find($id);
	
	if($book==null)
	 return new Response(null,400);

	$jsonContent=$this->get('serializer')->serialize($book,'json');
        
	return new Response($jsonContent,200);
    }

    /**
     * @Route("api/v1/books", name="getListBook", methods="GET")
     */
    public function getListBook()
    {
	$repository=$this->getDoctrine()->getRepository(Book::class);

	$books=$repository->findAll();

	$listBook=[];
	foreach($books as $book)
		array_push($listBook,$this->get('serializer')->serialize($book,'json'));
	
	if($listBook==null)
	 return new Response(null,204);
        
	return new Response(implode($listBook),200);
    }

    /**
     * @Route("api/v1/books/{id}", name="deleteBook", methods="DELETE")
     */
    public function deleteBook($id)
    {
	$entityManager = $this->getDoctrine()->getManager();
	$book=$entityManager->getRepository(Book::class)->find($id);

	if($book==null)
	 return new Response(null,400);

	$entityManager->remove($book);
	$entityManager->flush();
        
	return new Response(null,204);
    }

    /**
     * @Route("api/v1/books/{id}", name="modifyBook", methods="PUT")
     */
    public function modifyBook($id,Request $request)
    {
	try{
	   $bookRequest=json_decode($request->getContent(),1,512,JSON_THROW_ON_ERROR);
	   $entityManager=$this->getDoctrine()->getManager();
	   $book=$entityManager->getRepository(Book::class)->find($id);
	   if($book==null)
	     return new Response(null,400);


          $book->setTitle($bookRequest['title']);
          $book->setPrice($bookRequest['price']);

	  $entityManager->persist($book);
	  $entityManager->flush();

	  return new Response(null,204);
	}
	catch(\JsonException $exception) {
	   return new Response($exception->getMessage(),400);
	}

    }
}
